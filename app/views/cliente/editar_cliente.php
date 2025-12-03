<?php
// Asumimos que $clienteData está definido por el controlador
if (empty($clienteData)) {
    die("Error: Datos del cliente no disponibles.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente - Restaurante</title>
    <link rel="stylesheet" href="../../public/css/pedido.css">
</head>
<body>
    <main class="panel-main">
        <h1> Editar Cliente #<?= (int)$clienteData['idCliente'] ?> </h1>

        <!-- ✅ Mensajes de sesión -->
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

        <!-- ✅ Formulario -->
        <form action="index.php?entidad=cliente&action=actualizar" method="POST">
            <!-- Campo oculto con el ID del cliente -->
            <input type="hidden" name="idCliente" value="<?= (int)$clienteData['idCliente'] ?>">

            <div class="form-group">
                <label for="nombreCliente">Nombre Completo *</label>
                <input type="text" 
                       id="nombreCliente" 
                       name="nombreCliente" 
                       value="<?= htmlspecialchars($clienteData['nombreCliente']) ?>" 
                       required 
                       maxlength="100">
            </div>

            <div class="form-group">
                <label for="telefonoCliente">Teléfono</label>
                <input type="text" 
                       id="telefonoCliente" 
                       name="telefonoCliente" 
                       value="<?= htmlspecialchars($clienteData['telefonoCliente']) ?>" 
                       maxlength="20">
            </div>

            <div class="form-group">
                <label for="emailCliente">Correo Electrónico</label>
                <input type="email" 
                       id="emailCliente" 
                       name="emailCliente" 
                       value="<?= htmlspecialchars($clienteData['emailCliente']) ?>" 
                       maxlength="100">
                <small>El correo debe ser único.</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Actualizar Cliente</button>
                <a href="index.php?entidad=cliente&action=listar" class="btn-secondary">Cancelar</a>
            </div>
        </form>
    </main>
</body>
</html>