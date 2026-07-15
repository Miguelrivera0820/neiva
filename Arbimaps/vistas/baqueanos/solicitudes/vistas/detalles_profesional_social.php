<?php
$where = "";

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../index.php");
    exit();
}

$idUsuario = (int) $_SESSION['id_usuario'];

$sql = "SELECT rol_usuario, rol_usuario_dos, nombre_usuario
        FROM usuarios_cons
        WHERE id_usuario = $idUsuario";
$resultado = $mysqli->query($sql);

if (!$resultado || $resultado->num_rows === 0) {
    header("Location: ../../acceso_denegado.php");
    exit();
}

$datosUsuario = $resultado->fetch_assoc();

$rolUsuario     = $datosUsuario['rol_usuario'] ?? '';
$rolUsuarioDos  = $datosUsuario['rol_usuario_dos'] ?? '';

$rolesPermitidos = array("administrador", "director_proyectos", "soporte", "social");

if (!in_array($rolUsuario, $rolesPermitidos) && !in_array($rolUsuarioDos, $rolesPermitidos)) {
    header("Location: ../../acceso_denegado.php");
    exit();
}


$sqlCountDevueltas = "SELECT COUNT(*) AS total
    FROM copia_devolucion_baqueanos AS c
    JOIN solicitud_baqueanos AS s ON c.copia_id = s.id
    WHERE s.sb_estado_cuenta != 'RADICADO'";
$resultDevueltas = $mysqli->query($sqlCountDevueltas);
$rowDevueltas = $resultDevueltas ? $resultDevueltas->fetch_assoc() : ['total' => 0];
$totalRadicadas = (int)($rowDevueltas['total'] ?? 0);

$sqlCount = "
    SELECT COUNT(*) AS total
    FROM solicitud_baqueanos
    WHERE
        UPPER(TRIM(REPLACE(REPLACE(COALESCE(sb_estado_gerencia,''), '\r',''), '\n',''))) = 'APROBADO'
        AND
        UPPER(TRIM(REPLACE(REPLACE(COALESCE(sb_estado_cuenta,''), '\r',''), '\n',''))) NOT IN ('RADICADO','APROBADO')
        AND
        UPPER(TRIM(REPLACE(REPLACE(COALESCE(sb_estado_dias,''), '\r',''), '\n',''))) <> 'DIAS_AGREGADOS'
";

$result = $mysqli->query($sqlCount);
$row = $result->fetch_assoc();
$totalCargado = (int)($row['total'] ?? 0);



$sqlCount = "SELECT COUNT(*) AS total 
            FROM solicitud_baqueanos 
            WHERE (
                    sb_estado_gerencia = 'DEVUELTO' OR 
                    sb_estado_operaciones = 'DEVUELTO'
                )
            AND sb_tipo_motivo IS NOT NULL 
            AND sb_tipo_motivo != ''";

$result = $mysqli->query($sqlCount);
$row = $result->fetch_assoc();
$totalMotivos = $row['total'];

$sqlResumen = "SELECT 
                    SUM(CASE WHEN sb_estado_profesional = 'APROBADO' THEN 1 ELSE 0 END) AS total_aprobadas,
                    SUM(CASE WHEN sb_estado_profesional = 'PENDIENTE' THEN 1 ELSE 0 END) AS total_pendientes
                FROM solicitud_baqueanos
                WHERE sb_estado_lider = 'APROBADO'
                    AND sb_estado_profesional IN ('APROBADO', 'PENDIENTE')
                    AND sb_estado_operaciones != 'DEVUELTO'
                    AND sb_estado_gerencia != 'DEVUELTO'
            ";

$resResumen = $mysqli->query($sqlResumen);

$totalAprobadas = 0;
$totalPendientes = 0;

