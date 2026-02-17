<?php
/**
 * API: Obtener Logs del Sistema
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
    responderJSON(false, 'Solo administradores pueden ver logs');
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $pagina = $input['pagina'] ?? 0;
    $limite = $input['limite'] ?? 50;
    $filtro = $input['filtro'] ?? '';
    
    $offset = $pagina * $limite;
    
    $query = "SELECT l.*, u.nombre, u.apellidos 
              FROM logs l
              LEFT JOIN usuarios u ON l.usuario_id = u.id
              WHERE 1=1";
    $params = [];
    
    if (!empty($filtro)) {
        $query .= " AND (l.tipo_accion LIKE ? OR u.usuario LIKE ?)";
        $params = ["%$filtro%", "%$filtro%"];
    }
    
    $queryTotal = $query;
    $stmtTotal = $pdo->prepare($queryTotal);
    $stmtTotal->execute($params);
    $total = $stmtTotal->rowCount();
    
    $query .= " ORDER BY l.fecha_hora DESC LIMIT ? OFFSET ?";
    $params[] = $limite;
    $params[] = $offset;
    
    $logs = obtenerFilas($query, $params);
    
    responderJSON(true, 'Logs obtenidos', [
        'logs' => $logs,
        'total' => $total
    ]);
    
} catch (Exception $e) {
    registrarError("Error en api/obtener-logs.php: " . $e->getMessage());
    responderJSON(false, 'Error: ' . $e->getMessage(), null, 500);
}

?>