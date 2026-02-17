<?php
/**
 * API: Obtener Datos de Registros
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
    $usuario = obtenerFila(
        "SELECT id, tipo_usuario FROM usuarios WHERE id = ?",
        [$_SESSION['usuario_id']]
    );
    
    if (!$usuario) {
        responderJSON(false, 'Usuario no encontrado', null, 401);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $pagina = $input['pagina'] ?? 0;
    $limite = $input['limite'] ?? REGISTROS_POR_PAGINA;
    $busqueda = $input['busqueda'] ?? '';
    $filtros = $input['filtros'] ?? [];
    $ordenamiento = $input['ordenamiento'] ?? ['columna' => 'id', 'direccion' => 'DESC'];
    
    $tablas_metadata = obtenerFilas(
        "SELECT tabla_nombre, campos_json FROM formularios_metadata ORDER BY fecha_creacion DESC"
    );
    
    if (empty($tablas_metadata)) {
        responderJSON(true, 'Sin datos', ['datos' => [], 'columnas' => [], 'total' => 0]);
    }
    
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
        
        $columnOrden = $ordenamiento['columna'];
        $direccionOrden = strtoupper($ordenamiento['direccion']) === 'ASC' ? 'ASC' : 'DESC';
        
        if (in_array($columnOrden, $campos) || $columnOrden === 'id') {
            $query .= " ORDER BY `$columnOrden` $direccionOrden";
        } else {
            $query .= " ORDER BY `id` DESC";
        }
        
        $queryTotal = preg_replace('/ORDER BY .+$/i', '', $query);
        $stmtTotal = $pdo->prepare($queryTotal);
        $stmtTotal->execute($params);
        $totalRegistros = $stmtTotal->rowCount();
        
        $offset = $pagina * $limite;
        $query .= " LIMIT ? OFFSET ?";
        $params[] = $limite;
        $params[] = $offset;
        
        $stmt = ejecutarQuery($query, $params);
        $registros = $stmt->fetchAll();
        
        foreach ($registros as $registro) {
            $registro['tabla_origen'] = $tabla;
            $todos_registros[] = $registro;
        }
        
        foreach ($campos as $campo) {
            if (!in_array($campo, $columnas_totales)) {
                $columnas_totales[] = $campo;
            }
        }
    }
    
    usort($todos_registros, function($a, $b) use ($ordenamiento) {
        $columna = $ordenamiento['columna'];
        $direccion = $ordenamiento['direccion'];
        
        $valA = $a[$columna] ?? '';
        $valB = $b[$columna] ?? '';
        
        if ($direccion === 'ASC') {
            return strcmp($valA, $valB);
        } else {
            return strcmp($valB, $valA);
        }
    });
    
    $offset = $pagina * $limite;
    $registrosPaginados = array_slice($todos_registros, $offset, $limite);
    
    responderJSON(
        true,
        'Datos obtenidos correctamente',
        [
            'datos' => $registrosPaginados,
            'columnas' => $columnas_totales,
            'total' => count($todos_registros)
        ]
    );
    
} catch (Exception $e) {
    registrarError("Error en api/obtener-datos.php: " . $e->getMessage());
    responderJSON(false, 'Error al obtener datos: ' . $e->getMessage(), null, 500);
}

?>