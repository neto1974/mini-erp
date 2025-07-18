<?php

$servidor = "localhost";
$usuario = "root"; // Altere para seu usuário do banco de dados criado
$senha = ""; // Altere para sua senha do banco de dados criado
$banco = "mini_erp";

$conexao_banco = new mysqli($servidor, $usuario, $senha, $banco);

if ($conexao_banco->connect_error) {
    die("Falha na conexão: " . $conexao_banco->connect_error);
}

$conexao_banco->set_charset("utf8");

// Configurações do PHPMailer.ComentadoN descomente quando configurar seu servidor ftp (leia README.md)
#require_once 'ativos/lib/PHPMailer/PHPMailer.php';
#require_once 'ativos/lib/PHPMailer/SMTP.php';
#require_once 'ativos/lib/PHPMailer/Exception.php';

#use PHPMailer\PHPMailer\PHPMailer;
#use PHPMailer\PHPMailer\SMTP;
#use PHPMailer\PHPMailer\Exception;
?>
