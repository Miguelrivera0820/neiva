<?php
date_default_timezone_set('America/Bogota');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../../../../conexion.php';
require_once __DIR__ . '/../funciones_productos.php';

$urlConsulta = '../../../index.php?page=prod_catastrales/consultar_producto';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $urlConsulta);
    exit;
}

$codigoProducto = trim((string) ($_POST['codigo_producto'] ?? ''));
$nombreDocumento = trim((string) ($_POST['nombre_documento'] ?? ''));
$tokenRecibido = (string) ($_POST['csrf_token'] ?? '');
$tokenSesion = (string) ($_SESSION['csrf_eliminar_documento_producto'] ?? '');
$codigoSeguro = normalizarCodigoProducto($codigoProducto);
$urlGestion = '../../../index.php?page=prod_catastrales/cargar_documentos_producto&codigo='
    . rawurlencode($codigoProducto)
    . '&seccion=documentos';

function mostrarErrorEliminacionProducto($mensaje, $urlGestion)
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

if ($tokenSesion === '' || $tokenRecibido === '' || !hash_equals($tokenSesion, $tokenRecibido)) {
    mostrarErrorEliminacionProducto('La solicitud de eliminación no es válida. Recargue la página.', $urlGestion);
}

if ($codigoSeguro === '' || $codigoSeguro !== $codigoProducto) {
    mostrarErrorEliminacionProducto('El código del producto no es válido.', $urlConsulta);
}

if ($nombreDocumento === ''
    || basename($nombreDocumento) !== $nombreDocumento
    || strpos($nombreDocumento, "\0") !== false
    || strtolower(pathinfo($nombreDocumento, PATHINFO_EXTENSION)) !== 'pdf'
) {
    mostrarErrorEliminacionProducto('El nombre del documento no es válido.', $urlGestion);
}

$conn = new mysqli($servidor, $usuario, $contrasena, $base_de_datos);
if ($conn->connect_error) {
    mostrarErrorEliminacionProducto('No fue posible conectar con la base de datos.', $urlGestion);
}

$sqlProducto = "SELECT certificado_id, estado
                FROM certificado_catastral
                WHERE codigo_certificado = ?
                  AND prod_tipo_producto IS NOT NULL
                  AND TRIM(prod_tipo_producto) <> ''
                LIMIT 1";
$stmtProducto = $conn->prepare($sqlProducto);
if (!$stmtProducto) {
    mostrarErrorEliminacionProducto('No fue posible validar la solicitud.', $urlGestion);
}

$stmtProducto->bind_param('s', $codigoProducto);
if (!$stmtProducto->execute()) {
    mostrarErrorEliminacionProducto('No fue posible validar la solicitud.', $urlGestion);
}

$stmtProducto->bind_result($idProducto, $estadoProducto);
$productoExiste = $stmtProducto->fetch();
$stmtProducto->close();

if (!$productoExiste) {
    mostrarErrorEliminacionProducto('No se encontró la solicitud de producto catastral.', $urlConsulta);
}

if (!asegurarFlujoProductosCatastrales($conn)) {
    mostrarErrorEliminacionProducto('No fue posible preparar el flujo de productos.', $urlGestion);
}
$estadoProductoNormalizado = strtoupper(trim((string) $estadoProducto));
if (!usuarioProductoTieneRol(['editor', 'administrador'])
    || !in_array($estadoProductoNormalizado, [ESTADO_PRODUCTO_EN_EDITOR, ESTADO_PRODUCTO_DEVOLUCION], true)
) {
    mostrarErrorEliminacionProducto('La solicitud no está habilitada para eliminar documentos.', $urlGestion);
}

if (!usuarioProductoTieneRol('administrador')) {
    $flujoProducto = obtenerFlujoProducto($conn, $codigoProducto);
    if (!$flujoProducto
        || trim((string) ($flujoProducto['editor_cedula'] ?? '')) !== cedulaUsuarioProductoActual()
    ) {
        mostrarErrorEliminacionProducto('Esta solicitud está asignada a otro editor.', $urlGestion);
    }
}

$directorioProducto = directorioDocumentosProducto($codigoProducto);
$directorioReal = realpath($directorioProducto);
$rutaDocumento = $directorioProducto . DIRECTORY_SEPARATOR . $nombreDocumento;
$rutaDocumentoReal = realpath($rutaDocumento);

if ($directorioReal === false
    || $rutaDocumentoReal === false
    || !is_file($rutaDocumentoReal)
    || strcasecmp(dirname($rutaDocumentoReal), $directorioReal) !== 0
) {
    mostrarErrorEliminacionProducto('El documento seleccionado no existe.', $urlGestion);
}

if (!unlink($rutaDocumentoReal)) {
    mostrarErrorEliminacionProducto('No fue posible eliminar el documento.', $urlGestion);
}

$conn->close();
unset($_SESSION['csrf_eliminar_documento_producto']);
header('Location: ' . $urlGestion . '&resultado=documento_eliminado');
exit;
