<?php

require_once '../models/modelo_produto.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'];
    if ($acao === 'cadastrar') {
        $variacoes = isset($_POST['variacoes']) ? $_POST['variacoes'] : [];
        cadastrar_produto($_POST['nome'], $variacoes);
        header('Location: ../views/index.php?mensagem=' . urlencode('Produto cadastrado com sucesso'));
    } elseif ($acao === 'atualizar') {
        $variacoes = isset($_POST['variacoes']) ? $_POST['variacoes'] : [];
        atualizar_produto($_POST['id_produto'], $_POST['nome'], $variacoes);
        header('Location: ../views/index.php?mensagem=' . urlencode('Produto atualizado com sucesso'));
    }
}
?>
