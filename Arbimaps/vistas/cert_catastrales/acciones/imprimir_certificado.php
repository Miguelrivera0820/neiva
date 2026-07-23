<?php
session_start();
date_default_timezone_set('America/Bogota');
require '../../../../conexion.php';

// Forzar UTF-8 en la conexión para evitar problemas con acentos/ñ
if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->set_charset('utf8mb4');
}

// ===============================
// 1) Validar parámetro de entrada
// ===============================
if (!isset($_GET['codigo_certificado']) || $_GET['codigo_certificado'] === '') {
    die("ID no especificado");
}
$codigo_certificado = $_GET['codigo_certificado'];
$codigo_seguro = preg_replace('/[^A-Za-z0-9_-]/', '', $codigo_certificado);
$solicitud_firma = ($_GET['firmar'] ?? '') === '1';

if ($codigo_seguro === '' || $codigo_seguro !== $codigo_certificado) {
    die("El código del certificado no es válido");
}

// ===================================
// 2) Consultar certificado principal
// ===================================
$sql = "SELECT * FROM certificado_catastral WHERE codigo_certificado = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $codigo_certificado);
$stmt->execute();
$resultado = $stmt->get_result();
$certificado = $resultado->fetch_assoc();

if (!$certificado) {
    die("No se encontró el certificado");
}

$directorio_certificado = dirname(__DIR__, 4)
    . DIRECTORY_SEPARATOR . 'resoluciones'
    . DIRECTORY_SEPARATOR . $codigo_seguro;
$ruta_certificado = $directorio_certificado . DIRECTORY_SEPARATOR . 'certificado.pdf';
$ruta_marcador_firma = $directorio_certificado . DIRECTORY_SEPARATOR . 'firmado.flag';
$version_diseno_firma = 'firma_centrada_v2';
$certificado_ya_firmado = is_file($ruta_certificado) && is_file($ruta_marcador_firma);
$contenido_marcador_firma = $certificado_ya_firmado
    ? trim((string) file_get_contents($ruta_marcador_firma))
    : '';
$firma_con_diseno_actual = strpos($contenido_marcador_firma, $version_diseno_firma) === 0;

// Actualiza una sola vez los certificados firmados con el diseño anterior.
if ($certificado_ya_firmado && !$firma_con_diseno_actual) {
    $solicitud_firma = true;
}

// Si ya fue firmado, no se vuelve a generar ni a reemplazar mediante una URL directa.
if ($solicitud_firma && $certificado_ya_firmado && $firma_con_diseno_actual) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="certificado_' . $codigo_seguro . '.pdf"');
    header('Content-Length: ' . filesize($ruta_certificado));
    readfile($ruta_certificado);
    exit;
}

// Imprimir entrega el archivo que ya esté guardado (sin firma o reemplazado por el firmado).
if (!$solicitud_firma && is_file($ruta_certificado)) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="certificado_' . $codigo_seguro . '.pdf"');
    header('Content-Length: ' . filesize($ruta_certificado));
    readfile($ruta_certificado);
    exit;
}

// ===================================
// 3) Consultar propietarios relacionados
// ===================================
$sql_prop = "SELECT nombres_propietario, tipo_doc_propietario, cc_num_propietario 
             FROM certificado_propietarios 
             WHERE prop_cod_certificado = ?";
$stmt_prop = $mysqli->prepare($sql_prop);
$stmt_prop->bind_param("s", $codigo_certificado);
$stmt_prop->execute();
$res_prop = $stmt_prop->get_result();

$propietores = [];
while ($row = $res_prop->fetch_assoc()) {
    $propietores[] = [
        'nombre' => $row['nombres_propietario'],
        'tipo_doc' => $row['tipo_doc_propietario'],
        'cc' => $row['cc_num_propietario']
    ];
}

// ===================================
// 4) Preparar variables del certificado
// ===================================
$direccion = $certificado['cert_direccion_predio'] ?? '';
$npn_predio = $certificado['cert_npn_predio'] ?? '';
$fmi_predio = $certificado['cert_fmi_predio'] ?? '';

$area_terreno = isset($certificado['cert_area_terreno_predio']) && $certificado['cert_area_terreno_predio'] !== ''
    ? number_format((float)$certificado['cert_area_terreno_predio'], 2, ',', '.')
    : '';
$area_construida = isset($certificado['cert_area_construccion_predio']) && $certificado['cert_area_construccion_predio'] !== ''
    ? number_format((float)$certificado['cert_area_construccion_predio'], 2, ',', '.')
    : '';
