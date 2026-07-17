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
            $_SESSION['id_usuario']         = $row['id_usuario'];
            $_SESSION['usuario_cons']       = $row['usuario_cons'];
            $_SESSION['nombre_usuario']     = $row['nombre_usuario'];
            $_SESSION['apellido_usuario']   = $row['apellido_usuario'];
            $_SESSION['rol_usuario']        = $row['rol_usuario'];
            $_SESSION['cedula_usuario']     = $row['cedula_usuario'];

            header("Location: inicio.php");
            exit();
        } else {
            $error_login = "Usuario o contraseña incorrectos.";
        }
    } else {
        $error_login = "Usuario o contraseña incorrectos.";
    }
}

$sql = "SELECT 
    tr.cod_tramite, 
    tr.fecha_rad, 
    tr.mutacion_tramite, 
    his.historial_rol_usuario,
    his.historial_nombre_usuario,
    his.historial_apellido_usuario,
    his.creacion_tram_nombre_usuario,
    his.creacion_tram_apellido_usuario,
    his.creacion_tram_rol_usuario,
    his.historial_estado_tramite,
    his.est_ventanilla,
    his.fecha_ventanilla,
    his.est_procedencia,
    his.fecha_procedencia,
    his.est_atencion_procedencia,
    his.fecha_ate_procedencia,
    his.est_conservacion,
    his.fecha_conservacion,
    his.est_lider_juridico,
    his.fecha_lid_juridico,
    his.est_control_calidad,
    his.fecha_cont_calidad,
    his.est_lider_economico,
    his.fecha_lid_economico,
    his.est_consolidacion,
    his.fecha_consolidacion,
    his.est_edicion,
    his.fecha_edicion,
    his.est_avaluos,
    his.fecha_avaluos,
    his.est_reconocimiento,
    his.fecha_reconocimiento,
    his.est_director,
    his.fecha_director,
    his.fecha_limite,
    est.es_nombre,
    (
        SELECT a.asignacion_fecha_tramite
        FROM asignacion_tramite a
        WHERE a.asignacion_cod_tramite = his.historial_cod_tramite
        ORDER BY a.asignacion_fecha_tramite DESC
        LIMIT 1
    ) AS ultima_asignacion_fecha


FROM historial_asignacion his 
LEFT JOIN tramite_radicacion tr 
    ON his.historial_cod_tramite = tr.cod_tramite
LEFT JOIN estados_tramite est
    ON his.historial_estado_tramite = est.id
ORDER BY tr.fecha_rad DESC";


$resultado = $mysqli->query($sql);

$tramites = [];
if ($resultado) {
    while ($row = $resultado->fetch_assoc()) {

        $tramites[] = $row;
    }
}

?>

<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->

<!-- DataTables CSS con integración Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<style>
    .estado-filtro {
        display: flex;
        gap: 10px;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }

    .estado-item:hover {
        background-color: #002f551e;
        outline: 2px solid #002F55;
    }

    .estado-item {
        display: flex;
        align-items: center;
        gap: 6px;
        color: black;
    }

    .estado-bola {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
    }

    .limpiar:hover {
        background-color: #002F55;
        color: white;
    }

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


