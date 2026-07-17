<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../conexion.php';
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
if (!ob_get_level()) ob_start();

function send_json($arr, $code = 200) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($arr);
    exit;
}
if (!isset($mysqli) || $mysqli === null) {
    send_json([
        "success" => false,
        "message" => "Error: no se encontró la conexión \$mysqli. Revisa conexion.php"
    ], 500);
}
$data           = $_POST;
$origen         = $_POST['origen'] ?? $_GET['origen'] ?? null;
$incapacitado   = $_POST['incapacitado'] ?? 'no';

$detalleDiscapacidad = trim($_POST['con_discapacidad'] ?? '');

if ($incapacitado === 'no') {
    $data['con_discapacidad'] = 'NO';
} else {
    if ($detalleDiscapacidad === '') {
        send_json([
            "success" => false,
            "message" => "Debe especificar la discapacidad."
        ], 400);
    }
    $data['con_discapacidad'] = 'SI - ' . $detalleDiscapacidad;
}

// === 2.1 Si viene de completar_datos, cargar datos actuales de BD y mezclarlos ===
if ($origen === 'completar') {
    if (empty($data['con_num_identidad'])) {
        send_json([
            "success" => false,
            "message" => "No se recibió el número de identidad."
        ], 400);
    }
    $stmtExist = $mysqli->prepare("SELECT * FROM contratacion WHERE con_num_identidad = ?");
    if (!$stmtExist) {
        send_json([
            "success" => false,
            "message" => "Error preparando la consulta de búsqueda",
            "error"   => $mysqli->error
        ], 500);
    }
    $stmtExist->bind_param("s", $data['con_num_identidad']);
    $stmtExist->execute();
    $resExist = $stmtExist->get_result();

    if ($resExist->num_rows === 0) {
        send_json([
            "success" => false,
            "message" => "No existe un registro para esa cédula."
        ], 404);
    }
    $rowExist   = $resExist->fetch_assoc();
    $stmtExist->close();
    $data       = array_merge($rowExist, $data);
}

// ====== 2.2 Convertir arrays (campo[]) a string para guardar en BD ======
$camposLista = ['con_enfermedades', 'con_alergias', 'con_medicamentos'];
foreach ($camposLista as $campo) {
    if (isset($_POST[$campo]) && is_array($_POST[$campo])) {
        $items = array_map(function ($x) {
            return trim((string)$x);
        }, $_POST[$campo]);
        $items = array_values(array_filter($items, fn($x) => $x !== ''));

        if (count($items) === 0) {
            $data[$campo] = 'NINGUNA';
        } else {
            $data[$campo] = implode(', ', $items);
        }
    } else {
        $data[$campo] = isset($data[$campo]) ? trim((string)$data[$campo]) : 'NINGUNA';
        if ($data[$campo] === '') $data[$campo] = 'NINGUNA';
    }
}

$data['con_contingencia'] = isset($data['con_contingencia']) ? trim((string)$data['con_contingencia']) : '';
if ($data['con_contingencia'] === '') $data['con_contingencia'] = 'NINGUNA';

// === 3. Manejo de campos booleanos (checkbox) ===
$bool_fields = [
    'con_amarilla',
    'con_tetano1',
    'con_tetano2',
    'con_tetano3',
    'con_covid1',
    'con_covid2',
    'con_covid3',
    'con_influenza',
    'con_hepatitis_a',
    'con_hepatitis_c',
    'vph_1',
    'vph_2',
    'vph_3'
];

foreach ($bool_fields as $field) {
    if (isset($_POST[$field])) {
        $data[$field] = 1;
    } elseif ($origen === 'completar') {
    } else {
        $data[$field] = 0;
    }
}

// === 4. Manejo de campos opcionales ===
$data['con_correo_corporativo'] = $data['con_correo_corporativo'] ?? null;
$data['con_jefe']               = $data['con_jefe'] ?? null;

// === 5. Ajuste EPS (OTRO) ===
$epsPost = $_POST['con_eps'] ?? null;
if ($epsPost === 'OTRO' && !empty($_POST['otra_eps'])) {
    $data['con_eps'] = trim($_POST['otra_eps']);
} elseif ($epsPost !== null) {
    $data['con_eps'] = $epsPost;
}

