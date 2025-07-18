<?php
require_once '../models/modelo_cupom.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'];
    if ($acao === 'cadastrar') {
        cadastrar_cupom($_POST['codigo'], $_POST['desconto'], $_POST['valor_minimo'], $_POST['validade']);
        header('Location: ../views/cupons.php');
    } elseif ($acao === 'atualizar') {
        atualizar_cupom($_POST['id_cupom'], $_POST['codigo'], $_POST['desconto'], $_POST['valor_minimo'], $_POST['validade'], isset($_POST['ativo']) ? 1 : 0);
        header('Location: ../views/cupons.php');
    }
}
?>
