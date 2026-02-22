<?php
/**
 * API: Obtener Estadísticas
 * Devuelve datos para gráficos Chart.js
 * Filtros: fecha_desde, fecha_hasta, asesor, delegado, curso, pais, metodo_pago, web
 */
define('SISTEMA_REGISTROS', true);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/auth.php';

iniciarSesionSegura();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    // =====================================================
    // FILTROS DINÁMICOS (aplicados a todas las consultas)
    // =====================================================
    $where = [];
    $params = [];

    if (!empty($_GET['fecha_desde'])) {
        $where[] = "r.fecha >= :fecha_desde";
        $params[':fecha_desde'] = $_GET['fecha_desde'];
    }
    if (!empty($_GET['fecha_hasta'])) {
        $where[] = "r.fecha <= :fecha_hasta";
        $params[':fecha_hasta'] = $_GET['fecha_hasta'];
    }
    if (!empty($_GET['asesor'])) {
        $where[] = "r.asesor = :asesor";
        $params[':asesor'] = $_GET['asesor'];
    }
    if (!empty($_GET['delegado'])) {
        $where[] = "r.delegado = :delegado";
        $params[':delegado'] = $_GET['delegado'];
    }
    if (!empty($_GET['curso'])) {
        $where[] = "r.curso = :curso";
        $params[':curso'] = $_GET['curso'];
    }
    if (!empty($_GET['pais'])) {
        $where[] = "r.pais = :pais";
        $params[':pais'] = $_GET['pais'];
    }
    if (!empty($_GET['metodo_pago'])) {
        $where[] = "r.metodo_pago = :metodo_pago";
        $params[':metodo_pago'] = $_GET['metodo_pago'];
    }
    if (!empty($_GET['web'])) {
        $where[] = "r.web = :web";
        $params[':web'] = $_GET['web'];
    }

    $whereSQL = count($where) > 0 ? ' WHERE ' . implode(' AND ', $where) : '';

    // =====================================================
    // 1. RESUMEN GENERAL
    // =====================================================
    $sql = "SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN r.fecha = CURDATE() THEN 1 END) as hoy,
                COUNT(CASE WHEN r.fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as semana,
                COUNT(CASE WHEN r.fecha >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN 1 END) as mes,
                COUNT(DISTINCT r.asesor) as asesores,
                COUNT(DISTINCT r.delegado) as delegados,
                COUNT(DISTINCT r.curso) as cursos,
                COUNT(DISTINCT r.pais) as paises
            FROM registros r $whereSQL";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $resumen = $stmt->fetch();

    // =====================================================
    // 2. REGISTROS POR DÍA (últimos 30 días)
    // =====================================================
    $whereDia = $where;
    $whereDia[] = "r.fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    $whereDiaSQL = ' WHERE ' . implode(' AND ', $whereDia);

    $sqlDia = "SELECT DATE_FORMAT(r.fecha, '%Y-%m-%d') as dia, COUNT(*) as total
               FROM registros r $whereDiaSQL
               GROUP BY dia ORDER BY dia ASC";
    $stmt = $db->prepare($sqlDia);
    $stmt->execute($params);
    $porDia = $stmt->fetchAll();

    // =====================================================
    // 3. REGISTROS POR SEMANA (últimas 12 semanas)
    // =====================================================
    $whereSemana = $where;
    $whereSemana[] = "r.fecha >= DATE_SUB(CURDATE(), INTERVAL 12 WEEK)";
    $whereSemanaSQL = ' WHERE ' . implode(' AND ', $whereSemana);

    $sqlSemana = "SELECT YEARWEEK(r.fecha, 1) as semana_num,
                         MIN(DATE_FORMAT(r.fecha, '%Y-%m-%d')) as inicio_semana,
                         COUNT(*) as total
                  FROM registros r $whereSemanaSQL
                  GROUP BY semana_num ORDER BY semana_num ASC";
    $stmt = $db->prepare($sqlSemana);
    $stmt->execute($params);
    $porSemana = $stmt->fetchAll();

    // =====================================================
    // 4. REGISTROS POR MES (últimos 12 meses)
    // =====================================================
    $whereMes = $where;
    $whereMes[] = "r.fecha >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
    $whereMesSQL = ' WHERE ' . implode(' AND ', $whereMes);

    $sqlMes = "SELECT DATE_FORMAT(r.fecha, '%Y-%m') as mes_num,
                      DATE_FORMAT(r.fecha, '%M %Y') as mes_nombre,
                      COUNT(*) as total
               FROM registros r $whereMesSQL
               GROUP BY mes_num ORDER BY mes_num ASC";
    $stmt = $db->prepare($sqlMes);
    $stmt->execute($params);
    $porMes = $stmt->fetchAll();

    // =====================================================
    // 5. REGISTROS POR ASESOR (top 15)
    // =====================================================
    $sqlAsesor = "SELECT IFNULL(r.asesor, 'Sin Asesor') as nombre, COUNT(*) as total
                  FROM registros r $whereSQL
                  GROUP BY r.asesor ORDER BY total DESC LIMIT 15";
    $stmt = $db->prepare($sqlAsesor);
    $stmt->execute($params);
    $porAsesor = $stmt->fetchAll();

    // =====================================================
    // 6. REGISTROS POR DELEGADO (top 15)
    // =====================================================
    $sqlDelegado = "SELECT IFNULL(r.delegado, 'Sin Delegado') as nombre, COUNT(*) as total
                    FROM registros r $whereSQL
                    GROUP BY r.delegado ORDER BY total DESC LIMIT 15";
    $stmt = $db->prepare($sqlDelegado);
    $stmt->execute($params);
    $porDelegado = $stmt->fetchAll();

    // =====================================================
    // 7. REGISTROS POR CURSO (top 15)
    // =====================================================
    $sqlCurso = "SELECT IFNULL(r.curso, 'Sin Curso') as nombre, COUNT(*) as total
                 FROM registros r $whereSQL
                 GROUP BY r.curso ORDER BY total DESC LIMIT 15";
    $stmt = $db->prepare($sqlCurso);
    $stmt->execute($params);
    $porCurso = $stmt->fetchAll();

    // =====================================================
    // 8. REGISTROS POR PAÍS (top 15)
    // =====================================================
    $sqlPais = "SELECT IFNULL(r.pais, 'Sin País') as nombre, COUNT(*) as total
                FROM registros r $whereSQL
                GROUP BY r.pais ORDER BY total DESC LIMIT 15";
    $stmt = $db->prepare($sqlPais);
    $stmt->execute($params);
    $porPais = $stmt->fetchAll();

    // =====================================================
    // 9. REGISTROS POR MÉTODO DE PAGO
    // =====================================================
    $sqlMetodo = "SELECT IFNULL(r.metodo_pago, 'Sin Método') as nombre, COUNT(*) as total
                  FROM registros r $whereSQL
                  GROUP BY r.metodo_pago ORDER BY total DESC";
    $stmt = $db->prepare($sqlMetodo);
    $stmt->execute($params);
    $porMetodoPago = $stmt->fetchAll();

    // =====================================================
    // 10. REGISTROS POR HORA DEL DÍA
    // =====================================================
    $whereHora = $where;
    $whereHora[] = "r.hora IS NOT NULL";
    $whereHoraSQL = ' WHERE ' . implode(' AND ', $whereHora);

    $sqlHora = "SELECT HOUR(r.hora) as hora_num, COUNT(*) as total
                FROM registros r $whereHoraSQL
                GROUP BY hora_num ORDER BY hora_num ASC";
    $stmt = $db->prepare($sqlHora);
    $stmt->execute($params);
    $porHora = $stmt->fetchAll();

    // =====================================================
    // 11. VALORES PARA FILTROS
    // =====================================================
    $filtros = [];
    $camposFiltro = ['asesor', 'delegado', 'curso', 'pais', 'metodo_pago', 'web'];
    foreach ($camposFiltro as $campo) {
        $sqlF = "SELECT DISTINCT $campo FROM registros WHERE $campo IS NOT NULL AND $campo != '' ORDER BY $campo ASC";
        $stmtF = $db->query($sqlF);
        $filtros[$campo] = $stmtF->fetchAll(PDO::FETCH_COLUMN);
    }

    // =====================================================
    // RESPUESTA
    // =====================================================
    echo json_encode([
        'success'        => true,
        'resumen'        => $resumen,
        'por_dia'        => $porDia,
        'por_semana'     => $porSemana,
        'por_mes'        => $porMes,
        'por_asesor'     => $porAsesor,
        'por_delegado'   => $porDelegado,
        'por_curso'      => $porCurso,
        'por_pais'       => $porPais,
        'por_metodo_pago'=> $porMetodoPago,
        'por_hora'       => $porHora,
        'filtros'        => $filtros
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log("Error estadisticas: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al obtener estadísticas']);
}
