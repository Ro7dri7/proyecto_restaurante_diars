<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalles del Pedido #<?= $pedidoData['idPedido'] ?> - D'alicias</title>
    <!-- ✅ Ruta absoluta CORREGIDA -->
    <link rel="stylesheet" href="/mvc_restaurante/public/css/pedido_detalle.css">
</head>
<body>
    <div class="container-detalle">
        <h1 class="titulo-detalle">Detalles del Pedido #<?= $pedidoData['idPedido'] ?></h1>

        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Cliente:</span>
                <span class="info-value"><?= htmlspecialchars($pedidoData['nombreCliente'] ?? 'N/A') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha de Toma:</span>
                <span class="info-value"><?= date('d/m/Y H:i', strtotime($pedidoData['fechaHoraToma'])) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Estado:</span>
                <span class="info-value"><?= htmlspecialchars($pedidoData['estadoPedido']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Total:</span>
                <span class="info-value">S/ <?= number_format($pedidoData['total'], 2) ?></span>
            </div>
        </div>

        <h2>Productos en este Pedido:</h2>
        <table class="table-detalle">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['nombreProducto']) ?></td>
                    <td><?= $d['cantidad'] ?></td>
                    <td>S/ <?= number_format($d['precioUnitario'], 2) ?></td>
                    <td>S/ <?= number_format($d['subtotal'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totales-detalle">
            <p><strong>Subtotal General:</strong> S/ <?= number_format($pedidoData['subtotalGeneral'], 2) ?></p>
            <p><strong>IGV (18%):</strong> S/ <?= number_format($pedidoData['igv'], 2) ?></p>
            <p><strong>Total a Pagar:</strong> S/ <?= number_format($pedidoData['total'], 2) ?></p>
        </div>

        <a href="/mvc_restaurante/public/index.php?action=listar" class="btn-back-detalle">← Volver a Lista de Pedidos</a>
    </div>
</body>
</html>