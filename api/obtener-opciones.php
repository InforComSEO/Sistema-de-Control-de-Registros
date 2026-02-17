<?php
/**
 * API: Obtener Opciones de Usuario
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
    
    $opciones = obtenerFilas(
        "SELECT seccion, opcion_nombre, valor_json FROM opciones_sistema WHERE usuario_id = ?",
        [$usuario_id]
    );
    
    $resultado = [];
    foreach ($opciones as $opcion) {
        if (!isset($resultado[$opcion['seccion']])) {
            $resultado[$opcion['seccion']] = [];
        }
        $resultado[$opcion['seccion']][$opcion['opcion_nombre']] = json_decode($opcion['valor_json'], true);
    }
    
    responderJSON(true, 'Opciones obtenidas', $resultado);
    
} catch (Exception $e) {
    registrarError("Error en api/obtener-opciones.php: " . $e->getMessage());
    responderJSON(false, 'Error: ' . $e->getMessage(), null, 500);
}

?>