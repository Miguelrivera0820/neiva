<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

header('Content-Type: application/json; charset=utf-8');

neiva_bootstrap();
neiva_require_methods('POST', true);
neiva_require_permission('menu.personalGerencia', $PERMISOS, true);
neiva_require_csrf('global', true);

if (!isset($mysqli) || $mysqli === null) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: no se encontró la conexión a la base de datos ($mysqli).'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido.'
    ]);
    exit;
}

$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if (!is_array($data) || !isset($data['con_num_identidad']) || empty($data['con_num_identidad'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos inválidos o incompletos (con_num_identidad requerido).'
    ]);
    exit;
}

$con_num_identidad = $data['con_num_identidad'];

$sql = "UPDATE solicitudes_otrosi 
        SET sol_estado_gerencia = 'ACEPTADO' 
        WHERE con_num_identidad = ?";

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("s", $con_num_identidad);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Solicitud aprobada correctamente.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar estado: ' . $mysqli->error
        ]);
    }

    $stmt->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error al preparar la consulta: ' . $mysqli->error
    ]);
}

$mysqli->close();
