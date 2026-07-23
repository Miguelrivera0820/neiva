<?php
// session_start();
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// CONFIG Y CONEXIÓN
$conexion_file = '../conexion.php';
if (!file_exists($conexion_file)) {
    die("Error: No se encuentra el archivo de conexión en: " . $conexion_file);
}
require $conexion_file;

if (!isset($mysqli) || $mysqli === null) {
    die("Error: La variable \$mysqli no está definida en conexion.php. Verifica el archivo.");
}

// CONTROL DE ACCESO
$rol_usuario = $_SESSION['rol_usuario'] ?? '';
$nombre_usuario = $_SESSION['nombre_usuario'] ?? 'Usuario';
$apellido_usuario = $_SESSION['apellido_usuario'] ?? '';
$cedula_usuario = $_SESSION['cedula_usuario'] ?? '';

$roles_permitidos = [
    'administrador',
    'soporte',
    'usuarios_ops',
    'ventanilla_catastral',
    'procedencia_juridica',
    'atencion_procedencia',
    'revision_juridica',
    'lider_juridico',
    'coordinacion_tecnica',
    'control_calidad',
    'consolidacion',
    'editor',
    'edicion',
    'reconocedor',
    'reconocimiento',
    'avaluos',
    'componente_economico',
    'lider_economico',
    'director_catastro',
    'director',
    'director_proyectos'
];
$tiene_permiso = !empty($rol_usuario) && in_array($rol_usuario, $roles_permitidos);
if (!$tiene_permiso) {
    header('Location: ../index_graficas.php');
    exit;
}

$roles_exportar_completados = ['administrador', 'director_proyectos', 'procedencia_juridica'];
$roles_usuario_actual = array_filter([
    $_SESSION['rol_usuario'] ?? '',
    $_SESSION['rol_usuario_dos'] ?? '',
    $_SESSION['rol_usuario_tres'] ?? '',
]);
$puede_exportar_completados = count(array_intersect($roles_exportar_completados, $roles_usuario_actual)) > 0;

if (empty($_SESSION['csrf_cancelar_tramites'])) {
    $_SESSION['csrf_cancelar_tramites'] = bin2hex(random_bytes(32));
}


// Verificar si estamos en modo vista detallada
$action = $_GET['action'] ?? '';
$cod_tramite = $_GET['cod'] ?? '';

// Consulta para la vista detallada
$row_detalle = null;
$notificaciones = [];
$has_notification = false;
$latest_notification_url = '';

function obtenerNotificacionResolucionFinal($mysqli, $cod_tramite)
{
    $sql = "SELECT r.notificacion
            FROM resoluciones r
            INNER JOIN entrega_asignacion ea ON ea.id_entrega_asignacion = r.id_entrega_asignacion
            WHERE ea.entrega_cod_tramite = ?
              AND r.notificacion IS NOT NULL
              AND r.notificacion <> ''
            ORDER BY r.id_resoluciones DESC
            LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return '';
    }
    $stmt->bind_param("s", $cod_tramite);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return $row['notificacion'] ?? '';
}

if ($action === 'ver' && !empty($cod_tramite)) {
    $query_detalle = "SELECT 
        id_procede,
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
        notificacion_procede_nombre
    FROM procede_tramite
    WHERE cod_radicacion_tramite = ?
    ORDER BY id_procede DESC
    LIMIT 1";

    $stmt_detalle = $mysqli->prepare($query_detalle);
    if ($stmt_detalle) {
        $stmt_detalle->bind_param("s", $cod_tramite);
        $stmt_detalle->execute();
        $result_detalle = $stmt_detalle->get_result();
        if ($result_detalle->num_rows > 0) {
            $row_detalle = $result_detalle->fetch_assoc();
        }
        $stmt_detalle->close();
    }

    // Escanear notificaciones en la carpeta notificacion_procede
    if ($row_detalle) {
        $anio = substr($row_detalle['cod_radicacion_tramite'], 4, 4);
        $base_fs = "../../tramites_conservacion/$anio/" . $row_detalle['cod_radicacion_tramite'];
        $url_base_dir = "vistas/tramites_conservacion/$anio/" . $row_detalle['cod_radicacion_tramite'] . "/";
        $dir_fs = $base_fs . '/notificacion_procede';
        if (is_dir($dir_fs)) {
            $files = glob($dir_fs . "/*.pdf");
            foreach ($files as $file) {
                $notificaciones[] = [
                    'name' => basename($file),
                    'subdir' => 'notificacion_procede',
                    'url' => $url_base_dir . 'notificacion_procede/' . rawurlencode(basename($file)),
                    'date' => filemtime($file) // Usar timestamp para ordenar
                ];
            }
        }

        $has_notification = !empty($notificaciones);

        // Fallback a BD si no hay archivos en FS
        if (!$has_notification && !empty($row_detalle['notificacion_procede_nombre'])) {
            $has_notification = true;
            $notificaciones[] = [
                'name' => $row_detalle['notificacion_procede_nombre'],
                'subdir' => 'notificacion_procede',
                'url' => $url_base_dir . 'notificacion_procede/' . rawurlencode($row_detalle['notificacion_procede_nombre']),
                'date' => 0 // Timestamp 0 para BD (menos prioridad)
            ];
        }

        // Ordenar por fecha descendente (más reciente primero)
        if ($has_notification) {
            usort($notificaciones, function ($a, $b) {
                return $b['date'] - $a['date']; // Ordenar por timestamp descendente
            });
            $latest_notification_url = $notificaciones[0]['url'];
        }
    }
}

// Consulta para la tabla principal (solo si no estamos en modo detalle)
$resultado_tramites = false;
if ($action !== 'ver') {
    $sql = "SELECT 
        pt.cod_radicacion_tramite,
        pt.fecha_rad_tramite,
        pt.tipo_mutacion_tramite,
        pt.fecha_resp_tramite,
        pt.nombre_comp_interesado,
        pt.telefono_interesado,
        pt.correo_interesado,
        CASE WHEN pt.notificacion_procede_nombre IS NOT NULL THEN 1 ELSE 0 END as has_notificacion
    FROM procede_tramite pt
    INNER JOIN (
        SELECT cod_radicacion_tramite, MAX(id_procede) AS id_procede
        FROM procede_tramite
        GROUP BY cod_radicacion_tramite
    ) ult ON ult.id_procede = pt.id_procede
    WHERE NOT EXISTS (
        SELECT 1 FROM tramites_cancelados tc
        WHERE tc.cod_tramite = pt.cod_radicacion_tramite
          AND tc.estado = 'CANCELADO'
    )
    ORDER BY pt.id_procede DESC";
    $resultado_tramites = $mysqli->query($sql);
    if (!$resultado_tramites) {
        die("Error en la consulta: " . $mysqli->error);
    }

    // Preparar datos para la tabla con URLs de notificaciones
    $rows = [];
    if ($resultado_tramites && $resultado_tramites->num_rows > 0) {
        while ($row = $resultado_tramites->fetch_assoc()) {
            $cod = $row['cod_radicacion_tramite'] ?? '';
            $anio = substr($cod, 4, 4);
            $base_fs = "../../tramites_conservacion/$anio/$cod";
            $url_base_dir = "vistas/tramites_conservacion/$anio/$cod/";
            $notification_url = '';
            $resolution_notification = obtenerNotificacionResolucionFinal($mysqli, $cod);

            if ($row['has_notificacion']) {
                $dir_fs = $base_fs . '/notificacion_procede';
                if (is_dir($dir_fs)) {
                    $files = glob($dir_fs . "/*.pdf");
                    if (!empty($files)) {
                        // Tomar el archivo más reciente
                        $latest_file = $files[0];
                        $latest_time = filemtime($latest_file);
                        foreach ($files as $file) {
                            $file_time = filemtime($file);
                            if ($file_time > $latest_time) {
                                $latest_file = $file;
                                $latest_time = $file_time;
                            }
                        }
                        $notification_url = $url_base_dir . 'notificacion_procede/' . rawurlencode(basename($latest_file));
                    }
                }
                // Fallback a BD si no hay archivos en FS
                if (!$notification_url) {
                    $sql_notification = "SELECT notificacion_procede_nombre
                                         FROM procede_tramite
                                         WHERE cod_radicacion_tramite = ?
                                           AND notificacion_procede_nombre IS NOT NULL
                                           AND notificacion_procede_nombre <> ''
                                         ORDER BY id_procede DESC
                                         LIMIT 1";
                    $stmt_notification = $mysqli->prepare($sql_notification);
                    if ($stmt_notification) {
                        $stmt_notification->bind_param("s", $cod);
                        $stmt_notification->execute();
                        $stmt_notification->bind_result($db_notification_name);
                        $stmt_notification->fetch();
                        $stmt_notification->close();
                        if (!empty($db_notification_name)) {
                            $notification_url = $url_base_dir . 'notificacion_procede/' . rawurlencode($db_notification_name);
                        }
                    }
                }
            }
            $row['notification_url'] = $notification_url;
            $row['resolution_notification'] = $resolution_notification;
            $rows[] = $row;
        }
        $resultado_tramites->data_seek(0); // Resetear el puntero para reutilizar el resultado
    }
}
?>

