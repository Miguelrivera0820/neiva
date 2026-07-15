<?php
// Puedes usar este título si en tu layout lo muestras en el breadcrumb o encabezado
$titulo_pagina = "Contratación de Personal";
?>

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
            flex-direction: column;
            align-items: center;
            /* Centrar los botones */
        }

        .step {
            width: 100%;
            /* Ocupa todo el ancho */
            text-align: center;
            margin: 10px 0;
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
        <h1 class="h3  text-center mb-0" style="color:#002F55"><b>INFORMACION PERSONAL</b></h1>
        <small class="text-muted">Formulario paso a paso para incorporar nuevos cooperadores</small>
    </div>

    <!-- Navegación de secciones (botones)--Mejorado -->
    <!-- Barra de fondo -->
    <div class="stepper-wrapper mb-0">
        <!-- Barra de fondo -->
        <div class="stepper-line">
            <div id="stepper-progress"></div>
        </div>

        <!-- Botones -->
        <div class="stepper">
            <button type="button" class="step active">Datos Personales</button>
            <button type="button" class="step">Núcleo Familiar</button>
            <button type="button" class="step">Datos Financieros</button>
            <button type="button" class="step">Historial Académico</button>
            <button type="button" class="step">Vinculación Contractual</button>
            <button type="button" class="step">Información Sociodemográfica</button>
            <button type="button" class="step">Historial de Vacunación</button>
            <button type="button" class="step">Otro Sí</button>
            <button type="button" class="step">Experiencia Laboral</button>
        </div>
    </div>

    <!-- Card principal del formulario -->
    <div class="card card-especial-tres rounded-4 shadow-lg mx-3  border-0 my-3">

        <div class="card-body p-0 mb-3">

            <!-- FORMULARIO PRINCIPAL -->
            <form id="formContratacion"
                action="/arbimaps/Arbimaps/vistas/Personal/guardar_contratacion.php"
                method="POST"
                enctype="multipart/form-data">


                <!-- ========== SECCIÓN 1: DATOS PERSONALES ========== -->
                <div id="section1" class="seccion-formulario ">
                    <div class="mb-3 d-flex align-items-center text-start rounded-4 card-header shadow m-2 py-3 text-white">
                        <i class="bi bi-person-circle me-3 fs-2"></i>
                        <div>
                            <h3 class="h5 mb-1">Datos personales</h3>
                            <p class=" mb-0" style="font-size: 0.85rem; color:#999999">
                                Información básica del colaborador.
                            </p>
                        </div>
                    </div>

                    <div class="row p-4 g-3">
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_nombres" class="form-label fw-bold" style="font-size:0.9em;">Nombres</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="con_nombres" name="con_nombres"
                                    placeholder="Ingrese los nombres..." name="con_nombres" aria-label="Nombres"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_apellidos" class="form-label fw-bold" style="font-size:0.9em;">Apellidos</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm" id="basic-addon1"><i class="bi-people"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="con_apellidos" placeholder="Ingrese los apellidos..."
                                    name="con_apellidos" aria-label="PrimerApellido" aria-describedby="basic-addon1">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2 ">
                            <label for="con_tipo_documento" class="form-label" style="font-size:0.9em;"><b>Tipo de documento</b></label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_tipo_documento">
                                    <i class="bi-person-badge"></i>
                                </label>
                                <select class="form-select" style="font-size:0.9em;" id="con_tipo_documento" name="con_tipo_documento" required>
                                    <option value="" disabled selected>Selecciona el tipo de documento</option>
                                    <option value="CEDULA_CIUDADANIA">Cédula de ciudadanía</option>
                                    <option value="CEDULA_EXTRANJERA">Cédula de extranjería</option>
                                    <option value="NIT">N.I.T</option>
                                    <option value="PASAPORTE">Pasaporte</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_num_identidad" class="form-label fw-bold" style="font-size:0.9em;">Número de identidad</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-vcard"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="con_num_identidad" name="con_num_identidad" placeholder="Ingrese el número de documento..." name="cert_primer_nombre" aria-label="PrimerNombre" aria-describedby="basic-addon1">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_FechaExpe" class="form-label fw-bold" style="font-size:0.9em;">Fecha de Expedición del Documento</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar2-event"></i></span>
                                <input type="date" class="form-control shadow-sm" style="font-size:0.9em;" id="con_FechaExpe" name="con_FechaExpe" name="fechaExpe" aria-label="fecha" aria-describedby="basic-addon1">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_lugarE" class="form-label fw-bold" style="font-size:0.9em;">Lugar de expedición</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-geo"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="con_lugarE"
                                    placeholder="Ingrese la ciudad de expedición" name="con_lugarE" name="lugar" aria-label="lugar" aria-describedby="basic-addon1">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_fecha_nacimiento" class="form-label fw-bold" style="font-size:0.9em;">Fecha de nacimiento</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar-date"></i></span>
                                <input type="date" class="form-control shadow-sm" style="font-size:0.9em;" id="con_fecha_nacimiento" name="con_fecha_nacimiento" name="fechaExpe" aria-label="fecha" aria-describedby="basic-addon1">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_edad" class="form-label fw-bold" style="font-size:0.9em;">Edad</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-cake"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="con_edad" name="con_edad" aria-label="PrimerNombre" aria-describedby="basic-addon1">
                            </div>
                            <small class="p-1 mt-1 bg-success rounded-2 text-center tilt" style="color: #ffffffff;">⚠ Se calcula automáticamente </small>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_lugar_nacimiento" class="form-label fw-bold" style="font-size:0.9em;">Lugar de nacimiento</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-pin-map-fill"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="con_lugar_nacimiento"
                                    name="con_lugar_nacimiento" placeholder="Ciudad de nacimiento" aria-label="PrimerNombre" aria-describedby="basic-addon1">
                            </div>
                        </div>

                        <div class="col-12 s my-4" style="border-bottom:2px dashed #002f557a"></div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_direccion" class="form-label fw-bold" style="font-size:0.9em;">Dirección</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-geo-alt"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="con_direccion"
                                    name="con_direccion" placeholder="Dirección de residencia" aria-label="PrimerNombre" aria-describedby="basic-addon1">
                            </div>
                        </div>


                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_barrio" class="form-label fw-bold" style="font-size:0.9em;">Barrio</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-houses"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="con_barrio"
                                    name="con_barrio" placeholder="Barrio" aria-label="Barrio" aria-describedby="basic-addon1">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_ciudad" class="form-label fw-bold" style="font-size:0.9em;">Ciudad</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-houses"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="con_ciudad"
                                    name="con_ciudad" placeholder="Ciudad" aria-label="Ciudad" aria-describedby="basic-addon1">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_tel_fijo" class="form-label fw-bold" style="font-size:0.9em;">Teléfono fijo</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-telephone"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="con_tel_fijo"
                                    name="con_tel_fijo" placeholder="Teléfono fijo (opcional)" aria-label="Ciudad" aria-describedby="basic-addon1">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_celular" class="form-label fw-bold" style="font-size:0.9em;">Celular</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-phone"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="con_celular"
                                    name="con_celular" placeholder="Número de celular" aria-label="Ciudad" aria-describedby="basic-addon1">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_correo" class="form-label fw-bold" style="font-size:0.9em;">Correo electrónico personal</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at"></i></span>
                                <input type="email" class="form-control" id="con_correo" placeholder="correo@ejemplo.com"
                                    name="con_correo" aria-label="PrimerNombre">
                            </div>
                        </div>

                        <div class="col-md-5 mx-auto p-1 px-2 my-2">
                            <label for="con_correo_corporativo" class="form-label fw-bold" style="font-size:0.9em;">Correo electrónico corporativo</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at"></i></span>
                                <input type="email" class="form-control" id="con_correo_corporativo" placeholder="correo.corporativo@empresa.com"
                                    name="con_correo_corporativo" aria-label="PrimerNombre">
                            </div>
                        </div>

                        <div class="col-12  my-4" style="border-bottom:2px dashed #002f557a"></div>

                        <div class="col-12">
                            <h6 class="fw-bold p-2 text-white text-center w-25 rounded-3" style="background-color: #002F55;">Documentos</h6>
                        </div>

                        <div class="col-12 col-lg-6  p-1 px-3 my-3">
                            <label for="con_cedula" class="form-label fw-bold">Cédula (PDF / Imagen)</label>
                            <div class="input-group mb-1 shadow-sm">
                                <label class="input-group-text" for="con_cedula" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
                                <input type="file" class="form-control" style="font-size:0.8em;" id="con_cedula" name="con_cedula">
                            </div>
                            <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                        </div>


                        <div class="col-12 col-lg-6  p-1 px-3 my-3">
                            <label for="con_rut" class="form-label fw-bold">RUT</label>
                            <div class="input-group mb-1 shadow-sm">
                                <label class="input-group-text" for="con_rut" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
                                <input type="file" class="form-control" style="font-size:0.8em;" id="con_rut" name="con_rut">
                            </div>
                            <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                        </div>

                        <div class="col-12 col-lg-6  p-1 px-3 my-3">
                            <label for="con_antecedentes" class="form-label fw-bold">Antecedentes Judiciales</label>
                            <div class="input-group mb-1 shadow-sm">
                                <label class="input-group-text" for="con_antecedentes" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
                                <input type="file" class="form-control" style="font-size:0.8em;" id="con_antecedentes" name="con_antecedentes">
                            </div>
                            <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                        </div>

                        <div class="col-12 col-lg-6  p-1 px-3 my-3">
                            <label for="con_contraloria" class="form-label fw-bold">Antecedentes Contraloria</label>
                            <div class="input-group mb-1 shadow-sm">
                                <label class="input-group-text" for="con_contraloria" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
                                <input type="file" class="form-control" style="font-size:0.8em;" id="con_contraloria" name="con_contraloria">
                            </div>
                            <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                        </div>

                        <div class="col-12 col-lg-6  p-1 px-3 my-3">
                            <label for="con_procuraduria" class="form-label fw-bold">Antecedentes Procuraduria</label>
                            <div class="input-group mb-1 shadow-sm">
                                <label class="input-group-text" for="con_procuraduria" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
                                <input type="file" class="form-control" style="font-size:0.8em;" id="con_procuraduria" name="con_procuraduria">
                            </div>
                            <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                        </div>

                        <div class="col-12 col-lg-6  p-1 px-3 my-3">
                            <label for="con_hoja_vida" class="form-label fw-bold">Hoja de vida</label>
                            <div class="input-group mb-1 shadow-sm">
                                <label class="input-group-text" for="con_hoja_vida" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
                                <input type="file" class="form-control" style="font-size:0.8em;" id="con_hoja_vida" name="con_hoja_vida">
                            </div>
                        </div>

                        <div class="col-md-6 text-center mx-auto">
                            <label class="form-label fw-bold">Foto del colaborador</label>
                            <div class="border rounded-3 p-3 text-center" style="border-style: dashed;">
                                <label for="photo" class="text-muted mb-2" style="cursor:pointer;">
                                    <i class="bi bi-cloud-arrow-up fs-3 d-block mb-1"></i>
                                    <span>Haz clic para seleccionar una foto</span>
                                </label>
                                <input type="file" id="photo" name="photo" accept="image/*" class="d-none">
                                <div id="previewFoto" class="mt-2"></div>
                            </div>
                        </div>

                    </div>

                    <!-- Navegación entre secciones -->
                    <div class="d-flex justify-content-end mt-4 mx-3">
                        <button type="button" class="btn text-white btn-next-section" style="background-color: #002F55;" data-next="section2">
                            Siguiente
                            <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>


                <!-- ========== SECCIÓN 2: INFORMACION FAMILIAR  ========== -->
                <div id="section2" class="seccion-formulario">
                    <div class="mb-3 d-flex align-items-center text-start rounded-4 card-header shadow m-2 py-3 text-white">
                        <i class="bi bi-people-fill me-3 fs-2"></i>
                        <div>
                            <h3 class="h5 mb-1">Núcleo familiar</h3>
                            <p class=" mb-0" style="font-size: 0.85rem; color:#999999">
                                Información básica familiar del colaborador.
                            </p>
                        </div>
                    </div>

                    <div class="row p-4 g-3">

                        <div class="col-md-4 p-1 px-2 my-2 ">
                            <label for="con_estado_civil" class="form-label" style="font-size:0.9em;"><b>Estado civil</b></label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_estado_civil">
                                    <i class="bi bi-heart"></i>
                                </label>
                                <select class="form-select" style="font-size:0.9em;" id="con_estado_civil" name="con_estado_civil" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="SOLTERO">SOLTERO</option>
                                    <option value="CASADO">CASADO</option>
                                    <option value="UNIÓN LIBRE">UNIÓN LIBRE</option>
                                    <option value="VIUDO">VIUDO</option>
                                    <option value="SEPARADO">SEPARADO</option>
                                    <option value="DIVORCIADO">DIVORCIADO</option>
                                    <option value="UNIÓN DE HECHO">UNIÓN DE HECHO</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_nombre_conyuge" class="form-label fw-bold" style="font-size:0.9em;">Nombre del cónyuge</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm" id="basic-addon1"><i class="bi-people"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="con_nombre_conyuge" placeholder="Ingrese nombre..."
                                    name="con_nombre_conyuge" aria-label="nombre_conyuge" aria-describedby="basic-addon1">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_enfermedades" class="form-label fw-bold" style="font-size:0.9em;">Enfermedades</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm" id="basic-addon1"><i class="bi bi-bandaid"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="con_enfermedades" placeholder="Ingresa las enfermedades"
                                    name="con_enfermedades" aria-label="enfermedades" aria-describedby="basic-addon1">
                            </div>
                            <small class="text-muted">Si no tienes, escribe: NINGUNA</small>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label class="form-label fw-bold" style="font-size:0.9em;">Alergias</label>
                            <input type="hidden" id="con_alergias" name="con_alergias">
                            <div id="alergiasContainer">
                                <div class="input-group mb-2 alergia-row">
                                    <span class="input-group-text shadow-sm"><i class="bi bi-bandaid"></i></span>
                                    <input type="text"
                                        class="form-control shadow-sm alergia-input"
                                        style="font-size:0.9em;"
                                        id="con_alergias_0"
                                        placeholder="Ej: POLEN">
                                    <button type="button" class="btn btn-outline-primary btn-add-alergia" title="Agregar">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted">Si no tienes, escribe: NINGUNA</small>
                        </div>



                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_medicamentos" class="form-label fw-bold" style="font-size:0.9em;">Medicamentos</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm" id="basic-addon1"><i class="bi bi-prescription2"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="con_medicamentos" placeholder="Menciona los medicamentos"
                                    name="con_medicamentos" aria-label="medicamentos" aria-describedby="basic-addon1">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_emergencia" class="form-label fw-bold" style="font-size:0.9em;">Contacto de emergencia</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm" id="basic-addon1"><i class="bi bi-person-exclamation"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="con_emergencia" placeholder="Ingrese el nombre del contacto de emergencia"
                                    name="con_emergencia" aria-label="contacto de emergencia" aria-describedby="basic-addon1">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_parentesco" class="form-label fw-bold" style="font-size:0.9em;">Parentesco </label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm" id="basic-addon1"><i class="bi bi-person-badge"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="con_parentesco" placeholder="Menciona el parentesco con el contacto de emergencia"
                                    name="con_parentesco" aria-label="parentesco" aria-describedby="basic-addon1">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_tel_emergencia" class="form-label fw-bold" style="font-size:0.9em;">Número telefónico </label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm" id="basic-addon1"><i class="bi bi-telephone-x"></i></span>
                                <input type="number" class="form-control shadow-sm" style="font-size:0.9em;" id="con_tel_emergencia" placeholder="ingresa el número telefónico"
                                    name="con_tel_emergencia" aria-label="contacto emergencia" aria-describedby="basic-addon1">
                            </div>
                        </div>

                        <div class="col-12  my-4" style="border-bottom:2px dashed #002f557a"></div>

                        <div class="col-md-12 p-1 px-2 my-2">
                            <label for="con_contingencia"
                                class="form-label fw-bold text-center w-100"
                                style="font-size:0.9em;">
                                Plan de contingencia
                            </label>
                            <div class="input-group">
                                <span class="input-group-text shadow-sm"><i class="bi bi-exclamation-triangle"></i></span>
                                <textarea
                                    class="form-control shadow-sm text-center"
                                    style="font-size:0.9em; resize:none;"
                                    id="con_contingencia"
                                    name="con_contingencia"
                                    placeholder="Ingrese la contingencia..."
                                    rows="4"></textarea>
                            </div>
                            <small class="text-muted d-block text-center">
                                Indique el protocolo o acción a seguir en caso de emergencia médica (alergias, enfermedades, etc.).
                            </small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4 mx-3">

                        <!-- ATRÁS -->
                        <button type="button"
                            class="btn  btn-prev-section text-white px-4 d-inline-flex align-items-center" style="background-color: #002F55;"
                            data-prev="section1">
                            <i class="bi bi-arrow-left me-2"></i>
                            Atrás
                        </button>

                        <!-- SIGUIENTE -->
                        <button type="button"
                            class="btn  btn-next-section text-white px-4 d-inline-flex align-items-center" style="background-color: #002F55;"
                            data-next="section3">
                            Siguiente
                            <i class="bi bi-arrow-right ms-2"></i>
                        </button>

                    </div>

                </div>

                <!-- ========== SECCIÓN 3: INFORMACION BANCARIA  ========== -->
                <div id="section3" class="seccion-formulario">

                    <div class="mb-3 d-flex align-items-center text-start rounded-4 card-header shadow m-2 py-3 text-white">
                        <i class="bi bi-coin me-3 fs-2"></i>
                        <div>
                            <h3 class="h5 mb-1">Datos financieros</h3>
                            <p class=" mb-0" style="font-size: 0.85rem; color:#999999">
                                Debes llenar todos los campos.
                            </p>
                        </div>
                    </div>

                    <div class="row p-4 g-3">

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_num_cuenta" class="form-label fw-bold" style="font-size:0.9em;">Nº Cuenta Bancaria</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-bank"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="con_num_cuenta" name="con_num_cuenta"
                                    placeholder="ingresa el número de la cuenta bancaria" name="con_nombres" aria-label="Nombres"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2 ">
                            <label for="con_tipo_cuenta" class="form-label" style="font-size:0.9em;"><b>Tipo de Cuenta</b></label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_tipo_cuenta">
                                    <i class="bi bi-safe"></i>
                                </label>
                                <select class="form-select" style="font-size:0.9em;" id="con_tipo_cuenta" name="con_tipo_cuenta" required>
                                    <option value="" disabled selected>Selecciona</option>
                                    <option value="AHORROS">AHORROS</option>
                                    <option value="CORRIENTE">CORRIENTE</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2 ">
                            <label for="con_financiera" class="form-label" style="font-size:0.9em;"><b>Entidad Financiera</b></label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_financiera">
                                    <i class="bi bi-bank2"></i>
                                </label>
                                <select class="form-select" style="font-size:0.9em;" id="con_financiera" name="con_financiera" required>
                                    <option value="" disabled selected>Selecciona</option>
                                    <option value="BANCOLOMBIA">BANCOLOMBIA</option>
                                    <option value="DAVIVIENDA">DAVIVIENDA</option>
                                    <option value="BANCO DE BOGOTA S.A.">BANCO DE BOGOTÁ S.A.</option>
                                    <option value="BBVA COLOMBIA">BBVA COLOMBIA</option>
                                    <option value="BANCO DE OCCIDENTE">BANCO DE OCCIDENTE</option>
                                    <option value="BANCO COLPATRIA">BANCO COLPATRIA</option>
                                    <option value="BANCO AGRARIO DE COLOMBIA">BANCO AGRARIO DE COLOMBIA</option>
                                    <option value="BANCAMIA S.A.">BANCAMÍA S.A.</option>
                                    <option value="BANCO W S.A">BANCO W S.A.</option>
                                    <option value="BANCO FALABELLA S.A.">BANCO FALABELLA S.A.</option>
                                    <option value="BANCO UNION S.A">BANCO UNIÓN S.A</option>
                                    <option value="BANCO MUNDO MUJER">BANCO MUNDO MUJER</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12  my-4" style="border-bottom:2px dashed #002f557a"></div>
                        <div class="col-12 mt-0">
                            <h6 class="fw-bold p-2 text-white text-center w-25 rounded-3" style="background-color: #002F55;">Documentos</h6>
                        </div>

                        <div class="col-12 col-lg-7 mx-auto  p-1 px-2 my-2">
                            <label for="con_bancario" class="form-label fw-bold">Certificado Bancario</label>
                            <div class="input-group mb-1 shadow-sm">
                                <label class="input-group-text" for="con_bancario" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
                                <input type="file" class="form-control" style="font-size:0.8em;" id="con_bancario" name="con_bancario">
                            </div>
                        </div>

                        <div class="mb-3 d-flex align-items-center text-start rounded-4 card-header shadow m-2 py-3 text-white">
                            <i class="bi bi-coin me-3 fs-2"></i>
                            <div>
                                <h3 class="h5 mb-1">Seguridad social</h3>
                                <p class=" mb-0" style="font-size: 0.85rem; color:#999999">
                                    Debes llenar todos los campos.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2 ">
                            <label for="con_eps" class="form-label" style="font-size:0.9em;"><b>EPS</b></label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_eps">
                                    <i class="bi bi-hospital"></i>
                                </label>
                                <select class="form-select" style="font-size:0.9em;" id="con_eps" name="con_eps" required>
                                    <option value="" disabled selected>Selecciona </option>
                                    <option value="NUEVA_EPS">NUEVA EPS</option>
                                    <option value="SANITAS">SANITAS</option>
                                    <option value="EMSSANAR">EMSSANAR</option>
                                    <option value="MALLAMAS">MALLAMAS</option>
                                    <option value="ASMET_SALUD">ASMET SALUD</option>
                                    <option value="FAMISANAR">FAMISANAR</option>
                                    <option value="SURA">SURA</option>
                                    <option value="OTRO">OTRO</option>
                                </select>
                                <input type="text" class="form-control mt-2" id="otra_eps" name="otra_eps" placeholder="Escriba la EPS" style="display:none;">
                            </div>
                        </div>
                        <div class="col-md-4 p-1 px-2 my-2 ">
                            <label for="con_arl" class="form-label" style="font-size:0.9em;"><b>ARL</b></label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_arl">
                                    <i class="bi bi-cone-striped"></i>
                                </label>
                                <select class="form-select" style="font-size:0.9em;" id="con_arl" name="con_arl" required>
                                    <option value="" disabled selected>Selecciona </option>
                                    <option value="SURA">SURA</option>
                                    <option value="POSITIVA">POSITIVA</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 p-1 px-2 my-2 ">
                            <label for="con_afp" class="form-label" style="font-size:0.9em;"><b>AFP</b></label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_afp">
                                    <i class="bi bi-clipboard"></i>
                                </label>
                                <select class="form-select" style="font-size:0.9em;" id="con_afp" name="con_afp" required>
                                    <option value="" disabled selected>Selecciona </option>
                                    <option value="PORVENIR">PORVENIR</option>
                                    <option value="COLFONDOS">COLFONDOS</option>
                                    <option value="COLPENSIONES">COLPENSIONES</option>
                                    <option value="PROTECCIÓN">PROTECCIÓN</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12  my-4" style="border-bottom:2px dashed #002f557a"></div>

                        <div class="col-12 mt-0">
                            <h6 class="fw-bold p-2 text-white text-center w-25 rounded-3" style="background-color: #002F55;">Documentos</h6>
                        </div>

                        <div class="col-12 col-lg-6  p-1 px-2 my-2">
                            <label for="con_certificado_eps" class="form-label fw-bold">Certificado EPS</label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_certificado_eps" style="font-size:0.9em;"><i class="bi bi-file-earmark-pdf"></i></label>
                                <input type="file" class="form-control" style="font-size:0.9em;" id="con_certificado_eps" name="con_certificado_eps">
                            </div>
                        </div>

                        <div class="col-12 col-lg-6  p-1 px-2 my-2">
                            <label for="con_arlCer" class="form-label fw-bold">Certificado ARL</label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_arlCer" style="font-size:0.9em;"><i class="bi bi-file-earmark-pdf"></i></label>
                                <input type="file" class="form-control" style="font-size:0.9em;" id="con_arlCer" name="con_arlCer">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4 mx-3">

                        <!-- ATRÁS -->
                        <button type="button"
                            class="btn text-white btn-prev-section px-4 d-inline-flex align-items-center" style="background-color: #002F55;"
                            data-prev="section2">
                            <i class="bi bi-arrow-left me-2"></i>
                            Atrás
                        </button>

                        <!-- SIGUIENTE -->
                        <button type="button"
                            class="btn text-white btn-next-section px-4 d-inline-flex align-items-center" style="background-color: #002F55;"
                            data-next="section4">
                            Siguiente
                            <i class="bi bi-arrow-right ms-2"></i>
                        </button>

                    </div>

                </div>

                <!-- ========== SECCIÓN 4: INFORMACION ACADEMICA  ========== -->
                <div id="section4" class="seccion-formulario">

                    <div class="mb-3 d-flex align-items-center text-start rounded-4 card-header shadow m-2 py-3 text-white">
                        <i class="bi bi-mortarboard me-3 fs-2"></i>
                        <div>
                            <h3 class="h5 mb-1">Historial académico</h3>
                            <p class=" mb-0" style="font-size: 0.85rem; color:#999999">
                                Información de la formación del colabo. Por favor llene la información de nivel cursado.
                            </p>
                        </div>
                    </div>


                    <div class="row p-4 g-3 justify-content-center">

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_profesion" class="form-label fw-bold" style="font-size:0.9em;">Profesión</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-raised-hand"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="con_profesion" name="con_profesion"
                                    placeholder="Ingrese la profesión del colaborador..." aria-label="Nombres"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2 ">
                            <label for="con_escolaridad" class="form-label" style="font-size:0.9em;"><b>Escolaridad</b></label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_escolaridad">
                                    <i class="bi bi-backpack2"></i>
                                </label>
                                <select class="form-select" style="font-size:0.9em;" id="con_escolaridad" name="con_escolaridad" required>
                                    <option value="" disabled selected>Selecciona</option>
                                    <option value="BACHILLER">BACHILLER</option>
                                    <option value="TÉCNICO">TÉCNICO</option>
                                    <option value="TECNÓLOGO">TECNÓLOGO</option>
                                    <option value="PROFESIONAL">PROFESIONAL</option>
                                    <option value="ESPECIALISTA">ESPECIALISTA</option>
                                    <option value="GRADO">GRADO</option>
                                    <option value="PREGRADO">PREGRADO</option>
                                    <option value="MAGISTER">MAGÍSTER</option>
                                    <option value="DOCTORADO">DOCTORADO</option>
                                    <option value="ESPECIALIZACION">ESPECIALIZACIÓN</option>
                                    <option value="CONSOLIDACION">CONSOLIDACIÓN</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_grado" class="form-label fw-bold" style="font-size:0.9em;">Fecha de graduación</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar2-event"></i></span>
                                <input type="date" class="form-control shadow-sm" style="font-size:0.9em;" id="con_grado" name="con_grado" aria-label="fecha" aria-describedby="basic-addon1">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_num_tarjeta" class="form-label fw-bold" style="font-size:0.9em;">N° Tarjeta Profesional</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-vcard"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="con_num_tarjeta" name="con_num_tarjeta" placeholder="Ingrese el número de la tarjeta profesional..." name="cert_primer_nombre" aria-label="PrimerNombre" aria-describedby="basic-addon1">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_expedicion" class="form-label fw-bold" style="font-size:0.9em;">Fecha de Expedición de Tarjeta Profesional</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar-date"></i></span>
                                <input type="date" class="form-control shadow-sm" style="font-size:0.9em;" id="con_expedicion" name="con_expedicion" name="fechaExpe" aria-label="fecha" aria-describedby="basic-addon1">
                            </div>
                        </div>

                        <div class="col-12  my-4" style="border-bottom:2px dashed #002f557a"></div>

                        <div class="col-12 mt-0">
                            <h6 class="fw-bold p-2 text-white text-center w-25 rounded-3" style="background-color: #002F55;">Documentos</h6>
                        </div>

                        <div class="col-12 col-lg-6  p-1 px-3 my-3">
                            <label for="con_tarjeta" class="form-label fw-bold">Tarjeta profesional</label>
                            <div class="input-group mb-1 shadow-sm">
                                <label class="input-group-text" for="con_tarjeta" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
                                <input type="file" class="form-control" style="font-size:0.8em;" id="con_tarjeta" name="con_tarjeta">
                            </div>
                            <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                        </div>

                        <div class="col-12 col-lg-6  p-1 px-3 my-3">
                            <label for="con_cerTarjeta" class="form-label fw-bold">Certifcado Tarjeta profesional</label>
                            <div class="input-group mb-1 shadow-sm">
                                <label class="input-group-text" for="con_cerTarjeta" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
                                <input type="file" class="form-control" style="font-size:0.8em;" id="con_cerTarjeta" name="con_cerTarjeta">
                            </div>
                            <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                        </div>

                        <div class="col-12 my-4" style="border-bottom:2px dashed #002f557a"></div>

                        <div class="mb-3 d-flex align-items-center text-start rounded-4 card-header shadow m-2 py-3 text-white">
                            <i class="bi bi-mortarboard me-3 fs-2"></i>
                            <div>
                                <h3 class="h5 mb-1">Estudios</h3>
                                <p class=" mb-0" style="font-size: 0.85rem; color:#999999">
                                    Informacion de otros estudios. Por favor completa la informacion si cuenta con otros estudios.
                                </p>
                            </div>
                        </div>

                        <div class="col-12 mt-0">
                            <h6 class="fw-bold p-2 text-white text-center w-25 rounded-3" style="background-color: #002F55;">
                                Soportes académicos
                            </h6>
                        </div>

                        <div class="col-12 mb-2">
                            <div class="otrosi-title">Carga de Información</div>

                            <div class="otrosi-box text-center">
                                <label class="form-label mb-2" for="est_estudios">Agregar estudios</label>

                                <div class="d-inline-flex align-items-center gap-2 otrosi-add-wrapper">
                                    <input type="number" id="est_estudios" min="1" class="form-control otrosi-input-number" />
                                    <button type="button" class="btn btn-otrosi-add" onclick="crearEstudios()">
                                        <span class="fw-bold">+</span>
                                    </button>
                                </div>

                                <!-- Si necesitas el con_id aquí, deja esta línea (solo si existe $con_id) -->
                                <input type="hidden" id="con_id" value="<?php echo $con_id; ?>">
                            </div>
                        </div>

                        <div class="col-12 mt-3">
                            <div id="estudios"></div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">

                            <!-- ATRÁS -->
                            <button type="button"
                                class="btn text-white btn-prev-section px-4 d-inline-flex align-items-center" style="background-color: #002F55;"
                                data-prev="section3">
                                <i class="bi bi-arrow-left me-2"></i>
                                Atrás
                            </button>

                            <!-- SIGUIENTE -->
                            <button type="button"
                                class="btn text-white btn-next-section px-4 d-inline-flex align-items-center" style="background-color: #002F55;"
                                data-next="section5">
                                Siguiente
                                <i class="bi bi-arrow-right ms-2"></i>
                            </button>

                        </div>

                    </div>

                </div>

                <!-- ========== SECCIÓN 5: INFORMACION CONTRATUAL ========== -->
                <div id="section5" class="seccion-formulario">
                    <div class="mb-3 d-flex align-items-center text-start rounded-4 card-header shadow m-2 py-3 text-white">
                        <i class="bi bi-file-earmark-break me-3 fs-2"></i>
                        <div>
                            <h3 class="h5 mb-1">Vinculación Contractual</h3>
                            <p class=" mb-0" style="font-size: 0.85rem; color:#999999">
                                Información sobre la contratación del colaborador.
                            </p>
                        </div>
                    </div>
                    <div class="row p-4 g-3">
                        <div class="col-md-5 p-1 px-2 my-2 ">
                            <label for="con_sede" class="form-label" style="font-size:0.9em;"><b>Sede de Trabajo</b></label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_sede">
                                    <i class="bi bi-building"></i>
                                </label>
                                <select class="form-select" style="font-size:0.9em;" id="con_sede" name="con_sede" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="NEIVA">NEIVA</option>
                                    <option value="LEIVA">LEIVA</option>
                                    <option value="SAN_PEDRO">SAN_PEDRO</option>
                                    <option value="SAN_JUAN">SAN_JUAN</option>
                                    <option value="BELLO">BELLO</option>
                                    <option value="NECOCLI">NECOCLI</option>
                                    <option value="VALLE_GUAMUEZ">VALLE_GUAMUEZ</option>
                                    <option value="ARBOLETES">ARBOLETES</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label d-block">Presencialidad</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="con_presencialidad" id="presencialidad_si" value="SI">
                                <label class="form-check-label" for="presencialidad_si">SI</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="con_presencialidad" id="presencialidad_no" value="NO" checked>
                                <label class="form-check-label" for="presencialidad_no">NO</label>
                            </div>
                        </div>
                        <div class="col-md-5 p-1 px-2 my-2 ">
                            <label for="con_cargo" class="form-label" style="font-size:0.9em;"><b>Rol</b></label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_cargo">
                                    <i class="bi bi-file-person-fill"></i>
                                </label>
                                <select class="form-select" style="font-size:0.9em;" id="con_cargo" name="con_cargo" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="ABOGADOS">ABOGADOS</option>
                                    <option value="ASIGNADOR">ASIGNADOR</option>
                                    <option value="AUXILIAR_ADMINISTRATIVO">AUXILIAR_ADMINISTRATIVO</option>
                                    <option value="AUXILIAR_ADMINISTRATIVO_VENTANILLA_UNICA">AUXILIAR ADMINISTRATIVO VENTANILLA ÚNICA</option>
                                    <option value="AUXILIAR_ADMINISTRATIVO_Y_DE_OPERACIONES">AUXILIAR ADMINISTRATIVO Y DE OPERACIONES</option>
                                    <option value="AUXILIAR_DE_SERVICIOS_GENERALES">AUXILIAR DE SERVICIOS GENERALES</option>
                                    <option value="AUXILIAR_SOCIAL">AUXILIAR SOCIAL</option>
                                    <option value="AUXILIAR_VENTANILLA">AUXILIAR VENTANILLA</option>
                                    <option value="CONSOLIDADOR">CONSOLIDADOR</option>
                                    <option value="CONSULTA_VUR">CONSULTA VUR</option>
                                    <option value="CONTROL_DE_CALIDAD">CONTROL DE CALIDAD</option>
                                    <option value="COORDINADOR_CUADRILLA">COORDINADOR CUADRILLA</option>
                                    <option value="DESARROLLADOR">DESARROLLADOR</option>
                                    <option value="DIGITADOR">DIGITADOR</option>
                                    <option value="DIGITALIZADOR">DIGITALIZADOR</option>
                                    <option value="DIRECTOR_DE_OPERACIONES">DIRECTOR DE OPERACIONES</option>
                                    <option value="DIRECTOR_FINANCIERO">DIRECTOR FINANCIERO</option>
                                    <option value="DIRECTOR_PLANEACION">DIRECTOR PLANEACIÓN</option>
                                    <option value="DIRECTORA_COMERCIAL">DIRECTORA COMERCIAL</option>
                                    <option value="EDITOR">EDITOR</option>
                                    <option value="GERENTE">GERENTE</option>
                                    <option value="GESTORA_DE_TALENTO_HUMANO">GESTORA DE TALENTO HUMANO</option>
                                    <option value="HSEQ">HSEQ</option>
                                    <option value="JEFE_DE_OPERACIONES">JEFE DE OPERACIONES</option>
                                    <option value="LIDER_CONSOLIDACION">LÍDER CONSOLIDACIÓN</option>
                                    <option value="LIDER_CONSOLIDACION">LÍDER CONSOLIDACIÓN</option>
                                    <option value="LIDER_RECONOCIMIENTO">LÍDER RECONOCIMIENTO</option>
                                    <option value="LIDER_TECNICO">LÍDER TÉCNICO</option>
                                    <option value="MERGIN_MAPS">MERGIN MAPS</option>
                                    <option value="PRESUPUESTO">PRESUPUESTO</option>
                                    <option value="PROFESIONAL_2_CON_POSGRADO">PROFESIONAL 2 CON POSGRADO</option>
                                    <option value="PROFESIONAL_3_CON_POSGRADO">PROFESIONAL 3 CON POSGRADO</option>
                                    <option value="PROFESIONAL_HSEQ_CON_POSGRADO">PROFESIONAL HSEQ CON POSGRADO</option>
                                    <option value="PROFESIONAL_CATASTRAL_1">PROFESIONAL CATASTRAL 1</option>
                                    <option value="PROGRAMADORA_DE_SOFTWARE">PROGRAMADORA DE SOFTWARE</option>
                                    <option value="PROFESIONAL_SIG">PROFESIONAL SIG</option>
                                    <option value="PROFESIONAL_SOCIAL">PROFESIONAL_SOCIAL</option>
                                    <option value="RECONOCEDOR_PREDIAL">RECONOCEDOR PREDIAL</option>
                                    <option value="SOPORTE_NIVEL_1">SOPORTE NIVEL 1</option>
                                    <option value="SOPORTE_NIVEL_2">SOPORTE NIVEL 2</option>
                                    <option value="SUBGERENTE_GENERAL">SUBGERENTE GERENTE</option>
                                    <option value="TECNOLOGO">TECNOLOGO</option>
                                    <option value="TECNOLOGO_GRADO_2">TECNOLOGO GRADO 2</option>
                                    <option value="RECONOCEDOR_PREDIAL_JUNIOR">RECONOCEDOR PREDIAL JUNIOR</option>
                                    <option value="COORDINADOR_SIG">COORDINADOR SIG</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 p-1 px-2 my-2 ">
                            <label for="con_proyecto" class="form-label" style="font-size:0.9em;"><b>Proyecto</b></label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_proyecto">
                                    <i class="bi bi-bezier"></i>
                                </label>
                                <select class="form-select" style="font-size:0.9em;" id="con_proyecto" name="con_proyecto" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="ANT_AVALUOS">ANT_AVALÚOS</option>
                                    <option value="ADMINISTRATIVO">ADMINISTRATIVO</option>
                                    <option value="ARBIMAPS">ARBIMAPS</option>
                                    <option value="ARBITECH">ARBITECH</option>
                                    <option value="ARBOLETES">VALOR + ARBOLETES</option>
                                    <option value="CONTABILIDAD">CONTABILIDAD</option>
                                    <option value="LEIVA">IGAC LEIVA</option>
                                    <option value="NECOCLÍ">VALOR + NECOCLÍ</option>
                                    <option value="SAN_JUAN">VALOR + SAN JUAN DE URABA</option>
                                    <option value="SAN_PEDRO">VALOR + SAN PEDRO DE URABA</option>
                                    <option value="VALLE_ABURRA">VALLE DE ABURRA BELLO</option>
                                    <option value="VALLE_GUAMUEZ">IGAC VALLE DEL GUAMUEZ</option>>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 p-1 px-2 my-2 ">
                            <label for="con_tipo_contrato" class="form-label" style="font-size:0.9em;"><b>Tipo de contrato</b></label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_tipo_contrato">
                                    <i class="bi bi-file-earmark-medical"></i>
                                </label>
                                <select class="form-select" style="font-size:0.9em;" id="con_tipo_contrato" name="con_tipo_contrato" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="PRESTACION_SERVICIOS">PRESTACIÓN DE SERVICIOS</option>
                                    <option value="LABORAL_TERMINO_FIJO">LABORAL A TERMINO FIJO</option>
                                    <option value="LABORAL_TERMINO_INDEFINIDO">LABORAL A TERMINO INDEFINIDO</option>
                                    <option value="ORDEN_SERVICIO">ORDEN DE SERVICIO</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_fecha_inicio" class="form-label fw-bold" style="font-size:0.9em;">Fecha de Contrato</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar-date"></i></span>
                                <input type="date" class="form-control shadow-sm" style="font-size:0.9em;" id="con_fecha_inicio" name="con_fecha_inicio" aria-label="fecha" aria-describedby="basic-addon1">
                            </div>
                        </div>
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_fecha_final" class="form-label fw-bold" style="font-size:0.9em;">Fecha de finalización de contrato</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar-date"></i></span>
                                <input type="date" class="form-control shadow-sm" style="font-size:0.9em;" id="con_fecha_final" name="con_fecha_final" aria-label="fecha" aria-describedby="basic-addon1">
                            </div>
                        </div>
                        <div class="col-md-4 mt-2 px-2 my-2">
                            <label for="con_duracion" class="form-label fw-bold" style="font-size:0.9em;">Duración de contrato</label>
                            <div class="input-group">
                                <span class="input-group-text shadow-sm "><i class="bi bi-calendar-date"></i></span>
                                <input class="form-control" type="text" id="con_duracion" name="con_duracion" readonly>
                                <input class="form-control" type="hidden" id="con_duracion_hidden" name="con_duracion_hidden">
                            </div>
                        </div>
                        <!-- <div class="col-md-4 p-1 px-2 my-2">
                            <label class="form-label" for="con_salario_mostrado">Salario/Honorarios</label>
                            <input class="form-control"
                                id="con_salario_mostrado"
                                name="con_salario_mostrado"
                                type="text"
                                placeholder="Ingrese el salario del personal"
                                oninput="formatCurrency(this, 'con_salario')"
                                autocomplete="off"
                                required>

                            <input type="hidden" id="con_salario" name="con_salario">
                            <small class="text-primary">Este es el que se guarda</small>
                        </div> -->

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_salario_mostrado" class="form-label fw-bold" style="font-size:0.9em;">Salario/Honorarios</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-piggy-bank"></i></span>
                                <input class="form-control"
                                    id="con_salario_mostrado"
                                    name="con_salario_mostrado"
                                    type="text"
                                    placeholder="Ingrese el salario del personal"
                                    oninput="formatCurrency(this, 'con_salario')"
                                    autocomplete="off"
                                    required>
                                <input type="hidden" id="con_salario" name="con_salario">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_jefe" class="form-label fw-bold" style="font-size:0.9em;">Jefe Inmediato / Supervisor</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-gear"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="con_jefe"
                                    name="con_jefe" placeholder="Ingrese el nombre del jefe inmediato" aria-describedby="basic-addon1">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_per_cargo" class="form-label fw-bold" style="font-size:0.9em;">Nº Personas a Cargo</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-gear"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="con_per_cargo"
                                    name="con_per_cargo" placeholder="Ingrese el total de personas a cargo" aria-describedby="basic-addon1">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_valor_proyecto_mostrado" class="form-label fw-bold" style="font-size:0.9em;">Valor Total Proyecto</label>
                            <div class="input-group shadow-sm shadow-warning">
                                <span class="input-group-text shadow-sm bg-warning border border-warning"><i class="bi bi-cash-coin"></i></span>
                                <input type="text"
                                    class="form-control border border-warning "
                                    id="con_valor_proyecto_mostrado"
                                    name="con_valor_proyecto_mostrado"
                                    placeholder="Se calcula automáticamente"
                                    readonly>

                                <input type="hidden" class="form-control" id="con_valor_proyecto" name="con_valor_proyecto">
                            </div>
                        </div>

                        <div class="col-12  my-4" style="border-bottom:2px dashed #002f557a"></div>

                        <div class="col-12">
                            <h6 class="fw-bold p-2 text-white text-center w-25 rounded-3" style="background-color: #002F55;">Documentos</h6>
                        </div>

                        <div class="col-12 col-lg-6  p-1 px-3 my-3">
                            <label for="con_contrato" class="form-label fw-bold">Contrato</label>
                            <div class="input-group mb-1 shadow-sm">
                                <label class="input-group-text" for="con_contrato" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
                                <input type="file" class="form-control" style="font-size:0.8em;" id="con_contrato" name="con_contrato">
                            </div>
                            <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                        </div>

                        <div class="col-12 col-lg-6  p-1 px-3 my-3">
                            <label for="con_examenes" class="form-label fw-bold">Exámenes de Ingreso</label>
                            <div class="input-group mb-1 shadow-sm">
                                <label class="input-group-text" for="con_examenes" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
                                <input type="file" class="form-control" style="font-size:0.8em;" id="con_examenes" name="con_examenes">
                            </div>
                            <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                        </div>


                        <div class="col-12 col-lg-6  p-1 px-3 my-3">
                            <label for="con_cumplimiento" class="form-label fw-bold">Póliza de Cumplimiento</label>
                            <div class="input-group mb-1 shadow-sm">
                                <label class="input-group-text" for="con_cumplimiento" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
                                <input type="file" class="form-control" style="font-size:0.8em;" id="con_cumplimiento" name="con_cumplimiento">
                            </div>
                            <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                        </div>

                        <div class="col-12 col-lg-6  p-1 px-3 my-3">
                            <label for="con_acta" class="form-label fw-bold">Acta de Inicio</label>
                            <div class="input-group mb-1 shadow-sm">
                                <label class="input-group-text" for="con_acta" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
                                <input type="file" class="form-control" style="font-size:0.8em;" id="con_acta" name="con_acta">
                            </div>
                            <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">

                            <!-- ATRÁS -->
                            <button type="button"
                                class="btn text-white btn-prev-section px-4 d-inline-flex align-items-center" style="background-color: #002F55;"
                                data-prev="section4">
                                <i class="bi bi-arrow-left me-2"></i>
                                Atrás
                            </button>

                            <!-- SIGUIENTE -->
                            <button type="button"
                                class="btn text-white btn-next-section px-4 d-inline-flex align-items-center" style="background-color: #002F55;" s
                                data-next="section6">
                                Siguiente
                                <i class="bi bi-arrow-right ms-2"></i>
                            </button>

                        </div>

                    </div>
                </div>

                <!-- ========== SECCIÓN 6: SOCIODEMOGRFICA ========== -->
                <div id="section6" class="seccion-formulario">

                    <div class="mb-3 d-flex align-items-center text-start rounded-4 card-header shadow m-2 py-3 text-white">
                        <i class="bi bi-person-bounding-box me-3 fs-2"></i>
                        <div>
                            <h3 class="h5 mb-1">Información Sociodemográfica</h3>
                            <p class=" mb-0" style="font-size: 0.85rem; color:#999999">
                                Información básica del colaborador.
                            </p>
                        </div>
                    </div>

                    <div class="row p-4 g-3">

                        <div class="col-md-4 p-1 px-2 my-2 ">
                            <label for="con_genero" class="form-label" style="font-size:0.9em;"><b>Género</b></label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_genero">
                                    <i class="bi bi-gender-ambiguous"></i>
                                </label>
                                <select class="form-select" style="font-size:0.9em;" id="con_genero" name="con_genero" required>
                                    <option value="" disabled selected>Selecciona</option>
                                    <option value="MASCULINO">MASCULINO</option>
                                    <option value="FEMENINO">FEMENINO</option>
                                    <option value="OTRO">OTRO</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2 ">
                            <label for="con_raza" class="form-label" style="font-size:0.9em;"><b>Raza</b></label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_raza">
                                    <i class="bi bi-person-arms-up"></i>
                                </label>
                                <select class="form-select" style="font-size:0.9em;" id="con_raza" name="con_raza" required>
                                    <option value="" disabled selected>Selecciona</option>
                                    <option value="MESTIZO">MESTIZO</option>
                                    <option value="COMUNIDADES NEGRAS O AFROCOLOMBIANAS">COMUNIDADES NEGRAS O AFROCOLOMBIANAS</option>
                                    <option value="PUEBLOS Y COMUNIDADES INDIGENAS">PUEBLOS Y COMUNIDADES INDÍGENAS</option>
                                    <option value="COMUNIDAD RAIZAL">COMUNIDAD RAIZAL</option>
                                    <option value="PUEBLO ROM O GITANO">PUEBLO ROM O GITANO</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2 ">
                            <label for="con_vivienda" class="form-label" style="font-size:0.9em;"><b>Vivienda</b></label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_vivienda">
                                    <i class="bi bi-house"></i>
                                </label>
                                <select class="form-select" style="font-size:0.9em;" id="con_vivienda" name="con_vivienda" required>
                                    <option value="" disabled selected>Selecciona</option>
                                    <option value="ARRENDADA">ARRENDADA</option>
                                    <option value="PROPIA">PROPIA</option>
                                    <option value="FAMILIAR">FAMILIAR</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2 mx-auto">
                            <label for="con_estrato" class="form-label" style="font-size:0.9em;"><b>Estrato</b></label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_estrato">
                                    <i class="bi bi-house-exclamation"></i>
                                </label>
                                <select class="form-select" style="font-size:0.9em;" id="con_estrato" name="con_estrato" required>
                                    <option value="" disabled selected>Selecciona</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12  my-4" style="border-bottom:2px dashed #002f557a"></div>

                        <div class="row">

                            <div class="col-md-4 p-1 px-2 my-2 ">
                                <label for="incapacitado" class="form-label" style="font-size:0.9em;"><b>¿Tiene alguna discapacidad?</b></label>
                                <div class="input-group shadow-sm">
                                    <label class="input-group-text" for="incapacitado">
                                        <i class="bi bi-person-wheelchair"></i>
                                    </label>
                                    <select class="form-select" style="font-size:0.9em;" id="incapacitado" name="incapacitado" onchange="mostrarCertificado()" required>
                                        <option value="" disabled selected>Selecciona</option>
                                        <option value="si">Sí</option>
                                        <option value="no">No</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Campo que se mostrará solo si elige "Sí" -->
                            <!-- Campo ¿Cuál? -->

                            <div class="col-md-4 p-1 px-2 my-2" id="campo_cual" style="display: none;">
                                <label for="con_discapacidad" class="form-label fw-bold" style="font-size:0.9em;">¿Cual?</label>
                                <div class="input-group ">
                                    <span class="input-group-text shadow-sm"><i class="bi bi-lungs"></i></span>
                                    <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="con_discapacidad" name="con_discapacidad"
                                        placeholder="Describa la discapacidad" aria-label="discapacidad" aria-describedby="basic-addon1">
                                </div>
                            </div>

                            <!-- Campo certificado debajo -->

                            <div class="col-12 col-lg-4  p-1 px-3 my-3" id="con_cerIncapacidad" style="display: none;">
                                <label for="con_cerIncapacidad" class="form-label fw-bold">Cargue certificado de Discapacidad</label>
                                <div class="input-group mb-1 shadow-sm">
                                    <label class="input-group-text" for="con_cerIncapacidad" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
                                    <input type="file" class="form-control" style="font-size:0.8em;" id="con_cerIncapacidad" name="con_cerIncapacidad">
                                </div>
                                <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                            </div>


                        </div>

                        <div class="d-flex justify-content-between mt-4">

                            <!-- ATRÁS -->
                            <button type="button"
                                class="btn text-white btn-prev-section px-4 d-inline-flex align-items-center" style="background-color: #002F55;"
                                data-prev="section5">
                                <i class="bi bi-arrow-left me-2"></i>
                                Atrás
                            </button>

                            <!-- SIGUIENTE -->
                            <button type="button"
                                class="btn text-white btn-next-section px-4 d-inline-flex align-items-center" style="background-color: #002F55;"
                                data-next="section7">
                                Siguiente
                                <i class="bi bi-arrow-right ms-2"></i>
                            </button>

                        </div>

                    </div>

                </div>

                <!-- ========== SECCIÓN 7: VACUNAS ========== -->
                <div id="section7" class="seccion-formulario">

                    <div class="mb-3 d-flex align-items-center text-start rounded-4 card-header shadow m-2 py-3 text-white">
                        <i class="bi bi-hospital me-3 fs-2"></i>
                        <div>
                            <h3 class="h5 mb-1">Esquema de vacunación</h3>
                            <p class=" mb-0" style="font-size: 0.85rem; color:#999999">
                                Ingresa todos los campos correspondientes a las vacunas que tiene el colaborador.
                            </p>
                        </div>
                    </div>

                    <div class="row p-4 g-3">
                        <div class="container">
                            <div class="title-vacunas fw-bold">Esquema de vacunación</div>
                            <!-- RECUADRO QUE RODEA TODO -->
                            <div class="vaccines-box">
                                <div class="row justify-content-center">
                                    <div class="col-md-10">
                                        <div class="row g-3 ">
                                            <div class="col-md-4 ">
                                                <div class="checklist-item">
                                                    <input type="checkbox" id="con_tetano1" name="con_tetano1">
                                                    <label for="con_tetano1">Tétano 1 Dosis</label>
                                                </div>
                                                <div class="checklist-item">
                                                    <input type="checkbox" id="con_tetano2" name="con_tetano2">
                                                    <label for="con_tetano2">Tétano 2 Dosis</label>
                                                </div>
                                                <div class="checklist-item">
                                                    <input type="checkbox" id="con_tetano3" name="con_tetano3">
                                                    <label for="con_tetano3">Tétano 3 Dosis</label>
                                                </div>
                                                <div class="checklist-item">
                                                    <input type="checkbox" id="con_influenza" name="con_influenza">
                                                    <label for="con_influenza">Influenza</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="checklist-item">
                                                    <input type="checkbox" id="con_hepatitis_a" name="con_hepatitis_a">
                                                    <label for="con_hepatitis_a">Hepatitis A</label>
                                                </div>
                                                <div class="checklist-item">
                                                    <input type="checkbox" id="con_hepatitis_c" name="con_hepatitis_c">
                                                    <label for="con_hepatitis_c">Hepatitis C</label>
                                                </div>
                                                <div class="checklist-item">
                                                    <input type="checkbox" id="con_covid1" name="con_covid1">
                                                    <label for="con_covid1">Covid-19: 1 Dosis</label>
                                                </div>
                                                <div class="checklist-item">
                                                    <input type="checkbox" id="con_covid2" name="con_covid2">
                                                    <label for="con_covid2">Covid-19: 2 Dosis</label>
                                                </div>
                                                <div class="checklist-item">
                                                    <input type="checkbox" id="con_covid3" name="con_covid3">
                                                    <label for="con_covid3">Covid-19: 3 Dosis</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="checklist-item">
                                                    <input type="checkbox" id="con_amarilla" name="con_amarilla">
                                                    <label for="con_amarilla">Fiebre Amarilla</label>
                                                </div>
                                                <div class="checklist-item">
                                                    <input type="checkbox" id="vph_1" name="vph_1">
                                                    <label for="vph_1">VPH 1</label>
                                                </div>
                                                <div class="checklist-item">
                                                    <input type="checkbox" id="vph_2" name="vph_2">
                                                    <label for="vph_2">VPH 2</label>
                                                </div>
                                                <div class="checklist-item">
                                                    <input type="checkbox" id="vph_3" name="vph_3">
                                                    <label for="vph_3">VPH 3</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- FIN DEL RECUADRO -->
                        </div>

                        <div class="col-md-6 p-1 px-2 my-3 ">
                            <label for="con_rh" class="form-label" style="font-size:0.9em;"><b>RH sanguineo</b></label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_rh">
                                    <i class="bi-person-badge"></i>
                                </label>
                                <select class="form-select" style="font-size:0.9em;" id="con_rh" name="con_rh" required>
                                    <option value="" disabled selected>Selecciona el tipo de documento</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                    <option value="A-">A-</option>
                                    <option value="A+">A+</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                </select>
                            </div>
                        </div>

                        <!-- <div class="col-md-6">
                            <label for="con_cerVacunacion" class="form-label">Certificado de Vacunación</label>
                            <input type="file" class="form-control" id="con_cerVacunacion" name="con_cerVacunacion">
                            <label for="">
                                <a href="https://mivacuna.sispro.gov.co/MiVacuna/Account/LoginCarnetDigitalVac"
                                    target="_blank" rel="noopener noreferrer">
                                    <b>Descarga tu certificado de Vacunación aquí</b>
                                </a>
                            </label>
                        </div> -->

                        <div class="col-12 col-lg-6  p-1 px-3 my-3">
                            <label for="con_cerVacunacion" class="form-label fw-bold">Certificación de vacunación</label>
                            <div class="input-group mb-1 shadow-sm">
                                <label class="input-group-text" for="con_cerVacunacion" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
                                <input type="file" class="form-control" style="font-size:0.8em;" id="con_cerVacunacion" name="con_cerVacunacion">
                            </div>
                            <label for="">
                                <a href="https://mivacuna.sispro.gov.co/MiVacuna/Account/LoginCarnetDigitalVac"
                                    target="_blank" rel="noopener noreferrer">
                                    <!-- <b>Descarga tu certificado de Vacunación aquí</b> -->
                                    <small class="p-1 px-2 mt-1 bg-success rounded-2 text-center tilt" style="color: #ffffffff;"> <i class="bi bi-file-break me-2"></i>Descarga tu certificado aquí</small>
                                </a>
                            </label>
                            <!-- <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div> -->
                        </div>
                        <button type="submit" class="btn btn-guardar" id="btn_enviar">
                            <b>Guardar Información</b>
                        </button>
                        <div class="d-flex justify-content-between mt-4">
                            <!-- ATRÁS -->
                            <button type="button"
                                class="btn text-white btn-prev-section px-4 d-inline-flex align-items-center" style="background-color: #002F55;"
                                data-prev="section6">
                                <i class="bi bi-arrow-left me-2"></i>
                                Atrás
                            </button>
                            <!-- SIGUIENTE -->
                            <button type="button"
                                class="btn text-white btn-next-section px-4 d-inline-flex align-items-center" style="background-color: #002F55;"
                                data-next="section8">
                                Siguiente
                                <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- ========== SECCIÓN 8: OTRO SI ========== -->
                <div id="section8" class="seccion-formulario">
                    <div class="mb-3 text-center">
                        <h3 class="h5 mb-1" style="color:#0F5699;">Otro Sí</h3>
                        <p class="text-muted mb-0" style="font-size: 0.85rem;">
                            Llene por favor todos los campos.
                        </p>
                    </div>
                    <div class="row g-3">
                        <!-- TÍTULO Y CAJA DE "AGREGAR OTROS SÍ" -->
                        <div class="col-12 mb-2">
                            <div class="otrosi-title">Carga de Información</div>
                            <div class="otrosi-box text-center">
                                <label class="form-label mb-2" for="otr_otrosi">Agregar Otros sí</label>
                                <div class="d-inline-flex align-items-center gap-2 otrosi-add-wrapper">
                                    <input type="number" id="otr_otrosi" min="1"
                                        class="form-control otrosi-input-number" />
                                    <button type="button" class="btn btn-otrosi-add"
                                        onclick="crearOtrosi()">
                                        <span class="fw-bold">+</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- AQUÍ SE PINTAN LOS FORMULARIOS GENERADOS -->
                        <div class="col-12 mt-3">
                            <div id="unidadesGeneradas"></div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <!-- ATRÁS -->
                            <button type="button"
                                class="btn btn-primary btn-prev-section px-4 d-inline-flex align-items-center"
                                data-prev="section7">
                                <i class="bi bi-arrow-left me-2"></i>
                                Atrás
                            </button>
                            <!-- SIGUIENTE -->
                            <button type="button"
                                class="btn btn-primary btn-next-section px-4 d-inline-flex align-items-center"
                                data-next="section9">
                                Siguiente
                                <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ========== SECCIÓN 9: Exéperiencia Laboral  ========== -->

                <div id="section9" class="seccion-formulario">

                    <div class="mb-3 d-flex align-items-center text-start rounded-4 card-header shadow m-2 py-3 text-white">
                        <i class="bi bi-mortarboard me-3 fs-2"></i>
                        <div>
                            <h3 class="h5 mb-1">Exéperiencia Laboral</h3>
                            <p class=" mb-0" style="font-size: 0.85rem; color:#999999">
                                Información de la formación del colabo. Por favor llene la información de la Exéperiencia Laboral.
                            </p>
                        </div>
                    </div>


                    <div class="row p-4 g-3 justify-content-center">

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="exp_empresa" class="form-label fw-bold" style="font-size:0.9em;">Empresa o Entidad</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-raised-hand"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="exp_empresa" name="exp_empresa"
                                    placeholder="Ingrese la empresa o entidad...">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2 ">
                            <label for="exp_entidad" class="form-label" style="font-size:0.9em;"><b> Tipo de Entidad</b></label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="exp_entidad">
                                    <i class="bi bi-backpack2"></i>
                                </label>
                                <select class="form-select" style="font-size:0.9em;" id="exp_entidad" name="exp_entidad">
                                    <option value="" disabled selected>Selecciona</option>
                                    <option value="PUBLICA">PUBLICA</option>
                                    <option value="PRIVADA">PRIVADA</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="exp_pais" class="form-label fw-bold" style="font-size:0.9em;">Pais</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-raised-hand"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="exp_pais" name="exp_pais"
                                    placeholder="Ingrese el pais">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="exp_departamento" class="form-label fw-bold" style="font-size:0.9em;">Departamento</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-raised-hand"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="exp_departamento" name="exp_departamento"
                                    placeholder="Ingrese el departamento">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="exp_municipio" class="form-label fw-bold" style="font-size:0.9em;">Municipio</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-raised-hand"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="exp_municipio" name="exp_municipio"
                                    placeholder="Ingrese el municipio">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="exp_correo_entidad" class="form-label fw-bold" style="font-size:0.9em;">Correo electrónico entidad</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at"></i></span>
                                <input type="email" class="form-control" id="exp_correo_entidad" placeholder="correo@ejemplo.com"
                                    name="exp_correo_entidad">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="exp_telefonos" class="form-label fw-bold" style="font-size:0.9em;">Telefonos</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-raised-hand"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="exp_telefonos" name="exp_telefonos"
                                    placeholder="Ingrese el numero de telefono">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="exp_fecha_ingreso" class="form-label fw-bold" style="font-size:0.9em;">Fecha de ingreso</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar2-event"></i></span>
                                <input type="date" class="form-control shadow-sm" style="font-size:0.9em;" id="exp_fecha_ingreso" name="exp_fecha_ingreso">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="exp_fecha_salida" class="form-label fw-bold" style="font-size:0.9em;">Fecha de retiro</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar2-event"></i></span>
                                <input type="date" class="form-control shadow-sm" style="font-size:0.9em;" id="exp_fecha_salida" name="exp_fecha_salida">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="exp_cargo" class="form-label fw-bold" style="font-size:0.9em;">Cargo o contrato actual</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-raised-hand"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="exp_cargo" name="exp_cargo"
                                    placeholder="Ingrese el cargano o contrato actual">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="exp_dependencias" class="form-label fw-bold" style="font-size:0.9em;">Dependencia</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-raised-hand"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="exp_dependencias" name="exp_dependencias"
                                    placeholder="Ingrese la dependencia">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="exp_direccion" class="form-label fw-bold" style="font-size:0.9em;">Direccion</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-raised-hand"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="exp_direccion" name="exp_direccion"
                                    placeholder="Ingrese la direccion">
                            </div>
                        </div>

                        <div class="col-12  my-4" style="border-bottom:2px dashed #002f557a"></div>

                        <div class="col-12 mt-0">
                            <h6 class="fw-bold p-2 text-white text-center w-25 rounded-3" style="background-color: #002F55;">Documentos</h6>
                        </div>


                        <div class="col-12 col-lg-6  p-1 px-3 my-3">
                            <label for="exp_certificado_laboral" class="form-label fw-bold">Certifcado Experiencia laboral</label>
                            <div class="input-group mb-1 shadow-sm">
                                <label class="input-group-text" for="exp_certificado_laboral" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
                                <input type="file" class="form-control" style="font-size:0.8em;" id="exp_certificado_laboral" name="exp_certificado_laboral">
                            </div>
                            <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                        </div>

                        <div class="col-12 my-4" style="border-bottom:2px dashed #002f557a"></div>

                        <div class="mb-3 d-flex align-items-center text-start rounded-4 card-header shadow m-2 py-3 text-white">
                            <i class="bi bi-mortarboard me-3 fs-2"></i>
                            <div>
                                <h3 class="h5 mb-1">Experiencias</h3>
                                <p class=" mb-0" style="font-size: 0.85rem; color:#999999">
                                    Informacion de otras experiencias laborales. Por favor completa la informacion si cuenta con otras experencias.
                                </p>
                            </div>
                        </div>

                        <div class="col-12 mb-2">
                            <div class="otrosi-title">Carga de Información</div>

                            <div class="otrosi-box text-center">
                                <label class="form-label mb-2" for="est_estudios">Agregar experiencia</label>

                                <div class="d-inline-flex align-items-center gap-2 otrosi-add-wrapper">
                                    <input type="number" id="exp_cantidad" min="1" class="form-control otrosi-input-number" />
                                    <button type="button" class="btn btn-otrosi-add" onclick="crearExperiencias()">
                                        <span class="fw-bold">+</span>
                                    </button>
                                </div>

                                <!-- Si necesitas el con_id aquí, deja esta línea (solo si existe $con_id) -->
                                <input type="hidden" id="con_id" value="<?php echo $con_id; ?>">
                            </div>
                        </div>

                        <div class="col-12 mt-3">
                            <div id="experiencias"></div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">

                            <!-- Botón Atrás -->
                            <button type="button"
                                class="btn btn-primary btn-prev-section px-4 d-inline-flex align-items-center"
                                data-prev="section8">
                                <i class="bi bi-arrow-left me-2"></i>
                                Atrás
                            </button>
                            <!-- Panel + Enviar Datos -->
                            <div class="panel-enviar-datos mx-3">
                                <a href="/arbimaps/Arbimaps/index.php?page=Personal/personal_activo" class="btn-enviar-datos">
                                    Enviar Datos
                                </a>
                            </div>
                            <!-- Botón Inicio -->
                            <button type="button"
                                class="btn btn-primary btn-next-section px-4 d-inline-flex align-items-center"
                                data-next="section1">
                                Inicio
                                <i class="bi bi-arrow-right ms-2"></i>
                            </button>

                        </div>

                    </div>

                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://kit.fontawesome.com/9944c94262.js" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="../../../js/scripts.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>

<!-- ========== JS BÁSICO PARA NAVEGAR ENTRE SECCIONES (SIN CAMPOS OBLIGATORIOS) ========== -->
<script>
    //funcion para el campo de alergias
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('alergiasContainer');
        const hidden = document.getElementById('con_alergias');
        if (!container || !hidden) return;

        function toUpper(el) {
            el.value = (el.value || "").toUpperCase();
        }

        function syncHidden() {
            const inputs = container.querySelectorAll('.alergia-input');

            const first = (inputs[0]?.value || '').trim().toUpperCase();
            if (first === "NINGUNA") {
                container.querySelectorAll('.alergia-row').forEach((row, idx) => {
                    if (idx > 0) row.remove();
                });
                hidden.value = "NINGUNA";
                return;
            }
            const valores = Array.from(inputs)
                .map(i => (i.value || '').trim().toUpperCase())
                .filter(v => v.length > 0);

            hidden.value = valores.join(', ');
        }

        function addRow() {
            const index = container.querySelectorAll('.alergia-row').length;
            const row = document.createElement('div');
            row.className = 'input-group mb-2 alergia-row';
            row.innerHTML = `
                        <span class="input-group-text shadow-sm"><i class="bi bi-bandaid"></i></span>
                        <input type="text"
                            class="form-control shadow-sm alergia-input"
                            style="font-size:0.9em;"
                            id="con_alergias_${index}"
                            placeholder="Ej: MANÍ">
                        <button type="button" class="btn btn-outline-danger btn-remove-alergia" title="Quitar">
                            <i class="bi bi-dash-lg"></i>
                        </button>
                        `;
            container.appendChild(row);
        }
        container.addEventListener('click', function(e) {
            if (e.target.closest('.btn-add-alergia')) {
                addRow();
                return;
            }
            const rm = e.target.closest('.btn-remove-alergia');
            if (rm) {
                rm.closest('.alergia-row').remove();
                syncHidden();
            }
        });
        container.addEventListener('input', function(e) {
            const inp = e.target.closest('.alergia-input');
            if (!inp) return;
            toUpper(inp);
            syncHidden();
        });
        syncHidden();
    });




    // FUNCION QUE PERMITE EL CAMPO DEL SALARIO CON FORMATO COP
    function formatCurrency(input, hiddenId) {
        // Dejar solo números
        let value = input.value.replace(/\D/g, "");
        if (value === "") {
            input.value = "";
            if (hiddenId) {
                document.getElementById(hiddenId).value = "";
            }
            return;
        }
        let number = parseInt(value, 10);
        let formatted = new Intl.NumberFormat("es-CO", {
            style: "currency",
            currency: "COP",
            minimumFractionDigits: 0
        }).format(number);
        // Muestra: $ 2.500.000
        input.value = formatted;
        // Guarda solo el número en el input oculto correspondiente
        if (hiddenId) {
            document.getElementById(hiddenId).value = number;
        }
    }
