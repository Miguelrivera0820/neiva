<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 2) . '/config/permisos.php';
require_once dirname(__DIR__, 3) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('GET');
neiva_require_permission('soporte.gestion_tickets', $PERMISOS);

$chatSoportePageBase = neiva_app_url('Arbimaps/index.php?page=soporte/chat_soporte');
$chatNotificacionesSoporteUrl = neiva_app_url('Arbimaps/vistas/soporte/chat_notificaciones_soporte.php');

$estados = ['PENDIENTE', 'EN REVISION', 'SOLUCIONADO'];
$datos = [];
$conteos = [];

foreach ($estados as $estado) {
    $solicitud_soporte = "SELECT 
        s.codigo_error,
        s.asunto,
        s.descripcion,
        s.prioridad,
        s.tipo_error,
        s.fecha_hora_creacion,
        s.nombre_solicitante,
        s.apellido_solicitante,
        s.correo_solicitante,
        s.celular_solicitante,
        u.nombre_usuario  AS nombre_soporte,
        u.apellido_usuario AS apellido_soporte,
        u.rol_usuario      AS rol_soporte
    FROM solicitud_soporte s
    INNER JOIN usuarios_cons u 
        ON u.id_usuario = s.id_usuario
    WHERE s.estado_error = '$estado'
    ORDER BY 
        FIELD(s.prioridad, 'bajo', 'medio', 'alto', 'urgente') DESC,
        s.fecha_hora_creacion DESC";

    $obtener_datos = $mysqli->query($solicitud_soporte);
    $datos[$estado] = $obtener_datos;
    $conteos[$estado] = $obtener_datos ? $obtener_datos->num_rows : 0;
}
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<style>
    :root {
        --primary-color: #022F55;
        --primary-light: #e7f1ff;
        --secondary-color: #1a202c;
        --success-color: #198754;
        --warning-color: #ffc107;
        --border-radius: 0.5rem;
    }

    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-card {
        background: white;
        padding: 1.25rem;
        border-radius: var(--border-radius);
        border-left: 4px solid var(--primary-color);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .stat-value {
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--primary-color);
    }

    .stat-label {
        font-size: 0.85rem;
        color: #6c757d;
        text-transform: uppercase;
        font-weight: 600;
    }

    .tabla-solicitudes-wrapper {
        background: #ffffff;
        border-radius: var(--border-radius);
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.06);
        padding: 1.25rem;
        margin-top: 0.1rem;
    }

    .tabla-solicitudes-wrapper h5 {
        margin-bottom: 0.75rem;
        color: var(--secondary-color);
        font-weight: 600;
    }

    .tabla-solicitudes thead {
        background-color: #f8fafc;
    }

    .tabla-solicitudes thead th {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        border-bottom-width: 1px;
    }

    .tabla-solicitudes tbody td {
        font-size: 0.9rem;
        vertical-align: middle;
    }

    .badge-estado {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.15rem 0.6rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-estado-pendiente {
        background-color: #fef3c7;
        color: #92400e;
    }

    .badge-estado-enrevision {
        background-color: #e0f2fe;
        color: #0369a1;
    }

    .badge-estado-solucionado {
        background-color: #dcfce7;
        color: #166534;
    }

    .badge-prioridad {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.15rem 0.6rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-prioridad-bajo {
        background-color: #dcfce7;
        color: #166534;
    }

    .badge-prioridad-medio {
        background-color: #fef9c3;
        color: #854d0e;
    }

    .badge-prioridad-alto {
        background-color: #fee2e2;
        color: #b91c1c;
    }

    .badge-prioridad-urgente {
        background-color: #fee2e2;
        color: #991b1b;
        box-shadow: 0 0 0 1px rgba(220, 38, 38, 0.3);
    }

    .titulo-seccion {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--color-texto-muted);
        margin-bottom: 0.25rem;
        font-weight: 600;
    }

    .btn-ver-asunto {
        padding: 0.2rem 0.7rem;
        font-size: 0.8rem;
        border-radius: 999px;
        border: 1px solid var(--primary-color);
        background-color: #ffffff;
        color: var(--primary-color);
        font-weight: 500;
        transition: all 0.2s ease-in-out;
    }

    .btn-ver-asunto:hover {
        background-color: var(--primary-color);
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
    }

    .card-especial {
        margin-top: 5%;
    }

    .contenedor-texto-asunto {
        border: 1px solid #d1d5de;
        border-radius: 10px;
        background-color: #f9fafb;
        padding: 0.75rem 1rem;
        min-height: 120px;
        max-height: 260px;
        overflow-y: auto;
        font-size: 1.2rem;
        color: #111827;
        white-space: pre-wrap;
    }

    div.dataTables_wrapper {
        width: 100%;
        padding: 0.5rem 1.5rem 0.75rem 1.5rem;
        box-sizing: border-box;
    }

    div.dataTables_wrapper div.dataTables_length {
        margin-bottom: 0.5rem;
    }

    div.dataTables_wrapper div.dataTables_filter {
        text-align: right !important;
        margin-bottom: 0.5rem;
    }

    div.dataTables_wrapper div.dataTables_filter input {
        margin-left: .5rem;
    }

    div.dataTables_wrapper div.dataTables_info {
        padding-top: 0.5rem;
    }

    div.dataTables_wrapper div.dataTables_paginate {
        padding-top: 0.5rem;
    }

    div.dataTables_wrapper .dataTables_paginate .pagination {
        justify-content: flex-end !important;
    }


    .table-responsive {
        overflow-x: visible;
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

    .positive {
        color: #166534;
    }

    .negative {
        color: #631704ff;
    }
</style>

<div class="container-fluid p-3">

    <div class="d-flex align-items-center justify-content-center gap-3 mb-3">
        <div class="d-flex align-items-center justify-content-center rounded-circle"
            style="width: 70px; height: 70px; background: var(--primary-light);">
            <i class="bi bi-life-preserver" style="color:var(--primary-color); font-size: 2rem;"></i>
        </div>
        <div class="text-start">
            <span class="text-uppercase small text-muted fw-semibold">Centro de solicitudes</span>
            <h3 class="mb-0 fw-bold" style="color: #002F55;">Soporte técnico</h3>

        </div>
    </div>

    <!-- <div class="stats-container px-5">
        <div class="stat-card">
            <div class="stat-value"><?php echo $conteos['PENDIENTE']; ?></div>
            <div class="stat-label">Pendientes</div>
        </div>
        <div class="stat-card" style="border-left-color: var(--warning-color);">
            <div class="stat-value" style="color: #f57c00;"><?php echo $conteos['EN REVISION']; ?></div>
            <div class="stat-label">En Revisión</div>
        </div>
        <div class="stat-card" style="border-left-color: var(--success-color);">
            <div class="stat-value" style="color: var(--success-color);"><?php echo $conteos['SOLUCIONADO']; ?></div>
            <div class="stat-label">Solucionados</div>
        </div>
        <div class="stat-card" style="border-left-color: #6c757d;">
            <div class="stat-value" style="color: #6c757d;">
                <?php echo array_sum($conteos); ?>
            </div>
            <div class="stat-label">Total</div>
        </div>
    </div> -->

    <div class="row my-3 px-0">
        <div class="col-lg-12 col-xl-12 d-flex justify-content-center align-items-center ">
            <!-- Consultas -->
            <div class="row">

                <?php
                // Calcular porcentajes respecto a tickets de soporte
                $total_tickets = array_sum($conteos);
                if ($total_tickets > 0) {
                    // Tickets pendientes
                    $porcentaje_pendientes = round(($conteos['PENDIENTE'] / $total_tickets) * 100);
                    $icono_pendientes = $porcentaje_pendientes >= 50 ? '<i class="bi bi-caret-up-fill text-success"></i>' : '<i class="bi bi-caret-down-fill text-danger"></i>';
                    $clase_pendientes = $porcentaje_pendientes >= 50 ? 'positive' : 'negative';

                    // Tickets en revisión
                    $porcentaje_revision = round(($conteos['EN REVISION'] / $total_tickets) * 100);
                    $icono_revision = $porcentaje_revision >= 50 ? '<i class="bi bi-caret-up-fill text-success"></i>' : '<i class="bi bi-caret-down-fill text-danger"></i>';
                    $clase_revision = $porcentaje_revision >= 50 ? 'positive' : 'negative';

                    // Tickets solucionados
                    $porcentaje_solucionado = round(($conteos['SOLUCIONADO'] / $total_tickets) * 100);
                    $icono_solucionado = $porcentaje_solucionado >= 50 ? '<i class="bi bi-caret-up-fill text-success"></i>' : '<i class="bi bi-caret-down-fill text-danger"></i>';
                    $clase_solucionado = $porcentaje_solucionado >= 50 ? 'positive' : 'negative';
                } else {
                    // Si no hay tickets de soporte, todos los porcentajes son 0
                    $porcentaje_pendientes = $porcentaje_revision = $porcentaje_solucionado = 0;
                    $icono_pendientes = $icono_revision = $icono_solucionado = '<i class="bi bi-caret-down-fill text-danger"></i>';
                    $clase_pendientes = $clase_revision = $clase_solucionado = 'negative';
                }
                ?>

                <div class="col-xl-3 col-md-6 p-1">
                    <div class="card border-left shadow especial h-100 " style="border-radius: 15px; border-left: 0.25rem solid #0F5699 !important;">
                        <div class="card-body ">
                            <div class="row align-content-center justify-content-center">
                                <div class="col-12 d-flex justify-content-between align-items-center ">
                                    <i class="bi bi-clock-history p-2" style="border-radius: 12px; background-color:#13538f17; color:#0F5699"></i>
                                    <span class="ms-0 <?php echo $porcentaje_pendientes >= 50 ? 'positive' : 'negative'; ?>">
                                        <?php echo $icono_pendientes; ?> <?php echo $porcentaje_pendientes; ?>%
                                    </span>
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="text-xs font-weight-bold text-uppercase  fw-bold" style="color: #0F5699;font-size:13px">
                                        Tickets pendientes</div>
                                    <div class="stat-value"><?php echo $conteos['PENDIENTE']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6  p-1 ">
                    <div class="card shadow h-100 especial" style="border-radius: 15px; border-left: 0.25rem solid #ffc107 !important;">
                        <div class="card-body ">
                            <div class="row">
                                <div class="col-12 d-flex justify-content-between align-items-center">
                                    <i class="bi bi-search p-2" style="border-radius: 12px; background-color:#13538f17; color:#0F5699"></i>
                                    <span class="ms-0  <?php echo $porcentaje_revision >= 50 ? 'positive' : 'negative'; ?>">
                                        <?php echo $icono_revision; ?> <?php echo $porcentaje_revision; ?>%
                                    </span>
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="text-xs font-weight-bold text-uppercase  fw-bold" style="color: #0F5699;font-size:13px">
                                        Tickets en revisión</div>
                                    <div class="stat-value" style="color: #f57c00;"><?php echo $conteos['EN REVISION']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6  p-1">
                    <div class="card  shadow h-100 especial" style="border-radius: 15px; border-left: 0.25rem solid #198754 !important;">
                        <div class="card-body ">
                            <div class="row">
                                <div class="col-12 d-flex justify-content-between align-items-center ">
                                    <i class="bi bi-check-circle p-2" style="border-radius: 12px; background-color:#13538f17; color:#0F5699"></i>
                                    <span class="ms-0  <?php echo $porcentaje_solucionado >= 50 ? 'positive' : 'negative'; ?>">
                                        <?php echo $icono_solucionado; ?> <?php echo $porcentaje_solucionado; ?>%
                                    </span>
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="text-xs font-weight-bold text-uppercase  fw-bold" style="color: #0F5699;font-size:13px">
                                        Tickets solucionados</div>
                                    <div class="stat-value" style="color: var(--success-color);"><?php echo $conteos['SOLUCIONADO']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6  p-1 ">
                    <div class="card shadow card-especial mt-0 h-100" style="border-radius: 18px; background-color: #002F55;">
                        <div class="card-body ">
                            <div class="row">
                                <div class="col-12 d-flex justify-content-between align-items-center">
                                    <i class="bi bi-bug-fill  bg-white  p-2" style="border-radius: 12px;color:#002F55"></i>
                                    <!-- <span class="text-white"><i class="bi bi-caret-up-fill text-success"></i>+15%</span> -->
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="text-xs font-weight-bold text-white text-uppercase  fw-bold" style="font-size:13px">
                                        Total de tickets generados</div>
                                    <div class="stat-value" style="color: #ffffffff;">
                                        <?php echo array_sum($conteos); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- <div class="tabla-solicitudes-wrapper"> -->

    <div class="card card-especial shadow mt-2 h-100  d-flex justify-content-center mt-2">
        <div
            class="card-header py-3 text-center d-flex flex-row align-items-center justify-content-between" style="background: linear-gradient(305deg, #198754, #002F55);; border-radius:0px 15px 0px 0px;">
            <div class="d-flex align-items-center ">
                <div class="d-flex justify-content-center align-items-center me-3 rounded-5  p-2" style="width: 35px; height: 35px; background-color: #ffffffff;">
                    <i class="bi bi-bug" style="color: #002F55;"></i>
                </div>
                <div>
                    <div class=" text-start text-white" style="font-size: 1em;  font-weight: 700; ;">
                        TICKETS
                    </div>
                    <div style="font-size: 70%; color: #f3f8fdff;" class="text-start">
                        Listado de todos los tickets generados en plataforma.
                    </div>
                </div>
            </div>
        </div>
        <div class="table-responsive mt-3">
            <table id="tablaTickets" class="table table-hover tabla-solicitudes">
                <thead>
                    <tr>
                        <th style="text-align: center; font-size: 14px">Código</th>
                        <th style="text-align: center; font-size: 14px">Solicitante</th>
                        <th style="text-align: center; font-size: 14px">Prioridad</th>
                        <th style="text-align: center; font-size: 14px">Tipo</th>
                        <th style="text-align: center; font-size: 14px">Estado</th>
                        <th style="text-align: center; font-size: 14px">Asunto</th>
                        <th style="text-align: center; font-size: 14px">Creado</th>
                        <th style="text-align: center; font-size: 14px">Encargado</th>
                        <th style="text-align: center; font-size: 14px">Chat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($estados as $estado): ?>
                        <?php
                        $resultado = $datos[$estado] ?? null;
                        if ($resultado && $resultado->num_rows > 0) {
                            $resultado->data_seek(0);
                            while ($row = $resultado->fetch_assoc()):
                                $prioridadLower = strtolower($row['prioridad'] ?? '');
                                $clasePrioridad = 'badge-prioridad-bajo';
                                if ($prioridadLower === 'medio' || $prioridadLower === 'media') {
                                    $clasePrioridad = 'badge-prioridad-medio';
                                } elseif ($prioridadLower === 'alto' || $prioridadLower === 'alta') {
                                    $clasePrioridad = 'badge-prioridad-alto';
                                } elseif ($prioridadLower === 'urgente') {
                                    $clasePrioridad = 'badge-prioridad-urgente';
                                }

                                $estadoUpper = strtoupper($estado);
                                $claseEstado = 'badge-estado-pendiente';
                                if ($estadoUpper === 'EN REVISION') {
                                    $claseEstado = 'badge-estado-enrevision';
                                } elseif ($estadoUpper === 'SOLUCIONADO') {
                                    $claseEstado = 'badge-estado-solucionado';
                                }
                        ?>
                                <tr>
                                    <td class="text-center" style="font-size: 13px">
                                        <?php echo htmlspecialchars($row['codigo_error'], ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td class="text-center" style="font-size: 13px">
                                        <?php echo htmlspecialchars(($row['nombre_solicitante'] ?? '') . ' ' . ($row['apellido_solicitante'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td class="text-center" style="font-size: 13px">
                                        <span class="badge-prioridad <?php echo $clasePrioridad; ?>">
                                            <?php echo htmlspecialchars($row['prioridad'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td class="text-center" style="font-size: 13px">
                                        <?php echo htmlspecialchars($row['tipo_error'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td class="text-center" style="font-size: 13px">
                                        <span class="badge-estado <?php echo $claseEstado; ?>">
                                            <?php echo htmlspecialchars($estadoUpper, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td class="text-center" style="font-size: 13px">
                                        <button
                                            type="button"
                                            class="btn-ver-asunto"
                                            data-asunto="<?php echo htmlspecialchars($row['asunto'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-codigo="<?php echo htmlspecialchars($row['codigo_error'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalAsunto">
                                            Ver
                                        </button>
                                    </td>
                                    <td class="text-center" style="font-size: 13px">
                                        <?php echo htmlspecialchars($row['fecha_hora_creacion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td class="text-center" style="font-size: 13px">
                                        <?php
                                        $nombreSoporte = trim(($row['nombre_soporte'] ?? '') . ' ' . ($row['apellido_soporte'] ?? ''));
                                        if ($nombreSoporte !== '') {
                                            echo htmlspecialchars($nombreSoporte, ENT_QUOTES, 'UTF-8');
                                        } else {
                                            echo '<span class="text-muted" style="font-size:0.8rem;">Sin asignar</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-center" style="font-size: 13px">
                                        <a href="<?php echo htmlspecialchars($chatSoportePageBase . '&codigo=' . urlencode($row['codigo_error']), ENT_QUOTES, 'UTF-8'); ?>"
                                            class="btn btn-sm btn-chat-notif-soporte"
                                            data-codigo="<?php echo htmlspecialchars($row['codigo_error'], ENT_QUOTES, 'UTF-8'); ?>"
                                            style="border-radius:999px; border:1px solid #022F55; color:#022F55; 
                                                            font-size:0.8rem; position:relative;margin-left: 4%; width: 70px">
                                            Chat
                                            <span class="notif-dot-soporte" style="
                                                    display:none;
                                                    position:absolute;
                                                    top:-2px;
                                                    margin-left: 16%;
                                                    width:9px;
                                                    height:9px;
                                                    border-radius:50%;
                                                    background-color:#dc3545;
                                                    box-shadow:0 0 0 2px #fff;
                                                "></span>
                                        </a>
                                    </td>
                                </tr>
                        <?php endwhile;
                        }
                        ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- </div> -->
</div>

<div class="modal fade" id="modalAsunto" tabindex="-1" aria-labelledby="modalAsuntoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 900px;">
        <div class="modal-content" style="min-height: 260px; max-height: 500px; display: flex; flex-direction: column;">
            <div style="border: none; box-shadow: none !important;">
                <button
                    type="button"
                    class="btn-close bg-white"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"
                    style="position: absolute; top: 12px; right: 12px; z-index: 10; font-size: 1.2rem;">
                </button>
                <div class="modal-header text-white" style="background-color: #002F55;">
                    <h5 class="modal-title text-center" id="modalAsuntoLabel"></h5>
                </div>
            </div>
            <div class="h-100 p-2 d-flex justify-content-center">
                <div class="modal-body" style="overflow-y: auto;">
                    <h6 class="fw-bold">Asunto del ticket:</h6>
                    <hr>
                    <p id="modalAsuntoTexto" class="mb-0"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery y DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#tablaTickets').DataTable({
            language: window.neivaDataTablesLanguage ? window.neivaDataTablesLanguage() : {},
            autoWidth: false,
            responsive: true
        });
        inicializarNotificacionesChatSoporte();
    });

    function inicializarNotificacionesChatSoporte() {
        var botones = document.querySelectorAll('.btn-chat-notif-soporte');
        if (botones.length === 0) return;

        var codigos = [];
        botones.forEach(function(btn) {
            var c = btn.getAttribute('data-codigo');
            if (c) codigos.push(c);
        });

        function actualizarNotificacionesSoporte() {
            if (codigos.length === 0) return;

            var url = <?php echo json_encode($chatNotificacionesSoporteUrl); ?> +
                '?codigos=' + encodeURIComponent(codigos.join(','));

            fetch(url)
                .then(function(res) {
                    return res.json();
                })
                .then(function(data) {
                    botones.forEach(function(btn) {
                        var cod = btn.getAttribute('data-codigo');
                        var dot = btn.querySelector('.notif-dot-soporte');
                        if (!dot) return;

                        if (data[cod] && data[cod].tiene_nuevo) {
                            dot.style.display = 'inline-block';
                        } else {
                            dot.style.display = 'none';
                        }
                    });
                })
                .catch(function(err) {
                    console.error('Error notificaciones soporte:', err);
                });
        }

        actualizarNotificacionesSoporte();
        setInterval(actualizarNotificacionesSoporte, 1000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        var modalAsuntoTexto = document.getElementById('modalAsuntoTexto');
        var modalAsuntoLabel = document.getElementById('modalAsuntoLabel');

        var boton = document.querySelectorAll('.btn-ver-asunto');
        boton.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var asunto = this.getAttribute('data-asunto');
                var codigo = this.getAttribute('data-codigo');

                modalAsuntoLabel.textContent = (codigo ? '' + codigo : '');
                modalAsuntoTexto.textContent = asunto;
            });
        });
    });
</script>
