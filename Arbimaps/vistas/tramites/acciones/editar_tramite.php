<?php
$cod_tramite = $_GET['cod'] ?? '';
$info_cod_tramite = $_GET['cod'] ?? '';

$sql = "SELECT * FROM tramite_radicacion WHERE cod_tramite = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $cod_tramite);
$stmt->execute();
$resultado = $stmt->get_result();
$tramite = $resultado->fetch_assoc();

$sql2 = "SELECT * FROM tramite_info_predio WHERE info_cod_tramite = ?";
$stmt2 = $mysqli->prepare($sql2);
$stmt2->bind_param("s", $info_cod_tramite);
$stmt2->execute();
$resultado2 = $stmt2->get_result();
$info_predio = $resultado2->fetch_assoc();

// Eliminar sufijo -NN (como -01, -02, etc.) del código de trámite
$cod_tramite_sin_sufijo = preg_replace('/-\d+$/', '', $cod_tramite);

// (Opcional) detectar el año desde el código para no hardcodear 2025
if (preg_match('/^[A-Z]+-(\d{4})-/', $cod_tramite, $m)) {
    $anio = $m[1];
} else {
    $anio = '2025'; // deja 2025 si prefieres fijo
}

$base = "/cons_neiva/Practica/tramites_conservacion/$anio/";
$ruta_con_sufijo = $base . $cod_tramite . '/';
$ruta_sin_sufijo = $base . $cod_tramite_sin_sufijo . '/';

// Elegir la ruta que existe (preferir con sufijo)
$docroot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
$fs_con = $docroot . $ruta_con_sufijo;
$fs_sin = $docroot . $ruta_sin_sufijo;

if ($docroot && is_dir($fs_con)) {
    $ruta_pdf = $ruta_con_sufijo;
} elseif ($docroot && is_dir($fs_sin)) {
    $ruta_pdf = $ruta_sin_sufijo;
} else {
    // Fallback: construir con sufijo por defecto
    $ruta_pdf = $ruta_con_sufijo;
}
?>

<script>
    let predios = [];

    fetch('../predios.json')
        .then(res => res.json())
        .then(data => {
            predios = data;
        });

    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('npn_predio');
        const suggestionsBox = document.getElementById('suggestions');

        input.addEventListener('input', function() {
            const valor = this.value.trim();
            suggestionsBox.innerHTML = '';

            if (valor.length < 5) return;

            const coincidencias = predios
                .filter(p => p.numero_predio.startsWith(valor))
                .slice(0, 10);

            if (coincidencias.length === 0) return;

            coincidencias.forEach(p => {
                const item = document.createElement('div');
                item.textContent = p.numero_predio;

                item.addEventListener('click', () => {
                    input.value = p.numero_predio;
                    suggestionsBox.innerHTML = '';

                    // Autocompletar los campos
                    document.getElementById('direccion_predio').value = p.direccion_predio || '';
                    document.getElementById('dest_econ_predio').value = p.dest_econ_predio || '';
                    document.getElementById('area_terreno_predio').value = p.area_terreno_predio || '';
                    document.getElementById('area_construccion_predio').value = p.area_construccion_predio || '';
                    document.getElementById('nombre_propietario_tramite').value = p.nombre_propietario_tramite || '';
                    document.getElementById('tipo_doc_propietario_tramite').value = p.tipo_doc_propietario_tramite || '';
                    document.getElementById('numero_doc_propietario_tramite').value = p.numero_doc_propietario_tramite || '';
                    document.getElementById('avaluo_terreno_tramite').value = p.avaluo_terreno_tramite || '';
                });

                suggestionsBox.appendChild(item);
            });
        });

        // Ocultar sugerencias al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !suggestionsBox.contains(e.target)) {
                suggestionsBox.innerHTML = '';
            }
        });
    });
</script>


<script>
    // Mostrar el modal de documentos automáticamente al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        var myModal = new bootstrap.Modal(document.getElementById('modalAlertaFiltro'));
        myModal.show();
    });
</script>

