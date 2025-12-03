<?php
// Vista: app/views/reclamo/notificar_resolucion.php
?>
<!-- Enlazamos el CSS externo -->
<link rel="stylesheet" href="/mvc_restaurante/public/css/reclamo/notificar_resolucion.css">
<!-- ICONOS LUCIDE (CDN) -->
<script src="https://unpkg.com/lucide@latest"></script>
<!-- HEADER DEL CONTENIDO -->
<div class="page-header">
    <h2>Notificación de Resoluciones</h2>
    <div class="user-info">
        <div class="user-details">
            <span class="user-name">Admin. Mostrador</span>
            <span class="user-role">Sede Central</span>
        </div>
        <div class="avatar">AM</div>
    </div>
</div>
<!-- WORK AREA -->
<div class="work-area">
    <div class="container">
        <!-- NOTIFICACIÓN -->
        <div id="notification" class="notification hidden">
            <svg width="24" height="24"><use href="#alert-circle"/></svg>
            <span id="notification-message">Mensaje de notificación</span>
        </div>
        <!-- BANDEJA DE SALIDA -->
        <section id="outbox-list" class="step-section">
            <div class="step-header">
                <h3><span class="step-number">1</span> Bandeja de Salida <span id="total-resueltos" class="badge">0</span></h3>
            </div>
            <div class="step-body">
                <div id="reclamos-resueltos" class="order-list">
                    <table>
                        <thead>
                            <tr>
                                <th>ID Reclamo</th>
                                <th>Cliente</th>
                                <th>Resolución</th>
                                <th>Fecha</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-resueltos">
                            <!-- Filas generadas dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        <!-- PANEL DE REDACCIÓN -->
        <section id="draft-panel" class="step-section hidden">
            <div class="step-header">
                <h3><span class="step-number">2</span> Redactar Notificación <span id="draft-id">REC-0000</span></h3>
                <button id="btn-cerrar-panel" class="change-link">Cerrar Panel</button>
            </div>
            <div class="step-body">
                <div class="form-row full">
                    <div class="form-group">
                        <label for="canal-comunicacion">Canal de Comunicación</label>
                        <div class="channel-buttons">
                            <button type="button" id="btn-email" class="channel-btn active">
                                <svg width="18" height="18"><use href="#mail"/></svg>
                                Email
                            </button>
                            <button type="button" id="btn-whatsapp" class="channel-btn">
                                <svg width="18" height="18"><use href="#message-square"/></svg>
                                WhatsApp
                            </button>
                        </div>
                    </div>
                </div>
                <div class="form-row full">
                    <div class="form-group">
                        <label for="destinatario">Destinatario</label>
                        <div class="recipient-info">
                            <span class="label">Nombre:</span>
                            <span id="destinatario-nombre">---</span>
                            <span class="label">Email:</span>
                            <span id="destinatario-email">---</span>
                        </div>
                    </div>
                </div>
                <div class="form-row full">
                    <div class="form-group">
                        <label for="vista-previa">Vista Previa del Mensaje (Generado automáticamente)</label>
                        <textarea id="vista-previa" readonly rows="8" placeholder="El mensaje se generará automáticamente..."></textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" id="btn-enviar-notificacion" class="btn-submit">
                        <svg width="18" height="18"><use href="#send"/></svg>
                        Enviar Notificación Oficial
                    </button>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
    // Variables de estado
    let reclamoSeleccionado = null;
    let canalActual = 'email'; // Por defecto

    // Elementos del DOM
    const tablaResueltos = document.getElementById('tabla-resueltos');
    const panelRedaccion = document.getElementById('draft-panel');
    const btnCerrarPanel = document.getElementById('btn-cerrar-panel');
    const btnEmail = document.getElementById('btn-email');
    const btnWhatsapp = document.getElementById('btn-whatsapp');
    const draftIdElement = document.getElementById('draft-id');
    const destinatarioNombre = document.getElementById('destinatario-nombre');
    const destinatarioEmail = document.getElementById('destinatario-email');
    const vistaPrevia = document.getElementById('vista-previa');
    const btnEnviarNotificacion = document.getElementById('btn-enviar-notificacion');
    const totalResueltos = document.getElementById('total-resueltos');
    const notification = document.getElementById('notification');
    const mensajeNotificacion = document.getElementById('notification-message');

    // Mostrar notificación
    function mostrarNotificacion(mensaje, tipo = 'error') {
        notification.className = `notification ${tipo}`;
        notification.style.display = 'flex';
        mensajeNotificacion.textContent = mensaje;
        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }

    // Cargar reclamos resueltos
    function cargarReclamosResueltos() {
        tablaResueltos.innerHTML = '<tr><td colspan="5" class="text-center py-4">Cargando reclamos resueltos...</td></tr>';
        fetch('/mvc_restaurante/public/index.php?entidad=reclamo&action=obtener-resueltos')
            .then(response => response.json())
            .then(data => {
                tablaResueltos.innerHTML = '';
                if (data.reclamos && data.reclamos.length > 0) {
                    totalResueltos.textContent = data.reclamos.length;
                    data.reclamos.forEach(reclamo => {
                        const fila = document.createElement('tr');
                        const fecha = new Date(reclamo.fechaResolucion).toLocaleString();
                        
                        // ✅ MODIFICACIÓN: Lógica para asignar la clase CSS correcta
                        const estadoClase = reclamo.estadoReclamo === 'Validado' ? 'estado-procedente' : 
                                          reclamo.estadoReclamo === 'Rechazado' ? 'estado-improcedente' :
                                          'estado-notificado'; // Nuevo estado

                        fila.innerHTML = `
                            <td>#${reclamo.idReclamo}</td>
                            <td>${reclamo.clienteNombre}</td>
                            <td><span class="${estadoClase}">${reclamo.estadoReclamo}</span></td>
                            <td>${fecha}</td>
                            <td><button class="action-btn" data-id="${reclamo.idReclamo}" 
                                data-nombre="${reclamo.clienteNombre}"
                                data-email="${reclamo.clienteEmail}"
                                data-estado="${reclamo.estadoReclamo}"
                                data-motivo="${reclamo.comentarioResolucion}">Notificar</button></td>
                        `;
                        tablaResueltos.appendChild(fila);
                    });
                    document.querySelectorAll('.action-btn').forEach(btn => {
                        btn.addEventListener('click', () => {
                            reclamoSeleccionado = {
                                idReclamo: parseInt(btn.dataset.id),
                                clienteNombre: btn.dataset.nombre,
                                clienteEmail: btn.dataset.email,
                                estadoReclamo: btn.dataset.estado,
                                comentarioResolucion: btn.dataset.motivo
                            };
                            mostrarPanelRedaccion();
                            panelRedaccion.classList.remove('hidden');
                        });
                    });
                } else {
                    totalResueltos.textContent = '0';
                    tablaResueltos.innerHTML = '<tr><td colspan="5" class="text-center py-4">No hay reclamos resueltos para notificar.</td></tr>';
                }
            })
            .catch(() => {
                tablaResueltos.innerHTML = '<tr><td colspan="5" class="text-center py-4">Error al cargar reclamos resueltos.</td></tr>';
            });
    }

    // Mostrar panel de redacción
    function mostrarPanelRedaccion() {
        draftIdElement.textContent = `REC-${reclamoSeleccionado.idReclamo}`;
        destinatarioNombre.textContent = reclamoSeleccionado.clienteNombre;
        destinatarioEmail.textContent = reclamoSeleccionado.clienteEmail;
        generarVistaPrevia(); // Genera el mensaje automáticamente
    }

    // Generar vista previa del mensaje
    function generarVistaPrevia() {
        const nombre = reclamoSeleccionado.clienteNombre;
        const idReclamo = reclamoSeleccionado.idReclamo;
        const estado = reclamoSeleccionado.estadoReclamo;
        const motivo = reclamoSeleccionado.comentarioResolucion || 'Sin motivo especificado.';
        
        let mensaje = `Estimado(a) ${nombre},\n\n`;
        mensaje += `Le informamos que su reclamo ${idReclamo} ha sido declarado ${estado.toUpperCase()} tras la revisión.\n\n`;
        mensaje += `Motivo: ${motivo}\n\n`;
        mensaje += `Cualquier duda adicional estamos a su servicio.\nAtte. D'alicias.`;
        
        vistaPrevia.value = mensaje;
    }

    // Cambiar canal de comunicación
    btnEmail.addEventListener('click', () => {
        canalActual = 'email';
        btnEmail.classList.add('active');
        btnWhatsapp.classList.remove('active');
        generarVistaPrevia(); // Actualiza el mensaje si cambia el canal
    });

    btnWhatsapp.addEventListener('click', () => {
        canalActual = 'whatsapp';
        btnEmail.classList.remove('active');
        btnWhatsapp.classList.add('active');
        generarVistaPrevia(); // Actualiza el mensaje si cambia el canal
    });

    // Eventos
    btnCerrarPanel.addEventListener('click', () => {
        panelRedaccion.classList.add('hidden');
        reclamoSeleccionado = null;
    });

    btnEnviarNotificacion.addEventListener('click', () => {
        if (!reclamoSeleccionado) return;
        enviarNotificacion();
    });

    // Enviar notificación
    function enviarNotificacion() {
        const formData = new FormData();
        formData.append('idReclamo', reclamoSeleccionado.idReclamo);
        formData.append('canal', canalActual);
        formData.append('mensaje', vistaPrevia.value.trim());
        
        fetch('/mvc_restaurante/public/index.php?entidad=reclamo&action=enviar-notificacion', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion(data.message, 'success');
                // Eliminar de la lista (simulado, en producción se podría recargar)
                const fila = document.querySelector(`[data-id="${reclamoSeleccionado.idReclamo}"]`)?.closest('tr');
                if (fila) fila.remove();
                // Actualizar contador
                const total = parseInt(totalResueltos.textContent) - 1;
                totalResueltos.textContent = total > 0 ? total : '0';
                // Cerrar panel
                panelRedaccion.classList.add('hidden');
                reclamoSeleccionado = null;
            } else {
                mostrarNotificacion(data.message || 'Error desconocido.', 'error');
            }
        })
        .catch(() => {
            mostrarNotificacion('Error al enviar la notificación.', 'error');
        });
    }

    // Iniciar
    cargarReclamosResueltos();
</script>