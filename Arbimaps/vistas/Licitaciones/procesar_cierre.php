<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require __DIR__ . '/../../../conexion.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(["success" => false, "message" => "Método no permitido."]);
  exit;
}

$radicado = $_POST['radicado'] ?? '';
$motivo   = trim($_POST['motivo'] ?? '');

if ($radicado === '' || $motivo === '') {
  echo json_encode(["success" => false, "message" => "Faltan datos obligatorios."]);
  exit;
}

if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
  echo json_encode(["success" => false, "message" => "Error subiendo el archivo."]);
  exit;
}

/* SOLO PDF - 20MB */
$maxBytes = 20 * 1024 * 1024;
if ($_FILES['archivo']['size'] > $maxBytes) {
  echo json_encode(["success" => false, "message" => "El PDF supera 20MB."]);
  exit;
}

$ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
if ($ext !== 'pdf') {
  echo json_encode(["success" => false, "message" => "Solo se permite archivo PDF."]);
  exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($_FILES['archivo']['tmp_name']);
if ($mime !== 'application/pdf') {
  echo json_encode(["success" => false, "message" => "El archivo no parece ser un PDF válido."]);
  exit;
}

/* Guardar archivo */
$uploadDir = __DIR__ . '/../../../uploads/cierres/';
if (!is_dir($uploadDir)) {
  @mkdir($uploadDir, 0775, true);
}

$safeRadicado = preg_replace('/[^a-zA-Z0-9_-]/', '_', $radicado);
$newName = "cierre_{$safeRadicado}_" . date('Ymd_His') . ".pdf";

if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $uploadDir . $newName)) {
  echo json_encode(["success" => false, "message" => "No se pudo guardar el PDF."]);
  exit;
}

$rutaBD = "uploads/cierres/" . $newName;

try {
  $mysqli->begin_transaction();

  $stmt1 = $mysqli->prepare("
    INSERT INTO licitaciones_cierres (lc_cer_radicado, motivo, archivo, fecha_cierre)
    VALUES (?, ?, ?, NOW())
  ");
  if (!$stmt1) throw new Exception($mysqli->error);

  $stmt1->bind_param("sss", $radicado, $motivo, $rutaBD);
  if (!$stmt1->execute()) throw new Exception($stmt1->error);
  $stmt1->close();

  $estado = 'CERRADO';
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
  $mysqli->rollback();
  echo json_encode(["success" => false, "message" => "Error al cerrar: " . $e->getMessage()]);
  exit;
}
