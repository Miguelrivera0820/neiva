<?php
require_once __DIR__ . '/funciones_restricciones.php';

if (!usuarioPuedeAdministrarBloqueos()) {
    echo '<div class="alert alert-danger m-4">No tiene permisos para administrar bloqueos de predios.</div>';
    return;
}

if (empty($_SESSION['csrf_predios_bloqueados'])) {
    $_SESSION['csrf_predios_bloqueados'] = bin2hex(random_bytes(32));
}

$estadoFiltro = $_GET['estado_bloqueo'] ?? 'BLOQUEADO';
$estadosPermitidos = ['BLOQUEADO', 'DESBLOQUEADO', 'TODOS'];
if (!in_array($estadoFiltro, $estadosPermitidos, true)) {
    $estadoFiltro = 'BLOQUEADO';
}

$buscar = trim($_GET['buscar_predio_bloqueado'] ?? '');
$condiciones = [];
$parametros = [];
$tipos = '';

if ($estadoFiltro !== 'TODOS') {
    $condiciones[] = 'estado = ?';
    $parametros[] = $estadoFiltro;
    $tipos .= 's';
}

if ($buscar !== '') {
    $normalizado = '%' . normalizarIdentificadorPredio($buscar) . '%';
    $condiciones[] = '(npn_normalizado LIKE ? OR fmi_normalizado LIKE ?)';
    $parametros[] = $normalizado;
    $parametros[] = $normalizado;
    $tipos .= 'ss';
}

$sql = "SELECT * FROM predios_bloqueados";
if ($condiciones) {
    $sql .= ' WHERE ' . implode(' AND ', $condiciones);
}
$sql .= " ORDER BY fecha_bloqueo DESC LIMIT 500";

$stmt = $mysqli->prepare($sql);
if ($parametros) {
    $referencias = [$tipos];
    foreach ($parametros as $indice => $valor) {
        $referencias[] = &$parametros[$indice];
    }
    call_user_func_array([$stmt, 'bind_param'], $referencias);
}
$stmt->execute();
$registros = $stmt->get_result();
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .restriccion-card { border: 0; border-radius: 14px; }
    .restriccion-icon {
        width: 48px; height: 48px; border-radius: 14px; display: grid;
        place-items: center; background: #a3170d; color: #fff; font-size: 1.25rem;
    }
    .tabla-restricciones thead th {
        background: #002F55; color: #fff; font-size: .75rem;
        vertical-align: middle; white-space: nowrap;
    }
    .tabla-restricciones td { font-size: .82rem; vertical-align: middle; }
    .motivo-restriccion { min-width: 210px; max-width: 360px; white-space: normal; }
</style>

