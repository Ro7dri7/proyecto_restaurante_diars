<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Pedidos - D'alicias</title>
    <link rel="stylesheet" href="/mvc_restaurante/public/css/pedido/pedido_lista.css">
</head>
<body>
    <div class="container">
        <h1>Lista de Pedidos</h1>

        <!-- Botón Volver al Dashboard -->
        <a href="/mvc_restaurante/public/" class="btn-dashboard">← Volver al Dashboard</a>

        <!-- Mensajes de éxito o error -->
        <?php if (!empty($_SESSION['mensaje_exito'])): ?>
            <div class="alerta-exito">
                <?= htmlspecialchars($_SESSION['mensaje_exito']) ?>
            </div>
            <?php unset($_SESSION['mensaje_exito']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alerta-error">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

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
                <tr class="<?= $p['estadoPedido'] === 'Cancelado' ? 'pedido-cancelado' : '' ?>">
                    <td><?= $p['idPedido'] ?></td>
                    <td><?= htmlspecialchars($p['nombreCliente']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($p['fechaHoraToma'])) ?></td>
                    <td>S/ <?= number_format($p['total'], 2) ?></td>
                    <td>
                        <?php
                        $estadoLower = strtolower(str_replace(' ', '-', $p['estadoPedido']));
                        $claseEstado = 'estado-' . $estadoLower;
                        echo "<span class=\"$claseEstado\">" . htmlspecialchars($p['estadoPedido']) . "</span>";
                        ?>
                    </td>
                    <td class="action-cell">
                        <div class="btn-group">
                            <a href="/mvc_restaurante/public/index.php?entidad=pedido&action=editar&id=<?= $p['idPedido'] ?>" class="btn btn-editar">Editar</a>
                            <a href="/mvc_restaurante/public/index.php?entidad=pedido&action=ver&id=<?= $p['idPedido'] ?>" class="btn btn-ver">Ver</a>

                            <!-- BOTÓN ELIMINAR -->
                            <form action="/mvc_restaurante/public/index.php?entidad=pedido&action=eliminar" method="POST" style="display:inline;">
                                <input type="hidden" name="idPedido" value="<?= $p['idPedido'] ?>">
                                <button type="submit" class="btn btn-eliminar">Eliminar</button>
                            </form>

                            <!-- BOTÓN CONFIRMAR -->
                            <?php
                            $estadoActual = $p['estadoPedido'];
                            $esFinal = ($estadoActual === 'Entregado');
                            $claseBoton = $esFinal ? 'btn-confirmar-final' : 'btn-confirmar';
                            $textoBoton = $esFinal ? '✅ Listo para cobranza' : '🍽️ Confirmar';
                            $onclick = $esFinal 
                                ? "alert('Estado final del pedido. Está listo para realizar cobranza.')" 
                                : "cambiarEstado({$p['idPedido']})";
                            ?>
                            <button type="button" class="<?= $claseBoton ?>" onclick="<?= $onclick ?>">
                                <?= $textoBoton ?>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function cambiarEstado(idPedido) {
            if (confirm("¿Desea avanzar el estado del pedido #" + idPedido + "?")) {
                const url = "/mvc_restaurante/public/index.php?entidad=pedido&action=cambiarEstado&id=" + idPedido + "&t=" + Date.now();
                window.location.href = url;
            }
        }
    </script>
</body>
</html>