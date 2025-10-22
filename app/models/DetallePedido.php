<?php
// Ubicación: app/models/DetallePedido.php

/**
 * Clase DetallePedido
 * * Representa una línea de producto dentro de un pedido.
 * Se encarga de todas las interacciones con la tabla 'detalle_pedido'.
 */
class DetallePedido {

    private $conn;
    private $table_name = "detalle_pedido";

    /**
     * Constructor
     * @param PDO $db Objeto de conexión a la base de datos.
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    // --- MÉTODOS CRUD ---

    /**
     * CREAR un nuevo detalle de pedido.
     * Este método es seguro porque calcula el subtotal en el servidor.
     * @param int $idPedido
     * @param int $idProducto
     * @param int $cantidad
     * @param float $precioUnitario El precio REAL (obtenido de la BD)
     * @return bool Devuelve true si la creación fue exitosa.
     */
    public function crear($idPedido, $idProducto, $cantidad, $precioUnitario) {
        
        // ---- CÁLCULO DE RESPONSABILIDAD (CE DetallePedido) ----
        // Aquí se cumple el método +calcularSubtotal()
        $subtotal = $precioUnitario * $cantidad;
        // ----------------------------------------------------

        $query = "INSERT INTO " . $this->table_name . "
                  SET
                    idPedido = :idPedido,
                    idProducto = :idProducto,
                    cantidad = :cantidad,
                    precioUnitario = :precioUnitario,
                    subtotal = :subtotal";
        
        $stmt = $this->conn->prepare($query);

        // Sanitizar datos
        $idPedido = htmlspecialchars(strip_tags($idPedido));
        $idProducto = htmlspecialchars(strip_tags($idProducto));
        $cantidad = htmlspecialchars(strip_tags($cantidad));
        $precioUnitario = htmlspecialchars(strip_tags($precioUnitario));
        $subtotal = htmlspecialchars(strip_tags($subtotal));

        // Vincular parámetros
        $stmt->bindParam(":idPedido", $idPedido);
        $stmt->bindParam(":idProducto", $idProducto);
        $stmt->bindParam(":cantidad", $cantidad);
        $stmt->bindParam(":precioUnitario", $precioUnitario);
        $stmt->bindParam(":subtotal", $subtotal);

        // Ejecutar
        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * LEER todos los detalles (items) de un pedido específico.
     * Vital para ver un pedido, generar una factura o un reclamo.
     * @param int $idPedido El ID del pedido principal.
     * @return PDOStatement El objeto statement con los resultados.
     */
    public function leerPorIdPedido($idPedido) {
        // Hacemos un JOIN con la tabla 'producto' para obtener el nombre del producto
        $query = "SELECT 
                    d.idDetallePedido, 
                    d.idProducto, 
                    p.nombreProducto, 
                    d.cantidad, 
                    d.precioUnitario, 
                    d.subtotal
                  FROM " . $this->table_name . " d
                  LEFT JOIN producto p ON d.idProducto = p.idProducto
                  WHERE 
                    d.idPedido = ?
                  ORDER BY 
                    p.nombreProducto ASC";

        $stmt = $this->conn->prepare($query);

        $idPedido = htmlspecialchars(strip_tags($idPedido));
        $stmt->bindParam(1, $idPedido);
        
        $stmt->execute();
        return $stmt;
    }

    /**
     * LEER un único ítem de detalle por su ID principal (idDetallePedido).
     * @param int $idDetalle El ID del detalle a buscar.
     * @return array|false Un array asociativo con los datos o false si no se encuentra.
     */
    public function leerUno($idDetalle) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE idDetallePedido = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        
        $idDetalle = htmlspecialchars(strip_tags($idDetalle));
        $stmt->bindParam(1, $idDetalle);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * ACTUALIZAR un ítem de detalle existente (ej. cambiar la cantidad).
     * También recalcula el subtotal para mantener la consistencia.
     * @param int $idDetalle El ID del detalle (idDetallePedido) a actualizar.
     * @param int $cantidad La nueva cantidad.
     * @param float $precioUnitario El precio unitario (para recalcular).
     * @return bool Devuelve true si la actualización fue exitosa.
     */
    public function actualizar($idDetalle, $cantidad, $precioUnitario) {
        // Recalcular el subtotal
        $subtotal = $cantidad * $precioUnitario;

        $query = "UPDATE " . $this->table_name . "
                  SET
                    cantidad = :cantidad,
                    subtotal = :subtotal
                  WHERE
                    idDetallePedido = :id";
        
        $stmt = $this->conn->prepare($query);

        // Sanitizar
        $idDetalle = htmlspecialchars(strip_tags($idDetalle));
        $cantidad = htmlspecialchars(strip_tags($cantidad));
        $subtotal = htmlspecialchars(strip_tags($subtotal));

        // Vincular
        $stmt->bindParam(':cantidad', $cantidad);
        $stmt->bindParam(':subtotal', $subtotal);
        $stmt->bindParam(':id', $idDetalle);

        return $stmt->execute();
    }

    /**
     * ELIMINAR un ítem de detalle específico.
     * Útil si un usuario quiere quitar un producto de un pedido ya creado.
     * @param int $idDetalle El ID del detalle (idDetallePedido) a eliminar.
     * @return bool Devuelve true si la eliminación fue exitosa.
     */
    public function eliminar($idDetalle) {
        $query = "DELETE FROM " . $this->table_name . " WHERE idDetallePedido = :id";
        $stmt = $this->conn->prepare($query);
        
        $idDetalle = htmlspecialchars(strip_tags($idDetalle));
        $stmt->bindParam(':id', $idDetalle);

        return $stmt->execute();
    }
}
?>