</script>


<!-- función para calculo de valor pagado actualizada -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const fechaInicio = document.getElementById("con_fecha_inicio");
        const fechaFinal = document.getElementById("con_fecha_final");
        const durTxt = document.getElementById("con_duracion");
        const durHidden = document.getElementById("con_duracion_hidden");

        const salarioHidden = document.getElementById("con_salario");
        const valorMostrado = document.getElementById("con_valor_proyecto_mostrado");
        const valorHidden = document.getElementById("con_valor_proyecto");

        if (!fechaInicio || !fechaFinal || !durTxt || !durHidden || !salarioHidden || !valorMostrado || !valorHidden) {
            console.warn("Faltan elementos para cálculo de duración/valor.");
            return;
        }

        ["input", "change"].forEach(evt => {
            fechaInicio.addEventListener(evt, calcularDuracion);
            fechaFinal.addEventListener(evt, calcularDuracion);
        });
        calcularDuracion();

        function parseYMD(ymd) {
            const [y, m, d] = ymd.split("-").map(Number);
            return new Date(y, m - 1, d);
        }

        function lastDayOfMonth(date) {
            return new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
        }

        function isFullCalendarMonth(from, to) {
            if (from.getFullYear() !== to.getFullYear()) return false;
            if (from.getMonth() !== to.getMonth()) return false;
            if (from.getDate() !== 1) return false;
            return to.getDate() === lastDayOfMonth(from);
        }

        function addMonths(date, n) {
            const y = date.getFullYear();
            const m = date.getMonth() + n;
            const d = date.getDate();
            const candidate = new Date(y, m, 1);
            const maxDay = lastDayOfMonth(candidate);
            return new Date(candidate.getFullYear(), candidate.getMonth(), Math.min(d, maxDay));
        }

        function diffDaysInclusive(from, to) {
            const msPerDay = 24 * 60 * 60 * 1000;
            const a = new Date(from.getFullYear(), from.getMonth(), from.getDate());
            const b = new Date(to.getFullYear(), to.getMonth(), to.getDate());
            return Math.round((b - a) / msPerDay) + 1;
        }

        function calcularDuracion() {
            const inicioStr = fechaInicio.value;
            const finStr = fechaFinal.value;
            if (!inicioStr || !finStr) {
                limpiarDuracion();
                calcularValorProyecto();
                return;
            }

            const start = parseYMD(inicioStr);
            const end = parseYMD(finStr);
            if (end < start) {
                durTxt.value = "Fecha final inválida";
                durHidden.value = "";
                calcularValorProyecto();
                return;
            }

            let meses = 0;
            let cursor = new Date(start.getFullYear(), start.getMonth(), start.getDate());
            while (true) {
                const finMesCursor = new Date(cursor.getFullYear(), cursor.getMonth(), lastDayOfMonth(cursor));
                if (cursor.getDate() === 1 && finMesCursor <= end) {
                    meses += 1;
                    cursor = new Date(cursor.getFullYear(), cursor.getMonth() + 1, 1);
                    continue;
                }
                break;
            }

            let dias = 0;
            if (cursor <= end) {
                dias = diffDaysInclusive(cursor, end);
            }
            durTxt.value = construirTextoDuracion(meses, dias);
            durHidden.value = `${meses}|${dias}`;
            calcularValorProyecto();
        }

        function limpiarDuracion() {
            durTxt.value = "";
            durHidden.value = "";
        }

        function construirTextoDuracion(meses, dias) {
            if (meses > 0 && dias > 0)
                return `${meses} ${meses === 1 ? "mes" : "meses"} y ${dias} ${dias === 1 ? "día" : "días"}`;
            if (meses > 0)
                return `${meses} ${meses === 1 ? "mes" : "meses"}`;
            return `${dias} ${dias === 1 ? "día" : "días"}`;
        }

        function calcularValorProyecto() {
            const salario = Number(salarioHidden.value || 0);
            const dur = durHidden.value;
            if (!salario || !dur.includes("|")) {
                valorMostrado.value = "";
                valorHidden.value = "";
                return;
            }

            const [meses, dias] = dur.split("|").map(Number);
            const total = (salario * meses) + (salario / 30 * dias);
            const totalRedondeado = Math.round(total);

            valorHidden.value = totalRedondeado;
            valorMostrado.value = new Intl.NumberFormat("es-CO", {
                style: "currency",
                currency: "COP",
                minimumFractionDigits: 0
            }).format(totalRedondeado);
        }
        window.formatCurrency = function(input, hiddenId) {
            const value = input.value.replace(/\D/g, "");
            document.getElementById(hiddenId).value = value;
            input.value = new Intl.NumberFormat("es-CO", {
                style: "currency",
                currency: "COP",
                minimumFractionDigits: 0
            }).format(value || 0);

            calcularValorProyecto();
        };
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mostrar solo la primera sección al cargar
        const secciones = document.querySelectorAll('.seccion-formulario');
        const botonesSeccion = document.querySelectorAll('.section-btn');

        function mostrarSeccion(id) {
            // Mostrar/ocultar secciones
            secciones.forEach(sec => {
                if (sec.id === id) {
                    sec.classList.remove('d-none');
                } else {
                    sec.classList.add('d-none');
                }
            });
            // Pintar botones (activo: fondo blanco, letra azul; resto: fondo azul, letra blanca)
            botonesSeccion.forEach(btn => {
                const esActivo = btn.getAttribute('data-section') === id;
                if (esActivo) {
                    btn.classList.add('active');
                    btn.style.backgroundColor = "#ffffff";
                    btn.style.color = "#003B66";
                    btn.style.border = "2px solid #003B66";
                } else {
                    btn.classList.remove('active');
                    btn.style.backgroundColor = "#003B66";
                    btn.style.color = "white";
                    btn.style.border = "none";
                }
            });
        }
        // Por defecto, mostramos section1
        mostrarSeccion('section1');
        // Click en los botones de la parte superior
        botonesSeccion.forEach(btn => {
            btn.addEventListener('click', function() {
                const target = this.getAttribute('data-section');
                mostrarSeccion(target);
            });
        });
        // Botones "Siguiente"
        const botonesSiguiente = document.querySelectorAll('.btn-next-section');
        botonesSiguiente.forEach(btn => {
            btn.addEventListener('click', function() {
                const nextId = this.getAttribute('data-next');
                if (nextId) {
                    mostrarSeccion(nextId);
                }
            });
        });
        // Botones "Atrás"
        const botonesAtras = document.querySelectorAll('.btn-prev-section');
        botonesAtras.forEach(btn => {
            btn.addEventListener('click', function() {
                const prevId = this.getAttribute('data-prev');
                if (prevId) {
                    mostrarSeccion(prevId);
                }
            });
        });
        // Calcular edad automáticamente cuando cambie la fecha de nacimiento
        const inputFechaNacimiento = document.getElementById('con_fecha_nacimiento');
        const inputEdad = document.getElementById('con_edad');
        if (inputFechaNacimiento && inputEdad) {
            inputFechaNacimiento.addEventListener('change', function() {
                if (!this.value) {
                    inputEdad.value = '';
                    return;
                }
                const hoy = new Date();
                const fechaNac = new Date(this.value);
                let edad = hoy.getFullYear() - fechaNac.getFullYear();
                const m = hoy.getMonth() - fechaNac.getMonth();

                if (m < 0 || (m === 0 && hoy.getDate() < fechaNac.getDate())) {
                    edad--;
                }
                inputEdad.value = isNaN(edad) ? '' : edad;
            });
        }
        // Mostrar / ocultar campo "OTRA EPS"
        const selectEPS = document.getElementById('con_eps');
        const inputOtraEPS = document.getElementById('otra_eps');

        if (selectEPS && inputOtraEPS) {
            function toggleOtraEPS() {
                if (selectEPS.value === 'OTRO') {
                    inputOtraEPS.style.display = 'block'; // se muestra
                    inputOtraEPS.required = true; // lo hacemos obligatorio
                } else {
                    inputOtraEPS.style.display = 'none'; // se oculta
                    inputOtraEPS.required = false;
                    inputOtraEPS.value = ''; // limpiamos el texto
                }
            }
            // Ejecutar al cargar por si ya viene con valor
            toggleOtraEPS();
            // Ejecutar cada vez que cambie
            selectEPS.addEventListener('change', toggleOtraEPS);
        }
        // Previsualizar foto
        const inputFoto = document.getElementById('photo');
        const previewFoto = document.getElementById('previewFoto');
        if (inputFoto && previewFoto) {
            inputFoto.addEventListener('change', function(e) {
                previewFoto.innerHTML = '';
                const file = e.target.files[0];
                if (file) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.classList.add('img-thumbnail');
                    img.style.maxWidth = '140px';
                    img.style.maxHeight = '140px';
                    previewFoto.appendChild(img);
                }
            });
        }

        // FUNCION PARA CONVERTIR EN MAYÚSCULAS TODO LO QUE SE ESCRIBA EN INPUTS DE TEXTO
        document.querySelectorAll("input[type='text']").forEach(function(input) {
            input.addEventListener("input", function() {
                this.value = this.value.toUpperCase();
            });
        });

        // ====== ✅ LISTAS POR COMAS (ENFERMEDADES / ALERGIAS / MEDICAMENTOS) ======
        setupCommaToArray("con_enfermedades", "con_enfermedades_list");
        //setupCommaToArray("con_alergias", "con_alergias_list");
        setupCommaToArray("con_medicamentos", "con_medicamentos_list");

        function setupCommaToArray(inputId, containerId) {
            const input = document.getElementById(inputId);
            if (!input) return;

            // contenedor invisible para los hidden inputs
            let container = document.getElementById(containerId);
            if (!container) {
                container = document.createElement("div");
                container.id = containerId;
                container.style.display = "none";
                input.parentNode.appendChild(container);
            }

            function buildHiddenInputs() {
                container.innerHTML = "";

                const raw = (input.value || "").trim();
                if (raw.toUpperCase() === "NINGUNA" || raw === "") return;

                const items = raw
                    .split(",")
                    .map(x => x.trim())
                    .filter(x => x.length > 0);

                items.forEach(val => {
                    const hidden = document.createElement("input");
                    hidden.type = "hidden";
                    hidden.name = input.name + "[]"; // con_enfermedades[]
                    hidden.value = val;
                    container.appendChild(hidden);
                });
            }

            input.addEventListener("input", buildHiddenInputs);
            input.addEventListener("blur", buildHiddenInputs);
            buildHiddenInputs();
        }

    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const steps = document.querySelectorAll('.step');
        const sections = document.querySelectorAll('.seccion-formulario');
        const progress = document.getElementById('stepper-progress');

        let currentStep = 0;

        // ✅ Mostrar solo la sección activa
        function showOnlyActiveSection() {
            sections.forEach((sec, i) => {
                sec.classList.toggle('d-none', i !== currentStep);
            });
        }

        // ✅ Validar SOLO la sección activa (sin deshabilitar nada)
        function validateCurrentSection() {
            const activeSection = sections[currentStep];
            if (!activeSection) return true;

            const requiredFields = activeSection.querySelectorAll('input:required, select:required, textarea:required');

            for (const field of requiredFields) {
                if (!field.checkValidity()) {
                    field.reportValidity(); // enfoca y muestra mensaje nativo
                    return false;
                }
            }
            return true;
        }

        function updateProgress() {
            if (!progress) return;
            const percent = (currentStep / (steps.length - 1)) * 100;
            progress.style.width = percent + '%';
        }

        function paintSteps() {
            steps.forEach((step, i) => {
                step.classList.remove('active', 'completed');
                if (i < currentStep) step.classList.add('completed');
                if (i === currentStep) step.classList.add('active');
            });
        }

        function goToStep(index) {
            if (index < 0 || index >= steps.length) return;
            currentStep = index;
            showOnlyActiveSection();
            paintSteps();
            updateProgress();
        }

        // Click en pasos
        steps.forEach((btn, index) => {
            btn.addEventListener('click', () => goToStep(index));
        });

        // Siguiente (✅ valida solo sección actual)
        document.querySelectorAll('.btn-next-section').forEach(btn => {
            btn.addEventListener('click', () => {
                if (!validateCurrentSection()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Campos incompletos',
                        text: 'Completa los campos obligatorios de esta sección.'
                    });
                    return;
                }

                const nextId = btn.getAttribute('data-next');
                if (!nextId) return;

                const nextIndex = Array.from(sections).findIndex(s => s.id === nextId);
                if (nextIndex !== -1) goToStep(nextIndex);
            });
        });

        // Atrás
        document.querySelectorAll('.btn-prev-section').forEach(btn => {
            btn.addEventListener('click', () => {
                const prevId = btn.getAttribute('data-prev');
                if (!prevId) return;

                const prevIndex = Array.from(sections).findIndex(s => s.id === prevId);
                if (prevIndex !== -1) goToStep(prevIndex);
            });
        });

        // Inicial
        goToStep(0);
    });
