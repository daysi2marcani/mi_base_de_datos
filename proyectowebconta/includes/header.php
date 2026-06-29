<?php
require_once __DIR__ . '/../config/session.php';
requireLogin();

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$userInitial = strtoupper(substr(getUserName(), 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FinPlan AI - <?= ucfirst($currentPage) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-icon"><i class="fas fa-chart-pie"></i></div>
            <span>FinPlan AI</span>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php" class="nav-item <?= $currentPage === 'index' ? 'active' : '' ?>">
                <i class="fas fa-house"></i> Dashboard
            </a>
            <a href="transacciones.php" class="nav-item <?= $currentPage === 'transacciones' ? 'active' : '' ?>">
                <i class="fas fa-exchange-alt"></i> Transacciones
            </a>
            <a href="presupuesto.php" class="nav-item <?= $currentPage === 'presupuesto' ? 'active' : '' ?>">
                <i class="fas fa-wallet"></i> Presupuesto
            </a>
            <a href="metas.php" class="nav-item <?= $currentPage === 'metas' ? 'active' : '' ?>">
                <i class="fas fa-bullseye"></i> Metas de Ahorro
            </a>
            <a href="analisis.php" class="nav-item <?= $currentPage === 'analisis' ? 'active' : '' ?>">
                <i class="fas fa-brain"></i> Análisis ML
                <span class="badge-ml">AI</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar"><?= $userInitial ?></div>
                <div class="user-details">
                    <span><?= htmlspecialchars(getUserName()) ?></span>
                    <small>Planificador Financiero</small>
                </div>
            </div>
            <a href="#" class="nav-item" onclick="logout()" style="margin-top:8px">
                <i class="fas fa-sign-out-alt"></i> Cerrar sesión
            </a>
        </div>
    </aside>
    <main class="main-content">
