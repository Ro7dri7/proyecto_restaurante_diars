<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pedido #<?= $pedidoData['idPedido'] ?> - D'alicias</title>
    <link rel="stylesheet" href="/mvc_restaurante/public/css/pedido/pedido.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body>
    <header class="panel-header">
        <h1>D'alicias</h1>
        <h2>Editar Pedido #<?= $pedidoData['idPedido'] ?></h2>
    </header>
    <main class="panel-main">
        <form id="formPedido" action="/mvc_restaurante/public/index.php?entidad=pedido&action=actualizar&id=<?= $pedidoData['idPedido'] ?>" method="POST">
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

                <div class="form-group">
                    <label for="estado">Estado del Pedido:</label>
                    <select id="estado" name="estadoPedido" required>
                        <option value="Registrado" <?= $pedidoData['estadoPedido'] == 'Registrado' ? 'selected' : '' ?>>Registrado</option>
                        <option value="En Preparación" <?= $pedidoData['estadoPedido'] == 'En Preparación' ? 'selected' : '' ?>>En Preparación</option>
                        <option value="Listo para Entrega" <?= $pedidoData['estadoPedido'] == 'Listo para Entrega' ? 'selected' : '' ?>>Listo para Entrega</option>
                        <option value="Entregado" <?= $pedidoData['estadoPedido'] == 'Entregado' ? 'selected' : '' ?>>Entregado</option>
                        <option value="Cancelado" <?= $pedidoData['estadoPedido'] == 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                </div>
            </section>

            <!-- SECCIÓN MODIFICADA: AÑADIR PRODUCTOS CON FILTROS EN UNA SOLA FILA -->
            <section class="card">
                <h2>Añadir Productos</h2>

                <!-- Filtros en una sola fila -->
                <div class="item-filters-container">
                    <div class="filter-item">
                        <label for="filtroCategoria">Filtrar por Categoría:</label>
                        <select id="filtroCategoria">
                            <option value="">-- Todas las Categorías --</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= $categoria['idCategoria'] ?>">
                                    <?= htmlspecialchars($categoria['nombreCategoria']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-item">
                        <label for="filtroBusqueda">Buscar por Nombre o ID:</label>
                        <input type="text" id="filtroBusqueda" placeholder="Ej: Pizza o 101..." autocomplete="off">
                    </div>
                </div>

                <!-- Selector de producto (se llena dinámicamente) -->
                <div class="item-adder">
                    <div class="form-group">
                        <label for="producto">Producto (Resultados: <span id="countProductos">0</span>) :</label>
                        <select id="producto" disabled>
                            <option value="">-- Seleccione un Producto --</option>
                            <!-- Los productos se cargarán aquí dinámicamente con JavaScript -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cantidad">Cantidad:</label>
                        <input type="number" id="cantidad" value="1" min="1">
                    </div>
                    <button type="button" id="btnAnadirProducto" class="btn btn-anadir" disabled>+ Añadir</button>
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

            <!-- Botones -->
            <div class="acciones-formulario">
                <button type="submit" class="btn btn-guardar">Guardar Cambios</button>
                <a href="/mvc_restaurante/public/index.php?entidad=pedido&action=listar" class="btn btn-cancelar">Cancelar</a>
            </div>
        </form>
    </main>
    <footer class="panel-footer">
        &copy; <?= date("Y") ?> Restaurante D'alicias. Todos los derechos reservados.
    </footer>

    <!-- ✅ Cargar los productos en memoria para el JavaScript -->
    <script>
        const PRODUCTOS_DB = <?= json_encode(array_map(function ($producto) {
                                    return [
                                        'id' => $producto['idProducto'],
                                        'nombre' => $producto['nombreProducto'],
                                        'precio' => (float)$producto['precioProducto'],
                                        'categoria' => $producto['idCategoria']
                                    ];
                                }, $productos)) ?>;
    </script>

    <script src="/mvc_restaurante/public/js/pedido.js"></script>
</body>

</html>