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

// POST 
$cod_tramite = $_POST['cod_tramite'] ?? '';
$tipo_mutacion_tramite = $_POST['tipo_mutacion_tramite'] ?? '';
$actividad_tramite = $_POST['tipo_proceso_muta'] ?? '';
$fecha_resp_tramite = $_POST['fecha_respuesta_tramite'] ?? null;
$actividad_a_realizar = $_POST['actividad_tramite'] ?? '';
$nombre_comp_interesado = $_POST['nombre_interesado'] ?? '';
$telefono_interesado = $_POST['telefono_interesado'] ?? '';
$correo_interesado = $_POST['correo_interesado'] ?? '';
$cod_catastro = $_POST['cod_catastro'] ?? '';
$fmi_predio = $_POST['fmi_predio'] ?? '';
$direccion_predio = $_POST['direccion_predio'] ?? '';
$propietarios_predio = $_POST['propietarios_predio'] ?? '';
$tipo_oficio = $_POST['tipo_oficio'] ?? '';
$cont_documento = $_POST['cont_documento'] ?? '';
$observacion = $_POST['observacion'] ?? '';

// Limpieza de contenido
$cont_documento = preg_replace('/(<br\s*\/?>\s*){2,}/', '<br><br>', $cont_documento);

// --- Carpetas ---
$anio = substr($cod_tramite, 4, 4);
$ruta_base = realpath(__DIR__ . '/../tramites_conservacion');
if ($ruta_base === false) {
    $ruta_base = __DIR__ . '/../tramites_conservacion';
}
$ruta_base .= "/no_procede_completar/$anio/$cod_tramite";

if (!file_exists($ruta_base)) {
    mkdir($ruta_base, 0777, true);
}

$ruta_documentos = $ruta_base . '/documentos';
if (!file_exists($ruta_documentos)) {
    mkdir($ruta_documentos, 0777, true);
}

$ruta_firmados = $ruta_base . '/no_procede_completar_firmados';
if (!file_exists($ruta_firmados)) {
    mkdir($ruta_firmados, 0777, true);
}

// --- PDF CONFIGURACIÓN ---
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);

