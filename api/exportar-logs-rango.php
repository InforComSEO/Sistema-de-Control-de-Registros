<?php
/**
 * API: Exportar Logs por Rango a Excel
 */

require_once '../config/conexion.php';
require_once '../config/constantes.php';
require_once '../config/funciones.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    die('No autorizado');
}

$usuario = obtenerFila("SELECT tipo_usuario FROM usuarios WHERE id = ?", [$_SESSION['usuario_id']]);
if ($usuario['tipo_usuario'] !== 'administrador') {
    http_response_code(403);
    die('Solo administradores pueden exportar logs');
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $fecha_desde = $input['fecha_desde'] ?? null;
    $fecha_hasta = $input['fecha_hasta'] ?? null;
    
    if (!$fecha_desde || !$fecha_hasta) {
        http_response_code(400);
        die('Fechas requeridas');
    }
    
    try {
        $desde = DateTime::createFromFormat('d/m/Y', $fecha_desde)->format('Y-m-d 00:00:00');
        $hasta = DateTime::createFromFormat('d/m/Y', $fecha_hasta)->format('Y-m-d 23:59:59');
    } catch (Exception $e) {
        http_response_code(400);
        die('Formato de fecha inválido');
    }
    
    $logs = obtenerFilas(
        "SELECT l.*, u.nombre, u.apellidos 
         FROM logs l
         LEFT JOIN usuarios u ON l.usuario_id = u.id
         WHERE l.fecha_hora BETWEEN ? AND ?
         ORDER BY l.fecha_hora DESC",
        [$desde, $hasta]
    );
    
    $filename = 'logs_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    $encabezados = ['ID', 'Fecha y Hora', 'Usuario', 'Acción', 'Tabla', 'Registro ID', 'IP', 'Campo', 'Valor Anterior', 'Valor Nuevo'];
    fputcsv($output, $encabezados, ';');
    
    foreach ($logs as $log) {
        $fila = [
            $log['id'],
            $log['fecha_hora'],
            ($log['nombre'] ?? '') . ' ' . ($log['apellidos'] ?? ''),
            $log['tipo_accion'],
            $log['tabla_afectada'] ?? '-',
            $log['registro_id'] ?? '-',
            $log['ip_address'] ?? '-',
            $log['campo_modificado'] ?? '-',
            $log['valor_anterior'] ?? '-',
            $log['valor_nuevo'] ?? '-'
        ];
        fputcsv($output, $fila, ';');
    }
    
    fputcsv($output, [], ';');
    fputcsv($output, ['Total de logs:', count($logs)], ';');
    fputcsv($output, ['Rango:', "$fecha_desde - $fecha_hasta"], ';');
    fputcsv($output, ['Exportado:', date('d/m/Y H:i:s')], ';');
    fputcsv($output, ['Exportado por:', $_SESSION['nombre'] . ' ' . $_SESSION['apellidos']], ';');
    
    fclose($output);
    
    registrarLog($_SESSION['usuario_id'], 'EXPORTAR_LOGS');
    
} catch (Exception $e) {
    registrarError("Error en api/exportar-logs-rango.php: " . $e->getMessage());
    http_response_code(500);
    die('Error al exportar');
}

?>