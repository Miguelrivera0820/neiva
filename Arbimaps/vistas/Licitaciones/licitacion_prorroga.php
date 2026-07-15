<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require __DIR__ . '/../../../conexion.php';

$radicado = trim($_GET['radicado'] ?? '');
if ($radicado === '') {
  echo "<div class='alert alert-danger'>Radicado no válido.</div>";
  exit;
}

$stmt = $mysqli->prepare("
  SELECT
    lc_tipo_entidad,
    lc_proceso,
    lc_municipio,
    lc_departamento,
    lc_entidad,
    lc_valor,
    lc_numero_proceso,
    lc_nombre_licitacion,
    lc_proyecto,
    lc_fecha_apertura,
    lc_fecha_presentacion,
    lc_radicado,
    lc_estado
  FROM licitaciones
  WHERE lc_radicado = ?
  LIMIT 1
");
$stmt->bind_param("s", $radicado);
$stmt->execute();
$info = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$info) {
  echo "<div class='alert alert-danger'>No se encontró la licitación.</div>";
  exit;
}

// Fechas para input type="date"
$fechaApertura = !empty($info['lc_fecha_apertura']) ? date('Y-m-d', strtotime($info['lc_fecha_apertura'])) : '';
$fechaPresentacion = !empty($info['lc_fecha_presentacion']) ? date('Y-m-d', strtotime($info['lc_fecha_presentacion'])) : '';
?>

