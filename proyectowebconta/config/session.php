<?php
/**
 * Gestión de sesiones y autenticación
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool
{
    return isset($_SESSION['usuario_id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function getUserId(): int
{
    return (int) ($_SESSION['usuario_id'] ?? 0);
}

function getUserName(): string
{
    return $_SESSION['usuario_nombre'] ?? 'Usuario';
}

function jsonResponse(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
