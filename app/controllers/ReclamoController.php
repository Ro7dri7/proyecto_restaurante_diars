<?php
// app/controllers/ReclamoController.php

// ✅ Rutas absolutas usando __DIR__ para evitar errores de inclusión
require_once __DIR__ . '/../models/reclamo.php';

class ReclamoController {
    private $db;
    private $reclamoModel;

    public function __construct($database) {
        $this->db = $database;
        $this->reclamoModel = new Reclamo($this->db);
    }

    // =========================================================
    // SECCIÓN 1: REGISTRO DE RECLAMOS (CLIENTE)
    // =========================================================

    // Mostrar formulario de registro de reclamo dentro del layout
    public function registrar() {
        // Capturamos el contenido de la vista
        ob_start();
        require_once __DIR__ . '/../views/reclamo/registrar_reclamo.php';
        $content = ob_get_clean();

        // Asignamos título y cargamos el layout
        $title = "Registrar Solicitud de Reclamo - D'alicias";
        include __DIR__ . '/../views/layout/layout.php';
    }

    // Buscar cliente vía AJAX (para autocomplete en el registro)
    public function buscarCliente() {
        header('Content-Type: application/json; charset=utf-8');
        $termino = $_GET['q'] ?? '';
        
        if (strlen($termino) < 2) {
            echo json_encode(['cliente' => null]);
            return;
        }

        $cliente = $this->reclamoModel->buscarClientePorTermino($termino);
        echo json_encode(['cliente' => $cliente], JSON_UNESCAPED_UNICODE);
    }

    // Obtener pedidos entregados de un cliente vía AJAX
    public function obtenerPedidos() {
        header('Content-Type: application/json; charset=utf-8');
        $idCliente = (int)($_GET['id'] ?? 0);
        
        if (!$idCliente) {
            echo json_encode(['pedidos' => []]);
            return;
        }

        $pedidos = $this->reclamoModel->obtenerPedidosPorCliente($idCliente);
        echo json_encode(['pedidos' => $pedidos], JSON_UNESCAPED_UNICODE);
    }

