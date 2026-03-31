<?php
$config = require __DIR__ . '/../config/config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if (in_array($action, ['create', 'update'], true)) {
        $anexoNome = handleUpload($_FILES['anexo'] ?? [], $config);
        $data = [trim($_POST['descricao']), (int)$_POST['categoria_id'], (int)$_POST['responsavel_id'], (float)$_POST['valor'], $_POST['data_prevista'], $_POST['data_recebimento'] ?: null, $_POST['status'], trim($_POST['observacoes']), isset($_POST['recorrente']) ? 1 : 0, $anexoNome ?: ($_POST['anexo_atual'] ?? null)];
    }
    if ($action === 'create') {
        $pdo->prepare('INSERT INTO contas_receber (descricao,categoria_id,responsavel_id,valor,data_prevista,data_recebimento,status,observacoes,recorrente,anexo) VALUES (?,?,?,?,?,?,?,?,?,?)')->execute($data);
    }
    if ($action === 'update') {
        $id = (int)$_POST['id'];
        $data[] = $id;
        $pdo->prepare('UPDATE contas_receber SET descricao=?,categoria_id=?,responsavel_id=?,valor=?,data_prevista=?,data_recebimento=?,status=?,observacoes=?,recorrente=?,anexo=? WHERE id=?')->execute($data);
    }
    if ($action === 'delete') {
        $pdo->prepare('DELETE FROM contas_receber WHERE id=?')->execute([(int)$_POST['id']]);
    }
    if ($action === 'mark_received') {
        $pdo->prepare("UPDATE contas_receber SET status='Recebido', data_recebimento=CURDATE() WHERE id=?")->execute([(int)$_POST['id']]);
    }
    flash('success', 'Operação concluída em contas a receber.');
    redirect('index.php?module=contas_receber');
}

$fMes = $_GET['mes'] ?? '';
$fCategoria = $_GET['categoria'] ?? '';
$fStatus = $_GET['status'] ?? '';
$fBusca = trim($_GET['q'] ?? '');
$where = ['1=1']; $params=[];
if ($fMes) {$where[]="DATE_FORMAT(data_prevista,'%Y-%m')=?"; $params[]=$fMes;}
if ($fCategoria){$where[]='categoria_id=?';$params[]=$fCategoria;}
if ($fStatus){$where[]='status=?';$params[]=$fStatus;}
if ($fBusca){$where[]='descricao LIKE ?';$params[]="%$fBusca%";}
$ws=implode(' AND ',$where);
$contas = $pdo->prepare("SELECT cr.*, c.nome categoria, r.nome responsavel FROM contas_receber cr LEFT JOIN categorias c ON c.id=cr.categoria_id LEFT JOIN responsaveis r ON r.id=cr.responsavel_id WHERE $ws ORDER BY cr.data_prevista DESC");
$contas->execute($params);
$contas=$contas->fetchAll();
$categorias=$pdo->query('SELECT id,nome FROM categorias ORDER BY nome')->fetchAll();
$responsaveis=$pdo->query('SELECT id,nome FROM responsaveis ORDER BY nome')->fetchAll();
?>
<div class="card"><h3>Filtros</h3><form class="filters"><input type="month" name="mes" value="<?=esc($fMes)?>"><select name="categoria"><option value="">Categoria</option><?php foreach($categorias as $c): ?><option value="<?=$c['id']?>" <?=$fCategoria==$c['id']?'selected':''?>><?=esc($c['nome'])?></option><?php endforeach;?></select><select name="status"><option value="">Status</option><?php foreach(['Pendente','Recebido','Vencido','Cancelado'] as $s): ?><option <?=$fStatus===$s?'selected':''?>><?=$s?></option><?php endforeach;?></select><input name="q" value="<?=esc($fBusca)?>" placeholder="Buscar"><button class="btn-secondary">Filtrar</button></form></div>
<div class="card"><h3>Nova conta a receber</h3><form method="post" enctype="multipart/form-data" class="grid-form"><input type="hidden" name="action" value="create"><input name="descricao" placeholder="Descrição" required><select name="categoria_id"><?php foreach($categorias as $c): ?><option value="<?=$c['id']?>"><?=esc($c['nome'])?></option><?php endforeach;?></select><select name="responsavel_id"><?php foreach($responsaveis as $r): ?><option value="<?=$r['id']?>"><?=esc($r['nome'])?></option><?php endforeach;?></select><input type="number" step="0.01" name="valor" placeholder="Valor" required><input type="date" name="data_prevista" required><input type="date" name="data_recebimento"><select name="status"><?php foreach(['Pendente','Recebido','Vencido','Cancelado'] as $s): ?><option><?=$s?></option><?php endforeach;?></select><label><input type="checkbox" name="recorrente">Recorrente</label><input type="file" name="anexo" accept=".pdf,image/*"><textarea name="observacoes" placeholder="Observações"></textarea><button class="btn-primary">Salvar</button></form></div>
<div class="card"><h3>Listagem</h3><div class="table-wrap"><table><tr><th>Descrição</th><th>Categoria</th><th>Prevista</th><th>Valor</th><th>Status</th><th>Ações</th></tr><?php foreach($contas as $c): ?><tr><td><?=esc($c['descricao'])?></td><td><?=esc($c['categoria'])?></td><td><?=date('d/m/Y',strtotime($c['data_prevista']))?></td><td>R$ <?=number_format($c['valor'],2,',','.')?></td><td><span class="badge <?=statusBadgeClass($c['status'])?>"><?=esc($c['status'])?></span></td><td class="actions"><form method="post"><input type="hidden" name="action" value="mark_received"><input type="hidden" name="id" value="<?=$c['id']?>"><button class="btn-success">Recebido</button></form><form method="post" onsubmit="return confirm('Excluir conta?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$c['id']?>"><button class="btn-danger">Excluir</button></form><?php if($c['anexo']): ?><a class="btn-link" target="_blank" href="uploads/<?=esc($c['anexo'])?>">Anexo</a><?php endif; ?></td></tr><?php endforeach;?></table></div></div>
