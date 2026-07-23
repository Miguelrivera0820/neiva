<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../../../conexion.php';

function insertarNotificacionSolicitud($mysqli, int $solicitudId, string $tipo, string $mensaje): void
{
    $usuarioSolicitud = 0;
    $sqlNoti = "SELECT usuario_id FROM solicitud_baqueanos WHERE id = ? LIMIT 1";
    $stmtNoti = $mysqli->prepare($sqlNoti);
    if (!$stmtNoti) return;

    $stmtNoti->bind_param("i", $solicitudId);
    if (!$stmtNoti->execute()) {
        $stmtNoti->close();
        return;
    }

    $stmtNoti->bind_result($usuarioSolicitud);
    if (!$stmtNoti->fetch()) {
        $stmtNoti->close();
        return;
    }
    $stmtNoti->close();

    $usuarioSolicitud = (int)$usuarioSolicitud;
    if ($usuarioSolicitud <= 0) return;

    $sqlMensaje = "INSERT INTO notificaciones_baqueanos
                                (
                                    id_usuario, 
                                    solicitud_id, 
                                    tipo, 
                                    mensaje, 
                                    leida, 
                                    fecha_creacion
                                )
                    VALUES (?, ?, ?, ?, 0, NOW())";
    $stmtMensaje = $mysqli->prepare($sqlMensaje);
    if (!$stmtMensaje) return;
    $stmtMensaje->bind_param("iiss", $usuarioSolicitud, $solicitudId, $tipo, $mensaje);
    $stmtMensaje->execute();
    $stmtMensaje->close();
}

function insertarNotificacionPorRol(mysqli $mysqli, string $rolDestino, int $solicitudId, string $tipo, string $mensaje): void
{
    $sqlUsuarios = "SELECT id_usuario 
                    FROM usuarios_cons 
                    WHERE rol_usuario = ? 
                        OR rol_usuario_dos = ? 
                        OR rol_usuario_tres = ?";
    $stmtUsuarios = $mysqli->prepare($sqlUsuarios);
    if (!$stmtUsuarios) return;

    $stmtUsuarios->bind_param("sss", $rolDestino, $rolDestino, $rolDestino);
    if (!$stmtUsuarios->execute()) {
        $stmtUsuarios->close();
        return;
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
        return;
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

$idSolicitud = $_POST['id_solicitud'] ?? null;
if (!$idSolicitud) {
    echo json_encode(['success' => false, 'message' => 'ID de solicitud no proporcionado.']);
    exit;
}

date_default_timezone_set('America/Bogota');

$fechaHoraPago = $_POST['fecha_hora_pago'] ?? null;
$medioPago = $_POST['sb_medio_pago'] ?? null;

if (!$fechaHoraPago || !$medioPago) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit;
}

$fechaHoraPago = str_replace('T', ' ', $fechaHoraPago);

if (!strtotime($fechaHoraPago)) {
    echo json_encode(['success' => false, 'message' => 'Fecha inválida.']);
    exit;
}

$medioPago = strtoupper(trim($medioPago));

$sqlCheck = "SELECT sb_soporte_pago FROM solicitud_baqueanos WHERE id = ? LIMIT 1";
$stmtCheck = $mysqli->prepare($sqlCheck);
if (!$stmtCheck) {
    echo json_encode(['success' => false, 'message' => 'Error preparando consulta: ' . $mysqli->error]);
    exit;
}

$stmtCheck->bind_param("i", $idSolicitud);
$stmtCheck->execute();
$resCheck = $stmtCheck->get_result();
$row = $resCheck->fetch_assoc();
$stmtCheck->close();

$soporte = $row['sb_soporte_pago'] ?? null;
if (!$soporte) {
    echo json_encode(['success' => false, 'message' => 'No se puede marcar como pagado: no hay soporte cargado.']);
    exit;
}

$nuevoEstado = "PAGADO";
$sql = "UPDATE solicitud_baqueanos 
        SET sb_estado_final_pago = ?,
            fecha_hora_pago = ?,
            sb_medio_pago = ?
        WHERE id = ?";
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error preparando consulta: ' . $mysqli->error]);
    exit;
}

$stmt->bind_param("sssi", $nuevoEstado, $fechaHoraPago, $medioPago, $idSolicitud);

if ($stmt->execute()) {

    $tipo = "SOLICITUD_BAQUEANOS";
    $mensajeUsuario = "FELICIDADES tu solicitud ARB_{$idSolicitud} terminó exitosamente todo el proceso y ya fue PAGADA.";
    insertarNotificacionSolicitud($mysqli, (int)$idSolicitud, $tipo, $mensajeUsuario);
    $mensajeRol = "La solicitud ARB_{$idSolicitud} fue marcada como PAGADA por el área de pagos.";
    insertarNotificacionPorRol($mysqli, "social", (int)$idSolicitud, $tipo, $mensajeRol);
    echo json_encode(['success' => true, 'message' => 'Solicitud marcada como PAGADA.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $stmt->error]);
}
$stmt->close();
$mysqli->close();
