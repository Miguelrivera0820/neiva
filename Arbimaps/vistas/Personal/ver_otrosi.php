<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../conexion.php';

function h($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function normalizarRutaDoc(string $ruta): string
{
    $ruta = trim($ruta);
    if ($ruta === '') return '';

    if (strpos($ruta, '/vistas/Personal/') !== false) {
        return $ruta;
    }

    $old = '/arbimaps/Arbimaps/';
    $new = '/arbimaps/Arbimaps/vistas/Personal/';

    if (strpos($ruta, $old) === 0) {
        $resto = substr($ruta, strlen($old));
        return $new . ltrim($resto, '/');
    }
    if (strpos($ruta, 'Arbitrium_otrosi/') === 0) {
        return $new . ltrim($ruta, '/');
    }

    return $ruta;
}

$errores    = [];
$otr_cedula = $_GET['con_num_identidad'] ?? '';

if (!isset($mysqli) || $mysqli === null) {
    http_response_code(500);
    $errores[] = 'Error: no se encontró la conexión $mysqli. Revisa conexion.php';
}
if (trim($otr_cedula) === '') {
    $errores[] = 'Cédula no proporcionada.';
}

$total_documentos = 0;
$documentos = [];

if (!$errores) {
    $sql_total  = "SELECT COUNT(*) AS total FROM otrosi WHERE otr_cedula = ?";
    $stmt_total = $mysqli->prepare($sql_total);

    if (!$stmt_total) {
        $errores[] = 'Error al preparar total: ' . $mysqli->error;
    } else {
        $stmt_total->bind_param("s", $otr_cedula);
        $stmt_total->execute();
        $result_total = $stmt_total->get_result();
        $row_total = $result_total ? $result_total->fetch_assoc() : null;
        $total_documentos = (int)($row_total['total'] ?? 0);
        $stmt_total->close();
    }

    if (!$errores) {
        $sql = "SELECT 
                    otr_id, 
                    otr_otrosi, 
                    otr_tipo, 
                    otr_fecha, 
                    otr_fecha_Final, 
                    otr_salario, 
                    otr_cargo, 
                    otr_proyecto, 
                    otr_cumplimiento, 
                    otr_acta, 
                    otr_actaFi
                FROM otrosi
                WHERE otr_cedula = ?";
        $stmt = $mysqli->prepare($sql);

        if (!$stmt) {
            $errores[] = 'Error al preparar consulta: ' . $mysqli->error;
        } else {
            $stmt->bind_param("s", $otr_cedula);
            $stmt->execute();
            $resultado = $stmt->get_result();
            if ($resultado && $resultado->num_rows > 0) {
                $documentos = $resultado->fetch_all(MYSQLI_ASSOC);
            }
            $stmt->close();
        }
    }
}

if (isset($mysqli) && $mysqli) {
    $mysqli->close();
}

function renderDocumento(string $archivo, string $label = 'Documento'): void
{
    $archivo = normalizarRutaDoc($archivo);

    // 1) Sin archivo
    if (trim($archivo) === '') {
        echo "
        <div class='alert alert-secondary small text-center mb-2'>
            <i class='bi bi-file-earmark-x'></i>
            $label no cargado
        </div>";
        return;
    }

    // 2) Validar existencia física
    $rutaFisica = $_SERVER['DOCUMENT_ROOT'] . $archivo;

    if (!file_exists($rutaFisica)) {
        echo "
        <div class='alert alert-warning small text-center mb-2'>
            <i class='bi bi-exclamation-triangle'></i>
            $label registrado, pero no encontrado en el servidor
        </div>";
        return;
    }

    // 3) Render estilo “como la otra vista”
    $archivoNombre = basename($archivo);
    $url = h($archivo);
?>
    <div class="col-12 col-md-4 mb-3 ">
        <div class="border rounded-3 p-3 shadow-sm h-100 bg-white justify-items-center">

            <h6 class="fw-bold text-center mb-2">
                <?= h($label) ?>
            </h6>


            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-pdf fs-4"></i>
                <input class="form-control form-control-sm" value="<?= h($archivoNombre) ?>" readonly>
            </div>

            <div class="text-center mt-2">
                <button type="button"
                    class="bot_mostrar_vista btn btn-sm"
                    onclick="abrirModalInforme('<?= $url ?>')">
                    <i class="bi bi-eye"></i> Vista previa
                </button>
            </div>

        </div>
    </div>
<?php
}
?>

<style>
    .card-hear {
        background-color: #002F55;
        background-image: url("data:image/svg+xml;utf8,\<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23e5e7eb'>\<path d='M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0'/>\<path fill-rule='evenodd' d='M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1'/>\</svg>");
        background-repeat: no-repeat;
        background-size: 4em;
        background-position: 101% 140%;
    }

    .card-header {
        background: linear-gradient(325deg, #635223ff, #012949ff);
    }
</style>

<div class="container-fluid ">
    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errores as $e): ?>
                    <li><?= h($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>

        <div class="mb-3  d-flex align-items-center text-start rounded-4 card-hear shadow  px-3 py-2 text-white">
            <i class="bi bi-file-earmark-medical me-3 fs-2"></i>
            <div>
                <h3 class="h5 mb-1">Documentos de Otro Sí</h3>
                <p class=" mb-0" style="font-size: 0.85rem; color:#999999">
                    Información de la contratación del colaborador.
                </p>

                <div class=" p-1 w-50 text-center rounded-3 my-2 " style="font-size: 0.7em; background-color:#198754">
                    <?= (int)$total_documentos ?>
                    registrado<?= ((int)$total_documentos === 1 ? '' : 's') ?>
                </div>
            </div>
        </div>

        <?php if (empty($documentos)): ?>
            <div class="alert alert-info">No hay documentos registrados para esta cédula.</div>
        <?php else: ?>
            <?php foreach ($documentos as $fila): ?>
                <?php
                $otr_id          = $fila['otr_id'] ?? '';
                $otr_tipo        = $fila['otr_tipo'] ?? '';
                $otr_fecha       = $fila['otr_fecha'] ?? '';
                $otr_fecha_final = $fila['otr_fecha_Final'] ?? '';
                $otr_cargo       = $fila['otr_cargo'] ?? '';
                $otr_proyecto    = $fila['otr_proyecto'] ?? '';
                $otr_salario     = $fila['otr_salario'] ?? 0;

                $salario_fmt = number_format((float)$otr_salario, 0, ',', '.');

                $otr_otrosi       = $fila['otr_otrosi'] ?? '';
                $otr_cumplimiento = $fila['otr_cumplimiento'] ?? '';
                $otr_acta         = $fila['otr_acta'] ?? '';
                $otr_actaFi       = $fila['otr_actaFi'] ?? '';
                ?>
                <div class="card card-especial-tres mx-2 mb-4 shadow">
                    <div class="card-header py-3 text-center text-white ">
                        <i class="bi bi-file-earmark-binary fs-4 me-2 "></i>
                        Documento N° <?= h($otr_id) ?>
                    </div>
                    <div class="card-body">
                        <div class="row p-2 justify-content-center">
                            <!-- <p><strong>Tipo de documento:</strong> <?= h($otr_tipo) ?></p>
                            <p><strong>Fecha de Inicio Otro Sí:</strong> <?= h($otr_fecha) ?></p>
                            <p><strong>Fecha de Final Otro Sí:</strong> <?= h($otr_fecha_final) ?></p>
                            <p><strong>Cargo:</strong> <?= h($otr_cargo) ?></p>
                            <p><strong>Proyecto:</strong> <?= h($otr_proyecto) ?></p>
                            <p><strong>Salario:</strong> $<?= h($salario_fmt) ?></p> -->
                            <div class="col-md-4 p-1 px-2 my-1">
                                <label for="con_lugar_nacimiento" class="form-label fw-bold" style="font-size:0.9em;">Tipo de documento:</label>
                                <div class="input-group ">
                                    <span class="input-group-text shadow-sm"><i class="bi bi-file-earmark-richtext"></i></span>
                                    <input class="form-control" name="con_lugar_nacimiento" id="con_lugar_nacimiento" value="<?php echo $otr_tipo; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-4 p-1 px-2 my-1">
                                <label for="con_lugar_nacimiento" class="form-label fw-bold" style="font-size:0.9em;">Fecha de inicio del otro si:</label>
                                <div class="input-group ">
                                    <span class="input-group-text shadow-sm"><i class="bi bi-file-earmark-richtext"></i></span>
                                    <input class="form-control" name="con_lugar_nacimiento" id="con_lugar_nacimiento" value="<?php echo $otr_fecha; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-4 p-1 px-2 my-1">
                                <label for="con_lugar_nacimiento" class="form-label fw-bold" style="font-size:0.9em;">Fecha de finalización del otro si:</label>
                                <div class="input-group ">
                                    <span class="input-group-text shadow-sm"><i class="bi bi-file-earmark-richtext"></i></span>
                                    <input class="form-control" name="con_lugar_nacimiento" id="con_lugar_nacimiento" value="<?php echo $otr_fecha_final; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-4 p-1 px-2 my-1">
                                <label for="con_lugar_nacimiento" class="form-label fw-bold" style="font-size:0.9em;">Cargo estipulado en el otro si:</label>
                                <div class="input-group ">
                                    <span class="input-group-text shadow-sm"><i class="bi bi-file-earmark-richtext"></i></span>
                                    <input class="form-control" name="con_lugar_nacimiento" id="con_lugar_nacimiento" value="<?php echo $otr_cargo; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-4 p-1 px-2 my-1">
                                <label for="con_lugar_nacimiento" class="form-label fw-bold" style="font-size:0.9em;">Proyecto estipulado en el otro si:</label>
                                <div class="input-group ">
                                    <span class="input-group-text shadow-sm"><i class="bi bi-file-earmark-richtext"></i></span>
                                    <input class="form-control" name="con_lugar_nacimiento" id="con_lugar_nacimiento" value="<?php echo $otr_proyecto; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-4 p-1 px-2 my-1">
                                <label for="con_lugar_nacimiento" class="form-label fw-bold" style="font-size:0.9em;">Salario estipulado en el otro si:</label>
                                <div class="input-group ">
                                    <span class="input-group-text shadow-sm"><i class="bi bi-file-earmark-richtext"></i></span>
                                    <input class="form-control" name="con_lugar_nacimiento" id="con_lugar_nacimiento" value="<?php echo $salario_fmt; ?>" readonly>
                                </div>
                            </div>



                            <div class="col-12  my-4" style="border-bottom:2px dashed #002f557a"></div>

                            <div class="col-12">
                                <h6 class="fw-bold p-2 text-white text-center w-25 rounded-3" style="background-color: #002F55;">Documento Adjuntos</h6>
                            </div>
                            <?php renderDocumento($otr_otrosi, 'Documento Otro Sí'); ?>
                            <?php renderDocumento($otr_cumplimiento, 'Documento de Cumplimiento'); ?>
                            <?php renderDocumento($otr_acta, 'Acta Inicial'); ?>
                            <?php renderDocumento($otr_actaFi, 'Acta Final'); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="modal fade" id="modalPDF" tabindex="-1" style="z-index: 999999;">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; height: 92vh; display:flex; flex-direction:column;">

            <!-- Header -->
            <div class="modal-header">
                <h7 class="modal-title fw-bold" style="color: #002F55;">Vista previa del documento</h7>
                <button type="button" class="btn-close text-white" style="background-color: #002F55;" data-bs-dismiss="modal"></button>
            </div>

            <!-- Cuerpo flexible -->
            <div class="modal-body p-0" style="flex:1; overflow:hidden;">
                <iframe id="iframePDF" width="100%" height="100%" style="border:0;"></iframe>
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn text-white btn-sm" style="background-color: #002F55;" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    function abrirModalInforme(rutaPDF) {
        const iframe = document.getElementById('iframePDF');
        iframe.src = rutaPDF;

        const modal = new bootstrap.Modal(document.getElementById('modalPDF'));
        modal.show();
    }

    document.getElementById('modalPDF')
        ?.addEventListener('hidden.bs.modal', () => {
            document.getElementById('iframePDF').src = '';
        });
</script>