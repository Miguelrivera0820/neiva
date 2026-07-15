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

$rolesPermitidos = array("administrador", "director_catastro", "Directivos", "soporte", "social");

if (!in_array($rolUsuario, $rolesPermitidos) && !in_array($rolUsuarioDos, $rolesPermitidos)) {
    header("Location: ../../acceso_denegado.php");
    exit();
}

$nombre = $_SESSION['nombre_usuario'];

// Variables iniciales
$id                     = $_GET['id'] ?? '';
$sb_tipo_documento      = $_GET['sb_tipo_documento'] ?? null;
$sb_numero_identidad    = $_GET['sb_numero_identidad'] ?? null;

$sb_baqueano_nombre     = $_POST['sb_baqueano_nombre'] ?? null;
$sb_baqueano_apellido   = $_POST['sb_baqueano_apellido'] ?? null;
$sb_telefono_baqueano   = $_POST['sb_telefono_baqueano'] ?? null;
$sb_correo_baqueano     = $_POST['sb_correo_baqueano'] ?? null;
$sb_direccion           = $_POST['sb_direccion'] ?? null;
$sb_cuenta              = $_POST['sb_cuenta'] ?? null;
$sb_tipo_cuenta         = $_POST['sb_tipo_cuenta'] ?? null;
$sb_num_cuenta          = $_POST['sb_num_cuenta'] ?? null;
$sb_titular             = $_POST['sb_titular'] ?? null;
$sb_year                = $_POST['sb_year'] ?? null;
$sb_fecha_inicio        = $_POST['sb_fecha_inicio'] ?? null;
$sb_fecha_fin           = $_POST['sb_fecha_fin'] ?? null;
$sb_dias_calculados     = $_POST['sb_dias_calculados'] ?? null;
$sb_cobro_diario        = $_POST['sb_cobro_diario'] ?? null;
$sb_valor_cobrar        = $_POST['sb_valor_cobrar'] ?? 0;
$sb_unidad_intervencion = $_POST['sb_unidad_intervencion'] ?? null;
$sb_tipo_unidad         = $_POST['sb_tipo_unidad'] ?? null;
$sb_municipio           = $_POST['sb_municipio'] ?? null;
$sb_vereda              = $_POST['sb_vereda'] ?? null;
$sb_tipo_actividad      = $_POST['sb_tipo_actividad'] ?? null;
$sb_coordinador         = $_POST['sb_coordinador'] ?? null;
$sb_lider_cuadrilla     = $_POST['sb_lider_cuadrilla'] ?? null;
$sb_transporte          = $_POST['sb_transporte'] ?? null;
$sb_porque_transporte   = $_POST['sb_porque_transporte'] ?? null;
$sb_hospedaje           = $_POST['sb_hospedaje'] ?? null;
$sb_porque_hospedaje    = $_POST['sb_porque_hospedaje'] ?? null;

$sb_estado_lider        = $_POST['sb_estado_lider'] ?? null;
$sb_razon_lider         = $_POST['sb_razon_lider'] ?? null;
$sb_estado_profesional  = $_POST['sb_estado_profesional'] ?? null;
$sb_razon_profesional   = $_POST['sb_razon_profesional'] ?? null;
$sb_estado_operaciones  = $_POST['sb_estado_operaciones'] ?? null;
$sb_razon_operaciones   = $_POST['sb_razon_operaciones'] ?? null;
$sb_estado_gerencia     = $_POST['sb_estado_gerencia'] ?? null;
$sb_razon_gerencia      = $_POST['sb_razon_gerencia'] ?? null;

$sb_reconocedor          = $_POST['sb_reconocedor'] ?? null;
$sb_profesional_baqueano = $_POST['sb_profesional_baqueano'] ?? null;

// Importante: estos deben guardarse como número (sin $ ni puntos)
$sb_cuanto_hospedaje    = $_POST['sb_cuanto_hospedaje'] ?? null;
$sb_cuanto_transporte   = $_POST['sb_cuanto_transporte'] ?? null;

$stmt = null;

// Verifica si `$id` fue proporcionado
if ($id) {
    $query = "SELECT * FROM solicitud_baqueanos WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $id                             = $row['id'] ?? '';
        $sb_numero_identidad            = $row['sb_numero_identidad'] ?? '';
        $sb_tipo_documento              = $row['sb_tipo_documento'] ?? '';
        $sb_baqueano_nombre             = $row['sb_baqueano_nombre'] ?? '';
        $sb_baqueano_apellido           = $row['sb_baqueano_apellido'] ?? '';
        $sb_telefono_baqueano           = $row['sb_telefono_baqueano'] ?? '';
        $sb_correo_baqueano             = $row['sb_correo_baqueano'] ?? '';
        $sb_direccion                   = $row['sb_direccion'] ?? '';
        $sb_cuenta                      = $row['sb_cuenta'] ?? '';
        $sb_tipo_cuenta                 = $row['sb_tipo_cuenta'] ?? '';
        $sb_num_cuenta                  = $row['sb_num_cuenta'] ?? '';
        $sb_titular                     = $row['sb_titular'] ?? '';
        $sb_year                        = $row['sb_year'] ?? '';
        $sb_fecha_inicio                = $row['sb_fecha_inicio'] ?? '';
        $sb_fecha_fin                   = $row['sb_fecha_fin'] ?? '';
        $sb_dias_calculados             = $row['sb_dias_calculados'] ?? '';
        $sb_cobro_diario                = $row['sb_cobro_diario'] ?? '';
        $sb_valor_cobrar                = $row['sb_valor_cobrar'] ?? 0;
        $sb_unidad_intervencion         = $row['sb_unidad_intervencion'] ?? '';
        $sb_unidad_operativa            = $row['sb_unidad_operativa'] ?? '';
        $sb_tipo_unidad                 = $row['sb_tipo_unidad'] ?? '';
        $sb_municipio                   = $row['sb_municipio'] ?? '';
        $sb_vereda                      = $row['sb_vereda'] ?? '';
        $sb_tipo_actividad              = $row['sb_tipo_actividad'] ?? '';
        $sb_coordinador                 = $row['sb_coordinador'] ?? '';
        $sb_lider_cuadrilla             = $row['sb_lider_cuadrilla'] ?? '';
        $sb_transporte                  = $row['sb_transporte'] ?? '';
        $sb_porque_transporte           = $row['sb_porque_transporte'] ?? '';
        $sb_hospedaje                   = $row['sb_hospedaje'] ?? '';
        $sb_porque_hospedaje            = $row['sb_porque_hospedaje'] ?? '';
        $sb_estado_lider                = $row['sb_estado_lider'] ?? '';
        $sb_razon_lider                 = $row['sb_razon_lider'] ?? '';
        $sb_estado_profesional          = $row['sb_estado_profesional'] ?? '';
        $sb_razon_profesional           = $row['sb_razon_profesional'] ?? '';
        $sb_estado_operaciones          = $row['sb_estado_operaciones'] ?? '';
        $sb_razon_operaciones           = $row['sb_razon_operaciones'] ?? '';
        $sb_estado_gerencia             = $row['sb_estado_gerencia'] ?? '';
        $sb_razon_gerencia              = $row['sb_razon_gerencia'] ?? '';
        $sb_profesional_baqueano        = $row['sb_profesional_baqueano'] ?? '';
        $sb_reconocedor                 = $row['sb_reconocedor'] ?? '';
        $sb_cuanto_hospedaje            = $row['sb_cuanto_hospedaje'] ?? '';
        $sb_cuanto_transporte           = $row['sb_cuanto_transporte'] ?? '';
    } else {
        echo "No se encontraron datos para esta cédula.<br>";
        exit;
    }
}

