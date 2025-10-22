<?php
// Obtener el ID del pedido (ya validado en el controlador)
if (!isset($pedidoID) || $pedidoID <= 0) {
    header("Location: /mvc_restaurante/public/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Pedido Registrado! - D'alicias</title>
    <!-- Enlace al CSS externo -->
    <link rel="stylesheet" href="/mvc_restaurante/public/css/pedido_exito.css">
</head>

<body>
    <!-- Header -->
    <header class="panel-header-exito">
        <h1>D'alicias</h1>
        <a href="/mvc_restaurante/public/index.php">Panel de Pedidos</a>
    </header>

    <!-- Contenido Principal -->
    <main class="main-exito">
        <div class="container-exito">
            <!-- Imagen de Mascota (Circular y más grande) -->
            <div class="logo-container">
                <img src="/mvc_restaurante/public/img/logo.png" alt="Mascota de D'alicias">
            </div>

            <!-- Título -->
            <h2 class="titulo-exito">¡Pedido Registrado!</h2>

            <!-- Línea separadora -->
            <hr class="separador">

            <!-- Mensaje de éxito -->
            <p class="mensaje-exito">
                ¡Buen trabajo! El pedido #<strong><?= htmlspecialchars($pedidoID) ?></strong> ha sido creado exitosamente.
            </p>

            <!-- Botón para nuevo pedido -->
            <a href="/mvc_restaurante/public/index.php?action=crear" class="btn-exito">Registrar un Nuevo Pedido</a>
            <a href="/mvc_restaurante/public/index.php?action=listar" class="btn-exito"
                style="background-color: #FFA500; margin-left: 10px;">Ver Lista de Pedidos</a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer-exito">
        <p>&copy; 2025 Restaurante D'alicias. Todos los derechos reservados.</p>
    </footer>
</body>

</html>