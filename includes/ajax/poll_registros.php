<?php
/**
 * AJAX: Polling de nuevos registros
 * Soporta vista_tipo: asesor, delegado
 */
define('SISTEMA_REGISTROS', true);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');
iniciarSesionSegura();
if (!estaAutenticado()) { echo json_encode(['success' => false, 'message' => 'No autenticado']); exit; }

try {
    $db = Database::getInstance()->getConnection();
    $lastId = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
    $vistaTipo = isset($_GET['vista_tipo']) ? trim($_GET['vista_tipo']) : '';

    $where = "WHERE id > :last_id";
    $params = [':last_id' => $lastId];

    if ($vistaTipo === 'asesor') {
        $where .= " AND asesor IS NOT NULL AND asesor != ''";
    } elseif ($vistaTipo === 'delegado') {
        $where .= " AND delegado IS NOT NULL AND delegado != ''";
    }

    $stmt = $db->prepare("SELECT * FROM registros $where ORDER BY id ASC LIMIT 50");
    $stmt->execute($params);
    $nuevos = $stmt->fetchAll();

    foreach ($nuevos as &$reg) {
        $reg['campos_extra'] = !empty($reg['campos_extra']) ? json_decode($reg['campos_extra'], true) : [];
    }
    unset($reg);

    echo json_encode(['success' => true, 'nuevos' => $nuevos, 'count' => count($nuevos)]);
} catch (PDOException $e) {
    error_log("Error poll_registros: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error']);
}