<?php
// Ubicación: app/models/Pedido.php

/**
 * Clase Pedido
 * Representa la cabecera de un pedido en el sistema.
 * Contiene todos los métodos para el flujo de Cocina, Cobranza y Facturación.
 */
class Pedido
{
    private $conn;
    private $table_name = "pedido";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * REGISTRAR PEDIDO (Crear): Inserta la cabecera del pedido.
     */
    public function crear($idCliente)
    {
        $query = "INSERT INTO " . $this->table_name . "
                  SET
                    idCliente = :idCliente,
                    idEmpleado = 1, 
                    estadoPedido = 'Registrado',
                    subtotalGeneral = 0,
                    igv = 0,
                    total = 0,
                    fechaHoraToma = NOW()";

        $stmt = $this->conn->prepare($query);
        $idCliente = htmlspecialchars(strip_tags($idCliente));
        $stmt->bindParam(":idCliente", $idCliente);

        try {
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
        } catch (PDOException $e) {
            return false;
        }
        return false;
    }

    /**
     * ACTUALIZAR TOTALES: Recalcula subtotal, igv y total.
     */
    public function actualizarTotales($idPedido, $subtotal, $igv, $total)
    {
        $query = "UPDATE " . $this->table_name . "
                  SET
                    subtotalGeneral = :subtotal,
                    igv = :igv,
                    total = :total
                  WHERE
                    idPedido = :id";

        $stmt = $this->conn->prepare($query);

        $idPedido = htmlspecialchars(strip_tags($idPedido));
        $subtotal = htmlspecialchars(strip_tags($subtotal));
        $igv = htmlspecialchars(strip_tags($igv));
        $total = htmlspecialchars(strip_tags($total));

        $stmt->bindParam(':subtotal', $subtotal);
        $stmt->bindParam(':igv', $igv);
        $stmt->bindParam(':total', $total);
        $stmt->bindParam(':id', $idPedido);

        return $stmt->execute();
    }

