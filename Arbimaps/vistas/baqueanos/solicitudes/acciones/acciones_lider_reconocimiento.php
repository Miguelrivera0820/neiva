<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../../../../conexion.php';

function insertarNotificacionSolicitud(mysqli $mysqli, int $solicitudId, string $tipo, string $mensaje): void
{
    $usuarioSolicitud = 0;
    $sqlNoti = "SELECT usuario_id FROM solicitud_baqueanos WHERE id = ? LIMIT 1";
    $stmtNoti = $mysqli->prepare($sqlNoti);
    if (!$stmtNoti) {
        die("Error preparando SELECT usuario_id: " . $mysqli->error);
    }
    $stmtNoti->bind_param("i", $solicitudId);
    if (!$stmtNoti->execute()) {
        $stmtNoti->close();
        die("Error ejecutando SELECT usuario_id: " . $stmtNoti->error);
    }

    $stmtNoti->bind_result($usuarioSolicitud);
    if (!$stmtNoti->fetch()) {
        $stmtNoti->close();
        return;
    }
    $stmtNoti->close();

    $usuarioSolicitud = (int)$usuarioSolicitud;
    if ($usuarioSolicitud <= 0) {
        return;
    }

    $sqlMensaje = "INSERT INTO notificaciones_baqueanos
                                (
                                    id_usuario, 
                                    solicitud_id, 
                                    tipo, 
                                    mensaje, 
                                    leida, 
                                    fecha_creacion
                                )
                    VALUES (?, ?, ?, ?, 0, NOW())";
    $stmtMensaje = $mysqli->prepare($sqlMensaje);
    if (!$stmtMensaje) {
        die("Error preparando INSERT notificaciones_baqueanos: " . $mysqli->error);
    }

    $stmtMensaje->bind_param("iiss", $usuarioSolicitud, $solicitudId, $tipo, $mensaje);
    if (!$stmtMensaje->execute()) {
        $stmtMensaje->close();
        die("Error insertando notificación: " . $stmtMensaje->error);
    }
    $stmtMensaje->close();
}

function insertarNotificacionPorRol(mysqli $mysqli, string $rolDestino, int $solicitudId, string $tipo, string $mensaje): void
{
    $sqlUsuarios = "SELECT id_usuario 
                    FROM usuarios_cons 
                    WHERE rol_usuario = ? 
                        OR rol_usuario_dos = ? 
                        OR rol_usuario_tres = ?";
    $stmtUsuarios = $mysqli->prepare($sqlUsuarios);
    if (!$stmtUsuarios) {
        die("Error preparando SELECT usuarios por rol: " . $mysqli->error);
    }

    $stmtUsuarios->bind_param("sss", $rolDestino, $rolDestino, $rolDestino);
    if (!$stmtUsuarios->execute()) {
        $stmtUsuarios->close();
        die("Error ejecutando SELECT usuarios por rol: " . $stmtUsuarios->error);
    }

    $resultado = $stmtUsuarios->get_result();
    if ($resultado->num_rows === 0) {
        $stmtUsuarios->close();
        return;
    }

    $sqlInsert = "INSERT INTO notificaciones_baqueanos
                                (
                                    id_usuario, 
                                    solicitud_id, 
                                    tipo, 
                                    mensaje, 
                                    leida, 
                                    fecha_creacion
                                )
                    VALUES (?, ?, ?, ?, 0, NOW())";
    $stmtInsert = $mysqli->prepare($sqlInsert);
    if (!$stmtInsert) {
        $stmtUsuarios->close();
        die("Error preparando INSERT notificación: " . $mysqli->error);
    }

    while ($usuario = $resultado->fetch_assoc()) {
        $idUsuario = (int)$usuario['id_usuario'];
        $stmtInsert->bind_param("iiss", $idUsuario, $solicitudId, $tipo, $mensaje);
        $stmtInsert->execute();
    }
    $stmtInsert->close();
    $stmtUsuarios->close();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit;
}

$id     = $_POST['id'] ?? null;
$accion = $_POST['accion'] ?? null;
$razon  = isset($_POST['sb_razon_profesional']) ? trim($_POST['sb_razon_profesional']) : '';

