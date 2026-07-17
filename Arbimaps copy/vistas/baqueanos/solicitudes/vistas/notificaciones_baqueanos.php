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

$rolesPermitidos = array("administrador", "director_catastro", "gerencia", "soporte", "social", "lider_reconocimiento");

if (!in_array($rolUsuario, $rolesPermitidos) && !in_array($rolUsuarioDos, $rolesPermitidos)) {
    header("Location: ../../acceso_denegado.php");
    exit();
}

$idUsuario = (int)$_SESSION['id_usuario'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'marcar_una') {
        $idNoti = (int)($_POST['id_notificacion'] ?? 0);
        if ($idNoti > 0) {
            $sql = "UPDATE notificaciones_baqueanos
                    SET leida = 1
                    WHERE id_notificacion = ?
                        AND id_usuario = ?";
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("ii", $idNoti, $idUsuario);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    if ($accion === 'marcar_todas') {
        $sql = "UPDATE notificaciones_baqueanos
                SET leida = 1
                WHERE id_usuario = ?
                    AND leida = 0";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("i", $idUsuario);
            $stmt->execute();
            $stmt->close();
        }
    }

    $redirect = "index.php?page=baqueanos/solicitudes/vistas/notificaciones_baqueanos&id=3&notif=1&src=bq";
    echo "<script>window.location.href=" . json_encode($redirect) . ";</script>";
    exit;
}

$total      = 0;
$noLeidas   = 0;

$sqlTotal = "SELECT COUNT(*) AS total
                FROM notificaciones_baqueanos
                WHERE id_usuario = ?";
if ($stmt = $mysqli->prepare($sqlTotal)) {
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $total = (int)($r['total'] ?? 0);
    $stmt->close();
}

$sqlNoLeidas = "SELECT COUNT(*) AS c
                FROM notificaciones_baqueanos
                WHERE id_usuario = ?
                    AND leida = 0";
if ($stmt = $mysqli->prepare($sqlNoLeidas)) {
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $noLeidas = (int)($r['c'] ?? 0);
    $stmt->close();
}

$leidas             = max(0, $total - $noLeidas);
$porcentajeLeidas   = ($total > 0) ? round(($leidas / $total) * 100) : 0;

$notis = [];
$sqlList = "SELECT 
                id_notificacion, 
                solicitud_id, 
                tipo, 
                mensaje, 
                leida, 
                fecha_creacion
            FROM notificaciones_baqueanos
            WHERE id_usuario = ?
            ORDER BY fecha_creacion DESC
            LIMIT 25";
if ($stmt = $mysqli->prepare($sqlList)) {
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($res && $row = $res->fetch_assoc()) $notis[] = $row;
    $stmt->close();
}

$nombre = trim(($_SESSION['nombre_usuario'] ?? '') . ' ' . ($_SESSION['apellido_usuario'] ?? ''));
?>

