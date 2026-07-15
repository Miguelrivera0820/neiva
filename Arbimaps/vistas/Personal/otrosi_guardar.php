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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'otr_cedula'    => $_POST['otr_cedula'] ?? null,
        'otr_tipo'      => $_POST['otr_tipo'] ?? null,
        'otr_fecha'     => $_POST['otr_fecha'] ?? null,
        'otr_fecha_Final'  => $_POST['otr_fecha_Final'] ?? null,
        'otr_salario'  => $_POST['otr_salario'] ?? null,
        'otr_cargo'     => $_POST['otr_cargo'] ?? null,
        'otr_proyecto'  => $_POST['otr_proyecto'] ?? null

    ];
    $required_fields = [
                        'otr_cedula', 
                        'otr_tipo', 
                        'otr_fecha', 
                        'otr_cargo', 
                        'otr_proyecto'
                    ];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            echo json_encode([
                "success" => false,
                "message" => "Falta el campo obligatorio: $field"
            ]);
            exit;
        }
    }
    if (!isset($_SESSION['id_usuario'])) {
        echo json_encode([
            "success" => false,
            "message" => "Error: Usuario no autenticado."
        ]);
        exit;
    }
    function guardarOtrosi($archivo, $carpetaUsuario)
    {
        if ($archivo && isset($archivo['error']) && $archivo['error'] === UPLOAD_ERR_OK) {
            if (!file_exists($carpetaUsuario)) {
                mkdir($carpetaUsuario, 0777, true);
            }
            $nombreArchivo = basename($archivo['name']);
            $rutaDestino = $carpetaUsuario . $nombreArchivo;
            if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                return $rutaDestino;
            }
        }
        return null;
    }
    $uploadDir = 'Arbitrium_otrosi/';
    $carpetaUsuario = $uploadDir . $data['otr_cedula'] . '/';

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $subcarpetas = [
        'otr_otrosi' => 'Otro si/',
        'otr_cumplimiento' => 'Poliza/',
        'otr_acta' => 'Acta_Inicio/',
        'otr_actaFi' => 'Acta_Final/'
    ];

    $rutasSubcarpetas = [];
    foreach ($subcarpetas as $campo => $sub) {
        $ruta = $carpetaUsuario . $sub;
        if (!file_exists($ruta)) {
            mkdir($ruta, 0777, true);
        }
        $rutasSubcarpetas[$campo] = $ruta;
    }

    $otr_otrosi         = isset($_FILES['otr_otrosi']) ? guardarOtrosi($_FILES['otr_otrosi'], $rutasSubcarpetas['otr_otrosi']) : null;
    $otr_cumplimiento   = isset($_FILES['otr_cumplimiento']) ? guardarOtrosi($_FILES['otr_cumplimiento'], $rutasSubcarpetas['otr_cumplimiento']) : null;
    $otr_acta           = isset($_FILES['otr_acta']) ? guardarOtrosi($_FILES['otr_acta'], $rutasSubcarpetas['otr_acta']) : null;
    $otr_actaFi         = isset($_FILES['otr_actaFi']) ? guardarOtrosi($_FILES['otr_actaFi'], $rutasSubcarpetas['otr_actaFi']) : null;

    $sql = "INSERT INTO otrosi (
                            otr_cedula, 
                            otr_tipo, 
                            otr_fecha, 
                            otr_fecha_Final, 
                            otr_salario,
                            otr_cargo, 
                            otr_proyecto, 
                            otr_otrosi,
                            otr_cumplimiento, 
                            otr_acta, 
                            otr_actaFi
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ? ,?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        echo json_encode([
            "success" => false,
            "message" => "Error al preparar la consulta",
            "error" => $mysqli->error
        ]);
        exit;
    }
    $stmt->bind_param(
        "sssssssssss",
        $data['otr_cedula'],
        $data['otr_tipo'],
        $data['otr_fecha'],
        $data['otr_fecha_Final'],
        $data['otr_salario'],
        $data['otr_cargo'],
        $data['otr_proyecto'],
        $otr_otrosi,
        $otr_cumplimiento,
        $otr_acta,
        $otr_actaFi
    );
    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Datos guardados correctamente."
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Error al guardar los datos.",
            "error" => $stmt->error
        ]);
    }

    $stmt->close();
    $mysqli->close();
}
