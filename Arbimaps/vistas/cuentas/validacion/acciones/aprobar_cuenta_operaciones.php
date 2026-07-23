<?php
// Iniciar sesión solo si no está iniciada
if (session_status() === PHP_SESSION_NONE) session_start();

// Conexión centralizada
require_once __DIR__ . '/../../../../../conexion.php';

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}

// Recibir datos del formulario
$id               = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$numero_identidad = trim($_POST['numero_identidad'] ?? '');

// Validar datos mínimos
if (!$id || $numero_identidad === '') {
    $url = neiva_app_url('Arbimaps/index.php?page=cuentas/aprobacion_operaciones')
         . '&msg=' . urlencode('Datos incompletos para aprobar la cuenta.');
    header('Location: ' . $url);
    exit;
}

// Preparar UPDATE usando tu estilo
$stmt = $mysqli->prepare("
    UPDATE cuenta
    SET estado_final   = 'Aprobado',
        fecha_aprobacion_final = NOW()
    WHERE id = ?
      AND numero_identidad = ?
      AND estado_seguridad_social = 'Aprobado'
");

if ($stmt) {
    $stmt->bind_param("is", $id, $numero_identidad);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Se actualizó correctamente
        $msg = 'Cuenta aprobada correctamente.';
    } else {
        // No se actualizó nada: o no coincide id/cedula o SS no está aprobada
        $msg = 'No se pudo aprobar la cuenta. Verifique que la seguridad social esté aprobada.';
    }

    $stmt->close();
} else {
    $msg = 'Error al preparar la consulta.';
}

// Redirigir de vuelta a la vista de aprobación de operaciones
$url = neiva_app_url('Arbimaps/index.php?page=cuentas/aprobacion_operaciones')
     . '&msg=' . urlencode($msg);
header('Location: ' . $url);
exit;
