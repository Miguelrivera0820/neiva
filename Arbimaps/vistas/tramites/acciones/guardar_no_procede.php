<?php
ob_start();
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

// --- POST ---
$cod_tramite             = $_POST['cod_tramite'] ?? '';
$tipo_mutacion_tramite   = $_POST['tipo_mutacion_tramite'] ?? '';
$actividad_tramite       = $_POST['tipo_proceso_muta'] ?? '';
$fecha_resp_tramite      = $_POST['fecha_respuesta_tramite'] ?? null;
$actividad_a_realizar    = $_POST['actividad_tramite'] ?? '';
$nombre_comp_interesado  = $_POST['nombre_interesado'] ?? '';
$telefono_interesado     = $_POST['telefono_interesado'] ?? '';
$correo_interesado       = $_POST['correo_interesado'] ?? '';
$cod_catastro            = $_POST['cod_catastro'] ?? '';
$fmi_predio              = $_POST['fmi_predio'] ?? '';
$direccion_predio        = $_POST['direccion_predio'] ?? '';
$propietarios_predio     = $_POST['propietarios_predio'] ?? '';
$tipo_oficio             = $_POST['tipo_oficio'] ?? '';
$cont_documento          = $_POST['cont_documento'] ?? '';

// Limpieza de contenido (opcional, pero útil para normalizar breaks)
$cont_documento = preg_replace('/(<br\s*\/?>\s*){2,}/', '<br><br>', $cont_documento);

// --- Carpetas ---
$anio = substr($cod_tramite, 4, 4);
$ruta_base = "../../tramites_conservacion/$anio/$cod_tramite/no_procede";
if (!file_exists($ruta_base)) mkdir($ruta_base, 0777, true);

$ruta_documentos = $ruta_base . '/documentos';
if (!file_exists($ruta_documentos)) mkdir($ruta_documentos, 0777, true);

// Ruta base original
$original_base = "../tramites_conservacion/$anio/$cod_tramite/";

// --- PDF CONFIGURACIÓN ---
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);

// --- USAR EL CONTENIDO EDITADO PARA EL HTML DEL PDF ---
// Wrap en <html><head><body> para mejor compatibilidad con Dompdf (el contenido ya incluye <style> y markup)
$html = '
<html>
<head>
    <!-- El $cont_documento ya incluye el <style> original, así que no lo duplicamos -->
</head>
<body>
    ' . $cont_documento . '
</body>
</html>';

