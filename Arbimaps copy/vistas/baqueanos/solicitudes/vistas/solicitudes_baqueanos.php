<?php
$idUsuario = $_SESSION['id_usuario'];
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

    .chip-aprobado {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .28rem .65rem;
        border-radius: 999px;
        border: 1px solid rgba(40, 167, 69, .35);
        color: #1f7a36;
        font-weight: 600;
        font-size: 12px;
        letter-spacing: .2px;
        background: transparent;
        line-height: 1;
    }

    .chip-aprobado i {
        font-size: 14px;
        color: #28a745;
    }

    .chip-aprobado:hover {
        border-color: rgba(40, 167, 69, .6);
        transform: translateY(-.5px);
        transition: .15s ease;
    }
</style>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<div class="container-fluid">
    <div class="my-4 text-center">
        <h4 class=" mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 700 !important ">SOLICITUDES BAQUEANOS</h4>
        <small> Lista de solicitudes registradas</small>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <div class="d-flex align-items-center ">
                        <div class="d-flex justify-content-center align-items-center me-3 rounded-5 p-2" style="width: 35px; height: 35px; background-color: #ffffffff;">
                            <i class="bi bi-person-video2" style="color: #002F55;"></i>
                        </div>
                        <div>
                            <div class=" text-start text-white" style="font-size: 1.2em; font-weight: 700;">
                                Detalles solicitudes baqueanos
                            </div>
                            <div style="font-size: 70%; color: #f3f8fdff;" class="text-start">
                                Seguimiento de las solicitudes
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive ">
                        <table class="table table-card text-center align-middle" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr class="text-center">
                                    <th style="text-align: center; vertical-align: middle; font-size: 11px">N° RAD</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">Nombre completo</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 11px">N° Días</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 11px">Total</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 11px">Actividad</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 11px">L. Reconocimiento</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 11px">P. Social</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 11px">J. Operaciones</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 11px">Gerencia</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 11px">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql2 = "SELECT
                                    id,
                                    usuario_id,
                                    sb_baqueano_nombre,
                                    sb_baqueano_apellido,
                                    sb_numero_identidad,
                                    sb_dias_calculados,
                                    sb_cobro_diario,
                                    sb_valor_cobrar,
                                    sb_tipo_actividad,
                                    sb_estado_lider,
                                    sb_estado_profesional,
                                    sb_estado_operaciones,
                                    sb_estado_gerencia
                                FROM solicitud_baqueanos
                                WHERE (
                                    sb_estado_lider IN ('PENDIENTE', 'APROBADO', 'DEVUELTO') OR
                                    sb_estado_profesional IN ('PENDIENTE', 'APROBADO', 'DEVUELTO') OR
                                    sb_estado_operaciones IN ('PENDIENTE', 'APROBADO', 'DEVUELTO') OR
                                    sb_estado_gerencia IN ('PENDIENTE', 'APROBADO', 'DEVUELTO')
                                )
                                AND (sb_estado_final_pago IS NULL OR sb_estado_final_pago != 'PAGADO')
                                ORDER BY id ASC";
                                if ($stmt = $mysqli->prepare($sql2)) {
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>
                                                    <a href='index.php?page=baqueanos/solicitudes/vistas/informacion_mi_solicitud&id=" . urlencode($row['id']) . "' class='rad btn-link'>
                                                        ARB_" . htmlspecialchars($row['id']) . "
                                                    </a>
                                                </td>";
                                            echo "<td class='text-center'>
                                                    <div class='d-flex justify-content-center'>
                                                        <div class='user-info'>
                                                            <div class='user-name'>" . htmlspecialchars($row['sb_baqueano_nombre']) . "</div>
                                                            <div class='user-lastname'>" . htmlspecialchars($row['sb_baqueano_apellido']) . "</div>
                                                        </div>
                                                    </div>
                                                </td>";
                                            echo "<td>" . htmlspecialchars($row['sb_dias_calculados']) . "</td>";
                                            echo "<td>$" . number_format($row['sb_valor_cobrar'], 0, ',', '.') . "</td>";
                                            echo "<td>" . htmlspecialchars($row['sb_tipo_actividad']) . "</td>";
                                            echo "<td class='text-center'>" .
                                                ($row['sb_estado_lider'] === 'APROBADO'
                                                    ? '<i class="fa-solid fa-circle-check fa-xl" style="color: rgb(24, 167, 22);"></i>'
                                                    : ($row['sb_estado_lider'] === 'DEVUELTO'
                                                        ? '<i class="fa-solid fa-circle-xmark fa-xl" style="color: rgb(200, 30, 30);"></i>'
                                                        : '<i class="fa-solid fa-spinner fa-xl fa-spin" style="color:rgb(56, 133, 195);"></i>')
                                                ) . "</td>";
                                            echo "<td class='text-center'>" .
                                                ($row['sb_estado_profesional'] === 'APROBADO'
                                                    ? '<i class="fa-solid fa-circle-check fa-xl" style="color: rgb(24, 167, 22);"></i>'
                                                    : ($row['sb_estado_profesional'] === 'DEVUELTO'
                                                        ? '<i class="fa-solid fa-circle-xmark fa-xl" style="color: rgb(200, 30, 30);"></i>'
                                                        : '<i class="fa-solid fa-spinner fa-xl fa-spin" style="color:rgb(56, 133, 195);"></i>')
                                                ) . "</td>";
                                            echo "<td class='text-center'>" .
                                                ($row['sb_estado_operaciones'] === 'APROBADO'
                                                    ? '<i class="fa-solid fa-circle-check fa-xl" style="color: rgb(24, 167, 22);"></i>'
                                                    : ($row['sb_estado_operaciones'] === 'DEVUELTO'
                                                        ? '<i class="fa-solid fa-circle-xmark fa-xl" style="color: rgb(200, 30, 30);"></i>'
                                                        : '<i class="fa-solid fa-spinner fa-xl fa-spin" style="color:rgb(56, 133, 195);"></i>')
                                                ) . "</td>";
                                            echo "<td class='text-center'>" .
                                                ($row['sb_estado_gerencia'] === 'APROBADO'
                                                    ? '<i class="fa-solid fa-circle-check fa-xl" style="color: rgb(24, 167, 22);"></i>'
                                                    : ($row['sb_estado_gerencia'] === 'DEVUELTO'
                                                        ? '<i class="fa-solid fa-circle-xmark fa-xl" style="color: rgb(200, 30, 30);"></i>'
                                                        : '<i class="fa-solid fa-spinner fa-xl fa-spin" style="color:rgb(56, 133, 195);"></i>')
                                                ) . "</td>";
                                            $todo_aprobado =
                                                $row['sb_estado_lider'] === 'APROBADO' &&
                                                $row['sb_estado_profesional'] === 'APROBADO' &&
                                                $row['sb_estado_operaciones'] === 'APROBADO' &&
                                                $row['sb_estado_gerencia'] === 'APROBADO';
                                            echo "<td class='text-center'>";
                                            if ($todo_aprobado) {
                                                echo "<span class='chip-aprobado' title='Esta solicitud ya fue aprobada en todas las etapas'>
                                                            Ya aprobado
                                                        </span>";
                                            } else {
                                                echo "
                                                    <a href='index.php?page=baqueanos/solicitudes/vistas/informacion_editar&id=" . urlencode($row['id']) . "' title='Editar'>
                                                        <i class='fa-solid fa-pencil' style='color: #64666a; font-size: 18px;'></i>
                                                    </a>";
                                            }
                                            if (
                                                $row['sb_estado_lider'] === 'DEVUELTO' ||
                                                $row['sb_estado_profesional'] === 'DEVUELTO' ||
                                                $row['sb_estado_operaciones'] === 'DEVUELTO' ||
                                                $row['sb_estado_gerencia'] === 'DEVUELTO'
                                            ) {
                                                echo "&nbsp;
                                                    <a href='index.php?page=baqueanos/solicitudes/vistas/informacion_devolucion&id=" . urlencode($row['id']) . "' title='Editar Devolución'>
                                                        <i class='fa-solid fa-user-pen' style='color:red; font-size: 18px;'></i>
                                                    </a>";
                                            }
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                                    }
                                    $stmt->close();
                                } else {
                                    echo "<tr><td colspan='10'>Error al preparar la consulta: " . htmlspecialchars($mysqli->error) . "</td></tr>";
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
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
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
            ]
        });
    });
    document.addEventListener("DOMContentLoaded", function() {
        const lista = document.getElementById("listaNotificaciones");
        const contador = document.getElementById("contadorNotificaciones");
        if (!lista || !contador) return;
    });
</script>

<?php if (!empty($_SESSION['swal_success'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Solicitud creada',
            text: 'La solicitud de baqueano fue creada correctamente.',
            confirmButtonColor: '#002F55'
        });
    </script>
<?php unset($_SESSION['swal_success']);
endif; ?>