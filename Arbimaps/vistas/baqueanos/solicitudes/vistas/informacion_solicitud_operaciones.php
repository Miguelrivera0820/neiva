<?php
$where = "";

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../index.php");
    exit();
}

$idUsuario = $_SESSION['id_usuario'];

$sql = "SELECT rol_usuario, nombre_usuario FROM usuarios_cons WHERE id_usuario = ?";
$stmtUser = $mysqli->prepare($sql);
$stmtUser->bind_param("i", $idUsuario);
$stmtUser->execute();
$stmtUser->bind_result($rolUsuarioDB, $nombreUsuarioDB);
$stmtUser->fetch();
$stmtUser->close();

$rolUsuario = $_SESSION['rol_usuario'];
$rolesPermitidos = array("administrador", "director_catastro", "Directivos", "soporte");

if (!in_array($rolUsuario, $rolesPermitidos)) {
    header("Location: ../../acceso_denegado.php");
    exit();
}

$nombre = $_SESSION['nombre_usuario'];

$idUsuario = $_SESSION['id_usuario'];
$id = $_GET['id'] ?? '';
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
$sb_profesional_baqueano    = $_POST['sb_profesional_baqueano'] ?? null;
$sb_cuanto_hospedaje        = $_POST['sb_cuanto_hospedaje'] ?? null;
$sb_cuanto_transporte       = $_POST['sb_cuanto_transporte'] ?? null;
$sb_reconocedor             = $_POST['sb_reconocedor'] ?? null;

$sql_cuentas = "SELECT * FROM solicitud_baqueanos WHERE id = ?";
$stmt_cuentas = $mysqli->prepare($sql_cuentas);
$stmt_cuentas->bind_param("i", $idUsuario);
$stmt_cuentas->execute();
$resultado_cuentas = $stmt_cuentas->get_result();

$datos_cuentas  = null;
$ruta_cuentas   = null;

if ($id) {
    $query = "
        SELECT 
            sb.*,
            ex.fecha_fin_nueva,
            ex.dias_agregados,
            ex.dias_nuevo_total,
            ex.valor_adicional,
            ex.valor_nuevo_total,
            ex.created_at AS ext_created_at,
            ex.observacion AS ext_observacion
        FROM solicitud_baqueanos sb
        LEFT JOIN (
            SELECT e1.*
            FROM solicitud_baqueanos_extensiones e1
            INNER JOIN (
                SELECT solicitud_id, MAX(id) AS max_id
                FROM solicitud_baqueanos_extensiones
                GROUP BY solicitud_id
            ) ult ON ult.solicitud_id = e1.solicitud_id AND ult.max_id = e1.id
        ) ex ON ex.solicitud_id = sb.id
        WHERE sb.id = ?
        LIMIT 1
    ";
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

        $ext_fecha_fin_nueva   = $row['fecha_fin_nueva'] ?? null;
        $ext_dias_agregados    = (int)($row['dias_agregados'] ?? 0);
        $ext_dias_nuevo_total  = (int)($row['dias_nuevo_total'] ?? 0);
        $ext_valor_adicional   = (int)($row['valor_adicional'] ?? 0);
        $ext_valor_nuevo_total = (int)($row['valor_nuevo_total'] ?? 0);
        $ext_created_at        = $row['ext_created_at'] ?? null;
        $ext_observacion       = $row['ext_observacion'] ?? '';

        $tieneExtension = ($ext_dias_agregados > 0);

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
        $sb_cuanto_hospedaje        = $row['sb_cuanto_hospedaje'] ?? 0;
        $sb_cuanto_transporte       = $row['sb_cuanto_transporte'] ?? 0;
        $sb_reconocedor             = $row['sb_reconocedor'] ?? '';
    } else {
        echo "No se encontraron datos para esta cédula.<br>";
        exit;
    }
}
$stmt_cuentas->close();
$stmt->close();
$mysqli->close();

$ext_valor_adicional   = $ext_valor_adicional ?? 0;
$ext_valor_nuevo_total = $ext_valor_nuevo_total ?? 0;
$ext_fecha_fin_nueva   = $ext_fecha_fin_nueva ?? null;
$ext_created_at        = $ext_created_at ?? null;
$ext_observacion       = $ext_observacion ?? '';
$tieneExtension        = $tieneExtension ?? false;
$ext_dias_agregados    = $ext_dias_agregados ?? 0;
$ext_dias_nuevo_total  = $ext_dias_nuevo_total ?? 0;

$extValorAdicionalFmt   = '$ ' . number_format($ext_valor_adicional, 0, ',', '.');
$extValorNuevoTotalFmt  = '$ ' . number_format($ext_valor_nuevo_total, 0, ',', '.');
$extFechaNuevaFmt       = $ext_fecha_fin_nueva ? date('Y-m-d', strtotime($ext_fecha_fin_nueva)) : '';
$extCreatedAtFmt        = $ext_created_at ? date('Y-m-d H:i', strtotime($ext_created_at)) : '';