    /**
     * LEER TODOS: Obtiene la lista general de pedidos.
     */
    public function leerTodos()
    {
        $query = "SELECT 
                    p.idPedido, p.fechaHoraToma, c.nombreCliente, p.total, p.estadoPedido
                  FROM " . $this->table_name . " p
                  LEFT JOIN cliente c ON p.idCliente = c.idCliente
                  ORDER BY p.fechaHoraToma DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * OBTENER PEDIDOS POR ESTADOS ESPECÍFICOS.
     * Obtiene todos los pedidos con cualquiera de los estados proporcionados.
     * Soporta opcionalmente un rango de fechas.
     */
    public function obtenerPedidosPorEstados($estados, $fechaInicio = null, $fechaFin = null) {
        // Validar que $estados sea un array
        if (!is_array($estados)) {
            $estados = [$estados];
        }

        // Construir la parte de la consulta para los estados
        $placeholders = str_repeat('?,', count($estados) - 1) . '?';
        
        $sql = "
            SELECT 
                p.idPedido, p.total, p.fechaHoraToma, c.nombreCliente, p.idCliente, p.estadoPedido
            FROM " . $this->table_name . " p
            INNER JOIN cliente c ON p.idCliente = c.idCliente
            WHERE p.estadoPedido IN ($placeholders)
        ";

        // Parámetros iniciales (los estados)
        $params = $estados;

        // Si se proporcionan fechas, agregamos el filtro a la consulta
        if ($fechaInicio && $fechaFin) {
            $sql .= " AND p.fechaHoraToma BETWEEN ? AND ?";
            $params[] = $fechaInicio;
            $params[] = $fechaFin . ' 23:59:59'; // Incluir todo el día final
        }

        $sql .= " ORDER BY p.fechaHoraToma DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * LEER UNO: Obtiene la cabecera de un pedido específico.
     * CORREGIDO: Ya no incluye dniCliente (columna inexistente).
     */
    public function leerUno($idPedido)
    {
        $query = "SELECT 
                    p.*, 
                    c.nombreCliente,
                    c.emailCliente
                  FROM " . $this->table_name . " p
                  LEFT JOIN cliente c ON p.idCliente = c.idCliente
                  WHERE p.idPedido = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $idPedido = htmlspecialchars(strip_tags($idPedido));
        $stmt->bindParam(1, $idPedido);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * ACTUALIZAR ESTADO (Genérico): Cambia el estado a cualquier valor string.
     */
    public function actualizarEstado($idPedido, $nuevoEstado)
    {
        $query = "UPDATE " . $this->table_name . " SET estadoPedido = :estado WHERE idPedido = :id";
        $stmt = $this->conn->prepare($query);

        $idPedido = htmlspecialchars(strip_tags($idPedido));
        $nuevoEstado = htmlspecialchars(strip_tags($nuevoEstado));

        $stmt->bindParam(':estado', $nuevoEstado);
        $stmt->bindParam(':id', $idPedido);

        return $stmt->execute();
    }

    /**
     * ENTREGAR PEDIDO: Marca como entregado y guarda la fecha.
     */
    public function entregarPedido($idPedido)
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET 
                    estadoPedido = 'Entregado', 
                    fechaHoraEntrega = NOW() 
                  WHERE idPedido = :id";

        $stmt = $this->conn->prepare($query);
        $idPedido = htmlspecialchars(strip_tags($idPedido));
        $stmt->bindParam(':id', $idPedido);

        return $stmt->execute();
    }

    /**
     * MODIFICAR PEDIDO (Cabecera): Actualización flexible de campos.
     */
    public function actualizarGenerico($idPedido, $data)
    {
        $sanitized_data = [];
        $set_parts = [];

        foreach ($data as $key => $value) {
            $placeholder = ':' . $key;
            $set_parts[] = "$key = $placeholder";
            $sanitized_data[$placeholder] = htmlspecialchars(strip_tags($value));
        }

        $sanitized_data[':id'] = htmlspecialchars(strip_tags($idPedido));
        $setString = implode(', ', $set_parts);

        $query = "UPDATE " . $this->table_name . " SET $setString WHERE idPedido = :id";
        $stmt = $this->conn->prepare($query);

        try {
            return $stmt->execute($sanitized_data);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * CONSULTAR ESTADO: Obtiene únicamente el estado de un pedido.
     */
    public function consultarEstado($idPedido)
    {
        $query = "SELECT estadoPedido FROM " . $this->table_name . " WHERE idPedido = ?";
        $stmt = $this->conn->prepare($query);
        $idPedido = htmlspecialchars(strip_tags($idPedido));
        $stmt->bindParam(1, $idPedido);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['estadoPedido'] : false;
    }

    /**
     * LEER POR RANGO DE FECHAS: Para reportes generales.
     */
    public function leerPorRangoDeFechas($fechaInicio, $fechaFin)
    {
        $fechaFinCompleta = $fechaFin . ' 23:59:59';
        $query = "SELECT 
                    p.idPedido, p.fechaHoraToma, c.nombreCliente, p.total, p.estadoPedido
                  FROM " . $this->table_name . " p
                  LEFT JOIN cliente c ON p.idCliente = c.idCliente
                  WHERE 
                    p.fechaHoraToma BETWEEN ? AND ?
                  ORDER BY 
                    p.fechaHoraToma DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $fechaInicio);
        $stmt->bindParam(2, $fechaFinCompleta);
        $stmt->execute();
        return $stmt;
    }

    /**
     * OBTENER DURACIÓN: Calcula tiempo entre toma y entrega.
     */
    public function getDuracionEnMinutos($idPedido)
    {
        $query = "SELECT 
                    TIMESTAMPDIFF(MINUTE, fechaHoraToma, fechaHoraEntrega) AS duracion
                  FROM " . $this->table_name . " 
                  WHERE 
                    idPedido = ? AND fechaHoraEntrega IS NOT NULL";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $idPedido);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (float) $row['duracion'] : null;
    }

    /**
     * OBTENER PRÓXIMO ID (Sugerencia visual).
     */
    public function obtenerProximoID()
    {
        $query = "SELECT MAX(idPedido) as max_id FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row['max_id'] ?? 0) + 1;
    }

    /**
     * OBTENER PEDIDOS POR ESTADO ESPECÍFICO (Individual).
     */
    public function obtenerPedidosPorEstado($estado)
    {
        $sql = "
            SELECT 
                p.idPedido, p.total, p.fechaHoraToma, c.nombreCliente, p.idCliente, p.estadoPedido
            FROM " . $this->table_name . " p
            INNER JOIN cliente c ON p.idCliente = c.idCliente
            WHERE p.estadoPedido = ?
            ORDER BY p.fechaHoraToma DESC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * CAMBIAR ESTADO SIGUIENTE (Flujo de Cocina):
     * Registrado -> Cocina -> Preparado -> Entregado
     */
    public function cambiarEstadoSiguiente($idPedido)
    {
        $query = "SELECT estadoPedido FROM " . $this->table_name . " WHERE idPedido = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$idPedido]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return false;

        $estadoActual = $row['estadoPedido'];
        $nuevoEstado = '';

        switch ($estadoActual) {
            case 'Registrado':
                $nuevoEstado = 'Cocina';
                break;
            case 'Cocina':
                $nuevoEstado = 'Preparado';
                break;
            case 'Preparado':
                $nuevoEstado = 'Entregado';
                break;
            case 'Entregado':
                return true; 
            default:
                return false;
        }

        if ($nuevoEstado) {
            return $this->actualizarEstado($idPedido, $nuevoEstado);
        }
        return false;
    }

    public function getSiguienteEstado($estadoActual)
    {
        $estados = [
            'Registrado' => 'Cocina',
            'Cocina' => 'Preparado',
            'Preparado' => 'Entregado',
            'Entregado' => 'Entregado'
        ];
        $siguiente = $estados[$estadoActual] ?? $estadoActual;
        $esFinal = ($estadoActual === 'Entregado');
        return ['siguiente' => $siguiente, 'esFinal' => $esFinal];
    }

    // =================================================================
    //  NUEVOS MÉTODOS PARA FLUJO DE PAGO Y FACTURACIÓN (BOLETA)
    // =================================================================

    /**
     * 1. OBTENER PEDIDOS PENDIENTES DE PAGO (Para Barra Lateral de Caja)
     * Lógica: Muestra todo lo que NO esté 'Pagado', 'Facturado' ni 'Cancelado'.
     */
    public function obtenerPedidosPendientesDePago()
    {
        $sql = "SELECT 
                    p.idPedido, 
                    p.total, 
                    p.fechaHoraToma, 
                    p.estadoPedido,
                    c.nombreCliente, 
                    c.idCliente
                FROM " . $this->table_name . " p
                INNER JOIN cliente c ON p.idCliente = c.idCliente
                WHERE p.estadoPedido NOT IN ('Pagado', 'Facturado', 'Cancelado')
                ORDER BY p.fechaHoraToma DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 2. CONFIRMAR PAGO
     * Cambia el estado del pedido a 'Pagado'.
     */
    public function confirmarPago($idPedido)
    {
        return $this->actualizarEstado($idPedido, 'Pagado');
    }

    /**
     * 3. MARCAR COMO FACTURADO
     * Cambia el estado a 'Facturado' después de emitir boleta/factura.
     */
    public function marcarComoFacturado($idPedido)
    {
        return $this->actualizarEstado($idPedido, 'Facturado');
    }

    /**
     * 4. OBTENER PEDIDOS PARA FACTURACIÓN (Para Generar Boleta)
     * Muestra SOLO los pedidos que ya pasaron por el proceso de pago ('Pagado').
     * CORREGIDO: ya no incluye c.dniCliente
     */
    public function obtenerPedidosPagados()
    {
        $sql = "SELECT 
                    p.idPedido, 
                    p.total, 
                    p.fechaHoraToma, 
                    p.estadoPedido,
                    c.nombreCliente, 
                    c.idCliente
                FROM " . $this->table_name . " p
                INNER JOIN cliente c ON p.idCliente = c.idCliente
                WHERE p.estadoPedido = 'Pagado'
                ORDER BY p.fechaHoraToma DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =================================================================
    //  MÉTODO PARA ACCEDER A LA CONEXIÓN (Uso interno controlado)
    // =================================================================
    public function getConnection()
    {
        return $this->conn;
    }

    // =================================================================
    //  BÚSQUEDA POR CRITERIO
    // =================================================================
    public function buscarPorCriterio($query) {
        // Primero intentar buscar por ID (número)
        if (is_numeric($query)) {
            $sql = "
                SELECT 
                    p.idPedido,
                    p.fechaHoraToma,
                    p.estadoPedido,
                    p.total,
                    c.nombreCliente,
                    c.emailCliente
                FROM " . $this->table_name . " p
                INNER JOIN cliente c ON p.idCliente = c.idCliente
                WHERE p.idPedido = ?
                LIMIT 1
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$query]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Luego buscar por nombre de cliente
        $sql = "
            SELECT 
                p.idPedido,
                p.fechaHoraToma,
                p.estadoPedido,
                p.total,
                c.nombreCliente,
                c.emailCliente
            FROM " . $this->table_name . " p
            INNER JOIN cliente c ON p.idCliente = c.idCliente
            WHERE c.nombreCliente LIKE ?
            ORDER BY p.fechaHoraToma DESC
            LIMIT 1
        ";
        $searchTerm = "%{$query}%";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$searchTerm]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}