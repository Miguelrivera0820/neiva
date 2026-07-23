<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../../../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit;
}

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$sql_select = "SELECT * FROM solicitud_baqueanos WHERE id = ?";
$stmt_select = $mysqli->prepare($sql_select);
if (!$stmt_select) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta de selección: ' . $mysqli->error]);
    exit;
}
$stmt_select->bind_param("i", $id);
$stmt_select->execute();
$result = $stmt_select->get_result();
if (!$result || $result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Registro no encontrado']);
    exit;
}
$current_row = $result->fetch_assoc();
$stmt_select->close();

$sb_tipo_documento          = $_POST['sb_tipo_documento'] ?? '';
$sb_numero_identidad        = $_POST['sb_numero_identidad'] ?? ($_POST['numero_identidad'] ?? '');
$sb_baqueano_nombre         = $_POST['sb_baqueano_nombre'] ?? '';
$sb_baqueano_apellido       = $_POST['sb_baqueano_apellido'] ?? '';
$sb_telefono_baqueano       = $_POST['sb_telefono_baqueano'] ?? '';
$sb_correo_baqueano         = $_POST['sb_correo_baqueano'] ?? '';
$sb_direccion               = $_POST['sb_direccion'] ?? '';
$sb_cuenta                  = $_POST['sb_cuenta'] ?? '';
$sb_tipo_cuenta             = $_POST['sb_tipo_cuenta'] ?? '';
$sb_num_cuenta              = $_POST['sb_num_cuenta'] ?? '';
$sb_titular                 = $_POST['sb_titular'] ?? '';
$sb_year                    = $_POST['sb_year'] ?? '';
$sb_fecha_inicio            = $_POST['sb_fecha_inicio'] ?? '';
$sb_fecha_fin               = $_POST['sb_fecha_fin'] ?? '';
$sb_dias_calculados         = $_POST['sb_dias_calculados'] ?? '';
$sb_cobro_diario            = $_POST['sb_cobro_diario'] ?? '';
$sb_valor_cobrar            = $_POST['sb_valor_cobrar'] ?? '';
$sb_unidad_intervencion     = $_POST['sb_unidad_intervencion'] ?? '';
$sb_unidad_operativa        = $_POST['sb_unidad_operativa'] ?? '';
$sb_tipo_unidad             = $_POST['sb_tipo_unidad'] ?? '';
$sb_municipio               = $_POST['sb_municipio'] ?? '';
$sb_vereda                  = $_POST['sb_vereda'] ?? '';
$sb_tipo_actividad          = $_POST['sb_tipo_actividad'] ?? '';
$sb_coordinador             = $_POST['sb_coordinador'] ?? '';
$sb_lider_cuadrilla         = $_POST['sb_lider_cuadrilla'] ?? '';
$sb_transporte              = $_POST['sb_transporte'] ?? '';
$sb_porque_transporte       = $_POST['sb_porque_transporte'] ?? '';
$sb_hospedaje               = $_POST['sb_hospedaje'] ?? '';
$sb_porque_hospedaje        = $_POST['sb_porque_hospedaje'] ?? '';
$sb_razon_lider             = $_POST['sb_razon_lider'] ?? '';
$sb_razon_profesional       = $_POST['sb_razon_profesional'] ?? '';
$sb_razon_operaciones       = $_POST['sb_razon_operaciones'] ?? '';
$sb_razon_gerencia          = $_POST['sb_razon_gerencia'] ?? '';
$sb_reconocedor             = $_POST['sb_reconocedor'] ?? '';
$sb_profesional_baqueano    = $_POST['sb_profesional_baqueano'] ?? '';
$sb_cuanto_hospedaje        = $_POST['sb_cuanto_hospedaje'] ?? '';
$sb_cuanto_transporte       = $_POST['sb_cuanto_transporte'] ?? '';

