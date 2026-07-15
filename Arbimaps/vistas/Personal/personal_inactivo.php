<?php
$rolesPermitidos = array("administrador", "director_presupuestos", "pagos", "seguridad_social", "director_proyectos", "Directivos");
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

    .col-toggle-pill:has(input[type="checkbox"]:checked) {
        background-color: #0776d0ff;
        border: 1px solid #0776d0ff;
        color: white;
    }

    .dataTables_wrapper .dataTables_length {
        float: left;
        text-align: left;
    }

    .dataTables_wrapper .dataTables_filter {
        float: right;
        text-align: right;
    }

    .dataTables_wrapper .dataTables_info {
        float: left;
        text-align: left;
    }

    .dataTables_wrapper .dataTables_paginate {
        float: right;
        text-align: right;
    }

    @media (max-width: 767.98px) {

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            float: none;
            text-align: center;
        }
    }

    .table-responsive {
        overflow-x: visible !important;
    }

    table.dataTable th,
    table.dataTable td {
        white-space: normal !important;
    }

    .dataTables_wrapper .dataTables_length label {
        display: flex;
        align-items: center;
        gap: .5rem;
        margin-bottom: 11px;
    }

    .dataTables_wrapper .dataTables_filter label {
        display: flex;
        align-items: center;
        gap: .5rem;
        margin-bottom: 0;
    }

    .dataTables_wrapper .dataTables_filter input {
        margin-left: 0 !important;
    }

    .card-header {
        background: linear-gradient(325deg, #632323ff, #012949ff);
    }
</style>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">


<div class="container-fluid">
    <div class="my-4 text-center">
        <h4 class=" mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 700 !important ">MÓDULO DEL PERSONAL INACTIVO</h4>
        <small> Lista de personal inactivo</small>
    </div>

    <div class="card mb-4" style="border-radius:15px;">
        <!-- Header card -->
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">

            <div class="d-flex align-items-center ">
                <div class="d-flex justify-content-center align-items-center me-3 rounded-5  p-2" style="width: 35px; height: 35px; background-color: #ffffffff;">
                    <i class="bi bi-person-video2" style="color: #002F55;"></i>
                </div>
                <div>
                    <div class=" text-start text-white" style="font-size: 1em;  font-weight: 700;">
                        Personal Inactivo de arbitrium
                    </div>
                    <div style="font-size: 70%; color: #f3f8fdff;" class="text-start">
                        Listado de cooperadores inactivos en plataforma
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered " id="dataTable" width="100%" cellspacing="0">
                    <thead class="historial-thead">
                        <tr>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Nombres</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Apellidos</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">N° identidad</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Correo personal</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">N° Celular</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Cargo</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Proyecto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT 
                                    con_nombres, 
                                    con_apellidos, 
                                    con_num_identidad, 
                                    con_correo,
                                    con_celular,
                                    con_cargo, 
                                    con_proyecto, 
                                    con_fecha_final, 
                                    con_estado
                                FROM contratacion 
                                WHERE con_estado IN ('Inactivo') ";
                        if ($result = $mysqli->query($sql)) :
                            if ($result->num_rows > 0) :
                                while ($row = $result->fetch_assoc()) :
                                    $fecha_fin = new DateTime($row['con_fecha_final']);
                                    $hoy = new DateTime();
                                    $intervalo = $hoy->diff($fecha_fin);
                                    $dias_restantes = (int)$intervalo->format('%r%a');
                        ?>
                                    <tr class="text-center" style="text-align: center; vertical-align: middle; font-size: 13px;">
                                        <td class="text-align: center; vertical-align: middle; font-size: 14px;"><?= htmlspecialchars($row['con_nombres']); ?></td>
                                        <td class="text-align: center; vertical-align: middle; font-size: 14px"><?= htmlspecialchars($row['con_apellidos']); ?></td>
                                        <td class="text-align: center; vertical-align: middle; font-size: 14px">
                                            <a href="index.php?page=Personal/informacion_personal&con_num_identidad=<?= urlencode($row['con_num_identidad']); ?>" class="btn btn-link" style="font-size: 13px;">
                                                <?= htmlspecialchars($row['con_num_identidad']); ?>
                                            </a>
                                        </td>
                                        <td class="text-align: center; vertical-align: middle; font-size: 14px"><?= htmlspecialchars($row['con_correo']); ?></td>
                                        <td class="text-align: center; vertical-align: middle; font-size: 14px"><?= htmlspecialchars($row['con_celular']); ?></td>
                                        <td class="text-align: center; vertical-align: middle; font-size: 14px"><?= htmlspecialchars($row['con_cargo']); ?></td>
                                        <td class="text-align: center; vertical-align: middle; font-size: 14px"><?= htmlspecialchars($row['con_proyecto']); ?></td>
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
                                <td colspan="10">Error en la consulta: <?= htmlspecialchars($mysqli->error); ?></td>
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

<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="../js/scripts.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>

<script>
    $(document).ready(function() {
        var table = $('#dataTable').DataTable({
            order: [],
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
    });
</script>