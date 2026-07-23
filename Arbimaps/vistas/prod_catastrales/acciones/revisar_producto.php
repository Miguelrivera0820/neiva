<?php
date_default_timezone_set('America/Bogota');
require_once __DIR__ . '/../../../includes/auth.php';
require __DIR__ . '/../../../../conexion.php';
require_once __DIR__ . '/../funciones_productos.php';

$urlConsulta = '../../../index.php?page=prod_catastrales/consultar_producto';

function errorRevisionProducto($mensaje, $url)
{
    $mensajeJs = json_encode($mensaje, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $urlJs = json_encode($url, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    echo "<script>alert($mensajeJs); window.location.href = $urlJs;</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !usuarioProductoTieneRol(['coordinacion_tecnica', 'administrador'])) {
    errorRevisionProducto('No tiene permisos para revisar documentos.', $urlConsulta);
}

$codigoProducto = trim((string) ($_POST['codigo_producto'] ?? ''));
$accion = trim((string) ($_POST['accion'] ?? ''));
$token = (string) ($_POST['csrf_token'] ?? '');
$tokenSesion = (string) ($_SESSION['csrf_revisar_producto'] ?? '');
$urlRevision = '../../../index.php?page=prod_catastrales/revisar_producto&codigo=' . rawurlencode($codigoProducto);

if ($tokenSesion === '' || $token === '' || !hash_equals($tokenSesion, $token)) {
    errorRevisionProducto('La solicitud no es válida. Recargue la página.', $urlRevision);
}
if ($codigoProducto === '' || normalizarCodigoProducto($codigoProducto) !== $codigoProducto) {
    errorRevisionProducto('El código del producto no es válido.', $urlConsulta);
}

if (!asegurarFlujoProductosCatastrales($mysqli)) {
    errorRevisionProducto('No fue posible preparar el flujo de productos.', $urlRevision);
}
$estadoPendiente = ESTADO_PRODUCTO_PENDIENTE_APROBACION;
$stmtProducto = $mysqli->prepare(
    "SELECT certificado_id FROM certificado_catastral
     WHERE codigo_certificado = ? AND UPPER(TRIM(estado)) = ? LIMIT 1"
);
if (!$stmtProducto) {
    errorRevisionProducto('No fue posible consultar la solicitud.', $urlRevision);
}
$stmtProducto->bind_param('ss', $codigoProducto, $estadoPendiente);
$stmtProducto->execute();
$stmtProducto->bind_result($idProducto);
$productoExiste = $stmtProducto->fetch();
$stmtProducto->close();
if (!$productoExiste) {
    errorRevisionProducto('La solicitud ya no está pendiente de aprobación.', $urlConsulta);
}

if ($accion === 'reemplazar') {
    $nombreDocumento = trim((string) ($_POST['nombre_documento'] ?? ''));
    if ($nombreDocumento === ''
        || basename($nombreDocumento) !== $nombreDocumento
        || strtolower(pathinfo($nombreDocumento, PATHINFO_EXTENSION)) !== 'pdf'
    ) {
        errorRevisionProducto('El documento seleccionado no es válido.', $urlRevision);
    }
    if (!isset($_FILES['documento_firmado']) || $_FILES['documento_firmado']['error'] !== UPLOAD_ERR_OK) {
        errorRevisionProducto('Debe seleccionar el documento firmado.', $urlRevision);
    }
    if (($_FILES['documento_firmado']['size'] ?? 0) <= 0 || $_FILES['documento_firmado']['size'] > 20 * 1024 * 1024) {
        errorRevisionProducto('El documento firmado debe pesar menos de 20 MB.', $urlRevision);
    }
    if (strtolower(pathinfo($_FILES['documento_firmado']['name'], PATHINFO_EXTENSION)) !== 'pdf') {
        errorRevisionProducto('Solo se permiten documentos PDF.', $urlRevision);
    }
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES['documento_firmado']['tmp_name']);
        if (!in_array($mime, ['application/pdf', 'application/x-pdf'], true)) {
            errorRevisionProducto('El archivo seleccionado no es un PDF válido.', $urlRevision);
        }
    }

    $directorio = directorioDocumentosProducto($codigoProducto);
    $directorioReal = realpath($directorio);
    $rutaActual = realpath($directorio . DIRECTORY_SEPARATOR . $nombreDocumento);
    if ($directorioReal === false || $rutaActual === false || strcasecmp(dirname($rutaActual), $directorioReal) !== 0) {
        errorRevisionProducto('El documento que desea reemplazar no existe.', $urlRevision);
    }
    if (!move_uploaded_file($_FILES['documento_firmado']['tmp_name'], $rutaActual)) {
        errorRevisionProducto('No fue posible reemplazar el documento.', $urlRevision);
    }
    if (file_put_contents(rutaMarcaDocumentoFirmadoProducto($codigoProducto), date('Y-m-d H:i:s'), LOCK_EX) === false) {
        errorRevisionProducto('El documento se reemplazó, pero no fue posible registrar la firma.', $urlRevision);
    }

    header('Location: ' . $urlRevision . '&resultado=reemplazado');
    exit;
}

