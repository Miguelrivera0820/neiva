<?php
$where = "";
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../index.php");
    exit();
}

$idUsuario = (int) $_SESSION['id_usuario'];
$sql = "SELECT 
            rol_usuario, 
            rol_usuario_dos, 
            nombre_usuario
        FROM usuarios_cons
        WHERE id_usuario = $idUsuario";
$resultado = $mysqli->query($sql);
if (!$resultado || $resultado->num_rows === 0) {
    header("Location: ../../acceso_denegado.php");
    exit();
}

$datosUsuario   = $resultado->fetch_assoc();
$rolUsuario     = $datosUsuario['rol_usuario'] ?? '';
$rolUsuarioDos  = $datosUsuario['rol_usuario_dos'] ?? '';

$rolesPermitidos = array(
    "administrador",
    "atencion_juridica",
    "avaluos",
    "coordinacion_tecnica",
    "consolidacion",
    "control_calidad",
    "director",
    "director_catastro",
    "director_catastral",
    "director_presupuestos",
    "director_proyectos",
    "editor",
    "gerencia",
    "lider_reconocimiento",
    "pagos",
    "procedencia_juridica",
    "reconocedor",
    "revision_juridica",
    "seguridad_social",
    "social",
    "soporte",
    "soporte_nivel1",
    "usuarios_ops",
    "ventanilla_catastral",
);

if (!in_array($rolUsuario, $rolesPermitidos) && !in_array($rolUsuarioDos, $rolesPermitidos)) {
    header("Location: ../../acceso_denegado.php");
    exit();
}
$idUsuario = $_SESSION['id_usuario'];
// Validar que venga el con_num_identidad
if (isset($_GET['con_num_identidad'])) {
    $con_num_identidad = $_GET['con_num_identidad'];

    $sql = "
        SELECT 
    c.con_nombres, 
    c.con_apellidos,
    c.con_tipo_documento,
    c.con_num_identidad,
    c.con_fecha_inicio,
    c.con_fecha_final,
    c.con_cargo,
    c.con_proyecto,
    c.con_duracion,
    c.con_salario,
    c.con_valor_proyecto,
    c.con_sede,
    c.con_duracion,
    c.photo,
    so.sol_fecha_inicio,
    so.sol_nueva_fecha_final,
    so.sol_duracion,
    so.sol_nuevo_rol,
    so.sol_nuevo_proyecto,
    so.sol_nuevo_salario,
    so.sol_tipo_otrosi,
    so.sol_motivo_rechazo,
    cop.cop_fecha_inicio,
    cop.cop_nueva_fecha_final,
    cop.cop_duracion,
    cop.cop_nuevo_salario,
    so.sol_valor_otrosi
FROM solicitudes_otrosi AS so
JOIN contratacion AS c ON so.con_id = c.con_id
JOIN copia_solicitudes_otrosi AS cop ON so.con_id = cop.cop_id
WHERE c.con_num_identidad = ?
LIMIT 1
    ";

    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param('s', $con_num_identidad);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $con_nombres            = $row['con_nombres'];
            $con_apellidos          = $row['con_apellidos'];
            $con_tipo_documento     = $row['con_tipo_documento'];
            $con_num_identidad      = $row['con_num_identidad'];
            $con_fecha_inicio       = $row['con_fecha_inicio'];
            $con_fecha_final        = $row['con_fecha_final'];
            $sol_nueva_fecha_final  = $row['sol_nueva_fecha_final'];
            $sol_fecha_inicio       = $row['sol_fecha_inicio'];
            $sol_duracion           = $row['sol_duracion'];
            $sol_nuevo_rol          = $row['sol_nuevo_rol'];
            $sol_nuevo_proyecto     = $row['sol_nuevo_proyecto'];
            $sol_nuevo_salario      = $row['sol_nuevo_salario'];
            $sol_tipo_otrosi        = $row['sol_tipo_otrosi'];
            $sol_motivo_rechazo  = $row['sol_motivo_rechazo'];
            $cop_fecha_inicio       = $row['cop_fecha_inicio'];
            $cop_nueva_fecha_final  = $row['cop_nueva_fecha_final'];
            $cop_duracion           = $row['cop_duracion'];
            $cop_nuevo_salario      = $row['cop_nuevo_salario'];
            $sol_valor_otrosi       = $row['sol_valor_otrosi'];
            $foto_user = $row['photo'] ?? null;
            $con_cargo_actual = $row['con_cargo'] ?? null;
            $con_proyecto_actual = $row['con_proyecto'] ?? null;
            $con_duracion = $row['con_duracion'] ?? null;
            $con_salario = $row['con_salario'] ?? null;
            $con_valor_proyecto = $row['con_valor_proyecto'] ?? null;
            $con_sede = $row['con_sede'] ?? null;
            $con_duracion = $row['con_duracion'] ?? null;
        } else {
            echo "No se encontró la solicitud.";
            exit;
        }
    } else {
        echo "Error en la consulta: " . $mysqli->error;
        exit;
    }
} else {
    echo "ID no válido.";
    exit;
}
// Consulta adicional para traer con_id y id
$sql_ids = "
    SELECT so.id, c.con_id 
    FROM solicitudes_otrosi AS so
    JOIN contratacion AS c ON so.con_id = c.con_id
    WHERE c.con_num_identidad = ?
    LIMIT 1
