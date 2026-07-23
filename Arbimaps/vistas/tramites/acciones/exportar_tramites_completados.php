<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$baseAplicacion = realpath(dirname(__DIR__, 4));
$conexionFile = $baseAplicacion !== false ? $baseAplicacion . '/conexion.php' : '';
if ($baseAplicacion === false || !is_file($conexionFile)) {
    http_response_code(500);
    die('No fue posible localizar la configuracion de la aplicacion.');
}
require $conexionFile;

if (!isset($mysqli) || $mysqli === null) {
    http_response_code(500);
    die('No fue posible establecer la conexion con la base de datos.');
}

$rolesPermitidos = ['administrador', 'director_proyectos', 'procedencia_juridica'];
$rolesUsuario = array_filter([
    $_SESSION['rol_usuario'] ?? '',
    $_SESSION['rol_usuario_dos'] ?? '',
    $_SESSION['rol_usuario_tres'] ?? '',
]);
if (count(array_intersect($rolesPermitidos, $rolesUsuario)) < 1) {
    http_response_code(403);
    die('No tiene permiso para exportar tramites completados.');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['exportar_tramites_completados'])) {
    http_response_code(405);
    header('Allow: POST');
    die('Metodo no permitido.');
}
if (!class_exists('ZipArchive')) {
    http_response_code(500);
    die('La extension ZipArchive no esta disponible en el servidor.');
}

$csrf = $_POST['csrf_token'] ?? '';
$csrfSesion = $_SESSION['csrf_cancelar_tramites'] ?? '';
if (!is_string($csrf) || $csrfSesion === '' || !hash_equals($csrfSesion, $csrf)) {
    http_response_code(419);
    die('La sesion del formulario vencio. Recargue la pagina e intente nuevamente.');
}

$codigos = $_POST['codigos'] ?? [];
$codigos = is_array($codigos)
    ? array_values(array_unique(array_filter(array_map('trim', $codigos))))
    : [];
if (count($codigos) < 1 || count($codigos) > 200) {
    http_response_code(422);
    die('Seleccione entre 1 y 200 tramites.');
}
foreach ($codigos as $codigo) {
    if (!preg_match('/^[A-Za-z0-9_-]{1,30}$/', $codigo)) {
        http_response_code(422);
        die('Uno de los codigos seleccionados no es valido.');
    }
}

$resolverArchivo = static function (string $ruta) use ($baseAplicacion): ?string {
    $ruta = ltrim(str_replace('\\', '/', trim($ruta)), '/');
    if ($ruta === '' || strpos($ruta, '..') !== false) {
        return null;
    }
    $rutaReal = realpath($baseAplicacion . '/' . $ruta);
    $prefijoBase = rtrim(str_replace('\\', '/', $baseAplicacion), '/') . '/';
    $rutaNormalizada = $rutaReal === false ? '' : str_replace('\\', '/', $rutaReal);
    if ($rutaReal === false || !is_file($rutaReal) || stripos($rutaNormalizada, $prefijoBase) !== 0) {
        return null;
    }
    return $rutaReal;
};

$stmtNotificacion = $mysqli->prepare(
    "SELECT r.notificacion
     FROM resoluciones r
     INNER JOIN entrega_asignacion ea ON ea.id_entrega_asignacion = r.id_entrega_asignacion
     WHERE ea.entrega_cod_tramite = ?
       AND r.notificacion IS NOT NULL AND r.notificacion <> ''
     ORDER BY r.id_resoluciones DESC LIMIT 1"
);
$stmtOficio = $mysqli->prepare(
    "SELECT d.doc1
     FROM doc_entrega_asignacion d
     INNER JOIN usuarios_cons u ON u.cedula_usuario = d.doc_cedula_usuario
     WHERE d.cod_tramite = ? AND u.rol_usuario = 'director_catastro'
       AND d.doc1 IS NOT NULL AND d.doc1 <> ''
     ORDER BY d.id_doc_entrega DESC LIMIT 1"
);
if (!$stmtNotificacion || !$stmtOficio) {
    http_response_code(500);
    die('No fue posible preparar la consulta de los documentos a exportar.');
}

$paquetes = [];
$errores = [];
foreach ($codigos as $codigo) {
    $notificacion = '';
    $stmtNotificacion->bind_param('s', $codigo);
    $stmtNotificacion->execute();
    $stmtNotificacion->bind_result($notificacionDb);
    if ($stmtNotificacion->fetch()) {
        $notificacion = (string) $notificacionDb;
    }
    $stmtNotificacion->free_result();
    if ($notificacion === '') {
        $errores[] = $codigo . ': no tiene notificacion de recibido';
        continue;
    }

    $oficio = '';
    $stmtOficio->bind_param('s', $codigo);
    $stmtOficio->execute();
    $stmtOficio->bind_result($oficioDb);
    if ($stmtOficio->fetch()) {
        $oficio = (string) $oficioDb;
    }
    $stmtOficio->free_result();

    $anio = preg_match('/^CAT-(\d{4})-/', $codigo, $coincidencia)
        ? $coincidencia[1]
        : substr($codigo, 4, 4);
    $archivoOficio = $oficio !== ''
        ? $resolverArchivo("tramites_conservacion/$anio/$codigo/tramites_revision/director_catastro/" . basename($oficio))
        : null;
    $archivoNotificacion = $resolverArchivo('archivos/notificaciones/' . basename($notificacion));

    if ($archivoOficio === null) {
        $errores[] = $codigo . ': no se encontro el oficio de solicitud del director';
    }
    if ($archivoNotificacion === null) {
        $errores[] = $codigo . ': no se encontro el archivo de notificacion de recibido';
    }
    if ($archivoOficio !== null && $archivoNotificacion !== null) {
        $paquetes[$codigo] = [
            'oficio' => $archivoOficio,
            'oficio_nombre' => basename($oficio),
            'notificacion' => $archivoNotificacion,
            'notificacion_nombre' => basename($notificacion),
        ];
    }
}
$stmtNotificacion->close();
$stmtOficio->close();

if ($errores) {
    http_response_code(422);
    die("No fue posible crear el ZIP:\n" . implode("\n", $errores));
}

$zipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tramites_cierre_' . bin2hex(random_bytes(8)) . '.zip';
$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    http_response_code(500);
    die('No fue posible crear el ZIP de cierre.');
}
foreach ($paquetes as $codigo => $paquete) {
    $carpetaZip = preg_replace('/[^A-Za-z0-9_-]/', '_', $codigo);
    $zip->addEmptyDir($carpetaZip);
    $zip->addFile($paquete['oficio'], $carpetaZip . '/oficio_solicitud_' . $paquete['oficio_nombre']);
    $zip->addFile($paquete['notificacion'], $carpetaZip . '/notificacion_recibido_' . $paquete['notificacion_nombre']);
}
if (!$zip->close()) {
    @unlink($zipPath);
    http_response_code(500);
    die('No fue posible finalizar el ZIP de cierre.');
}

clearstatcache(true, $zipPath);
$zipSize = filesize($zipPath);
if ($zipSize === false || $zipSize < 22) {
    @unlink($zipPath);
    http_response_code(500);
    die('El ZIP de cierre fue generado vacio o invalido.');
}

$filename = 'tramites_completados_cierre_' . date('Ymd_His') . '.zip';
while (ob_get_level() > 0) {
    ob_end_clean();
}
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . $zipSize);
header('Cache-Control: max-age=0');
header('X-Content-Type-Options: nosniff');
readfile($zipPath);
@unlink($zipPath);
exit;
