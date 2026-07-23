<?php
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
$rol_usuario                  = $_SESSION['rol_usuario'] ?? '';
$nombre_usuario               = $_SESSION['nombre_usuario'] ?? 'Usuario';
$apellido_usuario             = $_SESSION['apellido_usuario'] ?? '';
$cedula_usuario               = $_SESSION['cedula_usuario'] ?? '';
$roles_pueden_ver_no_procede = ['ventanilla_catastral', 'procedencia_juridica', 'director_catastro', 'administrador', 'soporte'];
$roles_pueden_completar_no_procede = ['ventanilla_catastral', 'director_catastro', 'administrador'];
$roles_permitidos_rechazados  = $roles_pueden_ver_no_procede;
$tiene_permiso_rechazados = !empty($rol_usuario) && in_array($rol_usuario, $roles_permitidos_rechazados);
$appBase = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/neiva/Arbimaps/index.php')), '/');


$upload_dir = '../../Uploads/documentos_tramites/';
if (!file_exists($upload_dir)) {
  mkdir($upload_dir, 0777, true);
}


// Función para obtener URL del documento y estado de notificación
function getDocumentUrl($mysqli, $cod)
{
  $anio = substr($cod, 4, 4);

  $base_path = realpath(__DIR__ . '/../../../../');
  if ($base_path === false) {
    return ['success' => false, 'msg' => 'No se pudo resolver la ruta base'];
  }

  $tramite_dir = $base_path . neiva_app_url('Arbimaps/vistas/tramites/tramites_conservacion/no_procede_completar/') . $anio . '/' . $cod;
  if (!is_dir($tramite_dir)) {
    return ['success' => false, 'msg' => 'Directorio del trámite no encontrado'];
  }

  $base_filename = "no_procede_completar_{$cod}.pdf";
  $base_path = $tramite_dir . '/' . $base_filename;

  if (!file_exists($base_path)) {
    $pattern = $tramite_dir . "/no_procede_completar_*{$cod}*.pdf";
    $matches = glob($pattern);
    if (!empty($matches)) {
      $base_path = $matches[0];
      $base_filename = basename($base_path);
    }
  }

  $filename = $base_filename;

  // VERIFICAR NOTIFICACIONES
  $notificaciones_dir = $tramite_dir . '/notificaciones';
  $notificaciones = [];
  $has_notification = false;
  $appBaseLocal = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/neiva/Arbimaps/index.php')), '/');

  if (is_dir($notificaciones_dir)) {
    $notification_files = glob($notificaciones_dir . "/*.pdf");
    foreach ($notification_files as $file) {
      $notificaciones[] = [
        'name' => basename($file),
        'url' => 'vistas/tramites/cuentas_rechazadas/ver_notificacion_no_procede_completar.php?cod=' . urlencode($cod) . '&archivo=' . rawurlencode(basename($file)),
        'date' => date('Y-m-d H:i:s', filemtime($file))
      ];
    }
    $has_notification = !empty($notificaciones);
  }

  // Verificar también en la base de datos
  $sql_notification = "SELECT notificacion FROM no_procede_completar WHERE cod_radicacion_tramite = ?";
  $stmt_notification = $mysqli->prepare($sql_notification);
  $has_db_notification = false;

  if ($stmt_notification) {
    $stmt_notification->bind_param("s", $cod);
    $stmt_notification->execute();
    $result_notification = $stmt_notification->get_result();
    if ($row = $result_notification->fetch_assoc()) {
      $has_db_notification = !empty($row['notificacion']);
    }
    $stmt_notification->close();
  }

  $final_has_notification = $has_notification || $has_db_notification;

  if (!file_exists($base_path)) {
    return [
      'success' => false,
      'msg' => 'Documento no encontrado',
      'has_notification' => $final_has_notification,
      'notifications' => $notificaciones
    ];
  }

  // Construir URL relativa incluyendo cons_neiva
  $url_rel = $appBaseLocal . '/vistas/tramites/tramites_conservacion/no_procede_completar/' . $anio . '/' . $cod;
  $url_rel .= '/' . rawurlencode($filename);

  return [
    'success' => true,
    'url' => $url_rel,
    'filename' => $filename,
    'has_notification' => $final_has_notification,
    'notifications' => $notificaciones,
    'fs_path' => $base_path
  ];
}

function getDocumentUrlSeguro($mysqli, $cod)
{
  $anio = substr($cod, 4, 4);
  $script_dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/neiva/Arbimaps/index.php')), '/');
  $base_path = realpath(__DIR__ . '/../../../../');
  $roots = [];

  if ($base_path !== false) {
    $roots[] = [
      'fs' => $base_path . '/Arbimaps',
      'web' => $script_dir,
    ];

    $htdocs = dirname($base_path);
    $roots[] = [
      'fs' => $htdocs . neiva_app_url('Arbimaps/Arbimaps'),
      'web' => neiva_app_url('Arbimaps/Arbimaps'),
    ];
  }

  $notificaciones = [];
  $has_notification = false;
  $pdf_url = '';
  $pdf_path = '';

  foreach ($roots as $root) {
    $tramite_dir = $root['fs'] . '/vistas/tramites/tramites_conservacion/no_procede_completar/' . $anio . '/' . $cod;
    if (!is_dir($tramite_dir)) {
      continue;
    }

    $notificaciones_dir = $tramite_dir . '/notificaciones';
    if (is_dir($notificaciones_dir)) {
      foreach (glob($notificaciones_dir . '/*.pdf') ?: [] as $file) {
        $notificaciones[] = [
          'name' => basename($file),
          'url' => 'vistas/tramites/cuentas_rechazadas/ver_notificacion_no_procede_completar.php?cod=' . urlencode($cod) . '&archivo=' . rawurlencode(basename($file)),
          'date' => date('Y-m-d H:i:s', filemtime($file)),
        ];
      }
      $has_notification = $has_notification || !empty($notificaciones);
    }

    $candidatos = [$tramite_dir . "/no_procede_completar_{$cod}.pdf"];
    foreach (glob($tramite_dir . "/no_procede_completar_*{$cod}*.pdf") ?: [] as $match) {
      $candidatos[] = $match;
    }

    foreach ($candidatos as $candidato) {
      if (is_file($candidato)) {
        $pdf_path = $candidato;
        $pdf_url = $root['web'] . '/vistas/tramites/tramites_conservacion/no_procede_completar/' . $anio . '/' . $cod . '/' . rawurlencode(basename($candidato));
        break 2;
      }
    }
  }

  $has_db_notification = false;
  $has_db_document = false;
  $stmt = $mysqli->prepare("SELECT LENGTH(notificacion) AS len_notificacion, LENGTH(documento_generado) AS len_documento FROM no_procede_completar WHERE cod_radicacion_tramite = ?");
  if ($stmt) {
    $stmt->bind_param("s", $cod);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
      $has_db_notification = (int)($row['len_notificacion'] ?? 0) > 0;
      $has_db_document = (int)($row['len_documento'] ?? 0) > 0;
    }
    $stmt->close();
  }

  if ($pdf_url === '' && $has_db_document) {
    $pdf_url = $script_dir . '/vistas/tramites/acciones/descargar_no_procede_completar.php?cod=' . urlencode($cod);
  }

  return [
    'success' => $pdf_url !== '',
    'url' => $pdf_url,
    'filename' => $pdf_path !== '' ? basename($pdf_path) : "no_procede_completar_{$cod}.pdf",
    'msg' => $pdf_url !== '' ? '' : 'Documento no encontrado',
    'has_notification' => $has_notification || $has_db_notification,
    'notifications' => $notificaciones,
    'fs_path' => $pdf_path,
  ];
}

function separarNombreCompleto($nombreCompleto)
{
  $partes = preg_split('/\s+/', trim((string)$nombreCompleto));
  $partes = array_values(array_filter($partes, function ($p) {
    return $p !== '';
  }));
  $total = count($partes);

  if ($total <= 1) {
    return [$partes[0] ?? '', '', '', ''];
  }
  if ($total === 2) {
    return [$partes[0], '', $partes[1], ''];
  }
  if ($total === 3) {
    return [$partes[0], '', $partes[1], $partes[2]];
  }

  return [
    implode(' ', array_slice($partes, 0, $total - 2)),
    '',
    $partes[$total - 2],
    $partes[$total - 1],
  ];
}


function verificarEvidencias($cod)
{
  $anio = substr($cod, 4, 4);

  // Construir ruta base absoluta desde la ubicación del archivo actual
  $base_path = realpath(__DIR__ . '/../../../../');
  if ($base_path === false) {
    $base_path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..'
      . DIRECTORY_SEPARATOR . 'Arbimaps' . DIRECTORY_SEPARATOR . 'vistas' . DIRECTORY_SEPARATOR . 'tramites' . DIRECTORY_SEPARATOR . 'tramites_conservacion' . DIRECTORY_SEPARATOR
      . 'no_procede_completar' . DIRECTORY_SEPARATOR . $anio . DIRECTORY_SEPARATOR . $cod;
    $evidencias_dir = $base_path . DIRECTORY_SEPARATOR . 'evidencias';
  } else {
    $evidencias_dir = $base_path . neiva_app_url('Arbimaps/vistas/tramites/tramites_conservacion/no_procede_completar/')
      . $anio . '/' . $cod . '/evidencias';
  }

  if (!is_dir($evidencias_dir)) {
    return false;
  }

  // Buscar archivos PDF e imágenes
  $archivos = array_merge(
    glob($evidencias_dir . "/*.pdf") ?: [],
    glob($evidencias_dir . "/*.jpg") ?: [],
    glob($evidencias_dir . "/*.jpeg") ?: [],
    glob($evidencias_dir . "/*.png") ?: []
  );

  return count($archivos) > 0;
}

