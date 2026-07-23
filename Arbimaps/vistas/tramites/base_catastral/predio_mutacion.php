<?php
if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    echo "<script>alert('Sesion expirada o no iniciada.'); window.location='index.php';</script>";
    exit();
}

if (!isset($mysqli)) {
    require dirname(__DIR__, 4) . '/conexion.php';
}

require_once __DIR__ . '/base_catastral_lib.php';

$cod_tramite = $_GET['cod'] ?? $_POST['cod_tramite'] ?? '';
$rol_usuario = $_SESSION['rol_usuario'] ?? '';
$nombre_usuario = trim(($_SESSION['nombre_usuario'] ?? '') . ' ' . ($_SESSION['apellido_usuario'] ?? ''));

if ($cod_tramite === '') {
    echo '<div class="alert alert-warning">Falta el codigo del tramite.</div>';
    return;
}

$sql = "SELECT * FROM tramite_radicacion WHERE cod_tramite = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $cod_tramite);
$stmt->execute();
$tramite = $stmt->get_result()->fetch_assoc();

if (!$tramite) {
    echo '<div class="alert alert-warning">No se encontro el tramite solicitado.</div>';
    return;
}

$numero_predial = $tramite['npn_predio'] ?? '';
$mutacion = $tramite['mutacion_tramite'] ?? '';
$draft = bc_load_or_create_draft($cod_tramite, $numero_predial, $mutacion, $rol_usuario, $nombre_usuario);
$message = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['bc_action'] ?? 'save';
    $editableTabs = bc_editable_tabs_for_role($rol_usuario);

    try {
        if ($action === 'revert_change') {
            $draft = bc_revert_proposed_change(
                $cod_tramite,
                $draft,
                $_POST['revert_registro'] ?? '',
                (int) ($_POST['revert_row'] ?? -1),
                $_POST['revert_col'] ?? '',
                $rol_usuario,
                $nombre_usuario
            );
            $message = 'Cambio revertido al valor oficial original.';
        } elseif (!empty($editableTabs)) {
            $draft = bc_apply_posted_changes($cod_tramite, $draft, $_POST['fields'] ?? [], $rol_usuario, $nombre_usuario);
            $message = 'Propuesta guardada y Excel temporal actualizado.';
        } elseif ($action !== 'consolidate') {
            $message = 'Tu rol puede consultar la informacion, pero no editar esta propuesta.';
        }

        if ($action === 'consolidate') {
            $draft = bc_consolidate_official($cod_tramite, $draft, $rol_usuario, $nombre_usuario);
            $message = 'Cambios aplicados a la base oficial. Se genero backup en la carpeta del radicado.';
        }
    } catch (Throwable $e) {
        $errorMessage = 'No se pudo actualizar la base catastral: ' . $e->getMessage();
    }
}

$visibleTabs = bc_mutation_tabs($mutacion);
$editableTabs = bc_editable_tabs_for_role($rol_usuario);
$tabLabels = [
    'informacion' => 'Informacion del predio',
    'propietarios' => 'Propietarios',
    'construcciones' => 'Construcciones',
    'direcciones' => 'Direcciones',
    'avaluo' => 'Avaluo',
    'cambios' => 'Cambios propuestos',
    'comparativo' => 'Comparativo',
];

function bc_h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function bc_render_table(array $draft, string $registroKey, string $tab, bool $editable): void
{
    $columns = bc_columns_for_tab($tab);
    $data = $draft[$registroKey] ?? ['headers' => [], 'rows' => []];
    $headers = $data['headers'] ?? [];
    $rows = $data['rows'] ?? [];

    if (empty($rows)) {
        echo '<div class="alert alert-warning mb-3">No se encontraron filas para este predio en ' . bc_h(str_replace('_propuesto', '', $registroKey)) . '.</div>';
        return;
    }

    echo '<div class="table-responsive mb-4">';
    echo '<table class="table table-sm table-bordered table-hover base-table">';
    echo '<thead class="table-light"><tr><th>Fila Excel</th>';
    foreach ($columns as $col) {
        echo '<th>' . bc_h($headers[$col] ?? $col) . '</th>';
    }
    echo '</tr></thead><tbody>';

    foreach ($rows as $rowIndex => $row) {
        echo '<tr><td class="text-muted">' . bc_h($row['row_number'] ?? '') . '</td>';
        foreach ($columns as $col) {
            $value = $row['values'][$col] ?? '';
            echo '<td>';
            if ($editable) {
                echo '<input class="form-control form-control-sm" name="fields[' . bc_h($registroKey) . '][' . (int) $rowIndex . '][' . bc_h($col) . ']" value="' . bc_h($value) . '">';
            } else {
                echo bc_h($value);
            }
            echo '</td>';
        }
        echo '</tr>';
    }

    echo '</tbody></table></div>';
}