</script>



<script>
    // ===================== VALIDACIÓN CON SWEETALERT + FETCH ÚNICO =====================
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formContratacion');
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
                text: 'Se enviarán los datos de contratación.',
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
                                text: data.message || 'La información se guardó correctamente.'
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


<script>
    // discapacidad
    function mostrarCertificado() {
        const select = document.getElementById('incapacitado');
        const campoCual = document.getElementById('campo_cual');
        const campoCertificado = document.getElementById('con_cerIncapacidad');
        const inputCual = document.getElementById('con_discapacidad');

        if (!select || !campoCual || !campoCertificado || !inputCual) return;

        if (select.value === 'si') {
            campoCual.style.display = 'block';
            campoCertificado.style.display = 'block';
            inputCual.required = true;
        } else {
            campoCual.style.display = 'none';
            campoCertificado.style.display = 'none';
            inputCual.required = false;
            inputCual.value = '';
            const fileInput = document.getElementById('con_cerIncapacidad');
            if (fileInput) fileInput.value = '';
        }
    }
</script>


<script>
    // otro si 
    function crearOtrosi() {
        var cantidad = document.getElementById("otr_otrosi").value;
        var formularios = []; // Array para almacenar los formularios generados

        // Limpiar las unidades generadas previamente
        document.getElementById("unidadesGeneradas").innerHTML = "";

        // Crear nuevas unidades de construcción según la cantidad ingresada
        for (var i = 0; i < cantidad; i++) {
            var card = document.createElement("div");
            card.className = "card shadow-lg border-0 mt-4 otrosi-card";

            var cardHeader = document.createElement("div");
            cardHeader.className = "card-header otrosi-header";
            cardHeader.innerHTML = "<h2 class='text-center font-weight-light my-2'>Otro sí " + (i + 1) + "</h2>";


            var cardBody = document.createElement("div");
            cardBody.className = "card-body";

            cardBody.innerHTML = `
            <form id="formulario_otrosi_${i}" action="/arbimaps/Arbimaps/vistas/Personal/otrosi_guardar.php" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label" for="otr_cedula_${i}">N° Identidad</label>
                        <input class="form-control" name="otr_cedula" id="otr_cedula_${i}" placeholder="Ingrese el número de identidad">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="otr_tipo_${i}">Tipo de Otro sí</label>
                        <select class="form-control custom-select" id="otr_tipo_${i}" name="otr_tipo">
                            <option value="">SELECCIONE</option>
                                <option value="PRÓRROGA">PRÓRROGA</option>
                                <option value="ADICIÓN">ADICIÓN</option>
                                <option value="CAMBIO_DE_ROL">CAMBIO DE ROL</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="otr_proyecto_${i}">Proyecto</label>
                        <select class="form-control custom-select" id="otr_proyecto_${i}" name="otr_proyecto">
                            <option value="">SELECCIONE</option>
                                <option value="ANT_AVALUOS">ANT_AVALÚOS</option>
                                <option value="ADMINISTRATIVO">ADMINISTRATIVO</option>
                                <option value="ARBOLETES">VALOR + ARBOLETES</option>
                                <option value="CONTABILIDAD">CONTABILIDAD</option>
                                <option value="GENERAL_ARBITRIUM">GENERAL - ARBITRIUM</option>
                                <option value="LEIVA">IGAC LEIVA</option>
                                <option value="NECOCLÍ">VALOR + NECOCLÍ</option>
                                <option value="SAN_JUAN">VALOR + SAN JUAN DE URABA</option>
                                <option value="SAN_PEDRO">VALOR + SAN PEDRO DE URABA</option>
                                <option value="VALLE_ABURRA">VALLE DE ABURRA BELLO</option>
                                <option value="VALLE_GUAMUEZ">IGAC VALLE DEL GUAMUEZ</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="otr_fecha_${i}">Fecha Inicio Otro sí</label>
                        <input class="form-control" id="otr_fecha_${i}" name="otr_fecha" type="date" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="otr_fecha_Final_${i}">Fecha Final Otro sí</label>
                        <input class="form-control" id="otr_fecha_Final_${i}" name="otr_fecha_Final" type="date" />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="otr_salario_${i}">Salario/Honorarios</label>
                        <input class="form-control" 
                            id="con_salario_mostrado_${i}" 
                            name="con_salario_mostrado" 
                            type="text" 
                            placeholder="Ingrese el salario del personal" 
                            data-hidden-id="otr_salario_${i}" 
                            oninput="formatCurrency(this, 'otr_salario_${i}')">

                        <input type="hidden" id="otr_salario_${i}" name="otr_salario">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="otr_cargo_${i}">Cargo/Rol</label>
                        <select class="form-control custom-select" id="otr_cargo_${i}" name="otr_cargo">
                            <option value="">SELECCIONE</option>
                                        <option value="ABOGADOS">ABOGADOS</option>
                                        <option value="ASIGNADOR">ASIGNADOR</option>
                                        <option value="AUXILIAR_ADMINISTRATIVO">AUXILIAR_ADMINISTRATIVO</option>
                                        <option value="AUXILIAR_ADMINISTRATIVO_VENTANILLA_UNICA">AUXILIAR ADMINISTRATIVO VENTANILLA ÚNICA</option>
                                        <option value="AUXILIAR_ADMINISTRATIVO_Y_DE_OPERACIONES">AUXILIAR ADMINISTRATIVO Y DE OPERACIONES</option>
                                        <option value="AUXILIAR_DE_SERVICIOS_GENERALES">AUXILIAR DE SERVICIOS GENERALES</option>
                                        <option value="AUXILIAR_SOCIAL">AUXILIAR SOCIAL</option>
                                        <option value="AUXILIAR_VENTANILLA">AUXILIAR VENTANILLA</option>
                                        <option value="CONSOLIDADOR">CONSOLIDADOR</option>
                                        <option value="CONSULTA_VUR">CONSULTA VUR</option>
                                        <option value="CONTROL_DE_CALIDAD">CONTROL DE CALIDAD</option>
                                        <option value="COORDINADOR_CUADRILLA">COORDINADOR CUADRILLA</option>
                                        <option value="DESARROLLADOR">DESARROLLADOR</option>
                                        <option value="DIGITADOR">DIGITADOR</option>
                                        <option value="DIGITALIZADOR">DIGITALIZADOR</option>
                                        <option value="DIRECTOR_DE_OPERACIONES">DIRECTOR DE OPERACIONES</option>
                                        <option value="DIRECTOR_FINANCIERO">DIRECTOR FINANCIERO</option>
                                        <option value="DIRECTOR_PLANEACION">DIRECTOR PLANEACIÓN</option>
                                        <option value="DIRECTORA_COMERCIAL">DIRECTORA COMERCIAL</option>
                                        <option value="EDITOR">EDITOR</option>
                                        <option value="GERENTE">GERENTE</option>
                                        <option value="GESTORA_DE_TALENTO_HUMANO">GESTORA DE TALENTO HUMANO</option>
                                        <option value="HSEQ">HSEQ</option>
                                        <option value="JEFE_DE_OPERACIONES">JEFE DE OPERACIONES</option>
                                        <option value="LIDER_CONSOLIDACION">LÍDER CONSOLIDACIÓN</option>
                                        <option value="LIDER_CONSOLIDACION">LÍDER CONSOLIDACIÓN</option>
                                        <option value="LIDER_RECONOCIMIENTO">LÍDER RECONOCIMIENTO</option>
                                        <option value="LIDER_TECNICO">LÍDER TÉCNICO</option>
                                        <option value="MERGIN_MAPS">MERGIN MAPS</option>
                                        <option value="PRESUPUESTO">PRESUPUESTO</option>
                                        <option value="PROFESIONAL_2_CON_POSGRADO">PROFESIONAL 2 CON POSGRADO</option>
                                        <option value="PROFESIONAL_3_CON_POSGRADO">PROFESIONAL 3 CON POSGRADO</option>
                                        <option value="PROFESIONAL_HSEQ_CON_POSGRADO">PROFESIONAL HSEQ CON POSGRADO</option>
                                        <option value="PROFESIONAL_CATASTRAL_1">PROFESIONAL CATASTRAL 1</option>
                                        <option value="PROGRAMADORA_DE_SOFTWARE">PROGRAMADORA DE SOFTWARE</option>
                                        <option value="PROFESIONAL_SIG">PROFESIONAL SIG</option>
                                        <option value="PROFESIONAL_SOCIAL">PROFESIONAL_SOCIAL</option>
                                        <option value="RECONOCEDOR_PREDIAL">RECONOCEDOR PREDIAL</option>
                                        <option value="SOPORTE_NIVEL_1">SOPORTE NIVEL 1</option>
                                        <option value="SOPORTE_NIVEL_2">SOPORTE NIVEL 2</option>
                                        <option value="SUBGERENTE_GENERAL">SUBGERENTE GERENTE</option>
                                        <option value="TECNOLOGO">TECNOLOGO</option>
                                        <option value="TECNOLOGO_GRADO_2">TECNOLOGO GRADO 2</option>
                                        <option value="RECONOCEDOR_PREDIAL_JUNIOR">RECONOCEDOR PREDIAL JUNIOR</option>
                                        <option value="COORDINADOR_SIG">COORDINADOR SIG</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                            <label for="otr_otrosi_${i}" class="form-label">Otro sí</label>
                            <input type="file" class="form-control" id="otr_otrosi_${i}" name="otr_otrosi">
                    </div>
                    <div class="col-md-6">
                            <label for="otr_cumplimiento_${i}" class="form-label">Póliza de Cumplimiento</label>
                            <input type="file" class="form-control" id="otr_cumplimiento_${i}" name="otr_cumplimiento">
                    </div>
                    <div class="col-md-6">
                            <label for="otr_acta_${i}" class="form-label">Acta de Inicio</label>
                            <input type="file" class="form-control" id="otr_acta_${i}" name="otr_acta">
                    </div>
                    <div class="col-md-6">
                            <label for="otr_actaFi_${i}" class="form-label">Acta final</label>
                            <input type="file" class="form-control" id="otr_actaFi_${i}" name="otr_actaFi">
                    </div>
                </div>
            </form>
        `;

            card.appendChild(cardHeader);
            card.appendChild(cardBody);

            formularios.push(card);
            document.getElementById("unidadesGeneradas").appendChild(card);
        }

        // Crear un botón para enviar todos los formularios generados
        var botonWrapper = document.createElement("div");
        botonWrapper.className = "mt-3";

        var botonEnviar = document.createElement("button");
        botonEnviar.type = "button";
        botonEnviar.textContent = "Enviar Formularios";
        botonEnviar.className = "btn btn-guardar w-100 text-center";

        botonEnviar.addEventListener("click", function(event) {
            event.preventDefault(); // Prevenir el comportamiento por defecto del botón

            // Enviar cada formulario individualmente
            formularios.forEach(function(card, index) {
                var form = document.getElementById(`formulario_otrosi_${index}`);
                if (form && form.checkValidity()) {
                    const formData = new FormData(form);
                    fetch(form.action, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(text => {
                            console.log("💬 Respuesta cruda del servidor:", text);
                            try {
                                const data = JSON.parse(text);
                                if (data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Éxito',
                                        text: data.message || 'Datos guardados correctamente.',
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: data.message || 'Ocurrió un error en el servidor.'
                                    });
                                }
                            } catch (e) {
                                console.error("⚠️ Respuesta inesperada del servidor:", text);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error de respuesta',
                                    text: 'La respuesta del servidor no tiene el formato esperado.'
                                });
                            }
                        })
                        .catch(error => {
                            console.error("❌ Error al enviar los datos:", error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error de red',
                                text: 'Hubo un problema al enviar los datos. Inténtalo de nuevo.'
                            });
                        });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Campos incompletos',
                        text: 'Por favor, completa todos los campos requeridos en el formulario2 ' + (index + 1) + '.',
                        confirmButtonText: 'Entendido'
                    });
                }
            });
        });

        botonWrapper.appendChild(botonEnviar);

        // Agregar el botón al div unidadesGeneradas
        document.getElementById("unidadesGeneradas").appendChild(botonWrapper);
    }
