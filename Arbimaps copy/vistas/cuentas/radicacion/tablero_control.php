<?php
$mesesNombre = [
    '01' => 'Enero',
    '02' => 'Febrero',
    '03' => 'Marzo',
    '04' => 'Abril',
    '05' => 'Mayo',
    '06' => 'Junio',
    '07' => 'Julio',
    '08' => 'Agosto',
    '09' => 'Septiembre',
    '10' => 'Octubre',
    '11' => 'Noviembre',
    '12' => 'Diciembre',
];

if (array_key_exists('mes', $_GET)) {
    if ($_GET['mes'] === '') {
        $mesSeleccionado = '';
    } else {
        $mesSeleccionado = $mysqli->real_escape_string($_GET['mes']);
    }
} else {
    $mesActualNumero = date('m');
    $mesSeleccionado = $mesesNombre[$mesActualNumero];
}

$proyectoSeleccionado = isset($_GET['Proyecto']) ? $mysqli->real_escape_string($_GET['Proyecto']) : '';
$rolSeleccionado      = isset($_GET['Rol']) ? $mysqli->real_escape_string($_GET['Rol']) : '';

$anioSeleccionado = isset($_GET['anio']) ? $mysqli->real_escape_string($_GET['anio']) : date('Y');

$aniosDisponibles = [];
$sqlAnios = "SELECT DISTINCT anio_cuenta AS anio FROM cuenta WHERE anio_cuenta IS NOT NULL UNION SELECT DISTINCT pag_anio_cuenta AS anio FROM cuentas_pagadas WHERE pag_anio_cuenta IS NOT NULL UNION SELECT DISTINCT rec_anio_cuenta AS anio FROM cuentas_rachazadas WHERE rec_anio_cuenta IS NOT NULL ORDER BY anio DESC";
$resultAnios = $mysqli->query($sqlAnios);
if ($resultAnios && $resultAnios->num_rows > 0) {
    while ($row = $resultAnios->fetch_assoc()) {
        $aniosDisponibles[] = $row['anio'];
    }
}

$aniosDisponibles = array_unique(array_merge($aniosDisponibles, [2025, 2026]));
sort($aniosDisponibles);

if (empty($aniosDisponibles)) {
    $aniosDisponibles = [date('Y')];
}

$filtroCuenta  = "1=1";
$filtroPagadas = "1=1";

if ($mesSeleccionado !== '') {
    $filtroCuenta  .= " AND Periodo_Facturacion = '$mesSeleccionado'";
    $filtroPagadas .= " AND pag_Periodo_Facturacion = '$mesSeleccionado'";
}

if ($proyectoSeleccionado !== '') {
    $filtroCuenta  .= " AND proyecto = '$proyectoSeleccionado'";
    $filtroPagadas .= " AND pag_proyecto = '$proyectoSeleccionado'";
}

if ($rolSeleccionado !== '') {
    $filtroCuenta  .= " AND cargo = '$rolSeleccionado'";
    $filtroPagadas .= " AND pag_cargo = '$rolSeleccionado'";
}

if ($anioSeleccionado !== '') {
    $filtroCuenta  .= " AND anio_cuenta = '$anioSeleccionado'";
    $filtroPagadas .= " AND pag_anio_cuenta = '$anioSeleccionado'";
}

$sql    = "SELECT SUM(valor) AS total FROM cuenta WHERE $filtroCuenta";
$result = $mysqli->query($sql);
$row    = $result ? $result->fetch_assoc() : ['total' => 0];
$total  = $row['total'] ?? 0;

$sql           = "SELECT SUM(pag_valor_aprobado) AS total_pagado FROM cuentas_pagadas WHERE $filtroPagadas";
$result_pagado = $mysqli->query($sql);
$row_pagado    = $result_pagado ? $result_pagado->fetch_assoc() : ['total_pagado' => 0];
$total_pagado  = $row_pagado['total_pagado'] ?? 0;

