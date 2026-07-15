<?php
require "conexion.php";

session_start();

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

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>DATOS DE GESTIÓN</title>


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <!-- Fondo personalizado para este template: Poppins-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <style>
        body,
        html {
            font-family: 'Poppins' !important;
        }
    </style>

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <!-- bootstrao icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper" class="p-3">

        <!-- Sidebar -->

        <ul class="navbar-nav ms-2 sidebar sidebar-dark accordion shadow-sm border" id="accordionSidebar" style=" border-radius: 15px; background-color: #ffffffff;">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index_graficas.php">
                <div class="sidebar-brand-icon">
                    <img src="imagenes/favicom_03.png" alt="Logo" style="height: 1.5em; ">
                </div>
                <div class="sidebar-brand-text mx-1 " style="color: #0F5699;"> <span style="font-size: 1em;">Conservación</span></div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0" style="background-color: #0f569980;">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item active d-flex justify-content-center align-items-center my-2">
                <a class="nav-link w-80 " style="color: #0F5699;" href="index_graficas.php">
                    <i class="bi bi-house-fill" style="font-size: 1em; color: #0F5699;"></i>
                    <span style="font-size: 1em;">Datos de Gestión</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider" style="background-color: #0f569980;">

            <!-- Heading -->
            <div class="sidebar-heading" style="color: #0F5699;">
                Interface
            </div>

            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item ">
                <a class="nav-link collapsed w-100" href="#" data-toggle="collapse" data-target="#collapseTwo"
                    aria-expanded="true" aria-controls="collapseTwo">
                    <i class="bi-envelope-paper" style="color: #0F5699;"></i>
                    <span style="font-size:1em; color: #0F5699">Tramites</span>
                </a>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h5 class="collapse-header">Acciones Trámite</h5>
                        <a class="collapse-item " href="tramites/creacion_tramite.php">Crear Trámite</a>
                        <!--  <a class="collapse-item" href="../buttons.html">Completar Trámites</a>-->
                        <a class="collapse-item " href="tramites/consultas_tramites.php">Consultar Tramites</a>
                        <!-- <a class="collapse-item" href="../cards.html">Tramites Desestimiento</a>
                        <a class="collapse-item" href="../cards.html">Tramites a Notificar</a>
                        <a class="collapse-item" href="../cards.html">Tramites a Subsanar</a> -->
                    </div>
                </div>
            </li>

            <!-- Nav Item - Utilities Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed w-100 " href="#" data-toggle="collapse" data-target="#collapseUtilities"
                    aria-expanded="true" aria-controls="collapseUtilities">
                    <i class="bi-clipboard-data" style="color: #0F5699;"></i>
                    <span style=" font-size:1em; color: #0F5699;">Seguimiento</span>
                </a>
                <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header fs-1">Asignaciones</h6>
                        <a class="collapse-item " href="tramites/mis_asignaciones/mis_asignaciones.php">
                            Mis Asignaciones</a>
                        <a class="collapse-item" href="tramites/asignacion/asignacion_tramites.php">
                            Estado de Trámite</a>
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
            <div id="content" class=" px-3">

                <!-- Topbar -->
                <nav class="navbar navbar-expand  bg-transparent topbar mb-4 static-top border ">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Search -->
                    <form
                        class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..."
                                aria-label="Search" aria-describedby="basic-addon2">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
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
                                <i class="fas fa-bell fa-fw"></i>
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
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">Douglas McGee</span>
                                <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
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
                        <h3 class=" mb-0 fw-bold" style="color: #0F5699; font-weight: 500 !important">DATOS DE GESTIÓN CATASTRAL</h3>
                        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                                class="fas fa-download fa-sm text-white-50"></i> Generar Reporte</a>
                    </div>

                    <!-- Consultas de cartas-->
                    <div class="row my-3 d-flex justify-content-center align-items-center">

                        <div class="col-lg-5 d-flex justify-content-center aling-items-center">
                            <!-- Consultas -->
                            <div class="row">
                                <div class="col-xl-6 col-md-6  py-2 ">
                                    <div class="card shadow  h-100" style="border-radius: 18px; background-color: #0F5699;">
                                        <div class="card-body p-3 ">
                                            <div class="row">
                                                <div class="col-12 d-flex justify-content-between align-items-center">
                                                    <i class="bi bi-folder text-primary bg-white  p-2" style="border-radius: 12px;"></i>
                                                    <span class="text-white"><i class="bi bi-caret-up-fill text-success"></i>+15%</span>
                                                </div>
                                                <div class="col-12">
                                                    <div class="text-s  text-white text-uppercase my-2">
                                                        Trámites Radicados</div>
                                                    <div class="h5 mb-0 font-weight-bold text-white-800 d-flex justify-content-between align-items-center mx-1"
                                                        style="color: #ffffffff;">
                                                        <?php echo $total_rad; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="col-xl-6 col-md-6 py-3">
                                    <div class="card border-left-primary shadow h-100 py-2" style="border-radius: 15px;">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-12 d-flex justify-content-between align-items-center">
                                                    <i class="bi bi-folder text-primary bg-white  p-2" style="border-radius: 12px;"></i>
                                                    <span class="text-white"><i class="bi bi-caret-up-fill text-success"></i>+15%</span>
                                                </div>
                                                <div class="col-12">
                                                    <div class="text-s  text-white text-uppercase my-2">
                                                        Trámites Asignados</div>
                                                    <div class="h5 mb-0 font-weight-bold text-white-800 d-flex justify-content-between align-items-center mx-1"
                                                        style="color: #0F5699;">
                                                        <?php echo $total_asignaciones; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pending Requests Card Example -->
                                <div class="col-xl-6 col-md-6  py-3">
                                    <div class="card border-left-success shadow h-100 py-2" style="border-radius: 15px;">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                        TOTAL CULMINADOS</div>
                                                    <div class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                                                </div>
                                                <div class="col-auto">
                                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>



                                <!-- Earnings (Monthly) Card Example -->
                                <div class="col-xl-6 col-md-6  py-3">
                                    <div class="card border-left-warning shadow h-100 py-2" style="border-radius: 15px;">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                        TOTAL VENCIDOS</div>
                                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                        <?php echo $total_vencidas; ?>
                                                    </div>

                                                </div>
                                                <div class="col-auto">
                                                    <i class="fas fa-clock fa-2x text-danger"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>


                        <!-- Card de gráfica de torta -->
                        <div class="col-lg-7 ">
                            <div class="shadow rounded-4" style=" border-radius: 15px;">
                                <!-- Cuerpo -->
                                <div class="card-body">
                                    <canvas id="myPieChart"></canvas>
                                </div>
                                <div id="legend-container" class="mt-4 text-center small"></div>
                            </div>
                        </div>

                        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

                        <!-- Card de gráfica de barra -->
                        <div class="col-lg-7">
                            <div class="shadow rounded-4" style="border-radius: 15px;">
                                <!-- Cuerpo -->
                                <div class="card-body">
                                    <canvas id="myBarChart"></canvas>
                                </div>
                                <div id="legend-container" class="mt-4 text-center small"></div>
                            </div>
                        </div>
                       

                    </div>

                    <!-- Content Row -->

                    <div class="row">


                        <div class="col-12">

                            <!-- Dropdown Card Example -->
                            <div class="card shadow mb-4">


                                <div class="card-body">
                                    <!-- Encabezado -->
                                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                        <h6 class="m-0 font-weight-bold text-primary">Distribución por Tipo de Mutación</h6>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
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
                                                        <td><?= htmlspecialchars($responsable) ?></td>
                                                        <td class="text-center"><?= htmlspecialchars($data['rol']) ?></td>
                                                        <td class="text-center"><?= $data['asignado'] ?></td>
                                                        <td class="text-center" style="color:green"><?= $data['a_tiempo'] ?></td>
                                                        <td class="text-center" style="color:orange"><?= $data['a_vencer'] ?></td>
                                                        <td class="text-center" style="color:red"><?= $data['vencido'] ?></td>
                                                        <td class="text-center" style="color:gray"><?= $data['caducado'] ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>

                                        </table>
                                    </div>
                                </div>

                            </div>

                        </div>
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
                        <span>Copyright &copy; Your Website 2021</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
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
    <script src="js/demo/chart-area-demo.js"></script>

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
                                '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e',
                                '#e74a3b', '#858796', '#20c997', '#6610f2',
                                '#fd7e14', '#6f42c1', '#17a2b8', '#ffc107'
                            ],
                            borderColor: "#ffffff"
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
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
                                'rgba(255, 99, 132, 0.2)',
                                'rgba(255, 159, 64, 0.2)',
                                'rgba(255, 205, 86, 0.2)',
                                'rgba(75, 192, 192, 0.2)',
                                'rgba(54, 162, 235, 0.2)',
                                'rgba(153, 102, 255, 0.2)',
                                'rgba(201, 203, 207, 0.2)'
                            ],
                            borderColor: [
                                'rgb(255, 99, 132)',
                                'rgb(255, 159, 64)',
                                'rgb(255, 205, 86)',
                                'rgb(75, 192, 192)',
                                'rgb(54, 162, 235)',
                                'rgb(153, 102, 255)',
                                'rgb(201, 203, 207)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            });
    </script>

</body>

</html>