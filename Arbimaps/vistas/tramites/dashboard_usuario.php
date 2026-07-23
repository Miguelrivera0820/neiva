<?php
require_once __DIR__ . '/dashboard_lib.php';

$usuario = trim($_GET['usuario'] ?? '');
$rol = trim($_GET['rol'] ?? '');
$rolSesion = $_SESSION['rol_usuario'] ?? '';
if ($rolSesion === 'director_proyectos') {
    $rolSesion = 'director_catastro';
}
$data = td_dashboard_data($mysqli, array_merge(td_dashboard_filters(), ['usuario' => $usuario, 'rol' => $rol]));
$items = $data['items'];
$puedeReasignar = td_can_reassign($rolSesion);
$puedeGestionarBase = td_can_reassign($rolSesion);

$itemsPorCodigo = [];
foreach ($items as $item) {
    $itemsPorCodigo[$item['cod_tramite']] = $item;
}

$usuariosDestino = [];
$resUsuarios = $mysqli->query("SELECT cedula_usuario, nombre_usuario, apellido_usuario, rol_usuario, rol_usuario_dos FROM usuarios_cons ORDER BY rol_usuario, nombre_usuario");
while ($resUsuarios && ($row = $resUsuarios->fetch_assoc())) {
    $usuariosDestino[] = $row;
}

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $puedeReasignar) {
    $cod = trim($_POST['cod_tramite'] ?? '');
    $destino = trim($_POST['usuario_destino'] ?? '');
    $motivo = trim($_POST['motivo'] ?? '');
    $itemActual = $itemsPorCodigo[$cod] ?? null;
    $rolActualTramite = $itemActual['rol'] ?? '';

    $dest = null;
    foreach ($usuariosDestino as $u) {
        if ((string) $u['cedula_usuario'] === $destino) {
            $dest = $u;
            break;
        }
    }

    $destRolPrincipal = $dest['rol_usuario'] ?? '';
    $destRolSecundario = $dest['rol_usuario_dos'] ?? '';

    if ($cod !== '' && $dest && $rolActualTramite !== '' && $destRolPrincipal !== $rolActualTramite && $destRolSecundario !== $rolActualTramite) {
        $mensaje = 'No se puede reasignar a otro rol. Selecciona un usuario del rol ' . $rolActualTramite . '.';
    } elseif ($cod !== '' && $dest) {
        if (($dest['rol_usuario'] ?? '') !== $rolActualTramite && ($dest['rol_usuario_dos'] ?? '') === $rolActualTramite) {
            $dest['rol_usuario'] = $rolActualTramite;
        }
        $entregaIdTramite = 0;
        $sqlAsignacionBase = "
            SELECT asignacion_id_tramite
            FROM asignacion_tramite
            WHERE asignacion_cod_tramite = ?
              AND asignacion_rol_usuario = ?
            ORDER BY asignacion_fecha_tramite DESC, asignacion_id_tramite DESC
            LIMIT 1
        ";
        if ($stmtBase = $mysqli->prepare($sqlAsignacionBase)) {
            $stmtBase->bind_param('ss', $cod, $rolActualTramite);
            if ($stmtBase->execute()) {
                $resBase = $stmtBase->get_result();
                if ($rowBase = $resBase->fetch_assoc()) {
                    $entregaIdTramite = (int)($rowBase['asignacion_id_tramite'] ?? 0);
                }
            }
            $stmtBase->close();
        }

        if ($entregaIdTramite <= 0) {
            $sqlRevisionBase = "
                SELECT entrega_id_tramite
                FROM historial_revision
                WHERE historial_cod_tramite = ?
                ORDER BY COALESCE(fecha_creacion, historial_fecha_tramite) DESC, id_revision DESC
                LIMIT 1
            ";
            if ($stmtBase = $mysqli->prepare($sqlRevisionBase)) {
                $stmtBase->bind_param('s', $cod);
                if ($stmtBase->execute()) {
                    $resBase = $stmtBase->get_result();
                    if ($rowBase = $resBase->fetch_assoc()) {
                        $entregaIdTramite = (int)($rowBase['entrega_id_tramite'] ?? 0);
                    }
                }
                $stmtBase->close();
            }
        }

        if ($entregaIdTramite <= 0) {
            $sqlEntregaBase = "
                SELECT entrega_id_tramite
                FROM entrega_asignacion
                WHERE entrega_cod_tramite = ?
                ORDER BY COALESCE(fecha_creacion, historial_fecha_tramite) DESC, id_entrega_asignacion DESC
                LIMIT 1
            ";
            if ($stmtBase = $mysqli->prepare($sqlEntregaBase)) {
                $stmtBase->bind_param('s', $cod);
                if ($stmtBase->execute()) {
                    $resBase = $stmtBase->get_result();
                    if ($rowBase = $resBase->fetch_assoc()) {
                        $entregaIdTramite = (int)($rowBase['entrega_id_tramite'] ?? 0);
                    }
                }
                $stmtBase->close();
            }
        }

        if ($entregaIdTramite <= 0) {
            $mensaje = 'No se pudo reasignar: no se encontro la asignacion base del tramite para el rol ' . $rolActualTramite . '.';
        } else {
        $nombreSesion = $_SESSION['nombre_usuario'] ?? '';
        $apellidoSesion = $_SESSION['apellido_usuario'] ?? '';
        $ccSesion = $_SESSION['cedula_usuario'] ?? ($_SESSION['id_usuario'] ?? 0);
        $nombreOrigen = $nombreSesion;
        $apellidoOrigen = $apellidoSesion;
        $ccOrigen = $ccSesion;
        $rolOrigen = $rolSesion;

        $sqlOrigen = "SELECT
                creacion_tram_cc_usuario,
                creacion_tram_nombre_usuario,
                creacion_tram_apellido_usuario,
                creacion_tram_rol_usuario,
                quien_entrego_cc,
                quien_entrego_nombre,
                quien_entrego_apellido,
                quien_entrego_rol
            FROM entrega_asignacion
            WHERE entrega_cod_tramite = ?
            ORDER BY id_entrega_asignacion DESC
            LIMIT 1";
        if ($stmtOrigen = $mysqli->prepare($sqlOrigen)) {
            $stmtOrigen->bind_param('s', $cod);
            if ($stmtOrigen->execute()) {
                $resOrigen = $stmtOrigen->get_result();
                if ($rowOrigen = $resOrigen->fetch_assoc()) {
                    $ccOrigen = $rowOrigen['creacion_tram_cc_usuario'] ?: ($rowOrigen['quien_entrego_cc'] ?: $ccOrigen);
                    $nombreOrigen = $rowOrigen['creacion_tram_nombre_usuario'] ?: ($rowOrigen['quien_entrego_nombre'] ?: $nombreOrigen);
                    $apellidoOrigen = $rowOrigen['creacion_tram_apellido_usuario'] ?: ($rowOrigen['quien_entrego_apellido'] ?: $apellidoOrigen);
                    $rolOrigen = $rowOrigen['creacion_tram_rol_usuario'] ?: ($rowOrigen['quien_entrego_rol'] ?: $rolOrigen);
                }
            }
            $stmtOrigen->close();
        }

        $fechaLimite = date('Y-m-d', strtotime('+4 days'));
        $actorReasigna = trim($nombreSesion . ' ' . $apellidoSesion . ' - ' . $rolSesion);
        $obs = 'Reasignado desde tablero de control por ' . $actorReasigna . '. Motivo: ' . $motivo;
        $sql = "INSERT INTO entrega_asignacion (
            entrega_id_tramite, entrega_cod_tramite, entrega_cc_usuario, entrega_nombre_usuario,
            entrega_apellido_usuario, entrega_rol_usuario, observacion_a_usuario_tramite,
            historial_fecha_tramite, creacion_tram_cc_usuario, creacion_tram_nombre_usuario,
            creacion_tram_apellido_usuario, creacion_tram_rol_usuario, anio_cuenta,
            quien_entrego_cc, quien_entrego_nombre, quien_entrego_apellido, quien_entrego_rol,
            fecha_limite, historial_estado_tramite
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, 0, ?, ?, ?, ?, ?, 'REASIGNADO')";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param(
            'isissssisssissss',
            $entregaIdTramite,
            $cod,
            $dest['cedula_usuario'],
            $dest['nombre_usuario'],
            $dest['apellido_usuario'],
            $dest['rol_usuario'],
            $obs,
            $ccOrigen,
            $nombreOrigen,
            $apellidoOrigen,
            $rolOrigen,
            $ccOrigen,
            $nombreOrigen,
            $apellidoOrigen,
            $rolOrigen,
            $fechaLimite
        );
        if ($stmt->execute()) {
            $stmtUpdate = $mysqli->prepare("
                UPDATE historial_revision
                SET asignacion_cc_usuario = ?,
                    asignacion_nombre_usuario = ?,
                    asignacion_apellido_usuario = ?,
                    asignacion_rol_usuario = ?,
                    rol_actual = ?,
                    fecha_limite = ?
                WHERE id_revision = (
                    SELECT id_revision
                    FROM (
                        SELECT id_revision
                        FROM historial_revision
                        WHERE historial_cod_tramite = ?
                        ORDER BY COALESCE(fecha_creacion, historial_fecha_tramite) DESC, id_revision DESC
                        LIMIT 1
                    ) AS ultimo_historial
                )
            ");
            if ($stmtUpdate) {
                $stmtUpdate->bind_param(
                    'sssssss',
                    $dest['cedula_usuario'],
                    $dest['nombre_usuario'],
                    $dest['apellido_usuario'],
                    $dest['rol_usuario'],
                    $dest['rol_usuario'],
                    $fechaLimite,
                    $cod
                );
                $stmtUpdate->execute();
                $stmtUpdate->close();
            }
            $mensaje = 'Tramite reasignado correctamente.';
            $data = td_dashboard_data($mysqli, array_merge(td_dashboard_filters(), ['usuario' => $usuario, 'rol' => $rol]));
            $items = $data['items'];
        } else {
            $mensaje = 'No se pudo reasignar: ' . $stmt->error;
        }
        }
    }
}
?>

