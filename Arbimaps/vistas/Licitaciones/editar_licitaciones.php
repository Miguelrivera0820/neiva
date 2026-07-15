<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require __DIR__ . '/../../../conexion.php';

$radicado = $_GET['radicado'] ?? '';
if ($radicado === '') {
    echo "<div class='alert alert-danger'>Radicado no válido</div>";
    exit;
}

$sql = "SELECT *
        FROM licitaciones
        WHERE lc_radicado = ?
        LIMIT 1";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $radicado);
$stmt->execute();
$res = $stmt->get_result();
$lc = $res->fetch_assoc();
$stmt->close();

if (!$lc) {
    echo "<div class='alert alert-warning'>No se encontró la licitación</div>";
    exit;
}
?>

<div class="container-fluid px-3">

  <div class="text-center my-4">
    <h1 class="h3 mb-0" style="color:#002F55"><b>EDITAR LICITACIÓN</b></h1>
    <small class="text-muted">Radicado: <b><?php echo htmlspecialchars($lc['lc_radicado']); ?></b></small>
  </div>

  <div class="card rounded-4 shadow-lg mx-3 border-0 my-3">
    <div class="card-body p-3 mb-3">

      <form id="formLicitaciones"
            action="/arbimaps/Arbimaps/vistas/licitaciones/actualizar_licitacion.php"
            method="POST"
            enctype="multipart/form-data">

        <!-- ✅ Enviamos el radicado oculto -->
        <input type="hidden" name="lc_radicado" value="<?php echo htmlspecialchars($lc['lc_radicado']); ?>">

        <div class="row g-3">

          <div class="col-md-4 p-2">
            <label class="form-label" style="font-size:0.9em;"><b>Tipo de Entidad</b></label>
            <div class="input-group shadow-sm">
              <label class="input-group-text"><i class="bi bi-backpack2"></i></label>
              <select class="form-select" style="font-size:0.9em;" id="lc_tipo_entidad" name="lc_tipo_entidad" required>
                <option value="PUBLICA" <?php echo ($lc['lc_tipo_entidad'] ?? '') === 'PUBLICA' ? 'selected' : ''; ?>>PUBLICA</option>
                <option value="PRIVADA" <?php echo ($lc['lc_tipo_entidad'] ?? '') === 'PRIVADA' ? 'selected' : ''; ?>>PRIVADA</option>
              </select>
            </div>
          </div>

          <div class="col-md-4 p-2">
            <label class="form-label" style="font-size:0.9em;"><b>Procesos</b></label>
            <div class="input-group shadow-sm">
              <label class="input-group-text"><i class="bi bi-file-earmark-medical"></i></label>
              <select class="form-select" style="font-size:0.9em;" id="lc_procesos" name="lc_procesos" required>
                <?php
                $opciones = [
                  "LICITACION_PUEBLICA" => "LICITACION PUBLICA",
                  "SELECCION_ABREVIADA_DE_MENOR_CUANTIA" => "SELECCION ABREVIADA DE MENOR CUANTIA",
                  "REGIMEN_ESPECIAL" => "REGIMEN ESPECIAL",
                  "ACUERDO_MARCO" => "ACUERDO MARCO",
                  "CONCURSO_DE_MERITOS_ABIERTOS" => "CONCURSO DE MERITOS ABIERTOS",
                  "CONVOCATORIAS" => "CONVOCATORIAS",
                ];
                $sel = $lc['lc_procesos'] ?? '';
                foreach ($opciones as $val => $txt) {
                    $selected = ($sel === $val) ? 'selected' : '';
                    echo "<option value='".htmlspecialchars($val)."' $selected>".htmlspecialchars($txt)."</option>";
                }
                ?>
              </select>
            </div>
          </div>

          <div class="col-md-4 p-1 px-2 my-2">
            <label class="form-label fw-bold" style="font-size:0.9em;">Municipio</label>
            <div class="input-group">
              <span class="input-group-text shadow-sm"><i class="bi bi-bank"></i></span>
              <input type="text" class="form-control shadow-sm" style="font-size:0.9em;"
                     name="lc__municipio" required
                     value="<?php echo htmlspecialchars($lc['lc__municipio'] ?? ''); ?>">
            </div>
          </div>

          <div class="col-md-4 p-1 px-2 my-2">
            <label class="form-label fw-bold" style="font-size:0.9em;">Departamento</label>
            <div class="input-group">
              <span class="input-group-text shadow-sm"><i class="bi bi-bank"></i></span>
              <input type="text" class="form-control shadow-sm" style="font-size:0.9em;"
                     name="lc_departamento" required
                     value="<?php echo htmlspecialchars($lc['lc_departamento'] ?? ''); ?>">
            </div>
          </div>

          <div class="col-md-4 p-1 px-2 my-2">
            <label class="form-label fw-bold" style="font-size:0.9em;">Entidad</label>
            <div class="input-group">
              <span class="input-group-text shadow-sm"><i class="bi bi-bank"></i></span>
              <input type="text" class="form-control shadow-sm" style="font-size:0.9em;"
                     name="lc_entidad" required
                     value="<?php echo htmlspecialchars($lc['lc_entidad'] ?? ''); ?>">
            </div>
          </div>

          <div class="col-md-4 p-1 px-2 my-2">
            <label class="form-label fw-bold" style="font-size:0.9em;">Valor Contrato</label>
            <div class="input-group">
              <span class="input-group-text shadow-sm"><i class="bi bi-bank"></i></span>

              <!-- visible -->
              <input type="text" class="form-control shadow-sm" style="font-size:0.9em;"
                     id="lc_valor_visible"
                     placeholder="$ 0"
                     inputmode="numeric"
                     autocomplete="off"
                     value="<?php echo htmlspecialchars($lc['lc_valor'] ?? ''); ?>"
                     oninput="formatCurrency(this, 'lc_valor')">

              <!-- hidden real -->
              <input type="hidden" id="lc_valor" name="lc_valor"
                     value="<?php echo htmlspecialchars($lc['lc_valor'] ?? ''); ?>">
            </div>
          </div>

          <div class="col-md-4 p-1 px-2 my-2">
            <label class="form-label fw-bold" style="font-size:0.9em;">N° Proceso</label>
            <div class="input-group">
              <span class="input-group-text shadow-sm"><i class="bi bi-bank"></i></span>
              <input type="text" class="form-control shadow-sm" style="font-size:0.9em;"
                     name="lc_numero_proceso" required
                     value="<?php echo htmlspecialchars($lc['lc_numero_proceso'] ?? ''); ?>">
            </div>
          </div>

          <div class="col-md-4 p-1 px-2 my-2">
            <label class="form-label fw-bold" style="font-size:0.9em;">Nombre de licitación/Objeto</label>
            <div class="input-group">
              <span class="input-group-text shadow-sm"><i class="bi bi-bank"></i></span>
              <input type="text" class="form-control shadow-sm" style="font-size:0.9em;"
                     name="lc_nombre_licitacion" required
                     value="<?php echo htmlspecialchars($lc['lc_nombre_licitacion'] ?? ''); ?>">
            </div>
          </div>

          <div class="col-md-4 p-1 px-2 my-2">
            <label class="form-label fw-bold" style="font-size:0.9em;">Nombre Proyecto</label>
            <div class="input-group">
              <span class="input-group-text shadow-sm"><i class="bi bi-bank"></i></span>
              <input type="text" class="form-control shadow-sm" style="font-size:0.9em;"
                     name="lc_proyecto" required
                     value="<?php echo htmlspecialchars($lc['lc_proyecto'] ?? ''); ?>">
            </div>
          </div>

          <div class="col-md-4 p-1 px-2 my-2">
            <label class="form-label fw-bold" style="font-size:0.9em;">Fecha Apertura</label>
            <div class="input-group">
              <span class="input-group-text shadow-sm"><i class="bi bi-calendar2-event"></i></span>
              <input type="date" class="form-control shadow-sm" style="font-size:0.9em;"
                     name="lc_fecha_apertura" required
                     value="<?php echo htmlspecialchars($lc['lc_fecha_apertura'] ?? ''); ?>">
            </div>
          </div>

          <div class="col-md-4 p-1 px-2 my-2">
            <label class="form-label fw-bold" style="font-size:0.9em;">Fecha Presentación</label>
            <div class="input-group">
              <span class="input-group-text shadow-sm"><i class="bi bi-calendar2-event"></i></span>
              <input type="date" class="form-control shadow-sm" style="font-size:0.9em;"
                     name="lc_fecha_presentacion" required
                     value="<?php echo htmlspecialchars($lc['lc_fecha_presentacion'] ?? ''); ?>">
            </div>
          </div>

          <div class="col-12 mt-3 d-flex gap-2">
            <button type="submit" class="btn text-white" id="btn_enviar" style="background:#003B66;">
              <b>Guardar cambios</b>
            </button>

            <a href="index.php?page=licitaciones/consultar_licitaciones" class="btn btn-secondary">
              Volver
            </a>
          </div>

        </div>
      </form>

    </div>
  </div>
