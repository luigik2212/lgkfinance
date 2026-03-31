<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action=$_POST['action']??'';
    if($action==='create'){
        $pdo->prepare('INSERT INTO parcelamentos (descricao,valor_total,quantidade_parcelas,parcela_atual,valor_parcela,saldo_restante,status,conta_pagar_id) VALUES (?,?,?,?,?,?,?,?)')->execute([trim($_POST['descricao']),(float)$_POST['valor_total'],(int)$_POST['quantidade_parcelas'],(int)$_POST['parcela_atual'],(float)$_POST['valor_parcela'],(float)$_POST['saldo_restante'],$_POST['status'],!empty($_POST['conta_pagar_id'])?(int)$_POST['conta_pagar_id']:null]);
    }
    if($action==='advance'){
        $id=(int)$_POST['id'];
        $pdo->prepare('UPDATE parcelamentos SET parcela_atual = parcela_atual + 1, saldo_restante = GREATEST(saldo_restante - valor_parcela,0), status = IF(parcela_atual + 1 >= quantidade_parcelas, "Quitado", status) WHERE id=?')->execute([$id]);
    }
    if($action==='delete'){$pdo->prepare('DELETE FROM parcelamentos WHERE id=?')->execute([(int)$_POST['id']]);}
    flash('success','Parcelamentos atualizados.'); redirect('index.php?module=parcelamentos');
}
$contas=$pdo->query('SELECT id,descricao FROM contas_pagar ORDER BY id DESC LIMIT 100')->fetchAll();
$pars=$pdo->query('SELECT p.*, cp.descricao conta FROM parcelamentos p LEFT JOIN contas_pagar cp ON cp.id=p.conta_pagar_id ORDER BY p.id DESC')->fetchAll();
?>
<div class="card"><h3>Novo parcelamento</h3><form method="post" class="grid-form"><input type="hidden" name="action" value="create"><input name="descricao" placeholder="Descrição" required><input type="number" step="0.01" name="valor_total" placeholder="Valor total" required><input type="number" name="quantidade_parcelas" placeholder="Qtd parcelas" required><input type="number" name="parcela_atual" value="1" required><input type="number" step="0.01" name="valor_parcela" placeholder="Valor parcela" required><input type="number" step="0.01" name="saldo_restante" placeholder="Saldo restante" required><select name="status"><option>Ativo</option><option>Quitado</option><option>Cancelado</option></select><select name="conta_pagar_id"><option value="">Vincular conta</option><?php foreach($contas as $c):?><option value="<?=$c['id']?>"><?=esc($c['descricao'])?></option><?php endforeach;?></select><button class="btn-primary">Salvar</button></form></div>
<div class="card"><h3>Parcelamentos</h3><div class="table-wrap"><table><tr><th>Descrição</th><th>Total</th><th>Atual</th><th>Qtd</th><th>Saldo</th><th>Status</th><th>Ações</th></tr><?php foreach($pars as $p):?><tr><td><?=esc($p['descricao'])?></td><td>R$ <?=number_format($p['valor_total'],2,',','.')?></td><td><?=$p['parcela_atual']?></td><td><?=$p['quantidade_parcelas']?></td><td>R$ <?=number_format($p['saldo_restante'],2,',','.')?></td><td><?=esc($p['status'])?></td><td class="actions"><form method="post"><input type="hidden" name="action" value="advance"><input type="hidden" name="id" value="<?=$p['id']?>"><button class="btn-secondary">Avançar parcela</button></form><form method="post" onsubmit="return confirm('Excluir parcelamento?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$p['id']?>"><button class="btn-danger">Excluir</button></form></td></tr><?php endforeach;?></table></div></div>