// === 6. Validación de campos obligatorios ===
$required_fields = ['con_nombres', 'con_apellidos', 'con_tipo_documento', 'con_num_identidad'];
foreach ($required_fields as $field) {
    if (empty($data[$field])) {
        send_json([
            "success" => false,
            "message" => "Falta el campo obligatorio: $field"
        ], 400);
    }
}

// === 7. Preparar la consulta de INSERT ===
$sql = "INSERT INTO contratacion (
                            con_nombres, 
                            con_apellidos, 
                            con_tipo_documento, 
                            con_num_identidad, 
                            con_FechaExpe,
                            con_lugarE, 
                            con_fecha_nacimiento, 
                            con_edad, 
                            con_lugar_nacimiento, 
                            con_direccion,
                            con_barrio, 
                            con_ciudad, 
                            con_tel_fijo, 
                            con_celular, 
                            con_correo,
                            con_correo_corporativo, 
                            con_estado_civil, 
                            con_nombre_conyuge,
                            con_enfermedades, 
                            con_alergias, 
                            con_contingencia,
                            con_medicamentos, 
                            con_emergencia, 
                            con_parentesco,
                            con_tel_emergencia, 
                            con_num_cuenta, 
                            con_tipo_cuenta, 
                            con_financiera,
                            con_eps, 
                            con_afp, 
                            con_arl, 
                            con_rh, 
                            con_profesion, 
                            con_grado, 
                            con_num_tarjeta,
                            con_expedicion, 
                            con_sede, 
                            con_presencialidad,
                            con_cargo, 
                            con_proyecto, 
                            con_tipo_contrato, 
                            con_fecha_inicio,
                            con_fecha_final, 
                            con_duracion, 
                            con_salario, 
                            con_jefe, 
                            con_per_cargo,
                            con_valor_proyecto, 
                            con_genero,
                            con_raza, 
                            con_escolaridad, 
                            con_vivienda, 
                            con_estrato, 
                            con_discapacidad,
                            con_amarilla, 
                            con_tetano1, 
                            con_tetano2, 
                            con_tetano3, 
                            con_covid1, 
                            con_covid2,
                            con_covid3, 
                            con_influenza, 
                            con_hepatitis_a, 
                            con_hepatitis_c, 
                            vph_1, 
                            vph_2, 
                            vph_3,
                            con_contrato, 
                            con_hoja_vida, 
                            con_certificado_eps, 
                            con_rut, 
                            con_cedula,
                            con_examenes, 
                            con_bancario, 
                            con_arlCer, 
                            con_antecedentes, 
                            con_tarjeta,
                            photo, 
                            con_cerTarjeta,
                            con_contraloria, 
                            con_procuraduria, 
                            con_cumplimiento, 
                            con_acta, 
                            con_actaFi,
                            con_cerIncapacidad, 
                            con_cerVacunacion,
                            con_otrosi, 
                            con_estado, 
                            otr_id
                        ) VALUES (
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                            ?, ?, ?, ?, ?, ?, ?, ?, ?
                        )
                        ON DUPLICATE KEY UPDATE
                            con_nombres             = VALUES(con_nombres),
                            con_apellidos           = VALUES(con_apellidos),
                            con_tipo_documento      = VALUES(con_tipo_documento),
                            con_FechaExpe           = VALUES(con_FechaExpe),
                            con_lugarE              = VALUES(con_lugarE),
                            con_fecha_nacimiento    = VALUES(con_fecha_nacimiento),
                            con_edad                = VALUES(con_edad),
                            con_lugar_nacimiento    = VALUES(con_lugar_nacimiento),
                            con_direccion           = VALUES(con_direccion),
                            con_barrio              = VALUES(con_barrio),
                            con_ciudad              = VALUES(con_ciudad),
                            con_tel_fijo            = VALUES(con_tel_fijo),
                            con_celular             = VALUES(con_celular),
                            con_correo              = VALUES(con_correo),
                            con_correo_corporativo  = VALUES(con_correo_corporativo),
                            con_estado_civil        = VALUES(con_estado_civil),
                            con_nombre_conyuge      = VALUES(con_nombre_conyuge),
                            con_enfermedades        = VALUES(con_enfermedades),
                            con_alergias            = VALUES(con_alergias),
                            con_contingencia        = VALUES(con_contingencia),
                            con_medicamentos        = VALUES(con_medicamentos),
                            con_emergencia          = VALUES(con_emergencia),
                            con_parentesco          = VALUES(con_parentesco),
                            con_tel_emergencia      = VALUES(con_tel_emergencia),
                            con_num_cuenta          = VALUES(con_num_cuenta),
                            con_tipo_cuenta         = VALUES(con_tipo_cuenta),
                            con_financiera          = VALUES(con_financiera),
                            con_eps                 = VALUES(con_eps),
                            con_afp                 = VALUES(con_afp),
                            con_arl                 = VALUES(con_arl),
                            con_rh                  = VALUES(con_rh),
                            con_profesion           = VALUES(con_profesion),
                            con_grado               = VALUES(con_grado),
                            con_num_tarjeta         = VALUES(con_num_tarjeta),
                            con_expedicion          = VALUES(con_expedicion),
                            con_sede                = VALUES(con_sede),
                            con_presencialidad      = VALUES(con_presencialidad),
                            con_cargo               = VALUES(con_cargo),
                            con_proyecto            = VALUES(con_proyecto),
                            con_tipo_contrato       = VALUES(con_tipo_contrato),
                            con_fecha_inicio        = VALUES(con_fecha_inicio),
                            con_fecha_final         = VALUES(con_fecha_final),
                            con_duracion            = VALUES(con_duracion),
                            con_salario             = VALUES(con_salario),
                            con_jefe                = VALUES(con_jefe),
                            con_per_cargo           = VALUES(con_per_cargo),
                            con_valor_proyecto      = VALUES(con_valor_proyecto),
                            con_genero              = VALUES(con_genero),
                            con_raza                = VALUES(con_raza),
                            con_escolaridad         = VALUES(con_escolaridad),
                            con_vivienda            = VALUES(con_vivienda),
                            con_estrato             = VALUES(con_estrato),
                            con_discapacidad        = VALUES(con_discapacidad),
                            con_amarilla            = VALUES(con_amarilla),
                            con_tetano1             = VALUES(con_tetano1),
                            con_tetano2             = VALUES(con_tetano2),
                            con_tetano3             = VALUES(con_tetano3),
                            con_covid1              = VALUES(con_covid1),
                            con_covid2              = VALUES(con_covid2),
                            con_covid3              = VALUES(con_covid3),
                            con_influenza           = VALUES(con_influenza),
                            con_hepatitis_a         = VALUES(con_hepatitis_a),
                            con_hepatitis_c         = VALUES(con_hepatitis_c),
                            vph_1                   = VALUES(vph_1),
                            vph_2                   = VALUES(vph_2),
                            vph_3                   = VALUES(vph_3),
                            con_contrato            = VALUES(con_contrato),
                            con_hoja_vida           = VALUES(con_hoja_vida),
                            con_certificado_eps     = VALUES(con_certificado_eps),
                            con_rut                 = VALUES(con_rut),
                            con_cedula              = VALUES(con_cedula),
                            con_examenes            = VALUES(con_examenes),
                            con_bancario            = VALUES(con_bancario),
                            con_arlCer              = VALUES(con_arlCer),
                            con_antecedentes        = VALUES(con_antecedentes),
                            con_tarjeta             = VALUES(con_tarjeta),
                            photo                   = VALUES(photo),
                            con_cerTarjeta          = VALUES(con_cerTarjeta),
                            con_contraloria         = VALUES(con_contraloria),
                            con_procuraduria        = VALUES(con_procuraduria),
                            con_cumplimiento        = VALUES(con_cumplimiento),
                            con_acta                = VALUES(con_acta),
                            con_actaFi              = VALUES(con_actaFi),
                            con_cerIncapacidad      = VALUES(con_cerIncapacidad),
                            con_cerVacunacion       = VALUES(con_cerVacunacion),
                            con_otrosi              = VALUES(con_otrosi),
                            con_estado              = VALUES(con_estado),
                            otr_id                  = VALUES(otr_id)";
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    send_json([
        "success" => false,
        "message" => "Error al preparar la consulta",
        "error"   => $mysqli->error
    ], 500);
}

