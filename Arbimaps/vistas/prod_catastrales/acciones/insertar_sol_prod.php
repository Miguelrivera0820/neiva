<?php
// Handler para solicitudes de productos catastrales.
date_default_timezone_set('America/Bogota');
require_once __DIR__ . '/../../../includes/auth.php';
require __DIR__ . '/../../../../conexion.php';
require_once __DIR__ . '/../funciones_productos.php';

$conn = new mysqli($servidor, $usuario, $contrasena, $base_de_datos);
if ($conn->connect_error) {
    die('Error de conexión: ' . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return;
}

if (!usuarioProductoTieneRol(['ventanilla_catastral', 'administrador'])) {
    die('No tiene permisos para crear solicitudes de productos catastrales.');
}

$cert_tipo_documento              = trim($_POST['cert_documento_interado'] ?? '');
$cert_num_cc_interesado           = trim($_POST['num_cc_interesado'] ?? '');
$cert_primer_nombre_interesado    = trim($_POST['cert_primer_nombre'] ?? '');
$cert_segundo_nombre_interesado   = trim($_POST['cert_segundo_nombre'] ?? '');
$cert_primer_apellido_interesado  = trim($_POST['cert_primer_apellido'] ?? '');
$cert_segundo_apellido_interesado = trim($_POST['cert_segundo_apellido'] ?? '');
$cert_numero_cel_interesado       = trim($_POST['cert_telefono_interesado'] ?? '');
$cert_correo_electronico          = trim($_POST['cert_correo_interesado'] ?? '');
$prod_tipo_producto               = trim($_POST['prod_tipo_producto'] ?? '');

if (
    $cert_tipo_documento === ''
    || $cert_num_cc_interesado === ''
    || $cert_primer_nombre_interesado === ''
    || $cert_primer_apellido_interesado === ''
    || $prod_tipo_producto === ''
) {
    echo "<script>
        alert('Debe completar todos los campos obligatorios de la solicitud.');
        window.location.href = '../../../index.php?page=prod_catastrales/solicitar_producto';
    </script>";
    return;
}

$hora_creacion_producto = date('Y-m-d H:i:s');
$anio_actual = date('Y');

// La tabla comparte campos con certificados y todos están definidos como NOT NULL.
// Los campos que no aplican a un producto catastral se guardan vacíos.
$cert_soporte_pago             = '';
$cert_medio_envio              = '';
$cert_npn_predio               = '';
$cert_fmi_predio               = '';
$cert_anio_vigencia            = '';
$cert_avaluo_terreno_tramite   = '';
$cert_direccion_predio         = '';
$cert_dest_econ_predio         = '';
$cert_area_terreno_predio      = '';
$cert_area_construccion_predio = '';
$estado_producto               = ESTADO_PRODUCTO_SIN_CARGAR;

$conn->begin_transaction();

try {
    if (!asegurarFlujoProductosCatastrales($conn)) {
        throw new Exception('No fue posible preparar la estructura de productos catastrales.');
    }

    // El código se genera antes del INSERT porque codigo_certificado es NOT NULL.
    $sql_count = "SELECT COUNT(*)
                  FROM certificado_catastral
                  WHERE YEAR(certificado_hora_creacion) = ?
                    AND prod_tipo_producto IS NOT NULL
                    AND TRIM(prod_tipo_producto) <> ''";
    $stmt_count = $conn->prepare($sql_count);
    if (!$stmt_count) {
        throw new Exception('No fue posible preparar el consecutivo: ' . $conn->error);
    }

    $stmt_count->bind_param('s', $anio_actual);
    if (!$stmt_count->execute()) {
        throw new Exception('No fue posible consultar el consecutivo: ' . $stmt_count->error);
    }

    $stmt_count->bind_result($total_productos_anio);
    $stmt_count->fetch();
    $stmt_count->close();

    $consecutivo = ((int) $total_productos_anio) + 1;
    $codigo_producto = 'PROD-' . $anio_actual . '-' . str_pad($consecutivo, 2, '0', STR_PAD_LEFT);

    $sql = "INSERT INTO certificado_catastral (
        codigo_certificado,
        certificado_hora_creacion,
        cert_tipo_documento,
        cert_num_cc_interesado,
        cert_primer_nombre_interesado,
        cert_segundo_nombre_interesado,
        cert_primer_apellido_interesado,
        cert_segundo_apellido_interesado,
        cert_numero_cel_interesado,
        cert_correo_electronico,
        cert_soporte_pago,
        cert_medio_envio,
        cert_npn_predio,
        cert_fmi_predio,
        cert_anio_vigencia,
        cert_avaluo_terreno_tramite,
        cert_direccion_predio,
        cert_dest_econ_predio,
        cert_area_terreno_predio,
        cert_area_construccion_predio,
        prod_tipo_producto,
        estado
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('No fue posible preparar la solicitud: ' . $conn->error);
    }

    $stmt->bind_param(
        'ssssssssssssssssssssss',
        $codigo_producto,
        $hora_creacion_producto,
        $cert_tipo_documento,
        $cert_num_cc_interesado,
        $cert_primer_nombre_interesado,
        $cert_segundo_nombre_interesado,
        $cert_primer_apellido_interesado,
        $cert_segundo_apellido_interesado,
        $cert_numero_cel_interesado,
        $cert_correo_electronico,
        $cert_soporte_pago,
        $cert_medio_envio,
        $cert_npn_predio,
        $cert_fmi_predio,
        $cert_anio_vigencia,
        $cert_avaluo_terreno_tramite,
        $cert_direccion_predio,
        $cert_dest_econ_predio,
        $cert_area_terreno_predio,
        $cert_area_construccion_predio,
        $prod_tipo_producto,
        $estado_producto
    );

    if (!$stmt->execute()) {
        throw new Exception('No fue posible guardar la solicitud: ' . $stmt->error);
    }

    $id_producto = $conn->insert_id;
    $stmt->close();

    if (isset($_FILES['cert_sopor_pago']) && $_FILES['cert_sopor_pago']['error'] === UPLOAD_ERR_OK) {
        $directorio_base = __DIR__ . '/../../../soportes_pago/' . $codigo_producto . '/';

        if (!is_dir($directorio_base) && !mkdir($directorio_base, 0777, true)) {
            throw new Exception('No fue posible crear la carpeta para el soporte de pago.');
        }

        $nombre_archivo = basename($_FILES['cert_sopor_pago']['name']);
        $ruta_destino = $directorio_base . $nombre_archivo;

        if (!move_uploaded_file($_FILES['cert_sopor_pago']['tmp_name'], $ruta_destino)) {
            throw new Exception('No fue posible guardar el soporte de pago.');
        }

        $ruta_relativa = 'soportes_pago/' . $codigo_producto . '/' . $nombre_archivo;
        $estado_coordinacion = ESTADO_PRODUCTO_EN_COORDINACION;
        $sql_soporte = "UPDATE certificado_catastral
                        SET cert_soporte_pago = ?, estado = ?
                        WHERE certificado_id = ?";
        $stmt_soporte = $conn->prepare($sql_soporte);
        if (!$stmt_soporte) {
            throw new Exception('No fue posible preparar el soporte de pago: ' . $conn->error);
        }

        $stmt_soporte->bind_param('ssi', $ruta_relativa, $estado_coordinacion, $id_producto);
        if (!$stmt_soporte->execute()) {
            throw new Exception('No fue posible registrar el soporte de pago: ' . $stmt_soporte->error);
        }
        $stmt_soporte->close();
    }

    $conn->commit();

    header('Location: ../../../index.php?page=prod_catastrales/consultar_producto&guardado=1');
    exit;
} catch (Throwable $error) {
    $conn->rollback();
    $mensaje_error = json_encode(
        'Error al guardar la solicitud: ' . $error->getMessage(),
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    );

    echo "<script>
        alert($mensaje_error);
        window.location.href = '../../../index.php?page=prod_catastrales/solicitar_producto';
    </script>";
}

$conn->close();
