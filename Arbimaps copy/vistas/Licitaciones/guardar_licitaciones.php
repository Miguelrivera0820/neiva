<?php
/* ============================
   BUFFER + JSON SEGURO
============================ */
if (!ob_get_level()) ob_start();

function send_json($arr, $code = 200) {
    while (ob_get_level() > 0) ob_end_clean();
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($arr);
    exit;
}

/* ============================
   SESIÓN Y CONEXIÓN
============================ */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../conexion.php';

if (!isset($mysqli) || !$mysqli) {
    send_json([
        "success" => false,
        "message" => "No hay conexión a la base de datos"
    ], 500);
}

/* ============================
   1. GENERAR RADICADO
============================ */
$sqlRad = "SELECT lc_radicado FROM licitaciones
           WHERE lc_radicado LIKE 'LIC_ARB_%'
           ORDER BY id DESC LIMIT 1";

$resRad = $mysqli->query($sqlRad);

$num = 1;
if ($resRad && $resRad->num_rows > 0) {
    $row = $resRad->fetch_assoc();
    $num = (int) str_replace('LIC_ARB_', '', $row['lc_radicado']) + 1;
}
$lc_radicado = 'LIC_ARB_' . str_pad($num, 2, '0', STR_PAD_LEFT);

/* ============================
   2. DATOS DEL FORMULARIO
============================ */
$lc_tipo_entidad       = $_POST['lc_tipo_entidad'] ?? '';
$lc_proceso            = $_POST['lc_procesos'] ?? '';
$lc_municipio          = $_POST['lc_municipio'] ?? '';
$lc_departamento       = $_POST['lc_departamento'] ?? '';
$lc_entidad            = $_POST['lc_entidad'] ?? '';
$lc_valor              = (int) ($_POST['lc_valor'] ?? 0);
$lc_numero_proceso     = $_POST['lc_numero_proceso'] ?? '';
$lc_nombre_licitacion  = $_POST['lc_nombre_licitacion'] ?? '';
$lc_proyecto           = $_POST['lc_proyecto'] ?? '';
$lc_fecha_apertura     = $_POST['lc_fecha_apertura'] ?? null;
$lc_fecha_presentacion = $_POST['lc_fecha_presentacion'] ?? null;

/* ============================
   3. INSERT LICITACIÓN
============================ */
$sql = "INSERT INTO licitaciones (
    lc_radicado, lc_tipo_entidad, lc_proceso, lc_municipio,
    lc_departamento, lc_entidad, lc_valor, lc_numero_proceso,
    lc_nombre_licitacion, lc_proyecto,
    lc_fecha_apertura, lc_fecha_presentacion
) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    send_json([
        "success" => false,
        "message" => "Prepare licitaciones falló: " . $mysqli->error
    ], 500);
}

if (!$stmt->bind_param(
    "ssssssiissss",
    $lc_radicado,
    $lc_tipo_entidad,
    $lc_proceso,
    $lc_municipio,
    $lc_departamento,
    $lc_entidad,
    $lc_valor,
    $lc_numero_proceso,
    $lc_nombre_licitacion,
    $lc_proyecto,
    $lc_fecha_apertura,
    $lc_fecha_presentacion
)) {
    send_json([
        "success" => false,
        "message" => "bind_param licitaciones falló: " . $stmt->error
    ], 500);
}

if (!$stmt->execute()) {
    send_json(["success" => false, "message" => "execute licitaciones falló: " . $stmt->error], 500);
}

$lc_id = $mysqli->insert_id;
$stmt->close();

/* ============================
   4. CARPETAS
============================ */
$baseDir = __DIR__ . "/Arbitrium_licitaciones/";
$carpeta = $baseDir . $lc_radicado . "/";

$subcarpetas = ['Adendas/','Condiciones/','Proyecto/','Otros/'];

foreach ($subcarpetas as $dir) {
    $path = $carpeta . $dir;
    if (!file_exists($path)) {
        if (!mkdir($path, 0777, true)) {
            send_json([
                "success" => false,
                "message" => "No se pudo crear carpeta: " . $path
            ], 500);
        }
    }
}

