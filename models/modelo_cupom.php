<?php
require_once '../config.php';

function cadastrar_cupom($codigo, $desconto, $valor_minimo, $validade) {
    global $conexao_banco;
    $sql = "INSERT INTO cupons (codigo, desconto, valor_minimo, validade) VALUES (?, ?, ?, ?)";
    $stmt = $conexao_banco->prepare($sql);
    $stmt->bind_param("sdds", $codigo, $desconto, $valor_minimo, $validade);
    return $stmt->execute();
}

function listar_cupons() {
    global $conexao_banco;
    $sql = "SELECT * FROM cupons";
    $resultado = $conexao_banco->query($sql);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}

function obter_cupom($id_cupom) {
    global $conexao_banco;
    $sql = "SELECT * FROM cupons WHERE id_cupom = ?";
    $stmt = $conexao_banco->prepare($sql);
    $stmt->bind_param("i", $id_cupom);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function atualizar_cupom($id_cupom, $codigo, $desconto, $valor_minimo, $validade, $ativo) {
    global $conexao_banco;
    $sql = "UPDATE cupons SET codigo = ?, desconto = ?, valor_minimo = ?, validade = ?, ativo = ? WHERE id_cupom = ?";
    $stmt = $conexao_banco->prepare($sql);
    $stmt->bind_param("sddsi", $codigo, $desconto, $valor_minimo, $validade, $ativo, $id_cupom);
    return $stmt->execute();
}
?>
