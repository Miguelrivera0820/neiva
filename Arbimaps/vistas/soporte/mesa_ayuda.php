<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 2) . '/config/permisos.php';
require_once dirname(__DIR__, 3) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('GET');
neiva_require_permission('soporte.gestion_tickets', $PERMISOS);

$id_usuario_sesion = $_SESSION['id_usuario'] ?? null;
$id_usuario_filtro = $id_usuario_sesion ? (int)$id_usuario_sesion : 0;
$cambiarEstadoAction = neiva_app_url('Arbimaps/vistas/soporte/acciones/cambiar_estado.php');
$asignarErrorPageBase = neiva_app_url('Arbimaps/index.php?page=soporte/asignar_error');

$estados = ['PENDIENTE', 'EN REVISION', 'SOLUCIONADO'];
$datos = [];
$conteos = [];

foreach ($estados as $estado) {
    if ($id_usuario_filtro > 0) {
        $condicionUsuario = " AND id_usuario = {$id_usuario_filtro}";
    } else {
        $condicionUsuario = " AND 1 = 0";
    }

    $query = "SELECT 
        codigo_error,
        asunto,
        descripcion,
        prioridad,
        tipo_error,
        fecha_hora_creacion,
        nombre_solicitante,
        apellido_solicitante,
        correo_solicitante,
        celular_solicitante,
        imagen_ruta
    FROM solicitud_soporte
    WHERE estado_error = '$estado' $condicionUsuario
    ORDER BY 
        FIELD(prioridad, 'bajo', 'medio', 'alto', 'urgente') DESC,
        fecha_hora_creacion DESC";

    $result = $mysqli->query($query);
    $datos[$estado] = $result;
    $conteos[$estado] = $result->num_rows;
}
?>


