-- ===================================================================
-- ESCUELA INTERNACIONAL DE PSICOLOGÍA - SISTEMA DE CONTROL DE REGISTROS
-- Base de Datos: zqgikadc_administracionphp
-- ===================================================================

-- ===================================================================
-- TABLA: usuarios (Administradores y Consultores)
-- ===================================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    pais VARCHAR(50) NOT NULL,
    telefono VARCHAR(20) UNIQUE NOT NULL,
    usuario VARCHAR(100) UNIQUE NOT NULL,
    contraseña_hash VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('administrador', 'consultor') NOT NULL DEFAULT 'consultor',
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_usuario (usuario),
    INDEX idx_telefono (telefono),
    INDEX idx_tipo_usuario (tipo_usuario),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar usuario admin por defecto (contraseña: 123456)
INSERT INTO usuarios (nombre, apellidos, pais, telefono, usuario, contraseña_hash, tipo_usuario) 
VALUES ('Admin', 'Sistema', 'Colombia', '573001234567', 'admin', '$2y$12$R9h7cIPz0gi.URNNX3kh2OPST9/PgBkqquzi.Ee/IeIGWIPbzuB7m', 'administrador')
ON DUPLICATE KEY UPDATE usuario=VALUES(usuario);

-- ===================================================================
-- TABLA: paises_prefijos
-- ===================================================================
CREATE TABLE IF NOT EXISTS paises_prefijos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pais VARCHAR(50) NOT NULL UNIQUE,
    prefijo VARCHAR(10) NOT NULL,
    digitos_min INT NOT NULL,
    digitos_max INT NOT NULL,
    mascara VARCHAR(50),
    
    INDEX idx_pais (pais)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO paises_prefijos (pais, prefijo, digitos_min, digitos_max, mascara) VALUES
('Argentina', '+54', 9, 10, '+54 9 XXXXXXXXXX'),
('Bolivia', '+591', 8, 9, '+591 XXXXXXXXX'),
('Brasil', '+55', 10, 11, '+55 XX XXXXX-XXXX'),
('Chile', '+56', 9, 10, '+56 9 XXXX XXXX'),
('Colombia', '+57', 10, 10, '+57 X XXX XXXX'),
('Costa Rica', '+506', 8, 8, '+506 XXXX XXXX'),
('Cuba', '+53', 8, 9, '+53 XXXXXXXX'),
('Ecuador', '+593', 9, 10, '+593 9 XXXXX XXXXX'),
('El Salvador', '+503', 8, 8, '+503 XXXX XXXX'),
('España', '+34', 9, 9, '+34 XXX XX XX XX'),
('Estados Unidos', '+1', 10, 10, '+1 (XXX) XXX-XXXX'),
('Guatemala', '+502', 8, 8, '+502 XXXX XXXX'),
('Honduras', '+504', 8, 8, '+504 XXXX XXXX'),
('México', '+52', 10, 10, '+52 XX XXXX XXXX'),
('Nicaragua', '+505', 8, 8, '+505 XXXX XXXX'),
('Panamá', '+507', 8, 8, '+507 XXXX XXXX'),
('Paraguay', '+595', 9, 10, '+595 9 XXXXXX'),
('Perú', '+51', 9, 9, '+51 9 XXXX XXXX'),
('Puerto Rico', '+1', 10, 10, '+1 (XXX) XXX-XXXX'),
('República Dominicana', '+1', 10, 10, '+1 (XXX) XXX-XXXX'),
('Uruguay', '+598', 8, 9, '+598 9 XXXX XXXX'),
('Venezuela', '+58', 10, 10, '+58 XXX XXX XXXX');

