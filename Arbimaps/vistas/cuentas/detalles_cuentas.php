<?php
$esDirectorPresupuesto = isset($_SESSION['rol_usuario'])
    && $_SESSION['rol_usuario'] === 'director_presupuestos';

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

$mesSeleccionado    = (isset($_GET['mes']) && $_GET['mes'] !== '') ? trim($_GET['mes']) : '';
$anioSeleccionado   = (isset($_GET['anio']) && $_GET['anio'] !== '') ? trim($_GET['anio']) : 'todas';

$tieneMes = ($mesSeleccionado !== '');

$aniosDisponibles = [];
$sqlAnios = "SELECT DISTINCT anio_cuenta 
                FROM cuenta 
                WHERE anio_cuenta IS NOT NULL AND anio_cuenta != '' 
                ORDER BY anio_cuenta ASC";
$resultAnios = $mysqli->query($sqlAnios);
if ($resultAnios && $resultAnios->num_rows > 0) {
    while ($row = $resultAnios->fetch_assoc()) {
        $aniosDisponibles[] = $row['anio_cuenta'];
    }
}
if (empty($aniosDisponibles)) {
    $aniosDisponibles = [date('Y')];
}
$anioFiltrado       = $mysqli->real_escape_string($anioSeleccionado);
$mesWhereCuenta     = "";
$mesWherePagadas    = "";

if ($tieneMes) {
    $mesEsc             = $mysqli->real_escape_string($mesSeleccionado);
    $mesWhereCuenta     = " AND TRIM(Periodo_Facturacion) = '$mesEsc' ";
    $mesWherePagadas    = " AND TRIM(pag_Periodo_Facturacion) = '$mesEsc' ";
}

$whereAnioCuenta = ($anioSeleccionado == 'todas') ? '' : "AND anio_cuenta = '$anioFiltrado'";
$sql = "SELECT COALESCE(SUM(valor), 0) AS total 
        FROM cuenta 
        WHERE 1=1 $whereAnioCuenta $mesWhereCuenta";
$result = $mysqli->query($sql);
$total  = ($result && $result->num_rows > 0) ? (float)$result->fetch_assoc()["total"] : 0;

$whereAnioPagadas = ($anioSeleccionado == 'todas') ? '' : "AND pag_anio_cuenta = '$anioFiltrado'";
$sql = "SELECT COALESCE(SUM(pag_valor_aprobado), 0) AS total_pagado 
        FROM cuentas_pagadas 
        WHERE 1=1 $whereAnioPagadas $mesWherePagadas";
$result_pagado  = $mysqli->query($sql);
$total_pagado   = ($result_pagado && $result_pagado->num_rows > 0) ? (float)$result_pagado->fetch_assoc()["total_pagado"] : 0;

$whereAnioAprobado = ($anioSeleccionado == 'todas') ? '' : "AND anio_cuenta = '$anioFiltrado'";
$sql = "SELECT COALESCE(SUM(valor_aprobado), 0) AS valor_aprobado 
        FROM cuenta 
        WHERE estado_final = 'Aprobado' $whereAnioAprobado $mesWhereCuenta";
$result_aprobado = $mysqli->query($sql);
$valor_aprobado = ($result_aprobado && $result_aprobado->num_rows > 0)
    ? (float)($result_aprobado->fetch_assoc()["valor_aprobado"] ?? 0)
    : 0;

$diferencia_valor   = $total - $valor_aprobado;
$total_mes          = $total + $total_pagado;

$colCuenta  = "creado_en";
$colPagadas = "pag_creado_en";

$checkCuenta = $mysqli->query("SHOW COLUMNS FROM cuenta LIKE 'fecha_subida'");
if ($checkCuenta && $checkCuenta->num_rows > 0) {
    $colCuenta = "fecha_subida";
}
$checkPagadas = $mysqli->query("SHOW COLUMNS FROM cuentas_pagadas LIKE 'pag_fecha_subida'");
if ($checkPagadas && $checkPagadas->num_rows > 0) {
    $colPagadas = "pag_fecha_subida";
}


