<?php require_once 'includes/header.php'; ?>

<div class="page-header">
    <h2>Transacciones</h2>
    <p>Registra y administra tus ingresos y gastos</p>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px">
    <div class="tabs" id="filterTabs">
        <button class="tab active" onclick="filterTipo('')">Todas</button>
        <button class="tab" onclick="filterTipo('ingreso')">Ingresos</button>
        <button class="tab" onclick="filterTipo('gasto')">Gastos</button>
    </div>
    <button class="btn btn-primary btn-sm" onclick="openModal('modalTransaccion')">
        <i class="fas fa-plus"></i> Nueva Transacción
    </button>
</div>

<div class="card">
    <div class="table-container" id="tablaTransacciones">
        <div class="loading"><div class="spinner"></div> Cargando transacciones...</div>
    </div>
</div>

<!-- Modal Nueva Transacción -->
<div class="modal-overlay" id="modalTransaccion">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-plus-circle" style="color:var(--accent-light);margin-right:8px"></i> Nueva Transacción</h3>
            <button class="modal-close" onclick="closeModal('modalTransaccion')">&times;</button>
        </div>
        <form onsubmit="crearTransaccion(event)">
            <div class="form-group">
                <label>Tipo</label>
                <select class="form-control" id="txTipo" onchange="loadCategorias()" required>
                    <option value="ingreso">Ingreso</option>
                    <option value="gasto">Gasto</option>
                </select>
            </div>
            <div class="form-group">
                <label>Categoría</label>
                <select class="form-control" id="txCategoria" required></select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Monto (Q)</label>
                    <input type="number" class="form-control" id="txMonto" step="0.01" min="0.01" placeholder="0.00" required>
                </div>
                <div class="form-group">
                    <label>Fecha</label>
                    <input type="date" class="form-control" id="txFecha" required>
                </div>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <input type="text" class="form-control" id="txDescripcion" placeholder="Descripción opcional">
            </div>
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-save"></i> Guardar Transacción
            </button>
        </form>
    </div>
</div>

<script>
let currentFilter = '';

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('txFecha').value = new Date().toISOString().split('T')[0];
    loadCategorias();
    loadTransacciones();
});

function filterTipo(tipo) {
    currentFilter = tipo;
    document.querySelectorAll('#filterTabs .tab').forEach(t => t.classList.remove('active'));
    event.target.classList.add('active');
    loadTransacciones();
}

async function loadCategorias() {
    const tipo = document.getElementById('txTipo').value;
    const data = await apiFetch(`${API.transacciones}?action=categorias&tipo=${tipo}`);
    const select = document.getElementById('txCategoria');
    select.innerHTML = data.categorias.map(c =>
        `<option value="${c.id}">${c.nombre}</option>`
    ).join('');
}

async function loadTransacciones() {
    const container = document.getElementById('tablaTransacciones');
    try {
        let url = API.transacciones;
        if (currentFilter) url += `&tipo=${currentFilter}`;
        const data = await apiFetch(url);

        if (data.transacciones.length === 0) {
            container.innerHTML = '<div class="empty-state"><i class="fas fa-receipt"></i><p>No hay transacciones registradas</p></div>';
            return;
        }

        container.innerHTML = `
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Categoría</th>
                        <th>Descripción</th>
                        <th>Tipo</th>
                        <th>Monto</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    ${data.transacciones.map(t => `
                        <tr>
                            <td>${t.fecha}</td>
                            <td>
                                <span class="category-badge" style="background:${t.color}22;color:${t.color}">
                                    <i class="fas ${t.icono}"></i> ${t.categoria}
                                </span>
                            </td>
                            <td>${t.descripcion || '-'}</td>
                            <td>${t.tipo === 'ingreso'
                                ? '<span style="color:var(--success)">Ingreso</span>'
                                : '<span style="color:var(--danger)">Gasto</span>'}</td>
                            <td class="amount-${t.tipo === 'ingreso' ? 'income' : 'expense'}">
                                ${t.tipo === 'ingreso' ? '+' : '-'}${formatMoney(t.monto)}
                            </td>
                            <td>
                                <button class="btn btn-danger btn-sm" onclick="eliminarTx(${t.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    } catch (err) {
        showToast(err.message, 'error');
    }
}

async function crearTransaccion(e) {
    e.preventDefault();
    try {
        await apiFetch(API.transacciones, {
            method: 'POST',
            body: JSON.stringify({
                categoria_id: document.getElementById('txCategoria').value,
                monto: document.getElementById('txMonto').value,
                descripcion: document.getElementById('txDescripcion').value,
                fecha: document.getElementById('txFecha').value,
            }),
        });
        showToast('Transacción registrada');
        closeModal('modalTransaccion');
        e.target.reset();
        document.getElementById('txFecha').value = new Date().toISOString().split('T')[0];
        loadTransacciones();
    } catch (err) {
        showToast(err.message, 'error');
    }
}

async function eliminarTx(id) {
    if (!confirm('¿Eliminar esta transacción?')) return;
    try {
        await apiFetch(`${API.transacciones}?id=${id}`, { method: 'DELETE' });
        showToast('Transacción eliminada');
        loadTransacciones();
    } catch (err) {
        showToast(err.message, 'error');
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
