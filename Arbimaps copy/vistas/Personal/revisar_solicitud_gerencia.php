<?php
$idUsuario = $_SESSION['id_usuario'];

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "
        SELECT 
            c.con_id,
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
            so.sol_motivo,
            so.sol_valor_otrosi
        FROM solicitudes_otrosi AS so
        JOIN contratacion AS c ON so.con_id = c.con_id
        WHERE so.id = ?
        LIMIT 1
    ";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $con_id                 = $row['con_id'];
            $con_nombres            = $row['con_nombres'];
            $con_apellidos          = $row['con_apellidos'];
            $con_tipo_documento     = $row['con_tipo_documento'];
            $con_num_identidad      = $row['con_num_identidad'];
            $con_fecha_inicio       = $row['con_fecha_inicio'];
            $con_fecha_final        = $row['con_fecha_final'];
            $sol_fecha_inicio       = $row['sol_fecha_inicio'];
            $sol_nueva_fecha_final  = $row['sol_nueva_fecha_final'];
            $sol_duracion           = $row['sol_duracion'];
            $sol_nuevo_rol          = $row['sol_nuevo_rol'];
            $sol_nuevo_proyecto     = $row['sol_nuevo_proyecto'];
            $sol_nuevo_salario      = $row['sol_nuevo_salario'];
            $sol_tipo_otrosi        = $row['sol_tipo_otrosi'];
            $sol_motivo             = $row['sol_motivo'];
            $sol_valor_otrosi       = $row['sol_valor_otrosi'];
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
$rolesPermitidos = array("administrador", "director_presupuestos", "pagos", "seguridad_social", "director_proyectos", "Directivos");

$sol_nuevo_salario_num = is_numeric($sol_nuevo_salario) ? (float)$sol_nuevo_salario : 0;
$sol_valor_otrosi_num  = is_numeric($sol_valor_otrosi) ? (float)$sol_valor_otrosi : 0;

$valorFormateado3 = number_format($sol_nuevo_salario_num, 0, ',', '.');
$valorFormateadoOtrosi = number_format($sol_valor_otrosi_num, 0, ',', '.');

