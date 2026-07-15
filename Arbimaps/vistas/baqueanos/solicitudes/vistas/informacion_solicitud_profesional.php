<?php
$where = "";

require_once dirname(__DIR__, 4) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 4) . '/config/permisos.php';

neiva_bootstrap();
neiva_require_methods('GET');
neiva_require_permission('menu.baqueanos', $PERMISOS);

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

$rolesPermitidos = array("administrador", "director_proyectos", "soporte", "social");

if (!in_array($rolUsuario, $rolesPermitidos) && !in_array($rolUsuarioDos, $rolesPermitidos)) {
    header("Location: ../../acceso_denegado.php");
    exit();
}


$idUsuario                  = $_SESSION['id_usuario'];
$id                         = $_GET['id'] ?? '';
$markReadUrl = neiva_app_url('Arbimaps/vistas/baqueanos/solicitudes/acciones/marcar_notificacion_lectura.php');
$csrfToken = neiva_csrf_token('global');

function normalizar_vacio($valor)
{
    if ($valor === null) return '';
    $v = trim((string)$valor);
    return ($v === '' || $v === '0') ? '' : $v;
}

function mostrar_valor($valor): string
{
    $v = trim((string)($valor ?? ''));
    if ($v === '' || $v === '0') {
        return '<span class="sin-registrar">sin registrar</span>';
    }
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}
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
$sb_cuanto_transporte       = $_POST['sb_cuanto_transporte'] ?? '';

$sb_hospedaje               = $_POST['sb_hospedaje'] ?? null;
$sb_porque_hospedaje        = $_POST['sb_porque_hospedaje'] ?? null;
$sb_cuanto_hospedaje        = $_POST['sb_cuanto_hospedaje'] ?? '';

$sb_profesional_baqueano    = $_POST['sb_profesional_baqueano'] ?? null;
$sb_reconocedor             = $_POST['sb_reconocedor'] ?? null;

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
        $row = $result->fetch_assoc();

        $id                         = $row['id'] ?? '';
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
        $sb_cuanto_transporte       = $row['sb_cuanto_transporte'] ?? '';

        $sb_hospedaje               = $row['sb_hospedaje'] ?? '';
        $sb_porque_hospedaje        = $row['sb_porque_hospedaje'] ?? '';
        $sb_cuanto_hospedaje        = $row['sb_cuanto_hospedaje'] ?? '';

        $sb_profesional_baqueano    = $row['sb_profesional_baqueano'] ?? '';
        $sb_reconocedor             = $row['sb_reconocedor'] ?? '';
    } else {
        echo "No se encontraron datos para esta cédula.<br>";
        exit;
    }
}


$stmt_cuentas->close();
$stmt->close();
$mysqli->close();

$valorFormateado    = '$ ' . number_format((float)$sb_cobro_diario, 0, ',', '.');
$valorFormateado1   = '$ ' . number_format((float)$sb_valor_cobrar, 0, ',', '.');
$valorFormateado3   = '$ ' . number_format((float)$sb_cuanto_hospedaje, 0, ',', '.');
$valorFormateado4   = '$ ' . number_format((float)$sb_cuanto_transporte, 0, ',', '.');

$nombre = $_SESSION['nombre_usuario'];
?>