/* ============================
   5. FUNCIÓN GUARDAR ARCHIVO
============================ */
function guardarArchivo($file, $ruta) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $nombre = uniqid() . "_" . basename($file['name']);
    $destino = $ruta . $nombre;

    if (!move_uploaded_file($file['tmp_name'], $destino)) {
        return null;
    }

    return $destino;
}

/* ============================
   6. ARCHIVOS ÚNICOS
============================ */
$archivosUnicos = [
    'lc_adendas'              => ['dir' => 'Adendas/',     'tipo' => 'ADENDA'],
    'lc_condiciones'          => ['dir' => 'Condiciones/', 'tipo' => 'CONDICIONES'],
    'lc_condiciones_proyecto' => ['dir' => 'Proyecto/',    'tipo' => 'PROYECTO']
];

foreach ($archivosUnicos as $campo => $info) {

    if (!isset($_FILES[$campo])) continue;
    if ($_FILES[$campo]['error'] !== UPLOAD_ERR_OK) continue;

    $archivo = [
        'name'     => $_FILES[$campo]['name'],
        'tmp_name' => $_FILES[$campo]['tmp_name'],
        'error'    => $_FILES[$campo]['error']
    ];

    $ruta = guardarArchivo($archivo, $carpeta . $info['dir']);

    if ($ruta) {
        $sqlDoc = "INSERT INTO documentos_licitacion
                   (lc_id, lc_doc_radicado, doc_tipo, doc_ruta, fecha_subida)
                   VALUES (?, ?, ?, ?, NOW())";

        $stmtDoc = $mysqli->prepare($sqlDoc);
        if (!$stmtDoc) {
            send_json(["success"=>false, "message"=>"Prepare doc falló: ".$mysqli->error], 500);
        }

        if (!$stmtDoc->bind_param("isss", $lc_id, $lc_radicado, $info['tipo'], $ruta)) {
            send_json(["success"=>false, "message"=>"bind_param doc falló: ".$stmtDoc->error], 500);
        }

        if (!$stmtDoc->execute()) {
            send_json(["success"=>false, "message"=>"execute doc falló: ".$stmtDoc->error], 500);
        }

        $stmtDoc->close();
    }
}


/* ============================
   7. ARCHIVOS MÚLTIPLES
============================ */
if (isset($_FILES['lc_otros_doc']) && isset($_FILES['lc_otros_doc']['name'])) {

    foreach ($_FILES['lc_otros_doc']['name'] as $i => $name) {

        if ($_FILES['lc_otros_doc']['error'][$i] !== UPLOAD_ERR_OK) continue;

        $archivo = [
            'name'     => $_FILES['lc_otros_doc']['name'][$i],
            'tmp_name' => $_FILES['lc_otros_doc']['tmp_name'][$i],
            'error'    => $_FILES['lc_otros_doc']['error'][$i]
        ];

        $ruta = guardarArchivo($archivo, $carpeta . "Otros/");

        if ($ruta) {
            $sqlDoc = "INSERT INTO documentos_licitacion
                       (lc_id, lc_doc_radicado, doc_tipo, doc_ruta, fecha_subida)
                       VALUES (?, ?, 'OTROS', ?, NOW())";

            $stmtDoc = $mysqli->prepare($sqlDoc);
            if (!$stmtDoc) {
                send_json(["success"=>false, "message"=>"Prepare otros falló: ".$mysqli->error], 500);
            }

            if (!$stmtDoc->bind_param("iss", $lc_id, $lc_radicado, $ruta)) {
                send_json(["success"=>false, "message"=>"bind_param otros falló: ".$stmtDoc->error], 500);
            }

            if (!$stmtDoc->execute()) {
                send_json(["success"=>false, "message"=>"execute otros falló: ".$stmtDoc->error], 500);
            }

            $stmtDoc->close();
        }
    }
}

/* ============================
   8. RESPUESTA FINAL
============================ */
send_json([
    "success"  => true,
    "radicado" => $lc_radicado,
    "message"  => "Licitación guardada correctamente"
]);
