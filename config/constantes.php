<?php
/**
 * Constantes y Variables Globales del Sistema
 */

// ===================================================================
// INFORMACIÓN DE LA INSTITUCIÓN
// ===================================================================
define('INSTITUCION_NOMBRE', 'Escuela Internacional de Psicología');
define('INSTITUCION_SIGLA', 'EIP');
define('ANO_ACTUAL', date('Y'));

// ===================================================================
// URL BASE DINÁMICA
// ===================================================================
$protocolo = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$dominio = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('URL_BASE', $protocolo . '://' . $dominio . '/');

// ===================================================================
// SEGURIDAD - TOKEN API DINÁMICO
// ===================================================================
function obtenerTokenAPI() {
    global $pdo;
    
    try {
        $query = "SELECT valor_json FROM opciones_sistema 
                  WHERE usuario_id IS NULL 
                  AND seccion = 'sistema' 
                  AND opcion_nombre = 'api_token' 
                  LIMIT 1";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $resultado = $stmt->fetch();
        
        if ($resultado) {
            return json_decode($resultado['valor_json'], true);
        }
    } catch (Exception $e) {
        // Si no existe BD aún, usar token por defecto
    }
    
    // Token por defecto (se reemplaza en primera ejecución)
    return 'token-secreto-psicologia-2026-' . md5('psicologia-envivo');
}

define('API_TOKEN_SECRET', obtenerTokenAPI());
define('SESSION_TIMEOUT', 3600); // 60 minutos
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_OPTIONS', ['cost' => 12]);

// ===================================================================
// COLORES DEL SISTEMA
// ===================================================================
define('COLOR_AZUL_1', '#050018');
define('COLOR_AZUL_2', '#020B31');
define('COLOR_AZUL_3', '#07325A');
define('COLOR_ROJO', '#FF3600');
define('COLOR_CELESTE', '#00BCFF');
define('COLOR_AMARILLO', '#FFC700');
define('COLOR_BLANCO', '#FFFFFF');
define('COLOR_NEGRO', '#000000');
define('COLOR_GRIS_CLARO', '#F5F5F5');
define('COLOR_GRIS_MEDIO', '#CCCCCC');
define('COLOR_EXITO', '#10B981');
define('COLOR_ERROR', '#EF4444');
define('COLOR_ADVERTENCIA', '#F59E0B');
define('COLOR_INFO', '#3B82F6');

// ===================================================================
// PAGINACIÓN
// ===================================================================
define('REGISTROS_POR_PAGINA', 50);
define('REGISTROS_TABLA_INICIAL', 50);

// ===================================================================
// CACHÉ
// ===================================================================
define('CACHE_FILTROS_DURACION', 300);
define('RUTA_CACHE', __DIR__ . '/../cache/');

// ===================================================================
// RUTAS
// ===================================================================
define('RUTA_RAIZ', dirname(__DIR__) . '/');
define('RUTA_IMG', RUTA_RAIZ . 'img/');
define('RUTA_CSS', RUTA_RAIZ . 'css/');
define('RUTA_JS', RUTA_RAIZ . 'js/');
define('RUTA_MODALES', RUTA_RAIZ . 'modales/');
define('RUTA_VISTAS', RUTA_RAIZ . 'vistas/');
define('RUTA_API', RUTA_RAIZ . 'api/');
define('RUTA_CONFIG', RUTA_RAIZ . 'config/');
define('RUTA_LOGS', RUTA_RAIZ . 'logs/');
define('RUTA_BACKUPS', RUTA_RAIZ . 'backups/');
define('RUTA_UPLOADS', RUTA_RAIZ . 'uploads/');

// ===================================================================
// CREAR DIRECTORIOS SI NO EXISTEN
// ===================================================================
$directorios = [
    RUTA_CACHE,
    RUTA_LOGS,
    RUTA_BACKUPS,
    RUTA_UPLOADS
];

foreach ($directorios as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// ===================================================================
// TIPOS DE USUARIOS
// ===================================================================
define('TIPO_ADMINISTRADOR', 'administrador');
define('TIPO_CONSULTOR', 'consultor');

$TIPOS_USUARIOS = [
    'administrador' => 'Administrador',
    'consultor' => 'Consultor'
];

// ===================================================================
// PAÍSES LATINOAMERICANOS Y ESPAÑA
// ===================================================================
$PAISES = [
    "Argentina",
    "Bolivia",
    "Brasil",
    "Chile",
    "Colombia",
    "Costa Rica",
    "Cuba",
    "Ecuador",
    "El Salvador",
    "España",
    "Estados Unidos",
    "Guatemala",
    "Honduras",
    "México",
    "Nicaragua",
    "Panamá",
    "Paraguay",
    "Perú",
    "Puerto Rico",
    "República Dominicana",
    "Uruguay",
    "Venezuela"
];

// ===================================================================
// ACCIONES DE LOG
// ===================================================================
$TIPOS_ACCIONES_LOG = [
    'LOGIN' => 'Inicio de sesión',
    'LOGOUT' => 'Cierre de sesión',
    'LOGIN_FALLIDO' => 'Intento fallido de login',
    'CREAR_USUARIO' => 'Crear usuario',
    'EDITAR_USUARIO' => 'Editar usuario',
    'ELIMINAR_USUARIO' => 'Eliminar usuario',
    'EDITAR_REGISTRO' => 'Editar registro en tabla',
    'CREAR_REGISTRO' => 'Crear registro',
    'ELIMINAR_REGISTRO' => 'Eliminar registro',
    'INTENTO_EDICION_SIN_PERMISO' => 'Intento de edición sin permiso',
    'IMPORTAR_EXCEL' => 'Importar datos de Excel',
    'EXPORTAR_EXCEL' => 'Exportar a Excel',
    'EXPORTAR_LOGS' => 'Exportar logs',
    'LIMPIAR_LOGS' => 'Limpiar logs',
    'CREAR_BACKUP' => 'Crear backup',
    'RESETEAR_BD' => 'Resetear base de datos',
    'CAMBIAR_OPCIONES' => 'Cambiar opciones de sistema',
    'REGENERAR_TOKEN' => 'Regenerar token API',
    'FORMULARIO_RECIBIDO' => 'Formulario recibido desde WordPress'
];

// ===================================================================
// PALABRAS DE ENLACE
// ===================================================================
$PALABRAS_ENLACE = [
    'de', 'del', 'la', 'los', 'las', 'el', 'y', 'o', 'u', 'e', 'a', 'ante', 'bajo', 'con', 'contra', 'desde', 'durante', 'en', 'entre', 'hacia', 'para', 'por', 'según', 'sin', 'sobre', 'tras', 'vía'
];

?>