<div class="container-fluid">
  <div class="my-4">
    <h4 class="fw-bold" style="color:#002F55;">Cerrar Licitación</h4>
    <div class="text-muted">
      <b>Radicado:</b> <?php echo htmlspecialchars($info['lc_radicado']); ?> |
      <b>Entidad:</b> <?php echo htmlspecialchars($info['lc_entidad'] ?? ''); ?> |
      <b>Estado actual:</b> <?php echo htmlspecialchars($info['lc_estado'] ?? ''); ?>
    </div>
  </div>

  <div class="card shadow">
    <div class="card-body">

      <form id="formCierre" action="<?= neiva_app_url('Arbimaps/vistas/Licitaciones/procesar_cierre_prorroga.php') ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="radicado" value="<?php echo htmlspecialchars($radicado); ?>">

        <div class="row g-3">

          <div class="col-md-4 p-2">
            <label for="lc_tipo_entidad" class="form-label" style="font-size:0.9em;"><b>Tipo de Entidad</b></label>
            <div class="input-group shadow-sm">
              <label class="input-group-text" for="lc_tipo_entidad"><i class="bi bi-backpack2"></i></label>
              <select class="form-select" style="font-size:0.9em;" id="lc_tipo_entidad" name="lc_tipo_entidad">
                <option value="" disabled <?php echo empty($info['lc_tipo_entidad']) ? 'selected' : ''; ?>>Selecciona</option>
                <option value="PUBLICA" <?php echo ($info['lc_tipo_entidad'] ?? '') === 'PUBLICA' ? 'selected' : ''; ?>>PUBLICA</option>
                <option value="PRIVADA" <?php echo ($info['lc_tipo_entidad'] ?? '') === 'PRIVADA' ? 'selected' : ''; ?>>PRIVADA</option>
              </select>
            </div>
          </div>

          <div class="col-md-4 p-2">
            <label for="lc_proceso" class="form-label" style="font-size:0.9em;"><b>Procesos</b></label>
            <div class="input-group shadow-sm">
              <label class="input-group-text" for="lc_proceso"><i class="bi bi-file-earmark-medical"></i></label>
              <select class="form-select" style="font-size:0.9em;" id="lc_proceso" name="lc_proceso" required>
                <option value="" <?php echo empty($info['lc_proceso']) ? 'selected' : ''; ?>>SELECCIONE</option>
                <option value="LICITACION_PUEBLICA" <?php echo ($info['lc_proceso'] ?? '') === 'LICITACION_PUEBLICA' ? 'selected' : ''; ?>>LICITACION PUBLICA</option>
                <option value="SELECCION_ABREVIADA_DE_MENOR_CUANTIA" <?php echo ($info['lc_proceso'] ?? '') === 'SELECCION_ABREVIADA_DE_MENOR_CUANTIA' ? 'selected' : ''; ?>>SELECCION ABREVIADA DE MENOR CUANTIA</option>
                <option value="REGIMEN_ESPECIAL" <?php echo ($info['lc_proceso'] ?? '') === 'REGIMEN_ESPECIAL' ? 'selected' : ''; ?>>REGIMEN ESPECIAL</option>
                <option value="ACUERDO_MARCO" <?php echo ($info['lc_proceso'] ?? '') === 'ACUERDO_MARCO' ? 'selected' : ''; ?>>ACUERDO MARCO</option>
                <option value="CONCURSO_DE_MERITOS_ABIERTOS" <?php echo ($info['lc_proceso'] ?? '') === 'CONCURSO_DE_MERITOS_ABIERTOS' ? 'selected' : ''; ?>>CONCURSO DE MERITOS ABIERTOS</option>
                <option value="CONVOCATORIAS" <?php echo ($info['lc_proceso'] ?? '') === 'CONVOCATORIAS' ? 'selected' : ''; ?>>CONVOCATORIAS</option>
              </select>
            </div>
          </div>

          <div class="col-md-4 p-1 px-2 my-2">
            <label for="lc_municipio" class="form-label fw-bold" style="font-size:0.9em;">Municipio</label>
            <div class="input-group">
              <span class="input-group-text shadow-sm"><i class="bi bi-bank"></i></span>
              <input type="text" class="form-control shadow-sm" style="font-size:0.9em;"
                     id="lc_municipio" name="lc_municipio"
                     value="<?php echo htmlspecialchars($info['lc_municipio'] ?? ''); ?>" required>
            </div>
          </div>

          <div class="col-md-4 p-1 px-2 my-2">
            <label for="lc_departamento" class="form-label fw-bold" style="font-size:0.9em;">Departamento</label>
            <div class="input-group">
              <span class="input-group-text shadow-sm"><i class="bi bi-bank"></i></span>
              <input type="text" class="form-control shadow-sm" style="font-size:0.9em;"
                     id="lc_departamento" name="lc_departamento"
                     value="<?php echo htmlspecialchars($info['lc_departamento'] ?? ''); ?>" required>
            </div>
          </div>

          <div class="col-md-4 p-1 px-2 my-2">
            <label for="lc_entidad" class="form-label fw-bold" style="font-size:0.9em;">Entidad</label>
            <div class="input-group">
              <span class="input-group-text shadow-sm"><i class="bi bi-bank"></i></span>
              <input type="text" class="form-control shadow-sm" style="font-size:0.9em;"
                     id="lc_entidad" name="lc_entidad"
                     value="<?php echo htmlspecialchars($info['lc_entidad'] ?? ''); ?>" required>
            </div>
          </div>

          <div class="col-md-4 p-1 px-2 my-2">
            <label for="lc_valor_display" class="form-label fw-bold" style="font-size:0.9em;">Valor Contrato</label>
            <div class="input-group">
              <span class="input-group-text shadow-sm"><i class="bi bi-bank"></i></span>
              <input type="text" class="form-control shadow-sm" style="font-size:0.9em;"
                     id="lc_valor_display" placeholder="$ 0" inputmode="numeric" autocomplete="off" required
                     oninput="formatCurrency(this, 'lc_valor')">
              <input type="hidden" id="lc_valor" name="lc_valor"
                     value="<?php echo htmlspecialchars($info['lc_valor'] ?? ''); ?>">
            </div>
          </div>

          <div class="col-md-4 p-1 px-2 my-2">
            <label for="lc_numero_proceso" class="form-label fw-bold" style="font-size:0.9em;">N° Proceso</label>
            <div class="input-group">
              <span class="input-group-text shadow-sm"><i class="bi bi-bank"></i></span>
              <input type="text" class="form-control shadow-sm" style="font-size:0.9em;"
                     id="lc_numero_proceso" name="lc_numero_proceso"
                     value="<?php echo htmlspecialchars($info['lc_numero_proceso'] ?? ''); ?>" required>
            </div>
          </div>

          <div class="col-md-4 p-1 px-2 my-2">
            <label for="lc_nombre_licitacion" class="form-label fw-bold" style="font-size:0.9em;">Nombre licitación/Objeto</label>
            <div class="input-group">
              <span class="input-group-text shadow-sm"><i class="bi bi-bank"></i></span>
              <input type="text" class="form-control shadow-sm" style="font-size:0.9em;"
                     id="lc_nombre_licitacion" name="lc_nombre_licitacion"
                     value="<?php echo htmlspecialchars($info['lc_nombre_licitacion'] ?? ''); ?>" required>
            </div>
          </div>

          <div class="col-md-4 p-1 px-2 my-2">
            <label for="lc_proyecto" class="form-label fw-bold" style="font-size:0.9em;">Nombre Proyecto</label>
            <div class="input-group">
              <span class="input-group-text shadow-sm"><i class="bi bi-bank"></i></span>
              <input type="text" class="form-control shadow-sm" style="font-size:0.9em;"
                     id="lc_proyecto" name="lc_proyecto"
                     value="<?php echo htmlspecialchars($info['lc_proyecto'] ?? ''); ?>" required>
            </div>
          </div>

          <div class="col-md-4 p-1 px-2 my-2">
            <label for="lc_fecha_apertura" class="form-label fw-bold" style="font-size:0.9em;">Fecha Apertura</label>
            <div class="input-group">
              <span class="input-group-text shadow-sm"><i class="bi bi-calendar2-event"></i></span>
              <input type="date" class="form-control shadow-sm" style="font-size:0.9em;"
                     id="lc_fecha_apertura" name="lc_fecha_apertura"
                     value="<?php echo htmlspecialchars($fechaApertura); ?>" required>
            </div>
          </div>

          <div class="col-md-4 p-1 px-2 my-2">
            <label for="lc_fecha_presentacion" class="form-label fw-bold" style="font-size:0.9em;">Fecha Presentación</label>
            <div class="input-group">
              <span class="input-group-text shadow-sm"><i class="bi bi-calendar2-event"></i></span>
              <input type="date" class="form-control shadow-sm" style="font-size:0.9em;"
                     id="lc_fecha_presentacion" name="lc_fecha_presentacion"
                     value="<?php echo htmlspecialchars($fechaPresentacion); ?>" required>
            </div>
          </div>

          <!-- ========== ADENDAS / CONDICIONES / PROYECTO (EXISTENTES + NUEVO) ========== -->

          <div class="col-12 col-lg-4 p-1 px-3 my-3">
            <label class="form-label fw-bold">Adendas</label>
            <div class="input-group mb-2 shadow-sm">
              <label class="input-group-text" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
              <input type="file" class="form-control" style="font-size:0.8em;" id="lc_adendas" name="lc_adendas" accept="application/pdf">
            </div>

            <div class="row g-2">
              <div class="col-12 col-md-6" id="adendasExistentes"></div>
              <div class="col-12 col-md-6" id="adendasNuevo"></div>
            </div>
          </div>

          <div class="col-12 col-lg-4 p-1 px-3 my-3">
            <label class="form-label fw-bold">Pliego Definitivo</label>
            <div class="input-group mb-2 shadow-sm">
              <label class="input-group-text" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
              <input type="file" class="form-control" style="font-size:0.8em;" id="lc_condiciones" name="lc_condiciones" accept="application/pdf">
            </div>

            <div class="row g-2">
              <div class="col-12 col-md-6" id="condicionesExistentes"></div>
              <div class="col-12 col-md-6" id="condicionesNuevo"></div>
            </div>
          </div>

          <div class="col-12 col-lg-4 p-1 px-3 my-3">
            <label class="form-label fw-bold">Proyecto Pliego</label>
            <div class="input-group mb-2 shadow-sm">
              <label class="input-group-text" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
              <input type="file" class="form-control" style="font-size:0.8em;" id="lc_condiciones_proyecto" name="lc_condiciones_proyecto" accept="application/pdf">
            </div>

            <div class="row g-2">
              <div class="col-12 col-md-6" id="proyectoExistentes"></div>
              <div class="col-12 col-md-6" id="proyectoNuevo"></div>
            </div>
          </div>

          <!-- ========== OTROS (NUEVOS + EXISTENTES SIN TITULO) ========== -->

          <div class="col-12 my-4">
            <label class="form-label fw-bold text-center d-block">Documentos del proceso (Otros)</label>

            <div class="drop-zone" id="dropZone"
                 style="border:2px dashed #cfd8dc; border-radius:12px; padding:18px; cursor:pointer;">

              <div class="text-center mb-3">
                <i class="bi bi-cloud-upload fs-1 text-primary"></i>
                <p class="mt-2 mb-1">Haz clic o arrastra documentos aquí</p>
                <small class="text-muted">Solo archivos PDF (máx. 20 MB cada uno)</small>
              </div>

              <div class="fw-bold mb-2">Nuevos (por subir)</div>
              <div class="row g-3" id="previewNuevosOtros"></div>

              <hr class="my-3">

              <div class="row g-3" id="otrosExistentes"></div>

              <input type="file" id="lc_otros_doc" name="lc_otros_doc[]" multiple accept="application/pdf" hidden>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Motivo del cierre</label>
            <textarea name="pro_motivo" class="form-control" rows="4" required placeholder="Describe el motivo del cierre..."></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Documento soporte (PDF)</label>
            <input type="file" name="pro_doc" class="form-control" required accept="application/pdf">
            <small class="text-muted">Solo PDF - máximo 20MB</small>
          </div>

          <div class="d-flex gap-2">
            <button id="btnGuardar" type="submit" class="btn btn-danger">
              <i class="bi bi-save2 me-1"></i> Guardar y Cerrar
            </button>

            <a href="index.php?page=licitaciones/consultar_licitaciones" class="btn btn-secondary">Cancelar</a>
          </div>

        </div>
      </form>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const RADICADO = <?php echo json_encode($radicado); ?>;

  // Formateo valor contrato al cargar
  const hidden = document.getElementById("lc_valor");
  const display = document.getElementById("lc_valor_display");
  if (hidden && display && hidden.value) {
    display.value = new Intl.NumberFormat('es-CO', {
      style: 'currency',
      currency: 'COP',
      maximumFractionDigits: 0
    }).format(hidden.value);
  }

  function escapeHtml(str) {
    return String(str)
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }

  function renderNuevoFile(containerId, file) {
    const box = document.getElementById(containerId);
    if (!box) return;

    if (!file) { box.innerHTML = ''; return; }

    box.innerHTML = `
      <div class="card shadow-sm h-100 border border-success">
        <div class="card-body">
          <div class="fw-bold" style="font-size:0.9em;">
            <i class="bi bi-file-earmark-pdf text-danger me-1"></i>
            ${escapeHtml(file.name)}
          </div>
          <div class="text-muted mt-2" style="font-size:0.75em;">Nuevo (pendiente por guardar)</div>
        </div>
      </div>
    `;
  }

  function cardExistente(doc) {
    return `
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <div class="fw-bold" style="font-size:0.9em;">
            <i class="bi bi-file-earmark-pdf text-danger me-1"></i>
            ${escapeHtml(doc.name || 'Documento')}
          </div>
          <div class="d-flex gap-2 mt-2">
            <a class="btn btn-sm btn-outline-primary" href="${doc.url}" target="_blank">Ver</a>
          </div>
          <div class="text-muted mt-2" style="font-size:0.75em;">Documento existente</div>
        </div>
      </div>
    `;
  }

  function appendExistente(containerId, doc, colClass) {
    const cont = document.getElementById(containerId);
    if (!cont) return;

    const col = document.createElement('div');
    col.className = colClass;
    col.innerHTML = cardExistente(doc);
    cont.appendChild(col);
  }

  // Preview nuevos (adenda/condiciones/proyecto)
  const inpAdendas = document.getElementById('lc_adendas');
  if (inpAdendas) inpAdendas.addEventListener('change', () => renderNuevoFile('adendasNuevo', inpAdendas.files && inpAdendas.files[0]));

  const inpCond = document.getElementById('lc_condiciones');
  if (inpCond) inpCond.addEventListener('change', () => renderNuevoFile('condicionesNuevo', inpCond.files && inpCond.files[0]));

  const inpProy = document.getElementById('lc_condiciones_proyecto');
  if (inpProy) inpProy.addEventListener('change', () => renderNuevoFile('proyectoNuevo', inpProy.files && inpProy.files[0]));

  // Pintar EXISTENTES en su lugar por doc_tipo
  ['adendasExistentes','condicionesExistentes','proyectoExistentes','otrosExistentes'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.innerHTML = '';
  });

  if (RADICADO) {
    fetch(`<?= neiva_app_url('Arbimaps/vistas/Licitaciones/get_documentos_licitacion.php?radicado=') ?>${encodeURIComponent(RADICADO)}`)
      .then(r => r.json())
      .then(data => {
        if (!data.success) return;
        if (!data.docs || !data.docs.length) return;

        data.docs.forEach(doc => {
          const tipo = (doc.tipo || '').toUpperCase().trim();

          if (tipo === 'ADENDA') {
            appendExistente('adendasExistentes', doc, 'col-12');
          } else if (tipo === 'CONDICIONES') {
            appendExistente('condicionesExistentes', doc, 'col-12');
          } else if (tipo === 'PROYECTO') {
            appendExistente('proyectoExistentes', doc, 'col-12');
          } else {
            // OTROS
            appendExistente('otrosExistentes', doc, 'col-12 col-md-6 col-lg-4');
          }
        });
      })
      .catch(err => console.error("ERROR pintando existentes =>", err));
  }

  // NUEVOS (OTROS)
  const inputOtros = document.getElementById('lc_otros_doc');
  const previewNuevosOtros = document.getElementById('previewNuevosOtros');
  const dropZone = document.getElementById('dropZone');

  if (dropZone && inputOtros && previewNuevosOtros) {
    dropZone.addEventListener('click', (e) => {
      if (e.target.closest('a')) return;
      inputOtros.click();
    });

    inputOtros.addEventListener('change', () => {
      previewNuevosOtros.innerHTML = '';
      if (!inputOtros.files || inputOtros.files.length === 0) return;

      [...inputOtros.files].forEach(file => {
        const col = document.createElement('div');
        col.className = 'col-12 col-md-6 col-lg-4';
        col.innerHTML = `
          <div class="card shadow-sm h-100 border border-success">
            <div class="card-body">
              <div class="fw-bold" style="font-size:0.9em;">
                <i class="bi bi-file-earmark-pdf text-danger me-1"></i>
                ${escapeHtml(file.name)}
              </div>
              <div class="text-muted mt-2" style="font-size:0.75em;">Nuevo (pendiente por guardar)</div>
            </div>
          </div>
        `;
        previewNuevosOtros.appendChild(col);
      });
    });

    dropZone.addEventListener('dragover', (e) => { e.preventDefault(); });
    dropZone.addEventListener('drop', (e) => {
      e.preventDefault();

      const files = [...(e.dataTransfer.files || [])].filter(f => f.type === 'application/pdf');
      if (!files.length) return;

      const dt = new DataTransfer();
      files.forEach(f => dt.items.add(f));
      inputOtros.files = dt.files;
      inputOtros.dispatchEvent(new Event('change'));
    });
  }

  // SUBMIT con fetch
  const form = document.getElementById('formCierre');
  const btnGuardar = document.getElementById('btnGuardar');
  if (!form) return;

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(form);
    btnGuardar.disabled = true;

    fetch(form.action, { method: 'POST', body: formData })
      .then(async (res) => {
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); }
        catch {
          Swal.fire({ icon: 'error', title: 'Error', text: 'El servidor no devolvió JSON válido.' });
          return;
        }

        if (data.success) {
          Swal.fire({ icon: 'success', title: 'Guardado', text: data.message || 'Guardado con éxito.' })
            .then(() => window.location.href = "<?= neiva_app_url('Arbimaps/index.php?page=licitaciones/consultar_licitaciones') ?>");
        } else {
          Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Ocurrió un error.' });
        }
      })
      .catch(() => Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo enviar la información.' }))
      .finally(() => btnGuardar.disabled = false);
  });

});
</script>
