<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

function redirect(string $url): void
{
    header("Location: {$url}");
    exit;
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function esc(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }

    $msg = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function logAction(PDO $pdo, int $userId, string $action, string $entity, ?int $entityId, string $description): void
{
    $stmt = $pdo->prepare('INSERT INTO logs (usuario_id, acao, entidade, entidade_id, descricao) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$userId, $action, $entity, $entityId, $description]);
}

function statusBadgeClass(string $status): string
{
    return match ($status) {
        'Pago', 'Recebido' => 'success',
        'Pendente' => 'warning',
        'Vencido' => 'danger',
        'Cancelado' => 'dark',
        'Parcelado' => 'info',
        default => 'secondary'
    };
}

function handleUpload(array $file, array $config): ?string
{
    if (empty($file['name'])) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }

    if (($file['size'] ?? 0) > $config['max_upload_size']) {
        return null;
    }

    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $config['allowed_uploads'], true)) {
        return null;
    }

    if (!is_dir($config['upload_dir'])) {
        mkdir($config['upload_dir'], 0777, true);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('anexo_', true) . '.' . strtolower($ext);
    $target = rtrim($config['upload_dir'], '/') . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $target)) {
        return $filename;
    }

    return null;
}
