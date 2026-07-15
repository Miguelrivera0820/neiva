<?php
$sql2 = "SELECT 
            id,
            tipo_contactanos,
            nombre_completo,
            correo, 
            numero_telefono,
            departamento,
            municipio,
            asunto_contactanos,
            duda_contactanos,
            imagen,
            documento_respuesta,
            estado_respuesta
        FROM contactanos";

$result = $mysqli->query($sql2);
if (!$result) {
    die("Error en la consulta: " . $mysqli->error);
}
?>
<style>
    .eye-modal {
        background: none;
        border: none;
        color: #002F55;
        padding: 0;
        cursor: pointer;
    }

    .eye-modal i {
        font-size: 15px;
        color: #002f55;
        transition: all 0.25s ease;
    }

    .eye-modal:hover i {
        transform: scale(1.25);
        opacity: 1;
    }

    .eye-modal.step-btn.active i {
        transform: scale(1.2);
    }

    .modal-title {
        background-color: #002F55;
        color: #ffffffff;
    }

    .asunto-container {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 15px;
        background: #f8f9fa;
    }

    .asunto-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .asunto-content {
        margin-top: 5%;
        text-align: center;
        max-height: 50vh;
        overflow-y: auto;
        white-space: pre-wrap;
        word-break: break-word;
        padding-right: 5px;
        font-size: 14px;
        line-height: 1.6;
    }

    .badge-title {
        background-color: #dde3ea;
        border-radius: 20px;
        padding: 5px;
        width: 80px;
        margin: 3px;
        text-align: center;
    }

    .asunto-content::-webkit-scrollbar {
        width: 6px;
    }

    .asunto-content::-webkit-scrollbar-thumb {
        background-color: #002F55;
        border-radius: 10px;
    }

    .modal-eye {
        background: none;
        border: none;
        color: #002F55;
        cursor: pointer;
        padding: 0;
    }

    .modal-eye i {
        font-size: 15px;
        color: #002f55;
        transition: all 0.25s ease;
    }

    .modal-eye:hover i {
        transform: scale(1.2);
        opacity: 1;
    }

    .modal-eye.step-btn.active i {
        transform: scale(1.2);
    }
