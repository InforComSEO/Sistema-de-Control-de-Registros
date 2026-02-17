<?php
/**
 * Funciones Globales del Sistema
 */

require_once 'conexion.php';
require_once 'constantes.php';

// ===================================================================
// FUNCIÓN: Registrar en logs
// ===================================================================
function registrarLog($usuario_id, $tipo_accion, $tabla_afectada = null, $registro_id = null, $campo_modificado = null, $valor_anterior = null, $valor_nuevo = null) {
    global $pdo;
    
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $query = "INSERT INTO logs (usuario_id, tipo_accion, tabla_afectada, registro_id, campo_modificado, valor_anterior, valor_nuevo, ip_address, user_agent) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            $usuario_id,
            $tipo_accion,
            $tabla_afectada,
            $registro_id,
            $campo_modificado,
            $valor_anterior ? json_encode($valor_anterior) : null,
            $valor_nuevo ? json_encode($valor_nuevo) : null,
            $ip,
            $user_agent
        ]);
        
        return true;
    } catch (Exception $e) {
        registrarError("Error registrando log: " . $e->getMessage());
        return false;
    }
}

// ===================================================================
// FUNCIÓN: Registrar cambio en historial
// ===================================================================
function registrarHistorialCambio($tabla_nombre, $registro_id, $usuario_id, $campo_nombre, $valor_anterior, $valor_nuevo) {
    global $pdo;
    
    try {
        $query = "INSERT INTO historial_cambios (tabla_nombre, registro_id, usuario_id, campo_nombre, valor_anterior, valor_nuevo) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            $tabla_nombre,
            $registro_id,
            $usuario_id,
            $campo_nombre,
            $valor_anterior,
            $valor_nuevo
        ]);
        
        return true;
    } catch (Exception $e) {
        registrarError("Error registrando historial: " . $e->getMessage());
        return false;
    }
}

