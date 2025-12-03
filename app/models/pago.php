<?php
// app/models/Pago.php

class Pago {
    private $conexion;
    private $table_name = "pago";

    public function __construct($db) {
        $this->conexion = $db;
    }

    /**
     * Registra un nuevo pago
     */
    public function registrarPago($idPedido, $idCliente, $monto, $metodoPago) {
        try {
            $sql = "INSERT INTO " . $this->table_name . " 
                    (idPedido, idCliente, fechaHora, monto, metodoPago, estado, notificadoPago) 
                    VALUES (?, ?, NOW(), ?, ?, 'Confirmado', 0)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$idPedido, $idCliente, $monto, $metodoPago]);
            return $this->conexion->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Error al registrar pago: " . $e->getMessage());
        }
    }

    /**
     * (RESTORED) Obtiene los datos básicos de un pedido para mostrar en formularios.
     */
    public function obtenerDatosPedido($idPedido) {
        $sql = "
            SELECT 
                p.idPedido,
                p.total,
                p.idCliente,
                c.nombreCliente
            FROM pedido p
            INNER JOIN cliente c ON p.idCliente = c.idCliente
            WHERE p.idPedido = ?
            LIMIT 1
        ";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$idPedido]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * (NEW) Obtiene el último pago registrado para un pedido (Para el timeline y estado)
     */
    public function obtenerPorPedido($idPedido) {
        $sql = "SELECT * FROM " . $this->table_name . " WHERE idPedido = ? ORDER BY idPago DESC LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$idPedido]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * (UPDATED) Anula el pago actualizando el estado y guardando el motivo
     */
    public function anularPago($idPedido, $motivo, $autorizadoPor) {
        try {
            $sql = "UPDATE " . $this->table_name . " 
                    SET estado = 'Anulado', 
                        motivoAnulacion = ?, 
                        autorizadoPor = ?, 
                        fechaAnulacion = NOW(),
                        notificadoPago = 0
                    WHERE idPedido = ?"; 
            
            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([$motivo, $autorizadoPor, $idPedido]);
        } catch (PDOException $e) {
            throw new Exception("Error al anular pago: " . $e->getMessage());
        }
    }

    /**
     * (UPDATED) Obtiene listado completo con ítems y cálculos
     * Mantiene tu lógica original para que el reporte de comprobantes no se rompa.
     */
    public function obtenerTodos() {
        try {
            // 1. Consulta principal: pagos confirmados + cliente + pedido
            $sql = "
                SELECT 
                    p.idPago,
                    p.fechaHora,
                    p.monto AS montoRecibido,
                    p.metodoPago,
                    p.estado,
                    c.nombreCliente,
                    c.emailCliente AS docIdentidad,
                    pe.idPedido,
                    pe.total AS totalPedido
                FROM " . $this->table_name . " p
                INNER JOIN cliente c ON p.idCliente = c.idCliente
                INNER JOIN pedido pe ON p.idPedido = pe.idPedido
                WHERE p.estado = 'Confirmado'
                ORDER BY p.fechaHora DESC
            ";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $comprobantes = [];

            // 2. Para cada pago, cargar los ítems del pedido y calcular IGV
            foreach ($resultados as $fila) {
                $total = floatval($fila['totalPedido']);
                $subtotal = $total / 1.18;
                $igv = $total - $subtotal;

                $fila['subtotalGeneral'] = number_format($subtotal, 2, '.', '');
                $fila['igv'] = number_format($igv, 2, '.', '');
                $fila['monto'] = number_format($total, 2, '.', '');

                // 3. Obtener ítems del pedido
                $sqlItems = "
                    SELECT 
                        dp.cantidad,
                        dp.precioUnitario,
                        (dp.cantidad * dp.precioUnitario) AS subtotal,
                        pr.nombreProducto
                    FROM detalle_pedido dp
                    INNER JOIN producto pr ON dp.idProducto = pr.idProducto
                    WHERE dp.idPedido = ?
                    ORDER BY dp.idDetallePedido
                ";

                $stmtItems = $this->conexion->prepare($sqlItems);
                $stmtItems->execute([$fila['idPedido']]);
                $fila['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

                $comprobantes[] = $fila;
            }

            return $comprobantes;
        } catch (PDOException $e) {
            error_log("Error en Pago::obtenerTodos(): " . $e->getMessage());
            return [];
        }
    }
}