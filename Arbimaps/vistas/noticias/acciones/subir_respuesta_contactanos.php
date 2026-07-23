<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') die('Método no permitido');
if (!isset($_SESSION['id_usuario'])) die('Sesión no válida');

if (!isset($_POST['contactanos_id'], $_POST['tipo_archivo'], $_FILES['archivo'])) {
    die('Datos incompletos');
}

$contactanos_id = (int) $_POST['contactanos_id'];
$tipo_archivo   = $_POST['tipo_archivo'];
$archivo        = $_FILES['archivo'];

if ($contactanos_id <= 0) die('ID inválido');
if (!in_array($tipo_archivo, ['foto', 'documento'], true)) die('Tipo inválido');
if ($archivo['error'] !== UPLOAD_ERR_OK) die('Error al subir archivo');


$stmtTel = $mysqli->prepare("SELECT numero_telefono FROM contactanos WHERE id = ?");
if (!$stmtTel) die("Error preparando consulta: " . $mysqli->error);

$stmtTel->bind_param("i", $contactanos_id);
$stmtTel->execute();
$resTel = $stmtTel->get_result();
$rowTel = $resTel->fetch_assoc();
$stmtTel->close();

if (!$rowTel) die("La solicitud no existe");

$telefonoRaw = (string)$rowTel['numero_telefono'];


$telefono = preg_replace('/\D+/', '', $telefonoRaw);
if ($telefono === '') $telefono = 'sin_tel';

$extPermitidas = ($tipo_archivo === 'foto')
    ? ['jpg', 'jpeg', 'png', 'webp']
    : ['pdf', 'doc', 'docx', 'xls', 'xlsx'];

$extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
if (!in_array($extension, $extPermitidas, true)) die('Extensión no permitida');

$folderSolicitud = "sol_{$contactanos_id}_tel_{$telefono}/";
$subFolder = ($tipo_archivo === 'foto') ? "imagenes/" : "documentos/";

$basePathFisico = __DIR__ . '/../imagenes/contactanos/';
$rutaDestinoFisico = $basePathFisico . $folderSolicitud . $subFolder;

if (!is_dir($rutaDestinoFisico)) {
    if (!mkdir($rutaDestinoFisico, 0775, true)) {
        die('No se pudo crear la carpeta destino');
    }
}

$prefijo = ($tipo_archivo === 'foto') ? 'foto' : 'doc';
$nombreFinal = "{$prefijo}_contactanos_{$contactanos_id}_" . time() . ".{$extension}";

$rutaFinalFisico = $rutaDestinoFisico . $nombreFinal;

if (!move_uploaded_file($archivo['tmp_name'], $rutaFinalFisico)) {
    die('No se pudo guardar el archivo');
}
$rutaBD = 'vistas/noticias/imagenes/contactanos/' . $folderSolicitud . $subFolder . $nombreFinal;

if ($tipo_archivo === 'foto') {
    $stmt = $mysqli->prepare("
        UPDATE contactanos
        SET tipo_respuesta = 'foto',
            imagen = ?,
            estado_respuesta = 'CONTESTADA'
        WHERE id = ?
    ");
} else {
    $stmt = $mysqli->prepare("
        UPDATE contactanos
        SET tipo_respuesta = 'documento',
            documento_respuesta = ?,
            estado_respuesta = 'CONTESTADA'
        WHERE id = ?
    ");
}

if (!$stmt) die("Error preparando update: " . $mysqli->error);

$stmt->bind_param("si", $rutaBD, $contactanos_id);

if (!$stmt->execute()) {
    die("Error en BD: " . $stmt->error);
}

$stmt->close();

header("Location: /arbimaps/Arbimaps/index.php?page=noticias/vistas/solicitudes_contactanos&ok=" . urlencode("Archivo cargado correctamente"));
exit;
