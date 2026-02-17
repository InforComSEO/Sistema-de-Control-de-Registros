<?php
/**
 * API: Crear Usuario (Consultor)
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
    responderJSON(false, 'Solo administradores pueden crear consultores');
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $validNombre = validarNombre($input['nombre'] ?? '');
    $validApellidos = validarApellidos($input['apellidos'] ?? '');
    $validPais = validarPais($input['pais'] ?? '');
    $validTelefono = validarTelefono($input['telefono'] ?? '', $input['pais'] ?? '');
    $validUsuario = validarUsuario($input['usuario'] ?? '');
    $validContraseña = validarContraseña($input['contraseña'] ?? '');
    
    if (!$validNombre['valido']) responderJSON(false, $validNombre['mensaje']);
    if (!$validApellidos['valido']) responderJSON(false, $validApellidos['mensaje']);
    if (!$validPais['valido']) responderJSON(false, $validPais['mensaje']);
    if (!$validTelefono['valido']) responderJSON(false, $validTelefono['mensaje']);
    if (!$validUsuario['valido']) responderJSON(false, $validUsuario['mensaje']);
    if (!$validContraseña['valido']) responderJSON(false, $validContraseña['mensaje']);
    
    $query = "INSERT INTO usuarios (nombre, apellidos, pais, telefono, usuario, contraseña_hash, tipo_usuario) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = ejecutarQuery($query, [
        $validNombre['valor'],
        $validApellidos['valor'],
        $validPais['valor'],
        $validTelefono['valor'],
        $validUsuario['valor'],
        hashContraseña($validContraseña['valor']),
        'consultor'
    ]);
    
    if ($stmt) {
        $nuevoId = $pdo->lastInsertId();
        registrarLog($_SESSION['usuario_id'], 'CREAR_USUARIO', 'usuarios', $nuevoId);
        responderJSON(true, 'Consultor creado correctamente', ['usuario_id' => $nuevoId]);
    } else {
        responderJSON(false, 'Error al crear consultor');
    }
    
} catch (Exception $e) {
    registrarError("Error en api/crear-usuario.php: " . $e->getMessage());
    responderJSON(false, 'Error: ' . $e->getMessage(), null, 500);
}

?>