<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../../../../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $back = $_SERVER['HTTP_REFERER'] ?? '/';
    header('Location: ' . $back);
    exit;
}

function limpiar($dato, $conexion)
{
    return mysqli_real_escape_string($conexion, trim($dato));
}

$id = isset($_POST['id']) ? limpiar($_POST['id'], $mysqli) : '';
if (empty($id)) {
    $_SESSION['swal_error'] = 'ID inválido o vacío';
    $back = $_SERVER['HTTP_REFERER'] ?? neiva_app_url('Arbimaps/index.php?page=baqueanos/solicitudes/vistas/solicitud_baqueanos');
    header('Location: ' . $back);
    exit;
}

// Captura de datos
$sb_tipo_documento      = limpiar($_POST['sb_tipo_documento'], $mysqli);
$sb_numero_identidad    = limpiar($_POST['sb_numero_identidad'], $mysqli);
$sb_baqueano_nombre     = limpiar($_POST['sb_baqueano_nombre'], $mysqli);
$sb_baqueano_apellido   = limpiar($_POST['sb_baqueano_apellido'], $mysqli);
$sb_telefono_baqueano   = limpiar($_POST['sb_telefono_baqueano'], $mysqli);
$sb_correo_baqueano     = limpiar($_POST['sb_correo_baqueano'], $mysqli);
$sb_direccion           = limpiar($_POST['sb_direccion'], $mysqli);
$sb_cuenta              = limpiar($_POST['sb_cuenta'], $mysqli);
$sb_tipo_cuenta         = limpiar($_POST['sb_tipo_cuenta'], $mysqli);
$sb_num_cuenta          = limpiar($_POST['sb_num_cuenta'], $mysqli);
$sb_titular             = limpiar($_POST['sb_titular'], $mysqli);
$sb_year                = limpiar($_POST['sb_year'], $mysqli);
$sb_fecha_inicio        = limpiar($_POST['sb_fecha_inicio'], $mysqli);
$sb_fecha_fin           = limpiar($_POST['sb_fecha_fin'], $mysqli);
$sb_dias_calculados     = limpiar($_POST['sb_dias_calculados'], $mysqli);
$sb_cobro_diario        = limpiar($_POST['sb_cobro_diario'], $mysqli);
$sb_valor_cobrar        = limpiar($_POST['sb_valor_cobrar'], $mysqli);
$sb_unidad_intervencion = limpiar($_POST['sb_unidad_intervencion'], $mysqli);
$sb_unidad_operativa    = limpiar($_POST['sb_unidad_operativa'], $mysqli);
$sb_tipo_unidad         = limpiar($_POST['sb_tipo_unidad'], $mysqli);
$sb_municipio           = limpiar($_POST['sb_municipio'], $mysqli);
$sb_vereda              = limpiar($_POST['sb_vereda'], $mysqli);
$sb_tipo_actividad      = limpiar($_POST['sb_tipo_actividad'], $mysqli);
$sb_coordinador         = limpiar($_POST['sb_coordinador'], $mysqli);
$sb_lider_cuadrilla     = limpiar($_POST['sb_lider_cuadrilla'], $mysqli);
$sb_transporte          = limpiar($_POST['sb_transporte'], $mysqli);
$sb_porque_transporte   = limpiar($_POST['sb_porque_transporte'], $mysqli);
$sb_hospedaje           = limpiar($_POST['sb_hospedaje'], $mysqli);
$sb_porque_hospedaje    = limpiar($_POST['sb_porque_hospedaje'], $mysqli);
$sb_reconocedor         = limpiar($_POST['sb_reconocedor'], $mysqli);
$sb_profesional_baqueano= limpiar($_POST['sb_profesional_baqueano'], $mysqli);

// Capturar valores de cuanto_hospedaje y cuanto_transporte solo si se enviaron (campos visibles)
$sb_cuanto_hospedaje    = isset($_POST['sb_cuanto_hospedaje']) ? 
                          preg_replace('/[^\d]/', '', limpiar($_POST['sb_cuanto_hospedaje'], $mysqli)) : NULL;
