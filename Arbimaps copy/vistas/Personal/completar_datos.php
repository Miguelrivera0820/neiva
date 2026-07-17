<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../../../conexion.php';

$idUsuario = $_SESSION['id_usuario'] ?? null;
$con_nombres            = $_POST['con_nombres'] ?? "";
$con_apellidos          = $_POST['con_apellidos'] ?? "";
$con_tipo_documento     = $_POST['con_tipo_documento'] ?? "";
$con_num_identidad      = $_GET['con_num_identidad'] ?? "";
$con_FechaExpe          = $_POST['con_FechaExpe'] ?? "";
$con_lugarE             = $_POST['con_lugarE'] ?? "";
$con_fecha_nacimiento   = $_POST['con_fecha_nacimiento'] ?? "";
$con_edad               = $_POST['con_edad'] ?? "";
$con_lugar_nacimiento   = $_POST['con_lugar_nacimiento'] ?? "";
$con_direccion          = $_POST['con_direccion'] ?? "";
$con_barrio             = $_POST['con_barrio'] ?? "";
$con_ciudad             = $_POST['con_ciudad'] ?? "";
$con_tel_fijo           = $_POST['con_tel_fijo'] ?? "";
$con_celular            = $_POST['con_celular'] ?? "";
$con_correo             = $_POST['con_correo'] ?? "";
$con_correo_corporativo = $_POST['con_correo_corporativo'] ?? "";
$con_estado_civil       = $_POST['con_estado_civil'] ?? "";
$con_nombre_conyuge     = $_POST['con_nombre_conyuge'] ?? "";
$con_enfermedades       = $_POST['con_enfermedades'] ?? "";
$con_alergias           = $_POST['con_alergias'] ?? "";
$con_medicamentos       = $_POST['con_medicamentos'] ?? "";
$con_emergencia         = $_POST['con_emergencia'] ?? "";
$con_parentesco         = $_POST['con_parentesco'] ?? "";
$con_tel_emergencia     = $_POST['con_tel_emergencia'] ?? "";
$con_num_cuenta         = $_POST['con_num_cuenta'] ?? "";
$con_tipo_cuenta        = $_POST['con_tipo_cuenta'] ?? "";
$con_financiera         = $_POST['con_financiera'] ?? "";
$con_eps                = $_POST['con_eps'] ?? "";
$con_afp                = $_POST['con_afp'] ?? "";
$con_arl                = $_POST['con_arl'] ?? "";
$con_rh                 = $_POST['con_rh'] ?? "";
$con_profesion          = $_POST['con_profesion'] ?? "";
$con_grado              = $_POST['con_grado'] ?? "";
$con_num_tarjeta        = $_POST['con_num_tarjeta'] ?? "";
$con_expedicion         = $_POST['con_expedicion'] ?? "";
$con_escolaridad        = $_POST['con_escolaridad'] ?? "";
$con_sede               = $_POST['con_sede'] ?? "";
$con_presencialidad     = $_POST['con_presencialidad'] ?? "";
$con_cargo              = $_POST['con_cargo'] ?? "";
$con_proyecto           = $_POST['con_proyecto'] ?? "";
$con_tipo_contrato      = $_POST['con_tipo_contrato'] ?? "";
$con_fecha_inicio       = $_POST['con_fecha_inicio'] ?? "";
$con_fecha_final        = $_POST['con_fecha_final'] ?? "";
$con_duracion           = $_POST['con_duracion'] ?? "";
$con_salario            = $_POST['con_salario'] ?? "";
$con_jefe               = $_POST['con_jefe'] ?? "";
$con_per_cargo          = $_POST['con_per_cargo'] ?? "";
$con_valor_proyecto     = $_POST['con_valor_proyecto'] ?? "";
$con_genero             = $_POST['con_genero'] ?? "";
$con_raza               = $_POST['con_raza'] ?? "";
$con_vivienda           = $_POST['con_vivienda'] ?? "";
$con_estrato            = $_POST['con_estrato'] ?? "";
$incapacitado           = $_POST['incapacitado'] ?? "";
$con_discapacidad       = $_POST['con_discapacidad'] ?? "";
$con_amarilla           = $_POST['con_amarilla'] ?? "";
$con_tetano1            = $_POST['con_tetano1'] ?? "";
$con_tetano2            = $_POST['con_tetano2'] ?? "";
$con_tetano3            = $_POST['con_tetano3'] ?? "";
$con_covid1             = $_POST['con_covid1'] ?? "";
$con_covid2             = $_POST['con_covid2'] ?? "";
$con_covid3             = $_POST['con_covid3'] ?? "";
$con_influenza          = $_POST['con_influenza'] ?? "";
$con_hepatitis_a        = $_POST['con_hepatitis_a'] ?? "";
$con_hepatitis_c        = $_POST['con_hepatitis_c'] ?? "";
$vph_1                  = $_POST['vph_1'] ?? "";
$vph_2                  = $_POST['vph_2'] ?? "";
$vph_3                  = $_POST['vph_3'] ?? "";

