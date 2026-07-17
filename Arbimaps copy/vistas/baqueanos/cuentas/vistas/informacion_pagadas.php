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
$rolesPermitidos = array("administrador", "director_catastro", "gerencia", "soporte");

if (!in_array($rolUsuario, $rolesPermitidos)) {
    header("Location: ../../acceso_denegado.php");
    exit();
}

$idUsuario                  = $_SESSION['id_usuario'];
$id                         = $_GET['ctl_pag_id'] ?? '';
$sb_tipo_documento          = $_GET['ctl_sb_tipo_documento'] ?? null;
$sb_numero_identidad        = $_GET['ctl_sb_numero_identidad'] ?? null;

$sb_baqueano_nombre         = $_POST['ctl_sb_baqueano_nombre'] ?? null;
$sb_baqueano_apellido       = $_POST['ctl_sb_baqueano_apellido'] ?? null;
$sb_telefono_baqueano       = $_POST['ctl_sb_telefono_baqueano'] ?? null;
$sb_correo_baqueano         = $_POST['ctl_sb_correo_baqueano'] ?? null;
$sb_direccion               = $_POST['ctl_sb_direccion'] ?? null;
$sb_cuenta                  = $_POST['ctl_sb_cuenta'] ?? null;
$sb_tipo_cuenta             = $_POST['ctl_sb_tipo_cuenta'] ?? null;
$sb_num_cuenta              = $_POST['ctl_sb_num_cuenta'] ?? null;
$sb_titular                 = $_POST['ctl_sb_titular'] ?? null;

$sb_year                    = $_POST['ctl_sb_year'] ?? null;
$sb_fecha_inicio            = $_POST['ctl_sb_fecha_inicio'] ?? null;
$sb_fecha_fin               = $_POST['ctl_sb_fecha_fin'] ?? null;
$sb_dias_calculados         = $_POST['ctl_sb_dias_calculados'] ?? null;
$sb_cobro_diario            = $_POST['ctl_sb_cobro_diario'] ?? null;
$sb_valor_cobrar            = $_POST['ctl_sb_valor_cobrar'] ?? 0;

$sb_unidad_intervencion     = $_POST['ctl_sb_unidad_intervencion'] ?? null;
$sb_tipo_unidad             = $_POST['ctl_sb_tipo_unidad'] ?? null;
$sb_municipio               = $_POST['ctl_sb_municipio'] ?? null;
$sb_vereda                  = $_POST['ctl_sb_vereda'] ?? null;

$sb_tipo_actividad          = $_POST['ctl_sb_tipo_actividad'] ?? null;
$sb_coordinador             = $_POST['ctl_sb_coordinador'] ?? null;
$sb_lider_cuadrilla         = $_POST['ctl_sb_lider_cuadrilla'] ?? null;
$sb_transporte              = $_POST['ctl_sb_transporte'] ?? null;
$sb_porque_transporte       = $_POST['ctl_sb_porque_transporte'] ?? null;
$sb_hospedaje               = $_POST['ctl_sb_hospedaje'] ?? null;
$sb_porque_hospedaje        = $_POST['ctl_sb_porque_hospedaje'] ?? null;

$sb_profesional_baqueano    = $_POST['ctl_sb_profesional_baqueano'] ?? null;

$sb_cuenta_baqueano         = $_POST['ctl_sb_cuenta_baqueano'] ?? null;
$sb_rut_baqueano            = $_POST['ctl_sb_rut_baqueano'] ?? null;
$sb_certificado_baqueano    = $_POST['ctl_sb_certificado_baqueano'] ?? null;
$sb_observacion_cuenta      = $_POST['ctl_sb_observacion_cuenta'] ?? null;
$ba_periodo_facturacion     = $_POST['ctl_ba_periodo_facturacion'] ?? null;

$stmt_cuentas = $mysqli->prepare("SELECT * FROM solicitudes_pagadas_baqueanos WHERE ctl_pag_id = ?");
$stmt_cuentas->bind_param("i", $id);

$datos_cuentas = null;
$ruta_cuentas = null;

$row = null;