// ===================================================================
// FUNCIÓN: Capitalizar nombres
// ===================================================================
function capitalizarNombre($nombre) {
    global $PALABRAS_ENLACE;
    
    $nombre = trim($nombre);
    $nombre = mb_strtolower($nombre, 'UTF-8');
    
    $palabras = explode(' ', $nombre);
    
    $resultado = [];
    foreach ($palabras as $i => $palabra) {
        if ($i === 0) {
            $resultado[] = mb_strtoupper(mb_substr($palabra, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($palabra, 1, null, 'UTF-8');
        } else {
            if (in_array(mb_strtolower($palabra, 'UTF-8'), $PALABRAS_ENLACE)) {
                $resultado[] = $palabra;
            } else {
                $resultado[] = mb_strtoupper(mb_substr($palabra, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($palabra, 1, null, 'UTF-8');
            }
        }
    }
    
    return implode(' ', $resultado);
}

// ===================================================================
// FUNCIÓN: Validar nombre
// ===================================================================
function validarNombre($nombre) {
    $nombre = trim($nombre);
    
    if (empty($nombre)) {
        return ['valido' => false, 'mensaje' => 'El campo Nombre no debe de estar vacío'];
    }
    
    if (strlen($nombre) < 2) {
        return ['valido' => false, 'mensaje' => 'El campo Nombre debe tener al menos 2 caracteres'];
    }
    
    if (strlen($nombre) > 100) {
        return ['valido' => false, 'mensaje' => 'El campo Nombre no debe exceder 100 caracteres'];
    }
    
    if (preg_match('/\s{2,}/', $nombre)) {
        return ['valido' => false, 'mensaje' => 'El campo Nombre no debe tener espacios múltiples'];
    }
    
    return ['valido' => true, 'valor' => capitalizarNombre($nombre)];
}

// ===================================================================
// FUNCIÓN: Validar apellidos
// ===================================================================
function validarApellidos($apellidos) {
    $apellidos = trim($apellidos);
    
    if (empty($apellidos)) {
        return ['valido' => false, 'mensaje' => 'El campo Apellido no debe de estar vacío'];
    }
    
    if (strlen($apellidos) < 2) {
        return ['valido' => false, 'mensaje' => 'El campo Apellido debe tener al menos 2 caracteres'];
    }
    
    if (strlen($apellidos) > 100) {
        return ['valido' => false, 'mensaje' => 'El campo Apellido no debe exceder 100 caracteres'];
    }
    
    if (preg_match('/\s{2,}/', $apellidos)) {
        return ['valido' => false, 'mensaje' => 'El campo Apellido no debe tener espacios múltiples'];
    }
    
    return ['valido' => true, 'valor' => capitalizarNombre($apellidos)];
}

// ===================================================================
// FUNCIÓN: Validar país
// ===================================================================
function validarPais($pais) {
    global $PAISES;
    
    $pais = trim($pais);
    
    if (empty($pais)) {
        return ['valido' => false, 'mensaje' => 'Debes seleccionar un País para continuar'];
    }
    
    if (!in_array($pais, $PAISES)) {
        return ['valido' => false, 'mensaje' => 'Debes seleccionar un País válido'];
    }
    
    return ['valido' => true, 'valor' => $pais];
}

// ===================================================================
// FUNCIÓN: Obtener prefijo de país
// ===================================================================
function obtenerPrefijoPais($pais) {
    $resultado = obtenerFila(
        "SELECT prefijo, digitos_min, digitos_max FROM paises_prefijos WHERE pais = ?",
        [$pais]
    );
    
    return $resultado ?: null;
}

// ===================================================================
// FUNCIÓN: Validar teléfono
// ===================================================================
function validarTelefono($telefono, $pais, $usuario_id_actual = null) {
    $telefono = trim(str_replace([' ', '-', '+', '(', ')'], '', $telefono));
    
    if (empty($telefono)) {
        return ['valido' => false, 'mensaje' => 'El campo Teléfono no debe de estar vacío'];
    }
    
    if (!ctype_digit($telefono)) {
        return ['valido' => false, 'mensaje' => 'El Teléfono solo debe contener números'];
    }
    
    $prefijo_info = obtenerPrefijoPais($pais);
    if (!$prefijo_info) {
        return ['valido' => false, 'mensaje' => 'País no válido'];
    }
    
    if (strlen($telefono) < $prefijo_info['digitos_min'] || strlen($telefono) > $prefijo_info['digitos_max']) {
        return ['valido' => false, 'mensaje' => "El Teléfono debe tener entre {$prefijo_info['digitos_min']} y {$prefijo_info['digitos_max']} dígitos"];
    }
    
    $query = "SELECT id FROM usuarios WHERE telefono = ?";
    $params = [$prefijo_info['prefijo'] . $telefono];
    
    if ($usuario_id_actual) {
        $query .= " AND id != ?";
        $params[] = $usuario_id_actual;
    }
    
    $existe = obtenerFila($query, $params);
    if ($existe) {
        return ['valido' => false, 'mensaje' => "El número {$prefijo_info['prefijo']} " . implode(' ', str_split($telefono, 3)) . " ya está registrado"];
    }
    
    return ['valido' => true, 'valor' => $prefijo_info['prefijo'] . $telefono];
}

// ===================================================================
// FUNCIÓN: Validar usuario
// ===================================================================
function validarUsuario($usuario, $usuario_id_actual = null) {
    $usuario = trim($usuario);
    
    if (empty($usuario)) {
        return ['valido' => false, 'mensaje' => 'El campo Usuario no debe de estar vacío'];
    }
    
    if (strlen($usuario) < 4) {
        return ['valido' => false, 'mensaje' => 'El campo Usuario debe tener al menos 4 caracteres'];
    }
    
    if (strlen($usuario) > 100) {
        return ['valido' => false, 'mensaje' => 'El campo Usuario no debe exceder 100 caracteres'];
    }
    
    $query = "SELECT id FROM usuarios WHERE usuario = ?";
    $params = [$usuario];
    
    if ($usuario_id_actual) {
        $query .= " AND id != ?";
        $params[] = $usuario_id_actual;
    }
    
    $existe = obtenerFila($query, $params);
    if ($existe) {
        return ['valido' => false, 'mensaje' => 'El Usuario ya está registrado'];
    }
    
    return ['valido' => true, 'valor' => $usuario];
}

// ===================================================================
// FUNCIÓN: Validar contraseña
// ===================================================================
function validarContraseña($contraseña) {
    if (empty($contraseña)) {
        return ['valido' => false, 'mensaje' => 'El campo Contraseña no debe de estar vacío'];
    }
    
    if (strlen($contraseña) < 6) {
        return ['valido' => false, 'mensaje' => 'El campo Contraseña debe tener al menos 6 caracteres'];
    }
    
    return ['valido' => true, 'valor' => $contraseña];
}

// ===================================================================
// FUNCIÓN: Hash de contraseña
// ===================================================================
function hashContraseña($contraseña) {
    return password_hash($contraseña, PASSWORD_HASH_ALGO, PASSWORD_HASH_OPTIONS);
}

// ===================================================================
// FUNCIÓN: Verificar contraseña
// ===================================================================
function verificarContraseña($contraseña, $hash) {
    return password_verify($contraseña, $hash);
}

// ===================================================================
// FUNCIÓN: Obtener datos de sesión
// ===================================================================
function obtenerSesion() {
    session_start();
    
    if (isset($_SESSION['usuario_id']) && isset($_SESSION['usuario'])) {
        $usuario = obtenerFila(
            "SELECT id, nombre, apellidos, usuario, tipo_usuario FROM usuarios WHERE id = ? AND activo = 1",
            [$_SESSION['usuario_id']]
        );
        
        if ($usuario) {
            return $usuario;
        }
    }
    
    return null;
}

// ===================================================================
// FUNCIÓN: Proteger ruta
// ===================================================================
function protegerRuta() {
    $sesion = obtenerSesion();
    
    if (!$sesion) {
        header('Location: ' . URL_BASE . 'login.php');
        exit;
    }
    
    return $sesion;
}

// ===================================================================
// FUNCIÓN: Responder JSON
// ===================================================================
function responderJSON($exito = true, $mensaje = '', $datos = null, $codigo = 200) {
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    
    echo json_encode([
        'exito' => $exito,
        'mensaje' => $mensaje,
        'datos' => $datos
    ]);
    
    exit;
}

// ===================================================================
// FUNCIÓN: Procesar fecha y hora
// ===================================================================
function procesarFechaHora($campos) {
    if (isset($campos['fecha_hora']) && !empty($campos['fecha_hora'])) {
        $fecha_hora = $campos['fecha_hora'];
        
        if (preg_match('/^(\d{1,2}\/\d{1,2}\/\d{4})\s(\d{2}:\d{2}:\d{2})$/', $fecha_hora, $matches)) {
            return [
                'fecha' => $matches[1],
                'hora' => $matches[2]
            ];
        }
        elseif (preg_match('/^(\d{4}-\d{2}-\d{2})\s(\d{2}:\d{2}:\d{2})$/', $fecha_hora, $matches)) {
            $fecha = explode('-', $matches[1]);
            return [
                'fecha' => $fecha[2] . '/' . $fecha[1] . '/' . $fecha[0],
                'hora' => $matches[2]
            ];
        }
    }
    
    if (isset($campos['fecha']) && isset($campos['hora'])) {
        return [
            'fecha' => $campos['fecha'],
            'hora' => $campos['hora']
        ];
    }
    
    return null;
}

// ===================================================================
// FUNCIÓN: Limpiar entrada
// ===================================================================
function limpiarEntrada($entrada) {
    return htmlspecialchars(trim($entrada), ENT_QUOTES, 'UTF-8');
}

// ===================================================================
// FUNCIÓN: Generar nombre de tabla
// ===================================================================
function generarNombreTabla($nombre_formulario) {
    $nombre = mb_strtolower($nombre_formulario, 'UTF-8');
    $nombre = preg_replace('/[^a-z0-9]/', '_', $nombre);
    $nombre = preg_replace('/_+/', '_', $nombre);
    $nombre = trim($nombre, '_');
    return 'formulario_' . substr($nombre, 0, 50);
}

?>