// Query para la lista (filtramos los blobs grandes, usamos LENGTH para verificar existencia).
// La fuente principal es tramites_por_completar, pero algunos flujos solo dejan
// registro en no_procede_completar; se incluyen como respaldo para no ocultarlos.
$sql = "
    SELECT
        cod_tramite,
        fmi_predio_tram,
        npn_predio_tram,
        nombre_propietario_tram,
        tipo_doc_propietario_tram,
        cedula_propietario_tram,
        valor_avaluo_terreno_tram,
        direccion_predio_terreno_tram,
        destino_econ_predio_tram,
        area_terr_predio_tram,
        area_cons_predio_tram,
        fecha_rad,
        documento_interesado,
        num_doc_interesado,
        primer_nombre_interesado,
        segundo_nombre_interesado,
        primer_apellido_interesado,
        segundo_apellido_interesado,
        telefono_interesado,
        correo_interesado,
        mutacion_tramite,
        fecha_limite_respuesta,
        tsolicitante_tramite,
        fmi_predio,
        npn_predio,
        observacion_tramite,
        LENGTH(sol_escrita_tramite) > 0 AS has_sol_escrita_tramite,
        LENGTH(cop_escritura_tramite) > 0 AS has_cop_escritura_tramite,
        LENGTH(ctl_tramite) > 0 AS has_ctl_tramite,
        LENGTH(doc_identidad_tramite) > 0 AS has_doc_identidad_tramite,
        LENGTH(carta_autorizacion_tramite) > 0 AS has_carta_autorizacion_tramite,
        LENGTH(otros_doc_tramite) > 0 AS has_otros_doc_tramite,
        0 AS esta_reactivado
    FROM tramites_por_completar
    UNION ALL
    SELECT
        npc.cod_radicacion_tramite AS cod_tramite,
        npc.fmi_predio AS fmi_predio_tram,
        npc.cod_catastro AS npn_predio_tram,
        COALESCE(NULLIF(npc.propietarios_predio, ''), npc.nombre_comp_interesado) AS nombre_propietario_tram,
        '' AS tipo_doc_propietario_tram,
        '' AS cedula_propietario_tram,
        0 AS valor_avaluo_terreno_tram,
        npc.direccion_predio AS direccion_predio_terreno_tram,
        '' AS destino_econ_predio_tram,
        0 AS area_terr_predio_tram,
        0 AS area_cons_predio_tram,
        npc.fecha_rad_tramite AS fecha_rad,
        '' AS documento_interesado,
        '' AS num_doc_interesado,
        npc.nombre_comp_interesado AS primer_nombre_interesado,
        '' AS segundo_nombre_interesado,
        '' AS primer_apellido_interesado,
        '' AS segundo_apellido_interesado,
        npc.telefono_interesado,
        npc.correo_interesado,
        npc.tipo_mutacion_tramite AS mutacion_tramite,
        npc.fecha_resp_tramite AS fecha_limite_respuesta,
        '' AS tsolicitante_tramite,
        npc.fmi_predio,
        npc.cod_catastro AS npn_predio,
        npc.observacion AS observacion_tramite,
        LENGTH(npc.sol_escrita_tramite) > 0 AS has_sol_escrita_tramite,
        LENGTH(npc.cop_escritura_tramite) > 0 AS has_cop_escritura_tramite,
        LENGTH(npc.ctl_tramite) > 0 AS has_ctl_tramite,
        LENGTH(npc.doc_identidad_tramite) > 0 AS has_doc_identidad_tramite,
        LENGTH(npc.carta_autorizacion_tramite) > 0 AS has_carta_autorizacion_tramite,
        LENGTH(npc.otros_doc_tramite) > 0 AS has_otros_doc_tramite,
        EXISTS (
            SELECT 1
            FROM tramite_radicacion tr
            WHERE tr.cod_tramite LIKE CONCAT(npc.cod_radicacion_tramite, '-%')
        ) AS esta_reactivado
    FROM no_procede_completar npc
    WHERE NOT EXISTS (
        SELECT 1
        FROM tramites_por_completar tpc
        WHERE tpc.cod_tramite = npc.cod_radicacion_tramite
    )
    ORDER BY fecha_rad DESC
";
// Reemplazar el bucle while existente por este:
$res = $mysqli->query($sql);
if (!$res) {
  die("Error consultando trÃ¡mites por completar: " . $mysqli->error);
}
$rows = [];
while ($r = $res->fetch_assoc()) {
  $cod = $r['cod_tramite'] ?? '';
  $anio = substr($cod, 4, 4);

  // Resolver carpeta FS
  $fs_dir = realpath(__DIR__ . "/../tramites_conservacion/no_procede_completar/$anio/$cod");
  if ($fs_dir === false) {
    $fs_dir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'tramites_conservacion'
      . DIRECTORY_SEPARATOR . 'no_procede_completar'
      . DIRECTORY_SEPARATOR . $anio . DIRECTORY_SEPARATOR . $cod;
  }

  // VERIFICAR NOTIFICACIONES
  $notificaciones_dir = $fs_dir . DIRECTORY_SEPARATOR . 'notificaciones';
  $has_notification = false;
  $notification_url = '';
  $notification_count = 0;
  $appBaseLocal = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/neiva/Arbimaps/index.php')), '/');

  if (is_dir($notificaciones_dir)) {
    $notification_files = glob($notificaciones_dir . DIRECTORY_SEPARATOR . "*.pdf");
    $notification_count = count($notification_files);
    $has_notification = $notification_count > 0;

    // Tomar la notificación más reciente para vista previa
    if ($has_notification) {
      $latest_notification = $notification_files[0];
      // Encontrar el archivo más reciente por fecha de modificación
      foreach ($notification_files as $file) {
        if (filemtime($file) > filemtime($latest_notification)) {
          $latest_notification = $file;
        }
      }
      $notification_url = 'vistas/tramites/cuentas_rechazadas/ver_notificacion_no_procede_completar.php?cod=' . urlencode($cod) . '&archivo=' . rawurlencode(basename($latest_notification));
    }
  }

  // Verificar también en la base de datos
  if (!$has_notification) {
    $sql_check = "SELECT notificacion FROM no_procede_completar WHERE cod_radicacion_tramite = ?";
    $stmt_check = $mysqli->prepare($sql_check);
    if ($stmt_check) {
      $stmt_check->bind_param("s", $cod);
      $stmt_check->execute();
      $result_check = $stmt_check->get_result();
      if ($row_check = $result_check->fetch_assoc()) {
        if (!$has_notification && !empty($row_check['notificacion'])) {
          $has_notification = true;
          $notification_url = 'vistas/tramites/cuentas_rechazadas/ver_notificacion_no_procede_completar.php?cod=' . urlencode($cod) . '&archivo=' . rawurlencode((string) $row_check['notificacion']);
        }
      }
      $stmt_check->close();
    }
  }

  // Asignar valores
  $r['has_notification'] = $has_notification;
  $r['notification_url'] = $notification_url;
  $r['notification_count'] = $notification_count;

  // VERIFICAR EVIDENCIAS - AGREGAR ESTA LÍNEA
  $r['has_evidencia'] = verificarEvidencias($cod);

  if (
    trim((string)($r['segundo_nombre_interesado'] ?? '')) === ''
    && trim((string)($r['primer_apellido_interesado'] ?? '')) === ''
    && trim((string)($r['segundo_apellido_interesado'] ?? '')) === ''
    && str_word_count((string)($r['primer_nombre_interesado'] ?? '')) > 1
  ) {
    [
      $r['primer_nombre_interesado'],
      $r['segundo_nombre_interesado'],
      $r['primer_apellido_interesado'],
      $r['segundo_apellido_interesado']
    ] = separarNombreCompleto($r['primer_nombre_interesado']);
  }

  $rows[] = $r;
}

// Manejo de acción
$action = $_GET['action'] ?? '';
$cod = $_GET['cod'] ?? '';
$msg = $_GET['msg'] ?? '';

// Fetch row data if needed
$rowData = null;
if ($cod && in_array($action, ['ver', 'completar'])) {
  foreach ($rows as $row) {
    if ($row['cod_tramite'] === $cod) {
      $rowData = $row;
      break;
    }
  }
  if (!$rowData) {
    $msg = 'error:Trámite no encontrado';
  }
}

