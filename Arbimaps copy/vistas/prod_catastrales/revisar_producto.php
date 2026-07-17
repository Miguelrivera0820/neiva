<?php
require_once __DIR__ . '/funciones_productos.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$codigoProducto = trim((string) ($_GET['codigo'] ?? ''));
$codigoSeguro = normalizarCodigoProducto($codigoProducto);
$producto = null;
$errorRevision = '';
$documentosProducto = [];
$documentoFirmadoCargado = false;

if (!usuarioProductoTieneRol(['coordinacion_tecnica', 'administrador'])) {
    $errorRevision = 'No tiene permisos para revisar productos catastrales.';
} elseif ($codigoSeguro === '' || $codigoSeguro !== $codigoProducto) {
    $errorRevision = 'El código del producto no es válido.';
} elseif (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    $errorRevision = 'No fue posible conectar con la base de datos.';
} else {
    asegurarFlujoProductosCatastrales($mysqli);
    $stmt = $mysqli->prepare(
        "SELECT c.codigo_certificado, c.prod_tipo_producto, c.estado,
                f.editor_nombre, f.observacion_revision
         FROM certificado_catastral c
         LEFT JOIN producto_catastral_flujo f ON f.codigo_producto = c.codigo_certificado
         WHERE c.codigo_certificado = ?
           AND c.prod_tipo_producto IS NOT NULL
           AND TRIM(c.prod_tipo_producto) <> ''
         LIMIT 1"
    );
    if ($stmt) {
        $stmt->bind_param('s', $codigoProducto);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $producto = $resultado ? $resultado->fetch_assoc() : null;
        $stmt->close();
    }

    if (!$producto) {
        $errorRevision = 'No se encontró la solicitud.';
    } elseif (strtoupper(trim((string) $producto['estado'])) !== ESTADO_PRODUCTO_PENDIENTE_APROBACION) {
        $errorRevision = 'La solicitud no está pendiente de aprobación.';
    } else {
        $documentosProducto = listarDocumentosProducto($codigoProducto);
        $documentoFirmadoCargado = is_file(rutaMarcaDocumentoFirmadoProducto($codigoProducto));
    }
}

if (empty($_SESSION['csrf_revisar_producto'])) {
    $_SESSION['csrf_revisar_producto'] = bin2hex(random_bytes(32));
}
$tokenRevision = $_SESSION['csrf_revisar_producto'];
?>

<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-header text-white py-3" style="background:#003b64">
            <h1 class="h5 mb-0"><i class="bi bi-clipboard-check me-2"></i>Revisión del producto catastral</h1>
        </div>
        <div class="card-body p-4">
            <?php if ($errorRevision !== ''): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($errorRevision, ENT_QUOTES, 'UTF-8') ?></div>
            <?php else: ?>
                <?php if (($_GET['resultado'] ?? '') === 'reemplazado'): ?>
                    <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i>El documento firmado reemplazó correctamente al documento anterior.</div>
                <?php endif; ?>
                <div class="row g-3 mb-4">
                    <div class="col-md-4"><span class="text-muted d-block small">Código</span><strong><?= htmlspecialchars($codigoProducto, ENT_QUOTES, 'UTF-8') ?></strong></div>
                    <div class="col-md-4"><span class="text-muted d-block small">Producto</span><strong><?= htmlspecialchars(ucfirst(str_replace('_', ' ', (string) $producto['prod_tipo_producto'])), ENT_QUOTES, 'UTF-8') ?></strong></div>
                    <div class="col-md-4"><span class="text-muted d-block small">Editor</span><strong><?= htmlspecialchars((string) ($producto['editor_nombre'] ?? 'Sin registrar'), ENT_QUOTES, 'UTF-8') ?></strong></div>
                </div>

                <h2 class="h6">Documentos entregados</h2>
                <?php if (count($documentosProducto) === 0): ?>
                    <div class="alert alert-warning">No hay documentos para revisar.</div>
                <?php else: ?>
                    <div class="d-grid gap-3 mb-4">
                        <?php foreach ($documentosProducto as $documento): ?>
                            <div class="border rounded p-3">
                                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                                    <div>
                                        <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                                        <strong><?= htmlspecialchars($documento['nombre'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    </div>
                                    <a href="<?= htmlspecialchars($documento['url'], ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-outline-primary" download>
                                        <i class="bi bi-download me-1"></i>Descargar
                                    </a>
                                </div>
                                <form action="vistas/prod_catastrales/acciones/revisar_producto.php" method="post" enctype="multipart/form-data" class="mt-3">
                                    <input type="hidden" name="accion" value="reemplazar">
                                    <input type="hidden" name="codigo_producto" value="<?= htmlspecialchars($codigoProducto, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="nombre_documento" value="<?= htmlspecialchars($documento['nombre'], ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($tokenRevision, ENT_QUOTES, 'UTF-8') ?>">
                                    <label class="form-label small fw-semibold">Reemplazar por documento firmado</label>
                                    <div class="input-group">
                                        <input type="file" name="documento_firmado" class="form-control" accept="application/pdf,.pdf" required>
                                        <button type="submit" class="btn btn-outline-success"><i class="bi bi-arrow-repeat me-1"></i>Reemplazar</button>
                                    </div>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-5">
                            <?php if (!$documentoFirmadoCargado): ?>
                                <div class="small text-warning mb-2"><i class="bi bi-info-circle me-1"></i>Reemplace primero el documento por la versión firmada.</div>
                            <?php endif; ?>
                            <form action="vistas/prod_catastrales/acciones/revisar_producto.php" method="post" onsubmit="return confirm('¿Aprobar definitivamente estos documentos?');">
                                <input type="hidden" name="accion" value="aprobar">
                                <input type="hidden" name="codigo_producto" value="<?= htmlspecialchars($codigoProducto, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($tokenRevision, ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit" class="btn btn-success w-100" <?= $documentoFirmadoCargado ? '' : 'disabled' ?>><i class="bi bi-check-circle me-1"></i>Aprobar documentos</button>
                            </form>
                        </div>
                        <div class="col-md-7">
                            <form action="vistas/prod_catastrales/acciones/revisar_producto.php" method="post">
                                <input type="hidden" name="accion" value="rechazar">
                                <input type="hidden" name="codigo_producto" value="<?= htmlspecialchars($codigoProducto, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($tokenRevision, ENT_QUOTES, 'UTF-8') ?>">
                                <textarea name="observacion" class="form-control mb-2" rows="2" maxlength="2000" placeholder="Motivo de la devolución" required></textarea>
                                <button type="submit" class="btn btn-danger w-100"><i class="bi bi-arrow-counterclockwise me-1"></i>Devolver al editor</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <a href="index.php?page=prod_catastrales/consultar_producto" class="btn btn-outline-secondary mt-4"><i class="bi bi-arrow-left me-1"></i>Volver</a>
        </div>
    </div>
</div>
