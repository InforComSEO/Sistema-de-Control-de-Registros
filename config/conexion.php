<?php
/**
 * Configuración de Conexión a Base de Datos
 * Escuela Internacional de Psicología - Sistema de Control de Registros
 */

// ===================================================================
// CONFIGURACIÓN DE BD
// ===================================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'zqgikadc_administracionphp');
define('DB_USER', 'zqgikadc_admin');
define('DB_PASS', 'aBjar1BKI4sW');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8mb4_unicode_ci');

// ===================================================================
// CREAR CONEXIÓN PDO
// ===================================================================
try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    
    $pdo = new PDO(
        $dsn,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
    );
    
    // Verificar conexión
    if (!$pdo) {
        throw new Exception("No se pudo establecer conexión a la BD");
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode([
        'error' => 'Error de conexión a base de datos',
        'mensaje' => $e->getMessage(),
        'código' => $e->getCode()
    ]));
}

// ===================================================================
// FUNCIÓN: Ejecutar query con seguridad
// ===================================================================
function ejecutarQuery($query, $parametros = []) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($parametros);
        return $stmt;
    } catch (PDOException $e) {
        registrarError($e->getMessage(), $query);
        throw new Exception("Error en la consulta: " . $e->getMessage());
    }
}

// ===================================================================
// FUNCIÓN: Obtener una fila
// ===================================================================
function obtenerFila($query, $parametros = []) {
    $stmt = ejecutarQuery($query, $parametros);
    return $stmt->fetch();
}

// ===================================================================
// FUNCIÓN: Obtener todas las filas
// ===================================================================
function obtenerFilas($query, $parametros = []) {
    $stmt = ejecutarQuery($query, $parametros);
    return $stmt->fetchAll();
}

// ===================================================================
// FUNCIÓN: Registrar errores
// ===================================================================
function registrarError($error, $query = '') {
    $archivo_log = __DIR__ . '/../logs/errores.log';
    
    // Crear directorio si no existe
    if (!is_dir(dirname($archivo_log))) {
        mkdir(dirname($archivo_log), 0755, true);
    }
    
    $contenido = "[" . date('Y-m-d H:i:s') . "] " . $error;
    if ($query) {
        $contenido .= " | Query: " . $query;
    }
    $contenido .= "\n";
    
    file_put_contents($archivo_log, $contenido, FILE_APPEND);
}

?>