<link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
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

    .modal {
        z-index: 20050 !important;
    }

    .modal-backdrop {
        z-index: 20040 !important;
    }

    @media (max-width:768px) {
        .modal-dialog {
            margin-top: 110px !important;
        }
    }

    .modalPro {
        background: #fff;
        border-radius: 1rem !important;
    }

    .modalHeaderPro {
        position: sticky;
        top: 0;
        z-index: 5;
        color: #fff;
        border: 0;
        background: linear-gradient(135deg, var(--brand) 0%, var(--brand-2) 55%, var(--brand) 100%);
        backdrop-filter: blur(6px);
        padding: 1rem 1.25rem;
    }

    .modalChipPro {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .35rem .6rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, .12);
        color: rgba(255, 255, 255, .92);
        font-size: .8rem;
        border: 1px solid rgba(255, 255, 255, .14);
    }

    .modalBodyPro {
        max-height: calc(100vh - 240px);
        overflow-y: auto;
        padding: 1.25rem;
    }

    .modalSectionPro {
        background: #fff;
        border: 1px solid rgba(0, 0, 0, .06);
        border-radius: 16px;
        padding: 1rem 1rem 1.1rem;
        box-shadow: var(--shadow-soft);
    }

    .modalSectionHeadPro {
        display: flex;
        align-items: center;
        gap: .75rem;
    }

    .modalSectionIconPro {
        width: 44px;
        height: 44px;
        border-radius: var(--r-14);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        border: 1px solid rgba(0, 0, 0, .06);
    }

    .modalFooterPro {
        position: sticky;
        bottom: 0;
        z-index: 5;
        background: rgba(255, 255, 255, .92);
        backdrop-filter: blur(6px);
        border-top: 1px solid rgba(0, 0, 0, .08);
        padding: 1rem 1.25rem;
        display: flex;
        justify-content: center;
        gap: .5rem;
    }

    .modalBodyPro::-webkit-scrollbar {
        width: 10px;
    }

    .modalBodyPro::-webkit-scrollbar-thumb {
        background: rgba(0, 47, 85, .22);
        border-radius: 999px;
    }

    .modalBodyPro::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, .04);
        border-radius: 999px;
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
        justify-content: center;
        align-items: center;
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
        <h4 class="mb-0 fw-bold mb-2 text-brand" style="font-weight:700 !important;">SOLICITUD BAQUEANO</h4>
        <small>Revisión profesional social</small>
    </div>

    <form id="formulario_editar"
        action="./vistas/baqueanos/solicitudes/acciones/acciones_lider_reconocimiento.php"
        method="POST" enctype="multipart/form-data">

        <input type="hidden" id="id" name="id" value="<?php echo $id; ?>">
        <input type="hidden" name="accion" id="accion">
        <input type="hidden" name="sb_razon_profesional" id="razon_hidden">

        <div class="container py-3">
            <div class="card border-0 shadow-sm rounded-4 text-white bg-brand">
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
            <div class="tabs-wrap">
                <i class="bi bi-arrow-repeat tab-cursor" aria-hidden="true"></i>
                <ul class="nav icon-tab gap-3 mb-4 justify-content-center align-items-center" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" type="button" title="Datos generales" onclick="seccion(1)">
                            <i class="bi bi-card-checklist"></i>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" type="button" title="Plan de trabajo" onclick="seccion(2)">
                            <i class="bi bi-calendar2-week"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <div class="tab-content p-3">
            <div class="form-section" id="section1">
                <div class="card rounded-4 shadow-sm border-0 section-one-card">
                    <div class="card-body p-4">
                        <div class="ticket-bite p-2 w-50 text-center text-white rounded-4 bg-brand" style="left:1%;">
                            <h6 class="fw-bold mb-0">Información general</h6>
                            <small>Datos del solicitante</small>
                        </div>

                        <div class="row mt-5">
                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" for="id_mostrar">Número de radicado</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-hash"></i></span>
                                    <input class="form-control" id="id_mostrar" value="ARB_<?php echo htmlspecialchars($id); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_tipo_documento">Tipo Documento del usuario</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                    <input class="form-control" id="sb_tipo_documento" value="<?php echo htmlspecialchars($sb_tipo_documento); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_numero_identidad">Documento de Identidad</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                                    <input class="form-control" id="sb_numero_identidad" value="<?php echo htmlspecialchars($sb_numero_identidad); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_baqueano_nombre">Nombres</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input class="form-control" id="sb_baqueano_nombre" value="<?php echo htmlspecialchars($sb_baqueano_nombre); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_baqueano_apellido">Apellidos</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input class="form-control" id="sb_baqueano_apellido" value="<?php echo htmlspecialchars($sb_baqueano_apellido); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_telefono_baqueano">Teléfono</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input class="form-control" id="sb_telefono_baqueano" value="<?php echo htmlspecialchars($sb_telefono_baqueano); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_correo_baqueano">Correo</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input class="form-control" id="sb_correo_baqueano" value="<?php echo htmlspecialchars($sb_correo_baqueano); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_direccion">Dirección</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                    <input class="form-control" id="sb_direccion" value="<?php echo htmlspecialchars($sb_direccion); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_cuenta">Cuenta Bancaria</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-bank2"></i></span>
                                    <?php
                                    $sb_cuenta_text = (trim($sb_cuenta ?? '') !== '') ? $sb_cuenta : 'Sin registrar';
                                    $is_empty = (trim($sb_cuenta ?? '') === '');
                                    ?>

                                    <input class="form-control" id="sb_cuenta_view"
                                        value="<?php echo htmlspecialchars($sb_cuenta_text); ?>"
                                        style="<?php echo $is_empty ? 'color:#6c757d;' : ''; ?>"
                                        disabled>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_tipo_cuenta">Tipo de cuenta</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-bank2"></i></span>
                                    <?php
                                    $sb_tipo_cuenta_text = (trim($sb_tipo_cuenta ?? '') !== '') ? $sb_tipo_cuenta : 'Sin registrar';
                                    $is_empty = (trim($sb_tipo_cuenta ?? '') === '');
                                    ?>

                                    <input class="form-control" id="sb_tipo_cuenta_text_view"
                                        value="<?php echo htmlspecialchars($sb_tipo_cuenta_text); ?>"
                                        style="<?php echo $is_empty ? 'color:#6c757d;' : ''; ?>"
                                        disabled>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_num_cuenta">N° Cuenta</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-bank"></i></span>
                                    <?php
                                    $sb_num_cuenta_text = (trim($sb_num_cuenta ?? '') !== '') ? $sb_num_cuenta : 'Sin registrar';
                                    $is_empty = (trim($sb_num_cuenta ?? '') === '');
                                    ?>

                                    <input class="form-control" id="sb_cuenta_view"
                                        value="<?php echo htmlspecialchars($sb_num_cuenta_text); ?>"
                                        style="<?php echo $is_empty ? 'color:#6c757d;' : ''; ?>"
                                        disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_titular">Titular de la Cuenta</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-bank"></i></span>
                                    <?php
                                    $sb_titular_text = (trim($sb_titular ?? '') !== '') ? $sb_titular : 'Sin registrar';
                                    $is_empty = (trim($sb_titular ?? '') === '');
                                    ?>

                                    <input class="form-control" id="sb_titular_view"
                                        value="<?php echo htmlspecialchars($sb_titular_text); ?>"
                                        style="<?php echo $is_empty ? 'color:#6c757d;' : ''; ?>"
                                        disabled>
                                </div>
                            </div>

                            <div class="mt-4 d-flex justify-content-end">
                                <button type="button" class="btn btn-brand" onclick="nextSection()"><b>SIGUIENTE</b></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-section d-none" id="section2">
                <div class="card rounded-4 shadow-sm border-0 section-one-card">
                    <div class="card-body p-4">
                        <div class="ticket-bite p-2 w-50 text-center text-white rounded-4 bg-brand" style="right:1%; left:auto;">
                            <h6 class="fw-bold mb-0">Plan de trabajo</h6>
                            <small>Información operativa y logística</small>
                        </div>

                        <div class="row mt-0">
                            <div class="col-12 mt-0 d-flex justify-content-start">
                                <h6 class="fw-bold p-2 text-white text-center rounded-3 mb-0" style="background-color: #002F55; width: fit-content; min-width: 380px;">
                                    Responsables
                                </h6>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_profesional_baqueano">Profesional Social</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-person-workspace"></i></span>
                                    <input class="form-control" name="sb_profesional_baqueano" id="sb_profesional_baqueano"
                                        value="<?php echo htmlspecialchars($sb_profesional_baqueano); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_coordinador">Coordinador</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-person-workspace"></i></span>
                                    <input class="form-control" id="sb_coordinador" value="<?php echo htmlspecialchars($sb_coordinador); ?>" disabled>
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
                                <label class="form-label fw-bold" style="font-size:0.9em;">Fecha de Inicio</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                                    <input type="date" class="form-control" value="<?php echo htmlspecialchars($sb_fecha_inicio); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Fecha Final</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-calendar-x"></i></span>
                                    <input type="date" class="form-control" value="<?php echo htmlspecialchars($sb_fecha_fin); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Total de Días</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                                    <input class="form-control" value="<?php echo htmlspecialchars($sb_dias_calculados); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-6 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Valor Diario</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                                    <input class="form-control" value="<?php echo htmlspecialchars($valorFormateado); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-6 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Total a Cobrar</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                    <input class="form-control" value="<?php echo htmlspecialchars($valorFormateado1); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-12 my-4 border-bottom" style="border-bottom:2px dashed #002f557a !important;"></div>

                            <div class="col-12 mt-0 d-flex justify-content-start">
                                <h6 class="fw-bold p-2 text-white text-center rounded-3 mb-0"
                                    style="background-color:#198754; width: fit-content; min-width: 380px;">
                                    Ubicación y actividad
                                </h6>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Unidad de Intervención</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-diagram-3"></i></span>
                                    <input class="form-control" value="<?php echo htmlspecialchars($sb_unidad_intervencion); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Unidad Operativa</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-building"></i></span>
                                    <input class="form-control" value="<?php echo htmlspecialchars($sb_unidad_operativa); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Tipo de Unidad</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-tags"></i></span>
                                    <input class="form-control" value="<?php echo htmlspecialchars($sb_tipo_unidad); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Municipio</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-geo"></i></span>
                                    <input class="form-control" value="<?php echo htmlspecialchars($sb_municipio); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Vereda/Barrio</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-pin-map"></i></span>
                                    <input class="form-control" value="<?php echo htmlspecialchars($sb_vereda); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Tipo de Actividad</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-clipboard-check"></i></span>
                                    <input class="form-control" value="<?php echo htmlspecialchars($sb_tipo_actividad); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-6 p-1 px-2 my-3" id="grupo_lider">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_lider_cuadrilla">Líder de Cuadrilla</label>
                                <div class="input-group shadow-sm rounded-3 precargado">
                                    <span class="input-group-text"><i class="bi bi-person-check"></i></span>
                                    <?php
                                    $sb_lider_cuadrilla_text = (trim($sb_lider_cuadrilla ?? '') !== '') ? $sb_lider_cuadrilla : 'Sin registrar';
                                    $is_empty = (trim($sb_lider_cuadrilla ?? '') === '');
                                    ?>

                                    <input class="form-control" id="sb_lider_cuadrilla_view"
                                        value="<?php echo htmlspecialchars($sb_lider_cuadrilla_text); ?>"
                                        style="<?php echo $is_empty ? 'color:#6c757d;' : ''; ?>"
                                        disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-6 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Reconocedor</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-person-lines-fill"></i></span>
                                    <?php
                                    $sb_reconocedor_text = (trim($sb_reconocedor ?? '') !== '') ? $sb_reconocedor : 'Sin registrar';
                                    $is_empty = (trim($sb_reconocedor ?? '') === '');
                                    ?>

                                    <input class="form-control" id="sb_reconocedor"
                                        value="<?php echo htmlspecialchars($sb_reconocedor_text); ?>"
                                        style="<?php echo $is_empty ? 'color:#6c757d;' : ''; ?>"
                                        disabled>
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
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_transporte_mostrar">Transporte</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-truck"></i></span>
                                    <input class="form-control" id="sb_transporte_mostrar" value="<?php echo htmlspecialchars($sb_transporte); ?>" disabled>
                                </div>
                            </div>

                            <div id="grupo_mostrar_transporte" style="display:none;" class="col-12 col-lg-8 p-0 m-0">
                                <div class="row g-0">
                                    <div class="col-md-6 col-lg-6 p-1 px-2 my-3">
                                        <label class="form-label fw-bold" style="font-size:0.9em;"><b>¿Por qué?</b></label>
                                        <div class="input-group shadow-sm rounded-2 precargado">
                                            <span class="input-group-text"><i class="bi bi-question-circle"></i></span>
                                            <input class="form-control" value="<?php echo htmlspecialchars($sb_porque_transporte); ?>" disabled>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-6 p-1 px-2 my-3">
                                        <label class="form-label fw-bold" style="font-size:0.9em;"><b>¿Cuánto?</b></label>
                                        <div class="input-group shadow-sm rounded-2 precargado">
                                            <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                                            <input class="form-control" value="<?php echo htmlspecialchars($valorFormateado4); ?>" disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_hospedaje_mostrar">Hospedaje</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-house-door"></i></span>
                                    <input class="form-control" id="sb_hospedaje_mostrar" value="<?php echo htmlspecialchars($sb_hospedaje); ?>" disabled>
                                </div>
                            </div>

                            <div id="grupo_mostrar_hospedaje" style="display:none;" class="col-12 col-lg-8 p-0 m-0">
                                <div class="row g-0">
                                    <div class="col-md-6 col-lg-6 p-1 px-2 my-3">
                                        <label class="form-label fw-bold" style="font-size:0.9em;"><b>¿Por qué?</b></label>
                                        <div class="input-group shadow-sm rounded-2 precargado">
                                            <span class="input-group-text"><i class="bi bi-question-circle"></i></span>
                                            <input class="form-control" value="<?php echo htmlspecialchars($sb_porque_hospedaje); ?>" disabled>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-6 p-1 px-2 my-3">
                                        <label class="form-label fw-bold" style="font-size:0.9em;"><b>¿Cuánto?</b></label>
                                        <div class="input-group shadow-sm rounded-2 precargado">
                                            <span class="input-group-text"><i class="bi bi-cash"></i></span>
                                            <input class="form-control" value="<?php echo htmlspecialchars($valorFormateado3); ?>" disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-center align-items-center flex-wrap" style="gap:.75rem;">
                            <button type="button"
                                class="btn btn-light border d-inline-flex align-items-center justify-content-center rounded-circle shadow-sm btn-circle"
                                data-toggle="modal" data-target="#modalPlanTrabajo" title="Editar">
                                <i class="bi bi-pencil fs-5"></i>
                            </button>
                            <button type="button"
                                class="btn btn-outline-success d-inline-flex align-items-center justify-content-center rounded-circle shadow-sm btn-circle btn-circle-approve"
                                onclick="accionFormulario('aprobar')" title="Aprobar">
                                <i class="bi bi-check-circle fs-5"></i>
                            </button>
                            <button type="button"
                                class="btn btn-outline-danger d-inline-flex align-items-center justify-content-center rounded-circle shadow-sm btn-circle btn-circle-return"
                                onclick="accionFormulario('devolver')" title="Devolver">
                                <i class="bi bi-reply-fill fs-5"></i>
                            </button>
                        </div>

                        <div id="campo-observacion" class="mt-4" style="display:none;">
                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                        <div>
                                            <h6 class="fw-bold mb-1 text-brand">Razón de devolución</h6>
                                            <small class="text-muted">Escriba el motivo por el cual devuelve la solicitud</small>
                                        </div>
                                        <button class="btn btn-brand d-inline-flex align-items-center justify-content-center rounded-circle shadow-sm btn-circle"
                                            id="enviar-devulucion" title="Enviar razón">
                                            <i class="bi bi-send fs-5"></i>
                                        </button>
                                    </div>

                                    <label for="sb_razon_profesional" class="form-label fw-bold" style="font-size:0.9em;">Observación</label>
                                    <div class="input-group shadow-sm rounded-2 precargado">
                                        <span class="input-group-text"><i class="bi bi-chat-left-text"></i></span>
                                        <textarea class="form-control" id="sb_razon_profesional" rows="3"
                                            placeholder="Escriba la razón por la cual devuelve la solicitud"></textarea>
                                    </div>
                                    <input type="hidden" id="correo" value="correo@ejemplo.com">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="modal fade" id="modalPlanTrabajo" tabindex="-1" role="dialog" aria-labelledby="modalPlanTrabajoLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden modalPro">
                <form id="formulario_editar_modal"
                    action="./vistas/baqueanos/solicitudes/acciones/acciones_lider_reconocimiento.php"
                    method="POST">

                    <input type="hidden" name="accion" value="editar">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <input type="hidden" name="id" id="modal_id">

                    <div class="modal-header modalHeaderPro position-relative d-flex align-items-center">
                        <div class="position-absolute top-50 start-50 translate-middle text-center">
                            <h5 class="modal-title mb-0 fw-bold text-white" id="modalPlanTrabajoLabel">PLAN DE TRABAJO</h5>
                            <small class="text-white-50">Ajusta fechas y valores</small>
                        </div>
                        <div class="d-flex align-items-end gap-2 ms-auto">
                            <span class="modalChipPro"><i class="bi bi-shield-check me-1"></i>Edición</span>
                        </div>
                    </div>

                    <div class="modal-body modalBodyPro">
                        <section class="modalSectionPro">
                            <div class="modalSectionHeadPro">
                                <div class="modalSectionIconPro" style="background: rgba(245, 140, 11, .15); color: rgba(245, 140, 11, 1);">
                                    <i class="bi bi-cash-coin"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0" style="color:#111;">Fechas y valores</h6>
                                    <small class="text-muted">Define el rango y los costos del plan</small>
                                </div>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_fecha_inicio">Fecha de Inicio</label>
                                    <div class="input-group shadow-sm rounded-3 precargado">
                                        <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                                        <input type="date" class="form-control" name="sb_fecha_inicio" id="sb_fecha_inicio" value="<?php echo $sb_fecha_inicio; ?>">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_fecha_fin">Fecha Final</label>
                                    <div class="input-group shadow-sm rounded-3 precargado">
                                        <span class="input-group-text"><i class="bi bi-calendar-x"></i></span>
                                        <input type="date" class="form-control" name="sb_fecha_fin" id="sb_fecha_fin" value="<?php echo $sb_fecha_fin; ?>">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_dias_calculados">Total de Días</label>
                                    <div class="input-group shadow-sm rounded-3 precargado">
                                        <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                                        <input class="form-control" name="sb_dias_calculados" id="sb_dias_calculados" value="<?php echo $sb_dias_calculados; ?>" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_cobro_diario">Valor Diario</label>
                                    <div class="input-group shadow-sm rounded-3 precargado">
                                        <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                                        <input class="form-control" name="sb_cobro_diario" id="sb_cobro_diario" value="<?php echo $sb_cobro_diario; ?>">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_valor_cobrar_visible">Total a Cobrar</label>
                                    <div class="input-group shadow-sm rounded-3 precargado">
                                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                        <input type="text" class="form-control" id="sb_valor_cobrar_visible" readonly>
                                    </div>
                                </div>

                                <input type="hidden" name="sb_valor_cobrar" id="sb_valor_cobrar">
                                <input type="hidden" id="id" name="id" value="<?php echo $id; ?>">
                            </div>
                        </section>
                    </div>

                    <div class="modal-footer modalFooterPro">
                        <button type="submit" id="btn-guardar" class="btn btn-brand px-4 d-inline-flex align-items-center gap-2">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <b>GUARDAR CAMBIOS</b>
                        </button>
                        <button type="button" class="btn btn-light border px-4" data-dismiss="modal">CERRAR</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="../../js/scripts.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/js/all.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://kit.fontawesome.com/9944c94262.js" crossorigin="anonymous"></script>

