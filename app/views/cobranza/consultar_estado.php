<?php
// app/views/cobranza/consultar_estado.php

// Recibir los datos del controlador con valores por defecto para evitar errores
$orden = $orden ?? null;
$mensajeError = $mensajeError ?? '';
$pedidosLista = $pedidosLista ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Estado de Cobranza</title>
    <!-- Carga de Iconos -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <!-- CSS -->
    <link rel="stylesheet" href="/mvc_restaurante/public/css/cobranza/consultar_estado.css">
</head>
<body>

    <header class="header-main">
        <div class="header-left">
            <a href="/mvc_restaurante/public/" class="btn-regresar">
                <ion-icon name="arrow-back-outline"></ion-icon> Dashboard
            </a>
            <div class="titulo-pagina">
                <ion-icon name="receipt-outline"></ion-icon>
                <h1>Gestión de Cobranza</h1>
            </div>
        </div>
        <div class="usuario-info">
            <span>Sistema Restaurante</span>
            <small><?= date('d/m/Y') ?></small>
        </div>
    </header>

    <div class="contenedor-layout">

        <!-- Barra de Búsqueda -->
        <div class="barra-busqueda">
            <form method="GET" action="/mvc_restaurante/public/index.php" class="search-box">
                <input type="hidden" name="entidad" value="cobranza">
                <input type="hidden" name="action" value="consultar_estado">
                
                <div class="icono-busqueda">
                    <ion-icon name="search-outline"></ion-icon>
                </div>
                <input type="text" name="query" id="input-busqueda" class="input-search" 
                       placeholder="Ingrese N° de Orden, Mesa o Cliente..." 
                       value="<?= htmlspecialchars($_GET['query'] ?? '') ?>" autocomplete="off">
                <button type="submit" class="btn-buscar">Consultar</button>
            </form>
        </div>

        <!-- Filtro de Fechas -->
        <div class="filtro-fechas" style="margin-bottom: 20px; display: flex; justify-content: center;">
            <form method="GET" action="/mvc_restaurante/public/index.php" class="form-filtro" style="display: flex; gap: 10px; align-items: center; background: white; padding: 10px 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <input type="hidden" name="entidad" value="cobranza">
                <input type="hidden" name="action" value="consultar_estado">
                
                <div style="display: flex; align-items: center; gap: 5px;">
                    <label for="fechaInicio" style="font-weight: 500; color: #555;">Desde:</label>
                    <input type="date" name="fechaInicio" id="fechaInicio" value="<?= htmlspecialchars($_GET['fechaInicio'] ?? '') ?>" style="padding: 5px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                
                <div style="display: flex; align-items: center; gap: 5px;">
                    <label for="fechaFin" style="font-weight: 500; color: #555;">Hasta:</label>
                    <input type="date" name="fechaFin" id="fechaFin" value="<?= htmlspecialchars($_GET['fechaFin'] ?? '') ?>" style="padding: 5px; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <button type="submit" class="btn-filtrar" style="background-color: #6c757d; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; display: flex; align-items: center; gap: 5px;">
                    <ion-icon name="filter-outline"></ion-icon> Filtrar
                </button>
                
                <?php if(!empty($_GET['fechaInicio']) || !empty($_GET['fechaFin'])): ?>
                    <a href="/mvc_restaurante/public/index.php?entidad=cobranza&action=consultar_estado" style="color: #d9534f; text-decoration: none; font-size: 0.9em; display: flex; align-items: center;">
                        <ion-icon name="close-circle-outline"></ion-icon> Limpiar
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($mensajeError): ?>
            <!-- Mensaje de Error -->
            <div class="mensaje-error fade-in">
                <div class="icono-error">
                    <ion-icon name="alert-circle-outline"></ion-icon>
                </div>
                <h3><?= htmlspecialchars($mensajeError) ?></h3>
                <p>Intente con el número de orden (ej. 1001) o nombre del cliente.</p>
            </div>

        <?php elseif ($orden): ?>
            <!-- CASO 1: DETALLE DE UNA ORDEN ENCONTRADA -->
            <div class="tarjeta-resultado fade-in">
                
                <!-- Cabecera -->
                <div class="cabecera-orden">
                    <div class="info-orden-izq">
                        <div class="titulo-orden">
                            <!-- Validamos ID y Estado -->
                            <?php 
                                $idPrincipal = $orden['id'] ?? $orden['idPedido'] ?? '---';
                                $estadoPrincipal = $orden['estado'] ?? $orden['estadoPedido'] ?? 'Desconocido';
                                $st = strtoupper($estadoPrincipal); // Normalizar para comparaciones
                            ?>
                            <h2>Orden #<?= htmlspecialchars($idPrincipal) ?></h2>
                            
                            <span class="badge-estado <?= strtolower($estadoPrincipal) ?>">
                                <?= htmlspecialchars($estadoPrincipal) ?>
                            </span>
                        </div>
                        <div class="meta-orden">
                            <span><ion-icon name="time-outline"></ion-icon> <?= $orden['fecha'] ?? date('d/m/Y') ?></span>
                            <span><ion-icon name="restaurant-outline"></ion-icon> <?= htmlspecialchars($orden['mesa'] ?? '---') ?></span>
                        </div>
                    </div>
                    <div class="info-orden-der">
                        <span class="label-total">Total Orden</span>
                        <div class="monto-total">S/ <?= number_format($orden['total'] ?? 0, 2) ?></div>
                    </div>
                </div>

                <!-- Cuerpo -->
                <div class="cuerpo-resultado">
                    <!-- Columna Cliente -->
                    <div class="columna-cliente">
                        <h3 class="titulo-seccion">Cliente</h3>
                        <div class="datos-cliente">
                            <p class="nombre-cliente"><?= htmlspecialchars($orden['cliente'] ?? $orden['nombreCliente'] ?? 'Cliente General') ?></p>
                            <p class="doc-cliente"><?= htmlspecialchars($orden['doc'] ?? '---') ?></p>
                        </div>
                        <div class="info-extra-cliente">
                            <ion-icon name="information-circle"></ion-icon>
                            <p>Historial crediticio: Bueno.<br>Frecuencia: Alta.</p>
                        </div>
                    </div>

                    <!-- Columna Historial -->
                    <div class="columna-historial">
                        <h3 class="titulo-seccion">Detalle de Cobranza</h3>
                        
                        <div class="timeline-eventos">
                            <?php if (!empty($orden['historial'])): ?>
                                <?php foreach ($orden['historial'] as $idx => $evento): ?>
                                    <?php 
                                        // Determinar iconos y colores según tipo
                                        $icon = 'ellipse'; $colorClass = 'gris';
                                        $tipo = $evento['tipo'] ?? '';
                                        if($tipo == 'creacion') { $icon='clipboard-outline'; $colorClass='gris'; }
                                        if($tipo == 'preparacion') { $icon='restaurant-outline'; $colorClass='azul'; }
                                        if($tipo == 'pago') { $icon='checkmark-circle'; $colorClass='verde'; }
                                        if($tipo == 'anulacion') { $icon='close-circle'; $colorClass='rojo'; }
                                        $isLast = $idx === count($orden['historial']) - 1;
                                    ?>
                                    <div class="evento-timeline">
                                        <div class="col-icono">
                                            <div class="icono-timeline <?= $colorClass ?>">
                                                <ion-icon name="<?= $icon ?>"></ion-icon>
                                            </div>
                                            <?php if(!$isLast): ?><div class="linea-conectora"></div><?php endif; ?>
                                        </div>
                                        <div class="contenido-evento">
                                            <div class="desc-evento"><?= htmlspecialchars($evento['desc'] ?? '') ?></div>
                                            <div class="fecha-evento"><?= $evento['fecha'] ?? '' ?></div>
                                            
                                            <!-- Detalles Extra: Pago -->
                                            <?php if(isset($evento['metodo'])): ?>
                                                <div class="detalle-box verde">
                                                    <p><strong>Método:</strong> <?= htmlspecialchars($evento['metodo']) ?></p>
                                                    <p><strong>Comprobante:</strong> <?= htmlspecialchars($evento['comprobante']) ?></p>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Detalles Extra: Anulación -->
                                            <?php if(isset($evento['motivo'])): ?>
                                                <div class="detalle-box rojo">
                                                    <p><strong>Motivo:</strong> <?= htmlspecialchars($evento['motivo']) ?></p>
                                                    <p><strong>Autorizado:</strong> <?= htmlspecialchars($evento['autorizado'] ?? 'Supervisor') ?></p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No hay historial disponible.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="acciones-cobranza">
                            <?php if ($st === 'PENDIENTE' || $st === 'REGISTRADO' || $st === 'ENTREGADO'): ?>
                                <a href="/mvc_restaurante/public/index.php?entidad=cobranza&action=registrar_pago&query=<?= urlencode($idPrincipal) ?>" class="btn-accion verde">
                                    <ion-icon name="cash-outline"></ion-icon> Ir a Pagar
                                </a>
                            <?php elseif ($st === 'PAGADO'): ?>
                                <!-- Botón "Generar Boleta" que redirige a comprobantes.php -->
                                <a href="/mvc_restaurante/public/index.php?entidad=cobranza&action=comprobantes&query=<?= urlencode($idPrincipal) ?>" class="btn-accion azul">
                                    <ion-icon name="receipt-outline"></ion-icon> Generar Boleta
                                </a>
                                <button class="btn-accion rojo" onclick="abrirModalAnulacion(<?= $idPrincipal ?>)">
                                    <ion-icon name="arrow-undo-outline"></ion-icon> Anular Pago
                                </button>
                            <?php elseif ($st === 'FACTURADO'): ?>
                                <!-- Botón "Ver Comprobante" que muestra la boleta ya generada usando el modal -->
                                <button class="btn-accion azul" onclick="verComprobante(<?= htmlspecialchars(json_encode($orden), ENT_QUOTES, 'UTF-8') ?>)">
                                    <ion-icon name="document-text-outline"></ion-icon> Ver Comprobante
                                </button>
                                <!-- No mostramos el botón de anulación si está facturado -->
                            <?php elseif ($st === 'ANULADO'): ?>
                                <span class="mensaje-anulado">
                                    <ion-icon name="ban-outline"></ion-icon> Orden anulada.
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Footer: Tabla de Items -->
                <div class="footer-items">
                    <details>
                        <summary>Ver productos consumidos (<?= count($orden['items'] ?? []) ?> items) <ion-icon name="chevron-down-outline"></ion-icon></summary>
                        <div class="tabla-container">
                            <table class="tabla-items">
                                <tbody>
                                    <?php if(!empty($orden['items'])): ?>
                                        <?php foreach ($orden['items'] as $item): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['nombreProducto']) ?></td>
                                                <td class="text-right">S/ <?= number_format($item['precioUnitario'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </details>
                </div>
            </div>

        <?php else: ?>
            <!-- CASO 2: LISTADO INICIAL (ÚLTIMOS PEDIDOS) -->
            <div class="estado-inicial fade-in">
                <div class="icono-inicial">
                    <ion-icon name="wallet-outline"></ion-icon>
                </div>
                <h3>Últimos Pedidos Entregados / Pagados / Facturados</h3>
                <p>Seleccione un pedido de la lista para ver su estado detallado.</p>
            </div>

            <!-- Lista de Pedidos Recientes -->
            <div class="lista-pedidos">
                <?php if (empty($pedidosLista)): ?>
                    <div class="mensaje-vacio">
                        <p>No se encontraron pedidos recientes con los criterios seleccionados.</p>
                    </div>
                <?php else: ?>
                    <div class="grid-pedidos">
                        <?php foreach ($pedidosLista as $pedido): ?>
                            <?php 
                                // Intentamos obtener el estado de varias formas comunes
                                $estado = $pedido['estadoPedido'] ?? $pedido['estado'] ?? $pedido['ESTADO'] ?? $pedido['estado_pedido'] ?? 'Desconocido';
                                $claseEstado = strtolower($estado);
                                
                                // Intentamos obtener el ID
                                $idPedido = $pedido['idPedido'] ?? $pedido['id'] ?? '---';

                                // Intentamos obtener la mesa
                                $mesa = $pedido['mesa'] ?? $pedido['numeroMesa'] ?? '---';
                            ?>
                            <div class="tarjeta-pedido" onclick="location.href='/mvc_restaurante/public/index.php?entidad=cobranza&action=consultar_estado&query=<?= urlencode($idPedido) ?>'">
                                <div class="info-pedido">
                                    <div class="titulo-pedido">
                                        <h4>Orden #<?= htmlspecialchars($idPedido) ?></h4>
                                        <span class="badge-estado <?= $claseEstado ?>">
                                            <?= htmlspecialchars($estado) ?>
                                        </span>
                                    </div>
                                    <div class="meta-pedido">
                                        <span><ion-icon name="time-outline"></ion-icon> <?= date('d/m/Y H:i', strtotime($pedido['fechaHoraToma'])) ?></span>
                                        <span><ion-icon name="restaurant-outline"></ion-icon> Mesa <?= htmlspecialchars($mesa) ?></span>
                                    </div>
                                    <div class="cliente-pedido">
                                        <strong><?= htmlspecialchars($pedido['nombreCliente']) ?></strong>
                                    </div>
                                    <div class="total-pedido">
                                        Total: S/ <?= number_format($pedido['total'], 2) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>

    <!-- Modal Anular Pago -->
    <div id="modal-anular" class="modal-anular">
        <div class="modal-anular-content">
            <div class="modal-header">
                <div class="titulo-modal">
                    <div class="icono-warning"><ion-icon name="warning-outline"></ion-icon></div>
                    <div>
                        <h3>Anular Cobro</h3>
                        <span>Esta acción es irreversible</span>
                    </div>
                </div>
                <button class="btn-cerrar" onclick="cerrarModalAnulacion()"><ion-icon name="close-outline"></ion-icon></button>
            </div>
            
            <form id="form-anular" method="POST" action="/mvc_restaurante/public/index.php?entidad=cobranza&action=anular_pago">
                <div class="modal-body">
                    <p class="texto-confirmacion">Está a punto de anular el pago de la <strong>Orden #<span id="modal-anular-id">---</span></strong>.</p>
                    <input type="hidden" name="idPedido" id="modal-anular-id-input">
                    
                    <div class="form-group">
                        <label>Motivo de Anulación</label>
                        <select name="motivo" id="input-motivo-anulacion" required>
                            <option value="">Seleccione un motivo...</option>
                            <option value="Error de Digitación">Error de Digitación</option>
                            <option value="Devolución de Pedido">Devolución de Pedido</option>
                            <option value="Doble Cobro">Doble Cobro</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Clave de Supervisor</label>
                        <input type="password" name="claveSupervisor" id="input-clave-supervisor" placeholder="Ingrese clave..." required>
                    </div>
                    
                    <div id="error-msg" class="error-msg" style="display:none;">
                        <ion-icon name="alert-circle"></ion-icon> Complete todos los campos.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancelar" onclick="cerrarModalAnulacion()">Cancelar</button>
                    <button type="button" class="btn-confirmar" onclick="confirmarAnulacion()">Confirmar Anulación</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL VISTA PREVIA BOLETA -->
    <div id="modal-pdf" class="modal">
        <div class="modal-content a4-wrapper">
            <div class="modal-header">
                <h3 id="pdf-header-title">Vista Previa</h3>
                <div class="modal-actions">
                    <button onclick="window.print()"><ion-icon name="print-outline"></ion-icon></button>
                    <button class="btn-cerrar"><ion-icon name="close-outline"></ion-icon></button>
                </div>
            </div>
            <div class="a4-page">
                <div class="invoice-header">
                    <div class="empresa-info">
                        <h2>D'ALICIAS COCINA</h2>
                        <p>Av. Principal 123, Lima</p>
                        <p>RUC: 20123456789</p>
                    </div>
                    <div class="invoice-data">
                        <div class="recuadro-ruc">
                            <h3>BOLETA DE VENTA</h3>
                            <p id="pdf-serie">B001-000000</p>
                        </div>
                    </div>
                </div>
                <div class="cliente-info">
                    <p><strong>Cliente:</strong> <span id="pdf-cliente">---</span></p>
                    <p><strong>Fecha:</strong> <span id="pdf-fecha">---</span></p>
                    <p><strong>DNI/RUC:</strong> <span id="pdf-doc">---</span></p>
                </div>
                <table class="invoice-items">
                    <thead>
                        <tr>
                            <th>Cant.</th>
                            <th>Descripción</th>
                            <th align="right">P. Unit</th>
                            <th align="right">Total</th>
                        </tr>
                    </thead>
                    <tbody id="pdf-items-body">
                    </tbody>
                </table>
                <div class="invoice-total">
                    <div class="fila-total">
                        <span>OP. GRAVADA</span>
                        <span id="pdf-subtotal">S/ 0.00</span>
                    </div>
                    <div class="fila-total">
                        <span>IGV (18%)</span>
                        <span id="pdf-igv">S/ 0.00</span>
                    </div>
                    <div class="fila-total grand-total">
                        <span>TOTAL A PAGAR</span>
                        <span id="pdf-total">S/ 0.00</span>
                    </div>
                </div>
                <div class="invoice-footer">
                    <p>¡Gracias por su preferencia!</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function abrirModalAnulacion(id) {
            document.getElementById('modal-anular-id').innerText = id;
            document.getElementById('modal-anular-id-input').value = id;
            document.getElementById('modal-anular').classList.add('active');
        }

        function cerrarModalAnulacion() {
            document.getElementById('modal-anular').classList.remove('active');
            document.getElementById('error-msg').style.display = 'none';
        }

        function confirmarAnulacion() {
            const motivo = document.getElementById('input-motivo-anulacion').value;
            const clave = document.getElementById('input-clave-supervisor').value;
            
            if(!motivo || !clave) {
                document.getElementById('error-msg').style.display = 'flex';
                return;
            }
            document.getElementById('form-anular').submit();
        }

        // Función corregida para ver el comprobante
        function verComprobante(orden) {
            // 1. Llenar cabecera con validación y fallback
            const idPago = orden.idPago || orden.idPedido || orden.id || '0';
            document.getElementById('pdf-serie').innerText = 'B001-' + String(idPago).padStart(6, '0');
            
            document.getElementById('pdf-cliente').innerText = orden.nombreCliente || orden.cliente || 'Cliente General';
            document.getElementById('pdf-doc').innerText = orden.doc || '---';
            document.getElementById('pdf-fecha').innerText = orden.fechaHora || orden.fecha || new Date().toLocaleDateString();

            // 2. Calcular montos
            const total = parseFloat(orden.monto || orden.total || 0);
            let subtotal, igv;

            if (orden.subtotalGeneral !== undefined && orden.igv !== undefined) {
                subtotal = parseFloat(orden.subtotalGeneral);
                igv = parseFloat(orden.igv);
            } else {
                subtotal = total / 1.18;
                igv = total - subtotal;
            }

            // 3. Mostrar totales formateados
            document.getElementById('pdf-total').innerText = 'S/ ' + total.toFixed(2);
            document.getElementById('pdf-subtotal').innerText = 'S/ ' + subtotal.toFixed(2);
            document.getElementById('pdf-igv').innerText = 'S/ ' + igv.toFixed(2);

            // 4. Llenar tabla de items
            const tbody = document.getElementById('pdf-items-body');
            tbody.innerHTML = '';
            
            if (orden.items && orden.items.length > 0) {
                orden.items.forEach(item => {
                    const precio = parseFloat(item.precioUnitario || 0);
                    const cantidad = parseInt(item.cantidad || 1);
                    const subtotalItem = item.subtotal ? parseFloat(item.subtotal) : (precio * cantidad);

                    const row = `
                        <tr>
                            <td>${cantidad}</td>
                            <td>${item.nombreProducto}</td>
                            <td align="right">S/ ${precio.toFixed(2)}</td>
                            <td align="right">S/ ${subtotalItem.toFixed(2)}</td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="4" align="center">Detalles no disponibles</td></tr>';
            }

            // 5. Mostrar Modal
            const modal = document.getElementById('modal-pdf');
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);
        }

        // Cerrar Modal PDF
        document.querySelector('#modal-pdf .btn-cerrar').addEventListener('click', () => {
            const modal = document.getElementById('modal-pdf');
            modal.classList.remove('show');
            setTimeout(() => modal.style.display = 'none', 300);
        });

        // Auto-focus búsqueda
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('input-busqueda');
            if(input && !input.value) input.focus();
        });
    </script>
</body>
</html>