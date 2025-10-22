<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Pedidos - D'alicias</title>
    <link rel="stylesheet" href="/mvc_restaurante/public/css/pedido_lista.css">
</head>
<body>
    <div class="container">
        <a href="/mvc_restaurante/public/index.php?action=crear" class="back-link">← Volver a Crear Pedido</a>
        <h1>Lista de Pedidos</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $p): ?>
                <tr>
                    <td><?= $p['idPedido'] ?></td>
                    <td><?= htmlspecialchars($p['nombreCliente']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($p['fechaHoraToma'])) ?></td>
                    <td>S/ <?= number_format($p['total'], 2) ?></td>
                    <td><?= htmlspecialchars($p['estadoPedido']) ?></td>
                    <td class="action-cell">
                        <a href="/mvc_restaurante/public/index.php?action=editar&id=<?= $p['idPedido'] ?>" class="btn btn-editar">Editar</a>
                        <a href="/mvc_restaurante/public/index.php?action=ver&id=<?= $p['idPedido'] ?>" class="btn btn-ver">Ver</a>
                        <form action="/mvc_restaurante/public/index.php?action=eliminar" method="POST" style="display:inline;" 
                              onsubmit="return confirm('¿Está seguro de eliminar el pedido #<?= $p['idPedido'] ?>?')">
                            <input type="hidden" name="idPedido" value="<?= $p['idPedido'] ?>">
                            <button type="submit" class="btn btn-eliminar">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>