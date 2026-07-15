<?php
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../conexion.php';

if (!isset($mysqli) || $mysqli === null) {
  http_response_code(500);
  echo json_encode(["success"=>false,"message"=>"No se encontró \$mysqli. Revisa conexion.php"]);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(["success"=>false,"message"=>"Método no permitido."]);
  exit;
}

if (!isset($_SESSION['id_usuario'])) {
  echo json_encode(["success"=>false,"message"=>"Usuario no autenticado."]);
  exit;
}

function toDatetimeOrNull($value) {
  if (empty($value)) return null;

  $dt = DateTime::createFromFormat('d/m/Y', $value);
  if ($dt) return $dt->format('Y-m-d 00:00:00');

  $dt = DateTime::createFromFormat('Y-m-d', $value);
  if ($dt) return $dt->format('Y-m-d 00:00:00');

  $dt = DateTime::createFromFormat('Y-m-d H:i:s', $value);
  if ($dt) return $dt->format('Y-m-d H:i:s');

  return null;
}

$data = [
  'exp_empresa'         => $_POST['exp_empresa'] ?? null,
  'exp_entidad'         => $_POST['exp_entidad'] ?? null,
  'exp_pais'            => $_POST['exp_pais'] ?? null,
  'exp_departamento'    => $_POST['exp_departamento'] ?? null,
  'exp_municipio'       => $_POST['exp_municipio'] ?? null,
  'exp_correo_entidad'  => $_POST['exp_correo_entidad'] ?? null,
  'exp_telefonos'       => $_POST['exp_telefonos'] ?? null,
  'exp_fecha_ingreso'   => $_POST['exp_fecha_ingreso'] ?? null,
  'exp_fecha_salida'    => $_POST['exp_fecha_salida'] ?? null,
  'exp_cargo'           => $_POST['exp_cargo'] ?? null,
  'exp_dependencias'    => $_POST['exp_dependencias'] ?? null,
  'exp_direccion'       => $_POST['exp_direccion'] ?? null,
];

$required = [
  'exp_empresa','exp_entidad','exp_pais','exp_departamento','exp_municipio',
  'exp_telefonos','exp_cargo','exp_dependencias','exp_direccion'
];
foreach ($required as $f) {
  if (empty($data[$f])) {
    echo json_encode(["success"=>false,"message"=>"Falta el campo obligatorio: $f"]);
    exit;
  }
}

if (!in_array($data['exp_entidad'], ['PUBLICA','PRIVADA'], true)) {
  echo json_encode(["success"=>false,"message"=>"Tipo de entidad inválido."]);
  exit;
}

if (!empty($data['exp_correo_entidad']) && !filter_var($data['exp_correo_entidad'], FILTER_VALIDATE_EMAIL)) {
  echo json_encode(["success"=>false,"message"=>"Correo electrónico inválido."]);
  exit;
}

$ing = toDatetimeOrNull($data['exp_fecha_ingreso']);
$sal = toDatetimeOrNull($data['exp_fecha_salida']);

if (!empty($data['exp_fecha_ingreso']) && $ing === null) {
  echo json_encode(["success"=>false,"message"=>"Fecha de ingreso inválida. Usa dd/mm/yyyy o yyyy-mm-dd."]);
  exit;
}
if (!empty($data['exp_fecha_salida']) && $sal === null) {
  echo json_encode(["success"=>false,"message"=>"Fecha de retiro inválida. Usa dd/mm/yyyy o yyyy-mm-dd."]);
  exit;
}
if (!empty($ing) && !empty($sal) && $sal < $ing) {
  echo json_encode(["success"=>false,"message"=>"La fecha de retiro no puede ser menor que la de ingreso."]);
  exit;
}

$data['exp_fecha_ingreso'] = $ing;
$data['exp_fecha_salida']  = $sal;

// =========================
// PDF a BLOB (longblob)
// =========================
$exp_certificado_laboral = null;

if (isset($_FILES['exp_certificado_laboral']) && $_FILES['exp_certificado_laboral']['error'] !== UPLOAD_ERR_NO_FILE) {

  $file = $_FILES['exp_certificado_laboral'];

  if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["success"=>false,"message"=>"Error subiendo el archivo."]);
    exit;
  }

  // 20MB
  $maxSize = 20 * 1024 * 1024;
  if ($file['size'] > $maxSize) {
    echo json_encode(["success"=>false,"message"=>"El archivo excede 20 MB."]);
    exit;
  }

  // validar mime real
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime = $finfo->file($file['tmp_name']);
  if ($mime !== 'application/pdf') {
    echo json_encode(["success"=>false,"message"=>"Solo se permite PDF."]);
    exit;
  }

  // leer bytes para longblob
  $exp_certificado_laboral = file_get_contents($file['tmp_name']);
  if ($exp_certificado_laboral === false) {
    echo json_encode(["success"=>false,"message"=>"No se pudo leer el PDF."]);
    exit;
  }
}

// =========================
// INSERT en experiencia (exp_*)
// =========================
$sql = "INSERT INTO experiencia (
  exp_empresa, exp_entidad, exp_pais, exp_departamento, exp_municipio,
  exp_correo_entidad, exp_telefonos, exp_fecha_ingreso, exp_fecha_salida,
  exp_cargo, exp_dependencias, exp_direccion, exp_certificado_laboral
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
  echo json_encode(["success"=>false,"message"=>"Error al preparar la consulta","error"=>$mysqli->error]);
  exit;
}

/**
 * Para BLOB en mysqli:
 * - bind_param acepta string, pero para BLOB grande es mejor send_long_data
 */
$stmt->bind_param(
  "sssssssssssss",
  $data['exp_empresa'],
  $data['exp_entidad'],
  $data['exp_pais'],
  $data['exp_departamento'],
  $data['exp_municipio'],
  $data['exp_correo_entidad'],
  $data['exp_telefonos'],
  $data['exp_fecha_ingreso'],
  $data['exp_fecha_salida'],
  $data['exp_cargo'],
  $data['exp_dependencias'],
  $data['exp_direccion'],
  $exp_certificado_laboral
);

// Si hay PDF, envíalo como long data (posición 12 porque empieza en 0)
if ($exp_certificado_laboral !== null) {
  $stmt->send_long_data(12, $exp_certificado_laboral);
}

if ($stmt->execute()) {
  echo json_encode([
    "success" => true,
    "message" => "Experiencia guardada correctamente.",
    "id_experiencia" => $stmt->insert_id
  ]);
} else {
  echo json_encode([
    "success" => false,
    "message" => "Error al guardar.",
    "error" => $stmt->error
  ]);
}

$stmt->close();
$mysqli->close();