if (!$id || !$accion) {
    echo "datos incompletos";
    exit;
}

switch ($accion) {
    case 'aprobar':
        $sqlUpdate = "UPDATE solicitud_baqueanos 
                        SET sb_estado_profesional = 'APROBADO' 
                        WHERE id = ?";
        $stmt = $mysqli->prepare($sqlUpdate);
        if (!$stmt) {
            die("Error al preparar la consulta");
        }

        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            $stmt->close();
            die("Error al actualizar el estado");
        }
        $stmt->close();

        $tipo    = "SOLICITUD_BAQUEANOS";
        $mensaje = "La solicitud ARB_{$id} fue APROBADA por el profesional social y está pendiente para tu aprobación.";

        insertarNotificacionPorRol($mysqli, "director_proyectos", (int)$id, $tipo, $mensaje);

        $_SESSION['alerta'] = 'aprobado';
        header("Location: /arbimaps/Arbimaps/index.php?page=baqueanos/solicitudes/vistas/detalles_profesional_social");
        exit;
    case 'devolver':
        $sqlUpdate = "UPDATE solicitud_baqueanos 
                        SET sb_estado_profesional = 'DEVUELTO', sb_razon_profesional = ? 
                        WHERE id = ?";
        $stmt = $mysqli->prepare($sqlUpdate);
        if (!$stmt) {
            die("Error al preparar la consulta");
        }

        $stmt->bind_param("si", $razon, $id);
        if (!$stmt->execute()) {
            $stmt->close();
            die("Error al actualizar el estado");
        }
        $stmt->close();

        $tipo    = "SOLICITUD_BAQUEANOS";
        $mensaje = "Su solicitud ARB_{$id} fue DEVUELTA por el profesional social."
            . ($razon !== '' ? " Razón: {$razon}" : "");

        insertarNotificacionSolicitud($mysqli, (int)$id, $tipo, $mensaje);

        $_SESSION['alerta'] = 'devuelto';
        header("Location: /arbimaps/Arbimaps/index.php?page=baqueanos/solicitudes/vistas/detalles_profesional_social");
        exit;
    case 'editar':
        $sb_fecha_inicio    = trim($mysqli->real_escape_string($_POST['sb_fecha_inicio'] ?? ''));
        $sb_fecha_fin       = trim($mysqli->real_escape_string($_POST['sb_fecha_fin'] ?? ''));
        $sb_dias_calculados = trim($mysqli->real_escape_string($_POST['sb_dias_calculados'] ?? ''));
        $sb_valor_cobrar    = preg_replace('/[^\d]/', '', $_POST['sb_valor_cobrar'] ?? '');
        $sb_cobro_diario    = preg_replace('/[^\d]/', '', $_POST['sb_cobro_diario'] ?? '');
        if (empty($sb_fecha_inicio) || empty($sb_fecha_fin) || empty($sb_dias_calculados)) {
            die("Datos incompletos para editar");
        }
        $sql = "UPDATE solicitud_baqueanos SET
                sb_fecha_inicio    = ?,
                sb_fecha_fin       = ?,
                sb_dias_calculados = ?,
                sb_valor_cobrar    = ?,
                sb_cobro_diario    = ?
            WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            die("Error al preparar la consulta");
        }

        $stmt->bind_param(
            "ssiddi",
            $sb_fecha_inicio,
            $sb_fecha_fin,
            $sb_dias_calculados,
            $sb_valor_cobrar,
            $sb_cobro_diario,
            $id
        );
        if (!$stmt->execute()) {
            $stmt->close();
            die("Error al actualizar la solicitud");
        }
        $stmt->close();

        $tipo    = "SOLICITUD_BAQUEANOS";
        $mensaje = "Su solicitud ARB_{$id} fue EDITADA por el profesional social.";

        insertarNotificacionSolicitud($mysqli, (int)$id, $tipo, $mensaje);

        $_SESSION['alerta'] = 'editado';
        header("Location: /arbimaps/Arbimaps/index.php?page=baqueanos/solicitudes/vistas/detalles_profesional_social");
        exit;
    default:
        echo "acción no válida";
        exit;
}
$mysqli->close();
