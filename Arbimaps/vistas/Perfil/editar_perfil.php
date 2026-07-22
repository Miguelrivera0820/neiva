<?php
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$sql = "SELECT * FROM usuarios_cons WHERE id_usuario = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

if (isset($_SESSION['success_actualizacion'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            ' . $_SESSION['success_actualizacion'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
    unset($_SESSION['success_actualizacion']);
}

$foto_usuario_default = './assets/fotos_usuarios/default.png';
$foto_usuario_perfil  = $foto_usuario_default;
if (!empty($_SESSION['id_usuario']) && !empty($_SESSION['foto_user'])) {
    $ruta_relativa_foto = './assets/fotos_usuarios/' . $_SESSION['id_usuario'] . '/' . $_SESSION['foto_user'];
    $ruta_fisica_foto   = __DIR__ . '/../../assets/fotos_usuarios/' . $_SESSION['id_usuario'] . '/' . $_SESSION['foto_user'];

    if (file_exists($ruta_fisica_foto)) {
        $foto_usuario_perfil = $ruta_relativa_foto;
    }
}

if (isset($_SESSION['error_actualizacion'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            ' . $_SESSION['error_actualizacion'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
    unset($_SESSION['error_actualizacion']);
}

if (isset($_SESSION['debe_cambiar_password']) && $_SESSION['debe_cambiar_password'] == 1) {
    echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>¡Atención!</strong> Por razones de seguridad, debes actualizar tu contraseña antes de continuar navegando.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
}
?>

<style>
    .perfil-container {
        min-height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        background-color: #002F55;
    }

    .perfil-content {
        text-align: center;
        color: #FFFFFF;
        z-index: 2;
        animation: fadeIn 1s ease-in;
    }


    .shape {
        position: absolute;
        background-color: rgba(255, 255, 255, 0.05);
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
        animation-delay: 2s;
    }

    .shape3 {
        width: 150px;
        height: 150px;
        top: 60%;
        left: 10%;
        animation-delay: 4s;
    }

    .input-perfil {
        background-color: transparent;
        color: #fff !important;
    }

    .input-perfil:focus {
        color: #fff !important;
        background-color: transparent !important;
        border-color: #ffffff !important;
        box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25) !important;
    }

    .btn:hover {
        background-color: #FFFFFF !important;
        color: #002F55 !important;
    }

    .foto-perfil:hover {
        background-color: #00182b !important;
        cursor: pointer;
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

    .foto-container {
        position: relative;
        display: inline-block;
        width: 15em;
        height: 15em;
    }

    .foto-perfil {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        filter: drop-shadow(0px 0px 3px #ffffff7b);
    }

    /* Overlay oculto por defecto */
    .foto-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.8);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        cursor: pointer;
        transition: opacity 0.3s ease;
    }

    /* Mostrar overlay al hacer hover */
    .foto-container:hover .foto-overlay {
        opacity: 1;
    }

    /* Estilo para el ícono de toggle de contraseña */
    .toggle-password {
        cursor: pointer;
        color: #fff;
        background-color: transparent;
        border: none;
        padding: 0.375rem 0.75rem;
    }

    .toggle-password:hover {
        color: #ccc;
    }
</style>

<body>
    <div class="perfil-container rounded-5">
        <div class="floating-shapes">
            <div class="shape shape1"></div>
            <div class="shape shape2"></div>
            <div class="shape shape3"></div>
        </div>

        <div class="container-fluid p-5 text-center">
            <div class="my-5">
                <h2 class="text-white fw-bold">Editar tu perfil</h2>
                <small class="text-white ">
                    Aquí podrías consultar y editar la información de tu perfil
                </small>
            </div>
            <div class="row mt-2">
                <div class="col-12 col-lg-9 p-4 shadow-lg rounded-5 d-flex flex-column aling-items-center justify-content-center"
                    style="background-color:#00182b">
                    <div class="perfil-content mb-1 ">
                        <h3>Información del perfil</h3>
                    </div>

                    <div style="border-bottom:1px solid #ffffff55; margin-left:12.5%" class="mb-4 w-75"></div>

                    <form action="./vistas/Perfil/actualizacion/actualizar_perfil.php" method="POST" enctype="multipart/form-data">
                        <div class="form-row" style="animation: fadeIn 1.1s ease-in;">

                            <div class="col-12 col-md-4 p-1 px-2 my-2">
                                <label for="nombre_usuario" class="form-label text-white" style="font-size:0.9em">Nombre Usuario</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-person-circle"></i></span>
                                    <input type="text"
                                        class="form-control input-perfil text-white"
                                        style="font-size: 0.9em;"
                                        id="nombre_usuario" name="nombre_usuario"
                                        value="<?php echo htmlspecialchars($usuario['usuario_cons']); ?>" readonly>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 p-1 px-2 my-2">
                                <label for="password_cons" class="form-label text-white" style="font-size:0.9em">Contraseña (deja vacío para no cambiar)</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-unlock-fill"></i></span>
                                    <input type="password" class="form-control input-perfil text-white"
                                        style="font-size: 0.9em;"
                                        id="password_cons" name="password_cons"
                                        value="<?php echo htmlspecialchars($usuario['password_cons']); ?>">
                                    <span class="input-group-text toggle-password" onclick="togglePassword()">
                                        <i class="bi bi-eye" id="toggleIcon"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 p-1 px-2 my-2">
                                <label for="cedula_usuario" class="form-label text-white" style="font-size:0.9em">Número de cédula</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                                    <input type="text" class="form-control text-white input-perfil"
                                        style="font-size: 0.9em;"
                                        id="cedula_usuario" name="cedula_usuario"
                                        value="<?php echo htmlspecialchars($usuario['cedula_usuario']); ?>" readonly>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 p-1 px-2 my-2">
                                <label for="nombre_usuario_real" class="form-label text-white" style="font-size:0.9em">Nombres</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                    <input type="text" class="text-white form-control input-perfil"
                                        style="font-size: 0.9em;"
                                        id="nombre_usuario_real" name="nombre_usuario_real"
                                        value="<?php echo htmlspecialchars($usuario['nombre_usuario']); ?>" readonly>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 p-1 px-2 my-2">
                                <label for="apellido_usuario" class="form-label text-white" style="font-size:0.9em">Apellidos</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-people-fill"></i></span>
                                    <input type="text" class="text-white form-control input-perfil"
                                        style="font-size: 0.9em;"
                                        id="apellido_usuario" name="apellido_usuario"
                                        value="<?php echo htmlspecialchars($usuario['apellido_usuario']); ?>" readonly>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 p-1 px-2 my-2">
                                <label for="correo_usuario" class="form-label text-white" style="font-size:0.9em">Correo electrónico</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-envelope-plus"></i></span>
                                    <input type="email" class="text-white form-control input-perfil"
                                        style="font-size: 0.9em;"
                                        id="correo_usuario" name="correo_usuario"
                                        value="<?php echo htmlspecialchars($usuario['correo_usuario']); ?>">
                                </div>
                            </div>

                            <div class="col-12 col-md-6 p-1 px-2 my-2">
                                <label for="celular_usuario" class="form-label text-white" style="font-size:0.9em">Número de celular</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-telephone-fill me-1"></i>+57</span>
                                    <input type="text" class="text-white form-control input-perfil"
                                        style="font-size: 0.9em;"
                                        id="celular_usuario" name="celular_usuario"
                                        value="<?php echo htmlspecialchars($usuario['celular_usuario']); ?>">
                                </div>
                            </div>

                            <div class="col-12 col-md-6 p-1 px-2 my-2">
                                <label for="rol_usuario" class="form-label text-white" style="font-size:0.9em">Rol / Cargo</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                                    <input type="text" class="text-white form-control input-perfil"
                                        style="font-size: 0.9em;"
                                        id="rol_usuario" name="rol_usuario"
                                        value="<?php echo htmlspecialchars($usuario['rol_usuario']); ?>" readonly>
                                </div>
                            </div>

                        </div>
                        <button type="submit" class="btn my-3 mx-5 text-white" style="outline:1px solid white; cursor:pointer;">
                            <i class="bi bi-person-up me-2"></i> Actualizar datos
                        </button>
                </div>

                <div class="col-12 col-lg-3 d-flex flex-column aling-items-center justify-content-center">
                    <div class="perfil-content p-4">
                        <div class="my-4">
                            <h6><?php echo $_SESSION['nombre_usuario'] . " " . $_SESSION['apellido_usuario']; ?></h6>
                            <small><b>Rol: </b><?php echo htmlspecialchars($usuario['rol_usuario']); ?></small>
                        </div>

                        <div class="foto-container">
                            <img src="<?php echo $foto_usuario_perfil; ?>"
                                alt="imagen del perfil"
                                class="rounded-circle foto-perfil">
                            <div class="foto-overlay d-flex flex-column" onclick="document.getElementById('input-foto').click()">
                                <i class="bi bi-camera" style="font-size:1.8em"></i>
                                <span>Cambiar foto</span>
                            </div>
                        </div>
                        <input type="file" id="input-foto" name="foto_user_file" style="display:none" accept="image/*">
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            var x = document.getElementById("password_cons");
            var icon = document.getElementById("toggleIcon");
            if (x.type === "password") {
                x.type = "text";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            } else {
                x.type = "password";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            }
        }
    </script>