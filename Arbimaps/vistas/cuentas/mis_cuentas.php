<?php
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 3) . '/conexion.php';
neiva_require_auth();
neiva_require_methods('GET');

$idUsuario = isset($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : 0;

echo "<script>console.log('variable: " . $idUsuario . "');</script>";

$query = "SELECT 
            id,
            fecha_subida, 
            numero_identidad, 
            primer_nombre,  
            primer_apellido, 
            segundo_nombre, 
            segundo_apellido, 
            telefono, 
            correo, 
            cargo, 
            proyecto,
            valor_aprobado, 
            estado, 
            estado_seguridad_social,
            estado_final, 
            pagado, 
            Periodo_Facturacion, 
            fecha_aprobado,
            anio_cuenta
            FROM cuenta 
            WHERE numero_identidad = $idUsuario AND estado IN ('Aprobado', 'Pendiente') 
            AND estado_final != 'Rechazado'
            AND estado_seguridad_social != 'Rechazado'
        ORDER BY fecha_subida ASC";

$result = $mysqli->query($query);


// Se realiza una consulta para obtener el cargo del usuario
//para permisos y manejo del MENÚ
// $sql = "SELECT id_usuario, cedula_usuario, rol_usuario  FROM usuarios_cons WHERE id_usuario = $idUsuario";
// $resultado = $mysqli->query($sql);


// Permite visualizar el menú a estos roles
// $roles_coordinador            = ['coordinador'];
// $roles_Lider_Reconocimiento   = ['Lider_Reconocimiento'];
// $roles_profesional_social     = ['profesional_social'];
// $roles_admitidos              = ['coordinador', 'Lider_Reconocimiento', 'profesional_social'];
?>

<style>
    /* Número activo en la paginación */
    .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
        background-color: #002F55 !important;
       
        border-color: #002F55 !important;
        color: #fff !important;
    }

    /* Hover sobre números */
    .dataTables_wrapper .dataTables_paginate .page-link:hover {
        background-color: #457b9d !important;
        /* Azul oscuro */
        color: #fff !important;
    }

    /* Texto de los links de paginación */
    .dataTables_wrapper .dataTables_paginate .page-link {
        color: #002f55 !important;
        /* Azul Bootstrap */
        border-radius: 8px;
        /* Bordes más redondeados */
        margin: 0 2px;
    }

    /* --- ESTILO GENERAL DE LAS TABS --- */
    .custom-tabs {
        border-bottom: none !important;
        gap: 0px;
    }

    .custom-tabs .nav-link {
        border: none !important;
        padding: 10px 28px;
        font-weight: 600;
        border-radius: 8px 25px 0% 0;
        /* Curvas superiores */
        position: relative;
        top: 6px;
        /* Baja las inactivas */
        transition: all 0.25s ease-in-out;
        color: #ffffff;
    }

    /* --- CURVA INFERIOR PARA FUSIONAR CON LA CARD --- */
    .custom-tabs .nav-link.active::after {
        content: "";
        position: absolute;
        bottom: -10px;
        left: 0;
        right: 0;
        height: 12px;
        background: inherit;
        border-radius: 0 0 16px 16px;
        z-index: 5;
    }

    /* --- COLORES PERSONALIZADOS PARA CADA TAB --- */

    /* 🟦 Radicadas (Azul) */
    .nav-link.radicadas-tab {
        background: #002f55c7;
        color: #ffffffd8;
       
    }

    .nav-link.radicadas-tab.active {
        background: #002F55 !important;
        top: 0px;
        color: #fff;
    }

   
    .nav-link.pagadas-tab {
        background: #2887d5a9;
        color: #f5f2f2ea;
    }

    .nav-link.pagadas-tab.active {
        background: #2887d5 !important;
        top: 0px;
        color: #ffffffff;
    }

    
    .nav-link.rechazadas-tab {
        background: #ef9910c7;
        color: #ffffffd8;
    }

    .nav-link.rechazadas-tab.active {
        background: #EF9A10 !important;
        top: 0px;
        color: #fff;
    }

    .btn-link {
        font-size: 14px !important;
    }
