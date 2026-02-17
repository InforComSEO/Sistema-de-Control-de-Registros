<?php
/**
 * API: Obtener Filtros Dinámicos
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
    $filtros = [];
    
    $cacheKey = 'filtros_sistema';
    $cacheArchivo = RUTA_CACHE . $cacheKey . '.json';
    
    if (file_exists($cacheArchivo)) {
        $tiempoCache = filemtime($cacheArchivo);
        $ahora = time();
        
        if (($ahora - $tiempoCache) < CACHE_FILTROS_DURACION) {
            $filtros = json_decode(file_get_contents($cacheArchivo), true);
            responderJSON(true, 'Filtros obtenidos del caché', ['filtros' => $filtros]);
        }
    }
    
    $tablas_metadata = obtenerFilas(
        "SELECT tabla_nombre FROM formularios_metadata ORDER BY fecha_creacion DESC"
    );
    
    $camposFiltrar = ['asesor', 'delegado', 'creado_desde'];
    
    foreach ($camposFiltrar as $campo) {
        $valores = [];
        
        foreach ($tablas_metadata as $meta) {
            $tabla = $meta['tabla_nombre'];
            
            $query = "SELECT DISTINCT `$campo` FROM `$tabla` WHERE `$campo` IS NOT NULL AND `$campo` != '' ORDER BY `$campo` ASC";
            
            $stmt = ejecutarQuery($query);
            $resultados = $stmt->fetchAll();
            
            foreach ($resultados as $row) {
                $valor = $row[$campo];
                if (!in_array($valor, $valores)) {
                    $valores[] = $valor;
                }
            }
        }
        
        if (!empty($valores)) {
            $filtros[$campo] = $valores;
        }
    }
    
    if (!is_dir(RUTA_CACHE)) {
        mkdir(RUTA_CACHE, 0755, true);
    }
    file_put_contents($cacheArchivo, json_encode($filtros));
    
    responderJSON(true, 'Filtros obtenidos correctamente', ['filtros' => $filtros]);
    
} catch (Exception $e) {
    registrarError("Error en api/obtener-filtros.php: " . $e->getMessage());
    responderJSON(false, 'Error al obtener filtros', null, 500);
}

?>