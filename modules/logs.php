<?php
$logs = $pdo->query('SELECT l.*, u.nome usuario FROM logs l LEFT JOIN usuarios u ON u.id=l.usuario_id ORDER BY l.id DESC LIMIT 300')->fetchAll();
?>
<div class="card"><h3>Histórico de ações</h3><div class="table-wrap"><table><tr><th>Data/Hora</th><th>Usuário</th><th>Ação</th><th>Entidade</th><th>Registro</th><th>Descrição</th></tr><?php foreach($logs as $l):?><tr><td><?=date('d/m/Y H:i',strtotime($l['created_at']))?></td><td><?=esc($l['usuario'])?></td><td><?=esc($l['acao'])?></td><td><?=esc($l['entidade'])?></td><td>#<?=$l['entidade_id']?></td><td><?=esc($l['descricao'])?></td></tr><?php endforeach;?></table></div></div>
