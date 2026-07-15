<?php
$idUsuario = $_SESSION['id_usuario'];

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
            $con_nombres            = $row['con_nombres'];
            $con_apellidos          = $row['con_apellidos'];
            $con_tipo_documento     = $row['con_tipo_documento'];
            $con_num_identidad      = $row['con_num_identidad'];
            $con_fecha_inicio       = $row['con_fecha_inicio'];
            $sol_fecha_inicio       = $row['sol_fecha_inicio'];
            $sol_nueva_fecha_final  = $row['sol_nueva_fecha_final'];
            $con_fecha_final        = $row['con_fecha_final'];
            $sol_duracion           = $row['sol_duracion'];
            $sol_nuevo_rol          = $row['sol_nuevo_rol'];
            $sol_nuevo_proyecto     = $row['sol_nuevo_proyecto'];
            $sol_nuevo_salario      = $row['sol_nuevo_salario'];
            $sol_tipo_otrosi        = $row['sol_tipo_otrosi'];
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

function formatoCOP($valor): string
{
    // Si viene null o string vacío, mostramos 0 (o puedes devolver '' si prefieres)
    if ($valor === null || $valor === '') {
        return '$ 0';
    }

    // Si viene como string numérico desde MySQL, lo convertimos a número
    // (si llegara con separadores, esto también ayuda)
    if (is_string($valor)) {
        $valor = str_replace(['.', ' '], '', $valor);
        $valor = str_replace(',', '.', $valor);
    }

    $num = (float)$valor;
    return '$ ' . number_format($num, 0, ',', '.');
}

$valorFormateado  = formatoCOP($sol_nuevo_salario);
$valorFormateado2 = formatoCOP($sol_valor_otrosi);

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
        <h1 class="h3  text-center mb-0 fw-bold" style="color:#002F55">Carga de documento otro sí</h1>
        <small class="text-muted">Módulo para el cargue de documento de contrato</small>
    </div>

    <div class="card rounded-4 m-3 shadow p-2">
        <form id="multiStepForm">
            <div class="container-fluid">
                <div class="form-section" id="section1">

                    <div class="row p-2 justify-content-center ">
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
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar-date"></i></span>
                                <input class="form-control" name="con_fecha_final" id="con_fecha_final" value="<?php echo $con_fecha_final; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="con_expedicion" class="form-label fw-bold" style="font-size:0.9em;">Cargo / Rol</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-file-person-fill"></i></span>
                                <input class="form-control" name="sol_nuevo_rol" id="sol_nuevo_rol" value="<?php echo $sol_nuevo_rol; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="con_expedicion" class="form-label fw-bold" style="font-size:0.9em;">Proyecto</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-bezier"></i></span>
                                <input class="form-control" name="sol_nuevo_proyecto" id="sol_nuevo_proyecto" value="<?php echo $sol_nuevo_proyecto; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="con_salario_mostrado" class="form-label fw-bold" style="font-size:0.9em;">Salario/Honorarios</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm text-white" style="background-color: #002F55;border:1px solid #002F55;"><i class="bi bi-piggy-bank"></i></span>
                                <input class="form-control" name="sol_nuevo_salario" id="sol_nuevo_salario" value="<?php echo $valorFormateado; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="con_valor_proyecto_mostrado" class="form-label fw-bold" style="font-size:0.9em;">Valor Total Otro Sí</label>
                            <div class="input-group shadow-sm shadow-warning">
                                <span class="input-group-text shadow-sm bg-warning border border-warning"><i class="bi bi-cash-coin"></i></span>
                                <input class="form-control  border border-warning" name="sol_valor_otrosi" id="sol_valor_otrosi" value="<?php echo $valorFormateado2; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label class="form-label" for="sol_tipo_otrosi" style="font-size:0.9em;">Tipo de solicitud del otro si</label>
                            <div class="input-group shadow-sm shadow-warning">
                                <span class="input-group-text "><i class="bi bi-file-earmark-text"></i></span>
                                <input class="form-control" name="sol_tipo_otrosi" id="sol_tipo_otrosi" value="<?php echo $sol_tipo_otrosi; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="sol_fecha_inicio" class="form-label fw-bold" style="font-size:0.9em;">Fecha de Inicio de otro si</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm text-white" style="background-color: #002F55;border:1px solid #002F55;"><i class="bi bi-calendar-date"></i></span>
                                <input class="form-control" style="border:1px solid #002F55; border-bottom-right-radius:6px; border-top-right-radius:6px" name="sol_fecha_inicio" id="sol_fecha_inicio" type="date" value="<?php echo $sol_fecha_inicio; ?>">
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="sol_nueva_fecha_final" class="form-label fw-bold" style="font-size:0.9em;">Nueva Fecha Final</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm text-white" style="background-color: #002F55;border:1px solid #002F55;"><i class="bi bi-calendar-date"></i></span>
                                <input class="form-control" style="border:1px solid #002F55; border-bottom-right-radius:6px; border-top-right-radius:6px" name="sol_nueva_fecha_final" id="sol_nueva_fecha_final" type="date" value="<?php echo $sol_nueva_fecha_final; ?>">
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-sm-6 mb-3">
                            <label for="sol_duracion" class="form-label fw-bold" style="font-size:0.8em;">Duración de Otro Sí</label>
                            <div class="input-group ">
                                <span class="input-group-text shadow-sm"><i class="bi bi-calendar2-event"></i></span>
                                <input class="form-control" id="sol_duracion" value="<?php echo $sol_duracion; ?>" readonly>
                                <input type="hidden" name="sol_duracion" id="sol_duracion_hidden" value="<?php echo $sol_duracion; ?>">
                            </div>
                        </div>

                        <!-- <div class="col-12 col-md-6 file-upload-item mb-3">
                            <label class="form-label" for="sol_archivo_otrosi">Nuevo Otro Si</label>
                            <input class="custom-file" id="sol_archivo_otrosi" name="sol_archivo_otrosi" type="file">
                        </div> -->

                        <div class="col-9 mt-5 px-0 ">
                            <div class="position-relative h-100">

                                <!-- Icono flotante -->
                                <div class="position-absolute top-0 start-50 translate-middle" style="z-index:99">
                                    <div class="rounded-5 shadow d-flex align-items-center justify-content-center"
                                        style="width:45px; height:45px; background-color:#002f55 !important;">
                                        <i class="bi bi-file-earmark-pdf text-white fs-4"></i>
                                    </div>
                                </div>

                                <!-- Card -->
                                <div class="card-especial d-flex flex-column justify-content-center shadow rounded-4 pt-4 p-4 h-100 text-center" style="border: 1px solid #002f559e;">
                                    <h6 class="fw-semibold mb-1 mt-2" style="color:#002F55;">
                                        Cargar documento otro sí
                                    </h6>

                                    <div class="container-fluid">
                                        <div class="row align-items-center">
                                            <div class="col-8 px-1">
                                                <div class="input-group  shadow-sm">
                                                    <label class="input-group-text" for="sol_archivo_otrosi" style="font-size:0.8em;"><i class="bi bi-file-earmark-pdf"></i></label>
                                                    <input type="file" class="form-control" style="font-size:0.8em;" id="sol_archivo_otrosi" name="sol_archivo_otrosi">
                                                </div>
                                            </div>

                                            <div class="col-4">
                                                <input type="hidden" id="solicitud_id" name="solicitud_id" value="<?php echo $id; ?>">
                                                <div class=" d-flex justify-content-center">
                                                    <button type="button" id="btn_cargar" class="btn bg-success rounded-3 text-white w-75" > <i class="bi bi-file-earmark-arrow-up me-2"></i>Cargar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </form>
    </div>


