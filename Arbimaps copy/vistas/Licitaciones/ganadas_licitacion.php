<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require __DIR__ . '/../../../conexion.php';

$radicado = $_GET['radicado'] ?? '';
if ($radicado === '') {
    echo "<div class='alert alert-danger'>Radicado no válido.</div>";
    exit;
}

$stmt = $mysqli->prepare("SELECT lc_radicado, lc_entidad, lc_nombre_licitacion, lc_estado
                          FROM licitaciones WHERE lc_radicado = ? LIMIT 1");
$stmt->bind_param("s", $radicado);
$stmt->execute();
$info = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$info) {
    echo "<div class='alert alert-danger'>No se encontró la licitación.</div>";
    exit;
}
?>

<div class="container-fluid">
    <div class="my-4">
        <h4 class="fw-bold" style="color:#002F55;">Licitación Ganada</h4>
        <div class="text-muted">
            <b>Radicado:</b> <?php echo htmlspecialchars($info['lc_radicado']); ?> |
            <b>Entidad:</b> <?php echo htmlspecialchars($info['lc_entidad'] ?? ''); ?> |
            <b>Estado actual:</b> <?php echo htmlspecialchars($info['lc_estado'] ?? ''); ?>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">


            <form id="formCierre" action="/arbimaps/Arbimaps/vistas/Licitaciones/procesar_cierre_ganadas.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="radicado" value="<?php echo htmlspecialchars($radicado); ?>">

                <div class="row g-3">

                <div class="col-12 col-lg-6  p-1 px-3 my-3">
                    <label for="numero_poliza" class="form-label fw-bold" >N° Poliza</label>
                    <div class="input-group ">
                        <span class="input-group-text shadow-sm"><i class="bi bi-bank"></i></span>
                        <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="numero_poliza" name="numero_poliza"
                            placeholder="ingresa el numero de proceso" name=""
                            required>
                    </div>
                </div>


                <div class="col-12 col-lg-6  p-1 px-3 my-3">
                    <label for="poliza_doc" class="form-label fw-bold">Poliza</label>
                    <div class="input-group mb-1 shadow-sm">
                        <label class="input-group-text" for="poliza_doc" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
                        <input type="file" class="form-control" style="font-size:0.8em;" id="poliza_doc" name="poliza_doc" required>
                    </div>
                    <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                </div>

                <div class="col-12 col-lg-6  p-1 px-3 my-3">
                    <label for="fecha_inicio" class="form-label fw-bold" >Fecha de Inicio</label>
                    <div class="input-group ">
                        <span class="input-group-text"><i class="bi bi-calendar2-event"></i></span>
                        <input type="date" class="form-control shadow-sm" style="font-size:0.9em;" id="fecha_inicio" name="fecha_inicio" aria-label="fecha" aria-describedby="basic-addon1" required>
                    </div>
                </div>

                <div class="col-12 col-lg-6  p-1 px-3 my-3">
                    <label for="acta_inicio_doc" class="form-label fw-bold">Acta de Inicio</label>
                    <div class="input-group mb-1 shadow-sm">
                        <label class="input-group-text" for="acta_inicio_doc" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
                        <input type="file" class="form-control" style="font-size:0.8em;" id="acta_inicio_doc" name="acta_inicio_doc" required>>
                    </div>
                    <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                </div>

                <div class="d-flex gap-2">
                    <button id="btnGuardar" type="submit" class="btn btn-danger">
                        <i class="bi bi-save2 me-1"></i> Guardar y Cerrar
                    </button>

                    <a href="index.php?page=licitaciones/consultar_licitaciones" class="btn btn-secondary">
                        Cancelar
                    </a>
                </div>

            </form>

        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formCierre');
        const btnGuardar = document.getElementById('btnGuardar');

        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(form);
            btnGuardar.disabled = true;

            fetch(form.action, {
                    method: 'POST',
                    body: formData
                })
                .then(async (res) => {
                    const text = await res.text();
                    let data;

                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'El servidor no devolvió JSON válido.'
                        });
                        return;
                    }

                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Guardado',
                            text: data.message || 'Cierre guardado con éxito.',
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            window.location.href = "/arbimaps/Arbimaps/index.php?page=licitaciones/consultar_licitaciones";
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Ocurrió un error.'
                        });
                    }
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de red',
                        text: 'No se pudo enviar la información.'
                    });
                })
                .finally(() => {
                    btnGuardar.disabled = false;
                });
        });
    });
</script>