if ($con_num_identidad) {
    $query = "SELECT * FROM contratacion WHERE con_num_identidad = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $con_num_identidad);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $con_nombres            = $row['con_nombres']            ?? $con_nombres;
        $con_apellidos          = $row['con_apellidos']          ?? $con_apellidos;
        $con_tipo_documento     = $row['con_tipo_documento']     ?? $con_tipo_documento;
        $con_num_identidad      = $row['con_num_identidad']      ?? $con_num_identidad;
        $con_FechaExpe          = $row['con_FechaExpe']          ?? $con_FechaExpe;
        $con_lugarE             = $row['con_lugarE']             ?? $con_lugarE;
        $con_fecha_nacimiento   = $row['con_fecha_nacimiento']   ?? $con_fecha_nacimiento;
        $con_edad               = $row['con_edad']               ?? $con_edad;
        $con_lugar_nacimiento   = $row['con_lugar_nacimiento']   ?? $con_lugar_nacimiento;
        $con_direccion          = $row['con_direccion']          ?? $con_direccion;
        $con_barrio             = $row['con_barrio']             ?? $con_barrio;
        $con_ciudad             = $row['con_ciudad']             ?? $con_ciudad;
        $con_tel_fijo           = $row['con_tel_fijo']           ?? $con_tel_fijo;
        $con_celular            = $row['con_celular']            ?? $con_celular;
        $con_correo             = $row['con_correo']             ?? $con_correo;
        $con_correo_corporativo = $row['con_correo_corporativo'] ?? $con_correo_corporativo;
        $con_estado_civil       = $row['con_estado_civil']       ?? $con_estado_civil;
        $con_nombre_conyuge     = $row['con_nombre_conyuge']     ?? $con_nombre_conyuge;
        $con_enfermedades       = $row['con_enfermedades']       ?? $con_enfermedades;
        $con_alergias           = $row['con_alergias']           ?? $con_alergias;
        $con_medicamentos       = $row['con_medicamentos']       ?? $con_medicamentos;
        $con_emergencia         = $row['con_emergencia']         ?? $con_emergencia;
        $con_parentesco         = $row['con_parentesco']         ?? $con_parentesco;
        $con_tel_emergencia     = $row['con_tel_emergencia']     ?? $con_tel_emergencia;
        $con_num_cuenta         = $row['con_num_cuenta']         ?? $con_num_cuenta;
        $con_tipo_cuenta        = $row['con_tipo_cuenta']        ?? $con_tipo_cuenta;
        $con_financiera         = $row['con_financiera']         ?? $con_financiera;
        $con_eps                = $row['con_eps']                ?? $con_eps;
        $con_afp                = $row['con_afp']                ?? $con_afp;
        $con_arl                = $row['con_arl']                ?? $con_arl;
        $con_rh                 = $row['con_rh']                 ?? $con_rh;
        $con_profesion          = $row['con_profesion']          ?? $con_profesion;
        $con_grado              = $row['con_grado']              ?? $con_grado;
        $con_num_tarjeta        = $row['con_num_tarjeta']        ?? $con_num_tarjeta;
        $con_expedicion         = $row['con_expedicion']         ?? $con_expedicion;
        $con_escolaridad        = $row['con_escolaridad']        ?? $con_escolaridad;
        $con_sede               = $row['con_sede']               ?? $con_sede;
        $con_presencialidad     = $row['con_presencialidad']     ?? $con_presencialidad;
        $con_cargo              = $row['con_cargo']              ?? $con_cargo;
        $con_proyecto           = $row['con_proyecto']           ?? $con_proyecto;
        $con_tipo_contrato      = $row['con_tipo_contrato']      ?? $con_tipo_contrato;
        $con_fecha_inicio       = $row['con_fecha_inicio']       ?? $con_fecha_inicio;
        $con_fecha_final        = $row['con_fecha_final']        ?? $con_fecha_final;
        $con_duracion           = $row['con_duracion']           ?? $con_duracion;
        $con_salario            = $row['con_salario']            ?? $con_salario;
        $con_jefe               = $row['con_jefe']               ?? $con_jefe;
        $con_per_cargo          = $row['con_per_cargo']          ?? $con_per_cargo;
        $con_valor_proyecto     = $row['con_valor_proyecto']     ?? $con_valor_proyecto;
        $con_genero             = $row['con_genero']             ?? $con_genero;
        $con_raza               = $row['con_raza']               ?? $con_raza;
        $con_vivienda           = $row['con_vivienda']           ?? $con_vivienda;
        $con_estrato            = $row['con_estrato']            ?? $con_estrato;
        $incapacitado           = $row['incapacitado']           ?? $incapacitado;
        $con_discapacidad       = $row['con_discapacidad']       ?? $con_discapacidad;
        $con_amarilla           = $row['con_amarilla']           ?? $con_amarilla;
        $con_tetano1            = $row['con_tetano1']            ?? $con_tetano1;
        $con_tetano2            = $row['con_tetano2']            ?? $con_tetano2;
        $con_tetano3            = $row['con_tetano3']            ?? $con_tetano3;
        $con_covid1             = $row['con_covid1']             ?? $con_covid1;
        $con_covid2             = $row['con_covid2']             ?? $con_covid2;
        $con_covid3             = $row['con_covid3']             ?? $con_covid3;
        $con_influenza          = $row['con_influenza']          ?? $con_influenza;
        $con_hepatitis_a        = $row['con_hepatitis_a']        ?? $con_hepatitis_a;
        $con_hepatitis_c        = $row['con_hepatitis_c']        ?? $con_hepatitis_c;
        $vph_1                  = $row['vph_1']                  ?? $vph_1;
        $vph_2                  = $row['vph_2']                  ?? $vph_2;
        $vph_3                  = $row['vph_3']                  ?? $vph_3;
    } else {
        header("Location: personal_activo.php?guardado=invalido");
        exit;
    }
}

