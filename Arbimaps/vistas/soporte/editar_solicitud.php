<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 2) . '/config/permisos.php';
require_once dirname(__DIR__, 3) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods(['GET', 'POST']);
neiva_require_permission('soporte.ticket', $PERMISOS);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    neiva_require_csrf('global');
}

$id_usuario_sesion = $_SESSION['id_usuario'] ?? null;
if (!$id_usuario_sesion) {
    echo '<div class="container mt-4">';
    echo '<div class="alert alert-warning">Debe iniciar sesión para acceder al chat.</div>';
    echo '</div>';
    return;
}

// funcion para obtener el codigo de ticket para identificar 
$codigo_error = isset($_GET['codigo']) ? trim($_GET['codigo']) : null;
if (!$codigo_error) {
    echo '<div class="container mt-4">';
    echo '<div class="alert alert-warning">Código de error no especificado.</div>';
    echo '</div>';
    return;
}

$chatSoporteUrl = neiva_app_url('Arbimaps/index.php?page=soporte/chat_soporte&codigo=' . urlencode((string) $codigo_error));
$baseSoporteUrl = neiva_app_url('Arbimaps/index.php?page=soporte/base');
$servicioClienteUrl = neiva_app_url('Arbimaps/index.php?page=soporte/servicio_cliente');
$chatMensajesUrl = neiva_app_url('Arbimaps/vistas/soporte/acciones/chat_soporte_mensajes.php?codigo=' . urlencode((string) $codigo_error));
$assetsFotosBaseFs = neiva_join_paths(neiva_app_root(), 'Arbimaps', 'assets', 'fotos_usuarios');
$assetsFotosBaseUrl = neiva_app_url('Arbimaps/assets/fotos_usuarios');

// consulta los datos del usuario para identificar si es de soporte o no
$rol_usuario = '';
$correo_usuario = '';
if (isset($mysqli) && $mysqli instanceof mysqli) {
    $sqlUsuario = "SELECT 
                        rol_usuario, 
                        correo_usuario 
                    FROM usuarios_cons 
                    WHERE id_usuario = ?";
    $stmtUsuario = $mysqli->prepare($sqlUsuario);
    if ($stmtUsuario) {
        $stmtUsuario->bind_param('i', $id_usuario_sesion);
        $stmtUsuario->execute();
        $resUsuario = $stmtUsuario->get_result();
        if ($resUsuario && $resUsuario->num_rows > 0) {
            $infoUsuario        = $resUsuario->fetch_assoc();
            $rol_usuario        = $infoUsuario['rol_usuario'] ?? '';
            $correo_usuario     = $infoUsuario['correo_usuario'] ?? '';
        }
        $stmtUsuario->close();
    }
}



// consulta para obtener la solicitud por codigo para mostra el correo, id y si el chat esta bloquedo
$sqlSolicitud = "SELECT 
                    id_usuario,
                    codigo_error, 
                    asunto, 
                    descripcion, 
                    correo_solicitante, 
                    chat_bloqueado,
                    imagen_ruta
                 FROM solicitud_soporte
                 WHERE codigo_error = ?
                 LIMIT 1";
$stmtSolicitud = $mysqli->prepare($sqlSolicitud);
$stmtSolicitud->bind_param('s', $codigo_error);
$stmtSolicitud->execute();
$resSolicitud   = $stmtSolicitud->get_result();
$solicitud      = $resSolicitud->fetch_assoc();
$stmtSolicitud->close();
if (!$solicitud) {
    echo '<div class="container mt-4">';
    echo '<div class="alert alert-info">No se encontró la solicitud con código: ' . htmlspecialchars($codigo_error) . '</div>';
    echo '</div>';
    return;
}

// convertir el nombre de la variable en la bd a una ruta real para poder visualizarla en el front-End
$imgSrc = !empty($solicitud['imagen_ruta'])
    ? neiva_app_url('Arbimaps/vistas/soporte/' . ltrim($solicitud['imagen_ruta'], '/'))
    : null;

