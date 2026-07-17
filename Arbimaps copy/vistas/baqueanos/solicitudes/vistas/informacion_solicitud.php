<?php
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../index.php");
    exit();
}

$idUsuario = (int)($_SESSION['id_usuario'] ?? 0);

$sqlRol = "SELECT rol_usuario, rol_usuario_dos
            FROM usuarios_cons
            WHERE id_usuario = $idUsuario";
$resRol = $mysqli->query($sqlRol);

$rolUsuario = '';
$rolUsuarioDos = '';

if ($resRol && $resRol->num_rows > 0) {
    $dataRol = $resRol->fetch_assoc();
    $rolUsuario    = $dataRol['rol_usuario'] ?? '';
    $rolUsuarioDos = $dataRol['rol_usuario_dos'] ?? '';
}

$rolesPermitidos = array("administrador", "director_catastro", "Directivos", "soporte", "lider_reconocimiento");

if (!in_array($rolUsuario, $rolesPermitidos) && !in_array($rolUsuarioDos, $rolesPermitidos)) {
    header("Location: ../../acceso_denegado.php");
    exit();
}

$id = $_GET['id'] ?? '';

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

$sb_tipo_documento              = $_GET['sb_tipo_documento'] ?? '';
$sb_numero_identidad            = $_GET['sb_numero_identidad'] ?? '';
$sb_baqueano_nombre             = $_POST['sb_baqueano_nombre'] ?? '';
$sb_baqueano_apellido           = $_POST['sb_baqueano_apellido'] ?? '';
$sb_telefono_baqueano           = $_POST['sb_telefono_baqueano'] ?? '';
$sb_correo_baqueano             = $_POST['sb_correo_baqueano'] ?? '';
$sb_direccion                   = $_POST['sb_direccion'] ?? '';
$sb_cuenta                      = $_POST['sb_cuenta'] ?? '';
$sb_tipo_cuenta                 = $_POST['sb_tipo_cuenta'] ?? '';
$sb_num_cuenta                  = $_POST['sb_num_cuenta'] ?? '';
$sb_titular                     = $_POST['sb_titular'] ?? '';
$sb_year                        = $_POST['sb_year'] ?? '';
$sb_fecha_inicio                = $_POST['sb_fecha_inicio'] ?? '';
$sb_fecha_fin                   = $_POST['sb_fecha_fin'] ?? '';
$sb_dias_calculados             = $_POST['sb_dias_calculados'] ?? '';
$sb_cobro_diario                = $_POST['sb_cobro_diario'] ?? '';
$sb_valor_cobrar                = $_POST['sb_valor_cobrar'] ?? 0;
$sb_unidad_intervencion         = $_POST['sb_unidad_intervencion'] ?? '';
$sb_unidad_operativa            = $_POST['sb_unidad_operativa'] ?? '';
$sb_tipo_unidad                 = $_POST['sb_tipo_unidad'] ?? '';
$sb_municipio                   = $_POST['sb_municipio'] ?? '';
$sb_vereda                      = $_POST['sb_vereda'] ?? '';
$sb_tipo_actividad              = $_POST['sb_tipo_actividad'] ?? '';
$sb_coordinador                 = $_POST['sb_coordinador'] ?? '';
$sb_lider_cuadrilla             = $_POST['sb_lider_cuadrilla'] ?? '';
$sb_transporte                  = $_POST['sb_transporte'] ?? '';
$sb_porque_transporte           = $_POST['sb_porque_transporte'] ?? '';
$sb_hospedaje                   = $_POST['sb_hospedaje'] ?? '';
$sb_porque_hospedaje            = $_POST['sb_porque_hospedaje'] ?? '';
$sb_reconocedor                 = $_POST['sb_reconocedor'] ?? '';
$sb_profesional_baqueano        = $_POST['sb_profesional_baqueano'] ?? '';
$sb_cuanto_hospedaje            = $_POST['sb_cuanto_hospedaje'] ?? '';
$sb_cuanto_transporte           = $_POST['sb_cuanto_transporte'] ?? '';

// ====== Cargar datos si viene id ======
if ($id) {
    $query = "SELECT * FROM solicitud_baqueanos WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $id                         = normalizar_vacio($row['id'] ?? '');
        $sb_numero_identidad        = normalizar_vacio($row['sb_numero_identidad'] ?? '');
        $sb_tipo_documento          = normalizar_vacio($row['sb_tipo_documento'] ?? '');
        $sb_baqueano_nombre         = normalizar_vacio($row['sb_baqueano_nombre'] ?? '');
        $sb_baqueano_apellido       = normalizar_vacio($row['sb_baqueano_apellido'] ?? '');
        $sb_telefono_baqueano       = normalizar_vacio($row['sb_telefono_baqueano'] ?? '');
        $sb_correo_baqueano         = normalizar_vacio($row['sb_correo_baqueano'] ?? '');
        $sb_direccion               = normalizar_vacio($row['sb_direccion'] ?? '');

        // FINANCIEROS (opcionales)
        $sb_cuenta                  = normalizar_vacio($row['sb_cuenta'] ?? '');
        $sb_tipo_cuenta             = normalizar_vacio($row['sb_tipo_cuenta'] ?? '');
        $sb_num_cuenta              = normalizar_vacio($row['sb_num_cuenta'] ?? '');
        $sb_titular                 = normalizar_vacio($row['sb_titular'] ?? '');

        $sb_year                    = normalizar_vacio($row['sb_year'] ?? '');
        $sb_fecha_inicio            = normalizar_vacio($row['sb_fecha_inicio'] ?? '');
        $sb_fecha_fin               = normalizar_vacio($row['sb_fecha_fin'] ?? '');
        $sb_dias_calculados         = normalizar_vacio($row['sb_dias_calculados'] ?? '');
        $sb_cobro_diario            = normalizar_vacio($row['sb_cobro_diario'] ?? '');
        $sb_valor_cobrar            = normalizar_vacio($row['sb_valor_cobrar'] ?? '');

        $sb_unidad_intervencion     = normalizar_vacio($row['sb_unidad_intervencion'] ?? '');
        $sb_unidad_operativa        = normalizar_vacio($row['sb_unidad_operativa'] ?? '');
        $sb_tipo_unidad             = normalizar_vacio($row['sb_tipo_unidad'] ?? '');
        $sb_municipio               = normalizar_vacio($row['sb_municipio'] ?? '');
        $sb_vereda                  = normalizar_vacio($row['sb_vereda'] ?? '');
        $sb_tipo_actividad          = normalizar_vacio($row['sb_tipo_actividad'] ?? '');
        $sb_coordinador             = normalizar_vacio($row['sb_coordinador'] ?? '');
        $sb_lider_cuadrilla         = normalizar_vacio($row['sb_lider_cuadrilla'] ?? '');

        $sb_transporte              = normalizar_vacio($row['sb_transporte'] ?? '');
        $sb_porque_transporte       = normalizar_vacio($row['sb_porque_transporte'] ?? '');
        $sb_hospedaje               = normalizar_vacio($row['sb_hospedaje'] ?? '');
        $sb_porque_hospedaje        = normalizar_vacio($row['sb_porque_hospedaje'] ?? '');

        $sb_reconocedor             = normalizar_vacio($row['sb_reconocedor'] ?? '');
        $sb_profesional_baqueano    = normalizar_vacio($row['sb_profesional_baqueano'] ?? '');
        $sb_cuanto_hospedaje        = normalizar_vacio($row['sb_cuanto_hospedaje'] ?? '');
        $sb_cuanto_transporte       = normalizar_vacio($row['sb_cuanto_transporte'] ?? '');
    } else {
        echo "No se encontraron datos para esta cédula.<br>";
        exit;
    }

    $stmt->close();
}

