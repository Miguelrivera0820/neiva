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

$rolesPermitidos = array("administrador", "director_proyectos", "soporte", "social");

if (!in_array($rolUsuario, $rolesPermitidos) && !in_array($rolUsuarioDos, $rolesPermitidos)) {
    header("Location: ../../acceso_denegado.php");
    exit();
}

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

$sql_cuentas = "SELECT * FROM solicitud_baqueanos WHERE id = ?";
$stmt_cuentas = $mysqli->prepare($sql_cuentas);
$stmt_cuentas->bind_param("i", $idUsuario);
$stmt_cuentas->execute();
$resultado_cuentas = $stmt_cuentas->get_result();

$datos_cuentas  = null;
$ruta_cuentas   = null;


if ($id) {
    // Eliminar de copia_devolucion_baqueanos para reactivar la solicitud
    $sql_delete = "DELETE FROM copia_devolucion_baqueanos WHERE copia_id = ?";
    $stmt_delete = $mysqli->prepare($sql_delete);
    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $id);
        $stmt_delete->execute();
        $stmt_delete->close();
    }

    // Actualizar estado de pagos a PENDIENTE y razón vacía
    $sql_update = "UPDATE solicitud_baqueanos SET sb_estado_pagos = 'PENDIENTE', sb_razon_pagos = '' WHERE id = ?";
    $stmt_update = $mysqli->prepare($sql_update);
    if ($stmt_update) {
        $stmt_update->bind_param("i", $id);
        $stmt_update->execute();
        $stmt_update->close();
    }

    $query = "SELECT * FROM solicitud_baqueanos WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id = $row['id'] ?? '';
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

        // Sumar días y valor de extensiones
        $sql_ext = "SELECT SUM(dias_agregados) AS dias_extensiones, SUM(valor_adicional) AS valor_extensiones FROM solicitud_baqueanos_extensiones WHERE solicitud_id = ? AND estado = 'REGISTRADO'";
        $stmt_ext = $mysqli->prepare($sql_ext);
        $stmt_ext->bind_param("i", $id);
        $stmt_ext->execute();
        $res_ext = $stmt_ext->get_result();
        $row_ext = $res_ext ? $res_ext->fetch_assoc() : null;
        $dias_extensiones = $row_ext && $row_ext['dias_extensiones'] ? (int)$row_ext['dias_extensiones'] : 0;
        $valor_extensiones = $row_ext && $row_ext['valor_extensiones'] ? (int)$row_ext['valor_extensiones'] : 0;
        $stmt_ext->close();

        $sb_dias_calculados = ((int)$sb_dias_calculados) + $dias_extensiones;
        $sb_valor_cobrar = ((int)$sb_valor_cobrar) + $valor_extensiones;
    } else {
        echo "No se encontraron datos para esta cédula.<br>";
        exit;
    }
}

$valorFormateado    = '$ ' . number_format((float)$sb_cobro_diario, 0, ',', '.');
$valorFormateado1   = '$ ' . number_format((float)$sb_valor_cobrar, 0, ',', '.');

$nombre = $_SESSION['nombre_usuario'];
?>

