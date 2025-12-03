<?php
// Ubicación: app/models/Producto.php

class Producto {

    private $conn;
    private $table_name = "producto";

    // Propiedades (ahora incluye idCategoria)
    private $idProducto;
    private $nombreProducto;
    private $descripcionProducto;
    private $precioProducto;
    private $estadoProducto;
    private $idCategoria; // <-- Nueva propiedad

    public function __construct($db) {
        $this->conn = $db;
    }

    // --- MÉTODOS CRUD REFACTORIZADOS (ACTUALIZADOS) ---

    /**
     * CREAR un nuevo producto usando un array de datos.
     * @param array $data Array asociativo (ej: ['nombreProducto' => 'Valor', 'idCategoria' => 2])
     * @return bool
     */
    public function crear($data) {
        // 1. Sanitizar todos los datos del array con un bucle
        $sanitized_data = [];
        foreach ($data as $key => $value) {
            // Para idCategoria y precioProducto, no usamos htmlspecialchars (son numéricos)
            if ($key === 'idCategoria' || $key === 'precioProducto') {
                $sanitized_data[$key] = $value; // Se validará al enlazar
            } else {
                $sanitized_data[$key] = htmlspecialchars(strip_tags($value));
            }
        }

        // 2. Construir la consulta dinámicamente
        $columns = implode(', ', array_keys($sanitized_data));
        $placeholders = ':' . implode(', :', array_keys($sanitized_data));

        $query = "INSERT INTO " . $this->table_name . " ($columns) VALUES ($placeholders)";
        $stmt = $this->conn->prepare($query);

        // 3. Vincular parámetros de forma segura
        foreach ($sanitized_data as $key => $value) {
            if ($key === 'idCategoria') {
                $stmt->bindValue(':' . $key, (int)$value, PDO::PARAM_INT);
            } elseif ($key === 'precioProducto') {
                $stmt->bindValue(':' . $key, (float)$value, PDO::PARAM_STR);
            } else {
                $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
            }
        }

        // 4. Ejecutar la consulta
        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            return false;
        }
        return false;
    }

    /**
     * ACTUALIZAR un producto existente usando un array de datos y un ID.
     * @param array $data Array asociativo
     * @param int $id El ID del producto a actualizar
     * @return bool
     */
    public function actualizar($data, $id) {
        // 1. Sanitizar datos y construir la parte "SET"
        $sanitized_data = [];
        $set_parts = [];

        foreach ($data as $key => $value) {
            if ($key === 'idCategoria' || $key === 'precioProducto') {
                $sanitized_data[$key] = $value;
            } else {
                $sanitized_data[$key] = htmlspecialchars(strip_tags($value));
            }
            $set_parts[] = "$key = :$key";
        }

        $setString = implode(', ', $set_parts);
        $query = "UPDATE " . $this->table_name . " SET $setString WHERE idProducto = :id";
        $stmt = $this->conn->prepare($query);

        // 2. Vincular parámetros
        foreach ($sanitized_data as $key => $value) {
            if ($key === 'idCategoria') {
                $stmt->bindValue(':' . $key, (int)$value, PDO::PARAM_INT);
            } elseif ($key === 'precioProducto') {
                $stmt->bindValue(':' . $key, (float)$value, PDO::PARAM_STR);
            } else {
                $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
            }
        }
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);

        // 3. Ejecutar
        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            return false;
        }
        return false;
    }

    /**
     * ELIMINAR un producto.
     * @param int $id El ID del producto a eliminar
     * @return bool
     */
    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE idProducto = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // --- MÉTODOS DE LECTURA (ACTUALIZADOS PARA INCLUIR CATEGORÍA) ---

    public function leerTodos() {
        $query = "SELECT 
                    p.idProducto,
                    p.nombreProducto,
                    p.descripcionProducto,
                    p.precioProducto,
                    p.estadoProducto,
                    p.idCategoria,
                    c.nombreCategoria
                  FROM " . $this->table_name . " p
                  INNER JOIN categoria c ON p.idCategoria = c.idCategoria
                  ORDER BY p.nombreProducto ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function leerActivos() {
        $query = "SELECT 
                    p.idProducto,
                    p.nombreProducto,
                    p.precioProducto,
                    p.idCategoria,
                    c.nombreCategoria
                  FROM " . $this->table_name . " p
                  INNER JOIN categoria c ON p.idCategoria = c.idCategoria
                  WHERE p.estadoProducto = 'Activo'
                  ORDER BY p.nombreProducto ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * LEER un único producto por su ID (con categoría).
     * @param int $id El ID del producto a buscar
     * @return array|false
     */
    public function leerUno($id) {
        $query = "SELECT 
                    p.*,
                    c.nombreCategoria
                  FROM " . $this->table_name . " p
                  INNER JOIN categoria c ON p.idCategoria = c.idCategoria
                  WHERE p.idProducto = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * NUEVO: Obtener todas las categorías activas (para usar en formularios).
     * @return PDOStatement
     */
    public function obtenerCategorias() {
        $query = "SELECT idCategoria, nombreCategoria 
                  FROM categoria 
                  WHERE estadoCategoria = 'Activo'
                  ORDER BY nombreCategoria ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>