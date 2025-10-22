<?php
// Ubicación: app/models/Cliente.php

/**
 * Clase Cliente
 * * Representa a un cliente en el sistema.
 * Se encarga de todas las interacciones con la tabla 'cliente'.
 * Implementa las operaciones CRUD (Crear, Leer, Actualizar, Eliminar)
 * de forma optimizada y flexible.
 */
class Cliente {

    // --- Atributos ---
    private $conn; // Objeto de conexión a la base de datos (PDO)
    private $table_name = "cliente"; // Nombre de la tabla en la BD

    /**
     * Constructor de la clase.
     * * @param PDO $db El objeto de conexión a la base de datos.
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    // --- MÉTODOS CRUD REFACTORIZADOS ---

    /**
     * CREAR un nuevo cliente usando un array de datos.
     * @param array $data Array asociativo (ej: ['nombreCliente' => 'Valor', 'emailCliente' => 'correo@...'])
     * @return bool Devuelve true si la creación fue exitosa, de lo contrario false.
     */
    public function crear($data) {
        // 1. Sanitizar todos los datos del array con un bucle
        $sanitized_data = [];
        foreach ($data as $key => $value) {
            $sanitized_data[$key] = htmlspecialchars(strip_tags($value));
        }

        // 2. Construir la consulta dinámicamente
        $columns = implode(', ', array_keys($sanitized_data));
        $placeholders = ':' . implode(', :', array_keys($sanitized_data));

        $query = "INSERT INTO " . $this->table_name . " ($columns) VALUES ($placeholders)";
        
        $stmt = $this->conn->prepare($query);

        // 3. Ejecutar la consulta pasando el array de datos
        try {
            if ($stmt->execute($sanitized_data)) {
                return true;
            }
        } catch (PDOException $e) {
            // Puedes registrar el error si lo necesitas: error_log($e->getMessage());
            return false;
        }
        return false;
    }

    /**
     * ACTUALIZAR un cliente existente usando un array de datos y un ID.
     * @param array $data Array asociativo con los campos a actualizar
     * @param int $id El ID del cliente a actualizar
     * @return bool Devuelve true si la actualización fue exitosa, de lo contrario false.
     */
    public function actualizar($data, $id) {
        // 1. Sanitizar datos y construir la parte "SET" de la consulta
        $sanitized_data = [];
        $set_parts = [];
        
        foreach ($data as $key => $value) {
            $placeholder = ':' . $key;
            $set_parts[] = "$key = $placeholder";
            $sanitized_data[$placeholder] = htmlspecialchars(strip_tags($value));
        }

        // 2. Agregar el ID al array de datos para vincularlo
        $sanitized_data[':id'] = htmlspecialchars(strip_tags($id));
        
        // 3. Unir las partes "SET"
        $setString = implode(', ', $set_parts);

        // 4. Construir la consulta final
        $query = "UPDATE " . $this->table_name . " SET $setString WHERE idCliente = :id";
        
        $stmt = $this->conn->prepare($query);

        // 5. Ejecutar pasando el array de datos completo
        try {
            if ($stmt->execute($sanitized_data)) {
                return true;
            }
        } catch (PDOException $e) {
            return false;
        }
        return false;
    }

    /**
     * ELIMINAR un cliente de la base de datos por su ID.
     * @param int $id El ID del cliente a eliminar
     * @return bool Devuelve true si la eliminación fue exitosa, de lo contrario false.
     */
    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE idCliente = :id";
        $stmt = $this->conn->prepare($query);
        
        $id = htmlspecialchars(strip_tags($id));
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }


    // --- MÉTODOS DE LECTURA ---

    /**
     * LEER todos los clientes (para un panel de administración).
     * @return PDOStatement El objeto statement con los resultados.
     */
    public function leerTodos() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY nombreCliente ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * LEER la información de un único cliente por su ID.
     * @param int $id El ID del cliente a buscar
     * @return array|false Un array asociativo con los datos del cliente o false si no se encuentra.
     */
    public function leerUno($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE idCliente = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        
        $id = htmlspecialchars(strip_tags($id));
        $stmt->bindParam(1, $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * LEER solo los clientes necesarios para un dropdown o <select>.
     * Es más eficiente que leerTodos() porque solo trae las columnas necesarias.
     * @return PDOStatement El objeto statement con los resultados.
     */
    public function leerParaDropdown() {
        $query = "SELECT idCliente, nombreCliente 
                  FROM " . $this->table_name . " 
                  ORDER BY nombreCliente ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>