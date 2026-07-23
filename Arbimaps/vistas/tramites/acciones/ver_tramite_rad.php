<?php
$cod_tramite = $_GET['cod'] ?? '';
$info_cod_tramite = $cod_tramite;

// Consulta principal del trámite
$sql = "SELECT * FROM tramite_radicacion WHERE cod_tramite = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $cod_tramite);
$stmt->execute();
$resultado = $stmt->get_result();
$tramite = $resultado->fetch_assoc();

$tipo_tramite = strtoupper(trim($tramite['tipo_tramite'] ?? ''));
if (!in_array($tipo_tramite, ['ACTUALIZACION', 'CONSERVACION'], true)) {
    $tipo_tramite = !empty($tramite['subtipo_conservacion'])
        ? 'CONSERVACION'
        : 'ACTUALIZACION';
}

$mostrar_valor_tramite = static function ($valor) {
    return htmlspecialchars(str_replace('_', ' ', trim((string) $valor)));
};

// Consulta de información del predio
$sql2 = "SELECT * FROM tramite_info_predio WHERE info_cod_tramite = ?";
$stmt2 = $mysqli->prepare($sql2);
$stmt2->bind_param("s", $info_cod_tramite);
$stmt2->execute();
$resultado2 = $stmt2->get_result();
$propietarios = $resultado2->fetch_all(MYSQLI_ASSOC);

// ==============================================
// CONSULTA UNIFICADA DE TRAZABILIDAD / HISTORIAL
// ==============================================
$historial = [];

// 1. Radicación inicial (de tramite_radicacion)
if (!empty($tramite)) {
    $historial[] = [
        'fecha' => $tramite['fecha_rad'],
        'actor' => trim(($tramite['nombre_usuario'] ?? '') . ' ' . ($tramite['apellido_usuario'] ?? '')),
        'rol_actor' => str_replace('_', ' ', $tramite['rol_usuario'] ?? 'ventanilla_catastral'),
        'destinatario' => '',
        'rol_destinatario' => '',
        'accion' => 'Radicación Inicial',
        'observacion' => $tramite['observacion_tramite'] ?? '',
        'tipo' => 'radicacion',
        'badge_class' => 'bg-success'
    ];
}

// 2. Historial de asignaciones
$sql_asig = "SELECT ha.*, 
                    d.nombre_doc1 AS doc1, d.nombre_doc2 AS doc2, d.nombre_doc3 AS doc3, d.nombre_doc4 AS doc4, d.nombre_doc5 AS doc5,
                    d.tipo_doc1, d.tipo_doc2, d.tipo_doc3, d.tipo_doc4, d.tipo_doc5
             FROM historial_asignacion ha
             LEFT JOIN documentos_tram_asignacion d 
                    ON d.cod_tramite = ha.historial_cod_tramite 
                   AND d.doc_cedula_usuario = ha.creacion_tram_cc_usuario
             WHERE ha.historial_cod_tramite = ? 
             ORDER BY ha.historial_fecha_tramite ASC";
if ($stmt_asig = $mysqli->prepare($sql_asig)) {
    $stmt_asig->bind_param("s", $cod_tramite);
    $stmt_asig->execute();
    $res_asig = $stmt_asig->get_result();
    while ($row = $res_asig->fetch_assoc()) {
        $fecha = !empty($row['fecha_creacion']) ? $row['fecha_creacion'] : $row['historial_fecha_tramite'];
        $historial[] = [
            'fecha' => $fecha,
            'actor' => trim(($row['creacion_tram_nombre_usuario'] ?? '') . ' ' . ($row['creacion_tram_apellido_usuario'] ?? '')),
            'rol_actor' => str_replace('_', ' ', $row['creacion_tram_rol_usuario'] ?? ''),
            'destinatario' => trim(($row['historial_nombre_usuario'] ?? '') . ' ' . ($row['historial_apellido_usuario'] ?? '')),
            'rol_destinatario' => str_replace('_', ' ', $row['historial_rol_usuario'] ?? ''),
            'accion' => 'Asignación de Trámite',
            'observacion' => $row['observacion_a_usuario_tramite'] ?? '',
            'tipo' => 'asignacion',
            'badge_class' => 'bg-primary',
            'doc1' => $row['doc1'] ?? null,
            'doc2' => $row['doc2'] ?? null,
            'doc3' => $row['doc3'] ?? null,
            'doc4' => $row['doc4'] ?? null,
            'doc5' => $row['doc5'] ?? null,
            'tipo_doc1' => $row['tipo_doc1'] ?? null,
            'tipo_doc2' => $row['tipo_doc2'] ?? null,
            'tipo_doc3' => $row['tipo_doc3'] ?? null,
            'tipo_doc4' => $row['tipo_doc4'] ?? null,
            'tipo_doc5' => $row['tipo_doc5'] ?? null
        ];
    }
    $stmt_asig->close();
}

// 3. Historial de revisiones
$sql_rev = "SELECT hr.*, 
                   d.doc1, d.doc2, d.doc3, d.doc4, d.doc5,
                   d.tipo_doc1, d.tipo_doc2, d.tipo_doc3, d.tipo_doc4, d.tipo_doc5
            FROM historial_revision hr
            LEFT JOIN doc_entrega_asignacion d 
                   ON d.asignacion_id_tramite = hr.entrega_id_tramite
                  AND d.cod_tramite = hr.historial_cod_tramite
            WHERE hr.historial_cod_tramite = ? 
            ORDER BY hr.historial_fecha_tramite ASC";
if ($stmt_rev = $mysqli->prepare($sql_rev)) {
    $stmt_rev->bind_param("s", $cod_tramite);
    $stmt_rev->execute();
    $res_rev = $stmt_rev->get_result();
    while ($row = $res_rev->fetch_assoc()) {
        $fecha = !empty($row['fecha_creacion']) ? $row['fecha_creacion'] : $row['historial_fecha_tramite'];
        $historial[] = [
            'fecha' => $fecha,
            'actor' => trim(($row['creacion_tram_nombre_usuario'] ?? '') . ' ' . ($row['creacion_tram_apellido_usuario'] ?? '')),
            'rol_actor' => str_replace('_', ' ', $row['creacion_tram_rol_usuario'] ?? ''),
            'destinatario' => trim(($row['asignacion_nombre_usuario'] ?? '') . ' ' . ($row['asignacion_apellido_usuario'] ?? '')),
            'rol_destinatario' => str_replace('_', ' ', $row['asignacion_rol_usuario'] ?? ''),
            'accion' => 'Revisión / Envío de Trámite',
            'observacion' => $row['observacion_a_usuario_tramite'] ?? '',
            'tipo' => 'revision',
            'badge_class' => 'bg-info',
            'doc1' => $row['doc1'] ?? null,
            'doc2' => $row['doc2'] ?? null,
            'doc3' => $row['doc3'] ?? null,
            'doc4' => $row['doc4'] ?? null,
            'doc5' => $row['doc5'] ?? null,
            'tipo_doc1' => $row['tipo_doc1'] ?? null,
            'tipo_doc2' => $row['tipo_doc2'] ?? null,
            'tipo_doc3' => $row['tipo_doc3'] ?? null,
            'tipo_doc4' => $row['tipo_doc4'] ?? null,
            'tipo_doc5' => $row['tipo_doc5'] ?? null
        ];
    }
    $stmt_rev->close();
}

// 4. Historial de devoluciones
$sql_dev = "SELECT hd.*, 
                   d.doc1, d.doc2, d.doc3, d.doc4, d.doc5,
                   d.tipo_doc1, d.tipo_doc2, d.tipo_doc3, d.tipo_doc4, d.tipo_doc5
            FROM historial_devolucion hd
            LEFT JOIN doc_entrega_asignacion d 
                   ON d.asignacion_id_tramite = hd.entrega_id_tramite
                  AND d.cod_tramite = hd.historial_cod_tramite
            WHERE hd.historial_cod_tramite = ? 
            ORDER BY hd.historial_fecha_tramite ASC";
