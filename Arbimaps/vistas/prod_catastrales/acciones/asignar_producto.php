<?php
date_default_timezone_set('America/Bogota');
require_once __DIR__ . '/../../../includes/auth.php';
require __DIR__ . '/../../../../conexion.php';
require_once __DIR__ . '/../funciones_productos.php';

$urlConsulta = '../../../index.php?page=prod_catastrales/consultar_producto';

function errorAsignarProducto($mensaje, $url)
{
    $mensajeJs = json_encode($mensaje, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $urlJs = json_encode($url, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    echo "<script>alert($mensajeJs); window.location.href = $urlJs;</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !usuarioProductoTieneRol(['coordinacion_tecnica', 'administrador'])) {
    errorAsignarProducto('No tiene permisos para realizar esta asignación.', $urlConsulta);
}

$codigoProducto = trim((string) ($_POST['codigo_producto'] ?? ''));
$editorCedula = trim((string) ($_POST['editor_cedula'] ?? ''));
$token = (string) ($_POST['csrf_token'] ?? '');
$tokenSesion = (string) ($_SESSION['csrf_asignar_producto'] ?? '');
$urlAsignar = '../../../index.php?page=prod_catastrales/asignar_producto&codigo=' . rawurlencode($codigoProducto);

if ($tokenSesion === '' || $token === '' || !hash_equals($tokenSesion, $token)) {
    errorAsignarProducto('La solicitud no es válida. Recargue la página.', $urlAsignar);
}
if (normalizarCodigoProducto($codigoProducto) !== $codigoProducto || $codigoProducto === '' || $editorCedula === '') {
    errorAsignarProducto('Los datos de asignación no son válidos.', $urlAsignar);
}

if (!asegurarFlujoProductosCatastrales($mysqli)) {
    errorAsignarProducto('No fue posible preparar el flujo de productos.', $urlAsignar);
}
$stmtEditor = $mysqli->prepare(
    "SELECT nombre_usuario, apellido_usuario
     FROM usuarios_cons
     WHERE cedula_usuario = ? AND (rol_usuario = 'editor' OR rol_usuario_dos = 'editor')
     LIMIT 1"
);
if (!$stmtEditor) {
    errorAsignarProducto('No fue posible consultar el editor seleccionado.', $urlAsignar);
}
$stmtEditor->bind_param('s', $editorCedula);
$stmtEditor->execute();
$resultadoEditor = $stmtEditor->get_result();
$editor = $resultadoEditor ? $resultadoEditor->fetch_assoc() : null;
$stmtEditor->close();
if (!$editor) {
    errorAsignarProducto('El editor seleccionado no es válido.', $urlAsignar);
}

$editorNombre = trim($editor['nombre_usuario'] . ' ' . $editor['apellido_usuario']);
$estadoEsperado = ESTADO_PRODUCTO_EN_COORDINACION;
$estadoEditor = ESTADO_PRODUCTO_EN_EDITOR;
$mysqli->begin_transaction();

try {
    $stmtEstado = $mysqli->prepare(
        "UPDATE certificado_catastral SET estado = ?
         WHERE codigo_certificado = ? AND UPPER(TRIM(estado)) = ?"
    );
    if (!$stmtEstado) {
        throw new Exception('No fue posible preparar el cambio de estado.');
    }
    $stmtEstado->bind_param('sss', $estadoEditor, $codigoProducto, $estadoEsperado);
    $stmtEstado->execute();
    if ($stmtEstado->affected_rows !== 1) {
        throw new Exception('La solicitud ya no está disponible para asignación.');
    }
    $stmtEstado->close();

    $stmtFlujo = $mysqli->prepare(
        "INSERT INTO producto_catastral_flujo
            (codigo_producto, editor_cedula, editor_nombre, observacion_revision, fecha_asignacion)
         VALUES (?, ?, ?, NULL, NOW())
         ON DUPLICATE KEY UPDATE
            editor_cedula = VALUES(editor_cedula), editor_nombre = VALUES(editor_nombre),
            observacion_revision = NULL, fecha_asignacion = NOW()"
    );
    if (!$stmtFlujo) {
        throw new Exception('No fue posible preparar la asignación del editor.');
    }
    $stmtFlujo->bind_param('sss', $codigoProducto, $editorCedula, $editorNombre);
    $stmtFlujo->execute();
    $stmtFlujo->close();

    $mysqli->commit();
    unset($_SESSION['csrf_asignar_producto']);
    header('Location: ' . $urlConsulta . '&resultado=asignado');
    exit;
} catch (Throwable $error) {
    $mysqli->rollback();
    errorAsignarProducto('Error: ' . $error->getMessage(), $urlAsignar);
}
