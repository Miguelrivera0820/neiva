<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once __DIR__ . '/../../../../conexion.php';
if (!isset($mysqli) || $mysqli === null) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Error: no se encontró la conexión $mysqli. Revisa conexion.php']);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$fecha = isset($_POST['pag_fecha_pago']) ? trim($_POST['pag_fecha_pago']) : '';

if ($id <= 0 || $fecha === '') {
    echo json_encode(['ok' => false, 'msg' => 'Datos incompletos.']);
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    echo json_encode(['ok' => false, 'msg' => 'Formato de fecha inválido.']);
    exit;
}

$stmt = $mysqli->prepare("UPDATE cuentas_pagadas SET pag_fecha_pago = ? WHERE id = ?");
if (!$stmt) {
    echo json_encode(['ok' => false, 'msg' => 'Error prepare: ' . $mysqli->error]);
    exit;
}

$stmt->bind_param("si", $fecha, $id);
$ok = $stmt->execute();

echo json_encode([
    'ok' => (bool)$ok,
    'msg' => $ok ? 'Actualizado' : ('Error: ' . $stmt->error)
]);
