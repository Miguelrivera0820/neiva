<?php
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../index.php");
    exit();
}

$idUsuario                  = $_SESSION['id_usuario'];
$id                         = $_GET['id'] ?? '';
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
$sb_reconocedor             = $_POST['sb_reconocedor'] ?? null;
$sb_cuanto_hospedaje        = $_POST['sb_cuanto_hospedaje'] ?? null;
$sb_cuanto_transporte       = $_POST['sb_cuanto_transporte'] ?? null;

// Consulta para obtener las cuentas radicadas del usuario autenticado
$sql_cuentas = "SELECT * FROM solicitud_baqueanos WHERE id = ?";
$stmt_cuentas = $mysqli->prepare($sql_cuentas);
$stmt_cuentas->bind_param("i", $idUsuario);
$stmt_cuentas->execute();
$resultado_cuentas = $stmt_cuentas->get_result();

// Inicializar variables
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
        $sb_numero_identidad        = $row['sb_numero_identidad']       ?? '';
        $sb_tipo_documento          = $row['sb_tipo_documento']         ?? '';
        $sb_baqueano_nombre         = $row['sb_baqueano_nombre']        ?? '';
        $sb_baqueano_apellido       = $row['sb_baqueano_apellido']      ?? '';
        $sb_telefono_baqueano       = $row['sb_telefono_baqueano']      ?? '';
        $sb_correo_baqueano         = $row['sb_correo_baqueano']        ?? '';
        $sb_direccion               = $row['sb_direccion']              ?? '';
        $sb_cuenta                  = $row['sb_cuenta']                 ?? '';
        $sb_tipo_cuenta             = $row['sb_tipo_cuenta']            ?? '';
        $sb_num_cuenta              = $row['sb_num_cuenta']             ?? '';
        $sb_titular                 = $row['sb_titular']                ?? '';
        $sb_year                    = $row['sb_year']                   ?? '';
        $sb_fecha_inicio            = $row['sb_fecha_inicio']           ?? '';
        $sb_fecha_fin               = $row['sb_fecha_fin']              ?? '';
        $sb_dias_calculados         = $row['sb_dias_calculados']        ?? '';
        $sb_cobro_diario            = $row['sb_cobro_diario']           ?? '';
        $sb_valor_cobrar            = $row['sb_valor_cobrar']           ?? 0;
        $sb_unidad_intervencion     = $row['sb_unidad_intervencion']    ?? '';
        $sb_unidad_operativa        = $row['sb_unidad_operativa']       ?? '';
        $sb_tipo_unidad             = $row['sb_tipo_unidad']            ?? '';
        $sb_municipio               = $row['sb_municipio']              ?? '';
        $sb_vereda                  = $row['sb_vereda']                 ?? '';
        $sb_tipo_actividad          = $row['sb_tipo_actividad']         ?? '';
        $sb_coordinador             = $row['sb_coordinador']            ?? '';
        $sb_lider_cuadrilla         = $row['sb_lider_cuadrilla']        ?? '';
        $sb_transporte              = $row['sb_transporte']             ?? '';
        $sb_porque_transporte       = $row['sb_porque_transporte']      ?? '';
        $sb_hospedaje               = $row['sb_hospedaje']              ?? '';
        $sb_porque_hospedaje        = $row['sb_porque_hospedaje']       ?? '';
        $sb_profesional_baqueano    = $row['sb_profesional_baqueano']   ?? '';
        $sb_reconocedor             = $row['sb_reconocedor']            ?? '';
        $sb_cuanto_hospedaje        = $row['sb_cuanto_hospedaje']       ?? '';
        $sb_cuanto_transporte       = $row['sb_cuanto_transporte']      ?? '';
    } else {
        echo "No se encontraron datos para esta cédula.<br>";
        exit;
    }
}

$sql_estado = "SELECT sb_estado_operaciones, sb_estado_gerencia FROM solicitud_baqueanos WHERE id = ?";
$stmt_estado = $mysqli->prepare($sql_estado);
$stmt_estado->bind_param("i", $id);
$stmt_estado->execute();
$result = $stmt_estado->get_result();

