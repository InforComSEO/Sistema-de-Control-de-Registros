<?php
/**
 * Helper: Registro de Logs de Actividad
 * Uso: registrarLog('accion', 'detalle opcional');
 */

if (!defined('SISTEMA_REGISTROS')) {
    die('Acceso directo no permitido');
}

/**
 * Registrar una actividad en el log
 * @param string $accion   Tipo de acción (login, logout, editar_registro, crear_consultor, etc.)
 * @param string $detalle  Detalle descriptivo de la acción
 * @param int|null $usuarioId  ID del usuario (null = tomar de sesión)
 */
function registrarLog($accion, $detalle = '', $usuarioId = null, $usuarioNombre = null) {
    try {
        $db = Database::getInstance()->getConnection();

        // Si no se pasa usuario, intentar obtener de la sesión
        if ($usuarioId === null && isset($_SESSION['usuario_id'])) {
            $usuarioId = $_SESSION['usuario_id'];
        }
        if ($usuarioNombre === null && isset($_SESSION['usuario_nombre'])) {
            $usuarioNombre = $_SESSION['usuario_nombre'];
        }

        $ip = obtenerIPReal();
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : '';
        $fecha = date('Y-m-d');
        $hora = date('H:i:s');

        $stmt = $db->prepare("INSERT INTO logs_actividad 
            (usuario_id, usuario_nombre, accion, detalle, ip, user_agent, fecha, hora) 
            VALUES (:uid, :unombre, :accion, :detalle, :ip, :ua, :fecha, :hora)");

        $stmt->execute([
            ':uid'     => $usuarioId,
            ':unombre' => $usuarioNombre,
            ':accion'  => $accion,
            ':detalle' => mb_substr($detalle, 0, 5000),
            ':ip'      => $ip,
            ':ua'      => $userAgent,
            ':fecha'   => $fecha,
            ':hora'    => $hora
        ]);

    } catch (Exception $e) {
        error_log("Error registrarLog: " . $e->getMessage());
    }
}

/**
 * Obtener IP real del visitante
 */
function obtenerIPReal() {
    $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = $_SERVER[$header];
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}