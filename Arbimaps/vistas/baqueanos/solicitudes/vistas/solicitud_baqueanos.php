<?php
date_default_timezone_set('America/Bogota');

$diaSemana = date('N');

if (!in_array($diaSemana, [1, 2])) {
    echo '
    <style>
        .lock-wrapper{
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            background:linear-gradient(135deg,#f3f6f9 0%,#e9eef3 100%);
            padding:40px;
        }

        .lock-card{
            background:#ffffff;
            border-radius:24px;
            padding:60px 50px;
            text-align:center;
            box-shadow:0 25px 70px rgba(0,47,85,0.12);
            max-width:600px;
            width:100%;
            position:relative;
            overflow:hidden;
            animation:fadeIn .6s ease-out;
        }

        .lock-card::before{
            content:"";
            position:absolute;
            top:0;
            left:0;
            width:100%;
            height:6px;
            background:linear-gradient(90deg,#002F55,#004a80);
        }

        .lock-icon{
            font-size:70px;
            color:#002F55;
            margin-bottom:25px;
            animation:float 3s ease-in-out infinite;
        }

        .lock-title{
            font-size:26px;
            font-weight:700;
            color:#002F55;
            margin-bottom:15px;
        }

        .lock-text{
            font-size:16px;
            color:#6b7280;
            line-height:1.6;
            margin-bottom:25px;
        }

        .lock-badge{
            display:inline-block;
            background:#002F55;
            color:#fff;
            padding:8px 18px;
            border-radius:30px;
            font-size:14px;
            font-weight:600;
            letter-spacing:.5px;
            box-shadow:0 8px 20px rgba(0,47,85,.25);
        }

        @keyframes fadeIn{
            from{opacity:0; transform:translateY(20px);}
            to{opacity:1; transform:translateY(0);}
        }

        @keyframes float{
            0%{transform:translateY(0);}
            50%{transform:translateY(-8px);}
            100%{transform:translateY(0);}
        }
    </style>
    <div class="lock-wrapper">
        <div class="lock-card">
            <div class="lock-icon">
                <i class="bi bi-lock-fill"></i>
            </div>
            <div class="lock-title">
                Acceso restringido temporalmente
            </div>
            <div class="lock-text">
                Las solicitudes de baqueanos solo pueden realizarse los días 
                <strong>lunes y martes</strong> como parte del control operativo semanal.
                <br><br>
                Intenta nuevamente la proxima semana para continuar con el proceso.
            </div>
            <div class="lock-badge">
                Disponible únicamente los lunes y martes
            </div>
        </div>
    </div>
    ';
    exit;
}
?>
<style>
    .stepper-wrapper {
        position: relative;
        padding: 25px 0;
    }

    .stepper-line {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        height: 4px;
        background: #e5e7eb;
        border-radius: 2px;
        z-index: 1;
        width: calc(100% + 30px);
    }

    #stepper-progress {
        height: 100%;
        width: 0%;
        background: #003B66;
        border-radius: 2px;
        transition: width 0.5s ease;
    }

    .stepper {
        position: relative;
        display: flex;
        justify-content: center;
        gap: .6rem;
        flex-wrap: wrap;
        width: fit-content;
        margin: 0 auto;
        z-index: 2;
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
        position: relative;
        z-index: 2;
    }

    .step.active {
        background: #fff;
        border: 2px solid #003B66;
        box-shadow: 0 0 0 4px rgba(0, 59, 102, 0.15);
    }

    .step.completed {
        background: #003B66;
        color: #fff;
    }

    @media (min-width:1600px) {
        .step {
            font-size: 0.8rem;
        }
    }

    @media (max-width: 768px) {
        .stepper {
            flex-direction: column;
            align-items: center;
        }

        .step {
            width: 100%;
            text-align: center;
            margin: 10px 0;
            font-size: 1rem;
        }

        .stepper-line {
            height: 3px;
        }

        #stepper-progress {
            height: 100%;
        }
    }

    @media (max-width: 480px) {
        .step {
            font-size: 0.9rem;
            padding: 8px 12px;
        }

        #stepper-progress {
            background: #2d2d2d;
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


    /* ===== SweetAlert2: estilo EXACTO tipo "Saved Actions" ===== */

    .swal2-popup.swal-saved {
        padding: 0 !important;
        margin-left: 15%;
        border-radius: 22px !important;
        width: 620px !important;
        max-width: 95vw !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .swal2-container.saved-backdrop {
        background: rgba(0, 0, 0, .18) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 24px !important;
    }

    .swal-saved-card {
        background: #fff;
        border-radius: 22px;
        border: 1px solid rgba(0, 0, 0, .06);
        box-shadow: 0 18px 60px rgba(0, 0, 0, .16);
        padding: 26px 28px 28px;
        gap: 18px;
    }

    .swal-saved-header {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #111827;
        font-weight: 700;
        font-size: 18px;
    }

    .swal-saved-divider {
        height: 1px;
        background: rgba(0, 0, 0, .08);
        margin: 14px 0 22px;
    }

    .swal-saved-body {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 14px;
        padding: 8px 0 2px;
    }

    .swal-saved-text {
        color: #9CA3AF;
        font-size: 16px;
        line-height: 1.35;
        max-width: 320px;
    }

    .swal-saved-btn {
        margin-top: 4px;
        border: 1px solid rgba(0, 0, 0, .10);
        background: #fff;
        color: #111827;
        font-weight: 600;
        border-radius: 12px;
        padding: 10px 14px;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 2px 0 rgba(0, 0, 0, .03);
        cursor: pointer;
    }

    .swal-saved-btn:active {
        transform: translateY(1px);
    }

    .swal-saved-btn svg {
        opacity: .85;
    }

    .swal-saved-illu svg {
        width: 280px;
        height: 280px;
    }
</style>
<div class="container-fluid px-3">
    <div class="text-center my-4">
        <h1 class="h3 text-center mb-0" style="color:#002F55"><b>SOLICITUD BAQUEANOS</b></h1>
        <small class="text-muted">Formulario para solicitar un baqueano</small>
    </div>

    <div class="stepper-wrapper mb-0">
        <div class="stepper">
            <div class="stepper-line">
                <div id="stepper-progress"></div>
            </div>

            <button type="button" class="step active">Datos generales</button>
            <button type="button" class="step">Plan de trabajo</button>
        </div>
    </div>

    <div class="card card-especial-tres rounded-4 shadow-lg mx-3 border-0 my-3">
        <div class="card-body p-0 mb-3">
            <form action="./vistas/baqueanos/solicitudes/acciones/procesar_solicitud.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="sol_usuario_id" id="sol_usuario_id" value="<?php echo $_SESSION['id_usuario']; ?>">
                <div id="section1" class="seccion-formulario">
                    <div class="mb-3 d-flex align-items-center text-start rounded-4 card-header shadow m-2 py-3 text-white">
                        <i class="bi bi-person-circle me-3 fs-2"></i>
                        <div>
                            <h3 class="h5 mb-1">Datos Generales</h3>
                            <p class="mb-0" style="font-size: 0.85rem; color:#999999">Información básica de la solicitud.</p>
                        </div>
                    </div>

                    <div class="row p-4 g-3">
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label class="form-label" for="sb_tipo_documento" style="font-size: 0.9em;"><b>Seleccione Tipo Documento</b></label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="sb_tipo_documento"><i class="bi bi-credit-card-2-front"></i></label>
                                <select class="form-select" style="font-size: 0.9rem;" id="sb_tipo_documento" name="sb_tipo_documento" required>
                                    <option value="" disabled selected><b>SELECCIONE</b></option>
                                    <option value="Cedula_Ciudadania">Cédula de Ciudadanía</option>
                                    <option value="Cedula_Extranjeria">Cédula de Extranjería</option>
                                    <option value="NIT">N.I.T.</option>
                                    <option value="Pasaporte">Pasaporte</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_numero_identidad">Número Documento de Identidad</label>
                            <div class="input-group">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-vcard"></i></span>
                                <input class="form-control shadow-sm" style="font-size:0.9em;" id="sb_numero_identidad" name="sb_numero_identidad" type="number"
                                    placeholder="Ingrese Número de Identidad.." maxlength="10"
                                    oninput="if(this.value.length > 10) this.value = this.value.slice(0, 10)" required>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_baqueano_nombre">Nombres</label>
                            <div class="input-group">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person"></i></span>
                                <input class="form-control shadow-sm" style="font-size:0.9em;" id="sb_baqueano_nombre" name="sb_baqueano_nombre" type="text"
                                    placeholder="Ingrese los nombres..." required>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_baqueano_apellido">Apellidos</label>
                            <div class="input-group">
                                <span class="input-group-text shadow-sm"><i class="bi bi-people"></i></span>
                                <input class="form-control shadow-sm" style="font-size:0.9em;" id="sb_baqueano_apellido" name="sb_baqueano_apellido" type="text"
                                    placeholder="Ingrese los apellidos..." required>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label class="form-label fw-bold" for="sb_telefono_baqueano" style="font-size:0.9em;">Número Telefónico</label>
                            <div class="input-group">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-raised-hand"></i></span>
                                <input class="form-control shadow-sm" id="sb_telefono_baqueano" name="sb_telefono_baqueano" type="tel"
                                    placeholder="Ingrese Número Telefónico..." required>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="sb_correo_baqueano" class="form-label fw-bold" style="font-size:0.9em;">Correo Electrónico</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="bi bi-envelope-at"></i></span>
                                <input type="email" class="form-control" id="sb_correo_baqueano" placeholder="Ingrese el correo electronico..."
                                    name="sb_correo_baqueano" required>
                            </div>
                        </div>

                        <div class="row justify-content-center">
                            <div class="col-md-4 p-1 px-2 my-2">
                                <label for="sb_direccion" class="form-label fw-bold" style="font-size:0.9em;">Dirección</label>
                                <div class="input-group">
                                    <span class="input-group-text shadow-sm"><i class="bi bi-geo-alt"></i></span>
                                    <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="sb_direccion"
                                        name="sb_direccion" placeholder="Ingrese la dirección..." required>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 my-4" style="border-bottom:2px dashed #002f557a"></div>

                        <div class="col-12 mt-0">
                            <h6 class="fw-bold p-2 text-white text-center w-25 rounded-3" style="background-color: #002F55;">
                                Información Financiera
                            </h6>
                        </div>

                        <!-- Cuenta Bancaria -->
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label class="form-label fw-bold" for="sb_cuenta" style="font-size:0.9em;">Cuenta Bancaria</label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="sb_cuenta"><i class="bi bi-bank2"></i></label>
                                <select class="form-select" style="font-size:0.9em;" id="sb_cuenta" name="sb_cuenta">
                                    <option value="" disabled selected><b>SELECCIONE</b></option>
                                    <option value="BANCOLOMBIA">BANCOLOMBIA</option>
                                    <option value="DAVIPLATA">DAVIPLATA</option>
                                    <option value="DAVIVIENDA">DAVIVIENDA</option>
                                    <option value="NEQUI">NEQUI</option>
                                    <option value="OTRO">OTRO</option>
                                </select>
                            </div>
                        </div>

                        <!-- Especifique otra cuenta (AHORA ES HORIZONTAL EN SU COLUMNA) -->
                        <div class="col-md-4 p-1 px-2 my-2 d-none" id="campo_otro">
                            <label class="form-label fw-bold" for="sb_cuenta_otro" style="font-size:0.9em;">Especifique otra cuenta</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="bi bi-pencil-square"></i></span>
                                <input
                                    type="text"
                                    class="form-control shadow-sm"
                                    style="font-size:0.9em;"
                                    id="sb_cuenta_otro"
                                    name="sb_cuenta_otro"
                                    placeholder="Ingrese el nombre del banco..">
                            </div>
                        </div>

                        <!-- Tipo de Cuenta -->
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="sb_tipo_cuenta" class="form-label fw-bold" style="font-size:0.9em;">Tipo de Cuenta</label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="sb_tipo_cuenta"><i class="bi bi-safe"></i></label>
                                <select class="form-select" style="font-size:0.9em;" id="sb_tipo_cuenta" name="sb_tipo_cuenta">
                                    <option value="" disabled selected>Selecciona</option>
                                    <option value="AHORROS">AHORROS</option>
                                    <option value="CORRIENTE">CORRIENTE</option>
                                </select>
                            </div>
                        </div>

                        <!-- Nº Cuenta -->
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="sb_num_cuenta" class="form-label fw-bold" style="font-size:0.9em;">Nº Cuenta Bancaria</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="bi bi-bank"></i></span>
                                <input type="text" class="form-control shadow-sm" style="font-size:0.9em;" id="sb_num_cuenta" name="sb_num_cuenta"
                                    placeholder="Ingresa el numero de cuenta...">
                            </div>
                        </div>

                        <!-- Titular (YA NO VA EN ROW APARTE, QUEDA HORIZONTAL) -->
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_titular">Titular de la Cuenta</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                <input class="form-control shadow-sm" style="font-size:0.9em;" id="sb_titular" name="sb_titular" type="text"
                                    placeholder="Ingrese el titular...">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="button"
                                class="btn text-white btn-next-section px-4 d-inline-flex align-items-center"
                                style="background-color:#002F55;"
                                data-next="section2">
                                Siguiente <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div id="section2" class="seccion-formulario d-none">
                    <div class="mb-3 d-flex align-items-center text-start rounded-4 card-header shadow m-2 py-3 text-white">
                        <i class="bi bi-person-circle me-3 fs-2"></i>
                        <div>
                            <h3 class="h5 mb-1">Plan de Trabajo</h3>
                            <p class="mb-0" style="font-size: 0.85rem; color:#999999">Información del plan de trabajo a desarrollar.</p>
                        </div>
                    </div>

                    <div class="row p-4 g-3">
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_profesional_baqueano">Profesional Social</label>
                            <div class="input-group">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-workspace"></i></span>
                                <input class="form-control shadow-sm" style="font-size:0.9em;" id="sb_profesional_baqueano" name="sb_profesional_baqueano" type="text"
                                    placeholder="Ingrese el nombre del profesional social encargado..." required>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_coordinador">Coordinador</label>
                            <div class="input-group">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-gear"></i></span>
                                <input class="form-control shadow-sm" style="font-size:0.9em;" id="sb_coordinador" name="sb_coordinador" type="text"
                                    placeholder="Ingrese el nombre del coordinador..." required>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label class="form-label fw-bold" for="sb_year_select" style="font-size:0.9em;">Año</label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="sb_year_select"><i class="bi bi-calendar3"></i></label>
                                <select class="form-select" id="sb_year_select" name="sb_year_select" style="font-size:0.9em;" disabled>
                                    <option value="2025">2025</option>
                                    <option value="2026">2026</option>
                                    <option value="2027">2027</option>
                                    <option value="2028">2028</option>
                                </select>
                            </div>
                            <input type="hidden" id="sb_year" name="sb_year" value="">
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="sb_fecha_inicio" class="form-label fw-bold" style="font-size:0.9em;">Fecha Inicio</label>
                            <div class="input-group">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar2-event"></i></span>
                                <input type="date" class="form-control shadow-sm" style="font-size:0.9em;" id="sb_fecha_inicio" name="sb_fecha_inicio" required>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="sb_fecha_fin" class="form-label fw-bold" style="font-size:0.9em;">Fecha Fin</label>
                            <div class="input-group">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar2-event"></i></span>
                                <input type="date" class="form-control shadow-sm" style="font-size:0.9em;" id="sb_fecha_fin" name="sb_fecha_fin" required>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_dias_calculados">Total de Días</label>
                            <div class="input-group">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-workspace"></i></span>
                                <input class="form-control shadow-sm" style="font-size:0.9em;" id="sb_dias_calculados"
                                    placeholder="Se calcula automáticamente" name="sb_dias_calculados" type="text" readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_cobro_diario">Valor a Cobrar por Día</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text shadow-sm bg-warning border border-warning"><i class="bi bi-cash-coin"></i></span>
                                <input type="text" class="form-control border border-warning" id="sb_cobro_diario_view"
                                    placeholder="Ingrese el valor por día">

                                <input type="hidden" id="sb_cobro_diario" name="sb_cobro_diario" value="">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_valor_cobrar_visible">Total a Cobrar</label>
                            <div class="input-group">
                                <span class="input-group-text shadow-sm"><i class="bi bi-piggy-bank"></i></span>
                                <input class="form-control shadow-sm" style="font-size:0.9em;" placeholder="Se calcula automáticamente"
                                    id="sb_valor_cobrar_visible" readonly>
                            </div>
                        </div>
                        <input type="hidden" name="sb_valor_cobrar" id="sb_valor_cobrar">

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="sb_unidad_intervencion" class="form-label fw-bold" style="font-size:0.9em;">
                                Unidad de Intervención
                            </label>

                            <div class="input-group">
                                <span class="input-group-text shadow-sm"><i class="bi bi-building"></i></span>
                                <input
                                    class="form-control shadow-sm"
                                    style="font-size:0.9em;"
                                    id="sb_unidad_intervencion"
                                    placeholder="Ingrese la unidad de intervención"
                                    name="sb_unidad_intervencion"
                                    type="text"
                                    inputmode="numeric"
                                    autocomplete="off">
                            </div>
                        </div>

                        <div class="col-12 my-4" style="border-bottom:2px dashed #002f557a"></div>

                        <div class="col-12 mt-0 d-flex justify-content-center">
                            <h6 class="fw-bold p-2 text-white text-center rounded-3 mb-0 w-100 w-md-50 w-lg-25"
                                style="background-color:#002F55;">
                                Otros datos
                            </h6>
                        </div>


                        <div class="col-md-4 p-1 px-2 my-2">
                            <label class="form-label fw-bold" for="sb_tipo_unidad" style="font-size:0.9em;">Tipo de Unidad</label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="sb_tipo_unidad"><i class="bi bi-tags"></i></label>
                                <select class="form-select" style="font-size:0.9em;" id="sb_tipo_unidad" name="sb_tipo_unidad" required>
                                    <option value="" disabled selected><b>SELECCIONE</b></option>
                                    <option value="URBANA">URBANA</option>
                                    <option value="RURAL">RURAL</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label class="form-label fw-bold" for="sb_unidad_operativa" style="font-size:0.9em;">Unidad Operativa</label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="sb_unidad_operativa"><i class="bi bi-tools"></i></label>
                                <select class="form-select" style="font-size:0.9em;" id="sb_unidad_operativa" name="sb_unidad_operativa" required>
                                    <option value="" disabled selected><b>SELECCIONE</b></option>
                                    <option value="BARRIO">BARRIO</option>
                                    <option value="VEREDA">VEREDA</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label class="form-label fw-bold" for="sb_municipio" style="font-size:0.9em;">Municipio</label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="sb_municipio"><i class="bi bi-geo-alt-fill"></i></label>
                                <select class="form-select" style="font-size:0.9em;" id="sb_municipio" name="sb_municipio" required>
                                    <option value="" disabled selected><b>SELECCIONE</b></option>
                                    <option value="Arboletes">ARBOLETES</option>
                                    <option value="San_Juan">SAN JUAN</option>
                                    <option value="Necocli">NECOCLÍ</option>
                                    <option value="San_Pedro">SAN PEDRO</option>
                                    <option value="Guamuez">VALLE DEL GUAMUEZ</option>
                                    <option value="Leiva">LEIVA</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_vereda">Vereda/Barrio</label>
                            <div class="input-group">
                                <span class="input-group-text shadow-sm"><i class="bi bi-house-door-fill"></i></span>
                                <input class="form-control shadow-sm" style="font-size:0.9em;" id="sb_vereda" name="sb_vereda" type="text"
                                    placeholder="Ingrese el nombre de la vereda" required>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label class="form-label fw-bold" for="sb_tipo_actividad" style="font-size:0.9em;">Tipo de Actividad</label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="sb_tipo_actividad"><i class="bi bi-clipboard-check"></i></label>
                                <select class="form-select" style="font-size:0.9em;" id="sb_tipo_actividad" name="sb_tipo_actividad" required onchange="mostrar()">
                                    <option value="" disabled selected><b>SELECCIONE</b></option>
                                    <option value="ATENCION_A_SALDOS">ATENCION A SALDOS</option>
                                    <option value="ATENCION_PQRS">ATENCION PQRS</option>
                                    <option value="OBSERVACION_INTERVENTORIA">OBSERVACION INTERVENTORIA</option>
                                    <option value="RECONOCIMIENTO">RECONOCIMIENTO</option>
                                    <option value="ACOMPAÑAMIENTO_SOCIAL">ACOMPAÑAMIENTO SOCIAL</option>
                                    <option value="CONTROL_DE_CALIDAD">CONTROL DE CALIDAD</option>
                                    <option value="INTERLOCUCIÓN">INTERLOCUCIÓN</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2" id="grupo_lider" style="display:none;">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_lider_cuadrilla">Líder de Cuadrilla</label>
                            <div class="input-group">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-badge-fill"></i></span>
                                <input class="form-control shadow-sm" style="font-size:0.9em;" id="sb_lider_cuadrilla" name="sb_lider_cuadrilla" type="text"
                                    placeholder="Ingrese el nombre del líder de cuadrilla">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2" id="grupo_reconocedor" style="display:none;">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_reconocedor">Reconocedor</label>
                            <div class="input-group">
                                <span class="input-group-text shadow-sm"><i class="bi bi-map-fill"></i></span>
                                <input class="form-control shadow-sm" style="font-size:0.9em;" id="sb_reconocedor" name="sb_reconocedor" type="text"
                                    placeholder="Ingrese el nombre del Reconocedor">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label class="form-label fw-bold" for="sb_transporte" style="font-size:0.9em;">Transporte</label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="sb_transporte"><i class="bi bi-truck"></i></label>
                                <select class="form-select" style="font-size:0.9em;" id="sb_transporte" name="sb_transporte" required onchange="mostrarPorque()">
                                    <option value="" disabled selected><b>SELECCIONE</b></option>
                                    <option value="SI">SI</option>
                                    <option value="NO">NO</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 p-1 px-2 my-2" id="grupo_porque" style="display:none;">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_porque_transporte">¿Por qué?</label>
                            <div class="input-group">
                                <span class="input-group-text shadow-sm"><i class="bi bi-chat-left-text"></i></span>
                                <textarea class="form-control shadow-sm" style="font-size:0.9em;" id="sb_porque_transporte" name="sb_porque_transporte"
                                    placeholder="Ingrese el motivo"></textarea>
                            </div>
                        </div>
                        <div class="col-md-4 p-1 px-2 my-2" id="grupo_valor" style="display:none;">
                            <label class="form-label fw-bold" style="font-size:0.9em;" for="sb_cuanto_transporte">Cuánto Transporte</label>
                            <div class="input-group">
                                <span class="input-group-text shadow-sm"><i class="bi bi-cash-coin"></i></span>
                                <input class="form-control shadow-sm" style="font-size:0.9em;" id="sb_cuanto_transporte" type="text" placeholder="Ej: 50.000">
                            </div>
                        </div>
                        <input type="hidden" id="sb_cuanto_transporte_real" name="sb_cuanto_transporte" value="">
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label class="form-label fw-bold" for="sb_hospedaje" style="font-size:0.9em;">Hospedaje</label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="sb_hospedaje"><i class="bi bi-house-heart"></i></label>
                                <select class="form-select" id="sb_hospedaje" name="sb_hospedaje" required onchange="mostrarHospedaje()">
                                    <option value="" disabled selected><b>SELECCIONE</b></option>
                                    <option value="SI">SI</option>
                                    <option value="NO">NO</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 p-1 px-2 my-2" id="grupo_hospedaje" style="display:none;">
                            <label class="form-label fw-bold" for="sb_porque_hospedaje" style="font-size:0.9em;">¿Por qué?</label>
                            <div class="input-group">
                                <span class="input-group-text shadow-sm"><i class="bi bi-chat-left-text"></i></span>
                                <textarea class="form-control shadow-sm" style="font-size:0.9em;" id="sb_porque_hospedaje" name="sb_porque_hospedaje"
                                    placeholder="Ingrese el motivo"></textarea>
                            </div>
                        </div>
                        <div class="col-md-4 p-1 px-2 my-2" id="grupo_valor_hospedaje" style="display:none;">
                            <label class="form-label fw-bold" for="sb_cuanto_hospedaje" style="font-size:0.9em;">Cuánto Hospedaje</label>
                            <div class="input-group">
                                <span class="input-group-text shadow-sm"><i class="bi bi-cash-coin"></i></span>
                                <input class="form-control shadow-sm" style="font-size:0.9em;" id="sb_cuanto_hospedaje" type="text" placeholder="Ej: 120.000">
                            </div>
                        </div>
                        <input type="hidden" id="sb_cuanto_hospedaje_real" name="sb_cuanto_hospedaje" value="">
                    </div>
                </div>

                <div class="form-group text-center my-3 d-none" id="wrapBtnSolicitar">
                    <button type="button" id="btnSolicitar" class="btn btn-block text-white" style="background-color: #002F55;">
                        <b><i class="bi bi-clipboard2-plus me-2"></i> SOLICITAR </b>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    (function() {
        const el = document.getElementById('sb_unidad_intervencion');
        if (!el) return;

        el.addEventListener('input', () => {
            el.value = el.value.replace(/\D+/g, '');
        });
    })();

    // ===== Año "Otro..." =====
    const sel = document.getElementById('sb_year_select');
    const otro = document.getElementById('sb_year_otro');
    const hidden = document.getElementById('sb_year');
    const wrapOtro = document.getElementById('wrap_year_otro');

    document.addEventListener('DOMContentLoaded', () => {
        const sel = document.getElementById('sb_year_select');
        const hidden = document.getElementById('sb_year');
        if (!sel || !hidden) return;

        const currentYear = new Date().getFullYear();
        let opt = sel.querySelector(`option[value="${currentYear}"]`);
        if (!opt) {
            opt = document.createElement('option');
            opt.value = String(currentYear);
            opt.textContent = String(currentYear);
            sel.appendChild(opt);
        }
        sel.value = String(currentYear);
        hidden.value = String(currentYear);
    });

    function setHidden(v) {
        hidden.value = (v || '').trim();
    }

    if (sel && otro && hidden && wrapOtro) {
        sel.addEventListener('change', () => {
            if (sel.value === 'otro') {
                wrapOtro.style.display = 'flex';
                otro.required = true;
                setHidden('');
                otro.focus();
            } else {
                wrapOtro.style.display = 'none';
                otro.required = false;
                otro.value = '';
                setHidden(sel.value);
            }
        });

        otro.addEventListener('input', () => {
            otro.value = otro.value.replace(/\D/g, '').slice(0, 4);
            setHidden(otro.value);
        });

        const form = sel.closest('form');
        if (form) {
            form.addEventListener('submit', (e) => {
                if (!/^(19|20)\d{2}$/.test(hidden.value)) {
                    e.preventDefault();
                    alert('Debe seleccionar o escribir un año válido (YYYY).');
                }
            });
        }
    }

    // ===== Stepper (solo este sistema) =====
    document.addEventListener('DOMContentLoaded', function() {
        const steps = document.querySelectorAll('.step');
        const sections = document.querySelectorAll('.seccion-formulario');
        const progress = document.getElementById('stepper-progress');
        const wrapBtnSolicitar = document.getElementById('wrapBtnSolicitar');
        let currentStep = 0;

        function toggleSolicitar() {
            if (!wrapBtnSolicitar) return;
            wrapBtnSolicitar.classList.toggle('d-none', currentStep !== 1);
        }

        function showOnlyActiveSection() {
            sections.forEach((sec, i) => {
                const isActive = i === currentStep;
                sec.classList.toggle('d-none', !isActive);
                sec.style.display = isActive ? 'block' : 'none';
            });
            toggleSolicitar();
        }

        function validateCurrentSection() {
            const activeSection = sections[currentStep];
            if (!activeSection) return true;
            const requiredFields = activeSection.querySelectorAll('input:required, select:required, textarea:required');
            for (const field of requiredFields) {
                if (!field.checkValidity()) {
                    field.reportValidity();
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

        steps.forEach((btn, index) => btn.addEventListener('click', () => goToStep(index)));

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
                const nextIndex = Array.from(sections).findIndex(s => s.id === nextId);
                if (nextIndex !== -1) goToStep(nextIndex);
            });
        });
        goToStep(0);
    });

    function mostrar() {
        const tipo = document.getElementById("sb_tipo_actividad");
        const grupoLider = document.getElementById("grupo_lider");
        const grupoReconocedor = document.getElementById("grupo_reconocedor");
        const inpLider = document.getElementById("sb_lider_cuadrilla");
        const inpRecon = document.getElementById("sb_reconocedor");

        if (!tipo || !grupoLider || !grupoReconocedor) return;
        const v = tipo.value;
        const mostrarCampos = (v === "RECONOCIMIENTO" || v === "CONTROL_DE_CALIDAD");

        grupoLider.style.display = mostrarCampos ? "block" : "none";
        grupoReconocedor.style.display = mostrarCampos ? "block" : "none";
        if (inpLider) {
            inpLider.required = mostrarCampos;
            if (!mostrarCampos) inpLider.value = "";
        }
        if (inpRecon) {
            inpRecon.required = mostrarCampos;
            if (!mostrarCampos) inpRecon.value = "";
        }
    }

    function mostrarPorque() {
        const transporte = document.getElementById("sb_transporte");
        const grupoPorque = document.getElementById("grupo_porque");
        const grupoValor = document.getElementById("grupo_valor");
        const txtPorque = document.getElementById("sb_porque_transporte");
        const inpCuanto = document.getElementById("sb_cuanto_transporte");
        const hiddenCuanto = document.getElementById("sb_cuanto_transporte_real");
        if (!transporte || !grupoPorque || !grupoValor) return;

        if (transporte.value === "SI") {
            grupoPorque.style.display = "block";
            grupoValor.style.display = "block";
            if (txtPorque) txtPorque.required = true;
            if (inpCuanto) inpCuanto.required = true;
        } else {
            grupoPorque.style.display = "none";
            grupoValor.style.display = "none";
            if (txtPorque) {
                txtPorque.required = false;
                txtPorque.value = "";
            }
            if (inpCuanto) {
                inpCuanto.required = false;
                inpCuanto.value = "";
            }
            if (hiddenCuanto) hiddenCuanto.value = "";
        }
        calcularValorACobrar();
    }

    function mostrarHospedaje() {
        const hospedaje = document.getElementById("sb_hospedaje");
        const grupoHosp = document.getElementById("grupo_hospedaje");
        const grupoValorHosp = document.getElementById("grupo_valor_hospedaje");
        const txtPorque = document.getElementById("sb_porque_hospedaje");
        const inpCuanto = document.getElementById("sb_cuanto_hospedaje");
        const hiddenCuanto = document.getElementById("sb_cuanto_hospedaje_real");
        if (!hospedaje || !grupoHosp || !grupoValorHosp) return;

        if (hospedaje.value === "SI") {
            grupoHosp.style.display = "block";
            grupoValorHosp.style.display = "block";
            if (txtPorque) txtPorque.required = true;
            if (inpCuanto) inpCuanto.required = true;
        } else {
            grupoHosp.style.display = "none";
            grupoValorHosp.style.display = "none";
            if (txtPorque) {
                txtPorque.required = false;
                txtPorque.value = "";
            }
            if (inpCuanto) {
                inpCuanto.required = false;
                inpCuanto.value = "";
            }
            if (hiddenCuanto) hiddenCuanto.value = "";
        }
        calcularValorACobrar();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const sb_fechaInicio = document.getElementById('sb_fecha_inicio');
        const sb_fechaFin = document.getElementById('sb_fecha_fin');
        const sb_diasCalculados = document.getElementById('sb_dias_calculados');

        function calcularDias() {
            if (!sb_fechaInicio?.value || !sb_fechaFin?.value) {
                sb_diasCalculados.value = '';
                return;
            }
            const inicio = new Date(sb_fechaInicio.value);
            const fin = new Date(sb_fechaFin.value);
            const diferencia = (fin - inicio) + 1;
            const dias = Math.ceil(diferencia / (1000 * 60 * 60 * 24));
            sb_diasCalculados.value = (dias >= 0) ? dias : '';
            calcularValorACobrar();
        }

        sb_fechaInicio?.addEventListener('change', calcularDias);
        sb_fechaFin?.addEventListener('change', calcularDias);
    });

    function calcularValorACobrar() {
        const diasEl = document.getElementById("sb_dias_calculados");
        const valorDiaEl = document.getElementById("sb_cobro_diario");
        const visibleEl = document.getElementById("sb_valor_cobrar_visible");
        const hiddenEl = document.getElementById("sb_valor_cobrar");
        const transporteEl = document.getElementById("sb_cuanto_transporte_real");
        const hospedajeEl = document.getElementById("sb_cuanto_hospedaje_real");
        if (!diasEl || !valorDiaEl || !visibleEl || !hiddenEl) return;

        const dias = parseFloat(diasEl.value) || 0;
        const valorPorDia = parseFloat(valorDiaEl.value) || 0;
        const transporte = parseFloat(transporteEl?.value) || 0;
        const hospedaje = parseFloat(hospedajeEl?.value) || 0;
        const total = (dias * valorPorDia) + transporte + hospedaje;

        visibleEl.value = total.toLocaleString("es-CO", {
            style: "currency",
            currency: "COP",
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
        hiddenEl.value = total;
    }

    function formatearMilesInput(input, hiddenId = null) {
        let valor = input.value.replace(/\D/g, '');
        if (hiddenId) {
            const h = document.getElementById(hiddenId);
            if (h) h.value = valor;
        }
        input.value = valor ? valor.replace(/\B(?=(\d{3})+(?!\d))/g, ".") : '';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const cobroView = document.getElementById("sb_cobro_diario_view");
        const cobroHidden = document.getElementById("sb_cobro_diario");

        cobroView?.addEventListener("input", function() {
            formatearMilesInput(this, "sb_cobro_diario");
            calcularValorACobrar();
        });

        const t = document.getElementById("sb_cuanto_transporte");
        t?.addEventListener("input", function() {
            formatearMilesInput(this, 'sb_cuanto_transporte_real');
            calcularValorACobrar();
        });

        const h = document.getElementById("sb_cuanto_hospedaje");
        h?.addEventListener("input", function() {
            formatearMilesInput(this, 'sb_cuanto_hospedaje_real');
            calcularValorACobrar();
        });

        const selectCuenta = document.getElementById('sb_cuenta');
        const campoOtro = document.getElementById('campo_otro');
        const inpOtro = document.getElementById('sb_cuenta_otro');

        function toggleCuentaOtro() {
            if (!selectCuenta || !campoOtro) return;

            const isOtro = (selectCuenta.value === 'OTRO');

            campoOtro.classList.toggle('d-none', !isOtro);

            if (inpOtro) {
                inpOtro.required = false;
                if (!isOtro) inpOtro.value = '';
            }
        }

        if (selectCuenta && campoOtro) {
            selectCuenta.addEventListener('change', toggleCuentaOtro);
            toggleCuentaOtro();
        }

        mostrar();
        mostrarPorque();
        mostrarHospedaje();
    });

    const btn = document.getElementById('btnSolicitar');
    if (btn) {
        btn.addEventListener('click', function() {
            Swal.fire({
                customClass: {
                    popup: 'swal-saved',
                    container: 'saved-backdrop',
                },
                showConfirmButton: false,
                showCancelButton: false,
                background: 'transparent',
                backdrop: true,
                allowOutsideClick: false,
                html: `
                    <div class="swal-saved-card">
                        <div class="swal-saved-header d-flex justify-content-center aling-items-center text-center w-100">
                            <i class="bi bi-clipboard2-check-fill" style="color:#002F55;font-size:20px;"></i>
                            Confirmación de solicitud
                        </div>
                        <div class="swal-saved-divider"></div>
                        <div class="swal-saved-body">
                            <div class="swal-saved-illu">
                                <i class="bi bi-patch-question-fill" 
                                    style="font-size:70px;color:#002F55;opacity:.15;"></i>
                            </div>
                            <div class="swal-saved-text">
                                Estás a punto de registrar una
                                <strong>solicitud de baqueanos</strong>.<br>
                                Verifica que toda la información sea correcta
                                antes de continuar.
                            </div>
                            <div class="d-flex gap-3 mt-3">
                                <button id="confirmarSolicitud" 
                                        class="btn text-white px-4"
                                        style="background:#002F55;border-radius:12px;">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Confirmar
                                </button>
                                <button id="cancelarSolicitud" 
                                    class="btn btn-light px-4"
                                    style="border-radius:12px;border:1px solid #dee2e6;">
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </div>`,
                didOpen: () => {
                    document.getElementById('confirmarSolicitud')
                        .addEventListener('click', () => {
                            Swal.close();
                            btn.closest('form').submit();
                        });
                    document.getElementById('cancelarSolicitud')
                        .addEventListener('click', () => {
                            Swal.close();
                        });
                }
            });
        });
    }


    document.addEventListener('DOMContentLoaded', function() {

        const fechaInicio = document.getElementById('sb_fecha_inicio');
        const fechaFin = document.getElementById('sb_fecha_fin');

        if (!fechaInicio || !fechaFin) return;
        const hoy = new Date();
        const diaActual = hoy.getDay();
        const diferenciaLunes = (diaActual === 0 ? -6 : 1 - diaActual);
        const lunesActual = new Date(hoy);
        lunesActual.setDate(hoy.getDate() + diferenciaLunes);

        const domingoSiguiente = new Date(lunesActual);
        domingoSiguiente.setDate(lunesActual.getDate() + 13);

        function formatear(fecha) {
            return fecha.toISOString().split('T')[0];
        }

        const hoyStr = formatear(hoy);
        const maxStr = formatear(domingoSiguiente);

        fechaInicio.min = hoyStr;
        fechaInicio.max = maxStr;
        fechaFin.min = hoyStr;
        fechaFin.max = maxStr;

        fechaInicio.addEventListener('change', function() {
            if (!this.value) return;
            fechaFin.min = this.value;

            if (fechaFin.value && fechaFin.value < this.value) {
                fechaFin.value = '';
            }
        });

        fechaFin.addEventListener('change', function() {
            if (!fechaInicio.value) return;
            if (this.value < fechaInicio.value) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Fecha inválida',
                    text: 'La fecha final no puede ser anterior a la fecha inicio.',
                    confirmButtonColor: '#002F55'
                });
                this.value = '';
            }
        });
    });
</script>


<?php if (!empty($_SESSION['swal_success'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Solicitud creada',
            text: 'La solicitud de baqueano fue creada correctamente.',
            confirmButtonColor: '#002F55'
        });
    </script>
<?php unset($_SESSION['swal_success']);
endif; ?>

<?php if (!empty($_SESSION['swal_error'])): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?php echo $_SESSION['swal_error']; ?>',
            confirmButtonColor: '#002F55'
        });
    </script>
<?php unset($_SESSION['swal_error']);
endif; ?>