";

if ($stmt_ids = $mysqli->prepare($sql_ids)) {
    $stmt_ids->bind_param('s', $con_num_identidad);
    $stmt_ids->execute();
    $result_ids = $stmt_ids->get_result();

    if ($row_ids = $result_ids->fetch_assoc()) {
        $id = $row_ids['id'];
        $con_id = $row_ids['con_id'];
    } else {
        echo "No se encontraron los IDs necesarios.";
        exit;
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

function numOrZero($v): float
{
    if ($v === null) return 0;
    if (is_string($v) && trim($v) === '') return 0;
    if (!is_numeric($v)) return 0;
    return (float)$v;
}
$valorFormateado_con_salario        = '$ ' . number_format(numOrZero($con_salario), 0, ',', '.');
$valorFormateado_con_valor_proyecto = '$ ' . number_format(numOrZero($con_valor_proyecto), 0, ',', '.');
$valorFormateado_sol_nuevo_salario  = '$ ' . number_format(numOrZero($sol_nuevo_salario), 0, ',', '.');
$valorFormateado_sol_valor_otrosi   = '$ ' . number_format(numOrZero($sol_valor_otrosi), 0, ',', '.');

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
        /* height: 45px; */
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

    /* .photo-img {
        max-height: 300px;
        width: auto;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        transition: transform 0.3s ease;
    } */

    /* Overlay */
    .photo-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 47, 85, 0.11);
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
        transition: opacity 0.3s ease-in-out;
    }

    /* Hover */
    .photo-wrapper:hover .photo-overlay {
        opacity: 1;
    }

    .photo-wrapper:hover .photo-img {
        transform: scale(1.03);
    }

    /* Icono */
    .photo-overlay i {
        font-size: 2rem;
        margin-bottom: 6px;
    }

    .photo-fixed {
        width: 270px;
        height: 300px;
        overflow: hidden;
        border-radius: 12px;
        border: 2px dashed #002f5582;
    }

    /* Imagen */
    .photo-fixed .photo-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        /* 🔑 CLAVE */
        object-position: center;
    }
</style>


