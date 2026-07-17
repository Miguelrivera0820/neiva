<?php
$esPago = isset($_SESSION['rol_usuario'])
    && in_array($_SESSION['rol_usuario'], ['pagos', 'administrador', 'soporte']);

$sql_cuentas = "SELECT * FROM cuenta WHERE id = ?";
$stmt_cuentas = $mysqli->prepare($sql_cuentas);
$stmt_cuentas->bind_param("i", $idUsuario);
$stmt_cuentas->execute();
$resultado_cuentas = $stmt_cuentas->get_result();

$datos_cuentas = null;
$ruta_cuentas = null;

if ($resultado_cuentas && $resultado_cuentas->num_rows > 0) {
    $datos_cuentas = $resultado_cuentas->fetch_assoc();

    if (!empty($datos_cuentas['informe_mensual'])) {
        $ruta_cuentas = "../../radicaciones/modelo_de_cuenta/vistas/modelo_de_cuenta/2025/2025-01";
    }
}


if (isset($_GET['numero_identidad'])) {
    $numero_identidad = $_GET['numero_identidad'];

    $query = "SELECT * FROM cuenta WHERE numero_identidad = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $numero_identidad);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $tipo_documento = $row['tipo_documento'];
        $numero_identidad = $row['numero_identidad'];
        $primer_nombre = $row['primer_nombre'];
        $segundo_nombre = $row['segundo_nombre'];
        $primer_apellido = $row['primer_apellido'];
        $segundo_apellido = $row['segundo_apellido'];
        $telefono = $row['telefono'];
        $correo = $row['correo'];
        $cargo = $row['cargo'];
        $proyecto = $row['proyecto'];
        $observacion = $row['observacion'];
        $informe_mensual = $row['informe_mensual'] ?? null;
        echo "Datos cargados correctamente.<br>";
    } else {
        echo "No se encontraron datos para esta cédula.<br>";
        exit();
    }
}
?>

<!-- <link rel="stylesheet" href="assets/sb-details.css"> -->

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
        color: #002F55;
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
        background-color: #002F55;
        border: 1px solid #002F55;
        /* tu azul */
        color: white;
    }
</style>


