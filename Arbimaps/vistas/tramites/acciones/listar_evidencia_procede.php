<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('GET', true);
neiva_require_permission('menu.tramites', $PERMISOS, true);

$cod_tramite = $_GET['cod_tramite'] ?? '';

if (empty($cod_tramite) || !preg_match('/^[A-Za-z0-9_-]+$/', $cod_tramite)) {
  header('Content-Type: application/json');
  echo json_encode([]);
  exit;
}

$anio = substr($cod_tramite, 4, 4);
$base_fs = "../../tramites_conservacion/$anio/$cod_tramite";
$evid_dir = "$base_fs/evidencia_procede";
$url_base_dir = "vistas/tramites_conservacion/$anio/$cod_tramite/evidencia_procede/";

$evidencias = [];

if (is_dir($evid_dir)) {
  $files = glob($evid_dir . "/*.{pdf,jpg,jpeg,png}", GLOB_BRACE);
  if (!empty($files)) {
    // Ordenar por fecha (más reciente primero)
    usort($files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });

    // Tomar solo el archivo más reciente
    $file = $files[0];
    $evidencias[] = [
        'name' => basename($file),
        'url' => $url_base_dir . rawurlencode(basename($file)),
        'date' => date('Y-m-d H:i:s', filemtime($file))
    ];
}
}

// Fallback a la base de datos si no hay archivos en FS
if (empty($evidencias)) {
  $sql = "SELECT evidencia_procede FROM procede_tramite WHERE cod_radicacion_tramite = ? LIMIT 1";
  $stmt = $mysqli->prepare($sql);
  if ($stmt) {
    $stmt->bind_param("s", $cod_tramite);
    $stmt->execute();
    $stmt->bind_result($db_evidencia_name);
    $stmt->fetch();
    if (!empty($db_evidencia_name)) {
      $evidencias[] = [
        'name' => $db_evidencia_name,
        'url' => $url_base_dir . rawurlencode($db_evidencia_name),
        'date' => 'N/D (BD)'
      ];
    }
    $stmt->close();
  }
}

header('Content-Type: application/json');
echo json_encode($evidencias);

$mysqli->close();
?>
