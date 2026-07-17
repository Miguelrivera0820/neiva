<?php
require_once __DIR__ . '/funciones_productos.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_eliminar_documento_producto'])) {
    $_SESSION['csrf_eliminar_documento_producto'] = bin2hex(random_bytes(32));
}
$tokenEliminarDocumento = $_SESSION['csrf_eliminar_documento_producto'];
$esAdminProducto = usuarioProductoTieneRol('administrador');
$esVentanillaProducto = usuarioProductoTieneRol('ventanilla_catastral');
$esCoordinacionProducto = usuarioProductoTieneRol('coordinacion_tecnica');
$esEditorProducto = usuarioProductoTieneRol('editor');

$codigoProducto = trim($_GET['codigo'] ?? '');
$seccion = strtolower(trim($_GET['seccion'] ?? 'documentos'));
if (!in_array($seccion, ['documentos', 'pago'], true)) {
    $seccion = 'documentos';
}

$tituloGestion = $seccion === 'pago' ? 'Soporte de pago' : 'Documentos del producto catastral';
$descripcionGestion = $seccion === 'pago'
    ? 'Carga o consulta el soporte de pago de la solicitud.'
    : 'Carga y consulta los documentos entregables de la solicitud.';
$producto = null;
$errorProducto = '';

if ($codigoProducto === '' || normalizarCodigoProducto($codigoProducto) !== $codigoProducto) {
    $errorProducto = 'El código de producto no es válido.';
} elseif (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    $errorProducto = 'No fue posible establecer la conexión con la base de datos.';
} else {
    asegurarFlujoProductosCatastrales($mysqli);
    $sqlProducto = "SELECT
        codigo_certificado,
        certificado_hora_creacion,
        cert_primer_nombre_interesado,
        cert_segundo_nombre_interesado,
        cert_primer_apellido_interesado,
        cert_segundo_apellido_interesado,
        cert_soporte_pago,
        prod_tipo_producto,
        estado
    FROM certificado_catastral
    WHERE codigo_certificado = ?
      AND prod_tipo_producto IS NOT NULL
      AND TRIM(prod_tipo_producto) <> ''
    LIMIT 1";

    $stmtProducto = $mysqli->prepare($sqlProducto);
    if (!$stmtProducto) {
        $errorProducto = 'No fue posible consultar la solicitud.';
    } else {
        $stmtProducto->bind_param('s', $codigoProducto);
        $stmtProducto->execute();
        $resultadoProducto = $stmtProducto->get_result();
        $producto = $resultadoProducto ? $resultadoProducto->fetch_assoc() : null;
        $stmtProducto->close();

        if (!$producto) {
            $errorProducto = 'No se encontró la solicitud de producto catastral.';
        }
    }
}

$nombresProductos = [
    'Carta_Catastral_Rural' => 'Carta catastral rural',
    'Carta_Catastral_Urbana' => 'Carta catastral urbana',
    'Plano_Predial_Catastral' => 'Plano predial catastral',
];

$documentosProducto = $producto ? listarDocumentosProducto($codigoProducto) : [];
$estadoProducto = $producto ? trim((string) ($producto['estado'] ?? '')) : '';
$estadoProductoNormalizado = strtoupper($estadoProducto);
$tieneDocumentos = estadoProductoTieneDocumentos($estadoProducto);
$soportePago = $producto ? trim((string) ($producto['cert_soporte_pago'] ?? '')) : '';
$pagoRealizado = $soportePago !== '';
$flujoProducto = $producto ? obtenerFlujoProducto($mysqli, $codigoProducto) : null;
$editorAsignadoActual = $flujoProducto
    && trim((string) ($flujoProducto['editor_cedula'] ?? '')) === cedulaUsuarioProductoActual();
$puedeCargarDocumentos = ($esAdminProducto || ($esEditorProducto && $editorAsignadoActual))
    && in_array($estadoProductoNormalizado, [ESTADO_PRODUCTO_EN_EDITOR, ESTADO_PRODUCTO_DEVOLUCION], true);
$puedeConsultarDocumentos = $esAdminProducto
    || ($esEditorProducto && $editorAsignadoActual)
    || ($esCoordinacionProducto && $estadoProductoNormalizado === ESTADO_PRODUCTO_APROBADO)
    || ($esVentanillaProducto && $estadoProductoNormalizado === ESTADO_PRODUCTO_APROBADO);

if ($producto && $seccion === 'documentos' && !$puedeConsultarDocumentos) {
    $errorProducto = 'No tiene permisos para consultar los documentos de esta solicitud.';
} elseif ($producto && $seccion === 'pago' && !$esVentanillaProducto && !$esAdminProducto) {
    $errorProducto = 'No tiene permisos para gestionar el soporte de pago.';
}
$rutaSoportePago = str_replace('\\', '/', $soportePago);
$rutaSoporteValida = $rutaSoportePago !== ''
    && strpos($rutaSoportePago, 'soportes_pago/') === 0
    && strpos($rutaSoportePago, '..') === false
    && strpos($rutaSoportePago, "\0") === false;