function getFileUrlFromDB($mysqli, $cod, $field)
{
  $base_web = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/neiva/Arbimaps/index.php')), '/');
  $anio = substr($cod, 4, 4);
  $cod_sin_sufijo = preg_replace('/-\d+$/', '', $cod);
  $base_fs = realpath(__DIR__ . '/../../../../');
  if ($base_fs === false) {
    $base_fs = __DIR__ . '/../../../../';
  }
  $rel_base = "$base_web/vistas/tramites/tramites_conservacion/$anio";

  $buscarArchivo = function ($file_name) use ($base_fs, $base_web, $rel_base, $anio, $cod, $cod_sin_sufijo) {
    $file_name = trim((string)$file_name);
    if ($file_name === '') return null;

    $candidatos = [
      [
        $base_fs . "/Arbimaps/vistas/tramites/tramites_conservacion/$anio/$cod/$file_name",
        "$rel_base/$cod/" . rawurlencode($file_name)
      ],
      [
        $base_fs . "/Arbimaps/vistas/tramites/tramites_conservacion/$anio/$cod_sin_sufijo/$file_name",
        "$rel_base/$cod_sin_sufijo/" . rawurlencode($file_name)
      ],
      [
        $base_fs . "/Arbimaps/vistas/tramites/tramites_conservacion/no_procede_completar/$anio/$cod/documentos/$file_name",
        "$base_web/vistas/tramites/tramites_conservacion/no_procede_completar/$anio/$cod/documentos/" . rawurlencode($file_name)
      ],
    ];

    foreach ($candidatos as [$fs_path, $web_url]) {
      if (file_exists($fs_path)) {
        return $web_url;
      }
    }

    $pattern = $base_fs . "/Arbimaps/vistas/tramites/tramites_conservacion/$anio/{$cod_sin_sufijo}-*/" . $file_name;
    $matches = glob($pattern);
    if (!empty($matches)) {
      usort($matches, function ($a, $b) {
        return filemtime($b) <=> filemtime($a);
      });
      $fs_match = $matches[0];
      $folder = basename(dirname($fs_match));
      return "$rel_base/$folder/" . rawurlencode($file_name);
    }

    return null;
  };

  $stmt_np = $mysqli->prepare("SELECT $field AS doc_value FROM no_procede_completar WHERE cod_radicacion_tramite = ?");
  if ($stmt_np) {
    $stmt_np->bind_param("s", $cod);
    $stmt_np->execute();
    $result_np = $stmt_np->get_result();
    if ($row_np = $result_np->fetch_assoc()) {
      $doc_value = $row_np['doc_value'] ?? '';
      if ($doc_value !== '' && $doc_value !== null) {
        if (strncmp($doc_value, '%PDF', 4) === 0) {
          $stmt_np->close();
          return $base_web . '/vistas/tramites/acciones/descargar_documento_no_procede.php?cod=' . urlencode($cod) . '&campo=' . urlencode($field);
        }
        $url_np = $buscarArchivo($doc_value);
        if ($url_np !== null) {
          $stmt_np->close();
          return $url_np;
        }
      }
    }
    $stmt_np->close();
  }

  $sql = "SELECT $field AS file_name FROM tramites_por_completar WHERE cod_tramite = ?";
  $stmt = $mysqli->prepare($sql);
  if (!$stmt) return null;

  $stmt->bind_param("s", $cod);
  $stmt->execute();
  $result = $stmt->get_result();
  $url = null;

  if ($row = $result->fetch_assoc()) {
    $file_name = trim($row['file_name'] ?? '');
    if ($file_name !== '') {
      $url = $buscarArchivo($file_name);
    }
  }

  $stmt->close();
  if ($url === null) {
    $stmt_np = $mysqli->prepare("SELECT $field AS doc_value FROM no_procede_completar WHERE cod_radicacion_tramite = ?");
    if ($stmt_np) {
      $stmt_np->bind_param("s", $cod);
      $stmt_np->execute();
      $result_np = $stmt_np->get_result();
      if ($row_np = $result_np->fetch_assoc()) {
        $doc_value = $row_np['doc_value'] ?? '';
        if ($doc_value !== '' && $doc_value !== null && strncmp($doc_value, '%PDF', 4) === 0) {
          $base_web = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/neiva/Arbimaps/index.php')), '/');
          $url = $base_web . '/vistas/tramites/acciones/descargar_documento_no_procede.php?cod=' . urlencode($cod) . '&campo=' . urlencode($field);
        }
      }
      $stmt_np->close();
    }
  }
  return $url;
}


