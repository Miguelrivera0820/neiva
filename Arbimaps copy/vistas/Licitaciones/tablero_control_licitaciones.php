<?php
/**
 * tablero_control_licitaciones.php
 * Tablero de Control y Seguimiento - LICITACIONES
 *
 * Requiere:
 * - conexion.php (debe crear $mysqli)
 * - Tabla: licitaciones (campos usados abajo)
 *
 * Campos usados (ajusta si cambian nombres):
 *  lc_radicado, lc_numero_proceso, lc_entidad, lc_nombre_licitacion,
 *  lc_valor, lc_fecha_apertura, lc_fecha_presentacion, lc_estado
 *
 * Estados esperados:
 *  '' o NULL = ACTIVA
 *  'GANADO', 'CERRADO', 'PRORROGA'
 */

if (session_status() === PHP_SESSION_NONE) session_start();
require __DIR__ . '/../../../conexion.php';

$mesesNombre = [
  '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
  '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
  '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre',
];

// =========================
// GET filtros
// =========================
$anioSeleccionado = isset($_GET['anio']) && $_GET['anio'] !== ''
  ? (int) $_GET['anio']
  : (int) date('Y');

$mesSeleccionado = isset($_GET['mes']) ? trim($_GET['mes']) : ''; // '01'..'12' o ''
$estadoSeleccionado = isset($_GET['estado']) ? strtoupper(trim($_GET['estado'])) : ''; // '', ACTIVA, GANADO, CERRADO, PRORROGA
$entidadFiltro = isset($_GET['entidad']) ? trim($_GET['entidad']) : ''; // texto libre

$nombre = $_SESSION['nombre'] ?? 'Usuario';

// =========================
// Detectar si MySQL soporta REGEXP_REPLACE (MySQL 8+)
// =========================
$usaRegexpReplace = false;
try {
  $test = $mysqli->query("SELECT REGEXP_REPLACE('a1.2', '[^0-9]', '') AS x");
  if ($test) $usaRegexpReplace = true;
} catch (Throwable $e) {
  $usaRegexpReplace = false;
}

// Expresión SQL para convertir lc_valor (texto) a número
// - Si tienes MySQL 8+: REGEXP_REPLACE
// - Si no: cadena de REPLACE (menos robusta pero funcional)
$valorExpr = $usaRegexpReplace
  ? "CAST(REGEXP_REPLACE(COALESCE(lc_valor,''), '[^0-9]', '') AS UNSIGNED)"
  : "CAST(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(lc_valor,''),'$',''),' ',''),'.',''),',',''),'COP','') AS UNSIGNED)";

// =========================
// Construir WHERE con parámetros (seguro)
// =========================
$where = "1=1";
$types = "";
$params = [];

$where .= " AND YEAR(lc_fecha_apertura) = ?";
$types .= "i";
$params[] = $anioSeleccionado;

if ($mesSeleccionado !== '' && isset($mesesNombre[$mesSeleccionado])) {
  $where .= " AND MONTH(lc_fecha_apertura) = ?";
  $types .= "i";
  $params[] = (int)$mesSeleccionado;
}

if ($entidadFiltro !== '') {
  $where .= " AND lc_entidad LIKE ?";
  $types .= "s";
  $params[] = "%{$entidadFiltro}%";
}

// Estado: ACTIVA = NULL o ''
$estadoPermitido = ['ACTIVA','GANADO','CERRADO','PRORROGA'];
if ($estadoSeleccionado !== '' && in_array($estadoSeleccionado, $estadoPermitido, true)) {
  if ($estadoSeleccionado === 'ACTIVA') {
    $where .= " AND (lc_estado IS NULL OR lc_estado='')";
  } else {
    $where .= " AND lc_estado = ?";
    $types .= "s";
    $params[] = $estadoSeleccionado;
  }
}

// Helper para ejecutar query preparada y retornar fila
function fetchOne($mysqli, $sql, $types, $params) {
  $stmt = $mysqli->prepare($sql);
  if (!$stmt) return null;
  if ($types !== "") $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $res = $stmt->get_result();
  $row = $res ? $res->fetch_assoc() : null;
  $stmt->close();
  return $row;
}