$nombreSolicitante = '';
$nombreProducto = '';
if ($producto) {
    $nombreSolicitante = implode(' ', array_filter([
        trim((string) ($producto['cert_primer_nombre_interesado'] ?? '')),
        trim((string) ($producto['cert_segundo_nombre_interesado'] ?? '')),
        trim((string) ($producto['cert_primer_apellido_interesado'] ?? '')),
        trim((string) ($producto['cert_segundo_apellido_interesado'] ?? '')),
    ], static function ($parte) {
        return $parte !== '';
    }));

    $tipoProducto = trim((string) ($producto['prod_tipo_producto'] ?? ''));
    $nombreProducto = $nombresProductos[$tipoProducto] ?? ucfirst(str_replace('_', ' ', $tipoProducto));
}
?>

<style>
    .gestion-producto .card-gestion {
        border: 1px solid #e1e8e4;
        border-radius: 16px;
        overflow: hidden;
    }

    .gestion-producto .encabezado-gestion {
        background: linear-gradient(135deg, #002f55, #0f5699);
        color: #fff;
    }

    .gestion-producto .panel-carga {
        border: 1px solid #dfe7e3;
        border-radius: 14px;
        height: 100%;
    }

    .gestion-producto .archivo-producto {
        background: #f7f9f8;
        border: 1px solid #e1e8e4;
        border-radius: 10px;
    }
</style>

<div class="container-fluid gestion-producto">
    <div class="card card-gestion shadow-sm mb-4">
        <div class="encabezado-gestion p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <h1 class="h4 mb-1"><?= htmlspecialchars($tituloGestion, ENT_QUOTES, 'UTF-8') ?></h1>
                <p class="mb-0 text-white-50"><?= htmlspecialchars($descripcionGestion, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <a href="index.php?page=prod_catastrales/consultar_producto" class="btn btn-light fw-semibold">
                <i class="bi bi-arrow-left me-1"></i>Volver a solicitudes
            </a>
        </div>

        <div class="card-body p-4">
            <?php if ($errorProducto !== ''): ?>
                <div class="alert alert-danger mb-0" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($errorProducto, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php else: ?>
                <?php if (($_GET['resultado'] ?? '') === 'documentos'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>Los documentos se cargaron correctamente.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                <?php elseif (($_GET['resultado'] ?? '') === 'documento_eliminado'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>El documento se eliminó correctamente.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                <?php elseif (($_GET['resultado'] ?? '') === 'pago'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>El soporte de pago se actualizó correctamente.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                <?php endif; ?>

                <?php if ($estadoProductoNormalizado === ESTADO_PRODUCTO_DEVOLUCION): ?>
                    <div class="alert alert-danger" role="alert">
                        <strong><i class="bi bi-exclamation-circle me-1"></i>Producto devuelto:</strong>
                        <?= htmlspecialchars((string) ($flujoProducto['observacion_revision'] ?? 'Debe corregir los documentos.'), ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="small text-muted">Código</div>
                        <div class="fw-bold text-primary"><?= htmlspecialchars($codigoProducto, ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">Solicitante</div>
                        <div class="fw-semibold"><?= htmlspecialchars($nombreSolicitante ?: 'Sin registrar', ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">Producto solicitado</div>
                        <div class="fw-semibold"><?= htmlspecialchars($nombreProducto, ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                </div>

                <div class="row g-4">
                    <?php if ($seccion === 'documentos'): ?>
                    <div class="col-lg-10 mx-auto">
                        <section class="panel-carga p-4">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                <div>
                                    <h2 class="h5 mb-1">Documentos solicitados</h2>
                                    <p class="small text-muted mb-0">Se guardarán en la carpeta interna del código <?= htmlspecialchars($codigoProducto, ENT_QUOTES, 'UTF-8') ?>.</p>
                                </div>
                                <?php if ($tieneDocumentos): ?>
                                    <span class="badge text-bg-success">Entregados</span>
                                <?php else: ?>
                                    <span class="badge text-bg-warning">Pendientes</span>
                                <?php endif; ?>
                            </div>

                            <?php if ($puedeCargarDocumentos): ?>
                                <form action="vistas/prod_catastrales/acciones/cargar_archivos_producto.php" method="post" enctype="multipart/form-data" onsubmit="return confirm('¿Está seguro de que desea subir los documentos seleccionados?');">
                                    <input type="hidden" name="codigo_producto" value="<?= htmlspecialchars($codigoProducto, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="tipo_carga" value="documentos">
                                    <label for="documentos_producto" class="form-label fw-semibold">Seleccionar documentos PDF</label>
                                    <input
                                        type="file"
                                        class="form-control"
                                        id="documentos_producto"
                                        name="documentos_producto[]"
                                        accept="application/pdf,.pdf"
                                        multiple
                                        required>
                                    <div class="form-text">Puede seleccionar varios PDF. Máximo 20 MB por archivo.</div>
                                    <button type="submit" class="btn btn-primary mt-3">
                                        <i class="bi bi-cloud-arrow-up me-1"></i>Subir documentos
                                    </button>
                                </form>
                            <?php elseif (!$pagoRealizado): ?>
                                <div class="alert alert-warning mb-0" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    El pago está pendiente. Primero debe cargar el soporte de pago para habilitar la carga de documentos.
                                </div>
                            <?php elseif ($estadoProductoNormalizado === ESTADO_PRODUCTO_PENDIENTE_APROBACION): ?>
                                <div class="alert alert-info mb-0" role="alert">
                                    <i class="bi bi-hourglass-split me-2"></i>Los documentos están pendientes de aprobación por Coordinación Técnica.
                                </div>
                            <?php elseif ($estadoProductoNormalizado === ESTADO_PRODUCTO_APROBADO): ?>
                                <div class="alert alert-success mb-0" role="alert">
                                    <i class="bi bi-check-circle-fill me-2"></i>Los documentos fueron aprobados y están disponibles para descarga.
                                </div>
                            <?php endif; ?>

                            <hr class="my-4">
                            <h3 class="h6 mb-3">Documentos cargados (<?= count($documentosProducto) ?>)</h3>
                            <?php if (count($documentosProducto) === 0): ?>
                                <div class="text-muted small">
                                    <i class="bi bi-inbox me-1"></i>Todavía no hay documentos entregados.
                                </div>
                            <?php else: ?>
                                <div class="d-grid gap-2">
                                    <?php foreach ($documentosProducto as $documento): ?>
                                        <div class="archivo-producto p-3 d-flex align-items-center justify-content-between gap-3">
                                            <div class="text-truncate">
                                                <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                                                <span class="fw-semibold"><?= htmlspecialchars($documento['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
                                                <div class="small text-muted ms-4">
                                                    <?= htmlspecialchars(formatearTamanoArchivoProducto($documento['tamano']), ENT_QUOTES, 'UTF-8') ?>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                                <a
                                                    href="<?= htmlspecialchars($documento['url'], ENT_QUOTES, 'UTF-8') ?>"
                                                    class="btn btn-sm btn-outline-primary"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    <?= $estadoProductoNormalizado === ESTADO_PRODUCTO_APROBADO ? 'download' : '' ?>>
                                                    <?= $estadoProductoNormalizado === ESTADO_PRODUCTO_APROBADO ? 'Descargar' : 'Ver' ?>
                                                </a>
                                                <?php if ($puedeCargarDocumentos): ?>
                                                <form
                                                    action="vistas/prod_catastrales/acciones/eliminar_archivo_producto.php"
                                                    method="post"
                                                    class="d-inline"
                                                    onsubmit="return confirm('¿Está seguro de eliminar este documento?');">
                                                    <input type="hidden" name="codigo_producto" value="<?= htmlspecialchars($codigoProducto, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="nombre_documento" value="<?= htmlspecialchars($documento['nombre'], ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($tokenEliminarDocumento, ENT_QUOTES, 'UTF-8') ?>">
                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Eliminar documento"
                                                        aria-label="Eliminar <?= htmlspecialchars($documento['nombre'], ENT_QUOTES, 'UTF-8') ?>">
                                                        <i class="bi bi-trash" aria-hidden="true"></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </section>
                    </div>
                    <?php endif; ?>

                    <?php if ($seccion === 'pago'): ?>
                    <div class="col-lg-8 mx-auto" id="soportePago">
                        <section class="panel-carga p-4">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                <div>
                                    <h2 class="h5 mb-1">Soporte de pago</h2>
                                    <p class="small text-muted mb-0">Actualiza el estado de pago de la solicitud.</p>
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
                                        href="<?= htmlspecialchars($rutaSoportePago, ENT_QUOTES, 'UTF-8') ?>"
                                        class="btn btn-outline-primary"
                                        target="_blank"
                                        rel="noopener noreferrer">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>Ver soporte de pago
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <form action="vistas/prod_catastrales/acciones/cargar_archivos_producto.php" method="post" enctype="multipart/form-data" onsubmit="return confirm('¿Está seguro de que desea subir el soporte de pago?');">
                                    <input type="hidden" name="codigo_producto" value="<?= htmlspecialchars($codigoProducto, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="tipo_carga" value="pago">
                                    <label for="soporte_pago_producto" class="form-label fw-semibold">Seleccionar soporte PDF</label>
                                    <input
                                        type="file"
                                        class="form-control"
                                        id="soporte_pago_producto"
                                        name="soporte_pago_producto"
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
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
