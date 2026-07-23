<?php
require_once __DIR__ . '/../../../includes/bootstrap.php';
require_once __DIR__ . '/../../../../conexion.php';
require_once __DIR__ . '/../funciones_restricciones.php';

neiva_bootstrap();
neiva_require_methods(['GET', 'POST'], true);
neiva_require_auth(true);

header('Content-Type: application/json; charset=utf-8');

try {
    $bloqueo = buscarBloqueoActivoPredio(
        $mysqli,
        (string) ($_REQUEST['npn'] ?? ''),
        (string) ($_REQUEST['fmi'] ?? '')
    );

    echo json_encode([
        'ok' => true,
        'bloqueado' => $bloqueo !== null,
        'mensaje' => $bloqueo
            ? 'Este predio está bloqueado y no se permite crear el trámite.'
            : 'Predio disponible.',
        'bloqueo' => $bloqueo,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    error_log('Restricciones consultar_estado failed: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'No fue posible consultar el estado del predio.',
    ], JSON_UNESCAPED_UNICODE);
}
