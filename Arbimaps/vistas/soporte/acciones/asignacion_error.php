<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('POST');
neiva_require_permission('soporte.gestion_tickets', $PERMISOS);
neiva_require_csrf('global');

$codigo_error = $_POST['codigo_error'] ?? null;
$id_usuario_asignado = $_POST['id_usuario_asignado'] ?? null;

if (!$codigo_error || !$id_usuario_asignado) {
    header(
        'Location: ' . neiva_app_url('Arbimaps/index.php?page=soporte/asignar_error')
        . '&codigo=' . urlencode((string) $codigo_error)
        . '&asignacion=missing'
    );
    exit;
}

$sql_update_ticket = "UPDATE solicitud_soporte 
                      SET id_usuario = ? 
                      WHERE codigo_error = ?";

$stmt_ticket = $mysqli->prepare($sql_update_ticket);

if (!$stmt_ticket) {
    header(
        'Location: ' . neiva_app_url('Arbimaps/index.php?page=soporte/asignar_error')
        . '&codigo=' . urlencode((string) $codigo_error)
        . '&asignacion=error'
    );
    exit;
}

$stmt_ticket->bind_param('is', $id_usuario_asignado, $codigo_error);

if ($stmt_ticket->execute()) {
    $stmt_ticket->close();
    header(
        'Location: ' . neiva_app_url('Arbimaps/index.php?page=soporte/asignar_error')
        . '&codigo=' . urlencode((string) $codigo_error)
        . '&asignacion=ok'
    );
    exit;
}

$stmt_ticket->close();
header(
    'Location: ' . neiva_app_url('Arbimaps/index.php?page=soporte/asignar_error')
    . '&codigo=' . urlencode((string) $codigo_error)
    . '&asignacion=fail'
);
exit;
