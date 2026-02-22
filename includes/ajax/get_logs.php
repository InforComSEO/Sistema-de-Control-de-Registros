<?php
/**
 * AJAX: Obtener logs de actividad con filtros
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

try {
    $db = Database::getInstance()->getConnection();

    $search     = isset($_GET['search']) ? trim($_GET['search']) : '';
    $accion     = isset($_GET['accion']) ? trim($_GET['accion']) : '';
    $usuario    = isset($_GET['usuario']) ? trim($_GET['usuario']) : '';
    $fechaDesde = isset($_GET['fecha_desde']) ? trim($_GET['fecha_desde']) : '';
    $fechaHasta = isset($_GET['fecha_hasta']) ? trim($_GET['fecha_hasta']) : '';
    $horaDesde  = isset($_GET['hora_desde']) ? trim($_GET['hora_desde']) : '';
    $horaHasta  = isset($_GET['hora_hasta']) ? trim($_GET['hora_hasta']) : '';
    $offset     = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $limit      = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

    if ($limit <= 0 || $limit > 99999) $limit = 50;
    if ($offset < 0) $offset = 0;

    $where = [];
    $params = [];

    if ($search !== '') {
        $where[] = "(accion LIKE :s1 OR detalle LIKE :s2 OR usuario_nombre LIKE :s3 OR ip LIKE :s4)";
        $sp = '%' . $search . '%';
        $params[':s1'] = $sp; $params[':s2'] = $sp; $params[':s3'] = $sp; $params[':s4'] = $sp;
    }
    if ($accion !== '') { $where[] = "accion = :accion"; $params[':accion'] = $accion; }
    if ($usuario !== '') { $where[] = "usuario_nombre = :usuario"; $params[':usuario'] = $usuario; }
    if ($fechaDesde !== '') { $where[] = "DATE(fecha) >= :fd"; $params[':fd'] = $fechaDesde; }
    if ($fechaHasta !== '') { $where[] = "DATE(fecha) <= :fh"; $params[':fh'] = $fechaHasta; }
    if ($horaDesde !== '') { $where[] = "hora >= :hd"; $params[':hd'] = $horaDesde; }
    if ($horaHasta !== '') { $where[] = "hora <= :hh"; $params[':hh'] = $horaHasta; }

    $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

    // Total
    $stmtTotal = $db->prepare("SELECT COUNT(*) as c FROM logs $whereClause");
    $stmtTotal->execute($params);
    $total = (int)$stmtTotal->fetch()['c'];

    // Registros — usar DATE(fecha) y TIME(fecha) para mostrar separados
    $sql = "SELECT id, usuario_id, usuario_nombre, tipo_usuario, accion, detalle, ip, 
                   DATE(fecha) as fecha_dia, hora, fecha as fecha_completa
            FROM logs $whereClause 
            ORDER BY fecha DESC, hora DESC 
            LIMIT :lim OFFSET :off";

    $stmt = $db->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $logsRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatear logs para el frontend
    $logs = [];
    foreach ($logsRaw as $log) {
        $logs[] = [
            'id' => $log['id'],
            'usuario_id' => $log['usuario_id'],
            'usuario_nombre' => $log['usuario_nombre'],
            'tipo_usuario' => $log['tipo_usuario'],
            'accion' => $log['accion'],
            'detalle' => $log['detalle'],
            'ip' => $log['ip'],
            'fecha' => $log['fecha_dia'],
            'hora' => $log['hora'] ?: date('H:i:s', strtotime($log['fecha_completa']))
        ];
    }

    // Filtros: acciones únicas y usuarios únicos
    $stmtAcciones = $db->query("SELECT DISTINCT accion FROM logs WHERE accion IS NOT NULL AND accion != '' ORDER BY accion ASC");
    $acciones = $stmtAcciones->fetchAll(PDO::FETCH_COLUMN);

    $stmtUsuarios = $db->query("SELECT DISTINCT usuario_nombre FROM logs WHERE usuario_nombre IS NOT NULL AND usuario_nombre != '' ORDER BY usuario_nombre ASC");
    $usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'success' => true,
        'logs' => $logs,
        'total' => $total,
        'has_more' => ($offset + $limit) < $total,
        'filtros' => [
            'acciones' => $acciones,
            'usuarios' => $usuarios
        ]
    ]);

} catch (PDOException $e) {
    error_log("Error get_logs: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al obtener logs']);
}