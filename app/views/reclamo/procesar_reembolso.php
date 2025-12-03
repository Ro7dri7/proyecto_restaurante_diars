<?php // app/views/reclamo/procesar_reembolso.php ?>
<link rel="stylesheet" href="/mvc_restaurante/public/css/reclamo/procesar_reembolso.css">
<script src="https://unpkg.com/lucide@latest"></script>

<div class="page-header">
    <h2>Procesar Reembolsos</h2>
    <div class="user-info">
        <div class="user-details">
            <span class="user-name">Admin. Tesorería</span>
            <span class="user-role">Sede Central</span>
        </div>
        <div class="avatar">AT</div>
    </div>
</div>

<div class="work-area">
    <div class="container">
        <div id="notification" class="notification hidden">
            <svg width="24" height="24"><use href="#alert-circle"/></svg>
            <span id="notification-message">...</span>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><svg width="24" height="24"><use href="#credit-card"/></svg></div>
                <div class="stat-content">
                    <span class="stat-label">Pendientes de Pago</span>
                    <span id="total-pendientes" class="stat-value">0</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><svg width="24" height="24"><use href="#dollar-sign"/></svg></div>
                <div class="stat-content">
                    <span class="stat-label">Monto Total</span>
                    <span id="monto-total" class="stat-value">S/ 0.00</span>
                </div>
            </div>
        </div>

        <!-- Lista de reclamos pendientes -->
        <section class="list-section">
            <div class="section-header">
                <h3><span class="step-number">1</span> Reclamos Listos para Pagar</h3>
            </div>
            <div class="section-body">
                <table class="order-list">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Fecha Validación</th>
                            <th>Monto</th>
                            <th>Producto</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-reclamos">
                        <tr><td colspan="6" class="text-center">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Panel de pago (modal) -->
        <div id="panel-pago" class="panel-section hidden">
            <div class="panel-header">
                <h3>Procesar Pago para <span id="pago-id">REC-</span></h3>
                <button id="btn-cerrar" class="change-link">Cerrar</button>
            </div>
            <div class="panel-body">
                <div class="summary-box">
                    <div>Cliente: <span id="pago-cliente">---</span></div>
                    <div>Monto: <span id="pago-monto">S/ 0.00</span></div>
                </div>
                <div class="form-group">
                    <label>Número de Operación</label>
                    <input type="text" id="input-operacion" required placeholder="Ej. OP-12345" />
                </div>
                <div class="form-actions">
                    <button id="btn-cancelar" class="btn-cancel">Cancelar</button>
                    <button id="btn-confirmar" class="btn-submit">Confirmar Pago</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let reclamoSeleccionado = null;
const tabla = document.getElementById('tabla-reclamos');
const panel = document.getElementById('panel-pago');
const btnCerrar = document.getElementById('btn-cerrar');
const btnCancelar = document.getElementById('btn-cancelar');
const btnConfirmar = document.getElementById('btn-confirmar');

function mostrarNotificacion(mensaje, tipo = 'error') {
    const n = document.getElementById('notification');
    const msg = document.getElementById('notification-message');
    n.className = `notification ${tipo}`;
    n.style.display = 'flex';
    msg.textContent = mensaje;
    setTimeout(() => n.classList.add('hidden'), 3000);
}

function cargarReclamos() {
    tabla.innerHTML = '<tr><td colspan="6" class="text-center">Cargando...</td></tr>';
    fetch('/mvc_restaurante/public/index.php?entidad=reclamo&action=obtener-para-reembolso')
        .then(r => r.json())
        .then(data => {
            const reclamos = data.reclamos || [];
            document.getElementById('total-pendientes').textContent = reclamos.length;
            const monto = reclamos.reduce((sum, r) => sum + (r.montoSeleccionado || r.totalPedido), 0);
            document.getElementById('monto-total').textContent = `S/ ${monto.toFixed(2)}`;

            if (reclamos.length === 0) {
                tabla.innerHTML = '<tr><td colspan="6" class="text-center">No hay reclamos para reembolsar.</td></tr>';
                return;
            }

            tabla.innerHTML = '';
            reclamos.forEach(r => {
                const monto = r.montoSeleccionado || r.totalPedido;
                const fecha = new Date(r.fechaResolucion).toLocaleDateString();
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>REC-${r.idReclamo}</td>
                    <td>${r.clienteNombre}</td>
                    <td>${fecha}</td>
                    <td>S/ ${monto.toFixed(2)}</td>
                    <td>${r.productoAfectado || 'N/A'}</td>
                    <td><button class="action-btn" data-id="${r.idReclamo}">Pagar</button></td>
                `;
                tabla.appendChild(tr);
            });

            document.querySelectorAll('.action-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = parseInt(btn.dataset.id);
                    const reclamo = reclamos.find(r => r.idReclamo === id);
                    if (reclamo) {
                        reclamoSeleccionado = reclamo;
                        document.getElementById('pago-id').textContent = `REC-${reclamo.idReclamo}`;
                        document.getElementById('pago-cliente').textContent = reclamo.clienteNombre;
                        const m = reclamo.montoSeleccionado || reclamo.totalPedido;
                        document.getElementById('pago-monto').textContent = `S/ ${m.toFixed(2)}`;
                        document.getElementById('input-operacion').value = '';
                        panel.classList.remove('hidden');
                    }
                });
            });
        })
        .catch(() => {
            tabla.innerHTML = '<tr><td colspan="6" class="text-center">Error al cargar reclamos.</td></tr>';
        });
}

btnCerrar.onclick = () => panel.classList.add('hidden');
btnCancelar.onclick = () => panel.classList.add('hidden');

btnConfirmar.onclick = () => {
    const operacion = document.getElementById('input-operacion').value.trim();
    if (!operacion) {
        mostrarNotificacion('Ingrese el número de operación.', 'error');
        return;
    }

    fetch('/mvc_restaurante/public/index.php?entidad=reclamo&action=procesar-pago', {
        method: 'POST',
        body: new URLSearchParams({
            idReclamo: reclamoSeleccionado.idReclamo,
            numeroOperacion: operacion
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(data.message, 'success');
            cargarReclamos(); // Recargar la lista
            panel.classList.add('hidden');
        } else {
            mostrarNotificacion(data.message, 'error');
        }
    })
    .catch(() => mostrarNotificacion('Error al procesar el pago.', 'error'));
};

cargarReclamos();
</script>