function bc_render_changes(array $draft): void
{
    $changes = $draft['cambios'] ?? [];
    if (empty($changes)) {
        echo '<div class="alert alert-info">Todavia no hay cambios propuestos.</div>';
        return;
    }

    echo '<div class="table-responsive"><table class="table table-sm table-bordered">';
    echo '<thead class="table-light"><tr><th>Fecha</th><th>Usuario</th><th>Rol</th><th>Accion</th><th>Registro</th><th>Fila</th><th>Campo</th><th>Anterior</th><th>Nuevo</th></tr></thead><tbody>';
    foreach (array_reverse($changes) as $change) {
        echo '<tr>';
        foreach (['fecha', 'usuario', 'rol', 'accion', 'registro', 'fila', 'campo', 'valor_anterior', 'valor_nuevo'] as $key) {
            $value = $change[$key] ?? '';
            if ($key === 'accion' && $value === '') {
                $value = 'MODIFICACION';
            }
            echo '<td>' . bc_h($value) . '</td>';
        }
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}

function bc_render_compare(array $draft, string $registro, string $role): void
{
    $originalKey = $registro . '_original';
    $proposedKey = $registro . '_propuesto';
    $originalRows = $draft[$originalKey]['rows'] ?? [];
    $proposedRows = $draft[$proposedKey]['rows'] ?? [];
    $headers = $draft[$proposedKey]['headers'] ?? [];

    echo '<h6 class="fw-bold text-primary mt-3">' . bc_h(strtoupper($registro)) . '</h6>';
    echo '<div class="table-responsive"><table class="table table-sm table-bordered">';
    echo '<thead class="table-light"><tr><th>Fila</th><th>Campo</th><th>Valor oficial</th><th>Valor propuesto</th><th>Estado</th><th>Accion</th></tr></thead><tbody>';

    $printed = 0;
    foreach ($proposedRows as $rowIndex => $row) {
        foreach (($row['values'] ?? []) as $col => $newValue) {
            $oldValue = $originalRows[$rowIndex]['values'][$col] ?? '';
            if (bc_normalize_value($oldValue) === bc_normalize_value($newValue)) {
                continue;
            }

            $printed++;
            echo '<tr><td>' . bc_h($row['row_number'] ?? '') . '</td>';
            echo '<td>' . bc_h($headers[$col] ?? $col) . '</td>';
            echo '<td>' . bc_h($oldValue) . '</td>';
            echo '<td>' . bc_h($newValue) . '</td>';
            echo '<td><span class="badge bg-warning text-dark">Modificado</span></td>';
            echo '<td>';
            if (bc_can_revert_column($role, $col)) {
                echo '<button type="submit" class="btn btn-outline-danger btn-sm bc-revert-btn" name="bc_action" value="revert_change"';
                echo ' data-registro="' . bc_h($registro) . '" data-row="' . (int) $rowIndex . '" data-col="' . bc_h($col) . '">';
                echo '<i class="bi bi-arrow-counterclockwise me-1"></i> Revertir</button>';
            } else {
                echo '<span class="text-muted">Solo consulta</span>';
            }
            echo '</td></tr>';
        }
    }

    if ($printed === 0) {
        echo '<tr><td colspan="6" class="text-muted">Sin diferencias.</td></tr>';
    }

    echo '</tbody></table></div>';
}
?>

<style>
    .base-table th,
    .base-table td {
        min-width: 180px;
        vertical-align: middle;
    }

    .base-table th:first-child,
    .base-table td:first-child {
        min-width: 90px;
    }

    .base-summary {
        border: 1px solid #d1d3e2;
        border-radius: .35rem;
        padding: .5rem .75rem;
        background: #fff;
        height: 100%;
    }

    .bc-loading-overlay {
        align-items: center;
        background: rgba(255, 255, 255, .92);
        bottom: 0;
        display: none;
        justify-content: center;
        left: 0;
        position: fixed;
        right: 0;
        top: 0;
        z-index: 3000;
    }

    .bc-loading-overlay.is-visible {
        display: flex;
    }

    .bc-loading-panel {
        background: #fff;
        border: 1px solid #d1d3e2;
        border-radius: .5rem;
        box-shadow: 0 .75rem 2rem rgba(0, 47, 85, .18);
        max-width: 440px;
        padding: 1.5rem;
        text-align: center;
        width: calc(100% - 2rem);
    }

    .bc-loading-title {
        color: #002F55;
        font-weight: 700;
        margin-bottom: .35rem;
    }

    .bc-loading-text {
        color: #4b5563;
        font-size: .95rem;
        margin-bottom: 0;
    }
</style>

<div class="bc-loading-overlay" id="bcLoadingOverlay" role="alert" aria-live="assertive" aria-hidden="true">
    <div class="bc-loading-panel">
        <div class="spinner-border text-primary mb-3" role="status" aria-hidden="true"></div>
        <div class="bc-loading-title" id="bcLoadingTitle">Procesando base catastral</div>
        <p class="bc-loading-text" id="bcLoadingText">Por favor espera y no cierres esta ventana.</p>
    </div>
</div>

<div class="container-fluid px-4 py-3">
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h3 mb-1 fw-bold">Base catastral del tramite</h1>
            <div class="text-muted">Radicado <?= bc_h($cod_tramite) ?> | Predio <?= bc_h($numero_predial) ?></div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-primary btn-sm" href="index.php?page=tramites/acciones/asignar_tram_procedencia&cod=<?= urlencode($cod_tramite) ?>">
                <i class="bi bi-arrow-left me-1"></i> Volver
            </a>
            <?php if (file_exists(bc_temp_xlsx_path($cod_tramite))): ?>
                <a class="btn btn-primary btn-sm" href="vistas/tramites/base_catastral/descargar_temporal.php?cod=<?= urlencode($cod_tramite) ?>">
                    <i class="bi bi-file-earmark-excel me-1"></i> Excel temporal
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= bc_h($message) ?></div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <div class="alert alert-danger"><?= bc_h($errorMessage) ?></div>
    <?php endif; ?>

    <div class="row g-2 mb-3">
        <div class="col-md-3"><div class="base-summary"><b>Mutacion</b><br><?= bc_h($mutacion) ?></div></div>
        <div class="col-md-3"><div class="base-summary"><b>Rol actual</b><br><?= bc_h($rol_usuario) ?></div></div>
        <div class="col-md-3"><div class="base-summary"><b>Estado propuesta</b><br><?= bc_h($draft['meta']['estado'] ?? 'EN_PROPUESTA') ?></div></div>
        <div class="col-md-3"><div class="base-summary"><b>Ultima actualizacion</b><br><?= bc_h($draft['meta']['updated_at'] ?? '') ?></div></div>
    </div>

    <form method="POST" action="index.php?page=tramites/base_catastral/predio_mutacion&cod=<?= urlencode($cod_tramite) ?>" id="bcProposalForm">
        <input type="hidden" name="cod_tramite" value="<?= bc_h($cod_tramite) ?>">
        <input type="hidden" name="revert_registro" id="bcRevertRegistro" value="">
        <input type="hidden" name="revert_row" id="bcRevertRow" value="">
        <input type="hidden" name="revert_col" id="bcRevertCol" value="">

        <ul class="nav nav-tabs" role="tablist">
            <?php foreach ($visibleTabs as $index => $tab): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $index === 0 ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#bc-<?= bc_h($tab) ?>" type="button" role="tab">
                        <?= bc_h($tabLabels[$tab] ?? $tab) ?>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="tab-content bg-white border border-top-0 p-3 mb-3">
            <?php foreach ($visibleTabs as $index => $tab): ?>
                <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>" id="bc-<?= bc_h($tab) ?>" role="tabpanel">
                    <?php if (in_array($tab, ['informacion', 'propietarios', 'construcciones', 'direcciones', 'avaluo'], true)): ?>
                        <?php $editable = in_array($tab, $editableTabs, true); ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><?= bc_h($tabLabels[$tab]) ?></h5>
                            <span class="badge <?= $editable ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $editable ? 'Editable por tu rol' : 'Solo consulta' ?>
                            </span>
                        </div>
                        <h6 class="fw-bold text-primary">Registro 1</h6>
                        <?php bc_render_table($draft, 'registro1_propuesto', $tab, $editable); ?>
                        <h6 class="fw-bold text-primary">Registro 2</h6>
                        <?php bc_render_table($draft, 'registro2_propuesto', $tab, $editable); ?>
                    <?php elseif ($tab === 'cambios'): ?>
                        <h5>Cambios propuestos</h5>
                        <?php bc_render_changes($draft); ?>
                    <?php elseif ($tab === 'comparativo'): ?>
                        <h5>Comparativo oficial vs propuesta</h5>
                        <?php bc_render_compare($draft, 'registro1', $rol_usuario); ?>
                        <?php bc_render_compare($draft, 'registro2', $rol_usuario); ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-success" id="bcSaveProposalBtn" <?= empty($editableTabs) ? 'disabled' : '' ?>>
                <i class="bi bi-save me-1"></i> Guardar propuesta
            </button>
            <?php if (bc_can_consolidate($rol_usuario)): ?>
                <button type="submit" class="btn btn-warning" id="bcApplyOfficialBtn" name="bc_action" value="consolidate">
                    <i class="bi bi-check-circle me-1"></i> Aplicar a base oficial
                </button>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
    (function() {
        const overlay = document.getElementById('bcLoadingOverlay');
        const title = document.getElementById('bcLoadingTitle');
        const text = document.getElementById('bcLoadingText');
        const form = document.getElementById('bcProposalForm');
        const saveButton = document.getElementById('bcSaveProposalBtn');
        const applyButton = document.getElementById('bcApplyOfficialBtn');
        const revertRegistro = document.getElementById('bcRevertRegistro');
        const revertRow = document.getElementById('bcRevertRow');
        const revertCol = document.getElementById('bcRevertCol');

        function showLoading(nextTitle, nextText) {
            if (!overlay) return;
            title.textContent = nextTitle;
            text.textContent = nextText;
            overlay.classList.add('is-visible');
            overlay.setAttribute('aria-hidden', 'false');
        }

        if (form) {
            form.querySelectorAll('.bc-revert-btn').forEach(function(button) {
                button.addEventListener('click', function() {
                    if (revertRegistro) revertRegistro.value = button.dataset.registro || '';
                    if (revertRow) revertRow.value = button.dataset.row || '';
                    if (revertCol) revertCol.value = button.dataset.col || '';
                });
            });

            form.addEventListener('submit', function(event) {
                const submitter = event.submitter || document.activeElement;
                const isConsolidation = submitter && submitter.value === 'consolidate';
                const isReversion = submitter && submitter.value === 'revert_change';

                if (isConsolidation) {
                    showLoading(
                        'Actualizando base oficial',
                        'Se esta generando backup y aplicando la propuesta. Este proceso puede tardar; no cierres ni recargues la pagina.'
                    );
                    return;
                }

                if (isReversion) {
                    showLoading(
                        'Revirtiendo cambio',
                        'Estamos devolviendo el campo seleccionado al valor oficial original y registrando la trazabilidad.'
                    );
                    return;
                }

                showLoading(
                    'Guardando propuesta temporal',
                    'Estamos actualizando el preliminar del radicado. Por favor espera y no cierres esta ventana.'
                );
            });
        }

        if (applyButton) {
            applyButton.addEventListener('click', function(event) {
                const accepted = confirm('Esto aplicara la propuesta al Excel oficial. Deseas continuar?');
                if (!accepted) {
                    event.preventDefault();
                    return;
                }
            });
        }
    })();
</script>
