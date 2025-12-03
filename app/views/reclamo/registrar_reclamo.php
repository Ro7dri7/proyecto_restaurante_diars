<?php
// Vista: app/views/reclamo/registrar_reclamo.php
// Se renderiza dentro del layout.php
?>

<!-- Enlazamos el CSS externo -->
<link rel="stylesheet" href="/mvc_restaurante/public/css/reclamo/registrar_reclamo.css">

<!-- ICONOS LUCIDE (CDN) -->
<script src="https://unpkg.com/lucide@latest"></script>

<!-- HEADER DEL CONTENIDO -->
<div class="page-header">
    <h2>Registrar Nueva Solicitud</h2>
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

        <!-- PASO 1: IDENTIFICAR CLIENTE -->
        <section id="step1" class="step-section">
            <div class="step-header">
                <h3><span class="step-number">1</span> Identificar Cliente</h3>
                <button id="btn-cambiar-cliente" class="change-link hidden">Cambiar Cliente</button>
            </div>
            <div class="step-body">
                <div id="client-search" class="search-form">
                    <input type="text" id="input-termino" placeholder="Buscar por nombre o email..." />
                    <button id="btn-buscar-cliente">Buscar</button>
                </div>
                <div id="client-display" class="client-display hidden">
                    <div class="client-avatar"><svg width="32" height="32"><use href="#user"/></svg></div>
                    <div class="client-info">
                        <h4 id="cliente-nombre">Nombre Cliente</h4>
                        <div class="client-meta">
                            <span>Email: <span id="cliente-email">email@mail.com</span></span>
                            <span>•</span>
                            <span>Tel: <span id="cliente-telefono">999888777</span></span>
                        </div>
                    </div>
                    <div class="client-status">
                        <span class="status-badge"><svg width="14" height="14"><use href="#check-circle"/></svg> Validado</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- PASO 2: SELECCIONAR PEDIDO -->
        <section id="step2" class="step-section hidden">
            <div class="step-header">
                <h3><span class="step-number">2</span> Seleccionar Pedido Afectado</h3>
                <button id="btn-cambiar-pedido" class="change-link hidden">Cambiar Pedido</button>
            </div>
            <div class="step-body">
                <div id="order-list" class="order-list">
                    <table>
                        <thead>
                            <tr>
                                <th>ID Pedido</th>
                                <th>Fecha</th>
                                <th>Detalle</th>
                                <th>Total</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-pedidos">
                            <!-- Filas generadas dinámicamente -->
                        </tbody>
                    </table>
                </div>
                <div id="pedido-seleccionado" class="selected-order hidden">
                    <div class="order-summary">
                        <div class="order-icon"><svg width="24" height="24"><use href="#shopping-bag"/></svg></div>
                        <div class="order-details">
                            <p>Pedido Seleccionado: <span id="pedido-id">#0000</span></p>
                            <p id="pedido-items">Items del pedido...</p>
                        </div>
                        <div class="order-total">
                            <span id="pedido-total">S/ 0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- PASO 3: DETALLE Y SOLUCIÓN -->
        <section id="step3" class="step-section hidden">
            <div class="step-header">
                <h3><span class="step-number">3</span> Detalle y Solución</h3>
            </div>
            <div class="step-body">
                <form id="formulario-reclamo">
                    <input type="hidden" id="input-id-pedido" />
                    <input type="hidden" id="input-id-cliente" />

                    <div class="form-row">
                        <div class="form-group">
                            <label for="input-producto-afectado">Producto Afectado <span class="required">*</span></label>
                            <input type="text" id="input-producto-afectado" required placeholder="Ej. Lomo Saltado, Bebida..." />
                        </div>
                        <div class="form-group">
                            <label for="select-metodo-devolucion">Solución Esperada (Método) <span class="required">*</span></label>
                            <select id="select-metodo-devolucion" required>
                                <option value="Devolución de Dinero">Devolución de Dinero</option>
                                <option value="Cambio de Producto">Cambio de Producto</option>
                                <option value="Nota de Crédito">Nota de Crédito</option>
                                <option value="Cupón de Descuento">Cupón de Descuento</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row full">
                        <div class="form-group">
                            <label for="textarea-motivo">Motivo del Reclamo <span class="required">*</span></label>
                            <textarea id="textarea-motivo" required rows="4" placeholder="Describa detalladamente el problema con el producto o servicio..."></textarea>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" id="btn-cancelar" class="btn-cancel">Cancelar</button>
                        <button type="submit" id="btn-registrar" class="btn-submit">
                            <svg width="18" height="18"><use href="#save"/></svg>
                            Registrar Solicitud
                        </button>
                    </div>
                </form>
            </div>
        </section>

    </div>