$condMesCuentaFecha     = "";
$condMesPagadasFecha    = "";

if ($tieneMes && isset($meses[$mesSeleccionado])) {
    $numeroMes = (int)$meses[$mesSeleccionado];
    $mes2 = str_pad((string)$numeroMes, 2, "0", STR_PAD_LEFT);
    $condMesCuentaFecha     = " AND DATE_FORMAT($colCuenta, '%m') = '$mes2' ";
    $condMesPagadasFecha    = " AND DATE_FORMAT($colPagadas, '%m') = '$mes2' ";
}

// ---Sumar cuentas cargadas (tabla cuenta)
$whereYearCuenta = ($anioSeleccionado == 'todas') ? '' : "AND YEAR($colCuenta) = '$anioFiltrado'";
$sqlCuenta = "SELECT COALESCE(SUM(valor), 0) AS total_cuentas
            FROM cuenta
            WHERE 1=1 $whereYearCuenta $condMesCuentaFecha";
$resCuenta = $mysqli->query($sqlCuenta);
$totalCuenta = ($resCuenta && $resCuenta->num_rows > 0)
    ? (float)$resCuenta->fetch_assoc()['total_cuentas'] : 0;


// ---Sumar cuentas pagadas cargadas (tabla cuentas_pagadas)
$whereYearPagadas = ($anioSeleccionado == 'todas') ? '' : "AND YEAR($colPagadas) = '$anioFiltrado'";
$sqlPagadas = "SELECT COALESCE(SUM(pag_valor_aprobado), 0) AS total_pagadas
            FROM cuentas_pagadas
            WHERE 1=1 $whereYearPagadas $condMesPagadasFecha";
$resPagadas = $mysqli->query($sqlPagadas);
$totalPagadas = ($resPagadas && $resPagadas->num_rows > 0)
    ? (float)$resPagadas->fetch_assoc()['total_pagadas'] : 0;

$total_por_fecha = $totalCuenta + $totalPagadas;


$desglose_por_mes   = [];
$temp               = [];

$sqlCuentaDesglose = "
    SELECT 
        COALESCE(TRIM(Periodo_Facturacion), 'Sin período') AS periodo,
        COALESCE(SUM(valor), 0) AS total_cobrado,
        COUNT(*) AS cantidad
    FROM cuenta
    WHERE 1=1 $whereYearCuenta $condMesCuentaFecha
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

$sqlPagadasDesglose = "
    SELECT 
        COALESCE(TRIM(pag_Periodo_Facturacion), 'Sin período') AS periodo,
        COALESCE(SUM(pag_valor_aprobado), 0) AS total_cobrado,
        COUNT(*) AS cantidad
    FROM cuentas_pagadas
    WHERE 1=1 $whereYearPagadas $condMesPagadasFecha
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

// ===============================
// NUEVO: Pagos del mes (por pag_fecha_pago y pag_valor)
// ===============================
$total_pagado_mes = 0;
$cantidad_pagado_mes = 0;

$condMesPagoFecha = "";
$whereYearPagoFecha = "";

