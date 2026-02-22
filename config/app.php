<?php
/**
 * Configuración General del Sistema
 * Sistema de Control de Registros
 * Escuela Internacional de Psicología
 */

// Evitar acceso directo
if (!defined('SISTEMA_REGISTROS')) {
    http_response_code(403);
    exit('Acceso denegado');
}

// URL base del sistema
define('BASE_URL', 'https://administracion.psicologiaenvivo.com');

// Nombre del sistema
define('SYSTEM_NAME', 'Escuela Internacional de Psicología | Sistema de Registros');

// Versión
define('SYSTEM_VERSION', '1.0.0');

// Zona horaria
date_default_timezone_set('America/Lima');

// Configuración de sesión
define('SESSION_LIFETIME', 28800); // 8 horas en segundos
define('SESSION_NAME', 'SCR_SESSION');

// Polling interval (milisegundos)
define('POLLING_INTERVAL', 3000); // 3 segundos

// Prefijos telefónicos por país
define('PREFIJOS_TELEFONICOS', json_encode([
    'Argentina'            => '+54',
    'Bolivia'              => '+591',
    'Brasil'               => '+55',
    'Chile'                => '+56',
    'Colombia'             => '+57',
    'Costa Rica'           => '+506',
    'Cuba'                 => '+53',
    'Ecuador'              => '+593',
    'El Salvador'          => '+503',
    'España'               => '+34',
    'Estados Unidos'       => '+1',
    'Guatemala'            => '+502',
    'Honduras'             => '+504',
    'México'               => '+52',
    'Nicaragua'            => '+505',
    'Panamá'               => '+507',
    'Paraguay'             => '+595',
    'Perú'                 => '+51',
    'Puerto Rico'          => '+1',
    'República Dominicana' => '+1',
    'Uruguay'              => '+598',
    'Venezuela'            => '+58'
]));

// Países disponibles
define('PAISES_LISTA', json_encode([
    'Argentina', 'Bolivia', 'Brasil', 'Chile', 'Colombia',
    'Costa Rica', 'Cuba', 'Ecuador', 'El Salvador', 'España',
    'Estados Unidos', 'Guatemala', 'Honduras', 'México', 'Nicaragua',
    'Panamá', 'Paraguay', 'Perú', 'Puerto Rico',
    'República Dominicana', 'Uruguay', 'Venezuela'
]));

// Palabras enlace (no se capitalizan)
define('PALABRAS_ENLACE', json_encode([
    'de', 'del', 'la', 'las', 'los', 'el', 'en', 'y', 'a',
    'e', 'o', 'u', 'con', 'sin', 'por', 'para', 'al', 'lo'
]));