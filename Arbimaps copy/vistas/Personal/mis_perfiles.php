<?php
$where = "";
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../index.php");
    exit();
}

$idUsuario = (int) $_SESSION['id_usuario'];

$sql = "SELECT 
            id_usuario,
            cedula_usuario,
            nombre_usuario,
            apellido_usuario,
            correo_usuario,
            celular_usuario,
            rol_usuario, 
            rol_usuario_dos
        FROM usuarios_cons
        WHERE id_usuario = ?";
$stmtUser = $mysqli->prepare($sql);
$stmtUser->bind_param("i", $idUsuario);
$stmtUser->execute();
$resultado = $stmtUser->get_result();

if (!$resultado || $resultado->num_rows === 0) {
    header("Location: ../../acceso_denegado.php");
    exit();
}

$datosUsuario   = $resultado->fetch_assoc();
$rolUsuario     = $datosUsuario['rol_usuario'] ?? '';
$rolUsuarioDos  = $datosUsuario['rol_usuario_dos'] ?? '';
$cedulaSesion   = trim((string)($datosUsuario['cedula_usuario'] ?? ''));

$rolesPermitidos = array(
    "administrador",
    "atencion_juridica",
    "avaluos",
    "coordinacion_tecnica",
    "consolidacion",
    "control_calidad",
    "director",
    "director_catastro",
    "director_catastral",
    "director_presupuestos",
    "director_proyectos",
    "editor",
    "gerencia",
    "lider_reconocimiento",
    "pagos",
    "procedencia_juridica",
    "reconocedor",
    "revision_juridica",
    "seguridad_social",
    "social",
    "soporte",
    "soporte_nivel1",
    "usuarios_ops",
    "ventanilla_catastral",
);

if (!in_array($rolUsuario, $rolesPermitidos) && !in_array($rolUsuarioDos, $rolesPermitidos)) {
    header("Location: ../../acceso_denegado.php");
    exit();
}

$cedulaSesion = (string) $_SESSION['id_usuario'];
$esAdmin = (strtolower($rolUsuario) === 'administrador' || strtolower($rolUsuarioDos) === 'administrador');

$sqlContratacion = "SELECT
    c.con_id,
    c.con_nombres,
    c.con_apellidos,
    c.con_tipo_documento,
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
    ON u.cedula_usuario = c.con_num_identidad
WHERE c.con_estado IN ('ACTIVO', 'EN CURSO')";

if (!$esAdmin) {
    $sqlContratacion .= " AND c.con_num_identidad = ?";
}

$sqlContratacion .= " ORDER BY c.con_id ASC";
$stmt = $mysqli->prepare($sqlContratacion);

