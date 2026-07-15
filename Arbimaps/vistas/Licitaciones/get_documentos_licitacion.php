<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require __DIR__ . '/../../../conexion.php';

mysqli_set_charset($mysqli, 'utf8mb4');
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
error_reporting(E_ALL);

$radicado = trim($_GET['radicado'] ?? '');
if ($radicado === '') {
  echo json_encode(['success' => false, 'message' => 'Radicado no válido']);
  exit;
}

$sql = "
  SELECT id, doc_tipo, doc_ruta, fecha_subida
  FROM documentos_licitacion
  WHERE TRIM(lc_doc_radicado) = TRIM(?)
  ORDER BY fecha_subida DESC, id DESC
";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
  echo json_encode(['success' => false, 'message' => 'Error preparando SQL', 'error' => $mysqli->error]);
  exit;
}

$stmt->bind_param("s", $radicado);

if (!$stmt->execute()) {
  echo json_encode(['success' => false, 'message' => 'Error ejecutando SQL', 'error' => $stmt->error]);
  exit;
}

$res = $stmt->get_result();

$docs = [];
$docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/'); // ej: C:/xampp/htdocs
$baseUrlPrefix = ''; // si tu proyecto vive dentro de /arbimaps/Arbimaps, no necesitas hardcodearlo aquí

while ($row = $res->fetch_assoc()) {
  $ruta = $row['doc_ruta'] ?? '';

  // Normaliza slashes
  $rutaNorm = str_replace('\\', '/', $ruta);

  // 1) Si es una ruta física (contiene docRoot), conviértela a URL relativa
  if ($rutaNorm && stripos($rutaNorm, $docRoot) === 0) {
    $rutaNorm = substr($rutaNorm, strlen($docRoot)); // queda: /arbimaps/Arbimaps/...
  }

  // 2) Asegura que empiece por /
  if ($rutaNorm && $rutaNorm[0] !== '/') {
    $rutaNorm = '/' . $rutaNorm;
  }

  // 3) (Opcional) Si tu doc_ruta a veces guarda cosas como "Arbimaps/vistas/.."
  // puedes forzar prefijo base así (descomenta si lo necesitas):
  // if ($baseUrlPrefix && stripos($rutaNorm, $baseUrlPrefix) !== 0) {
  //   $rutaNorm = rtrim($baseUrlPrefix, '/') . '/' . ltrim($rutaNorm, '/');
  // }

  $name = basename($rutaNorm);

  $docs[] = [
    'id'    => (int)$row['id'],
    'tipo'  => $row['doc_tipo'] ?? '',
    'name'  => $name ?: 'Documento',
    'url'   => $rutaNorm,
    'fecha' => $row['fecha_subida'] ?? ''
  ];
}

$stmt->close();

echo json_encode(['success' => true, 'docs' => $docs], JSON_UNESCAPED_UNICODE);
exit;
