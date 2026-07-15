<style>
    /* Contenedor de la barra y botones */
    .stepper-wrapper {
        position: relative;
        padding: 25px 0;
    }

    /* Barra de progreso */
    .stepper-line {
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 4px;
        background: #e5e7eb;
        transform: translateY(-50%);
        z-index: 1;
        border-radius: 2px;
    }

    #stepper-progress {
        height: 100%;
        width: 0%;
        background: #003B66;
        border-radius: 2px;
        transition: width 0.5s ease;
    }

    /* Estilos básicos de los botones */
    .stepper {
        display: flex;
        justify-content: space-between;
        position: relative;
        z-index: 2;
        flex-wrap: wrap;
    }

    .step {
        background: #e5e7eb;
        border-radius: 9px;
        padding: 8px 8px;
        font-size: 0.68rem;
        border: 2px solid #dae1e5ff;
        margin: 2px;
        white-space: nowrap;
        width: auto;
        cursor: pointer;
        transition: all 0.3s ease;
    }


    /* Paso activo */
    .step.active {
        background: #fff;
        border: 2px solid #003B66;
        box-shadow: 0 0 0 4px rgba(0, 59, 102, 0.15);
    }

    /* Paso completado */
    .step.completed {
        background: #003B66;
        color: #fff;
    }

    /* Barra de progreso */
    .stepper-line {
        width: 100%;
    }

    @media (min-width:1600px) {
        .step {
            font-size: 0.8rem;
        }
    }

    /* Media Query para pantallas pequeñas */
    @media (max-width: 768px) {
        .stepper {
            /* flex-direction: column; */
            align-items: center;
            /* Centrar los botones */
        }

        .step {
            width: auto;
            /* Mantener ancho automático */
            text-align: center;
            margin: 10px 5px;
            font-size: 1rem;
            /* Aumentar tamaño de fuente */
        }

        .stepper-line {
            height: 3px;
            /* Reducir altura en pantallas pequeñas */
        }

        #stepper-progress {
            height: 100%;
        }
    }

    @media (max-width: 480px) {
        .stepper-wrapper {
            padding: 35px 0;
        }
    }

    @media (max-width: 480px) {
        .step {
            font-size: 0.9rem;
            /* Ajustar fuente en pantallas más pequeñas */
            padding: 8px 12px;
            /* Reducir padding */
        }

        #stepper-progress {
            background: #2d2d2d;
            /* Cambiar el color de la barra */
        }
    }

    .card-header {
        background-color: #002F55;
        background-image: url("data:image/svg+xml;utf8,\<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23e5e7eb'>\<path d='M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0'/>\<path fill-rule='evenodd' d='M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1'/>\</svg>");
        background-repeat: no-repeat;
        background-size: 4em;
        background-position: 101% 140%;
    }

    .tilt {
        display: inline-block;
        animation: tilt 0.8s ease-in-out infinite;
    }

    @keyframes tilt {
        0% {
            transform: rotate(0deg);
        }

        25% {
            transform: rotate(1deg);
        }

        50% {
            transform: rotate(0deg);
        }

        75% {
            transform: rotate(-1deg);
        }

        100% {
            transform: rotate(0deg);
        }
    }
</style>

