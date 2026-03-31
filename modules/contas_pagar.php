<?php
$config = require __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if (in_array($action, ['create', 'update'], true)) {
        $anexoNome = handleUpload($_FILES['anexo'] ?? [], $config);
        $data = [
            trim($_POST['descricao']), (int)$_POST['categoria_id'], (int)$_POST['responsavel_id'], $_SESSION['user']['id'],
            !empty($_POST['usuario_pagou_id']) ? (int)$_POST['usuario_pagou_id'] : null, (float)$_POST['valor'], $_POST['data_vencimento'],
            $_POST['data_pagamento'] ?: null, $_POST['status'], trim($_POST['observacoes']), isset($_POST['recorrente']) ? 1 : 0,
            $anexoNome ?: ($_POST['anexo_atual'] ?? null), $_POST['tipo_pagamento'], !empty($_POST['numero_parcelas']) ? (int)$_POST['numero_parcelas'] : null
        ];
    }

    if ($action === 'create') {
        $sql = 'INSERT INTO contas_pagar (descricao,categoria_id,responsavel_id,usuario_lancou_id,usuario_pagou_id,valor,data_vencimento,data_pagamento,status,observacoes,recorrente,anexo,tipo_pagamento,numero_parcelas) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
        $pdo->prepare($sql)->execute($data);
        logAction($pdo, $_SESSION['user']['id'], 'CREATE', 'contas_pagar', (int)$pdo->lastInsertId(), 'Conta a pagar criada');
    }
    if ($action === 'update') {
        $id = (int)$_POST['id'];
        $sql = 'UPDATE contas_pagar SET descricao=?,categoria_id=?,responsavel_id=?,usuario_lancou_id=?,usuario_pagou_id=?,valor=?,data_vencimento=?,data_pagamento=?,status=?,observacoes=?,recorrente=?,anexo=?,tipo_pagamento=?,numero_parcelas=? WHERE id=?';
        $data[] = $id;
        $pdo->prepare($sql)->execute($data);
        logAction($pdo, $_SESSION['user']['id'], 'UPDATE', 'contas_pagar', $id, 'Conta a pagar atualizada');
    }
    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $pdo->prepare('DELETE FROM contas_pagar WHERE id=?')->execute([$id]);
        logAction($pdo, $_SESSION['user']['id'], 'DELETE', 'contas_pagar', $id, 'Conta a pagar excluída');
    }
    if ($action === 'mark_paid') {
        $id = (int)$_POST['id'];
        $pdo->prepare("UPDATE contas_pagar SET status='Pago', data_pagamento=CURDATE(), usuario_pagou_id=? WHERE id=?")->execute([$_SESSION['user']['id'], $id]);
        logAction($pdo, $_SESSION['user']['id'], 'MARK_PAID', 'contas_pagar', $id, 'Conta marcada como paga');
    }
    flash('success', 'Operação concluída em contas a pagar.');
    redirect('index.php?module=contas_pagar');
}

