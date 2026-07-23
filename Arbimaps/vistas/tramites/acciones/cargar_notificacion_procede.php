<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('POST');
neiva_require_permission('menu.tramites', $PERMISOS);
neiva_require_csrf('global');

function redirNotificacionProcede(string $cod_tramite, string $msg): void
{
    $url = neiva_app_url('Arbimaps/index.php?page=tramites/cuentas_completadas/tramites_completos&action=ver&cod=' . urlencode($cod_tramite) . '&msg=' . urlencode($msg));
    header("Location: $url");
    exit;
}

if (!isset($mysqli) || $mysqli === null) {
    die("Error: Conexión no establecida.");
}

$cod_tramite = trim($_POST['cod_tramite'] ?? '');
if ($cod_tramite === '' || !preg_match('/^[A-Za-z0-9_-]+$/', $cod_tramite)) {
    redirNotificacionProcede($cod_tramite, "error:Código de trámite no válido.");
}

if (!usuarioTieneAlgunRol(['ventanilla_catastral', 'administrador'])) {
    redirNotificacionProcede($cod_tramite, "error:El recibido de notificación solo lo puede cargar ventanilla catastral o administrador.");
}

if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    redirNotificacionProcede($cod_tramite, "error:Error al subir el archivo.");
}

$archivo = $_FILES['archivo'];
$ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
if ($ext !== 'pdf') {
    redirNotificacionProcede($cod_tramite, "error:Solo se permiten archivos PDF.");
}

$mime = mime_content_type($archivo['tmp_name']);
if ($mime !== false && !in_array($mime, ['application/pdf', 'application/x-pdf'], true)) {
    redirNotificacionProcede($cod_tramite, "error:El archivo no parece ser un PDF válido.");
}

$ruta_notificacion_final = dirname(__DIR__, 3) . '/archivos/notificaciones';
if (!is_dir($ruta_notificacion_final) && !mkdir($ruta_notificacion_final, 0777, true)) {
    redirNotificacionProcede($cod_tramite, "error:No se pudo crear la carpeta del recibido final.");
}

$timestamp = date('Ymd_His');
$cod_seguro = preg_replace('/[^A-Za-z0-9_-]/', '_', $cod_tramite);
$nombre_archivo = "{$cod_seguro}_recibido_notificacion_{$timestamp}.pdf";
$ruta_destino_final = $ruta_notificacion_final . '/' . $nombre_archivo;

if (!move_uploaded_file($archivo['tmp_name'], $ruta_destino_final)) {
    redirNotificacionProcede($cod_tramite, "error:Error al guardar el archivo en el sistema.");
}

$sql_resolucion = "SELECT r.id_resoluciones
                   FROM resoluciones r
                   INNER JOIN entrega_asignacion ea ON ea.id_entrega_asignacion = r.id_entrega_asignacion
                   WHERE ea.entrega_cod_tramite = ?
                   ORDER BY r.id_resoluciones DESC
                   LIMIT 1";
$stmt_resolucion = $mysqli->prepare($sql_resolucion);
if (!$stmt_resolucion) {
    @unlink($ruta_destino_final);
    redirNotificacionProcede($cod_tramite, "error:Error al buscar la resolución del trámite.");
}

$stmt_resolucion->bind_param("s", $cod_tramite);
$stmt_resolucion->execute();
$res_resolucion = $stmt_resolucion->get_result();
$row_resolucion = $res_resolucion->fetch_assoc();
$stmt_resolucion->close();

if (empty($row_resolucion['id_resoluciones'])) {
    @unlink($ruta_destino_final);
    redirNotificacionProcede($cod_tramite, "error:No existe una resolución asociada para guardar el recibido.");
}

$id_resolucion = (int)$row_resolucion['id_resoluciones'];
$sql_update_resolucion = "UPDATE resoluciones SET notificacion = ? WHERE id_resoluciones = ?";
$stmt_update_resolucion = $mysqli->prepare($sql_update_resolucion);
if (!$stmt_update_resolucion) {
    @unlink($ruta_destino_final);
    redirNotificacionProcede($cod_tramite, "error:Error al preparar la actualización de resolución.");
}

$stmt_update_resolucion->bind_param("si", $nombre_archivo, $id_resolucion);
if (!$stmt_update_resolucion->execute()) {
    @unlink($ruta_destino_final);
    $stmt_update_resolucion->close();
    redirNotificacionProcede($cod_tramite, "error:No se pudo guardar el recibido final.");
}
$stmt_update_resolucion->close();

$anio = substr($cod_tramite, 4, 4);
$ruta_base_tramite = dirname(__DIR__, 2) . "/tramites_conservacion/$anio/$cod_tramite";
$ruta_notificacion_procede = "$ruta_base_tramite/notificacion_procede";
if (!is_dir($ruta_notificacion_procede)) {
    mkdir($ruta_notificacion_procede, 0777, true);
}

$ruta_destino_procede = "$ruta_notificacion_procede/$nombre_archivo";
@copy($ruta_destino_final, $ruta_destino_procede);

$contenido_archivo = @file_get_contents($ruta_destino_final);
$sql_update_procede = "UPDATE procede_tramite SET notificacion_procede = ?, notificacion_procede_nombre = ? WHERE cod_radicacion_tramite = ?";
$stmt_update_procede = $mysqli->prepare($sql_update_procede);
if ($stmt_update_procede && $contenido_archivo !== false) {
    $null = null;
    $stmt_update_procede->bind_param("bss", $null, $nombre_archivo, $cod_tramite);
    $stmt_update_procede->send_long_data(0, $contenido_archivo);
    $stmt_update_procede->execute();
    $stmt_update_procede->close();
}

redirNotificacionProcede($cod_tramite, "success:Recibido de notificación final cargado correctamente.");
