<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('GET', true);
neiva_require_permission('menu.tramites', $PERMISOS, true);
ob_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

$mutacion_tramite = $_GET['mutacion_tramite'] ?? '';
$tipo_proceso_muta = $_GET['tipo_proceso_muta'] ?? '';
$fecha_respuesta_tramite = $_GET['fecha_respuesta_tramite'] ?? '';

$cod = $_GET['cod_tramite'] ?? '';
$response = ['success' => false];

if (!$cod) {
    echo json_encode(['success' => false, 'error' => 'Código de trámite no especificado.']);
    exit;
}

// Consulta principal (agregando campos para documento y num_doc_interesado si no están)
$sql = "SELECT 
            r.cod_tramite,
            r.fecha_rad,
            r.mutacion_tramite,
            r.primer_nombre_interesado,
            r.segundo_nombre_interesado,
            r.primer_apellido_interesado,
            r.segundo_apellido_interesado,
            r.telefono_interesado,
            r.correo_interesado,
            r.fmi_predio,
            r.documento_interesado,  -- Agregado si existe
            r.num_doc_interesado,    -- Agregado si existe
            r.fmi_predio_tram,       -- Agregado si existe
            i.npn_predio_tram,
            i.fmi_predio_tram,
            i.direccion_predio_terreno_tram,
            i.nombre_propietario_tram,
            i.tipo_doc_propietario_tram,
            i.cedula_propietario_tram
        FROM tramite_radicacion r
        LEFT JOIN tramite_info_predio i ON i.info_cod_tramite = r.cod_tramite
        WHERE r.cod_tramite = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $cod);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $nombre_interesado = trim(
        $row['primer_nombre_interesado'] . ' ' .
            $row['segundo_nombre_interesado'] . ' ' .
            $row['primer_apellido_interesado'] . ' ' .
            $row['segundo_apellido_interesado']
    );

    $propietarios = $row['nombre_propietario_tram'] . ' - ' .
        $row['tipo_doc_propietario_tram'] . ' ' .
        $row['cedula_propietario_tram'];

    $num_doc_interesado = $row['num_doc_interesado'] ?? $row['cedula_propietario_tram']; // Fallback si no existe
    $tipo_doc_interesado = $row['documento_interesado'] ?? $row['tipo_doc_propietario_tram']; // Fallback

    $nombre_funcionario = ($_SESSION['nombre_usuario'] ?? '') . ' ' . ($_SESSION['apellido_usuario'] ?? '');
    $cargo_funcionario = $_SESSION['rol_usuario'] ?? '';

    $fecha = new DateTime();
    $dia = $fecha->format('d');
    $meses = [
        1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    $mes = $meses[(int)$fecha->format('n')];
    $anio = $fecha->format('Y');

    // Generar número de resolución (ejemplo simple: año + secuencial, ajusta según tu DB)
    $numero = '001'; // TODO: Implementar consulta para obtener siguiente número, e.g., SELECT MAX(numero) + 1 FROM no_procede_completar WHERE anio = $anio

    $base_url_imagenes = rtrim(neiva_app_url('Arbimaps/imagenes'), '/') . '/';

    // Header ajustado para coincidir con la imagen
    $css = '<style>
    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 11pt;
        line-height: 1.4;
        margin: 0;
        padding: 0;
    }
    table { 
        border-collapse: collapse; 
        width: 100%; 
        border: 1px solid #000;
    }
    td { 
        border: 1px solid #000; 
        padding: 4px; 
        vertical-align: middle; 
    }
    img { 
        max-width: 55px; 
        height: auto; 
    }
    .center { text-align: center; }
    .justify { text-align: justify; }
    .bold { font-weight: bold; }