<style>
    html,
    body {
        font-family: 'Poppins', sans-serif !important;
        background: #f8f9fc !important;
    }

    .iframe-pdf {
        width: 100%;
        min-height: 70vh;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
    }

    .section-title {
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
        border-bottom: 2px solid #7FAB64;
        color: #7FAB64;
        font-weight: 600;
    }

    .details-table th {
        width: 35%;
        white-space: nowrap;
        background: #f9fafb;
    }

    .details-table td {
        word-break: break-word;
    }

    .pdf-panel {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 0.75rem;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .notification-section {
        background: #fff3cd;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
    }

    .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
        background-color: #002F55 !important;
        border-color: #002F55 !important;
        color: #fff !important;
    }

    .dataTables_wrapper .dataTables_paginate .page-link:hover {
        background-color: #457b9d !important;
        color: #fff !important;
    }

    .dataTables_wrapper .dataTables_paginate .page-link {
        color: #002f55 !important;
        border-radius: 8px;
        margin: 0 2px;
    }

    .modal-altura {
        height: 80vh;
        margin-top: 5%;
    }

    .modal-altura .modal-content {
        height: 100%;
    }

    .modal-altura .modal-body {
        overflow-y: auto;
    }

    .modal-header .modal-title {
        color: #0F5699;
        font-weight: 600;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }

    .nav-tabs .nav-link {
        color: #000000 !important;
    }

    .nav-tabs .nav-link:hover {
        color: #002F55 !important;
    }

    .nav-tabs .nav-link.active {
        color: #002F55 !important;
        font-weight: 600;
        border-bottom: 3px solid #002F55 !important;
        background-color: transparent !important;
    }

    .nav-tabs .nav-link i {
        color: inherit !important;
    }

    .empty-state {
        min-height: 60vh;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px dashed #e5e7eb;
        border-radius: 12px;
        background: #f8fafc;
        padding: 1.25rem;
    }

    .tramites-completos-page {
        background-color: #EDEDED;
        color: #0A2C1B;
        min-height: 100%;
    }

    .tramites-completos-page .tramites-completos-list-card {
        background: transparent;
        border: none;
        border-radius: 18px !important;
        box-shadow: none !important;
        overflow: hidden;
    }

    .tramites-completos-page .tramites-completos-list-header {
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%) !important;
        border: 0;
        color: #ffffff !important;
        padding: 0.95rem 1.15rem !important;
    }

    .tramites-completos-page .tramites-completos-list-header h6 {
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .tramites-completos-page .tramites-completos-list-body {
        background-color: #ffffff;
        border: none;
        border-radius: 0 0 18px 18px;
        padding: 1.25rem;
    }

    .tramites-completos-page .tramites-completos-table-wrap {
        overflow-x: visible;
    }

    .tramites-completos-page #datatable {
        border-collapse: separate !important;
        border-spacing: 0;
        color: #0A2C1B;
        table-layout: fixed;
        width: 100% !important;
    }

    .tramites-completos-page #datatable th,
    .tramites-completos-page #datatable td {
        min-width: 0;
        overflow-wrap: anywhere;
        word-break: normal;
    }

    .tramites-completos-page #datatable thead th {
        background-color: #EDEDED !important;
        border: 0 !important;
        color: #0A2C1B !important;
        font-size: 0.72rem;
        font-weight: 800;
        padding: 0.85rem 0.4rem;
        text-align: center;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: normal;
    }

    .tramites-completos-page #datatable thead th:first-child {
        border-radius: 12px 0 0 12px;
    }

    .tramites-completos-page #datatable thead th:last-child {
        border-radius: 0 12px 12px 0;
    }

    .tramites-completos-page #datatable tbody td {
        border-left: 0 !important;
        border-right: 0 !important;
        border-top: none;
        color: #0A2C1B;
        font-size: 0.78rem;
        padding: 0.7rem 0.35rem;
        text-align: center;
        vertical-align: middle;
    }

    .tramites-completos-page #datatable tbody tr:hover td {
        background-color: #F6F8F7;
    }

    .tramites-completos-page #datatable .btn-action {
        justify-content: center;
        margin: 0.1rem;
        white-space: normal;
    }

    .tramites-completos-page .dataTables_wrapper .dataTables_filter {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 0.54rem;
    }

    .tramites-completos-page .dataTables_wrapper .dataTables_filter label,
    .tramites-completos-page .dataTables_wrapper .dataTables_length label {
        align-items: center;
        color: #0A2C1B;
        display: inline-flex;
        gap: 0.45rem;
        white-space: nowrap;
    }

    .tramites-completos-page .dataTables_wrapper .dataTables_filter input,
    .tramites-completos-page .dataTables_wrapper .dataTables_length select {
        border: 1px solid #C0D2C8;
        border-radius: 14px !important;
    }

    .tramites-completos-page .dataTables_wrapper .dataTables_info {
        color: #7F8E85 !important;
        font-size: 0.82rem;
    }

    .tramites-completos-page .dataTables_wrapper .dataTables_paginate .pagination {
        gap: 0.35rem;
        justify-content: flex-end;
    }

    .tramites-completos-page .dataTables_wrapper .dataTables_paginate .page-link {
        align-items: center;
        background-color: #F6F8F7 !important;
        border: 1px solid transparent !important;
        border-radius: 18px !important;
        color: #0A2C1B !important;
        display: flex;
        font-size: 0.8rem;
        height: 31px;
        justify-content: center;
        min-width: 31px;
    }

    .tramites-completos-page .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
        background-color: #0A2C1B !important;
        border-color: #0A2C1B !important;
        color: #ffffff !important;
    }

    .tramites-completos-page .dataTables_wrapper .dataTables_paginate .page-link:hover {
        background-color: #C0D2C8 !important;
        border-color: #C0D2C8 !important;
        color: #0A2C1B !important;
    }

    #notificationModal .modal-content {
        border: none;
        border-radius: 20px;
        overflow: hidden;
    }

    #notificationModal .modal-header {
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%) !important;
        border: 0;
    }

    #notificationModal .modal-title {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }

    #notificationModal #notificationCod {
        border: 1px dotted #E0DA93;
        border-radius: 10px;
        color: #E0DA93;
        padding: 0.25rem 0.75rem;
    }

    #notificationModal .modal-body,
    #notificationModal .modal-footer {
        background-color: #F6F8F7;
    }

    #notificationModal .modal-footer {
        border-top: 1px solid #D1DDD5;
    }

    #notificationModal .nav-tabs .nav-link {
        border-radius: 11px 11px 0 0;
        color: #0A2C1B !important;
        font-weight: 700;
    }

    #notificationModal .nav-tabs .nav-link.active {
        background-color: #0A2C1B;
        border-color: #D1DDD5;
        color: #ffffff;
    }

    #notificationModal .notification-viewer-alert {
        background-color: #A0C882;
        border: 0;
        border-radius: 16px;
        color: #0A2C1B;
        margin: 1rem auto;
        max-width: 620px;
        text-align: center;
        width: 70%;
    }

    #notificationModal .modal-document-card {
        background-color: #ffffff;
        border: none;
        border-radius: 18px;
        margin: 1.5rem auto;
        overflow: hidden;
        padding: 0;
        width: 90%;
    }

    #notificationModal .modal-document-card iframe {
        border: none !important;
        border-radius: 14px;
        padding: 1rem;
    }

    #notificationModal #openNewTab,
    #notificationModal #openEvidNewTab,
    #notificationModal .modal-footer .btn {
        background-color: #0A2C1B !important;
        border-color: #0A2C1B !important;
        border-radius: 14px;
        color: #ffffff !important;
        font-weight: 700;
        padding: 0.55rem 1rem;
    }

    #notificationModal .evidencia-viewer-alert {
        background-color: #A0C882;
        border: 0;
        border-radius: 16px;
        color: #0A2C1B;
        margin: 1rem auto;
        max-width: 620px;
        text-align: center;
        width: 70%;
    }

    #notificationModal .evidencia-upload-card,
    #notificationModal .evidencia-preview-card {
        background-color: #ffffff;
        border: none !important;
        border-radius: 18px !important;
        box-shadow: none !important;
        margin: 1.5rem auto;
        padding: 1.25rem;
        width: 90%;
    }

    #notificationModal .evidencia-upload-form>label {
        color: #0A2C1B;
        font-size: 0.9rem;
        font-weight: 800;
        margin-bottom: 0.65rem;
    }

    #notificationModal .evidencia-upload-group {
        border: 1px solid #C0D2C8;
        border-radius: 14px;
        box-shadow: none !important;
        height: 46px;
        margin: auto;
        max-width: 620px;
        overflow: hidden;
        width: 100% !important;
    }

    #notificationModal .evidencia-upload-group .input-group-text {
        background-color: #F6F8F7 !important;
        border: 0 !important;
        color: #0A2C1B;
        min-width: 44px;
        justify-content: center;
    }

    #notificationModal .evidencia-upload-label {
        background-color: #ffffff;
        border: 0 !important;
        color: #7F8E85;
        cursor: pointer;
        font-size: 0.88rem;
        margin: 0;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    #notificationModal .evidencia-upload-button {
        background-color: #0A2C1B !important;
        border-color: #0A2C1B !important;
        border-radius: 14px;
        color: #ffffff !important;
        font-weight: 800;
        padding: 0.55rem 1rem;
    }

    #notificationModal .evidencia-preview-title {
        color: #0A2C1B;
        font-weight: 800;
        margin-bottom: 1rem;
    }

    #notificationModal #evidenciaContent .iframe-pdf,
    #notificationModal #evidenciaContent img {
        border: 0px solid #C0D2C8 !important;
        border-radius: 14px !important;
        margin-bottom: 1rem;
        width: 100%;
        max-height: 950px !important;
    }

    #notificationModal #evidenciaContent .empty-state {
        background-color: rgba(174, 225, 54, 0.12);
        border: 1px dashed #A0C882;
        border-radius: 14px;
        color: #0A2C1B;
        min-height: 280px;
    }

    .tramites-completos-page .tramite-detail-card {
        background: transparent;
        border: none;
        border-radius: 18px;
        box-shadow: none !important;
        overflow: visible;
    }

    .tramites-completos-page.tramites-completos-detail-page {
        height: auto !important;
        min-height: 100%;
        overflow: visible;
    }

    .tramites-completos-page .tramite-detail-header {
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%) !important;
        border: 0;
        border-radius: 18px !important;
        color: #ffffff;
        padding: 1rem 1.25rem !important;
    }

    .tramites-completos-page .tramite-detail-header .btn {
        border: none;
        border-radius: 14px;
        color: #0A2C1B;
        font-weight: 700;
        padding: 0.55rem 0.9rem;
    }

    .tramites-completos-page .tramite-detail-body {
        background: transparent;
        padding: 1.25rem 0;
    }

    .tramites-completos-page .tramite-detail-mini-card {
        background-color: #ffffff;
        border: none;
        border-radius: 18px;
        margin-bottom: 1rem;
        overflow: hidden;
        padding: 1rem;
    }

    .tramites-completos-page .tramite-detail-mini-card .section-title {
        /* border-bottom: 2px solid #7FAB64;
        color: #0A2C1B;
        font-weight: 700;
        margin: 0 0 1rem;
        padding: 0.35rem 0.5rem 0.65rem; */
        margin-bottom: 1rem;
    }

    .tramites-completos-page .tramite-detail-mini-card .details-table {
        border-collapse: separate !important;
        border-spacing: 0;
        color: #0A2C1B;
        margin-bottom: 0;
    }

    .tramites-completos-page .tramite-detail-mini-card .details-table th,
    .tramites-completos-page .tramite-detail-mini-card .details-table td {
        border-left: 0 !important;
        border-right: 0 !important;
        border-top: 0 !important;
        color: #0A2C1B;
        font-size: 0.82rem;
        padding: 0.75rem;
        text-align: center;
        vertical-align: middle;
    }

    .tramites-completos-page .tramite-detail-mini-card .details-table th {
        background-color: #ffffff;
        font-weight: 800;
        width: 40%;
    }

    .tramites-completos-page .tramite-detail-mini-card .details-table tr:hover th,
    .tramites-completos-page .tramite-detail-mini-card .details-table tr:hover td {
        background-color: #F6F8F7;
    }

    .tramites-completos-page .tramite-detail-pdf {
        background-color: #ffffff;
        border: none;
        border-radius: 18px;
        padding: 1.25rem;
    }

    .tramites-completos-page .tramite-detail-pdf h6 {
        color: #0A2C1B !important;
    }

    .tramites-completos-page .tramite-detail-pdf .btn {
        background-color: #0A2C1B !important;
        border-color: #0A2C1B !important;
        border-radius: 14px;
        font-weight: 700;
        padding: 0.55rem 0.9rem;
    }

    .tramites-completos-page .tramite-detail-pdf .iframe-pdf {
        border: 1px solid #C0D2C8;
        border-radius: 14px;
    }

    .tramites-completos-page .tramite-notificaciones-card {
        background-color: #ffffff;
        border: none !important;
        border-radius: 18px !important;
        overflow: hidden;
        padding: 0 !important;
        width: 100%;
    }

    .tramites-completos-page .tramite-notificaciones-header {
        align-items: center;
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%) !important;
        color: #ffffff;
        display: flex;
        font-size: 1rem;
        font-weight: 800;
        gap: 0.5rem;
        justify-content: center;
        padding: 0.95rem 1rem;
    }

    .tramites-completos-page .tramite-notificaciones-header .badge {
        background-color: #AEE136 !important;
        border-radius: 999px;
        color: #0A2C1B !important;
        font-weight: 800;
        margin-left: auto;
        padding: 0.35rem 0.75rem;
    }

    .tramites-completos-page .tramite-notificaciones-body {
        padding: 1.25rem;
    }

    .tramites-completos-page .tramite-notificaciones-title {
        color: #0A2C1B;
        display: inline-flex;
        font-weight: 800;
        margin-bottom: 1rem;
    }

    .tramites-completos-page .tramite-notificacion-item {
        background-color: #F6F8F7 !important;
        border: 1px solid #C0D2C8 !important;
        border-radius: 14px !important;
        padding: 0.85rem !important;
        text-align: left;
    }

    .tramites-completos-page .tramite-notificacion-item .badge {
        background-color: #A0C882 !important;
        border-radius: 10px;
        color: #0A2C1B !important;
    }

    .tramites-completos-page .tramite-notificacion-item .btn {
        background-color: #0A2C1B !important;
        border: 0;
        border-radius: 14px;
        color: #ffffff !important;
        font-weight: 700;
        padding: 0.5rem 0.85rem;
    }

    .tramites-completos-page .tramite-notificacion-empty {
        background-color: rgba(174, 225, 54, 0.16) !important;
        border: 1px solid rgba(174, 225, 54, 0.5);
        border-radius: 14px;
        color: #0A2C1B;
        font-weight: 700;
        margin: 0 auto;
        width: fit-content;
    }

    .tramites-completos-page .tramite-notificacion-upload {
        border-top: 1px solid #E6ECE8;
        margin-top: 0rem;
        padding-top: 1.25rem;
    }

    .tramites-completos-page .tramite-notificacion-upload .input-group {
        border: 1px solid #C0D2C8;
        border-radius: 14px !important;
        box-shadow: none !important;
        height: 46px;
        margin: auto;
        max-width: 620px;
        overflow: hidden;
        width: 100% !important;
    }

    .tramites-completos-page .tramite-notificacion-upload .input-group-text {
        background-color: #F6F8F7 !important;
        border: 0 !important;
        color: #0A2C1B;
    }

    .tramites-completos-page .tramite-notificacion-upload .form-control {
        border: 0 !important;
        color: #0A2C1B;
    }

    .tramites-completos-page .tramite-notificacion-upload .btn-success {
        background-color: #0A2C1B !important;
        border-color: #0A2C1B !important;
        border-radius: 14px;
        color: #ffffff !important;
        font-weight: 800;
        padding: 0.55rem 1rem;
    }

    .view-document {
        background-color: #AEE136 !important;
        border-color: #AEE136 !important;
        color: #0A2C1B !important;
    }

    .btn_ver {
        background-color: #0A2C1B !important;
        border-color: #0A2C1B !important;
        transition: transform 0.22s ease, box-shadow 0.22s ease;
        color: white;
    }

    .btn_ver:hover {
        background-color: #000000 !important;
        transform: scale(1.03);
    }