// consulta para identificar a que usuario esta asignado el ticket para mostar la foto correspondiente
$id_asignado            = (int)($solicitud['id_usuario'] ?? 0);
$nombre_asignado        = '';
$apellido_asignado      = '';
$foto_asignado          = null;
$rol_asignado           = '';
$correo_asignado        = '';

if ($id_asignado > 0) {
    $sqlAsignado = "SELECT 
                        id_usuario,
                        nombre_usuario,
                        apellido_usuario,
                        foto_user,
                        rol_usuario,
                        correo_usuario
                    FROM usuarios_cons
                    WHERE id_usuario = ?
                    LIMIT 1";

    $stmtAsignado = $mysqli->prepare($sqlAsignado);
    if ($stmtAsignado) {
        $stmtAsignado->bind_param("i", $id_asignado);
        $stmtAsignado->execute();
        $resAsignado = $stmtAsignado->get_result();

        if ($resAsignado && $resAsignado->num_rows > 0) {
            $a = $resAsignado->fetch_assoc();
            $nombre_asignado   = $a['nombre_usuario'] ?? '';
            $apellido_asignado = $a['apellido_usuario'] ?? '';
            $foto_asignado     = $a['foto_user'] ?? null;
            $rol_asignado      = $a['rol_usuario'] ?? '';
            $correo_asignado   = $a['correo_usuario'] ?? '';
        }
        $stmtAsignado->close();
    }
}

// esta funcion define los permisos para ver partes dek chat correpondientes al de soporte y al usuario que solicito la revision
$rol_normalizado    = strtolower(trim($rol_usuario));
$es_soporte         = (strpos($rol_normalizado, 'soporte') !== false);
$es_user_soporte    = ($rol_normalizado === 'soporte');
$tipo_remitente     = $es_soporte ? 'SOPORTE' : 'CLIENTE';

// esta funcion es para identificar en que estado esa la solicitud
$chat_bloqueado = (int)($solicitud['chat_bloqueado'] ?? 0);

// funcion para validar si es cliente mostrar su propio ticket
if ($tipo_remitente === 'CLIENTE' && $correo_usuario !== ($solicitud['correo_solicitante'] ?? '')) {
    echo '<div class="container mt-4">';
    echo '<div class="alert alert-danger">No tiene permiso para ver este chat.</div>';
    echo '</div>';
    return;
}


