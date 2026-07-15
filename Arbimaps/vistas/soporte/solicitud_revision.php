<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 2) . '/config/permisos.php';
require_once dirname(__DIR__, 3) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('GET');
neiva_require_permission('soporte.ticket', $PERMISOS);

$codigo_error = 'BUG-0001';
$formAction = neiva_app_url('Arbimaps/vistas/soporte/acciones/cargar_reporte.php');

$id_usuario_sesion = $_SESSION['id_usuario'] ?? null;
$nombre_solicitante = '';
$apellido_solicitante = '';
$correo_solicitante = '';
$celular_solicitante = '';

if ($id_usuario_sesion && isset($mysqli) && $mysqli instanceof mysqli) {
    $query_usuario = "SELECT nombre_usuario, apellido_usuario, correo_usuario, celular_usuario 
                      FROM usuarios_cons 
                      WHERE id_usuario = ?";
    $stmt_usuario = $mysqli->prepare($query_usuario);
    if ($stmt_usuario) {
        $stmt_usuario->bind_param("i", $id_usuario_sesion);
        $stmt_usuario->execute();
        $resultado_usuario = $stmt_usuario->get_result();
        if ($resultado_usuario && $resultado_usuario->num_rows > 0) {
            $datos_usuario = $resultado_usuario->fetch_assoc();
            $nombre_solicitante = $datos_usuario['nombre_usuario'] ?? '';
            $apellido_solicitante = $datos_usuario['apellido_usuario'] ?? '';
            $correo_solicitante = $datos_usuario['correo_usuario'] ?? '';
            $celular_solicitante = $datos_usuario['celular_usuario'] ?? '';
        }
        $stmt_usuario->close();
    }
}

if (isset($mysqli) && $mysqli instanceof mysqli) {
    $datos = $mysqli->query("SELECT MAX(id_soporte) AS id_maximo FROM solicitud_soporte");
    $cargar = $datos->fetch_assoc();
    $nextId = (int)$cargar['id_maximo'] + 1;
    $codigo_error = 'BUG-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
}

$usuarios = [];

if (isset($mysqli) && $mysqli instanceof mysqli) {
    $usuarios_cons1 =
        "SELECT 
            id_usuario, 
            rol_usuario
        FROM usuarios_cons
        WHERE rol_usuario = 'soporte'
        ";

    $datos_usuarios = $mysqli->query($usuarios_cons1);

    if ($datos_usuarios && $datos_usuarios->num_rows > 0) {
        while ($mostrar = $datos_usuarios->fetch_assoc()) {
            $usuarios[] = $mostrar;
        }
    } else {
        $usuarios_cons2 =
            "SELECT 
                    id_usuario, 
                    nombre_usuario,
                    apellido_usuario
                FROM usuarios_cons
            ";
        $datos_usuarios = $mysqli->query($usuarios_cons2);

        while ($mostrar = $datos_usuarios->fetch_assoc()) {
            $usuarios[] = $mostrar;
        }
    }
}
?>

<style>
    .error-container {
        min-height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        background-color: #ffffffff;
    }

    .error-content {
        text-align: center;
        color: #FFFFFF;
        z-index: 2;
        animation: fadeIn 1s ease-in;
    }


    .floating-shapes {
        position: absolute;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: 1;
    }

    .shape {
        position: absolute;
        /* background-color: #002f555d; */
        background: radial-gradient(circle, rgba(0, 47, 85, 0.5) 61%, rgba(255, 255, 255, 0.88) 100%);
        border-radius: 50%;
        animation: float-shapes 20s infinite ease-in-out;
    }

    .shape1 {
        width: 300px;
        height: 300px;
        top: 10%;
        left: -150px;
        animation-delay: 0s;
    }

    .shape2 {
        width: 200px;
        height: 200px;
        bottom: 20%;
        right: -100px;
        animation-delay: 0.5s;
    }

    .shape3 {
        width: 150px;
        height: 150px;
        top: 60%;
        left: 10%;
        animation-delay: 1s;
    }

    .shape4 {
        width: 90px;
        height: 90px;
        top: 10%;
        left: 90%;
        animation-delay: 1.2s;
    }

    .shape5 {
        width: 150px;
        height: 150px;
        top: 90%;
        left: 10%;
        animation-delay: 2s;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-20px);
        }
    }

    @keyframes float-shapes {

        0%,
        100% {
            transform: translate(0, 0) rotate(0deg);
        }

        25% {
            transform: translate(50px, -50px) rotate(90deg);
        }

        50% {
            transform: translate(0, -100px) rotate(180deg);
        }

        75% {
            transform: translate(-50px, -50px) rotate(270deg);
        }
    }

    @media (max-width: 768px) {
        .error-number {
            font-size: 6rem;
        }

        .error-title {
            font-size: 1.5rem;
        }

        .error-description {
            font-size: 1rem;
        }
    }
