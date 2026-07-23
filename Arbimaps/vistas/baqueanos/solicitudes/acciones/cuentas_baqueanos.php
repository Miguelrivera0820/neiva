<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../../../../conexion.php';

function insertarNotificacionPorRol(mysqli $mysqli, string $rolDestino, int $solicitudId, string $tipo, string $mensaje): void
{
    $sqlUsuarios = "SELECT id_usuario 
                    FROM usuarios_cons 
                    WHERE rol_usuario = ? 
                        OR rol_usuario_dos = ? 
                        OR rol_usuario_tres = ?";
    $stmtUsuarios = $mysqli->prepare($sqlUsuarios);
    if (!$stmtUsuarios) {
        die("Error preparando SELECT usuarios por rol: " . $mysqli->error);
    }

    $stmtUsuarios->bind_param("sss", $rolDestino, $rolDestino, $rolDestino);
    if (!$stmtUsuarios->execute()) {
        $stmtUsuarios->close();
        die("Error ejecutando SELECT usuarios por rol: " . $stmtUsuarios->error);
    }

    $resultado = $stmtUsuarios->get_result();
    if ($resultado->num_rows === 0) {
        $stmtUsuarios->close();
        return;
    }

    $sqlInsert = "INSERT INTO notificaciones_baqueanos
                                (
                                    id_usuario, 
                                    solicitud_id, 
                                    tipo, 
                                    mensaje, 
                                    leida, 
                                    fecha_creacion
                                )
                    VALUES (?, ?, ?, ?, 0, NOW())";
    $stmtInsert = $mysqli->prepare($sqlInsert);
    if (!$stmtInsert) {
        $stmtUsuarios->close();
        die("Error preparando INSERT notificación: " . $mysqli->error);
    }
    while ($usuario = $resultado->fetch_assoc()) {
        $idUsuario = (int)$usuario['id_usuario'];
        $stmtInsert->bind_param("iiss", $idUsuario, $solicitudId, $tipo, $mensaje);
        $stmtInsert->execute();
    }
    $stmtInsert->close();
    $stmtUsuarios->close();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

$usuario_id = $_SESSION['id_usuario'];
$idSolicitud = $_POST['id'] ?? null;
if (!$idSolicitud) {
    echo json_encode(['success' => false, 'message' => 'ID de solicitud no proporcionado.']);
    exit;
}

$sb_observacion_cuenta  = $_POST['sb_observacion_cuenta'] ?? null;
$sb_numero_identidad    = $_POST['sb_numero_identidad'] ?? null;
$sb_confirmar_dias      = intval($_POST['sb_confirmar_dias'] ?? 0);
$sb_confirmar_valor     = $_POST['sb_confirmar_valor'] ?? '0';
$sb_confirmar_valor     = (int) preg_replace('/[^\d]/', '', $sb_confirmar_valor);

$mes_actual = date('m');
$ba_periodo_facturacion = $mes_actual;

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

$base_upload_dir    = '../DOCUMENTOS_BAQUENOS/cuentas_baqueanos/';
$usuario_base_dir   = $base_upload_dir . $año_actual . '/' . $ba_periodo_facturacion . '/RAD_' . $idSolicitud . '/';

$sub_dir_map = [
    'sb_cuenta_baqueano'      => 'cuenta_de_cobro',
    'sb_cedula_baqueano'      => 'cedula',
    'sb_rut_baqueano'         => 'RUT',
    'sb_certificado_baqueano' => 'certificado_bancario'
];

$archivos_subidos = [];

foreach ($sub_dir_map as $campo => $subcarpeta) {
    $upload_dir = $usuario_base_dir . $subcarpeta . '/';
    if (!file_exists($upload_dir) && !mkdir($upload_dir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => "Error al crear carpeta: $upload_dir"]);
        exit;
    }
    if (isset($_FILES[$campo]) && $_FILES[$campo]['error'] === 0) {
        $file_name = basename($_FILES[$campo]['name']);
        $destino = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES[$campo]['tmp_name'], $destino)) {
            $archivos_subidos[$campo] = $file_name;
        } else {
            echo json_encode(['success' => false, 'message' => "Error al subir el archivo: $file_name"]);
            exit;
        }
    } else {
        $archivos_subidos[$campo] = null;
    }
}

$sql = "UPDATE solicitud_baqueanos SET 
    sb_cuenta_baqueano = ?,
    sb_cedula_baqueano = ?,
    sb_rut_baqueano = ?,
    sb_certificado_baqueano = ?,
    sb_observacion_cuenta = ?,
    ba_periodo_facturacion = ?,
    sb_confirmar_dias = ?,
    sb_confirmar_valor = ?,
    sb_estado_cuenta = 'RADICADO'
    WHERE id = ?";
$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta: ' . $mysqli->error]);
    exit;
}
$stmt->bind_param(
    "ssssssiii",
    $archivos_subidos['sb_cuenta_baqueano'],
    $archivos_subidos['sb_cedula_baqueano'],
    $archivos_subidos['sb_rut_baqueano'],
    $archivos_subidos['sb_certificado_baqueano'],
    $sb_observacion_cuenta,
    $ba_periodo_facturacion,
    $sb_confirmar_dias,
    $sb_confirmar_valor,
    $idSolicitud
);
if ($stmt->execute()) {
    $tipo    = "CUENTA_BAQUEANOS";
    $mensaje = "La cuenta de cobro de la solicitud ARB_{$idSolicitud} fue RADICADA por el área social y está pendiente de tu revisión.";
    insertarNotificacionPorRol($mysqli, "director_proyectos", (int)$idSolicitud, $tipo, $mensaje);
    echo json_encode(['success' => true, 'message' => 'Cuenta radicada y notificación enviada correctamente.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar en la base de datos: ' . $stmt->error]);
}
$stmt->close();
$mysqli->close();