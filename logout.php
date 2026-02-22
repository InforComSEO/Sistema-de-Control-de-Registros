<?php
/**
 * Logout - Cerrar sesión
 * Sistema de Control de Registros
 */

define('SISTEMA_REGISTROS', true);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/auth.php';

cerrarSesion();

header('Location: index.php');
exit;