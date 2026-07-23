<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('POST', true);
neiva_require_permission('menu.personalGerencia', $PERMISOS, true);
neiva_require_csrf('global', true);

// VALIDAR CONEXIÓN
if (!isset($mysqli) || $mysqli === null) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Error: no se encontró la conexión a la base de datos.'
    ]);
    exit;
}

// SOLO PERMITE POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido.'
    ]);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

// LEER JSON DEL FETCH
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// VALIDAR QUE EL JSON SEA VALIDO
if ($data === null || json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'success' => false,
        'message' => 'El cuerpo de la solicitud no contiene JSON válido.'
    ]);
    exit;
}

// VALIDAR CAMPOS
if (!isset($data['id'], $data['motivo'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan datos en la solicitud (id o motivo).'
    ]);
    exit;
}

$id = (int)$data['id'];
$motivo = trim($data['motivo']);

if ($id <= 0 || $motivo === '') {
    echo json_encode([
        'success' => false,
        'message' => 'El motivo está vacío o el ID no es válido.'
    ]);
    exit;
}

// CONSULTA SQL
$sql = "UPDATE solicitudes_otrosi 
        SET sol_estado = 'RECHAZADO', sol_motivo_rechazo = ? 
        WHERE id = ?";

if ($stmt = $mysqli->prepare($sql)) {

    $stmt->bind_param("si", $motivo, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al ejecutar la consulta: ' . $stmt->error
        ]);
    }

    $stmt->close();

} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error al preparar sentencia SQL: ' . $mysqli->error
    ]);
}

$mysqli->close();