// Si seleccionó mes y existe en el array $meses
if ($tieneMes && isset($meses[$mesSeleccionado])) {
    $numeroMes = (int)$meses[$mesSeleccionado];

    // Filtrar por mes de la fecha de pago (pag_fecha_pago)
    $condMesPagoFecha = " AND MONTH(pag_fecha_pago) = $numeroMes ";

    // Filtrar por año de la fecha de pago (si no es 'todas')
    if ($anioSeleccionado != 'todas') {
        $whereYearPagoFecha = " AND YEAR(pag_fecha_pago) = '$anioFiltrado' ";
    }

    $sqlPagosMes = "
        SELECT 
            COALESCE(SUM(pag_valor), 0) AS total_pagado_mes,
            COUNT(*) AS cantidad_pagado_mes
        FROM cuentas_pagadas
        WHERE pag_fecha_pago IS NOT NULL
        $whereYearPagoFecha
        $condMesPagoFecha
    ";

    $resPagosMes = $mysqli->query($sqlPagosMes);
    if ($resPagosMes && $resPagosMes->num_rows > 0) {
        $rowPagosMes = $resPagosMes->fetch_assoc();
        $total_pagado_mes = (float)$rowPagosMes['total_pagado_mes'];
        $cantidad_pagado_mes = (int)$rowPagosMes['cantidad_pagado_mes'];
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

    /* Número activo en la paginación */
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

    .col-toggle-pill:has(input[type="checkbox"]:checked) {
        background-color: #002F55;
        border: 1px solid #002F55;
        color: white;
    }

    .task-card {
        width: 100%;
        border-radius: 14px;
        padding: 16px;
        color: #fff;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        text-align: left;
        position: relative;
        box-shadow: 0 5px 8px rgba(0, 0, 0, 0.25);
        transition: all 0.3s ease;
    }

    .task-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 12px rgba(0, 0, 0, 0.35);
    }

    .valor-grande {
        font-size: 1.15em;
        font-weight: bold;
    }

    .task-icon {
        position: absolute;
        bottom: 10px;
        right: 15px;
        font-size: 17px;
        cursor: pointer;
    }

    .blue-card {
        background: linear-gradient(355deg, #348ea0, #0e64ae);
    }

    .blue-card2 {
        background: linear-gradient(1335deg, #34c759, #1a8e3f);
    }

    .blue-card3 {
        background: linear-gradient(1335deg, #9a6b4c, #ab6600);
    }

    .blue-card4 {
        background: linear-gradient(1335deg, #9b59b6, #6c3483);
    }

    .blue-card5 {
        background: linear-gradient(1335deg, #ffa726, #fb8c00);
    }

    .green-card6 {
        background: #1fb96b;
        color: #fff;
        border-radius: 12px;
        padding: 18px;
        position: relative;
        box-shadow: 0 10px 18px rgba(0, 0, 0, .12);
    }

    .card-resumen {
        background: linear-gradient(1335deg, #f9d423, #ffcc00);
        color: #222;
        overflow-y: auto;

    }

    .meses-vertical {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        margin-top: 0.5rem;
    }

    .mes-item-btn {
        width: 100%;
        border-radius: 10px;
        padding: 0.45rem 0.75rem;
        font-size: 0.85rem;
        font-weight: 500;
        color: #111827;
        background-color: #f9fafb;
        border: 1px solid #e5e7eb;
        text-align: left;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
    }

    .mes-item-btn span {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .mes-item-btn i {
        font-size: 0.85rem;
        color: #9ca3af;
    }

    .mes-item-btn:hover {
        background-color: #eef2ff;
        border-color: #c7d2fe;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.08);
    }

    .mes-item-btn.activo {
        background-color: #022F55;
        border-color: #022F55;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.25);
    }

    .mes-item-btn.activo i {
        color: #bfdbfe;
    }

    .btn-sm {
        background-color: #022F55;
        color: #ffffff;
    }
</style>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<div class="container-fluid">
    <div class="mb-2">
        <button class="btn btn-sm" onclick="toggleBloqueResumen()">
            <i id="iconBloqueResumen" class="fa-solid fa-eye-slash"></i>
            Detalles
        </button>
    </div>

    <div id="resumenMinimizado"
        class="card mb-2 shadow-sm border-0 rounded-4"
        style="width: 90%; margin-left: 5%; display:none; cursor:pointer;"
        onclick="toggleBloqueResumen()">
        <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
            <span>
                <i class="fa-solid fa-chart-pie"></i>
                Resumen cuentas
            </span>
            <small class="text-muted">Click para ver detalles</small>
        </div>
    </div>

    <div id="bloqueResumenMeses">
        <div class="row p-2 align-items-center">
            <div class="col-md-3 h-100" id="colFiltroMes">
                <form method="GET" id="formMes" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <?php if (isset($_GET['page']) && $_GET['page'] != ''): ?>
                        <input type="hidden" name="page" value="<?php echo htmlspecialchars($_GET['page']); ?>">
                    <?php endif; ?>

                    <div class="bg-white shadow p-3 rounded-4 text-center">
                        <div class="titulo-seccion-meses fw-bold pb-1 border-bottom">Filtrar por año y mes</div>
                        <div class="text-muted" style="font-size: 0.78rem; margin-bottom: 0.3rem;">
                            Selecciona un año y un mes para ver el resumen y las cuentas radicadas.
                        </div>
                        <div class="mb-3">
                            <label for="anio" class="form-label">Año</label>
                            <select name="anio" id="anio" class="form-select" onchange="this.form.submit()">
                                <option value="todas" <?php echo ($anioSeleccionado == "todas") ? 'selected' : ''; ?>>Todas</option>
                                <?php
                                foreach ($aniosDisponibles as $a) {
                                    $selected = ($anioSeleccionado == $a) ? 'selected' : '';
                                    echo "<option value=\"$a\" $selected>$a</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="meses-vertical">
                            <?php
                            $mesesNombres = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
                            foreach ($mesesNombres as $mes) {
                                $activo = ($mesSeleccionado === $mes) ? 'activo' : '';
                                echo '
                                    <button type="submit" name="mes" value="' . $mes . '" class="mes-item-btn ' . $activo . '">
                                        <span><i class="bi bi-calendar3"></i>' . $mes . '</span>
                                        <i class="bi bi-chevron-right"></i>
                                    </button>
                                ';
                            }
                            ?>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-md-9 h-100" id="colResumenMes">
                <div class="row px-2">
                    <div class="card col-12 mb-3 px-0 shadow-lg border-0 rounded-4">
                        <div class="card-header">
                            <div class="mb-0 fw-bold text-center" style="color: #002F55; font-size:1.2em; font-weight: 700 !important;">
                                RESUMEN CUENTAS COORESPONDIENTE AL PERIODO PRESTADO
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 p-2">
                                    <div class="task-card blue-card h-100">
                                        <p><b>VALOR COBRADO X PRESTADORES</b><br>
                                            Cuentas Periodo Facturado:
                                            <b><i><?php echo $tieneMes ? ($mesSeleccionado . ' ' . ($anioSeleccionado == 'todas' ? 'Todos los años' : $anioSeleccionado)) : (($anioSeleccionado == 'todas') ? 'Todos los años' : 'Año ' . $anioSeleccionado); ?></i></b><br>
                                            <span id="valorTotal" class="valor-grande" style="display:none;">
                                                $<?php echo number_format($total_mes, 2, ',', '.'); ?>
                                            </span>
                                        </p>
                                        <div class="task-icon" onclick="toggleValor()">
                                            <i id="icono" class="fa-solid fa-eye-slash"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 p-2">
                                    <div class="task-card blue-card2 h-100">
                                        <p><b>VALOR PAGADO X ARBITRIUM</b><br>Cuentas Pagadas:
                                            <b><i><?php echo $tieneMes ? ($mesSeleccionado . ' ' . ($anioSeleccionado == 'todas' ? 'Todos los años' : $anioSeleccionado)) : (($anioSeleccionado == 'todas') ? 'Todos los años' : 'Año ' . $anioSeleccionado); ?></i></b><br>
                                            <span id="valorTotalPagado" class="valor-grande" style="display:none;">
                                                $<?php echo number_format($total_pagado, 2, ',', '.'); ?>
                                            </span>
                                        </p>
                                        <div class="task-icon" onclick="toggleValorPagado()">
                                            <i id="iconoPagado" class="fa-solid fa-eye-slash"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 p-2">
                                    <div class="task-card blue-card3 h-100">
                                        <p><b>VALOR APROBADO X SUPERVISORES</b><br>Cuentas Aprobadas:
                                            <b><i><?php echo $tieneMes ? ($mesSeleccionado . ' ' . ($anioSeleccionado == 'todas' ? 'Todos los años' : $anioSeleccionado)) : (($anioSeleccionado == 'todas') ? 'Todos los años' : 'Año ' . $anioSeleccionado); ?></i></b><br>
                                            <span id="valorTotalaprobado" class="valor-grande" style="display:none;">
                                                $<?php echo number_format($valor_aprobado, 2, ',', '.'); ?>
                                            </span>
                                        </p>
                                        <div class="task-icon" onclick="toggleValoraprobado()">
                                            <i id="iconoAprobado" class="fa-solid fa-eye-slash"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 p-2">
                                    <div class="task-card blue-card4 h-100">
                                        <p><b>RESTANTE X VALIDAR</b><br>Pendiente x Aprobar:
                                            <b><i><?php echo $tieneMes ? ($mesSeleccionado . ' ' . ($anioSeleccionado == 'todas' ? 'Todos los años' : $anioSeleccionado)) : (($anioSeleccionado == 'todas') ? 'Todos los años' : 'Año ' . $anioSeleccionado); ?></i></b><br>
                                            <span id="valorDiferencia" class="valor-grande" style="display:none;">
                                                $<?php echo number_format($diferencia_valor, 2, ',', '.'); ?>
                                            </span>
                                        </p>
                                        <div class="task-icon" onclick="toggleValorDiferencia()">
                                            <i id="iconoDiferencia" class="fa-solid fa-eye-slash"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card col-12 mb-3 px-0 shadow-lg border-0 rounded-4">
                        <div class="card-header">
                            <div class="mb-0 fw-bold text-center" style="color: #002F55; font-size:1.2em; font-weight: 700 !important;">
                                <?php if ($tieneMes): ?>
                                    CUENTAS COBRADAS DURANTE <?php echo mb_strtoupper($mesSeleccionado . ' ' . ($anioSeleccionado == 'todas' ? 'TODOS LOS AÑOS' : $anioSeleccionado), 'UTF-8'); ?>
                                <?php else: ?>
                                    CUENTAS COBRADAS DURANTE <?php echo ($anioSeleccionado == 'todas') ? 'TODOS LOS AÑOS' : 'EL AÑO ' . mb_strtoupper($anioSeleccionado, 'UTF-8'); ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-12 col-md-6 p-2">
                                    <div class="row g-2"><!-- g-2 = espacio entre tarjetas -->

                                        <!-- TARJETA NARANJA -->
                                        <div class="col-12">
                                            <div class="task-card blue-card5">
                                                <p><b>VALOR</b><br>
                                                    <?php if ($tieneMes): ?>
                                                        Cuentas Subidas Durante el Mes: <b><i><?php echo $mesSeleccionado; ?></i></b><br>
                                                    <?php else: ?>
                                                        Cuentas Subidas Durante <?php echo ($anioSeleccionado == 'todas') ? 'Todos los años' : 'el Año'; ?>:
                                                        <b><i><?php echo ($anioSeleccionado == 'todas') ? 'Todos los años' : $anioSeleccionado; ?></i></b><br>
                                                    <?php endif; ?>
                                                    <span id="valorTotalFecha" class="valor-grande" style="display:none;">
                                                        $<?php echo number_format($total_por_fecha, 2, ',', '.'); ?>
                                                    </span>
                                                </p>
                                                <div class="task-icon" onclick="toggleValorFecha()">
                                                    <i id="iconoFecha" class="fa-solid fa-eye-slash"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- TARJETA VERDE (NUEVA) CON OJITO -->
                                        <div class="col-12">
                                            <div class="task-card green-card6">
                                                <p><b>VALORES PAGADOS</b><br>
                                                    <?php if ($tieneMes): ?>
                                                        Pagados durante el mes:
                                                        <b><i><?php echo $mesSeleccionado . " " . (($anioSeleccionado == 'todas') ? '' : $anioSeleccionado); ?></i></b><br>
                                                        <span id="valorPagadoMes" class="valor-grande" style="display:none;">
                                                            $<?php echo number_format($total_pagado_mes, 0, ',', '.'); ?>
                                                        </span>
                                                        <span id="cantidadPagadoMes" style="display:none;">
                                                            <br><small>(<?php echo $cantidad_pagado_mes; ?> pagos)</small>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="sin-registros">Selecciona un mes para ver los pagos del mes.</span>
                                                    <?php endif; ?>
                                                </p>
                                                <?php if ($tieneMes): ?>
                                                    <div class="task-icon" onclick="toggleValorPagadoMes()">
                                                        <i id="iconoPagadoMes" class="fa-solid fa-eye-slash"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="col-12 col-md-6 p-2">
                                    <div class="row g-2">
                                        <!-- TARJETA AMARILLA (la que ya tienes) -->
                                        <div class="col-12">
                                            <div class="task-card card-resumen">
                                                <p><b>Resumen</b><br>
                                                    <?php if (!empty($desglose_por_mes)): ?>
                                                <div class="scrollable-resumen">
                                                    <?php foreach ($desglose_por_mes as $d): ?>
                                                        <div>
                                                            <b><?php echo htmlspecialchars($d['periodo']); ?>:</b>
                                                            $<?php echo number_format($d['total'], 0, ',', '.'); ?>
                                                            <small>(<?php echo $d['cantidad']; ?> cuentas)</small>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <p class="sin-registros">Sin registros disponibles</p>
                                            <?php endif; ?>
                                            </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4" style="border-radius:15px;">
        <div class="card-header py-3 text-center d-flex flex-row align-items-center justify-content-between"
            style="background: linear-gradient(355deg, #0e64ae, #348ea0); border-radius:0px 15px 0px 0px;">
            <div class="d-flex align-items-center ">
                <div class="d-flex justify-content-center align-items-center me-3 rounded-5  p-2"
                    style="width: 35px; height: 35px; background-color: #ffffffff;">
                    <i class="bi bi-journal-check " style="color: #002F55;"></i>
                </div>
                <div>
                    <div class=" text-start text-white" style="font-size: 1em;  font-weight: 700;">
                        CUENTAS RADICADAS
                    </div>
                    <div style="font-size: 70%; color: #f3f8fdff;" class="text-start">
                        Listado de cuentas con su estado y valores asociados.
                    </div>
                </div>
            </div>

            <div class="d-none d-md-flex align-items-center gap-2 col-toggle-bar" style="color: #002f55; font-size: 0.9rem;">
                <div class="col-toggle-chip me-2">
                    <i class="fas fa-sliders-h me-1"></i>
                    <span>Agregar columnas</span>
                </div>
                <label class="col-toggle-pill me-1">
                    <input type="checkbox" class="toggle-col" data-col="8">
                    <span>Valor x cobrar</span>
                </label>
                <label class="col-toggle-pill me-1">
                    <input type="checkbox" class="toggle-col" data-col="6">
                    <span>Rol</span>
                </label>
            </div>

        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th style="text-align: center; vertical-align: middle; font-size: 12px">Fecha Radicación</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 12px">N° Rad</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 12px">Cédula</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 12px">Nombres</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 12px">Apellidos</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 12px">Rol</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 12px">Proyecto</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 12px">Valor X Cobrar</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 12px">Año</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 12px">Periodo</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 12px">Valor aprobado</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 12px">J. Camilo</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 12px">Katherine</th>
                            <th style="text-align: center; vertical-align: middle; font-size: 12px">J.Ramon</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
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

                        if ($tieneMes) {
                            $mesFiltrado = $mysqli->real_escape_string($mesSeleccionado);
                            $sql .= " AND Periodo_Facturacion = '$mesFiltrado'";
                        }
                        $whereAnioTabla = ($anioSeleccionado == 'todas') ? '' : "AND anio_cuenta = '$anioFiltrado'";
                        $sql .= " $whereAnioTabla";
                        $sql .= " ORDER BY fecha_subida ASC";

                        if ($result = $mysqli->query($sql)) {
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()):
                                    $nombres   = trim(($row['primer_nombre'] ?? '') . ' ' . ($row['segundo_nombre'] ?? ''));
                                    $apellidos = trim(($row['primer_apellido'] ?? '') . ' ' . ($row['segundo_apellido'] ?? ''));
                        ?>
                                    <tr>
                                        <td style='text-align: center; vertical-align: middle; font-size: 12px'>
                                            <?= htmlspecialchars($row['fecha_subida']) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 12px'>
                                            ARB_CUE<?= htmlspecialchars($row['id']) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 12px'>
                                            <a style='text-align: center; vertical-align: middle; font-size: 12px'
                                                href="index.php?page=cuentas/validacion/revisar_cuentas&id=<?= urlencode($row['id']) ?>&numero_identidad=<?= urlencode($row['numero_identidad']) ?><?= $tieneMes ? '&mes=' . urlencode($mesSeleccionado) : '' ?>&anio=<?= urlencode($anioSeleccionado) ?>"
                                                class="btn btn-link p-0">
                                                <?= htmlspecialchars($row['numero_identidad']) ?>
                                            </a>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 12px'>
                                            <?= htmlspecialchars($nombres) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 12px'>
                                            <?= htmlspecialchars($apellidos) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 12px'>
                                            <?= htmlspecialchars($row['cargo']) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 12px'>
                                            <?= htmlspecialchars($row['proyecto']) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 12px; background-color: #f7de7a; font-weight: bold;'>
                                            $<?= number_format((float)$row['valor'], 0, ',', '.') ?>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle; font-size: 12px">
                                            <?= !empty($row['anio_cuenta']) ? htmlspecialchars($row['anio_cuenta']) : 'Año no disponible' ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 12px'>
                                            <?= htmlspecialchars($row['Periodo_Facturacion']) ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 12px; background-color: #50c676; font-weight: bold;'>
                                            $<?= number_format((float)$row['valor_aprobado'], 0, ',', '.') ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 12px'>
                                            <?php if (!empty($row['estado']) && $row['estado'] == 'Aprobado'): ?>
                                                <i class="fa-solid fa-circle-check fa-lg" style="color: rgb(24, 167, 22);"></i>
                                            <?php else: ?>
                                                <i class="fa-solid fa-spinner fa-lg fa-spin" style="color:rgb(56, 133, 195);"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 12px'>
                                            <?php if (!empty($row['estado_seguridad_social']) && $row['estado_seguridad_social'] == 'Aprobado'): ?>
                                                <i class="fa-solid fa-circle-check fa-lg" style="color: rgb(24, 167, 22);"></i>
                                            <?php else: ?>
                                                <i class="fa-solid fa-spinner fa-lg fa-spin" style="color:rgb(56, 133, 195);"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle; font-size: 12px'>
                                            <?php if (!empty($row['estado_final']) && $row['estado_final'] == 'Aprobado'): ?>
                                                <i class="fa-solid fa-circle-check fa-lg" style="color: rgb(24, 167, 22);"></i>
                                            <?php else: ?>
                                                <i class="fa-solid fa-spinner fa-lg fa-spin" style="color:rgb(56, 133, 195);"></i>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php
                                endwhile;
                            } else {
                                ?>
                                <tr>
                                    <td colspan="14" class="text-center">No se encontraron registros disponibles</td>
                                </tr>
                            <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="14" class="text-center">
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
            if (mini) mini.style.display = "block";
            if (icono) {
                icono.classList.remove("fa-eye-slash");
                icono.classList.add("fa-eye");
            }
        } else {
            bloque.style.display = "block";
            if (mini) mini.style.display = "none";
            if (icono) {
                icono.classList.remove("fa-eye");
                icono.classList.add("fa-eye-slash");
            }
        }
    }

    function toggleValorPagadoMes() {
        var valor = document.getElementById("valorPagadoMes");
        var cantidad = document.getElementById("cantidadPagadoMes");
        var icono = document.getElementById("iconoPagadoMes");

        if (!valor || !icono) return;

        if (valor.style.display === "none") {
            valor.style.display = "inline";
            if (cantidad) cantidad.style.display = "inline";
            icono.classList.remove("fa-eye-slash");
            icono.classList.add("fa-eye");
        } else {
            valor.style.display = "none";
            if (cantidad) cantidad.style.display = "none";
            icono.classList.remove("fa-eye");
            icono.classList.add("fa-eye-slash");
        }
    }
</script>