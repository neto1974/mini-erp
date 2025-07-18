<?php

require_once '../models/modelo_pedido.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'];
    
    if ($acao === 'comprar') {
        error_log("Dados recebidos no POST de comprar: " . print_r($_POST, true));
        
        $id_variacao = isset($_POST['id_variacao']) ? (int)$_POST['id_variacao'] : 0;
        $quantidade = isset($_POST['quantidade']) ? (int)$_POST['quantidade'] : 1;
        
        if ($id_variacao <= 0) {
            header('Location: ../views/index.php?mensagem=' . urlencode('ID de variação inválido') . '&acao=comprar');
            exit;
        }
        
        $resultado = adicionar_ao_carrinho($id_variacao, $quantidade);
        header('Location: ../views/index.php?mensagem=' . urlencode($resultado['mensagem']) . '&acao=comprar');
    } elseif ($acao === 'finalizar') {
        $resultado = finalizar_pedido($_POST['cep'], $_POST['endereco_completo'], $_POST['codigo_cupom'] ?? null);
        header('Location: ../views/index.php?mensagem=' . urlencode($resultado['mensagem']) . '&acao=finalizar');
    }
}
?>
