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

    /* botones adicionales de las opciones */
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
</style>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container-fluid">
    <div class="my-4 text-center">
        <h4 class="mb-0 fw-bold mb-2" style="color: #FFA000; font-weight: 700 !important;">
            CUENTAS DEVUELTAS
        </h4>
    </div>
    <div class="row mt-5">
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
                                Cuentas Devueltas
                            </div>
                            <div style="font-size: 70%; color: #f3f8fdff;" class="text-start">
                                Listado de las solicitudes que han sido devueltas
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-card text-center align-middle" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">N° RAD</th>
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
                                $idUsuario = (int) $idUsuario;
                                $sql = "SELECT
                                c.id,
                                c.copia_id,
                                c.copia_usuario_id,
                                c.copia_sb_baqueano_nombre,
                                c.copia_sb_baqueano_apellido,
                                c.copia_sb_numero_identidad,
                                c.copia_sb_dias_calculados,
                                c.copia_sb_cobro_diario,
                                c.copia_sb_valor_cobrar,
                                c.copia_sb_tipo_actividad,
                                c.copia_sb_estado_lider,
                                c.copia_sb_estado_profesional,
                                c.copia_sb_estado_operaciones,
                                s.sb_estado_cuenta,
                                s.id AS solicitud_id
                            FROM copia_devolucion_baqueanos AS c
                            JOIN solicitud_baqueanos AS s ON c.copia_id = s.id
                            WHERE s.sb_estado_cuenta != 'RADICADO'
                            ORDER BY c.id ASC";
                                if ($stmt = $mysqli->prepare($sql)) {
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td class='text-center'><a href='index.php?page=baqueanos/solicitudes/vistas/informacion_mi_rechazo&id=" . urlencode($row['copia_id']) . "' class='rad btn-link'>ARB_" . htmlspecialchars($row['copia_id']) . "</a></td>";
                                            echo "<td class='text-center'>
                                                    <div class='d-flex justify-content-center'>
                                                        <div class='user-info'>
                                                            <div class='user-name'>" . htmlspecialchars($row['copia_sb_baqueano_nombre']) . "</div>
                                                            <div class='user-lastname'>" . htmlspecialchars($row['copia_sb_baqueano_apellido']) . "</div>
                                                        </div>
                                                    </div>
                                                </td>";
                                            echo "<td class='text-center'>" . htmlspecialchars($row['copia_sb_numero_identidad']) . "</td>";
                                            echo "<td class='text-center'>" . htmlspecialchars($row['copia_sb_dias_calculados']) . "</td>";
                                            echo "<td class='text-center'>$" . number_format($row['copia_sb_cobro_diario'], 0, ',', '.') . "</td>";
                                            echo "<td class='text-center'>$" . number_format($row['copia_sb_valor_cobrar'], 0, ',', '.') . "</td>";
                                            echo "<td class='text-center'>" . htmlspecialchars($row['copia_sb_tipo_actividad']) . "</td>";
                                            echo "<td class='text-center'>";
                                            if ($row['sb_estado_cuenta'] === 'DEVUELTO') {
                                                echo "<a href='index.php?page=baqueanos/solicitudes/vistas/radicar_cuenta_baqueanos&id=" . urlencode($row['solicitud_id']) . "' title='Ver o agregar cuenta'>
                                                <i class='fas fa-file-invoice-dollar fa-lg text-success'></i>
                                            </a>";
                                            }
                                            echo "</td>";
                                        }
                                    } else {
                                    }
                                    $stmt->close();
                                } else {
                                    echo "<tr><td colspan='9'>Error al preparar la consulta: " . htmlspecialchars($mysqli->error) . "</td></tr>";
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
<script src="https://kit.fontawesome.com/9944c94262.js" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<!-- <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script> -->
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        var table = $('#dataTable').DataTable({
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
            ],
            initComplete: function() {
                this.api().columns().every(function() {
                    var column = this;
                    var select = $('<select><option value="">Todos</option></select>')
                        .appendTo($(column.footer()).empty())
                        .on('change', function() {
                            var val = $.fn.dataTable.util.escapeRegex($(this).val());
                            column.search(val ? '^' + val + '$' : '', true, false).draw();
                        });

                    column.data().unique().sort().each(function(d) {
                        select.append('<option value="' + d + '">' + d + '</option>');
                    });
                });

                // Ocultar selects específicos
                $('#dataTable tfoot th:nth-child(2) select').hide();
                $('#dataTable tfoot th:nth-child(3) select').hide();
                $('#dataTable tfoot th:nth-child(4) select').hide();
                $('#dataTable tfoot th:nth-child(5) select').hide();
                $('#dataTable tfoot th:nth-child(9) select').hide();
            }
        });
    });
</script>