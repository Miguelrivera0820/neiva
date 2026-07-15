<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 2) . '/config/permisos.php';
require_once dirname(__DIR__, 3) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('GET');
neiva_require_permission('soporte.ticket', $PERMISOS);
$id_usuario_sesion = (int) ($_SESSION['id_usuario'] ?? 0);
$rol_usuario = '';
$correo_usuario = '';

if ($id_usuario_sesion > 0) {
    $sqlUsuario = "SELECT rol_usuario, correo_usuario FROM usuarios_cons WHERE id_usuario = ? LIMIT 1";
    $stmtUsuario = $mysqli->prepare($sqlUsuario);
    if ($stmtUsuario) {
        $stmtUsuario->bind_param('i', $id_usuario_sesion);
        $stmtUsuario->execute();
        $resUsuario = $stmtUsuario->get_result();
        if ($resUsuario && $resUsuario->num_rows > 0) {
            $usuarioActual = $resUsuario->fetch_assoc();
            $rol_usuario = strtolower(trim((string) ($usuarioActual['rol_usuario'] ?? '')));
            $correo_usuario = (string) ($usuarioActual['correo_usuario'] ?? '');
        }
        $stmtUsuario->close();
    }
}
$codigo = $_GET['codigo'] ?? null;
if (!$codigo) {
    echo "Código no válido";
    exit;
}
$sql = "SELECT 
            codigo_error,
            asunto,
            descripcion,
            prioridad,
            tipo_error,
            estado_error,
            fecha_hora_creacion,
            correo_solicitante
        FROM solicitud_soporte
        WHERE codigo_error = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('s', $codigo);
$stmt->execute();
$result = $stmt->get_result();
$ticket = $result->fetch_assoc();
$stmt->close();

if (!$ticket) {
    echo "Ticket no encontrado";
    exit;
}
$es_soporte = str_contains($rol_usuario, 'soporte');
if (!$es_soporte && $correo_usuario !== (string) ($ticket['correo_solicitante'] ?? '')) {
    neiva_abort(403, 'No tiene permisos para imprimir este ticket.');
}
$fecha_impresion = date('Y-m-d H:i:s');
$logo_url = neiva_app_url('Arbimaps/assets/img/logo_final_arbitrium.png');
?>
<title>Ticket <?php echo htmlspecialchars($ticket['codigo_error']); ?></title>
<style>
    :root {
        --ticket-bg-img: url("<?php echo htmlspecialchars($logo_url, ENT_QUOTES, 'UTF-8'); ?>");
        --bg-size: 380px;
    }

    body {
        --page-bg: #f3f4f6;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
        background: var(--page-bg);
        margin: 0;
        padding: 0;
    }

    .ticket-wrapper {
        position: relative;
        max-width: 520px;
        margin: 24px auto;
        padding: 22px 26px;
        font-size: 13.5px;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.18);
        overflow: hidden;
        isolation: isolate;
    }

    .ticket-bg {
        position: absolute;
        inset: 0;
        background-image: var(--ticket-bg-img);
        background-repeat: no-repeat;
        background-position: center center;
        background-size: var(--bg-size) auto;
        opacity: 0.10;
        filter: grayscale(1);
        z-index: 0;
        pointer-events: none;
    }

    .ticket-notch {
        position: absolute;
        top: 50%;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: var(--page-bg);
        border: 1px solid #e5e7eb;
        transform: translateY(-50%);
        z-index: 3;
        pointer-events: none;
    }

    .ticket-notch.left {
        left: -9px;
    }

    .ticket-notch.right {
        right: -9px;
    }

    .ticket-content {
        position: relative;
        z-index: 2;
    }

    .ticket-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 10px;
        margin-bottom: 10px;
        border-bottom: 1px dashed #cbd5e1;
    }

    .ticket-logo img {
        height: 44px;
        object-fit: contain;
    }

    .ticket-title h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        letter-spacing: 0.2px;
    }

    .ticket-title small {
        color: #6b7280;
        font-size: 12px;
        font-weight: 600;
    }

    .ticket-body {
        margin-top: 6px;
        padding: 10px 0 4px;
    }

    .ticket-row {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 6px;
        padding: 4px 0;
    }

    .ticket-row .label {
        font-weight: 700;
        color: #374151;
        min-width: 90px;
    }

    .ticket-row .value {
        color: #111827;
        text-align: right;
        max-width: 240px;
        word-break: break-word;
    }

    .ticket-tear {
        border: none;
        border-top: 1px dashed #cbd5e1;
        margin: 10px 0;
    }

    .ticket-block {
        margin-top: 10px;
        padding: 10px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.72);
        backdrop-filter: blur(2px);
    }

    .ticket-block .label {
        display: block;
        font-weight: 800;
        margin-bottom: 6px;
        color: #374151;
    }

    .ticket-block .descripcion {
        margin: 0;
        white-space: pre-wrap;
        color: #111827;
        line-height: 1.35;
    }

    .ticket-footer {
        border-top: 1px dashed #cbd5e1;
        margin-top: 12px;
        padding-top: 10px;
        text-align: center;
        color: #6b7280;
        font-size: 10px;
    }

    .text-upper {
        text-transform: uppercase;
    }

    @media print {
        @page {
            margin: 0;
        }

        html,
        body {
            width: 100%;
            height: 100%;
            background: #ffffff !important;
            margin: 0 !important;
            padding: 0 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body * {
            visibility: hidden;
        }

        #ticket-soporte,
        #ticket-soporte * {
            visibility: visible;
        }

        #ticket-soporte {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ticket-wrapper {
            box-shadow: none;
            margin: 0;
        }

        .ticket-bg {
            opacity: 0.08;
        }
    }
