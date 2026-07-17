<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../conexion.php';
if (!isset($mysqli) || $mysqli === null) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error: no se encontró la conexión \$mysqli. Revisa conexion.php"
    ]);
    exit;
}

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

// Validar datos
if (isset($data['id'], $data['motivo'])) {
    $id = (int)$data['id'];
    $motivo = trim($data['motivo']);

    if ($id > 0 && $motivo !== '') {
        $sql = "UPDATE solicitudes_otrosi SET sol_estado = 'RECHAZADO', sol_motivo_rechazo = ? WHERE id = ?";

        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("si", $motivo, $id);

            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al ejecutar: ' . $stmt->error]);
            }

            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta: ' . $mysqli->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'El motivo está vacío o el ID no es válido.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Faltan datos en la solicitud.']);
}

$mysqli->close();
