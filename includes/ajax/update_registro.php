<?php
/**
 * AJAX: Actualizar campo de un registro (edición inline)
 * Sistema de Control de Registros
 */

define('SISTEMA_REGISTROS', true);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

iniciarSesionSegura();

if (!estaAutenticado()) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Validar CSRF
if (!isset($_POST['csrf_token']) || !validarTokenCSRF($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Token inválido']);
    exit;
}

$registroId = isset($_POST['registro_id']) ? (int)$_POST['registro_id'] : 0;
$campo      = isset($_POST['campo']) ? trim($_POST['campo']) : '';
$valor      = isset($_POST['valor']) ? trim($_POST['valor']) : '';

if ($registroId <= 0 || $campo === '') {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

// Columnas editables
$columnasPermitidas = [
    'nombre', 'apellidos', 'telefono', 'correo', 'asesor', 'delegado',
    'curso', 'pais', 'ciudad', 'moneda', 'metodo_pago', 'ip',
    'fecha', 'hora', 'categoria', 'file_url', 'formulario_id', 'web'
];

try {
    $db = Database::getInstance()->getConnection();

    // Verificar si es campo dinámico
    $esCampoDinamico = false;
    if (!in_array($campo, $columnasPermitidas)) {
        $stmtCheck = $db->prepare("SELECT id FROM campos_dinamicos WHERE nombre_campo = :campo AND activo = 1");
        $stmtCheck->execute([':campo' => $campo]);
        if ($stmtCheck->fetch()) {
            $esCampoDinamico = true;
        } else {
            echo json_encode(['success' => false, 'message' => 'Campo no permitido']);
            exit;
        }
    }

    if ($esCampoDinamico) {
        // Actualizar campo dinámico dentro del JSON
        $stmtGet = $db->prepare("SELECT campos_extra FROM registros WHERE id = :id");
        $stmtGet->execute([':id' => $registroId]);
        $row = $stmtGet->fetch();

        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'Registro no encontrado']);
            exit;
        }

        $extra = !empty($row['campos_extra']) ? json_decode($row['campos_extra'], true) : [];
        $extra[$campo] = $valor;

        $stmtUp = $db->prepare("UPDATE registros SET campos_extra = :extra WHERE id = :id");
        $stmtUp->execute([':extra' => json_encode($extra), ':id' => $registroId]);
    } else {
        // Actualizar columna directa
        $stmt = $db->prepare("UPDATE registros SET `$campo` = :valor WHERE id = :id");
        $stmt->execute([':valor' => $valor, ':id' => $registroId]);
    }

    // Registrar log
    registrarLog(
        $_SESSION['user_id'],
        $_SESSION['user_nombre'] . ' ' . $_SESSION['user_apellidos'],
        $_SESSION['user_tipo'],
        'Editó registro',
        'Registro #' . $registroId . ' | Campo: ' . $campo . ' → ' . mb_substr($valor, 0, 200)
    );

    echo json_encode(['success' => true, 'message' => 'Actualizado correctamente']);

} catch (PDOException $e) {
    error_log("Error update_registro: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
}