<?php
$puede_ver_cancelados = usuarioTieneAlgunRol($PERMISOS['tramites.cancelados']);
if (!$puede_ver_cancelados) {
    http_response_code(403);
    echo '<div class="alert alert-danger m-4">No tiene permiso para consultar los trámites cancelados.</div>';
    return;
}

$sql = "SELECT
        tc.cod_tramite,
        tc.motivo,
        tc.estado_anterior,
        tc.cancelado_por_nombre,
        tc.cancelado_por_rol,
        tc.fecha_cancelacion,
        tr.fecha_rad,
        tr.mutacion_tramite,
        tr.primer_nombre_interesado,
        tr.primer_apellido_interesado,
        tr.fmi_predio,
        tr.municipio_rad
    FROM tramites_cancelados tc
    INNER JOIN tramite_radicacion tr ON tr.cod_tramite = tc.cod_tramite
    WHERE tc.estado = 'CANCELADO'
    ORDER BY tc.fecha_cancelacion DESC";
$resultado_cancelados = $mysqli->query($sql);
$tramites_cancelados = [];
if ($resultado_cancelados) {
    while ($fila = $resultado_cancelados->fetch_assoc()) {
        $tramites_cancelados[] = $fila;
    }
}
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .cancelados-page {
        background-color: #EDEDED;
        color: #0A2C1B;
        min-height: 100%;
    }

    .cancelados-card {
        background: transparent;
        border: none;
        border-radius: 18px !important;
        overflow: hidden;
    }

    .cancelados-card>.card-header,
    .cancelados-page .card-header {
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%) !important;
        border: 0 !important;
        color: #ffffff !important;
        padding: 0.95rem 1.15rem !important;
    }

    .cancelados-page .card-body {
        background-color: #ffffff;
        border: none !important;
        border-radius: 0 0 18px 18px;
        padding: 1.25rem;
    }

    .cancelados-page #tablaCancelados {
        border-collapse: separate !important;
        border-spacing: 0;
        color: #0A2C1B;
    }

    .cancelados-page #tablaCancelados thead th {
        background-color: #EDEDED !important;
        border: 0 !important;
        color: #0A2C1B !important;
        font-size: 0.72rem;
        font-weight: 800;
        padding: 0.85rem 0.45rem;
        text-align: center;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }

    .cancelados-page #tablaCancelados tbody td {
        border-left: 0 !important;
        border-right: 0 !important;
        border-top: none;
        color: #0A2C1B;
        font-size: 0.82rem;
        text-align: center;
        vertical-align: middle;
    }

    .cancelados-page #tablaCancelados tbody tr:hover td {
        background-color: #F6F8F7;
    }

    .cancelados-page .dataTables_wrapper .dataTables_filter {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 1rem;
    }

    .cancelados-page .dataTables_wrapper .dataTables_filter input,
    .cancelados-page .dataTables_wrapper .dataTables_length select {
        border-radius: 14px !important;
    }

    .cancelados-page .dataTables_wrapper .dataTables_info {
        color: #7F8E85 !important;
        font-size: 0.82rem;
    }

    .cancelados-page .dataTables_wrapper .dataTables_paginate .pagination {
        gap: 0.35rem;
        justify-content: flex-end;
    }

    .cancelados-page .dataTables_wrapper .dataTables_paginate .page-link {
        align-items: center;
        background-color: #F6F8F7 !important;
        border: 1px solid transparent !important;
        border-radius: 18px !important;
        color: #0A2C1B !important;
        display: flex;
        font-size: 0.8rem;
        height: 31px;
        justify-content: center;
        min-width: 31px;
    }

    .cancelados-page .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
        background-color: #0A2C1B !important;
        border-color: #0A2C1B !important;
        color: #ffffff !important;
    }

    .cancelados-page .dataTables_wrapper .dataTables_paginate .page-link:hover {
        background-color: #C0D2C8 !important;
        border-color: #C0D2C8 !important;
        color: #0A2C1B !important;
    }

    .cancelados-page .btn {
        border-radius: 14px;
        font-weight: 700;
    }

    .cancelados-page .btn-ver-cancelado,
    .cancelados-page .btn-volver-cancelados {
        background-color: #0A2C1B !important;
        border-color: #0A2C1B !important;
        color: #ffffff !important;
        transition: transform 0.22s ease, box-shadow 0.22s ease;
    }

    .cancelados-page .btn-ver-cancelado:hover,
    .cancelados-page .btn-volver-cancelados:hover {
        background-color: #000000 !important;
        box-shadow: 0 10px 20px rgba(10, 44, 27, 0.16);
        color: #ffffff !important;
        transform: translateY(-1px);
    }
