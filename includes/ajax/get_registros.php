<?php
/**
 * AJAX: Obtener registros con filtros, b¨²squeda, paginaci¨®n
 * Soporta vista_tipo: asesor (solo con asesor), delegado (solo con delegado)
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

    $offset = max(0, isset($_GET['offset']) ? (int)$_GET['offset'] : 0);
    $limit  = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    if ($limit <= 0 || $limit > 99999) $limit = 99999;

    $vistaTipo    = isset($_GET['vista_tipo']) ? trim($_GET['vista_tipo']) : '';
    $search       = isset($_GET['search']) ? trim($_GET['search']) : '';
    $formularioId = isset($_GET['formulario_id']) ? trim($_GET['formulario_id']) : '';
    $asesor       = isset($_GET['asesor']) ? trim($_GET['asesor']) : '';
    $delegado     = isset($_GET['delegado']) ? trim($_GET['delegado']) : '';
    $curso        = isset($_GET['curso']) ? trim($_GET['curso']) : '';
    $pais         = isset($_GET['pais']) ? trim($_GET['pais']) : '';
    $ciudad       = isset($_GET['ciudad']) ? trim($_GET['ciudad']) : '';
    $moneda       = isset($_GET['moneda']) ? trim($_GET['moneda']) : '';
    $metodoPago   = isset($_GET['metodo_pago']) ? trim($_GET['metodo_pago']) : '';
    $web          = isset($_GET['web']) ? trim($_GET['web']) : '';
    $categoria    = isset($_GET['categoria']) ? trim($_GET['categoria']) : '';
    $fechaDesde   = isset($_GET['fecha_desde']) ? trim($_GET['fecha_desde']) : '';
    $fechaHasta   = isset($_GET['fecha_hasta']) ? trim($_GET['fecha_hasta']) : '';
    $horaDesde    = isset($_GET['hora_desde']) ? trim($_GET['hora_desde']) : '';
    $horaHasta    = isset($_GET['hora_hasta']) ? trim($_GET['hora_hasta']) : '';
    $sortColumn   = isset($_GET['sort_column']) ? trim($_GET['sort_column']) : 'fecha_registro';
    $sortDir      = isset($_GET['sort_dir']) && strtoupper($_GET['sort_dir']) === 'ASC' ? 'ASC' : 'DESC';

    $allowedSort = ['id','nombre','apellidos','telefono','correo','asesor','delegado','curso','pais','ciudad','moneda','metodo_pago','ip','fecha','hora','categoria','formulario_id','web','fecha_registro'];
    if (!in_array($sortColumn, $allowedSort)) $sortColumn = 'fecha_registro';

    $where = [];
    $params = [];

    // Vista tipo: filtrar solo registros con asesor o delegado
    if ($vistaTipo === 'asesor') {
        $where[] = "r.asesor IS NOT NULL AND r.asesor != ''";
    } elseif ($vistaTipo === 'delegado') {
        $where[] = "r.delegado IS NOT NULL AND r.delegado != ''";
    }

    if ($search !== '') {
        $where[] = "(r.nombre LIKE :search OR r.apellidos LIKE :search2 OR r.telefono LIKE :search3 OR r.correo LIKE :search4 OR r.asesor LIKE :search5 OR r.delegado LIKE :search6 OR r.curso LIKE :search7 OR r.pais LIKE :search8 OR r.ciudad LIKE :search9 OR r.categoria LIKE :search10 OR r.formulario_id LIKE :search11)";
        $sp = '%' . $search . '%';
        for ($i = 1; $i <= 11; $i++) $params[':search' . ($i === 1 ? '' : $i)] = $sp;
    }
    if ($formularioId !== '') { $where[] = "r.formulario_id = :formulario_id"; $params[':formulario_id'] = $formularioId; }
    if ($asesor !== '')       { $where[] = "r.asesor = :asesor"; $params[':asesor'] = $asesor; }
    if ($delegado !== '')     { $where[] = "r.delegado = :delegado"; $params[':delegado'] = $delegado; }
    if ($curso !== '')        { $where[] = "r.curso = :curso"; $params[':curso'] = $curso; }
    if ($pais !== '')         { $where[] = "r.pais = :pais"; $params[':pais'] = $pais; }
    if ($ciudad !== '')       { $where[] = "r.ciudad = :ciudad"; $params[':ciudad'] = $ciudad; }
    if ($moneda !== '')       { $where[] = "r.moneda = :moneda"; $params[':moneda'] = $moneda; }
    if ($metodoPago !== '')   { $where[] = "r.metodo_pago = :metodo_pago"; $params[':metodo_pago'] = $metodoPago; }
    if ($web !== '')          { $where[] = "r.web = :web"; $params[':web'] = $web; }
    if ($categoria !== '')    { $where[] = "r.categoria = :categoria"; $params[':categoria'] = $categoria; }
    if ($fechaDesde !== '')   { $where[] = "r.fecha >= :fecha_desde"; $params[':fecha_desde'] = $fechaDesde; }
    if ($fechaHasta !== '')   { $where[] = "r.fecha <= :fecha_hasta"; $params[':fecha_hasta'] = $fechaHasta; }
    if ($horaDesde !== '')    { $where[] = "r.hora >= :hora_desde"; $params[':hora_desde'] = $horaDesde; }
    if ($horaHasta !== '')    { $where[] = "r.hora <= :hora_hasta"; $params[':hora_hasta'] = $horaHasta; }

    $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

    $stmtCount = $db->prepare("SELECT COUNT(*) as total FROM registros r $whereClause");
    $stmtCount->execute($params);
    $totalFiltered = (int)$stmtCount->fetch()['total'];

    $sql = "SELECT r.* FROM registros r $whereClause ORDER BY r.$sortColumn $sortDir LIMIT :limit OFFSET :offset";
    $stmt = $db->prepare($sql);
    foreach ($params as $key => $value) $stmt->bindValue($key, $value);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $registros = $stmt->fetchAll();

    foreach ($registros as &$reg) {
        $reg['campos_extra'] = !empty($reg['campos_extra']) ? json_decode($reg['campos_extra'], true) : [];
    }
    unset($reg);

    $stmtDyn = $db->query("SELECT nombre_campo, nombre_mostrar, tipo_dato, mostrar_lista, mostrar_filtro FROM campos_dinamicos WHERE activo = 1 ORDER BY orden ASC");
    $camposDinamicos = $stmtDyn->fetchAll();

    // Total general seg¨²n vista
    if ($vistaTipo === 'asesor') {
        $stmtTotal = $db->query("SELECT COUNT(*) as total FROM registros WHERE asesor IS NOT NULL AND asesor != ''");
    } elseif ($vistaTipo === 'delegado') {
        $stmtTotal = $db->query("SELECT COUNT(*) as total FROM registros WHERE delegado IS NOT NULL AND delegado != ''");
    } else {
        $stmtTotal = $db->query("SELECT COUNT(*) as total FROM registros");
    }
    $totalGeneral = (int)$stmtTotal->fetch()['total'];

    echo json_encode([
        'success' => true, 'registros' => $registros, 'total_filtered' => $totalFiltered,
        'total_general' => $totalGeneral, 'offset' => $offset, 'limit' => $limit,
        'has_more' => ($offset + $limit) < $totalFiltered, 'campos_dinamicos' => $camposDinamicos
    ]);
} catch (PDOException $e) {
    error_log("Error get_registros: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al obtener registros']);
}