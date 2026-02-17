<?php
/**
 * API: Obtener Token API Actual
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
    responderJSON(false, 'Solo administradores pueden ver el token');
}

try {
    $resultado = obtenerFila(
        "SELECT valor_json FROM opciones_sistema 
         WHERE usuario_id IS NULL 
         AND seccion = 'sistema' 
         AND opcion_nombre = 'api_token'"
    );
    
    if ($resultado) {
        $token = json_decode($resultado['valor_json'], true);
        responderJSON(true, 'Token obtenido', ['token' => $token]);
    } else {
        responderJSON(false, 'Token no encontrado');
    }
    
} catch (Exception $e) {
    registrarError("Error en api/obtener-token.php: " . $e->getMessage());
    responderJSON(false, 'Error: ' . $e->getMessage(), null, 500);
}

?>