</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://kit.fontawesome.com/9944c94262.js" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="../../../js/scripts.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable();
    });

    document.addEventListener('DOMContentLoaded', function() {
        const btnAprobado = document.getElementById("btn_cargar");
        if (btnAprobado) {
            btnAprobado.addEventListener("click", function(event) {
                event.preventDefault();

                const numeroIdentidad = document.getElementById("con_num_identidad").value;
                const archivoInput = document.getElementById("sol_archivo_otrosi");
                const solicitudId = document.getElementById("solicitud_id").value;

                if (!numeroIdentidad) {
                    console.error("El número de identidad no está disponible.");
                    return;
                }
                if (!solicitudId) {
                    console.error("El ID de la solicitud no está disponible.");
                    Swal.fire({
                        title: 'Error',
                        text: 'No se encontró la solicitud asociada',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                    return;
                }
                if (!archivoInput || archivoInput.files.length === 0) {
                    console.error("No se ha seleccionado un archivo.");
                    Swal.fire({
                        title: 'Error',
                        text: 'Debes seleccionar un archivo',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                    return;
                }

                const formData = new FormData();
                formData.append("con_num_identidad", numeroIdentidad);
                formData.append("solicitud_id", solicitudId);
                formData.append("sol_archivo_otrosi", archivoInput.files[0]);

                fetch("<?= neiva_app_url('Arbimaps/vistas/Personal/acciones/cargar_otrosi.php') ?>", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log("Respuesta del servidor:", data);
                        if (data.status === "ok") {
                            Swal.fire({
                                title: 'Éxito',
                                text: data.message || 'La solicitud se actualizó correctamente',
                                icon: 'success',
                                confirmButtonText: 'Aceptar'
                            }).then(() => {
                                window.location.href = `<?= neiva_app_url('Arbimaps/index.php?page=Personal/solicitudes_seguridad_social') ?>`;
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.message || 'No se pudo cargar la información',
                                icon: 'error',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    })
                    .catch((error) => {
                        console.error("Error al enviar el formulario:", error);
                        Swal.fire({
                            title: 'Error',
                            text: 'Error inesperado al enviar los datos',
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                    });
            });
        } else {
            console.error("No se encontró el botón 'btn_cargar'.");
        }
    });
</script>