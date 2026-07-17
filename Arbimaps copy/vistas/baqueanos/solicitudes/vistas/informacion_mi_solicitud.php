<?php
$where = "";

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../index.php");
    exit();
}

$idUsuario = (int) $_SESSION['id_usuario'];

$sql = "SELECT rol_usuario, rol_usuario_dos, nombre_usuario
        FROM usuarios_cons
        WHERE id_usuario = $idUsuario";
$resultado = $mysqli->query($sql);

if (!$resultado || $resultado->num_rows === 0) {
    header("Location: ../../acceso_denegado.php");
    exit();
}

$datosUsuario = $resultado->fetch_assoc();

$rolUsuario     = $datosUsuario['rol_usuario'] ?? '';
$rolUsuarioDos  = $datosUsuario['rol_usuario_dos'] ?? '';

$rolesPermitidos = array("administrador", "director_proyectos", "soporte", "lider_reconocimiento", "social", "gerencia");

if (!in_array($rolUsuario, $rolesPermitidos) && !in_array($rolUsuarioDos, $rolesPermitidos)) {
    header("Location: ../../acceso_denegado.php");
    exit();
}

$idUsuario  = $_SESSION['id_usuario'];
$id         = $_GET['id'] ?? '';
$sb_tipo_documento          = $_GET['sb_tipo_documento'] ?? null;
$sb_numero_identidad        = $_GET['sb_numero_identidad'] ?? null;
$sb_baqueano_nombre         = $_POST['sb_baqueano_nombre'] ?? null;
$sb_baqueano_apellido       = $_POST['sb_baqueano_apellido'] ?? null;
$sb_telefono_baqueano       = $_POST['sb_telefono_baqueano'] ?? null;
$sb_correo_baqueano         = $_POST['sb_correo_baqueano'] ?? null;
$sb_direccion               = $_POST['sb_direccion'] ?? null;
$sb_cuenta                  = $_POST['sb_cuenta'] ?? null;
$sb_tipo_cuenta             = $_POST['sb_tipo_cuenta'] ?? null;
$sb_num_cuenta              = $_POST['sb_num_cuenta'] ?? null;
$sb_titular                 = $_POST['sb_titular'] ?? null;
$sb_year                    = $_POST['sb_year'] ?? null;
$sb_fecha_inicio            = $_POST['sb_fecha_inicio'] ?? null;
$sb_fecha_fin               = $_POST['sb_fecha_fin'] ?? null;
$sb_dias_calculados         = $_POST['sb_dias_calculados'] ?? null;
$sb_cobro_diario            = $_POST['sb_cobro_diario'] ?? null;
$sb_valor_cobrar            = $_POST['sb_valor_cobrar'] ?? 0;
$sb_unidad_intervencion     = $_POST['sb_unidad_intervencion'] ?? null;
$sb_tipo_unidad             = $_POST['sb_tipo_unidad'] ?? null;
$sb_municipio               = $_POST['sb_municipio'] ?? null;
$sb_vereda                  = $_POST['sb_vereda'] ?? null;
$sb_tipo_actividad          = $_POST['sb_tipo_actividad'] ?? null;
$sb_coordinador             = $_POST['sb_coordinador'] ?? null;
$sb_lider_cuadrilla         = $_POST['sb_lider_cuadrilla'] ?? null;
$sb_transporte              = $_POST['sb_transporte'] ?? null;
$sb_porque_transporte       = $_POST['sb_porque_transporte'] ?? null;
$sb_hospedaje               = $_POST['sb_hospedaje'] ?? null;
$sb_porque_hospedaje        = $_POST['sb_porque_hospedaje'] ?? null;
$sb_reconocedor             = $_POST['sb_reconocedor'] ?? null;
$sb_profesional_baqueano    = $_POST['sb_profesional_baqueano'] ?? null;
$sb_cuanto_hospedaje        = $_POST['sb_cuanto_hospedaje'] ?? null;
$sb_cuanto_transporte       = $_POST['sb_cuanto_transporte'] ?? null;

$sql_cuentas = "SELECT * FROM solicitud_baqueanos WHERE id = ?";
$stmt_cuentas = $mysqli->prepare($sql_cuentas);
$stmt_cuentas->bind_param("i", $idUsuario);
$stmt_cuentas->execute();
$resultado_cuentas = $stmt_cuentas->get_result();

