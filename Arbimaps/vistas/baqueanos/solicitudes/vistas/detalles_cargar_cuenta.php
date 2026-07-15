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
        font-size: 12px;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
    }

    table.table.table-card {
        border-collapse: separate !important;
        border-spacing: 0 12px !important;
    }

    table.table.table-card td,
    table.table.table-card th {
        border-top: 0 !important;
    }

    .rad-link {
        text-decoration: none !important;
        color: #002F55;
        font-weight: 700;
    }

    .rad-link:hover {
        text-decoration: none !important;
        color: #002F55;
    }

    td.acciones {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        flex-wrap: nowrap;
        min-width: 140px;
    }

    .accion-btn {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        box-shadow: 0 6px 14px rgba(0, 47, 85, .08);
        transition: transform .15s ease, box-shadow .15s ease, background-color .15s ease;
        text-decoration: none !important;
        padding: 0;
    }

    .accion-btn i {
        font-size: 18px;
    }

    .accion-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 22px rgba(0, 47, 85, .14);
        background: #f8fafc;
    }

    .table-card tbody tr:hover .accion-btn {
        background: rgba(255, 255, 255, .14) !important;
        border-color: rgba(255, 255, 255, .22) !important;
        box-shadow: none;
    }

    .info-item {
        padding: 12px 12px;
        border: 1px solid #e6e6e6;
        border-radius: 14px;
        background: #f8fafc;
        box-shadow: 0 8px 18px rgba(0, 47, 85, .06);
    }

    .info-label {
        font-size: 12px;
        color: #6c757d;
        margin-bottom: 2px;
        letter-spacing: .2px;
    }

    .info-value {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
    }

    .badge-warn {
        background: #fff3cd;
        color: #664d03;
        border: 1px solid #ffecb5;
        padding: 4px 8px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        display: inline-block;
        white-space: nowrap;
    }

    .mini-muted {
        font-size: 12px;
        color: #6c757d;
        line-height: 1.2;
        margin-top: 4px;
    }

    td.td-acciones {
        padding: 10px 10px !important;
        background: transparent !important;
        min-width: auto !important;
    }

    .acciones-wrap {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        flex-wrap: nowrap;
        width: 100%;
    }

    .table-card tbody tr:hover td.td-acciones {
        background-color: #17619d !important;
        color: #fff !important;
    }

    td.td-acciones {
        vertical-align: middle !important;
    }

    .custom-modal-position {
        margin-top: 8vh;
    }

    #modalInfo .modal-dialog {
        margin-top: 25vh;
    }
