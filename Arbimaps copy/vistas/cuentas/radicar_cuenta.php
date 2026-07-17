<?php
$idUsuario = $_SESSION['id_usuario'];
$sql2 = "SELECT 
            id_usuario, 
            cedula_usuario, 
            nombre_usuario, 
            apellido_usuario, 
            correo_usuario, 
            celular_usuario 
        FROM usuarios_cons 
        WHERE id_usuario = ?";
$stmt2 = $mysqli->prepare($sql2);
$stmt2->bind_param("i", $idUsuario);
$stmt2->execute();
$resultado2 = $stmt2->get_result();

if ($resultado2->num_rows > 0) {
    $usuario = $resultado2->fetch_assoc();

    $nombres = explode(" ", trim($usuario['nombre_usuario']));
    $primer_nombre      = $nombres[0];
    $segundo_nombre     = isset($nombres[1]) ? $nombres[1] : '';

    $apellidos = explode(" ", trim($usuario['apellido_usuario']));
    $primer_apellido    = $apellidos[0];
    $segundo_apellido   = isset($apellidos[1]) ? $apellidos[1] : '';
} else {
    header("Location: acceso_denegado.php");
    exit();
}
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var myModal = new bootstrap.Modal(document.getElementById('modalAlertaFiltro'));
        myModal.show();
    });
</script>