// =========================
// KPIs
// =========================
$rowTotal = fetchOne(
  $mysqli,
  "SELECT COALESCE(SUM($valorExpr),0) AS total_valor, COUNT(*) AS total_count
   FROM licitaciones WHERE $where",
  $types, $params
);
$totalValor = (float)($rowTotal['total_valor'] ?? 0);
$totalCount = (int)($rowTotal['total_count'] ?? 0);

// Ganadas
$rowGan = fetchOne(
  $mysqli,
  "SELECT COALESCE(SUM($valorExpr),0) AS valor_ganado, COUNT(*) AS count_ganado
   FROM licitaciones
   WHERE $where AND lc_estado='GANADO'",
  $types, $params
);
$valorGanado = (float)($rowGan['valor_ganado'] ?? 0);
$countGanado = (int)($rowGan['count_ganado'] ?? 0);

// Cerradas (si para ti “CERRADO” = perdida, aquí está “Perdidas”)
$rowCer = fetchOne(
  $mysqli,
  "SELECT COALESCE(SUM($valorExpr),0) AS valor_cerrado, COUNT(*) AS count_cerrado
   FROM licitaciones
   WHERE $where AND lc_estado='CERRADO'",
  $types, $params
);
$valorCerrado = (float)($rowCer['valor_cerrado'] ?? 0);
$countCerrado = (int)($rowCer['count_cerrado'] ?? 0);

// Activas
$rowAct = fetchOne(
  $mysqli,
  "SELECT COALESCE(SUM($valorExpr),0) AS valor_activa, COUNT(*) AS count_activa
   FROM licitaciones
   WHERE $where AND (lc_estado IS NULL OR lc_estado='')",
  $types, $params
);
$valorActiva = (float)($rowAct['valor_activa'] ?? 0);
$countActiva = (int)($rowAct['count_activa'] ?? 0);

// Prórroga
$rowPro = fetchOne(
  $mysqli,
  "SELECT COALESCE(SUM($valorExpr),0) AS valor_prorroga, COUNT(*) AS count_prorroga
   FROM licitaciones
   WHERE $where AND lc_estado='PRORROGA'",
  $types, $params
);
$valorProrroga = (float)($rowPro['valor_prorroga'] ?? 0);
$countProrroga = (int)($rowPro['count_prorroga'] ?? 0);

// “Pendiente” (a tu estilo): Total - Ganado - Cerrado - Prórroga (si hay estados raros, puede variar)
$valorPendiente = max(0, $totalValor - ($valorGanado + $valorCerrado + $valorProrroga));

// Tasa de éxito (solo con ganadas vs cerradas, si quieres)
$baseExito = ($countGanado + $countCerrado) > 0 ? ($countGanado + $countCerrado) : 0;
$tasaExito = $baseExito > 0 ? round(($countGanado / $baseExito) * 100, 1) : 0.0;

// =========================
// Pie: Valores por estado
// =========================
$labelsEstado = ['Activas','Prórroga','Ganadas','Cerradas'];
$valoresEstado = [$valorActiva, $valorProrroga, $valorGanado, $valorCerrado];

// =========================
// Línea: Todas vs Ganadas por mes (año)
// =========================
$mesesOrdenados = array_values($mesesNombre);
$radicados = array_fill(0, 12, 0); // “todas”
$ganadas   = array_fill(0, 12, 0);

$sqlLine = "
  SELECT
    MONTH(lc_fecha_apertura) AS mes_num,
    COALESCE(SUM($valorExpr),0) AS total_todas,
    COALESCE(SUM(CASE WHEN lc_estado='GANADO' THEN $valorExpr ELSE 0 END),0) AS total_ganadas
  FROM licitaciones
  WHERE YEAR(lc_fecha_apertura)=?
  GROUP BY MONTH(lc_fecha_apertura)
  ORDER BY MONTH(lc_fecha_apertura)
";
$stmt = $mysqli->prepare($sqlLine);
$stmt->bind_param("i", $anioSeleccionado);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
  $idx = (int)$r['mes_num'] - 1;
  if ($idx >= 0 && $idx < 12) {
    $radicados[$idx] = (float)$r['total_todas'];
    $ganadas[$idx]   = (float)$r['total_ganadas'];
  }
}
$stmt->close();

