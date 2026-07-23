<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../../../../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

$idSolicitud = $_GET['id_solicitud'] ?? null;
if (!$idSolicitud) {
    echo json_encode(['success' => false, 'message' => 'ID de solicitud no proporcionado.']);
    exit;
}

// Validar que sea numérico
if (!is_numeric($idSolicitud)) {
    echo json_encode(['success' => false, 'message' => 'ID de solicitud inválido.']);
    exit;
}

// Obtener sb_year
$sqlYear = "SELECT sb_year FROM solicitud_baqueanos WHERE id = ? LIMIT 1";
$stmtYear = $mysqli->prepare($sqlYear);
if (!$stmtYear) {
    echo json_encode(['success' => false, 'message' => 'Error preparando consulta: ' . $mysqli->error]);
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
    echo json_encode(['success' => false, 'message' => 'Año inválido', 'documentos' => []]);
    exit;
}

$mes_actual = date('m');
$base_path = __DIR__ . '/../DOCUMENTOS_BAQUENOS/soporte_pago/';
$carpeta_solicitud = $base_path . $año_actual . '/' . $mes_actual . '/RAD_' . $idSolicitud . '/';

$documentos = [];

if (file_exists($carpeta_solicitud)) {
    $archivos = array_diff(scandir($carpeta_solicitud), ['.', '..']);
    
    foreach ($archivos as $archivo) {
        $ruta_completa = $carpeta_solicitud . $archivo;
        
        if (is_file($ruta_completa)) {
            $tamaño = filesize($ruta_completa);
            $fecha_modificacion = filemtime($ruta_completa);
            
            $documentos[] = [
                'nombre' => $archivo,
                'tamaño' => $tamaño,
                'tamaño_formato' => formatearTamaño($tamaño),
                'fecha' => date('d/m/Y H:i', $fecha_modificacion),
                'url_descarga' => 'descargar_soporte.php?id_solicitud=' . $idSolicitud . '&archivo=' . urlencode($archivo)
            ];
        }
    }
    
    // Ordenar por fecha (más recientes primero)
    usort($documentos, function($a, $b) {
        return $b['fecha'] <=> $a['fecha'];
    });
}

echo json_encode([
    'success' => true,
    'documentos' => $documentos,
    'total' => count($documentos)
]);

function formatearTamaño($bytes) {
    $unidades = ['B', 'KB', 'MB', 'GB'];
    $tamaño = $bytes;
    $unidad = 0;
    
    while ($tamaño >= 1024 && $unidad < count($unidades) - 1) {
        $tamaño /= 1024;
        $unidad++;
    }
    
    return round($tamaño, 2) . ' ' . $unidades[$unidad];
}
?>
