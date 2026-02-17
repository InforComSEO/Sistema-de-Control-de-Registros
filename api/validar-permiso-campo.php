<?php
/**
 * API: Validar Permiso de Campo
 */

require_once '../config/conexion.php';
require_once '../config/constantes.php';
require_once '../config/funciones.php';

header('Content-Type: application/json; charset=utf-8');

session_start();
if (!isset($_SESSION['usuario_id'])) {
    responderJSON(false, 'No autorizado', null, 401);
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $usuario_id = $_SESSION['usuario_id'];
    $tabla_nombre = $input['tabla_nombre'] ?? null;
    $campo_nombre = $input['campo_nombre'] ?? null;
    
    if (!$tabla_nombre || !$campo_nombre) {
        responderJSON(false, 'Tabla y campo requeridos');
    }
    
    $permiso = obtenerFila(
        "SELECT puede_editar FROM permisos_campos 
         WHERE usuario_id = ? AND tabla_nombre = ? AND campo_nombre = ?",
        [$usuario_id, $tabla_nombre, $campo_nombre]
    );
    
    if (!$permiso) {
        responderJSON(true, 'Permiso permitido', ['puede_editar' => true]);
    }
    
    if ($permiso['puede_editar']) {
        responderJSON(true, 'Permiso permitido', ['puede_editar' => true]);
    } else {
        registrarLog(
            $usuario_id,
            'INTENTO_EDICION_SIN_PERMISO',
            $tabla_nombre,
            null,
            $campo_nombre
        );
        
        responderJSON(false, "No tienes permiso para editar el campo: $campo_nombre");
    }
    
} catch (Exception $e) {
    registrarError("Error en api/validar-permiso-campo.php: " . $e->getMessage());
    responderJSON(false, 'Error: ' . $e->getMessage(), null, 500);
}

?>