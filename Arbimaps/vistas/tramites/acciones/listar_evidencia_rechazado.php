<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('GET', true);
neiva_require_permission('menu.tramites', $PERMISOS, true);

$cod = $_GET['cod_tramite'] ?? '';
$anio = substr($cod, 4, 4);
$base_dir = "../../tramites_conservacion/$anio/$cod/evidencias_rechazados";
$data = [];

if (is_dir($base_dir)) {
  $files = glob("$base_dir/*.{pdf,jpg,jpeg,png}", GLOB_BRACE);

  if (!empty($files)) {
    usort($files, function($a, $b) {
      return filemtime($b) - filemtime($a);
    });

    $latest = $files[0];

    $data[] = [
      'name' => basename($latest),
      'url'  => "vistas/tramites_conservacion/$anio/$cod/evidencias_rechazados/" . rawurlencode(basename($latest)),
      'date' => date('Y-m-d H:i:s', filemtime($latest))
    ];
  }
}

header('Content-Type: application/json');
echo json_encode($data);
?>
