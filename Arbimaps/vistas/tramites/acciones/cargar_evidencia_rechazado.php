<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('POST');
neiva_require_permission('menu.tramites', $PERMISOS);
neiva_require_csrf('global');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo'])) {
  $cod_tramite = $_POST['cod_tramite'] ?? '';
  $archivo = $_FILES['archivo'];

  if (empty($cod_tramite) || $archivo['error'] !== UPLOAD_ERR_OK) {
    die("Error: Código o archivo inválido.");
  }

  $anio = substr($cod_tramite, 4, 4);
  $base_dir = "../../tramites_conservacion/$anio/$cod_tramite";
  $evid_dir = "$base_dir/evidencias_rechazados";

  if (!is_dir($evid_dir)) {
    mkdir($evid_dir, 0777, true);
  }

  $ext = pathinfo($archivo['name'], PATHINFO_EXTENSION);
  $nombre_archivo = "evidencia_{$cod_tramite}_" . date("Ymd_His") . ".$ext";
  $ruta_destino = "$evid_dir/$nombre_archivo";

  if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
    // Guardar nombre en la base de datos
    $sql = "UPDATE rechazado_tramite SET evidencia_rechazado = ? WHERE cod_radicacion_tramite = ?";
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
      $stmt->bind_param("ss", $nombre_archivo, $cod_tramite);
      $stmt->execute();
      $stmt->close();
    }

    // Redirección corregida
    header("Location: " . neiva_app_url("Arbimaps/index.php?page=tramites/cuentas_rechazadas/rechazados&msg=success:Evidencia cargada correctamente&cod_evid=" . urlencode($cod_tramite)));
exit;
  } else {
    die("Error al mover el archivo.");
  }
}
?>
