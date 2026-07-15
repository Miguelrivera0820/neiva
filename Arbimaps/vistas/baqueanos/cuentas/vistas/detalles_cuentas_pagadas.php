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
$rolesPermitidos = array("administrador", "director_proyectos", "soporte", "pagos", "social", "gerencia");

if (!in_array($rolUsuario, $rolesPermitidos) && !in_array($rolUsuarioDos, $rolesPermitidos)) {
    header("Location: ../../acceso_denegado.php");
    exit();
}

$nombre = $_SESSION['nombre_usuario'];
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

    .stepper {
        background: #f1f5f9;
        border: 1px solid #e5e7eb;
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
        background: #ffffff !important;
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
        background: #e7f7ee;
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

    .btnDocSoporte {
        cursor: pointer;
        user-select: none;
    }

    .btnDocSoporte:hover {
        background-color: #f0f4f8 !important;
        transform: translateX(4px);
    }

    .modal {
        margin-top: 5%;
        height: 90%;
    }

    .toggle-eye i {
        transition: all 0.3s ease;
        font-size: 16px;
    }

    .toggle-eye.active i {
        transform: scale(1.2);
    }

    .icon-eye {
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
    }

    .icon-eye i {
        font-size: 15px;
        color: #004f8f;
        transition: all 0.25s ease;
    }

    .icon-eye:hover i {
        transform: scale(1.15);
        opacity: 0.8;
    }

    .icon-eye.step-btn.active i {
        transform: scale(1.2);
    }
</style>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container-fluid">
    <div class="my-4 text-center">
        <h4 class=" mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 700 !important ">HISTORIAL DE CUENTAS PAGADAS</h4>
        <small>Solicitudes de Baqueanos Pagadas</small>
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
                                Cuentas Cargadas
                            </div>
                            <div style="font-size: 70%; color: #f3f8fdff;" class="text-start">
                                Estado de las cuentas pagadas de las solicitudes
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table  table-card text-center align-middle" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr class="text-center">
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Fecha</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">N° Radicado</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Cédula</th>
                                    <th class="text-center align-middle; vertical-aling: middle;" style="font-size:13px; width: 160px;">Nombres</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Municipio</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Vereda</th>
                                    <!-- <th style="text-align: center; vertical-align: middle; font-size: 12px">Social</th> -->
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Valor Pagado</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Medio pago</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Soporte de Pago</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT 
                                            fecha_creacion, 
                                            id, 
                                            sb_numero_identidad, 
                                            sb_baqueano_nombre, 
                                            sb_baqueano_apellido, 
                                            sb_municipio,
                                            sb_vereda, 
                                            sb_profesional_baqueano, 
                                            sb_valor_cobrar,
                                            fecha_hora_pago, 
                                            sb_medio_pago
                                        FROM solicitud_baqueanos
                                        WHERE UPPER(TRIM(sb_estado_final_pago)) = 'PAGADO'
                                        ORDER BY fecha_hora_pago ASC";

                                $stmt = $mysqli->prepare($sql);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result) {
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . date('Y-m-d h:i A', strtotime($row['fecha_hora_pago'])) . "</td>";
                                            echo "<td class='text-center'><a href='index.php?page=baqueanos/cuentas/vistas/informacion_solicitud_radicacion&id=" . urlencode($row['id']) . "' class='rad-link'>ARB_" . htmlspecialchars($row['id']) . "</a></td>";
                                            echo "<td class='text-center'>" . htmlspecialchars($row['sb_numero_identidad']) . "</td>";
                                            echo "<td class='text-center align-middle' style='width:160px;'>
                                                <div class='d-inline-block text-start text-truncate mw-100' style='max-width:160px;'>
                                                    <div class='fw-semibold text-truncate'>" . htmlspecialchars($row['sb_baqueano_nombre']) . "</div>
                                                    <div class='small text-muted text-truncate'>" . htmlspecialchars($row['sb_baqueano_apellido']) . "</div>
                                                </div>
                                            </td>";
                                            echo "<td class='text-center'>" . htmlspecialchars($row['sb_municipio']) . "</td>";
                                            echo "<td class='text-center'>" . htmlspecialchars($row['sb_vereda']) . "</td>";
                                            //echo "<td class='text-center'>" . htmlspecialchars($row['sb_profesional_baqueano']) . "</td>";
                                            echo "<td class='text-center'>$" . number_format($row['sb_valor_cobrar'], 0, ',', '.') . "</td>";
                                            echo "<td class='text-center'>" .
                                                htmlspecialchars(!empty($row['sb_medio_pago']) ? $row['sb_medio_pago'] : 'SIN REGISTRO') . "</td>";
                                            echo "<td class='text-center'>
                                                    <button 
                                                        type='button' 
                                                        class='icon-eye btnVerSoporte'
                                                        data-bs-toggle='modal'
                                                        data-bs-target='#modalVerSoporte'
                                                        data-id='" . htmlspecialchars($row['id']) . "'
                                                        data-radicado='ARB_" . htmlspecialchars($row['id']) . "'>
                                                        <i class='fa-solid fa-eye'></i>
                                                    </button>
                                                </td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='10'>No se encontraron registros disponibles</td></tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='10'>Error en la consulta.</td></tr>";
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

<div class="modal fade" id="modalVerSoporte" tabindex="-1" aria-labelledby="modalVerSoporteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow">
            <div class="modal-header text-white justify-content-center position-relative" style="background-color:#002F55;">
                <div class="text-center">
                    <h5 class="modal-title mb-0" id="modalVerSoporteLabel">Soporte de Pago</h5>
                    <small class="text-white-50">
                        Radicado: <b id="modalRadicadoSoporte">-</b>
                    </small>
                </div>
                <button type="button"
                    class="btn-close btn-close-white position-absolute end-0 top-50 translate-middle-y me-3"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12 col-md-5">
                        <div class="fw-semibold small mb-2" style="color:#002F55;">
                            <i class="fa-solid fa-file-check me-2"></i>Documentos
                        </div>
                        <div class="border rounded-3 p-2" style="max-height: 400px; overflow-y: auto; background-color:#f8fafc;">
                            <div id="listaSoportesCargados"></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-7">
                        <div class="fw-semibold small mb-2" style="color:#002F55;">
                            <i class="fa-solid fa-eye me-2"></i>Vista previa
                        </div>
                        <div class="border rounded-3 overflow-hidden bg-white" style="height: 400px; display:flex; align-items:center; justify-content:center;">
                            <iframe id="previewPdfSoporte" style="display:none; width:100%; height:100%;" frameborder="0"></iframe>
                            <img id="previewImgSoporte" style="display:none; width:100%; height:100%; object-fit:contain;" alt="Vista previa">
                            <div id="previewPlaceholder" style="color:#6b7280; text-align:center; padding:20px;">
                                <i class="fa-solid fa-image" style="font-size:48px; margin-bottom:10px; display:block;"></i>
                                <div style="font-size:12px;">Selecciona un documento para ver la vista previa</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://kit.fontawesome.com/9944c94262.js" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>

<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const modal = document.getElementById('modalVerSoporte');
        document.querySelectorAll(".icon-eye").forEach(button => {
            button.addEventListener("click", function() {
                document.querySelectorAll(".icon-eye i").forEach(i => {
                    i.classList.remove("fa-eye-slash");
                    i.classList.add("fa-eye");
                });
                const icon = this.querySelector("i");
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            });
        });
        modal.addEventListener('hidden.bs.modal', function() {
            document.querySelectorAll(".icon-eye i").forEach(i => {
                i.classList.remove("fa-eye-slash");
                i.classList.add("fa-eye");
            });
        });
    });
