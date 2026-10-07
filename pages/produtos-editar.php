<?php

require_once '../includes/auth.php';
require_once '../config/conexao.php';
require_once '../includes/produtos-funcoes.php';

exigirAdmin();

/* Verificar se foi informado um ID */
$id = (int) get('id');

if ($id <= 0) {
    definirMensagem('Produto não encontrado.', 'danger');
    redirecionar('produtos.php');
}

/* Buscar o produto */
$sql = $pdo->prepare(
    'SELECT *
     FROM produtos
     WHERE id = ?'
);

$sql->execute([$id]);

$produto = $sql->fetch();

if (!$produto) {
    definirMensagem('Produto não encontrado.', 'danger');
    redirecionar('produtos.php');
}

/* Dados que aparecem no formulário */
$dados = [
    'nome' => $produto['nome'],
    'descricao' => $produto['descricao'],
    'preco' => $produto['preco'],
    'categoria_id' => $produto['categoria_id'],
    'status' => $produto['status'],
];

$erros = [];

/* Buscar categorias */
$sqlCategorias = $pdo->query(
    'SELECT id, nome
     FROM categorias
     WHERE status = 1
     ORDER BY nome'
);

$categorias = $sqlCategorias->fetchAll();

/* Atualizar produto */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    csrfValidar();

    $dados = lerFormularioProduto();

    $erros = validarProduto($pdo, $dados);

    if (!$erros) {

        $sql = $pdo->prepare(
            'UPDATE produtos
             SET nome = :nome,
                 descricao = :descricao,
                 preco = :preco,
                 categoria_id = :categoria_id,
                 status = :status
             WHERE id = :id'
        );

        $sql->execute([
            ':nome' => $dados['nome'],
            ':descricao' => $dados['descricao'],
            ':preco' => $dados['preco'],
            ':categoria_id' => $dados['categoria_id'],
            ':status' => $dados['status'],
            ':id' => $id,
        ]);

        definirMensagem('Produto atualizado com sucesso!');

        redirecionar('produtos.php');
    }
}

$titulo = 'Editar produto';
$menuAtivo = 'produtos';

require_once '../includes/header.php';
?>

<div class="container-fluid">

    <div class="mb-4">

        <h1 class="h3 mb-1">
            Editar produto
        </h1>

        <p class="text-muted mb-0">
            Altere os dados do produto da Doce Tiffany.
        </p>

    </div>

    <?php exibirMensagem(); ?>

    <?php if ($erros): ?>

        <div class="alert alert-danger">

            <strong>Verifique os campos abaixo:</strong>

            <ul class="mb-0 mt-2">

                <?php foreach ($erros as $erro): ?>

                    <li><?= e($erro) ?></li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>


    <div class="card shadow-sm">

        <div class="card-body">

            <form method="POST">

                <?= csrfCampo() ?>

                <div class="row">

                    <!-- NOME -->
                    <div class="col-md-8 mb-3">

                        <label class="form-label">
                            Nome do produto
                        </label>

                        <input
                            type="text"
                            name="nome"
                            class="form-control"
                            value="<?= e($dados['nome']) ?>"
                            required
                        >

                    </div>


                    <!-- PREÇO -->
                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            Preço
                        </label>

                        <input
                            type="number"
                            name="preco"
                            class="form-control"
                            value="<?= e($dados['preco']) ?>"
                            min="0"
                            step="0.01"
                            required
                        >

                    </div>


                    <!-- CATEGORIA -->
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Categoria
                        </label>

                        <select
                            name="categoria_id"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Selecione uma categoria
                            </option>

                            <?php foreach ($categorias as $categoria): ?>

                                <option
                                    value="<?= $categoria['id'] ?>"
                                    <?= (int) $dados['categoria_id'] === (int) $categoria['id'] ? 'selected' : '' ?>
                                >
                                    <?= e($categoria['nome']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- STATUS -->
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Status
                        </label>

                        <div class="form-check mt-2">

                            <input
                                type="checkbox"
                                name="status"
                                class="form-check-input"
                                id="status"
                                <?= $dados['status'] ? 'checked' : '' ?>
                            >

                            <label
                                class="form-check-label"
                                for="status"
                            >
                                Produto ativo
                            </label>

                        </div>

                    </div>


                    <!-- DESCRIÇÃO -->
                    <div class="col-12 mb-4">

                        <label class="form-label">
                            Descrição
                        </label>

                        <textarea
                            name="descricao"
                            class="form-control"
                            rows="5"
                        ><?= e($dados['descricao']) ?></textarea>

                    </div>

                </div>


                <div class="d-flex gap-2">

                    <a
                        href="produtos.php"
                        class="btn btn-secondary"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Salvar alterações
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?php require_once '../includes/footer.php'; ?>