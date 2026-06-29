/**
 * FinPlan AI - JavaScript principal
 */

const API = {
    auth: 'api/auth.php',
    transacciones: 'api/transacciones.php',
    analisis: 'api/analisis.php',
    metas: 'api/metas.php',
};

function formatMoney(amount) {
    return new Intl.NumberFormat('es-GT', {
        style: 'currency',
        currency: 'GTQ',
        minimumFractionDigits: 2,
    }).format(amount);
}

function showToast(message, type = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}

async function apiFetch(url, options = {}) {
    const defaults = {
        headers: { 'Content-Type': 'application/json' },
    };
    const response = await fetch(url, { ...defaults, ...options });
    const data = await response.json();
    if (!response.ok) {
        throw new Error(data.error || 'Error en la solicitud');
    }
    return data;
}

function openModal(id) {
    document.getElementById(id)?.classList.add('active');
}

function closeModal(id) {
    document.getElementById(id)?.classList.remove('active');
}

document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
    }
});

function getTrendIcon(tendencia) {
    const icons = {
        creciente: 'fa-arrow-trend-up',
        decreciente: 'fa-arrow-trend-down',
        estable: 'fa-minus',
    };
    return icons[tendencia] || 'fa-minus';
}

function getTrendClass(tendencia) {
    return `trend-${tendencia}`;
}

function renderProgressBar(porcentaje) {
    const pct = Math.min(100, Math.max(0, porcentaje));
    return `
        <div class="progress-bar">
            <div class="progress-fill" style="width: ${pct}%"></div>
        </div>
    `;
}

function renderConfidenceBar(confianza) {
    return `
        <div class="confidence-bar">
            <div class="confidence-track">
                <div class="confidence-fill" style="width: ${confianza}%"></div>
            </div>
            <span class="confidence-label">${confianza}% confianza</span>
        </div>
    `;
}

function initCharts(historico, gastosCategoria) {
    const mesesMap = {};
    historico.forEach(row => {
        if (!mesesMap[row.mes]) {
            mesesMap[row.mes] = { ingreso: 0, gasto: 0 };
        }
        if (row.tipo === 'ingreso') mesesMap[row.mes].ingreso = parseFloat(row.total);
        else mesesMap[row.mes].gasto = parseFloat(row.total);
    });

    const labels = Object.keys(mesesMap).map(m => {
        const [y, mo] = m.split('-');
        const meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        return meses[parseInt(mo) - 1] + ' ' + y.slice(2);
    });
    const ingresos = Object.values(mesesMap).map(v => v.ingreso);
    const gastos = Object.values(mesesMap).map(v => v.gasto);

    const lineCtx = document.getElementById('chartHistorico');
    if (lineCtx) {
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Ingresos',
                        data: ingresos,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                    },
                    {
                        label: 'Gastos',
                        data: gastos,
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { color: '#94a3b8', font: { family: 'Inter' } },
                    },
                },
                scales: {
                    x: { ticks: { color: '#64748b' }, grid: { color: 'rgba(51,65,85,0.3)' } },
                    y: {
                        ticks: {
                            color: '#64748b',
                            callback: v => 'Q' + v.toLocaleString(),
                        },
                        grid: { color: 'rgba(51,65,85,0.3)' },
                    },
                },
            },
        });
    }

    const doughnutCtx = document.getElementById('chartCategorias');
    if (doughnutCtx && gastosCategoria.length > 0) {
        new Chart(doughnutCtx, {
            type: 'doughnut',
            data: {
                labels: gastosCategoria.map(c => c.nombre),
                datasets: [{
                    data: gastosCategoria.map(c => parseFloat(c.total)),
                    backgroundColor: gastosCategoria.map(c => c.color),
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#94a3b8', font: { family: 'Inter', size: 11 }, padding: 12 },
                    },
                },
            },
        });
    }
}