$salarioOriginal = (string)(int)$sol_nuevo_salario_num;

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
        <form id="multiStepForm">
            <div class="container-fluid">
                <div class="form-section" id="section1">

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

                        <!-- <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label class="form-label" for="con_fecha_inicio">Fecha de Inicio de Contrato</label>
                            <input class="form-control" name="con_fecha_inicio" id="con_fecha_inicio" value="<?php echo $con_fecha_inicio; ?>" readonly>
                        </div> -->

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="con_fecha_inicio" class="form-label fw-bold" style="font-size:0.9em;">Fecha de Inicio del Contrato</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar-date"></i></span>
                                <input class="form-control" name="con_fecha_inicio" id="con_fecha_inicio" value="<?php echo $con_fecha_inicio; ?>" readonly>
                            </div>
                        </div>

                        <!-- <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label class="form-label" for="con_fecha_final">Fecha Final del Contrato</label>
                            <input class="form-control" name="con_fecha_final" id="con_fecha_final" value="<?php echo $con_fecha_final; ?>" readonly>
                        </div> -->

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="con_fecha_final" class="form-label fw-bold" style="font-size:0.9em;">Fecha de finalización de contrato</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar-date"></i></span>
                                <input class="form-control" name="con_fecha_final" id="con_fecha_final" value="<?php echo $con_fecha_final; ?>" readonly>
                            </div>
                        </div>

                        <!-- <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label class="form-label" for="sol_nuevo_rol">Cargo/Rol</label>
                            <input class="form-control" name="sol_nuevo_rol" id="sol_nuevo_rol" value="<?php echo $sol_nuevo_rol; ?>" readonly>
                        </div> -->

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="con_expedicion" class="form-label fw-bold" style="font-size:0.9em;">Cargo / Rol</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-file-person-fill"></i></span>
                                <input class="form-control" name="sol_nuevo_rol" id="sol_nuevo_rol" value="<?php echo $sol_nuevo_rol; ?>" readonly>
                            </div>
                        </div>

                        <!-- <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label class="form-label" for="sol_nuevo_proyecto">Proyecto</label>
                            <input class="form-control" name="sol_nuevo_proyecto" id="sol_nuevo_proyecto" value="<?php echo $sol_nuevo_proyecto; ?>" readonly>
                        </div> -->

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="con_expedicion" class="form-label fw-bold" style="font-size:0.9em;">Proyecto</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-bezier"></i></span>
                                <input class="form-control" name="sol_nuevo_proyecto" id="sol_nuevo_proyecto" value="<?php echo $sol_nuevo_proyecto; ?>" readonly>
                            </div>
                        </div>

                        <!-- HONORARIOS (salario) -->
                        <!-- <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label class="form-label" for="con_salario_mostrado">Honorarios</label><span class="input-group-text">$
                                <input class="form-control" id="con_salario_mostrado" value="<?php echo $valorFormateado3; ?>" oninput="formatCurrency(this, 'sol_nuevo_salario')" required></span>
                            <input type="hidden" name="sol_nuevo_salario" id="sol_nuevo_salario" value="<?php echo $salarioOriginal; ?>">
                        </div> -->

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="con_salario_mostrado" class="form-label fw-bold" style="font-size:0.9em;">Salario/Honorarios</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm text-white" style="background-color: #002F55;border:1px solid #002F55;"><i class="bi bi-piggy-bank"></i></span>
                                <input class="form-control" style="border:1px solid #002F55; border-bottom-right-radius:6px; border-top-right-radius:6px" id="con_salario_mostrado" value="<?php echo $valorFormateado3; ?>" oninput="formatCurrency(this, 'sol_nuevo_salario')" required></span>
                                <input type="hidden" name="sol_nuevo_salario" id="sol_nuevo_salario" value="<?php echo $salarioOriginal; ?>">
                            </div>
                        </div>

                        <!-- VALOR TOTAL OTROSÍ -->
                        <!-- <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label class="form-label" for="valor_otrosi_visible">Valor total otrosí</label><span class="input-group-text">$
                                <input class="form-control" id="valor_otrosi_visible" value="<?php echo $valorFormateadoOtrosi; ?> " oninput="formatCurrency(this, 'sol_valor_otrosi')" required></span>
                            <input type="hidden" name="sol_valor_otrosi" id="sol_valor_otrosi" value="<?php echo $sol_valor_otrosi; ?>">
                        </div> -->

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="con_valor_proyecto_mostrado" class="form-label fw-bold" style="font-size:0.9em;">Valor Total Otro Sí</label>
                            <div class="input-group shadow-sm shadow-warning">
                                <span class="input-group-text shadow-sm bg-warning border border-warning"><i class="bi bi-cash-coin"></i></span>
                                <input class="form-control border-warning " style="border-bottom-right-radius:6px; border-top-right-radius:6px" id="valor_otrosi_visible" value="<?php echo $valorFormateadoOtrosi; ?> " oninput="formatCurrency(this, 'sol_valor_otrosi')" required></span>
                                <input class="border-warning" type="hidden" name="sol_valor_otrosi" id="sol_valor_otrosi" value="<?php echo $sol_valor_otrosi; ?>">
                            </div>
                        </div>

                        <!-- <div class="col-md-6">
                            <label class="form-label" for="sol_tipo_otrosi">Tipo de Solicitud Otro Si</label>
                            <input class="form-control" name="sol_tipo_otrosi" id="sol_tipo_otrosi" value="<?php echo $sol_tipo_otrosi; ?>" readonly>
                        </div> -->

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label class="form-label" for="sol_tipo_otrosi" style="font-size:0.9em;">Tipo de solicitud del otro si</label>
                            <div class="input-group shadow-sm shadow-warning">
                                <span class="input-group-text "><i class="bi bi-file-earmark-text"></i></span>
                                <input class="form-control" name="sol_tipo_otrosi" id="sol_tipo_otrosi" value="<?php echo $sol_tipo_otrosi; ?>" readonly>
                            </div>
                        </div>

                        <!-- <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label class="form-label" for="sol_fecha_inicio">Fecha de Inicio de Otro Si</label>
                            <input class="form-control" name="sol_fecha_inicio" id="sol_fecha_inicio" type="date" value="<?php echo $sol_fecha_inicio; ?>">
                        </div> -->

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="sol_fecha_inicio" class="form-label fw-bold" style="font-size:0.9em;">Fecha de Inicio de otro si</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm text-white" style="background-color: #002F55;border:1px solid #002F55;"><i class="bi bi-calendar-date"></i></span>
                                <input class="form-control" style="border:1px solid #002F55; border-bottom-right-radius:6px; border-top-right-radius:6px" name="sol_fecha_inicio" id="sol_fecha_inicio" type="date" value="<?php echo $sol_fecha_inicio; ?>">
                            </div>
                        </div>

                        <!-- <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label class="form-label" for="sol_nueva_fecha_final">Nueva Fecha Final</label>
                            <input class="form-control" name="sol_nueva_fecha_final" id="sol_nueva_fecha_final" type="date" value="<?php echo $sol_nueva_fecha_final; ?>">
                        </div> -->

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="sol_nueva_fecha_final" class="form-label fw-bold" style="font-size:0.9em;">Nueva Fecha Final</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm text-white" style="background-color: #002F55;border:1px solid #002F55;"><i class="bi bi-calendar-date"></i></span>
                                <input class="form-control" style="border:1px solid #002F55; border-bottom-right-radius:6px; border-top-right-radius:6px" name="sol_nueva_fecha_final" id="sol_nueva_fecha_final" type="date" value="<?php echo $sol_nueva_fecha_final; ?>">
                            </div>
                        </div>

                        <!-- DURACIÓN -->
                        <!-- <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label class="form-label" for="duracion_visible">Duración de Otro Sí</label>
                            <input class="form-control" id="sol_duracion" value="<?php echo $sol_duracion; ?>" readonly>
                            <input type="hidden" name="sol_duracion" id="sol_duracion_hidden" value="<?php echo $sol_duracion; ?>">
                        </div> -->

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="sol_duracion" class="form-label fw-bold" style="font-size:0.8em;">Duración de Otro Sí</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar2-event"></i></span>
                                <input class="form-control" id="sol_duracion" value="<?php echo $sol_duracion; ?>" readonly>
                                <input type="hidden" name="sol_duracion" id="sol_duracion_hidden" value="<?php echo $sol_duracion; ?>">
                            </div>
                        </div>

                        <!-- <div class="col-md-6">
                            <label class="form-label" for="sol_motivo">Justificación Técnica</label>
                            <textarea class="form-control" name="sol_motivo" id="sol_motivo" readonly
                                oninput="autoResize(this)"><?php echo $sol_motivo; ?></textarea>
                        </div> -->

                        <div class="col-12 text-center">
                            <div class="row justify-content-center">
                                <div class="col-12 col-md-10 col-sm-6 my-4">
                                    <label class="form-label" for="sol_motivo">Justificación Técnica</label>
                                    <textarea class="form-control" name="sol_motivo" id="sol_motivo" readonly
                                        oninput="autoResize(this)"><?php echo $sol_motivo; ?></textarea>
                                </div>
                            </div>
                        </div>

                        <script>
                            function autoResize(textarea) {
                                textarea.style.height = 'auto'; // Reinicia el alto
                                textarea.style.height = (textarea.scrollHeight) + 'px'; // Ajusta al contenido
                            }

                            // Llamar autoResize cuando cargue la página
                            window.addEventListener('DOMContentLoaded', () => {
                                const textarea = document.getElementById('sol_motivo');
                                if (textarea) autoResize(textarea);
                            });
                        </script>

                    </div>

                    <input type="hidden" id="con_id" name="con_id" value="<?php echo $con_id; ?>">
                    <input type="hidden" id="solicitud_id" name="id" value="<?php echo $id; ?>">


                    <!-- Botones principales -->
                    <div class="mt-4 d-flex justify-content-center">
                        <button type="button" id="btn_aprobacion" class="btn btn-success" style="margin-right: 1cm;"> <i class="bi bi-hand-thumbs-up me-1"></i> Aprobar</button>
                        <button type="button" id="btn_devolucion" class="btn text-white " style="margin-right: 1cm; background-color: #002f55"><i class="bi bi-pen me-1"></i> Modificar</button>
                        <button type="button" id="btn-rechazar" class="btn btn-danger"> <i class="bi bi-hand-thumbs-down me-1"></i> Rechazar</button>
                    </div>

                    <!-- Campo de observación (se oculta inicialmente) -->
                    <!-- <div id="campo-observacion" style="display:none;" class="mt-3">
                        <textarea class="form-control" id="razon" placeholder="Escriba la razón del rechazo"></textarea>
                        <button class="btn btn-primary mt-2" id="enviar-rechazo">Enviar Razón</button>
                        <input type="hidden" id="correo" value="correo@ejemplo.com">
                    </div> -->

                    <div id="campo-observacion" class="my-3 col-10" style="display:none; margin-left:8%">
                        <label for="razon" class="form-label fw-bold" style="color:#002F55;">Razón del rechazo</label>
                        <textarea class="form-control" id="razon" placeholder="Escriba la razón del rechazo"></textarea>

                        <button type="submit" id="enviar-rechazo" class="btn btn-danger mt-2">
                            Confirmar rechazo
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>