if ($resResumen && $resResumen->num_rows > 0) {
    $rowResumen = $resResumen->fetch_assoc();
    $totalAprobadas = (int)($rowResumen['total_aprobadas'] ?? 0);
    $totalPendientes = (int)($rowResumen['total_pendientes'] ?? 0);
}
?>
<style>
    :root {
        --color-primario: #022F55;
        --color-primario-suave: #f1f5f9;
        --color-borde-suave: #e5e7eb;
        --color-texto-muted: #6b7280;
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

    .col-toggle-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.3rem 0.8rem;
        border-radius: 9px;
        background-color: #015599ff;
        color: #fff;
        font-weight: 500;
        border: 1px solid #015599ff;
    }

    .dataTables_length {
        margin-bottom: 0.5em;
    }

    .card-header {
        background: linear-gradient(355deg, #0a579bff, #012949ff);
    }

    .table-card {
        border-collapse: separate !important;
        border-spacing: 0 8px;
    }

    .table-card tbody tr {
        background: transparent;
    }

    .table-card tbody td {
        background-color: #ffffff;
        padding: 10px 10px;
        vertical-align: middle;
        border-top: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
        transition: background-color .15s ease, color .15s ease;
        font-size: 13px;
    }

    .table-card tbody tr td:first-child {
        border-left: 2px solid #002f55;
        border-radius: 8px 0 0 8px;
    }

    .table-card tbody tr td:last-child {
        border-right: 1px solid #e5e7eb;
        border-radius: 0 8px 8px 0;
    }

    .table-card tbody tr {
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
        transition: transform .15s ease, box-shadow .15s ease;
        border: none !important;
        border-radius: 8px;
    }

    .table-card tbody tr:hover td {
        background-color: #17619d !important;
        color: #ffffff !important;
        border-top-color: rgba(255, 255, 255, .18) !important;
        border-bottom-color: rgba(255, 255, 255, .18) !important;
    }

    .table-card tbody tr:hover a,
    .table-card tbody tr:hover .btn-link {
        color: #ffffff !important;
    }

    .table-card tbody tr:hover i {
        color: #ffffff !important;
    }

    .table-card tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px #002f5570;
    }

    .table-card thead th {
        border: none !important;
        color: #050505ff;
        font-weight: 600;
    }

    .table-card-2 {
        border-collapse: separate !important;
        border-spacing: 0 10px;
    }

    .table-card-2 tbody tr {
        background: transparent;
    }

    .table-card-2 tbody td {
        background-color: #ffffff;
        padding: 14px 5px;
        vertical-align: middle;
        border-top: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
    }

    .table-card-2 tbody tr td:first-child {
        border-left: 3px solid #429047ff;
        border-radius: 8px 0 0 8px;
    }

    .table-card-2 tbody tr td:last-child {
        border-right: 1px solid #e5e7eb;
        border-radius: 0 8px 8px 0;
    }

    .table-card-2 tbody tr {
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
        transition: transform .15s ease, box-shadow .15s ease;
        border: none !important;
        border-radius: 8px;
    }

    .table-card-2 tbody tr:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 12px #002f5570;
    }

    .table-card-2 thead th {
        border: none !important;
        color: #050505ff;
        font-weight: 600;
    }

    .apellidos {
        text-align: center;
        font-size: 12px;
        color: #414447ff;
    }

    .nombres {
        text-align: center !important;
        font-size: 14px;
        font-weight: 500;
    }

    .table-card-2 tbody tr.estado-pendiente td:first-child {
        border-left: 3px solid #002f55 !important;
    }

    .table-card-2 tbody tr.estado-aprobado td:first-child {
        border-left: 3px solid #28a745 !important;
    }

    .table-card-2 tbody tr.estado-rechazado td:first-child {
        border-left: 4px solid #dc3545 !important;
    }

    .table-card-2 tbody tr.estado-desconocido td:first-child {
        border-left: 3px solid #6c757d !important;
    }

    table.table.table-card {
        border-collapse: separate !important;
        border-spacing: 0 12px !important;
    }

    table.table.table-card td,
    table.table.table-card th {
        border-top: 0 !important;
    }

    td.nombres {
        text-align: center;
        vertical-align: middle;
        font-size: 14px;
        width: 5%;
    }

    .user-cell {
        display: grid;
        grid-template-columns: 40px 200px;
        align-items: center;
        justify-content: center;
        column-gap: 20px;
    }

    .user-photo {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid #002F55;
    }

    .user-info {
        width: 200px;
        text-align: left;
        line-height: 1.1;
    }

    .user-name,
    .user-lastname {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .user-name {
        font-weight: 600;
    }

    .user-lastname {
        opacity: .85;
        font-size: 13px;
    }

    .rad-link {
        text-decoration: none !important;
        color: #002F55;
        font-weight: 600;
    }

    .rad-link:hover {
        text-decoration: none !important;
        color: #002F55;
    }

    /* botones adicionales de las opciones */
    .stepper {
        background: rgb(225, 242, 255);
        border: 1px solid #83c3ff;
        border-radius: 14px;
        padding: 6px;
        overflow: hidden;
        box-shadow: 0 10px 22px rgba(0, 47, 85, .10);
    }

    .step-btn {
        position: relative;
        border-radius: 0;
        border: 0 !important;
        padding: .58rem 1.05rem;
        font-weight: 700;
        font-size: 13px;
        letter-spacing: .2px;
        color: #002F55 !important;
        background: #d0ecff !important;
        transition: transform .18s ease, filter .18s ease, box-shadow .18s ease, background .18s ease;
    }

    .step-btn:not(:last-child)::after {
        content: "";
        position: absolute;
        top: 0;
        right: -18px;
        width: 0;
        height: 0;
        border-top: 22px solid transparent;
        border-bottom: 22px solid transparent;
        border-left: 18px solid #ffffff;
        z-index: 2;
        transition: border-left-color .18s ease;
    }

    .step-btn:not(:first-child) {
        padding-left: 1.35rem;
    }

    .step-btn:hover {
        transform: translateY(-1px);
        filter: brightness(1.02);
        box-shadow: 0 8px 18px rgba(0, 47, 85, .10);
    }



    .step-btn>* {
        position: relative;
        z-index: 3;
    }



    .step-btn .badge-custom2 {
        background: #d0ecff;
        color: #1f7a3a;
        border: 1px solid rgba(31, 122, 58, .18);
    }

    .step-btn.active .badge-custom,
    .step-btn.active .badge-custom2 {
        background: rgba(255, 255, 255, .20);
        color: #fff;
        border-color: rgba(255, 255, 255, .28);
    }

    .stepper-wrap {
        max-width: 100%;
    }

    .stepper.flex-wrap {
        width: 100%;
    }

    @media (max-width: 767.98px) {
        .stepper {
            padding: 8px;
        }

        .step-btn {
            border-radius: 12px !important;
        }

        .step-btn:not(:last-child)::after {
            display: none;
        }

        .step-btn:not(:first-child) {
            padding-left: 1.05rem;
        }
    }

    .mini-stats {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .mini-stat {
        width: 170px;
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 12px;
        box-shadow: 0 6px 14px rgba(2, 47, 85, 0.06);
        padding: 10px 12px;
    }

    .mini-stat-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .mini-stat-num {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.05;
    }

    .mini-stat-label {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        margin-top: 2px;
    }

    .mini-stat-dot {
        width: 28px;
        height: 28px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        color: #fff;
        flex: 0 0 auto;
    }

    .dot-aprob {
        background: #28a745;
    }

    .dot-pend {
        background: #002F55;
    }

    .mini-stat-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 8px;
        font-size: 11px;
        color: #94a3b8;
    }

    .mini-ring {
        width: 34px;
        height: 34px;
        border-radius: 999px;
        border: 3px solid #e5e7eb;
        position: relative;
    }

    .mini-ring::after {
        content: "";
        position: absolute;
        inset: -3px;
        border-radius: 999px;
        border: 3px solid transparent;
        border-top-color: #0f172a;
        transform: rotate(var(--rot, 0deg));
    }

    @media (max-width: 768px) {
        .mini-stats {
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .mini-stat {
            width: 160px;
        }
    }

    .swal2-popup.swal-saved {
        padding: 0 !important;
        border-radius: 22px !important;
        width: 620px !important;
        max-width: 95vw !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .swal2-container.saved-backdrop {
        background: rgba(0, 0, 0, .18) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 24px !important;
    }

    .swal-saved-card {
        background: #fff;
        border-radius: 22px;
        box-shadow: 0 18px 60px rgba(0, 0, 0, .16);
        padding: 26px 28px 28px;
    }

    .swal-saved-header {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        font-weight: 700;
        font-size: 18px;
    }

    .swal-saved-divider {
        height: 1px;
        background: rgba(0, 0, 0, .08);
        margin: 14px 0 22px;
    }

    .swal-saved-body {
        text-align: center;
    }

    .swal-saved-text {
        color: #6c757d;
        font-size: 16px;
        margin-top: 10px;
    }

    .btn-swal-primary {
        background-color: #022F55 !important;
        color: #ffffff !important;
        border: none;
        border-radius: 12px;
        padding: .5rem 1.5rem;
        transition: .2s ease;
    }

    .btn-swal-primary:hover {
        background-color: #011f3a !important;
        color: #ffffff !important;
        transform: translateY(-1px);
    }
</style>

<style>

</style>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container-fluid">
    <div class="row align-items-center mt-0 mb-2">
        <div class="col-12 col-md-6 text-center text-md-center mb-2 mb-md-0">
            <h4 class="mb-0 fw-bold" style="color:#002F55;">PROFESIONAL SOCIAL</h4>
            <small>Solicitudes baqueanos</small>
        </div>
        <?php
        $totalGeneral = $totalAprobadas + $totalPendientes;

        $porcAprob = ($totalGeneral > 0) ? round(($totalAprobadas / $totalGeneral) * 100) : 0;
        $porcPend = ($totalGeneral > 0) ? (100 - $porcAprob) : 0;

        $rotAprob = (int) round(($porcAprob / 100) * 360);
        $rotPend  = (int) round(($porcPend / 100) * 360);
        ?>
        <div class="col-12 col-md-6 d-flex justify-content-md-end justify-content-center">
            <div class="mini-stats">
                <div class="mini-stat">
                    <div class="mini-stat-top">
                        <div>
                            <div class="mini-stat-num"><?php echo $totalAprobadas; ?></div>
                            <div class="mini-stat-label">Aprobadas</div>
                        </div>
                        <div class="mini-stat-dot dot-aprob">
                            <i class="fa-solid fa-check" style="font-size: 12px;"></i>
                        </div>
                    </div>
                    <div class="mini-stat-footer">
                        <span><?php echo $porcAprob; ?>%</span>
                        <span class="mini-ring" style="--rot: <?php echo $rotAprob; ?>deg;"></span>
                    </div>
                </div>

                <div class="mini-stat">
                    <div class="mini-stat-top">
                        <div>
                            <div class="mini-stat-num"><?php echo $totalPendientes; ?></div>
                            <div class="mini-stat-label">Pendientes</div>
                        </div>
                        <div class="mini-stat-dot dot-pend">
                            <i class="fa-solid fa-clock" style="font-size: 12px;"></i>
                        </div>
                    </div>
                    <div class="mini-stat-footer">
                        <span><?php echo $porcPend; ?>%</span>
                        <span class="mini-ring" style="--rot: <?php echo $rotPend; ?>deg;"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-center">
        <div class="stepper-wrap w-100">
            <div class="btn-group stepper flex-wrap justify-content-center gap-2 gap-md-0"
                role="group" aria-label="Steps">
                <button type="button"
                    class="btn btn-info step-btn"
                    data-step="1"
                    data-href="index.php?page=baqueanos/solicitudes/vistas/solicitud_baqueanos">
                    Solicitar baqueano
                </button>
                <button type="button"
                    class="btn btn-danger step-btn"
                    data-step="2"
                    data-href="index.php?page=baqueanos/solicitudes/vistas/detalles_profesional_rechazadas">
                    Devolución de Cuentas
                    <span class="badge-custom"><?php echo $totalRadicadas; ?></span>
                </button>
                <button type="button"
                    class="btn btn-success step-btn"
                    data-step="3"
                    data-href="index.php?page=baqueanos/solicitudes/vistas/detalles_cargar_cuenta">
                    Cargar cuenta
                    <span class="badge-custom2"><?php echo $totalCargado; ?></span>
                </button>
                <button type="button"
                    class="btn btn-danger step-btn"
                    data-step="4"
                    data-href="index.php?page=baqueanos/solicitudes/vistas/detalles_profesional_solicitudes">
                    Devolución de solicitudes
                    <span class="badge-custom"><?php echo $totalMotivos; ?></span>
                </button>
            </div>
        </div>
    </div>
    <div class="row mt-5">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <div class="d-flex align-items-center ">
                        <div class="d-flex justify-content-center align-items-center me-3 rounded-5  p-2" style="width: 35px; height: 35px; background-color: #ffffffff;">
                            <i class="bi bi-person-video2" style="color: #002F55;"></i>
                        </div>
                        <div>
                            <div class=" text-start text-white" style="font-size: 1.2em;  font-weight: 700;">
                                Solicitudes baqueanos
                            </div>
                            <div style="font-size: 70%; color: #f3f8fdff;" class="text-start">
                                Listado se las solicitudes que te fueron asignadas y estan en proceso de aprobación
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table  table-card text-center align-middle" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr class="text-center">
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">N° Radicado</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">Nombre completo</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">N° identidad</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Cantidad de Días</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Valor del Día</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Total</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Tipo de Actividad</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Estados</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql2 = "SELECT 
                                id,
                                usuario_id,
                                sb_baqueano_nombre,
                                sb_baqueano_apellido,
                                sb_numero_identidad,
                                sb_dias_calculados,
                                sb_cobro_diario,
                                sb_valor_cobrar,
                                sb_tipo_actividad,
                                sb_estado_lider,
                                sb_estado_profesional,
                                sb_estado_gerencia,
                                sb_estado_operaciones
                            FROM solicitud_baqueanos 
                            WHERE sb_estado_lider = 'APROBADO' 
                            AND sb_estado_profesional IN ('PENDIENTE', 'APROBADO') 
                            AND sb_estado_operaciones != 'DEVUELTO'
                            AND sb_estado_gerencia != 'DEVUELTO' 
                            AND (sb_estado_final_pago IS NULL OR sb_estado_final_pago != 'PAGADO')
                            ORDER BY id ASC";
                                if ($stmt = $mysqli->prepare($sql2)) {
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td><a href='index.php?page=baqueanos/solicitudes/vistas/informacion_solicitud_profesional&id=" . urlencode($row['id']) . "' class='rad btn-link'>ARB_" . htmlspecialchars($row['id']) . "</a></td>";
                                            echo "<td class='text-center'>
                                                    <div class='d-flex justify-content-center'>
                                                        <div class='user-info'>
                                                            <div class='user-name'>" . htmlspecialchars($row['sb_baqueano_nombre']) . "</div>
                                                            <div class='user-lastname'>" . htmlspecialchars($row['sb_baqueano_apellido']) . "</div>
                                                        </div>
                                                    </div>
                                                </td>";
                                            echo "<td class='text-center'>" . htmlspecialchars($row['sb_numero_identidad']) . "</td>";
                                            echo "<td class='text-center'>" . htmlspecialchars($row['sb_dias_calculados']) . "</td>";
                                            echo "<td class='text-center'>$" . number_format($row['sb_cobro_diario'], 0, ',', '.') . "</td>";
                                            echo "<td class='text-center'>$" . number_format($row['sb_valor_cobrar'], 0, ',', '.') . "</td>";
                                            echo "<td class='text-center'>" . htmlspecialchars($row['sb_tipo_actividad']) . "</td>";
                                            echo "<td class='text-center'>" .
                                                ($row['sb_estado_profesional'] === 'APROBADO'
                                                    ? '<i class="fa-solid fa-circle-check fa-xl" style="color: rgb(24, 167, 22);"></i>'
                                                    : '<i class="fa-solid fa-spinner fa-xl fa-spin" style="color:rgb(56, 133, 195);"></i>')
                                                . "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='10'>No se encontraron registros disponibles</td></tr>";
                                    }
                                    $stmt->close();
                                } else {
                                    echo "<tr><td colspan='10'>Error al preparar la consulta: " . htmlspecialchars($mysqli->error) . "</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://kit.fontawesome.com/9944c94262.js" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/js/all.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
    $(document).ready(function() {

        var table = $('#dataTable').DataTable({
            language: {
                lengthMenu: "Mostrar _MENU_ registros por página",
                zeroRecords: "No se encontraron resultados",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                infoEmpty: "Mostrando 0 a 0 de 0 registros",
                infoFiltered: "(filtrado de _MAX_ registros totales)",
                search: "Buscar:",
                paginate: {
                    first: "Primero",
                    last: "Último",
                    next: "Siguiente",
                    previous: "Anterior"
                }
            },
            pageLength: 10
        });

        function actualizarContadores() {
            var aprobadas = 0;
            var pendientes = 0;
            table.rows({
                search: 'applied'
            }).every(function() {
                var row = this.data();
                var estadoHtml = row[7];
                if (estadoHtml.includes('fa-circle-check')) {
                    aprobadas++;
                } else {
                    pendientes++;
                }
            });
            $('.mini-stat-num').eq(0).text(aprobadas);
            $('.mini-stat-num').eq(1).text(pendientes);
            var total = aprobadas + pendientes;
            var porcAprob = total > 0 ? Math.round((aprobadas / total) * 100) : 0;
            var porcPend = 100 - porcAprob;
            $('.mini-stat-footer span').eq(0).text(porcAprob + "%");
            $('.mini-stat-footer span').eq(2).text(porcPend + "%");
        }
        table.on('draw', function() {
            actualizarContadores();
        });
        actualizarContadores();
    });

    //script para los botones
    document.querySelectorAll('.step-btn').forEach(b => {
        b.addEventListener('click', () => {
            const href = b.dataset.href;
            if (href) window.location.href = href;
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        fetch("cargar_notificaciones.php?tipo=profesional_social")
            .then(response => response.json())
            .then(data => {
                const lista = document.getElementById("listaNotificaciones");
                const contador = document.getElementById("contadorNotificaciones");

                lista.innerHTML = "";

                if (data.length === 0) {
                    lista.innerHTML = '<span class="dropdown-item text-muted">Sin notificaciones</span>';
                    contador.style.display = "none";
                } else {
                    data.forEach(noti => {
                        const item = document.createElement("a");
                        item.className = "dropdown-item d-flex align-items-center";
                        item.href = noti.link;
                        item.innerHTML = `<div class="font-weight-bold" title="${noti.mensaje}">${noti.mensaje}</div>`;
                        lista.appendChild(item);
                    });

                    contador.innerText = data.length;
                    contador.style.display = "inline-block";
                }
            })
    });
</script>

<?php if (isset($_SESSION['alerta'])): ?>
    <script>
        <?php if ($_SESSION['alerta'] === 'aprobado'): ?>
            Swal.fire({
                customClass: {
                    popup: 'swal-saved',
                    container: 'saved-backdrop',
                },
                showConfirmButton: false,
                background: 'transparent',
                html: `
                    <div class="swal-saved-card">
                        <div class="swal-saved-header" style="color:#198754;">
                            <i class="bi bi-check-circle-fill"></i>
                            Solicitud aprobada
                        </div>
                        <div class="swal-saved-divider"></div>
                        <div class="swal-saved-body">
                            <i class="bi bi-patch-check-fill"
                                style="font-size:75px;color:#198754;opacity:.15;"></i>
                            <div class="swal-saved-text">
                                La <strong>solicitud fue aprobada correctamente</strong>.
                            </div>
                            <div class="mt-4">
                                <button onclick="Swal.close()" 
                                    class="btn-swal-primary">
                                    Aceptar
                                </button>
                            </div>
                        </div>
                    </div>`
            });

        <?php elseif ($_SESSION['alerta'] === 'devuelto'): ?>

            Swal.fire({
                customClass: {
                    popup: 'swal-saved',
                    container: 'saved-backdrop',
                },
                showConfirmButton: false,
                background: 'transparent',
                html: `
                    <div class="swal-saved-card">
                        <div class="swal-saved-header" style="color:#198754;">
                            <i class="bi bi-check-circle-fill"></i>
                            Solicitud devuelta
                        </div>
                        <div class="swal-saved-divider"></div>
                        <div class="swal-saved-body">
                            <i class="bi bi-reply-fill"
                                style="font-size:75px;color:#198754;opacity:.15;"></i>
                            <div class="swal-saved-text">
                                La <strong>solicitud fue devuelta correctamente</strong>.
                            </div>
                            <div class="mt-4">
                                <button onclick="Swal.close()" 
                                    class="btn btn-brand px-4 rounded-3">
                                    Aceptar
                                </button>
                            </div>
                        </div>
                    </div>`
            });

        <?php elseif ($_SESSION['alerta'] === 'editado'): ?>

            Swal.fire({
                customClass: {
                    popup: 'swal-saved',
                    container: 'saved-backdrop',
                },
                showConfirmButton: false,
                background: 'transparent',
                html: `
                    <div class="swal-saved-card">
                        <div class="swal-saved-header" style="color:#198754;">
                            <i class="bi bi-check2-square"></i>
                            Cambios guardados
                        </div>
                        <div class="swal-saved-divider"></div>
                        <div class="swal-saved-body">
                            <i class="bi bi-cloud-check-fill"
                                style="font-size:75px;color:#198754;opacity:.15;"></i>
                            <div class="swal-saved-text">
                                La información fue actualizada correctamente.
                            </div>
                            <div class="mt-4">
                                <button onclick="Swal.close()" 
                                    class="btn btn-brand px-4 rounded-3">
                                    Aceptar
                                </button>
                            </div>
                        </div>
                    </div>`
            });

        <?php endif; ?>
    </script>
<?php unset($_SESSION['alerta']);
endif; ?>