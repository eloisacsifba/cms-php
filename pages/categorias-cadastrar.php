<?php
require_once '../includes/auth.php';
require_once '../config/conexao.php';

exigirAdmin();

$dados = [
    'nome' => '',
    'descricao' => '',
    'status' => 1,
];

$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfValidar();

    $dados['nome'] = trim(post('nome'));
    $dados['descricao'] = trim(post('descricao'));
    $dados['status'] = isset($_POST['status']) ? 1 : 0;

    // Validação do nome.
    if ($dados['nome'] === '') {
        $erros['nome'] = 'Informe o nome da categoria.';
    } elseif (mb_strlen($dados['nome']) > 100) {
        $erros['nome'] = 'O nome pode ter no máximo 100 caracteres.';
    } else {
        $sql = $pdo->prepare(
            'SELECT COUNT(*) FROM categorias WHERE nome = ?'
        );
        $sql->execute([$dados['nome']]);

        if ((int) $sql->fetchColumn() > 0) {
            $erros['nome'] = 'Já existe uma categoria com esse nome.';
        }
    }

    // Validação da descrição.
    if (mb_strlen($dados['descricao']) > 255) {
        $erros['descricao'] = 'A descrição pode ter no máximo 255 caracteres.';
    }

    // Cadastro no banco.
    if (!$erros) {
        $sql = $pdo->prepare(
            'INSERT INTO categorias (nome, descricao, status)
             VALUES (:nome, :descricao, :status)'
        );

        $sql->execute([
            ':nome' => $dados['nome'],
            ':descricao' => $dados['descricao'] !== ''
                ? $dados['descricao']
                : null,
            ':status' => $dados['status'],
        ]);

        definirMensagem('Categoria cadastrada com sucesso!');
        redirecionar('categorias.php');
    }
}

$titulo = 'Nova categoria';
$menuAtivo = 'categorias';

require_once '../includes/header.php';
?>

<main class="content">

    <div class="page-header">
        <div>
            <ol class="breadcrumb">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="categorias.php">Categorias</a></li>
                <li>Cadastrar</li>
            </ol>

            <h1>Nova categoria</h1>
            <p>Cadastre uma categoria para organizar os produtos da Doce Tiffany.</p>
        </div>

        <div class="page-header__actions">
            <a href="categorias.php" class="btn btn-light">
                <i class="bi bi-arrow-left"></i>
                Voltar para categorias
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

    <form method="post" class="categoria-form">

        <?= csrfCampo() ?>

        <section class="card categoria-form__card">

            <div class="categoria-form__header">
                <h2>Dados da categoria</h2>
                <p>Preencha as informações para criar uma nova categoria.</p>
            </div>

            <div class="categoria-form__body">

                <div class="categoria-form__field">
                    <label for="nome" class="form-label">
                        Nome da categoria <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="nome"
                        name="nome"
                        class="form-control"
                        value="<?= e($dados['nome']) ?>"
                        maxlength="100"
                        placeholder="Ex.: Doces"
                        required
                    >

                    <?php if (isset($erros['nome'])): ?>
                        <small class="categoria-form__error">
                            <?= e($erros['nome']) ?>
                        </small>
                    <?php endif; ?>
                </div>

                <div class="categoria-form__field">
                    <label for="descricao" class="form-label">
                        Descrição
                    </label>

                    <textarea
                        id="descricao"
                        name="descricao"
                        class="form-control categoria-form__description"
                        maxlength="255"
                        rows="5"
                        placeholder="Descreva os produtos desta categoria."
                    ><?= e($dados['descricao']) ?></textarea>

                    <small>Máximo de 255 caracteres.</small>

                    <?php if (isset($erros['descricao'])): ?>
                        <small class="categoria-form__error">
                            <?= e($erros['descricao']) ?>
                        </small>
                    <?php endif; ?>
                </div>

            </div>
        </section>

        <section class="card categoria-form__card">

            <div class="categoria-form__header">
                <h2>Disponibilidade</h2>
                <p>Defina se a categoria poderá ser utilizada no sistema.</p>
            </div>

            <div class="categoria-form__status-body">
                <label class="categoria-form__check" for="status">
                    <input
                        type="checkbox"
                        name="status"
                        id="status"
                        value="1"
                        <?= $dados['status'] ? 'checked' : '' ?>
                    >

                    <span>
                        <strong>Categoria ativa</strong>
                        <small>
                            Categorias inativas não devem ser oferecidas para novos produtos.
                        </small>
                    </span>
                </label>
            </div>

        </section>

        <div class="categoria-form__actions">
            <a href="categorias.php" class="btn btn-secondary">
                Cancelar
            </a>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i>
                Salvar categoria
            </button>
        </div>

    </form>

</main>

<?php require_once '../includes/footer.php'; ?>