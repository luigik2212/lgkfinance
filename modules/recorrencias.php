<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $pdo->prepare('INSERT INTO recorrencias (tipo,descricao,valor,categoria_id,responsavel_id,periodicidade,proxima_data,ativo) VALUES (?,?,?,?,?,?,?,1)')->execute([$_POST['tipo'], trim($_POST['descricao']), (float)$_POST['valor'], (int)$_POST['categoria_id'], (int)$_POST['responsavel_id'], $_POST['periodicidade'], $_POST['proxima_data']]);
    }
    if ($action === 'generate') {
        $rec = $pdo->prepare('SELECT * FROM recorrencias WHERE id=?');
        $rec->execute([(int)$_POST['id']]);
        $r = $rec->fetch();
        if ($r) {
            if ($r['tipo'] === 'pagar') {
                $pdo->prepare("INSERT INTO contas_pagar (descricao,categoria_id,responsavel_id,usuario_lancou_id,valor,data_vencimento,status,recorrente,tipo_pagamento) VALUES (?,?,?,?,?,?,'Pendente',1,'Recorrência')")->execute([$r['descricao'], $r['categoria_id'], $r['responsavel_id'], $_SESSION['user']['id'], $r['valor'], $r['proxima_data']]);
            } else {
                $pdo->prepare("INSERT INTO contas_receber (descricao,categoria_id,responsavel_id,valor,data_prevista,status,recorrente) VALUES (?,?,?,?,?,'Pendente',1)")->execute([$r['descricao'], $r['categoria_id'], $r['responsavel_id'], $r['valor'], $r['proxima_data']]);
            }
            $pdo->prepare('UPDATE recorrencias SET proxima_data = DATE_ADD(proxima_data, INTERVAL 1 MONTH) WHERE id=?')->execute([$r['id']]);
        }
    }
    if ($action === 'delete') {
        $pdo->prepare('DELETE FROM recorrencias WHERE id=?')->execute([(int)$_POST['id']]);
    }
    flash('success', 'Recorrências atualizadas.');
    redirect('index.php?module=recorrencias');
}
$categorias=$pdo->query('SELECT id,nome FROM categorias ORDER BY nome')->fetchAll();
$responsaveis=$pdo->query('SELECT id,nome FROM responsaveis ORDER BY nome')->fetchAll();
$recs=$pdo->query('SELECT r.*, c.nome categoria, rp.nome responsavel FROM recorrencias r LEFT JOIN categorias c ON c.id=r.categoria_id LEFT JOIN responsaveis rp ON rp.id=r.responsavel_id ORDER BY proxima_data')->fetchAll();
?>
<div class="card"><h3>Nova recorrência</h3><form method="post" class="grid-form"><input type="hidden" name="action" value="create"><select name="tipo"><option value="pagar">Pagar</option><option value="receber">Receber</option></select><input name="descricao" placeholder="Descrição" required><input type="number" step="0.01" name="valor" placeholder="Valor" required><select name="categoria_id"><?php foreach($categorias as $c):?><option value="<?=$c['id']?>"><?=esc($c['nome'])?></option><?php endforeach;?></select><select name="responsavel_id"><?php foreach($responsaveis as $r):?><option value="<?=$r['id']?>"><?=esc($r['nome'])?></option><?php endforeach;?></select><select name="periodicidade"><option>Mensal</option></select><input type="date" name="proxima_data" required><button class="btn-primary">Salvar</button></form></div>
<div class="card"><h3>Recorrências futuras</h3><div class="table-wrap"><table><tr><th>Tipo</th><th>Descrição</th><th>Valor</th><th>Próxima data</th><th>Ações</th></tr><?php foreach($recs as $r):?><tr><td><?=esc($r['tipo'])?></td><td><?=esc($r['descricao'])?></td><td>R$ <?=number_format($r['valor'],2,',','.')?></td><td><?=date('d/m/Y',strtotime($r['proxima_data']))?></td><td class="actions"><form method="post"><input type="hidden" name="action" value="generate"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn-success">Gerar lançamento</button></form><form method="post" onsubmit="return confirm('Excluir recorrência?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn-danger">Excluir</button></form></td></tr><?php endforeach;?></table></div></div>
