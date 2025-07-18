CREATE DATABASE IF NOT EXISTS mini_erp;
USE mini_erp;

CREATE TABLE IF NOT EXISTS produtos (
    id_produto INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS variacoes (
    id_variacao INT AUTO_INCREMENT PRIMARY KEY,
    id_produto INT NOT NULL,
    cor VARCHAR(7) NOT NULL COMMENT 'Cor em formato hexadecimal, ex: #FF0000 para vermelho',
    tamanho ENUM('PP', 'P', 'M', 'G', 'GG', 'XG') NOT NULL,
    genero ENUM('Masculino', 'Feminino', 'Unissex') NOT NULL,
    tecido ENUM('Algodão', 'Poliéster', 'Malha', 'Jeans') NOT NULL,
    preco DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (id_produto) REFERENCES produtos(id_produto) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS estoque (
    id_estoque INT AUTO_INCREMENT PRIMARY KEY,
    id_variacao INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 0,
    FOREIGN KEY (id_variacao) REFERENCES variacoes(id_variacao) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    subtotal DECIMAL(10, 2) NOT NULL,
    frete DECIMAL(10, 2) NOT NULL,
    cep VARCHAR(8) NOT NULL,
    endereco_completo VARCHAR(255) NOT NULL,
    status ENUM('confirmado', 'enviado', 'entregue') NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS itens_pedido (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_variacao INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido) ON DELETE CASCADE,
    FOREIGN KEY (id_variacao) REFERENCES variacoes(id_variacao) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS cupons (
    id_cupom INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    desconto DECIMAL(10, 2) NOT NULL,
    valor_minimo DECIMAL(10, 2) NOT NULL,
    validade DATE NOT NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE
);