// --- DOMPDF ---
$dompdf = new Dompdf($options);
$dompdf->loadHtml(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$nombre_pdf = "rechazado_{$cod_tramite}.pdf";
$ruta_pdf   = $ruta_base . '/' . $nombre_pdf;
file_put_contents($ruta_pdf, $dompdf->output());

// --- BLOB ---
$documento_generado_blob = file_get_contents($ruta_pdf);

// --- Copiar documentos originales (BLOBs) ---
$sql_fetch = "SELECT 
    sol_escrita_tramite,
    cop_escritura_tramite,
    ctl_tramite,
    doc_identidad_tramite,
    carta_autorizacion_tramite,
    otros_doc_tramite
FROM tramite_radicacion 
WHERE cod_tramite = ?";
$stmt_fetch = $mysqli->prepare($sql_fetch);
$stmt_fetch->bind_param("s", $cod_tramite);
$stmt_fetch->execute();
$result_fetch = $stmt_fetch->get_result();
$original_docs = $result_fetch->fetch_assoc();
$stmt_fetch->close();

// --- Guardar físicamente los archivos ---
$document_paths = [];
foreach ($original_docs as $campo => $blob) {
    if (!empty($blob)) {
        $nombre_archivo = $campo . ".pdf";
        $ruta_archivo = $ruta_documentos . '/' . $nombre_archivo;
        file_put_contents($ruta_archivo, $blob);
        $document_paths[$campo] = $blob;
    } else {
        $document_paths[$campo] = '';
    }
}

// --- TRANSACCIÓN ---
$mysqli->begin_transaction();

try {
    // INSERT rechazado_tramite
    $sql_insert = "INSERT INTO rechazado_tramite (
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
        sol_escrita_tramite,
        cop_escritura_tramite,
        ctl_tramite,
        doc_identidad_tramite,
        carta_autorizacion_tramite,
        otros_doc_tramite,
        documento_generado
    ) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt_insert = $mysqli->prepare($sql_insert);
    $stmt_insert->bind_param(
        "ssssssssssssssssssssb",
        $cod_tramite,
        $tipo_mutacion_tramite,
        $actividad_tramite,
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
        $document_paths['sol_escrita_tramite'],
        $document_paths['cop_escritura_tramite'],
        $document_paths['ctl_tramite'],
        $document_paths['doc_identidad_tramite'],
        $document_paths['carta_autorizacion_tramite'],
        $document_paths['otros_doc_tramite'],
        $documento_generado_blob
    );
    $stmt_insert->send_long_data(20, $documento_generado_blob);
    $stmt_insert->execute();
    $stmt_insert->close();

    // --- Copiar datos al registro de rechazados ---
    $sql_rech = "
        INSERT INTO rechazados (
            cod_tramite,
            fmi_predio_tram,
            nombre_propietario_tram,
            tipo_doc_propietario_tram,
            cedula_propietario_tram,
            valor_avaluo_terreno_tram,
            direccion_predio_terreno_tram,
            destino_econ_predio_tram,
            area_terr_predio_tram,
            area_cons_predio_tram,
            fecha_rad,
            documento_interesado,
            num_doc_interesado,
            primer_nombre_interesado,
            segundo_nombre_interesado,
            primer_apellido_interesado,
            segundo_apellido_interesado,
            telefono_interesado,
            correo_interesado,
            mutacion_tramite,
            fecha_limite_respuesta,
            tsolicitante_tramite,
            fmi_predio,
            npn_predio,
            observacion_tramite
        )
        SELECT
            r.cod_tramite,                
            p.fmi_predio_tram,
            p.nombre_propietario_tram,
            p.tipo_doc_propietario_tram,
            p.cedula_propietario_tram,
            p.valor_avaluo_terreno_tram,
            p.direccion_predio_terreno_tram,
            p.destino_econ_predio_tram,
            p.area_terr_predio_tram,
            p.area_cons_predio_tram,
            r.fecha_rad,
            r.documento_interesado,
            r.num_doc_interesado,
            r.primer_nombre_interesado,
            r.segundo_nombre_interesado,
            r.primer_apellido_interesado,
            r.segundo_apellido_interesado,
            r.telefono_interesado,
            r.correo_interesado,
            r.mutacion_tramite,
            r.fecha_limite_respuesta,
            r.tsolicitante_tramite,
            r.fmi_predio,
            r.npn_predio,
            r.observacion_tramite
        FROM tramite_radicacion AS r
        JOIN tramite_info_predio AS p
          ON r.cod_tramite = p.info_cod_tramite
        WHERE r.cod_tramite = ?";
    
    $stmt_rech = $mysqli->prepare($sql_rech);
    $stmt_rech->bind_param("s", $cod_tramite);
    $stmt_rech->execute();
    $stmt_rech->close();

    // --- Eliminar originales ---
    $stmt_del_historial = $mysqli->prepare("DELETE FROM historial_asignacion WHERE historial_cod_tramite = ?");
    $stmt_del_historial->bind_param("s", $cod_tramite);
    $stmt_del_historial->execute();
    $stmt_del_historial->close();

    $stmt_del_asignacion = $mysqli->prepare("DELETE FROM asignacion_tramite WHERE asignacion_cod_tramite = ?");
    $stmt_del_asignacion->bind_param("s", $cod_tramite);
    $stmt_del_asignacion->execute();
    $stmt_del_asignacion->close();

    $stmt_del_estados = $mysqli->prepare("DELETE FROM estados_tramite WHERE cod_tramite = ?");
    $stmt_del_estados->bind_param("s", $cod_tramite);
    $stmt_del_estados->execute();
    $stmt_del_estados->close();

    $stmt_del_predio = $mysqli->prepare("DELETE FROM tramite_info_predio WHERE info_cod_tramite = ?");
    $stmt_del_predio->bind_param("s", $cod_tramite);
    $stmt_del_predio->execute();
    $stmt_del_predio->close();

    $stmt_del_tramite = $mysqli->prepare("DELETE FROM tramite_radicacion WHERE cod_tramite = ?");
    $stmt_del_tramite->bind_param("s", $cod_tramite);
    $stmt_del_tramite->execute();
    $stmt_del_tramite->close();

    $mysqli->commit();

    $response = [
        'success' => true,
        'message' => 'Documentos copiados, PDF generado y trámite movido correctamente.',
        'ruta_pdf' => $ruta_pdf
    ];
} catch (Exception $e) {
    $mysqli->rollback();
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

?>