?>

  <style>
  html,
  body {
    font-family: 'Poppins', sans-serif !important;
    background: #f8f9fc !important;
  }

  .table th {
    min-width: 120px;
  }

  .no-procede-page .details-table {
    border-collapse: separate !important;
    border-spacing: 0;
    color: #0A2C1B;
    margin-bottom: 0;
  }

  .no-procede-page .details-table tbody th,
  .no-procede-page .details-table tbody td {
    border-left: 0 !important;
    border-right: 0 !important;
    border-top: none !important;
    color: #0A2C1B;
    font-size: 0.82rem;
    padding: 0.75rem;
    text-align: center;
    vertical-align: middle;
  }

  .no-procede-page .details-table tbody th {
    background-color: #FFFFFF !important;
    font-weight: 700;
    white-space: nowrap;
    width: 35%;
  }

  .no-procede-page .details-table tbody td {
    overflow-wrap: anywhere;
    word-break: normal;
  }

  .no-procede-page .details-table tbody tr:hover th,
  .no-procede-page .details-table tbody tr:hover td {
    background-color: #F6F8F7 !important;
  }

  .no-procede-page .details-table tbody tr:first-child th {
    border-top-left-radius: 14px;
  }

  .no-procede-page .details-table tbody tr:first-child td {
    border-top-right-radius: 14px;
  }

  .no-procede-page .details-table tbody tr:last-child th {
    border-bottom-left-radius: 14px;
  }

  .no-procede-page .details-table tbody tr:last-child td {
    border-bottom-right-radius: 14px;
  }

  .pdf-panel {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    padding: 0.75rem;
    height: 100%;
    display: flex;
    flex-direction: column;
  }

  .iframe-pdf {
    width: 100%;
    min-height: 70vh;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
  }

  .iframe-notification {
    width: 100%;
    height: 300px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
  }

  .no-procede-page .notificaciones-card {
    background-color: #ffffff;
    border: none !important;
    border-radius: 20px !important;
    overflow: hidden;
    padding: 0 !important;
    width: 100%;
  }

  .no-procede-page .notificaciones-header {
    align-items: center;
    background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%);
    color: #ffffff;
    display: flex;
    font-size: 1rem;
    font-weight: 800;
    gap: 0.5rem;
    justify-content: center;
    margin: 0;
    padding: 0.95rem 1rem;
    text-align: center;
  }

  .no-procede-page .notificaciones-header .badge {
    background-color: #AEE136 !important;
    border-radius: 999px;
    color: #0A2C1B !important;
    font-weight: 800;
    margin-left: auto;
    padding: 0.35rem 0.75rem;
  }

  .no-procede-page .notificaciones-body {
    padding: 1.25rem;
  }

  .no-procede-page .notificaciones-title {
    color: #0A2C1B !important;
    display: inline-flex;
    font-weight: 800;
    margin-bottom: 1rem !important;
  }

  .no-procede-page .notificacion-item {
    background-color: #F6F8F7 !important;
    border: 1px solid #C0D2C8 !important;
    border-radius: 14px !important;
    padding: 0.75rem !important;
    text-align: left;
  }

  .no-procede-page .notificacion-item>div {
    min-width: 0;
    overflow-wrap: anywhere;
  }

  .no-procede-page .notificacion-db-alert {
    background-color: rgba(174, 225, 54, 0.16) !important;
    border: 1px solid rgba(174, 225, 54, 0.5);
    border-radius: 14px;
    color: #0A2C1B;
    font-weight: 700;
  }

  .no-procede-page .notificacion-upload {
    border-top: 1px solid #E6ECE8;
    margin-top: 1.25rem;
    padding-top: 1.25rem;
  }

  .no-procede-page .notificacion-upload .input-group {
    border: 1px solid #C0D2C8;
    border-radius: 14px !important;
    box-shadow: none !important;
    height: 46px;
    max-width: 620px;
    overflow: hidden;
    width: 100% !important;
  }

  .no-procede-page .notificacion-upload .input-group-text {
    background-color: #F6F8F7 !important;
    border: 0 !important;
  }

  .no-procede-page .notificacion-upload .input-group-text i {
    color: #0A2C1B !important;
  }

  .no-procede-page .notificacion-upload .form-control {
    border: 0 !important;
    color: #0A2C1B;
  }

  .no-procede-page .notificacion-upload .btn-success {
    background-color: #0A2C1B !important;
    border-color: #0A2C1B !important;
    border-radius: 14px;
    color: #ffffff !important;
    font-weight: 800;
    padding: 0.55rem 1rem;
  }

  .no-procede-page .completar-form label {
    color: #0A2C1B !important;
    font-size: 0.9rem;
    font-weight: 700 !important;
    margin-bottom: 0.4rem;
  }

  .no-procede-page .completar-form .input-group {
    border-radius: 14px;
    box-shadow: none !important;
  }

  .no-procede-page .completar-form .input-group-text {
    align-items: center;
    background-color: #F6F8F7;
    border: 1px solid #C0D2C8;
    border-radius: 14px 0 0 14px;
    border-right: 0;
    color: #0A2C1B;
    display: flex;
    justify-content: center;
    min-width: 44px;
  }

  .no-procede-page .completar-form .input-group>.form-control {
    background-color: #ffffff !important;
    border: 1px solid #C0D2C8;
    border-radius: 0 14px 14px 0 !important;
    color: #0A2C1B;
    font-size: 0.9rem !important;
    min-height: 42px;
  }

  .no-procede-page .completar-form .input-group>.form-control[readonly] {
    background-color: #F8FAF9 !important;
    color: #0A2C1B;
  }

  .no-procede-page .completar-form .input-group>.form-control:focus {
    border-color: #7FAB64;
    box-shadow: 0 0 0 0.18rem rgba(127, 171, 100, 0.18);
  }

  .no-procede-page .completar-form .document-upload-group {
    cursor: pointer;
    width: 100%;
  }

  .no-procede-page .completar-form .document-file-label {
    align-items: center;
    background-color: #ffffff !important;
    border: 1px solid #C0D2C8;
    border-radius: 0 14px 14px 0 !important;
    color: #7F8E85 !important;
    cursor: pointer;
    display: flex;
    font-size: 0.86rem;
    font-weight: 500 !important;
    margin: 0;
    min-height: 42px;
    min-width: 0;
    overflow: hidden;
    padding: 0.55rem 0.75rem;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .no-procede-page .completar-form .document-file-label.has-file {
    color: #0A2C1B !important;
    font-weight: 700 !important;
  }

  .no-procede-page .completar-form .document-upload-help {
    color: #7F8E85 !important;
    display: block;
    margin-top: 0.4rem;
  }

  .no-procede-page .documentos-adjuntos-scroll {
    scrollbar-color: #C0D2C8 transparent;
    scrollbar-width: thin;
  }

  .no-procede-page .documentos-adjuntos-scroll::-webkit-scrollbar {
    width: 3px;
  }

  .no-procede-page .documentos-adjuntos-scroll::-webkit-scrollbar-track {
    background: transparent;
  }

  .no-procede-page .documentos-adjuntos-scroll::-webkit-scrollbar-thumb {
    background-color: #7FAB64;
    border-radius: 999px;
  }

  .no-procede-page .documentos-adjuntos-scroll::-webkit-scrollbar-thumb:hover {
    background-color: #0A2C1B;
  }

  .no-procede-page .documento-adjunto-item {
    background-color: #F8FAF9;
    border: 1px solid #E6ECE8;
    border-radius: 16px;
    margin-bottom: 1rem;
    padding: 1rem;
  }

  .no-procede-page .documento-adjunto-item:last-child {
    margin-bottom: 0;
  }

  .no-procede-page .documento-adjunto-header {
    gap: 0.75rem;
    margin-bottom: 0.85rem;
  }

  .no-procede-page .documento-adjunto-title {
    align-items: center;
    color: #0A2C1B;
    display: flex;
    font-size: 0.9rem;
    font-weight: 800;
    gap: 0.65rem;
    line-height: 1.3;
    min-width: 0;
    text-align: left;
  }

  .no-procede-page .documento-adjunto-icon {
    align-items: center;
    background-color: #AEE136;
    border-radius: 11px;
    color: #0A2C1B;
    display: inline-flex;
    flex: 0 0 36px;
    height: 36px;
    justify-content: center;
    width: 36px;
  }

  .no-procede-page .documento-adjunto-icon i {
    font-size: 1.05rem;
  }

  .no-procede-page .documento-adjunto-status {
    border-radius: 9px;
    font-size: 0.68rem;
    font-weight: 800;
    margin-left: 0.35rem;
    padding: 0.32rem 0.6rem;
  }

  .no-procede-page .documento-adjunto-status.is-uploaded {
    background-color: #AEE136;
    color: #0A2C1B;
  }

  .no-procede-page .documento-adjunto-status.is-pending {
    background-color: #FFFCD9;
    border: 1px solid #E0DA93;
    color: #7A7548;
  }

  .no-procede-page .documento-adjunto-item .document-upload-field>label {
    color: #506159 !important;
    font-size: 0.78rem;
    margin-bottom: 0.45rem;
  }

  .no-procede-page .documento-adjunto-open {
    align-items: center;
    background-color: #0A2C1B;
    border: 0;
    color: #ffffff;
    display: inline-flex;
    flex: 0 0 auto;
    height: 36px;
    justify-content: center;
    width: 36px;
  }

  .no-procede-page .documento-adjunto-open:hover {
    background-color: #000000;
    color: #ffffff;
  }

  .modal-header .modal-title {
    color: #FEFEFE !important;
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

  .section-title {
    border-bottom: 2px solid #7FAB64;
    color: #7FAB64;
    font-weight: 600;
  }

  .rechazados-detail-mini-card .section-title {
    margin-bottom: 1rem;
  }

  .tab-content {
    border: none;
    border-top: 1px solid #dee2e6;
    padding: 1rem;
    border-radius: 0 0 0.375rem 0.375rem;
  }

  .nav-tabs .nav-link.active {
    background-color: #002f550e;
    border-color: #dee2e6 #dee2e6 #fff;
    color: #002F55;
    font-weight: 700;
  }

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

  .bot_verenotrapesta:hover {
    background-color: #002F55;
    ;
    color: #FFFF;
  }

  .bot_verenotrapesta {
    background-color: #FFFF;
    color: #002F55;
    border: 1px solid #002F55;
  }



  .btn-notificacion {
    background-color: rgba(0, 112, 204, 0.43) !important;
    background-color: #0070cc6e !important;
    color: #002f55 !important;
  }

  .btn-notificacion:hover {
    filter: brightness(0.97);
  }

  .btn-notificacion.evidencia {
    background-color: #002f55 !important;
    border-color: #002f55 !important;
    color: #ffffff !important;
  }

  .btn-notificacion.evidencia:hover {
    filter: brightness(1.05);
  }

  .bot_verenotrapesta.d-none {
    display: inline-flex !important;
    align-items: center;
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

  #notificationModal .modal-content {
    border: none;
    border-radius: 20px;
    overflow: hidden;
  }

  #notificationModal .modal-header {
    background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%) !important;
    border: 0;
  }

  #notificationModal .modal-title {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
  }

  #notificationModal #notificationCod {
    border: 1px dotted #E0DA93;
    border-radius: 10px;
    color: #E0DA93;
    padding: 0.25rem 0.75rem;
  }

  #notificationModal .modal-body,
  #notificationModal .modal-footer {
    background-color: #F6F8F7;
  }

  #notificationModal .modal-footer {
    border-top: 1px solid #D1DDD5;
  }

  #notificationModal .nav-tabs .nav-link {
    border-radius: 11px 11px 0 0;
    color: #0A2C1B !important;
    font-weight: 700;
  }

  #notificationModal .nav-tabs .nav-link.active {
    background-color: #0A2C1B;
    border-color: #D1DDD5;
    color: #ffffff;
  }

  #notificationModal .notification-viewer-alert {
    background-color: #A0C882;
    border: 0;
    border-radius: 16px;
    color: #0A2C1B;
    margin: 1rem auto;
    max-width: 620px;
    text-align: center;
    width: 70%;
  }

  #notificationModal .modal-document-card {
    background-color: #ffffff;
    border: none;
    border-radius: 18px;
    margin: 1.5rem auto;
    overflow: hidden;
    padding: 0;
    width: 90%;
  }

  #notificationModal .modal-document-card iframe {
    border: none !important;
    border-radius: 14px;
    padding: 1rem;
  }

  #notificationModal #openNewTab,
  #notificationModal .modal-footer .btn {
    background-color: #0A2C1B !important;
    border-color: #0A2C1B !important;
    border-radius: 14px;
    color: #ffffff !important;
    font-weight: 700;
    padding: 0.55rem 1rem;
  }

  .no-procede-page {
    background-color: #EDEDED;
    color: #0A2C1B;
    min-height: 100%;
  }

  .no-procede-card {
    background: transparent;
    border: none;
    border-radius: 18px !important;
    overflow: hidden;
  }

  .no-procede-card>.card-header,
  .no-procede-page .card-header {
    background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%) !important;
    border: 0 !important;
    color: #ffffff !important;
    padding: 0.95rem 1.15rem !important;
  }

  .no-procede-card>.card-header h6,
  .no-procede-page .card-header h6 {
    font-size: 0.85rem;
    font-weight: 700;
    letter-spacing: 0;
    text-transform: uppercase;
  }

  .no-procede-page .card-body {
    background-color: #ffffff;
    border: none !important;
    border-radius: 0 0 18px 18px;
    padding: 1.25rem;
  }

  .no-procede-page .no-procede-card>.main-card-body.is-listado {
    background-color: #ffffff;
    padding: 1.3rem !important;
  }

  .no-procede-page .no-procede-card>.main-card-body.is-vista-interna {
    background-color: transparent;
  }

  .no-procede-page #tablaTramites {
    border-collapse: separate !important;
    border-spacing: 0;
    color: #0A2C1B;
    table-layout: fixed;
    width: 100% !important;
  }

  .no-procede-page .tramites-table-wrap {
    overflow-x: visible;
  }

  .no-procede-page #tablaTramites th,
  .no-procede-page #tablaTramites td {
    min-width: 0;
    overflow-wrap: anywhere;
    word-break: normal;
  }

  .no-procede-page #tablaTramites thead th {
    background-color: #EDEDED !important;
    border: 0 !important;
    color: #0A2C1B !important;
    font-size: 0.72rem;
    font-weight: 800;
    padding: 0.85rem 0.45rem;
    text-align: center;
    text-transform: uppercase;
    vertical-align: middle;
    white-space: normal;
  }

  .no-procede-page #tablaTramites tbody td {
    border-left: 0 !important;
    border-right: 0 !important;
    border-top: none;
    color: #0A2C1B;
    font-size: 0.82rem;
    text-align: center;
    vertical-align: middle;
  }

  .no-procede-page #tablaTramites tbody td:last-child .btn,
  .no-procede-page #tablaTramites tbody td:last-child .disabled {
    margin: 0.15rem;
    white-space: normal;
  }

  .no-procede-page #tablaTramites .view-notification {
    align-items: center;
    display: inline-flex;
    gap: 0.3rem;
    justify-content: center;
    line-height: 1;
    white-space: nowrap;
  }

  .no-procede-page #tablaTramites .notification-count {
    align-items: center;
    display: inline-flex;
    flex: 0 0 auto;
    font-size: 0.72rem;
    height: 1.25rem;
    justify-content: center;
    line-height: 1;
    min-width: 1.25rem;
    padding: 0rem;
  }

  .no-procede-page #tablaTramites tbody tr:hover td {
    background-color: #F6F8F7;
  }

  .no-procede-page .dataTables_wrapper .dataTables_filter {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 0.54rem;
  }

  .no-procede-page .dataTables_wrapper .dataTables_filter label {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    margin-bottom: 0.4rem;
    gap: 0.2rem;
  }

  .no-procede-page .dataTables_wrapper .dataTables_length label {
    align-items: center;
    justify-content: center;
    color: #0A2C1B;
    display: inline-flex;
    gap: 0.45rem;
    white-space: nowrap;
  }

  .no-procede-page .dataTables_wrapper .dataTables_filter input,
  .no-procede-page .dataTables_wrapper .dataTables_length select {
    border-radius: 14px !important;
  }



  .no-procede-page .dataTables_wrapper .dataTables_info {
    color: #7F8E85 !important;
    font-size: 0.82rem;
  }

  .no-procede-page .dataTables_wrapper .dataTables_paginate .pagination {
    gap: 0.35rem;
    justify-content: flex-end;
  }

  .no-procede-page .dataTables_wrapper .dataTables_paginate .page-link {
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

  .no-procede-page .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
    background-color: #0A2C1B !important;
    border-color: #0A2C1B !important;
    color: #ffffff !important;
  }

  .no-procede-page .dataTables_wrapper .dataTables_paginate .page-link:hover {
    background-color: #C0D2C8 !important;
    border-color: #C0D2C8 !important;
    color: #0A2C1B !important;
  }

  .no-procede-page .btn {
    border-radius: 14px;
    font-weight: 700;
  }

  .no-procede-page .btn-ver,
  .no-procede-page .btn-action[style*="#002f55"] {
    background-color: #0A2C1B !important;
    border-color: #0A2C1B !important;
    color: #ffffff !important;
  }

  .no-procede-page .btn-notificacion {
    background-color: #AEE136 !important;
    border-color: #AEE136 !important;
    color: #0A2C1B !important;
  }

  .no-procede-page .btn-notificacion.evidencia {
    background-color: #0A2C1B !important;
    border-color: #0A2C1B !important;
    color: #ffffff !important;
  }

  .no-procede-page .badge-soft {
    background-color: #AEE136;
    border: 0;
    border-radius: 999px;
    color: #0A2C1B;
    font-weight: 800;
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
    color: #fff;
  }

  .rechazados-detail-mini-card {
    border-radius: 18px !important;
  }