// === 8. Función para guardar archivos ===
function guardarArchivo($archivo, $carpetaUsuario)
{
    if ($archivo && isset($archivo['error']) && $archivo['error'] === UPLOAD_ERR_OK) {
        if (!file_exists($carpetaUsuario)) {
            if (!mkdir($carpetaUsuario, 0777, true) && !is_dir($carpetaUsuario)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $carpetaUsuario));
            }
        }
        $nombreArchivo = basename($archivo['name']);
        $rutaDestino   = $carpetaUsuario . $nombreArchivo;
        if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            return $rutaDestino;
        } else {
            throw new RuntimeException('Failed to move uploaded file.');
        }
    }
    return null;
}


// === 9. Directorio para archivos ===
$con_nombres       = trim($data['con_nombres']);
$con_num_identidad = trim($data['con_num_identidad']);

$uploadDir      = 'Arbitrium_personal/';
$carpetaUsuario = $uploadDir . $con_num_identidad . '_' . $con_nombres . '/';

if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
        throw new RuntimeException(sprintf('Directory "%s" was not created', $uploadDir));
    }
}

$subcarpetas = [
    'hoja_vida'          => 'Hoja_de_Vida/',
    'contrato'           => 'Contrato/',
    'certificado_eps'    => 'Certificado_EPS/',
    'rut'                => 'RUT/',
    'cedula'             => 'Cedula/',
    'examenes'           => 'Examenes/',
    'con_bancario'       => 'Bancario/',
    'con_arlCer'         => 'Arl/',
    'con_antecedentes'   => 'Antecedentes/',
    'con_tarjeta'        => 'Tarjeta/',
    'photo'              => 'photo/',
    'con_cerTarjeta'     => 'Certificado_Tarjeta/',
    'con_contraloria'    => 'Contraloria/',
    'con_procuraduria'   => 'Procuraduria/',
    'con_cumplimiento'   => 'Poliza/',
    'con_acta'           => 'Acta_Inicial/',
    'con_actaFi'         => 'Acta_Final/',
    'con_cerIncapacidad' => 'Discapacidad/',
    'con_cerVacunacion'  => 'Vacunacion/'
];