if ($stmt_dev = $mysqli->prepare($sql_dev)) {
    $stmt_dev->bind_param("s", $cod_tramite);
    $stmt_dev->execute();
    $res_dev = $stmt_dev->get_result();
    while ($row = $res_dev->fetch_assoc()) {
        $fecha = !empty($row['fecha_creacion']) ? $row['fecha_creacion'] : $row['historial_fecha_tramite'];
        $historial[] = [
            'fecha' => $fecha,
            'actor' => trim(($row['nombre_sesion'] ?? '') . ' ' . ($row['apellido_sesion'] ?? '')),
            'rol_actor' => str_replace('_', ' ', $row['rol_actual'] ?? ''),
            'destinatario' => trim(($row['devolucion_tram_nombre_usuario'] ?? '') . ' ' . ($row['devolucion_tram_apellido_usuario'] ?? '')),
            'rol_destinatario' => str_replace('_', ' ', $row['devolucion_tram_rol_usuario'] ?? ''),
            'accion' => 'Devolución de Trámite',
            'observacion' => $row['observacion_a_usuario_tramite'] ?? '',
            'tipo' => 'devolucion',
            'badge_class' => 'bg-danger',
            'doc1' => $row['doc1'] ?? null,
            'doc2' => $row['doc2'] ?? null,
            'doc3' => $row['doc3'] ?? null,
            'doc4' => $row['doc4'] ?? null,
            'doc5' => $row['doc5'] ?? null,
            'tipo_doc1' => $row['tipo_doc1'] ?? null,
            'tipo_doc2' => $row['tipo_doc2'] ?? null,
            'tipo_doc3' => $row['tipo_doc3'] ?? null,
            'tipo_doc4' => $row['tipo_doc4'] ?? null,
            'tipo_doc5' => $row['tipo_doc5'] ?? null
        ];
    }
    $stmt_dev->close();
}

// 5. Resoluciones finales: Procede
$sql_procede = "SELECT * FROM procede_tramite WHERE cod_radicacion_tramite = ?";
if ($stmt_procede = $mysqli->prepare($sql_procede)) {
    $stmt_procede->bind_param("s", $cod_tramite);
    $stmt_procede->execute();
    $res_procede = $stmt_procede->get_result();
    while ($row = $res_procede->fetch_assoc()) {
        $historial[] = [
            'fecha' => $row['fecha_rad_tramite'],
            'actor' => 'Coordinación Técnica / Consolidación',
            'rol_actor' => 'validador',
            'destinatario' => '',
            'rol_destinatario' => '',
            'accion' => 'Trámite PROCEDE (Aprobación Final)',
            'observacion' => $row['actividad_a_realizar'] ?? '',
            'tipo' => 'resolucion_procede',
            'badge_class' => 'bg-success'
        ];
    }
    $stmt_procede->close();
}

// 6. Resoluciones finales: No procede completar
$sql_noproc = "SELECT * FROM no_procede_completar WHERE cod_radicacion_tramite = ?";
if ($stmt_noproc = $mysqli->prepare($sql_noproc)) {
    $stmt_noproc->bind_param("s", $cod_tramite);
    $stmt_noproc->execute();
    $res_noproc = $stmt_noproc->get_result();
    while ($row = $res_noproc->fetch_assoc()) {
        $historial[] = [
            'fecha' => $row['fecha_rad_tramite'],
            'actor' => 'Coordinación Técnica / Consolidación',
            'rol_actor' => 'validador',
            'destinatario' => '',
            'rol_destinatario' => '',
            'accion' => 'Trámite NO PROCEDE (Por Completar / Devuelto)',
            'observacion' => $row['observacion'] ?? '',
            'tipo' => 'resolucion_noprocede',
            'badge_class' => 'bg-dark'
        ];
    }
    $stmt_noproc->close();
}

// 7. Asignaciones activas actuales
$sql_act_asig = "SELECT ha.*, 
                        d.nombre_doc1 AS doc1, d.nombre_doc2 AS doc2, d.nombre_doc3 AS doc3, d.nombre_doc4 AS doc4, d.nombre_doc5 AS doc5,
                        d.tipo_doc1, d.tipo_doc2, d.tipo_doc3, d.tipo_doc4, d.tipo_doc5
                 FROM asignacion_tramite ha
                 LEFT JOIN documentos_tram_asignacion d 
                        ON d.cod_tramite = ha.asignacion_cod_tramite 
                       AND d.doc_cedula_usuario = ha.creacion_tram_cc_usuario
                 WHERE ha.asignacion_cod_tramite = ?";
if ($stmt_act_asig = $mysqli->prepare($sql_act_asig)) {
    $stmt_act_asig->bind_param("s", $cod_tramite);
    $stmt_act_asig->execute();
    $res_act_asig = $stmt_act_asig->get_result();
    while ($row = $res_act_asig->fetch_assoc()) {
        $historial[] = [
            'fecha' => $row['asignacion_fecha_tramite'],
            'actor' => trim(($row['creacion_tram_nombre_usuario'] ?? '') . ' ' . ($row['creacion_tram_apellido_usuario'] ?? '')),
            'rol_actor' => str_replace('_', ' ', $row['creacion_tram_rol_usuario'] ?? ''),
            'destinatario' => trim(($row['asignacion_nombre_usuario'] ?? '') . ' ' . ($row['asignacion_apellido_usuario'] ?? '')),
            'rol_destinatario' => str_replace('_', ' ', $row['asignacion_rol_usuario'] ?? ''),
            'accion' => 'Asignación Activa (Pendiente)',
            'observacion' => $row['observacion_a_usuario_tramite'] ?? '',
            'tipo' => 'asignacion_activa',
            'badge_class' => 'bg-warning text-dark',
            'doc1' => $row['doc1'] ?? null,
            'doc2' => $row['doc2'] ?? null,
            'doc3' => $row['doc3'] ?? null,
            'doc4' => $row['doc4'] ?? null,
            'doc5' => $row['doc5'] ?? null,
            'tipo_doc1' => $row['tipo_doc1'] ?? null,
            'tipo_doc2' => $row['tipo_doc2'] ?? null,
            'tipo_doc3' => $row['tipo_doc3'] ?? null,
            'tipo_doc4' => $row['tipo_doc4'] ?? null,
            'tipo_doc5' => $row['tipo_doc5'] ?? null
        ];
    }
    $stmt_act_asig->close();
}

// 8. Entregas activas actuales
$sql_act_ent = "SELECT ea.*, 
                       d.doc1, d.doc2, d.doc3, d.doc4, d.doc5,
                       d.tipo_doc1, d.tipo_doc2, d.tipo_doc3, d.tipo_doc4, d.tipo_doc5
                FROM entrega_asignacion ea
                LEFT JOIN doc_entrega_asignacion d 
                       ON d.asignacion_id_tramite = ea.id_entrega_asignacion
                      AND d.cod_tramite = ea.entrega_cod_tramite
                WHERE ea.entrega_cod_tramite = ?";
if ($stmt_act_ent = $mysqli->prepare($sql_act_ent)) {
    $stmt_act_ent->bind_param("s", $cod_tramite);
    $stmt_act_ent->execute();
    $res_act_ent = $stmt_act_ent->get_result();
    while ($row = $res_act_ent->fetch_assoc()) {
        $fecha = !empty($row['fecha_creacion']) ? $row['fecha_creacion'] : $row['historial_fecha_tramite'];
        $historial[] = [
            'fecha' => $fecha,
            'actor' => trim(($row['quien_entrego_nombre'] ?? '') . ' ' . ($row['quien_entrego_apellido'] ?? '')),
            'rol_actor' => str_replace('_', ' ', $row['quien_entrego_rol'] ?? ''),
            'destinatario' => trim(($row['entrega_nombre_usuario'] ?? '') . ' ' . ($row['entrega_apellido_usuario'] ?? '')),
            'rol_destinatario' => str_replace('_', ' ', $row['entrega_rol_usuario'] ?? ''),
            'accion' => 'Entrega Activa (Pendiente de Validación)',
            'observacion' => $row['observacion_a_usuario_tramite'] ?? '',
            'tipo' => 'entrega_activa',
            'badge_class' => 'bg-warning text-dark',
            'doc1' => $row['doc1'] ?? null,
            'doc2' => $row['doc2'] ?? null,
            'doc3' => $row['doc3'] ?? null,
            'doc4' => $row['doc4'] ?? null,
            'doc5' => $row['doc5'] ?? null,
            'tipo_doc1' => $row['tipo_doc1'] ?? null,
            'tipo_doc2' => $row['tipo_doc2'] ?? null,
            'tipo_doc3' => $row['tipo_doc3'] ?? null,
            'tipo_doc4' => $row['tipo_doc4'] ?? null,
            'tipo_doc5' => $row['tipo_doc5'] ?? null
        ];
    }
    $stmt_act_ent->close();
}

