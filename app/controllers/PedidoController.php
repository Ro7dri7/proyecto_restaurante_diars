<?php
// Ubicación: app/controllers/PedidoController.php

class PedidoController
{

    private $db;
    private $productoModel;
    private $clienteModel;
    private $pedidoModel;
    private $detallePedidoModel;

    public function __construct($db)
    {
        $this->db = $db;
        $this->productoModel = new Producto($this->db);
        $this->clienteModel = new Cliente($this->db);
        $this->pedidoModel = new Pedido($this->db);
        $this->detallePedidoModel = new DetallePedido($this->db);
    }

    /**
     * ACCIÓN: mostrarFormularioCrear
     * Carga los datos necesarios y la vista del formulario.
     */
    public function mostrarFormularioCrear()
    {
        $clientesStmt = $this->clienteModel->leerParaDropdown();
        $productosStmt = $this->productoModel->leerActivos();
        $clientes = $clientesStmt->fetchAll(PDO::FETCH_ASSOC);
        $productos = $productosStmt->fetchAll(PDO::FETCH_ASSOC);

        // 🔥 OBTENER EL PRÓXIMO ID DE PEDIDO
        $proximoID = $this->pedidoModel->obtenerProximoID();

        // PASAR LAS VARIABLES A LA VISTA
        $viewData = [
            'clientes' => $clientes,
            'productos' => $productos,
            'proximoID' => $proximoID
        ];
        extract($viewData); // Hace que las variables sean accesibles en la vista

        require_once __DIR__ . '/../views/pedido_form.php';
    }

