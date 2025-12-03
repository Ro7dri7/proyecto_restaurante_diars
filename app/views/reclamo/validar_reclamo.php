<?php
// Vista: app/views/reclamo/validar_reclamo.php
?>
<link rel="stylesheet" href="/mvc_restaurante/public/css/reclamo/validar_reclamo.css">
<div class="page-header">
    <h2>Validación de Reclamos</h2>
    <div class="user-info">
        <div class="user-details">
            <span class="user-name">Admin. Mostrador</span>
            <span class="user-role">Sede Central</span>
        </div>
        <div class="avatar">AM</div>
    </div>
</div>
<div class="work-area">
    <div class="container">
        <div id="notification" class="notification hidden">
            <svg width="24" height="24"><use href="#alert-circle"/></svg>
            <span id="notification-message">Mensaje de notificación</span>
        </div>
        <section id="pending-list" class="step-section">
            <div class="step-header">
                <h3><span class="step-number">1</span> Pendientes de Validación</h3>
                <select id="filter-sort" class="change-link">
                    <option value="reciente">Más recientes</option>
                    <option value="prioridad-alta">Prioridad Alta</option>
                </select>
            </div>
            <div class="step-body">
                <div id="reclamos-pendientes" class="order-list">
                    <table>
                        <thead>
                            <tr>
                                <th>ID Reclamo</th>
                                <th>Producto Afectado</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Prioridad</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-pendientes">
                            <!-- Filas generadas dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        <section id="validation-panel" class="step-section hidden">
            <div class="step-header">
                <h3><span class="step-number">2</span> Validar Solicitud</h3>
                <button id="btn-cerrar-panel" class="change-link">Cerrar Panel</button>
            </div>
            <div class="step-body">
                <div id="detalle-reclamo" class="client-display">
                    <div class="client-avatar"><svg width="32" height="32"><use href="#user"/></svg></div>
                    <div class="client-info">
                        <h4 id="reclamo-id">REC-0000</h4>
                        <div class="client-meta">
                            <span>Cliente: <span id="cliente-nombre">---</span></span>
                            <span>•</span>
                            <span>Pedido: <span id="pedido-id">#0000</span></span>
                        </div>
                    </div>
                    <div class="client-status">
                        <span class="status-badge"><svg width="14" height="14"><use href="#check-circle"/></svg> Solicitado</span>
                    </div>
                </div>
                <div class="form-row full">
                    <div class="form-group">
                        <label for="motivo-cliente">Motivo del Cliente</label>
                        <textarea id="motivo-cliente" readonly rows="4" placeholder="El cliente reportó..."></textarea>
                    </div>
                </div>
                <div class="form-row full">
                    <div class="form-group">
                        <label for="comentario-interno">Comentario Interno (Opcional)</label>
                        <textarea id="comentario-interno" rows="4" placeholder="Notas internas sobre la validación..."></textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" id="btn-rechazar" class="btn-cancel">Rechazar</button>
                    <button type="button" id="btn-validar" class="btn-submit">
                        <svg width="18" height="18"><use href="#save"/></svg>
                        Validar
                    </button>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
    let reclamoSeleccionado = null;
    const tablaPendientes = document.getElementById('tabla-pendientes');
    const panelValidacion = document.getElementById('validation-panel');
    const btnCerrarPanel = document.getElementById('btn-cerrar-panel');
    const btnRechazar = document.getElementById('btn-rechazar');
    const btnValidar = document.getElementById('btn-validar');
    const inputComentario = document.getElementById('comentario-interno');
    const notification = document.getElementById('notification');
    const mensajeNotificacion = document.getElementById('notification-message');

    function mostrarNotificacion(mensaje, tipo = 'error') {
        notification.className = `notification ${tipo}`;
        notification.style.display = 'flex';
        mensajeNotificacion.textContent = mensaje;
        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }

    function cargarReclamosPendientes() {
        tablaPendientes.innerHTML = '<tr><td colspan="6" class="text-center py-4">Cargando reclamos...</td></tr>';
        fetch('/mvc_restaurante/public/index.php?entidad=reclamo&action=obtener-pendientes')
            .then(response => response.json())
            .then(data => {
                tablaPendientes.innerHTML = '';
                if (data.reclamos && data.reclamos.length > 0) {
                    data.reclamos.forEach(reclamo => {
                        const fila = document.createElement('tr');
                        const fecha = new Date(reclamo.fechaSolicitud).toLocaleString();
                        fila.innerHTML = `
                            <td>#${reclamo.idReclamo}</td>
                            <td>${reclamo.productoAfectado}</td>
                            <td>${reclamo.clienteNombre}</td>
                            <td>${fecha}</td>
                            <td><span class="badge-prioridad prioridad-media">${reclamo.prioridad}</span></td>
                            <td><button class="action-btn" data-id="${reclamo.idReclamo}" 
                                data-nombre="${reclamo.clienteNombre}"
                                data-pedido="${reclamo.idPedido}"
                                data-motivo="${reclamo.motivo}">Validar</button></td>
                        `;
                        tablaPendientes.appendChild(fila);
                    });
                    document.querySelectorAll('.action-btn').forEach(btn => {
                        btn.addEventListener('click', () => {
                            reclamoSeleccionado = {
                                idReclamo: parseInt(btn.dataset.id),
                                clienteNombre: btn.dataset.nombre,
                                idPedido: parseInt(btn.dataset.pedido),
                                motivo: btn.dataset.motivo
                            };
                            mostrarDetalleReclamo();
                            panelValidacion.classList.remove('hidden');
                        });
                    });
                } else {
                    tablaPendientes.innerHTML = '<tr><td colspan="6" class="text-center py-4">No hay reclamos pendientes.</td></tr>';
                }
            })
            .catch(() => {
                tablaPendientes.innerHTML = '<tr><td colspan="6" class="text-center py-4">Error al cargar reclamos.</td></tr>';
            });
    }

    function mostrarDetalleReclamo() {
        document.getElementById('reclamo-id').textContent = `REC-${reclamoSeleccionado.idReclamo}`;
        document.getElementById('cliente-nombre').textContent = reclamoSeleccionado.clienteNombre;
        document.getElementById('pedido-id').textContent = `#${reclamoSeleccionado.idPedido}`;
        document.getElementById('motivo-cliente').value = reclamoSeleccionado.motivo;
        inputComentario.value = '';
    }

    btnCerrarPanel.addEventListener('click', () => {
        panelValidacion.classList.add('hidden');
        reclamoSeleccionado = null;
    });

    function validarReclamo(accion) {
        const formData = new FormData();
        formData.append('idReclamo', reclamoSeleccionado.idReclamo);
        formData.append('accion', accion);
        formData.append('comentario', inputComentario.value.trim());
        fetch('/mvc_restaurante/public/index.php?entidad=reclamo&action=validar-reclamo', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion(data.message, 'success');
                cargarReclamosPendientes(); // Recargamos la lista
                panelValidacion.classList.add('hidden');
                reclamoSeleccionado = null;
            } else {
                mostrarNotificacion(data.message || 'Error desconocido.', 'error');
            }
        })
        .catch(() => {
            mostrarNotificacion('Error al validar el reclamo.', 'error');
        });
    }

    btnRechazar.addEventListener('click', () => { if (reclamoSeleccionado) validarReclamo('rechazar'); });
    btnValidar.addEventListener('click', () => { if (reclamoSeleccionado) validarReclamo('aprobar'); });

    // Iniciar
    cargarReclamosPendientes();
</script>