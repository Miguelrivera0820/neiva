<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../../conexion.php';

ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
if (!ob_get_level()) ob_start();

function send_json($arr, $code = 200)
{
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

$data   = $_POST;
$origen = $_POST['origen'] ?? $_GET['origen'] ?? null;

$incapacitado          = strtolower(trim($_POST['incapacitado'] ?? 'no'));
$detalleDiscapacidad   = trim($_POST['con_discapacidad'] ?? '');
$esSi                  = in_array($incapacitado, ['si', 'sí', '1', 'true', 'on'], true);

if (!$esSi) {
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

    $rowExist = $resExist->fetch_assoc();
    $stmtExist->close();

    $data = array_merge($rowExist, $data);
}

$camposLista = ['con_enfermedades', 'con_alergias', 'con_medicamentos'];
foreach ($camposLista as $campo) {
    if (isset($_POST[$campo]) && is_array($_POST[$campo])) {
        $items = array_map(fn($x) => trim((string)$x), $_POST[$campo]);
        $items = array_values(array_filter($items, fn($x) => $x !== ''));
        $data[$campo] = (count($items) === 0) ? 'NINGUNA' : implode(', ', $items);
    } else {
        $data[$campo] = isset($data[$campo]) ? trim((string)$data[$campo]) : 'NINGUNA';
        if ($data[$campo] === '') $data[$campo] = 'NINGUNA';
    }
}

$data['con_contingencia'] = isset($data['con_contingencia']) ? trim((string)$data['con_contingencia']) : '';
if ($data['con_contingencia'] === '') $data['con_contingencia'] = 'NINGUNA';

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
        // conserva si viene de completar
    } else {
        $data[$field] = 0;
    }
}

$epsPost = trim((string)($_POST['con_eps'] ?? ''));
$otraEps = trim((string)($_POST['otra_eps'] ?? ''));

if ($epsPost === 'OTRO') {
    if ($otraEps === '') {
        send_json([
            "success" => false,
            "message" => "Debe especificar la EPS."
        ], 400);
    }
    $data['con_eps'] = $otraEps;
} else {
    $data['con_eps'] = $epsPost;
}

$data['con_correo_corporativo'] = $data['con_correo_corporativo'] ?? null;

$camposVinculacion = [
    'con_sede',
    'con_presencialidad',
    'con_cargo',
    'con_proyecto',
    'con_tipo_contrato',
    'con_fecha_inicio',
    'con_fecha_final',
    'con_duracion',
    'con_salario',
    'con_jefe',
    'con_per_cargo',
    'con_valor_proyecto',
    'con_contrato',
    'con_examenes',
    'con_cumplimiento',
    'con_acta',
    'con_actaFi'
];

$required_fields = [
    'con_nombres',
    'con_apellidos',
    'con_tipo_documento',
    'con_num_identidad',
    'con_celular',
    'con_direccion',
    'con_barrio',
    'con_ciudad',
    'con_FechaExpe',
    'con_lugarE',
    'con_fecha_nacimiento',
    'con_lugar_nacimiento',
    'con_correo',
    'con_correo_corporativo',
    'con_estado_civil',
    'con_nombre_conyuge',
    'con_enfermedades',
    'con_alergias',
    'con_medicamentos',
    'con_contingencia',
    'con_emergencia',
    'con_parentesco',
    'con_tel_emergencia',
    'con_num_cuenta',
    'con_tipo_cuenta',
    'con_financiera',
    'con_eps',
    'con_afp',
    'con_arl',
    'con_profesion',
    'con_escolaridad',
    'con_grado',
    'con_num_tarjeta',
    'con_expedicion',
    'con_genero',
    'con_raza',
    'con_vivienda',
    'con_estrato',
    'con_rh'
];

foreach ($required_fields as $field) {
    if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
        send_json([
            "success" => false,
            "message" => "Falta el campo obligatorio: $field"
        ], 400);
    }
}