$anio_vigencia = $certificado['cert_anio_vigencia'] ?? '';
$valor_avaluo = isset($certificado['cert_avaluo_terreno_tramite']) && $certificado['cert_avaluo_terreno_tramite'] !== ''
    ? number_format((float)$certificado['cert_avaluo_terreno_tramite'], 0, ',', '.')
    : '';

$solicitante_nombre = trim(
    ($certificado['cert_primer_nombre_interesado'] ?? '') . ' ' .
    ($certificado['cert_segundo_nombre_interesado'] ?? '') . ' ' .
    ($certificado['cert_primer_apellido_interesado'] ?? '') . ' ' .
    ($certificado['cert_segundo_apellido_interesado'] ?? '')
);
$solicitante_tipo_doc = $certificado['cert_tipo_documento'] ?? '';
$solicitante_num_doc = $certificado['cert_num_cc_interesado'] ?? '';

$documentos_mapeo = [
    'Cedula_Ciudadania' => 'Cédula de Ciudadanía',
    'Cedula_Extranjeria' => 'Cédula de Extranjería',
    'NIT' => 'N.I.T.',
    'Pasaporte' => 'Pasaporte'
];
$tipo_doc_legible = $documentos_mapeo[$solicitante_tipo_doc] ?? str_replace('_', ' ', $solicitante_tipo_doc);

// Fecha “Dado en Neiva…”
$meses_es = [
    1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
    5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
    9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
];
$fechaBase = !empty($certificado['certificado_hora_creacion'])
    ? new DateTime($certificado['certificado_hora_creacion'])
    : new DateTime();

$dia = (int)$fechaBase->format('d');
$mes = $meses_es[(int)$fechaBase->format('n')];
$anio = $fechaBase->format('Y');
$linea_fecha = "Dado en Neiva, a los {$dia} días del mes de {$mes} del año {$anio}";

// ===================================
// 5) Preparar firma del certificado
// ===================================
$imagen_firma_html = '';
if ($solicitud_firma) {
    $ruta_firma = __DIR__ . '/../firmas/firma.jpeg';
    if (!is_file($ruta_firma)) {
        die('No se encontró la imagen de firma configurada.');
    }

    $firma_base64 = base64_encode(file_get_contents($ruta_firma));
    $imagen_firma_html = "
        <div style='height:65px; text-align:center; line-height:65px;'>
            <img
                src='data:image/jpeg;base64,{$firma_base64}'
                alt='Firma'
                style='display:inline-block; width:145px; height:65px; vertical-align:bottom; object-fit:contain;'>
        </div>";
}

$bloque_firma = "
    <div style='margin-top:30px; text-align:center; font-size:12px;'>
      {$imagen_firma_html}
      <div style='width:260px; margin:0 auto; padding-top:4px; border-top:1px solid #000; text-align:center;'>
        Subdirector/a Administrativo/a
      </div>
    </div>";

// ===================================
// 6) Construir bloque de propietarios
// ===================================
$texto_propietarios = "";
if (!empty($propietores)) {
    foreach ($propietores as $p) {
        // Quitar ceros a la izquierda convirtiendo a número entero
        $cc_limpia = ltrim($p['cc'], '0'); 
        // Si quieres garantizar que siempre sea numérico:
        // $cc_limpia = (int)$p['cc'];

        $texto_propietarios .= "<p style='font-size:12px;'>
           <b>Propietario:</b> {$p['nombre']} <br>
           <b>{$p['tipo_doc']}:</b> {$cc_limpia}</p>";
    }
} else {
    $texto_propietarios = "<p style='font-size:12px;'>No registra propietarios</p>";
}


