<?php
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../conexion.php';

if (!isset($mysqli) || $mysqli === null) {
  http_response_code(500);
  echo json_encode(["success"=>false,"message"=>"No se encontró la conexión \$mysqli."]);
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

/* =========================
   Función guardar archivo
   (MISMA lógica que estudios)
========================= */
function guardarArchivo($archivo, $carpetaUsuario)
{
  if ($archivo && isset($archivo['error']) && $archivo['error'] === UPLOAD_ERR_OK) {
    if (!file_exists($carpetaUsuario)) {
      mkdir($carpetaUsuario, 0777, true);
    }

    $nombreArchivo = time() . "_" . basename($archivo['name']);
    $rutaDestino   = $carpetaUsuario . $nombreArchivo;

    if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
      return $rutaDestino;
    }
  }
  return null;
}

/* =========================
   Leer datos
========================= */
$data = [
  'exp_empresa'         => $_POST['exp_empresa'] ?? '',
  'exp_entidad'         => $_POST['exp_entidad'] ?? '',
  'exp_pais'            => $_POST['exp_pais'] ?? '',
  'exp_departamento'    => $_POST['exp_departamento'] ?? '',
  'exp_municipio'       => $_POST['exp_municipio'] ?? '',
  'exp_correo_entidad'  => $_POST['exp_correo_entidad'] ?? '',
  'exp_telefonos'       => $_POST['exp_telefonos'] ?? '',
  'exp_fecha_ingreso'   => $_POST['exp_fecha_ingreso'] ?? null,
  'exp_fecha_salida'    => $_POST['exp_fecha_salida'] ?? null,
  'exp_cargo'           => $_POST['exp_cargo'] ?? '',
  'exp_dependencias'    => $_POST['exp_dependencias'] ?? '',
  'exp_direccion'       => $_POST['exp_direccion'] ?? ''
];

/* =========================
   Validaciones
========================= */
$required = [
  'exp_empresa','exp_entidad','exp_pais','exp_departamento',
  'exp_municipio','exp_telefonos','exp_cargo',
  'exp_dependencias','exp_direccion'
];

foreach ($required as $f) {
  if (trim($data[$f]) === '') {
    echo json_encode(["success"=>false,"message"=>"Falta el campo obligatorio: $f"]);
    exit;
  }
}

if (!in_array($data['exp_entidad'], ['PUBLICA','PRIVADA'], true)) {
  echo json_encode(["success"=>false,"message"=>"Tipo de entidad inválido."]);
  exit;
}

if ($data['exp_correo_entidad'] !== '' &&
    !filter_var($data['exp_correo_entidad'], FILTER_VALIDATE_EMAIL)) {
  echo json_encode(["success"=>false,"message"=>"Correo electrónico inválido."]);
  exit;
}

/* =========================
   Guardar certificado (RUTA)
========================= */
$uploadDir = 'Arbitrium_experiencia/';
$carpetaUsuario = $uploadDir . $_SESSION['id_usuario'] . '/Certificado/';

$exp_certificado_laboral = isset($_FILES['exp_certificado_laboral'])
  ? guardarArchivo($_FILES['exp_certificado_laboral'], $carpetaUsuario)
  : null;

// la columna es NOT NULL
if ($exp_certificado_laboral === null) {
  $exp_certificado_laboral = '';
}

/* =========================
   INSERT (SIN con_id)
========================= */
$sql = "INSERT INTO experiencia (
  exp_empresa,
  exp_entidad,
  exp_pais,
  exp_departamento,
  exp_municipio,
  exp_correo_entidad,
  exp_telefonos,
  exp_fecha_ingreso,
  exp_fecha_salida,
  exp_cargo,
  exp_dependencias,
  exp_direccion,
  exp_certificado_laboral
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $mysqli->prepare($sql);

if (!$stmt) {
  echo json_encode([
    "success"=>false,
    "message"=>"Error al preparar la consulta",
    "error"=>$mysqli->error
  ]);
  exit;
}

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
