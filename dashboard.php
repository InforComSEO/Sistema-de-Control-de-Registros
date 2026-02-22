<?php
/**
 * DASHBOARD - Panel Principal
 * Sistema de Control de Registros
 * Escuela Internacional de Psicología
 */

define('SISTEMA_REGISTROS', true);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/auth.php';

// Requiere autenticación
requerirAutenticacion();

// Datos del usuario actual
$userId     = $_SESSION['user_id'];
$userName   = $_SESSION['user_nombre'];
$userApell  = $_SESSION['user_apellidos'];
$userTipo   = $_SESSION['user_tipo'];
$userUsuario = $_SESSION['user_usuario'];
$yearActual = date('Y');
$csrfToken  = generarTokenCSRF();
$esAdmin    = ($userTipo === 'administrador');

// Iniciales para avatar
$iniciales = strtoupper(mb_substr($userName, 0, 1) . mb_substr($userApell, 0, 1));

// Datos para el modal de editar usuario
$paises = json_decode(PAISES_LISTA, true);
$prefijos = json_decode(PREFIJOS_TELEFONICOS, true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="robots" content="noindex, nofollow">
    <title>Dashboard | <?php echo SYSTEM_NAME; ?></title>
    <link rel="icon" type="image/webp" href="assets/img/favicon.webp">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css?v=<?php echo SYSTEM_VERSION; ?>">
    <link rel="stylesheet" href="assets/css/modal-usuario.css?v=<?php echo SYSTEM_VERSION; ?>">
    <link rel="stylesheet" href="assets/css/registros.css?v=<?php echo SYSTEM_VERSION; ?>">
    <link rel="stylesheet" href="assets/css/consultores.css?v=<?php echo SYSTEM_VERSION; ?>">
</head>
<body>
    <!-- Token CSRF global -->
    <input type="hidden" id="csrfTokenDash" value="<?php echo $csrfToken; ?>">
    <!-- Prefijos telefónicos -->
    <input type="hidden" id="prefijosData" value='<?php echo PREFIJOS_TELEFONICOS; ?>'>

    <div class="dashboard-layout">

        <!-- =====================================================
             SIDEBAR
             ===================================================== -->
        <aside class="sidebar">
            <!-- Logo -->
            <div class="sidebar-logo">
                <img src="assets/img/logo.webp" alt="Escuela Internacional de Psicología">
            </div>

            <!-- Menú -->
            <nav class="sidebar-menu">
                <span class="menu-section-title">Principal</span>
                <a href="#" class="menu-item active" data-page="dashboard-main" data-title="Dashboard">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>

                <?php if ($esAdmin): ?>
                <span class="menu-section-title">Gestión</span>
                <a href="#" class="menu-item" data-page="consultores" data-title="Consultores">
                    <i class="fas fa-users"></i>
                    <span>Consultores</span>
                </a>
                <?php endif; ?>

                <span class="menu-section-title">Registros</span>
                <a href="#" class="menu-item" data-page="asesores" data-title="Asesores">
                    <i class="fas fa-user-tie"></i>
                    <span>Asesores</span>
                </a>
                <a href="#" class="menu-item" data-page="delegados" data-title="Delegados">
                    <i class="fas fa-user-shield"></i>
                    <span>Delegados</span>
                </a>

                <span class="menu-section-title">Análisis</span>
                <a href="#" class="menu-item" data-page="estadisticas" data-title="Estadísticas">
                    <i class="fas fa-chart-bar"></i>
                    <span>Estadísticas</span>
                </a>

                <?php if ($esAdmin): ?>
                <span class="menu-section-title">Sistema</span>
                <a href="#" class="menu-item" data-page="logs" data-title="Logs del Sistema">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Logs</span>
                </a>
                <a href="#" class="menu-item" data-page="opciones-sistema" data-title="Opciones de Sistema">
                    <i class="fas fa-cogs"></i>
                    <span>Opciones de Sistema</span>
                </a>
                <a href="#" class="menu-item" data-page="importar-excel" data-title="Importar desde Excel">
                    <i class="fas fa-file-excel"></i>
                    <span>Importar desde Excel</span>
                </a>
                <a href="#" class="menu-item menu-separator menu-danger" data-page="resetear-bd" data-title="Resetear Base de Datos">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Resetear BD</span>
                </a>
                <?php endif; ?>
            </nav>
        </aside>

        <!-- =====================================================
             HEADER
             ===================================================== -->
        <header class="header">
            <!-- Título -->
            <div class="header-title">
                <h1><?php echo SYSTEM_NAME; ?></h1>
                <span class="separator">|</span>
                <span class="page-name" id="pageName">Dashboard</span>
            </div>

            <!-- Usuario -->
            <div class="header-user">
                <div class="header-user-info">
                    <div class="user-name"><?php echo htmlspecialchars($userName . ' ' . $userApell); ?></div>
                    <div class="user-role"><?php echo htmlspecialchars(ucfirst($userTipo)); ?></div>
                </div>
                <div class="header-user-avatar"><?php echo $iniciales; ?></div>
                <div class="header-dropdown">
                    <button class="header-dropdown-toggle" id="userDropdownToggle">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="header-dropdown-menu" id="userDropdownMenu">
                        <a href="#" id="btnEditUser">
                            <i class="fas fa-user-edit"></i> Editar Usuario
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-danger">
                            <i class="fas fa-sign-out-alt"></i> Salir del sistema
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- =====================================================
             CONTENT (Panel donde se carga la información)
             ===================================================== -->
        <main class="content" id="contentArea">
            <!-- Aquí se cargan las páginas via AJAX -->
        </main>

        <!-- =====================================================
             FOOTER
             ===================================================== -->
        <footer class="footer">
            <div class="footer-left">
                <span>&copy; Escuela Internacional de Psicología <?php echo $yearActual; ?></span>
            </div>
            <div class="footer-right">
                <span class="status-dot">🟢</span>
                <span>Sistema en línea</span>
            </div>
        </footer>

    </div>

    <!-- =====================================================
         MODAL: EDITAR USUARIO
         ===================================================== -->
    <div class="modal-overlay" id="modalEditUser">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-header-left">
                    <i class="fas fa-user-edit"></i>
                    <h3>Editar Usuario</h3>
                </div>
                <button class="modal-close-btn" id="btnCloseEditUser"><i class="fas fa-times"></i></button>
            </div>
            <form id="formEditUser" autocomplete="off" novalidate>
                <div class="modal-body">

                    <!-- Nombre -->
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Nombre <span class="required">*</span></label>
                        <input type="text" id="editNombre" class="form-control" placeholder="Ingrese nombre" autocomplete="off">
                        <div class="error-message" id="errorEditNombre"></div>
                    </div>

                    <!-- Apellidos -->
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Apellidos <span class="required">*</span></label>
                        <input type="text" id="editApellidos" class="form-control" placeholder="Ingrese apellidos" autocomplete="off">
                        <div class="error-message" id="errorEditApellidos"></div>
                    </div>

                    <!-- País -->
                    <div class="form-group">
                        <label><i class="fas fa-globe-americas"></i> País <span class="required">*</span></label>
                        <select id="editPais" class="form-control form-control-select">
                            <option value="">-- Seleccionar País --</option>
                            <?php foreach ($paises as $p): ?>
                            <option value="<?php echo htmlspecialchars($p); ?>"><?php echo htmlspecialchars($p); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="error-message" id="errorEditPais"></div>
                    </div>

                    <!-- Teléfono / WhatsApp -->
                    <div class="form-group">
                        <label><i class="fab fa-whatsapp"></i> Teléfono / WhatsApp <span class="required">*</span></label>
                        <div class="input-phone-wrapper">
                            <span class="phone-prefix" id="editPhonePrefix">+--</span>
                            <input type="text" id="editTelefono" class="form-control" placeholder="Número de celular" autocomplete="off" maxlength="15">
                        </div>
                        <div class="error-message" id="errorEditTelefono"></div>
                    </div>

                    <!-- Usuario -->
                    <div class="form-group">
                        <label><i class="fas fa-at"></i> Usuario <span class="required">*</span></label>
                        <input type="text" id="editUsuario" class="form-control" placeholder="Nombre de usuario" autocomplete="off">
                        <div class="error-message" id="errorEditUsuario"></div>
                    </div>

                    <!-- Contraseña -->
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Contraseña <span class="required">*</span></label>
                        <div class="input-password-wrapper">
                            <input type="text" id="editPassword" class="form-control" placeholder="Contraseña" autocomplete="off">
                            <button type="button" class="toggle-pass" id="toggleEditPassword" title="Mostrar/Ocultar">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                        <div class="error-message" id="errorEditPassword"></div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancelar" id="btnCancelEditUser">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-guardar" id="btnGuardarEditUser">
                        <span class="btn-spinner"></span>
                        <span class="btn-label"><i class="fas fa-save"></i> Guardar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <script src="assets/js/dashboard.js?v=<?php echo SYSTEM_VERSION; ?>"></script>
</body>
</html>