<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $nome = trim($_POST['nome'] ?? '');

    if ($action === 'create' && $nome !== '') {
        $stmt = $pdo->prepare('INSERT INTO categorias (nome) VALUES (?)');
        $stmt->execute([$nome]);
        logAction($pdo, $_SESSION['user']['id'], 'CREATE', 'categorias', (int)$pdo->lastInsertId(), "Categoria {$nome} criada");
    }

    if ($action === 'update' && $nome !== '') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('UPDATE categorias SET nome=? WHERE id=?');
        $stmt->execute([$nome, $id]);
        logAction($pdo, $_SESSION['user']['id'], 'UPDATE', 'categorias', $id, "Categoria {$nome} atualizada");
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM categorias WHERE id=?')->execute([$id]);
        logAction($pdo, $_SESSION['user']['id'], 'DELETE', 'categorias', $id, 'Categoria excluída');
    }

    flash('success', 'Operação concluída em categorias.');
    redirect('index.php?module=categorias');
}

$categorias = $pdo->query('SELECT * FROM categorias ORDER BY nome')->fetchAll();
?>

<div class="card">
    <div class="card-header-row">
        <h3>Lista de categorias</h3>
        <button class="btn-primary" type="button" data-modal-target="modal-categoria-create">Adicionar nova categoria</button>
    </div>
    <div class="table-wrap">
        <table>
            <tr><th>Nome</th><th>Ações</th></tr>
            <?php foreach ($categorias as $c): ?>
                <tr>
                    <td><?= esc($c['nome']) ?></td>
                    <td class="actions">
                        <button class="btn-secondary" type="button" data-modal-target="modal-categoria-edit-<?= $c['id'] ?>">Editar</button>
                        <form method="post" onsubmit="return confirm('Excluir categoria?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <button class="btn-danger" type="submit">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<div class="modal" id="modal-categoria-create" aria-hidden="true">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Adicionar nova categoria</h3>
            <button class="modal-close" type="button" data-modal-close>&times;</button>
        </div>
        <form method="post" class="grid-form">
            <input type="hidden" name="action" value="create">
            <input name="nome" placeholder="Nome da categoria" required>
            <div class="modal-actions">
                <button class="btn-secondary" type="button" data-modal-close>Cancelar</button>
                <button class="btn-primary" type="submit">Salvar</button>
            </div>
        </form>
    </div>
</div>

<?php foreach ($categorias as $c): ?>
    <div class="modal" id="modal-categoria-edit-<?= $c['id'] ?>" aria-hidden="true">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Editar categoria</h3>
                <button class="modal-close" type="button" data-modal-close>&times;</button>
            </div>
            <form method="post" class="grid-form">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                <input name="nome" value="<?= esc($c['nome']) ?>" required>
                <div class="modal-actions">
                    <button class="btn-secondary" type="button" data-modal-close>Cancelar</button>
                    <button class="btn-primary" type="submit">Salvar alterações</button>
                </div>
            </form>
        </div>
    </div>
<?php endforeach; ?>