function guardarArchivoWeb($archivo, $carpetaFisica, $carpetaWeb, $soloImagen = false)
{
    if ($archivo && isset($archivo['error']) && $archivo['error'] === UPLOAD_ERR_OK) {
        if ($soloImagen) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $archivo['tmp_name']);
            finfo_close($finfo);
            if (strpos((string)$mime, 'image/') !== 0) {
                throw new RuntimeException('El archivo no es una imagen válida.');
            }
        }

        if (!file_exists($carpetaFisica)) {
            if (!mkdir($carpetaFisica, 0777, true) && !is_dir($carpetaFisica)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $carpetaFisica));
            }
        }

        $ext = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $ext = $ext ? ('.' . strtolower($ext)) : '';

        $nombreArchivo = 'file_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . $ext;
        $rutaFisicaDestino = rtrim($carpetaFisica, '/\\') . DIRECTORY_SEPARATOR . $nombreArchivo;

        if (move_uploaded_file($archivo['tmp_name'], $rutaFisicaDestino)) {
            return rtrim($carpetaWeb, '/') . '/' . $nombreArchivo;
        }
        throw new RuntimeException('Failed to move uploaded file.');
    }
    return null;
}

$con_nombres       = trim((string)$data['con_nombres']);
$con_num_identidad = trim((string)$data['con_num_identidad']);

$baseWeb    = neiva_app_url('Arbimaps/vistas/Personal/Arbitrium_personal/');
$baseFisica = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\') . $baseWeb;

$carpetaRel           = $con_num_identidad . '_' . $con_nombres . '/';
$carpetaUsuarioFisica = $baseFisica . $carpetaRel;
$carpetaUsuarioWeb    = $baseWeb . $carpetaRel;

