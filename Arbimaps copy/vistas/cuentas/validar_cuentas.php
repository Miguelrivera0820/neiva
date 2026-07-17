<?php
$meses = [
    "Enero"         => 1,
    "Febrero"       => 2,
    "Marzo"         => 3,
    "Abril"         => 4,
    "Mayo"          => 5,
    "Junio"         => 6,
    "Julio"         => 7,
    "Agosto"        => 8,
    "Septiembre"    => 9,
    "Octubre"       => 10,
    "Noviembre"     => 11,
    "Diciembre"     => 12
];

$mesSeleccionado = isset($_GET['mes']) && $_GET['mes'] != ''
    ? $_GET['mes']
    : (array_search((int)date('n'), $meses) ?: date('n'));

$sql = "SELECT 
            COALESCE(SUM(valor), 0) AS total 
        FROM cuenta 
        WHERE TRIM(Periodo_Facturacion) = '$mesSeleccionado'";

$result = $mysqli->query($sql);
$total = ($result && $result->num_rows > 0) ? (float)$result->fetch_assoc()["total"] : 0;

$sql = "SELECT 
            COALESCE(SUM(pag_valor_aprobado), 0) AS total_pagado 
        FROM cuentas_pagadas 
        WHERE TRIM(pag_Periodo_Facturacion) = '$mesSeleccionado'";
$result_pagado = $mysqli->query($sql);
$total_pagado = ($result_pagado && $result_pagado->num_rows > 0) ? (float)$result_pagado->fetch_assoc()["total_pagado"] : 0;

$sql = "SELECT 
            COALESCE(SUM(valor_aprobado), 0) AS valor_aprobado 
        FROM cuenta 
        WHERE estado_final = 'Aprobado' 
        AND TRIM(Periodo_Facturacion) = '$mesSeleccionado'";
$result_aprobado = $mysqli->query($sql);
$valor_aprobado = ($result_aprobado && $result_aprobado->num_rows > 0)
    ? (float)($result_aprobado->fetch_assoc()["valor_aprobado"] ?? 0)
    : 0;

$diferencia_valor = $total - $valor_aprobado;

$total_mes = $total + $total_pagado;

// Usar el mismo mapa de meses para obtener el número
$numeroMes = isset($meses[$mesSeleccionado]) ? $meses[$mesSeleccionado] : (int)$mesSeleccionado;

$colCuenta = "creado_en";
$colPagadas = "pag_creado_en";

$checkCuenta = $mysqli->query("SHOW COLUMNS FROM cuenta LIKE 'fecha_subida'");
if ($checkCuenta && $checkCuenta->num_rows > 0) {
    $colCuenta = "fecha_subida";
}
$checkPagadas = $mysqli->query("SHOW COLUMNS FROM cuentas_pagadas LIKE 'pag_fecha_subida'");
if ($checkPagadas && $checkPagadas->num_rows > 0) {
    $colPagadas = "pag_fecha_subida";
}

// ---Sumar cuentas cargadas (tabla cuenta)
$sqlCuenta = "SELECT COALESCE(SUM(valor), 0) AS total_cuentas
            FROM cuenta
            WHERE DATE_FORMAT($colCuenta, '%m') = LPAD($numeroMes, 2, '0')
";
$resCuenta = $mysqli->query($sqlCuenta);
$totalCuenta = ($resCuenta && $resCuenta->num_rows > 0)
    ? (float)$resCuenta->fetch_assoc()['total_cuentas'] : 0;

// ---Sumar cuentas pagadas cargadas (tabla cuentas_pagadas)
$sqlPagadas = "SELECT 
                COALESCE(SUM(pag_valor_aprobado), 0) AS total_pagadas
            FROM cuentas_pagadas
            WHERE DATE_FORMAT($colPagadas, '%m') = LPAD($numeroMes, 2, '0')
";
$resPagadas = $mysqli->query($sqlPagadas);
$totalPagadas = ($resPagadas && $resPagadas->num_rows > 0)
    ? (float)$resPagadas->fetch_assoc()['total_pagadas'] : 0;

// --- Total combinado ---
$total_por_fecha = $totalCuenta + $totalPagadas;

$desglose_por_mes   = [];
$temp               = [];