</style>
</head>

<!-- <body id="page-top">
  <div id="wrapper" class="p-2">
    <div id="content-wrapper" class="d-flex flex-column"> -->
<?php
$esVistaVer = $action === 'ver' && $rowData;
$esVistaCompletar = $action === 'completar' && $rowData
  && empty($rowData['esta_reactivado'])
  && in_array($rol_usuario, $roles_pueden_completar_no_procede, true);
$esVistaListado = !$esVistaVer && !$esVistaCompletar;
?>
<div id="content" class=" container-fluid rounded-4 p-3 no-procede-page">
  <div class="my-4 text-start w-100 px-1 ">
    <h2 class=" mb-0 fw-bold" style="color: #0A2C1B; font-weight: 700 !important">TRÁMITES POR COMPLETAR</h2>
    <small style="color: #7f8e85;">Consulta el listado de los trámites catatrales, verifica notifiaciones.</small>
  </div>
  <div class="card no-procede-card mb-4">
    <?php if ($esVistaListado): ?>
      <div class="card-header py-3 d-flex align-items-center justify-content-between">
        <div class="text-xs text-white text-uppercase my-2" style="font-size:1rem; font-weight:500;">
          Información de trámites
        </div>
      </div>
    <?php endif; ?>
    <div class="card-body main-card-body p-1 <?php echo $esVistaListado ? 'is-listado' : 'is-vista-interna'; ?>">
      <?php if ($msg): ?>
        <div class="alert <?php echo strpos($msg, 'success') !== false ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show">
          <i class="bi <?php echo strpos($msg, 'success') !== false ? 'bi-check-circle' : 'bi-exclamation-triangle'; ?>"></i>
          <?php echo htmlspecialchars(str_replace(['error:', 'success:'], '', $msg)); ?>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      <?php endif; ?>

      <?php if ($action === 'ver' && $rowData): ?>
        <!-- <h5 class="section-title d-flex align-items-center">
          <i class="bi bi-eye me-2"></i>
          <span>Detalles del Trámite <?php echo htmlspecialchars($rowData['cod_tramite']); ?></span>
          <a href="?page=tramites/cuentas_rechazadas/no_procede_completar" class="btn ms-auto" style="background-color: #002F55; color: #ffff;">Volver a la lista</a>
        </h5> -->

        <div class="card-header py-4 d-flex flex-row align-items-center justify-content-between rounded-4" style="background-color: #002F55;">
          <div class="text-xs text-white text-uppercase my-2" style="font-size:1rem; font-weight:200;">
            Detalles del Trámite: <br>
            <small style="font-weight:600; font: size 1rem;"><?php echo htmlspecialchars($rowData['cod_tramite']); ?></small>
          </div>
          <a href="?page=tramites/cuentas_rechazadas/no_procede_completar" class="btn btn-sm btn-light px-3 py-2">
            <i class="bi bi-arrow-left me-2"></i> Volver a la lista
          </a>
        </div>

        <div class="container-fluid ">
          <div class="row p-3 px-0">
            <div class="col-md-12 col-lg-6 ps-0 ">
              <div class="card p-3 rechazados-detail-mini-card">
                <div class="table-responsive ">
                  <table class="table table-bordered details-table ">
                    <tbody>
                      <?php
                      $orderedKeys = [
                        'cod_tramite',
                        'fecha_rad',
                        'documento_interesado',
                        'num_doc_interesado',
                        'primer_nombre_interesado',
                        'segundo_nombre_interesado',
                        'primer_apellido_interesado',
                        'segundo_apellido_interesado',
                        'telefono_interesado',
                        'correo_interesado',
                        'fmi_predio',
                        'npn_predio',
                        'direccion_predio_terreno_tram',
                        'destino_econ_predio_tram',
                        'area_terr_predio_tram',
                        'area_cons_predio_tram',
                        'valor_avaluo_terreno_tram',
                        'observacion_tramite',
                        'mutacion_tramite',
                        'tsolicitante_tramite'
                      ];
                      foreach ($orderedKeys as $key) {
                        if (array_key_exists($key, $rowData)) {
                          $displayKey = htmlspecialchars(str_replace('_', ' ', strtoupper($key)));
                          $value = htmlspecialchars($rowData[$key] ?? '');
                          echo "<tr><th scope='row'>{$displayKey}</th><td>{$value}</td></tr>";
                        }
                      }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <div class="col-md-12 col-lg-6 px-0 mt-3 mt-md-0 ">
              <div class="pdf-panel p-4">
                <div class="d-flex align-items-top justify-content-between mb-2">
                  <h6 class="fw-bold " style="color: #0A2C1B;">
                    <i class="bi bi-file-earmark-pdf"></i> Documento Principal
                    <br>
                    <?php
                    $docInfo = getDocumentUrlSeguro($mysqli, $cod);
                    $docState = 'Generado';
                    echo "<span class='badge badge-soft small px-3 rounded-3 mt-1'>{$docState}</span>";
                    ?>
                  </h6>
                  <div>
                    <a class="btn_ver btn btn-sm px-3 rounded-3" href="<?php echo $docInfo['url']; ?>" target="_blank">
                      <i class="bi bi-box-arrow-up-right me-2"></i> Abrir en nueva pestaña
                    </a>
                  </div>
                </div>

                <?php if ($docInfo['success']): ?>
                  <iframe class="iframe-pdf" style="height: 100%;" src="<?php echo $docInfo['url']; ?>#toolbar=1&navpanes=0&scrollbar=1"></iframe>
                <?php else: ?>
                  <div class="alert alert-warning mb-0">
                    <?php echo htmlspecialchars($docInfo['msg']); ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <hr class="my-5" style="border: none; border-bottom:4px dotted #0A5F5E">

            <!-- SECCIÓN DE NOTIFICACIONES -->
            <div class="col-12 col-lg-10  px-0  mx-auto">
              <div class="card card-documentos shadow h-100 d-flex flex-column text-center notificaciones-card">
                <div class="notificaciones-header p-4">
                  <i class="bi bi-bell"></i> Notificaciones
                  <?php if ($docInfo['has_notification']): ?>
                    <span class="badge small px-3 rounded-3">Subida</span>
                  <?php else: ?>
                    <span class="badge small px-3 rounded-3">Pendiente</span>
                  <?php endif; ?>
                </div>

                <div class="notificaciones-body">

                  <!-- Lista de notificaciones existentes -->
                  <?php if (!empty($docInfo['notifications'])): ?>
                    <div class="mb-3">
                      <p class="notificaciones-title">
                        <i class="bi bi-check-circle me-2"></i> Notificaciones subidas:
                      </p>
                      <?php foreach ($docInfo['notifications'] as $notification): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 notificacion-item">
                          <div>
                            <i class="bi bi-file-pdf text-danger"></i>
                            <span class="ml-2"><?php echo htmlspecialchars($notification['name']); ?></span> <br>
                            <small class="text-muted ml-2 ms-3"><?php echo $notification['date']; ?></small>
                          </div>
                          <a href="<?php echo $notification['url']; ?>" target="_blank"
                            class="btn_ver btn btn-sm rounded-4 px-3 p-2">
                            <i class="bi bi-arrow-up-right me-2"></i> Ver en otra pestaña
                          </a>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php elseif ($docInfo['has_notification']): ?>
                    <div class="alert notificacion-db-alert">
                      <i class="bi bi-check-circle"></i> Notificación cargada en la base de datos.
                    </div>
                  <?php endif; ?>

                  <!-- Formulario para subir nueva notificación -->
                  <form class="notificacion-upload" action="<?php echo htmlspecialchars($appBase); ?>/vistas/tramites/acciones/cargar_notificacion.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="cod_tramite" value="<?php echo htmlspecialchars($cod); ?>">

                    <div class="form-group">
                      <label class="font-weight-bold mb-1">Seleccionar notificación:</label>
                      <div class="input-group"
                        style="width: 70%; margin: auto; border-radius: 12px;
                                    overflow: hidden; box-shadow: 0 0 4px rgba(0, 0, 0, 0.1); height: 46px;">

                        <span class="input-group-text bg-white"
                          style="border-right: none; display: flex; align-items: center; border-radius: 12px 0 0 12px;">
                          <i class="bi bi-file-earmark-text-fill" style="color:#002f55; font-size: 1.1rem;"></i>
                        </span>

                        <label for="archivo_notificacion"
                          class="form-control text-start d-flex align-items-center"
                          style="cursor: pointer; border-left: none; border-radius: 0 12px 12px 0; height: 100%;">
                          <span id="archivo-notificacion-nombre" class="text-muted">Seleccionar archivo PDF</span>
                        </label>

                        <input type="file" id="archivo_notificacion" name="archivo" class="form-control"
                          accept="application/pdf" required style="display:none;">
                      </div>

                      <small class="form-text text-muted">
                        Solo PDF
                      </small>
                      <br>

                      <?php if ($docInfo['has_notification']): ?>
                        <small class="form-text text-success">
                          <i class="bi bi-info-circle"></i> Ya existe una notificación subida. Al subir una nueva se reemplazará.
                        </small>
                      <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-success mt-3 fw-light btn_ver">
                      <i class="bi bi-cloud-upload me-2"></i>
                      <?php echo $docInfo['has_notification'] ? 'Reemplazar Notificación' : 'Subir Notificación'; ?>
                    </button>
                  </form>
                </div>

                <script>
                  (function() {
                    var input = document.getElementById('archivo_notificacion');
                    var labelSpan = document.getElementById('archivo-notificacion-nombre');
                    if (input && labelSpan) {
                      input.addEventListener('change', function() {
                        labelSpan.textContent = (this.files && this.files.length > 0) ?
                          this.files[0].name :
                          'Seleccionar archivo PDF';
                      });
                    }
                  })();
                </script>
              </div>
            </div>


          </div>
        </div>


      <?php elseif ($action === 'completar' && $rowData && empty($rowData['esta_reactivado']) && in_array($rol_usuario, $roles_pueden_completar_no_procede, true)): ?>
        <!-- <h5 class="section-title">
          <i class="bi bi-check-circle"></i> Completar Trámite <?php echo htmlspecialchars($rowData['cod_tramite']); ?>
        </h5> -->

        <div class="card-header py-4 d-flex flex-row align-items-center justify-content-between rounded-4" style="background-color: #002F55;">
          <div class="text-xs text-white text-uppercase my-2" style="font-size:1rem; font-weight:200;">
            Completar trámite <br>
            <small>Cod: </small>
            <small style="font-weight:600; font: size 1rem;"><?php echo htmlspecialchars($rowData['cod_tramite']); ?></small>
          </div>
        </div>

        <form class="completar-form" action="<?php echo htmlspecialchars($appBase); ?>/vistas/tramites/acciones/completar_tramite.php" method="post" enctype="multipart/form-data">
          <input type="hidden" name="id_completar" value="<?php echo htmlspecialchars($rowData['cod_tramite']); ?>">

          <div class="container-fluid">
            <div class="row p-3 pb-0 px-0">
              <div class="col-lg-6 px-0">
                <div class="card  mb-3 p-3 shadow-sm rechazados-detail-mini-card">
                  <h6 class="section-title p-2 "> <i class="bi bi-info-circle me-1"></i> Información General</h6>
                  <!-- <div class="card-header" style="background-color: #f8f9fc; border-left: 4px solid #0F5699;">
                    <h6 class="mb-0 font-weight-bold" style="color: #0F5699;">
                      <i class="bi bi-info-circle"></i> Información General
                    </h6>
                  </div> -->
                  <div class="card-body px-1">
                    <div class="row ">
                      <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-muted small">Código Radicación</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                          <input type="text" class="form-control form-control-sm" name="cod_radicacion_tramite"
                            value="<?php echo htmlspecialchars($rowData['cod_tramite']); ?>" readonly>
                        </div>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-muted small">Fecha Radicación</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bi bi-calendar-date"></i></span>
                          <input type="text" class="form-control form-control-sm" name="fecha_rad_tramite"
                            value="<?php echo htmlspecialchars($rowData['fecha_rad']); ?>" readonly>
                        </div>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-muted small">Tipo Mutación</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bi bi-diagram-3-fill"></i></span>
                          <input type="text" class="form-control form-control-sm" name="tipo_mutacion_tramite"
                            value="<?php echo htmlspecialchars($rowData['mutacion_tramite']); ?>" readonly>
                        </div>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-muted small">Fecha Respuesta</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                          <input type="text" class="form-control form-control-sm" name="fecha_resp_tramite"
                            value="<?php echo htmlspecialchars($rowData['fecha_limite_respuesta']); ?>" readonly>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Información del Interesado -->
                <div class="card  mb-3 p-3 shadow-sm rechazados-detail-mini-card">
                  <h6 class="section-title p-2"> <i class="bi bi-person me-1"></i> Información del Interesado</h6>
                  <!-- <div class="card-header" style="background-color: #f8f9fc; border-left: 4px solid #0F5699;">
                    <h6 class="mb-0 font-weight-bold" style="color: #0F5699;">
                      <i class="bi bi-person"></i> Información del Interesado
                    </h6>
                  </div> -->
                  <div class="card-body px-1 ">
                    <div class="row ">
                      <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-muted small">Documento Interesado</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                          <input type="text" class="form-control form-control-sm" name="documento_interesado"
                            value="<?php echo htmlspecialchars($rowData['documento_interesado']); ?>">
                        </div>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-muted small">Número Documento</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                          <input type="text" class="form-control form-control-sm" name="num_doc_interesado"
                            value="<?php echo htmlspecialchars($rowData['num_doc_interesado']); ?>">
                        </div>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-muted small">Primer Nombre</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                          <input type="text" class="form-control form-control-sm" name="primer_nombre_interesado"
                            value="<?php echo htmlspecialchars($rowData['primer_nombre_interesado']); ?>">
                        </div>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-muted small">Segundo Nombre</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bi bi-person"></i></span>
                          <input type="text" class="form-control form-control-sm" name="segundo_nombre_interesado"
                            value="<?php echo htmlspecialchars($rowData['segundo_nombre_interesado']); ?>">
                        </div>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-muted small">Primer Apellido</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bi bi-people-fill"></i></span>
                          <input type="text" class="form-control form-control-sm" name="primer_apellido_interesado"
                            value="<?php echo htmlspecialchars($rowData['primer_apellido_interesado']); ?>">
                        </div>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-muted small">Segundo Apellido</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bi bi-people"></i></span>
                          <input type="text" class="form-control form-control-sm" name="segundo_apellido_interesado"
                            value="<?php echo htmlspecialchars($rowData['segundo_apellido_interesado']); ?>">
                        </div>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-muted small">Teléfono</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bi bi-telephone-fill"></i></span>
                          <input type="text" class="form-control form-control-sm" name="telefono_interesado"
                            value="<?php echo htmlspecialchars($rowData['telefono_interesado']); ?>">
                        </div>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-muted small">Correo Electrónico</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bi bi-envelope-at-fill"></i></span>
                          <input type="text" class="form-control form-control-sm" name="correo_interesado"
                            value="<?php echo htmlspecialchars($rowData['correo_interesado']); ?>">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Información del Predio -->
                <div class="card  mb-3 p-3 shadow-sm rechazados-detail-mini-card">
                  <h6 class="section-title p-2"> <i class="bi bi-geo-alt me-1"></i> Información del Predio</h6>
                  <!-- <div class="card-header" style="background-color: #f8f9fc; border-left: 4px solid #0F5699;">
                    <h6 class="mb-0 font-weight-bold" style="color: #0F5699;">
                      <i class="bi bi-geo-alt"></i> Información del Predio
                    </h6>
                  </div> -->
                  <div class="card-body px-1">
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-muted small">FMI</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bi bi-map-fill"></i></span>
                          <input type="text" class="form-control form-control-sm" name="fmi_predio"
                            value="<?php echo htmlspecialchars($rowData['fmi_predio']); ?>">
                        </div>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="font-weight-bold text-muted small">Código Catastro</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bi bi-map"></i></span>
                          <input type="text" class="form-control form-control-sm" name="cod_catastro"
                            value="<?php echo htmlspecialchars($rowData['npn_predio']); ?>">
                        </div>
                      </div>
                      <div class="col-md-12 mb-0">
                        <label class="font-weight-bold text-muted small">Actividad</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bi bi-house-exclamation"></i></span>
                          <input type="text" class="form-control form-control-sm" name="actividad_tramite"
                            value="<?php echo htmlspecialchars($rowData['tsolicitante_tramite']); ?>">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Columna derecha: Documentos Adjuntos -->
              <div class="col-lg-6 pe-0">
                <div class="card shadow-sm p-3 rechazados-detail-mini-card">
                  <h6 class="section-title p-2"> <i class="bi bi-file-earmark-pdf me-1"></i> Documentos Adjuntos</h6>
                  <!-- <div class="card-header" style="background-color: #f8f9fc; border-left: 4px solid #0F5699;">
                    <h6 class="mb-0 font-weight-bold" style="color: #0F5699;">
                      <i class="bi bi-file-earmark-pdf"></i> Documentos Adjuntos
                    </h6>
                  </div> -->
                  <div class="card-body documentos-adjuntos-scroll px-1" style="max-height: 1010px; overflow-y: auto;">
                    <?php
                    $campos = [
                      'sol_escrita_tramite' => 'Solicitud Escrita',
                      'cop_escritura_tramite' => 'Copia de Escritura',
                      'ctl_tramite' => 'CTL',
                      'doc_identidad_tramite' => 'Documento de Identidad',
                      'carta_autorizacion_tramite' => 'Carta de Autorización',
                      'otros_doc_tramite' => 'Otros Documentos'
                    ];

                    foreach ($campos as $campo => $titulo):
                      $url_archivo = getFileUrlFromDB($mysqli, $cod, $campo);
                    ?>
                      <div class="documento-adjunto-item">
                        <div class="d-flex justify-content-between align-items-center documento-adjunto-header">
                          <span class="documento-adjunto-title">
                            <span class="documento-adjunto-icon"><i class="bi bi-file-earmark-pdf-fill"></i></span>
                            <span><?php echo $titulo; ?></span>
                            <?php if ($url_archivo): ?>
                              <span class="badge documento-adjunto-status is-uploaded">Subido</span>
                            <?php else: ?>
                              <span class="badge documento-adjunto-status is-pending">Pendiente</span>
                            <?php endif; ?>
                          </span>
                          <?php if ($url_archivo): ?>
                            <a href="<?php echo $url_archivo; ?>" target="_blank"
                              class="btn btn-sm documento-adjunto-open"
                              title="Ver documento en nueva pestaña">
                              <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                          <?php endif; ?>
                        </div>
                        <div class="form-group document-upload-field">
                          <label class="small text-muted">Subir/Reemplazar <?php echo $titulo; ?> (PDF):</label>
                          <div class="input-group document-upload-group">
                            <span class="input-group-text"><i class="bi bi-file-earmark-pdf"></i></span>
                            <label for="documento-<?php echo htmlspecialchars($campo); ?>"
                              class="form-control document-file-label">
                              <span>Seleccionar archivo PDF</span>
                            </label>
                            <input type="file" id="documento-<?php echo htmlspecialchars($campo); ?>"
                              name="<?php echo $campo; ?>" accept="application/pdf" hidden>
                          </div>
                          <small class="form-text text-muted document-upload-help">
                            Solo PDF. Se guardará en la carpeta del trámite.
                          </small>
                        </div>
                        <?php if ($url_archivo): ?>
                          <button type="button"
                            class="btn btn-light btn-sm btn-block text-left"
                            data-toggle="collapse"
                            data-target="#preview-<?php echo $campo; ?>">
                            <i class="bi bi-eye"></i> Vista previa
                          </button>
                          <div id="preview-<?php echo $campo; ?>" class="collapse mt-2">
                            <iframe src="<?php echo $url_archivo; ?>#toolbar=0&navpanes=0&scrollbar=1"
                              style="width: 100%; height: 300px; border: 1px solid #e5e7eb; border-radius: 6px;">
                            </iframe>
                          </div>
                        <?php endif; ?>
                      </div>
                    <?php endforeach; ?>
                    <script>
                      document.querySelectorAll('.document-upload-field input[type="file"]').forEach(function(input) {
                        input.addEventListener('change', function() {
                          var label = this.closest('.document-upload-group').querySelector('.document-file-label');
                          var fileName = this.files && this.files.length ? this.files[0].name : 'Seleccionar archivo PDF';
                          label.querySelector('span').textContent = fileName;
                          label.classList.toggle('has-file', Boolean(this.files && this.files.length));
                        });
                      });
                    </script>
                  </div>
                </div>
              </div>
            </div>
          </div>


          <!-- Botones de acción -->
          <div class="mt-4 pt-3 text-end border-top w-100 ">
            <a href="?page=tramites/cuentas_rechazadas/no_procede_completar"
              class="btn px-3 rounded-3 p-2" style="border: 1px solid #e0da93; background-color: #e0da93;">
              <i class="bi bi-arrow-left me-2"></i> Cancelar
            </a>
            <button type="submit" id="btnCompletar" class="btn  btn-ver px-3 rounded-3 p-2">
              <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
              <i class="bi bi-check-circle me-2 "></i> Confirmar y Completar
            </button>
          </div>
        </form>

      <?php else: ?>
        <?php
        $roles_notificacion = ['director_catastro', 'ventanilla_catastral', 'procedencia_juridica', 'administrador'];
        $rol_usuario = $_SESSION['rol_usuario'] ?? '';
        $puede_completar_no_procede = in_array($rol_usuario, $roles_pueden_completar_no_procede, true);
        ?>
        <!-- Tabla completa corregida -->
        <div class="table-responsive tramites-table-wrap">
          <table id="tablaTramites" class="table">
            <thead>
              <tr>
                <th style="text-align: center; vertical-align: middle;border-top-left-radius: 12px; border-bottom-left-radius:12px;">Código Trámite</th>
                <th style="text-align: center; vertical-align: middle;">FMI</th>
                <th style="text-align: center; vertical-align: middle; max-width:190px;">Nombre Propietario</th>
                <th style="text-align: center; vertical-align: middle;">Documento</th>
                <th style="text-align: center; vertical-align: middle; ">Fecha Rad.</th>

                <?php if (in_array($rol_usuario, $roles_notificacion)): ?>
                  <th style="text-align: center; vertical-align: middle;">Notificación</th>
                <?php endif; ?>

                <th style="text-align: center; vertical-align: middle; border-top-right-radius: 12px; border-bottom-right-radius:12px;">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td style="text-align: center; vertical-align: middle;"><?php echo htmlspecialchars($row['cod_tramite'] ?? ''); ?></td>
                  <td style="text-align: center; vertical-align: middle;"><?php echo htmlspecialchars($row['fmi_predio_tram'] ?? ''); ?></td>
                  <td style="text-align: center; vertical-align: middle;max-width:190px;"><?php echo htmlspecialchars($row['nombre_propietario_tram'] ?? ''); ?></td>
                  <td style="text-align: center; vertical-align: middle;"><?php echo htmlspecialchars($row['tipo_doc_propietario_tram'] ?? ''); ?></td>
                  <td style="text-align: center; vertical-align: middle;">
                    <?php echo htmlspecialchars($row['fecha_rad'] ?? ''); ?>
                    <?php if (!empty($row['esta_reactivado'])): ?>
                      <div><span class="badge badge-success mt-1">Reactivado</span></div>
                    <?php endif; ?>
                  </td>

                  <?php if (in_array($rol_usuario, $roles_notificacion)): ?>
                    <td style="text-align: center; vertical-align: middle;">
                      <?php if ($row['has_notification']): ?>
                        <?php
                        // Verificar si tiene evidencias para definir el color
                        $tieneEvidencia = isset($row['has_evidencia']) && $row['has_evidencia'];
                        $btnClass = $tieneEvidencia ? 'btn-notificacion evidencia' : 'btn-notificacion';
                        ?>
                        <button type="button"
                          class="btn btn-sm <?php echo $btnClass; ?>  view-notification p-2 "
                          data-cod="<?php echo htmlspecialchars($row['cod_tramite']); ?>"
                          data-url="<?php echo htmlspecialchars($row['notification_url']); ?>"
                          title="Ver Notificación" style="font-size:0.8rem; border-radius:14px;">
                          <i class="bi bi-bell me-0"></i> Notificación
                          <?php if ($row['notification_count'] > 1): ?>
                            <span class="badge badge-light notification-count" style="color: #0A2C1B;"><?php echo $row['notification_count']; ?></span>
                          <?php endif; ?>
                        </button>
                      <?php else: ?>
                        <span class="badge rounded-3" style="border: 1px solid #E0DA93; background-color: #FFFCD9; color: #98935D;">Sin notificación</span>
                      <?php endif; ?>
                    </td>
                  <?php endif; ?>

                  <td style="text-align: center; vertical-align: middle;">
                    <a href="?page=tramites/cuentas_rechazadas/no_procede_completar&action=ver&cod=<?php echo urlencode($row['cod_tramite']); ?>"
                      class="btn btn-sm btn-action btn_ver px-3 rounded-3" style="background-color: #002f55; color: #ffff" title="Ver">
                      <i class="bi bi-eye"></i>
                    </a>
                    <?php if ($puede_completar_no_procede && empty($row['esta_reactivado'])): ?>
                      <a href="?page=tramites/cuentas_rechazadas/no_procede_completar&action=completar&cod=<?php echo urlencode($row['cod_tramite']); ?>"
                        class="btn btn-success btn-sm btn-action rounded-3" title="Completar">
                        <i class="bi bi-check-circle"></i> Completar
                      </a>
                    <?php elseif (!empty($row['esta_reactivado'])): ?>
                      <span class="btn btn-secondary btn-sm disabled" title="Trámite ya reactivado">
                        Reactivado
                      </span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Modal para Vista Previa de Notificación y Firmados -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true" style=" z-index: 99999 !important;">
  <div class="modal-dialog modal-xl modal-altura">
    <div class="modal-content">
      <div class="modal-header p-4">
        <h5 class="modal-title text-white" id="notificationModalLabel">
          <i class="bi bi-bell me-2"></i> Gestión de Documentos - <span id="notificationCod"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white me-2" data-bs-dismiss="modal" aria-label="Cerrar">
        </button>
      </div>
      <div class="modal-body">
        <!-- Pestañas -->
        <ul class="nav nav-tabs" id="documentTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <a class="nav-link active" id="notification-tab" data-toggle="tab" href="#notification" role="tab" aria-controls="notification" aria-selected="true">
              <i class="bi bi-bell"></i> Notificación
            </a>
          </li>
          <?php if (false): ?>
            <li class="nav-item" role="presentation">
              <a class="nav-link" id="evidencia-tab" style="color: #002F55 !important;" data-toggle="tab" href="#evidencia" role="tab" aria-controls="evidencia" aria-selected="false">
                <i class="bi bi-folder-check"></i> Evidencias
              </a>
            </li>
          <?php endif; ?>
        </ul>

        <!-- Contenido de las pestañas -->
        <div class="tab-content rounded-3" id="documentTabsContent">
          <!-- Pestaña de Notificación -->
          <div class="tab-pane fade show active" id="notification" role="tabpanel" aria-labelledby="notification-tab">
            <div class="notification-viewer-alert p-3">
              <i class="bi bi-exclamation-circle-fill me-2"></i>
              Visualizando notificación para: <strong id="notificationCodTab"></strong>
            </div>
            <div class="pdf-panel modal-document-card">
              <iframe id="notificationIframe" class="iframe-pdf" src="" style="min-height: 60vh;"></iframe>
            </div>
            <?php if ($rol_usuario === 'ventanilla_catastral'): ?>
              <div class="mt-3 text-center">
                <a id="openNewTab" href="#" class="btn btn-sm" target="_blank">
                  <i class="bi bi-box-arrow-up-right me-2"></i> Abrir en nueva pestaña
                </a>
              </div>
            <?php endif; ?>
          </div>

          <?php if (false): ?>
            <!-- Pestaña de Evidencias -->
            <div class="tab-pane fade" id="evidencia" role="tabpanel" aria-labelledby="evidencia-tab">
              <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                Gestión de evidencias para: <strong id="evidenciaCodTab"></strong>
              </div>

              <!-- Formulario para subir nuevas evidencias -->
              <div class="upload-section mt-4 text-center ">
                <h6 class=" fw-bold"><i class="bi bi-cloud-upload"></i> Subir Evidencia:</h6>
                <form id="uploadEvidenciaForm" action="<?php echo htmlspecialchars($appBase); ?>/vistas/tramites/acciones/cargar_evidencia.php" method="post" enctype="multipart/form-data">
                  <input type="hidden" name="cod_tramite" id="evidenciaCodInput" value="">

                  <div class="card card-documentos col-8 mx-auto shadow h-100 p-3 border d-flex flex-column text-center my-4">
                    <label class="form-label fw-bold mb-2">Seleccionar evidencia (PDF o imagen):</label>

                    <div class="input-group"
                      style="width: 70%; margin: auto; border-radius: 12px;
                                overflow: hidden; box-shadow: 0 0 4px rgba(0, 0, 0, 0.1); height: 46px;">

                      <span class="input-group-text bg-white"
                        style="border-right: none; display: flex; align-items: center; border-radius: 12px 0 0 12px;">
                        <i class="bi bi-file-earmark-pdf-fill" style="color:#002f55; font-size: 1.1rem;"></i>
                      </span>

                      <label for="archivo_evidencia"
                        class="form-control text-start d-flex align-items-center"
                        style="cursor: pointer; border-left: none; border-radius: 0 12px 12px 0; height: 100%;">
                        <span id="archivo-evidencia-nombre" class="text-muted">Seleccionar archivo PDF</span>
                      </label>

                      <input type="file" id="archivo_evidencia" name="archivo"
                        accept="application/pdf,image/*" required style="display: none;">
                    </div>

                    <div class="form-text mt-2">
                      Puedes subir archivos PDF
                    </div>

                    <button type="submit" class="btn btn-success mt-2" style="background-color: #002F55;">
                      <i class="bi bi-cloud-upload me-2"></i>
                      <span id="evidenciaButtonText">Subir Evidencia</span>
                    </button>

                    <div id="evidenciaUploadInfo" class="mt-2" style="display: none;">
                      <small class="form-text text-success">
                        <i class="bi bi-info-circle"></i> Ya existen evidencias cargadas. Puedes subir más si lo necesitas.
                      </small>
                    </div>
                  </div>
                </form>

                <!-- Script para mostrar nombre del archivo -->
                <script>
                  (function() {
                    var input = document.getElementById('archivo_evidencia');
                    var labelSpan = document.getElementById('archivo-evidencia-nombre');
                    if (input && labelSpan) {
                      input.addEventListener('change', function() {
                        labelSpan.textContent = (this.files && this.files.length > 0) ?
                          this.files[0].name :
                          'Seleccionar archivo PDF o imagen';
                      });
                    }
                  })();
                </script>

              </div>

              <!-- Vista previa de evidencias existentes -->
              <div id="evidenciaPreview" class="my-3" style="display: none;">
                <h6 class="mb-3 fw-bold text-center"><i class="bi bi-folder2-open text-primary"></i> Evidencias Existentes:</h6>
                <div class="list-group px-2" id="evidenciaList"></div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="../../vendor/jquery/jquery.min.js"></script>
