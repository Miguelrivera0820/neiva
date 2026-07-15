<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 2) . '/config/permisos.php';

neiva_bootstrap();
neiva_require_methods('GET');
neiva_require_permission('menu.personalViabilidadFinanciera', $PERMISOS);

// Verifica si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}
// Se obtiene el ID de usuario de la sesión
$idUsuario = $_SESSION['id_usuario'];
$aprobarPresupuestoUrl = neiva_app_url('Arbimaps/vistas/Personal/acciones/aprobar_presupuesto.php');
$rechazoPresupuestoUrl = neiva_app_url('Arbimaps/vistas/Personal/acciones/rechazo_presupuesto.php');

// Verificar que con_id esté presente y sea un número válido
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "
        SELECT 
            c.con_nombres, 
            c.con_apellidos,
            c.con_tipo_documento,
            c.con_num_identidad,
            c.con_fecha_inicio,
            c.con_fecha_final,
            c.photo,
            so.sol_fecha_inicio,
            so.sol_nueva_fecha_final,
            so.sol_duracion,
            so.sol_nuevo_rol,
            so.sol_nuevo_proyecto,
            so.sol_nuevo_salario,
            so.sol_tipo_otrosi,
            so.sol_valor_otrosi,
            so.sol_motivo
        FROM solicitudes_otrosi AS so
        JOIN contratacion AS c ON so.con_id = c.con_id
        WHERE so.id = ?
        LIMIT 1
    ";

    // Preparar y ejecutar la consulta
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param('i', $id);  // Usar 'i' si con_id es un entero
        $stmt->execute();
        $result = $stmt->get_result();

        // Verificar si se encontró el resultado
        if ($row = $result->fetch_assoc()) {
            $con_nombres       = $row['con_nombres'];
            $con_apellidos     = $row['con_apellidos'];
            $con_tipo_documento = $row['con_tipo_documento'];
            $con_num_identidad = $row['con_num_identidad'];
            $con_fecha_inicio  = $row['con_fecha_inicio'];
            $sol_fecha_inicio  = $row['sol_fecha_inicio'];
            $sol_nueva_fecha_final = $row['sol_nueva_fecha_final'];
            $con_fecha_final   = $row['con_fecha_final'];
            $sol_duracion      = $row['sol_duracion'];
            $sol_nuevo_rol     = $row['sol_nuevo_rol'];
            $sol_nuevo_proyecto = $row['sol_nuevo_proyecto'];
            $sol_nuevo_salario = $row['sol_nuevo_salario'];
            $sol_tipo_otrosi   = $row['sol_tipo_otrosi'];
            $sol_motivo        = $row['sol_motivo'];
            $sol_valor_otrosi  = $row['sol_valor_otrosi'];
            $foto_user = $row['photo'] ?? null;
        } else {
            echo "No se encontró la solicitud.";
            exit;
        }
    } else {
        echo "Error en la preparación de la consulta: " . $mysqli->error;
        exit;
    }
} else {
    echo "ID no válido.";
    exit;
}

// Lista de roles permitidos
$rolesPermitidos = array("administrador", "director_presupuestos", "pagos", "seguridad_social", "director_proyectos", "Directivos");


function ajustarRutaDocumento($rutaBD)
{
    if (empty($rutaBD)) {
        return [null, null];
    }
    $prefijoCorrecto = neiva_app_url('Arbimaps/vistas/Personal') . '/';
    if (strpos($rutaBD, $prefijoCorrecto) === 0) {
        $rutaWeb = $rutaBD;
    } else {
        $prefijoViejo = neiva_app_url('Arbimaps') . '/';
        if (strpos($rutaBD, $prefijoViejo) === 0) {
            $rutaBD = substr($rutaBD, strlen($prefijoViejo));
        }
        $rutaWeb = $prefijoCorrecto . ltrim($rutaBD, "/");
    }
    $rutaRelativa = preg_replace('#^https?://[^/]+/#i', '', $rutaWeb);
    $rutaFisica = neiva_join_paths(neiva_app_root(), str_replace('/', DIRECTORY_SEPARATOR, $rutaRelativa));
    return [$rutaWeb, $rutaFisica];
}

