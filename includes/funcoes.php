<?php

function buscarServicos(mysqli $conexao): array
{
    $servicos = [];
    $sql = 'SELECT id_servico, nm_servico, vl_preco FROM Servico ORDER BY nm_servico';
    $resultado = $conexao->query($sql);

    if ($resultado) {
        while ($servico = $resultado->fetch_assoc()) {
            $servicos[] = $servico;
        }
    }

    return $servicos;
}

function buscarBarbeiros(mysqli $conexao): array
{
    $barbeiros = [];
    $sql = "
        SELECT
            b.id_barbeiro,
            b.nm_barbeiro,
            GROUP_CONCAT(DISTINCT be.nm_email ORDER BY be.nm_email SEPARATOR ', ') AS emails,
            GROUP_CONCAT(DISTINCT bt.nr_telefone ORDER BY bt.nr_telefone SEPARATOR ', ') AS telefones
        FROM Barbeiro b
        LEFT JOIN Barbeiro_Email be ON be.id_barbeiro = b.id_barbeiro
        LEFT JOIN Barbeiro_Telefone bt ON bt.id_barbeiro = b.id_barbeiro
        GROUP BY b.id_barbeiro, b.nm_barbeiro
        ORDER BY b.nm_barbeiro
    ";
    $resultado = $conexao->query($sql);

    if ($resultado) {
        while ($barbeiro = $resultado->fetch_assoc()) {
            $barbeiros[] = $barbeiro;
        }
    }

    return $barbeiros;
}

function buscarClientes(mysqli $conexao): array
{
    $clientes = [];
    $sql = 'SELECT id_cliente, nm_cliente, cpf_cliente FROM Cliente ORDER BY nm_cliente';
    $resultado = $conexao->query($sql);

    if ($resultado) {
        while ($cliente = $resultado->fetch_assoc()) {
            $clientes[] = $cliente;
        }
    }

    return $clientes;
}

function buscarClientePorIdentificacao(mysqli $conexao, string $identificacao): ?array
{
    $identificacao = trim($identificacao);
    $identificacaoNumerica = somenteNumeros($identificacao);

    $sql = "
        SELECT DISTINCT c.id_cliente, c.nm_cliente, c.cpf_cliente
        FROM Cliente c
        LEFT JOIN Cliente_Email ce ON ce.id_cliente = c.id_cliente
        LEFT JOIN Cliente_Telefone ct ON ct.id_cliente = c.id_cliente
        WHERE c.cpf_cliente = ?
            OR REPLACE(REPLACE(REPLACE(c.cpf_cliente, '.', ''), '-', ''), ' ', '') = ?
            OR LOWER(ce.nm_email) = LOWER(?)
            OR ct.nr_telefone = ?
            OR REPLACE(REPLACE(REPLACE(REPLACE(ct.nr_telefone, '(', ''), ')', ''), '-', ''), ' ', '') = ?
        LIMIT 1
    ";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param('sssss', $identificacao, $identificacaoNumerica, $identificacao, $identificacao, $identificacaoNumerica);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $cliente = $resultado->fetch_assoc();

    if ($cliente) {
        return $cliente;
    }

    return null;
}

function listarProximosAgendamentos(mysqli $conexao): array
{
    $agendamentos = [];
    $sql = "
        SELECT
            a.id_agendamento,
            a.dt_hora,
            a.status,
            c.nm_cliente,
            b.nm_barbeiro,
            GROUP_CONCAT(s.nm_servico ORDER BY s.nm_servico SEPARATOR ', ') AS servicos
        FROM Agendamento a
        INNER JOIN Cliente c ON c.id_cliente = a.id_cliente
        INNER JOIN Barbeiro b ON b.id_barbeiro = a.id_barbeiro
        INNER JOIN Agendamento_Servico ags ON ags.id_agendamento = a.id_agendamento
        INNER JOIN Servico s ON s.id_servico = ags.id_servico
        WHERE a.dt_hora >= NOW()
        GROUP BY a.id_agendamento, a.dt_hora, a.status, c.nm_cliente, b.nm_barbeiro
        ORDER BY a.dt_hora ASC
    ";
    $resultado = $conexao->query($sql);

    if ($resultado) {
        while ($agendamento = $resultado->fetch_assoc()) {
            $agendamentos[] = $agendamento;
        }
    }

    return $agendamentos;
}

function listarProximosAgendamentosCliente(mysqli $conexao, int $idCliente): array
{
    $agendamentos = [];
    $sql = "
        SELECT
            a.id_agendamento,
            a.dt_hora,
            a.status,
            c.nm_cliente,
            b.nm_barbeiro,
            GROUP_CONCAT(s.nm_servico ORDER BY s.nm_servico SEPARATOR ', ') AS servicos
        FROM Agendamento a
        INNER JOIN Cliente c ON c.id_cliente = a.id_cliente
        INNER JOIN Barbeiro b ON b.id_barbeiro = a.id_barbeiro
        INNER JOIN Agendamento_Servico ags ON ags.id_agendamento = a.id_agendamento
        INNER JOIN Servico s ON s.id_servico = ags.id_servico
        WHERE a.dt_hora >= NOW()
            AND a.id_cliente = ?
            AND a.status <> 'Cancelado'
        GROUP BY a.id_agendamento, a.dt_hora, a.status, c.nm_cliente, b.nm_barbeiro
        ORDER BY a.dt_hora ASC
    ";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param('i', $idCliente);
    $stmt->execute();
    $resultado = $stmt->get_result();

    while ($agendamento = $resultado->fetch_assoc()) {
        $agendamentos[] = $agendamento;
    }

    return $agendamentos;
}