if (!$esAdmin) {
    $stmt->bind_param("s", $cedulaSesion);
}
$stmt->execute();
$resultadoContratacion = $stmt->get_result();
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
        width: 1% !important;
        white-space: nowrap;
        padding-left: 12px;
        padding-right: 12px;
    }

    .user-photo {
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid #002F55;
        display: block;
    }

    .user-info {
        width: auto !important;
        max-width: 140px;
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

    td.nombres {
        width: 1% !important;
        white-space: nowrap;
        padding-left: 12px;
        padding-right: 12px;
    }

    .user-cell {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        gap: 10px;
    }

    .doc-cell {
        display: flex;
        flex-direction: column;
        align-items: center;
        line-height: 1.1;
    }

    .doc-type {
        font-weight: 600;
        font-size: 12px;
        opacity: .9;
        white-space: nowrap;
    }

    .doc-number {
        font-size: 13px;
        white-space: nowrap;
    }

    .table-card tbody tr.estado-activo td {
        background-color: #e9f7ef !important;
    }

    .table-card tbody tr.estado-activo td:first-child {
        border-left: 2px solid #28a745 !important;
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
        <h4 class=" mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 700 !important ">
            MIS PERFILES DE CONTRATACION
        </h4>
        <small>Lista de mis perfiles</small>
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
                        <table class="table table-card " id="datatable" width="100%" cellspacing="0">
                            <thead>
                                <tr class="text-center">
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px;">Nombres</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">Documento</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">Cargo</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">Correo</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">Fecha final</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">Tipo de contrato</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">Actualizar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($resultadoContratacion && $resultadoContratacion->num_rows > 0): ?>
                                    <?php while ($row = $resultadoContratacion->fetch_assoc()): ?>
                                        <tr class="<?= (strtoupper(trim($row['con_estado'] ?? '')) === 'ACTIVO') ? 'estado-activo' : '' ?>">
                                            <td class="nombres">
                                                <?php
                                                $foto           = $row['foto_user'] ?? '';
                                                $idUsuarioFoto  = $row['id_usuario'] ?? '';
                                                $rutaFoto = '';
                                                if (!empty($foto) && !empty($idUsuarioFoto)) {
                                                    $rutaFoto = "/arbimaps/Arbimaps/assets/fotos_usuarios/" . $idUsuarioFoto . "/" . $foto;
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
                                            <td style="text-align:center; font-size:13px;">
                                                <div class="doc-cell">
                                                    <div class="doc-type"><?= htmlspecialchars($row['con_tipo_documento'] ?? '') ?></div>
                                                    <a class="doc-number text-primary"
                                                        href="/arbimaps/Arbimaps/index.php?page=Personal/informacion_personal_individual&con_num_identidad=<?= urlencode($row['con_num_identidad'] ?? '') ?>">
                                                        <?= htmlspecialchars($row['con_num_identidad'] ?? '') ?>
                                                    </a>
                                                </div>
                                            </td>
                                            <td style="text-align:center; font-size:13px;">
                                                <?= htmlspecialchars($row['con_cargo']) ?>
                                            </td>
                                            <td style="text-align:center; font-size:13px;">
                                                <?= htmlspecialchars($row['con_correo']) ?>
                                            </td>
                                            <td style="text-align:center; font-size:13px;">
                                                <?= htmlspecialchars($row['con_fecha_final']) ?>
                                            </td>
                                            <td style="text-align:center; font-size:13px;">
                                                <?= htmlspecialchars($row['con_tipo_contrato']) ?>
                                            </td>
                                            <td style="text-align:center; font-size:13px;">
                                                <?php
                                                $ident = urlencode($row['con_num_identidad'] ?? '');
                                                ?>
                                                <a href="/arbimaps/Arbimaps/index.php?page=Personal/editar_perfil_profesional&con_num_identidad=<?= $ident ?>"
                                                    class="text-primary"
                                                    title="Editar perfil de contratación">
                                                    <i class="bi bi-pencil" style="font-size:1.2em; color: #022F55"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
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
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
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
                        window.location.href = '/arbimaps/Arbimaps/index.php?page=Personal/mis_perfiles';
                    });
                }
            });
        }
    });

    $(document).ready(function() {
        $('#datatable').DataTable({
            responsive: true,
            autoWidth: false,
            scrollX: true,
            language: {
                lengthMenu: "Mostrar _MENU_ registros por página",

                emptyTable: `
                    <div class="py-5 text-center">
                        <div class="fw-semibold mb-2" style="color:#002F55;">
                            No tienes perfiles registrados
                        </div>
                        <a href="/arbimaps/Arbimaps/index.php?page=Personal/agregar_mi_perfil"
                        class="btn btn-primary"
                        style="background:#002F55;border-color:#002F55;">
                            <i class="bi bi-plus-circle me-1"></i> Agregar nuevo
                        </a>

                        <div class="text-muted mt-2" style="font-size:12px;">
                            Crea tu primer perfil de contratación.
                        </div>
                    </div>
                `,

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
            ],
            columnDefs: [{
                targets: 0,
                orderable: false
            }]
        });
    });
</script>