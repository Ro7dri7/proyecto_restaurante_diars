<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Pedido Registrado! - D'alicias</title>
    <link rel="stylesheet" href="/mvc_restaurante/public/css/pedido/pedido_exito.css">
</head>
<body>
    <header class="panel-header-exito">
        <h1>D'alicias</h1>
        <a href="/mvc_restaurante/public/index.php?entidad=dashboard">Panel de Pedidos</a>
    </header>

    <main class="main-exito">
        <div class="container-exito">
            <div class="logo-container">
                <img src="/mvc_restaurante/public/img/logo.png" alt="Mascota de D'alicias">
            </div>
            <h2 class="titulo-exito">¡Pedido Registrado!</h2>
            <hr class="separador">
            <p class="mensaje-exito">
                ¡Buen trabajo! El pedido #<strong><?= htmlspecialchars($pedidoID) ?></strong> ha sido creado exitosamente.
            </p>
            <a href="/mvc_restaurante/public/index.php?entidad=pedido&action=crear" class="btn-exito">Nuevo Pedido</a>
            <a href="/mvc_restaurante/public/index.php?entidad=pedido&action=listar" class="btn-exito" style="background-color: #FFA500; margin-left: 10px;">Ver Lista de Pedidos</a>
        </div>
    </main>

    <footer class="footer-exito">
        <p>&copy; 2025 Restaurante D'alicias. Todos los derechos reservados.</p>
    </footer>
</body>
</html>