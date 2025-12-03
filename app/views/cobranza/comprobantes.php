<?php
// app/views/cobranza/comprobantes.php

// Recibir datos preparados por el controlador
$comprobantes = $comprobantes ?? [];
$mensaje = $_GET['mensaje'] ?? '';
$ordenParaVistaPrevia = $ordenParaVistaPrevia ?? null; // ✅ Recibido del controlador
$idPedidoSeleccionado = $_GET['query'] ?? null;

// Calcular total de hoy para las tarjetas superiores (opcional)
$montoHoy = 0;
$fechaHoy = date('Y-m-d');
foreach($comprobantes as $c) {
    if(strpos($c['fechaHora'], $fechaHoy) === 0) {
        $montoHoy += floatval($c['montoRecibido'] ?? $c['monto']);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobantes Digitales</title>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <link rel="stylesheet" href="/mvc_restaurante/public/css/cobranza/comprobantes.css">
    <style>
        /* Estilo para indicar que la fila es clickeable */
        tbody tr { cursor: pointer; transition: background 0.2s; }
        tbody tr:hover { background-color: #f1f5f9; }
        /* Para que la fila seleccionada se vea diferente */
        tbody tr.selected { background-color: #e0f2fe; border-left: 4px solid #0284c7; }
    </style>
</head>
<body>

    <header class="header-main">
        <div class="header-left">
            <a href="/mvc_restaurante/public/" class="btn-regresar">
                <ion-icon name="arrow-back-outline"></ion-icon> Dashboard
            </a>
            <div class="titulo-pagina">
                <ion-icon name="receipt-outline"></ion-icon>
                <h1>Comprobantes Digitales</h1>
            </div>
        </div>
        <div class="usuario-info">
            <span>Sistema Restaurante</span>
            <small><?= date('d/m/Y') ?></small>
        </div>
    </header>

    <div class="contenedor-layout">

        <?php if ($mensaje): ?>
            <div class="mensaje-exito fade-in">
                <div class="icono-exito">
                    <ion-icon name="checkmark-circle-outline"></ion-icon>
                </div>
                <h3><?= htmlspecialchars(str_replace('+', ' ', $mensaje)) ?></h3>
            </div>
        <?php endif; ?>

        <div class="stats-container">
            <div class="card-stat">
                <span class="stat-label">Emitidos Total</span>
                <span class="stat-number"><?= isset($comprobantes) ? count($comprobantes) : 0 ?></span>
            </div>
            <div class="card-stat stat-green">
                <span class="stat-label">Ingresos Hoy</span>
                <span class="stat-number">S/ <?= number_format($montoHoy, 2) ?></span>
            </div>
        </div>

        <div class="panel-blanco">
            
            <!-- SECCIÓN VISTA PREVIA (Solo si hay un pedido seleccionado) -->
            <?php if ($ordenParaVistaPrevia): ?>
                <div class="vista-previa-pedido fade-in" style="border: 2px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 20px; background: #f8fafc;">
                    <div class="vista-previa-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                        <div>
                            <h3 style="margin:0; color:#1e293b;">Vista Previa del Pedido #<?= htmlspecialchars($ordenParaVistaPrevia['idPedido']) ?></h3>
                            <small style="color:#64748b;">Fecha: <?= date('d/m/Y H:i', strtotime($ordenParaVistaPrevia['fechaHoraToma'])) ?></small>
                        </div>
                        <span class="badge-estado <?= strtolower($ordenParaVistaPrevia['estadoPedido']) ?>">
                            <?= htmlspecialchars($ordenParaVistaPrevia['estadoPedido']) ?>
                        </span>
                    </div>

                    <!-- Resumen de Cliente y Total -->
                    <div class="vista-previa-body" style="display:flex; gap:40px; margin-bottom:20px;">
                        <div class="info-cliente">
                            <p style="margin:5px 0;"><strong>Cliente:</strong> <?= htmlspecialchars($ordenParaVistaPrevia['nombreCliente']) ?></p>
                            <p style="margin:5px 0;"><strong>Email:</strong> <?= htmlspecialchars($ordenParaVistaPrevia['emailCliente'] ?? '---') ?></p>
                        </div>
                        <div class="info-total">
                            <p style="margin:5px 0; font-size:1.2em; color:#0f172a;"><strong>Total: S/ <?= number_format($ordenParaVistaPrevia['total'], 2) ?></strong></p>
                        </div>
                    </div>

                    <!-- Botones: Ver Detalle y Emitir Boleta -->
                    <div class="acciones-vista-previa" style="display:flex; gap:10px;">
                        <!-- Botón "Ver Detalle" -->
                        <button class="btn-accion gris" onclick="mostrarDetalle(<?= htmlspecialchars(json_encode($ordenParaVistaPrevia), ENT_QUOTES, 'UTF-8') ?>)">
                            <ion-icon name="eye-outline"></ion-icon> Ver Detalle Completo
                        </button>

                        <!-- Botón "Emitir Boleta" (Solo si está pagado pero no facturado, o si quieres permitir reimpresión) -->
                        <?php if($ordenParaVistaPrevia['estadoPedido'] === 'Pagado'): ?>
                            <form method="POST" action="/mvc_restaurante/public/index.php?entidad=cobranza&action=generar_boleta_desde_comprobantes" style="display:inline;">
                                <input type="hidden" name="idPedido" value="<?= $ordenParaVistaPrevia['idPedido'] ?>">
                                <button type="submit" class="btn-accion azul">
                                    <ion-icon name="receipt-outline"></ion-icon> Emitir Boleta
                                </button>
                            </form>
                        <?php endif; ?>

                        <!-- Botón Cerrar -->
                        <a href="/mvc_restaurante/public/index.php?entidad=cobranza&action=comprobantes" class="btn-accion gris">
                            <ion-icon name="close-outline"></ion-icon> Cerrar Vista
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="toolbar">
                <div class="search-box">
                    <ion-icon name="search-outline"></ion-icon>
                    <input type="text" id="buscador" placeholder="Buscar por Cliente o Monto...">
                </div>
                <div class="actions-box">
                    <button class="btn-outline"><ion-icon name="filter-outline"></ion-icon> Filtros</button>
                    <button class="btn-primary" onclick="location.reload()"><ion-icon name="refresh-outline"></ion-icon> Actualizar</button>
                </div>
            </div>

            <div class="tabla-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Fecha/Hora</th>
                            <th>N° Pedido</th>
                            <th>Cliente</th>
                            <th>Método</th>
                            <th align="right">Total</th>
                            <th align="center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($comprobantes)): ?>
                            <tr><td colspan="6" align="center">No hay comprobantes registrados.</td></tr>
                        <?php else: ?>
                            <?php foreach($comprobantes as $comp): ?>
                            <?php 
                                // Determinar si esta fila está seleccionada
                                $isSelected = ($idPedidoSeleccionado == $comp['idPedido']) ? 'selected' : '';
                            ?>
                            <tr class="<?= $isSelected ?>" 
                                onclick="window.location.href='/mvc_restaurante/public/index.php?entidad=cobranza&action=comprobantes&query=<?= $comp['idPedido'] ?>'">
                                
                                <td><?= date('d/m/Y H:i', strtotime($comp['fechaHora'])) ?></td>
                                <td><span class="badge-pedido">#<?= $comp['idPedido'] ?></span></td>
                                <td>
                                    <div class="col-cliente">
                                        <strong><?= htmlspecialchars($comp['nombreCliente']) ?></strong>
                                        <small><?= $comp['docIdentidad'] ?: '---' ?></small>
                                    </div>
                                </td>
                                <td><span class="badge-metodo"><?= $comp['metodoPago'] ?></span></td>
                                <td align="right" class="font-bold">S/ <?= number_format($comp['monto'], 2) ?></td>
                                <td align="center">
                                    <div class="btn-group">
                                        <!-- Botón Ver Detalle (con stopPropagation) -->
                                        <button class="btn-icon" 
                                                onclick='event.stopPropagation(); mostrarDetalle(<?= htmlspecialchars(json_encode($comp), ENT_QUOTES, 'UTF-8') ?>)' 
                                                title="Ver Detalle Rápido">
                                            <ion-icon name="eye-outline"></ion-icon>
                                        </button>
                                        
                                        <!-- Botón Enviar Email (con stopPropagation) -->
                                        <button class="btn-icon" onclick="event.stopPropagation();" title="Enviar Email">
                                            <ion-icon name="mail-outline"></ion-icon>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="footer-tabla">
                <span>Mostrando <?= isset($comprobantes) ? count($comprobantes) : 0 ?> registros</span>
                <div class="paginacion">
                    <button disabled>&lt;</button>
                    <button class="active">1</button>
                    <button>2</button>
                    <button>&gt;</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalle (Oculto por defecto) -->
    <div id="modal-detalle" class="modal-detalle" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
        <div class="modal-detalle-content" style="background:white; padding:20px; border-radius:8px; width:90%; max-width:500px; max-height:80vh; overflow-y:auto;">
            <div class="modal-header" style="display:flex; justify-content:space-between; margin-bottom:15px;">
                <h3 id="modal-titulo-detalle">Detalle del Pedido</h3>
                <button class="btn-cerrar" style="background:none; border:none; font-size:1.5rem; cursor:pointer;" onclick="cerrarModalDetalle()"><ion-icon name="close-outline"></ion-icon></button>
            </div>
            <div class="modal-body">
                <!-- Tabla de Items -->
                <table class="tabla-items-detalle" style="width:100%; border-collapse:collapse;">
                    <thead style="background:#f1f5f9;">
                        <tr>
                            <th align="left" style="padding:8px;">Cant.</th>
                            <th align="left" style="padding:8px;">Descripción</th>
                            <th align="right" style="padding:8px;">P. Unit</th>
                            <th align="right" style="padding:8px;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="detalle-items-body">
                        <!-- Se llenará con JavaScript -->
                    </tbody>
                </table>

                <!-- Totales -->
                <div class="resumen-totales" style="margin-top:20px; border-top:1px solid #ddd; padding-top:10px;">
                    <div class="fila-total" style="display:flex; justify-content:space-between;">
                        <span>Subtotal:</span>
                        <span id="detalle-subtotal">S/ 0.00</span>
                    </div>
                    <div class="fila-total" style="display:flex; justify-content:space-between;">
                        <span>IGV (18%):</span>
                        <span id="detalle-igv">S/ 0.00</span>
                    </div>
                    <div class="fila-total grand-total" style="display:flex; justify-content:space-between; font-weight:bold; font-size:1.1em; margin-top:5px;">
                        <span>Total a Pagar:</span>
                        <span id="detalle-total">S/ 0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL VISTA PREVIA BOLETA (Para el futuro si se necesita imprimir) -->
    <div id="modal-pdf" class="modal" style="display:none;">
        <div class="modal-content a4-wrapper">
            <!-- Contenido PDF aquí si se requiere -->
        </div>
    </div>

    <script>
        // --- LÓGICA DEL MODAL "VER DETALLE" ---
        function mostrarDetalle(orden) {
            console.log("Datos recibidos:", orden); // Para depuración
            const tbody = document.getElementById('detalle-items-body');
            const subtotalEl = document.getElementById('detalle-subtotal');
            const igvEl = document.getElementById('detalle-igv');
            const totalEl = document.getElementById('detalle-total');
            const tituloEl = document.getElementById('modal-titulo-detalle');

            // Limpiar tabla
            tbody.innerHTML = '';

            if (orden.idPedido) {
                tituloEl.innerText = 'Detalle del Pedido #' + orden.idPedido;
            }

            // Verificar si el objeto tiene la propiedad items
            if (orden && orden.items && orden.items.length > 0) {
                let subtotalGeneral = 0;
                
                orden.items.forEach(item => {
                    const precio = parseFloat(item.precioUnitario);
                    const cantidad = parseFloat(item.cantidad);
                    const subtotalItem = item.subtotal ? parseFloat(item.subtotal) : (precio * cantidad);
                    
                    subtotalGeneral += subtotalItem;

                    const row = `
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:8px;">${cantidad}</td>
                            <td style="padding:8px;">${item.nombreProducto}</td>
                            <td align="right" style="padding:8px;">S/ ${precio.toFixed(2)}</td>
                            <td align="right" style="padding:8px;">S/ ${subtotalItem.toFixed(2)}</td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });

                // Cálculos (asumiendo que los precios son incluidos IGV)
                // Si el backend envía 'total' usamos eso, sino calculamos
                let totalFinal = orden.total ? parseFloat(orden.total) : (subtotalGeneral * 1.18); 
                // Si 'total' viene del objeto orden, usamos ese. Si no, asumimos subtotalGeneral es base imponible? 
                // Ajuste común: Si subtotalGeneral es la suma de (cant*precio), y precio incluye IGV, entonces subtotalGeneral es el TOTAL.
                // Si precio es unitario SIN IGV, entonces hay que multiplicar.
                // En este sistema parece que 'total' es el monto final.
                
                // Usaremos el total que viene en el objeto orden para ser precisos
                if(orden.total || orden.monto) {
                     totalFinal = parseFloat(orden.total || orden.monto);
                } else {
                     totalFinal = subtotalGeneral; // Fallback
                }

                let baseImponible = totalFinal / 1.18;
                let igv = totalFinal - baseImponible;

                subtotalEl.innerText = 'S/ ' + baseImponible.toFixed(2);
                igvEl.innerText = 'S/ ' + igv.toFixed(2);
                totalEl.innerText = 'S/ ' + totalFinal.toFixed(2);
                
            } else {
                tbody.innerHTML = '<tr><td colspan="4" align="center" style="padding:15px; color:gray;">No hay ítems disponibles o no se han cargado.</td></tr>';
                subtotalEl.innerText = 'S/ 0.00';
                igvEl.innerText = 'S/ 0.00';
                totalEl.innerText = 'S/ 0.00';
            }

            // Mostrar modal
            document.getElementById('modal-detalle').style.display = 'flex';
        }

        function cerrarModalDetalle() {
            document.getElementById('modal-detalle').style.display = 'none';
        }

        // Cerrar al dar click afuera del contenido del modal
        document.getElementById('modal-detalle').addEventListener('click', function(e) {
            // e.target es el elemento clickeado. Si es el fondo (modal-detalle), cerramos.
            // Si es modal-detalle-content o sus hijos, no cerramos.
            if (e.target === this) {
                cerrarModalDetalle();
            }
        });
        // --- FIN MODAL DETALLE ---

        // Buscador simple en cliente
        document.getElementById('buscador').addEventListener('keyup', function() {
            const val = this.value.toLowerCase();
            const filas = document.querySelectorAll('tbody tr');
            filas.forEach(fila => {
                const texto = fila.innerText.toLowerCase();
                fila.style.display = texto.includes(val) ? '' : 'none';
            });
        });
    </script>
</body>
</html>