if ($id) {
    $query = "SELECT * FROM solicitudes_pagadas_baqueanos WHERE ctl_pag_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row    = $result->fetch_assoc();
        $id     = $row['ctl_pag_id'] ?? '';

        $sb_numero_identidad        = $row['ctl_sb_numero_identidad'] ?? '';
        $sb_tipo_documento          = $row['ctl_sb_tipo_documento'] ?? '';
        $sb_baqueano_nombre         = $row['ctl_sb_baqueano_nombre'] ?? '';
        $sb_baqueano_apellido       = $row['ctl_sb_baqueano_apellido'] ?? '';
        $sb_telefono_baqueano       = $row['ctl_sb_telefono_baqueano'] ?? '';
        $sb_correo_baqueano         = $row['ctl_sb_correo_baqueano'] ?? '';
        $sb_direccion               = $row['ctl_sb_direccion'] ?? '';
        $sb_cuenta                  = $row['ctl_sb_cuenta'] ?? '';
        $sb_tipo_cuenta             = $row['ctl_sb_tipo_cuenta'] ?? '';
        $sb_num_cuenta              = $row['ctl_sb_num_cuenta'] ?? '';
        $sb_titular                 = $row['ctl_sb_titular'] ?? '';
        $sb_year                    = $row['ctl_sb_year'] ?? '';
        $sb_fecha_inicio            = $row['ctl_sb_fecha_inicio'] ?? '';
        $sb_fecha_fin               = $row['ctl_sb_fecha_fin'] ?? '';
        $sb_dias_calculados         = $row['ctl_sb_dias_calculados'] ?? '';
        $sb_cobro_diario            = $row['ctl_sb_cobro_diario'] ?? '';
        $sb_valor_cobrar            = $row['ctl_sb_valor_cobrar'] ?? 0;

        $sb_unidad_intervencion     = $row['ctl_sb_unidad_intervencion'] ?? '';
        $sb_unidad_operativa        = $row['ctl_sb_unidad_operativa'] ?? '';
        $sb_tipo_unidad             = $row['ctl_sb_tipo_unidad'] ?? '';
        $sb_municipio               = $row['ctl_sb_municipio'] ?? '';
        $sb_vereda                  = $row['ctl_sb_vereda'] ?? '';

        $sb_tipo_actividad          = $row['ctl_sb_tipo_actividad'] ?? '';
        $sb_coordinador             = $row['ctl_sb_coordinador'] ?? '';
        $sb_lider_cuadrilla         = $row['ctl_sb_lider_cuadrilla'] ?? '';
        $sb_transporte              = $row['ctl_sb_transporte'] ?? '';
        $sb_porque_transporte       = $row['ctl_sb_porque_transporte'] ?? '';
        $sb_hospedaje               = $row['ctl_sb_hospedaje'] ?? '';
        $sb_porque_hospedaje        = $row['ctl_sb_porque_hospedaje'] ?? '';
        $sb_profesional_baqueano    = $row['ctl_sb_profesional_baqueano'] ?? '';

        $sb_cedula_baqueano         = $row['ctl_sb_cedula_baqueano'] ?? '';
        $sb_cuenta_baqueano         = $row['ctl_sb_cuenta_baqueano'] ?? '';
        $sb_rut_baqueano            = $row['ctl_sb_rut_baqueano'] ?? '';
        $sb_certificado_baqueano    = $row['ctl_sb_certificado_baqueano'] ?? '';
        $sb_observacion_cuenta      = $row['ctl_sb_observacion_cuenta'] ?? '';
        $ba_periodo_facturacion     = $row['ctl_ba_periodo_facturacion'] ?? '';
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
</style>

<div class="container-fluid page-wrap">
    <div class="my-4 text-center">
        <h4 class="mb-1 page-title">APROBAR RADICACIONES DE SOLICITUDES GERENCIA</h4>
        <div class="subtle">En este módulo está la cuenta radicada por el personal baqueano.</div>
    </div>

    <div class="container-xl my-4">
        <input type="hidden" id="id" name="id" value="<?php echo htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8'); ?>">

        <div class="row g-3 align-items-stretch">
            <div class="col-12 col-lg-10 mx-auto">
                <div class="card card-pro h-100">
                    <div class="card-header-pro">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-semibold">Información de la cuenta</div>
                                <div class="small" style="opacity:.85;">Consulta datos y revisa documentos</div>
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
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