$css = '<style>
    @page { margin: 1cm; size: A4 portrait; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; line-height: 1.4; margin: 0; padding: 0; }
    table { border-collapse: collapse; width: 100%; border: 1px solid #000; }
    table.sin-bordes { border: none !important; }
    table.sin-bordes td { border: none !important; }
    td { border: 1px solid #000; padding: 4px; vertical-align: middle; }
    img { max-width: 55px; height: auto; }
    .center { text-align: center; }
    .justify { text-align: justify; }
    .bold { font-weight: bold; }
    .firma-section { text-align: center; margin-top: 40px; width: 100%; }
    .firma-container { display: block; width: 300px; margin: 0 auto; text-align: center; }
    .firma-linea { border-top: 1px solid #000; margin-bottom: 5px; width: 100%; }
    .firma-nombre { font-weight: bold; margin-top: 0; }
    .firma-cargo { font-weight: bold; margin-top: 0; }
    .firma-institucion { font-size: 11px; margin-top: 3px; }
</style>';

$html = $css . $cont_documento;

$dompdf = new Dompdf($options);
$dompdf->loadHtml(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$nombre_pdf = "no_procede_completar_{$cod_tramite}.pdf";
$ruta_pdf = $ruta_base . '/' . $nombre_pdf;
file_put_contents($ruta_pdf, $dompdf->output());

$documento_generado_blob = file_get_contents($ruta_pdf);

// VERIFICAR ORIGEN DE LOS DATOS
$source_table = 'tramite_radicacion';
$sql_check = "SELECT cod_tramite FROM tramites_por_completar WHERE cod_tramite = ?";
$stmt_check = $mysqli->prepare($sql_check);
$stmt_check->bind_param("s", $cod_tramite);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Ya existe en tramites_por_completar, ese es el origen
    $source_table = 'tramites_por_completar';
}
$stmt_check->close();

// Fetch documentos según tabla origen
$sql_fetch = "SELECT
    sol_escrita_tramite,
    cop_escritura_tramite,
    ctl_tramite,
    doc_identidad_tramite,
    carta_autorizacion_tramite,
    otros_doc_tramite
FROM {$source_table} WHERE cod_tramite = ?";

$stmt_fetch = $mysqli->prepare($sql_fetch);
$stmt_fetch->bind_param("s", $cod_tramite);
$stmt_fetch->execute();
$result_fetch = $stmt_fetch->get_result();
$original_docs = $result_fetch->fetch_assoc();
$stmt_fetch->close();

$document_fields = [];
foreach ($original_docs as $campo => $blob) {
    if (!empty($blob)) {
        $nombre_archivo = $campo . ".pdf";
        $ruta_archivo = $ruta_documentos . '/' . $nombre_archivo;
        file_put_contents($ruta_archivo, $blob);
        $document_fields[$campo] = $blob; 
    } else {
        $document_fields[$campo] = '';
    }
}

// === LOGS PARA DEBUG ===
$log_file = $ruta_base . '/debug_log.txt';
$log = "=== INICIO PROCESO: " . date('Y-m-d H:i:s') . " ===\n";
$log .= "Código Trámite: $cod_tramite\n";
$log .= "Source Table: $source_table\n\n";

// === TRANSACCIÓN ===
$mysqli->begin_transaction();

try {
    // 1. INSERT en no_procede_completar
    $sql = "INSERT INTO no_procede_completar (
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
        documento_generado,
        documento_recibido,
        sol_escrita_tramite,
        cop_escritura_tramite,
        ctl_tramite,
        doc_identidad_tramite,
        carta_autorizacion_tramite,
        otros_doc_tramite,
        observacion
    ) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '', ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param(
        "ssssssssssssssssssssss",
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
        $documento_generado_blob,
        $document_fields['sol_escrita_tramite'],
        $document_fields['cop_escritura_tramite'],
        $document_fields['ctl_tramite'],
        $document_fields['doc_identidad_tramite'],
        $document_fields['carta_autorizacion_tramite'],
        $document_fields['otros_doc_tramite'],
        $observacion
    );
    $stmt->send_long_data(14, $documento_generado_blob);
    $stmt->execute();
    $id_completar = $stmt->insert_id;
    $stmt->close();
    
    $log .= "INSERT en no_procede_completar - ID: $id_completar\n\n";

    // 2. VERIFICAR SI YA EXISTE EN tramites_por_completar Y ELIMINARLO
    $sql_check_tpc = "SELECT id_procedes FROM tramites_por_completar WHERE cod_tramite = ?";
    $stmt_tpc_check = $mysqli->prepare($sql_check_tpc);
    $stmt_tpc_check->bind_param("s", $cod_tramite);
    $stmt_tpc_check->execute();
    $result_tpc_check = $stmt_tpc_check->get_result();
    $existing_tpc = $result_tpc_check->fetch_assoc();
    $stmt_tpc_check->close();
    
    $log .= "Verificando tramites_por_completar...\n";
    $log .= "¿Ya existe? " . ($existing_tpc ? "SÍ (id_procedes anterior: {$existing_tpc['id_procedes']})" : "NO") . "\n\n";

    if ($existing_tpc) {
        $log .= "ELIMINANDO registro anterior de tramites_por_completar...\n";
        
        // Eliminar el registro anterior
        $stmt_delete_tpc = $mysqli->prepare("DELETE FROM tramites_por_completar WHERE cod_tramite = ?");
        $stmt_delete_tpc->bind_param("s", $cod_tramite);
        $stmt_delete_tpc->execute();
        $deleted_rows = $stmt_delete_tpc->affected_rows;
        $stmt_delete_tpc->close();
        
        $log .= "Registro eliminado - Filas afectadas: $deleted_rows\n\n";
    }

    $log .= "INSERTANDO nuevo registro en tramites_por_completar...\n";
    
    if ($source_table === 'tramite_radicacion') {
        $sql_insert_tpc = "
        INSERT INTO tramites_por_completar (
            id_procedes,
            cod_tramite,
            fmi_predio_tram,
            npn_predio_tram,
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
            observacion_tramite,
            sol_escrita_tramite,
            cop_escritura_tramite,
            ctl_tramite,
            doc_identidad_tramite,
            carta_autorizacion_tramite,
            otros_doc_tramite
        )
        SELECT 
            ?, r.cod_tramite, p.fmi_predio_tram, p.npn_predio_tram, p.nombre_propietario_tram,
            p.tipo_doc_propietario_tram, p.cedula_propietario_tram, p.valor_avaluo_terreno_tram,
            p.direccion_predio_terreno_tram, p.destino_econ_predio_tram, p.area_terr_predio_tram,
            p.area_cons_predio_tram, NOW(), r.documento_interesado, r.num_doc_interesado,
            r.primer_nombre_interesado, r.segundo_nombre_interesado, r.primer_apellido_interesado,
            r.segundo_apellido_interesado, r.telefono_interesado, r.correo_interesado,
            r.mutacion_tramite, r.fecha_limite_respuesta, r.tsolicitante_tramite, r.fmi_predio,
            r.npn_predio, r.observacion_tramite, ?, ?, ?, ?, ?, ?
        FROM tramite_radicacion AS r
        JOIN tramite_info_predio AS p ON r.cod_tramite = p.info_cod_tramite
        WHERE r.cod_tramite = ?";

        $stmt_tpc = $mysqli->prepare($sql_insert_tpc);
        
        if (!$stmt_tpc) {
            $log .= "ERROR preparando INSERT: " . $mysqli->error . "\n";
            throw new Exception("Error preparando INSERT: " . $mysqli->error);
        }
        
        $stmt_tpc->bind_param(
            "isssssss",
            $id_completar,
            $document_fields['sol_escrita_tramite'],
            $document_fields['cop_escritura_tramite'],
            $document_fields['ctl_tramite'],
            $document_fields['doc_identidad_tramite'],
            $document_fields['carta_autorizacion_tramite'],
            $document_fields['otros_doc_tramite'],
            $cod_tramite
        );
        
        $stmt_tpc->execute();
        $rows_tpc = $stmt_tpc->affected_rows;
        
        if ($stmt_tpc->error) {
            $log .= "ERROR ejecutando INSERT: " . $stmt_tpc->error . "\n";
            throw new Exception("Error ejecutando INSERT: " . $stmt_tpc->error);
        }
        
        $stmt_tpc->close();
        
        $log .= "INSERT completado - Filas insertadas: $rows_tpc\n\n";

        if ($rows_tpc === 0) {
            $log .= "No hubo datos en tramite_radicacion/tramite_info_predio. Insertando respaldo directo en tramites_por_completar...\n";

            $sql_insert_tpc_fallback = "
                INSERT INTO tramites_por_completar (
                    id_procedes,
                    cod_tramite,
                    fmi_predio_tram,
                    npn_predio_tram,
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
                    observacion_tramite,
                    sol_escrita_tramite,
                    cop_escritura_tramite,
                    ctl_tramite,
                    doc_identidad_tramite,
                    carta_autorizacion_tramite,
                    otros_doc_tramite
                ) VALUES (
                    ?, ?, ?, ?, ?, '', '', 0, ?, '', 0, 0, NOW(), '', 0, ?, '', '', '', ?, ?, ?, ?, '',
                    ?, ?, ?, ?, ?, ?, ?, ?, ?
                )";

            $stmt_tpc_fallback = $mysqli->prepare($sql_insert_tpc_fallback);
            if (!$stmt_tpc_fallback) {
                $log .= "ERROR preparando fallback INSERT: " . $mysqli->error . "\n";
                throw new Exception("Error preparando fallback INSERT: " . $mysqli->error);
            }

            $nombre_propietario_fallback = $propietarios_predio !== '' ? $propietarios_predio : $nombre_comp_interesado;

            $stmt_tpc_fallback->bind_param(
                "isssssssssssssssssss",
                $id_completar,
                $cod_tramite,
                $fmi_predio,
                $cod_catastro,
                $nombre_propietario_fallback,
                $direccion_predio,
                $nombre_comp_interesado,
                $telefono_interesado,
                $correo_interesado,
                $tipo_mutacion_tramite,
                $fecha_resp_tramite,
                $fmi_predio,
                $cod_catastro,
                $observacion,
                $document_fields['sol_escrita_tramite'],
                $document_fields['cop_escritura_tramite'],
                $document_fields['ctl_tramite'],
                $document_fields['doc_identidad_tramite'],
                $document_fields['carta_autorizacion_tramite'],
                $document_fields['otros_doc_tramite']
            );

            $stmt_tpc_fallback->execute();
            $rows_tpc = $stmt_tpc_fallback->affected_rows;
            $stmt_tpc_fallback->close();

            $log .= "Fallback INSERT completado - Filas insertadas: $rows_tpc\n\n";
        }
    } else {
        $rows_tpc = 0;
        $log .= "El trámite no proviene de tramite_radicacion, se omite INSERT\n\n";
    }

    // 3. LIMPIAR REGISTROS RELACIONADOS
    $tables_to_clean = [
        'historial_asignacion' => 'historial_cod_tramite',
        'asignacion_tramite' => 'asignacion_cod_tramite',
        'estados_tramite' => 'cod_tramite',
        'tramite_info_predio' => 'info_cod_tramite'
    ];

    foreach ($tables_to_clean as $table => $column) {
        $stmt_del = $mysqli->prepare("DELETE FROM {$table} WHERE {$column} = ?");
        $stmt_del->bind_param("s", $cod_tramite);
        $stmt_del->execute();
        $stmt_del->close();
    }

    // 4. DELETE del registro original SOLO SI VIENE DE tramite_radicacion
    if ($source_table === 'tramite_radicacion') {
        $stmt_delete = $mysqli->prepare("DELETE FROM tramite_radicacion WHERE cod_tramite = ?");
        $stmt_delete->bind_param("s", $cod_tramite);
        $stmt_delete->execute();
        $stmt_delete->close();
    }

    $mysqli->commit();
    
    $log .= "\n=== PROCESO COMPLETADO EXITOSAMENTE ===\n";
    $log .= "Fecha/Hora: " . date('Y-m-d H:i:s') . "\n";
    file_put_contents($log_file, $log, FILE_APPEND);

    $response = [
        'success' => true,
        'message' => 'Documento guardado correctamente',
        'ruta_pdf' => $ruta_pdf,
        'id_completar' => $id_completar,
        'source_table' => $source_table,
        'tramites_por_completar_modificados' => $rows_tpc,
        'existing_record' => $existing_tpc ? true : false,
        'log_file' => $ruta_base . '/debug_log.txt',
        'redirect' => neiva_app_url('Arbimaps/index.php?page=seguimiento/mis_asignaciones')
    ];

} catch (Exception $e) {
    $mysqli->rollback();
    $log .= "\n ERROR: " . $e->getMessage() . "\n";
    $log .= "Trace: " . $e->getTraceAsString() . "\n";
    file_put_contents($log_file, $log, FILE_APPEND);
    
    $response = [
        'success' => false,
        'error' => $e->getMessage(),
        'log_file' => $ruta_base . '/debug_log.txt'
    ];
}

ob_end_clean();
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