<script>
    $(document).ready(function() {
        $('#dataTable').DataTable();
    });

    document.addEventListener("DOMContentLoaded", function() {
        const transporte = document.getElementById("sb_transporte_mostrar")?.value?.trim().toUpperCase();
        const hospedaje = document.getElementById("sb_hospedaje_mostrar")?.value?.trim().toUpperCase();

        const grupoTransporte = document.getElementById("grupo_mostrar_transporte");
        const grupoHospedaje = document.getElementById("grupo_mostrar_hospedaje");

        if (transporte === "SI" && grupoTransporte) grupoTransporte.style.display = "block";
        if (hospedaje === "SI" && grupoHospedaje) grupoHospedaje.style.display = "block";
    });

    function accionFormulario(accion) {
        if (accion === 'devolver') {
            document.getElementById('campo-observacion').style.display = 'block';
            document.getElementById('sb_razon_profesional').focus();
            return;
        }

        let titulo = '';
        let texto = '';
        let confirmText = '';
        let icono = 'question';

        if (accion === 'aprobar') {
            titulo = '¿Aprobar solicitud?';
            texto = 'La solicitud será aprobada definitivamente.';
            confirmText = 'Sí, aprobar';
        }

        Swal.fire({
            customClass: {
                popup: 'swal-saved',
                container: 'saved-backdrop',
            },
            showConfirmButton: false,
            background: 'transparent',
            html: `
                <div class="swal-saved-card">
                    <div class="swal-saved-header text-success">
                        <i class="bi bi-check-circle-fill"></i>
                        Confirmar aprobación
                    </div>
                    <div class="swal-saved-divider"></div>
                    <div class="swal-saved-body">
                        <i class="bi bi-patch-question-fill text-success" style="font-size:70px;opacity:.15;"></i>
                        <div class="swal-saved-text">
                            La solicitud será aprobada definitivamente.
                        </div>
                        <div class="d-flex justify-content-center gap-3 mt-4">
                            <button id="confirmarAprobacion" class="btn btn-brand px-4 rounded-3">
                                Sí, aprobar
                            </button>
                            <button onclick="Swal.close()" class="btn btn-light border px-4 rounded-3">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>`,
            didOpen: () => {
                document.getElementById('confirmarAprobacion')
                    .addEventListener('click', () => {
                        Swal.close();
                        document.getElementById('accion').value = accion;
                        document.getElementById('formulario_editar').submit();
                    });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const btnEnviar = document.getElementById('enviar-devulucion');
        if (!btnEnviar) return;

        btnEnviar.addEventListener('click', function(e) {
            e.preventDefault();
            const razon = document.getElementById('sb_razon_profesional').value.trim();
            if (!razon) {
                Swal.fire({
                    customClass: {
                        popup: 'swal-saved',
                        container: 'saved-backdrop',
                    },
                    showConfirmButton: false,
                    background: 'transparent',
                    html: `
                        <div class="swal-saved-card">
                            <div class="swal-saved-header text-danger">
                                <i class="bi bi-x-circle-fill"></i>
                                Error
                            </div>
                            <div class="swal-saved-divider"></div>
                            <div class="swal-saved-body">
                                <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size:70px;opacity:.15;"></i>
                                <div class="swal-saved-text">
                                    Debe escribir una razón por la cual devuelve la solicitud.
                                </div>
                                <div class="mt-3">
                                    <button onclick="Swal.close()" class="btn btn-brand px-4">
                                        Aceptar
                                    </button>
                                </div>
                            </div>
                        </div>`
                });
                return;
            }

            Swal.fire({
                customClass: {
                    popup: 'swal-saved',
                    container: 'saved-backdrop',
                },
                showConfirmButton: false,
                background: 'transparent',
                html: `
                    <div class="swal-saved-card">
                        <div class="swal-saved-header" style="color: #002F55">
                            <i class="bi bi-reply-fill"></i>
                            Confirmar devolución
                        </div>
                        <div class="swal-saved-divider"></div>
                        <div class="swal-saved-body">
                            <i class="bi bi-exclamation-circle-fill" style="font-size:70px;opacity:.15; color: #485865bf"></i>
                            <div class="swal-saved-text">
                                Esta acción devolverá la solicitud al profesional.
                            </div>
                            <div class="d-flex justify-content-center gap-3 mt-4">
                                <button id="confirmarDevolucion" class="btn btn text-white px-4 rounded-3" style="background-color: #002F55">
                                    Sí, devolver
                                </button>
                                <button onclick="Swal.close()" class="btn btn-light border px-4 rounded-3">
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </div>`,
                didOpen: () => {
                    document.getElementById('confirmarDevolucion')
                        .addEventListener('click', () => {
                            Swal.close();
                            document.getElementById('razon_hidden').value = razon;
                            document.getElementById('accion').value = 'devolver';
                            document.getElementById('formulario_editar').submit();
                        });
                }
            });
        });
    });

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
        const valorTransporte = <?php echo json_encode((float)($sb_cuanto_transporte ?? 0)); ?>;
        const valorHospedaje = <?php echo json_encode((float)($sb_cuanto_hospedaje ?? 0)); ?>;
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
        const sbDiasCalculados = document.getElementById("sb_dias_calculados");
        if (sbCobroDiario) sbCobroDiario.addEventListener("input", calcularValorACobrar);
        if (sbDiasCalculados) sbDiasCalculados.addEventListener("input", calcularValorACobrar);
        calcularValorACobrar();
    });

    document.addEventListener('DOMContentLoaded', function() {
        const sb_fechaInicio    = document.getElementById('sb_fecha_inicio');
        const sb_fechaFin       = document.getElementById('sb_fecha_fin');
        const sb_diasCalculados = document.getElementById('sb_dias_calculados');
        function calcularDias() {
            const inicioStr = sb_fechaInicio.value;
            const finStr    = sb_fechaFin.value;

            if (inicioStr && finStr) {
                const [y1, m1, d1] = inicioStr.split('-').map(Number);
                const [y2, m2, d2] = finStr.split('-').map(Number);
                const inicio = new Date(y1, m1 - 1, d1);
                const fin = new Date(y2, m2 - 1, d2);
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
        sb_fechaInicio.addEventListener('change', calcularDias);
        sb_fechaFin.addEventListener('change', calcularDias);
        calcularDias();
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
        const navLinks = document.querySelectorAll('.icon-tab .nav-link');
        navLinks.forEach(btn => btn.classList.remove('active'));

        const btnActivo = navLinks[index - 1];
        if (btnActivo) btnActivo.classList.add('active');
    }

    document.addEventListener("DOMContentLoaded", function() {
        showSection(currentSectionIndex);
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
<?php if (!empty($id)): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetch(<?php echo json_encode($markReadUrl); ?>, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                solicitud_id: <?php echo json_encode((int) $id); ?>,
                marca: 'profesional',
                csrf_token: <?php echo json_encode($csrfToken); ?>
            })
        }).catch(function(error) {
            console.error('No fue posible marcar la notificación como leída:', error);
        });
    });
</script>
<?php endif; ?>
