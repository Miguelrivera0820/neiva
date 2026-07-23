<?php
require_once __DIR__ . '/../../../includes/bootstrap.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('POST');
neiva_require_permission('soporte.ticket');
neiva_require_csrf('global');

$id_usuario = (int) ($_POST['id_usuario'] ?? 0);
$codigo_error = trim((string) ($_POST['codigo_error'] ?? ''));
$asunto = trim((string) ($_POST['asunto'] ?? ''));
$descripcion = trim((string) ($_POST['descripcion'] ?? ''));
$prioridad = trim((string) ($_POST['prioridad'] ?? ''));
$tipo_error = trim((string) ($_POST['tipo_error'] ?? ''));
$nombre_solicitante = trim((string) ($_POST['nombre_solicitante'] ?? ''));
$apellido_solicitante = trim((string) ($_POST['apellido_solicitante'] ?? ''));
$correo_solicitante = trim((string) ($_POST['correo_solicitante'] ?? ''));
$celular_solicitante = trim((string) ($_POST['celular_solicitante'] ?? ''));
$estado_error = 'PENDIENTE';

if ($id_usuario <= 0 || $codigo_error === '' || $asunto === '' || $descripcion === '' || $prioridad === '' || $tipo_error === '') {
    die('Faltan datos obligatorios.');
}

$imagen_ruta = null;
if (isset($_FILES['imagen']) && ($_FILES['imagen']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    $imagenValidada = neiva_validate_upload($_FILES['imagen'], [
        'required' => false,
        'max_bytes' => 5 * 1024 * 1024,
        'allowed_mimes' => [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        ],
        'image_decode' => true,
    ]);

    if (!empty($imagenValidada)) {
        $codigo_carpeta = preg_replace('/[^A-Za-z0-9_-]/', '_', $codigo_error);
        $carpeta_base = __DIR__ . '/../reportes_imagenes/';
        $carpeta_destino = $carpeta_base . $codigo_carpeta . '/';
        $carpeta_relativa = 'reportes_imagenes/' . $codigo_carpeta . '/';

        neiva_ensure_directory($carpeta_destino);

        $nombre_nuevo = bin2hex(random_bytes(16)) . '.' . $imagenValidada['extension'];
        $ruta_fisica = $carpeta_destino . $nombre_nuevo;
        $ruta_bd = $carpeta_relativa . $nombre_nuevo;

        if (!move_uploaded_file($imagenValidada['tmp_name'], $ruta_fisica)) {
            die('No se pudo guardar la imagen en el servidor.');
        }

        $imagen_ruta = $ruta_bd;
    }
}

$sql = "INSERT INTO solicitud_soporte
                (
                    id_usuario,
                    codigo_error,
                    asunto,
                    descripcion,
                    prioridad,
                    tipo_error,
                    estado_error,
                    nombre_solicitante,
                    apellido_solicitante,
                    correo_solicitante,
                    celular_solicitante,
                    imagen_ruta
                )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    error_log('Soporte cargar_reporte prepare failed: ' . $mysqli->error);
    die('No fue posible registrar el reporte.');
}

$stmt->bind_param(
    "isssssssssss",
    $id_usuario,
    $codigo_error,
    $asunto,
    $descripcion,
    $prioridad,
    $tipo_error,
    $estado_error,
    $nombre_solicitante,
    $apellido_solicitante,
    $correo_solicitante,
    $celular_solicitante,
    $imagen_ruta
);

if ($stmt->execute()) {
    header('Location: ' . neiva_app_url('Arbimaps/index.php?page=soporte/solicitud_revision&ok=1'), true, 303);
    exit;
}

error_log('Soporte cargar_reporte execute failed: ' . $stmt->error);
die('No fue posible registrar el reporte.');
