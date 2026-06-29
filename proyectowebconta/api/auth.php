<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'login':
        login();
        break;
    case 'register':
        register();
        break;
    case 'logout':
        logout();
        break;
    default:
        jsonResponse(['error' => 'Acción no válida'], 400);
}

function login(): void
{
    $data = json_decode(file_get_contents('php://input'), true);
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if (!$email || !$password) {
        jsonResponse(['error' => 'Email y contraseña requeridos'], 400);
    }

    $db = getConnection();
    $stmt = $db->prepare('SELECT id, nombre, password FROM usuarios WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        jsonResponse(['error' => 'Credenciales incorrectas'], 401);
    }

    $_SESSION['usuario_id'] = $user['id'];
    $_SESSION['usuario_nombre'] = $user['nombre'];

    jsonResponse(['success' => true, 'nombre' => $user['nombre']]);
}

function register(): void
{
    $data = json_decode(file_get_contents('php://input'), true);
    $nombre = trim($data['nombre'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if (!$nombre || !$email || !$password) {
        jsonResponse(['error' => 'Todos los campos son requeridos'], 400);
    }

    $db = getConnection();
    $stmt = $db->prepare('SELECT id FROM usuarios WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        jsonResponse(['error' => 'El email ya está registrado'], 409);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare('INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)');
    $stmt->execute([$nombre, $email, $hash]);

    $_SESSION['usuario_id'] = (int) $db->lastInsertId();
    $_SESSION['usuario_nombre'] = $nombre;

    jsonResponse(['success' => true, 'nombre' => $nombre]);
}

function logout(): void
{
    session_destroy();
    jsonResponse(['success' => true]);
}
