<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../../../../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['id_usuario'])) {
        echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
        exit;
    }

    $usuario_id = $_SESSION['id_usuario'];

    $idSolicitud = $_POST['id_solicitud'] ?? null;
    if (!$idSolicitud) {
        echo json_encode(['success' => false, 'message' => 'ID de solicitud no proporcionado.']);
        exit;
    }

    $sqlYear = "SELECT sb_year FROM solicitud_baqueanos WHERE id = ? LIMIT 1";
    $stmtYear = $mysqli->prepare($sqlYear);
    if (!$stmtYear) {
        echo json_encode(['success' => false, 'message' => 'Error preparando consulta sb_year: ' . $mysqli->error]);
        exit;
    }

    $stmtYear->bind_param("i", $idSolicitud);
    $stmtYear->execute();
    $resYear = $stmtYear->get_result();
    $rowYear = $resYear->fetch_assoc();
    $stmtYear->close();

    $sb_year = $rowYear['sb_year'] ?? null;

    $año_actual = (int)$sb_year;
    if ($año_actual < 2000 || $año_actual > 2100) {
        echo json_encode(['success' => false, 'message' => 'El campo sb_year no es válido o está vacío para esta solicitud.']);
        exit;
    }

    $mes_actual = date('m');
    $base_upload_dir = '../DOCUMENTOS_BAQUENOS/soporte_pago/';
    $usuario_base_dir = $base_upload_dir . $año_actual . '/' . $mes_actual . '/RAD_' . $idSolicitud . '/';

    if (!file_exists($usuario_base_dir) && !mkdir($usuario_base_dir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => "Error al crear carpeta: $usuario_base_dir"]);
        exit;
    }

    if (isset($_FILES['archivo_soporte']) && $_FILES['archivo_soporte']['error'] === 0) {
        $file_name = basename($_FILES['archivo_soporte']['name']);
        $destino = $usuario_base_dir . $file_name;

        if (move_uploaded_file($_FILES['archivo_soporte']['tmp_name'], $destino)) {

            $ruta_relativa = $año_actual . '/' . $mes_actual . '/RAD_' . $idSolicitud . '/' . $file_name;

            $sql = "UPDATE solicitud_baqueanos 
            SET sb_soporte_pago = ? 
            WHERE id = ?";
            $stmt = $mysqli->prepare($sql);

            if (!$stmt) {
                echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta: ' . $mysqli->error]);
                exit;
            }

            $stmt->bind_param("si", $ruta_relativa, $idSolicitud);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Soporte de pago subido y guardado en base de datos.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar en la base de datos: ' . $stmt->error]);
            }

            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => "Error al subir el archivo: $file_name"]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No se ha subido ningún archivo.']);
    }

    $mysqli->close();
}
