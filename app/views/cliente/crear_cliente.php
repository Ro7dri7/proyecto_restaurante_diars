<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cliente - Restaurante</title>
    <link rel="stylesheet" href="../../public/css/pedido.css">
</head>
<body>
    <main class="panel-main">
        <h1> Registrar Nuevo Cliente </h1>

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
        <form action="index.php?entidad=cliente&action=guardar" method="POST">
            <div class="form-group">
                <label for="nombreCliente">Nombre Completo *</label>
                <input type="text" 
                       id="nombreCliente" 
                       name="nombreCliente" 
                       value="<?= htmlspecialchars($_POST['nombreCliente'] ?? '') ?>" 
                       required 
                       maxlength="100">
            </div>

            <div class="form-group">
                <label for="telefonoCliente">Teléfono</label>
                <input type="text" 
                       id="telefonoCliente" 
                       name="telefonoCliente" 
                       value="<?= htmlspecialchars($_POST['telefonoCliente'] ?? '') ?>" 
                       maxlength="20">
            </div>

            <div class="form-group">
                <label for="emailCliente">Correo Electrónico</label>
                <input type="email" 
                       id="emailCliente" 
                       name="emailCliente" 
                       value="<?= htmlspecialchars($_POST['emailCliente'] ?? '') ?>" 
                       maxlength="100">
                <small>El correo debe ser único.</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Registrar Cliente</button>
                <a href="index.php?entidad=cliente&action=listar" class="btn-secondary">Ver Lista</a>
            </div>
        </form>
    </main>
</body>
</html>