    // Registrar el reclamo en la base de datos (POST)
    public function procesar() {
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        // Sanitización y validación básica
        $idPedido = (int)($_POST['idPedido'] ?? 0);
        $idCliente = (int)($_POST['idCliente'] ?? 0);
        $producto = trim($_POST['productoAfectado'] ?? '');
        $motivo = trim($_POST['motivo'] ?? '');
        $metodo = trim($_POST['metodoDevolucion'] ?? '');
        $monto = !empty($_POST['montoSeleccionado']) ? (float)$_POST['montoSeleccionado'] : null;

        if (!$idPedido || !$idCliente || empty($producto) || empty($motivo) || empty($metodo)) {
            echo json_encode([
                'success' => false,
                'message' => 'Todos los campos son obligatorios.'
            ]);
            return;
        }

        $idReclamo = $this->reclamoModel->crear($idPedido, $idCliente, $producto, $motivo, $metodo, $monto);

        if ($idReclamo) {
            echo json_encode([
                'success' => true,
                'message' => "Reclamo registrado correctamente. Ticket #R$idReclamo"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al registrar el reclamo. Intente nuevamente.'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // =========================================================
    // SECCIÓN 2: VALIDACIÓN DE RECLAMOS (ADMINISTRADOR)
    // =========================================================

    // Mostrar vista de validación de reclamos
    public function validar() {
        // Capturamos el contenido de la vista
        ob_start();
        require_once __DIR__ . '/../views/reclamo/validar_reclamo.php';
        $content = ob_get_clean();

        // Asignamos título y cargamos el layout
        $title = "Validar Solicitud de Reclamo - D'alicias";
        include __DIR__ . '/../views/layout/layout.php';
    }

    // Obtener reclamos pendientes vía AJAX para la tabla
    public function obtenerPendientes() {
        header('Content-Type: application/json; charset=utf-8');
        $pendientes = $this->reclamoModel->obtenerReclamosPendientes();
        echo json_encode(['reclamos' => $pendientes], JSON_UNESCAPED_UNICODE);
    }

    // Procesar la validación de un reclamo (Aprobar/Rechazar)
    public function validarReclamo() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $idReclamo = (int)($_POST['idReclamo'] ?? 0);
        $accion = $_POST['accion'] ?? ''; // 'aprobar' o 'rechazar'
        $comentario = trim($_POST['comentario'] ?? '');

        if (!$idReclamo || !in_array($accion, ['aprobar', 'rechazar'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Datos inválidos.'
            ]);
            return;
        }

        // Definir el nuevo estado basado en la acción
        $nuevoEstado = $accion === 'aprobar' ? 'Validado' : 'Rechazado';

        $exito = $this->reclamoModel->actualizarEstado($idReclamo, $nuevoEstado, $comentario);

        if ($exito) {
            echo json_encode([
                'success' => true,
                'message' => "Solicitud REC-{$idReclamo} " . strtolower($nuevoEstado) . " exitosamente."
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar el estado del reclamo.'
            ]);
        }
    }

    // =========================================================
    // SECCIÓN 3: NOTIFICAR RESOLUCIÓN DE RECLAMOS (ADMINISTRADOR)
    // =========================================================

    // Mostrar vista de notificación de resoluciones
    public function notificarResolucion() {
        // Capturamos el contenido de la vista
        ob_start();
        require_once __DIR__ . '/../views/reclamo/notificar_resolucion.php';
        $content = ob_get_clean();

        // Asignamos título y cargamos el layout
        $title = "Notificar Resolución de Reclamo - D'alicias";
        include __DIR__ . '/../views/layout/layout.php';
    }

    // Obtener reclamos resueltos vía AJAX para la tabla
    public function obtenerResueltos() {
        header('Content-Type: application/json; charset=utf-8');
        $resueltos = $this->reclamoModel->obtenerReclamosResueltos();
        echo json_encode(['reclamos' => $resueltos], JSON_UNESCAPED_UNICODE);
    }

    // Procesar el envío de la notificación
    public function enviarNotificacion() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $idReclamo = (int)($_POST['idReclamo'] ?? 0);
        $canal = $_POST['canal'] ?? ''; // 'email' o 'whatsapp'
        $mensaje = trim($_POST['mensaje'] ?? '');

        if (!$idReclamo || !in_array($canal, ['email', 'whatsapp'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Datos inválidos.'
            ]);
            return;
        }

        // Aquí iría la lógica real de envío por email o whatsapp (PHP Mailer, Twilio, etc.)
        // Por ahora, simulamos el éxito.
        $exito = true; 

        if ($exito) {
            // ✅ Marcamos el reclamo como "Notificado" usando el método del modelo
            $this->reclamoModel->marcarComoNotificado($idReclamo);

            echo json_encode([
                'success' => true,
                'message' => "Notificación enviada exitosamente por {$canal} para el reclamo REC-{$idReclamo}. Estado actualizado a 'Notificado'."
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al enviar la notificación.'
            ]);
        }
    }

    // =========================================================
    // SECCIÓN 4: CONSULTAR ESTADO DE RECLAMOS (ADMINISTRADOR)
    // =========================================================

    // Mostrar vista de consulta de estado de reclamos
    public function consultarEstado() {
        // Capturamos el contenido de la vista
        ob_start();
        require_once __DIR__ . '/../views/reclamo/consultar_estado.php';
        $content = ob_get_clean();

        // Asignamos título y cargamos el layout
        $title = "Consultar Estado de Reclamos - D'alicias";
        include __DIR__ . '/../views/layout/layout.php';
    }

    // Obtener todos los reclamos vía AJAX para la tabla
    public function obtenerTodos() {
        header('Content-Type: application/json; charset=utf-8');
        // Llamamos al método del modelo que recupera todo el historial
        $reclamos = $this->reclamoModel->obtenerTodosLosReclamos();
        echo json_encode(['reclamos' => $reclamos], JSON_UNESCAPED_UNICODE);
    }

    // =========================================================
    // SECCIÓN 5: PROCESAR REEMBOLSO (CUS5 - TESORERÍA)
    // =========================================================

    // ✅ Muestra la vista para procesar reembolsos
    public function procesarReembolso() {
        ob_start();
        require_once __DIR__ . '/../views/reclamo/procesar_reembolso.php';
        $content = ob_get_clean();

        $title = "Procesar Reembolso - D'alicias";
        include __DIR__ . '/../views/layout/layout.php';
    }

    // ✅ Obtiene los reclamos listos para reembolso (estado 'Validado')
    public function obtenerParaReembolso() {
        header('Content-Type: application/json; charset=utf-8');
        $reclamos = $this->reclamoModel->obtenerReclamosParaReembolso();
        echo json_encode(['reclamos' => $reclamos], JSON_UNESCAPED_UNICODE);
    }

    // ✅ Procesa el pago del reembolso
    public function procesarPago() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $idReclamo = (int)($_POST['idReclamo'] ?? 0);
        $numeroOperacion = trim($_POST['numeroOperacion'] ?? '');

        if (!$idReclamo || empty($numeroOperacion)) {
            echo json_encode([
                'success' => false,
                'message' => 'ID de reclamo y número de operación son obligatorios.'
            ]);
            return;
        }

        $exito = $this->reclamoModel->marcarComoReembolsado($idReclamo, $numeroOperacion);

        if ($exito) {
            echo json_encode([
                'success' => true,
                'message' => "Reembolso procesado para el reclamo REC-{$idReclamo}."
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al procesar el reembolso.'
            ]);
        }
    }
}