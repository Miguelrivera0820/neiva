<?php
date_default_timezone_set('America/Bogota');
require_once __DIR__ . '/../../../includes/auth.php';
require __DIR__ . '/../../../../conexion.php';
require_once __DIR__ . '/../funciones_productos.php';

$conn = new mysqli($servidor, $usuario, $contrasena, $base_de_datos);
if ($conn->connect_error) {
    die('Error de conexión: ' . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../../index.php?page=prod_catastrales/consultar_producto');
    exit;
}

$codigoProducto = trim($_POST['codigo_producto'] ?? '');
$tipoCarga = trim($_POST['tipo_carga'] ?? '');
$codigoSeguro = normalizarCodigoProducto($codigoProducto);
$seccionGestion = $tipoCarga === 'pago' ? 'pago' : 'documentos';
$urlGestion = '../../../index.php?page=prod_catastrales/cargar_documentos_producto&codigo='
    . rawurlencode($codigoProducto)
    . '&seccion=' . $seccionGestion;

function mostrarErrorCargaProducto($mensaje, $urlGestion)
{
    $mensajeJs = json_encode(
        $mensaje,
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    );
    $urlJs = json_encode($urlGestion, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

    echo "<script>
        alert($mensajeJs);
        window.location.href = $urlJs;
    </script>";
    exit;
}

if ($codigoSeguro === '' || $codigoSeguro !== $codigoProducto) {
    mostrarErrorCargaProducto('El código del producto no es válido.', '../../../index.php?page=prod_catastrales/consultar_producto');
}

if (!asegurarFlujoProductosCatastrales($conn)) {
    mostrarErrorCargaProducto('No fue posible preparar el flujo de productos.', $urlGestion);
}

$sqlProducto = "SELECT certificado_id, cert_soporte_pago, estado
                FROM certificado_catastral
                WHERE codigo_certificado = ?
                  AND prod_tipo_producto IS NOT NULL
                  AND TRIM(prod_tipo_producto) <> ''
                LIMIT 1";
$stmtProducto = $conn->prepare($sqlProducto);

if (!$stmtProducto) {
    mostrarErrorCargaProducto('No fue posible validar la solicitud: ' . $conn->error, $urlGestion);
}

$stmtProducto->bind_param('s', $codigoProducto);
if (!$stmtProducto->execute()) {
    mostrarErrorCargaProducto('No fue posible validar la solicitud: ' . $stmtProducto->error, $urlGestion);
}

$stmtProducto->bind_result($idProducto, $soportePagoProducto, $estadoProductoActual);
$productoExiste = $stmtProducto->fetch();
$stmtProducto->close();

if (!$productoExiste) {
    mostrarErrorCargaProducto('No se encontró la solicitud de producto catastral.', '../../../index.php?page=prod_catastrales/consultar_producto');
}

function validarPdfProducto($archivo)
{
    if (!isset($archivo['error']) || $archivo['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Uno de los archivos no pudo cargarse. Intente nuevamente.');
    }

    if (($archivo['size'] ?? 0) <= 0 || $archivo['size'] > 20 * 1024 * 1024) {
        throw new Exception('Cada archivo debe pesar menos de 20 MB.');
    }

    $extension = strtolower(pathinfo($archivo['name'] ?? '', PATHINFO_EXTENSION));
    if ($extension !== 'pdf') {
        throw new Exception('Solo se permiten documentos en formato PDF.');
    }

    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($archivo['tmp_name']);
        if (!in_array($mime, ['application/pdf', 'application/x-pdf'], true)) {
            throw new Exception('El archivo seleccionado no es un PDF válido.');
        }
    }
}

function nombreSeguroDocumentoProducto($nombreOriginal)
{
    $nombreBase = pathinfo((string) $nombreOriginal, PATHINFO_FILENAME);
    $nombreBase = preg_replace('/[^A-Za-z0-9_-]+/', '_', $nombreBase);
    $nombreBase = trim($nombreBase, '_-');

    return ($nombreBase !== '' ? $nombreBase : 'documento') . '.pdf';
}

function rutaDisponibleDocumentoProducto($directorio, $nombreArchivo)
{
    $nombreBase = pathinfo($nombreArchivo, PATHINFO_FILENAME);
    $extension = '.pdf';
    $ruta = $directorio . DIRECTORY_SEPARATOR . $nombreArchivo;
    $numero = 1;

    while (file_exists($ruta)) {
        $ruta = $directorio . DIRECTORY_SEPARATOR . $nombreBase . '_' . $numero . $extension;
        $numero++;
    }

    return $ruta;
}

try {
    if ($tipoCarga === 'documentos') {
        if (!usuarioProductoTieneRol(['editor', 'administrador'])) {
            throw new Exception('Solo el editor asignado puede cargar los documentos del producto.');
        }

        $estadoNormalizado = strtoupper(trim((string) $estadoProductoActual));
        if (!in_array($estadoNormalizado, [ESTADO_PRODUCTO_EN_EDITOR, ESTADO_PRODUCTO_DEVOLUCION], true)) {
            throw new Exception('La solicitud no está habilitada para cargar documentos.');
        }

        if (!usuarioProductoTieneRol('administrador')) {
            $flujoProducto = obtenerFlujoProducto($conn, $codigoProducto);
            if (!$flujoProducto
                || trim((string) ($flujoProducto['editor_cedula'] ?? '')) !== cedulaUsuarioProductoActual()
            ) {
                throw new Exception('Esta solicitud está asignada a otro editor.');
            }
        }

        if (trim((string) $soportePagoProducto) === '') {
            throw new Exception('El pago está pendiente. Debe cargar el soporte de pago antes de subir documentos.');
        }

        if (!isset($_FILES['documentos_producto']) || !is_array($_FILES['documentos_producto']['name'])) {
            throw new Exception('Debe seleccionar al menos un documento para cargar.');
        }

        $archivos = [];
        $totalArchivos = count($_FILES['documentos_producto']['name']);
        for ($indice = 0; $indice < $totalArchivos; $indice++) {
            $archivo = [
                'name' => $_FILES['documentos_producto']['name'][$indice] ?? '',
                'type' => $_FILES['documentos_producto']['type'][$indice] ?? '',
                'tmp_name' => $_FILES['documentos_producto']['tmp_name'][$indice] ?? '',
                'error' => $_FILES['documentos_producto']['error'][$indice] ?? UPLOAD_ERR_NO_FILE,
                'size' => $_FILES['documentos_producto']['size'][$indice] ?? 0,
            ];
            validarPdfProducto($archivo);
            $archivos[] = $archivo;
        }

        if (count($archivos) === 0) {
            throw new Exception('Debe seleccionar al menos un documento para cargar.');
        }

        $directorioProducto = directorioDocumentosProducto($codigoProducto);
        if (!is_dir($directorioProducto) && !mkdir($directorioProducto, 0777, true)) {
            throw new Exception('No fue posible crear la carpeta del producto.');
        }

        foreach ($archivos as $archivo) {
            $nombreArchivo = nombreSeguroDocumentoProducto($archivo['name']);
            $rutaDestino = rutaDisponibleDocumentoProducto($directorioProducto, $nombreArchivo);

            if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                throw new Exception('No fue posible guardar uno de los documentos.');
            }
        }

        $marcaFirmado = rutaMarcaDocumentoFirmadoProducto($codigoProducto);
        if (is_file($marcaFirmado) && !unlink($marcaFirmado)) {
            throw new Exception('No fue posible reiniciar el estado de firma del documento.');
        }

        $estadoDocumentos = ESTADO_PRODUCTO_PENDIENTE_APROBACION;
        $estadoEditor = ESTADO_PRODUCTO_EN_EDITOR;
        $estadoDevolucion = ESTADO_PRODUCTO_DEVOLUCION;
        $sqlEstado = "UPDATE certificado_catastral
                      SET estado = ?
                      WHERE certificado_id = ?
                        AND UPPER(TRIM(estado)) IN (?, ?)";
        $stmtEstado = $conn->prepare($sqlEstado);
        if (!$stmtEstado) {
            throw new Exception('No fue posible preparar el estado de los documentos: ' . $conn->error);
        }

        $stmtEstado->bind_param('siss', $estadoDocumentos, $idProducto, $estadoEditor, $estadoDevolucion);
        if (!$stmtEstado->execute()) {
            throw new Exception('No fue posible actualizar el estado de los documentos: ' . $stmtEstado->error);
        }
        if ($stmtEstado->affected_rows !== 1) {
            throw new Exception('El estado de la solicitud cambió durante la carga.');
        }
        $stmtEstado->close();

        header('Location: ' . $urlGestion . '&resultado=documentos');
        exit;
    }

    if ($tipoCarga === 'pago') {
        if (!usuarioProductoTieneRol(['ventanilla_catastral', 'administrador'])) {
            throw new Exception('Solo Ventanilla puede registrar el soporte de pago.');
        }

        if (trim((string) $soportePagoProducto) !== '') {
            throw new Exception('El soporte de pago ya está registrado.');
        }

        if (!isset($_FILES['soporte_pago_producto'])) {
            throw new Exception('Debe seleccionar el soporte de pago.');
        }

        validarPdfProducto($_FILES['soporte_pago_producto']);

        $directorioPago = __DIR__ . '/../../../soportes_pago/' . $codigoSeguro . '/';
        if (!is_dir($directorioPago) && !mkdir($directorioPago, 0777, true)) {
            throw new Exception('No fue posible crear la carpeta del soporte de pago.');
        }

        $nombreSoporte = 'soporte_pago.pdf';
        $rutaDestinoPago = $directorioPago . $nombreSoporte;
        if (!move_uploaded_file($_FILES['soporte_pago_producto']['tmp_name'], $rutaDestinoPago)) {
            throw new Exception('No fue posible guardar el soporte de pago.');
        }

        $rutaRelativaPago = 'soportes_pago/' . $codigoSeguro . '/' . $nombreSoporte;
        $estadoCoordinacion = ESTADO_PRODUCTO_EN_COORDINACION;
        $sqlPago = "UPDATE certificado_catastral
                    SET cert_soporte_pago = ?, estado = ?
                    WHERE certificado_id = ?";
        $stmtPago = $conn->prepare($sqlPago);
        if (!$stmtPago) {
            throw new Exception('No fue posible preparar la actualización del pago: ' . $conn->error);
        }

        $stmtPago->bind_param('ssi', $rutaRelativaPago, $estadoCoordinacion, $idProducto);
        if (!$stmtPago->execute()) {
            throw new Exception('No fue posible actualizar el soporte de pago: ' . $stmtPago->error);
        }
        $stmtPago->close();

        header('Location: ' . $urlGestion . '&resultado=pago#soportePago');
        exit;
    }

    throw new Exception('El tipo de carga solicitado no es válido.');
} catch (Throwable $error) {
    mostrarErrorCargaProducto('Error: ' . $error->getMessage(), $urlGestion);
}
