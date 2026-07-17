<?php
$where = "";
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}

$nombre    = $_SESSION['id_usuario'];
$idUsuario = $_SESSION['id_usuario'];

// Consulta de cuentas rechazadas
$sql = "SELECT 
            cr.id,
            cr.numero_identidad, 
            cr.razon, 
            cr.fecha_rechazo, 
            cr.primer_nombre, 
            cr.segundo_nombre, 
            cr.primer_apellido, 
            cr.segundo_apellido,
            cr.rec_anio_cuenta,
            u.nombre_usuario AS nombre, 
            u.apellido_usuario AS apellido
        FROM cuentas_rechazadas cr
        LEFT JOIN usuarios_cons u ON cr.usuario_rechazo_id = u.id_usuario
        ORDER BY cr.fecha_rechazo ASC";

$result = $mysqli->query($sql);
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<style>
    :root {
        --color-primario: #022F55;
        --color-primario-suave: #f1f5f9;
        --color-borde-suave: #e5e7eb;
        --color-texto-muted: #6b7280;
    }

    .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
        background-color: #EF9A10 !important;
        border-color: #EF9A10 !important;
        color: #fff !important;
    }

    .dataTables_wrapper .dataTables_paginate .page-link:hover {
        background-color: #ef991091 !important;
        color: #fff !important;
    }

    .dataTables_wrapper .dataTables_paginate .page-link {
        color: #00070cff !important;
        border-radius: 8px;
        margin: 0 2px;
    }

    .col-toggle-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.3rem 0.8rem;
        border-radius: 9px;
        background-color: #ffffffaf;
        color: #002F55;
        font-weight: 500;
        border: 1px solid #2E7D32;
    }

    .col-toggle-pill {
        display: inline-flex;
        gap: 3px;
        align-items: center;
        padding: 0.15rem 0.55rem;
        color: #fff;
        border-radius: 9px;
        border: 1px solid var(--color-borde-suave);
        background-color: #ffffff02;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
        font-weight: 500;
        transition: background-color 0.25s ease, color 0.25s ease;
    }

    .col-toggle-pill input[type="checkbox"] {
        margin-right: 4px;
        cursor: pointer;

    }

    .col-toggle-pill span {
        cursor: pointer;
    }

    .col-toggle-pill:hover {
        background-color: #ffffffff;
        border-color: #cbd5f5;
        color: #002F55;
    }

    .col-toggle-pill input[type="checkbox"]:checked+span {
        color: var(--color-primario);
        font-weight: 600;
    }

    .col-toggle-pill input[type="checkbox"]:checked+span:hover {
        color: var(--color-primario);
        font-weight: 600;
    }

    .toogle-col {
        background-color: #ffffffff !important;
    }

    .col-toggle-pill:has(input[type="checkbox"]:checked) {
        background-color: #ffffffff;
        border: 1px solid #ffffffff;
        color: #2E7D32;
    }

    .btn-eye-toggle {
        border: none;
        background: transparent;
        padding: 0.15rem 0.35rem;
        cursor: pointer;
    }

    .btn-eye-toggle:focus {
        outline: none;
        box-shadow: none;
    }

    .eye-icon {
        font-size: 1.2rem;
        color: #6b7280;
        transition: transform 0.2s ease, color 0.2s ease;
    }

    .btn-eye-toggle:hover .eye-icon {
        transform: scale(1.1);
        color: #EF9A10;
    }

    .eye-icon.eye-open {
        color: #EF9A10;
        transform: scale(1.2);
    }

    #modalRazon .modal-dialog {
        max-width: 650px;
    }

    .modal-content-razon {
        border-radius: 18px;
        border: none;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.25);
        overflow: hidden;
    }

    .modal-header-razon {
        background: linear-gradient(133deg, #ffa726, #fb8c00);
        color: #fff;
        border-bottom: none;
        padding: 0.9rem 1.4rem;
    }

    .modal-header-razon .title-wrapper {
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }

    .modal-header-razon .icon-circle {
        width: 34px;
        height: 34px;
        border-radius: 999px;
        background-color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-header-razon .icon-circle i {
        color: #EF9A10;
        font-size: 1.1rem;
    }

    .modal-header-razon .modal-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    .modal-header-razon .modal-subtitle {
        font-size: 0.75rem;
        color: #f3f8fd;
        margin-top: -2px;
    }

    .modal-body-razon {
        background-color: #f9fafb;
        color: #374151;
        padding: 1rem 1.4rem 1.1rem 1.4rem;
        font-size: 0.93rem;
        line-height: 1.5;
        max-height: 320px;
        overflow-y: auto;
    }

    .modal-body-razon p {
        margin-bottom: 0;
        white-space: pre-wrap;
    }

    .modal-footer-razon {
        background-color: #f9fafb;
        border-top: none;
        padding: 0.6rem 1.4rem 1rem 1.4rem;
        display: flex;
        justify-content: flex-end;
    }

    .btn-razon-close {
        background-color: #022F55;
        border-color: #022F55;
        color: #ffffff;
        border-radius: 999px;
        padding: 0.3rem 1.2rem;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .btn-razon-close:hover {
        background-color: #EF9A10;
        border-color: #EF9A10;
        color: #ffffff;
    }

    /* Animación sutil de entrada del modal */
    #modalRazon.modal.fade .modal-dialog {
        transform: translateY(-10px);
        transition: transform 0.25s ease-out;
    }

    #modalRazon.modal.show .modal-dialog {
        transform: translateY(0);
    }
</style>
<div class="container-fluid">
    <div class="my-4 text-center">
        <h4 class=" mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 700 !important ">MÓDULO DE CUENTAS</h4>
        <small> Modulo para consulta de estado general de las cuentas</small>
    </div>
    <div class="card shadow mb-4" style="border-radius:15px;">
        <div
            class="card-header py-3 text-center d-flex flex-row align-items-center justify-content-between" style="background: linear-gradient(1335deg, #ffa726, #fb8c00); border-radius:0px 15px 0px 0px;">
            <div class="d-flex align-items-center ">
                <div class="d-flex justify-content-center align-items-center me-3 rounded-5  p-2" style="width: 35px; height: 35px; background-color: #ffffffff;">
                    <i class="bi bi-journal-x" style="color:#EF9A10"></i>
                </div>
                <div>
                    <div class=" text-start text-white" style="font-size: 1em;  font-weight: 700; ;">
                        CUENTAS RECHAZADAS
                    </div>
                    <div style="font-size: 70%; color: #f3f8fdff;" class="text-start">
                        LISTADO DE CUENTAS QUE HAN SIDO RECHAZADAS
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered " id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Fecha de rechazo</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Cédula</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Nombres</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Apellidos</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Rechazado por</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Año</th>
                            <th class="motivo-rechazo" style="text-align: center; vertical-align: middle; font-size: 14px">Motivo del rechazo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <?php
                                $nombres   = trim(($row['primer_nombre'] ?? '') . ' ' . ($row['segundo_nombre'] ?? ''));
                                $apellidos = trim(($row['primer_apellido'] ?? '') . ' ' . ($row['segundo_apellido'] ?? ''));
                                $rechazadoPor = trim(($row['nombre'] ?? '') . ' ' . ($row['apellido'] ?? ''));
                                ?>
                                <tr>
                                    <td class="p-1" style='text-align: center; vertical-align: middle; font-size: 0.9em'>
                                        <?= htmlspecialchars($row['fecha_rechazo']) ?>
                                    </td>
                                    <td style='text-align: center; vertical-align: middle; font-size: 0.9em'>
                                        <a href="index.php?page=cuentas/radicacion/detalle_cuenta_rechazada&id=<?= urlencode($row['id']) ?>"
                                            class="btn btn-link" style='text-align: center; vertical-align: middle; font-size: 1em'>
                                            <?= htmlspecialchars($row['numero_identidad']) ?>
                                        </a>
                                    </td>
                                    <td style='text-align: center; vertical-align: middle; font-size: 0.9em'>
                                        <?= htmlspecialchars($nombres) ?>
                                    </td>
                                    <td style='text-align: center; vertical-align: middle; font-size: 0.9em'>
                                        <?= htmlspecialchars($apellidos) ?>
                                    </td>
                                    <td style='text-align: center; vertical-align: middle; font-size: 0.9em'>
                                        <?= htmlspecialchars($rechazadoPor) ?>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle; font-size: 14px">
                                        <?= !empty($row['rec_anio_cuenta']) ? htmlspecialchars($row['rec_anio_cuenta']) : 'Año no disponible' ?>
                                    </td>
                                    <td class="motivo_rechazo p-1" style="text-align: center; vertical-align: middle; font-size: 0.9em;">
                                        <button type="button"
                                            class="btn-eye-toggle"
                                            data-razon="<?= htmlspecialchars($row['razon'], ENT_QUOTES) ?>"
                                            onclick="mostrarRazon(this)">
                                            <i class="bi bi-eye-slash eye-icon"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php elseif ($result): ?>
                            <tr>
                                <td colspan="6" class="text-center">
                                    No se encontraron registros disponibles
                                </td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">
                                    Error en la consulta: <?= htmlspecialchars($mysqli->error) ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalRazon" tabindex="-1" aria-labelledby="modalRazonLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-razon">
            <div class="modal-header modal-header-razon">
                <div class="title-wrapper">
                    <div class="icon-circle">
                        <i class="bi bi-eye"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" id="modalRazonLabel">Motivo de rechazo</h5>
                        <div class="modal-subtitle">
                            Detalle de la observación registrada para la cuenta
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body modal-body-razon">
                <p id="modalRazonContenido"></p>
            </div>
            <div class="modal-footer modal-footer-razon">
                <button type="button" class="btn btn-razon-close btn-sm" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        var table = $('#dataTable').DataTable({
            language: window.neivaDataTablesLanguage ? window.neivaDataTablesLanguage() : {}
        });
        $('.toggle-col').each(function() {
            var colIndex = parseInt($(this).data('col'), 10);
            var col = table.column(colIndex);
            $(this).prop('checked', col.visible());
        });
        $('.toggle-col').on('change', function() {
            var colIndex = parseInt($(this).data('col'), 10);
            var col = table.column(colIndex);
            col.visible($(this).is(':checked'));
        });
    });

    let eyeButtonActivo = null;

    function mostrarRazon(btn) {
        const texto = btn.getAttribute('data-razon');
        document.getElementById('modalRazonContenido').textContent = texto;
        eyeButtonActivo = btn;

        const icon = btn.querySelector('.eye-icon');
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye', 'eye-open');

        var modal = new bootstrap.Modal(document.getElementById('modalRazon'));
        modal.show();
    }

    document.getElementById('modalRazon').addEventListener('hidden.bs.modal', function() {
        if (eyeButtonActivo) {
            const icon = eyeButtonActivo.querySelector('.eye-icon');
            icon.classList.remove('bi-eye', 'eye-open');
            icon.classList.add('bi-eye-slash');
            eyeButtonActivo = null;
        }
    });
</script>