$sb_cuanto_transporte   = isset($_POST['sb_cuanto_transporte']) ? 
                          preg_replace('/[^\d]/', '', limpiar($_POST['sb_cuanto_transporte'], $mysqli)) : NULL;

// Si los campos no se enviaron, obtener los valores actuales de la BD
if ($sb_cuanto_hospedaje === NULL || $sb_cuanto_transporte === NULL) {
    $query_actual = "SELECT sb_cuanto_hospedaje, sb_cuanto_transporte FROM solicitud_baqueanos WHERE id = ?";
    $stmt_actual = $mysqli->prepare($query_actual);
    $stmt_actual->bind_param("i", $id);
    $stmt_actual->execute();
    $result_actual = $stmt_actual->get_result();
    
    if ($row_actual = $result_actual->fetch_assoc()) {
        if ($sb_cuanto_hospedaje === NULL) {
            $sb_cuanto_hospedaje = $row_actual['sb_cuanto_hospedaje'];
        }
        if ($sb_cuanto_transporte === NULL) {
            $sb_cuanto_transporte = $row_actual['sb_cuanto_transporte'];
        }
    }
    $stmt_actual->close();
}

$sb_valor_cobrar = preg_replace('/[^\d]/', '', $sb_valor_cobrar);

// Consulta SQL
$sql = "UPDATE solicitud_baqueanos SET
    sb_tipo_documento        = ?, 
    sb_numero_identidad      = ?, 
    sb_baqueano_nombre       = ?, 
    sb_baqueano_apellido     = ?, 
    sb_telefono_baqueano     = ?, 
    sb_correo_baqueano       = ?, 
    sb_direccion             = ?, 
    sb_cuenta                = ?, 
    sb_tipo_cuenta           = ?, 
    sb_num_cuenta            = ?, 
    sb_titular               = ?, 
    sb_year                  = ?, 
    sb_fecha_inicio          = ?, 
    sb_fecha_fin             = ?, 
    sb_dias_calculados       = ?, 
    sb_cobro_diario          = ?, 
    sb_valor_cobrar          = ?, 
    sb_unidad_intervencion   = ?, 
    sb_unidad_operativa      = ?, 
    sb_tipo_unidad           = ?, 
    sb_municipio             = ?, 
    sb_vereda                = ?, 
    sb_tipo_actividad        = ?, 
    sb_coordinador           = ?, 
    sb_lider_cuadrilla       = ?, 
    sb_transporte            = ?, 
    sb_porque_transporte     = ?, 
    sb_hospedaje             = ?, 
    sb_porque_hospedaje      = ?, 
    sb_cuanto_hospedaje      =?,
    sb_cuanto_transporte     =?,
    sb_reconocedor           =?,
    sb_profesional_baqueano  =?
    
WHERE id = ?";

$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    $_SESSION['swal_error'] = 'Error al preparar la consulta: ' . $mysqli->error;
    $mysqli->close();
    $back = $_SERVER['HTTP_REFERER'] ?? neiva_app_url('Arbimaps/index.php?page=baqueanos/solicitudes/vistas/solicitud_baqueanos');
    header('Location: ' . $back);
    exit;
}

$stmt->bind_param(
    "sssssssssssssssssssssssssssssssssi",
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
    $sb_cuanto_hospedaje,
    $sb_cuanto_transporte,
    $sb_reconocedor,
    $sb_profesional_baqueano,
    $id,
);

if ($stmt->execute()) {
    $_SESSION['swal_success'] = true;
    $stmt->close();
    $mysqli->close();
    header("Location: /arbimaps/Arbimaps/index.php?page=baqueanos/solicitudes/vistas/solicitudes_baqueanos");
    exit;
} else {
    $_SESSION['swal_error'] = 'Error al ejecutar la actualización: ' . $stmt->error;
    $stmt->close();
    $mys->close();
    $back = $_SERVER['HTTP_REFERER'] ?? neiva_app_url('Arbimaps/index.php?page=baqueanos/solicitudes/vistas/solicitudes_baqueanos');
    header('Location: ' . $back);
    exit;
}