foreach ($subcarpetas as $subcarpeta) {
    $rutaSubcarpeta = $carpetaUsuario . $subcarpeta;
    if (!file_exists($rutaSubcarpeta)) {
        if (!mkdir($rutaSubcarpeta, 0777, true) && !is_dir($rutaSubcarpeta)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $rutaSubcarpeta));
        }
    }
}

// === 10. Guardar archivos ===
try {
    $con_hoja_vida       = isset($_FILES['con_hoja_vida'])       ? guardarArchivo($_FILES['con_hoja_vida'],       $carpetaUsuario . $subcarpetas['hoja_vida']) : null;
    $con_certificado_eps = isset($_FILES['con_certificado_eps']) ? guardarArchivo($_FILES['con_certificado_eps'], $carpetaUsuario . $subcarpetas['certificado_eps']) : null;
    $con_rut             = isset($_FILES['con_rut'])             ? guardarArchivo($_FILES['con_rut'],             $carpetaUsuario . $subcarpetas['rut']) : null;
    $con_cedula          = isset($_FILES['con_cedula'])          ? guardarArchivo($_FILES['con_cedula'],          $carpetaUsuario . $subcarpetas['cedula']) : null;
    $con_examenes        = isset($_FILES['con_examenes'])        ? guardarArchivo($_FILES['con_examenes'],        $carpetaUsuario . $subcarpetas['examenes']) : null;
    $con_contrato        = isset($_FILES['con_contrato'])        ? guardarArchivo($_FILES['con_contrato'],        $carpetaUsuario . $subcarpetas['contrato']) : null;
    $con_bancario        = isset($_FILES['con_bancario'])        ? guardarArchivo($_FILES['con_bancario'],        $carpetaUsuario . $subcarpetas['con_bancario']) : null;
    $con_arlCer          = isset($_FILES['con_arlCer'])          ? guardarArchivo($_FILES['con_arlCer'],          $carpetaUsuario . $subcarpetas['con_arlCer']) : null;
    $con_antecedentes    = isset($_FILES['con_antecedentes'])    ? guardarArchivo($_FILES['con_antecedentes'],    $carpetaUsuario . $subcarpetas['con_antecedentes']) : null;
    $con_tarjeta         = isset($_FILES['con_tarjeta'])         ? guardarArchivo($_FILES['con_tarjeta'],         $carpetaUsuario . $subcarpetas['con_tarjeta']) : null;
    $photo               = isset($_FILES['photo'])               ? guardarArchivo($_FILES['photo'],               $carpetaUsuario . $subcarpetas['photo']) : null;
    $con_cerTarjeta      = isset($_FILES['con_cerTarjeta'])      ? guardarArchivo($_FILES['con_cerTarjeta'],      $carpetaUsuario . $subcarpetas['con_cerTarjeta']) : null;
    $con_contraloria     = isset($_FILES['con_contraloria'])     ? guardarArchivo($_FILES['con_contraloria'],     $carpetaUsuario . $subcarpetas['con_contraloria']) : null;
    $con_procuraduria    = isset($_FILES['con_procuraduria'])    ? guardarArchivo($_FILES['con_procuraduria'],    $carpetaUsuario . $subcarpetas['con_procuraduria']) : null;
    $con_cumplimiento    = isset($_FILES['con_cumplimiento'])    ? guardarArchivo($_FILES['con_cumplimiento'],    $carpetaUsuario . $subcarpetas['con_cumplimiento']) : null;
    $con_acta            = isset($_FILES['con_acta'])            ? guardarArchivo($_FILES['con_acta'],            $carpetaUsuario . $subcarpetas['con_acta']) : null;
    $con_actaFi          = isset($_FILES['con_actaFi'])          ? guardarArchivo($_FILES['con_actaFi'],          $carpetaUsuario . $subcarpetas['con_actaFi']) : null;
    $con_cerIncapacidad  = isset($_FILES['con_cerIncapacidad'])  ? guardarArchivo($_FILES['con_cerIncapacidad'],  $carpetaUsuario . $subcarpetas['con_cerIncapacidad']) : null;
    $con_cerVacunacion   = isset($_FILES['con_cerVacunacion'])   ? guardarArchivo($_FILES['con_cerVacunacion'],   $carpetaUsuario . $subcarpetas['con_cerVacunacion']) : null;
} catch (Exception $e) {
    send_json([
        "success" => false,
        "message" => "Error al guardar archivos",
        "error"   => $e->getMessage()
    ], 500);
}