    /**
     * ACCIÓN: guardarPedido (Versión Segura y Completa)
     * Recalcula todos los precios y totales en el servidor, utilizando transacciones.
     */
    public function guardarPedido()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php");
            exit();
        }
        if (empty($_POST['idCliente']) || empty($_POST['productos'])) {
            die("Error: Faltan datos (cliente o productos).");
        }

        $this->db->beginTransaction();

        try {
            $idCliente = filter_var($_POST['idCliente'], FILTER_VALIDATE_INT);
            if ($idCliente === false) {
                throw new Exception("ID de Cliente inválido.");
            }

            $pedidoID = $this->pedidoModel->crear($idCliente);
            if (!$pedidoID) {
                throw new Exception("No se pudo crear la cabecera del pedido.");
            }

            $productosPOST = $_POST['productos'];
            $subtotalGeneral = 0.0;
            $itemsGuardados = 0;

            for ($i = 0; $i < count($productosPOST['id']); $i++) {
                $idProducto = filter_var($productosPOST['id'][$i], FILTER_VALIDATE_INT);
                $cantidad = filter_var($productosPOST['cantidad'][$i], FILTER_VALIDATE_INT);

                if ($idProducto === false || $cantidad === false || $cantidad <= 0) {
                    continue;
                }

                $productoData = $this->productoModel->leerUno($idProducto);
                if (!$productoData) {
                    throw new Exception("Producto con ID $idProducto no encontrado en la base de datos.");
                }

                $precioUnitarioReal = (float)$productoData['precioProducto'];

                if (!$this->detallePedidoModel->crear($pedidoID, $idProducto, $cantidad, $precioUnitarioReal)) {
                    throw new Exception("No se pudo registrar el detalle del producto ID: $idProducto");
                }

                $subtotalGeneral += ($precioUnitarioReal * $cantidad);
                $itemsGuardados++;
            }

            if ($itemsGuardados === 0) {
                throw new Exception("El pedido debe contener al menos un producto válido.");
            }

            $igv = $subtotalGeneral * 0.18;
            $total = $subtotalGeneral + $igv;

            if (!$this->pedidoModel->actualizarTotales($pedidoID, $subtotalGeneral, $igv, $total)) {
                throw new Exception("No se pudo actualizar los totales de la cabecera del pedido.");
            }

            $this->db->commit();

            header("Location: index.php?action=exito&pedido_id=" . $pedidoID);
            exit();
        } catch (Exception $e) {
            $this->db->rollBack();
            die("Error al guardar el pedido: " . $e->getMessage());
        }
    }

    /**
     * ACCIÓN: exito
     * Muestra la página de confirmación de pedido.
     */
    public function mostrarExito()
    {
        $pedidoID = isset($_GET['pedido_id']) ? (int)$_GET['pedido_id'] : null;
        if ($pedidoID === null || $pedidoID <= 0) {
            header("Location: index.php");
            exit();
        }
        require_once __DIR__ . '/../views/pedido_exito.php';
    }

    /**
     * ACCIÓN: listarPedidos
     * Muestra la lista de todos los pedidos registrados.
     */
    public function listarPedidos()
    {
        $stmt = $this->pedidoModel->leerTodos();
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../views/pedido_lista.php';
    }

    /**
     * ACCIÓN: mostrarFormularioEditar
     * Carga los datos de un pedido existente para editarlo.
     */
    public function mostrarFormularioEditar()
    {
        $idPedido = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if ($idPedido === null || $idPedido <= 0) {
            header("Location: index.php?action=listar");
            exit();
        }

        // Cargar datos del pedido
        $pedidoData = $this->pedidoModel->leerUno($idPedido);
        if (!$pedidoData) {
            die("Pedido no encontrado.");
        }

        // Cargar detalles del pedido
        $detalleStmt = $this->detallePedidoModel->leerPorIdPedido($idPedido);
        $detalles = $detalleStmt->fetchAll(PDO::FETCH_ASSOC);

        // Cargar listas para dropdowns
        $clientesStmt = $this->clienteModel->leerParaDropdown();
        $productosStmt = $this->productoModel->leerActivos();
        $clientes = $clientesStmt->fetchAll(PDO::FETCH_ASSOC);
        $productos = $productosStmt->fetchAll(PDO::FETCH_ASSOC);

        // Pasar datos a la vista
        $viewData = [
            'pedidoData' => $pedidoData,
            'detalles' => $detalles,
            'clientes' => $clientes,
            'productos' => $productos
        ];
        extract($viewData);
        require_once __DIR__ . '/../views/pedido_editar.php';
    }
    /**
     * ACCIÓN: eliminarPedido
     * Elimina un pedido y sus detalles de forma segura (usando POST).
     */
    public function eliminarPedido()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=listar");
            exit();
        }

        $idPedido = isset($_POST['idPedido']) ? (int)$_POST['idPedido'] : null;
        if ($idPedido === null || $idPedido <= 0) {
            header("Location: index.php?action=listar");
            exit();
        }

        try {
            // Primero, eliminar todos los detalles del pedido
            $detalleStmt = $this->detallePedidoModel->leerPorIdPedido($idPedido);
            while ($detalle = $detalleStmt->fetch(PDO::FETCH_ASSOC)) {
                $this->detallePedidoModel->eliminar($detalle['idDetallePedido']);
            }

            // Luego, eliminar la cabecera del pedido
            $query = "DELETE FROM pedido WHERE idPedido = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $idPedido, PDO::PARAM_INT);
            $stmt->execute();

            // Redirigir con mensaje de éxito (opcional: podrías usar flash messages)
            header("Location: index.php?action=listar");
            exit();
        } catch (Exception $e) {
            die("Error al eliminar el pedido: " . $e->getMessage());
        }
    }
    /**
     * ACCIÓN: actualizarPedido
     * Actualiza un pedido existente (cabecera y detalles).
     */
    public function actualizarPedido()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=listar");
            exit();
        }

        $idPedido = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if ($idPedido === null || $idPedido <= 0) {
            header("Location: index.php?action=listar");
            exit();
        }

        if (empty($_POST['idCliente']) || empty($_POST['productos'])) {
            die("Error: Faltan datos (cliente o productos).");
        }

        $this->db->beginTransaction();

        try {
            // 1. Actualizar cabecera (cliente)
            $idCliente = filter_var($_POST['idCliente'], FILTER_VALIDATE_INT);
            if ($idCliente === false) {
                throw new Exception("ID de Cliente inválido.");
            }
            $this->pedidoModel->actualizarGenerico($idPedido, ['idCliente' => $idCliente]);

            // 2. Eliminar TODOS los detalles antiguos
            $detalleStmt = $this->detallePedidoModel->leerPorIdPedido($idPedido);
            while ($detalle = $detalleStmt->fetch(PDO::FETCH_ASSOC)) {
                $this->detallePedidoModel->eliminar($detalle['idDetallePedido']);
            }

            // 3. Insertar nuevos detalles
            $productosPOST = $_POST['productos'];
            $subtotalGeneral = 0.0;
            $itemsGuardados = 0;

            for ($i = 0; $i < count($productosPOST['id']); $i++) {
                $idProducto = filter_var($productosPOST['id'][$i], FILTER_VALIDATE_INT);
                $cantidad = filter_var($productosPOST['cantidad'][$i], FILTER_VALIDATE_INT);

                if ($idProducto === false || $cantidad === false || $cantidad <= 0) continue;

                $productoData = $this->productoModel->leerUno($idProducto);
                if (!$productoData) {
                    throw new Exception("Producto con ID $idProducto no encontrado.");
                }

                $precioUnitarioReal = (float)$productoData['precioProducto'];
                if (!$this->detallePedidoModel->crear($idPedido, $idProducto, $cantidad, $precioUnitarioReal)) {
                    throw new Exception("Error al crear detalle para producto ID: $idProducto");
                }

                $subtotalGeneral += ($precioUnitarioReal * $cantidad);
                $itemsGuardados++;
            }

            if ($itemsGuardados === 0) {
                throw new Exception("El pedido debe tener al menos un producto.");
            }

            // 4. Actualizar totales
            $igv = $subtotalGeneral * 0.18;
            $total = $subtotalGeneral + $igv;
            $this->pedidoModel->actualizarTotales($idPedido, $subtotalGeneral, $igv, $total);

            $this->db->commit();
            header("Location: index.php?action=exito&pedido_id=" . $idPedido);
            exit();
        } catch (Exception $e) {
            $this->db->rollBack();
            die("Error al actualizar el pedido: " . $e->getMessage());
        }
    }
    
    /**
     * ACCIÓN: mostrarDetallesPedido
     * Muestra los detalles completos de un pedido específico.
     */
    public function mostrarDetallesPedido()
    {
        $idPedido = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if ($idPedido === null || $idPedido <= 0) {
            header("Location: index.php?action=listar");
            exit();
        }

        // Cargar datos del pedido
        $pedidoData = $this->pedidoModel->leerUno($idPedido);
        if (!$pedidoData) {
            die("Pedido no encontrado.");
        }

        // Cargar detalles del pedido
        $detalleStmt = $this->detallePedidoModel->leerPorIdPedido($idPedido);
        $detalles = $detalleStmt->fetchAll(PDO::FETCH_ASSOC);

        // Pasar datos a la vista
        $viewData = [
            'pedidoData' => $pedidoData,
            'detalles' => $detalles
        ];
        extract($viewData);
        require_once __DIR__ . '/../views/pedido_detalle.php';
    }
}
