<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caja - Registrar Pago</title>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <link rel="stylesheet" href="/mvc_restaurante/public/css/cobranza/registrar_pago.css">
</head>
<body>

    <header class="header-caja">
        <div class="header-izq">
            <a href="/mvc_restaurante/public/" class="btn-regresar">
                <ion-icon name="arrow-back-outline"></ion-icon>
                Dashboard
            </a>

            <div class="titulo-seccion">
                <ion-icon name="cash-outline"></ion-icon>
                <h1>Caja - Registrar Pago</h1>
            </div>
        </div>

        <div class="info-sistema">
            <span>Sistema Restaurante</span>
            <small id="fecha-actual">---</small>
        </div>
    </header>

    <div class="contenedor-principal">
        
        <aside class="columna-lista">
            <div class="lista-header">
                <h2>Pendientes de Cobro</h2>
                <span class="badge-contador"><?= count($pedidos) ?></span>
            </div>

            <div class="lista-scroll" id="lista-pedidos">
                </div>
        </aside>

        <main class="columna-pago">
            
            <div id="estado-vacio" class="mensaje-centro">
                <ion-icon name="receipt-outline"></ion-icon>
                <h3>Ningún pedido seleccionado</h3>
                <p>Selecciona un pedido de la izquierda para proceder al cobro.</p>
            </div>

            <div id="panel-cobro" style="display: none;">
                
                <div class="info-pedido-header">
                    <div>
                        <h2 id="txt-pedido-id">Pedido N° ---</h2>
                        <span id="txt-cliente">---</span>
                    </div>
                    <div class="total-grande">
                        <small>Total a Pagar</small>
                        <div id="txt-total">S/ 0.00</div>
                    </div>
                </div>

                <div class="grid-formulario">
                    
                    <div class="seccion-detalle">
                        <h3>Resumen</h3>
                        <table class="tabla-detalle">
                            <tbody id="tabla-items">
                                </tbody>
                        </table>

                        <h3 style="margin-top: 20px;">Tipo de Comprobante</h3>
                        <div class="botones-toggle">
                            <button type="button" class="btn-toggle activo" onclick="setComprobante('Boleta', this)">Boleta</button>
                            <button type="button" class="btn-toggle" onclick="setComprobante('Factura', this)">Factura</button>
                        </div>
                    </div>

                    <div class="seccion-pago">
                        <h3>Método de Pago</h3>
                        <div class="grid-metodos">
                            <button type="button" class="btn-metodo activo" onclick="setMetodo('Efectivo')">
                                <ion-icon name="cash-outline"></ion-icon> Efectivo
                            </button>
                            <button type="button" class="btn-metodo" onclick="setMetodo('Tarjeta')">
                                <ion-icon name="card-outline"></ion-icon> Tarjeta
                            </button>
                            <button type="button" class="btn-metodo" onclick="setMetodo('Yape')">
                                <ion-icon name="qr-code-outline"></ion-icon> Digital
                            </button>
                        </div>

                        <div id="box-efectivo" class="box-monto">
                            <label>Monto Recibido</label>
                            <div class="input-icon">
                                <span>S/</span>
                                <input type="number" step="0.01" id="input-recibido" placeholder="0.00">
                            </div>
                            <div class="fila-vuelto">
                                <span>Vuelto:</span>
                                <span id="lbl-vuelto" class="vuelto-valor">S/ 0.00</span>
                            </div>
                        </div>

                        <!-- 
                             CORRECCIÓN AQUI:
                             Se agregó "index.php" al action para evitar que el servidor haga redirect 
                             y pierda los datos POST.
                        -->
                        <form method="POST" action="/mvc_restaurante/public/index.php?entidad=cobranza&action=procesar_registro" class="form-final">
                            <input type="hidden" name="idPedido" id="post-id">
                            <input type="hidden" name="idCliente" id="post-cliente">
                            <input type="hidden" name="monto" id="post-monto">
                            <input type="hidden" name="metodoPago" id="post-metodo" value="Efectivo">
                            
                            <input type="hidden" name="tipoComprobante" id="post-tipo-comprobante" value="Boleta">
                            
                            <button type="submit" id="btn-cobrar" class="btn-cobrar" disabled>
                                Confirmar y Cobrar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Recibimos los datos de PHP como objeto JSON
        const pedidosDB = <?php echo json_encode($pedidos); ?>;
        
        // Variables de estado
        let pedidoActual = null;
        let metodoActual = 'Efectivo';
        let tipoComprobanteActual = 'Boleta';

        // Inicialización al cargar la página
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('fecha-actual').innerText = new Date().toLocaleDateString();
            renderizarLista();
            
            // Listener para cálculo de vuelto en tiempo real
            const inputRecibido = document.getElementById('input-recibido');
            if(inputRecibido){
                inputRecibido.addEventListener('input', calcularVuelto);
                inputRecibido.addEventListener('keyup', calcularVuelto);
            }
        });

        /**
         * Renderiza la lista lateral de pedidos pendientes
         */
        function renderizarLista() {
            const contenedor = document.getElementById('lista-pedidos');
            contenedor.innerHTML = ''; // Limpiar lista anterior
            
            if (pedidosDB.length === 0) {
                contenedor.innerHTML = '<div class="estado-vacio-lista">No hay pedidos pendientes de cobro.</div>';
                return;
            }
            
            pedidosDB.forEach(p => {
                const div = document.createElement('div');
                div.className = 'tarjeta-pedido';
                div.id = 'pedido-row-' + p.idPedido; // ID único para manipular estilos
                div.onclick = () => seleccionarPedido(p, div);
                
                div.innerHTML = `
                    <div class="fila-top">
                        <strong>Pedido #${p.idPedido}</strong>
                        <span class="hora">
                            ${new Date(p.fechaHoraToma).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                        </span>
                    </div>
                    <div class="fila-bottom">
                        <span>${p.nombreCliente}</span>
                        <span class="precio">S/ ${parseFloat(p.total).toFixed(2)}</span>
                    </div>
                    <div style="font-size: 0.8rem; color: #666; margin-top: 5px;">Estado: ${p.estadoPedido}</div>
                `;
                contenedor.appendChild(div);
            });
        }

        /**
         * Selecciona un pedido y llena el formulario derecho
         */
        function seleccionarPedido(pedido, elementoDOM) {
            pedidoActual = pedido;
            
            // Resaltar visualmente la selección
            document.querySelectorAll('.tarjeta-pedido').forEach(el => el.classList.remove('seleccionado'));
            if(elementoDOM) elementoDOM.classList.add('seleccionado');
            
            // Mostrar panel principal
            document.getElementById('estado-vacio').style.display = 'none';
            document.getElementById('panel-cobro').style.display = 'block';

            // Llenar datos visuales
            document.getElementById('txt-pedido-id').innerText = 'Pedido N° ' + pedido.idPedido;
            document.getElementById('txt-cliente').innerText = pedido.nombreCliente;
            document.getElementById('txt-total').innerText = 'S/ ' + parseFloat(pedido.total).toFixed(2);

            // Resumen simple en la tabla
            document.getElementById('tabla-items').innerHTML = `
                <tr>
                    <td>Consumo en Salón</td>
                    <td align="right">S/ ${parseFloat(pedido.total).toFixed(2)}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">TOTAL</td>
                    <td align="right" style="font-weight: bold;">S/ ${parseFloat(pedido.total).toFixed(2)}</td>
                </tr>
            `;

            // Llenar datos ocultos para PHP
            document.getElementById('post-id').value = pedido.idPedido;
            document.getElementById('post-cliente').value = pedido.idCliente;
            
            // Resetear inputs de pago
            document.getElementById('input-recibido').value = '';
            document.getElementById('lbl-vuelto').innerText = 'S/ 0.00';
            
            // Si estamos en modo efectivo, resetear estado del botón
            if(metodoActual === 'Efectivo'){
                calcularVuelto();
            } else {
                setMetodo(metodoActual);
            }
        }

        /**
         * Cambia entre Boleta y Factura
         */
        function setComprobante(tipo, btn) {
            tipoComprobanteActual = tipo;
            document.getElementById('post-tipo-comprobante').value = tipo;

            // Actualizar estilo de botones
            const botones = document.querySelectorAll('.btn-toggle');
            botones.forEach(b => b.classList.remove('activo'));
            btn.classList.add('activo');
        }

        /**
         * Cambia el método de pago (Efectivo, Tarjeta, Yape)
         */
        function setMetodo(metodo) {
            metodoActual = metodo;
            document.getElementById('post-metodo').value = metodo;

            // Actualizar estilos visuales
            const botones = document.querySelectorAll('.btn-metodo');
            botones.forEach(btn => {
                btn.classList.remove('activo');
                if(btn.innerText.includes(metodo)) btn.classList.add('activo');
            });

            const boxEfectivo = document.getElementById('box-efectivo');
            const inputRecibido = document.getElementById('input-recibido');
            const btnCobrar = document.getElementById('btn-cobrar');

            if(metodo === 'Efectivo') {
                boxEfectivo.style.display = 'block';
                inputRecibido.value = '';
                inputRecibido.disabled = false;
                inputRecibido.focus();
                calcularVuelto();
            } else {
                // Para Tarjeta/Digital, el cobro es exacto
                boxEfectivo.style.display = 'none';
                inputRecibido.value = pedidoActual.total;
                document.getElementById('post-monto').value = pedidoActual.total;
                
                // Habilitar botón inmediatamente
                btnCobrar.disabled = false;
                btnCobrar.style.opacity = '1';
                btnCobrar.style.cursor = 'pointer';
                btnCobrar.innerText = 'Confirmar Pago con ' + metodo;
            }
        }

        /**
         * Calcula el vuelto y valida si se puede cobrar
         */
        function calcularVuelto() {
            if(!pedidoActual || metodoActual !== 'Efectivo') return;
            
            const btn = document.getElementById('btn-cobrar');
            const total = parseFloat(pedidoActual.total);
            const recibido = parseFloat(document.getElementById('input-recibido').value);
            
            // Si el input está vacío o no es número
            if (isNaN(recibido)) {
                document.getElementById('post-monto').value = 0;
                document.getElementById('lbl-vuelto').innerText = '---';
                btn.disabled = true;
                btn.style.opacity = '0.5';
                return;
            }

            // Guardar monto para PHP
            document.getElementById('post-monto').value = recibido;

            const vuelto = recibido - total;
            const lbl = document.getElementById('lbl-vuelto');
            
            if (vuelto >= 0) {
                // Pago suficiente
                lbl.innerText = 'S/ ' + vuelto.toFixed(2);
                lbl.style.color = 'green';
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.style.cursor = 'pointer';
                btn.innerText = 'Confirmar y Cobrar';
            } else {
                // Pago insuficiente
                lbl.innerText = 'Falta S/ ' + Math.abs(vuelto).toFixed(2);
                lbl.style.color = 'red';
                btn.disabled = true;
                btn.style.opacity = '0.5';
                btn.style.cursor = 'not-allowed';
            }
        }
    </script>
</body>
</html>