<div class="container-fluid py-3">
    <div class="text-center mb-3">
        <h3 class="mb-0 fw-bold" style="color:#002F55;">BLOQUEO Y DESBLOQUEO DE PREDIOS</h3>
        <small>Administración y trazabilidad de restricciones por NPN o FMI</small>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-4">
            <div class="card restriccion-card shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="restriccion-icon"><i class="bi bi-lock-fill"></i></div>
                        <div>
                            <h5 class="fw-bold mb-1">Bloquear predio</h5>
                            <small class="text-muted">Ingrese el NPN, el FMI o ambos.</small>
                        </div>
                    </div>

                    <form id="formBloquearPredio">
                        <input type="hidden" name="csrf_token"
                            value="<?= htmlspecialchars($_SESSION['csrf_predios_bloqueados']) ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold" for="bloqueo_npn">Código catastral (NPN)</label>
                            <input class="form-control" id="bloqueo_npn" name="npn" maxlength="100"
                                placeholder="Ingrese el número predial">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold" for="bloqueo_fmi">FMI</label>
                            <input class="form-control" id="bloqueo_fmi" name="fmi" maxlength="100"
                                placeholder="Ej. 200-65415412">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold" for="bloqueo_motivo">Motivo del bloqueo</label>
                            <textarea class="form-control" id="bloqueo_motivo" name="motivo" rows="5"
                                maxlength="2000" required
                                placeholder="Litigio, medida cautelar, orden judicial..."></textarea>
                        </div>

                        <button type="submit" class="btn w-100 text-white" style="background:#b42318;">
                            <i class="bi bi-lock-fill me-2"></i>Bloquear predio
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="card restriccion-card shadow-sm">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                        <div>
                            <h5 class="fw-bold mb-1">Predios restringidos</h5>
                            <small class="text-muted">Consulte bloqueos activos y el historial.</small>
                        </div>

                        <form method="GET" action="index.php" id="formFiltrosPrediosBloqueados"
                            class="d-flex gap-2 flex-wrap">
                            <input type="hidden" name="page" value="restricciones_predios/administrar">
                            <select class="form-select form-select-sm" id="filtroEstadoPrediosBloqueados"
                                name="estado_bloqueo" style="width:170px;"
                                aria-label="Filtrar predios por estado">
                                <option value="BLOQUEADO" <?= $estadoFiltro === 'BLOQUEADO' ? 'selected' : '' ?>>Bloqueados</option>
                                <option value="DESBLOQUEADO" <?= $estadoFiltro === 'DESBLOQUEADO' ? 'selected' : '' ?>>Desbloqueados</option>
                                <option value="TODOS" <?= $estadoFiltro === 'TODOS' ? 'selected' : '' ?>>Todo el historial</option>
                            </select>
                            <input class="form-control form-control-sm" id="buscarPredioBloqueado"
                                name="buscar_predio_bloqueado"
                                value="<?= htmlspecialchars($buscar) ?>" placeholder="Buscar NPN o FMI"
                                style="width:190px;" autocomplete="off">
                            <button type="submit" class="btn btn-sm text-white" style="background:#002F55;"
                                title="Aplicar filtros">
                                <i class="bi bi-search"></i>
                            </button>
                            <?php if ($buscar !== '' || $estadoFiltro !== 'BLOQUEADO'): ?>
                                <a href="index.php?page=restricciones_predios/administrar"
                                    class="btn btn-sm btn-outline-secondary" title="Limpiar filtros">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered tabla-restricciones">
                            <thead>
                                <tr>
                                    <th>Estado</th>
                                    <th>NPN / FMI</th>
                                    <th>Motivo</th>
                                    <th>Auditoría</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$registros || $registros->num_rows === 0): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4">No hay registros.</td></tr>
                                <?php else: ?>
                                    <?php while ($registro = $registros->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <span class="badge <?= $registro['estado'] === 'BLOQUEADO' ? 'bg-danger' : 'bg-success' ?>">
                                                    <?= htmlspecialchars($registro['estado']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($registro['npn'])): ?>
                                                    <div><b>NPN:</b> <?= htmlspecialchars($registro['npn']) ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($registro['fmi'])): ?>
                                                    <div><b>FMI:</b> <?= htmlspecialchars($registro['fmi']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="motivo-restriccion">
                                                <?= nl2br(htmlspecialchars($registro['motivo'])) ?>
                                                <?php if (!empty($registro['motivo_desbloqueo'])): ?>
                                                    <div class="text-success small mt-2">
                                                        <b>Desbloqueo:</b>
                                                        <?= nl2br(htmlspecialchars($registro['motivo_desbloqueo'])) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div><?= htmlspecialchars($registro['fecha_bloqueo']) ?></div>
                                                <small>Por <?= htmlspecialchars($registro['usuario_bloqueo_nombre']) ?></small>
                                                <?php if (!empty($registro['fecha_desbloqueo'])): ?>
                                                    <div class="text-success mt-2">
                                                        <?= htmlspecialchars($registro['fecha_desbloqueo']) ?><br>
                                                        <small>Por <?= htmlspecialchars($registro['usuario_desbloqueo_nombre']) ?></small>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($registro['estado'] === 'BLOQUEADO'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-success btn-desbloquear"
                                                        data-id="<?= (int) $registro['id_bloqueo'] ?>">
                                                        <i class="bi bi-unlock-fill"></i> Desbloquear
                                                    </button>
                                                <?php else: ?>
                                                    —
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const formFiltrosPredios = document.getElementById('formFiltrosPrediosBloqueados');
const filtroEstadoPredios = document.getElementById('filtroEstadoPrediosBloqueados');
const buscarPredioBloqueado = document.getElementById('buscarPredioBloqueado');
let temporizadorBusquedaPredio = null;

function aplicarFiltrosPrediosBloqueados() {
    if (!formFiltrosPredios) return;
    formFiltrosPredios.requestSubmit();
}

filtroEstadoPredios?.addEventListener('change', aplicarFiltrosPrediosBloqueados);

buscarPredioBloqueado?.addEventListener('input', function () {
    clearTimeout(temporizadorBusquedaPredio);
    temporizadorBusquedaPredio = setTimeout(aplicarFiltrosPrediosBloqueados, 550);
});

document.getElementById('formBloquearPredio')?.addEventListener('submit', async function (event) {
    event.preventDefault();
    const npn = document.getElementById('bloqueo_npn').value.trim();
    const fmi = document.getElementById('bloqueo_fmi').value.trim();
    const motivo = document.getElementById('bloqueo_motivo').value.trim();

    if (!npn && !fmi) {
        return Swal.fire('Dato requerido', 'Ingrese un NPN o un FMI.', 'warning');
    }
    if (motivo.length < 5) {
        return Swal.fire('Motivo requerido', 'Describa el motivo del bloqueo.', 'warning');
    }

    const respuesta = await fetch('vistas/restricciones_predios/acciones/guardar_bloqueo.php', {
        method: 'POST',
        body: new FormData(this)
    });
    const data = await respuesta.json();
    if (!respuesta.ok || !data.ok) {
        return Swal.fire('No fue posible bloquear', data.mensaje || 'Ocurrió un error.', 'error');
    }

    await Swal.fire('Predio bloqueado', data.mensaje, 'success');
    window.location.reload();
});

document.querySelectorAll('.btn-desbloquear').forEach(function (boton) {
    boton.addEventListener('click', async function () {
        const resultado = await Swal.fire({
            title: 'Desbloquear predio',
            input: 'textarea',
            inputLabel: 'Motivo del desbloqueo',
            inputPlaceholder: 'Indique la orden o causa de liberación del predio...',
            showCancelButton: true,
            confirmButtonText: 'Desbloquear',
            confirmButtonColor: '#198754',
            inputValidator: value => !value || value.trim().length < 5
                ? 'Debe registrar un motivo.'
                : undefined
        });
        if (!resultado.isConfirmed) return;

        const datos = new FormData();
        datos.append('csrf_token', '<?= htmlspecialchars($_SESSION['csrf_predios_bloqueados']) ?>');
        datos.append('id_bloqueo', this.dataset.id);
        datos.append('motivo_desbloqueo', resultado.value.trim());

        const respuesta = await fetch('vistas/restricciones_predios/acciones/desbloquear.php', {
            method: 'POST',
            body: datos
        });
        const data = await respuesta.json();
        if (!respuesta.ok || !data.ok) {
            return Swal.fire('No fue posible desbloquear', data.mensaje || 'Ocurrió un error.', 'error');
        }

        await Swal.fire('Predio desbloqueado', data.mensaje, 'success');
        window.location.reload();
    });
});
</script>
