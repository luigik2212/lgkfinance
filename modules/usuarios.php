<?php
if (($_SESSION['user']['tipo'] ?? '') !== 'Administrador') {
    echo '<div class="alert danger">Acesso restrito a administradores.</div>';
    return;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $pdo->prepare('INSERT INTO usuarios (nome,email,senha,tipo_usuario,status) VALUES (?,?,?,?,?)')->execute([
            trim($_POST['nome']),
            trim($_POST['email']),
            password_hash($_POST['senha'], PASSWORD_DEFAULT),
            $_POST['tipo_usuario'],
            (int)$_POST['status']
        ]);
    }

    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $senhaSql = '';
        $params = [trim($_POST['nome']), trim($_POST['email']), $_POST['tipo_usuario'], (int)$_POST['status']];

        if (!empty($_POST['senha'])) {
            $senhaSql = ', senha=?';
            $params[] = password_hash($_POST['senha'], PASSWORD_DEFAULT);
        }

        $params[] = $id;
        $pdo->prepare("UPDATE usuarios SET nome=?,email=?,tipo_usuario=?,status=? $senhaSql WHERE id=?")->execute($params);
    }

    if ($action === 'delete') {
        $pdo->prepare('DELETE FROM usuarios WHERE id=? AND id<>?')->execute([(int)($_POST['id'] ?? 0), $_SESSION['user']['id']]);
    }

    flash('success', 'Usuários atualizados.');
    redirect('index.php?module=usuarios');
}

$users = $pdo->query('SELECT * FROM usuarios ORDER BY nome')->fetchAll();
?>

<div class="card">
    <div class="card-header-row">
        <h3>Lista de usuários</h3>
        <button class="btn-primary" type="button" data-modal-target="modal-usuario-create">Adicionar usuário</button>
    </div>
    <div class="table-wrap">
        <table>
            <tr><th>Nome</th><th>Email</th><th>Tipo</th><th>Status</th><th>Ações</th></tr>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= esc($u['nome']) ?></td>
                    <td><?= esc($u['email']) ?></td>
                    <td><?= esc($u['tipo_usuario']) ?></td>
                    <td><?= $u['status'] ? 'Ativo' : 'Inativo' ?></td>
                    <td class="actions">
                        <button class="btn-secondary" type="button" data-modal-target="modal-usuario-edit-<?= $u['id'] ?>">Editar</button>
                        <form method="post" onsubmit="return confirm('Excluir usuário?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <button class="btn-danger" type="submit" <?= $u['id'] == $_SESSION['user']['id'] ? 'disabled' : '' ?>>Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<div class="modal" id="modal-usuario-create" aria-hidden="true">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Adicionar usuário</h3>
            <button class="modal-close" type="button" data-modal-close>&times;</button>
        </div>
        <form method="post" class="grid-form">
            <input type="hidden" name="action" value="create">
            <input name="nome" placeholder="Nome" required>
            <input type="email" name="email" placeholder="E-mail" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <select name="tipo_usuario"><option>Administrador</option><option>Usuário</option></select>
            <select name="status"><option value="1">Ativo</option><option value="0">Inativo</option></select>
            <div class="modal-actions">
                <button class="btn-secondary" type="button" data-modal-close>Cancelar</button>
                <button class="btn-primary" type="submit">Salvar</button>
            </div>
        </form>
    </div>
</div>

<?php foreach ($users as $u): ?>
    <div class="modal" id="modal-usuario-edit-<?= $u['id'] ?>" aria-hidden="true">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Editar usuário</h3>
                <button class="modal-close" type="button" data-modal-close>&times;</button>
            </div>
            <form method="post" class="grid-form">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                <input name="nome" value="<?= esc($u['nome']) ?>" required>
                <input type="email" name="email" value="<?= esc($u['email']) ?>" required>
                <input name="senha" placeholder="Nova senha (opcional)">
                <select name="tipo_usuario"><option <?= $u['tipo_usuario'] === 'Administrador' ? 'selected' : '' ?>>Administrador</option><option <?= $u['tipo_usuario'] === 'Usuário' ? 'selected' : '' ?>>Usuário</option></select>
                <select name="status"><option value="1" <?= $u['status'] ? 'selected' : '' ?>>Ativo</option><option value="0" <?= !$u['status'] ? 'selected' : '' ?>>Inativo</option></select>
                <div class="modal-actions">
                    <button class="btn-secondary" type="button" data-modal-close>Cancelar</button>
                    <button class="btn-primary" type="submit">Salvar alterações</button>
                </div>
            </form>
        </div>
    </div>
<?php endforeach; ?>
