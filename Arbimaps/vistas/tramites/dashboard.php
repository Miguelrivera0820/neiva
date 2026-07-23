<?php
date_default_timezone_set('America/Bogota');
require_once __DIR__ . '/dashboard_lib.php';

if (!isset($total_cul)) {
    $total_cul = 0;
    $sql_total_cul = "SELECT COUNT(*) AS total_cul FROM procede_tramite";
    $result_total_cul = $mysqli->query($sql_total_cul);
    if ($result_total_cul && $row_total_cul = $result_total_cul->fetch_assoc()) {
        $total_cul = (int)$row_total_cul['total_cul'];
    }
}

$tramites_cerrados = [];
$sql_tramites_cerrados = "
    SELECT cod_radicacion_tramite
    FROM procede_tramite
    UNION
    SELECT cod_radicacion_tramite
    FROM no_procede_completar
";
$result_tramites_cerrados = $mysqli->query($sql_tramites_cerrados);
if ($result_tramites_cerrados) {
    while ($row_cerrado = $result_tramites_cerrados->fetch_assoc()) {
        $tramites_cerrados[$row_cerrado['cod_radicacion_tramite']] = true;
    }
}

$sql_tramites_dashboard = "
    SELECT *
    FROM (
        SELECT
            at.asignacion_cod_tramite AS historial_cod_tramite,
            COALESCE(NULLIF(ua.nombre_usuario, ''), at.asignacion_nombre_usuario) AS historial_nombre_usuario,
            COALESCE(NULLIF(ua.apellido_usuario, ''), at.asignacion_apellido_usuario) AS historial_apellido_usuario,
            COALESCE(NULLIF(ua.rol_usuario, ''), at.asignacion_rol_usuario) AS historial_rol_usuario,
            at.asignacion_estado_tramite AS historial_estado_tramite,
            at.fecha_limite,
            at.asignacion_fecha_tramite AS fecha_movimiento,
            'asignacion' AS etapa
        FROM asignacion_tramite at
        LEFT JOIN usuarios_cons ua
            ON TRIM(CAST(ua.cedula_usuario AS CHAR)) = TRIM(CAST(at.asignacion_cc_usuario AS CHAR))

        UNION ALL

        SELECT
            ea.entrega_cod_tramite AS historial_cod_tramite,
            COALESCE(NULLIF(ue.nombre_usuario, ''), ea.entrega_nombre_usuario) AS historial_nombre_usuario,
            COALESCE(NULLIF(ue.apellido_usuario, ''), ea.entrega_apellido_usuario) AS historial_apellido_usuario,
            COALESCE(NULLIF(ue.rol_usuario, ''), ea.entrega_rol_usuario) AS historial_rol_usuario,
            ea.historial_estado_tramite,
            ea.fecha_limite,
            COALESCE(ea.fecha_creacion, ea.historial_fecha_tramite) AS fecha_movimiento,
            'revision' AS etapa
        FROM entrega_asignacion ea
        LEFT JOIN usuarios_cons ue
            ON TRIM(CAST(ue.cedula_usuario AS CHAR)) = TRIM(CAST(ea.entrega_cc_usuario AS CHAR))
    ) AS movimientos
    ORDER BY fecha_movimiento DESC, etapa DESC
";

$tramites = [];
$tramites_vistos = [];
$result_tramites_dashboard = $mysqli->query($sql_tramites_dashboard);
if ($result_tramites_dashboard) {
    while ($row_tramite = $result_tramites_dashboard->fetch_assoc()) {
        $cod_tramite_actual = $row_tramite['historial_cod_tramite'] ?? '';
        if ($cod_tramite_actual === '' || isset($tramites_vistos[$cod_tramite_actual]) || isset($tramites_cerrados[$cod_tramite_actual])) {
            continue;
        }

        $tramites_vistos[$cod_tramite_actual] = true;
        $tramites[] = $row_tramite;
    }
}

$total_asignaciones = count($tramites);
$total_vencidas = 0;
$hoy_dashboard = new DateTime();
foreach ($tramites as $tramite_dashboard) {
    $fecha_limite_dashboard = $tramite_dashboard['fecha_limite'] ?? '';
    if ($fecha_limite_dashboard === '' || $fecha_limite_dashboard === '0000-00-00') {
        continue;
    }

    $fecha_limite_obj = new DateTime($fecha_limite_dashboard);
    if ((int)$hoy_dashboard->diff($fecha_limite_obj)->format('%r%a') < 0) {
        $total_vencidas++;
    }
}

$conteos_dashboard = [];
$hoy_conteos_dashboard = new DateTime();

foreach ($tramites as $tramite_conteo_dashboard) {
    $responsable_dashboard = trim(
        ($tramite_conteo_dashboard['historial_nombre_usuario'] ?? '') . ' ' .
            ($tramite_conteo_dashboard['historial_apellido_usuario'] ?? '')
    );
    if ($responsable_dashboard === '') {
        $responsable_dashboard = 'Sin responsable';
    }

    $rol_dashboard = $tramite_conteo_dashboard['historial_rol_usuario'] ?? '';
    $clave_responsable = $responsable_dashboard . '|' . $rol_dashboard;

    if (!isset($conteos_dashboard[$clave_responsable])) {
        $conteos_dashboard[$clave_responsable] = [
            'responsable' => $responsable_dashboard,
            'rol' => $rol_dashboard,
            'asignado' => 0,
            'en_asignacion' => 0,
            'en_revision' => 0,
            'a_tiempo' => 0,
            'a_vencer' => 0,
            'vencido' => 0,
            'caducado' => 0,
        ];
    }

    $conteos_dashboard[$clave_responsable]['asignado']++;

    if (($tramite_conteo_dashboard['etapa'] ?? '') === 'revision') {
        $conteos_dashboard[$clave_responsable]['en_revision']++;
    } else {
        $conteos_dashboard[$clave_responsable]['en_asignacion']++;
    }

    $fecha_limite_conteo = $tramite_conteo_dashboard['fecha_limite'] ?? '';
    if ($fecha_limite_conteo === '' || $fecha_limite_conteo === '0000-00-00') {
        continue;
    }

    $fecha_limite_obj = new DateTime($fecha_limite_conteo);
    $dias_conteo = (int)$hoy_conteos_dashboard->diff($fecha_limite_obj)->format('%r%a');

    if ($dias_conteo >= 3) {
        $conteos_dashboard[$clave_responsable]['a_tiempo']++;
    } elseif ($dias_conteo >= 1) {
        $conteos_dashboard[$clave_responsable]['a_vencer']++;
    } elseif ($dias_conteo >= -10) {
        $conteos_dashboard[$clave_responsable]['vencido']++;
    } else {
        $conteos_dashboard[$clave_responsable]['caducado']++;
    }
}

