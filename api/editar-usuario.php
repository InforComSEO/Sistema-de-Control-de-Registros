<?php
/**
 * API: Editar Usuario (Consultor)
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
    responderJSON(false, 'Solo administradores pueden editar usuarios');
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $usuario_id = $input['usuario_id'] ?? null;
    $nombre = $input['nombre'] ?? null;
    $apellidos = $input['apellidos'] ?? null;
    $pais = $input['pais'] ?? null;
    $telefono = $input['telefono'] ?? null;
    $usuario_nuevo = $input['usuario'] ?? null;
    $contraseña = $input['contraseña'] ?? null;
    
    if (!$usuario_id) {
        responderJSON(false, 'Usuario requerido');
    }
    
    // Obtener usuario actual
    $usuarioActual = obtenerFila("SELECT * FROM usuarios WHERE id = ?", [$usuario_id]);
    if (!$usuarioActual) {
        responderJSON(false, 'Usuario no encontrado');
    }
    
    // Validar nombre
    if ($nombre) {
        $validNombre = validarNombre($nombre);
        if (!$validNombre['valido']) {
            responderJSON(false, $validNombre['mensaje']);
        }
        $nombre = $validNombre['valor'];
    } else {
        $nombre = $usuarioActual['nombre'];
    }
    
    // Validar apellidos
    if ($apellidos) {
        $validApellidos = validarApellidos($apellidos);
        if (!$validApellidos['valido']) {
            responderJSON(false, $validApellidos['mensaje']);
        }
        $apellidos = $validApellidos['valor'];
    } else {
        $apellidos = $usuarioActual['apellidos'];
    }
    
    // Validar país
    if ($pais) {
        $validPais = validarPais($pais);
        if (!$validPais['valido']) {
            responderJSON(false, $validPais['mensaje']);
        }
        $pais = $validPais['valor'];
    } else {
        $pais = $usuarioActual['pais'];
    }
    
    // Validar teléfono
    if ($telefono) {
        $validTelefono = validarTelefono($telefono, $pais, $usuario_id);
        if (!$validTelefono['valido']) {
            responderJSON(false, $validTelefono['mensaje']);
        }
        $telefono = $validTelefono['valor'];
    } else {
        $telefono = $usuarioActual['telefono'];
    }
    
    // Validar usuario
    if ($usuario_nuevo) {
        $validUsuario = validarUsuario($usuario_nuevo, $usuario_id);
        if (!$validUsuario['valido']) {
            responderJSON(false, $validUsuario['mensaje']);
        }
        $usuario_nuevo = $validUsuario['valor'];
    } else {
        $usuario_nuevo = $usuarioActual['usuario'];
    }
    
    // Preparar query de actualización
    $campos = [];
    $parametros = [];
    
    if ($nombre !== $usuarioActual['nombre']) {
        $campos[] = "nombre = ?";
        $parametros[] = $nombre;
    }
    
    if ($apellidos !== $usuarioActual['apellidos']) {
        $campos[] = "apellidos = ?";
        $parametros[] = $apellidos;
    }
    
    if ($pais !== $usuarioActual['pais']) {
        $campos[] = "pais = ?";
        $parametros[] = $pais;
    }
    
    if ($telefono !== $usuarioActual['telefono']) {
        $campos[] = "telefono = ?";
        $parametros[] = $telefono;
    }
    
    if ($usuario_nuevo !== $usuarioActual['usuario']) {
        $campos[] = "usuario = ?";
        $parametros[] = $usuario_nuevo;
    }
    
    // Actualizar contraseña si se proporciona
    if (!empty($contraseña)) {
        $validContraseña = validarContraseña($contraseña);
        if (!$validContraseña['valido']) {
            responderJSON(false, $validContraseña['mensaje']);
        }
        
        $campos[] = "contraseña_hash = ?";
        $parametros[] = hashContraseña($contraseña);
    }
    
    // Si no hay cambios
    if (empty($campos)) {
        responderJSON(true, 'No hay cambios que guardar');
    }
    
    // Agregar ID al final de los parámetros
    $parametros[] = $usuario_id;
    
    // Ejecutar actualización
    $query = "UPDATE usuarios SET " . implode(", ", $campos) . " WHERE id = ?";
    $stmt = ejecutarQuery($query, $parametros);
    
    if ($stmt && $stmt->rowCount() > 0) {
        // Registrar en logs
        registrarLog($_SESSION['usuario_id'], 'EDITAR_USUARIO', 'usuarios', $usuario_id);
        
        responderJSON(true, 'Usuario actualizado correctamente');
    } else {
        responderJSON(false, 'Error al actualizar usuario');
    }
    
} catch (Exception $e) {
    registrarError("Error en api/editar-usuario.php: " . $e->getMessage());
    responderJSON(false, 'Error: ' . $e->getMessage(), null, 500);
}

?>