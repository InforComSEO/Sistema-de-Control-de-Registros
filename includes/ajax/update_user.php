<?php
/**
 * AJAX: Actualizar datos del usuario actual
 * La contraseña es OPCIONAL: si viene vacía, no se cambia
 * Sistema de Control de Registros
 */

define('SISTEMA_REGISTROS', true);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

iniciarSesionSegura();

if (!estaAutenticado()) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

if (!isset($_POST['csrf_token']) || !validarTokenCSRF($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido']);
    exit;
}

$nombre    = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : '';
$pais      = isset($_POST['pais']) ? trim($_POST['pais']) : '';
$telefono  = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
$usuario   = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$password  = isset($_POST['password']) ? $_POST['password'] : '';

// Validaciones
if (empty($nombre)) {
    echo json_encode(['success' => false, 'message' => 'El campo Nombre no debe de estar vacío']); exit;
}
if (empty($apellidos)) {
    echo json_encode(['success' => false, 'message' => 'El campo Apellido no debe de estar vacío']); exit;
}
if (empty($pais)) {
    echo json_encode(['success' => false, 'message' => 'Debes seleccionar un País para continuar']); exit;
}
if (empty($telefono)) {
    echo json_encode(['success' => false, 'message' => 'El campo Teléfono no debe de estar vacío', 'field' => 'telefono']); exit;
}
if (empty($usuario)) {
    echo json_encode(['success' => false, 'message' => 'El campo Usuario no debe de estar vacío']); exit;
}
if (strlen($usuario) < 4) {
    echo json_encode(['success' => false, 'message' => 'El campo Centro de Estudios debe tener al menos 4 caracteres']); exit;
}

// Validar contraseña SOLO si se proporcionó una nueva
if ($password !== '' && strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'El campo Contraseña debe tener al menos 6 caracteres']); exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $userId = $_SESSION['user_id'];

    // Verificar teléfono único
    $stmtCheck = $db->prepare("SELECT id FROM usuarios WHERE telefono = :telefono AND id != :id");
    $stmtCheck->execute([':telefono' => $telefono, ':id' => $userId]);
    if ($stmtCheck->fetch()) {
        echo json_encode(['success' => false, 'message' => 'El número ' . $telefono . ' ya está registrado', 'field' => 'telefono']); exit;
    }

    // Verificar usuario único
    $stmtCheckUser = $db->prepare("SELECT id FROM usuarios WHERE usuario = :usuario AND id != :id");
    $stmtCheckUser->execute([':usuario' => $usuario, ':id' => $userId]);
    if ($stmtCheckUser->fetch()) {
        echo json_encode(['success' => false, 'message' => 'El usuario ya existe']); exit;
    }

    // Actualizar CON o SIN contraseña
    if ($password !== '') {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE usuarios SET nombre = :nombre, apellidos = :apellidos, pais = :pais, telefono = :telefono, usuario = :usuario, password = :password WHERE id = :id");
        $stmt->execute([
            ':nombre' => $nombre, ':apellidos' => $apellidos, ':pais' => $pais,
            ':telefono' => $telefono, ':usuario' => $usuario, ':password' => $hashedPassword, ':id' => $userId
        ]);
        $logDetalle = 'Actualizó datos personales y contraseña';
    } else {
        $stmt = $db->prepare("UPDATE usuarios SET nombre = :nombre, apellidos = :apellidos, pais = :pais, telefono = :telefono, usuario = :usuario WHERE id = :id");
        $stmt->execute([
            ':nombre' => $nombre, ':apellidos' => $apellidos, ':pais' => $pais,
            ':telefono' => $telefono, ':usuario' => $usuario, ':id' => $userId
        ]);
        $logDetalle = 'Actualizó datos personales (sin cambiar contraseña)';
    }

    $_SESSION['user_nombre']    = $nombre;
    $_SESSION['user_apellidos'] = $apellidos;
    $_SESSION['user_usuario']   = $usuario;

    registrarLog($userId, $nombre . ' ' . $apellidos, $_SESSION['user_tipo'], 'Editó su perfil', $logDetalle);

    echo json_encode([
        'success'         => true,
        'message'         => 'Datos actualizados correctamente',
        'nombre_completo' => $nombre . ' ' . $apellidos
    ]);

} catch (PDOException $e) {
    error_log("Error update_user: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al actualizar datos']);
}