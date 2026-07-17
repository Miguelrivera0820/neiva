<?php
// require "../conexion.php";

// session_start();
require_once __DIR__ . '/../prod_catastrales/funciones_productos.php';

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

$filtroSoloCertificados = '';
if (isset($mysqli) && $mysqli instanceof mysqli && asegurarColumnaProductosCatastrales($mysqli)) {
    $filtroSoloCertificados = "WHERE prod_tipo_producto IS NULL
           OR TRIM(prod_tipo_producto) = ''";
}

$sql2 = "SELECT 
            certificado_hora_creacion,
            codigo_certificado,
            CONCAT(cert_primer_nombre_interesado, ' ', cert_segundo_nombre_interesado, ' ', cert_primer_apellido_interesado, ' ', cert_segundo_apellido_interesado) AS nombre_solicitante,
            cert_soporte_pago
        FROM certificado_catastral
        $filtroSoloCertificados
        ORDER BY certificado_hora_creacion DESC";

$resultado2 = $mysqli->query($sql2);
?>

<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->

<!-- DataTables CSS con integración Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

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

    .estado-pago-certificado {
        gap: .5rem;
        min-width: 155px;
    }

    .estado-pago-certificado .badge {
        border-radius: 8px;
        font-size: .76rem;
        padding: .55rem .65rem;
        text-transform: uppercase;
        width: 100%;
    }

    .estado-pago-certificado .btn {
        font-size: .78rem;
        font-weight: 600;
        white-space: nowrap;
    }

</style>

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h2 mb-0 text-gray-1200"><B>TRAMITES CATASTRALES NEIVA</B></h1>
    </div>

    <a href="index.php?page=cert_catastrales/generar_cert_catastrales" class="btn text-white" style="background-color: #002F55;">
        <i class="bi bi-file-earmark-plus"></i> <b>Solicitar Certificado</b>
    </a>
    <br>
    <br>

    <div class="row">

        <div class="col-lg-12">

            <!-- Dropdown Card Example -->
            <div class="card shadow mb-4">
                <!-- Card Header - Dropdown -->
                <div
                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between" style="background-color: #002F55;">
                    <h6 class="m-0 font-weight-bold text-white">Información de Trámites</h6>
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
                                    <th>Fecha de Radicacion</th>
                                    <th>Codigo Certificado</th>
                                    <th>Nombre Solicitante</th>
                                    <th>Estado de Pago</th>
                                    <th>Impresion</th>
                                    <th>Firma</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $resultado2->fetch_assoc()) { ?>
                                    <?php
                                    $codigoCertificado = trim((string) ($row['codigo_certificado'] ?? ''));
                                    $codigoCertificadoSeguro = preg_replace('/[^A-Za-z0-9_-]/', '', $codigoCertificado);
                                    $directorioCertificado = dirname(__DIR__, 3)
                                        . DIRECTORY_SEPARATOR . 'resoluciones'
                                        . DIRECTORY_SEPARATOR . $codigoCertificadoSeguro;
                                    $certificadoFirmado = $codigoCertificadoSeguro !== ''
                                        && $codigoCertificadoSeguro === $codigoCertificado
                                        && is_file($directorioCertificado . DIRECTORY_SEPARATOR . 'certificado.pdf')
                                        && is_file($directorioCertificado . DIRECTORY_SEPARATOR . 'firmado.flag');
                                    $soportePago = trim((string) ($row['cert_soporte_pago'] ?? ''));
                                    $rutaSoporte = str_replace('\\', '/', $soportePago);
                                    $rutaSoporteValida = $rutaSoporte !== ''
                                        && strpos($rutaSoporte, 'soportes_pago/') === 0
                                        && strpos($rutaSoporte, '..') === false
                                        && strpos($rutaSoporte, "\0") === false;
                                    $urlGestionPago = 'index.php?page=cert_catastrales/gestionar_pago_certificado'
                                        . '&codigo=' . rawurlencode($codigoCertificado);
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars((string) $row['certificado_hora_creacion'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>
                                            <a href="index.php?page=cert_catastrales/acciones/ver_datos_certificado&codigo_certificado=<?= rawurlencode($codigoCertificado) ?>">
                                                <?= htmlspecialchars($codigoCertificado, ENT_QUOTES, 'UTF-8') ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars(trim((string) $row['nombre_solicitante']), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>
                                            <div class="estado-pago-certificado d-flex flex-column align-items-stretch">
                                                <?php if ($soportePago !== ''): ?>
                                                    <span class="badge badge-success d-flex justify-content-center">Pagado</span>
                                                    <?php if ($rutaSoporteValida): ?>
                                                        <a
                                                            href="<?= htmlspecialchars($rutaSoporte, ENT_QUOTES, 'UTF-8') ?>"
                                                            class="btn btn-sm btn-outline-success"
                                                            target="_blank"
                                                            rel="noopener noreferrer">
                                                            <i class="bi bi-file-earmark-pdf me-1"></i>Ver soporte
                                                        </a>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge badge-danger d-flex justify-content-center">Pendiente</span>
                                                    <a href="<?= htmlspecialchars($urlGestionPago, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-receipt me-1"></i>Subir soporte de pago
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>

                                        <td class="text-center align-middle">
                                            <a href="vistas/cert_catastrales/acciones/imprimir_certificado.php?codigo_certificado=<?= rawurlencode($codigoCertificado) ?>"
                                                class="btn btn-sm text-white" style="background-color:#002F55"
                                                target="_blank">
                                                Imprimir
                                            </a>
                                        </td>
                                        <td class="text-center align-middle">
                                            <?php if (!$certificadoFirmado): ?>
                                                <a
                                                    href="vistas/cert_catastrales/acciones/imprimir_certificado.php?codigo_certificado=<?= rawurlencode($codigoCertificado) ?>&amp;firmar=1"
                                                    class="btn btn-sm btn-secondary d-flex justify-content-center btn-firmar-certificado"
                                                    target="_blank"
                                                    rel="noopener noreferrer">
                                                    Firmar
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php } ?>
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
                    language: {
                        url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                    }
                });

                $('.btn-firmar-certificado').on('click', function() {
                    sessionStorage.setItem('certificadoFirmaIniciada', String(Date.now()));
                });
            });

            window.addEventListener('focus', function() {
                const firmaIniciada = Number(sessionStorage.getItem('certificadoFirmaIniciada') || 0);
                if (firmaIniciada > 0 && Date.now() - firmaIniciada >= 1000) {
                    sessionStorage.removeItem('certificadoFirmaIniciada');
                    window.location.reload();
                }
            });
        </script>

    </div>
    <!-- End of Main Content -->

</div>
