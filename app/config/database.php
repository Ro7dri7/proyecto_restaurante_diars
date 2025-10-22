<?php
// Ubicación: app/config/database.php

/*
 * 1. Cargar el "autoloader" de Composer
 * (Desde 'app/config', debemos subir 2 niveles (../..) 
 * para encontrar la carpeta 'vendor')
 */
require_once __DIR__ . '/../../vendor/autoload.php';

/*
 * 2. Cargar las variables de entorno del archivo .env
 * (Le decimos que el archivo .env está 2 niveles arriba)
 */
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();


class Database {

    // Atributos privados para las credenciales
    private $host;
    private $db_name;
    private $username;
    private $password;

    // Conexión pública para que los modelos la usen
    public $conn;

    // El Constructor se ejecuta al crear el objeto (new Database())
    // y lee las variables de $_ENV (cargadas por Dotenv)
    public function __construct() {
        $this->host = $_ENV['DB_HOST'];
        $this->db_name = $_ENV['DB_NAME'];
        $this->username = $_ENV['DB_USER'];
        $this->password = $_ENV['DB_PASS'];
    }

    // Método para obtener la conexión PDO
    public function getConnection() {
        $this->conn = null;

        // DSN (Data Source Name) - La cadena de conexión
        $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name;

        try {
            // 1. Crear la conexión PDO
            $this->conn = new PDO($dsn, $this->username, $this->password);

            // 2. Configurar el charset a UTF-8
            $this->conn->exec("set names utf8");

            // 3. Configurar PDO para que lance excepciones en errores
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $exception) {
            // Si algo falla, muestra el error y detiene la app
            echo "Error de conexión a la Base de Datos: " . $exception->getMessage();
            exit;
        }

        // 4. Devolver la conexión lista para usarse
        return $this->conn;
    }
}
?>