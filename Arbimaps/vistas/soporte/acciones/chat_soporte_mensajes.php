<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('GET');
neiva_require_permission('menu.soporte', $PERMISOS);

$id_usuario_sesion = $_SESSION['id_usuario'] ?? null;

if (!$id_usuario_sesion) {
    exit;
}

$codigo_error = isset($_GET['codigo']) ? trim($_GET['codigo']) : null;
if (!$codigo_error) {
    exit;
}
$sqlMensajes = "SELECT 
                    c.id_mensaje,
                    c.codigo_error,
                    c.id_usuario,
                    c.mensaje,
                    c.fecha_envio,
                    c.tipo_remitente,
                    u.nombre_usuario,
                    u.apellido_usuario
                FROM soporte_chat c
                INNER JOIN usuarios_cons u ON u.id_usuario = c.id_usuario
                WHERE c.codigo_error = ?
                ORDER BY c.fecha_envio ASC";

$stmtMensajes = $mysqli->prepare($sqlMensajes);
if ($stmtMensajes) {    
    $stmtMensajes->bind_param('s', $codigo_error);
    $stmtMensajes->execute();
    $resMensajes = $stmtMensajes->get_result();

    if ($resMensajes && $resMensajes->num_rows > 0) {
        while ($m = $resMensajes->fetch_assoc()) {
            $esPropio = ((int)$m['id_usuario'] === (int)$id_usuario_sesion);
            $claseBurbuja = $esPropio ? 'mensaje-propio' : 'mensaje-otro';
            $nombreRemitente = trim(($m['nombre_usuario'] ?? '') . ' ' . ($m['apellido_usuario'] ?? ''));
            ?>
            <div class="d-flex <?php echo $esPropio ? 'justify-content-end' : 'justify-content-start'; ?>">
                <div class="mensaje-burbuja <?php echo $claseBurbuja; ?>">
                    <div style="font-size:0.8rem; font-weight:600; margin-bottom:0.15rem;">
                        <?php echo htmlspecialchars($nombreRemitente ?: $m['tipo_remitente'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div>
                        <?php echo nl2br(htmlspecialchars($m['mensaje'], ENT_QUOTES, 'UTF-8')); ?>
                    </div>
                    <div class="mensaje-meta">
                        <?php echo htmlspecialchars($m['fecha_envio'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        ?>
        <p class="text-muted mb-0" style="font-size:0.9rem;">
            Aún no hay mensajes en este chat. Escribe el primero.
        </p>
        <?php
    }
    $stmtMensajes->close();
}
