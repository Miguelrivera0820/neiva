<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';
require_once dirname(__DIR__, 4) . '/vendor/autoload.php';

neiva_bootstrap();
neiva_require_methods('POST', true);
neiva_require_permission('menu.tramites', $PERMISOS, true);
neiva_require_csrf('global', true);

use Dompdf\Dompdf;
use Dompdf\Options;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

// --- Recibir datos ---
$cod_tramite            = $_POST['cod_tramite'] ?? '';
$tipo_mutacion_tramite  = $_POST['tipo_mutacion_tramite'] ?? '';
$tipo_proceso_muta      = $_POST['tipo_proceso_muta'] ?? '';
$fecha_resp_tramite     = $_POST['fecha_respuesta_tramite'] ?? null;
$actividad_a_realizar   = $_POST['actividad_tramite'] ?? '';
$nombre_comp_interesado = $_POST['nombre_interesado'] ?? '';
$telefono_interesado    = $_POST['telefono_interesado'] ?? '';
$correo_interesado      = $_POST['correo_interesado'] ?? '';
$cod_catastro           = $_POST['cod_catastro'] ?? '';
$fmi_predio             = $_POST['fmi_predio'] ?? '';
$direccion_predio       = $_POST['direccion_predio'] ?? '';
$propietarios_predio    = $_POST['propietarios_predio'] ?? '';
$tipo_oficio            = $_POST['tipo_oficio'] ?? '';
$cont_documento = $_POST['cont_documento'] ?? $_POST['contenido_html'] ?? '';

// --- Validaciones básicas ---
if (empty($cod_tramite)) {
    echo json_encode(['success' => false, 'error' => 'Falta el código del trámite.']);
    exit;
}
if (trim($cont_documento) === '') {
    echo json_encode(['success' => false, 'error' => 'El contenido del documento está vacío.']);
    exit;
}


// Traer fecha de radicación para la plantilla 
$fecha_rad_tramite = null;
try {
    $stmtFecha = $mysqli->prepare("SELECT fecha_rad FROM tramite_radicacion WHERE cod_tramite = ?");
    $stmtFecha->bind_param("s", $cod_tramite);
    $stmtFecha->execute();
    $resFecha = $stmtFecha->get_result();
    if ($rowF = $resFecha->fetch_assoc()) {
        $fecha_rad_tramite = $rowF['fecha_rad'];
    }
    $stmtFecha->close();
} catch (Exception $e) {}

$cont_es_plano = (strip_tags($cont_documento) === $cont_documento);

if ($cont_es_plano) {
    $cont_documento = preg_replace('/\r\n|\r|\n/', '<br>', trim($cont_documento));

    // Armamos la misma plantilla con <b> que usa la vista previa 
    $nombre_funcionario = (($_SESSION['nombre_usuario'] ?? '') . ' ' . ($_SESSION['apellido_usuario'] ?? ''));
    $cargo_funcionario  = ($_SESSION['rol_usuario'] ?? '');

    $plantilla_html = "
        <b>Asunto:</b> Comunicación sobre la procedencia del trámite <b>{$cod_tramite}</b>.
        <br><br>
        Estimado(a) Señor(a)/Sr(a). <b>{$nombre_comp_interesado}</b>:
        <br><br>
        Con relación a su solicitud presentada el día <b>" . ($fecha_rad_tramite ?: '') . "</b> referente a <b>{$tipo_mutacion_tramite}</b> con actividad <b>{$tipo_proceso_muta}</b>, nos permitimos informarle que, tras la revisión pertinente, su petición ha sido aceptada para su procedencia y se dará solución en los próximos días.
        <br><br>
        En este sentido, le comunicamos que el proceso se encuentra en curso y estará resuelto en la fecha <b>{$fecha_resp_tramite}</b>, o bien, en el transcurso del presente mes, según lo previsto en nuestros plazos de trámite.
        <br><br>
        Agradecemos su paciencia y comprensión durante el desarrollo de este proceso. En caso de requerir información adicional, se le comunicará a través de los medios suministrados por el solicitante.
        <br><br>
        Cordialmente,
        <br><br><br>
        _________________________________
        <br>
        {$nombre_funcionario}
        <br>
        {$cargo_funcionario}
        <br>
        Dirección de Catastro Neiva
        ";
    $cont_documento = $plantilla_html;
}

