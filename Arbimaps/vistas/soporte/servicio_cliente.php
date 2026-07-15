<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 2) . '/config/permisos.php';
require_once dirname(__DIR__, 3) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('GET');
neiva_require_permission('soporte.ticket', $PERMISOS);

$id_usuario_sesion = $_SESSION['id_usuario'] ?? null;
$chatSoportePageBase = neiva_app_url('Arbimaps/index.php?page=soporte/chat_soporte');
$chatBloqueosUrl = neiva_app_url('Arbimaps/vistas/soporte/chat_bloqueos.php');
$chatNotificacionesUrl = neiva_app_url('Arbimaps/vistas/soporte/chat_notificaciones.php');
$imprimirSolicitudBase = neiva_app_url('Arbimaps/index.php?page=soporte/imprimir_solicitud');

$nombre_usuario   = '';
$apellido_usuario = '';
$correo_usuario   = '';
$celular_usuario  = '';

$total_solucionados = 0;

if (!$id_usuario_sesion) {
    echo '<div class="container mt-4">';
    echo '<div class="alert alert-warning">Debe iniciar sesión para ver sus solicitudes.</div>';
    echo '</div>';
    return;
}

if (isset($mysqli) && $mysqli instanceof mysqli) {
    $usuario_cons = "SELECT 
                        nombre_usuario,
                        apellido_usuario,
                        correo_usuario,
                        celular_usuario
                    FROM usuarios_cons
                    WHERE id_usuario = ?";

    $obtener_datos = $mysqli->prepare($usuario_cons);

    if ($obtener_datos) {
        $obtener_datos->bind_param('i', $id_usuario_sesion);
        $obtener_datos->execute();
        $mostrar_datos = $obtener_datos->get_result();

        if ($mostrar_datos && $mostrar_datos->num_rows > 0) {
            $datos_u = $mostrar_datos->fetch_assoc();
            $nombre_usuario   = $datos_u['nombre_usuario']   ?? '';
            $apellido_usuario = $datos_u['apellido_usuario'] ?? '';
            $correo_usuario   = $datos_u['correo_usuario']   ?? '';
            $celular_usuario  = $datos_u['celular_usuario']  ?? '';
        }

        $obtener_datos->close();
    } else {
        echo '<div class="container mt-4">';
        echo '<div class="alert alert-danger">Error al preparar la consulta de usuario.</div>';
        echo '</div>';
        return;
    }
} else {
    echo '<div class="container mt-4">';
    echo '<div class="alert alert-danger">Error de conexión a la base de datos.</div>';
    echo '</div>';
    return;
}

$solicitudes = [];
if (!empty($correo_usuario)) {
    $solicitud_soporte = "SELECT 
                            codigo_error,
                            asunto,
                            descripcion,
                            prioridad,
                            tipo_error,
                            estado_error,
                            fecha_hora_creacion,
                            chat_bloqueado
                        FROM solicitud_soporte
                        WHERE correo_solicitante = ?
                        ORDER BY fecha_hora_creacion DESC";

    $obtener_datos_solicitudes = $mysqli->prepare($solicitud_soporte);

    if ($obtener_datos_solicitudes) {
        $obtener_datos_solicitudes->bind_param('s', $correo_usuario);
        $obtener_datos_solicitudes->execute();
        $mostrar_datos_usuarios = $obtener_datos_solicitudes->get_result();

        if ($mostrar_datos_usuarios && $mostrar_datos_usuarios->num_rows > 0) {
            while ($row = $mostrar_datos_usuarios->fetch_assoc()) {
                $solicitudes[] = $row;

                $estadoTmp = strtoupper($row['estado_error'] ?? '');
                if ($estadoTmp === 'SOLUCIONADO') {
                    $total_solucionados++;
                }
            }
        }

        $obtener_datos_solicitudes->close();
    }
}

$total = count($solicitudes);
$pendientes = 0;
$enrevision = 0;
$solucionados = 0;

foreach ($solicitudes as $sol) {
    $estado = strtoupper($sol['estado_error']);
    if ($estado === 'PENDIENTE') {
        $pendientes++;
    } elseif ($estado === 'EN REVISION') {
        $enrevision++;
    } elseif ($estado === 'SOLUCIONADO') {
        $solucionados++;
    }
}
?>