// esta funcion es el centro de envio de mensajes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion     = isset($_POST['accion']) ? trim($_POST['accion']) : '';
    $es_ajax    = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
    if ($accion === 'vaciar_chat') {
        if (!$es_user_soporte) {
            echo '<div class="container mt-4">';
            echo '<div class="alert alert-danger">No tiene permiso para vaciar este chat.</div>';
            echo '</div>';
            return;
        }
        $sqlVaciar  = "DELETE FROM soporte_chat WHERE codigo_error = ?";
        $stmtVaciar = $mysqli->prepare($sqlVaciar);
        if ($stmtVaciar) {
            $stmtVaciar->bind_param('s', $codigo_error);
            $stmtVaciar->execute();
            $stmtVaciar->close();
        }
        $_SESSION['chat_vaciado_ok'] = 1;
        $url_redireccion = $chatSoporteUrl;
        echo '<script>window.location.href = ' . json_encode($url_redireccion) . ';</script>';
        exit;
    }
    if ($accion === 'bloquear_chat') {
        if (!$es_user_soporte) {
            echo '<div class="container mt-4">';
            echo '<div class="alert alert-danger">No tiene permiso para bloquear el chat.</div>';
            echo '</div>';
            return;
        }
        $sqlBloquear = "UPDATE 
                            solicitud_soporte 
                        SET chat_bloqueado = 1 
                        WHERE codigo_error = ?
                        LIMIT 1";
        $stmtBloquear = $mysqli->prepare($sqlBloquear);
        if ($stmtBloquear) {
            $stmtBloquear->bind_param('s', $codigo_error);
            $stmtBloquear->execute();
            $stmtBloquear->close();
        }
        $_SESSION['chat_bloqueado_ok'] = 1;
        $url_redireccion = $chatSoporteUrl;
        echo '<script>window.location.href = ' . json_encode($url_redireccion) . ';</script>';
        exit;
    }
    if ($accion === 'desbloquear_chat') {
        if (!$es_user_soporte) {
            echo '<div class="container mt-4">';
            echo '<div class="alert alert-danger">No tiene permiso para desbloquear el chat.</div>';
            echo '</div>';
            return;
        }
        $sqlDesbloquear = "UPDATE 
                                solicitud_soporte 
                            SET chat_bloqueado = 0 
                            WHERE codigo_error = ?
                            LIMIT 1";
        $stmtDesbloquear = $mysqli->prepare($sqlDesbloquear);
        if ($stmtDesbloquear) {
            $stmtDesbloquear->bind_param('s', $codigo_error);
            $stmtDesbloquear->execute();
            $stmtDesbloquear->close();
        }
        $_SESSION['chat_desbloqueado_ok'] = 1;
        $url_redireccion = $chatSoporteUrl;
        echo '<script>window.location.href = ' . json_encode($url_redireccion) . ';</script>';
        exit;
    }

    // enviar mensaje
    $mensaje = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';
    if ($chat_bloqueado === 1) {
        if ($es_ajax) {
            http_response_code(423);
            echo 'CHAT_BLOQUEADO';
            exit;
        }
        $_SESSION['chat_bloqueado_msg'] = 1;
        $url_redireccion = $chatSoporteUrl;
        echo '<script>window.location.href = ' . json_encode($url_redireccion) . ';</script>';
        exit;
    }
    if ($mensaje !== '') {
        $sqlInsertChat = "INSERT INTO 
                                soporte_chat
                                (
                                    codigo_error, 
                                    id_usuario, 
                                    mensaje, 
                                    tipo_remitente
                                )
                          VALUES (?, ?, ?, ?)";
        $stmtChat = $mysqli->prepare($sqlInsertChat);
        if ($stmtChat) {
            $stmtChat->bind_param(
                'siss',
                $codigo_error,
                $id_usuario_sesion,
                $mensaje,
                $tipo_remitente
            );
            $stmtChat->execute();
            $stmtChat->close();
        }
    }
    if ($es_ajax) {
        echo 'OK';
        exit;
    }
    $url_redireccion = $chatSoporteUrl;
    echo '<script>window.location.href = ' . json_encode($url_redireccion) . ';</script>';
    exit;
}

// esta consulta y funcion es para cargar los mensajes del chat
$mensajes = [];
$sqlMensajes = "SELECT 
                    c.id_mensaje,
                    c.codigo_error,
                    c.id_usuario,
                    c.mensaje,
                    c.fecha_envio,
                    c.tipo_remitente,
                    u.nombre_usuario,
                    u.apellido_usuario
                FROM soporte_chat c
                INNER JOIN usuarios_cons u ON u.id_usuario = c.id_usuario
                WHERE c.codigo_error = ?
                ORDER BY c.fecha_envio ASC";

$stmtMensajes = $mysqli->prepare($sqlMensajes);
if ($stmtMensajes) {
    $stmtMensajes->bind_param('s', $codigo_error);
    $stmtMensajes->execute();
    $resMensajes    = $stmtMensajes->get_result();
    if ($resMensajes && $resMensajes->num_rows > 0) {
        while ($m = $resMensajes->fetch_assoc()) {
            $mensajes[] = $m;
        }
    }
    $stmtMensajes->close();
}

// consulta y funcion para cargar el solicitante que creo el tiquete para mostrar si el usuario es soporte para la foto
$nombre_solicitante     = '';
$apellido_solicitante   = '';
$foto_solicitante       = null;
$id_solicitante         = null;
$rol_solicitante        = '';

$sqlSolicitante = "SELECT 
                        id_usuario, 
                        nombre_usuario, 
                        apellido_usuario, 
                        foto_user,
                        rol_usuario
                   FROM usuarios_cons
                   WHERE correo_usuario = ?
                   LIMIT 1";

