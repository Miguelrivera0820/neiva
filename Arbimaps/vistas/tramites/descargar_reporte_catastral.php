<?php
date_default_timezone_set('America/Bogota');

require_once __DIR__ . '/../../includes/auth.php';

$registro = $_GET['registro'] ?? '';
if (!in_array($registro, ['1', '2'], true)) {
    http_response_code(400);
    exit('Reporte no valido.');
}

$baseDir = dirname(__DIR__, 3) . '/dat_neiva';
$pattern = $baseDir . '/CATASTRAL_REGISTRO' . $registro . '_*.xlsx';
$files = glob($pattern);

if (!$files) {
    http_response_code(404);
    exit('No se encontro el archivo del reporte R' . $registro . '.');
}

usort($files, function ($a, $b) {
    return filemtime($b) <=> filemtime($a);
});

$filePath = $files[0];
if (!is_file($filePath) || !is_readable($filePath)) {
    http_response_code(404);
    exit('El archivo del reporte no esta disponible.');
}

$downloadName = 'CATASTRAL_REGISTRO' . $registro . '_' . date('Ymd') . '.xlsx';

if (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

readfile($filePath);
exit;
