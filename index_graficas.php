<?php
require "conexion.php";

session_start();

$rol_usuario = $_SESSION['rol_usuario'] ?? '';
$roles_permitidos_rechazados = ['procedencia_juridica', 'ventanilla_catastral', 'director_catastro'];

// Debe estar logueado y su rol debe estar permitido
$tiene_permiso_rechazados = isset($_SESSION['nombre_usuario']) && in_array($rol_usuario, $roles_permitidos_rechazados);

$error_login = "";


$sql = "SELECT 
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
    his.fecha_limite
FROM historial_asignacion AS his
ORDER BY his.fecha_limite DESC";


$resultado = $mysqli->query($sql);

$tramites = [];
if ($resultado) {
    while ($row = $resultado->fetch_assoc()) {

        $tramites[] = $row;
    }
}

// Consulta para contar todas las asignaciones
$sql = "SELECT COUNT(*) AS total_asignaciones FROM historial_asignacion";
$result = $mysqli->query($sql);

$total_asignaciones = 0;
if ($result && $row = $result->fetch_assoc()) {
    $total_asignaciones = $row['total_asignaciones'];
}

// Consulta para contar asignaciones vencidas
$sql_vencidas = "SELECT COUNT(*) AS total_vencidas FROM historial_asignacion WHERE fecha_limite < CURDATE()";

$result_vencidas = $mysqli->query($sql_vencidas);

$total_vencidas = 0;
if ($result_vencidas && $row_vencidas = $result_vencidas->fetch_assoc()) {
    $total_vencidas = $row_vencidas['total_vencidas'];
}


$sql3 = "SELECT COUNT(*) AS cod_tramite FROM tramite_radicacion";
$result3 = $mysqli->query($sql3);

$total_radicadas = 0;
if ($result3 && $row_radicadas = $result3->fetch_assoc()) {
    $total_rad = $row_radicadas['cod_tramite'];
}