</script>

<script>
    // ===================== CREAR ESTUDIOS (SIN FORM DENTRO DE FORM) =====================
    function crearEstudios() {
        const cantidad = parseInt(document.getElementById("est_estudios")?.value, 10);
        const estudiosContainer = document.getElementById("estudios");

        if (!estudiosContainer) return;

        if (!cantidad || cantidad < 1) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Por favor, ingresa un número válido de estudios.",
            });
            return;
        }

        // Limpiar antes de generar
        estudiosContainer.innerHTML = "";

        const wrappers = []; // guardamos los contenedores para enviarlos luego

        for (let i = 0; i < cantidad; i++) {
            const card = document.createElement("div");
            card.className = "card shadow-lg border-0 mt-4 otrosi-card";

            const cardHeader = document.createElement("div");
            cardHeader.className = "card-header otrosi-header";
            cardHeader.innerHTML =
                "<h2 class='text-center font-weight-light my-2'>Estudio " + (i + 1) + "</h2>";

            const cardBody = document.createElement("div");
            cardBody.className = "card-body";

            // OJO: aquí usamos DIV en vez de FORM
            const wrapperId = `wrapper_estudios_${i}`;
            const actionUrl = "/arbimaps/Arbimaps/vistas/Personal/estudios_guardar.php";

            cardBody.innerHTML = `
        <div id="${wrapperId}" class="estudios-wrapper" data-action="${actionUrl}">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="est_cedula_${i}">N° Identidad</label>
              <input class="form-control" name="est_cedula" id="est_cedula_${i}" placeholder="Ingrese el número de identidad">
            </div>

            <div class="col-md-6">
              <label class="form-label" for="est_fechaGra_${i}">Fecha de Grado</label>
              <input class="form-control" id="est_fechaGra_${i}" name="est_fechaGra" type="date" />
            </div>

            <div class="col-md-6">
              <label class="form-label" for="est_nom_estudio_${i}">Nombre del Título</label>
              <input class="form-control" name="est_nom_estudio" id="est_nom_estudio_${i}" placeholder="Ingrese el nombre del título">
            </div>

            <div class="col-md-6">
              <label class="form-label" for="est_escolaridad_${i}">Escolaridad</label>
              <select class="form-control custom-select" id="est_escolaridad_${i}" name="est_escolaridad">
                <option value="">SELECCIONE</option>
                <option value="MAGISTER">MAGÍSTER</option>
                <option value="DOCTORADO">DOCTORADO</option>
                <option value="TECNOLOGIA_ESPECIALIZADA">TECNOLOGÍA ESPECIALIZADA</option>
                <option value="CONVALIDACION">CONVALIDACIÓN</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label" for="est_diploma_${i}">Diploma</label>
              <input class="form-control" id="est_diploma_${i}" name="est_diploma" type="file">
            </div>

            <div class="col-md-6">
              <label class="form-label" for="est_Cestudios_${i}">Certificado de Estudios</label>
              <input class="form-control" id="est_Cestudios_${i}" name="est_Cestudios" type="file">
            </div>
          </div>
        </div>
      `;

            card.appendChild(cardHeader);
            card.appendChild(cardBody);
            estudiosContainer.appendChild(card);

            wrappers.push(wrapperId);
        }

        // Botón para enviar todos los "wrappers"
        const botonWrapper = document.createElement("div");
        botonWrapper.className = "mt-3";

        const botonEnviar = document.createElement("button");
        botonEnviar.type = "button";
        botonEnviar.textContent = "Enviar Formularios";
        botonEnviar.className = "btn btn-guardar w-100 text-center";

        botonEnviar.addEventListener("click", async function(event) {
            event.preventDefault();

            for (let index = 0; index < wrappers.length; index++) {
                const wrapper = document.getElementById(wrappers[index]);
                if (!wrapper) continue;

                const url = wrapper.dataset.action;

                // Armamos FormData desde inputs del wrapper
                const formData = new FormData();
                const fields = wrapper.querySelectorAll("input, select, textarea");

                fields.forEach((el) => {
                    if (!el.name) return;

                    if (el.type === "file") {
                        if (el.files && el.files.length > 0) {
                            formData.append(el.name, el.files[0]);
                        } else {
                            // si no cargó archivo, no lo mandamos
                        }
                    } else if (el.type === "checkbox") {
                        formData.append(el.name, el.checked ? "1" : "0");
                    } else if (el.type === "radio") {
                        if (el.checked) formData.append(el.name, el.value);
                    } else {
                        formData.append(el.name, el.value);
                    }
                });

                try {
                    const resp = await fetch(url, {
                        method: "POST",
                        body: formData
                    });
                    const text = await resp.text();

                    console.log("Respuesta cruda del servidor (estudios):", text);

                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        Swal.fire({
                            icon: "error",
                            title: "Error de respuesta",
                            text: `El servidor no devolvió JSON válido (Estudio ${index + 1}).`,
                        });
                        continue;
                    }

                    if (data.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Éxito",
                            text: data.message || `Estudio ${index + 1} guardado correctamente.`,
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: data.message || `Ocurrió un error en el servidor (Estudio ${index + 1}).`,
                        });
                    }
                } catch (error) {
                    console.error("Error al enviar los datos:", error);
                    Swal.fire({
                        icon: "error",
                        title: "Error de red",
                        text: `Hubo un problema al enviar (Estudio ${index + 1}).`,
                    });
                }
            }
        });

        botonWrapper.appendChild(botonEnviar);
        estudiosContainer.appendChild(botonWrapper);
    }
