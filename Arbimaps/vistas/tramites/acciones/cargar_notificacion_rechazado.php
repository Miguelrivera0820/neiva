<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('POST');
neiva_require_permission('menu.tramites', $PERMISOS);
neiva_require_csrf('global');

if (!isset($mysqli) || $mysqli === null) {
  die("Error: Conexión no establecida.");
}

$cod_tramite = $_POST['cod_tramite'] ?? '';
if (empty($cod_tramite)) {
  die("Código de trámite no recibido.");
}

// Rutas
$anio = substr($cod_tramite, 4, 4);
$ruta_base = "../../tramites_conservacion/$anio/$cod_tramite";
$ruta_notificacion = "$ruta_base/notificaciones_rechazadas";

if (!file_exists($ruta_notificacion)) {
  mkdir($ruta_notificacion, 0777, true);
}

// Validar archivo
if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
  $redirect_url = neiva_app_url("Arbimaps/index.php?page=tramites/cuentas_rechazadas/rechazados&action=ver&cod=" . urlencode($cod_tramite) . "&msg=error:" . urlencode("Error al subir el archivo."));
  header("Location: $redirect_url");
  exit;
}

$archivo = $_FILES['archivo'];
$ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
if ($ext !== 'pdf') {
  $redirect_url = neiva_app_url("Arbimaps/index.php?page=tramites/cuentas_rechazadas/rechazados&action=ver&cod=" . urlencode($cod_tramite) . "&msg=error:" . urlencode("Solo se permiten archivos PDF."));
  header("Location: $redirect_url");
  exit;
}

// Nombre único con timestamp
$timestamp = time();
$nombre_archivo = "notificacion_{$cod_tramite}_{$timestamp}.pdf";
$ruta_destino = "$ruta_notificacion/$nombre_archivo";

// Mover archivo
if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
  // Actualizar BD con el nombre más reciente
  $sql = "UPDATE rechazado_tramite SET rechazado_notificacion = ? WHERE cod_radicacion_tramite = ?";
  $stmt = $mysqli->prepare($sql);
  if ($stmt) {
    $stmt->bind_param("ss", $nombre_archivo, $cod_tramite);
    $stmt->execute();
    $stmt->close();
  }
  
  $msg = "success:Notificación cargada correctamente.";
} else {
  $msg = "error:Error al guardar el archivo.";
}

$redirect_url = neiva_app_url("Arbimaps/index.php?page=tramites/cuentas_rechazadas/rechazados&action=ver&cod=" . urlencode($cod_tramite) . "&msg=" . urlencode($msg));
header("Location: $redirect_url");
exit;
?>
