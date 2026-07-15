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
$rolesPermitidos = array("administrador", "director_proyectos", "soporte", "social", "gerencia");

if (!in_array($rolUsuario, $rolesPermitidos) && !in_array($rolUsuarioDos, $rolesPermitidos)) {
    header("Location: ../../acceso_denegado.php");
    exit();
}



function money_fmt($n)
{
    return '$' . number_format((float)$n, 0, ',', '.');
}
function h($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    http_response_code(500);
    echo "No existe la conexión \$mysqli.";
    exit;
}

// Allow year filter to be empty string for "Todos"
$yearSelected = isset($_GET['year']) ? trim((string)$_GET['year']) : null;

$municipioSelected = isset($_GET['municipio']) ? trim((string)$_GET['municipio']) : '';
$tipoSelected      = isset($_GET['tipo']) ? trim((string)$_GET['tipo']) : '';

$years = [];
$sqlYears = "SELECT DISTINCT sb_year AS y FROM solicitud_baqueanos WHERE sb_year IS NOT NULL ORDER BY sb_year DESC";
if ($res = $mysqli->query($sqlYears)) {
    while ($r = $res->fetch_assoc()) $years[] = (int)$r['y'];
    $res->free();
}
// Determine default year selection: preserve empty string as 'Todos', otherwise default to latest year
if ($yearSelected === null) {
    $yearSelected = count($years) ? (string)$years[0] : (string)date('Y');
} else {
    // if user provided non-empty value, validate it's a year; empty string denotes "Todos"
    if ($yearSelected !== '') {
        if (is_numeric($yearSelected)) {
            $yInt = (int)$yearSelected;
            if ($yInt < 2000 || $yInt > 2100) {
                $yearSelected = count($years) ? (string)$years[0] : (string)date('Y');
            } else {
                $yearSelected = (string)$yInt;
            }
        } else {
            $yearSelected = count($years) ? (string)$years[0] : (string)date('Y');
        }
    }
}

$municipiosList = [];
$sqlMuns = "
    SELECT DISTINCT COALESCE(NULLIF(TRIM(sb_municipio),''), 'Sin municipio') AS m
    FROM solicitud_baqueanos
    " . ($yearSelected === '' ? "" : "WHERE sb_year = " . (int)$yearSelected) . "
    ORDER BY m ASC
";
if ($res = $mysqli->query($sqlMuns)) {
    while ($r = $res->fetch_assoc()) $municipiosList[] = (string)$r['m'];
    $res->free();
}

$tiposList = [];
$sqlTipos = "
    SELECT DISTINCT COALESCE(NULLIF(TRIM(sb_tipo_actividad),''), 'Sin módulo') AS t
    FROM solicitud_baqueanos
    " . ($yearSelected === '' ? "" : "WHERE sb_year = " . (int)$yearSelected) . "
    ORDER BY t ASC
";
if ($res = $mysqli->query($sqlTipos)) {
    while ($r = $res->fetch_assoc()) $tiposList[] = (string)$r['t'];
    $res->free();
}

