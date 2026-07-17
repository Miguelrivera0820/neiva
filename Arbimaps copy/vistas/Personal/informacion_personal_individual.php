<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}

$idUsuario = $_SESSION['id_usuario'];

$con_num_identidad = isset($_GET['con_num_identidad']) ? trim($_GET['con_num_identidad']) : '';
if ($con_num_identidad === '') {
    echo "No se recibió el número de identidad.";
    exit;
}

$query = "SELECT * FROM contratacion WHERE con_num_identidad = ? LIMIT 1";
$stmt  = $mysqli->prepare($query);
if (!$stmt) {
    echo "Error preparando consulta: " . $mysqli->error;
    exit;
}

$stmt->bind_param("s", $con_num_identidad);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    echo "No se encontraron datos para esta cédula.";
    exit;
}

$row = $result->fetch_assoc();

$con_nombres            = $row['con_nombres']            ?? "";
$con_apellidos          = $row['con_apellidos']          ?? "";
$con_tipo_documento     = $row['con_tipo_documento']     ?? "";
$con_FechaExpe          = $row['con_FechaExpe']          ?? "";
$con_lugarE             = $row['con_lugarE']             ?? "";
$con_fecha_nacimiento   = $row['con_fecha_nacimiento']   ?? "";
$con_edad               = $row['con_edad']               ?? "";
$con_lugar_nacimiento   = $row['con_lugar_nacimiento']   ?? "";
$con_direccion          = $row['con_direccion']          ?? "";
$con_barrio             = $row['con_barrio']             ?? "";
$con_ciudad             = $row['con_ciudad']             ?? "";
$con_tel_fijo           = $row['con_tel_fijo']           ?? "";
$con_celular            = $row['con_celular']            ?? "";
$con_correo             = $row['con_correo']             ?? "";
$con_correo_corporativo = $row['con_correo_corporativo'] ?? "";
$con_estado_civil       = $row['con_estado_civil']       ?? "";
$con_nombre_conyuge     = $row['con_nombre_conyuge']     ?? "";
$con_enfermedades       = $row['con_enfermedades']       ?? "";
$con_alergias           = $row['con_alergias']           ?? "";
$con_medicamentos       = $row['con_medicamentos']       ?? "";
$con_contingencia       = $row['con_contingencia']       ?? "";
$con_emergencia         = $row['con_emergencia']         ?? "";
$con_parentesco         = $row['con_parentesco']         ?? "";
$con_tel_emergencia     = $row['con_tel_emergencia']     ?? "";
$con_num_cuenta         = $row['con_num_cuenta']         ?? "";
$con_tipo_cuenta        = $row['con_tipo_cuenta']        ?? "";
$con_financiera         = $row['con_financiera']         ?? "";
$con_eps                = $row['con_eps']                ?? "";
$con_afp                = $row['con_afp']                ?? "";
$con_arl                = $row['con_arl']                ?? "";
$con_rh                 = $row['con_rh']                 ?? "";
$con_profesion          = $row['con_profesion']          ?? "";   
$con_escolaridad        = $row['con_escolaridad']        ?? "";
$con_grado              = $row['con_grado']              ?? "";   
$con_num_tarjeta        = $row['con_num_tarjeta']        ?? "";   
$con_expedicion         = $row['con_expedicion']         ?? "";  
$con_sede               = $row['con_sede']               ?? "";
$con_presencialidad     = $row['con_presencialidad']     ?? "";
$con_cargo              = $row['con_cargo']              ?? "";
$con_proyecto           = $row['con_proyecto']           ?? "";
$con_tipo_contrato      = $row['con_tipo_contrato']      ?? "";
$con_fecha_inicio       = $row['con_fecha_inicio']       ?? "";
$con_fecha_final        = $row['con_fecha_final']        ?? "";
$con_duracion           = $row['con_duracion']           ?? "";
$con_salario            = $row['con_salario']            ?? "";
$con_jefe               = $row['con_jefe']               ?? "";
$con_per_cargo          = $row['con_per_cargo']          ?? "";
$con_valor_proyecto     = $row['con_valor_proyecto']     ?? "";
$con_genero             = $row['con_genero']             ?? "";
$con_raza               = $row['con_raza']               ?? "";
$con_vivienda           = $row['con_vivienda']           ?? "";
$con_estrato            = $row['con_estrato']            ?? "";
$con_discapacidad       = $row['con_discapacidad']       ?? "";

$con_amarilla           = $row['con_amarilla']           ?? 0;
$con_tetano1            = $row['con_tetano1']            ?? 0;
$con_tetano2            = $row['con_tetano2']            ?? 0;
$con_tetano3            = $row['con_tetano3']            ?? 0;
$con_covid1             = $row['con_covid1']             ?? 0;
$con_covid2             = $row['con_covid2']             ?? 0;
$con_covid3             = $row['con_covid3']             ?? 0;
$con_influenza          = $row['con_influenza']          ?? 0;
$con_hepatitis_a        = $row['con_hepatitis_a']        ?? 0;
$con_hepatitis_c        = $row['con_hepatitis_c']        ?? 0;
$vph_1                  = $row['vph_1']                  ?? 0;
$vph_2                  = $row['vph_2']                  ?? 0;
$vph_3                  = $row['vph_3']                  ?? 0;

$cedula                 = $row['con_cedula']        ?? null;
$rut                    = $row['con_rut']           ?? null;
$judiciales             = $row['con_antecedentes']  ?? null;
$contraloria            = $row['con_contraloria']   ?? null;
$procuraduria           = $row['con_procuraduria']  ?? null;
$hojavida               = $row['con_hoja_vida']     ?? null;
$foto                   = $row['photo']             ?? null;

$rolesPermitidos = array("administrador", "director_presupuestos", "pagos", "seguridad_social", "director_proyectos", "Directivos", "usuarios_ops");

function ajustarRutaDocumento($rutaBD)
{
    if (empty($rutaBD)) return [null, null];

    $rutaBD = str_replace('\\', '/', $rutaBD);
    $prefijoCorrecto = "/arbimaps/Arbimaps/vistas/Personal/";

    if (strpos($rutaBD, $prefijoCorrecto) === 0) {
        $rutaWeb = $rutaBD;
    } else {
        $prefijoViejo = "/arbimaps/Arbimaps/";
        if (strpos($rutaBD, $prefijoViejo) === 0) {
            $rutaBD = substr($rutaBD, strlen($prefijoViejo));
        }
        $rutaWeb = $prefijoCorrecto . ltrim($rutaBD, "/");
    }

    $rutaWeb = preg_replace('#/+#', '/', $rutaWeb);
    $rutaFisica = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . $rutaWeb;
    return [$rutaWeb, $rutaFisica];
}
?>
<style>
    .form-group label {
        height: 45px;
        display: flex;
        align-items: center;
        font-weight: 600;
        margin-bottom: 0.5rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .form-control {
        font-size: 14px;
    }

    .form-label {
        font-weight: 600;
        color: rgb(29, 30, 30);
        font-size: 0.95rem;
    }

    .form-title {
        font-size: 1.3rem;
        font-weight: 600;
        text-align: center;
        margin-bottom: 0.3rem;
        color: #333;
    }

    .form-subtitle {
        text-align: center;
        font-size: 0.95rem;
        color: #6c757d;
        margin-bottom: 2rem;
    }

    .card-form {
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        background-color: #fff;

    }

    .card-form header {
        background: linear-gradient(90deg, #0062cc, #3f87d0);
        color: #fff;
        padding: 1.5rem 2rem;
        font-size: 1.5rem;
        font-weight: 600;
        text-align: center;
        border-top-left-radius: 1rem;
        border-top-right-radius: 1rem;
    }

    .active-section-btn {
        background-color: #0062cc !important;
        color: white !important;
        font-weight: bold;
    }

    .mt-4.d-flex {
        padding: 1rem;
    }

    .btn-outline-primary {
        border-color: #0062cc !important;

    }

    .btn-outline-primary:hover {
        background-color: #0062cc !important;
        border-color: #0062cc !important;
        color: white !important;
    }

    .active-section-btn:focus,
    .active-section-btn:active,
    .active-section-btn:focus-visible {
        box-shadow: none !important;
        border-color: #0062cc !important;
        outline: none;
    }

    .stepper-wrapper {
        position: relative;
        padding: 25px 0;
    }

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
        font-size: 0.7rem;
        border: 2px solid #dae1e5ff;
        margin: 2px;
        white-space: nowrap;
        width: auto;
        cursor: pointer;
        transition: all 0.3s ease;
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

    .stepper-line {
        width: 100%;
    }

    @media (min-width:1600px) {
        .step {
            font-size: 0.9rem;
        }
    }

    .card-header {
        background-color: #002F55;
        background-image: url("data:image/svg+xml;utf8,\<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23e5e7eb'>\<path d='M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0'/>\<path fill-rule='evenodd' d='M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1'/>\</svg>");
        background-repeat: no-repeat;
        background-size: 4em;
        background-position: 101% 140%;
    }

    .photo-wrapper {
        position: relative;
        display: inline-block;
        max-width: 100%;
    }

    .photo-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 47, 85, 0.65);
        color: #fff;
        font-size: 0.95rem;
        font-weight: 600;
        border-radius: 12px;

        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;

        opacity: 0;
        text-decoration: none;
        transition: opacity 0.3s ease;
    }

    .photo-wrapper:hover .photo-overlay {
        opacity: 1;
    }

    .photo-wrapper:hover .photo-img {
        transform: scale(1.02);
    }

    .photo-overlay i {
        font-size: 2rem;
        margin-bottom: 6px;
    }

    .photo-fixed {
        width: 320px;
        height: 350px;
        overflow: hidden;
        border-radius: 12px;
        border: 2px dashed #002f5582;
    }

    .photo-fixed .photo-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }
</style>
<div class="container-fluid px-3">

    <div class="my-4 text-center">
        <h4 class=" mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 700 !important ">SECCIÓN DE INFORMACIÓN PERSONAL </h4>
        <small> Modulo para la consulta de la información del personal</small>
    </div>

    <div class="card-form p-3">
        <div class="stepper-wrapper  mb-0 justify-content-center">
            <div class="stepper-line">
                <div id="stepper-progress"></div>
            </div>
            <div class="stepper">
                <button class="step active" onclick="seccion(1)">Datos Personales</button>
                <button class="step" onclick="seccion(2)">Núcleo Familiar</button>
                <button class="step" onclick="seccion(3)">Datos Financieros</button>
                <button class="step" onclick="seccion(4)">Historial Académico</button>
                <button class="step" onclick="seccion(5)">Vinculación Contractual</button>
                <button class="step" onclick="seccion(6)">Información Sociodemográfica</button>
                <button class="step" onclick="seccion(7)">Historial de Vacunación</button>
                <button class="step" onclick="seccion(8)">Documentos</button>
            </div>
        </div>

        <div class="card-body ">
            <form id="multiStepForm" method="POST" action="guardar_cambios.php">
                <div class="form-section" id="section1">
                    <div class="mb-3  d-flex align-items-center text-start rounded-4 card-header shadow  p-3 text-white">
                        <i class="bi bi-person-circle me-3 fs-2"></i>
                        <div>
                            <h3 class="h5 mb-1">Datos personales</h3>
                            <p class=" mb-0" style="font-size: 0.85rem; color:#999999">
                                Información básica del colaborador.
                            </p>
                        </div>
                    </div>
                    <div class="row px-4">
                        <?php
                        $cedula = $row['con_cedula'] ?? null;
                        $rut = $row['con_rut'] ?? null;
                        $judiciales = $row['con_antecedentes'] ?? null;
                        $contraloria = $row['con_contraloria'] ?? null;
                        $procuraduria = $row['con_procuraduria'] ?? null;
                        $hojavida = $row['con_hoja_vida'] ?? null;
                        $foto = $row['photo'] ?? null;
                        ?>
                        <div class="col-12">
                            <div class="row  align-items-center justify-content-center">
                                <div class="col-12 col-lg-3 p-1 px-3  mx-auto ">
                                    <div class="card shadow-sm rounded-4 border p-3" style="background-color: #002f5523;">
                                        <label class="form-label text-center w-100" for="photo">Foto Personal</label>
                                        <?php if (!empty($row['photo'])): ?>
                                            <?php
                                            list($rutaWeb, $rutaFisica) = ajustarRutaDocumento($row['photo']);
                                            ?>
                                            <?php if ($rutaWeb && file_exists($rutaFisica)): ?>
                                                <div class="text-center mt-3">
                                                    <div class="photo-wrapper photo-fixed">
                                                        <img src="<?php echo htmlspecialchars($rutaWeb, ENT_QUOTES, 'UTF-8'); ?>"
                                                            alt="Foto del colaborador"
                                                            class="photo-img">
                                                        <a href="<?php echo htmlspecialchars($rutaWeb, ENT_QUOTES, 'UTF-8'); ?>"
                                                            download
                                                            class="photo-overlay"
                                                            title="Descargar foto">
                                                            <i class="bi bi-download"></i>
                                                            <span>Descargar</span>
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="alert alert-warning mt-3 small text-center">
                                                    <i class="bi bi-exclamation-triangle"></i>
                                                    La foto está registrada, pero no se encuentra en el servidor.
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="alert alert-secondary mt-3 small text-center">
                                                <i class="bi bi-person-x"></i>
                                                No se ha cargado ninguna foto.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-12 col-lg-9">
                                    <div class="row">
                                        <div class="col-md-6 p-1 px-2 my-1">
                                            <label for="con_nombres" class="form-label fw-bold" style="font-size:0.9em;">Nombres</label>
                                            <div class="input-group  ">
                                                <span class="input-group-text shadow-sm"><i class="bi bi-person"></i></span>
                                                <input type="text" class="form-control" id="nombres" name="nombres" value="<?php echo $con_nombres ?? ''; ?>" disabled>
                                            </div>
                                        </div>

                                        <div class="col-md-6 p-1 px-2 my-1">
                                            <label for="con_apellidos" class="form-label fw-bold" style="font-size:0.9em;">Apellidos</label>
                                            <div class="input-group ">
                                                <span class="input-group-text shadow-sm" id="basic-addon1"><i class="bi-people"></i></span>
                                                <input type="text" class="form-control" name="con_apellidos" id="con_apellidos" value="<?php echo $con_apellidos; ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-4 p-1 px-2 my-1">
                                            <label for="con_apellidos" class="form-label fw-bold" style="font-size:0.9em;">Tipo de documento</label>
                                            <div class="input-group ">
                                                <span class="input-group-text shadow-sm" id="basic-addon1"><i class="bi-person-badge"></i></span>
                                                <input type="text" class="form-control" name="con_apellidos" id="con_apellidos" value="<?php echo $con_tipo_documento; ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-4 p-1 px-2 my-1">
                                            <label for="con_num_identidad" class="form-label fw-bold" style="font-size:0.9em;">Número de identidad</label>
                                            <div class="input-group ">
                                                <span class="input-group-text shadow-sm"><i class="bi bi-person-vcard"></i></span>
                                                <input class="form-control" name="con_num_identidad" id="con_num_identidad" value="<?php echo $con_num_identidad; ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-4 p-1 px-2 my-1">
                                            <label for="con_celular" class="form-label fw-bold" style="font-size:0.9em;">Celular</label>
                                            <div class="input-group ">
                                                <span class="input-group-text shadow-sm"><i class="bi bi-phone"></i></span>
                                                <input class="form-control" name="con_celular" id="con_celular" value="<?php echo $con_celular; ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-4 p-1 px-2 my-1">
                                            <label for="con_direccion" class="form-label fw-bold" style="font-size:0.9em;">Dirección</label>
                                            <div class="input-group ">
                                                <span class="input-group-text shadow-sm"><i class="bi bi-geo-alt"></i></span>
                                                <input class="form-control" name="con_direccion" id="con_direccion" value="<?php echo $con_direccion; ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-4 p-1 px-2 my-1">
                                            <label for="con_edad" class="form-label fw-bold" style="font-size:0.9em;">Edad</label>
                                            <div class="input-group ">
                                                <span class="input-group-text shadow-sm"><i class="bi bi-cake"></i></span>
                                                <input class="form-control" name="con_edad" id="con_edad" value="<?php echo $con_edad; ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-4 p-1 px-2 my-1">
                                            <label for="con_barrio" class="form-label fw-bold" style="font-size:0.9em;">Barrio</label>
                                            <div class="input-group ">
                                                <span class="input-group-text shadow-sm"><i class="bi bi-houses"></i></span>
                                                <input class="form-control" name="con_barrio" id="con_barrio" value="<?php echo $con_barrio; ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-4 p-1 px-2 my-1">
                                            <label for="con_ciudad" class="form-label fw-bold" style="font-size:0.9em;">Ciudad</label>
                                            <div class="input-group ">
                                                <span class="input-group-text shadow-sm"><i class="bi bi-houses"></i></span>
                                                <input class="form-control" name="con_ciudad" id="con_ciudad" value="<?php echo $con_ciudad; ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-4 p-1 px-2 my-1">
                                            <label for="con_FechaExpe" class="form-label fw-bold" style="font-size:0.8em;">Fecha de Expedición del Documento</label>
                                            <div class="input-group ">
                                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar2-event"></i></span>
                                                <input class="form-control" name="con_FechaExpe" id="con_FechaExpe" value="<?php echo $con_FechaExpe; ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-4 p-1 px-2 my-1">
                                            <label for="con_lugarE" class="form-label fw-bold" style="font-size:0.8em;">Lugar de expedición del documento</label>
                                            <div class="input-group ">
                                                <span class="input-group-text shadow-sm"><i class="bi bi-geo"></i></span>
                                                <input class="form-control" name="con_lugarE" id="con_lugarE" value="<?php echo $con_lugarE; ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-4 p-1 px-2 my-1">
                                            <label for="con_fecha_nacimiento" class="form-label fw-bold" style="font-size:0.9em;">Fecha de nacimiento</label>
                                            <div class="input-group ">
                                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar-date"></i></span>
                                                <input class="form-control" name="con_fecha_nacimiento" id="con_fecha_nacimiento" value="<?php echo $con_fecha_nacimiento; ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-4 p-1 px-2 my-1">
                                            <label for="con_lugar_nacimiento" class="form-label fw-bold" style="font-size:0.9em;">Lugar de nacimiento</label>
                                            <div class="input-group ">
                                                <span class="input-group-text shadow-sm"><i class="bi bi-pin-map-fill"></i></span>
                                                <input class="form-control" name="con_lugar_nacimiento" id="con_lugar_nacimiento" value="<?php echo $con_lugar_nacimiento; ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-4 p-1 px-2 my-1">
                                            <label for="con_tel_fijo" class="form-label fw-bold" style="font-size:0.9em;">Teléfono fijo</label>
                                            <div class="input-group ">
                                                <span class="input-group-text shadow-sm"><i class="bi bi-telephone"></i></span>
                                                <input class="form-control" name="con_tel_fijo" id="con_tel_fijo" value="<?php echo $con_tel_fijo; ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-6 p-1 px-2 my-1">
                                            <label for="con_correo" class="form-label fw-bold" style="font-size:0.9em;">Correo electrónico personal</label>
                                            <div class="input-group shadow-sm">
                                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at"></i></span>
                                                <input class="form-control" name="con_correo" id="con_correo" value="<?php echo $con_correo; ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mx-auto p-1 px-2 my-1">
                                            <label for="con_correo_corporativo" class="form-label fw-bold" style="font-size:0.9em;">Correo electrónico corporativo</label>
                                            <div class="input-group shadow-sm">
                                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at"></i></span>
                                                <input class="form-control" name="con_correo_corporativo" id="con_correo_corporativo" value="<?php echo $con_correo_corporativo; ?>" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- <div class="col-md-6">
                            <label class="form-label" for="con_cedula">Cédula de Ciudadanía</label>
                            <input class="form-control mb-2" id="con_cedula" value="<?php echo $row['con_cedula'] ?>" readonly>
                            <br>
                            <?php if ($row['con_cedula'] != null) {
                                list($rutaWeb, $rutaFisica) = ajustarRutaDocumento($row['con_cedula']);
                                if ($rutaWeb && file_exists($rutaFisica)) {
                                    echo "<iframe src='" . htmlspecialchars($rutaWeb, ENT_QUOTES, 'UTF-8') . "' id='con_cedula' style='width: 100%; height: 500px;' frameborder='0'></iframe>";
                                } else {
                                    echo "<p>El archivo no existe.</p>";
                                }
                            }
                            ?>
                        </div> -->

                        <div class="col-12  my-4" style="border-bottom:2px dashed #002f557a"></div>
                        <div class="col-12 mt-0">
                            <h6 class="fw-bold p-2 text-white text-center w-25 rounded-3" style="background-color: #002F55;">Documentos Adjuntos</h6>
                        </div>
                        <div class="col-12 col-lg-4 p-1 px-2 my-4">
                            <label class="form-label fw-bold mb-1" style="font-size:0.9em">
                                Cédula de ciudadanía
                            </label>
                            <?php if (!empty($cedula)): ?>
                                <?php
                                list($rutaWeb, $rutaFisica) = ajustarRutaDocumento($cedula);
                                ?>
                                <?php if ($rutaWeb && file_exists($rutaFisica)): ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </span>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($cedula); ?>" readonly>
                                    </div>
                                    <div class="d-flex justify-content-center gap-2 mt-2">
                                        <button type="button"
                                            class="bot_mostrar_vista btn btn-sm"
                                            onclick="abrirModalInforme('<?php echo htmlspecialchars($rutaWeb, ENT_QUOTES, 'UTF-8'); ?>')">
                                            <i class="bi bi-eye"></i> Vista previa
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text bg-warning border border-warning">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </span>
                                        <input type="text" class="form-control border border-warning text-warning" value="El archivo está registrado, pero no se encuentra en el servidor." readonly>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="input-group shadow-sm rounded-3">
                                    <span class="input-group-text bg-danger border border-danger">
                                        <i class="bi bi-file-earmark-x text-white"></i>
                                    </span>
                                    <input type="text" class="form-control border border-danger text-danger" value="No se ha cargado ningún documento" readonly>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-4 p-1 px-2 my-4">
                            <label class="form-label fw-bold mb-1" style="font-size:0.9em">
                                RUT
                            </label>
                            <?php if (!empty($rut)): ?>
                                <?php
                                list($rutaWeb, $rutaFisica) = ajustarRutaDocumento($rut);
                                ?>
                                <?php if ($rutaWeb && file_exists($rutaFisica)): ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </span>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($rut); ?>" readonly>
                                    </div>
                                    <div class="d-flex justify-content-center gap-2 mt-2">
                                        <button type="button"
                                            class="bot_mostrar_vista btn btn-sm"
                                            onclick="abrirModalInforme('<?php echo htmlspecialchars($rutaWeb, ENT_QUOTES, 'UTF-8'); ?>')">
                                            <i class="bi bi-eye"></i> Vista previa
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text bg-warning border border-warning">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </span>
                                        <input type="text" class="form-control border border-warning text-warning" value="El archivo está registrado, pero no se encuentra en el servidor." readonly>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="input-group shadow-sm rounded-3">
                                    <span class="input-group-text bg-danger border border-danger">
                                        <i class="bi bi-file-earmark-x text-white"></i>
                                    </span>
                                    <input type="text" class="form-control border border-danger text-danger" value="No se ha cargado ningún documento" readonly>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-4 p-1 px-2 my-4">
                            <label class="form-label fw-bold mb-1" style="font-size:0.9em">
                                Antecedentes Judiciales
                            </label>
                            <?php if (!empty($judiciales)): ?>
                                <?php
                                list($rutaWeb, $rutaFisica) = ajustarRutaDocumento($judiciales);
                                ?>
                                <?php if ($rutaWeb && file_exists($rutaFisica)): ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text">
                                            <i class="bi bi-file-earmark-pdf "></i>
                                        </span>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($judiciales); ?>" readonly>
                                    </div>
                                    <div class="d-flex justify-content-center gap-2 mt-2">
                                        <button type="button"
                                            class="bot_mostrar_vista btn btn-sm"
                                            onclick="abrirModalInforme('<?php echo htmlspecialchars($rutaWeb, ENT_QUOTES, 'UTF-8'); ?>')">
                                            <i class="bi bi-eye"></i> Vista previa
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text bg-warning border border-warning">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </span>
                                        <input type="text" class="form-control border border-warning text-warning" value="El archivo está registrado, pero no se encuentra en el servidor." readonly>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="input-group shadow-sm rounded-3">
                                    <span class="input-group-text bg-danger border border-danger">
                                        <i class="bi bi-file-earmark-x text-white"></i>
                                    </span>
                                    <input type="text" class="form-control border border-danger text-danger" value="No se ha cargado ningún documento" readonly>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-4 p-1 px-2 my-4">
                            <label class="form-label fw-bold mb-1" style="font-size:0.9em">
                                Antecedentes de la Contraloria
                            </label>
                            <?php if (!empty($contraloria)): ?>
                                <?php
                                list($rutaWeb, $rutaFisica) = ajustarRutaDocumento($contraloria);
                                ?>
                                <?php if ($rutaWeb && file_exists($rutaFisica)): ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text">
                                            <i class="bi bi-file-earmark-pdf "></i>
                                        </span>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($contraloria); ?>" readonly>
                                    </div>
                                    <div class="d-flex justify-content-center gap-2 mt-2">
                                        <button type="button"
                                            class="bot_mostrar_vista btn btn-sm"
                                            onclick="abrirModalInforme('<?php echo htmlspecialchars($rutaWeb, ENT_QUOTES, 'UTF-8'); ?>')">
                                            <i class="bi bi-eye"></i> Vista previa
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text bg-warning border border-warning">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </span>
                                        <input type="text" class="form-control border border-warning text-warning" value="El archivo está registrado, pero no se encuentra en el servidor." readonly>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="input-group shadow-sm rounded-3">
                                    <span class="input-group-text bg-danger border border-danger">
                                        <i class="bi bi-file-earmark-x text-white"></i>
                                    </span>
                                    <input type="text" class="form-control border border-danger text-danger" value="No se ha cargado ningún documento" readonly>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-4 p-1 px-2 my-4">
                            <label class="form-label fw-bold mb-1" style="font-size:0.9em">
                                Antecedentes de la Procuraduria
                            </label>
                            <?php if (!empty($procuraduria)): ?>
                                <?php
                                list($rutaWeb, $rutaFisica) = ajustarRutaDocumento($procuraduria);
                                ?>
                                <?php if ($rutaWeb && file_exists($rutaFisica)): ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text">
                                            <i class="bi bi-file-earmark-pdf "></i>
                                        </span>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($procuraduria); ?>" readonly>
                                    </div>
                                    <div class="d-flex justify-content-center gap-2 mt-2">
                                        <button type="button"
                                            class="bot_mostrar_vista btn btn-sm"
                                            onclick="abrirModalInforme('<?php echo htmlspecialchars($rutaWeb, ENT_QUOTES, 'UTF-8'); ?>')">
                                            <i class="bi bi-eye"></i> Vista previa
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text bg-warning border border-warning">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </span>
                                        <input type="text" class="form-control border border-warning text-warning" value="El archivo está registrado, pero no se encuentra en el servidor." readonly>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="input-group shadow-sm rounded-3">
                                    <span class="input-group-text bg-danger border border-danger">
                                        <i class="bi bi-file-earmark-x text-white"></i>
                                    </span>
                                    <input type="text" class="form-control border border-danger text-danger" value="No se ha cargado ningún documento" readonly>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-4 p-1 px-2 my-4">
                            <label class="form-label fw-bold mb-1" style="font-size:0.9em">
                                Hoja de vida
                            </label>
                            <?php if (!empty($hojavida)): ?>
                                <?php
                                list($rutaWeb, $rutaFisica) = ajustarRutaDocumento($hojavida);
                                ?>
                                <?php if ($rutaWeb && file_exists($rutaFisica)): ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text">
                                            <i class="bi bi-file-earmark-pdf "></i>
                                        </span>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($hojavida); ?>" readonly>
                                    </div>
                                    <div class="d-flex justify-content-center gap-2 mt-2">
                                        <button type="button"
                                            class="bot_mostrar_vista btn btn-sm"
                                            onclick="abrirModalInforme('<?php echo htmlspecialchars($rutaWeb, ENT_QUOTES, 'UTF-8'); ?>')">
                                            <i class="bi bi-eye"></i> Vista previa
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text bg-warning border border-warning">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </span>
                                        <input type="text" class="form-control border border-warning text-warning" value="El archivo está registrado, pero no se encuentra en el servidor." readonly>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="input-group shadow-sm rounded-3">
                                    <span class="input-group-text bg-danger border border-danger">
                                        <i class="bi bi-file-earmark-x text-white"></i>
                                    </span>
                                    <input type="text" class="form-control border border-danger text-danger" value="No se ha cargado ningún documento" readonly>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <script>
                        function abrirModalInforme(rutaPDF) {
                            const iframe = document.getElementById("iframePDF");
                            iframe.src = rutaPDF;

                            const modal = new bootstrap.Modal(document.getElementById("modalPDF"));
                            modal.show();
                        }
                    </script>

                    <div class="mt-4 d-flex justify-content-center">
                        <button type="button" class="btn text-white" style="background-color: #002F55;" onclick="nextSection()">
                            Siguiente <i class="bi bi-arrow-right ms-2"></i> </button>
                    </div>
                </div>
                <div class="form-section d-none " id="section2">
                    <div class="mb-3  d-flex align-items-center text-start rounded-4 card-header shadow  p-3 text-white">
                        <i class="bi bi-people-fill me-3 fs-2"></i>
                        <div>
                            <h3 class="h5 mb-1">Información familiar</h3>
                            <p class=" mb-0" style="font-size: 0.85rem; color:#999999">
                                Información básica del colaborador.
                            </p>
                        </div>
                    </div>
                    <div class="row px-4 card-especial-tres">
                        <div class="col-md-4 p-1 px-2 my-1">
                            <label for="con_nombres" class="form-label fw-bold" style="font-size:0.9em;">Estado Civil</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-heart"></i></span>
                                <input class="form-control" name="con_estado_civil" id="con_estado_civil" value="<?php echo $con_estado_civil; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_nombre_conyuge" class="form-label fw-bold" style="font-size:0.9em;">Nombre del cónyuge</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm" id="basic-addon1"><i class="bi-people"></i></span>
                                <input class="form-control" name="con_nombre_conyuge" id="con_nombre_conyuge" value="<?php echo $con_nombre_conyuge; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_enfermedades" class="form-label fw-bold" style="font-size:0.9em;">Enfermedades</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm" id="basic-addon1"><i class="bi bi-bandaid"></i></span>
                                <input class="form-control" name="con_enfermedades" id="con_enfermedades" value="<?php echo $con_enfermedades; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_alergias" class="form-label fw-bold" style="font-size:0.9em;">Alergias</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm" id="basic-addon1"><i class="bi bi-bandaid"></i></span>
                                <input class="form-control" name="con_alergias" id="con_alergias" value="<?php echo $con_alergias; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_medicamentos" class="form-label fw-bold" style="font-size:0.9em;">Medicamentos</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm" id="basic-addon1"><i class="bi bi-prescription2"></i></span>
                                <input class="form-control" name="con_medicamentos" id="con_medicamentos" value="<?php echo $con_medicamentos; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_contingencia" class="form-label fw-bold" style="font-size:0.9em;">Plan de contingencia</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm" id="basic-addon1"><i class="bi bi-prescription2"></i></span>
                                <input class="form-control" name="con_contingencia" id="con_contingencia" value="<?php echo $con_contingencia; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12  my-4" style="border-bottom:2px dashed #002f557a"></div>

                        <div class="col-12">
                            <h6 class="fw-bold p-2 text-white w-50 rounded-3 text-start px-3" style="background-color: #002F55;">
                                <i class="bi bi-hospital text-white me-2"></i> información de contacto de emergencia
                            </h6>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_emergencia" class="form-label fw-bold" style="font-size:0.9em;">Contacto de emergencia</label>
                            <div class="input-group shadow">
                                <span class="input-group-text shadow-sm" style="border: 1px solid #002F55;" id="basic-addon1"><i class="bi bi-person-exclamation"></i></span>
                                <input class="form-control" style="border: 1px solid #002F55;" name="con_emergencia" id="con_emergencia" value="<?php echo $con_emergencia; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_parentesco" class="form-label fw-bold" style="font-size:0.9em;">Parentesco </label>
                            <div class="input-group shadow">
                                <span class="input-group-text shadow-sm " style="border: 1px solid #002F55;" id="basic-addon1"><i class="bi bi-person-badge"></i></span>
                                <input class="form-control" style="border: 1px solid #002F55;" name="con_parentesco" id="con_parentesco" value="<?php echo $con_parentesco; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_tel_emergencia" class="form-label fw-bold" style="font-size:0.9em;">Número telefónico de contacto de emergencia </label>
                            <div class="input-group shadow ">
                                <span class="input-group-text shadow-sm" style="border: 1px solid #002F55;" id="basic-addon1"><i class="bi bi-telephone-x"></i></span>
                                <input class="form-control" style="border: 1px solid #002F55;" name="con_tel_emergencia" id="con_tel_emergencia" value="<?php echo $con_tel_emergencia; ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-between  ">
                        <button type="button" class="btn btn-secondary text-white" style="background-color: rgba(112, 113, 114, 1);" onclick="prevSection()"
                            style="margin-right: 1cm;"> <i class="bi bi-arrow-left me-2"></i> Atrás</button>

                        <button type="button" class="btn  text-white" style="background-color: #002F55;" onclick="nextSection()">
                            Siguiente <i class="bi bi-arrow-right ms-2"></i></button>
                    </div>
                </div>

                <!-- Sección 3: Sección datos financieros -->
                <div class="form-section d-none " id="section3">

                    <div class="mb-3  d-flex align-items-center text-start rounded-4 card-header shadow  p-3 text-white">
                        <i class="bi bi-coin me-3 fs-2"></i>
                        <div>
                            <h3 class="h5 mb-1">Información Bancaria</h3>
                            <p class=" mb-0" style="font-size: 0.85rem; color:#999999">
                                Información del colaborador.
                            </p>
                        </div>
                    </div>

                    <div class="row p-4 g-3 card-especial-tres">
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_num_cuenta" class="form-label fw-bold" style="font-size:0.9em;">Nº Cuenta Bancaria</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-bank"></i></span>
                                <input class="form-control" name="con_num_cuenta" id="con_num_cuenta" value="<?php echo $con_num_cuenta; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_num_cuenta" class="form-label fw-bold" style="font-size:0.9em;">Tipo de cuenta bancaria</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-safe"></i></span>
                                <input class="form-control" name="con_tipo_cuenta" id="con_tipo_cuenta" value="<?php echo $con_tipo_cuenta; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_num_cuenta" class="form-label fw-bold" style="font-size:0.9em;">Entidad Financiera</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-bank2"></i></span>
                                <input class="form-control" name="con_financiera" id="con_financiera" value="<?php echo $con_financiera; ?>" readonly>
                            </div>
                        </div>

                        <div class="mb-3  d-flex align-items-center text-start rounded-4 card-header shadow  p-3 text-white">
                            <i class="bi bi-shield-check me-3 fs-2"></i>
                            <div>
                                <h3 class="h5 mb-1">Información la seguridad social</h3>
                                <p class=" mb-0" style="font-size: 0.85rem; color:#999999">
                                    Información del colaborador.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_num_cuenta" class="form-label fw-bold" style="font-size:0.9em;">EPS</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-hospital"></i></span>
                                <input class="form-control" name="con_eps" id="con_eps" value="<?php echo $con_eps; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_num_cuenta" class="form-label fw-bold" style="font-size:0.9em;">AFP</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-clipboard"></i></span>
                                <input class="form-control" name="con_afp" id="con_afp" value="<?php echo $con_afp; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_num_cuenta" class="form-label fw-bold" style="font-size:0.9em;">ARL</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-cone-striped"></i></span>
                                <input class="form-control" name="con_arl" id="con_arl" value="<?php echo $con_arl; ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="col-12  my-4" style="border-bottom:2px dashed #002f557a"></div>

                    <div class="col-12 mt-0">
                        <h6 class="fw-bold p-2 text-white text-center w-25 rounded-3" style="background-color: #002F55;">Documentos adjuntos</h6>
                    </div>
                    <div class="row p-4 g-3 ">
                        <?php
                        $banco = $row['con_bancario'] ?? null;
                        $cer_eps = $row['con_certificado_eps'] ?? null;
                        $cer_arl = $row['con_arlCer'] ?? null;
                        ?>
                        <div class="col-12 col-lg-4 p-1 px-2 my-4">
                            <label class="form-label fw-bold mb-1" style="font-size:0.9em">
                                Certificado Bancario
                            </label>
                            <?php if (!empty($banco)): ?>
                                <?php
                                list($rutaWeb, $rutaFisica) = ajustarRutaDocumento($banco);
                                ?>
                                <?php if ($rutaWeb && file_exists($rutaFisica)): ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </span>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($banco); ?>" readonly>
                                    </div>

                                    <div class="d-flex justify-content-center gap-2 mt-2">
                                        <button type="button"
                                            class="bot_mostrar_vista btn btn-sm"
                                            onclick="abrirModalInforme('<?php echo htmlspecialchars($rutaWeb, ENT_QUOTES, 'UTF-8'); ?>')">
                                            <i class="bi bi-eye"></i> Vista previa
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text bg-warning border border-warning">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </span>
                                        <input type="text" class="form-control border border-warning text-warning" value="El archivo está registrado, pero no se encuentra en el servidor." readonly>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="input-group shadow-sm rounded-3">
                                    <span class="input-group-text bg-danger border border-danger">
                                        <i class="bi bi-file-earmark-x text-white"></i>
                                    </span>
                                    <input type="text" class="form-control border border-danger text-danger" value="No se ha cargado ningún documento" readonly>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-4 p-1 px-2 my-4">
                            <label class="form-label fw-bold mb-1" style="font-size:0.9em">
                                Certificado EPS
                            </label>
                            <?php if (!empty($cer_eps)): ?>
                                <?php
                                list($rutaWeb, $rutaFisica) = ajustarRutaDocumento($cer_eps);
                                ?>
                                <?php if ($rutaWeb && file_exists($rutaFisica)): ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </span>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($cer_eps); ?>" readonly>
                                    </div>

                                    <div class="d-flex justify-content-center gap-2 mt-2">
                                        <button type="button"
                                            class="bot_mostrar_vista btn btn-sm"
                                            onclick="abrirModalInforme('<?php echo htmlspecialchars($rutaWeb, ENT_QUOTES, 'UTF-8'); ?>')">
                                            <i class="bi bi-eye"></i> Vista previa
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text bg-warning border border-warning">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </span>
                                        <input type="text" class="form-control border border-warning text-warning" value="El archivo está registrado, pero no se encuentra en el servidor." readonly>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="input-group shadow-sm rounded-3">
                                    <span class="input-group-text bg-danger border border-danger">
                                        <i class="bi bi-file-earmark-x text-white"></i>
                                    </span>
                                    <input type="text" class="form-control border border-danger text-danger" value="No se ha cargado ningún documento" readonly>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-lg-4 p-1 px-2 my-4">
                            <label class="form-label fw-bold mb-1" style="font-size:0.9em">
                                Certificado ARL
                            </label>
                            <?php if (!empty($cer_arl)): ?>
                                <?php
                                list($rutaWeb, $rutaFisica) = ajustarRutaDocumento($cer_arl);
                                ?>
                                <?php if ($rutaWeb && file_exists($rutaFisica)): ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </span>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($cer_arl); ?>" readonly>
                                    </div>

                                    <div class="d-flex justify-content-center gap-2 mt-2">
                                        <button type="button"
                                            class="bot_mostrar_vista btn btn-sm"
                                            onclick="abrirModalInforme('<?php echo htmlspecialchars($rutaWeb, ENT_QUOTES, 'UTF-8'); ?>')">
                                            <i class="bi bi-eye"></i> Vista previa
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text bg-warning border border-warning">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </span>
                                        <input type="text" class="form-control border border-warning text-warning" value="El archivo está registrado, pero no se encuentra en el servidor." readonly>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="input-group shadow-sm rounded-3">
                                    <span class="input-group-text bg-danger border border-danger">
                                        <i class="bi bi-file-earmark-x text-white"></i>
                                    </span>
                                    <input type="text" class="form-control border border-danger text-danger" value="No se ha cargado ningún documento" readonly>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-between  ">
                        <button type="button" class="btn btn-secondary text-white" style="background-color: rgba(112, 113, 114, 1);" onclick="prevSection()"
                            style="margin-right: 1cm;"> <i class="bi bi-arrow-left me-2"></i> Atrás</button>
                        <button type="button" class="btn  text-white" style="background-color: #002F55;" onclick="nextSection()">
                            Siguiente <i class="bi bi-arrow-right ms-2"></i></button>
                    </div>
                </div>
                <div class="form-section d-none" id="section4">
                    <div class="mb-3  d-flex align-items-center text-start rounded-4 card-header shadow  p-3 text-white">
                        <i class="bi bi-mortarboard me-3 fs-2"></i>
                        <div>
                            <h3 class="h5 mb-1">Historial académico</h3>
                            <p class=" mb-0" style="font-size: 0.85rem; color:#999999">
                                Información académica del colaborador
                            </p>
                        </div>
                    </div>
                    <div class="row px-4 card-especial-tres">
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_profesion" class="form-label fw-bold" style="font-size:0.9em;">Profesión</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-raised-hand"></i></span>
                                <input class="form-control" name="con_profesion" id="con_profesion" value="<?php echo $con_profesion; ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_profesion" class="form-label fw-bold" style="font-size:0.9em;">Escolaridad</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-backpack2"></i></span>
                                <input class="form-control" name="con_escolaridad" id="con_escolaridad" value="<?php echo $con_escolaridad; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_profesion" class="form-label fw-bold" style="font-size:0.9em;">Fecha de graduación</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar2-event"></i></span>
                                <input class="form-control" name="con_grado" id="con_grado" value="<?php echo $con_grado; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_num_tarjeta" class="form-label fw-bold" style="font-size:0.9em;">N° Tarjeta Profesional</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-vcard"></i></span>
                                <input class="form-control" name="con_num_tarjeta" id="con_num_tarjeta" value="<?php echo $con_num_tarjeta; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_expedicion" class="form-label fw-bold" style="font-size:0.9em;">Fecha de Expedición de Tarjeta Profesional</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar-date"></i></span>
                                <input class="form-control" name="con_expedicion" id="con_expedicion" value="<?php echo $con_expedicion; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12  my-4" style="border-bottom:2px dashed #002f557a"></div>

                        <div class="col-12 mt-0">
                            <h6 class="fw-bold p-2 text-white text-center w-25 rounded-3" style="background-color: #002F55;">Documentos Adjuntos</h6>
                        </div>

                        <?php
                        $cer_tar_profe = $row['con_cerTarjeta'] ?? null;
                        $tar_profe = $row['con_tarjeta'] ?? null;
                        ?>
                        <div class="col-12 col-lg-6 p-1 px-2 my-4">
                            <label class="form-label fw-bold mb-1" style="font-size:0.9em">
                                Certificado de tarjeta profesional
                            </label>
                            <?php if (!empty($cer_tar_profe)): ?>
                                <?php
                                list($rutaWeb, $rutaFisica) = ajustarRutaDocumento($cer_tar_profe);
                                ?>
                                <?php if ($rutaWeb && file_exists($rutaFisica)): ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </span>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($cer_tar_profe); ?>" readonly>
                                    </div>

                                    <div class="d-flex justify-content-center gap-2 mt-2">
                                        <button type="button"
                                            class="bot_mostrar_vista btn btn-sm"
                                            onclick="abrirModalInforme('<?php echo htmlspecialchars($rutaWeb, ENT_QUOTES, 'UTF-8'); ?>')">
                                            <i class="bi bi-eye"></i> Vista previa
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text bg-warning border border-warning">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </span>
                                        <input type="text" class="form-control border border-warning text-warning" value="El archivo está registrado, pero no se encuentra en el servidor." readonly>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="input-group shadow-sm rounded-3">
                                    <span class="input-group-text bg-danger border border-danger">
                                        <i class="bi bi-file-earmark-x text-white"></i>
                                    </span>
                                    <input type="text" class="form-control border border-danger text-danger" value="No se ha cargado ningún documento" readonly>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-6 p-1 px-2 my-4">
                            <label class="form-label fw-bold mb-1" style="font-size:0.9em">
                                Tarjeta profesional
                            </label>
                            <?php if (!empty($tar_profe)): ?>
                                <?php
                                list($rutaWeb, $rutaFisica) = ajustarRutaDocumento($tar_profe);
                                ?>
                                <?php if ($rutaWeb && file_exists($rutaFisica)): ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </span>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($tar_profe); ?>" readonly>
                                    </div>

                                    <div class="d-flex justify-content-center gap-2 mt-2">
                                        <button type="button"
                                            class="bot_mostrar_vista btn btn-sm"
                                            onclick="abrirModalInforme('<?php echo htmlspecialchars($rutaWeb, ENT_QUOTES, 'UTF-8'); ?>')">
                                            <i class="bi bi-eye"></i> Vista previa
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text bg-warning border border-warning">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </span>
                                        <input type="text" class="form-control border border-warning text-warning" value="El archivo está registrado, pero no se encuentra en el servidor." readonly>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="input-group shadow-sm rounded-3">
                                    <span class="input-group-text bg-danger border border-danger">
                                        <i class="bi bi-file-earmark-x text-white"></i>
                                    </span>
                                    <input type="text" class="form-control border border-danger text-danger" value="No se ha cargado ningún documento" readonly>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-between  ">
                        <button type="button" class="btn btn-secondary text-white" style="background-color: rgba(112, 113, 114, 1);" onclick="prevSection()"
                            style="margin-right: 1cm;"> <i class="bi bi-arrow-left me-2"></i> Atrás</button>
                        <button type="button" class="btn  text-white" style="background-color: #002F55;" onclick="nextSection()">
                            Siguiente <i class="bi bi-arrow-right ms-2"></i></button>
                    </div>
                </div>

                <div class="form-section d-none" id="section5">
                    <div class="mb-3  d-flex align-items-center text-start rounded-4 card-header shadow  p-3 text-white">
                        <i class="bi bi-file-earmark-break me-3 fs-2"></i>
                        <div>
                            <h3 class="h5 mb-1">Vinculación Contractual</h3>
                            <p class=" mb-0" style="font-size: 0.85rem; color:#999999">
                                Información sobre la contratación del colaborador.
                            </p>
                        </div>
                    </div>
                    <div class="row p-4 g-3 card-especial-tres">
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_expedicion" class="form-label fw-bold" style="font-size:0.9em;">Sede de trabajo</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-building"></i></span>
                                <input class="form-control" name="con_sede" id="con_sede" value="<?php echo $con_sede; ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_expedicion" class="form-label fw-bold" style="font-size:0.9em;">Presencialidad</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-building"></i></span>
                                <input class="form-control" name="con_presencialidad" id="con_presencialidad" value="<?php echo $con_presencialidad; ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_expedicion" class="form-label fw-bold" style="font-size:0.9em;">Cargo / Rol</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-file-person-fill"></i></span>
                                <input class="form-control" name="con_cargo" id="con_cargo" value="<?php echo $con_cargo; ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_expedicion" class="form-label fw-bold" style="font-size:0.9em;">Proyecto</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-bezier"></i></span>
                                <input class="form-control" name="con_proyecto" id="con_proyecto" value="<?php echo $con_proyecto; ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_expedicion" class="form-label fw-bold" style="font-size:0.9em;">Tipo de contrato</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-bezier"></i></span>
                                <input class="form-control" name="con_tipo_contrato" id="con_tipo_contrato" value="<?php echo $con_tipo_contrato; ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_fecha_inicio" class="form-label fw-bold" style="font-size:0.9em;">Fecha de Contrato</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar-date"></i></span>
                                <input class="form-control" name="con_fecha_inicio" id="con_fecha_inicio" value="<?php echo $con_fecha_inicio; ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_fecha_final" class="form-label fw-bold" style="font-size:0.9em;">Fecha de finalización de contrato</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar-date"></i></span>
                                <input class="form-control" name="con_fecha_final" id="con_fecha_final" value="<?php echo $con_fecha_final; ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-4 mt-2 px-2 my-2">
                            <label for="con_duracion" class="form-label fw-bold" style="font-size:0.9em;">Duración de contrato</label>
                            <div class="input-group">
                                <span class="input-group-text shadow-sm "><i class="bi bi-calendar-date"></i></span>
                                <input class="form-control" name="con_duracion" id="con_duracion" value="<?php echo $con_duracion; ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_salario_mostrado" class="form-label fw-bold" style="font-size:0.9em;">Salario/Honorarios</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-piggy-bank"></i></span>
                                <input class="form-control" name="con_salario" id="con_salario"
                                    value="<?php echo $con_salario; ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_jefe" class="form-label fw-bold" style="font-size:0.9em;">Jefe Inmediato / Supervisor</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-gear"></i></span>
                                <input class="form-control" name="con_jefe" id="con_jefe" value="<?php echo $con_jefe; ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_per_cargo" class="form-label fw-bold" style="font-size:0.9em;">Nº Personas a Cargo</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-gear"></i></span>
                                <input class="form-control" name="con_per_cargo" id="con_per_cargo" value="<?php echo $con_per_cargo; ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_valor_proyecto_mostrado" class="form-label fw-bold" style="font-size:0.9em;">Valor Total Proyecto</label>
                            <div class="input-group shadow-sm shadow-warning">
                                <span class="input-group-text shadow-sm bg-warning border border-warning"><i class="bi bi-cash-coin"></i></span>
                                <input class="form-control border border-warning" name="con_valor_proyecto" id="con_valor_proyecto" value="<?php echo $con_valor_proyecto; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12  my-4" style="border-bottom:2px dashed #002f557a"></div>

                        <div class="col-12">
                            <h6 class="fw-bold p-2 text-white text-center w-25 rounded-3" style="background-color: #002F55;">Documentos Adjuntos</h6>
                        </div>
                        <?php
                        $contrato = $row['con_contrato'] ?? null;
                        $ex_ingreso = $row['con_examenes'] ?? null;
                        $poliza = $row['con_cumplimiento'] ?? null;
                        $acta_inicio = $row['con_acta'] ?? null;
                        $acta_final = $row['con_actaFi'] ?? null;
                        ?>
                        <div class="col-12 col-lg-4 p-1 px-2 my-4">
                            <label class="form-label fw-bold mb-1" style="font-size:0.9em">
                                Contrato
                            </label>
                            <?php if (!empty($contrato)): ?>
                                <?php
                                list($rutaWeb, $rutaFisica) = ajustarRutaDocumento($contrato);
                                ?>
                                <?php if ($rutaWeb && file_exists($rutaFisica)): ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </span>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($contrato); ?>" readonly>
                                    </div>
                                    <div class="d-flex justify-content-center gap-2 mt-2">
                                        <button type="button"
                                            class="bot_mostrar_vista btn btn-sm"
                                            onclick="abrirModalInforme('<?php echo htmlspecialchars($rutaWeb, ENT_QUOTES, 'UTF-8'); ?>')">
                                            <i class="bi bi-eye"></i> Vista previa
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text bg-warning border border-warning">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </span>
                                        <input type="text" class="form-control border border-warning text-warning" value="El archivo está registrado, pero no se encuentra en el servidor." readonly>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="input-group shadow-sm rounded-3">
                                    <span class="input-group-text bg-danger border border-danger">
                                        <i class="bi bi-file-earmark-x text-white"></i>
                                    </span>
                                    <input type="text" class="form-control border border-danger text-danger" value="No se ha cargado ningún documento" readonly>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-4 p-1 px-2 my-4">
                            <label class="form-label fw-bold mb-1" style="font-size:0.9em">
                                Exámenes de ingreso
                            </label>
                            <?php if (!empty($ex_ingreso)): ?>
                                <?php
                                list($rutaWeb, $rutaFisica) = ajustarRutaDocumento($ex_ingreso);
                                ?>
                                <?php if ($rutaWeb && file_exists($rutaFisica)): ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </span>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($ex_ingreso); ?>" readonly>
                                    </div>
                                    <div class="d-flex justify-content-center gap-2 mt-2">
                                        <button type="button"
                                            class="bot_mostrar_vista btn btn-sm"
                                            onclick="abrirModalInforme('<?php echo htmlspecialchars($rutaWeb, ENT_QUOTES, 'UTF-8'); ?>')">
                                            <i class="bi bi-eye"></i> Vista previa
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text bg-warning border border-warning">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </span>
                                        <input type="text" class="form-control border border-warning text-warning" value="El archivo está registrado, pero no se encuentra en el servidor." readonly>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="input-group shadow-sm rounded-3">
                                    <span class="input-group-text bg-danger border border-danger">
                                        <i class="bi bi-file-earmark-x text-white"></i>
                                    </span>
                                    <input type="text" class="form-control border border-danger text-danger" value="No se ha cargado ningún documento" readonly>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-4 p-1 px-2 my-4">
                            <label class="form-label fw-bold mb-1" style="font-size:0.9em">
                                Póliza de cumplimiento
                            </label>
                            <?php if (!empty($poliza)): ?>
                                <?php
                                list($rutaWeb, $rutaFisica) = ajustarRutaDocumento($poliza);
                                ?>
                                <?php if ($rutaWeb && file_exists($rutaFisica)): ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </span>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($poliza); ?>" readonly>
                                    </div>

                                    <div class="d-flex justify-content-center gap-2 mt-2">
                                        <button type="button"
                                            class="bot_mostrar_vista btn btn-sm"
                                            onclick="abrirModalInforme('<?php echo htmlspecialchars($rutaWeb, ENT_QUOTES, 'UTF-8'); ?>')">
                                            <i class="bi bi-eye"></i> Vista previa
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text bg-warning border border-warning">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </span>
                                        <input type="text" class="form-control border border-warning text-warning" value="El archivo está registrado, pero no se encuentra en el servidor." readonly>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="input-group shadow-sm rounded-3">
                                    <span class="input-group-text bg-danger border border-danger">
                                        <i class="bi bi-file-earmark-x text-white"></i>
                                    </span>
                                    <input type="text" class="form-control border border-danger text-danger" value="No se ha cargado ningún documento" readonly>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-lg-4 p-1 px-2 my-4">
                            <label class="form-label fw-bold mb-1" style="font-size:0.9em">
                                Documento de acta inicial
                            </label>
                            <?php if (!empty($acta_inicio)): ?>
                                <?php
                                list($rutaWeb, $rutaFisica) = ajustarRutaDocumento($acta_inicio);
                                ?>
                                <?php if ($rutaWeb && file_exists($rutaFisica)): ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </span>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($acta_inicio); ?>" readonly>
                                    </div>
                                    <div class="d-flex justify-content-center gap-2 mt-2">
                                        <button type="button"
                                            class="bot_mostrar_vista btn btn-sm"
                                            onclick="abrirModalInforme('<?php echo htmlspecialchars($rutaWeb, ENT_QUOTES, 'UTF-8'); ?>')">
                                            <i class="bi bi-eye"></i> Vista previa
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text bg-warning border border-warning">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </span>
                                        <input type="text" class="form-control border border-warning text-warning" value="El archivo está registrado, pero no se encuentra en el servidor." readonly>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="input-group shadow-sm rounded-3">
                                    <span class="input-group-text bg-danger border border-danger">
                                        <i class="bi bi-file-earmark-x text-white"></i>
                                    </span>
                                    <input type="text" class="form-control border border-danger text-danger" value="No se ha cargado ningún documento" readonly>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-4 p-1 px-2 my-4">
                            <label class="form-label fw-bold mb-1" style="font-size:0.9em">
                                Documento de acta de finalización
                            </label>
                            <?php if (!empty($acta_final)): ?>
                                <?php
                                list($rutaWeb, $rutaFisica) = ajustarRutaDocumento($acta_final);
                                ?>
                                <?php if ($rutaWeb && file_exists($rutaFisica)): ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </span>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($acta_final); ?>" readonly>
                                    </div>
                                    <div class="d-flex justify-content-center gap-2 mt-2">
                                        <button type="button"
                                            class="bot_mostrar_vista btn btn-sm"
                                            onclick="abrirModalInforme('<?php echo htmlspecialchars($rutaWeb, ENT_QUOTES, 'UTF-8'); ?>')">
                                            <i class="bi bi-eye"></i> Vista previa
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text bg-warning border border-warning">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </span>
                                        <input type="text" class="form-control border border-warning text-warning" value="El archivo está registrado, pero no se encuentra en el servidor." readonly>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="input-group shadow-sm rounded-3">
                                    <span class="input-group-text bg-danger border border-danger">
                                        <i class="bi bi-file-earmark-x text-white"></i>
                                    </span>
                                    <input type="text" class="form-control border border-danger text-danger" value="No se ha cargado ningún documento" readonly>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-4 d-flex justify-content-between  ">
                            <button type="button" class="btn btn-secondary text-white" style="background-color: rgba(112, 113, 114, 1);" onclick="prevSection()"
                                style="margin-right: 1cm;"> <i class="bi bi-arrow-left me-2"></i> Atrás</button>
                            <button type="button" class="btn  text-white" style="background-color: #002F55;" onclick="nextSection()">
                                Siguiente <i class="bi bi-arrow-right ms-2"></i></button>
                        </div>
                    </div>
                </div>
                <div class="form-section d-none" id="section6">
                    <div class="mb-3  d-flex align-items-center text-start rounded-4 card-header shadow  p-3 text-white">
                        <i class="bi bi-person-bounding-box me-3 fs-2"></i>
                        <div>
                            <h3 class="h5 mb-1">Información Sociodemográfica</h3>
                            <p class=" mb-0" style="font-size: 0.85rem; color:#999999">
                                Información básica del colaborador.
                            </p>
                        </div>
                    </div>

                    <div class="row p-4 g-3 card-especial-tres">
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_genero" class="form-label" style="font-size:0.9em;">Género</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-gender-ambiguous"></i></span>
                                <input class="form-control" name="con_genero" id="con_genero" value="<?php echo $con_genero; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_genero" class="form-label" style="font-size:0.9em;">Raza</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-arms-up"></i></span>
                                <input class="form-control" name="con_raza" id="con_raza" value="<?php echo $con_raza; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_genero" class="form-label" style="font-size:0.9em;">Vivienda</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-house"></i></span>
                                <input class="form-control" name="con_vivienda" id="con_vivienda" value="<?php echo $con_vivienda; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_genero" class="form-label" style="font-size:0.9em;">Estrato</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-house-exclamation"></i></span>
                                <input class="form-control" name="con_estrato" id="con_estrato" value="<?php echo $con_estrato; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_genero" class="form-label" style="font-size:0.9em;">¿Discapacidad?</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-wheelchair"></i></span>
                                <input class="form-control" name="con_discapacidad" id="con_discapacidad" value="<?php echo $con_discapacidad; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12  my-4" style="border-bottom:2px dashed #002f557a"></div>

                        <div class="col-12 mt-0">
                            <h6 class="fw-bold p-2 text-white text-center w-25 rounded-3" style="background-color: #002F55;">Documentos adjuntos</h6>
                        </div>
                        <?php
                        $cer_discapa = $row['con_cerIncapacidad'] ?? null;
                        ?>
                        <div class="col-12 col-lg-4 p-1 px-2 my-4">
                            <label class="form-label fw-bold mb-1" style="font-size:0.9em">
                                Certificado de discapacidad
                            </label>
                            <?php if (!empty($cer_discapa)): ?>
                                <?php
                                list($rutaWeb, $rutaFisica) = ajustarRutaDocumento($cer_discapa);
                                ?>
                                <?php if ($rutaWeb && file_exists($rutaFisica)): ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text">
                                            <i class="bi bi-file-earmark-pdf "></i>
                                        </span>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($cer_discapa); ?>" readonly>
                                    </div>
                                    <div class="d-flex justify-content-center gap-2 mt-2">
                                        <button type="button"
                                            class="bot_mostrar_vista btn btn-sm"
                                            onclick="abrirModalInforme('<?php echo htmlspecialchars($rutaWeb, ENT_QUOTES, 'UTF-8'); ?>')">
                                            <i class="bi bi-eye"></i> Vista previa
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text bg-warning border border-warning">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </span>
                                        <input type="text" class="form-control border border-warning text-warning" value="El archivo está registrado, pero no se encuentra en el servidor." readonly>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="input-group shadow-sm rounded-3">
                                    <span class="input-group-text bg-danger border border-danger">
                                        <i class="bi bi-file-earmark-x text-white"></i>
                                    </span>
                                    <input type="text" class="form-control border border-danger text-danger" value="No se ha cargado ningún documento" readonly>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-4 d-flex justify-content-between  ">
                            <button type="button" class="btn btn-secondary text-white" style="background-color: rgba(112, 113, 114, 1);" onclick="prevSection()"
                                style="margin-right: 1cm;"> <i class="bi bi-arrow-left me-2"></i> Atrás</button>
                            <button type="button" class="btn  text-white" style="background-color: #002F55;" onclick="nextSection()">
                                Siguiente <i class="bi bi-arrow-right ms-2"></i></button>
                        </div>
                    </div>
                </div>
                <div class="form-section d-none" id="section7">
                    <div class="mb-3  d-flex align-items-center text-start rounded-4 card-header shadow  p-3 text-white">
                        <i class="bi bi-hospital me-3 fs-2"></i>
                        <div>
                            <h3 class="h5 mb-1">Esquema de vacunación</h3>
                            <p class=" mb-0" style="font-size: 0.85rem; color:#999999">
                                Ingresa todos los campos correspondientes a las vacunas que tiene el colaborador.
                            </p>
                        </div>
                    </div>
                    <div class="row p-4 g-3 card-especial-tres">
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_jefe" class="form-label fw-bold" style="font-size:0.9em;">Vacuna de fiebre amarilla</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-prescription2"></i></span>
                                <input class="form-control fw-bold <?php echo ($con_amarilla == 1) ? 'text-success' : 'text-danger'; ?>"
                                    value="<?php echo ($con_amarilla == 1) ? 'Vacuna aplicada' : 'No aplicada'; ?>"
                                    readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_jefe" class="form-label fw-bold" style="font-size:0.9em;">Vacuna Tetano 1</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-prescription2"></i></span>
                                <input class="form-control fw-bold <?php echo ($con_tetano1 == 1) ? 'text-success' : 'text-danger'; ?>"
                                    value="<?php echo ($con_tetano1 == 1) ? 'Vacuna aplicada' : 'No aplicada'; ?>"
                                    readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_jefe" class="form-label fw-bold" style="font-size:0.9em;">Vacuna Tetano 2</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-prescription2"></i></span>
                                <input class="form-control fw-bold <?php echo ($con_tetano2 == 1) ? 'text-success' : 'text-danger'; ?>"
                                    value="<?php echo ($con_tetano2 == 1) ? 'Vacuna aplicada' : 'No aplicada'; ?>"
                                    readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_jefe" class="form-label fw-bold" style="font-size:0.9em;">Vacuna Tetano 3</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-prescription2"></i></span>
                                <input class="form-control fw-bold <?php echo ($con_tetano3 == 1) ? 'text-success' : 'text-danger'; ?>"
                                    value="<?php echo ($con_tetano3 == 1) ? 'Vacuna aplicada' : 'No aplicada'; ?>"
                                    readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_jefe" class="form-label fw-bold" style="font-size:0.9em;">Vacuna Covid 1</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-prescription2"></i></span>
                                <input class="form-control fw-bold <?php echo ($con_covid1 == 1) ? 'text-success' : 'text-danger'; ?>"
                                    value="<?php echo ($con_covid1 == 1) ? 'Vacuna aplicada' : 'No aplicada'; ?>"
                                    readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_jefe" class="form-label fw-bold" style="font-size:0.9em;">Vacuna Covid 2</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-prescription2"></i></span>
                                <input class="form-control fw-bold <?php echo ($con_covid2 == 1) ? 'text-success' : 'text-danger'; ?>"
                                    value="<?php echo ($con_covid2 == 1) ? 'Vacuna aplicada' : 'No aplicada'; ?>"
                                    readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_jefe" class="form-label fw-bold" style="font-size:0.9em;">Vacuna Covid 3</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-prescription2"></i></span>
                                <input class="form-control fw-bold <?php echo ($con_covid3 == 1) ? 'text-success' : 'text-danger'; ?>"
                                    value="<?php echo ($con_covid3 == 1) ? 'Vacuna aplicada' : 'No aplicada'; ?>"
                                    readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_jefe" class="form-label fw-bold" style="font-size:0.9em;">Influenza</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-prescription2"></i></span>
                                <input class="form-control fw-bold <?php echo ($con_influenza == 1) ? 'text-success' : 'text-danger'; ?>"
                                    value="<?php echo ($con_influenza == 1) ? 'Vacuna aplicada' : 'No aplicada'; ?>"
                                    readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_jefe" class="form-label fw-bold" style="font-size:0.9em;">Hepatitis A</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-prescription2"></i></span>
                                <input class="form-control fw-bold <?php echo ($con_hepatitis_a == 1) ? 'text-success' : 'text-danger'; ?>"
                                    value="<?php echo ($con_hepatitis_a == 1) ? 'Vacuna aplicada' : 'No aplicada'; ?>"
                                    readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_jefe" class="form-label fw-bold" style="font-size:0.9em;">Hepatitis C</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-prescription2"></i></span>
                                <input class="form-control fw-bold <?php echo ($con_hepatitis_c == 1) ? 'text-success' : 'text-danger'; ?>"
                                    value="<?php echo ($con_hepatitis_c == 1) ? 'Vacuna aplicada' : 'No aplicada'; ?>"
                                    readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_jefe" class="form-label fw-bold" style="font-size:0.9em;">VPH 1</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-prescription2"></i></span>
                                <input class="form-control fw-bold <?php echo ($vph_1 == 1) ? 'text-success' : 'text-danger'; ?>"
                                    value="<?php echo ($vph_1 == 1) ? 'Vacuna aplicada' : 'No aplicada'; ?>"
                                    readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_jefe" class="form-label fw-bold" style="font-size:0.9em;">VPH 2</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-prescription2"></i></span>
                                <input class="form-control fw-bold <?php echo ($vph_2 == 1) ? 'text-success' : 'text-danger'; ?>"
                                    value="<?php echo ($vph_2 == 1) ? 'Vacuna aplicada' : 'No aplicada'; ?>"
                                    readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_jefe" class="form-label fw-bold" style="font-size:0.9em;">VPH 3</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-prescription2"></i></span>
                                <input class="form-control fw-bold <?php echo ($vph_3 == 1) ? 'text-success' : 'text-danger'; ?>"
                                    value="<?php echo ($vph_3 == 1) ? 'Vacuna aplicada' : 'No aplicada'; ?>"
                                    readonly>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_jefe" class="form-label fw-bold" style="font-size:0.9em;">RH Sanguíneo</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-prescription2"></i></span>
                                <input class="form-control fw-bold <?php echo ($con_rh == 1) ? 'text-success' : 'text-danger'; ?>"
                                    value="<?php echo ($con_rh == 1) ? 'Vacuna aplicada' : 'No aplicada'; ?>"
                                    readonly>
                            </div>
                        </div>

                        <div class="col-12  my-4" style="border-bottom:2px dashed #002f557a"></div>
                        <div class="col-12">
                            <h6 class="fw-bold p-2 text-white text-center w-25 rounded-3" style="background-color: #002F55;">Documentos Adjuntos</h6>
                        </div>
                        <?php
                        $cer_vacuna = $row['con_cerVacunacion'] ?? null;
                        ?>
                        <div class="col-12 col-lg-6 p-1 px-2 my-4">
                            <label class="form-label fw-bold mb-1" style="font-size:0.9em">
                                Certificado Bancario
                            </label>
                            <?php if (!empty($cer_vacuna)): ?>
                                <?php
                                list($rutaWeb, $rutaFisica) = ajustarRutaDocumento($cer_vacuna);
                                ?>
                                <?php if ($rutaWeb && file_exists($rutaFisica)): ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </span>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($cer_vacuna); ?>" readonly>
                                    </div>

                                    <div class="d-flex justify-content-center gap-2 mt-2">
                                        <button type="button"
                                            class="bot_mostrar_vista btn btn-sm"
                                            onclick="abrirModalInforme('<?php echo htmlspecialchars($rutaWeb, ENT_QUOTES, 'UTF-8'); ?>')">
                                            <i class="bi bi-eye"></i> Vista previa
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text bg-warning border border-warning">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </span>
                                        <input type="text" class="form-control border border-warning text-warning" value="El archivo está registrado, pero no se encuentra en el servidor." readonly>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="input-group shadow-sm rounded-3">
                                    <span class="input-group-text bg-danger border border-danger">
                                        <i class="bi bi-file-earmark-x text-white"></i>
                                    </span>
                                    <input type="text" class="form-control border border-danger text-danger" value="No se ha cargado ningún documento" readonly>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-4 d-flex justify-content-between  ">
                            <button type="button" class="btn btn-secondary text-white" style="background-color: rgba(112, 113, 114, 1);" onclick="prevSection()"
                                style="margin-right: 1cm;"> <i class="bi bi-arrow-left me-2"></i> Atrás</button>
                            <button type="button" class="btn  text-white" style="background-color: #002F55;" onclick="nextSection()">
                                Siguiente <i class="bi bi-arrow-right ms-2"></i></button>
                        </div>
                    </div>
                </div>
                <div class="form-section d-none" id="section8">
                    <div class="mb-3  d-flex align-items-center text-start rounded-4 card-header shadow  p-3 text-white">
                        <i class="bi bi-person-bounding-box me-3 fs-2"></i>
                        <div>
                            <h3 class="h5 mb-1">Documentos Relacionados</h3>
                            <p class=" mb-0" style="font-size: 0.85rem; color:#999999">
                                Información básica del colaborador.
                            </p>
                        </div>
                    </div>
                    <div class="row p-4 g-3 card-especial-tres align-items-center justify-content-center">
                        <div class="col-md-4 p-1 px-2 my-2 text-center">
                            <label for="con_jefe" class="form-label fw-bold" style="font-size:0.9em;">Otro si</label>
                            <div class="input-group border">
                                <a href="/arbimaps/Arbimaps/index.php?page=Personal/ver_otrosi&con_id=<?php echo urlencode($row['con_id']); ?>&con_num_identidad=<?php echo urlencode($row['con_num_identidad']); ?>"
                                    class="btn text-white w-100" style="background-color: #002F55; border:1px solid #002F55">
                                    <i class="bi bi-file-pdf me-2"></i> Ver documentos
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4 p-1 px-2 my-2 text-center">
                            <label for="con_jefe" class="form-label fw-bold" style="font-size:0.9em;">Titulos Profesionales</label>
                            <div class="input-group ">
                                <a href="/arbimaps/Arbimaps/index.php?page=Personal/ver_estudios&con_id=<?php echo urlencode($row['con_id']); ?>&con_num_identidad=<?php echo urlencode($row['con_num_identidad']); ?>"
                                    class="btn text-white w-100" style="background-color: #002F55; border:1px solid #002F55">
                                    <i class="bi bi-file-pdf me-2"></i> Ver documentos
                                </a>
                            </div>
                        </div>
                        <div class="mt-4 d-flex justify-content-between  ">
                            <button type="button" class="btn btn-secondary text-white" style="background-color: rgba(112, 113, 114, 1);" onclick="prevSection()"
                                style="margin-right: 1cm;"> <i class="bi bi-arrow-left me-2"></i> Atrás</button>
                            <button type="button" class="btn btn-success px-4" id="btn_volver" style="background-color: #049811ff;"> <i class="bi bi-house-up me-2"></i> Inicio</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPDF" tabindex="-1" style="z-index: 999999;">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; height: 92vh; display:flex; flex-direction:column;">
            <div class="modal-header">
                <h7 class="modal-title fw-bold" style="color: #002F55;">Vista previa del documento</h7>
                <button type="button" class="btn-close text-white" style="background-color: #002F55;" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="flex:1; overflow:hidden;">
                <iframe id="iframePDF" width="100%" height="100%" style="border:0;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn text-white btn-sm" style="background-color: #002F55;" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="../js/scripts.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable();
    });

    document.getElementById('btn_volver').addEventListener('click', function() {
        window.location.href = 'index.php?page=Personal/personal_activo'; 
    });

    let currentSectionIndex = 1;
    const totalSections = 8;

    function showSection(index) {
        for (let i = 1; i <= totalSections; i++) {
            const section = document.getElementById('section' + i);
            if (section) {
                section.classList.add('d-none');
            }
        }
        const current = document.getElementById('section' + index);
        if (current) {
            current.classList.remove('d-none');
        }
        actualizarBotones(index);
    }

    function nextSection() {
        let nextIndex = currentSectionIndex + 1;
        while (nextIndex <= totalSections && !document.getElementById('section' + nextIndex)) {
            nextIndex++;
        }
        if (nextIndex <= totalSections) {
            currentSectionIndex = nextIndex;
            showSection(currentSectionIndex);
        }
    }

    function prevSection() {
        let prevIndex = currentSectionIndex - 1;
        while (prevIndex >= 1 && !document.getElementById('section' + prevIndex)) {
            prevIndex--;
        }
        if (prevIndex >= 1) {
            currentSectionIndex = prevIndex;
            showSection(currentSectionIndex);
        }
    }

    function seccion(index) {
        if (document.getElementById('section' + index)) {
            currentSectionIndex = index;
            showSection(index);
        }
    }

    function actualizarBotones(index) {
        document.querySelectorAll('.section-btn').forEach(btn => {
            btn.classList.remove('active-section-btn');
        });

        const botones = document.querySelectorAll('.section-btn');
        if (botones[index - 1]) {
            botones[index - 1].classList.add('active-section-btn');
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        showSection(currentSectionIndex);
    });

    function actualizarBotones(index) {
        const btnPrev = document.querySelector('.btn.btn-secondary');
        const btnNext = document.querySelector('.btn.btn-primary');

        if (btnPrev) btnPrev.disabled = (index === 1);
        if (btnNext) btnNext.disabled = (index === totalSections);

        document.querySelectorAll('.section-btn').forEach(btn => {
            btn.classList.remove('active-section-btn');
        });

        const botones = document.querySelectorAll('.section-btn');
        if (botones[index - 1]) {
            botones[index - 1].classList.add('active-section-btn');
        }
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const steps = document.querySelectorAll('.step');
        const sections = document.querySelectorAll('.form-section');
        let currentStep = 0;
        function goToStep(index) {
            currentStep = index;
            sections.forEach((sec, i) => {
                sec.classList.toggle('d-none', i !== index);
            });
            steps.forEach((step, i) => {
                step.classList.remove('active', 'completed');
                if (i < index) step.classList.add('completed');
                if (i === index) step.classList.add('active');
            });
            updateProgress();
        }

        function updateProgress() {
            const percent = (currentStep / (steps.length - 1)) * 100;
            document.getElementById('stepper-progress').style.width = percent + '%';
        }

        steps.forEach((btn, index) => {
            btn.addEventListener('click', () => goToStep(index));
        });

        document.querySelectorAll('.btn-next-section').forEach(btn => {
            btn.addEventListener('click', () => {
                if (currentStep < steps.length - 1) goToStep(currentStep + 1);
            });
        });

        document.querySelectorAll('.btn-prev-section').forEach(btn => {
            btn.addEventListener('click', () => {
                if (currentStep > 0) goToStep(currentStep - 1);
            });
        });
        goToStep(0);
    });
</script>