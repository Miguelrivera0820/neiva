<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../../../conexion.php';

$idUsuario = $_SESSION['id_usuario'] ?? null;

$row = [];

$datosSesionUsuario = [
    'cedula_usuario'   => '',
    'nombre_usuario'   => '',
    'apellido_usuario' => '',
    'correo_usuario'   => '',
    'celular_usuario'  => ''
];

if (!empty($idUsuario)) {
    $sqlUsuarioSesion = "SELECT 
                            id_usuario,
                            cedula_usuario,
                            nombre_usuario,
                            apellido_usuario,
                            correo_usuario,
                            celular_usuario
                        FROM usuarios_cons
                        WHERE id_usuario = ?";
    $stmtUsuarioSesion = $mysqli->prepare($sqlUsuarioSesion);

    if ($stmtUsuarioSesion) {
        $stmtUsuarioSesion->bind_param("i", $idUsuario);
        $stmtUsuarioSesion->execute();
        $resUsuarioSesion = $stmtUsuarioSesion->get_result();

        if ($resUsuarioSesion && $resUsuarioSesion->num_rows > 0) {
            $datosSesionUsuario = $resUsuarioSesion->fetch_assoc();
        }
        $stmtUsuarioSesion->close();
    }
}

$con_nombres            = $_POST['con_nombres'] ?? "";
$con_apellidos          = $_POST['con_apellidos'] ?? "";
$con_tipo_documento     = $_POST['con_tipo_documento'] ?? "";
$con_num_identidad      = $_GET['con_num_identidad'] ?? ($datosSesionUsuario['cedula_usuario'] ?? "");
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
$con_contingencia       = $_POST['con_contingencia'] ?? "";
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

$con_genero             = $_POST['con_genero'] ?? "";
$con_raza               = $_POST['con_raza'] ?? "";
$con_vivienda           = $_POST['con_vivienda'] ?? "";
$con_estrato            = $_POST['con_estrato'] ?? "";
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


if (empty($con_nombres)) {
    $con_nombres = $datosSesionUsuario['nombre_usuario'] ?? "";
}

if (empty($con_apellidos)) {
    $con_apellidos = $datosSesionUsuario['apellido_usuario'] ?? "";
}

if (empty($con_num_identidad)) {
    $con_num_identidad = $datosSesionUsuario['cedula_usuario'] ?? "";
}

if (empty($con_correo)) {
    $con_correo = $datosSesionUsuario['correo_usuario'] ?? "";
}

if (empty($con_celular)) {
    $con_celular = $datosSesionUsuario['celular_usuario'] ?? "";
}



if (!empty($con_num_identidad)) {
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
        $con_contingencia       = $row['con_contingencia']       ?? $con_contingencia;
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

        $con_genero             = $row['con_genero']             ?? $con_genero;
        $con_raza               = $row['con_raza']               ?? $con_raza;
        $con_vivienda           = $row['con_vivienda']           ?? $con_vivienda;
        $con_estrato            = $row['con_estrato']            ?? $con_estrato;
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
    }
}

function ajustarRutaDocumento($rutaBD)
{
    if (empty($rutaBD)) {
        return [null, null];
    }
    $prefijoCorrecto = neiva_app_url('Arbimaps/vistas/Personal/');
    if (strpos($rutaBD, $prefijoCorrecto) === 0) {
        $rutaWeb = $rutaBD;
    } else {
        $prefijoViejo = neiva_app_url('Arbimaps/');
        if (strpos($rutaBD, $prefijoViejo) === 0) {
            $rutaBD = substr($rutaBD, strlen($prefijoViejo));
        }
        $rutaWeb = $prefijoCorrecto . ltrim($rutaBD, "/");
    }
    $rutaFisica = $_SERVER['DOCUMENT_ROOT'] . $rutaWeb;
    return [$rutaWeb, $rutaFisica];
}

function renderDocumentoCampo($label, $valorBD, $collapseId, $inputName, $inputId = null)
{
    $inputId = $inputId ?: $inputName;

    $rutaWeb = null;
    $rutaFisica = null;
    $existe = false;

    if (!empty($valorBD)) {
        list($rutaWeb, $rutaFisica) = ajustarRutaDocumento($valorBD);
        $existe = ($rutaWeb && $rutaFisica && file_exists($rutaFisica));
    }
?>
    <div class="col-12 col-lg-4 p-1 px-2 my-4">
        <label class="form-label fw-bold mb-1" style="font-size:0.9em">
            <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
        </label>

        <?php if ($existe): ?>
            <div class="input-group shadow-sm rounded-3">
                <span class="input-group-text">
                    <i class="bi bi-file-earmark-pdf"></i>
                </span>

                <input type="text"
                    class="form-control"
                    value="<?php echo htmlspecialchars((string)$valorBD, ENT_QUOTES, 'UTF-8'); ?>"
                    readonly>

                <div class="input-group-append">
                    <button type="button"
                        class="bot_mostrar_vista btn"
                        onclick="abrirModalInforme('<?php echo htmlspecialchars($rutaWeb, ENT_QUOTES, 'UTF-8'); ?>')">
                        <i class="bi bi-eye"></i>
                    </button>

                    <button type="button"
                        class="btn btn-outline-secondary"
                        data-toggle="collapse"
                        data-target="#<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>"
                        aria-expanded="false"
                        aria-controls="<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>
                </div>
            </div>

            <div class="collapse mt-2" id="<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="input-group shadow-sm rounded-3">
                    <span class="input-group-text">
                        <i class="bi bi-upload"></i>
                    </span>
                    <input type="file"
                        class="form-control"
                        id="<?php echo htmlspecialchars($inputId, ENT_QUOTES, 'UTF-8'); ?>"
                        name="<?php echo htmlspecialchars($inputName, ENT_QUOTES, 'UTF-8'); ?>"
                        accept="application/pdf,image/*">
                </div>
            </div>
        <?php else: ?>
            <div class="input-group shadow-sm rounded-3">
                <span class="input-group-text">
                    <i class="bi bi-upload"></i>
                </span>
                <input type="file"
                    class="form-control"
                    id="<?php echo htmlspecialchars($inputId, ENT_QUOTES, 'UTF-8'); ?>"
                    name="<?php echo htmlspecialchars($inputName, ENT_QUOTES, 'UTF-8'); ?>"
                    accept="application/pdf,image/*">
            </div>
        <?php endif; ?>
    </div>
<?php
}