$stmtSolicitante = $mysqli->prepare($sqlSolicitante);
if ($stmtSolicitante) {
    $stmtSolicitante->bind_param("s", $solicitud['correo_solicitante']);
    $stmtSolicitante->execute();
    $resSolicitante = $stmtSolicitante->get_result();

    if ($resSolicitante && $resSolicitante->num_rows > 0) {
        $usr = $resSolicitante->fetch_assoc();
        $id_solicitante       = $usr['id_usuario'];
        $nombre_solicitante   = $usr['nombre_usuario'] ?? '';
        $apellido_solicitante = $usr['apellido_usuario'] ?? '';
        $foto_solicitante     = $usr['foto_user'] ?? null;
        $rol_solicitante      = $usr['rol_usuario'] ?? '';
    }
    $stmtSolicitante->close();
}


// ruta para mostrar foto del solicitante
$mostrar_foto_solicitante   = false;
$foto_solicitante_ruta      = '';


//funcion para mostrar la foto de perfil para el usuario que solicito la revision
if (!empty($id_solicitante) && !empty($foto_solicitante)) {
    $ruta_foto_fs = neiva_join_paths($assetsFotosBaseFs, (string) $id_solicitante, (string) $foto_solicitante);
    if (file_exists($ruta_foto_fs)) {
        $foto_solicitante_ruta = $assetsFotosBaseUrl . '/' . rawurlencode((string) $id_solicitante) . '/' . rawurlencode((string) $foto_solicitante);
        $mostrar_foto_solicitante = true;
    }
}

// funcion para mostra la foto en el panel segun el rol
$panel_titulo   = $es_soporte ? 'Detalle del solicitante:' : 'Usuario Soporte:';

$panel_nombre   = '';
$panel_apellido = '';
$panel_correo   = '';
$panel_rol      = '';
$panel_foto     = null;
$panel_id       = null;
if ($es_soporte) {
    $panel_nombre   = $nombre_solicitante;
    $panel_apellido = $apellido_solicitante;
    $panel_correo   = $solicitud['correo_solicitante'] ?? '';
    $panel_rol      = $rol_solicitante;
    $panel_foto     = $foto_solicitante;
    $panel_id       = $id_solicitante;
} else {
    $panel_nombre   = $nombre_asignado;
    $panel_apellido = $apellido_asignado;
    $panel_correo   = $correo_asignado;
    $panel_rol      = $rol_asignado;
    $panel_foto     = $foto_asignado;
    $panel_id       = $id_asignado;
}

$panel_mostrar_foto = false;
$panel_foto_ruta    = '';

if (!empty($panel_id) && !empty($panel_foto)) {
    $ruta_panel_fs = neiva_join_paths($assetsFotosBaseFs, (string) $panel_id, (string) $panel_foto);
    if (file_exists($ruta_panel_fs)) {
        $panel_foto_ruta = $assetsFotosBaseUrl . '/' . rawurlencode((string) $panel_id) . '/' . rawurlencode((string) $panel_foto);
        $panel_mostrar_foto = true;
    }
}