</script>

<script>
    // ===================== CREAR EXPERIENCIAS LABORALES (SIN FORM DENTRO DE FORM) =====================
    function crearExperiencias() {
        const cantidad = parseInt(document.getElementById("exp_cantidad")?.value, 10);
        const experienciasContainer = document.getElementById("experiencias");

        if (!experienciasContainer) return;

        if (!cantidad || cantidad < 1) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Por favor, ingresa un número válido de experiencias.",
            });
            return;
        }

        experienciasContainer.innerHTML = "";

        const wrappers = [];

        for (let i = 0; i < cantidad; i++) {
            const card = document.createElement("div");
            card.className = "card shadow-lg border-0 mt-4 otrosi-card";

            const cardHeader = document.createElement("div");
            cardHeader.className = "card-header otrosi-header";
            cardHeader.innerHTML =
                "<h2 class='text-center font-weight-light my-2'>Experiencia " + (i + 1) + "</h2>";

            const cardBody = document.createElement("div");
            cardBody.className = "card-body";

            const wrapperId = `wrapper_experiencia_${i}`;
            const actionUrl = "/arbimaps/Arbimaps/vistas/Personal/experiencia_guardar.php";

            cardBody.innerHTML = `
              <div id="${wrapperId}" class="experiencia-wrapper" data-action="${actionUrl}">
                <input type="hidden" name="con_id" value="${document.getElementById("con_id")?.value || ""}">

                <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold" for="exp_empresa_${i}">Empresa o Entidad</label>
                    <input type="text" class="form-control" id="exp_empresa_${i}" name="exp_empresa"
                    placeholder="Ingrese la empresa o entidad...">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold" for="exp_entidad_${i}">Tipo de Entidad</label>
                    <select class="form-select" id="exp_entidad_${i}" name="exp_entidad">
                    <option value="" disabled selected>Selecciona</option>
                    <option value="PUBLICA">PUBLICA</option>
                    <option value="PRIVADA">PRIVADA</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold" for="exp_pais_${i}">País</label>
                    <input type="text" class="form-control" id="exp_pais_${i}" name="exp_pais"
                    placeholder="Ingrese el país">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold" for="exp_departamento_${i}">Departamento</label>
                    <input type="text" class="form-control" id="exp_departamento_${i}" name="exp_departamento"
                    placeholder="Ingrese el departamento">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold" for="exp_municipio_${i}">Municipio</label>
                    <input type="text" class="form-control" id="exp_municipio_${i}" name="exp_municipio"
                    placeholder="Ingrese el municipio">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold" for="exp_correo_entidad_${i}">Correo electrónico entidad</label>
                    <input type="email" class="form-control" id="exp_correo_entidad_${i}" name="exp_correo_entidad"
                    placeholder="correo@ejemplo.com">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold" for="exp_telefonos_${i}">Teléfonos</label>
                    <input type="text" class="form-control" id="exp_telefonos_${i}" name="exp_telefonos"
                    placeholder="Ingrese el número de teléfono">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold" for="exp_fecha_ingreso_${i}">Fecha de ingreso</label>
                    <input type="date" class="form-control" id="exp_fecha_ingreso_${i}" name="exp_fecha_ingreso">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold" for="exp_fecha_salida_${i}">Fecha de retiro</label>
                    <input type="date" class="form-control" id="exp_fecha_salida_${i}" name="exp_fecha_salida">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold" for="exp_cargo_${i}">Cargo o contrato</label>
                    <input type="text" class="form-control" id="exp_cargo_${i}" name="exp_cargo"
                    placeholder="Ingrese el cargo o contrato">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold" for="exp_dependencias_${i}">Dependencia</label>
                    <input type="text" class="form-control" id="exp_dependencias_${i}" name="exp_dependencias"
                    placeholder="Ingrese la dependencia">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold" for="exp_direccion_${i}">Dirección</label>
                    <input type="text" class="form-control" id="exp_direccion_${i}" name="exp_direccion"
                    placeholder="Ingrese la dirección">
                </div>

                <div class="col-md-6 mt-3">
                    <label class="form-label fw-bold" for="exp_certificado_laboral_${i}">Certificado laboral (PDF)</label>
                    <input type="file"
                            class="form-control"
                            id="exp_certificado_laboral_${i}"
                            name="exp_certificado_laboral"
                            accept="application/pdf">
                </div>
                </div>
            </div>
            `;


            card.appendChild(cardHeader);
            card.appendChild(cardBody);
            experienciasContainer.appendChild(card);

            wrappers.push(wrapperId);
        }

        // Botón para enviar todos los wrappers
        const botonWrapper = document.createElement("div");
        botonWrapper.className = "mt-3";

        const botonEnviar = document.createElement("button");
        botonEnviar.type = "button";
        botonEnviar.textContent = "Enviar Experiencias";
        botonEnviar.className = "btn btn-guardar w-100 text-center";

        botonEnviar.addEventListener("click", async function(event) {
            event.preventDefault();

            for (let index = 0; index < wrappers.length; index++) {
                const wrapper = document.getElementById(wrappers[index]);
                if (!wrapper) continue;

                const url = wrapper.dataset.action;
                const formData = new FormData();

                const fields = wrapper.querySelectorAll("input, select, textarea");
                fields.forEach((el) => {
                    if (!el.name) return;

                    if (el.type === "file") {
                        if (el.files && el.files.length > 0) {
                            formData.append(el.name, el.files[0]);
                        }
                    } else if (el.type === "checkbox") {
                        formData.append(el.name, el.checked ? "1" : "0");
                    } else if (el.type === "radio") {
                        if (el.checked) formData.append(el.name, el.value);
                    } else {
                        formData.append(el.name, el.value);
                    }
                });

                try {
                    const resp = await fetch(url, {
                        method: "POST",
                        body: formData
                    });
                    const text = await resp.text();

                    console.log("💬 Respuesta cruda del servidor (experiencia):", text);

                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        Swal.fire({
                            icon: "error",
                            title: "Error de respuesta",
                            text: `El servidor no devolvió JSON válido (Experiencia ${index + 1}).`,
                        });
                        continue;
                    }

                    if (data.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Éxito",
                            text: data.message || `Experiencia ${index + 1} guardada correctamente.`,
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: data.message || `Ocurrió un error en el servidor (Experiencia ${index + 1}).`,
                        });
                    }
                } catch (error) {
                    console.error("❌ Error al enviar los datos:", error);
                    Swal.fire({
                        icon: "error",
                        title: "Error de red",
                        text: `Hubo un problema al enviar (Experiencia ${index + 1}).`,
                    });
                }
            }
        });

        botonWrapper.appendChild(botonEnviar);
        experienciasContainer.appendChild(botonWrapper);
    }