<div class="container-fluid">
    <div class=" text-center mt-4">
        <h1 class="h3  text-center mb-0 fw-bold" style="color:#002F55">Información de la Solicitud</h1>
        <small class="text-muted">Aquí encontrarás la información correspondiente a la información de la solicitud de otro si.</small>
    </div>

    <div class="card rounded-4 m-3 shadow p-2">
        <form id="multiStepForm" method="POST" action="<?= neiva_app_url('Arbimaps/vistas/Personal/acciones/edicion_solicitudes.php') ?>">
            <div class="container-fluid">
                <div class="form-section" id="section1">

                    <div class="row p-2">

                        <div class="col-3 ">
                            <!-- <div class="card shadow-sm rounded-4 border " style="background-color: #002f5523;"> -->
                            <?php if (!empty($row['photo'])): ?>
                                <?php
                                list($rutaWeb, $rutaFisica) = ajustarRutaDocumento($row['photo']);
                                ?>

                                <?php if ($rutaWeb && file_exists($rutaFisica)): ?>

                                    <!-- Imagen -->
                                    <div class="text-center mt-3">
                                        <div class="photo-wrapper photo-fixed">
                                            <img src="<?php echo htmlspecialchars($rutaWeb, ENT_QUOTES, 'UTF-8'); ?>"
                                                alt="Foto del colaborador"
                                                class="photo-img">
                                            <a class="photo-overlay"></a>
                                        </div>
                                    </div>

                                <?php else: ?>

                                    <!-- Archivo no encontrado -->
                                    <div class="alert alert-warning mt-3 small text-center">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        La foto está registrada, pero no se encuentra en el servidor.
                                    </div>

                                <?php endif; ?>

                            <?php else: ?>

                                <!-- Sin foto -->
                                <div class="alert alert-secondary mt-3 rounded-4 small text-center h-100 justify-content-center d-flex flex-column align-items-center">
                                    <i class="bi bi-person-x fs-2"></i>
                                    <small>No se ha cargado ninguna foto.</small>
                                </div>
                            <?php endif; ?>
                            <!-- </div> -->
                        </div>

                        <div class="col-9 align-content-center justify-content-center">
                            <div class="row ">
                                <div class="col-12">
                                    <div class="mb-3 d-flex align-items-center text-start rounded-4 card-header shadow m-2 py-3 text-white">
                                        <i class="bi bi-person-circle me-3 fs-2"></i>
                                        <div>
                                            <h3 class="h5 mb-1">Datos personales</h3>
                                            <p class=" mb-0" style="font-size: 0.85rem; color: #999999;">
                                                Información básica del colaborador.
                                            </p>
                                        </div>
                                    </div>

                                </div>

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label class="form-label fw-bold" for="nombres" style="font-size:0.9em;">Nombres</label>
                                    <div class="input-group ">
                                        <span class="input-group-text shadow-sm"><i class="bi bi-person"></i></span>
                                        <input type="text" class="form-control shadow-sm" id="nombres" name="nombres" value="<?php echo $con_nombres ?? ''; ?>" disabled>
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label class="form-label fw-bold" for="con_apellidos" style="font-size:0.9em;">Apellidos</label>
                                    <div class="input-group ">
                                        <span class="input-group-text shadow-sm" id="basic-addon1"><i class="bi-people"></i></span>
                                        <input class="form-control shadow-sm" name="con_apellidos" id="con_apellidos" value="<?php echo $con_apellidos; ?>" disabled>
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label class="form-label fw-bold" for="con_tipo_documento">Tipo de documento</label>
                                    <div class="input-group ">
                                        <label class="input-group-text" for="con_tipo_documento">
                                            <i class="bi-person-badge"></i>
                                        </label>
                                        <input class="form-control shadow-sm" name="con_tipo_documento" id="con_tipo_documento" value="<?php echo $con_tipo_documento; ?>" disabled>
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label class="form-label fw-bold" for="con_num_identidad">N° Identidad</label>
                                    <div class="input-group ">
                                        <span class="input-group-text shadow-sm"><i class="bi bi-person-vcard"></i></span>
                                        <input class="form-control" name="con_num_identidad" id="con_num_identidad" value="<?php echo $con_num_identidad; ?>" readonly>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="col-12  my-5" style="border-bottom:2px dashed #002f557a"></div>

                        <div class="col-12 mt-0 mb-3">
                            <h6 class="fw-bold p-2 text-white  text-center w-50 rounded-3" style="background-color: #002F55;">Información contractual vigente </h6>
                        </div>

                        <!-- ===================================================================================================================================== -->
                        <!-- aquí se muestra la información del contrato del colaborado actual, es decir el que se encuentra vigente antes de la solicitud del otro sí. Se trae de la tabla de contratación -->
                        <!-- ======================================================================================================================================================= -->

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="con_sede" class="form-label fw-bold" style="font-size:0.9em;">Sede de trabajo actual</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-building"></i></span>
                                <input class="form-control" name="con_sede" id="con_sede" value="<?php echo $con_sede; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="con_fecha_inicio" class="form-label fw-bold" style="font-size:0.9em;">Fecha de Inicio del Contrato</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar-date"></i></span>
                                <input class="form-control" name="con_fecha_inicio" id="con_fecha_inicio" value="<?php echo $con_fecha_inicio; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="con_fecha_final" class="form-label fw-bold" style="font-size:0.9em;">Fecha de finalización de contrato</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar2-x"></i></span>
                                <input class="form-control" name="con_fecha_final" id="con_fecha_final" value="<?php echo $con_fecha_final; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="con_expedicion" class="form-label fw-bold" style="font-size:0.9em;">Rol actual</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-file-person-fill"></i></span>
                                <input class="form-control" name="con_cargo_actual" id="con_cargo_actual" value="<?php echo $con_cargo_actual; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="con_expedicion" class="form-label fw-bold" style="font-size:0.9em;">Proyecto actual</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-bezier"></i></span>
                                <input class="form-control" name="con_proyecto_actual" id="con_proyecto_actual" value="<?php echo $con_proyecto_actual ?? ''; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="con_salario" class="form-label fw-bold" style="font-size:0.9em;">Salario/Honorarios</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-piggy-bank"></i></span>
                                <input class="form-control" name="con_salario" id="con_salario"
                                    value="<?php echo $valorFormateado_con_salario; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="con_valor_proyecto" class="form-label fw-bold" style="font-size:0.9em;">Valor Total Proyecto</label>
                            <div class="input-group shadow-sm shadow-warning">
                                <span class="input-group-text shadow-sm text-white" style="background-color: #002F55; border:1px solid #002f55"><i class="bi bi-cash-coin"></i></span>
                                <input class="form-control " style="border:1px solid #002f55" name="con_valor_proyecto" id="con_valor_proyecto" value="<?php echo $valorFormateado_con_valor_proyecto; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="con_duracion" class="form-label fw-bold" style="font-size:0.9em;">Duración de contrato</label>
                            <div class="input-group">
                                <span class="input-group-text shadow-sm text-white" style="background-color: #002F55; border:1px solid #002f55"><i class="bi bi-calendar-week"></i></span>
                                <input class="form-control" style="border:1px solid #002f55" name="con_duracion" id="con_duracion" value="<?php echo $con_duracion; ?>" readonly>
                            </div>
                        </div>

                        <!-- ===================================================================================================================================== -->
                        <!-- aquí se muestra la información del otro sí solicitado y la información del rechazo -->
                        <!-- ======================================================================================================================================================= -->

                        <div class="col-12  my-5" style="border-bottom:2px dashed #002f557a"></div>

                        <div class="col-12 mt-0 mb-3 ">
                            <h6 class="fw-bold p-2  text-center rounded-3" style="background-color: #ffc107; width:50%; margin-left:25%;color:#002F55">Información otro sí solicitado y motivo de rechazo </h6>
                        </div>

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="sol_fecha_inicio" class="form-label fw-bold" style="font-size:0.9em;">Fecha de inicio del otro sí solicitado</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar-date"></i></span>
                                <input class="form-control" name="sol_fecha_inicio" id="sol_fecha_inicio" value="<?php echo $sol_fecha_inicio; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="con_fecha_final" class="form-label fw-bold" style="font-size:0.9em;">Fecha de finalización de otro si solicitado</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar2-x"></i></span>
                                <input class="form-control" name="con_fecha_final" id="con_fecha_final" value="<?php echo $sol_nueva_fecha_final; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="sol_nuevo_rol" class="form-label fw-bold" style="font-size:0.9em;">Rol solicitado</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-file-person-fill"></i></span>
                                <input class="form-control" name="sol_nuevo_rol" id="sol_nuevo_rol" value="<?php echo $sol_nuevo_rol; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="sol_nuevo_proyecto" class="form-label fw-bold" style="font-size:0.9em;">Proyecto solicitado</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-bezier"></i></span>
                                <input class="form-control" name="sol_nuevo_proyecto" id="sol_nuevo_proyecto" value="<?php echo $sol_nuevo_proyecto ?? ''; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="sol_nuevo_salario" class="form-label fw-bold" style="font-size:0.9em;">Salario/Honorarios solicitado</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-piggy-bank"></i></span>
                                <input class="form-control"
                                    name="sol_nuevo_salario"
                                    id="sol_nuevo_salario_solicitado"
                                    value="<?php echo $valorFormateado_sol_nuevo_salario; ?>"
                                    readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="sol_tipo_otrosi" class="form-label fw-bold" style="font-size:0.9em;">Tipo de solicitud del otro si</label>
                            <div class="input-group shadow-sm shadow-warning">
                                <span class="input-group-text shadow-sm "><i class="bi bi-person-rolodex"></i></span>
                                <input class="form-control" name="sol_tipo_otrosi" id="sol_tipo_otrosi" value="<?php echo $sol_tipo_otrosi; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="sol_valor_otrosi" class="form-label fw-bold" style="font-size:0.9em;">Valor Total Proyecto</label>
                            <div class="input-group shadow-sm shadow-warning">
                                <span class="input-group-text shadow-sm bg-warning border border-warning"><i class="bi bi-cash-coin"></i></span>
                                <input class="form-control border border-warning" name="sol_valor_otrosi" id="" value="<?php echo $valorFormateado_sol_valor_otrosi; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="sol_duracion" class="form-label fw-bold" style="font-size:0.9em;">Duración de otro si solicitado</label>
                            <div class="input-group">
                                <span class="input-group-text shadow-sm bg-warning border border-warning"><i class="bi bi-calendar-week"></i></span>
                                <input class="form-control border border-warning" name="sol_duracion" id="" value="<?php echo $sol_duracion; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-12  py-2 ">
                            <div class="row align-items-center justify-content-center text-center">
                                <div class="col-md-8">
                                    <label class="form-label" for="sol_motivo_rechazo">Motivo Devolución</label>
                                    <input class="form-control text-center" style="height: 75px; overflow-y: auto;" name="sol_motivo_rechazo" id="sol_motivo_rechazo" value="<?php echo $sol_motivo_rechazo; ?>" disabled>
                                </div>
                            </div>
                        </div>

                        <!-- ==================================================================================================== -->
                        <!------------- sección para campos que se pueden modificar ---------------------------->
                        <!-- ======================================================================================================== -->

                        <div class="col-12  my-5" style="border-bottom:2px dashed #002f557a"></div>

                        <div class="col-12 mt-0 mb-3 ">
                            <h6 class="fw-bold p-2 bg-success text-white ms-auto text-center w-50 rounded-3" style="background-color: #002F55;">Modificar información para nueva solicitud </h6>
                        </div>
                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="sol_fecha_inicio_edit" class="form-label fw-bold" style="font-size:0.9em;">
                                Fecha de inicio del otro si
                            </label>
                            <div class="input-group">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar-date"></i></span>
                                <input class="form-control"
                                    name="sol_fecha_inicio"
                                    id="sol_fecha_inicio_edit"
                                    type="date"
                                    value="<?php echo $sol_fecha_inicio; ?>">
                            </div>
                        </div>
                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="sol_nueva_fecha_final_edit" class="form-label fw-bold" style="font-size:0.9em;">
                                Fecha de finalización de otro si
                            </label>
                            <div class="input-group">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar2-x"></i></span>
                                <input class="form-control"
                                    name="sol_nueva_fecha_final"
                                    id="sol_nueva_fecha_final_edit"
                                    type="date"
                                    value="<?php echo $sol_nueva_fecha_final; ?>">
                            </div>
                        </div>
                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="sol_nuevo_rol" class="form-label fw-bold" style="font-size:0.9em;">Rol</label>

                            <div class="input-group shadow-sm">
                                <label class="input-group-text" for="sol_nuevo_rol">
                                    <i class="bi bi-file-person-fill"></i>
                                </label>

                                <select class="form-select" style="font-size:0.9em;" name="sol_nuevo_rol" id="sol_nuevo_rol" required>
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
                        </div>
                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="sol_nuevo_proyecto" class="form-label fw-bold" style="font-size:0.9em;">Proyecto</label>
                            <div class="input-group">
                                <span class="input-group-text shadow-sm"><i class="bi bi-bezier"></i></span>
                                <input class="form-control" name="sol_nuevo_proyecto" id="sol_nuevo_proyecto"
                                    value="<?php echo $sol_nuevo_proyecto ?? ''; ?>" readonly>
                            </div>
                        </div>
                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="con_salario_mostrado" class="form-label fw-bold" style="font-size:0.9em;">
                                Salario/Honorarios
                            </label>
                            <div class="input-group">
                                <span class="input-group-text shadow-sm"><i class="bi bi-piggy-bank"></i></span>
                                <input class="form-control"
                                    id="con_salario_mostrado_edit"
                                    value="<?php echo $valorFormateado_sol_nuevo_salario; ?>"
                                    oninput="formatCurrency(this, 'sol_nuevo_salario_edit')"
                                    required>
                                <input type="hidden"
                                    name="sol_nuevo_salario"
                                    id="sol_nuevo_salario_edit"
                                    value="<?php echo $sol_nuevo_salario; ?>">
                            </div>
                        </div>
                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="sol_tipo_otrosi" class="form-label fw-bold" style="font-size:0.9em;">
                                Tipo de solicitud del otro si
                            </label>
                            <div class="input-group shadow-sm shadow-warning">
                                <span class="input-group-text shadow-sm"><i class="bi bi-person-rolodex"></i></span>
                                <input class="form-control"
                                    name="sol_tipo_otrosi"
                                    id="sol_tipo_otrosi"
                                    value="<?php echo $sol_tipo_otrosi; ?>"
                                    readonly>
                            </div>
                        </div>
                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="valor_otrosi_visible" class="form-label fw-bold" style="font-size:0.9em;">
                                Valor total
                            </label>
                            <div class="input-group shadow-sm shadow-warning">
                                <span class="input-group-text text-white shadow-sm bg-success border border-success">
                                    <i class="bi bi-cash-coin"></i>
                                </span>
                                <input class="form-control border border-success"
                                    id="valor_otrosi_visible"
                                    value="<?php echo $valorFormateado_sol_valor_otrosi; ?>"
                                    readonly>
                                <input type="hidden"
                                    name="sol_valor_otrosi"
                                    id="sol_valor_otrosi"
                                    value="<?php echo $sol_valor_otrosi; ?>">
                            </div>
                        </div>
                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="sol_duracion" class="form-label fw-bold" style="font-size:0.9em;">
                                Duración de otro si
                            </label>
                            <div class="input-group">
                                <span class="input-group-text text-white shadow-sm bg-success border border-success">
                                    <i class="bi bi-calendar-week"></i>
                                </span>
                                <input class="form-control border border-success"
                                    name="sol_duracion"
                                    id="sol_duracion"
                                    value="<?php echo $sol_duracion; ?>"
                                    readonly>
                                <input type="hidden"
                                    id="sol_dias"
                                    name="sol_dias"
                                    value="">
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="con_id" id="con_id" value="<?php echo $con_id ?? ''; ?>">
                    <input type="hidden" name="id" id="id" value="<?php echo $id ?? ''; ?>">
                    <div class="mt-4 d-flex justify-content-center">
                        <button type="submit" id="guardado" class="btn btn-success" style="margin-right: 1cm;">Guardar y Enviar</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- <script src="../js/scripts.js"></script> -->
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
<script>
    // Inicializar DataTable
    $(document).ready(function() {
        $('#dataTable').DataTable();
    });

    document.getElementById("multiStepForm").addEventListener("submit", function(e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);

        // Confirmación con SweetAlert
        Swal.fire({
            title: '¿Guardar cambios?',
            text: 'Se actualizará la solicitud con la información editada.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {

                // (Opcional) Log de depuración
                for (let [key, value] of formData.entries()) {
                    console.log(`${key}: ${value}`);
                }

                fetch("<?= neiva_app_url('Arbimaps/vistas/Personal/acciones/edicion_solicitudes.php') ?>", {
                        method: "POST",
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === "ok") {
                            Swal.fire({
                                title: '¡Guardado!',
                                text: data.message || 'Datos enviados con éxito',
                                icon: 'success',
                                confirmButtonText: 'Aceptar'
                            }).then(() => {
                                // Redirigir después de guardar
                                window.location.href = "<?= neiva_app_url('Arbimaps/index.php?page=Personal/solicitudes_dir_operaciones') ?>";
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.message || 'Algo falló al guardar los cambios',
                                icon: 'error',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire({
                            title: 'Error',
                            text: 'Error de red o del servidor',
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                    });
            }
        });
    });


    document.addEventListener("DOMContentLoaded", function() {
        const fechaInicio = document.getElementById("sol_fecha_inicio");
        const fechaFinal = document.getElementById("sol_nueva_fecha_final");

        if (fechaInicio && fechaFinal) {
            fechaInicio.addEventListener("change", calcularDuracionSolicitud);
            fechaFinal.addEventListener("change", calcularDuracionSolicitud);
        }

        function calcularDuracionSolicitud() {
            const inicio = fechaInicio.value;
            const final = fechaFinal.value;

            if (inicio && final) {
                const fechaInicioDate = new Date(inicio);
                const fechaFinalDate = new Date(final);

                if (fechaFinalDate >= fechaInicioDate) {
                    let años = fechaFinalDate.getFullYear() - fechaInicioDate.getFullYear();
                    let meses = fechaFinalDate.getMonth() - fechaInicioDate.getMonth();
                    let dias = fechaFinalDate.getDate() - fechaInicioDate.getDate();

                    if (dias < 0) {
                        meses -= 1;
                        const diasMesAnterior = new Date(fechaFinalDate.getFullYear(), fechaFinalDate.getMonth(), 0).getDate();
                        dias += diasMesAnterior;
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

                    document.getElementById("sol_duracion").value = duracion;
                    const duracionHidden = document.getElementById("sol_duracion_hidden");
                    if (duracionHidden) {
                        duracionHidden.value = duracion;
                    }
                } else {
                    document.getElementById("sol_duracion").value = "Fecha final inválida";
                    const duracionHidden = document.getElementById("sol_duracion_hidden");
                    if (duracionHidden) {
                        duracionHidden.value = "";
                    }
                }
            }
        }
    });


    //FUNCION QUE PERMITE EL CAMPO DEL SALARIO CON FORMATO COP
    function formatCurrency(input, hiddenId) {
        let value = input.value.replace(/\D/g, ""); // elimina todo menos dígitos
        if (!value) {
            input.value = "";
            document.getElementById(hiddenId).value = "";
            return;
        }

        let formattedValue = new Intl.NumberFormat("es-CO", {
            style: "currency",
            currency: "COP",
            minimumFractionDigits: 0
        }).format(value);

        input.value = formattedValue.replace("COP", "").trim();
        document.getElementById(hiddenId).value = value;
    }




    document.addEventListener("DOMContentLoaded", function() {
        const fechaInicio = document.getElementById("sol_fecha_inicio_edit");
        const fechaFinal = document.getElementById("sol_nueva_fecha_final_edit");
        const duracionInp = document.getElementById("sol_duracion");
        const diasHidden = document.getElementById("sol_dias");
        const salarioHidden = document.getElementById("sol_nuevo_salario_edit");
        const valorVisible = document.getElementById("valor_otrosi_visible");
        const valorHidden = document.getElementById("sol_valor_otrosi");

        if (!fechaInicio || !fechaFinal || !duracionInp || !diasHidden || !salarioHidden || !valorVisible || !valorHidden) {
            console.warn("Faltan elementos del formulario para cálculo automático.");
            return;
        }
        recalcularTodo();

        ["input", "change"].forEach(evt => {
            fechaInicio.addEventListener(evt, recalcularTodo);
            fechaFinal.addEventListener(evt, recalcularTodo);
        });

        ["input", "change"].forEach(evt => {
            const salarioVisible = document.getElementById("con_salario_mostrado_edit");
            if (salarioVisible) salarioVisible.addEventListener(evt, () => setTimeout(recalcularValorTotal, 0));
        });

        function parseYMD(ymd) {
            const [y, m, d] = ymd.split("-").map(Number);
            return new Date(y, m - 1, d);
        }

        function diffDias(inicio, fin) {
            const msPorDia = 24 * 60 * 60 * 1000;
            return Math.round((fin - inicio) / msPorDia) + 1;
        }

        function recalcularTodo() {
            recalcularDuracion();
            recalcularValorTotal();
        }

        function recalcularDuracion() {
            const inicio = fechaInicio.value;
            const fin = fechaFinal.value
            if (!inicio || !fin) {
                duracionInp.value = "";
                diasHidden.value = "";
                return;
            }
            const dIni = parseYMD(inicio);
            const dFin = parseYMD(fin);
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
            const formatted = new Intl.NumberFormat("es-CO", {
                style: "currency",
                currency: "COP",
                minimumFractionDigits: 0
            }).format(total).replace("COP", "").trim();
            valorVisible.value = formatted;
        }
    });
</script>