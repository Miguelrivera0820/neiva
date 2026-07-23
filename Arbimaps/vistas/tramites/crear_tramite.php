<?php
// session_start();
// require '../conexion.php';
// 
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const prediosDataUrl = <?= json_encode(neiva_app_url('Arbimaps/api/predios_dataset.php'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    let predios = [];
    let prediosPromise = null;

    function cargarPredios() {
        if (prediosPromise) {
            return prediosPromise;
        }

        prediosPromise = fetch(prediosDataUrl, {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('No fue posible cargar la base de predios.');
                }

                return res.json();
            })
            .then(data => {
                predios = Array.isArray(data) ? data : [];
                return predios;
            })
            .catch(error => {
                predios = [];
                console.error(error);
                return predios;
            });

        return prediosPromise;
    }

    document.addEventListener('DOMContentLoaded', function() {
        cargarPredios().then(() => {
            configurarBusqueda('npn_predio', 'numero_predio');
            configurarBusqueda('fmi_predio', 'matricula_inmobiliaria');
        });
    });

    function configurarBusqueda(inputId, campo) {
        const input = document.getElementById(inputId);
        if (!input) return;

        let suggestionsBox = document.createElement('div');
        suggestionsBox.className = 'autocomplete-items bg-white border position-absolute w-100 z-3';
        suggestionsBox.style.maxHeight = '200px';
        suggestionsBox.style.overflowY = 'auto';
        input.parentNode.appendChild(suggestionsBox);

        input.addEventListener('input', function() {
            const valor = this.value.trim();
            suggestionsBox.innerHTML = '';
            if (valor.length < 3) return;

            const valorNormalizado = campo === 'numero_doc_propietario' ?
                valor.replace(/^0+/, '') :
                valor;

            const coincidencias = predios
                .filter(p => {
                    let campoValor = String(p[campo] || '');
                    if (campo === 'numero_doc_propietario') campoValor = campoValor.replace(/^0+/, '');
                    return campoValor.startsWith(valorNormalizado);
                })
                .slice(0, 10);

            if (coincidencias.length === 0) return;

            coincidencias.forEach(p => {
                const item = document.createElement('div');
                item.textContent = p[campo];
                item.classList.add('p-2', 'border-bottom', 'cursor-pointer');

                item.addEventListener('click', () => {
                    input.value = p[campo];
                    suggestionsBox.innerHTML = '';

                    // Buscar todos los registros relacionados (mismo número_predio)
                    const relacionados = predios.filter(pr => pr.numero_predio === p.numero_predio);

                    if (relacionados.length > 0) {
                        // Llenar datos generales (una sola vez)
                        const base = relacionados[0];
                        document.getElementById('fmi_predio').value = base.matricula_inmobiliaria || '';
                        document.getElementById('npn_predio').value = base.numero_predio || '';
                        document.getElementById('direccion_predio').value = base.direccion_predio || '';
                        document.getElementById('dest_econ_predio').value = base.dest_econ_predio || '';
                        document.getElementById('area_terreno_predio').value = base.area_terreno_predio || '';
                        document.getElementById('area_construccion_predio').value = base.area_construccion_predio || '';
                        document.getElementById('avaluo_terreno_tramite').value = base.avaluo_terreno_tramite || '';
                        consultarBloqueoPredioFormulario();

                        // Crear tabla de propietarios
                        const tabla = document.querySelector('#tablaPropietarios tbody');
                        tabla.innerHTML = '';

                        relacionados.forEach((prop, index) => {
                            const fila = document.createElement('tr');
                            fila.innerHTML = `
                            <td><input type="text" class="text-center border-0 form-control px-1 form-control-sm rounded-4" 
                                name="propietarios[${index}][nombre]" 
                                value="${prop.nombre_propietario_tramite}" readonly></td>
                            <td><input type="text" class="text-center border-0 form-control px-1 form-control-sm rounded-4" 
                                name="propietarios[${index}][tipo_doc]" 
                                value="${prop.tipo_doc_propietario}" readonly></td>
                            <td><input type="text" class="text-center border-0 form-control px-1 form-control-sm rounded-4" 
                                name="propietarios[${index}][numero_doc]" 
                                value="${prop.numero_doc_propietario.replace(/^0+/, '')}" readonly></td>
                        `;
                            tabla.appendChild(fila);
                        });
                    }
                });

                suggestionsBox.appendChild(item);
            });
        });

        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !suggestionsBox.contains(e.target)) {
                suggestionsBox.innerHTML = '';
            }
        });
    }
</script>

<script>
    // Mostrar el modal de documentos automáticamente al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        var myModal = new bootstrap.Modal(document.getElementById('modalAlertaFiltro'));
        myModal.show();
    });
</script>

