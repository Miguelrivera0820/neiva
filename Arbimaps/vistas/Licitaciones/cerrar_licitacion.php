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
    <h4 class="fw-bold" style="color:#002F55;">Cerrar Licitación</h4>
    <div class="text-muted">
      <b>Radicado:</b> <?php echo htmlspecialchars($info['lc_radicado']); ?> |
      <b>Entidad:</b> <?php echo htmlspecialchars($info['lc_entidad'] ?? ''); ?> |
      <b>Estado actual:</b> <?php echo htmlspecialchars($info['lc_estado'] ?? ''); ?>
    </div>
  </div>

  <div class="card shadow">
    <div class="card-body">

      <form id="formCierre" action="<?= neiva_app_url('Arbimaps/vistas/Licitaciones/procesar_cierre.php') ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="radicado" value="<?php echo htmlspecialchars($radicado); ?>">

        <div class="mb-3">
          <label class="form-label fw-bold">Motivo del cierre</label>
          <textarea name="motivo" class="form-control" rows="4" required
                    placeholder="Describe el motivo del cierre..."></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Documento soporte (PDF)</label>
          <input type="file" name="archivo" class="form-control" required accept="application/pdf">
          <small class="text-muted">Solo PDF - máximo 20MB</small>
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
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('formCierre');
  const btnGuardar = document.getElementById('btnGuardar');

  if (!form) return;

  form.addEventListener('submit', function (e) {
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
          window.location.href = "<?= neiva_app_url('Arbimaps/index.php?page=licitaciones/consultar_licitaciones') ?>";
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
