<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../../../conexion.php';

/* =========================
   CONSULTA LICITACIONES CERRADAS
========================= */
$sql = "SELECT
            lc_radicado            AS radicado,
            lc_numero_proceso      AS numero_proceso,
            lc_entidad             AS entidad,
            lc_nombre_licitacion   AS nombre_licitacion,
            lc_fecha_apertura      AS fecha_apertura,
            lc_fecha_presentacion  AS fecha_presentacion
        FROM licitaciones
        WHERE lc_estado = 'CERRADO'
        ORDER BY lc_fecha_apertura DESC";

$resultado = $mysqli->query($sql);
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<style>
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
    .card-header {
        background: linear-gradient(355deg, #0a579bff, #012949ff);
    }
</style>

<div class="container-fluid">

    <div class="my-4 text-center">
        <h4 class="mb-0 fw-bold mb-2" style="color:#002F55;">LICITACIONES CERRADAS</h4>
        <small class="text-muted">Listado de licitaciones con estado CERRADO</small>
    </div>

    <div class="row">
        <div class="col-lg-12">

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="d-flex justify-content-center align-items-center me-3 rounded-5 p-2"
                            style="width:35px; height:35px; background-color:#fff;">
                            <i class="bi bi-lock-fill" style="color:#002F55;"></i>
                        </div>
                        <div>
                            <div class="text-start text-white" style="font-size:1em; font-weight:700;">
                                LICITACIONES CERRADAS
                            </div>
                            <div style="font-size:70%; color:#f3f8fdff;" class="text-start">
                                Listado de licitaciones cerradas
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">

                        <table class="table table-bordered table-hover" id="dataTableCerradas" width="100%" cellspacing="0">
                            <thead>
                                <tr class="text-center">
                                    <th>Radicado</th>
                                    <th>N° Proceso</th>
                                    <th>Entidad</th>
                                    <th>Nombre de Licitación</th>
                                    <th>Fecha Apertura</th>
                                    <th>Fecha Presentación</th>
                                    <th>Semáforo</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if ($resultado && $resultado->num_rows > 0): ?>
                                    <?php while ($row = $resultado->fetch_assoc()): ?>
                                        <?php
                                        // ======= SEMÁFORO basado en fecha de presentación =======
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
                                            <td class="fw-bold">
                                                <?php echo htmlspecialchars($row['radicado']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['numero_proceso'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['entidad'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['nombre_licitacion'] ?? ''); ?></td>
                                            <td>
                                                <?php echo !empty($row['fecha_apertura'])
                                                    ? date('d/m/Y', strtotime($row['fecha_apertura']))
                                                    : '—'; ?>
                                            </td>
                                            <td>
                                                <?php echo !empty($row['fecha_presentacion'])
                                                    ? date('d/m/Y', strtotime($row['fecha_presentacion']))
                                                    : '—'; ?>
                                            </td>
                                            <td><?php echo $semaforo; ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted" style="font-size:0.9em;">
                                            No se encontraron licitaciones cerradas
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- Si tu layout ya carga jQuery/DataTables, puedes quitar esto -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js" crossorigin="anonymous"></script>

<script>
$(document).ready(function() {
    if ($.fn.DataTable.isDataTable('#dataTableCerradas')) {
        $('#dataTableCerradas').DataTable().destroy();
    }

    $('#dataTableCerradas').DataTable({
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
        lengthMenu: [[10, 25, 50, -1],[10, 25, 50, "Todos"]],
        order: [[4, "desc"]] // Fecha Apertura
    });
});
</script>