</style>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container-fluid">
    <div class="my-4 text-center">
        <h4 class="mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 800 !important">RADICACIÓN DE CUENTAS</h4>
        <small style="color:#111;">Seccion para radicar una cuenta de cobro de una solicitud</small>
    </div>

    <div class="row mt-5">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="d-flex justify-content-center align-items-center me-3 rounded-5 p-2"
                            style="width: 35px; height: 35px; background-color: #ffffffff;">
                            <i class="bi bi-person-video2" style="color: #002F55;"></i>
                        </div>
                        <div>
                            <div class="text-start text-white" style="font-size: 1.2em; font-weight: 800;">
                                Solicitudes baqueanos
                            </div>
                            <div style="font-size: 70%; color: #f3f8fdff;" class="text-start">
                                Solicitudes que completaron el proceso de aprobación
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-card text-center align-middle" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">N° Radicado</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Nombres</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">N° identidad</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Cantidad de Días</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Valor del Día</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Total</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Tipo de Actividad</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Acciones</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $sql2 = "
                                SELECT 
                                    sb.id,
                                    sb.usuario_id,
                                    sb.sb_baqueano_nombre,
                                    sb.sb_baqueano_apellido,
                                    sb.sb_numero_identidad,
                                    sb.sb_dias_calculados,
                                    sb.sb_cobro_diario,
                                    sb.sb_valor_cobrar,
                                    sb.sb_tipo_actividad,
                                    sb.sb_estado_gerencia,
                                    sb.sb_estado_cuenta,
                                    sb.sb_fecha_fin,
                                    sb.sb_estado_pagos,
                                    sb.sb_fecha_inicio,
                                    ex.fecha_fin_nueva,
                                    ex.dias_agregados,
                                    ex.dias_nuevo_total,
                                    ex.valor_adicional,
                                    ex.valor_nuevo_total,
                                    ex.created_at AS ext_created_at
                                FROM solicitud_baqueanos sb
                                LEFT JOIN (
                                    SELECT e1.*
                                    FROM solicitud_baqueanos_extensiones e1
                                    INNER JOIN (
                                        SELECT solicitud_id, MAX(id) AS max_id
                                        FROM solicitud_baqueanos_extensiones
                                        GROUP BY solicitud_id
                                    ) ult ON ult.solicitud_id = e1.solicitud_id AND ult.max_id = e1.id
                                ) ex ON ex.solicitud_id = sb.id
                                WHERE
                                    UPPER(TRIM(REPLACE(REPLACE(COALESCE(sb.sb_estado_gerencia,''), '\r',''), '\n',''))) = 'APROBADO'
                                    AND
                                    UPPER(TRIM(REPLACE(REPLACE(COALESCE(sb.sb_estado_cuenta,''), '\r',''), '\n',''))) NOT IN ('RADICADO','APROBADO','DEVUELTO')
                                    AND
                                    UPPER(TRIM(REPLACE(REPLACE(COALESCE(sb.sb_estado_pagos,''), '\r',''), '\n',''))) <> 'DEVUELTO'
                                ORDER BY sb.id ASC
                                ";
                                $stmt = $mysqli->prepare($sql2);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                if ($result && $result->num_rows > 0):
                                    while ($row = $result->fetch_assoc()):
                                        $idSolicitud = (int)($row['id'] ?? 0);
                                        $fechaInicioRaw = $row['sb_fecha_inicio'] ?? null;
                                        $fechaFinRaw    = $row['sb_fecha_fin'] ?? null;
                                        $tieneExtension = !empty($row['fecha_fin_nueva']);
                                        $fechaFinVigenteRaw = $tieneExtension ? $row['fecha_fin_nueva'] : $fechaFinRaw;

                                        $fechaInicio = $fechaInicioRaw ? date('Y-m-d', strtotime($fechaInicioRaw)) : '';
                                        $fechaFin    = $fechaFinRaw ? date('Y-m-d', strtotime($fechaFinRaw)) : '';
                                        $fechaFinVigente = $fechaFinVigenteRaw ? date('Y-m-d', strtotime($fechaFinVigenteRaw)) : '';

                                        $totalDiasFechasBase = null;
                                        if ($fechaInicioRaw && $fechaFinRaw) {
                                            $inicio = new DateTime($fechaInicioRaw);
                                            $fin    = new DateTime($fechaFinRaw);
                                            if ($fin >= $inicio) {
                                                $totalDiasFechasBase = $inicio->diff($fin)->days + 1;
                                            }
                                        }

                                        $diasRegistradosBase = $row['sb_dias_calculados'] ?? null;
                                        $cobroDiario = (float)($row['sb_cobro_diario'] ?? 0);
                                        $valorBase   = (float)($row['sb_valor_cobrar'] ?? 0);
                                        $diasVigentes = $tieneExtension ? (int)$row['dias_nuevo_total'] : (int)($diasRegistradosBase ?? 0);
                                        $valorVigente = $tieneExtension ? (float)$row['valor_nuevo_total'] : $valorBase;
                                        $diasAgregados = $tieneExtension ? (int)$row['dias_agregados'] : 0;
                                        $valorAdicional = $tieneExtension ? (float)$row['valor_adicional'] : 0;

                                        $diasDiffWarning = (
                                            $totalDiasFechasBase !== null &&
                                            $diasRegistradosBase !== null &&
                                            (int)$diasRegistradosBase !== (int)$totalDiasFechasBase
                                        );
                                        $extFecha = $tieneExtension && !empty($row['ext_created_at'])
                                            ? date('Y-m-d H:i', strtotime($row['ext_created_at']))
                                            : '';
                                ?>
                                        <tr>
                                            <td class="text-center">
                                                <a href="index.php?page=baqueanos/solicitudes/vistas/informacion_mi_solicitud&id=<?= urlencode((string)$idSolicitud) ?>"
                                                    class="rad btn-link">
                                                    ARB_<?= htmlspecialchars((string)$idSolicitud) ?>
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                <div class="fw-semibold">
                                                    <?= htmlspecialchars((string)$row['sb_baqueano_nombre']) ?>
                                                </div>
                                                <div class="text-center">
                                                    <?= htmlspecialchars((string)$row['sb_baqueano_apellido']) ?>
                                                </div>
                                            </td>
                                            <td class="text-center"><?= htmlspecialchars((string)$row['sb_numero_identidad']) ?></td>
                                            <td class="text-center">
                                                <?= htmlspecialchars((string)$diasVigentes) ?>
                                                <?php if ($tieneExtension): ?>
                                                    <span class="badge-warn ms-2" title="Tiene extensión (+<?= (int)$diasAgregados ?> días)">
                                                        +<?= (int)$diasAgregados ?> días
                                                    </span>
                                                    <?php if ($extFecha): ?>
                                                        <div class="text-center">Ext: <?= htmlspecialchars($extFecha) ?></div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <?php if ($diasDiffWarning): ?>
                                                        <span class="badge-warn ms-2" title="Los días registrados no coinciden con el rango de fechas base">
                                                            Revisar
                                                        </span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">$<?= number_format($cobroDiario, 0, ',', '.') ?></td>
                                            <td class="text-center">
                                                $<?= number_format($valorVigente, 0, ',', '.') ?>
                                                <?php if ($tieneExtension): ?>
                                                    <div class="text-center">
                                                        +$<?= number_format($valorAdicional, 0, ',', '.') ?> (ext.)
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center"><?= htmlspecialchars((string)$row['sb_tipo_actividad']) ?></td>
                                            <td class="text-center td-acciones">
                                                <div class="acciones-wrap">
                                                    <?php if (($row['sb_estado_gerencia'] ?? '') === 'APROBADO'): ?>
                                                        <a class="accion-btn"
                                                            href="index.php?page=baqueanos/solicitudes/vistas/radicar_cuenta_baqueanos&id=<?= urlencode((string)$idSolicitud) ?>"
                                                            title="Ver o agregar cuenta">
                                                            <i class="fas fa-file-invoice-dollar text-success"></i>
                                                        </a>

                                                        <button type="button"
                                                            class="accion-btn"
                                                            title="Agregar días (extender fechas)"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalAgregarDias"
                                                            data-id="<?= htmlspecialchars((string)$idSolicitud) ?>"
                                                            data-inicio="<?= htmlspecialchars((string)$fechaInicio) ?>"
                                                            data-fin="<?= htmlspecialchars((string)$fechaFinVigente) ?>"
                                                            data-fin-base="<?= htmlspecialchars((string)$fechaFin) ?>"
                                                            data-tiene-extension="<?= $tieneExtension ? '1' : '0' ?>"
                                                            data-dias-vigentes="<?= htmlspecialchars((string)$diasVigentes) ?>"
                                                            data-cobro-diario="<?= htmlspecialchars((string)$cobroDiario) ?>"
                                                            data-valor-total="<?= htmlspecialchars((string)$valorVigente) ?>">
                                                            <i class="fas fa-calendar-plus text-primary"></i>
                                                        </button>

                                                        <button type="button"
                                                            class="accion-btn"
                                                            title="Ver información de fechas y días"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalInfo"
                                                            data-id="<?= htmlspecialchars((string)$idSolicitud) ?>"
                                                            data-inicio="<?= htmlspecialchars((string)$fechaInicio) ?>"
                                                            data-fin-base="<?= htmlspecialchars((string)$fechaFin) ?>"
                                                            data-fin-vigente="<?= htmlspecialchars((string)$fechaFinVigente) ?>"
                                                            data-dias-base="<?= htmlspecialchars((string)($diasRegistradosBase ?? '')) ?>"
                                                            data-dias-vigentes="<?= htmlspecialchars((string)$diasVigentes) ?>"
                                                            data-cobro-diario="<?= htmlspecialchars((string)$cobroDiario) ?>"
                                                            data-valor-base="<?= htmlspecialchars((string)$valorBase) ?>"
                                                            data-valor-vigente="<?= htmlspecialchars((string)$valorVigente) ?>"
                                                            data-dias-agregados="<?= htmlspecialchars((string)$diasAgregados) ?>"
                                                            data-valor-adicional="<?= htmlspecialchars((string)$valorAdicional) ?>"
                                                            data-ext-fecha="<?= htmlspecialchars((string)$extFecha) ?>">
                                                            <i class="fas fa-info-circle text-secondary"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php
                                    endwhile;
                                else:
                                    ?>
                                <?php
                                endif;
                                $stmt->close();
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAgregarDias" tabindex="-1" aria-labelledby="modalAgregarDiasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg modal-fullscreen-sm-down custom-modal-position">
        <div class="modal-content border-0 rounded-4 shadow">
            <form method="POST" action="./vistas/baqueanos/solicitudes/acciones/extender_fechas.php">
                <div class="modal-header text-white py-2" style="background-color: #002F55;">
                    <div class="d-flex align-items-center w-100">
                        <div class="flex-grow-1 text-center">
                            <h5 class="modal-title mb-0" id="modalAgregarDiasLabel">Agregar días</h5>
                            <small class="opacity-75 d-block">Extender fecha final de la solicitud</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body py-2">
                    <input type="hidden" name="id" id="ag_id" value="">
                    <input type="hidden" id="ag_cobro_diario_hidden" value="">

                    <div class="row g-2 mb-2">
                        <div class="col-12 col-md-6">
                            <div class="border rounded-3 p-2 bg-light">
                                <div class="small text-muted">Codigo de solicitud</div>
                                <div class="fw-bold small" id="ag_radicado">ARB_</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="border rounded-3 p-2 bg-light">
                                <div class="small text-muted">Cobro diario</div>
                                <div class="fw-bold small" id="ag_cobro_diario">$0</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="border rounded-3 p-2 bg-light">
                                <div class="small text-muted">Fecha inicio de actividad</div>
                                <div class="fw-bold small" id="ag_inicio">Sin dato</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="border rounded-3 p-2 bg-light">
                                <div class="small text-muted">Fecha final proyectada</div>
                                <div class="fw-bold small" id="ag_fin">Sin dato</div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-2">
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold mb-1 small">Agregar dias solicitud</label>
                            <input type="date" class="form-control form-control-sm" name="nueva_fecha_fin" id="ag_nueva_fin" required>
                            <div class="form-text small">Debe ser posterior a la fecha fin actual.</div>
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label fw-bold mb-1 small">Días a agregar</label>
                            <input type="number" class="form-control form-control-sm" id="ag_dias_agregar" min="1" step="1" readonly>
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label fw-bold mb-1 small">Nuevo total días</label>
                            <input type="number" class="form-control form-control-sm" id="ag_nuevo_total_dias" readonly>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold mb-1 small">Nuevo valor total</label>
                            <input type="text" class="form-control form-control-sm" id="ag_nuevo_valor_total" readonly>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold mb-1 small">Justificación</label>
                            <input type="text"
                                class="form-control form-control-sm"
                                name="observacion"
                                id="ag_observacion"
                                maxlength="250"
                                placeholder="Ej: Justificación para agregar días a la solicitud">
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 justify-content-center gap-2">
                    <button type="button" class="btn btn-outline-danger btn-sm px-3 d-inline-flex align-items-center gap-2"
                        data-bs-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Cerrar
                    </button>

                    <button type="submit" class="btn btn-success btn-sm px-3 d-inline-flex align-items-center gap-2"
                        id="ag_btn_guardar">
                        <i class="fas fa-save"></i>
                        Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="modalInfo" tabindex="-1" aria-labelledby="modalInfoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg modal-fullscreen-sm-down">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header text-white py-2" style="background-color: #002F55;">
                <div class="d-flex align-items-center w-100">
                    <div class="flex-grow-1 text-center">
                        <h5 class="modal-title mb-0" id="modalInfoLabel">Detalle de solicitud</h5>
                        <small class="opacity-75 d-block">Fechas, días y valores</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="border rounded-4 p-3 bg-light">
                            <div class="small text-muted">Fecha inicio de actividad</div>
                            <div class="fw-bold" id="info_inicio">Sin dato</div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="border rounded-4 p-3 bg-light">
                            <div class="small text-muted">Fecha final proyectada</div>
                            <div class="fw-bold" id="info_fin_base">Sin dato</div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="border rounded-4 p-3 bg-light">
                            <div class="small text-muted">Nueva fecha final de la actividad</div>
                            <div class="fw-bold" id="info_fin_vigente">Sin dato</div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="border rounded-4 p-3 bg-light">
                            <div class="small text-muted">Días iniciales / Dias totales</div>
                            <div class="fw-bold" id="info_dias">Sin dato</div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="border rounded-4 p-3 bg-light">
                            <div class="small text-muted">Valor inicial / Valor total</div>
                            <div class="fw-bold" id="info_valores">$0</div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="border rounded-4 p-3 bg-light">
                            <div class="small text-muted">Adición</div>
                            <div class="fw-bold" id="info_extension">Sin extensión</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="text-muted small text-center" id="info_ext_fecha"></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer py-2 justify-content-center gap-2">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://kit.fontawesome.com/9944c94262.js" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js" crossorigin="anonymous"></script>