<style>
    .crear-tramite-page {
        background-color: #EDEDED;
        color: #0A2C1B;
        min-height: 100%;
    }

    .crear-tramite-heading {
        color: #0A2C1B;
        font-weight: 700 !important;
        letter-spacing: 0;
    }

    .crear-tramite-subtitle {
        color: #7F8E85;
        font-size: 0.92rem;
    }

    .crear-tramite-card {
        background: linear-gradient(164deg, rgba(214, 221, 218, 0.83) 0%, rgba(255, 255, 255, 0.72) 18%, #ffffff 85%, rgba(214, 221, 218, 0.68) 100%);
        border: 1px solid rgba(192, 210, 200, 0.78);
        border-radius: 18px !important;
        box-shadow: 0 14px 30px rgba(10, 44, 27, 0.08) !important;
        overflow: hidden;
    }

    .crear-tramite-page .card-header {
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%) !important;
        border: 0;
        border-radius: 14px !important;
        color: #ffffff !important;
        margin: 0.15rem;
        padding: 0.85rem 1rem !important;
    }

    .crear-tramite-page .card-header h6 {
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0;
        margin: 0;
        text-transform: uppercase;
    }

    .crear-tramite-page .form-row {
        row-gap: 0.2rem;
    }

    .crear-tramite-page .form-label,
    .crear-tramite-page label {
        color: #0A2C1B;
        font-size: 0.84rem !important;
        font-weight: 400;
        margin-bottom: 0.4rem;
    }

    .crear-tramite-page .input-group {
        border-radius: 16px;
        box-shadow: 0 7px 16px rgba(10, 44, 27, 0.06) !important;
    }

    .crear-tramite-page .input-group-text {
        background-color: #F6F8F7;
        border: 1px solid #C0D2C8;
        border-right: 0;
        border-radius: 16px 0 0 16px;
        color: #0A2C1B;
        justify-content: center;
        min-width: 42px;
    }

    .crear-tramite-page .form-control,
    .crear-tramite-page .form-select {
        border: 1px solid #C0D2C8;
        border-radius: 0 16px 16px 0;
        color: #0A2C1B;
        font-size: 0.86rem !important;
        min-height: 42px;
    }

    .crear-tramite-page textarea.form-control {
        border-radius: 16px;
        min-height: 120px;
    }

    .crear-tramite-page .form-control:focus,
    .crear-tramite-page .form-select:focus {
        border-color: #0A2C1B;
        box-shadow: 0 0 0 0.15rem rgba(10, 44, 27, 0.12);
    }

    .crear-tramite-page .form-text {
        color: #7F8E85;
        font-size: 0.76rem;
    }

    .crear-tramite-page #tablaPropietarios {
        border-collapse: separate;
        border-spacing: 0;
        color: #0A2C1B;
    }

    .crear-tramite-page #tablaPropietarios thead th {
        background-color: #EDEDED !important;
        border: 0 !important;
        color: #0A2C1B !important;
        font-size: 0.72rem !important;
        padding: 0.75rem 0.55rem;
        text-transform: uppercase;
    }

    .crear-tramite-page #tablaPropietarios tbody td {
        border: 0 !important;
        border-bottom: 1px solid #E3E8E5 !important;
        padding: 0.65rem 0.5rem;
    }

    .crear-tramite-submit {
        align-items: center;
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%) !important;
        border: 0;
        border-radius: 20px;
        display: flex;
        justify-content: center;
        min-height: 48px;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .crear-tramite-submit i {
        display: inline-block;
        transition: transform 0.25s ease;
    }

    .crear-tramite-submit:hover {
        box-shadow: 0 12px 24px rgba(10, 44, 27, 0.18);
        color: #ffffff !important;
        transform: scale(1.01);
    }

    .crear-tramite-submit:hover i {
        transform: rotate(25deg);
    }

    .crear-tramite-page .modal-content {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
    }

    .crear-tramite-page .modal-header {
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%) !important;
    }

    .crear-tramite-page .modal-footer .btn {
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%) !important;
        border-radius: 16px;
    }

    .container-fluid.rounded-4.p-3>.my-3.text-center h3 {
        color: #0A2C1B !important;
        font-weight: 700 !important;
        letter-spacing: 0;
    }

    .container-fluid.rounded-4.p-3>.my-3.text-center small {
        color: #7F8E85;
        font-size: 0.92rem;
    }

    #miFormulario .card {
        background: white;
        border: none;
        border-radius: 18px !important;
        box-shadow: 0 14px 30px rgba(10, 44, 27, 0.08) !important;
        overflow: hidden;
    }

    #miFormulario .card-header {
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%) !important;
        border: 0;
        border-radius: 14px !important;
        color: #ffffff !important;
        margin: 0.15rem;
        padding: 0.85rem 1rem !important;
    }

    #miFormulario .card-header h7,
    #miFormulario .card-header h6 {
        display: block;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0;
        margin: 0;
        text-transform: uppercase;
    }

    #miFormulario .form-label,
    #miFormulario label {
        color: #0A2C1B;
        font-size: 0.84rem !important;
        font-weight: 600;
        margin-bottom: 0.4rem;
    }

    #miFormulario .input-group {
        border-radius: 12px;

    }

    #miFormulario .input-group-text {
        background-color: #F6F8F7;
        border: 1px solid #C0D2C8;
        border-right: 0;
        border-radius: 12px 0 0 12px;
        color: #0A2C1B;
        justify-content: center;
        min-width: 42px;
    }

    #miFormulario .form-control,
    #miFormulario .form-select {
        border: 1px solid #C0D2C8;
        border-radius: 0 12px 12px 0;
        color: #0A2C1B;
        font-size: 0.86rem !important;
        min-height: 42px;
    }

    #miFormulario input[type="file"].form-control {
        height: 42px;
        line-height: 42px;
        min-height: 42px;
        padding: 0 0.85rem 0 0;
    }

    #miFormulario input[type="file"].form-control::file-selector-button {
        background-color: #F6F8F7;
        border: 0;
        border-right: 1px solid #C0D2C8;
        color: #0A2C1B;
        height: 42px;
        margin: 0 0.85rem 0 0;
        padding: 0 1rem;
    }

    #miFormulario input[type="file"].form-control:hover::file-selector-button {
        background-color: #EDEDED;
        color: #0A2C1B;
    }

    #miFormulario textarea.form-control {
        border-radius: 16px;
        min-height: 120px;
    }

    #miFormulario .form-control:focus,
    #miFormulario .form-select:focus {
        border-color: #0A2C1B;
        box-shadow: 0 0 0 0.15rem rgba(10, 44, 27, 0.12);
    }

    #miFormulario .form-text {
        color: #7F8E85;
        font-size: 0.76rem;
    }

    #miFormulario #tablaPropietarios {
        border-collapse: separate;
        border-spacing: 0;
        color: #0A2C1B;
    }

    #miFormulario #tablaPropietarios thead th {
        background: linear-gradient(180deg, rgba(10, 44, 27, 1) 88%, rgba(160, 200, 130, 1) 100%) !important;
        border: 0 !important;
        color: #ffffff !important;
        font-size: 0.72rem !important;
        padding: 0.75rem 0.55rem;
        text-transform: uppercase;
    }

    #miFormulario #tablaPropietarios tbody td {
        border: 0 !important;
        border-bottom: 1px solid #E3E8E5 !important;
        padding: 0.65rem 0.5rem;
    }

    #miFormulario button[type="submit"] {
        align-items: center;
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%) !important;
        border: 0;
        border-radius: 20px;
        display: flex;
        justify-content: center;
        min-height: 48px;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    #miFormulario button[type="submit"] i {
        display: inline-block;
        transition: transform 0.25s ease;
    }

    #miFormulario button[type="submit"]:hover {
        box-shadow: 0 12px 24px rgba(10, 44, 27, 0.18);
        color: #ffffff !important;
        transform: scale(1.01);
    }

    #miFormulario button[type="submit"]:hover i {
        transform: rotate(25deg);
    }

    #municipio_rad {
        margin-left: 0;
    }

    #miFormulario div[style*="margin-left: 25%"] {
        margin-left: 0 !important;
    }

    #modalAlertaFiltro .modal-content {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
    }

    #modalAlertaFiltro .modal-header {
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%) !important;
    }

    #modalAlertaFiltro .modal-footer .btn {
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%) !important;
        border-radius: 16px;
    }

    .crear-tramite-loading {
        align-items: center;
        background: rgba(10, 44, 27, 0.68);
        backdrop-filter: blur(8px);
        display: none;
        inset: 0;
        justify-content: center;
        padding: 1rem;
        position: fixed;
        z-index: 1080;
    }

    .crear-tramite-loading.is-visible {
        display: flex;
    }

    .crear-tramite-loading-card {
        background: #ffffff;
        border: 1px solid rgba(192, 210, 200, 0.78);
        border-radius: 18px;
        box-shadow: 0 24px 48px rgba(10, 44, 27, 0.18);
        color: #0A2C1B;
        max-width: 460px;
        padding: 1.5rem;
        text-align: center;
        width: 100%;
    }

    .crear-tramite-loading-icon {
        align-items: center;
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%);
        border-radius: 999px;
        color: #ffffff;
        display: inline-flex;
        height: 56px;
        justify-content: center;
        margin-bottom: 1rem;
        width: 56px;
    }

    .crear-tramite-loading-icon i {
        animation: crearTramitePulse 1.2s ease-in-out infinite;
        font-size: 1.45rem;
    }

    .crear-tramite-loading-title {
        color: #0A2C1B;
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 0.45rem;
    }

    .crear-tramite-loading-text {
        color: #7F8E85;
        font-size: 0.88rem;
        margin-bottom: 1rem;
    }

    .crear-tramite-loading .progress {
        background-color: #EDEDED;
        border-radius: 999px;
        height: 8px;
        overflow: hidden;
    }

    .crear-tramite-loading .progress-bar {
        animation: crearTramiteProgress 2.6s ease-in-out infinite;
        background: linear-gradient(90deg, #0A2C1B, #0A5F5E, #029F96);
        border-radius: 999px;
    }

    .crear-tramite-submit-text {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    #miFormulario button[type="submit"].is-submitting {
        opacity: 0.92;
        pointer-events: none;
        transform: none;
    }

    @keyframes crearTramitePulse {
        0% {
            transform: scale(1);
            opacity: 1;
        }

        50% {
            transform: scale(1.12);
            opacity: 0.82;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    @keyframes crearTramiteProgress {
        0% {
            width: 18%;
        }

        50% {
            width: 82%;
        }

        100% {
            width: 96%;
        }
    }

    .input-group {
        position: relative;
        /* crea un nuevo contexto */
        overflow: visible !important;
        /* permite que se vea el menú */
    }

    .autocomplete-items {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 9999;
    }
</style>

<!-----------------------------------------------
            modal para mostrar documentos requeridos
-------------------------------------------------------->

<div class="modal fade" id="modalAlertaFiltro" tabindex="-1" aria-labelledby="modalAlertaFiltroLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-white px-4">
                <h5 class="modal-title" id="modalAlertaFiltroLabel">Antes de continuar</h5>
                <button type="button" class="btn-close bg-white me-1" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <!-- Aquí va el mensaje -->
                <h5 class="text-center"><b>Recuerda tener a mano los siguientes documentos:</b></h5>
                <hr class="m-2 my-4 " style="color: #002f44 !important;">
                <ul class="list-unstyled ms-2">
                    <li class="my-1"><i class="bi bi-dot "></i> Solicitud firmada por el ciudadano</li>
                    <li class="my-1"><i class="bi bi-dot "></i> Copia de escritura pública - Sentencia Judicial - Acto administrativo</li>
                    <li class="my-1"><i class="bi bi-dot "></i> Certificado de Tradición y Libertad</li>
                    <li class="my-1"><i class="bi bi-dot "></i>Documento de identidad</li>
                    <li class="my-1"><i class="bi bi-dot "></i> Recibo de pago actualizado</li>
                    <li class="my-1"><i class="bi bi-dot "></i> <b>En caso de ser Autorizado - Tercero:</b> anexar copia de documento de identidad del autorizado y carta de autorización firmada por el propietario</li>
                    <li class="my-1"><i class="bi bi-dot "></i> <b>Otros Documentos:</b> Avalúos, Levantamientos Topográficos, Planos, Recibos de Servicios Públicos, etc. </li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn rounded-3" style="background-color: #002F55; color: white;"
                    data-bs-dismiss="modal">Entendido</button>

            </div>
        </div>
    </div>
</div>

<!-- CONTENIDO PAGINA MODIFICACION -->
<div class="container-fluid rounded-4 p-3 " style="background-color:#EDEDED;">
    <div class="my-3 text-center">
        <h3 class=" mb-0 fw-bold" style="color: #002F55; font-weight: 700 !important">CREAR TRÁMITE</h3>
        <small>Completa el siguiente formulario para generar un nuevo trámite</small>
    </div>
    <form id="miFormulario" class="mb-0" action="index.php?page=tramites/cargue_tramite_rad_dos_1" method="POST" enctype="multipart/form-data">
        <div class="container-fluid">
            <div class="row">
                <!-- col-xl-12 col-lg-7 -> ESTO LO QUE HACE ES DEFINIR EL ESPACIO DE LA TARJETA -->
                <div class="col-xl-6 p-1">
                    <div class="card  shadow h-100 p-3 d-flex  justify-content-center" style="border-radius: 12px;">
                        <!-- Card Header - Dropdown -->
                        <div class=" p-3  text-center">
                            <h5 style="color: #0A2C1B; font-weight:800;" class="mb-0 ">FORMULARIO PARA REGISTRO DE TRÁMITE</h5>
                        </div>

                        <hr class="m-2 " style="color: #002f44 !important;">

                        <div class="card-body  p-2 ">
                            <div class="form-row text-center">
                                <div class="col-md-6 p-1 px-2 my-3 ">
                                    <label for="documento_interesado" class="form-label w-100 ps-2 text-start" style="font-size:0.9em;">Documento interesado</label>
                                    <div class="input-group ">
                                        <label class="input-group-text mb-0" for="documento_interesado">
                                            <i class="bi-person-badge" style="color: #0A2C1B !important;"></i>
                                        </label>
                                        <select class="form-select" style="font-size:0.9em;" id="documento_interesado" name="documento_interesado" required>
                                            <option value="" disabled selected>Selecciona el tipo de documento</option>
                                            <option value="Cedula_Ciudadania">Cédula de ciudadanía</option>
                                            <option value="Cedula_Extranjeria">Cédula de extranjería</option>
                                            <option value="NIT">N.I.T</option>
                                            <option value="Pasaporte">Pasaporte</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 p-1 px-2 my-3">
                                    <label for="num_doc_interesado" class="form-label fw-bold w-100 ps-2 text-start" style="font-size:0.9em;">Número Doc. de Identidad Interesado</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                                        <input type="number" class="form-control" style="font-size:0.9em;" id="num_doc_interesado" name="num_doc_interesado" placeholder="Ingrese el número de documento..." name="cert_primer_nombre" aria-label="PrimerNombre" aria-describedby="basic-addon1">
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-3">
                                    <label for="primer_nombre_interesado" class="form-label fw-bold w-100 ps-2 text-start" style="font-size:0.9em;">Primer nombre del interesado</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="primer_nombre_interesado" name="primer_nombre_interesado"
                                            placeholder="Ingrese primer nombre..." name="primer_nombre_interesado" aria-label="PrimerNombre"
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-3">
                                    <label for="segundo_nombre_interesado" class="form-label fw-bold w-100 ps-2 text-start" style="font-size:0.9em;">Segundo nombre del interesado</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="segundo_nombre_interesado" name="segundo_nombre_interesado"
                                            placeholder="Ingrese segundo nombre..." name="cert_primer_nombre" aria-label="PrimerNombre">
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-3">
                                    <label for="primer_apellido_interesado" class="form-label fw-bold w-100 ps-2 text-start" style="font-size:0.9em;">Primer apellido del interesado</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi-people-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="primer_apellido_interesado" placeholder="Ingrese primer apellido..."
                                            name="primer_apellido_interesado" aria-label="PrimerApellido" aria-describedby="basic-addon1">
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-3">
                                    <label for="segundo_apellido_interesado" class="form-label fw-bold w-100 ps-2 text-start" style="font-size:0.9em;">Segundo apellido del interesado</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi-people"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="segundo_apellido_interesado" placeholder="Ingrese segundo apellido..."
                                            name="segundo_apellido_interesado" aria-label="SegundoApellido">
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-3">
                                    <label for="telefono_interesado" class="form-label fw-bold w-100 ps-2 text-start" style="font-size:0.9em;">Número telefónico del interesado</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-telephone-fill me-1"></i> +57</span>
                                        <input type="text" class="form-control" id="telefono_interesado" placeholder="Número telefónico..."
                                            name="telefono_interesado" aria-label="PrimerNombre">
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-3">
                                    <label for="correo_interesado" class="form-label fw-bold w-100 ps-2 text-start" style="font-size:0.9em;">Correo electrónico del interesado</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at-fill"></i></span>
                                        <input type="text" class="form-control" id="correo_interesado" placeholder="Correo electrónico..."
                                            name="correo_interesado" aria-label="PrimerNombre">
                                    </div>
                                </div>

                                <input type="hidden" id="es_radicado_mercurio" name="es_radicado_mercurio" value="NO">
                                <input type="hidden" id="cod_tramite_mercurio" name="cod_tramite_mercurio" value="">

                                <div class="col-md-6 p-1 px-2 my-3 ">
                                    <label for="tipo_tramite" class="form-label  w-100 ps-2 text-start" style="font-size:0.9em;">Tipo de trámite</label>
                                    <div class="input-group">
                                        <label class="input-group-text mb-0" for="tipo_tramite">
                                            <i class="bi bi-diagram-3-fill"></i>
                                        </label>
                                        <select class="form-select" id="tipo_tramite" style="font-size:0.9em;" name="tipo_tramite" required>
                                            <option value="ACTUALIZACION" selected>Actualización Catastral</option>
                                            <option value="CONSERVACION">Conservación Catastral</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-3 ">
                                    <label for="mutacion_tramite" class="form-label  w-100 ps-2 text-start" style="font-size:0.9em;">Seleccione el trámite</label>
                                    <div class="input-group">
                                        <label class="input-group-text mb-0" for="mutacion_tramite">
                                            <i class="bi bi-file-earmark-text-fill"></i>
                                        </label>
                                        <select class="form-select" id="mutacion_tramite" style="font-size:0.9em;" name="mutacion_tramite" required>
                                            <option value="" disabled selected><b>Seleccione</b></option>
                                            <option value="Mutacion_1">Mutación 1</option>
                                            <option value="Mutacion_2">Mutación 2</option>
                                            <option value="Mutacion_3">Mutación 3</option>
                                            <option value="Mutacion_4">Mutación 4</option>
                                            <option value="Mutacion_5">Mutación 5</option>
                                            <option value="Cancelacion">Cancelación</option>
                                            <option value="Complementacion">Complementación</option>
                                            <option value="Peticion">Petición</option>
                                            <option value="Queja">Queja</option>
                                            <option value="Reclamo">Reclamo</option>
                                            <option value="Solicitud">Solicitud</option>
                                            <option value="Otro">Otro</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-3 " id="grupo_subtipo_conservacion" style="display:none;">
                                    <label for="subtipo_conservacion" class="form-label  w-100 ps-2 text-start" style="font-size:0.9em;">Seleccione subtipo de conservación</label>
                                    <div class="input-group">
                                        <label class="input-group-text mb-0" for="subtipo_conservacion">
                                            <i class="bi bi-file-earmark-check-fill"></i>
                                        </label>
                                        <select class="form-select" id="subtipo_conservacion" style="font-size:0.9em;" name="subtipo_conservacion">
                                            <option value="" disabled selected><b>Seleccione</b></option>
                                            <option value="Mutaciones">Mutaciones</option>
                                            <option value="Rectificacion">Rectificación</option>
                                            <option value="Otros_Procesos">Otros Procesos</option>
                                            <option value="Procesos_Especiales">Procesos Especiales</option>
                                            <option value="Edicion_GeograficaV2">Edición Geográfica V2</option>
                                            <option value="Proceso_Notificacion">Proceso de Notificación</option>
                                            <option value="Avaluo_Puntual">Avaluo_Puntual</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-3 " id="grupo_detalle_subtipo_conservacion" style="display:none;">
                                    <label for="detalle_subtipo_conservacion" class="form-label  w-100 ps-2 text-start" style="font-size:0.9em;">Seleccione detalle de conservacón</label>
                                    <div class="input-group">
                                        <label class="input-group-text mb-0" for="detalle_subtipo_conservacion">
                                            <i class="bi bi-list-check"></i>
                                        </label>
                                        <select class="form-select" id="detalle_subtipo_conservacion" style="font-size:0.9em;" name="detalle_subtipo_conservacion">
                                            <option value="" disabled selected><b>Seleccione</b></option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-3 " id="grupo_subtipo_actualizacion" style="display:none;">
                                    <label for="subtipo_actualizacion" class="form-label  w-100 ps-2 text-start" style="font-size:0.9em;">Seleccione subtipo de actualización</label>
                                    <div class="input-group">
                                        <label class="input-group-text mb-0" for="subtipo_actualizacion">
                                            <i class="bi bi-list-check"></i>
                                        </label>
                                        <select class="form-select" id="subtipo_actualizacion" style="font-size:0.9em;" name="subtipo_actualizacion">
                                            <option value="" disabled selected><b>Seleccione</b></option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-3">
                                    <label for="tsolicitante_tramite" class="form-label  w-100 ps-2 text-start" style="font-size:0.9em;">Seleccione tipo de solicitante</label>
                                    <div class="input-group">
                                        <label class="input-group-text mb-0" for="tsolicitante_tramite">
                                            <i class="bi bi-person-badge-fill"></i>
                                        </label>
                                        <select class="form-select" id="tsolicitante_tramite" style="font-size:0.9em;" name="tsolicitante_tramite" required>
                                            <option value="" disabled selected><b>Seleccione </b></option>
                                            <option value="Propietario">Propietario</option>
                                            <option value="Autorizado-Tercero">Autorizado - Tercero</option>
                                            <option value="Entidad_Publica">Entidad Pública</option>
                                            <option value="Poseedor">Poseedor</option>
                                        </select>
                                    </div>
                                </div>

                                <input type="hidden" id="municipio_rad" name="municipio_rad" value="NEIVA">


                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 p-1">
                    <div class="card card-especial shadow p-2 h-100 d-flex  justify-content-center" style="border-radius: 12px">

                        <div class=" p-3  text-center">
                            <h5 style="color: #0A2C1B; font-weight:800;" class="mb-0 ">INFORMACIÓN DEL PREDIO</h5>
                        </div>

                        <hr class="m-2 " style="color: #002f44 !important;">

                        <div class="card-body p-2">
                            <div class="form-row text-center d-flex justify-content-center">
                                <div class="col-md-6 p-1 px-2 my-3   d-flex flex-column  justify-content-center">
                                    <label for="fmi_predio" class="form-label fw-bold w-100 ps-2 text-start" style="font-size:0.9em;">FMI Predio</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-map-fill"></i></span>
                                        <input class="form-control" style="font-size:0.8em;" id="fmi_predio" name="fmi_predio" type="text"
                                            placeholder="Ingrese FMI del Predio">
                                    </div>
                                </div>
                                <div class="col-md-6 p-1 px-2 my-3   d-flex flex-column  justify-content-center position-relative">
                                    <label for="npn_predio" class="form-label fw-bold w-100 ps-2 text-start" style="font-size:0.9em;">Cod. Catastral Predio</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-map"></i></span>
                                        <input class="form-control " style="font-size:0.8em;" id="npn_predio" name="npn_predio" type="text"
                                            placeholder="Ingrese N° Predial de predio" required autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-12  p-1 px-2 mt-3 ">
                                    <div id="alertaPredioBloqueadoTramite" class="alert alert-danger d-none text-start"
                                        role="alert">
                                        <div class="d-flex gap-2">
                                            <i class="bi bi-lock-fill"></i>
                                            <div>
                                                <b>Predio bloqueado</b>
                                                <div>Este predio está bloqueado y no se permite crear el trámite.</div>
                                                <small id="detallePredioBloqueadoTramite"></small>
                                            </div>
                                        </div>
                                    </div>
                                    <h6 class="text-center fw-bold">Propietarios del Predio</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered text-secondary" id="tablaPropietarios">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th style="border-top-left-radius: 12px; border-bottom-left-radius: 12px;">Nombre Propietario</th>
                                                    <th>Tipo Documento</th>
                                                    <th style="border-top-right-radius: 12px; border-bottom-right-radius: 12px;">Número Documento</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="text-center">
                                                    <td colspan="3" class="text-center text-muted">Sin datos</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-3  d-flex flex-column  justify-content-center">
                                    <label for="avaluo_terreno_tramite" class="form-label fw-bold w-100 ps-2 text-start" style="font-size:0.9em;">Valor Avaluo de Terreno</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-piggy-bank-fill"></i></span>
                                        <input class="form-control " style="font-size:0.9em;" id="avaluo_terreno_tramite" name="avaluo_terreno_tramite" type="text"
                                            placeholder="Valor Terreno">
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-3  d-flex flex-column  justify-content-center">
                                    <label for="direccion_predio" class="form-label fw-bold w-100 ps-2 text-start" style="font-size:0.9em;">Dirección Predio</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-signpost-2-fill"></i></span>
                                        <input class="form-control " style="font-size:0.9em;" id="direccion_predio" name="direccion_predio" type="text"
                                            placeholder="Dirección Predios Base Alfanumerica">
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-3 d-flex flex-column  justify-content-center">
                                    <label for="dest_econ_predio" class="form-label fw-bold w-100 ps-2 text-start" style="font-size:0.9em;">Destino Economico Predio</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-house-exclamation-fill"></i></span>
                                        <input class="form-control " style="font-size:0.9em;" id="dest_econ_predio" name="dest_econ_predio" type="text"
                                            placeholder="Destino Económico Base Alfanumerica">
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-3  d-flex flex-column  justify-content-center">
                                    <label for="area_terreno_predio" class="form-label fw-bold w-100 ps-2 text-start" style="font-size:0.9em;">Area Terreno Predio</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-globe-europe-africa-fill"></i></span>
                                        <input class="form-control " style="font-size:0.9em;" id="area_terreno_predio" name="area_terreno_predio" type="number"
                                            placeholder="Area Terreno Predio Base Alfanumerica">
                                    </div>
                                </div>

                                <div class="col-md-8 p-1 px-2 my-3 d-flex flex-column  justify-content-center">
                                    <label for="area_construccion_predio" class="form-label fw-bold w-100 ps-2 text-start" style="font-size:0.9em;">Area Construccion Predio</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-house-fill"></i></span>
                                        <input class="form-control " style="font-size:0.9em;" id="area_construccion_predio" name="area_construccion_predio" type="number"
                                            placeholder="Area Construccion Predio Alfanumerica">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-12 p-1">
                    <div class="card card-especial-dos shadow mb-4 p-2 h-100" style="border-radius: 12px;">

                        <div class=" p-3  text-center">
                            <h5 style="color: #0A2C1B; font-weight:800;" class="mb-0 ">DOCUMENTACIÓN APORTADA POR EL USUARIO</h5>
                        </div>

                        <hr class="m-2 " style="color: #002f44 !important;">

                        <div class="card-body p-2">
                            <div class="container-fluid">
                                <div class="form-row text-center ">
                                    <div class=" col-12 col-lg-6 p-1 px-2 my-3">
                                        <label for="sol_escrita_tramite" class="form-label w-100 ps-2 text-start" style="font-size:0.9em;">Solicitud escrita</label>
                                        <div class="input-group mb-1  ">
                                            <label class="input-group-text mb-0" for="sol_escrita_tramite" style="font-size:0.9em;"><i class="bi bi-file-earmark-pdf-fill"></i></label>
                                            <input type="file" class="form-control " style="font-size:0.7em;" id="sol_escrita_tramite" name="sol_escrita_tramite">
                                        </div>
                                        <div class="form-text text-start mt-2  ps-2">Solo se permiten archivos PDF de hasta 20 MB.</div>
                                    </div>

                                    <div class="col-12 col-lg-6  p-1 px-2 my-3">
                                        <label for="cop_escritura_tramite" class="form-label w-100 ps-2 text-start" style="font-size:0.9em;">Copia de Escritura / Sentencia Judicial / Acto Administrativo</label>
                                        <div class="input-group mb-1 ">
                                            <label class="input-group-text mb-0" for="cop_escritura_tramite" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf-fill"></i></label>
                                            <input type="file" class="form-control" style="font-size:0.8em;" id="cop_escritura_tramite" name="cop_escritura_tramite">
                                        </div>
                                        <div class="form-text text-start mt-2  ps-2">Solo se permiten archivos PDF de hasta 20 MB.</div>
                                    </div>

                                    <div class="col-12 col-lg-6 p-1 px-2 my-3">
                                        <label for="ctl_tramite" class="form-label w-100 ps-2 text-start" style="font-size:0.9em;">Certificado Tradición y Libertad</label>
                                        <div class="input-group mb-1 ">
                                            <label class="input-group-text mb-0" for="ctl_tramite" style="font-size:0.9em;"><i class="bi bi-file-earmark-pdf-fill"></i></label>
                                            <input type="file" class="form-control" style="font-size:0.8em;" id="ctl_tramite" name="ctl_tramite">
                                        </div>
                                        <div class="form-text text-start mt-2  ps-2">Solo se permiten archivos PDF de hasta 20 MB.</div>
                                    </div>

                                    <div class="col-12 col-lg-6  p-1 px-2 my-3">
                                        <label for="doc_identidad_tramite" class="form-label w-100 ps-2 text-start" style="font-size:0.9em;">Documento de identidad</label>
                                        <div class="input-group mb-1 ">
                                            <label class="input-group-text mb-0" for="doc_identidad_tramite" style="font-size:0.9em;"><i class="bi bi-person-vcard-fill"></i></label>
                                            <input type="file" class="form-control" style="font-size:0.8em;" id="doc_identidad_tramite" name="doc_identidad_tramite">
                                        </div>
                                        <div class="form-text text-start mt-2  ps-2">Solo se permiten archivos PDF de hasta 20 MB.</div>
                                    </div>

                                    <div class="col-12 col-lg-6 p-1 px-2 my-3">
                                        <label for="carta_autorizacion_tramite" class="form-label w-100 ps-2 text-start" style="font-size:0.9em;" style="font-size:0.9em;">Carta Autorización Tramite (en caso de ser tercero)</label>
                                        <div class="input-group mb-1 ">
                                            <label class="input-group-text mb-0" for="carta_autorizacion_tramite" style="font-size:0.9em;"><i class="bi bi-envelope-open-fill"></i></label>
                                            <input type="file" class="form-control" style="font-size:0.8em;" id="carta_autorizacion_tramite" name="carta_autorizacion_tramite">
                                        </div>
                                        <div class="form-text text-start mt-2  ps-2">Solo se permiten archivos PDF de hasta 20 MB.</div>
                                    </div>

                                    <div class="col-12 col-lg-6 p-1 px-2 my-3">
                                        <label for="otros_doc_tramite" class="form-label w-100 ps-2 text-start" style="font-size:0.9em;" style="font-size:0.9em;">Otros documentos entregados</label>
                                        <div class="input-group mb-1 ">
                                            <label class="input-group-text mb-0" for="otros_doc_tramite" style="font-size:0.9em;"><i class="bi bi-file-pdf"></i></label>
                                            <input type="file" class="form-control" style="font-size:0.8em;" id="otros_doc_tramite" name="otros_doc_tramite">
                                        </div>
                                        <div class="form-text text-start mt-2  ps-2">Solo se permiten archivos PDF de hasta 20 MB.</div>
                                    </div>

                                    <div class="col-md-8 mx-auto p-2 d-flex flex-column  justify-content-center">
                                        <label for="observacion_tramite" style="font-size:0.9em;" class="my-2 p-2  w-75 rounded-3 mx-auto">Descripción y Observaciones de Trámite</label>
                                        <textarea class="form-control py-4 shadow-sm " style="font-size:0.8em; " id="observacion_tramite" name="observacion_tramite" placeholder="Haga una descripción clara u observación de la solicitud del trámite..."></textarea>
                                    </div>

                                </div>
                            </div>

                            <hr class="m-4 " style="color: #002f44 !important;">

                            <div class="form-group mt-4 mb-0  w-100 d-flex align-items-center justify-content-center">
                                <button type="submit" class="btn btn-block text-white w-50" id="btnCrearTramite">
                                    <b class="crear-tramite-submit-text"><i class="bi bi-file-earmark-plus me-2"></i> CREAR TRAMITE </b>
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="crear-tramite-loading" id="crearTramiteLoading" aria-live="polite" aria-hidden="true">
    <div class="crear-tramite-loading-card">
        <div class="crear-tramite-loading-icon">
            <i class="bi bi-file-earmark-plus"></i>
        </div>
        <div class="crear-tramite-loading-title">Creando trámite</div>
        <p class="crear-tramite-loading-text">
            Estamos guardando la información y cargando los documentos. No cierres esta ventana.
        </p>
        <div class="progress">
            <div class="progress-bar" role="progressbar" aria-label="Creando trámite"></div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tipoTramite = document.getElementById('tipo_tramite');
        const mutacionTramite = document.getElementById('mutacion_tramite');
        const grupoMutacionTramite = mutacionTramite.closest('.col-md-6');
        const grupoSubtipoConservacion = document.getElementById('grupo_subtipo_conservacion');
        const subtipoConservacion = document.getElementById('subtipo_conservacion');
        const grupoDetalleSubtipoConservacion = document.getElementById('grupo_detalle_subtipo_conservacion');
        const detalleSubtipoConservacion = document.getElementById('detalle_subtipo_conservacion');
        const grupoSubtipoActualizacion = document.getElementById('grupo_subtipo_actualizacion');
        const subtipoActualizacion = document.getElementById('subtipo_actualizacion');
        const grupoOtroProcesoActualizacion = document.getElementById('grupo_otro_proceso_actualizacion');
        const otroProcesoActualizacion = document.getElementById('otro_proceso_actualizacion');
        const grupoOtroSubtipoConservacion = document.getElementById('grupo_otro_subtipo_conservacion');
        const otroSubtipoConservacion = document.getElementById('otro_subtipo_conservacion');

        const opcionesPorMutacion = {
            "Mutacion_1": [
                "Cambio Propietario",
                "Cambio Poseedor",
                "Cambio Ocupante",
                "Cambio Mejoratario",
                "Cambio Arrendador"
            ],
            "Mutacion_2": [
                "Agregación NPH",
                "Segregacion NPH",
                "Predios RPH"
            ],
            "Mutacion_3": [
                "Incorporación Construcción",
                "Demolición Construcción",
                "Destino Económico Predio"
            ],
            "Mutacion_4": [
                "Revisión Avalúo"
            ],
            "Mutacion_5": [
                "Inscripción Predio Nuevo",
                "Inscripción Predio Omitido",
                "Informalidades"
            ],
            "Cancelacion": [
                "Cancelación Inscripción Catastral Orden Judicial",
                "Cancelación Doble Inscripción"
            ],
            "Complementacion": [
                "Adición Datos Predio",
                "Adición Datos Propietario"
            ]
        };

        const detallesPorConservacion = {
            "Mutaciones": [
                "Agregación/Segregación",
                "Cambio de Propietario o Poseedor",
                "Comisión - Inscripción de Predio",
                "Construcciones",
                "Incremento Anual",
                "Mejoras",
                "Predios RPH",
                "Solicitud de Autoestimaciones",
                "Informalidades",
                "Informalidades Existente",
                "Cambio de Propietario Masivo"
            ],
            "Rectificacion": [
                "Rectificación Aspectos Afectan Avalúo",
                "Rectificación Aspectos No Afectan Avalúo",
                "Rectificación Cabidas y Linderos",
                "Rectificación de Propietario",
                "Rectificación Áreas 01-11",
                "Cambio de Matrícula Inmobiliaria"
            ],
            "Otros_Procesos": [
                "Actos Administrativos Independientes",
                "Cambio Cedula Catastral",
                "Cambio Limte de Zona",
                "Eliminación de Predio",
                "Estratificación",
                "Marcar o Desmarcar Predios/Propietarios",
                "Revisión de Avalúo",
                "Recursos",
                "Rectificación de Persona",
                "Certificado Fines Notariales"
            ],
            "Procesos_Especiales": [
                "Proceso Especial - Infraestructura",
                "Proceso Especial - Resguardos",
                "Proceso Especial - Restitucion"
            ],
            "Edicion_GeograficaV2": [
                "Predios Normales",
                "Predios RPH Altura",
                "Parcelaciones",
                "Informalidades"
            ],
            "Proceso_Notificacion": [
                "Tipos de Notificaciones",
                "Tramites a los cuales aplica el proceso de Notificación",
                "Proceso de Notificación",
                "Por Citar",
                "Citar por Aviso",
                "Por Notificar - Renuncia a Terminos",
                "Avisor por Fijar",
                "Avisos por Desfijar"
            ],
            "Avaluo_Puntual": [
                "Avalúo Puntual Individual - Area de Terreno",
                "Avalúo Puntual Individual - Area Construida",
                "Avalúo Puntual por Carga Masiva RPH"
            ]
        };

        function alternarSubtipoTramite() {
            const esConservacion = tipoTramite.value === 'CONSERVACION';
            grupoMutacionTramite.style.display = esConservacion ? 'none' : '';
            grupoSubtipoConservacion.style.display = esConservacion ? '' : 'none';
            mutacionTramite.required = !esConservacion;
            subtipoConservacion.required = esConservacion;

            if (esConservacion) {
                mutacionTramite.value = '';
                subtipoActualizacion.value = '';
                if (otroProcesoActualizacion) {
                    otroProcesoActualizacion.value = '';
                }
            } else {
                subtipoConservacion.value = '';
                detalleSubtipoConservacion.value = '';
                if (otroSubtipoConservacion) {
                    otroSubtipoConservacion.value = '';
                }
            }

            actualizarSubtipoActualizacion();
            actualizarDetalleSubtipoConservacion();
            alternarOtrosProcesos();
        }

        function actualizarSubtipoActualizacion() {
            const opciones = opcionesPorMutacion[mutacionTramite.value] || [];
            const opcionesConOtro = opciones.length > 0 ? opciones.concat(['Otros Procesos']) : [];
            subtipoActualizacion.innerHTML = '<option value="" disabled selected><b>Seleccione</b></option>';

            opcionesConOtro.forEach(function(opcion) {
                const option = document.createElement('option');
                option.value = opcion;
                option.textContent = opcion;
                subtipoActualizacion.appendChild(option);
            });

            const mostrar = tipoTramite.value === 'ACTUALIZACION' && opcionesConOtro.length > 0;
            grupoSubtipoActualizacion.style.display = mostrar ? '' : 'none';
            subtipoActualizacion.required = mostrar;

            if (!mostrar) {
                subtipoActualizacion.value = '';
            }
        }

        function actualizarDetalleSubtipoConservacion() {
            const opciones = detallesPorConservacion[subtipoConservacion.value] || [];
            detalleSubtipoConservacion.innerHTML = '<option value="" disabled selected><b>Seleccione</b></option>';

            opciones.forEach(function(opcion) {
                const option = document.createElement('option');
                option.value = opcion;
                option.textContent = opcion;
                detalleSubtipoConservacion.appendChild(option);
            });

            const mostrar = tipoTramite.value === 'CONSERVACION' && opciones.length > 0;
            grupoDetalleSubtipoConservacion.style.display = mostrar ? '' : 'none';
            detalleSubtipoConservacion.required = mostrar;

            if (!mostrar) {
                detalleSubtipoConservacion.value = '';
            }
        }

        function alternarOtrosProcesos() {
            const mostrarOtroActualizacion = tipoTramite.value === 'ACTUALIZACION' &&
                (mutacionTramite.value === 'Otro' || subtipoActualizacion.value === 'Otros Procesos');
            const mostrarOtroConservacion = tipoTramite.value === 'CONSERVACION' &&
                subtipoConservacion.value === 'Otros_Procesos';

            if (grupoOtroProcesoActualizacion) {
                grupoOtroProcesoActualizacion.style.display = mostrarOtroActualizacion ? '' : 'none';
            }
            if (otroProcesoActualizacion) {
                otroProcesoActualizacion.required = mostrarOtroActualizacion;
            }
            if (grupoOtroSubtipoConservacion) {
                grupoOtroSubtipoConservacion.style.display = mostrarOtroConservacion ? '' : 'none';
            }
            if (otroSubtipoConservacion) {
                otroSubtipoConservacion.required = mostrarOtroConservacion;
            }

            if (!mostrarOtroActualizacion && otroProcesoActualizacion) {
                otroProcesoActualizacion.value = '';
            }

            if (!mostrarOtroConservacion && otroSubtipoConservacion) {
                otroSubtipoConservacion.value = '';
            }
        }

        tipoTramite.addEventListener('change', alternarSubtipoTramite);
        mutacionTramite.addEventListener('change', function() {
            actualizarSubtipoActualizacion();
            alternarOtrosProcesos();
        });
        subtipoActualizacion.addEventListener('change', alternarOtrosProcesos);
        subtipoConservacion.addEventListener('change', function() {
            actualizarDetalleSubtipoConservacion();
            alternarOtrosProcesos();
        });
        alternarSubtipoTramite();
    });

    const formTramite = document.getElementById("form-tramite");
    if (formTramite) {
        formTramite.addEventListener("submit", function(e) {
            e.preventDefault();

            const loadingScreen = document.getElementById("loading-screen");
            const submitBtn = document.getElementById("btn-submit");

            loadingScreen.style.display = "flex";
            submitBtn.classList.add("enviando");
            submitBtn.disabled = true;

            const formData = new FormData(this);

            fetch("index.php?page=tramites/cargue_tramite_rad", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = "index.php?page=tramites/consultar_tramite?codigo=" + encodeURIComponent(data.codigo);
                    } else {
                        loadingScreen.style.display = "none";
                        submitBtn.classList.remove("enviando");
                        submitBtn.disabled = false;
                        alert("Error: " + data.message);
                    }
                })
                .catch(err => {
                    loadingScreen.style.display = "none";
                    submitBtn.classList.remove("enviando");
                    submitBtn.disabled = false;
                    alert("Ocurrió un error en el servidor.");
                    console.error(err);
                });
        });
    }