// Para cada campo: si el valor POST no es cadena vacía (''), usa el nuevo; de lo contrario, usa el valor actual
$sb_tipo_documento          = ($sb_tipo_documento       !== '') ? $sb_tipo_documento        : $current_row['sb_tipo_documento'];
$sb_numero_identidad        = ($sb_numero_identidad     !== '') ? $sb_numero_identidad      : $current_row['sb_numero_identidad'];
$sb_baqueano_nombre         = ($sb_baqueano_nombre      !== '') ? $sb_baqueano_nombre       : $current_row['sb_baqueano_nombre'];
$sb_baqueano_apellido       = ($sb_baqueano_apellido    !== '') ? $sb_baqueano_apellido     : $current_row['sb_baqueano_apellido'];
$sb_telefono_baqueano       = ($sb_telefono_baqueano    !== '') ? $sb_telefono_baqueano     : $current_row['sb_telefono_baqueano'];
$sb_correo_baqueano         = ($sb_correo_baqueano      !== '') ? $sb_correo_baqueano       : $current_row['sb_correo_baqueano'];
$sb_direccion               = ($sb_direccion            !== '') ? $sb_direccion             : $current_row['sb_direccion'];
$sb_cuenta                  = ($sb_cuenta               !== '') ? $sb_cuenta                : $current_row['sb_cuenta'];
$sb_tipo_cuenta             = ($sb_tipo_cuenta          !== '') ? $sb_tipo_cuenta           : $current_row['sb_tipo_cuenta'];
$sb_num_cuenta              = ($sb_num_cuenta           !== '') ? $sb_num_cuenta            : $current_row['sb_num_cuenta'];
$sb_titular                 = ($sb_titular              !== '') ? $sb_titular               : $current_row['sb_titular'];
$sb_year                    = ($sb_year                 !== '') ? $sb_year                  : $current_row['sb_year'];
$sb_fecha_inicio            = ($sb_fecha_inicio         !== '') ? $sb_fecha_inicio          : $current_row['sb_fecha_inicio'];
$sb_fecha_fin               = ($sb_fecha_fin            !== '') ? $sb_fecha_fin             : $current_row['sb_fecha_fin'];
$sb_dias_calculados         = ($sb_dias_calculados      !== '') ? $sb_dias_calculados       : $current_row['sb_dias_calculados'];
$sb_cobro_diario            = ($sb_cobro_diario         !== '') ? $sb_cobro_diario          : $current_row['sb_cobro_diario'];
$sb_valor_cobrar            = ($sb_valor_cobrar         !== '') ? $sb_valor_cobrar          : $current_row['sb_valor_cobrar'];
$sb_unidad_intervencion     = ($sb_unidad_intervencion  !== '') ? $sb_unidad_intervencion   : $current_row['sb_unidad_intervencion'];
$sb_unidad_operativa        = ($sb_unidad_operativa     !== '') ? $sb_unidad_operativa      : $current_row['sb_unidad_operativa'];
$sb_tipo_unidad             = ($sb_tipo_unidad          !== '') ? $sb_tipo_unidad           : $current_row['sb_tipo_unidad'];
$sb_municipio               = ($sb_municipio            !== '') ? $sb_municipio             : $current_row['sb_municipio'];
$sb_vereda                  = ($sb_vereda               !== '') ? $sb_vereda                : $current_row['sb_vereda'];
$sb_tipo_actividad          = ($sb_tipo_actividad       !== '') ? $sb_tipo_actividad        : $current_row['sb_tipo_actividad'];
$sb_coordinador             = ($sb_coordinador          !== '') ? $sb_coordinador           : $current_row['sb_coordinador'];
$sb_lider_cuadrilla         = ($sb_lider_cuadrilla      !== '') ? $sb_lider_cuadrilla       : $current_row['sb_lider_cuadrilla'];
$sb_transporte              = ($sb_transporte           !== '') ? $sb_transporte            : $current_row['sb_transporte'];
$sb_porque_transporte       = ($sb_porque_transporte    !== '') ? $sb_porque_transporte     : $current_row['sb_porque_transporte'];
$sb_hospedaje               = ($sb_hospedaje            !== '') ? $sb_hospedaje             : $current_row['sb_hospedaje'];
$sb_porque_hospedaje        = ($sb_porque_hospedaje     !== '') ? $sb_porque_hospedaje      : $current_row['sb_porque_hospedaje'];
$sb_razon_lider             = '';
$sb_razon_profesional       = '';
$sb_razon_operaciones       = '';
$sb_razon_gerencia          = '';
$sb_reconocedor             = ($sb_reconocedor          !== '') ? $sb_reconocedor           : $current_row['sb_reconocedor'];
$sb_profesional_baqueano    = ($sb_profesional_baqueano !== '') ? $sb_profesional_baqueano  : $current_row['sb_profesional_baqueano'];
$sb_cuanto_hospedaje        = ($sb_cuanto_hospedaje     !== '') ? $sb_cuanto_hospedaje      : $current_row['sb_cuanto_hospedaje'];
$sb_cuanto_transporte       = ($sb_cuanto_transporte    !== '') ? $sb_cuanto_transporte     : $current_row['sb_cuanto_transporte'];

