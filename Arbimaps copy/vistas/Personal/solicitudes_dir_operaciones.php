<?php
$where = "";
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}
$idUsuario = $_SESSION['id_usuario'];
$rolesPermitidos = array("administrador", "director_presupuestos", "pagos", "seguridad_social", "director_proyectos", "Directivos", "soporte");


?>

<style>
    .form-label {
        font-weight: 600;
        color: rgb(29, 30, 30);
        font-size: 0.95rem;
    }

    .texto-grande {
        font-size: 15px;
        font-weight: bold;
    }

    body {
        background-color: #fff;
        font-family: 'Segoe UI', sans-serif;
    }

    h1 {
        color: #0a2d6c;
        font-weight: 700;
        text-align: center;
    }

    .subtitle {
        text-align: center;
        color: #444;
        margin-bottom: 30px;
    }

    .table-container {
        border: 1px solid #0062cc;
        border-radius: 10px;
        overflow-x: auto;
        width: 100%;
    }


    .table thead {
        background-color: #28a745;
        color: white;
    }


    .table thead .pendiente {
        background-color: #0062cc;
        color: white;
    }

    .table thead th {
        text-align: center;
        vertical-align: middle;
    }

    .table tbody td {
        vertical-align: middle;
        text-align: center;
    }

    .badge-pendiente {
        background-color: #002f55;
        color: #fff;
        padding: 5px 12px;
        border-radius: 10px;
        font-size: 0.9rem;
    }

    .badge-pendiente2 {
        background-color: #18722dff;
        color: #fff;
        padding: 5px 12px;
        border-radius: 10px;
        font-size: 0.9rem;
    }

    .action-btn {
        background-color: #0062cc;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        border: none;
    }

    .accion-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
    }

    .revisar-btn {
        background-color: #002F55;
        color: white;
        border: none;
        border-radius: 10px;
        width: 32px;
        height: 32px;
        padding-top: 2px;
        padding-left: 20px;
        padding-right: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .pendiente-btn {
        background-color: #002F55;
        color: white;
        border: none;
        border-radius: 10px;
        width: 32px;
        height: 32px;
        padding-top: 2px;
        padding-left: 20px;
        padding-right: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .rechazado-btn {
        background-color: #f00427ee;
        color: white;
        border: none;
        border-radius: 10px;
        width: 32px;
        height: 32px;
        padding-top: 2px;
        padding-left: 20px;
        padding-right: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .aprobado-btn {
        background-color: #28a745;
        color: white;
        border: none;
        border-radius: 10px;
        width: 32px;
        height: 32px;
        padding-top: 2px;
        padding-left: 20px;
        padding-right: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .revisar-btn:hover {
        background-color: #034a84ff;
        text-decoration: none;
        color: white;
    }

    .seccion-viable {
        background-color: #e6f4ea;
        border-left: 5px solid #28a745;
        padding: 10px 20px;
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .seccion-viable h2 {
        color: #218838;
        font-weight: bold;
        text-align: center;
        margin: 0;
    }

    .seccion-viable .subtitle {
        color: #155724;
        margin: 5px 0 0 0;
        text-align: center;
    }

    .seccion-pendiente {
        background-color: #e0f0ff;
        border-left: 5px solid #007bff;
        padding: 10px 20px;
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .seccion-pendiente h2 {
        color: #007bff;
        font-weight: bold;
        text-align: center;
        margin: 0;
    }

    .seccion-pendiente .subtitle {
        color: #007bff;
        margin: 5px 0 0 0;
        text-align: center;
    }

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

    .col-toggle-pill:has(input[type="checkbox"]:checked) {
        background-color: #0776d0ff;
        border: 1px solid #0776d0ff;
        color: white;
    }

    .dataTables_wrapper .dataTables_length {
        float: left;
        text-align: left;
    }

    .dataTables_wrapper .dataTables_filter {
        float: right;
        text-align: right;
    }

    .dataTables_wrapper .dataTables_info {
        float: left;
        text-align: left;
    }

    .dataTables_wrapper .dataTables_paginate {
        float: right;
        text-align: right;
    }

    @media (max-width: 767.98px) {

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            float: none;
            text-align: center;
        }
    }

    .table-responsive {
        overflow-x: visible !important;
    }

    table.dataTable th,
    table.dataTable td {
        white-space: normal !important;
    }

    .dataTables_wrapper .dataTables_length label {
        display: flex;
        align-items: center;
        gap: .5rem;
        margin-bottom: 11px;
    }

    .dataTables_wrapper .dataTables_filter label {
        display: flex;
        align-items: center;
        gap: .5rem;
        margin-bottom: 0;
    }

    .dataTables_wrapper .dataTables_filter input {
        margin-left: 0 !important;
    }

    .card-header2 {
        background: linear-gradient(305deg, #429047ff, #012949ff);
        padding: 0.2em 1em;
    }

    .card-header {
        background: linear-gradient(315deg, #012949ff, #0d5f82);
    }

    /* Tabla tipo cards */
    .table-card {
        border-collapse: separate !important;
        border-spacing: 0 8px;
        /* espacio entre filas */
    }

    /* Fila */
    .table-card tbody tr {
        background: transparent;
    }

    /* Celdas */
    .table-card tbody td {
        background-color: #ffffff;
        padding: 10px 10px;
        vertical-align: middle;
        border-top: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
    }

    /* Bordes redondeados SOLO en extremos */
    .table-card tbody tr td:first-child {
        border-left: 2px solid #002f55;
        border-radius: 8px 0 0 8px;
    }

    .table-card tbody tr td:last-child {
        border-right: 1px solid #e5e7eb;
        border-radius: 0 8px 8px 0;
    }

    /* Shadow por fila */
    .table-card tbody tr {
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
        transition: transform .15s ease, box-shadow .15s ease;
        border: none !important;
        border-radius: 8px;
    }

    /* Hover elegante */
    .table-card tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px #002f5570;
    }

    .table-card thead th {
        border: none !important;
        /* background-color: transparent !important; */
        color: #050505ff;
        font-weight: 600;
    }

    /* Tabla tipo cards */
    .table-card-2 {
        border-collapse: separate !important;
        border-spacing: 0 10px;
        /* espacio entre filas */
    }

    /* Fila */
    .table-card-2 tbody tr {
        background: transparent;
    }

    /* Celdas */
    .table-card-2 tbody td {
        background-color: #ffffff;
        padding: 14px 5px;
        vertical-align: middle;
        border-top: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
    }


    /* Bordes redondeados SOLO en extremos */
    .table-card-2 tbody tr td:first-child {
        border-left: 3px solid #429047ff;
        border-radius: 8px 0 0 8px;
    }

    .table-card-2 tbody tr td:last-child {
        border-right: 1px solid #e5e7eb;
        border-radius: 0 8px 8px 0;
    }

    /* Shadow por fila */
    .table-card-2 tbody tr {
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
        transition: transform .15s ease, box-shadow .15s ease;
        border: none !important;
        border-radius: 8px;
    }

    /* Hover elegante */
    .table-card-2 tbody tr:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 12px #002f5570;
    }

    .table-card-2 thead th {
        border: none !important;
        /* background-color: transparent !important; */
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

    /* Quita el border fijo (o lo sobreescribimos con mayor prioridad) */
    .table-card-2 tbody tr.estado-pendiente td:first-child {
        border-left: 3px solid #002f55 !important;
        /* azul */
    }

    .table-card-2 tbody tr.estado-aprobado td:first-child {
        border-left: 3px solid #28a745 !important;
        /* verde */
    }

    .table-card-2 tbody tr.estado-rechazado td:first-child {
        border-left: 4px solid #dc3545 !important;

        /* rojo */
    }

    .table-card-2 tbody tr.estado-desconocido td:first-child {
        border-left: 3px solid #6c757d !important;
        /* gris */
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

<!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"> -->
<div class="container-fluid">
    <div class="my-4 text-center">
        <h4 class=" mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 700 !important "> MÓDULO DE SOLICITUDES DE OTRO SI</h4>
        <small>A continuación se muestra los registros correspondientes a las solicitudes del otro si.</small>
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
                                Jefe de operaciones
                            </div>
                            <div style="font-size: 70%; color: #f3f8fdff;" class="text-start">
                                Panel general de solicitudes de otro si
                            </div>
                        </div>
                    </div>

                    <div class="d-none d-md-flex align-items-center gap-2 col-toggle-bar" style="color: #002f55; font-size: 0.9rem;">
                        <div class="col-toggle-chip me-2">
                            <i class="fas fa-sliders-h me-1"></i>
                            <span>Agregar columnas</span>
                        </div>

                        <div class="col-toggle-pill me-1">
                            <input type="checkbox" class="toggle-col" data-col="6" id="col-6">
                            <span>Honorarios</span>
                        </div>

                        <div class="col-toggle-pill me-1">
                            <input type="checkbox" class="toggle-col" data-col="7" id="col-7">
                            <span>Valor Total</span>
                        </div>

                        <div class="col-toggle-pill">
                            <input type="checkbox" class="toggle-col" data-col="11" id="col-11">
                            <span>Duración</span>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table  table-card " id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">N° Radicado</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">Nombre completo</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">N° documento</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">F. Inicio Contrato</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">RoL</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">Proyecto</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">Honorarios</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">Valor Total</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">Tipo Solicitud</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">F. Inicio</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">F. Final</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">Duración</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">Estados</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 13px">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT 
                                c.con_nombres, 
                                c.con_apellidos, 
                                c.con_num_identidad,
                                c.con_fecha_inicio, 
                                so.sol_nuevo_rol,
                                so.sol_nuevo_proyecto,
                                so.sol_nuevo_salario,
                                so.sol_tipo_otrosi,
                                so.sol_fecha_inicio,
                                so.sol_nueva_fecha_final,
                                so.sol_duracion,
                                so.id,
                                so.con_id,
                                so.sol_estado,
                                so.sol_estado_gerencia,
                                so.sol_valor_otrosi
                            FROM solicitudes_otrosi AS so
                            JOIN contratacion AS c ON so.con_id = c.con_id
                            ORDER BY so.con_id ASC
                        ";
                                if ($result = $mysqli->query($sql)) :
                                    if ($result->num_rows > 0) :
                                        while ($row = $result->fetch_assoc()) :

                                            $salario = $row['sol_nuevo_salario'];
                                            $valorOt = $row['sol_valor_otrosi'];

                                            $salario = ($salario === null || $salario === '' || !is_numeric($salario)) ? 0 : (float)$salario;
                                            $valorOt = ($valorOt === null || $valorOt === '' || !is_numeric($valorOt)) ? 0 : (float)$valorOt;
                                            $estado          = strtoupper($row['sol_estado']);
                                            $estado_gerencia = strtoupper($row['sol_estado_gerencia']);

                                            // *** ESTILOS DE LOS BADGE: SE MANTIENEN TAL CUAL ***
                                            if ($estado == 'PENDIENTE') {
                                                $badge_estado = '<span class="badge badge-warning px-2 py-2" style="border-radius: 20px; border:1px solid rgb(165, 165, 163); background-color: rgba(245, 210, 9, 0.82);" data-bs-toggle="tooltip" title="Pendiente por parte de presupuesto "> <i class="bi bi-hourglass " style="font-size:1.8em" style="color: rgb(165, 165, 163);"></i></span>';
                                            } elseif ($estado == 'RECHAZADO' || $estado == 'NO ACEPTADO') {
                                                $badge_estado = '<span class="badge px-2 py-2" style="border-radius: 20px; border:1px solid #d59c0b5b; background-color: #E74C3C;" data-bs-toggle="tooltip" title="Rechazado por parte de presupuesto "> <i class="bi bi-hand-thumbs-down " style="font-size:1.8em" ></i></span>';
                                            } elseif ($estado == 'NO VIABLE') {
                                                $badge_estado = '<span class="badge px-2 py-2" style="border-radius: 20px; border:1px solid #d59c0b5b; background-color: #6c757d;" data-bs-toggle="tooltip" title="No viable por parte de presupuesto "> <i class="bi bi-hand-thumbs-down " style="font-size:1.8em" ></i></span>';
                                            } elseif ($estado == 'VIABLE') {
                                                $badge_estado = '<span class="badge px-2 py-2" style="border-radius: 20px; border:1px solid #d59c0b5b; background-color: #f39c12;" data-bs-toggle="tooltip" title="Viable por parte de presupuesto "> <i class="bi bi-hand-thumbs-up " style="font-size:1.8em" ></i></span>';
                                            } else {
                                                $badge_estado = '<span class="badge badge-secondary px-3 py-1" style="border-radius: 20px;">' . htmlspecialchars($estado) . '</span>';
                                            }

                                            if ($estado_gerencia == 'DEVUELTO') {
                                                $badge_gerencia = '<span class="badge px-2 py-2" style="border-radius: 20px; border:1px solid #d59c0b5b; background-color: rgb(249, 28, 187);" data-bs-toggle="tooltip" title="Devuelto por parte de Gerencia "> <i class="bi bi-check-all " style="font-size:1.8em" ></i></span>';
                                            } elseif ($estado_gerencia == 'ACEPTADO') {
                                                $badge_gerencia = '<span class="badge px-2 py-2" style="border-radius: 20px; border:1px solid #d59c0b5b; background-color: rgb(40, 149, 47);" data-bs-toggle="tooltip" title="Aceptado parte de Gerencia "> <i class="bi bi-check-all " style="font-size:1.8em" ></i></span>';
                                            } elseif (!empty($estado_gerencia)) {
                                                $badge_gerencia = '<span class="badge badge-secondary ml-2">' . htmlspecialchars($estado_gerencia) . '</span>';
                                            } else {
                                                $badge_gerencia = '';
                                            }
                                ?>
                                            <tr style="text-align: center; vertical-align: middle; font-size: 13px;">
                                                <td class="text-center">RAD_<?= htmlspecialchars($row['id']) ?></td>
                                                <td class=" nombres text-align: start;  font-size: 14px;"><?= htmlspecialchars($row['con_nombres']) ?> <br>
                                                    <div class="apellidos"> <?= htmlspecialchars($row['con_apellidos']) ?> </div>
                                                </td>
                                                <td class="text-center">
                                                    <a href="index.php?page=Personal/informacion_personal&con_num_identidad=<?= urlencode($row['con_num_identidad']) ?>"
                                                        class="text-align: center; vertical-align: middle; font-size: 13px;">
                                                        <?= htmlspecialchars($row['con_num_identidad']) ?>
                                                    </a>
                                                </td>
                                                <td class="text-align: center; vertical-align: middle; font-size: 13px;"><?= htmlspecialchars($row['con_fecha_inicio']) ?></td>
                                                <td class="text-align: center; vertical-align: middle; font-size: 13px;"><?= htmlspecialchars($row['sol_nuevo_rol']) ?></td>
                                                <td class="text-align: center; vertical-align: middle; font-size: 13px;"><?= htmlspecialchars($row['sol_nuevo_proyecto']) ?></td>
                                                <td class="text-align: center; vertical-align: middle; font-size: 13px;">$ <?= number_format($salario, 0, ',', '.') ?></td>
                                                <td class="text-align: center; vertical-align: middle; font-size: 13px;">$ <?= number_format($valorOt, 0, ',', '.') ?></td>
                                                <td class="text-align: center; vertical-align: middle; font-size: 13px;"><?= htmlspecialchars($row['sol_tipo_otrosi']) ?></td>
                                                <td class="text-align: center; vertical-align: middle; font-size: 13px;"><?= htmlspecialchars($row['sol_fecha_inicio']) ?></td>
                                                <td class="text-align: center; vertical-align: middle; font-size: 13px;"><?= htmlspecialchars($row['sol_nueva_fecha_final']) ?></td>
                                                <td class="text-align: center; vertical-align: middle; font-size: 13px;"><?= htmlspecialchars($row['sol_duracion']) ?></td>
                                                <td class="text-align: center; vertical-align: middle; font-size: 13px; d-fex flex-column"><?= $badge_estado . ' ' . $badge_gerencia ?></td>
                                                <td class="text-align: center; vertical-align: middle; font-size: 13px;">
                                                    <?php
                                                    $estado = strtoupper(trim($row['sol_estado'] ?? ''));
                                                    $estadoGerencia = strtoupper(trim($row['sol_estado_gerencia'] ?? ''));

                                                    $puedeEditar = in_array($estado, ['NO VIABLE', 'RECHAZADO', 'NO ACEPTADO'], true)
                                                        || $estadoGerencia === 'DEVUELTO';
                                                    ?>

                                                    <?php if ($puedeEditar) : ?>
                                                        <!-- <a href="index.php?page=Personal/editar_solicitud&con_num_identidad=<?= urlencode($row['con_num_identidad']) ?>"
                                                            class="btn btn-primary btn-sm" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a> -->

                                                        <div class="accion-wrapper">
                                                            <a href="index.php?page=Personal/editar_solicitud&con_num_identidad=<?= urlencode($row['con_num_identidad']) ?>"
                                                                class="revisar-btn "
                                                                data-bs-toggle='tooltip'
                                                                title="Editar solicitud">
                                                                <i class="bi bi-file-earmark-richtext"></i>
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php
                                        endwhile;
                                    else :
                                        ?>
                                        <tr>
                                            <td colspan="14">No se encontraron registros disponibles</td>
                                        </tr>
                                    <?php
                                    endif;
                                else :
                                    ?>
                                    <tr>
                                        <td colspan="14">Error en la consulta: <?= htmlspecialchars($mysqli->error) ?></td>
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


<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {

        const table = $('#dataTable').DataTable({
            autoWidth: false,
            language: {
                decimal: ",",
                thousands: ".",
                processing: "Procesando...",
                search: "Buscar:",
                lengthMenu: "Mostrar _MENU_ registros",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                infoEmpty: "Mostrando 0 a 0 de 0 registros",
                infoFiltered: "(filtrado de _MAX_ registros totales)",
                loadingRecords: "Cargando...",
                zeroRecords: "No se encontraron resultados",
                emptyTable: "No hay datos disponibles en la tabla",
                paginate: {
                    first: "Primero",
                    previous: "Anterior",
                    next: "Siguiente",
                    last: "Último"
                },
                aria: {
                    sortAscending: ": activar para ordenar la columna de manera ascendente",
                    sortDescending: ": activar para ordenar la columna de manera descendente"
                }
            },
            columnDefs: [{
                targets: [6, 7, 11],
                visible: false
            }]
        });
        $('.toggle-col').each(function() {
            const colIndex = parseInt($(this).data('col'), 10);
            $(this).prop('checked', table.column(colIndex).visible());
        });
        $(document).on('change', '.toggle-col', function() {
            const colIndex = parseInt($(this).data('col'), 10);
            const visible = $(this).is(':checked');
            table.column(colIndex).visible(visible);
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = [].slice.call(
            document.querySelectorAll('[data-bs-toggle="tooltip"]')
        );

        tooltipTriggerList.forEach(function(tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>