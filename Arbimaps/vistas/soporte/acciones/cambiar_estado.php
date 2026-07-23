<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('POST');
neiva_require_permission('menu.soporte', $PERMISOS);
neiva_require_csrf('global');

if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    die("Error de conexión a la base de datos.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_error = $_POST['codigo_error'] ?? null;
    $nuevo_estado = $_POST['nuevo_estado'] ?? 'EN REVISION';

    if (!$codigo_error) {
        die("Código de error no recibido.");
    }

    $estados_permitidos = ['EN REVISION', 'SOLUCIONADO'];
    if (!in_array($nuevo_estado, $estados_permitidos, true)) {
        die("Estado no permitido.");
    }

    $sql = "UPDATE solicitud_soporte
            SET estado_error = ?
            WHERE codigo_error = ?";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        die("Error al preparar la consulta: " . $mysqli->error);
    }

    $stmt->bind_param("ss", $nuevo_estado, $codigo_error);

    if ($stmt->execute()) {
        header('Location: ' . neiva_app_url('Arbimaps/index.php?page=soporte/mesa_ayuda'), true, 303);
        exit;
    }

    die("Error al actualizar el estado: " . $stmt->error);
}

die("Método no permitido.");
