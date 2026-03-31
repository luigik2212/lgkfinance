<?php $current = $_GET['module'] ?? 'dashboard'; ?>
<aside class="sidebar" id="sidebar">
    <div class="brand">LGK <span>FINANCE</span></div>
    <nav>
        <?php
        $items = [
            'dashboard' => 'Dashboard',
            'categorias' => 'Categorias',
            'contas_pagar' => 'Contas a Pagar',
            'contas_receber' => 'Contas a Receber',
            'relatorios' => 'Relatórios',
            'calendario' => 'Calendário',
            'usuarios' => 'Usuários',
            'logs' => 'Logs',
        ];
        foreach ($items as $key => $label):
        ?>
            <a class="nav-item <?= $current === $key ? 'active' : '' ?>" href="index.php?module=<?= $key ?>"><?= esc($label) ?></a>
        <?php endforeach; ?>
    </nav>
</aside>
