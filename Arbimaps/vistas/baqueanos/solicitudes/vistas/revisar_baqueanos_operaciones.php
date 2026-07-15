<?php
$where = "";
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../index.php");
    exit();
}
$idUsuario = $_SESSION['id_usuario'];

$stmt = $mysqli->prepare("SELECT rol_usuario, nombre_usuario FROM usuarios_cons WHERE id_usuario = ?");
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$rolUsuario = $_SESSION['rol_usuario'];
$rolesPermitidos = array("administrador", "director_catastro", "soporte", "gerencia");

if (!in_array($rolUsuario, $rolesPermitidos)) {
    header("Location: ../../acceso_denegado.php");
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

$sb_cuenta_baqueano         = $_POST['sb_cuenta_baqueano'] ?? null;
$sb_rut_baqueano            = $_POST['sb_rut_baqueano'] ?? null;
$sb_certificado_baqueano    = $_POST['sb_certificado_baqueano'] ?? null;
$sb_observacion_cuenta      = $_POST['sb_observacion_cuenta'] ?? null;
$ba_periodo_facturacion     = $_POST['ba_periodo_facturacion'] ?? null;

$stmt_cuentas = $mysqli->prepare("SELECT * FROM solicitud_baqueanos WHERE id = ?");
$stmt_cuentas->bind_param("i", $id);

$datos_cuentas = null;
$ruta_cuentas = null;

$row = null;


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

        $sb_cedula_baqueano         = $row['sb_cedula_baqueano'] ?? '';
        $sb_cuenta_baqueano         = $row['sb_cuenta_baqueano'] ?? '';
        $sb_rut_baqueano            = $row['sb_rut_baqueano'] ?? '';
        $sb_certificado_baqueano    = $row['sb_certificado_baqueano'] ?? '';
        $sb_observacion_cuenta      = $row['sb_observacion_cuenta'] ?? '';
        $ba_periodo_facturacion     = $row['ba_periodo_facturacion'] ?? '';

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

$row = $row ?? [];

$valorFormateado    = '$ ' . number_format((float)$sb_cobro_diario, 0, ',', '.');
$valorFormateado1   = '$ ' . number_format((float)$sb_valor_cobrar, 0, ',', '.');

$nombre = $_SESSION['nombre_usuario'];
$mysqli->close();
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
        box-shadow: 0 14px 34px rgba(0, 0, 0, .06);
        overflow: hidden;
        background: #fff;
    }

    .card-header-pro {
        background: linear-gradient(135deg, #002F55 0%, #0b5aa0 100%);
        color: #fff;
        padding: 14px 18px;
    }

    .badge-pro {
        background: rgba(255, 255, 255, .18);
        border: 1px solid rgba(255, 255, 255, .25);
        color: #fff;
        font-weight: 600;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: .78rem;
    }

    .section-sep {
        border-bottom: 1px dashed rgba(0, 47, 85, .35);
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

    /* Documentos */
    .doc-card {
        border: 1px solid rgba(0, 0, 0, .10);
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 10px 24px rgba(0, 0, 0, .05);
    }

    .doc-body {
        padding: 12px 14px 14px;
    }

    .doc-file {
        border: 1px solid rgba(0, 0, 0, .12);
        border-radius: 14px;
        background: #f7f9fc;
        padding: 10px 12px;
        margin-bottom: 10px;
    }

    /* Iframe */
    .pdf-frame {
        width: 100%;
        height: 700px;
        border: 0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 10px 24px rgba(0, 0, 0, .06);
        background: #fff;
    }

    /* Botones */
    .action-bar {
        display: flex;
        gap: 12px;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 14px;
    }

    .btn-aprobar-custom {
        border: 2px solid #002F55;
        color: #002F55;
        background: transparent;
        transition: background-color .2s ease, color .2s ease, border-color .2s ease, transform .1s ease;
    }

    .btn-aprobar-custom:hover {
        background: #002F55;
        color: #fff;
    }

    .btn-aprobar-custom:hover i {
        color: #fff;
    }

    .btn-aprobar-custom:active {
        transform: scale(0.98);
    }

    .devolver-box {
        border: 1px dashed rgba(220, 53, 69, .45);
        background: rgba(220, 53, 69, .04);
        border-radius: 16px;
        padding: 14px;
    }

    .swal2-popup.swal-pro {
        padding: 0 !important;
        border-radius: 22px !important;
        width: 600px !important;
        max-width: 95vw !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .swal2-container.pro-backdrop {
        background: rgba(0, 0, 0, .18) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .swal-pro-card {
        background: #fff;
        border-radius: 22px;
        box-shadow: 0 18px 60px rgba(0, 0, 0, .16);
        padding: 26px 28px 28px;
    }

    .swal-pro-header {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        font-weight: 700;
        font-size: 18px;
    }

    .swal-pro-divider {
        height: 1px;
        background: rgba(0, 0, 0, .08);
        margin: 14px 0 22px;
    }

    .swal-pro-body {
        text-align: center;
    }

    .swal-pro-text {
        color: #6c757d;
        font-size: 16px;
        margin-top: 10px;
    }

    .btn-swal-primary {
        background-color: #022F55;
        color: #ffffff;
        border: none;
        border-radius: 12px;
        padding: .5rem 1.5rem;
        transition: .2s ease;
    }

    .btn-swal-primary:hover {
        background-color: #011f3a;
    }

    .sticky-actions-wrapper {
        position: sticky;
        bottom: 20px;
        z-index: 50;
        margin-top: 40px;
    }

    .sticky-actions-card {
        max-width: 400px;
        margin: auto;
        padding: 18px 20px;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(0, 0, 0, .08);
        box-shadow: 0 18px 40px rgba(0, 0, 0, .08);
        transition: all .3s ease;
    }

    .devolver-animado {
        animation: fadeSlide .35s ease forwards;
    }

    @keyframes fadeSlide {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="container-fluid page-wrap">
    <div class="my-4 text-center">
        <h4 class="mb-1 page-title">APROBAR RADICACIONES DE SOLICITUDES GERENCIA</h4>
        <div class="subtle">En este módulo está la cuenta radicada por el personal baqueano.</div>
    </div>

    <div class="container-xl my-4">
        <form id="formulario_editar" action="./vistas/baqueanos/solicitudes/acciones/acciones_radicacion_operaciones.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="id" name="id" value="<?php echo htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="accion" id="accion" value="">

            <div class="row g-3 align-items-stretch">
                <div class="col-12 col-lg-10 mx-auto">
                    <div class="card card-pro h-100">
                        <div class="card-header-pro">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-semibold">Información de la cuenta</div>
                                    <div class="small" style="opacity:.85;">Consulta datos y revisa documentos antes de aprobar o devolver</div>
                                </div>
                                <span class="badge-pro">Operaciones</span>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="container py-2">
                                <div class="row gy-3 gx-3">
                                    <div class="col-12 col-md-6">
                                        <div class="info-box">
                                            <div class="info-label">Tipo de documento</div>
                                            <div class="info-value"><?php echo htmlspecialchars((string)($sb_tipo_documento ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="info-box">
                                            <div class="info-label">Número documento</div>
                                            <div class="info-value"><?php echo htmlspecialchars((string)($sb_numero_identidad ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="info-box">
                                            <div class="info-label">Nombres</div>
                                            <div class="info-value"><?php echo htmlspecialchars((string)($sb_baqueano_nombre ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="info-box">
                                            <div class="info-label">Apellidos</div>
                                            <div class="info-value"><?php echo htmlspecialchars((string)($sb_baqueano_apellido ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="info-box">
                                            <div class="info-label">Teléfono</div>
                                            <div class="info-value"><?php echo htmlspecialchars((string)($sb_telefono_baqueano ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="info-box text-center">
                                            <div class="info-label">Correo electrónico</div>
                                            <div class="info-value"><?php echo htmlspecialchars((string)($sb_correo_baqueano ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="section-sep"></div>
                                    </div>

                                    <div class="col-12 d-flex align-items-center justify-content-between">
                                        <div class="text-uppercase small text-secondary fw-semibold">Fechas y valores</div>
                                        <span class="subtle small">Solo lectura</span>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="info-box">
                                            <div class="info-label">Año</div>
                                            <div class="info-value"><?php echo htmlspecialchars((string)($sb_year ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="info-box">
                                            <div class="info-label">Fecha inicio</div>
                                            <div class="info-value"><?php echo htmlspecialchars((string)($sb_fecha_inicio ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="info-box">
                                            <div class="info-label">Fecha fin</div>
                                            <div class="info-value"><?php echo htmlspecialchars((string)($sb_fecha_fin ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="info-box">
                                            <div class="info-label">Total de días</div>
                                            <div class="info-value"><?php echo htmlspecialchars((string)($sb_dias_calculados ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="info-box">
                                            <div class="info-label">Valor diario</div>
                                            <div class="info-value"><?php echo htmlspecialchars((string)($valorFormateado ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="info-box">
                                            <div class="info-label">Total a cobrar</div>
                                            <div class="info-value"><?php echo htmlspecialchars((string)($valorFormateado1 ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="section-sep"></div>
                                    </div>

                                    <div class="col-12 d-flex align-items-center justify-content-between">
                                        <div class="text-uppercase small text-secondary fw-semibold">Ubicación y unidad</div>
                                        <span class="subtle small">Solo lectura</span>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="info-box">
                                            <div class="info-label">Unidad intervención</div>
                                            <div class="info-value"><?php echo htmlspecialchars((string)($sb_unidad_intervencion ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="info-box">
                                            <div class="info-label">Unidad operativa</div>
                                            <div class="info-value"><?php echo htmlspecialchars((string)($sb_unidad_operativa ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="info-box">
                                            <div class="info-label">Tipo de unidad</div>
                                            <div class="info-value"><?php echo htmlspecialchars((string)($sb_tipo_unidad ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="info-box">
                                            <div class="info-label">Municipio</div>
                                            <div class="info-value"><?php echo htmlspecialchars((string)($sb_municipio ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="info-box">
                                            <div class="info-label">Vereda / barrio</div>
                                            <div class="info-value"><?php echo htmlspecialchars((string)($sb_vereda ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="info-box">
                                            <div class="info-label">Profesional asignado</div>
                                            <div class="info-value"><?php echo htmlspecialchars((string)($sb_profesional_baqueano ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="info-box">
                                            <div class="info-label">Observaciones</div>
                                            <div class="info-value"><?php echo htmlspecialchars((string)($sb_observacion_cuenta ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                    </div>

                                </div><!-- row -->
                            </div><!-- container -->
                        </div><!-- card-body -->
                    </div><!-- card -->
                </div><!-- col -->

                <!-- ===== Documentos PDF ===== -->
                <div class="col-12 col-lg-12 mx-auto">
                    <div class="card card-pro h-100">
                        <div class="text-dark px-3">
                            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <div class="fw-semibold">Documentos PDF</div>
                                <span class="fw-semibold">Radicados</span>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row g-3 align-items-stretch">
                                <?php $iframe_h = 480; ?>

                                <!-- Cedula -->
                                <div class="col-12 col-md-6 d-flex">
                                    <div class="doc-card mb-2 h-100 w-100">
                                        <div class="doc-body py-2">
                                            <div class="doc-file mb-2">
                                                <div class="info-label text-center fw-bold">Cédula Baqueano</div>
                                                <div class="info-label text-center">
                                                    <?php echo htmlspecialchars((string)($row['sb_cedula_baqueano'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                            </div>

                                            <?php
                                            if (!empty($row['sb_cedula_baqueano'])) {
                                                $base_upload_dir = '../Arbimaps/vistas/baqueanos/solicitudes/DOCUMENTOS_BAQUENOS/cuentas_baqueanos/';
                                                $year = (int)($row['sb_year'] ?? 0);
                                                $ruta = $base_upload_dir . $year . '/' . ($row['ba_periodo_facturacion'] ?? '') . '/RAD_' . ($row['id'] ?? '') . '/cedula/' . $row['sb_cedula_baqueano'];
                                            ?>
                                                <div class="d-flex gap-2 justify-content-center mb-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="togglePreview(this)">
                                                        <i class="bi bi-eye"></i> Abrir vista previa
                                                    </button>
                                                    <a class="btn btn-sm btn-outline-primary"
                                                        href="<?php echo htmlspecialchars($ruta, ENT_QUOTES, 'UTF-8'); ?>"
                                                        target="_blank" rel="noopener">
                                                        <i class="bi bi-box-arrow-up-right"></i> Ver en otra pestaña
                                                    </a>
                                                </div>

                                                <iframe src="<?php echo htmlspecialchars($ruta, ENT_QUOTES, 'UTF-8'); ?>"
                                                    class="pdf-frame w-100 d-none"
                                                    style="height:<?php echo (int)$iframe_h; ?>px"
                                                    frameborder="0"></iframe>
                                            <?php
                                            } else {
                                            ?>
                                                <div class="d-flex gap-2 justify-content-center mb-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                                                        <i class="bi bi-eye"></i> Abrir vista previa
                                                    </button>
                                                    <a class="btn btn-sm btn-outline-primary disabled" href="#" tabindex="-1" aria-disabled="true">
                                                        <i class="bi bi-box-arrow-up-right"></i> Ver en otra pestaña
                                                    </a>
                                                </div>

                                                <div class='subtle small text-center d-flex align-items-center justify-content-center'
                                                    style='height:<?php echo (int)$iframe_h; ?>px'>No hay documento cargado.</div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- RUT -->
                                <div class="col-12 col-md-6 d-flex">
                                    <div class="doc-card mb-2 h-100 w-100">
                                        <div class="doc-body py-2">
                                            <div class="doc-file mb-2">
                                                <div class="info-label text-center fw-bold">RUT</div>
                                                <div class="info-label text-center">
                                                    <?php echo htmlspecialchars((string)($row['sb_rut_baqueano'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                            </div>

                                            <?php
                                            if (!empty($row['sb_rut_baqueano'])) {
                                                $base_upload_dir = '../Arbimaps/vistas/baqueanos/solicitudes/DOCUMENTOS_BAQUENOS/cuentas_baqueanos/';
                                                $year = (int)($row['sb_year'] ?? 0);
                                                $ruta = $base_upload_dir . $year . '/' . ($row['ba_periodo_facturacion'] ?? '') . '/RAD_' . ($row['id'] ?? '') . '/RUT/' . $row['sb_rut_baqueano'];
                                            ?>
                                                <div class="d-flex gap-2 justify-content-center mb-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="togglePreview(this)">
                                                        <i class="bi bi-eye"></i> Abrir vista previa
                                                    </button>
                                                    <a class="btn btn-sm btn-outline-primary"
                                                        href="<?php echo htmlspecialchars($ruta, ENT_QUOTES, 'UTF-8'); ?>"
                                                        target="_blank" rel="noopener">
                                                        <i class="bi bi-box-arrow-up-right"></i> Ver en otra pestaña
                                                    </a>
                                                </div>

                                                <iframe src="<?php echo htmlspecialchars($ruta, ENT_QUOTES, 'UTF-8'); ?>"
                                                    class="pdf-frame w-100 d-none"
                                                    style="height:<?php echo (int)$iframe_h; ?>px"
                                                    frameborder="0"></iframe>
                                            <?php
                                            } else {
                                            ?>
                                                <div class="d-flex gap-2 justify-content-center mb-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                                                        <i class="bi bi-eye"></i> Abrir vista previa
                                                    </button>
                                                    <a class="btn btn-sm btn-outline-primary disabled" href="#" tabindex="-1" aria-disabled="true">
                                                        <i class="bi bi-box-arrow-up-right"></i> Ver en otra pestaña
                                                    </a>
                                                </div>

                                                <div class='subtle small text-center d-flex align-items-center justify-content-center'
                                                    style='height:<?php echo (int)$iframe_h; ?>px'>No hay documento cargado.</div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Certificado -->
                                <div class="col-12 col-md-6 d-flex">
                                    <div class="doc-card mb-2 h-100 w-100">
                                        <div class="doc-body py-2">
                                            <div class="doc-file mb-2">
                                                <div class="info-label text-center fw-bold">Certificado Bancario</div>
                                                <div class="info-label text-center">
                                                    <?php echo htmlspecialchars((string)($row['sb_certificado_baqueano'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                            </div>

                                            <?php
                                            if (!empty($row['sb_certificado_baqueano'])) {
                                                $base_upload_dir = '../Arbimaps/vistas/baqueanos/solicitudes/DOCUMENTOS_BAQUENOS/cuentas_baqueanos/';
                                                $year = (int)($row['sb_year'] ?? 0);
                                                $ruta = $base_upload_dir . $year . '/' . ($row['ba_periodo_facturacion'] ?? '') . '/RAD_' . ($row['id'] ?? '') . '/certificado_bancario/' . $row['sb_certificado_baqueano'];
                                            ?>
                                                <div class="d-flex gap-2 justify-content-center mb-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="togglePreview(this)">
                                                        <i class="bi bi-eye"></i> Abrir vista previa
                                                    </button>
                                                    <a class="btn btn-sm btn-outline-primary"
                                                        href="<?php echo htmlspecialchars($ruta, ENT_QUOTES, 'UTF-8'); ?>"
                                                        target="_blank" rel="noopener">
                                                        <i class="bi bi-box-arrow-up-right"></i> Ver en otra pestaña
                                                    </a>
                                                </div>

                                                <iframe src="<?php echo htmlspecialchars($ruta, ENT_QUOTES, 'UTF-8'); ?>"
                                                    class="pdf-frame w-100 d-none"
                                                    style="height:<?php echo (int)$iframe_h; ?>px"
                                                    frameborder="0"></iframe>
                                            <?php
                                            } else {
                                            ?>
                                                <div class="d-flex gap-2 justify-content-center mb-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                                                        <i class="bi bi-eye"></i> Abrir vista previa
                                                    </button>
                                                    <a class="btn btn-sm btn-outline-primary disabled" href="#" tabindex="-1" aria-disabled="true">
                                                        <i class="bi bi-box-arrow-up-right"></i> Ver en otra pestaña
                                                    </a>
                                                </div>

                                                <div class='subtle small text-center d-flex align-items-center justify-content-center'
                                                    style='height:<?php echo (int)$iframe_h; ?>px'>No hay documento cargado.</div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cuenta de cobro -->
                                <div class="col-12 col-md-6 d-flex">
                                    <div class="doc-card mb-2 h-100 w-100">
                                        <div class="doc-body py-2">
                                            <div class="doc-file mb-2">
                                                <div class="info-label text-center fw-bold">Cuenta de Cobro</div>
                                                <div class="info-label text-center">
                                                    <?php echo htmlspecialchars((string)($row['sb_cuenta_baqueano'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                            </div>

                                            <?php
                                            if (!empty($row['sb_cuenta_baqueano'])) {
                                                $base_upload_dir = '../Arbimaps/vistas/baqueanos/solicitudes/DOCUMENTOS_BAQUENOS/cuentas_baqueanos/';
                                                $year = (int)($row['sb_year'] ?? 0);
                                                $ruta = $base_upload_dir . $year . '/' . ($row['ba_periodo_facturacion'] ?? '') . '/RAD_' . ($row['id'] ?? '') . '/cuenta_de_cobro/' . $row['sb_cuenta_baqueano'];
                                            ?>
                                                <div class="d-flex gap-2 justify-content-center mb-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="togglePreview(this)">
                                                        <i class="bi bi-eye"></i> Abrir vista previa
                                                    </button>
                                                    <a class="btn btn-sm btn-outline-primary"
                                                        href="<?php echo htmlspecialchars($ruta, ENT_QUOTES, 'UTF-8'); ?>"
                                                        target="_blank" rel="noopener">
                                                        <i class="bi bi-box-arrow-up-right"></i> Ver en otra pestaña
                                                    </a>
                                                </div>

                                                <iframe src="<?php echo htmlspecialchars($ruta, ENT_QUOTES, 'UTF-8'); ?>"
                                                    class="pdf-frame w-100 d-none"
                                                    style="height:<?php echo (int)$iframe_h; ?>px"
                                                    frameborder="0"></iframe>
                                            <?php
                                            } else {
                                            ?>
                                                <div class="d-flex gap-2 justify-content-center mb-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                                                        <i class="bi bi-eye"></i> Abrir vista previa
                                                    </button>
                                                    <a class="btn btn-sm btn-outline-primary disabled" href="#" tabindex="-1" aria-disabled="true">
                                                        <i class="bi bi-box-arrow-up-right"></i> Ver en otra pestaña
                                                    </a>
                                                </div>

                                                <div class='subtle small text-center d-flex align-items-center justify-content-center'
                                                    style='height:<?php echo (int)$iframe_h; ?>px'>No hay documento cargado.</div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sticky-actions-wrapper">
                    <div class="sticky-actions-card">

                        <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                            <button
                                type="button"
                                id="btn-aprobar"
                                class="btn btn-aprobar-custom px-4 fw-bold shadow-sm rounded-3"
                                onclick="accionFormulario('aprobar')">
                                <i class="fas fa-check-circle me-2"></i> Aprobar
                            </button>

                            <button
                                type="button"
                                id="btn-devolver"
                                class="btn btn-outline-danger px-4 fw-bold shadow-sm rounded-3"
                                onclick="accionFormulario('devolver')">
                                <i class="fas fa-undo-alt me-2"></i> Devolver
                            </button>
                        </div>

                        <div id="campo-observacion" class="mt-4 devolver-animado" style="display:none;">
                            <div class="border rounded-4 p-3 bg-light">
                                <div class="fw-bold mb-2" style="color:#002F55;">
                                    Razón de devolución
                                </div>
                                <textarea
                                    class="form-control rounded-3"
                                    id="sb_razon_pagos"
                                    name="sb_razon_pagos"
                                    rows="3"
                                    placeholder="Escriba la razón por la cual devuelve la solicitud"></textarea>

                                <div class="text-center mt-3">
                                    <button class="btn btn-danger px-4 fw-bold shadow-sm rounded-3"
                                        id="enviar-devulucion">
                                        <i class="fas fa-paper-plane me-2"></i> Enviar Razón
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function accionFormulario(accion) {

        if (accion === 'devolver') {
            document.getElementById('campo-observacion').style.display = 'block';
            document.getElementById('sb_razon_pagos').focus();
            return;
        }

        Swal.fire({
            customClass: {
                popup: 'swal-pro',
                container: 'pro-backdrop',
            },
            showConfirmButton: false,
            background: 'transparent',
            html: `
            <div class="swal-pro-card">
                <div class="swal-pro-header" style="color:#002F55;">
                    <i class="bi bi-check-circle-fill"></i>
                    ¿Aprobar solicitud?
                </div>
                <div class="swal-pro-divider"></div>
                <div class="swal-pro-body">
                    <i class="bi bi-patch-check-fill text-success"
                        style="font-size:75px;opacity:.15;"></i>
                    <div class="swal-pro-text">
                        La solicitud será aprobada definitivamente.
                    </div>
                    <div class="mt-4 d-flex justify-content-center gap-3">
                        <button onclick="Swal.close()" 
                            class="btn btn-outline-secondary">
                            Cancelar
                        </button>
                        <button onclick="confirmarAccion('aprobar')" 
                            class="btn-swal-primary">
                            Sí, aprobar
                        </button>
                    </div>
                </div>
            </div>`
        });
    }

    function confirmarAccion(accion) {
        Swal.close();
        document.getElementById('accion').value = accion;
        document.getElementById('formulario_editar').submit();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const btnEnviar = document.getElementById('enviar-devulucion');
        if (!btnEnviar) return;

        btnEnviar.addEventListener('click', function(e) {
            e.preventDefault();
            const razon = document.getElementById('sb_razon_pagos').value.trim();
            if (!razon) {
                Swal.fire({
                    customClass: {
                        popup: 'swal-pro',
                        container: 'pro-backdrop',
                    },
                    showConfirmButton: false,
                    background: 'transparent',
                    html: `
                    <div class="swal-pro-card">
                        <div class="swal-pro-header" style="color:#dc3545;">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            Campo requerido
                        </div>
                        <div class="swal-pro-divider"></div>
                        <div class="swal-pro-body">
                            <div class="swal-pro-text">
                                Debe escribir una razón por la cual devuelve la solicitud.
                            </div>
                            <div class="mt-4">
                                <button onclick="Swal.close()" 
                                    class="btn-swal-primary">
                                    Entendido
                                </button>
                            </div>
                        </div>
                    </div>`
                });
                return;
            }

            Swal.fire({
                customClass: {
                    popup: 'swal-pro',
                    container: 'pro-backdrop',
                },
                showConfirmButton: false,
                background: 'transparent',
                html: `
                <div class="swal-pro-card">
                    <div class="swal-pro-header" style="color:#dc3545;">
                        <i class="bi bi-reply-fill"></i>
                        ¿Devolver solicitud?
                    </div>
                    <div class="swal-pro-divider"></div>
                    <div class="swal-pro-body">
                        <i class="bi bi-exclamation-triangle-fill text-danger"
                            style="font-size:75px;opacity:.15;"></i>
                        <div class="swal-pro-text">
                            Esta acción devolverá la solicitud.
                        </div>
                        <div class="mt-4 d-flex justify-content-center gap-3">
                            <button onclick="Swal.close()" 
                                class="btn btn-outline-secondary">
                                Cancelar
                            </button>
                            <button onclick="confirmarAccion('devolver')" 
                                class="btn-swal-primary">
                                Sí, devolver
                            </button>
                        </div>
                    </div>
                </div>`
            });
        });
    });

    // Toggle preview (solo UI)
    function togglePreview(btn) {
        const card = btn.closest('.doc-card');
        const iframe = card.querySelector('iframe.pdf-frame');
        if (!iframe) return;

        const isHidden = iframe.classList.toggle('d-none');
        btn.innerHTML = isHidden ?
            '<i class="bi bi-eye"></i> Abrir vista previa' :
            '<i class="bi bi-eye-slash"></i> Cerrar vista previa';
    }
</script>