-- ===================================================================
-- TABLA: formularios_metadata (Estructura dinámica de tablas)
-- ===================================================================
CREATE TABLE IF NOT EXISTS formularios_metadata (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tabla_nombre VARCHAR(100) UNIQUE NOT NULL,
    formulario_cf7_id VARCHAR(100),
    formulario_nombre VARCHAR(255) NOT NULL,
    dominio_origen VARCHAR(100),
    campos_json JSON,
    fecha_ultima_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_tabla_nombre (tabla_nombre),
    INDEX idx_formulario_nombre (formulario_nombre),
    INDEX idx_dominio_origen (dominio_origen)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- TABLA: cambios_estructura (Historial de cambios en BD)
-- ===================================================================
CREATE TABLE IF NOT EXISTS cambios_estructura (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tabla_nombre VARCHAR(100) NOT NULL,
    accion VARCHAR(50) NOT NULL,
    campo_nombre VARCHAR(100),
    tipo_dato VARCHAR(50),
    usuario_id INT,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    descripcion TEXT,
    
    INDEX idx_tabla_nombre (tabla_nombre),
    INDEX idx_fecha_hora (fecha_hora),
    INDEX idx_usuario_id (usuario_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- TABLA: logs (Auditoría completa del sistema)
-- ===================================================================
CREATE TABLE IF NOT EXISTS logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    tipo_accion VARCHAR(100) NOT NULL,
    tabla_afectada VARCHAR(100),
    registro_id INT,
    campo_modificado VARCHAR(100),
    valor_anterior LONGTEXT,
    valor_nuevo LONGTEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_tipo_accion (tipo_accion),
    INDEX idx_tabla_afectada (tabla_afectada),
    INDEX idx_fecha_hora (fecha_hora),
    INDEX idx_registro_id (registro_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- TABLA: historial_cambios (Cambios específicos en registros)
-- ===================================================================
CREATE TABLE IF NOT EXISTS historial_cambios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tabla_nombre VARCHAR(100) NOT NULL,
    registro_id INT NOT NULL,
    usuario_id INT,
    campo_nombre VARCHAR(100) NOT NULL,
    valor_anterior LONGTEXT,
    valor_nuevo LONGTEXT,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_tabla_nombre (tabla_nombre),
    INDEX idx_registro_id (registro_id),
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_fecha_hora (fecha_hora),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- TABLA: opciones_sistema (Configuración por usuario)
-- ===================================================================
CREATE TABLE IF NOT EXISTS opciones_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    seccion VARCHAR(100) NOT NULL,
    opcion_nombre VARCHAR(100) NOT NULL,
    valor_json JSON,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_usuario_seccion_opcion (usuario_id, seccion, opcion_nombre),
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_seccion (seccion),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- TABLA: cache_filtros (Caché de filtros)
-- ===================================================================
CREATE TABLE IF NOT EXISTS cache_filtros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tabla_nombre VARCHAR(100) NOT NULL,
    tipo_filtro VARCHAR(100) NOT NULL,
    valores_json JSON,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion DATETIME,
    
    UNIQUE KEY uk_tabla_tipo (tabla_nombre, tipo_filtro),
    INDEX idx_tabla_nombre (tabla_nombre),
    INDEX idx_fecha_expiracion (fecha_expiracion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- TABLA: sessions (Gestión de sesiones)
-- ===================================================================
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(255) UNIQUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion DATETIME,
    activa TINYINT(1) DEFAULT 1,
    
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_fecha_expiracion (fecha_expiracion),
    INDEX idx_activa (activa),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- TABLA: backups (Registro de backups automáticos)
-- ===================================================================
CREATE TABLE IF NOT EXISTS backups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_archivo VARCHAR(255) NOT NULL,
    tamano_mb DECIMAL(10, 2),
    ruta_almacenamiento VARCHAR(255),
    usuario_id INT,
    tipo ENUM('automatico', 'manual') DEFAULT 'manual',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion DATETIME,
    
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_fecha_creacion (fecha_creacion),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- TABLA DINÁMICA EJEMPLO: formulario_solicitud_inscripcion
-- ===================================================================
CREATE TABLE IF NOT EXISTS formulario_solicitud_inscripcion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    apellidos VARCHAR(100),
    pais VARCHAR(50),
    telefono VARCHAR(20),
    asesor VARCHAR(100),
    delegado VARCHAR(100),
    adjunto_url TEXT,
    email VARCHAR(100),
    fecha VARCHAR(10),
    hora VARCHAR(10),
    creado_desde VARCHAR(50),
    creado_desde_formulario VARCHAR(100),
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_asesor (asesor),
    INDEX idx_delegado (delegado),
    INDEX idx_fecha (fecha),
    INDEX idx_nombre (nombre),
    INDEX idx_creado_desde (creado_desde),
    FULLTEXT INDEX ft_nombre_apellidos (nombre, apellidos)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;