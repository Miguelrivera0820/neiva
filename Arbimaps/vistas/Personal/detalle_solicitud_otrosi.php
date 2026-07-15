<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../bienvenida.php");
    exit;
}

$idUsuario  = $_SESSION['id_usuario'];
$rolUsuario = $_SESSION['rol_usuario'] ?? '';

$solicitudId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($solicitudId <= 0) {
?>
    <div class="container-fluid mt-3">
        <div class="alert alert-danger">
            Solicitud no válida.
        </div>
        <a href="index.php?page=Personal/mis_notificaciones"
            class="btn btn-sm btn-outline-primary">
            <i class="bi bi-arrow-left-short"></i> Volver a mis notificaciones
        </a>
    </div>
<?php
    return;
}

$rolesQuePuedenCargar = ['administrador', 'soporte', 'soporte_nivel1', 'director_catastro', 'usuarios_ops', 'reconocedor'];
$puedeCargarArchivo = in_array($rolUsuario, $rolesQuePuedenCargar, true);


if ($puedeCargarArchivo) {
    $sqlDetalle = "
        SELECT o.*, u.nombre_usuario, u.apellido_usuario, u.usuario_cons
        FROM solicitudes_otrosi o
        LEFT JOIN usuarios_cons u ON o.sol_usuario_id = u.id_usuario
        WHERE o.id = ?
        LIMIT 1
    ";
} else {
    $sqlDetalle = "
        SELECT o.*, u.nombre_usuario, u.apellido_usuario, u.usuario_cons
        FROM solicitudes_otrosi o
        LEFT JOIN usuarios_cons u ON o.sol_usuario_id = u.id_usuario
        WHERE o.id = ? AND o.sol_usuario_id = ?
        LIMIT 1
    ";
}

$solicitud = null;

if ($stmt = $mysqli->prepare($sqlDetalle)) {
    if ($puedeCargarArchivo) {
        $stmt->bind_param("i", $solicitudId);
    } else {
        $stmt->bind_param("ii", $solicitudId, $idUsuario);
    }

    $stmt->execute();
    $result    = $stmt->get_result();
    $solicitud = $result->fetch_assoc();
    $stmt->close();
}

if (!$solicitud) {
?>
    <div class="container-fluid mt-3">
        <div class="alert alert-warning">
            No se encontró la solicitud o no tienes permisos para verla.
        </div>
        <a href="index.php?page=Personal/mis_notificaciones"
            class="btn btn-sm btn-outline-primary">
            <i class="bi bi-arrow-left-short"></i> Volver a mis notificaciones
        </a>
    </div>
<?php
    return;
}

$cedula = trim((string)($solicitud['con_num_identidad'] ?? ''));
$historialDocs = [];
$yaHayNotifFirmada = false;

if ($cedula !== '') {
    $carpetaCedulaFS = rtrim($_SERVER['DOCUMENT_ROOT'], '/') .
        neiva_app_url('Arbimaps/vistas/Personal/Arbitrium_otrosi_Nuevo/notificacion_firmada/') .
        $cedula . '/';

    $carpetaSolicitudFS = $carpetaCedulaFS . $solicitud['id'] . '/';
    $urlNotifBase = neiva_app_url('Arbimaps/vistas/Personal/Arbitrium_otrosi_Nuevo/notificacion_firmada/');

    if (is_dir($carpetaSolicitudFS)) {
        $archivos = glob($carpetaSolicitudFS . '*.pdf');
        $yaHayNotifFirmada = !empty($archivos);
        if ($yaHayNotifFirmada) {
            foreach ($archivos as $rutaCompleta) {
                $file = basename($rutaCompleta);
                $mtime = filemtime($rutaCompleta);
                $historialDocs[] = [
                    'nombre'    => $file,
                    'fecha_raw' => $mtime,
                    'fecha'     => date('d/m/Y H:i', $mtime),
                    'url'       => $urlNotifBase . $cedula . '/' . $solicitud['id'] . '/' . rawurlencode($file),
                ];
            }
            usort($historialDocs, function ($a, $b) {
                return $b['fecha_raw'] <=> $a['fecha_raw'];
            });
        }
    }
}

$swalAprobadoMsg = '';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!empty($_SESSION['swal_aprobado_ok'])) {
    $swalAprobadoMsg = $_SESSION['swal_aprobado_msg'] ?? 'La notificación se aprobó correctamente.';
    unset($_SESSION['swal_aprobado_ok'], $_SESSION['swal_aprobado_msg']);
}
$swalRechazadoMsg = '';
if (!empty($_SESSION['swal_rechazado_ok'])) {
    $swalRechazadoMsg = $_SESSION['swal_rechazado_msg'] ?? 'La notificación se marcó como RECHAZADA.';
    unset($_SESSION['swal_rechazado_ok'], $_SESSION['swal_rechazado_msg']);
}

