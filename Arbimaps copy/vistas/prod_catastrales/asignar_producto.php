<?php
require_once __DIR__ . '/funciones_productos.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$codigoProducto = trim((string) ($_GET['codigo'] ?? ''));
$codigoSeguro = normalizarCodigoProducto($codigoProducto);
$puedeAsignar = usuarioProductoTieneRol(['coordinacion_tecnica', 'administrador']);
$producto = null;
$editores = [];
$errorAsignacion = '';

if (!$puedeAsignar) {
    $errorAsignacion = 'No tiene permisos para asignar productos catastrales.';
} elseif ($codigoSeguro === '' || $codigoSeguro !== $codigoProducto) {
    $errorAsignacion = 'El código del producto no es válido.';
} elseif (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    $errorAsignacion = 'No fue posible conectar con la base de datos.';
} else {
    asegurarFlujoProductosCatastrales($mysqli);
    $stmtProducto = $mysqli->prepare(
        "SELECT codigo_certificado, prod_tipo_producto, estado
         FROM certificado_catastral
         WHERE codigo_certificado = ?
           AND prod_tipo_producto IS NOT NULL
           AND TRIM(prod_tipo_producto) <> ''
         LIMIT 1"
    );
    if ($stmtProducto) {
        $stmtProducto->bind_param('s', $codigoProducto);
        $stmtProducto->execute();
        $resultadoProducto = $stmtProducto->get_result();
        $producto = $resultadoProducto ? $resultadoProducto->fetch_assoc() : null;
        $stmtProducto->close();
    }

    if (!$producto) {
        $errorAsignacion = 'No se encontró la solicitud.';
    } elseif (strtoupper(trim((string) $producto['estado'])) !== ESTADO_PRODUCTO_EN_COORDINACION) {
        $errorAsignacion = 'La solicitud no está disponible para asignación.';
    } else {
        $sqlEditores = "SELECT cedula_usuario, nombre_usuario, apellido_usuario
                       FROM usuarios_cons
                       WHERE rol_usuario = 'editor' OR rol_usuario_dos = 'editor'
                       ORDER BY nombre_usuario, apellido_usuario";
        $resultadoEditores = $mysqli->query($sqlEditores);
        if ($resultadoEditores) {
            while ($editor = $resultadoEditores->fetch_assoc()) {
                $editores[] = $editor;
            }
            $resultadoEditores->free();
        } else {
            $errorAsignacion = 'No fue posible consultar los usuarios editores.';
        }
    }
}

if (empty($_SESSION['csrf_asignar_producto'])) {
    $_SESSION['csrf_asignar_producto'] = bin2hex(random_bytes(32));
}
?>

<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-header text-white py-3" style="background:#003b64">
            <h1 class="h5 mb-0"><i class="bi bi-person-plus me-2"></i>Asignar producto a editor</h1>
        </div>
        <div class="card-body p-4">
            <?php if ($errorAsignacion !== ''): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($errorAsignacion, ENT_QUOTES, 'UTF-8') ?></div>
            <?php else: ?>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="small text-muted">Código</div>
                        <div class="fw-bold text-primary"><?= htmlspecialchars($codigoProducto, ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Producto</div>
                        <div class="fw-semibold"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', (string) $producto['prod_tipo_producto'])), ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                </div>

                <?php if (count($editores) === 0): ?>
                    <div class="alert alert-warning">No hay usuarios con rol Editor disponibles.</div>
                <?php else: ?>
                    <form action="vistas/prod_catastrales/acciones/asignar_producto.php" method="post">
                        <input type="hidden" name="codigo_producto" value="<?= htmlspecialchars($codigoProducto, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_asignar_producto'], ENT_QUOTES, 'UTF-8') ?>">
                        <label for="editor_cedula" class="form-label fw-semibold">Seleccionar editor</label>
                        <select class="form-select" id="editor_cedula" name="editor_cedula" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($editores as $editor): ?>
                                <?php $nombreEditor = trim($editor['nombre_usuario'] . ' ' . $editor['apellido_usuario']); ?>
                                <option value="<?= htmlspecialchars((string) $editor['cedula_usuario'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($nombreEditor . ' - ' . $editor['cedula_usuario'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary mt-3">
                            <i class="bi bi-send me-1"></i>Asignar y enviar al editor
                        </button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>

            <a href="index.php?page=prod_catastrales/consultar_producto" class="btn btn-outline-secondary mt-3">
                <i class="bi bi-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>
</div>
