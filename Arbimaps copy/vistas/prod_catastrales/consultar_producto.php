<?php
require_once __DIR__ . '/funciones_productos.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rolProductoActual = (string) ($_SESSION['rol_usuario'] ?? '');
$esAdministradorProducto = usuarioProductoTieneRol('administrador');
$esVentanillaProducto = usuarioProductoTieneRol('ventanilla_catastral');
$esCoordinacionProducto = usuarioProductoTieneRol('coordinacion_tecnica');
$esEditorProducto = usuarioProductoTieneRol('editor');
$cedulaProductoActual = cedulaUsuarioProductoActual();

$solicitudesProductos = [];
$errorConsultaProductos = '';

if (!$esAdministradorProducto && !$esVentanillaProducto && !$esCoordinacionProducto && !$esEditorProducto) {
    $errorConsultaProductos = 'No tiene permisos para consultar productos catastrales.';
} elseif (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    $errorConsultaProductos = 'No fue posible establecer la conexión con la base de datos.';
} else {
    asegurarFlujoProductosCatastrales($mysqli);
    $sqlProductos = "SELECT
        c.certificado_id,
        c.codigo_certificado,
        c.certificado_hora_creacion,
        c.cert_primer_nombre_interesado,
        c.cert_segundo_nombre_interesado,
        c.cert_primer_apellido_interesado,
        c.cert_segundo_apellido_interesado,
        c.cert_soporte_pago,
        c.prod_tipo_producto,
        c.estado,
        f.editor_cedula,
        f.editor_nombre,
        f.observacion_revision
    FROM certificado_catastral c
    LEFT JOIN producto_catastral_flujo f ON f.codigo_producto = c.codigo_certificado
    WHERE c.prod_tipo_producto IS NOT NULL
      AND TRIM(c.prod_tipo_producto) <> ''
    ORDER BY c.certificado_hora_creacion DESC, c.certificado_id DESC";

    $resultadoProductos = $mysqli->query($sqlProductos);

    if ($resultadoProductos === false) {
        $errorConsultaProductos = 'No fue posible consultar las solicitudes de productos catastrales.';
    } else {
        while ($solicitudProducto = $resultadoProductos->fetch_assoc()) {
            $estadoFila = strtoupper(trim((string) ($solicitudProducto['estado'] ?? '')));
            $incluirFila = true;

            if (!$esAdministradorProducto && $esCoordinacionProducto) {
                $incluirFila = in_array($estadoFila, [
                    ESTADO_PRODUCTO_EN_COORDINACION,
                    ESTADO_PRODUCTO_EN_EDITOR,
                    ESTADO_PRODUCTO_PENDIENTE_APROBACION,
                    ESTADO_PRODUCTO_DEVOLUCION,
                    ESTADO_PRODUCTO_APROBADO,
                ], true);
            } elseif (!$esAdministradorProducto && $esEditorProducto) {
                $incluirFila = $cedulaProductoActual !== ''
                    && trim((string) ($solicitudProducto['editor_cedula'] ?? '')) === $cedulaProductoActual;
            }

            if ($incluirFila) {
                $solicitudesProductos[] = $solicitudProducto;
            }
        }
        $resultadoProductos->free();
    }
}

$nombresProductos = [
    'Carta_Catastral_Rural' => 'Carta catastral rural',
    'Carta_Catastral_Urbana' => 'Carta catastral urbana',
    'Plano_Predial_Catastral' => 'Plano predial catastral',
];

$totalDocumentosEntregados = 0;

foreach ($solicitudesProductos as $solicitudProducto) {
    if (estadoProductoTieneDocumentos($solicitudProducto['estado'] ?? '')) {
        $totalDocumentosEntregados++;
    }
}

