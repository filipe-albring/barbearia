<?php
require_once '../config/conexao.php';
require_once '../includes/funcoes.php';

$idAgendamento = (int) ($_POST['id_agendamento'] ?? 0);
$identificacao = $_POST['identificacao'] ?? '';

if ($idAgendamento <= 0 || !campoObrigatorio($identificacao)) {
    redirecionarComMensagem('agendamentos.php', 'danger', 'Não foi possível identificar o agendamento.');
}

$conexao = conectarBanco();
$cliente = buscarClientePorIdentificacao($conexao, $identificacao);

if (!$cliente) {
    redirecionarComMensagem('agendamentos.php', 'warning', 'Cliente não encontrado. Verifique os dados informados.', [
        'identificacao' => $identificacao,
    ]);
}

$cancelado = cancelarAgendamentoCliente($conexao, $idAgendamento, (int) $cliente['id_cliente']);

if ($cancelado) {
    redirecionarComMensagem('agendamentos.php', 'success', 'Agendamento cancelado com sucesso.', [
        'identificacao' => $identificacao,
    ]);
}

redirecionarComMensagem('agendamentos.php', 'danger', 'Não foi possível cancelar este agendamento.', [
    'identificacao' => $identificacao,
]);
