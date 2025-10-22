<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Pedidos - D'alicias</title>
    <!-- 🔥 RUTAS ABSOLUTAS para evitar errores 404 -->
    <link rel="stylesheet" href="/mvc_restaurante/public/css/pedido.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
    <header class="panel-header">
        <h1>D'alicias</h1>
        <h2>Panel de Pedidos</h2>
    </header>
    <main class="panel-main">
        <form id="formPedido" action="/mvc_restaurante/public/index.php?action=guardar" method="POST">
            <section class="card">
                <h2>Datos del Pedido</h2>
                <div class="form-group">
                    <label for="cliente">Cliente:</label>
                    <select id="cliente" name="idCliente" required>
                        <option value="">-- Seleccione un Cliente --</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?php echo $cliente['idCliente']; ?>">
                                <?php echo htmlspecialchars($cliente['nombreCliente']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- 🔥 NUEVO: Mostrar el número de pedido propuesto -->
                <div class="form-group">
                    <label>Número de Pedido Propuesto:</label>
                    <div style="font-size: 1.2rem; font-weight: bold; color: #e00018;">
                        #<?php echo $proximoID; ?>
                    </div>
                </div>
            </section>
            <section class="card">
                <h2>Añadir Productos</h2>
                <div class="item-adder">
                    <div class="form-group">
                        <label for="producto">Producto:</label>
                        <select id="producto">
                            <option value="">-- Seleccione un Producto --</option>
                            <?php foreach ($productos as $producto): ?>
                                <option 
                                    value="<?php echo $producto['idProducto']; ?>" 
                                    data-precio="<?php echo $producto['precioProducto']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($producto['nombreProducto']); ?>">
                                    <?php echo htmlspecialchars($producto['nombreProducto']); ?> (S/ <?php echo $producto['precioProducto']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cantidad">Cantidad:</label>
                        <input type="number" id="cantidad" value="1" min="1">
                    </div>
                    <button type="button" id="btnAnadirProducto" class="btn btn-anadir">Añadir</button>
                </div>
            </section>
            <section class="card">
                <h2>Detalle del Pedido</h2>
                <table id="tablaDetalle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unit.</th>
                            <th>Subtotal</th>
                            <th>Quitar</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoTablaDetalle">
                    </tbody>
                </table>
                <div class="totales">
                    <p>Subtotal General: <strong>S/ <span id="spanSubtotal">0.00</span></strong></p>
                    <p>IGV (18%): <strong>S/ <span id="spanIGV">0.00</span></strong></p>
                    <p><strong>Total a Pagar: <strong>S/ <span id="spanTotal">0.00</span></strong></strong></p>
                    <input type="hidden" name="subtotalGeneral" id="hiddenSubtotal">
                    <input type="hidden" name="igv" id="hiddenIGV">
                    <input type="hidden" name="total" id="hiddenTotal">
                </div>
                <div id="itemsOcultosParaEnvio"></div>
            </section>
            <button type="submit" class="btn btn-guardar">Guardar Pedido Completo</button>
        </form>
    </main>
    <footer class="panel-footer">
        &copy; <?php echo date("Y"); ?> Restaurante D'alicias. Todos los derechos reservados.
    </footer>
    <!-- 🔥 RUTA ABSOLUTA para el JS -->
    <script src="/mvc_restaurante/public/js/pedido.js"></script>
</body>
</html>