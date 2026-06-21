<?php
require_once '../config/conexao.php';
require_once '../includes/funcoes.php';

$dados = [
    'nome' => $_POST['nome'] ?? '',
    'data_nascimento' => $_POST['data_nascimento'] ?? '',
    'cpf' => $_POST['cpf'] ?? '',
    'email' => $_POST['email'] ?? '',
    'telefone' => $_POST['telefone'] ?? '',
];

if (
    !campoObrigatorio($dados['nome']) ||
    !campoObrigatorio($dados['data_nascimento']) ||
    !campoObrigatorio($dados['cpf']) ||
    !campoObrigatorio($dados['email']) ||
    !campoObrigatorio($dados['telefone'])
) {
    redirecionarComMensagem('cliente_novo.php', 'danger', 'Preencha todos os campos obrigatórios.');
}

if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
    redirecionarComMensagem('cliente_novo.php', 'danger', 'Informe um e-mail válido.');
}

$dataNascimento = converterDataBrasileiraParaMysql($dados['data_nascimento']);

if (!$dataNascimento) {
    redirecionarComMensagem('cliente_novo.php', 'danger', 'Informe a data de nascimento no formato dd/mm/aaaa.');
}

$dados['data_nascimento'] = $dataNascimento;

try {
    $conexao = conectarBanco();
    cadastrarCliente($conexao, $dados);
    redirecionarComMensagem('cliente_novo.php', 'success', 'Cliente cadastrado com sucesso.');
} catch (Throwable $erro) {
    redirecionarComMensagem('cliente_novo.php', 'danger', 'Não foi possível cadastrar o cliente. Verifique se CPF ou e-mail já existem.');
}
