<?php
/**
 * Página de Login
 */

require_once 'config/conexion.php';
require_once 'config/constantes.php';
require_once 'config/funciones.php';

if (obtenerSesion()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$mostrar_modal_error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = limpiarEntrada($_POST['usuario'] ?? '');
    $contraseña = $_POST['contraseña'] ?? '';
    
    if (empty($usuario) || empty($contraseña)) {
        $error = 'Usuario y contraseña son requeridos';
        $mostrar_modal_error = true;
    } else {
        $user = obtenerFila(
            "SELECT id, nombre, apellidos, usuario, contraseña_hash, tipo_usuario, activo FROM usuarios WHERE usuario = ? AND activo = 1",
            [$usuario]
        );
        
        if ($user && verificarContraseña($contraseña, $user['contraseña_hash'])) {
            session_start();
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario'] = $user['usuario'];
            $_SESSION['tipo_usuario'] = $user['tipo_usuario'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['apellidos'] = $user['apellidos'];
            
            registrarLog($user['id'], 'LOGIN');
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Las credenciales no son correctas';
            $mostrar_modal_error = true;
            
            if ($user) {
                registrarLog($user['id'], 'LOGIN_FALLIDO');
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo INSTITUCION_NOMBRE; ?></title>
    <link rel="icon" type="image/webp" href="<?php echo URL_BASE; ?>img/favicon.webp">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-container">
                <img src="<?php echo URL_BASE; ?>img/logo.webp" alt="Logo" class="logo">
            </div>
            
            <h1 class="titulo-sistema">
                <?php echo INSTITUCION_NOMBRE; ?>
                <span class="subtitulo">Sistema de Registros</span>
            </h1>
            
            <form id="formulario-login" method="POST" action="login.php" class="formulario-login">
                <div class="form-group">
                    <label for="usuario">Usuario</label>
                    <div class="input-wrapper">
                        <span class="icono-input">👤</span>
                        <input 
                            type="text" 
                            id="usuario" 
                            name="usuario" 
                            class="input-login" 
                            placeholder="Ingresa tu usuario"
                            required
                            autocomplete="username"
                        >
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="contraseña">Contraseña</label>
                    <div class="input-wrapper">
                        <span class="icono-input">🔒</span>
                        <input 
                            type="password" 
                            id="contraseña" 
                            name="contraseña" 
                            class="input-login" 
                            placeholder="Ingresa tu contraseña"
                            required
                            autocomplete="current-password"
                        >
                    </div>
                </div>
                
                <button type="submit" class="btn-login">Ingresar al Sistema</button>
            </form>
            
            <div class="footer-login">
                <p class="texto-footer">© <?php echo INSTITUCION_NOMBRE; ?> <?php echo ANO_ACTUAL; ?></p>
            </div>
        </div>
    </div>
    
    <?php if ($mostrar_modal_error): ?>
    <div class="modal-overlay" id="modal-error" style="display: flex;">
        <div class="modal-contenido">
            <div class="modal-header error">
                <h2>⚠ Error de Autenticación</h2>
            </div>
            <div class="modal-body">
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="cerrarModalError()">Entendido</button>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <script src="<?php echo URL_BASE; ?>js/autenticacion.js"></script>
</body>
</html>