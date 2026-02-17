<?php
/**
 * API: Resetear Base de Datos
 */

require_once '../config/conexion.php';
require_once '../config/constantes.php';
require_once '../config/funciones.php';

header('Content-Type: application/json; charset=utf-8');

session_start();
if (!isset($_SESSION['usuario_id'])) {
    responderJSON(false, 'No autorizado', null, 401);
}

$usuario = obtenerFila("SELECT tipo_usuario FROM usuarios WHERE id = ?", [$_SESSION['usuario_id']]);
if ($usuario['tipo_usuario'] !== 'administrador') {
    responderJSON(false, 'Solo administradores pueden resetear BD');
}

try {
    $tablas = obtenerFilas(
        "SELECT tabla_nombre FROM formularios_metadata"
    );
    
    foreach ($tablas as $tabla_row) {
        $tabla = $tabla_row['tabla_nombre'];
        
        try {
            ejecutarQuery("DELETE FROM `$tabla`");
            ejecutarQuery("ALTER TABLE `$tabla` AUTO_INCREMENT = 1");
        } catch (Exception $e) {
            registrarError("Error limpiando tabla $tabla: " . $e->getMessage());
        }
    }
    
    try {
        ejecutarQuery("DELETE FROM logs");
        ejecutarQuery("ALTER TABLE logs AUTO_INCREMENT = 1");
        
        ejecutarQuery("DELETE FROM historial_cambios");
        ejecutarQuery("ALTER TABLE historial_cambios AUTO_INCREMENT = 1");
        
        ejecutarQuery("DELETE FROM cambios_estructura");
        ejecutarQuery("ALTER TABLE cambios_estructura AUTO_INCREMENT = 1");
    } catch (Exception $e) {
        registrarError("Error limpiando tablas del sistema: " . $e->getMessage());
    }
    
    $archivosCache = glob(RUTA_CACHE . '*.json');
    foreach ($archivosCache as $archivo) {
        unlink($archivo);
    }
    
    registrarLog($_SESSION['usuario_id'], 'RESETEAR_BD');
    
    responderJSON(true, 'Base de datos reseteada correctamente');
    
} catch (Exception $e) {
    registrarError("Error en api/resetear-bd.php: " . $e->getMessage());
    responderJSON(false, 'Error: ' . $e->getMessage(), null, 500);
}

?>