<style>
    :root {
        --primary-color: #0d6efd;
        --primary-light: #e7f1ff;
        --secondary-color: #1a202c;
        --success-color: #198754;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --border-radius: 0.5rem;
    }

    /* Estilos de pestañas personalizadas */
    .nav-tabs-custom {
        border-bottom: 2px solid #dee2e6;
        margin-bottom: 0.8rem;
    }

    .nav-tabs-custom .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #6c757d;
        font-weight: 600;
        padding: 1rem 1.5rem;
        transition: all 0.3s ease;
        position: relative;
    }

    .nav-tabs-custom .nav-link:hover {
        color: var(--primary-color);
        background-color: var(--primary-light);
        border-radius: var(--border-radius) var(--border-radius) 0 0;
    }

    .nav-tabs-custom .nav-link.active {
        color: #002F55;
        background-color: white;
        border-bottom-color: #002F55;
    }

    .badge-count {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        margin-left: 0.5rem;
        border-radius: 10px;
    }

    /* Tarjetas de ticket mejoradas */
    .ticket-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-left: 4px solid var(--primary-color);
        border-radius: var(--border-radius);
        padding: 1.2rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .ticket-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        background-color: #48535d12;
        /* transform: translateY(-2px); */
        /* border-left-width: 6px; */
    }

    .ticket-card.priority-alto {
        border-left-color: var(--danger-color);
    }

    .ticket-card.priority-medio {
        border-left-color: var(--warning-color);
    }

    .ticket-card.priority-bajo {
        border-left-color: var(--success-color);
    }

    .ticket-card.priority-urgente {
        border-left-color: #b91c1c;
        /* rojo fuerte */
    }

    .ticket-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 0.75rem;
    }

    .ticket-codigo {
        font-weight: 700;
        color: var(--secondary-color);
        font-size: 0.9rem;
    }

    .ticket-asunto {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
        margin: 0.5rem 0;
    }

    .ticket-meta {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        font-size: 0.85rem;
        color: #6c757d;
        margin-top: 0.75rem;
    }

    .ticket-meta i {
        margin-right: 0.25rem;
    }

    /* Badges de prioridad */
    .badge-priority {
        font-size: 0.75rem;
        padding: 0.35rem 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .badge-alto {
        background-color: #dc354520;
        color: #dc3545;
    }

    .badge-medio {
        background-color: #ffc10720;
        color: #f57c00;
    }

    .badge-bajo {
        background-color: #19875420;
        color: #198754;
    }

    .badge-urgente {
        background-color: #b91c1c20;
        color: #b91c1c;
    }

    /* Panel de detalles expandible */
    .ticket-details {
        background: #f8f9fa;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        margin-top: 1rem;
    }

    .detail-row {
        display: flex;
        padding: 0.5rem 0;
        border-bottom: 1px solid #e9ecef;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        font-weight: 600;
        color: var(--secondary-color);
        min-width: 140px;
    }

    .detail-value {
        color: #495057;
        flex: 1;
    }

    /* Botones de acción */
    .action-buttons {
        display: flex;
        gap: 0.75rem;
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid #dee2e6;
    }

    .btn-action {
        flex: 1;
        padding: 0.6rem 1rem;
        border: none;
        border-radius: var(--border-radius);
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        text-decoration: none;
        color: inherit;
        width: 100%;
        box-shadow: 0 1px 4px rgba(2, 6, 23, 0.06);
    }

    .btn-view-image {
        background-color: var(--primary-light);
        color: var(--primary-color);
    }

    .btn-view-image:hover {
        background-color: var(--primary-color);
        color: white;
    }

    .btn-change-status {
        background-color: #19875420;
        color: var(--success-color);
    }

    .btn-change-status:hover {
        background-color: var(--success-color);
        color: white;
        box-shadow: 0 6px 18px rgba(25, 135, 84, 0.12);
    }

    /* Filtros avanzados */
    .filters-container {
        background: white;
        padding: 1rem;
        border-radius: var(--border-radius);
        margin-bottom: 1.5rem;
        border: 1px solid #e0e0e0;
    }

    .filter-group {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: end;
    }

    .filter-item {
        flex: 1;
        min-width: 200px;
    }

    .filter-item label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--secondary-color);
        margin-bottom: 0.25rem;
        display: block;
    }

    /* Vista de estadísticas */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.2rem;
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
        font-size: 1.5rem;
        font-weight: 700;
        color: #002F55;
    }

    .stat-label {
        font-size: 0.85rem;
        color: #6c757d;
        text-transform: uppercase;
        font-weight: 600;
    }

    /* Estado vacío mejorado */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    /* Modal mejorado */
    .modal-imagen .modal-dialog {
        max-width: 1000px;
    }

    .modal-imagen .modal-content {
        border-radius: var(--border-radius);
    }

    .modal-imagen .modal-body {
        padding: 2rem;
        min-height: 500px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
    }

    .modal-imagen img {
        max-width: 100%;
        max-height: 70vh;
        object-fit: contain;
        border-radius: var(--border-radius);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Toggle vista */
    .view-toggle {
        display: flex;
        gap: 0.5rem;
        background: #f8f9fa;
        padding: 0.25rem;
        border-radius: var(--border-radius);
        width: fit-content;
    }

    .view-toggle button {
        padding: 0.5rem 1rem;
        border: none;
        background: transparent;
        border-radius: calc(var(--border-radius) - 0.25rem);
        color: #6c757d;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .view-toggle button.active {
        background: white;
        color: var(--primary-color);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .ticket-meta {
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-group {
            flex-direction: column;
        }

        .filter-item {
            width: 100%;
        }
    }

    .positive {
        color: #166534;
    }

    .negative {
        color: #631704ff;
    }
</style>

<div class="container-fluid mb-5 p-3">
    <!-- Header -->
    
        <div class="d-flex align-items-center justify-content-center gap-3 my-4">
            <!-- <div class="d-flex align-items-center justify-content-center rounded-circle"
                style="width: 70px; height: 70px; background: var(--primary-light);">
                <i class="bi bi-life-preserver" style="color:var(--primary-color); font-size: 1.6rem;"></i>
            </div> -->
            <div class="text-center">
                <span class="text-uppercase small text-muted fw-semibold">Mesa de ayuda</span>
                <h3 class="mb-0 fw-bold" style="color: #002F55;">Sistema de Soporte Técnico</h3>
                <small class="text-muted">Gestión centralizada de reportes y solicitudes</small>
            </div>
        </div>
    

    <!-- Estadísticas -->
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
                // Calcular porcentajes respecto a trámites radicados
                if ($total_rad > 0) {
                    // Tickets pendientes
                    $porcentaje_pendientes = round(($conteos['PENDIENTE'] / array_sum($conteos)) * 100);
                    $icono_pendientes = $porcentaje_pendientes >= 50 ? '<i class="bi bi-caret-up-fill text-success"></i>' : '<i class="bi bi-caret-down-fill text-danger"></i>';
                    $clase_pendientes = $porcentaje_pendientes >= 50 ? 'positive' : 'negative';

                    // Tickets en revisión
                    $porcentaje_revision = round(($conteos['EN REVISION'] / array_sum($conteos)) * 100);
                    $icono_revision = $porcentaje_revision >= 50 ? '<i class="bi bi-caret-up-fill text-success"></i>' : '<i class="bi bi-caret-down-fill text-danger"></i>';
                    $clase_revision = $porcentaje_revision >= 50 ? 'positive' : 'negative';

                    // Tickets solucionados
                    $porcentaje_solucionado = round(($conteos['SOLUCIONADO'] / array_sum($conteos)) * 100);
                    $icono_solucionado = $porcentaje_solucionado >= 50 ? '<i class="bi bi-caret-up-fill text-success"></i>' : '<i class="bi bi-caret-down-fill text-danger"></i>';
                    $clase_solucionado = $porcentaje_solucionado >= 50 ? 'positive' : 'negative';
                } else {
                    // Si no hay trámites radicados, todos los porcentajes son 0
                    $porcentaje_asignados = $porcentaje_culminados = $porcentaje_vencidos = $porcentaje_cartas = $porcentaje_cert = 0;
                    $icono_asignados = $icono_culminados = $icono_vencidos = $icono_cartas = $icono_cert = '<i class="bi bi-caret-down-fill text-danger"></i>';
                    $clase_asignados = $clase_culminados = $clase_vencidos = $clase_cartas = $clase_cert = 'negative';
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

    <div class="filters-container ">
        <div class="filter-group">
            <div class="filter-item" style="flex: 2;">
                <label>
                    <i class="bi bi-search me-1"></i> Búsqueda General
                </label>
                <input type="text" class="form-control" id="searchGlobal"
                    placeholder="Código, asunto, solicitante...">
            </div>
            <div class="filter-item">
                <label>
                    <i class="bi bi-flag me-1"></i> Prioridad
                </label>
                <select class="form-select" id="filterPriority">
                    <option value="">Todas</option>
                    <option value="bajo">Baja</option>
                    <option value="medio">Media</option>
                    <option value="alto">Alta</option>
                    <option value="urgente">Urgente</option>
                </select>
            </div>
            <div class="filter-item" style="flex: 0.5;">
                <button class="btn btn-outline-secondary w-100" id="clearFilters">
                    <i class="bi bi-x-circle me-1"></i> Limpiar
                </button>
            </div>
        </div>
    </div>
    <ul class="nav nav-tabs nav-tabs-custom" id="ticketTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pendiente-tab" data-bs-toggle="tab"
                data-bs-target="#pendiente" type="button" role="tab">
                <i class="bi bi-hourglass-split me-2"></i>
                Pendientes
                <span class="badge badge-count bg-danger"><?php echo $conteos['PENDIENTE']; ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="revision-tab" data-bs-toggle="tab"
                data-bs-target="#revision" type="button" role="tab">
                <i class="bi bi-eye me-2"></i>
                En Revisión
                <span class="badge badge-count bg-warning"><?php echo $conteos['EN REVISION']; ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="solucionado-tab" data-bs-toggle="tab"
                data-bs-target="#solucionado" type="button" role="tab">
                <i class="bi bi-check-circle me-2"></i>
                Solucionados
                <span class="badge badge-count bg-success"><?php echo $conteos['SOLUCIONADO']; ?></span>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="ticketTabsContent">
        <?php
        $estados_config = [
            'PENDIENTE' => [
                'id' => 'pendiente',
                'active' => 'show active',
                'nuevo_estado' => 'EN REVISION',
                'btn_text' => 'Marcar en Revisión',
                'btn_icon' => 'bi-arrow-right-circle',
                'asignacion_error' => 'Soporte Técnico',
                'btn_asignacion' => 'Asignar error'
            ],
            'EN REVISION' => [
                'id' => 'revision',
                'active' => '',
                'nuevo_estado' => 'SOLUCIONADO',
                'btn_text' => 'Marcar Solucionado',
                'btn_icon' => 'bi-check-circle',
                'asignacion_error' => 'Soporte Técnico',
                'btn_asignacion' => 'Asignar error'

            ],
            'SOLUCIONADO' => [
                'id' => 'solucionado',
                'active' => '',
                'nuevo_estado' => null,
                'btn_text' => null,
                'btn_icon' => null,
                'asignacion_error' => null,
                'btn_asignacion' => null
            ]
        ];
        foreach ($estados_config as $estado => $config):
            $resultado = $datos[$estado];
            $resultado->data_seek(0);
        ?>
            <div class="tab-pane fade <?php echo $config['active']; ?>"
                id="<?php echo $config['id']; ?>"
                role="tabpanel">
                <?php if ($resultado->num_rows === 0): ?>
                    <div class="empty-state">
                        <i class="bi <?php echo $estado === 'PENDIENTE' ? 'bi-check2-circle' : ($estado === 'EN REVISION' ? 'bi-hourglass-split' : 'bi-check-all'); ?>"></i>
                        <h5>No hay tickets en estado "<?php echo $estado; ?>"</h5>
                        <p class="text-muted">Los nuevos tickets aparecerán aquí automáticamente.</p>
                    </div>
                <?php else: ?>
                    <div class="tickets-container ">
                        <?php while ($row = $resultado->fetch_assoc()):
                            $codigo = htmlspecialchars($row['codigo_error'], ENT_QUOTES, 'UTF-8');
                            $prioridad_class = 'priority-' . strtolower($row['prioridad'] ?? 'baja');
                            $badge_class = 'badge-' . strtolower($row['prioridad'] ?? 'baja');
                                $imgSrc = !empty($row['imagen_ruta'])
                                    ? neiva_app_url('Arbimaps/vistas/soporte/' . ltrim($row['imagen_ruta'], '/'))
                                    : null;
                        ?>
                            <div class="ticket-card <?php echo $prioridad_class; ?>"
                                data-codigo="<?php echo $codigo; ?>"
                                data-prioridad="<?php echo htmlspecialchars($row['prioridad'] ?? ''); ?>"
                                data-tipo="<?php echo htmlspecialchars($row['tipo_error'] ?? ''); ?>">

                                <div class="ticket-header">
                                    <div>
                                        <div class="ticket-codigo">#<?php echo $codigo; ?></div>
                                        <h5 class="ticket-asunto">
                                            <?php echo htmlspecialchars($row['asunto'], ENT_QUOTES, 'UTF-8'); ?>
                                        </h5>
                                    </div>
                                    <div>
                                        <span class="badge badge-priority <?php echo $badge_class; ?>">
                                            <?php echo htmlspecialchars($row['prioridad'] ?? 'N/A'); ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="ticket-meta">
                                    <span>
                                        <i class="bi bi-person"></i>
                                        <?php echo htmlspecialchars($row['nombre_solicitante'] . ' ' . $row['apellido_solicitante']); ?>
                                    </span>
                                    <span>
                                        <i class="bi bi-tag"></i>
                                        <?php echo htmlspecialchars($row['tipo_error'] ?? 'N/A'); ?>
                                    </span>
                                    <span>
                                        <i class="bi bi-calendar"></i>
                                        <?php echo htmlspecialchars($row['fecha_hora_creacion'] ?? 'N/A'); ?>
                                    </span>
                                    <?php if ($imgSrc): ?>
                                        <span>
                                            <i class="bi bi-paperclip"></i>
                                            Con imagen adjunta
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="ticket-details d-none">
                                    <div class="detail-row">
                                        <span class="detail-label">
                                            <i class="bi bi-person me-2"></i>Solicitante:
                                        </span>
                                        <span class="detail-value">
                                            <?php echo htmlspecialchars($row['nombre_solicitante'] . ' ' . $row['apellido_solicitante']); ?>
                                        </span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">
                                            <i class="bi bi-envelope me-2"></i>Correo:
                                        </span>
                                        <span class="detail-value">
                                            <?php echo htmlspecialchars($row['correo_solicitante'] ?? 'N/A'); ?>
                                        </span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">
                                            <i class="bi bi-phone me-2"></i>Teléfono:
                                        </span>
                                        <span class="detail-value">
                                            <?php echo htmlspecialchars($row['celular_solicitante'] ?? 'N/A'); ?>
                                        </span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">
                                            <i class="bi bi-card-text me-2"></i>Descripción:
                                        </span>
                                        <span class="detail-value">
                                            <?php echo nl2br(htmlspecialchars($row['descripcion'] ?? 'Sin descripción')); ?>
                                        </span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">
                                            <i class="bi bi-card-text me-2"></i>Prioridad:
                                        </span>
                                        <span class="detail-value">
                                            <?php echo nl2br(htmlspecialchars($row['prioridad'] ?? 'Sin prioridad')); ?>
                                        </span>
                                    </div>

                                    <div class="action-buttons">
                                        <?php if ($imgSrc): ?>
                                            <button type="button" class="btn-action btn-view-image"
                                                data-img="<?php echo htmlspecialchars($imgSrc); ?>"
                                                data-codigo="<?php echo $codigo; ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalImagenSoporte">
                                                <i class="bi bi-image me-2"></i> Ver Imagen
                                            </button>
                                        <?php endif; ?>

                                        <?php if ($config['nuevo_estado']): ?>
                                            <form method="POST"
                                                action="<?php echo htmlspecialchars($cambiarEstadoAction, ENT_QUOTES, 'UTF-8'); ?>"
                                                class="flex-fill">
                                                <?php echo neiva_csrf_input('global'); ?>
                                                <input type="hidden" name="codigo_error" value="<?php echo $codigo; ?>">
                                                <input type="hidden" name="nuevo_estado" value="<?php echo $config['nuevo_estado']; ?>">
                                                <button type="submit" class="btn-action btn-change-status w-100">
                                                    <i class="bi <?php echo $config['btn_icon']; ?> me-2"></i>
                                                    <?php echo $config['btn_text']; ?>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if (!empty($config['asignacion_error'])): ?>
                                            <?php $codigo_for_url = rawurlencode($codigo); ?>
                                            <a href="<?php echo htmlspecialchars($asignarErrorPageBase . '&codigo=' . $codigo_for_url, ENT_QUOTES, 'UTF-8'); ?>"
                                                class="btn-action btn-change-status w-100">
                                                <i class="bi <?php echo $config['btn_icon']; ?> me-2"></i>
                                                <?php echo htmlspecialchars($config['btn_asignacion'], ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="modal modal-imagen fade" id="modalImagenSoporte" tabindex="-1" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalImagenSoporteLabel">Imagen del Reporte</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <img src="" alt="Imagen del error" id="modalImagenSoporteImg">
            </div>
            <div class="modal-footer">
                <div class="me-auto">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="zoomOutBtn">
                        <i class="bi bi-zoom-out"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="zoomInBtn">
                        <i class="bi bi-zoom-in"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="zoomResetBtn">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
                <a href="#" class="btn btn-primary btn-sm" id="modalImagenSoporteLink"
                    target="_blank" rel="noopener">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Abrir en pestaña nueva
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.ticket-card').forEach(card => {
            card.addEventListener('click', function(e) {
                if (e.target.closest('.action-buttons') || e.target.closest('button')) {
                    return;
                }
                const details = this.querySelector('.ticket-details');
                if (details) {
                    details.classList.toggle('d-none');
                }
            });
        });

        const searchInput = document.getElementById('searchGlobal');
        const filterPriority = document.getElementById('filterPriority');

        function filterTickets() {
            const searchTerm = (searchInput.value || '').toLowerCase();
            const priority = (filterPriority.value || '').toLowerCase();

            document.querySelectorAll('.ticket-card').forEach(card => {
                const codigo = (card.dataset.codigo || '').toLowerCase();
                const asunto = card.querySelector('.ticket-asunto').textContent.toLowerCase();
                const cardPriority = (card.dataset.prioridad || '').toLowerCase();
                const solicitante = card.querySelector('.ticket-meta span:first-child').textContent.toLowerCase();

                const matchSearch = !searchTerm ||
                    codigo.includes(searchTerm) ||
                    asunto.includes(searchTerm) ||
                    solicitante.includes(searchTerm);

                const matchPriority = !priority || cardPriority === priority;

                if (matchSearch && matchPriority) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        searchInput.addEventListener('input', filterTickets);
        filterPriority.addEventListener('change', filterTickets);

        document.getElementById('clearFilters').addEventListener('click', function() {
            searchInput.value = '';
            filterPriority.value = '';
            filterTickets();
        });


        const imgModal = document.getElementById('modalImagenSoporteImg');
        const linkModal = document.getElementById('modalImagenSoporteLink');
        const titleModal = document.getElementById('modalImagenSoporteLabel');
        let zoomLevel = 1;

        document.querySelectorAll('.btn-view-image').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const src = this.getAttribute('data-img');
                const codigo = this.getAttribute('data-codigo');

                imgModal.src = src;
                imgModal.alt = 'Imagen del error #' + codigo;
                linkModal.href = src;
                titleModal.textContent = 'Imagen del Reporte #' + codigo;
                zoomLevel = 1;
                imgModal.style.transform = 'scale(1)';
            });
        });

        document.getElementById('zoomInBtn').addEventListener('click', function() {
            zoomLevel = Math.min(3, zoomLevel + 0.2);
            imgModal.style.transform = `scale(${zoomLevel})`;
        });

        document.getElementById('zoomOutBtn').addEventListener('click', function() {
            zoomLevel = Math.max(0.5, zoomLevel - 0.2);
            imgModal.style.transform = `scale(${zoomLevel})`;
        });

        document.getElementById('zoomResetBtn').addEventListener('click', function() {
            zoomLevel = 1;
            imgModal.style.transform = 'scale(1)';
        });

        document.getElementById('modalImagenSoporte').addEventListener('hidden.bs.modal', function() {
            zoomLevel = 1;
            imgModal.style.transform = 'scale(1)';
        });

        const style = document.createElement('style');
        style.textContent = `
        .ticket-details {
            animation: slideDown 0.3s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    `;
        document.head.appendChild(style);
    });
</script>
