<?php
$appConfig = require __DIR__ . '/../config/config.php';
$module = $_GET['module'] ?? 'dashboard';
$allowedModules = [
    'dashboard', 'categorias', 'responsaveis', 'contas_pagar', 'contas_receber',
    'recorrencias', 'parcelamentos', 'relatorios', 'usuarios', 'logs', 'calendario'
];
if (!in_array($module, $allowedModules, true)) {
    $module = 'dashboard';
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($appConfig['app_name']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="app-shell">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div class="main-area">
        <?php include __DIR__ . '/topbar.php'; ?>
        <main class="content">
            <?php if ($msg = flash('success')): ?><div class="alert success"><?= esc($msg) ?></div><?php endif; ?>
            <?php if ($msg = flash('error')): ?><div class="alert danger"><?= esc($msg) ?></div><?php endif; ?>
            <?php include __DIR__ . '/../modules/' . $module . '.php'; ?>
        </main>
    </div>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
