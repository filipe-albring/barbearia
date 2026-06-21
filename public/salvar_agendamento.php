<?php
require_once '../config/conexao.php';
require_once '../includes/funcoes.php';

$identificacao = $_POST['identificacao'] ?? '';
$idBarbeiro = (int) ($_POST['id_barbeiro'] ?? 0);
$dataAgendamento = $_POST['data_agendamento'] ?? '';
$horaAgendamento = $_POST['hora_agendamento'] ?? '';
$idsServicos = $_POST['servicos'] ?? [];

if (
    !campoObrigatorio($identificacao) ||
    $idBarbeiro <= 0 ||
    !campoObrigatorio($dataAgendamento) ||
    !campoObrigatorio($horaAgendamento) ||
    count($idsServicos) === 0
) {
    redirecionarComMensagem('agendar.php', 'danger', 'Preencha todos os campos e selecione pelo menos um serviço.', [
        'identificacao' => $identificacao,
    ]);
}

$dataHora = converterDataHoraBrasileiraParaMysql($dataAgendamento, $horaAgendamento);

if (!$dataHora) {
    redirecionarComMensagem('agendar.php', 'danger', 'Informe a data no formato dd/mm/aaaa e um horário válido.', [
        'identificacao' => $identificacao,
    ]);
}

if (strtotime($dataHora) < time()) {
    redirecionarComMensagem('agendar.php', 'danger', 'Escolha uma data e horário futuros.', [
        'identificacao' => $identificacao,
    ]);
}

$conexao = conectarBanco();
$cliente = buscarClientePorIdentificacao($conexao, $identificacao);
$barbeiros = buscarBarbeiros($conexao);
$servicos = buscarServicos($conexao);

if (!$cliente) {
    redirecionarComMensagem('agendar.php', 'warning', 'Cliente não encontrado. Cadastre-se antes de realizar o agendamento.', [
        'identificacao' => $identificacao,
    ]);
}

if (!idExisteNoArray($barbeiros, $idBarbeiro, 'id_barbeiro')) {
    redirecionarComMensagem('agendar.php', 'danger', 'Barbeiro inválido.', [
        'identificacao' => $identificacao,
    ]);
}

foreach ($idsServicos as $idServico) {
    if (!idExisteNoArray($servicos, (int) $idServico, 'id_servico')) {
        redirecionarComMensagem('agendar.php', 'danger', 'Serviço inválido.', [
            'identificacao' => $identificacao,
        ]);
    }
}

try {
    criarAgendamento($conexao, (int) $cliente['id_cliente'], $idBarbeiro, $dataHora, $idsServicos);
    redirecionarComMensagem('agendamentos.php', 'success', 'Agendamento realizado com sucesso.', [
        'identificacao' => $identificacao,
    ]);
} catch (Throwable $erro) {
    redirecionarComMensagem('agendar.php', 'danger', 'Não foi possível realizar o agendamento.', [
        'identificacao' => $identificacao,
    ]);
}
