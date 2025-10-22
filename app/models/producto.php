<?php
// Ubicación: app/models/Producto.php

class Producto {

    private $conn;
    private $table_name = "producto";

    // Las propiedades ahora pueden ser privadas o protegidas
    // ya que no las asignaremos desde fuera.
    private $idProducto;
    private $nombreProducto;
    private $descripcionProducto;
    private $precioProducto;
    private $estadoProducto;

    public function __construct($db) {
        $this->conn = $db;
    }

    // --- MÉTODOS CRUD REFACTORIZADOS ---

    /**
     * CREAR un nuevo producto usando un array de datos.
     * @param array $data Array asociativo (ej: ['nombreProducto' => 'Valor'])
     * @return bool
     */
    public function crear($data) {
        // 1. Sanitizar todos los datos del array con un bucle
        $sanitized_data = [];
        foreach ($data as $key => $value) {
            // Sanitiza el valor y lo guarda con su 'key'
            $sanitized_data[$key] = htmlspecialchars(strip_tags($value));
        }

        // 2. Construir la consulta dinámicamente
        // Obtiene las columnas (keys) del array
        $columns = implode(', ', array_keys($sanitized_data));
        
        // Crea los placeholders (ej: ":nombreProducto, :precioProducto")
        $placeholders = ':' . implode(', :', array_keys($sanitized_data));

        $query = "INSERT INTO " . $this->table_name . " ($columns) VALUES ($placeholders)";
        
        $stmt = $this->conn->prepare($query);

        // 3. Ejecutar la consulta pasando el array de datos
        // PDO mapeará automáticamente ':nombreProducto' con $sanitized_data['nombreProducto']
        try {
            if ($stmt->execute($sanitized_data)) {
                return true;
            }
        } catch (PDOException $e) {
            // Manejar error (opcional: registrar $e->getMessage())
            return false;
        }
        return false;
    }

    /**
     * ACTUALIZAR un producto existente usando un array de datos y un ID.
     * @param array $data Array asociativo (ej: ['nombreProducto' => 'Valor'])
     * @param int $id El ID del producto a actualizar
     * @return bool
     */
    public function actualizar($data, $id) {
        // 1. Sanitizar datos y construir la parte "SET"
        $sanitized_data = [];
        $set_parts = []; // Almacenará "columna = :columna"
        
        foreach ($data as $key => $value) {
            $placeholder = ':' . $key; // Ej: ":nombreProducto"
            $set_parts[] = "$key = $placeholder"; // Ej: "nombreProducto = :nombreProducto"
            
            // Sanitiza el valor y lo guarda con su placeholder como key
            $sanitized_data[$placeholder] = htmlspecialchars(strip_tags($value));
        }

        // 2. Agregar el ID al array de datos para vincularlo
        $sanitized_data[':id'] = htmlspecialchars(strip_tags($id));
        
        // 3. Unir las partes "SET"
        $setString = implode(', ', $set_parts); // "nombreProducto = :nombreProducto, ..."

        // 4. Construir la consulta final
        $query = "UPDATE " . $this->table_name . " SET $setString WHERE idProducto = :id";
        
        $stmt = $this->conn->prepare($query);

        // 5. Ejecutar pasando el array de datos completo
        try {
            if ($stmt->execute($sanitized_data)) {
                return true;
            }
        } catch (PDOException $e) {
            // Manejar error
            return false;
        }
        return false;
    }

    /**
     * ELIMINAR un producto (Este ya era eficiente).
     * @param int $id El ID del producto a eliminar
     * @return bool
     */
    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE idProducto = :id";
        $stmt = $this->conn->prepare($query);
        
        $id = htmlspecialchars(strip_tags($id));
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // --- MÉTODOS DE LECTURA (Estos se quedan igual) ---

    public function leerTodos() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY nombreProducto ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function leerActivos() {
        $query = "SELECT idProducto, nombreProducto, precioProducto 
                  FROM " . $this->table_name . " 
                  WHERE estadoProducto = 'Activo' 
                  ORDER BY nombreProducto ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * LEER un único producto por su ID.
     * @param int $id El ID del producto a buscar
     * @return array|false Un array asociativo con los datos del producto o false si no se encuentra.
     */
    public function leerUno($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE idProducto = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        
        $id = htmlspecialchars(strip_tags($id));
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row; // Devuelve la fila (o false si no hay fila)
    }
}
?>