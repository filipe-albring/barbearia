<?php

function conectarBanco(): mysqli
{
    // Esse arquivo considera o desenvolvimento local no XAMPP, utilizando 'localhost'.
    // Para o ambiente de produção (Máquina Virtual), altere para o IP Fixo da máquina do banco de dados (Por exemplo: 192.168.56.102).
    $servidor = 'localhost';
    $usuario = 'root';
    $senha = '';
    $banco = 'barbearia';

    $conexao = new mysqli($servidor, $usuario, $senha, $banco);

    if ($conexao->connect_error) {
        die('Erro ao conectar ao banco de dados: ' . $conexao->connect_error);
    }

    if (!$conexao->set_charset('utf8mb4')) {
        die('Erro ao configurar charset da conexão: ' . $conexao->error);
    }

    return $conexao;
}

