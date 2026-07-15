<?php
$query = "SELECT * FROM cuenta WHERE estado = 'aprobado'";
$result = $mysqli->query($query);

$cuentas_aprobadas = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cuentas_aprobadas[] = $row;
    }
}
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    :root {
        --color-primario: #022F55;
        --color-primario-suave: #f1f5f9;
        --color-borde-suave: #e5e7eb;
        --color-texto-muted: #6b7280;
    }

    /* Número activo en la paginación */
    .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
        background-color: #002F55 !important;

        border-color: #002F55 !important;
        color: #fff !important;
    }

    /* Hover sobre números */
    .dataTables_wrapper .dataTables_paginate .page-link:hover {
        background-color: #457b9d !important;
        /* Azul oscuro */
        color: #fff !important;
    }

    /* Texto de los links de paginación */
    .dataTables_wrapper .dataTables_paginate .page-link {
        color: #002f55 !important;
        /* Azul Bootstrap */
        border-radius: 8px;
        /* Bordes más redondeados */
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

    .col-toggle-pill {
        display: inline-flex;
        gap: 3px;
        align-items: center;
        padding: 0.15rem 0.55rem;
        color: #feffffff;
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
        background-color: #f1f5f9;
        border-color: #cbd5f5;
        color: #002F55;
    }

    .col-toggle-pill input[type="checkbox"]:checked+span {
        color: var(--color-primario-suave);
        font-weight: 600;
    }

    .col-toggle-pill input[type="checkbox"]:checked+span:hover {
        color: var(--color-primario);
        font-weight: 600;
    }

    .col-toggle-pill input[type="checkbox"]:checked {
        accent-color: #FFC107;
    }

    .toogle-col {
        background-color: #002F55 !important;
    }

    /* Truco correcto: aplicamos estilo AL LABEL USANDO :has() (CSS moderno) */
    .col-toggle-pill:has(input[type="checkbox"]:checked) {
        background-color: #0776d0ff;
        border: 1px solid #0776d0ff;
        /* tu azul */
        color: white;
    }
</style>


<div class="container-fluid">

    <div class="my-4 text-center">
        <h4 class=" mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 700 !important ">MÓDULO PARA VALIDACIÓN DE CUENTAS</h4>
        <small> Modulo para la validación de pagos de las cuentas</small>
    </div>
    <div class="card shadow mb-4" style="border-radius:15px;">
        <div
            class="card-header py-3 text-center d-flex flex-row align-items-center justify-content-between" style="background: linear-gradient(355deg, #074a85ff, #012949ff);; border-radius:0px 15px 0px 0px;">
            <div class="d-flex align-items-center ">
                <div class="d-flex justify-content-center align-items-center me-3 rounded-5  p-2" style="width: 35px; height: 35px; background-color: #ffffffff;">
                    <i class="bi bi-journal-check " style="color: #002F55;"></i>
                </div>
                <div>
                    <div class=" text-start text-white" style="font-size: 1em;  font-weight: 700; ;">
                        CUENTAS APROBADAS
                    </div>
                    <div style="font-size: 70%; color: #f3f8fdff;" class="text-start">
                        Listado de cuentas radicadas para validación; revisadas y aprobadas por Juan Camilo.
                    </div>
                </div>
            </div>
            <div class="d-none d-md-flex align-items-center gap-2 col-toggle-bar" style="color: #002f55; font-size: 0.9rem;">
                <div class="col-toggle-chip me-2">
                    <i class="fas fa-sliders-h me-1"></i>
                    <span>Agregar columnas</span>
                </div>
                <label class="col-toggle-pill me-1">
                    <input type="checkbox" class="toggle-col" data-col="5">
                    <span>Proyecto</span>
                </label>
                <label class="col-toggle-pill me-1">
                    <input type="checkbox" class="toggle-col" data-col="4">
                    <span>Rol</span>
                </label>
            </div>

        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered " id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Fecha aprobacion</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Cédula</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Nombre</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Apellido</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Rol</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Proyecto</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Año</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Mes</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Seg. Social</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT 
                                id,
                                fecha_aprobacion_presupuesto, 
                                numero_identidad, 
                                primer_nombre, 
                                primer_apellido, 
                                segundo_nombre, 
                                segundo_apellido, 
                                telefono, 
                                correo, 
                                cargo, 
                                proyecto,
                                Periodo_Facturacion, 
                                valor_aprobado, 
                                estado, 
                                estado_seguridad_social, 
                                estado_final, 
                                pagado,
                                anio_cuenta
                            FROM cuenta 
                            WHERE estado = 'Aprobado'  
                            AND estado_final != 'Rechazado'  
                            AND estado_seguridad_social != 'Rechazado'  
                            ORDER BY 
                            CASE 
                                WHEN estado_seguridad_social = 'Aprobado' THEN 1  
                            ELSE 0                                   
                        END ASC,
                        fecha_aprobacion_presupuesto DESC";
                        if ($result = $mysqli->query($sql)) :
                            if ($result->num_rows > 0) :
                                while ($row = $result->fetch_assoc()) :
                        ?>
                                    <tr>
                                        <td style='text-align: center; vertical-align: middle; font-size: 13px'>
                                            <?= !empty($row['fecha_aprobacion_presupuesto'])
                                                ? htmlspecialchars($row['fecha_aprobacion_presupuesto'])
                                                : 'Sin fecha' ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 13px'>
                                            <a style='text-align: center; vertical-align: middle; font-size: 13px' href="index.php?page=cuentas/validacion/revisar_cuenta_social&numero_identidad=<?= urlencode($row['numero_identidad']) ?>&id=<?= urlencode($row['id']) ?>"
                                                class="btn btn-link">
                                                <?= htmlspecialchars($row['numero_identidad']) ?>
                                            </a>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 13px'>
                                            <?= htmlspecialchars($row['primer_nombre'] . ' ' . $row['segundo_nombre']) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 13px'>
                                            <?= htmlspecialchars($row['primer_apellido'] . ' ' . $row['segundo_apellido']) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 13px'>
                                            <?= htmlspecialchars($row['cargo']) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 13px'>
                                            <?= htmlspecialchars($row['proyecto']) ?>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle; font-size: 14px">
                                            <?= !empty($row['anio_cuenta']) ? htmlspecialchars($row['anio_cuenta']) : 'Año no disponible' ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 13px'>
                                            <?= htmlspecialchars($row['Periodo_Facturacion']) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 13px'>
                                            <?php if (!empty($row['estado_seguridad_social']) && $row['estado_seguridad_social'] == 'Aprobado') : ?>
                                                <i class="fa-solid fa-circle-check fa-xl" style="color:  rgb(24, 167, 22);"></i>
                                            <?php else : ?>
                                                <i class="fa-solid fa-spinner fa-xl fa-spin" style="color:rgb(56, 133, 195);"></i>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php
                                endwhile;
                            else :
                                ?>
                                <tr>
                                    <td colspan="10">No se encontraron registros disponibles</td>
                                </tr>
                            <?php
                            endif;
                        else :
                            ?>
                            <tr>
                                <td colspan="10">
                                    Error en la consulta: <?= htmlspecialchars($mysqli->error) ?>
                                </td>
                            </tr>
                        <?php
                        endif;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://kit.fontawesome.com/9944c94262.js" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<!-- <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script> -->
<script src="../../../js/scripts.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        var table = $('#dataTable').DataTable({
            order: [], // <- MUY IMPORTANTE: respeta el orden del HTML / servidor
            language: {
                decimal: ",",
                thousands: ".",
                processing: "Procesando...",
                search: "Buscar:",
                lengthMenu: "Mostrar _MENU_ registros",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                infoEmpty: "Mostrando 0 a 0 de 0 registros",
                infoFiltered: "(filtrado de _MAX_ registros totales)",
                loadingRecords: "Cargando...",
                zeroRecords: "No se encontraron resultados",
                emptyTable: "No hay datos disponibles en la tabla",
                paginate: {
                    first: "Primero",
                    previous: "Anterior",
                    next: "Siguiente",
                    last: "Último"
                },
                aria: {
                    sortAscending: ": activar para ordenar la columna de manera ascendente",
                    sortDescending: ": activar para ordenar la columna de manera descendente"
                }
            }
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
</script>