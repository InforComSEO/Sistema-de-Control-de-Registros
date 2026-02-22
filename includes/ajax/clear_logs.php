<?php
/**
 * AJAX: Limpiar logs de actividad
 */
define('SISTEMA_REGISTROS', true);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');
iniciarSesionSegura();
if (!estaAutenticado()) { echo json_encode(['success' => false, 'message' => 'No autenticado']); exit; }

if (!esAdministrador()) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

if (!isset($_POST['csrf_token']) || !validarTokenCSRF($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Token inválido']);
    exit;
}

$tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : 'antiguos';
$dias = isset($_POST['dias']) ? (int)$_POST['dias'] : 30;

$diasPermitidos = [7, 30, 90, 180, 365];
if (!in_array($dias, $diasPermitidos)) $dias = 30;

$labels = [7 => '1 semana', 30 => '1 mes', 90 => '3 meses', 180 => '6 meses', 365 => '1 año'];

try {
    $db = Database::getInstance()->getConnection();

    if ($tipo === 'todos') {
        $stmt = $db->prepare("DELETE FROM logs");
        $stmt->execute();
        $eliminados = $stmt->rowCount();

        registrarLog(
            $_SESSION['user_id'],
            $_SESSION['user_nombre'] . ' ' . $_SESSION['user_apellidos'],
            $_SESSION['user_tipo'],
            'Limpió todos los logs',
            "Se eliminaron $eliminados registros de log"
        );

        echo json_encode(['success' => true, 'message' => "Se eliminaron $eliminados registros de log", 'eliminados' => $eliminados]);

    } else {
        // fecha es DATETIME, comparar con DATE()
        $fechaLimite = date('Y-m-d', strtotime("-$dias days"));
        $stmt = $db->prepare("DELETE FROM logs WHERE DATE(fecha) < :fecha");
        $stmt->execute([':fecha' => $fechaLimite]);
        $eliminados = $stmt->rowCount();

        $label = isset($labels[$dias]) ? $labels[$dias] : $dias . ' días';

        registrarLog(
            $_SESSION['user_id'],
            $_SESSION['user_nombre'] . ' ' . $_SESSION['user_apellidos'],
            $_SESSION['user_tipo'],
            'Limpió logs antiguos',
            "Eliminados logs de más de $label (anteriores a $fechaLimite) — $eliminados registros"
        );

        echo json_encode(['success' => true, 'message' => "Se eliminaron $eliminados logs de más de $label", 'eliminados' => $eliminados]);
    }

} catch (PDOException $e) {
    error_log("Error clear_logs: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al limpiar logs']);
}