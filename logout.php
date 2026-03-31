<?php
require_once __DIR__ . '/includes/functions.php';
if (isset($_SESSION['user']['id'])) {
    logAction($pdo, (int)$_SESSION['user']['id'], 'LOGOUT', 'usuarios', (int)$_SESSION['user']['id'], 'Usuário encerrou sessão.');
}
session_destroy();
redirect('login.php');