function renderDocumentoCampoCol($label, $valorBD, $collapseId, $inputName, $colClass = 'col-12 col-lg-4', $inputId = null)
{
    $inputId = $inputId ?: $inputName;

    $rutaWeb = null;
    $rutaFisica = null;
    $existe = false;

    if (!empty($valorBD)) {
        list($rutaWeb, $rutaFisica) = ajustarRutaDocumento($valorBD);
        $existe = ($rutaWeb && $rutaFisica && file_exists($rutaFisica));
    }
?>
    <div class="<?php echo htmlspecialchars($colClass, ENT_QUOTES, 'UTF-8'); ?> p-1 px-2 my-4">
        <label class="form-label fw-bold mb-1" style="font-size:0.9em">
            <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
        </label>

        <?php if ($existe): ?>
            <div class="input-group shadow-sm rounded-3">
                <span class="input-group-text">
                    <i class="bi bi-file-earmark-pdf"></i>
                </span>

                <input type="text"
                    class="form-control"
                    value="<?php echo htmlspecialchars((string)$valorBD, ENT_QUOTES, 'UTF-8'); ?>"
                    readonly>

                <div class="input-group-append">
                    <button type="button"
                        class="bot_mostrar_vista btn"
                        onclick="abrirModalInforme('<?php echo htmlspecialchars($rutaWeb, ENT_QUOTES, 'UTF-8'); ?>')">
                        <i class="bi bi-eye"></i>
                    </button>

                    <button type="button"
                        class="btn btn-outline-secondary"
                        data-toggle="collapse"
                        data-target="#<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>"
                        aria-expanded="false"
                        aria-controls="<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>
                </div>
            </div>

            <div class="collapse mt-2" id="<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="input-group shadow-sm rounded-3">
                    <span class="input-group-text">
                        <i class="bi bi-upload"></i>
                    </span>
                    <input type="file"
                        class="form-control"
                        id="<?php echo htmlspecialchars($inputId, ENT_QUOTES, 'UTF-8'); ?>"
                        name="<?php echo htmlspecialchars($inputName, ENT_QUOTES, 'UTF-8'); ?>"
                        accept="application/pdf,image/*">
                </div>
                <small class="text-muted d-block mt-1">Selecciona un archivo para reemplazar el actual.</small>
            </div>
        <?php else: ?>
            <div class="input-group shadow-sm rounded-3">
                <span class="input-group-text">
                    <i class="bi bi-upload"></i>
                </span>
                <input type="file"
                    class="form-control"
                    id="<?php echo htmlspecialchars($inputId, ENT_QUOTES, 'UTF-8'); ?>"
                    name="<?php echo htmlspecialchars($inputName, ENT_QUOTES, 'UTF-8'); ?>"
                    accept="application/pdf,image/*">
            </div>
            <small class="text-muted d-block mt-1">Cargue el documento en PDF o imagen.</small>
        <?php endif; ?>
    </div>
<?php
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

    .title-vacunas {
        text-align: left !important;
        margin-bottom: 10px;
    }

    .vaccines-box {
        border: 2px dotted #003B66;
        padding: 10px;
        border-radius: 10px;
        background: none !important;
        margin-top: 10px;
    }

    .checklist-item {
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .swal-card {
        width: 360px;
        margin-left: 23%;
        max-width: 92vw;
        padding: 26px 22px 18px 22px;
        border-radius: 16px;
        box-shadow: 0 14px 35px rgba(0, 0, 0, .15);
        background: #fff;
        position: relative;
        overflow: hidden;
        text-align: center;
        font-family: inherit;
    }

    .swal-card::before {
        content: "";
        position: absolute;
        inset: 0;
        opacity: .9;
        background-image:
            radial-gradient(circle at 12% 18%, rgba(0, 0, 0, .10) 2px, transparent 3px),
            radial-gradient(circle at 82% 22%, rgba(0, 0, 0, .10) 2px, transparent 3px),
            radial-gradient(circle at 72% 64%, rgba(0, 0, 0, .10) 2px, transparent 3px),
            radial-gradient(circle at 22% 72%, rgba(0, 0, 0, .10) 2px, transparent 3px),
            radial-gradient(circle at 45% 35%, rgba(0, 0, 0, .08) 1.5px, transparent 2.5px),
            radial-gradient(circle at 55% 80%, rgba(0, 0, 0, .08) 1.5px, transparent 2.5px);
        pointer-events: none;
    }

    .swal-title-like {
        position: relative;
        margin: 0;
        font-weight: 700;
        font-size: 22px;
        color: #2f2f2f;
    }

    .swal-sub-like {
        position: relative;
        margin: 10px 0 18px 0;
        font-size: 13px;
        color: #8b8b8b;
        line-height: 1.25rem;
    }

    .swal-icon-wrap {
        position: relative;
        width: 120px;
        height: 120px;
        margin: 18px auto 14px auto;
        border-radius: 999px;
        display: grid;
        place-items: center;
    }

    .swal-icon-wrap.green {
        background: rgba(46, 133, 204, 0.16);
    }

    .swal-icon-wrap.red {
        background: rgba(255, 76, 97, .16);
    }

    .swal-icon-circle {
        width: 86px;
        height: 86px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        color: #fff;
        font-size: 44px;
        font-weight: 800;
    }

    .swal-icon-circle.green {
        background: #002F55;
    }

    .swal-icon-circle.red {
        background: #ff4c61;
    }

    .swal-actions-like {
        position: relative;
        margin-top: 10px;
        display: grid;
        gap: 10px;
    }

    .swal-btn-like {
        width: 70%;
        border: 0;
        border-radius: 10px;
        padding: 14px 16px;
        font-weight: 800;
        letter-spacing: .4px;
        font-size: 13px;
        cursor: pointer;
    }

    .swal-btn-like.green {
        background: #002F55;
        color: #fff;
    }

    .swal-btn-like.red {
        background: #ff4c61;
        color: #fff;
    }

    .swal2-popup.swal2-modal.custom-swal-popup {
        padding: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .swal2-html-container.custom-swal-html {
        margin: 0 !important;
        padding: 0 !important;
    }

    .swal-actions-row {
        display: flex !important;
        justify-content: center;
        gap: 10px;
    }

    .swal-mini-btn {
        width: 36px;
        height: 36px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .swal-mini-btn i {
        font-size: 16px;
        line-height: 1;
    }
</style>
<div class="container-fluid px-3">

    <div class="my-4 text-center">
        <h4 class=" mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 700 !important ">SECCIÓN DE INFORMACIÓN OTRO SÍ </h4>
        <small> Modulo para la consulta de la información del contrato otro si</small>
    </div>

    <div class="card-form p-3">

        <div class="stepper-wrapper  mb-0 justify-content-center">
            <div class="stepper-line">
                <div id="stepper-progress"></div>
            </div>

            <div class="stepper">
                <button type="button" class="step active" onclick="seccion(1)">Datos Personales</button>
                <button type="button" class="step" onclick="seccion(2)">Núcleo Familiar</button>
                <button type="button" class="step" onclick="seccion(3)">Datos Financieros</button>
                <button type="button" class="step" onclick="seccion(4)">Historial Académico</button>
                <button type="button" class="step" onclick="seccion(6)">Información Sociodemográfica</button>
                <button type="button" class="step" onclick="seccion(7)">Historial de Vacunación</button>
            </div>
        </div>

        <div class="card-body ">
            <form id="formContratacion" action="<?= neiva_app_url('Arbimaps/vistas/Personal/acciones/agregar_perfil_contratacion.php') ?>" method="POST" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="origen" value="<?php echo !empty($row['con_id']) ? 'editar' : 'crear'; ?>">
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
                                        <input
                                            type="file"
                                            id="photo"
                                            name="photo"
                                            accept="image/*"
                                            class="d-none">

                                        <?php if (!empty($row['photo'])): ?>
                                            <?php list($rutaWeb, $rutaFisica) = ajustarRutaDocumento($row['photo']); ?>

                                            <?php if ($rutaWeb && file_exists($rutaFisica)): ?>
                                                <div class="text-center mt-3">
                                                    <div class="photo-wrapper photo-fixed" id="photoPicker" role="button" tabindex="0">
                                                        <img
                                                            id="photoPreview"
                                                            src="<?php echo htmlspecialchars($rutaWeb, ENT_QUOTES, 'UTF-8'); ?>"
                                                            alt="Foto del colaborador"
                                                            class="photo-img">
                                                        <div class="photo-overlay" title="Cambiar foto">
                                                            <i class="bi bi-camera"></i>
                                                            <span>Cambiar foto</span>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted d-block mt-2">Haz clic sobre la imagen para cambiarla.</small>
                                                </div>
                                            <?php else: ?>
                                                <div class="alert alert-warning mt-3 small text-center">
                                                    <i class="bi bi-exclamation-triangle"></i>
                                                    La foto está registrada, pero no se encuentra en el servidor.
                                                </div>

                                                <div class="text-center mt-3">
                                                    <div class="photo-wrapper photo-fixed" id="photoPicker" role="button" tabindex="0">
                                                        <img id="photoPreview" src="" alt="Sin foto" class="photo-img" style="display:none;">
                                                        <div class="photo-overlay" style="opacity:1;">
                                                            <i class="bi bi-camera"></i>
                                                            <span>Subir foto</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                        <?php else: ?>
                                            <div class="text-center mt-3">
                                                <div class="photo-wrapper photo-fixed" id="photoPicker" role="button" tabindex="0">
                                                    <img id="photoPreview" src="" alt="Sin foto" class="photo-img" style="display:none;">
                                                    <div class="photo-overlay" style="opacity:1;">
                                                        <i class="bi bi-camera"></i>
                                                        <span>Subir foto</span>
                                                    </div>
                                                </div>
                                                <small class="text-muted d-block mt-2">Haz clic para cargar una foto.</small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-12 col-lg-9">
                                    <div class="row">
                                        <div class="col-md-6 p-1 px-2 my-1">
                                            <label for="con_nombres" class="form-label fw-bold" style="font-size:0.9em;">Nombres</label>
                                            <div class="input-group">
                                                <span class="input-group-text shadow-sm"><i class="bi bi-person"></i></span>
                                                <input type="text"
                                                    class="form-control"
                                                    id="con_nombres"
                                                    name="con_nombres"
                                                    value="<?php echo htmlspecialchars($con_nombres ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                    readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-6 p-1 px-2 my-1">
                                            <label for="con_apellidos" class="form-label fw-bold" style="font-size:0.9em;">Apellidos</label>
                                            <div class="input-group">
                                                <span class="input-group-text shadow-sm" id="basic-addon1"><i class="bi-people"></i></span>
                                                <input type="text"
                                                    class="form-control"
                                                    id="con_apellidos"
                                                    name="con_apellidos"
                                                    value="<?php echo htmlspecialchars($con_apellidos ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                    readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-4 p-1 px-2 my-1">
                                            <label for="con_tipo_documento" class="form-label" style="font-size:0.9em;">Tipo de documento</label>
                                            <div class="input-group shadow-sm">
                                                <label class="input-group-text" for="con_tipo_documento">
                                                    <i class="bi-person-badge"></i>
                                                </label>
                                                <select class="form-select" id="con_tipo_documento" name="con_tipo_documento">
                                                    <option value="">Seleccione...</option>
                                                    <option value="CEDULA_CIUDADANIA" <?php echo ($con_tipo_documento === 'CEDULA_CIUDADANIA') ? 'selected' : ''; ?>>Cédula de Ciudadanía</option>
                                                    <option value="CEDULA_EXTRANJERA" <?php echo ($con_tipo_documento === 'CEDULA_EXTRANJERA') ? 'selected' : ''; ?>>Cédula de Extranjería</option>
                                                    <option value="NIT" <?php echo ($con_tipo_documento === 'NIT') ? 'selected' : ''; ?>>N.I.T.</option>
                                                    <option value="PASAPORTE" <?php echo ($con_tipo_documento === 'PASAPORTE') ? 'selected' : ''; ?>>Pasaporte</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4 p-1 px-2 my-1">
                                            <label for="con_num_identidad" class="form-label fw-bold" style="font-size:0.9em;">Número de identidad</label>
                                            <div class="input-group ">
                                                <span class="input-group-text shadow-sm"><i class="bi bi-person-vcard"></i></span>
                                                <input class="form-control"
                                                    name="con_num_identidad"
                                                    id="con_num_identidad"
                                                    value="<?php echo htmlspecialchars($con_num_identidad ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                    readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-4 p-1 px-2 my-1">
                                            <label for="con_celular" class="form-label fw-bold" style="font-size:0.9em;">Celular</label>
                                            <div class="input-group ">
                                                <span class="input-group-text shadow-sm"><i class="bi bi-phone"></i></span>
                                                <input class="form-control" type="text" name="con_celular" id="con_celular" value="<?php echo htmlspecialchars($con_celular ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-4 p-1 px-2 my-1">
                                            <label for="con_direccion" class="form-label fw-bold" style="font-size:0.9em;">Dirección</label>
                                            <div class="input-group ">
                                                <span class="input-group-text shadow-sm"><i class="bi bi-geo-alt"></i></span>
                                                <input class="form-control" type="text" name="con_direccion" id="con_direccion" value="<?php echo htmlspecialchars($con_direccion ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-4 p-1 px-2 my-1">
                                            <label for="con_edad" class="form-label fw-bold" style="font-size:0.9em;">Edad</label>
                                            <div class="input-group ">
                                                <span class="input-group-text shadow-sm"><i class="bi bi-cake"></i></span>
                                                <input class="form-control" type="text" name="con_edad" id="con_edad" value="<?php echo htmlspecialchars($con_edad ?? '', ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-4 p-1 px-2 my-1">
                                            <label for="con_barrio" class="form-label fw-bold" style="font-size:0.9em;">Barrio</label>
                                            <div class="input-group ">
                                                <span class="input-group-text shadow-sm"><i class="bi bi-houses"></i></span>
                                                <input class="form-control" type="text" name="con_barrio" id="con_barrio" value="<?php echo htmlspecialchars($con_barrio ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-4 p-1 px-2 my-1">
                                            <label for="con_ciudad" class="form-label fw-bold" style="font-size:0.9em;">Ciudad</label>
                                            <div class="input-group ">
                                                <span class="input-group-text shadow-sm"><i class="bi bi-houses"></i></span>
                                                <input class="form-control" type="text" name="con_ciudad" id="con_ciudad" value="<?php echo htmlspecialchars($con_ciudad ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-4 p-1 px-2 my-1">
                                            <label for="con_FechaExpe" class="form-label fw-bold" style="font-size:0.8em;">Fecha de Expedición del Documento</label>
                                            <div class="input-group ">
                                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar2-event"></i></span>
                                                <input class="form-control" type="date" name="con_FechaExpe" id="con_FechaExpe" value="<?php echo htmlspecialchars($con_FechaExpe ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-4 p-1 px-2 my-1">
                                            <label for="con_lugarE" class="form-label fw-bold" style="font-size:0.8em;">Lugar de expedición del documento</label>
                                            <div class="input-group ">
                                                <span class="input-group-text shadow-sm"><i class="bi bi-geo"></i></span>
                                                <input class="form-control" type="text" name="con_lugarE" id="con_lugarE" value="<?php echo htmlspecialchars($con_lugarE ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-4 p-1 px-2 my-1">
                                            <label for="con_fecha_nacimiento" class="form-label fw-bold" style="font-size:0.9em;">Fecha de nacimiento</label>
                                            <div class="input-group ">
                                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar-date"></i></span>
                                                <input class="form-control" type="date" name="con_fecha_nacimiento" id="con_fecha_nacimiento" value="<?php echo htmlspecialchars($con_fecha_nacimiento ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-4 p-1 px-2 my-1">
                                            <label for="con_lugar_nacimiento" class="form-label fw-bold" style="font-size:0.9em;">Lugar de nacimiento</label>
                                            <div class="input-group ">
                                                <span class="input-group-text shadow-sm"><i class="bi bi-pin-map-fill"></i></span>
                                                <input class="form-control" type="text" name="con_lugar_nacimiento" id="con_lugar_nacimiento" value="<?php echo htmlspecialchars($con_lugar_nacimiento ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-4 p-1 px-2 my-1">
                                            <label for="con_tel_fijo" class="form-label fw-bold" style="font-size:0.9em;">Teléfono fijo (Opcional)</label>
                                            <div class="input-group ">
                                                <span class="input-group-text shadow-sm"><i class="bi bi-telephone"></i></span>
                                                <input class="form-control" type="text" name="con_tel_fijo" id="con_tel_fijo" value="<?php echo htmlspecialchars($con_tel_fijo ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-6 p-1 px-2 my-1">
                                            <label for="con_correo" class="form-label fw-bold" style="font-size:0.9em;">Correo electrónico personal</label>
                                            <div class="input-group shadow-sm">
                                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at"></i></span>
                                                <input class="form-control" type="email" placeholder="correo@ejemplo.com" name="con_correo" id="con_correo" value="<?php echo htmlspecialchars($con_correo ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-6 mx-auto p-1 px-2 my-1">
                                            <label for="con_correo_corporativo" class="form-label fw-bold" style="font-size:0.9em;">Correo electrónico corporativo</label>
                                            <div class="input-group shadow-sm">
                                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at"></i></span>
                                                <input class="form-control" type="email" placeholder="correo.corporativo@empresa.com" name="con_correo_corporativo" id="con_correo_corporativo" value="<?php echo htmlspecialchars($con_correo_corporativo ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                        </div>

                                        <div class="row px-4 mt-5">
                                            <div class="col-12">
                                                <h6 class="fw-bold p-2 text-white rounded-3 text-start px-3 mb-3" style="background-color: #002F55;">
                                                    <i class="bi bi-folder2-open me-2"></i> Documentos
                                                </h6>
                                            </div>

                                            <?php
                                            renderDocumentoCampo('Cédula de ciudadanía', $cedula, 'collapse_cedula', 'con_cedula');
                                            renderDocumentoCampo('RUT', $rut, 'collapse_rut', 'con_rut');
                                            renderDocumentoCampo('Antecedentes Judiciales', $judiciales, 'collapse_antecedentes', 'con_antecedentes');
                                            renderDocumentoCampo('Antecedentes de la Contraloría', $contraloria, 'collapse_contraloria', 'con_contraloria');
                                            renderDocumentoCampo('Antecedentes de la Procuraduría', $procuraduria, 'collapse_procuraduria', 'con_procuraduria');
                                            renderDocumentoCampo('Hoja de vida', $hojavida, 'collapse_hoja_vida', 'con_hoja_vida');
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-center">
                        <button type="button" class="btn text-white" style="background-color: #002F55;" onclick="nextSection()">
                            Siguiente <i class="bi bi-arrow-right ms-2"></i> </button>
                    </div>

                </div>

                <!-- Sección 2: Información familiar -->
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
                            <label for="con_estado_civil" class="form-label" style="font-size:0.9em;">Estado civil</label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_estado_civil">
                                    <i class="bi bi-heart"></i>
                                </label>
                                <select class="form-select" id="con_estado_civil" name="con_estado_civil" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="SOLTERO" <?= ($con_estado_civil == "SOLTERO" ? "selected" : "") ?>>SOLTERO</option>
                                    <option value="CASADO" <?= ($con_estado_civil == "CASADO" ? "selected" : "") ?>>CASADO</option>
                                    <option value="UNIÓN LIBRE" <?= ($con_estado_civil == "UNIÓN LIBRE" ? "selected" : "") ?>>UNIÓN LIBRE</option>
                                    <option value="VIUDO" <?= ($con_estado_civil == "VIUDO" ? "selected" : "") ?>>VIUDO</option>
                                    <option value="SEPARADO" <?= ($con_estado_civil == "SEPARADO" ? "selected" : "") ?>>SEPARADO</option>
                                    <option value="DIVORCIADO" <?= ($con_estado_civil == "DIVORCIADO" ? "selected" : "") ?>>DIVORCIADO</option>
                                    <option value="UNIÓN DE HECHO" <?= ($con_estado_civil == "UNIÓN DE HECHO" ? "selected" : "") ?>>UNIÓN DE HECHO</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_nombre_conyuge" class="form-label fw-bold" style="font-size:0.9em;">Nombre del cónyuge</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm" id="basic-addon1"><i class="bi-people"></i></span>
                                <input class="form-control" placeholder="Ingrese el nombre" name="con_nombre_conyuge" id="con_nombre_conyuge" value="<?= htmlspecialchars($con_nombre_conyuge ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_enfermedades" class="form-label fw-bold" style="font-size:0.9em;">Enfermedades</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm" id="basic-addon1"><i class="bi bi-bandaid"></i></span>
                                <input class="form-control" type="text" placeholder="Ingrese las enfermedades" name="con_enfermedades" id="con_enfermedades" value="<?= htmlspecialchars($con_enfermedades ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_alergias" class="form-label fw-bold" style="font-size:0.9em;">Alergias</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm" id="basic-addon1"><i class="bi bi-bandaid"></i></span>
                                <input class="form-control" placeholder="Ingrese las alergias" name="con_alergias" id="con_alergias" value="<?= htmlspecialchars($con_alergias ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_medicamentos" class="form-label fw-bold" style="font-size:0.9em;">Medicamentos</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm" id="basic-addon1"><i class="bi bi-prescription2"></i></span>
                                <input class="form-control" placeholder="Ingrese los medicamentos" name="con_medicamentos" id="con_medicamentos" value="<?= htmlspecialchars($con_medicamentos ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_contingencia" class="form-label fw-bold" style="font-size:0.9em;">Plan de contingencia</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm" id="basic-addon1"><i class="bi bi-prescription2"></i></span>
                                <input class="form-control" placeholder="Ingrese el pan de contingencia" name="con_contingencia" id="con_contingencia" value="<?php echo $con_contingencia; ?>">
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
                                <input class="form-control" placeholder="Ingrese el nombre del contacto de emergencia" style="border: 1px solid #002F55;" name="con_emergencia" id="con_emergencia" value="<?= htmlspecialchars($con_emergencia ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_parentesco" class="form-label fw-bold" style="font-size:0.9em;">Parentesco </label>
                            <div class="input-group shadow">
                                <span class="input-group-text shadow-sm " style="border: 1px solid #002F55;" id="basic-addon1"><i class="bi bi-person-badge"></i></span>
                                <input class="form-control" placeholder="Ingrese el parentesco" style="border: 1px solid #002F55;" name="con_parentesco" id="con_parentesco" value="<?= htmlspecialchars($con_parentesco ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_tel_emergencia" class="form-label fw-bold" style="font-size:0.9em;">Número telefónico de contacto de emergencia </label>
                            <div class="input-group shadow ">
                                <span class="input-group-text shadow-sm" style="border: 1px solid #002F55;" id="basic-addon1"><i class="bi bi-telephone-x"></i></span>
                                <input class="form-control" placeholder="Ingrese el número telefónico" style="border: 1px solid #002F55;" name="con_tel_emergencia" id="con_tel_emergencia" value="<?= htmlspecialchars($con_tel_emergencia ?? '', ENT_QUOTES, 'UTF-8'); ?>">
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
                <?php
                $eps_personalizada = '';
                $eps_predefinidas = [
                    "NUEVA_EPS",
                    "SANITAS",
                    "EMSSANAR",
                    "MALLAMAS",
                    "ASMET_SALUD",
                    "FAMISANAR",
                    "SURA",
                    "OTRO"
                ];
                if (!empty($con_eps) && !in_array($con_eps, $eps_predefinidas, true)) {
                    $eps_personalizada = $con_eps;
                    $con_eps = 'OTRO';
                }
                ?>

                <div class="form-section d-none " id="section3">
                    <?php
                    $banco   = $row['con_bancario'] ?? null;
                    $cer_eps = $row['con_certificado_eps'] ?? null;
                    $cer_arl = $row['con_arlCer'] ?? null;
                    ?>

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
                                <input class="form-control" type="text" name="con_num_cuenta" placeholder="Ingrese el número de cuenta bancaria" id="con_num_cuenta" value="<?= htmlspecialchars($con_num_cuenta ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_tipo_cuenta" class="form-label" style="font-size:0.9em;">Tipo de cuenta bancaria</label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_tipo_cuenta">
                                    <i class="bi bi-safe"></i>
                                </label>
                                <select class="form-select" id="con_tipo_cuenta" name="con_tipo_cuenta" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="AHORROS" <?= ($con_tipo_cuenta == "AHORROS"   ? "selected" : "") ?>>AHORROS</option>
                                    <option value="CORRIENTE" <?= ($con_tipo_cuenta == "CORRIENTE" ? "selected" : "") ?>>CORRIENTE</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_financiera" class="form-label" style="font-size:0.9em;">Entidad financiera</label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_financiera">
                                    <i class="bi bi-safe"></i>
                                </label>
                                <select class="form-select" id="con_financiera" name="con_financiera" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="BANCOLOMBIA" <?= ($con_financiera == "BANCOLOMBIA"               ? "selected" : "") ?>>BANCOLOMBIA</option>
                                    <option value="DAVIVIENDA" <?= ($con_financiera == "DAVIVIENDA"                ? "selected" : "") ?>>DAVIVIENDA</option>
                                    <option value="BANCO DE BOGOTA S.A." <?= ($con_financiera == "BANCO DE BOGOTA S.A."      ? "selected" : "") ?>>BANCO DE BOGOTÁ S.A.</option>
                                    <option value="BBVA COLOMBIA" <?= ($con_financiera == "BBVA COLOMBIA"             ? "selected" : "") ?>>BBVA COLOMBIA</option>
                                    <option value="BANCO DE OCCIDENTE" <?= ($con_financiera == "BANCO DE OCCIDENTE"        ? "selected" : "") ?>>BANCO DE OCCIDENTE</option>
                                    <option value="BANCO COLPATRIA" <?= ($con_financiera == "BANCO COLPATRIA"           ? "selected" : "") ?>>BANCO COLPATRIA</option>
                                    <option value="BANCO AGRARIO DE COLOMBIA" <?= ($con_financiera == "BANCO AGRARIO DE COLOMBIA" ? "selected" : "") ?>>BANCO AGRARIO DE COLOMBIA</option>
                                    <option value="BANCAMIA S.A." <?= ($con_financiera == "BANCAMIA S.A."             ? "selected" : "") ?>>BANCAMÍA S.A.</option>
                                    <option value="BANCO W S.A" <?= ($con_financiera == "BANCO W S.A"               ? "selected" : "") ?>>BANCO W S.A.</option>
                                    <option value="BANCO FALABELLA S.A." <?= ($con_financiera == "BANCO FALABELLA S.A."      ? "selected" : "") ?>>BANCO FALABELLA S.A.</option>
                                    <option value="BANCO UNION S.A" <?= ($con_financiera == "BANCO UNION S.A"           ? "selected" : "") ?>>BANCO UNIÓN S.A</option>
                                    <option value="BANCO MUNDO MUJER" <?= ($con_financiera == "BANCO MUNDO MUJER"         ? "selected" : "") ?>>BANCO MUNDO MUJER</option>
                                </select>
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
                            <label for="con_eps" class="form-label" style="font-size:0.9em;">Eps</label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_eps">
                                    <i class="bi bi-hospital"></i>
                                </label>
                                <select class="form-select" id="con_eps" name="con_eps">
                                    <option value="">SELECCIONE</option>
                                    <option value="NUEVA_EPS" <?= ($con_eps == "NUEVA_EPS" ? "selected" : "") ?>>NUEVA EPS</option>
                                    <option value="SANITAS" <?= ($con_eps == "SANITAS" ? "selected" : "") ?>>SANITAS</option>
                                    <option value="EMSSANAR" <?= ($con_eps == "EMSSANAR" ? "selected" : "") ?>>EMSSANAR</option>
                                    <option value="MALLAMAS" <?= ($con_eps == "MALLAMAS" ? "selected" : "") ?>>MALLAMAS</option>
                                    <option value="ASMET_SALUD" <?= ($con_eps == "ASMET_SALUD" ? "selected" : "") ?>>ASMET SALUD</option>
                                    <option value="FAMISANAR" <?= ($con_eps == "FAMISANAR" ? "selected" : "") ?>>FAMISANAR</option>
                                    <option value="SURA" <?= ($con_eps == "SURA" ? "selected" : "") ?>>SURA</option>
                                    <option value="OTRO" <?= ($con_eps == "OTRO" ? "selected" : "") ?>>OTRO</option>
                                </select>
                            </div>

                            <input type="text"
                                class="form-control mt-2"
                                id="otra_eps"
                                name="otra_eps"
                                placeholder="Escriba la EPS"
                                style="<?= ($eps_personalizada || $con_eps == 'OTRO') ? '' : 'display:none;'; ?>"
                                value="<?= htmlspecialchars($eps_personalizada ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_afp" class="form-label" style="font-size:0.9em;">AFP</label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_afp">
                                    <i class="bi bi-clipboard"></i>
                                </label>
                                <select class="form-select" id="con_afp" name="con_afp" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="PORVENIR" <?= ($con_afp == "PORVENIR"    ? "selected" : "") ?>>PORVENIR</option>
                                    <option value="COLFONDOS" <?= ($con_afp == "COLFONDOS"   ? "selected" : "") ?>>COLFONDOS</option>
                                    <option value="COLPENSIONES" <?= ($con_afp == "COLPENSIONES" ? "selected" : "") ?>>COLPENSIONES</option>
                                    <option value="PROTECCIÓN" <?= ($con_afp == "PROTECCIÓN"  ? "selected" : "") ?>>PROTECCIÓN</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_arl" class="form-label" style="font-size:0.9em;">ARL</label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_arl">
                                    <i class="bi bi-cone-striped"></i>
                                </label>
                                <select class="form-select" id="con_arl" name="con_arl" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="SURA" <?= ($con_arl == "SURA"     ? "selected" : "") ?>>SURA</option>
                                    <option value="POSITIVA" <?= ($con_arl == "POSITIVA" ? "selected" : "") ?>>POSITIVA</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 mt-2">
                            <h6 class="fw-bold p-2 text-white rounded-3 text-start px-3 mb-3" style="background-color:#002F55;">
                                <i class="bi bi-folder2-open me-2"></i> Certificados
                            </h6>
                        </div>

                        <?php
                        renderDocumentoCampo('Certificado Bancario', $banco, 'collapse_bancario', 'con_bancario');
                        renderDocumentoCampo('Certificado EPS', $cer_eps, 'collapse_cert_eps', 'con_certificado_eps');
                        renderDocumentoCampo('Certificado ARL', $cer_arl, 'collapse_cert_arl', 'con_arlCer');
                        ?>
                    </div>
                    <div class="mt-4 d-flex justify-content-between  ">
                        <button type="button" class="btn btn-secondary text-white" style="background-color: rgba(112, 113, 114, 1);" onclick="prevSection()"
                            style="margin-right: 1cm;"> <i class="bi bi-arrow-left me-2"></i> Atrás</button>
                        <button type="button" class="btn  text-white" style="background-color: #002F55;" onclick="nextSection()">
                            Siguiente <i class="bi bi-arrow-right ms-2"></i></button>
                    </div>
                </div>

                <!--Sección 4: Información Academica -->
                <div class="form-section d-none" id="section4">
                    <?php
                    $cer_tar_profe = $row['con_cerTarjeta'] ?? null;
                    $tar_profe     = $row['con_tarjeta'] ?? null;
                    ?>
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
                                <input class="form-control" type="text" placeholder="Ingrese la profesión" name="con_profesion" id="con_profesion" value="<?= htmlspecialchars($con_profesion ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_escolaridad" class="form-label" style="font-size:0.9em;">Escolaridad</label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_escolaridad">
                                    <i class="bi bi-cone-striped"></i>
                                </label>
                                <select class="form-select" id="con_escolaridad" name="con_escolaridad" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="BACHILLER" <?= ($con_escolaridad == "BACHILLER"        ? "selected" : "") ?>>BACHILLER</option>
                                    <option value="TÉCNICO" <?= ($con_escolaridad == "TÉCNICO"          ? "selected" : "") ?>>TÉCNICO</option>
                                    <option value="TECNÓLOGO" <?= ($con_escolaridad == "TECNÓLOGO"        ? "selected" : "") ?>>TECNÓLOGO</option>
                                    <option value="PROFESIONAL" <?= ($con_escolaridad == "PROFESIONAL"      ? "selected" : "") ?>>PROFESIONAL</option>
                                    <option value="ESPECIALISTA" <?= ($con_escolaridad == "ESPECIALISTA"     ? "selected" : "") ?>>ESPECIALISTA</option>
                                    <option value="GRADO" <?= ($con_escolaridad == "GRADO"            ? "selected" : "") ?>>GRADO</option>
                                    <option value="PREGRADO" <?= ($con_escolaridad == "PREGRADO"         ? "selected" : "") ?>>PREGRADO</option>
                                    <option value="MAGISTER" <?= ($con_escolaridad == "MAGISTER"         ? "selected" : "") ?>>MAGÍSTER</option>
                                    <option value="DOCTORADO" <?= ($con_escolaridad == "DOCTORADO"        ? "selected" : "") ?>>DOCTORADO</option>
                                    <option value="ESPECIALIZACION" <?= ($con_escolaridad == "ESPECIALIZACION"  ? "selected" : "") ?>>ESPECIALIZACIÓN</option>
                                    <option value="CONSOLIDACION" <?= ($con_escolaridad == "CONSOLIDACION"    ? "selected" : "") ?>>CONSOLIDACIÓN</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_grado" class="form-label fw-bold" style="font-size:0.9em;">Fecha de graduación</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar2-event"></i></span>
                                <input class="form-control" type="date" name="con_grado" id="con_grado" value="<?= htmlspecialchars($con_grado ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_num_tarjeta" class="form-label fw-bold" style="font-size:0.9em;">N° Tarjeta Profesional</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-vcard"></i></span>
                                <input class="form-control" type="text" placeholder="Ingrese el número de la tarjeta profesional" name="con_num_tarjeta" id="con_num_tarjeta" value="<?= htmlspecialchars($con_num_tarjeta ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_expedicion" class="form-label fw-bold" style="font-size:0.9em;">Fecha de Expedición de Tarjeta Profesional</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar-date"></i></span>
                                <input class="form-control" type="date" name="con_expedicion" id="con_expedicion" value="<?= htmlspecialchars($con_expedicion ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                        <div class="col-12 mt-5">
                            <h6 class="fw-bold p-2 text-white text-center w-25 rounded-3" style="background-color:#002F55;">
                                Documentos Adjuntos
                            </h6>
                        </div>
                        <div class="row px-0">
                            <?php
                            renderDocumentoCampoCol(
                                'Certificado de tarjeta profesional',
                                $cer_tar_profe,
                                'collapse_cer_tarjeta',
                                'con_cerTarjeta',
                                'col-12 col-lg-6'
                            );
                            renderDocumentoCampoCol(
                                'Tarjeta profesional',
                                $tar_profe,
                                'collapse_tarjeta',
                                'con_tarjeta',
                                'col-12 col-lg-6'
                            );
                            ?>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-between  ">
                        <button type="button" class="btn btn-secondary text-white" style="background-color: rgba(112, 113, 114, 1);" onclick="prevSection()"
                            style="margin-right: 1cm;"> <i class="bi bi-arrow-left me-2"></i> Atrás</button>

                        <button type="button" class="btn  text-white" style="background-color: #002F55;" onclick="nextSection()">
                            Siguiente <i class="bi bi-arrow-right ms-2"></i></button>
                    </div>

                </div>

                <!--Sección 6: Sociodemografica -->
                <div class="form-section d-none" id="section6">
                    <?php
                    $cer_discapa = $row['con_cerIncapacidad'] ?? null;
                    ?>
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
                            <label for="con_genero" class="form-label" style="font-size:0.9em;">Genero</label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_genero">
                                    <i class="bi bi-gender-ambiguous"></i>
                                </label>
                                <select class="form-select" id="con_genero" name="con_genero" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="MASCULINO" <?= ($con_genero ?? '') == "MASCULINO" ? "selected" : "" ?>>MASCULINO</option>
                                    <option value="FEMENINO" <?= ($con_genero ?? '') == "FEMENINO"  ? "selected" : "" ?>>FEMENINO</option>
                                    <option value="OTRO" <?= ($con_genero ?? '') == "OTRO"      ? "selected" : "" ?>>OTRO</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_raza" class="form-label" style="font-size:0.9em;">Raza</label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_raza">
                                    <i class="bi bi-person-arms-up"></i>
                                </label>
                                <select class="form-select" id="con_raza" name="con_raza" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="MESTIZO" <?= ($con_raza ?? '') == "MESTIZO" ? "selected" : "" ?>>MESTIZO</option>
                                    <option value="COMUNIDADES NEGRAS O AFROCOLOMBIANAS"
                                        <?= ($con_raza ?? '') == "COMUNIDADES NEGRAS O AFROCOLOMBIANAS" ? "selected" : "" ?>>
                                        COMUNIDADES NEGRAS O AFROCOLOMBIANAS
                                    </option>
                                    <option value="PUEBLOS Y COMUNIDADES INDIGENAS"
                                        <?= ($con_raza ?? '') == "PUEBLOS Y COMUNIDADES INDIGENAS" ? "selected" : "" ?>>
                                        PUEBLOS Y COMUNIDADES INDÍGENAS
                                    </option>
                                    <option value="COMUNIDAD RAIZAL"
                                        <?= ($con_raza ?? '') == "COMUNIDAD RAIZAL" ? "selected" : "" ?>>
                                        COMUNIDAD RAIZAL
                                    </option>
                                    <option value="PUEBLO ROM O GITANO"
                                        <?= ($con_raza ?? '') == "PUEBLO ROM O GITANO" ? "selected" : "" ?>>
                                        PUEBLO ROM O GITANO
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_vivienda" class="form-label" style="font-size:0.9em;">Vivienda</label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_vivienda">
                                    <i class="bi bi-house"></i>
                                </label>
                                <select class="form-select" id="con_vivienda" name="con_vivienda" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="ARRENDADA" <?= ($con_vivienda ?? '') == "ARRENDADA" ? "selected" : "" ?>>ARRENDADA</option>
                                    <option value="PROPIA" <?= ($con_vivienda ?? '') == "PROPIA"    ? "selected" : "" ?>>PROPIA</option>
                                    <option value="FAMILIAR" <?= ($con_vivienda ?? '') == "FAMILIAR"  ? "selected" : "" ?>>FAMILIAR</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="con_estrato" class="form-label" style="font-size:0.9em;">Estrato</label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_estrato">
                                    <i class="bi bi-house-exclamation"></i>
                                </label>
                                <select class="form-select" id="con_estrato" name="con_estrato" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="1" <?= ($con_estrato ?? '') == "1" ? "selected" : "" ?>>1</option>
                                    <option value="2" <?= ($con_estrato ?? '') == "2" ? "selected" : "" ?>>2</option>
                                    <option value="3" <?= ($con_estrato ?? '') == "3" ? "selected" : "" ?>>3</option>
                                    <option value="4" <?= ($con_estrato ?? '') == "4" ? "selected" : "" ?>>4</option>
                                    <option value="5" <?= ($con_estrato ?? '') == "5" ? "selected" : "" ?>>5</option>
                                    <option value="6" <?= ($con_estrato ?? '') == "6" ? "selected" : "" ?>>6</option>
                                </select>
                            </div>
                        </div>

                        <?php
                        $valorDiscap = strtoupper(trim((string)($con_discapacidad ?? '')));
                        $incapacitado = ($valorDiscap !== '' && $valorDiscap !== 'NO' && $valorDiscap !== 'N/A')
                            ? 'si'
                            : 'no';
                        ?>

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="incapacitado" class="form-label" style="font-size:0.9em;">¿Discapacidad?</label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="incapacitado">
                                    <i class="bi bi-person-wheelchair"></i>
                                </label>
                                <select class="form-select" id="incapacitado" name="incapacitado" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="si" <?= ($incapacitado === "si") ? "selected" : "" ?>>Sí</option>
                                    <option value="no" <?= ($incapacitado === "no") ? "selected" : "" ?>>No</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 p-1 px-2 my-2" id="campo_cual"
                            style="<?= ($incapacitado === 'si') ? '' : 'display:none;'; ?>">
                            <label class="form-label" for="con_discapacidad" style="font-size:0.9em;">¿Cuál?</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="bi bi-chat-left-text"></i></span>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="con_discapacidad"
                                    name="con_discapacidad"
                                    placeholder="Describa la discapacidad"
                                    value="<?= htmlspecialchars($con_discapacidad ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                        <div class="col-12 mt-5">
                            <h6 class="fw-bold p-2 text-white rounded-3 text-start px-3 mb-3" style="background-color:#002F55;">
                                <i class="bi bi-folder2-open me-2"></i> Documentos adjuntos
                            </h6>
                        </div>
                        <div class="row justify-content-center" id="grupo_cerIncapacidad" style="<?= ($incapacitado === 'si') ? '' : 'display:none;'; ?>">
                            <?php
                            renderDocumentoCampo(
                                'Certificado de discapacidad',
                                $cer_discapa,
                                'collapse_cer_discapacidad',
                                'con_cerIncapacidad'
                            );
                            ?>
                        </div>
                        <div class="mt-4 d-flex justify-content-between  ">
                            <button type="button" class="btn btn-secondary text-white" style="background-color: rgba(112, 113, 114, 1);" onclick="prevSection()"
                                style="margin-right: 1cm;"> <i class="bi bi-arrow-left me-2"></i> Atrás</button>
                            <button type="button" class="btn  text-white" style="background-color: #002F55;" onclick="nextSection()">
                                Siguiente <i class="bi bi-arrow-right ms-2"></i></button>
                        </div>
                    </div>
                </div>

                <!--Sección 7: Vacunas -->
                <div class="form-section d-none" id="section7">
                    <div class="mb-3  d-flex align-items-center text-start rounded-4 card-header shadow  p-3 text-white">
                        <i class="bi bi-hospital me-3 fs-2"></i>
                        <div>
                            <h3 class="h5 mb-1">Vacunas</h3>
                            <p class=" mb-0" style="font-size: 0.85rem; color:#999999">
                                Ingresa todos los campos correspondientes a las vacunas que tiene el colaborador.
                            </p>
                        </div>
                    </div>
                    <div class="row p-4 g-3">
                        <div class="container">
                            <div class="title-vacunas fw-bold">Esquema de vacunación</div>
                            <div class="vaccines-box">
                                <div class="row justify-content-center">
                                    <div class="col-md-10">
                                        <div class="row g-3 ">
                                            <div class="col-md-4 ">
                                                <div class="checklist-item">
                                                    <input type="checkbox" id="con_tetano1" name="con_tetano1" <?= !empty($con_tetano1) ? 'checked' : '' ?>>
                                                    <label for="con_tetano1">Tétano 1 Dosis</label>
                                                </div>
                                                <div class="checklist-item">
                                                    <input type="checkbox" id="con_tetano2" name="con_tetano2" <?= !empty($con_tetano2) ? 'checked' : '' ?>>
                                                    <label for="con_tetano2">Tétano 2 Dosis</label>
                                                </div>
                                                <div class="checklist-item">
                                                    <input type="checkbox" id="con_tetano3" name="con_tetano3" <?= !empty($con_tetano3) ? 'checked' : '' ?>>
                                                    <label for="con_tetano3">Tétano 3 Dosis</label>
                                                </div>
                                                <div class="checklist-item">
                                                    <input type="checkbox" id="con_influenza" name="con_influenza" <?= !empty($con_influenza) ? 'checked' : '' ?>>
                                                    <label for="con_influenza">Influenza</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="checklist-item">
                                                    <input type="checkbox" id="con_hepatitis_a" name="con_hepatitis_a" <?= !empty($con_hepatitis_a) ? 'checked' : '' ?>>
                                                    <label for="con_hepatitis_a">Hepatitis A</label>
                                                </div>
                                                <div class="checklist-item">
                                                    <input type="checkbox" id="con_hepatitis_c" name="con_hepatitis_c" <?= !empty($con_hepatitis_c) ? 'checked' : '' ?>>
                                                    <label for="con_hepatitis_c">Hepatitis C</label>
                                                </div>
                                                <div class="checklist-item">
                                                    <input type="checkbox" id="con_covid1" name="con_covid1" <?= !empty($con_covid1) ? 'checked' : '' ?>>
                                                    <label for="con_covid1">Covid-19: 1 Dosis</label>
                                                </div>
                                                <div class="checklist-item">
                                                    <input type="checkbox" id="con_covid2" name="con_covid2" <?= !empty($con_covid2) ? 'checked' : '' ?>>
                                                    <label for="con_covid2">Covid-19: 2 Dosis</label>
                                                </div>
                                                <div class="checklist-item">
                                                    <input type="checkbox" id="con_covid3" name="con_covid3" <?= !empty($con_covid3) ? 'checked' : '' ?>>
                                                    <label for="con_covid3">Covid-19: 3 Dosis</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="checklist-item">
                                                    <input type="checkbox" id="con_amarilla" name="con_amarilla" <?= !empty($con_amarilla) ? 'checked' : '' ?>>
                                                    <label for="con_amarilla">Fiebre Amarilla</label>
                                                </div>
                                                <div class="checklist-item">
                                                    <input type="checkbox" id="vph_1" name="vph_1" <?= !empty($vph_1) ? 'checked' : '' ?>>
                                                    <label for="vph_1">VPH 1</label>
                                                </div>
                                                <div class="checklist-item">
                                                    <input type="checkbox" id="vph_2" name="vph_2" <?= !empty($vph_2) ? 'checked' : '' ?>>
                                                    <label for="vph_2">VPH 2</label>
                                                </div>
                                                <div class="checklist-item">
                                                    <input type="checkbox" id="vph_3" name="vph_3" <?= !empty($vph_3) ? 'checked' : '' ?>>
                                                    <label for="vph_3">VPH 3</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 p-1 px-2 my-3 ">
                            <label for="con_rh" class="form-label" style="font-size:0.9em;">RH sanguineo</label>
                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="con_rh">
                                    <i class="bi-person-badge"></i>
                                </label>
                                <select class="form-select" style="font-size:0.9em;" id="con_rh" name="con_rh" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="O+" <?= ($con_rh ?? '') == "O+"  ? "selected" : "" ?>>O+</option>
                                    <option value="O-" <?= ($con_rh ?? '') == "O-"  ? "selected" : "" ?>>O-</option>
                                    <option value="A-" <?= ($con_rh ?? '') == "A-"  ? "selected" : "" ?>>A-</option>
                                    <option value="A+" <?= ($con_rh ?? '') == "A+"  ? "selected" : "" ?>>A+</option>
                                    <option value="B+" <?= ($con_rh ?? '') == "B+"  ? "selected" : "" ?>>B+</option>
                                    <option value="B-" <?= ($con_rh ?? '') == "B-"  ? "selected" : "" ?>>B-</option>
                                    <option value="AB+" <?= ($con_rh ?? '') == "AB+" ? "selected" : "" ?>>AB+</option>
                                    <option value="AB-" <?= ($con_rh ?? '') == "AB-" ? "selected" : "" ?>>AB-</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-6  p-1 px-2 my-3 d-flex align-items-center justify-content-start">
                            <button type="button" class="btn btn-guardar w-50 mt-4 bg-success text-white" id="btn_enviar">
                                <b>Guardar</b>
                            </button>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button"
                                class="btn text-white btn-prev-section px-4 d-inline-flex align-items-center" style="background-color: #002F55;"
                                data-prev="section6">
                                <i class="bi bi-arrow-left me-2"></i>
                                Atrás
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sección 8: Documentos (sigue igual, aunque no está en stepper) -->
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
                            <label class="form-label fw-bold" style="font-size:0.9em;">Otro si</label>
                            <div class="input-group border">
                                <a href="<?= neiva_app_url('Arbimaps/index.php?page=Personal/ver_otrosi&con_id=') ?><?php echo urlencode($row['con_id']); ?>&con_num_identidad=<?php echo urlencode($row['con_num_identidad']); ?>"
                                    class="btn text-white w-100" style="background-color: #002F55; border:1px solid #002F55">
                                    <i class="bi bi-file-pdf me-2"></i> Ver documentos
                                </a>
                            </div>
                        </div>

                        <div class="col-md-4 p-1 px-2 my-2 text-center">
                            <label class="form-label fw-bold" style="font-size:0.9em;">Titulos Profesionales</label>
                            <div class="input-group ">
                                <a href="<?= neiva_app_url('Arbimaps/index.php?page=Personal/ver_estudios&con_id=') ?><?php echo urlencode($row['con_id']); ?>&con_num_identidad=<?php echo urlencode($row['con_num_identidad']); ?>"
                                    class="btn text-white w-100" style="background-color: #002F55; border:1px solid #002F55">
                                    <i class="bi bi-file-pdf me-2"></i> Ver documentos
                                </a>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-between  ">
                            <button type="button" class="btn btn-secondary text-white" style="background-color: rgba(112, 113, 114, 1);" onclick="prevSection()">
                                <i class="bi bi-arrow-left me-2"></i> Atrás</button>

                            <button type="button" class="btn btn-success px-4" id="btn_volver" style="background-color: #049811ff;">
                                <i class="bi bi-house-up me-2"></i> Inicio</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal Vista previa -->
<div class="modal fade mt-4" id="modalVistaPrevia" tabindex="-1" role="dialog" aria-labelledby="modalVistaPreviaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#002F55; color:#fff;">
                <h5 class="modal-title" id="modalVistaPreviaLabel">
                    <i class="bi bi-eye me-2"></i> Vista previa del documento
                </h5>
            </div>

            <div class="modal-body p-0" style="height: 55vh;">
                <iframe id="iframeVistaPrevia"
                    src=""
                    style="width:100%; height:100%; border:0;"
                    loading="lazy"></iframe>
            </div>

            <div class="modal-footer">
                <a id="btnAbrirNuevaPestana" class="btn btn-outline-primary" href="#" target="_blank" rel="noopener">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Abrir en nueva pestaña
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="../../js/scripts.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/js/all.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://kit.fontawesome.com/9944c94262.js" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable();
    });

    const sectionOrder = [1, 2, 3, 4, 6, 7, 8];
    let currentPos = 0;

    function showSectionByPos(pos) {
        document.querySelectorAll(".form-section").forEach(s => s.classList.add("d-none"));

        const id = "section" + sectionOrder[pos];
        const current = document.getElementById(id);
        if (current) current.classList.remove("d-none");
        updateStepperUI(pos);
    }

    function updateStepperUI(pos) {
        const steps = document.querySelectorAll(".stepper .step");
        const progress = document.getElementById("stepper-progress");
        const stepIds = [1, 2, 3, 4, 6, 7];

        steps.forEach((btn, i) => {
            btn.classList.remove("active", "completed");
            const stepId = stepIds[i];
            const stepPos = sectionOrder.indexOf(stepId);
            if (stepPos < pos) btn.classList.add("completed");
            if (stepPos === pos) btn.classList.add("active");
        });

        if (progress) {
            const visiblePos = Math.max(0, Math.min(stepIds.length - 1, stepIds.indexOf(sectionOrder[pos])));
            const percent = (visiblePos / (stepIds.length - 1)) * 100;
            progress.style.width = percent + "%";
        }
    }

    function seccion(index) {
        const pos = sectionOrder.indexOf(index);
        if (pos !== -1) {
            currentPos = pos;
            showSectionByPos(currentPos);
        }
    }

    function nextSection() {
        if (currentPos < sectionOrder.length - 1) {
            currentPos++;
            showSectionByPos(currentPos);
        }
    }

    function prevSection() {
        if (currentPos > 0) {
            currentPos--;
            showSectionByPos(currentPos);
        }
    }
    document.addEventListener("DOMContentLoaded", function() {
        currentPos = 0;
        showSectionByPos(currentPos);
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const photoPicker = document.getElementById('photoPicker');
        const inputPhoto = document.getElementById('photo');
        const previewImg = document.getElementById('photoPreview');

        if (photoPicker && inputPhoto) {
            const openPicker = () => inputPhoto.click();

            photoPicker.addEventListener('click', openPicker);
            photoPicker.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    openPicker();
                }
            });

            inputPhoto.addEventListener('change', function() {
                const file = this.files && this.files[0];
                if (!file) return;

                if (!file.type.startsWith('image/')) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Archivo inválido',
                        text: 'Selecciona una imagen.'
                    });
                    this.value = '';
                    return;
                }

                const url = URL.createObjectURL(file);
                if (previewImg) {
                    previewImg.src = url;
                    previewImg.style.display = 'block';
                }
            });
        }
    });