function renderMLAnalysis(presupuesto, metas) {
    const container = document.getElementById('mlResults');
    if (!container) return;

    let html = `
        <div class="ml-banner">
            <div class="ml-icon"><i class="fas fa-brain"></i></div>
            <div style="flex:1">
                <h3>Análisis completado con Aprendizaje Automático</h3>
                <p>Se analizaron tus transacciones históricas para generar recomendaciones personalizadas.</p>
                ${renderConfidenceBar(presupuesto.confianza_global)}
                <div class="algo-tags">
                    ${presupuesto.algoritmos.map(a => `<span class="algo-tag"><i class="fas fa-microchip"></i> ${a}</span>`).join('')}
                </div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon income"><i class="fas fa-arrow-down"></i></div>
                <div class="stat-label">Ingreso Predicho (ML)</div>
                <div class="stat-value">${formatMoney(presupuesto.ingreso_predicho)}</div>
                <div class="stat-change ${presupuesto.tendencia_ingresos === 'creciente' ? 'positive' : 'negative'}">
                    <i class="fas ${getTrendIcon(presupuesto.tendencia_ingresos)}"></i>
                    Tendencia ${presupuesto.tendencia_ingresos}
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon expense"><i class="fas fa-arrow-up"></i></div>
                <div class="stat-label">Gasto Predicho (ML)</div>
                <div class="stat-value">${formatMoney(presupuesto.gasto_predicho)}</div>
                <div class="stat-change ${presupuesto.tendencia_gastos === 'decreciente' ? 'positive' : 'negative'}">
                    <i class="fas ${getTrendIcon(presupuesto.tendencia_gastos)}"></i>
                    Tendencia ${presupuesto.tendencia_gastos}
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon balance"><i class="fas fa-scale-balanced"></i></div>
                <div class="stat-label">Balance Proyectado</div>
                <div class="stat-value">${formatMoney(presupuesto.balance_mensual)}</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon savings"><i class="fas fa-piggy-bank"></i></div>
                <div class="stat-label">Ahorro Sugerido (20%)</div>
                <div class="stat-value">${formatMoney(presupuesto.ahorro_sugerido)}</div>
                <div class="stat-change">Óptimo: ${formatMoney(presupuesto.ahorro_optimo)}</div>
            </div>
        </div>

        <div class="card" style="margin-bottom:32px">
            <div class="card-header">
                <h3><i class="fas fa-wand-magic-sparkles" style="color:var(--accent-light);margin-right:8px"></i>
                    Presupuesto Sugerido por Categoría</h3>
            </div>
            <div class="suggestion-grid">
    `;

    presupuesto.sugerencias_categoria.forEach(s => {
        html += `
            <div class="suggestion-card">
                <div class="cat-header">
                    <div class="cat-icon" style="background:${s.color}22;color:${s.color}">
                        <i class="fas ${s.icono}"></i>
                    </div>
                    <div>
                        <div class="cat-name">${s.categoria}</div>
                        <div class="cat-type">${s.tipo_gasto === 'necesidad' ? 'Necesidad' : 'Deseo'}</div>
                    </div>
                    <span class="trend-badge ${getTrendClass(s.tendencia)}">
                        <i class="fas ${getTrendIcon(s.tendencia)}"></i> ${s.tendencia}
                    </span>
                </div>
                <div class="suggestion-amounts">
                    <div>
                        <div class="label">Promedio actual</div>
                        <div class="value">${formatMoney(s.promedio_actual)}</div>
                    </div>
                    <div>
                        <div class="label">Sugerido ML</div>
                        <div class="value" style="color:var(--accent-light)">${formatMoney(s.sugerido_ml)}</div>
                    </div>
                </div>
                ${renderConfidenceBar(s.confianza)}
            </div>
        `;
    });

    html += '</div></div>';

    // Metas sugeridas
    html += `
        <div class="card" style="margin-bottom:32px">
            <div class="card-header">
                <h3><i class="fas fa-bullseye" style="color:var(--warning);margin-right:8px"></i>
                    Metas de Ahorro Sugeridas por ML</h3>
            </div>
            <div class="goals-grid">
    `;

    metas.metas_sugeridas.forEach(m => {
        html += `
            <div class="goal-card">
                <div class="goal-header">
                    <h4>${m.nombre}</h4>
                    <span class="priority-badge priority-${m.prioridad}">${m.prioridad}</span>
                </div>
                <p style="font-size:13px;color:var(--text-secondary);margin-bottom:12px">${m.descripcion}</p>
                <div class="goal-amounts">
                    <span>Objetivo: <strong>${formatMoney(m.monto_objetivo)}</strong></span>
                    <span>~${m.meses_estimados} meses</span>
                </div>
                ${renderConfidenceBar(m.confianza)}
                <button class="btn btn-sm btn-ml btn-block" style="margin-top:12px"
                    onclick="crearMetaSugerida('${m.nombre}', ${m.monto_objetivo}, '${m.prioridad}')">
                    <i class="fas fa-plus"></i> Crear esta meta
                </button>
            </div>
        `;
    });

    html += '</div></div>';

    // Proyecciones de metas existentes
    if (metas.proyecciones_existentes.length > 0) {
        html += `
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-line" style="color:var(--success);margin-right:8px"></i>
                        Proyección de Metas Actuales</h3>
                </div>
                <div class="goals-grid">
        `;
        metas.proyecciones_existentes.forEach(p => {
            const pct = p.monto_objetivo > 0
                ? (p.monto_actual / p.monto_objetivo) * 100 : 0;
            html += `
                <div class="goal-card">
                    <div class="goal-header">
                        <h4>${p.nombre}</h4>
                        <span class="trend-badge ${p.probabilidad_exito >= 80 ? 'trend-decreciente' : p.probabilidad_exito >= 50 ? 'trend-estable' : 'trend-creciente'}">
                            ${p.probabilidad_exito}% éxito
                        </span>
                    </div>
                    ${renderProgressBar(pct)}
                    <div class="goal-amounts">
                        <span>${formatMoney(p.monto_actual)} / ${formatMoney(p.monto_objetivo)}</span>
                        <span>Faltan: ${formatMoney(p.faltante)}</span>
                    </div>
                    <p style="font-size:12px;color:var(--text-secondary);margin-top:12px">
                        <i class="fas fa-lightbulb" style="color:var(--warning)"></i> ${p.recomendacion}
                    </p>
                </div>
            `;
        });
        html += '</div></div>';
    }

    container.innerHTML = html;
}

async function crearMetaSugerida(nombre, monto, prioridad) {
    try {
        await apiFetch(`${API.metas}?action=crear`, {
            method: 'POST',
            body: JSON.stringify({
                nombre, monto_objetivo: monto, prioridad, sugerido_ml: 1,
            }),
        });
        showToast('Meta creada exitosamente');
        if (typeof loadMetas === 'function') loadMetas();
    } catch (err) {
        showToast(err.message, 'error');
    }
}

async function runMLAnalysis() {
    const btn = document.getElementById('btnRunML');
    const container = document.getElementById('mlResults');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<div class="spinner" style="width:16px;height:16px;border-width:2px"></div> Analizando...';
    }
    container.innerHTML = '<div class="loading"><div class="spinner"></div> Ejecutando algoritmos de ML...</div>';

    try {
        const data = await apiFetch(API.analisis);
        renderMLAnalysis(data.presupuesto, data.metas);
        showToast('Análisis ML completado');
    } catch (err) {
        container.innerHTML = `<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>${err.message}</p></div>`;
        showToast(err.message, 'error');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-brain"></i> Ejecutar Análisis ML';
        }
    }
}