function cancelarAgendamentoCliente(mysqli $conexao, int $idAgendamento, int $idCliente): bool
{
    $status = 'Cancelado';
    $sql = "
        UPDATE Agendamento
        SET status = ?
        WHERE id_agendamento = ?
            AND id_cliente = ?
            AND dt_hora >= NOW()
            AND status <> 'Cancelado'
    ";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param('sii', $status, $idAgendamento, $idCliente);
    $stmt->execute();

    return $stmt->affected_rows > 0;
}

function cadastrarCliente(mysqli $conexao, array $dados): int
{
    $conexao->begin_transaction();

    try {
        $sqlCliente = 'INSERT INTO Cliente (nm_cliente, dt_nascimento, cpf_cliente) VALUES (?, ?, ?)';
        $stmtCliente = $conexao->prepare($sqlCliente);
        $stmtCliente->bind_param('sss', $dados['nome'], $dados['data_nascimento'], $dados['cpf']);
        $stmtCliente->execute();

        $idCliente = $conexao->insert_id;

        $sqlEmail = 'INSERT INTO Cliente_Email (nm_email, id_cliente) VALUES (?, ?)';
        $stmtEmail = $conexao->prepare($sqlEmail);
        $stmtEmail->bind_param('si', $dados['email'], $idCliente);
        $stmtEmail->execute();

        $sqlTelefone = 'INSERT INTO Cliente_Telefone (nr_telefone, id_cliente) VALUES (?, ?)';
        $stmtTelefone = $conexao->prepare($sqlTelefone);
        $stmtTelefone->bind_param('si', $dados['telefone'], $idCliente);
        $stmtTelefone->execute();

        $conexao->commit();
        return $idCliente;
    } catch (Throwable $erro) {
        $conexao->rollback();
        throw $erro;
    }
}

function criarAgendamento(mysqli $conexao, int $idCliente, int $idBarbeiro, string $dataHora, array $idsServicos): int
{
    $status = 'Agendado';
    $conexao->begin_transaction();

    try {
        $sqlAgendamento = 'INSERT INTO Agendamento (dt_hora, status, id_cliente, id_barbeiro) VALUES (?, ?, ?, ?)';
        $stmtAgendamento = $conexao->prepare($sqlAgendamento);
        $stmtAgendamento->bind_param('ssii', $dataHora, $status, $idCliente, $idBarbeiro);
        $stmtAgendamento->execute();

        $idAgendamento = $conexao->insert_id;
        $sqlServico = 'INSERT INTO Agendamento_Servico (id_agendamento, id_servico) VALUES (?, ?)';
        $stmtServico = $conexao->prepare($sqlServico);

        foreach ($idsServicos as $idServico) {
            $idServico = (int) $idServico;
            $stmtServico->bind_param('ii', $idAgendamento, $idServico);
            $stmtServico->execute();
        }

        $conexao->commit();
        return $idAgendamento;
    } catch (Throwable $erro) {
        $conexao->rollback();
        throw $erro;
    }
}

function formatarMoeda(float $valor): string
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function formatarDataHora(string $dataHora): string
{
    return date('d/m/Y H:i', strtotime($dataHora));
}

function converterDataHoraBrasileiraParaMysql(string $data, string $hora): ?string
{
    $dataHora = DateTime::createFromFormat('d/m/Y H:i', trim($data) . ' ' . trim($hora));

    if (!$dataHora || $dataHora->format('d/m/Y H:i') !== trim($data) . ' ' . trim($hora)) {
        return null;
    }

    return $dataHora->format('Y-m-d H:i:s');
}

function converterDataBrasileiraParaMysql(string $data): ?string
{
    $dataConvertida = DateTime::createFromFormat('d/m/Y', trim($data));

    if (!$dataConvertida || $dataConvertida->format('d/m/Y') !== trim($data)) {
        return null;
    }

    return $dataConvertida->format('Y-m-d');
}

function campoObrigatorio(string $valor): bool
{
    return trim($valor) !== '';
}

function idExisteNoArray(array $itens, int $id, string $campoId): bool
{
    foreach ($itens as $item) {
        if ((int) $item[$campoId] === $id) {
            return true;
        }
    }

    return false;
}

function limparTexto(string $valor): string
{
    return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
}

function somenteNumeros(string $valor): string
{
    return preg_replace('/\D/', '', $valor);
}

function redirecionarComMensagem(string $pagina, string $tipo, string $mensagem, array $extras = []): void
{
    $parametros = http_build_query([
        'tipo' => $tipo,
        'mensagem' => $mensagem,
    ] + $extras);

    header("Location: {$pagina}?{$parametros}");
    exit;
}
