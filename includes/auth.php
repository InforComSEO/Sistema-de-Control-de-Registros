<?php
/**
 * Funciones de Autenticación
 * Sistema de Control de Registros
 */

// Evitar acceso directo
if (!defined('SISTEMA_REGISTROS')) {
    http_response_code(403);
    exit('Acceso denegado');
}

/**
 * Iniciar sesión segura
 */
function iniciarSesionSegura() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
        session_name(SESSION_NAME);
        session_start();
    }
}

/**
 * Intentar login
 */
function intentarLogin($usuario, $password) {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("SELECT id, nombre, apellidos, usuario, password, tipo, estado FROM usuarios WHERE usuario = :usuario LIMIT 1");
    $stmt->execute([':usuario' => $usuario]);
    $user = $stmt->fetch();

    if (!$user) {
        registrarLog(null, $usuario, 'desconocido', 'Login fallido', 'Usuario no encontrado');
        return ['success' => false, 'message' => 'Las credenciales ingresadas no son correctas'];
    }

    if ($user['estado'] === 'suspendido') {
        registrarLog($user['id'], $user['nombre'] . ' ' . $user['apellidos'], $user['tipo'], 'Login bloqueado', 'Cuenta suspendida');
        return ['success' => false, 'message' => 'Su cuenta se encuentra suspendida. Contacte al administrador.'];
    }

    if (!password_verify($password, $user['password'])) {
        registrarLog($user['id'], $user['nombre'] . ' ' . $user['apellidos'], $user['tipo'], 'Login fallido', 'Contraseña incorrecta');
        return ['success' => false, 'message' => 'Las credenciales ingresadas no son correctas'];
    }

    // Actualizar último acceso
    $stmtUpdate = $db->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id");
    $stmtUpdate->execute([':id' => $user['id']]);

    // Crear sesión
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_nombre'] = $user['nombre'];
    $_SESSION['user_apellidos'] = $user['apellidos'];
    $_SESSION['user_usuario'] = $user['usuario'];
    $_SESSION['user_tipo'] = $user['tipo'];
    $_SESSION['user_login_time'] = time();
    $_SESSION['user_ip'] = obtenerIP();

    // Regenerar ID de sesión para prevenir session fixation
    session_regenerate_id(true);

    // Registrar log
    registrarLog($user['id'], $user['nombre'] . ' ' . $user['apellidos'], $user['tipo'], 'Inicio de sesión', 'Acceso exitoso al sistema');

    return ['success' => true, 'tipo' => $user['tipo'], 'nombre' => $user['nombre']];
}

/**
 * Verificar si el usuario está autenticado
 */
function estaAutenticado() {
    iniciarSesionSegura();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_login_time'])) {
        return false;
    }

    // Verificar expiración de sesión
    if ((time() - $_SESSION['user_login_time']) > SESSION_LIFETIME) {
        cerrarSesion();
        return false;
    }

    return true;
}

/**
 * Verificar si es administrador
 */
function esAdministrador() {
    return isset($_SESSION['user_tipo']) && $_SESSION['user_tipo'] === 'administrador';
}

/**
 * Requerir autenticación (redirige si no está logueado)
 */
function requerirAutenticacion() {
    if (!estaAutenticado()) {
        header('Location: ' . BASE_URL . '/index.php?session=expired');
        exit;
    }
}

/**
 * Requerir rol administrador
 */
function requerirAdministrador() {
    requerirAutenticacion();
    if (!esAdministrador()) {
        header('Location: ' . BASE_URL . '/dashboard.php');
        exit;
    }
}

/**
 * Obtener datos del usuario actual
 */
function obtenerUsuarioActual() {
    if (!estaAutenticado()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'nombre' => $_SESSION['user_nombre'],
        'apellidos' => $_SESSION['user_apellidos'],
        'usuario' => $_SESSION['user_usuario'],
        'tipo' => $_SESSION['user_tipo'],
        'rol' => $_SESSION['user_tipo']
    ];
}

/**
 * Cerrar sesión
 */
function cerrarSesion() {
    iniciarSesionSegura();

    if (isset($_SESSION['user_id'])) {
        registrarLog(
            $_SESSION['user_id'],
            $_SESSION['user_nombre'] . ' ' . $_SESSION['user_apellidos'],
            $_SESSION['user_tipo'],
            'Cierre de sesión',
            'Salió del sistema'
        );
    }

    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
}

/**
 * Obtener IP del usuario
 */
function obtenerIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP) ?: $_SERVER['REMOTE_ADDR'];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return filter_var(trim($ips[0]), FILTER_VALIDATE_IP) ?: $_SERVER['REMOTE_ADDR'];
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Generar token CSRF
 */
function generarTokenCSRF() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validar token CSRF
 */
function validarTokenCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Registrar log en la BD
 * Tabla logs: fecha DATETIME DEFAULT current_timestamp(), hora TIME
 * No insertamos fecha (se llena sola), solo insertamos hora
 */
function registrarLog($usuario_id, $usuario_nombre, $tipo_usuario, $accion, $detalle = null) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO logs 
            (usuario_id, usuario_nombre, tipo_usuario, accion, detalle, ip, hora) 
            VALUES (:uid, :unombre, :tipo, :accion, :detalle, :ip, :hora)");
        $stmt->execute([
            ':uid'     => $usuario_id,
            ':unombre' => $usuario_nombre,
            ':tipo'    => $tipo_usuario,
            ':accion'  => $accion,
            ':detalle' => $detalle,
            ':ip'      => obtenerIP(),
            ':hora'    => date('H:i:s')
        ]);
    } catch (PDOException $e) {
        error_log("Error al registrar log: " . $e->getMessage());
    }
}