?>
<style>
    :root {
        --color-primario: #022F55;
        --color-borde-suave: #e5e7eb;
        --color-fondo-chat: #f9fafb;
    }

    .chat-mensajes {
        flex: 1;
        /* ocupa el espacio disponible */
        padding-right: 0.5rem;
        background-color: var(--color-fondo-chat);
        border-radius: 12px;
        border: 1px solid var(--color-borde-suave);
        padding: 0.75rem;
        min-height: 75%;
        max-height: 400px;
        overflow-y: auto;
    }

    .mensaje-burbuja {
        max-width: 80%;
        padding: 0.5rem 0.75rem;
        border-radius: 12px;
        margin-bottom: 0.4rem;
        font-size: 0.9rem;
    }

    .mensaje-propio {
        margin-left: auto;
        background-color: #022F55;
        color: #ffffff;
        border-bottom-right-radius: 2px;
    }

    .mensaje-otro {
        margin-right: auto;
        background-color: #ffffff;
        color: #111827;
        border-bottom-left-radius: 2px;
        border: 1px solid #e5e7eb;
    }

    .mensaje-meta {
        font-size: 0.7rem;
        color: #9ca3af;
        margin-top: 0.15rem;
    }

    .chat-input-area {
        margin-top: 0.75rem;
        border-radius: 12px;
        border: 1px solid var(--color-borde-suave);
        background-color: #ffffff;
        padding: 0.5rem 0.75rem;
    }

    .chat-textarea {
        border: none;
        resize: none;
        width: 100%;
        min-height: 60px;
        max-height: 140px;
        font-size: 0.9rem;
        outline: none;
    }

    .btn-enviar-chat {
        border-radius: 999px;
        border: 1px solid var(--color-primario);
        background-color: var(--color-primario);
        color: #ffffff;
        font-size: 0.85rem;
        padding: 0.35rem 1rem;
    }

    .btn-enviar-chat:hover {
        background-color: #011a33;
        color: #ffffff;
    }

    .boton-volver-chat {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.3rem 1rem;
        border-radius: 999px;
        color: var(--color-primario);
        border: 1px solid var(--color-primario);
        background-color: #ffffff;
        font-weight: 500;
        font-size: 0.85rem;
        text-decoration: none;
        transition: all 0.2s ease-in-out;
    }

    .boton-volver-chat:hover {
        background-color: var(--color-primario);
        color: #ffffff;
        text-decoration: none;
    }

    .btn-vaciar-chat {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.3rem 1rem;
        border-radius: 999px;
        color: #dc2626;
        border: 1px solid #dc2626;
        background-color: #ffffff;
        font-weight: 500;
        font-size: 0.85rem;
        text-decoration: none;
        transition: all 0.2s ease-in-out;
    }

    .btn-vaciar-chat:hover {
        background-color: #dc2626;
        color: #ffffff;
    }

    .btn-bloqueo-chat {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.3rem 1rem;
        border-radius: 999px;
        color: #111827;
        border: 1px solid #111827;
        background-color: #ffffff;
        font-weight: 500;
        font-size: 0.85rem;
        text-decoration: none;
        transition: all 0.2s ease-in-out;
    }

    .btn-bloqueo-chat:hover {
        background-color: #111827;
        color: #ffffff;
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

    .chat-layout {

        display: flex;
        flex-direction: column;
        overflow: hidden;
        /* clave para evitar desbordes */
    }

    .chat-header {
        flex-shrink: 0;
        background: linear-gradient(159deg, rgba(0, 47, 85, 1) 52%, rgba(0, 212, 255, 1) 100%);
        color: white;

    }

    .ticket-panel {
        height: 100%;
        overflow-y: auto;
        /* 🔥 scroll solo aquí */
        overflow-x: hidden;
    }

    .chat-panel {
        height: 100%;
        overflow: hidden;
    }

    .perfil-wrapper {
        position: relative;
        width: 130px;
        height: 130px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .perfil-avatar {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #022F55;
        padding: 2px;
        background-color: #6b7280;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    .perfil-avatar-placeholder {
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 2.4rem;
        color: white;
    }

    .perfil-rol {
        position: absolute;
        bottom: 6px;
        background-color: #dcfce7;
        color: #166534;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.75rem;
        border-radius: 999px;
        border: 1px solid #bbf7d0;
        white-space: nowrap;
    }
</style>

<div class="container-fluid card shadow rounded-4 border chat-layout h-100 ">
    <div class="row chat-header " style="height: 15%; z-index:99">
        <div class="col-12 px-3 d-flex  align-items-center border-bottom">
            <div class="border rounded-5 px-2 bg-white me-3"> <i class="bi bi-chat-quote fs-4" style="color: #022F55;"></i></div>
            <div>
                <span class="titulo-seccion">Chat de soporte</span>
                <h5 class="mb-1 d-flex gap-2 align-items-center ">
                    <strong><?php echo htmlspecialchars($solicitud['codigo_error'], ENT_QUOTES, 'UTF-8'); ?> </strong>
                    <?php if ($chat_bloqueado === 1): ?>
                        <span class="badge bg-secondary ms-2" style="font-size:0.7rem;">
                            <i class="bi bi-lock me-1"></i> Bloqueado
                        </span>
                    <?php endif; ?>
                </h5>
                <p class="mb-0 small" style="color: #b5b9beff;">
                    Módulo para intercambio de información acerca del ticket generado.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <?php if ($es_user_soporte): ?>
                    <form method="POST" id="formBloqueoChat" class="m-0">
                        <?php echo neiva_csrf_input('global'); ?>
                        <input type="hidden" name="accion" value="<?php echo ($chat_bloqueado === 1) ? 'desbloquear_chat' : 'bloquear_chat'; ?>">
                        <button type="button" id="btnBloqueoChat" class="btn-bloqueo-chat p-2 px-3">
                            <?php if ($chat_bloqueado === 1): ?>
                                <i class="bi bi-unlock me-1"></i> Desbloquear chat
                            <?php else: ?>
                                <i class="bi bi-lock me-1"></i> Bloquear chat
                            <?php endif; ?>
                        </button>
                    </form>
                    <form method="POST" id="formVaciarChat" class="m-0">
                        <?php echo neiva_csrf_input('global'); ?>
                        <input type="hidden" name="accion" value="vaciar_chat">
                        <button type="button" id="btnVaciarChat" class="btn-vaciar-chat p-2 px-3">
                            <i class="bi bi-trash3 me-1"></i> Vaciar chat
                        </button>
                    </form>
                <?php endif; ?>
                <a href="<?php
                            if ($es_soporte) {
                                echo htmlspecialchars($baseSoporteUrl, ENT_QUOTES, 'UTF-8');
                            } else {
                                echo htmlspecialchars($servicioClienteUrl, ENT_QUOTES, 'UTF-8');
                            }
                            ?>"
                    class="boton-volver-chat aling-items-center p-2 px-3">
                    <i class="bi bi-chevron-left me-1 aling-content-center"></i> Volver
                </a>
            </div>
        </div>
    </div>
    <div class="row flex-fill overflow-hidden">

        <div class="col-8 p-3 chat-panel d-flex flex-column">
            <div class="chat-mensajes" id="chatMensajes">
                <?php if (empty($mensajes)): ?>
                    <p class="text-muted mb-0" style="font-size:0.9rem;">
                        Aún no hay mensajes en este chat. Escribe el primero.
                    </p>
                <?php else: ?>
                    <?php foreach ($mensajes as $m): ?>
                        <?php
                        $esPropio = ((int)$m['id_usuario'] === (int)$id_usuario_sesion);
                        $claseBurbuja = $esPropio ? 'mensaje-propio' : 'mensaje-otro';
                        $nombreRemitente = trim(($m['nombre_usuario'] ?? '') . ' ' . ($m['apellido_usuario'] ?? ''));
                        ?>
                        <div class="d-flex <?php echo $esPropio ? 'justify-content-end' : 'justify-content-start'; ?>">
                            <div class="mensaje-burbuja <?php echo $claseBurbuja; ?>">
                                <div style="font-size:1rem; font-weight:600; margin-bottom:0.15rem;">
                                    <?php echo htmlspecialchars($nombreRemitente ?: $m['tipo_remitente'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                                <div>
                                    <?php echo nl2br(htmlspecialchars($m['mensaje'], ENT_QUOTES, 'UTF-8')); ?>
                                </div>
                                <div class="mensaje-meta">
                                    <?php echo htmlspecialchars($m['fecha_envio'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <form method="POST" class="chat-input-area mt-2" id="formEnviarMensaje">
                <?php echo neiva_csrf_input('global'); ?>
                <textarea
                    name="mensaje"
                    class="chat-textarea"
                    placeholder="<?php echo ($chat_bloqueado === 1) ? 'Chat bloqueado por soporte.' : 'Escribe un mensaje para soporte...'; ?>"
                    <?php echo ($chat_bloqueado === 1) ? 'disabled' : 'required'; ?>></textarea>
                <div class="d-flex justify-content-end mt-2">
                    <button type="submit" class="btn-enviar-chat" <?php echo ($chat_bloqueado === 1) ? 'disabled' : ''; ?>>
                        <i class="bi bi-send-fill me-2"></i> Enviar
                    </button>
                </div>
            </form>
            <?php if ($chat_bloqueado === 1): ?>
                <div class="alert alert-secondary mt-2 mb-0" style="font-size:0.9rem;">
                    <i class="bi bi-lock me-1"></i> Este chat está bloqueado. No se pueden enviar mensajes.
                </div>
            <?php endif; ?>
        </div>

        <div class="col-4 p-2 ticket-panel ">
            <div class="card  shadow rounded-4 p-3 border " style="overflow-y:auto; ">
                <div class="d-flex flex-column justify-content-center align-items-center mb-2">
                    <h5 class="titulo-seccion mb-2"><?php echo htmlspecialchars($panel_titulo); ?></h5>

                    <div class="perfil-wrapper mb-2">
                        <?php if ($panel_mostrar_foto): ?>
                            <img src="<?= htmlspecialchars($panel_foto_ruta) ?>" class="perfil-avatar">
                        <?php else: ?>
                            <div class="perfil-avatar perfil-avatar-placeholder">
                                <i class="bi bi-person"></i>
                            </div>
                        <?php endif; ?>

                        <span class="perfil-rol">
                            Rol: <?= htmlspecialchars($panel_rol) ?>
                        </span>
                    </div>

                    <h5 class="fw-bold mb-0 text-center" style="font-size: 1em;">
                        <?= htmlspecialchars(trim($panel_nombre . ' ' . $panel_apellido)) ?>
                    </h5>

                    <p class="text-muted small mb-3" style="font-size: 0.8em;">
                        <?= htmlspecialchars($panel_correo) ?>
                    </p>

                    <!-- <span class="border rounded-5 p-1 text-center w-50"
                        style="background-color: #dcfce7; color: #166534; font-size:0.8em">
                        Rol: <?= htmlspecialchars($panel_rol) ?>
                    </span> -->
                </div>
                <hr class="my-1">
                <h5 class="titulo-seccion text-center my-3">Detalle del ticket:</h5>
                <p class="mb-2 text-start" style="font-size:0.9rem;">
                    <strong>Asunto:</strong><br>
                    <?php echo htmlspecialchars($solicitud['asunto'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                </p>
                <p class="mb-0 text-start" style="font-size:0.9rem;">
                    <strong>Descripción:</strong><br>
                    <?php echo nl2br(htmlspecialchars($solicitud['descripcion'] ?? '', ENT_QUOTES, 'UTF-8')); ?>
                </p>
                <?php if ($imgSrc): ?>
                    <div class="card my-3 shadow-sm p-2 card-imagen-reporte justify-content-center align-items-center" style="width: 90%; margin-left:5%">
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if (!empty($_SESSION['chat_vaciado_ok'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Chat vaciado',
                    timer: 1400,
                    showConfirmButton: false
                });
            }
        });
    </script>
<?php unset($_SESSION['chat_vaciado_ok']);
endif; ?>

<?php if (!empty($_SESSION['chat_bloqueado_ok'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Chat bloqueado',
                    timer: 1400,
                    showConfirmButton: false
                });
            }
        });
    </script>
<?php unset($_SESSION['chat_bloqueado_ok']);
endif; ?>

<?php if (!empty($_SESSION['chat_desbloqueado_ok'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Chat desbloqueado',
                    timer: 1400,
                    showConfirmButton: false
                });
            }
        });
    </script>
<?php unset($_SESSION['chat_desbloqueado_ok']);
endif; ?>

<?php if (!empty($_SESSION['chat_bloqueado_msg'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'Chat bloqueado',
                    text: 'No se pueden enviar mensajes en este ticket.',
                    timer: 1800,
                    showConfirmButton: false
                });
            }
        });
    </script>