$totalSolicitudes = count($solicitudesProductos);
$totalDocumentosPendientes = $totalSolicitudes - $totalDocumentosEntregados;
$mostrarColumnaGestion = $esAdministradorProducto || $esCoordinacionProducto || $esEditorProducto;
if ($esVentanillaProducto && !$mostrarColumnaGestion) {
    foreach ($solicitudesProductos as $solicitudProducto) {
        if (strtoupper(trim((string) ($solicitudProducto['estado'] ?? ''))) === ESTADO_PRODUCTO_APROBADO) {
            $mostrarColumnaGestion = true;
            break;
        }
    }
}
?>

<style>
    .productos-consulta {
        color: #111827;
        font-family: 'Poppins', 'Open Sans', sans-serif;
    }

    .productos-consulta .titulo-modulo {
        color: #111827;
        font-size: clamp(1.8rem, 3vw, 2.55rem);
        font-weight: 800;
        letter-spacing: .01em;
    }

    .productos-consulta .btn-solicitar-producto {
        background: #003b64;
        border: 1px solid #003b64;
        border-radius: 8px;
        color: #fff;
        font-weight: 700;
        padding: .7rem 1rem;
    }

    .productos-consulta .btn-solicitar-producto:hover {
        background: #002f55;
        border-color: #002f55;
        color: #fff;
    }

    .productos-consulta .card-productos {
        border: 0;
        border-radius: 15px;
        overflow: hidden;
    }

    .productos-consulta .encabezado-tabla-productos {
        background: #003b64;
        color: #fff;
        font-size: 1.05rem;
        font-weight: 700;
    }

    .productos-consulta .resumen-productos {
        background: #f8fafc;
        border: 1px solid #dce3e8;
        border-radius: 10px;
    }

    .productos-consulta .resumen-icono {
        align-items: center;
        border-radius: 12px;
        display: inline-flex;
        font-size: 1.25rem;
        height: 42px;
        justify-content: center;
        width: 42px;
    }

    .productos-consulta .icono-total {
        background: #e8f1f8;
        color: #0f5699;
    }

    .productos-consulta .icono-entregado {
        background: #e7f7ee;
        color: #198754;
    }

    .productos-consulta .icono-pendiente {
        background: #fff3cd;
        color: #b77900;
    }

    .productos-consulta .controles-tabla {
        font-size: .95rem;
    }

    .productos-consulta .control-registros {
        display: inline-block;
        width: 84px;
    }

    .productos-consulta .control-busqueda {
        display: inline-block;
        max-width: 235px;
        width: 100%;
    }

    .productos-consulta .table thead th {
        background: #fff;
        color: #050505;
        cursor: pointer;
        font-size: .92rem;
        font-weight: 800;
        padding: .75rem .65rem;
        white-space: nowrap;
    }

    .productos-consulta .table tbody td {
        color: #111;
        font-size: .9rem;
        padding: .55rem .65rem;
        vertical-align: middle;
    }

    .productos-consulta .indicador-orden {
        color: #c8cdd2;
        float: right;
        font-size: .7rem;
        margin-left: .5rem;
    }

    .productos-consulta th.orden-activo .indicador-orden {
        color: #6c757d;
    }

    .productos-consulta .table thead th.sin-orden {
        cursor: default;
    }

    .productos-consulta .codigo-producto {
        color: #0f5699;
        font-weight: 700;
        white-space: nowrap;
    }

    .productos-consulta .badge-producto {
        background: #e8f1f8;
        color: #0f5699;
        font-weight: 600;
        white-space: normal;
    }

    .productos-consulta .estado-ancho {
        border-radius: 8px;
        display: block;
        font-size: .76rem;
        font-weight: 800;
        padding: .55rem .65rem;
        text-align: center;
        text-transform: uppercase;
        width: 100%;
    }

    .productos-consulta .estado-entregado,
    .productos-consulta .estado-pagado {
        background: #20c997;
        color: #fff;
    }

    .productos-consulta .estado-pendiente {
        background: #ef4938;
        color: #fff;
    }

    .productos-consulta .btn-accion-tabla {
        border-radius: 5px;
        font-size: .78rem;
        font-weight: 600;
        padding: .42rem .65rem;
    }

    .productos-consulta .btn-documentos {
        background: #003b64;
        border-color: #003b64;
        color: #fff;
    }

    .productos-consulta .btn-documentos:hover {
        background: #002f55;
        border-color: #002f55;
        color: #fff;
    }

    .productos-consulta .pie-tabla {
        font-size: .9rem;
    }

    .productos-consulta .paginacion-productos .btn {
        border: 1px solid #d9e0e5;
        border-radius: 8px;
        color: #003b64;
        min-width: 40px;
    }

    .productos-consulta .paginacion-productos .btn.active {
        background: #003b64;
        border-color: #003b64;
        color: #fff;
    }

    .productos-consulta .paginacion-productos .btn:disabled {
        background: #edf0f2;
        color: #7b8790;
    }