$mysqli->close();

$valorFormateado    = '$ ' . number_format((float)$sb_cobro_diario, 0, ',', '.');
$valorFormateado1   = '$ ' . number_format((float)$sb_valor_cobrar, 0, ',', '.');
$valorFormateado3   = '$ ' . number_format((float)$sb_cuanto_hospedaje, 0, ',', '.');
$valorFormateado4   = '$ ' . number_format((float)$sb_cuanto_transporte, 0, ',', '.');

$actionAprobar = rtrim(dirname($_SERVER['PHP_SELF']), '/') . "/vistas/baqueanos/solicitudes/acciones/aprobar_lider_reconocimiento.php";
?>

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

    /* NUEVO: "sin registrar" suave */
    .sin-registrar {
        color: #9CA3AF;
        font-style: italic;
        font-weight: 600;
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

    .modalNavPro {
        position: sticky;
        top: 76px;
        z-index: 4;
        background: rgba(255, 255, 255, .9);
        backdrop-filter: blur(6px);
        border-bottom: 1px solid rgba(0, 0, 0, .06);
        padding: .75rem 1rem;
        display: flex;
        gap: .5rem;
        overflow-x: auto;
    }

    .modalPillPro {
        border: 1px solid rgba(0, 47, 85, .18);
        background: #fff;
        color: var(--brand);
        border-radius: 999px;
        padding: .45rem .8rem;
        font-weight: 700;
        font-size: .85rem;
        display: inline-flex;
        align-items: center;
        transition: .2s ease;
        white-space: nowrap;
        cursor: pointer;
    }

    .modalPillPro:hover {
        background: rgba(0, 47, 85, .06);
        transform: translateY(-1px);
    }

    .modalPillPro.active {
        background: var(--brand);
        border-color: var(--brand);
        color: #fff;
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

    .modalDividerPro {
        border-bottom: 2px dashed rgba(0, 47, 85, .25);
        margin: 1.1rem 0;
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

    #modalPlanTrabajoNuevo .modal-dialog {
        max-width: 1200px;
    }

    #modalPlanTrabajoNuevo .modalBodyPro {
        max-height: calc(100vh - 320px);
    }

    #modalPlanTrabajoNuevo .modalNavPro {
        padding: .55rem .9rem;
    }

    #modalPlanTrabajoNuevo .modalHeaderPro {
        padding: .85rem 1.1rem;
    }

    .swal2-popup.swal-saved {
        padding: 0 !important;
        margin-left: 15%;
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
        border: 1px solid rgba(0, 0, 0, .06);
        box-shadow: 0 18px 60px rgba(0, 0, 0, .16);
        padding: 26px 28px 28px;
        gap: 18px;
    }

    .swal-saved-header {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #111827;
        font-weight: 700;
        font-size: 18px;
    }

    .swal-saved-divider {
        height: 1px;
        background: rgba(0, 0, 0, .08);
        margin: 14px 0 22px;
    }

    .swal-saved-body {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 14px;
        padding: 8px 0 2px;
    }

    .swal-saved-text {
        color: #9CA3AF;
        font-size: 16px;
        line-height: 1.35;
        max-width: 320px;
    }
</style>