// ===================================
// 6) CSS con encabezado y fondo
// ===================================
$css = "
<style>
  @page {
    margin: 1.5cm 2cm 2.5cm 2cm; /* top, right, bottom, left */
  }
  body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 12px;
    color:#111;
  }
  h3, h4 { margin: 5px 0; }
  .center { text-align: center; }
  .nota { font-size: 10px; text-align: justify; margin-top: 20px; }
  .firma { text-align:center; margin-top: 60px; }
  table { border-collapse: collapse; width:100%; }
  th, td { font-size: 12px; padding: 5px; border: 1px solid #000; }
  .encabezado td { border: none; }
  .fondo {
    position: fixed;
    top: 200px;
    left: 50%;
    width: 400px;
    opacity: 0.1;
    transform: translateX(-50%);
    z-index: -1;
  }
</style>

<style>
  body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 12px;
    color:#111;
  }
  h3, h4 { margin: 5px 0; }
  .center { text-align: center; }
  .nota { font-size: 10px; text-align: justify; margin-top: 20px; }
  .firma { text-align:center; margin-top: 60px; }
  table { border-collapse: collapse; width:100%; }
  th, td { font-size: 12px; padding: 5px; border: 1px solid #000; }
  .encabezado td { border: none; }
  .fondo {
    position: fixed;
    top: 200px;
    left: 50%;
    width: 400px;
    opacity: 0.1;
    transform: translateX(-50%);
    z-index: -1;
  }
</style>
";

// ===================================
// 7) Encabezado con logos + fondo
// ===================================
$encabezado = "
<style>
  .encabezado {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 10px;
  }

  .encabezado td {
    border: 1px solid #000;
    font-size: 12px;
    padding: 4px;
    vertical-align: middle;
    text-align: center;
  }

  /* Logo Alcaldía */
  .logo {
    width: 20%;
    height: 60px;
    background: url('http://192.168.0.57/cons_neiva/certificados/logos_certificado/alcaldia_neiva.png') no-repeat center center;
    background-size: contain;
  }

  /* Título central */
  .titulo {
    width: 42%;
    font-weight: bold;
    font-size: 25px;
  }

  /* Info */
  .info {
    width: 20%;
    font-size: 7px;
    text-align: left;
  }

  /* Logo MIPG */
  .mipg {
    width: 19%;
    height: 40px;
    background: url('http://192.168.0.57/cons_neiva/certificados/logos_certificado/mpg.png') no-repeat center center;
    background-size: contain;
  }

  /* Marca de agua fondo */
  .marca-agua {
    position: fixed;
    top: 50%;
    left: 50%;
    width: 750px;
    height: 750px;
    background: url('http://192.168.0.57/cons_neiva/certificados/logos_certificado/LOGO_NEIVA.png') no-repeat center center;
    background-size: contain;
    opacity: 0.08;
    transform: translate(-50%, -50%);
    z-index: -1;
  }
</style>

<!-- Encabezado -->
<table class='encabezado'>
  <tr>
    <td class='logo'></td>
    <td class='titulo'>CERTIFICADOS CATASTRALES</td>
      <table style='width:auto; border-collapse: collapse; text-align:left; font-size: 35px;'>
        <tr>
          <td style='text-align:left;'><b>FOR-GDAPC-09</b></td>
        </tr>
        <tr>
          <td style='text-align:left;'><b>Versión:</b> 01</td>
        </tr>
        <tr>
          <td style='text-align:left;'><b>Vigente desde:</b><br>
          Febrero 20 del 2025</td>
        </tr>
      </table>
    <td class='mipg'></td>
  </tr>
</table>

<!-- Marca de agua -->
<div class='marca-agua'></div>
";

// ===================================
// 8) Contenido del certificado
// ===================================
if (empty($npn_predio) || $npn_predio === '') {
    $cuerpo = "
    <div style='text-align:center; margin-top:20px;'>
      <h3 style='margin:0; font-size:14px; letter-spacing: 1px;'>LA SUBDIRECCIÓN DE GESTIÓN CATASTRAL</h3>
      <h4 style='margin:5px 0 25px 0; font-size:13px;'>CERTIFICA</h4>
    </div>

    <p style='text-align:justify; font-size:12px; line-height: 1.8; margin-bottom: 25px;'>
      Que consultada la base catastral que reposa en esta entidad, se constató que el/la ciudadano/a 
      <strong>" . htmlspecialchars($solicitante_nombre, ENT_QUOTES, 'UTF-8') . "</strong>, 
      identificado/a con <strong>" . htmlspecialchars($tipo_doc_legible, ENT_QUOTES, 'UTF-8') . "</strong> 
      número <strong>" . htmlspecialchars($solicitante_num_doc, ENT_QUOTES, 'UTF-8') . "</strong>, 
      <span style='font-size: 13px; font-weight: bold;'>NO POSEE</span> bienes inmuebles (predios) registrados a su nombre en el Municipio de Neiva.
    </p>

    <p style='font-size:12px; margin-bottom: 30px;'><b>Posee Bienes:</b> NO</p>

    <p style='margin-top:10px; font-size:12px;'>
      La presente se expide a solicitud del interesado.
    </p>

    <p style='font-size:12px;'>$linea_fecha</p><br><br><br>

    $bloque_firma

    <div style='margin-top:40px; font-size:8px; text-align:justify; border-top: 1px solid #ddd; padding-top: 10px;'>
      <b>NOTA:</b><br>
      Válido por 90 días a partir de la fecha de expedición.<br><br>
      Conforme con el Artículo 2.2.2.2.8. del Decreto No. 148 de 2020, Inscripción o incorporación catastral. 
      La información catastral resultado de los procesos de formación, actualización o conservación se inscribirá 
      o incorporará en la base catastral con la del acto administrativo que lo ordena.<br><br>
      Parágrafo. La inscripción en el catastro no constituye título de dominio, ni sanea los vicios de propiedad o la tradición 
      y no puede alegarse como excepción contra quien pretenda tener mejor derecho a la propiedad o posesión del predio.<br><br>
      Cualquier inquietud escribir al correo electrónico: 
      <b>gestion.catastral@alcaldianeiva.gov.co</b>
    </div>
    ";
} else {
    $cuerpo = "
    <div style='text-align:center; margin-top:10px;'>
      <h3 style='margin:0; font-size:14px;'>LA SUBDIRECCIÓN DE GESTIÓN CATASTRAL</h3>
      <h4 style='margin:5px 0 15px 0; font-size:13px;'>CERTIFICA</h4>
    </div>

    <p style='text-align:justify; font-size:12px;'>
      Que consultada la base catastral que reposa en la entidad, se encontró la siguiente información:
    </p>

    <p style='font-size:12px;'><b>Posee Bienes:</b> SI</p><br>

    $texto_propietarios

    <p style='font-size:12px;'><b>Dirección:</b> $direccion</p>
    <p style='font-size:12px;'><b>Número Predial Catastral:</b> $npn_predio</p>
    <p style='font-size:12px;'><b>Matrícula Inmobiliaria:</b> $fmi_predio</p>
    <p style='font-size:12px;'>
      <b>Área de Terreno:</b> {$area_terreno} m² &nbsp;&nbsp; 
      <b>Hectáreas:</b> ______ &nbsp;&nbsp; 
      <b>Área Construida:</b> {$area_construida} m²
    </p>

    <table style='width:60%; margin:15px auto; border:1px solid #000; border-collapse:collapse; font-size:12px; text-align:center;'>
      <tr>
        <th style='border:1px solid #000; padding:5px;'>VIGENCIA</th>
        <th style='border:1px solid #000; padding:5px;'>AVALÚO</th>
      </tr>
      <tr>
        <td style='border:1px solid #000; padding:5px;'>$anio_vigencia</td>
        <td style='border:1px solid #000; padding:5px;'>$$valor_avaluo</td>
      </tr>
    </table>

    <p style='margin-top:10px; font-size:12px;'>
      La presente se expide a solicitud del interesado.
    </p>

    <p style='font-size:12px;'>$linea_fecha</p><br><br><br>

    $bloque_firma

    <p style='margin-top:20px; font-size:12px;'>Género: ___________________</p>

    <div style='margin-top:20px; font-size:8px; text-align:justify;'>
      <b>NOTA:</b><br>
      Válido por 90 días a partir de la fecha de expedición.<br><br>
      Conforme con el Artículo 2.2.2.2.8. del Decreto No. 148 de 2020, Inscripción o incorporación catastral. 
      La información catastral resultado de los procesos de formación, actualización o conservación se inscribirá 
      o incorporará en la base catastral con la del acto administrativo que lo ordena.<br><br>
      Parágrafo. La inscripción en el catastro no constituye título de dominio, ni sanea los vicios de propiedad o la tradición 
      y no puede alegarse como excepción contra quien pretenda tener mejor derecho a la propiedad o posesión del predio.<br><br>
      Cualquier inquietud escribir al correo electrónico: 
      <b>gestion.catastral@alcaldianeiva.gov.co</b>
    </div>
    ";
}

// ===================================
// 9) Unir todo y generar PDF
// ===================================
$html = $css . $encabezado . $cuerpo;

require '../../../../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

if (!is_dir($directorio_certificado)
    && !mkdir($directorio_certificado, 0777, true)
    && !is_dir($directorio_certificado)
) {
    die('No fue posible crear la carpeta del certificado.');
}

$pdf_generado = $dompdf->output();
if (file_put_contents($ruta_certificado, $pdf_generado, LOCK_EX) === false) {
    $mensaje_error = $solicitud_firma
        ? 'No fue posible reemplazar el certificado por la versión firmada.'
        : 'No fue posible guardar el certificado.';
    die($mensaje_error);
}

if ($solicitud_firma
    && file_put_contents(
        $ruta_marcador_firma,
        $version_diseno_firma . '|' . date('Y-m-d H:i:s'),
        LOCK_EX
    ) === false
) {
    die('El certificado fue firmado, pero no fue posible registrar el estado de la firma.');
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="certificado_' . $codigo_seguro . '.pdf"');
header('Content-Length: ' . strlen($pdf_generado));
echo $pdf_generado;
exit;
