<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $stmt = $pdo->prepare('INSERT INTO responsaveis (nome, contato) VALUES (?,?)');
        $stmt->execute([trim($_POST['nome']), trim($_POST['contato'])]);
    }
    if ($action === 'update') {
        $stmt = $pdo->prepare('UPDATE responsaveis SET nome=?, contato=? WHERE id=?');
        $stmt->execute([trim($_POST['nome']), trim($_POST['contato']), (int)$_POST['id']]);
    }
    if ($action === 'delete') {
        $pdo->prepare('DELETE FROM responsaveis WHERE id=?')->execute([(int)$_POST['id']]);
    }
    flash('success', 'Responsáveis atualizados.');
    redirect('index.php?module=responsaveis');
}
$resps = $pdo->query('SELECT * FROM responsaveis ORDER BY nome')->fetchAll();
?>
<div class="card"><h3>Novo responsável</h3><form method="post" class="inline-form"><input type="hidden" name="action" value="create"><input name="nome" placeholder="Nome" required><input name="contato" placeholder="Contato"><button class="btn-primary">Salvar</button></form></div>
<div class="card"><h3>Responsáveis</h3><div class="table-wrap"><table><tr><th>Nome</th><th>Contato</th><th>Ações</th></tr><?php foreach($resps as $r): ?><tr><td><?=esc($r['nome'])?></td><td><?=esc($r['contato'])?></td><td><form method="post" class="inline-form"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?=$r['id']?>"><input name="nome" value="<?=esc($r['nome'])?>" required><input name="contato" value="<?=esc($r['contato'])?>"><button class="btn-secondary">Atualizar</button></form><form method="post" onsubmit="return confirm('Excluir responsável?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn-danger">Excluir</button></form></td></tr><?php endforeach; ?></table></div></div>