// Ordenar cronológicamente por fecha ascendente
usort($historial, static function ($a, $b) {
    return strtotime($a['fecha']) <=> strtotime($b['fecha']);
});

// ==============================================
// DEFINIR RUTA DE DOCUMENTOS SEGÚN TIPO DE TRÁMITE
// ==============================================

$cod_base = preg_replace('/-\d{2}$/', '', $cod_tramite);
$fmi = trim($tramite['fmi_predio'] ?? '');
$rutas_candidatas = [];
$anio_fecha_rad = '';

if (!empty($tramite['fecha_rad'])) {
    $timestamp_fecha_rad = strtotime($tramite['fecha_rad']);
    if ($timestamp_fecha_rad !== false) {
        $anio_fecha_rad = date('Y', $timestamp_fecha_rad);
    }
}

if (preg_match('/^(?:CAT|ARB)[-_]?(20\d{2})/i', $cod_tramite, $m)) {
    $anio = $m[1];
    $rutas_candidatas[] = "tramites_conservacion/$anio/$cod_tramite/";
    if ($cod_base !== $cod_tramite) {
        $rutas_candidatas[] = "tramites_conservacion/$anio/$cod_base/";
    }
} elseif (preg_match('/^(20\d{2})/', $cod_tramite, $m)) {
    $rutas_candidatas[] = "tramites_conservacion/{$m[1]}/$cod_tramite/";
}

if ($anio_fecha_rad !== '') {
    $rutas_candidatas[] = "tramites_conservacion/$anio_fecha_rad/$cod_tramite/";
}

$rutas_candidatas[] = 'tramites_conservacion/' . date('Y') . "/$cod_tramite/";
if ($fmi !== '') {
    $rutas_candidatas[] = "tramites_conservacion/documentos_radicados/$fmi/";
}
$rutas_candidatas = array_values(array_unique($rutas_candidatas));

$nombres_documentos = array_filter([
    $tramite['sol_escrita_tramite'] ?? '',
    $tramite['cop_escritura_tramite'] ?? '',
    $tramite['ctl_tramite'] ?? '',
    $tramite['doc_identidad_tramite'] ?? '',
    $tramite['carta_autorizacion_tramite'] ?? '',
    $tramite['otros_doc_tramite'] ?? '',
]);
$base_aplicacion = dirname(__DIR__, 3);
$base_tramites = dirname(__DIR__);
$bases_documentos = [
    ['fs' => $base_aplicacion, 'web' => ''],
    ['fs' => $base_tramites, 'web' => 'vistas/tramites/'],
];
$ruta_pdf = $rutas_candidatas[0] ?? '';
$ruta_pdf_web = $ruta_pdf;

if ($nombres_documentos) {
    foreach ($rutas_candidatas as $ruta_candidata) {
        foreach ($nombres_documentos as $nombre_documento) {
            foreach ($bases_documentos as $base_documento) {
                $archivo_candidato = $base_documento['fs'] . '/' . $ruta_candidata . basename($nombre_documento);
                if (is_file($archivo_candidato)) {
                    $ruta_pdf = $ruta_candidata;
                    $ruta_pdf_web = $base_documento['web'] . $ruta_candidata;
                    break 3;
                }
            }
        }
    }
}

$url_documento = static function ($nombre) use ($ruta_pdf_web) {
    return htmlspecialchars($ruta_pdf_web . rawurlencode(basename((string) $nombre)), ENT_QUOTES, 'UTF-8');
};
$existe_documento = static function ($nombre) use ($bases_documentos, $ruta_pdf) {
    foreach ($bases_documentos as $base_documento) {
        if (is_file($base_documento['fs'] . '/' . $ruta_pdf . basename((string) $nombre))) {
            return true;
        }
    }
    return false;
};

?>

<style>
    .ver-tramite-page {
        background-color: #EDEDED !important;
        color: #0A2C1B;
        min-height: 100%;
    }

    .ver-tramite-page .ver-tramite-heading {
        gap: 1rem;
    }

    .ver-tramite-page .ver-tramite-title {
        color: #0A2C1B;
        font-weight: 800;
        letter-spacing: 0;
    }

    .ver-tramite-page .ver-tramite-subtitle {
        color: #7F8E85;
    }

    .ver-tramite-page .ver-tramite-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
        justify-content: flex-end;
    }

    .ver-tramite-page .ver-tramite-action-btn,
    .ver-tramite-page .bot_mostrar_vista,
    .ver-tramite-page .bot_verenotrapesta {
        align-items: center;
        border: 1px solid #0A2C1B !important;
        border-radius: 14px !important;
        display: inline-flex;
        font-weight: 700;
        justify-content: center;
        min-height: 38px;
        transition: transform 0.22s ease, box-shadow 0.22s ease, background-color 0.22s ease;
    }

    .ver-tramite-page .ver-tramite-action-primary,
    .ver-tramite-page .bot_mostrar_vista {
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%) !important;
        color: #ffffff !important;
    }

    /* .ver-tramite-page .ver-tramite-action-secondary,
    .ver-tramite-page .bot_verenotrapesta {
        background-color: #AEE136 !important;
        color: #0A2C1B !important;
    } */

    .ver-tramite-page .ver-tramite-action-btn:hover,
    .ver-tramite-page .bot_mostrar_vista:hover,
    .ver-tramite-page .bot_verenotrapesta:hover {
        box-shadow: 0 12px 24px rgba(10, 44, 27, 0.16);
        text-decoration: none;
        transform: translateY(-1px);
    }

    .bot_verenotrapesta {
        color: #0A2C1B !important;
    }

    .bot_verenotrapesta:hover {
        background-color: #A0C882 !important;
        color: #0A2C1B !important;
        border: 1px solid #A0C882 !important;
    }

    .ver-tramite-page>.row>.col-xl-12>.card {
        background-color: #ffffff;
        border: 1px solid rgba(192, 210, 200, 0.78);
        border-radius: 20px !important;
        box-shadow: 0 14px 30px rgba(10, 44, 27, 0.08) !important;
        overflow: hidden;
    }

    .ver-tramite-page .card-header {
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%) !important;
        border: 0 !important;
        border-radius: 14px !important;
        box-shadow: 0 10px 20px rgba(10, 44, 27, 0.12);
        margin-left: 1rem !important;
        margin-right: 1rem !important;
    }

    .ver-tramite-page .card-header h5 {
        font-size: 0.95rem;
        font-weight: 800;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .ver-tramite-page .card-body {
        padding: 1.35rem 1.25rem;
    }

    .ver-tramite-page label {
        color: #0A2C1B;
        font-weight: 700 !important;
    }

    .ver-tramite-page .input-group {
        border-radius: 14px;
        box-shadow: none !important;
    }

    .ver-tramite-page .input-group-text {
        background-color: #F6F8F7;
        border: 1px solid #C0D2C8;
        border-right: 0;
        border-radius: 14px 0 0 14px;
        color: #0A2C1B;
        min-width: 44px;
        justify-content: center;
    }

    .ver-tramite-page .form-control,
    .ver-tramite-page .form-select {
        background-color: #ffffff !important;
        border: 1px solid #C0D2C8;
        border-radius: 0 14px 14px 0;
        color: #0A2C1B;
        font-size: 0.9rem !important;
        min-height: 42px;
    }

    .ver-tramite-page .form-control[readonly] {
        background-color: #F8FAF9 !important;
        color: #0A2C1B;
    }

    .ver-tramite-page hr {
        border-top: 1px solid #C0D2C8;
        margin: 2rem 0;
        opacity: 1;
    }

    .ver-tramite-page .iframe-animado iframe {
        border: 1px solid #C0D2C8 !important;
        border-radius: 16px;
        margin-top: 0.8rem;
    }

    #modalTrazabilidad .modal-content {
        border: 1px solid rgba(192, 210, 200, 0.78) !important;
        border-radius: 18px !important;
    }

    #modalTrazabilidad .modal-header {
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%) !important;
        border: 0 !important;
        border-top-left-radius: 18px !important;
        border-top-right-radius: 18px !important;
    }

    #modalTrazabilidad .modal-body,
    #modalTrazabilidad .modal-footer {
        background-color: #F6F8F7 !important;
    }

    #modalTrazabilidad .timeline-panel {
        border: 1px solid rgba(192, 210, 200, 0.78) !important;
        border-radius: 14px !important;
    }

    #modalTrazabilidad .timeline-panel h6 {
        color: #0A5F5E !important;
    }

    #modalTrazabilidad .modal-footer .btn {
        background-color: #0A2C1B !important;
        border-radius: 14px;
    }

    @media (max-width: 576px) {
        .ver-tramite-page .ver-tramite-heading {
            align-items: flex-start !important;
        }

        .ver-tramite-page .ver-tramite-actions,
        .ver-tramite-page .ver-tramite-action-btn {
            width: 100%;
        }
    }

    .boton_Gene_Reporte {
        display: flex;
        align-items: center;
        justify-content: center;
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%);
        border: none;
        font-size: 1rem;
        border-radius: 20px;
        text-decoration: none;
        color: #ffffff !important;
    }

    .boton_Gene_Reporte2 {
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(164deg, rgba(214, 221, 218, 0.83) 0%, rgba(255, 255, 255, 0.37) 15%, rgba(255, 255, 255, 1) 85%, rgba(214, 221, 218, 0.68) 100%) !important;
        border: 1px solid #0A2C1B !important;
        color: #0A2C1B !important;
        font-size: 1rem;
        border-radius: 20px;
        text-decoration: none;
    }


    .boton_Gene_Reporte,
    .boton_Gene_Reporte2,
    #btnAbrirCancelacion,
    #btnExportarSeleccionados {
        transition: transform 0.25s ease, color 0.25s ease;
    }

    .boton_Gene_Reporte,
    #btnAbrirCancelacion,
    #btnExportarSeleccionados i {
        display: inline-block;
        transition: transform 0.25s ease, color 0.25s ease;
    }

    .boton_Gene_Reporte2 i {
        display: inline-block;
        transition: transform 0.25s ease, color 0.25s ease;
    }

    .boton_Gene_Reporte:hover {
        color: #ffffff !important;
        transform: scale(1.01);
        font-weight: bold;
    }

    #btnAbrirCancelacion:hover,
    #btnExportarSeleccionados:hover {
        transform: scale(1.01);
        font-weight: bold;
    }

    .boton_Gene_Reporte2:hover {
        transform: scale(1.01);
        font-weight: bold;
    }
