<?php
// Incluindo dependências
require_once '../config.php';
require_once '../models/modelo_produto.php';

// Iniciando sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Função para adicionar ao carrinho
function adicionar_ao_carrinho($id_variacao, $quantidade) {
    global $conexao_banco;
    
    // Validar entrada
    $id_variacao = (int)$id_variacao;
    $quantidade = (int)$quantidade;
    if ($id_variacao <= 0) {
        error_log("Erro: id_variacao inválido ($id_variacao)");
        return ['sucesso' => false, 'mensagem' => 'ID da variação inválido'];
    }
    if ($quantidade <= 0) {
        error_log("Erro: quantidade inválida ($quantidade)");
        return ['sucesso' => false, 'mensagem' => 'Quantidade inválida'];
    }
    
    // Consultar variação e estoque
    $sql = "SELECT v.id_variacao, v.cor, v.tamanho, v.genero, v.tecido, v.preco, COALESCE(e.quantidade, 0) as quantidade, p.nome 
            FROM variacoes v 
            JOIN produtos p ON v.id_produto = p.id_produto 
            LEFT JOIN estoque e ON v.id_variacao = e.id_variacao 
            WHERE v.id_variacao = ?";
    $stmt = $conexao_banco->prepare($sql);
    $stmt->bind_param("i", $id_variacao);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
    
    // Depuração: logar resultado da consulta
    error_log("Consulta de estoque para id_variacao $id_variacao: " . print_r($item, true));
    
    if (!$item) {
        error_log("Erro: Variação não encontrada para id_variacao $id_variacao");
        return ['sucesso' => false, 'mensagem' => 'Variação não encontrada'];
    }
    
    if ($item['quantidade'] < $quantidade) {
        error_log("Erro: Estoque insuficiente para id_variacao $id_variacao. Estoque: {$item['quantidade']}, Solicitado: $quantidade");
        return ['sucesso' => false, 'mensagem' => 'Estoque insuficiente'];
    }
    
    if (!isset($_SESSION['carrinho'])) {
        $_SESSION['carrinho'] = [];
    }
    
    $_SESSION['carrinho'][$id_variacao] = [
        'nome' => $item['nome'],
        'cor' => $item['cor'],
        'tamanho' => $item['tamanho'],
        'genero' => $item['genero'],
        'tecido' => $item['tecido'],
        'preco' => $item['preco'],
        'quantidade' => $quantidade
    ];
    
    error_log("Item adicionado ao carrinho: id_variacao $id_variacao, quantidade $quantidade");
    return ['sucesso' => true, 'mensagem' => 'Item adicionado ao carrinho'];
}

// Função para calcular carrinho
function calcular_carrinho($codigo_cupom = null) {
    global $conexao_banco;
    
    // Inicializando subtotal
    $subtotal = 0;
    
    // Verificando se o carrinho existe
    if (!isset($_SESSION['carrinho']) || empty($_SESSION['carrinho'])) {
        $_SESSION['carrinho'] = [];
    } else {
        foreach ($_SESSION['carrinho'] as $item) {
            $subtotal += $item['preco'] * $item['quantidade'];
        }
    }
    
    $desconto = 0;
    if ($codigo_cupom) {
        $sql = "SELECT desconto, valor_minimo, validade, ativo 
                FROM cupons 
                WHERE codigo = ? AND ativo = TRUE AND validade >= CURDATE()";
        $stmt = $conexao_banco->prepare($sql);
        $stmt->bind_param("s", $codigo_cupom);
        $stmt->execute();
        $cupom = $stmt->get_result()->fetch_assoc();
        
        if ($cupom && $subtotal >= $cupom['valor_minimo']) {
            $desconto = $cupom['desconto'];
        }
    }
    
    $subtotal_com_desconto = $subtotal - $desconto;
    $frete = $subtotal_com_desconto > 200.00 ? 0.00 : ($subtotal_com_desconto >= 52.00 && $subtotal_com_desconto <= 166.59 ? 15.00 : 20.00);
    
    return [
        'subtotal' => $subtotal,
        'desconto' => $desconto,
        'frete' => $frete,
        'total' => $subtotal_com_desconto + $frete
    ];
}

// Função para finalizar pedido
function finalizar_pedido($cep, $endereco_completo, $codigo_cupom = null) {
    global $conexao_banco;
    
    // Calculando totais
    $totais = calcular_carrinho($codigo_cupom);
    
    // Verificando se o carrinho está vazio
    if (empty($_SESSION['carrinho'])) {
        return ['sucesso' => false, 'mensagem' => 'Carrinho vazio'];
    }
    
    // Criando pedido
    $sql = "INSERT INTO pedidos (subtotal, frete, cep, endereco_completo, status) 
            VALUES (?, ?, ?, ?, 'confirmado')";
    $stmt = $conexao_banco->prepare($sql);
    $stmt->bind_param("ddss", $totais['subtotal'], $totais['frete'], $cep, $endereco_completo);
    $stmt->execute();
    $id_pedido = $conexao_banco->insert_id;
    
    // Adicionando itens do pedido
    foreach ($_SESSION['carrinho'] as $id_variacao => $item) {
        $sql_item = "INSERT INTO itens_pedido (id_pedido, id_variacao, quantidade, preco_unitario) 
                     VALUES (?, ?, ?, ?)";
        $stmt_item = $conexao_banco->prepare($sql_item);
        $stmt_item->bind_param("iiid", $id_pedido, $id_variacao, $item['quantidade'], $item['preco']);
        $stmt_item->execute();
        
        // Atualizando estoque
        $sql_estoque = "UPDATE estoque SET quantidade = quantidade - ? WHERE id_variacao = ?";
        $stmt_estoque = $conexao_banco->prepare($sql_estoque);
        $stmt_estoque->bind_param("ii", $item['quantidade'], $id_variacao);
        $stmt_estoque->execute();
    }
    
    // Enviando e-mail (comentado para testes locais sem SMTP)
    /*
    $mensagem = "Pedido #$id_pedido\n\nItens:\n";
    foreach ($_SESSION['carrinho'] as $item) {
        $mensagem .= "- {$item['nome']} (Cor: {$item['cor']}, Tamanho: {$item['tamanho']}, Gênero: {$item['genero']}, Tecido: {$item['tecido']}, Qtd: {$item['quantidade']}) - R$ " . number_format($item['preco'], 2, ',', '.') . "\n";
    }
    $mensagem .= "\nSubtotal: R$ " . number_format($totais['subtotal'], 2, ',', '.') . 
                 "\nDesconto: R$ " . number_format($totais['desconto'], 2, ',', '.') . 
                 "\nFrete: R$ " . number_format($totais['frete'], 2, ',', '.') . 
                 "\nTotal: R$ " . number_format($totais['total'], 2, ',', '.') . 
                 "\n\nEndereço: $endereco_completo (CEP: $cep)";
    
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.exemplo.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'seu_email@exemplo.com';
        $mail->Password = 'sua_senha';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        $mail->setFrom('seu_email@exemplo.com', 'Mini ERP');
        $mail->addAddress('cliente@exemplo.com');
        $mail->Subject = "Confirmação do Pedido #$id_pedido";
        $mail->Body = $mensagem;
        
        $mail->send();
    } catch (Exception $e) {
        error_log("Erro ao enviar e-mail: {$mail->ErrorInfo}");
    }
    */
    
    // Limpar carrinho
    $_SESSION['carrinho'] = [];
    
    return ['sucesso' => true, 'mensagem' => 'Pedido finalizado com sucesso!'];
}
?>