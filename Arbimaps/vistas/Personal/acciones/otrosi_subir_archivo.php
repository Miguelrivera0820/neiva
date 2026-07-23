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
$redirectUrl = neiva_app_url('Arbimaps/index.php?page=Personal/mis_notificaciones');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . $redirectUrl);
    exit;
}

$solicitudId = isset($_POST['solicitud_id']) ? (int)$_POST['solicitud_id'] : 0;

if ($solicitudId <= 0 || !isset($_FILES['archivo_otrosi']) || empty($_FILES['archivo_otrosi']['name'])) {
    header("Location: " . $redirectUrl);
    exit;
}

$sql  = "SELECT con_num_identidad FROM solicitudes_otrosi WHERE id = ? LIMIT 1";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $solicitudId);
$stmt->execute();
$result = $stmt->get_result();
$sol    = $result->fetch_assoc();
$stmt->close();

if (!$sol) {
    header("Location: " . $redirectUrl);
    exit;
}

$cedula         = trim($sol['con_num_identidad']);
$carpetaBase    = __DIR__ . '/../Arbitrium_otrosi_Nuevo/notificacion_firmada/';
$nombreOriginal = $_FILES['archivo_otrosi']['name'];
$nombreSeguro   = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $nombreOriginal);
$carpetaCedula  = $carpetaBase . $cedula . '/';
$carpetaSolicitud = $carpetaCedula . $solicitudId . '/';

if (!is_dir($carpetaSolicitud)) {
    mkdir($carpetaSolicitud, 0775, true);
}

$rutaDestino    = $carpetaSolicitud . $nombreSeguro;

if (move_uploaded_file($_FILES['archivo_otrosi']['tmp_name'], $rutaDestino)) {
    $sqlUpdate = "UPDATE solicitudes_otrosi 
                    SET sol_notificacion_firmada = ?, sol_estado_cargado = 'CARGADO'
                    WHERE id = ?";
    $stmt = $mysqli->prepare($sqlUpdate);
    $stmt->bind_param("si", $nombreSeguro, $solicitudId);
    $stmt->execute();
    $stmt->close();
}

header("Location: " . $redirectUrl);
exit;