$valorFormateado    = '$ ' . number_format((float)$sb_cobro_diario, 0, ',', '.');
$valorFormateado1   = '$ ' . number_format((float)$sb_valor_cobrar, 0, ',', '.');
$valorFormateadoHospedaje   = '$ ' . number_format((float)$sb_cuanto_hospedaje, 0, ',', '.');
$valorFormateadoTransporte  = '$ ' . number_format((float)$sb_cuanto_transporte, 0, ',', '.');
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

    #modalPlanTrabajo .modal-dialog {
        max-width: 1200px;
    }

    #modalPlanTrabajo .modalBodyPro {
        max-height: calc(100vh - 320px);
    }

    #modalPlanTrabajo .modalNavPro {
        padding: .55rem .9rem;
    }

    #modalPlanTrabajo .modalHeaderPro {
        padding: .85rem 1.1rem;
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

    .btn-swal-primary {
        background-color: #022F55 !important;
        color: #ffffff !important;
        border: none;
        border-radius: 12px;
        padding: .5rem 1.5rem;
        transition: .2s ease;
    }

    .btn-swal-primary:hover {
        background-color: #011f3a !important;
        transform: translateY(-1px);
    }
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div class="container-fluid">
    <div class="my-4 text-center">
        <h4 class="mb-0 fw-bold mb-2 text-brand" style="font-weight:700 !important;">SOLICITUD DE BAQUEANOS</h4>
        <small>Validación jefe de operaciones</small>
    </div>

    <form id="formulario_editar" action="./vistas/baqueanos/solicitudes/acciones/acciones_jefe_operaciones.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" id="id" name="id" value="<?php echo $id; ?>">
        <input type="hidden" id="accion" name="accion" value="">
        <input type="hidden" name="sb_razon_operaciones" id="razon_hidden">

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
                                        $nombreB   = trim($sb_baqueano_nombre ?? '');
                                        $apellidoB = trim($sb_baqueano_apellido ?? '');
                                        $fullB     = trim($nombreB . ' ' . $apellidoB);
                                        echo htmlspecialchars($fullB, ENT_QUOTES, 'UTF-8');
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
                <div class="card rounded-4 shadow-sm border-0 section-one-card" id="section1">
                    <div class="card-body p-4">
                        <div class="ticket-bite p-2 w-50 text-center text-white rounded-4 bg-brand" style="left:1%;">
                            <h6 class="fw-bold mb-0">Información general</h6>
                            <small>Aquí se muestra información general de quien solicitó</small>
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
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_baqueano_nombre">Nombre</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input class="form-control" id="sb_baqueano_nombre" value="<?php echo htmlspecialchars($sb_baqueano_nombre); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_baqueano_apellido">Apellido</label>
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

            <div class="tab-pane fade" id="pane-seccionDos" role="tabpanel" aria-labelledby="tab-seccionDos" tabindex="0">
                <div class="card rounded-4 shadow-sm border-0 section-one-card d-none mt-0" id="section2">
                    <div class="card-body p-4">
                        <div class="ticket-bite p-2 w-50 text-center text-white rounded-4 bg-brand" style="right:1%; left:auto;">
                            <h6 class="fw-bold mb-0">Plan de trabajo</h6>
                            <small>Información operativa y logística de la solicitud</small>
                        </div>

                        <div class="row mt-0">
                            <div class="col-12 mt-0 d-flex justify-content-start">
                                <h6 class="fw-bold p-2 text-white text-center rounded-3 mb-0"
                                    style="background-color:#002F55; width:fit-content; min-width:380px;">
                                    Responsables
                                </h6>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_profesional_baqueano"><b>Profesional Social</b></label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-person-workspace"></i></span>
                                    <input class="form-control" name="sb_profesional_baqueano" id="sb_profesional_baqueano" value="<?php echo htmlspecialchars($sb_profesional_baqueano); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_coordinador_view"><b>Coordinador</b></label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-person-workspace"></i></span>
                                    <input class="form-control" id="sb_coordinador_view" value="<?php echo htmlspecialchars($sb_coordinador); ?>" disabled>
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
                                    style="background-color:rgba(245,140,11,.81); width:fit-content; min-width:380px;">
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

                            <?php if ($tieneExtension): ?>
                                <div class="col-12 mt-2">
                                    <div class="card border-0 shadow-sm rounded-4" style="border-left:6px solid rgba(245,140,11,.9) !important;">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                <div>
                                                    <h6 class="fw-bold mb-1 text-brand">Extensión registrada</h6>
                                                    <small class="text-muted">Se agregaron <b><?php echo $ext_dias_agregados; ?></b> días</small>
                                                    <?php if ($extCreatedAtFmt): ?>
                                                        <div class="mt-1" style="font-size:12px;">
                                                            Registrada: <?php echo htmlspecialchars($extCreatedAtFmt); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">Extensión</span>
                                            </div>

                                            <div class="row g-3 mt-2">
                                                <div class="col-md-6 col-lg-4">
                                                    <label class="form-label fw-bold" style="font-size:0.9em;">Días agregados</label>
                                                    <div class="input-group shadow-sm rounded-2 precargado">
                                                        <span class="input-group-text"><i class="bi bi-plus-circle"></i></span>
                                                        <input class="form-control" value="<?php echo $ext_dias_agregados; ?>" disabled>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-lg-4">
                                                    <label class="form-label fw-bold" style="font-size:0.9em;">Nueva fecha fin</label>
                                                    <div class="input-group shadow-sm rounded-2 precargado">
                                                        <span class="input-group-text"><i class="bi bi-calendar2-plus"></i></span>
                                                        <input type="date" class="form-control" value="<?php echo htmlspecialchars($extFechaNuevaFmt); ?>" disabled>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-lg-4">
                                                    <label class="form-label fw-bold" style="font-size:0.9em;">Nuevo total de días</label>
                                                    <div class="input-group shadow-sm rounded-2 precargado">
                                                        <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                                        <input class="form-control" value="<?php echo $ext_dias_nuevo_total; ?>" disabled>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-lg-6">
                                                    <label class="form-label fw-bold" style="font-size:0.9em;">Valor adicional</label>
                                                    <div class="input-group shadow-sm rounded-2 precargado">
                                                        <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                                                        <input class="form-control" value="<?php echo htmlspecialchars($extValorAdicionalFmt); ?>" disabled>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-lg-6">
                                                    <label class="form-label fw-bold" style="font-size:0.9em;">Nuevo valor total a cobrar</label>
                                                    <div class="input-group shadow-sm rounded-2 precargado">
                                                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                                        <input class="form-control" value="<?php echo htmlspecialchars($extValorNuevoTotalFmt); ?>" disabled>
                                                    </div>
                                                </div>

                                                <?php if (!empty($ext_observacion)): ?>
                                                    <div class="col-12">
                                                        <label class="form-label fw-bold" style="font-size:0.9em;">Observación de la extensión</label>
                                                        <div class="input-group shadow-sm rounded-2 precargado">
                                                            <span class="input-group-text"><i class="bi bi-chat-left-text"></i></span>
                                                            <input class="form-control" value="<?php echo htmlspecialchars($ext_observacion); ?>" disabled>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="col-12 my-4 border-bottom" style="border-bottom:2px dashed #002f557a !important;"></div>

                            <div class="col-12 mt-0 d-flex justify-content-start">
                                <h6 class="fw-bold p-2 text-white text-center rounded-3 mb-0"
                                    style="background-color:#198754; width:fit-content; min-width:380px;">
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

                            <div class="col-md-6 col-lg-6 p-1 px-2 my-3" id="grupo_lider_view" style="display:none;">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_lider_cuadrilla_view"><b>Líder de Cuadrilla</b></label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-person-check"></i></span>
                                    <input class="form-control" id="sb_lider_cuadrilla_view" value="<?php echo htmlspecialchars($sb_lider_cuadrilla); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-6 p-1 px-2 my-3" id="grupo_reconocedor_view" style="display:none;">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_reconocedor_view"><b>Reconocedor</b></label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-person-lines-fill"></i></span>
                                    <input class="form-control" id="sb_reconocedor_view" value="<?php echo htmlspecialchars($sb_reconocedor); ?>" disabled>
                                </div>
                            </div>

                            <div class="col-12 my-4 border-bottom" style="border-bottom:2px dashed #002f557a !important;"></div>

                            <div class="col-12 mt-0 d-flex justify-content-end">
                                <h6 class="fw-bold p-2 text-white text-center rounded-3 mb-0"
                                    style="background-color:#002F55; width:fit-content; min-width:380px;">
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

                            <div class="col-12 col-lg-8 p-0 m-0" id="grupo_mostrar_transporte" style="display:none;">
                                <div class="row g-0">
                                    <div class="col-md-6 col-lg-6 p-1 px-2 my-3">
                                        <label class="form-label fw-bold" style="font-size:0.9em;"><b>¿Por qué?</b></label>
                                        <div class="input-group shadow-sm rounded-2 precargado">
                                            <span class="input-group-text"><i class="bi bi-question-circle"></i></span>
                                            <input class="form-control" value="<?php echo htmlspecialchars($sb_porque_transporte); ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-6 p-1 px-2 my-3" id="campo_cuanto_transporte" style="display:none;">
                                        <label class="form-label fw-bold" style="font-size:0.9em;"><b>¿Cuánto?</b></label>
                                        <div class="input-group shadow-sm rounded-2 precargado">
                                            <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                                            <!-- FIX: ahora muestra el valor correcto de transporte -->
                                            <input class="form-control" value="<?php echo htmlspecialchars($valorFormateadoTransporte); ?>" disabled>
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

                            <div class="col-12 col-lg-8 p-0 m-0" id="grupo_mostrar_hospedaje" style="display:none;">
                                <div class="row g-0">
                                    <div class="col-md-6 col-lg-6 p-1 px-2 my-3">
                                        <label class="form-label fw-bold" style="font-size:0.9em;"><b>¿Por qué?</b></label>
                                        <div class="input-group shadow-sm rounded-2 precargado">
                                            <span class="input-group-text"><i class="bi bi-question-circle"></i></span>
                                            <input class="form-control" value="<?php echo htmlspecialchars($sb_porque_hospedaje); ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-6 p-1 px-2 my-3" id="campo_cuanto_hospedaje" style="display:none;">
                                        <label class="form-label fw-bold" style="font-size:0.9em;"><b>¿Cuánto?</b></label>
                                        <div class="input-group shadow-sm rounded-2 precargado">
                                            <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                                            <!-- FIX: ahora muestra el valor correcto de hospedaje -->
                                            <input class="form-control" value="<?php echo htmlspecialchars($valorFormateadoHospedaje); ?>" disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-center align-items-center flex-wrap" style="gap:.75rem;">
                            <button type="button" class="btn btn-light border d-inline-flex align-items-center justify-content-center rounded-circle shadow-sm btn-circle"
                                data-toggle="modal" data-target="#modalPlanTrabajo" title="Editar">
                                <i class="bi bi-pencil fs-5"></i>
                            </button>

                            <button type="button" id="btn-aprobar"
                                class="btn btn-outline-success d-inline-flex align-items-center justify-content-center rounded-circle shadow-sm btn-circle btn-circle-approve"
                                onclick="accionFormulario('aprobar')" title="Aprobar">
                                <i class="bi bi-check-circle fs-5"></i>
                            </button>

                            <button type="button" id="btn-devolver"
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

                                    <label for="sb_razon_operaciones" class="form-label fw-bold" style="font-size:0.9em;">Observación</label>
                                    <div class="input-group shadow-sm rounded-2 precargado">
                                        <span class="input-group-text"><i class="bi bi-chat-left-text"></i></span>
                                        <textarea class="form-control" id="sb_razon_operaciones" rows="3"
                                            placeholder="Escriba la razón por la cual devuelve la solicitud"></textarea>
                                    </div>

                                    <input type="hidden" id="correo" value="correo@ejemplo.com">
                                </div>
                            </div>
                        </div>

                        <!-- MODAL -->
                        <div class="modal fade" id="modalPlanTrabajo" tabindex="-1" role="dialog" aria-labelledby="modalPlanTrabajoLabel" aria-hidden="true">
                            <div class="modal-dialog modal-xl" role="document">
                                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden modalPro">
                                    <div id="formulario_editar_modal">
                                        <div class="modal-header modalHeaderPro position-relative d-flex align-items-center">
                                            <div class="position-absolute top-50 start-50 translate-middle text-center">
                                                <h5 class="modal-title mb-0 fw-bold text-white" id="modalPlanTrabajoLabel">PLAN DE TRABAJO</h5>
                                                <small class="text-white-50">Ajusta fechas, valores, ubicación y logística</small>
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
                                            <input type="hidden" name="id" id="modal_id">
                                            <input type="hidden" name="id" id="id_modal" value="<?php echo $id; ?>">

                                            <section id="sec-fechas" class="modalSectionPro">
                                                <div class="modalSectionHeadPro">
                                                    <div class="modalSectionIconPro" style="background:rgba(245,140,11,.15); color:rgba(245,140,11,1);">
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

                                                    <div class="col-md-6 mx-auto">
                                                        <label class="form-label fw-bold text-center fw-bold" style="font-size:0.9em;" for="sb_valor_cobrar_visible">Total a Cobrar</label>
                                                        <div class="input-group shadow-sm rounded-3 precargado">
                                                            <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                                            <input type="text" class="form-control" id="sb_valor_cobrar_visible" readonly>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="sb_valor_cobrar" id="sb_valor_cobrar">
                                                </div>
                                                <div class="modalDividerPro"></div>

                                                <div class="modalSectionHeadPro">
                                                    <div class="modalSectionIconPro" style="background:rgba(0,47,85,.15); color:rgba(0,47,85,1);">
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
                                                        <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_num_cuenta_modal">N° Cuenta</label>
                                                        <div class="input-group shadow-sm rounded-3 precargado">
                                                            <span class="input-group-text"><i class="bi bi-bank"></i></span>
                                                            <input class="form-control" name="sb_num_cuenta" id="sb_num_cuenta_modal"
                                                                value="<?php echo htmlspecialchars((string)$sb_num_cuenta); ?>"
                                                                placeholder="Ej: 0123456789">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_titular_modal">Titular</label>
                                                        <div class="input-group shadow-sm rounded-3 precargado">
                                                            <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                                            <input class="form-control" name="sb_titular" id="sb_titular_modal"
                                                                value="<?php echo htmlspecialchars((string)$sb_titular); ?>"
                                                                placeholder="Nombre del titular">
                                                        </div>
                                                    </div>
                                                </div>
                                            </section>

                                            <div class="modalDividerPro"></div>

                                            <section id="sec-ubicacion" class="modalSectionPro">
                                                <div class="modalSectionHeadPro">
                                                    <div class="modalSectionIconPro" style="background:rgba(25,135,84,.15); color:rgba(25,135,84,1);">
                                                        <i class="bi bi-geo-alt"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="fw-bold mb-0" style="color:#111;">Ubicación y actividad</h6>
                                                        <small class="text-muted">Zona, unidad y responsables</small>
                                                    </div>
                                                </div>

                                                <div class="row g-3 mt-2">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_unidad_intervencion">Unidad de Intervención</label>
                                                        <div class="input-group shadow-sm rounded-3 precargado">
                                                            <span class="input-group-text"><i class="bi bi-diagram-3"></i></span>
                                                            <input class="form-control" name="sb_unidad_intervencion" id="sb_unidad_intervencion" value="<?php echo $sb_unidad_intervencion; ?>">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_unidad_operativa">Unidad Operativa</label>
                                                        <div class="input-group shadow-sm rounded-3 precargado">
                                                            <span class="input-group-text"><i class="bi bi-building"></i></span>
                                                            <select class="custom-select form-control" name="sb_unidad_operativa" id="sb_unidad_operativa">
                                                                <option value="<?php echo $sb_unidad_operativa; ?>"><?php echo $sb_unidad_operativa; ?></option>
                                                                <option value="BARRIO">BARRIO</option>
                                                                <option value="VEREDA">VEREDA</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_tipo_unidad">Tipo de Unidad</label>
                                                        <div class="input-group shadow-sm rounded-3 precargado">
                                                            <span class="input-group-text"><i class="bi bi-tags"></i></span>
                                                            <select class="custom-select form-control" name="sb_tipo_unidad" id="sb_tipo_unidad">
                                                                <option value="<?php echo $sb_tipo_unidad; ?>"><?php echo $sb_tipo_unidad; ?></option>
                                                                <option value="URBANA">URBANA</option>
                                                                <option value="RURAL">RURAL</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_vereda">Vereda/Barrio</label>
                                                        <div class="input-group shadow-sm rounded-3 precargado">
                                                            <span class="input-group-text"><i class="bi bi-pin-map"></i></span>
                                                            <input class="form-control" name="sb_vereda" id="sb_vereda" value="<?php echo $sb_vereda; ?>">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_tipo_actividad">Tipo de Actividad</label>
                                                        <div class="input-group shadow-sm rounded-3 precargado">
                                                            <span class="input-group-text"><i class="bi bi-clipboard-check"></i></span>
                                                            <select class="custom-select form-control" id="sb_tipo_actividad" name="sb_tipo_actividad" onchange="mostrar()" required>
                                                                <option value="<?php echo $sb_tipo_actividad; ?>"><?php echo $sb_tipo_actividad; ?></option>
                                                                <option value="ATENCION_A_SALDOS">ATENCION A SALDOS</option>
                                                                <option value="ATENCION_PQRS">ATENCION PQRS</option>
                                                                <option value="OBSERVACION_INTERVENTORIA">OBSERVACION INTERVENTORIA</option>
                                                                <option value="RECONOCIMIENTO">RECONOCIMIENTO</option>
                                                                <option value="ACOMPAÑAMIENTO_SOCIAL">ACOMPAÑAMIENTO SOCIAL</option>
                                                                <option value="CONTROL_DE_CALIDAD">CONTROL DE CALIDAD</option>
                                                                <option value="INTERLOCUCIÓN">INTERLOCUCIÓN</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6" id="grupo_coordinador_modal" style="display:none;">
                                                        <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_coordinador"><b>Coordinador</b></label>
                                                        <div class="input-group shadow-sm rounded-3 precargado">
                                                            <span class="input-group-text"><i class="bi bi-person-workspace"></i></span>
                                                            <input class="form-control" name="sb_coordinador" id="sb_coordinador" value="<?php echo $sb_coordinador; ?>">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6" id="grupo_lider_modal" style="display:none;">
                                                        <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_lider_cuadrilla"><b>Líder de Cuadrilla</b></label>
                                                        <div class="input-group shadow-sm rounded-3 precargado">
                                                            <span class="input-group-text"><i class="bi bi-person-check"></i></span>
                                                            <input class="form-control" name="sb_lider_cuadrilla" id="sb_lider_cuadrilla" value="<?php echo $sb_lider_cuadrilla; ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </section>

                                            <div class="modalDividerPro"></div>

                                            <section id="sec-logistica" class="modalSectionPro">
                                                <div class="modalSectionHeadPro">
                                                    <div class="modalSectionIconPro" style="background:rgba(0,47,85,.15); color:rgba(0,47,85,1);">
                                                        <i class="bi bi-truck"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="fw-bold mb-0" style="color:#111;">Logística</h6>
                                                        <small class="text-muted">Transporte, hospedaje y justificaciones</small>
                                                    </div>
                                                </div>

                                                <div class="row g-3 mt-2">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_transporte">Transporte</label>
                                                        <div class="input-group shadow-sm rounded-3 precargado">
                                                            <span class="input-group-text"><i class="bi bi-truck"></i></span>
                                                            <select class="custom-select form-control" id="sb_transporte" name="sb_transporte" onchange="mostrarPorqueModal()">
                                                                <option value="<?php echo $sb_transporte; ?>"><?php echo $sb_transporte; ?></option>
                                                                <option value="SI">SI</option>
                                                                <option value="NO">NO</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6" id="grupo_porque_modal" style="display:none;">
                                                        <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_porque_transporte"><b>¿Por qué?</b></label>
                                                        <div class="input-group shadow-sm rounded-3 precargado">
                                                            <span class="input-group-text"><i class="bi bi-question-circle"></i></span>
                                                            <input class="form-control" name="sb_porque_transporte" id="sb_porque_transporte" value="<?php echo $sb_porque_transporte; ?>">
                                                        </div>
                                                    </div>

                                                    <!-- NUEVO: ¿Cuánto transporte? -->
                                                    <div class="col-md-6" id="grupo_cuanto_transporte_modal" style="display:none;">
                                                        <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_cuanto_transporte"><b>¿Cuánto?</b></label>
                                                        <div class="input-group shadow-sm rounded-3 precargado">
                                                            <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                                                            <input class="form-control" name="sb_cuanto_transporte" id="sb_cuanto_transporte"
                                                                value="<?php echo htmlspecialchars((string)$sb_cuanto_transporte); ?>">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_hospedaje">Hospedaje</label>
                                                        <div class="input-group shadow-sm rounded-3 precargado">
                                                            <span class="input-group-text"><i class="bi bi-house-door"></i></span>
                                                            <select class="custom-select form-control" id="sb_hospedaje" name="sb_hospedaje" onchange="mostrarHospedajeModal()">
                                                                <option value="<?php echo $sb_hospedaje; ?>"><?php echo $sb_hospedaje; ?></option>
                                                                <option value="SI">SI</option>
                                                                <option value="NO">NO</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6" id="grupo_hospedaje_modal" style="display:none;">
                                                        <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_porque_hospedaje"><b>¿Por qué?</b></label>
                                                        <div class="input-group shadow-sm rounded-3 precargado">
                                                            <span class="input-group-text"><i class="bi bi-question-circle"></i></span>
                                                            <input class="form-control" name="sb_porque_hospedaje" id="sb_porque_hospedaje" value="<?php echo $sb_porque_hospedaje; ?>">
                                                        </div>
                                                    </div>

                                                    <!-- NUEVO: ¿Cuánto hospedaje? -->
                                                    <div class="col-md-6" id="grupo_cuanto_hospedaje_modal" style="display:none;">
                                                        <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_cuanto_hospedaje"><b>¿Cuánto?</b></label>
                                                        <div class="input-group shadow-sm rounded-3 precargado">
                                                            <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                                                            <input class="form-control" name="sb_cuanto_hospedaje" id="sb_cuanto_hospedaje"
                                                                value="<?php echo htmlspecialchars((string)$sb_cuanto_hospedaje); ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </section>
                                        </div>

                                        <div class="modal-footer modalFooterPro">
                                            <button type="button" id="btnGuardarCambiosModal" class="btn btn-brand px-4 d-inline-flex align-items-center gap-2">
                                                <i class="bi bi-cloud-arrow-up"></i>
                                                <b>GUARDAR CAMBIOS</b>
                                            </button>
                                            <button type="button" class="btn btn-light border px-4" data-dismiss="modal">CERRAR</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /MODAL -->

                    </div>
                </div>
            </div>
        </div>
    </form>
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

    function accionFormulario(accion) {
        if (accion === 'devolver') {
            document.getElementById('campo-observacion').style.display = 'block';
            document.getElementById('sb_razon_operaciones').focus();
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
                    <div class="swal-saved-header text-brand">
                        <i class="bi bi-check-circle-fill"></i>
                        Confirmar aprobación
                    </div>
                    <div class="swal-saved-divider"></div>
                    <div class="swal-saved-body">
                        <i class="bi bi-patch-question-fill text-brand" 
                            style="font-size:75px;opacity:.15;"></i>
                        <div class="swal-saved-text">
                            La solicitud será aprobada definitivamente.
                        </div>
                        <div class="d-flex justify-content-center gap-3 mt-4">
                            <button id="confirmarAprobacion" 
                                class="btn-swal-primary">
                                Sí, aprobar
                            </button>
                            <button onclick="Swal.close()" 
                                class="btn btn-light border rounded-3 px-4">
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
            const razon = document.getElementById('sb_razon_operaciones').value.trim();
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
                            <div class="swal-saved-header" style="color:#dc3545;">
                                <i class="bi bi-x-circle-fill"></i>
                                Error
                            </div>
                            <div class="swal-saved-divider"></div>
                            <div class="swal-saved-body">
                                <i class="bi bi-exclamation-triangle-fill text-danger" 
                                    style="font-size:75px;opacity:.15;"></i>
                                <div class="swal-saved-text">
                                    Debe escribir una razón por la cual devuelve la solicitud.
                                </div>
                                <div class="mt-4">
                                    <button onclick="Swal.close()" 
                                        class="btn-swal-primary">
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
                        <div class="swal-saved-header" style="color:#dc3545;">
                            <i class="bi bi-reply-fill"></i>
                            Confirmar devolución
                        </div>
                        <div class="swal-saved-divider"></div>
                        <div class="swal-saved-body">
                            <i class="bi bi-exclamation-circle-fill text-danger" 
                                style="font-size:75px;opacity:.15;"></i>
                            <div class="swal-saved-text">
                                Esta acción devolverá la solicitud.
                            </div>
                            <div class="d-flex justify-content-center gap-3 mt-4">
                                <button id="confirmarDevolucion" 
                                    class="btn-swal-primary">
                                    Sí, devolver
                                </button>
                                <button onclick="Swal.close()" 
                                    class="btn btn-light border rounded-3 px-4">
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

    // Mostrar reconocedor/lider SOLO en la vista
    document.addEventListener("DOMContentLoaded", function() {
        const tipoActividad = document.getElementById("sb_tipo_actividad")?.value;
        const grupoReconocedor = document.getElementById("grupo_reconocedor_view");
        const grupoLider = document.getElementById("grupo_lider_view");
        const esActividadRequerida = tipoActividad === "RECONOCIMIENTO" || tipoActividad === "CONTROL_DE_CALIDAD";

        if (esActividadRequerida) {
            if (grupoReconocedor) grupoReconocedor.style.display = "block";
            if (grupoLider) grupoLider.style.display = "block";
        }
    });

    // Validación rango fecha modal
    document.addEventListener("DOMContentLoaded", function() {
        const fechaInicio = document.getElementById("sb_fecha_inicio");
        const fechaFin = document.getElementById("sb_fecha_fin");
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);

        const diasPermitidos = 14;
        const fechaMax = new Date(hoy);
        fechaMax.setDate(hoy.getDate() + diasPermitidos - 1);

        const fechaMaxStr = fechaMax.toISOString().split("T")[0];
        const fechaHoyStr = hoy.toISOString().split("T")[0];

        if (fechaInicio && fechaFin) {
            fechaInicio.min = fechaHoyStr;
            fechaInicio.max = fechaMaxStr;
            fechaFin.min = fechaHoyStr;
            fechaFin.max = fechaMaxStr;
        }
    });

    // Mostrar bloques en la vista (disabled)
    document.addEventListener("DOMContentLoaded", function() {
        function mostrarPorqueYCuantoTransporte() {
            const transporte = document.getElementById("sb_transporte_mostrar")?.value;
            const grupoPorque = document.getElementById("grupo_mostrar_transporte");
            const campoCuanto = document.getElementById("campo_cuanto_transporte");

            if (transporte === "SI") {
                if (grupoPorque) grupoPorque.style.display = "block";
                if (campoCuanto) campoCuanto.style.display = "block";
            }
        }

        function mostrarPorqueYCuantoHospedaje() {
            const hospedaje = document.getElementById("sb_hospedaje_mostrar")?.value;
            const grupoPorque = document.getElementById("grupo_mostrar_hospedaje");
            const campoCuanto = document.getElementById("campo_cuanto_hospedaje");

            if (hospedaje === "SI") {
                if (grupoPorque) grupoPorque.style.display = "block";
                if (campoCuanto) campoCuanto.style.display = "block";
            }
        }

        mostrarPorqueYCuantoTransporte();
        mostrarPorqueYCuantoHospedaje();
    });

    // Submit por fetch (editar)
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formulario_editar');
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            document.getElementById('accion').value = 'editar';
            const formData = new FormData(form);

            fetch("./vistas/baqueanos/solicitudes/acciones/acciones_jefe_operaciones.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire("Éxito", data.message, "success").then(() => {
                            window.location.href = "<?= neiva_app_url('Arbimaps/index.php?page=baqueanos/solicitudes/vistas/validar_solicitud') ?>";
                        });
                    } else {
                        Swal.fire("Error", data.message, "error");
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    Swal.fire("Error", "Hubo un problema al enviar la solicitud", "error");
                });
        });
    });

    // Calcular total
    document.addEventListener('DOMContentLoaded', function() {
        const sb_fechaInicio = document.getElementById('sb_fecha_inicio');
        const sb_fechaFin = document.getElementById('sb_fecha_fin');
        const sb_diasCalculados = document.getElementById('sb_dias_calculados');
        const sb_cobroDiario = document.getElementById('sb_cobro_diario');
        const sb_cuantoTransporte = document.getElementById('sb_cuanto_transporte');
        const sb_cuantoHospedaje = document.getElementById('sb_cuanto_hospedaje');
        const sb_totalVisible = document.getElementById('sb_valor_cobrar_visible');
        const sb_totalHidden = document.getElementById('sb_valor_cobrar');

        if (!sb_fechaInicio || !sb_fechaFin || !sb_diasCalculados || !sb_cobroDiario || !sb_totalVisible || !sb_totalHidden) {
            console.warn("Faltan inputs para cálculo en el modal.");
            return;
        }

        function parseCOP(texto) {
            if (texto === null || texto === undefined) return 0;
            let v = String(texto).trim();
            if (v === '') return 0;
            v = v.replace(/[^\d,.\-]/g, '');
            v = v.replace(/\.(?=\d{3}(?:\D|$))/g, '');
            v = v.replace(/,(?=\d{3}(?:\D|$))/g, '');
            v = v.replace(',', '.');
            const n = parseFloat(v);
            return isNaN(n) ? 0 : n;
        }

        function calcularValorACobrar() {
            const dias = parseFloat(sb_diasCalculados.value) || 0;
            const valorPorDia = parseCOP(sb_cobroDiario.value);
            const valorTransporte = parseCOP(sb_cuantoTransporte ? sb_cuantoTransporte.value : 0);
            const valorHospedaje = parseCOP(sb_cuantoHospedaje ? sb_cuantoHospedaje.value : 0);
            const totalDias = dias * valorPorDia;
            const total = totalDias + valorTransporte + valorHospedaje;

            sb_totalVisible.value = total.toLocaleString("es-CO", {
                style: "currency",
                currency: "COP",
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
            sb_totalHidden.value = Math.round(total);
        }

        function calcularDias() {
            if (!sb_fechaInicio.value || !sb_fechaFin.value) {
                sb_diasCalculados.value = '';
                calcularValorACobrar();
                return;
            }
            const inicio = new Date(sb_fechaInicio.value + "T00:00:00");
            const fin = new Date(sb_fechaFin.value + "T00:00:00");
            const diff = fin - inicio;
            const dias = Math.floor(diff / (1000 * 60 * 60 * 24)) + 1;
            if (dias > 0) sb_diasCalculados.value = dias;
            else sb_diasCalculados.value = '';
            calcularValorACobrar();
        }
        sb_cobroDiario.addEventListener('input', calcularValorACobrar);

        if (sb_cuantoTransporte) sb_cuantoTransporte.addEventListener('input', calcularValorACobrar);
        if (sb_cuantoHospedaje) sb_cuantoHospedaje.addEventListener('input', calcularValorACobrar);

        sb_fechaInicio.addEventListener('change', calcularDias);
        sb_fechaFin.addEventListener('change', calcularDias);
        sb_fechaInicio.addEventListener('input', calcularDias);
        sb_fechaFin.addEventListener('input', calcularDias);

        calcularDias();
        calcularValorACobrar();
    });



    // Mostrar campos actividad en MODAL (usa IDs del modal)
    function mostrar() {
        const valor = document.getElementById("sb_tipo_actividad")?.value;
        const grupoCoordinador = document.getElementById("grupo_coordinador_modal");
        const grupoLider = document.getElementById("grupo_lider_modal");

        if (!grupoCoordinador || !grupoLider) return;

        if (valor === "RECONOCIMIENTO" || valor === "CONTROL_DE_CALIDAD") {
            grupoCoordinador.style.display = "block";
            grupoLider.style.display = "block";
        } else {
            grupoCoordinador.style.display = "none";
            grupoLider.style.display = "none";
        }
    }
    document.addEventListener("DOMContentLoaded", mostrar);

    // Mostrar/ocultar logística en MODAL (incluye cuanto)
    function mostrarPorqueModal() {
        const valor = document.getElementById("sb_transporte")?.value;
        const grupoPorque = document.getElementById("grupo_porque_modal");
        const grupoCuanto = document.getElementById("grupo_cuanto_transporte_modal");
        const inputPorque = document.getElementById("sb_porque_transporte");
        const inputCuanto = document.getElementById("sb_cuanto_transporte");

        if (!grupoPorque || !grupoCuanto) return;
        if (valor === "SI") {
            grupoPorque.style.display = "block";
            grupoCuanto.style.display = "block";
        } else {
            grupoPorque.style.display = "none";
            grupoCuanto.style.display = "none";
            if (inputPorque) inputPorque.value = "";
            if (inputCuanto) inputCuanto.value = "";
        }
        const evento = new Event('input');
        if (inputCuanto) inputCuanto.dispatchEvent(evento);
    }

    function mostrarHospedajeModal() {
        const valor = document.getElementById("sb_hospedaje")?.value;
        const grupoHospedaje = document.getElementById("grupo_hospedaje_modal");
        const grupoCuanto = document.getElementById("grupo_cuanto_hospedaje_modal");
        const inputPorque = document.getElementById("sb_porque_hospedaje");
        const inputCuanto = document.getElementById("sb_cuanto_hospedaje");

        if (!grupoHospedaje || !grupoCuanto) return;
        if (valor === "SI") {
            grupoHospedaje.style.display = "block";
            grupoCuanto.style.display = "block";
        } else {
            grupoHospedaje.style.display = "none";
            grupoCuanto.style.display = "none";
            if (inputPorque) inputPorque.value = "";
            if (inputCuanto) inputCuanto.value = "";
        }
        const evento = new Event('input');
        if (inputCuanto) inputCuanto.dispatchEvent(evento);
    }



    document.addEventListener("DOMContentLoaded", function() {
        mostrarPorqueModal();
        mostrarHospedajeModal();
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

        if (index === 1) document.getElementById('tab-seccionUno')?.click();
        if (index === 2) document.getElementById('tab-seccionDos')?.click();
    }

    document.addEventListener("DOMContentLoaded", function() {
        showSection(currentSectionIndex);
    });

    (function() {
        const modalBody = document.querySelector('#modalPlanTrabajo .modalBodyPro');
        if (!modalBody) return;

        document.querySelectorAll('#modalPlanTrabajo [data-pro-pill]').forEach((btn) => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('#modalPlanTrabajo .modalPillPro')
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


    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('btnGuardarCambiosModal');
        if (!btn) return;

        btn.addEventListener('click', function() {
            document.getElementById('accion').value = 'editar';
            document.getElementById('formulario_editar').requestSubmit();
        });
    });
</script>