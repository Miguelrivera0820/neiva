<?php
require_once __DIR__ . '/base_catastral_lib.php';

if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    http_response_code(403);
    die('Sesion expirada o no iniciada.');
}

$cod_tramite = $_GET['cod'] ?? '';
$path = bc_temp_xlsx_path($cod_tramite);

if ($cod_tramite === '' || !file_exists($path)) {
    http_response_code(404);
    die('No existe Excel temporal para este tramite.');
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="base_catastral_' . bc_safe_code($cod_tramite) . '.xlsx"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
