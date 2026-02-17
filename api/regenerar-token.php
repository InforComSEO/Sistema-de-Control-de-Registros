<?php
/**
 * API: Regenerar Token API
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
    responderJSON(false, 'Solo administradores pueden regenerar tokens');
}

try {
    ejecutarQuery(
        "UPDATE tokens_api SET activo = 0 WHERE activo = 1"
    );
    
    $token_nuevo = 'token-' . bin2hex(random_bytes(32));
    
    ejecutarQuery(
        "INSERT INTO opciones_sistema (usuario_id, seccion, opcion_nombre, valor_json) 
         VALUES (NULL, ?, ?, ?) 
         ON DUPLICATE KEY UPDATE valor_json = VALUES(valor_json)",
        ['sistema', 'api_token', json_encode($token_nuevo)]
    );
    
    ejecutarQuery(
        "INSERT INTO tokens_api (token_valor, generado_por, razon, ip_generacion) 
         VALUES (?, ?, ?, ?)",
        [
            $token_nuevo,
            $_SESSION['usuario_id'],
            'Regeneración manual por administrador',
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]
    );
    
    registrarLog($_SESSION['usuario_id'], 'REGENERAR_TOKEN');
    
    responderJSON(true, 'Token regenerado correctamente', [
        'datos' => ['token' => $token_nuevo],
        'mensaje' => 'Actualiza la configuración del plugin WordPress con el nuevo token'
    ]);
    
} catch (Exception $e) {
    registrarError("Error en api/regenerar-token.php: " . $e->getMessage());
    responderJSON(false, 'Error: ' . $e->getMessage(), null, 500);
}

?>