$datos_cuentas  = null;
$ruta_cuentas   = null;


if ($id) {
    $query = "SELECT * FROM solicitud_baqueanos WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row    = $result->fetch_assoc();
        $id     = $row['id'] ?? '';
        $sb_numero_identidad        = $row['sb_numero_identidad'] ?? '';
        $sb_tipo_documento          = $row['sb_tipo_documento'] ?? '';
        $sb_baqueano_nombre         = $row['sb_baqueano_nombre'] ?? '';
        $sb_baqueano_apellido       = $row['sb_baqueano_apellido'] ?? '';
        $sb_telefono_baqueano       = $row['sb_telefono_baqueano'] ?? '';
        $sb_correo_baqueano         = $row['sb_correo_baqueano'] ?? '';
        $sb_direccion               = $row['sb_direccion'] ?? '';
        $sb_cuenta                  = $row['sb_cuenta'] ?? '';
        $sb_tipo_cuenta             = $row['sb_tipo_cuenta'] ?? '';
        $sb_num_cuenta              = $row['sb_num_cuenta'] ?? '';
        $sb_titular                 = $row['sb_titular'] ?? '';
        $sb_year                    = $row['sb_year'] ?? '';
        $sb_fecha_inicio            = $row['sb_fecha_inicio'] ?? '';
        $sb_fecha_fin               = $row['sb_fecha_fin'] ?? '';
        $sb_dias_calculados         = $row['sb_dias_calculados'] ?? '';
        $sb_cobro_diario            = $row['sb_cobro_diario'] ?? '';
        $sb_valor_cobrar            = $row['sb_valor_cobrar'] ?? 0;
        $sb_unidad_intervencion     = $row['sb_unidad_intervencion'] ?? '';
        $sb_unidad_operativa        = $row['sb_unidad_operativa'] ?? '';
        $sb_tipo_unidad             = $row['sb_tipo_unidad'] ?? '';
        $sb_municipio               = $row['sb_municipio'] ?? '';
        $sb_vereda                  = $row['sb_vereda'] ?? '';
        $sb_tipo_actividad          = $row['sb_tipo_actividad'] ?? '';
        $sb_coordinador             = $row['sb_coordinador'] ?? '';
        $sb_lider_cuadrilla         = $row['sb_lider_cuadrilla'] ?? '';
        $sb_transporte              = $row['sb_transporte'] ?? '';
        $sb_porque_transporte       = $row['sb_porque_transporte'] ?? '';
        $sb_hospedaje               = $row['sb_hospedaje'] ?? '';
        $sb_porque_hospedaje        = $row['sb_porque_hospedaje'] ?? '';
        $sb_profesional_baqueano    = $row['sb_profesional_baqueano'] ?? '';
        $sb_reconocedor             = $row['sb_reconocedor'] ?? '';
        $sb_cuanto_hospedaje        = $row['sb_cuanto_hospedaje'] ?? '';
        $sb_cuanto_transporte       = $row['sb_cuanto_transporte'] ?? '';
    } else {
        echo "No se encontraron datos para esta cédula.<br>";
        exit;
    }
}

if (isset($_GET['id'])) {
    $id_solicitud = (int)$_GET['id'];

    $sql = "UPDATE notificaciones_baqueanos 
            SET leido_coordinador = 1 
            WHERE solicitud_baqueanos = ?";
    $stmt = $mysqli->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $id_solicitud);
        $stmt->execute();
    }
}
$stmt_cuentas->close();
$mysqli->close();

$valorFormateado    = '$ ' . number_format($sb_cobro_diario, 0, ',', '.');
$valorFormateado1   = '$ ' . number_format($sb_valor_cobrar, 0, ',', '.');
$valorFormateado3   = '$ ' . number_format($sb_cuanto_hospedaje, 0, ',', '.');
$valorFormateado4   = '$ ' . number_format($sb_cuanto_transporte, 0, ',', '.');

