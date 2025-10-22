<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pedido #<?= $pedidoData['idPedido'] ?> - D'alicias</title>
    <link rel="stylesheet" href="/mvc_restaurante/public/css/pedido.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
    <header class="panel-header">
        <h1>D'alicias</h1>
        <h2>Editar Pedido #<?= $pedidoData['idPedido'] ?></h2>
    </header>
    <main class="panel-main">
        <form id="formPedido" action="/mvc_restaurante/public/index.php?action=actualizar&id=<?= $pedidoData['idPedido'] ?>" method="POST">
            <section class="card">
                <h2>Datos del Pedido</h2>
                <div class="form-group">
                    <label for="cliente">Cliente:</label>
                    <select id="cliente" name="idCliente" required>
                        <option value="">-- Seleccione un Cliente --</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= $cliente['idCliente'] ?>" 
                                <?= $cliente['idCliente'] == $pedidoData['idCliente'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cliente['nombreCliente']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </section>

            <!-- Sección para añadir nuevos productos (igual que en pedido_form.php) -->
            <section class="card">
                <h2>Añadir Productos</h2>
                <div class="item-adder">
                    <div class="form-group">
                        <label for="producto">Producto:</label>
                        <select id="producto">
                            <option value="">-- Seleccione un Producto --</option>
                            <?php foreach ($productos as $producto): ?>
                                <option 
                                    value="<?= $producto['idProducto'] ?>" 
                                    data-precio="<?= $producto['precioProducto'] ?>"
                                    data-nombre="<?= htmlspecialchars($producto['nombreProducto']) ?>">
                                    <?= htmlspecialchars($producto['nombreProducto']) ?> (S/ <?= $producto['precioProducto'] ?>)
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
                        <?php foreach ($detalles as $d): ?>
                        <tr data-idproducto="<?= $d['idProducto'] ?>">
                            <td><?= $d['idProducto'] ?></td>
                            <td><?= htmlspecialchars($d['nombreProducto']) ?></td>
                            <td><?= $d['cantidad'] ?></td>
                            <td>S/ <?= number_format($d['precioUnitario'], 2) ?></td>
                            <td>S/ <?= number_format($d['subtotal'], 2) ?></td>
                            <td><button type="button" class="btn btn-quitar">Quitar</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="totales">
                    <p>Subtotal General: <strong>S/ <span id="spanSubtotal"><?= number_format($pedidoData['subtotalGeneral'], 2) ?></span></strong></p>
                    <p>IGV (18%): <strong>S/ <span id="spanIGV"><?= number_format($pedidoData['igv'], 2) ?></span></strong></p>
                    <p><strong>Total a Pagar: <strong>S/ <span id="spanTotal"><?= number_format($pedidoData['total'], 2) ?></span></strong></strong></p>
                    <input type="hidden" name="subtotalGeneral" id="hiddenSubtotal" value="<?= $pedidoData['subtotalGeneral'] ?>">
                    <input type="hidden" name="igv" id="hiddenIGV" value="<?= $pedidoData['igv'] ?>">
                    <input type="hidden" name="total" id="hiddenTotal" value="<?= $pedidoData['total'] ?>">
                </div>
                <div id="itemsOcultosParaEnvio">
                    <?php foreach ($detalles as $d): ?>
                    <div id="item-oculto-<?= $d['idProducto'] ?>">
                        <input type="hidden" name="productos[id][]" value="<?= $d['idProducto'] ?>">
                        <input type="hidden" name="productos[cantidad][]" value="<?= $d['cantidad'] ?>">
                        <input type="hidden" name="productos[precio][]" value="<?= $d['precioUnitario'] ?>">
                        <input type="hidden" name="productos[subtotal][]" value="<?= $d['subtotal'] ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <button type="submit" class="btn btn-guardar">Guardar Cambios</button>
            <a href="/mvc_restaurante/public/index.php?action=listar" style="display: inline-block; margin-left: 10px; padding: 12px 24px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px;">Cancelar</a>
        </form>
    </main>
    <footer class="panel-footer">
        &copy; <?= date("Y") ?> Restaurante D'alicias. Todos los derechos reservados.
    </footer>
    <script src="/mvc_restaurante/public/js/pedido.js"></script>
</body>
</html>