<?php unset($_SESSION['chat_bloqueado_msg']);
endif; ?>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        var contenedor = document.getElementById('chatMensajes');
        if (contenedor) {
            contenedor.scrollTop = contenedor.scrollHeight;
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('formEnviarMensaje');
        if (!form) return;
        var textarea = form.querySelector('.chat-textarea');
        var btnEnviar = form.querySelector('.btn-enviar-chat');

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!textarea || textarea.disabled) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Chat bloqueado',
                        text: 'No se pueden enviar mensajes en este ticket.'
                    });
                }
                return;
            }
            var mensaje = textarea.value.trim();
            if (mensaje === '') return;
            if (btnEnviar) btnEnviar.disabled = true;

            var formData = new FormData(form);
            fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(function(response) {
                    if (response.status === 423) {
                        if (btnEnviar) btnEnviar.disabled = false;
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'info',
                                title: 'Chat bloqueado',
                                text: 'No se pueden enviar mensajes en este ticket.'
                            });
                        }
                        return;
                    }
                    textarea.value = '';
                    textarea.focus();

                    if (btnEnviar) btnEnviar.disabled = false;
                    if (typeof cargarMensajes === 'function') {
                        cargarMensajes(true);
                    } else {
                        var contenedor = document.getElementById('chatMensajes');
                        if (contenedor) contenedor.scrollTop = contenedor.scrollHeight;
                    }
                })
                .catch(function(error) {
                    console.error('Error enviando mensaje:', error);
                    if (btnEnviar) btnEnviar.disabled = false;
                });
        });
        textarea.addEventListener('keydown', function(e) {
            if (textarea.disabled) return;
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                form.dispatchEvent(new Event('submit', {
                    cancelable: true
                }));
            }
        });
    });