$sql = "SELECT SUM(valor_aprobado) AS valor_aprobado 
        FROM cuenta 
        WHERE estado_final = 'Aprobado' AND $filtroCuenta";
$result_aprobado = $mysqli->query($sql);
$row_aprobado    = $result_aprobado ? $result_aprobado->fetch_assoc() : ['valor_aprobado' => 0];
$valor_aprobado  = $row_aprobado['valor_aprobado'] ?? 0;

$diferencia_valor = $total - $valor_aprobado;

$total_mes = $total + $total_pagado;

$sql = "SELECT pag_cargo, SUM(pag_valor) AS total_cargo 
        FROM cuentas_pagadas 
        WHERE $filtroPagadas 
        GROUP BY pag_cargo";
$result_cargos = $mysqli->query($sql);

$labels_cargos  = [];
$valores_cargos = [];

if ($result_cargos && $result_cargos->num_rows > 0) {
    while ($row = $result_cargos->fetch_assoc()) {
        $labels_cargos[]  = $row['pag_cargo'];
        $valores_cargos[] = $row['total_cargo'];
    }
}

$sql = "
SELECT 
    meses.mes,
    COALESCE(radicado.total_radicado, 0) AS total_radicado,
    COALESCE(pagado.total_pagado, 0) AS total_pagado
FROM (
    SELECT 'Enero' AS mes UNION SELECT 'Febrero' UNION SELECT 'Marzo' UNION SELECT 'Abril' 
    UNION SELECT 'Mayo' UNION SELECT 'Junio' UNION SELECT 'Julio' UNION SELECT 'Agosto' 
    UNION SELECT 'Septiembre' UNION SELECT 'Octubre' UNION SELECT 'Noviembre' UNION SELECT 'Diciembre'
) AS meses
LEFT JOIN (
    SELECT 
        Periodo_Facturacion AS mes,
    SUM(valor) AS total_radicado
    FROM cuenta
    WHERE anio_cuenta = '$anioSeleccionado'
    GROUP BY Periodo_Facturacion
) AS radicado ON meses.mes = radicado.mes
LEFT JOIN (
    SELECT 
        pag_Periodo_Facturacion AS mes,
    SUM(pag_valor_aprobado) AS total_pagado
    FROM cuentas_pagadas
    WHERE pag_anio_cuenta = '$anioSeleccionado'
    GROUP BY pag_Periodo_Facturacion
) AS pagado ON meses.mes = pagado.mes
ORDER BY 
    FIELD(meses.mes, 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');
";

$resultado = $mysqli->query($sql);

$meses     = [];
$radicados = [];
$pagados   = [];

if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        $meses[]     = $fila['mes'];
        $radicados[] = (int) $fila['total_radicado'];
        $pagados[]   = (int) $fila['total_pagado'];
    }
}




$anio = $anioSeleccionado;

$proyectos = [
    "ANT_AVALUOS",
    "ADMINISTRATIVO",
    "ARBIMAPS",
    "ARBITECH",
    "ASESOR_JURIDICO_LITIGIO",
    "ARBOLETES",
    "CONTABILIDAD",
    "CONSERVACION_CATASTRAL_VALOR+",
    "GESTION_SOCIAL",
    "LEIVA",
    "PQRS_BELLO",
    "NECOCLI",
    "SAN_JUAN",
    "SAN_PEDRO",
    "VALLE_ABURRA",
    "VALLE_GUAMUEZ"
];

$mesesOrdenados = [
    "Enero",
    "Febrero",
    "Marzo",
    "Abril",
    "Mayo",
    "Junio",
    "Julio",
    "Agosto",
    "Septiembre",
    "Octubre",
    "Noviembre",
    "Diciembre"
];

$datosProyectos = [];
foreach ($proyectos as $proyecto) {
    $datosProyectos[$proyecto] = array_fill(0, 12, 0);
}

