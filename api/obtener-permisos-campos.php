<?php
/**
 * API: Obtener Permisos por Campo
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
    
    $usuario_id = $input['usuario_id'] ?? $_SESSION['usuario_id'];
    $tabla_nombre = $input['tabla_nombre'] ?? null;
    
    if (!$tabla_nombre) {
        responderJSON(false, 'Tabla requerida');
    }
    
    $permisos = obtenerFilas(
        "SELECT campo_nombre, puede_editar, puede_ver 
         FROM permisos_campos 
         WHERE usuario_id = ? AND tabla_nombre = ?",
        [$usuario_id, $tabla_nombre]
    );
    
    $resultado = [];
    foreach ($permisos as $permiso) {
        $resultado[$permiso['campo_nombre']] = [
            'editar' => $permiso['puede_editar'],
            'ver' => $permiso['puede_ver']
        ];
    }
    
    responderJSON(true, 'Permisos obtenidos', $resultado);
    
} catch (Exception $e) {
    registrarError("Error en api/obtener-permisos-campos.php: " . $e->getMessage());
    responderJSON(false, 'Error: ' . $e->getMessage(), null, 500);
}

?>