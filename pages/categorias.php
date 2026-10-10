<?php
/*
 * Categorias — listagem com busca, filtros e paginação.
 */

require_once '../includes/auth.php';
require_once '../config/conexao.php';

exigirAdmin();

$porPagina = 8;
$busca = trim(get('busca'));
$status = get('status');
$pagina = max(1, (int) get('pagina'));

$condicoes = [];
$parametros = [];

if ($busca !== '') {
    $condicoes[] = 'nome LIKE :busca';
    $parametros[':busca'] = '%' . addcslashes($busca, '%_\\') . '%';
}

if ($status === 'ativo' || $status === 'inativo') {
    $condicoes[] = 'status = :status';
    $parametros[':status'] = $status === 'ativo' ? 1 : 0;
}

$where = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

$sql = $pdo->prepare("SELECT COUNT(*) FROM categorias $where");
$sql->execute($parametros);
$total = (int) $sql->fetchColumn();

$totalPaginas = max(1, (int) ceil($total / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

$sql = $pdo->prepare(
    "SELECT id, nome, descricao, status, criado_em
     FROM categorias
     $where
     ORDER BY nome
     LIMIT :limite OFFSET :offset"
);

foreach ($parametros as $nome => $valor) {
    $sql->bindValue($nome, $valor);
}

$sql->bindValue(':limite', $porPagina, PDO::PARAM_INT);
$sql->bindValue(':offset', $offset, PDO::PARAM_INT);
$sql->execute();

$categorias = $sql->fetchAll();
$temFiltro = $busca !== '' || $status !== '';

function linkPaginaCategoria(int $numero): string
{
    $parametros = array_filter([
        'busca' => get('busca'),
        'status' => get('status'),
    ], 'strlen');

    $parametros['pagina'] = $numero;

    return 'categorias.php?' . e(http_build_query($parametros));
}

$titulo = 'Categorias';
$menuAtivo = 'categorias';

require_once '../includes/header.php';
?>

<main class="content">
    <div class="page-header">
        <div>
            <ol class="breadcrumb">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li>Categorias</li>
            </ol>
            <h1>Categorias</h1>
            <p>Organize os produtos da Doce Tiffany por categoria.</p>
        </div>

        <div class="page-header__actions">
            <a href="categorias-cadastrar.php" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nova categoria
            </a>
        </div>
    </div>

    <?php exibirMensagem(); ?>

    <section class="card">
        <form class="filters" action="categorias.php" method="get">
            <div class="input-icon">
                <i class="bi bi-search"></i>
                <input
                    type="search"
                    class="form-control"
                    name="busca"
                    placeholder="Buscar categoria..."
                    aria-label="Buscar categoria"
                    value="<?= e($busca) ?>"
                >
            </div>

            <select class="form-select" name="status" aria-label="Filtrar por status">
                <option value="">Todos os status</option>
                <option value="ativo" <?= $status === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                <option value="inativo" <?= $status === 'inativo' ? 'selected' : '' ?>>Inativo</option>
            </select>

            <button type="submit" class="btn btn-light">
                <i class="bi bi-funnel"></i> Filtrar
            </button>

            <?php if ($temFiltro): ?>
                <a href="categorias.php" class="btn btn-ghost">
                    <i class="bi bi-x-circle"></i> Limpar
                </a>
            <?php endif; ?>
        </form>

        <div class="table-responsive">
            <table class="table table--stack">
                <thead>
                    <tr>
                        <th>Categoria</th>
                        <th>Descrição</th>
                        <th>Status</th>
                        <th>Cadastro</th>
                        <th class="col-actions">Ações</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($categorias as $categoria): ?>
                        <tr>
                            <td data-label="Categoria">
                                <strong><?= e($categoria['nome']) ?></strong>
                            </td>

                            <td data-label="Descrição">
                                <?= e($categoria['descricao'] ?: 'Sem descrição') ?>
                            </td>

                            <td data-label="Status">
                                <?php if ($categoria['status']): ?>
                                    <span class="badge badge-success">Ativa</span>
                                <?php else: ?>
                                    <span class="badge badge-gray">Inativa</span>
                                <?php endif; ?>
                            </td>

                            <td data-label="Cadastro" class="text-muted">
                                <?= e(formatarData($categoria['criado_em'])) ?>
                            </td>

                            <td class="col-actions">
                                <a
                                    href="categorias-editar.php?id=<?= (int) $categoria['id'] ?>"
                                    class="action-btn"
                                    title="Editar categoria"
                                    aria-label="Editar categoria"
                                >
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <a
                                    href="categorias-excluir.php?id=<?= (int) $categoria['id'] ?>"
                                    class="action-btn action-btn--danger"
                                    title="Excluir categoria"
                                    aria-label="Excluir categoria"
                                >
                                    <i class="bi bi-trash3"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$categorias): ?>
                        <tr>
                            <td colspan="5" class="table-empty">
                                <i class="bi bi-search"></i>
                                <?= $temFiltro
                                    ? 'Nenhuma categoria encontrada com esses filtros.'
                                    : 'Nenhuma categoria cadastrada.' ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card__footer">
            <span class="pagination-info">
                Mostrando <strong><?= count($categorias) ?></strong>
                de <strong><?= $total ?></strong>
                <?= $total === 1 ? 'categoria' : 'categorias' ?>
            </span>

            <?php if ($totalPaginas > 1): ?>
                <nav aria-label="Paginação">
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                            <?php if ($i === $pagina): ?>
                                <li class="is-active">
                                    <span aria-current="page"><?= $i ?></span>
                                </li>
                            <?php else: ?>
                                <li>
                                    <a href="<?= linkPaginaCategoria($i) ?>"><?= $i ?></a>
                                </li>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once '../includes/footer.php'; ?>