$fMes = $_GET['mes'] ?? '';
$fCategoria = $_GET['categoria'] ?? '';
$fStatus = $_GET['status'] ?? '';
$fBusca = trim($_GET['q'] ?? '');
$fRec = $_GET['recorrente'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$where = ['1=1']; $params = [];
if ($fMes) { $where[] = "DATE_FORMAT(data_vencimento,'%Y-%m')=?"; $params[] = $fMes; }
if ($fCategoria) { $where[] = 'categoria_id=?'; $params[] = $fCategoria; }
if ($fStatus) { $where[] = 'status=?'; $params[] = $fStatus; }
if ($fBusca) { $where[] = 'descricao LIKE ?'; $params[] = "%$fBusca%"; }
if ($fRec !== '') { $where[] = 'recorrente=?'; $params[] = $fRec; }
$ws = implode(' AND ', $where);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM contas_pagar WHERE $ws");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));

$stmt = $pdo->prepare("SELECT cp.*, c.nome categoria, r.nome responsavel FROM contas_pagar cp LEFT JOIN categorias c ON c.id=cp.categoria_id LEFT JOIN responsaveis r ON r.id=cp.responsavel_id WHERE $ws ORDER BY cp.data_vencimento DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$contas = $stmt->fetchAll();

$categorias = $pdo->query('SELECT id,nome FROM categorias ORDER BY nome')->fetchAll();
$responsaveis = $pdo->query('SELECT id,nome FROM responsaveis ORDER BY nome')->fetchAll();
?>
<div class="card"><h3>Filtros</h3><form class="filters"><input type="month" name="mes" value="<?=esc($fMes)?>"><select name="categoria"><option value="">Categoria</option><?php foreach($categorias as $c): ?><option value="<?=$c['id']?>" <?=$fCategoria==$c['id']?'selected':''?>><?=esc($c['nome'])?></option><?php endforeach; ?></select><select name="status"><option value="">Status</option><?php foreach(['Pendente','Pago','Vencido','Cancelado','Parcelado'] as $s): ?><option <?=$fStatus===$s?'selected':''?>><?=$s?></option><?php endforeach; ?></select><select name="recorrente"><option value="">Recorrente?</option><option value="1" <?=$fRec==='1'?'selected':''?>>Sim</option><option value="0" <?=$fRec==='0'?'selected':''?>>Não</option></select><input name="q" placeholder="Buscar" value="<?=esc($fBusca)?>"><button class="btn-secondary">Filtrar</button></form></div>

<div class="card"><h3>Nova conta a pagar</h3>
<form method="post" enctype="multipart/form-data" class="grid-form">
<input type="hidden" name="action" value="create"><input name="descricao" placeholder="Descrição" required>
<select name="categoria_id" required><?php foreach($categorias as $c): ?><option value="<?=$c['id']?>"><?=esc($c['nome'])?></option><?php endforeach; ?></select>
<select name="responsavel_id"><?php foreach($responsaveis as $r): ?><option value="<?=$r['id']?>"><?=esc($r['nome'])?></option><?php endforeach; ?></select>
<input type="number" step="0.01" name="valor" placeholder="Valor" required><input type="date" name="data_vencimento" required>
<input type="date" name="data_pagamento"><select name="status"><?php foreach(['Pendente','Pago','Vencido','Cancelado','Parcelado'] as $s): ?><option><?=$s?></option><?php endforeach; ?></select>
<input name="tipo_pagamento" placeholder="PIX, Boleto..."><input type="number" name="numero_parcelas" placeholder="Parcelas"><label><input type="checkbox" name="recorrente"> Recorrente</label>
<input type="file" name="anexo" accept=".pdf,image/*"><textarea name="observacoes" placeholder="Observações"></textarea><button class="btn-primary">Salvar</button>
</form></div>

<div class="card"><h3>Listagem</h3><div class="table-wrap"><table><tr><th>Descrição</th><th>Categoria</th><th>Responsável</th><th>Venc.</th><th>Valor</th><th>Status</th><th>Ações</th></tr>
<?php foreach($contas as $c): $atrasada = $c['status']!=='Pago' && strtotime($c['data_vencimento']) < time(); ?><tr class="<?=$atrasada?'row-danger':''?>"><td><?=esc($c['descricao'])?></td><td><?=esc($c['categoria'])?></td><td><?=esc($c['responsavel'])?></td><td><?=date('d/m/Y',strtotime($c['data_vencimento']))?></td><td>R$ <?=number_format($c['valor'],2,',','.')?></td><td><span class="badge <?=statusBadgeClass($c['status'])?>"><?=esc($c['status'])?></span></td><td class="actions"><form method="post"><input type="hidden" name="action" value="mark_paid"><input type="hidden" name="id" value="<?=$c['id']?>"><button class="btn-success">Pago</button></form><form method="post" onsubmit="return confirm('Excluir conta?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$c['id']?>"><button class="btn-danger">Excluir</button></form><?php if($c['anexo']): ?><a class="btn-link" target="_blank" href="uploads/<?=esc($c['anexo'])?>">Anexo</a><?php endif; ?></td></tr><?php endforeach; ?></table></div>
<div class="pagination"><?php for($i=1;$i<=$totalPages;$i++): ?><a class="<?=$i===$page?'active':''?>" href="index.php?module=contas_pagar&page=<?=$i?>&mes=<?=urlencode($fMes)?>&categoria=<?=urlencode($fCategoria)?>&status=<?=urlencode($fStatus)?>&q=<?=urlencode($fBusca)?>&recorrente=<?=urlencode($fRec)?>"><?=$i?></a><?php endfor; ?></div></div>
