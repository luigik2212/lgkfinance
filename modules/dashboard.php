<?php
$monthStart = date('Y-m-01');
$monthEnd = date('Y-m-t');

$totals = [
    'pagas' => (float)$pdo->query("SELECT COALESCE(SUM(valor),0) FROM contas_pagar WHERE status='Pago' AND data_pagamento BETWEEN '$monthStart' AND '$monthEnd'")->fetchColumn(),
    'pendentes' => (float)$pdo->query("SELECT COALESCE(SUM(valor),0) FROM contas_pagar WHERE status='Pendente' AND data_vencimento BETWEEN '$monthStart' AND '$monthEnd'")->fetchColumn(),
    'recebidas' => (float)$pdo->query("SELECT COALESCE(SUM(valor),0) FROM contas_receber WHERE status='Recebido' AND data_recebimento BETWEEN '$monthStart' AND '$monthEnd'")->fetchColumn(),
    'vencidas' => (float)$pdo->query("SELECT COALESCE(SUM(valor),0) FROM contas_pagar WHERE status='Vencido' OR (status='Pendente' AND data_vencimento < CURDATE())")->fetchColumn(),
];
$saldo = $totals['recebidas'] - $totals['pagas'];

$proximas = $pdo->query("SELECT id, descricao, valor, data_vencimento, status FROM contas_pagar WHERE data_vencimento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) ORDER BY data_vencimento")->fetchAll();
$atrasadas = $pdo->query("SELECT id, descricao, valor, data_vencimento FROM contas_pagar WHERE status IN ('Pendente','Vencido') AND data_vencimento < CURDATE() ORDER BY data_vencimento")->fetchAll();

$entradaSaida = $pdo->query("SELECT
 (SELECT COALESCE(SUM(valor),0) FROM contas_receber WHERE status='Recebido' AND data_recebimento BETWEEN '$monthStart' AND '$monthEnd') AS entradas,
 (SELECT COALESCE(SUM(valor),0) FROM contas_pagar WHERE status='Pago' AND data_pagamento BETWEEN '$monthStart' AND '$monthEnd') AS saidas")->fetch();
$despesasCategoria = $pdo->query("SELECT c.nome, COALESCE(SUM(cp.valor),0) total FROM categorias c LEFT JOIN contas_pagar cp ON cp.categoria_id=c.id AND cp.data_vencimento BETWEEN '$monthStart' AND '$monthEnd' GROUP BY c.id,c.nome ORDER BY total DESC LIMIT 8")->fetchAll();
?>
<section class="grid cards-6">
    <article class="card"><h3>Pagas no mês</h3><strong>R$ <?= number_format($totals['pagas'], 2, ',', '.') ?></strong></article>
    <article class="card"><h3>Pendentes</h3><strong>R$ <?= number_format($totals['pendentes'], 2, ',', '.') ?></strong></article>
    <article class="card"><h3>Recebidas</h3><strong>R$ <?= number_format($totals['recebidas'], 2, ',', '.') ?></strong></article>
    <article class="card"><h3>Saldo</h3><strong>R$ <?= number_format($saldo, 2, ',', '.') ?></strong></article>
    <article class="card"><h3>Alertas vencidos</h3><strong class="text-danger">R$ <?= number_format($totals['vencidas'], 2, ',', '.') ?></strong></article>
    <article class="card"><h3>Resumo do mês</h3><strong><?= date('m/Y') ?></strong></article>
</section>

<section class="grid cols-2">
    <article class="card">
        <h3>Próximas contas a vencer</h3>
        <div class="table-wrap"><table><tr><th>Descrição</th><th>Vencimento</th><th>Valor</th></tr>
        <?php foreach ($proximas as $item): ?><tr><td><?= esc($item['descricao']) ?></td><td><?= date('d/m/Y', strtotime($item['data_vencimento'])) ?></td><td>R$ <?= number_format($item['valor'], 2, ',', '.') ?></td></tr><?php endforeach; ?>
        </table></div>
    </article>
    <article class="card">
        <h3>Contas atrasadas</h3>
        <div class="table-wrap"><table><tr><th>Descrição</th><th>Vencimento</th><th>Valor</th></tr>
        <?php foreach ($atrasadas as $item): ?><tr class="row-danger"><td><?= esc($item['descricao']) ?></td><td><?= date('d/m/Y', strtotime($item['data_vencimento'])) ?></td><td>R$ <?= number_format($item['valor'], 2, ',', '.') ?></td></tr><?php endforeach; ?>
        </table></div>
    </article>
</section>

<section class="grid cols-2">
    <article class="card"><h3>Entradas x Saídas</h3><canvas id="chartFluxo"></canvas></article>
    <article class="card"><h3>Despesas por categoria</h3><canvas id="chartCategoria"></canvas></article>
</section>
<script>
window.dashboardCharts = {
    fluxo: <?= json_encode([(float)$entradaSaida['entradas'], (float)$entradaSaida['saidas']]) ?>,
    categorias: <?= json_encode(array_column($despesasCategoria, 'nome')) ?>,
    valores: <?= json_encode(array_map('floatval', array_column($despesasCategoria, 'total'))) ?>
};
</script>
