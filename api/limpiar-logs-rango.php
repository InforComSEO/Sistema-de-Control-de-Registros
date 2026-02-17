<?php
/**
 * API: Limpiar Logs por Rango de Fechas
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
    responderJSON(false, 'Solo administradores pueden limpiar logs');
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $fecha_desde = $input['fecha_desde'] ?? null;
    $fecha_hasta = $input['fecha_hasta'] ?? null;
    
    if (!$fecha_desde || !$fecha_hasta) {
        responderJSON(false, 'Fechas requeridas');
    }
    
    try {
        $desde = DateTime::createFromFormat('d/m/Y', $fecha_desde)->format('Y-m-d 00:00:00');
        $hasta = DateTime::createFromFormat('d/m/Y', $fecha_hasta)->format('Y-m-d 23:59:59');
    } catch (Exception $e) {
        responderJSON(false, 'Formato de fecha inválido');
    }
    
    $resultado = obtenerFila(
        "SELECT COUNT(*) as total FROM logs WHERE fecha_hora BETWEEN ? AND ?",
        [$desde, $hasta]
    );
    
    $logsAEliminar = $resultado['total'];
    
    ejecutarQuery(
        "DELETE FROM logs WHERE fecha_hora BETWEEN ? AND ?",
        [$desde, $hasta]
    );
    
    registrarLog(
        $_SESSION['usuario_id'],
        'LIMPIAR_LOGS',
        null,
        null,
        'fecha_rango',
        "$fecha_desde - $fecha_hasta",
        "$logsAEliminar logs eliminados"
    );
    
    responderJSON(true, 'Logs eliminados correctamente', [
        'logs_eliminados' => $logsAEliminar,
        'rango' => "$fecha_desde - $fecha_hasta"
    ]);
    
} catch (Exception $e) {
    registrarError("Error en api/limpiar-logs-rango.php: " . $e->getMessage());
    responderJSON(false, 'Error: ' . $e->getMessage(), null, 500);
}

?>