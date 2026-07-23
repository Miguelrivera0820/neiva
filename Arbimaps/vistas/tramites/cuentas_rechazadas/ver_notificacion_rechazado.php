<?php
require '../../../../conexion.php';

$cod = $_GET['cod'] ?? '';
$archivo = $_GET['archivo'] ?? '';

if (!is_string($cod) || $cod === '' || !is_string($archivo) || $archivo === '') {
    http_response_code(400);
    echo 'Parametros invalidos.';
    exit;
}

$archivo = basename($archivo);
$anio = substr($cod, 4, 4);

$rutasBase = [
    __DIR__ . "/../../../tramites_conservacion/$anio/$cod/notificaciones_rechazadas/$archivo",
    __DIR__ . "/../../../../tramites_conservacion/$anio/$cod/notificaciones_rechazadas/$archivo",
    __DIR__ . "/../../../tramites_conservacion/$anio/$cod/notificacion/$archivo",
    __DIR__ . "/../../../tramites_conservacion/$anio/$cod/notificacion_no_procede/$archivo",
];

$rutaEncontrada = null;
foreach ($rutasBase as $ruta) {
    if (is_file($ruta)) {
        $rutaEncontrada = $ruta;
        break;
    }
}

if ($rutaEncontrada === null) {
    http_response_code(404);
    echo 'Notificacion no encontrada.';
    exit;
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . rawurlencode($archivo) . '"');
header('Content-Length: ' . filesize($rutaEncontrada));
readfile($rutaEncontrada);
exit;
?>
