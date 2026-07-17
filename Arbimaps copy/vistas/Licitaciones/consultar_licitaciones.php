<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require __DIR__ . '/../../../conexion.php';

/* =========================
   TAB ACTIVO (por GET)
   activas | cerradas | ganadas | prorroga
========================= */
$tab = $_GET['tab'] ?? 'activas';

$where  = "(lc_estado IS NULL OR lc_estado = '')";
$titulo = "LICITACIONES ACTIVAS";
$sub    = "Listado de licitaciones";

/* ====== Colores por TAB ====== */
$colorTab = "#0D5EA8";  // activas
$grad1    = "#0a579b";
$grad2    = "#002f55";

if ($tab === 'cerradas') {
  $colorTab = "#6C757D";
  $grad1    = "#6c757d";
  $grad2    = "#343a40";
} elseif ($tab === 'ganadas') {
  $colorTab = "#198754";
  $grad1    = "#198754";
  $grad2    = "#0f5132";
} elseif ($tab === 'prorroga') {
  $colorTab = "#6F42C1";
  $grad1    = "#6f42c1";
  $grad2    = "#3b1f7a";
}

/* ====== WHERE/Títulos por TAB ====== */
if ($tab === 'cerradas') {
  $where  = "lc_estado = 'CERRADO'";
  $titulo = "LICITACIONES CERRADAS";
  $sub    = "Listado de licitaciones con estado CERRADO";
} elseif ($tab === 'ganadas') {
  $where  = "lc_estado = 'GANADO'";
  $titulo = "LICITACIONES GANADAS";
  $sub    = "Listado de licitaciones con estado GANADO";
} elseif ($tab === 'prorroga') {
  $where  = "lc_estado = 'PRORROGA'";
  $titulo = "LICITACIONES EN PRÓRROGA";
  $sub    = "Listado de licitaciones con estado PRÓRROGA";
}

/* =========================
   CONSULTA
========================= */
$sql = "SELECT
            lc_radicado            AS radicado,
            lc_numero_proceso      AS numero_proceso,
            lc_entidad             AS entidad,
            lc_nombre_licitacion   AS nombre_licitacion,
            lc_valor               AS valor_contrato,
            lc_fecha_apertura      AS fecha_apertura,
            lc_fecha_presentacion  AS fecha_presentacion
        FROM licitaciones
        WHERE $where
        ORDER BY lc_fecha_apertura DESC";