</div>

<!-- <div class="container-fluid">
    <h1 class="mt-4 text-dark"> <b>Solicitud<b> </h1>
    <div class="card mb-4">
        <div class="card-body form-label">En este módulo encontrará la información correspondiente a la solicitud de Otro Si.</div>
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div>
                <div class="card-form mx-auto" style="max-width: 960px;">
                    <header>INFORMACIÓN SOLICITUD DE OTRO SI</header>
                    <div class="card-body">
                        <form id="multiStepForm">
                            <-- Sección 1: Datos personales --
                            <div class="form-section" id="section1">
                                <div class="mb-4 text-center">
                                    <h4 class="form-title mt-3">Datos personales</h4>
                                </div>
                                <div class="row">
                                    <div class="col-3 ">
                                        <-- <div class="card shadow-sm rounded-4 border " style="background-color: #002f5523;"> --
                                        <?php if (!empty($row['photo'])): ?>
                                            <?php
                                            list($rutaWeb, $rutaFisica) = ajustarRutaDocumento($row['photo']);
                                            ?>

                                            <?php if ($rutaWeb && file_exists($rutaFisica)): ?>

                                                <-- Imagen --
                                                <div class="text-center mt-3">
                                                    <div class="photo-wrapper photo-fixed">
                                                        <img src="<?php echo htmlspecialchars($rutaWeb, ENT_QUOTES, 'UTF-8'); ?>"
                                                            alt="Foto del colaborador"
                                                            class="photo-img">
                                                        <a class="photo-overlay"></a>
                                                    </div>
                                                </div>

                                            <?php else: ?>

                                                <-- Archivo no encontrado --
                                                <div class="alert alert-warning mt-3 small text-center">
                                                    <i class="bi bi-exclamation-triangle"></i>
                                                    La foto está registrada, pero no se encuentra en el servidor.
                                                </div>

                                            <?php endif; ?>

                                        <?php else: ?>

                                            <-- Sin foto --
                                            <div class="alert alert-secondary mt-3 small text-center">
                                                <i class="bi bi-person-x"></i>
                                                No se ha cargado ninguna foto.
                                            </div>
                                        <?php endif; ?>
                                        <-- </div> --
                                    </div>

                                    <div class="col-12 col-md-3 col-sm-6 mb-3">
                                        <label class="form-label" for="nombres">Nombres</label>
                                        <input type="text" class="form-control" id="nombres" name="nombres" value="<?php echo $con_nombres ?? ''; ?>" disabled>
                                    </div>
                                    <div class="col-12 col-md-3 col-sm-6 mb-3">
                                        <label class="form-label" for="con_apellidos">Apellidos</label>
                                        <input class="form-control" name="con_apellidos" id="con_apellidos" value="<?php echo $con_apellidos; ?>" readonly>
                                    </div>
                                    <div class="col-12 col-md-3 col-sm-6 mb-3">
                                        <label class="form-label" for="con_tipo_documento">T.Documento</label>
                                        <input class="form-control text-truncate" name="con_tipo_documento" id="con_tipo_documento" value="<?php echo $con_tipo_documento; ?>" readonly>
                                    </div>
                                    <div class="col-12 col-md-3 col-sm-6 mb-3">
                                        <label class="form-label" for="con_num_identidad">N° Identidad</label>
                                        <input class="form-control" name="con_num_identidad" id="con_num_identidad" value="<?php echo $con_num_identidad; ?>" readonly>
                                    </div>
                                    <div class="col-12 col-md-3 col-sm-6 mb-3">
                                        <label class="form-label" for="con_fecha_inicio">Fecha de Inicio de Contrato</label>
                                        <input class="form-control" name="con_fecha_inicio" id="con_fecha_inicio" value="<?php echo $con_fecha_inicio; ?>" readonly>
                                    </div>
                                    <div class="col-12 col-md-3 col-sm-6 mb-3">
                                        <label class="form-label" for="con_fecha_final">Fecha Final del Contrato</label>
                                        <input class="form-control" name="con_fecha_final" id="con_fecha_final" value="<?php echo $con_fecha_final; ?>" readonly>
                                    </div>
                                    <div class="col-12 col-md-3 col-sm-6 mb-3">
                                        <label class="form-label" for="sol_nuevo_rol">Cargo/Rol</label>
                                        <input class="form-control" name="sol_nuevo_rol" id="sol_nuevo_rol" value="<?php echo $sol_nuevo_rol; ?>" readonly>
                                    </div>
                                    <div class="col-12 col-md-3 col-sm-6 mb-3">
                                        <label class="form-label" for="sol_nuevo_proyecto">Proyecto</label>
                                        <input class="form-control" name="sol_nuevo_proyecto" id="sol_nuevo_proyecto" value="<?php echo $sol_nuevo_proyecto; ?>" readonly>
                                    </div>

                                    <-- HONORARIOS (salario) --
                                    <div class="col-12 col-md-3 col-sm-6 mb-3">
                                        <label class="form-label" for="con_salario_mostrado">Honorarios</label><span class="input-group-text">$
                                            <input class="form-control" id="con_salario_mostrado" value="<?php echo $valorFormateado3; ?>" oninput="formatCurrency(this, 'sol_nuevo_salario')" required></span>
                                        <input type="hidden" name="sol_nuevo_salario" id="sol_nuevo_salario" value="<?php echo $salarioOriginal; ?>">
                                    </div>


                                    <div class="col-md-6">
                                        <label class="form-label" for="sol_tipo_otrosi">Tipo de Solicitud Otro Si</label>
                                        <input class="form-control" name="sol_tipo_otrosi" id="sol_tipo_otrosi" value="<?php echo $sol_tipo_otrosi; ?>" readonly>
                                    </div>
                                    <div class="col-12 col-md-3 col-sm-6 mb-3">
                                        <label class="form-label" for="sol_fecha_inicio">Fecha de Inicio de Otro Si</label>
                                        <input class="form-control" name="sol_fecha_inicio" id="sol_fecha_inicio" type="date" value="<?php echo $sol_fecha_inicio; ?>">
                                    </div>
                                    <div class="col-12 col-md-3 col-sm-6 mb-3">
                                        <label class="form-label" for="sol_nueva_fecha_final">Nueva Fecha Final</label>
                                        <input class="form-control" name="sol_nueva_fecha_final" id="sol_nueva_fecha_final" type="date" value="<?php echo $sol_nueva_fecha_final; ?>">
                                    </div>

                                    <-- DURACIÓN --
                                    <div class="col-12 col-md-3 col-sm-6 mb-3">
                                        <label class="form-label" for="duracion_visible">Duración de Otro Sí</label>
                                        <input class="form-control" id="sol_duracion" value="<?php echo $sol_duracion; ?>" readonly>
                                        <input type="hidden" name="sol_duracion" id="sol_duracion_hidden" value="<?php echo $sol_duracion; ?>">
                                    </div>

                                    <-- VALOR TOTAL OTROSÍ --
                                    <div class="col-12 col-md-3 col-sm-6 mb-3">
                                        <label class="form-label" for="valor_otrosi_visible">Valor total otrosí</label><span class="input-group-text">$
                                            <input class="form-control" id="valor_otrosi_visible" value="<?php echo $valorFormateadoOtrosi; ?> " oninput="formatCurrency(this, 'sol_valor_otrosi')" required></span>
                                        <input type="hidden" name="sol_valor_otrosi" id="sol_valor_otrosi" value="<?php echo $sol_valor_otrosi; ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label" for="sol_motivo">Justificación Técnica</label>
                                        <textarea class="form-control" name="sol_motivo" id="sol_motivo" readonly
                                            oninput="autoResize(this)"><?php echo $sol_motivo; ?></textarea>
                                    </div>

                                    <script>
                                        function autoResize(textarea) {
                                            textarea.style.height = 'auto'; // Reinicia el alto
                                            textarea.style.height = (textarea.scrollHeight) + 'px'; // Ajusta al contenido
                                        }

                                        // Llamar autoResize cuando cargue la página
                                        window.addEventListener('DOMContentLoaded', () => {
                                            const textarea = document.getElementById('sol_motivo');
                                            if (textarea) autoResize(textarea);
                                        });
                                    </script>

                                </div>

                                <input type="hidden" id="con_id" name="con_id" value="<?php echo $con_id; ?>">
                                <input type="hidden" id="solicitud_id" name="id" value="<?php echo $id; ?>">


                                <-- Botones principales --
                                <div class="mt-4 d-flex justify-content-center">
                                    <button type="button" id="btn_aprobacion" class="btn btn-success" style="margin-right: 1cm;">Aprobar</button>
                                    <button type="button" id="btn_devolucion" class="btn btn-secondary" style="margin-right: 1cm;">Modificar</button>
                                    <button type="button" id="btn-rechazar" class="btn btn-danger">Rechazar</button>
                                </div>

                                <-- Campo de observación (se oculta inicialmente) --
                                <div id="campo-observacion" style="display:none;" class="mt-3">
                                    <textarea class="form-control" id="razon" placeholder="Escriba la razón del rechazo"></textarea>
                                    <button class="btn btn-primary mt-2" id="enviar-rechazo">Enviar Razón</button>
                                    <input type="hidden" id="correo" value="correo@ejemplo.com">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> -->


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://kit.fontawesome.com/9944c94262.js" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="../../../js/scripts.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>

