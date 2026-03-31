<?php
require_once __DIR__ . '/includes/functions.php';
$config = require __DIR__ . '/config/config.php';

if (isLoggedIn()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ? AND status = 1 LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($senha, $user['senha'])) {
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'nome' => $user['nome'],
            'tipo' => $user['tipo_usuario']
        ];
        logAction($pdo, (int)$user['id'], 'LOGIN', 'usuarios', (int)$user['id'], 'Usuário autenticou no sistema.');
        redirect('index.php');
    }

    flash('error', 'Credenciais inválidas.');
    redirect('login.php');
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= esc($config['app_name']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-body">
<div class="login-card">
    <h1>LGK <span>FINANCE</span></h1>
    <p>Controle financeiro doméstico profissional.</p>
    <?php if ($msg = flash('error')): ?><div class="alert danger"><?= esc($msg) ?></div><?php endif; ?>
    <form method="post">
        <label>E-mail</label>
        <input type="email" name="email" required placeholder="admin@lgkfinance.local">
        <label>Senha</label>
        <input type="password" name="senha" required placeholder="******">
        <button class="btn-primary" type="submit">Entrar</button>
    </form>
    <small>Usuário inicial: admin@lgkfinance.local / Senha: 123456</small>
</div>
</body>
</html>
