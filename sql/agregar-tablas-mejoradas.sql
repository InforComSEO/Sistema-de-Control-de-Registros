-- ===================================================================
-- NUEVAS TABLAS PARA MEJORAS
-- ===================================================================

-- ===================================================================
-- TABLA: permisos_campos (Matriz de permisos por campo)
-- ===================================================================
CREATE TABLE IF NOT EXISTS permisos_campos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tabla_nombre VARCHAR(100) NOT NULL,
    campo_nombre VARCHAR(100) NOT NULL,
    puede_editar TINYINT(1) DEFAULT 1,
    puede_ver TINYINT(1) DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_usuario_tabla_campo (usuario_id, tabla_nombre, campo_nombre),
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_tabla_nombre (tabla_nombre),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- TABLA: usuarios_eliminados (Auditoría de eliminaciones)
-- ===================================================================
CREATE TABLE IF NOT EXISTS usuarios_eliminados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id_original INT,
    nombre VARCHAR(100),
    apellidos VARCHAR(100),
    pais VARCHAR(50),
    telefono VARCHAR(20),
    usuario VARCHAR(100),
    tipo_usuario VARCHAR(50),
    fecha_creacion_original DATETIME,
    eliminado_por INT,
    fecha_eliminacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_eliminacion VARCHAR(45),
    motivo TEXT,
    
    INDEX idx_usuario_id_original (usuario_id_original),
    INDEX idx_eliminado_por (eliminado_por),
    INDEX idx_fecha_eliminacion (fecha_eliminacion),
    FOREIGN KEY (eliminado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- TABLA: tokens_api (Historial de tokens)
-- ===================================================================
CREATE TABLE IF NOT EXISTS tokens_api (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token_valor VARCHAR(255) UNIQUE NOT NULL,
    generado_por INT,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion DATETIME,
    activo TINYINT(1) DEFAULT 1,
    ip_generacion VARCHAR(45),
    razon VARCHAR(255),
    
    INDEX idx_token_valor (token_valor),
    INDEX idx_activo (activo),
    INDEX idx_fecha_creacion (fecha_creacion),
    FOREIGN KEY (generado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;