<div class="container-fluid px-3">

    <div class=" text-center my-4">
        <h1 class="h3  text-center mb-0" style="color:#002F55"><b>SOLICITUDES DE LICITACIONES</b></h1>
        <small class="text-muted">Formulario de solucitud de licitaciones</small>
    </div>

    <div class="card card-especial-tres rounded-4 shadow-lg mx-3  border-0 my-3">

        <div class="card-body p-3 mb-3">
            
            <!-- FORMULARIO PRINCIPAL -->
            <form id="formLicitaciones"
                action="<?= neiva_app_url('Arbimaps/vistas/Licitaciones/guardar_licitaciones.php') ?>"
                method="POST"
                enctype="multipart/form-data">

           

            <div class="row g-3">

                <div class="col-md-4 p-2">
                    <label for="lc_tipo_entidad" class="form-label" style="font-size:0.9em;"><b> Tipo de Entidad</b></label>
                    <div class="input-group shadow-sm">
                        <label class="input-group-text" for="lc_tipo_entidad">
                            <i class="bi bi-backpack2"></i>
                        </label>
                        <select class="form-select" style="font-size:0.9em;" id="lc_tipo_entidad" name="lc_tipo_entidad">
                            <option value="" disabled selected>Selecciona</option>
                            <option value="PUBLICA">PUBLICA</option>
                            <option value="PRIVADA">PRIVADA</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-4 p-2">
                    <label for="lc_proceso" class="form-label" style="font-size:0.9em;"><b>Procesos</b></label>
                    <div class="input-group shadow-sm">
                        <label class="input-group-text" for="lc_proceso">
                            <i class="bi bi-file-earmark-medical"></i>
                        </label>
                        <select class="form-select" style="font-size:0.9em;" id="lc_proceso" name="lc_proceso" required>
                            <option value="">SELECCIONE</option>
                            <option value="LICITACION_PUEBLICA">LICITACION PUBLICA</option>
                            <option value="SELECCION_ABREVIADA_DE_MENOR_CUANTIA">SELECCION ABREVIADA DE MENOR CUANTIA</option>
                            <option value="REGIMEN_ESPECIAL">REGIMEN ESPECIAL</option>
                            <option value="ACUERDO_MARCO">ACUERDO MARCO</option>
                            <option value="CONCURSO_DE_MERITOS_ABIERTOS">CONCURSO DE MERITOS ABIERTOS</option>
                            <option value="CONVOCATORIAS">CONVOCATORIAS</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-4 p-1 px-2 my-2">
                    <label for="lc_municipio" class="form-label fw-bold" style="font-size:0.9em;">Municipio</label>
                    <div class="input-group ">
                        <span class="input-group-text shadow-sm"><i class="bi bi-bank"></i></span>
                        <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="lc_municipio" name="lc_municipio"
                            placeholder="ingresa el nombre del municipio" 
                            required>
                    </div>
                </div>

                <div class="col-md-4 p-1 px-2 my-2">
                    <label for="lc_departamento" class="form-label fw-bold" style="font-size:0.9em;">Departamento</label>
                    <div class="input-group ">
                        <span class="input-group-text shadow-sm"><i class="bi bi-bank"></i></span>
                        <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="lc_departamento" name="lc_departamento"
                            placeholder="ingresa el nombre del departamento" name=""
                            required>
                    </div>
                </div>

                <div class="col-md-4 p-1 px-2 my-2">
                    <label for="lc_entidad" class="form-label fw-bold" style="font-size:0.9em;">Entidad</label>
                    <div class="input-group ">
                        <span class="input-group-text shadow-sm"><i class="bi bi-bank"></i></span>
                        <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="lc_entidad" name="lc_entidad"
                            placeholder="ingresa el nombre de la entidad" name=""
                            required>
                    </div>
                </div>

                <div class="col-md-4 p-1 px-2 my-2">
                    <label for="lc_valor" class="form-label fw-bold" style="font-size:0.9em;">Valor Contrato</label>
                    <div class="input-group">
                        <span class="input-group-text shadow-sm"><i class="bi bi-bank"></i></span>

                        <!-- Input visible -->
                        <input type="text"
                            class="form-control shadow-sm"
                            style="font-size:0.9em;"
                            id="lc_valor_display"
                            placeholder="$ 0"
                            inputmode="numeric"
                            autocomplete="off"
                            required
                            oninput="formatCurrency(this, 'lc_valor')">

                        <!-- Input oculto (valor limpio) -->
                        <input type="hidden" id="lc_valor" name="lc_valor">
                    </div>
                </div>

                <div class="col-md-4 p-1 px-2 my-2">
                    <label for="lc_numero_proceso" class="form-label fw-bold" style="font-size:0.9em;">N° Proceso</label>
                    <div class="input-group ">
                        <span class="input-group-text shadow-sm"><i class="bi bi-bank"></i></span>
                        <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="lc_numero_proceso" name="lc_numero_proceso"
                            placeholder="ingresa el numero de proceso" name=""
                            required>
                    </div>
                </div>

                <div class="col-md-4 p-1 px-2 my-2">
                    <label for="lc_nombre_licitacion" class="form-label fw-bold" style="font-size:0.9em;">Nombre de licitacion/Objeto</label>
                    <div class="input-group ">
                        <span class="input-group-text shadow-sm"><i class="bi bi-bank"></i></span>
                        <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="lc_nombre_licitacion" name="lc_nombre_licitacion"
                            placeholder="ingresa el nombre de la licitacion" name=""
                            required>
                    </div>
                </div>

                <div class="col-md-4 p-1 px-2 my-2">
                    <label for="lc_proyecto" class="form-label fw-bold" style="font-size:0.9em;">Nombre Proyecto</label>
                    <div class="input-group ">
                        <span class="input-group-text shadow-sm"><i class="bi bi-bank"></i></span>
                        <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="lc_proyecto" name="lc_proyecto"
                            placeholder="ingresa el nombre del proyecto" name=""
                            required>
                    </div>
                </div>

                <div class="col-md-4 p-1 px-2 my-2">
                    <label for="lc_fecha_apertura" class="form-label fw-bold" style="font-size:0.9em;">Fecha Apertura</label>
                    <div class="input-group ">
                        <span class="input-group-text shadow-sm"><i class="bi bi-calendar2-event"></i></span>
                        <input type="date" class="form-control shadow-sm" style="font-size:0.9em;" id="lc_fecha_apertura" name="lc_fecha_apertura"  aria-label="fecha" aria-describedby="basic-addon1" required>
                    </div>
                </div>


                <div class="col-md-4 p-1 px-2 my-2">
                    <label for="lc_fecha_presentacion" class="form-label fw-bold" style="font-size:0.9em;">Fecha Presentacion</label>
                    <div class="input-group ">
                        <span class="input-group-text shadow-sm"><i class="bi bi-calendar2-event"></i></span>
                        <input type="date" class="form-control shadow-sm" style="font-size:0.9em;" id="lc_fecha_presentacion" name="lc_fecha_presentacion"  aria-label="fecha" aria-describedby="basic-addon1" required>
                    </div>
                </div>

                <div class="col-12 col-lg-6  p-1 px-3 my-3">
                    <label for="lc_adendas" class="form-label fw-bold">Adendas</label>
                    <div class="input-group mb-1 shadow-sm">
                        <label class="input-group-text" for="lc_adendas" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
                        <input type="file" class="form-control" style="font-size:0.8em;" id="lc_adendas" name="lc_adendas">
                    </div>
                    <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                </div>

                <div class="col-12 col-lg-6  p-1 px-3 my-3">
                    <label for="lc_condiciones" class="form-label fw-bold">Pliego Condiciones Definitivas</label>
                    <div class="input-group mb-1 shadow-sm">
                        <label class="input-group-text" for="lc_condiciones" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
                        <input type="file" class="form-control" style="font-size:0.8em;" id="lc_condiciones" name="lc_condiciones">
                    </div>
                    <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                </div>

                <div class="col-12 col-lg-6  p-1 px-3 my-3">
                    <label for="lc_condiciones_proyecto" class="form-label fw-bold">Proyecto Pliego Condicones</label>
                    <div class="input-group mb-1 shadow-sm">
                        <label class="input-group-text" for="lc_condiciones_proyecto" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
                        <input type="file" class="form-control" style="font-size:0.8em;" id="lc_condiciones_proyecto" name="lc_condiciones_proyecto">
                    </div>
                    <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                </div>

                <div class="col-12 my-4">
                    <label class="form-label fw-bold text-center d-block">
                        Documentos del proceso
                    </label>

                    <div class="drop-zone" id="dropZone">
                        <div class="text-center mb-3">
                            <i class="bi bi-cloud-upload fs-1 text-primary"></i>
                            <p class="mt-2 mb-1">Haz clic o arrastra documentos aquí</p>
                            <small class="text-muted">
                                Solo archivos PDF (máx. 20 MB cada uno)
                            </small>
                        </div>

                        <!-- AQUÍ se quedan los documentos -->
                        <div class="row g-3" id="previewDocumentos"></div>

                        <input 
                            type="file"
                            id="lc_otros_doc"
                            name="lc_otros_doc[]"
                            multiple
                            accept="application/pdf"
                            hidden
                        >
                    </div>
                </div>

                <button type="submit" class="btn btn-guardar" id="btn_enviar">
                     <b>Guardar Información</b>
                </button>
      

            </form>

            </div>
        </div>
    </div>