<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-1" style="color:#002F55;">Detalle de asignaciones por usuario</h3>
            <div class="text-muted"><?= td_h($usuario) ?> | <?= td_h($rol) ?></div>
        </div>
        <a class="btn btn-outline-primary btn-sm" href="index.php?page=tramites/dashboard"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-info"><?= td_h($mensaje) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0" style="border-radius:12px;">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID tramite</th>
                            <th>Estado</th>
                            <th>Fecha asignacion</th>
                            <th>Fecha limite</th>
                            <th>Dias</th>
                            <th>Rol/etapa</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="fw-bold"><?= td_h($item['cod_tramite']) ?></td>
                                <td><?= td_h($item['estado_vencimiento']) ?></td>
                                <td><?= td_h($item['fecha_movimiento']) ?></td>
                                <td><?= td_h($item['fecha_limite']) ?></td>
                                <td><?= $item['dias'] === null ? '-' : (int) $item['dias'] ?></td>
                                <td><?= td_h(($item['rol'] ?? '') . ' / ' . ($item['etapa'] ?? '')) ?></td>
                                <td class="d-flex gap-1 flex-wrap">
                                    <a class="btn btn-sm btn-outline-primary" href="index.php?page=tramites/acciones/ver_tramite_rad&cod=<?= urlencode($item['cod_tramite']) ?>">Ver radicacion</a>
                                    <?php if ($puedeGestionarBase): ?>
                                        <a class="btn btn-sm btn-outline-success" href="index.php?page=tramites/base_catastral/predio_mutacion&cod=<?= urlencode($item['cod_tramite']) ?>">Base catastral</a>
                                    <?php endif; ?>
                                    <?php if ($puedeReasignar): ?>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalReasignar<?= md5($item['cod_tramite']) ?>">Reasignar</button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#modalHistorial<?= md5($item['cod_tramite']) ?>">Historial</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($items)): ?>
                            <tr><td colspan="7" class="text-center text-muted">No hay tramites para este usuario con los filtros actuales.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if ($puedeReasignar): ?>
        <?php foreach ($items as $item): ?>
                <div class="modal fade" id="modalReasignar<?= md5($item['cod_tramite']) ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <form method="POST" class="modal-content">
                            <div class="modal-header" style="background:#002F55;color:#fff;">
                                <h5 class="modal-title">Reasignar <?= td_h($item['cod_tramite']) ?></h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="cod_tramite" value="<?= td_h($item['cod_tramite']) ?>">
                                <label class="form-label fw-bold">Usuario destino</label>
                                <select name="usuario_destino" class="form-select" required>
                                    <option value="">Seleccione un usuario</option>
                                    <?php foreach ($usuariosDestino as $u): ?>
                                        <?php if (($u['rol_usuario'] ?? '') === ($item['rol'] ?? '') || ($u['rol_usuario_dos'] ?? '') === ($item['rol'] ?? '')): ?>
                                            <option value="<?= td_h($u['cedula_usuario']) ?>"><?= td_h($u['nombre_usuario'] . ' ' . $u['apellido_usuario'] . ' - ' . ($item['rol'] ?? $u['rol_usuario'])) ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <label class="form-label fw-bold mt-3">Motivo</label>
                                <textarea name="motivo" class="form-control" rows="4" required></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button class="btn btn-warning">Reasignar</button>
                            </div>
                        </form>
                    </div>
                </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php foreach ($items as $item): ?>
        <?php $historial = td_assignment_history($mysqli, $item['cod_tramite']); ?>
        <div class="modal fade" id="modalHistorial<?= md5($item['cod_tramite']) ?>" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width:min(1500px,96vw);">
                <div class="modal-content">
                    <div class="modal-header" style="background:#002F55;color:#fff;">
                        <h5 class="modal-title">Historial <?= td_h($item['cod_tramite']) ?></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle" style="min-width:1180px;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Evento</th>
                                        <th>Usuario destino</th>
                                        <th>Rol destino</th>
                                        <th>Origen</th>
                                        <th>Estado</th>
                                        <th>Observacion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historial as $h): ?>
                                        <tr>
                                            <td><?= td_h($h['fecha'] ?? '') ?></td>
                                            <td><?= td_h($h['evento'] ?? '') ?></td>
                                            <td><?= td_h(trim(($h['nombre'] ?? '') . ' ' . ($h['apellido'] ?? ''))) ?></td>
                                            <td><?= td_h($h['rol'] ?? '') ?></td>
                                            <td><?= td_h(trim(($h['origen_nombre'] ?? '') . ' ' . ($h['origen_apellido'] ?? '') . ' - ' . ($h['origen_rol'] ?? ''))) ?></td>
                                            <td><?= td_h($h['estado'] ?? '') ?></td>
                                            <td><?= td_h($h['observacion'] ?? '') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($historial)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">No hay historial disponible para este tramite.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
