<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('POST');
neiva_require_permission('menu.personalViabilidadFinanciera', $PERMISOS);
neiva_require_csrf('global');

if (!isset($mysqli) || $mysqli === null) {
    die('Error: no se encontró la conexión $mysqli. Revisa conexion.php');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pendientes_otrosi.php');
    exit;
}

$id               = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$motivo           = isset($_POST['motivo']) ? trim($_POST['motivo']) : '';
$numero_identidad = isset($_POST['numero_identidad']) ? trim($_POST['numero_identidad']) : '';

if ($id <= 0 || $motivo === '') {
    $_SESSION['mensaje_error'] = 'El motivo está vacío o el ID no es válido.';
    header('Location: ' . neiva_app_url('Arbimaps/index.php?page=Personal/viabilidad_financiera'));
    exit;
}
$sql = "UPDATE solicitudes_otrosi 
        SET sol_estado = 'NO VIABLE', sol_motivo_rechazo = ? 
        WHERE id = ?";

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("si", $motivo, $id);

    if ($stmt->execute()) {
        $_SESSION['mensaje_exito'] = 'La solicitud ha sido rechazada correctamente.';

        header('Location: ' . neiva_app_url('Arbimaps/index.php?page=Personal/viabilidad_financiera'));
        exit;
    } else {
        $_SESSION['mensaje_error'] = 'Error al ejecutar la actualización: ' . $stmt->error;
        header('Location: ' . neiva_app_url('Arbimaps/index.php?page=Personal/viabilidad_financiera'));
        exit;
    }

    $stmt->close();
} else {
    $_SESSION['mensaje_error'] = 'Error al preparar la consulta: ' . $mysqli->error;
    header('Location: ' . neiva_app_url('Arbimaps/index.php?page=Personal/viabilidad_financiera'));
    exit;
}

$mysqli->close();
