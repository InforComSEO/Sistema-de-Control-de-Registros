<?php
/**
 * API: Guardar Permisos por Campo
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
    responderJSON(false, 'Solo administradores pueden cambiar permisos');
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $usuario_id = $input['usuario_id'] ?? null;
    $tabla_nombre = $input['tabla_nombre'] ?? null;
    $permisos = $input['permisos'] ?? [];
    
    if (!$usuario_id || !$tabla_nombre) {
        responderJSON(false, 'Datos incompletos');
    }
    
    ejecutarQuery(
        "DELETE FROM permisos_campos WHERE usuario_id = ? AND tabla_nombre = ?",
        [$usuario_id, $tabla_nombre]
    );
    
    foreach ($permisos as $campo => $puede_editar) {
        ejecutarQuery(
            "INSERT INTO permisos_campos (usuario_id, tabla_nombre, campo_nombre, puede_editar) 
             VALUES (?, ?, ?, ?)",
            [
                $usuario_id,
                $tabla_nombre,
                $campo,
                $puede_editar ? 1 : 0
            ]
        );
    }
    
    registrarLog($_SESSION['usuario_id'], 'CAMBIAR_OPCIONES', null, null, 'permisos_campos');
    
    responderJSON(true, 'Permisos guardados correctamente');
    
} catch (Exception $e) {
    registrarError("Error en api/guardar-permisos-campos.php: " . $e->getMessage());
    responderJSON(false, 'Error: ' . $e->getMessage(), null, 500);
}

?>