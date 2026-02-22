<?php
/**
 * Configuración de Base de Datos
 * Sistema de Control de Registros
 * Escuela Internacional de Psicología
 */

// Evitar acceso directo
if (!defined('SISTEMA_REGISTROS')) {
    http_response_code(403);
    exit('Acceso denegado');
}

// Configuración de la Base de Datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'zqgikadc_administracionphp');
define('DB_USER', 'zqgikadc_admin');
define('DB_PASS', 'aBjar1BKI4sW');
define('DB_CHARSET', 'utf8mb4');

/**
 * Clase de conexión a la Base de Datos (Singleton)
 */
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Error de conexión BD: " . $e->getMessage());
            die("Error de conexión. Contacte al administrador.");
        }
    }

    /**
     * Obtener instancia única de la conexión
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtener la conexión PDO
     */
    public function getConnection() {
        return $this->connection;
    }

    // Evitar clonación
    private function __clone() {}

    // Evitar deserialización
    public function __wakeup() {
        throw new Exception("No se puede deserializar singleton");
    }
}