</script>

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
    });

    const modalVerSoporte = document.getElementById('modalVerSoporte');
    if (modalVerSoporte) {
        modalVerSoporte.addEventListener('show.bs.modal', async function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const radicado = button.getAttribute('data-radicado');

            document.getElementById('modalRadicadoSoporte').textContent = radicado;
            await cargarSoportesCargados(id);
        });
    }

    async function cargarSoportesCargados(idSolicitud) {
        try {
            const listaContainer = document.getElementById('listaSoportesCargados');
            listaContainer.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Cargando...</span></div></div>';

            const resp = await fetch('./vistas/baqueanos/solicitudes/acciones/obtener_documentos_soporte.php?id_solicitud=' + idSolicitud);
            const data = await resp.json();

            resetPreviewSoporte();
            if (data.success && data.documentos && data.documentos.length > 0) {
                listaContainer.innerHTML = data.documentos.map((doc, index) => `
                    <div class="d-flex align-items-center gap-2 p-2 mb-2 bg-white rounded-3 border border-light cursor-pointer btnDocSoporte"
                            data-index="${index}"
                            data-nombre="${doc.nombre}"
                            data-id-solicitud="${idSolicitud}"
                            style="cursor:pointer; transition:background-color .15s;">
                        <div style="color:#002F55; font-size:18px;">
                            ${obtenerIconoArchivo(doc.nombre)}
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="text-truncate mb-0" style="font-size:12px; font-weight:600; color:#002F55;">
                                ${doc.nombre}
                            </div>
                            <div style="font-size:10px; color:#6b7280;">
                                ${doc.tamaño_formato}
                            </div>
                        </div>
                        <a href="./vistas/baqueanos/solicitudes/acciones/descargar_soporte.php?id_solicitud=${idSolicitud}&archivo=${encodeURIComponent(doc.nombre)}"
                           class="btn btn-sm btn-link p-0"
                           title="Descargar"
                           style="color:#002F55; text-decoration:none;"
                           onclick="event.stopPropagation();">
                            <i class="fa-solid fa-download"></i>
                        </a>
                    </div>
                `).join('');

                document.querySelectorAll('.btnDocSoporte').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const nombre = this.getAttribute('data-nombre');
                        const idSol = this.getAttribute('data-id-solicitud');
                        mostrarPreviewSoporte(nombre, idSol);
                    });
                    btn.addEventListener('mouseover', function() {
                        this.style.backgroundColor = '#f0f4f8';
                    });
                    btn.addEventListener('mouseout', function() {
                        this.style.backgroundColor = '#ffffff';
                    });
                });

                if (data.documentos.length > 0) {
                    const primerDoc = data.documentos[0];
                    mostrarPreviewSoporte(primerDoc.nombre, idSolicitud);
                }
            } else {
                listaContainer.innerHTML = '<div class="alert alert-info m-0 p-2" style="font-size:12px;">No hay documentos cargados.</div>';
            }
        } catch (err) {
            console.error('Error cargando documentos:', err);
            document.getElementById('listaSoportesCargados').innerHTML = '<div class="alert alert-danger m-0 p-2" style="font-size:12px;">Error al cargar.</div>';
        }
    }

    let previewObjectUrlSoporte = null;

    function resetPreviewSoporte() {
        const iframe = document.getElementById('previewPdfSoporte');
        const img = document.getElementById('previewImgSoporte');
        const placeholder = document.getElementById('previewPlaceholder');

        iframe.style.display = 'none';
        img.style.display = 'none';
        placeholder.style.display = 'block';

        iframe.src = '';
        img.src = '';
        if (previewObjectUrlSoporte) {
            URL.revokeObjectURL(previewObjectUrlSoporte);
            previewObjectUrlSoporte = null;
        }
    }

    function mostrarPreviewSoporte(nombreArchivo, idSolicitud) {
        try {
            const iframe = document.getElementById('previewPdfSoporte');
            const img = document.getElementById('previewImgSoporte');
            const placeholder = document.getElementById('previewPlaceholder');

            resetPreviewSoporte();

            const urlArchivo = `./vistas/baqueanos/solicitudes/acciones/descargar_soporte.php?id_solicitud=${idSolicitud}&archivo=${encodeURIComponent(nombreArchivo)}&preview=1`;
            const ext = nombreArchivo.split('.').pop().toLowerCase();

            if (ext === 'pdf') {
                iframe.src = urlArchivo;
                iframe.style.display = 'block';
                placeholder.style.display = 'none';
            } else if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                img.src = urlArchivo;
                img.style.display = 'block';
                placeholder.style.display = 'none';
            } else {
                placeholder.innerHTML = '<i class="fa-solid fa-file" style="font-size:48px; margin-bottom:10px; color:#6b7280;"></i><div style="color:#6b7280;">Este tipo de archivo no puede ser previsualizad</div>';
                placeholder.style.display = 'block';
            }
        } catch (err) {
            console.error('Error mostrando preview:', err);
            document.getElementById('previewPlaceholder').innerHTML = '<div style="color:#dc3545;">Error al cargar la vista previa</div>';
            document.getElementById('previewPlaceholder').style.display = 'block';
        }
    }

    function obtenerIconoArchivo(nombre) {
        const ext = nombre.split('.').pop().toLowerCase();
        switch (ext) {
            case 'pdf':
                return '<i class="fa-solid fa-file-pdf"></i>';
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
                return '<i class="fa-solid fa-image"></i>';
            default:
                return '<i class="fa-solid fa-file"></i>';
        }
    }
</script>