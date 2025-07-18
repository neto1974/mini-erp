<?php
// Incluindo a conexão
require_once 'config.php';

// Verificando método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Método não permitido');
}

// Validando token (exemplo simples)
$token = 'seu_token_secreto'; // Altere para um token seguro
if (!isset($_POST['token']) || $_POST['token'] !== $token) {
    http_response_code(401);
    die('Token inválido');
}

// Processando webhook
$id_pedido = filter_var($_POST['id_pedido'], FILTER_VALIDATE_INT);
$status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);

if (!$id_pedido || !$status) {
    http_response_code(400);
    die('Dados inválidos');
}

// Verificando existência do pedido
$sql = "SELECT id_pedido FROM pedidos WHERE id_pedido = ?";
$stmt = $conexao_banco->prepare($sql);
$stmt->bind_param("i", $id_pedido);
$stmt->execute();
if (!$stmt->get_result()->fetch_assoc()) {
    http_response_code(404);
    die('Pedido não encontrado');
}

// Atualizando ou removendo pedido
if ($status === 'cancelado') {
    // Restaurando estoque
    $sql_itens = "SELECT id_variacao, quantidade FROM itens_pedido WHERE id_pedido = ?";
    $stmt_itens = $conexao_banco->prepare($sql_itens);
    $stmt_itens->bind_param("i", $id_pedido);
    $stmt_itens->execute();
    $itens = $stmt_itens->get_result()->fetch_all(MYSQLI_ASSOC);
    
    foreach ($itens as $item) {
        $sql_estoque = "UPDATE estoque SET quantidade = quantidade + ? WHERE id_variacao = ?";
        $stmt_estoque = $conexao_banco->prepare($sql_estoque);
        $stmt_estoque->bind_param("ii", $item['quantidade'], $item['id_variacao']);
        $stmt_estoque->execute();
    }
    
    // Removendo itens do pedido
    $sql_delete_itens = "DELETE FROM itens_pedido WHERE id_pedido = ?";
    $stmt_delete_itens = $conexao_banco->prepare($sql_delete_itens);
    $stmt_delete_itens->bind_param("i", $id_pedido);
    $stmt_delete_itens->execute();
    
    // Removendo pedido
    $sql_delete = "DELETE FROM pedidos WHERE id_pedido = ?";
    $stmt_delete = $conexao_banco->prepare($sql_delete);
    $stmt_delete->bind_param("i", $id_pedido);
    $stmt_delete->execute();
} else {
    $sql = "UPDATE pedidos SET status = ? WHERE id_pedido = ?";
    $stmt = $conexao_banco->prepare($sql);
    $stmt->bind_param("si", $status, $id_pedido);
    $stmt->execute();
}

http_response_code(200);
echo 'Sucesso';
?>