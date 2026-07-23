<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../../../../conexion.php';

function limpiar($dato, $conexion)
{
    return mysqli_real_escape_string($conexion, trim((string)$dato));
}

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
    if ($usuarioSolicitud <= 0) return;

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
$razon  = isset($_POST['sb_razon_operaciones']) ? trim($_POST['sb_razon_operaciones']) : '';

if (!$id || !$accion) {
    echo "datos incompletos";
    exit;
}
switch ($accion) {
    case 'aprobar':
        $sql = "UPDATE solicitud_baqueanos 
                SET sb_estado_operaciones = 'APROBADO' 
                WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) die("Error al preparar la consulta: " . $mysqli->error);

        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            $stmt->close();
            die("Error al actualizar el estado: " . $stmt->error);
        }
        $stmt->close();
        $tipo    = "SOLICITUD_BAQUEANOS";
        $mensaje = "La solicitud ARB_{$id} fue APROBADA por el jefe de operaciones y está pendiente de tu revisión en gerencia.";

        insertarNotificacionPorRol($mysqli, "gerencia", (int)$id, $tipo, $mensaje);

        $_SESSION['alerta'] = 'aprobado';
        header("Location: /arbimaps/Arbimaps/index.php?page=baqueanos/solicitudes/vistas/validar_solicitud");
        exit;
    case 'devolver':
        $sql2 = "UPDATE solicitud_baqueanos 
                    SET sb_estado_operaciones = 'DEVUELTO', 
                        sb_razon_operaciones = ?
                    WHERE id = ?";
        $stmt2 = $mysqli->prepare($sql2);
        if (!$stmt2) die("Error al preparar la consulta: " . $mysqli->error);
        $stmt2->bind_param("si", $razon, $id);
        if (!$stmt2->execute()) {
            $stmt2->close();
            die("Error al actualizar el estado: " . $stmt2->error);
        }
        $stmt2->close();

        $tipo    = "SOLICITUD_BAQUEANOS";
        $mensaje = "Su solicitud ARB_{$id} fue DEVUELTA por el jefe de operaciones."
            . ($razon !== '' ? " Razón: {$razon}" : "");

        insertarNotificacionSolicitud($mysqli, (int)$id, $tipo, $mensaje);

        $_SESSION['alerta'] = 'devuelto';
        header("Location: /arbimaps/Arbimaps/index.php?page=baqueanos/solicitudes/vistas/validar_solicitud");
        exit;
    case 'editar':
        $sb_fecha_inicio        = limpiar($_POST['sb_fecha_inicio'] ?? '', $mysqli);
        $sb_fecha_fin           = limpiar($_POST['sb_fecha_fin'] ?? '', $mysqli);

        $sb_dias_calculados     = (int)preg_replace('/[^\d]/', '', $_POST['sb_dias_calculados'] ?? '0');
        $sb_cobro_diario        = (int)preg_replace('/[^\d]/', '', $_POST['sb_cobro_diario'] ?? '0');

        $sb_cuenta              = limpiar($_POST['sb_cuenta'] ?? '', $mysqli);
        $sb_tipo_cuenta         = limpiar($_POST['sb_tipo_cuenta'] ?? '', $mysqli);
        $sb_num_cuenta          = limpiar($_POST['sb_num_cuenta'] ?? '', $mysqli);
        $sb_titular             = limpiar($_POST['sb_titular'] ?? '', $mysqli);

        $sb_unidad_intervencion = limpiar($_POST['sb_unidad_intervencion'] ?? '', $mysqli);
        $sb_unidad_operativa    = limpiar($_POST['sb_unidad_operativa'] ?? '', $mysqli);
        $sb_tipo_unidad         = limpiar($_POST['sb_tipo_unidad'] ?? '', $mysqli);
        $sb_vereda              = limpiar($_POST['sb_vereda'] ?? '', $mysqli);

        $sb_tipo_actividad      = limpiar($_POST['sb_tipo_actividad'] ?? '', $mysqli);
        $sb_coordinador         = limpiar($_POST['sb_coordinador'] ?? '', $mysqli);
        $sb_lider_cuadrilla     = limpiar($_POST['sb_lider_cuadrilla'] ?? '', $mysqli);

        $sb_transporte          = limpiar($_POST['sb_transporte'] ?? 'NO', $mysqli);
        $sb_porque_transporte   = limpiar($_POST['sb_porque_transporte'] ?? '', $mysqli);
        $sb_cuanto_transporte   = (int)preg_replace('/[^\d]/', '', $_POST['sb_cuanto_transporte'] ?? '0');

        $sb_hospedaje           = limpiar($_POST['sb_hospedaje'] ?? 'NO', $mysqli);
        $sb_porque_hospedaje    = limpiar($_POST['sb_porque_hospedaje'] ?? '', $mysqli);
        $sb_cuanto_hospedaje    = (int)preg_replace('/[^\d]/', '', $_POST['sb_cuanto_hospedaje'] ?? '0');

        if ($sb_transporte !== 'SI') {
            $sb_porque_transporte = '';
            $sb_cuanto_transporte = 0;
        }
        if ($sb_hospedaje !== 'SI') {
            $sb_porque_hospedaje = '';
            $sb_cuanto_hospedaje = 0;
        }

        $sb_valor_cobrar = ($sb_dias_calculados * $sb_cobro_diario) + $sb_cuanto_transporte + $sb_cuanto_hospedaje;

        $sql = "UPDATE solicitud_baqueanos SET 
        sb_fecha_inicio         = ?, 
        sb_fecha_fin            = ?, 
        sb_dias_calculados      = ?,
        sb_valor_cobrar         = ?,

        sb_cuenta               = ?,
        sb_tipo_cuenta          = ?,
        sb_num_cuenta           = ?,
        sb_titular              = ?,

        sb_unidad_intervencion  = ?, 
        sb_unidad_operativa     = ?, 
        sb_tipo_unidad          = ?, 
        sb_vereda               = ?, 
        sb_tipo_actividad       = ?, 
        sb_coordinador          = ?, 
        sb_lider_cuadrilla      = ?, 
        sb_transporte           = ?, 
        sb_porque_transporte    = ?, 
        sb_cuanto_transporte    = ?,
        sb_hospedaje            = ?, 
        sb_porque_hospedaje     = ?, 
        sb_cuanto_hospedaje     = ?,
        sb_cobro_diario         = ?
        WHERE id = ?";

        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta: ' . $mysqli->error]);
            exit;
        }

        $types = "ssii" . "ssss" . "sssssssss" . "i" . "ss" . "i" . "i" . "i";

        $stmt->bind_param(
            $types,
            $sb_fecha_inicio,
            $sb_fecha_fin,
            $sb_dias_calculados,
            $sb_valor_cobrar,

            $sb_cuenta,
            $sb_tipo_cuenta,
            $sb_num_cuenta,
            $sb_titular,

            $sb_unidad_intervencion,
            $sb_unidad_operativa,
            $sb_tipo_unidad,
            $sb_vereda,
            $sb_tipo_actividad,
            $sb_coordinador,
            $sb_lider_cuadrilla,
            $sb_transporte,
            $sb_porque_transporte,

            $sb_cuanto_transporte,

            $sb_hospedaje,
            $sb_porque_hospedaje,

            $sb_cuanto_hospedaje,
            $sb_cobro_diario,
            $id
        );

        if ($stmt->execute()) {
            $tipo    = "SOLICITUD_BAQUEANOS";
            $mensaje = "Su solicitud ARB_{$id} fue EDITADA por el jefe de operaciones.";
            insertarNotificacionSolicitud($mysqli, (int)$id, $tipo, $mensaje);

            echo json_encode(['success' => true, 'message' => 'Solicitud editada correctamente']);
            $stmt->close();
            exit;
        } else {
            $stmt->close();
            echo json_encode(['success' => false, 'message' => 'Error al editar la solicitud: ' . $stmt->error]);
            exit;
        }
    default:
        echo "acción no válida";
        exit;
}
$mysqli->close();