if ($rowEstado = $result->fetch_assoc()) {
    if ($rowEstado['sb_estado_operaciones'] === 'APROBADO' || $rowEstado['sb_estado_gerencia'] === 'APROBADO') {
        echo json_encode([
            'success' => false,
            'message' => 'La solicitud no puede editarse porque ya fue aprobada por el jefe de operaciones o gerencia.'
        ]);
        exit;
    }
}

$stmt_cuentas->close();
$stmt->close();
$mysqli->close();

$valorFormateado    = '$ ' . number_format($sb_cobro_diario, 0, ',', '.');
$valorFormateado1   = '$ ' . number_format($sb_valor_cobrar, 0, ',', '.');
$valorFormateado3   = '$ ' . number_format($sb_cuanto_hospedaje, 0, ',', '.');
$valorFormateado4   = '$ ' . number_format($sb_cuanto_transporte, 0, ',', '.');
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

    .precargado {
        border: 1px solid #002F55;
        box-shadow: 0 0 10px #002f5517 !important;
    }

    .precargado input,
    .precargado select,
    .precargado textarea {
        background-color: #002f5517 !important;
        font-weight: 500;
    }

    .section-one-card {
        border: 1px solid rgba(0, 47, 85, .20) !important;
        box-shadow: 0 14px 34px rgba(0, 47, 85, .14) !important;
        transition: transform .18s ease, box-shadow .18s ease;
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
    .tab-content {
        overflow: visible !important;
    }

    .campo-incompleto {
        border: 1px solid #dc3545 !important;
        box-shadow: 0 0 0 .15rem rgba(220, 53, 69, .15) !important;
    }

    .ticket-spacer {
        height: 20px;
    }

    #btnEditar:hover,
    #btnEditar:focus {
        background-color: #002F55 !important;
        border-color: #002F55 !important;
        color: #fff !important;
    }

    #btnEditar:hover *,
    #btnEditar:focus * {
        color: #fff !important;
    }

    #btnEditar:focus {
        box-shadow: 0 0 0 .25rem rgba(0, 47, 85, .25) !important;
    }

    #btnEditar:active {
        transform: scale(0.98);
    }

    /* estilos para las alertas */
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

    .solo-lectura-badge {
        background: rgba(0, 47, 85, 0.08);
        color: #002F55;
        border: 1px solid rgba(0, 47, 85, 0.25);
        font-size: 0.85rem;
        letter-spacing: .3px;
        backdrop-filter: blur(4px);
        box-shadow: 0 6px 18px rgba(0, 47, 85, 0.12);
        transition: all .3s ease;
    }

    .solo-lectura-badge i {
        font-size: 0.9rem;
    }

    @keyframes flotacionInstitucional {
        0% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-4px);
        }

        100% {
            transform: translateY(0px);
        }
    }

    .flotando {
        animation: flotacionInstitucional 3.5s ease-in-out infinite;
    }

    #pane-seccionDos.solo-lectura input:not([type="hidden"]),
    #pane-seccionDos.solo-lectura textarea {
        background-color: #f3f4f6 !important;
        cursor: not-allowed !important;
    }

    #pane-seccionDos.solo-lectura select {
        pointer-events: none;
        background-color: #f3f4f6 !important;
        cursor: not-allowed !important;
    }
</style>

