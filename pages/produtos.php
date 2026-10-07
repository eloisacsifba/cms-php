<?php

require_once '../includes/auth.php';
require_once '../config/conexao.php';
require_once '../includes/produtos-funcoes.php';

exigirAdmin();

$sql = $pdo->query(
    'SELECT 
        p.id,
        p.nome,
        p.preco,
        p.imagem,
        p.status,
        p.criado_em,
        c.nome AS categoria
     FROM produtos p
     INNER JOIN categorias c ON c.id = p.categoria_id
     ORDER BY p.id DESC'
);

$produtos = $sql->fetchAll();

$titulo = 'Produtos';
$menuAtivo = 'produtos';

require_once '../includes/header.php';
?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Produtos</h1>
            <p class="text-muted mb-0">
                Gerencie os produtos da Doce Tiffany.
            </p>
        </div>

        <a href="produtos-cadastrar.php" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i>
            Novo produto
        </a>
    </div>

    <?php exibirMensagem(); ?>

    <div class="card shadow-sm">
        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Produto</th>
                            <th>Categoria</th>
                            <th>Preço</th>
                            <th>Status</th>
                            <th>Cadastro</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (!$produtos): ?>

                        <tr>
                            <td colspan="7" class="text-center py-4">
                                Nenhum produto cadastrado.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($produtos as $produto): ?>

                            <tr>

                                <td>
                                    <?= e($produto['id']) ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= e($produto['nome']) ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= e($produto['categoria']) ?>
                                </td>

                                <td>
                                    R$
                                    <?= number_format(
                                        $produto['preco'],
                                        2,
                                        ',',
                                        '.'
                                    ) ?>
                                </td>

                                <td>
                                    <?php if ($produto['status']): ?>
                                        <span class="badge badge-success">
                                            Ativo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-gray">
                                            Inativo
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?= formatarData($produto['criado_em']) ?>
                                </td>

                                <td>

                                    <a
                                        href="produtos-editar.php?id=<?= $produto['id'] ?>"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        Editar
                                    </a>

                                    <a
                                        href="produtos-excluir.php?id=<?= $produto['id'] ?>"
                                        class="btn btn-sm btn-outline-danger"
                                    >
                                        Excluir
                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>