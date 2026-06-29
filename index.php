<?php require_once 'includes/header.php'; ?>

<div class="page-header">
    <h2>Dashboard</h2>
    <p>Resumen financiero de <?= htmlspecialchars(getUserName()) ?></p>
</div>

<div class="stats-grid" id="statsGrid">
    <div class="loading"><div class="spinner"></div> Cargando...</div>
</div>

<div class="charts-grid">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-chart-line" style="color:var(--accent-light);margin-right:8px"></i> Historial Financiero</h3>
        </div>
        <div class="chart-container">
            <canvas id="chartHistorico"></canvas>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-chart-pie" style="color:var(--warning);margin-right:8px"></i> Gastos del Mes</h3>
        </div>
        <div class="chart-container">
            <canvas id="chartCategorias"></canvas>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-bolt" style="color:var(--success);margin-right:8px"></i> Acciones Rápidas</h3>
    </div>
    <div style="display:flex;gap:12px;flex-wrap:wrap">
        <a href="transacciones.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Nueva Transacción
        </a>
        <a href="analisis.php" class="btn btn-ml btn-sm">
            <i class="fas fa-brain"></i> Ejecutar Análisis ML
        </a>
        <a href="metas.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-bullseye"></i> Ver Metas de Ahorro
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const data = await apiFetch(`${API.transacciones}?action=resumen`);
        const { ingresos_mes, gastos_mes, balance_mes, historico, gastos_categoria } = data;

        document.getElementById('statsGrid').innerHTML = `
            <div class="stat-card">
                <div class="stat-icon income"><i class="fas fa-arrow-down"></i></div>
                <div class="stat-label">Ingresos del Mes</div>
                <div class="stat-value">${formatMoney(ingresos_mes)}</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon expense"><i class="fas fa-arrow-up"></i></div>
                <div class="stat-label">Gastos del Mes</div>
                <div class="stat-value">${formatMoney(gastos_mes)}</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon balance"><i class="fas fa-scale-balanced"></i></div>
                <div class="stat-label">Balance del Mes</div>
                <div class="stat-value">${formatMoney(balance_mes)}</div>
                <div class="stat-change ${balance_mes >= 0 ? 'positive' : 'negative'}">
                    <i class="fas fa-${balance_mes >= 0 ? 'check' : 'exclamation-triangle'}"></i>
                    ${balance_mes >= 0 ? 'Superávit' : 'Déficit'}
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon savings"><i class="fas fa-piggy-bank"></i></div>
                <div class="stat-label">Tasa de Ahorro</div>
                <div class="stat-value">${ingresos_mes > 0 ? ((balance_mes / ingresos_mes) * 100).toFixed(1) : 0}%</div>
            </div>
        `;

        initCharts(historico, gastos_categoria);
    } catch (err) {
        showToast(err.message, 'error');
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