<style>
    /* Estilo general del popup */
    .arbimaps-popup {
        border-radius: 15px !important;
        padding: 25px !important;
        border: 2px solid #0F5699 !important;
    }

    /* Loader moderno tipo “pulse ring” */
    .custom-loader {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: conic-gradient(#022F55 0% 25%,
                #0F5699 25% 50%,
                #4DA6FF 50% 75%,
                #66CC99 75% 100%);
        animation: spin 2s linear infinite;
        margin: 0 auto;
        position: relative;
    }

    .custom-loader::before {
        content: '';
        position: absolute;
        top: 10px;
        left: 10px;
        right: 10px;
        bottom: 10px;
        background: white;
        border-radius: 50%;
        box-shadow: 0 0 20px rgba(2, 47, 85, 0.1);
    }

    .custom-loader::after {
        content: '';
        position: absolute;
        top: 20px;
        left: 20px;
        right: 20px;
        bottom: 20px;
        background: linear-gradient(135deg, #022F55, #0F5699);
        border-radius: 50%;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    @keyframes pulseAnim {
        0% {
            transform: scale(0.4);
            opacity: 1;
        }

        100% {
            transform: scale(1.4);
            opacity: 0;
        }
    }

    .precargado {
        border: 1px solid #002f55a1;
        box-shadow: 0 0 10px #002f5517 !important;
    }

    .precargado input {
        background-color: #002f5517 !important;
        font-weight: 500;
    }
</style>
<div class="modal fade" id="modalAlertaFiltro" tabindex="-1" aria-labelledby="modalAlertaFiltroLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-white " style="background-color: #002F55;">
                <h5 class="modal-title" id="modalAlertaFiltroLabel">Antes de continuar</h5>
                <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>

            </div>
            <div class="modal-body">
                <!-- Aquí va el mensaje -->
                <h5 class="text-center"><b>Recuerda tener a mano los siguientes documentos:</b></h5></br>
                <ul class="list-unstyled ms-2">
                    <li><i class="bi bi-play"></i>Informe mensual de actividades <strong> VALIDADO</strong></li>
                    <li><i class="bi bi-play"></i>Cuenta de cobro mensual de actividades</li>
                    <li><i class="bi bi-play"></i>Seguridad social</li>
                    <li><i class="bi bi-play"></i>Planilla de pago (Sin clave/Contraseña)</li>
                    <li><i class="bi bi-play"></i>Cedula de ciudadanía, certificado bancario y RUT unificado en un solo documento PDF</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" style="background-color: #002F55; color: white;"
                    data-bs-dismiss="modal">Entendido</button>

            </div>
        </div>
    </div>
</div>
<div class="container-fluid ">
    <div class="my-5 text-center">
        <h4 class=" mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 700 !important ">RADICACIÓN CUENTAS DE COBRO</h4>
        <small>Módulo para radicación de cuentas de cobro para el pago de tus honorarios como OPS</small>
    </div>

    <div class="row px-2">
        <div class="col-12 ">
            <form id="miFormulario" action="./vistas/cuentas/radicacion/acciones/formulario_subir_archivos.php" method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class=" card shadow  col-12 mt-4 px-2 mb-4 border rounded-4" style=" position:relative;  overflow:visible; border: 1px solid #002F55 !important ">
                        <div class=" p-2 w-50 text-center text-white rounded-4" style="background-color: #002f55; position:absolute; top:-30px; left:1%">
                            <h6 class="fw-bold mb-0 ">Información personal</h6>
                            <small>Aquí se muestra tu información personal</small>
                        </div>
                        <div class="row p-4  pt-4">
                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3 ">
                                <label for="    " class="form-label" style="font-size:0.9em;"><b>Tipo de documento</b></label>
                                <div class="input-group shadow-sm rounded-2">
                                    <label class="input-group-text" for="Documento">
                                        <i class="bi-person-badge"></i>
                                    </label>
                                    <select class="form-select" style="font-size:0.9em;" id="Documento" name="Documento" required>
                                        <option value="" disabled selected>Selecciona el tipo de documento</option>
                                        <option value="Cedula_Ciudadania">Cédula de ciudadanía</option>
                                        <option value="Cedula_Extranjeria">Cédula de extranjería</option>
                                        <option value="NIT">N.I.T</option>
                                        <option value="Pasaporte">Pasaporte</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label for="numero_identidad" class="form-label fw-bold" style="font-size:0.9em;">Número Documento</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($usuario['cedula_usuario'])) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                                    <input type="number" class="form-control " style="font-size:0.9em;" id="numero_identidad" name="numero_identidad" placeholder="Ingrese el número de documento..."
                                        aria-label="PrimerNombre" aria-describedby="basic-addon1" value="<?php echo $usuario['cedula_usuario']; ?>" readonly>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label for="primer_nombre" class="form-label fw-bold" style="font-size:0.9em;">Primer nombre </label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($primer_nombre)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                    <input type="text" class="form-control" style="font-size:0.9em;" id="primer_nombre" name="primer_nombre"
                                        placeholder="Ingrese primer nombre..." name="primer_nombre" aria-label="PrimerNombre" value="<?= $primer_nombre ?>" readonly>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label for="segundo_nombre" class="form-label fw-bold" style="font-size:0.9em;">Segundo nombre</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($segundo_nombre)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" style="font-size:0.9em;" id="segundo_nombre" name="segundo_nombre"
                                        placeholder="Ingrese segundo nombre..." name="cert_primer_nombre" aria-label="PrimerNombre" value="<?= $segundo_nombre ?>" readonly>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label for="primer_apellido" class="form-label fw-bold" style="font-size:0.9em;">Primer Apellido </label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($primer_apellido)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi-people-fill"></i></span>
                                    <input type="text" class="form-control" style="font-size:0.9em;" id="primer_apellido" placeholder="Ingrese primer apellido..."
                                        name="primer_apellido" aria-label="PrimerApellido" aria-describedby="basic-addon1" value="<?= $primer_apellido ?>" readonly>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label for="segundo_apellido" class="form-label fw-bold" style="font-size:0.9em;">Segundo Apellido </label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($segundo_apellido)) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi-people"></i></span>
                                    <input type="text" class="form-control" style="font-size:0.9em;" id="segundo_apellido" placeholder="Ingrese segundo apellido..."
                                        name="segundo_apellido" aria-label="SegundoApellido" value="<?= $segundo_apellido ?>" readonly>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label for="telefono" class="form-label fw-bold" style="font-size:0.9em;">Número telefónico</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($usuario['celular_usuario'])) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-telephone-fill me-1"></i> +57</span>
                                    <input type="text" style="font-size:0.9em;" class="form-control" id="telefono" placeholder="Número telefónico..."
                                        name="telefono" aria-label="PrimerNombre" value="<?php echo $usuario['celular_usuario']; ?>">
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-8 p-1 px-2 my-3">
                                <label for="correo" class="form-label fw-bold" style="font-size:0.9em;">Correo electrónico</label>
                                <div class="input-group shadow-sm rounded-2 <?php echo (!empty($usuario['correo_usuario'])) ? 'precargado' : ''; ?>">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at-fill"></i></span>
                                    <input type="text" style="font-size:0.9em;" class="form-control " id="correo" placeholder="Correo electrónico..."
                                        name="correo" aria-label="PrimerNombre" value="<?php echo $usuario['correo_usuario']; ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow col-12 mt-5 px-2 mb-4 border rounded-4" style=" position:relative;  overflow:visible; border: 1px solid #05518F !important">
                        <div class=" p-2 w-50 text-center text-white rounded-4" style="background-color: #05518F; position:absolute; top:-30px; left:49%">
                            <h6 class="fw-bold mb-0 ">Información del cobro</h6>
                            <small>ingresa los datos de tu cobro</small>
                        </div>
                        <div class="row p-4 pt-4">
                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label for="Periodo_Facturacion" class="form-label" style="font-size:0.9em;"><b>Seleccione el Periodo</b></label>
                                <div class="input-group shadow-sm rounded-2">
                                    <label class="input-group-text" for="Periodo_Facturacion">
                                        <i class="bi bi-calendar-week"></i>
                                    </label>
                                    <select class="form-select" id="Periodo_Facturacion" style="font-size:0.9em;" name="Periodo_Facturacion" required>
                                        <option value="" disabled selected><b>Selecciona el periodo</b></option>
                                        <option value="Enero">Enero</option>
                                        <option value="Febrero">Febrero</option>
                                        <option value="Marzo">Marzo</option>
                                        <option value="Abril">Abril</option>
                                        <option value="Mayo">Mayo</option>
                                        <option value="Junio">Junio</option>
                                        <option value="Julio">Julio</option>
                                        <option value="Agosto">Agosto</option>
                                        <option value="Septiembre">Septiembre</option>
                                        <option value="Octubre">Octubre</option>
                                        <option value="Noviembre">Noviembre</option>
                                        <option value="Diciembre">Diciembre</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label for="anio_cuenta" class="form-label" style="font-size:0.9em;">
                                    <b>Año</b>
                                </label>
                                <div class="input-group shadow-sm rounded-2">
                                    <span class="input-group-text">
                                        <i class="bi bi-calendar-event"></i>
                                    </span>
                                    <select class="form-select" id="anio_cuenta" name="anio_cuenta" required>
                                        <option value="" selected disabled>Selecciona el año</option>
                                        <option value="2026">2026</option>
                                        <option value="2025">2025</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label for="valor" class="form-label fw-bold" style="font-size:0.9em;">Ingresa el valor a cobrar</label>
                                <div class="input-group shadow-sm rounded-2">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-currency-dollar"></i></span>
                                    <input type="text" style="font-size:0.9em;" class="form-control" id="valor" placeholder="Ingresa valor ..."
                                        name="valor" aria-label="PrimerNombre">
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label for="Val_Seg_Social" class="form-label fw-bold" style="font-size:0.9em;">Valor del IBC</label>
                                <div class="input-group shadow-sm shadow-warning rounded-2 border border-warning">
                                    <span class="input-group-text bg-warning" id="basic-addon1"><i class="bi bi-currency-dollar"></i></span>
                                    <input type="text" style="font-size:0.9em;" class="form-control " id="Val_Seg_Social" placeholder="Valor IBC ..."
                                        name="Val_Seg_Social" aria-label="PrimerNombre" readonly>
                                </div>
                                <small id="msgIBC" class="p-1 fw-bold mt-1 bg-warning rounded-2 text-center tilt" style="color: #002F55;">⚠ Este valor es un aproximado </small>
                            </div>

                            <script>
                                const input = document.getElementById('valor');
                                input.addEventListener('input', function(e) {
                                    let value = e.target.value.replace(/\D/g, "");
                                    e.target.value = new Intl.NumberFormat('es-CO').format(value);
                                });
                                document.querySelector("form").addEventListener("submit", function() {
                                    input.value = input.value.replace(/\D/g, "");
                                    const inputIBC = document.getElementById("Val_Seg_Social");
                                    if (inputIBC) {
                                        inputIBC.value = inputIBC.value.replace(/\D/g, "");
                                    }
                                });
                            </script>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label for="Fecha_Inicio" class="form-label fw-bold" style="font-size:0.9em;">Periodo fecha de inicio</label>
                                <div class="input-group shadow-sm rounded-2">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-event"></i></span>
                                    <input type="date" style="font-size:0.9em;" class="form-control" id="Fecha_Inicio" placeholder="Ingresa Fecha_Inicio ..."
                                        name="Fecha_Inicio" aria-label="PrimerNombre">
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label for="Fecha_Final" class="form-label fw-bold" style="font-size:0.9em;">Periodo fecha final</label>
                                <div class="input-group shadow-sm rounded-2">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-event-fill"></i></span>
                                    <input type="date" style="font-size:0.9em;" class="form-control" id="Fecha_Final" placeholder="Ingresa Fecha_Final ..."
                                        name="Fecha_Final" aria-label="PrimerNombre">
                                </div>
                            </div>

                            <script>
                                document.getElementById("valor").addEventListener("input", function() {
                                    let valorLimpio = this.value.replace(/\D/g, "");
                                    let valorCobrar = parseFloat(valorLimpio) || 0;
                                        this.value = valorCobrar ? new Intl.NumberFormat("es-CO").format(valorCobrar) : "";
                                    let ibc = 0;
                                    const umbralIBC = 4377262.5;
                                    const ibcFijo = 1750905;
                                    if (valorCobrar > 0) {
                                        if (valorCobrar > umbralIBC) {
                                            ibc = valorCobrar * 0.40;
                                        } else {
                                            ibc = ibcFijo;
                                        }
                                    }
                                    const inputIBC = document.getElementById("Val_Seg_Social");
                                    inputIBC.value = ibc ? new Intl.NumberFormat("es-CO").format(Math.round(ibc)) : "";
                                });
                            </script>
                            <style>
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
                            <script>
                                const target = document.getElementById("Val_Seg_Social");
                                const message = document.getElementById("msgIBC");
                                const observer = new IntersectionObserver(entries => {
                                    entries.forEach(entry => {
                                        if (entry.isIntersecting) {
                                            message.style.display = 'block';
                                            setTimeout(() => message.style.display = 'none', 900000);
                                        }
                                    });
                                });
                                observer.observe(target);
                            </script>
                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label for="cant_dias" class="form-label fw-bold" style="font-size:0.9em;">Cantidad de días</label>
                                <div class="input-group shadow-sm rounded-2">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-week"></i></span>
                                    <input type="number" style="font-size:0.9em;" class="form-control" id="cant_dias" placeholder="Ingresa cant_dias ..."
                                        name="cant_dias" aria-label="cantidaddedias" readonly>
                                </div>
                            </div>
                            <script>
                                document.getElementById("Fecha_Inicio").addEventListener("change", calcularDias);
                                document.getElementById("Fecha_Final").addEventListener("change", calcularDias);

                                function calcularDias() {
                                    let fechaInicio = document.getElementById("Fecha_Inicio").value;
                                    let fechaFinal = document.getElementById("Fecha_Final").value;
                                    if (fechaInicio && fechaFinal) {
                                        let inicio = new Date(fechaInicio);
                                        let final = new Date(fechaFinal);
                                        let diferencia = (final - inicio) / (1000 * 60 * 60 * 24) + 1;
                                        if (diferencia >= 0) {
                                            document.getElementById("cant_dias").value = diferencia;
                                        } else {
                                            document.getElementById("cant_dias").value = "Fecha inválida";
                                        }
                                    }
                                }
                            </script>
                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label for="cargo" class="form-label" style="font-size:0.9em;"><b>Selecciona tu rol</b></label>
                                <div class="input-group shadow-sm rounded-2">
                                    <label class="input-group-text" for="cargo">
                                        <i class="bi bi-file-person"></i>
                                    </label>
                                    <select class="form-select" id="cargo" style="font-size:0.9em;" name="cargo" required>
                                        <option value="" disabled selected><b>Selecciona tu rol</b></option>
                                        <option value="Profesional_Juridico">PROFESIONAL JURIDICO</option>
                                        <option value="Asignador">ASIGNADOR</option>
                                        <option value="Auxiliar_administrativo">AUXILIAR_ADMINISTRATIVO</option>
                                        <option value="Auxiliar_administrativo_ventanilla_unica">AUXILIAR ADMINISTRATIVO VENTANILLA ÚNICA</option>
                                        <option value="Auxiliar_administrativo_y_de_operaciones">AUXILIAR ADMINISTRATIVO Y DE OPERACIONES</option>
                                        <option value="Auxiliar_de_servicios_generales">AUXILIAR DE SERVICIOS GENERALES</option>
                                        <option value="Auxiliar_social">AUXILIAR SOCIAL</option>
                                        <option value="Auxiliar_ventanilla">AUXILIAR VENTANILLA</option>
                                        <option value="Consolidador">CONSOLIDADOR</option>
                                        <option value="Consulta_vur">CONSULTA VUR</option>
                                        <option value="Control_de_calidad">CONTROL DE CALIDAD</option>
                                        <option value="Coordinador_cuadrilla">COORDINADOR CUADRILLA</option>
                                        <option value="Desarrollador">DESARROLLADOR</option>
                                        <option value="Digitador">DIGITADOR</option>
                                        <option value="Digitalizador">DIGITALIZADOR</option>
                                        <option value="Director_de_operaciones">DIRECTOR DE OPERACIONES</option>
                                        <option value="Director_financiero">DIRECTOR FINANCIERO</option>
                                        <option value="Director_planeacion">DIRECTOR PLANEACIÓN</option>
                                        <option value="Directora_comercial">DIRECTORA COMERCIAL</option>
                                        <option value="Editor">EDITOR</option>
                                        <option value="Gerente">GERENTE</option>
                                        <option value="Gestora_de_talento_humano">GESTORA DE TALENTO HUMANO</option>
                                        <option value="Hseq">HSEQ</option>
                                        <option value="Jefe_de_operaciones">JEFE DE OPERACIONES</option>
                                        <option value="Lider_consolidacion">LÍDER CONSOLIDACIÓN</option>
                                        <option value="Lider_reconocimiento">LÍDER RECONOCIMIENTO</option>
                                        <option value="Lider_tecnico">LÍDER TÉCNICO</option>
                                        <option value="Mergin_maps">MERGIN MAPS</option>
                                        <option value="Presupuesto">PRESUPUESTO</option>
                                        <option value="Profesional_2_con_posgrado">PROFESIONAL 2 CON POSGRADO</option>
                                        <option value="Profesional_3_con_posgrado">PROFESIONAL 3 CON POSGRADO</option>
                                        <option value="Profesional_hseq_con_posgrado">PROFESIONAL HSEQ CON POSGRADO</option>
                                        <option value="Profesional_catastral_1">PROFESIONAL CATASTRAL 1</option>
                                        <option value="Programadora_de_software">PROGRAMADORA DE SOFTWARE</option>
                                        <option value="Profesional_sig">PROFESIONAL SIG</option>
                                        <option value="Profesional_social">PROFESIONAL_SOCIAL</option>
                                        <option value="Reconocedor_predial">RECONOCEDOR PREDIAL</option>
                                        <option value="Soporte_nivel_1">SOPORTE NIVEL 1</option>
                                        <option value="Soporte_nivel_2">SOPORTE NIVEL 2</option>
                                        <option value="Tecnologo">TECNOLOGO</option>
                                        <option value="Tecnologo_grado_2">TECNOLOGO GRADO 2</option>
                                        <option value="Reconocedor_predial_junior">RECONOCEDOR PREDIAL JUNIOR</option>
                                        <option value="Coordinador_sig">COORDINADOR SIG</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                                <label for="proyecto" class="form-label" style="font-size:0.9em;"><b>Selecciona el proyecto</b></label>
                                <div class="input-group shadow-sm rounded-2">
                                    <label class="input-group-text" for="proyecto">
                                        <i class="bi bi-file-earmark-text-fill"></i>
                                    </label>
                                    <select class="form-select" id="proyecto" style="font-size:0.9em;" name="proyecto" required>
                                        <option value="" disabled selected><b>Selecciona el proyecto</b></option>
                                        <option value="AFINIA-GESTION_PREDIAL">AFINIIA - GESTIÓN PREDIAL</option>
                                        <option value="ANT_AVALUOS">ANT_AVALÚOS</option>
                                        <option value="ADMINISTRATIVO">ADMINISTRATIVO</option>
                                        <option value="ARBIMAPS">ARBIMAPS</option>
                                        <option value="ARBIPRO">ARBIPRO</option>
                                        <option value="ASESOR_JURIDICO_LITIGIO">ASESOR_JURIDICO_LITIGIO</option>
                                        <option value="ARBOLETES">VALOR + ARBOLETES</option>
                                        <option value="CALIDAD_EXTERNA_VALOR+">CALIDAD EXTERNA - VALOR +</option>
                                        <option value="CONTABILIDAD">CONTABILIDAD</option>
                                        <option value="CONSERVACION_CATASTRAL_VALOR+">CONSERVACION CATASTRAL VALOR +</option>
                                        <option value="GESTION_SOCIAL">GESTION SOCIAL</option>
                                        <option value="GESTION_JURIDICA">GESTION JURIDICA</option>
                                        <option value="GESTION_JURIDICA_LITIGIO">GESTION JURIDICA - LITIGIO</option>
                                        <option value="FTP - IGAC">FTP - IGAC</option>
                                        <option value="FTP - VALOR+">FTP - VALOR+</option>
                                        <option value="NECOCLÍ">VALOR + NECOCLÍ</option>
                                        <option value="SAN_JUAN">VALOR + SAN JUAN DE URABA</option>
                                        <option value="SAN_PEDRO">VALOR + SAN PEDRO DE URABA</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow col-12 mt-5 px-2 mb-4 border rounded-4" style=" position:relative;  overflow:visible; border: 1px solid #2887D5 !important">
                        <div class=" p-2 w-50 text-center text-white rounded-4" style="background-color: #2887D5; position:absolute; top:-30px; left:1%">
                            <h6 class="fw-bold mb-0 ">Documentos obligatorios</h6>
                            <small>Adjunta todos los archivos solicitados</small>
                        </div>
                        <div class="row p-4  pt-4">
                            <div class=" col-12 col-lg-4 p-1 px-2 my-4">
                                <label for="informe_mensual" class="form-label fw-bold">Informe Mensual</label>
                                <div class="input-group  shadow-sm rounded-2">
                                    <label class="input-group-text" for="informe_mensual" style="font-size:0.9em;"><i class="bi bi-file-earmark-pdf-fill"></i></label>
                                    <input type="file" class="form-control" style="font-size:0.8em;" id="informe_mensual" name="informe_mensual">
                                </div>
                                <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                            </div>
                            <div class=" col-12 col-lg-4 p-1 px-2 my-4">
                                <label for="cuenta_cobro" class="form-label fw-bold">Cuenta de Cobro</label>
                                <div class="input-group mb-1 shadow-sm rounded-2">
                                    <label class="input-group-text" for="cuenta_cobro" style="font-size:0.9em;"><i class="bi bi-file-earmark-pdf-fill"></i></label>
                                    <input type="file" class="form-control" style="font-size:0.8em;" id="cuenta_cobro" name="cuenta_cobro">
                                </div>
                                <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                            </div>
                            <div class=" col-12 col-lg-4 p-1 px-2 my-4">
                                <label for="seguridad_social" class="form-label fw-bold">Seguridad Social</label>
                                <div class="input-group mb-1 shadow-sm rounded-2">
                                    <label class="input-group-text" for="seguridad_social" style="font-size:0.9em;"><i class="bi bi-file-earmark-pdf-fill"></i></label>
                                    <input type="file" class="form-control" style="font-size:0.8em;" id="seguridad_social" name="seguridad_social">
                                </div>
                                <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                            </div>
                            <div class=" col-12 col-lg-6 p-1 px-2 my-3">
                                <label for="retencion" class="form-label fw-bold">Retención de la Fuente</label>
                                <div class="input-group mb-1 shadow-sm rounded-2">
                                    <label class="input-group-text" for="retencion" style="font-size:0.9em;"><i class="bi bi-file-earmark-pdf-fill"></i></label>
                                    <input type="file" class="form-control" style="font-size:0.8em;" id="retencion" name="retencion">
                                </div>
                                <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                            </div>
                            <div class=" col-12 col-lg-6 p-1 px-2 my-3">
                                <label for="primera_vez" class="form-label fw-bold">Archivos CC - C. Bancario - RUT</label>
                                <div class="input-group mb-1 shadow-sm rounded-2">
                                    <label class="input-group-text" for="primera_vez" style="font-size:0.9em;"><i class="bi bi-file-earmark-pdf-fill"></i></label>
                                    <input type="file" class="form-control" style="font-size:0.8em;" id="primera_vez" name="primera_vez">
                                </div>
                                <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-row px-2">
                    <div class="col-md-12  px-4 d-flex flex-column  justify-content-center">
                        <label for="observacion" style="font-size:1.2em;" class="my-2"><b>Observaciones</b></label>
                        <textarea class="form-control py-4 rounded-4 shadow" style="font-size:0.8em;  resize: none;" id="observacion" name="observacion" placeholder="Escribe aquí tu observación respecto a la radicación de tu cuenta de cobro"></textarea>
                    </div>
                </div>
                <div class="form-group text-center my-3 ">
                    <button type="submit" class="btn btn-block text-white" style="background-color: #002F55;">
                        <b><i class="bi bi-clipboard2-plus me-2"></i> RADICAR CUENTA </b>
                    </button>
                </div>
            </form>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const form = document.getElementById('miFormulario');
                    form.addEventListener('submit', function(event) {
                        event.preventDefault();
                        Swal.fire({
                            title: `
                            <div class="custom-loader"></div>
                                <div style="font-weight: 700; font-size: 1.5rem; color: #002F55;">
                                    Radicando su cuenta...
                                </div>
                            `,
                            html: `
                                <div style="margin-bottom: 15px; color:#FF3B3B; font-weight: bold; font-size: 0.9rem;">
                                    ¡POR FAVOR, NO ABANDONE ESTA PÁGINA! </br> ESPERE...
                                </div>
                                <div class="my-3" style=" font-size: 0.85rem; opacity: .8;">
                                    La página se actualizará cuando los datos hayan sido cargados correctamente.
                                </div>
                                <div style="margin-bottom: 10px; font-size: 0.95rem;">
                                    Recuerda verificar en:<br>
                                    <strong style="color:#0F5699">"Mis Cuentas Radicadas"</strong>
                                </div>
                            `,
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#002f55',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            background: "#ffffff",
                            backdrop: `
                                rgba(0, 47, 85, 0.35)
                                backdrop-filter: blur(3px)
                            `,
                            customClass: {
                                popup: 'arbimaps-popup'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const formData = new FormData(form);
                                fetch(form.action, {
                                        method: 'POST',
                                        body: formData
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            var redirectUrl = data.redirect ? data.redirect : '/arbimaps/Arbimaps/index.php?page=cuentas/radicar_cuenta';
                                            window.location.href = redirectUrl;
                                        } else {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Error',
                                                text: data.message
                                            });
                                        }
                                    })
                                    .catch(error => {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: 'Hubo un problema al enviar la cuenta. Inténtalo de nuevo.'
                                        });
                                    });
                            }
                        });
                    });
                });
                document.addEventListener("DOMContentLoaded", function() {
                    function convertirMayusculas(input) {
                        input.value = input.value.toUpperCase();
                    }
                    document.querySelectorAll("input[type='text']").forEach(function(input) {
                        input.addEventListener("input", function() {
                            convertirMayusculas(this);
                        });
                    });
                });
            </script>
        </div>
    </div>
</div>
<script src="../../../js/scripts.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable();
    });
</script>