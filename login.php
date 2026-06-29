<?php
require_once __DIR__ . '/config/session.php';
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FinPlan AI - Iniciar Sesión</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<div class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <div class="logo-icon"><i class="fas fa-chart-pie"></i></div>
                <h1>FinPlan AI</h1>
                <p>Planificación Financiera con Aprendizaje Automático</p>
            </div>

            <div class="auth-tabs">
                <button class="auth-tab active" onclick="switchAuth('login')">Iniciar Sesión</button>
                <button class="auth-tab" onclick="switchAuth('register')">Registrarse</button>
            </div>

            <form id="formLogin" class="auth-form active" onsubmit="handleLogin(event)">
                <div class="form-group">
                    <label>Correo electrónico</label>
                    <input type="email" class="form-control" id="loginEmail" placeholder="tu@email.com" required>
                </div>
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" class="form-control" id="loginPassword" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Ingresar
                </button>
            </form>

            <form id="formRegister" class="auth-form" onsubmit="handleRegister(event)">
                <div class="form-group">
                    <label>Nombre completo</label>
                    <input type="text" class="form-control" id="regNombre" placeholder="Tu nombre" required>
                </div>
                <div class="form-group">
                    <label>Correo electrónico</label>
                    <input type="email" class="form-control" id="regEmail" placeholder="tu@email.com" required>
                </div>
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" class="form-control" id="regPassword" placeholder="••••••••" required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-user-plus"></i> Crear cuenta
                </button>
            </form>

            <div class="demo-credentials">
                <strong>Demo:</strong> demo@finplan.com / demo123
            </div>
        </div>
    </div>
</div>

<script src="assets/js/app.js"></script>
<script>
function switchAuth(type) {
    document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
    if (type === 'login') {
        document.querySelector('.auth-tab:first-child').classList.add('active');
        document.getElementById('formLogin').classList.add('active');
    } else {
        document.querySelector('.auth-tab:last-child').classList.add('active');
        document.getElementById('formRegister').classList.add('active');
    }
}

async function handleLogin(e) {
    e.preventDefault();
    try {
        await apiFetch('api/auth.php?action=login', {
            method: 'POST',
            body: JSON.stringify({
                email: document.getElementById('loginEmail').value,
                password: document.getElementById('loginPassword').value,
            }),
        });
        window.location.href = 'index.php';
    } catch (err) {
        showToast(err.message, 'error');
    }
}

async function handleRegister(e) {
    e.preventDefault();
    try {
        await apiFetch('api/auth.php?action=register', {
            method: 'POST',
            body: JSON.stringify({
                nombre: document.getElementById('regNombre').value,
                email: document.getElementById('regEmail').value,
                password: document.getElementById('regPassword').value,
            }),
        });
        window.location.href = 'index.php';
    } catch (err) {
        showToast(err.message, 'error');
    }
}
</script>
</body>
</html>
