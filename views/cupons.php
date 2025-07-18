<?php
// Incluindo dependências
require_once '../models/modelo_cupom.php';
require_once '../controllers/controlador_cupom.php';

// Carregando dados para edição
$cupom_editar = null;
if (isset($_GET['editar'])) {
    $cupom_editar = obter_cupom($_GET['editar']);
}

// Carregando cupons
$cupons = listar_cupons();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Cupons</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 1200px; }
        .card { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <!-- Formulário de cadastro/edição -->
        <div class="card">
            <div class="card-header">
                <h2><?php echo $cupom_editar ? 'Editar Cupom' : 'Cadastrar Cupom'; ?></h2>
            </div>
            <div class="card-body">
                <form method="POST" action="../controllers/controlador_cupom.php">
                    <input type="hidden" name="acao" value="<?php echo $cupom_editar ? 'atualizar' : 'cadastrar'; ?>">
                    <?php if ($cupom_editar): ?>
                        <input type="hidden" name="id_cupom" value="<?php echo $cupom_editar['id_cupom']; ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="codigo" class="form-label">Código</label>
                        <input type="text" class="form-control" id="codigo" name="codigo" value="<?php echo $cupom_editar ? $cupom_editar['codigo'] : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="desconto" class="form-label">Desconto (R$)</label>
                        <input type="number" step="0.01" class="form-control" id="desconto" name="desconto" value="<?php echo $cupom_editar ? $cupom_editar['desconto'] : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="valor_minimo" class="form-label">Valor Mínimo (R$)</label>
                        <input type="number" step="0.01" class="form-control" id="valor_minimo" name="valor_minimo" value="<?php echo $cupom_editar ? $cupom_editar['valor_minimo'] : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="validade" class="form-label">Validade</label>
                        <input type="date" class="form-control" id="validade" name="validade" value="<?php echo $cupom_editar ? $cupom_editar['validade'] : ''; ?>" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="ativo" name="ativo" <?php echo $cupom_editar && $cupom_editar['ativo'] ? 'checked' : ''; ?>>
                        <label for="ativo" class="form-check-label">Ativo</label>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo $cupom_editar ? 'Atualizar' : 'Cadastrar'; ?></button>
                </form>
            </div>
        </div>

        <!-- Listagem de cupons -->
        <div class="card">
            <div class="card-header">
                <h2>Cupons</h2>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Desconto</th>
                            <th>Valor Mínimo</th>
                            <th>Validade</th>
                            <th>Ativo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cupons as $cupom): ?>
                            <tr>
                                <td><?php echo $cupom['codigo']; ?></td>
                                <td>R$ <?php echo number_format($cupom['desconto'], 2, ',', '.'); ?></td>
                                <td>R$ <?php echo number_format($cupom['valor_minimo'], 2, ',', '.'); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($cupom['validade'])); ?></td>
                                <td><?php echo $cupom['ativo'] ? 'Sim' : 'Não'; ?></td>
                                <td>
                                    <a href="?editar=<?php echo $cupom['id_cupom']; ?>" class="btn btn-sm btn-warning">Editar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <a href="index.php" class="btn btn-secondary">Voltar</a>
    </div>
</body>
</html>