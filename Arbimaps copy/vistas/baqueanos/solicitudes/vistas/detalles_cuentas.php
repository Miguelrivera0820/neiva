<?php
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}
$idUsuario = $_SESSION['id_usuario'];

$sql = "SELECT rol_usuario, nombre_usuario FROM usuarios_cons WHERE id_usuario = $idUsuario";
$resultado = $mysqli->query($sql);

$rolesUsuario = array_filter([
    $_SESSION['rol_usuario'] ?? null,
    $_SESSION['rol_usuario_dos'] ?? null,
    $_SESSION['rol_usuario_tres'] ?? null,
]);
$rolesPermitidos = array("administrador", "director_catastro", "director_proyectos", "soporte");

if (count(array_intersect($rolesUsuario, $rolesPermitidos)) === 0) {
    header("Location: ../../acceso_denegado.php");
    exit();
}

$rolesPermitidos = array("administrador", "soporte",  "director_catastro", "director_proyectos");

$nombre = $_SESSION['nombre_usuario'];
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

    .swal2-popup.swal-pro {
        padding: 0 !important;
        border-radius: 22px !important;
        width: 600px !important;
        max-width: 95vw !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .swal2-container.pro-backdrop {
        background: rgba(0, 0, 0, .18) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .swal-pro-card {
        background: #fff;
        border-radius: 22px;
        box-shadow: 0 18px 60px rgba(0, 0, 0, .16);
        padding: 26px 28px 28px;
    }

    .swal-pro-header {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        font-weight: 700;
        font-size: 18px;
    }

    .swal-pro-divider {
        height: 1px;
        background: rgba(0, 0, 0, .08);
        margin: 14px 0 22px;
    }

    .swal-pro-body {
        text-align: center;
    }

    .swal-pro-text {
        color: #6c757d;
        font-size: 16px;
        margin-top: 10px;
    }

    .btn-swal-primary {
        background-color: #022F55;
        color: #ffffff;
        border: none;
        border-radius: 12px;
        padding: .5rem 1.5rem;
        transition: .2s ease;
    }

    .btn-swal-primary:hover {
        background-color: #011f3a;
    }
</style>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<div class="container-fluid">
    <div class="my-4 text-center">
        <h4 class=" mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 700 !important ">CUENTAS RADICADAS</h4>
        <small>Cuentas radicadas por el profesional social</small>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <div class="d-flex align-items-center ">
                        <div class="d-flex justify-content-center align-items-center me-3 rounded-5  p-2" style="width: 35px; height: 35px; background-color: #ffffffff;">
                            <i class="bi bi-person-video2" style="color: #002F55;"></i>
                        </div>
                        <div>
                            <div class=" text-start text-white" style="font-size: 1.2em;  font-weight: 700;">
                                Radicaciones de solicitudes
                            </div>
                            <div style="font-size: 70%; color: #f3f8fdff;" class="text-start">
                                Lista de solicitudes radicadas por el profesional social
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive ">
                        <table class="table  table-card text-center align-middle" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr class="text-center">
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">N° RAD</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">Nombres</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">N° identidad</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Cantidad de Días</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Valor del Día</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Total</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Tipo de Actividad</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 12px">Estados</th>
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
                                                sb_estado_cuenta
                                            FROM solicitud_baqueanos
                                            WHERE sb_estado_lider != 'DEVUELTO'
                                            AND sb_estado_cuenta = 'RADICADO'
                                            AND sb_estado_gerencia = 'APROBADO'
                                            AND sb_estado_operaciones IN ('PENDIENTE', 'APROBADO')
                                            AND sb_estado_profesional != 'DEVUELTO'
                                            ORDER BY id ASC;
                                            ";
                                if ($stmt = $mysqli->prepare($sql2)) {
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td><a href='index.php?page=baqueanos/solicitudes/vistas/revisar_baqueanos_two&id=" . urlencode($row['id']) . "' class='rad btn-link'>ARB_" . htmlspecialchars($row['id']) . "</a></td>";
                                            echo "<td class='text-center'>
                                                    <div class='d-flex justify-content-center'>
                                                        <div class='user-info'>
                                                            <div class='user-name'>" . htmlspecialchars($row['sb_baqueano_nombre']) . "</div>
                                                            <div class='user-lastname'>" . htmlspecialchars($row['sb_baqueano_apellido']) . "</div>
                                                        </div>
                                                    </div>
                                                </td>";
                                            echo "<td class='text-center'>" . htmlspecialchars($row['sb_numero_identidad']) . "</td>";
                                            echo "<td class='text-center'>" . htmlspecialchars($row['sb_dias_calculados']) . "</td>";
                                            echo "<td class='text-center'>$" . number_format($row['sb_cobro_diario'], 0, ',', '.') . "</td>";
                                            echo "<td>$" . number_format($row['sb_valor_cobrar'], 0, ',', '.') . "</td>";
                                            echo "<td>" . htmlspecialchars($row['sb_tipo_actividad']) . "</td>";
                                            echo "<td class='text-center'>" .
                                                ($row['sb_estado_cuenta'] === 'APROBADO'
                                                    ? '<i class="fa-solid fa-circle-check fa-xl" style="color: rgb(24, 167, 22);"></i>'
                                                    : '<i class="fa-solid fa-spinner fa-xl fa-spin" style="color:rgb(56, 133, 195);"></i>')
                                                . "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='9'>No se encontraron registros disponibles</td></tr>";
                                    }
                                    $stmt->close();
                                } else {
                                    echo "<tr><td colspan='9'>Error al preparar la consulta: " . htmlspecialchars($mysqli->error) . "</td></tr>";
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
<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="../../js/scripts.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/js/all.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://kit.fontawesome.com/9944c94262.js" crossorigin="anonymous"></script>
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
</script>


<?php if (isset($_SESSION['alerta'])): ?>
    <script>
        <?php if ($_SESSION['alerta'] === 'aprobado'): ?>

            Swal.fire({
                customClass: {
                    popup: 'swal-pro',
                    container: 'pro-backdrop',
                },
                showConfirmButton: false,
                background: 'transparent',
                html: `
                <div class="swal-pro-card">
                    <div class="swal-pro-header" style="color:#002F55;">
                        <i class="bi bi-check-circle-fill"></i>
                        Solicitud aprobada
                    </div>
                    <div class="swal-pro-divider"></div>
                    <div class="swal-pro-body">
                        <i class="bi bi-patch-check-fill text-success"
                            style="font-size:75px;opacity:.15;"></i>
                        <div class="swal-pro-text">
                            La solicitud fue aprobada correctamente.
                        </div>
                        <div class="mt-4">
                            <button onclick="Swal.close()" 
                                class="btn-swal-primary">
                                Aceptar
                            </button>
                        </div>
                    </div>
                </div>`
            });

        <?php elseif ($_SESSION['alerta'] === 'devuelto'): ?>

            Swal.fire({
                customClass: {
                    popup: 'swal-pro',
                    container: 'pro-backdrop',
                },
                showConfirmButton: false,
                background: 'transparent',
                html: `
                <div class="swal-pro-card">
                    <div class="swal-pro-header" style="color:#dc3545;">
                        <i class="bi bi-reply-fill"></i>
                        Solicitud devuelta
                    </div>
                    <div class="swal-pro-divider"></div>
                    <div class="swal-pro-body">
                        <i class="bi bi-exclamation-circle-fill text-danger"
                            style="font-size:75px;opacity:.15;"></i>
                        <div class="swal-pro-text">
                            La solicitud fue devuelta correctamente.
                        </div>
                        <div class="mt-4">
                            <button onclick="Swal.close()" 
                                class="btn-swal-primary">
                                Aceptar
                            </button>
                        </div>
                    </div>
                </div>`
            });

        <?php endif; ?>
    </script>
<?php unset($_SESSION['alerta']);
endif; ?>
