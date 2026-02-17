<?php
/**
 * API: Importar Datos desde Excel
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
    responderJSON(false, 'Solo administradores pueden importar');
}

try {
    if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        responderJSON(false, 'Error al subir archivo');
    }
    
    $archivo = $_FILES['archivo']['tmp_name'];
    $nombreOriginal = $_FILES['archivo']['name'];
    
    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
    if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
        responderJSON(false, 'Formato de archivo no válido');
    }
    
    $datos = [];
    
    if ($extension === 'csv') {
        $handle = fopen($archivo, 'r');
        $encabezados = fgetcsv($handle);
        
        while (($fila = fgetcsv($handle)) !== false) {
            $registro = [];
            foreach ($encabezados as $i => $encabezado) {
                $registro[$encabezado] = $fila[$i] ?? '';
            }
            $datos[] = $registro;
        }
        fclose($handle);
    } else {
        responderJSON(false, 'Para archivos XLSX/XLS, convertir a CSV primero', null, 400);
    }
    
    if (empty($datos)) {
        responderJSON(false, 'El archivo está vacío');
    }
    
    $primeraFila = $datos[0];
    $encabezados = array_keys($primeraFila);
    
    $tieneNombre = in_array('nombre', $encabezados);
    if (!$tieneNombre) {
        responderJSON(false, 'El Excel debe tener al menos una columna "nombre"');
    }
    
    $tabla_nombre = 'formulario_importacion_' . date('YmdHis');
    
    $sql = "CREATE TABLE IF NOT EXISTS `$tabla_nombre` (
        id INT AUTO_INCREMENT PRIMARY KEY,";
    
    foreach ($encabezados as $campo) {
        $campo_limpio = preg_replace('/[^a-z0-9_]/', '_', strtolower($campo));
        
        if (strpos($campo_limpio, 'fecha') !== false && strpos($campo_limpio, 'hora') !== false) {
            $sql .= "`fecha` VARCHAR(10),";
            $sql .= "`hora` VARCHAR(10),";
        } else {
            $sql .= "`$campo_limpio` VARCHAR(500),";
        }
    }
    
    $sql .= "`fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_fecha (fecha)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    try {
        $pdo->exec($sql);
    } catch (PDOException $e) {
        registrarError("Error creando tabla: " . $e->getMessage());
        responderJSON(false, 'Error creando tabla');
    }
    
    $registrosInsertados = 0;
    $errores = [];
    
    foreach ($datos as $fila) {
        $columnas = [];
        $valores = [];
        $placeholders = [];
        
        foreach ($fila as $campo => $valor) {
            $campo_limpio = preg_replace('/[^a-z0-9_]/', '_', strtolower($campo));
            
            if (strpos($campo, 'fecha') !== false && strpos($campo, 'hora') !== false) {
                $fechaHora = procesarFechaHora(['fecha_hora' => $valor]);
                if ($fechaHora) {
                    $columnas[] = '`fecha`';
                    $valores[] = $fechaHora['fecha'];
                    $placeholders[] = '?';
                    
                    $columnas[] = '`hora`';
                    $valores[] = $fechaHora['hora'];
                    $placeholders[] = '?';
                }
            } else {
                if ($campo_limpio === 'nombre' || $campo_limpio === 'apellidos') {
                    $valor = capitalizarNombre($valor);
                }
                
                $columnas[] = "`$campo_limpio`";
                $valores[] = $valor;
                $placeholders[] = '?';
            }
        }
        
        if (!empty($columnas)) {
            $query = "INSERT INTO `$tabla_nombre` (" . implode(', ', $columnas) . ") VALUES (" . implode(', ', $placeholders) . ")";
            
            try {
                $stmt = ejecutarQuery($query, $valores);
                if ($stmt) {
                    $registrosInsertados++;
                }
            } catch (Exception $e) {
                $errores[] = "Fila: " . $e->getMessage();
            }
        }
    }
    
    if ($registrosInsertados > 0) {
        $camposJSON = json_encode($encabezados);
        ejecutarQuery(
            "INSERT INTO formularios_metadata (tabla_nombre, formulario_nombre, campos_json) VALUES (?, ?, ?)",
            [$tabla_nombre, 'Importación ' . date('d/m/Y'), $camposJSON]
        );
        
        registrarLog($_SESSION['usuario_id'], 'IMPORTAR_EXCEL', $tabla_nombre);
        
        $cacheArchivo = RUTA_CACHE . 'filtros_sistema.json';
        if (file_exists($cacheArchivo)) {
            unlink($cacheArchivo);
        }
    }
    
    responderJSON(true, 'Importación completada', [
        'registros_importados' => $registrosInsertados,
        'tabla_nombre' => $tabla_nombre,
        'errores' => $errores
    ]);
    
} catch (Exception $e) {
    registrarError("Error en api/importar-excel.php: " . $e->getMessage());
    responderJSON(false, 'Error: ' . $e->getMessage(), null, 500);
}

?>