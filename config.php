<?php
// Configurando a conexão com o banco de dados
$servidor = "localhost";
$usuario = "root"; // Altere se necessário
$senha = ""; // Altere se necessário
$banco = "mini_erp";

$conexao_banco = new mysqli($servidor, $usuario, $senha, $banco);

// Verificando a conexão
if ($conexao_banco->connect_error) {
    die("Falha na conexão: " . $conexao_banco->connect_error);
}

// Definindo charset
$conexao_banco->set_charset("utf8");

// Configurações do PHPMailer
#require_once 'ativos/lib/PHPMailer/PHPMailer.php';
#require_once 'ativos/lib/PHPMailer/SMTP.php';
#require_once 'ativos/lib/PHPMailer/Exception.php';

#use PHPMailer\PHPMailer\PHPMailer;
#use PHPMailer\PHPMailer\SMTP;
#use PHPMailer\PHPMailer\Exception;
?>