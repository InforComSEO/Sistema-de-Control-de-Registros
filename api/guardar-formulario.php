<?php
/**
 * API: Guardar Datos de Formulario desde WordPress
 */

require_once '../config/conexion.php';
require_once '../config/constantes.php';
require_once '../config/funciones.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $token_recibido = $_SERVER['HTTP_X_API_TOKEN'] ?? '';
    $token_esperado = API_TOKEN_SECRET;
    
    if ($token_recibido !== $token_esperado) {
        http_response_code(401);
        die(json_encode(['exito' => false, 'mensaje' => 'Token inválido']));
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['dominio_origen']) || empty($input['formulario_nombre']) || empty($input['campos'])) {
        responderJSON(false, 'Datos incompletos');
    }
    
    $dominio_origen = $input['dominio_origen'];
    $formulario_nombre = $input['formulario_nombre'];
    $formulario_id = $input['formulario_id'] ?? null;
    $campos = $input['campos'];
    
    $tabla_nombre = generarNombreTabla($formulario_nombre);
    
    $metadata = obtenerFila(
        "SELECT id, campos_json FROM formularios_metadata WHERE tabla_nombre = ?",
        [$tabla_nombre]
    );
    
    $camposActuales = [];
    if ($metadata) {
        $camposActuales = json_decode($metadata['campos_json'], true) ?: [];
    }
    
    $nuevosCampos = [];
    foreach (array_keys($campos) as $campo) {
        if ($campo !== 'fecha_hora' && !in_array($campo, $camposActuales)) {
            $nuevosCampos[] = $campo;
        }
    }
    
    if (!$metadata) {
        crearTablaFormulario($tabla_nombre, $camposActuales);
        
        $camposJSON = json_encode($camposActuales);
        ejecutarQuery(
            "INSERT INTO formularios_metadata (tabla_nombre, formulario_cf7_id, formulario_nombre, dominio_origen, campos_json) 
             VALUES (?, ?, ?, ?, ?)",
            [$tabla_nombre, $formulario_id, $formulario_nombre, $dominio_origen, $camposJSON]
        );
    }
    
    if (!empty($nuevosCampos)) {
        foreach ($nuevosCampos as $campo) {
            try {
                $pdo->exec("ALTER TABLE `$tabla_nombre` ADD COLUMN `$campo` VARCHAR(500) DEFAULT NULL");
                
                ejecutarQuery(
                    "INSERT INTO cambios_estructura (tabla_nombre, accion, campo_nombre, tipo_dato) VALUES (?, ?, ?, ?)",
                    [$tabla_nombre, 'AGREGAR_COLUMNA', $campo, 'VARCHAR(500)']
                );
            } catch (PDOException $e) {
                registrarError("Error agregando columna $campo: " . $e->getMessage());
            }
        }
        
        $todosLosCampos = array_unique(array_merge($camposActuales, $nuevosCampos));
        $camposJSON = json_encode($todosLosCampos);
        ejecutarQuery(
            "UPDATE formularios_metadata SET campos_json = ? WHERE tabla_nombre = ?",
            [$camposJSON, $tabla_nombre]
        );
        
        $cacheArchivo = RUTA_CACHE . 'filtros_sistema.json';
        if (file_exists($cacheArchivo)) {
            unlink($cacheArchivo);
        }
    }
    
    $fechaHora = procesarFechaHora($campos);
    if (!$fechaHora) {
        $fechaHora = [
            'fecha' => date('d/m/Y'),
            'hora' => date('H:i:s')
        ];
    }
    
    $datosPrepara = [
        'creado_desde' => $dominio_origen,
        'creado_desde_formulario' => $formulario_nombre,
        'fecha' => $fechaHora['fecha'],
        'hora' => $fechaHora['hora']
    ];
    
    foreach ($campos as $campo => $valor) {
        if ($campo !== 'fecha_hora' && $campo !== 'fecha' && $campo !== 'hora') {
            if ($campo === 'nombre' || $campo === 'apellidos') {
                $valor = capitalizarNombre($valor);
            }
            $datosPrepara[$campo] = $valor;
        }
    }
    
    $columnas = implode(", ", array_map(function($col) { return "`$col`"; }, array_keys($datosPrepara)));
    $placeholders = implode(", ", array_fill(0, count($datosPrepara), "?"));
    
    $query = "INSERT INTO `$tabla_nombre` ($columnas) VALUES ($placeholders)";
    $stmt = ejecutarQuery($query, array_values($datosPrepara));
    
    if ($stmt) {
        $registroId = $pdo->lastInsertId();
        
        try {
            $query_log = "INSERT INTO logs (usuario_id, tipo_accion, tabla_afectada, registro_id, ip_address) 
                         VALUES (NULL, ?, ?, ?, ?)";
            ejecutarQuery($query_log, [
                'FORMULARIO_RECIBIDO',
                $tabla_nombre,
                $registroId,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            registrarError("Error registrando log: " . $e->getMessage());
        }
        
        $cacheArchivo = RUTA_CACHE . 'filtros_sistema.json';
        if (file_exists($cacheArchivo)) {
            unlink($cacheArchivo);
        }
        
        responderJSON(true, 'Datos guardados correctamente', [
            'tabla_creada' => $tabla_nombre,
            'registro_id' => $registroId,
            'nuevos_campos' => $nuevosCampos
        ]);
    } else {
        responderJSON(false, 'Error al guardar datos');
    }
    
} catch (Exception $e) {
    registrarError("Error en api/guardar-formulario.php: " . $e->getMessage());
    responderJSON(false, 'Error: ' . $e->getMessage(), null, 500);
}

function crearTablaFormulario($tabla_nombre, $campos) {
    global $pdo;
    
    $sql = "CREATE TABLE IF NOT EXISTS `$tabla_nombre` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        creado_desde VARCHAR(100),
        creado_desde_formulario VARCHAR(255),
        fecha VARCHAR(10),
        hora VARCHAR(10),
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        
        INDEX idx_creado_desde (creado_desde),
        INDEX idx_fecha (fecha),";
    
    foreach ($campos as $campo) {
        $sql .= "`$campo` VARCHAR(500),\n        ";
    }
    
    $sql = rtrim($sql, ",\n        ");
    $sql .= "\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    try {
        $pdo->exec($sql);
        return true;
    } catch (PDOException $e) {
        registrarError("Error creando tabla $tabla_nombre: " . $e->getMessage());
        return false;
    }
}

?>