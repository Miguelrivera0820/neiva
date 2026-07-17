<?php
// require "../../conexion.php";

// session_start();

$error_login = "";

if ($_POST) {
    $usuario_cons = $_POST['usuario_cons'];
    $password_cons = $_POST['password_cons'];

    $sql = "SELECT id_usuario, usuario_cons, password_cons, nombre_usuario, apellido_usuario, rol_usuario, cedula_usuario FROM usuarios_cons WHERE usuario_cons='$usuario_cons'";
    $resultado = $mysqli->query($sql);

    if (!$resultado) {
        die("Error en la consulta SQL: " . $mysqli->error);
    }

    $num = $resultado->num_rows;

    if ($num > 0) {
        $row = $resultado->fetch_assoc();

        if ($password_cons == $row['password_cons']) {
            $_SESSION['id_usuario'] = $row['id_usuario'];
            $_SESSION['usuario_cons'] = $row['usuario_cons'];
            $_SESSION['nombre_usuario'] = $row['nombre_usuario'];
            $_SESSION['apellido_usuario'] = $row['apellido_usuario'];
            $_SESSION['rol_usuario'] = $row['rol_usuario'];
            $_SESSION['cedula_usuario'] = $row['cedula_usuario'];

            header("Location: inicio.php");
            exit();
        } else {
            $error_login = "Usuario o contraseña incorrectos.";
        }
    } else {
        $error_login = "Usuario o contraseña incorrectos.";
    }
}

$cedula_usuario = $_SESSION['cedula_usuario'];

$sql = "SELECT 
    dt.id_devolucion,
    dt.historial_cod_tramite AS entrega_cod_tramite,
    dt.rol_actual,
    dt.nombre_sesion,
    dt.apellido_sesion,
    dt.historial_fecha_tramite,
    dt.motivo_devolucion,
    dt.observacion_a_usuario_tramite,
    dt.fecha_limite,
    dt.fecha_creacion,
    tr.npn_predio,
    tr.mutacion_tramite,
    (
        SELECT et.es_nombre
        FROM estados_tramite et
        WHERE et.cod_tramite = dt.historial_cod_tramite
        ORDER BY et.id DESC
        LIMIT 1
    ) AS es_nombre
FROM devolucion_tramites AS dt
LEFT JOIN tramite_radicacion AS tr 
    ON dt.historial_cod_tramite = tr.cod_tramite
WHERE dt.creacion_tram_cc_usuario = ?
   AND (dt.estado_devolucion IS NULL OR dt.estado_devolucion <> 'SUBSANADO')  -- FILTRO CORRECTO
   AND NOT EXISTS (
       SELECT 1 FROM tramites_cancelados tc
       WHERE tc.cod_tramite = dt.historial_cod_tramite
         AND tc.estado = 'CANCELADO'
   )
ORDER BY dt.historial_fecha_tramite DESC";


$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $cedula_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

$tramites = [];
while ($row = $resultado->fetch_assoc()) {
    $tramites[] = $row;
}

$rol_usuario = $_SESSION['rol_usuario'];

$mapa_roles = [
    'coordinacion_tecnica' => 'est_conservacion',
    'lider_juridico'       => 'est_lider_juridico',
    'control_calidad'      => 'est_control_calidad',
    'lider_economico'      => 'est_lider_economico',
    'consolidacion'        => 'est_consolidacion',
    'edicion'              => 'est_edicion',
    'avaluos'              => 'est_avaluos',
    'reconocimiento'       => 'est_reconocimiento',
    'director'             => 'est_director',
    'ventanilla_catastral' => 'est_ventanilla',
    'procedencia_juridica' => 'est_procedencia',
];

?>

<style>
    /* Número activo en la paginación */
    .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
        background-color: #002F55 !important;
        /* Rojo */
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
</style>

<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->

<!-- DataTables CSS con integración Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between my-4">
        <h1 class="h2 mb-0 text-gray-1200"><B>TRAMITES EN DEVOLUCIÓN</B></h1>
    </div>


    <div class="row">

        <div class="col-lg-12">

            <!-- Dropdown Card Example -->
            <div class="card shadow mb-4">
                <!-- Card Header - Dropdown -->
                <div
                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between" style="background-color: #002F55;">
                    <h6 class="m-0 font-weight-bold text-primary text-white">Información de trámites en devolución</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggl text-white" href="#" role="button" id="dropdownMenuLink"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>

                        </a>

                    </div>

                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th style="text-align: center; vertical-align: middle;">Fecha Asignación</th>
                                    <th style="text-align: center; vertical-align: middle;">Cod. Tramite</th>
                                    <th style="text-align: center; vertical-align: middle;">Area Asignada</th>
                                    <th style="text-align: center; vertical-align: middle;">NPN Predio</th>
                                    <th style="text-align: center; vertical-align: middle;">Tipo de Trámite</th>
                                    <th style="text-align: center; vertical-align: middle;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tramites as $tramite): ?>
                                    <tr>
                                        <td style="text-align: center; vertical-align: middle;"><?php echo date("Y-m-d H:i", strtotime($tramite['fecha_creacion'])); ?></td>

                                        <td style="text-align: center; vertical-align: middle;">
                                            <a href="index.php?page=tramites/acciones/ver_tramite_rad&cod=<?php echo urlencode($tramite['entrega_cod_tramite']); ?>">
                                                <?php echo htmlspecialchars($tramite['entrega_cod_tramite']); ?>
                                            </a>
                                        </td>

                                        <td style="text-align: center; vertical-align: middle;"><?php echo htmlspecialchars($tramite['rol_actual']); ?></td>
                                        <td style="text-align: center; vertical-align: middle;"><?php echo htmlspecialchars($tramite['npn_predio']); ?></td>
                                        <td style="text-align: center; vertical-align: middle;"><?php echo htmlspecialchars($tramite['mutacion_tramite']); ?></td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <a href="index.php?page=tramites/acciones/ver_tramite_rad&cod=<?php echo urlencode($tramite['entrega_cod_tramite']); ?>"
                                                class="btn btn-sm text-white my-1" style="background-color: #022F55"><b>Ver</b></a>


                                            <a href="index.php?page=seguimiento/asignar_subsanacion&cod=<?php echo urlencode($tramite['entrega_cod_tramite']); ?>"
                                                class="btn btn-sm" style="background-color: #66CC99; color:#022F55"><b>SUBSANAR</b></a>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>
                            </tbody>

                        </table>
                    </div>

                </div>

            </div>

        </div>
        <!-- /.container-fluid -->
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
            });
        </script>

    </div>
    <!-- End of Main Content -->

</div>