uasort($conteos_dashboard, function ($a, $b) {
    $comparacion_total = ($b['asignado'] ?? 0) <=> ($a['asignado'] ?? 0);
    if ($comparacion_total !== 0) {
        return $comparacion_total;
    }

    return strcmp($a['responsable'] ?? '', $b['responsable'] ?? '');
});

$dashboardError = '';
try {
    $dashboardData = td_dashboard_data($mysqli);
} catch (Throwable $e) {
    $dashboardError = $e->getMessage();
    $dashboardData = [
        'filters' => td_dashboard_filters(),
        'items' => [],
        'conteos' => [],
        'usuarios' => [],
        'roles' => [],
        'mutaciones' => [],
        'status_counts' => ['a_tiempo' => 0, 'a_vencer' => 0, 'vencido' => 0, 'caducado' => 0, 'sin_fecha' => 0],
        'etapa_counts' => ['en_asignacion' => 0, 'en_revision' => 0],
        'mutacion_counts' => [],
        'total_rad' => $total_rad ?? 0,
        'total_cul' => $total_cul ?? 0,
        'total_asignaciones' => 0,
        'total_vencidas' => 0,
    ];
}
$dashboardFilters = $dashboardData['filters'];
$tramites = $dashboardData['items'];
$conteos_dashboard = $dashboardData['conteos'];
$total_rad = $dashboardData['total_rad'];
$total_cul = $dashboardData['total_cul'];
$total_asignaciones = $dashboardData['total_asignaciones'];
$total_vencidas = $dashboardData['total_vencidas'];
$dashboardUsuarios = $dashboardData['usuarios'];
$dashboardRoles = $dashboardData['roles'];
$dashboardMutaciones = $dashboardData['mutaciones'];
$dashboardStatusCounts = $dashboardData['status_counts'];
$dashboardEtapaCounts = $dashboardData['etapa_counts'];
$dashboardMutacionCounts = $dashboardData['mutacion_counts'];
?>

<style>
    /* Número activo en la paginación */
    .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        background-color: #0A2C1B !important;
        border-radius: 20px;
        width: 31px;
        height: 31px;
        border-color: #0A2C1B !important;
        color: #fff !important;
    }

    div.dataTables_wrapper div.dataTables_paginate ul.pagination {
        background-color: #002F55;
    }

    .pagination {
        margin-top: 15px !important;

        align-items: center;
    }

    /* Hover sobre números */
    .dataTables_wrapper .dataTables_paginate .page-link:hover {
        background-color: #457b9d !important;
        /* Azul oscuro */
        color: #fff !important;
    }

    /* Texto de los links de paginación */
    .dataTables_wrapper .dataTables_paginate .page-link {
        color: #002f55 !important;
        /* Azul Bootstrap */
        border-radius: 8px;
        /* Bordes más redondeados */
        margin: 0 2px;
    }

    .dashboard-table-wrapper .dataTables_paginate .pagination {
        align-items: center;
        gap: 0.25rem;
    }

    #dataTable_wrapper .dataTables_paginate .pagination {
        align-items: center;
        gap: 0.25rem;
    }

    .dashboard-table-wrapper div.dataTables_paginate ul.pagination {
        background-color: transparent;
    }

    #dataTable_wrapper div.dataTables_paginate ul.pagination {
        background-color: transparent;
    }

    .dashboard-table-wrapper .dataTables_paginate .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 31px;
        height: 31px;
        color: #0A2C1B !important;
        background-color: #F6F8F7 !important;
        border: 1px solid transparent !important;
        border-radius: 18px !important;
        font-size: 0.8rem;
    }

    #dataTable_wrapper .dataTables_paginate .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 31px;
        height: 31px;
        color: #0A2C1B !important;
        background-color: #F6F8F7 !important;
        border: 1px solid transparent !important;
        border-radius: 18px !important;
        font-size: 0.8rem;
    }

    .dashboard-table-wrapper .dataTables_paginate .page-item.active .page-link {
        background-color: #0A2C1B !important;
        border-color: #0A2C1B !important;
        color: #fff !important;
    }

    #dataTable_wrapper .dataTables_paginate .page-item.active .page-link {
        background-color: #0A2C1B !important;
        border-color: #0A2C1B !important;
        color: #fff !important;
    }

    .dashboard-table-wrapper .dataTables_paginate .page-link:hover {
        background-color: #C0D2C8 !important;
        border-color: #C0D2C8 !important;
        color: #0A2C1B !important;
    }

    #dataTable_wrapper .dataTables_paginate .page-link:hover {
        background-color: #C0D2C8 !important;
        border-color: #C0D2C8 !important;
        color: #0A2C1B !important;
    }

    .dashboard-table-wrapper .dataTables_filter {
        margin-bottom: 0rem;
        text-align: left !important;
    }

    #dataTable_wrapper .dataTables_filter {
        margin-bottom: 0rem;
        text-align: left !important;
    }

    .dashboard-table-wrapper .dataTables_filter label {
        color: #7F8E85;
        font-size: 0.82rem;
        font-weight: 600;
    }

    #dataTable_wrapper .dataTables_filter label {
        color: #7F8E85;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .dashboard-table-wrapper .dataTables_filter input {
        border: 1px solid #C0D2C8;
        border-radius: 18px;
        color: #0A2C1B;
        font-size: 0.82rem;
        margin-left: 0.5rem;
        outline: none;
        padding: 0.45rem 0.85rem;
    }

    #dataTable_wrapper .dataTables_filter input {
        border: 1px solid #C0D2C8;
        border-radius: 18px;
        color: #0A2C1B;
        font-size: 0.82rem;
        margin-left: 0.5rem;
        outline: none;
        padding: 0.45rem 0.85rem;
    }

    .dashboard-table-wrapper .dataTables_filter input:focus {
        border-color: #0A2C1B;
        box-shadow: 0 0 0 0.15rem rgba(10, 44, 27, 0.12);
    }

    #dataTable_wrapper .dataTables_filter input:focus {
        border-color: #0A2C1B;
        box-shadow: 0 0 0 0.15rem rgba(10, 44, 27, 0.12);
    }

    .dashboard-table-wrapper .dataTables_info {
        color: #7F8E85;
        font-size: 0.78rem;
        padding-top: 1rem;
    }

    .dashboard-asignaciones-table {
        border-collapse: separate !important;
        border-spacing: 0;
        color: #0A2C1B;
    }

    .dashboard-asignaciones-table thead th {
        background-color: #EDEDED !important;
        border: 0 !important;
        /* border-bottom: 1px solid #D8DED9 !important; */
        color: #0A2C1B !important;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.8rem 0.65rem;
        text-transform: uppercase;
        vertical-align: middle;

    }

    .dashboard-asignaciones-table tbody td {
        border: 0 !important;
        border-bottom: 1px solid #E3E8E5 !important;
        padding: 0.75rem 0.65rem;
        vertical-align: middle;
    }

    .dashboard-asignaciones-table tbody tr:hover td {
        background-color: #F6F8F7;
    }

    .dashboard-asignaciones-table tbody tr:hover td,
    .dashboard-asignaciones-table tbody tr:hover a {
        color: #0A2C1B !important;
    }

    .boton_Gene_Reporte {
        display: flex;
        align-items: center;
        justify-content: center;
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%);
        border: none;
        font-size: 1rem;
        border-radius: 20px;
    }

    .boton_Gene_Reporte2 {
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(164deg, rgba(214, 221, 218, 0.83) 0%, rgba(255, 255, 255, 0.37) 15%, rgba(255, 255, 255, 1) 85%, rgba(214, 221, 218, 0.68) 100%);
        border: 1px solid #0A2C1B;
        color: #0A2C1B;
        font-size: 1rem;
        border-radius: 20px;
    }

    .card_panel {
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 0%, rgba(15, 61, 38, 1) 100%);
    }
