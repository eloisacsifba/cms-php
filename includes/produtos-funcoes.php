<?php

/* ==========================================================================
   Funções do CRUD de produtos
   ========================================================================== */

/** Lê os dados enviados pelo formulário de produto. */
function lerFormularioProduto(): array
{
    return [
        'nome'         => post('nome'),
        'descricao'    => limparHtml(post('descricao')),
        'preco'        => post('preco'),
        'categoria_id' => (int) post('categoria_id'),
        'status'       => isset($_POST['status']) ? 1 : 0,
    ];
}

/** Valida os dados do produto. */
function validarProduto(PDO $pdo, array $dados): array
{
    $erros = [];

    if (mb_strlen($dados['nome']) < 3) {
        $erros['nome'] = 'Informe o nome do produto.';
    } elseif (mb_strlen($dados['nome']) > 150) {
        $erros['nome'] = 'O nome do produto é muito grande.';
    }

    if ($dados['preco'] === '' || !is_numeric($dados['preco'])) {
        $erros['preco'] = 'Informe um preço válido.';
    } elseif ((float) $dados['preco'] < 0) {
        $erros['preco'] = 'O preço não pode ser negativo.';
    }

    if ($dados['categoria_id'] <= 0) {
        $erros['categoria_id'] = 'Selecione uma categoria.';
    } else {
        $sql = $pdo->prepare(
            'SELECT COUNT(*) FROM categorias WHERE id = ? AND status = 1'
        );
        $sql->execute([$dados['categoria_id']]);

        if ((int) $sql->fetchColumn() === 0) {
            $erros['categoria_id'] = 'A categoria selecionada não existe.';
        }
    }

    return $erros;
}

/** Busca o nome de uma categoria. */
function nomeCategoria(PDO $pdo, int $categoriaId): string
{
    $sql = $pdo->prepare(
        'SELECT nome FROM categorias WHERE id = ?'
    );
    $sql->execute([$categoriaId]);

    return $sql->fetchColumn() ?: 'Sem categoria';
}