// --- Tabla cuenta ---
$sqlCuentaDesglose = "
    SELECT 
        COALESCE(TRIM(Periodo_Facturacion), 'Sin período') AS periodo,
        COALESCE(SUM(valor), 0) AS total_cobrado,
        COUNT(*) AS cantidad
    FROM cuenta
    WHERE DATE_FORMAT($colCuenta, '%m') = LPAD($numeroMes, 2, '0')
    GROUP BY periodo
";
$resultCuenta = $mysqli->query($sqlCuentaDesglose);
if ($resultCuenta && $resultCuenta->num_rows > 0) {
    while ($row = $resultCuenta->fetch_assoc()) {
        $p = $row['periodo'];
        $temp[$p]['total'] = ($temp[$p]['total'] ?? 0) + (float)$row['total_cobrado'];
        $temp[$p]['cantidad'] = ($temp[$p]['cantidad'] ?? 0) + (int)$row['cantidad'];
    }
}

// --- Tabla cuentas_pagadas ---
$sqlPagadasDesglose = "
    SELECT 
        COALESCE(TRIM(pag_Periodo_Facturacion), 'Sin período') AS periodo,
        COALESCE(SUM(pag_valor_aprobado), 0) AS total_cobrado,
        COUNT(*) AS cantidad
    FROM cuentas_pagadas
    WHERE DATE_FORMAT($colPagadas, '%m') = LPAD($numeroMes, 2, '0')
    GROUP BY periodo
";
$resultPagadas = $mysqli->query($sqlPagadasDesglose);
if ($resultPagadas && $resultPagadas->num_rows > 0) {
    while ($row = $resultPagadas->fetch_assoc()) {
        $p = $row['periodo'];
        $temp[$p]['total'] = ($temp[$p]['total'] ?? 0) + (float)$row['total_cobrado'];
        $temp[$p]['cantidad'] = ($temp[$p]['cantidad'] ?? 0) + (int)$row['cantidad'];
    }
}
$ordenMeses = [
    'Enero',
    'Febrero',
    'Marzo',
    'Abril',
    'Mayo',
    'Junio',
    'Julio',
    'Agosto',
    'Septiembre',
    'Octubre',
    'Noviembre',
    'Diciembre'
];