<div class="container-fluid">

    <!-- Page Heading -->
    <div class="my-4 text-center">
        <h1 class="h2 mb-0 text-gray-1200"><B>TRAMITES CATASTRALES NEIVA</B></h1>
    </div>
    <div style="margin-left:12.5%; border-left:3px solid #002F55; border-right:3px solid #002F55; 
    border-right:3px solid #002F55; border-top:1px solid #002f5550; border-bottom:1px solid #002f5550"
        class="py-3 w-75 shadow rounded-4 text-center card-filtros ">
        <h5 class="text-center">Filtrar por estádo del trámite</h5>
        <div class="estado-filtro py-2 ">
            <button class="estado-item btn border " data-estado="a_tiempo">
                <span class="estado-bola" style="background-color: #28a745;"></span> A TIEMPO
            </button>
            <button class="estado-item btn border" data-estado="a_vencer">
                <span class="estado-bola" style="background-color: #ffc107;"></span> A VENCER
            </button>
            <button class="estado-item btn border" data-estado="vencido">
                <span class="estado-bola" style="background-color: #dc3545;"></span> VENCIDO
            </button>
            <button class="estado-item btn border" data-estado="caducado">
                <span class="estado-bola" style="background-color: #6c757d;"></span> CADUCADO
            </button>
            <!-- Botón limpiar filtros -->
            <button id="limpiarFiltros" class="btn btn-sm fw-bold limpiar" style="border: 2px solid #002F55;">
                <i class="bi bi-filter-right me-2"></i> LIMPIAR FILTROS
            </button>
        </div>
    </div>

    <br>

    <div class="row">

        <div class="col-lg-12">

            <!-- Dropdown Card Example -->
            <div class="card shadow mb-4">
                <!-- Card Header - Dropdown -->
                <div
                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between" style="background-color: #002F55;">
                    <h6 class="m-0 font-weight-bold text-primary text-white">Información de Trámites Asignados</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle text-white" href="#" role="button" id="dropdownMenuLink"
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
                                    <th class="text-center">Fecha Radicación</th>
                                    <th class="text-center">Cod. Trámite</th>
                                    <th class="text-center">Tipo Trámite</th>
                                    <th class="text-center">Rol</th>
                                    <th class="text-center">Responsable</th>
                                    <th class="text-center">Fecha Asignación</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tramites as $tramite): ?>
                                    <?php
                                    $fecha_limite = strtotime($tramite['fecha_limite']);
                                    $hoy = strtotime(date('Y-m-d'));
                                    $diferencia_dias = floor(($fecha_limite - $hoy) / (60 * 60 * 24));

                                    if ($diferencia_dias >= 3) {
                                        $estado = "A TIEMPO";
                                        $color = "green"; // Verde
                                    } elseif ($diferencia_dias >= 1 && $diferencia_dias <= 2) {
                                        $estado = "A VENCER";
                                        $color = "orange"; // Naranja
                                    } elseif ($diferencia_dias < 0 && abs($diferencia_dias) <= 10) {
                                        $estado = "VENCIDO";
                                        $color = "red"; // Rojo
                                    } elseif (abs($diferencia_dias) > 10) {
                                        $estado = "CADUCADO";
                                        $color = "gray"; // Gris
                                    } else {
                                        $estado = "HOY";
                                        $color = "blue"; // Azul
                                    }

                                    // Para el data-estado quitamos espacios y lo pasamos a minúsculas
                                    $estado_attr = strtolower(str_replace(' ', '_', $estado));
                                    ?>
                                    <tr data-estado="<?= $estado_attr ?>">
                                        <td class="text-center"><?= date("Y-m-d H:i", strtotime($tramite['fecha_rad'])); ?></td>
                                        <td class="text-center">
                                            <a href="../ver_tramite_rad.php?cod=<?= urlencode($tramite['cod_tramite']); ?>">
                                                <?= htmlspecialchars($tramite['cod_tramite']); ?>
                                            </a>
                                        </td>
                                        <td class="text-center"><?= htmlspecialchars($tramite['mutacion_tramite']); ?></td>
                                        <td class="text-center"><?= htmlspecialchars($tramite['historial_rol_usuario']); ?></td>
                                        <td class="text-center"><?= htmlspecialchars($tramite['historial_nombre_usuario'] . ' ' . $tramite['historial_apellido_usuario']); ?></td>
                                        <td class="text-center"><?= htmlspecialchars($tramite['ultima_asignacion_fecha']); ?></td>
                                        <td class="text-center">
                                            <span style="color: <?= $color ?>; font-weight: bold;"><?= htmlspecialchars($estado) ?></span>
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

        <script>
            //Funcion filtros
            document.querySelectorAll('.estado-item').forEach(item => {
                item.addEventListener('click', function() {
                    const estadoSeleccionado = this.getAttribute('data-estado');
                    const filas = document.querySelectorAll('#dataTable tbody tr');

                    filas.forEach(fila => {
                        if (estadoSeleccionado === 'todos' || fila.getAttribute('data-estado') === estadoSeleccionado) {
                            fila.style.display = '';
                        } else {
                            fila.style.display = 'none';
                        }
                    });
                });
            });

            // Botón limpiar filtros
            document.getElementById('limpiarFiltros').addEventListener('click', function() {
                const filas = document.querySelectorAll('#dataTable tbody tr');
                filas.forEach(fila => {
                    fila.style.display = '';
                });
            });
        </script>

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