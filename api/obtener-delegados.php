<?php
/**
 * API: Obtener Datos de Delegados
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
    $busqueda = $input['busqueda'] ?? '';
    
    $tablas_metadata = obtenerFilas(
        "SELECT tabla_nombre FROM formularios_metadata"
    );
    
    $delegadosMap = [];
    
    foreach ($tablas_metadata as $meta) {
        $tabla = $meta['tabla_nombre'];
        
        $query = "SELECT 
                    DISTINCT delegado,
                    COUNT(*) as total_registros,
                    MAX(fecha) as ultima_actividad
                  FROM `$tabla`
                  WHERE delegado IS NOT NULL AND delegado != ''
                  GROUP BY delegado
                  ORDER BY total_registros DESC";
        
        $stmt = ejecutarQuery($query);
        $resultados = $stmt->fetchAll();
        
        foreach ($resultados as $row) {
            $delegado = $row['delegado'];
            
            if (!isset($delegadosMap[$delegado])) {
                $delegadosMap[$delegado] = [
                    'nombre' => $delegado,
                    'total_registros' => 0,
                    'ultima_actividad' => null
                ];
            }
            
            $delegadosMap[$delegado]['total_registros'] += $row['total_registros'];
            
            if ($row['ultima_actividad']) {
                if (!$delegadosMap[$delegado]['ultima_actividad'] || 
                    strtotime($row['ultima_actividad']) > strtotime($delegadosMap[$delegado]['ultima_actividad'])) {
                    $delegadosMap[$delegado]['ultima_actividad'] = $row['ultima_actividad'];
                }
            }
        }
    }
    
    $delegados = array_values($delegadosMap);
    
    if (!empty($busqueda)) {
        $delegados = array_filter($delegados, function($d) use ($busqueda) {
            return stripos($d['nombre'], $busqueda) !== false;
        });
        $delegados = array_values($delegados);
    }
    
    $totalDelegados = count($delegados);
    $totalRegistros = array_sum(array_map(function($d) { return $d['total_registros']; }, $delegados));
    $promedioPorDelegado = $totalDelegados > 0 ? round($totalRegistros / $totalDelegados, 2) : 0;
    
    responderJSON(true, 'Delegados obtenidos', [
        'delegados' => $delegados,
        'estadisticas' => [
            'total_delegados' => $totalDelegados,
            'registros_delegados' => $totalRegistros,
            'promedio_por_delegado' => $promedioPorDelegado
        ]
    ]);
    
} catch (Exception $e) {
    registrarError("Error en api/obtener-delegados.php: " . $e->getMessage());
    responderJSON(false, 'Error: ' . $e->getMessage(), null, 500);
}

?>