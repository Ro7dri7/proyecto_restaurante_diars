<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pago Registrado - D'alicias</title>
    <link rel="stylesheet" href="/mvc_restaurante/public/css/cobranza/pago_exito.css">
</head>
<body>
    <div class="container">
        <img src="/mvc_restaurante/public/img/gallina_pagado.png" class="logo" alt="Gallina Pagado">

        <h1 class="title">¡Pago Registrado!</h1>

        <p class="subtitle">
            ¡Buen trabajo! El pago del pedido #<strong><?= htmlspecialchars($idPedido) ?></strong> ha sido registrado exitosamente.
        </p>

        <div class="btn-group">
            <a href="/mvc_restaurante/public/?entidad=cobranza&action=registrar_pago" class="btn-primary">Registrar otro pago</a>
            <a href="/mvc_restaurante/public/" class="btn-secondary">Volver al Dashboard</a>
        </div>
    </div>
</body>
</html>