<style>
    .page-wrap {
        font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
    }

    .page-title {
        color: #002F55;
        font-weight: 800;
        letter-spacing: .2px;
    }

    .subtle {
        color: #6c7a89;
    }

    .card-pro {
        border: 1px solid rgba(0, 0, 0, .08);
        border-radius: 18px;
        box-shadow: 0 14px 34px rgba(0, 0, 0, 0.06);
        overflow: hidden;
        background: #fff;
    }

    .card-pro .card-header-pro {
        background: linear-gradient(135deg, #002F55 0%, #0b5aa0 100%);
        color: #fff;
        padding: 14px 18px;
    }

    .badge-pro {
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.25);
        color: #fff;
        font-weight: 600;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: .78rem;
    }

    .section-sep {
        border-bottom: 1px dashed rgba(0, 47, 85, 0.35);
        margin: 12px 0;
    }

    .info-box {
        border: 1px solid rgba(0, 0, 0, .12);
        border-radius: 14px;
        background: #f7f9fc;
        padding: 10px 12px;
        height: 100%;
    }

    .info-label {
        text-transform: uppercase;
        font-size: .72rem;
        color: rgba(0, 0, 0, .55);
        letter-spacing: .4px;
        margin-bottom: 4px;
        line-height: 1;
    }

    .info-value {
        font-weight: 650;
        color: #132233;
        font-size: .95rem;
        line-height: 1.2;
        word-break: break-word;
    }

    .input-box {
        border: 1px solid rgba(0, 0, 0, .16);
        border-radius: 14px;
        background: #fff;
        padding: 10px 12px;
        height: 100%;
    }

    .input-box input,
    .input-box textarea {
        width: 100%;
        border: 0;
        outline: none;
        background: transparent;
        font-size: .95rem;
        padding: 0;
        margin: 0;
    }

    .help-chip {
        font-size: .78rem;
        color: rgba(0, 0, 0, .55);
    }

    /* Upload cards */
    .upload-card {
        width: 100%;
    }

    .upload-area {
        border: 2px dashed #cfd6e4;
        border-radius: 16px;
        cursor: pointer;
        background: #fff;
        transition: 160ms ease;
        user-select: none;
    }

    .upload-area:hover {
        border-color: #9db2ff;
        box-shadow: 0 10px 26px rgba(0, 0, 0, 0.08);
        transform: translateY(-1px);
    }

    .upload-icon {
        width: 60px;
        height: auto;
        display: block;
        flex: 0 0 auto;
        opacity: .95;
    }

    .file-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 10px;
        border-radius: 999px;
        background: #f1f5ff;
        border: 1px solid rgba(13, 110, 253, .18);
        font-size: .82rem;
        color: #113a66;
        margin-top: 8px;
    }

    .file-pill i {
        color: #0d6efd;
    }

    /* Observaciones PRO (ya no queda al aire) */
    .obs-card {
        border-radius: 18px;
        border: 1px solid rgba(0, 0, 0, .08);
        box-shadow: 0 12px 30px rgba(0, 0, 0, .06);
        overflow: hidden;
        background: #fff;
    }

    .obs-header {
        background: linear-gradient(135deg, rgba(0, 47, 85, .08) 0%, rgba(11, 90, 160, .10) 100%);
        padding: 14px 18px;
        border-bottom: 1px solid rgba(0, 0, 0, .06);
    }

    .obs-title {
        margin: 0;
        font-weight: 800;
        color: #002F55;
        letter-spacing: .2px;
        font-size: 1rem;
    }

    .obs-sub {
        margin: 4px 0 0;
        font-size: .82rem;
        color: rgba(0, 0, 0, .55);
    }

    .obs-body {
        padding: 16px 18px 18px 18px;
    }

    .obs-textarea {
        border: 1px solid rgba(0, 0, 0, .14);
        border-radius: 16px;
        background: #fbfcff;
        padding: 12px 12px;
        transition: 140ms ease;
    }

    .obs-textarea:focus-within {
        border-color: rgba(13, 110, 253, .40);
        box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .10);
        background: #fff;
    }

    .obs-textarea textarea {
        width: 100% !important;
        display: block;
        border: 0 !important;
        outline: none !important;
        background: transparent !important;
        font-size: .95rem;
        line-height: 1.35;
        padding: 0;
        margin: 0;
        min-height: 140px;
        resize: vertical;
    }

    .obs-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-top: 10px;
        font-size: .78rem;
        color: rgba(0, 0, 0, .55);
    }

    .obs-counter strong {
        color: #002F55;
    }

    .btn-pro {
        border-radius: 14px;
        padding: 12px 16px;
        font-weight: 800;
        letter-spacing: .2px;
        box-shadow: 0 10px 24px rgba(25, 135, 84, 0.18);
    }

    .is-invalid-lite {
        border-color: rgba(220, 53, 69, .55) !important;
        box-shadow: 0 0 0 .2rem rgba(220, 53, 69, .12);
    }

    .upload-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(240, 242, 245, 0.85);
        backdrop-filter: blur(6px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: 0.25s ease;
    }

    .upload-modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    /* Modal Card */
    .upload-modal {
        width: 100%;
        max-width: 460px;
        background: #ffffff;
        border-radius: 18px;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.12);
        padding: 26px 24px 28px;
        position: relative;
        animation: fadeInUp 0.3s ease;
    }

    @keyframes fadeInUp {
        from {
            transform: translateY(12px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .upload-modal h3 {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 20px;
        color: #333;
    }

    /* Drop Area Style */
    .upload-drop-zone {
        border: 2px dashed #e3e6eb;
        border-radius: 14px;
        padding: 26px 16px;
        text-align: center;
        background: #fafbfc;
        margin-bottom: 22px;
    }

    .upload-drop-zone small {
        display: block;
        margin-top: 6px;
        font-size: .75rem;
        color: #9aa2af;
    }

    /* Progress container */
    .upload-file-card {
        background: #f8f9fb;
        border-radius: 12px;
        padding: 12px 14px;
        margin-top: 10px;
    }

    .upload-file-header {
        display: flex;
        justify-content: space-between;
        font-size: .85rem;
        font-weight: 500;
        margin-bottom: 8px;
        color: #333;
    }

    .upload-progress {
        height: 6px;
        background: #e5e9f2;
        border-radius: 999px;
        overflow: hidden;
    }

    .upload-progress-bar {
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, #006cc5, #002F55);
        transition: width 0.2s ease;
    }

    /* Percentage */
    .upload-percent {
        font-size: .75rem;
        color: #7b8794;
        margin-top: 6px;
        text-align: right;
    }

    .upload-zone-custom {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
        min-height: 110px;
        padding: 22px 16px;
        text-align: center;
    }

    .upload-top-text {
        font-size: 0.95rem;
        font-weight: 500;
        color: #444;
    }

    .upload-bottom-text {
        font-size: 0.85rem;
        font-weight: 700;
        color: #014882;
        margin-top: 12px;
        letter-spacing: 0.5px;
    }
</style>

<div class="container-fluid page-wrap">
    <div class="my-4 text-center">
        <h4 class="mb-1 page-title">RADICACIÓN DE CUENTAS</h4>
        <div class="subtle">Radica la solicitud de pago</div>
    </div>
    <form id="miFormulario"
        action="./vistas/baqueanos/solicitudes/acciones/cuentas_baqueanos.php"
        method="POST"
        enctype="multipart/form-data">
        <div class="container-xl my-4">
            <div class="row g-3 align-items-start">
                <div class="col-12 col-lg-8">
                    <div class="card card-pro min-vh-55">
                        <div class="card-header-pro">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-semibold">Resumen de la solicitud</div>
                                    <div class="small" style="opacity:.85;">Verifica la información antes de radicar</div>
                                </div>
                                <span class="badge-pro">Solicitud</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="container py-2">
                                <div class="row gy-3 gx-3">
                                    <div class="col-12 col-md-6">
                                        <div class="info-box">
                                            <div class="info-label">Tipo de documento</div>
                                            <div class="info-value">
                                                <?php echo htmlspecialchars($sb_tipo_documento ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="info-box">
                                            <div class="info-label">Número documento</div>
                                            <div class="info-value">
                                                <?php echo htmlspecialchars($sb_numero_identidad ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="info-box">
                                            <div class="info-label">Número telefónico</div>
                                            <div class="info-value">
                                                <?php echo htmlspecialchars($sb_telefono_baqueano ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="info-box">
                                            <div class="info-label">Nombres</div>
                                            <div class="info-value">
                                                <?php echo htmlspecialchars($sb_baqueano_nombre ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="info-box">
                                            <div class="info-label">Apellidos</div>
                                            <div class="info-value">
                                                <?php echo htmlspecialchars($sb_baqueano_apellido ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="info-box text-center">
                                            <div class="info-label">Correo electrónico</div>
                                            <div class="info-value">
                                                <?php echo htmlspecialchars($sb_correo_baqueano ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="section-sep"></div>
                                    </div>
                                    <div class="col-12 d-flex align-items-center justify-content-between">
                                        <div class="text-uppercase small text-secondary fw-semibold">Fechas y valores</div>
                                        <span class="help-chip">Confirma días y total exactamente</span>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="info-box">
                                            <div class="info-label">Año</div>
                                            <div class="info-value">
                                                <?php echo htmlspecialchars($sb_year ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="info-box">
                                            <div class="info-label">Fecha inicio</div>
                                            <div class="info-value">
                                                <?php echo htmlspecialchars($sb_fecha_inicio ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="info-box">
                                            <div class="info-label">Fecha fin</div>
                                            <div class="info-value">
                                                <?php echo htmlspecialchars($sb_fecha_fin ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="info-box">
                                            <div class="info-label">Total de días</div>

                                            <div class="info-value" id="dias_calculados_text">
                                                <?php echo htmlspecialchars($sb_dias_calculados ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                            <input type="hidden" id="dias_calculados" value="<?php echo htmlspecialchars($sb_dias_calculados ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-8">
                                        <div class="input-box">
                                            <div class="info-label">Confirmar total de días</div>
                                            <input
                                                type="number"
                                                id="sb_confirmar_dias"
                                                name="sb_confirmar_dias"
                                                required
                                                min="1"
                                                placeholder="Ej: 13">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="info-box text-center" style="background:#fff;">
                                            <div class="info-label">Valor diario</div>
                                            <div class="info-value">
                                                <?php echo htmlspecialchars($valorFormateado ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="info-box">
                                            <div class="info-label">Total a cobrar</div>
                                            <div class="info-value">
                                                <?php echo htmlspecialchars($valorFormateado1 ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                            <input type="hidden" id="sb_valor_cobrar" value="<?php echo htmlspecialchars((string)$sb_valor_cobrar ?? '0', ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-8">
                                        <div class="input-box">
                                            <div class="info-label">Confirmar total a cobrar</div>
                                            <input
                                                type="text"
                                                id="sb_confirmar_valor"
                                                name="sb_confirmar_valor"
                                                required
                                                inputmode="numeric"
                                                autocomplete="off"
                                                placeholder="Ej: 250.000">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="section-sep"></div>
                                    </div>

                                    <div class="col-12 d-flex align-items-center justify-content-between">
                                        <div class="text-uppercase small text-secondary fw-semibold">Ubicación y unidad</div>
                                        <span class="help-chip">Solo lectura</span>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="info-box">
                                            <div class="info-label">Unidad intervención</div>
                                            <div class="info-value">
                                                <?php echo htmlspecialchars($sb_unidad_intervencion ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="info-box">
                                            <div class="info-label">Unidad operativa</div>
                                            <div class="info-value">
                                                <?php echo htmlspecialchars($sb_unidad_operativa ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="info-box">
                                            <div class="info-label">Tipo de unidad</div>
                                            <div class="info-value">
                                                <?php echo htmlspecialchars($sb_tipo_unidad ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="info-box">
                                            <div class="info-label">Municipio</div>
                                            <div class="info-value">
                                                <?php echo htmlspecialchars($sb_municipio ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="info-box">
                                            <div class="info-label">Vereda / barrio</div>
                                            <div class="info-value">
                                                <?php echo htmlspecialchars($sb_vereda ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="info-box">
                                            <div class="info-label">Profesional</div>
                                            <div class="info-value">
                                                <?php echo htmlspecialchars($sb_profesional_baqueano ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="card card-pro min-vh-55">
                        <div class="card-header-pro">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="fw-semibold">Documentos PDF</div>
                                <span class="badge-pro">Requeridos</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="small subtle mb-3">
                                Sube los documentos en PDF.
                            </div>
                            <div class="mb-3">
                                <label class="text-uppercase small text-secondary text-center w-100 lh-1 mb-2" for="sb_cuenta_baqueano">
                                    Cuenta de cobro
                                </label>
                                <div class="upload-card w-100">
                                    <input id="sb_cuenta_baqueano" name="sb_cuenta_baqueano" type="file" accept="application/pdf,.pdf" required hidden />
                                    <label class="upload-area d-flex align-items-center gap-3 p-3 w-100" for="sb_cuenta_baqueano">
                                        <img class="upload-icon" src="<?= neiva_app_url('Arbimaps/vistas/baqueanos/assets/img/document.png') ?>" alt="Icono de carga" />
                                        <div class="upload-text">
                                            <div class="fw-semibold">Haz clic para cargar</div>
                                            <div class="text-muted small">PDF</div>
                                        </div>
                                    </label>
                                    <div class="small mt-2" data-file-name-for="sb_cuenta_baqueano" aria-live="polite"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="text-uppercase small text-secondary text-center w-100 lh-1 mb-2" for="sb_rut_baqueano">
                                    Rut
                                </label>
                                <div class="upload-card w-100">
                                    <input id="sb_rut_baqueano" name="sb_rut_baqueano" type="file" accept="application/pdf,.pdf" required hidden />
                                    <label class="upload-area d-flex align-items-center gap-3 p-3 w-100" for="sb_rut_baqueano">
                                        <img class="upload-icon" src="<?= neiva_app_url('Arbimaps/vistas/baqueanos/assets/img/document.png') ?>" alt="Icono de carga" />
                                        <div class="upload-text">
                                            <div class="fw-semibold">Haz clic para cargar</div>
                                            <div class="text-muted small">PDF</div>
                                        </div>
                                    </label>
                                    <div class="small mt-2" data-file-name-for="sb_rut_baqueano" aria-live="polite"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="text-uppercase small text-secondary text-center w-100 lh-1 mb-2" for="sb_cedula_baqueano">
                                    Cédula
                                </label>

                                <div class="upload-card w-100">
                                    <input id="sb_cedula_baqueano" name="sb_cedula_baqueano" type="file" accept="application/pdf,.pdf" required hidden />
                                    <label class="upload-area d-flex align-items-center gap-3 p-3 w-100" for="sb_cedula_baqueano">
                                        <img class="upload-icon" src="<?= neiva_app_url('Arbimaps/vistas/baqueanos/assets/img/document.png') ?>" alt="Icono de carga" />
                                        <div class="upload-text">
                                            <div class="fw-semibold">Haz clic para cargar</div>
                                            <div class="text-muted small">PDF</div>
                                        </div>
                                    </label>
                                    <div class="small mt-2" data-file-name-for="sb_cedula_baqueano" aria-live="polite"></div>
                                </div>
                            </div>
                            <div class="mb-1">
                                <label class="text-uppercase small text-secondary text-center w-100 lh-1 mb-2" for="sb_certificado_baqueano">
                                    Certificado bancario
                                </label>
                                <div class="upload-card w-100">
                                    <input id="sb_certificado_baqueano" name="sb_certificado_baqueano" type="file" accept="application/pdf,.pdf" required hidden />
                                    <label class="upload-area d-flex align-items-center gap-3 p-3 w-100" for="sb_certificado_baqueano">
                                        <img class="upload-icon" src="<?= neiva_app_url('Arbimaps/vistas/baqueanos/assets/img/document.png') ?>" alt="Icono de carga" />
                                        <div class="upload-text">
                                            <div class="fw-semibold">Haz clic para cargar</div>
                                            <div class="text-muted small">PDF</div>
                                        </div>
                                    </label>
                                    <div class="small mt-2" data-file-name-for="sb_certificado_baqueano" aria-live="polite"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 mt-3">
                    <div class="obs-card">
                        <div class="obs-header d-flex align-items-start justify-content-between flex-wrap gap-2">
                            <div>
                                <p class="obs-title">Observaciones</p>
                            </div>
                            <span class="badge-pro" style="background: rgba(0,47,85,.10); border-color: rgba(0,47,85,.18); color:#002F55;">
                                Nota
                            </span>
                        </div>

                        <div class="obs-body">
                            <div class="obs-textarea">
                                <textarea
                                    id="sb_observacion_cuenta"
                                    name="sb_observacion_cuenta"
                                    placeholder="Ej: Se adjunta cuenta corregida. Se realizó ajuste por días efectivos."
                                    oninput="convertirMayusculas(this)"
                                    rows="5"
                                    maxlength="500"
                                    style="resize: vertical;"></textarea>
                            </div>
                            <div class="obs-meta">
                                <div class="obs-counter" aria-live="polite">
                                    <strong id="obsCount">0</strong>/500
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="id" name="id" value="<?php echo htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="form-group mt-4 mb-0 d-flex justify-content-center">
                    <button type="submit" class="btn btn-success btn-pro px-5">
                        <i class="fas fa-file-upload"></i> CARGAR
                    </button>
                </div>
            </div>
        </div>
    </form>
    <div class="upload-modal-overlay" id="uploadModal">
        <div class="upload-modal">
            <h3 class="text-center">Cargando documentos</h3>
            <div class="upload-drop-zone upload-zone-custom">
                <div class="upload-top-text">
                    Cargando documentos por favor
                </div>
                <div class="upload-bottom-text">
                    NO INTERRUMPAS EL PROCESO
                </div>
                <small>Cargando documentos...</small>
            </div>
            <div class="upload-file-card">
                <div class="upload-file-header">
                    <span>documentos</span>
                    <span id="uploadPercentText">0%</span>
                </div>
                <div class="upload-progress">
                    <div class="upload-progress-bar" id="uploadProgressBar"></div>
                </div>
                <div class="upload-percent" id="uploadBytes">
                    0 KB / 0 KB
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        const form = document.getElementById('miFormulario');
        const inputConfirmar = document.getElementById("sb_confirmar_valor");
        const inputTotalHidden = document.getElementById("sb_valor_cobrar");
        const inputConfirmarDias = document.getElementById("sb_confirmar_dias");
        const inputDiasCalculadosHidden = document.getElementById("dias_calculados");

        const obs = document.getElementById("sb_observacion_cuenta");
        const obsCount = document.getElementById("obsCount");
        if (obs && obsCount) {
            const updateCount = () => obsCount.textContent = (obs.value || "").length;
            obs.addEventListener("input", updateCount);
            updateCount();
        }

        function markInvalid(el, msg) {
            if (!el) return;
            const box = el.closest('.input-box');
            if (box) box.classList.add('is-invalid-lite');
            if (msg) Swal.fire({
                icon: 'warning',
                title: 'Validación',
                text: msg
            });
            el.focus();
        }

        function clearInvalid(el) {
            if (!el) return;
            const box = el.closest('.input-box');
            if (box) box.classList.remove('is-invalid-lite');
        }

        function wireFileName(inputId) {
            const input = document.getElementById(inputId);
            const out = document.querySelector(`[data-file-name-for="${inputId}"]`);
            if (!input || !out) return;

            input.addEventListener('change', () => {
                if (input.files && input.files.length > 0) {
                    const name = input.files[0].name;
                    out.innerHTML = `<span class="file-pill"><i class="fas fa-check-circle"></i> ${name}</span>`;
                } else {
                    out.textContent = '';
                }
            });
        }

        wireFileName("sb_cuenta_baqueano");
        wireFileName("sb_rut_baqueano");
        wireFileName("sb_cedula_baqueano");
        wireFileName("sb_certificado_baqueano");

        inputConfirmar.addEventListener("input", function(e) {
            clearInvalid(inputConfirmar);
            let value = e.target.value.replace(/[^\d]/g, "");
            if (value) {
                const number = parseInt(value, 10);
                e.target.value = "$" + number.toLocaleString("es-CO");
            } else {
                e.target.value = "";
            }
        });

        inputConfirmarDias.addEventListener("input", function() {
            clearInvalid(inputConfirmarDias);
            const maxDias = parseInt(inputDiasCalculadosHidden.value, 10);
            const valor = parseInt(inputConfirmarDias.value, 10);

            if (!isNaN(valor) && !isNaN(maxDias) && valor > maxDias) {
                inputConfirmarDias.value = maxDias;
            }
        });

        form.addEventListener("submit", function(event) {
            event.preventDefault();

            const formData = new FormData(form);

            const modal = document.getElementById("uploadModal");
            const bar = document.getElementById("uploadProgressBar");
            const percentText = document.getElementById("uploadPercentText");
            const bytesText = document.getElementById("uploadBytes");

            modal.classList.add("active");

            const xhr = new XMLHttpRequest();
            xhr.open("POST", form.action, true);

            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    bar.style.width = percent + "%";
                    percentText.textContent = percent + "%";

                    const loadedKB = (e.loaded / 1024).toFixed(0);
                    const totalKB = (e.total / 1024).toFixed(0);
                    bytesText.textContent = `${loadedKB} KB / ${totalKB} KB`;
                }
            };

            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);

                    if (response.success) {
                        bar.style.width = "100%";
                        percentText.textContent = "100%";

                        setTimeout(() => {
                            window.location.href = '<?= neiva_app_url('Arbimaps/index.php?page=baqueanos/solicitudes/vistas/detalles_cargar_cuenta') ?>';
                        }, 600);
                    } else {
                        modal.classList.remove("active");
                        Swal.fire("Error", response.message, "error");
                    }
                } else {
                    modal.classList.remove("active");
                    Swal.fire("Error", "Error del servidor", "error");
                }
            };

            xhr.onerror = function() {
                modal.classList.remove("active");
                Swal.fire("Error", "Falló la carga", "error");
            };

            xhr.send(formData);
        });

    });
</script>