<?php
/**
 * API: Guardar Opciones de Usuario
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
    $opciones = $input['opciones'] ?? [];
    
    foreach ($opciones as $seccion => $datos) {
        foreach ($datos as $opcion_nombre => $valor) {
            ejecutarQuery(
                "INSERT INTO opciones_sistema (usuario_id, seccion, opcion_nombre, valor_json) 
                 VALUES (?, ?, ?, ?) 
                 ON DUPLICATE KEY UPDATE valor_json = VALUES(valor_json)",
                [
                    $usuario_id,
                    $seccion,
                    $opcion_nombre,
                    json_encode($valor)
                ]
            );
        }
    }
    
    registrarLog($_SESSION['usuario_id'], 'CAMBIAR_OPCIONES');
    
    responderJSON(true, 'Opciones guardadas correctamente');
    
} catch (Exception $e) {
    registrarError("Error en api/guardar-opciones.php: " . $e->getMessage());
    responderJSON(false, 'Error: ' . $e->getMessage(), null, 500);
}

?>