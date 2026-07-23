<?php
// session_start();
// CONFIG Y CONEXIÓN
$conexion_file = '../conexion.php';
if (!file_exists($conexion_file)) {
  die("Error: No se encuentra el archivo de conexión en: " . $conexion_file);
}
require $conexion_file;

if (!isset($mysqli) || $mysqli === null) {
  die("Error: La variable \$mysqli no está definida en conexion.php. Verifica el archivo.");
}

// CONTROL DE ACCESO
$rol_usuario = $_SESSION['rol_usuario'] ?? '';
$nombre_usuario = $_SESSION['nombre_usuario'] ?? 'Usuario';
$apellido_usuario = $_SESSION['apellido_usuario'] ?? '';
$cedula_usuario = $_SESSION['cedula_usuario'] ?? '';

$roles_permitidos_rechazados = ['administrador','soporte','procedencia_juridica', 'ventanilla_catastral', 'director_proyectos', 'coordinacion_tecnica'];
$tiene_permiso_rechazados = !empty($rol_usuario) && in_array($rol_usuario, $roles_permitidos_rechazados);
if (!$tiene_permiso_rechazados) {
  header('Location: ../index_graficas.php');
  exit;
}

// SERVIDOR DE PDF EN LA MISMA PÁGINA
if (isset($_GET['doc_cod'])) {
  $cod = $_GET['doc_cod'];
  if (!is_string($cod) || strlen($cod) > 64) {
    http_response_code(400);
    echo "Código inválido.";
    exit;
  }
  if ($mysqli->connect_error) {
    http_response_code(500);
    echo "Error de conexión.";
    exit;
  }
  $sqlDoc = "SELECT documento_generado
               FROM rechazado_tramite
               WHERE cod_radicacion_tramite = ?
               LIMIT 1";
  $stmtDoc = $mysqli->prepare($sqlDoc);
  if ($stmtDoc === false) {
    http_response_code(500);
    echo "Error al preparar consulta: " . $mysqli->errno . " - " . $mysqli->error;
    exit;
  }

  $okBind = $stmtDoc->bind_param("s", $cod);
  if ($okBind === false) {
    http_response_code(500);
    echo "Error en bind_param: " . $stmtDoc->errno . " - " . $stmtDoc->error;
    exit;
  }

  $okExec = $stmtDoc->execute();
  if ($okExec === false) {
    http_response_code(500);
    echo "Error al ejecutar consulta: " . $stmtDoc->errno . " - " . $stmtDoc->error;
    exit;
  }

  $stmtDoc->store_result();
  $stmtDoc->bind_result($docBlob);

  if ($stmtDoc->num_rows > 0) {
    $stmtDoc->fetch();
    if (!empty($docBlob)) {
      header('Content-Type: application/pdf');
      header('Content-Disposition: inline; filename="rechazado_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $cod) . '.pdf"');
      header('Content-Length: ' . strlen($docBlob));
      echo $docBlob;
      exit;
    }
  }

  http_response_code(404);
  echo "Documento no encontrado para el código proporcionado.";
  exit;
}

// CONSULTA DE TABLA PRINCIPAL
if ($mysqli->connect_error) {
  die("Error de conexión: " . $mysqli->connect_error);
}

$action = $_GET['action'] ?? '';
$cod_tramite = $_GET['cod'] ?? '';
$row_detalle = null;
$notificaciones = [];
$has_notification = false;
$latest_notification_url = '';
if ($action === 'ver' && !empty($cod_tramite)) {
  $query_detalle = "SELECT 
      r.cod_tramite,
      r.fmi_predio_tram,
      r.nombre_propietario_tram,
      r.tipo_doc_propietario_tram,
      r.cedula_propietario_tram,
      r.valor_avaluo_terreno_tram,
      r.direccion_predio_terreno_tram,
      r.destino_econ_predio_tram,
      r.area_terr_predio_tram,
      r.area_cons_predio_tram,
      r.fecha_rad,
      r.documento_interesado,
      r.num_doc_interesado,
      r.primer_nombre_interesado,
      r.segundo_nombre_interesado,
      r.primer_apellido_interesado,
      r.segundo_apellido_interesado,
      r.telefono_interesado,
      r.correo_interesado,
      r.mutacion_tramite,
      r.fecha_limite_respuesta,
      r.tsolicitante_tramite,
      r.fmi_predio,
      r.npn_predio,
      r.observacion_tramite
  FROM rechazados r
  WHERE r.cod_tramite = ?
  LIMIT 1";

  $stmt_detalle = $mysqli->prepare($query_detalle);
  if ($stmt_detalle) {
    $stmt_detalle->bind_param("s", $cod_tramite);
    $stmt_detalle->execute();
    $result_detalle = $stmt_detalle->get_result();
    if ($result_detalle->num_rows > 0) {
      $row_detalle = $result_detalle->fetch_assoc();
    }
    $stmt_detalle->close();
  }

  // Escanear notificaciones en múltiples carpetas posibles
  if ($row_detalle) {
    $anio = substr($row_detalle['cod_tramite'], 4, 4);
    $base_fs = "../../tramites_conservacion/$anio/" . $row_detalle['cod_tramite'];
    $sub_dirs = ['notificacion', 'notificacion_no_procede', 'notificaciones_rechazadas'];
    $url_base_dir = "vistas/tramites_conservacion/$anio/" . $row_detalle['cod_tramite'] . "/";

    foreach ($sub_dirs as $sub) {
      $dir_fs = $base_fs . '/' . $sub;
      if (is_dir($dir_fs)) {
        $files = glob($dir_fs . "/*.pdf");
        foreach ($files as $file) {
          $notificaciones[] = [
            'name' => basename($file),
            'subdir' => $sub,
            'url' => 'vistas/tramites/cuentas_rechazadas/ver_notificacion_rechazado.php?cod=' . urlencode($row_detalle['cod_tramite']) . '&archivo=' . rawurlencode(basename($file)),
            'date' => date('Y-m-d H:i:s', filemtime($file))
          ];
        }
      }
    }

    $has_notification = !empty($notificaciones);
    if (!$has_notification) {
      $sql_notification = "SELECT rechazado_notificacion FROM rechazado_tramite WHERE cod_radicacion_tramite = ?";
      $stmt_notification = $mysqli->prepare($sql_notification);
      $db_notification_name = '';
      if ($stmt_notification) {
        $stmt_notification->bind_param("s", $row_detalle['cod_tramite']);
        $stmt_notification->execute();
        $stmt_notification->bind_result($db_notification_name);
        $stmt_notification->fetch();
        $stmt_notification->close();
      }

      if (!empty($db_notification_name)) {
        $has_notification = true;
        $notificaciones[] = [
          'name' => $db_notification_name,
          'subdir' => 'notificaciones_rechazadas',
          'url' => 'vistas/tramites/cuentas_rechazadas/ver_notificacion_rechazado.php?cod=' . urlencode($row_detalle['cod_tramite']) . '&archivo=' . rawurlencode($db_notification_name),
          'date' => 'N/D (BD)'
        ];
      }
    }

    // Ordenar por fecha descendente
    if ($has_notification) {
      usort($notificaciones, function ($a, $b) {
        $da = ($a['date'] === 'N/D (BD)') ? '1970-01-01 00:00:00' : $a['date'];
        $db = ($b['date'] === 'N/D (BD)') ? '1970-01-01 00:00:00' : $b['date'];
        return strtotime($db) - strtotime($da);
      });
      $latest_notification_url = $notificaciones[0]['url'];
    }
  }
}

// Consulta para la tabla principal (solo si no estamos en modo detalle)
$result = false;
if ($action !== 'ver') {
  $query = "SELECT 
      r.cod_tramite,
      r.fmi_predio_tram,
      r.nombre_propietario_tram,
      r.tipo_doc_propietario_tram,
      r.cedula_propietario_tram,
      r.valor_avaluo_terreno_tram,
      r.direccion_predio_terreno_tram,
      r.destino_econ_predio_tram,
      r.area_terr_predio_tram,
      r.area_cons_predio_tram,
      r.fecha_rad,
      r.documento_interesado,
      r.num_doc_interesado,
      r.primer_nombre_interesado,
      r.segundo_nombre_interesado,
      r.primer_apellido_interesado,
      r.segundo_apellido_interesado,
      r.telefono_interesado,
      r.correo_interesado,
      r.mutacion_tramite,
      r.fecha_limite_respuesta,
      r.tsolicitante_tramite,
      r.fmi_predio,
      r.npn_predio,
      r.observacion_tramite,
      CASE WHEN rt.rechazado_notificacion IS NOT NULL THEN 1 ELSE 0 END as has_notificacion
  FROM rechazados r
  LEFT JOIN rechazado_tramite rt ON r.cod_tramite = rt.cod_radicacion_tramite
  ORDER BY r.id_denegado DESC";

  $result = $mysqli->query($query);

  if (!$result) {
    echo "<div class='alert alert-danger'>Error en la consulta: " . $mysqli->error . "</div>";
    $result = false;
  }

  // Preparar datos para la tabla con URLs de notificaciones
  $rows = [];
  if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $cod = $row['cod_tramite'] ?? '';
      $anio = substr($cod, 4, 4);
      $base_fs = "../../tramites_conservacion/$anio/$cod";
      $url_base_dir = "vistas/tramites_conservacion/$anio/$cod/";
      $notification_url = '';

      if ($row['has_notificacion']) {
        $sub_dirs = ['notificacion', 'notificacion_no_procede', 'notificaciones_rechazadas'];
        foreach ($sub_dirs as $sub) {
          $dir_fs = $base_fs . '/' . $sub;
          if (is_dir($dir_fs)) {
            $files = glob($dir_fs . "/*.pdf");
            if (!empty($files)) {
              // Tomar el archivo más reciente
              $latest_file = $files[0];
              foreach ($files as $file) {
                if (filemtime($file) > filemtime($latest_file)) {
                  $latest_file = $file;
                }
              }
              $notification_url = 'vistas/tramites/cuentas_rechazadas/ver_notificacion_rechazado.php?cod=' . urlencode($cod) . '&archivo=' . rawurlencode(basename($latest_file));
              break;
            }
          }
        }
        // Fallback a BD si no hay archivos en FS
        if (!$notification_url) {
          $sql_notification = "SELECT rechazado_notificacion FROM rechazado_tramite WHERE cod_radicacion_tramite = ?";
          $stmt_notification = $mysqli->prepare($sql_notification);
          if ($stmt_notification) {
            $stmt_notification->bind_param("s", $cod);
            $stmt_notification->execute();
            $stmt_notification->bind_result($db_notification_name);
            $stmt_notification->fetch();
            $stmt_notification->close();
            if (!empty($db_notification_name)) {
              $notification_url = 'vistas/tramites/cuentas_rechazadas/ver_notificacion_rechazado.php?cod=' . urlencode($cod) . '&archivo=' . rawurlencode($db_notification_name);
            }
          }
        }
      }
      $row['notification_url'] = $notification_url;
      $rows[] = $row;
    }
    $result->data_seek(0);
  }
}
?>

