<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('POST');
neiva_require_permission('menu.tramites', $PERMISOS);
neiva_require_csrf('global');

function redirEvidenciaProcede(string $cod_tramite, string $msg): void
{
    $url = neiva_app_url('Arbimaps/index.php?page=tramites/cuentas_completadas/tramites_completos&msg=' . urlencode($msg) . '&cod_evid=' . urlencode($cod_tramite));
    header("Location: $url");
    exit;
}

$cod_tramite = trim($_POST['cod_tramite'] ?? '');
$archivo = $_FILES['archivo'] ?? null;

if ($cod_tramite === '' || !preg_match('/^[A-Za-z0-9_-]+$/', $cod_tramite)) {
    redirEvidenciaProcede($cod_tramite, 'error:Código de trámite no válido.');
}

if (!$archivo || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    redirEvidenciaProcede($cod_tramite, 'error:Archivo inválido.');
}

$ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
$extensiones_permitidas = ['pdf', 'jpg', 'jpeg', 'png'];
if (!in_array($ext, $extensiones_permitidas, true)) {
    redirEvidenciaProcede($cod_tramite, 'error:Solo se permiten archivos PDF o imágenes.');
}

$mime = mime_content_type($archivo['tmp_name']);
$mimes_permitidos = ['application/pdf', 'application/x-pdf', 'image/jpeg', 'image/png'];
if ($mime !== false && !in_array($mime, $mimes_permitidos, true)) {
    redirEvidenciaProcede($cod_tramite, 'error:El archivo no tiene un formato permitido.');
}

$anio = substr($cod_tramite, 4, 4);
$base_dir = "../../tramites_conservacion/$anio/$cod_tramite";
$evid_dir = "$base_dir/evidencia_procede";

if (!is_dir($evid_dir) && !mkdir($evid_dir, 0777, true)) {
    redirEvidenciaProcede($cod_tramite, 'error:No se pudo crear la carpeta de evidencia.');
}

$nombre_archivo = "evidencia_procede_{$cod_tramite}_" . date("Ymd_His") . ".$ext";
$ruta_destino = "$evid_dir/$nombre_archivo";

if (!move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
    redirEvidenciaProcede($cod_tramite, 'error:Error al mover el archivo.');
}

$sql = "UPDATE procede_tramite SET evidencia_procede = ? WHERE cod_radicacion_tramite = ?";
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    @unlink($ruta_destino);
    redirEvidenciaProcede($cod_tramite, 'error:Error al preparar la consulta.');
}

$stmt->bind_param("ss", $nombre_archivo, $cod_tramite);
if (!$stmt->execute()) {
    @unlink($ruta_destino);
    $stmt->close();
    redirEvidenciaProcede($cod_tramite, 'error:No se pudo guardar la evidencia.');
}
$stmt->close();

redirEvidenciaProcede($cod_tramite, 'success:Evidencia cargada correctamente.');
