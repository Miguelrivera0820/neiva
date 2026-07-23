<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../../../../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

$idSolicitud = $_POST['id_solicitud'] ?? null;

if (!$idSolicitud) {
    echo json_encode(['success' => false, 'message' => 'ID de solicitud no proporcionado']);
    exit;
}

$sql = "SELECT sb_soporte_pago FROM solicitud_baqueanos WHERE id = ? LIMIT 1";
$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error preparando consulta: ' . $mysqli->error]);
    exit;
}

$stmt->bind_param("i", $idSolicitud);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Solicitud no encontrada']);
    $stmt->close();
    exit;
}

$row = $result->fetch_assoc();
$stmt->close();

$soporte = $row['sb_soporte_pago'] ?? null;

if (!$soporte) {
    echo json_encode(['success' => false, 'message' => 'No hay soporte de pago cargado']);
    exit;
}

// Detectar el tipo MIME basado en el contenido
$mimeType = 'application/octet-stream';
$finfo = finfo_open(FILEINFO_MIME_TYPE);
if ($finfo) {
    $mimeType = finfo_buffer($finfo, $soporte) ?: 'application/octet-stream';
    finfo_close($finfo);
}

// Si no se detecta, intentar por signatures comunes
if ($mimeType === 'application/octet-stream') {
    $header = substr($soporte, 0, 4);
    
    // PDF: %PDF
    if (strpos($soporte, '%PDF') === 0) {
        $mimeType = 'application/pdf';
    }
    // JPEG: FFD8FF
    elseif ($header === "\xFF\xD8\xFF\xE0" || $header === "\xFF\xD8\xFF\xE1") {
        $mimeType = 'image/jpeg';
    }
    // PNG: 89504E47
    elseif ($header === "\x89PNG") {
        $mimeType = 'image/png';
    }
    // GIF: GIF87a o GIF89a
    elseif (substr($soporte, 0, 6) === 'GIF87a' || substr($soporte, 0, 6) === 'GIF89a') {
        $mimeType = 'image/gif';
    }
}

// Convertir a base64
$base64 = base64_encode($soporte);

echo json_encode([
    'success' => true,
    'soporte' => $base64,
    'mimeType' => $mimeType,
    'nombreArchivo' => 'Soporte de pago cargado'
]);

$mysqli->close();
?>
