<?php
/**
 * Página de Inicio - Redirección
 */

require_once 'config/conexion.php';
require_once 'config/constantes.php';
require_once 'config/funciones.php';

// Obtener sesión
$sesion = obtenerSesion();

if ($sesion) {
    // Usuario logueado
    header('Location: dashboard.php');
} else {
    // Usuario no logueado
    header('Location: login.php');
}
exit;
?>