</script>
<script>
    function cargarMensajes(autoScroll) {
        var contenedor = document.getElementById('chatMensajes');
        if (!contenedor) return;

        var xhr = new XMLHttpRequest();
        xhr.open(
            'GET',
            <?php echo json_encode($chatMensajesUrl); ?>,
            true
        );
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var estabaAbajo = (contenedor.scrollTop + contenedor.clientHeight) >= (contenedor.scrollHeight - 10);

                contenedor.innerHTML = xhr.responseText;
                if (autoScroll || estabaAbajo) {
                    contenedor.scrollTop = contenedor.scrollHeight;
                }
            }
        };
        xhr.send();
    }
    document.addEventListener('DOMContentLoaded', function() {
        cargarMensajes(true);
        setInterval(function() {
            cargarMensajes(false);
        }, 1000);
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var btn = document.getElementById('btnVaciarChat');
        var form = document.getElementById('formVaciarChat');
        if (!btn || !form) return;

        btn.addEventListener('click', function() {
            if (typeof Swal === 'undefined') {
                var ok = confirm('¿Seguro que desea vaciar el chat de este ticket? Esta acción no se puede deshacer.');
                if (ok) form.submit();
                return;
            }
            Swal.fire({
                title: '¿Vaciar chat?',
                text: 'Se eliminarán todos los mensajes de este ticket. Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, vaciar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                confirmButtonColor: '#dc2626'
            }).then(function(result) {
                if (result.isConfirmed) form.submit();
            });
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var btn = document.getElementById('btnBloqueoChat');
        var form = document.getElementById('formBloqueoChat');
        if (!btn || !form) return;

        btn.addEventListener('click', function() {
            var accion = form.querySelector('input[name="accion"]').value;

            var titulo = (accion === 'bloquear_chat') ? '¿Bloquear chat?' : '¿Desbloquear chat?';
            var texto = (accion === 'bloquear_chat') ?
                'Al bloquear, nadie podrá enviar mensajes en este ticket.' :
                'Al desbloquear, se podrá enviar mensajes nuevamente.';

            if (typeof Swal === 'undefined') {
                var ok = confirm(titulo + '\n' + texto);
                if (ok) form.submit();
                return;
            }
            Swal.fire({
                title: titulo,
                text: texto,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then(function(result) {
                if (result.isConfirmed) form.submit();
            });
        });
    });
</script>
<?php if (isset($_GET['open'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ta = document.querySelector('.chat-textarea');
            if (ta) {
                ta.focus();
                ta.scrollIntoView({
                    behavior: 'smooth',
                    block: 'end'
                });
            }
            if (typeof cargarMensajes === 'function') cargarMensajes(true);
        });
    </script>
<?php endif; ?>