</style>

<div class="container-fluid productos-consulta">
    <div class="d-flex flex-column align-items-start gap-3 mb-4">
        <h1 class="titulo-modulo mb-0">PRODUCTOS CATASTRALES NEIVA</h1>
        <?php if ($esVentanillaProducto || $esAdministradorProducto): ?>
            <a href="index.php?page=prod_catastrales/solicitar_producto" class="btn btn-solicitar-producto">
                <i class="bi bi-file-earmark-plus me-1"></i>Solicitar producto
            </a>
        <?php endif; ?>
    </div>

    <div class="card card-productos shadow-sm mb-4">
        <div class="encabezado-tabla-productos px-3 py-3 d-flex align-items-center justify-content-between">
            <span>Información de solicitudes</span>
            <i class="bi bi-three-dots-vertical"></i>
        </div>

        <div class="card-body p-4">
            <?php if ($errorConsultaProductos !== ''): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <span><?= htmlspecialchars($errorConsultaProductos, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php else: ?>
                <?php if (($_GET['guardado'] ?? '') === '1'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>La solicitud se guardó correctamente.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                <?php elseif (($_GET['resultado'] ?? '') === 'asignado'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>El producto se asignó y fue enviado al editor.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                <?php elseif (($_GET['resultado'] ?? '') === 'aprobado'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>Los documentos fueron aprobados correctamente.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                <?php elseif (($_GET['resultado'] ?? '') === 'devuelto'): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="bi bi-arrow-counterclockwise me-2"></i>El producto fue devuelto al editor para corrección.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                <?php endif; ?>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="resumen-productos h-100 p-3 d-flex align-items-center gap-3">
                            <span class="resumen-icono icono-total"><i class="bi bi-files"></i></span>
                            <div>
                                <div class="small text-muted">Total solicitudes</div>
                                <div class="h5 mb-0"><?= $totalSolicitudes ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="resumen-productos h-100 p-3 d-flex align-items-center gap-3">
                            <span class="resumen-icono icono-entregado"><i class="bi bi-file-earmark-check"></i></span>
                            <div>
                                <div class="small text-muted">Documentos entregados</div>
                                <div class="h5 mb-0 text-success"><?= $totalDocumentosEntregados ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="resumen-productos h-100 p-3 d-flex align-items-center gap-3">
                            <span class="resumen-icono icono-pendiente"><i class="bi bi-hourglass-split"></i></span>
                            <div>
                                <div class="small text-muted">Pendientes por entregar</div>
                                <div class="h5 mb-0 text-warning"><?= $totalDocumentosPendientes ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="controles-tabla d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <label for="cantidadRegistrosProductos" class="mb-0">Mostrar</label>
                        <select class="form-select control-registros" id="cantidadRegistrosProductos">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span>registros</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label for="buscarSolicitudProducto" class="mb-0">Buscar:</label>
                        <input
                            type="search"
                            class="form-control control-busqueda"
                            id="buscarSolicitudProducto"
                            autocomplete="off">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0" id="tablaSolicitudesProductos">
                        <thead>
                            <tr>
                                <th data-columna="0">Fecha de radicación <span class="indicador-orden">↕</span></th>
                                <th data-columna="1">Código producto <span class="indicador-orden">↕</span></th>
                                <th data-columna="2">Nombre solicitante <span class="indicador-orden">↕</span></th>
                                <th data-columna="3">Producto solicitado <span class="indicador-orden">↕</span></th>
                                <th data-columna="4">Estado de entrega <span class="indicador-orden">↕</span></th>
                                <th data-columna="5">Estado de pago <span class="indicador-orden">↕</span></th>
                                <?php if ($mostrarColumnaGestion): ?>
                                    <th class="sin-orden"><?= $esCoordinacionProducto || $esAdministradorProducto ? 'Gestión' : 'Documentos' ?></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($solicitudesProductos as $solicitudProducto): ?>
                                <?php
                                $partesNombre = array_filter([
                                    trim((string) ($solicitudProducto['cert_primer_nombre_interesado'] ?? '')),
                                    trim((string) ($solicitudProducto['cert_segundo_nombre_interesado'] ?? '')),
                                    trim((string) ($solicitudProducto['cert_primer_apellido_interesado'] ?? '')),
                                    trim((string) ($solicitudProducto['cert_segundo_apellido_interesado'] ?? '')),
                                ], static function ($parteNombre) {
                                    return $parteNombre !== '';
                                });

                                $nombreSolicitante = implode(' ', $partesNombre);
                                $tipoProducto = trim((string) ($solicitudProducto['prod_tipo_producto'] ?? ''));
                                $nombreProducto = $nombresProductos[$tipoProducto]
                                    ?? ucfirst(str_replace('_', ' ', $tipoProducto));
                                $soportePago = trim((string) ($solicitudProducto['cert_soporte_pago'] ?? ''));
                                $pagoRealizado = $soportePago !== '';
                                $rutaSoporte = str_replace('\\', '/', $soportePago);
                                $rutaSoporteValida = $rutaSoporte !== ''
                                    && strpos($rutaSoporte, 'soportes_pago/') === 0
                                    && strpos($rutaSoporte, '..') === false
                                    && strpos($rutaSoporte, "\0") === false;
                                $fechaCreacion = (string) ($solicitudProducto['certificado_hora_creacion'] ?? '');
                                $fechaTimestamp = strtotime($fechaCreacion);
                                $fechaMostrada = $fechaTimestamp !== false
                                    ? date('d/m/Y H:i', $fechaTimestamp)
                                    : $fechaCreacion;
                                $codigoProducto = trim((string) ($solicitudProducto['codigo_certificado'] ?? ''));
                                $estadoProducto = trim((string) ($solicitudProducto['estado'] ?? ''));
                                $estadoProductoNormalizado = strtoupper($estadoProducto);
                                $tieneDocumentos = estadoProductoTieneDocumentos($estadoProducto);
                                $urlGestion = 'index.php?page=prod_catastrales/cargar_documentos_producto&codigo=' . rawurlencode($codigoProducto);
                                $urlAsignar = 'index.php?page=prod_catastrales/asignar_producto&codigo=' . rawurlencode($codigoProducto);
                                $urlRevisar = 'index.php?page=prod_catastrales/revisar_producto&codigo=' . rawurlencode($codigoProducto);
                                ?>
                                <tr data-tipo-producto="<?= htmlspecialchars($tipoProducto, ENT_QUOTES, 'UTF-8') ?>">
                                    <td class="text-nowrap" data-orden="<?= htmlspecialchars($fechaCreacion, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($fechaMostrada, ENT_QUOTES, 'UTF-8') ?></td>
                                    <td data-orden="<?= htmlspecialchars($codigoProducto, ENT_QUOTES, 'UTF-8') ?>">
                                        <span class="codigo-producto text-decoration-underline">
                                            <?= htmlspecialchars($codigoProducto !== '' ? $codigoProducto : 'Sin asignar', ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td data-orden="<?= htmlspecialchars($nombreSolicitante, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($nombreSolicitante !== '' ? $nombreSolicitante : 'Sin registrar', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td data-orden="<?= htmlspecialchars($nombreProducto, ENT_QUOTES, 'UTF-8') ?>"><span class="badge badge-producto p-2"><?= htmlspecialchars($nombreProducto, ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td data-orden="<?= $tieneDocumentos ? '1' : '0' ?>">
                                        <?php if ($estadoProductoNormalizado === ESTADO_PRODUCTO_APROBADO): ?>
                                            <span class="estado-ancho estado-entregado">Aprobado</span>
                                        <?php elseif ($estadoProductoNormalizado === ESTADO_PRODUCTO_PENDIENTE_APROBACION): ?>
                                            <span class="estado-ancho estado-pendiente" style="background:#f59e0b">Pendiente de aprobación</span>
                                        <?php elseif ($estadoProductoNormalizado === ESTADO_PRODUCTO_DEVOLUCION): ?>
                                            <span class="estado-ancho estado-pendiente">Devolución</span>
                                        <?php elseif ($estadoProductoNormalizado === ESTADO_PRODUCTO_EN_EDITOR): ?>
                                            <span class="estado-ancho estado-pendiente" style="background:#0f5699">Enviado a editor</span>
                                        <?php elseif ($estadoProductoNormalizado === ESTADO_PRODUCTO_EN_COORDINACION): ?>
                                            <span class="estado-ancho estado-pendiente" style="background:#6f42c1">Enviado a coordinación</span>
                                        <?php else: ?>
                                            <span class="estado-ancho estado-pendiente">Pendiente de pago</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-orden="<?= $pagoRealizado ? '1' : '0' ?>">
                                        <div class="d-flex flex-column align-items-stretch gap-2">
                                            <?php if ($pagoRealizado): ?>
                                                <span class="estado-ancho estado-pagado">Pagado</span>
                                                <?php if ($rutaSoporteValida): ?>
                                                    <a
                                                        href="<?= htmlspecialchars($rutaSoporte, ENT_QUOTES, 'UTF-8') ?>"
                                                        class="btn btn-sm btn-outline-success btn-accion-tabla text-nowrap"
                                                        target="_blank"
                                                        rel="noopener noreferrer">
                                                        <i class="bi bi-file-earmark-pdf me-1"></i>Ver soporte
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="estado-ancho estado-pendiente">Pendiente</span>
                                                <a href="<?= htmlspecialchars($urlGestion . '&seccion=pago#soportePago', ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-outline-danger btn-accion-tabla text-nowrap">
                                                    <i class="bi bi-receipt me-1"></i>Subir soporte de pago
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <?php if ($mostrarColumnaGestion): ?>
                                        <td class="text-center">
                                            <?php if (($esCoordinacionProducto || $esAdministradorProducto) && $estadoProductoNormalizado === ESTADO_PRODUCTO_EN_COORDINACION): ?>
                                                <a href="<?= htmlspecialchars($urlAsignar, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-primary btn-accion-tabla text-nowrap">
                                                    <i class="bi bi-person-plus me-1"></i>Asignar
                                                </a>
                                            <?php elseif (($esCoordinacionProducto || $esAdministradorProducto) && $estadoProductoNormalizado === ESTADO_PRODUCTO_PENDIENTE_APROBACION): ?>
                                                <a href="<?= htmlspecialchars($urlRevisar, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-documentos btn-accion-tabla text-nowrap">
                                                    <i class="bi bi-clipboard-check me-1"></i>Gestionar documentos
                                                </a>
                                            <?php elseif (($esEditorProducto || $esAdministradorProducto) && in_array($estadoProductoNormalizado, [ESTADO_PRODUCTO_EN_EDITOR, ESTADO_PRODUCTO_DEVOLUCION], true)): ?>
                                                <a href="<?= htmlspecialchars($urlGestion . '&seccion=documentos', ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-documentos btn-accion-tabla text-nowrap">
                                                    <i class="bi bi-cloud-arrow-up me-1"></i>Gestionar documentos
                                                </a>
                                            <?php elseif ($estadoProductoNormalizado === ESTADO_PRODUCTO_APROBADO): ?>
                                                <a href="<?= htmlspecialchars($urlGestion . '&seccion=documentos', ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-outline-success btn-accion-tabla text-nowrap">
                                                    <i class="bi bi-download me-1"></i>Descargar
                                                </a>
                                            <?php elseif ($estadoProductoNormalizado === ESTADO_PRODUCTO_EN_EDITOR): ?>
                                                <span class="small text-muted"><?= htmlspecialchars((string) ($solicitudProducto['editor_nombre'] ?? 'Asignado al editor'), ENT_QUOTES, 'UTF-8') ?></span>
                                            <?php elseif ($estadoProductoNormalizado === ESTADO_PRODUCTO_DEVOLUCION): ?>
                                                <span class="small text-danger">Devuelto al editor</span>
                                            <?php elseif ($esEditorProducto && $estadoProductoNormalizado === ESTADO_PRODUCTO_PENDIENTE_APROBACION): ?>
                                                <span class="small text-muted">En revisión</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                            <tr id="sinResultadosProductos" class="<?= $totalSolicitudes > 0 ? 'd-none' : '' ?>">
                                <td colspan="<?= $mostrarColumnaGestion ? 7 : 6 ?>" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    No se encontraron solicitudes de productos catastrales.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="pie-tabla d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-2">
                    <span id="informacionRegistrosProductos">Mostrando 0 a 0 de 0 registros</span>
                    <div class="paginacion-productos d-flex flex-wrap gap-1" id="paginacionProductos"></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($errorConsultaProductos === ''): ?>
    <script>
        (function () {
            const campoBusqueda = document.getElementById('buscarSolicitudProducto');
            const selectorCantidad = document.getElementById('cantidadRegistrosProductos');
            const tabla = document.getElementById('tablaSolicitudesProductos');
            const informacion = document.getElementById('informacionRegistrosProductos');
            const paginacion = document.getElementById('paginacionProductos');
            const filaSinResultados = document.getElementById('sinResultadosProductos');

            if (!campoBusqueda || !selectorCantidad || !tabla || !informacion || !paginacion || !filaSinResultados) {
                return;
            }

            const cuerpoTabla = tabla.querySelector('tbody');
            const filas = Array.from(tabla.querySelectorAll('tbody tr[data-tipo-producto]'));
            const encabezados = Array.from(tabla.querySelectorAll('thead th[data-columna]'));
            let paginaActual = 1;
            let columnaOrden = null;
            let direccionOrden = 'asc';

            function normalizarTexto(texto) {
                return texto
                    .toLocaleLowerCase('es')
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '');
            }

            function obtenerFilasOrdenadas() {
                const filasOrdenadas = filas.slice();

                if (columnaOrden === null) {
                    return filasOrdenadas;
                }

                filasOrdenadas.sort(function (filaA, filaB) {
                    const celdaA = filaA.cells[columnaOrden];
                    const celdaB = filaB.cells[columnaOrden];
                    const valorA = (celdaA.dataset.orden || celdaA.textContent).trim();
                    const valorB = (celdaB.dataset.orden || celdaB.textContent).trim();
                    const comparacion = valorA.localeCompare(valorB, 'es', {
                        numeric: true,
                        sensitivity: 'base'
                    });

                    return direccionOrden === 'asc' ? comparacion : -comparacion;
                });

                return filasOrdenadas;
            }

            function crearBotonPaginacion(texto, pagina, deshabilitado, activo) {
                const boton = document.createElement('button');
                boton.type = 'button';
                boton.className = 'btn btn-sm' + (activo ? ' active' : '');
                boton.textContent = texto;
                boton.disabled = deshabilitado;

                if (!deshabilitado) {
                    boton.addEventListener('click', function () {
                        paginaActual = pagina;
                        renderizarTabla();
                    });
                }

                return boton;
            }

            function renderizarPaginacion(totalPaginas, totalRegistros) {
                paginacion.innerHTML = '';
                if (totalRegistros === 0) {
                    return;
                }

                paginacion.appendChild(crearBotonPaginacion(
                    'Anterior',
                    paginaActual - 1,
                    paginaActual === 1,
                    false
                ));

                let paginaInicial = Math.max(1, paginaActual - 2);
                let paginaFinal = Math.min(totalPaginas, paginaInicial + 4);
                paginaInicial = Math.max(1, paginaFinal - 4);

                for (let pagina = paginaInicial; pagina <= paginaFinal; pagina++) {
                    paginacion.appendChild(crearBotonPaginacion(
                        String(pagina),
                        pagina,
                        false,
                        pagina === paginaActual
                    ));
                }

                paginacion.appendChild(crearBotonPaginacion(
                    'Siguiente',
                    paginaActual + 1,
                    paginaActual === totalPaginas,
                    false
                ));
            }

            function renderizarTabla() {
                const termino = normalizarTexto(campoBusqueda.value.trim());
                const cantidadPorPagina = Number(selectorCantidad.value) || 10;
                const filasOrdenadas = obtenerFilasOrdenadas();
                const filasFiltradas = filasOrdenadas.filter(function (fila) {
                    return termino === '' || normalizarTexto(fila.textContent).includes(termino);
                });

                filasOrdenadas.forEach(function (fila) {
                    cuerpoTabla.insertBefore(fila, filaSinResultados);
                    fila.classList.add('d-none');
                });

                const totalRegistros = filasFiltradas.length;
                const totalPaginas = Math.max(1, Math.ceil(totalRegistros / cantidadPorPagina));
                paginaActual = Math.min(paginaActual, totalPaginas);
                const inicio = (paginaActual - 1) * cantidadPorPagina;
                const fin = Math.min(inicio + cantidadPorPagina, totalRegistros);

                filasFiltradas.slice(inicio, fin).forEach(function (fila) {
                    fila.classList.remove('d-none');
                });

                filaSinResultados.classList.toggle('d-none', totalRegistros !== 0);
                informacion.textContent = totalRegistros === 0
                    ? 'Mostrando 0 a 0 de 0 registros'
                    : 'Mostrando ' + (inicio + 1) + ' a ' + fin + ' de ' + totalRegistros + ' registros';

                renderizarPaginacion(totalPaginas, totalRegistros);
            }

            campoBusqueda.addEventListener('input', function () {
                paginaActual = 1;
                renderizarTabla();
            });

            selectorCantidad.addEventListener('change', function () {
                paginaActual = 1;
                renderizarTabla();
            });

            encabezados.forEach(function (encabezado) {
                encabezado.addEventListener('click', function () {
                    const nuevaColumna = Number(encabezado.dataset.columna);
                    if (columnaOrden === nuevaColumna) {
                        direccionOrden = direccionOrden === 'asc' ? 'desc' : 'asc';
                    } else {
                        columnaOrden = nuevaColumna;
                        direccionOrden = 'asc';
                    }

                    encabezados.forEach(function (otroEncabezado) {
                        otroEncabezado.classList.remove('orden-activo');
                        otroEncabezado.querySelector('.indicador-orden').textContent = '↕';
                    });
                    encabezado.classList.add('orden-activo');
                    encabezado.querySelector('.indicador-orden').textContent = direccionOrden === 'asc' ? '▲' : '▼';
                    paginaActual = 1;
                    renderizarTabla();
                });
            });

            renderizarTabla();
        }());
    </script>
<?php endif; ?>
