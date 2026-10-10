```php
<?php

require_once '../includes/auth.php';
require_once '../config/conexao.php';
require_once '../includes/produtos-funcoes.php';

exigirAdmin();

/* =========================================================
   1. RECEBE OS FILTROS
   ========================================================= */

$busca = trim(get('busca'));
$categoriaId = get('categoria');
$status = get('status');

/* =========================================================
   2. MONTA AS CONDIÇÕES DA CONSULTA
   ========================================================= */

$condicoes = [];
$parametros = [];

if ($busca !== '') {
    $condicoes[] = 'p.nome LIKE :busca';
    $parametros[':busca'] = '%' . $busca . '%';
}

if (
    $categoriaId !== ''
    && ctype_digit($categoriaId)
    && (int) $categoriaId > 0
) {
    $condicoes[] = 'p.categoria_id = :categoria';
    $parametros[':categoria'] = (int) $categoriaId;
}

if ($status === 'ativo' || $status === 'inativo') {
    $condicoes[] = 'p.status = :status';
    $parametros[':status'] = $status === 'ativo' ? 1 : 0;
}

$where = $condicoes
    ? 'WHERE ' . implode(' AND ', $condicoes)
    : '';

/* =========================================================
   3. BUSCA OS PRODUTOS NO BANCO
   ========================================================= */

$sql = $pdo->prepare(
    "SELECT
        p.id,
        p.nome,
        p.preco,
        p.imagem,
        p.status,
        p.criado_em,
        c.nome AS categoria
     FROM produtos p
     INNER JOIN categorias c ON c.id = p.categoria_id
     $where
     ORDER BY p.id DESC"
);

$sql->execute($parametros);
$produtos = $sql->fetchAll();

/* =========================================================
   4. BUSCA AS CATEGORIAS PARA O FILTRO
   ========================================================= */

$sqlCategorias = $pdo->query(
    'SELECT id, nome FROM categorias ORDER BY nome'
);

$categorias = $sqlCategorias->fetchAll();

/* =========================================================
   5. CONFIGURA A PÁGINA
   ========================================================= */

$temFiltro = $busca !== ''
    || $categoriaId !== ''
    || $status !== '';

$titulo = 'Produtos';
$menuAtivo = 'produtos';

require_once '../includes/header.php';
?>

<main class="content">

    <!-- Cabeçalho da página -->
    <div class="page-header">

        <div>
            <ol class="breadcrumb">
                <li>
                    <a href="dashboard.php">Dashboard</a>
                </li>
                <li>Produtos</li>
            </ol>

            <h1>Produtos</h1>

            <p>Gerencie os produtos da Doce Tiffany.</p>
        </div>

        <div class="page-header__actions">
            <a href="produtos-cadastrar.php" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i>
                Novo produto
            </a>
        </div>

    </div>

    <?php exibirMensagem(); ?>

    <!-- Cartão principal -->
    <section class="card">

        <!-- Formulário de busca e filtros -->
        <form
            class="filters"
            id="formFiltrosProdutos"
            action="produtos.php"
            method="get"
        >

            <div class="input-icon">
                <i class="bi bi-search"></i>

                <input
                    type="search"
                    class="form-control"
                    id="busca"
                    name="busca"
                    placeholder="Buscar produto pelo nome..."
                    aria-label="Buscar produto pelo nome"
                    value="<?= e($busca) ?>"
                >
            </div>

            <select
                class="form-select"
                id="filtroCategoria"
                name="categoria"
                aria-label="Filtrar por categoria"
            >
                <option value="">Todas as categorias</option>

                <?php foreach ($categorias as $categoria): ?>
                    <option
                        value="<?= (int) $categoria['id'] ?>"
                        <?= (
                            $categoriaId === (string) $categoria['id']
                        ) ? 'selected' : '' ?>
                    >
                        <?= e($categoria['nome']) ?>
                    </option>
                <?php endforeach; ?>

            </select>

            <select
                class="form-select"
                id="filtroStatus"
                name="status"
                aria-label="Filtrar por status"
            >
                <option value="">Todos os status</option>

                <option
                    value="ativo"
                    <?= $status === 'ativo' ? 'selected' : '' ?>
                >
                    Ativos
                </option>

                <option
                    value="inativo"
                    <?= $status === 'inativo' ? 'selected' : '' ?>
                >
                    Inativos
                </option>
            </select>

            <button type="submit" class="btn btn-light">
                <i class="bi bi-funnel"></i>
                Filtrar
            </button>

            <?php if ($temFiltro): ?>
                <a href="produtos.php" class="btn btn-ghost">
                    <i class="bi bi-x-circle"></i>
                    <span class="filters__clear-text">Limpar</span>
                </a>
            <?php endif; ?>

        </form>

        <!-- Tabela de produtos -->
        <div class="table-responsive">

            <table class="table table--stack">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Produto</th>
                        <th>Categoria</th>
                        <th>Preço</th>
                        <th>Status</th>
                        <th>Cadastro</th>
                        <th class="col-actions">Ações</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (!$produtos): ?>

                        <tr>
                            <td colspan="7" class="table-empty">
                                <i class="bi bi-search"></i>

                                <?= $temFiltro
                                    ? 'Nenhum produto encontrado com os filtros informados.'
                                    : 'Nenhum produto cadastrado.' ?>
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($produtos as $produto): ?>

                            <tr>

                                <td data-label="ID">
                                    <?= (int) $produto['id'] ?>
                                </td>

                                <td data-label="Produto">
                                    <span class="user-cell__name">
                                        <?= e($produto['nome']) ?>
                                    </span>
                                </td>

                                <td
                                    data-label="Categoria"
                                    class="text-muted"
                                >
                                    <?= e($produto['categoria']) ?>
                                </td>

                                <td data-label="Preço">
                                    R$ <?= number_format(
                                        (float) $produto['preco'],
                                        2,
                                        ',',
                                        '.'
                                    ) ?>
                                </td>

                                <td data-label="Status">

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

                                <td
                                    data-label="Cadastro"
                                    class="text-muted"
                                >
                                    <?= formatarData($produto['criado_em']) ?>
                                </td>

                                <td class="col-actions">

                                    <a
                                        href="produtos-editar.php?id=<?= (int) $produto['id'] ?>"
                                        class="action-btn"
                                        title="Editar"
                                        aria-label="Editar <?= e($produto['nome']) ?>"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <a
                                        href="produtos-excluir.php?id=<?= (int) $produto['id'] ?>"
                                        class="action-btn action-btn--danger"
                                        title="Excluir"
                                        aria-label="Excluir <?= e($produto['nome']) ?>"
                                    >
                                        <i class="bi bi-trash3"></i>
                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

        <!-- Rodapé da tabela -->
        <div class="card__footer">

            <span class="pagination-info">
                Mostrando
                <strong><?= count($produtos) ?></strong>
                <?= count($produtos) === 1 ? 'produto' : 'produtos' ?>
            </span>

        </div>

    </section>

</main>

<?php require_once '../includes/footer.php'; ?>
```