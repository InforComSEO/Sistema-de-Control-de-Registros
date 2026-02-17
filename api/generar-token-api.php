<?php
/**
 * API: Generar Token API Único
 */

require_once '../config/conexion.php';
require_once '../config/funciones.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $token = 'token-' . bin2hex(random_bytes(32));
    
    $query = "INSERT INTO opciones_sistema (usuario_id, seccion, opcion_nombre, valor_json) 
              VALUES (NULL, ?, ?, ?) 
              ON DUPLICATE KEY UPDATE valor_json = VALUES(valor_json)";
    
    $stmt = ejecutarQuery($query, [
        'sistema',
        'api_token',
        json_encode($token)
    ]);
    
    $query_token = "INSERT INTO tokens_api (token_valor, razon, ip_generacion) 
                    VALUES (?, ?, ?)";
    
    ejecutarQuery($query_token, [
        $token,
        'Generación automática en instalación',
        $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
    
    responderJSON(true, 'Token generado correctamente', ['token' => $token]);
    
} catch (Exception $e) {
    responderJSON(false, 'Error: ' . $e->getMessage(), null, 500);
}

?>