<style>
    :root {
        --color-primario: #022F55;
        --color-primario-suave: #f1f5f9;
        --color-borde-suave: #e5e7eb;
        --color-texto-muted: #6b7280;
    }

    .wrapper-servicio-cliente {
        min-height: 70vh;
        background: linear-gradient(135deg, #f9fafb 0%, #eef2ff 100%);
        border-radius: 18px;
        border: 1px solid #e5e7eb;
    }

    .titulo-seccion {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--color-texto-muted);
        margin-bottom: 0.25rem;
        font-weight: 600;
    }

    .card-resumen-usuario {
        border-radius: 12px;
        background-color: #ffffff;
        border: 1px solid var(--color-borde-suave);
    }

    .card-solicitud {
        border-radius: 12px;
        border: 1px solid var(--color-borde-suave);
        background-color: #ffffff;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }

    .card-solicitud:hover {
        box-shadow: 0 6px 20px rgba(15, 23, 42, 0.12);
        transform: translateY(-1px);
    }

    .contenedor-editar {
        margin-top: 0.25rem;
        min-height: 1.2rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .icono-editar-link {
        text-decoration: none;
    }

    .contenedor-editar .icono-editar {
        opacity: 0;
        pointer-events: none;
        transition: opacity .2s ease-in-out;
    }

    .card-solicitud:hover .icono-editar {
        opacity: 1;
        pointer-events: auto;
    }

    .card-solicitud:hover .contenedor-editar .icono-editar {
        opacity: 1;
        transform: translateY(0);
        color: #022F55;
    }

    .badge-estado {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.18rem 0.7rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-estado-pendiente {
        background-color: #fef3c7;
        color: #92400e;
    }

    .badge-estado-enrevision {
        background-color: #e0f2fe;
        color: #0369a1;
    }

    .badge-estado-solucionado {
        background-color: #dcfce7;
        color: #166534;
    }

    .badge-prioridad {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.18rem 0.7rem;
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

    .circle-container {
        position: relative;
        width: 110px;
        height: 110px;
        display: inline-block;
    }

    .circle-center {
        position: absolute;
        top: 40%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        font-weight: bold;
        line-height: 0.5;
    }

    .circle-center span {
        font-size: 1.1rem;
        display: block;
    }

    .circle-center small {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .ticket-head {
        padding: 0.5rem;
        border-left: 7px dotted #fff;
        color: #fff;
    }

    .ticket-head-pendiente {
        background-color: #f59e0b;
    }

    .ticket-head-enrevision {
        background-color: #0369a1;
    }

    .ticket-head-solucionado {
        background-color: #166534;
    }

    .btn-chat-notif:hover {
        background-color: #002f5522;
    }

    /* badge visual para chat bloqueado */
    .badge-chat-bloqueado {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0.35rem 0.7rem;
        border-radius: 10px;
        border: 1px solid #dc2626;
        color: #dc2626;
        font-size: 0.8rem;
        background: #fff;
        margin-top: 0.5rem;
        margin-left: 4%;
        height: 30px;
    }
</style>

<div class="container-fluid">
    <div class="row px-2">
        <div class="col-4 h-100">
            <div class="card border rounded-4 p-3 d-flex flex-column text-center align-items-center">

                <?php if ($mostrar_foto_usuario): ?>
                    <span class="user-avatar mb-3" style="background-image: url('<?php echo $foto_usuario; ?>'); width:70px; height:70px" title="Foto de <?php echo $display_name; ?>"></span>
                <?php else: ?>
                    <div class="rounded-circle bg-secondary d-flex justify-content-center align-items-center mb-3"
                        style="width: 110px; height: 110px; font-size: 2rem; color: white;">
                        <i class="bi bi-person"></i>
                    </div>
                <?php endif; ?>

                <div>
                    <h5 class="fw-bold"><?php echo htmlspecialchars($nombre_usuario); ?></h5>
                    <p class="mb-0 text-muted small" style="font-size: 0.7em;">
                        <?php echo htmlspecialchars($correo_usuario, ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>

                <div class="row text-center my-4">
                    <div class="circle-container">
                        <canvas id="chartPendientes"></canvas>
                        <div class="circle-center borde">
                            <span id="numPendientes"></span><br>
                            <small id="porcPendientes"></small>
                        </div>
                        <h6 class="mt-2" style="font-size: 0.7em;">Pendientes</h6>
                    </div>

                    <div class="circle-container">
                        <canvas id="chartRevision"></canvas>
                        <div class="circle-center">
                            <span id="numRevision"></span><br>
                            <small id="porcRevision"></small>
                        </div>
                        <h6 class="mt-2" style="font-size: 0.7em;">En Revisión</h6>
                    </div>

                    <div class="circle-container">
                        <canvas id="chartSolucionados"></canvas>
                        <div class="circle-center">
                            <span id="numSolucionados"></span><br>
                            <small id="porcSolucionados"></small>
                        </div>
                        <h6 class="mt-2" style="font-size: 0.7em;">Solucionados</h6>
                    </div>
                </div>

                <div class="w-100 my-2" style="border-bottom:1px solid #002f5594;"></div>

                <h6 class="fw-bold">Actividad reciente</h6>

                <p class="text-start w-100 mb-0" style="font-size: 0.9em;">
                    Has generado <strong><?php echo $total; ?></strong> tickets
                </p>

                <?php if (empty($solicitudes)): ?>
                    <div class="card border shadow-sm p-3 mt-2" style="background-color: #02305530;">
                        <p class="mb-0 text-muted">
                            No se han encontrado solicitudes de soporte registradas con su usuario.
                        </p>
                    </div>
                <?php else: ?>
                    <div class="mt-2 w-100" style="max-height:40vh; overflow-y: auto;">
                        <?php foreach ($solicitudes as $s): ?>
                            <div class="shadow-sm my-2 p-3">
                                <div class="row">
                                    <div class="col-1">
                                        <i class="bi bi-bug-fill fs-5" style="color: #022F55;"></i>
                                    </div>
                                    <div class="col-11 d-flex justify-content-between">
                                        <p class="mb-0" style="font-size: 0.8em;">
                                            Creaste un ticket con código: <strong><?php echo htmlspecialchars($s['codigo_error'], ENT_QUOTES, 'UTF-8'); ?></strong>,
                                            correspondiente a: <strong><?php echo htmlspecialchars($s['tipo_error'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-8 p-2 h-100">
            <span class="fw-bold" style="font-size: 1em;">Historial de solicitudes</span>

            <?php if ($total_solucionados > 0): ?>
                <div id="alert-solucionados"
                    class="alert alert-success d-flex justify-content-between align-items-center py-2 px-3 mt-2 mb-2"
                    style="border-radius: 999px; font-size: 0.9rem;">
                    <span>
                        Tienes <strong><?php echo $total_solucionados; ?></strong> solicitud(es) marcadas como
                        <strong>solucionadas</strong>.
                    </span>
                    <span class="text-muted small">
                        Revisa el detalle en la lista de abajo.
                    </span>
                </div>
            <?php endif; ?>

            <?php if (empty($solicitudes)): ?>
                <div class="card card-especial shadow-sm p-3 mt-2">
                    <p class="mb-0 text-muted">
                        No se han encontrado solicitudes de soporte registradas con su usuario.
                    </p>
                </div>
            <?php else: ?>
                <div class="mt-2" style="max-height:75vh; overflow-y: auto;">
                    <?php foreach ($solicitudes as $s): ?>
                        <?php
                        $estado = strtoupper($s['estado_error'] ?? '');
                        $claseEstado = 'badge-estado-pendiente';
                        if ($estado === 'EN REVISION') {
                            $claseEstado = 'badge-estado-enrevision';
                        } elseif ($estado === 'SOLUCIONADO') {
                            $claseEstado = 'badge-estado-solucionado';
                        }

                        $claseHead = 'ticket-head-pendiente';
                        if ($estado === 'EN REVISION') {
                            $claseHead = 'ticket-head-enrevision';
                        } elseif ($estado === 'SOLUCIONADO') {
                            $claseHead = 'ticket-head-solucionado';
                        }

                        $prioridad = strtolower($s['prioridad'] ?? '');
                        $clasePrioridad = 'badge-prioridad-bajo';
                        if ($prioridad === 'medio' || $prioridad === 'media') {
                            $clasePrioridad = 'badge-prioridad-medio';
                        } elseif ($prioridad === 'alto' || $prioridad === 'alta') {
                            $clasePrioridad = 'badge-prioridad-alto';
                        } elseif ($prioridad === 'urgente') {
                            $clasePrioridad = 'badge-prioridad-urgente';
                        }

                        $chat_bloqueado = (int)($s['chat_bloqueado'] ?? 0);
                        ?>

                        <div class="card mb-3 card-solicitud">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-3 ticket-head <?php echo $claseHead; ?> text-center justify-content-center align-content-center">
                                        <h6 class="mb-0" style="font-size:0.8em;">
                                            Código: <br>
                                            <strong><?php echo htmlspecialchars($s['codigo_error'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                        </h6>
                                        <small style="font-size: 0.75em;">
                                            <?php echo htmlspecialchars($s['fecha_hora_creacion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </small><br>
                                        <span class="badge-estado my-2 <?php echo $claseEstado; ?>">
                                            <?php echo htmlspecialchars($estado ?: 'SIN ESTADO', ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>

                                    <div class="col-9 p-2 shadow">
                                        <div class="container-fluid">
                                            <div class="row">
                                                <div class="col-8 rounded-3 p-2">
                                                    <h6 class="mb-2" style="font-size: 1em;">
                                                        <strong>Asunto:</strong> <?php echo htmlspecialchars($s['asunto'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </h6>

                                                    <p class="mb-1 text-muted" style="font-size: 0.9rem;">
                                                        <strong>Descripción:</strong>
                                                        <?php echo nl2br(htmlspecialchars($s['descripcion'] ?? '', ENT_QUOTES, 'UTF-8')); ?>
                                                    </p>
                                                    <?php
                                                    $codigoTicket   = $s['codigo_error'];
                                                    $chat_bloqueado = (int)($s['chat_bloqueado'] ?? 0);

                                                    $href_chat = $chatSoportePageBase . '&codigo=' . urlencode($codigoTicket);
                                                    ?>

                                                    <div class="chat-boton-wrap"
                                                        data-codigo="<?php echo htmlspecialchars($codigoTicket, ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-href="<?php echo htmlspecialchars($href_chat, ENT_QUOTES, 'UTF-8'); ?>">

                                                        <?php if ($chat_bloqueado === 0): ?>
                                                            <a href="<?php echo $href_chat; ?>"
                                                                class="btn btn-sm btn-chat-notif"
                                                                data-codigo="<?php echo htmlspecialchars($codigoTicket, ENT_QUOTES, 'UTF-8'); ?>"
                                                                style="margin-top: 0.5rem; border-radius: 10px; border: 1px solid #022F55; 
                  color:#022F55; font-size:0.8rem; position: relative; margin-left: 4%; width: 110px; height: 30px; padding-top: 4px;">
                                                                <i class="bi bi-chat-dots me-1"></i> Abrir chat
                                                                <span class="notif-dot" style="
                display:none;
                position:absolute;
                top:-4%;
                width:9px;
                height:9px;
                margin-left:8%;
                border-radius:50%;
                background-color:#dc3545;
                box-shadow:0 0 0 2px #fff;
            "></span>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <div class="col-4 text-center justify-content-center align-content-center" style="border-left: 3px dashed #002f559e;">
                                                    <span class="badge-prioridad <?php echo $clasePrioridad; ?> mt-1">
                                                        <?php echo 'Prioridad: ' . htmlspecialchars($s['prioridad'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>

                                                    <div class="text-muted small mt-1">
                                                        Tipo: <?php echo htmlspecialchars($s['tipo_error'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>

                                                    <div class="contenedor-editar justify-content-center py-2">
                                                        <a href="javascript:void(0);"
                                                            onclick="imprimirTicket('<?php echo htmlspecialchars($s['codigo_error'], ENT_QUOTES, 'UTF-8'); ?>')"
                                                            class="icono-editar btn btn-outline-secondary rounded-3"
                                                            style="font-size: 0.8rem;">
                                                            <i class="bi bi-printer me-1" style="font-size: 0.8rem;"></i> Imprimir
                                                        </a>
                                                        <iframe id="iframeImpresion" style="display:none;" src=""></iframe>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    var pendientes = <?php echo (int)$pendientes; ?>;
    var enrevision = <?php echo (int)$enrevision; ?>;
    var solucionados = <?php echo (int)$solucionados; ?>;

    var total = pendientes + enrevision + solucionados;
    if (total === 0) total = 1;

    var porcPend = Math.round((pendientes / total) * 100);
    var porcRev = Math.round((enrevision / total) * 100);
    var porcSol = Math.round((solucionados / total) * 100);

    function crearGrafico(idCanvas, porcentaje, color) {
        new Chart(document.getElementById(idCanvas), {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [porcentaje, 100 - porcentaje],
                    backgroundColor: [color, '#e5e7eb'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '71%',
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    crearGrafico('chartPendientes', porcPend, '#f59e0b');
    crearGrafico('chartRevision', porcRev, '#065fa8ff');
    crearGrafico('chartSolucionados', porcSol, '#16a34a');

    document.getElementById('numPendientes').textContent = pendientes;
    document.getElementById('porcPendientes').textContent = porcPend + '%';

    document.getElementById('numRevision').textContent = enrevision;
    document.getElementById('porcRevision').textContent = porcRev + '%';

    document.getElementById('numSolucionados').textContent = solucionados;
    document.getElementById('porcSolucionados').textContent = porcSol + '%';
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var alerta = document.getElementById('alert-solucionados');
        if (alerta) {
            setTimeout(function() {
                alerta.style.display = 'none';
                alerta.classList.add('fade');
                alerta.classList.remove('show');
            }, 100000);
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        function getWrappers() {
            return document.querySelectorAll('.chat-boton-wrap');
        }

        function crearBotonChat(wrapper) {
            var codigo = wrapper.getAttribute('data-codigo');
            var href = wrapper.getAttribute('data-href');

            if (wrapper.querySelector('.btn-chat-notif')) return;

            var a = document.createElement('a');
            a.href = href;
            a.className = 'btn btn-sm btn-chat-notif';
            a.setAttribute('data-codigo', codigo);

            a.setAttribute('style',
                'margin-top: 0.5rem; border-radius: 10px; border: 1px solid #022F55;' +
                'color:#022F55; font-size:0.8rem; position: relative; margin-left: 4%;' +
                'width: 110px; height: 30px; padding-top: 4px;'
            );

            a.innerHTML = '<i class="bi bi-chat-dots me-1"></i> Abrir chat';

            var dot = document.createElement('span');
            dot.className = 'notif-dot';
            dot.setAttribute('style',
                'display:none; position:absolute; top:-4%; width:9px; height:9px;' +
                'margin-left:8%; border-radius:50%; background-color:#dc3545;' +
                'box-shadow:0 0 0 2px #fff;'
            );

            a.appendChild(dot);
            wrapper.appendChild(a);
        }

        function quitarBotonChat(wrapper) {
            var btn = wrapper.querySelector('.btn-chat-notif');
            if (btn) btn.remove();
        }

        function codigosDeWrappers() {
            var wrappers = getWrappers();
            var codigos = [];
            wrappers.forEach(function(w) {
                var c = w.getAttribute('data-codigo');
                if (c) codigos.push(c);
            });
            return codigos;
        }

        function codigosConBoton() {
            var botones = document.querySelectorAll('.btn-chat-notif');
            var codigos = [];
            botones.forEach(function(b) {
                var c = b.getAttribute('data-codigo');
                if (c) codigos.push(c);
            });
            return codigos;
        }

        function revisarBloqueos() {
            var codigos = codigosDeWrappers();
            if (codigos.length === 0) return;

            var url = <?php echo json_encode($chatBloqueosUrl . '?codigos='); ?> +
                encodeURIComponent(codigos.join(','));

            fetch(url)
                .then(function(r) {
                    return r.json();
                })
                .then(function(data) {
                    var wrappers = getWrappers();

                    wrappers.forEach(function(w) {
                        var codigo = w.getAttribute('data-codigo');
                        if (!codigo) return;

                        var bloqueado = (data[codigo] && data[codigo].chat_bloqueado === 1);

                        if (bloqueado) {
                            quitarBotonChat(w);
                        } else {
                            crearBotonChat(w);
                        }
                    });
                })
                .catch(function(e) {
                    console.error('Error consultando bloqueos:', e);
                });
        }

        function actualizarNotificacionesChat() {
            var codigos = codigosConBoton();
            if (codigos.length === 0) return;

            var url = <?php echo json_encode($chatNotificacionesUrl . '?codigos='); ?> +
                encodeURIComponent(codigos.join(','));

            fetch(url)
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    codigos.forEach(function(codigo) {
                        var btn = document.querySelector('.btn-chat-notif[data-codigo="' + codigo + '"]');
                        if (!btn) return;

                        var dot = btn.querySelector('.notif-dot');
                        if (!dot) return;

                        if (data[codigo] && data[codigo].tiene_nuevo) {
                            dot.style.display = 'inline-block';
                        } else {
                            dot.style.display = 'none';
                        }
                    });
                })
                .catch(function(error) {
                    console.error('Error al consultar notificaciones de chat:', error);
                });
        }
        revisarBloqueos();
        actualizarNotificacionesChat();
        setInterval(function() {
            revisarBloqueos();
            actualizarNotificacionesChat();
        }, 1000);

    });
</script>

<script>
    function imprimirTicket(codigo) {
        var iframe = document.getElementById('iframeImpresion');
        if (!iframe) return;

        var url = <?php echo json_encode($imprimirSolicitudBase . '&codigo='); ?> + encodeURIComponent(codigo);
        iframe.onload = null;
        iframe.onload = function() {
            try {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            } catch (e) {
                console.error('Error imprimiendo el ticket:', e);
            }
        };
        iframe.src = url;
    }
</script>
