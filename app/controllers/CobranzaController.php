<?php
// app/controllers/CobranzaController.php

require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/Pago.php';

class CobranzaController {
    
    private $pedidoModel;
    private $pagoModel;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->pedidoModel = new Pedido($db);
        $this->pagoModel = new Pago($db);
    }

    public function mostrarRegistrarPago() {
        $pedidos = $this->pedidoModel->obtenerPedidosPendientesDePago();
        include __DIR__ . '/../views/cobranza/registrar_pago.php';
    }

    public function procesarRegistro() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /mvc_restaurante/public/');
            exit;
        }

        $idPedido   = $_POST['idPedido']     ?? $_POST['id_pedido']     ?? null;
        $idCliente  = $_POST['idCliente']    ?? $_POST['id_cliente']    ?? null;
        $monto      = $_POST['monto']        ?? $_POST['monto_recibido']?? 0;
        $metodoPago = $_POST['metodoPago']   ?? $_POST['metodo_pago']   ?? '';

        if (!$idPedido || $monto <= 0 || empty($metodoPago)) {
            $faltante = [];
            if (!$idPedido) $faltante[] = "ID Pedido";
            if ($monto <= 0) $faltante[] = "Monto";
            if (empty($metodoPago)) $faltante[] = "Método de Pago";
            
            die("Error: Datos incompletos o inválidos (" . implode(', ', $faltante) . ").");
        }

        try {
            $idClienteFinal = $idCliente ? $idCliente : 1;

            $this->pagoModel->registrarPago($idPedido, $idClienteFinal, $monto, $metodoPago);
            $this->pedidoModel->confirmarPago($idPedido);

            // ✅ IMPORTANTE: Mostrar vista directamente, NO redirigir
            require_once __DIR__ . '/../views/cobranza/pago_exito.php';
            exit;

        } catch (Exception $e) {
            die("Error al procesar el pago: " . $e->getMessage());
        }
    }

    public function mostrarConsultarEstado() {
        $query = $_GET['query'] ?? '';
        $orden = null;
        $mensajeError = '';
        $pedidosLista = [];
        
        $fechaInicio = $_GET['fechaInicio'] ?? '';
        $fechaFin = $_GET['fechaFin'] ?? '';

        if (empty($query)) {
            $estados = ['Entregado', 'Pagado', 'Facturado'];

            if (!empty($fechaInicio) || !empty($fechaFin)) {
                $stmt = $this->pedidoModel->leerPorRangoDeFechas($fechaInicio, $fechaFin);
                $todosEnRango = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $pedidosRecientes = array_filter($todosEnRango, function($pedido) use ($estados) {
                    return in_array($pedido['estadoPedido'], $estados);
                });
                
                usort($pedidosRecientes, function($a, $b) {
                    return strtotime($b['fechaHoraToma']) - strtotime($a['fechaHoraToma']);
                });
                
                $pedidosLista = array_values($pedidosRecientes);
            } else {
                $pedidosLista = $this->pedidoModel->obtenerPedidosPorEstados($estados);
            }

        } else {
            $ordenData = $this->pedidoModel->buscarPorCriterio($query);

            if ($ordenData) {
                $sqlItems = "SELECT dp.cantidad, dp.precioUnitario, (dp.cantidad * dp.precioUnitario) as subtotal, p.nombreProducto 
                             FROM detalle_pedido dp
                             INNER JOIN producto p ON dp.idProducto = p.idProducto
                             WHERE dp.idPedido = ?";
                $stmt = $this->db->prepare($sqlItems);
                $stmt->execute([$ordenData['idPedido']]);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $pagoData = $this->pagoModel->obtenerPorPedido($ordenData['idPedido']);

                $historial = [];
                $historial[] = [
                    'tipo' => 'creacion',
                    'fecha' => date('d/m/Y H:i', strtotime($ordenData['fechaHoraToma'])),
                    'desc' => 'Orden registrada'
                ];

                if (!empty($ordenData['fechaHoraEntrega'])) {
                    $historial[] = [
                        'tipo' => 'preparacion',
                        'fecha' => date('d/m/Y H:i', strtotime($ordenData['fechaHoraEntrega'])),
                        'desc' => 'Orden entregada en mesa'
                    ];
                }

                if ($pagoData && ($pagoData['estado'] == 'Confirmado' || $pagoData['estado'] == 'Anulado')) {
                    $historial[] = [
                        'tipo' => 'pago',
                        'fecha' => date('d/m/Y H:i', strtotime($pagoData['fechaHora'])),
                        'desc' => 'Pago recibido',
                        'metodo' => $pagoData['metodoPago'],
                        'comprobante' => 'TK-' . str_pad($pagoData['idPago'], 6, '0', STR_PAD_LEFT)
                    ];
                }

                if ($pagoData && $pagoData['estado'] == 'Anulado') {
                    $historial[] = [
                        'tipo' => 'anulacion',
                        'fecha' => date('d/m/Y H:i', strtotime($pagoData['fechaAnulacion'] ?? date('Y-m-d H:i:s'))),
                        'desc' => 'Pago Anulado / Extornado',
                        'motivo' => $pagoData['motivoAnulacion'] ?? 'Sin motivo',
                        'autorizado' => $pagoData['autorizadoPor'] ?? 'Supervisor'
                    ];
                }

                $orden = [
                    'id' => $ordenData['idPedido'],
                    'fecha' => date('d/m/Y H:i', strtotime($ordenData['fechaHoraToma'])),
                    'mesa' => 'Mesa General',
                    'cliente' => $ordenData['nombreCliente'],
                    'doc' => $ordenData['emailCliente'] ?? '---',
                    'total' => (float)$ordenData['total'],
                    'estado' => strtoupper($ordenData['estadoPedido']),
                    'items' => $items,
                    'historial' => $historial
                ];

                if ($orden['estado'] === 'FACTURADO' || $orden['estado'] === 'PAGADO') {
                    $subtotalGeneral = $orden['total'] / 1.18;
                    $igv = $orden['total'] - $subtotalGeneral;
                    $orden['subtotalGeneral'] = number_format($subtotalGeneral, 2, '.', '');
                    $orden['igv'] = number_format($igv, 2, '.', '');
                    $orden['monto'] = number_format($orden['total'], 2, '.', '');
                }

            } else {
                $mensajeError = "No se encontró ninguna orden con ese criterio.";
            }
        }

        include __DIR__ . '/../views/cobranza/consultar_estado.php';
    }

    public function generarBoleta() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /mvc_restaurante/public/index.php?entidad=cobranza&action=consultar_estado');
            exit;
        }

        $idPedido = $_POST['idPedido'] ?? null;
        if (!$idPedido) die("ID de pedido no proporcionado.");

        $estadoActual = $this->pedidoModel->consultarEstado($idPedido);
        if ($estadoActual !== 'Pagado') {
            die("Solo se puede generar boleta para pedidos pagados.");
        }

        $this->pedidoModel->marcarComoFacturado($idPedido);
        header("Location: /mvc_restaurante/public/index.php?entidad=cobranza&action=consultar_estado&query=" . $idPedido);
        exit;
    }

    public function generarBoletaDesdeComprobantes() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /mvc_restaurante/public/');
            exit;
        }

        $idPedido = $_POST['idPedido'] ?? null;
        if (!$idPedido) die("Error ID");

        $estadoActual = $this->pedidoModel->consultarEstado($idPedido);
        if ($estadoActual !== 'Pagado') {
            die("Solo se puede generar boleta para pedidos pagados.");
        }

        $this->pedidoModel->marcarComoFacturado($idPedido);
        header("Location: /mvc_restaurante/public/index.php?entidad=cobranza&action=comprobantes&query=" . $idPedido . "&mensaje=Pedido+Pagado+y+Facturado%2C+FIN+DEL+PEDIDO");
        exit;
    }

    public function anularPago() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idPedido = $_POST['idPedido'] ?? null;
            $motivo = $_POST['motivo'] ?? '';
            $clave = $_POST['claveSupervisor'] ?? '';

            if ($idPedido && $motivo && !empty($clave)) {
                $this->pagoModel->anularPago($idPedido, $motivo, 'Supervisor');
                $this->pedidoModel->actualizarEstado($idPedido, 'Anulado');
                header("Location: /mvc_restaurante/public/index.php?entidad=cobranza&action=consultar_estado&query=" . $idPedido);
                exit;
            } else {
                die("Datos inválidos para anulación.");
            }
        }
    }

    public function mostrarComprobantes() {
        $comprobantes = $this->pagoModel->obtenerTodos();
        $ordenParaVistaPrevia = null;
        $idPedidoSeleccionado = $_GET['query'] ?? null;

        if ($idPedidoSeleccionado) {
            $ordenParaVistaPrevia = $this->pedidoModel->buscarPorCriterio($idPedidoSeleccionado);

            if ($ordenParaVistaPrevia) {
                $sqlItems = "SELECT dp.cantidad, dp.precioUnitario, (dp.cantidad * dp.precioUnitario) as subtotal, p.nombreProducto 
                             FROM detalle_pedido dp
                             INNER JOIN producto p ON dp.idProducto = p.idProducto
                             WHERE dp.idPedido = ?";
                $stmt = $this->db->prepare($sqlItems);
                $stmt->execute([$ordenParaVistaPrevia['idPedido']]);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $ordenParaVistaPrevia['items'] = $items;
            }
        }

        include __DIR__ . '/../views/cobranza/comprobantes.php';
    }
}
?>