<?php
// Vista: app/views/reclamo/consultar_estado.php
?>
<!-- Enlazamos el CSS externo -->
<link rel="stylesheet" href="/mvc_restaurante/public/css/reclamo/consultar_estado.css">
<!-- ICONOS LUCIDE (CDN) -->
<script src="https://unpkg.com/lucide@latest"></script>
<!-- HEADER DEL CONTENIDO -->
<div class="page-header">
    <h2>Consultar Estado de Reclamos</h2>
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
        <!-- FILTROS DE BÚSQUEDA -->
        <section id="filters-section" class="step-section">
            <div class="step-header">
                <h3><span class="step-number">1</span> Filtros de Búsqueda</h3>
                <button id="btn-limpiar-filtros" class="change-link">Limpiar Filtros</button>
            </div>
            <div class="step-body">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="input-search">Buscar por ID, Cliente o Producto...</label>
                        <div class="search-input-wrapper">
                            <svg width="16" height="16"><use href="#search"/></svg>
                            <input type="text" id="input-search" placeholder="Buscar..." />
                        </div>
                    </div>
                    <div class="filter-group">
                        <label for="select-status">Estado</label>
                        <select id="select-status">
                            <option value="Todos">Todos los Estados</option>
                            <option value="Solicitado">Solicitado</option>
                            <option value="Validado">Validado</option>
                            <option value="Rechazado">Rechazado</option>
                            <option value="Notificado">Notificado</option>
                            <option value="Reembolsado">Reembolsado</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="input-date-start">Fecha Inicio</label>
                        <input type="date" id="input-date-start" />
                    </div>
                    <div class="filter-group">
                        <label for="input-date-end">Fecha Fin</label>
                        <input type="date" id="input-date-end" />
                    </div>
                </div>
            </div>
        </section>

        <!-- RESULTADOS DE LA BÚSQUEDA -->
        <section id="results-section" class="step-section">
            <div class="step-header">
                <h3><span class="step-number">2</span> Resultados de la Búsqueda <span id="total-registros" class="badge">0</span></h3>
            </div>
            <div class="step-body">
                <div id="reclamos-lista" class="order-list">
                    <table>
                        <thead>
                            <tr>
                                <th>ID Ticket</th>
                                <th>Cliente</th>
                                <th>Fecha Reg.</th>
                                <th>Producto</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-resultados">
                            <!-- Filas generadas dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
    // Variables de estado
    let allClaims = [];
    let filteredClaims = [];

    // Elementos del DOM
    const inputSearch = document.getElementById('input-search');
    const selectStatus = document.getElementById('select-status');
    const inputDateStart = document.getElementById('input-date-start');
    const inputDateEnd = document.getElementById('input-date-end');
    const btnLimpiarFiltros = document.getElementById('btn-limpiar-filtros');
    const tablaResultados = document.getElementById('tabla-resultados');
    const totalRegistros = document.getElementById('total-registros');
    const notification = document.getElementById('notification');
    const mensajeNotificacion = document.getElementById('notification-message');

    // Mostrar notificación
    function mostrarNotificacion(mensaje, tipo = 'error') {
        if (!notification) return; // Si no existe, no hacemos nada
        notification.className = `notification ${tipo}`;
        notification.style.display = 'flex';
        mensajeNotificacion.textContent = mensaje;
        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }

    // Cargar todos los reclamos
    function cargarTodosLosReclamos() {
        tablaResultados.innerHTML = '<tr><td colspan="6" class="text-center py-4">Cargando reclamos...</td></tr>';
        fetch('/mvc_restaurante/public/index.php?entidad=reclamo&action=obtener-todos')
            .then(response => response.json())
            .then(data => {
                allClaims = data.reclamos || [];
                aplicarFiltros(); // Aplicamos filtros iniciales (todos)
            })
            .catch(() => {
                tablaResultados.innerHTML = '<tr><td colspan="6" class="text-center py-4">Error al cargar los reclamos.</td></tr>';
            });
    }

    // Aplicar filtros
    function aplicarFiltros() {
        const searchQuery = inputSearch.value.trim().toLowerCase();
        const statusFilter = selectStatus.value;
        const dateStart = inputDateStart.value;
        const dateEnd = inputDateEnd.value;

        filteredClaims = allClaims.filter(claim => {
            // Filtro por texto
            const matchesSearch = !searchQuery || 
                claim.idReclamo.toLowerCase().includes(searchQuery) ||
                claim.clienteNombre.toLowerCase().includes(searchQuery) ||
                claim.productoAfectado.toLowerCase().includes(searchQuery);

            // Filtro por estado
            const matchesStatus = statusFilter === 'Todos' || claim.estadoReclamo === statusFilter;

            // Filtro por fecha inicio
            const matchesDateStart = !dateStart || new Date(claim.fechaSolicitud) >= new Date(dateStart);

            // Filtro por fecha fin
            const matchesDateEnd = !dateEnd || new Date(claim.fechaSolicitud) <= new Date(dateEnd);

            return matchesSearch && matchesStatus && matchesDateStart && matchesDateEnd;
        });

        renderizarTabla();
    }

    // Renderizar la tabla
    function renderizarTabla() {
        tablaResultados.innerHTML = '';
        totalRegistros.textContent = filteredClaims.length;

        if (filteredClaims.length === 0) {
            tablaResultados.innerHTML = '<tr><td colspan="6" class="text-center py-4">No se encontraron reclamos con los filtros actuales.</td></tr>';
            return;
        }

        filteredClaims.forEach(claim => {
            const fila = document.createElement('tr');
            const fecha = new Date(claim.fechaSolicitud).toLocaleDateString();
            const estadoClase = getEstadoClass(claim.estadoReclamo);
            fila.innerHTML = `
                <td>#${claim.idReclamo}</td>
                <td>${claim.clienteNombre}</td>
                <td>${fecha}</td>
                <td>${claim.productoAfectado}</td>
                <td><span class="${estadoClase}">${claim.estadoReclamo}</span></td>
                <td><button class="action-btn" data-id="${claim.idReclamo}" title="Ver Detalle"><svg width="16" height="16"><use href="#eye"/></svg></button></td>
            `;
            tablaResultados.appendChild(fila);
        });

        // Agregar evento a botones de ver detalle (opcional)
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const idReclamo = parseInt(btn.dataset.id);
                mostrarDetalleReclamo(idReclamo);
            });
        });
    }

    // Obtener clase CSS para el estado
    function getEstadoClass(estado) {
        switch (estado) {
            case 'Solicitado': return 'estado-solicitado';
            case 'Validado': return 'estado-validado';
            case 'Rechazado': return 'estado-rechazado';
            case 'Notificado': return 'estado-notificado';
            case 'Reembolsado': return 'estado-reembolsado';
            default: return 'estado-otro';
        }
    }

    // Mostrar detalle de un reclamo (opcional, solo muestra una notificación)
    function mostrarDetalleReclamo(idReclamo) {
        const claim = allClaims.find(c => c.idReclamo === idReclamo);
        if (claim) {
            const message = `ID: #${claim.idReclamo}\nCliente: ${claim.clienteNombre}\nProducto: ${claim.productoAfectado}\nEstado: ${claim.estadoReclamo}\nFecha: ${new Date(claim.fechaSolicitud).toLocaleString()}`;
            mostrarNotificacion(`Detalles del reclamo:\n\n${message}`, 'success');
        } else {
            mostrarNotificacion('Reclamo no encontrado.', 'error');
        }
    }

    // Eventos
    inputSearch.addEventListener('input', aplicarFiltros);
    selectStatus.addEventListener('change', aplicarFiltros);
    inputDateStart.addEventListener('change', aplicarFiltros);
    inputDateEnd.addEventListener('change', aplicarFiltros);

    btnLimpiarFiltros.addEventListener('click', () => {
        inputSearch.value = '';
        selectStatus.value = 'Todos';
        inputDateStart.value = '';
        inputDateEnd.value = '';
        aplicarFiltros();
    });

    // Iniciar
    cargarTodosLosReclamos();
</script>