if (!file_exists($baseFisica)) {
    if (!mkdir($baseFisica, 0777, true) && !is_dir($baseFisica)) {
        send_json([
            "success" => false,
            "message" => "No se pudo crear el directorio base de archivos.",
            "dir"     => $baseFisica
        ], 500);
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
    $rutaSubcarpetaFisica = $carpetaUsuarioFisica . $subcarpeta;
    if (!file_exists($rutaSubcarpetaFisica)) {
        if (!mkdir($rutaSubcarpetaFisica, 0777, true) && !is_dir($rutaSubcarpetaFisica)) {
            send_json([
                "success" => false,
                "message" => "No se pudo crear subcarpeta.",
                "dir"     => $rutaSubcarpetaFisica
            ], 500);
        }
    }
}

try {
    $con_hoja_vida       = isset($_FILES['con_hoja_vida'])       ? guardarArchivoWeb($_FILES['con_hoja_vida'],       $carpetaUsuarioFisica . $subcarpetas['hoja_vida'],         $carpetaUsuarioWeb . $subcarpetas['hoja_vida']) : null;
    $con_certificado_eps = isset($_FILES['con_certificado_eps']) ? guardarArchivoWeb($_FILES['con_certificado_eps'], $carpetaUsuarioFisica . $subcarpetas['certificado_eps'],   $carpetaUsuarioWeb . $subcarpetas['certificado_eps']) : null;
    $con_rut             = isset($_FILES['con_rut'])             ? guardarArchivoWeb($_FILES['con_rut'],             $carpetaUsuarioFisica . $subcarpetas['rut'],               $carpetaUsuarioWeb . $subcarpetas['rut']) : null;
    $con_cedula          = isset($_FILES['con_cedula'])          ? guardarArchivoWeb($_FILES['con_cedula'],          $carpetaUsuarioFisica . $subcarpetas['cedula'],            $carpetaUsuarioWeb . $subcarpetas['cedula']) : null;
    $con_examenes        = isset($_FILES['con_examenes'])        ? guardarArchivoWeb($_FILES['con_examenes'],        $carpetaUsuarioFisica . $subcarpetas['examenes'],          $carpetaUsuarioWeb . $subcarpetas['examenes']) : null;
    $con_contrato        = isset($_FILES['con_contrato'])        ? guardarArchivoWeb($_FILES['con_contrato'],        $carpetaUsuarioFisica . $subcarpetas['contrato'],          $carpetaUsuarioWeb . $subcarpetas['contrato']) : null;
    $con_bancario        = isset($_FILES['con_bancario'])        ? guardarArchivoWeb($_FILES['con_bancario'],        $carpetaUsuarioFisica . $subcarpetas['con_bancario'],      $carpetaUsuarioWeb . $subcarpetas['con_bancario']) : null;
    $con_arlCer          = isset($_FILES['con_arlCer'])          ? guardarArchivoWeb($_FILES['con_arlCer'],          $carpetaUsuarioFisica . $subcarpetas['con_arlCer'],        $carpetaUsuarioWeb . $subcarpetas['con_arlCer']) : null;
    $con_antecedentes    = isset($_FILES['con_antecedentes'])    ? guardarArchivoWeb($_FILES['con_antecedentes'],    $carpetaUsuarioFisica . $subcarpetas['con_antecedentes'],  $carpetaUsuarioWeb . $subcarpetas['con_antecedentes']) : null;
    $con_tarjeta         = isset($_FILES['con_tarjeta'])         ? guardarArchivoWeb($_FILES['con_tarjeta'],         $carpetaUsuarioFisica . $subcarpetas['con_tarjeta'],       $carpetaUsuarioWeb . $subcarpetas['con_tarjeta']) : null;
    $photo               = isset($_FILES['photo'])               ? guardarArchivoWeb($_FILES['photo'],               $carpetaUsuarioFisica . $subcarpetas['photo'],             $carpetaUsuarioWeb . $subcarpetas['photo'], true) : null;
    $con_cerTarjeta      = isset($_FILES['con_cerTarjeta'])      ? guardarArchivoWeb($_FILES['con_cerTarjeta'],      $carpetaUsuarioFisica . $subcarpetas['con_cerTarjeta'],    $carpetaUsuarioWeb . $subcarpetas['con_cerTarjeta']) : null;
    $con_contraloria     = isset($_FILES['con_contraloria'])     ? guardarArchivoWeb($_FILES['con_contraloria'],     $carpetaUsuarioFisica . $subcarpetas['con_contraloria'],   $carpetaUsuarioWeb . $subcarpetas['con_contraloria']) : null;
    $con_procuraduria    = isset($_FILES['con_procuraduria'])    ? guardarArchivoWeb($_FILES['con_procuraduria'],    $carpetaUsuarioFisica . $subcarpetas['con_procuraduria'],  $carpetaUsuarioWeb . $subcarpetas['con_procuraduria']) : null;
    $con_cumplimiento    = isset($_FILES['con_cumplimiento'])    ? guardarArchivoWeb($_FILES['con_cumplimiento'],    $carpetaUsuarioFisica . $subcarpetas['con_cumplimiento'],  $carpetaUsuarioWeb . $subcarpetas['con_cumplimiento']) : null;
    $con_acta            = isset($_FILES['con_acta'])            ? guardarArchivoWeb($_FILES['con_acta'],            $carpetaUsuarioFisica . $subcarpetas['con_acta'],          $carpetaUsuarioWeb . $subcarpetas['con_acta']) : null;
    $con_actaFi          = isset($_FILES['con_actaFi'])          ? guardarArchivoWeb($_FILES['con_actaFi'],          $carpetaUsuarioFisica . $subcarpetas['con_actaFi'],        $carpetaUsuarioWeb . $subcarpetas['con_actaFi']) : null;
    $con_cerIncapacidad  = isset($_FILES['con_cerIncapacidad'])  ? guardarArchivoWeb($_FILES['con_cerIncapacidad'],  $carpetaUsuarioFisica . $subcarpetas['con_cerIncapacidad'],$carpetaUsuarioWeb . $subcarpetas['con_cerIncapacidad']) : null;
    $con_cerVacunacion   = isset($_FILES['con_cerVacunacion'])   ? guardarArchivoWeb($_FILES['con_cerVacunacion'],   $carpetaUsuarioFisica . $subcarpetas['con_cerVacunacion'], $carpetaUsuarioWeb . $subcarpetas['con_cerVacunacion']) : null;
} catch (Exception $e) {
    send_json([
        "success" => false,
        "message" => "Error al guardar archivos",
        "error"   => $e->getMessage()
    ], 500);
}

$existingFiles = null;
$stmtCheck = $mysqli->prepare("
    SELECT 
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

if ($existingFiles) {
    if ($con_contrato        === null && !empty($existingFiles['con_contrato']))        $con_contrato        = $existingFiles['con_contrato'];
    if ($con_hoja_vida       === null && !empty($existingFiles['con_hoja_vida']))       $con_hoja_vida       = $existingFiles['con_hoja_vida'];
    if ($con_certificado_eps === null && !empty($existingFiles['con_certificado_eps'])) $con_certificado_eps = $existingFiles['con_certificado_eps'];
    if ($con_rut             === null && !empty($existingFiles['con_rut']))             $con_rut             = $existingFiles['con_rut'];
    if ($con_cedula          === null && !empty($existingFiles['con_cedula']))          $con_cedula          = $existingFiles['con_cedula'];
    if ($con_examenes        === null && !empty($existingFiles['con_examenes']))        $con_examenes        = $existingFiles['con_examenes'];
    if ($con_bancario        === null && !empty($existingFiles['con_bancario']))        $con_bancario        = $existingFiles['con_bancario'];
    if ($con_arlCer          === null && !empty($existingFiles['con_arlCer']))          $con_arlCer          = $existingFiles['con_arlCer'];
    if ($con_antecedentes    === null && !empty($existingFiles['con_antecedentes']))    $con_antecedentes    = $existingFiles['con_antecedentes'];
    if ($con_tarjeta         === null && !empty($existingFiles['con_tarjeta']))         $con_tarjeta         = $existingFiles['con_tarjeta'];
    if ($photo               === null && !empty($existingFiles['photo']))               $photo               = $existingFiles['photo'];
    if ($con_cerTarjeta      === null && !empty($existingFiles['con_cerTarjeta']))      $con_cerTarjeta      = $existingFiles['con_cerTarjeta'];
    if ($con_contraloria     === null && !empty($existingFiles['con_contraloria']))     $con_contraloria     = $existingFiles['con_contraloria'];
    if ($con_procuraduria    === null && !empty($existingFiles['con_procuraduria']))    $con_procuraduria    = $existingFiles['con_procuraduria'];
    if ($con_cumplimiento    === null && !empty($existingFiles['con_cumplimiento']))    $con_cumplimiento    = $existingFiles['con_cumplimiento'];
    if ($con_acta            === null && !empty($existingFiles['con_acta']))            $con_acta            = $existingFiles['con_acta'];
    if ($con_actaFi          === null && !empty($existingFiles['con_actaFi']))          $con_actaFi          = $existingFiles['con_actaFi'];
    if ($con_cerIncapacidad  === null && !empty($existingFiles['con_cerIncapacidad']))  $con_cerIncapacidad  = $existingFiles['con_cerIncapacidad'];
    if ($con_cerVacunacion   === null && !empty($existingFiles['con_cerVacunacion']))   $con_cerVacunacion   = $existingFiles['con_cerVacunacion'];
}

if ($existingFiles) {
    foreach ($camposVinculacion as $campo) {
        if (!array_key_exists($campo, $data) || $data[$campo] === null || $data[$campo] === '') {
            $data[$campo] = $existingFiles[$campo] ?? null;
        }
    }
}

$otr_id     = (isset($_SESSION['otr_id']) && is_numeric($_SESSION['otr_id'])) ? (int)$_SESSION['otr_id'] : 0;
$con_otrosi = null;
$con_estado = 'ACTIVO';

$cols = [
    "con_nombres",
    "con_apellidos",
    "con_tipo_documento",
    "con_num_identidad",
    "con_FechaExpe",
    "con_lugarE",
    "con_fecha_nacimiento",
    "con_edad",
    "con_lugar_nacimiento",
    "con_direccion",
    "con_barrio",
    "con_ciudad",
    "con_tel_fijo",
    "con_celular",
    "con_correo",
    "con_correo_corporativo",

    "con_estado_civil",
    "con_nombre_conyuge",
    "con_enfermedades",
    "con_alergias",
    "con_contingencia",
    "con_medicamentos",
    "con_emergencia",
    "con_parentesco",
    "con_tel_emergencia",

    "con_num_cuenta",
    "con_tipo_cuenta",
    "con_financiera",
    "con_eps",
    "con_afp",
    "con_arl",

    "con_rh",

    "con_profesion",
    "con_grado",
    "con_num_tarjeta",
    "con_expedicion",
    "con_escolaridad",

    "con_sede",
    "con_presencialidad",
    "con_cargo",
    "con_proyecto",
    "con_tipo_contrato",
    "con_fecha_inicio",
    "con_fecha_final",
    "con_duracion",
    "con_salario",
    "con_jefe",
    "con_per_cargo",
    "con_valor_proyecto",

    "con_genero",
    "con_raza",
    "con_vivienda",
    "con_estrato",
    "con_discapacidad",

    "con_amarilla",
    "con_tetano1",
    "con_tetano2",
    "con_tetano3",
    "con_covid1",
    "con_covid2",
    "con_covid3",
    "con_influenza",
    "con_hepatitis_a",
    "con_hepatitis_c",
    "vph_1",
    "vph_2",
    "vph_3",
    "con_contrato",
    "con_hoja_vida",
    "con_certificado_eps",
    "con_rut",
    "con_cedula",
    "con_examenes",
    "con_bancario",
    "con_arlCer",
    "con_antecedentes",
    "con_tarjeta",
    "photo",
    "con_cerTarjeta",
    "con_contraloria",
    "con_procuraduria",
    "con_cumplimiento",
    "con_acta",
    "con_actaFi",
    "con_cerIncapacidad",
    "con_cerVacunacion",

    "con_otrosi",
    "con_estado",
    "otr_id"
];

$vals = [];
foreach ($cols as $c) {
    switch ($c) {
        case "con_contrato":        $vals[] = $con_contrato; break;
        case "con_hoja_vida":       $vals[] = $con_hoja_vida; break;
        case "con_certificado_eps": $vals[] = $con_certificado_eps; break;
        case "con_rut":             $vals[] = $con_rut; break;
        case "con_cedula":          $vals[] = $con_cedula; break;
        case "con_examenes":        $vals[] = $con_examenes; break;
        case "con_bancario":        $vals[] = $con_bancario; break;
        case "con_arlCer":          $vals[] = $con_arlCer; break;
        case "con_antecedentes":    $vals[] = $con_antecedentes; break;
        case "con_tarjeta":         $vals[] = $con_tarjeta; break;
        case "photo":               $vals[] = $photo; break;
        case "con_cerTarjeta":      $vals[] = $con_cerTarjeta; break;
        case "con_contraloria":     $vals[] = $con_contraloria; break;
        case "con_procuraduria":    $vals[] = $con_procuraduria; break;
        case "con_cumplimiento":    $vals[] = $con_cumplimiento; break;
        case "con_acta":            $vals[] = $con_acta; break;
        case "con_actaFi":          $vals[] = $con_actaFi; break;
        case "con_cerIncapacidad":  $vals[] = $con_cerIncapacidad; break;
        case "con_cerVacunacion":   $vals[] = $con_cerVacunacion; break;
        case "con_otrosi":          $vals[] = $con_otrosi; break;
        case "con_estado":          $vals[] = $con_estado; break;
        case "otr_id":              $vals[] = (string)$otr_id; break;
        default:                    $vals[] = $data[$c] ?? null; break;
    }
}

$placeholders = implode(',', array_fill(0, count($cols), '?'));
$updates      = implode(',', array_map(fn($c) => "$c=VALUES($c)", $cols));

$sql = "INSERT INTO contratacion (" . implode(',', $cols) . ")
        VALUES ($placeholders)
        ON DUPLICATE KEY UPDATE $updates";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    send_json([
        "success" => false,
        "message" => "Error al preparar la consulta",
        "error"   => $mysqli->error
    ], 500);
}

$types = str_repeat('s', count($cols));
$stmt->bind_param($types, ...$vals);

if ($stmt->execute()) {
    header("Location: /arbimaps/Arbimaps/index.php?page=Personal/mis_perfiles&guardado=ok");
    exit;
}

send_json([
    "success" => false,
    "message" => "Error al guardar los datos.",
    "error"   => $stmt->error
], 500);