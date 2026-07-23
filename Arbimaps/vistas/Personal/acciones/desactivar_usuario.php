<?php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../../conexion.php';
if (!isset($mysqli) || $mysqli === null) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error: no se encontró la conexión \$mysqli. Revisa conexion.php"
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_usuario'])) {
    $id = trim($_POST['id_usuario']);

    $sql = "UPDATE contratacion SET con_estado = 'INACTIVO' WHERE con_num_identidad = ?";
    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Error al preparar la consulta",
            "error" => $mysqli->error
        ]);
        exit;
    }

    $stmt->bind_param("s", $id);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Usuario desactivado correctamente"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Error al ejecutar la consulta",
            "error" => $stmt->error
        ]);
    }

    $stmt->close();
    exit;
}

// Si llega por GET o sin id_usuario
http_response_code(400);
echo json_encode([
    "success" => false,
    "message" => "Solicitud inválida. Falta id_usuario o método no permitido."
]);