</style>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container-fluid">
    <div class="my-4 text-center">
        <h4 class="mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 700 !important;">
            MÓDULO CONTACTANOS
        </h4>
        <small>MODULO PARA LA GESTION DE SOLICITUDES DE USUARIOS CONTACTANOS</small>
    </div>
    <div class="card shadow mb-4" style="border-radius:15px;">
        <div class="card-header py-3 text-center d-flex flex-row align-items-center justify-content-between"
            style="background: linear-gradient(355deg, #074a85ff, #002f55); border-radius:0px 15px 0px 0px;">
            <div class="d-flex align-items-center">
                <div class="d-flex justify-content-center align-items-center me-3 rounded-5 p-2"
                    style="width: 35px; height: 35px; background-color: #ffffffff;">
                    <i class="bi bi-clipboard-plus" style="color: #002F55;"></i>
                </div>
                <div>
                    <div class="text-start text-white" style="font-size: 1em; font-weight: 700;">
                        SOLICITUDES CONTACTANOS
                    </div>
                    <div style="font-size: 70%; color: #f3f8fdff;" class="text-start">
                        Lista de todas las solicitudes recibidas a través del formulario de Contactanos.
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="datatable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th style="text-align:center; vertical-align:middle; font-size:14px">Tipo</th>
                            <th style="text-align:center; vertical-align:middle; font-size:14px">Nombre completo</th>
                            <th style="text-align:center; vertical-align:middle; font-size:14px">Correo</th>
                            <th style="text-align:center; vertical-align:middle; font-size:14px">Teléfono</th>
                            <th style="text-align:center; vertical-align:middle; font-size:14px">Departamento</th>
                            <th style="text-align:center; vertical-align:middle; font-size:14px">Municipio</th>
                            <th style="text-align:center; vertical-align:middle; font-size:14px">Asunto</th>
                            <th style="text-align:center; vertical-align:middle; font-size:14px">Duda</th>
                            <th style="text-align:center; vertical-align:middle; font-size:14px">Estado</th>
                            <th style="text-align:center; vertical-align:middle; font-size:14px">Respuesta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td style="text-align:center; vertical-align:middle; font-size:13px">
                                        <?= htmlspecialchars($row["tipo_contactanos"]) ?>
                                    </td>
                                    <td style="text-align:center; vertical-align:middle; font-size:13px">
                                        <?= htmlspecialchars($row["nombre_completo"]) ?>
                                    </td>
                                    <td style="text-align:center; vertical-align:middle; font-size:13px">
                                        <?= htmlspecialchars($row["correo"]) ?>
                                    </td>
                                    <td style="text-align:center; vertical-align:middle; font-size:13px">
                                        <?= htmlspecialchars($row["numero_telefono"]) ?>
                                    </td>
                                    <td style="text-align:center; vertical-align:middle; font-size:13px">
                                        <?= htmlspecialchars($row["departamento"]) ?>
                                    </td>
                                    <td style="text-align:center; vertical-align:middle; font-size:13px">
                                        <?= htmlspecialchars($row["municipio"]) ?>
                                    </td>
                                    <td class="text-center aling-middle">
                                        <button
                                            type="button"
                                            class="eye-modal"
                                            data-bs-toggle="modal"
                                            data-bs-target="#asuntoModal"
                                            data-asunto="<?= htmlspecialchars($row['asunto_contactanos']) ?>">
                                            <i class="fa-regular fa-eye eye-icon"></i>
                                        </button>
                                    </td>
                                    <td class="text-center aling-middle">
                                        <button
                                            type="button"
                                            class="modal-eye"
                                            data-bs-toggle="modal"
                                            data-bs-target="#dudaModal"
                                            data-duda="<?= htmlspecialchars($row['duda_contactanos']) ?>">
                                            <i class="fa-regular fa-eye eye-icon"></i>
                                        </button>
                                    </td>
                                    <!-- <td style="text-align:center; vertical-align:middle; font-size:13px">
                                        <?= htmlspecialchars($row["duda_contactanos"]) ?>
                                    </td> -->
                                    <?php
                                    $estado = strtoupper(trim($row["estado_respuesta"] ?? ''));

                                    $color = match ($estado) {
                                        'PENDIENTE'  => '#f1c40f',
                                        'CONTESTADA' => '#2ecc71',
                                        default      => '#7f8c8d',
                                    };
                                    ?>
                                    <td style="text-align:center; vertical-align:middle; font-size:13px; font-weight:600; color: <?= $color ?>;">
                                        <?= htmlspecialchars($row["estado_respuesta"] ?? '') ?>
                                    </td>
                                    <td style="text-align:center; vertical-align:middle; font-size:13px">
                                        <?php
                                        $img = isset($row['imagen']) ? trim((string)$row['imagen']) : '';
                                        $doc = isset($row['documento_respuesta']) ? trim((string)$row['documento_respuesta']) : '';
                                        $tieneRespuesta = ($img !== '' || $doc !== '');
                                        $iconColor = $tieneRespuesta ? '#198754' : '#0d6efd';
                                        $bgColor   = $tieneRespuesta ? '#eaf7ef' : '#f3f8fd';
                                        $border    = $tieneRespuesta ? '#bfe8cc' : '#d7e6f6';
                                        $title     = $tieneRespuesta ? 'Ya hay respuesta cargada (clic para reemplazar)' : 'Cargar archivo';
                                        ?>
                                        <a href="#"
                                            title="<?= $title ?>"
                                            class="text-decoration-none"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalCargar<?= (int)$row['id'] ?>"
                                            style="display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:50%; background:<?= $bgColor ?>; border:1px solid <?= $border ?>;">
                                            <i class="bi bi-cloud-arrow-up-fill" style="font-size:18px; color:<?= $iconColor ?>;"></i>
                                        </a>
                                        <div class="modal fade"
                                            id="modalCargar<?= (int)$row['id'] ?>"
                                            tabindex="-1"
                                            aria-labelledby="modalCargarLabel<?= (int)$row['id'] ?>"
                                            aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content" style="border-radius:16px;">
                                                    <form action="./vistas/noticias/acciones/subir_respuesta_contactanos.php" method="POST" enctype="multipart/form-data">
                                                        <div class="modal-header">
                                                            <div>
                                                                <h5 class="modal-title fw-bold" id="modalCargarLabel<?= (int)$row['id'] ?>">Cargar archivo</h5>
                                                                <div class="text-muted" style="font-size:.9rem;">
                                                                    Solicitud #<?= (int)$row['id'] ?> · <?= htmlspecialchars($row['nombre_completo']) ?>
                                                                </div>
                                                            </div>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="contactanos_id" value="<?= (int)$row['id'] ?>">
                                                            <?php
                                                            $img = isset($row['imagen']) ? trim((string)$row['imagen']) : '';
                                                            $doc = isset($row['documento_respuesta']) ? trim((string)$row['documento_respuesta']) : '';
                                                            $imgEsc = htmlspecialchars($img, ENT_QUOTES, 'UTF-8');
                                                            $docEsc = htmlspecialchars($doc, ENT_QUOTES, 'UTF-8');

                                                            $docExt = $doc !== '' ? strtolower(pathinfo($doc, PATHINFO_EXTENSION)) : '';
                                                            $esPdf  = ($docExt === 'pdf');
                                                            ?>

                                                            <?php if ($img !== '' || $doc !== ''): ?>
                                                                <div class="mb-3 p-2 rounded-3" style="background:#f8fafc; border:1px solid #e5e7eb;">
                                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                                        <div class="fw-semibold" style="font-size:.95rem;">Respuesta cargada</div>
                                                                        <span class="badge text-bg-success" style="font-size:.75rem;">CONTESTADA</span>
                                                                    </div>

                                                                    <div class="row g-2 align-items-start">
                                                                        <div class="col-12 col-md-7">
                                                                            <?php if ($img !== ''): ?>
                                                                                <a href="<?= $imgEsc ?>" target="_blank" class="d-block">
                                                                                    <img
                                                                                        src="<?= $imgEsc ?>"
                                                                                        alt="Respuesta (imagen)"
                                                                                        style="width:100%; max-height:170px; object-fit:contain; border-radius:10px; border:1px solid #dde3ea; background:white;">
                                                                                </a>
                                                                            <?php elseif ($doc !== '' && $esPdf): ?>
                                                                                <div style="height:170px; border-radius:10px; overflow:hidden; border:1px solid #dde3ea; background:white;">
                                                                                    <iframe src="<?= $docEsc ?>" title="Documento PDF" style="width:100%; height:100%; border:0;"></iframe>
                                                                                </div>
                                                                            <?php else: ?>
                                                                                <div class="p-2 rounded-3 d-flex align-items-center gap-2"
                                                                                    style="border:1px solid #dde3ea; background:white;">
                                                                                    <i class="bi bi-file-earmark-text" style="font-size:1.2rem;"></i>
                                                                                    <div class="small">
                                                                                        <div class="fw-semibold">Archivo cargado</div>
                                                                                        <div class="text-muted">.<?= htmlspecialchars($docExt, ENT_QUOTES, 'UTF-8') ?></div>
                                                                                    </div>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                        </div>

                                                                        <div class="col-12 col-md-5">
                                                                            <div class="small text-muted mb-2">Acciones</div>

                                                                            <?php if ($img !== ''): ?>
                                                                                <a class="btn btn-sm btn-outline-primary w-100 mb-2" href="<?= $imgEsc ?>" target="_blank">
                                                                                    Abrir imagen
                                                                                </a>
                                                                            <?php endif; ?>

                                                                            <?php if ($doc !== ''): ?>
                                                                                <a class="btn btn-sm btn-outline-primary w-100 mb-2" href="<?= $docEsc ?>" target="_blank">
                                                                                    <?= $esPdf ? 'Abrir PDF' : 'Descargar / Abrir' ?>
                                                                                </a>
                                                                            <?php endif; ?>

                                                                            <div class="small text-muted" style="line-height:1.2;">
                                                                                Si subes un nuevo archivo, se <b>reemplazará</b>.
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endif; ?>
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label fw-semibold">Tipo de archivo</label>
                                                                    <select name="tipo_archivo" class="form-select" required>
                                                                        <option value="">Seleccione…</option>
                                                                        <option value="foto">Foto</option>
                                                                        <option value="documento">Documento</option>
                                                                    </select>
                                                                    <div class="form-text">Foto: jpg/png/webp · Documento: pdf/doc/docx/xls/xlsx</div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label fw-semibold">Archivo</label>
                                                                    <input type="file" name="archivo" class="form-control" required>
                                                                    <div class="form-text">Máx: 5MB (foto) / 10MB (documento)</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                            <button type="submit" class="btn btn-success">Subir</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">No hay registros.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="asuntoModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header position-relative flex-colum text-center" style="background-color:#002F55;">
                <div class="w-100">
                    <h5 class="modal-title m-0 text-white">
                        Detalle del asunto
                    </h5>
                    <small class="text-white-50">
                        Detalle de la solcitud contactanos
                    </small>
                </div>
                <button type="button"
                    class="btn-close btn-close-white position-absolute end-0 top-0 mt-3 me-3"
                    data-bs-dismiss="modal">
                </button>
            </div>
            <div class="modal-body">
                <div class="asunto-container">
                    <div class="asunto-header">
                        <span class="badge-title">
                            Asunto
                        </span>
                        <small class="charCount text-muted"></small>
                    </div>
                    <div class="asunto-content" id="modalAsuntoContent"></div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="dudaModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header position-relative flex-colum text-center" style="background-color:#002F55;">
                <div class="w-100">
                    <h5 class="modal-title m-0 text-white">
                        Detalle de la duda
                    </h5>
                    <small class="text-white-50">
                        Detalle de la solicitud contactanos
                    </small>
                </div>
                <button
                    type="button"
                    class="btn-close btn-close-white
                            position-absolute end-0 top-0 mt-3 me-3"
                    data-bs-dismiss="modal">
                </button>
            </div>
            <div class="modal-body">
                <div class="asunto-container">
                    <div class="asunto-header">
                        <span class="badge-title">
                            Duda
                        </span>
                        <small class="charCount text-muted"></small>
                    </div>
                    <div class="asunto-content" id="modalDudaContent"></div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<script>
    $(document).ready(function() {
        $('#datatable').DataTable({
            searching: true,
            lengthChange: true,
            pageLength: 10,
            order: [
                [0, 'asc']
            ],
            language: {
                search: "Buscar:",
                lengthMenu: "Mostrar _MENU_ registros",
                zeroRecords: "No se encontraron resultados",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                infoEmpty: "Mostrando 0 a 0 de 0 registros",
                infoFiltered: "(filtrado de _MAX_ registros en total)",
                paginate: {
                    first: "Primero",
                    last: "Último",
                    next: "Siguiente",
                    previous: "Anterior"
                }
            }
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        function configurarModal(modalId, dataAttr, contentId) {
            const modal = document.getElementById(modalId);
            let activeButton = null;

            modal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                activeButton = button;
                const texto = button.getAttribute(dataAttr);
                const content = modal.querySelector(`#${contentId}`);
                const counter = modal.querySelector('.charCount');
                content.textContent = texto;
                counter.textContent = texto.length + " caracteres";
                const icon = button.querySelector('.eye-icon');
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');

            });
            modal.addEventListener('hidden.bs.modal', function() {

                if (activeButton) {
                    const icon = activeButton.querySelector('.eye-icon');
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
                activeButton = null;
            });
        }
        configurarModal('asuntoModal', 'data-asunto', 'modalAsuntoContent');
        configurarModal('dudaModal', 'data-duda', 'modalDudaContent');
    });
</script>