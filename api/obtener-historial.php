<?php
/**
 * API: Obtener Historial de Cambios
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
    $registro_id = $input['registro_id'] ?? null;
    
    if (!$registro_id) {
        responderJSON(false, 'Registro requerido');
    }
    
    $historial = obtenerFilas(
        "SELECT hc.*, u.nombre, u.apellidos 
         FROM historial_cambios hc
         JOIN usuarios u ON hc.usuario_id = u.id
         WHERE hc.registro_id = ?
         ORDER BY hc.fecha_hora DESC",
        [$registro_id]
    );
    
    $historialFormato = [];
    foreach ($historial as $cambio) {
        $historialFormato[] = [
            'fecha_hora' => date('d/m/Y H:i', strtotime($cambio['fecha_hora'])),
            'usuario' => $cambio['nombre'] . ' ' . $cambio['apellidos'],
            'campo_nombre' => ucfirst($cambio['campo_nombre']),
            'valor_anterior' => $cambio['valor_anterior'],
            'valor_nuevo' => $cambio['valor_nuevo']
        ];
    }
    
    responderJSON(true, 'Historial obtenido', $historialFormato);
    
} catch (Exception $e) {
    registrarError("Error en api/obtener-historial.php: " . $e->getMessage());
    responderJSON(false, 'Error: ' . $e->getMessage(), null, 500);
}

?>