</style>

<div class="container-fluid rounded-4 p-3 ver-tramite-page">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between my-4 ver-tramite-heading">

        <div class="my-4 text-start">
            <h2 class="mb-0 fw-bold ver-tramite-title">TRAMITES DE RADICACION</h2>
            <small class="ver-tramite-subtitle">Consulta la informacion de los tramites, genera constancia o consulta su trazabilidad.</small>
        </div>

        <div class="ver-tramite-actions">
            <a href="../tramites/constancia_radicacion.php?cod=<?php echo urlencode($cod_tramite); ?>"
                target="_blank"
                class="boton_Gene_Reporte p-3 px-4 "
                style="background-color:#002F55;">
                <i class="bi bi-cloud-download me-1"></i> Generar Constancia
            </a>

            <button type="button"
                class="boton_Gene_Reporte2 p-3 px-4  ver-tramite-action-secondary"
                style="background-color:#E67E22; border-color:#E67E22;"
                data-bs-toggle="modal"
                data-bs-target="#modalTrazabilidad">
                <i class="bi bi-clock-history me-1"></i> Ver Trazabilidad
            </button>
        </div>

    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-12 px-3">
                <div class="card shadow-sm mb-4 p-3" style="border-radius: 22px !important;">

                    <div class=" p-3  text-center">
                        <h5 style="color: #0A2C1B; font-weight:800;" class="mb-0 ">INFORMACIÓN DEL TRÁMITE</h5>
                    </div>

                    <hr class="m-2 " style="color: #002f44 !important;">

                    <div class="card-body">
                        <div class="form-group">
                            <d class="form-row px-3 mb-3">

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="cod_tramite" class="form-label fw-bold" style="font-size:0.9em">Identificador radicación</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi-journal-text"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;"
                                            id="cod_tramite" name="cod_tramite"
                                            value="<?php echo htmlspecialchars($tramite['cod_tramite'] ?? ''); ?>" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="fecha_rad" class="form-label fw-bold" style="font-size:0.9em">Fecha y hora de radicación</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-calendar-date"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;"
                                            id="fecha_rad" name="fecha_rad"
                                            value="<?php echo htmlspecialchars($tramite['fecha_rad'] ?? ''); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                <label for="documento_interesado"><b>Tipo Documento Interesado</b></label>
                                <input class="form-control py-4" id="num_doc_interesado" name="num_doc_interesado" type="text"
                                    value="<?php echo htmlspecialchars($tramite['documento_interesado']); ?>" readonly>
                            </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="documento_interesado" class="form-label fw-bold" style="font-size:0.9em">Tipo de documento interesado</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi-person-badge"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="documento_interesado" name="documento_interesado"
                                            value="<?php echo htmlspecialchars($tramite['documento_interesado'] ?? ''); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                <label for="num_doc_interesado"><b>Número Documento de Identidad Interesado</b></label>
                                <input class="form-control py-4" id="num_doc_interesado" name="num_doc_interesado" type="number"
                                    value="<?php echo htmlspecialchars($tramite['num_doc_interesado']); ?>" readonly>
                            </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="num_doc_interesado" class="form-label fw-bold" style="font-size:0.9em">Número de documento de indentidad del interesado</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="num_doc_interesado" name="num_doc_interesado"
                                            value="<?php echo htmlspecialchars($tramite['num_doc_interesado'] ?? ''); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                <label for="primer_nombre_interesado"><b>Primer Nombre Interesado</b></label>
                                <input class="form-control py-4" id="primer_nombre_interesado" name="primer_nombre_interesado" type="text"
                                    value="<?php echo htmlspecialchars($tramite['primer_nombre_interesado']); ?>" readonly>
                            </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="primer_nombre_interesado" class="form-label fw-bold" style="font-size:0.9em">Primer nombre del interesado</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="primer_nombre_interesado" name="primer_nombre_interesado"
                                            value="<?php echo htmlspecialchars($tramite['primer_nombre_interesado'] ?? ''); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                <label for="segundo_nombre_interesado"><b>Segundo Nombre Interesado</b></label>
                                <input class="form-control py-4" id="segundo_nombre_interesado" name="segundo_nombre_interesado" type="text"
                                    value="<?php echo htmlspecialchars($tramite['segundo_nombre_interesado']); ?>" readonly>
                            </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="segundo_nombre_interesado" class="form-label fw-bold" style="font-size:0.9em">Segundo nombre del interesado</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="segundo_nombre_interesado" name="segundo_nombre_interesado"
                                            value="<?php echo htmlspecialchars($tramite['segundo_nombre_interesado'] ?? ''); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                <label for="primer_apellido_interesado"><b>Primer Apellido Interesado</b></label>
                                <input class="form-control py-4" id="primer_apellido_interesado" name="primer_apellido_interesado" type="text"
                                    value="<?php echo htmlspecialchars($tramite['primer_apellido_interesado']); ?>" readonly>
                            </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="primer_apellido_interesado" class="form-label fw-bold" style="font-size:0.9em">Primer apellido del interesado</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi-people-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="primer_apellido_interesado" name="primer_apellido_interesado"
                                            value="<?php echo htmlspecialchars($tramite['primer_apellido_interesado'] ?? ''); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                <label for="segundo_apellido_interesado"><b>Segundo Apellido Interesado</b></label>
                                <input class="form-control py-4" id="segundo_apellido_interesado" name="segundo_apellido_interesado" type="text"
                                    value="<?php echo htmlspecialchars($tramite['segundo_apellido_interesado']); ?>" readonly>
                            </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="segundo_apellido_interesado" class="form-label fw-bold" style="font-size:0.9em">Segundo apellido del interesado</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi-people"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="segundo_apellido_interesado" name="segundo_apellido_interesado"
                                            value="<?php echo htmlspecialchars($tramite['segundo_apellido_interesado'] ?? ''); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                <label for="telefono_interesado"><b>Número Telefónico Interesado</b></label>
                                <input class="form-control py-4" id="telefono_interesado" name="telefono_interesado" type="text"
                                    value="<?php echo htmlspecialchars($tramite['telefono_interesado']); ?>" readonly>
                            </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="telefono_interesado" class="form-label fw-bold" style="font-size:0.9em">Número telefónico del interesado</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-telephone-fill me-2"></i>+57</span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="telefono_interesado" name="telefono_interesado"
                                            value="<?php echo htmlspecialchars($tramite['telefono_interesado'] ?? ''); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                <label for="correo_interesado"><b>Correo Electrónico Interesado</b></label>
                                <input class="form-control py-4" id="correo_interesado" name="correo_interesado" type="text"
                                    value="<?php echo htmlspecialchars($tramite['correo_interesado']); ?>" readonly>
                            </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="correo_interesado" class="form-label fw-bold" style="font-size:0.9em">Correo electrónico de interesado</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-envelope-at-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="correo_interesado" name="correo_interesado"
                                            value="<?php echo htmlspecialchars($tramite['correo_interesado'] ?? ''); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                <label for="mutacion_tramite"><b>Seleccione Tramite</b></label>
                                <input class="form-control py-4" id="mutacion_tramite" name="mutacion_tramite" type="text"
                                    value="<?php echo htmlspecialchars($tramite['mutacion_tramite']); ?>" readonly>
                            </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="tipo_tramite" class="form-label fw-bold" style="font-size:0.9em">Tipo de Proceso</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-file-earmark-text-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="tipo_tramite" name="tipo_tramite"
                                            value="<?php echo $mostrar_valor_tramite($tipo_tramite); ?>" readonly>
                                    </div>
                                </div>

                                <?php if ($tipo_tramite === 'ACTUALIZACION'): ?>
                                    <div class="col-md-6 p-1 px-2 my-2">
                                        <label for="mutacion_tramite" class="form-label fw-bold" style="font-size:0.9em">Trámite de actualización</label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text"><i class="bi bi-diagram-3-fill"></i></span>
                                            <input type="text" class="form-control" style="font-size: 0.9em;" id="mutacion_tramite" name="mutacion_tramite"
                                                value="<?php echo $mostrar_valor_tramite($tramite['mutacion_tramite'] ?? ''); ?>" readonly>
                                        </div>
                                    </div>

                                    <?php if (!empty($tramite['subtipo_actualizacion'])): ?>
                                        <div class="col-md-6 p-1 px-2 my-2">
                                            <label for="subtipo_actualizacion" class="form-label fw-bold" style="font-size:0.9em">Subtipo de actualización</label>
                                            <div class="input-group shadow-sm">
                                                <span class="input-group-text"><i class="bi bi-list-check"></i></span>
                                                <input type="text" class="form-control" style="font-size: 0.9em;" id="subtipo_actualizacion" name="subtipo_actualizacion"
                                                    value="<?php echo $mostrar_valor_tramite($tramite['subtipo_actualizacion']); ?>" readonly>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($tramite['otro_proceso_actualizacion'])): ?>
                                        <div class="col-md-6 p-1 px-2 my-2">
                                            <label for="otro_proceso_actualizacion" class="form-label fw-bold" style="font-size:0.9em">Otro proceso de actualización</label>
                                            <div class="input-group shadow-sm">
                                                <span class="input-group-text"><i class="bi bi-pencil-square"></i></span>
                                                <input type="text" class="form-control" style="font-size: 0.9em;" id="otro_proceso_actualizacion" name="otro_proceso_actualizacion"
                                                    value="<?php echo $mostrar_valor_tramite($tramite['otro_proceso_actualizacion']); ?>" readonly>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="col-md-6 p-1 px-2 my-2">
                                        <label for="subtipo_conservacion" class="form-label fw-bold" style="font-size:0.9em">Subtipo de conservación</label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text"><i class="bi bi-diagram-3-fill"></i></span>
                                            <input type="text" class="form-control" style="font-size: 0.9em;" id="subtipo_conservacion" name="subtipo_conservacion"
                                                value="<?php echo $mostrar_valor_tramite($tramite['subtipo_conservacion'] ?? $tramite['mutacion_tramite'] ?? ''); ?>" readonly>
                                        </div>
                                    </div>

                                    <?php if (!empty($tramite['detalle_subtipo_conservacion'])): ?>
                                        <div class="col-md-6 p-1 px-2 my-2">
                                            <label for="detalle_subtipo_conservacion" class="form-label fw-bold" style="font-size:0.9em">Detalle de conservación</label>
                                            <div class="input-group shadow-sm">
                                                <span class="input-group-text"><i class="bi bi-list-check"></i></span>
                                                <input type="text" class="form-control" style="font-size: 0.9em;" id="detalle_subtipo_conservacion" name="detalle_subtipo_conservacion"
                                                    value="<?php echo $mostrar_valor_tramite($tramite['detalle_subtipo_conservacion']); ?>" readonly>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($tramite['otro_subtipo_conservacion'])): ?>
                                        <div class="col-md-6 p-1 px-2 my-2">
                                            <label for="otro_subtipo_conservacion" class="form-label fw-bold" style="font-size:0.9em">Otro subtipo de conservación</label>
                                            <div class="input-group shadow-sm">
                                                <span class="input-group-text"><i class="bi bi-pencil-square"></i></span>
                                                <input type="text" class="form-control" style="font-size: 0.9em;" id="otro_subtipo_conservacion" name="otro_subtipo_conservacion"
                                                    value="<?php echo $mostrar_valor_tramite($tramite['otro_subtipo_conservacion']); ?>" readonly>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <!-- <div class="col-md-6">
                                <label for="tsolicitante_tramite"><b>Seleccione Tipo Solicitante</b></label>
                                <input class="form-control py-4" id="tsolicitante_tramite" name="tsolicitante_tramite" type="text"
                                    value="<?php echo htmlspecialchars($tramite['tsolicitante_tramite']); ?>" readonly>
                            </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="tsolicitante_tramite" class="form-label fw-bold" style="font-size:0.9em">Tipo de solicitante</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-bounding-box"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="tsolicitante_tramite" name="tsolicitante_tramite"
                                            value="<?php echo htmlspecialchars($tramite['tsolicitante_tramite'] ?? ''); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                <label for="fmi_predio"><b>FMI Predio</b></label>
                                <input class="form-control py-4" id="fmi_predio" name="fmi_predio" type="text"
                                    value="<?php echo htmlspecialchars($tramite['fmi_predio']); ?>" readonly>
                            </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="fmi_predio" class="form-label fw-bold" style="font-size:0.9em">FMI predio</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-map-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="fmi_predio" name="fmi_predio"
                                            value="<?php echo htmlspecialchars($tramite['fmi_predio'] ?? ''); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                <label for="npn_predio"><b>Cod. Catastral Predio</b></label>
                                <input class="form-control py-4" id="npn_predio" name="npn_predio" type="text"
                                    value="<?php echo htmlspecialchars($tramite['npn_predio']); ?>" readonly>
                            </div> -->

                                <!-- <div class="col-md-6 p-1 px-2 my-2">
                                <label for="npn_predio" class="form-label fw-bold" style="font-size:0.9em">Cod. catastral predio</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi-map"></i></span>
                                    <input type="text" class="form-control" style="font-size: 0.9em;" id="npn_predio" name="npn_predio"
                                        value="<?php echo htmlspecialchars($tramite['npn_predio'] ?? ''); ?>" readonly>
                                </div>
                            </div> -->
                                <div class="col-md-6 p-1 px-2 my-1">
                                    <label for="npn_predio" class="form-label fw-bold" style="font-size:0.9em;">Cod. catastral predio </label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-map"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="npn_predio"
                                            name="npn_predio" aria-label="PrimerNombre" value="<?php echo htmlspecialchars($tramite['npn_predio'] ?? ''); ?>" readonly>
                                        <a class="bot_mostrar_vista btn" style="font-size:0.9em; border-top-left-radius: 0px !important; border-bottom-left-radius:0px !important;" type="button" id="button-addon2"
                                            href="../neiva_visor_dos/index.html?valor=<?php echo htmlspecialchars($tramite['npn_predio'] ?? ''); ?>" target="_blank">
                                            <i class="bi bi-globe-americas me-1"></i> Ver en Visor Cat.</a>
                                    </div>
                                </div>
                        </div>

                        <!-- FUNCIÓN PARA MOSTRAR Y OCULTAR -->
                        <script>
                            function toggleIframe(id, boton) {
                                const visor = document.getElementById(id);
                                const icono = boton.querySelector("i");
                                const texto = boton.querySelector("span");
                                visor.classList.toggle("mostrar");
                                if (visor.classList.contains("mostrar")) {
                                    icono.classList.replace("bi-eye", "bi-eye-slash");
                                    texto.textContent = "Ocultar Vista Previa";
                                } else {
                                    icono.classList.replace("bi-eye-slash", "bi-eye");
                                    texto.textContent = "Mostrar Vista Previa";
                                }
                            }
                        </script>


                        <div class="form-row">

                            <!-- Solicitud escrita -->
                            <div class="col-md-6 p-1 px-3 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em">Solicitud escrita</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-file-earmark-pdf-fill"></i></span>
                                    <input type="text" class="form-control" style="font-size: 0.9em;"
                                        value="<?php echo htmlspecialchars($tramite['sol_escrita_tramite']); ?>" readonly>
                                </div>

                                <?php if (!empty($tramite['sol_escrita_tramite']) && $existe_documento($tramite['sol_escrita_tramite'])): ?>
                                    <div class="d-flex justify-content-center gap-2 my-2">
                                        <a href="<?php echo $url_documento($tramite['sol_escrita_tramite']); ?>"
                                            target="_blank" class="bot_verenotrapesta btn btn-sm px-3">
                                            <i class="bi bi-box-arrow-right me-2"></i> Ver en otra pestaña
                                        </a>
                                        <button type="button" class="bot_mostrar_vista btn btn-sm px-3"
                                            onclick="toggleIframe('visor_sol_escrita', this)">
                                            <i class="bi bi-eye me-2"></i> <span>Mostrar Vista Previa</span>
                                        </button>
                                    </div>

                                    <div id="visor_sol_escrita" class="iframe-animado">
                                        <iframe src="<?php echo $url_documento($tramite['sol_escrita_tramite']); ?>"
                                            width="100%" height="650px" style="border: 1px solid #ccc;"></iframe>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No se ha cargado ningún documento</span>
                                <?php endif; ?>
                            </div>

                            <!-- Copia de Escritura -->
                            <div class="col-md-6 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em">Copia de Escritura / Sentencia Judicial / Acto Administrativo</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-file-earmark-pdf-fill"></i></span>
                                    <input class="form-control" style="font-size: 0.9em;"
                                        value="<?php echo htmlspecialchars($tramite['cop_escritura_tramite']); ?>" readonly>
                                </div>

                                <?php if (!empty($tramite['cop_escritura_tramite']) && $existe_documento($tramite['cop_escritura_tramite'])): ?>
                                    <div class="d-flex justify-content-center gap-2 my-2">
                                        <a href="<?php echo $url_documento($tramite['cop_escritura_tramite']); ?>"
                                            target="_blank" class="bot_verenotrapesta btn btn-sm px-3">
                                            <i class="bi bi-box-arrow-right me-2"></i> Ver en otra pestaña
                                        </a>
                                        <button type="button" class="bot_mostrar_vista btn btn-sm px-3"
                                            onclick="toggleIframe('visor_escritura',this)">
                                            <i class="bi bi-eye me-2"></i> <span>Mostrar Vista Previa</span>
                                        </button>
                                    </div>
                                    <div id="visor_escritura" class="iframe-animado">
                                        <iframe src="<?php echo $url_documento($tramite['cop_escritura_tramite']); ?>"
                                            width="100%" height="750px" style="border: 1px solid #ccc;"></iframe>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No se ha cargado ningún documento</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Certificado Tradición -->
                        <div class="form-row">
                            <div class="col-md-6 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em">Certificado Tradición y Libertad</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-file-earmark-pdf-fill"></i></span>
                                    <input class="form-control" style="font-size: 0.9em;"
                                        value="<?php echo htmlspecialchars($tramite['ctl_tramite']); ?>" readonly>
                                </div>

                                <?php if (!empty($tramite['ctl_tramite']) && $existe_documento($tramite['ctl_tramite'])): ?>
                                    <div class="d-flex justify-content-center gap-2 my-2">
                                        <a href="<?php echo $url_documento($tramite['ctl_tramite']); ?>"
                                            target="_blank" class="bot_verenotrapesta btn btn-sm px-3">
                                            <i class="bi bi-box-arrow-right me-2"></i> Ver en otra pestaña
                                        </a>
                                        <button type="button" class="bot_mostrar_vista btn btn-sm px-3"
                                            onclick="toggleIframe('visor_ctl',this)">
                                            <i class="bi bi-eye me-2"></i> <span>Mostrar Vista Previa</span>
                                        </button>
                                    </div>
                                    <div id="visor_ctl" class="iframe-animado">
                                        <iframe src="<?php echo $url_documento($tramite['ctl_tramite']); ?>"
                                            width="100%" height="750px" style="border: 1px solid #ccc;"></iframe>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No se ha cargado ningún documento</span>
                                <?php endif; ?>
                            </div>

                            <!-- Documento de Identidad -->
                            <div class="col-md-6 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em">Documento de Identidad</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                                    <input class="form-control" style="font-size: 0.9em;"
                                        value="<?php echo htmlspecialchars($tramite['doc_identidad_tramite']); ?>" readonly>
                                </div>

                                <?php if (!empty($tramite['doc_identidad_tramite']) && $existe_documento($tramite['doc_identidad_tramite'])): ?>
                                    <div class="d-flex justify-content-center gap-2 my-2">
                                        <a href="<?php echo $url_documento($tramite['doc_identidad_tramite']); ?>"
                                            target="_blank" class="bot_verenotrapesta btn btn-sm px-3">
                                            <i class="bi bi-box-arrow-right me-2"></i> Ver en otra pestaña
                                        </a>
                                        <button type="button" class="bot_mostrar_vista btn btn-sm px-3"
                                            onclick="toggleIframe('visor_docid',this)">
                                            <i class="bi bi-eye me-2"></i> <span>Mostrar Vista Previa</span>
                                        </button>
                                    </div>
                                    <div id="visor_docid" class="iframe-animado">
                                        <iframe src="<?php echo $url_documento($tramite['doc_identidad_tramite']); ?>"
                                            width="100%" height="750px" style="border: 1px solid #ccc;"></iframe>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No se ha cargado ningún documento</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Carta de Autorización y Otros Documentos -->
                        <div class="form-row">
                            <div class="col-md-6 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em">Carta de Autorización</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-envelope-open-fill"></i></span>
                                    <input class="form-control" style="font-size: 0.9em;"
                                        value="<?php echo htmlspecialchars($tramite['carta_autorizacion_tramite']); ?>" readonly>
                                </div>

                                <?php if (!empty($tramite['carta_autorizacion_tramite']) && $existe_documento($tramite['carta_autorizacion_tramite'])): ?>
                                    <div class="d-flex justify-content-center gap-2 my-2">
                                        <a href="<?php echo $url_documento($tramite['carta_autorizacion_tramite']); ?>"
                                            target="_blank" class="bot_verenotrapesta btn btn-sm px-3">
                                            <i class="bi bi-box-arrow-right me-2"></i> Ver en otra pestaña
                                        </a>
                                        <button type="button" class="bot_mostrar_vista btn btn-sm px-3"
                                            onclick="toggleIframe('visor_autorizacion',this)">
                                            <i class="bi bi-eye me-2"></i> <span>Mostrar Vista Previa</span>
                                        </button>
                                    </div>
                                    <div id="visor_autorizacion" class="iframe-animado">
                                        <iframe src="<?php echo $url_documento($tramite['carta_autorizacion_tramite']); ?>"
                                            width="100%" height="750px" style="border: 1px solid #ccc;"></iframe>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No se ha cargado ningún documento</span>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em">Otros Documentos</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-file-earmark-pdf"></i></span>
                                    <input class="form-control" style="font-size: 0.9em;"
                                        value="<?php echo htmlspecialchars($tramite['otros_doc_tramite']); ?>" readonly>
                                </div>

                                <?php if (!empty($tramite['otros_doc_tramite']) && $existe_documento($tramite['otros_doc_tramite'])): ?>
                                    <div class="d-flex justify-content-center gap-2 my-2">
                                        <a href="<?php echo $url_documento($tramite['otros_doc_tramite']); ?>"
                                            target="_blank" class="bot_verenotrapesta btn btn-sm px-3">
                                            <i class="bi bi-box-arrow-right me-2"></i> Ver en otra pestaña
                                        </a>
                                        <button type="button" class="bot_mostrar_vista btn btn-sm px-3"
                                            onclick="toggleIframe('visor_otros',this)">
                                            <i class="bi bi-eye me-2"></i> <span>Mostrar Vista Previa</span>
                                        </button>
                                    </div>
                                    <div id="visor_otros" class="iframe-animado">
                                        <iframe src="<?php echo $url_documento($tramite['otros_doc_tramite']); ?>"
                                            width="100%" height="750px" style="border: 1px solid #ccc;"></iframe>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No se ha cargado ningún documento</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group my-4">
                            <label class="my-2 fw-bold">Descripción y Observaciones de Trámite</label>
                            <input class="form-control py-4" style="background-color: #002f5544;"
                                value="<?php echo htmlspecialchars($tramite['observacion_tramite']); ?>" readonly>
                        </div>

                        <hr style="border: none; border-bottom:3px dotted #D1DDD5">

                        <!-- <div class="card-header mx-3 rounded-4 mb-5" style="background-color: #002F55;">
                            <h5 class="text-white text-center py-1 mb-0"><B>INFORMACIÓN DEL PREDIO</B></h5>
                        </div> -->

                        <div class=" p-3  text-center">
                            <h5 style="color: #0A2C1B; font-weight:800;" class="mb-0 ">INFORMACIÓN DEL PREDIO </h5>
                        </div>

                        <hr class="m-2 mb-4 " style="color: #002f44 !important;">

                        <?php
                        if (!empty($propietarios)):
                            $predio = $propietarios[0];
                        ?>

                            <div class="form-row">
                                <!-- <div class="col-md-6">
                                <label for="fmi_predio_tram"><b>FMI Predio/Terreno</b></label>
                                <input class="form-control py-4" id="fmi_predio_tram" name="fmi_predio_tram" type="text"
                                    value="<?php echo htmlspecialchars($info_predio['fmi_predio_tram']); ?>" readonly>
                            </div> -->

                                <div class="col-md-6 p-1 px-2 my-1">
                                    <label for="fmi_predio_tram" class="form-label fw-bold" style="font-size:0.9em;">FMI predio / Terreno</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-map-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="fmi_predio_tram"
                                            name="fmi_predio_tram" aria-label="FMI del predio" value="<?php echo htmlspecialchars($predio['fmi_predio_tram']); ?>" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-1">
                                    <label for="npn_predio_tram" class="form-label fw-bold" style="font-size:0.9em;">Cod. catastral predio - NPN</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-map"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="npn_predio_tram"
                                            name="npn_predio_tram" aria-label="PrimerNombre" value="<?php echo urlencode($predio['npn_predio_tram']); ?>" readonly>
                                        <a class="bot_mostrar_vista btn" style="font-size:0.9em; border-top-left-radius: 0px !important; border-bottom-left-radius:0px !important;" type="button" id="button-addon2"
                                            href="../neiva_visor_dos/index.html?valor=<?php echo urlencode($predio['npn_predio_tram']); ?>" target="_blank">
                                            <i class="bi bi-globe-americas me-1"></i> Ver en Visor Cat.</a>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                <label for="nombre_propietario_tram"><b>Nombre Propietario Predio/Terreno</b></label>
                                <input class="form-control py-4" id="nombre_propietario_tram" name="nombre_propietario_tram" type="text"
                                    value="<?php echo htmlspecialchars($info_predio['nombre_propietario_tram']); ?>" readonly>
                            </div> -->

                                <!-- <div class="col-md-4 p-1 px-2 my-1">
                                    <label for="nombre_propietario_tram" class="form-label fw-bold" style="font-size:0.9em;">Nombre Propietario Predio/Terreno</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="nombre_propietario_tram"
                                            name="nombre_propietario_tram" aria-label="PrimerNombre" value="<?php echo htmlspecialchars($info_predio['nombre_propietario_tram']); ?>" readonly>
                                    </div>
                                </div> -->

                                <!-- Tabla de propietarios -->

                                <div class="col-md-12  my-4 text-center border p-3 rounded-4  shadow-sm">
                                    <label class="form-label my-3">Propietarios del Predio / Terreno</label>
                                    <div class="table-responsive">
                                        <table class="table  table-sm text-center">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th style="background-color: #A2C985; color:#0A2C1B !important; border-top-left-radius: 12px !important; border-bottom-left-radius:12px !important;" class=" text-center px-2 border-0 ">Nombre Propietario</th>
                                                    <th style="background-color: #A2C985; color:#0A2C1B !important; border-radius:0px;" class="text-white text-center px-2 border-0">Tipo Documento</th>
                                                    <th style="background-color: #A2C985; color:#0A2C1B !important; border-top-right-radius: 12px !important; border-bottom-right-radius:12px !important;" class="text-white text-center px-2 border-0">Número Documento</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($propietarios as $prop): ?>
                                                    <tr>
                                                        <td class="py-2 border-0"><?php echo htmlspecialchars($prop['nombre_propietario_tram']); ?></td>
                                                        <td class="py-2 border-0"><?php echo htmlspecialchars($prop['tipo_doc_propietario_tram']); ?></td>
                                                        <td class="py-2 border-0"><?php echo htmlspecialchars($prop['cedula_propietario_tram']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>


                                <!-- <div class="col-md-6">
                                <label for="tipo_doc_propietario_tram"><b>Tipo Documento Propietario Predio/Terreno</b></label>
                                <input class="form-control py-4" id="tipo_doc_propietario_tram" name="tipo_doc_propietario_tram" type="text"
                                    value="<?php echo htmlspecialchars($info_predio['tipo_doc_propietario_tram']); ?>" readonly>
                            </div> -->

                                <!-- <div class="col-md-4 p-1 px-2 my-1">
                                    <label for="tipo_doc_propietario_tram" class="form-label fw-bold" style="font-size:0.9em;">Tipo Documento Propietario Predio/Terreno</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi-person-badge"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="tipo_doc_propietario_tram"
                                            name="tipo_doc_propietario_tram" aria-label="PrimerNombre" value="<?php echo htmlspecialchars($info_predio['tipo_doc_propietario_tram']); ?>" readonly>
                                    </div>
                                </div> -->

                                <!-- <div class="col-md-6">
                                <label for="cedula_propietario_tram"><b>Número de Documento Propietario Predio/Terreno</b></label>
                                <input class="form-control py-4" id="cedula_propietario_tram" name="cedula_propietario_tram" type="text"
                                    value="<?php echo htmlspecialchars($info_predio['cedula_propietario_tram']); ?>" readonly>
                            </div> -->

                                <!-- <div class="col-md-4 p-1 px-2 my-1">
                                    <label for="cedula_propietario_tram" class="form-label fw-bold" style="font-size:0.9em;">Número de Documento Propietario Predio/Terreno</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-vcard-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="cedula_propietario_tram"
                                            name="cedula_propietario_tram" aria-label="PrimerNombre" value="<?php echo htmlspecialchars($info_predio['cedula_propietario_tram']); ?>" readonly>
                                    </div>
                                </div> -->

                                <!-- <div class="col-md-6">
                                <label for="valor_avaluo_terreno_tram"><b>Valor del Avaluo de Predio/Terreno</b></label>
                                <input class="form-control py-4" id="valor_avaluo_terreno_tram" name="valor_avaluo_terreno_tram" type="text"
                                    value="<?php echo htmlspecialchars($info_predio['valor_avaluo_terreno_tram']); ?>" readonly>
                            </div> -->

                                <div class="col-md-4 p-1 px-2 my-1">
                                    <label for="valor_avaluo_terreno_tram" class="form-label fw-bold" style="font-size:0.9em;">Valor del avaluo del predio / Terreno</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-piggy-bank"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="valor_avaluo_terreno_tram"
                                            name="valor_avaluo_terreno_tram" aria-label="PrimerNombre" value="<?php echo htmlspecialchars($predio['valor_avaluo_terreno_tram']); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                <label for="direccion_predio_terreno_tram"><b>Dirección del Predio/Terreno</b></label>
                                <input class="form-control py-4" id="direccion_predio_terreno_tram" name="direccion_predio_terreno_tram" type="text"
                                    value="<?php echo htmlspecialchars($info_predio['direccion_predio_terreno_tram']); ?>" readonly>
                            </div> -->

                                <div class="col-md-4 p-1 px-2 my-1">
                                    <label for="direccion_predio_terreno_tram" class="form-label fw-bold" style="font-size:0.9em;">Dirección del Predio / Terreno</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-signpost-2-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="direccion_predio_terreno_tram"
                                            name="direccion_predio_terreno_tram" aria-label="PrimerNombre" value="<?php echo htmlspecialchars($predio['direccion_predio_terreno_tram']); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                <label for="destino_econ_predio_tram"><b>Destino Economico Predio/Terreno</b></label>
                                <input class="form-control py-4" id="destino_econ_predio_tram" name="destino_econ_predio_tram" type="text"
                                    value="<?php echo htmlspecialchars($info_predio['destino_econ_predio_tram']); ?>" readonly>
                            </div> -->

                                <div class="col-md-4 p-1 px-2 my-1">
                                    <label for="destino_econ_predio_tram" class="form-label fw-bold" style="font-size:0.9em;">Destinación económica predio / Terreno</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-house-exclamation "></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="destino_econ_predio_tram"
                                            name="destino_econ_predio_tram" aria-label="PrimerNombre" value="<?php echo htmlspecialchars($predio['destino_econ_predio_tram']); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                <label for="area_terr_predio_tram"><b>Area Predio/Terreno</b></label>
                                <input class="form-control py-4" id="area_terr_predio_tram" name="area_terr_predio_tram" type="text"
                                    value="<?php echo htmlspecialchars($info_predio['area_terr_predio_tram']); ?>" readonly>
                            </div> -->

                                <div class="col-md-6 p-1 px-2 my-1">
                                    <label for="area_terr_predio_tram" class="form-label fw-bold" style="font-size:0.9em;">Area predio / Terreno</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-globe-americas"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="area_terr_predio_tram"
                                            name="area_terr_predio_tram" aria-label="PrimerNombre" value="<?php echo htmlspecialchars($predio['area_terr_predio_tram']); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                <label for="area_cons_predio_tram"><b>Area Construcción Predio/Terreno</b></label>
                                <input class="form-control py-4" id="area_cons_predio_tram" name="area_cons_predio_tram" type="text"
                                    value="<?php echo htmlspecialchars($info_predio['area_cons_predio_tram']); ?>" readonly>
                            </div> -->

                                <div class="col-md-6 p-1 px-2 my-1">
                                    <label for="area_cons_predio_tram" class="form-label fw-bold" style="font-size:0.9em;">Area de construcción predio / Terreno</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-house-door-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="area_cons_predio_tram"
                                            name="area_cons_predio_tram" aria-label="PrimerNombre" value="<?php echo htmlspecialchars($predio['area_cons_predio_tram']); ?>" readonly>
                                    </div>
                                </div>


                            </div>

                        <?php else: ?>
                            <p class="text-danger text-center">No se encontraron datos del predio para este trámite.</p>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>


</div>
<!-- /.container-fluid -->

<!-- Modal de Trazabilidad -->
<div class="modal fade" id="modalTrazabilidad" tabindex="-1" aria-labelledby="modalTrazabilidadLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header text-white" style="background-color: #002F55; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <h5 class="modal-title fw-bold" id="modalTrazabilidadLabel">
                    <i class="bi bi-clock-history me-2"></i>Historial de Trazabilidad - <?php echo htmlspecialchars($cod_tramite); ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <?php if (empty($historial)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-info-circle-fill" style="font-size: 2.5rem;"></i>
                        <p class="mt-3">No hay historial registrado para este trámite.</p>
                    </div>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($historial as $index => $h): ?>
                            <div class="timeline-item mb-4 position-relative d-flex">
                                <div class="timeline-badge-container d-flex flex-column align-items-center me-3">
                                    <div class="timeline-badge rounded-circle d-flex align-items-center justify-content-center text-white <?php echo $h['badge_class']; ?>"
                                        style="width: 40px; height: 40px; z-index: 2;">
                                        <i class="bi <?php
                                                        echo match ($h['tipo']) {
                                                            'radicacion' => 'bi-file-earmark-plus',
                                                            'asignacion' => 'bi-person-plus-fill',
                                                            'revision' => 'bi-file-earmark-check-fill',
                                                            'devolucion' => 'bi-arrow-left-circle-fill',
                                                            'resolucion_procede' => 'bi-check-circle-fill',
                                                            'resolucion_noprocede' => 'bi-x-circle-fill',
                                                            'asignacion_activa', 'entrega_activa' => 'bi-hourglass-split',
                                                            default => 'bi-dot'
                                                        };
                                                        ?>"></i>
                                    </div>
                                    <?php if ($index < count($historial) - 1): ?>
                                        <div class="timeline-line bg-secondary opacity-25" style="width: 2px; flex-grow: 1; min-height: 40px; margin-top: 5px;"></div>
                                    <?php endif; ?>
                                </div>
                                <div class="timeline-panel card border-0 shadow-sm rounded-3 flex-grow-1 p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold mb-0 text-primary" style="font-size: 1.05rem;">
                                            <?php echo htmlspecialchars($h['accion']); ?>
                                        </h6>
                                        <span class="badge bg-secondary opacity-75 text-white" style="font-size: 0.75rem;">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            <?php echo date('d/m/Y h:i A', strtotime($h['fecha'])); ?>
                                        </span>
                                    </div>

                                    <div class="mb-2" style="font-size: 0.9rem;">
                                        <strong>Remitente/Actor:</strong>
                                        <span class="text-dark"><?php echo htmlspecialchars($h['actor']); ?></span>
                                        <?php if (!empty($h['rol_actor'])): ?>
                                            <span class="badge bg-light text-secondary border ms-1" style="font-size: 0.75rem; text-transform: uppercase;">
                                                <?php echo htmlspecialchars($h['rol_actor']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (!empty($h['destinatario'])): ?>
                                        <div class="mb-2" style="font-size: 0.9rem;">
                                            <strong>Destinatario/Asignado:</strong>
                                            <span class="text-dark"><?php echo htmlspecialchars($h['destinatario']); ?></span>
                                            <?php if (!empty($h['rol_destinatario'])): ?>
                                                <span class="badge bg-light text-secondary border ms-1" style="font-size: 0.75rem; text-transform: uppercase;">
                                                    <?php echo htmlspecialchars($h['rol_destinatario']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($h['observacion'])): ?>
                                        <div class="mt-2 bg-light p-2 rounded text-muted mb-2" style="font-size: 0.85rem; border-left: 3px solid #ccc; font-style: italic;">
                                            "<?php echo htmlspecialchars($h['observacion']); ?>"
                                        </div>
                                    <?php endif; ?>

                                    <?php
                                    $adjuntos_trazabilidad = [];
                                    for ($i = 1; $i <= 5; $i++) {
                                        if (!empty($h["doc$i"])) {
                                            $adjuntos_trazabilidad[] = [
                                                'ruta' => $h["doc$i"],
                                                'tipo' => $h["tipo_doc$i"] ?? 'Documento'
                                            ];
                                        }
                                    }
                                    ?>
                                    <?php if (!empty($adjuntos_trazabilidad)): ?>
                                        <div class="mt-2 pt-2 border-top" style="font-size: 0.82rem;">
                                            <span class="fw-bold text-secondary"><i class="bi bi-paperclip me-1"></i> Documentos Adjuntos:</span>
                                            <div class="d-flex flex-wrap gap-2 mt-1">
                                                <?php foreach ($adjuntos_trazabilidad as $adj): ?>
                                                    <?php
                                                    // Sanitizar y limpiar ruta si tiene ../ inicial para asegurar descarga directa
                                                    $clean_url = htmlspecialchars((string)$adj['ruta'], ENT_QUOTES, 'UTF-8');
                                                    ?>
                                                    <a href="<?php echo $clean_url; ?>" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2 fw-semibold rounded-pill d-flex align-items-center gap-1" style="font-size: 0.75rem;">
                                                        <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                                                        <?php echo htmlspecialchars($adj['tipo'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                <button type="button" class="btn text-white fw-bold shadow-sm" style="background-color: #002F55;" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        position: relative;
        padding-left: 10px;
    }

    .timeline-item {
        position: relative;
    }

    .timeline-panel {
        background: #fff;
        border: 1px solid #e3e6f0;
    }

    .timeline-badge {
        box-shadow: 0 0 0 5px rgba(255, 255, 255, 1), 0 0 10px rgba(0, 0, 0, 0.1);
    }
</style>