function obtenerDatosTablero(mysqli $mysqli, $year, string $municipio, string $tipo): array
{
    $year = trim((string)$year);

    $municipio = trim($municipio);
    $tipo      = trim($tipo);

    $whereExtra = "";

    // build year filter clause: empty string means all years
    $whereYear = "";
    if ($year !== '') {
        $whereYear = " AND sb_year = " . (int)$year;
    }

    if ($municipio !== '') {
        if ($municipio === 'Sin municipio') {
            $whereExtra .= " AND (sb_municipio IS NULL OR TRIM(sb_municipio) = '') ";
        } else {
            $munEsc = $mysqli->real_escape_string($municipio);
            $whereExtra .= " AND TRIM(sb_municipio) = '{$munEsc}' ";
        }
    }

    if ($tipo !== '') {
        if ($tipo === 'Sin módulo') {
            $whereExtra .= " AND (sb_tipo_actividad IS NULL OR TRIM(sb_tipo_actividad) = '') ";
        } else {
            $tipoEsc = $mysqli->real_escape_string($tipo);
            $whereExtra .= " AND TRIM(sb_tipo_actividad) = '{$tipoEsc}' ";
        }
    }

    $sqlKPIs = "
        SELECT
            COUNT(*) AS total_solicitudes,
            SUM(
                CASE
                    WHEN
                        sb_estado_lider = 'PENDIENTE' OR
                        sb_estado_profesional = 'PENDIENTE' OR
                        sb_estado_operaciones = 'PENDIENTE' OR
                        sb_estado_gerencia = 'PENDIENTE' OR
                        sb_estado_cuenta = 'RADICADO' OR
                        sb_estado_pagos = 'PENDIENTE'
                    THEN 1 ELSE 0
                END
            ) AS total_pendientes,
            SUM(
                CASE
                    WHEN
                        sb_estado_lider = 'DEVUELTO' OR
                        sb_estado_profesional = 'DEVUELTO' OR
                        sb_estado_operaciones = 'DEVUELTO' OR
                        sb_estado_gerencia = 'DEVUELTO' OR
                        sb_estado_cuenta = 'DEVUELTO' OR
                        sb_estado_pagos = 'DEVUELTO'
                    THEN 1 ELSE 0
                END
            ) AS total_devueltas,
            SUM(
                CASE
                    WHEN
                        sb_estado_gerencia = 'APROBADO' OR
                        sb_estado_pagos = 'APROBADO' OR
                        sb_estado_cuenta = 'APROBADO'
                    THEN 1 ELSE 0
                END
            ) AS total_aprobadas,
            SUM(
                CASE
                    WHEN UPPER(TRIM(sb_estado_final_pago)) = 'PAGADO'
                    THEN 1 ELSE 0
                END
            ) AS total_pagadas,
            COALESCE(SUM(CASE WHEN UPPER(TRIM(sb_estado_final_pago)) = 'PAGADO' THEN sb_valor_cobrar ELSE 0 END), 0) AS valor_pagado,
            COALESCE(SUM(CASE WHEN UPPER(TRIM(sb_estado_final_pago)) <> 'PAGADO' OR sb_estado_final_pago IS NULL THEN sb_valor_cobrar ELSE 0 END), 0) AS valor_pendiente
        FROM solicitud_baqueanos
        WHERE 1=1 {$whereYear} $whereExtra
    ";

    $kpis = [
        "total_solicitudes" => 0,
        "total_pendientes"  => 0,
        "total_devueltas"   => 0,
        "total_aprobadas"   => 0,
        "total_pagadas"     => 0,
        "valor_pagado"      => 0,
        "valor_pendiente"   => 0,
    ];

    if ($res = $mysqli->query($sqlKPIs)) {
        $row = $res->fetch_assoc();
        if ($row) {
            foreach ($kpis as $k => $_) $kpis[$k] = (float)($row[$k] ?? 0);
        }
        $res->free();
    }

    $sqlDonut = "
        SELECT
            SUM(CASE WHEN UPPER(TRIM(sb_estado_final_pago))='PAGADO' THEN 1 ELSE 0 END) AS pagado,
            SUM(CASE WHEN
                sb_estado_lider='DEVUELTO'
                OR sb_estado_profesional='DEVUELTO'
                OR sb_estado_operaciones='DEVUELTO'
                OR sb_estado_gerencia='DEVUELTO'
                OR sb_estado_cuenta='DEVUELTO'
                OR sb_estado_pagos='DEVUELTO'
            THEN 1 ELSE 0 END) AS devuelto,
            SUM(CASE WHEN
                sb_estado_lider='PENDIENTE'
                OR sb_estado_profesional='PENDIENTE'
                OR sb_estado_operaciones='PENDIENTE'
                OR sb_estado_gerencia='PENDIENTE'
                OR sb_estado_cuenta='RADICADO'
                OR sb_estado_pagos='PENDIENTE'
            THEN 1 ELSE 0 END) AS pendiente,
            SUM(CASE WHEN
                (
                    sb_estado_gerencia='APROBADO'
                    OR sb_estado_pagos='APROBADO'
                    OR sb_estado_cuenta='APROBADO'
                )
                AND (UPPER(TRIM(sb_estado_final_pago))<>'PAGADO' OR sb_estado_final_pago IS NULL)
                AND NOT (
                    sb_estado_lider='DEVUELTO'
                    OR sb_estado_profesional='DEVUELTO'
                    OR sb_estado_operaciones='DEVUELTO'
                    OR sb_estado_gerencia='DEVUELTO'
                    OR sb_estado_cuenta='DEVUELTO'
                    OR sb_estado_pagos='DEVUELTO'
                )
            THEN 1 ELSE 0 END) AS aprobado
        FROM solicitud_baqueanos
        WHERE 1=1 {$whereYear} $whereExtra
    ";
    $donut = ["pendiente" => 0, "aprobado" => 0, "devuelto" => 0, "pagado" => 0];
    if ($res = $mysqli->query($sqlDonut)) {
        $row = $res->fetch_assoc();
        if ($row) {
            foreach ($donut as $k => $_) $donut[$k] = (int)($row[$k] ?? 0);
        }
        $res->free();
    }

    $sqlMunicipios = "
        SELECT
            COALESCE(NULLIF(TRIM(sb_municipio), ''), 'Sin municipio') AS municipio,
            COUNT(*) AS c
        FROM solicitud_baqueanos
        WHERE 1=1 {$whereYear} $whereExtra
        GROUP BY municipio
        ORDER BY c DESC
        LIMIT 20
    ";

    $munLabels = [];
    $munCounts = [];

    if ($res = $mysqli->query($sqlMunicipios)) {
        while ($r = $res->fetch_assoc()) {
            $munLabels[] = (string)$r['municipio'];
            $munCounts[] = (int)$r['c'];
        }
        $res->free();
    }

    $municipios = [
        "labels" => $munLabels,
        "counts" => $munCounts
    ];

    $sqlBar = "
        SELECT
            DATE_FORMAT(fecha_creacion, '%Y-%m') AS ym,
            SUM(CASE WHEN UPPER(TRIM(sb_estado_final_pago))='PAGADO' THEN 1 ELSE 0 END) AS pagadas_c,
            SUM(CASE WHEN UPPER(TRIM(sb_estado_final_pago))='PAGADO' THEN COALESCE(sb_valor_cobrar,0) ELSE 0 END) AS pagadas_s,

            SUM(CASE WHEN UPPER(TRIM(sb_estado_final_pago))<>'PAGADO' OR sb_estado_final_pago IS NULL THEN 1 ELSE 0 END) AS pendientes_c,
            SUM(CASE WHEN UPPER(TRIM(sb_estado_final_pago))<>'PAGADO' OR sb_estado_final_pago IS NULL THEN COALESCE(sb_valor_cobrar,0) ELSE 0 END) AS pendientes_s
        FROM solicitud_baqueanos
        WHERE 1=1 {$whereYear} $whereExtra
        GROUP BY ym
        ORDER BY ym ASC
    ";

    $map = [];
    if ($res = $mysqli->query($sqlBar)) {
        while ($r = $res->fetch_assoc()) {
            $map[$r['ym']] = [
                "pagadas_c"     => (int)$r['pagadas_c'],
                "pendientes_c"  => (int)$r['pendientes_c'],
                "pagadas_s"     => (float)$r['pagadas_s'],
                "pendientes_s"  => (float)$r['pendientes_s'],
            ];
        }
        $res->free();
    }

    $labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    $counts_pagadas     = array_fill(0, 12, 0);
    $counts_pendientes  = array_fill(0, 12, 0);

    $sums_pagadas       = array_fill(0, 12, 0);
    $sums_pendientes    = array_fill(0, 12, 0);

    foreach ($map as $ym => $vals) {
        $m = (int)substr((string)$ym, 5, 2);
        if ($m >= 1 && $m <= 12) {
            $i = $m - 1;
            $counts_pagadas[$i]    = (int)($vals['pagadas_c'] ?? 0);
            $counts_pendientes[$i] = (int)($vals['pendientes_c'] ?? 0);

            $sums_pagadas[$i]      = (float)($vals['pagadas_s'] ?? 0);
            $sums_pendientes[$i]   = (float)($vals['pendientes_s'] ?? 0);
        }
    }

    $currentMonth = (int)date('n');
    if ($year === '' || (int)$year !== (int)date('Y')) $currentMonth = 12;
    $idxCur  = max(0, min(11, $currentMonth - 1));
    $idxPrev = max(0, $idxCur - 1);

    $analyticsCurrentPagado = (float)$sums_pagadas[$idxCur];
    $analyticsPrevPagado    = (float)$sums_pagadas[$idxPrev];

    $analyticsCurrentPend   = (float)$sums_pendientes[$idxCur];
    $analyticsPrevPend      = (float)$sums_pendientes[$idxPrev];

    $deltaPagado = 0.0;
    if ($analyticsPrevPagado > 0) $deltaPagado = round((($analyticsCurrentPagado - $analyticsPrevPagado) / $analyticsPrevPagado) * 100, 2);
    elseif ($analyticsCurrentPagado > 0) $deltaPagado = 100.0;

    $deltaPend = 0.0;
    if ($analyticsPrevPend > 0) $deltaPend = round((($analyticsCurrentPend - $analyticsPrevPend) / $analyticsPrevPend) * 100, 2);
    elseif ($analyticsCurrentPend > 0) $deltaPend = 100.0;

    $analytics = [
        "labels" => $labels,
        "pagadas_values"    => $sums_pagadas,
        "pendientes_values" => $sums_pendientes,
        "current_pagado"    => $analyticsCurrentPagado,
        "prev_pagado"       => $analyticsPrevPagado,
        "delta_pagado"      => $deltaPagado,
        "current_pend"      => $analyticsCurrentPend,
        "prev_pend"         => $analyticsPrevPend,
        "delta_pend"        => $deltaPend,
    ];

    $sqlModules = "
        SELECT 
            COALESCE(NULLIF(TRIM(sb_tipo_actividad), ''), 'Sin módulo') AS modulo,
            COUNT(*) AS c
        FROM solicitud_baqueanos
        WHERE 1=1 {$whereYear} $whereExtra
        GROUP BY modulo
        ORDER BY c DESC
        LIMIT 12
    ";

    $modLabels = [];
    $modCounts = [];

    if ($res = $mysqli->query($sqlModules)) {
        while ($r = $res->fetch_assoc()) {
            $modLabels[] = (string)$r['modulo'];
            $modCounts[] = (int)$r['c'];
        }
        $res->free();
    }

    $modules = [
        "labels" => $modLabels,
        "counts" => $modCounts
    ];

    $sqlRecent = "
        SELECT
            id,
            fecha_creacion,
            sb_tipo_actividad,
            sb_baqueano_nombre,
            sb_baqueano_apellido,
            sb_valor_cobrar,
            sb_estado_lider,
            sb_estado_profesional,
            sb_estado_operaciones,
            sb_estado_gerencia,
            sb_estado_cuenta,
            sb_estado_pagos,
            sb_estado_final_pago
        FROM solicitud_baqueanos
        WHERE 1=1 {$whereYear} $whereExtra
        ORDER BY fecha_creacion DESC, id DESC
        LIMIT 6
    ";

    $recent = [];
    if ($res = $mysqli->query($sqlRecent)) {
        while ($r = $res->fetch_assoc()) {
            $estado = 'PENDIENTE';
            if (strtoupper(trim((string)$r['sb_estado_final_pago'])) === 'PAGADO') {
                $estado = 'PAGADO';
            } else {
                $tieneDevuelto = in_array('DEVUELTO', [
                    $r['sb_estado_lider'],
                    $r['sb_estado_profesional'],
                    $r['sb_estado_operaciones'],
                    $r['sb_estado_gerencia'],
                    $r['sb_estado_cuenta'],
                    $r['sb_estado_pagos']
                ], true);

                $tienePend = in_array('PENDIENTE', [
                    $r['sb_estado_lider'],
                    $r['sb_estado_profesional'],
                    $r['sb_estado_operaciones'],
                    $r['sb_estado_gerencia'],
                    $r['sb_estado_pagos']
                ], true) || (($r['sb_estado_cuenta'] ?? '') === 'RADICADO');

                $tieneAprob = in_array('APROBADO', [
                    $r['sb_estado_gerencia'],
                    $r['sb_estado_cuenta'],
                    $r['sb_estado_pagos']
                ], true);

                if ($tieneDevuelto) $estado = 'DEVUELTO';
                else if ($tienePend) $estado = 'PENDIENTE';
                else if ($tieneAprob) $estado = 'APROBADO';
            }

            $recent[] = [
                "id"    => (int)$r["id"],
                "fecha" => $r["fecha_creacion"] ? date('Y-m-d H:i', strtotime($r["fecha_creacion"])) : '-',
                "actividad" => $r["sb_tipo_actividad"] ?? '-',
                "nombre" => trim(($r["sb_baqueano_nombre"] ?? '') . ' ' . ($r["sb_baqueano_apellido"] ?? '')),
                "total" => (float)($r["sb_valor_cobrar"] ?? 0),
                "estado" => $estado
            ];
        }
        $res->free();
    }

    $activity = [];
    foreach ($recent as $r) {
        $activity[] = [
            "title" => "Solicitud ARB_{$r['id']} ({$r['estado']})",
            "time"  => $r['fecha'],
            "link"  => "index.php?page=baqueanos/solicitudes/vistas/informacion_mi_solicitud&id=" . urlencode((string)$r['id'])
        ];
    }

    $metaMes = max(1, (float)$kpis["valor_pagado"] + (float)$kpis["valor_pendiente"]);
    $pctPagado = min(100, round(($kpis["valor_pagado"] / $metaMes) * 100, 1));
    $pctPendiente = min(100, round(($kpis["valor_pendiente"] / $metaMes) * 100, 1));

    return [
        "year" => $year,
        "kpis" => [
            "total_solicitudes" => (int)$kpis["total_solicitudes"],
            "total_pendientes"  => (int)$kpis["total_pendientes"],
            "total_aprobadas"   => (int)$kpis["total_aprobadas"],
            "total_devueltas"   => (int)$kpis["total_devueltas"],
            "total_pagadas"     => (int)$kpis["total_pagadas"],
            "valor_pagado"      => (float)$kpis["valor_pagado"],
            "valor_pendiente"   => (float)$kpis["valor_pendiente"],
            "pct_pagado"        => (float)$pctPagado,
            "pct_pendiente"     => (float)$pctPendiente,
        ],
        "donut" => $donut,
        "bar" => [
            "labels" => $labels,
            "counts_pagadas"    => $counts_pagadas,
            "counts_pendientes" => $counts_pendientes,
            "sums_pagadas"      => $sums_pagadas,
            "sums_pendientes"   => $sums_pendientes,
        ],
        "analytics" => $analytics,
        "modules" => $modules,
        "municipios" => $municipios,
        "recent" => $recent,
        "activity" => array_slice($activity, 0, 7),
        "server_time" => date('Y-m-d H:i:s'),
    ];
}

