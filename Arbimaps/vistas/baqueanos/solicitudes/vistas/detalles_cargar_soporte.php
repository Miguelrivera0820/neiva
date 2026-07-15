<?php
$where = "";

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../index.php");
    exit();
}

$idUsuario = $_SESSION['id_usuario'];

$sql = "SELECT rol_usuario, nombre_usuario FROM usuarios_cons WHERE id_usuario = ?";
$stmtUser = $mysqli->prepare($sql);
$stmtUser->bind_param("i", $idUsuario);
$stmtUser->execute();
$stmtUser->bind_result($rolUsuarioDB, $nombreUsuarioDB);
$stmtUser->fetch();
$stmtUser->close();

$rolUsuario = $_SESSION['rol_usuario'];
$rolesPermitidos = array("administrador", "director_catastro", "Directivos", "soporte", "pagos");

if (!in_array($rolUsuario, $rolesPermitidos)) {
    header("Location: ../../acceso_denegado.php");
    exit();
}

$nombre = $_SESSION['nombre_usuario'];

function badgeEstado($estado)
{
    $estado = strtoupper(trim((string)$estado));
    switch ($estado) {
        case 'APROBADO':
            return 'success';
        case 'PENDIENTE':
            return 'warning';
        case 'DEVUELTO':
            return 'danger';
        case 'CARGADO':
            return 'primary';
        case 'PAGADO':
            return 'dark';
        default:
            return 'secondary';
    }
}
?>