function ajustarRutaDocumento($rutaBD)
{
    if (empty($rutaBD)) {
        return [null, null];
    }
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
    $rutaFisica = $_SERVER['DOCUMENT_ROOT'] . $rutaWeb;
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

    /*Bordes cuadrados*/
    .form-control {
        font-size: 14px;
    }

    /* Tipos de textos y letras */
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
        font-size: 0.9rem;
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

    /* Overlay */
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

    /* Hover */
    .photo-wrapper:hover .photo-overlay {
        opacity: 1;
    }

    .photo-wrapper:hover .photo-img {
        transform: scale(1.02);
    }

    /* Icono */
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

    /* Imagen */
    .photo-fixed .photo-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }
</style>

<div class="container-fluid px-3">
    <div class="card shadow-sm border-0 mb-4">
        <div class="my-4 text-center">
            <h4 class=" mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 700 !important ">SECCIÓN DE INFORMACIÓN PERSONAL </h4>
            <small> Modulo para la consulta de la información del personal</small>
        </div>
        <div class="card-form p-3">
            <div class="stepper-wrapper w-50  mb-0 justify-content-center">
                <div class="stepper-line">
                    <div id="stepper-progress"></div>
                </div>
                <div class="stepper">
                    <button class="step active" onclick="seccion(1)">Datos Personales</button>
                    <button class="step" onclick="seccion(2)">Otro Sí</button>
                </div>
            </div>
            <div class="card-body">
                <form id="formContratacion"
                    action="/arbimaps/Arbimaps/vistas/Personal/guardar_contratacion.php"
                    method="POST"
                    enctype="multipart/form-data">
                    <input type="hidden" name="origen" value="completar">
                    <input type="hidden" name="con_num_identidad"
                        value="<?php echo htmlspecialchars($con_num_identidad ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <div id="section5" class="seccion-formulario d-none">
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
                                            <input type="text" class="form-control" id="con_nombres" name="con_nombres" value="<?php echo htmlspecialchars($con_nombres ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4 p-1 px-2 my-1">
                                        <label for="con_num_identidad" class="form-label fw-bold" style="font-size:0.9em;">Número de identidad</label>
                                        <div class="input-group ">
                                            <span class="input-group-text shadow-sm"><i class="bi bi-person-vcard"></i></span>
                                            <input class="form-control" placeholder="Ingrese el número de identidad" name="con_num_identidad" id="con_num_identidad" value="<?php echo htmlspecialchars($con_num_identidad ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-2 p-1 px-2 my-2 d-flex flex-column justify-content-center align-items-center">
                                        <label class="form-label d-block">Presencialidad</label>
                                        <div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="con_presencialidad" id="presencialidad_si" value="SI">
                                                <label class="form-check-label" for="presencialidad_si">SI</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="con_presencialidad" id="presencialidad_no" value="NO" checked>
                                                <label class="form-check-label" for="presencialidad_no">NO</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 p-1 px-2 my-2">
                                        <label for="con_sede" class="form-label" style="font-size:0.9em;">Sede de trabajo</label>
                                        <div class="input-group shadow-sm">
                                            <label class="input-group-text" for="con_sede">
                                                <i class="bi bi-building"></i>
                                            </label>
                                            <select class="form-select" id="con_sede" name="con_sede" required>
                                                <option value="">SELECCIONE</option>
                                                <option value="NEIVA">NEIVA</option>
                                                <option value="LEIVA">LEIVA</option>
                                                <option value="SAN_PEDRO">SAN_PEDRO</option>
                                                <option value="SAN_JUAN">SAN_JUAN</option>
                                                <option value="BELLO">BELLO</option>
                                                <option value="NECOCLI">NECOCLI</option>
                                                <option value="VALLE_GUAMUEZ">VALLE_GUAMUEZ</option>
                                                <option value="ARBOLETES">ARBOLETES</option>n>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 p-1 px-2 my-2">
                                        <label for="con_cargo" class="form-label" style="font-size:0.9em;">Cargo / Rol</label>
                                        <div class="input-group shadow-sm">
                                            <label class="input-group-text" for="con_cargo">
                                                <i class="bi bi-file-person-fill"></i>
                                            </label>
                                            <select class="form-select" id="con_cargo" name="con_cargo" required>
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
                                    <div class="col-md-4 p-1 px-2 my-2">
                                        <label for="con_proyecto" class="form-label" style="font-size:0.9em;">Proyecto</label>
                                        <div class="input-group shadow-sm">
                                            <label class="input-group-text" for="con_proyecto">
                                                <i class="bi bi-bezier"></i>
                                            </label>
                                            <select class="form-select" id="con_proyecto" name="con_proyecto" required>
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
                                                <option value="VALLE_GUAMUEZ">IGAC VALLE DEL GUAMUEZ</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4 p-1 px-2 my-2">
                                        <label for="con_tipo_contrato" class="form-label" style="font-size:0.9em;">Tipo de contrato</label>
                                        <div class="input-group shadow-sm">
                                            <label class="input-group-text" for="con_tipo_contrato">
                                                <i class="bi bi-file-earmark-medical"></i>
                                            </label>
                                            <select class="form-select" id="con_tipo_contrato" name="con_tipo_contrato" required>
                                                <option value="">SELECCIONE</option>
                                                <option value="PRESTACION_SERVICIOS">PRESTACIÓN DE SERVICIOS</option>
                                                <option value="LABORAL_TERMINO_FIJO">LABORAL A TERMINO FIJO</option>
                                                <option value="LABORAL_TERMINO_INDEFINIDO">LABORAL A TERMINO INDEFINIDO</option>
                                                <option value="ORDEN_SERVICIO">ORDEN DE SERVICIO</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4 p-1 px-2 my-2">
                                        <label for="con_fecha_inicio" class="form-label fw-bold" style="font-size:0.9em;">Fecha de inicio de contrato</label>
                                        <div class="input-group ">
                                            <span class="input-group-text shadow-sm"><i class="bi bi-calendar-date"></i></span>
                                            <input type="date" class="form-control" id="con_fecha_inicio" name="con_fecha_inicio">
                                        </div>
                                    </div>

                                    <div class="col-md-4 p-1 px-2 my-2">
                                        <label for="con_fecha_final" class="form-label fw-bold" style="font-size:0.9em;">Fecha de finalización de contrato</label>
                                        <div class="input-group ">
                                            <span class="input-group-text shadow-sm"><i class="bi bi-calendar-date"></i></span>
                                            <input type="date" class="form-control" id="con_fecha_final" name="con_fecha_final">
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

                                    <div class="col-md-4 mt-2 px-2 my-2">
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

                                    <div class="col-md-4 mt-2 px-2 my-2">
                                        <label for="con_valor_proyecto_mostrado" class="form-label fw-bold" style="font-size:0.9em;">Valor Total Proyecto</label>
                                        <div class="input-group shadow-sm shadow-warning">
                                            <span class="input-group-text shadow-sm bg-warning border border-warning"><i class="bi bi-cash-coin"></i></span>
                                            <input type="text"
                                                class="form-control border-warning"
                                                id="con_valor_proyecto_mostrado"
                                                name="con_valor_proyecto_mostrado"
                                                placeholder="Ingrese el valor total del proyecto"
                                                oninput="formatCurrency(this, 'con_valor_proyecto')"
                                                autocomplete="off">
                                            <input type="hidden" id="con_valor_proyecto" name="con_valor_proyecto">
                                        </div>
                                    </div>

                                    <div class="col-md-6 mt-2 px-2 my-2">
                                        <label for="con_jefe" class="form-label fw-bold" style="font-size:0.9em;">Jefe Inmediato / Supervisor</label>
                                        <div class="input-group ">
                                            <span class="input-group-text shadow-sm"><i class="bi bi-person-gear"></i></span>
                                            <input type="text" class="form-control" id="con_jefe" name="con_jefe"
                                                placeholder="Ingrese el nombre del jefe inmediato">
                                        </div>
                                    </div>

                                    <div class="col-md-6 mt-2 px-2 my-2">
                                        <label for="con_per_cargo" class="form-label fw-bold" style="font-size:0.9em;">Nº Personas a Cargo</label>
                                        <div class="input-group ">
                                            <span class="input-group-text shadow-sm"><i class="bi bi-person-gear"></i></span>
                                            <input type="text" class="form-control" id="con_per_cargo" name="con_per_cargo"
                                                placeholder="Ingrese el total de personas a cargo">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12  my-4" style="border-bottom:2px dashed #002f557a"></div>
                            <div class="col-12 mt-0">
                                <h6 class="fw-bold p-2 text-white text-center w-25 rounded-3" style="background-color: #002F55;">Adjuntar documentos</h6>
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
                                <label for="con_contrato" class="form-label fw-bold">Exámenes de ingreso</label>
                                <div class="input-group mb-1 shadow-sm">
                                    <label class="input-group-text" for="con_contrato" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
                                    <input type="file" class="form-control" style="font-size:0.8em;" id="con_examenes" name="con_examenes">
                                </div>
                                <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                            </div>

                            <div class="col-12 col-lg-6  p-1 px-3 my-3">
                                <label for="con_contrato" class="form-label fw-bold">Póliza de Cumplimiento</label>
                                <div class="input-group mb-1 shadow-sm">
                                    <label class="input-group-text" for="con_contrato" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
                                    <input type="file" class="form-control" style="font-size:0.8em;" id="con_cumplimiento" name="con_cumplimiento">
                                </div>
                                <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                            </div>

                            <div class="col-12 col-lg-6  p-1 px-3 my-3">
                                <label for="con_contrato" class="form-label fw-bold">Acta de Inicio</label>
                                <div class="input-group mb-1 shadow-sm">
                                    <label class="input-group-text" for="con_contrato" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
                                    <input type="file" class="form-control" style="font-size:0.8em;" id="con_acta" name="con_acta">
                                </div>
                                <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                            </div>

                            <div class="col-12 d-flex justify-content-center">
                                <button type="submit" class="btn bg-success text-white w-25" id="btn_enviar">
                                    <b>Guardar </b>
                                </button>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button"
                                    class="btn btn-primary btn-next-section px-4 d-inline-flex align-items-center"
                                    data-next="section8">
                                    Siguiente
                                    <i class="bi bi-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="section8" class="seccion-formulario">
                        <div class="mb-3 text-center">
                            <h3 class="h5 mb-1" style="color:#0F5699;">Otro Sí</h3>
                            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                                Llene por favor todos los campos.
                            </p>
                        </div>
                        <div class="row g-3">
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
                            <div class="col-12 mt-3">
                                <div id="unidadesGeneradas"></div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-guardar" id="btn_enviar">
                                    <b>Guardar </b>
                                </button>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button"
                                    class="btn btn-primary btn-prev-section px-4 d-inline-flex align-items-center"
                                    data-prev="section5">
                                    <i class="bi bi-arrow-left me-2"></i>
                                    Atrás
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="../js/scripts.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>