if ($stmt) $stmt->close();
$mysqli->close();

$nombre = $_SESSION['nombre_usuario'];
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    :root {
        --brand: #002F55;
        --brand-2: #001f3a;
        --shadow-1: 0 8px 20px rgba(0, 0, 0, .10);
        --shadow-2: 0 10px 24px rgba(0, 0, 0, .14);
        --shadow-soft: 0 12px 28px rgba(0, 0, 0, .06);
        --r-14: 14px;
        --r-16: 16px;
    }

    .bg-brand {
        background: var(--brand) !important;
    }

    .text-brand {
        color: var(--brand) !important;
    }

    .btn-brand {
        background: var(--brand) !important;
        border-color: var(--brand) !important;
        color: #fff !important;
        transition: .2s ease;
    }

    .btn-brand:hover {
        background: var(--brand-2) !important;
        border-color: var(--brand-2) !important;
        transform: translateY(-1px);
    }

    .precargado {
        border: 1px solid #002f55a1;
        box-shadow: 0 0 10px #002f5517 !important;
        border-radius: .5rem;
    }

    .precargado input,
    .precargado textarea,
    .precargado select {
        background-color: #002f5517 !important;
        font-weight: 500;
    }

    .icon-tab .nav-link {
        width: 44px;
        height: 44px;
        border-radius: var(--r-14);
        background: #fff;
        border: 0;
        box-shadow: var(--shadow-1);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        transition: .18s ease;
    }

    .icon-tab .nav-link i {
        font-size: 1.25rem;
        color: #111;
    }

    .icon-tab .nav-link.active {
        outline: 2px solid rgba(13, 110, 253, .25);
        box-shadow: var(--shadow-2);
    }

    .icon-tab .nav-link:hover {
        transform: translateY(-3px);
    }

    .tabs-wrap {
        display: inline-block;
        padding: .25rem .5rem;
        position: relative;
    }

    .tab-cursor {
        position: absolute;
        left: 50%;
        top: -14px;
        transform: translateX(-50%) scale(.9);
        opacity: 0;
        pointer-events: none;
        font-size: 1.1rem;
        transition: .2s ease;
        filter: drop-shadow(0 6px 12px rgba(0, 0, 0, .15));
    }

    .tabs-wrap:hover .tab-cursor {
        opacity: 1;
        transform: translateX(-50%) scale(1);
        animation: cursorSpin .75s cubic-bezier(.2, .8, .2, 1);
    }

    @keyframes cursorSpin {
        from {
            transform: translateX(-50%) scale(1) rotate(0)
        }

        to {
            transform: translateX(-50%) scale(1) rotate(360deg)
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .tabs-wrap:hover .tab-cursor {
            animation: none;
        }

        .icon-tab .nav-link {
            transition: none;
        }
    }

    .section-one-card {
        position: relative;
        overflow: visible !important;
    }

    .ticket-bite {
        position: absolute;
        top: -38px;
        z-index: 20;
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

    .btn-circle {
        width: 44px;
        height: 44px;
        transition: .2s ease;
    }

    .btn-circle-approve:hover {
        background-color: rgba(3, 170, 11, .74) !important;
        border-color: #fff !important;
        color: #fff !important;
        transform: translateY(-1px);
    }

    .btn-circle-return:hover {
        background-color: rgba(204, 41, 57, .92) !important;
        border-color: #fff !important;
        color: #fff !important;
        transform: translateY(-1px);
    }

    .campo-incompleto {
        border: 2px solid #dc3545 !important;
        box-shadow: 0 0 0 .15rem rgba(220, 53, 69, .15) !important;
    }

    .swal2-popup.swal-saved {
        padding: 0 !important;
        border-radius: 22px !important;
        width: 620px !important;
        max-width: 95vw !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .swal2-container.saved-backdrop {
        background: rgba(0, 0, 0, .18) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 24px !important;
    }

    .swal-saved-card {
        background: #fff;
        border-radius: 22px;
        box-shadow: 0 18px 60px rgba(0, 0, 0, .16);
        padding: 26px 28px 28px;
    }

    .swal-saved-header {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        font-weight: 700;
        font-size: 18px;
    }

    .swal-saved-divider {
        height: 1px;
        background: rgba(0, 0, 0, .08);
        margin: 14px 0 22px;
    }

    .swal-saved-body {
        text-align: center;
    }

    .swal-saved-text {
        color: #6c757d;
        font-size: 16px;
        margin-top: 10px;
    }
</style>

<div class="container-fluid">
    <div class="my-4 text-center">
        <h4 class="mb-0 fw-bold mb-2 text-brand" style="font-weight:700 !important;">SOLICITUD DE BAQUEANOS EN DEVOLUCION</h4>
        <small>Actualiza la información para la solicitud se vuelva a activar</small>
    </div>

    <div class="container py-3">
        <div class="card border-0 shadow-sm rounded-4 text-white bg-brand">
            <div class="card-body p-4">
                <div class="mb-4 pb-3 border-bottom border-white border-opacity-25 text-center">
                    <h6 class="fw-bold mb-1 text-center">Resumen de la solicitud</h6>
                    <small class="text-white-50 text-center">Información principal de la solicitud</small>
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
                                    $nombreB   = trim($sb_baqueano_nombre ?? '');
                                    $apellidoB = trim($sb_baqueano_apellido ?? '');
                                    $full      = trim($nombreB . ' ' . $apellidoB);
                                    echo htmlspecialchars($full, ENT_QUOTES, 'UTF-8');
                                    ?>
                                </div>
                            </div>
                            <div>
                                <div class="form-label small text-white-50 mb-1">Teléfono</div>
                                <div class="rounded-3 px-3 py-2 bg-white bg-opacity-10 border border-white border-opacity-25 fw-semibold text-truncate">
                                    <?php echo htmlspecialchars($sb_telefono_baqueano ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </div>
                            <div>
                                <div class="form-label small text-white-50 mb-1">Correo</div>
                                <div class="rounded-3 px-3 py-2 bg-white bg-opacity-10 border border-white border-opacity-25 fw-semibold text-truncate">
                                    <?php echo htmlspecialchars($sb_correo_baqueano ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="id" name="id" value="<?php echo htmlspecialchars($id); ?>">
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-3">
        <div class="tabs-wrap">
            <i class="bi bi-arrow-repeat tab-cursor" aria-hidden="true"></i>
            <ul class="nav icon-tab gap-3 mb-4 justify-content-center align-items-center" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-seccionUno" type="button"
                        role="tab" aria-selected="true" title="Datos generales"
                        onclick="seccion(1)">
                        <i class="bi bi-card-checklist"></i>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-seccionDos" type="button"
                        role="tab" aria-selected="false" title="Plan de trabajo"
                        onclick="seccion(2)">
                        <i class="bi bi-calendar2-week"></i>
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content p-3">
        <div class="tab-pane fade show active" id="pane-seccionUno" role="tabpanel" aria-labelledby="tab-seccionUno" tabindex="0">
            <div class="card rounded-4 shadow-sm border-0 section-one-card">
                <div class="card-body p-4">
                    <div class="ticket-bite p-2 w-50 text-center text-white rounded-4 bg-brand" style="left:1%;">
                        <h6 class="fw-bold mb-0">Datos generales</h6>
                        <small>Información general del solicitante</small>
                    </div>

                    <div class="row mt-5 form-section" id="section1">
                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="id_mostrar">Número de radicado</label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-hash"></i></span>
                                <input class="form-control" id="id_mostrar" value="ARB_<?php echo htmlspecialchars($id); ?>" disabled>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="sb_tipo_documento">Tipo Documento</label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                <select class="custom-select form-control" id="sb_tipo_documento" name="sb_tipo_documento">
                                    <option value="<?php echo htmlspecialchars($sb_tipo_documento); ?>"><?php echo htmlspecialchars($sb_tipo_documento); ?></option>
                                    <option value="Cedula_Extranjeria">Cédula de Extranjería</option>
                                    <option value="NIT">N.I.T.</option>
                                    <option value="Pasaporte">Pasaporte</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="sb_numero_identidad">Documento de Identidad</label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                                <input class="form-control" id="sb_numero_identidad" name="sb_numero_identidad" value="<?php echo htmlspecialchars($sb_numero_identidad); ?>">
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="sb_baqueano_nombre">Nombre</label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input class="form-control" id="sb_baqueano_nombre" name="sb_baqueano_nombre" value="<?php echo htmlspecialchars($sb_baqueano_nombre); ?>">
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="sb_baqueano_apellido">Apellido</label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input class="form-control" id="sb_baqueano_apellido" name="sb_baqueano_apellido" value="<?php echo htmlspecialchars($sb_baqueano_apellido); ?>">
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="sb_telefono_baqueano">Teléfono</label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input class="form-control" id="sb_telefono_baqueano" name="sb_telefono_baqueano" value="<?php echo htmlspecialchars($sb_telefono_baqueano); ?>">
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="sb_correo_baqueano">Correo</label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input class="form-control" id="sb_correo_baqueano" name="sb_correo_baqueano" value="<?php echo htmlspecialchars($sb_correo_baqueano); ?>">
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="sb_direccion">Dirección</label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                <input class="form-control" id="sb_direccion" name="sb_direccion" value="<?php echo htmlspecialchars($sb_direccion); ?>">
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="sb_cuenta">Cuenta Bancaria</label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-bank2"></i></span>
                                <select class="custom-select form-control" id="sb_cuenta" name="sb_cuenta">
                                    <option value="<?php echo htmlspecialchars($sb_cuenta); ?>"><?php echo htmlspecialchars($sb_cuenta); ?></option>
                                    <option value="BANCOLOMBIA">BANCOLOMBIA</option>
                                    <option value="DAVIPLATA">DAVIPLATA</option>
                                    <option value="DAVIVIENDA">DAVIVIENDA</option>
                                    <option value="NEQUI">NEQUI</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="sb_tipo_cuenta">Tipo de Cuenta Bancaria</label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-bank"></i></span>
                                <select class="custom-select form-control" id="sb_tipo_cuenta" name="sb_tipo_cuenta">
                                    <option value="<?php echo htmlspecialchars($sb_tipo_cuenta); ?>"><?php echo htmlspecialchars($sb_tipo_cuenta); ?></option>
                                    <option value="AHORROS">AHORROS</option>
                                    <option value="CORRIENTE">CORRIENTE</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="sb_num_cuenta">N° Cuenta</label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-credit-card-2-front"></i></span>
                                <input class="form-control" id="sb_num_cuenta" name="sb_num_cuenta" value="<?php echo htmlspecialchars($sb_num_cuenta); ?>">
                            </div>
                        </div>

                        <div class="col-md-12 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="sb_titular">Titular de la Cuenta</label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-person-check"></i></span>
                                <input class="form-control" id="sb_titular" name="sb_titular" value="<?php echo htmlspecialchars($sb_titular); ?>">
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-end w-100 px-2">
                            <button type="button" class="btn btn-brand" onclick="nextSection()"><b>SIGUIENTE</b></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="pane-seccionDos" role="tabpanel" aria-labelledby="tab-seccionDos" tabindex="0">
            <div class="card rounded-4 shadow-sm border-0 section-one-card">
                <div class="card-body p-4">
                    <div class="ticket-bite p-2 w-50 text-center text-white rounded-4 bg-brand" style="right:1%; left:auto;">
                        <h6 class="fw-bold mb-0">Plan de trabajo</h6>
                        <small>Operación, ubicación, logística y devolución</small>
                    </div>

                    <div class="row mt-0 form-section d-none" id="section2">
                        <div class="col-12 mt-0 d-flex justify-content-start">
                            <h6 class="fw-bold p-2 text-white text-center rounded-3 mb-0"
                                style="background-color: #002F55; width: fit-content; min-width: 380px;">
                                Responsables
                            </h6>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="sb_profesional_baqueano"><b>Profesional Social</b></label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-person-workspace"></i></span>
                                <input class="form-control" name="sb_profesional_baqueano" id="sb_profesional_baqueano" value="<?php echo htmlspecialchars($sb_profesional_baqueano); ?>">
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="sb_coordinador"><b>Coordinador</b></label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-person-workspace"></i></span>
                                <input class="form-control" name="sb_coordinador" id="sb_coordinador" value="<?php echo htmlspecialchars($sb_coordinador); ?>">
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_year">Año</label>
                            <div class="input-group shadow-sm rounded-2 precargado">
                                <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                                <input class="form-control" id="sb_year" value="<?php echo htmlspecialchars($sb_year); ?>" disabled>
                            </div>
                        </div>

                        <div class="col-12 my-4 border-bottom" style="border-bottom:2px dashed #002f557a !important;"></div>

                        <div class="col-12 mt-0 d-flex justify-content-end">
                            <h6 class="fw-bold p-2 text-white text-center rounded-3 mb-0"
                                style="background-color: rgba(245, 140, 11, 0.81); width: fit-content; min-width: 380px;">
                                Fechas y valores
                            </h6>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="sb_fecha_inicio">Fecha de Inicio</label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                                <input type="date" class="form-control color-input" id="sb_fecha_inicio" name="sb_fecha_inicio" value="<?php echo htmlspecialchars($sb_fecha_inicio); ?>">
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="sb_fecha_fin">Fecha Final</label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-calendar-x"></i></span>
                                <input type="date" class="form-control color-input" id="sb_fecha_fin" name="sb_fecha_fin" value="<?php echo htmlspecialchars($sb_fecha_fin); ?>">
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="sb_dias_calculados">Total de Días</label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                                <input class="form-control" name="sb_dias_calculados" id="sb_dias_calculados" value="<?php echo htmlspecialchars($sb_dias_calculados); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-6 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="sb_cobro_diario">Valor Diario</label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                                <input class="form-control" name="sb_cobro_diario" id="sb_cobro_diario" value="<?php echo htmlspecialchars($sb_cobro_diario); ?>">
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-6 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="sb_valor_cobrar_visible">Total a Cobrar</label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                <input type="text" class="form-control" id="sb_valor_cobrar_visible"
                                    value="<?php echo number_format((float)$sb_valor_cobrar, 0, ',', '.'); ?>" readonly>
                            </div>
                        </div>

                        <input type="hidden" name="sb_valor_cobrar" id="sb_valor_cobrar" value="<?php echo htmlspecialchars($sb_valor_cobrar); ?>">

                        <div class="col-12 my-4 border-bottom" style="border-bottom:2px dashed #002f557a !important;"></div>

                        <div class="col-12 mt-0 d-flex justify-content-start">
                            <h6 class="fw-bold p-2 text-white text-center rounded-3 mb-0"
                                style="background-color:#198754;  width: fit-content; min-width: 380px;">
                                Ubicación y actividad
                            </h6>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="sb_unidad_intervencion">Unidad de Intervención</label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-diagram-3"></i></span>
                                <input class="form-control color-input" id="sb_unidad_intervencion" name="sb_unidad_intervencion" value="<?php echo htmlspecialchars($sb_unidad_intervencion); ?>">
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="sb_unidad_operativa">Unidad Operativa</label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-building"></i></span>
                                <select class="custom-select form-control" name="sb_unidad_operativa" id="sb_unidad_operativa">
                                    <option value="<?php echo htmlspecialchars($sb_unidad_operativa); ?>"><?php echo htmlspecialchars($sb_unidad_operativa); ?></option>
                                    <option value="BARRIO">BARRIO</option>
                                    <option value="VEREDA">VEREDA</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="sb_tipo_unidad">Tipo de Unidad</label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-tags"></i></span>
                                <select class="custom-select form-control" name="sb_tipo_unidad" id="sb_tipo_unidad">
                                    <option value="<?php echo htmlspecialchars($sb_tipo_unidad); ?>"><?php echo htmlspecialchars($sb_tipo_unidad); ?></option>
                                    <option value="URBANA">URBANA</option>
                                    <option value="RURAL">RURAL</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="sb_municipio">Municipio</label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-geo"></i></span>
                                <select class="custom-select form-control" name="sb_municipio" id="sb_municipio" readonly>
                                    <option value="<?php echo htmlspecialchars($sb_municipio); ?>"><?php echo htmlspecialchars($sb_municipio); ?></option>
                                    <option value="Leiva">ARBOLETES</option>
                                    <option value="Guamuez">SAN JUAN</option>
                                    <option value="Necocli">NECOCLÍ</option>
                                    <option value="Arboletes">SAN PEDRO</option>
                                    <option value="San_Juan">VALLE DEL GUAMUEZ</option>
                                    <option value="San_Pedro">LEIVA</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="sb_vereda">Vereda/Barrio</label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-pin-map"></i></span>
                                <input class="form-control color-input" id="sb_vereda" name="sb_vereda" value="<?php echo htmlspecialchars($sb_vereda); ?>">
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="sb_tipo_actividad">Tipo de Actividad</label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-clipboard-check"></i></span>
                                <select class="custom-select form-control" id="sb_tipo_actividad" name="sb_tipo_actividad" onchange="mostrar()">
                                    <option value="<?php echo htmlspecialchars($sb_tipo_actividad); ?>"><?php echo htmlspecialchars($sb_tipo_actividad); ?></option>
                                    <option value="ATENCION_A_SALDOS">ATENCION A SALDOS</option>
                                    <option value="ATENCION_PQRS">ATENCION A PQRS</option>
                                    <option value="OBSERVACION_INTERVENTORIA">OBSERVACION DE INTERVENTORIA</option>
                                    <option value="RECONOCIMIENTO">RECONOCIMIENTO</option>
                                    <option value="ACOMPAÑAMIENTO_SOCIAL">ACOMPAÑAMIENTO SOCIAL</option>
                                    <option value="CONTROL_DE_CALIDAD">CONTROL DE CALIDAD</option>
                                    <option value="INTERLOCUCIÓN">INTERLOCUCIÓN</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6 p-1 px-2 my-3" id="grupo_lider" style="display: none;">
                            <label class="form-label fw-bold" for="sb_lider_cuadrilla"><b>Líder de Cuadrilla</b></label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-person-check"></i></span>
                                <input class="form-control color-input" id="sb_lider_cuadrilla" name="sb_lider_cuadrilla" value="<?php echo htmlspecialchars($sb_lider_cuadrilla); ?>">
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-6 p-1 px-2 my-3" id="grupo_reconocedor" style="display: none;">
                            <label class="form-label fw-bold" for="sb_reconocedor"><b>Reconocedor</b></label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-person-lines-fill"></i></span>
                                <input class="form-control" id="sb_reconocedor" name="sb_reconocedor" value="<?php echo htmlspecialchars($sb_reconocedor); ?>">
                            </div>
                        </div>

                        <div class="col-12 my-4 border-bottom" style="border-bottom:2px dashed #002f557a !important;"></div>

                        <div class="col-12 mt-0 d-flex justify-content-end">
                            <h6 class="fw-bold p-2 text-white text-center rounded-3 mb-0"
                                style="background-color: #002F55; width: fit-content; min-width: 380px;">
                                Logística
                            </h6>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="sb_transporte">Transporte</label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-truck"></i></span>
                                <select class="custom-select form-control" id="sb_transporte" name="sb_transporte">
                                    <option value="<?php echo htmlspecialchars($sb_transporte); ?>"><?php echo htmlspecialchars($sb_transporte); ?></option>
                                    <option value="SI">SI</option>
                                    <option value="NO">NO</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3" id="grupo_porque" style="display: none;">
                            <label class="form-label fw-bold" for="sb_porque_transporte"><b>¿Por qué?</b></label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-question-circle"></i></span>
                                <input class="form-control" name="sb_porque_transporte" id="sb_porque_transporte" value="<?php echo htmlspecialchars($sb_porque_transporte); ?>">
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3" id="campo_cuanto_transporte" style="display: none;">
                            <label class="form-label fw-bold"><b>¿Cuánto?</b></label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                                <input class="form-control" id="sb_cuanto_transporte" name="sb_cuanto_transporte"
                                    value="<?php echo htmlspecialchars($sb_cuanto_transporte); ?>">
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                            <label class="form-label fw-bold" for="sb_hospedaje">Hospedaje</label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-house-door"></i></span>
                                <select class="custom-select form-control" id="sb_hospedaje" name="sb_hospedaje">
                                    <option value="<?php echo htmlspecialchars($sb_hospedaje); ?>"><?php echo htmlspecialchars($sb_hospedaje); ?></option>
                                    <option value="SI">SI</option>
                                    <option value="NO">NO</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3" id="grupo_mostrar_hospedaje" style="display: none;">
                            <label class="form-label fw-bold"><b>¿Por qué?</b></label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-question-circle"></i></span>
                                <input class="form-control" id="sb_porque_hospedaje" name="sb_porque_hospedaje" value="<?php echo htmlspecialchars($sb_porque_hospedaje); ?>">
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 p-1 px-2 my-3" id="campo_cuanto_hospedaje" style="display: none;">
                            <label class="form-label fw-bold"><b>¿Cuánto?</b></label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-cash"></i></span>
                                <input class="form-control" id="sb_cuanto_hospedaje" name="sb_cuanto_hospedaje"
                                    value="<?php echo htmlspecialchars($sb_cuanto_hospedaje); ?>">
                            </div>
                        </div>

                        <div class="col-12 p-1 px-2 my-3">
                            <label class="form-label fw-bold">Motivo de Devolución</label>
                            <div class="input-group shadow-sm precargado">
                                <span class="input-group-text"><i class="bi bi-chat-left-text"></i></span>
                                <textarea class="form-control color-input" rows="4" disabled><?php
                                                                                                echo trim(
                                                                                                    ($sb_razon_lider ? "Líder: $sb_razon_lider\n" : "") .
                                                                                                        ($sb_razon_profesional ? "Profesional: $sb_razon_profesional\n" : "") .
                                                                                                        ($sb_razon_operaciones ? "Operaciones: $sb_razon_operaciones\n" : "") .
                                                                                                        ($sb_razon_gerencia ? "Gerencia: $sb_razon_gerencia" : "")
                                                                                                );
                                                                                                ?>
                                </textarea>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-center align-items-center flex-wrap w-100" style="gap:.75rem;">
                            <button type="button" id="btn-volver-enviar"
                                class="btn btn-outline-success d-inline-flex align-items-center justify-content-center rounded-circle shadow-sm btn-circle btn-circle-approve"
                                title="Volver a enviar">
                                <i class="bi bi-check-circle fs-5"></i>
                            </button>

                            <!-- <button type="button" id="btn-devolver"
                                class="btn btn-outline-danger d-inline-flex align-items-center justify-content-center rounded-circle shadow-sm btn-circle btn-circle-return"
                                title="Cancelar solicitud">
                                <i class="bi bi-x-circle fs-5"></i>
                            </button> -->
                        </div>

                        <div id="campo-observacion" class="mt-4 w-100" style="display:none;">
                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                        <div>
                                            <h6 class="fw-bold mb-1 text-brand">Razón de cancelación</h6>
                                            <small class="text-muted">Escriba el motivo por el cual cancela la solicitud</small>
                                        </div>
                                        <button class="btn btn-brand d-inline-flex align-items-center justify-content-center rounded-circle shadow-sm btn-circle"
                                            id="enviar-devulucion" title="Enviar razón">
                                            <i class="bi bi-send fs-5"></i>
                                        </button>
                                    </div>

                                    <label for="sb_razon_baqueano" class="form-label fw-bold" style="font-size:0.9em;">Observación</label>
                                    <div class="input-group shadow-sm precargado">
                                        <span class="input-group-text"><i class="bi bi-chat-left-text"></i></span>
                                        <textarea class="form-control" id="sb_razon_baqueano" placeholder="Escriba la razón por la cual devuelve la solicitud"></textarea>
                                    </div>

                                    <input type="hidden" id="correo" value="correo@ejemplo.com">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <form
        id="form_aprobar"
        method="post"
        action="<?php echo rtrim(dirname($_SERVER['PHP_SELF']), '/'); ?>/vistas/baqueanos/solicitudes/acciones/aprobar_devolucion.php"
        style="display:none;">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

        <input type="hidden" name="sb_numero_identidad" id="hidden_sb_numero_identidad" value="<?php echo htmlspecialchars($sb_numero_identidad); ?>">
        <input type="hidden" name="sb_tipo_documento" id="hidden_sb_tipo_documento">
        <input type="hidden" name="sb_baqueano_nombre" id="hidden_sb_baqueano_nombre">
        <input type="hidden" name="sb_baqueano_apellido" id="hidden_sb_baqueano_apellido">
        <input type="hidden" name="sb_telefono_baqueano" id="hidden_sb_telefono_baqueano">
        <input type="hidden" name="sb_correo_baqueano" id="hidden_sb_correo_baqueano">
        <input type="hidden" name="sb_direccion" id="hidden_sb_direccion">
        <input type="hidden" name="sb_cuenta" id="hidden_sb_cuenta">
        <input type="hidden" name="sb_tipo_cuenta" id="hidden_sb_tipo_cuenta">
        <input type="hidden" name="sb_num_cuenta" id="hidden_sb_num_cuenta">
        <input type="hidden" name="sb_titular" id="hidden_sb_titular">
        <input type="hidden" name="sb_year" id="hidden_sb_year" value="<?php echo htmlspecialchars($sb_year); ?>">

        <input type="hidden" name="sb_fecha_inicio" id="hidden_sb_fecha_inicio">
        <input type="hidden" name="sb_fecha_fin" id="hidden_sb_fecha_fin">
        <input type="hidden" name="sb_dias_calculados" id="hidden_sb_dias_calculados">
        <input type="hidden" name="sb_cobro_diario" id="hidden_sb_cobro_diario">
        <input type="hidden" name="sb_valor_cobrar" id="hidden_sb_valor_cobrar">

        <input type="hidden" name="sb_unidad_intervencion" id="hidden_sb_unidad_intervencion">
        <input type="hidden" name="sb_unidad_operativa" id="hidden_sb_unidad_operativa">
        <input type="hidden" name="sb_tipo_unidad" id="hidden_sb_tipo_unidad">
        <input type="hidden" name="sb_municipio" id="hidden_sb_municipio">
        <input type="hidden" name="sb_vereda" id="hidden_sb_vereda">
        <input type="hidden" name="sb_tipo_actividad" id="hidden_sb_tipo_actividad">
        <input type="hidden" name="sb_coordinador" id="hidden_sb_coordinador">
        <input type="hidden" name="sb_lider_cuadrilla" id="hidden_sb_lider_cuadrilla">

        <input type="hidden" name="sb_transporte" id="hidden_sb_transporte">
        <input type="hidden" name="sb_porque_transporte" id="hidden_sb_porque_transporte">
        <input type="hidden" name="sb_hospedaje" id="hidden_sb_hospedaje">
        <input type="hidden" name="sb_porque_hospedaje" id="hidden_sb_porque_hospedaje">

        <input type="hidden" name="sb_razon_lider" id="hidden_sb_razon_lider">
        <input type="hidden" name="sb_razon_profesional" id="hidden_sb_razon_profesional">
        <input type="hidden" name="sb_razon_operaciones" id="hidden_sb_razon_operaciones">
        <input type="hidden" name="sb_razon_gerencia" id="hidden_sb_razon_gerencia">

        <input type="hidden" name="sb_reconocedor" id="hidden_sb_reconocedor">
        <input type="hidden" name="sb_profesional_baqueano" id="hidden_sb_profesional_baqueano">

        <input type="hidden" name="sb_cuanto_hospedaje" id="hidden_sb_cuanto_hospedaje">
        <input type="hidden" name="sb_cuanto_transporte" id="hidden_sb_cuanto_transporte">
    </form>

    <form
        id="form_cancelar"
        method="post"
        action="<?php echo rtrim(dirname($_SERVER['PHP_SELF']), '/'); ?>/vistas/baqueanos/solicitudes/acciones/eliminar_solicitud_baqueano.php"
        style="display:none;">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
        <input type="hidden" name="sb_razon_baqueano" id="hidden_sb_razon_baqueano">
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="../../js/scripts.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnCancelar = document.getElementById('btn-devolver');
        const campoObservacion = document.getElementById('campo-observacion');
        const btnEnviar = document.getElementById('enviar-devulucion');

        btnCancelar.addEventListener('click', function() {
            campoObservacion.style.display = 'block';
        });

        btnEnviar.addEventListener('click', function(e) {
            e.preventDefault();
            const razon = document.getElementById('sb_razon_baqueano').value.trim();

            if (!razon) {
                Swal.fire('Error', 'Debe escribir la razón de la cancelación', 'error');
                return;
            }

            Swal.fire({
                title: '¿Cancelar solicitud?',
                text: 'Esta acción eliminará definitivamente la solicitud.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('hidden_sb_razon_baqueano').value = razon;
                    document.getElementById('form_cancelar').submit();
                }
            });
        });
    });

    function mostrar() {
        const valor = document.getElementById("sb_tipo_actividad").value;
        const grupoReconocedor = document.getElementById("grupo_reconocedor");
        const grupoLider = document.getElementById("grupo_lider");

        if (valor === "RECONOCIMIENTO" || valor === "CONTROL_DE_CALIDAD") {
            grupoReconocedor.style.display = "block";
            grupoLider.style.display = "block";
        } else {
            grupoReconocedor.style.display = "none";
            grupoLider.style.display = "none";
        }
    }
    document.addEventListener("DOMContentLoaded", mostrar);

    function limpiarNumero(valor) {
        if (valor === null || valor === undefined) return 0;
        let v = String(valor).trim();
        if (v === '') return 0;
        v = v.replace(/[^\d,.\-]/g, '');
        v = v.replace(/\.(?=\d{3}(?:\D|$))/g, '');
        v = v.replace(/,(?=\d{3}(?:\D|$))/g, '');
        v = v.replace(',', '.');
        const numero = parseFloat(v);
        return isNaN(numero) ? 0 : numero;
    }

    function calcularValorACobrar() {
        const dias = limpiarNumero(document.getElementById("sb_dias_calculados")?.value);
        const valorPorDia = limpiarNumero(document.getElementById("sb_cobro_diario")?.value);
        const valorTransporte = limpiarNumero(document.getElementById("sb_cuanto_transporte")?.value);
        const valorHospedaje = limpiarNumero(document.getElementById("sb_cuanto_hospedaje")?.value);
        const totalDias = dias * valorPorDia;
        const total = totalDias + valorTransporte + valorHospedaje;

        const totalFormateado = total.toLocaleString("es-CO", {
            style: "currency",
            currency: "COP",
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
        document.getElementById("sb_valor_cobrar_visible").value = totalFormateado;
        document.getElementById("sb_valor_cobrar").value = total;
    }


    document.addEventListener("DOMContentLoaded", function() {
        const sbCobroDiario = document.getElementById("sb_cobro_diario");
        const sbCuantoTransporte = document.getElementById("sb_cuanto_transporte");
        const sbCuantoHospedaje = document.getElementById("sb_cuanto_hospedaje");

        if (sbCobroDiario) sbCobroDiario.addEventListener("input", calcularValorACobrar);
        if (sbCuantoTransporte) sbCuantoTransporte.addEventListener("input", calcularValorACobrar);
        if (sbCuantoHospedaje) sbCuantoHospedaje.addEventListener("input", calcularValorACobrar);

        calcularValorACobrar();
    });


    document.addEventListener('DOMContentLoaded', function() {
        const sb_fechaInicio = document.getElementById('sb_fecha_inicio');
        const sb_fechaFin = document.getElementById('sb_fecha_fin');
        const sb_diasCalculados = document.getElementById('sb_dias_calculados');

        function calcularDias() {
            if (sb_fechaInicio.value && sb_fechaFin.value) {
                const inicio = new Date(sb_fechaInicio.value);
                const fin = new Date(sb_fechaFin.value);

                const diferencia = (fin - inicio);
                const dias = Math.floor(diferencia / (1000 * 60 * 60 * 24)) + 1;

                sb_diasCalculados.value = (dias > 0) ? dias : '';
            } else {
                sb_diasCalculados.value = '';
            }
            calcularValorACobrar();
        }

        sb_fechaInicio.addEventListener('input', calcularDias);
        sb_fechaFin.addEventListener('input', calcularDias);
        calcularDias();
    });

    function mostrarCamposTransporte() {
        const valor         = document.getElementById("sb_transporte").value;
        const campoCuanto   = document.getElementById("sb_cuanto_transporte");
        document.getElementById("grupo_porque").style.display = (valor === "SI") ? "block" : "none";
        document.getElementById("campo_cuanto_transporte").style.display = (valor === "SI") ? "block" : "none";
        if (valor !== "SI" && campoCuanto) {
            campoCuanto.value = '';
        }
        calcularValorACobrar();
    }

    function mostrarCamposHospedaje() {
        const valor         = document.getElementById("sb_hospedaje").value;
        const campoCuanto   = document.getElementById("sb_cuanto_hospedaje");
        document.getElementById("grupo_mostrar_hospedaje").style.display = (valor === "SI") ? "block" : "none";
        document.getElementById("campo_cuanto_hospedaje").style.display = (valor === "SI") ? "block" : "none";
        if (valor !== "SI" && campoCuanto) {
            campoCuanto.value = '';
        }
        calcularValorACobrar();
    }

    document.addEventListener("DOMContentLoaded", function() {
        mostrarCamposTransporte();
        mostrarCamposHospedaje();
        document.getElementById("sb_transporte").addEventListener("change", mostrarCamposTransporte);
        document.getElementById("sb_hospedaje").addEventListener("change", mostrarCamposHospedaje);
    });

    let currentSectionIndex = 1;
    const totalSections = 2;

    function showSection(index) {
        for (let i = 1; i <= totalSections; i++) {
            const section = document.getElementById('section' + i);
            if (section) section.classList.add('d-none');
        }
        const current = document.getElementById('section' + index);
        if (current) current.classList.remove('d-none');
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

    function nextSection() {
        if (currentSectionIndex < totalSections) {
            if (validarSeccion(currentSectionIndex)) {
                currentSectionIndex++;
                showSection(currentSectionIndex);
            }
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
        document.querySelectorAll('.icon-tab .nav-link').forEach(btn => btn.classList.remove('active'));
        if (index === 1) {
            document.getElementById('tab-seccionUno')?.classList.add('active');
            document.getElementById('pane-seccionUno')?.classList.add('show', 'active');
            document.getElementById('pane-seccionDos')?.classList.remove('show', 'active');
        } else {
            document.getElementById('tab-seccionDos')?.classList.add('active');
            document.getElementById('pane-seccionDos')?.classList.add('show', 'active');
            document.getElementById('pane-seccionUno')?.classList.remove('show', 'active');
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        showSection(currentSectionIndex);
    });

    function copyVisibleToHidden() {
        const fields = [
            'sb_tipo_documento', 'sb_numero_identidad', 'sb_baqueano_nombre', 'sb_baqueano_apellido',
            'sb_telefono_baqueano', 'sb_correo_baqueano', 'sb_direccion', 'sb_cuenta', 'sb_tipo_cuenta',
            'sb_num_cuenta', 'sb_titular', 'sb_fecha_inicio', 'sb_fecha_fin', 'sb_dias_calculados',
            'sb_cobro_diario', 'sb_valor_cobrar', 'sb_unidad_intervencion', 'sb_unidad_operativa', 'sb_tipo_unidad',
            'sb_municipio', 'sb_vereda', 'sb_tipo_actividad', 'sb_coordinador', 'sb_lider_cuadrilla', 'sb_transporte',
            'sb_porque_transporte', 'sb_hospedaje', 'sb_porque_hospedaje', 'sb_reconocedor', 'sb_profesional_baqueano',
            'sb_cuanto_hospedaje', 'sb_cuanto_transporte'
        ];

        fields.forEach((name) => {
            const visible = document.getElementById(name);
            const hidden = document.getElementById('hidden_' + name);
            if (visible && hidden) hidden.value = visible.value;
        });

        const vYear = document.getElementById('sb_year');
        const hYear = document.getElementById('hidden_sb_year');
        if (vYear && hYear) hYear.value = vYear.value;
    }

    document.getElementById('btn-volver-enviar').addEventListener('click', function() {
        Swal.fire({
            customClass: {
                popup: 'swal-saved',
                container: 'saved-backdrop',
            },
            showConfirmButton: false,
            background: 'transparent',
            html: `
                <div class="swal-saved-card">
                    <div class="swal-saved-header text-brand">
                        <i class="bi bi-send-fill"></i>
                        Reenviar solicitud
                    </div>
                    <div class="swal-saved-divider"></div>
                    <div class="swal-saved-body">
                        <i class="bi bi-arrow-repeat text-brand" style="font-size:70px;opacity:.15;"></i>
                        <div class="swal-saved-text">
                            La solicitud será enviada nuevamente para revisión.
                        </div>
                        <div class="d-flex justify-content-center gap-3 mt-4">
                            <button id="confirmarReenvio" class="btn btn-brand px-4 rounded-3">
                                Sí, enviar
                            </button>
                            <button onclick="Swal.close()" class="btn btn-light px-4 rounded-3 border">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>`,
            didOpen: () => {
                document.getElementById('confirmarReenvio')
                    .addEventListener('click', () => {
                        Swal.close();
                        copyVisibleToHidden();
                        document.getElementById('form_aprobar').submit();
                    });
            }
        });
    });


    document.addEventListener("DOMContentLoaded", function() {
        const fechaInicio = document.getElementById("sb_fecha_inicio");
        const fechaFin = document.getElementById("sb_fecha_fin");

        if (!fechaInicio || !fechaFin) return;

        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);

        const diaActual = hoy.getDay();
        const diffLunes = (diaActual === 0 ? -6 : 1 - diaActual);
        const lunesActual = new Date(hoy);
        lunesActual.setDate(hoy.getDate() + diffLunes);

        const domingoSiguiente = new Date(lunesActual);
        domingoSiguiente.setDate(lunesActual.getDate() + 13);

        function formatear(fecha) {
            return fecha.toISOString().split('T')[0];
        }

        const hoyStr = formatear(hoy);
        const maxStr = formatear(domingoSiguiente);

        fechaInicio.min = hoyStr;
        fechaInicio.max = maxStr;
        fechaFin.min = hoyStr;
        fechaFin.max = maxStr;

        fechaInicio.addEventListener("change", function() {
            if (!this.value) return;
            if (this.value < hoyStr) {
                Swal.fire({
                    icon: "warning",
                    title: "Fecha inválida",
                    text: "No puedes seleccionar fechas pasadas.",
                    confirmButtonColor: "#002F55"
                });
                this.value = "";
                return;
            }
            if (this.value > maxStr) {
                Swal.fire({
                    icon: "warning",
                    title: "Fuera de rango",
                    text: "Solo puedes seleccionar fechas dentro de esta y la próxima semana.",
                    confirmButtonColor: "#002F55"
                });
                this.value = "";
                return;
            }
            fechaFin.min = this.value;
            if (fechaFin.value && fechaFin.value < this.value) {
                fechaFin.value = "";
            }
        });
        fechaFin.addEventListener("change", function() {
            if (!this.value) return;
            if (fechaInicio.value && this.value < fechaInicio.value) {
                Swal.fire({
                    icon: "warning",
                    title: "Fecha inválida",
                    text: "La fecha final no puede ser anterior a la fecha inicio.",
                    confirmButtonColor: "#002F55"
                });
                this.value = "";
                return;
            }
            if (this.value > maxStr) {
                Swal.fire({
                    icon: "warning",
                    title: "Fuera de rango",
                    text: "Solo puedes seleccionar fechas dentro de esta y la próxima semana.",
                    confirmButtonColor: "#002F55"
                });
                this.value = "";
            }
        });
    });
</script>