foreach ($ordenMeses as $m) {
    if (isset($temp[$m])) {
        $desglose_por_mes[] = [
            'periodo'  => $m,
            'total'    => $temp[$m]['total'],
            'cantidad' => $temp[$m]['cantidad'],
        ];
    }
}
if (isset($temp['Sin período'])) {
    $desglose_por_mes[] = [
        'periodo'  => 'Sin período',
        'total'    => $temp['Sin período']['total'],
        'cantidad' => $temp['Sin período']['cantidad'],
    ];
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

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container-fluid">
    <div class="my-4 text-center">
        <h4 class=" mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 700 !important ">MÓDULO PARA VALIDACIÓN DE CUENTAS</h4>
        <small> Modulo para la validación de pagos de las cuentas</small>
    </div>
    <div class="card shadow mb-4" style="border-radius:15px;">
        <div
            class="card-header py-3 text-center d-flex flex-row align-items-center justify-content-between" style="background: linear-gradient(355deg, #074a85ff, #002f55);; border-radius:0px 15px 0px 0px;">
            <div class="d-flex align-items-center ">
                <div class="d-flex justify-content-center align-items-center me-3 rounded-5  p-2" style="width: 35px; height: 35px; background-color: #ffffffff;">
                    <i class="bi bi-clipboard-plus " style="color: #002F55;"></i>
                </div>
                <div>
                    <div class=" text-start text-white" style="font-size: 1em;  font-weight: 700; ;">
                        CUENTAS RADICADAS
                    </div>
                    <div style="font-size: 70%; color: #f3f8fdff;" class="text-start">
                        Listado de cuentas radicadas para validación y habilitación para desembolso.
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
                    <input type="checkbox" class="toggle-col" data-col="8">
                    <span>Valor a cobrar</span>
                </label>
                <label class="col-toggle-pill">
                    <input type="checkbox" class="toggle-col" data-col="10">
                    <span>Valor aprobado</span>
                </label>
            </div>

        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered " id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Fecha Radicación</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">N° Rad</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Cédula</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Nombres</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Apellidos</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Rol</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Proyecto</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Valor a Cobrar</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Año</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Periodo</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">Valor aprobado</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">J. Camilo</th>
                            <!-- <th style="text-align: center; vertical-align: middle; font-size: 14px">Katherine</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 14px">J.Ramon</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $mesSeleccionado = isset($_GET['mes']) ? $_GET['mes'] : '';
                        $sql = "SELECT 
                                        id, 
                                        fecha_subida, 
                                        numero_identidad, 
                                        primer_nombre, 
                                        primer_apellido, 
                                        segundo_nombre, 
                                        segundo_apellido, 
                                        Periodo_Facturacion, 
                                        valor, 
                                        telefono, 
                                        correo, 
                                        cargo, 
                                        proyecto, 
                                        valor_aprobado, 
                                        estado, 
                                        estado_seguridad_social, 
                                        estado_final, 
                                        pagado,
                                        anio_cuenta
                                    FROM cuenta 
                                    WHERE estado IN ('Aprobado', 'Pendiente') 
                                    AND estado_final != 'Rechazado'
                                    AND estado_seguridad_social != 'Rechazado'";

                        if (!empty($mesSeleccionado)) {
                            $mesFiltrado = $mysqli->real_escape_string($mesSeleccionado);
                            $sql .= " AND Periodo_Facturacion = '$mesFiltrado'";
                        }

                        $sql .= " ORDER BY fecha_subida ASC";

                        if ($result = $mysqli->query($sql)) {
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()):
                                    $nombres   = trim(($row['primer_nombre'] ?? '') . ' ' . ($row['segundo_nombre'] ?? ''));
                                    $apellidos = trim(($row['primer_apellido'] ?? '') . ' ' . ($row['segundo_apellido'] ?? ''));
                        ?>
                                    <tr>
                                        <td style='text-align: center; vertical-align: middle; font-size: 13px'>
                                            <?= htmlspecialchars($row['fecha_subida']) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 13px'>
                                            ARB_CUE<?= htmlspecialchars($row['id']) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 13px'>
                                            <a href="index.php?page=cuentas/validacion/revisar_cuentas&id=<?= urlencode($row['id']) ?>&numero_identidad=<?= urlencode($row['numero_identidad']) ?><?= isset($_GET['mes']) && $_GET['mes'] !== '' ? '&mes=' . urlencode($_GET['mes']) : '' ?>" class="btn btn-link p-0" style='text-align: center; vertical-align: middle; font-size: 13px'>
                                                <?= htmlspecialchars($row['numero_identidad']) ?>
                                            </a>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 13px'>
                                            <?= htmlspecialchars($nombres) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 13px'>
                                            <?= htmlspecialchars($apellidos) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 13px'>
                                            <?= htmlspecialchars($row['cargo']) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 13px'>
                                            <?= htmlspecialchars($row['proyecto']) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 13px' style="background-color: #f7de7a; font-weight: bold;">
                                            $<?= number_format($row['valor'], 0, ',', '.') ?>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle; font-size: 14px">
                                            <?= !empty($row['anio_cuenta']) ? htmlspecialchars($row['anio_cuenta']) : 'Año no disponible' ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 13px'>
                                            <?= htmlspecialchars($row['Periodo_Facturacion']) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 13px' style="background-color: #50c676; font-weight: bold;">
                                            $<?= number_format($row['valor_aprobado'], 0, ',', '.') ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 13px'>
                                            <?php if (!empty($row['estado']) && $row['estado'] == 'Aprobado'): ?>
                                                <i class="fa-solid fa-circle-check fa-lg" style="color: rgb(24, 167, 22);"></i>
                                            <?php else: ?>
                                                <i class="fa-solid fa-spinner fa-lg fa-spin" style="color:rgb(56, 133, 195);"></i>
                                            <?php endif; ?>
                                        </td>
                                        <!-- <td style='text-align: center; vertical-align: middle; font-size: 14px'>
                                            <?php if (!empty($row['estado_seguridad_social']) && $row['estado_seguridad_social'] == 'Aprobado'): ?>
                                                <i class="fa-solid fa-circle-check fa-lg" style="color: rgb(24, 167, 22);"></i>
                                            <?php else: ?>
                                                <i class="fa-solid fa-spinner fa-lg fa-spin" style="color:rgb(56, 133, 195);"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 14px'>
                                            <?php if (!empty($row['estado_final']) && $row['estado_final'] == 'Aprobado'): ?>
                                                <i class="fa-solid fa-circle-check fa-lg" style="color: rgb(24, 167, 22);"></i>
                                            <?php else: ?>
                                                <i class="fa-solid fa-spinner fa-lg fa-spin" style="color:rgb(56, 133, 195);"></i>
                                            <?php endif; ?>
                                        </td> -->
                                    </tr>
                                <?php
                                endwhile;
                            } else {
                                ?>
                                <tr>
                                    <td colspan="13" class="text-center">No se encontraron registros disponibles</td>
                                </tr>
                            <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="13" class="text-center">
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


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://kit.fontawesome.com/9944c94262.js" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>

<script>
    $(document).ready(function() {
        var tabla;
        if ($.fn.DataTable.isDataTable('#dataTable')) {
            tabla = $('#dataTable').DataTable();
        } else {
            tabla = $('#dataTable').DataTable({
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
        }

        function toggleColumn(colIndex, show) {
            var idx = colIndex - 1;
            tabla.column(idx).visible(show, false);
            tabla.columns.adjust().draw(false);
        }

        $('.toggle-col').each(function() {
            var col = parseInt($(this).data('col'), 10);
            var show = $(this).is(':checked');
            toggleColumn(col, show);
        });

        $('.toggle-col').on('change', function() {
            var col = parseInt($(this).data('col'), 10);
            var show = $(this).is(':checked');
            toggleColumn(col, show);
        });
    });

    function toggleValor() {
        var valor = document.getElementById("valorTotal");
        var icono = document.getElementById("icono");

        if (valor.style.display === "none") {
            valor.style.display = "inline";
            icono.classList.remove("fa-eye-slash");
            icono.classList.add("fa-eye");
        } else {
            valor.style.display = "none";
            icono.classList.remove("fa-eye");
            icono.classList.add("fa-eye-slash");
        }
    }

    function toggleValorPagado() {
        var span = document.getElementById("valorTotalPagado");
        var icono = document.getElementById("iconoPagado");

        if (span.style.display === "none") {
            span.style.display = "inline";
            icono.classList.remove("fa-eye-slash");
            icono.classList.add("fa-eye");
        } else {
            span.style.display = "none";
            icono.classList.remove("fa-eye");
            icono.classList.add("fa-eye-slash");
        }
    }

    function toggleValoraprobado() {
        var span = document.getElementById("valorTotalaprobado");
        var icono = document.getElementById("iconoAprobado");

        if (span.style.display === "none") {
            span.style.display = "inline";
            icono.classList.remove("fa-eye-slash");
            icono.classList.add("fa-eye");
        } else {
            span.style.display = "none";
            icono.classList.remove("fa-eye");
            icono.classList.add("fa-eye-slash");
        }
    }

    function toggleValorDiferencia() {
        var valorDiferencia = document.getElementById("valorDiferencia");
        var iconoDiferencia = document.getElementById("iconoDiferencia");

        if (valorDiferencia.style.display === "none") {
            valorDiferencia.style.display = "inline";
            iconoDiferencia.classList.remove("fa-eye-slash");
            iconoDiferencia.classList.add("fa-eye");
        } else {
            valorDiferencia.style.display = "none";
            iconoDiferencia.classList.remove("fa-eye");
            iconoDiferencia.classList.add("fa-eye-slash");
        }
    }

    function toggleValorFecha() {
        var span = document.getElementById("valorTotalFecha");
        var icono = document.getElementById("iconoFecha");

        if (span.style.display === "none") {
            span.style.display = "inline";
            icono.classList.remove("fa-eye-slash");
            icono.classList.add("fa-eye");
        } else {
            span.style.display = "none";
            icono.classList.remove("fa-eye");
            icono.classList.add("fa-eye-slash");
        }
    }

    function toggleBloqueResumen() {
        var bloque = document.getElementById("bloqueResumenMeses");
        var icono = document.getElementById("iconBloqueResumen");
        var mini = document.getElementById("resumenMinimizado");

        if (!bloque) return;

        if (bloque.style.display === "" || bloque.style.display === "block") {
            bloque.style.display = "none";
            if (mini) {
                mini.style.display = "block";
            }
            if (icono) {
                icono.classList.remove("fa-eye-slash");
                icono.classList.add("fa-eye");
            }
        } else {
            bloque.style.display = "block";
            if (mini) {
                mini.style.display = "none";
            }
            if (icono) {
                icono.classList.remove("fa-eye");
                icono.classList.add("fa-eye-slash");
            }
        }
    }
</script>