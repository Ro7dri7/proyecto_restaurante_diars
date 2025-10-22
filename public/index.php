<?php
// Ubicación: public/index.php (Front Controller Corregido)

// --- 1. Cargar TODOS los archivos necesarios ---
require_once __DIR__ . '/../app/config/database.php';

// Cargar Modelos
require_once __DIR__ . '/../app/models/Producto.php';
require_once __DIR__ . '/../app/models/Cliente.php';
require_once __DIR__ . '/../app/models/Pedido.php';
require_once __DIR__ . '/../app/models/DetallePedido.php';

// Cargar Controlador
// ¡¡ESTA ES LA LÍNEA QUE PROBABLEMENTE FALTABA O ESTABA MAL!!
require_once __DIR__ . '/../app/controllers/PedidoController.php';

// --- 2. Conexión a la BD ---
try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    die("Error de conexión a la BD: " . $e->getMessage());
}

// --- 3. Enrutamiento (Router) ---
$action = isset($_GET['action']) ? $_GET['action'] : 'crear';

// Esta es tu línea 26. Ahora SÍ encontrará la clase.
$controller = new PedidoController($db);

// En public/index.php, dentro del switch
switch ($action) {
    case 'crear':
        $controller->mostrarFormularioCrear();
        break;
    case 'guardar':
        $controller->guardarPedido();
        break;
    case 'exito':
        $controller->mostrarExito();
        break;
    case 'listar':
        $controller->listarPedidos();
        break;
    case 'editar':
        $controller->mostrarFormularioEditar();
        break;
    case 'actualizar':
        $controller->actualizarPedido();
        break;
    case 'eliminar':
        $controller->eliminarPedido();
        break;
    case 'ver': // 🔥 NUEVO
        $controller->mostrarDetallesPedido();
        break;
    default:
        $controller->mostrarFormularioCrear();
        break;
}