</style>
<div id="ticket-soporte">
    <div class="ticket-wrapper">
        <div class="ticket-bg"></div>
        <span class="ticket-notch left"></span>
        <span class="ticket-notch right"></span>
        <div class="ticket-content">
            <div class="ticket-header">
                <div class="ticket-logo">
                    <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="Alcaldía de Neiva" />
                </div>
                <div class="ticket-title">
                    <h2>Ticket de Soporte</h2>
                    <small>ARBIMAPS</small>
                </div>
            </div>
            <div class="ticket-body">
                <div class="ticket-row">
                    <span class="label">Código:</span>
                    <span class="value"><?php echo htmlspecialchars($ticket['codigo_error']); ?></span>
                </div>
                <div class="ticket-row">
                    <span class="label">Fecha:</span>
                    <span class="value"><?php echo htmlspecialchars($ticket['fecha_hora_creacion']); ?></span>
                </div>
                <div class="ticket-row">
                    <span class="label">Estado:</span>
                    <span class="value"><?php echo htmlspecialchars($ticket['estado_error']); ?></span>
                </div>
                <div class="ticket-row">
                    <span class="label">Prioridad:</span>
                    <span class="value text-upper"><?php echo htmlspecialchars($ticket['prioridad']); ?></span>
                </div>
                <div class="ticket-row">
                    <span class="label">Tipo:</span>
                    <span class="value"><?php echo htmlspecialchars($ticket['tipo_error']); ?></span>
                </div>
                <hr class="ticket-tear">
                <div class="ticket-row">
                    <span class="label">Asunto:</span>
                    <span class="value"><?php echo htmlspecialchars($ticket['asunto']); ?></span>
                </div>
                <div class="ticket-block">
                    <span class="label">Descripción:</span>
                    <p class="descripcion"><?php echo nl2br(htmlspecialchars($ticket['descripcion'])); ?></p>
                </div>
            </div>
            <div class="ticket-footer">
                <small>Generado por Arbimaps - Soporte Técnico</small><br>
                <small>Este ticket es solo informativo.</small>
            </div>
        </div>
    </div>
</div>