<style>
    :root {
        --bg: #f5f7fb;
        --card: #ffffff;
        --text: #0f172a;
        --muted: #6b7280;
        --primary: #2563eb;
        --primary2: #0b5aa0;
        --line: rgba(15, 23, 42, .08);
        --shadow: 0 14px 30px rgba(15, 23, 42, .08);
        --radius: 18px;
    }

    .dashboard-shell {
        background: var(--bg);
        border-radius: 18px;
        padding: 14px;
    }

    .dash-topbar {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 12px 14px;
    }

    .dash-title {
        font-weight: 800;
        color: var(--text);
        margin: 0;
        letter-spacing: .2px;
    }

    .dash-subtitle {
        margin: 0;
        color: var(--muted);
        font-size: .9rem;
    }

    .search-pill {
        background: #f2f4f8;
        border: 1px solid rgba(15, 23, 42, .06);
        border-radius: 999px;
        padding: 10px 12px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .search-pill input {
        border: 0;
        outline: 0;
        background: transparent;
        width: 100%;
        font-size: .92rem;
    }

    .icon-btn {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        border: 1px solid rgba(15, 23, 42, .08);
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .icon-btn:hover {
        background: #f6f8ff;
        border-color: rgba(37, 99, 235, .25);
    }

    .kpi-card {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 14px 14px;
        height: 100%;
    }

    .kpi-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: rgba(37, 99, 235, .10);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .kpi-label {
        color: var(--muted);
        font-size: .85rem;
        margin: 0;
    }

    .kpi-value {
        color: var(--text);
        font-size: 1.35rem;
        font-weight: 800;
        margin: 0;
    }

    .kpi-foot {
        color: var(--muted);
        font-size: .8rem;
    }

    .panel-card {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .panel-head {
        padding: 14px 16px;
        border-bottom: 1px solid rgba(15, 23, 42, .06);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .panel-head h6 {
        margin: 0;
        font-weight: 800;
        color: var(--text);
    }

    .panel-body {
        padding: 14px 16px;
    }

    .badge-soft {
        background: rgba(37, 99, 235, .10);
        color: var(--primary);
        border: 1px solid rgba(37, 99, 235, .20);
        padding: 6px 10px;
        border-radius: 999px;
        font-weight: 700;
        font-size: .78rem;
    }

    .noti-row {
        display: flex;
        gap: 12px;
        padding: 12px 12px;
        border-radius: 14px;
        border: 1px solid rgba(15, 23, 42, .06);
        background: #fff;
        transition: background .15s ease, transform .08s ease, border-color .15s ease;
    }

    .noti-row:hover {
        background: #f7f9ff;
        border-color: rgba(37, 99, 235, .18);
        transform: translateY(-1px);
    }

    .noti-row.unread {
        background: rgba(37, 99, 235, .06);
        border-color: rgba(37, 99, 235, .18);
    }

    .noti-dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: #cbd5e1;
        margin-top: 6px;
    }

    .noti-row.unread .noti-dot {
        background: #f59e0b;
    }

    .noti-text {
        color: var(--text);
        font-weight: 650;
        font-size: .92rem;
    }

    .noti-meta {
        color: var(--muted);
        font-size: .78rem;
        margin-top: 3px;
    }

    .btn-ghost {
        border: 1px solid rgba(15, 23, 42, .10);
        background: #fff;
        border-radius: 12px;
        padding: 8px 10px;
        font-weight: 700;
        font-size: .82rem;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-ghost:hover {
        background: #f7f9ff;
        border-color: rgba(37, 99, 235, .25);
        color: var(--primary);
    }

    .btn-primary-pro {
        background: linear-gradient(135deg, #002F55 0%, #0b5aa0 100%);
        border: 0;
        border-radius: 12px;
        font-weight: 800;
        padding: 8px 12px;
    }

    .btn-primary-pro:hover {
        filter: brightness(1.02);
    }


    .noti-scroll {
        max-height: 65vh;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 6px;
    }

    .noti-scroll {
        -webkit-overflow-scrolling: touch;
    }

    .noti-scroll::-webkit-scrollbar {
        width: 8px;
    }

    .noti-scroll::-webkit-scrollbar-thumb {
        background: rgba(15, 23, 42, .18);
        border-radius: 999px;
    }

    .noti-scroll::-webkit-scrollbar-track {
        background: transparent;
    }
</style>

<div class="container-fluid dashboard-shell">
    <div class="dash-topbar mb-3">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-lg-4">
                <h5 class="dash-title">Notificaciones Baqueanos</h5>
                <p class="dash-subtitle">Bienvenido, <?php echo htmlspecialchars($nombre ?: 'Usuario', ENT_QUOTES, 'UTF-8'); ?></p>
            </div>

            <div class="col-12 col-lg-5">
                <div class="search-pill">
                    <i class="bi bi-search"></i>
                    <input id="filtroNoti" type="text" placeholder="Buscar notificaciones (tipo, mensaje, solicitud)..." autocomplete="off">
                </div>
            </div>

            <div class="col-12 col-lg-3 d-flex justify-content-lg-end gap-2">
                <button class="icon-btn" type="button" title="Refrescar" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>

                <form method="POST" class="m-0">
                    <input type="hidden" name="accion" value="marcar_todas">
                    <button class="btn btn-primary-pro text-white" type="submit" <?php echo ($noLeidas === 0) ? 'disabled' : ''; ?>>
                        <i class="bi bi-check2-all me-1"></i> Marcar todas
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-12 col-md-4">
            <div class="kpi-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="kpi-icon"><i class="bi bi-bell"></i></div>
                    <div class="flex-grow-1">
                        <p class="kpi-label">Total notificaciones</p>
                        <p class="kpi-value"><?php echo (int)$total; ?></p>
                        <div class="kpi-foot">Histórico de tu usuario</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="kpi-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="kpi-icon"><i class="bi bi-exclamation-circle"></i></div>
                    <div class="flex-grow-1">
                        <p class="kpi-label">No leídas</p>
                        <p class="kpi-value"><?php echo (int)$noLeidas; ?></p>
                        <div class="kpi-foot">Pendientes por revisar</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="kpi-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="kpi-icon"><i class="bi bi-check-circle"></i></div>
                    <div class="flex-grow-1">
                        <p class="kpi-label">Leídas</p>
                        <p class="kpi-value"><?php echo (int)$leidas; ?> <span style="font-size:.95rem;color:var(--muted);font-weight:700;">(<?php echo (int)$porcentajeLeidas; ?>%)</span></p>
                        <div class="kpi-foot">Control de lectura</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="panel-card">
                <div class="panel-head">
                    <h6>Últimas notificaciones</h6>
                    <span class="badge-soft"><?php echo (int)$noLeidas; ?> nuevas</span>
                </div>

                <div class="panel-body">
                    <?php if (empty($notis)): ?>
                        <div class="text-center py-4" style="color:var(--muted);">
                            <i class="bi bi-bell-slash" style="font-size:2rem;"></i>
                            <div class="mt-2 fw-bold" style="color:var(--text);">No tienes notificaciones</div>
                            <div class="small">Cuando se genere una, aparecerá aquí.</div>
                        </div>
                    <?php else: ?>
                        <div class="noti-scroll">
                            <div class="d-flex flex-column gap-2" id="contenedorNotis">
                                <?php foreach ($notis as $n): ?>
                                    <?php
                                    $esLeida = (int)$n['leida'] === 1;
                                    $urlSolicitud = "index.php?page=baqueanos/solicitudes/vistas/informacion_mi_solicitud&id=" . (int)$n['solicitud_id'];
                                    ?>
                                    <div class="noti-row <?php echo $esLeida ? '' : 'unread'; ?>"
                                        data-text="<?php echo htmlspecialchars(strtolower(($n['tipo'] ?? '') . ' ' . ($n['mensaje'] ?? '') . ' ' . ($n['solicitud_id'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>">
                                        <div class="noti-dot"></div>

                                        <div class="flex-grow-1">
                                            <div class="d-flex flex-wrap justify-content-between gap-2">
                                                <div>
                                                    <div class="noti-text">
                                                        <?php echo htmlspecialchars((string)($n['tipo'] ?? 'NOTIFICACIÓN'), ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                    <div style="color:var(--text);font-size:.9rem;">
                                                        <?php echo htmlspecialchars((string)$n['mensaje'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                    <div class="noti-meta">
                                                        <i class="bi bi-clock me-1"></i>
                                                        <?php echo date('d/m/Y H:i', strtotime($n['fecha_creacion'])); ?>
                                                        <span class="mx-2">·</span>
                                                        <i class="bi bi-hash me-1"></i>
                                                        Solicitud: <?php echo (int)$n['solicitud_id']; ?>
                                                    </div>
                                                </div>

                                                <div class="d-flex gap-2">
                                                    <a class="btn btn-ghost flex-grow-1" href="<?php echo $urlSolicitud; ?>">
                                                        <i class="bi bi-box-arrow-up-right me-1"></i> Ver
                                                    </a>

                                                    <?php if (!$esLeida): ?>
                                                        <form method="POST" class="m-0 flex-grow-1">
                                                            <input type="hidden" name="accion" value="marcar_una">
                                                            <input type="hidden" name="id_notificacion" value="<?php echo (int)$n['id_notificacion']; ?>">
                                                            <button class="btn btn-ghost w-100" type="submit">
                                                                <i class="bi bi-check2 me-1"></i> Leída
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="btn btn-ghost flex-grow-1 disabled">
                                                            <i class="bi bi-check-circle me-1"></i> Leída
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="panel-card mb-3">
                <div class="panel-head">
                    <h6>Resumen rápido</h6>
                    <span class="badge-soft">Hoy</span>
                </div>
                <div class="panel-body">
                    <div class="mb-2" style="color:var(--muted); font-size:.85rem;">Estado de lectura</div>
                    <div class="progress" style="height: 10px; border-radius:999px; background:#eef2ff;">
                        <div class="progress-bar" role="progressbar"
                            style="width: <?php echo (int)$porcentajeLeidas; ?>%; background: linear-gradient(135deg, #002F55 0%, #0b5aa0 100%);"
                            aria-valuenow="<?php echo (int)$porcentajeLeidas; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2" style="font-size:.82rem; color:var(--muted);">
                        <span><?php echo (int)$porcentajeLeidas; ?>% leídas</span>
                        <span><?php echo (int)$noLeidas; ?> pendientes</span>
                    </div>
                    <hr style="border-color:rgba(15,23,42,.08);">
                </div>
            </div>
            <div class="panel-card">
                <div class="panel-head">
                    <h6>Tips</h6>
                    <span class="badge-soft">Baqueanos</span>
                </div>
                <div class="panel-body" style="color:var(--muted); font-size:.9rem;">
                    <ul class="mb-0 ps-3">
                        <li>Usa el buscador para filtrar por mensaje o solicitud.</li>
                        <li>Marca como leída para limpiar el contador.</li>
                        <li>En “Ver” te lleva al detalle de la solicitud.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('filtroNoti');
        const cont = document.getElementById('contenedorNotis');
        if (!input || !cont) return;

        input.addEventListener('input', function() {
            const q = (input.value || '').toLowerCase().trim();
            const items = cont.querySelectorAll('.noti-row');

            items.forEach(it => {
                const text = it.getAttribute('data-text') || '';
                it.style.display = (q === '' || text.includes(q)) ? '' : 'none';
            });
        });
    });
</script>