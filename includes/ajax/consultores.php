<?php
/**
 * AJAX: CRUD de Consultores
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

if (!esAdministrador()) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

try {
    $db = Database::getInstance()->getConnection();

    switch ($action) {

        // =====================================================
        // LISTAR CONSULTORES
        // =====================================================
        case 'listar':
            $stmt = $db->query("SELECT id, nombre, apellidos, pais, telefono, usuario, tipo, estado, fecha_creacion, ultimo_acceso FROM usuarios WHERE tipo = 'consultor' ORDER BY nombre ASC, apellidos ASC");
            $consultores = $stmt->fetchAll();

            echo json_encode(['success' => true, 'consultores' => $consultores]);
            break;

        // =====================================================
        // OBTENER UN CONSULTOR
        // =====================================================
        case 'obtener':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                exit;
            }

            $stmt = $db->prepare("SELECT id, nombre, apellidos, pais, telefono, usuario FROM usuarios WHERE id = :id AND tipo = 'consultor'");
            $stmt->execute([':id' => $id]);
            $consultor = $stmt->fetch();

            if (!$consultor) {
                echo json_encode(['success' => false, 'message' => 'Consultor no encontrado']);
                exit;
            }

            echo json_encode(['success' => true, 'consultor' => $consultor]);
            break;

        // =====================================================
        // CREAR CONSULTOR
        // =====================================================
        case 'crear':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
                exit;
            }

            if (!isset($_POST['csrf_token']) || !validarTokenCSRF($_POST['csrf_token'])) {
                echo json_encode(['success' => false, 'message' => 'Token inválido']);
                exit;
            }

            $nombre    = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
            $apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : '';
            $pais      = isset($_POST['pais']) ? trim($_POST['pais']) : '';
            $telefono  = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
            $usuario   = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
            $password  = isset($_POST['password']) ? $_POST['password'] : '';

            // Validaciones
            if (empty($nombre)) { echo json_encode(['success' => false, 'message' => 'El campo Nombre no debe de estar vacío', 'field' => 'nombre']); exit; }
            if (empty($apellidos)) { echo json_encode(['success' => false, 'message' => 'El campo Apellido no debe de estar vacío', 'field' => 'apellidos']); exit; }
            if (empty($pais)) { echo json_encode(['success' => false, 'message' => 'Debes seleccionar un País', 'field' => 'pais']); exit; }
            if (empty($telefono)) { echo json_encode(['success' => false, 'message' => 'El campo Teléfono no debe de estar vacío', 'field' => 'telefono']); exit; }
            if (empty($usuario)) { echo json_encode(['success' => false, 'message' => 'El campo Usuario no debe de estar vacío', 'field' => 'usuario']); exit; }
            if (strlen($usuario) < 4) { echo json_encode(['success' => false, 'message' => 'El usuario debe tener al menos 4 caracteres', 'field' => 'usuario']); exit; }
            if (empty($password)) { echo json_encode(['success' => false, 'message' => 'El campo Contraseña no debe de estar vacío', 'field' => 'password']); exit; }
            if (strlen($password) < 6) { echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres', 'field' => 'password']); exit; }

            // Verificar teléfono único
            $stmtCheck = $db->prepare("SELECT id FROM usuarios WHERE telefono = :telefono");
            $stmtCheck->execute([':telefono' => $telefono]);
            if ($stmtCheck->fetch()) {
                echo json_encode(['success' => false, 'message' => 'El número ' . $telefono . ' ya está registrado', 'field' => 'telefono']);
                exit;
            }

            // Verificar usuario único
            $stmtCheck2 = $db->prepare("SELECT id FROM usuarios WHERE usuario = :usuario");
            $stmtCheck2->execute([':usuario' => $usuario]);
            if ($stmtCheck2->fetch()) {
                echo json_encode(['success' => false, 'message' => 'El usuario "' . $usuario . '" ya existe', 'field' => 'usuario']);
                exit;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $db->prepare("INSERT INTO usuarios (nombre, apellidos, pais, telefono, usuario, password, tipo, estado) VALUES (:nombre, :apellidos, :pais, :telefono, :usuario, :password, 'consultor', 'activo')");
            $stmt->execute([
                ':nombre' => $nombre, ':apellidos' => $apellidos, ':pais' => $pais,
                ':telefono' => $telefono, ':usuario' => $usuario, ':password' => $hashedPassword
            ]);

            registrarLog($_SESSION['user_id'], $_SESSION['user_nombre'] . ' ' . $_SESSION['user_apellidos'], $_SESSION['user_tipo'], 'Creó consultor', 'Consultor: ' . $nombre . ' ' . $apellidos . ' (' . $usuario . ')');

            echo json_encode(['success' => true, 'message' => 'Consultor creado correctamente']);
            break;

        // =====================================================
        // EDITAR CONSULTOR
        // =====================================================
        case 'editar':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
                exit;
            }

            if (!isset($_POST['csrf_token']) || !validarTokenCSRF($_POST['csrf_token'])) {
                echo json_encode(['success' => false, 'message' => 'Token inválido']);
                exit;
            }

            $id        = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $nombre    = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
            $apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : '';
            $pais      = isset($_POST['pais']) ? trim($_POST['pais']) : '';
            $telefono  = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
            $usuario   = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
            $password  = isset($_POST['password']) ? $_POST['password'] : '';

            if ($id <= 0) { echo json_encode(['success' => false, 'message' => 'ID inválido']); exit; }
            if (empty($nombre)) { echo json_encode(['success' => false, 'message' => 'El campo Nombre no debe de estar vacío', 'field' => 'nombre']); exit; }
            if (empty($apellidos)) { echo json_encode(['success' => false, 'message' => 'El campo Apellido no debe de estar vacío', 'field' => 'apellidos']); exit; }
            if (empty($pais)) { echo json_encode(['success' => false, 'message' => 'Debes seleccionar un País', 'field' => 'pais']); exit; }
            if (empty($telefono)) { echo json_encode(['success' => false, 'message' => 'El campo Teléfono no debe de estar vacío', 'field' => 'telefono']); exit; }
            if (empty($usuario)) { echo json_encode(['success' => false, 'message' => 'El campo Usuario no debe de estar vacío', 'field' => 'usuario']); exit; }
            if (strlen($usuario) < 4) { echo json_encode(['success' => false, 'message' => 'El usuario debe tener al menos 4 caracteres', 'field' => 'usuario']); exit; }
            if ($password !== '' && strlen($password) < 6) { echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres', 'field' => 'password']); exit; }

            // Verificar que es consultor
            $stmtVerify = $db->prepare("SELECT id FROM usuarios WHERE id = :id AND tipo = 'consultor'");
            $stmtVerify->execute([':id' => $id]);
            if (!$stmtVerify->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Consultor no encontrado']);
                exit;
            }

            // Verificar teléfono único
            $stmtCheck = $db->prepare("SELECT id FROM usuarios WHERE telefono = :telefono AND id != :id");
            $stmtCheck->execute([':telefono' => $telefono, ':id' => $id]);
            if ($stmtCheck->fetch()) {
                echo json_encode(['success' => false, 'message' => 'El número ' . $telefono . ' ya está registrado', 'field' => 'telefono']);
                exit;
            }

            // Verificar usuario único
            $stmtCheck2 = $db->prepare("SELECT id FROM usuarios WHERE usuario = :usuario AND id != :id");
            $stmtCheck2->execute([':usuario' => $usuario, ':id' => $id]);
            if ($stmtCheck2->fetch()) {
                echo json_encode(['success' => false, 'message' => 'El usuario "' . $usuario . '" ya existe', 'field' => 'usuario']);
                exit;
            }

            if ($password !== '') {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE usuarios SET nombre = :nombre, apellidos = :apellidos, pais = :pais, telefono = :telefono, usuario = :usuario, password = :password WHERE id = :id");
                $stmt->execute([':nombre' => $nombre, ':apellidos' => $apellidos, ':pais' => $pais, ':telefono' => $telefono, ':usuario' => $usuario, ':password' => $hashedPassword, ':id' => $id]);
            } else {
                $stmt = $db->prepare("UPDATE usuarios SET nombre = :nombre, apellidos = :apellidos, pais = :pais, telefono = :telefono, usuario = :usuario WHERE id = :id");
                $stmt->execute([':nombre' => $nombre, ':apellidos' => $apellidos, ':pais' => $pais, ':telefono' => $telefono, ':usuario' => $usuario, ':id' => $id]);
            }

            registrarLog($_SESSION['user_id'], $_SESSION['user_nombre'] . ' ' . $_SESSION['user_apellidos'], $_SESSION['user_tipo'], 'Editó consultor #' . $id, 'Consultor: ' . $nombre . ' ' . $apellidos);

            echo json_encode(['success' => true, 'message' => 'Consultor actualizado correctamente']);
            break;

        // =====================================================
        // SUSPENDER / ACTIVAR
        // =====================================================
        case 'toggle_estado':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
                exit;
            }

            if (!isset($_POST['csrf_token']) || !validarTokenCSRF($_POST['csrf_token'])) {
                echo json_encode(['success' => false, 'message' => 'Token inválido']);
                exit;
            }

            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id <= 0) { echo json_encode(['success' => false, 'message' => 'ID inválido']); exit; }

            $stmtGet = $db->prepare("SELECT id, nombre, apellidos, estado FROM usuarios WHERE id = :id AND tipo = 'consultor'");
            $stmtGet->execute([':id' => $id]);
            $consultor = $stmtGet->fetch();

            if (!$consultor) {
                echo json_encode(['success' => false, 'message' => 'Consultor no encontrado']);
                exit;
            }

            $nuevoEstado = ($consultor['estado'] === 'activo') ? 'suspendido' : 'activo';

            $stmtUpdate = $db->prepare("UPDATE usuarios SET estado = :estado WHERE id = :id");
            $stmtUpdate->execute([':estado' => $nuevoEstado, ':id' => $id]);

            $accion = ($nuevoEstado === 'suspendido') ? 'Suspendió' : 'Activó';
            registrarLog($_SESSION['user_id'], $_SESSION['user_nombre'] . ' ' . $_SESSION['user_apellidos'], $_SESSION['user_tipo'], $accion . ' consultor #' . $id, 'Consultor: ' . $consultor['nombre'] . ' ' . $consultor['apellidos']);

            echo json_encode(['success' => true, 'message' => 'Consultor ' . $nuevoEstado . ' correctamente', 'nuevo_estado' => $nuevoEstado]);
            break;

        // =====================================================
        // ELIMINAR
        // =====================================================
        case 'eliminar':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
                exit;
            }

            if (!isset($_POST['csrf_token']) || !validarTokenCSRF($_POST['csrf_token'])) {
                echo json_encode(['success' => false, 'message' => 'Token inválido']);
                exit;
            }

            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id <= 0) { echo json_encode(['success' => false, 'message' => 'ID inválido']); exit; }

            $stmtGet = $db->prepare("SELECT id, nombre, apellidos FROM usuarios WHERE id = :id AND tipo = 'consultor'");
            $stmtGet->execute([':id' => $id]);
            $consultor = $stmtGet->fetch();

            if (!$consultor) {
                echo json_encode(['success' => false, 'message' => 'Consultor no encontrado']);
                exit;
            }

            $stmtDel = $db->prepare("DELETE FROM usuarios WHERE id = :id AND tipo = 'consultor'");
            $stmtDel->execute([':id' => $id]);

            registrarLog($_SESSION['user_id'], $_SESSION['user_nombre'] . ' ' . $_SESSION['user_apellidos'], $_SESSION['user_tipo'], 'Eliminó consultor #' . $id, 'Consultor: ' . $consultor['nombre'] . ' ' . $consultor['apellidos']);

            echo json_encode(['success' => true, 'message' => 'Consultor eliminado correctamente']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }

} catch (PDOException $e) {
    error_log("Error consultores: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
}