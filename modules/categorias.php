<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $nome = trim($_POST['nome'] ?? '');
    if ($action === 'create' && $nome !== '') {
        $stmt = $pdo->prepare('INSERT INTO categorias (nome) VALUES (?)');
        $stmt->execute([$nome]);
        logAction($pdo, $_SESSION['user']['id'], 'CREATE', 'categorias', (int)$pdo->lastInsertId(), "Categoria {$nome} criada");
    }
    if ($action === 'update') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare('UPDATE categorias SET nome=? WHERE id=?');
        $stmt->execute([$nome, $id]);
        logAction($pdo, $_SESSION['user']['id'], 'UPDATE', 'categorias', $id, "Categoria {$nome} atualizada");
    }
    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $pdo->prepare('DELETE FROM categorias WHERE id=?')->execute([$id]);
        logAction($pdo, $_SESSION['user']['id'], 'DELETE', 'categorias', $id, 'Categoria excluída');
    }
    flash('success', 'Operação concluída em categorias.');
    redirect('index.php?module=categorias');
}
$categorias = $pdo->query('SELECT * FROM categorias ORDER BY nome')->fetchAll();
?>
<div class="card">
    <h3>Nova categoria</h3>
    <form method="post" class="inline-form">
        <input type="hidden" name="action" value="create">
        <input name="nome" placeholder="Nome da categoria" required>
        <button class="btn-primary">Salvar</button>
    </form>
</div>
<div class="card">
    <h3>Lista de categorias</h3>
    <div class="table-wrap"><table><tr><th>Nome</th><th>Ações</th></tr>
    <?php foreach ($categorias as $c): ?>
        <tr>
            <td><?= esc($c['nome']) ?></td>
            <td>
                <form method="post" class="inline-form">
                    <input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= $c['id'] ?>">
                    <input name="nome" value="<?= esc($c['nome']) ?>" required>
                    <button class="btn-secondary">Atualizar</button>
                </form>
                <form method="post" onsubmit="return confirm('Excluir categoria?')">
                    <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $c['id'] ?>">
                    <button class="btn-danger">Excluir</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?></table></div>
</div>
