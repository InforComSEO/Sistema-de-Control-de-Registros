<?php
require_once '../../config/database.php';
require_once '../roles.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$rol = ($id)
    ? get_rol($db, $id)
    : [
        'id'=>0,
        'nombre'=>'',
        'permisos'=>[
            'dashboard'=>1,
            'editar'=>1,
            'stats'=>1,
            'exportar'=>1,
            'admin'=>0,
            'columns'=>[]
        ]
    ];
if ($rol && is_string($rol['permisos'])) $rol['permisos'] = json_decode($rol['permisos'],true);
header('Content-Type: application/json');
echo json_encode($rol);