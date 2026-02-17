<?php
/**
 * API: Obtener Estadísticas del Sistema
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
    $filtros = $input['filtros'] ?? [];
    
    $estadisticas = [];
    
    $tablas_metadata = obtenerFilas(
        "SELECT tabla_nombre FROM formularios_metadata"
    );
    
    $totalRegistros = 0;
    $registrosHoy = 0;
    $registrosMes = 0;
    $asesoresUnicos = 0;
    $delegadosUnicos = 0;
    
    foreach ($tablas_metadata as $meta) {
        $tabla = $meta['tabla_nombre'];
        
        $query = "SELECT COUNT(*) as total FROM `$tabla` WHERE 1=1";
        $params = [];
        
        foreach ($filtros as $col => $val) {
            if (!empty($val) && $col !== 'fecha_desde' && $col !== 'fecha_hasta') {
                $query .= " AND `$col` = ?";
                $params[] = $val;
            }
        }
        
        if (!empty($filtros['fecha_desde'])) {
            $query .= " AND `fecha` >= ?";
            $params[] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $query .= " AND `fecha` <= ?";
            $params[] = $filtros['fecha_hasta'];
        }
        
        $result = obtenerFila($query, $params);
        $totalRegistros += $result['total'];
        
        $queryHoy = str_replace("WHERE 1=1", "WHERE DATE(fecha) = CURDATE()", $query);
        $resultHoy = obtenerFila($queryHoy, $params);
        $registrosHoy += $resultHoy['total'];
        
        $queryMes = str_replace("WHERE 1=1", "WHERE MONTH(fecha) = MONTH(NOW()) AND YEAR(fecha) = YEAR(NOW())", $query);
        $resultMes = obtenerFila($queryMes, $params);
        $registrosMes += $resultMes['total'];
        
        $queryAsesores = "SELECT COUNT(DISTINCT asesor) as total FROM `$tabla` WHERE asesor IS NOT NULL AND asesor != ''";
        $resultAsesores = obtenerFila($queryAsesores);
        $asesoresUnicos += $resultAsesores['total'];
        
        $queryDelegados = "SELECT COUNT(DISTINCT delegado) as total FROM `$tabla` WHERE delegado IS NOT NULL AND delegado != ''";
        $resultDelegados = obtenerFila($queryDelegados);
        $delegadosUnicos += $resultDelegados['total'];
    }
    
    $estadisticas = [
        'total_registros' => $totalRegistros,
        'registros_hoy' => $registrosHoy,
        'registros_mes' => $registrosMes,
        'asesores_unicos' => $asesoresUnicos,
        'delegados_unicos' => $delegadosUnicos,
        'usuarios_activos' => obtenerFila("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1")['total'],
        'consultores' => obtenerFila("SELECT COUNT(*) as total FROM usuarios WHERE tipo_usuario = 'consultor'")['total']
    ];
    
    responderJSON(true, 'Estadísticas obtenidas', $estadisticas);
    
} catch (Exception $e) {
    registrarError("Error en api/obtener-estadisticas.php: " . $e->getMessage());
    responderJSON(false, 'Error: ' . $e->getMessage(), null, 500);
}

?>