$sql = "
UPDATE solicitud_baqueanos SET
    sb_tipo_documento = ?,
    sb_numero_identidad = ?,
    sb_baqueano_nombre = ?,
    sb_baqueano_apellido = ?,
    sb_telefono_baqueano = ?,
    sb_correo_baqueano = ?,
    sb_direccion = ?,
    sb_cuenta = ?,
    sb_tipo_cuenta = ?,
    sb_num_cuenta = ?,
    sb_titular = ?,
    sb_year = ?,
    sb_fecha_inicio = ?,
    sb_fecha_fin = ?,
    sb_dias_calculados = ?,
    sb_cobro_diario = ?,
    sb_valor_cobrar = ?,
    sb_unidad_intervencion = ?,
    sb_unidad_operativa = ?,
    sb_tipo_unidad = ?,
    sb_municipio = ?,
    sb_vereda = ?,
    sb_tipo_actividad = ?,
    sb_coordinador = ?,
    sb_lider_cuadrilla = ?,
    sb_transporte = ?,
    sb_porque_transporte = ?,
    sb_hospedaje = ?,
    sb_porque_hospedaje = ?,
    sb_razon_lider = ?,
    sb_razon_profesional = ?,
    sb_razon_operaciones = ?,
    sb_razon_gerencia = ?,
    sb_reconocedor = ?,
    sb_profesional_baqueano = ?,
    sb_cuanto_hospedaje = ?,
    sb_cuanto_transporte = ?,
    sb_estado_lider = 'PENDIENTE',
    sb_estado_profesional = 'PENDIENTE',
    sb_estado_operaciones = 'PENDIENTE',
    sb_estado_gerencia = 'PENDIENTE'
WHERE id = ?
";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta: ' . $mysqli->error]);
    exit;
}

$types = str_repeat('s', 37) . 'i';
$stmt->bind_param(
    $types,
    $sb_tipo_documento,
    $sb_numero_identidad,
    $sb_baqueano_nombre,
    $sb_baqueano_apellido,
    $sb_telefono_baqueano,
    $sb_correo_baqueano,
    $sb_direccion,
    $sb_cuenta,
    $sb_tipo_cuenta,
    $sb_num_cuenta,
    $sb_titular,
    $sb_year,
    $sb_fecha_inicio,
    $sb_fecha_fin,
    $sb_dias_calculados,
    $sb_cobro_diario,
    $sb_valor_cobrar,
    $sb_unidad_intervencion,
    $sb_unidad_operativa,
    $sb_tipo_unidad,
    $sb_municipio,
    $sb_vereda,
    $sb_tipo_actividad,
    $sb_coordinador,
    $sb_lider_cuadrilla,
    $sb_transporte,
    $sb_porque_transporte,
    $sb_hospedaje,
    $sb_porque_hospedaje,
    $sb_razon_lider,
    $sb_razon_profesional,
    $sb_razon_operaciones,
    $sb_razon_gerencia,
    $sb_reconocedor,
    $sb_profesional_baqueano,
    $sb_cuanto_hospedaje,
    $sb_cuanto_transporte,
    $id
);

if ($stmt->execute()) {
    header("Location: /arbimaps/Arbimaps/index.php?page=baqueanos/solicitudes/vistas/solicitudes_baqueanos");
    exit;
}

http_response_code(500);
echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $stmt->error]);
exit;
