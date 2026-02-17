<?php
/**
 * API: Obtener Campos Dinámicos de Tabla
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
    $tabla_nombre = $input['tabla_nombre'] ?? null;
    
    if (!$tabla_nombre) {
        responderJSON(false, 'Tabla requerida');
    }
    
    $metadata = obtenerFila(
        "SELECT campos_json FROM formularios_metadata WHERE tabla_nombre = ?",
        [$tabla_nombre]
    );
    
    if (!$metadata) {
        responderJSON(false, 'Tabla no encontrada');
    }
    
    $campos = json_decode($metadata['campos_json'], true) ?: [];
    
    $campos_estandar = ['id', 'creado_desde', 'creado_desde_formulario', 'fecha', 'hora', 'fecha_creacion'];
    $todos_campos = array_unique(array_merge($campos, $campos_estandar));
    
    responderJSON(true, 'Campos obtenidos', ['campos' => $todos_campos]);
    
} catch (Exception $e) {
    registrarError("Error en api/obtener-campos-tabla.php: " . $e->getMessage());
    responderJSON(false, 'Error: ' . $e->getMessage(), null, 500);
}

?>