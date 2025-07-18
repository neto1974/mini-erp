<?php
// Incluindo a conexão
require_once '../config.php';

// Função para cadastrar produto
function cadastrar_produto($nome, $variacoes) {
    global $conexao_banco;
    $sql = "INSERT INTO produtos (nome) VALUES (?)";
    $stmt = $conexao_banco->prepare($sql);
    $stmt->bind_param("s", $nome);
    $stmt->execute();
    $id_produto = $conexao_banco->insert_id;
    
    foreach ($variacoes as $variacao) {
        // Validar dados da variação
        if (empty($variacao['cor']) || empty($variacao['tamanho']) || empty($variacao['genero']) || empty($variacao['tecido']) || !isset($variacao['preco']) || !isset($variacao['quantidade_estoque'])) {
            error_log("Erro: Dados de variação incompletos: " . print_r($variacao, true));
            continue;
        }
        
        $sql_variacao = "INSERT INTO variacoes (id_produto, cor, tamanho, genero, tecido, preco) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_variacao = $conexao_banco->prepare($sql_variacao);
        $stmt_variacao->bind_param("issssd", $id_produto, $variacao['cor'], $variacao['tamanho'], $variacao['genero'], $variacao['tecido'], $variacao['preco']);
        $stmt_variacao->execute();
        $id_variacao = $conexao_banco->insert_id;
        
        // Depuração: logar inserção de variação
        error_log("Inserindo variação id_variacao: $id_variacao, cor: {$variacao['cor']}, tamanho: {$variacao['tamanho']}, genero: {$variacao['genero']}, tecido: {$variacao['tecido']}, preco: {$variacao['preco']}");
        
        $quantidade = (int)$variacao['quantidade_estoque'];
        if ($quantidade < 0) {
            error_log("Erro: Quantidade inválida ($quantidade) para id_variacao $id_variacao");
            $quantidade = 0;
        }
        
        $sql_estoque = "INSERT INTO estoque (id_variacao, quantidade) VALUES (?, ?)";
        $stmt_estoque = $conexao_banco->prepare($sql_estoque);
        $stmt_estoque->bind_param("ii", $id_variacao, $quantidade);
        $stmt_estoque->execute();
        
        // Depuração: logar inserção de estoque
        error_log("Inserindo estoque para id_variacao: $id_variacao, quantidade: $quantidade");
    }
    return $id_produto;
}

// Função para listar produtos
function listar_produtos() {
    global $conexao_banco;
    $sql = "SELECT p.id_produto, p.nome FROM produtos p";
    $resultado = $conexao_banco->query($sql);
    $produtos = $resultado->fetch_all(MYSQLI_ASSOC);
    
    foreach ($produtos as &$produto) {
        $sql_variacoes = "SELECT v.id_variacao, v.cor, v.tamanho, v.genero, v.tecido, v.preco, COALESCE(e.quantidade, 0) as quantidade 
                          FROM variacoes v 
                          LEFT JOIN estoque e ON v.id_variacao = e.id_variacao 
                          WHERE v.id_produto = ?";
        $stmt_variacoes = $conexao_banco->prepare($sql_variacoes);
        $stmt_variacoes->bind_param("i", $produto['id_produto']);
        $stmt_variacoes->execute();
        $variacoes = $stmt_variacoes->get_result()->fetch_all(MYSQLI_ASSOC);
        $produto['variacoes'] = $variacoes;
        
        // Depuração: logar variações encontradas
        if (empty($variacoes)) {
            error_log("Nenhuma variação encontrada para o produto id_produto: {$produto['id_produto']}");
        } else {
            error_log("Variações encontradas para id_produto: {$produto['id_produto']}, " . print_r($variacoes, true));
        }
    }
    return $produtos;
}

// Função para obter produto
function obter_produto($id_produto) {
    global $conexao_banco;
    $sql = "SELECT id_produto, nome FROM produtos WHERE id_produto = ?";
    $stmt = $conexao_banco->prepare($sql);
    $stmt->bind_param("i", $id_produto);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Função para obter variações
function obter_variacoes($id_produto) {
    global $conexao_banco;
    $sql = "SELECT v.id_variacao, v.cor, v.tamanho, v.genero, v.tecido, v.preco, COALESCE(e.quantidade, 0) as quantidade 
            FROM variacoes v 
            LEFT JOIN estoque e ON v.id_variacao = e.id_variacao 
            WHERE v.id_produto = ?";
    $stmt = $conexao_banco->prepare($sql);
    $stmt->bind_param("i", $id_produto);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Função para atualizar produto
function atualizar_produto($id_produto, $nome, $variacoes) {
    global $conexao_banco;
    $sql = "UPDATE produtos SET nome = ? WHERE id_produto = ?";
    $stmt = $conexao_banco->prepare($sql);
    $stmt->bind_param("si", $nome, $id_produto);
    $stmt->execute();
    
    // Deletar variações existentes
    $sql_delete_variacoes = "DELETE FROM variacoes WHERE id_produto = ?";
    $stmt_delete_variacoes = $conexao_banco->prepare($sql_delete_variacoes);
    $stmt_delete_variacoes->bind_param("i", $id_produto);
    $stmt_delete_variacoes->execute();
    
    $sql_delete_estoque = "DELETE FROM estoque WHERE id_variacao IN (SELECT id_variacao FROM variacoes WHERE id_produto = ?)";
    $stmt_delete_estoque = $conexao_banco->prepare($sql_delete_estoque);
    $stmt_delete_estoque->bind_param("i", $id_produto);
    $stmt_delete_estoque->execute();
    
    // Inserir novas variações
    foreach ($variacoes as $variacao) {
        // Validar dados da variação
        if (empty($variacao['cor']) || empty($variacao['tamanho']) || empty($variacao['genero']) || empty($variacao['tecido']) || !isset($variacao['preco']) || !isset($variacao['quantidade_estoque'])) {
            error_log("Erro: Dados de variação incompletos: " . print_r($variacao, true));
            continue;
        }
        
        $sql_variacao = "INSERT INTO variacoes (id_produto, cor, tamanho, genero, tecido, preco) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_variacao = $conexao_banco->prepare($sql_variacao);
        $stmt_variacao->bind_param("issssd", $id_produto, $variacao['cor'], $variacao['tamanho'], $variacao['genero'], $variacao['tecido'], $variacao['preco']);
        $stmt_variacao->execute();
        $id_variacao = $conexao_banco->insert_id;
        
        // Depuração: logar inserção de variação
        error_log("Atualizando variação id_variacao: $id_variacao, cor: {$variacao['cor']}, tamanho: {$variacao['tamanho']}, genero: {$variacao['genero']}, tecido: {$variacao['tecido']}, preco: {$variacao['preco']}");
        
        $quantidade = (int)$variacao['quantidade_estoque'];
        if ($quantidade < 0) {
            error_log("Erro: Quantidade inválida ($quantidade) para id_variacao $id_variacao");
            $quantidade = 0;
        }
        
        $sql_estoque = "INSERT INTO estoque (id_variacao, quantidade) VALUES (?, ?)";
        $stmt_estoque = $conexao_banco->prepare($sql_estoque);
        $stmt_estoque->bind_param("ii", $id_variacao, $quantidade);
        $stmt_estoque->execute();
        
        // Depuração: logar inserção de estoque
        error_log("Atualizando estoque para id_variacao: $id_variacao, quantidade: $quantidade");
    }
}
?>