<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('GET');
neiva_require_permission('menu.tramites', $PERMISOS);

$cod = $_GET['cod'] ?? '';
$campo = $_GET['campo'] ?? '';
$camposPermitidos = [
    'sol_escrita_tramite',
    'cop_escritura_tramite',
    'ctl_tramite',
    'doc_identidad_tramite',
    'carta_autorizacion_tramite',
    'otros_doc_tramite',
    'notificacion',
    'firmado',
    'evidencias',
];

if ($cod === '' || !in_array($campo, $camposPermitidos, true)) {
    http_response_code(400);
    exit('Parametros invalidos.');
}

$sql = "SELECT {$campo} FROM no_procede_completar WHERE cod_radicacion_tramite = ? LIMIT 1";
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

$filename = $campo . '_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $cod) . '.pdf';

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Content-Length: ' . strlen($documento));
echo $documento;
