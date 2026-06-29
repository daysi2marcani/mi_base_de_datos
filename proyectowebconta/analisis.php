<?php require_once 'includes/header.php'; ?>

<div class="page-header">
    <h2>Análisis con Aprendizaje Automático</h2>
    <p>Algoritmos predictivos para optimizar tu planificación financiera</p>
</div>

<div style="margin-bottom:32px">
    <button class="btn btn-ml" id="btnRunML" onclick="runMLAnalysis()">
        <i class="fas fa-brain"></i> Ejecutar Análisis ML
    </button>
</div>

<div id="mlResults">
    <div class="card">
        <div class="empty-state">
            <i class="fas fa-brain"></i>
            <p>Presiona "Ejecutar Análisis ML" para analizar tus finanzas</p>
            <p style="font-size:13px;margin-top:8px;color:var(--text-muted)">
                Se utilizarán Regresión Lineal, Media Móvil Ponderada y Regla 50/30/20 Adaptativa
            </p>
        </div>
    </div>
</div>

<div class="card" style="margin-top:32px">
    <div class="card-header">
        <h3><i class="fas fa-info-circle" style="color:var(--info);margin-right:8px"></i> Sobre los Algoritmos ML</h3>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px">
        <div>
            <h4 style="font-size:14px;margin-bottom:8px;color:var(--accent-light)">
                <i class="fas fa-chart-line"></i> Regresión Lineal Simple
            </h4>
            <p style="font-size:13px;color:var(--text-secondary)">
                Predice tendencias futuras de ingresos y gastos basándose en datos históricos mensuales.
                Calcula la pendiente y el coeficiente de determinación (R²) para medir confianza.
            </p>
        </div>
        <div>
            <h4 style="font-size:14px;margin-bottom:8px;color:var(--accent-light)">
                <i class="fas fa-weight-hanging"></i> Media Móvil Ponderada
            </h4>
            <p style="font-size:13px;color:var(--text-secondary)">
                Suaviza fluctuaciones dando mayor peso a los meses recientes,
                generando estimaciones más precisas del comportamiento financiero actual.
            </p>
        </div>
        <div>
            <h4 style="font-size:14px;margin-bottom:8px;color:var(--accent-light)">
                <i class="fas fa-balance-scale"></i> Regla 50/30/20 Adaptativa
            </h4>
            <p style="font-size:13px;color:var(--text-secondary)">
                Clasifica gastos en necesidades y deseos, sugiriendo presupuestos personalizados.
                Si detecta tendencias crecientes en deseos, recomienda reducir un 5%.
            </p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
