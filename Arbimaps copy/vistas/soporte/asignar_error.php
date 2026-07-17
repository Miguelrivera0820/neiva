<?php
require $_SERVER['DOCUMENT_ROOT'] . '/arbimaps/conexion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$codigo = isset($_GET['codigo']) ? trim($_GET['codigo']) : null;

if (!$codigo) {
    echo '<div class="container mt-4">';
    echo '<div class="alert alert-warning">Código de error no especificado.</div>';
    echo '<a href="/arbimaps/Arbimaps/index.php?page=soporte/mesa_ayuda" class="btn btn-secondary">Volver</a>';
    echo '</div>';
    return;
}

$sql = "SELECT 
            codigo_error, 
            asunto, 
            descripcion, 
            prioridad, 
            tipo_error, 
            fecha_hora_creacion, 
            nombre_solicitante, 
            apellido_solicitante, 
            correo_solicitante, 
            celular_solicitante, 
            imagen_ruta, 
            id_usuario 
        FROM solicitud_soporte 
        WHERE codigo_error = ? 
        LIMIT 1";

$stmt = $mysqli->prepare($sql);

$stmt->bind_param('s', $codigo);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo '<div class="container mt-4">';
    echo '<div class="alert alert-info">No se encontró el reporte con código: ' . htmlspecialchars($codigo) . '</div>';
    echo '<a href="/arbimaps/Arbimaps/index.php?page=soporte/mesa_ayuda" class="btn btn-secondary">Volver</a>';
    echo '</div>';
    return;
}

// SOLO usuarios cuyo rol_usuario inicia con 'soporte'
$usuarios_cons = "SELECT 
        id_usuario,
        nombre_usuario, 
        apellido_usuario,
        rol_usuario
    FROM usuarios_cons
    WHERE rol_usuario LIKE 'soporte%'";

$buscar_datos   = $mysqli->prepare($usuarios_cons);
$buscar_datos->execute();
$datos_usuarios = $buscar_datos->get_result();

$imgSrc = !empty($row['imagen_ruta']) ? '/arbimaps/Arbimaps/vistas/soporte/' . ltrim($row['imagen_ruta'], '/') : null;
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<style>
    :root {
        --color-primario: #022F55;
        --color-primario-suave: #f1f5f9;
        --color-borde-suave: #e5e7eb;
        --color-texto-muted: #6b7280;
    }

    .contenedor-boton-volver {
        margin-top: 0;
        margin-right: 0;
    }

    .boton-volver {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.4rem 1.2rem;
        border-radius: 999px;
        color: var(--color-primario);
        border: 1px solid var(--color-primario);
        background-color: #ffffff;
        font-weight: 500;
        font-size: 0.9rem;
        text-decoration: none;
        box-shadow: 0 1px 4px rgba(2, 6, 23, 0.06);
        transition: all 0.2s ease-in-out;
    }

    .boton-volver:hover {
        background-color: var(--color-primario);
        color: #ffffff;
        text-decoration: none;
        box-shadow: 0 4px 10px rgba(2, 6, 23, 0.15);
        transform: translateY(-1px);
    }

    .wrapper-asignar-error {
        min-height: 70vh;
        background: linear-gradient(135deg, #f9fafb 0%, #eef2ff 100%);
        border-radius: 18px;
        border: 1px solid #e5e7eb;
    }

    .header-asignacion {
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .badge-estado {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.2rem 0.7rem;
        font-size: 0.75rem;
        font-weight: 600;
        background-color: #e0f2fe;
        color: #0369a1;
    }

    .titulo-seccion {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--color-texto-muted);
        margin-bottom: 0.25rem;
        font-weight: 600;
    }

    .card-detalles-reporte {
        border-radius: 12px;
        background-color: #ffffff;
        border: 1px solid var(--color-borde-suave);
    }

    .card-detalles-reporte dt {
        font-size: 0.85rem;
        color: var(--color-texto-muted);
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .card-detalles-reporte dd {
        font-size: 0.9rem;
        color: #111827;
        margin-bottom: 0.65rem;
    }

    .badge-prioridad {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.2rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-prioridad-bajo {
        background-color: #dcfce7;
        color: #166534;
    }

    .badge-prioridad-medio {
        background-color: #fef9c3;
        color: #854d0e;
    }

    .badge-prioridad-alto {
        background-color: #fee2e2;
        color: #b91c1c;
    }

    .badge-prioridad-urgente {
        background-color: #fee2e2;
        color: #991b1b;
        box-shadow: 0 0 0 1px rgba(220, 38, 38, 0.3);
    }

    .badge-tipo {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.2rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 500;
        background-color: #eff6ff;
        color: #1d4ed8;
        margin-left: 0.5rem;
    }

    .card-imagen-reporte {
        border-radius: 12px;
        background-color: #ffffff;
        border: 1px solid var(--color-borde-suave);
    }

    .image-hover-wrapper {
        position: relative;
        width: 100%;
        height: 320px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-radius: 10px;
        border: 3px dashed #002f557d;
        text-decoration: none;
    }

    .image-hover-img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        transition: transform 0.3s ease, filter 0.3s ease;
        border-radius: 10px;
    }

    /* Overlay */
    .image-hover-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 47, 85, 0.75);
        color: #fff;
        font-weight: 600;
        font-size: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        opacity: 0;
        transition: opacity 0.3s ease;
        backdrop-filter: blur(2px);
    }

    /* Hover effects */
    .image-hover-wrapper:hover .image-hover-overlay {
        opacity: 1;
    }

    .image-hover-wrapper:hover .image-hover-img {
        transform: scale(1.05);
        filter: brightness(0.85);
    }
