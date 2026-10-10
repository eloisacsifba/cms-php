<?php

require_once '../includes/auth.php';
require_once '../config/conexao.php';
require_once '../includes/produtos-funcoes.php';

exigirAdmin();

/* Verifica se o ID informado é válido. */
$id = (int) get('id');

if ($id <= 0) {
    definirMensagem('Produto não encontrado.', 'danger');
    redirecionar('produtos.php');
}

/* Busca o produto no banco de dados. */
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

/* Preenche o formulário com os dados atuais. */
$dados = [
    'nome' => $produto['nome'],
    'descricao' => $produto['descricao'],
    'preco' => $produto['preco'],
    'categoria_id' => $produto['categoria_id'],
    'status' => $produto['status'],
];

$erros = [];

/* Busca as categorias ativas. */
$sqlCategorias = $pdo->query(
    'SELECT id, nome
     FROM categorias
     WHERE status = 1
     ORDER BY nome'
);

$categorias = $sqlCategorias->fetchAll();

/* Processa o envio do formulário. */
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

<main class="content">

    <div class="page-header">
        <div>
            <ol class="breadcrumb">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="produtos.php">Produtos</a></li>
                <li>Editar produto</li>
            </ol>

            <h1>Editar produto</h1>
            <p>Altere as informações do produto da Doce Tiffany.</p>
        </div>

        <div class="page-header__actions">
            <a href="produtos.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Voltar
            </a>
        </div>
    </div>

    <?php exibirMensagem(); ?>

    <?php if ($erros): ?>
        <div class="alert alert-danger">
            <strong>Verifique os campos abaixo:</strong>
            <ul>
                <?php foreach ($erros as $erro): ?>
                    <li><?= e($erro) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="produto-form">

        <?= csrfCampo() ?>

        <section class="card produto-form__card">

            <div class="produto-form__header">
                <div>
                    <h2>Dados do produto</h2>
                    <p>Informações principais do item cadastrado.</p>
                </div>
            </div>

            <div class="produto-form__body">

                <div class="produto-form__field">
                    <label for="nome" class="form-label">
                        Nome do produto <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="nome"
                        name="nome"
                        class="form-control"
                        value="<?= e($dados['nome']) ?>"
                        maxlength="150"
                        required
                    >
                </div>

                <div class="produto-form__field">
                    <label for="preco" class="form-label">
                        Preço (R$) <span>*</span>
                    </label>

                    <input
                        type="number"
                        id="preco"
                        name="preco"
                        class="form-control"
                        value="<?= e($dados['preco']) ?>"
                        min="0"
                        step="0.01"
                        required
                    >

                    <small>Informe o preço em reais.</small>
                </div>

                <div class="produto-form__field">
                    <label for="categoria_id" class="form-label">
                        Categoria <span>*</span>
                    </label>

                    <select
                        id="categoria_id"
                        name="categoria_id"
                        class="form-control"
                        required
                    >
                        <option value="">Selecione uma categoria</option>

                        <?php foreach ($categorias as $categoria): ?>
                            <option
                                value="<?= (int) $categoria['id'] ?>"
                                <?= (int) $dados['categoria_id'] === (int) $categoria['id'] ? 'selected' : '' ?>
                            >
                                <?= e($categoria['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="produto-form__field">
                    <label for="descricao" class="form-label">
                        Descrição
                    </label>

                    <textarea
                        id="descricao"
                        name="descricao"
                        class="form-control produto-form__description"
                        rows="5"
                    ><?= e($dados['descricao']) ?></textarea>

                    <small>Descreva as características do produto.</small>
                </div>

            </div>
        </section>

        <section class="card produto-form__card produto-form__status">

            <div class="produto-form__header">
                <div>
                    <h2>Disponibilidade</h2>
                    <p>Controle se o produto está ativo no sistema.</p>
                </div>
            </div>

            <div class="produto-form__status-body">
                <label class="produto-form__check" for="status">
                    <input
                        type="checkbox"
                        name="status"
                        id="status"
                        value="1"
                        <?= $dados['status'] ? 'checked' : '' ?>
                    >

                    <span>
                        <strong>Produto ativo</strong>
                        <small>
                            O produto está habilitado no cadastro.
                        </small>
                    </span>
                </label>
            </div>

        </section>

        <div class="produto-form__actions">
            <a href="produtos.php" class="btn btn-secondary">
                Cancelar
            </a>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i>
                Salvar alterações
            </button>
        </div>

    </form>

</main>

<?php require_once '../includes/footer.php'; ?>
```