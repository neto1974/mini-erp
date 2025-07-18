<?php
// Incluindo dependências
require_once '../models/modelo_produto.php';
require_once '../models/modelo_pedido.php';
require_once '../controllers/controlador_produto.php';
require_once '../controllers/controlador_pedido.php';

// Iniciando sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Processando mensagens
$mensagem = isset($_GET['mensagem']) ? urldecode($_GET['mensagem']) : '';

// Carregando dados para edição
$produto_editar = null;
$variacoes_editar = [];
if (isset($_GET['editar'])) {
    $produto_editar = obter_produto($_GET['editar']);
    $variacoes_editar = obter_variacoes($_GET['editar']);
}

// Carregando produtos
$produtos = listar_produtos();

// Carregando carrinho
$carrinho = isset($_SESSION['carrinho']) ? $_SESSION['carrinho'] : [];
$totais = calcular_carrinho();

// Verificando ações para rolagem
$rolar_para_carrinho = isset($_GET['acao']) && $_GET['acao'] === 'comprar' && strpos($mensagem, 'Estoque insuficiente') === false;
$rolar_para_produtos = isset($_GET['acao']) && $_GET['acao'] === 'finalizar' && strpos($mensagem, 'Erro') === false && strpos($mensagem, 'Carrinho vazio') === false;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Mini ERP - Loja de Vestuário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 1200px; }
        .card { margin-bottom: 20px; }
        .variacao-group { border: 1px solid #ccc; padding: 15px; margin-bottom: 15px; position: relative; }
        .remove-variacao { position: absolute; top: 10px; right: 10px; }
        .color-swatch { width: 30px; height: 30px; display: inline-block; border: 1px solid #ccc; margin: 5px; cursor: pointer; }
        .color-swatch.selected { border: 3px solid #000; }
        .color-swatch-label { display: inline-block; margin-left: 10px; vertical-align: top; line-height: 30px; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <?php if ($mensagem): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>

        <!-- Formulário de cadastro/edição -->
        <div class="card">
            <div class="card-header">
                <h2><?php echo $produto_editar ? 'Editar Produto' : 'Cadastrar Produto'; ?></h2>
            </div>
            <div class="card-body">
                <form method="POST" action="../controllers/controlador_produto.php">
                    <input type="hidden" name="acao" value="<?php echo $produto_editar ? 'atualizar' : 'cadastrar'; ?>">
                    <?php if ($produto_editar): ?>
                        <input type="hidden" name="id_produto" value="<?php echo $produto_editar['id_produto']; ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome do Produto</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?php echo $produto_editar ? htmlspecialchars($produto_editar['nome']) : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <h5>Variações</h5>
                        <div id="variacoes_container">
                            <?php if ($variacoes_editar): ?>
                                <?php foreach ($variacoes_editar as $index => $variacao): ?>
                                    <div class="variacao-group" data-index="<?php echo $index; ?>">
                                        <button type="button" class="btn btn-danger btn-sm remove-variacao">Remover</button>
                                        <div class="mb-3">
                                            <label class="form-label">Cor</label>
                                            <div>
                                                <?php
                                                $cores = [
                                                    ['nome' => 'Preto', 'hex' => '#000000'],
                                                    ['nome' => 'Vermelho', 'hex' => '#FF0000'],
                                                    ['nome' => 'Azul', 'hex' => '#0000FF']
                                                ];
                                                foreach ($cores as $cor): ?>
                                                    <div style="display: inline-block;">
                                                        <div class="color-swatch <?php echo $variacao['cor'] === $cor['hex'] ? 'selected' : ''; ?>" style="background-color: <?php echo $cor['hex']; ?>" data-color="<?php echo $cor['hex']; ?>" data-index="<?php echo $index; ?>"></div>
                                                        <label class="color-swatch-label"><?php echo $cor['nome']; ?></label>
                                                        <input type="radio" name="variacoes[<?php echo $index; ?>][cor]" value="<?php echo $cor['hex']; ?>" <?php echo $variacao['cor'] === $cor['hex'] ? 'checked' : ''; ?> style="display: none;" required>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Tamanho</label>
                                            <select class="form-control" name="variacoes[<?php echo $index; ?>][tamanho]" required>
                                                <option value="">Selecione o tamanho</option>
                                                <option value="PP" <?php echo $variacao['tamanho'] === 'PP' ? 'selected' : ''; ?>>PP</option>
                                                <option value="P" <?php echo $variacao['tamanho'] === 'P' ? 'selected' : ''; ?>>P</option>
                                                <option value="M" <?php echo $variacao['tamanho'] === 'M' ? 'selected' : ''; ?>>M</option>
                                                <option value="G" <?php echo $variacao['tamanho'] === 'G' ? 'selected' : ''; ?>>G</option>
                                                <option value="GG" <?php echo $variacao['tamanho'] === 'GG' ? 'selected' : ''; ?>>GG</option>
                                                <option value="XG" <?php echo $variacao['tamanho'] === 'XG' ? 'selected' : ''; ?>>XG</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Gênero</label>
                                            <select class="form-control" name="variacoes[<?php echo $index; ?>][genero]" required>
                                                <option value="">Selecione o gênero</option>
                                                <option value="Masculino" <?php echo $variacao['genero'] === 'Masculino' ? 'selected' : ''; ?>>Masculino</option>
                                                <option value="Feminino" <?php echo $variacao['genero'] === 'Feminino' ? 'selected' : ''; ?>>Feminino</option>
                                                <option value="Unissex" <?php echo $variacao['genero'] === 'Unissex' ? 'selected' : ''; ?>>Unissex</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Tecido</label>
                                            <select class="form-control" name="variacoes[<?php echo $index; ?>][tecido]" required>
                                                <option value="">Selecione o tecido</option>
                                                <option value="Algodão" <?php echo $variacao['tecido'] === 'Algodão' ? 'selected' : ''; ?>>Algodão</option>
                                                <option value="Poliéster" <?php echo $variacao['tecido'] === 'Poliéster' ? 'selected' : ''; ?>>Poliéster</option>
                                                <option value="Malha" <?php echo $variacao['tecido'] === 'Malha' ? 'selected' : ''; ?>>Malha</option>
                                                <option value="Jeans" <?php echo $variacao['tecido'] === 'Jeans' ? 'selected' : ''; ?>>Jeans</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Preço (R$)</label>
                                            <input type="number" step="0.01" class="form-control" name="variacoes[<?php echo $index; ?>][preco]" value="<?php echo $variacao['preco']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Quantidade em Estoque</label>
                                            <input type="number" class="form-control" name="variacoes[<?php echo $index; ?>][quantidade_estoque]" value="<?php echo $variacao['quantidade']; ?>" required>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="variacao-group" data-index="0">
                                    <button type="button" class="btn btn-danger btn-sm remove-variacao">Remover</button>
                                    <div class="mb-3">
                                        <label class="form-label">Cor</label>
                                        <div>
                                            <?php
                                            $cores = [
                                                ['nome' => 'Preto', 'hex' => '#000000'],
                                                ['nome' => 'Vermelho', 'hex' => '#FF0000'],
                                                ['nome' => 'Azul', 'hex' => '#0000FF']
                                            ];
                                            foreach ($cores as $cor): ?>
                                                <div style="display: inline-block;">
                                                    <div class="color-swatch" style="background-color: <?php echo $cor['hex']; ?>" data-color="<?php echo $cor['hex']; ?>" data-index="0"></div>
                                                    <label class="color-swatch-label"><?php echo $cor['nome']; ?></label>
                                                    <input type="radio" name="variacoes[0][cor]" value="<?php echo $cor['hex']; ?>" style="display: none;" required>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tamanho</label>
                                        <select class="form-control" name="variacoes[0][tamanho]" required>
                                            <option value="">Selecione o tamanho</option>
                                            <option value="PP">PP</option>
                                            <option value="P">P</option>
                                            <option value="M">M</option>
                                            <option value="G">G</option>
                                            <option value="GG">GG</option>
                                            <option value="XG">XG</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Gênero</label>
                                        <select class="form-control" name="variacoes[0][genero]" required>
                                            <option value="">Selecione o gênero</option>
                                            <option value="Masculino">Masculino</option>
                                            <option value="Feminino">Feminino</option>
                                            <option value="Unissex">Unissex</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tecido</label>
                                        <select class="form-control" name="variacoes[0][tecido]" required>
                                            <option value="">Selecione o tecido</option>
                                            <option value="Algodão">Algodão</option>
                                            <option value="Poliéster">Poliéster</option>
                                            <option value="Malha">Malha</option>
                                            <option value="Jeans">Jeans</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Preço (R$)</label>
                                        <input type="number" step="0.01" class="form-control" name="variacoes[0][preco]" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Quantidade em Estoque</label>
                                        <input type="number" class="form-control" name="variacoes[0][quantidade_estoque]" required>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <button type="button" id="add_variacao" class="btn btn-secondary mt-2">Adicionar Variação</button>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo $produto_editar ? 'Atualizar' : 'Cadastrar'; ?></button>
                </form>
            </div>
        </div>

        <!-- Listagem de produtos -->
        <div class="card" id="secao_produtos">
            <div class="card-header">
                <h2>Produtos</h2>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Variações</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtos as $produto): ?>
                            <tr>
                                <td><?php echo $produto['id_produto']; ?></td>
                                <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                                <td>
                                    <?php foreach ($produto['variacoes'] as $variacao): ?>
                                        <div>
                                            <span class="color-swatch" style="background-color: <?php echo $variacao['cor']; ?>"></span>
                                            <span>Cor: <?php echo $variacao['cor']; ?>, Tamanho: <?php echo $variacao['tamanho']; ?>, Gênero: <?php echo $variacao['genero']; ?>, Tecido: <?php echo $variacao['tecido']; ?></span>
                                            <br>
                                            Preço: R$ <?php echo number_format($variacao['preco'], 2, ',', '.'); ?> - 
                                            Estoque: <?php echo $variacao['quantidade']; ?>
                                            <form method="POST" action="../controllers/controlador_pedido.php" style="display:inline;">
                                                <input type="hidden" name="acao" value="comprar">
                                                <input type="hidden" name="id_variacao" value="<?php echo $variacao['id_variacao']; ?>">
                                                <input type="hidden" name="quantidade" value="1">
                                                <button type="submit" class="btn btn-sm btn-success">Comprar</button>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>
                                </td>
                                <td>
                                    <a href="?editar=<?php echo $produto['id_produto']; ?>" class="btn btn-sm btn-warning">Editar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Carrinho -->
        <div class="card" id="secao_carrinho">
            <div class="card-header">
                <h2>Carrinho</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($carrinho)): ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Variação</th>
                                <th>Preço</th>
                                <th>Quantidade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($carrinho as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['nome']); ?></td>
                                    <td>
                                        <span class="color-swatch" style="background-color: <?php echo $item['cor']; ?>"></span>
                                        Cor: <?php echo $item['cor']; ?>, Tamanho: <?php echo $item['tamanho']; ?>, Gênero: <?php echo $item['genero']; ?>, Tecido: <?php echo $item['tecido']; ?>
                                    </td>
                                    <td>R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></td>
                                    <td><?php echo $item['quantidade']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p><strong>Subtotal:</strong> R$ <?php echo number_format($totais['subtotal'], 2, ',', '.'); ?></p>
                    <p><strong>Desconto:</strong> R$ <?php echo number_format($totais['desconto'], 2, ',', '.'); ?></p>
                    <p><strong>Frete:</strong> R$ <?php echo number_format($totais['frete'], 2, ',', '.'); ?></p>
                    <p><strong>Total:</strong> R$ <?php echo number_format($totais['total'], 2, ',', '.'); ?></p>
                    
                    <!-- Formulário de finalização -->
                    <form method="POST" action="../controllers/controlador_pedido.php">
                        <input type="hidden" name="acao" value="finalizar">
                        <div class="mb-3">
                            <label for="cep" class="form-label">CEP</label>
                            <input type="text" class="form-control" id="cep" name="cep" required>
                            <button type="button" onclick="validarCep()" class="btn btn-secondary mt-2">Validar CEP</button>
                        </div>
                        <div id="resultado_cep" class="mb-3"></div>
                        <div class="mb-3">
                            <label for="endereco_completo" class="form-label">Endereço Completo</label>
                            <input type="text" class="form-control" id="endereco_completo" name="endereco_completo" required>
                        </div>
                        <div class="mb-3">
                            <label for="codigo_cupom" class="form-label">Código do Cupom</label>
                            <input type="text" class="form-control" id="codigo_cupom" name="codigo_cupom">
                        </div>
                        <button type="submit" class="btn btn-success">Finalizar Pedido</button>
                    </form>
                <?php else: ?>
                    <p>Carrinho vazio.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Link para gerenciar cupons -->
        <a href="cupons.php" class="btn btn-info mt-3">Gerenciar Cupons</a>
    </div>

    <script>
        // Função para validar CEP
        function validarCep() {
            const cep = $('#cep').val().replace(/\D/g, '');
            if (cep.length === 8) {
                $.getJSON(`https://viacep.com.br/ws/${cep}/json/`, function(data) {
                    if (!data.erro) {
                        $('#resultado_cep').html(`
                            <div class="alert alert-success">
                                Endereço: ${data.logradouro}, ${data.bairro}, ${data.localidade} - ${data.uf}
                            </div>
                        `);
                        $('#endereco_completo').val(`${data.logradouro}, ${data.bairro}, ${data.localidade} - ${data.uf}`);
                    } else {
                        $('#resultado_cep').html('<div class="alert alert-danger">CEP inválido.</div>');
                    }
                }).fail(function() {
                    $('#resultado_cep').html('<div class="alert alert-danger">Erro ao buscar dados do CEP.</div>');
                });
            } else {
                $('#resultado_cep').html('<div class="alert alert-danger">CEP deve ter 8 dígitos.</div>');
            }
        }

        // Seleção de cor
        $(document).on('click', '.color-swatch', function() {
            const index = $(this).data('index');
            const color = $(this).data('color');
            $(this).siblings('.color-swatch').removeClass('selected');
            $(this).addClass('selected');
            $(this).siblings(`input[name="variacoes[${index}][cor]"][value="${color}"]`).prop('checked', true);
        });

        // Adicionar nova variação
        let variacaoIndex = <?php echo count($variacoes_editar) ?: 1; ?>;
        $('#add_variacao').click(function() {
            const newVariacao = `
                <div class="variacao-group" data-index="${variacaoIndex}">
                    <button type="button" class="btn btn-danger btn-sm remove-variacao">Remover</button>
                    <div class="mb-3">
                        <label class="form-label">Cor</label>
                        <div>
                            <?php
                            $cores = [
                                ['nome' => 'Preto', 'hex' => '#000000'],
                                ['nome' => 'Vermelho', 'hex' => '#FF0000'],
                                ['nome' => 'Azul', 'hex' => '#0000FF']
                            ];
                            foreach ($cores as $cor): ?>
                                <div style="display: inline-block;">
                                    <div class="color-swatch" style="background-color: <?php echo $cor['hex']; ?>" data-color="<?php echo $cor['hex']; ?>" data-index="${variacaoIndex}"></div>
                                    <label class="color-swatch-label"><?php echo $cor['nome']; ?></label>
                                    <input type="radio" name="variacoes[${variacaoIndex}][cor]" value="<?php echo $cor['hex']; ?>" style="display: none;" required>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tamanho</label>
                        <select class="form-control" name="variacoes[${variacaoIndex}][tamanho]" required>
                            <option value="">Selecione o tamanho</option>
                            <option value="PP">PP</option>
                            <option value="P">P</option>
                            <option value="M">M</option>
                            <option value="G">G</option>
                            <option value="GG">GG</option>
                            <option value="XG">XG</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gênero</label>
                        <select class="form-control" name="variacoes[${variacaoIndex}][genero]" required>
                            <option value="">Selecione o gênero</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Feminino">Feminino</option>
                            <option value="Unissex">Unissex</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tecido</label>
                        <select class="form-control" name="variacoes[${variacaoIndex}][tecido]" required>
                            <option value="">Selecione o tecido</option>
                            <option value="Algodão">Algodão</option>
                            <option value="Poliéster">Poliéster</option>
                            <option value="Malha">Malha</option>
                            <option value="Jeans">Jeans</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Preço (R$)</label>
                        <input type="number" step="0.01" class="form-control" name="variacoes[${variacaoIndex}][preco]" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantidade em Estoque</label>
                        <input type убиtype="number" class="form-control" name="variacoes[${variacaoIndex}][quantidade_estoque]" required>
                    </div>
                </div>
            `;
            $('#variacoes_container').append(newVariacao);
            variacaoIndex++;
        });

        // Remover variação
        $(document).on('click', '.remove-variacao', function() {
            if ($('.variacao-group').length > 1) {
                $(this).closest('.variacao-group').remove();
            }
        });

        // Rolar para seções
        <?php if ($rolar_para_carrinho): ?>
            document.getElementById('secao_carrinho').scrollIntoView({ behavior: 'smooth' });
        <?php endif; ?>
        <?php if ($rolar_para_produtos): ?>
            document.getElementById('secao_produtos').scrollIntoView({ behavior: 'smooth' });
        <?php endif; ?>
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>