</style>

<!-- DataTables CSS con integración Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

<!-- Begin Page Content -->
<div class="container-fluid rounded-4 p-3" style="background-color:#EDEDED;">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4 px-1 my-4">
        <div class="d-flex flex-column align-items-center gap-1">
            <h2 class=" mb-0 w-100" style="color: #0A2C1B; font-weight: 700 !important"> PANEL DE CONTROL </h2>
            <small style="color: #7F8E85;">Visualización de datos de gestión catastral</small>
        </div>

        <div class="d-flex flex-column flex-sm-row gap-2 align-items-stretch align-items-sm-center">

            <button type="button"
                class="boton_Gene_Reporte  text-white p-3 px-4"
                data-bs-toggle="modal"
                data-bs-target="#modalFiltrarTramite"
                aria-controls="modalFiltrarTramite">
                <i class="bi bi-funnel me-2"></i>
                <small class="h-100">Filtros</small>
            </button>

            <button type="button" id="btnExportarDashboard"
                class="boton_Gene_Reporte2  p-3 " style="background-color: #fff !important;">
                <i class="bi bi-file-earmark-spreadsheet  me-2 "></i>
                <small class="h-100">Exportar tablero Excel</small>
            </button>

        </div>
    </div>

    <?php if (!empty($dashboardError)): ?>
        <div class="alert alert-warning mx-4">
            No se pudo cargar el consolidado del tablero: <?= td_h($dashboardError) ?>
        </div>
    <?php endif; ?>

    <?php
    $hayFiltrosAvanzados = ($dashboardFilters['fecha_rad_desde'] ?? '') !== ''
        || ($dashboardFilters['fecha_rad_hasta'] ?? '') !== ''
        || ($dashboardFilters['fecha_asig_desde'] ?? '') !== ''
        || ($dashboardFilters['fecha_asig_hasta'] ?? '') !== ''
        || ($dashboardFilters['fecha_venc_desde'] ?? '') !== ''
        || ($dashboardFilters['fecha_venc_hasta'] ?? '') !== ''
        || ($dashboardFilters['fecha_resp_desde'] ?? '') !== ''
        || ($dashboardFilters['fecha_resp_hasta'] ?? '') !== '';
    ?>
    <div class="modal fade" id="modalFiltrarTramite" tabindex="-1" aria-labelledby="modalFiltrarTramiteLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <form method="GET" id="filtrar_tramite" class="modal-content border-0" style="border-radius: 18px; overflow: hidden;">
                <input type="hidden" name="page" value="tramites/dashboard">
                <div class="modal-header text-white px-4" style="background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%); border: none;">
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalFiltrarTramiteLabel">
                            <i class="bi bi-funnel me-2"></i>Filtros del tablero
                        </h5>
                        <small class="text-white-50">Refina la visualizacion de tramites del panel de control.</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label mb-1 fw-bold small">Usuario responsable</label>
                            <select class="form-select form-select-sm" name="usuario">
                                <option value="">Todos</option>
                                <?php foreach ($dashboardUsuarios as $usuario): ?>
                                    <option value="<?= td_h($usuario['cc']) ?>" <?= $dashboardFilters['usuario'] === (string) $usuario['cc'] ? 'selected' : '' ?>><?= td_h($usuario['nombre'] . ' - ' . $usuario['rol']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label mb-1 fw-bold small">Estado plazo</label>
                            <select class="form-select form-select-sm" name="tipo_estado">
                                <option value="">Todos</option>
                                <?php foreach (['a_tiempo' => 'A tiempo', 'a_vencer' => 'A vencer', 'vencido' => 'Vencido', 'caducado' => 'Caducado'] as $value => $label): ?>
                                    <option value="<?= td_h($value) ?>" <?= $dashboardFilters['tipo_estado'] === $value ? 'selected' : '' ?>><?= td_h($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label mb-1 fw-bold small">Estado flujo</label>
                            <select class="form-select form-select-sm" name="estado">
                                <option value="">Todos</option>
                                <option value="en_asignacion" <?= $dashboardFilters['estado'] === 'en_asignacion' ? 'selected' : '' ?>>En asignacion</option>
                                <option value="en_revision" <?= $dashboardFilters['estado'] === 'en_revision' ? 'selected' : '' ?>>En revision</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label mb-1 fw-bold small">Condicion</label>
                            <select class="form-select form-select-sm" name="condicion">
                                <option value="">Todas</option>
                                <?php foreach (['A TIEMPO', 'PRIORIDAD', 'SUSPENSION', 'TUTELA'] as $condicion): ?>
                                    <option value="<?= td_h($condicion) ?>" <?= $dashboardFilters['condicion'] === $condicion ? 'selected' : '' ?>><?= td_h($condicion) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label mb-1 fw-bold small">Rol actual</label>
                            <select class="form-select form-select-sm" name="rol">
                                <option value="">Todos</option>
                                <?php foreach ($dashboardRoles as $rol): ?>
                                    <option value="<?= td_h($rol) ?>" <?= $dashboardFilters['rol'] === $rol ? 'selected' : '' ?>><?= td_h($rol) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label mb-1 fw-bold small">Tipo de tramite</label>
                            <select class="form-select form-select-sm" name="mutacion">
                                <option value="">Todas</option>
                                <?php foreach ($dashboardMutaciones as $mutacion): ?>
                                    <option value="<?= td_h($mutacion) ?>" <?= $dashboardFilters['mutacion'] === $mutacion ? 'selected' : '' ?>><?= td_h($mutacion) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-sm w-100" id="btnMasFiltros" style="color:#0A2C1B; border:1px solid #0A2C1B;">
                                <i class="bi bi-sliders me-1"></i> Mas filtros
                            </button>
                        </div>
                    </div>

                    <div id="filtrosAvanzados" class="mt-4 <?= $hayFiltrosAvanzados ? '' : 'd-none' ?>">
                        <div class="border-top pt-3">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label mb-1 fw-bold small">Radicacion desde</label>
                                    <input type="date" class="form-control form-control-sm" name="fecha_rad_desde" value="<?= td_h($dashboardFilters['fecha_rad_desde']) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label mb-1 fw-bold small">Radicacion hasta</label>
                                    <input type="date" class="form-control form-control-sm" name="fecha_rad_hasta" value="<?= td_h($dashboardFilters['fecha_rad_hasta']) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label mb-1 fw-bold small">Asignacion desde</label>
                                    <input type="date" class="form-control form-control-sm" name="fecha_asig_desde" value="<?= td_h($dashboardFilters['fecha_asig_desde']) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label mb-1 fw-bold small">Asignacion hasta</label>
                                    <input type="date" class="form-control form-control-sm" name="fecha_asig_hasta" value="<?= td_h($dashboardFilters['fecha_asig_hasta']) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label mb-1 fw-bold small">Vencimiento desde</label>
                                    <input type="date" class="form-control form-control-sm" name="fecha_venc_desde" value="<?= td_h($dashboardFilters['fecha_venc_desde']) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label mb-1 fw-bold small">Vencimiento hasta</label>
                                    <input type="date" class="form-control form-control-sm" name="fecha_venc_hasta" value="<?= td_h($dashboardFilters['fecha_venc_hasta']) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label mb-1 fw-bold small">Respuesta desde</label>
                                    <input type="date" class="form-control form-control-sm" name="fecha_resp_desde" value="<?= td_h($dashboardFilters['fecha_resp_desde']) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label mb-1 fw-bold small">Respuesta hasta</label>
                                    <input type="date" class="form-control form-control-sm" name="fecha_resp_hasta" value="<?= td_h($dashboardFilters['fecha_resp_hasta']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a class="btn btn-outline-secondary" href="index.php?page=tramites/dashboard">Limpiar</a>
                    <button class="btn text-white" style="background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%);">
                        <i class="bi bi-funnel me-1"></i> Aplicar filtros
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Consultas de cartas-->
    <div class="row d-flex justify-content-center align-items-center ">

        <!-- Sección de las tarjetas de información -->
        <div class="col-lg-12 col-xl-12 d-flex justify-content-center align-items-center ">
            <!-- Consultas -->
            <div class="container-fluid ">
                <div class="row ">

                    <?php
                    // Calcular porcentajes respecto a trámites radicados
                    if ($total_rad > 0) {
                        // Trámites Asignados
                        $porcentaje_asignados = round(($total_asignaciones / $total_rad) * 100);
                        $icono_asignados = $porcentaje_asignados >= 50 ? '<i class="bi bi-caret-up-fill text-success"></i>' : '<i class="bi bi-caret-down-fill text-danger"></i>';
                        $clase_asignados = $porcentaje_asignados >= 50 ? 'positive' : 'negative';

                        // Trámites Culminados
                        $porcentaje_culminados = round(($total_cul / $total_rad) * 100);
                        $icono_culminados = $porcentaje_culminados >= 50 ? '<i class="bi bi-caret-up-fill text-success"></i>' : '<i class="bi bi-caret-down-fill text-danger"></i>';
                        $clase_culminados = $porcentaje_culminados >= 50 ? 'positive' : 'negative';

                        // Trámites Vencidos
                        $porcentaje_vencidos = round(($total_vencidas / $total_rad) * 100);
                        $icono_vencidos = $porcentaje_vencidos >= 50 ? '<i class="bi bi-caret-up-fill text-danger"></i>' : '<i class="bi bi-caret-down-fill text-success"></i>';
                        $clase_vencidos = $porcentaje_vencidos >= 50 ? 'negative' : 'positive'; // Invertido: vencidos altos es negativo


                    } else {
                        // Si no hay trámites radicados, todos los porcentajes son 0
                        $porcentaje_asignados = $porcentaje_culminados = $porcentaje_vencidos = $porcentaje_cartas = $porcentaje_cert = 0;
                        $icono_asignados = $icono_culminados = $icono_vencidos = $icono_cartas = $icono_cert = '<i class="bi bi-caret-down-fill text-danger"></i>';
                        $clase_asignados = $clase_culminados = $clase_vencidos = $clase_cartas = $clase_cert = 'negative';
                    }
                    ?>

                    <!-- carta de tramites radicados -->

                    <div class="col-xl col-md-6  p-1 ">
                        <div class="card especial card_panel h-100" style="border-radius: 18px; ">
                            <div class="card-body ">
                                <div class="row">
                                    <div class="col-12 d-flex justify-content-between align-items-center">
                                        <i class="bi bi-folder  bg-white p-2" style="border-radius: 120px; color:#0A2C1B; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;"></i>
                                        <span class="text-white d-flex align-items-center"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;color:#A0C882;"></i>100%</span>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <div class="text-xs  text-white text-uppercase my-2 " style="font-size:1rem">
                                            Trámites Radicados</div>
                                        <div class="h1 mb-0 text-white-800 d-flex justify-content-between align-items-center mx-1"
                                            style="color: #ffffffff;">
                                            <?php echo $total_rad; ?>
                                            <a type="button" class="btn btn-sm bg-white btn_vermas px-3" href="index.php?page=tramites/consultar_tramite" style="color: #0A2C1B; border-radius: 12px;">Ver más</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- carta de tramites asignados -->
                    <div class="col-xl col-md-6 p-1">
                        <div class="card border-left  especial h-100 " style="border-radius: 15px; ">
                            <div class="card-body ">
                                <div class="row">
                                    <div class="col-12 d-flex justify-content-between align-items-center ">
                                        <i class="bi-clipboard-data p-2 text-white" style=" background-color:#16407D;  border-radius: 120px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;"></i>
                                        <span class="ms-0  <?php echo $porcentaje_asignados >= 50 ? 'positive' : 'negative'; ?>" style="color:#0A2C1B; font-weight:600">
                                            <?php echo $icono_asignados; ?> <?php echo $porcentaje_asignados; ?>%
                                        </span>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <div class="text-xs text-uppercase my-2" style="color:#0A2C1B;font-size:1rem">
                                            Trámites Asignados</div>
                                        <div class="h1 mb-0 text-white-800 d-flex justify-content-between align-items-center mx-1"
                                            style="color:#0A2C1B;">
                                            <?php echo $total_asignaciones; ?>
                                            <a type="button" class="btn btn-sm btn_vermas px-3" style="background-color: #16407D; color: #fff; border: 1px solid #0F5699; border-radius: 12px;"
                                                href="index.php?page=seguimiento/mis_asignaciones">Ver más</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Carta de tramites Culminados -->
                    <div class="col-xl col-md-6  p-1">
                        <div class="card border-left h-100 especial" style="border-radius: 15px;">
                            <div class="card-body ">
                                <div class="row">
                                    <div class="col-12 d-flex justify-content-between align-items-center ">
                                        <i class="bi-file-earmark-check p-2 text-white" style="background-color:#198754; border-radius: 120px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;"></i>
                                        <span class="ms-0 <?php echo $clase_culminados; ?>" style="color:#0A2C1B; font-weight:600">
                                            <?php echo $icono_culminados; ?> <?php echo $porcentaje_culminados; ?>%
                                        </span>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <div class="text-xs text-uppercase my-2" style="color:#0A2C1B;font-size:1rem">
                                            Trámites Culminados</div>
                                        <div class="h1 mb-0 text-white-800 d-flex justify-content-between align-items-center mx-1"
                                            style="color:#0A2C1B;">
                                            <?php echo $total_cul; ?>
                                            <a type="button" class="btn btn-sm bg-success text-white border-success btn_vermas px-3" style="border-radius: 12px;">Ver más</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Carta de trámites vencidos -->
                    <div class="col-xl col-md-6  p-1">
                        <div class="card border-left h-100 especial" style="border-radius: 15px;">
                            <div class="card-body ">
                                <div class="row">
                                    <div class="col-12 d-flex justify-content-between align-items-center">
                                        <i class="bi-clock p-2 text-white" style="background-color:#dc3545; border-radius: 120px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;"></i>
                                        <span class="ms-0 <?php echo $clase_vencidos; ?>" style="color:#0A2C1B; font-weight:600">
                                            <?php echo $icono_vencidos; ?> <?php echo $porcentaje_vencidos; ?>%
                                        </span>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <div class="text-xs text-uppercase my-2" style="color:#0A2C1B;font-size:1rem">
                                            Trámites Vencidos</div>
                                        <div class="h1 mb-0 text-white-800 d-flex justify-content-between align-items-center mx-1"
                                            style="color:#0A2C1B;">
                                            <?php echo $total_vencidas; ?>
                                            <a type="button" class="btn btn-sm bg-danger text-white btn_vermas px-3" style=" border-radius: 12px;">Ver más</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card de gráfica de torta -->
        <div class="col-md-12 col-xl-6  d-flex mt-1 p-1 pe-3 pe-xl-1  ps-md-3">
            <div class="card shadow w-100 h-100 p-3 dashboard-chart-card" style=" border-radius: 15px;">
                <div class=" py-2 mb-2 text-start">
                    <h5 style="color: #0A2C1B;" class="mb-0 fw-bold">Trámites</h5>
                </div>
                <div class="row g-2 align-items-center" style="min-height: 355px !important;">
                    <div class="col-lg-7">
                        <canvas id="myPieChart"></canvas>
                    </div>
                    <div class="col-lg-5">
                        <div class="table-responsive chart_datos" style="max-height: 285px;">
                            <table class="table table-sm mb-0 chart_datos_tabla">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th class="text-center">%</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaResumenTipos">
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-3">Cargando...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card de gráfica de barra -->
        <div class="col-md-12 col-xl-6  d-flex mt-1 p-1 ps-3 ps-xl-1 pe-3">
            <div class="card shadow w-100 h-100 p-3 dashboard-chart-card" style="border-radius: 15px;">
                <div class=" py-2 mb-2 text-start">
                    <h5 style="color: #0A2C1B;" class="mb-0 fw-bold">Tr&aacute;mite por tipo</h5>
                </div>
                <canvas id="myBarChart"></canvas>
            </div>
        </div>

        <div class="col-12  mt-1 px-3">
            <!-- Dropdown Card Example -->
            <div class="card shadow mb-2  h-100" style="border-radius: 20px;">
                <div class="card-body p-3 h-100 d-flex flex-column">
                    <!-- Encabezado -->
                    <div class=" py-2 mb-2 text-start">
                        <h5 class="mb-0 fw-bold" style="color: #0A2C1B;">Distribución y estado de las asignaciones</h5>
                    </div>
                    <div class="table-responsive dashboard-table-wrapper">
                        <table class="table text-center details-table dashboard-asignaciones-table h-100" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr class="text-center">
                                    <th class="text-center" style="border-top-left-radius: 12px; border-bottom-left-radius:12px;">Responsable</th>
                                    <th class="text-center">Rol</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">En Asignación</th>
                                    <th class="text-center">En Revisión</th>
                                    <th class="text-center">A tiempo</th>
                                    <th class="text-center">A vencer</th>
                                    <th class="text-center">Vencido</th>
                                    <th class="text-center" style="border-top-right-radius: 12px; border-bottom-right-radius:12px;">Caducado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Inicializamos arreglo para acumular por responsable
                                $conteos = $conteos_dashboard;

                                if (false) {
                                    foreach ($tramites as $tramite) {

                                        $cod_tramite_actual = $tramite['historial_cod_tramite'] ?? '';
                                        if ($cod_tramite_actual !== '' && isset($tramites_cerrados[$cod_tramite_actual])) {
                                            continue;
                                        }

                                        $responsable = trim(($tramite['historial_nombre_usuario'] ?? '') . ' ' . ($tramite['historial_apellido_usuario'] ?? ''));
                                        if ($responsable === '') {
                                            $responsable = 'Sin responsable';
                                        }
                                        $rol = $tramite['historial_rol_usuario'] ?? '';
                                        $fecha_limite = $tramite['fecha_limite'] ?? '';

                                        if (!isset($conteos[$responsable])) {
                                            $conteos[$responsable] = [
                                                'rol' => $rol,
                                                'asignado' => 0,
                                                'en_asignacion' => 0,
                                                'en_revision' => 0,
                                                'a_tiempo' => 0,
                                                'a_vencer' => 0,
                                                'vencido' => 0,
                                                'caducado' => 0
                                            ];
                                        }

                                        // TOTAL
                                        $conteos[$responsable]['asignado']++;

                                        // 👉 NUEVO: etapa
                                        if ($tramite['etapa'] === 'asignacion') {
                                            $conteos[$responsable]['en_asignacion']++;
                                        } else {
                                            $conteos[$responsable]['en_revision']++;
                                        }

                                        // Fechas
                                        if ($fecha_limite !== '' && $fecha_limite !== '0000-00-00') {
                                            $hoy = new DateTime();
                                            $fechaLimiteObj = new DateTime($fecha_limite);
                                            $diferencia_dias = (int)$hoy->diff($fechaLimiteObj)->format('%r%a');

                                            if ($diferencia_dias >= 3) {
                                                $conteos[$responsable]['a_tiempo']++;
                                            } elseif ($diferencia_dias >= 1 && $diferencia_dias <= 2) {
                                                $conteos[$responsable]['a_vencer']++;
                                            } elseif ($diferencia_dias < 0 && abs($diferencia_dias) <= 10) {
                                                $conteos[$responsable]['vencido']++;
                                            } elseif (abs($diferencia_dias) > 10) {
                                                $conteos[$responsable]['caducado']++;
                                            }
                                        }
                                    }
                                }

                                // Imprimimos la tabla
                                foreach ($conteos as $responsable => $data):
                                ?>
                                    <tr>
                                        <td class=" text-center" style="font-size: 0.8em;">
                                            <a class="fw-bold" style="color:#002F55;" href="index.php?page=tramites/dashboard_usuario=<?= urlencode($data['cc'] ?? '') ?>&rol=<?= urlencode($data['rol'] ?? '') ?>">
                                                <?= htmlspecialchars($data['responsable'] ?? $responsable) ?>
                                            </a>
                                        </td>
                                        <td class="text-center" style="font-size: 0.8em;"><?= htmlspecialchars($data['rol']) ?></td>
                                        <td class="text-center" style="color:black; font-size: 0.8em;"><b><?= $data['asignado'] ?></b></td>
                                        <td class="text-center" style="color:purple; font-size: 0.8em;"><?= $data['en_asignacion'] ?></td>
                                        <td class="text-center" style="color:blue; font-size: 0.8em;"><?= $data['en_revision'] ?></td>
                                        <td class="text-center" style="color:green; font-size: 0.8em"><?= $data['a_tiempo'] ?></td>
                                        <td class="text-center" style="color:orange; font-size: 0.8em"><?= $data['a_vencer'] ?></td>
                                        <td class="text-center" style="color:red; font-size: 0.8em"><?= $data['vencido'] ?></td>
                                        <td class="text-center" style="color:gray; font-size: 0.8em"><?= $data['caducado'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>

                        </table>
                    </div>
                </div>

            </div>

        </div>

        <div class="col-12  mt-1 px-3">
            <!-- Card de gráfica de barra -->
            <div class="card shadow w-100 h-100 p-3 dashboard-chart-card" style="border-radius: 15px;">
                <div class=" py-2 mb-2 text-start">
                    <h5 style="color: #0A2C1B;" class="mb-0 fw-bold">Tr&aacute;mites asignados por usuario</h5>
                </div>
                <canvas id="myBarChart2"></canvas>
            </div>
        </div>


    </div>

</div>


<?php
$graficoUsuariosAsignados = [];
foreach ($conteos_dashboard as $dataUsuarioAsignado) {
    $totalAsignadoUsuario = (int)($dataUsuarioAsignado['asignado'] ?? 0);
    if ($totalAsignadoUsuario <= 0) {
        continue;
    }

    $graficoUsuariosAsignados[] = [
        'usuario' => $dataUsuarioAsignado['responsable'] ?? 'Sin responsable',
        'total' => $totalAsignadoUsuario,
    ];
}

usort($graficoUsuariosAsignados, function ($a, $b) {
    return ($b['total'] ?? 0) <=> ($a['total'] ?? 0);
});

$graficoUsuariosAsignados = array_slice($graficoUsuariosAsignados, 0, 12);
?>

<script>
    function descargarReporteCatastro(registro) {
        const link = document.createElement('a');
        link.href = `vistas/tramites/descargar_reporte_catastral.php?registro=${registro}`;
        link.download = '';
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    document.getElementById('btnReporteCatastro')?.addEventListener('click', function() {
        descargarReporteCatastro(1);
        setTimeout(function() {
            descargarReporteCatastro(2);
        }, 700);
    });

    const usuariosAsignadosGrafico = <?= json_encode($graficoUsuariosAsignados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const ctxBarUsuarios = document.getElementById('myBarChart2')?.getContext('2d');

    if (ctxBarUsuarios) {
        const labelsUsuariosAsignados = usuariosAsignadosGrafico.length > 0 ?
            usuariosAsignadosGrafico.map((item) => item.usuario) : ['Sin asignaciones'];
        const datosUsuariosAsignados = usuariosAsignadosGrafico.length > 0 ?
            usuariosAsignadosGrafico.map((item) => Number(item.total || 0)) : [0];
        const coloresUsuariosAsignados = ['#F2C94C', '#FFD166', '#FFE08A', '#E0A800', '#C99700', '#F6D365'];

        new Chart(ctxBarUsuarios, {
            type: 'bar',
            data: {
                labels: labelsUsuariosAsignados,
                datasets: [{
                    label: 'Tramites asignados',
                    data: datosUsuariosAsignados,
                    backgroundColor: labelsUsuariosAsignados.map((_, index) => coloresUsuariosAsignados[index % coloresUsuariosAsignados.length]),
                    borderColor: 'transparent',
                    borderWidth: 0,
                    borderRadius: 14,
                    borderSkipped: false,
                    barPercentage: 0.72,
                    categoryPercentage: 0.82,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.parsed.x} tramites asignados`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(10, 44, 27, 0.08)'
                        },
                        ticks: {
                            precision: 0,
                            color: '#7F8E85'
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#0A2C1B',
                            font: {
                                size: 11,
                                family: 'Poppins, sans-serif'
                            }
                        }
                    }
                }
            }
        });
    }

    fetch('vistas/tramites/obtener_datos.php')
        .then(response => response.json())
        .then(data => {
            const torta = data.torta || {
                labels: [],
                datos: []
            };
            const barra = data.barra || {
                labels: [],
                datos: []
            };
            const tortaFiltrada = torta.labels.reduce((acc, label, index) => {
                const valor = Number(torta.datos[index] || 0);
                if (valor > 0) {
                    acc.labels.push(label);
                    acc.datos.push(valor);
                }
                return acc;
            }, {
                labels: [],
                datos: []
            });
            if (tortaFiltrada.labels.length === 0) {
                tortaFiltrada.labels = ['Sin datos'];
                tortaFiltrada.datos = [1];
            }
            const barraFiltrada = barra.labels.reduce((acc, label, index) => {
                const valor = Number(barra.datos[index] || 0);
                if (valor > 0) {
                    acc.labels.push(label);
                    acc.datos.push(valor);
                }
                return acc;
            }, {
                labels: [],
                datos: []
            });
            if (barraFiltrada.labels.length === 0) {
                barraFiltrada.labels = ['Sin datos'];
                barraFiltrada.datos = [0];
            }
            const diccionarioTiposTramite = {
                'MUTACION_1': 'Mut. 1',
                'MUTACION_2': 'Mut. 2',
                'MUTACION_3': 'Mut. 3',
                'MUTACION_4': 'Mut. 4',
                'MUTACION_5': 'Mut. 5',
                'MUTACION PRIMERA': 'Mut. 1',
                'MUTACION SEGUNDA': 'Mut. 2',
                'MUTACION TERCERA': 'Mut. 3',
                'MUTACION CUARTA': 'Mut. 4',
                'MUTACION QUINTA': 'Mut. 5',
                'RECTIFICACION': 'Rect.',
                'CANCELACION': 'Canc.',
                'COMPLEMENTACION': 'Compl.',
                'PETICION': 'Petic.',
                'RECLAMO': 'Recl.',
                'QUEJA': 'Queja',
                'SOLICITUD': 'Solic.',
                'CERTIFICADO CATASTRAL': 'Cert.',
                'CERTIFICADOS CATASTRALES': 'Cert.',
                'CONSERVACION': 'Cons.',
                'INSCRIPCION': 'Insc.',
                'DESENGLOBE': 'Des.',
                'ENGLOBE': 'Eng.',
                'AVALUO': 'Aval.',
                'SIN DATOS': 'S/D'
            };
            const labelsBarrasAbreviados = barraFiltrada.labels.map((label) => {
                const normalizado = String(label || '').trim().toUpperCase();
                return diccionarioTiposTramite[normalizado] || String(label || '').replace(/_/g, ' ').slice(0, 6);
            });
            const totalTipos = tortaFiltrada.datos.reduce((sum, value) => sum + Number(value || 0), 0);
            const coloresTorta = [
                '#198754', '#FFDD00', '#DD0000', '#d63384',
                '#16407D', '#fd7e14', '#C9CBCF', '#60D2FD',
                '#20c997', '#6f42c1', '#17a2b8', '#0dcaf0'
            ];
            const tablaTipos = document.getElementById('tablaResumenTipos');
            if (tablaTipos) {
                tablaTipos.innerHTML = tortaFiltrada.labels.map((label, index) => {
                    const cantidad = Number(tortaFiltrada.datos[index] || 0);
                    const porcentaje = totalTipos > 0 ? Math.round((cantidad / totalTipos) * 100) : 0;
                    const color = coloresTorta[index % coloresTorta.length];
                    return `<tr>
                        <td>
                            <span class="chart_datos_tipo">
                                <span class="chart_datos_dot" style="background-color:${color};"></span>
                                <span>
                                    <span class="chart_datos_nombre">${label}</span>
                                    <span class="chart_datos_meta">${cantidad} tramites</span>
                                </span>
                            </span>
                        </td>
                        <td class="text-center chart_datos_porcentaje">${porcentaje}%</td>
                    </tr>`;
                }).join('');
            }
            if (data.error) {
                console.error('Error cargando datos de graficas:', data.error);
            }

            //alert("Datos recibidos:", data);
            // Gráfico de torta
            var ctxPie = document.getElementById("myPieChart").getContext('2d');
            new Chart(ctxPie, {
                type: 'polarArea',
                data: {
                    labels: tortaFiltrada.labels,
                    datasets: [{
                        data: tortaFiltrada.datos,
                        backgroundColor: coloresTorta,
                        borderColor: "#ffffff"
                    }]
                },
                options: {
                    responsive: true, // <-- aquí estaba mal escrito
                    plugins: {
                        legend: {
                            display: false,
                            position: 'bottom',
                            labels: {
                                color: '#001628ff', // en Chart.js v3+ es `color` no `fontColor`
                                font: {
                                    size: 11,
                                    family: 'Poppins, sans-serif'
                                }
                            }
                        }
                    }
                }
            });

            // Gráfico de barra 
            var ctxBar = document.getElementById('myBarChart').getContext('2d');
            const patronCanvasBarras = document.createElement('canvas');
            patronCanvasBarras.width = 10;
            patronCanvasBarras.height = 10;
            const patronCtxBarras = patronCanvasBarras.getContext('2d');
            patronCtxBarras.strokeStyle = 'rgba(10, 44, 27, 0.28)';
            patronCtxBarras.lineWidth = 2;
            patronCtxBarras.beginPath();
            patronCtxBarras.moveTo(-2, 10);
            patronCtxBarras.lineTo(10, -2);
            patronCtxBarras.moveTo(2, 12);
            patronCtxBarras.lineTo(12, 2);
            patronCtxBarras.stroke();
            const patronBarras = ctxBar.createPattern(patronCanvasBarras, 'repeat');
            const coloresGraficoBarras = ['#0A2C1B', '#C0D2C8', '#A2C985', '#0A5F5E', '#029F96', '#029F60', '#E0DA93'];
            const saltosBeteado = [4, 3];
            const indicesBeteados = new Set();
            let siguienteBeteado = 0;
            let indiceSaltoBeteado = 0;

            while (siguienteBeteado < barraFiltrada.labels.length) {
                indicesBeteados.add(siguienteBeteado);
                siguienteBeteado += saltosBeteado[indiceSaltoBeteado % saltosBeteado.length];
                indiceSaltoBeteado++;
            }

            const coloresBarras = barraFiltrada.labels.map((_, index) => {
                return indicesBeteados.has(index) ? patronBarras : coloresGraficoBarras[index % coloresGraficoBarras.length];
            });
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: labelsBarrasAbreviados,
                    datasets: [{
                        label: 'Cantidad de Trámites por tipo',
                        data: barraFiltrada.datos,
                        backgroundColor: coloresBarras,
                        borderColor: coloresBarras,
                        borderWidth: 0,
                        borderRadius: 18,
                        borderSkipped: false,
                        barPercentage: 0.72,
                        categoryPercentage: 0.82,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                title: function(context) {
                                    return barraFiltrada.labels[context[0].dataIndex] || '';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            suggestedMin: 0, // 👈 mejor que min
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }) // <-- cierro el then(data => { ... })
        .catch(error => console.error('Error cargando datos:', error));
</script>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.getElementById('btnExportarDashboard')?.addEventListener('click', function() {
        const query = new URLSearchParams(window.location.search);
        query.delete('page');
        window.location.href = 'vistas/tramites/acciones/exportar_dashboard_excel.php?' + query.toString();
    });

    document.getElementById('btnMasFiltros')?.addEventListener('click', function() {
        const panel = document.getElementById('filtrosAvanzados');
        panel?.classList.toggle('d-none');
    });
</script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- script para poner en español las tablas -->
<script>
    $(document).ready(function() {

        $('#dataTable').DataTable({
            dom: "<'row align-items-center mb-2'<'col-md-6'f><'col-md-6 text-md-end'l>>" +
                "<'row'<'col-12'tr>>" +
                "<'row align-items-center mt-2'<'col-md-5'i><'col-md-7'p>>",
            pageLength: 10,
            pagingType: 'simple_numbers',
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
                search: "Buscar:",
                searchPlaceholder: "Responsable, rol o estado...",
                lengthMenu: "Mostrar _MENU_"
            }
        });

        $('#dataTable2').DataTable({
            language: window.neivaDataTablesLanguage ? window.neivaDataTablesLanguage() : {}
        });

        $('#dataTable3').DataTable({
            language: window.neivaDataTablesLanguage ? window.neivaDataTablesLanguage() : {}
        });

    });
</script>