$roles_permitidos = ['procedencia_juridica', 'ventanilla_catastral', 'director_catastro'];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="imagenes/favicom_03.png">

    <title>Datos De Gestión</title>


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css"> -->
    <!-- Fondo personalizado para este template: Poppins-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <style>
        body,
        html {
            font-family: 'Poppins' !important;
            background-color: #f8f9fc !important;
        }
    </style>

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <!-- bootstrap icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://kit.fontawesome.com/9944c94262.js" crossorigin="anonymous"></script> <!-- Script para que funcionen animacion e iconos -->
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper" class="p-2">

        <!-- Sidebar -->

        <ul class="navbar-nav ms-2 sidebar sidebar-dark accordion shadow-sm border p-sm-1 p-0 " id="accordionSidebar" style=" border-radius: 12px; background-color: #002F55;">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index_graficas.php">
                <div class="sidebar-brand-icon">
                    <img src="imagenes/favicom_03.png" alt="Logo" style="height: 1.5em; ">
                </div>
                <div class="sidebar-brand-text mx-2 text-white"> <span style="font-size: 1em;">Conservación</span></div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0" style="background-color: #0f569980;">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item active d-flex justify-content-center align-items-center p-2">
                <a class="nav-link w-100 text-center" style="color: #002F55;" href="index_graficas.php">
                    <i class="bi bi-house-fill" style="font-size: 1em; color: #0F5699"></i>
                    <span style="font-size: 0.8em">Datos de Gestión</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider" style="background-color: #0f569980;">

            <!-- Heading -->
            <div class="sidebar-heading text-white">
                Actividades
            </div>

            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item ">
                <a class="boton_hover nav-link collapsed w-100 my-2" href="#" data-toggle="collapse" data-target="#collapseTwo"
                    aria-expanded="true" aria-controls="collapseTwo">
                    <i class="bi-envelope-paper "></i>
                    <span class="text-white" style="font-size:0.8em">Tramites</span>
                </a>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="py-2 collapse-inner  bg-light border" style="border-radius:10px">
                        <h5 class="collapse-header " style="color: #0f5699d2;">Acciones</h5>
                        <a class="collapse-item mx-2" style="color: #0F5699; font-size:0.8rem" href="tramites/creacion_tramite.php"><i class="bi bi-file-earmark-plus m-1"></i> Crear Trámite</a>
                        <!--  <a class="collapse-item" href="../buttons.html">Completar Trámites</a>-->
                        <a class="collapse-item mx-2" style="color: #0F5699; font-size:0.8rem" href="tramites/consultas_tramites.php"><i class="bi bi-search m-1"></i> Consultar Tramites</a>
                        <?php if (!empty($tiene_permiso_rechazados) && $tiene_permiso_rechazados): ?>
                            <a class="collapse-item mx-2" style="color: #0F5699; font-size:0.8rem" href="tramites/cuentas_rechazadas/rechazados.php">
                                Trámites rechazados
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($tiene_permiso_rechazados) && $tiene_permiso_rechazados): ?>
                            <a class="collapse-item mx-2" style="color: #0F5699; font-size:0.8rem" href="tramites/cuentas_rechazadas/no_procede_completar.php">
                                Trámites por completar
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($tiene_permiso_rechazados) && $tiene_permiso_rechazados): ?>
                            <a class="collapse-item mx-2" style="color: #0F5699; font-size:0.8rem" href="./tramites/cuentas_completadas/tramites_completos.php">
                                Trámites completados
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </li>

            <!-- Nav Item - Utilities Collapse Menu -->
            <li class="nav-item">
                <a class="boton_hover nav-link collapsed w-100 my-2" href="#" data-toggle="collapse" data-target="#collapseUtilities"
                    aria-expanded="true" aria-controls="collapseUtilities">
                    <i class="bi-clipboard-data "></i>
                    <span class="text-white" style=" font-size:0.8em;">Seguimiento</span>
                </a>
                <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities"
                    data-parent="#accordionSidebar">
                    <div class=" py-2 collapse-inner bg-light border" style="border-radius:10px">
                        <h6 class="collapse-header fs-1" style="color: #0f5699d2;">Acciones</h6>
                        <a class="collapse-item " style="color: #0F5699;font-size:0.8rem" href="tramites/mis_asignaciones/mis_asignaciones.php">
                            <i class="bi bi-file-earmark-person m-1"></i> Mis Asignaciones</a>
                        <a class="collapse-item" style="color: #0F5699;font-size:0.8rem"
                            href="tramites/mis_asignaciones/mis_revisiones.php">
                            <i class="fa-solid fa-file m-1"></i> Mis Revisiones
                        </a>

                        <a class="collapse-item" style="color: #0F5699;font-size:0.8rem"
                            href="tramites/mis_asignaciones/mis_devoluciones.php">
                            <i class="bi bi-file-earmark-x-fill m-1"></i> Mis Devoluciones
                        </a>

                        <a class="collapse-item" style="color: #0F5699; font-size:0.8rem" href="tramites/asignacion/asignacion_tramites.php">
                            <i class="bi bi-bar-chart-line m-1"></i> Estado de Trámite</a>

                    </div>
                </div>
            </li>

            <li class="nav-item">
                <a class="boton_hover nav-link collapsed w-100 my-2" href="#" data-toggle="collapse" data-target="#collapCertificadosCatastrales"
                    aria-expanded="true" aria-controls="collapseUtilities">
                    <i class="bi-award "></i><!-- PROPONER ICONOS PARA CERTIFICADOS CATASTRALES-->
                    <span class="text-white" style=" font-size:0.8em;">Certificados Catastrales</span>
                </a>
                <div id="collapCertificadosCatastrales" class="collapse" aria-labelledby="headingUtilities"
                    data-parent="#accordionSidebar">
                    <div class=" py-2 collapse-inner bg-light border" style="border-radius:10px">
                        <h6 class="collapse-header fs-1" style="color: #0f5699d2;">Acciones</h6>
                        <a class="collapse-item mx-0" style="color: #0F5699;font-size:0.7rem" href="certificados/cons_cert_catastral.php">
                            <i class="bi bi-search m-1"></i> Consulta Cert. Catastrales</a>
                        <a class="collapse-item mx-0" style="color: #0F5699;font-size:0.7rem" href="certificados/cons_cert_catastral.php">
                            <i class="bi-patch-check m-1"></i> Generar Certificado</a>
                        <a class="collapse-item mx-0" style="color: #0F5699; font-size:0.7rem" href="tramites/asignacion/asignacion_tramites.php">
                            <i class="bi-map m-1"></i> Manzana Catastrales</a>
                    </div>
                </div>
            </li>
       <!-- ----------------------------------------------- boton prática---------------------------------------------------- -->
            <li class="nav-item">
                <a href="Practica/index.php">práctica</a>
            </li>
            
            <hr class="sidebar-divider" style="background-color: #0f569980;">
             <div class="sidebar-heading text-white">
               GESTIÓN DE USUARIOS
            </div>

            <li class="nav-item">
                <a class="boton_hover nav-link collapsed w-100 my-2 " href="#" data-toggle="collapse" data-target="#collapUsuarios"
                    aria-expanded="true" aria-controls="collapseUtilities">
                    <i class="bi bi-people-fill"></i><!-- PROPONER ICONOS PARA CERTIFICADOS CATASTRALES-->
                    <span class="text-white" style=" font-size:0.8em;">Usuarios</span>
                </a>
                <div id="collapUsuarios" class="collapse" aria-labelledby="headingUtilities"
                    data-parent="#accordionSidebar">
                    <div class=" py-2 collapse-inner bg-light border" style="border-radius:10px">
                        <h6 class="collapse-header fs-1" style="color: #0f5699d2;">Accion usuario</h6>
                        <a class="collapse-item mx-0" style="color: #0F5699;font-size:0.8rem" href="usuarios/crear_usuario.php">
                            <i class="bi bi-person-plus m-1"></i>Crear Usuario</a>
                            <a class="collapse-item mx-0" style="color: #0F5699;font-size:0.8rem" href="usuarios/consultas_usuario.php">
                            <i class="bi bi-person-gear m-1"></i>Consultar Usuario</a>
                            <a class="collapse-item mx-0" style="color: #0F5699;font-size:0.8rem" href="#">
                            <i class="bi bi-calendar-check"></i> Seguimiento Usuario</a>
                    </div>
                </div>
            </li>
            <!-- Divider -->
            <hr class="sidebar-divider" style="background-color: #0f569980;">

            <!-- 
            <div class="sidebar-heading">
                Addons
            </div>

          
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages"
                    aria-expanded="true" aria-controls="collapsePages">
                    <i class="fas fa-fw fa-folder"></i>
                    <span>Pages</span>
                </a>
                <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Login Screens:</h6>
                        <a class="collapse-item" href="login.html">Login</a>
                        <a class="collapse-item" href="register.html">Register</a>
                        <a class="collapse-item" href="forgot-password.html">Forgot Password</a>
                        <div class="collapse-divider"></div>
                        <h6 class="collapse-header">Other Pages:</h6>
                        <a class="collapse-item" href="404.html">404 Page</a>
                        <a class="collapse-item" href="blank.html">Blank Page</a>
                    </div>
                </div>
            </li>

            
            <li class="nav-item">
                <a class="nav-link" href="charts.html">
                    <i class="fas fa-fw fa-chart-area"></i>
                    <span>Charts</span></a>
            </li>

       
            <li class="nav-item">
                <a class="nav-link" href="tables.html">
                    <i class="fas fa-fw fa-table"></i>
                    <span>Tables</span></a>
            </li>

          
            <hr class="sidebar-divider d-none d-md-block">    -->

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle" style="background-color: #0F5699;"></button>
            </div>



        </ul>

        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content" class=" px-1">

                <!-- Topbar -->
                <nav class="navbar navbar-expand rounded-4 topbar px-2 mb-4 static-top shadow" style="background-color: #002F55;border-radius: 12px;">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Search -->
                    <form
                        class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100  navbar-search">
                        <div class="input-group border bg-white" style="border-radius: 13px;">
                            <input type="text" class="form-control bg-transparent border-0 small" placeholder="Buscar..."
                                aria-label="Search" aria-describedby="basic-addon2">
                            <div class="input-group-append">
                                <button class="btn text-white " type="button" style="background-color: #0F5699; border-radius:12px">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-search fa-fw"></i>
                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                                aria-labelledby="searchDropdown">
                                <form class="form-inline mr-auto w-100 navbar-search">
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light border-0 small"
                                            placeholder="Search for..." aria-label="Search"
                                            aria-describedby="basic-addon2">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button">
                                                <i class="fas fa-search fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </li>

                        <!-- Nav Item - Alerts -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell fa-fw text-white"></i>
                                <!-- Counter - Alerts -->
                                <span class="badge badge-danger badge-counter">3+</span>
                            </a>
                            <!-- Dropdown - Alerts -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header">
                                    Alerts Center
                                </h6>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-primary">
                                            <i class="fas fa-file-alt text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">December 12, 2019</div>
                                        <span class="font-weight-bold">A new monthly report is ready to download!</span>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-success">
                                            <i class="fas fa-donate text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">December 7, 2019</div>
                                        $290.29 has been deposited into your account!
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-warning">
                                            <i class="fas fa-exclamation-triangle text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">December 2, 2019</div>
                                        Spending Alert: We've noticed unusually high spending for your account.
                                    </div>
                                </a>
                                <a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
                            </div>
                        </li>

                        <!-- Nav Item - Messages -->
                        <!-- <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-envelope fa-fw"></i>
                                -- Counter - Messages --
                                <span class="badge badge-danger badge-counter">12</span>
                            </a>
                            -- Dropdown - Messages 
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="messagesDropdown">
                                <h6 class="dropdown-header">
                                    Message Center
                                </h6>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image mr-3">
                                        <img class="rounded-circle" src="img/undraw_profile_1.svg" alt="...">
                                        <div class="status-indicator bg-success"></div>
                                    </div>
                                    <div class="font-weight-bold">
                                        <div class="text-truncate">Hi there! I am wondering if you can help me with a
                                            problem I've been having.</div>
                                        <div class="small text-gray-500">Emily Fowler · 58m</div>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image mr-3">
                                        <img class="rounded-circle" src="img/undraw_profile_2.svg" alt="...">
                                        <div class="status-indicator"></div>
                                    </div>
                                    <div>
                                        <div class="text-truncate">I have the photos that you ordered last month, how
                                            would you like them sent to you?</div>
                                        <div class="small text-gray-500">Jae Chun · 1d</div>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image mr-3">
                                        <img class="rounded-circle" src="img/undraw_profile_3.svg" alt="...">
                                        <div class="status-indicator bg-warning"></div>
                                    </div>
                                    <div>
                                        <div class="text-truncate">Last month's report looks great, I am very happy with
                                            the progress so far, keep up the good work!</div>
                                        <div class="small text-gray-500">Morgan Alvarez · 2d</div>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image mr-3">
                                        <img class="rounded-circle" src="https://source.unsplash.com/Mv9hjnEUHR4/60x60"
                                            alt="...">
                                        <div class="status-indicator bg-success"></div>
                                    </div>
                                    <div>
                                        <div class="text-truncate">Am I a good boy? The reason I ask is because someone
                                            told me that people say this to all dogs, even if they aren't good...</div>
                                        <div class="small text-gray-500">Chicken the Dog · 2w</div>
                                    </div>
                                </a>
                                <a class="dropdown-item text-center small text-gray-500" href="#">Read More Messages</a>
                            </div>
                        </li> -->

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-white small">
                                 <?php echo $_SESSION['nombre_usuario']; ?>
                                </span>
                                <img class="img-profile rounded-circle"
                                    src="img/undraw_profile.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Settings
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Activity Log
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Cerrar Sesión
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h4 class=" mb-0 fw-bold" style="color: #002F55; font-weight: 700 !important">DATOS DE GESTIÓN CATASTRAL</h4>
                        <a href="#" class=" boton_Gene_Reporte d-none d-sm-inline-block btn btn-sm shadow-sm text-white" style="background-color: #002F55; border-radius:7px"><i
                                class="icono_b_g bi bi-file-earmark-arrow-down fa-sm text-white"></i> Generar Reporte</a>
                    </div>

                    <!-- Consultas de cartas-->
                    <div class="row d-flex justify-content-center align-items-center">

                        <!-- Sección de las tarjetas de información -->
                        <div class="col-lg-12 col-xl-12 d-flex justify-content-center align-items-center ">
                            <!-- Consultas -->
                            <div class="row">

                                <!-- carta de tramites radicados -->
                                <div class="col-xl-2 col-md-6  p-1 ">
                                    <div class="card shadow  h-100" style="border-radius: 18px; background-color: #002F55;">
                                        <div class="card-body ">
                                            <div class="row">
                                                <div class="col-12 d-flex justify-content-between align-items-center">
                                                    <i class="bi bi-folder text-primary bg-white  p-2" style="border-radius: 12px;"></i>
                                                    <span class="text-white"><i class="bi bi-caret-up-fill text-success"></i>+15%</span>
                                                </div>
                                                <div class="col-12 mt-2">
                                                    <div class="text-xs font-weight-bold text-white text-uppercase my-2">
                                                        Trámites Radicados</div>
                                                    <div class="h5 mb-0 font-weight-bold text-white-800 d-flex justify-content-between align-items-center mx-1"
                                                        style="color: #ffffffff;">
                                                        <?php echo $total_rad; ?>
                                                        <a type="button" class="btn btn-sm bg-white " style="color: #0F5699; border-radius: 12px;">Ver más</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- carta de tramites asignados -->
                                <div class="col-xl-2 col-md-6 p-1">
                                    <div class="card border-left-primary shadow h-100 " style="border-radius: 15px;">
                                        <div class="card-body ">
                                            <div class="row">
                                                <div class="col-12 d-flex justify-content-between align-items-center ">
                                                    <i class="bi-clipboard-data p-2" style="border-radius: 12px; background-color:#13538f17; color:#0F5699"></i>
                                                    <span class="text-primary"><i class="bi bi-caret-up-fill text-success"></i>+15%</span>
                                                </div>
                                                <div class="col-12 mt-2">
                                                    <div class="text-xs font-weight-bold text-uppercase my-2" style="color: #0F5699;">
                                                        Trámites Asignados</div>
                                                    <div class="h5 mb-0 font-weight-bold text-white-800 d-flex justify-content-between align-items-center mx-1"
                                                        style="color: #0F5699;">
                                                        <?php echo $total_asignaciones; ?>
                                                        <a type="button" class="btn btn-sm" style="background-color: #0F5699; color: #fff; border: 1px solid #0F5699; border-radius: 12px;"
                                                        href="tramites/mis_asignaciones/mis_asignaciones.php">Ver más</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Carta de tramites Culminados -->
                                <div class="col-xl-2 col-md-6  p-1">
                                    <div class="card border-left-success shadow h-100" style="border-radius: 15px;">
                                        <div class="card-body ">
                                            <div class="row">
                                                <div class="col-12 d-flex justify-content-between align-items-center ">
                                                    <i class="bi-file-earmark-check p-2" style="border-radius: 12px; background-color:#13538f17; color:#0F5699"></i>
                                                    <span class="text-primary"><i class="bi bi-caret-up-fill text-success"></i>+15%</span>
                                                </div>
                                                <div class="col-12 mt-2">
                                                    <div class="text-xs font-weight-bold text-uppercase my-2" style="color: #0F5699;">
                                                        Trámites Culminados</div>
                                                    <div class="h5 mb-0 font-weight-bold text-white-800 d-flex justify-content-between align-items-center mx-1"
                                                        style="color: #0F5699;">
                                                        0
                                                        <a type="button" class="btn btn-sm bg-success text-white border-success" style="border-radius: 12px;">Ver más</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Carta de trámites vencidos -->
                                <div class="col-xl-2 col-md-6  p-1">
                                    <div class="card border-left-danger shadow h-100" style="border-radius: 15px;">
                                        <div class="card-body ">
                                            <div class="row">
                                                <div class="col-12 d-flex justify-content-between align-items-center">
                                                    <i class="bi-clock  p-2" style="border-radius: 12px; background-color:#13538f17; color:#0F5699"></i>
                                                    <span class="text-primary"><i class="bi bi-caret-up-fill text-success"></i>+15%</span>
                                                </div>
                                                <div class="col-12 mt-2">
                                                    <div class="text-xs font-weight-bold text-uppercase my-2" style="color: #0F5699;">
                                                        Trámites Vencidos</div>
                                                    <div class="h5 mb-0 font-weight-bold text-white-800 d-flex justify-content-between align-items-center mx-1"
                                                        style="color: #0F5699;">
                                                        <?php echo $total_vencidas; ?>
                                                        <a type="button" class="btn btn-sm bg-danger text-white" style=" border-radius: 12px;">Ver más</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- cartas se cartas Catastrales -->
                                <div class="col-xl-2 col-md-6  p-1 ">
                                    <div class="card border-left-warning shadow h-100" style="border-radius: 15px;">
                                        <div class="card-body ">
                                            <div class="row">
                                                <div class="col-12 d-flex justify-content-between align-items-center">
                                                    <i class="bi-file-earmark-text p-2" style="border-radius: 12px; background-color:#13538f17; color:#0F5699"></i>
                                                    <span class="text-primary"><i class="bi bi-caret-up-fill text-success"></i>+15%</span>
                                                </div>
                                                <div class="col-12 mt-2">
                                                    <div class="text-xs font-weight-bold text-uppercase my-2" style="color: #0F5699;">
                                                        Cartas Catastrales</div>
                                                    <div class="h5 mb-0 font-weight-bold text-white-800 d-flex justify-content-between align-items-center mx-1"
                                                        style="color: #0F5699;">
                                                        <?php echo $total_vencidas; ?>
                                                        <a type="button" class="btn btn-sm bg-warning text-white" style=" border-radius: 12px;">Ver más</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- cart certificados catastrales -->
                                <div class="col-xl-2 col-md-6  p-1">
                                    <div class="card border-left-info shadow h-100" style="border-radius: 15px;">
                                        <div class="card-body ">
                                            <div class="row">
                                                <div class="col-12 d-flex justify-content-between align-items-center">
                                                    <i class="bi-award p-2" style="border-radius: 12px; background-color:#13538f17; color:#0F5699"></i>
                                                    <span class="text-primary"><i class="bi bi-caret-up-fill text-success"></i>+15%</span>
                                                </div>
                                                <div class="col-12 mt-2">
                                                    <div class="text-xs font-weight-bold text-uppercase my-2" style="color: #0F5699;">
                                                        Certificados Catastrales</div>
                                                    <div class="h5 mb-0 font-weight-bold text-white-800 d-flex justify-content-between align-items-center mx-1"
                                                        style="color: #0F5699;">
                                                        <?php echo $total_vencidas; ?>
                                                        <a type="button" class="btn btn-sm text-white bg-info" style="color: #0F5699;  border-radius: 12px;"
                                                        href="certificados/cons_cert_catastral.php">Ver más</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Card de gráfica de torta -->
                        <div class="col-md-12 col-xl-6 col-xxl-5 d-flex mt-1 p-1">
                            <div class="card shadow w-100 h-100 p-2" style=" border-radius: 15px;">
                                <div class=" py-2 mb-2 text-center" style=" border-radius: 10px; background-color: #002F55;">
                                    <h6 style="color: #ffffffff;" class="mb-0">Trámites</h6>
                                </div>
                                <canvas id="myPieChart" class="h-100"></canvas>
                            </div>
                        </div>

                        <!-- Card de gráfica de barra -->
                        <div class="col-md-12 col-xl-6 d-flex mt-1 p-1">
                            <div class="card shadow w-100 h-100 p-2" style="border-radius: 15px;">
                                <div class=" py-2 mb-2 text-center" style=" border-radius: 10px; background-color: #002F55;">
                                    <h6 style="color: #ffffffff;" class="mb-0">Cantidad de trámite por tipo de mutación</h6>
                                </div>
                                <canvas id="myBarChart"></canvas>
                            </div>
                        </div>

                        <div class="col-xl-12 p-0">
                            <!-- Dropdown Card Example -->
                            <div class="card shadow my-4 h-100" style="border-radius: 20px;">
                                <div class="card-body p-3 h-100 d-flex flex-column">
                                    <!-- Encabezado -->
                                    <div class="card-header  py-3 d-flex flex-row align-items-center justify-content-center">
                                        <h6 class="m-0 text-center font-weight-bold" style="color: #0F5699;background-color:#ffffff">Distribución por Tipo de Mutación</h6>
                                    </div>
                                    <div class="table-responsive flex-grow-1">
                                        <table class="table table-bordered text-center h-100" id="dataTable" width="100%" cellspacing="0">
                                            <thead style="font-size: 0.7em;">
                                                <tr class="text-center" style="color: #0F5699;">
                                                    <th>Responsable</th>
                                                    <th>Rol</th>
                                                    <th>Asignado</th>
                                                    <th>A tiempo</th>
                                                    <th>A vencer</th>
                                                    <th>Vencido</th>
                                                    <th>Caducado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Inicializamos arreglo para acumular por responsable
                                                $conteos = [];

                                                foreach ($tramites as $tramite) {
                                                    $responsable = $tramite['historial_nombre_usuario'] . ' ' . $tramite['historial_apellido_usuario'];
                                                    $rol = $tramite['historial_rol_usuario'];
                                                    $fecha_limite = $tramite['fecha_limite'];

                                                    // Calculamos días restantes
                                                    $hoy = new DateTime();
                                                    $fechaLimiteObj = new DateTime($fecha_limite);
                                                    $diferencia_dias = (int)$hoy->diff($fechaLimiteObj)->format('%r%a');

                                                    // Inicializamos si no existe
                                                    if (!isset($conteos[$responsable])) {
                                                        $conteos[$responsable] = [
                                                            'rol' => $rol,
                                                            'asignado' => 0,
                                                            'a_tiempo' => 0,
                                                            'a_vencer' => 0,
                                                            'vencido' => 0,
                                                            'caducado' => 0
                                                        ];
                                                    }

                                                    // Incrementamos asignado
                                                    $conteos[$responsable]['asignado']++;

                                                    // Clasificación según días
                                                    if ($diferencia_dias >= 3) {
                                                        $conteos[$responsable]['a_tiempo']++;
                                                    } elseif ($diferencia_dias >= 1 && $diferencia_dias <= 2) {
                                                        $conteos[$responsable]['a_vencer']++;
                                                    } elseif ($diferencia_dias < 0 && abs($diferencia_dias) <= 10) {
                                                        $conteos[$responsable]['vencido']++;
                                                    } elseif (abs($diferencia_dias) > 10) {
                                                        $conteos[$responsable]['caducado']++;
                                                    }
                                                }

                                                // Imprimimos la tabla
                                                foreach ($conteos as $responsable => $data):
                                                ?>
                                                    <tr>
                                                        <td class=" text-center" style="font-size: 0.8em;"><?= htmlspecialchars($responsable) ?></td>
                                                        <td class="text-center" style="font-size: 0.8em;"><?= htmlspecialchars($data['rol']) ?></td>
                                                        <td class="text-center" style="color:blue; font-size: 0.8em;"><?= $data['asignado'] ?></td>
                                                        <td class="text-center" style="color:green; font-size: 0.8em"><?= $data['a_tiempo'] ?></td>
                                                        <td class="text-center" style="color:orange; font-size: 0.8em"><?= $data['a_vencer'] ?></td>
                                                        <td class="text-center" style="color:red; font-size: 0.8em"><?= $data['vencido'] ?></td>
                                                        <td class="text-center" style="color:gray; font-size: 0.8em"><?= $data['caducado'] ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>

                                        </table>
                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- Content Row -->
                    <div class="row">

                        <!-- /.container-fluid -->

                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Arbitrium 2025</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top" style="background-color: #002f55b6;">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="index.php">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="vendor/chart.js/Chart.min.js"></script>

    <!-- Page level custom scripts -->
    <!-- <script src="js/demo/chart-area-demo.js"></script> -->


    <script>
        fetch('tramites/obtener_datos.php')
        .then(response => response.json())
        .then(data => {
            // Gráfico de torta
            var ctxPie = document.getElementById("myPieChart").getContext('2d');
            new Chart(ctxPie, {
                type: 'pie',
                data: {
                    labels: data.torta.labels,
                    datasets: [{
                        data: data.torta.datos,
                        backgroundColor: [
                            '#0d6efd', '#6610f2', '#6f42c1', '#d63384',
                            '#dc3545', '#fd7e14', '#ffc107', '#198754',
                            '#20c997', '#6f42c1', '#17a2b8', '#0dcaf0'
                        ],
                        borderColor: "#ffffff"
                    }]
                },
                options: {
                    responsive: true, // <-- aquí estaba mal escrito
                    plugins: {
                        legend: {
                            display: true,
                            position: 'right',
                            labels: {
                                color: '#333', // en Chart.js v3+ es `color` no `fontColor`
                                font: {
                                    size: 10,
                                    family: 'Poppins, sans-serif'
                                }
                            }
                        }
                    }
                }
            });

            // Gráfico de barra 
            var ctxBar = document.getElementById('myBarChart').getContext('2d');
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: data.barra.labels,
                    datasets: [{
                        label: 'Cantidad de Trámites por tipo de Mutación',
                        data: data.barra.datos,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.5)',
                            'rgba(255, 205, 86, 0.5)',
                            'rgba(75, 192, 192, 0.5)',
                            'rgba(54, 162, 235, 0.5)',
                            'rgba(153, 102, 255, 0.5)',
                        ],
                        borderColor: [
                            'rgb(255, 99, 132)',
                            'rgb(255, 205, 86)',
                            'rgb(75, 192, 192)',
                            'rgb(54, 162, 235)',
                            'rgb(153, 102, 255)'
                        ],
                        borderWidth: 1,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            suggestedMin: 0, // 👈 mejor que min
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }) // <-- cierro el then(data => { ... })
        .catch(error => console.error('Error cargando datos:', error));
    </script>

</body>

</html>