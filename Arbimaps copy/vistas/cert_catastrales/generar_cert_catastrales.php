<style>
    .autocomplete-items div {
        padding: 8px;
        cursor: pointer;
        border-bottom: 1px solid #ddd;
    }

    .autocomplete-items div:hover {
        background-color: rgb(25, 73, 136);
    }

    .zindex-dropdown {
        z-index: 9999;
    }
</style>

<script>
    let predios = [];

    // Cargar datos del JSON
    fetch('../dat_neiva/predios.json')
        .then(res => res.json())
        .then(data => {
            predios = data;
        });

    document.addEventListener('DOMContentLoaded', function() {
        configurarBusqueda('cert_npn_predio', 'numero_predio');
        configurarBusqueda('cert_fmi_predio', 'matricula_inmobiliaria');
        configurarBusqueda('cc_propietario', 'numero_doc_propietario');
    });

    function configurarBusqueda(inputId, campo) {
        const input = document.getElementById(inputId);
        if (!input) return;

        let suggestionsBox = document.createElement('div');
        suggestionsBox.className = 'autocomplete-items bg-white border position-absolute w-100 zindex-dropdown';
        suggestionsBox.style.maxHeight = '200px';
        suggestionsBox.style.overflowY = 'auto';
        input.parentNode.appendChild(suggestionsBox);

        input.addEventListener('input', function() {
            let valor = this.value.trim();
            suggestionsBox.innerHTML = '';

            if (valor.length < 3) return;

            // Normalizar (solo para cédulas)
            const valorNormalizado = campo === 'numero_doc_propietario' ?
                valor.replace(/^0+/, '') :
                valor;

            const coincidencias = predios
                .filter(p => {
                    let campoValor = String(p[campo] || '');
                    if (campo === 'numero_doc_propietario') {
                        campoValor = campoValor.replace(/^0+/, '');
                    }
                    return campoValor.startsWith(valorNormalizado);
                })
                .slice(0, 10);

            if (coincidencias.length === 0) return;

            coincidencias.forEach(p => {
                const item = document.createElement('div');
                item.textContent = p[campo];

                item.addEventListener('click', () => {
                    input.value = p[campo];
                    suggestionsBox.innerHTML = '';

                    // Llenar los otros campos automáticamente
                    document.getElementById('cert_fmi_predio').value = p.matricula_inmobiliaria || '';
                    document.getElementById('cert_npn_predio').value = p.numero_predio || '';
                    document.getElementById('direccion_predio').value = p.direccion_predio || '';
                    document.getElementById('dest_econ_predio').value = p.dest_econ_predio || '';
                    document.getElementById('area_terreno_predio').value = p.area_terreno_predio || '';
                    document.getElementById('area_construccion_predio').value = p.area_construccion_predio || '';
                    document.getElementById('avaluo_terreno_tramite').value = p.avaluo_terreno_tramite || '';
                    document.getElementById('anio_vigencia').value = p.anio_vigencia || '';

                    // --- Mostrar propietarios relacionados ---
                    const cont = document.getElementById('propietariosContainer');
                    cont.innerHTML = '';

                    // Buscar todos los registros del mismo predio (por matrícula)
                    const relacionados = predios.filter(pr => pr.matricula_inmobiliaria === p.matricula_inmobiliaria);

                    if (relacionados.length > 0) {
                        let tabla = `<table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Nombre Propietario</th>
                                            <th>Tipo Documento</th>
                                            <th>Número Documento</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;

                        relacionados.forEach((prop, index) => {
                            tabla += `
                            <tr>
                                <td><input type="text" class="form-control" 
                                    name="propietarios[${index}][nombre]" 
                                    value="${prop.nombre_propietario_tramite}" readonly></td>
                                <td><input type="text" class="form-control" 
                                    name="propietarios[${index}][tipo_doc]" 
                                    value="${prop.tipo_doc_propietario}" readonly></td>
                                <td><input type="text" class="form-control" 
                                    name="propietarios[${index}][numero_doc]" 
                                    value="${prop.numero_doc_propietario.replace(/^0+/, '')}" readonly></td>
                            </tr>`;
                        });

                        tabla += `</tbody></table>`;
                        cont.innerHTML = tabla;
                    } else {
                        cont.innerHTML = '<p class="text-danger">No se encontraron propietarios para este predio.</p>';
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

            fetch("../tramites/cargue_tramite_rad.php", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = "index.php?page=cert_catastrales/consulta_cert_catastrales" + encodeURIComponent(data.codigo);
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

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-center mb-4">
        <h1 class="h3 text-gray-800 text-center mb-1"><b>Solicitud Certificados Catastrales</b></h1>
    </div>

    <!-- <div class="row">
         col-xl-12 col-lg-7 -> ESTO LO QUE HACE ES DEFINIR EL ESPACIO DE LA TARJETA --
        <div class="col-xl-12 col-lg-7">
            <div class="card shadow mb-4">
                -- Card Header - Dropdown --
                <div class="card-header py-3 text-center" style="background-color: #002F55;">
                    <h6 class="m-0 font-weight-bold text-primary text-white">DATOS DEL SOLICITANTE/INTERESADO</h6>
                </div>
                <div class="card-body">
                    <form id="miFormulario" action="insertar_sol_cert.php" method="POST" enctype="multipart/form-data">
                        --  CAMBIAR EL MODO DE ID, DESACTIVAR PORQUE DEBE SER AUTOMATICO --
                        <div class="form-row">
                            <div class="col-md-6">
                                <label for="cert_documento_interado"><b>Seleccione Tipo Documento Interesado</b></label>
                                <select class="custom-select" id="cert_documento_interado" name="cert_documento_interado" required>
                                    <option value="" disabled selected><b>SELECCIONE</b></option>
                                    <option value="Cedula_Ciudadania">Cédula de Ciudadanía</option>
                                    <option value="Cedula_Extranjeria">Cédula de Extranjería</option>
                                    <option value="NIT">N.I.T.</option>
                                    <option value="Pasaporte">Pasaporte</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="num_cc_interesado"><b>Número Documento de Identidad Interesado</b></label>
                                <input class="form-control py-4" id="num_cc_interesado" name="num_cc_interesado" type="number"
                                    placeholder="Ingrese Número de Identidad" required>
                            </div>
                        </div><br>

                        <div class="form-row">
                            <div class="col-md-6">
                                <label for="cert_primer_nombre"><b>Primer Nombre Interesado</b></label>
                                <input class="form-control py-4" id="cert_primer_nombre" name="cert_primer_nombre" type="text"
                                    placeholder="Ingrese Primer Nombre" required>
                            </div>
                            <div class="col-md-6">
                                <label for="cert_segundo_nombre"><b>Segundo Nombre Interesado</b></label>
                                <input class="form-control py-4" id="cert_segundo_nombre" name="cert_segundo_nombre" type="text"
                                    placeholder="Ingrese Segundo Nombre">
                            </div>
                        </div><br>

                        <div class="form-row">
                            <div class="col-md-6">
                                <label for="cert_primer_apellido"><b>Primer Apellido Interesado</b></label>
                                <input class="form-control py-4" id="cert_primer_apellido" name="cert_primer_apellido" type="text"
                                    placeholder="Ingrese Primer Apellido" required>
                            </div>
                            <div class="col-md-6">
                                <label for="cert_segundo_apellido"><b>Segundo Apellido Interesado</b></label>
                                <input class="form-control py-4" id="cert_segundo_apellido" name="cert_segundo_apellido" type="text"
                                    placeholder="Ingrese Segundo Apellido">
                            </div>
                        </div><br>

                        <div class="form-row">
                            <div class="col-md-6">
                                <label for="cert_telefono_interesado"><b>Número Telefónico Interesado</b></label>
                                <input class="form-control py-4" id="cert_telefono_interesado" name="cert_telefono_interesado" type="text"
                                    placeholder="Ingrese Número Telefónico" required>
                            </div>
                            <div class="col-md-6">
                                <label for="cert_correo_interesado"><b>Correo Electrónico Interesado</b></label>
                                <input class="form-control py-4" id="cert_correo_interesado" name="cert_correo_interesado" type="email"
                                    placeholder="Ingrese Correo Electrónico" required>
                            </div>
                        </div><br>

                        <div class="form-row">
                            <div class="col-md-6">
                                <label for="cert_sopor_pago"><b>Soporte de Pago</b></label>
                                <input class="custom-file" id="cert_sopor_pago" name="cert_sopor_pago" type="file">
                            </div>
                            <div class="col-md-6">
                                <label for="cert_medio_envio"><b>Medios de Envio/Entrega</b></label><br>
                                <input type="checkbox" name="cert_medio_envio[]" value="Fisico"><b> Fisico</b></option>
                                <input type="checkbox" name="cert_medio_envio[]" value="Correo"><b> Correo</b></option>
                                <input type="checkbox" name="cert_medio_envio[]" value="Mensajeria"><b> Mensajeria</b></option>
                                <input type="checkbox" name="cert_medio_envio[]" value="Plataforma"><b> Cargue Plataforma</b></option>
                            </div>
                        </div><br>

                        <hr>
                        <h5 class="text-primary text-center mb-3"><b>INFORMACIÓN DEL PREDIO</b></h4><br>

                            <div class="form-row">
                                <div class="col-md-6">
                                    <label for="cert_fmi_predio"><b>Busqueda por FMI Predio</b></label>
                                    <input class="form-control py-4" id="cert_fmi_predio" name="cert_fmi_predio" type="text"
                                        placeholder="Ingrese FMI del Predio">
                                </div>
                                <div class="col-md-6 position-relative">
                                    <label for="cert_npn_predio"><b>Busqueda por Cod. Catastral Predio</b></label>
                                    <input class="form-control py-4" id="cert_npn_predio" name="cert_npn_predio" type="text"
                                        placeholder="Ingrese N° Predial de predio" required autocomplete="off">
                                    <div id="suggestions" class="autocomplete-items bg-white border position-absolute w-100 zindex-dropdown"
                                        style="max-height: 200px; overflow-y: auto;"></div>
                                </div>
                            </div><br>

                            <div class="form-row">
                                <div class="col-md-12">
                                    <label><b>Propietarios del Predio</b></label>
                                    <div id="propietariosContainer"></div>
                                </div>
                            </div><br>

                            <div class="form-row">
                                <div class="col-md-6">
                                    <label for="anio_vigencia"><b>Año Vigencia de Avaluo</b></label>
                                    <input class="form-control py-4" id="anio_vigencia" name="anio_vigencia" type="text" readonly>

                                </div>
                                <div class="col-md-6">
                                    <label for="avaluo_terreno_tramite"><b>Valor Avaluo de Terreno</b></label>
                                    <input class="form-control py-4" id="avaluo_terreno_tramite" name="avaluo_terreno_tramite" type="text"
                                        readonly>
                                </div>
                            </div><br>

                            <div class="form-row">
                                <div class="col-md-6">
                                    <label for="direccion_predio"><b>Dirección Predio</b></label>
                                    <input class="form-control py-4" id="direccion_predio" name="direccion_predio" type="text"
                                        readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="dest_econ_predio"><b>Destino Economico Predio</b></label>
                                    <input class="form-control py-4" id="dest_econ_predio" name="dest_econ_predio" type="text"
                                        readonly>
                                </div>
                            </div><br>

                            <div class="form-row">
                                <div class="col-md-6">
                                    <label for="area_terreno_predio"><b>Area Terreno Predio</b></label>
                                    <input class="form-control py-4" id="area_terreno_predio" name="area_terreno_predio" type="number"
                                        readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="area_construccion_predio"><b>Area Construccion Predio</b></label>
                                    <input class="form-control py-4" id="area_construccion_predio" name="area_construccion_predio" type="number"
                                        readonly>
                                </div>
                            </div><br>
                            <div class="form-group mt-4 mb-0">
                                <button type="submit" class="btn btn-success btn-block">
                                    <b><i class="fas fa-file-upload"></i> GENERAR CERTIFICADO </b>
                                </button>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </div> -->

    <form id="miFormulario" action="index.php?page=cert_catastrales/acciones/insertar_sol_cert" method="post" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-12  mb-2">
                <div class="card shadow h-100 mb-2 p-1">
                    <div class="card-header especial py-3 text-center" style="background-color: #002F55; border-radius:12px">
                        <h6 class="m-0 font-weight-bold text-primary text-white">DATOS DEL SOLICITANTE / INTERESADO</h6>
                    </div>
                    <div class="card-body px-4">
                        <div class="form-row">

                            <!-- <div class="col-md-5 p-1 my-2">
                                <label for="cert_documento_interado mb-1"><b>Seleccione Tipo Documento Interesado</b></label>
                                <select class="custom-select" id="cert_documento_interado" name="cert_documento_interado" required>
                                    <option value="" disabled selected><b>SELECCIONE</b></option>
                                    <option value="Cedula_Ciudadania">Cédula de Ciudadanía</option>
                                    <option value="Cedula_Extranjeria">Cédula de Extranjería</option>
                                    <option value="NIT">N.I.T.</option>
                                    <option value="Pasaporte">Pasaporte</option>
                                </select>
                            </div> -->


                            <div class="col-md-6 p-1 px-2 my-2 ">
                                <label for="cert_documento_interado" class="form-label"><b>Tipo de documento</b></label>
                                <div class="input-group shadow-sm">
                                    <label class="input-group-text" for="cert_documento_interado">
                                        <i class="bi-person-badge"></i>
                                    </label>
                                    <select class="form-select" id="cert_documento_interado" name="cert_documento_interado" required>
                                        <option value="" disabled selected>Selecciona el tipo de documento</option>
                                        <option value="Cedula_Ciudadania">Cédula de ciudadanía</option>
                                        <option value="Cedula_Extranjeria">Cédula de extranjería</option>
                                        <option value="NIT">N.I.T</option>
                                        <option value="Pasaporte">Pasaporte</option>
                                    </select>
                                </div>
                            </div>

                            <!-- <div class="col-md-7 p-1 my-2">
                                <label for="num_cc_interesado"><b>Número Documento de Identidad Interesado</b></label>
                                <input class="form-control py-2" id="num_cc_interesado" name="num_cc_interesado" type="number"
                                    placeholder="Ingrese Número de Identidad" required>
                            </div> -->

                            <div class="col-md-6 p-1 px-2 my-2">
                                <label for="num_cc_interesado" class="form-label fw-bold">Número Documento de Identidad Interesado</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                                    <input type="number" class="form-control" id="num_cc_interesado" name="num_cc_interesado" placeholder="Ingrese el número de documento..." name="cert_primer_nombre" aria-label="PrimerNombre" aria-describedby="basic-addon1">
                                </div>
                            </div>

                            <!-- <div class="col-md-6 p-1 my-2">
                                <label for="cert_primer_nombre"><b>Primer Nombre Interesado</b></label>
                                <input class="form-control py-2" id="cert_primer_nombre" name="cert_primer_nombre" type="text"
                                    placeholder="Ingrese Primer Nombre" required>
                            </div> -->

                            <div class="col-md-6 p-1 px-2 my-2">
                                <label for="cert_primer_nombre" class="form-label fw-bold">Primer nombre del interesado</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                    <input type="text" class="form-control" id="cert_primer_nombre" name="cert_primer_nombre"
                                        placeholder="Ingrese primer nombre..." name="cert_primer_nombre" aria-label="PrimerNombre"
                                        required>
                                </div>
                            </div>


                            <!-- <div class="col-md-6 p-1 my-2">
                                <label for="cert_segundo_nombre"><b>Segundo Nombre Interesado</b></label>
                                <input class="form-control py-2" id="cert_segundo_nombre" name="cert_segundo_nombre" type="text"
                                    placeholder="Ingrese Segundo Nombre">
                            </div> -->

                            <div class="col-md-6 p-1 px-2 my-2">
                                <label for="cert_segundo_nombre" class="form-label fw-bold">Segundo nombre del interesado</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="cert_segundo_nombre" name="cert_segundo_nombre"
                                        placeholder="Ingrese segundo nombre..." name="cert_primer_nombre" aria-label="PrimerNombre">
                                </div>
                            </div>

                            <!-- <div class="col-md-6 p-1 my-2">
                                <label for="cert_primer_apellido"><b>Primer Apellido Interesado</b></label>
                                <input class="form-control py-2" id="cert_primer_apellido" name="cert_primer_apellido" type="text"
                                    placeholder="Ingrese Primer Apellido" required>
                            </div> -->


                            <div class="col-md-6 p-1 px-2 my-2">
                                <label for="cert_primer_apellido" class="form-label fw-bold">Primer apellido del interesado</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi-people-fill"></i></span>
                                    <input type="text" class="form-control" id="cert_primer_apellido" placeholder="Ingrese primer apellido..."
                                        name="cert_primer_nombre" aria-label="PrimerApellido" aria-describedby="basic-addon1">
                                </div>
                            </div>

                            <!-- <div class="col-md-6 p-1 my-2">
                                <label for="cert_segundo_apellido"><b>Segundo Apellido Interesado</b></label>
                                <input class="form-control py-2" id="cert_segundo_apellido" name="cert_segundo_apellido" type="text"
                                    placeholder="Ingrese Segundo Apellido">
                            </div> -->

                            <div class="col-md-6 p-1 px-2 my-2">
                                <label for="cert_segundo_apellido" class="form-label fw-bold">Segundo apellido del interesado</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi-people"></i></span>
                                    <input type="text" class="form-control" id="cert_segundo_apellido" placeholder="Ingrese segundo apellido..."
                                        name="cert_segundo_apellido" aria-label="SegundoApellido">
                                </div>
                            </div>

                            <!-- <div class="col-md-6 p-1 my-2">
                                <label for="cert_telefono_interesado"><b>Número Telefónico Interesado</b></label>
                                <input class="form-control py-2" id="cert_telefono_interesado" name="cert_telefono_interesado" type="text"
                                    placeholder="Ingrese Número Telefónico" required>
                            </div> -->

                            <div class="col-md-6 p-1 px-2 my-2">
                                <label for="cert_telefono_interesado" class="form-label fw-bold">Número telefónico del interesado</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-telephone-fill me-1"></i> +57</span>
                                    <input type="text" class="form-control" id="cert_telefono_interesado" placeholder="Número telefónico..."
                                        name="cert_telefono_interesado" aria-label="PrimerNombre">
                                </div>
                            </div>

                            <!-- <div class="col-md-6 p-1 my-2">
                                <label for="cert_correo_interesado"><b>Correo Electrónico Interesado</b></label>
                                <input class="form-control py-2" id="cert_correo_interesado" name="cert_correo_interesado" type="email"
                                    placeholder="Ingrese Correo Electrónico" required>
                            </div> -->

                            <div class="col-md-6 p-1 px-2 my-2">
                                <label for="cert_correo_interesado" class="form-label fw-bold">Correo electrónico del interesado</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at-fill"></i></span>
                                    <input type="text" class="form-control" id="cert_correo_interesado" placeholder="Correo electrónico..."
                                        name="cert_correo_interesado" aria-label="PrimerNombre">
                                </div>
                            </div>

                            <!-- <div class="col-md-12 p-1 my-2">
                                <label for="cert_sopor_pago"><b>Soporte de Pago</b></label>
                                <input class="custom-file form-control" style="font-size:0.8em;" id="cert_sopor_pago" name="cert_sopor_pago" type="file">
                            </div> -->

                            <div class="col-md-12 p-1 px-2 my-2">
                                <label for="cert_sopor_pago" class="form-label fw-bold">Soporte de pago</label>
                                <div class="input-group mb-1 shadow-sm">
                                    <label class="input-group-text" for="cert_sopor_pago"><i class="bi bi-cloud-upload-fill"></i></label>
                                    <input type="file" class="form-control" id="cert_sopor_pago" name="cert_sopor_pago">
                                </div>
                                <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                            </div>

                            <div class="col-md-12 p-1 px-2 my-2">
                                <label for="cert_medio_envio" class="mb-1"><b>Medios de Envío/Entrega</b></label><br>

                                <input type="checkbox" name="cert_medio_envio[]" value="Fisico" id="envio_fisico">
                                <label for="envio_fisico">Físico</label><br>

                                <input type="checkbox" name="cert_medio_envio[]" value="Correo" id="envio_correo">
                                <label for="envio_correo">Correo</label><br>

                                <input type="checkbox" name="cert_medio_envio[]" value="Mensajeria" id="envio_mensajeria">
                                <label for="envio_mensajeria">Mensajería</label><br>

                                <input type="checkbox" name="cert_medio_envio[]" value="Plataforma" id="envio_plataforma">
                                <label for="envio_plataforma">Cargue Plataforma</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card shadow h-100 mb-2 p-1 ">
                    <div class="card-header especial py-3 text-center" style="background-color: #002F55;border-radius:12px">
                        <h6 class="m-0 font-weight-bold text-primary text-white">INFORMACIÓN DEL PREDIO</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-row">

                            <!-- <div class="col-md-6 p-1">
                                <label for="cert_fmi_predio"><b>Busqueda por FMI Predio</b></label>
                                <input class="form-control py-2" id="cert_fmi_predio" name="cert_fmi_predio" type="text"
                                    placeholder="Ingrese FMI del Predio">
                            </div> -->

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label for="cert_fmi_predio" class="form-label fw-bold">Búsqueda por FMI del predio</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" id="cert_fmi_predio" placeholder="Ingrese FMI del predio..."
                                        name="cert_fmi_predio" aria-label="BusquedaporFMI">
                                </div>
                            </div>

                            <!-- <div class="col-md-4">
                                <label for="cc_propietario"><b>Busqueda por Cedula Propietario</b></label>
                                <input class="form-control py-4" id="cc_propietario" name="cc_propietario" type="text"
                                    placeholder="Ingrese Cedula del Interesado/Propietario">
                            </div> -->

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label for="cc_propietario" class="form-label fw-bold">Búsqueda por cedula del propietario</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" id="cc_propietario" name="cc_propietario" placeholder="Ingrese cedula de interesado..."
                                        name="cc_propietario" aria-label="BusquedaporFMI">
                                </div>
                            </div>

                            <!-- <div class="col-md-6 position-relative">
                                <label for="cert_npn_predio"><b>Busqueda por Cod. Catastral Predio</b></label>
                                <input class="form-control py-2" id="cert_npn_predio" name="cert_npn_predio" type="text"
                                    placeholder="Ingrese N° Predial de predio" required autocomplete="off">
                                <div id="suggestions" class="autocomplete-items bg-white border position-absolute w-100 zindex-dropdown"
                                    style="max-height: 200px; overflow-y: auto;"></div>
                            </div> -->

                            <div class="col-md-4 p-1 px-2 my-2 position-relative">
                                <label for="cert_npn_predio" class="form-label fw-bold">Búsqueda por cod. catastral del predio</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi-map"></i></span>
                                    <input type="text" class="form-control" id="cert_npn_predio" placeholder="Ingrese número predial..."
                                        name="cert_npn_predio" aria-label="BusquedaporFMI">
                                </div>
                                <div id="suggestions" class="autocomplete-items bg-white border position-absolute w-100 zindex-dropdown"
                                    style="max-height: 210px; overflow-y: auto;"></div>
                                <div class="form-text">Ingresa el número catastral de 30 dígitos</div>
                            </div>

                            <div class=" col-md-12 p-1">
                                <label class="d-none"><b>Propietarios del Predio</b></label>
                                <div id="propietariosContainer"></div>
                            </div>

                            <!-- <div class="col-md-6 p-1">
                                <label for="anio_vigencia"><b>Año Vigencia de Avaluo</b></label>
                                <input class="form-control py-2" id="anio_vigencia" name="anio_vigencia" type="text" readonly>
                            </div> -->

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label for="anio_vigencia" class="form-label fw-bold">Año vigencia de avalúo</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-date"></i></span>
                                    <input type="text" class="form-control" id="anio_vigencia"
                                        name="anio_vigencia" aria-label="PrimerNombre" readonly>
                                </div>
                            </div>

                            <!-- <div class="col-md-6 p-1">
                                <label for="avaluo_terreno_tramite"><b>Valor Avaluo de Terreno</b></label>
                                <input class="form-control py-2" id="avaluo_terreno_tramite" name="avaluo_terreno_tramite" type="text"
                                    readonly>
                            </div> -->

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label for="avaluo_terreno_tramite" class="form-label fw-bold">Valor avalúo de terreno</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-piggy-bank"></i></span>
                                    <input type="text" class="form-control" id="avaluo_terreno_tramite"
                                        name="avaluo_terreno_tramite" aria-label="PrimerNombre" readonly>
                                </div>
                            </div>

                            <!-- <div class="col-md-4 p-1">
                                <label for="direccion_predio"><b>Dirección Predio</b></label>
                                <input class="form-control py-2" id="direccion_predio" name="direccion_predio" type="text"
                                    readonly>
                            </div> -->

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label for="direccion_predio" class="form-label fw-bold">Dirección del predio</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-geo-alt-fill"></i></span>
                                    <input type="text" class="form-control" id="direccion_predio"
                                        name="direccion_predio" aria-label="PrimerNombre" readonly>
                                </div>
                            </div>

                            <!-- <div class="col-md-6 p-1">
                                <label for="dest_econ_predio"><b>Destino Economico Predio</b></label>
                                <input class="form-control py-2" id="dest_econ_predio" name="dest_econ_predio" type="text"
                                    readonly>
                            </div> -->

                            <div class="col-md-5 p-1 px-2 my-2">
                                <label for="dest_econ_predio" class="form-label fw-bold">Destino Economico Predio</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi-briefcase"></i></span>
                                    <input type="text" class="form-control" id="dest_econ_predio"
                                        name="dest_econ_predio" aria-label="PrimerNombre" readonly>
                                </div>
                            </div>

                            <!-- <div class="col-md-6 p-1">
                                <label for="area_terreno_predio"><b>Area Terreno Predio</b></label>
                                <input class="form-control py-2" id="area_terreno_predio" name="area_terreno_predio" type="number"
                                    readonly>
                            </div> -->

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label for="area_terreno_predio" class="form-label fw-bold">Area terreno predio</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-globe-americas"></i></span>
                                    <input type="text" class="form-control" id="area_terreno_predio"
                                        name="area_terreno_predio" aria-label="PrimerNombre" readonly>
                                </div>
                            </div>

                            <!-- <div class="col-md-6 p-1">
                                <label for="area_construccion_predio"><b>Area Construccion Predio</b></label>
                                <input class="form-control py-2 " id="area_construccion_predio" name="area_construccion_predio" type="number"
                                    readonly>
                            </div> -->

                            <div class="col-md-3 p-1 px-2 my-2">
                                <label for="area_construccion_predio" class="form-label fw-bold">Area construcción predio</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-house-door-fill"></i></span>
                                    <input type="text" class="form-control" id="area_construccion_predio"
                                        name="area_construccion_predio" aria-label="PrimerNombre" readonly>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group mt-4 mb-0">
            <button type="submit" class="btn btn-block text-white" style="background-color: #002F55;">
                <b><i class="bi bi-file-earmark-plus me-2"></i> GENERAR CERTIFICADO </b>
            </button>
        </div>
    </form>

</div>