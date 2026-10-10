```php
<?php
/* ==========================================================================
   Exclusão de produtos
   ========================================================================== */

require_once '../includes/auth.php';
require_once '../config/conexao.php';
require_once '../includes/produtos-funcoes.php';

exigirAdmin();

/* Verifica o ID recebido pela URL. */
$id = (int) get('id');

if ($id <= 0) {
    definirMensagem('Produto não encontrado.', 'danger');
    redirecionar('produtos.php');
}

/* Busca o produto no banco de dados. */
$sql = $pdo->prepare(
    'SELECT id, nome, preco, categoria_id, status
     FROM produtos
     WHERE id = ?'
);
$sql->execute([$id]);

$produto = $sql->fetch();

if (!$produto) {
    definirMensagem('Produto não encontrado.', 'danger');
    redirecionar('produtos.php');
}

/* Processa a confirmação da exclusão. */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    csrfValidar();

    $idConfirmado = (int) post('id');

    if ($idConfirmado !== $id) {
        definirMensagem(
            'Não foi possível confirmar o produto.',
            'danger'
        );
        redirecionar('produtos.php');
    }

    /* Confere novamente se o produto existe. */
    $sql = $pdo->prepare(
        'SELECT id FROM produtos WHERE id = ?'
    );
    $sql->execute([$id]);

    if (!$sql->fetch()) {
        definirMensagem('Este produto já não existe.', 'danger');
        redirecionar('produtos.php');
    }

    /* Exclui o produto usando uma consulta preparada. */
    $sql = $pdo->prepare(
        'DELETE FROM produtos WHERE id = ?'
    );
    $sql->execute([$id]);

    definirMensagem('Produto excluído com sucesso!');
    redirecionar('produtos.php');
}

$titulo = 'Excluir produto';
$menuAtivo = 'produtos';

require_once '../includes/header.php';
?>

<main class="content">

    <div class="page-header">

        <div>
            <ol class="breadcrumb">
                <li>
                    <a href="dashboard.php">Dashboard</a>
                </li>
                <li>
                    <a href="produtos.php">Produtos</a>
                </li>
                <li>Excluir produto</li>
            </ol>

            <h1>Excluir produto</h1>

            <p>
                Confira as informações antes de excluir o produto.
            </p>
        </div>

        <div class="page-header__actions">
            <a href="produtos.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Voltar
            </a>
        </div>

    </div>

    <?php exibirMensagem(); ?>

    <section class="card">

        <div class="card__header">
            <h2>Confirmar exclusão</h2>

            <p>
                Confira os dados do produto selecionado.
            </p>
        </div>

        <div class="card__body">

            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Atenção!</strong>
                Esta ação não poderá ser desfeita.
            </div>

            <dl class="product-details">

                <div>
                    <dt>ID do produto</dt>
                    <dd><?= e($produto['id']) ?></dd>
                </div>

                <div>
                    <dt>Nome do produto</dt>
                    <dd><?= e($produto['nome']) ?></dd>
                </div>

                <div>
                    <dt>Preço</dt>
                    <dd>
                        R$
                        <?= number_format(
                            (float) $produto['preco'],
                            2,
                            ',',
                            '.'
                        ) ?>
                    </dd>
                </div>

                <div>
                    <dt>Status</dt>
                    <dd>
                        <span class="badge <?= $produto['status']
                            ? 'badge-success'
                            : 'badge-gray' ?>">
                            <?= $produto['status'] ? 'Ativo' : 'Inativo' ?>
                        </span>
                    </dd>
                </div>

            </dl>

            <form
                method="post"
                action="produtos-excluir.php?id=<?= (int) $produto['id'] ?>"
            >

                <?= csrfCampo() ?>

                <input
                    type="hidden"
                    name="id"
                    value="<?= (int) $produto['id'] ?>"
                >

                <div class="form-actions">

                    <a href="produtos.php" class="btn btn-secondary">
                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash3"></i>
                        Confirmar exclusão
                    </button>

                </div>

            </form>

        </div>

    </section>

</main>

<?php require_once '../includes/footer.php'; ?>
```