$resultado = $mysqli->query($sql);
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
  /* ====== TABS estilo CUENTAS ====== */
  .custom-tabs-lic{
    border-bottom:none !important;
    gap:0px;
    width:75%;
    margin-bottom:0;
  }
  .custom-tabs-lic .nav-link{
    border:none !important;
    padding:10px 28px;
    font-weight:600;
    border-radius:8px 25px 0 0;
    position:relative;
    top:6px;
    transition:all .25s ease-in-out;
    color:#fff;
  }
  .custom-tabs-lic .nav-link.active::after{
    content:"";
    position:absolute;
    bottom:-10px;
    left:0; right:0;
    height:12px;
    background:inherit;
    border-radius:0 0 16px 16px;
    z-index:5;
  }

  .nav-link.activas-tab{ background:#0d5ea8c7; color:#ffffffd8; }
  .nav-link.activas-tab.active{ background:#0D5EA8 !important; top:0; color:#fff; }

  .nav-link.cerradas-tab{ background:#6c757dc7; color:#ffffffd8; }
  .nav-link.cerradas-tab.active{ background:#6C757D !important; top:0; color:#fff; }

  .nav-link.ganadas-tab{ background:#198754c7; color:#ffffffd8; }
  .nav-link.ganadas-tab.active{ background:#198754 !important; top:0; color:#fff; }

  .nav-link.prorroga-tab{ background:#6f42c1c7; color:#ffffffd8; }
  .nav-link.prorroga-tab.active{ background:#6F42C1 !important; top:0; color:#fff; }

  /* ====== DataTables paginación ====== */
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

  /* ====== Botones acciones ====== */
  .btn-action{
    font-size:.78rem;
    padding:.35rem .6rem;
    border-radius:999px;
    border:0;
    font-weight:800;
    box-shadow:0 4px 10px rgba(0,0,0,.08);
    transition:transform .12s ease, filter .12s ease;
    white-space:nowrap;
  }
  .btn-action:hover{ transform:translateY(-1px); filter:brightness(.98); }
  .btn-cerradas{ background:#0b2a4a; color:#fff; }
  .btn-ganadas{ background:#198754; color:#fff; }
  .btn-prorroga{ background:#6f42c1; color:#fff; }
</style>

<div class="container-fluid">

  <div class="my-4 text-center">
    <h4 class="mb-0 fw-bold mb-2" style="color:#002F55;"><?php echo $titulo; ?></h4>
    <small class="text-muted"><?php echo $sub; ?></small>
  </div>

  <!-- TABS -->
  <ul class="nav nav-tabs custom-tabs-lic mb-0" id="tabLicitaciones" role="tablist">

    <li class="nav-item" role="presentation">
      <a class="nav-link activas-tab <?php echo ($tab==='activas'?'active':''); ?>"
         href="index.php?page=licitaciones/consultar_licitaciones&tab=activas">
        <i class="bi bi-lightning-charge-fill me-2"></i> Activas
      </a>
    </li>

    <li class="nav-item" role="presentation">
      <a class="nav-link cerradas-tab <?php echo ($tab==='cerradas'?'active':''); ?>"
         href="index.php?page=licitaciones/consultar_licitaciones&tab=cerradas">
        <i class="bi bi-lock-fill me-2"></i> Cerradas
      </a>
    </li>

    <li class="nav-item" role="presentation">
      <a class="nav-link ganadas-tab <?php echo ($tab==='ganadas'?'active':''); ?>"
         href="index.php?page=licitaciones/consultar_licitaciones&tab=ganadas">
        <i class="bi bi-trophy-fill me-2"></i> Ganadas
      </a>
    </li>

    <li class="nav-item" role="presentation">
      <a class="nav-link prorroga-tab <?php echo ($tab==='prorroga'?'active':''); ?>"
         href="index.php?page=licitaciones/consultar_licitaciones&tab=prorroga">
        <i class="bi bi-clock-history me-2"></i> Prórroga
      </a>
    </li>

  </ul>

  <div class="row">
    <div class="col-lg-12">

      <div class="card shadow mb-4" style="border-radius:0px 15px 15px 15px;">
        <!-- HEADER con color dinámico -->
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between"
             style="background: linear-gradient(355deg, <?php echo $grad1; ?>, <?php echo $grad2; ?>);
                    border-radius:0px 15px 0px 0px;">

          <div class="d-flex align-items-center">
            <div class="d-flex justify-content-center align-items-center me-3 rounded-5 p-2"
                 style="width:35px; height:35px; background-color:#fff;">
              <i class="bi bi-briefcase" style="color:#002F55;"></i>
            </div>

            <div>
              <div class="text-start text-white" style="font-size:1em; font-weight:800;">
                CONSULTA DE LICITACIONES
              </div>
              <div style="font-size:70%; color:#f3f8fdff;" class="text-start">
                Filtro por estado (tabs)
              </div>
            </div>
          </div>

        </div>

        <div class="card-body">
          <div class="table-responsive">

            <table class="table table-bordered table-hover" id="dataTableLicitaciones" width="100%" cellspacing="0">
              <!-- THEAD con color dinámico -->
              <thead style="background: <?php echo $colorTab; ?>; color:#fff;">
                <tr class="text-center">
                  <th>Radicado</th>
                  <th>N° Proceso</th>
                  <th>Entidad</th>
                  <th>Nombre de Licitación</th>
                  <th>Valor Contrato</th>
                  <th>Fecha Apertura</th>
                  <th>Fecha Presentación</th>
                  <th>Semáforo</th>

                  <?php if ($tab === 'activas' || $tab === 'prorroga'): ?>
                    <th>Acciones</th>
                  <?php endif; ?>
                </tr>
              </thead>

              <tbody>
                <?php if ($resultado && $resultado->num_rows > 0): ?>
                  <?php while ($row = $resultado->fetch_assoc()): ?>
                    <?php
                      // ======= SEMÁFORO basado en fecha_presentacion =======
                      $semaforo = "<span class='badge bg-secondary' style='font-size:0.8em;'>Sin fecha</span>";

                      if (!empty($row['fecha_presentacion'])) {
                        $fecha_fin = new DateTime($row['fecha_presentacion']);
                        $hoy = new DateTime();
                        $intervalo = $hoy->diff($fecha_fin);
                        $dias_restantes = (int)$intervalo->format('%r%a');

                        if ($dias_restantes < 0) {
                          $semaforo = "<span class='badge bg-danger' style='font-size:0.8em;'>Vencida</span>";
                        } elseif ($dias_restantes <= 7) {
                          $semaforo = "<span class='badge bg-warning text-dark' style='font-size:0.8em;'>Vence en $dias_restantes días</span>";
                        } else {
                          $semaforo = "<span class='badge bg-success' style='font-size:0.8em;'>Vigente ($dias_restantes días)</span>";
                        }
                      }
                    ?>

                    <tr class="text-center">
                      <td>
                        <a href="index.php?page=licitaciones/editar_licitaciones&radicado=<?php echo urlencode($row['radicado']); ?>"
                           class="fw-bold text-primary text-decoration-none">
                          <?php echo htmlspecialchars($row['radicado']); ?>
                        </a>
                      </td>

                      <td><?php echo htmlspecialchars($row['numero_proceso'] ?? ''); ?></td>
                      <td><?php echo htmlspecialchars($row['entidad'] ?? ''); ?></td>
                      <td><?php echo htmlspecialchars($row['nombre_licitacion'] ?? ''); ?></td>

                      <td class="text-end fw-bold">
                        <?php
                          $valor = $row['valor_contrato'] ?? '';
                          $valorNumerico = preg_replace('/[^\d]/', '', $valor);
                          echo ($valorNumerico !== '')
                            ? '$ ' . number_format((float)$valorNumerico, 0, ',', '.')
                            : '—';
                        ?>
                      </td>

                      <td><?php echo !empty($row['fecha_apertura']) ? date('d/m/Y', strtotime($row['fecha_apertura'])) : '—'; ?></td>
                      <td><?php echo !empty($row['fecha_presentacion']) ? date('d/m/Y', strtotime($row['fecha_presentacion'])) : '—'; ?></td>
                      <td><?php echo $semaforo; ?></td>

                      <?php if ($tab === 'activas' || $tab === 'prorroga'): ?>
                        <td class="text-center">
                          <div class="d-flex justify-content-center gap-2 flex-wrap">

                            <a class="btn btn-action btn-cerradas"
                               href="index.php?page=licitaciones/cerrar_licitacion&radicado=<?php echo urlencode($row['radicado']); ?>">
                              <i class="bi bi-lock-fill me-1"></i> Cerrar
                            </a>

                            <a class="btn btn-action btn-ganadas"
                               href="index.php?page=licitaciones/ganadas_licitacion&radicado=<?php echo urlencode($row['radicado']); ?>">
                              <i class="bi bi-trophy-fill me-1"></i> Ganada
                            </a>

                            <?php if ($tab === 'activas'): ?>
                              <a class="btn btn-action btn-prorroga"
                                 href="index.php?page=licitaciones/licitacion_prorroga&radicado=<?php echo urlencode($row['radicado']); ?>">
                                <i class="bi bi-clock-history me-1"></i> Prórroga
                              </a>
                            <?php endif; ?>

                          </div>
                        </td>
                      <?php endif; ?>

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

<!-- Si tu layout ya carga jQuery/DataTables, quita estas 3 líneas -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js" crossorigin="anonymous"></script>

<script>
$(function() {

  if ($.fn.DataTable.isDataTable('#dataTableLicitaciones')) {
    $('#dataTableLicitaciones').DataTable().destroy();
  }

  var dt = $('#dataTableLicitaciones').DataTable({
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
    order: [[5, "desc"]],
    deferRender: true
  });

  $('#dataTableLicitaciones_filter input')
    .off('keyup.DT input.DT')
    .on('keyup input', function () {
      dt.search(this.value).draw();
    });

});
</script>