<script src="../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../../js/sb-admin-2.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  $(document).ready(function() {
    $('#tablaTramites').DataTable({
      "language": window.neivaDataTablesLanguage ? window.neivaDataTablesLanguage() : {},
      "pageLength": 10,
      "order": [
        [0, 'desc']
      ],
      "responsive": true,
      "searching": true,
      "lengthChange": true,
      "info": true,
      "autoWidth": false
    });

    $('[data-toggle="collapse"]').on('click', function(e) {
      e.preventDefault();
      const target = $(this).data('target');
      $('.collapse').not(target).collapse('hide');
      $(target).collapse('toggle');
    });

    $('.btn[data-toggle="collapse"]').on('click', function() {
      const target = $(this).data('target');
      const iframe = $(target).find('iframe');
      const src = iframe.attr('src');
      if (!iframe.data('loaded')) {
        iframe.attr('src', src);
        iframe.data('loaded', true);
      }
    });

    $(document).on('click', '.view-notification', function() {
      const cod = $(this).data('cod');
      const url = $(this).data('url');
      $('#notificationCod').text(cod);
      $('#notificationCodTab').text(cod);
      $('#notificationIframe').attr('src', url + '#toolbar=1&navpanes=0&scrollbar=1');
      $('#downloadNotification').attr('href', url);
      $('#openNewTab').attr('href', url);
      $('#notificationModal').modal('show');
      $('#documentTabs a[href="#notification"]').tab('show');
      $('#documentTabs a').off('click').on('click', function(e) {
        e.preventDefault();
        $(this).tab('show');
      });
    });

    function cargarEvidencias(cod) {
      $.ajax({
        url: '<?php echo htmlspecialchars($appBase); ?>/vistas/tramites/acciones/cargar_evidencia.php',
        type: 'GET',
        data: {
          cod_tramite: cod
        },
        dataType: 'json',
        success: function(response) {
          const list = $('#evidenciaList');
          list.empty();
          $('.iframe-evidencia').remove();

          if (response.success && response.evidencias && response.evidencias.length > 0) {
            $('#evidenciaPreview').show();
            $('#evidenciaUploadInfo').show();

            // Vista previa del más reciente
            const latest = response.evidencias[0];
            const previewHtml = latest.url.endsWith('.pdf') ?
              `<iframe class="iframe-pdf iframe-evidencia mb-3"
                       src="${latest.url}#toolbar=1&navpanes=0&scrollbar=1"></iframe>` :
              `<img src="${latest.url}" class="img-fluid mb-3 iframe-evidencia rounded border" alt="Evidencia más reciente">`;
            $('#evidenciaPreview').prepend(previewHtml);

            // Listado de todas las evidencias
            response.evidencias.forEach(file => {
              const icon = file.url.endsWith('.pdf') ?
                'bi-file-earmark-pdf text-danger' :
                'bi-file-earmark-image text-success';
              const item = `
                      <div class="d-flex justify-content-between bg-white shadow align-items-center p-4 border rounded-4 mb-2">
                        <div>
                          <i class="bi ${icon} ms-2"></i>
                          <span class="ml-2">${file.name}</span><br>
                          <small class="text-muted ms-2 mt-4"><i class="bi bi-clock me-1"></i>${file.date}</small>
                        </div>
                        <a href="${file.url}" target="_blank" class="btn btn-sm" style="background-color: #002F55; color: white;">
                          <i class="bi bi-box-arrow-up-right"></i> Ver
                        </a>
                      </div>`;
              list.append(item);
            });

          } else {
            $('#evidenciaPreview').show();
            $('#evidenciaUploadInfo').hide();
            list.html(`
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
        error: function() {
          const list = $('#evidenciaList');
          $('#evidenciaPreview').show();
          $('#evidenciaUploadInfo').hide();
          list.html(`
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

    $(document).on('click', '.view-notification', function() {
      const cod = $(this).data('cod');
      $('#evidenciaCodTab').text(cod);
      $('#evidenciaCodInput').val(cod);
    });

    $('form[action*="completar_tramite.php"]').on('submit', function() {
      const btn = $('#btnCompletar');
      btn.prop('disabled', true);
      btn.find('.spinner-border').removeClass('d-none');
      btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');
    });

    <?php if (isset($_SESSION['tramite_msg'])): ?>
      const tramiteMsg = "<?php echo addslashes($_SESSION['tramite_msg']); ?>";
      const isSuccess = tramiteMsg.includes('success:');
      const message = tramiteMsg.replace('success:', '').replace('error:', '');
      Swal.fire({
        icon: isSuccess ? 'success' : 'error',
        title: isSuccess ? '¡Éxito!' : 'Error',
        text: message,
        confirmButtonColor: '#002F55',
        confirmButtonText: 'Entendido'
      });
      <?php unset($_SESSION['tramite_msg']); ?>
    <?php endif; ?>
  });
</script>

<?php
if (isset($mysqli)) {
  $mysqli->close();
}
?>
