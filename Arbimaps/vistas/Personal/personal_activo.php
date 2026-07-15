<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../../../conexion.php';

$sql = "SELECT 
            c.con_id,
            c.con_nombres,
            c.con_apellidos,
            c.con_num_identidad,
            c.con_correo,
            c.con_celular,
            c.con_cargo,
            c.con_proyecto,
            c.con_fecha_final,
            c.con_estado,
            c.con_tipo_contrato,
            u.id_usuario,
            u.foto_user
        FROM contratacion c
        LEFT JOIN usuarios_cons u
            ON u.id_usuario = c.con_num_identidad
        WHERE c.con_estado IN ('ACTIVO', 'EN CURSO')
        ORDER BY c.con_id ASC";

$resultado = $mysqli->query($sql);
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


    /* Tabla tipo cards */
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

    .swal-card {
        width: 360px;
        margin-left: 23%;
        max-width: 92vw;
        padding: 26px 22px 18px 22px;
        border-radius: 16px;
        box-shadow: 0 14px 35px rgba(0, 0, 0, .15);
        background: #fff;
        position: relative;
        overflow: hidden;
        text-align: center;
        font-family: inherit;
    }

    .swal-card::before {
        content: "";
        position: absolute;
        inset: 0;
        opacity: .9;
        background-image:
            radial-gradient(circle at 12% 18%, rgba(0, 0, 0, .10) 2px, transparent 3px),
            radial-gradient(circle at 82% 22%, rgba(0, 0, 0, .10) 2px, transparent 3px),
            radial-gradient(circle at 72% 64%, rgba(0, 0, 0, .10) 2px, transparent 3px),
            radial-gradient(circle at 22% 72%, rgba(0, 0, 0, .10) 2px, transparent 3px),
            radial-gradient(circle at 45% 35%, rgba(0, 0, 0, .08) 1.5px, transparent 2.5px),
            radial-gradient(circle at 55% 80%, rgba(0, 0, 0, .08) 1.5px, transparent 2.5px);
        pointer-events: none;
    }

    .swal-title-like {
        position: relative;
        margin: 0;
        font-weight: 700;
        font-size: 22px;
        color: #2f2f2f;
    }

    .swal-sub-like {
        position: relative;
        margin: 10px 0 18px 0;
        font-size: 13px;
        color: #8b8b8b;
        line-height: 1.25rem;
    }

    .swal-icon-wrap {
        position: relative;
        width: 120px;
        height: 120px;
        margin: 18px auto 14px auto;
        border-radius: 999px;
        display: grid;
        place-items: center;
    }

    .swal-icon-wrap.green {
        background: rgba(46, 133, 204, 0.16);
    }

    .swal-icon-wrap.red {
        background: rgba(255, 76, 97, .16);
    }

    .swal-icon-circle {
        width: 86px;
        height: 86px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        color: #fff;
        font-size: 44px;
        font-weight: 800;
    }

    .swal-icon-circle.green {
        background: #002F55;
    }

    .swal-icon-circle.red {
        background: #ff4c61;
    }

    .swal-actions-like {
        position: relative;
        margin-top: 10px;
        display: grid;
        gap: 10px;
    }

    .swal-btn-like {
        width: 70%;
        border: 0;
        border-radius: 10px;
        padding: 14px 16px;
        font-weight: 800;
        letter-spacing: .4px;
        font-size: 13px;
        cursor: pointer;
    }

    .swal-btn-like.green {
        background: #002F55;
        color: #fff;
    }

    .swal-btn-like.red {
        background: #ff4c61;
        color: #fff;
    }

    .swal2-popup.swal2-modal.custom-swal-popup {
        padding: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .swal2-html-container.custom-swal-html {
        margin: 0 !important;
        padding: 0 !important;
    }

    .swal-actions-row {
        display: flex !important;
        justify-content: center;
        gap: 10px;
    }

    .swal-mini-btn {
        width: 36px;
        height: 36px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .swal-mini-btn i {
        font-size: 16px;
        line-height: 1;
    }
</style>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container-fluid">
    <div class="my-4 text-center">
        <h4 class=" mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 700 !important ">MÓDULO DEL PERSONAL ACTIVO</h4>
        <small> Lista de personal activo</small>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <div class="d-flex align-items-center ">
                        <div class="d-flex justify-content-center align-items-center me-3 rounded-5  p-2" style="width: 35px; height: 35px; background-color: #ffffffff;">
                            <i class="bi bi-person-video2" style="color: #002F55;"></i>
                        </div>
                        <div>
                            <div class=" text-start text-white" style="font-size: 1.2em;  font-weight: 700;">
                                Personal Activo de arbitrium
                            </div>
                            <div style="font-size: 70%; color: #f3f8fdff;" class="text-start">
                                Listado de cooperadores activos en plataforma
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive ">
                        <table class="table  table-card " id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr class="text-center">
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">Nombres</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">N° identidad</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">Cargo/Rol</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">Proyecto</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">Semáforo</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">Fecha de Terminacion</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($resultado && $resultado->num_rows > 0): ?>
                                    <?php while ($row = $resultado->fetch_assoc()): ?>
                                        <?php
                                        $con_id     = (int)$row['con_id'];
                                        $con_estado = $row['con_estado'];

                                        $tipo_contrato = strtoupper(trim($row['con_tipo_contrato'] ?? ''));

                                        $sql_verificar = "SELECT COUNT(*) AS total 
                                                            FROM otrosi 
                                                            WHERE con_id = ?";
                                        $stmt_verificar = $mysqli->prepare($sql_verificar);

                                        if (!$stmt_verificar) {
                                            die("Error al preparar consulta otrosí: " . $mysqli->error);
                                        }

                                        $stmt_verificar->bind_param("i", $con_id);
                                        $stmt_verificar->execute();
                                        $res_verificar = $stmt_verificar->get_result();
                                        $row_verificar = $res_verificar->fetch_assoc();
                                        $stmt_verificar->close();
                                        $ya_solicitado = ((int)$row_verificar['total']) > 0;

                                        $sql_aceptado = "SELECT COUNT(*) AS total
                                                            FROM solicitudes_otrosi
                                                            WHERE con_id = ?
                                                            AND sol_estado_notificacion = 'ACEPTADO'";
                                        $stmt_aceptado = $mysqli->prepare($sql_aceptado);
                                        if (!$stmt_aceptado) {
                                            die("Error al preparar consulta solicitudes_otrosi: " . $mysqli->error);
                                        }

                                        $stmt_aceptado->bind_param("i", $con_id);
                                        $stmt_aceptado->execute();
                                        $res_aceptado = $stmt_aceptado->get_result();
                                        $row_aceptado = $res_aceptado->fetch_assoc();
                                        $stmt_aceptado->close();
                                        $otrosi_completado = ((int)$row_aceptado['total']) > 0;
                                        $fecha_final_otrosi = null;
                                        $sql_fecha_otrosi = "SELECT sol_nueva_fecha_final
                                                            FROM solicitudes_otrosi
                                                            WHERE con_id = ?
                                                            AND sol_estado_notificacion = 'ACEPTADO'
                                                            ORDER BY sol_nueva_fecha_final DESC
                                                            LIMIT 1";
                                        $stmt_fecha_otrosi = $mysqli->prepare($sql_fecha_otrosi);
                                        if ($stmt_fecha_otrosi) {
                                            $stmt_fecha_otrosi->bind_param("i", $con_id);
                                            $stmt_fecha_otrosi->execute();
                                            $res_fecha_otrosi = $stmt_fecha_otrosi->get_result();
                                            if ($fila_fecha = $res_fecha_otrosi->fetch_assoc()) {
                                                $fecha_final_otrosi = $fila_fecha['sol_nueva_fecha_final'];
                                            }
                                            $stmt_fecha_otrosi->close();
                                        }
                                        $fecha_fin_semaforo = (!empty($fecha_final_otrosi)) ? $fecha_final_otrosi : ($row['con_fecha_final'] ?? null);
                                        $dias_restantes = null;
                                        if ($tipo_contrato === 'LABORAL_TERMINO_INDEFINIDO') {
                                            $semaforo = "<span class='badge bg-primary' style='font-size:1.3em;' data-bs-toggle='tooltip' data-bs-placement='top' title='térmno indefinido' ><i class='bi bi-infinity'></i></span>";
                                        } else {
                                            if (!empty($fecha_fin_semaforo)) {
                                                $fecha_fin      = new DateTime($fecha_fin_semaforo);
                                                $hoy            = new DateTime();
                                                $intervalo      = $hoy->diff($fecha_fin);
                                                $dias_restantes = (int) $intervalo->format('%r%a');
                                                if ($dias_restantes < 0) {
                                                    $semaforo = "<span class='badge bg-danger' style='font-size:1.3em;' data-bs-toggle='tooltip' data-bs-placement='top' title='Vencido' ><i class='bi bi-calendar-x'></i></span>";
                                                } elseif ($dias_restantes <= 30) {
                                                    $semaforo = "<span class='badge bg-warning text-dark' style='font-size:0.8em;'>Vence en $dias_restantes días</span>";
                                                } else {
                                                    $meses_restantes = $intervalo->m + ($intervalo->y * 12);
                                                    $dias_extra      = $intervalo->d;
                                                    $texto = "Faltan ";
                                                    if ($meses_restantes > 0) {
                                                        $texto .= "$meses_restantes " . ($meses_restantes == 1 ? "mes" : "meses");
                                                    }
                                                    if ($dias_extra > 0) {
                                                        if ($meses_restantes > 0) $texto .= " y ";
                                                        $texto .= "$dias_extra " . ($dias_extra == 1 ? "día" : "días");
                                                    }
                                                    $semaforo = "<span class='badge bg-success' style='font-size:0.8em;'>$texto</span>";
                                                }
                                            } else {
                                                $semaforo = "<span class='badge bg-secondary' style='font-size:0.8em;'>Sin fecha final</span>";
                                            }
                                        }
                                        $fecha_mostrar = '';
                                        if (
                                            $tipo_contrato !== 'LABORAL_TERMINO_INDEFINIDO' &&
                                            !empty($fecha_fin_semaforo) &&
                                            $dias_restantes !== null &&
                                            $dias_restantes < 0
                                        ) {
                                            $fecha_mostrar = date('d/m/Y', strtotime($fecha_fin_semaforo));
                                        }
                                        ?>
                                        <tr class="text-center">
                                            <td class="nombres">
                                                <?php
                                                $foto           = $row['foto_user'] ?? '';
                                                $idUsuarioFoto  = $row['id_usuario'] ?? '';
                                                $rutaFoto = '';
                                                if (!empty($foto) && !empty($idUsuarioFoto)) {
                                                    $rutaFoto = neiva_app_url('Arbimaps/assets/fotos_usuarios/') . $idUsuarioFoto . "/" . $foto;
                                                }
                                                $fallback = "https://ui-avatars.com/api/?name=" . urlencode(($row['con_nombres'] ?? 'U') . ' ' . ($row['con_apellidos'] ?? '')) . "&background=002F55&color=fff";
                                                ?>
                                                <div class="user-cell">
                                                    <img
                                                        src="<?= !empty($rutaFoto) ? htmlspecialchars($rutaFoto) : $fallback ?>"
                                                        alt="Foto"
                                                        class="user-photo"
                                                        onerror="this.src='<?= $fallback ?>';">
                                                    <div class="user-info">
                                                        <div class="user-name"><?= htmlspecialchars($row['con_nombres'] ?? '') ?></div>
                                                        <div class="user-lastname"><?= htmlspecialchars($row['con_apellidos'] ?? '') ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style='text-align: center; vertical-align: middle; font-size: 13px'>
                                                <a href="<?= neiva_app_url('Arbimaps/index.php?page=Personal/informacion_personal&con_num_identidad=') ?><?php echo urlencode($row['con_num_identidad']); ?>"
                                                    class="text-primary">
                                                    <?php echo htmlspecialchars($row['con_num_identidad']); ?>
                                                </a>
                                            </td>
                                            <td style='text-align: center; vertical-align: middle; font-size: 13px'><?php echo htmlspecialchars($row['con_cargo']); ?></td>
                                            <td style='text-align: center; vertical-align: middle; font-size: 13px'><?php echo htmlspecialchars($row['con_proyecto']); ?></td>
                                            <td style='text-align: center; vertical-align: middle; font-size: 13px'><?php echo $semaforo; ?></td>
                                            <td style='text-align: center; vertical-align: middle; font-size: 13px'>
                                                <?php echo $fecha_mostrar ?: '<span class="text-muted">—</span>'; ?>
                                            </td>
                                            <td style='text-align: center; vertical-align: middle; font-size: 13px'>
                                                <?php if ($otrosi_completado): ?>
                                                    <span class="badge bg-success m-1" style="font-size:0.7em;">
                                                        OTRO SI COMPLETADO
                                                    </span>
                                                <?php elseif ($con_estado === 'EN CURSO'): ?>
                                                    <span class="badge text-dark m-1" style='text-align: center; vertical-align: middle; font-size: 0.7em; background-color: #93eaed;'>
                                                        PERSONAL EN VALIDACIÓN
                                                    </span>
                                                <?php else: ?>
                                                    <a href="<?= neiva_app_url('Arbimaps/index.php?page=Personal/editar_personal&con_num_identidad=') ?><?php echo urlencode($row['con_num_identidad']); ?>"
                                                        class="text-decoration-none d-inline-flex align-items-center justify-content-center m-1"
                                                        style="width:32px; height:32px; color:#002F55; border-radius:6px;"
                                                        onmouseover="this.style.background='#e9f1f8'"
                                                        onmouseout="this.style.background='transparent'"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="bottom"
                                                        title="Editar">
                                                        <i class="bi bi-pencil" style="font-size:18px;"></i>
                                                    </a>
                                                    <a href="<?= neiva_app_url('Arbimaps/index.php?page=Personal/completar_datos&con_num_identidad=') ?><?php echo urlencode($row['con_num_identidad']); ?>"
                                                        class="text-decoration-none d-inline-flex align-items-center justify-content-center m-1"
                                                        style="width:32px; height:32px; color:#787878; border-radius:6px;"
                                                        onmouseover="this.style.background='#f0f0f0'"
                                                        onmouseout="this.style.background='transparent'"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="bottom"
                                                        title="Completar">
                                                        <i class="bi bi-person-fill-gear" style="font-size:18px;"></i>
                                                    </a>
                                                    <button type="button"
                                                        class="btn-desactivar d-inline-flex align-items-center justify-content-center m-1"
                                                        style="width:32px; height:32px; color:#ffc107; background:transparent; border:0; border-radius:6px;"
                                                        onmouseover="this.style.background='#fff3cd'"
                                                        onmouseout="this.style.background='transparent'"
                                                        data-id="<?php echo htmlspecialchars($row['con_num_identidad']); ?>"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="bottom"
                                                        title="Desactivar">
                                                        <i class="bi bi-ban" style="font-size:18px;"></i>
                                                    </button>

                                                    <?php
                                                    if ($dias_restantes !== null && $dias_restantes <= 30):
                                                    ?>
                                                        <?php if ($ya_solicitado): ?>
                                                            <span class="badge bg-success m-1" style="font-size:0.7em;">
                                                                Ya solicitado
                                                            </span>
                                                        <?php else: ?>
                                                            <a href="<?= neiva_app_url('Arbimaps/index.php?page=Personal/solicitar_otrosi&con_id=') ?><?php echo urlencode($row['con_id']); ?>"
                                                                class="text-decoration-none d-inline-flex align-items-center justify-content-center m-1"
                                                                style="width:32px; height:32px; color:#198754; border-radius:6px;"
                                                                onmouseover="this.style.background='#d1e7dd'"
                                                                onmouseout="this.style.background='transparent'"
                                                                data-bs-toggle='tooltip'
                                                                data-bs-placement="bottom"
                                                                title="Solicitar Otro Sí">
                                                                <i class="bi bi-journal-plus" namespace="solicitar otro si"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted" style="font-size:0.9em;">
                                            No se encontraron registros disponibles
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>

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
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, "Todos"]
            ]
        });
        $('#dataTable').on('click', '.btn-desactivar', function() {
            const idUsuario = $(this).data('id');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción desactivará al usuario.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, desactivar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('<?= neiva_app_url('Arbimaps/vistas/Personal/acciones/desactivar_usuario.php') ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'id_usuario=' + encodeURIComponent(idUsuario)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Éxito', data.message, 'success')
                                    .then(() => location.reload());
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        })
                        .catch(() => {
                            Swal.fire('Error inesperado', 'No se pudo procesar la solicitud.', 'error');
                        });
                }
            });
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = [].slice.call(
            document.querySelectorAll('[data-bs-toggle="tooltip"]')
        );
        tooltipTriggerList.forEach(function(tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });


    document.addEventListener('DOMContentLoaded', function() {
        const params = new URLSearchParams(window.location.search);
        const estado = params.get('guardado');
        if (estado === 'ok') {
            Swal.fire({
                customClass: {
                    popup: 'custom-swal-popup',
                    htmlContainer: 'custom-swal-html'
                },
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: true,
                html: `
                    <div class="swal-card">
                        <h2 class="swal-title-like">¡Guardado!</h2>
                        <div class="swal-icon-wrap green">
                            <div class="swal-icon-circle green">✓</div>
                        </div>
                        <div class="swal-sub-like">
                            Los datos se guardaron correctamente.
                        </div>
                        <div class="swal-actions-like d-flex justify-content-center">
                            <button type="button" id="swalOk" class="swal-btn-like green w-auto px-4">
                                ACEPTAR
                            </button>
                        </div>
                    </div>
                `,
                didOpen: () => {
                    document.getElementById('swalOk')?.addEventListener('click', () => {
                        window.location.href = '<?= neiva_app_url('Arbimaps/index.php?page=Personal/personal_activo') ?>';
                    });
                }
            });
        }
    });
</script>