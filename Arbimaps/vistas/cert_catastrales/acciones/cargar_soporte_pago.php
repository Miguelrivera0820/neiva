<?php
date_default_timezone_set('America/Bogota');
require __DIR__ . '/../../../../conexion.php';

$conn = new mysqli($servidor, $usuario, $contrasena, $base_de_datos);
if ($conn->connect_error) {
    die('Error de conexión: ' . $conn->connect_error);
}

$urlConsulta = '../../../index.php?page=cert_catastrales/consulta_cert_catastrales';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $urlConsulta);
    exit;
}

$codigoCertificado = trim((string) ($_POST['codigo_certificado'] ?? ''));
$codigoSeguro = preg_replace('/[^A-Za-z0-9_-]/', '', $codigoCertificado);
$urlGestion = '../../../index.php?page=cert_catastrales/gestionar_pago_certificado&codigo=' . rawurlencode($codigoCertificado);

function mostrarErrorSoporteCertificado($mensaje, $url)
{
    $mensajeJs = json_encode(
        $mensaje,
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    );
    $urlJs = json_encode($url, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    echo "<script>alert($mensajeJs); window.location.href = $urlJs;</script>";
    exit;
}

if ($codigoCertificado === '' || $codigoSeguro !== $codigoCertificado) {
    mostrarErrorSoporteCertificado('El código del certificado no es válido.', $urlConsulta);
}

$sqlCertificado = "SELECT certificado_id
    FROM certificado_catastral
    WHERE codigo_certificado = ?
      AND (prod_tipo_producto IS NULL OR TRIM(prod_tipo_producto) = '')
    LIMIT 1";
$stmtCertificado = $conn->prepare($sqlCertificado);
if (!$stmtCertificado) {
    mostrarErrorSoporteCertificado('No fue posible validar el certificado: ' . $conn->error, $urlGestion);
}

$stmtCertificado->bind_param('s', $codigoCertificado);
if (!$stmtCertificado->execute()) {
    mostrarErrorSoporteCertificado('No fue posible validar el certificado: ' . $stmtCertificado->error, $urlGestion);
}

$stmtCertificado->bind_result($idCertificado);
$certificadoExiste = $stmtCertificado->fetch();
$stmtCertificado->close();
if (!$certificadoExiste) {
    mostrarErrorSoporteCertificado('No se encontró la solicitud de certificado catastral.', $urlConsulta);
}

try {
    if (!isset($_FILES['soporte_pago_certificado'])) {
        throw new Exception('Debe seleccionar el soporte de pago.');
    }

    $archivo = $_FILES['soporte_pago_certificado'];
    if (!isset($archivo['error']) || $archivo['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('El archivo no pudo cargarse. Intente nuevamente.');
    }
    if (($archivo['size'] ?? 0) <= 0 || $archivo['size'] > 20 * 1024 * 1024) {
        throw new Exception('El archivo debe pesar menos de 20 MB.');
    }
    if (strtolower(pathinfo((string) ($archivo['name'] ?? ''), PATHINFO_EXTENSION)) !== 'pdf') {
        throw new Exception('Solo se permiten documentos en formato PDF.');
    }

    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($archivo['tmp_name']);
        if (!in_array($mime, ['application/pdf', 'application/x-pdf'], true)) {
            throw new Exception('El archivo seleccionado no es un PDF válido.');
        }
    }

    $directorioPago = __DIR__ . '/../../../soportes_pago/' . $codigoSeguro . '/';
    if (!is_dir($directorioPago) && !mkdir($directorioPago, 0777, true)) {
        throw new Exception('No fue posible crear la carpeta del soporte de pago.');
    }

    $nombreSoporte = 'soporte_pago.pdf';
    if (!move_uploaded_file($archivo['tmp_name'], $directorioPago . $nombreSoporte)) {
        throw new Exception('No fue posible guardar el soporte de pago.');
    }

    $rutaRelativa = 'soportes_pago/' . $codigoSeguro . '/' . $nombreSoporte;
    $sqlPago = "UPDATE certificado_catastral SET cert_soporte_pago = ? WHERE certificado_id = ?";
    $stmtPago = $conn->prepare($sqlPago);
    if (!$stmtPago) {
        throw new Exception('No fue posible preparar la actualización del pago: ' . $conn->error);
    }
    $stmtPago->bind_param('si', $rutaRelativa, $idCertificado);
    if (!$stmtPago->execute()) {
        throw new Exception('No fue posible actualizar el soporte de pago: ' . $stmtPago->error);
    }
    $stmtPago->close();

    header('Location: ' . $urlGestion . '&resultado=pago');
    exit;
} catch (Throwable $error) {
    mostrarErrorSoporteCertificado('Error: ' . $error->getMessage(), $urlGestion);
}
