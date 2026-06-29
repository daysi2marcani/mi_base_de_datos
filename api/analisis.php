<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/ml_engine.php';

if (!isLoggedIn()) {
    jsonResponse(['error' => 'No autorizado'], 401);
}

$db = getConnection();
$userId = getUserId();
$ml = new MLEngine($db, $userId);
$action = $_GET['action'] ?? 'completo';

switch ($action) {
    case 'presupuesto':
        $resultado = $ml->analizarPresupuesto();
        $ml->guardarAnalisis('presupuesto', $resultado, $resultado['confianza_global']);
        jsonResponse(['analisis' => $resultado]);
        break;

    case 'metas':
        $resultado = $ml->sugerirMetasAhorro();
        $ml->guardarAnalisis('metas_ahorro', $resultado, 85.0);
        jsonResponse(['analisis' => $resultado]);
        break;

    case 'completo':
    default:
        $presupuesto = $ml->analizarPresupuesto();
        $metas = $ml->sugerirMetasAhorro();
        $ml->guardarAnalisis('completo', [
            'presupuesto' => $presupuesto,
            'metas'       => $metas,
        ], $presupuesto['confianza_global']);

        jsonResponse([
            'presupuesto' => $presupuesto,
            'metas'       => $metas,
        ]);
}