// --- Crear carpeta destino ---
$anio = substr($cod_tramite, 4, 4);
$ruta_base = "../../tramites_conservacion/$anio/$cod_tramite/procede";
if (!file_exists($ruta_base)) {
    mkdir($ruta_base, 0777, true);
}


// URL base de imágenes 
$base_url_imagenes = rtrim(neiva_app_url('Arbimaps/imagenes'), '/') . '/';

// Encabezado institucional
$header = "
<table style='border: 1px solid #000; width: 100%; font-family: Arial, sans-serif;' cellpadding='4' cellspacing='0'>
    <tr>
        <td style='width: 12%; text-align: center; vertical-align: middle;'>
            <img src='{$base_url_imagenes}alcaldia_neiva.png' alt='Logo Alcaldía' style='width: 55px; height: auto;'>
        </td>
        <td style='width: 23%; text-align: left; font-size: 9px; line-height: 1.2; vertical-align: middle;'>
            <strong>ALCALDÍA MUNICIPAL</strong><br>
            NEIVA - HUILA<br>
            NIT:891.900.406-2
        </td>
        <td style='width: 25%; text-align: center; vertical-align: middle;'>
            <span style='font-weight: bold; font-size: 11px;'>COMUNICACION</span>
        </td>
        <td style='width: 23%; text-align: right; font-size: 9px; line-height: 1.3; vertical-align: middle;'>
            <strong>CÓDIGO:GA-FR-10</strong><br>
            <strong>VERSIÓN: 01</strong><br>
            <strong>VIGENCIA: 2025</strong>
        </td>
        <td style='width: 12%; text-align: center; vertical-align: middle;'>
            <img src='{$base_url_imagenes}alcaldia_neiva.png' alt='Logo Alcaldía' style='width: 55px; height: auto;'>
        </td>
    </tr>
</table>
";

$css = '<style>
@page { 
    size: A4 portrait;
    margin: 1.0cm 1.5cm 1.5cm 1.5cm;
}
body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 11pt;
    line-height: 1.25;
    margin: 0;
    padding: 0;
}
table { border-collapse: collapse; width: 100%; }
td { padding: 4px; vertical-align: middle; }
img { max-width: 55px; height: auto; display: block; margin: 0 auto; }
.contenido {
    text-align: justify;
    font-size: 11pt;
    margin-top: 8px;
    padding: 12px;
    page-break-inside: avoid;
}
b, strong { font-weight: bold; }
br { display: block; content: ""; margin: 0.3em 0; }