$sql = "SELECT pag_proyecto, pag_Periodo_Facturacion, SUM(pag_valor_aprobado) AS total
        FROM cuentas_pagadas
        WHERE pag_anio_cuenta = $anio
        GROUP BY pag_Periodo_Facturacion, pag_proyecto";

$result = $mysqli->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $proyecto = strtoupper(trim($row['pag_proyecto']));
        $mes      = ucfirst(strtolower(trim($row['pag_Periodo_Facturacion'])));
        $valor    = (float) $row['total'];

        if (in_array($proyecto, $proyectos) && in_array($mes, $mesesOrdenados)) {
            $indiceMes = array_search($mes, $mesesOrdenados);
            $datosProyectos[$proyecto][$indiceMes] = $valor;
        }
    }
} else {
    echo "Error en la consulta de proyectos: " . $mysqli->error;
}

$labelsProyectos  = json_encode($proyectos);
$valoresProyectos = json_encode(array_values($datosProyectos));







$nombre = $_SESSION['nombre'] ?? '';

$getHiddenInputs = '';
foreach ($_GET as $key => $value) {
    if (!in_array($key, ['mes', 'Proyecto', 'Rol'])) {
        $getHiddenInputs .= '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">' . PHP_EOL;
    }
}
?>

<style>
    body {
        background: radial-gradient(circle at top, #eef2ff 0, #f9fafb 50%, #e5e7eb 100%);
        color: #111827;
    }

    .glass-panel {
        background: #ffffff;
        border-radius: 20px;
        padding: 22px 24px;
        box-shadow: 0 16px 35px rgba(15, 23, 42, 0.10);
        border: 1px solid #e5e7eb;
    }

    .dashboard-title i {
        font-size: 2rem;
        color: #f59e0b;
        padding: 10px;
        border-radius: 16px;
        background: radial-gradient(circle at top, rgba(245, 158, 11, 0.18), transparent 70%);
    }

    .filters-card {
        margin-top: 16px;
        margin-bottom: 18px;
        background: #f9fafb;
        border-radius: 16px;
        border: 1px solid #e5e7eb;
        padding: 14px 16px;
    }

    .filters-summary {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.5rem;
        background: #ffffff;
        border-radius: 999px;
        padding: 6px 10px;
        border: 1px dashed #e5e7eb;
        font-size: 0.8rem;
        color: #6b7280;
    }

    .filters-summary>span:first-child {
        flex-basis: 100%;
        text-align: center;
        font-weight: 600;
    }

    .badge-filter {
        background: #f3f4f6;
        border-radius: 999px;
        padding: 3px 8px;
        color: #111827;
        border: 1px solid #e5e7eb;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.7rem;
    }

    .badge-filter i {
        font-size: 0.9rem;
        color: #f59e0b;
        margin-right: 4px;
    }

    .kpi-card {
        border-radius: 16px;
        background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
        border: 1px solid #e5e7eb;
        position: relative;
        overflow: hidden;
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
    }

    .kpi-card::after {
        content: "";
        position: absolute;
        right: -30px;
        bottom: -30px;
        width: 120px;
        height: 120px;
        border-radius: 999px;
        background: radial-gradient(circle at center, rgba(59, 130, 246, 0.12), transparent 70%);
    }

    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 25px rgba(15, 23, 42, 0.18);
        border-color: #d1d5db;
    }

    .card-panel {
        border-radius: 18px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 12px 28px rgba(148, 163, 184, 0.20);
    }

    .chart-container {
        position: relative;
        height: 290px;
    }

    .chart-container-lg {
        position: relative;
        height: 380px;
    }
</style>