<style>
  .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
    background-color: #002F55 !important;
    border-color: #002F55 !important;
    color: #fff !important;
  }

  .dataTables_wrapper .dataTables_paginate .page-link:hover {
    background-color: #457b9d !important;
    color: #fff !important;
  }

  .dataTables_wrapper .dataTables_paginate .page-link {
    color: #002f55 !important;
    border-radius: 8px;
    margin: 0 2px;
  }

  .iframe-pdf {
    width: 100%;
    min-height: 70vh;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
  }

  .section-title {
    color: #0F5699;
    font-weight: 600;
    /* border-bottom: 2px solid #D1DDD5; */
    padding-bottom: 0.5rem;
    margin-bottom: 1rem;
  }

  .details-table th {
    width: 35%;
    white-space: nowrap;
    background: #f9fafb;
  }

  .details-table td {
    word-break: break-word;
  }

  .pdf-panel {
    background: #FFFFFF;
    /* background: linear-gradient(202deg, rgba(10, 44, 27, 1) 75%, rgba(27, 64, 1, 1) 95%);; */
    border: none;
    border-radius: 18px;
    /* padding: 0.75rem; */
    height: 100%;
    display: flex;
    flex-direction: column;
  }

  .notification-section {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 8px;
    padding: 1rem;
    margin-top: 1rem;
  }

  .modal-altura {
    height: 80vh;
    margin-top: 5%;
  }

  .modal-altura .modal-content {
    height: 100%;
  }

  .modal-altura .modal-body {
    overflow-y: auto;
  }

  .modal-header .modal-title {
    color: #0F5699;
    font-weight: 600;
  }

  .badge-soft {
    background: #eef2ff;
    color: #374151;
    border: 1px solid #e5e7eb;
  }

  .btn-action {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
  }

  .nav-tabs .nav-link {
    color: #000000 !important;
  }

  .nav-tabs .nav-link:hover {
    color: #002F55 !important;
  }

  .nav-tabs .nav-link.active {
    color: #0A2C1B !important;
    font-weight: 600;
    border-bottom: 3px solid #0A2C1B !important;
    background-color: transparent !important;
  }

  .nav-tabs .nav-link i {
    color: inherit !important;
  }

  #evidenciaPreview iframe,
  #evidenciaPreview img,
  #evidenciaPreview .iframe-pdf {
    width: 100% !important;
    max-width: 100% !important;
    height: 85vh;
    border-radius: 10px;
  }

  .empty-state {
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px dashed #e5e7eb;
    border-radius: 12px;
    background: #f8fafc;
    padding: 1.25rem;
  }

  .rechazados-page {
    background-color: #EDEDED;
    color: #0A2C1B;
    min-height: 100%;
  }

  .rechazados-page-title {
    color: #0A2C1B !important;
    font-weight: 800 !important;
    letter-spacing: 0;
  }

  .rechazados-card {
    background: transparent;
    border: none;
    border-radius: 18px !important;
    /* box-shadow: 0 14px 30px rgba(10, 44, 27, 0.08) !important; */
    overflow: hidden;
  }

  .rechazados-card>.card-header,
  .rechazados-page .card-header {
    background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%) !important;
    border: 0 !important;
    color: #ffffff !important;
    padding: 0.95rem 1.15rem !important;
  }

  .rechazados-card>.card-header h6,
  .rechazados-page .card-header h6 {
    font-size: 0.85rem;
    font-weight: 700;
    letter-spacing: 0;
    text-transform: uppercase;
  }

  .rechazados-page .card-body {
    padding: 1.25rem;
    border: none !important;
  }

  .rechazados-detail-mini-card {
    background-color: #ffffff;
    border: none;
    border-radius: 18px !important;
    /* box-shadow: 0 12px 26px rgba(10, 44, 27, 0.07); */
    overflow: hidden;
  }

  .rechazados-detail-mini-card .section-title {
    margin-bottom: 1rem;
  }

  .rechazados-page #dataTable,
  .rechazados-page .details-table {
    border-collapse: separate !important;
    border-spacing: 0;
    color: #0A2C1B;
  }

  .rechazados-page #dataTable thead th {
    background-color: #EDEDED !important;
    border: 0 !important;
    color: #0A2C1B !important;
    font-size: 0.72rem;
    font-weight: 800;
    padding: 0.85rem 0.45rem;
    text-align: center;
    text-transform: uppercase;
    vertical-align: middle;
  }

  .rechazados-page #dataTable tbody td,
  .rechazados-page .details-table tbody td,
  .rechazados-page .details-table tbody th {
    border-left: 0 !important;
    border-right: 0 !important;
    border-top: none;
    color: #0A2C1B;
    font-size: 0.82rem;
    vertical-align: middle;
    text-align: center;
  }

  .rechazados-page .details-table tbody th {
    background-color: #FFFFFF !important;
    text-align: center;
    color: #0A2C1B;
    font-weight: 800;
  }

  .rechazados-page #dataTable tbody tr:hover td,
  .rechazados-page .details-table tbody tr:hover td,
  .rechazados-page .details-table tbody tr:hover th {
    background-color: #F6F8F7;
  }

  .rechazados-page #dataTable a {
    color: #0A5F5E;
    font-weight: 700;
    text-decoration: none;
  }

  .rechazados-page .dataTables_wrapper .dataTables_filter {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 1rem;
  }

  .rechazados-page .dataTables_wrapper .dataTables_filter input,
  .rechazados-page .dataTables_wrapper .dataTables_length select {
    border-radius: 14px !important;
  }

  .rechazados-page .dataTables_wrapper .dataTables_info {
    color: #7F8E85 !important;
    font-size: 0.82rem;
  }

  .rechazados-page .dataTables_wrapper .dataTables_paginate .pagination {
    gap: 0.35rem;
    justify-content: flex-end;
  }

  .rechazados-page .dataTables_wrapper .dataTables_paginate .page-link {
    align-items: center;
    background-color: #F6F8F7 !important;
    border: 1px solid transparent !important;
    border-radius: 18px !important;
    color: #0A2C1B !important;
    display: flex;
    font-size: 0.8rem;
    height: 31px;
    justify-content: center;
    min-width: 31px;
  }

  .rechazados-page .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
    background-color: #0A2C1B !important;
    border-color: #0A2C1B !important;
    color: #ffffff !important;
  }

  .rechazados-page .dataTables_wrapper .dataTables_paginate .page-link:hover {
    background-color: #C0D2C8 !important;
    border-color: #C0D2C8 !important;
    color: #0A2C1B !important;
  }

  .rechazados-page .section-title {
    border-bottom: 2px solid #7FAB64;
    color: #7FAB64;
    font-weight: 600;
  }

  .rechazados-page .btn,
  #notificationModal .btn {
    border-radius: 14px;
    font-weight: 700;
  }

  /* .rechazados-page .btn[style*="#002F55"],
  #notificationModal .btn[style*="#002F55"] {
    background-color: #0A2C1B !important;
    border-color: #0A2C1B !important;
  } */

  .rechazados-page .view-notification {
    background-color: #AEE136 !important;
    border-color: #AEE136 !important;
    color: #0A2C1B !important;
  }


  .rechazados-page .card-documentos,
  #notificationModal .card-documentos {
    border: none !important;
    border-radius: 18px !important;
    /* box-shadow: 0 12px 26px rgba(10, 44, 27, 0.08) !important; */
  }

  .rechazados-notificaciones-card {
    background-color: #ffffff;
    /* max-width: 900px; */
    overflow: hidden;
    padding: 0 !important;
    width: 100%;
  }

  .rechazados-notificaciones-header {
    align-items: center;
    background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%) !important;
    color: #ffffff;
    display: flex;
    font-size: 1rem;
    font-weight: 800;
    gap: 0.5rem;
    justify-content: center;
    margin: 0 !important;
    padding: 0.95rem 1rem !important;
    text-align: center;
  }

  .rechazados-notificaciones-header .badge {
    background-color: #AEE136 !important;
    border-radius: 999px;
    color: #0A2C1B !important;
    font-weight: 800;
  }

  .rechazados-notificaciones-body {
    padding: 1.25rem;
  }

  .rechazados-notificaciones-title {
    color: #0A2C1B !important;
    display: inline-flex;
    font-weight: 800;
    margin-bottom: 1rem;
  }

  .rechazados-notificacion-item {
    background-color: #F6F8F7 !important;
    border: 1px solid #C0D2C8 !important;
    border-radius: 14px !important;
    text-align: left;
  }

  .rechazados-notificacion-empty {
    background-color: rgba(174, 225, 54, 0.16) !important;
    border: 1px solid rgba(174, 225, 54, 0.5);
    border-radius: 14px;
    color: #0A2C1B;
    font-weight: 700;
    margin: 0 auto;
    width: fit-content;
  }

  .rechazados-notificacion-upload {
    border-top: 1px solid #E6ECE8;
    /* margin-top: 1.25rem; */
    padding-top: 1.25rem;
  }

  .rechazados-notificacion-upload .input-group {
    border: 1px solid #C0D2C8;
    border-radius: 14px !important;
    box-shadow: none !important;
    height: 46px;
    max-width: 620px;
    overflow: hidden;
    width: 100% !important;
  }

  .rechazados-notificacion-upload .input-group-text {
    background-color: #F6F8F7 !important;
    border: 0 !important;
  }

  .rechazados-notificacion-upload .input-group-text i {
    color: #0A2C1B !important;
  }

  .rechazados-notificacion-upload .form-control {
    border: 0 !important;
    color: #0A2C1B;
  }

  .rechazados-notificacion-upload .btn-success {
    background-color: #0A2C1B !important;
    border-color: #0A2C1B !important;
    border-radius: 14px;
    color: #ffffff !important;
    font-weight: 800;
    padding: 0.55rem 1rem;
  }

  .rechazados-page .iframe-pdf,
  #notificationModal iframe {
    border: 1px solid #C0D2C8 !important;
    border-radius: 14px !important;
  }

  #notificationModal .modal-content {
    border: none;
    border-radius: 20px;
    overflow: hidden;
  }

  #notificationModal .modal-header {
    background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%) !important;
    border: 0;
  }

  #notificationModal .modal-body,
  #notificationModal .modal-footer {
    background-color: #F6F8F7;
  }

  #notificationModal .nav-tabs .nav-link {
    border-radius: 11px 11px 0 0;
    color: #0A2C1B;
    font-weight: 700;
  }

  #notificationModal .nav-tabs .nav-link.active {
    background-color: #0A2C1B;
    border-color: #D1DDD5;
    color: #ffffff;
  }

  .btn_ver {
    background-color: #0A2C1B !important;
    border-color: #0A2C1B !important;
    transition: transform 0.22s ease, box-shadow 0.22s ease;
    color: white;
  }

  .btn_ver:hover {
    background-color: #000000 !important;
    transform: scale(1.03);
  }

  #notificationModal .btn-cerrar-rechazado,
  #notificationModal .btn-subir-evidencia {
    background-color: #0A2C1B !important;
    border-color: #0A2C1B !important;
    color: #ffffff !important;
  }

  #notificationCod {
    color: #E0DA93;
    border: 1px dotted #E0DA93;
    border-radius: 10px;
  }