<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container-fluid">
    <div class="my-4 text-center">
        <h4 class=" mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 700 !important ">MÓDULO PARA PAGO DE CUENTAS</h4>
        <small> Modulo para la validación de pagos de las cuentas</small>
    </div>
    <div class="card shadow mb-4" style="border-radius:15px;">
        <div
            class="card-header py-3 text-center d-flex flex-row align-items-center justify-content-between" style="background: linear-gradient(355deg, #0e64ae, #348ea0);; border-radius:0px 15px 0px 0px;">
            <div class="d-flex align-items-center ">
                <div class="d-flex justify-content-center align-items-center me-3 rounded-5  p-2" style="width: 35px; height: 35px; background-color: #ffffffff;">
                    <i class="bi bi-journal-check " style="color: #002F55;"></i>
                </div>
                <div>
                    <div class=" text-start text-white" style="font-size: 1em;  font-weight: 700; ;">
                        CUENTAS APROBADAS
                    </div>
                    <div style="font-size: 70%; color: #f3f8fdff;" class="text-start">
                        LISTADO DE CUENTAS APROBADAS POR LOS TRES FILTROS
                    </div>
                </div>
            </div>
            <div class="d-none d-md-flex align-items-center gap-2 col-toggle-bar" style="color: #002f55; font-size: 0.9rem;">
                <div class="col-toggle-chip me-2">
                    <i class="fas fa-sliders-h me-1"></i>
                    <span>Agregar columnas</span>
                </div>
                <label class="col-toggle-pill me-1">
                    <input type="checkbox" class="toggle-col" data-col="1">
                    <span>Año</span>
                </label>
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
                            <th style="text-align: center; vertical-align: middle; font-size: 14px" class="text-center">Fecha de aprobación</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px" class="text-center">Año</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px" class="text-center">Cédula</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px" class="text-center">Nombre</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px" class="text-center">Apellido</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px" class="text-center">Rol</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px" class="text-center">Proyecto</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px" class="text-center">Estado</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px" class="text-center">Valor Aprobado</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px" class="text-center">Pago</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT 
                                    id,
                                    fecha_aprobado, 
                                    numero_identidad, 
                                    primer_nombre, 
                                    primer_apellido, 
                                    segundo_nombre, 
                                    segundo_apellido, 
                                    cargo, 
                                    proyecto, 
                                    estado_final,
                                    valor_aprobado, 
                                    fecha_aprobacion_final,
                                    pagado,
                                    anio_cuenta 
                                FROM cuenta 
                                WHERE estado_final IN ('Aprobado') 
                                ORDER BY fecha_aprobado ASC";
                        if ($result = $mysqli->query($sql)) {
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $pagado      = strtolower($row['pagado'] ?? '');
                                    $estadoClass = ($pagado === 'pagado') ? 'estado-aprobado' : '';
                        ?>
                                    <tr>
                                        <td style='text-align: center; vertical-align: middle; font-size: 14px'>
                                            <?= htmlspecialchars($row['fecha_aprobacion_final']  ?? 'sin fecha') ?>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle; font-size: 14px">
                                            <?= !empty($row['anio_cuenta']) ? htmlspecialchars($row['anio_cuenta']) : 'Año no disponible' ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 14px'>
                                            <?php if ($esPago): ?>
                                                <a href="index.php?page=cuentas/radicacion/imprimir_cuenta_aprobada&id=<?= (int)$row['id'] ?>"
                                                    class="btn btn-link">
                                                    <?= htmlspecialchars($row['numero_identidad']) ?>
                                                </a>
                                            <?php else: ?>
                                                <?= htmlspecialchars($row['numero_identidad']) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 14px'>
                                            <?= htmlspecialchars($row['primer_nombre'] . ' ' . $row['segundo_nombre']) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 14px'>
                                            <?= htmlspecialchars($row['primer_apellido'] . ' ' . $row['segundo_apellido']) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 14px'>
                                            <?= htmlspecialchars($row['cargo']) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 14px'>
                                            <?= htmlspecialchars($row['proyecto']) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 14px'>
                                            <?= htmlspecialchars($row['estado_final']) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 14px'>
                                            &#36;<?= number_format((float)$row['valor_aprobado'], 0, ',', '.') ?>
                                        </td>
                                        <td class="<?= $estadoClass ?>" style='text-align: center; vertical-align: middle; font-size: 14px'>
                                            <?= htmlspecialchars(ucfirst($pagado)) ?>
                                        </td>
                                    </tr>
                                <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="9">No se encontraron registros disponibles</td>
                                </tr>
                            <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="9">
                                    Error en la consulta: <?= htmlspecialchars($mysqli->error) ?>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



<!-- SCRIPT DATATABLES PARA PODER BUSCAR FILTRAR Y DEMAS-->
<!-- jQuery (requerido por DataTables) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 5 JS -->
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        var table = $('#dataTable').DataTable({
            language: window.neivaDataTablesLanguage ? window.neivaDataTablesLanguage() : {},
                // decimal: ",",
                // thousands: ".",
                // processing: "Procesando...",
                // search: "Buscar:",
                // lengthMenu: "Mostrar _MENU_ registros",
                // info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                // infoEmpty: "Mostrando 0 a 0 de 0 registros",
                // infoFiltered: "(filtrado de _MAX_ registros totales)",
                // infoPostFix: "",
                // loadingRecords: "Cargando...",
                // zeroRecords: "No se encontraron resultados",
                // emptyTable: "No hay datos disponibles en la tabla",
                // paginate: {
                //     first: "Primero",
                //     previous: "Anterior",
                //     next: "Siguiente",
                //     last: "Último"
                // },
                // aria: {
                //     sortAscending: ": activar para ordenar la columna de manera ascendente",
                //     sortDescending: ": activar para ordenar la columna de manera descendente"
                // }
            columnDefs: [{
                targets: [1, 4, 5],
                visible: false
            }]
        });
        $('.toggle-col').on('change', function() {
            var colIndex = parseInt($(this).data('col'), 10);
            var col = table.column(colIndex);
            col.visible($(this).is(':checked'));
        });
    });
</script>