// === 10.1. Si ya existe el registro, conservar rutas de archivos antiguos si no se subió uno nuevo ===
$existingFiles = null;
$stmtCheck = $mysqli->prepare("
    SELECT 
        con_contrato, 
        con_hoja_vida, 
        con_certificado_eps, 
        con_rut, 
        con_cedula,
        con_examenes, 
        con_bancario, 
        con_arlCer, 
        con_antecedentes, 
        con_tarjeta,
        photo, 
        con_cerTarjeta, 
        con_contraloria, 
        con_procuraduria, 
        con_cumplimiento,
        con_acta, 
        con_actaFi, 
        con_cerIncapacidad, 
        con_cerVacunacion
    FROM contratacion
    WHERE con_num_identidad = ?
");
if ($stmtCheck) {
    $stmtCheck->bind_param("s", $data['con_num_identidad']);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();
    if ($resCheck && $resCheck->num_rows > 0) {
        $existingFiles = $resCheck->fetch_assoc();
    }
    $stmtCheck->close();
}

// Si existe registro previo y NO se subió archivo nuevo en este campo, conservar ruta anterior
if ($existingFiles) {
    if ($con_contrato      === null && !empty($existingFiles['con_contrato']))      $con_contrato      = $existingFiles['con_contrato'];
    if ($con_hoja_vida     === null && !empty($existingFiles['con_hoja_vida']))     $con_hoja_vida     = $existingFiles['con_hoja_vida'];
    if ($con_certificado_eps === null && !empty($existingFiles['con_certificado_eps'])) $con_certificado_eps = $existingFiles['con_certificado_eps'];
    if ($con_rut           === null && !empty($existingFiles['con_rut']))           $con_rut           = $existingFiles['con_rut'];
    if ($con_cedula        === null && !empty($existingFiles['con_cedula']))        $con_cedula        = $existingFiles['con_cedula'];
    if ($con_examenes      === null && !empty($existingFiles['con_examenes']))      $con_examenes      = $existingFiles['con_examenes'];
    if ($con_bancario      === null && !empty($existingFiles['con_bancario']))      $con_bancario      = $existingFiles['con_bancario'];
    if ($con_arlCer        === null && !empty($existingFiles['con_arlCer']))        $con_arlCer        = $existingFiles['con_arlCer'];
    if ($con_antecedentes  === null && !empty($existingFiles['con_antecedentes']))  $con_antecedentes  = $existingFiles['con_antecedentes'];
    if ($con_tarjeta       === null && !empty($existingFiles['con_tarjeta']))       $con_tarjeta       = $existingFiles['con_tarjeta'];
    if ($photo             === null && !empty($existingFiles['photo']))             $photo             = $existingFiles['photo'];
    if ($con_cerTarjeta    === null && !empty($existingFiles['con_cerTarjeta']))    $con_cerTarjeta    = $existingFiles['con_cerTarjeta'];
    if ($con_contraloria   === null && !empty($existingFiles['con_contraloria']))   $con_contraloria   = $existingFiles['con_contraloria'];
    if ($con_procuraduria  === null && !empty($existingFiles['con_procuraduria']))  $con_procuraduria  = $existingFiles['con_procuraduria'];
    if ($con_cumplimiento  === null && !empty($existingFiles['con_cumplimiento']))  $con_cumplimiento  = $existingFiles['con_cumplimiento'];
    if ($con_acta          === null && !empty($existingFiles['con_acta']))          $con_acta          = $existingFiles['con_acta'];
    if ($con_actaFi        === null && !empty($existingFiles['con_actaFi']))        $con_actaFi        = $existingFiles['con_actaFi'];
    if ($con_cerIncapacidad === null && !empty($existingFiles['con_cerIncapacidad'])) $con_cerIncapacidad = $existingFiles['con_cerIncapacidad'];
    if ($con_cerVacunacion === null && !empty($existingFiles['con_cerVacunacion'])) $con_cerVacunacion = $existingFiles['con_cerVacunacion'];
}

// === 11. Valores extra para las columnas nuevas ===
if (isset($_SESSION['otr_id']) && is_numeric($_SESSION['otr_id'])) {
    $otr_id = (int) $_SESSION['otr_id'];
} else {
    $otr_id = 0;
}

$con_otrosi = null;
$con_estado = 'ACTIVO';

// === 12. bind_param ===
$stmt->bind_param(
    str_repeat('s', 88) . 'i',
    $data['con_nombres'],
    $data['con_apellidos'],
    $data['con_tipo_documento'],
    $data['con_num_identidad'],
    $data['con_FechaExpe'],
    $data['con_lugarE'],
    $data['con_fecha_nacimiento'],
    $data['con_edad'],
    $data['con_lugar_nacimiento'],
    $data['con_direccion'],
    $data['con_barrio'],
    $data['con_ciudad'],
    $data['con_tel_fijo'],
    $data['con_celular'],
    $data['con_correo'],
    $data['con_correo_corporativo'],
    $data['con_estado_civil'],
    $data['con_nombre_conyuge'],
    $data['con_enfermedades'],
    $data['con_alergias'],
    $data['con_contingencia'],
    $data['con_medicamentos'],
    $data['con_emergencia'],
    $data['con_parentesco'],
    $data['con_tel_emergencia'],
    $data['con_num_cuenta'],
    $data['con_tipo_cuenta'],
    $data['con_financiera'],
    $data['con_eps'],
    $data['con_afp'],
    $data['con_arl'],
    $data['con_rh'],
    $data['con_profesion'],
    $data['con_grado'],
    $data['con_num_tarjeta'],
    $data['con_expedicion'],
    $data['con_sede'],
    $data['con_presencialidad'],
    $data['con_cargo'],
    $data['con_proyecto'],
    $data['con_tipo_contrato'],
    $data['con_fecha_inicio'],
    $data['con_fecha_final'],
    $data['con_duracion'],
    $data['con_salario'],
    $data['con_jefe'],
    $data['con_per_cargo'],
    $data['con_valor_proyecto'],
    $data['con_genero'],
    $data['con_raza'],
    $data['con_escolaridad'],
    $data['con_vivienda'],
    $data['con_estrato'],
    $data['con_discapacidad'],
    $data['con_amarilla'],
    $data['con_tetano1'],
    $data['con_tetano2'],
    $data['con_tetano3'],
    $data['con_covid1'],
    $data['con_covid2'],
    $data['con_covid3'],
    $data['con_influenza'],
    $data['con_hepatitis_a'],
    $data['con_hepatitis_c'],
    $data['vph_1'],
    $data['vph_2'],
    $data['vph_3'],
    $con_contrato,
    $con_hoja_vida,
    $con_certificado_eps,
    $con_rut,
    $con_cedula,
    $con_examenes,
    $con_bancario,
    $con_arlCer,
    $con_antecedentes,
    $con_tarjeta,
    $photo,
    $con_cerTarjeta,
    $con_contraloria,
    $con_procuraduria,
    $con_cumplimiento,
    $con_acta,
    $con_actaFi,
    $con_cerIncapacidad,
    $con_cerVacunacion,
    $con_otrosi,
    $con_estado,
    $otr_id
);

// === 13. Ejecutar ===
if ($stmt->execute()) {
    $con_id = $mysqli->insert_id;
    $sqlCopia = "INSERT INTO copia_historial_contratacion (
        con_id,
        con_num_identidad,
        cop_sede,
        cop_presencialidad,
        cop_cargo,
        cop_proyecto,
        cop_tipo_contrato,
        cop_fecha_inicio,
        cop_fecha_final,
        cop_duracion,
        cop_salario,
        cop_jefe,
        cop_per_cargo,
        cop_valor_proyecto,
        cop_contrato,
        cop_examenes,
        cop_acta,
        cop_actaFi
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmtCopia = $mysqli->prepare($sqlCopia);

    if ($stmtCopia) {
        $stmtCopia->bind_param(
            "isssssssssisssssss",
            $con_id,
            $data['con_num_identidad'],
            $data['con_sede'],
            $data['con_presencialidad'],
            $data['con_cargo'],
            $data['con_proyecto'],
            $data['con_tipo_contrato'],
            $data['con_fecha_inicio'],
            $data['con_fecha_final'],
            $data['con_duracion'],
            $data['con_salario'],
            $data['con_jefe'],
            $data['con_per_cargo'],
            $data['con_valor_proyecto'],
            $con_contrato,
            $con_examenes,
            $con_acta,
            $con_actaFi
        );
        $stmtCopia->execute();
        $stmtCopia->close();
    }
    if ($origen === 'editar') {
        if ($stmt->affected_rows > 0) {
            $cedula = urlencode($data['con_num_identidad']);
            header("Location: ../../index.php?page=Personal/personal_activo&guardado=ok");
            exit;
        }
        else {
            $cedula = urlencode($data['con_num_identidad']);
            header("Location: ../../index.php?page=Personal/personal_activo&&guardado=error");
            exit;
        }
    } else {
        if ($stmt->affected_rows > 0) {
            send_json([
                "success" => true,
                "message" => "Datos guardados correctamente."
            ], 200);
        } else {
            send_json([
                "success" => false,
                "message" => "Error al guardar los datos.",
                "error"   => $stmt->error
            ], 500);
        }
        // send_json ya hace exit
    }
}