<div class="container-fluid">
    <div class="my-4 text-center">
        <h4 class="mb-0 fw-bold mb-2 text-brand" style="font-weight:700 !important;">SOLICITUD BAQUEANO</h4>
        <small>Aprobación lider de reconocimiento</small>
    </div>

    <form id="formulario_editar" action="./vistas/baqueanos/solicitudes/acciones/acciones_lider_aprobacion.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" id="solicitud_id" value="<?php echo htmlspecialchars($id); ?>">
        <input type="hidden" name="numero_identidad" value="<?php echo htmlspecialchars($sb_numero_identidad); ?>">
        <input type="hidden" name="accion" id="accion" value="">
        <input type="hidden" name="sb_razon_lider" id="razon_hidden" value="">

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
                        <button class="nav-link active" id="tab-seccionUno" data-bs-toggle="tab" data-bs-target="#pane-seccionUno"
                            type="button" role="tab" aria-controls="pane-seccionUno" aria-selected="true"
                            title="Datos generales" onclick="seccion(1)">
                            <i class="bi bi-card-checklist"></i>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-seccionDos" data-bs-toggle="tab" data-bs-target="#pane-seccionDos"
                            type="button" role="tab" aria-controls="pane-seccionDos" aria-selected="false"
                            title="Plan de trabajo" onclick="seccion(2)">
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
                            <h6 class="fw-bold mb-0">Información general</h6>
                            <small>Aquí se muestra información general de quien solicito</small>
                        </div>

                        <div class="row mt-5">
                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" for="id_mostrar">Número de radicado</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-hash"></i></span>
                                    <input class="form-control" id="id_mostrar" style="font-size:0.9em;" value="ARB_<?php echo htmlspecialchars($id); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_tipo_documento_view">Tipo Documento del usuario</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                    <input class="form-control" id="sb_tipo_documento_view" value="<?php echo htmlspecialchars($sb_tipo_documento); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_numero_identidad_view">Documento de Identidad</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                                    <input class="form-control" id="sb_numero_identidad_view" value="<?php echo htmlspecialchars($sb_numero_identidad); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_baqueano_nombre_view">Nombres</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input class="form-control" id="sb_baqueano_nombre_view" value="<?php echo htmlspecialchars($sb_baqueano_nombre); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_baqueano_apellido_view">Apellidos</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input class="form-control" id="sb_baqueano_apellido_view" value="<?php echo htmlspecialchars($sb_baqueano_apellido); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_telefono_baqueano_view">Teléfono</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input class="form-control" id="sb_telefono_baqueano_view" value="<?php echo htmlspecialchars($sb_telefono_baqueano); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_correo_baqueano_view">Correo</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input class="form-control" id="sb_correo_baqueano_view" value="<?php echo htmlspecialchars($sb_correo_baqueano); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_direccion_view">Dirección</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                    <input class="form-control" id="sb_direccion_view" value="<?php echo htmlspecialchars($sb_direccion); ?>" disabled>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_cuenta_view">Cuenta Bancaria</label>
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

                            <div class="col-md-6 col-lg-6 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_num_cuenta_view">N° cuenta</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-bank"></i></span>
                                    <?php
                                    $sb_num_cuenta_text = (trim($sb_num_cuenta ?? '') !== '') ? $sb_num_cuenta : 'Sin registrar';
                                    $is_empty = (trim($sb_num_cuenta ?? '') === '');
                                    ?>

                                    <input class="form-control" id="sb_num_cuenta_view"
                                        value="<?php echo htmlspecialchars($sb_num_cuenta_text); ?>"
                                        style="<?php echo $is_empty ? 'color:#6c757d;' : ''; ?>"
                                        disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-6 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_titular_view">Titular de la Cuenta</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
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

                            <div class="col-md-6 col-lg-6 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_tipo_cuenta_view">Tipo de cuenta</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-safe"></i></span>
                                    <?php
                                    $sb_tipo_text = (trim($sb_tipo_cuenta ?? '') !== '') ? $sb_tipo_cuenta : 'Sin registrar';
                                    $is_empty = (trim($sb_tipo_cuenta ?? '') === '');
                                    ?>
                                    <input class="form-control" id="sb_tipo_cuenta_view"
                                        value="<?php echo htmlspecialchars($sb_tipo_text); ?>"
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

            <!-- SECCION 2 (sin cambios estructurales) -->
            <div class="tab-pane fade" id="pane-seccionDos" role="tabpanel" aria-labelledby="tab-seccionDos" tabindex="0">
                <div class="card rounded-4 shadow-sm border-0 section-one-card">
                    <div class="card-body p-4">
                        <div class="ticket-bite p-2 w-50 text-center text-white rounded-4 bg-brand" style="right:1%; left:auto;">
                            <h6 class="fw-bold mb-0">Plan de trabajo</h6>
                            <small>Información operativa y logística de la solicitud</small>
                        </div>

                        <div class="row mt-0">
                            <div class="col-12 mt-0 d-flex justify-content-start">
                                <h6 class="fw-bold p-2 text-white text-center rounded-3 mb-0"
                                    style="background-color: #002F55; width: fit-content; min-width: 380px;">
                                    Responsables
                                </h6>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_profesional_baqueano_view">Profesional Social</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-person-workspace"></i></span>
                                    <input class="form-control" id="sb_profesional_baqueano_view" value="<?php echo htmlspecialchars($sb_profesional_baqueano); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_coordinador_view">Coordinador</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-person-workspace"></i></span>
                                    <input class="form-control" id="sb_coordinador_view" value="<?php echo htmlspecialchars($sb_coordinador); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_year_view">Año</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                                    <input class="form-control" id="sb_year_view" value="<?php echo htmlspecialchars($sb_year); ?>" disabled>
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
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_fecha_inicio_view">Fecha de Inicio</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                                    <input type="date" class="form-control" id="sb_fecha_inicio_view" value="<?php echo htmlspecialchars($sb_fecha_inicio); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_fecha_fin_view">Fecha Final</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-calendar-x"></i></span>
                                    <input type="date" class="form-control" id="sb_fecha_fin_view" value="<?php echo htmlspecialchars($sb_fecha_fin); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_dias_calculados_view">Total de Días</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                                    <input class="form-control" id="sb_dias_calculados_view" value="<?php echo htmlspecialchars($sb_dias_calculados); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-6 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_cobro_diario_view">Valor Diario</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                                    <input class="form-control" id="sb_cobro_diario_view" value="<?php echo htmlspecialchars($valorFormateado); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-6 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_valor_cobrar_view">Total a Cobrar</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                    <input class="form-control" id="sb_valor_cobrar_view" value="<?php echo htmlspecialchars($valorFormateado1); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-12 my-4 border-bottom" style="border-bottom:2px dashed #002f557a !important;"></div>

                            <div class="col-12 mt-0 d-flex justify-content-start">
                                <h6 class="fw-bold p-2 text-white text-center rounded-3 mb-0"
                                    style="background-color:#198754;  width: fit-content; min-width: 380px;">
                                    Ubicación y actividad
                                </h6>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_unidad_intervencion_view">Unidad de Intervención</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-diagram-3"></i></span>
                                    <input class="form-control" id="sb_unidad_intervencion_view" value="<?php echo htmlspecialchars($sb_unidad_intervencion); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_unidad_operativa_view">Unidad Operativa</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-building"></i></span>
                                    <input class="form-control" id="sb_unidad_operativa_view" value="<?php echo htmlspecialchars($sb_unidad_operativa); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_tipo_unidad_view">Tipo de Unidad</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-tags"></i></span>
                                    <input class="form-control" id="sb_tipo_unidad_view" value="<?php echo htmlspecialchars($sb_tipo_unidad); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_municipio_view">Municipio</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-geo"></i></span>
                                    <input class="form-control" id="sb_municipio_view" value="<?php echo htmlspecialchars($sb_municipio); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_vereda_view">Vereda/Barrio</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-pin-map"></i></span>
                                    <input class="form-control" id="sb_vereda_view" value="<?php echo htmlspecialchars($sb_vereda); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_tipo_actividad_view">Tipo de Actividad</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-clipboard-check"></i></span>
                                    <input class="form-control" id="sb_tipo_actividad_view" value="<?php echo htmlspecialchars($sb_tipo_actividad); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-6 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_lider_cuadrilla_view">Líder de Cuadrilla</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
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
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_reconocedor_view">Reconocedor</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-person-lines-fill"></i></span>
                                    <?php
                                    $sb_reconocedor_text = (trim($sb_reconocedor ?? '') !== '') ? $sb_reconocedor : 'Sin registrar';
                                    $is_empty = (trim($sb_reconocedor ?? '') === '');
                                    ?>

                                    <input class="form-control" id="sb_reconocedor_view"
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
                            <button type="button" class="btn btn-light border d-inline-flex align-items-center justify-content-center rounded-circle shadow-sm btn-circle"
                                data-toggle="modal" data-target="#modalPlanTrabajoNuevo" title="Editar">
                                <i class="bi bi-pencil fs-5"></i>
                            </button>

                            <button type="button" class="btn btn-outline-success d-inline-flex align-items-center justify-content-center rounded-circle shadow-sm btn-circle btn-circle-approve"
                                onclick="accionFormulario('aprobar')" title="Aprobar">
                                <i class="bi bi-check-circle fs-5"></i>
                            </button>

                            <button type="button" class="btn btn-outline-danger d-inline-flex align-items-center justify-content-center rounded-circle shadow-sm btn-circle btn-circle-return"
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
                                        <button type="button" class="btn btn-brand d-inline-flex align-items-center justify-content-center rounded-circle shadow-sm btn-circle"
                                            id="enviar-devulucion" title="Enviar razón">
                                            <i class="bi bi-send fs-5"></i>
                                        </button>
                                    </div>

                                    <label for="sb_razon_lider" class="form-label fw-bold" style="font-size:0.9em;">Observación</label>
                                    <div class="input-group shadow-sm rounded-2 precargado">
                                        <span class="input-group-text"><i class="bi bi-chat-left-text"></i></span>
                                        <textarea class="form-control" id="sb_razon_lider" rows="3"
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
</div>

