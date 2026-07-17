<?php
$query = "SELECT * FROM cuentas_pagadas";
$result = $mysqli->query($query);
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
        background-color: #2E7D32 !important;
        border-color: #2E7D32 !important;
        color: #fff !important;
    }

    .dataTables_wrapper .dataTables_paginate .page-link:hover {
        background-color: #2e7d3270 !important;
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
        transition: background-color 0.25s ease, color 0.25s ease;
        font-weight: 500;
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

    .col-toggle-pill:has(input[type="checkbox"]:checked) {
        background-color: #ffffffff;
        border: 1px solid #ffffffff;
        color: #2E7D32;
    }

    .check-wrap {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .check-pro {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #2E7D32;
        /* moderno y consistente */
        transform: translateY(1px);
    }

    /* borde suave para que no se vea “barato” */
    .check-pro {
        outline: none;
    }

    /* ===== Selección de fila (look profesional) ===== */
    tr.row-selected td {
        background-color: #2e7d3212 !important;
        /* verde suave */
    }

    tr.row-selected td:first-child {
        box-shadow: inset 3px 0 0 #2E7D32;
        /* barrita lateral */
    }

    /* Hace que el primer header (checkbox) se vea alineado y limpio */
    th.select-col,
    td.select-col {
        width: 44px;
        text-align: center;
        vertical-align: middle;
    }

    /* Pequeño contador con estética */
    .sel-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        background: #ffffffcc;
        border: 1px solid #e5e7eb;
        color: #022F55;
        font-size: 0.85rem;
        font-weight: 600;
    }
</style>

<div class="container-fluid">
    <div class="my-4 text-center">
        <h4 class=" mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 700 !important ">MÓDULO DE CUENTAS</h4>
        <small> Modulo para consulta de estado general de las cuentas</small>
    </div>

    <div class="card shadow mb-4" style="border-radius:15px;">
        <div class="card-header py-3 text-center d-flex flex-row align-items-center justify-content-between"
            style="background: linear-gradient(1335deg, #1a8e3f, #34c759); border-radius:0px 15px 0px 0px;">
            <div class="d-flex align-items-center ">
                <div class="d-flex justify-content-center align-items-center me-3 rounded-5  p-2"
                    style="width: 35px; height: 35px; background-color: #ffffffff;">
                    <i class="bi bi-journal-bookmark-fill" style="color:#2E7D32"></i>
                </div>
                <div>
                    <div class=" text-start text-white" style="font-size: 1em;  font-weight: 700;">
                        CUENTAS PAGADAS
                    </div>
                    <div style="font-size: 70%; color: #f3f8fdff;" class="text-start">
                        LISTADO DE CUENTAS QUE HAN SIDO PAGADAS
                    </div>
                </div>
            </div>

            <div class="d-none d-md-flex align-items-center gap-2 col-toggle-bar" style="color: #002f55; font-size: 0.9rem;">
                <div class="col-toggle-chip me-2">
                    <i class="fas fa-sliders-h me-1"></i>
                    <span>Agregar columnas</span>
                </div>
                <label class="col-toggle-pill me-1">
                    <input type="checkbox" class="toggle-col" data-col="7">
                    <span>Proyecto</span>
                </label>
                <label class="col-toggle-pill me-1">
                    <input type="checkbox" class="toggle-col" data-col="6">
                    <span>Rol</span>
                </label>
                <label class="col-toggle-pill me-1">
                    <input type="checkbox" class="toggle-col" data-col="8">
                    <span>Año</span>
                </label>
            </div>
        </div>

        <div class="card-body">
            <div class="d-flex justify-content-end align-items-center gap-2 mb-2">
                <span class="sel-badge">
                    <i class="bi bi-check2-square"></i>
                    <span id="selCount">0</span> seleccionados
                </span>

                <button class="btn btn-sm btn-success" id="btnEditarMasivo">
                    <i class="bi bi-calendar2-range"></i> Cambiar fecha (seleccionados)
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="select-col">
                                <span class="check-wrap">
                                    <input type="checkbox" id="checkAll" class="check-pro">
                                </span>
                            </th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Fecha radicación</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">ID Radicación</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Cédula</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Nombres</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Apellidos</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Rol</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Proyecto</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Año</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Mes</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Valor Pagado</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Fecha de pago</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Modificar fecha pago</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT
                                id,
                                pag_fecha_subida,
                                pag_numero_identidad,
                                pag_primer_nombre,
                                pag_segundo_nombre,
                                pag_primer_apellido,
                                pag_segundo_apellido,
                                pag_cargo,
                                pag_proyecto,
                                pag_valor_aprobado,
                                pag_Periodo_Facturacion,
                                pag_anio_cuenta,
                                pag_fecha_pago
                            FROM cuentas_pagadas
                            ORDER BY pag_fecha_subida ASC";
                        if ($result = $mysqli->query($sql)) {
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                        ?>
                                    <tr>
                                        <td style="text-align:center; vertical-align: middle;">
                                            <input type="checkbox" class="row-check" value="<?= (int)$row['id'] ?>">
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 14px'>
                                            <?= date('Y-m-d', strtotime($row['pag_fecha_subida'])) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 14px'>
                                            <a href="index.php?page=cuentas/radicacion/detalle_cuenta_pagada&id=<?= urlencode($row['id']) ?>" class="btn btn-link">
                                                <?= htmlspecialchars('ARB_' . $row['id']) ?>
                                            </a>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 14px'>
                                            <?= htmlspecialchars($row['pag_numero_identidad']) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 14px'>
                                            <?= htmlspecialchars($row['pag_primer_nombre'] . ' ' . $row['pag_segundo_nombre']) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 14px'>
                                            <?= htmlspecialchars($row['pag_primer_apellido'] . ' ' . $row['pag_segundo_apellido']) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 14px'>
                                            <?= htmlspecialchars($row['pag_cargo']) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 14px'>
                                            <?= htmlspecialchars($row['pag_proyecto']) ?>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle; font-size: 14px">
                                            <?= !empty($row['pag_anio_cuenta']) ? htmlspecialchars($row['pag_anio_cuenta']) : 'Año no disponible' ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 14px'>
                                            <?= htmlspecialchars($row['pag_Periodo_Facturacion']) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 14px'>
                                            $<?= number_format($row['pag_valor_aprobado'], 0, ',', '.') ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 14px'>
                                            <?= htmlspecialchars($row['pag_fecha_pago']) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 14px'>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-success btn-edit-fecha"
                                                data-id="<?= (int)$row['id'] ?>"
                                                data-fecha="<?= htmlspecialchars($row['pag_fecha_pago'] ?? '') ?>">
                                                <i class="bi bi-calendar-event"></i> Cambiar
                                            </button>
                                        </td>
                                    </tr>
                                <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="13" style='text-align: center; vertical-align: middle; font-size: 14px'>
                                        No se encontraron registros disponibles
                                    </td>
                                </tr>
                            <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="13" style='text-align: center; vertical-align: middle; font-size: 14px'>
                                    Error en la consulta: <?= htmlspecialchars($mysqli->error) ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalFechaPago" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">Modificar fecha de pago</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_id">
                <input type="hidden" id="edit_ids">

                <label class="form-label mb-1" style="font-size: 0.9rem;">Nueva fecha</label>
                <input type="date" class="form-control form-control-sm" id="edit_fecha_pago">
                <small class="text-muted" id="edit_msg" style="display:block; margin-top:6px;"></small>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-sm btn-success" id="btnGuardarFecha">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        var table = $.fn.dataTable.isDataTable('#dataTable') ?
            $('#dataTable').DataTable() :
            $('#dataTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                columnDefs: [{
                    targets: [7, 6, 8],
                    visible: false
                }]
            });

        $('.toggle-col').on('change', function() {
            var colIndex = parseInt($(this).data('col'), 10);
            table.column(colIndex).visible($(this).is(':checked'));
        });

        const modalEl = document.getElementById('modalFechaPago');
        const modal = new bootstrap.Modal(modalEl);

        function refreshSelectionUI() {
            $('.row-check').each(function() {
                $(this).closest('tr').toggleClass('row-selected', $(this).is(':checked'));
            });

            const count = $('.row-check:checked').length;
            $('#selCount').text(count);

            const total = $('.row-check').length;
            $('#checkAll').prop('checked', total > 0 && count === total);
        }


        $('#checkAll').on('change', function() {
            const checked = $(this).is(':checked');
            $('.row-check').prop('checked', checked);
            refreshSelectionUI();
        });

        $('#dataTable').on('change', '.row-check', function() {
            refreshSelectionUI();
        });

        $('#dataTable').on('click', '.btn-edit-fecha', function() {
            const id = $(this).data('id');
            const fecha = ($(this).data('fecha') || '').toString().substring(0, 10);
            $('#edit_id').val(id);
            $('#edit_ids').val('');
            $('#edit_fecha_pago').val(fecha);
            $('#edit_msg').text('');
            modal.show();
        });

        $('#btnEditarMasivo').on('click', function() {
            const ids = $('.row-check:checked').map(function() {
                return this.value;
            }).get();
            if (ids.length === 0) {
                alert('Selecciona al menos un registro.');
                return;
            }
            $('#edit_id').val('');
            $('#edit_ids').val(ids.join(','));
            $('#edit_fecha_pago').val('');
            $('#edit_msg').text('Vas a actualizar ' + ids.length + ' registro(s).');
            modal.show();
        });

        $('#btnGuardarFecha').on('click', function() {
            const id = $('#edit_id').val();
            const idsCsv = $('#edit_ids').val();
            const fecha = $('#edit_fecha_pago').val();
            if (!fecha) {
                $('#edit_msg').text('Selecciona una fecha.');
                return;
            }

            const isMasivo = !!idsCsv;
            const url = isMasivo ?
                './vistas/cuentas/acciones/actualizar_fecha_pago_masivo.php' :
                './vistas/cuentas/acciones/actualizar_fecha_pago.php';

            const payload = isMasivo ? {
                ids: idsCsv,
                pag_fecha_pago: fecha
            } : {
                id: id,
                pag_fecha_pago: fecha
            };
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: payload,
                success: function(resp) {
                    if (resp && resp.ok) {
                        const COL_FECHA_PAGO = 11;
                        if (isMasivo) {
                            const ids = idsCsv.split(',').map(x => x.trim()).filter(Boolean);
                            ids.forEach(function(oneId) {
                                const chk = $('.row-check[value="' + oneId + '"]');
                                if (chk.length) {
                                    const row = table.row(chk.closest('tr'));
                                    const rowData = row.data();
                                    rowData[COL_FECHA_PAGO] = fecha;
                                    row.data(rowData);
                                }
                            });
                            table.draw(false);
                            $('#checkAll').prop('checked', false);
                            $('.row-check').prop('checked', false);
                        } else {
                            const btn = $('#dataTable .btn-edit-fecha[data-id="' + id + '"]');
                            const row = table.row(btn.closest('tr'));
                            const rowData = row.data();
                            rowData[COL_FECHA_PAGO] = fecha;
                            row.data(rowData).draw(false);
                        }
                        modal.hide();
                    } else {
                        $('#edit_msg').text(resp && resp.msg ? resp.msg : 'No se pudo actualizar.');
                    }
                },
                error: function() {
                    $('#edit_msg').text('Error de servidor.');
                }
            });
        });
    });
    refreshSelectionUI();
</script>