//Mostrar el valor de la copia 
$salario = ($sol_nuevo_salario === '' || $sol_nuevo_salario === null) ? 0 : (float)$sol_nuevo_salario;
$otrosi  = ($sol_valor_otrosi  === '' || $sol_valor_otrosi  === null) ? 0 : (float)$sol_valor_otrosi;

$valorFormateado  = '$ ' . number_format($salario, 0, ',', '.');
$valorFormateado2 = '$ ' . number_format($otrosi,  0, ',', '.');

$mysqli->close();

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

    .area-justificacion {
        resize: none;
        overflow-y: auto;
        height: 120px !important;
        text-align: center;
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

<div class="container-fluid ">

    <div class=" text-center mt-4">
        <h1 class="h3  text-center mb-0" style="color:#002F55"><b>SOLICITUD</b></h1>
        <small class="text-muted">Informacion de contratacion actual</small>
    </div>

    <div class=" rounded-4   mb-3">

        <div class="seccion-formulario ">

            <div class="card rounded-4 m-3 shadow p-2">
                <div class="container-fluid ">
                    <div class="row p-2 ">
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
                                <div class="alert alert-secondary mt-3 small text-center">
                                    <i class="bi bi-person-x"></i>
                                    No se ha cargado ninguna foto.
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
                                        <input class="form-control shadow-sm" name="con_apellidos" id="con_apellidos" value="<?php echo $con_apellidos; ?>" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label class="form-label fw-bold" for="con_tipo_documento">Tipo de documento</label>
                                    <div class="input-group ">
                                        <label class="input-group-text" for="con_tipo_documento">
                                            <i class="bi-person-badge"></i>
                                        </label>
                                        <input class="form-control shadow-sm" name="con_tipo_documento" id="con_tipo_documento" value="<?php echo $con_tipo_documento; ?>" readonly>
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

                        <div class="col-12  my-4" style="border-bottom:2px dashed #002f557a"></div>

                        <div class="col-12 mt-0 mb-3">
                            <h6 class="fw-bold p-2 text-white text-center w-25 rounded-3" style="background-color: #002F55;">Información de solicitud </h6>
                        </div>

                        <div class="col-12 col-md-6 col-sm-6 mb-3">
                            <label for="con_fecha_inicio" class="form-label fw-bold" style="font-size:0.9em;">Fecha de Inicio del Contrato</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar-date"></i></span>
                                <input class="form-control" name="con_fecha_inicio" id="con_fecha_inicio" value="<?php echo $con_fecha_inicio; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-sm-6 mb-3">
                            <label for="con_fecha_final" class="form-label fw-bold" style="font-size:0.9em;">Fecha de finalización de contrato</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar-date"></i></span>
                                <input class="form-control" name="con_fecha_final" id="con_fecha_final" value="<?php echo $con_fecha_final; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12  my-5" style="border-bottom:2px dashed #002f557a"></div>
                        <div class="col-12 mt-0 mb-3 ">
                            <h6 class="fw-bold p-2 bg-success text-white ms-auto text-center w-50 rounded-3" style="background-color: #002F55;">
                                Información solicitud otro si
                            </h6>
                        </div>
                        <div class="col-12 col-md-4 col-sm-6 mb-3">
                            <label for="con_expedicion" class="form-label fw-bold" style="font-size:0.9em;">Cargo / Rol</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-file-person-fill"></i></span>
                                <input class="form-control" name="sol_nuevo_rol" id="sol_nuevo_rol" value="<?php echo $sol_nuevo_rol; ?>" readonly>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 col-sm-6 mb-3">
                            <label for="con_expedicion" class="form-label fw-bold" style="font-size:0.9em;">Proyecto</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-bezier"></i></span>
                                <input class="form-control" name="sol_nuevo_proyecto" id="sol_nuevo_proyecto" value="<?php echo $sol_nuevo_proyecto; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-4 col-sm-6 mb-3">
                            <label for="con_salario_mostrado" class="form-label fw-bold" style="font-size:0.9em;">Salario/Honorarios</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-piggy-bank"></i></span>
                                <input class="form-control" name="sol_nuevo_salario" id="sol_nuevo_salario" value="<?php echo $valorFormateado; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-4 col-sm-6 mb-3">
                            <label for="con_valor_proyecto_mostrado" class="form-label fw-bold" style="font-size:0.9em;">Valor Total Otro Sí</label>
                            <div class="input-group shadow-sm shadow-warning">
                                <span class="input-group-text shadow-sm bg-warning border border-warning"><i class="bi bi-cash-coin"></i></span>
                                <input class="form-control border-warning" name="sol_valor_otrosi" id="sol_valor_otrosi" value="<?php echo $valorFormateado2; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-4 col-sm-6 mb-3">
                            <label class="form-label" for="sol_tipo_otrosi" style="font-size:0.9em;">Tipo de solicitud del otro si</label>
                            <div class="input-group shadow-sm shadow-warning">
                                <span class="input-group-text "><i class="bi bi-file-earmark-text"></i></span>
                                <input class="form-control" name="sol_tipo_otrosi" id="sol_tipo_otrosi" value="<?php echo $sol_tipo_otrosi; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-4 col-sm-6 mb-3">
                            <label for="sol_fecha_inicio" class="form-label fw-bold" style="font-size:0.9em;">Fecha de Contrato</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar-date"></i></span>
                                <input class="form-control" name="sol_fecha_inicio" id="sol_fecha_inicio" value="<?php echo $sol_fecha_inicio; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-sm-6 mb-3">
                            <label for="sol_nueva_fecha_final" class="form-label fw-bold" style="font-size:0.9em;">Nueva Fecha Final de Otro Si</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar-date"></i></span>
                                <input class="form-control" name="sol_nueva_fecha_final" id="sol_nueva_fecha_final" value="<?php echo $sol_nueva_fecha_final; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-sm-6 mb-3">
                            <label for="sol_duracion" class="form-label fw-bold" style="font-size:0.8em;">Duración de Otro Sí</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar2-event"></i></span>
                                <input class="form-control" name="sol_duracion" id="sol_duracion" value="<?php echo $sol_duracion; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-12  py-2 ">
                            <div class="row align-items-center justify-content-center text-center">
                                <div class="col-md-8">
                                    <label class="form-label" for="sol_motivo">Justificación Técnica</label>
                                    <textarea class="form-control text-center" name="sol_motivo" style="height: 75px; overflow-y: auto;" id="sol_motivo" readonly
                                        oninput="autoResize(this)"><?php echo $sol_motivo; ?></textarea>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- BOTONES VIABLE / NO VIABLE -->
        <div class="my-2 d-flex justify-content-center">
            <!-- <form method="post"
                action="/arbimaps/Arbimaps/vistas/Personal/acciones/aprobar_presupuesto.php"
                onsubmit="return confirm('¿Confirmas marcar esta solicitud como VIABLE?');"
                class="me-3">

                <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                <input type="hidden" name="numero_identidad" value="<?php echo htmlspecialchars($con_num_identidad); ?>">

                <button type="submit" class="btn btn-success " style="margin-right: 1cm;">
                    <i class="bi bi-hand-thumbs-up"></i> Viable
                </button>
            </form> -->

            <!-- corrección para poder mostrar un sweet alert -->
            <form method="post"
                action="<?php echo htmlspecialchars($aprobarPresupuestoUrl, ENT_QUOTES, 'UTF-8'); ?>"
                id="form-viable"
                class="me-3">
                <?php echo neiva_csrf_input('global'); ?>

                <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                <input type="hidden" name="numero_identidad" value="<?php echo htmlspecialchars($con_num_identidad); ?>">

                <button type="submit" class="btn btn-success " style="margin-right: 1cm;">
                    <i class="bi bi-hand-thumbs-up"></i> Viable
                </button>
            </form>

            <!-- <form method="post"
                action="/arbimaps/Arbimaps/vistas/Personal/acciones/rechazo_presupuesto.php"
                id="form-no-viable"
                onsubmit="return confirm('¿Confirmas rechazar esta solicitud? Esta acción no se puede deshacer.');">

                <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                <input type="hidden" name="numero_identidad" value="<?php echo htmlspecialchars($con_num_identidad); ?>">
                <input type="hidden" name="accion" value="no_viable">

                <div class="d-flex flex-column align-items-center ">
                    <button type="button" id="btn-no-viable" class="btn btn-danger">
                        <i class="bi bi-hand-thumbs-down"></i> No Viable
                    </button>
                    <div id="contenedor-razon" class="mt-3" style="display:none; width:500%; margin-left:-80%;">
                        <label for="razon" class="form-label fw-bold" style="color:#002F55;">Razón del rechazo</label>
                        <textarea
                            class="form-control"
                            id="razon"
                            name="motivo"
                            style="height: 100px; font-size:0.9em; resize:none;"
                            rows="3"
                            placeholder="Describe brevemente por qué se rechaza la solicitud"
                            required></textarea>

                        <button type="submit" class="btn btn-danger mt-2">
                            Confirmar rechazo
                        </button>
                    </div>
                </div>
            </form> -->

            <!-- corrección parar mostrar un sweet alert para el rechazo -->
            <form method="post"
                action="<?php echo htmlspecialchars($rechazoPresupuestoUrl, ENT_QUOTES, 'UTF-8'); ?>"
                id="form-no-viable">
                <?php echo neiva_csrf_input('global'); ?>

                <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                <input type="hidden" name="numero_identidad" value="<?php echo htmlspecialchars($con_num_identidad); ?>">
                <input type="hidden" name="accion" value="no_viable">

                <div class="d-flex flex-column align-items-center ">
                    <button type="button" id="btn-no-viable" class="btn btn-danger">
                        <i class="bi bi-hand-thumbs-down"></i> No Viable
                    </button>
                    <div id="contenedor-razon" class="mt-3" style="display:none; width:500%; margin-left:-80%;">
                        <label for="razon" class="form-label fw-bold" style="color:#002F55;">Razón del rechazo</label>
                        <textarea
                            class="form-control"
                            id="razon"
                            name="motivo"
                            style="height: 100px; font-size:0.9em; resize:none;"
                            rows="3"
                            placeholder="Describe brevemente por qué se rechaza la solicitud"
                            required></textarea>

                        <button type="submit" class="btn btn-danger mt-2">
                            Confirmar rechazo
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>

</div>

<script>
    function autoResize(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = (textarea.scrollHeight) + 'px';
    }

    window.addEventListener('DOMContentLoaded', () => {
        const textarea = document.getElementById('sol_motivo');
        if (textarea) autoResize(textarea);

        const btnNoViable = document.getElementById('btn-no-viable');
        const contRazon = document.getElementById('contenedor-razon');
        const txtRazon = document.getElementById('razon');

        if (btnNoViable && contRazon) {
            btnNoViable.addEventListener('click', function() {
                if (contRazon.style.display === 'none' || contRazon.style.display === '') {
                    contRazon.style.display = 'block';
                    if (txtRazon) {
                        txtRazon.focus();
                    }
                }
            });
        }
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        /* ====== BOTÓN VIABLE ====== */
        document.querySelector("#form-viable button").addEventListener("click", function(e) {
            e.preventDefault();

            Swal.fire({
                title: "¿Confirmar viabilidad?",
                text: "Esta solicitud será marcada como VIABLE",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#198754",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Sí, aprobar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("form-viable").submit();
                }
            });
        });

        /* ====== BOTÓN NO VIABLE ====== */
        document.getElementById("btn-no-viable").addEventListener("click", function() {
            document.getElementById("contenedor-razon").style.display = "block";
        });

        /* ====== CONFIRMAR RECHAZO ====== */
        document.querySelector("#form-no-viable button[type='submit']").addEventListener("click", function(e) {
            e.preventDefault();

            const motivo = document.getElementById("razon").value.trim();

            if (motivo === "") {
                Swal.fire({
                    icon: "warning",
                    title: "Campo requerido",
                    text: "Debes ingresar la razón del rechazo"
                });
                return;
            }

            Swal.fire({
                title: "¿Rechazar solicitud?",
                text: "Esta acción no se puede deshacer",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Sí, rechazar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("form-no-viable").submit();
                }
            });
        });

    });
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="../js/scripts.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
