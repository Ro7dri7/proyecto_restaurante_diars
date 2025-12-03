<?php
// Ubicación: public/index.php (Front Controller)

// Iniciar sesión para manejar mensajes flash y autenticación si fuera necesario
session_start();

// --- 1. Cargar la configuración de la base de datos ---
require_once __DIR__ . '/../app/config/database.php';

// --- 2. Cargar Modelos ---
require_once __DIR__ . '/../app/models/Producto.php';
require_once __DIR__ . '/../app/models/Cliente.php';
require_once __DIR__ . '/../app/models/Pedido.php';
require_once __DIR__ . '/../app/models/DetallePedido.php';
require_once __DIR__ . '/../app/models/Pago.php';
require_once __DIR__ . '/../app/models/Reclamo.php'; // 👈 Modelo Reclamo

// --- 3. Cargar Controladores ---
require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/PedidoController.php';
require_once __DIR__ . '/../app/controllers/ClienteController.php';
require_once __DIR__ . '/../app/controllers/CobranzaController.php';
require_once __DIR__ . '/../app/controllers/ReclamoController.php'; // 👈 Controlador Reclamo

// --- 4. Conexión a la base de datos ---
try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    die("Error crítico de conexión a la BD: " . htmlspecialchars($e->getMessage()));
}

// --- 5. Determinar la entidad y la acción (Routing) ---
$entidad = $_GET['entidad'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';

$entidad = strtolower(trim($entidad));
$action = strtolower(trim($action));

// --- 6. Instanciar el controlador correcto ---
$controller = null;

switch ($entidad) {
    case 'dashboard':
        $controller = new DashboardController($db);
        break;
    case 'pedido':
        $controller = new PedidoController($db);
        break;
    case 'cliente':
        $controller = new ClienteController($db);
        break;
    case 'cobranza':
        $controller = new CobranzaController($db);
        break;
    case 'reclamo':
        $controller = new ReclamoController($db);
        break;
    default:
        http_response_code(404);
        die("Entidad no soportada: " . htmlspecialchars($entidad));
}

// --- 7. Ejecutar la acción correspondiente dentro del controlador ---
try {
    switch ($entidad) {
        case 'dashboard':
            $controller->mostrarDashboard();
            break;

        case 'pedido':
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
                case 'ver':
                    $controller->mostrarDetallesPedido();
                    break;
                case 'cambiarestado':
                    $idPedido = (int)($_GET['id'] ?? 0);
                    $controller->cambiarEstado($idPedido);
                    break;
                default:
                    $controller->listarPedidos();
                    break;
            }
            break;

        case 'cliente':
            switch ($action) {
                case 'crear':
                    $controller->mostrarFormularioCrear();
                    break;
                case 'guardar':
                    $controller->procesarCreacion();
                    break;
                case 'listar':
                    $controller->listar();
                    break;
                case 'editar':
                    $controller->mostrarFormularioEditar();
                    break;
                case 'actualizar':
                    $controller->actualizarCliente();
                    break;
                case 'eliminar':
                    $controller->eliminarCliente();
                    break;
                default:
                    $controller->listar();
                    break;
            }
            break;

        case 'cobranza':
            switch ($action) {
                case 'registrar_pago':
                    $controller->mostrarRegistrarPago();
                    break;
                case 'procesar_registro':
                    $controller->procesarRegistro();
                    break;
                case 'comprobantes':
                    $controller->mostrarComprobantes();
                    break;
                case 'consultar_estado':
                    $controller->mostrarConsultarEstado();
                    break;
                case 'anular_pago':
                    $controller->anularPago();
                    break;
                case 'generar_boleta':
                    $controller->generarBoleta();
                    break;
                case 'generar_boleta_desde_comprobantes':
                    $controller->generarBoletaDesdeComprobantes();
                    break;
                default:
                    $controller->mostrarRegistrarPago();
                    break;
            }
            break;

        // 👇 BLOQUE ACTUALIZADO: ENTIDAD RECLAMO
        case 'reclamo':
            switch ($action) {
                // --- Acciones Cliente (Registro) ---
                case 'registrar':
                    $controller->registrar();
                    break;
                case 'buscar-cliente':
                    $controller->buscarCliente();
                    return; // Detener ejecución: respuesta JSON
                case 'obtener-pedidos':
                    $controller->obtenerPedidos();
                    return; // Detener ejecución: respuesta JSON
                case 'procesar':
                    $controller->procesar();
                    return; // Detener ejecución: respuesta JSON
                
                // --- Acciones Admin (Validación) ---
                case 'validar':
                    $controller->validar();
                    break;
                case 'obtener-pendientes':
                    $controller->obtenerPendientes();
                    return; // Detener ejecución: respuesta JSON
                case 'validar-reclamo':
                    $controller->validarReclamo();
                    return; // Detener ejecución: respuesta JSON

                // --- Acciones Admin (Notificación) ---
                case 'notificar-resolucion':
                    $controller->notificarResolucion();
                    break;
                case 'obtener-resueltos':
                    $controller->obtenerResueltos();
                    return; // Detener ejecución: respuesta JSON
                case 'enviar-notificacion':
                    $controller->enviarNotificacion();
                    return; // Detener ejecución: respuesta JSON

                // --- Acciones Admin (Consulta de Estado) ---
                case 'consultar-estado':
                    $controller->consultarEstado();
                    break;
                case 'obtener-todos':
                    $controller->obtenerTodos();
                    return; // Detener ejecución: respuesta JSON

                // --- ✅ NUEVO: CUS5 - Procesar Reembolso (Tesorería) ---
                case 'procesar-reembolso':
                    $controller->procesarReembolso();
                    break;
                case 'obtener-para-reembolso':
                    $controller->obtenerParaReembolso();
                    return; // Retorno directo para JSON
                case 'procesar-pago':
                    $controller->procesarPago();
                    return; // Retorno directo para JSON

                default:
                    $controller->registrar();
            }
            break;
    }

} catch (Exception $e) {
    error_log("Error en la aplicación: " . $e->getMessage());
    $_SESSION['error'] = "Ocurrió un error inesperado: " . $e->getMessage();
    // Redirigir a la raíz del proyecto en caso de error grave. Ajusta la ruta si es necesario.
    header('Location: /mvc_restaurante/public/');
    exit;
}