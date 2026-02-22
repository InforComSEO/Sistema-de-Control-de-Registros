<?php
/**
 * AJAX: Obtener datos del usuario actual
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

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id, nombre, apellidos, pais, telefono, usuario FROM usuarios WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user) {
        echo json_encode([
            'success' => true,
            'user' => [
                'nombre'    => $user['nombre'],
                'apellidos' => $user['apellidos'],
                'pais'      => $user['pais'],
                'telefono'  => $user['telefono'],
                'usuario'   => $user['usuario']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    }
} catch (PDOException $e) {
    error_log("Error get_user_data: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al obtener datos']);
}