<?php
require_once __DIR__ . '/../prod_catastrales/funciones_productos.php';

$codigoCertificado = trim((string) ($_GET['codigo'] ?? ''));
$codigoSeguro = preg_replace('/[^A-Za-z0-9_-]/', '', $codigoCertificado);
$certificado = null;
$errorCertificado = '';

if ($codigoCertificado === '' || $codigoSeguro !== $codigoCertificado) {
    $errorCertificado = 'El código del certificado no es válido.';
} elseif (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    $errorCertificado = 'No fue posible establecer la conexión con la base de datos.';
} elseif (!asegurarColumnaProductosCatastrales($mysqli)) {
    $errorCertificado = 'No fue posible preparar la estructura de certificados.';
} else {
    $sqlCertificado = "SELECT
        codigo_certificado,
        cert_primer_nombre_interesado,
        cert_segundo_nombre_interesado,
        cert_primer_apellido_interesado,
        cert_segundo_apellido_interesado,
        cert_soporte_pago
    FROM certificado_catastral
    WHERE codigo_certificado = ?
      AND (prod_tipo_producto IS NULL OR TRIM(prod_tipo_producto) = '')
    LIMIT 1";

    $stmtCertificado = $mysqli->prepare($sqlCertificado);
    if (!$stmtCertificado) {
        $errorCertificado = 'No fue posible consultar el certificado.';
    } else {
        $stmtCertificado->bind_param('s', $codigoCertificado);
        $stmtCertificado->execute();
        $resultadoCertificado = $stmtCertificado->get_result();
        $certificado = $resultadoCertificado ? $resultadoCertificado->fetch_assoc() : null;
        $stmtCertificado->close();

        if (!$certificado) {
            $errorCertificado = 'No se encontró la solicitud de certificado catastral.';
        }
    }
}

$nombreSolicitante = '';
$soportePago = '';
$rutaSoporte = '';
$rutaSoporteValida = false;

if ($certificado) {
    $nombreSolicitante = implode(' ', array_filter([
        trim((string) ($certificado['cert_primer_nombre_interesado'] ?? '')),
        trim((string) ($certificado['cert_segundo_nombre_interesado'] ?? '')),
        trim((string) ($certificado['cert_primer_apellido_interesado'] ?? '')),
        trim((string) ($certificado['cert_segundo_apellido_interesado'] ?? '')),
    ], static function ($parte) {
        return $parte !== '';
    }));

    $soportePago = trim((string) ($certificado['cert_soporte_pago'] ?? ''));
    $rutaSoporte = str_replace('\\', '/', $soportePago);
    $rutaSoporteValida = $rutaSoporte !== ''
        && strpos($rutaSoporte, 'soportes_pago/') === 0
        && strpos($rutaSoporte, '..') === false
        && strpos($rutaSoporte, "\0") === false;
}
?>

<style>
    .gestion-pago-certificado .card-gestion {
        border: 1px solid #e1e8e4;
        border-radius: 16px;
        overflow: hidden;
    }

    .gestion-pago-certificado .encabezado-gestion {
        background: linear-gradient(135deg, #002f55, #0f5699);
        color: #fff;
    }

    .gestion-pago-certificado .panel-pago {
        border: 1px solid #dfe7e3;
        border-radius: 14px;
    }
</style>

<div class="container-fluid gestion-pago-certificado">
    <div class="card card-gestion shadow-sm mb-4">
        <div class="encabezado-gestion p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <h1 class="h4 mb-1">Soporte de pago</h1>
                <p class="mb-0 text-white-50">Carga o consulta el soporte de pago del certificado.</p>
            </div>
            <a href="index.php?page=cert_catastrales/consulta_cert_catastrales" class="btn btn-light fw-semibold">
                <i class="bi bi-arrow-left me-1"></i>Volver a certificados
            </a>
        </div>

        <div class="card-body p-4">
            <?php if ($errorCertificado !== ''): ?>
                <div class="alert alert-danger mb-0" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($errorCertificado, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php else: ?>
                <?php if (($_GET['resultado'] ?? '') === 'pago'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>El soporte de pago se actualizó correctamente.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                <?php endif; ?>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="small text-muted">Código del certificado</div>
                        <div class="fw-bold text-primary"><?= htmlspecialchars($codigoCertificado, ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Solicitante</div>
                        <div class="fw-semibold"><?= htmlspecialchars($nombreSolicitante ?: 'Sin registrar', ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <section class="panel-pago p-4">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                <div>
                                    <h2 class="h5 mb-1">Estado de pago</h2>
                                    <p class="small text-muted mb-0">El estado cambia a pagado al registrar el soporte.</p>
                                </div>
                                <?php if ($soportePago !== ''): ?>
                                    <span class="badge text-bg-success">Pagado</span>
                                <?php else: ?>
                                    <span class="badge text-bg-warning">Pendiente</span>
                                <?php endif; ?>
                            </div>

                            <?php if ($soportePago !== ''): ?>
                                <div class="alert alert-success mb-3">
                                    <i class="bi bi-check-circle-fill me-2"></i>El soporte de pago ya está registrado.
                                </div>
                                <?php if ($rutaSoporteValida): ?>
                                    <a
                                        href="<?= htmlspecialchars($rutaSoporte, ENT_QUOTES, 'UTF-8') ?>"
                                        class="btn btn-outline-primary"
                                        target="_blank"
                                        rel="noopener noreferrer">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>Ver soporte de pago
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <form action="vistas/cert_catastrales/acciones/cargar_soporte_pago.php" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="codigo_certificado" value="<?= htmlspecialchars($codigoCertificado, ENT_QUOTES, 'UTF-8') ?>">
                                    <label for="soporte_pago_certificado" class="form-label fw-semibold">Seleccionar soporte PDF</label>
                                    <input
                                        type="file"
                                        class="form-control"
                                        id="soporte_pago_certificado"
                                        name="soporte_pago_certificado"
                                        accept="application/pdf,.pdf"
                                        required>
                                    <div class="form-text">Solo PDF, máximo 20 MB.</div>
                                    <button type="submit" class="btn btn-success mt-3">
                                        <i class="bi bi-receipt me-1"></i>Subir soporte de pago
                                    </button>
                                </form>
                            <?php endif; ?>
                        </section>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
