<?php
/**
 * Script de instalación - Ejecutar una vez desde el navegador
 * http://localhost/proyectowebconta/install.php
 */
$messages = [];

try {
    $host = 'localhost';
    $user = 'root';
    $pass = '';

    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS finplan_ai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE finplan_ai");

    $sql = file_get_contents(__DIR__ . '/database/schema.sql');
    // Quitar CREATE DATABASE y USE del schema (ya ejecutados)
    $sql = preg_replace('/CREATE DATABASE.*?;/is', '', $sql);
    $sql = preg_replace('/USE finplan_ai;/i', '', $sql);

    // Ejecutar statement por statement
    $statements = preg_split('/;\s*\n/', $sql);
    foreach ($statements as $statement) {
        $statement = trim($statement);
        // Ignorar comentarios puros y líneas vacías
        $clean = preg_replace('/--.*$/m', '', $statement);
        $clean = trim($clean);
        if (!empty($clean)) {
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                // Ignorar duplicados en re-instalación
                if (strpos($e->getMessage(), 'Duplicate') === false) {
                    throw $e;
                }
            }
        }
    }

    // Configurar password demo
    $hash = password_hash('demo123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE email = 'demo@finplan.com'");
    $stmt->execute([$hash]);

    // Si no existe el usuario demo, crearlo
    if ($stmt->rowCount() === 0) {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
        $stmt->execute(['Usuario Demo', 'demo@finplan.com', $hash]);
    }

    $messages[] = ['ok', 'Base de datos instalada correctamente'];
    $messages[] = ['ok', 'Usuario demo: demo@finplan.com / demo123'];
    $messages[] = ['info', 'Accede a login.php para comenzar'];
} catch (Exception $e) {
    $messages[] = ['error', 'Error: ' . $e->getMessage()];
    $messages[] = ['info', 'Asegúrate de que MySQL esté activo en XAMPP'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>FinPlan AI - Instalación</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <div class="logo-icon"><i class="fas fa-cog"></i></div>
                <h1>Instalación FinPlan AI</h1>
            </div>
            <?php foreach ($messages as [$type, $msg]): ?>
                <div style="padding:12px;margin:8px 0;border-radius:8px;font-size:14px;
                    background:<?= $type === 'ok' ? 'rgba(16,185,129,0.15)' : ($type === 'error' ? 'rgba(239,68,68,0.15)' : 'rgba(99,102,241,0.15)') ?>;
                    color:<?= $type === 'ok' ? '#10b981' : ($type === 'error' ? '#ef4444' : '#818cf8') ?>">
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endforeach; ?>
            <?php if (!empty($messages) && $messages[0][0] === 'ok'): ?>
                <a href="login.php" class="btn btn-primary btn-block" style="margin-top:16px;text-decoration:none">
                    <i class="fas fa-sign-in-alt"></i> Ir al Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
