<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('GET');
neiva_require_permission('menu.tramites', $PERMISOS);

$cod = $_GET['cod'] ?? '';
if ($cod === '') {
    http_response_code(400);
    exit('Codigo de tramite requerido.');
}

$sql = "SELECT documento_generado FROM no_procede_completar WHERE cod_radicacion_tramite = ? LIMIT 1";
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    exit('No fue posible preparar la consulta.');
}

$stmt->bind_param("s", $cod);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($documento);

if (!$stmt->fetch() || empty($documento)) {
    $stmt->close();
    http_response_code(404);
    exit('Documento no encontrado.');
}
$stmt->close();

$filename = 'no_procede_completar_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $cod) . '.pdf';

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Content-Length: ' . strlen($documento));
echo $documento;
