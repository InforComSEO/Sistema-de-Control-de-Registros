<?php
/**
 * API: Obtener Lista de Consultores
 */

require_once '../config/conexion.php';
require_once '../config/constantes.php';
require_once '../config/funciones.php';

header('Content-Type: application/json; charset=utf-8');

session_start();
if (!isset($_SESSION['usuario_id'])) {
    responderJSON(false, 'No autorizado', null, 401);
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $pagina = $input['pagina'] ?? 0;
    $limite = $input['limite'] ?? 50;
    $busqueda = $input['busqueda'] ?? '';
    
    $offset = $pagina * $limite;
    
    $query = "SELECT id, nombre, apellidos, pais, telefono, usuario, tipo_usuario, fecha_creacion 
              FROM usuarios 
              WHERE tipo_usuario = 'consultor'";
    $params = [];
    
    if (!empty($busqueda)) {
        $query .= " AND (nombre LIKE ? OR apellidos LIKE ? OR usuario LIKE ? OR telefono LIKE ?)";
        $params = ["%$busqueda%", "%$busqueda%", "%$busqueda%", "%$busqueda%"];
    }
    
    $queryTotal = $query;
    $stmtTotal = $pdo->prepare($queryTotal);
    $stmtTotal->execute($params);
    $total = $stmtTotal->rowCount();
    
    $query .= " ORDER BY fecha_creacion DESC LIMIT ? OFFSET ?";
    $params[] = $limite;
    $params[] = $offset;
    
    $consultores = obtenerFilas($query, $params);
    
    responderJSON(true, 'Consultores obtenidos', [
        'consultores' => $consultores,
        'total' => $total,
        'pagina' => $pagina
    ]);
    
} catch (Exception $e) {
    registrarError("Error en api/obtener-consultores.php: " . $e->getMessage());
    responderJSON(false, 'Error: ' . $e->getMessage(), null, 500);
}

?>