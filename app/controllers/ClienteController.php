<?php

class ClienteController
{
    private $db;
    private $clienteModel;

    public function __construct($db)
    {
        $this->db = $db;
        $this->clienteModel = new Cliente($db); // 👈 Instanciamos el modelo correctamente
    }

    /**
     * Muestra el formulario para crear un nuevo cliente.
     */
    public function mostrarFormularioCrear()
    {
        require_once __DIR__ . '/../views/cliente/crear.php';
    }

    /**
     * Procesa la creación de un nuevo cliente.
     */
    public function procesarCreacion()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?entidad=cliente&action=crear");
            exit();
        }

        $nombre = trim($_POST['nombreCliente'] ?? '');
        $telefono = trim($_POST['telefonoCliente'] ?? '');
        $email = trim($_POST['emailCliente'] ?? '');

        // Validaciones
        if (empty($nombre)) {
            $_SESSION['error'] = "El nombre del cliente es obligatorio.";
            header("Location: index.php?entidad=cliente&action=crear");
            exit();
        }

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "El correo electrónico no es válido.";
            header("Location: index.php?entidad=cliente&action=crear");
            exit();
        }

        // Verificar unicidad del email (si se proporciona)
        if (!empty($email)) {
            $stmt = $this->db->prepare("SELECT idCliente FROM cliente WHERE emailCliente = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = "Ya existe un cliente con ese correo electrónico.";
                header("Location: index.php?entidad=cliente&action=crear");
                exit();
            }
        }

        try {
            // ✅ Usar el método 'crear' del modelo (que ya tiene sanitización interna)
            $data = [
                'nombreCliente' => $nombre,
                'telefonoCliente' => $telefono,
                'emailCliente' => $email
            ];

            if ($this->clienteModel->crear($data)) {
                $_SESSION['exito'] = "Cliente creado con éxito.";
            } else {
                $_SESSION['error'] = "Error al guardar el cliente.";
            }

            header("Location: index.php?entidad=cliente&action=crear");
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = "Error al guardar el cliente: " . $e->getMessage();
            header("Location: index.php?entidad=cliente&action=crear");
            exit();
        }
    }

    /**
     * Muestra la lista de todos los clientes.
     */
    public function listar()
    {
        try {
            // ✅ Usar el método 'leerTodos' del modelo
            $stmt = $this->clienteModel->leerTodos();
            $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            require_once __DIR__ . '/../views/cliente/listar.php';
        } catch (Exception $e) {
            $_SESSION['error'] = "Error al cargar la lista de clientes.";
            header("Location: index.php");
            exit();
        }
    }

    /**
     * Muestra el formulario para editar un cliente existente.
     */
    public function mostrarFormularioEditar()
    {
        $idCliente = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if ($idCliente <= 0) {
            header("Location: index.php?entidad=cliente&action=listar");
            exit();
        }

        try {
            // ✅ Usar el método 'leerUno' del modelo
            $clienteData = $this->clienteModel->leerUno($idCliente);

            if (!$clienteData) {
                $_SESSION['error'] = "Cliente no encontrado.";
                header("Location: index.php?entidad=cliente&action=listar");
                exit();
            }

            require_once __DIR__ . '/../views/cliente/editar.php';
        } catch (Exception $e) {
            $_SESSION['error'] = "Error al cargar los datos del cliente.";
            header("Location: index.php?entidad=cliente&action=listar");
            exit();
        }
    }

    /**
     * Actualiza un cliente existente.
     */
    public function actualizarCliente()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?entidad=cliente&action=listar");
            exit();
        }

        $idCliente = isset($_POST['idCliente']) ? (int)$_POST['idCliente'] : null;
        if ($idCliente <= 0) {
            $_SESSION['error'] = "ID de cliente inválido.";
            header("Location: index.php?entidad=cliente&action=listar");
            exit();
        }

        $nombre = trim($_POST['nombreCliente'] ?? '');
        $telefono = trim($_POST['telefonoCliente'] ?? '');
        $email = trim($_POST['emailCliente'] ?? '');

        if (empty($nombre)) {
            $_SESSION['error'] = "El nombre es obligatorio.";
            header("Location: index.php?entidad=cliente&action=editar&id=" . $idCliente);
            exit();
        }

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Correo electrónico inválido.";
            header("Location: index.php?entidad=cliente&action=editar&id=" . $idCliente);
            exit();
        }

        // Verificar unicidad del email (excluyendo al propio cliente)
        if (!empty($email)) {
            $stmt = $this->db->prepare("SELECT idCliente FROM cliente WHERE emailCliente = ? AND idCliente != ?");
            $stmt->execute([$email, $idCliente]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = "Otro cliente ya tiene ese correo electrónico.";
                header("Location: index.php?entidad=cliente&action=editar&id=" . $idCliente);
                exit();
            }
        }

        try {
            // ✅ Usar el método 'actualizar' del modelo
            $data = [
                'nombreCliente' => $nombre,
                'telefonoCliente' => $telefono,
                'emailCliente' => $email
            ];

            if ($this->clienteModel->actualizar($data, $idCliente)) {
                $_SESSION['exito'] = "Cliente actualizado con éxito.";
            } else {
                $_SESSION['error'] = "Error al actualizar el cliente.";
            }

            header("Location: index.php?entidad=cliente&action=listar");
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = "Error al actualizar el cliente: " . $e->getMessage();
            header("Location: index.php?entidad=cliente&action=editar&id=" . $idCliente);
            exit();
        }
    }

    /**
     * Elimina un cliente (solo si no tiene pedidos asociados).
     */
    public function eliminarCliente()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?entidad=cliente&action=listar");
            exit();
        }

        $idCliente = isset($_POST['idCliente']) ? (int)$_POST['idCliente'] : null;
        if ($idCliente <= 0) {
            header("Location: index.php?entidad=cliente&action=listar");
            exit();
        }

        try {
            // Verificar si tiene pedidos
            $stmt = $this->db->prepare("SELECT idPedido FROM pedido WHERE idCliente = ? LIMIT 1");
            $stmt->execute([$idCliente]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = "No se puede eliminar: el cliente tiene pedidos registrados.";
                header("Location: index.php?entidad=cliente&action=listar");
                exit();
            }

            // ✅ Usar el método 'eliminar' del modelo
            if ($this->clienteModel->eliminar($idCliente)) {
                $_SESSION['exito'] = "Cliente eliminado con éxito.";
            } else {
                $_SESSION['error'] = "Error al eliminar el cliente.";
            }

            header("Location: index.php?entidad=cliente&action=listar");
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = "Error al eliminar el cliente: " . $e->getMessage();
            header("Location: index.php?entidad=cliente&action=listar");
            exit();
        }
    }
}