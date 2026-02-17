<?php
/**
 * Página de Logout
 */

require_once 'config/conexion.php';
require_once 'config/constantes.php';
require_once 'config/funciones.php';

session_start();

if (isset($_SESSION['usuario_id'])) {
    registrarLog($_SESSION['usuario_id'], 'LOGOUT');
}

session_destroy();

header('Location: login.php');
exit;
?>