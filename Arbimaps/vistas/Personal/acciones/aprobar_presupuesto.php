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
    header('Location: ' . neiva_app_url('Arbimaps/index.php?page=Personal/viabilidad_financiera'));
    exit;
}

// Datos recibidos del formulario
$id               = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$numero_identidad = isset($_POST['numero_identidad']) ? trim($_POST['numero_identidad']) : '';

// Validar
if ($id <= 0) {
    $_SESSION['mensaje_error'] = 'ID de solicitud no válido.';
    header('Location: ' . neiva_app_url('Arbimaps/index.php?page=Personal/viabilidad_financiera'));
    exit;
}

// Puedes actualizar por ID (más preciso)
$sql = "UPDATE solicitudes_otrosi 
        SET sol_estado = 'VIABLE'
        WHERE id = ?";

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['mensaje_exito'] = 'La solicitud ha sido marcada como VIABLE.';

        header('Location: ' . neiva_app_url('Arbimaps/index.php?page=Personal/viabilidad_financiera'));
        exit;
    } else {
        $_SESSION['mensaje_error'] = 'Error al actualizar estado: ' . $stmt->error;
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
