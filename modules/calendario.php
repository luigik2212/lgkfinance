<?php
$inicio = date('Y-m-01');
$fim = date('Y-m-t');
$eventos = $pdo->query("SELECT data_vencimento AS data_evento, descricao, 'pagar' tipo, status FROM contas_pagar WHERE data_vencimento BETWEEN '$inicio' AND '$fim' UNION ALL SELECT data_prevista AS data_evento, descricao, 'receber' tipo, status FROM contas_receber WHERE data_prevista BETWEEN '$inicio' AND '$fim' ORDER BY data_evento ASC")->fetchAll();
$agrupado = [];
foreach ($eventos as $e) { $agrupado[$e['data_evento']][] = $e; }
?>
<div class="card"><h3>Calendário financeiro - <?= date('m/Y') ?></h3><div class="calendar-grid"><?php for($d=1;$d<=date('t');$d++): $day=date('Y-m-').str_pad((string)$d,2,'0',STR_PAD_LEFT); ?><div class="day"><header><?= $d ?></header><?php if(!empty($agrupado[$day])): foreach($agrupado[$day] as $ev): ?><div class="event <?=$ev['tipo']==='pagar'?'pay':'receive'?> <?=($ev['status']==='Vencido'?'overdue':'')?>"><?=esc($ev['descricao'])?></div><?php endforeach; endif; ?></div><?php endfor; ?></div></div>