<!-- CONTENIDO PAGINA MODIFICACION -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <h1 class="h3 mb-0 text-center fw-bold">TRÁMITES DE RADICACIÓN</h1>
        <a href="../tramites/constancia_radicacion.php?cod=<?php echo urlencode($cod_tramite); ?>"
            target="_blank"
            class="d-none d-sm-inline-block btn btn-sm  shadow-sm text-white" style="background-color:#002F55;">
            <i class="bi bi-cloud-download me-1"></i> Generar Constancia
        </a>
    </div>
    <!-- Content Row -->

    <div class="row">

        <!-- col-xl-12 col-lg-7 -> ESTO LO QUE HACE ES DEFINIR EL ESPACIO DE LA TARJETA -->
        <div class="col-12 ">
            <div class="card shadow mb-4"></BR>
                <!-- <h4 class="text-primary text-center mb-3"><B>DATOS TRAMITES DE RADICACIÓN</B></h4> -->

                <div class="card-header mx-3 rounded-4" style="background-color: #002F55;">
                    <h5 class="text-white text-center py-1 m-0"><B>DATOS DEL TRÁMITE RADICADO</B></h5>
                </div>

                <form id="miFormulario" class="mb-0" action="edicion_tram_rad.php" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                        <div class="form-group">
                            <!--  CAMBIAR EL MODO DE ID, DESACTIVAR PORQUE DEBE SER AUTOMATICO -->
                            <div class="form-row px-3  mb-3">

                                <!-- <div class="col-md-6">
                                    <label for="cod_tramite"><b>ID_Radicacion</b></label>
                                    <input class="form-control py-4" id="cod_tramite" name="cod_tramite" type="text"
                                        value="<?php echo htmlspecialchars($tramite['cod_tramite']); ?>" readonly>
                                </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="cod_tramite" class="form-label fw-bold" style="font-size:0.9em">Identificador radicación</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi-journal-text"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="cod_tramite" name="cod_tramite"
                                            value="<?php echo htmlspecialchars($tramite['cod_tramite']); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="fecha_rad"><b>Hora - Fecha Radicación</b></label>
                                    <input class="form-control py-4" id="fecha_rad" name="fecha_rad" type="text"
                                        value="<?php echo htmlspecialchars($tramite['fecha_rad']); ?>" readonly>
                                </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="fecha_rad" class="form-label fw-bold" style="font-size:0.9em">Fecha y hora de radicación</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-calendar-date"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="fecha_rad" name="fecha_rad"
                                            value="<?php echo htmlspecialchars($tramite['fecha_rad']); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="documento_interesado"><b>Tipo Documento Interesado</b></label>
                                    <select class="custom-select" style="font-size:1.02em;" id="documento_interesado" name="documento_interesado" required>
                                        <option value="<?php echo htmlspecialchars($tramite['documento_interesado']); ?>" disabled selected>
                                            <b><?php echo htmlspecialchars($tramite['documento_interesado']); ?></b>
                                        </option>
                                        -- <option value="Cedula_Ciudadania">Cédula de Ciudadanía</option> --
                                        <option value="Cedula_Extranjeria">Cédula de Extranjería</option>
                                        <option value="NIT">N.I.T.</option>
                                        <option value="Pasaporte">Pasaporte</option>
                                    </select>
                                </div> -->

                                <div class="col-md-6 p-1 px-2 my-2 ">
                                    <label for="documento_interesado" class="form-label" style="font-size:0.9em;"><b>Documento interesado</b></label>
                                    <div class="input-group shadow-sm">
                                        <label class="input-group-text" for="documento_interesado">
                                            <i class="bi-person-badge"></i>
                                        </label>
                                        <select class="form-select" style="font-size:0.9em;" id="documento_interesado" name="documento_interesado" required>
                                            <option value="<?php echo htmlspecialchars($tramite['documento_interesado']); ?>" disabled selected>
                                                <b><?php echo htmlspecialchars($tramite['documento_interesado']); ?></b>
                                                <!-- <option value="Cedula_Ciudadania">Cédula de ciudadanía</option> -->
                                            <option value="Cedula_Extranjeria">Cédula de extranjería</option>
                                            <option value="NIT">N.I.T</option>
                                            <option value="Pasaporte">Pasaporte</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="num_doc_interesado"><b>Número Documento de Identidad Interesado</b></label>
                                    <input class="form-control py-4" id="num_doc_interesado" name="num_doc_interesado" type="number"
                                        value="<?php echo htmlspecialchars($tramite['num_doc_interesado']); ?>">
                                </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="num_doc_interesado" class="form-label fw-bold" style="font-size:0.9em">Número de documento de indentidad del interesado</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="num_doc_interesado" name="num_doc_interesado"
                                            value="<?php echo htmlspecialchars($tramite['num_doc_interesado']); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="primer_nombre_interesado"><b>Primer Nombre Interesado</b></label>
                                    <input class="form-control py-4" id="primer_nombre_interesado" name="primer_nombre_interesado" type="text"
                                        value="<?php echo htmlspecialchars($tramite['primer_nombre_interesado']); ?>">
                                </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="primer_nombre_interesado" class="form-label fw-bold" style="font-size:0.9em">Primer nombre del interesado</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="primer_nombre_interesado" name="primer_nombre_interesado"
                                            value="<?php echo htmlspecialchars($tramite['primer_nombre_interesado']); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="segundo_nombre_interesado"><b>Segundo Nombre Interesado</b></label>
                                    <input class="form-control py-4" id="segundo_nombre_interesado" name="segundo_nombre_interesado" type="text"
                                        value="<?php echo htmlspecialchars($tramite['segundo_nombre_interesado']); ?>">
                                </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="segundo_nombre_interesado" class="form-label fw-bold" style="font-size:0.9em">Segundo nombre del interesado</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="segundo_nombre_interesado" name="segundo_nombre_interesado"
                                            value="<?php echo htmlspecialchars($tramite['segundo_nombre_interesado']); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="primer_apellido_interesado"><b>Primer Apellido Interesado</b></label>
                                    <input class="form-control py-4" id="primer_apellido_interesado" name="primer_apellido_interesado" type="text"
                                        value="<?php echo htmlspecialchars($tramite['primer_apellido_interesado']); ?>">
                                </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="primer_apellido_interesado" class="form-label fw-bold" style="font-size:0.9em">Primer apellido del interesado</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi-people-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="primer_apellido_interesado" name="primer_apellido_interesado"
                                            value="<?php echo htmlspecialchars($tramite['primer_apellido_interesado']); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="segundo_apellido_interesado"><b>Segundo Apellido Interesado</b></label>
                                    <input class="form-control py-4" id="segundo_apellido_interesado" name="segundo_apellido_interesado" type="text"
                                        value="<?php echo htmlspecialchars($tramite['segundo_apellido_interesado']); ?>">
                                </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="segundo_apellido_interesado" class="form-label fw-bold" style="font-size:0.9em">Segundo apellido del interesado</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi-people"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="segundo_apellido_interesado" name="segundo_apellido_interesado"
                                            value="<?php echo htmlspecialchars($tramite['segundo_apellido_interesado']); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="telefono_interesado"><b>Número Telefónico Interesado</b></label>
                                    <input class="form-control py-4" id="telefono_interesado" name="telefono_interesado" type="text"
                                        value="<?php echo htmlspecialchars($tramite['telefono_interesado']); ?>">
                                </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="telefono_interesado" class="form-label fw-bold" style="font-size:0.9em">Número telefónico del interesado</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-telephone-fill me-2"></i>+57</span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="telefono_interesado" name="telefono_interesado"
                                            value="<?php echo htmlspecialchars($tramite['telefono_interesado']); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="correo_interesado"><b>Correo Electrónico Interesado</b></label>
                                    <input class="form-control py-4" id="correo_interesado" name="correo_interesado" type="text"
                                        value="<?php echo htmlspecialchars($tramite['correo_interesado']); ?>">
                                </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="correo_interesado" class="form-label fw-bold" style="font-size:0.9em">Correo electrónico de interesado</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-envelope-at-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="correo_interesado" name="correo_interesado"
                                            value="<?php echo htmlspecialchars($tramite['correo_interesado']); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="mutacion_tramite"><b>Seleccione Tramite</b></label>
                                    <select class="custom-select" style="font-size:1.08em;" id="mutacion_tramite" name="mutacion_tramite" required>
                                        <option value="<?php echo htmlspecialchars($tramite['mutacion_tramite']); ?>" disabled selected>
                                            <b><?php echo htmlspecialchars($tramite['mutacion_tramite']); ?></b>
                                        </option>
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
                                </div> -->

                                <div class="col-md-6 p-1 px-2 my-2 ">
                                    <label for="mutacion_tramite" class="form-label" style="font-size:0.9em;"><b>Seleccione el trámite</b></label>
                                    <div class="input-group shadow-sm">
                                        <label class="input-group-text" for="mutacion_tramite">
                                            <i class="bi bi-file-earmark-text-fill"></i>
                                        </label>
                                        <select class="form-select" id="mutacion_tramite" style="font-size:0.9em;" name="mutacion_tramite" required>
                                            <option value="<?php echo htmlspecialchars($tramite['mutacion_tramite']); ?>" disabled selected>
                                                <b><?php echo htmlspecialchars($tramite['mutacion_tramite']); ?></b>
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

                                <!-- <div class="col-md-6">
                                    <label for="tsolicitante_tramite"><b>Seleccione Tipo Solicitante</b></label>
                                    <select class="custom-select" style="font-size:1.02em;" id="tsolicitante_tramite" name="tsolicitante_tramite" required>
                                        <option value="<?php echo htmlspecialchars($tramite['tsolicitante_tramite']); ?>" disabled selected>
                                            <b><?php echo htmlspecialchars($tramite['tsolicitante_tramite']); ?></b>
                                        </option>
                                        <option value="Propietario">Propietario</option>
                                        <option value="Autorizado-Tercero">Autorizado - Tercero</option>
                                        <option value="Entidad_Publica">Entidad Pública</option>
                                        <option value="Poseedor">Poseedor</option>
                                    </select>
                                </div>  -->

                                <div class="col-md-6 p-1 px-2 my-2 ">
                                    <label for="tsolicitante_tramite" class="form-label" style="font-size:0.9em;"><b>Seleccione el trámite</b></label>
                                    <div class="input-group shadow-sm">
                                        <label class="input-group-text" for="tsolicitante_tramite">
                                            <i class="bi bi-file-earmark-text-fill"></i>
                                        </label>
                                        <select class="form-select" id="tsolicitante_tramite" style="font-size:0.9em;" name="tsolicitante_tramite" required>
                                            <option value="<?php echo htmlspecialchars($tramite['tsolicitante_tramite']); ?>" disabled selected>
                                                <b><?php echo htmlspecialchars($tramite['tsolicitante_tramite']); ?></b>
                                            <option value="Propietario">Propietario</option>
                                            <option value="Autorizado-Tercero">Autorizado - Tercero</option>
                                            <option value="Entidad_Publica">Entidad Pública</option>
                                            <option value="Poseedor">Poseedor</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="npn_predio" class="form-label fw-bold" style="font-size:0.9em">Solicitud escrita</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-file-earmark-pdf-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;"
                                            value="<?php echo htmlspecialchars($tramite['sol_escrita_tramite']); ?>" readonly>
                                    </div>

                                    <?php if (!empty($tramite['sol_escrita_tramite'])): ?>
                                        <div class="d-flex justify-content-center gap-2 my-2">
                                            <a href="<?php echo $ruta_pdf . $tramite['sol_escrita_tramite']; ?>"
                                                target="_blank" class="bot_verenotrapesta btn btn-sm">
                                                <i class="bi bi-box-arrow-right"></i> Ver en otra pestaña
                                            </a>
                                            <button type="button" class="bot_mostrar_vista btn btn-sm" onclick="toggleIframe('visor_sol_escrita', this)">
                                                <i class="bi bi-eye"></i> <span>Mostrar Vista Previa</span>
                                            </button>
                                        </div>
                                        <div id="visor_sol_escrita" class="iframe-animado">
                                            <iframe src="<?php echo $ruta_pdf . $tramite['sol_escrita_tramite']; ?>"
                                                width="100%" height="650px" style="border: 1px solid #ccc;"></iframe>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No se ha cargado aún ningún documento</span>
                                    <?php endif; ?>
                                </div>

                                <!-- Copia de Escritura -->
                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label class="form-label fw-bold" style="font-size:0.9em">Copia de Escritura / Sentencia Judicial / Acto Administrativo</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-file-earmark-pdf-fill"></i></span>
                                        <input class="form-control" style="font-size: 0.9em;"
                                            value="<?php echo htmlspecialchars($tramite['cop_escritura_tramite']); ?>" readonly>
                                    </div>

                                    <?php if (!empty($tramite['cop_escritura_tramite'])): ?>
                                        <div class="d-flex justify-content-center gap-2 my-2">
                                            <a href="<?php echo $ruta_pdf . $tramite['cop_escritura_tramite']; ?>" target="_blank" class="bot_verenotrapesta btn btn-sm">
                                                <i class="bi bi-box-arrow-right"></i> Ver en otra pestaña
                                            </a>
                                            <button type="button" class="bot_mostrar_vista btn btn-sm" onclick="toggleIframe('visor_escritura',this)">
                                                <i class="bi bi-eye"></i> <span>Mostrar Vista Previa</span>
                                            </button>
                                        </div>
                                        <div id="visor_escritura" class="iframe-animado">
                                            <iframe src="<?php echo $ruta_pdf . $tramite['cop_escritura_tramite']; ?>" width="100%" height="750px" style="border: 1px solid #ccc;"></iframe>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No se ha cargado aún ningún documento</span>
                                    <?php endif; ?>
                                </div>

                                <!-- Certificado Tradición -->
                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label class="form-label fw-bold" style="font-size:0.9em">Certificado Tradición y Libertad</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-file-earmark-pdf-fill"></i></span>
                                        <input class="form-control" style="font-size: 0.9em;"
                                            value="<?php echo htmlspecialchars($tramite['ctl_tramite']); ?>" readonly>
                                    </div>

                                    <?php if (!empty($tramite['ctl_tramite'])): ?>
                                        <div class="d-flex justify-content-center gap-2 my-2">
                                            <a href="<?php echo $ruta_pdf . $tramite['ctl_tramite']; ?>" target="_blank" class="bot_verenotrapesta btn btn-sm">
                                                <i class="bi bi-box-arrow-right"></i> Ver en otra pestaña
                                            </a>
                                            <button type="button" class="bot_mostrar_vista btn btn-sm" onclick="toggleIframe('visor_ctl',this)">
                                                <i class="bi bi-eye"></i> <span>Mostrar Vista Previa</span>
                                            </button>
                                        </div>
                                        <div id="visor_ctl" class="iframe-animado">
                                            <iframe src="<?php echo $ruta_pdf . $tramite['ctl_tramite']; ?>" width="100%" height="750px" style="border: 1px solid #ccc;"></iframe>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No se ha cargado aún ningún documento</span>
                                    <?php endif; ?>
                                </div>

                                <!-- Documento de Identidad -->
                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label class="form-label fw-bold" style="font-size:0.9em">Documento de Identidad</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                                        <input class="form-control" style="font-size: 0.9em;"
                                            value="<?php echo htmlspecialchars($tramite['doc_identidad_tramite']); ?>" readonly>
                                    </div>

                                    <?php if (!empty($tramite['doc_identidad_tramite'])): ?>
                                        <div class="d-flex justify-content-center gap-2 my-2">
                                            <a href="<?php echo $ruta_pdf . $tramite['doc_identidad_tramite']; ?>" target="_blank" class="bot_verenotrapesta btn btn-sm">
                                                <i class="bi bi-box-arrow-right"></i> Ver en otra pestaña
                                            </a>
                                            <button type="button" class="bot_mostrar_vista btn btn-sm" onclick="toggleIframe('visor_docid',this)">
                                                <i class="bi bi-eye"></i> <span>Mostrar Vista Previa</span>
                                            </button>
                                        </div>
                                        <div id="visor_docid" class="iframe-animado">
                                            <iframe src="<?php echo $ruta_pdf . $tramite['doc_identidad_tramite']; ?>" width="100%" height="750px" style="border: 1px solid #ccc;"></iframe>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No se ha cargado aún ningún documento</span>
                                    <?php endif; ?>
                                </div>

                                <!-- Carta de Autorización -->
                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label class="form-label fw-bold" style="font-size:0.9em">Carta de Autorización (si aplica)</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-envelope-open-fill"></i></span>
                                        <input class="form-control" style="font-size: 0.9em;"
                                            value="<?php echo htmlspecialchars($tramite['carta_autorizacion_tramite']); ?>" readonly>
                                    </div>

                                    <?php if (!empty($tramite['carta_autorizacion_tramite'])): ?>
                                        <div class="d-flex justify-content-center gap-2 my-2">
                                            <a href="<?php echo $ruta_pdf . $tramite['carta_autorizacion_tramite']; ?>" target="_blank" class="bot_verenotrapesta btn btn-sm">
                                                <i class="bi bi-box-arrow-right"></i> Ver en otra pestaña
                                            </a>
                                            <button type="button" class="bot_mostrar_vista btn btn-sm" onclick="toggleIframe('visor_autorizacion',this)">
                                                <i class="bi bi-eye"></i> <span>Mostrar Vista Previa</span>
                                            </button>
                                        </div>
                                        <div id="visor_autorizacion" class="iframe-animado">
                                            <iframe src="<?php echo $ruta_pdf . $tramite['carta_autorizacion_tramite']; ?>" width="100%" height="750px" style="border: 1px solid #ccc;"></iframe>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No se ha cargado aún ningún documento</span>
                                    <?php endif; ?>
                                </div>

                                <!-- Otros Documentos -->
                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label class="form-label fw-bold" style="font-size:0.9em">Otros Documentos Entregados</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-file-earmark-pdf"></i></span>
                                        <input class="form-control" style="font-size: 0.9em;"
                                            value="<?php echo htmlspecialchars($tramite['otros_doc_tramite']); ?>" readonly>
                                    </div>

                                    <?php if (!empty($tramite['otros_doc_tramite'])): ?>
                                        <div class="d-flex justify-content-center gap-2 my-2">
                                            <a href="<?php echo $ruta_pdf . $tramite['otros_doc_tramite']; ?>" target="_blank" class="bot_verenotrapesta btn btn-sm">
                                                <i class="bi bi-box-arrow-right"></i> Ver en otra pestaña
                                            </a>
                                            <button type="button" class="bot_mostrar_vista btn btn-sm" onclick="toggleIframe('visor_otros',this)">
                                                <i class="bi bi-eye"></i> <span>Mostrar Vista Previa</span>
                                            </button>
                                        </div>
                                        <div id="visor_otros" class="iframe-animado">
                                            <iframe src="<?php echo $ruta_pdf . $tramite['otros_doc_tramite']; ?>" width="100%" height="750px" style="border: 1px solid #ccc;"></iframe>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No se ha cargado aún ningún documento</span>
                                    <?php endif; ?>
                                </div>

                            </div>

                            <!-- FUNCION PARA MOSTRAR Y OCULTAR -->
                            <script>
                                function toggleIframe(id, boton) {
                                    const visor = document.getElementById(id);
                                    const icono = boton.querySelector("i");
                                    const texto = boton.querySelector("span");

                                    visor.classList.toggle("mostrar");

                                    if (visor.classList.contains("mostrar")) {
                                        icono.classList.replace("bi-eye", "bi-eye-slash");
                                        texto.textContent = "Ocultar Vista Previa";
                                    } else {
                                        icono.classList.replace("bi-eye-slash", "bi-eye");
                                        texto.textContent = "Mostrar Vista Previa";
                                    }
                                }
                            </script>

                            <!-- <div class="form-row">
                                <div class="col-md-6 mb-4">
                                    <label for="sol_escrita_tramite"><b>Solicitud Escrita</b></label>
                                    <input class="form-control py-4" id="sol_escrita_tramite" name="sol_escrita_tramite" type="text"
                                        value="<?php echo htmlspecialchars($tramite['sol_escrita_tramite']); ?>" readonly>

                                    <?php if (!empty($tramite['sol_escrita_tramite'])): ?>
                                        <div class="d-flex justify-content-center gap-2 my-2">
                                            <a href="<?php echo $ruta_pdf . $tramite['cod_tramite'] . '/' . $tramite['sol_escrita_tramite']; ?>"
                                                target="_blank" class="btn btn-outline-info btn-sm">
                                                Ver en otra pestaña
                                            </a>
                                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleIframe('visor_sol_escrita')">
                                                Mostrar/Ocultar Vista Previa
                                            </button>
                                        </div>
                                        <div id="visor_sol_escrita" style="display: none;">
                                            <iframe src="<?php echo $ruta_pdf . $tramite['cod_tramite'] . '/' . $tramite['sol_escrita_tramite']; ?>"
                                                width="100%" height="750px" style="border: 1px solid #ccc;"></iframe>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No se ha cargado aún ningún documento</span>
                                    <?php endif; ?>
                                </div>

                                ------- Copia de Escritura -------
                                <div class="col-md-6 mb-4">
                                    <label for="cop_escritura_tramite"><b>Copia de Escritura - Sentencia Judicial - Acto Administrativo</b></label>
                                    <input class="form-control py-4" id="cop_escritura_tramite" name="cop_escritura_tramite" type="text"
                                        value="<?php echo htmlspecialchars($tramite['cop_escritura_tramite']); ?>" readonly>

                                    <?php if (!empty($tramite['cop_escritura_tramite'])): ?>
                                        <div class="d-flex justify-content-center gap-2 my-2">
                                            <a href="<?php echo $ruta_pdf . $tramite['cod_tramite'] . '/' . $tramite['cop_escritura_tramite']; ?>"
                                                target="_blank" class="btn btn-outline-info btn-sm">
                                                Ver en otra pestaña
                                            </a>
                                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleIframe('visor_escritura')">
                                                Mostrar/Ocultar Vista Previa
                                            </button>
                                        </div>
                                        <div id="visor_escritura" style="display: none;">
                                            <iframe src="<?php echo $ruta_pdf . $tramite['cod_tramite'] . '/' . $tramite['cop_escritura_tramite']; ?>"
                                                width="100%" height="750px" style="border: 1px solid #ccc;"></iframe>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No se ha cargado aún ningún documento</span>
                                    <?php endif; ?>
                                </div>
                            </div> -->

                            <!-- <div class="form-row">
                                ----- Certificado Tradición -----
                                <div class="col-md-6 mb-4">
                                    <label for="ctl_tramite"><b>Certificado Tradición y Libertad</b></label>
                                    <input class="form-control py-4" id="ctl_tramite" name="ctl_tramite" type="text"
                                        value="<?php echo htmlspecialchars($tramite['ctl_tramite']); ?>" readonly>

                                    <?php if (!empty($tramite['ctl_tramite'])): ?>
                                        <div class="d-flex justify-content-center gap-2 my-2">
                                            <a href="<?php echo $ruta_pdf . $tramite['cod_tramite'] . '/' . $tramite['ctl_tramite']; ?>"
                                                target="_blank" class="btn btn-outline-info btn-sm">
                                                Ver en otra pestaña
                                            </a>
                                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleIframe('visor_ctl')">
                                                Mostrar/Ocultar Vista Previa
                                            </button>
                                        </div>
                                        <div id="visor_ctl" style="display: none;">
                                            <iframe src="<?php echo $ruta_pdf . $tramite['cod_tramite'] . '/' . $tramite['ctl_tramite']; ?>"
                                                width="100%" height="750px" style="border: 1px solid #ccc;"></iframe>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No se ha cargado aún ningún documento</span>
                                    <?php endif; ?>
                                </div>

                                ----- Documento de Identidad -----
                                <div class="col-md-6 mb-4">
                                    <label for="doc_identidad_tramite"><b>Documento de Identidad</b></label>
                                    <input class="form-control py-4" id="doc_identidad_tramite" name="doc_identidad_tramite" type="text"
                                        value="<?php echo htmlspecialchars($tramite['doc_identidad_tramite']); ?>" readonly>

                                    <?php if (!empty($tramite['doc_identidad_tramite'])): ?>
                                        <div class="d-flex justify-content-center gap-2 my-2">
                                            <a href="<?php echo $ruta_pdf . $tramite['cod_tramite'] . '/' . $tramite['doc_identidad_tramite']; ?>"
                                                target="_blank" class="btn btn-outline-info btn-sm">
                                                Ver en otra pestaña
                                            </a>
                                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleIframe('visor_docid')">
                                                Mostrar/Ocultar Vista Previa
                                            </button>
                                        </div>
                                        <div id="visor_docid" style="display: none;">
                                            <iframe src="<?php echo $ruta_pdf . $tramite['cod_tramite'] . '/' . $tramite['doc_identidad_tramite']; ?>"
                                                width="100%" height="750px" style="border: 1px solid #ccc;"></iframe>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No se ha cargado aún ningún documento</span>
                                    <?php endif; ?>
                                </div>
                            </div> -->

                            <!-- <div class="form-row">
                                ----- Carta de Autorización ------
                                <div class="col-md-6 mb-4">
                                    <label for="carta_autorizacion_tramite"><b>Carta Autorización Tramite (en caso de ser tercero)</b></label>
                                    <input class="form-control py-4" id="carta_autorizacion_tramite" name="carta_autorizacion_tramite" type="text"
                                        value="<?php echo htmlspecialchars($tramite['carta_autorizacion_tramite']); ?>" readonly>

                                    <?php if (!empty($tramite['carta_autorizacion_tramite'])): ?>
                                        <div class="d-flex justify-content-center gap-2 my-2">
                                            <a href="<?php echo $ruta_pdf . $tramite['cod_tramite'] . '/' . $tramite['carta_autorizacion_tramite']; ?>"
                                                target="_blank" class="btn btn-outline-info btn-sm">
                                                Ver en otra pestaña
                                            </a>
                                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleIframe('visor_autorizacion')">
                                                Mostrar/Ocultar Vista Previa
                                            </button>
                                        </div>
                                        <div id="visor_autorizacion" style="display: none;">
                                            <iframe src="<?php echo $ruta_pdf . $tramite['cod_tramite'] . '/' . $tramite['carta_autorizacion_tramite']; ?>"
                                                width="100%" height="750px" style="border: 1px solid #ccc;"></iframe>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No se ha cargado aún ningún documento</span>
                                    <?php endif; ?>
                                </div>

                                ----- Otros Documentos -------
                                <div class="col-md-6 mb-4">
                                    <label for="otros_doc_tramite"><b>Otros Documentos Entregados</b></label>
                                    <input class="form-control py-4" id="otros_doc_tramite" name="otros_doc_tramite" type="text"
                                        value="<?php echo htmlspecialchars($tramite['otros_doc_tramite']); ?>" readonly>

                                    <?php if (!empty($tramite['otros_doc_tramite'])): ?>
                                        <div class="d-flex justify-content-center gap-2 my-2">
                                            <a href="<?php echo $ruta_pdf . $tramite['cod_tramite'] . '/' . $tramite['otros_doc_tramite']; ?>"
                                                target="_blank" class="btn btn-outline-info btn-sm">
                                                Ver en otra pestaña
                                            </a>
                                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleIframe('visor_otros')">
                                                Mostrar/Ocultar Vista Previa
                                            </button>
                                        </div>
                                        <div id="visor_otros" style="display: none;">
                                            <iframe src="<?php echo $ruta_pdf . $tramite['cod_tramite'] . '/' . $tramite['otros_doc_tramite']; ?>"
                                                width="100%" height="750px" style="border: 1px solid #ccc;"></iframe>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No se ha cargado aún ningún documento</span>
                                    <?php endif; ?>
                                </div>
                            </div> -->

                            <div class="form-group my-4">
                                <label for="observacion_tramite" class="my-2"><b>Descripción y Observaciones de Trámite</b></label>
                                <input class="form-control py-4" id="observacion_tramite" name="observacion_tramite" type="textarea"
                                    style="background-color: #002f5544;"
                                    value="<?php echo htmlspecialchars($tramite['observacion_tramite']); ?>" readonly>
                            </div>

                            <hr>
                            <div class="card-header mx-3 rounded-4 mb-5" style="background-color: #002F55;">
                                <h5 class="text-white text-center py-1 mb-0"><B>INFORMACIÓN DEL PREDIO</B></h5>
                            </div>

                            <div class="form-row">

                                <!-- <div class="col-md-6">
                                    <label for="fmi_predio_tram"><b>FMI Predio/Terreno</b></label>
                                    <input class="form-control py-4" id="fmi_predio_tram" name="fmi_predio_tram" type="text"
                                        value="<?php echo htmlspecialchars($info_predio['fmi_predio_tram']); ?>">
                                </div>  -->

                                <div class="col-md-6 p-1 px-2 my-1">
                                    <label for="fmi_predio_tram" class="form-label fw-bold" style="font-size:0.9em;">FMI predio / Terreno</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-map-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="fmi_predio_tram"
                                            name="fmi_predio_tram" aria-label="PrimerNombre" value="<?php echo htmlspecialchars($info_predio['fmi_predio_tram']); ?>">
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="npn_predio_tram"><b>Cod. Catastral Predio - NPN </b></label>
                                    <div class="input-group">
                                        <input class="form-control py-4" id="npn_predio_tram" name="npn_predio_tram" type="text"
                                            value="<?php echo htmlspecialchars($info_predio['npn_predio_tram']); ?>">
                                        <div id="suggestions" class="autocomplete-items bg-white  position-absolute w-100 zindex-dropdown" style="max-height: 200px; overflow-y: auto;"></div>

                                        <div class="input-group-append">
                                            <a href="../../neiva_visor/index.html?valor=<?php echo urlencode($info_predio['npn_predio_tram']); ?>"
                                                target="_blank"
                                                class="btn btn-outline-primary btn-sm">
                                                Ver en mapa
                                            </a>
                                        </div>
                                    </div>
                                </div> -->

                                <div class="col-md-6 p-1 px-2 my-1">
                                    <label for="npn_predio_tram" class="form-label fw-bold" style="font-size:0.9em;">Cod. catastral predio - NPN</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-map"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="npn_predio_tram"
                                            name="npn_predio_tram" aria-label="PrimerNombre" value="<?php echo htmlspecialchars($info_predio['npn_predio_tram']); ?>" readonly>
                                        <a class="bot_mostrar_vista btn" style="font-size:0.9em" type="button" id="button-addon2"
                                            href="../neiva_visor/index.html?valor=<?php echo urlencode($info_predio['npn_predio_tram']); ?>" target="_blank">
                                            <i class="bi bi-globe-americas me-1"></i> Ver en Visor Cat.</a>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="nombre_propietario_tram"><b>Nombre Propietario Predio/Terreno</b></label>
                                    <input class="form-control py-4" id="nombre_propietario_tram" name="nombre_propietario_tram" type="text"
                                        value="<?php echo htmlspecialchars($info_predio['nombre_propietario_tram']); ?>" readonly>
                                </div> -->

                                <div class="col-md-4 p-1 px-2 my-2">
                                    <label for="nombre_propietario_tram" class="form-label fw-bold" style="font-size:0.9em;">Nombre Propietario Predio/Terreno</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="nombre_propietario_tram"
                                            name="nombre_propietario_tram" aria-label="PrimerNombre" value="<?php echo htmlspecialchars($info_predio['nombre_propietario_tram']); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="tipo_doc_propietario_tram"><b>Tipo Documento Propietario Predio/Terreno</b></label>
                                    <input class="form-control py-4" id="tipo_doc_propietario_tram" name="tipo_doc_propietario_tram" type="text"
                                        value="<?php echo htmlspecialchars($info_predio['tipo_doc_propietario_tram']); ?>" readonly>
                                </div>  -->

                                <div class="col-md-4 p-1 px-2 my-2">
                                    <label for="tipo_doc_propietario_tram" class="form-label fw-bold" style="font-size:0.9em;">Tipo Documento Propietario Predio/Terreno</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi-person-badge"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="tipo_doc_propietario_tram"
                                            name="tipo_doc_propietario_tram" aria-label="PrimerNombre" value="<?php echo htmlspecialchars($info_predio['tipo_doc_propietario_tram']); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="cedula_propietario_tram"><b>Número de Documento Propietario Predio/Terreno</b></label>
                                    <input class="form-control py-4" id="cedula_propietario_tram" name="cedula_propietario_tram" type="text"
                                        value="<?php echo htmlspecialchars($info_predio['cedula_propietario_tram']); ?>" readonly>
                                </div> -->

                                <div class="col-md-4 p-1 px-2 my-2">
                                    <label for="cedula_propietario_tram" class="form-label fw-bold" style="font-size:0.9em;">Número de Documento Propietario Predio/Terreno</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-vcard-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="cedula_propietario_tram"
                                            name="cedula_propietario_tram" aria-label="PrimerNombre" value="<?php echo htmlspecialchars($info_predio['cedula_propietario_tram']); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="valor_avaluo_terreno_tram"><b>Valor del Avaluo de Predio/Terreno</b></label>
                                    <input class="form-control py-4" id="valor_avaluo_terreno_tram" name="valor_avaluo_terreno_tram" type="text"
                                        value="<?php echo htmlspecialchars($info_predio['valor_avaluo_terreno_tram']); ?>" readonly>
                                </div> -->

                                <div class="col-md-4 p-1 px-2 my-2">
                                    <label for="valor_avaluo_terreno_tram" class="form-label fw-bold" style="font-size:0.9em;">Valor del avaluo del predio / Terreno</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-piggy-bank"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="valor_avaluo_terreno_tram"
                                            name="valor_avaluo_terreno_tram" aria-label="PrimerNombre" value="<?php echo htmlspecialchars($info_predio['valor_avaluo_terreno_tram']); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="direccion_predio_terreno_tram"><b>Dirección del Predio/Terreno</b></label>
                                    <input class="form-control py-4" id="direccion_predio_terreno_tram" name="direccion_predio_terreno_tram" type="text"
                                        value="<?php echo htmlspecialchars($info_predio['direccion_predio_terreno_tram']); ?>" readonly>
                                </div> -->

                                <div class="col-md-4 p-1 px-2 my-2">
                                    <label for="direccion_predio_terreno_tram" class="form-label fw-bold" style="font-size:0.9em;">Dirección del Predio / Terreno</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-signpost-2-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="direccion_predio_terreno_tram"
                                            name="direccion_predio_terreno_tram" aria-label="PrimerNombre" value="<?php echo htmlspecialchars($info_predio['direccion_predio_terreno_tram']); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="destino_econ_predio_tram"><b>Destino Economico Predio/Terreno</b></label>
                                    <input class="form-control py-4" id="destino_econ_predio_tram" name="destino_econ_predio_tram" type="text"
                                        value="<?php echo htmlspecialchars($info_predio['destino_econ_predio_tram']); ?>" readonly>
                                </div> -->

                                <div class="col-md-4 p-1 px-2 my-2">
                                    <label for="destino_econ_predio_tram" class="form-label fw-bold" style="font-size:0.9em;">Destinación económica predio / Terreno</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-house-exclamation "></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="destino_econ_predio_tram"
                                            name="destino_econ_predio_tram" aria-label="PrimerNombre" value="<?php echo htmlspecialchars($info_predio['destino_econ_predio_tram']); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="area_terr_predio_tram"><b>Area Predio/Terreno</b></label>
                                    <input class="form-control py-4" id="area_terr_predio_tram" name="area_terr_predio_tram" type="text"
                                        value="<?php echo htmlspecialchars($info_predio['area_terr_predio_tram']); ?>" readonly>
                                </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="area_terr_predio_tram" class="form-label fw-bold" style="font-size:0.9em;">Area predio / Terreno</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-globe-americas"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="area_terr_predio_tram"
                                            name="area_terr_predio_tram" aria-label="PrimerNombre" value="<?php echo htmlspecialchars($info_predio['area_terr_predio_tram']); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="area_cons_predio_tram"><b>Area Construcción Predio/Terreno</b></label>
                                    <input class="form-control py-4" id="area_cons_predio_tram" name="area_cons_predio_tram" type="text"
                                        value="<?php echo htmlspecialchars($info_predio['area_cons_predio_tram']); ?>" readonly>
                                </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="area_cons_predio_tram" class="form-label fw-bold" style="font-size:0.9em;">Area de construcción predio / Terreno</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-house-door-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="area_cons_predio_tram"
                                            name="area_cons_predio_tram" aria-label="PrimerNombre" value="<?php echo htmlspecialchars($info_predio['area_cons_predio_tram']); ?>" readonly>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->
</div>
<!-- End of Main Content -->
<script>
    document.getElementById("form-tramite").addEventListener("submit", function(e) {
        e.preventDefault();

        const loadingScreen = document.getElementById("loading-screen");
        const submitBtn = document.getElementById("btn-submit");

        loadingScreen.style.display = "flex";
        submitBtn.classList.add("enviando");
        submitBtn.disabled = true;

        const formData = new FormData(this);

        fetch("../cargue_tramite_rad.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = "consultas_tramites.php?codigo=" + encodeURIComponent(data.codigo);
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
</script>
</body>

</html>