<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
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
            pageLength: 10,
            order: [
                [0, 'asc']
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, "Todos"]
            ]
        });
    });

    function formatMoneyCOP(n) {
        try {
            return new Intl.NumberFormat('es-CO', {
                maximumFractionDigits: 0
            }).format(Number(n || 0));
        } catch {
            return n;
        }
    }

    function addDays(dateStr, days) {
        const d = new Date(dateStr + "T00:00:00");
        d.setDate(d.getDate() + days);
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    }

    function diffDaysInclusive(startStr, endStr) {
        const start = new Date(startStr + "T00:00:00");
        const end = new Date(endStr + "T00:00:00");
        const ms = end - start;
        if (ms < 0) return null;
        return Math.floor(ms / (1000 * 60 * 60 * 24)) + 1;
    }

    // Modal AGREGAR DÍAS: cargar datos desde la fila (vigentes)
    const modalAgregarDias = document.getElementById('modalAgregarDias');
    modalAgregarDias.addEventListener('show.bs.modal', function(event) {
        const btn = event.relatedTarget;

        const id = btn.getAttribute('data-id') || '';
        const inicio = btn.getAttribute('data-inicio') || '';
        const fin = btn.getAttribute('data-fin') || '';
        const cobroDiario = Number(btn.getAttribute('data-cobro-diario') || 0);

        document.getElementById('ag_id').value = id;
        document.getElementById('ag_radicado').textContent = 'ARB_' + id;
        document.getElementById('ag_inicio').textContent = inicio || 'Sin dato';
        document.getElementById('ag_fin').textContent = fin || 'Sin dato';
        document.getElementById('ag_cobro_diario').textContent = '$' + formatMoneyCOP(cobroDiario);
        document.getElementById('ag_cobro_diario_hidden').value = String(cobroDiario);

        const inputNuevaFin = document.getElementById('ag_nueva_fin');
        const inputDiasAgregar = document.getElementById('ag_dias_agregar');
        const inputNuevoTotalDias = document.getElementById('ag_nuevo_total_dias');
        const inputNuevoValorTotal = document.getElementById('ag_nuevo_valor_total');

        inputNuevaFin.value = '';
        inputDiasAgregar.value = '';
        inputNuevoTotalDias.value = '';
        inputNuevoValorTotal.value = '';

        if (!inicio || !fin) {
            inputNuevaFin.disabled = true;
            document.getElementById('ag_btn_guardar').disabled = true;
            return;
        }
        inputNuevaFin.disabled = false;
        document.getElementById('ag_btn_guardar').disabled = false;
        inputNuevaFin.min = addDays(fin, 1);
    });

    document.getElementById('ag_nueva_fin').addEventListener('change', function() {
        const nuevaFin = this.value;
        const inicio = document.getElementById('ag_inicio').textContent.trim();
        const finActual = document.getElementById('ag_fin').textContent.trim();
        const cobroDiario = Number(document.getElementById('ag_cobro_diario_hidden').value || 0);
        const inputDiasAgregar = document.getElementById('ag_dias_agregar');
        const inputNuevoTotalDias = document.getElementById('ag_nuevo_total_dias');
        const inputNuevoValorTotal = document.getElementById('ag_nuevo_valor_total');

        inputDiasAgregar.value = '';
        inputNuevoTotalDias.value = '';
        inputNuevoValorTotal.value = '';

        if (!nuevaFin || !inicio || !finActual || inicio === 'Sin dato' || finActual === 'Sin dato') return;
        if (nuevaFin <= finActual) {
            alert("Solo puedes aumentar la fecha fin. Debe ser posterior a la fecha fin actual.");
            this.value = '';
            return;
        }
        const totalActual = diffDaysInclusive(inicio, finActual);
        const totalNuevo = diffDaysInclusive(inicio, nuevaFin);
        if (totalActual === null || totalNuevo === null) return;

        const diasAgregar = totalNuevo - totalActual;
        if (diasAgregar <= 0) {
            alert("La nueva fecha no incrementa el rango. Selecciona una fecha posterior.");
            this.value = '';
            return;
        }
        const nuevoValorTotal = cobroDiario * totalNuevo;

        inputDiasAgregar.value = diasAgregar;
        inputNuevoTotalDias.value = totalNuevo;
        inputNuevoValorTotal.value = '$' + formatMoneyCOP(nuevoValorTotal);
    });

    const modalInfo = document.getElementById('modalInfo');
    modalInfo.addEventListener('show.bs.modal', function(event) {
        const btn = event.relatedTarget;
        const id = btn.getAttribute('data-id') || '';
        const inicio = btn.getAttribute('data-inicio') || '';
        const finBase = btn.getAttribute('data-fin-base') || '';
        const finVigente = btn.getAttribute('data-fin-vigente') || '';
        const diasBase = btn.getAttribute('data-dias-base') || '';
        const diasVigentes = btn.getAttribute('data-dias-vigentes') || '';
        const cobroDiario = Number(btn.getAttribute('data-cobro-diario') || 0);
        const valorBase = Number(btn.getAttribute('data-valor-base') || 0);
        const valorVigente = Number(btn.getAttribute('data-valor-vigente') || 0);
        const diasAgregados = btn.getAttribute('data-dias-agregados') || '0';
        const valorAdicional = btn.getAttribute('data-valor-adicional') || '0';
        const extFecha = btn.getAttribute('data-ext-fecha') || '';

        document.getElementById('modalInfoLabel').textContent = 'Detalle de solicitud ARB_' + id;
        document.getElementById('info_inicio').textContent = inicio || 'Sin dato';
        document.getElementById('info_fin_base').textContent = finBase || 'Sin dato';
        document.getElementById('info_fin_vigente').textContent = finVigente || 'Sin dato';
        document.getElementById('info_dias').textContent =
            (diasBase ? `${diasBase}` : 'Sin dato') + ' / ' + (diasVigentes ? `${diasVigentes}` : 'Sin dato');

        document.getElementById('info_valores').textContent =
            '$' + formatMoneyCOP(valorBase) + ' / $' + formatMoneyCOP(valorVigente);

        if (Number(diasAgregados) > 0) {
            document.getElementById('info_extension').textContent =
                `+${diasAgregados} días, +$${formatMoneyCOP(valorAdicional)} (ext.)`;
        } else {
            document.getElementById('info_extension').textContent = 'Sin extensión';
        }
        document.getElementById('info_ext_fecha').textContent = extFecha ? ('Registrada: ' + extFecha) : '';
    });

    (function() {
        function formatear(fecha) {
            return fecha.toISOString().split('T')[0];
        }

        function obtenerMaxPermitido() {
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            const diaActual = hoy.getDay();
            const diffLunes = (diaActual === 0 ? -6 : 1 - diaActual);
            const lunesActual = new Date(hoy);
            lunesActual.setDate(hoy.getDate() + diffLunes);
            const domingoSiguiente = new Date(lunesActual);
            domingoSiguiente.setDate(lunesActual.getDate() + 13);

            return formatear(domingoSiguiente);
        }
        const modalAgregarDias = document.getElementById('modalAgregarDias');

        if (!modalAgregarDias) return;
        modalAgregarDias.addEventListener('show.bs.modal', function() {
            const inputNuevaFin = document.getElementById('ag_nueva_fin');
            const finActual = document.getElementById('ag_fin')?.textContent?.trim();

            if (!inputNuevaFin || !finActual || finActual === 'Sin dato') return;

            const maxPermitido = obtenerMaxPermitido();
            const minPermitido = inputNuevaFin.min || '';

            inputNuevaFin.max = maxPermitido;
            inputNuevaFin.addEventListener('change', function() {
                const nuevaFecha = this.value;
                if (!nuevaFecha) return;
                if (nuevaFecha <= finActual) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Fecha inválida',
                        text: 'La nueva fecha debe ser posterior a la fecha fin actual.',
                        confirmButtonColor: '#002F55'
                    });
                    this.value = '';
                    return;
                }
                if (nuevaFecha > maxPermitido) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Fuera de rango',
                        text: 'Solo puedes extender dentro de esta y la próxima semana.',
                        confirmButtonColor: '#002F55'
                    });
                    this.value = '';
                    return;
                }
            }, {
                once: false
            });
        });
    })();
</script>