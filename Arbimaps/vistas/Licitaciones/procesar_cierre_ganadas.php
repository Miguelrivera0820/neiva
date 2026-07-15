<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require __DIR__ . '/../../../conexion.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(["success" => false, "message" => "Método no permitido."]);
  exit;
}

$radicado      = trim($_POST['radicado'] ?? '');
$numero_poliza = trim($_POST['numero_poliza'] ?? '');
$fecha_inicio  = trim($_POST['fecha_inicio'] ?? '');

if ($radicado === '' || $numero_poliza === '' || $fecha_inicio === '') {
  echo json_encode(["success" => false, "message" => "Faltan datos obligatorios (radicado, numero_poliza, fecha_inicio)."]);
  exit;
}

// Validar fecha YYYY-MM-DD
$dt = DateTime::createFromFormat('Y-m-d', $fecha_inicio);
if (!$dt || $dt->format('Y-m-d') !== $fecha_inicio) {
  echo json_encode(["success" => false, "message" => "Fecha de inicio inválida."]);
  exit;
}

/**
 * Guarda un PDF si fue enviado en el input $fieldName.
 * Retorna ruta relativa para guardar en BD o NULL si no se envió archivo.
 */
function guardarPdfOpcional(string $fieldName, string $radicado, string $prefix, string $uploadDirAbs, string $uploadDirRel): ?string
{
  if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
    return null; // no se envió
  }

  if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
    throw new Exception("Error subiendo el archivo: $fieldName");
  }

  // 20MB
  $maxBytes = 20 * 1024 * 1024;
  if ($_FILES[$fieldName]['size'] > $maxBytes) {
    throw new Exception("El PDF ($fieldName) supera 20MB.");
  }

  // extensión
  $ext = strtolower(pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION));
  if ($ext !== 'pdf') {
    throw new Exception("Solo se permite PDF ($fieldName).");
  }

  // mime real
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime  = $finfo->file($_FILES[$fieldName]['tmp_name']);
  if ($mime !== 'application/pdf') {
    throw new Exception("El archivo ($fieldName) no parece un PDF válido.");
  }

  if (!is_dir($uploadDirAbs)) {
    if (!mkdir($uploadDirAbs, 0775, true) && !is_dir($uploadDirAbs)) {
      throw new Exception("No se pudo crear el directorio de subida.");
    }
  }

  $safeRadicado = preg_replace('/[^a-zA-Z0-9_-]/', '_', $radicado);
  $newName = "{$prefix}_{$safeRadicado}_" . date('Ymd_His') . ".pdf";

  $destAbs = rtrim($uploadDirAbs, '/\\') . DIRECTORY_SEPARATOR . $newName;

  if (!move_uploaded_file($_FILES[$fieldName]['tmp_name'], $destAbs)) {
    throw new Exception("No se pudo guardar el PDF ($fieldName).");
  }

  return rtrim($uploadDirRel, '/\\') . "/" . $newName;
}

try {
  $uploadDirAbs = __DIR__ . '/../../../uploads/cierres/';
  $uploadDirRel = 'uploads/cierres';

  // Estos nombres deben coincidir con el FORM:
  $rutaPoliza = guardarPdfOpcional('poliza_doc', $radicado, 'poliza', $uploadDirAbs, $uploadDirRel);
  $rutaActa   = guardarPdfOpcional('acta_inicio_doc', $radicado, 'acta_inicio', $uploadDirAbs, $uploadDirRel);

  $mysqli->begin_transaction();

  $stmt1 = $mysqli->prepare("
    INSERT INTO licitaciones_gandas
      (lc_gana_radicado, poliza_doc, numero_poliza, acta_inicio_doc, fecha_inicio, fecha_ganada)
    VALUES
      (?, ?, ?, ?, ?, NOW())
  ");
  if (!$stmt1) throw new Exception($mysqli->error);

  // 5 placeholders => 5 params
  $stmt1->bind_param("sssss", $radicado, $rutaPoliza, $numero_poliza, $rutaActa, $fecha_inicio);

  if (!$stmt1->execute()) throw new Exception($stmt1->error);
  $stmt1->close();

  $estado = 'GANADO';
  $stmt2 = $mysqli->prepare("
    UPDATE licitaciones
    SET lc_estado = ?
    WHERE lc_radicado = ?
    LIMIT 1
  ");
  if (!$stmt2) throw new Exception($mysqli->error);

  $stmt2->bind_param("ss", $estado, $radicado);
  if (!$stmt2->execute()) throw new Exception($stmt2->error);
  $stmt2->close();

  $mysqli->commit();

  echo json_encode(["success" => true, "message" => "Cierre guardado con éxito."]);
  exit;

} catch (Exception $e) {
  if (isset($mysqli)) $mysqli->rollback();
  echo json_encode(["success" => false, "message" => "Error al cerrar: " . $e->getMessage()]);
  exit;
}
