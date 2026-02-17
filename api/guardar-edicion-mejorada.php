<?php
/**
 * API: Guardar Edición con Validación de Permisos
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
    $usuario_id = $_SESSION['usuario_id'];
    $input = json_decode(file_get_contents('php://input'), true);
    
    $registro_id = $input['registro_id'] ?? null;
    $columna = $input['columna'] ?? null;
    $valor_anterior = $input['valor_anterior'] ?? null;
    $valor_nuevo = $input['valor_nuevo'] ?? null;
    
    if (!$registro_id || !$columna) {
        responderJSON(false, 'Datos incompletos');
    }
    
    // Encontrar tabla
    $tablas = obtenerFilas("SHOW TABLES LIKE 'formulario_%'");
    $tabla_encontrada = null;
    
    foreach ($tablas as $tabla_row) {
        $tabla = array_values($tabla_row)[0];
        $registro = obtenerFila("SELECT * FROM `$tabla` WHERE id = ?", [$registro_id]);
        if ($registro) {
            $tabla_encontrada = $tabla;
            break;
        }
    }
    
    if (!$tabla_encontrada) {
        responderJSON(false, 'Registro no encontrado');
    }
    
    // Validar permiso
    $permiso = obtenerFila(
        "SELECT puede_editar FROM permisos_campos 
         WHERE usuario_id = ? AND tabla_nombre = ? AND campo_nombre = ?",
        [$usuario_id, $tabla_encontrada, $columna]
    );
    
    if ($permiso && !$permiso['puede_editar']) {
        registrarLog(
            $usuario_id,
            'INTENTO_EDICION_SIN_PERMISO',
            $tabla_encontrada,
            $registro_id,
            $columna
        );
        
        responderJSON(false, "No tienes permiso para editar el campo: $columna");
    }
    
    // Validar datos
    if ($columna === 'nombre' || $columna === 'apellidos') {
        $valor_nuevo = capitalizarNombre($valor_nuevo);
    }
    
    // Actualizar
    $query = "UPDATE `$tabla_encontrada` SET `$columna` = ? WHERE id = ?";
    $stmt = ejecutarQuery($query, [$valor_nuevo, $registro_id]);
    
    if ($stmt) {
        registrarLog(
            $usuario_id,
            'EDITAR_REGISTRO',
            $tabla_encontrada,
            $registro_id,
            $columna,
            $valor_anterior,
            $valor_nuevo
        );
        
        registrarHistorialCambio(
            $tabla_encontrada,
            $registro_id,
            $usuario_id,
            $columna,
            $valor_anterior,
            $valor_nuevo
        );
        
        $cacheArchivo = RUTA_CACHE . 'filtros_sistema.json';
        if (file_exists($cacheArchivo)) {
            unlink($cacheArchivo);
        }
        
        responderJSON(true, 'Registro actualizado correctamente');
    } else {
        responderJSON(false, 'Error al actualizar registro');
    }
    
} catch (Exception $e) {
    registrarError("Error en api/guardar-edicion-mejorada.php: " . $e->getMessage());
    responderJSON(false, 'Error: ' . $e->getMessage(), null, 500);
}

?>