</div>

<!-- LÓGICA JAVASCRIPT EN ESPAÑOL -->
<script>
    // Variables de estado
    let clienteSeleccionado = null;
    let pedidoSeleccionado = null;

    // Elementos del DOM
    const paso1 = document.getElementById('step1');
    const paso2 = document.getElementById('step2');
    const paso3 = document.getElementById('step3');
    const inputTermino = document.getElementById('input-termino');
    const btnBuscar = document.getElementById('btn-buscar-cliente');
    const contenedorCliente = document.getElementById('client-display');
    const btnCambiarCliente = document.getElementById('btn-cambiar-cliente');
    const tablaPedidos = document.getElementById('tabla-pedidos');
    const contenedorPedido = document.getElementById('pedido-seleccionado');
    const btnCambiarPedido = document.getElementById('btn-cambiar-pedido');
    const formulario = document.getElementById('formulario-reclamo');
    const btnCancelar = document.getElementById('btn-cancelar');
    const btnRegistrar = document.getElementById('btn-registrar');
    const notificacion = document.getElementById('notification');
    const mensajeNotificacion = document.getElementById('notification-message');

    // Mostrar notificación
    function mostrarNotificacion(mensaje, tipo = 'error') {
        notificacion.className = `notification ${tipo}`;
        notificacion.style.display = 'flex';
        mensajeNotificacion.textContent = mensaje;
        setTimeout(() => {
            notificacion.classList.add('hidden');
        }, 3000);
    }

    // Reiniciar formulario (solo limpiar campos)
    function reiniciarFormulario() {
        clienteSeleccionado = null;
        pedidoSeleccionado = null;
        inputTermino.value = '';
        document.getElementById('input-producto-afectado').value = '';
        document.getElementById('textarea-motivo').value = '';
        document.getElementById('select-metodo-devolucion').value = 'Devolución de Dinero';

        // Ocultar elementos
        contenedorCliente.classList.add('hidden');
        btnCambiarCliente.classList.add('hidden');
        contenedorPedido.classList.add('hidden');
        btnCambiarPedido.classList.add('hidden');
        tablaPedidos.innerHTML = '';

        // Mostrar solo el paso 1
        paso2.classList.add('hidden');
        paso3.classList.add('hidden');
    }

    // Buscar cliente
    btnBuscar.addEventListener('click', (e) => {
        e.preventDefault();
        const termino = inputTermino.value.trim();
        if (!termino) {
            mostrarNotificacion('Ingrese un nombre o email para buscar.', 'error');
            return;
        }

        fetch(`/mvc_restaurante/public/index.php?entidad=reclamo&action=buscar-cliente&q=${encodeURIComponent(termino)}`)
            .then(response => response.json())
            .then(data => {
                if (data.cliente) {
                    clienteSeleccionado = data.cliente;
                    mostrarCliente();
                    mostrarNotificacion(`Cliente ${clienteSeleccionado.nombreCliente} identificado correctamente.`, 'success');
                } else {
                    mostrarNotificacion('Cliente no encontrado en la Base de Datos.', 'error');
                }
            })
            .catch(() => {
                mostrarNotificacion('Error al buscar cliente. Intente nuevamente.', 'error');
            });
    });

    // Mostrar cliente
    function mostrarCliente() {
        document.getElementById('cliente-nombre').textContent = clienteSeleccionado.nombreCliente;
        document.getElementById('cliente-email').textContent = clienteSeleccionado.emailCliente || '—';
        document.getElementById('cliente-telefono').textContent = clienteSeleccionado.telefonoCliente || 'No registrado';
        contenedorCliente.classList.remove('hidden');
        btnCambiarCliente.classList.remove('hidden');

        // Cargar pedidos
        cargarPedidos();
        paso2.classList.remove('hidden'); // Mostrar paso 2
    }

    // Cargar pedidos del cliente
    function cargarPedidos() {
        if (!clienteSeleccionado.idCliente) return;

        tablaPedidos.innerHTML = '<tr><td colspan="5" class="text-center py-4">Cargando pedidos...</td></tr>';

        fetch(`/mvc_restaurante/public/index.php?entidad=reclamo&action=obtener-pedidos&id=${clienteSeleccionado.idCliente}`)
            .then(response => response.json())
            .then(data => {
                tablaPedidos.innerHTML = '';
                if (data.pedidos && data.pedidos.length > 0) {
                    data.pedidos.forEach(pedido => {
                        const fila = document.createElement('tr');
                        const totalFormateado = `S/ ${parseFloat(pedido.total).toFixed(2)}`;
                        fila.innerHTML = `
                            <td>#${pedido.idPedido}</td>
                            <td>${pedido.fecha}</td>
                            <td title="${pedido.items}">${pedido.items.length > 30 ? pedido.items.substring(0, 30) + '...' : pedido.items}</td>
                            <td>${totalFormateado}</td>
                            <td><button class="action-btn" data-id="${pedido.idPedido}">Seleccionar</button></td>
                        `;
                        tablaPedidos.appendChild(fila);
                    });

                    // Agregar evento a botones
                    document.querySelectorAll('.action-btn').forEach(btn => {
                        btn.addEventListener('click', () => {
                            const idPedido = parseInt(btn.dataset.id);
                            const pedido = data.pedidos.find(p => p.idPedido === idPedido);
                            if (pedido) {
                                pedidoSeleccionado = pedido;
                                mostrarPedido();
                                paso3.classList.remove('hidden'); // Mostrar paso 3
                            }
                        });
                    });
                } else {
                    tablaPedidos.innerHTML = '<tr><td colspan="5" class="text-center py-4">No hay pedidos entregados para este cliente.</td></tr>';
                }
            })
            .catch(() => {
                tablaPedidos.innerHTML = '<tr><td colspan="5" class="text-center py-4">Error al cargar pedidos.</td></tr>';
            });
    }

    // Mostrar pedido seleccionado
    function mostrarPedido() {
        document.getElementById('pedido-id').textContent = `#${pedidoSeleccionado.idPedido}`;
        document.getElementById('pedido-items').textContent = pedidoSeleccionado.items;
        document.getElementById('pedido-total').textContent = `S/ ${parseFloat(pedidoSeleccionado.total).toFixed(2)}`;
        document.getElementById('input-id-pedido').value = pedidoSeleccionado.idPedido;
        document.getElementById('input-id-cliente').value = clienteSeleccionado.idCliente;
        contenedorPedido.classList.remove('hidden');
        btnCambiarPedido.classList.remove('hidden');
    }

    // Eventos de botones
    btnCambiarCliente.addEventListener('click', () => {
        reiniciarFormulario();
        inputTermino.focus();
    });

    btnCambiarPedido.addEventListener('click', () => {
        pedidoSeleccionado = null;
        contenedorPedido.classList.add('hidden');
        btnCambiarPedido.classList.add('hidden');
        paso3.classList.add('hidden'); // Ocultar paso 3
    });

    btnCancelar.addEventListener('click', () => {
        reiniciarFormulario();
    });

    // Enviar reclamo
    formulario.addEventListener('submit', (e) => {
        e.preventDefault();

        const producto = document.getElementById('input-producto-afectado').value.trim();
        const motivo = document.getElementById('textarea-motivo').value.trim();
        const metodo = document.getElementById('select-metodo-devolucion').value;

        if (!producto || !motivo) {
            mostrarNotificacion('Todos los campos son obligatorios.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('idPedido', document.getElementById('input-id-pedido').value);
        formData.append('idCliente', document.getElementById('input-id-cliente').value);
        formData.append('productoAfectado', producto);
        formData.append('motivo', motivo);
        formData.append('metodoDevolucion', metodo);

        fetch('/mvc_restaurante/public/index.php?entidad=reclamo&action=procesar', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion(data.message, 'success');
                setTimeout(reiniciarFormulario, 3000);
            } else {
                mostrarNotificacion(data.message || 'Error desconocido.', 'error');
            }
        })
        .catch(() => {
            mostrarNotificacion('Error al registrar el reclamo.', 'error');
        });
    });

    // Iniciar
    // No hacemos nada aquí porque queremos que el usuario inicie desde cero.
</script>