<div class="container-fluid py-3">
    <div class="glass-panel">
        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-start mb-4 flex-column flex-md-row gap-2">
            <div class="dashboard-title d-flex align-items-center gap-3">
                <i class="bi bi-speedometer2"></i>
                <div>
                    <div class="fw-bold fs-3 text-dark">
                        Tablero de Control y Seguimiento
                    </div>
                    <div class="text-muted small mt-1">
                        Radicadas, aprobadas y pagadas por periodo, con filtros dinámicos.
                    </div>
                </div>
            </div>
            <div>
                <span class="badge rounded-pill bg-success-subtle border border-success text-success d-inline-flex align-items-center gap-2 px-3 py-2">
                    <i class="bi bi-person-circle"></i>
                    <span class="small"><?php echo htmlspecialchars($nombre ?: 'Usuario'); ?></span>
                </span>
            </div>
        </div>

        <!-- FILTROS -->
        <form method="GET"
            action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>"
            id="formMes">

            <div class="filters-card">
                <?php echo $getHiddenInputs; ?>

                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-3">
                        <label for="mes" class="form-label text-uppercase small text-secondary fw-semibold mb-1">
                            Mes
                        </label>
                        <select name="mes" id="mes" class="form-select form-select-sm rounded-pill fw-medium"
                            onchange="this.form.submit();">
                            <option value="" <?php echo $mesSeleccionado === '' ? 'selected' : ''; ?>>Todos</option>
                            <?php
                            foreach ($mesesNombre as $num => $nombreMes) {
                                $selected = ($mesSeleccionado === $nombreMes) ? 'selected' : '';
                                echo "<option value=\"$nombreMes\" $selected>$nombreMes</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="Proyecto" class="form-label text-uppercase small text-secondary fw-semibold mb-1">
                            Proyecto
                        </label>
                        <select name="Proyecto" id="Proyecto" class="form-select form-select-sm rounded-pill fw-medium"
                            onchange="this.form.submit();">
                            <option value="">Todos</option>
                            <option value="ANT_AVALUOS" <?php if ($proyectoSeleccionado == 'ANT_AVALUOS') echo 'selected'; ?>>ANT_AVALUOS</option>
                            <option value="ADMINISTRATIVO" <?php if ($proyectoSeleccionado == 'ADMINISTRATIVO') echo 'selected'; ?>>ADMINISTRATIVO</option>
                            <option value="ARBIMAPS" <?php if ($proyectoSeleccionado == 'ARBIMAPS') echo 'selected'; ?>>ARBIMAPS</option>
                            <option value="ARBITECH" <?php if ($proyectoSeleccionado == 'ARBITECH') echo 'selected'; ?>>ARBITECH</option>
                            <option value="ASESOR_JURIDICO_LITIGIO" <?php if ($proyectoSeleccionado == 'ASESOR_JURIDICO_LITIGIO') echo 'selected'; ?>>ASESOR_JURIDICO_LITIGIO</option>
                            <option value="CONTABILIDAD" <?php if ($proyectoSeleccionado == 'CONTABILIDAD') echo 'selected'; ?>>CONTABILIDAD</option>
                            <option value="CONSERVACION_CATASTRAL_VALOR+" <?php if ($proyectoSeleccionado == 'CONSERVACION_CATASTRAL_VALOR+') echo 'selected'; ?>>CONSERVACION CATASTRAL VALOR +</option>
                            <option value="GESTION_SOCIAL" <?php if ($proyectoSeleccionado == 'GESTION_SOCIAL') echo 'selected'; ?>>GESTION SOCIAL</option>
                            <option value="LEIVA" <?php if ($proyectoSeleccionado == 'LEIVA') echo 'selected'; ?>>IGAC LEIVA</option>
                            <option value="VALLE_GUAMUEZ" <?php if ($proyectoSeleccionado == 'VALLE_GUAMUEZ') echo 'selected'; ?>>IGAC VALLE GUAMUEZ</option>
                            <option value="ARBOLETES" <?php if ($proyectoSeleccionado == 'ARBOLETES') echo 'selected'; ?>>VALOR + ARBOLETES</option>
                            <option value="NECOCLI" <?php if ($proyectoSeleccionado == 'NECOCLI') echo 'selected'; ?>>VALOR + NECOCLI</option>
                            <option value="SAN_JUAN" <?php if ($proyectoSeleccionado == 'SAN_JUAN') echo 'selected'; ?>>VALOR + SAN JUAN DE URABA</option>
                            <option value="SAN_PEDRO" <?php if ($proyectoSeleccionado == 'SAN_PEDRO') echo 'selected'; ?>>VALOR + SAN PEDRO DE URABA</option>
                            <option value="PQRS_BELLO" <?php if ($proyectoSeleccionado == 'PQRS_BELLO') echo 'selected'; ?>>PQRS - BELLO</option>
                            <option value="VALLE_ABURRA" <?php if ($proyectoSeleccionado == 'VALLE_ABURRA') echo 'selected'; ?>>VALLE DE ABURRA BELLO</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="Rol" class="form-label text-uppercase small text-secondary fw-semibold mb-1">
                            Rol
                        </label>
                        <select name="Rol" id="Rol" class="form-select form-select-sm rounded-pill fw-medium"
                            onchange="this.form.submit();">
                            <option value="">Todos</option>
                            <option value="Abogados" <?php if ($rolSeleccionado == 'Abogados') echo 'selected'; ?>>Abogados</option>
                            <option value="Asignador" <?php if ($rolSeleccionado == 'Asignador') echo 'selected'; ?>>Asignador</option>
                            <option value="Auxiliar_administrativo" <?php if ($rolSeleccionado == 'Auxiliar_administrativo') echo 'selected'; ?>>Aux. Administrativo</option>
                            <option value="Auxiliar_Social" <?php if ($rolSeleccionado == 'Auxiliar_Social') echo 'selected'; ?>>Aux. Social</option>
                            <option value="Auxiliar_Ventanilla" <?php if ($rolSeleccionado == 'Auxiliar_Ventanilla') echo 'selected'; ?>>Aux. Ventanilla</option>
                            <option value="Coordinador_Cuadrilla" <?php if ($rolSeleccionado == 'Coordinador_Cuadrilla') echo 'selected'; ?>>Coordinador Cuadrilla</option>
                            <option value="Consolidador" <?php if ($rolSeleccionado == 'Consolidador') echo 'selected'; ?>>Consolidador</option>
                            <option value="Consulta_VUR" <?php if ($rolSeleccionado == 'Consulta_VUR') echo 'selected'; ?>>Consulta VUR</option>
                            <option value="Control_Calidad" <?php if ($rolSeleccionado == 'Control_Calidad') echo 'selected'; ?>>Control de Calidad</option>
                            <option value="Desarrollador" <?php if ($rolSeleccionado == 'Desarrollador') echo 'selected'; ?>>Desarrollador</option>
                            <option value="Digitador" <?php if ($rolSeleccionado == 'Digitador') echo 'selected'; ?>>Digitador</option>
                            <option value="Digitalizador" <?php if ($rolSeleccionado == 'Digitalizador') echo 'selected'; ?>>Digitalizador</option>
                            <option value="Editor" <?php if ($rolSeleccionado == 'Editor') echo 'selected'; ?>>Editor</option>
                            <option value="Mergin_APP" <?php if ($rolSeleccionado == 'Mergin_APP') echo 'selected'; ?>>Mergin MAPS</option>
                            <option value="Presupuesto" <?php if ($rolSeleccionado == 'Presupuesto') echo 'selected'; ?>>Presupuesto</option>
                            <option value="Lider_Reconocimiento" <?php if ($rolSeleccionado == 'Lider_Reconocimiento') echo 'selected'; ?>>Lider Reconocimiento</option>
                            <option value="Profesional_Social" <?php if ($rolSeleccionado == 'Profesional_Social') echo 'selected'; ?>>Profesional Social</option>
                            <option value="Profesional_Catastral_I" <?php if ($rolSeleccionado == 'Profesional_Catastral_I') echo 'selected'; ?>>Profesional Catastral I</option>
                            <option value="Reconocedor_Predial" <?php if ($rolSeleccionado == 'Reconocedor_Predial') echo 'selected'; ?>>Reconocedor Predial</option>
                            <option value="Reconocedor_Junior" <?php if ($rolSeleccionado == 'Reconocedor_Junior') echo 'selected'; ?>>Reconocedor Junior</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="anio" class="form-label text-uppercase small text-secondary fw-semibold mb-1">
                            Año
                        </label>
                        <select name="anio" id="anio" class="form-select form-select-sm rounded-pill fw-medium"
                            onchange="this.form.submit();">
                            <?php
                            foreach ($aniosDisponibles as $a) {
                                $selected = ($anioSeleccionado == $a) ? 'selected' : '';
                                echo "<option value=\"$a\" $selected>$a</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-12 mt-2">
                        <div class="filters-summary">
                            <span>Filtros:</span>
                            <span class="badge-filter">
                                <i class="bi bi-calendar-event"></i>
                                <?php echo $mesSeleccionado ?: 'Todos los meses'; ?>
                            </span>
                            <span class="badge-filter">
                                <i class="bi bi-diagram-3"></i>
                                <?php echo $proyectoSeleccionado ?: 'Todos los proyectos'; ?>
                            </span>
                            <span class="badge-filter">
                                <i class="bi bi-people"></i>
                                <?php echo $rolSeleccionado ?: 'Todos los roles'; ?>
                            </span>
                            <span class="badge-filter">
                                <i class="bi bi-calendar"></i>
                                <?php echo $anioSeleccionado ?: date('Y'); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- KPIs -->
        <div class="row mt-3">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="kpi-card p-3 h-100">
                    <div class="text-uppercase small text-secondary fw-semibold mb-1">Total mes</div>
                    <div class="d-flex justify-content-between align-items-end position-relative">
                        <div>
                            <div class="fw-semibold mb-1">Radicadas + Pagadas</div>
                            <div class="fw-bold fs-4">
                                $<?php echo number_format($total_mes, 2, ',', '.'); ?>
                            </div>
                        </div>
                        <div class="display-6 text-warning opacity-50">
                            <i class="bi bi-clipboard-data"></i>
                        </div>
                    </div>
                    <div class="mt-2 small text-muted d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-light border text-secondary">
                            Periodo <?php echo htmlspecialchars($mesSeleccionado ?: 'Todos'); ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="kpi-card p-3 h-100">
                    <div class="text-uppercase small text-secondary fw-semibold mb-1">Pagado</div>
                    <div class="d-flex justify-content-between align-items-end position-relative">
                        <div>
                            <div class="fw-semibold mb-1">Cuentas Pagadas</div>
                            <div class="fw-bold fs-4">
                                $<?php echo number_format($total_pagado, 2, ',', '.'); ?>
                            </div>
                        </div>
                        <div class="display-6 text-success opacity-50">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                    <div class="mt-2 small text-muted">
                        <span class="badge rounded-pill bg-light border text-secondary">
                            Fuente: cuentas_pagadas
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="kpi-card p-3 h-100">
                    <div class="text-uppercase small text-secondary fw-semibold mb-1">Aprobado</div>
                    <div class="d-flex justify-content-between align-items-end position-relative">
                        <div>
                            <div class="fw-semibold mb-1">Cuentas Aprobadas</div>
                            <div class="fw-bold fs-4">
                                $<?php echo number_format($valor_aprobado, 2, ',', '.'); ?>
                            </div>
                        </div>
                        <div class="display-6 text-primary opacity-50">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                    </div>
                    <div class="mt-2 small text-muted">
                        <span class="badge rounded-pill bg-light border text-secondary">
                            Estado: Aprobado
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="kpi-card p-3 h-100">
                    <div class="text-uppercase small text-secondary fw-semibold mb-1">Pendiente</div>
                    <div class="d-flex justify-content-between align-items-end position-relative">
                        <div>
                            <div class="fw-semibold mb-1">Por aprobar</div>
                            <div class="fw-bold fs-4">
                                $<?php echo number_format($diferencia_valor, 2, ',', '.'); ?>
                            </div>
                        </div>
                        <div class="display-6 text-danger opacity-50">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                    <div class="mt-2 small text-muted">
                        <span class="badge rounded-pill bg-light border text-secondary">
                            Radicadas - Aprobadas
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRAFICAS RESUMEN -->
        <div class="row mt-2">
            <div class="col-lg-7 mb-3">
                <div class="card card-panel border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-bar-chart-line text-primary"></i>
                                    <span class="fw-semibold">Valores por Estado</span>
                                </div>
                                <div class="text-muted small">
                                    Comparación entre total del mes, pagado, aprobado y pendiente.
                                </div>
                            </div>
                            <div>
                                <span class="badge rounded-pill bg-light border text-secondary d-inline-flex align-items-center gap-1 small">
                                    <i class="bi bi-currency-dollar"></i> Pesos COP
                                </span>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="graficoValores"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 mb-3">
                <div class="card card-panel border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-pie-chart text-primary"></i>
                                    <span class="fw-semibold">Pagos por Cargo</span>
                                </div>
                                <div class="text-muted small">
                                    Distribución porcentual y valor pagado por cada cargo.
                                </div>
                            </div>
                            <div>
                                <span class="badge rounded-pill bg-light border text-secondary d-inline-flex align-items-center gap-1 small">
                                    <i class="bi bi-people"></i> Cargos
                                </span>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="graficoCargos"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RADICADAS VS PAGADAS -->
        <div class="mt-3">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-activity text-primary"></i>
                <span class="fw-semibold">Cuentas Radicadas vs Pagadas</span>
            </div>
            <div class="text-muted small mb-2">
                Evolución mensual de los valores radicados y pagados durante el año.
            </div>
            <div class="card card-panel border-0">
                <div class="card-body">
                    <div class="chart-container-lg">
                        <canvas id="graficoLineal"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- GASTOS POR PROYECTO -->
        <div class="mt-3">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-graph-up-arrow text-primary"></i>
                <span class="fw-semibold">Gastos por Proyecto</span>
            </div>
            <div class="text-muted small mb-2">
                Valor pagado por proyecto a lo largo del año <?php echo $anio; ?>.
            </div>
            <div class="card card-panel border-0">
                <div class="card-body">
                    <div class="chart-container-lg">
                        <canvas id="graficoLineasProyectos"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    Chart.defaults.color = '#111827';
    Chart.defaults.font.family = "'system-ui', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
    Chart.defaults.font.size = 11;

    const datosCargos = {
        labels: <?php echo json_encode($labels_cargos); ?>,
        valores: <?php echo json_encode($valores_cargos); ?>
    };

    const ctxCargos = document.getElementById('graficoCargos').getContext('2d');
    const graficoCargos = new Chart(ctxCargos, {
        type: 'pie',
        data: {
            labels: datosCargos.labels,
            datasets: [{
                data: datosCargos.valores,
                backgroundColor: [
                    '#0ea5e9', '#f97316', '#22c55e',
                    '#a855f7', '#e11d48', '#14b8a6',
                    '#facc15', '#6b7280', '#4ade80',
                    '#3b82f6', '#f97373', '#c4b5fd'
                ],
                borderColor: '#ffffff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 10
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = Number(context.raw) || 0;
                            const total = context.dataset.data.reduce((a, b) => Number(a) + Number(b), 0);
                            const porcentaje = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            const valorFormateado = value.toLocaleString('es-CO');
                            return `${label}: $${valorFormateado} (${porcentaje}%)`;
                        }
                    }
                }
            }
        }
    });

    const datosGrafico = {
        labels: ['Total mes', 'Pagadas', 'Aprobadas', 'Pendientes'],
        valores: [
            <?php echo (float)$total_mes; ?>,
            <?php echo (float)$total_pagado; ?>,
            <?php echo (float)$valor_aprobado; ?>,
            <?php echo (float)$diferencia_valor; ?>
        ]
    };

    const ctxValores = document.getElementById('graficoValores').getContext('2d');
    const gradBar = ctxValores.createLinearGradient(0, 0, 0, 220);
    gradBar.addColorStop(0, 'rgba(59,130,246,0.9)');
    gradBar.addColorStop(0.5, 'rgba(34,197,94,0.9)');
    gradBar.addColorStop(1, 'rgba(245,158,11,0.9)');

    const graficoValores = new Chart(ctxValores, {
        type: 'bar',
        data: {
            labels: datosGrafico.labels,
            datasets: [{
                label: 'Valor ($)',
                data: datosGrafico.valores,
                backgroundColor: gradBar,
                borderRadius: 10,
                maxBarThickness: 60
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '$' + context.raw.toLocaleString('es-CO');
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(209,213,219,0.7)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString('es-CO');
                        }
                    }
                }
            },
            animation: {
                duration: 900,
                easing: 'easeOutCubic'
            }
        }
    });

    const labelsLineal = [
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

    const dataRadicado = <?php echo json_encode($radicados); ?>;
    const dataPagado = <?php echo json_encode($pagados); ?>;

    const ctxLineal = document.getElementById('graficoLineal').getContext('2d');
    const graficoLineal = new Chart(ctxLineal, {
        type: 'line',
        data: {
            labels: labelsLineal,
            datasets: [{
                label: 'Valor Cuentas Radicadas',
                data: dataRadicado,
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245,158,11,0.18)',
                tension: 0.35,
                fill: true,
                pointRadius: 3.5,
                pointHoverRadius: 5
            }, {
                label: 'Total Cuentas Pagadas',
                data: dataPagado,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59,130,246,0.18)',
                tension: 0.35,
                fill: true,
                pointRadius: 3.5,
                pointHoverRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': $' + context.raw.toLocaleString('es-CO');
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 0,
                        minRotation: 0
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(209,213,219,0.7)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString('es-CO');
                        }
                    }
                }
            }
        }
    });

    // LINEAL GASTOS POR PROYECTO
    const proyectosJS = <?php echo $labelsProyectos; ?>;
    const gastosJS = <?php echo $valoresProyectos; ?>;

    const mesesProy = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];

    const coloresProy = [
        '#f97316', '#22c55e', '#3b82f6',
        '#a855f7', '#e11d48', '#14b8a6',
        '#eab308', '#6b7280', '#4ade80',
        '#38bdf8', '#f97373', '#c4b5fd'
    ];

    const datasetsProy = proyectosJS.map((nombre, idx) => ({
        label: nombre,
        data: gastosJS[idx],
        borderColor: coloresProy[idx % coloresProy.length],
        backgroundColor: coloresProy[idx % coloresProy.length] + '33',
        fill: false,
        tension: 0.25,
        pointRadius: 2.4,
        pointHoverRadius: 4
    }));

    const ctxProy = document.getElementById('graficoLineasProyectos').getContext('2d');
    const graficoProyectos = new Chart(ctxProy, {
        type: 'line',
        data: {
            labels: mesesProy,
            datasets: datasetsProy
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 6,
                        font: {
                            size: 9
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            const val = ctx.raw || 0;
                            return ctx.dataset.label + ': $' + val.toLocaleString('es-CO');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(209,213,219,0.7)'
                    },
                    ticks: {
                        callback: v => '$' + v.toLocaleString('es-CO'),
                        font: {
                            size: 9
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 9
                        }
                    }
                }
            }
        }
    });
</script>

<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="../../../js/scripts.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>