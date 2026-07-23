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
    __DIR__ . "/../../../tramites_conservacion/no_procede_completar/$anio/$cod/notificaciones/$archivo",
    __DIR__ . "/../../../../tramites_conservacion/no_procede_completar/$anio/$cod/notificaciones/$archivo",
];

$rutaEncontrada = null;
foreach ($rutasBase as $ruta) {
    if (is_file($ruta)) {
        $rutaEncontrada = $ruta;
        break;
    }
}

if ($rutaEncontrada === null) {
    $sql = "SELECT notificacion FROM no_procede_completar WHERE cod_radicacion_tramite = ? AND LENGTH(notificacion) > 0 ORDER BY id_completar DESC LIMIT 1";
    $stmt = $mysqli->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $cod);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        $stmt->close();

        if (!empty($fila['notificacion'])) {
            $blob = $fila['notificacion'];
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . rawurlencode($archivo) . '"');
            header('Content-Length: ' . strlen($blob));
            echo $blob;
            exit;
        }
    }

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