<div class="container-fluid">
    <div class="my-4 text-center">
        <h4 class="mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 700 !important">EDITAR SOLICITUD DE BAQUEANOS</h4>
        <small>Actualice la información de su solicitud (si no está aprobada)</small>
    </div>

    <form id="formulario_editar" action="./vistas/baqueanos/solicitudes/acciones/editar_solicitud_baqueano.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" id="id" name="id" value="<?php echo $id; ?>">

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
                        <div class="ticket-bite p-2 w-50 text-center text-white rounded-4"
                            style="background-color:#002f55; position:absolute; left:1%">
                            <h6 class="fw-bold mb-0">Datos generales</h6>
                            <small>Actualice los datos generales de la solicitud</small>
                        </div>
                        <div class="ticket-spacer"></div>
                        <div class="row mt-0">
                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="id_mostrar">Número de radicado</label>
                                <div class="input-group shadow-sm rounded-2 precargado">
                                    <span class="input-group-text"><i class="bi bi-hash"></i></span>
                                    <input class="form-control" style="font-size:0.9em;" id="id_mostrar" value="ARB_<?php echo $id; ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_tipo_documento">Tipo Documento</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_tipo_documento)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                    <select class="form-control" id="sb_tipo_documento" name="sb_tipo_documento">
                                        <option value="<?php echo $sb_tipo_documento; ?>"><?php echo $sb_tipo_documento; ?></option>
                                        <option value="Cedula_Extranjeria">Cédula de Extranjería</option>
                                        <option value="NIT">N.I.T.</option>
                                        <option value="Pasaporte">Pasaporte</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_numero_identidad">Documento de Identidad</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_numero_identidad)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                                    <input class="form-control" style="font-size:0.9em;" id="sb_numero_identidad" name="sb_numero_identidad" value="<?php echo $sb_numero_identidad; ?>">
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_baqueano_nombre">Nombre</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_baqueano_nombre)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input class="form-control" style="font-size:0.9em;" id="sb_baqueano_nombre" name="sb_baqueano_nombre" value="<?php echo $sb_baqueano_nombre; ?>">
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_baqueano_apellido">Apellido</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_baqueano_apellido)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input class="form-control" style="font-size:0.9em;" id="sb_baqueano_apellido" name="sb_baqueano_apellido" value="<?php echo $sb_baqueano_apellido; ?>">
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_telefono_baqueano">Teléfono</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_telefono_baqueano)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input class="form-control" style="font-size:0.9em;" id="sb_telefono_baqueano" name="sb_telefono_baqueano" value="<?php echo $sb_telefono_baqueano; ?>">
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_correo_baqueano">Correo</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_correo_baqueano)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input class="form-control" style="font-size:0.9em;" id="sb_correo_baqueano" name="sb_correo_baqueano" value="<?php echo $sb_correo_baqueano; ?>">
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_direccion">Dirección</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_direccion)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                    <input class="form-control" style="font-size:0.9em;" id="sb_direccion" name="sb_direccion" value="<?php echo $sb_direccion; ?>">
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_cuenta">Cuenta Bancaria</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_cuenta)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-bank2"></i></span>
                                    <select class="form-control" id="sb_cuenta" name="sb_cuenta">
                                        <option value="<?php echo $sb_cuenta; ?>"><?php echo $sb_cuenta; ?></option>
                                        <option value="BANCOLOMBIA">BANCOLOMBIA</option>
                                        <option value="DAVIPLATA">DAVIPLATA</option>
                                        <option value="DAVIVIENDA">DAVIVIENDA</option>
                                        <option value="NEQUI">NEQUI</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_tipo_cuenta">Tipo de Cuenta Bancaria</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_tipo_cuenta)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-safe"></i></span>
                                    <select class="form-control" id="sb_tipo_cuenta" name="sb_tipo_cuenta">
                                        <option value="<?php echo $sb_tipo_cuenta; ?>"><?php echo $sb_tipo_cuenta; ?></option>
                                        <option value="AHORROS">AHORROS</option>
                                        <option value="CORRIENTE">CORRIENTE</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_num_cuenta">N° Cuenta</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_num_cuenta)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-bank"></i></span>
                                    <input class="form-control" style="font-size:0.9em;" id="sb_num_cuenta" name="sb_num_cuenta" value="<?php echo $sb_num_cuenta; ?>">
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_titular">Titular de la Cuenta</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_titular)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                    <input class="form-control" style="font-size:0.9em;" id="sb_titular" name="sb_titular" value="<?php echo $sb_titular; ?>">
                                </div>
                            </div>

                            <div class="mt-4 d-flex justify-content-end">
                                <button type="button" class="btn" style="background-color: #002F55; color: #ffff" onclick="nextSection()"><b>SIGUIENTE</b></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="pane-seccionDos" role="tabpanel" aria-labelledby="tab-seccionDos" tabindex="0">
                <div class="card rounded-4 shadow-sm border-0 section-one-card">
                    <div class="card-body p-4">
                        <div class="ticket-bite p-2 w-50 text-center text-white rounded-4"
                            style="background-color:#002f55; position:absolute; right:1%; left:auto;">
                            <h6 class="fw-bold mb-0">Plan de trabajo</h6>
                            <small>Información operativa y logística de la solicitud</small>
                        </div>
                        <div class="ticket-spacer"></div>
                        <div class="row mt-0">
                            <div class="d-flex justify-content-end mb-3">
                                <div id="tituloSoloLectura"
                                    class="solo-lectura-badge d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill">
                                    <i class="bi bi-lock-fill"></i>
                                    <span class="fw-semibold">Sección de solo lectura</span>
                                </div>
                            </div>

                            <div class="col-12 mt-0 d-flex justify-content-start">
                                <h6 class="fw-bold p-2 text-white text-center rounded-3 mb-0 w-50" style="background-color:#002F55;">
                                    Responsables
                                </h6>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_profesional_baqueano">Profesional Social</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_profesional_baqueano)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-person-workspace"></i></span>
                                    <input class="form-control" style="font-size:0.9em;" name="sb_profesional_baqueano" id="sb_profesional_baqueano"
                                        value="<?php echo $sb_profesional_baqueano; ?>">
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_coordinador">Coordinador</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_coordinador)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                    <input class="form-control" style="font-size:0.9em;" name="sb_coordinador" id="sb_coordinador"
                                        value="<?php echo $sb_coordinador; ?>">
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_year">Año</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_year)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                                    <input class="form-control" style="font-size:0.9em;" name="sb_year" id="sb_year"
                                        value="<?php echo $sb_year; ?>" readonly>
                                </div>
                            </div>

                            <div class="col-12 my-4" style="border-bottom:2px dashed #002f557a"></div>

                            <div class="col-12 mt-0 d-flex justify-content-end">
                                <h6 class="fw-bold p-2 text-white text-center rounded-3 mb-0 w-50" style="background-color: rgba(245, 140, 11, 0.81)">
                                    Fechas y valores
                                </h6>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_fecha_inicio">Fecha de Inicio</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_fecha_inicio)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                                    <input type="date" class="form-control" style="font-size:0.9em;" name="sb_fecha_inicio" id="sb_fecha_inicio"
                                        value="<?php echo $sb_fecha_inicio; ?>">
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_fecha_fin">Fecha Final</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_fecha_fin)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-calendar-x"></i></span>
                                    <input type="date" class="form-control" style="font-size:0.9em;" name="sb_fecha_fin" id="sb_fecha_fin"
                                        value="<?php echo $sb_fecha_fin; ?>">
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_dias_calculados">Total de Días</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_dias_calculados)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                                    <input class="form-control" style="font-size:0.9em;" name="sb_dias_calculados" id="sb_dias_calculados"
                                        value="<?php echo $sb_dias_calculados; ?>" readonly>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-6 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_cobro_diario_visible">Valor Diario</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_cobro_diario)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                                    <input class="form-control" style="font-size:0.9em;" id="sb_cobro_diario_visible" placeholder="$ 0">
                                </div>
                                <input type="hidden" name="sb_cobro_diario" id="sb_cobro_diario" value="<?php echo $sb_cobro_diario; ?>">
                            </div>

                            <div class="col-md-6 col-lg-6 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_valor_cobrar_visible">Total a Cobrar</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_valor_cobrar)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                    <input type="text" class="form-control" style="font-size:0.9em;" id="sb_valor_cobrar_visible" readonly>
                                </div>
                            </div>

                            <input type="hidden" name="sb_valor_cobrar" id="sb_valor_cobrar">

                            <div class="col-12 my-4" style="border-bottom:2px dashed #002f557a"></div>

                            <div class="col-12 mt-0 d-flex justify-content-start">
                                <h6 class="fw-bold p-2 text-white text-center rounded-3 mb-0 w-50" style="background-color:#198754;">
                                    Ubicación y actividad
                                </h6>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_unidad_intervencion">Unidad de Intervención</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_unidad_intervencion)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-diagram-3"></i></span>
                                    <input class="form-control" style="font-size:0.9em;" name="sb_unidad_intervencion" id="sb_unidad_intervencion"
                                        value="<?php echo $sb_unidad_intervencion; ?>">
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_unidad_operativa">Unidad Operativa</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_unidad_operativa)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-building"></i></span>
                                    <select class="form-control" name="sb_unidad_operativa" id="sb_unidad_operativa">
                                        <option value="<?php echo $sb_unidad_operativa; ?>"><?php echo $sb_unidad_operativa; ?></option>
                                        <option value="BARRIO">BARRIO</option>
                                        <option value="VEREDA">VEREDA</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_tipo_unidad">Tipo de Unidad</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_tipo_unidad)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-tags"></i></span>
                                    <select class="form-control" name="sb_tipo_unidad" id="sb_tipo_unidad">
                                        <option value="<?php echo $sb_tipo_unidad; ?>"><?php echo $sb_tipo_unidad; ?></option>
                                        <option value="URBANA">URBANA</option>
                                        <option value="RURAL">RURAL</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_municipio">Municipio</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_municipio)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-geo"></i></span>
                                    <select class="form-control" name="sb_municipio" id="sb_municipio" readonly>
                                        <option value="<?php echo $sb_municipio; ?>"><?php echo $sb_municipio; ?></option>
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
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_vereda">Vereda/Barrio</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_vereda)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-pin-map"></i></span>
                                    <input class="form-control" style="font-size:0.9em;" name="sb_vereda" id="sb_vereda"
                                        value="<?php echo $sb_vereda; ?>">
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_tipo_actividad">Tipo de Actividad</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_tipo_actividad)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-clipboard-check"></i></span>
                                    <select class="form-control" id="sb_tipo_actividad" name="sb_tipo_actividad" onchange="mostrarCamposPrincipales()" required>
                                        <option value="<?php echo $sb_tipo_actividad; ?>"><?php echo $sb_tipo_actividad; ?></option>
                                        <option value="RECONOCIMIENTO">RECONOCIMIENTO</option>
                                        <option value="ACOMPAÑAMIENTO_SOCIAL">ACOMPAÑAMIENTO SOCIAL</option>
                                        <option value="CONTROL_DE_CALIDAD">CONTROL DE CALIDAD</option>
                                        <option value="INTERLOCUCIÓN">INTERLOCUCIÓN</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-6 p-1 px-2 my-3" id="grupo_lider" style="display: none;">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_lider_cuadrilla"><b>Líder de Cuadrilla</b></label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_lider_cuadrilla)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-person-check"></i></span>
                                    <input class="form-control" style="font-size:0.9em;" name="sb_lider_cuadrilla" id="sb_lider_cuadrilla"
                                        value="<?php echo $sb_lider_cuadrilla; ?>">
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-6 p-1 px-2 my-3" id="grupo_reconocedor" style="display: none;">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_reconocedor"><b>Reconocedor</b></label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_reconocedor)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-person-lines-fill"></i></span>
                                    <input class="form-control" style="font-size:0.9em;" id="sb_reconocedor" name="sb_reconocedor"
                                        value="<?php echo $sb_reconocedor; ?>">
                                </div>
                            </div>

                            <div class="col-12 my-4" style="border-bottom:2px dashed #002f557a"></div>

                            <div class="col-12 mt-0 d-flex justify-content-end">
                                <h6 class="fw-bold p-2 text-white text-center rounded-3 mb-0 w-50" style="background-color:#002F55;">
                                    Logística
                                </h6>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_transporte">Transporte</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_transporte)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-truck"></i></span>
                                    <select class="form-control" id="sb_transporte" name="sb_transporte" onchange="mostrarCamposTransporte()">
                                        <option value="<?php echo $sb_transporte; ?>"><?php echo $sb_transporte; ?></option>
                                        <option value="SI">SI</option>
                                        <option value="NO">NO</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3" id="campo_porque_transporte" style="display: none;">
                                <label class="form-label fw-bold" style="font-size:0.9em;"><b>¿Por qué?</b></label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_porque_transporte)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-question-circle"></i></span>
                                    <input class="form-control" name="sb_porque_transporte" value="<?php echo htmlspecialchars($sb_porque_transporte); ?>">
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3" id="campo_cuanto_transporte" style="display: none;">
                                <label class="form-label fw-bold" style="font-size:0.9em;"><b>¿Cuánto?</b></label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_cuanto_transporte)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-cash"></i></span>
                                    <input type="text" class="form-control" id="sb_cuanto_transporte_visible" placeholder="$ 0">
                                </div>
                                <input type="hidden" name="sb_cuanto_transporte" id="sb_cuanto_transporte" value="<?php echo $sb_cuanto_transporte; ?>">
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_hospedaje">Hospedaje</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_hospedaje)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-house-door"></i></span>
                                    <select class="form-control" id="sb_hospedaje" name="sb_hospedaje" onchange="mostrarCamposHospedaje()">
                                        <option value="<?php echo $sb_hospedaje; ?>"><?php echo $sb_hospedaje; ?></option>
                                        <option value="SI">SI</option>
                                        <option value="NO">NO</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3" id="campo_porque_hospedaje" style="display: none;">
                                <label class="form-label fw-bold" style="font-size:0.9em;"><b>¿Por qué?</b></label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_porque_hospedaje)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-question-circle"></i></span>
                                    <input class="form-control" name="sb_porque_hospedaje" value="<?php echo htmlspecialchars($sb_porque_hospedaje); ?>">
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3" id="campo_cuanto_hospedaje" style="display: none;">
                                <label class="form-label fw-bold" style="font-size:0.9em;"><b>¿Cuánto?</b></label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($sb_cuanto_hospedaje)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-cash"></i></span>
                                    <input type="text" class="form-control" id="sb_cuanto_hospedaje_visible" placeholder="$ 0">
                                </div>
                                <input type="hidden" name="sb_cuanto_hospedaje" id="sb_cuanto_hospedaje" value="<?php echo $sb_cuanto_hospedaje; ?>">
                            </div>

                            <div class="mt-4 d-flex justify-content-center">
                                <button type="button" id="btnEditar" class="btn shadow d-inline-flex align-items-center gap-2"
                                    style="border-color:#002F55; color:#002F55;">
                                    <i class="bi bi-check2-circle"></i>
                                    <strong>GUARDAR CAMBIOS</strong>
                                </button>
                            </div>

                            <div id="campo-observacion" class="mt-3" style="display:none;">
                                <textarea class="form-control" id="sb_razon_operaciones" placeholder="Escriba la razón por la cual devuelve la solicitud"></textarea>
                                <button class="btn btn-primary mt-2" id="enviar-devulucion">Enviar Razón</button>
                                <input type="hidden" id="correo" value="correo@ejemplo.com">
                            </div>
                        </div>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $('#dataTable').DataTable();
    });

    document.addEventListener("DOMContentLoaded", function() {
        mostrarCamposPrincipales();
        mostrarCamposTransporte();
        mostrarCamposHospedaje();
        calcularDias();
    });

    function mostrarCamposPrincipales() {
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

        mostrarCamposTransporte();
        mostrarCamposHospedaje();
    }

    function mostrarCamposTransporte() {
        const transporte = document.getElementById("sb_transporte")?.value;
        const campoPorque = document.getElementById("campo_porque_transporte");
        const campoCuanto = document.getElementById("campo_cuanto_transporte");

        if (transporte === "SI") {
            campoPorque.style.display = "block";
            campoCuanto.style.display = "block";
        } else {
            campoPorque.style.display = "none";
            campoCuanto.style.display = "none";
        }
    }

    function mostrarCamposHospedaje() {
        const hospedaje = document.getElementById("sb_hospedaje")?.value;
        const campoPorque = document.getElementById("campo_porque_hospedaje");
        const campoCuanto = document.getElementById("campo_cuanto_hospedaje");
        if (hospedaje === "SI") {
            campoPorque.style.display = "block";
            campoCuanto.style.display = "block";
        } else {
            campoPorque.style.display = "none";
            campoCuanto.style.display = "none";
        }
    }

    // Confirmación SweetAlert antes de enviar
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formulario_editar');
        const btnEditar = document.getElementById('btnEditar');

        if (btnEditar && form) {
            btnEditar.addEventListener('click', function() {
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
                            <div class="swal-saved-header d-flex justify-content-center aling-items-center text-center w-100">
                                Confirmación de edición
                            </div>
                            <div class="swal-saved-divider"></div>
                            <div class="swal-saved-body">
                                <div class="swal-saved-illu">
                                    <i class="bi bi-patch-question-fill" 
                                        style="font-size:70px;color:#002F55;opacity:.15;"></i>
                                </div>
                                <div class="swal-saved-text">
                                    Estás a punto de guardar cambios en la
                                    <strong>solicitud de baqueanos</strong>.<br>
                                    Verifica que toda la información sea correcta
                                    antes de continuar.
                                </div>
                                <div class="d-flex gap-3 mt-3">
                                    <button id="confirmarEdicion" 
                                            class="btn text-white px-4"
                                            style="background:#002F55;border-radius:12px;">
                                        <i class="bi bi-check-circle me-2"></i>
                                        Confirmar
                                    </button>
                                    <button id="cancelarEdicion" 
                                        class="btn btn-light px-4"
                                        style="border-radius:12px;border:1px solid #dee2e6;">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>`,
                    didOpen: () => {
                        document.getElementById('confirmarEdicion')
                            .addEventListener('click', () => {
                                Swal.close();
                                form.submit();
                            });
                        document.getElementById('cancelarEdicion')
                            .addEventListener('click', () => {
                                Swal.close();
                            });
                    }
                });
            });
        }
    });

    document.addEventListener("DOMContentLoaded", function() {
        const sb_fechaInicio = document.getElementById('sb_fecha_inicio');
        const sb_fechaFin = document.getElementById('sb_fecha_fin');
        const sb_diasCalculados = document.getElementById('sb_dias_calculados');
        const sb_cobroDiarioVisible = document.getElementById('sb_cobro_diario_visible');
        const sb_cobroDiario = document.getElementById('sb_cobro_diario');
        const sb_valorCobrarVisible = document.getElementById('sb_valor_cobrar_visible');
        const sb_valorCobrar = document.getElementById('sb_valor_cobrar');

        function formatearPesos(valor) {
            const numerico = parseFloat(valor) || 0;
            return '$ ' + numerico.toLocaleString("es-CO", {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }

        function limpiarValor(valor) {
            return valor.replace(/[^\d]/g, '') || '0';
        }

        function configurarCampoMoneda(inputVisibleId, inputHiddenId) {
            const visible = document.getElementById(inputVisibleId);
            const hidden = document.getElementById(inputHiddenId);

            if (!visible || !hidden) return;

            visible.value = formatearPesos(hidden.value || 0);

            visible.addEventListener('input', function(e) {
                const limpio = limpiarValor(e.target.value);
                hidden.value = limpio;
                e.target.value = formatearPesos(limpio);
            });
        }

        configurarCampoMoneda("sb_cuanto_transporte_visible", "sb_cuanto_transporte");
        configurarCampoMoneda("sb_cuanto_hospedaje_visible", "sb_cuanto_hospedaje");

        if (sb_cobroDiario && sb_cobroDiarioVisible) {
            const valorInicial = sb_cobroDiario.value || '0';
            sb_cobroDiarioVisible.value = formatearPesos(valorInicial);
        }

        if (sb_cobroDiarioVisible) {
            sb_cobroDiarioVisible.addEventListener('input', function(e) {
                const valorLimpio = limpiarValor(e.target.value);
                sb_cobroDiario.value = valorLimpio;
                e.target.value = formatearPesos(valorLimpio);
                calcularValorACobrar();
            });
        }

        function calcularValorACobrar() {
            const dias = parseFloat(sb_diasCalculados?.value || 0);
            const valorPorDia = parseFloat(sb_cobroDiario?.value || 0);
            const total = dias * valorPorDia;
            if (sb_valorCobrarVisible) {
                sb_valorCobrarVisible.value = "$" + total.toLocaleString("es-CO", {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
            }
            if (sb_valorCobrar) {
                sb_valorCobrar.value = total;
            }
        }

        window.calcularDias = function calcularDias() {
            const inicio = new Date(sb_fechaInicio.value);
            const fin = new Date(sb_fechaFin.value);
            if (sb_fechaInicio.value && sb_fechaFin.value) {
                const diferencia = (fin - inicio) + 1;
                const dias = Math.ceil(diferencia / (1000 * 60 * 60 * 24));
                if (dias >= 0) {
                    sb_diasCalculados.value = dias;
                } else {
                    sb_diasCalculados.value = '';
                }
            } else {
                sb_diasCalculados.value = '';
            }
            calcularValorACobrar();
        }

        sb_fechaInicio.addEventListener('change', window.calcularDias);
        sb_fechaFin.addEventListener('change', window.calcularDias);
        window.calcularDias();
    });

    let currentSectionIndex = 1;
    const totalSections = 2;

    function showSection(index) {
        document.getElementById('pane-seccionUno')?.classList.remove('show', 'active');
        document.getElementById('pane-seccionDos')?.classList.remove('show', 'active');

        document.getElementById('tab-seccionUno')?.classList.remove('active');
        document.getElementById('tab-seccionDos')?.classList.remove('active');

        if (index === 1) {
            document.getElementById('pane-seccionUno')?.classList.add('show', 'active');
            document.getElementById('tab-seccionUno')?.classList.add('active');
        } else if (index === 2) {
            document.getElementById('pane-seccionDos')?.classList.add('show', 'active');
            document.getElementById('tab-seccionDos')?.classList.add('active');
        }
    }

    function validarSeccion(index) {
        let seccionActual = null;
        if (index === 1) seccionActual = document.getElementById('pane-seccionUno');
        if (index === 2) seccionActual = document.getElementById('pane-seccionDos');

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
                    title: "Fecha fuera de rango",
                    text: "Solo puedes seleccionar fechas dentro de esta y la próxima semana.",
                    confirmButtonColor: "#002F55"
                });
                this.value = "";
                return;
            }
            fechaFin.min = this.value;
        });

        fechaFin.addEventListener("change", function() {
            if (!this.value) return;
            if (this.value < fechaInicio.value) {
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

    document.addEventListener("DOMContentLoaded", function() {
        const seccionDos = document.getElementById("pane-seccionDos");
        if (!seccionDos) return;

        seccionDos.classList.add("solo-lectura");
        seccionDos.querySelectorAll("input, textarea").forEach(campo => {
            if (campo.type === "hidden") return;
            if (campo.id === "btnEditar") return;
            campo.readOnly = true;
            campo.setAttribute("tabindex", "-1");
        });
        seccionDos.querySelectorAll("select").forEach(sel => {
            sel.setAttribute("tabindex", "-1");
        });
    });
    document.addEventListener("DOMContentLoaded", function() {
        const titulo = document.getElementById("tituloSoloLectura");
        if (titulo) titulo.classList.add("flotando");
    });
</script>