</style>

<!-- DataTables CSS con integración Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

<!-- Contenido -->
<div class="container-fluid tramites-completos-page rounded-4 p-3 <?php echo ($action === 'ver' && $row_detalle) ? 'tramites-completos-detail-page' : 'h-100'; ?>">

    <div class="d-sm-flex align-items-center justify-content-between my-4 mt-2">
        <h3 class="mb-0 fw-bold rechazados-page-title my-3">
            <?php echo ($action === 'ver' && $row_detalle) ? 'DETALLE DEL TRÁMITE COMPLETADO' : 'TRÁMITES COMPLETADOS'; ?>
        </h3>
    </div>

    <?php if (!empty($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show mt-3">
            <i class="bi bi-check-circle"></i>
            <?php echo htmlspecialchars(str_replace('success:', '', $_GET['msg'])); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($action === 'ver' && $row_detalle): ?>
        <div class="card shadow mb-4 tramite-detail-card">

            <div class="card-header py-4 d-flex flex-row align-items-center justify-content-between rounded-4 tramite-detail-header">
                <div class="text-xs text-white text-uppercase my-2" style="font-size:1rem; font-weight:200;">
                    Detalle del trámite: <br>
                    <small style="font-weight:600; font: size 1rem;"><?php echo htmlspecialchars($row_detalle['cod_radicacion_tramite']); ?></small>
                </div>
                <a href="?page=tramites/cuentas_completadas/tramites_completos" class="btn btn-sm btn-light px-3 py-2">
                    <i class="bi bi-arrow-left me-2"></i> Volver a la lista
                </a>
            </div>

            <div class="card-body tramite-detail-body px-1">
                <div class="container-fluid">
                    <div class="row">
                        <!-- Columna datos -->
                        <div class="col-lg-5 ps-lg-0">
                            <div class="tramite-detail-mini-card">
                                <h6 class="section-title px-2"><i class="bi bi-info-circle me-2"></i>Información del Trámite</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered details-table">
                                        <tbody>
                                            <tr>
                                                <th scope="row">ID Procede</th>
                                                <td><?php echo htmlspecialchars($row_detalle['id_procede'] ?? ''); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Código Radicación</th>
                                                <td><?php echo htmlspecialchars($row_detalle['cod_radicacion_tramite'] ?? ''); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Fecha Radicación</th>
                                                <td><?php echo htmlspecialchars($row_detalle['fecha_rad_tramite'] ?? ''); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Tipo Mutación</th>
                                                <td><?php echo htmlspecialchars($row_detalle['tipo_mutacion_tramite'] ?? ''); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Actividad Trámite</th>
                                                <td><?php echo htmlspecialchars($row_detalle['actividad_tramite'] ?? ''); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Fecha Respuesta</th>
                                                <td><?php echo htmlspecialchars($row_detalle['fecha_resp_tramite'] ?? ''); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Actividad a Realizar</th>
                                                <td><?php echo htmlspecialchars($row_detalle['actividad_a_realizar'] ?? ''); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tramite-detail-mini-card">
                                <h6 class="section-title px-2"><i class="bi bi-person me-2"></i>Interesado</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered details-table">
                                        <tbody>
                                            <tr>
                                                <th scope="row">Nombre Completo</th>
                                                <td><?php echo htmlspecialchars($row_detalle['nombre_comp_interesado'] ?? ''); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Teléfono</th>
                                                <td><?php echo htmlspecialchars($row_detalle['telefono_interesado'] ?? ''); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Correo</th>
                                                <td><?php echo htmlspecialchars($row_detalle['correo_interesado'] ?? ''); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tramite-detail-mini-card">
                                <h6 class="section-title px-2"><i class="bi bi-geo-alt me-2"></i>Predio</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered details-table">
                                        <tbody>
                                            <tr>
                                                <th scope="row">Código Catastro</th>
                                                <td><?php echo htmlspecialchars($row_detalle['cod_catastro'] ?? ''); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">FMI Predio</th>
                                                <td><?php echo htmlspecialchars($row_detalle['fmi_predio'] ?? ''); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Dirección Predio</th>
                                                <td><?php echo htmlspecialchars($row_detalle['direccion_predio'] ?? ''); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Propietarios</th>
                                                <td><?php echo htmlspecialchars($row_detalle['propietarios_predio'] ?? ''); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tramite-detail-mini-card mb-0">
                                <h6 class="section-title px-2"><i class="bi bi-file-earmark-pdf me-2"></i>Documentos</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered details-table">
                                        <tbody>
                                            <tr>
                                                <th scope="row">Tipo Oficio</th>
                                                <td><?php echo htmlspecialchars($row_detalle['tipo_oficio'] ?? ''); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Columna PDF -->
                        <div class="col-lg-7 px-0 pe-lg-0">
                            <div class="pdf-panel tramite-detail-pdf">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h6 class="fw-bold mb-0" style="color: #002F55;">
                                        <i class="bi bi-file-earmark-pdf"></i> Documento Generado
                                    </h6>
                                    <?php
                                    $anio = substr($row_detalle['cod_radicacion_tramite'], 4, 4);
                                    $pdfUrl = "vistas/tramites_conservacion/$anio/{$row_detalle['cod_radicacion_tramite']}/procede/oficio_{$row_detalle['cod_radicacion_tramite']}.pdf";
                                    ?>
                                    <a href="<?php echo $pdfUrl; ?>" target="_blank" class="btn text-white btn-sm">
                                        <i class="bi bi-box-arrow-up-right"></i> Abrir en pestaña
                                    </a>
                                </div>
                                <iframe class="iframe-pdf" style="height: 100%;" src="<?php echo $pdfUrl; ?>#toolbar=1&navpanes=0&scrollbar=1" title="Documento generado"></iframe>
                                <div class="text-muted small mt-2">
                                    <i class="bi bi-info-circle"></i> Si el documento no carga correctamente, puede abrirlo en una nueva pestaña.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-5" style="border: none; border-bottom:4px dotted #0A5F5E">
                <!-- SECCIÓN DE NOTIFICACIONES -->
                <div class="col-12 col-lg-10 mt-2  mx-auto">
                    <div class="card card-documentos  shadow h-100 d-flex flex-column text-center my-4 tramite-notificaciones-card">
                        <div class="tramite-notificaciones-header">
                            <i class="bi bi-bell"></i> Notificaciones del Trámite
                            <?php if ($has_notification): ?>
                                <?php
                                $cantidad = count($notificaciones);
                                $texto = ($cantidad === 1) ? 'archivo' : 'archivos';
                                ?>
                                <span class="badge  px-3 rounded-2">
                                    <?php echo $cantidad . ' ' . $texto; ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="tramite-notificaciones-body">

                            <?php if ($has_notification): ?>
                                <!-- Mostrar la notificación más reciente -->
                                <div class="mb-2">
                                    <span class="form-label tramite-notificaciones-title">Notificación Más Reciente</span>
                                    <div class="d-flex justify-content-between align-items-center mb-3 tramite-notificacion-item">
                                        <div class="flex-grow-1">
                                            <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                                            <strong><?php echo htmlspecialchars($notificaciones[0]['name']); ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <span class="badge rounded-3 px-3 mt-2" style="background-color: #A0C882; color: #0A2C1B; "><?php echo htmlspecialchars($notificaciones[0]['subdir']); ?></span>
                                            </small>
                                        </div>
                                        <a href="<?php echo htmlspecialchars($notificaciones[0]['url']); ?>" target="_blank" class="btn_ver btn btn-sm">
                                            <i class="bi bi-box-arrow-up-right"></i> Ver
                                        </a>
                                    </div>
                                </div>

                                <!-- Lista completa de notificaciones (si hay más de una) -->
                                <?php if (count($notificaciones) > 1): ?>
                                    <div class="mb-4">
                                        <h6 class="tramite-notificaciones-title">Otras Notificaciones</h6>
                                        <?php foreach (array_slice($notificaciones, 1) as $notification): ?>
                                            <div class="d-flex justify-content-between align-items-center mb-3 tramite-notificacion-item">
                                                <div class="flex-grow-1">
                                                    <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                                                    <strong><?php echo htmlspecialchars($notification['name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <span class="badge ms-2"><?php echo htmlspecialchars($notification['subdir']); ?></span>
                                                        <span class="ms-2"><?php echo ($notification['date'] === 0) ? 'N/D (BD)' : date('Y-m-d H:i:s', $notification['date']); ?></span>
                                                    </small>
                                                </div>
                                                <a href="<?php echo htmlspecialchars($notification['url']); ?>" target="_blank" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-box-arrow-up-right"></i> Ver
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="alert tramite-notificacion-empty">
                                    <i class="bi bi-exclamation-triangle me-2"></i> No hay notificaciones cargadas.
                                </div>
                            <?php endif; ?>
                            <br>


                            <!-- FORMULARIO DE CARGA DE NUEVA NOTIFICACIÓN -->
                            <form class="tramite-notificacion-upload" action="vistas/tramites/acciones/cargar_notificacion_procede.php" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="cod_tramite" value="<?php echo htmlspecialchars($row_detalle['cod_radicacion_tramite']); ?>">

                                <div class="form-group">
                                    <div class="input-group">

                                        <!-- Ícono (igual estilo al ejemplo) -->
                                        <span class="input-group-text">
                                            <i class="bi bi-file-earmark-text-fill"></i>
                                        </span>

                                        <!-- Label clickable (reemplaza al input visible) -->
                                        <label for="archivo_procede"
                                            class="form-control text-start d-flex align-items-center"
                                            style="cursor: pointer; border-left: none; border-radius: 0 12px 12px 0; height: 100%;">
                                            <span id="archivo-procede-nombre" class="text-muted">Seleccionar archivo PDF</span>
                                        </label>

                                        <!-- Input real (oculto) -->
                                        <input type="file" id="archivo_procede" name="archivo" class="form-control"
                                            accept="application/pdf" required style="display: none;">
                                    </div>

                                    <small class="form-text text-muted">
                                        Solo PDF
                                    </small>
                                </div>

                                <button type="submit" class="btn btn-success mt-2" style="background-color: #002F55;">
                                    <i class="bi bi-cloud-upload me-2"></i>
                                    <?php echo $has_notification ? 'Subir Otra Notificación' : 'Subir Notificación'; ?>
                                </button>
                            </form>
                        </div>

                        <script>
                            // Mostrar el nombre del archivo seleccionado (aislado para no interferir con otros formularios)
                            (function() {
                                const input = document.getElementById('archivo_procede');
                                const nameSpan = document.getElementById('archivo-procede-nombre');
                                input.addEventListener('change', function(e) {
                                    const fileName = this.files && this.files[0] ? this.files[0].name : 'Seleccionar archivo PDF';
                                    nameSpan.textContent = fileName;
                                });
                            })();
                        </script>

                    </div>
                </div>

            </div>
        </div>
    <?php else: ?>
        <div class="card shadow mb-4 tramites-completos-list-card">

            <div class=" tramites-completos-list-header py-3 d-flex flex-row align-items-center justify-content-between" style="background-color: #002F55;">
                <div class="text-xs text-white text-uppercase my-2 " style="font-size:1rem; font-weight:500;">
                    Listado de trámites que procedieron y se asignaron </div>
                <!-- <div class="dropdown no-arrow">
                    <a class="dropdown-toggle text-white" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw"></i>
                    </a>
                </div> -->
            </div>

            <div class="card-body tramites-completos-list-body">
                <?php if ($resultado_tramites && $resultado_tramites->num_rows > 0): ?>
                    <?php if ($puede_exportar_completados): ?>
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <span id="contadorSeleccionadosCompletados" class="text-muted small">0 trámites seleccionados</span>
                            <form method="post" action="vistas/tramites/acciones/exportar_tramites_completados.php"
                                id="formExportarTramitesCompletados" class="m-0">
                                <input type="hidden" name="csrf_token"
                                    value="<?php echo htmlspecialchars($_SESSION['csrf_cancelar_tramites'], ENT_QUOTES); ?>">
                                <input type="hidden" name="exportar_tramites_completados" value="1">
                                <div id="codigosExportacionCompletados"></div>
                                <button type="submit" id="btnExportarSeleccionadosCompletados" class="btn  btn-sm text-dark border-0 rounded-3 px-3" style="color: #0A2C1B !important; background-color:#E0DA93;" disabled>
                                    <i class="bi bi-file-earmark-zip me-1"></i> Descargar ZIP seleccionados
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                    <div class="table-responsive tramites-completos-table-wrap">
                        <table id="datatable" class="table text-center">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="text-align: center; max-width:30px;" data-orderable="false">
                                        <?php if ($puede_exportar_completados): ?>
                                            <input type="checkbox" id="seleccionarTodosCompletados" class="form-check-input" aria-label="Seleccionar todos">
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </th>
                                    <th style="text-align: center;">Código Radicación</th>
                                    <th style="text-align: center;">Fecha Radicación</th>
                                    <th style="text-align: center;">Tipo de Mutación</th>
                                    <th style="text-align: center;">Fecha Respuesta</th>
                                    <th style="text-align: center;">Nombre del Interesado</th>
                                    <th style="text-align: center;">Teléfono</th>
                                    <th style="text-align: center;">Correo</th>
                                    <th style="text-align: center;">Notificación</th>
                                    <?php if ($rol_usuario === 'director_catastro'): ?>
                                        <th style="text-align: center;">Acciones</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $fila): ?>
                                    <tr>
                                        <td class="text-center align-middle">
                                            <?php if ($puede_exportar_completados && !empty($fila['resolution_notification'])): ?>
                                                <input type="checkbox" class="form-check-input tramite-checkbox-completados"
                                                    value="<?php echo htmlspecialchars($fila['cod_radicacion_tramite']); ?>"
                                                    aria-label="Seleccionar <?php echo htmlspecialchars($fila['cod_radicacion_tramite']); ?>">
                                            <?php else: ?>
                                                <span class="text-muted" title="Este trámite no tiene notificación de recibido">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($fila['cod_radicacion_tramite']); ?></td>
                                        <td><?php echo htmlspecialchars($fila['fecha_rad_tramite']); ?></td>
                                        <td><?php echo htmlspecialchars($fila['tipo_mutacion_tramite']); ?></td>
                                        <td><?php echo htmlspecialchars($fila['fecha_resp_tramite']); ?></td>
                                        <td><?php echo htmlspecialchars($fila['nombre_comp_interesado']); ?></td>
                                        <td><?php echo htmlspecialchars($fila['telefono_interesado']); ?></td>
                                        <td><?php echo htmlspecialchars($fila['correo_interesado']); ?></td>
                                        <td style="text-align: center;">
                                            <?php if (!empty($fila['resolution_notification'])): ?>
                                                <a href="index.php?page=seguimiento/resolucion&cod=<?php echo urlencode($fila['cod_radicacion_tramite']); ?>"
                                                    class="btn btn-sm  btn-action fw-bold rounded-4 px-2"
                                                    style="background-color: #FFDD00; color:#0A2C1B; font-size:0.8rem;"
                                                    title="Ver cierre del tramite">
                                                    <i class="bi bi-folder-check "></i> Ver cierre
                                                </a>
                                            <?php elseif (!empty($fila['notification_url'])): ?>
                                                <button type="button" class="btn btn-sm  btn-action view-document fw-bold px-2"
                                                    data-cod="<?php echo htmlspecialchars($fila['cod_radicacion_tramite']); ?>"
                                                    data-url="<?php echo htmlspecialchars($fila['notification_url']); ?>"
                                                    style="background-color: #ffc107; border-radius:14px; font-size:0.8rem;"
                                                    title="Ver notificación">
                                                    <i class="bi bi-bell"></i> Notificación
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted fst-italic small">-</span>
                                            <?php endif; ?>
                                        </td>

                                        <?php if ($rol_usuario === 'director_catastro'): ?>
                                            <td style="text-align: center;">
                                                <a href="?page=tramites/cuentas_completadas/tramites_completos&action=ver&cod=<?php echo urlencode($fila['cod_radicacion_tramite']); ?>"
                                                    class="btn btn-sm text-white btn-action btn_ver rounded-3"
                                                    style="background-color: #002F55;"
                                                    title="Ver detalle">
                                                    <i class="bi bi-eye"></i> Ver
                                                </a>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning text-center">No se encontraron registros en <b>procede_tramite</b>.</div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-altura">
        <div class="modal-content">
            <div class="modal-header p-4">
                <h5 class="modal-title text-white" id="notificationModalLabel">
                    <i class="bi bi-bell me-2"></i> Gestión del Trámite - <span id="notificationCod"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white me-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <!-- Pestañas -->
                <ul class="nav nav-tabs" id="documentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="notificacion-tab" data-bs-toggle="tab" data-bs-target="#notificacion" type="button" role="tab" aria-controls="notificacion" aria-selected="true">
                            <i class="bi bi-bell"></i> Notificación
                        </button>
                    </li>
                    <?php if ($rol_usuario !== 'director_catastro'): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="evidencia-tab" data-bs-toggle="tab" data-bs-target="#evidencia" type="button" role="tab" aria-controls="evidencia" aria-selected="false">
                                <i class="bi bi-folder-check"></i> Evidencia
                            </button>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- Contenido de pestañas -->
                <div class="tab-content mt-3" id="documentTabsContent">

                    <!-- TAB 1: NOTIFICACIÓN -->
                    <div class="tab-pane fade show active" id="notificacion" role="tabpanel" aria-labelledby="notificacion-tab">
                        <div class="notification-viewer-alert p-3">
                            <i class="bi bi-exclamation-circle-fill me-2"></i> Visualizando notificación de: <strong id="notificationCodTab"></strong>
                        </div>

                        <div class="pdf-panel modal-document-card">
                            <iframe id="notificationIframe" class="iframe-pdf" src="" style="min-height: 60vh;"></iframe>
                        </div>

                        <div class="mt-3 mx-auto text-center" style="width: 70%; max-width: 600px;">
                            <a id="openNewTab" href="#" class="btn btn-sm" target="_blank">
                                <i class="bi bi-box-arrow-up-right me-2"></i> Abrir en nueva pestaña
                            </a>
                        </div>
                    </div>

                    <!-- TAB 2: EVIDENCIA -->
                    <?php if ($rol_usuario !== 'director_catastro'): ?>
                        <div class="tab-pane fade" id="evidencia" role="tabpanel" aria-labelledby="evidencia-tab">
                            <div class="evidencia-viewer-alert p-3">
                                <i class="bi bi-folder-check me-2"></i> Subir evidencia para el trámite: <strong id="evidenciaCodTab"></strong>
                            </div>

                            <div class="card  d-flex flex-column text-center evidencia-upload-card">
                                <form action="vistas/tramites/acciones/cargar_evidencia_procede.php" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="cod_tramite" id="evidenciaCodInput" value="">

                                    <div class="form-group mb-3 evidencia-upload-form">
                                        <label class="fw-bold" for="evidencia-archivo-procede">Seleccionar Evidencia:</label>

                                        <div class="input-group evidencia-upload-group">

                                            <!-- Ícono -->
                                            <span class="input-group-text">
                                                <i class="bi bi-file-earmark-text-fill"></i>
                                            </span>

                                            <!-- Label clickable -->
                                            <label for="evidencia-archivo-procede"
                                                class="form-control text-start d-flex align-items-center evidencia-upload-label">
                                                <span id="evidencia-procede-nombre" class="text-muted">Ningún archivo seleccionado</span>
                                            </label>

                                            <!-- Input real (oculto) -->
                                            <input type="file" id="evidencia-archivo-procede" name="archivo" class="form-control"
                                                accept="application/pdf,image/*" required style="display: none;">
                                        </div>

                                        <small class="form-text text-muted">
                                            Solo PDF
                                        </small>
                                    </div>

                                    <button type="submit" class="btn btn-sm evidencia-upload-button">
                                        <i class="bi bi-cloud-upload me-2"></i> Subir Evidencia
                                    </button>
                                </form>
                            </div>

                            <script>
                                // Aislado para no interferir con otros formularios
                                (function() {
                                    const input = document.getElementById('evidencia-archivo-procede');
                                    const nameSpan = document.getElementById('evidencia-procede-nombre');
                                    input.addEventListener('change', function() {
                                        const fileName = this.files && this.files[0] ? this.files[0].name : 'Ningún archivo seleccionado';
                                        nameSpan.textContent = fileName;
                                    });
                                })();
                            </script>




                            <!-- Vista previa -->
                            <div id="evidenciaPreview" class="card card-documentos d-flex flex-column text-center evidencia-preview-card"
                                style="display: none;">
                                <h6 class="evidencia-preview-title"><i class="bi bi-eye me-2"></i> Evidencia existente</h6>
                                <div id="evidenciaContent"></div>
                            </div>

                            <div class="mt-3 mx-auto text-center" style="width: 70%; max-width: 600px;">
                                <a id="openEvidNewTab" href="#" class="btn btn-sm d-none" target="_blank" rel="noopener">
                                    <i class="bi bi-box-arrow-up-right me-2"></i> Abrir evidencia en nueva pestaña
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDescargaArchivosCompletados" tabindex="-1" aria-labelledby="tituloModalDescargaArchivosCompletados" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #002F55; color: white;">
                <h5 class="modal-title" id="tituloModalDescargaArchivosCompletados">Exportando tramites</h5>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-3 fw-semibold" id="textoProgresoExportacionCompletados">Preparando la exportacion...</p>
                <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" id="barraProgresoExportacionCompletados" style="width: 0%; background-color: #002F55;"></div>
                </div>
                <div class="small text-muted mt-2" id="porcentajeProgresoExportacionCompletados">0%</div>
                <div class="mt-3 d-none" id="mensajeErrorExportacionCompletados"></div>
                <button type="button" class="btn btn-primary mt-3 d-none" id="btnCerrarModalDescargaCompletados" data-bs-dismiss="modal">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap 5 JS -->
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        // Inicializar DataTable
        var table = $('#datatable').DataTable({
            responsive: true,
            order: [
                [1, 'desc']
            ],
            language: window.neivaDataTablesLanguage ? window.neivaDataTablesLanguage() : {},
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            columnDefs: [{
                targets: [0, -1, -2],
                orderable: false
            }]
        });

        const seleccionadosCompletados = new Set();
        const contadorSeleccionados = document.getElementById('contadorSeleccionadosCompletados');
        const btnExportarSeleccionados = document.getElementById('btnExportarSeleccionadosCompletados');
        const formExportarCompletados = document.getElementById('formExportarTramitesCompletados');
        const seleccionarTodosCompletados = document.getElementById('seleccionarTodosCompletados');
        const modalDescargaCompletados = document.getElementById('modalDescargaArchivosCompletados');
        const barraProgresoCompletados = document.getElementById('barraProgresoExportacionCompletados');
        const textoProgresoCompletados = document.getElementById('textoProgresoExportacionCompletados');
        const porcentajeProgresoCompletados = document.getElementById('porcentajeProgresoExportacionCompletados');
        const mensajeErrorCompletados = document.getElementById('mensajeErrorExportacionCompletados');
        const btnCerrarModalCompletados = document.getElementById('btnCerrarModalDescargaCompletados');
        let intervaloProgresoCompletados = null;

        function actualizarProgresoCompletados(porcentaje, texto) {
            if (barraProgresoCompletados) {
                barraProgresoCompletados.style.width = porcentaje + '%';
                barraProgresoCompletados.setAttribute('aria-valuenow', porcentaje);
            }
            if (textoProgresoCompletados) {
                textoProgresoCompletados.textContent = texto;
            }
            if (porcentajeProgresoCompletados) {
                porcentajeProgresoCompletados.textContent = porcentaje + '%';
            }
        }

        function inicializarProgresoCompletados() {
            actualizarProgresoCompletados(0, 'Preparando la exportacion...');
            if (mensajeErrorCompletados) {
                mensajeErrorCompletados.className = 'mt-3 d-none';
                mensajeErrorCompletados.textContent = '';
            }
            if (btnCerrarModalCompletados) {
                btnCerrarModalCompletados.classList.add('d-none');
            }
        }

        function mostrarModalDescargaCompletados() {
            inicializarProgresoCompletados();
            if (modalDescargaCompletados) {
                const modalInstance = window.bootstrap && window.bootstrap.Modal ?
                    window.bootstrap.Modal.getOrCreateInstance(modalDescargaCompletados) :
                    null;
                if (modalInstance) {
                    modalInstance.show();
                } else if (window.jQuery) {
                    $(modalDescargaCompletados).modal('show');
                }
            }
            if (intervaloProgresoCompletados) {
                clearInterval(intervaloProgresoCompletados);
            }
            intervaloProgresoCompletados = setInterval(function() {
                const barraActual = parseInt(barraProgresoCompletados?.getAttribute('aria-valuenow') || '0', 10);
                const siguiente = Math.min(90, barraActual + Math.floor(Math.random() * 10) + 4);
                actualizarProgresoCompletados(siguiente, 'Procesando la exportacion...');
            }, 350);
        }

        function detenerProgresoCompletados() {
            if (intervaloProgresoCompletados) {
                clearInterval(intervaloProgresoCompletados);
                intervaloProgresoCompletados = null;
            }
        }

        function finalizarProgresoCompletados(mensaje) {
            detenerProgresoCompletados();
            actualizarProgresoCompletados(100, mensaje || 'Exportacion finalizada.');
            if (btnCerrarModalCompletados) {
                btnCerrarModalCompletados.classList.remove('d-none');
            }
            setTimeout(function() {
                if (modalDescargaCompletados) {
                    const modalInstance = window.bootstrap && window.bootstrap.Modal ?
                        window.bootstrap.Modal.getOrCreateInstance(modalDescargaCompletados) :
                        null;
                    if (modalInstance) {
                        modalInstance.hide();
                    } else if (window.jQuery) {
                        $(modalDescargaCompletados).modal('hide');
                    }
                }
            }, 800);
        }

        function mostrarErrorCompletados(mensaje) {
            detenerProgresoCompletados();
            if (mensajeErrorCompletados) {
                mensajeErrorCompletados.className = 'mt-3 alert alert-danger py-2';
                mensajeErrorCompletados.textContent = mensaje;
            }
            if (btnCerrarModalCompletados) {
                btnCerrarModalCompletados.classList.remove('d-none');
            }
            actualizarProgresoCompletados(0, 'No se pudo completar la exportacion.');
        }

        function actualizarSeleccionCompletados() {
            if (contadorSeleccionados) {
                contadorSeleccionados.textContent = seleccionadosCompletados.size + (seleccionadosCompletados.size === 1 ? ' trámite seleccionado' : ' trámites seleccionados');
            }
            if (btnExportarSeleccionados) {
                btnExportarSeleccionados.disabled = seleccionadosCompletados.size === 0;
            }
            if (seleccionarTodosCompletados) {
                const checkboxesFiltrados = $(table.rows({
                    search: 'applied'
                }).nodes()).find('.tramite-checkbox-completados').toArray();
                seleccionarTodosCompletados.checked = checkboxesFiltrados.length > 0 && checkboxesFiltrados.every(cb => seleccionadosCompletados.has(cb.value));
                seleccionarTodosCompletados.indeterminate = checkboxesFiltrados.some(cb => seleccionadosCompletados.has(cb.value)) && !seleccionarTodosCompletados.checked;
            }
        }

        $('#datatable tbody').on('change', '.tramite-checkbox-completados', function() {
            if (this.checked) {
                seleccionadosCompletados.add(this.value);
            } else {
                seleccionadosCompletados.delete(this.value);
            }
            actualizarSeleccionCompletados();
        });

        if (seleccionarTodosCompletados) {
            seleccionarTodosCompletados.addEventListener('change', function() {
                $(table.rows({
                    search: 'applied'
                }).nodes()).find('.tramite-checkbox-completados').each(function() {
                    this.checked = seleccionarTodosCompletados.checked;
                    if (this.checked) {
                        seleccionadosCompletados.add(this.value);
                    } else {
                        seleccionadosCompletados.delete(this.value);
                    }
                });
                actualizarSeleccionCompletados();
            });
        }

        table.on('draw', function() {
            $(table.rows({
                page: 'current'
            }).nodes()).find('.tramite-checkbox-completados').each(function() {
                this.checked = seleccionadosCompletados.has(this.value);
            });
            actualizarSeleccionCompletados();
        });

        if (btnExportarSeleccionados) {
            btnExportarSeleccionados.addEventListener('click', function(event) {
                event.preventDefault();
                if (seleccionadosCompletados.size === 0) {
                    return;
                }
                if (formExportarCompletados && typeof formExportarCompletados.requestSubmit === 'function') {
                    formExportarCompletados.requestSubmit();
                } else if (formExportarCompletados) {
                    formExportarCompletados.dispatchEvent(new Event('submit', {
                        cancelable: true,
                        bubbles: true
                    }));
                }
            });
        }

        function llenarCodigosSeleccionadosCompletados(contenedor) {
            contenedor.innerHTML = '';
            Array.from(seleccionadosCompletados).forEach(function(codigo) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'codigos[]';
                input.value = codigo;
                contenedor.appendChild(input);
            });
        }

        if (formExportarCompletados) {
            formExportarCompletados.addEventListener('submit', async function(event) {
                event.preventDefault();

                if (seleccionadosCompletados.size === 0) {
                    return;
                }

                llenarCodigosSeleccionadosCompletados(document.getElementById('codigosExportacionCompletados'));
                mostrarModalDescargaCompletados();

                const formData = new FormData(formExportarCompletados);

                try {
                    const response = await fetch(formExportarCompletados.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        const textoError = await response.text();
                        throw new Error(textoError || 'No se pudo completar la exportacion.');
                    }

                    const disposition = response.headers.get('content-disposition') || '';
                    const match = disposition.match(/filename="?([^";]+)"?/i);
                    const filename = match ? decodeURIComponent(match[1]) : 'tramites_completados.zip';
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    window.URL.revokeObjectURL(url);

                    finalizarProgresoCompletados('La descarga ha finalizado.');
                } catch (error) {
                    mostrarErrorCompletados(error.message || 'No se pudo completar la exportacion.');
                }
            });
        }

        // Manejo del botón de notificación
        $(document).on('click', '.view-document', function(e) {
            e.preventDefault();
            const cod = $(this).data('cod');
            const url = $(this).data('url');

            console.log('Abriendo modal para:', cod, url);

            // Actualizar texto del modal
            $('#notificationCod').text(cod);
            $('#notificationCodTab').text(cod);
            $('#evidenciaCodTab').text(cod);
            $('#evidenciaCodInput').val(cod);

            // Configurar iframe y enlaces
            const pdfUrl = url + '#toolbar=1&navpanes=0&scrollbar=1';
            $('#notificationIframe').attr('src', pdfUrl);
            $('#downloadNotification').attr('href', url);
            $('#openNewTab').attr('href', url);

            // Mostrar el modal correcto
            $('#notificationModal').modal('show');

            // Cargar evidencias si corresponde
            cargarEvidencia(cod);
        });

        // Limpiar iframe al cerrar el modal
        $('#documentModal').on('hidden.bs.modal', function() {
            $('#documentIframe').attr('src', '');
        });
    });


    function cargarEvidencia(cod) {
        $.ajax({
            url: 'vistas/tramites/acciones/listar_evidencia_procede.php',
            type: 'GET',
            data: {
                cod_tramite: cod
            },
            dataType: 'json',
            success: function(data) {
                const preview = $('#evidenciaPreview');
                const cont = $('#evidenciaContent');
                const openBtn = $('#openEvidNewTab');

                cont.empty();

                if (data && data.length > 0) {
                    // Mostrar contenedor + botón general a la primera evidencia
                    preview.show();
                    openBtn.attr('href', data[0].url).removeClass('d-none');

                    // Pintar cada evidencia
                    data.forEach(file => {
                        const ext = (file.name || '').split('.').pop().toLowerCase();
                        let viewer = '';

                        if (ext === 'pdf') {
                            viewer = `
              <iframe src="${file.url}#toolbar=1&navpanes=0&scrollbar=1"
                      class="iframe-pdf" style="height:70vh;"></iframe>
            `;
                        } else {
                            viewer = `
              <img src="${file.url}" class="img-fluid rounded border" alt="Evidencia" style="max-height: 400px;">
            `;
                        }

                        cont.append(viewer);
                    });
                } else {
                    // SIN evidencias: mantener el contenedor visible y mostrar alerta bonita
                    preview.show();
                    openBtn.addClass('d-none').attr('href', '#');
                    cont.html(`
          <div class="empty-state text-center">
            <div>
              <i class="bi bi-folder-x" style="font-size: 2rem;"></i>
              <p class="mt-2 mb-0 fw-semibold">No hay evidencia cargada.</p>
              <small class="text-muted">Usa el formulario superior para subir un archivo.</small>
            </div>
          </div>
        `);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error cargando evidencia:', error);
                const preview = $('#evidenciaPreview');
                const cont = $('#evidenciaContent');
                const openBtn = $('#openEvidNewTab');

                preview.show();
                openBtn.addClass('d-none').attr('href', '#');
                cont.html(`
        <div class="empty-state text-center">
          <div>
            <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
            <p class="mt-2 mb-0 fw-semibold">No se pudo cargar la evidencia.</p>
            <small class="text-muted">Intenta nuevamente más tarde.</small>
          </div>
        </div>
      `);
            }
        });
    }
</script>

<script>
    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        const codEvid = urlParams.get('cod_evid');
        if (codEvid) {
            localStorage.setItem('evidencia_' + codEvid, 'true');
        }

        $('.view-document').each(function() {
            const cod = $(this).data('cod');
            if (localStorage.getItem('evidencia_' + cod) === 'true') {
                $(this).css('background-color', '#002f55');
            } else {
                $(this).css('background-color', '#0070cc6e');
            }
        });
    });
</script>


<?php
if (isset($mysqli)) {
    $mysqli->close();
}
?>