if ($accion === 'aprobar') {
    if (!productoTieneDocumentos($codigoProducto)) {
        errorRevisionProducto('No hay documentos para aprobar.', $urlRevision);
    }
    if (!is_file(rutaMarcaDocumentoFirmadoProducto($codigoProducto))) {
        errorRevisionProducto('Primero debe reemplazar el documento por su versión firmada.', $urlRevision);
    }
    $nuevoEstado = ESTADO_PRODUCTO_APROBADO;
    $observacion = '';
} elseif ($accion === 'rechazar') {
    $nuevoEstado = ESTADO_PRODUCTO_DEVOLUCION;
    $observacion = trim((string) ($_POST['observacion'] ?? ''));
    if ($observacion === '') {
        errorRevisionProducto('Debe indicar el motivo de la devolución.', $urlRevision);
    }
    $marcaFirmado = rutaMarcaDocumentoFirmadoProducto($codigoProducto);
    if (is_file($marcaFirmado) && !unlink($marcaFirmado)) {
        errorRevisionProducto('No fue posible reiniciar el estado de firma del documento.', $urlRevision);
    }
} else {
    errorRevisionProducto('La acción solicitada no es válida.', $urlRevision);
}

$mysqli->begin_transaction();
try {
    $stmtEstado = $mysqli->prepare(
        "UPDATE certificado_catastral SET estado = ?
         WHERE certificado_id = ? AND UPPER(TRIM(estado)) = ?"
    );
    if (!$stmtEstado) {
        throw new Exception('No fue posible preparar el cambio de estado.');
    }
    $stmtEstado->bind_param('sis', $nuevoEstado, $idProducto, $estadoPendiente);
    $stmtEstado->execute();
    if ($stmtEstado->affected_rows !== 1) {
        throw new Exception('El estado de la solicitud cambió.');
    }
    $stmtEstado->close();

    $stmtFlujo = $mysqli->prepare(
        "UPDATE producto_catastral_flujo
         SET observacion_revision = ?, fecha_revision = NOW()
         WHERE codigo_producto = ?"
    );
    if (!$stmtFlujo) {
        throw new Exception('No fue posible preparar el resultado de la revisión.');
    }
    $stmtFlujo->bind_param('ss', $observacion, $codigoProducto);
    $stmtFlujo->execute();
    $stmtFlujo->close();

    $mysqli->commit();
    unset($_SESSION['csrf_revisar_producto']);
    $resultado = $accion === 'aprobar' ? 'aprobado' : 'devuelto';
    header('Location: ' . $urlConsulta . '&resultado=' . $resultado);
    exit;
} catch (Throwable $error) {
    $mysqli->rollback();
    errorRevisionProducto('Error: ' . $error->getMessage(), $urlRevision);
}