</script>

<script>
    let predioBloqueadoActual = null;
    let consultaBloqueoTimer = null;
    let envioTramiteAutorizado = false;

    function ocultarAlertaPredioBloqueado() {
        predioBloqueadoActual = null;
        document.getElementById('alertaPredioBloqueadoTramite')?.classList.add('d-none');
    }

    function mostrarAlertaPredioBloqueado(bloqueo) {
        predioBloqueadoActual = bloqueo;
        const alerta = document.getElementById('alertaPredioBloqueadoTramite');
        const detalle = document.getElementById('detallePredioBloqueadoTramite');
        alerta?.classList.remove('d-none');
        if (detalle) {
            const identificador = bloqueo.npn || bloqueo.fmi || '';
            detalle.textContent = [
                identificador ? `Predio: ${identificador}.` : '',
                bloqueo.motivo ? `Motivo: ${bloqueo.motivo}.` : '',
                bloqueo.fecha_bloqueo ? `Fecha: ${bloqueo.fecha_bloqueo}.` : ''
            ].filter(Boolean).join(' ');
        }
    }

    async function consultarBloqueoPredioFormulario() {
        const npn = document.getElementById('npn_predio')?.value.trim() || '';
        const fmi = document.getElementById('fmi_predio')?.value.trim() || '';
        if (!npn && !fmi) {
            ocultarAlertaPredioBloqueado();
            return false;
        }

        const parametros = new URLSearchParams({
            npn,
            fmi
        });
        try {
            const respuesta = await fetch(
                `vistas/restricciones_predios/acciones/consultar_estado.php?${parametros.toString()}`, {
                    cache: 'no-store'
                }
            );
            const data = await respuesta.json();
            if (respuesta.ok && data.bloqueado) {
                mostrarAlertaPredioBloqueado(data.bloqueo || {});
                return true;
            }
            ocultarAlertaPredioBloqueado();
            return false;
        } catch (error) {
            console.error('Error consultando bloqueo del predio:', error);
            return false;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        ['npn_predio', 'fmi_predio'].forEach(function(id) {
            const campo = document.getElementById(id);
            campo?.addEventListener('input', function() {
                clearTimeout(consultaBloqueoTimer);
                consultaBloqueoTimer = setTimeout(consultarBloqueoPredioFormulario, 350);
            });
            campo?.addEventListener('change', consultarBloqueoPredioFormulario);
        });

        const formulario = document.getElementById('miFormulario');
        const botonCrearTramite = document.getElementById('btnCrearTramite');
        const loadingCrearTramite = document.getElementById('crearTramiteLoading');

        function mostrarEstadoCreacion() {
            if (botonCrearTramite) {
                botonCrearTramite.disabled = true;
                botonCrearTramite.classList.add('is-submitting');
                botonCrearTramite.innerHTML = '<b class="crear-tramite-submit-text"><span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>CREANDO TRAMITE...</b>';
            }

            if (loadingCrearTramite) {
                loadingCrearTramite.classList.add('is-visible');
                loadingCrearTramite.setAttribute('aria-hidden', 'false');
            }
        }

        formulario?.addEventListener('submit', async function(evento) {
            if (envioTramiteAutorizado) return;
            evento.preventDefault();

            const bloqueado = await consultarBloqueoPredioFormulario();
            if (bloqueado) {
                const bloqueo = predioBloqueadoActual || {};
                await Swal.fire({
                    icon: 'error',
                    title: 'Predio bloqueado',
                    text: `Este predio está bloqueado y no se permite crear el trámite. Motivo: ${String(bloqueo.motivo || 'Restricción activa')}`,
                    confirmButtonColor: '#b42318'
                });
                return;
            }

            envioTramiteAutorizado = true;
            mostrarEstadoCreacion();
            formulario.submit();
        });
    });
</script>