</script>

<style>
    /* PANEL que ocupa el espacio entre los botones */
    .panel-enviar-datos {
        background: #f2f6fa;
        padding: 15px;
        border-radius: 10px;
        border: 2px solid #e2e6ea;
        width: 100%;
        display: flex;
        justify-content: center;
    }

    /* Botón ancho y estilizado */
    .btn-enviar-datos {
        background-color: #003B66;
        color: white !important;
        padding: 12px 50px;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 10px;
        border: none;
        transition: 0.3s ease;
        width: 100%;
        /* 🔥 fuerza a ocupar el espacio disponible */
        max-width: 450px;
        /* 🔥 límite elegante */
        text-align: center;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.15);
    }

    .btn-enviar-datos:hover {
        background-color: #004780;
        transform: translateY(-2px);
        box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.25);
    }
</style>

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
    /* Título igual que el resto, solo alineado a la izquierda */
    .title-vacunas {
        text-align: left !important;
        margin-bottom: 10px;
    }

    /* Marco grande que envuelve TODAS las vacunas */
    .vaccines-box {

        border: 2px dotted #003B66;
        /* Azul */
        padding: 10px;
        border-radius: 10px;
        background: none !important;
        /* SIN fondo */
        margin-top: 10px;
    }

    /* Estilo simple para cada checkbox */
    .checklist-item {
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
</style>

<style>
    /* Título "Carga de Información" alineado a la izquierda */
    .otrosi-title {
        text-align: left !important;
        margin-bottom: 10px;
        font-weight: normal;
        font-size: 0.95rem;
    }

    /* Caja que envuelve "Agregar Otros sí" (similar a vacunas) */
    .otrosi-box {
        border: 2px solid #003B66;
        border-radius: 10px;
        padding: 15px 20px;
        background: none;
    }

    /* Contenedor del input + botón, centrados */
    .otrosi-add-wrapper {
        justify-content: center;
    }

    .otrosi-input-number {
        max-width: 120px;
        text-align: center;
    }

    .btn-otrosi-add {
        background-color: #003B66;
        color: #fff;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        font-size: 1.4rem;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-otrosi-add:hover {
        background-color: #004780;
    }

    /* Card de cada "Otro sí" con encabezado azul y texto blanco */
    .otrosi-card {
        border-radius: 10px;
        overflow: hidden;
    }

    .otrosi-header {
        background-color: #003B66;
        color: #ffffff;
        border-bottom: none;
    }

    .otrosi-header h2 {
        font-size: 1.1rem;
        margin: 0;
    }
</style>



<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>