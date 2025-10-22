<?php
// Ubicación: app/models/Pedido.php

/**
 * Clase Pedido
 * * Representa la cabecera de un pedido en el sistema.
 * Contiene todos los métodos definidos en el 'CE Pedido'.
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
     * Cumple la responsabilidad de +registrarPedido()
     * @param int $idCliente
     * @return int|false Devuelve el ID del nuevo pedido o false si falla.
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
     * ACTUALIZAR TOTALES: Cumple la responsabilidad de +recalcularTotales()
     * @param int $idPedido
     * @param float $subtotal
     * @param float $igv
     * @param float $total
     * @return bool
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
     * LEER TODOS: Obtiene la lista de todos los pedidos.
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
     * LEER UNO: Obtiene la cabecera de un pedido específico con el nombre del cliente.
     */
    public function leerUno($idPedido)
    {
        $query = "SELECT 
                p.*, 
                c.nombreCliente
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
     * ACTUALIZAR ESTADO: Para cumplir con +confirmarPedido, +cancelarPedido
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
     * ENTREGAR PEDIDO: Para cumplir con +entregarPedido
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

    // --- MÉTODOS FALTANTES (AÑADIDOS) ---

    /**
     * MODIFICAR PEDIDO (Cabecera): Cumple la responsabilidad de +modificarPedido
     * Este método flexible permite actualizar campos de la cabecera.
     * (Modificar los *items* se haría desde el DetallePedidoModel).
     * @param int $idPedido El ID del pedido a actualizar.
     * @param array $data Array asociativo (ej: ['idCliente' => 5, 'idEmpleado' => 2])
     * @return bool
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
     * CONSULTAR ESTADO: Cumple la responsabilidad de +consultarEstado
     * Obtiene únicamente el estado de un pedido.
     * @param int $idPedido
     * @return string|false Devuelve el string del estado o false si no lo encuentra.
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
     * GENERAR REPORTE (Datos): Cumple la responsabilidad de +generarReportePedido
     * Obtiene los pedidos dentro de un rango de fechas.
     * @param string $fechaInicio (Formato 'YYYY-MM-DD')
     * @param string $fechaFin (Formato 'YYYY-MM-DD')
     * @return PDOStatement El objeto statement con los resultados del reporte.
     */
    public function leerPorRangoDeFechas($fechaInicio, $fechaFin)
    {
        // Aseguramos que la fecha fin incluya todo el día
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
     * OBTENER DURACIÓN EN MINUTOS: Para cumplir con +getDuracionEnMinutos
     * @param int $idPedido
     * @return float|null Devuelve los minutos o null si el pedido no está entregado.
     */
    public function getDuracionEnMinutos($idPedido)
    {
        // TIMESTAMPDIFF es una función de MySQL
        $query = "SELECT 
                    TIMESTAMPDIFF(MINUTE, fechaHoraToma, fechaHoraEntrega) AS duracion
                  FROM " . $this->table_name . " 
                  WHERE 
                    idPedido = ? AND fechaHoraEntrega IS NOT NULL";

        $stmt = $this->conn->prepare($query);

        $idPedido = htmlspecialchars(strip_tags($idPedido));
        $stmt->bindParam(1, $idPedido);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? (float) $row['duracion'] : null;
    }

    /**
     * OBTENER EL PRÓXIMO ID DE PEDIDO (para mostrarlo en el formulario)
     * Este método consulta el último ID usado y devuelve el siguiente.
     * ¡Importante: No crea el pedido, solo sugiere el número!
     * @return int El próximo ID disponible
     */
    public function obtenerProximoID()
    {
        $query = "SELECT MAX(idPedido) as max_id FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row['max_id'] ?? 0) + 1;
    }
}
