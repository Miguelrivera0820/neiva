<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../../../../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}
$id               = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$numero_identidad = trim($_POST['numero_identidad'] ?? '');

if (!$id || $numero_identidad === '') {
    $url = neiva_app_url('Arbimaps/index.php?page=cuentas/aprobacion_social')
         . '&msg=' . urlencode('Datos incompletos para aprobar la cuenta.');
    header('Location: ' . $url);
    exit;
}

$stmt = $mysqli->prepare("
    UPDATE cuenta
    SET estado_seguridad_social = 'Aprobado',
        fecha_aprobado_social = NOW()
    WHERE id = ? AND numero_identidad = ?
");

if ($stmt) {
    $stmt->bind_param("is", $id, $numero_identidad);
    $stmt->execute();
    $stmt->close();

    $url = neiva_app_url('Arbimaps/index.php?page=cuentas/aprobacion_social')
         . '&msg=' . urlencode('Cuenta aprobada correctamente.');
    header('Location: ' . $url);
    exit;
} else {
    $url = neiva_app_url('Arbimaps/index.php?page=cuentas/aprobacion_social')
         . '&msg=' . urlencode('Error al preparar la consulta.');
    header('Location: ' . $url);
    exit;
}