if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json; charset=utf-8');

    $yearAjax = isset($_GET['year']) ? trim((string)$_GET['year']) : '';
    if ($yearAjax !== '') {
        if (!is_numeric($yearAjax)) {
            $yearAjax = (string)date('Y');
        } else {
            $yInt = (int)$yearAjax;
            if ($yInt < 2000 || $yInt > 2100) $yearAjax = (string)date('Y');
            else $yearAjax = (string)$yInt;
        }
    }

    $munAjax  = isset($_GET['municipio']) ? trim((string)$_GET['municipio']) : '';
    $tipoAjax = isset($_GET['tipo']) ? trim((string)$_GET['tipo']) : '';

    echo json_encode(obtenerDatosTablero($mysqli, $yearAjax, $munAjax, $tipoAjax), JSON_UNESCAPED_UNICODE);
    exit;
}

$data = obtenerDatosTablero($mysqli, $yearSelected, $municipioSelected, $tipoSelected);
$k = $data["kpis"];
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    :root {
        --brand: #0B5EA8;
        --brand2: #052B55;
        --soft: #F3F7FB;
        --card: #ffffff;
        --muted: #6b7280;
        --border: #e5e7eb;
    }

    body {
        background: #f6f8fc;
        font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Helvetica Neue", sans-serif;
    }

    .dash-wrap {
        padding: 18px;
    }

    .cardx {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 18px;
        box-shadow: 0 12px 28px rgba(7, 25, 60, .06);
    }

    .cardx+.cardx {
        margin-top: 14px;
    }

    .cardpad {
        padding: 16px;
    }

    .blue-card {
        color: #fff;
        background: linear-gradient(135deg, var(--brand), var(--brand2));
        border: none;
    }

    .mini-btn {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, .18);
        border: 1px solid rgba(255, 255, 255, .18);
    }

    .chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        background: var(--soft);
        border: 1px solid #e6eef8;
        color: #0c2f55;
        font-weight: 600;
    }

    .kpi {
        border-radius: 16px;
        padding: 14px 14px;
        border: 1px solid var(--border);
        background: #fff;
        box-shadow: 0 10px 22px rgba(7, 25, 60, .04);
        height: 100%;
    }

    .kpi .label {
        color: var(--muted);
        font-size: 12px;
        font-weight: 600;
    }

    .kpi .val {
        font-size: 26px;
        font-weight: 800;
        color: #0b2f55;
        line-height: 1.05;
    }

    .kpi .delta {
        font-size: 12px;
        font-weight: 700;
    }

    .delta.pos {
        color: #15a34a;
    }

    .delta.neg {
        color: #ef4444;
    }

    .kpi .icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #eef6ff;
        color: var(--brand2);
    }

    .progress.slim {
        height: 8px;
        border-radius: 999px;
    }

    .progress .progress-bar {
        border-radius: 999px;
    }

    .title {
        font-weight: 900;
        color: #0b2f55;
        letter-spacing: .2px;
    }

    .subtle {
        color: var(--muted);
        font-size: 12px;
    }

    .status-pill {
        font-size: 11px;
        font-weight: 800;
        padding: 6px 10px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid var(--border);
    }

    .st-pend {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .st-apr {
        background: #ecfdf5;
        color: #047857;
    }

    .st-dev {
        background: #fef2f2;
        color: #b91c1c;
    }

    .st-pag {
        background: #f0fdf4;
        color: #15803d;
    }

    .activity-item {
        display: flex;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px dashed #e6edf7;
    }

    .activity-item:last-child {
        border-bottom: 0;
    }

    .activity-dot {
        width: 10px;
        height: 10px;
        border-radius: 99px;
        margin-top: 6px;
        background: var(--brand);
        box-shadow: 0 0 0 6px rgba(11, 94, 168, .10);
    }

    .activity-title {
        font-size: 12px;
        font-weight: 800;
        color: #0b2f55;
        line-height: 1.2;
    }

    .activity-time {
        font-size: 11px;
        color: var(--muted);
    }

    .muted-link {
        text-decoration: none;
        color: #0b2f55;
    }

    .muted-link:hover {
        text-decoration: underline;
    }

    @media (max-width: 991.98px) {
        .dash-wrap {
            padding: 12px;
        }
    }

    .chart-carousel {
        border-radius: 18px;
        overflow: hidden;
    }

    .cc-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .cc-tabs {
        display: inline-flex;
        gap: 4px;
        padding: 4px;
        background: #f3f7fb;
        border: 1px solid #e6eef8;
        border-radius: 999px;
        flex-wrap: wrap;
    }

    .cc-tab {
        border: 0;
        background: transparent;
        padding: 6px 9px;
        font-size: 11px;
        font-weight: 800;
        color: #0b2f55;
        border-radius: 999px;
        cursor: pointer;
        transition: .15s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        line-height: 1;
        white-space: nowrap;
    }

    .cc-tab i {
        font-size: 12px;
    }

    .cc-tab.active {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 8px 18px rgba(7, 25, 60, .08);
    }

    .cc-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .cc-navbtn {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        border: 1px solid #e6eef8;
        background: #ffffff;
        color: #0b2f55;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: .15s ease;
        flex: 0 0 auto;
    }

    .cc-navbtn:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 18px rgba(7, 25, 60, .08);
    }

    .cc-body {
        margin-top: 12px;
        position: relative;
        min-height: 290px;
    }

    .cc-slide {
        position: absolute;
        inset: 0;
        opacity: 0;
        pointer-events: none;
        transform: translateX(8px);
        transition: opacity .18s ease, transform .18s ease;
    }

    .cc-slide.active {
        opacity: 1;
        pointer-events: auto;
        transform: translateX(0);
    }

    .cc-canvaswrap {
        height: 280px;
    }

    @media (max-width: 991.98px) {
        .cc-canvaswrap {
            height: 240px;
        }
    }

    .chart-wrap {
        height: 240px;
        padding: 6px 8px 18px;
    }

    .table-scroll::-webkit-scrollbar {
        width: 8px;
    }

    .table-scroll::-webkit-scrollbar-thumb {
        background: #dbe7f6;
        border-radius: 10px;
    }

    .table-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .activity-scroll::-webkit-scrollbar {
        width: 8px;
    }

    .activity-scroll::-webkit-scrollbar-thumb {
        background: #dbe7f6;
        border-radius: 10px;
    }

    .activity-scroll::-webkit-scrollbar-track {
        background: transparent;
    }


    .brand-float {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 15px 19px;
        border-radius: 16px;
        background: rgba(255, 255, 255, .75);
        border: 1px solid rgba(229, 231, 235, .9);
        box-shadow: 0 10px 22px rgba(7, 25, 60, .08);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        position: relative;
        overflow: hidden;
        transition: transform .18s ease, box-shadow .18s ease, padding .18s ease;
    }

    .brand-float:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 26px rgba(7, 25, 60, .12);
    }

    .brand-float::after {
        content: "";
        position: absolute;
        top: -40%;
        left: -60%;
        width: 60%;
        height: 180%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .55), transparent);
        transform: rotate(18deg);
        animation: brandShine 4.5s ease-in-out infinite;
        pointer-events: none;
        opacity: .8;
    }

    @keyframes brandShine {
        0% {
            left: -70%;
            opacity: .0;
        }

        20% {
            opacity: .7;
        }

        50% {
            left: 120%;
            opacity: .2;
        }

        100% {
            left: 120%;
            opacity: 0;
        }
    }

    .card-logo {
        height: 28px;
        width: auto;
        display: block;
        filter: drop-shadow(0 6px 10px rgba(0, 0, 0, .08));
        animation: brandFloat 2.6s ease-in-out infinite;
    }

    @keyframes brandFloat {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-2px);
        }
    }

    .brand-float.is-compact {
        padding: 6px 8px;
        border-radius: 14px;
    }

    .card-logo {
        height: 54px;
        width: auto;
    }

    .brand-float.is-compact .card-logo {
        height: 34px;
        animation: none;
    }

    @media (max-width: 575.98px) {
        .card-logo {
            height: 22px;
        }

        .brand-float {
            padding: 6px 8px;
        }
    }