</style>

<!-- DataTables CSS con integración Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

<!-- Contenido -->
<div class="container-fluid rounded-4 p-3 rechazados-page">
  <div class="d-sm-flex align-items-center justify-content-between my-4 mt-2">
    <h3 class="mb-0 fw-bold rechazados-page-title my-3">
      <?php echo ($action === 'ver' && $row_detalle) ? 'DETALLE DEL TRÁMITE RECHAZADO' : 'TRÁMITES RECHAZADOS'; ?>
    </h3>
  </div>

  <?php if (!empty($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show mt-3" id="alert-msg">
      <i class="bi bi-check-circle"></i>
      <?php echo htmlspecialchars(str_replace('success:', '', $_GET['msg'])); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const alertBox = document.getElementById('alert-msg');
        setTimeout(() => {
          if (alertBox) {
            alertBox.classList.remove('show');
            alertBox.classList.add('fade');
            setTimeout(() => alertBox.remove(), 500);
          }
        }, 3000);
        if (window.history.replaceState) {
          const url = new URL(window.location);
          url.searchParams.delete('msg');
          window.history.replaceState({}, document.title, url.pathname + url.search);
        }
      });
    </script>
  <?php endif; ?>

  <?php if ($action === 'ver' && $row_detalle): ?>
    <div class="card rechazados-card mb-4 border-none">
      <div class="card-header py-4 d-flex flex-row align-items-center justify-content-between rounded-4" style="background-color: #002F55;">
        <!-- <h6 class="m-0 font-weight-bold text-white">Detalle del Trámite: <?php echo htmlspecialchars($row_detalle['cod_tramite']); ?></h6> -->
        <div class="text-xs text-white text-uppercase my-2" style="font-size:1rem; font-weight:200;">
          Código del Trámite: <br>
          <small style="font-weight:600; font: size 1rem;"><?php echo htmlspecialchars($row_detalle['cod_tramite']); ?></small>
        </div>
        <a href="?page=tramites/cuentas_rechazadas/rechazados" class="btn btn-sm btn-light px-3 py-2">
          <i class="bi bi-arrow-left me-2"></i> Volver a la lista
        </a>
      </div>

      <div class="card-body px-1">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-5 ps-lg-0 ">
              <div class="card p-3 rechazados-detail-mini-card">
                <h6 class="section-title px-2">Información del Trámite</h6>
                <div class="table-responsive">
                  <table class="table table-bordered details-table">
                    <tbody>
                      <tr>
                        <th scope="row" style="border-top-left-radius: 14px;">Código</th>
                        <td style="border-top-right-radius:14px;"><?php echo htmlspecialchars($row_detalle['cod_tramite'] ?? ''); ?></td>
                      </tr>
                      <tr>
                        <th scope="row">Mutación</th>
                        <td><?php echo htmlspecialchars($row_detalle['mutacion_tramite'] ?? ''); ?></td>
                      </tr>
                      <tr>
                        <th scope="row">Fecha Radicación</th>
                        <td><?php echo htmlspecialchars($row_detalle['fecha_rad'] ?? ''); ?></td>
                      </tr>
                      <tr>
                        <th scope="row">Fecha Límite Respuesta</th>
                        <td><?php echo htmlspecialchars($row_detalle['fecha_limite_respuesta'] ?? ''); ?></td>
                      </tr>
                      <tr>
                        <th scope="row" style="border-bottom-left-radius: 14px;">Tipo Solicitante</th>
                        <td style="border-bottom-right-radius:14px;"><?php echo htmlspecialchars($row_detalle['tsolicitante_tramite'] ?? ''); ?></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>


              <div class="card p-3 mt-3 rechazados-detail-mini-card">
                <h6 class="section-title px-2">Interesado</h6>
                <div class="table-responsive">
                  <table class="table table-bordered details-table">
                    <tbody>
                      <tr>
                        <th scope="row" style="border-top-left-radius: 14px;">Nombres</th>
                        <td style="border-top-right-radius:14px;">
                          <?php
                          $nombres = [
                            $row_detalle['primer_nombre_interesado'] ?? '',
                            $row_detalle['segundo_nombre_interesado'] ?? ''
                          ];
                          echo htmlspecialchars(implode(' ', array_filter($nombres)));
                          ?>
                        </td>
                      </tr>
                      <tr>
                        <th scope="row">Apellidos</th>
                        <td>
                          <?php
                          $apellidos = [
                            $row_detalle['primer_apellido_interesado'] ?? '',
                            $row_detalle['segundo_apellido_interesado'] ?? ''
                          ];
                          echo htmlspecialchars(implode(' ', array_filter($apellidos)));
                          ?>
                        </td>
                      </tr>
                      <tr>
                        <th scope="row">Documento</th>
                        <td>
                          <?php
                          echo htmlspecialchars(($row_detalle['documento_interesado'] ?? '') . ' ' . ($row_detalle['num_doc_interesado'] ?? ''));
                          ?>
                        </td>
                      </tr>
                      <tr>
                        <th scope="row">Teléfono</th>
                        <td><?php echo htmlspecialchars($row_detalle['telefono_interesado'] ?? ''); ?></td>
                      </tr>
                      <tr>
                        <th scope="row" style="border-bottom-left-radius: 14px;">Correo</th>
                        <td style="border-bottom-right-radius:14px;"><?php echo htmlspecialchars($row_detalle['correo_interesado'] ?? ''); ?></td>
                      </tr>
                    </tbody>
                  </table>
                </div>

              </div>

              <div class="card p-3 mt-3 rechazados-detail-mini-card">
                <h6 class="section-title px-2">Propietario</h6>
                <div class="table-responsive">
                  <table class="table table-bordered details-table">
                    <tbody>
                      <tr>
                        <th scope="row" style="border-top-left-radius: 14px;">Nombre</th>
                        <td style="border-top-right-radius:14px;"><?php echo htmlspecialchars($row_detalle['nombre_propietario_tram'] ?? ''); ?></td>
                      </tr>
                      <tr>
                        <th scope="row">Tipo Documento</th>
                        <td><?php echo htmlspecialchars($row_detalle['tipo_doc_propietario_tram'] ?? ''); ?></td>
                      </tr>
                      <tr>
                        <th scope="row" style="border-bottom-left-radius: 14px;">N° Documento</th>
                        <td style="border-bottom-right-radius:14px;"><?php echo htmlspecialchars($row_detalle['cedula_propietario_tram'] ?? ''); ?></td>
                      </tr>
                    </tbody>
                  </table>
                </div>

              </div>

              <div class="card p-3 mt-3 rechazados-detail-mini-card">
                <h6 class="section-title px-2">Predio</h6>
                <div class="table-responsive">
                  <table class="table table-bordered details-table">
                    <tbody>
                      <tr>
                        <th scope="row" style="border-top-left-radius: 14px;">NPN</th>
                        <td style="border-top-right-radius:14px;"><?php echo htmlspecialchars($row_detalle['npn_predio'] ?? ''); ?></td>
                      </tr>
                      <tr>
                        <th scope="row">Dirección</th>
                        <td><?php echo htmlspecialchars($row_detalle['direccion_predio_terreno_tram'] ?? ''); ?></td>
                      </tr>
                      <tr>
                        <th scope="row">FMI (Trámite)</th>
                        <td><?php echo htmlspecialchars($row_detalle['fmi_predio_tram'] ?? ''); ?></td>
                      </tr>
                      <tr>
                        <th scope="row">FMI (Final)</th>
                        <td><?php echo htmlspecialchars($row_detalle['fmi_predio'] ?? ''); ?></td>
                      </tr>
                      <tr>
                        <th scope="row">Destino Económico</th>
                        <td><?php echo htmlspecialchars($row_detalle['destino_econ_predio_tram'] ?? ''); ?></td>
                      </tr>
                      <tr>
                        <th scope="row">Área Terreno (m²)</th>
                        <td><?php echo htmlspecialchars($row_detalle['area_terr_predio_tram'] ?? ''); ?></td>
                      </tr>
                      <tr>
                        <th scope="row">Área Construida (m²)</th>
                        <td><?php echo htmlspecialchars($row_detalle['area_cons_predio_tram'] ?? ''); ?></td>
                      </tr>
                      <tr>
                        <th scope="row" style="border-bottom-left-radius: 14px;">Avalúo Terreno</th>
                        <td style="border-bottom-right-radius:14px;"><?php echo htmlspecialchars($row_detalle['valor_avaluo_terreno_tram'] ?? ''); ?></td>
                      </tr>
                    </tbody>
                  </table>
                </div>

              </div>

            </div>

            <!-- Columna PDF principal -->
            <div class="col-lg-7 px-0 mt-3 mt-lg-0">
              <div class="pdf-panel p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <h6 class="fw-bold mb-0" style="color: #0A2C1B;">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Documento Generado
                  </h6>
                  <?php
                  $pdfUrl = 'vistas/tramites/cuentas_rechazadas/ver_pdf_rechazado.php?doc_cod=' . urlencode($row_detalle['cod_tramite']);
                  ?>
                  <a href="<?php echo $pdfUrl; ?>" target="_blank" class="btn text-white btn_ver btn-sm px-3 rounded-3" style="background-color: #002F55;">
                    <i class="bi bi-box-arrow-up-right me-2"></i> Abrir en pestaña
                  </a>
                </div>
                <iframe class="iframe-pdf" style="height: 120%;" src="<?php echo $pdfUrl; ?>#toolbar=1&navpanes=0&scrollbar=1" title="Documento generado"></iframe>
                <div class="text-muted small mt-4">
                  <i class="bi bi-info-circle"></i> Si el documento no carga correctamente, puede abrirlo en una nueva pestaña.
                </div>
              </div>
            </div>

            <div class="col-12 col-md-9 mx-auto px-0">
              <div class="card p-3 mt-3 rechazados-detail-mini-card">
                <h6 class="section-title px-2 ">Observación</h6>
                <div class="alert alert-light border-none">
                  <?php echo htmlspecialchars($row_detalle['observacion_tramite'] ?? 'Sin observaciones'); ?>
                </div>
              </div>
            </div>

          </div>
        </div>

        <hr class="my-5" style="border: none; border-bottom:4px dotted #0A5F5E">
        <!-- SECCIÓN DE NOTIFICACIONES -->
        <div class="col-12 col-lg-10 mt-2  mx-auto">
          <div class="card card-documentos  shadow h-100 w-100 d-flex flex-column text-center my-4 rechazados-notificaciones-card">
            <div class="rechazados-notificaciones-header">
              <i class="bi bi-bell"></i> Notificaciones del Trámite
              <?php if ($has_notification): ?>
                <?php
                $cantidad = count($notificaciones);
                $texto = ($cantidad === 1) ? 'archivo' : 'archivos';
                ?>
                <span class="badge ms-auto px-3 rounded-2">
                  <?php echo $cantidad . ' ' . $texto; ?>
                </span>
              <?php endif; ?>
            </div>

            <div class="rechazados-notificaciones-body">
              <?php if ($has_notification): ?>
                <div class="mb-2">
                  <span class="form-label fw-bold rechazados-notificaciones-title" style="color: #002f55;">Notificaciones Existentes</span>
                  <?php foreach ($notificaciones as $notification): ?>
                    <div class="d-flex justify-content-between align-items-center p-3 border rounded mb-1 bg-light rechazados-notificacion-item">
                      <div class="flex-grow-1">
                        <i class="bi bi-file-earmark-pdf text-danger me-0"></i>
                        <strong><?php echo htmlspecialchars($notification['name']); ?></strong>
                        <br>
                        <small class="text-muted">
                          <?php if (isset($notification['subdir'])): ?>
                            <span class="badge rounded-3 mt-2" style="background-color: #A0C882; color: #0A2C1B; ">
                              <?php echo htmlspecialchars($notification['subdir']); ?>
                            </span>
                          <?php endif; ?>
                        </small>
                      </div>
                      <a href="<?php echo htmlspecialchars($notification['url']); ?>" target="_blank" class="btn_ver btn btn-sm text-white p-2 px-3">
                        <i class="bi bi-box-arrow-up-right me-2"></i> Ver
                      </a>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div class="alert rechazados-notificacion-empty" style="background-color: #f8000015; width: fit-content; margin: auto;">
                  <i class="bi bi-exclamation-triangle"></i> No hay notificaciones cargadas.
                </div>
              <?php endif; ?>

              <br>

              <!-- FORMULARIO DE CARGA DE NUEVA NOTIFICACIÓN -->
              <form class="rechazados-notificacion-upload" action="vistas/tramites/acciones/cargar_notificacion_rechazado.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="cod_tramite" value="<?php echo htmlspecialchars($row_detalle['cod_tramite']); ?>">

                <div class="form-group">
                  <div class="input-group"
                    style="width: 70%; margin: auto; border-radius: 12px;
                  overflow: hidden; box-shadow: 0 0 4px rgba(0, 0, 0, 0.1); height: 46px;">
                    <span class="input-group-text bg-white"
                      style="border-right: none; display: flex; align-items: center; border-radius: 12px 0 0 12px;">
                      <i class="bi bi-file-earmark-text-fill" style="color:#002f55; font-size: 1.1rem;"></i>
                    </span>

                    <label for="archivo"
                      class="form-control text-start d-flex align-items-center"
                      style="cursor: pointer; border-left: none; border-radius: 0 12px 12px 0; height: 100%;">
                      <span id="archivo-nombre" class="text-muted">Ningún archivo seleccionado</span>
                    </label>

                    <input type="file" id="archivo" name="archivo" accept="application/pdf" required style="display: none;">
                  </div>

                  <small class="form-text text-muted">Solo PDF</small>
                </div>

                <button type="submit" class="btn btn-success mt-2" style="background-color: #002F55;">
                  <i class="bi bi-file-earmark-arrow-up me-2"></i>
                  <?php echo $has_notification ? 'Subir Otra Notificación' : 'Subir Notificación'; ?>
                </button>

                <script>
                  document.getElementById('archivo').addEventListener('change', function(e) {
                    const fileName = e.target.files[0] ? e.target.files[0].name : 'Ningún archivo seleccionado';
                    document.getElementById('archivo-nombre').textContent = fileName;
                  });
                </script>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

  <?php else: ?>
    <div class="card rechazados-card mb-4">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between" style="background-color: #002F55;">
        <!-- <h6 class="m-0 font-weight-bold text-white">Listado de Trámites </h6> -->
        <div class="text-xs text-white text-uppercase my-2 " style="font-size:1rem; font-weight:500;">
          Listado de trámites</div>
        <div class="dropdown no-arrow">
          <a class="dropdown-toggle text-white" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-ellipsis-v fa-sm fa-fw"></i>
          </a>
        </div>
      </div>
      <div class="card-body" style="background-color: #FFFFFF;">
        <div class="table-responsive">
          <table class="table" id="dataTable" width="100%" cellspacing="0">
            <thead>
              <tr>
                <th style="text-align: center; vertical-align: middle; border-top-left-radius: 12px; border-bottom-left-radius:12px;">ID Trámite</th>
                <th style="text-align: center; vertical-align: middle;">Nombre Completo</th>
                <th style="text-align: center; vertical-align: middle;">Tipo Doc</th>
                <th style="text-align: center; vertical-align: middle;">Doc Interesado</th>
                <th style="text-align: center; vertical-align: middle;">NPN Predio</th>
                <th style="text-align: center; vertical-align: middle;">Dirección</th>
                <th style="text-align: center; vertical-align: middle;">Notificación</th>
                <th style="text-align: center; vertical-align: middle; border-top-right-radius: 12px; border-bottom-right-radius:12px;">Acción</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($result && $result->num_rows > 0): ?>
                <?php foreach ($rows as $row): ?>
                  <tr>
                    <td style="text-align: center; vertical-align: middle;"><?php echo htmlspecialchars($row['cod_tramite'] ?? ''); ?></td>
                    <td style="text-align: center; vertical-align: middle;">
                      <?php echo htmlspecialchars(trim(
                        ($row['primer_nombre_interesado'] ?? '') . ' ' .
                          ($row['segundo_nombre_interesado'] ?? '') . ' ' .
                          ($row['primer_apellido_interesado'] ?? '') . ' ' .
                          ($row['segundo_apellido_interesado'] ?? '')
                      )); ?>
                    </td>
                    <td style="text-align: center; vertical-align: middle;"><?php echo htmlspecialchars($row['documento_interesado'] ?? ''); ?></td>
                    <td style="text-align: center; vertical-align: middle;"><?php echo htmlspecialchars($row['num_doc_interesado'] ?? ''); ?></td>
                    <td style="text-align: center; vertical-align: middle;"><?php echo htmlspecialchars($row['npn_predio'] ?? ''); ?></td>
                    <td style="text-align: center; vertical-align: middle;"><?php echo htmlspecialchars($row['direccion_predio_terreno_tram'] ?? ''); ?></td>
                    <td style="text-align: center; vertical-align: middle;">
                      <?php if (!empty($row['notification_url'])): ?>
                        <button type="button" class="btn btn-sm btn-action view-notification"
                          data-cod="<?php echo htmlspecialchars($row['cod_tramite']); ?>"
                          data-url="<?php echo htmlspecialchars($row['notification_url']); ?>"
                          style="background-color: #ffc107; color: black;"
                          title="Ver Notificación">
                          <i class="bi bi-bell"></i> Notificación
                        </button>
                      <?php else: ?>
                        <span class="text-muted fst-italic small">-</span>
                      <?php endif; ?>
                    </td>

                    <td style="text-align: center; vertical-align: middle;">
                      <a href="?page=tramites/cuentas_rechazadas/rechazados&action=ver&cod=<?php echo urlencode($row['cod_tramite'] ?? ''); ?>"
                        class="btn btn-sm text-white px-3 btn_ver" style="background-color: #002F55;" title="Ver detalle">
                        <i class="bi bi-eye"></i>
                      </a>
                    </td>

                  </tr>
                <?php endforeach; ?>
              <?php else: ?>

              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<!-- Modal para Vista Previa de Notificación -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-altura">
    <div class="modal-content">
      <div class="modal-header" style="background-color: #002F55;">
        <h5 class="modal-title text-white" id="notificationModalLabel">
          <i class="bi bi-bell"></i> Gestión del Trámite - <span id="notificationCod"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <ul class="nav nav-tabs" id="documentTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="notificacion-tab" data-bs-toggle="tab" data-bs-target="#notificacion" type="button" role="tab" aria-controls="notificacion" aria-selected="true">
              <i class="bi bi-bell"></i> Notificación
            </button>
          </li>
          <?php if ($rol_usuario !== 'director_catastro'): ?>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="evidencia-tab" data-bs-toggle="tab" data-bs-target="#evidencia" type="button" role="tab" aria-controls="evidencia" aria-selected="false">
                <i class="bi bi-folder-check"></i> Evidencia
              </button>
            </li>
          <?php endif; ?>
        </ul>

        <div class="tab-content mt-3" id="documentTabsContent">
          <div class="tab-pane fade show active" id="notificacion" role="tabpanel" aria-labelledby="notificacion-tab">

            <div class="alert alert-info mx-auto" style="width: 90%;">
              <i class="bi bi-info-circle"></i> Visualizando notificación de:
              <strong id="notificationCodTab"></strong>
            </div>

            <div class="card card-documentos mx-auto shadow h-100 p-3 border d-flex flex-column text-center my-4"
              style="width: 90%; border-radius: 12px;">
              <iframe id="notificationIframe"
                src=""
                style="display:block; width:100%; height:85vh; border:none; border-radius:10px;">
              </iframe>
            </div>

            <div class="mt-3 mx-auto text-center" style="width: 70%; max-width: 600px;">
              <a id="openNewTab" href="#" class="bot_verenotrapesta btn btn-sm btn-outline-primary" target="_blank">
                <i class="bi bi-box-arrow-up-right"></i> Abrir en nueva pestaña
              </a>
            </div>
          </div>

          <!-- TAB 2: EVIDENCIA -->
          <?php if ($rol_usuario !== 'director_catastro'): ?>
            <div class="tab-pane fade" id="evidencia" role="tabpanel" aria-labelledby="evidencia-tab">

              <div class="alert alert-info mx-auto" style="width: 90%;">
                <i class="bi bi-folder-check"></i> Subir evidencia para el trámite:
                <strong id="evidenciaCodTab"></strong>
              </div>

              <div class="card card-documentos mx-auto shadow h-100 p-3 border d-flex flex-column text-center my-4"
                style="width: 90%; border-radius: 12px;">
                <form action="vistas/tramites/acciones/cargar_evidencia_rechazado.php" method="post" enctype="multipart/form-data">
                  <input type="hidden" name="cod_tramite" id="evidenciaCodInput" value="">

                  <div class="form-group mb-3">
                    <label class="fw-bold">Seleccionar Evidencia:</label>
                    <div class="input-group"
                      style="width: 70%; margin: auto; border-radius: 12px;
                        overflow: hidden; box-shadow: 0 0 4px rgba(0, 0, 0, 0.1); height: 46px;">

                      <span class="input-group-text bg-white"
                        style="border-right: none; display: flex; align-items: center; border-radius: 12px 0 0 12px;">
                        <i class="bi bi-file-earmark-text-fill" style="color:#002f55; font-size: 1.1rem;"></i>
                      </span>

                      <label for="evidencia-archivo"
                        class="form-control text-start d-flex align-items-center"
                        style="cursor: pointer; border-left: none; border-radius: 0 12px 12px 0; height: 100%;">
                        <span id="evidencia-nombre" class="text-muted">Ningún archivo seleccionado</span>
                      </label>

                      <input type="file" id="evidencia-archivo" name="archivo" accept="application/pdf,image/*" required style="display: none;">
                    </div>
                    <small class="form-text text-muted">PDF</small>
                  </div>

                  <button type="submit" class="btn btn-sm btn-subir-evidencia">
                    <i class="bi bi-cloud-upload"></i> Subir Evidencia
                  </button>

                  <script>
                    document.getElementById('evidencia-archivo').addEventListener('change', function(e) {
                      const fileName = e.target.files[0] ? e.target.files[0].name : 'Ningún archivo seleccionado';
                      document.getElementById('evidencia-nombre').textContent = fileName;
                    });
                  </script>
                </form>
              </div>

              <div id="evidenciaPreview"
                class="card card-documentos mx-auto shadow h-100 p-3 border d-flex flex-column text-center my-4"
                style="display: none; width: 100%; border-radius: 12px;">
                <h6 class="fw-bold" style="color:#002F55;">
                  <i class="bi bi-eye"></i> Evidencia existente
                </h6>
                <div id="evidenciaContent"></div>
                <div class="mt-3 mx-auto text-center" style="width: 70%; max-width: 600px;">
                  <a id="openEvidenciaTab" href="#" class="bot_verenotrapesta btn btn-sm btn-outline-primary" target="_blank">
                    <i class="bi bi-box-arrow-up-right"></i> Abrir en nueva pestaña
                  </a>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-cerrar-rechazado" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- jQuery primero -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<script>
  $(document).ready(function() {
    var table = $('#dataTable').DataTable({
      language: window.neivaDataTablesLanguage ? window.neivaDataTablesLanguage() : {},
      pageLength: 25,
      order: [
        [0, 'desc']
      ],
      responsive: true,
      searching: true,
      lengthChange: true,
      info: true,
      autoWidth: false
    });

    $(document).on('click', '.view-notification', function(e) {
      e.preventDefault();

      const cod = $(this).data('cod');
      const url = $(this).data('url');

      $('#notificationCod').text(cod);
      $('#notificationCodTab').text(cod);
      $('#evidenciaCodTab').text(cod);
      $('#evidenciaCodInput').val(cod);

      const pdfUrl = url + '#toolbar=1&navpanes=0&scrollbar=1';
      $('#notificationIframe').attr('src', pdfUrl);
      $('#downloadNotification').attr('href', url);
      $('#openNewTab').attr('href', url);

      cargarEvidencia(cod);
      $('#notificationModal').modal('show');
    });

    function cargarEvidencia(cod) {
      $.ajax({
        url: 'vistas/tramites/acciones/listar_evidencia_rechazado.php',
        type: 'GET',
        data: {
          cod_tramite: cod
        },
        dataType: 'json',
        success: function(data) {
          const preview = $('#evidenciaPreview');
          const cont = $('#evidenciaContent');
          const btnOpen = $('#openEvidenciaTab');

          cont.empty();

          if (data && data.length > 0) {
            preview.show();
            btnOpen.removeClass('d-none').attr('href', data[0].url);

            data.forEach(file => {
              const ext = (file.name || '').split('.').pop().toLowerCase();
              let viewer = '';

              if (ext === 'pdf') {
                viewer = `
                  <iframe src="${file.url}#toolbar=1&navpanes=0&scrollbar=1"
                          class="iframe-pdf mb-3"
                          style="display:block;margin:0 auto;height:70vh;width:100%;border:1px solid #dee2e6;border-radius:8px;"></iframe>
                `;
              } else {
                viewer = `
                  <img src="${file.url}"
                       class="img-fluid rounded border mb-3"
                       alt="Evidencia"
                       style="max-height:400px;display:block;margin:0 auto;">
                `;
              }

              cont.append(viewer);
            });
          } else {
            preview.show();
            btnOpen.addClass('d-none').attr('href', '#');
            cont.html(`
              <div class="empty-state text-center w-100">
                <div>
                  <i class="bi bi-folder-x" style="font-size: 2rem;"></i>
                  <p class="mt-2 mb-0 fw-semibold">No hay evidencia cargada.</p>
                  <small class="text-muted">Usa el formulario superior para subir un archivo.</small>
                </div>
              </div>
            `);
          }
        },
        error: function(xhr, status, error) {
          console.error('Error cargando evidencia:', error);
          const preview = $('#evidenciaPreview');
          const cont = $('#evidenciaContent');
          const btnOpen = $('#openEvidenciaTab');

          preview.show();
          btnOpen.addClass('d-none').attr('href', '#');
          cont.html(`
            <div class="empty-state text-center w-100">
              <div>
                <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                <p class="mt-2 mb-0 fw-semibold">No se pudo cargar la evidencia.</p>
                <small class="text-muted">Intenta nuevamente más tarde.</small>
              </div>
            </div>
          `);
        }
      });
    }

    // Limpiar al cerrar modal
    $('#notificationModal').on('hidden.bs.modal', function() {
      $('#notificationIframe').attr('src', '');
      $('#evidenciaContent').empty();
      $('#evidenciaPreview').hide();
      $('#openEvidenciaTab').addClass('d-none').attr('href', '#');
    });

    $('#notificationModal').on('show.bs.modal', function() {
      $('#notificacion-tab').tab('show');
    });
  });
</script>

<script>
  $(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const codEvid = urlParams.get('cod_evid');
    if (codEvid) {
      localStorage.setItem('evidencia_rechazado_' + codEvid, 'true');
    }

    $('.view-notification').each(function() {
      const cod = $(this).data('cod');
      if (localStorage.getItem('evidencia_rechazado_' + cod) === 'true') {
        $(this).css('background-color', '#002f55');
        $(this).removeClass('btn-info').addClass('text-white');
      } else {
        $(this).css('background-color', '#0070cc6e');
        $(this).removeClass('btn-info').addClass('text-dark');
      }
    });
  });
</script>


<?php
if (isset($mysqli)) $mysqli->close();
?>