<!-- MODAL -->
<div class="modal fade" id="modalPlanTrabajoNuevo" tabindex="-1" role="dialog" aria-labelledby="modalPlanTrabajoNuevoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden modalPro">
            <form id="formulario_editar_modal" action="./vistas/baqueanos/solicitudes/acciones/acciones_lider_aprobacion.php" method="POST" enctype="multipart/form-data">

                <div class="modal-header modalHeaderPro position-relative d-flex align-items-center">
                    <div class="position-absolute top-50 start-50 translate-middle text-center">
                        <h5 class="modal-title mb-0 fw-bold text-white" id="modalPlanTrabajoNuevoLabel">INFORMACION DE LA SOLICITUD</h5>
                        <small class="text-white-50">Ajusta fechas, valores, ubicación, logística y finanzas</small>
                    </div>
                    <div class="d-flex align-items-end gap-2 ms-auto">
                        <span class="modalChipPro"><i class="bi bi-shield-check me-1"></i>Edición</span>
                    </div>
                </div>

                <div class="modalNavPro">
                    <button type="button" class="modalPillPro active" data-pro-pill="sec-fechas"><i class="bi bi-calendar2-week me-2"></i>Fechas y valores</button>
                    <button type="button" class="modalPillPro" data-pro-pill="sec-ubicacion"><i class="bi bi-geo-alt me-2"></i>Ubicación y actividad</button>
                    <button type="button" class="modalPillPro" data-pro-pill="sec-logistica"><i class="bi bi-truck me-2"></i>Logística</button>
                </div>

                <div class="modal-body modalBodyPro">
                    <input type="hidden" name="id" id="modal_id" value="<?php echo htmlspecialchars($id); ?>">
                    <input type="hidden" name="accion" value="editar">

                    <section id="sec-fechas" class="modalSectionPro">
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
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_fecha_inicio_modal">Fecha de Inicio</label>
                                <div class="input-group shadow-sm rounded-3 precargado">
                                    <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                                    <input type="date" class="form-control" name="sb_fecha_inicio" id="sb_fecha_inicio_modal" value="<?php echo htmlspecialchars($sb_fecha_inicio); ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_fecha_fin_modal">Fecha Final</label>
                                <div class="input-group shadow-sm rounded-3 precargado">
                                    <span class="input-group-text"><i class="bi bi-calendar-x"></i></span>
                                    <input type="date" class="form-control" name="sb_fecha_fin" id="sb_fecha_fin_modal" value="<?php echo htmlspecialchars($sb_fecha_fin); ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_dias_calculados_modal">Total de Días</label>
                                <div class="input-group shadow-sm rounded-3 precargado">
                                    <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                                    <input class="form-control" name="sb_dias_calculados" id="sb_dias_calculados_modal" value="<?php echo htmlspecialchars($sb_dias_calculados); ?>" readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_cobro_diario_modal">Valor Diario</label>
                                <div class="input-group shadow-sm rounded-3 precargado">
                                    <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                                    <input class="form-control" name="sb_cobro_diario" id="sb_cobro_diario_modal" value="<?php echo htmlspecialchars($sb_cobro_diario); ?>">
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
                        </div>

                        <!-- NUEVO: SECCIÓN FINANCIERA (opcionales) -->
                        <div class="modalDividerPro"></div>

                        <div class="modalSectionHeadPro">
                            <div class="modalSectionIconPro" style="background: rgba(0, 47, 85, .15); color: rgba(0, 47, 85, 1);">
                                <i class="bi bi-bank2"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0" style="color:#111;">Información financiera</h6>
                                <small class="text-muted">Campos opcionales del baqueano</small>
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_cuenta_modal">Cuenta Bancaria</label>
                                <div class="input-group shadow-sm rounded-3 precargado">
                                    <span class="input-group-text"><i class="bi bi-bank2"></i></span>
                                    <select class="custom-select form-control" name="sb_cuenta" id="sb_cuenta_modal" required>
                                        <option value="" disabled <?php echo empty($sb_cuenta) ? 'selected' : ''; ?>>
                                            Selecciona...
                                        </option>
                                        <?php if (!empty($sb_cuenta)) { ?>
                                            <option value="<?php echo htmlspecialchars($sb_cuenta); ?>" selected>
                                                <?php echo htmlspecialchars($sb_cuenta); ?>
                                            </option>
                                        <?php } ?>
                                        <option value="BANCOLOMBIA">BANCOLOMBIA</option>
                                        <option value="DAVIPLATA">DAVIPLATA</option>
                                        <option value="DAVIVIENDA">DAVIVIENDA</option>
                                        <option value="NEQUI">NEQUI</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_tipo_cuenta_modal">Tipo de Cuenta</label>
                                <div class="input-group shadow-sm rounded-3 precargado">
                                    <span class="input-group-text"><i class="bi bi-safe"></i></span>
                                    <select class="custom-select form-control" name="sb_tipo_cuenta" id="sb_tipo_cuenta_modal" required>
                                        <option value="" disabled <?php echo empty($sb_tipo_cuenta) ? 'selected' : ''; ?>>
                                            Selecciona...
                                        </option>
                                        <?php if (!empty($sb_tipo_cuenta)) { ?>
                                            <option value="<?php echo htmlspecialchars($sb_tipo_cuenta); ?>" selected>
                                                <?php echo htmlspecialchars($sb_tipo_cuenta); ?>
                                            </option>
                                        <?php } ?>
                                        <option value="AHORROS">AHORROS</option>
                                        <option value="CORRIENTE">CORRIENTE</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_num_cuenta_modal">Nº Cuenta Bancaria</label>
                                <div class="input-group shadow-sm rounded-3 precargado">
                                    <span class="input-group-text"><i class="bi bi-bank"></i></span>
                                    <input class="form-control" name="sb_num_cuenta" id="sb_num_cuenta_modal"
                                        value="<?php echo htmlspecialchars($sb_num_cuenta); ?>" placeholder="Ej: 0123456789">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_titular_modal">Titular</label>
                                <div class="input-group shadow-sm rounded-3 precargado">
                                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                    <input class="form-control" name="sb_titular" id="sb_titular_modal"
                                        value="<?php echo htmlspecialchars($sb_titular); ?>" placeholder="Nombre del titular">
                                </div>
                            </div>

                            <input type="hidden" name="sb_year" value="<?php echo htmlspecialchars($sb_year); ?>">
                            <input type="hidden" name="sb_profesional_baqueano" value="<?php echo htmlspecialchars($sb_profesional_baqueano); ?>">
                            <input type="hidden" name="sb_reconocedor" value="<?php echo htmlspecialchars($sb_reconocedor); ?>">
                            <input type="hidden" name="sb_direccion" value="<?php echo htmlspecialchars($sb_direccion); ?>">
                        </div>
                    </section>

                    <div class="modalDividerPro"></div>

                    <section id="sec-ubicacion" class="modalSectionPro">
                        <div class="modalSectionHeadPro">
                            <div class="modalSectionIconPro" style="background: rgba(25, 135, 84, .15); color: rgba(25, 135, 84, 1);">
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0" style="color:#111;">Ubicación y actividad</h6>
                                <small class="text-muted">Define la zona y responsables operativos</small>
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_unidad_intervencion_modal">Unidad de Intervención</label>
                                <div class="input-group shadow-sm rounded-3 precargado">
                                    <span class="input-group-text"><i class="bi bi-diagram-3"></i></span>
                                    <input class="form-control" name="sb_unidad_intervencion" id="sb_unidad_intervencion_modal" value="<?php echo htmlspecialchars($sb_unidad_intervencion); ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_unidad_operativa_modal">Unidad Operativa</label>
                                <div class="input-group shadow-sm rounded-3 precargado">
                                    <span class="input-group-text"><i class="bi bi-building"></i></span>
                                    <select class="custom-select form-control" name="sb_unidad_operativa" id="sb_unidad_operativa_modal">
                                        <option value="<?php echo htmlspecialchars($sb_unidad_operativa); ?>"><?php echo htmlspecialchars($sb_unidad_operativa); ?></option>
                                        <option value="BARRIO">BARRIO</option>
                                        <option value="VEREDA">VEREDA</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_tipo_unidad_modal">Tipo de Unidad</label>
                                <div class="input-group shadow-sm rounded-3 precargado">
                                    <span class="input-group-text"><i class="bi bi-tags"></i></span>
                                    <select class="custom-select form-control" name="sb_tipo_unidad" id="sb_tipo_unidad_modal">
                                        <option value="<?php echo htmlspecialchars($sb_tipo_unidad); ?>"><?php echo htmlspecialchars($sb_tipo_unidad); ?></option>
                                        <option value="URBANA">URBANA</option>
                                        <option value="RURAL">RURAL</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_municipio_modal">Municipio</label>
                                <div class="input-group shadow-sm rounded-3 precargado">
                                    <span class="input-group-text"><i class="bi bi-geo"></i></span>
                                    <select class="custom-select form-control" name="sb_municipio" id="sb_municipio_modal">
                                        <option value="<?php echo htmlspecialchars($sb_municipio); ?>"><?php echo htmlspecialchars($sb_municipio); ?></option>
                                        <option value="Arboletes">ARBOLETES</option>
                                        <option value="San_Juan">SAN JUAN</option>
                                        <option value="Necocli">NECOCLÍ</option>
                                        <option value="San_Pedro">SAN PEDRO</option>
                                        <option value="Valle_del_Guamuez">VALLE DEL GUAMUEZ</option>
                                        <option value="Leiva">LEIVA</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_vereda_modal">Vereda/Barrio</label>
                                <div class="input-group shadow-sm rounded-3 precargado">
                                    <span class="input-group-text"><i class="bi bi-pin-map"></i></span>
                                    <input class="form-control" name="sb_vereda" id="sb_vereda_modal" value="<?php echo htmlspecialchars($sb_vereda); ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_tipo_actividad_modal">Tipo de Actividad</label>
                                <div class="input-group shadow-sm rounded-3 precargado">
                                    <span class="input-group-text"><i class="bi bi-clipboard-check"></i></span>
                                    <select class="custom-select form-control" id="sb_tipo_actividad_modal" name="sb_tipo_actividad" onchange="mostrar_modal()">
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

                            <div class="col-md-6" id="grupo_coordinador">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_coordinador_modal"><b>Coordinador</b></label>
                                <div class="input-group shadow-sm rounded-3 precargado">
                                    <span class="input-group-text"><i class="bi bi-person-workspace"></i></span>
                                    <input class="form-control" name="sb_coordinador" id="sb_coordinador_modal" value="<?php echo htmlspecialchars($sb_coordinador); ?>">
                                </div>
                            </div>

                            <div class="col-md-6" id="grupo_lider">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_lider_cuadrilla_modal"><b>Líder de Cuadrilla</b></label>
                                <div class="input-group shadow-sm rounded-3 precargado">
                                    <span class="input-group-text"><i class="bi bi-person-check"></i></span>
                                    <input class="form-control" name="sb_lider_cuadrilla" id="sb_lider_cuadrilla_modal" value="<?php echo htmlspecialchars($sb_lider_cuadrilla); ?>">
                                </div>
                            </div>
                        </div>
                    </section>



                    <div class="modalDividerPro"></div>

                    <section id="sec-logistica" class="modalSectionPro">
                        <div class="modalSectionHeadPro">
                            <div class="modalSectionIconPro" style="background: rgba(0, 47, 85, .15); color: rgba(0, 47, 85, 1);">
                                <i class="bi bi-truck"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0" style="color:#111;">Logística</h6>
                                <small class="text-muted">Transporte, hospedaje y justificaciones</small>
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <!-- Transporte -->
                            <div class="col-12 col-lg-4">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_transporte_modal">Transporte</label>
                                <div class="input-group shadow-sm rounded-3 precargado">
                                    <span class="input-group-text"><i class="bi bi-truck"></i></span>
                                    <select class="custom-select form-control" id="sb_transporte_modal" name="sb_transporte" onchange="mostrarPorque_modal()">
                                        <option value="<?php echo htmlspecialchars($sb_transporte); ?>"><?php echo htmlspecialchars($sb_transporte); ?></option>
                                        <option value="SI">SI</option>
                                        <option value="NO">NO</option>
                                    </select>
                                </div>
                            </div>

                            <!-- ¿Por qué? Transporte -->
                            <div class="col-12 col-lg-4" id="grupo_porque">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_porque_transporte_modal"><b>¿Por qué?</b></label>
                                <div class="input-group shadow-sm rounded-3 precargado">
                                    <span class="input-group-text"><i class="bi bi-question-circle"></i></span>
                                    <input class="form-control" name="sb_porque_transporte" id="sb_porque_transporte_modal"
                                        value="<?php echo htmlspecialchars($sb_porque_transporte); ?>">
                                </div>
                            </div>

                            <!-- ¿Cuánto? Transporte -->
                            <div class="col-12 col-lg-4">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_cuanto_transporte_modal"><b>¿Cuánto?</b></label>
                                <div class="input-group shadow-sm rounded-3 precargado">
                                    <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                                    <input class="form-control" name="sb_cuanto_transporte" id="sb_cuanto_transporte_modal"
                                        value="<?php echo htmlspecialchars($sb_cuanto_transporte); ?>">
                                </div>
                            </div>

                            <!-- Hospedaje -->
                            <div class="col-12 col-lg-4">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_hospedaje_modal">Hospedaje</label>
                                <div class="input-group shadow-sm rounded-3 precargado">
                                    <span class="input-group-text"><i class="bi bi-house-door"></i></span>
                                    <select class="custom-select form-control" id="sb_hospedaje_modal" name="sb_hospedaje" onchange="mostrarHospedaje_modal()">
                                        <option value="<?php echo htmlspecialchars($sb_hospedaje); ?>"><?php echo htmlspecialchars($sb_hospedaje); ?></option>
                                        <option value="SI">SI</option>
                                        <option value="NO">NO</option>
                                    </select>
                                </div>
                            </div>

                            <!-- ¿Por qué? Hospedaje -->
                            <div class="col-12 col-lg-4" id="grupo_hospedaje">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_porque_hospedaje_modal"><b>¿Por qué?</b></label>
                                <div class="input-group shadow-sm rounded-3 precargado">
                                    <span class="input-group-text"><i class="bi bi-question-circle"></i></span>
                                    <input class="form-control" name="sb_porque_hospedaje" id="sb_porque_hospedaje_modal"
                                        value="<?php echo htmlspecialchars($sb_porque_hospedaje); ?>">
                                </div>
                            </div>

                            <!-- ¿Cuánto? Hospedaje -->
                            <div class="col-12 col-lg-4" id="grupo_cuanto_hospedaje">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_cuanto_hospedaje_modal"><b>¿Cuánto?</b></label>
                                <div class="input-group shadow-sm rounded-3 precargado">
                                    <span class="input-group-text"><i class="bi bi-cash"></i></span>
                                    <input class="form-control" name="sb_cuanto_hospedaje" id="sb_cuanto_hospedaje_modal"
                                        value="<?php echo htmlspecialchars($sb_cuanto_hospedaje); ?>">
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
                <div class="modal-footer modalFooterPro">
                    <button type="submit" id="btn-guardar" class="btn btn-brand px-4 d-inline-flex align-items-center gap-2">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <b>ACTUALIZAR</b>
                    </button>
                    <button type="button" class="btn btn-light border px-4" data-dismiss="modal">CERRAR</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function accionFormulario(accion) {
        if (accion === 'devolver') {
            document.getElementById('campo-observacion').style.display = 'block';
            document.getElementById('sb_razon_lider').focus();
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
            showCancelButton: false,
            background: 'transparent',
            backdrop: true,
            allowOutsideClick: false,
            html: `
                <div class="swal-saved-card">
                    <div class="swal-saved-header d-flex justify-content-center align-items-center text-center w-100">
                        <i class="bi bi-check-circle-fill" style="color:#002F55;font-size:20px;"></i>
                        Confirmación
                    </div>
                    <div class="swal-saved-divider"></div>
                    <div class="swal-saved-body">
                        <div>
                            <i class="bi bi-patch-question-fill"
                                style="font-size:70px;color:#002F55;opacity:.15;"></i>
                        </div>
                        <div class="swal-saved-text">
                            La solicitud será <strong>aprobada definitivamente</strong>.<br>
                            ¿Deseas continuar?
                        </div>
                        <div class="d-flex gap-3 mt-3">
                            <button id="confirmarAccion"
                                    class="btn text-white px-4"
                                    style="background:#002F55;border-radius:12px;">
                                <i class="bi bi-check-circle me-2"></i>
                                Confirmar
                            </button>
                            <button id="cancelarAccion"
                                class="btn btn-light px-4"
                                style="border-radius:12px;border:1px solid #dee2e6;">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>`,
            didOpen: () => {
                document.getElementById('confirmarAccion')
                    .addEventListener('click', () => {
                        Swal.close();
                        let form = document.getElementById('formulario_editar');
                        let accionInput = document.getElementById('accion');
                        if (!accionInput) {
                            accionInput = document.createElement('input');
                            accionInput.type = 'hidden';
                            accionInput.name = 'accion';
                            accionInput.id = 'accion';
                            form.appendChild(accionInput);
                        }
                        accionInput.value = accion;
                        form.submit();
                    });
                document.getElementById('cancelarAccion')
                    .addEventListener('click', () => {
                        Swal.close();
                    });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const btnEnviar = document.getElementById('enviar-devulucion');
        if (!btnEnviar) return;

        btnEnviar.addEventListener('click', function(e) {
            e.preventDefault();
            const razon = document.getElementById('sb_razon_lider').value.trim();
            if (!razon) {
                Swal.fire('Error', 'Debe escribir una razón por la cual devuelve la solicitud', 'error');
                return;
            }
            Swal.fire({
                customClass: {
                    popup: 'swal-saved',
                    container: 'saved-backdrop',
                },
                showConfirmButton: false,
                showCancelButton: false,
                background: 'transparent',
                backdrop: true,
                allowOutsideClick: false,
                html: `
                    <div class="swal-saved-card">
                        <div class="swal-saved-header d-flex justify-content-center align-items-center text-center w-100">
                            <i class="bi bi-reply-fill" style="color:#002F55;font-size:20px;"></i>
                            Confirmación de devolución
                        </div>
                        <div class="swal-saved-divider"></div>
                        <div class="swal-saved-body">
                            <div>
                                <i class="bi bi-exclamation-circle-fill"
                                    style="font-size:70px;color:#002F55;opacity:.15;"></i>
                            </div>
                            <div class="swal-saved-text">
                                La solicitud será <strong>devuelta</strong> al profesional.<br>
                                ¿Deseas continuar?
                            </div>
                            <div class="d-flex gap-3 mt-3">
                                <button id="confirmarDevolucion"
                                        class="btn text-white px-4"
                                        style="background:#002F55;border-radius:12px;">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Confirmar
                                </button>
                                <button id="cancelarDevolucion"
                                    class="btn btn-light px-4"
                                    style="border-radius:12px;border:1px solid #dee2e6;">
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </div>`,
                didOpen: () => {
                    document.getElementById('confirmarDevolucion')
                        .addEventListener('click', () => {
                            Swal.close();
                            const form = document.getElementById('formulario_editar');
                            document.getElementById('razon_hidden').value = razon;
                            document.getElementById('accion').value = 'devolver';

                            form.submit();
                        });
                    document.getElementById('cancelarDevolucion')
                        .addEventListener('click', () => {
                            Swal.close();
                        });
                }
            });
        });
    });

    function mostrar_modal() {
        const valor = document.getElementById("sb_tipo_actividad_modal").value;
        const grupoCoordinador = document.getElementById("grupo_coordinador");
        const grupoLider = document.getElementById("grupo_lider");

        if (valor === "RECONOCIMIENTO" || valor === "CONTROL_DE_CALIDAD") {
            grupoCoordinador.style.display = "block";
            grupoLider.style.display = "block";
        } else {
            grupoCoordinador.style.display = "none";
            grupoLider.style.display = "none";
        }
    }

    function mostrarPorque_modal() {
        const valor = document.getElementById("sb_transporte_modal").value;
        const grupoPorque = document.getElementById("grupo_porque");
        grupoPorque.style.display = (valor === "SI") ? "block" : "none";
    }

    function mostrarHospedaje_modal() {
        const valor = document.getElementById("sb_hospedaje_modal").value;
        const grupoHospedaje = document.getElementById("grupo_hospedaje");
        grupoHospedaje.style.display = (valor === "SI") ? "block" : "none";
    }

    document.addEventListener("DOMContentLoaded", function() {
        mostrar_modal();
        mostrarPorque_modal();
        mostrarHospedaje_modal();
    });

    document.addEventListener("DOMContentLoaded", function() {
        const sb_fechaInicio        = document.getElementById('sb_fecha_inicio_modal');
        const sb_fechaFin           = document.getElementById('sb_fecha_fin_modal');
        const sb_diasCalculados     = document.getElementById('sb_dias_calculados_modal');
        const sb_cobroDiario        = document.getElementById('sb_cobro_diario_modal');
        const sb_cuantoTransporte   = document.getElementById('sb_cuanto_transporte_modal');
        const sb_cuantoHospedaje    = document.getElementById('sb_cuanto_hospedaje_modal');
        const sb_valorCobrarVisible = document.getElementById('sb_valor_cobrar_visible');
        const sb_valorCobrar        = document.getElementById('sb_valor_cobrar');

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
            const dias              = limpiarNumero(sb_diasCalculados?.value);
            const valorPorDia       = limpiarNumero(sb_cobroDiario?.value);
            const valorTransporte   = limpiarNumero(sb_cuantoTransporte?.value);
            const valorHospedaje    = limpiarNumero(sb_cuantoHospedaje?.value);
            const totalDias         = dias * valorPorDia;
            const total             = totalDias + valorTransporte + valorHospedaje;
            if (sb_valorCobrarVisible) {
                sb_valorCobrarVisible.value = "$ " + total.toLocaleString("es-CO", {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
            }
            if (sb_valorCobrar) {
                sb_valorCobrar.value = total;
            }
        }

        function calcularDias() {
            if (!sb_fechaInicio.value || !sb_fechaFin.value) {
                sb_diasCalculados.value = '';
                calcularValorACobrar();
                return;
            }

            const inicio        = new Date(sb_fechaInicio.value + 'T00:00:00');
            const fin           = new Date(sb_fechaFin.value + 'T00:00:00');
            const diferencia    = (fin - inicio);
            const dias          = Math.floor(diferencia / (1000 * 60 * 60 * 24)) + 1;

            sb_diasCalculados.value = (dias > 0) ? dias : '';
            calcularValorACobrar();
        }

        sb_fechaInicio.addEventListener('change', calcularDias);
        sb_fechaFin.addEventListener('change', calcularDias);
        sb_cobroDiario.addEventListener('input', calcularValorACobrar);
        sb_cuantoTransporte.addEventListener('input', calcularValorACobrar);
        sb_cuantoHospedaje.addEventListener('input', calcularValorACobrar);

        calcularDias();
        calcularValorACobrar();
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
                document.getElementById('tab-seccionDos')?.click();
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
        document.querySelectorAll('.section-btn').forEach(btn => btn.classList.remove('active-section-btn'));
        const botones = document.querySelectorAll('.section-btn');
        if (botones[index - 1]) botones[index - 1].classList.add('active-section-btn');
    }

    document.addEventListener("DOMContentLoaded", function() {
        showSection(currentSectionIndex);
    });

    function mostrar_formulario_principal() {
        const transporte = document.getElementById("sb_transporte_mostrar")?.value?.trim().toUpperCase();
        const hospedaje = document.getElementById("sb_hospedaje_mostrar")?.value?.trim().toUpperCase();

        const grupoTransporte = document.getElementById("grupo_mostrar_transporte");
        const grupoHospedaje = document.getElementById("grupo_mostrar_hospedaje");

        if (transporte === "SI" && grupoTransporte) grupoTransporte.style.display = "block";
        if (hospedaje === "SI" && grupoHospedaje) grupoHospedaje.style.display = "block";
    }

    function mostrar_principal() {
        const tipoActividad = document.getElementById("sb_tipo_actividad_view")?.value?.trim().toUpperCase();
        const grupoReconocedor = document.getElementById("grupo_reconocedor");
        const grupoLider = document.getElementById("grupo_lider_principal");
        const mostrar = (tipoActividad === "RECONOCIMIENTO" || tipoActividad === "CONTROL_DE_CALIDAD");

        if (grupoReconocedor) grupoReconocedor.style.display = mostrar ? "block" : "none";
        if (grupoLider) grupoLider.style.display = mostrar ? "block" : "none";
    }

    document.addEventListener("DOMContentLoaded", function() {
        mostrar_formulario_principal();
        mostrar_principal();
    });

    document.addEventListener("DOMContentLoaded", function() {
        const fechaInicio = document.getElementById("sb_fecha_inicio_modal");
        const fechaFin = document.getElementById("sb_fecha_fin_modal");

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
                    title: "Fecha fuera de rango",
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
                    title: "Fecha fuera de rango",
                    text: "Solo puedes seleccionar fechas dentro de esta y la próxima semana.",
                    confirmButtonColor: "#002F55"
                });
                this.value = "";
            }
        });
    });
</script>

<script>
    (function() {
        const modalBody = document.querySelector('#modalPlanTrabajoNuevo .modalBodyPro');
        if (!modalBody) return;

        document.querySelectorAll('#modalPlanTrabajoNuevo [data-pro-pill]').forEach((btn) => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('#modalPlanTrabajoNuevo .modalPillPro')
                    .forEach((b) => b.classList.remove('active'));

                btn.classList.add('active');

                const targetId = btn.getAttribute('data-pro-pill');
                const target = document.getElementById(targetId);
                if (!target) return;

                const bodyRect = modalBody.getBoundingClientRect();
                const targetRect = target.getBoundingClientRect();
                const offset = 20;

                const scrollTop = modalBody.scrollTop + (targetRect.top - bodyRect.top) - offset;

                modalBody.scrollTo({
                    top: scrollTop,
                    behavior: 'smooth'
                });
            });
        });
    })();
</script>