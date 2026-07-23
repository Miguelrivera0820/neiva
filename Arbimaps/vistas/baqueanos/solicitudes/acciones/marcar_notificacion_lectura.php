<?php
require_once dirname(__DIR__, 4) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 4) . '/config/permisos.php';
require_once dirname(__DIR__, 5) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('POST', true);
neiva_require_permission('menu.baqueanos', $PERMISOS, true);
neiva_require_csrf('global', true);

$solicitudId = (int) ($_POST['solicitud_id'] ?? 0);
$marca = (string) ($_POST['marca'] ?? '');

$columnasPermitidas = [
    'coordinador' => 'leido_coordinador',
    'profesional' => 'leido_profesional',
];

if ($solicitudId <= 0 || !isset($columnasPermitidas[$marca])) {
    neiva_abort(400, 'Solicitud inválida.', true);
}

$columna = $columnasPermitidas[$marca];
$sql = "UPDATE notificaciones_baqueanos SET {$columna} = 1 WHERE solicitud_baqueanos = ?";
$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    neiva_abort(500, 'No fue posible preparar la actualización.', true);
}

$stmt->bind_param('i', $solicitudId);
$stmt->execute();
$stmt->close();

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'ok' => true,
    'solicitud_id' => $solicitudId,
    'marca' => $marca,
], JSON_UNESCAPED_UNICODE);
exit;
