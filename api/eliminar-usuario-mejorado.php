<?php
/**
 * API: Eliminar Usuario con Auditoría
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
    responderJSON(false, 'Solo administradores pueden eliminar usuarios');
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $usuario_id = $input['usuario_id'] ?? null;
    $motivo = $input['motivo'] ?? 'Sin especificar';
    
    if (!$usuario_id) {
        responderJSON(false, 'Usuario requerido');
    }
    
    $usuarioAEliminar = obtenerFila(
        "SELECT id, nombre, apellidos, pais, telefono, usuario, tipo_usuario, fecha_creacion 
         FROM usuarios WHERE id = ?",
        [$usuario_id]
    );
    
    if (!$usuarioAEliminar) {
        responderJSON(false, 'Usuario no encontrado');
    }
    
    if ($usuarioAEliminar['tipo_usuario'] === 'administrador') {
        responderJSON(false, 'No puedes eliminar un administrador');
    }
    
    // Guardar en tabla de auditoría
    ejecutarQuery(
        "INSERT INTO usuarios_eliminados 
         (usuario_id_original, nombre, apellidos, pais, telefono, usuario, tipo_usuario, 
          fecha_creacion_original, eliminado_por, ip_eliminacion, motivo) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [
            $usuarioAEliminar['id'],
            $usuarioAEliminar['nombre'],
            $usuarioAEliminar['apellidos'],
            $usuarioAEliminar['pais'],
            $usuarioAEliminar['telefono'],
            $usuarioAEliminar['usuario'],
            $usuarioAEliminar['tipo_usuario'],
            $usuarioAEliminar['fecha_creacion'],
            $_SESSION['usuario_id'],
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $motivo
        ]
    );
    
    // Eliminar permisos y opciones
    ejecutarQuery(
        "DELETE FROM permisos_campos WHERE usuario_id = ?",
        [$usuario_id]
    );
    
    ejecutarQuery(
        "DELETE FROM opciones_sistema WHERE usuario_id = ?",
        [$usuario_id]
    );
    
    // Eliminar usuario
    $stmt = ejecutarQuery(
        "DELETE FROM usuarios WHERE id = ? AND tipo_usuario = 'consultor'",
        [$usuario_id]
    );
    
    if ($stmt && $stmt->rowCount() > 0) {
        registrarLog(
            $_SESSION['usuario_id'],
            'ELIMINAR_USUARIO',
            'usuarios',
            $usuario_id
        );
        
        responderJSON(true, 'Consultor eliminado correctamente y auditado');
    } else {
        responderJSON(false, 'Error al eliminar usuario');
    }
    
} catch (Exception $e) {
    registrarError("Error en api/eliminar-usuario-mejorado.php: " . $e->getMessage());
    responderJSON(false, 'Error: ' . $e->getMessage(), null, 500);
}

?>