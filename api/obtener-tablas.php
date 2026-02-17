<?php
/**
 * API: Obtener Lista de Tablas
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
    $tablas = obtenerFilas(
        "SELECT tabla_nombre, formulario_nombre, campos_json 
         FROM formularios_metadata 
         ORDER BY formulario_nombre ASC"
    );
    
    responderJSON(true, 'Tablas obtenidas', ['tablas' => $tablas]);
    
} catch (Exception $e) {
    registrarError("Error en api/obtener-tablas.php: " . $e->getMessage());
    responderJSON(false, 'Error: ' . $e->getMessage(), null, 500);
}

?>