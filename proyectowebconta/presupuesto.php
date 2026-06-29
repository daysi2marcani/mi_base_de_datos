<?php require_once 'includes/header.php'; ?>

<div class="page-header">
    <h2>Presupuesto</h2>
    <p>Controla tus gastos con presupuestos sugeridos por ML</p>
</div>

<div style="margin-bottom:24px">
    <button class="btn btn-ml btn-sm" onclick="cargarPresupuesto()">
        <i class="fas fa-wand-magic-sparkles"></i> Generar Presupuesto con ML
    </button>
</div>

<div id="presupuestoContent">
    <div class="card">
        <div class="empty-state">
            <i class="fas fa-wallet"></i>
            <p>Genera un presupuesto inteligente basado en tu historial financiero</p>
        </div>
    </div>
</div>

<script>
async function cargarPresupuesto() {
    const container = document.getElementById('presupuestoContent');
    container.innerHTML = '<div class="loading"><div class="spinner"></div> Generando presupuesto con ML...</div>';

    try {
        const data = await apiFetch(`${API.analisis}?action=presupuesto`);
        const p = data.analisis;

        let html = `
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon income"><i class="fas fa-arrow-down"></i></div>
                    <div class="stat-label">Ingreso Estimado</div>
                    <div class="stat-value">${formatMoney(p.ingreso_predicho)}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon expense"><i class="fas fa-arrow-up"></i></div>
                    <div class="stat-label">Gasto Estimado</div>
                    <div class="stat-value">${formatMoney(p.gasto_predicho)}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon balance"><i class="fas fa-scale-balanced"></i></div>
                    <div class="stat-label">Balance Proyectado</div>
                    <div class="stat-value">${formatMoney(p.balance_mensual)}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon savings"><i class="fas fa-piggy-bank"></i></div>
                    <div class="stat-label">% Ahorro Actual</div>
                    <div class="stat-value">${p.porcentaje_ahorro}%</div>
                </div>
            </div>

            <div class="card" style="margin-bottom:24px">
                <div class="card-header"><h3>Distribución 50/30/20</h3></div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;text-align:center">
                    <div>
                        <div style="font-size:13px;color:var(--text-muted);margin-bottom:4px">Necesidades</div>
                        <div style="font-size:24px;font-weight:700;color:var(--danger)">${formatMoney(p.distribucion.necesidades)}</div>
                    </div>
                    <div>
                        <div style="font-size:13px;color:var(--text-muted);margin-bottom:4px">Deseos</div>
                        <div style="font-size:24px;font-weight:700;color:var(--warning)">${formatMoney(p.distribucion.deseos)}</div>
                    </div>
                    <div>
                        <div style="font-size:13px;color:var(--text-muted);margin-bottom:4px">Ahorro</div>
                        <div style="font-size:24px;font-weight:700;color:var(--success)">${formatMoney(p.distribucion.ahorro)}</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Presupuesto por Categoría (Sugerido ML)</h3>
                    ${renderConfidenceBar(p.confianza_global)}
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th>Tipo</th>
                                <th>Promedio Actual</th>
                                <th>Sugerido ML</th>
                                <th>Tendencia</th>
                                <th>Diferencia</th>
                            </tr>
                        </thead>
                        <tbody>
        `;

        p.sugerencias_categoria.forEach(s => {
            const diff = s.sugerido_ml - s.promedio_actual;
            html += `
                <tr>
                    <td>
                        <span class="category-badge" style="background:${s.color}22;color:${s.color}">
                            <i class="fas ${s.icono}"></i> ${s.categoria}
                        </span>
                    </td>
                    <td>${s.tipo_gasto === 'necesidad' ? 'Necesidad' : 'Deseo'}</td>
                    <td>${formatMoney(s.promedio_actual)}</td>
                    <td style="font-weight:700;color:var(--accent-light)">${formatMoney(s.sugerido_ml)}</td>
                    <td>
                        <span class="trend-badge ${getTrendClass(s.tendencia)}">
                            <i class="fas ${getTrendIcon(s.tendencia)}"></i> ${s.tendencia}
                        </span>
                    </td>
                    <td class="${diff <= 0 ? 'amount-income' : 'amount-expense'}">
                        ${diff <= 0 ? '' : '+'}${formatMoney(diff)}
                    </td>
                </tr>
            `;
        });

        html += '</tbody></table></div></div>';
        container.innerHTML = html;
        showToast('Presupuesto generado con ML');
    } catch (err) {
        container.innerHTML = `<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>${err.message}</p></div>`;
        showToast(err.message, 'error');
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
