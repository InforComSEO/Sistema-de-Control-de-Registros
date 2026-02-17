<?php
/**
 * API: Exportar a Excel/CSV
 */

require_once '../config/conexion.php';
require_once '../config/constantes.php';
require_once '../config/funciones.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    die('No autorizado');
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $busqueda = $input['busqueda'] ?? '';
    $filtros = $input['filtros'] ?? [];
    
    $tablas_metadata = obtenerFilas(
        "SELECT tabla_nombre, campos_json FROM formularios_metadata ORDER BY fecha_creacion DESC"
    );
    
    $todos_registros = [];
    $columnas_totales = [];
    
    foreach ($tablas_metadata as $meta) {
        $tabla = $meta['tabla_nombre'];
        $campos = json_decode($meta['campos_json'], true) ?: [];
        
        $query = "SELECT * FROM `$tabla` WHERE 1=1";
        $params = [];
        
        if (!empty($busqueda)) {
            $query .= " AND (";
            $condiciones = [];
            foreach ($campos as $campo) {
                $condiciones[] = "`$campo` LIKE ?";
            }
            $query .= implode(" OR ", $condiciones) . ")";
            for ($i = 0; $i < count($campos); $i++) {
                $params[] = "%$busqueda%";
            }
        }
        
        foreach ($filtros as $columna => $valor) {
            if (!empty($valor)) {
                if ($columna === 'fecha_desde' || $columna === 'fecha_hasta') {
                    continue;
                }
                $query .= " AND `$columna` = ?";
                $params[] = $valor;
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
        
        $stmt = ejecutarQuery($query, $params);
        $registros = $stmt->fetchAll();
        
        foreach ($registros as $registro) {
            $todos_registros[] = $registro;
        }
        
        foreach ($campos as $campo) {
            if (!in_array($campo, $columnas_totales)) {
                $columnas_totales[] = $campo;
            }
        }
    }
    
    $filename = 'registros_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    $encabezados = array_merge(['ID'], $columnas_totales);
    fputcsv($output, $encabezados, ';');
    
    foreach ($todos_registros as $registro) {
        $fila = [$registro['id'] ?? ''];
        foreach ($columnas_totales as $columna) {
            $fila[] = $registro[$columna] ?? '';
        }
        fputcsv($output, $fila, ';');
    }
    
    fclose($output);
    
    registrarLog($_SESSION['usuario_id'], 'EXPORTAR_EXCEL');
    
} catch (Exception $e) {
    registrarError("Error en api/exportar-excel.php: " . $e->getMessage());
    http_response_code(500);
    die('Error al exportar');
}

?>