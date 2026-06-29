<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

if (!isLoggedIn()) {
    jsonResponse(['error' => 'No autorizado'], 401);
}

$db = getConnection();
$userId = getUserId();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getMetas($db, $userId);
        break;
    case 'POST':
        $action = $_GET['action'] ?? 'crear';
        if ($action === 'abono') {
            abonarMeta($db, $userId);
        } else {
            crearMeta($db, $userId);
        }
        break;
    case 'DELETE':
        eliminarMeta($db, $userId);
        break;
    default:
        jsonResponse(['error' => 'Método no permitido'], 405);
}

function getMetas(PDO $db, int $userId): void
{
    $stmt = $db->prepare('SELECT * FROM metas_ahorro WHERE usuario_id = ? ORDER BY prioridad DESC, created_at DESC');
    $stmt->execute([$userId]);
    $metas = $stmt->fetchAll();

    foreach ($metas as &$meta) {
        $meta['progreso'] = $meta['monto_objetivo'] > 0
            ? round(($meta['monto_actual'] / $meta['monto_objetivo']) * 100, 1)
            : 0;
    }

    jsonResponse(['metas' => $metas]);
}

function crearMeta(PDO $db, int $userId): void
{
    $data = json_decode(file_get_contents('php://input'), true);

    $stmt = $db->prepare("
        INSERT INTO metas_ahorro (usuario_id, nombre, monto_objetivo, fecha_limite, prioridad, sugerido_ml)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $userId,
        trim($data['nombre'] ?? ''),
        (float) ($data['monto_objetivo'] ?? 0),
        $data['fecha_limite'] ?? null,
        $data['prioridad'] ?? 'media',
        (int) ($data['sugerido_ml'] ?? 0),
    ]);

    jsonResponse(['success' => true, 'id' => (int) $db->lastInsertId()]);
}

function abonarMeta(PDO $db, int $userId): void
{
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int) ($data['id'] ?? 0);
    $monto = (float) ($data['monto'] ?? 0);

    if (!$id || $monto <= 0) {
        jsonResponse(['error' => 'ID y monto requeridos'], 400);
    }

    $stmt = $db->prepare('
        UPDATE metas_ahorro SET monto_actual = monto_actual + ?
        WHERE id = ? AND usuario_id = ?
    ');
    $stmt->execute([$monto, $id, $userId]);

    jsonResponse(['success' => true]);
}

function eliminarMeta(PDO $db, int $userId): void
{
    $id = (int) ($_GET['id'] ?? 0);
    $stmt = $db->prepare('DELETE FROM metas_ahorro WHERE id = ? AND usuario_id = ?');
    $stmt->execute([$id, $userId]);
    jsonResponse(['success' => true]);
}
