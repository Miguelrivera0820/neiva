<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../../conexion.php';

if (!isset($mysqli) || $mysqli === null) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'Error: no se encontró la conexión $mysqli.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    if (!isset($_SESSION['id_usuario'])) {
        echo json_encode([
            "status"  => "error",
            "message" => "Error: Usuario no autenticado."
        ]);
        exit;
    }

    $idUsuario = $_SESSION['id_usuario'];
    function limpiar($dato, $conexion)
    {
        return mysqli_real_escape_string($conexion, trim($dato));
    }
    $con_num_identidad  = limpiar($_POST['con_num_identidad'] ?? '', $mysqli);
    $solicitud_id       = isset($_POST['solicitud_id']) ? (int)$_POST['solicitud_id'] : 0;
    if ($solicitud_id <= 0) {
        echo json_encode(["status" => "error", "message" => "ID de solicitud inválido."]);
        exit;
    }
    if (empty($con_num_identidad)) {
        echo json_encode(["status" => "error", "message" => "Número de identificación no válido."]);
        exit;
    }
    if (!isset($_FILES['sol_archivo_otrosi']) || $_FILES['sol_archivo_otrosi']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["status" => "error", "message" => "Archivo no válido o no enviado."]);
        exit;
    }
    function guardarArchivo($archivo, $rutaBase, $cedula)
    {
        if ($archivo && $archivo['error'] === UPLOAD_ERR_OK) {
            $carpetaUsuario = rtrim($rutaBase, '/\\') . '/' . $cedula . '/';
            if (!file_exists($carpetaUsuario)) {
                mkdir($carpetaUsuario, 0777, true);
            }
            $nombreOriginal = basename($archivo['name']);
            $nombreSeguro   = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $nombreOriginal);
            $nombreArchivo  = uniqid('otrosi_', true) . '_' . $nombreSeguro;
            $rutaDestino = $carpetaUsuario . $nombreArchivo;
            if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                return $nombreArchivo;
            }
        }
        return null;
    }
    $uploadDirBase = __DIR__ . '/../Arbitrium_otrosi_Nuevo/documentos_firmados';
    $nombreArchivo = guardarArchivo($_FILES['sol_archivo_otrosi'], $uploadDirBase, $con_num_identidad);
    if ($nombreArchivo) {
        $nombreArchivo = limpiar($nombreArchivo, $mysqli);
        $sql = "UPDATE solicitudes_otrosi 
                SET sol_archivo_otrosi = ?, 
                    sol_estado_cargado = 'CARGADO' 
                WHERE id = ?";

        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("si", $nombreArchivo, $solicitud_id);
            $okUpdate = $stmt->execute();
            $stmt->close();

            if ($okUpdate) {
                $mensaje = "Tienes una nueva solicitud de Otro Sí pendiente por aceptar.";
                $sqlUsuario = "SELECT id_usuario 
                                FROM usuarios_cons 
                                WHERE id_usuario = ? 
                                LIMIT 1";
                if ($stmtU = $mysqli->prepare($sqlUsuario)) {
                    $stmtU->bind_param("s", $con_num_identidad);
                    $stmtU->execute();
                    $resUsuario = $stmtU->get_result();
                    if ($resUsuario && $resUsuario->num_rows > 0) {
                        $fila      = $resUsuario->fetch_assoc();
                        $idDestino = $fila['id_usuario'];
                        $sqlNotif = "INSERT INTO notificaciones (usuario_id, mensaje, leido, solicitud_id) 
                                        VALUES (?, ?, 0, ?)";
                        if ($stmtN = $mysqli->prepare($sqlNotif)) {
                            $stmtN->bind_param("isi", $idDestino, $mensaje, $solicitud_id);
                            $stmtN->execute();
                            $stmtN->close();
                        }
                    }
                    $stmtU->close();
                }
                echo json_encode(["status" => "ok", "message" => "Actualización exitosa."]);
            } else {
                echo json_encode([
                    "status"  => "error",
                    "message" => "Error al actualizar la solicitud: " . $mysqli->error
                ]);
            }
        } else {
            echo json_encode([
                "status"  => "error",
                "message" => "Error en la preparación del UPDATE: " . $mysqli->error
            ]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "No se pudo guardar el archivo en el servidor."]);
    }
    exit;
} else {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["status" => "error", "message" => "Método no permitido"]);
    exit;
}