// =========================
// Tabla (últimas licitaciones según filtros)
// =========================
$sqlTabla = "
  SELECT
    lc_radicado AS radicado,
    lc_numero_proceso AS numero_proceso,
    lc_entidad AS entidad,
    lc_nombre_licitacion AS nombre_licitacion,
    lc_valor AS valor_contrato,
    lc_fecha_apertura AS fecha_apertura,
    lc_fecha_presentacion AS fecha_presentacion,
    lc_estado AS estado
  FROM licitaciones
  WHERE $where
  ORDER BY lc_fecha_apertura DESC
  LIMIT 500
";
$stmtT = $mysqli->prepare($sqlTabla);
if ($types !== "") $stmtT->bind_param($types, ...$params);
$stmtT->execute();
$resultTabla = $stmtT->get_result();
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" crossorigin="anonymous">

<style>
  body{
    background: radial-gradient(circle at top, #eef2ff 0, #f9fafb 50%, #e5e7eb 100%);
    color:#111827;
  }
  .glass-panel{
    background:#fff;
    border-radius:20px;
    padding:22px 24px;
    box-shadow:0 16px 35px rgba(15,23,42,.10);
    border:1px solid #e5e7eb;
  }
  .dashboard-title i{
    font-size:2rem;
    color:#0d5ea8;
    padding:10px;
    border-radius:16px;
    background: radial-gradient(circle at top, rgba(13,94,168,.18), transparent 70%);
  }
  .filters-card{
    margin-top:16px; margin-bottom:18px;
    background:#f9fafb;
    border-radius:16px;
    border:1px solid #e5e7eb;
    padding:14px 16px;
  }
  .filters-summary{
    display:flex; flex-wrap:wrap; justify-content:center; gap:.5rem;
    background:#fff; border-radius:999px;
    padding:6px 10px; border:1px dashed #e5e7eb;
    font-size:.8rem; color:#6b7280;
  }
  .filters-summary>span:first-child{ flex-basis:100%; text-align:center; font-weight:600; }
  .badge-filter{
    background:#f3f4f6; border-radius:999px; padding:3px 8px;
    color:#111827; border:1px solid #e5e7eb;
    display:inline-flex; align-items:center; gap:4px; font-size:.7rem;
  }
  .badge-filter i{ font-size:.9rem; color:#0d5ea8; margin-right:4px; }
  .kpi-card{
    border-radius:16px;
    background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
    border:1px solid #e5e7eb;
    position:relative; overflow:hidden;
    transition:transform .15s ease, box-shadow .15s ease, border-color .15s ease;
  }
  .kpi-card::after{
    content:""; position:absolute; right:-30px; bottom:-30px;
    width:120px; height:120px; border-radius:999px;
    background: radial-gradient(circle at center, rgba(13,94,168,.12), transparent 70%);
  }
  .kpi-card:hover{ transform:translateY(-3px); box-shadow:0 12px 25px rgba(15,23,42,.18); border-color:#d1d5db; }
  .card-panel{
    border-radius:18px;
    border:1px solid #e5e7eb;
    box-shadow:0 12px 28px rgba(148,163,184,.20);
  }
  .chart-container{ position:relative; height:290px; }
  .chart-container-lg{ position:relative; height:380px; }
  .dt-head-center thead th{ text-align:center; }
</style>

<div class="container-fluid py-3">
  <div class="glass-panel">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-start mb-4 flex-column flex-md-row gap-2">
      <div class="dashboard-title d-flex align-items-center gap-3">
        <i class="bi bi-speedometer2"></i>
        <div>
          <div class="fw-bold fs-3 text-dark">Tablero de Control - Licitaciones</div>
          <div class="text-muted small mt-1">
            Totales, estados y evolución mensual (todas vs ganadas).
          </div>
        </div>
      </div>
      <div>
        <span class="badge rounded-pill bg-success-subtle border border-success text-success d-inline-flex align-items-center gap-2 px-3 py-2">
          <i class="bi bi-person-circle"></i>
          <span class="small"><?php echo htmlspecialchars($nombre); ?></span>
        </span>
      </div>
    </div>

    <!-- FILTROS -->
    <form method="GET" action="" id="formFiltros" autocomplete="off">
      <!-- Mantener la página actual al enviar filtros -->
      <input type="hidden" name="page" value="licitaciones/tablero_control_licitaciones">
      <div class="filters-card">
        <div class="row g-3 align-items-end">
          <div class="col-12 col-md-3">
            <label class="form-label text-uppercase small text-secondary fw-semibold mb-1">Año</label>
            <select name="anio" class="form-select form-select-sm rounded-pill fw-medium">
              <?php
                $anioActual = (int)date('Y');
                for ($a=$anioActual+1; $a>=($anioActual-5); $a--){
                  $sel = ($anioSeleccionado===$a) ? 'selected' : '';
                  echo "<option value=\"$a\" $sel>$a</option>";
                }
              ?>
            </select>
          </div>

          <div class="col-12 col-md-3">
            <label class="form-label text-uppercase small text-secondary fw-semibold mb-1">Mes</label>
            <select name="mes" class="form-select form-select-sm rounded-pill fw-medium">
              <option value="" <?php echo $mesSeleccionado===''?'selected':''; ?>>Todos</option>
              <?php foreach($mesesNombre as $num=>$nom): ?>
                <option value="<?php echo $num; ?>" <?php echo $mesSeleccionado===$num?'selected':''; ?>>
                  <?php echo $nom; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-12 col-md-3">
            <label class="form-label text-uppercase small text-secondary fw-semibold mb-1">Estado</label>
            <select name="estado" class="form-select form-select-sm rounded-pill fw-medium">
              <option value="" <?php echo $estadoSeleccionado===''?'selected':''; ?>>Todos</option>
              <option value="ACTIVA" <?php echo $estadoSeleccionado==='ACTIVA'?'selected':''; ?>>Activas</option>
              <option value="PRORROGA" <?php echo $estadoSeleccionado==='PRORROGA'?'selected':''; ?>>Prórroga</option>
              <option value="GANADO" <?php echo $estadoSeleccionado==='GANADO'?'selected':''; ?>>Ganadas</option>
              <option value="CERRADO" <?php echo $estadoSeleccionado==='CERRADO'?'selected':''; ?>>Cerradas</option>
            </select>
          </div>

          <div class="col-12 col-md-3">
            <label class="form-label text-uppercase small text-secondary fw-semibold mb-1">Entidad (contiene)</label>
            <input
              type="text"
              name="entidad"
              class="form-control form-control-sm rounded-pill"
              placeholder="Ej: Alcaldía..."
              value="<?php echo htmlspecialchars($entidadFiltro); ?>"
              onkeydown="if(event.key==='Enter'){ event.preventDefault(); this.form.submit(); }"
            >
          </div>

          <div class="col-12 mt-2">
            <div class="filters-summary">
              <span>Filtros:</span>
              <span class="badge-filter"><i class="bi bi-calendar"></i><?php echo (int)$anioSeleccionado; ?></span>
              <span class="badge-filter"><i class="bi bi-calendar-event"></i><?php echo $mesSeleccionado ? $mesesNombre[$mesSeleccionado] : 'Todos los meses'; ?></span>
              <span class="badge-filter"><i class="bi bi-flag"></i><?php echo $estadoSeleccionado ?: 'Todos los estados'; ?></span>
              <span class="badge-filter"><i class="bi bi-building"></i><?php echo $entidadFiltro !== '' ? htmlspecialchars($entidadFiltro) : 'Todas las entidades'; ?></span>
            </div>
            <div class="mt-3 d-flex justify-content-center gap-2">
              <button type="button" id="btnAplicarFiltros" class="btn btn-primary btn-sm rounded-pill px-4">Aplicar filtros</button>
              <button type="button" id="btnLimpiarFiltros" class="btn btn-outline-secondary btn-sm rounded-pill px-4">Limpiar</button>
            </div>
          </div>
        </div>
      </div>
      <!-- Botón submit oculto como respaldo -->
      <button type="submit" class="d-none" aria-hidden="true"></button>
    </form>

    <!-- KPIs -->
    <div class="row mt-3">
      <div class="col-md-3 col-sm-6 mb-3">
        <div class="kpi-card p-3 h-100">
          <div class="text-uppercase small text-secondary fw-semibold mb-1">Total licitado</div>
          <div class="d-flex justify-content-between align-items-end position-relative">
            <div>
              <div class="fw-semibold mb-1">Suma de valores</div>
              <div class="fw-bold fs-4">$<?php echo number_format($totalValor, 0, ',', '.'); ?></div>
              <div class="text-muted small"># Licitaciones: <?php echo number_format($totalCount, 0, ',', '.'); ?></div>
            </div>
            <div class="display-6 text-primary opacity-50"><i class="bi bi-clipboard-data"></i></div>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6 mb-3">
        <div class="kpi-card p-3 h-100">
          <div class="text-uppercase small text-secondary fw-semibold mb-1">Ganadas</div>
          <div class="d-flex justify-content-between align-items-end position-relative">
            <div>
              <div class="fw-semibold mb-1">Valor ganado</div>
              <div class="fw-bold fs-4">$<?php echo number_format($valorGanado, 0, ',', '.'); ?></div>
              <div class="text-muted small"># Ganadas: <?php echo number_format($countGanado, 0, ',', '.'); ?> | Éxito: <?php echo $tasaExito; ?>%</div>
            </div>
            <div class="display-6 text-success opacity-50"><i class="bi bi-trophy-fill"></i></div>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6 mb-3">
        <div class="kpi-card p-3 h-100">
          <div class="text-uppercase small text-secondary fw-semibold mb-1">Cerradas</div>
          <div class="d-flex justify-content-between align-items-end position-relative">
            <div>
              <div class="fw-semibold mb-1">Valor cerrado</div>
              <div class="fw-bold fs-4">$<?php echo number_format($valorCerrado, 0, ',', '.'); ?></div>
              <div class="text-muted small"># Cerradas: <?php echo number_format($countCerrado, 0, ',', '.'); ?></div>
            </div>
            <div class="display-6 text-secondary opacity-50"><i class="bi bi-lock-fill"></i></div>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6 mb-3">
        <div class="kpi-card p-3 h-100">
          <div class="text-uppercase small text-secondary fw-semibold mb-1">Pendiente</div>
          <div class="d-flex justify-content-between align-items-end position-relative">
            <div>
              <div class="fw-semibold mb-1">Activas + Prórroga</div>
              <div class="fw-bold fs-4">$<?php echo number_format(($valorActiva + $valorProrroga), 0, ',', '.'); ?></div>
              <div class="text-muted small">Activas: <?php echo $countActiva; ?> | Prórroga: <?php echo $countProrroga; ?></div>
            </div>
            <div class="display-6 text-warning opacity-50"><i class="bi bi-hourglass-split"></i></div>
          </div>
        </div>
      </div>
    </div>

    <!-- GRAFICAS -->
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
                <div class="text-muted small">Comparación entre activas, prórroga, ganadas y cerradas.</div>
              </div>
              <span class="badge rounded-pill bg-light border text-secondary d-inline-flex align-items-center gap-1 small">
                <i class="bi bi-currency-dollar"></i> COP
              </span>
            </div>
            <div class="chart-container">
              <canvas id="graficoEstadoBar"></canvas>
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
                  <span class="fw-semibold">Distribución por Estado</span>
                </div>
                <div class="text-muted small">Participación porcentual del valor por estado.</div>
              </div>
              <span class="badge rounded-pill bg-light border text-secondary d-inline-flex align-items-center gap-1 small">
                <i class="bi bi-graph-up"></i> %
              </span>
            </div>
            <div class="chart-container">
              <canvas id="graficoEstadoPie"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- TODAS VS GANADAS -->
    <div class="mt-3">
      <div class="d-flex align-items-center gap-2 mb-1">
        <i class="bi bi-activity text-primary"></i>
        <span class="fw-semibold">Todas vs Ganadas (por mes)</span>
      </div>
      <div class="text-muted small mb-2">
        Evolución mensual del valor total licitado vs valor ganado durante el año <?php echo (int)$anioSeleccionado; ?>.
      </div>
      <div class="card card-panel border-0">
        <div class="card-body">
          <div class="chart-container-lg">
            <canvas id="graficoLineal"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- TABLA -->
    <div class="mt-3">
      <div class="d-flex align-items-center gap-2 mb-1">
        <i class="bi bi-table text-primary"></i>
        <span class="fw-semibold">Listado (máx. 500)</span>
      </div>
      <div class="text-muted small mb-2">Se muestra según los filtros aplicados.</div>

      <div class="card card-panel border-0">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered table-hover dt-head-center" id="tablaLicitaciones" width="100%" cellspacing="0">
              <thead style="background:#0d5ea8; color:#fff;">
                <tr>
                  <th>Radicado</th>
                  <th>N° Proceso</th>
                  <th>Entidad</th>
                  <th>Nombre</th>
                  <th>Estado</th>
                  <th>Valor</th>
                  <th>Fecha Apertura</th>
                  <th>Fecha Presentación</th>
                  <th>Semáforo</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($resultTabla && $resultTabla->num_rows > 0): ?>
                  <?php while($row=$resultTabla->fetch_assoc()): ?>
                    <?php
                      $estado = strtoupper(trim($row['estado'] ?? ''));
                      $estadoTxt = $estado !== '' ? $estado : 'ACTIVA';

                      // Semáforo por fecha_presentacion
                      $semaforo = "<span class='badge bg-secondary' style='font-size:0.8em;'>Sin fecha</span>";
                      if (!empty($row['fecha_presentacion'])) {
                        $fechaFin = new DateTime($row['fecha_presentacion']);
                        $hoy = new DateTime();
                        $intervalo = $hoy->diff($fechaFin);
                        $dias = (int)$intervalo->format('%r%a');

                        if ($dias < 0) {
                          $semaforo = "<span class='badge bg-danger' style='font-size:0.8em;'>Vencida</span>";
                        } elseif ($dias <= 7) {
                          $semaforo = "<span class='badge bg-warning text-dark' style='font-size:0.8em;'>Vence en $dias días</span>";
                        } else {
                          $semaforo = "<span class='badge bg-success' style='font-size:0.8em;'>Vigente ($dias días)</span>";
                        }
                      }

                      // Formateo valor como en tu tabla
                      $valor = $row['valor_contrato'] ?? '';
                      $valorNumerico = preg_replace('/[^\d]/', '', (string)$valor);
                      $valorFmt = ($valorNumerico !== '') ? ('$ '.number_format((float)$valorNumerico, 0, ',', '.')) : '—';
                    ?>
                    <tr>
                      <td class="fw-bold text-primary"><?php echo htmlspecialchars($row['radicado'] ?? ''); ?></td>
                      <td><?php echo htmlspecialchars($row['numero_proceso'] ?? ''); ?></td>
                      <td><?php echo htmlspecialchars($row['entidad'] ?? ''); ?></td>
                      <td><?php echo htmlspecialchars($row['nombre_licitacion'] ?? ''); ?></td>
                      <td>
                        <span class="badge rounded-pill <?php
                          echo ($estadoTxt==='GANADO') ? 'bg-success' :
                               (($estadoTxt==='CERRADO') ? 'bg-secondary' :
                               (($estadoTxt==='PRORROGA') ? 'bg-purple' : 'bg-primary'));
                        ?>" style="font-size:.8em;">
                          <?php echo htmlspecialchars($estadoTxt); ?>
                        </span>
                      </td>
                      <td class="text-end fw-bold"><?php echo $valorFmt; ?></td>
                      <td><?php echo !empty($row['fecha_apertura']) ? date('d/m/Y', strtotime($row['fecha_apertura'])) : '—'; ?></td>
                      <td><?php echo !empty($row['fecha_presentacion']) ? date('d/m/Y', strtotime($row['fecha_presentacion'])) : '—'; ?></td>
                      <td><?php echo $semaforo; ?></td>
                    </tr>
                  <?php endwhile; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<?php $stmtT->close(); ?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  Chart.defaults.color = '#111827';
  Chart.defaults.font.family = "'system-ui', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
  Chart.defaults.font.size = 11;

  // ====== BAR: Valores por Estado
  const labelsEstado = <?php echo json_encode($labelsEstado); ?>;
  const valoresEstado = <?php echo json_encode($valoresEstado); ?>;

  const ctxBar = document.getElementById('graficoEstadoBar').getContext('2d');
  new Chart(ctxBar, {
    type: 'bar',
    data: {
      labels: labelsEstado,
      datasets: [{
        label: 'Valor ($)',
        data: valoresEstado,
        borderRadius: 10,
        maxBarThickness: 60
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (ctx) => '$' + (Number(ctx.raw)||0).toLocaleString('es-CO')
          }
        }
      },
      scales: {
        x: { grid: { display:false } },
        y: {
          beginAtZero:true,
          ticks: { callback: (v)=>'$'+Number(v).toLocaleString('es-CO') }
        }
      }
    }
  });

  // ====== PIE: Distribución por Estado
  const ctxPie = document.getElementById('graficoEstadoPie').getContext('2d');
  new Chart(ctxPie, {
    type: 'pie',
    data: {
      labels: labelsEstado,
      datasets: [{
        data: valoresEstado,
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position:'right', labels:{ usePointStyle:true, boxWidth:10 } },
        tooltip: {
          callbacks: {
            label: function(context){
              const label = context.label || '';
              const value = Number(context.raw) || 0;
              const total = context.dataset.data.reduce((a,b)=>Number(a)+Number(b),0);
              const pct = total>0 ? ((value/total)*100).toFixed(1) : 0;
              return `${label}: $${value.toLocaleString('es-CO')} (${pct}%)`;
            }
          }
        }
      }
    }
  });

  // ====== LINE: Todas vs Ganadas por mes
  const labelsMes = <?php echo json_encode($mesesOrdenados); ?>;
  const dataTodas = <?php echo json_encode($radicados); ?>;
  const dataGanadas = <?php echo json_encode($ganadas); ?>;

  const ctxLine = document.getElementById('graficoLineal').getContext('2d');
  new Chart(ctxLine, {
    type: 'line',
    data: {
      labels: labelsMes,
      datasets: [{
        label: 'Total licitado',
        data: dataTodas,
        tension: 0.35,
        fill: true,
        pointRadius: 3.5,
        pointHoverRadius: 5
      },{
        label: 'Total ganado',
        data: dataGanadas,
        tension: 0.35,
        fill: true,
        pointRadius: 3.5,
        pointHoverRadius: 5
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode:'index', intersect:false },
      plugins: {
        legend: { position:'top', labels:{ usePointStyle:true } },
        tooltip: {
          callbacks: {
            label: (ctx) => `${ctx.dataset.label}: $${(Number(ctx.raw)||0).toLocaleString('es-CO')}`
          }
        }
      },
      scales: {
        x: { grid:{ display:false }, ticks:{ maxRotation:0, minRotation:0 } },
        y: {
          beginAtZero:true,
          ticks:{ callback:(v)=>'$'+Number(v).toLocaleString('es-CO') }
        }
      }
    }
  });

  // ====== DataTable
  $(function(){
    if ($.fn.DataTable.isDataTable('#tablaLicitaciones')) {
      $('#tablaLicitaciones').DataTable().destroy();
    }

    $('#tablaLicitaciones').DataTable({
      language: {
        lengthMenu: "Mostrar _MENU_ registros por página",
        emptyTable: "No se encontraron registros",
        zeroRecords: "No se encontraron resultados",
        info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
        infoEmpty: "Mostrando 0 a 0 de 0 registros",
        infoFiltered: "(filtrado de _MAX_ registros totales)",
        search: "Buscar:",
        paginate: { first: "Primero", last: "Último", next: "Siguiente", previous: "Anterior" }
      },
      pageLength: 10,
      lengthMenu: [[10, 25, 50, -1],[10, 25, 50, "Todos"]],
      order: [[6, "desc"]],
      deferRender: true
    });

    // ===== Filtros: botónes Aplicar y Limpiar
    $('#btnAplicarFiltros').on('click', function(){
      $('#formFiltros').submit();
    });
    $('#btnLimpiarFiltros').on('click', function(){
      var f = $('#formFiltros')[0];
      f.reset();
      $('#formFiltros input[name="page"]').val('licitaciones/tablero_control_licitaciones');
      $('#formFiltros').submit();
    });

  });
</script>