</div>

<script>
function formatCurrency(input, hiddenId) {
    let value = input.value.replace(/\D/g, '');
    if (value === '') {
        input.value = '';
        document.getElementById(hiddenId).value = '';
        return;
    }
    let number = parseInt(value, 10);
    input.value = new Intl.NumberFormat('es-CO', {
        style: 'currency', currency: 'COP', minimumFractionDigits: 0
    }).format(number);
    document.getElementById(hiddenId).value = number;
}
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formLicitaciones');
    const submitButton = document.getElementById('btn_enviar');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!form.checkValidity()) {
            Swal.fire({ icon:'warning', title:'Campos incompletos', text:'Completa los campos requeridos.' });
            form.reportValidity();
            return;
        }

        Swal.fire({
            icon: 'question',
            title: '¿Actualizar licitación?',
            text: 'Se guardarán los cambios.',
            showCancelButton: true,
            confirmButtonText: 'Actualizar',
            cancelButtonText: 'Cancelar'
        }).then((r) => {
            if (!r.isConfirmed) return;

            submitButton.disabled = true;
            const formData = new FormData(form);

            fetch(form.action, { method:'POST', body: formData })
            .then(res => res.text())
            .then(text => {
                let data;
                try { data = JSON.parse(text); }
                catch { throw new Error('Respuesta no es JSON'); }

                if (data.success) {
                    Swal.fire({ icon:'success', title:'Actualizado', text: data.message || 'Cambios guardados' })
                    .then(() => window.location.href = 'index.php?page=licitaciones/consultar_licitaciones');
                } else {
                    Swal.fire({ icon:'error', title:'Error', text: data.message || 'No se pudo actualizar' });
                }
            })
            .catch(() => Swal.fire({ icon:'error', title:'Error', text:'Error de servidor o red' }))
            .finally(() => submitButton.disabled = false);
        });
    });
});
</script>