</style>

<style>
    .card-especial .estado-select {
        border-radius: 999px;
        border: 1px solid #d0d7de;
        padding: 0.45rem 1rem;
        font-size: 0.9rem;
        background-color: #f9fafb;
        transition: all 0.2s ease;
        box-shadow: none;
    }

    .card-especial .estado-select:hover {
        background-color: #f3f4f6;
        border-color: #9ca3af;
    }

    .card-especial .estado-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        background-color: #ffffff;
    }

    .card-especial .form-label {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        margin-bottom: 0.25rem;
    }

    .card-especial .estado-select option[value="bajo"],
    .card-especial .estado-select option[value="medio"],
    .card-especial .estado-select option[value="alto"],
    .card-especial .estado-select option[value="urgente"],
    .card-especial .estado-select option[value="nuevo"],
    .card-especial .estado-select option[value="antiguo"] {
        color: #9ca3af;
    }



    .contenedor-imagen {
        border: 2px dashed #9ca3af;
        border-radius: 8px;
        padding: 20px;
        max-width: 590px;
        text-align: center;
        font-family: sans-serif;
        min-height: 245px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .contenedor-imagen input[type="file"] {
        display: none;
    }

    .contenedor-imagen label.btn-elegir-imagen {
        display: inline-block;
        padding: 10px 15px;
        border-radius: 6px;
        background-color: #002f55;
        color: #fff;
        cursor: pointer;
        margin-bottom: 10px;
    }

    .contenedor-imagen img {
        display: none;
        width: 100%;
        max-width: 100%;
        height: 150px;
        object-fit: contain;
        border-radius: 6px;
    }
</style>

<div class="error-container rounded-5">
    <div class="floating-shapes">
        <div class="shape shape1"></div>
        <div class="shape shape2"></div>
        <div class="shape shape3"></div>
        <div class="shape shape4"></div>
        <div class="shape shape5"></div>
    </div>
    <div class="container-fluid" style="margin-bottom: 4%; z-index:999">
        <div class="my-4 text-center">
            <h3 class="mb-0 fw-bold" style="color: #002f55; font-weight: 700 !important">CREAR REPORTE / TICKET</h3>
            <small>Crea un reporte relacionado con fallas, solictudes o recomendaciones dentro de la plataforma.</small>
        </div>
        <form id="miFormulario"
            action="<?php echo htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>"
            method="POST"
            enctype="multipart/form-data">
            <?php echo neiva_csrf_input('global'); ?>
            <input type="hidden" name="nombre_solicitante" value="<?php echo htmlspecialchars($nombre_solicitante, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="apellido_solicitante" value="<?php echo htmlspecialchars($apellido_solicitante, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="correo_solicitante" value="<?php echo htmlspecialchars($correo_solicitante, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="celular_solicitante" value="<?php echo htmlspecialchars($celular_solicitante, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="row justify-content-center ">
                <div class="col-10 col-lg-9">
                    <div class="card card-especial-tres  shadow h-100 py-4 px-4 d-flex justify-content-center rounded-5 "
                        style="min-height: 70vh; border: 1px solid #002f553d">

                        <div class="row justify-content-center h-100 px-4">
                            <div class="col-4  p-2 ">
                                <div class="card card-especial-fecha text-white shadow p-2 justify-content-center " style="background-color: #002f55;">
                                    <label for="fecha_hora" class=" ms-3 form-label fw-bold ">Fecha y hora:</label>
                                    <input type="datetime-local"
                                        class="card text-white p-2 d-flex justify-content-center text-center" style="background-color: transparent;"
                                        id="fecha_hora" name="fecha_hora" readonly>
                                </div>
                            </div>

                            <div class="col-4  p-2">
                                <div class="card card-especial-codigo text-white shadow p-2 justify-content-center" style="background-color: #002f55;">
                                    <label for="codigo_error" class="ms-3 form-label"><b>Código de error:</b></label>
                                    <input
                                        type="text"
                                        id="codigo_error"
                                        name="codigo_error"
                                        class="form-control text-center card  p-2 d-flex justify-content-center text-white"
                                        style="background-color: transparent;"
                                        value="<?php echo htmlspecialchars($codigo_error, ENT_QUOTES, 'UTF-8'); ?>"
                                        readonly>
                                </div>
                            </div>

                            <div class="col-11 my-4" style="border-bottom: 2px dashed #002f5577;"></div>

                            <div class="col-6 py-3 ">
                                <div class="card  shadow-sm border  p-3 d-flex justify-content-center">
                                    <label for="asunto" class="form-label" >
                                        <b>Asunto</b>
                                    </label>
                                    <textarea
                                        id="asunto"
                                        name="asunto"
                                        class="form-control text-center"
                                        style="min-height: 140px;"
                                        placeholder="Escribe el asunto del reporte"
                                        required
                                        maxlength="500"></textarea>
                                </div>
                            </div>

                            <div class="col-6 py-3">
                                <div class="card  shadow-sm border p-3 d-flex justify-content-center">
                                    <label for="descripcion" class="form-label" >
                                        <b>Descripción</b>
                                    </label>
                                    <textarea
                                        id="descripcion"
                                        name="descripcion"
                                        class="form-control text-center"
                                        style="min-height: 140px;"
                                        placeholder="Incluye qué estabas haciendo, qué ocurrió y qué esperabas que ocurriera"
                                        required
                                        maxlength="500"></textarea>
                                </div>
                            </div>

                            <div class="col-6 py-3 d-flex align-items-center ">
                                <div class="row">
                                    <div class="col-12 ">
                                        <div class="card  shadow-sm border p-3 d-flex justify-content-center">
                                            <label for="prioridad" class="form-label"><b>Prioridad</b></label>
                                            <select id="prioridad" name="prioridad" class="form-select estado-select" required>
                                                <option class="text-center" value="" selected disabled>Seleccione una prioridad</option>
                                                <option class="text-center" value="bajo">Bajo</option>
                                                <option class="text-center" value="medio">Medio</option>
                                                <option class="text-center" value="alto">Alto</option>
                                                <option class="text-center" value="urgente">Urgente</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 py-3">
                                        <div class="card  shadow-sm border  p-3 d-flex justify-content-center">
                                            <label for="id_usuario" class="form-label"><b>Equipo de soporte</b></label>
                                            <select id="id_usuario" name="id_usuario" class="form-select estado-select" required>
                                                <option class="text-center" value="" selected disabled>Seleccione un usuario</option>
                                                <?php foreach ($usuarios as $u): ?>
                                                    <option value="<?php echo (int)$u['id_usuario']; ?>">
                                                        <?php
                                                        if (!empty($u['rol_usuario'])) {
                                                            echo htmlspecialchars($u['rol_usuario'], ENT_QUOTES, 'UTF-8');
                                                        } else {
                                                            $texto = ($u['nombre_usuario'] ?? '') . ' ' . ($u['apellido_usuario'] ?? '');
                                                            echo htmlspecialchars(trim($texto), ENT_QUOTES, 'UTF-8');
                                                        }
                                                        ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 ">
                                        <div class="card  shadow-sm border h-100 p-3 d-flex justify-content-center">
                                            <label for="tipo_error" class="form-label"><b>Tipo de error</b></label>
                                            <select id="tipo_error" name="tipo_error" class="form-select estado-select" required>
                                                <option value="" disabled selected>Seleccione un tipo de error</option>
                                                <option value="REGISTRO DE USUARIO">Registro de usuario</option>
                                                <option value="ACTUALIZACION DE PERFIL">Actualización de perfil</option>
                                                <option value="ASIGNACION DE TRAMITES">Asignacion de tramites</option>
                                                <option value="PERDIDA DE TRAMITES EN ASIGNACION">Perdida de tramites en asignacion</option>
                                                <option value="CARGA DE DOCUMENTOS">Carga de documentos</option>
                                                <option value="PERDIDA DE DOCUMENTOS">Perdida de documentos</option>
                                                <option value="ORDEN DE DOCUMENTOS">Orden de documentos</option>
                                            </select>
                                        </div>
                                    </div>


                                </div>
                            </div>

                            <div class="col-6 py-3">
                                <div class="card  shadow-sm border h-100 p-4 justify-content-center">
                                    <label class="form-label"><b>Subir imagen del error</b></label>
                                    <div class="contenedor-imagen">
                                        <p class="form-label texto-imagen" style="margin-top: 5%;">Selecciona una imagen</p>
                                        <label for="imagen" class="btn-elegir-imagen">Elegir archivo</label>
                                        <input type="file" id="imagen" name="imagen" accept="image/*">
                                        <img id="preview" alt="Vista previa">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 my-4" style="border-bottom: 2px dashed #002f5577;"></div>

                            <div class="col-6">
                                <div class="btn text-white w-100">
                                    <button type="submit" class="btn text-white"
                                        style="background-color: #022F55; width: 100%; height: 100%;
                                        border-radius: 12px;">
                                        <i class="bi bi-ticket-detailed me-2"></i>
                                        Crear ticket
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

</div>

<script>
    window.addEventListener('load', function() {
        const now = new Date();
        const tzoffset = now.getTimezoneOffset() * 60000;
        const localISOTime = new Date(Date.now() - tzoffset).toISOString().slice(0, 16);
        document.getElementById('fecha_hora').value = localISOTime;
    });
</script>

<script>
    const input = document.getElementById('imagen');
    const preview = document.getElementById('preview');
    const contenedor = document.querySelector('.contenedor-imagen');
    const texto = contenedor.querySelector('.texto-imagen');
    const btnElegir = contenedor.querySelector('.btn-elegir-imagen');

    input.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) {
            preview.style.display = 'none';
            preview.src = '';
            texto.style.display = 'block';
            btnElegir.style.display = 'inline-block';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            texto.style.display = 'none';
            btnElegir.style.display = 'none';
        };
        reader.readAsDataURL(file);
    });

    preview.addEventListener('click', function() {
        input.click();
    });
