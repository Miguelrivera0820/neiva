<?php

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/vistas/tramites/base_catastral/base_catastral_lib.php';

neiva_bootstrap();
neiva_require_auth();
neiva_require_methods('GET', true);

$prediosPath = bc_predios_json_path();
if (!is_file($prediosPath)) {
    neiva_abort(404, 'No se encontro la base de predios.', true);
}

$prediosPayload = @file_get_contents($prediosPath);
if ($prediosPayload === false) {
    error_log('[predios_dataset] No se pudo leer el archivo de predios: ' . $prediosPath);
    neiva_abort(500, 'No fue posible cargar la base de predios.', true);
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: private, max-age=300');
header('X-Content-Type-Options: nosniff');
echo $prediosPayload;
exit;
