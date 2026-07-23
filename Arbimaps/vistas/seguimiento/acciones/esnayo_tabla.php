<h1>Ensayo tablas bootstrap 5 en español</h1>

<?php
require "../conexion.php";

//  session_start();

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

$sql = "SELECT 
            t.cod_tramite, 
            t.fecha_rad, 
            t.mutacion_tramite, 
            t.primer_nombre_interesado, 
            t.primer_apellido_interesado, 
            t.fmi_predio,
            e.es_nombre
        FROM tramite_radicacion t
        LEFT JOIN estados_tramite e 
            ON e.id = (
                SELECT MAX(e2.id)
                FROM estados_tramite e2
                WHERE e2.cod_tramite = t.cod_tramite
            )
        ORDER BY t.fecha_rad DESC";


$resultado = $mysqli->query($sql);

$tramites = [];
if ($resultado) {
    while ($row = $resultado->fetch_assoc()) {
        $tramites[] = $row;
    }
}

?>

<!-- Bootstrap 5 CSS -->
<!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> -->

<!-- DataTables CSS con integración Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h4 class=" mb-0 fw-bold" style="color: #002F55; font-weight: 700 !important">TRÁMITES CATASTRALES NEIVA</h4>
    </div>

    <a href="index.php?page=tramites/asignacion_tramites" class="btn boton_t_as bg-white " style="border-left:3px solid #002F55;border-bottom:3px solid #002F55;">
        <b>TRÁMITES ASIGNADOS NEIVA</b>
    </a>

    <a href="index.php?page=tramites/revision_tramites" class="btn boton_t_rev bg-white" style="border-left:3px solid #F6C23E;border-bottom:3px solid #F6C23E;">
        <b>TRÁMITES EN REVISIÓN NEIVA</b>
    </a>
    <br>
    <br>

    <div class="row">

        <div class="col-lg-12">

            <!-- Dropdown Card Example -->
            <div class="card shadow mb-4" style="border-radius: 15px;">
                <!-- Card Header - Dropdown -->
                <div
                    class="card-header py-3 text-center d-flex flex-row align-items-center justify-content-between" style="background-color: #002F55;">
                    <h6 class="m-0 font-weight-bold text-white">Información de Trámites</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle text-white" href="#" role="button" id="dropdownMenuLink"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-white-400"></i>
                        </a>

                    </div>

                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th style="text-align: center; vertical-align: middle;">Fecha Radicación</th>
                                    <th style="text-align: center; vertical-align: middle;">Cod. Tramite</th>
                                    <th style="text-align: center; vertical-align: middle;">Solicitud Tramite</th>
                                    <th style="text-align: center; vertical-align: middle;">Nombre y Apellido Solicitante</th>
                                    <th style="text-align: center; vertical-align: middle;">FMI Predio</th>
                                    <th style="text-align: center; vertical-align: middle;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tramites as $tramite): ?>
                                    <tr>
                                        <td style="text-align: center; vertical-align: middle;"><?php echo date("Y-m-d H:i", strtotime($tramite['fecha_rad'])); ?></td>
                                        <td style="text-align: center; vertical-align: middle;"><a href="index.php?page=tramites/acciones/ver_tramite_rad&cod=<?php echo urlencode($tramite['cod_tramite']); ?>">
                                                <?php echo htmlspecialchars($tramite['cod_tramite']); ?></a>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;"><?php echo htmlspecialchars($tramite['mutacion_tramite']); ?></td>
                                        <td style="text-align: center; vertical-align: middle;"><?php echo htmlspecialchars($tramite['primer_nombre_interesado'] . ' ' . $tramite['primer_apellido_interesado']); ?></td>
                                        <td style="text-align: center; vertical-align: middle;"><?php echo htmlspecialchars($tramite['fmi_predio']); ?></td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <a href="index.php?page=tramites/acciones/ver_tramite_rad&cod=<?php echo urlencode($tramite['cod_tramite']); ?>"
                                                class="btn btn-sm text-white  my-1" style="background-color: #002F55">
                                                Ver
                                            </a>

                                            <a href="index.php?page=tramites/acciones/editar_tramite&cod=<?php echo urlencode($tramite['cod_tramite']); ?>"
                                                class="btn btn-sm shadow my-1 text-white" style="background-color:#0F5699">Editar</a>

                                            <?php if (!in_array(strtoupper($tramite['es_nombre']), ['ASIGNADO', 'REVISION', 'ENTREGADO', 'DEVOLUCION'])): ?>
                                                <a href="index.php?page=tramites/acciones/asignar_tram_procedencia&cod=<?php echo urlencode($tramite['cod_tramite']); ?>"
                                                    class="btn btn-sm shadow my-1" style="background-color:#66CC99">Asignar</a>
                                            <?php endif; ?>
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

        <!-- Inicialización DataTable en español -->
        <script>
            $(document).ready(function() {
                $('#dataTable').DataTable({
                    language: window.neivaDataTablesLanguage ? window.neivaDataTablesLanguage() : {}
                });
            });
        </script>

    </div>
    <!-- End of Main Content -->



</div