</style>';

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

    // Body adaptado para "No Procede Completar", con estructura exacta de la imagen
    $date_str = "{$dia} de {$mes} de {$anio}";
    $body = "
    <div style='text-align: center; font-size: 12px; margin-top: 20px; font-weight: bold;'>
        No. {$numero}
    </div>
 
    <div style='text-align: center; font-size: 12px; margin-top: 20px; font-weight: bold;'>
            EL DIRECTOR LOCAL ADMINISTRATIVO DE CATASTRO
    </div>
    <br>
    <div style='text-align: justify; font-size: 12px;'>
        En uso de sus facultades legales, en especial las conferidas por el artículo 5° de la Ley 14 de 1983, artículo 6° del Decreto 3496 de 1983, artículo 10 de la Resolución IGAC.
    </div>
    <div style='text-align: center; font-size: 12px; font-weight: bold; margin-top: 15px;'>
        CONSIDERANDO
    </div>
    <br>
 
    <div style='text-align: justify; font-size: 12px;'>
        <b>Artículo Primero.</b> - El proceso solicitado para la mutación por <b>{$tipo_proceso_muta}</b> del predio con NPN <b>{$row['npn_predio_tram']}</b> y matrícula inmobiliaria <b>{$row['fmi_predio_tram']}</b>, no se encuentra completo en cuanto a la documentación requerida y por lo tanto se comunica que <b>NO PROCEDE COMPLETAR</b> el trámite en este momento.<br><br>
        <b>Artículo Segundo.</b> - Notifíquese al interesado a través del correo <b>{$row['correo_interesado']}</b> y/o teléfono <b>{$row['telefono_interesado']}</b> para que realice los ajustes necesarios y pueda reenviar su solicitud cuando lo considere pertinente.<br><br>
 
        El interesado contará con un término de <b>treinta (30) días hábiles</b>, contados a partir de la fecha de notificación del presente acto, para realizar los ajustes o complementos requeridos y <b>retomar el proceso de mutación catastral</b>. Transcurrido dicho plazo sin que se allegue la documentación completa, se entenderá que <b>desiste del trámite</b>, conforme a lo previsto en el Código de Procedimiento Administrativo y de lo Contencioso Administrativo.
    </div>
 
    <br>
    <br>
    
    <table style='width: 100%; border: none; margin-top: 40px;'>
        <tr>
            <td style='border: none; text-align: center; width: 100%;'>
                <div style='width: 300px; margin: 0 auto;'>
                    <div style='border-top: 1px solid #000; margin-bottom: 5px;'></div>
                    <div style='font-weight: bold;'>{$nombre_funcionario}</div>
                    <div style='font-weight: bold;'>{$cargo_funcionario}</div>
                    <div style='font-size: 11px; margin-top: 3px;'>Dirección de Catastro Neiva</div>
                </div>
            </td>
        </tr>
    </table>
    
    <div style='text-align: left; font-size: 10px; margin-top: 20px;'>
        Proyecta: - <br>
        Revisó: -
    </div>
";

    $plantilla = $header . $body . $css;

    $response = [
        'success' => true,
        'tramite' => [
            'cod_tramite' => $row['cod_tramite'],
            'fecha_radicacion' => $row['fecha_rad'],
            'mutacion_tramite' => $mutacion_tramite ?: $row['mutacion_tramite'],
            'tipo_proceso_muta' => $tipo_proceso_muta,
            'fecha_respuesta_tramite' => $fecha_respuesta_tramite,
            'nombre_interesado' => $nombre_interesado,
            'correo_interesado' => $row['correo_interesado'],
            'telefono_interesado' => $row['telefono_interesado'],
            'direccion_predio' => $row['direccion_predio_terreno_tram'],
            'npn_predio_tram' => $row['npn_predio_tram'],
            'fmi_predio_tram' => $row['fmi_predio_tram'],
            'fmi_predio' => $row['fmi_predio'],
            'propietarios' => $propietarios,
            'nombre_funcionario' => $nombre_funcionario,
            'cargo_funcionario' => $cargo_funcionario,
            'plantilla' => $plantilla
        ]
    ];
}

ob_end_clean();
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
