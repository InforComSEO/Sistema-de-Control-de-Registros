<?php
/**
 * INDEX - Página de Login
 * Sistema de Control de Registros
 * Escuela Internacional de Psicología
 */

define('SISTEMA_REGISTROS', true);

// Cargar configuración
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/auth.php';

// Iniciar sesión
iniciarSesionSegura();

// Si ya está autenticado, redirigir al dashboard
if (estaAutenticado()) {
    header('Location: dashboard.php');
    exit;
}

// Verificar si el login está habilitado
$loginHabilitado = true;
$loginMensaje = '';
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT valor FROM opciones_globales WHERE opcion = 'login_habilitado'");
    $stmt->execute();
    $result = $stmt->fetch();
    if ($result && $result['valor'] === '0') {
        $loginHabilitado = false;
        $stmtMsg = $db->prepare("SELECT valor FROM opciones_globales WHERE opcion = 'login_mensaje'");
        $stmtMsg->execute();
        $resultMsg = $stmtMsg->fetch();
        $loginMensaje = $resultMsg ? $resultMsg['valor'] : 'Sistema en mantenimiento.';
    }
} catch (PDOException $e) {
    error_log("Error al verificar login: " . $e->getMessage());
}

// Procesar LOGIN via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    header('Content-Type: application/json');

    // Validar CSRF
    if (!isset($_POST['csrf_token']) || !validarTokenCSRF($_POST['csrf_token'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Token de seguridad inválido. Recargue la página.',
            'new_csrf' => generarTokenCSRF()
        ]);
        exit;
    }

    // Verificar si login está habilitado
    if (!$loginHabilitado) {
        echo json_encode([
            'success' => false,
            'message' => $loginMensaje
        ]);
        exit;
    }

    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($usuario) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Debes completar todos los campos para ingresar.',
            'new_csrf' => generarTokenCSRF()
        ]);
        exit;
    }

    $resultado = intentarLogin($usuario, $password);

    if (!$resultado['success']) {
        $resultado['new_csrf'] = generarTokenCSRF();
    }

    echo json_encode($resultado);
    exit;
}

// Generar token CSRF
$csrfToken = generarTokenCSRF();
$yearActual = date('Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="robots" content="noindex, nofollow">
    <title>Iniciar Sesión | <?php echo SYSTEM_NAME; ?></title>
    <link rel="icon" type="image/webp" href="assets/img/favicon.webp">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/login.css?v=<?php echo SYSTEM_VERSION; ?>">
</head>
<body class="login-body">

    <div class="login-container">
        <div class="login-card">

            <!-- Header con Logo -->
            <div class="login-header">
                <img src="assets/img/logo.webp" alt="Escuela Internacional de Psicología">
                <h1>Sistema de Registros</h1>
            </div>

            <?php if (!$loginHabilitado): ?>
                <!-- Mensaje de sistema deshabilitado -->
                <div class="login-form-body" style="text-align: center; padding: 40px 25px;">
                    <i class="fas fa-tools" style="font-size: 40px; color: var(--azul3); margin-bottom: 15px;"></i>
                    <p style="font-size: 14px; color: #374151; line-height: 1.6;">
                        <?php echo htmlspecialchars($loginMensaje); ?>
                    </p>
                </div>
            <?php else: ?>
                <!-- Formulario de Login -->
                <div class="login-form-body">
                    <h2><i class="fas fa-lock" style="margin-right: 6px; font-size: 15px;"></i> Acceso al Sistema</h2>

                    <form id="loginForm" method="POST" autocomplete="off" novalidate>
                        <input type="hidden" id="csrfToken" name="csrf_token" value="<?php echo $csrfToken; ?>">

                        <!-- Usuario -->
                        <div class="input-group">
                            <label for="inputUsuario">Usuario</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user input-icon"></i>
                                <input
                                    type="text"
                                    id="inputUsuario"
                                    name="usuario"
                                    placeholder="Ingrese su usuario"
                                    autocomplete="off"
                                    required
                                >
                            </div>
                        </div>

                        <!-- Contraseña -->
                        <div class="input-group">
                            <label for="inputPassword">Contraseña</label>
                            <div class="input-wrapper">
                                <i class="fas fa-key input-icon"></i>
                                <input
                                    type="password"
                                    id="inputPassword"
                                    name="password"
                                    placeholder="Ingrese su contraseña"
                                    autocomplete="off"
                                    required
                                >
                                <button type="button" class="toggle-password" id="togglePassword" title="Mostrar/Ocultar">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Botón Login -->
                        <button type="submit" class="btn-login" id="btnLogin">
                            <span class="spinner"></span>
                            <span class="btn-text"><i class="fas fa-sign-in-alt"></i> Ingresar</span>
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Footer -->
            <div class="login-footer">
                <p>&copy; Escuela Internacional de Psicología <?php echo $yearActual; ?></p>
            </div>

        </div>
    </div>

    <!-- =====================================================
         MODAL DE ERROR (solo se cierra con botón)
         ===================================================== -->
    <div class="modal-overlay" id="modalError">
        <div class="modal-box">
            <div class="modal-box-header">
                <i class="fas fa-exclamation-circle"></i>
                <h3>Error de Acceso</h3>
            </div>
            <div class="modal-box-body">
                <p id="modalErrorMsg">Las credenciales ingresadas no son correctas</p>
            </div>
            <div class="modal-box-footer">
                <button class="btn-modal-close" id="btnCloseError">
                    <i class="fas fa-times" style="margin-right: 5px;"></i> Cerrar
                </button>
            </div>
        </div>
    </div>

    <!-- =====================================================
         MODAL DE BIENVENIDA
         ===================================================== -->
    <div class="modal-overlay modal-welcome" id="modalWelcome">
        <div class="modal-box">
            <div class="modal-box-header">
                <i class="fas fa-check-circle"></i>
                <h3>¡Bienvenido!</h3>
            </div>
            <div class="modal-box-body">
                <p>Hola, <strong id="modalWelcomeName"></strong>. Ingresando al sistema...</p>
            </div>
            <div class="modal-box-footer">
                <div class="spinner" style="display:inline-block; width:20px; height:20px; border: 2px solid var(--gris-borde); border-top-color: var(--celeste); border-radius: 50%; animation: spin 0.6s linear infinite;"></div>
            </div>
        </div>
    </div>

    <script src="assets/js/login.js?v=<?php echo SYSTEM_VERSION; ?>"></script>
</body>
</html>