</style>
<!-- DataTables CSS con integración Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container-fluid ">
    <div class="my-4 text-center">
        <h4 class=" mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 700 !important ">MODULO DE CUENTAS</h4>
        <small> En este apartado podrás consultar tanto el estado actual de tu cuenta como el registro de cuentas pagadas y rechazadas</small>
    </div>


    <ul class="nav nav-tabs custom-tabs mb-0" id="myTab" role="tablist" style="width: 75%;">

        <!-- Azul -->
        <li class="nav-item" role="presentation">
            <button class="nav-link radicadas-tab active d-flex justify-content-center align-items-center"
                id="radicadas-tab"
                data-bs-toggle="tab"
                data-bs-target="#radicadas"
                type="button"
                role="tab">
                <i class="bi bi-journal-plus me-2" style="color: #ffffffff;"></i>
                Cuentas radicadas
            </button>
        </li>

        <!-- Verde -->
        <li class="nav-item" role="presentation">
            <button class="nav-link pagadas-tab"
                id="pagadas-tab"
                data-bs-toggle="tab"
                data-bs-target="#pagadas"
                type="button"
                role="tab">
                <i class="bi bi-journal-check me-2" style="color: #ffffffff;"></i>
                Cuentas pagadas
            </button>
        </li>

        <!-- Naranja -->
        <li class="nav-item" role="presentation">
            <button class="nav-link rechazadas-tab"
                id="rechazadas-tab"
                data-bs-toggle="tab"
                data-bs-target="#rechazadas"
                type="button"
                role="tab">
                <i class="bi bi-journal-x me-2" style="color: #ffffffff;"></i>
                Cuentas rechazadas
            </button>
        </li>

    </ul>


    <div class="tab-content">
        <div class="tab-pane fade show active" id="radicadas" role="tabpanel">
            <div class="card shadow mb-4" style="border-radius:0px 15px 15px 15px;">
                <div
                    class="card-header py-3 text-center d-flex flex-row align-items-center justify-content-between" style="background-color: #002F55; border-radius:0px 15px 0px 0px;">
                    <div class="tabla-cuentas-titulo">
                        <div class="d-flex align-items-center ">
                            <!-- <div class="tabla-cuentas-icono bg-white me-2 d-flex justify-content-center align-items-center rounded-5" style="width: 32px; height: 32px;">
                                <i class="bi bi-journal-plus"></i>
                            </div> -->
                            <div class="ms-1">
                                <!-- <div class="tabla-cuentas-titulo-principal text-start " style="color: #fff !important;">
                                    Cuentas radicadas
                                </div> -->
                                <div class="subtexto " style="font-size:0.8em; color:#fff ">
                                    Listado de cuentas que has radicado
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- cuerpo de la tabla -->
                <div class="card-body">
                    <div class="table-responsive ">
                        <table class="table table-bordered " id="dataTable2" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px">Fecha</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px">N° Radicado</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px">Cédula</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px">Nombre</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px">Apellido</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px">Cargo</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px">Proyecto</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px">Año</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px">Periodo</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px">Fecha de Aprobación</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                if ($result) {
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td style='text-align: center; vertical-align: middle; font-size: 14px'>" . htmlspecialchars($row['fecha_subida']) . "</td>";
                                            echo "<td style='text-align: center; vertical-align: middle; font-size: 14px'>ARB_CUE" . htmlspecialchars($row['id']) . "</td>";
                                            echo "<td style='text-align: center; vertical-align: middle; font-size: 14px'>
                                                                <a href='index.php?page=cuentas/radicacion/detalles_mi_cuenta&numero_identidad=" . urlencode($row['numero_identidad']) . "&id=" . urlencode($row['id']) . "' class='btn btn-link'>
                                                                    " . htmlspecialchars($row['numero_identidad']) . "
                                                                </a>
                                                            </td>";
                                            echo "<td style='text-align: center; vertical-align: middle; font-size: 14px'>" . htmlspecialchars($row['primer_nombre']) . "</td>";
                                            echo "<td style='text-align: center; vertical-align: middle; font-size: 14px'>" . htmlspecialchars($row['primer_apellido']) . "</td>";
                                            echo "<td style='text-align: center; vertical-align: middle; font-size: 14px'>" . htmlspecialchars($row['cargo']) . "</td>";
                                            echo "<td style='text-align: center; vertical-align: middle; font-size: 14px'>" . htmlspecialchars($row['proyecto']) . "</td>";
                                            echo "<td style='text-align: center; vertical-align: middle; font-size: 14px'>" . htmlspecialchars($row['anio_cuenta']) . "</td>";
                                            echo "<td style='text-align: center; vertical-align: middle; font-size: 14px; font-weight:700; background-color:#002F55; color: white'>" . htmlspecialchars($row['Periodo_Facturacion']) . "</td>";
                                            echo "<td style='text-align: center; vertical-align: middle; font-size: 14px'>" . htmlspecialchars($row['fecha_aprobado'] ?? 'En proceso de validación') . "</td>";
                                        }
                                    } else {
                                        // echo "<tr><td colspan='7'>No se encontraron registros disponibles</td></tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='7'>Error en la consulta: " . htmlspecialchars($mysqli->error) . "</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade " id="pagadas" role="tabpanel">
            <div class="card shadow mb-4" style="border-radius:0px 15px 15px 15px">
                <div
                    class="card-header py-3 text-center d-flex flex-row align-items-center justify-content-between" style="background-color: #2887d5; border-radius:0px 15px 0px 0px;">
                    <div class="tabla-cuentas-titulo">
                        <div class="d-flex align-items-center ">
                            <!-- <div class="tabla-cuentas-icono bg-white me-2 d-flex justify-content-center align-items-center rounded-5" style="width: 32px; height: 32px;">
                                <i class="bi bi-journal-check"></i>
                            </div> -->
                            <div class="ms-1">
                                <!-- <div class="tabla-cuentas-titulo-principal text-start " style="color: #fff !important;">
                                    Cuentas pagadas
                                </div> -->
                                <div class="subtexto " style="font-size:0.8em; color:#fff ">
                                    Listado de cuentas que han sido pagadas
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- cuerpo de la tabla -->
                <div class="card-body">
                    <div class="table-responsive ">
                        <table class="table table-bordered " id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px ">Fecha radicación</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px">ID Radicación</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px">Cédula</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px">Nombres</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px">Apellidos</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px">Cargo</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px">Proyecto</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px">Año</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px">Mes</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px ">Valor Pagado</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $sql = "SELECT 
                                            id,
                                            pag_fecha_subida,  
                                            pag_numero_identidad, 
                                            pag_primer_nombre, 
                                            pag_segundo_nombre, 
                                            pag_primer_apellido,
                                            pag_segundo_apellido, 
                                            pag_cargo, 
                                            pag_proyecto, 
                                            pag_valor_aprobado, 
                                            pag_Periodo_Facturacion,
                                            pag_anio_cuenta
                                        FROM cuentas_pagadas
                                        WHERE pag_numero_identidad = $idUsuario
                                        ORDER BY pag_fecha_subida ASC";
                                if ($result = $mysqli->query($sql)) {
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td style='text-align: center; vertical-align: middle; font-size: 14px'>" . date('Y-m-d', strtotime($row['pag_fecha_subida'])) . "</td>";
                                            echo "<td style='text-align: center; vertical-align: middle; font-size: 14px'>
                                                                <a href='index.php?page=cuentas/radicacion/detalle_cuenta&pag_numero_identidad=" . urlencode($row['pag_numero_identidad']) . "&id=" . urlencode($row['id']) . "' class='btn btn-link'>
                                                                    " . htmlspecialchars($row['pag_numero_identidad']) . "
                                                                </a>
                                                            </td>";
                                            echo "<td style='text-align: center; vertical-align: middle; font-size: 14px'>" . htmlspecialchars($row['pag_numero_identidad']) . "</td>";
                                            echo "<td style='text-align: center; vertical-align: middle; font-size: 14px'>" . htmlspecialchars($row['pag_primer_nombre'] . ' ' . $row['pag_segundo_nombre']) . "</td>";
                                            echo "<td style='text-align: center; vertical-align: middle; font-size: 14px'>" . htmlspecialchars($row['pag_primer_apellido'] . ' ' . $row['pag_segundo_apellido']) . "</td>";
                                            echo "<td style='text-align: center; vertical-align: middle; font-size: 14px'>" . htmlspecialchars($row['pag_cargo']) . "</td>";
                                            echo "<td style='text-align: center; vertical-align: middle; font-size: 14px'>" . htmlspecialchars($row['pag_proyecto']) . "</td>";
                                            echo "<td style='text-align: center; vertical-align: middle; font-size: 14px'>" . htmlspecialchars($row['pag_anio_cuenta']) . "</td>";
                                            echo "<td style='text-align: center; vertical-align: middle; font-size: 14px'>" . htmlspecialchars($row['pag_Periodo_Facturacion']) . "</td>";
                                            echo "<td style='text-align: center; vertical-align: middle; font-size: 14px'>$" . number_format($row['pag_valor_aprobado'], 0, ',', '.') . "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        // echo "<tr><td colspan='9'>No se encontraron registros disponibles</td></tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='9'>Error en la consulta: " . htmlspecialchars($mysqli->error) . "</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade " id="rechazadas" role="tabpanel">
            <div class="card shadow mb-4" style="border-radius:0px 15px 15px 15px">
                <div
                    class="card-header py-3 text-center d-flex flex-row align-items-center justify-content-between" style="background-color: #EF9A10; border-radius:0px 15px 0px 0px;">
                    <div class="tabla-cuentas-titulo">
                        <div class="d-flex align-items-center ">
                            <!-- <div class="tabla-cuentas-icono bg-white me-2 d-flex justify-content-center align-items-center rounded-5" style="width: 32px; height: 32px;">
                                <i class="bi bi-journal-x"></i>
                            </div> -->
                            <div class="ms-1">
                                <!-- <div class="tabla-cuentas-titulo-principal text-start fw-bold fs-4 " style="color: #ffff !important;">
                                    Cuentas rechazadas
                                </div> -->
                                <div class="subtexto " style="font-size:0.8em; color:#ffff ">
                                    Listado de cuentas que han sido rechazadas
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- cuerpo de la tabla -->
                <div class="card-body">
                    <div class="table-responsive ">
                        <table class="table table-bordered " id="dataTable3" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px " class="text-center">Fecha de rechazo</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px " class="motivo-rechazo text-center">Motivo de Devolución</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px " class="text-center">Cédula</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px " class="text-center">Nombres</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px " class="text-center">Apellidos</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px " class="text-center">Devuelto Por</th>
                                    <th style="text-align: center; vertical-align: middle; font-size: 14px " class="text-center">Año</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $sql = "SELECT 
                                            cr.id, 
                                            cr.numero_identidad, 
                                            cr.razon, 
                                            cr.fecha_rechazo, 
                                            cr.primer_nombre, 
                                            cr.segundo_nombre, 
                                            cr.primer_apellido, 
                                            cr.segundo_apellido,
                                            cr.rec_anio_cuenta,
                                            u.nombre_usuario AS nombre, u.apellido_usuario AS apellido
                                        FROM cuentas_rechazadas cr
                                        LEFT JOIN usuarios_cons u ON cr.usuario_rechazo_id = u.id_usuario
                                        WHERE cr.numero_identidad = $idUsuario
                                        ORDER BY cr.fecha_rechazo ASC";
                                if ($result = $mysqli->query($sql)) {
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td style='text-align: center; vertical-align: middle; font-size: 14px'>" . htmlspecialchars($row['fecha_rechazo']) . "</td>";
                                            echo "<td class='motivo_rechazo' style='text-align: center; vertical-align: middle; font-size: 14px'>" . htmlspecialchars($row['razon']) . "</td>";
                                            echo "<td style='text-align: center; vertical-align: middle; font-size: 14px'>
                                                                        <a href='index.php?page=cuentas/radicacion/detalle_cuenta_rechazadas&numero_identidad=" . urlencode($row['numero_identidad']) . "&id=" . urlencode($row['id']) . "' class='btn btn-link'>
                                                                            " . htmlspecialchars($row['numero_identidad']) . "
                                                                        </a>
                                                                        </td>";
                                            echo "<td style='text-align: center; vertical-align: middle; font-size: 14px'>" . htmlspecialchars($row['primer_nombre'] . ' ' . $row['segundo_nombre']) . "</td>";
                                            echo "<td style='text-align: center; vertical-align: middle; font-size: 14px'>" . htmlspecialchars($row['primer_apellido'] . ' ' . $row['segundo_apellido']) . "</td>";
                                            echo "<td style='text-align: center; vertical-align: middle; font-size: 14px'>" . htmlspecialchars($row['nombre'] . ' ' . $row['apellido']) . "</td>";
                                            echo "<td style='text-align: center; vertical-align: middle; font-size: 14px'>" . htmlspecialchars($row['rec_anio_cuenta']) . "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        // echo "<tr><td colspan='6'>No se encontraron registros disponibles</td></tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6'>Error en la consulta: " . htmlspecialchars($mysqli->error) . "</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- SCRIPT DATATABLES PARA PODER BUSCAR FILTRAR Y DEMAS-->
    <!-- jQuery (requerido por DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- script para poner en español las tablas -->
    <script>
        $(document).ready(function() {

            $('#dataTable').DataTable({
                language: window.neivaDataTablesLanguage ? window.neivaDataTablesLanguage() : {}
            });

            $('#dataTable2').DataTable({
                language: window.neivaDataTablesLanguage ? window.neivaDataTablesLanguage() : {}
            });

            $('#dataTable3').DataTable({
                language: window.neivaDataTablesLanguage ? window.neivaDataTablesLanguage() : {}
            });

        });
    </script>

</div>
