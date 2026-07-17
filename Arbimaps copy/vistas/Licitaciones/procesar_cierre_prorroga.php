<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require __DIR__ . '/../../../conexion.php';

mysqli_set_charset($mysqli, 'utf8mb4');
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
error_reporting(E_ALL);

function jsonFail($msg, $extra = []) {
  echo json_encode(array_merge(['success' => false, 'message' => $msg], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}

function isPdf($tmpPath) {
  if (!is_file($tmpPath)) return false;
  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime  = finfo_file($finfo, $tmpPath);
  finfo_close($finfo);
  return $mime === 'application/pdf';
}

function ensureDir($path) {
  if (!is_dir($path)) {
    if (!mkdir($path, 0775, true)) return false;
  }
  return true;
}

function safeFilename($name) {
  $name = preg_replace('/[^\w\-. ]+/u', '_', $name);
  $name = str_replace(' ', '_', $name);
  return $name;
}

function saveUploadedPdf($file, $absDir, $urlBase) {
  if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
    return [false, null, "No se subió archivo."];
  }
  if ($file['error'] !== UPLOAD_ERR_OK) {
    return [false, null, "Error al subir archivo."];
  }
  if (($file['size'] ?? 0) > 20 * 1024 * 1024) {
    return [false, null, "El archivo excede 20MB."];
  }
  if (!isPdf($file['tmp_name'])) {
    return [false, null, "Solo se permiten PDFs."];
  }
  if (!ensureDir($absDir)) {
    return [false, null, "No se pudo crear carpeta destino."];
  }

  $original = safeFilename($file['name'] ?? 'documento.pdf');
  $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
  if ($ext !== 'pdf') return [false, null, "Extensión inválida."];

  $unique = uniqid('', true) . '_' . $original;
  $absPath = rtrim($absDir, '/\\') . DIRECTORY_SEPARATOR . $unique;

  if (!move_uploaded_file($file['tmp_name'], $absPath)) {
    return [false, null, "No se pudo guardar el archivo."];
  }

  $url = rtrim($urlBase, '/') . '/' . $unique;
  return [true, $url, null];
}

// =========================
// 1) VALIDAR POST
// =========================
$radicado = trim($_POST['radicado'] ?? '');
if ($radicado === '') jsonFail("Radicado no válido.");

// Campos licitaciones (TABLA 1)
$lc_tipo_entidad       = trim($_POST['lc_tipo_entidad'] ?? '');
$lc_proceso            = trim($_POST['lc_proceso'] ?? '');
$lc_municipio          = trim($_POST['lc_municipio'] ?? '');
$lc_departamento       = trim($_POST['lc_departamento'] ?? '');
$lc_entidad            = trim($_POST['lc_entidad'] ?? '');
$lc_valor              = trim($_POST['lc_valor'] ?? '');
$lc_numero_proceso     = trim($_POST['lc_numero_proceso'] ?? '');
$lc_nombre_licitacion  = trim($_POST['lc_nombre_licitacion'] ?? '');
$lc_proyecto           = trim($_POST['lc_proyecto'] ?? '');
$lc_fecha_apertura     = trim($_POST['lc_fecha_apertura'] ?? '');
$lc_fecha_presentacion = trim($_POST['lc_fecha_presentacion'] ?? '');

// TABLA 3 (licitaciones_prorroga) => NOMBRES DEL FORM
$pro_motivo = trim($_POST['pro_motivo'] ?? '');
if ($pro_motivo === '') jsonFail("El motivo es obligatorio.");

// Soporte obligatorio => name="pro_doc"
if (!isset($_FILES['pro_doc'])) jsonFail("Debes adjuntar el documento soporte (PDF).");

// =========================
// 2) RUTAS BASE
// =========================
$basePublic = "/arbimaps/Arbimaps/vistas/Licitaciones/Arbitrium_licitaciones/{$radicado}";
$baseAbs    = realpath(__DIR__) . "/Arbitrium_licitaciones/{$radicado}";

$folders = [
  'ADENDA'      => ['abs' => $baseAbs . "/Adendas",     'url' => $basePublic . "/Adendas"],
  'CONDICIONES' => ['abs' => $baseAbs . "/Condiciones", 'url' => $basePublic . "/Condiciones"],
  'PROYECTO'    => ['abs' => $baseAbs . "/Proyecto",    'url' => $basePublic . "/Proyecto"],
  'OTROS'       => ['abs' => $baseAbs . "/Otros",       'url' => $basePublic . "/Otros"],
  'SOPORTE'     => ['abs' => $baseAbs . "/Soporte",     'url' => $basePublic . "/Soporte"],
];

// =========================
// 3) TRANSACCIÓN
// =========================
$mysqli->begin_transaction();

try {

  // 3.1 Obtener ID licitación (para FK documentos_licitacion.lc_id)
  $lic = $mysqli->prepare("SELECT id FROM licitaciones WHERE lc_radicado = ? LIMIT 1");
  if (!$lic) throw new Exception("Error preparando SELECT licitación: " . $mysqli->error);
  $lic->bind_param("s", $radicado);
  $lic->execute();
  $rowLic = $lic->get_result()->fetch_assoc();
  $lic->close();

  if (!$rowLic) throw new Exception("No existe licitación con ese radicado.");
  $lc_id = (int)$rowLic['id'];

  
  // 3.2 UPDATE licitaciones (TABLA 1) + CAMBIAR ESTADO A PRORROGA
  $upd = $mysqli->prepare("
    UPDATE licitaciones SET
      lc_tipo_entidad = ?,
      lc_proceso = ?,
      lc_municipio = ?,
      lc_departamento = ?,
      lc_entidad = ?,
      lc_valor = ?,
      lc_numero_proceso = ?,
      lc_nombre_licitacion = ?,
      lc_proyecto = ?,
      lc_fecha_apertura = ?,
      lc_fecha_presentacion = ?,
      lc_estado = 'PRORROGA'
    WHERE lc_radicado = ?
    LIMIT 1
  ");
  if (!$upd) throw new Exception("Error preparando UPDATE licitaciones: " . $mysqli->error);

  $upd->bind_param(
    "ssssssssssss",
    $lc_tipo_entidad,
    $lc_proceso,
    $lc_municipio,
    $lc_departamento,
    $lc_entidad,
    $lc_valor,
    $lc_numero_proceso,
    $lc_nombre_licitacion,
    $lc_proyecto,
    $lc_fecha_apertura,
    $lc_fecha_presentacion,
    $radicado
  );

  if (!$upd->execute()) throw new Exception("Error actualizando licitación: " . $upd->error);
  $upd->close();


  // 3.3 Guardar SOPORTE (pro_doc) (TABLA 3)
  [$okSup, $urlSoporte, $errSup] = saveUploadedPdf($_FILES['pro_doc'], $folders['SOPORTE']['abs'], $folders['SOPORTE']['url']);
  if (!$okSup) throw new Exception("Soporte: " . $errSup);

  // 3.4 INSERT licitaciones_prorroga (TABLA 3)
  // Campos: lc_pro_radicado | pro_motivo | pro_doc | fecha_prorroga | fecha_inico_prorroga
  $fecha_inicio = $lc_fecha_presentacion ?: date('Y-m-d');

  $insP = $mysqli->prepare("
    INSERT INTO licitaciones_prorroga
      (lc_pro_radicado, pro_motivo, pro_doc, fecha_prorroga, fecha_inico_prorrogra)
    VALUES (?, ?, ?, NOW(), ?)
  ");
  if (!$insP) throw new Exception("Error preparando INSERT prorroga: " . $mysqli->error);

  $insP->bind_param("ssss", $radicado, $pro_motivo, $urlSoporte, $fecha_inicio);
  if (!$insP->execute()) throw new Exception("Error insertando prórroga: " . $insP->error);
  $insP->close();

  // 3.5 INSERT documentos_licitacion (TABLA 2) (ACUMULA)
  // Campos reales: lc_id | lc_doc_radicado | doc_tipo | doc_ruta | fecha_subida
  $insD = $mysqli->prepare("
    INSERT INTO documentos_licitacion (lc_id, lc_doc_radicado, doc_tipo, doc_ruta, fecha_subida)
    VALUES (?, ?, ?, ?, NOW())
  ");
  if (!$insD) throw new Exception("Error preparando INSERT documentos_licitacion: " . $mysqli->error);

  // ADENDA
  if (isset($_FILES['lc_adendas']) && $_FILES['lc_adendas']['error'] !== UPLOAD_ERR_NO_FILE) {
    [$ok, $url, $err] = saveUploadedPdf($_FILES['lc_adendas'], $folders['ADENDA']['abs'], $folders['ADENDA']['url']);
    if (!$ok) throw new Exception("Adenda: " . $err);

    $tipo = "ADENDA";
    $insD->bind_param("isss", $lc_id, $radicado, $tipo, $url);
    if (!$insD->execute()) throw new Exception("Error insertando ADENDA: " . $insD->error);
  }

  // CONDICIONES
  if (isset($_FILES['lc_condiciones']) && $_FILES['lc_condiciones']['error'] !== UPLOAD_ERR_NO_FILE) {
    [$ok, $url, $err] = saveUploadedPdf($_FILES['lc_condiciones'], $folders['CONDICIONES']['abs'], $folders['CONDICIONES']['url']);
    if (!$ok) throw new Exception("Condiciones: " . $err);

    $tipo = "CONDICIONES";
    $insD->bind_param("isss", $lc_id, $radicado, $tipo, $url);
    if (!$insD->execute()) throw new Exception("Error insertando CONDICIONES: " . $insD->error);
  }

  // PROYECTO
  if (isset($_FILES['lc_condiciones_proyecto']) && $_FILES['lc_condiciones_proyecto']['error'] !== UPLOAD_ERR_NO_FILE) {
    [$ok, $url, $err] = saveUploadedPdf($_FILES['lc_condiciones_proyecto'], $folders['PROYECTO']['abs'], $folders['PROYECTO']['url']);
    if (!$ok) throw new Exception("Proyecto: " . $err);

    $tipo = "PROYECTO";
    $insD->bind_param("isss", $lc_id, $radicado, $tipo, $url);
    if (!$insD->execute()) throw new Exception("Error insertando PROYECTO: " . $insD->error);
  }

  // OTROS (multiple)
  if (isset($_FILES['lc_otros_doc']) && isset($_FILES['lc_otros_doc']['name']) && is_array($_FILES['lc_otros_doc']['name'])) {
    $names = $_FILES['lc_otros_doc']['name'];
    $tmps  = $_FILES['lc_otros_doc']['tmp_name'];
    $sizes = $_FILES['lc_otros_doc']['size'];
    $errs  = $_FILES['lc_otros_doc']['error'];

    for ($i = 0; $i < count($names); $i++) {
      if ($errs[$i] === UPLOAD_ERR_NO_FILE) continue;
      if ($errs[$i] !== UPLOAD_ERR_OK) throw new Exception("Otros: error subiendo archivo #".($i+1));

      $file = [
        'name' => $names[$i],
        'tmp_name' => $tmps[$i],
        'size' => $sizes[$i],
        'error' => $errs[$i]
      ];

      [$ok, $url, $err] = saveUploadedPdf($file, $folders['OTROS']['abs'], $folders['OTROS']['url']);
      if (!$ok) throw new Exception("Otros: " . $err . " (archivo: ".$names[$i].")");

      $tipo = "OTROS";
      $insD->bind_param("isss", $lc_id, $radicado, $tipo, $url);
      if (!$insD->execute()) throw new Exception("Error insertando OTROS: " . $insD->error);
    }
  }

  $insD->close();

  $mysqli->commit();

  echo json_encode([
    'success' => true,
    'message' => 'Guardado correcto'
  ], JSON_UNESCAPED_UNICODE);
  exit;

} catch (Exception $e) {
  $mysqli->rollback();
  jsonFail("Error guardando: " . $e->getMessage());
}
