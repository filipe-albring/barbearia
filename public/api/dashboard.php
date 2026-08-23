<?php
declare(strict_types=1);

require_once '../../config/conexao.php';

header('Content-Type: application/json; charset=utf-8');

function responderJson(array $corpo, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($corpo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function buscarLinhasDashboard(mysqli $conexao, string $dataInicial): array
{
    $sql = <<<'SQL'
        WITH itens_do_periodo AS (
            SELECT
                id_agendamento,
                dt_hora,
                status,
                cliente,
                barbeiro,
                servico,
                valor_unitario
            FROM vw_dashboard_itens_financeiros
            WHERE dt_hora >= ?
        ),
        itens_tratados AS (
            SELECT
                id_agendamento,
                DATE_FORMAT(dt_hora, '%Y-%m-%d') AS data_agendamento,
                DATE_FORMAT(dt_hora, '%H:%i') AS horario,
                status,
                cliente,
                barbeiro,
                servico,
                CAST(COALESCE(valor_unitario, 0) AS DECIMAL(10, 2)) AS valor_unitario
            FROM itens_do_periodo
        )
        SELECT *
        FROM itens_tratados
        ORDER BY data_agendamento DESC, horario DESC, id_agendamento DESC
    SQL;

    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        throw new RuntimeException('Não foi possível preparar a consulta dos indicadores.');
    }

    $stmt->bind_param('s', $dataInicial);
    $stmt->execute();
    $resultado = $stmt->get_result();

    return $resultado->fetch_all(MYSQLI_ASSOC);
}

function buscarProximosAgendamentosDashboard(mysqli $conexao): array
{
    $sql = <<<'SQL'
        WITH proximos_agendamentos AS (
            SELECT
                id_agendamento,
                dt_hora,
                status,
                cliente,
                barbeiro,
                servicos,
                valor_total
            FROM vw_dashboard_agendamentos
            WHERE dt_hora >= NOW()
                AND status <> 'Cancelado'
        )
        SELECT
            id_agendamento,
            DATE_FORMAT(dt_hora, '%d/%m/%Y %H:%i') AS data_hora,
            status,
            cliente,
            barbeiro,
            servicos,
            CAST(COALESCE(valor_total, 0) AS DECIMAL(10, 2)) AS valor_total
        FROM proximos_agendamentos
        ORDER BY dt_hora ASC
        LIMIT 5
    SQL;

    $resultado = $conexao->query($sql);

    if (!$resultado) {
        throw new RuntimeException('Não foi possível preparar a agenda futura.');
    }

    return $resultado->fetch_all(MYSQLI_ASSOC);
}

$periodosPermitidos = [0, 7, 30, 90];
$periodo = filter_input(INPUT_GET, 'periodo', FILTER_VALIDATE_INT);
$periodo = in_array($periodo, $periodosPermitidos, true) ? $periodo : 30;
$dataInicial = $periodo === 0
    ? '1900-01-01 00:00:00'
    : (new DateTimeImmutable('today'))->modify("-{$periodo} days")->format('Y-m-d 00:00:00');

try {
    $conexao = conectarBanco();
    $itensFinanceiros = buscarLinhasDashboard($conexao, $dataInicial);
    $proximosAgendamentos = buscarProximosAgendamentosDashboard($conexao);
    $conexao->close();

    responderJson([
        'ok' => true,
        'periodo' => $periodo,
        'gerado_em' => (new DateTimeImmutable())->format(DATE_ATOM),
        'itens_financeiros' => $itensFinanceiros,
        'proximos_agendamentos' => $proximosAgendamentos,
    ]);
} catch (Throwable $erro) {
    error_log('Erro no dashboard: ' . $erro->getMessage());

    responderJson([
        'ok' => false,
        'mensagem' => 'Não foi possível consultar os dados do dashboard. Verifique a conexão com o banco e execute a migração analítica.',
    ], 500);
}
