<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../../../../conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo 'No autenticado';
    exit;
}

$idSolicitud = $_GET['id_solicitud'] ?? null;
$archivo = $_GET['archivo'] ?? null;
$preview = $_GET['preview'] ?? 0;

if (!$idSolicitud || !$archivo) {
    http_response_code(400);
    echo 'Parámetros inválidos';
    exit;
}

if (!is_numeric($idSolicitud)) {
    http_response_code(400);
    echo 'ID de solicitud inválido';
    exit;
}

// Obtener sb_year
$sqlYear = "SELECT sb_year FROM solicitud_baqueanos WHERE id = ? LIMIT 1";
$stmtYear = $mysqli->prepare($sqlYear);
if (!$stmtYear) {
    http_response_code(500);
    echo 'Error en la consulta';
    exit;
}

$stmtYear->bind_param("i", $idSolicitud);
$stmtYear->execute();
$resYear = $stmtYear->get_result();
$rowYear = $resYear->fetch_assoc();
$stmtYear->close();

$sb_year = $rowYear['sb_year'] ?? null;
$año_actual = (int)$sb_year;

if ($año_actual < 2000 || $año_actual > 2100) {
    http_response_code(400);
    echo 'Año inválido';
    exit;
}

$mes_actual = date('m');
$base_path = __DIR__ . '/../DOCUMENTOS_BAQUENOS/soporte_pago/';
$carpeta_solicitud = $base_path . $año_actual . '/' . $mes_actual . '/RAD_' . $idSolicitud . '/';

// Validar nombre de archivo (evitar path traversal)
$nombre_archivo = basename($archivo);
$ruta_archivo = $carpeta_solicitud . $nombre_archivo;

// Verificar que el archivo existe y está dentro de la carpeta permitida
if (!file_exists($ruta_archivo) || strpos(realpath($ruta_archivo), realpath($carpeta_solicitud)) !== 0) {
    http_response_code(404);
    echo 'Archivo no encontrado';
    exit;
}

// Obtener extensión del archivo
$ext = strtolower(pathinfo($ruta_archivo, PATHINFO_EXTENSION));

// Mapeo de tipos MIME
$mimeTypes = [
    'pdf' => 'application/pdf',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
    'txt' => 'text/plain',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls' => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];

$mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';

// Si es vista previa, mostrar en línea
if ($preview == 1) {
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: inline; filename="' . basename($ruta_archivo) . '"');
    header('Cache-Control: public, max-age=3600');
    header('Content-Length: ' . filesize($ruta_archivo));
    readfile($ruta_archivo);
} else {
    // Si es descarga, descargar
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($ruta_archivo) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($ruta_archivo));
    readfile($ruta_archivo);
}
exit;
?>