<script>
    // ===== UTILIDAD: MONEDA (UNA SOLA VEZ) =====
    function formatCurrency(input, hiddenId) {
        let value = input.value.replace(/\D/g, "");
        if (!value) {
            input.value = "";
            if (hiddenId) document.getElementById(hiddenId).value = "";
            return;
        }
        const number = parseInt(value, 10);
        const formatted = new Intl.NumberFormat("es-CO", {
            style: "currency",
            currency: "COP",
            minimumFractionDigits: 0
        }).format(number);

        input.value = formatted;
        if (hiddenId) document.getElementById(hiddenId).value = number;
    }

    // ===== STEPPER (UNA SOLA LÓGICA) =====
    document.addEventListener('DOMContentLoaded', function() {
        const steps = document.querySelectorAll('.step');
        const sections = document.querySelectorAll('.seccion-formulario');
        const progress = document.getElementById('stepper-progress');

        if (!steps.length || !sections.length) return;

        let currentStep = 0;

        function updateProgress() {
            const percent = (currentStep / (steps.length - 1)) * 100;
            if (progress) progress.style.width = percent + '%';
        }

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

    // ===== LÓGICA DE CAMPOS / VALIDACIONES =====
    document.addEventListener('DOMContentLoaded', function() {
        // Duración contrato
        const fechaInicio = document.getElementById("con_fecha_inicio");
        const fechaFinal = document.getElementById("con_fecha_final");
        const duracionInp = document.getElementById("con_duracion");
        const diasHidden = document.getElementById("con_duracion_hidden");
        const salarioHidden = document.getElementById("con_salario");
        const salarioVisible = document.getElementById("con_salario_mostrado");
        const valorVisible = document.getElementById("con_valor_proyecto_mostrado");
        const valorHidden = document.getElementById("con_valor_proyecto");


        if (fechaInicio && fechaFinal && duracionInp && diasHidden && salarioHidden && valorVisible && valorHidden) {

            recalcularTodo();

            ["input", "change"].forEach(evt => {
                fechaInicio.addEventListener(evt, recalcularTodo);
                fechaFinal.addEventListener(evt, recalcularTodo);
            });

            // Cuando cambie el salario visible, esperamos a que formatCurrency actualice el hidden
            ["input", "change"].forEach(evt => {
                if (salarioVisible) salarioVisible.addEventListener(evt, () => setTimeout(recalcularValorTotal, 0));
            });

            function parseYMD(ymd) {
                const [y, m, d] = ymd.split("-").map(Number);
                return new Date(y, m - 1, d);
            }

            // Diferencia en días INCLUSIVA (incluye inicio y fin)
            function diffDias(inicio, fin) {
                const msPorDia = 24 * 60 * 60 * 1000;
                return Math.round((fin - inicio) / msPorDia) + 1;
            }

            function recalcularTodo() {
                recalcularDuracion();
                recalcularValorTotal();
            }

            function recalcularDuracion() {
                const inicioStr = fechaInicio.value;
                const finStr = fechaFinal.value;

                if (!inicioStr || !finStr) {
                    duracionInp.value = "";
                    diasHidden.value = "";
                    return;
                }

                const dIni = parseYMD(inicioStr);
                const dFin = parseYMD(finStr);

                if (dFin < dIni) {
                    duracionInp.value = "Fecha final inválida";
                    diasHidden.value = "";
                    return;
                }

                const dias = diffDias(dIni, dFin);
                diasHidden.value = String(dias);
                duracionInp.value = `${dias} día${dias === 1 ? "" : "s"}`;
            }

            function esMesCompleto(dIni, dFin) {
                if (dIni.getFullYear() !== dFin.getFullYear()) return false;
                if (dIni.getMonth() !== dFin.getMonth()) return false;
                if (dIni.getDate() !== 1) return false;

                const ultimoDiaMes = new Date(dIni.getFullYear(), dIni.getMonth() + 1, 0).getDate();
                return dFin.getDate() === ultimoDiaMes;
            }

            function recalcularValorTotal() {
                const salario = Number(salarioHidden.value || 0);
                const inicioStr = fechaInicio.value;
                const finStr = fechaFinal.value;

                if (!salario || !inicioStr || !finStr) {
                    valorHidden.value = "";
                    valorVisible.value = "";
                    return;
                }

                const dIni = parseYMD(inicioStr);
                const dFin = parseYMD(finStr);

                if (dFin < dIni) {
                    valorHidden.value = "";
                    valorVisible.value = "";
                    return;
                }

                let total;
                if (esMesCompleto(dIni, dFin)) {
                    total = salario;
                } else {
                    const dias = diffDias(dIni, dFin);
                    const valorDia = salario / 30;
                    total = Math.round(valorDia * dias);
                }

                valorHidden.value = String(total);

                // Dejamos el formato igual al del resto (COP, sin decimales)
                const formatted = new Intl.NumberFormat("es-CO", {
                    style: "currency",
                    currency: "COP",
                    minimumFractionDigits: 0
                }).format(total);

                valorVisible.value = formatted;
            }
        }

        function calcularDuracion() {
            const inicio = inicioInput ? inicioInput.value : "";
            const final = finalInput ? finalInput.value : "";
            if (!inicio || !final) return;

            const fechaInicio = new Date(inicio);
            const fechaFinal = new Date(final);

            const out = document.getElementById("con_duracion");
            const outHidden = document.getElementById("con_duracion_hidden");

            if (!out || !outHidden) return;

            if (fechaFinal >= fechaInicio) {
                let años = fechaFinal.getFullYear() - fechaInicio.getFullYear();
                let meses = fechaFinal.getMonth() - fechaInicio.getMonth();
                let dias = fechaFinal.getDate() - fechaInicio.getDate();

                if (dias < 0) {
                    meses -= 1;
                    const mesAnterior = new Date(fechaFinal.getFullYear(), fechaFinal.getMonth(), 0).getDate();
                    dias += mesAnterior;
                }
                if (meses < 0) {
                    años -= 1;
                    meses += 12;
                }

                let texto = "";
                if (años > 0) texto += `${años} año${años > 1 ? "s" : ""} `;
                if (meses > 0) texto += `${meses} mes${meses > 1 ? "es" : ""} `;
                if (dias > 0) texto += `${dias} día${dias > 1 ? "s" : ""}`;

                const duracion = texto.trim() || "0 días";
                out.value = duracion;
                outHidden.value = duracion;
            } else {
                out.value = "Fecha final inválida";
                outHidden.value = "";
            }
        }

        if (inicioInput) inicioInput.addEventListener("change", calcularDuracion);
        if (finalInput) finalInput.addEventListener("change", calcularDuracion);

        // Edad
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
                if (m < 0 || (m === 0 && hoy.getDate() < fechaNac.getDate())) edad--;
                inputEdad.value = isNaN(edad) ? '' : edad;
            });
        }

        // Discapacidad (si existen esos IDs en tu HTML)
        function mostrarCertificado() {
            const select = document.getElementById('incapacitado');
            const campoCual = document.getElementById('campo_cual');
            const grupoCert = document.getElementById('grupo_cerIncapacidad');
            const inputCual = document.getElementById('con_discapacidad');
            if (!select || !campoCual || !grupoCert) return;

            if (select.value === 'si') {
                campoCual.style.display = 'block';
                grupoCert.style.display = 'block';
                if (inputCual) inputCual.required = true;
            } else {
                campoCual.style.display = 'none';
                grupoCert.style.display = 'none';
                if (inputCual) {
                    inputCual.required = false;
                    inputCual.value = '';
                }
                const fileInput = document.getElementById('con_cerIncapacidad');
                if (fileInput) fileInput.value = '';
            }
        }
        mostrarCertificado();

        // Mayúsculas
        document.querySelectorAll("input[type='text']").forEach(function(input) {
            input.addEventListener("input", function() {
                this.value = this.value.toUpperCase();
            });
        });

        // Mensajes por GET
        const params = new URLSearchParams(window.location.search);
        const estado = params.get('guardado');

        if (estado === 'ok') {
            Swal.fire({
                    icon: 'success',
                    title: '¡Guardado!',
                    text: 'Los datos se guardaron correctamente.'
                })
                .then(() => window.location.href = '../../index.php?page=Personal/personal_activo');
        } else if (estado === 'error') {
            Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Hubo un problema al guardar los datos.'
                })
                .then(() => window.location.href = '../../index.php?page=Personal/personal_activo');
        } else if (estado === 'invalido') {
            Swal.fire({
                    icon: 'warning',
                    title: 'Número de identidad no válido',
                    text: 'No se recibió el número de identidad.'
                })
                .then(() => window.location.href = '../../index.php?page=Personal/personal_activo');
        }
    });

    // ===== OTRO SÍ (TU FUNCIÓN ORIGINAL) =====
    function crearOtrosi() {
        var cantidad = document.getElementById("otr_otrosi").value;
        var formularios = [];

        document.getElementById("unidadesGeneradas").innerHTML = "";

        for (var i = 0; i < cantidad; i++) {
            var card = document.createElement("div");
            card.className = "card shadow-lg border-0 mt-4 otrosi-card";

            var cardHeader = document.createElement("div");
            cardHeader.className = "card-header otrosi-header";
            cardHeader.innerHTML = "<h2 class='text-center font-weight-light my-2'>Otro sí " + (i + 1) + "</h2>";

            var cardBody = document.createElement("div");
            cardBody.className = "card-body";

            cardBody.innerHTML = `
        <form id="formulario_otrosi_${i}" action="/Arbimaps/vistas/Personal/otrosi_guardar.php" method="POST" enctype="multipart/form-data">
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

        var botonWrapper = document.createElement("div");
        botonWrapper.className = "mt-3";

        var botonEnviar = document.createElement("button");
        botonEnviar.type = "button";
        botonEnviar.textContent = "Enviar Formularios";
        botonEnviar.className = "btn btn-guardar w-100 text-center";

        botonEnviar.addEventListener("click", function(event) {
            event.preventDefault();

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
                            try {
                                const data = JSON.parse(text);
                                if (data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Éxito',
                                        text: data.message || 'Datos guardados correctamente.'
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: data.message || 'Ocurrió un error en el servidor.'
                                    });
                                }
                            } catch (e) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error de respuesta',
                                    text: 'La respuesta del servidor no tiene el formato esperado.'
                                });
                            }
                        })
                        .catch(() => {
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
                        text: 'Por favor, completa todos los campos requeridos en el formulario ' + (index + 1) + '.',
                        confirmButtonText: 'Entendido'
                    });
                }
            });
        });

        botonWrapper.appendChild(botonEnviar);
        document.getElementById("unidadesGeneradas").appendChild(botonWrapper);
    }
</script>