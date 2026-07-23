<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../../conexion.php';
if (!isset($mysqli) || $mysqli === null) {
    http_response_code(500);
    exit('Error: no se encontró la conexión $mysqli. Revisa conexion.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    exit('ID inválido');
}

$sql = "UPDATE solicitudes_otrosi
        SET sol_estado_usuario = 'RECHAZADO'
        WHERE id = ?";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    exit('Error al preparar la consulta');
}

$stmt->bind_param("i", $id);

if (!$stmt->execute()) {
    $stmt->close();
    http_response_code(500);
    exit('Error al actualizar el estado');
}

$stmt->close();
$mysqli->close();
$_SESSION['swal_rechazado_ok'] = 1;
$_SESSION['swal_rechazado_msg'] = 'La notificación se marcó como RECHAZADA.';

header('Location: /arbimaps/Arbimaps/index.php?page=Personal/mis_notificaciones');
exit;