</style>

<div class="card card-especial-tres rounded-4 shadow-lg w-100 mx-1 border-0 my-3 p-4 d-flex flex-column">
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <div class="title fs-4">Tablero de Control</div>
            <div class="subtle">Actualización en tiempo real. <span id="serverTime"><?= h($data["server_time"]) ?></span></div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <form id="filtersForm" class="d-flex align-items-center gap-2 flex-wrap" method="get" action="">
                <input type="hidden" name="page" value="<?= h($_GET['page'] ?? '') ?>">

                <span class="chip"><i class="bi bi-calendar-event"></i> Año</span>
                <select class="form-select form-select-sm" name="year" id="yearSelect" style="width:90px; border-radius:12px;">
                    <option value="" <?= ($yearSelected === '' ? 'selected' : '') ?>>Todos</option>
                    <?php foreach (($years ?: [(int)date('Y')]) as $y): ?>
                        <option value="<?= h($y) ?>" <?= ((string)$y === (string)$yearSelected ? 'selected' : '') ?>><?= h($y) ?></option>
                    <?php endforeach; ?>
                </select>

                <span class="chip"><i class="bi bi-geo-alt"></i> Municipio</span>
                <select class="form-select form-select-sm" name="municipio" id="municipioSelect" style="width:150px; border-radius:12px;">
                    <option value="" <?= ($municipioSelected === '' ? 'selected' : '') ?>>Todos</option>
                    <?php foreach ($municipiosList as $m): ?>
                        <option value="<?= h($m) ?>" <?= ($m === $municipioSelected ? 'selected' : '') ?>><?= h($m) ?></option>
                    <?php endforeach; ?>
                </select>

                <span class="chip"><i class="bi bi-diagram-3"></i> Tipo</span>
                <select class="form-select form-select-sm" name="tipo" id="tipoSelect" style="width:160px; border-radius:12px;">
                    <option value="" <?= ($tipoSelected === '' ? 'selected' : '') ?>>Todos</option>
                    <?php foreach ($tiposList as $t): ?>
                        <option value="<?= h($t) ?>" <?= ($t === $tipoSelected ? 'selected' : '') ?>><?= h($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>

            <a href="index.php" class="brand-float" aria-label="Ir al inicio">
                <img src="<?= neiva_app_url('Arbimaps/assets/img/logocompleto.png') ?>" alt="Logo" class="card-logo">
            </a>

        </div>
    </div>
    <div class="row g-3">
        <!-- izquierda -->
        <div class="col-12 col-lg-3">
            <div class="cardx blue-card cardpad">

                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="subtle text-white-50">Resumen</div>
                        <div class="fw-black" style="font-weight:900; font-size:18px;">Solicitudes Baqueanos</div>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="subtle text-white-50">Valor pendiente</div>
                    <div class="fw-black" id="k_valor_pendiente" style="font-weight:900; font-size:28px;">
                        <?= h(money_fmt($k["valor_pendiente"])) ?>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="d-flex justify-content-between">
                        <div class="subtle text-white-50">Progreso pagado</div>
                        <div class="subtle text-white-50"><span id="k_pct_pagado"><?= h($k["pct_pagado"]) ?></span>%</div>
                    </div>
                    <div class="progress slim bg-white bg-opacity-25">
                        <div class="progress-bar bg-white" id="bar_pagado" style="width: <?= h($k["pct_pagado"]) ?>%;"></div>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <a class="btn btn-light btn-sm fw-bold" href="index.php?page=baqueanos/solicitudes/vistas/solicitudes_baqueanos">
                        <i class="bi bi-list-check me-1"></i> Ver solicitudes
                    </a>
                    <a class="btn btn-outline-light btn-sm fw-bold" href="index.php?page=baqueanos/cuentas/vistas/detalles_cuentas_pagadas">
                        <i class="bi bi-cash-coin me-1"></i> Pagadas
                    </a>
                </div>
            </div>

            <div class="card card-especial-tres rounded-4 shadow-lg w-100 mx-1 border-0 my-3 p-4 d-flex flex-column"
                style="min-height: 380px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="title fs-6 mb-0">Información financiera</div>
                        <div class="subtle">Seguimiento</div>
                    </div>
                    <div class="mini-btn" style="background:#f2f6ff; border-color:#e6eef8; color:#0b2f55;">
                        <i class="bi bi-graph-up"></i>
                    </div>
                </div>
                <div class="flex-grow-1 d-flex flex-column justify-content-center">
                    <div class="mt-4">
                        <div class="d-flex justify-content-between">
                            <div class="fw-bold" style="font-size:12px; color:#0b2f55;">Valor pagado</div>
                            <div class="subtle" id="k_valor_pagado"><?= h(money_fmt($k["valor_pagado"])) ?></div>
                        </div>
                        <div class="progress slim mt-2">
                            <div class="progress-bar" id="bar_pagado_2" style="width: <?= h($k["pct_pagado"]) ?>%;"></div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="d-flex justify-content-between">
                            <div class="fw-bold" style="font-size:12px; color:#0b2f55;">Valor pendiente</div>
                            <div class="subtle"><?= h(money_fmt($k["valor_pendiente"])) ?></div>
                        </div>
                        <div class="progress slim mt-2">
                            <div class="progress-bar" id="bar_pendiente" style="width: <?= h($k["pct_pendiente"]) ?>%;"></div>
                        </div>
                    </div>
                </div>
                <div class="mt-auto pt-3 d-flex gap-2 flex-wrap">
                    <span class="chip"><i class="bi bi-hourglass-split"></i> Pendientes: <b id="k_pendientes"><?= h($k["total_pendientes"]) ?></b></span>
                    <span class="chip"><i class="bi bi-x-circle"></i> Devueltas: <b id="k_devueltas"><?= h($k["total_devueltas"]) ?></b></span>
                </div>
            </div>
        </div>

        <!-- centro -->
        <div class="col-12 col-lg-6">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <div class="kpi d-flex align-items-start justify-content-between">
                        <div>
                            <div class="label">Total solicitudes</div>
                            <div class="val" id="k_total"><?= h($k["total_solicitudes"]) ?></div>
                            <div class="delta pos"><i class="bi bi-arrow-up-right"></i> En vivo</div>
                        </div>
                        <div class="icon"><i class="bi bi-files"></i></div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="kpi d-flex align-items-start justify-content-between">
                        <div>
                            <div class="label">Pendientes</div>
                            <div class="val" id="k_pend"><?= h($k["total_pendientes"]) ?></div>
                            <div class="delta"><i class="bi bi-activity"></i> En revisión</div>
                        </div>
                        <div class="icon"><i class="bi bi-hourglass"></i></div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="kpi d-flex align-items-start justify-content-between">
                        <div>
                            <div class="label">Pagadas</div>
                            <div class="val" id="k_pag"><?= h($k["total_pagadas"]) ?></div>
                            <div class="delta pos"><i class="bi bi-check2-circle"></i> Cerradas</div>
                        </div>
                        <div class="icon"><i class="bi bi-cash-stack"></i></div>
                    </div>
                </div>
            </div>

            <!-- graficas -->
            <div class="cardx cardpad mt-3 chart-carousel" id="chartsCarousel">
                <div class="cc-head">
                    <div class="cc-titles" style="max-width:120px;">
                        <div class="title mb-0 lh-1" id="cc_title" style="font-size:14px;">
                            Estadísticas
                        </div>
                    </div>
                    <div class="cc-actions">
                        <div class="cc-tabs" role="tablist" aria-label="Cambiar gráfico">
                            <button type="button" class="cc-tab" data-slide="municipios" aria-selected="false">
                                <i class="bi bi-geo-alt"></i> Municipios
                            </button>
                            <button type="button" class="cc-tab active" data-slide="bar" aria-selected="true">
                                <i class="bi bi-bar-chart"></i> Barras
                            </button>
                            <button type="button" class="cc-tab" data-slide="analytics" aria-selected="false">
                                <i class="bi bi-activity"></i> Análisis
                            </button>
                            <button type="button" class="cc-tab" data-slide="modules" aria-selected="false">
                                <i class="bi bi-diagram-3"></i> Tipo
                            </button>
                        </div>
                        <button type="button" class="cc-navbtn" id="cc_prev" aria-label="Anterior">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button type="button" class="cc-navbtn" id="cc_next" aria-label="Siguiente">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <div class="cc-body" id="cc_body">
                    <div class="cc-slide" id="slide_municipios" data-key="municipios">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <span class="chip"><i class="bi bi-geo-alt"></i> Top municipios</span>
                            <span class="subtle">Cantidad de solicitudes por municipio</span>
                        </div>
                        <div class="mt-3 chart-wrap">
                            <canvas id="chartMunicipios"></canvas>
                        </div>
                    </div>

                    <div class="cc-slide active" id="slide_bar" data-key="bar">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="chip"><i class="bi bi-calendar3"></i> <?= h($yearSelected === '' ? 'Todos' : $yearSelected) ?></span>
                            <span class="subtle">Tooltip muestra valor total</span>
                        </div>
                        <div class="mt-3 chart-wrap">
                            <canvas id="chartBar"></canvas>
                        </div>
                    </div>

                    <div class="cc-slide" id="slide_analytics" data-key="analytics">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <span class="chip"><i class="bi bi-calendar3"></i> <?= h($yearSelected === '' ? 'Todos' : $yearSelected) ?></span>

                            <div class="text-end">
                                <div class="fw-black" style="font-weight:900; color:#0b2f55;" id="an_current">
                                    <?= h(money_fmt($data["analytics"]["current_pagado"] ?? 0)) ?>
                                </div>
                                <?php $dp = (float)($data["analytics"]["delta_pagado"] ?? 0); ?>
                                <div>
                                    <span id="an_delta" class="<?= ($dp >= 0 ? 'delta pos' : 'delta neg') ?>" style="font-weight:800;">
                                        <?= ($dp >= 0 ? '+' : '') . h($dp) ?>%
                                    </span>
                                    <span class="subtle ms-1">vs mes anterior</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 cc-canvaswrap" style="height:220px;">
                            <canvas id="chartAnalytics"></canvas>
                        </div>
                    </div>

                    <div class="cc-slide" id="slide_modules" data-key="modules">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <span class="chip"><i class="bi bi-diagram-3"></i> Top módulos</span>
                            <span class="subtle">Solicitudes por módulo</span>
                        </div>
                        <div class="mt-3 chart-wrap">
                            <canvas id="chartModules"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- recientes -->
            <div class="cardx cardpad mt-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="title fs-6 mb-0">Transacciones recientes</div>
                        <div class="subtle">Últimas solicitudes registradas</div>
                    </div>
                    <a class="chip muted-link" href="index.php?page=baqueanos/solicitudes/vistas/solicitudes_baqueanos">
                        Ver más <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <!-- Scroll vertical para NO crecer hacia abajo -->
                <div class="mt-3 table-scroll" style="max-height:260px; overflow:auto; border-radius:12px;">
                    <div class="table-responsive" style="margin:0;">
                        <table id="tblRecent" class="table table-sm align-middle mb-0">
                            <!-- sticky header -->
                            <thead style="position:sticky; top:0; z-index:2; background:#fff;">
                                <tr>
                                    <th>Radicado</th>
                                    <th>Nombre</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="recentBody">
                                <?php foreach ($data["recent"] as $r): ?>
                                    <tr>
                                        <td>
                                            <a class="muted-link fw-bold"
                                                href="<?= h("index.php?page=baqueanos/solicitudes/vistas/informacion_mi_solicitud&id=" . urlencode((string)$r['id'])) ?>">
                                                <?= h("ARB_" . $r["id"]) ?>
                                            </a>
                                        </td>
                                        <td><?= h($r["nombre"]) ?></td>
                                        <td class="subtle"><?= h($r["fecha"]) ?></td>
                                        <td class="fw-bold"><?= h(money_fmt($r["total"])) ?></td>
                                        <td>
                                            <?php
                                            $cls = "st-pend";
                                            if ($r["estado"] === "APROBADO") $cls = "st-apr";
                                            if ($r["estado"] === "DEVUELTO") $cls = "st-dev";
                                            if ($r["estado"] === "PAGADO")  $cls = "st-pag";
                                            ?>
                                            <span class="status-pill <?= $cls ?>">
                                                <i class="bi bi-circle-fill" style="font-size:8px;"></i> <?= h($r["estado"]) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- derecha -->
        <div class="col-12 col-lg-3">
            <div class="cardx cardpad">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="title fs-6 mb-0">Distribución de estados</div>
                        <div class="subtle">Resumen general</div>
                    </div>
                    <span class="chip"><i class="bi bi-pie-chart"></i> <?= h($yearSelected === '' ? 'Todos' : $yearSelected) ?></span>
                </div>
                <div class="mt-3" style="height:260px;">
                    <canvas id="chartDonut"></canvas>
                </div>
                <div class="mt-3 d-grid gap-2" style="font-size:12px;">
                    <div class="d-flex justify-content-between"><span>Pendiente</span><b id="d_pend"><?= h($data["donut"]["pendiente"]) ?></b></div>
                    <div class="d-flex justify-content-between"><span>Aprobado</span><b id="d_apr"><?= h($data["donut"]["aprobado"]) ?></b></div>
                    <div class="d-flex justify-content-between"><span>Devuelto</span><b id="d_dev"><?= h($data["donut"]["devuelto"]) ?></b></div>
                    <div class="d-flex justify-content-between"><span>Pagado</span><b id="d_pag"><?= h($data["donut"]["pagado"]) ?></b></div>
                </div>
            </div>

            <div class="cardx cardpad">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="title fs-6 mb-0">Actividad reciente</div>
                        <div class="subtle">Basado en últimas solicitudes</div>
                    </div>
                    <div class="mini-btn" style="background:#f2f6ff; border-color:#e6eef8; color:#0b2f55;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>

                <!-- Scroll vertical para NO crecer hacia abajo -->
                <div class="mt-2 activity-scroll" style="max-height:260px; overflow:auto; border-radius:12px;">
                    <div id="activityList">
                        <?php foreach ($data["activity"] as $a): ?>
                            <div class="activity-item">
                                <div class="activity-dot"></div>
                                <div class="flex-grow-1">
                                    <a class="activity-title muted-link" href="<?= h($a["link"]) ?>"><?= h($a["title"]) ?></a>
                                    <div class="activity-time"><?= h($a["time"]) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js" crossorigin="anonymous"></script>

<script>
    $(function() {
        $('#tblRecent').DataTable({
            paging: false,
            searching: false,
            info: false,
            ordering: false
        });
    });

    ['yearSelect', 'municipioSelect', 'tipoSelect'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', () => {
            document.getElementById('filtersForm').submit();
        });
    });

    const fmtMoney = (n) => new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        maximumFractionDigits: 0
    }).format(Number(n || 0));

    const safe = (s) => String(s ?? '').replace(/[&<>"']/g, (m) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    } [m]));

    function statusClass(est) {
        if (est === 'APROBADO') return 'st-apr';
        if (est === 'DEVUELTO') return 'st-dev';
        if (est === 'PAGADO') return 'st-pag';
        return 'st-pend';
    }

    // relleno tipo 
    function makeFluidPattern(ctx, color = 'rgba(255,125,80,0.45)') {
        const c = document.createElement('canvas');
        c.width = 44;
        c.height = 26;

        const p = c.getContext('2d');
        p.clearRect(0, 0, c.width, c.height);
        p.fillStyle = color;
        p.globalAlpha = 0.22;
        p.fillRect(0, 0, c.width, c.height);

        p.globalAlpha = 0.75;
        p.strokeStyle = color;
        p.lineWidth = 3;
        p.lineCap = 'round';

        p.beginPath();
        p.moveTo(-6, 8);
        p.quadraticCurveTo(8, 0, 22, 8);
        p.quadraticCurveTo(36, 16, 50, 8);
        p.stroke();

        p.globalAlpha = 0.55;
        p.lineWidth = 2.5;
        p.beginPath();
        p.moveTo(-6, 18);
        p.quadraticCurveTo(10, 10, 22, 18);
        p.quadraticCurveTo(34, 26, 50, 18);
        p.stroke();

        return ctx.createPattern(c, 'repeat');
    }

    // bar por mes
    const barCfg = {
        type: 'bar',
        data: {
            labels: <?= json_encode($data["bar"]["labels"], JSON_UNESCAPED_UNICODE) ?>,
            datasets: [{
                    label: 'Pendientes',
                    data: <?= json_encode($data["bar"]["counts_pendientes"], JSON_UNESCAPED_UNICODE) ?>,
                    borderWidth: 1,
                    borderRadius: 10,
                    borderColor: 'rgba(255, 125, 80, 1)',
                    backgroundColor: (ctx) => {
                        const chart = ctx.chart;
                        if (!chart.chartArea) return 'rgba(255, 125, 80, 0.25)';
                        return makeFluidPattern(chart.ctx, 'rgba(255, 125, 80, 0.55)');
                    },
                    hoverBackgroundColor: 'rgba(255, 125, 80, 0.75)',
                },
                {
                    label: 'Pagadas',
                    data: <?= json_encode($data["bar"]["counts_pagadas"], JSON_UNESCAPED_UNICODE) ?>,
                    borderWidth: 1,
                    borderRadius: 10,
                    borderColor: 'rgba(34, 197, 94, 1)',
                    backgroundColor: (ctx) => {
                        const chart = ctx.chart;
                        if (!chart.chartArea) return 'rgba(34, 197, 94, 0.25)';
                        return makeFluidPattern(chart.ctx, 'rgba(34, 197, 94, 0.55)');
                    },
                    hoverBackgroundColor: 'rgba(34, 197, 94, 0.75)',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    bottom: 14
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        afterBody: function(ctx) {
                            const idx = ctx?.[0]?.dataIndex ?? 0;
                            const sumsPend = <?= json_encode($data["bar"]["sums_pendientes"], JSON_UNESCAPED_UNICODE) ?>;
                            const sumsPag = <?= json_encode($data["bar"]["sums_pagadas"], JSON_UNESCAPED_UNICODE) ?>;

                            const vPend = sumsPend[idx] ?? 0;
                            const vPag = sumsPag[idx] ?? 0;

                            return [
                                'Valor pendientes: ' + fmtMoney(vPend),
                                'Valor pagadas: ' + fmtMoney(vPag),
                            ];
                        }
                    }
                },
                legend: {
                    display: true
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 0,
                        autoSkip: true,
                        padding: 6
                    }
                },
                y: {
                    beginAtZero: true
                }
            }
        }
    };
    const chartBar = new Chart(document.getElementById('chartBar'), barCfg);

    // donut
    const donutCfg = {
        type: 'doughnut',
        data: {
            labels: ['Pendiente', 'Aprobado', 'Devuelto', 'Pagado'],
            datasets: [{
                data: [
                    <?= (int)$data["donut"]["pendiente"] ?>,
                    <?= (int)$data["donut"]["aprobado"] ?>,
                    <?= (int)$data["donut"]["devuelto"] ?>,
                    <?= (int)$data["donut"]["pagado"] ?>
                ],
                backgroundColor: (ctx) => {
                    const chart = ctx.chart;
                    const {
                        chartArea
                    } = chart;
                    if (!chartArea) return '#8ec5ff';

                    const cx = (chartArea.left + chartArea.right) / 2;
                    const cy = (chartArea.top + chartArea.bottom) / 2;

                    if (typeof chart.ctx.createConicGradient === 'function') {
                        const g = chart.ctx.createConicGradient(-Math.PI / 2, cx, cy);
                        g.addColorStop(0.00, '#9fd2ff');
                        g.addColorStop(0.35, '#4ea3ff');
                        g.addColorStop(0.70, '#0b63c8');
                        g.addColorStop(1.00, '#053a7a');
                        return g;
                    }
                    const g2 = chart.ctx.createLinearGradient(chartArea.left, 0, chartArea.right, 0);
                    g2.addColorStop(0, '#9fd2ff');
                    g2.addColorStop(1, '#053a7a');
                    return g2;
                },
                borderWidth: 0,
                cutout: '70%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    };
    const chartDonut = new Chart(document.getElementById('chartDonut'), donutCfg);

    // patrones área
    function makeStripePattern(ctx, strokeColor = 'rgba(255, 125, 80, 0.35)') {
        const c = document.createElement('canvas');
        c.width = 16;
        c.height = 16;
        const p = c.getContext('2d');
        p.clearRect(0, 0, 16, 16);
        p.strokeStyle = strokeColor;
        p.lineWidth = 4;
        p.beginPath();
        p.moveTo(-4, 16);
        p.lineTo(16, -4);
        p.stroke();
        p.beginPath();
        p.moveTo(0, 20);
        p.lineTo(20, 0);
        p.stroke();
        return ctx.createPattern(c, 'repeat');
    }

    function makeDotsPattern(ctx, dotColor = 'rgba(11, 99, 200, 0.35)') {
        const c = document.createElement('canvas');
        c.width = 18;
        c.height = 18;
        const p = c.getContext('2d');
        p.clearRect(0, 0, 18, 18);
        p.fillStyle = dotColor;
        p.beginPath();
        p.arc(5, 5, 2.2, 0, Math.PI * 2);
        p.fill();
        p.beginPath();
        p.arc(14, 14, 2.2, 0, Math.PI * 2);
        p.fill();
        return ctx.createPattern(c, 'repeat');
    }

    const analyticsCfg = {
        type: 'line',
        data: {
            labels: <?= json_encode($data["analytics"]["labels"], JSON_UNESCAPED_UNICODE) ?>,
            datasets: [{
                    label: 'Pendientes ($)',
                    data: <?= json_encode($data["analytics"]["pendientes_values"], JSON_UNESCAPED_UNICODE) ?>,
                    tension: 0.35,
                    fill: true,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    borderColor: 'rgba(255, 125, 80, 1)',
                    pointBackgroundColor: 'rgba(255, 125, 80, 1)',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    backgroundColor: (ctx) => {
                        const chart = ctx.chart;
                        const {
                            chartArea
                        } = chart;
                        if (!chartArea) return 'rgba(255, 125, 80, 0.12)';
                        return makeStripePattern(chart.ctx, 'rgba(255, 125, 80, 0.35)');
                    }
                },
                {
                    label: 'Pagadas ($)',
                    data: <?= json_encode($data["analytics"]["pagadas_values"], JSON_UNESCAPED_UNICODE) ?>,
                    tension: 0.35,
                    fill: true,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    borderColor: 'rgba(34, 197, 94, 1)',
                    pointBackgroundColor: 'rgba(34, 197, 94, 1)',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    backgroundColor: (ctx) => {
                        const chart = ctx.chart;
                        const {
                            chartArea
                        } = chart;
                        if (!chartArea) return 'rgba(34, 197, 94, 0.12)';
                        return makeDotsPattern(chart.ctx, 'rgba(34, 197, 94, 0.35)');
                    },
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const v = context.parsed.y ?? 0;
                            return context.dataset.label + ': ' + fmtMoney(v);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(v) {
                            const n = Number(v || 0);
                            if (n >= 1000000) return '$' + Math.round(n / 1000000) + 'M';
                            if (n >= 1000) return '$' + Math.round(n / 1000) + 'K';
                            return '$' + n;
                        }
                    }
                }
            }
        }
    };
    const chartAnalytics = new Chart(document.getElementById('chartAnalytics'), analyticsCfg);

    // modulos
    const modulesCfg = {
        type: 'bar',
        data: {
            labels: <?= json_encode($data["modules"]["labels"] ?? [], JSON_UNESCAPED_UNICODE) ?>,
            datasets: [{
                label: 'Solicitudes',
                data: <?= json_encode($data["modules"]["counts"] ?? [], JSON_UNESCAPED_UNICODE) ?>,
                borderWidth: 1,
                borderRadius: 10,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            layout: {
                padding: {
                    bottom: 10,
                    left: 8,
                    right: 12,
                    top: 6
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ' ' + (ctx.parsed.x ?? 0) + ' solicitudes'
                    }
                }
            },
            scales: {
                y: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        autoSkip: false,
                        callback: function(v) {
                            const label = this.getLabelForValue(v);
                            return (label.length > 22) ? (label.slice(0, 22) + '…') : label;
                        }
                    }
                },
                x: {
                    beginAtZero: true
                }
            }
        }
    };
    const chartModules = new Chart(document.getElementById('chartModules'), modulesCfg);

    // municipios
    const municipiosCfg = {
        type: 'bar',
        data: {
            labels: <?= json_encode($data["municipios"]["labels"] ?? [], JSON_UNESCAPED_UNICODE) ?>,
            datasets: [{
                label: 'Solicitudes',
                data: <?= json_encode($data["municipios"]["counts"] ?? [], JSON_UNESCAPED_UNICODE) ?>,
                borderWidth: 1,
                borderRadius: 10,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ' ' + (ctx.parsed.x ?? 0) + ' solicitudes'
                    }
                }
            },
            scales: {
                y: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        autoSkip: false,
                        callback: function(v) {
                            const label = this.getLabelForValue(v);
                            return (label.length > 22) ? (label.slice(0, 22) + '…') : label;
                        }
                    }
                },
                x: {
                    beginAtZero: true
                }
            }
        }
    };
    const chartMunicipios = new Chart(document.getElementById('chartMunicipios'), municipiosCfg);

    const slides = [{
            key: 'bar',
            title: 'Estadísticas',
            subtitle: 'Cantidad de solicitudes por mes',
            idx: 0
        },
        {
            key: 'analytics',
            title: 'Análisis',
            subtitle: 'Valor total por mes',
            idx: 1
        },
        {
            key: 'modules',
            title: 'Módulos',
            subtitle: 'Solicitudes por módulo',
            idx: 2
        },
        {
            key: 'municipios',
            title: 'Municipios',
            subtitle: 'Cantidad de solicitudes por municipio',
            idx: 3
        },
    ];

    let activeKey = 'bar';

    function setActiveSlide(key) {
        activeKey = key;

        document.querySelectorAll('.cc-slide').forEach(el => el.classList.toggle('active', el.dataset.key === key));
        document.querySelectorAll('.cc-tab').forEach(btn => btn.classList.toggle('active', btn.dataset.slide === key));

        const meta = slides.find(s => s.key === key) || slides[0];
        document.getElementById('cc_title').textContent = meta.title;
        document.getElementById('cc_subtitle').textContent = meta.subtitle;

        requestAnimationFrame(() => {
            if (key === 'bar') {
                chartBar.resize();
                chartBar.update('none');
            } else if (key === 'analytics') {
                chartAnalytics.resize();
                chartAnalytics.update('none');
            } else if (key === 'modules') {
                chartModules.resize();
                chartModules.update('none');
            } else if (key === 'municipios') {
                chartMunicipios.resize();
                chartMunicipios.update('none');
            }
        });
    }

    function goNext() {
        const cur = slides.find(s => s.key === activeKey)?.idx ?? 0;
        const nxt = (cur + 1) % slides.length;
        setActiveSlide(slides[nxt].key);
    }

    function goPrev() {
        const cur = slides.find(s => s.key === activeKey)?.idx ?? 0;
        const prv = (cur - 1 + slides.length) % slides.length;
        setActiveSlide(slides[prv].key);
    }

    document.getElementById('cc_next').addEventListener('click', goNext);
    document.getElementById('cc_prev').addEventListener('click', goPrev);
    document.querySelectorAll('.cc-tab').forEach(btn => btn.addEventListener('click', () => setActiveSlide(btn.dataset.slide)));

    (function() {
        const area = document.getElementById('cc_body');
        let x0 = null;
        area.addEventListener('touchstart', (e) => {
            x0 = e.touches?.[0]?.clientX ?? null;
        }, {
            passive: true
        });
        area.addEventListener('touchend', (e) => {
            const x1 = e.changedTouches?.[0]?.clientX ?? null;
            if (x0 === null || x1 === null) return;
            const dx = x1 - x0;
            if (Math.abs(dx) < 40) return;
            if (dx < 0) goNext();
            else goPrev();
            x0 = null;
        }, {
            passive: true
        });
    })();

    setActiveSlide('bar');

    async function refreshDashboard() {
        try {
            const year = document.getElementById('yearSelect')?.value ?? '<?= h($yearSelected) ?>';
            const municipio = document.getElementById('municipioSelect')?.value || '';
            const tipo = document.getElementById('tipoSelect')?.value || '';

            const resp = await fetch(
                `tablero_control.php?ajax=1&year=${encodeURIComponent(year)}&municipio=${encodeURIComponent(municipio)}&tipo=${encodeURIComponent(tipo)}`, {
                    cache: 'no-store'
                }
            );
            const j = await resp.json();

            document.getElementById('serverTime').textContent = j.server_time;

            document.getElementById('k_total').textContent = j.kpis.total_solicitudes;
            document.getElementById('k_pend').textContent = j.kpis.total_pendientes;
            document.getElementById('k_pag').textContent = j.kpis.total_pagadas;

            document.getElementById('k_pendientes').textContent = j.kpis.total_pendientes;
            document.getElementById('k_devueltas').textContent = j.kpis.total_devueltas;

            document.getElementById('k_valor_pagado').textContent = fmtMoney(j.kpis.valor_pagado);
            document.getElementById('k_valor_pendiente').textContent = fmtMoney(j.kpis.valor_pendiente);

            document.getElementById('k_pct_pagado').textContent = j.kpis.pct_pagado;
            document.getElementById('bar_pagado').style.width = j.kpis.pct_pagado + '%';
            document.getElementById('bar_pagado_2').style.width = j.kpis.pct_pagado + '%';
            document.getElementById('bar_pendiente').style.width = j.kpis.pct_pendiente + '%';

            document.getElementById('d_pend').textContent = j.donut.pendiente;
            document.getElementById('d_apr').textContent = j.donut.aprobado;
            document.getElementById('d_dev').textContent = j.donut.devuelto;
            document.getElementById('d_pag').textContent = j.donut.pagado;
            chartDonut.data.datasets[0].data = [j.donut.pendiente, j.donut.aprobado, j.donut.devuelto, j.donut.pagado];
            chartDonut.update('none');

            chartModules.data.labels = (j.modules?.labels || []);
            chartModules.data.datasets[0].data = (j.modules?.counts || []);
            chartModules.update('none');

            chartBar.data.labels = j.bar.labels;
            chartBar.data.datasets[0].data = j.bar.counts_pendientes;
            chartBar.data.datasets[1].data = j.bar.counts_pagadas;
            chartBar.update('none');

            chartMunicipios.data.labels = (j.municipios?.labels || []);
            chartMunicipios.data.datasets[0].data = (j.municipios?.counts || []);
            chartMunicipios.update('none');

            document.getElementById('an_current').textContent = fmtMoney(j.analytics.current_pagado || 0);
            const dPct = Number(j.analytics.delta_pagado || 0);
            const elDelta = document.getElementById('an_delta');
            elDelta.textContent = (dPct >= 0 ? '+' : '') + dPct + '%';
            elDelta.className = (dPct >= 0) ? 'delta pos' : 'delta neg';

            chartAnalytics.data.labels = j.analytics.labels;
            chartAnalytics.data.datasets[0].data = j.analytics.pendientes_values;
            chartAnalytics.data.datasets[1].data = j.analytics.pagadas_values;
            chartAnalytics.update('none');

            if (activeKey === 'bar') {
                chartBar.resize();
                chartBar.update('none');
            } else if (activeKey === 'municipios') {
                chartMunicipios.resize();
                chartMunicipios.update('none');
            } else if (activeKey === 'modules') {
                chartModules.resize();
                chartModules.update('none');
            } else {
                chartAnalytics.resize();
                chartAnalytics.update('none');
            }

            const body = document.getElementById('recentBody');
            body.innerHTML = (j.recent || []).map(r => `
                <tr>
                    <td>
                        <a class="muted-link fw-bold" href="index.php?page=baqueanos/solicitudes/vistas/informacion_mi_solicitud&id=${encodeURIComponent(r.id)}">
                            ARB_${safe(r.id)}
                        </a>
                    </td>
                    <td>${safe(r.nombre)}</td>
                    <td class="subtle">${safe(r.fecha)}</td>
                    <td class="fw-bold">${fmtMoney(r.total)}</td>
                    <td>
                        <span class="status-pill ${statusClass(r.estado)}">
                            <i class="bi bi-circle-fill" style="font-size:8px;"></i> ${safe(r.estado)}
                        </span>
                    </td>
                </tr>
            `).join('');

            const act = document.getElementById('activityList');
            act.innerHTML = (j.activity || []).map(a => `
                <div class="activity-item">
                    <div class="activity-dot"></div>
                    <div class="flex-grow-1">
                        <a class="activity-title muted-link" href="${safe(a.link)}">${safe(a.title)}</a>
                        <div class="activity-time">${safe(a.time)}</div>
                    </div>
                </div>
            `).join('');

        } catch (e) {
            console.warn('No se pudo refrescar tablero:', e);
        }
    }

    setInterval(refreshDashboard, 10000);



    (function() {
        const logo = document.querySelector('.brand-float');
        if (!logo) return;

        let last = 0;
        window.addEventListener('scroll', () => {
            const y = window.scrollY || 0;
            if (y > 40 && last <= 40) logo.classList.add('is-compact');
            if (y <= 40 && last > 40) logo.classList.remove('is-compact');
            last = y;
        }, {
            passive: true
        });
    })();
</script>