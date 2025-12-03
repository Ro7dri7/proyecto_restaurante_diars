<?php
// app/models/Reclamo.php

class Reclamo {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    // =========================================================
    // SECCIÓN 1: MÉTODOS PARA EL REGISTRO (Cliente/Mostrador)
    // =========================================================

    // Buscar cliente por nombre o email en la base de datos real
    public function buscarClientePorTermino($termino) {
        $termino = "%{$termino}%";
        $sql = "SELECT idCliente, nombreCliente, emailCliente, telefonoCliente 
                FROM cliente 
                WHERE nombreCliente LIKE ? OR emailCliente LIKE ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$termino, $termino]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener pedidos ENTREGADOS o FINALIZADOS de un cliente
    public function obtenerPedidosPorCliente($idCliente) {
        $sql = "SELECT 
                    p.idPedido,
                    p.fechaHoraToma AS fecha,
                    p.total,
                    p.estadoPedido,
                    GROUP_CONCAT(pr.nombreProducto, ' x', dp.cantidad SEPARATOR ', ') AS items
                FROM pedido p
                INNER JOIN detalle_pedido dp ON p.idPedido = dp.idPedido
                INNER JOIN producto pr ON dp.idProducto = pr.idProducto
                WHERE p.idCliente = ? 
                  AND p.estadoPedido IN ('Entregado', 'Facturado', 'Finalizado')
                GROUP BY p.idPedido
                ORDER BY p.fechaHoraToma DESC
                LIMIT 10";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idCliente]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Crear reclamo en la base de datos
    public function crear($idPedido, $idCliente, $productoAfectado, $motivo, $metodoDevolucion, $montoSeleccionado = null) {
        // Estado inicial 'Solicitado'
        $sql = "INSERT INTO reclamo (
            idPedido, idCliente, productoAfectado, motivo, metodoDevolucion, montoSeleccionado, estadoReclamo, fechaSolicitud
        ) VALUES (?, ?, ?, ?, ?, ?, 'Solicitado', NOW())";

        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([
            $idPedido,
            $idCliente,
            $productoAfectado,
            $motivo,
            $metodoDevolucion,
            $montoSeleccionado
        ]);

        if ($success) {
            return $this->db->lastInsertId(); // Devuelve el idReclamo generado
        }
        return false;
    }

    // =========================================================
    // SECCIÓN 2: MÉTODOS PARA VALIDACIÓN (Administrador)
    // =========================================================