?>

<style>
    .btn-back {
        display: inline-flex;
        align-items: center;
        overflow: hidden;
        padding: 0.25rem 0.6rem;
        transition: padding 0.3s ease;
        border: 1px solid #002f55;
        color: #002F55 !important;
    }

    .btn-back:hover {
        background-color: #002f55 !important;
        color: white !important;
    }

    .btn-back:hover i {
        color: #ffffff;
    }

    /* Texto oculto inicialmente */
    .btn-back .btn-text {
        max-width: 0;
        opacity: 0;
        white-space: nowrap;
        overflow: hidden;
        margin-left: 0;
        transition:
            max-width 0.45s ease,
            opacity 0.35s ease,
            margin-left 0.45s ease;
    }

    /* Hover: se despliega hacia la izquierda */
    .btn-back:hover .btn-text {
        max-width: 200px;
        opacity: 1;
        margin-left: 0.4rem;
    }

    /* Ajuste fino del ícono */
    .btn-back i {
        font-size: 1.2rem;
    }
</style>


<div class="container-fluid mt-3 p-3 ">
    <div class="row">
        <div class="col-12 card shadow brder p-3">
            <div class="container-fluid ">
                <div class="row">
                    <!-- sección de lado izquierdo -->
                    <div class="col-8  p-2">
                        <div class="container-fluid alig-items-center justify-content-center">
                            <div class="row justify-content-center align-items-center">
                                <div class="col-12 d-flex justify-content-between align-items-center my-3 px-4 pb-3" style="border-bottom: 2px dashed #02305569;">
                                    <div>
                                        <h5 class="mb-0 fw-bold" style="color:#002F55 !important;">
                                            Solicitud de Otrosi #<?php echo $solicitud['id']; ?>
                                        </h5>
                                        <small class="opacity-75">
                                            Generada el
                                            <?php echo date('d/m/Y H:i', strtotime($solicitud['sol_fecha_solicitud'] ?? $solicitud['fecha'] ?? 'now')); ?>
                                        </small>
                                    </div>
                                    <div>

                                        <a href="index.php?page=Personal/mis_notificaciones"
                                            class="btn btn-sm btn-light rounded-pill btn-back">
                                            <i class="bi bi-bell"></i>
                                            <span class="btn-text">Ir a notificaciones</span>
                                        </a>

                                        <a href="index.php?page=dashboardcopy"
                                            class="btn btn-sm btn-outline-light rounded-pill btn-back" style="border: 1px solid #002f55;">
                                            <i class="bi bi-house"></i>
                                        </a>
                                    </div>
                                </div>

                                <div class="col-11 mt-2">
                                    <div class="rounded-4 p-4 px-5 d-flex justify-content-between " style="background-color:#002f55 !important; color:white;">
                                        <div class="d-flex flex-column justify-content-center align-items-center">
                                            <h5 class="my- fw-bold" style="align-items:center !important">Usuario que realizó la solicitud</h5>
                                            <div class=" d-flex flex-column align-items-start">
                                                <small style="color: #afbdc8;">Nombre completo:</small>
                                                <h6>
                                                    <?php echo htmlspecialchars(($solicitud['nombre_usuario'] ?? '') . ' ' . ($solicitud['apellido_usuario'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                </h6>

                                                <small style="color: #afbdc8;">Rol:</small>
                                                <span>
                                                    <?php echo htmlspecialchars($solicitud['usuario_cons'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </div>

                                        </div>

                                        <img src="..<?= neiva_app_url('Arbimaps/assets/img/jefe.png') ?>" style="width: 10em; height:12em" alt="imagen de jefe">

                                    </div>
                                </div>

                                <div class="col-12 mt-3 px-0">
                                    <div class="container-fluid">
                                        <div class="row justify-content-center">
                                            <?php if (!empty($solicitud['sol_archivo_otrosi'])): ?>
                                                <?php
                                                $carpetaBase = neiva_app_url('Arbimaps/vistas/Personal/Arbitrium_otrosi_Nuevo/documentos_firmados/');
                                                $cedula      = $solicitud['con_num_identidad'];
                                                $archivoPdf  = $solicitud['sol_archivo_otrosi'];
                                                $rutaPdf     = $carpetaBase . $cedula . '/' . $archivoPdf;
                                                ?>
                                                <div class="col-5 mt-3 px-0 ">
                                                    <div class="position-relative h-100">

                                                        <!-- Icono flotante -->
                                                        <div class="position-absolute top-0 start-50 translate-middle" style="z-index:99">
                                                            <div class="rounded-5 shadow d-flex align-items-center justify-content-center"
                                                                style="width:45px; height:45px; background-color:#002f55 !important;">
                                                                <i class="bi bi-file-earmark-pdf text-white fs-4"></i>
                                                            </div>
                                                        </div>

                                                        <!-- Card -->
                                                        <div class="card-especial d-flex flex-column justify-content-center shadow rounded-4 pt-4 p-3 h-100 text-center" style="border: 1px solid #002f559e;">
                                                            <h6 class="fw-semibold mb-1 mt-2" style="color:#002F55;">
                                                                Archivo adjunto
                                                            </h6>

                                                            <small class="text-muted d-block text-truncate mb-3"
                                                                style="max-width: 100%;">
                                                                <?php echo htmlspecialchars($archivoPdf, ENT_QUOTES, 'UTF-8'); ?>
                                                            </small>

                                                            <div class="d-flex gap-2 px-0">
                                                                <a href="<?php echo $rutaPdf; ?>"
                                                                    target="_blank"
                                                                    class="btn btn-sm rounded-pill w-50" style="background-color:#002f55 !important; color:white;">
                                                                    <i class="bi bi-eye me-1"></i> Ver
                                                                </a>

                                                                <a href="<?php echo $rutaPdf; ?>"
                                                                    download
                                                                    class="btn btn-sm  rounded-pill w-50" style="border: 1px solid #002f55 !important; color:white; color:#002F55 !important;">
                                                                    <i class="bi bi-download me-1"></i> Descargar
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            <?php endif; ?>

                                            <?php if (!empty($historialDocs)): ?>

                                                <div class="col-7 mt-3 ">
                                                    <div class="position-relative h-100">

                                                        <!-- Icono flotante -->
                                                        <div class="position-absolute top-0 start-50 translate-middle" style="z-index:99">
                                                            <div class="rounded-5 shadow d-flex align-items-center justify-content-center"
                                                                style="width:45px; height:45px; background-color:#002f55 !important;">
                                                                <i class="bi bi-clock-history text-white fs-4"></i>
                                                            </div>
                                                        </div>

                                                        <!-- Card -->
                                                        <div class="card-especial shadow  rounded-4 pt-4 p-3 h-100 text-center align-items-center" style="border: 1px solid #002f559e;">
                                                            <h6 class="fw-semibold mb-1 mt-2" style="color:#002F55;">
                                                                Historial de notificaciones firmadas
                                                            </h6>

                                                            <?php foreach ($historialDocs as $doc): ?>

                                                                <small class="text-muted d-block text-truncate my-2"
                                                                    style="max-width: 100%;">
                                                                    <?php echo htmlspecialchars($doc['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                                                </small>

                                                                <small class="text-muted d-block text-truncate mb-3"
                                                                    style="max-width: 100%;"> <strong>Fecha de carga:</strong>
                                                                    <?php echo htmlspecialchars($doc['fecha'], ENT_QUOTES, 'UTF-8'); ?>
                                                                </small>

                                                                <div class="d-flex gap-2 px-4">
                                                                    <a href="<?php echo $doc['url']; ?>"
                                                                        target="_blank"
                                                                        class="btn btn-sm rounded-pill w-50" style="background-color:#002f55 !important; color:white;">
                                                                        <i class="bi bi-eye me-1"></i> Ver
                                                                    </a>

                                                                    <a href="<?php echo $doc['url']; ?>"
                                                                        download
                                                                        class="btn btn-sm  rounded-pill w-50" style="border: 1px solid #002f55 !important; color:white; color:#002F55 !important;">
                                                                        <i class="bi bi-download me-1"></i> Descargar
                                                                    </a>
                                                                </div>

                                                            <?php endforeach; ?>

                                                        </div>
                                                    </div>
                                                </div>

                                            <?php endif; ?>

                                            <?php if ($puedeCargarArchivo): ?>
                                                <?php if ($yaHayNotifFirmada): ?>
                                                    <div class="col-10 mt-3 text-center">
                                                        <div class="alert alert-success mb-0 py-2 px-3">
                                                            <i class="bi bi-check-circle-fill me-2"></i>
                                                            <strong>La notificación firmada ya fue subida</strong>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="col-7 mt-3 ">
                                                        <div class="position-relative h-100">

                                                            <!-- Icono flotante -->
                                                            <div class="position-absolute top-0 start-50 translate-middle" style="z-index:99">
                                                                <div class="rounded-5 shadow d-flex align-items-center justify-content-center"
                                                                    style="width:45px; height:45px; background-color:#002f55 !important;">
                                                                    <i class="bi bi-file-earmark-arrow-up text-white fs-4"></i>
                                                                </div>
                                                            </div>

                                                            <!-- Card -->
                                                            <div class="card-especial d-flex flex-column justify-content-center shadow rounded-4 pt-4 p-3 h-100 text-center" style="border: 1px solid #002f559e;">
                                                                <h6 class="fw-semibold mb-2 mt-2" style="color:#002F55;">
                                                                    Cargar documento de otro si firmado
                                                                </h6>

                                                                <form action="<?= neiva_app_url('Arbimaps/vistas/Personal/acciones/otrosi_subir_archivo.php') ?>"
                                                                    method="post" enctype="multipart/form-data" class="row g-3">
                                                                    <input type="hidden" name="solicitud_id" value="<?php echo (int)$solicitud['id']; ?>">
                                                                    <input type="hidden" name="usuario_id" value="<?php echo (int)$idUsuario; ?>">

                                                                    <div class=" col-12 ">

                                                                        <div class="input-group rounded-2">
                                                                            <label class="input-group-text" for="doc" style="font-size:0.9em; color:#002F55"><i class="bi bi-file-earmark-pdf-fill"></i></label>
                                                                            <input type="file" name="archivo_otrosi" class="form-control " style="font-size:0.9em"
                                                                                accept="application/pdf" required>
                                                                        </div>
                                                                        <div class="form-text">Solo se permiten archivos PDF.</div>
                                                                    </div>

                                                                    <div class="col-12 d-flex align-items-end px-3">
                                                                        <button type="submit" class="btn btn-sm btn-success rounded-pill w-100 ">
                                                                            <i class="bi bi-cloud-upload me-1"></i> Cargar documento
                                                                        </button>
                                                                    </div>

                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- sección del lado derecho  -->
                    <div class="col-4 px-0 ">
                        <div class="card h-100 p-4 shadow border-0 text-white alig-items-center justify-content-center"
                            style="background-color: #002f55; border-radius: 16px;">

                            <!-- Header: título + badge -->
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div>
                                    <h5 class="mb-3 fw-semibold text-center">Información</h5>

                                </div>

                                <!-- Badge (ej: estado/prioridad) -->
                                <span class="badge rounded-pill"
                                    style="background: rgba(255, 102, 102, .15); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.35);">
                                    <i class="bi bi-file-earmark-pdf fs-3 "></i>
                                </span>
                            </div>

                            <!-- Motivo: texto principal (como el párrafo grande de la imagen) -->
                            <h6 class="mb-2 fw-semibold text-white-50 text-start">Motivo de la solicitud:</h6>
                            <p class="mb-3 text-white-75 text-center" style="line-height: 1.35;">
                                <?php echo htmlspecialchars($solicitud['sol_motivo'] ?? 'Motivo de la solicitud no registrado.', ENT_QUOTES, 'UTF-8'); ?>
                            </p>

                            <!-- Footer: “History of Design” + meta con íconos + avatares -->
                            <div class="d-flex align-items-center justify-content-between pt-2"
                                style="border-top: 1px solid rgba(255,255,255,.10);">

                                <!-- Bloque tipo "History of Design" -->
                                <div class="d-flex flex-column gap-1">
                                    <div class="d-flex align-items-center gap-2 mb-0">
                                        <i class="bi bi-clock-history"></i>
                                        <span class="fw-semibold">Detalles</span>
                                    </div>


                                    <!-- Sección opcional: “grid” de datos (similar a tu layout original pero más limpio) -->
                                    <div class="row my-2 g-2 align-items-center justify-content-center">

                                        <div class="col-6">
                                            <div class="p-2 rounded-3 shadow" style="background: rgba(255, 255, 255, 0.12);">
                                                <small class="text-white-50 d-block">Tipo de otro si</small>
                                                <div class="fw-semibold">
                                                    <?php echo htmlspecialchars($solicitud['sol_tipo_otrosi'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <div class="p-2 rounded-3" style="background: rgba(255, 255, 255, 0.12);">
                                                <small class="text-white-50 d-block">Nuevo rol</small>
                                                <div class="fw-semibold">
                                                    <?php echo htmlspecialchars($solicitud['sol_nuevo_rol'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <div class="p-2 rounded-3" style="background: rgba(255, 255, 255, 0.12);">
                                                <small class="text-white-50 d-block">Nuevo proyecto</small>
                                                <div class="fw-semibold">
                                                    <?php echo htmlspecialchars($solicitud['sol_nuevo_proyecto'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <div class="p-2 rounded-3" style="background: rgba(255, 255, 255, 0.12);">
                                                <small class="text-white-50 d-block">Nuevo salario</small>
                                                <div class="fw-semibold">
                                                    $ <?php echo number_format((float)($solicitud['sol_nuevo_salario'] ?? 0), 0, ',', '.'); ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-8">
                                            <div class="p-2 rounded-3" style="background: rgba(255, 255, 255, 0.12);">
                                                <small class="text-white-50 d-block">Valor OTROSI</small>
                                                <div class="fw-semibold">
                                                    $ <?php echo number_format((float)($solicitud['sol_valor_otrosi'] ?? 0), 0, ',', '.'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Meta items con íconos (compacto como en la imagen) -->
                                    <div class="d-flex mt-2 flex-wrap gap-3 text-white-50 small align-items-center justify-content-center">
                                        <span class="d-inline-flex align-items-center gap-2">
                                            <i class="bi bi-calendar-event"></i>
                                            <span>
                                                <span class="text-white-50">Inicio:</span>
                                                <?php echo htmlspecialchars($solicitud['sol_fecha_inicio'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </span>

                                        <span class="d-inline-flex align-items-center gap-2">
                                            <i class="bi bi-calendar-check"></i>
                                            <span>
                                                <span class="text-white-50">Nueva final:</span>
                                                <?php echo htmlspecialchars($solicitud['sol_nueva_fecha_final'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </span>

                                        <span class="d-inline-flex align-items-center gap-2">
                                            <i class="bi bi-hourglass-split"></i>
                                            <span>
                                                <span class="text-white-50">Duración:</span>
                                                <?php echo htmlspecialchars($solicitud['sol_duracion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- consulta para conocer el estado de la solicitud por parte del colaboradorl -->

                            <?php $estadoUsuario = strtoupper(trim($solicitud['sol_estado_usuario'] ?? '')); ?>

                            <!-- <script>
                                console.log("estadoUsuario:", <?php echo json_encode($estadoUsuario); ?>);
                            </script> -->

                            <!-- Sección de botones para aprobar o rechazar -->
                            <?php if ($estadoUsuario === 'PENDIENTE'): ?>

                                <!-- BOTONES -->
                                <div class="rounded-3 p-3 pb-0 d-flex justify-content-between align-items-center mt-3">
                                    <form id="form-aprobar"
                                        method="post"
                                        action="<?= neiva_app_url('Arbimaps/vistas/Personal/acciones/aprobar_notificacion.php') ?>"
                                        class="w-100 px-2">

                                        <input type="hidden" name="id" value="<?php echo (int)$solicitud['id']; ?>">
                                        <input type="hidden" name="numero_identidad"
                                            value="<?php echo htmlspecialchars($solicitud['con_num_identidad'] ?? ''); ?>">

                                        <button type="submit" class="btn btn-success w-100">
                                            Aprobar
                                        </button>
                                    </form>

                                    <form id="form-rechazar"
                                        method="post"
                                        action="<?= neiva_app_url('Arbimaps/vistas/Personal/acciones/rechazo_notificacion.php') ?>"
                                        class="w-100 px-2">

                                        <input type="hidden" name="id" value="<?php echo (int)$solicitud['id']; ?>">
                                        <input type="hidden" name="numero_identidad"
                                            value="<?php echo htmlspecialchars($solicitud['con_num_identidad'] ?? ''); ?>">

                                        <button type="submit" class="btn btn-danger w-100">
                                            Rechazar
                                        </button>
                                    </form>
                                </div>

                            <?php elseif ($estadoUsuario === 'RECHAZADO'): ?>

                                <!-- BANNER RECHAZADO -->
                                <div class="alert alert-danger rounded-5 mt-3 text-center d-flex g-2 justify-content-center align-items-center">
                                    <div class="p-1 bg-white rounded-5 me-2 border border-danger">
                                        <i class="bi bi-hand-thumbs-down px-1 "></i>
                                    </div>
                                    <strong>Rechazaste esta solicitud</strong>
                                </div>

                            <?php elseif ($estadoUsuario === 'ACEPTADO'): ?>

                                <!-- BANNER APROBADO -->
                                <div class="alert alert-success rounded-5 mt-3 text-center d-flex g-2 justify-content-center align-items-center">
                                    <div class="p-1 bg-white rounded-5 me-2 border border-success">
                                        <i class="bi bi-hand-thumbs-up px-1 "></i>
                                    </div>
                                    <strong>Aprobaste esta solicitud</strong>
                                </div>

                            <?php endif; ?>


                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


<!-- <div class="container-fluid mt-2">
    <div class="row">
        <div class="col-12 col-xxl-10 mx-auto">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header d-flex justify-content-between align-items-center rounded-4"
                    style="background: linear-gradient(135deg, #0F5699 0%, #002F55 100%); color:white;">
                    <div>
                        <h6 class="mb-0">
                            Detalle solicitud de OTROSI Mí perrito #<?php echo $solicitud['id']; ?>
                        </h6>
                        <small class="opacity-75">
                            Registrada el
                            <?php echo date('d/m/Y H:i', strtotime($solicitud['sol_fecha_solicitud'] ?? $solicitud['fecha'] ?? 'now')); ?>
                            por
                            <?php echo htmlspecialchars(($solicitud['nombre_usuario'] ?? '') . ' ' . ($solicitud['apellido_usuario'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        </small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="index.php?page=Personal/mis_notificaciones"
                            class="btn btn-sm btn-light rounded-pill">
                            <i class="bi bi-bell"></i> Mis notificaciones
                        </a>
                        <a href="index.php?page=dashboardcopy"
                            class="btn btn-sm btn-outline-light rounded-pill">
                            <i class="bi bi-house"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-lg-6">
                            <div class="border rounded-3 p-3 h-100">
                                <h6 class="fw-semibold mb-3" style="color:#002F55;">
                                    <i class="bi bi-person-badge me-2"></i> Datos del usuario
                                </h6>
                                <div class="mb-2">
                                    <small class="text-muted d-block">Usuario</small>
                                    <span class="fw-semibold">
                                        <?php echo htmlspecialchars($solicitud['usuario_cons'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted d-block">Nombre completo</small>
                                    <span>
                                        <?php echo htmlspecialchars(($solicitud['nombre_usuario'] ?? '') . ' ' . ($solicitud['apellido_usuario'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted d-block">Documento</small>
                                    <span>
                                        <?php echo htmlspecialchars($solicitud['con_num_identidad'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="border rounded-3 p-3 h-100">
                                <h6 class="fw-semibold mb-3" style="color:#002F55;">
                                    <i class="bi bi-file-text me-2"></i> Información del OTROSI
                                </h6>
                                <div class="row">
                                    <div class="col-12 mb-2">
                                        <small class="text-muted d-block">Tipo de OTROSI</small>
                                        <span class="fw-semibold">
                                            <?php echo htmlspecialchars($solicitud['sol_tipo_otrosi'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <small class="text-muted d-block">Fecha inicio</small>
                                        <span>
                                            <?php echo htmlspecialchars($solicitud['sol_fecha_inicio'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <small class="text-muted d-block">Nueva fecha final</small>
                                        <span>
                                            <?php echo htmlspecialchars($solicitud['sol_nueva_fecha_final'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <small class="text-muted d-block">Nuevo rol</small>
                                        <span>
                                            <?php echo htmlspecialchars($solicitud['sol_nuevo_rol'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <small class="text-muted d-block">Nuevo proyecto</small>
                                        <span>
                                            <?php echo htmlspecialchars($solicitud['sol_nuevo_proyecto'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <small class="text-muted d-block">Nuevo salario</small>
                                        <span>
                                            $ <?php echo number_format((float)($solicitud['sol_nuevo_salario'] ?? 0), 0, ',', '.'); ?>
                                        </span>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <small class="text-muted d-block">Valor OTROSI</small>
                                        <span>
                                            $ <?php echo number_format((float)($solicitud['sol_valor_otrosi'] ?? 0), 0, ',', '.'); ?>
                                        </span>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <small class="text-muted d-block">Duración</small>
                                        <span>
                                            <?php echo htmlspecialchars($solicitud['sol_duracion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="border rounded-3 p-3">
                                <h6 class="fw-semibold mb-3" style="color:#002F55;">
                                    <i class="bi bi-info-circle me-2"></i> Motivo y estados
                                </h6>
                                <div class="row g-3">
                                    <div class="col-12 col-lg-6">
                                        <small class="text-muted d-block">Motivo de la solicitud</small>
                                        <p class="mb-0" style="font-size:0.85rem;">
                                            <?php echo nl2br(htmlspecialchars($solicitud['sol_motivo'] ?? '', ENT_QUOTES, 'UTF-8')); ?>
                                        </p>
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <div class="row">
                                            <div class="col-6 mb-2">
                                                <small class="text-muted d-block">Estado general</small>
                                                <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis">
                                                    <?php echo htmlspecialchars($solicitud['sol_estado'] ?? 'Sin estado', ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </div>
                                            <div class="col-6 mb-2">
                                                <small class="text-muted d-block">Gerencia</small>
                                                <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">
                                                    <?php echo htmlspecialchars($solicitud['sol_estado_gerencia'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </div>
                                            <div class="col-6 mb-2">
                                                <small class="text-muted d-block">Estado usuario</small>
                                                <span class="badge rounded-pill bg-info-subtle text-info-emphasis">
                                                    <?php echo htmlspecialchars($solicitud['sol_estado_usuario'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </div>
                                            <div class="col-6 mb-2">
                                                <small class="text-muted d-block">Estado cargado</small>
                                                <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis">
                                                    <?php echo htmlspecialchars($solicitud['sol_estado_cargado'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if (!empty($solicitud['sol_motivo_devolucion'])): ?>
                                        <div class="col-12">
                                            <div class="alert alert-warning mb-0 py-2 px-3">
                                                <small class="text-muted d-block mb-1">
                                                    Motivo de devolución
                                                </small>
                                                <span style="font-size:0.82rem;">
                                                    <?php echo nl2br(htmlspecialchars($solicitud['sol_motivo_devolucion'], ENT_QUOTES, 'UTF-8')); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($solicitud['sol_motivo_rechazo'])): ?>
                                        <div class="col-12">
                                            <div class="alert alert-danger mb-0 py-2 px-3">
                                                <small class="text-muted d-block mb-1 text-uppercase">
                                                    Motivo de rechazo
                                                </small>
                                                <span style="font-size:0.82rem;">
                                                    <?php echo nl2br(htmlspecialchars($solicitud['sol_motivo_rechazo'], ENT_QUOTES, 'UTF-8')); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($solicitud['sol_archivo_otrosi'])): ?>
                            <?php
                            $carpetaBase = neiva_app_url('Arbimaps/vistas/Personal/Arbitrium_otrosi_Nuevo/documentos_firmados/');
                            $cedula      = $solicitud['con_num_identidad'];
                            $archivoPdf  = $solicitud['sol_archivo_otrosi'];
                            $rutaPdf     = $carpetaBase . $cedula . '/' . $archivoPdf;
                            ?>
                            <div class="col-12">
                                <div class="border rounded-3 p-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                                    <div>
                                        <h6 class="fw-semibold mb-1" style="color:#002F55;">
                                            <i class="bi bi-paperclip me-2"></i> Archivo adjunto
                                        </h6>
                                        <small class="text-muted">
                                            Se adjuntó un documento para esta solicitud.
                                        </small>
                                        <div class="mt-1">
                                            <small class="text-muted" style="font-size: 0.75rem;">
                                                <?php echo htmlspecialchars($archivoPdf, ENT_QUOTES, 'UTF-8'); ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="<?php echo $rutaPdf; ?>"
                                            target="_blank"
                                            class="btn btn-sm btn-primary rounded-pill">
                                            <i class="bi bi-eye me-1"></i> Ver PDF
                                        </a>
                                        <a href="<?php echo $rutaPdf; ?>"
                                            download
                                            class="btn btn-sm btn-outline-primary rounded-pill">
                                            <i class="bi bi-download me-1"></i> Descargar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($historialDocs)): ?>
                            <div class="col-12">
                                <div class="border rounded-3 p-3">
                                    <h6 class="fw-semibold mb-3" style="color:#002F55;">
                                        <i class="bi bi-clock-history me-2"></i> Historial de notificaciones firmadas
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Nombre del archivo</th>
                                                    <th style="width: 180px;">Fecha de carga</th>
                                                    <th style="width: 140px;" class="text-end">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($historialDocs as $doc): ?>
                                                    <tr>
                                                        <td>
                                                            <small>
                                                                <?php echo htmlspecialchars($doc['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                                            </small>
                                                        </td>
                                                        <td>
                                                            <small class="text-muted">
                                                                <?php echo htmlspecialchars($doc['fecha'], ENT_QUOTES, 'UTF-8'); ?>
                                                            </small>
                                                        </td>
                                                        <td class="text-end">
                                                            <a href="<?php echo $doc['url']; ?>"
                                                                target="_blank"
                                                                class="btn btn-xs btn-outline-primary rounded-pill me-1">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                            <a href="<?php echo $doc['url']; ?>"
                                                                download
                                                                class="btn btn-xs btn-outline-secondary rounded-pill">
                                                                <i class="bi bi-download"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($puedeCargarArchivo): ?>
                            <?php if ($yaHayNotifFirmada): ?>
                                <div class="col-12">
                                    <div class="alert alert-success mb-0 py-2 px-3">
                                        <i class="bi bi-check-circle-fill me-2"></i>
                                        <strong>Ya se subió la notificación firmada.</strong>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="col-12">
                                    <div class="border rounded-3 p-3">
                                        <h6 class="fw-semibold mb-3" style="color:#002F55;">
                                            <i class="bi bi-upload me-2"></i> Cargar / actualizar documento de OTROSI
                                        </h6>
                                        <form action="<?= neiva_app_url('Arbimaps/vistas/Personal/acciones/otrosi_subir_archivo.php') ?>"
                                            method="post" enctype="multipart/form-data" class="row g-3">
                                            <input type="hidden" name="solicitud_id" value="<?php echo (int)$solicitud['id']; ?>">
                                            <input type="hidden" name="usuario_id" value="<?php echo (int)$idUsuario; ?>">

                                            <div class="col-12 col-lg-8">
                                                <label class="form-label small mb-1">Seleccione el documento (PDF)</label>
                                                <input type="file" name="archivo_otrosi" class="form-control form-control-sm"
                                                    accept="application/pdf" required>
                                                <small class="text-muted">Solo se permiten archivos en formato PDF.</small>
                                            </div>
                                            <div class="col-12 col-lg-4 d-flex align-items-end">
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill w-100">
                                                    <i class="bi bi-cloud-upload me-1"></i> Cargar documento
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        <div class="col-12">
                            <div class="border rounded-3 p-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <form id="form-aprobar"
                                    method="post"
                                    action="<?= neiva_app_url('Arbimaps/vistas/Personal/acciones/aprobar_notificacion.php') ?>"
                                    class="me-3">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($solicitud['id']); ?>">
                                    <input type="hidden" name="numero_identidad" value="<?php echo htmlspecialchars($solicitud['con_num_identidad'] ?? ''); ?>">
                                    <button type="submit" class="btn btn-primary" style="margin-right: 1cm;">
                                        Aprobar
                                    </button>
                                </form>

                                <form id="form-rechazar"
                                    method="post"
                                    action="<?= neiva_app_url('Arbimaps/vistas/Personal/acciones/rechazo_notificacion.php') ?>"
                                    class="me-3">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($solicitud['id']); ?>">
                                    <input type="hidden" name="numero_identidad" value="<?php echo htmlspecialchars($solicitud['con_num_identidad'] ?? ''); ?>">
                                    <button type="submit" class="btn btn-danger" style="margin-right: 1cm;">
                                        Rechazado
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> -->



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('form-aprobar');
        if (!form) return;
        form.addEventListener('submit', function(e) {
            if (form.dataset.swalSubmitted === '1') return;
            e.preventDefault();
            Swal.fire({
                title: '¿Aprobar solicitud?',
                text: '¿Confirmas marcar esta solicitud como VIABLE?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, aprobar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.dataset.swalSubmitted = '1';
                    form.submit();
                }
            });
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const formRechazar = document.getElementById('form-rechazar');
        if (!formRechazar) return;
        formRechazar.addEventListener('submit', function(e) {
            if (formRechazar.dataset.swalSubmitted === '1') return;
            e.preventDefault();
            Swal.fire({
                title: '¿Rechazar solicitud?',
                text: '¿Confirmas marcar esta solicitud como RECHAZADA?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, rechazar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    formRechazar.dataset.swalSubmitted = '1';
                    formRechazar.submit();
                }
            });
        });
    });
</script>
<?php if (!empty($swalAprobadoMsg)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({
                icon: 'success',
                title: 'Aprobado',
                text: <?php echo json_encode($swalAprobadoMsg); ?>,
                confirmButtonText: 'OK'
            });
        });
    </script>
<?php endif; ?>
<?php if (!empty($swalRechazadoMsg)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({
                icon: 'error',
                title: 'Rechazado',
                text: <?php echo json_encode($swalRechazadoMsg); ?>,
                confirmButtonText: 'OK'
            });
        });
    </script>
<?php endif; ?>