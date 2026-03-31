<?php
$ini = $_GET['ini'] ?? date('Y-m-01');
$fim = $_GET['fim'] ?? date('Y-m-t');
$cat = $pdo->prepare('SELECT c.nome, COALESCE(SUM(cp.valor),0) total FROM categorias c LEFT JOIN contas_pagar cp ON cp.categoria_id=c.id AND cp.data_vencimento BETWEEN ? AND ? GROUP BY c.id,c.nome ORDER BY total DESC');
$cat->execute([$ini,$fim]);
$cat = $cat->fetchAll();
$fluxo = $pdo->prepare("SELECT DATE_FORMAT(data_vencimento,'%Y-%m') mes, SUM(valor) saidas FROM contas_pagar WHERE data_vencimento BETWEEN ? AND ? GROUP BY mes ORDER BY mes");
$fluxo->execute([$ini,$fim]);
$saidas = $fluxo->fetchAll();
$entradas = $pdo->prepare("SELECT DATE_FORMAT(data_prevista,'%Y-%m') mes, SUM(valor) entradas FROM contas_receber WHERE data_prevista BETWEEN ? AND ? GROUP BY mes ORDER BY mes");
$entradas->execute([$ini,$fim]);
$entradas = $entradas->fetchAll();
$vencidas = $pdo->query("SELECT descricao, valor, data_vencimento FROM contas_pagar WHERE status IN ('Pendente','Vencido') AND data_vencimento < CURDATE() ORDER BY data_vencimento")->fetchAll();
?>
<div class="card"><h3>Filtros do relatório</h3><form class="filters"><input type="hidden" name="module" value="relatorios"><input type="date" name="ini" value="<?=esc($ini)?>"><input type="date" name="fim" value="<?=esc($fim)?>"><button class="btn-secondary">Aplicar</button><button onclick="window.print();return false;" class="btn-primary">Imprimir / PDF</button></form></div>
<div class="grid cards-3"><article class="card"><h4>Total de despesas</h4><strong>R$ <?=number_format(array_sum(array_column($cat,'total')),2,',','.')?></strong></article><article class="card"><h4>Total de entradas</h4><strong>R$ <?=number_format(array_sum(array_column($entradas,'entradas')),2,',','.')?></strong></article><article class="card"><h4>Fluxo de caixa</h4><strong>R$ <?=number_format(array_sum(array_column($entradas,'entradas')) - array_sum(array_column($cat,'total')),2,',','.')?></strong></article></div>
<div class="grid cols-2"><article class="card"><h3>Despesas por categoria</h3><div class="table-wrap"><table><tr><th>Categoria</th><th>Total</th></tr><?php foreach($cat as $r):?><tr><td><?=esc($r['nome'])?></td><td>R$ <?=number_format($r['total'],2,',','.')?></td></tr><?php endforeach;?></table></div></article><article class="card"><h3>Contas vencidas</h3><div class="table-wrap"><table><tr><th>Descrição</th><th>Vencimento</th><th>Valor</th></tr><?php foreach($vencidas as $v):?><tr class="row-danger"><td><?=esc($v['descricao'])?></td><td><?=date('d/m/Y',strtotime($v['data_vencimento']))?></td><td>R$ <?=number_format($v['valor'],2,',','.')?></td></tr><?php endforeach;?></table></div></article></div>
