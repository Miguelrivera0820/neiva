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

$idsCsv = isset($_POST['ids']) ? trim($_POST['ids']) : '';
$fecha  = isset($_POST['pag_fecha_pago']) ? trim($_POST['pag_fecha_pago']) : '';

if ($idsCsv === '' || $fecha === '') {
    echo json_encode(['ok' => false, 'msg' => 'Datos incompletos.']);
    exit;
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    echo json_encode(['ok' => false, 'msg' => 'Formato de fecha inválido.']);
    exit;
}

$ids = array_filter(array_map('intval', explode(',', $idsCsv)));
$ids = array_values(array_unique($ids));
if (count($ids) === 0) {
    echo json_encode(['ok' => false, 'msg' => 'No hay IDs válidos.']);
    exit;
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$sql = "UPDATE cuentas_pagadas SET pag_fecha_pago = ? WHERE id IN ($placeholders)";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    echo json_encode(['ok' => false, 'msg' => 'Error prepare: ' . $mysqli->error]);
    exit;
}

$types  = 's' . str_repeat('i', count($ids));
$params = array_merge([$fecha], $ids);

$tmp    = [];
$tmp[]  = $types;
foreach ($params as $k => $v) {
    $tmp[] = &$params[$k];
}
call_user_func_array([$stmt, 'bind_param'], $tmp);

$ok = $stmt->execute();
echo json_encode([
    'ok' => (bool)$ok,
    'msg' => $ok ? ('Actualizados: ' . $stmt->affected_rows) : ('Error: ' . $stmt->error)
]);