/* Firma */
.firma-wrap { text-align: center; margin-top: 80px; margin-bottom: 40px; padding-bottom: 20px; }
.firma-line { display: inline-block; width: 55%; border-bottom: 1px solid #000; height: 1px; vertical-align: middle; }
.firma-nombre { text-align: center !important; display: block; margin: 15px auto; line-height: 1.4; }
.contenido p { margin: 0 0 8px 0; }
.firma-wrap, .firma-nombre { page-break-inside: avoid; }
</style>';

// Refuerzo de reglas de firma
$css = str_replace('</style>', "
.firma-wrap { text-align: center; margin-top: 90px; margin-bottom: 2px; }
.firma-line { display: inline-block; width: 55%; border-bottom: 1px solid #000; height: 1px; vertical-align: middle; }
.firma-nombre { text-align: center; margin-top: 2px; font-weight: bold; }
</style>", $css);

$cont_documento = trim($cont_documento);

//Unificar <br> redundantes para mejor estructuracion
$cont_documento_processed = preg_replace('/(<br\\s*\\/?>\\s*){2,}/i', '<br>', $cont_documento);

$cont_documento_processed = str_replace(["\r\n", "\r"], "\n", $cont_documento_processed);
$cont_documento_processed = preg_replace('/\n{2,}/', '<br><br><br><br>', $cont_documento_processed);
$cont_documento_processed = preg_replace('/\n/', '<br><br><br>', $cont_documento_processed);
$cont_documento_processed = preg_replace('/(<br\\s*\\/?>\\s*){5,}/i', '<br><br><br><br>', $cont_documento_processed);

$cont_documento_processed = preg_replace(
    '/_{6,}(?:\\s|&nbsp;)*(?:<br\\s*\\/?>)*/i',
    '<div class="firma-wrap"><span class="firma-line"></span></div>',
    $cont_documento_processed
);
$cont_documento_processed = preg_replace(
    '/^[\\s]*_{6,}[\\s]*$/m',
    '<div class="firma-wrap"><span class="firma-line"></span></div>',
    $cont_documento_processed
);

$firma_pos = strpos($cont_documento_processed, '<div class="firma-wrap">');
if ($firma_pos !== false) {
    $pre_firma  = substr($cont_documento_processed, 0, $firma_pos);
    $post_firma = substr($cont_documento_processed, $firma_pos);
    $post_firma = preg_replace(
        '/<div class="firma-wrap">.*?<\\/div>\\s*(.+)$/s',
        '<div class="firma-wrap"><span class="firma-line"></span></div><div class="firma-nombre">$1</div>',
        $post_firma
    );
    $cont_documento_processed = $pre_firma . $post_firma;
}

$valores_clave = array_filter([
    trim((string)$cod_tramite),
    trim((string)$nombre_comp_interesado),
    trim((string)$fecha_rad_tramite),
    trim((string)$tipo_mutacion_tramite),
    trim((string)$tipo_proceso_muta),
    trim((string)$fecha_resp_tramite),
]);

$envolver_negrilla = function (string $html, string $texto): string {
    if ($texto === '') return $html;

    if (stripos($html, '<b>' . $texto . '</b>') !== false ||
        stripos($html, '<strong>' . $texto . '</strong>') !== false) {
        return $html;
    }

    $patron = '/' . preg_quote($texto, '/') . '/u';
    return preg_replace($patron, '<b>$0</b>', $html, 1);
};

// Poner en negrilla los valores críticos 
foreach ($valores_clave as $valor) {
    $cont_documento_processed = $envolver_negrilla($cont_documento_processed, $valor);
}

$cont_documento_processed = preg_replace('/\bAsunto:\b/u', '<b>Asunto:</b>', $cont_documento_processed, 1);

$html_final = $css . $header . '<div class="contenido">' . $cont_documento_processed . '</div>';

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml(mb_convert_encoding($html_final, 'HTML-ENTITIES', 'UTF-8'));
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$nombre_pdf = "oficio_{$cod_tramite}.pdf";
$ruta_pdf   = "$ruta_base/$nombre_pdf";
file_put_contents($ruta_pdf, $dompdf->output());

$documento_blob = file_get_contents($ruta_pdf);

// --- Insertar en la base de datos  ---
$sql = "INSERT INTO procede_tramite (
    cod_radicacion_tramite,
    fecha_rad_tramite,
    tipo_mutacion_tramite,
    actividad_tramite,
    fecha_resp_tramite,
    actividad_a_realizar,
    nombre_comp_interesado,
    telefono_interesado,
    correo_interesado,
    cod_catastro,
    fmi_predio,
    direccion_predio,
    propietarios_predio,
    tipo_oficio,
    cont_documento,
    documento_generado
) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param(
    "sssssssssssssss",
    $cod_tramite,
    $tipo_mutacion_tramite,
    $tipo_proceso_muta,
    $fecha_resp_tramite,
    $actividad_a_realizar,
    $nombre_comp_interesado,
    $telefono_interesado,
    $correo_interesado,
    $cod_catastro,
    $fmi_predio,
    $direccion_predio,
    $propietarios_predio,
    $tipo_oficio,
    $cont_documento,
    $documento_blob
);

// --- Ejecutar ---
try {
    $stmt->execute();
    echo json_encode([
        'success'   => true,
        'message'   => 'Documento guardado correctamente.',
        'ruta_pdf'  => $ruta_pdf,
        'id_procede' => $stmt->insert_id
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error'   => 'Error al guardar en la base de datos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
