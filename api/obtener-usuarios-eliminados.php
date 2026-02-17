<?php
/**
 * API: Obtener Usuarios Eliminados
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
    responderJSON(false, 'Solo administradores pueden ver usuarios eliminados');
}

try {
    $usuarios = obtenerFilas(
        "SELECT ue.*, u.nombre as nombre_eliminado_por, u.apellidos as apellidos_eliminado_por 
         FROM usuarios_eliminados ue
         LEFT JOIN usuarios u ON ue.eliminado_por = u.id
         ORDER BY ue.fecha_eliminacion DESC"
    );
    
    $resultado = [];
    foreach ($usuarios as $user) {
        $resultado[] = [
            'id' => $user['id'],
            'nombre' => $user['nombre'],
            'apellidos' => $user['apellidos'],
            'usuario' => $user['usuario'],
            'pais' => $user['pais'],
            'telefono' => $user['telefono'],
            'tipo_usuario' => $user['tipo_usuario'],
            'fecha_creacion_original' => $user['fecha_creacion_original'],
            'eliminado_por' => $user['eliminado_por'],
            'nombre_eliminado_por' => ($user['nombre_eliminado_por'] ?? '') . ' ' . ($user['apellidos_eliminado_por'] ?? ''),
            'fecha_eliminacion' => $user['fecha_eliminacion'],
            'ip_eliminacion' => $user['ip_eliminacion'],
            'motivo' => $user['motivo']
        ];
    }
    
    responderJSON(true, 'Usuarios eliminados obtenidos', ['usuarios' => $resultado]);
    
} catch (Exception $e) {
    registrarError("Error en api/obtener-usuarios-eliminados.php: " . $e->getMessage());
    responderJSON(false, 'Error: ' . $e->getMessage(), null, 500);
}

?>