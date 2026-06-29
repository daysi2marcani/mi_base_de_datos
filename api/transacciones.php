<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

if (!isLoggedIn()) {
    jsonResponse(['error' => 'No autorizado'], 401);
}

$db = getConnection();
$userId = getUserId();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        if ($action === 'categorias') {
            getCategorias($db);
        } elseif ($action === 'resumen') {
            getResumen($db, $userId);
        } else {
            getTransacciones($db, $userId);
        }
        break;
    case 'POST':
        crearTransaccion($db, $userId);
        break;
    case 'DELETE':
        eliminarTransaccion($db, $userId);
        break;
    default:
        jsonResponse(['error' => 'Método no permitido'], 405);
}

function getCategorias(PDO $db): void
{
    $tipo = $_GET['tipo'] ?? '';
    if ($tipo) {
        $stmt = $db->prepare('SELECT * FROM categorias WHERE tipo = ? ORDER BY nombre');
        $stmt->execute([$tipo]);
    } else {
        $stmt = $db->query('SELECT * FROM categorias ORDER BY tipo, nombre');
    }
    jsonResponse(['categorias' => $stmt->fetchAll()]);
}

function getTransacciones(PDO $db, int $userId): void
{
    $mes = $_GET['mes'] ?? date('m');
    $anio = $_GET['anio'] ?? date('Y');
    $tipo = $_GET['tipo'] ?? '';

    $sql = "
        SELECT t.*, c.nombre AS categoria, c.tipo, c.icono, c.color
        FROM transacciones t
        JOIN categorias c ON t.categoria_id = c.id
        WHERE t.usuario_id = ?
          AND MONTH(t.fecha) = ? AND YEAR(t.fecha) = ?
    ";
    $params = [$userId, $mes, $anio];

    if ($tipo) {
        $sql .= ' AND c.tipo = ?';
        $params[] = $tipo;
    }
    $sql .= ' ORDER BY t.fecha DESC';

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    jsonResponse(['transacciones' => $stmt->fetchAll()]);
}

function getResumen(PDO $db, int $userId): void
{
    $stmt = $db->prepare("
        SELECT c.tipo, SUM(t.monto) AS total
        FROM transacciones t
        JOIN categorias c ON t.categoria_id = c.id
        WHERE t.usuario_id = ?
          AND MONTH(t.fecha) = MONTH(CURDATE())
          AND YEAR(t.fecha) = YEAR(CURDATE())
        GROUP BY c.tipo
    ");
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll();

    $ingresos = 0;
    $gastos = 0;
    foreach ($rows as $row) {
        if ($row['tipo'] === 'ingreso') $ingresos = (float) $row['total'];
        else $gastos = (float) $row['total'];
    }

    // Datos para gráfico mensual (6 meses)
    $stmt = $db->prepare("
        SELECT DATE_FORMAT(t.fecha, '%Y-%m') AS mes,
               c.tipo,
               SUM(t.monto) AS total
        FROM transacciones t
        JOIN categorias c ON t.categoria_id = c.id
        WHERE t.usuario_id = ?
          AND t.fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(t.fecha, '%Y-%m'), c.tipo
        ORDER BY mes
    ");
    $stmt->execute([$userId]);
    $historico = $stmt->fetchAll();

    // Gastos por categoría del mes
    $stmt = $db->prepare("
        SELECT c.nombre, c.color, SUM(t.monto) AS total
        FROM transacciones t
        JOIN categorias c ON t.categoria_id = c.id
        WHERE t.usuario_id = ? AND c.tipo = 'gasto'
          AND MONTH(t.fecha) = MONTH(CURDATE())
          AND YEAR(t.fecha) = YEAR(CURDATE())
        GROUP BY c.id, c.nombre, c.color
        ORDER BY total DESC
    ");
    $stmt->execute([$userId]);

    jsonResponse([
        'ingresos_mes'    => $ingresos,
        'gastos_mes'      => $gastos,
        'balance_mes'     => $ingresos - $gastos,
        'historico'       => $historico,
        'gastos_categoria' => $stmt->fetchAll(),
    ]);
}

function crearTransaccion(PDO $db, int $userId): void
{
    $data = json_decode(file_get_contents('php://input'), true);

    $categoriaId = (int) ($data['categoria_id'] ?? 0);
    $monto = (float) ($data['monto'] ?? 0);
    $descripcion = trim($data['descripcion'] ?? '');
    $fecha = $data['fecha'] ?? date('Y-m-d');

    if (!$categoriaId || $monto <= 0) {
        jsonResponse(['error' => 'Categoría y monto son requeridos'], 400);
    }

    $stmt = $db->prepare("
        INSERT INTO transacciones (usuario_id, categoria_id, monto, descripcion, fecha)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$userId, $categoriaId, $monto, $descripcion, $fecha]);

    jsonResponse(['success' => true, 'id' => (int) $db->lastInsertId()]);
}

function eliminarTransaccion(PDO $db, int $userId): void
{
    $id = (int) ($_GET['id'] ?? 0);
    if (!$id) {
        jsonResponse(['error' => 'ID requerido'], 400);
    }

    $stmt = $db->prepare('DELETE FROM transacciones WHERE id = ? AND usuario_id = ?');
    $stmt->execute([$id, $userId]);

    jsonResponse(['success' => true]);
}
