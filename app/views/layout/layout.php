<?php
$title = $title ?? "D'alicias";
$content = $content ?? "";
// Definimos una ruta base para evitar problemas con enlaces rotos
$baseUrl = '/mvc_restaurante/public'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            background-color: #f9f9f9;
        }

        .sidebar {
            width: 250px;
            background-color: #333;
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            box-shadow: 2px 0 5px rgba(0,0,0,0.2);
            overflow-y: auto;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            margin-left: 250px;
        }

        .sidebar-header {
            background-color: #4CAF50;
            padding: 15px;
            text-align: center;
            font-size: 1.2em;
            border-bottom: 1px solid #444;
        }

        .menu-item {
            padding: 12px 20px;
            cursor: pointer;
            border-bottom: 1px solid #444;
            transition: background 0.2s;
            position: relative;
        }

        .menu-item:hover {
            background-color: #575757;
        }

        .submenu {
            display: none;
            background-color: #444;
            padding-left: 20px;
        }

        .submenu.active {
            display: block;
        }

        .submenu a {
            display: block;
            padding: 8px 15px;
            color: #ddd;
            text-decoration: none;
            transition: color 0.2s;
            border-radius: 4px;
            margin: 2px 0;
        }

        .submenu a:hover {
            background-color: #007bff;
            color: white;
        }

        .footer {
            background-color: #222;
            color: white;
            text-align: center;
            padding: 10px;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 250px;
            border-top: 1px solid #444;
        }
    </style>
</head>
<body>
    <!-- Sidebar izquierdo -->
    <div class="sidebar">
        <div class="sidebar-header">D'alicias</div>

        <!-- DASHBOARD -->
        <div class="menu-item">
            <a href="<?= $baseUrl ?>/index.php?entidad=dashboard" style="color:white; text-decoration:none; display:block;">Dashboard</a>
        </div>

        <!-- PEDIDO -->
        <div class="menu-item" onclick="toggleSubmenu('pedido')">
            Pedido
            <span id="arrow-pedido" style="float:right;">▶</span>
        </div>
        <div id="submenu-pedido" class="submenu">
            <a href="<?= $baseUrl ?>/index.php?entidad=pedido&action=crear">Registrar Pedido</a>
            <a href="<?= $baseUrl ?>/index.php?entidad=pedido&action=listar">Listado de Pedido</a>
            <a href="<?= $baseUrl ?>/index.php?entidad=pedido&action=reporte">Reporte de Pedido</a>
        </div>

        <!-- CLIENTE -->
        <div class="menu-item" onclick="toggleSubmenu('cliente')">
            Cliente
            <span id="arrow-cliente" style="float:right;">▶</span>
        </div>
        <div id="submenu-cliente" class="submenu">
             <a href="<?= $baseUrl ?>/index.php?entidad=cliente&action=crear">Registrar Cliente</a>
             <a href="<?= $baseUrl ?>/index.php?entidad=cliente&action=listar">Listado Clientes</a>
        </div>

        <!-- COBRANZA -->
        <div class="menu-item" onclick="toggleSubmenu('cobranza')">
            Cobranza
            <span id="arrow-cobranza" style="float:right;">▶</span>
        </div>
        <div id="submenu-cobranza" class="submenu">
            <a href="<?= $baseUrl ?>/index.php?entidad=cobranza&action=registrar_pago">Registrar Pago</a>
            <a href="<?= $baseUrl ?>/index.php?entidad=cobranza&action=consultar_estado">Consultar Estado</a>
            <a href="<?= $baseUrl ?>/index.php?entidad=cobranza&action=comprobantes">Comprobantes</a>
        </div>

        <!-- RECLAMOS -->
        <div class="menu-item" onclick="toggleSubmenu('reclamo')">
            Reclamos
            <span id="arrow-reclamo" style="float:right;">▶</span>
        </div>
        <div id="submenu-reclamo" class="submenu">
            <a href="<?= $baseUrl ?>/index.php?entidad=reclamo&action=registrar">Registrar Reclamo</a>
            <a href="<?= $baseUrl ?>/index.php?entidad=reclamo&action=validar">Validar Reclamo</a>
            <a href="<?= $baseUrl ?>/index.php?entidad=reclamo&action=notificar-resolucion">Notificar Resolución</a>
            <a href="<?= $baseUrl ?>/index.php?entidad=reclamo&action=consultar-estado">Consultar Estado</a>
            <!-- 👇 NUEVO: PROCESAR REEMBOLSO (TESORERÍA) -->
            <a href="<?= $baseUrl ?>/index.php?entidad=reclamo&action=procesar-reembolso">Procesar Reembolso</a>
        </div>

        <div class="footer">
            &copy; 2025 Restaurante D'alicias
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="main-content">
        <?= $content ?>
    </div>

    <script>
        function toggleSubmenu(id) {
            // Cerrar otros submenús abiertos
            const submenus = document.querySelectorAll('.submenu');
            submenus.forEach(submenu => {
                const menuItem = submenu.previousElementSibling;
                if (menuItem && submenu.id !== 'submenu-' + id) {
                    const arrow = menuItem.querySelector('span');
                    submenu.classList.remove('active');
                    if (arrow) arrow.textContent = '▶';
                }
            });

            const submenu = document.getElementById('submenu-' + id);
            const arrow = document.getElementById('arrow-' + id);

            if (submenu) {
                if (submenu.classList.contains('active')) {
                    submenu.classList.remove('active');
                    if (arrow) arrow.textContent = '▶';
                } else {
                    submenu.classList.add('active');
                    if (arrow) arrow.textContent = '▼';
                }
            }
        }
    </script>
</body>
</html>