$nombre = $_SESSION['nombre_usuario'];
?>
<style>
    .icon-tab .nav-link {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        background: #fff;
        border: 0;
        box-shadow: 0 8px 20px rgba(0, 0, 0, .10);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .icon-tab .nav-link i {
        font-size: 1.25rem;
        color: #111;
    }

    .icon-tab .nav-link.active {
        outline: 2px solid rgba(13, 110, 253, .25);
        box-shadow: 0 10px 24px rgba(0, 0, 0, .14);
    }

    .tabs-wrap {
        display: inline-block;
        padding: .25rem .5rem;
    }

    .tab-cursor {
        position: absolute;
        left: 50%;
        top: -14px;
        transform: translateX(-50%) scale(.9);
        opacity: 0;
        pointer-events: none;
        font-size: 1.1rem;
        transition: opacity .2s ease, transform .2s ease;
        filter: drop-shadow(0 6px 12px rgba(0, 0, 0, .15));
    }

    .tabs-wrap:hover .tab-cursor {
        opacity: 1;
        transform: translateX(-50%) scale(1);
        animation: cursorSpin .75s cubic-bezier(.2, .8, .2, 1);
    }

    @keyframes cursorSpin {
        from {
            transform: translateX(-50%) scale(1) rotate(0deg);
        }

        to {
            transform: translateX(-50%) scale(1) rotate(360deg);
        }
    }

    .icon-tab .nav-link {
        transition: transform .18s ease, box-shadow .18s ease, outline-color .18s ease;
    }

    .tabs-wrap:hover .icon-tab .nav-link {
        transform: translateY(-2px);
    }

    .icon-tab .nav-link:hover {
        transform: translateY(-3px);
    }

    @media (prefers-reduced-motion: reduce) {
        .tabs-wrap:hover .tab-cursor {
            animation: none;
        }

        .icon-tab .nav-link {
            transition: none;
        }

        .tabs-wrap:hover .icon-tab .nav-link {
            transform: none;
        }
    }

    .timeline-item.completed .timeline-title {
        color: #394150;
    }

    .timeline-item.current .timeline-title {
        color: #00c48c;
    }

    .timeline-item.upcoming .timeline-title {
        color: #c5cadb;
    }

    .timeline-subtitle {
        font-size: 12px;
        color: #9ca3af;
    }

    .precargado {
        border: 1px solid #002f55a1;
        box-shadow: 0 0 10px #002f5517 !important;
    }

    .precargado input {
        background-color: #002f5517 !important;
        font-weight: 500;
    }

    .precargadodos {
        border: 1px solid #0481e788;
        box-shadow: 0 0 5px rgba(1, 109, 119, 0.24) !important;
    }

    .precargadodos input {
        background-color: #0481e788 !important;
        font-weight: 600;
    }

    .section-one-card {
        position: relative;
        overflow: visible !important;
    }

    .ticket-bite {
        position: absolute;
        top: -38px;
        left: -20%;
        z-index: 9999;
        overflow: visible;
    }

    .ticket-bite::before,
    .ticket-bite::after {
        content: "";
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #fff;
    }

    .ticket-bite::before {
        left: -11px;
    }

    .ticket-bite::after {
        right: -11px;
    }

    #pane-seccionUno,
    .tab-content,
    .card-especial-tres {
        overflow: visible !important;
    }
</style>
<div class="container-fluid">
    <div class="my-4 text-center">
        <h4 class=" mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 700 !important ">INFORMACIÓN DE MI SOLICITUD</h4>
        <small>Información detallada de mi solicitud</small>
    </div>
    <div class="container py-3">
        <div class="card border-0 shadow-sm rounded-4 text-white" style="background:#002F55;">
            <div class="card-body p-4">
                <div class="mb-4 pb-3 border-bottom border-white border-opacity-25 text-center">
                    <h6 class="fw-bold mb-1 text-center">Resumen de la solicitud</h6>
                    <small class="text-white-50 text-center">Información principal del radicado</small>
                </div>
                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <div class="d-grid gap-3">
                            <div>
                                <div class="form-label small text-white-50 mb-1">Número de radicado</div>
                                <div class="rounded-3 px-3 py-2 bg-white bg-opacity-10 border border-white border-opacity-25 fw-semibold text-truncate">
                                    ARB_<?php echo htmlspecialchars($id ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </div>
                            <div>
                                <div class="form-label small text-white-50 mb-1">Tipo de documento</div>
                                <div class="rounded-3 px-3 py-2 bg-white bg-opacity-10 border border-white border-opacity-25 fw-semibold text-truncate">
                                    <?php echo htmlspecialchars($sb_tipo_documento ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </div>
                            <div>
                                <div class="form-label small text-white-50 mb-1">Número de documento</div>
                                <div class="rounded-3 px-3 py-2 bg-white bg-opacity-10 border border-white border-opacity-25 fw-semibold text-truncate">
                                    <?php echo htmlspecialchars($sb_numero_identidad ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-none d-md-block col-md-1">
                        <div class="h-100 border-start border-white border-opacity-25 mx-auto"></div>
                    </div>
                    <div class="col-12 col-md-5">
                        <div class="d-grid gap-3">
                            <div>
                                <div class="form-label small text-white-50 mb-1">Nombres y apellidos</div>
                                <div class="rounded-3 px-3 py-2 bg-white bg-opacity-10 border border-white border-opacity-25 fw-semibold text-truncate">
                                    <?php
                                    $nombre   = trim($sb_baqueano_nombre ?? '');
                                    $apellido = trim($sb_baqueano_apellido ?? '');
                                    $full     = trim($nombre . ' ' . $apellido);
                                    echo htmlspecialchars($full, ENT_QUOTES, 'UTF-8');
                                    ?>
                                </div>
                            </div>
                            <div>
                                <div class="form-label small text-white-50 mb-1">Número telefónico</div>
                                <div class="rounded-3 px-3 py-2 bg-white bg-opacity-10 border border-white border-opacity-25 fw-semibold text-truncate">
                                    <?php echo htmlspecialchars($sb_telefono_baqueano ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </div>
                            <div>
                                <div class="form-label small text-white-50 mb-1">Correo electrónico</div>
                                <div class="rounded-3 px-3 py-2 bg-white bg-opacity-10 border border-white border-opacity-25 fw-semibold text-truncate">
                                    <?php echo htmlspecialchars($sb_correo_baqueano ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-3">
        <div class="tabs-wrap position-relative">
            <i class="bi bi-arrow-repeat tab-cursor" aria-hidden="true"></i>
            <ul class="nav icon-tab gap-3 mb-4 justify-content-center align-items-center" role="tablist">
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link active"
                        id="tab-seccionUno"
                        data-bs-toggle="tab"
                        data-bs-target="#pane-seccionUno"
                        type="button"
                        role="tab"
                        aria-controls="pane-seccionUno"
                        aria-selected="true"
                        title="Datos generales">
                        <i class="bi bi-card-checklist"></i>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link"
                        id="tab-seccionDos"
                        data-bs-toggle="tab"
                        data-bs-target="#pane-seccionDos"
                        type="button"
                        role="tab"
                        aria-controls="pane-seccionDos"
                        aria-selected="false"
                        title="Plan de trabajo">
                        <i class="bi bi-calendar2-week"></i>
                    </button>
                </li>
            </ul>
        </div>
    </div>
    <div class="tab-content p-3">
        <div
            class="tab-pane fade show active"
            id="pane-seccionUno"
            role="tabpanel"
            aria-labelledby="tab-seccionUno"
            tabindex="0">
            <div class="card rounded-4 shadow-sm border-0 section-one-card">
                <div class="card-body p-4">
                    <div class="ticket-bite p-2 w-50 text-center text-white rounded-4"
                        style="background-color:#002f55; position:absolute; left:1%">
                        <h6 class="fw-bold mb-0">Información general</h6>
                        <small>Aquí se muestra información general de quien solicito</small>
                    </div>

                    <div class="row mt-5">
                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3 ">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_direccion">Dirección</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_direccion)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                <input type="text" class="form-control " style="font-size:0.9em;<?php echo empty($sb_direccion) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>" id="sb_direccion" name="sb_direccion"
                                    aria-label="TipoDeDocumento" aria-describedby="basic-addon1" value="<?php echo !empty($sb_direccion) ? $sb_direccion : 'Sin registrar'; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3 ">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_cuenta">Cuenta Bancaria</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_cuenta)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi bi-bank2"></i></span>
                                <input type="text" class="form-control " style="font-size:0.9em;<?php echo empty($sb_cuenta) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>" id="sb_cuenta" name="sb_cuenta"
                                    aria-label="TipoDeDocumento" aria-describedby="basic-addon1" value="<?php echo !empty($sb_cuenta) ? $sb_cuenta : 'Sin registrar'; ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3 ">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_tipo_cuenta">Tipo de Cuenta Bancaria</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_tipo_cuenta)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi bi-safe"></i></span>
                                <input type="text" class="form-control " style="font-size:0.9em;<?php echo empty($sb_tipo_cuenta) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>" id="sb_tipo_cuenta" name="sb_tipo_cuenta"
                                    aria-label="TipoDeDocumento" aria-describedby="basic-addon1" value="<?php echo !empty($sb_tipo_cuenta) ? $sb_tipo_cuenta : 'Sin registrar'; ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6 p-1 px-2 my-3 ">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_num_cuenta">N° Cuenta</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_num_cuenta)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi bi-bank"></i></span>
                                <input type="text" class="form-control " style="font-size:0.9em;<?php echo empty($sb_num_cuenta) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>" id="sb_num_cuenta" name="sb_num_cuenta"
                                    aria-label="TipoDeDocumento" aria-describedby="basic-addon1" value="<?php echo !empty($sb_num_cuenta) ? $sb_num_cuenta : 'Sin registrar'; ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6 p-1 px-2 my-3 ">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_titular">Titular de la Cuenta</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_titular)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi-person-badge"></i></span>
                                <input type="text" class="form-control " style="font-size:0.9em;<?php echo empty($sb_titular) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>" id="sb_titular" name="sb_titular"
                                    aria-label="TipoDeDocumento" aria-describedby="basic-addon1" value="<?php echo !empty($sb_titular) ? $sb_titular : 'Sin registrar'; ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección Dos -->
        <div
            class="tab-pane fade"
            id="pane-seccionDos"
            role="tabpanel"
            aria-labelledby="tab-seccionDos"
            tabindex="0">

            <div class="card rounded-4 shadow-sm border-0 section-one-card">
                <div class="card-body p-4">
                    <div class="ticket-bite p-2 w-50 text-center text-white rounded-4"
                        style="background-color:#002f55; position:absolute; right:1%; left:auto;">
                        <h6 class="fw-bold mb-0">Plan de trabajo</h6>
                        <small>Información operativa y logística de la solicitud</small>
                    </div>
                    <div class="row mt-5">
                        <div class="col-12 mt-0 d-flex justify-content-start">
                            <h6 class="fw-bold p-2 text-white text-center rounded-3 mb-0 w-50"
                                style="background-color:#002F55;">
                                Responsables
                            </h6>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_profesional_baqueano">Profesional Social</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_profesional_baqueano)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi bi-person-workspace"></i></span>
                                <input class="form-control" style="font-size:0.9em;<?php echo empty($sb_profesional_baqueano) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>" name="sb_profesional_baqueano" id="sb_profesional_baqueano"
                                    value="<?php echo !empty($sb_profesional_baqueano) ? $sb_profesional_baqueano : 'Sin registrar'; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_coordinador">Coordinador</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_coordinador)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                <input class="form-control" style="font-size:0.9em;<?php echo empty($sb_coordinador) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>" id="sb_coordinador"
                                    value="<?php echo !empty($sb_coordinador) ? $sb_coordinador : 'Sin registrar'; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_year">Año</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_year)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                                <input class="form-control" style="font-size:0.9em;<?php echo empty($sb_year) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>" id="sb_year"
                                    value="<?php echo !empty($sb_year) ? $sb_year : 'Sin registrar'; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 my-4" style="border-bottom:2px dashed #002f557a"></div>

                        <!-- BLOQUE 2: Fechas y valores -->
                        <div class="col-12 mt-0 d-flex justify-content-end">
                            <h6 class="fw-bold p-2 text-white text-center rounded-3 mb-0 w-50"
                                style="background-color: rgba(245, 140, 11, 0.81)">
                                Fechas y valores
                            </h6>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_fecha_inicio">Fecha de Inicio</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_fecha_inicio)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                                <input type="date" class="form-control" style="font-size:0.9em;<?php echo empty($sb_fecha_inicio) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>"
                                    id="sb_fecha_inicio" value="<?php echo !empty($sb_fecha_inicio) ? $sb_fecha_inicio : ''; ?>" placeholder="<?php echo empty($sb_fecha_inicio) ? 'Sin registrar' : ''; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_fecha_fin">Fecha Final</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_fecha_fin)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi bi-calendar-x"></i></span>
                                <input type="date" class="form-control" style="font-size:0.9em;<?php echo empty($sb_fecha_fin) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>"
                                    id="sb_fecha_fin" value="<?php echo !empty($sb_fecha_fin) ? $sb_fecha_fin : ''; ?>" placeholder="<?php echo empty($sb_fecha_fin) ? 'Sin registrar' : ''; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_dias_calculados">Total de Días</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_dias_calculados)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                                <input class="form-control" style="font-size:0.9em;<?php echo empty($sb_dias_calculados) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>"
                                    id="sb_dias_calculados" value="<?php echo !empty($sb_dias_calculados) ? $sb_dias_calculados : 'Sin registrar'; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-6 p-1 px-2 my-3">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_cobro_diario">Valor Diario</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_cobro_diario)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                                <input class="form-control" style="font-size:0.9em;<?php echo empty($sb_cobro_diario) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>"
                                    id="sb_cobro_diario" value="<?php echo !empty($sb_cobro_diario) ? $valorFormateado : 'Sin registrar'; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-6 p-1 px-2 my-3">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_valor_cobrar">Total a Cobrar</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_valor_cobrar)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                <input type="text" class="form-control" style="font-size:0.9em;<?php echo empty($sb_valor_cobrar) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>"
                                    id="sb_valor_cobrar" value="<?php echo !empty($sb_valor_cobrar) ? $valorFormateado1 : 'Sin registrar'; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 my-4" style="border-bottom:2px dashed #002f557a"></div>

                        <!-- BLOQUE 3: Ubicación y actividad -->
                        <div class="col-12 mt-0 d-flex justify-content-start">
                            <h6 class="fw-bold p-2 text-white text-center rounded-3 mb-0 w-50"
                                style="background-color:#198754;">
                                Ubicación y actividad
                            </h6>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_unidad_intervencion">Unidad de Intervención</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_unidad_intervencion)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi bi-diagram-3"></i></span>
                                <input class="form-control" style="font-size:0.9em;<?php echo empty($sb_unidad_intervencion) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>"
                                    id="sb_unidad_intervencion" value="<?php echo !empty($sb_unidad_intervencion) ? $sb_unidad_intervencion : 'Sin registrar'; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_unidad_operativa">Unidad Operativa</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_unidad_operativa)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi bi-building"></i></span>
                                <input class="form-control" style="font-size:0.9em;<?php echo empty($sb_unidad_operativa) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>"
                                    id="sb_unidad_operativa" value="<?php echo !empty($sb_unidad_operativa) ? $sb_unidad_operativa : 'Sin registrar'; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_tipo_unidad">Tipo de Unidad</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_tipo_unidad)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi bi-tags"></i></span>
                                <input class="form-control" style="font-size:0.9em;<?php echo empty($sb_tipo_unidad) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>"
                                    id="sb_tipo_unidad" value="<?php echo !empty($sb_tipo_unidad) ? $sb_tipo_unidad : 'Sin registrar'; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_municipio">Municipio</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_municipio)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi bi-geo"></i></span>
                                <input class="form-control" style="font-size:0.9em;<?php echo empty($sb_municipio) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>"
                                    id="sb_municipio" value="<?php echo !empty($sb_municipio) ? $sb_municipio : 'Sin registrar'; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_vereda">Vereda/Barrio</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_vereda)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi bi-pin-map"></i></span>
                                <input class="form-control" style="font-size:0.9em;<?php echo empty($sb_vereda) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>"
                                    id="sb_vereda" value="<?php echo !empty($sb_vereda) ? $sb_vereda : 'Sin registrar'; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_tipo_actividad">Tipo de Actividad</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_tipo_actividad)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi bi-clipboard-check"></i></span>
                                <input class="form-control" style="font-size:0.9em;<?php echo empty($sb_tipo_actividad) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>"
                                    id="sb_tipo_actividad" value="<?php echo !empty($sb_tipo_actividad) ? $sb_tipo_actividad : 'Sin registrar'; ?>" onchange="mostrar()" readonly>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-6 p-1 px-2 my-3" id="grupo_lider" style="display:none;">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_lider_cuadrilla">Líder de Cuadrilla</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_lider_cuadrilla)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi bi-person-check"></i></span>
                                <input class="form-control" style="font-size:0.9em;<?php echo empty($sb_lider_cuadrilla) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>"
                                    id="sb_lider_cuadrilla" value="<?php echo !empty($sb_lider_cuadrilla) ? $sb_lider_cuadrilla : 'Sin registrar'; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-6 p-1 px-2 my-3" id="grupo_reconocedor" style="display:none;">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_reconocedor">Reconocedor</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_reconocedor)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi bi-person-lines-fill"></i></span>
                                <input class="form-control" style="font-size:0.9em;<?php echo empty($sb_reconocedor) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>"
                                    id="sb_reconocedor" value="<?php echo !empty($sb_reconocedor) ? $sb_reconocedor : 'Sin registrar'; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 my-4" style="border-bottom:2px dashed #002f557a"></div>

                        <!-- BLOQUE 4: Logística -->
                        <div class="col-12 mt-0 d-flex justify-content-end">
                            <h6 class="fw-bold p-2 text-white text-center rounded-3 mb-0 w-50"
                                style="background-color:#002F55;">
                                Logística
                            </h6>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_transporte_mostrar">Transporte</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_transporte)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi bi-truck"></i></span>
                                <input class="form-control" style="font-size:0.9em;<?php echo empty($sb_transporte) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>"
                                    id="sb_transporte_mostrar" value="<?php echo !empty($sb_transporte) ? $sb_transporte : 'Sin registrar'; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3" id="mostrar_porque_transporte" style="display:none;">
                            <label class="form-label fw-bold" style="font-size:0.9em;">¿Por qué?</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_porque_transporte)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi bi-question-circle"></i></span>
                                <input class="form-control" style="font-size:0.9em;<?php echo empty($sb_porque_transporte) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>"
                                    value="<?php echo !empty($sb_porque_transporte) ? $sb_porque_transporte : 'Sin registrar'; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3" id="mostrar_cuanto_transporte" style="display:none;">
                            <label class="form-label fw-bold" style="font-size:0.9em;">¿Cuánto?</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_cuanto_transporte)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi bi-cash"></i></span>
                                <input class="form-control" style="font-size:0.9em;<?php echo empty($sb_cuanto_transporte) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>"
                                    value="<?php echo !empty($sb_cuanto_transporte) ? $valorFormateado4 : 'Sin registrar'; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_hospedaje_mostrar">Hospedaje</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_hospedaje)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi bi-house-door"></i></span>
                                <input class="form-control" style="font-size:0.9em;<?php echo empty($sb_hospedaje) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>"
                                    id="sb_hospedaje_mostrar" value="<?php echo !empty($sb_hospedaje) ? $sb_hospedaje : 'Sin registrar'; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3" id="mostrar_porque_hospedaje" style="display:none;">
                            <label class="form-label fw-bold" style="font-size:0.9em;">¿Por qué?</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_porque_hospedaje)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi bi-question-circle"></i></span>
                                <input class="form-control" style="font-size:0.9em;<?php echo empty($sb_porque_hospedaje) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>"
                                    value="<?php echo !empty($sb_porque_hospedaje) ? $sb_porque_hospedaje : 'Sin registrar'; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3" id="mostrar_cuanto_hospedaje" style="display:none;">
                            <label class="form-label fw-bold" style="font-size:0.9em;">¿Cuánto?</label>
                            <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_cuanto_hospedaje)) ? 'precargado' : ''; ?>">
                                <span class="input-group-text"><i class="bi bi-cash"></i></span>
                                <input class="form-control" style="font-size:0.9em;<?php echo empty($sb_cuanto_hospedaje) ? 'background-color:#f3f6fa;color:#7a7a7a;' : ''; ?>"
                                    value="<?php echo !empty($sb_cuanto_hospedaje) ? $valorFormateado3 : 'Sin registrar'; ?>" readonly>
                            </div>
                        </div>
                    </div>
                    <div id="campo-observacion" class="mt-3" style="display:none;">
                        <textarea class="form-control" id="sb_razon_gerencia" placeholder="Escriba la razón por la cual devuelve la solicitud"></textarea>
                        <button class="btn btn-primary mt-2" id="enviar-devulucion">Enviar Razón</button>
                        <input type="hidden" id="correo" value="correo@ejemplo.com">
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
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable();
    });
    document.addEventListener("DOMContentLoaded", function() {
        function mostrarCamposCondicionales() {
            const transporteInput = document.getElementById("sb_transporte_mostrar");
            const hospedajeInput = document.getElementById("sb_hospedaje_mostrar");
            const transporte = transporteInput?.value?.trim().toUpperCase();
            const hospedaje = hospedajeInput?.value?.trim().toUpperCase();

            document.getElementById("mostrar_porque_transporte").style.display = (transporte === "SI") ? "block" : "none";
            document.getElementById("mostrar_cuanto_transporte").style.display = (transporte === "SI") ? "block" : "none";
            document.getElementById("mostrar_porque_hospedaje").style.display = (hospedaje === "SI") ? "block" : "none";
            document.getElementById("mostrar_cuanto_hospedaje").style.display = (hospedaje === "SI") ? "block" : "none";
        }
        mostrarCamposCondicionales();
    });

    function mostrar() {
        const tipoActividad = document.getElementById("sb_tipo_actividad").value;
        const grupoLider = document.getElementById("grupo_lider");
        const grupoReconocedor = document.getElementById("grupo_reconocedor");
        const valorLider = document.getElementById("sb_lider_cuadrilla").value.trim();
        const valorReconocedor = document.getElementById("sb_reconocedor").value.trim();
        const esActividadRequerida = tipoActividad === "RECONOCIMIENTO" || tipoActividad === "CONTROL_DE_CALIDAD";
        grupoLider.style.display = (esActividadRequerida && valorLider !== "") ? "block" : "none";
        grupoReconocedor.style.display = (esActividadRequerida && valorReconocedor !== "") ? "block" : "none";
    }
    document.addEventListener('DOMContentLoaded', mostrar);
    let currentSectionIndex = 1;
    const totalSections = 2;

    function formato(input) {
        let value = input.value.replace(/\D/g, "");
        let formattedValue = new Intl.NumberFormat("es-CO", {
            style: "currency",
            currency: "COP",
            minimumFractionDigits: 0
        }).format(value);
        input.value = formattedValue.replace("COP", "").trim();
        const hiddenId = input.getAttribute("data-hidden-id");
        const hiddenInput = document.getElementById(hiddenId);
        if (hiddenInput) {
            hiddenInput.value = value;
        } else {
            console.warn("No se encontró el input oculto:", hiddenId);
        }
    }

    function showSection(index) {
        for (let i = 1; i <= totalSections; i++) {
            const section = document.getElementById('section' + i);
            if (section) {
                section.classList.add('d-none');
            }
        }
        const current = document.getElementById('section' + index);
        if (current) {
            current.classList.remove('d-none');
        }
        actualizarBotones(index);
    }

    function validarSeccion(index) {
        const secciones = document.querySelectorAll('.form-section');
        const seccionActual = secciones[index - 1];
        let todoLleno = true;
        if (seccionActual) {
            const camposRequeridos = seccionActual.querySelectorAll('[required]');
            camposRequeridos.forEach(campo => {
                if (!campo.value.trim()) {
                    campo.classList.add('campo-incompleto');
                    todoLleno = false;
                } else {
                    campo.classList.remove('campo-incompleto');
                }
            });
        }
        return todoLleno;
    }
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[required]').forEach(campo => {
            campo.addEventListener('input', () => {
                if (campo.value.trim()) {
                    campo.classList.remove('campo-incompleto');
                }
            });
        });
    });

    function nextSection() {
        if (currentSectionIndex < totalSections) {
            if (validarSeccion(currentSectionIndex)) {
                currentSectionIndex++;
                showSection(currentSectionIndex);
            }
        }
    }

    function prevSection() {
        if (currentSectionIndex > 1) {
            currentSectionIndex--;
            showSection(currentSectionIndex);
        }
    }

    function seccion(index) {
        if (index > currentSectionIndex) {
            if (!validarSeccion(currentSectionIndex)) return;
        }
        currentSectionIndex = index;
        showSection(index);
    }

    function actualizarBotones(index) {
        document.querySelectorAll('.section-btn').forEach(btn => {
            btn.classList.remove('active-section-btn');
        });
        const botones = document.querySelectorAll('.section-btn');
        if (botones[index - 1]) {
            botones[index - 1].classList.add('active-section-btn');
        }
    }
    document.addEventListener("DOMContentLoaded", function() {
        showSection(currentSectionIndex);
    });
</script>