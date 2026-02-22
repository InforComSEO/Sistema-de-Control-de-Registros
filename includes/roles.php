<?php
function usuario_es_admin() {
    return isset($_SESSION['user_tipo']) && $_SESSION['user_tipo']=='administrador';
}

function get_roles($db) {
    $stmt = $db->query("SELECT * FROM roles");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_rol($db, $id) {
    $stmt = $db->prepare("SELECT * FROM roles WHERE id=?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Procesa guardado/edición de rol
if ($_SERVER['REQUEST_METHOD']=='POST') {
    require_once '../config/database.php';
    session_start();
    if (!usuario_es_admin()) exit('Acceso denegado');

    $id = isset($_POST['rolId']) ? intval($_POST['rolId']) : 0;
    $nombre = trim($_POST['nombre']);
    $permisos = [];
    foreach(['dashboard','editar','stats','exportar','admin'] as $perm)
        $permisos[$perm] = isset($_POST[$perm]) ? 1 : 0;
    $permisos['columns'] = array_filter(array_map('trim', explode(',', $_POST['columns'])));
    if ($id) {
        $stmt = $db->prepare("UPDATE roles SET nombre=?, permisos=? WHERE id=?");
        $stmt->execute([$nombre, json_encode($permisos), $id]);
    } else {
        $stmt = $db->prepare("INSERT INTO roles (nombre, permisos) VALUES (?, ?)");
        $stmt->execute([$nombre, json_encode($permisos)]);
    }
    header('Location: ../pages/opciones-sistema.php'); exit;
}