    // Obtener reclamos pendientes (Estado 'Solicitado')
    public function obtenerReclamosPendientes() {
        $sql = "SELECT 
                    r.idReclamo,
                    r.idPedido,
                    r.idCliente,
                    r.productoAfectado,
                    r.motivo,
                    r.metodoDevolucion,
                    'Media' AS prioridad, 
                    r.fechaSolicitud,
                    c.nombreCliente AS clienteNombre,
                    p.total AS totalPedido
                FROM reclamo r
                INNER JOIN cliente c ON r.idCliente = c.idCliente
                INNER JOIN pedido p ON r.idPedido = p.idPedido
                WHERE r.estadoReclamo = 'Solicitado'
                ORDER BY r.fechaSolicitud DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Actualizar el estado de un reclamo (Validado/Rechazado)
    public function actualizarEstado($idReclamo, $nuevoEstado, $comentario = null) {
        $sql = "UPDATE reclamo 
                SET estadoReclamo = ?, comentarioResolucion = ?, fechaResolucion = NOW()
                WHERE idReclamo = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nuevoEstado, $comentario, $idReclamo]);
    }

    // =========================================================
    // SECCIÓN 3: MÉTODOS PARA NOTIFICACIÓN (Administrador)
    // =========================================================

    // Obtener reclamos listos para notificar (Validado/Rechazado)
    public function obtenerReclamosResueltos() {
        $sql = "SELECT 
                    r.idReclamo,
                    r.idPedido,
                    r.idCliente,
                    r.productoAfectado,
                    r.motivo,
                    r.metodoDevolucion,
                    r.estadoReclamo,
                    r.comentarioResolucion,
                    r.fechaResolucion,
                    c.nombreCliente AS clienteNombre,
                    c.emailCliente AS clienteEmail,
                    c.telefonoCliente AS clienteTelefono,
                    p.total AS totalPedido
                FROM reclamo r
                INNER JOIN cliente c ON r.idCliente = c.idCliente
                INNER JOIN pedido p ON r.idPedido = p.idPedido
                WHERE r.estadoReclamo IN ('Validado', 'Rechazado')
                ORDER BY r.fechaResolucion DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Marcar un reclamo como notificado (cambia estado a 'Notificado')
    public function marcarComoNotificado($idReclamo) {
        // Actualizamos el estado a 'Notificado' para que salga de la lista de pendientes de notificar
        $sql = "UPDATE reclamo 
                SET estadoReclamo = 'Notificado', fechaResolucion = NOW() 
                WHERE idReclamo = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$idReclamo]);
    }

    // =========================================================
    // SECCIÓN 4: MÉTODOS PARA CONSULTA HISTÓRICA (Administrador)
    // =========================================================

    // Obtener todos los reclamos (sin filtrar por estado)
    public function obtenerTodosLosReclamos() {
        $sql = "SELECT 
                    r.idReclamo,
                    r.idPedido,
                    r.idCliente,
                    r.productoAfectado,
                    r.motivo,
                    r.metodoDevolucion,
                    r.estadoReclamo,
                    'Media' AS prioridad,
                    r.fechaSolicitud,
                    r.fechaResolucion,
                    c.nombreCliente AS clienteNombre,
                    c.emailCliente AS clienteEmail,
                    c.telefonoCliente AS clienteTelefono,
                    p.total AS totalPedido
                FROM reclamo r
                INNER JOIN cliente c ON r.idCliente = c.idCliente
                INNER JOIN pedido p ON r.idPedido = p.idPedido
                ORDER BY r.fechaSolicitud DESC"; 

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================================================
    // SECCIÓN 5: MÉTODOS PARA PROCESAR REEMBOLSO (CUS5 - Tesorería)
    // =========================================================

    // ✅ Obtiene reclamos con estado "Validado" (listos para reembolsar)
    public function obtenerReclamosParaReembolso() {
        $sql = "SELECT 
                    r.idReclamo,
                    r.idPedido,
                    r.idCliente,
                    r.productoAfectado,
                    r.motivo,
                    r.metodoDevolucion,
                    r.montoSeleccionado,
                    r.estadoReclamo,
                    r.comentarioResolucion,
                    r.fechaSolicitud,
                    r.fechaResolucion,
                    c.nombreCliente AS clienteNombre,
                    c.emailCliente AS clienteEmail,
                    c.telefonoCliente AS clienteTelefono,
                    p.total AS totalPedido
                FROM reclamo r
                INNER JOIN cliente c ON r.idCliente = c.idCliente
                INNER JOIN pedido p ON r.idPedido = p.idPedido
                WHERE r.estadoReclamo = 'Validado'
                ORDER BY r.fechaResolucion DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ Actualiza el estado a "Reembolsado" y guarda el número de operación
    public function marcarComoReembolsado($idReclamo, $numeroOperacion) {
        // Actualizamos el comentario para incluir la operación y el estado
        // Usamos CONCAT_WS para manejar casos donde el comentario previo sea nulo
        $sql = "UPDATE reclamo 
                SET estadoReclamo = 'Reembolsado', 
                    comentarioResolucion = CONCAT_WS('. ', comentarioResolucion, CONCAT('Reembolsado. Operación: ', ?)),
                    fechaResolucion = NOW()
                WHERE idReclamo = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$numeroOperacion, $idReclamo]);
    }
}