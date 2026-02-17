<?php
/**
 * API: Obtener Datos de Asesores
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
    
    $asesoresMap = [];
    
    foreach ($tablas_metadata as $meta) {
        $tabla = $meta['tabla_nombre'];
        
        $query = "SELECT 
                    DISTINCT asesor,
                    COUNT(*) as total_registros,
                    MAX(fecha) as ultima_actividad
                  FROM `$tabla`
                  WHERE asesor IS NOT NULL AND asesor != ''
                  GROUP BY asesor
                  ORDER BY total_registros DESC";
        
        $stmt = ejecutarQuery($query);
        $resultados = $stmt->fetchAll();
        
        foreach ($resultados as $row) {
            $asesor = $row['asesor'];
            
            if (!isset($asesoresMap[$asesor])) {
                $asesoresMap[$asesor] = [
                    'nombre' => $asesor,
                    'total_registros' => 0,
                    'ultima_actividad' => null
                ];
            }
            
            $asesoresMap[$asesor]['total_registros'] += $row['total_registros'];
            
            if ($row['ultima_actividad']) {
                if (!$asesoresMap[$asesor]['ultima_actividad'] || 
                    strtotime($row['ultima_actividad']) > strtotime($asesoresMap[$asesor]['ultima_actividad'])) {
                    $asesoresMap[$asesor]['ultima_actividad'] = $row['ultima_actividad'];
                }
            }
        }
    }
    
    $asesores = array_values($asesoresMap);
    
    if (!empty($busqueda)) {
        $asesores = array_filter($asesores, function($a) use ($busqueda) {
            return stripos($a['nombre'], $busqueda) !== false;
        });
        $asesores = array_values($asesores);
    }
    
    $totalAsesores = count($asesores);
    $totalRegistros = array_sum(array_map(function($a) { return $a['total_registros']; }, $asesores));
    $promedioRegistros = $totalAsesores > 0 ? round($totalRegistros / $totalAsesores, 2) : 0;
    
    responderJSON(true, 'Asesores obtenidos', [
        'asesores' => $asesores,
        'estadisticas' => [
            'total_asesores' => $totalAsesores,
            'registros_asesores' => $totalRegistros,
            'promedio_por_asesor' => $promedioRegistros
        ]
    ]);
    
} catch (Exception $e) {
    registrarError("Error en api/obtener-asesores.php: " . $e->getMessage());
    responderJSON(false, 'Error: ' . $e->getMessage(), null, 500);
}

?>