</style>

<div class="container-fluid rounded-4 p-3 cancelados-page">
    <div class="d-sm-flex align-items-center justify-content-between my-4">
        <div class="my-4 text-center">
            <h2 class="mb-0 fw-bold" style="color: #0A2C1B; font-weight: 700 !important">TRÁMITES CANCELADOS</h2>
            <small>Consulta de trámites retirados de las bandejas activas.</small>
        </div>

        <a href="index.php?page=tramites/consultar_tramite" class="btn btn-volver-cancelados px-3">
            <i class="bi bi-arrow-left me-1"></i> Volver a trámites
        </a>
    </div>

    <div class="card cancelados-card mb-4">
        <div class="card-header py-3">
            <div class="text-xs text-white text-uppercase my-2" style="font-size:1rem; font-weight:500;">
                Listado de cancelaciones
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle" id="tablaCancelados" width="100%">
                    <thead>
                        <tr>
                            <th style="border-top-left-radius: 12px; border-bottom-left-radius:12px;">Fecha cancelación</th>
                            <th>Fecha radicación</th>
                            <th>Código</th>
                            <th>Solicitud</th>
                            <th>Solicitante</th>
                            <th>FMI</th>
                            <th>Municipio</th>
                            <th>Etapa anterior</th>
                            <th>Motivo</th>
                            <th>Cancelado por</th>
                            <th style="border-top-right-radius: 12px; border-bottom-right-radius:12px;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tramites_cancelados as $tramite): ?>
                            <tr>
                                <td class="px-0"><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($tramite['fecha_cancelacion']))); ?></td>
                                <td class="px-0"><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($tramite['fecha_rad']))); ?></td>
                                <td class="px-0"><?php echo htmlspecialchars($tramite['cod_tramite']); ?></td>
                                <td class="px-0"><?php echo htmlspecialchars($tramite['mutacion_tramite']); ?></td>
                                <td class="px-0"><?php echo htmlspecialchars(trim($tramite['primer_nombre_interesado'] . ' ' . $tramite['primer_apellido_interesado'])); ?></td>
                                <td class="px-0"><?php echo htmlspecialchars($tramite['fmi_predio']); ?></td>
                                <td class="px-0"><?php echo htmlspecialchars($tramite['municipio_rad']); ?></td>
                                <td class="px-0"><?php echo htmlspecialchars($tramite['estado_anterior']); ?></td>
                                <td class="px-2" style="min-width:150px; max-width:290px !important; white-space:normal"><?php echo nl2br(htmlspecialchars($tramite['motivo'])); ?></td>
                                <td class="px-0">
                                    <?php echo htmlspecialchars($tramite['cancelado_por_nombre']); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($tramite['cancelado_por_rol']); ?></small>
                                </td>
                                <td class="text-center px-0">
                                    <a class="btn btn-sm btn-ver-cancelado px-3"
                                        href="index.php?page=tramites/acciones/ver_tramite_rad&cod=<?php echo urlencode($tramite['cod_tramite']); ?>">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(function() {
        $('#tablaCancelados').DataTable({
            order: [
                [0, 'desc']
            ],
            pageLength: 25,
            language: window.neivaDataTablesLanguage ? window.neivaDataTablesLanguage() : {}
        });
    });
</script>