<style>
    :root {
        --color-primario: #022F55;
        --color-primario-suave: #f1f5f9;
        --color-borde-suave: #e5e7eb;
        --color-texto-muted: #6b7280;
    }

    /* DataTables paginación igual a la otra vista */
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

    /* Header de card con degradado */
    .card-header {
        background: linear-gradient(355deg, #0a579bff, #012949ff);
    }

    /* Tabla tipo cards (igual estilo) */
    .table-card {
        border-collapse: separate !important;
        border-spacing: 0 12px !important;
    }

    .table-card tbody tr {
        background: transparent;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
        transition: transform .15s ease, box-shadow .15s ease;
        border: none !important;
        border-radius: 8px;
    }

    .table-card tbody td {
        background-color: #ffffff;
        padding: 12px 10px;
        vertical-align: middle;
        border-top: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
        transition: background-color .15s ease, color .15s ease;
        font-size: 13px;
    }

    .table-card tbody tr td:first-child {
        border-left: 3px solid #002f55;
        border-radius: 8px 0 0 8px;
    }

    .table-card tbody tr td:last-child {
        border-right: 1px solid #e5e7eb;
        border-radius: 0 8px 8px 0;
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

    .table-card tbody tr:hover td {
        background-color: #17619d !important;
        color: #ffffff !important;
        border-top-color: rgba(255, 255, 255, .18) !important;
        border-bottom-color: rgba(255, 255, 255, .18) !important;
    }

    .table-card tbody tr:hover a,
    .table-card tbody tr:hover .btn-link,
    .table-card tbody tr:hover .badge {
        color: #ffffff !important;
    }

    .table-card tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px #002f5570;
    }

    /* Link radicado como la otra vista */
    .rad-link {
        text-decoration: none !important;
        color: #002F55;
        font-weight: 600;
    }

    .rad-link:hover {
        text-decoration: none !important;
        color: #002F55;
    }

    /* Botón "Cargar" más alineado al estilo */
    .btn-outline-primary {
        border-color: #002F55 !important;
        color: #002F55 !important;
        font-weight: 600;
    }

    .btn-outline-primary:hover {
        background-color: #002F55 !important;
        border-color: #002F55 !important;
        color: #fff !important;
    }

    /* Modal más “clean” */
    .modal-content {
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }

    .modal-header {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
    }

    .modal-footer {
        background: #f8fafc;
        border-top: 1px solid #e5e7eb;
    }

    /* Estilos para documentos cargados */
    #listaDocumentosCargados .btn-link {
        opacity: 0.7;
        transition: opacity .15s ease;
    }

    #listaDocumentosCargados .btn-link:hover {
        opacity: 1;
    }

    #seccionDocumentosCargados .border-light {
        border-color: #e5e7eb !important;
    }

    .min-width-0 {
        min-width: 0;
    }

    .user-info {
        text-align: left;
        line-height: 1.1;
    }

    .user-name {
        font-weight: 700;
        color: #002F55;
        font-size: 13px;
    }

    .user-lastname {
        font-weight: 500;
        color: #6b7280;
        font-size: 12px;
    }

    .table-card tbody tr:hover .user-name,
    .table-card tbody tr:hover .user-lastname {
        color: #fff !important;
    }

    @media (max-width: 576px) {
        #modalSoportePago .modal-dialog {
            margin: .5rem;
        }

        #modalSoportePago .modal-content {
            max-height: calc(100vh - 1rem);
        }

        #modalSoportePago .modal-body {
            overflow: auto;
        }
    }

    .modal-backdrop {
        z-index: 3000 !important;
    }

    .modal {
        z-index: 3050 !important;
    }


    .swalAlertaPago {
        border-radius: 14px !important;
        padding: 18px 22px !important;
    }

    .swalFechaPago {
        width: 100%;
        padding: 8px 10px;
        font-size: 13px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        transition: all .2s ease;
    }

    .swalFechaPago:focus {
        border-color: #002F55;
        box-shadow: 0 0 0 3px rgba(0, 47, 85, .15);
        outline: none;
    }


    #modalSoportePago .modal-dialog {
        max-width: 600px;
    }

    #modalSoportePago .modal-content {
        max-height: 90vh;
        display: flex;
        flex-direction: column;
    }

    #modalSoportePago .modal-body {
        overflow-y: auto;
        max-height: calc(90vh - 160px);
        padding-right: 10px;
    }

    #modalSoportePago .modal-body::-webkit-scrollbar {
        width: 6px;
    }

    #modalSoportePago .modal-body::-webkit-scrollbar-thumb {
        background-color: rgba(0, 47, 85, 0.4);
        border-radius: 6px;
    }

    #modalSoportePago .modal-body::-webkit-scrollbar-thumb:hover {
        background-color: rgba(0, 47, 85, 0.6);
    }

    .swal2-popup.swal-pro {
        padding: 0 !important;
        border-radius: 22px !important;
        width: 600px !important;
        max-width: 95vw !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .swal2-container.pro-backdrop {
        background: rgba(0, 0, 0, .18) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .swal-pro-card {
        background: #fff;
        border-radius: 22px;
        box-shadow: 0 18px 60px rgba(0, 0, 0, .16);
        padding: 26px 28px 28px;
    }

    .swal-pro-header {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        font-weight: 700;
        font-size: 18px;
    }

    .swal-pro-divider {
        height: 1px;
        background: rgba(0, 0, 0, .08);
        margin: 14px 0 22px;
    }

    .swal-pro-body {
        text-align: center;
    }

    .swal-pro-text {
        color: #6c757d;
        font-size: 15px;
        margin-top: 10px;
    }

    .btn-swal-primary {
        background-color: #022F55;
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: .5rem 1.5rem;
        transition: .2s ease;
    }

    .btn-swal-primary:hover {
        background-color: #011f3a;
    }
</style>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container-fluid">
    <div class="my-4 text-center">
        <h4 class="mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 700 !important;">
            GESTIÓN DE PAGOS
        </h4>
        <small>Subir Soporte de Pago</small>
    </div>

    <div class="row mt-4">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="d-flex justify-content-center align-items-center me-3 rounded-5 p-2"
                            style="width: 35px; height: 35px; background-color: #ffffffff;">
                            <i class="fa-solid fa-file-invoice-dollar" style="color: #002F55;"></i>
                        </div>
                        <div>
                            <div class="text-start text-white" style="font-size: 1.2em; font-weight: 700;">
                                Soporte de pago
                            </div>
                            <div style="font-size: 70%; color: #f3f8fdff;" class="text-start">
                                Carga y seguimiento del soporte asociado a cada solicitud
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
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">Nombres</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">N° identidad</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Días</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Valor día</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Total</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Actividad</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Estado</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Cargar soporte</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Marcar como pago</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql2 = "
                                    SELECT 
                                        id,
                                        sb_baqueano_nombre,
                                        sb_baqueano_apellido,
                                        sb_numero_identidad,
                                        sb_dias_calculados,
                                        sb_cobro_diario,
                                        sb_valor_cobrar,
                                        sb_tipo_actividad,
                                        sb_estado_pagos,
                                        sb_estado_final_pago
                                    FROM solicitud_baqueanos
                                    WHERE UPPER(TRIM(sb_estado_final_pago)) IN ('APROBADO','PAGADO')
                                    AND (sb_estado_final_pago IS NULL OR sb_estado_final_pago != 'PAGADO')
                                    ORDER BY id ASC
                                ";
                                $stmt = $mysqli->prepare($sql2);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result->num_rows > 0):
                                    while ($row = $result->fetch_assoc()):
                                        $id = (int)$row['id'];
                                        $radicado = "ARB_" . $id;
                                        $estado = $row['sb_estado_final_pago'] ?? '';
                                        $badge = badgeEstado($estado);
                                ?>
                                        <tr>
                                            <td class="text-nowrap">
                                                <a href="index.php?page=baqueanos/cuentas/vistas/informacion_solicitud_radicacion&id=<?= urlencode($row['id']) ?>"
                                                    class="rad btn-link">
                                                    <?= htmlspecialchars($radicado) ?>
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center">
                                                    <div class="user-info">
                                                        <div class="user-name"><?= htmlspecialchars($row['sb_baqueano_nombre']) ?></div>
                                                        <div class="user-lastname"><?= htmlspecialchars($row['sb_baqueano_apellido']) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-nowrap"><?= htmlspecialchars($row['sb_numero_identidad']) ?></td>
                                            <td class="text-nowrap"><?= htmlspecialchars($row['sb_dias_calculados']) ?></td>
                                            <td class="text-nowrap">$<?= number_format((float)$row['sb_cobro_diario'], 0, ',', '.') ?></td>
                                            <td class="text-nowrap">$<?= number_format((float)$row['sb_valor_cobrar'], 0, ',', '.') ?></td>
                                            <td class="text-truncate" style="max-width:160px;"><?= htmlspecialchars($row['sb_tipo_actividad']) ?></td>
                                            <td class="text-center text-nowrap">
                                                <span class="badge bg-<?= htmlspecialchars($badge) ?>">
                                                    <?= htmlspecialchars($estado) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button
                                                    type="button"
                                                    class="btn btn-link p-0"
                                                    style="color:#002F55; text-decoration:none;"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalSoportePago"
                                                    data-id="<?= htmlspecialchars($id) ?>"
                                                    data-radicado="<?= htmlspecialchars($radicado) ?>"
                                                    title="Cargar soporte"
                                                    aria-label="Cargar soporte">
                                                    <i class="fa-solid fa-upload"></i>
                                                </button>
                                            </td>
                                            <td class="text-center">
                                                <?php if (strtoupper(trim((string)$estado)) !== 'PAGADO'): ?>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-success px-3 btnMarcarPago"
                                                        data-id="<?= htmlspecialchars($id) ?>"
                                                        data-radicado="<?= htmlspecialchars($radicado) ?>">
                                                        <i class="fa-solid fa-circle-check me-1"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <span class="aviss text-nowrap">PAGADO</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php
                                    endwhile;
                                else:
                                    ?>
                                    <tr>
                                        <td colspan="10" class="text-center">No se encontraron registros disponibles</td>
                                    </tr>
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
<div class="modal fade" id="modalSoportePago" tabindex="-1" aria-labelledby="modalSoportePagoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-md">
        <div class="modal-content shadow">
            <form id="formSoportePago" method="POST" enctype="multipart/form-data"
                action="./vistas/baqueanos/solicitudes/acciones/cargar_soporte_pago.php">
                <div class="modal-header text-white justify-content-center position-relative" style="background-color:#002F55;">
                    <div class="text-center">
                        <h5 class="modal-title mb-0" id="modalSoportePagoLabel">Cargar soporte de pago</h5>
                        <small class="text-white-50">Radicado: <b class="text-white" id="modalRadicado">-</b></small>
                    </div>

                    <button type="button"
                        class="btn-close btn-close-white position-absolute end-0 top-50 translate-middle-y me-3"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_solicitud" id="modalIdSolicitud" value="">
                    <div class="mb-3" id="seccionDocumentosCargados" style="display:none;">
                        <div class="fw-semibold small mb-2" style="color:#002F55;">
                            <i class="fa-solid fa-file-check me-2"></i>Documentos cargados
                        </div>
                        <div class="border rounded-3 bg-light p-2" style="max-height:180px; overflow-y:auto;">
                            <div id="listaDocumentosCargados"></div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Documento</label>
                        <div class="border rounded-3 p-3 bg-light">
                            <label for="archivoSoporte" class="w-100 mb-0" style="cursor:pointer;">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="fs-3" style="color:#002F55;">
                                        <i class="fa-solid fa-file-arrow-up"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">Haz clic aquí para seleccionar el archivo</div>
                                        <div class="text-muted small">PDF o imagen</div>
                                    </div>
                                </div>
                            </label>
                            <input class="form-control d-none"
                                type="file"
                                id="archivoSoporte"
                                name="archivo_soporte"
                                accept=".pdf,image/*"
                                required>
                            <div class="small mt-2">
                                <span class="text-muted">Archivo seleccionado:</span>
                                <span class="fw-semibold" id="nombreArchivo">Ninguno</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3" id="previewContainer" style="display:none;">
                        <div class="fw-semibold small mb-1">Vista previa</div>
                        <div class="border rounded-3 overflow-hidden bg-white" style="height:220px;">
                            <iframe id="previewPdf" style="display:none; width:100%; height:100%;" frameborder="0"></iframe>
                            <img id="previewImg" style="display:none; width:100%; height:100%; object-fit:contain;" alt="Vista previa">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm" style="background:#002F55; border-color:#002F55;">
                        <i class="fa-solid fa-cloud-arrow-up me-1"></i> Subir
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
<script src="https://kit.fontawesome.com/9944c94262.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                [5, 10, 25, 50],
                [5, 10, 25, 50]
            ],
        });
    });

    const modal = document.getElementById('modalSoportePago');
    modal.addEventListener('show.bs.modal', async function(event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const radicado = button.getAttribute('data-radicado');

        document.getElementById('modalIdSolicitud').value = id;
        document.getElementById('modalRadicado').textContent = radicado;
        document.getElementById('nombreArchivo').textContent = 'Ninguno';
        document.getElementById('archivoSoporte').value = '';

        resetPreview();

        await cargarDocumentosCargados(id);
    });

    async function cargarDocumentosCargados(idSolicitud) {
        try {
            const resp = await fetch('./vistas/baqueanos/solicitudes/acciones/obtener_documentos_soporte.php?id_solicitud=' + idSolicitud);
            const data = await resp.json();

            const seccion = document.getElementById('seccionDocumentosCargados');
            const lista = document.getElementById('listaDocumentosCargados');

            if (data.success && data.documentos && data.documentos.length > 0) {
                seccion.style.display = 'block';
                lista.innerHTML = data.documentos.map(doc => `
                    <div class="d-flex align-items-center justify-content-between p-2 mb-2 bg-white rounded-2 border border-light">
                        <div class="d-flex align-items-center gap-2 flex-grow-1 min-width-0">
                            <div style="color:#002F55; font-size:14px;">
                                ${obtenerIconoArchivo(doc.nombre)}
                            </div>
                            <div class="flex-grow-1 min-width-0">
                                <div class="text-truncate mb-0" style="font-size:12px; font-weight:600; color:#002F55;">
                                    ${doc.nombre}
                                </div>
                                <div style="font-size:10px; color:#6b7280;">
                                    ${doc.tamaño_formato} • ${doc.fecha}
                                </div>
                            </div>
                        </div>
                        <a href="./vistas/baqueanos/solicitudes/acciones/descargar_soporte.php?id_solicitud=${idSolicitud}&archivo=${encodeURIComponent(doc.nombre)}"
                            class="btn btn-sm btn-link p-0 ms-2"
                            title="Descargar"
                            style="color:#002F55; text-decoration:none;">
                            <i class="fa-solid fa-download"></i>
                        </a>
                    </div>
                `).join('');
            } else {
                seccion.style.display = 'none';
            }
        } catch (err) {
            console.error('Error cargando documentos:', err);
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


    let previewObjectUrl = null;

    function resetPreview() {
        const container = document.getElementById('previewContainer');
        const iframe = document.getElementById('previewPdf');
        const img = document.getElementById('previewImg');

        container.style.display = 'none';
        iframe.style.display = 'none';
        img.style.display = 'none';

        iframe.src = '';
        img.src = '';

        if (previewObjectUrl) {
            URL.revokeObjectURL(previewObjectUrl);
            previewObjectUrl = null;
        }
    }

    function setPreviewFromFile(file) {
        const container = document.getElementById('previewContainer');
        const iframe = document.getElementById('previewPdf');
        const img = document.getElementById('previewImg');

        resetPreview();

        if (!file) return;

        previewObjectUrl = URL.createObjectURL(file);
        container.style.display = 'block';

        if (file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf')) {
            iframe.src = previewObjectUrl;
            iframe.style.display = 'block';
            return;
        }

        if (file.type.startsWith('image/')) {
            img.src = previewObjectUrl;
            img.style.display = 'block';
            return;
        }

        container.style.display = 'none';
    }

    document.getElementById('archivoSoporte').addEventListener('change', function() {
        const file = (this.files && this.files.length) ? this.files[0] : null;
        document.getElementById('nombreArchivo').textContent = file ? file.name : 'Ninguno';
        setPreviewFromFile(file);
    });

    document.getElementById('formSoportePago').addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = this;
        const fileInput = document.getElementById('archivoSoporte');
        const idSolicitud = document.getElementById('modalIdSolicitud').value;

        if (!idSolicitud) {
            Swal.fire({
                customClass: {
                    popup: 'swal-pro',
                    container: 'pro-backdrop'
                },
                showConfirmButton: false,
                background: 'transparent',
                html: `
                    <div class="swal-pro-card">
                        <div class="swal-pro-header" style="color:#dc3545;">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            Atención
                        </div>
                        <div class="swal-pro-divider"></div>
                        <div class="swal-pro-body">
                            <div class="swal-pro-text">
                                No se encontró el ID de la solicitud.
                            </div>
                            <div class="mt-4">
                                <button onclick="Swal.close()" class="btn-swal-primary">
                                    Entendido
                                </button>
                            </div>
                        </div>
                    </div>`
            });
            return;
        }
        if (!fileInput.files || !fileInput.files.length) {
            Swal.fire('Atención', 'Debes seleccionar un archivo antes de subir.', 'warning');
            return;
        }

        const confirmacion = await Swal.fire({
            customClass: {
                popup: 'swal-pro',
                container: 'pro-backdrop'
            },
            showConfirmButton: false,
            background: 'transparent',
            html: `
                <div class="swal-pro-card">
                    <div class="swal-pro-header" style="color:#002F55;">
                        <i class="fa-solid fa-upload"></i>
                        ¿Confirmar carga?
                    </div>
                    <div class="swal-pro-divider"></div>
                    <div class="swal-pro-body">
                        <div class="swal-pro-text">
                            Se subirá el documento y se asociará a la solicitud seleccionada.
                        </div>
                        <div class="mt-4 d-flex justify-content-center gap-3">
                            <button onclick="Swal.close()" class="btn btn-outline-secondary">
                                Cancelar
                            </button>
                            <button onclick="Swal.clickConfirm()" class="btn-swal-primary">
                                Sí, subir
                            </button>
                        </div>
                    </div>
                </div>`,
            showCancelButton: false,
            preConfirm: () => true
        });
        if (!confirmacion.isConfirmed) return;

        try {
            Swal.fire({
                customClass: {
                    popup: 'swal-pro',
                    container: 'pro-backdrop'
                },
                background: 'transparent',
                showConfirmButton: false,
                allowOutsideClick: false,
                html: `
                    <div class="swal-pro-card text-center">
                        <div class="swal-pro-header">
                            <i class="fa-solid fa-spinner fa-spin"></i>
                            Procesando
                        </div>
                        <div class="swal-pro-divider"></div>
                        <div class="swal-pro-body">
                            <div class="swal-pro-text">
                                Por favor espera...
                            </div>
                        </div>
                    </div>`
            });

            const formData = new FormData(form);
            const resp = await fetch(form.action, {
                method: 'POST',
                body: formData
            });

            const data = await resp.json();

            if (data && data.success) {
                const modalEl = document.getElementById('modalSoportePago');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();

                await Swal.fire({
                    customClass: {
                        popup: 'swal-pro',
                        container: 'pro-backdrop'
                    },
                    showConfirmButton: false,
                    background: 'transparent',
                    html: `
                    <div class="swal-pro-card">
                        <div class="swal-pro-header text-success">
                            <i class="fa-solid fa-circle-check"></i>
                            Éxito
                        </div>
                        <div class="swal-pro-divider"></div>
                        <div class="swal-pro-body">
                            <div class="swal-pro-text">
                                ${data.message || 'Operación realizada correctamente.'}
                            </div>
                            <div class="mt-4">
                                <button onclick="Swal.close()" class="btn-swal-primary">
                                    Aceptar
                                </button>
                            </div>
                        </div>
                    </div>`
                });
                location.reload();
            } else {
                Swal.fire('Error', (data && data.message) ? data.message : 'Ocurrió un error al subir el soporte.', 'error');
            }
        } catch (err) {
            Swal.fire('Error', 'No se pudo procesar la respuesta del servidor.', 'error');
            console.error(err);
        }
    });

    document.addEventListener('click', async function(e) {
        const btn = e.target.closest('.btnMarcarPago');
        if (!btn) return;

        const id = btn.getAttribute('data-id');
        const radicado = btn.getAttribute('data-radicado');

        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const mins = String(now.getMinutes()).padStart(2, '0');
        const fechaActual = `${year}-${month}-${day}T${hours}:${mins}`;

        const confirmacion = await Swal.fire({
            icon: 'warning',
            title: 'Confirmar registro de pago',
            width: 420,
            html: `
            <div style="font-size:13px; margin-bottom:12px;">
                Radicado:
                <div style="font-weight:700; color:#002F55; font-size:15px;">
                    ${radicado}
                </div>
            </div>

            <div style="text-align:left; margin-top:12px;">
                <label style="font-size:12px; font-weight:600; color:#002F55;">
                    Fecha y hora
                </label>
                <input 
                    type="datetime-local"
                    id="fechaHoraPago"
                    value="${fechaActual}"
                    class="swalFechaPago"
                >
            </div>

            <div style="text-align:left; margin-top:14px;">
                <label style="font-size:12px; font-weight:600; color:#002F55;">
                    Medio de pago
                </label>
                <select id="medioPago" class="swalFechaPago">
                    <option value="">Seleccione...</option>
                    <option value="EFECTIVO">Efectivo/lider/social</option>
                    <option value="CONSIGNACION_BANCOLOLOMBIA">Consignación bancolombia</option>
                    <option value="CONSIGNACION_AHORRO_A_LA_MANO">Consignación ahorro a la mano</option>
                    <option value="CONSIGNACION_DAVIVIENDA">Consignación davivienda</option>
                    <option value="CONSIGNACION_DAVIPLATA">Consignación daviplata</option>
                    <option value="NEQUI">Nequi</option>
                    <option value="CONSIGNACION_BANCO_AGRARIO">Consignación banco agrario</option>
                    <option value="GIRO_SUCHANCE">Giro suchance</option>
                    <option value="TARJETA_CREDITO">Tarjeta crédito</option>
                    <option value="GIRO_SERVIENTREGA">Giro servientrega</option>
                    <option value="EFECTY">Efecty</option>
                </select>
            </div>

            <div style="font-size:11px; color:#6b7280; margin-top:12px;">
                Esta acción actualizará el estado y notificará al solicitante.
            </div>
        `,
            showCancelButton: true,
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            confirmButtonColor: '#002F55',
            customClass: {
                popup: 'swalAlertaPago'
            },
            preConfirm: () => {
                const fechaHora = document.getElementById('fechaHoraPago').value;
                const medioPago = document.getElementById('medioPago').value;

                if (!fechaHora) {
                    Swal.showValidationMessage('Debes seleccionar fecha y hora');
                    return false;
                }

                if (!medioPago) {
                    Swal.showValidationMessage('Debes seleccionar un medio de pago');
                    return false;
                }

                return {
                    fechaHora,
                    medioPago
                };
            }
        });

        if (!confirmacion.isConfirmed) return;

        try {
            Swal.fire({
                customClass: {
                    popup: 'swal-pro',
                    container: 'pro-backdrop'
                },
                background: 'transparent',
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                html: `
                <div class="swal-pro-card text-center">
                    <div class="swal-pro-header">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        Registrando pago
                    </div>
                    <div class="swal-pro-divider"></div>
                    <div class="swal-pro-body">
                        <div class="swal-pro-text">
                            Por favor espera mientras procesamos la información...
                        </div>
                    </div>
                </div>`
            });

            const fd = new FormData();
            fd.append('id_solicitud', id);
            fd.append('fecha_hora_pago', confirmacion.value.fechaHora);
            fd.append('sb_medio_pago', confirmacion.value.medioPago);

            const resp = await fetch('./vistas/baqueanos/solicitudes/acciones/marcar_como_pagado.php', {
                method: 'POST',
                body: fd
            });

            const data = await resp.json();
            if (data && data.success) {
                await Swal.fire({
                    customClass: {
                        popup: 'swal-pro',
                        container: 'pro-backdrop'
                    },
                    showConfirmButton: false,
                    background: 'transparent',
                    html: `
                    <div class="swal-pro-card">
                        <div class="swal-pro-header text-success">
                            <i class="fa-solid fa-circle-check"></i>
                            Pago registrado
                        </div>
                        <div class="swal-pro-divider"></div>
                        <div class="swal-pro-body">
                            <div class="swal-pro-text">
                                La solicitud fue marcada correctamente.
                            </div>
                            <div class="mt-4">
                                <button onclick="Swal.close()" class="btn-swal-primary">
                                    Aceptar
                                </button>
                            </div>
                        </div>
                    </div>`
                });

                location.reload();

            } else {
                Swal.fire({
                    customClass: {
                        popup: 'swal-pro',
                        container: 'pro-backdrop'
                    },
                    showConfirmButton: false,
                    background: 'transparent',
                    html: `
                    <div class="swal-pro-card">
                        <div class="swal-pro-header text-danger">
                            <i class="fa-solid fa-circle-xmark"></i>
                            Error
                        </div>
                        <div class="swal-pro-divider"></div>
                        <div class="swal-pro-body">
                            <div class="swal-pro-text">
                                ${data.message || 'No se pudo registrar el pago.'}
                            </div>
                            <div class="mt-4">
                                <button onclick="Swal.close()" class="btn-swal-primary">
                                    Entendido
                                </button>
                            </div>
                        </div>
                    </div>`
                });
            }

        } catch (err) {

            console.error(err);

            Swal.fire({
                customClass: {
                    popup: 'swal-pro',
                    container: 'pro-backdrop'
                },
                showConfirmButton: false,
                background: 'transparent',
                html: `
                <div class="swal-pro-card">
                    <div class="swal-pro-header text-danger">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        Error de comunicación
                    </div>
                    <div class="swal-pro-divider"></div>
                    <div class="swal-pro-body">
                        <div class="swal-pro-text">
                            Ocurrió un problema al comunicarse con el servidor.
                        </div>
                        <div class="mt-4">
                            <button onclick="Swal.close()" class="btn-swal-primary">
                                Entendido
                            </button>
                        </div>
                    </div>
                </div>`
            });

        }
    });
</script>