</div>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .btn-guardar {
        background-color: #003B66 !important;
        color: #fff !important;
        border: none !important;
        padding: 10px 25px;
        border-radius: 8px;
    }

    .btn-guardar:hover {
        background-color: #004780 !important;
    }
</style>

<style>
.drop-zone {
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    padding: 40px 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    background-color: #f8fafc;
}

.drop-zone:hover {
    background-color: #eef2ff;
    border-color: #2563eb;
}

.drop-zone.dragover {
    background-color: #e0e7ff;
    border-color: #1d4ed8;
}
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const entidadSelect = document.getElementById('lc_tipo_entidad');
        const procesosSelect = document.getElementById('lc_proceso');

        // Guardamos todas las opciones originales (excepto "SELECCIONE")
        const opcionesOriginales = Array.from(procesosSelect.options).slice(1);

        entidadSelect.addEventListener('change', function () {

        const entidad = this.value;
        const valorSeleccionado = procesosSelect.value;

        procesosSelect.innerHTML = '<option value="">SELECCIONE</option>';

        let opcionesPermitidas = [];

        if (entidad === 'PUBLICA') {
            opcionesPermitidas = opcionesOriginales.slice(0, 5);
        }

        if (entidad === 'PRIVADA') {
            opcionesPermitidas = [
                opcionesOriginales[opcionesOriginales.length - 1]
            ];
        }

        opcionesPermitidas.forEach(op => {
            const copia = op.cloneNode(true);
            procesosSelect.appendChild(copia);

            // 🔑 Mantener selección
            if (copia.value === valorSeleccionado) {
                copia.selected = true;
            }
        });
    });
});
</script>

