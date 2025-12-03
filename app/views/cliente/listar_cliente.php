<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Clientes - Restaurante</title>
    <link rel="stylesheet" href="../../public/css/pedido.css">
    <style>
        .acciones { white-space: nowrap; }
        .btn-small { padding: 4px 8px; font-size: 0.85em; margin: 0 2px; }
    </style>
</head>
<body>
    <main class="panel-main">
        <h1> Lista de Clientes </h1>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['exito'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['exito']) ?>
            </div>
            <?php unset($_SESSION['exito']); ?>
        <?php endif; ?>

        <div class="toolbar">
            <a href="index.php?entidad=cliente&action=crear" class="btn-primary">➕ Nuevo Cliente</a>
        </div>

        <?php if (empty($clientes)): ?>
            <p class="info">No hay clientes registrados.</p>
        <?php else: ?>
            <table class="tabla-listado">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td><?= (int)$cliente['idCliente'] ?></td>
                            <td><?= htmlspecialchars($cliente['nombreCliente']) ?></td>
                            <td><?= htmlspecialchars($cliente['telefonoCliente'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($cliente['emailCliente'] ?? '—') ?></td>
                            <td class="acciones">
                                <a href="index.php?entidad=cliente&action=editar&id=<?= (int)$cliente['idCliente'] ?>" 
                                   class="btn-small btn-secondary">✏️ Editar</a>
                                <?php
                                // ✅ CORRECCIÓN: Usar json_encode para escapar correctamente
                                $mensaje = "¿Eliminar cliente \"{$cliente['nombreCliente']}\"? Esta acción no se puede deshacer.";
                                ?>
                                <form action="index.php?entidad=cliente&action=eliminar" 
                                      method="POST" 
                                      style="display:inline;" 
                                      onsubmit="return confirm(<?= json_encode($mensaje) ?>)">
                                    <input type="hidden" name="idCliente" value="<?= (int)$cliente['idCliente'] ?>">
                                    <button type="submit" class="btn-small btn-danger">🗑️ Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>
</html>