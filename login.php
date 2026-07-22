<?php
require_once __DIR__ . '/Arbimaps/includes/bootstrap.php';
neiva_bootstrap();
require __DIR__ . '/conexion.php';

if (neiva_is_authenticated()) {
    header('Location: ' . neiva_dashboard_url(), true, 302);
    exit;
}

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim((string) ($_POST['usuario'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!neiva_validate_csrf_token($_POST['csrf_token'] ?? null, 'login')) {
        $mensaje = 'La sesión del formulario expiró. Intenta nuevamente.';
    } elseif ($usuario === '' || $password === '') {
        $mensaje = 'Debe ingresar usuario y contraseña.';
    } else {

    $stmt = $mysqli->prepare("SELECT 
                                id_usuario, 
                                password_cons, 
                                nombre_usuario, 
                                apellido_usuario, 
                                rol_usuario,
                                rol_usuario_dos,
                                cedula_usuario,
                                foto_user,
                                debe_cambiar_password
                            FROM usuarios_cons 
                            WHERE usuario_cons = ?");
    if ($stmt) {
        $stmt->bind_param('s', $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado && $resultado->num_rows > 0) {
            $row = $resultado->fetch_assoc();
            $hash = (string) ($row['password_cons'] ?? '');

            if ($hash !== '' && password_verify($password, $hash)) {
                if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    if ($newHash !== false) {
                        $stmtRehash = $mysqli->prepare('UPDATE usuarios_cons SET password_cons = ? WHERE id_usuario = ?');
                        if ($stmtRehash) {
                            $stmtRehash->bind_param('ss', $newHash, $row['id_usuario']);
                            if (!$stmtRehash->execute()) {
                                error_log('Login rehash execute failed for user ' . $row['id_usuario'] . ': ' . $stmtRehash->error);
                            }
                            $stmtRehash->close();
                        } else {
                            error_log('Login rehash prepare failed for user ' . $row['id_usuario'] . ': ' . $mysqli->error);
                        }
                    }
                }

                session_regenerate_id(true);
                $_SESSION['id_usuario'] = $row['id_usuario'];
                $_SESSION['nombre_usuario'] = $row['nombre_usuario'];
                $_SESSION['apellido_usuario'] = $row['apellido_usuario'];
                $_SESSION['rol_usuario'] = $row['rol_usuario'];
                $_SESSION['rol_usuario_dos'] = $row['rol_usuario_dos'];
                $_SESSION['cedula_usuario'] = $row['cedula_usuario'];
                $_SESSION['foto_user'] = !empty($row['foto_user']) ? $row['foto_user'] : '';
                $_SESSION['debe_cambiar_password'] = (int) ($row['debe_cambiar_password'] ?? 0);
                header('Location: ' . neiva_dashboard_url(), true, 302);
                exit();
            } else {
                $mensaje = "Contraseña incorrecta";
            }
        } else {
            $mensaje = "Usuario no encontrado";
        }

        $stmt->close();
    } else {
        $mensaje = "Error en la consulta de autenticación";
    }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesion | ARBIMapps</title>
    <link rel="icon" href="imagen/L_NW.webp" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Fleur+De+Leah&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lavishly+Yours&family=Libre+Baskerville:ital,wght@0,400..700;1,400..700&family=Mea+Culpa&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto+Serif:ital,opsz,wght@0,8..144,100..900;1,8..144,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">

    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_CO">
    <meta property="og:site_name" content="Arbitrium S.A.S">
    <meta property="og:title" content="Arbitrium S.A.S | Gestión predial y Catastro Multipropósito.">
    <meta property="og:description"
        content="Soluciones integrales en gestión predial, catastro multipropósito y tecnología en Colombia.">
    <meta property="og:url" content="https://www.arbitrium.com.co/">
    
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

</head>

<body>

    <div class="container-fluid hero">
        <header
            class="navbar navbar-expand-sm border-bottom d-flex justify-content-center p-0 gap-3 align-content-center w-100 login-header"
            data-bs-theme="dark">
            <a href="bienvenida.php"><img src="imagen/Neiva.webp" style="width: 90px;"></a>
            <a href="https://www.arbitrium.com.co/" target="_blank"><img src="imagen/logo.png" style="width: 90px;" class="border-start"></a>
        </header>

        <div class="container-fluid px-0 d-flex align-items-center justify-content-center login-main">
            <section
                class="container d-flex flex-column flex-md-row justify-content-center p-4 card-login rounded-5 login-card" style="animation: entrarDerecha 1s ease-out forwards;">

                <!-- <img src="imagen/mono.png" class="navidad-mono" alt="mono de navidad"> -->
                <!-- Panel de bienvenida -->
                <div class="col-12 col-md-6 px-3 ps-0 login-panel-col">
                    <aside
                        class=" order-2 order-md-1 shadow panel-az d-flex flex-column align-items-center text-end justify-content-center h-100 p-3 rounded-4">
                        <!-- <h1 class="fw-bold "> &copy; ARBI<span class="text-outline">Mapps</span>Neiva</h1> -->
                        <div class="d-flex flex-column  mt-auto w-100">
                            <h1 class="fw-bold mb-0 "> &copy; ARBI<span class="text-outline">Mapps</span></h1>
                            <small class="w-100  text-end">Neiva</small>
                        </div>
                        <!-- <p class="fw-light fs-6">Tu aplicativo catastral.</p> -->
                        <!-- <img src="imagen/arbiprop.png" alt="Logo Arbitrium" style="width: 200px;"
                            class="d-none d-md-block my-2 "> -->
                        <!-- <small class="text-center mx-3 fw-light my-3">
                            ¿No cuentas con un usuario? Comunícate con el líder de proyecto para registrarse.
                        </small>
                        <a href="Registro.html" class="btn_registrar p-2 fw-bold">Registrate</a> -->
                    </aside>
                </div>


                <!-- Formulario de login -->
                <section
                    class=" order-1 order-md-2 d-flex flex-column align-items-center justify-content-center col-12 col-md-6 p-4 py-5 text-center texto_login" style="position:relative; z-index: 999;">

                    <h1 class=" mb-2" style="font-weight: 700 !important;">¡Bienvenido!</h1>
                    <p style="font-size: 0.95rem;"> Ingresa tus credenciales para iniciar sesión</p>

                    <?php if (!empty($mensaje)): ?>
                        <div class="alert alert-danger w-100 text-center" role="alert">
                            <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="POST" class="w-100 px-5 login-form">
                        <?= neiva_csrf_input('login') ?>
                        <div class="d-flex flex-column gap-1 mt-3">
                            <label for="usuario" class="text-start fw-bolder ms-2">Usuario</label>
                            <input class="mb-3 input-login px-4" type="text" id="usuario" name="usuario"
                                placeholder="Ingrese su usuario" required>
                        </div>

                        <div class="d-flex flex-column gap-1 mt-3 text-start">
                            <label for="password" class="fw-bold ms-2">Contraseña</label>
                            <div class="password-field mb-1">
                                <input class="input-login input-password px-4" type="password" id="password" name="password"
                                    placeholder="Ingrese su contraseña" required>
                                <button type="button" class="toggle-password-btn" id="togglePassword"
                                    aria-label="Ver contraseña" title="Ver contraseña">
                                    <i class="bi bi-eye" style="color: #888686;" id="iconoOjo"></i>
                                </button>
                            </div>
                            <!-- <div class="options  d-flex align-items-center justify-content-start p-2 rounded-3 gap-2">
                                <input type="checkbox" id="remember" name="remember">
                                <label for="remember" style="font-size: 0.8em;">Recordar contraseña</label>
                            </div> -->
                        </div>

                        <hr class="my-3">


                        <div
                            class="actions mt-4 text-center d-flex flex-column flex-md-row justify-content-center gap-3">
                            <button type="submit" class="boton_login fw-bold">Iniciar sesión</button>
                        </div>


                        <!-- se elimina esta sección temporalmente -->
                        <!-- <div class="mt-3">
                            <a href="/recuperar" class="text-decoration-none" style="color: #0F5699;">¿Olvidaste tu
                                contraseña?</a>
                        </div> -->
                    </form>
                </section>

            </section>
        </div>

    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const iconoOjo = document.getElementById('iconoOjo');

        togglePassword.addEventListener('click', () => {
            // Cambiar tipo de input
            const mostrarPassword = passwordInput.getAttribute('type') === 'password';
            const tipo = mostrarPassword ? 'text' : 'password';
            passwordInput.setAttribute('type', tipo);
            togglePassword.setAttribute('aria-label', mostrarPassword ? 'Ocultar contraseña' : 'Ver contraseña');
            togglePassword.setAttribute('title', mostrarPassword ? 'Ocultar contraseña' : 'Ver contraseña');

            // Cambiar icono
            iconoOjo.classList.toggle('bi-eye');
            iconoOjo.classList.toggle('bi-eye-slash');
        });

        document.addEventListener("DOMContentLoaded", () => {
            const panelAz = document.querySelector(".panel-az");

            if (!panelAz || window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
                return;
            }

            const maxMove = 18;
            let targetX = 0;
            let targetY = 0;
            let currentX = 0;
            let currentY = 0;
            let frame = null;

            function animatePanelParallax() {
                currentX += (targetX - currentX) * 0.12;
                currentY += (targetY - currentY) * 0.12;

                panelAz.style.setProperty("--panel-parallax-x", `${currentX.toFixed(2)}px`);
                panelAz.style.setProperty("--panel-parallax-y", `${currentY.toFixed(2)}px`);

                if (Math.abs(targetX - currentX) > 0.1 || Math.abs(targetY - currentY) > 0.1) {
                    frame = requestAnimationFrame(animatePanelParallax);
                } else {
                    frame = null;
                }
            }

            function updatePanelParallax(clientX, clientY) {
                const rect = panelAz.getBoundingClientRect();
                const x = (clientX - rect.left) / rect.width - 0.5;
                const y = (clientY - rect.top) / rect.height - 0.5;

                targetX = x * maxMove * -1;
                targetY = y * maxMove * -1;

                if (!frame) {
                    frame = requestAnimationFrame(animatePanelParallax);
                }
            }

            panelAz.addEventListener("mousemove", (event) => {
                updatePanelParallax(event.clientX, event.clientY);
            });

            panelAz.addEventListener("mouseleave", () => {
                targetX = 0;
                targetY = 0;

                if (!frame) {
                    frame = requestAnimationFrame(animatePanelParallax);
                }
            });
        });

        document.addEventListener("DOMContentLoaded", () => {

            const snowContainer = document.getElementById('snow');

            if (!snowContainer) {
                return;
            }

            function createSnowflake() {
                const snowflake = document.createElement('div');
                snowflake.classList.add('snowflake');

                const inner = document.createElement('span');
                inner.textContent = '❄';
                snowflake.appendChild(inner);

                // Posición horizontal
                snowflake.style.left = Math.random() * window.innerWidth + 'px';

                // Tamaño aleatorio
                const size = Math.random() * 18 + 18;
                inner.style.fontSize = size + 'px';

                // Duración de caída
                const fallDuration = Math.random() * 5 + 5;
                snowflake.style.animation = `fall ${fallDuration}s linear infinite`;

                // Duración del giro
                const spinDuration = Math.random() * 3 + 2;
                inner.style.animation = `spin ${spinDuration}s linear infinite`;

                snowContainer.appendChild(snowflake);

                setTimeout(() => snowflake.remove(), fallDuration * 1000);
            }


            setInterval(createSnowflake, 200);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
        crossorigin="anonymous"></script>
</body>

</html>