<script>
    $(document).ready(function() {
        if ($('#dataTable').length) {
            $('#dataTable').DataTable();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const btnAprobado = document.getElementById("btn_aprobacion");
        if (!btnAprobado) {
            console.error("No se encontró el botón 'Aprobar'.");
            return;
        }
        btnAprobado.addEventListener("click", function(event) {
            event.preventDefault();
            const numeroIdentidad = document.getElementById("con_num_identidad").value;
            if (!numeroIdentidad) {
                Swal.fire({
                    title: 'Error',
                    text: 'No se encontró el número de identidad del contratista.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
                return;
            }
            Swal.fire({
                title: '¿Está seguro?',
                text: "Deseas aprobar la solicitud.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: "#198754",
                confirmButtonText: 'Sí, aprobar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) return;

                // Enviar aprobación al servidor
                fetch("/arbimaps/Arbimaps/vistas/Personal/acciones/aprobar_gerencia.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify({
                            con_num_identidad: numeroIdentidad
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log("Respuesta del servidor:", data);
                        if (data.success) {
                            Swal.fire({
                                title: 'Aprobado',
                                text: 'La solicitud ha sido aprobada correctamente.',
                                icon: 'success',
                                confirmButtonText: 'Aceptar'
                            }).then(() => {
                                window.location.href = "/arbimaps/Arbimaps/index.php?page=Personal/gerencia";
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.message || 'No se pudo aprobar la solicitud.',
                                icon: 'error',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    })
                    .catch((error) => {
                        console.error("Error al procesar la aprobación:", error);
                        Swal.fire({
                            title: 'Error',
                            text: 'Error inesperado al aprobar la solicitud.',
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                    });
            });
        });
    });


    document.addEventListener('DOMContentLoaded', function() {
        const btnRechazar = document.getElementById("btn-rechazar");
        const campoObservacion = document.getElementById("campo-observacion");
        const enviarRechazo = document.getElementById("enviar-rechazo");

        btnRechazar.addEventListener("click", function() {
            campoObservacion.style.display = "block";
        });

        enviarRechazo.addEventListener("click", function(event) {
            event.preventDefault();
            const razon = document.getElementById("razon").value.trim();
            const solicitudId = document.getElementById("solicitud_id").value;
            if (!razon) {
                Swal.fire("Error", "Debe escribir una razón para el rechazo", "error");
                return;
            }
            Swal.fire({
                title: '¿Está seguro?',
                text: "Esta acción rechazará la solicitud.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: "#DC3545",
                confirmButtonText: 'Sí, rechazar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("/arbimaps/Arbimaps/vistas/Personal/acciones/rechazo_gerencia.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                            },
                            body: JSON.stringify({
                                id: solicitudId,
                                motivo: razon
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire("Rechazado", "La solicitud ha sido rechazada", "success")
                                    .then(() => {
                                        window.location.href = "/arbimaps/Arbimaps/index.php?page=Personal/gerencia";
                                    });
                            } else {
                                Swal.fire("Error", data.message || "No se pudo rechazar la solicitud", "error");
                            }
                        })
                        .catch(error => {
                            console.error("Error en la solicitud:", error);
                            Swal.fire("Error", "Error de red o del servidor", "error");
                        });
                }
            });
        });
    });

    // función para realizar duracion
    document.getElementById("sol_fecha_inicio").addEventListener("change", calcularDuracionSolicitud);
    document.getElementById("sol_nueva_fecha_final").addEventListener("change", calcularDuracionSolicitud);

    function calcularDuracionSolicitud() {
        const inicio = document.getElementById("sol_fecha_inicio").value;
        const final = document.getElementById("sol_nueva_fecha_final").value;

        if (inicio && final) {
            const fechaInicio = new Date(inicio);
            const fechaFinal = new Date(final);

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

                document.getElementById("sol_duracion").value = duracion;
                document.getElementById("sol_duracion_hidden").value = duracion;
            } else {
                document.getElementById("sol_duracion").value = "Fecha final inválida";
                document.getElementById("sol_duracion_hidden").value = "";
            }
        }
    }

    // funcion devolucion gerencia
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("btn_devolucion").addEventListener("click", function() {
            Swal.fire({
                title: 'Razón de la devolución',
                input: 'textarea',
                inputPlaceholder: 'Escribe aquí la razón de la devolución...',
                inputAttributes: {
                    'aria-label': 'Razón de la devolución'
                },
                showCancelButton: true,
                confirmButtonColor: "#002f55",
                confirmButtonText: 'Enviar',
                cancelButtonText: 'Cancelar',
                preConfirm: (razon) => {
                    if (!razon) {
                        Swal.showValidationMessage('La razón es obligatoria');
                    }
                    return razon;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const razon = result.value;

                    const form = document.getElementById("multiStepForm");
                    if (!form) {
                        return;
                    }

                    const formData = new FormData(form);

                    formData.append("accion", "devolver");
                    formData.append("razon_devolucion", razon);
                    formData.append("sol_motivo_devolucion", razon);

                    fetch("/arbimaps/Arbimaps/vistas/Personal/acciones/modificar_gerencia.php", {
                            method: "POST",
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === "ok") {
                                Swal.fire("¡Éxito!", "Cambios de devolución guardados con éxito", "success").then(() => {
                                    window.location.href = "/arbimaps/Arbimaps/index.php?page=Personal/gerencia";
                                });
                            } else {
                                Swal.fire("Error", data.message || "Algo falló al guardar los cambios", "error");
                            }
                        })
                        .catch(err => {
                            Swal.fire("Error", "Error de red o del servidor", "error");
                        });
                }
            });
        });
    });

    // funcion honorarios
    function formatCurrency(input, hiddenId) {
        let value = input.value.replace(/\D/g, "");
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
</script>