</script>

<?php if (isset($_GET['ok'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            icon: 'success',
            title: '¡Ticket generado!',
            text: 'El reporte se registró correctamente. Ahora puedes generar uno nuevo',
            confirmButtonColor: '#002f55'
        });
    </script>
<?php endif; ?>



<!-- codigo antiguo, se conserva para revisión de versiones -- (Es la sección de las cards) -->


<!-- <div class="card-header py-2 text-center" style="border-radius: 12px; background-color: #002F55; color: white;">
                            <h7 class="m-0 fw-bold ">REPORTE</h7>
                        </div> -->
<!-- <div class="card-body px-1">
                            <div class="form-row text-center">
                                <div class="card  shadow h-100 p-2 d-flex justify-content-center" style="width:40%; margin-left: 3%; min-height: 140px;">
                                    <label for="asunto" class="form-label" style="font-size: 0.9rem;">
                                        <b>Asunto</b>
                                    </label>
                                    <textarea
                                        id="asunto"
                                        name="asunto"
                                        class="form-control text-center"
                                        style="min-height: 140px;"
                                        placeholder="Escriba el asunto del reporte"
                                        required
                                        maxlength="500"></textarea>
                                </div>
                                <div class="card  shadow h-100 p-2 d-flex justify-content-center"
                                    style="width:51%; margin-left: 46%; margin-top: -14.8%; min-height: 140px;">
                                    <label for="descripcion" class="form-label" style="font-size: 0.9rem;">
                                        <b>Descripción</b>
                                    </label>
                                    <textarea
                                        id="descripcion"
                                        name="descripcion"
                                        class="form-control text-center"
                                        style="min-height: 140px;"
                                        placeholder="Describa el error que presenta"
                                        required
                                        maxlength="500"></textarea>
                                </div>
                                <div class="card  shadow h-100 p-2 d-flex justify-content-center"
                                    style="margin-left: 3%; margin-top: 3%; width: 40%;">
                                    <label for="codigo_error" class="form-label"><b>Código de error</b></label>
                                    <input
                                        type="text"
                                        id="codigo_error"
                                        name="codigo_error"
                                        class="form-control text-center"
                                        value="<?php echo htmlspecialchars($codigo_error, ENT_QUOTES, 'UTF-8'); ?>"
                                        readonly>
                                </div>
                                <div class="card  shadow h-100 p-2 d-flex justify-content-center"
                                    style="margin-left: -40%; margin-top: 12%; width: 40%;">
                                    <label for="prioridad" class="form-label"><b>Prioridad</b></label>
                                    <select id="prioridad" name="prioridad" class="form-select estado-select" required>
                                        <option class="text-center" value="" selected disabled>Seleccione una prioridad</option>
                                        <option class="text-center" value="bajo">Bajo</option>
                                        <option class="text-center" value="medio">Medio</option>
                                        <option class="text-center" value="alto">Alto</option>
                                        <option class="text-center" value="urgente">Urgente</option>
                                    </select>
                                </div>
                                <div class="card  shadow h-100 p-2 justify-content-center" style="margin-top: 20%; margin-left: -40%; width: 40%;">
                                    <label for="fecha_hora" class="form-label">Fecha y hora:</label>
                                    <input type="datetime-local"
                                        class="card  shadow h-100 p-2 d-flex justify-content-center text-center"
                                        id="fecha_hora" name="fecha_hora" readonly>
                                </div>
                                <div class="card  shadow h-100 p-2 justify-content-center" style="margin-left: 4%; margin-top: 3%; width: 49%;">
                                    <label class="form-label"><b>Subir imagen del error</b></label>
                                    <div class="contenedor-imagen">
                                        <p class="form-label texto-imagen" style="margin-top: 5%;">Selecciona una imagen</p>
                                        <label for="imagen" class="btn-elegir-imagen">Elegir archivo</label>
                                        <input type="file" id="imagen" name="imagen" accept="image/*">
                                        <img id="preview" alt="Vista previa">
                                    </div>
                                </div>
                                <div class="card  shadow h-100 p-2 d-flex justify-content-center"
                                    style="margin-left: 3%; margin-top: 3%; width: 40%;">
                                    <label for="id_usuario" class="form-label"><b>Equipo de soporte</b></label>
                                    <select id="id_usuario" name="id_usuario" class="form-select estado-select" required>
                                        <option class="text-center" value="" selected disabled>Seleccione un usuario</option>
                                        <?php foreach ($usuarios as $u): ?>
                                            <option value="<?php echo (int)$u['id_usuario']; ?>">
                                                <?php
                                                if (!empty($u['rol_usuario'])) {
                                                    echo htmlspecialchars($u['rol_usuario'], ENT_QUOTES, 'UTF-8');
                                                } else {
                                                    $texto = ($u['nombre_usuario'] ?? '') . ' ' . ($u['apellido_usuario'] ?? '');
                                                    echo htmlspecialchars(trim($texto), ENT_QUOTES, 'UTF-8');
                                                }
                                                ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="card  shadow h-100 p-2 d-flex justify-content-center"
                                    style="margin-left: 3%; margin-top: 3%; width: 51%;">
                                    <label for="tipo_error" class="form-label"><b>Tipo de error</b></label>
                                    <select id="tipo_error" name="tipo_error" class="form-select estado-select" required>
                                        <option value="" disabled selected>Seleccione un tipo de error</option>
                                        <option value="REGISTRO DE USUARIO">Registro de usuario</option>
                                        <option value="ACTUALIZACION DE PERFIL">Actualización de perfil</option>
                                        <option value="ASIGNACION DE TRAMITES">Asignacion de tramites</option>
                                        <option value="PERDIDA DE TRAMITES EN ASIGNACION">Perdida de tramites en asignacion</option>
                                        <option value="CARGA DE DOCUMENTOS">Carga de documentos</option>
                                        <option value="PERDIDA DE DOCUMENTOS">Perdida de documentos</option>
                                        <option value="ORDEN DE DOCUMENTOS">Orden de documentos</option>
                                    </select>
                                </div>
                            </div>
                            <br>
                            <br>
                            <div class="btn text-white"
                                style="background-color: #022F55; width: 90%; height: 50px;
                                    display: flex; justify-content: center; align-items: center;
                                    border-radius: 12px; margin-top: -2%; margin-left: 5%;">
                                <button type="submit" class="btn text-white"
                                    style="background-color: #022F55; width: 100%; height: 100%;
                                        border-radius: 12px;">
                                    Cargar
                                </button>
                            </div>
                        </div> -->