<script>
    function formatCurrency(input, hiddenId) {
        // quitar todo excepto números
        let value = input.value.replace(/\D/g, "");

        // guardar valor limpio en hidden
        if (hiddenId) {
            document.getElementById(hiddenId).value = value;
        }

        // mostrar formato moneda en el input visible
        input.value = new Intl.NumberFormat("es-CO", {
            style: "currency",
            currency: "COP",
            minimumFractionDigits: 0
        }).format(value ? parseInt(value, 10) : 0);
    }

    // Convertir a mayúsculas SOLO inputs normales, NO el de moneda
    document.querySelectorAll("input[type='text']").forEach(function(input) {
        input.addEventListener("input", function() {

            // 👇 evitar que convierta el input de moneda
            if (this.id === "lc_valor_display") return;

            this.value = this.value.toUpperCase();
        });
    });
</script>

<script>
const dropZone = document.getElementById('dropZone');
const input = document.getElementById('lc_otros_doc');
const preview = document.getElementById('previewDocumentos');

let dataTransfer = new DataTransfer();

// abrir selector
dropZone.addEventListener('click', () => input.click());

// drag & drop
dropZone.addEventListener('dragover', e => {
    e.preventDefault();
    dropZone.classList.add('dragover');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('dragover');
});

dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    agregarArchivos(e.dataTransfer.files);
});

input.addEventListener('change', () => {
    agregarArchivos(input.files);
});

function agregarArchivos(files) {
    Array.from(files).forEach(file => {

        if (file.type !== 'application/pdf') return;

        // ✅ ACUMULAR ARCHIVOS REALES
        dataTransfer.items.add(file);
        input.files = dataTransfer.files;

        // Preview
        const url = URL.createObjectURL(file);

        const card = document.createElement('div');
        card.className = 'col-12 col-md-6 col-lg-4';

        card.innerHTML = `
            <div class="card shadow-sm h-100">
                <div class="card-body p-2">
                    <p class="fw-bold small mb-1">
                        <i class="bi bi-file-earmark-pdf text-danger me-1"></i>
                        ${file.name}
                    </p>
                    <small class="text-muted">
                        ${(file.size / 1024 / 1024).toFixed(2)} MB
                    </small>
                    <iframe 
                        src="${url}" 
                        height="200"
                        class="w-100 mt-2 border rounded">
                    </iframe>
                </div>
            </div>
        `;

        preview.appendChild(card);
    });
}
</script>


<script>
    // ===================== VALIDACIÓN CON SWEETALERT + FETCH ÚNICO =====================
    document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formLicitaciones');
    const submitButton = document.getElementById('btn_enviar');

    if (!form || !submitButton) return;

    form.setAttribute('novalidate', 'novalidate');

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        // ✅ Validación normal (solo los visibles / sin disabled)
        if (!form.checkValidity()) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                text: 'Por favor, completa todos los campos requeridos.',
                confirmButtonText: 'Entendido'
            });
            form.reportValidity();
            return;
        }

        Swal.fire({
            icon: 'question',
            title: '¿Guardar información?',
            text: 'Se enviarán los datos de licitacion.',
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (!result.isConfirmed) return;

            submitButton.disabled = true;

            // ✅ IMPORTANTE: habilitar todo antes de enviar (por si algo quedó disabled)
            form.querySelectorAll('input, select, textarea, button').forEach(el => {
                el.disabled = false;
            });

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(text => {
                console.log("💬 Respuesta cruda:", text);

                let data;
                
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de respuesta',
                        text: 'El servidor no devolvió JSON válido.'
                    });
                    return;
                }

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Datos guardados',
                        text: data.message || 'La información se guardó correctamente.',
                        confirmButtonText: 'Ir a consulta'
                    }).then(() => {
                        window.location.href = "<?= neiva_app_url('Arbimaps/index.php?page=licitaciones/consultar_licitaciones') ?>";
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Ocurrió un error en el servidor.'
                    });
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de red',
                    text: 'No se pudo enviar la información.'
                });
            })
            .finally(() => {
                submitButton.disabled = false;
            });
        });
    });
});
</script>