</script>

<script>
    function mostrarCertificado(limpiar = false) {
        const select = document.getElementById('incapacitado');
        const campoCual = document.getElementById('campo_cual');
        const grupoCert = document.getElementById('grupo_cerIncapacidad');
        const inputCual = document.getElementById('con_discapacidad');
        const fileInput = document.getElementById('con_cerIncapacidad');

        if (!select || !campoCual || !grupoCert) return;

        const esSi = select.value === 'si';

        campoCual.style.display = esSi ? 'block' : 'none';
        grupoCert.style.display = esSi ? 'flex' : 'none';

        if (inputCual) {
            if (!esSi && limpiar) inputCual.value = '';
        }

        if (fileInput && !esSi && limpiar) {
            fileInput.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const inputFechaNacimiento = document.getElementById('con_fecha_nacimiento');
        const inputEdad = document.getElementById('con_edad');
        const selectEPS = document.getElementById('con_eps');
        const inputOtraEPS = document.getElementById('otra_eps');
        const selectDiscapacidad = document.getElementById('incapacitado');

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

        function toggleOtraEPS() {
            if (!selectEPS || !inputOtraEPS) return;

            if (selectEPS.value === 'OTRO') {
                inputOtraEPS.style.display = 'block';
            } else {
                inputOtraEPS.style.display = 'none';
                inputOtraEPS.value = '';
            }
        }

        if (selectEPS) {
            toggleOtraEPS();
            selectEPS.addEventListener('change', toggleOtraEPS);
        }

        mostrarCertificado(false);

        if (selectDiscapacidad) {
            selectDiscapacidad.addEventListener('change', function() {
                mostrarCertificado(true);
            });
        }

        document.querySelectorAll("input[type='text']").forEach(function(input) {
            input.addEventListener("input", function() {
                this.value = this.value.toUpperCase();
            });
        });

        const params = new URLSearchParams(window.location.search);
        const estado = params.get('guardado');

        function urlVolverEditar() {
            const cedula = document.getElementById('con_num_identidad')?.value || '';
            return '<?= neiva_app_url('Arbimaps/index.php?page=Perfil/vistas/editar_perfil_contratacion') ?>' +
                '&con_num_identidad=' + encodeURIComponent(cedula);
        }

        if (estado === 'error') {
            Swal.fire({
                customClass: {
                    popup: 'custom-swal-popup',
                    htmlContainer: 'custom-swal-html'
                },
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: true,
                html: `
                    <div class="swal-card">
                        <h2 class="swal-title-like">Error</h2>
                        <div class="swal-icon-wrap red">
                            <div class="swal-icon-circle red">×</div>
                        </div>
                        <div class="swal-sub-like">
                            Hubo un problema al guardar los datos.
                        </div>
                        <div class="swal-actions-like d-flex justify-content-center">
                            <button type="button" id="swalErr" class="swal-btn-like red w-auto px-4">
                                OK
                            </button>
                        </div>
                    </div>
                `,
                didOpen: () => {
                    document.getElementById('swalErr')?.addEventListener('click', () => {
                        window.location.href = urlVolverEditar();
                    });
                }
            });
        } else if (estado === 'invalido') {
            Swal.fire({
                customClass: {
                    popup: 'custom-swal-popup',
                    htmlContainer: 'custom-swal-html'
                },
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: true,
                html: `
                    <div class="swal-card">
                        <h2 class="swal-title-like">Número de identidad no válido</h2>
                        <div class="swal-icon-wrap red">
                            <div class="swal-icon-circle red">×</div>
                        </div>
                        <div class="swal-sub-like">
                            No se recibió el número de identidad.
                        </div>
                        <div class="swal-actions-like d-flex justify-content-center">
                            <button type="button" id="swalInv" class="swal-btn-like red w-auto px-4">
                                OK
                            </button>
                        </div>
                    </div>
                `,
                didOpen: () => {
                    document.getElementById('swalInv')?.addEventListener('click', () => {
                        window.location.href = '<?= neiva_app_url('Arbimaps/index.php?page=Personal/personal_activo') ?>';
                    });
                }
            });
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const formEditar = document.getElementById('formContratacion');
        const btnGuardarEditar = document.getElementById('btn_enviar');

        if (!formEditar || !btnGuardarEditar) return;

        function obtenerValor(id) {
            const el = document.getElementById(id);
            return el ? String(el.value || '').trim() : '';
        }

        function obtenerTextoLabel(id) {
            const el = document.getElementById(id);
            if (!el) return id;

            const label = document.querySelector('label[for="' + id + '"]');
            if (label) {
                return label.textContent.replace(/\s+/g, ' ').trim();
            }
            return id;
        }

        function validarFormulario() {
            const camposObligatorios = [
                'con_tipo_documento',
                'con_num_identidad',
                'con_celular',
                'con_direccion',
                'con_barrio',
                'con_ciudad',
                'con_FechaExpe',
                'con_lugarE',
                'con_fecha_nacimiento',
                'con_lugar_nacimiento',
                'con_correo',
                'con_correo_corporativo',
                'con_estado_civil',
                'con_nombre_conyuge',
                'con_enfermedades',
                'con_alergias',
                'con_medicamentos',
                'con_contingencia',
                'con_emergencia',
                'con_parentesco',
                'con_tel_emergencia',
                'con_num_cuenta',
                'con_tipo_cuenta',
                'con_financiera',
                'con_eps',
                'con_afp',
                'con_arl',
                'con_profesion',
                'con_escolaridad',
                'con_grado',
                'con_num_tarjeta',
                'con_expedicion',
                'con_genero',
                'con_raza',
                'con_vivienda',
                'con_estrato',
                'incapacitado',
                'con_rh'
            ];

            for (const id of camposObligatorios) {
                const valor = obtenerValor(id);
                if (valor === '') {
                    return obtenerTextoLabel(id);
                }
            }

            if (obtenerValor('con_eps') === 'OTRO' && obtenerValor('otra_eps') === '') {
                return 'Eps';
            }

            if (obtenerValor('incapacitado') === 'si' && obtenerValor('con_discapacidad') === '') {
                return '¿Cuál?';
            }

            return null;
        }

        btnGuardarEditar.addEventListener('click', function() {
            const faltante = validarFormulario();

            if (faltante) {
                Swal.fire({
                    customClass: {
                        popup: 'custom-swal-popup',
                        htmlContainer: 'custom-swal-html'
                    },
                    showConfirmButton: false,
                    allowOutsideClick: true,
                    allowEscapeKey: true,
                    html: `
                        <div class="swal-card">
                            <h2 class="swal-title-like">Campos obligatorios</h2>
                            <div class="swal-icon-wrap red">
                                <div class="swal-icon-circle red">×</div>
                            </div>
                            <div class="swal-sub-like">
                                Falta completar: <b>${faltante}</b>.
                            </div>
                            <div class="swal-actions-like d-flex justify-content-center">
                                <button type="button" id="swalCamposOk" class="swal-btn-like red w-auto px-4">
                                    OK
                                </button>
                            </div>
                        </div>
                    `,
                    didOpen: () => {
                        document.getElementById('swalCamposOk')?.addEventListener('click', () => Swal.close());
                    }
                });
                return;
            }

            Swal.fire({
                customClass: {
                    popup: 'custom-swal-popup',
                    htmlContainer: 'custom-swal-html'
                },
                showConfirmButton: false,
                showCancelButton: false,
                allowOutsideClick: false,
                allowEscapeKey: true,
                html: `
                    <div class="swal-card">
                        <h2 class="swal-title-like">¿Actualizar datos?</h2>
                        <div class="swal-icon-wrap green">
                            <div class="swal-icon-circle green">✓</div>
                        </div>
                        <div class="swal-sub-like">
                            Se guardarán los cambios realizados en el perfil.<br>
                            ¿Deseas continuar?
                        </div>
                        <div class="swal-actions-like swal-actions-row">
                            <button type="button" id="swalConfirmUpdate"
                                    class="btn btn-sm rounded-circle shadow-sm swal-mini-btn"
                                    title="Actualizar" aria-label="Actualizar"
                                    style="background-color: #002F55; color: white;">
                                <i class="bi bi-check-lg"></i>
                            </button>

                            <button type="button" id="swalCancelUpdate"
                                    class="btn btn-danger btn-sm rounded-circle shadow-sm swal-mini-btn"
                                    title="Cancelar" aria-label="Cancelar">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                `,
                didOpen: () => {
                    const btnYes = document.getElementById('swalConfirmUpdate');
                    const btnNo = document.getElementById('swalCancelUpdate');

                    btnYes?.addEventListener('click', () => {
                        Swal.close();
                        formEditar.submit();
                    });

                    btnNo?.addEventListener('click', () => {
                        Swal.close();
                    });
                }
            });
        });
    });
</script>
<script>
    function abrirModalInforme(url) {
        if (!url) return;
        const cacheBuster = (url.includes('?') ? '&' : '?') + 'v=' + Date.now();
        const finalUrl = url + cacheBuster;
        const $iframe = $('#iframeVistaPrevia');
        const $btn = $('#btnAbrirNuevaPestana');

        $iframe.attr('src', finalUrl);
        $btn.attr('href', url);
        $('#modalVistaPrevia').modal('show');
    }

    $('#modalVistaPrevia').on('hidden.bs.modal', function() {
        $('#iframeVistaPrevia').attr('src', '');
        $('#btnAbrirNuevaPestana').attr('href', '#');
    });
</script>