</style>

<div class="container-fluid h-100 justify-content-center p-3">

    <div class="row  h-100 justify-content-center align-items-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow border rounded-4  px-0">
                <div class="card-header d-flex justify-content-between align-items-center px-4  py-3" style="background-color: #002f5526;">

                    <div>
                        <span>Ticket:</span>
                        <h4 class="mb-0 fw-bold" style="font-size: 1.5rem;">
                            <?php echo htmlspecialchars($row['codigo_error']); ?>
                        </h4>
                        <span class="text-muted small ">
                            Creado: <?php echo htmlspecialchars($row['fecha_hora_creacion'] ?? ''); ?>
                        </span>
                    </div>

                    <div class="contenedor-boton-volver">
                        <a href="/arbimaps/Arbimaps/index.php?page=soporte/mesa_ayuda"
                            class="boton-volver">
                            <i class="bi bi-chevron-left me-1 aling-content-center"></i> Volver
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12 col-lg-7  px-0  justify-content-center align-content-center">
                                <div class="card rounded-5 mb-3  shadow-sm p-3 card-detalles-reporte">

                                    <p class="my-2 text-center mb-2">
                                        <strong>Asunto: </strong>
                                        <?php echo htmlspecialchars($row['asunto']); ?>
                                    </p>

                                    <div class="d-flex align-items-center justify-content-between  my-3">
                                        <?php
                                        $prioridad = strtolower($row['prioridad'] ?? '');
                                        $clasePrioridad = 'badge-prioridad-bajo';
                                        if ($prioridad === 'medio' || $prioridad === 'media') {
                                            $clasePrioridad = 'badge-prioridad-medio';
                                        } elseif ($prioridad === 'alto' || $prioridad === 'alta') {
                                            $clasePrioridad = 'badge-prioridad-alto';
                                        } elseif ($prioridad === 'urgente') {
                                            $clasePrioridad = 'badge-prioridad-urgente';
                                        }
                                        ?>
                                        <span class="badge-prioridad px-3 <?php echo $clasePrioridad; ?>">
                                            Prioridad: <?php echo htmlspecialchars($row['prioridad'] ?? 'N/A'); ?>
                                        </span>
                                        <?php if (!empty($row['tipo_error'])): ?>
                                            <span class="badge-tipo px-3">
                                                <?php echo htmlspecialchars($row['tipo_error']); ?>
                                            </span>
                                        <?php endif; ?>

                                    </div>

                                    <span class="fw-bold">Detalles del reporte:</span>
                                    <hr>
                                    <dl class="row mb-0 mt-2">
                                        <dt class="col-sm-4 fw-bold">Descripción</dt>
                                        <dd class="col-sm-8">
                                            <?php echo nl2br(htmlspecialchars($row['descripcion'] ?? '')); ?>
                                        </dd>
                                        <dt class="col-sm-4 fw-bold">Solicitante</dt>
                                        <dd class="col-sm-8">
                                            <?php echo htmlspecialchars(($row['nombre_solicitante'] ?? '') . ' ' . ($row['apellido_solicitante'] ?? '')); ?>
                                        </dd>
                                        <dt class="col-sm-4 fw-bold">Correo</dt>
                                        <dd class="col-sm-8">
                                            <?php echo htmlspecialchars($row['correo_solicitante'] ?? ''); ?>
                                        </dd>
                                        <dt class="col-sm-4 fw-bold">Teléfono</dt>
                                        <dd class="col-sm-8">
                                            <?php echo htmlspecialchars($row['celular_solicitante'] ?? ''); ?>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                            <div class="col-12 col-lg-5 ">
                                <?php if ($imgSrc): ?>
                                    <div class="card  shadow-sm p-2 h-100 card-imagen-reporte justify-content-center align-items-center">
                                        <span class="titulo-seccion fw-bold text-center">Imagen del reporte</span>
                                        <a href="<?php echo htmlspecialchars($imgSrc); ?>"
                                            target="_blank"
                                            class="image-hover-wrapper">

                                            <img src="<?php echo htmlspecialchars($imgSrc); ?>"
                                                alt="Imagen reporte"
                                                class="image-hover-img">

                                            <div class="image-hover-overlay">
                                                <i class="bi bi-box-arrow-up-right me-2"></i>
                                                Ver en otra pestaña
                                            </div>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="card card-especial shadow-sm p-3 card-imagen-reporte">
                                        <span class="titulo-seccion">Imagen del reporte</span>
                                        <div class="alert alert-secondary mb-0 mt-3 text-center">
                                            Sin imagen adjunta
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-12 col-lg-4">
            <div class="card card shadow my-3  p-2 py-3">
                <span class="text-center my-2">Seleccione usuario al que va a asignar ticket</span>
                <form action="/arbimaps/Arbimaps/vistas/soporte/acciones/asignacion_error.php" method="POST">
                    <Input type="hidden" name="codigo_error" value="<?php echo htmlspecialchars($row['codigo_error'], ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="card card-especial shadow-sm p-3 card-detalles-reporte mt-4">
                        <span class="titulo-seccion">Equipo de soporte</span>
                        <div class="mt-2" style="max-height: 260px; overflow-y: auto;">
                            <?php if ($datos_usuarios && $datos_usuarios->num_rows > 0): ?>
                                <?php while ($u = $datos_usuarios->fetch_assoc()): ?>
                                    <label
                                        class="d-flex align-items-center justify-content-between mb-2 p-2"
                                        style="border: 1px solid #e5e7eb; border-radius: 10px; cursor: pointer; background-color: #ffffff;">

                                        <div class="d-flex align-items-center">
                                            <input
                                                type="radio"
                                                name="id_usuario_asignado"
                                                value="<?php echo (int)$u['id_usuario']; ?>"
                                                style="margin-right: 0.6rem;">
                                            <div>
                                                <div style="font-weight: 600;">
                                                    <?php echo htmlspecialchars($u['nombre_usuario'] . ' ' . $u['apellido_usuario'], ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <div style="font-size: 0.8rem; color: #6b7280;">
                                                    Rol: <?php echo htmlspecialchars($u['rol_usuario'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-muted mb-0" style="font-size: 0.9rem;">
                                    No se encontraron usuarios de soporte.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <Button type="submit"
                        class="btn shadow-sm mt-3 card-detalles-reporte "
                        style="width: 40%; margin-left:30%; text-decoration: none; cursor: pointer; border: none; background-color: #002F55;">
                        <small class="d-block my-2 text-center text-white"> <i class="bi bi-person-fill-add me-2"></i>Asignar error</small>
                    </Button>
                </form>
            </div>
        </div>
    </div>


    <!-- <div class="bg-white shadow rounded-5 p-4 h-100">

        <div class="header-asignacion d-flex justify-content-between align-items-start">
            <div class="d-flex align-items-start gap-3">
                <div class="d-flex align-items-center justify-content-center rounded-circle"
                    style="width: 46px; height: 46px; background-color: var(--color-primario-suave); margin-top: 5%;">
                </div>
                <div>
                    <span class="titulo-seccion">Asignación de error</span>
                    <h4 class="mb-1">
                        <?php echo htmlspecialchars($row['codigo_error']); ?>
                    </h4>
                    <p class="mb-1 text-muted small">
                        <?php echo htmlspecialchars($row['asunto']); ?>
                    </p>
                    <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
                        <?php
                        $prioridad = strtolower($row['prioridad'] ?? '');
                        $clasePrioridad = 'badge-prioridad-bajo';
                        if ($prioridad === 'medio' || $prioridad === 'media') {
                            $clasePrioridad = 'badge-prioridad-medio';
                        } elseif ($prioridad === 'alto' || $prioridad === 'alta') {
                            $clasePrioridad = 'badge-prioridad-alto';
                        } elseif ($prioridad === 'urgente') {
                            $clasePrioridad = 'badge-prioridad-urgente';
                        }
                        ?>
                        <span class="badge-prioridad <?php echo $clasePrioridad; ?>">
                            Prioridad: <?php echo htmlspecialchars($row['prioridad'] ?? 'N/A'); ?>
                        </span>
                        <?php if (!empty($row['tipo_error'])): ?>
                            <span class="badge-tipo">
                                <?php echo htmlspecialchars($row['tipo_error']); ?>
                            </span>
                        <?php endif; ?>
                        <span class="text-muted small ms-1">
                            Creado: <?php echo htmlspecialchars($row['fecha_hora_creacion'] ?? ''); ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="contenedor-boton-volver">
                <a href="/arbimaps/Arbimaps/index.php?page=soporte/mesa_ayuda"
                    class="boton-volver">
                    Volver
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 mb-3 mb-md-0">
                <div class="card card-especial shadow-sm p-3 card-detalles-reporte">
                    <span class="titulo-seccion">Detalles del reporte</span>

                    <dl class="row mb-0 mt-2">
                        <dt class="col-sm-4">Descripción</dt>
                        <dd class="col-sm-8">
                            <?php echo nl2br(htmlspecialchars($row['descripcion'] ?? '')); ?>
                        </dd>
                        <dt class="col-sm-4">Solicitante</dt>
                        <dd class="col-sm-8">
                            <?php echo htmlspecialchars(($row['nombre_solicitante'] ?? '') . ' ' . ($row['apellido_solicitante'] ?? '')); ?>
                        </dd>
                        <dt class="col-sm-4">Correo</dt>
                        <dd class="col-sm-8">
                            <?php echo htmlspecialchars($row['correo_solicitante'] ?? ''); ?>
                        </dd>
                        <dt class="col-sm-4">Teléfono</dt>
                        <dd class="col-sm-8">
                            <?php echo htmlspecialchars($row['celular_solicitante'] ?? ''); ?>
                        </dd>
                    </dl>
                </div>
                <br>
                <form action="/arbimaps/Arbimaps/vistas/soporte/acciones/asignacion_error.php" method="POST">
                    <Input type="hidden" name="codigo_error" value="<?php echo htmlspecialchars($row['codigo_error'], ENT_QUOTES, 'UTF-8'); ?>">
                    <Button type="submit"
                        class="card card-especial shadow-sm p-3 card-detalles-reporte mx-auto"
                        style="width: 40%; text-decoration: none; cursor: pointer; border: none; background-color: #ffffff;">
                        <small class="text-muted d-block my-2 text-center">Asignar error</small>
                    </Button>
                    <div class="card card-especial shadow-sm p-3 card-detalles-reporte mt-4">
                        <span class="titulo-seccion">Equipo de soporte</span>
                        <div class="mt-2" style="max-height: 260px; overflow-y: auto;">
                            <?php if ($datos_usuarios && $datos_usuarios->num_rows > 0): ?>
                                <?php while ($u = $datos_usuarios->fetch_assoc()): ?>
                                    <label
                                        class="d-flex align-items-center justify-content-between mb-2 p-2"
                                        style="border: 1px solid #e5e7eb; border-radius: 10px; cursor: pointer; background-color: #ffffff;">

                                        <div class="d-flex align-items-center">
                                            <input
                                                type="radio"
                                                name="id_usuario_asignado"
                                                value="<?php echo (int)$u['id_usuario']; ?>"
                                                style="margin-right: 0.6rem;">
                                            <div>
                                                <div style="font-weight: 600;">
                                                    <?php echo htmlspecialchars($u['nombre_usuario'] . ' ' . $u['apellido_usuario'], ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <div style="font-size: 0.8rem; color: #6b7280;">
                                                    Rol: <?php echo htmlspecialchars($u['rol_usuario'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-muted mb-0" style="font-size: 0.9rem;">
                                    No se encontraron usuarios de soporte.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-md-4">
                <?php if ($imgSrc): ?>
                    <div class="card card-especial shadow-sm p-3 card-imagen-reporte">
                        <span class="titulo-seccion text-center">Imagen del reporte</span>
                        <a href="<?php echo htmlspecialchars($imgSrc); ?>"
                            target="_blank"
                            style="width: 100%; height: 220px; display: flex; align-items: center; justify-content: center;
                                        overflow: hidden; border-radius: 10px; border: 1px solid #e5e7eb; margin-top: 0.5rem;">
                            <img src="<?php echo htmlspecialchars($imgSrc); ?>"
                                alt="Imagen reporte"
                                style="max-width: 100%; max-height: 100%; object-fit: contain; cursor: pointer; border-radius: 10px;">
                        </a>
                    </div>
                <?php else: ?>
                    <div class="card card-especial shadow-sm p-3 card-imagen-reporte">
                        <span class="titulo-seccion">Imagen del reporte</span>
                        <div class="alert alert-secondary mb-0 mt-3 text-center">
                            Sin imagen adjunta
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div> -->
</div>

<?php
$stmt->close();
?>

<?php if (isset($_GET['asignacion'])): ?>
    <script>
        (function() {
            const estado = <?= json_encode($_GET['asignacion']) ?>;

            if (estado === "ok") {
                Swal.fire({
                    icon: "success",
                    title: "Asignación exitosa",
                    text: "El error fue asignado correctamente.",
                    timer: 1800,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = "/arbimaps/Arbimaps/index.php?page=soporte/mesa_ayuda";
                });
            }

            if (estado === "missing") {
                Swal.fire({
                    icon: "warning",
                    title: "Datos incompletos",
                    text: "Debes seleccionar un usuario de soporte."
                });
            }

            if (estado === "fail" || estado === "error") {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo asignar el error. Intenta nuevamente."
                });
            }
        })();
    </script>
<?php endif; ?>

<script>
    document.querySelector("form")?.addEventListener("submit", function(e) {
        const seleccionado = this.querySelector('input[name="id_usuario_asignado"]:checked');

        if (!seleccionado) {
            e.preventDefault();
            Swal.fire({
                icon: "info",
                title: "Selecciona un usuario",
                text: "Debes elegir a quién asignar el ticket."
            });
        }
    });
</script>