<?php
/**
 * Dashboard Principal del Sistema - VERSIÓN COMPLETA
 * Escuela Internacional de Psicología - Sistema de Control de Registros
 */

require_once 'config/conexion.php';
require_once 'config/constantes.php';
require_once 'config/funciones.php';

// Proteger ruta
$usuario = protegerRuta();

// Obtener todas las tablas de formularios
$tablas_metadata = obtenerFilas(
    "SELECT * FROM formularios_metadata ORDER BY formulario_nombre ASC"
);

// Determinar cuál sección mostrar
$seccion = $_GET['seccion'] ?? 'dashboard';
$tabla_activa = $_GET['tabla'] ?? null;

// Obtener estructura de tabla si existe
$estructura_tabla = null;
if ($tabla_activa && $tablas_metadata) {
    foreach ($tablas_metadata as $meta) {
        if ($meta['tabla_nombre'] === $tabla_activa) {
            $estructura_tabla = json_decode($meta['campos_json'], true);
            break;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $seccion === 'dashboard' ? 'Dashboard' : ucfirst($seccion); ?> - <?php echo INSTITUCION_NOMBRE; ?></title>
    <link rel="icon" type="image/webp" href="<?php echo URL_BASE; ?>img/favicon.webp">
    
    <!-- Estilos -->
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>css/variables.css">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>css/dashboard.css">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>css/tabla.css">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>css/modales.css">
</head>
<body>
    <div class="contenedor-principal">
        <!-- HEADER -->
        <header class="header">
            <div class="header-contenido">
                <h1 class="header-titulo">
                    <?php echo INSTITUCION_NOMBRE; ?> | Sistema de Registros
                </h1>
                
                <div class="header-usuario">
                    <span class="usuario-info">
                        <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?>
                        <small class="tipo-usuario"><?php echo ucfirst($usuario['tipo_usuario']); ?></small>
                    </span>
                    
                    <div class="dropdown-menu">
                        <button class="btn-menu-usuario">⚙ Opciones</button>
                        <div class="dropdown-contenido">
                            <a href="#" onclick="abrirModalEditarUsuario()" class="dropdown-item">📝 Editar Usuario</a>
                            <div class="dropdown-divider"></div>
                            <a href="logout.php" class="dropdown-item salir">🚪 Salir del Sistema</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- CONTENEDOR FLEX -->
        <div class="contenedor-flex">
            <!-- SIDEBAR -->
            <aside class="sidebar">
                <div class="sidebar-logo">
                    <img src="<?php echo URL_BASE; ?>img/logo.webp" alt="Logo <?php echo INSTITUCION_NOMBRE; ?>">
                </div>
                
                <nav class="sidebar-menu">
                    <!-- Dashboard -->
                    <div class="menu-item <?php echo $seccion === 'dashboard' ? 'activo' : ''; ?>">
                        <a href="dashboard.php?seccion=dashboard">
                            📊 Dashboard
                        </a>
                    </div>
                    
                    <?php if ($usuario['tipo_usuario'] === 'administrador'): ?>
                    <!-- Crear Consultor -->
                    <div class="menu-item">
                        <a href="#" onclick="abrirModalCrearConsultor(); return false;">
                            👤 Crear Consultor
                        </a>
                    </div>
                    
                    <!-- Consultores -->
                    <div class="menu-item <?php echo $seccion === 'consultores' ? 'activo' : ''; ?>">
                        <a href="dashboard.php?seccion=consultores">
                            👥 Consultores
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Asesores -->
                    <div class="menu-item <?php echo $seccion === 'asesores' ? 'activo' : ''; ?>">
                        <a href="dashboard.php?seccion=asesores">
                            🎓 Asesores
                        </a>
                    </div>
                    
                    <!-- Delegados -->
                    <div class="menu-item <?php echo $seccion === 'delegados' ? 'activo' : ''; ?>">
                        <a href="dashboard.php?seccion=delegados">
                            📍 Delegados
                        </a>
                    </div>
                    
                    <!-- Estadísticas -->
                    <div class="menu-item <?php echo $seccion === 'estadisticas' ? 'activo' : ''; ?>">
                        <a href="dashboard.php?seccion=estadisticas">
                            📈 Estadísticas
                        </a>
                    </div>
                    
                    <!-- Logs -->
                    <?php if ($usuario['tipo_usuario'] === 'administrador'): ?>
                    <div class="menu-item <?php echo $seccion === 'logs' ? 'activo' : ''; ?>">
                        <a href="dashboard.php?seccion=logs">
                            📋 Logs
                        </a>
                    </div>
                    
                    <!-- Importar Excel -->
                    <div class="menu-item">
                        <a href="#" onclick="abrirModalImportarExcel(); return false;">
                            📥 Importar de Excel
                        </a>
                    </div>
                    
                    <!-- Opciones Sistema -->
                    <div class="menu-item <?php echo $seccion === 'opciones' ? 'activo' : ''; ?>">
                        <a href="dashboard.php?seccion=opciones">
                            ⚙ Opciones de Sistema
                        </a>
                    </div>
                    
                    <!-- Resetear BD -->
                    <div class="menu-item">
                        <a href="#" onclick="abrirModalResetearBD(); return false;">
                            🔄 Resetear BD
                        </a>
                    </div>
                    <?php endif; ?>
                </nav>
            </aside>
            
            <!-- PANEL PRINCIPAL -->
            <main class="panel-principal">
                <!-- DASHBOARD -->
                <?php if ($seccion === 'dashboard'): ?>
                    <?php include 'vistas/dashboard-principal.php'; ?>
                
                <!-- CONSULTORES (Solo Admin) -->
                <?php elseif ($seccion === 'consultores' && $usuario['tipo_usuario'] === 'administrador'): ?>
                    <?php include 'vistas/consultores.php'; ?>
                
                <!-- ASESORES -->
                <?php elseif ($seccion === 'asesores'): ?>
                    <?php include 'vistas/asesores.php'; ?>
                
                <!-- DELEGADOS -->
                <?php elseif ($seccion === 'delegados'): ?>
                    <?php include 'vistas/delegados.php'; ?>
                
                <!-- ESTADÍSTICAS -->
                <?php elseif ($seccion === 'estadisticas'): ?>
                    <?php include 'vistas/estadisticas.php'; ?>
                
                <!-- LOGS (Solo Admin) -->
                <?php elseif ($seccion === 'logs' && $usuario['tipo_usuario'] === 'administrador'): ?>
                    <?php include 'vistas/logs-mejorada.php'; ?>
                
                <!-- OPCIONES (Solo Admin) -->
                <?php elseif ($seccion === 'opciones' && $usuario['tipo_usuario'] === 'administrador'): ?>
                    <?php include 'vistas/opciones-sistema-mejorada.php'; ?>
                
                <?php endif; ?>
            </main>
        </div>
        
        <!-- FOOTER -->
        <footer class="footer">
            <p class="footer-texto">© <?php echo INSTITUCION_NOMBRE; ?> <?php echo ANO_ACTUAL; ?></p>
            <p class="footer-estado">🟢 Sistema en línea</p>
        </footer>
    </div>
    
    <!-- MODALES -->
    <?php include 'modales/editar-usuario.php'; ?>
    <?php include 'modales/crear-consultor.php'; ?>
    <?php include 'modales/importar-excel.php'; ?>
    <?php include 'modales/confirmar-edicion.php'; ?>
    <?php include 'modales/confirmar-eliminacion.php'; ?>
    
    <!-- Variables JavaScript -->
    <script>
        const URL_BASE = '<?php echo URL_BASE; ?>';
        const USUARIO_ID = <?php echo $usuario['id']; ?>;
        const TIPO_USUARIO = '<?php echo $usuario['tipo_usuario']; ?>';
        const SECCION_ACTUAL = '<?php echo $seccion; ?>';
    </script>
    
    <!-- Scripts -->
    <script src="<?php echo URL_BASE; ?>js/funciones-globales.js"></script>
    <script src="<?php echo URL_BASE; ?>js/tabla-dinamica.js"></script>
    <script src="<?php echo URL_BASE; ?>js/modales.js"></script>
    <script src="<?php echo URL_BASE; ?>js/validaciones.js"></script>
    <script src="<?php echo URL_BASE; ?>js/ajax-handler.js"></script>
    <script src="<?php echo URL_BASE; ?>js/tiempo-real.js"></script>
</body>
</html>