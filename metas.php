<?php require_once 'includes/header.php'; ?>

<div class="page-header">
    <h2>Metas de Ahorro</h2>
    <p>Define y da seguimiento a tus objetivos financieros</p>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px">
    <p style="color:var(--text-secondary);font-size:14px">
        <i class="fas fa-lightbulb" style="color:var(--warning)"></i>
        Usa el <a href="analisis.php" style="color:var(--accent-light)">Análisis ML</a> para obtener metas sugeridas automáticamente
    </p>
    <button class="btn btn-primary btn-sm" onclick="openModal('modalMeta')">
        <i class="fas fa-plus"></i> Nueva Meta
    </button>
</div>

<div class="goals-grid" id="metasGrid">
    <div class="loading"><div class="spinner"></div> Cargando metas...</div>
</div>

<!-- Modal Nueva Meta -->
<div class="modal-overlay" id="modalMeta">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-bullseye" style="color:var(--warning);margin-right:8px"></i> Nueva Meta de Ahorro</h3>
            <button class="modal-close" onclick="closeModal('modalMeta')">&times;</button>
        </div>
        <form onsubmit="crearMeta(event)">
            <div class="form-group">
                <label>Nombre de la meta</label>
                <input type="text" class="form-control" id="metaNombre" placeholder="Ej: Vacaciones, Auto nuevo..." required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Monto objetivo (Q)</label>
                    <input type="number" class="form-control" id="metaMonto" step="0.01" min="1" placeholder="0.00" required>
                </div>
                <div class="form-group">
                    <label>Fecha límite</label>
                    <input type="date" class="form-control" id="metaFecha">
                </div>
            </div>
            <div class="form-group">
                <label>Prioridad</label>
                <select class="form-control" id="metaPrioridad">
                    <option value="baja">Baja</option>
                    <option value="media" selected>Media</option>
                    <option value="alta">Alta</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-save"></i> Crear Meta
            </button>
        </form>
    </div>
</div>

<!-- Modal Abono -->
<div class="modal-overlay" id="modalAbono">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-coins" style="color:var(--success);margin-right:8px"></i> Registrar Abono</h3>
            <button class="modal-close" onclick="closeModal('modalAbono')">&times;</button>
        </div>
        <form onsubmit="registrarAbono(event)">
            <input type="hidden" id="abonoMetaId">
            <div class="form-group">
                <label>Monto del abono (Q)</label>
                <input type="number" class="form-control" id="abonoMonto" step="0.01" min="0.01" placeholder="0.00" required>
            </div>
            <button type="submit" class="btn btn-success btn-block">
                <i class="fas fa-plus"></i> Abonar
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', loadMetas);

async function loadMetas() {
    const container = document.getElementById('metasGrid');
    try {
        const data = await apiFetch(API.metas);

        if (data.metas.length === 0) {
            container.innerHTML = `
                <div class="empty-state" style="grid-column:1/-1">
                    <i class="fas fa-bullseye"></i>
                    <p>No tienes metas de ahorro. ¡Crea una o usa el Análisis ML!</p>
                </div>`;
            return;
        }

        container.innerHTML = data.metas.map(m => `
            <div class="goal-card">
                <div class="goal-header">
                    <h4>${m.nombre} ${m.sugerido_ml == 1 ? '<i class="fas fa-robot" style="color:var(--accent-light);font-size:12px" title="Sugerida por ML"></i>' : ''}</h4>
                    <span class="priority-badge priority-${m.prioridad}">${m.prioridad}</span>
                </div>
                ${renderProgressBar(m.progreso)}
                <div class="goal-amounts">
                    <span><strong>${formatMoney(parseFloat(m.monto_actual))}</strong> de ${formatMoney(parseFloat(m.monto_objetivo))}</span>
                    <span>${m.progreso}%</span>
                </div>
                ${m.fecha_limite ? `<p style="font-size:12px;color:var(--text-muted);margin-top:8px"><i class="fas fa-calendar"></i> Meta: ${m.fecha_limite}</p>` : ''}
                <div style="display:flex;gap:8px;margin-top:16px">
                    <button class="btn btn-success btn-sm" style="flex:1" onclick="abrirAbono(${m.id})">
                        <i class="fas fa-plus"></i> Abonar
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="eliminarMeta(${m.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `).join('');
    } catch (err) {
        showToast(err.message, 'error');
    }
}

async function crearMeta(e) {
    e.preventDefault();
    try {
        await apiFetch(`${API.metas}?action=crear`, {
            method: 'POST',
            body: JSON.stringify({
                nombre: document.getElementById('metaNombre').value,
                monto_objetivo: document.getElementById('metaMonto').value,
                fecha_limite: document.getElementById('metaFecha').value || null,
                prioridad: document.getElementById('metaPrioridad').value,
            }),
        });
        showToast('Meta creada exitosamente');
        closeModal('modalMeta');
        e.target.reset();
        loadMetas();
    } catch (err) {
        showToast(err.message, 'error');
    }
}

function abrirAbono(id) {
    document.getElementById('abonoMetaId').value = id;
    document.getElementById('abonoMonto').value = '';
    openModal('modalAbono');
}

async function registrarAbono(e) {
    e.preventDefault();
    try {
        await apiFetch(`${API.metas}?action=abono`, {
            method: 'POST',
            body: JSON.stringify({
                id: document.getElementById('abonoMetaId').value,
                monto: document.getElementById('abonoMonto').value,
            }),
        });
        showToast('Abono registrado');
        closeModal('modalAbono');
        loadMetas();
    } catch (err) {
        showToast(err.message, 'error');
    }
}

async function eliminarMeta(id) {
    if (!confirm('¿Eliminar esta meta?')) return;
    try {
        await apiFetch(`${API.metas}?id=${id}`, { method: 'DELETE' });
        showToast('Meta eliminada');
        loadMetas();
    } catch (err) {
        showToast(err.message, 'error');
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
