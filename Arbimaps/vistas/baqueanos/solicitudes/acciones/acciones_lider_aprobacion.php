<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../../../../conexion.php';

function limpiar($dato, $conexion)
{
    return mysqli_real_escape_string($conexion, trim((string)$dato));
}

function limpiarNullable($dato, $conexion)
{
    $v = trim((string)($dato ?? ''));
    if ($v === '') return null;
    return mysqli_real_escape_string($conexion, $v);
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
    if ($usuarioSolicitud <= 0) {
        return;
    }

    $sqlMensaje = "INSERT INTO notificaciones_baqueanos
                    (id_usuario, solicitud_id, tipo, mensaje, leida, fecha_creacion)
                    VALUES (?, ?, ?, ?, 0, NOW())";
    $stmtMensaje = $mysqli->prepare($sqlMensaje);
    if (!$stmtMensaje) {
        die("Error preparando INSERT notificación: " . $mysqli->error);
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
                    (id_usuario, solicitud_id, tipo, mensaje, leida, fecha_creacion)
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
$razon  = isset($_POST['sb_razon_lider']) ? trim($_POST['sb_razon_lider']) : '';

if (!$id || !$accion) {
    echo "datos incompletos";
    exit;
}

switch ($accion) {

    case 'aprobar':
        $sqlUpdate = "UPDATE solicitud_baqueanos 
                        SET sb_estado_lider = 'APROBADO' 
                        WHERE id = ?";
        $stmt = $mysqli->prepare($sqlUpdate);
        if (!$stmt) die("Error al preparar la consulta");

        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            $stmt->close();
            die("Error al actualizar el estado");
        }
        $stmt->close();

        $tipo    = "SOLICITUD_BAQUEANO";
        $mensaje = "La solicitud ARB_{$id} fue APROBADA por el líder de reconocimiento y está pendiente para tu aprobación";

        insertarNotificacionPorRol($mysqli, "social", (int)$id, $tipo, $mensaje);

        $_SESSION['alerta'] = 'aprobado';
        header("Location: /arbimaps/Arbimaps/index.php?page=baqueanos/solicitudes/vistas/detalles_solicitudes");
        exit;

    case 'devolver':
        $sqlUpdate = "UPDATE solicitud_baqueanos 
                        SET sb_estado_lider = 'DEVUELTO', sb_razon_lider = ? 
                        WHERE id = ?";
        $stmt = $mysqli->prepare($sqlUpdate);
        if (!$stmt) die("Error al preparar la consulta");

        $stmt->bind_param("si", $razon, $id);
        if (!$stmt->execute()) {
            $stmt->close();
            die("Error al actualizar el estado");
        }
        $stmt->close();

        $tipo    = "SOLICITUD_BAQUEANO";
        $mensaje = "Su solicitud ARB_{$id} fue DEVUELTA por el líder de reconocimiento."
            . ($razon !== '' ? " Razón: {$razon}" : "");

        insertarNotificacionSolicitud($mysqli, (int)$id, $tipo, $mensaje);

        $_SESSION['alerta'] = 'devuelto';
        header("Location: /arbimaps/Arbimaps/index.php?page=baqueanos/solicitudes/vistas/detalles_solicitudes");
        exit;

    case 'editar':
        // Datos generales / financieros (opcionales)
        $sb_direccion            = limpiarNullable($_POST['sb_direccion'] ?? null, $mysqli);

        $sb_cuenta               = limpiarNullable($_POST['sb_cuenta'] ?? null, $mysqli);
        $sb_tipo_cuenta          = limpiarNullable($_POST['sb_tipo_cuenta'] ?? null, $mysqli);
        $sb_num_cuenta           = limpiarNullable($_POST['sb_num_cuenta'] ?? null, $mysqli);
        $sb_titular              = limpiarNullable($_POST['sb_titular'] ?? null, $mysqli);

        $sb_profesional_baqueano = limpiarNullable($_POST['sb_profesional_baqueano'] ?? null, $mysqli);
        $sb_year                 = limpiarNullable($_POST['sb_year'] ?? null, $mysqli);
        $sb_reconocedor          = limpiarNullable($_POST['sb_reconocedor'] ?? null, $mysqli);

        // Fechas y valores
        $sb_fecha_inicio         = limpiar($_POST['sb_fecha_inicio'] ?? '', $mysqli);
        $sb_fecha_fin            = limpiar($_POST['sb_fecha_fin'] ?? '', $mysqli);
        $sb_dias_calculados      = limpiar($_POST['sb_dias_calculados'] ?? '', $mysqli);

        // Valores pueden venir vacíos (opcionales)
        $sb_cobro_diario         = limpiarNullable($_POST['sb_cobro_diario'] ?? null, $mysqli);
        $sb_valor_cobrar         = limpiarNullable($_POST['sb_valor_cobrar'] ?? null, $mysqli);

        // Ubicación
        $sb_unidad_intervencion  = limpiarNullable($_POST['sb_unidad_intervencion'] ?? null, $mysqli);
        $sb_unidad_operativa     = limpiarNullable($_POST['sb_unidad_operativa'] ?? null, $mysqli);
        $sb_tipo_unidad          = limpiarNullable($_POST['sb_tipo_unidad'] ?? null, $mysqli);
        $sb_municipio            = limpiarNullable($_POST['sb_municipio'] ?? null, $mysqli);
        $sb_vereda               = limpiarNullable($_POST['sb_vereda'] ?? null, $mysqli);

        // Actividad
        $sb_tipo_actividad       = limpiarNullable($_POST['sb_tipo_actividad'] ?? null, $mysqli);
        $sb_coordinador          = limpiarNullable($_POST['sb_coordinador'] ?? null, $mysqli);
        $sb_lider_cuadrilla      = limpiarNullable($_POST['sb_lider_cuadrilla'] ?? null, $mysqli);

        // Transporte
        $sb_transporte           = limpiarNullable($_POST['sb_transporte'] ?? null, $mysqli);
        $sb_porque_transporte    = limpiarNullable($_POST['sb_porque_transporte'] ?? null, $mysqli);
        $sb_cuanto_transporte    = limpiarNullable($_POST['sb_cuanto_transporte'] ?? null, $mysqli);

        // Hospedaje (ANTES FALTABAN ESTOS 3, ahora quedan bien)
        $sb_hospedaje            = limpiarNullable($_POST['sb_hospedaje'] ?? null, $mysqli);
        $sb_porque_hospedaje     = limpiarNullable($_POST['sb_porque_hospedaje'] ?? null, $mysqli);
        $sb_cuanto_hospedaje     = limpiarNullable($_POST['sb_cuanto_hospedaje'] ?? null, $mysqli);

        $sqlUpdate = "UPDATE solicitud_baqueanos 
                    SET 
                        sb_direccion = ?,
                        sb_cuenta = ?,
                        sb_tipo_cuenta = ?,
                        sb_num_cuenta = ?,
                        sb_titular = ?,
                        sb_profesional_baqueano = ?,
                        sb_year = ?,
                        sb_reconocedor = ?,
                        
                        sb_fecha_inicio = ?,
                        sb_fecha_fin = ?,
                        sb_dias_calculados = ?,
                        sb_cobro_diario = ?,
                        sb_valor_cobrar = ?,

                        sb_unidad_intervencion = ?,
                        sb_unidad_operativa = ?,
                        sb_tipo_unidad = ?,
                        sb_municipio = ?,
                        sb_vereda = ?,

                        sb_tipo_actividad = ?,
                        sb_coordinador = ?,
                        sb_lider_cuadrilla = ?,

                        sb_transporte = ?,
                        sb_porque_transporte = ?,
                        sb_cuanto_transporte = ?,

                        sb_hospedaje = ?,
                        sb_porque_hospedaje = ?,
                        sb_cuanto_hospedaje = ?

                    WHERE id = ?";

        $stmt = $mysqli->prepare($sqlUpdate);
        if (!$stmt) die("Error al preparar la consulta: " . $mysqli->error);

        $types = str_repeat("s", 27) . "i";

        $stmt->bind_param(
            $types,
            $sb_direccion,
            $sb_cuenta,
            $sb_tipo_cuenta,
            $sb_num_cuenta,
            $sb_titular,
            $sb_profesional_baqueano,
            $sb_year,
            $sb_reconocedor,

            $sb_fecha_inicio,
            $sb_fecha_fin,
            $sb_dias_calculados,
            $sb_cobro_diario,
            $sb_valor_cobrar,

            $sb_unidad_intervencion,
            $sb_unidad_operativa,
            $sb_tipo_unidad,
            $sb_municipio,
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

            $id
        );

        if (!$stmt->execute()) {
            $stmt->close();
            die("Error al actualizar los datos: " . $stmt->error);
        }
        $stmt->close();

        $tipo    = "SOLICITUD_BAQUEANO";
        $mensaje = "Su solicitud ARB_{$id} fue EDITADA por el líder de reconocimiento.";
        insertarNotificacionSolicitud($mysqli, (int)$id, $tipo, $mensaje);

        $_SESSION['alerta'] = 'editado';
        header("Location: /arbimaps/Arbimaps/index.php?page=baqueanos/solicitudes/vistas/detalles_solicitudes");
        exit;
}

$mysqli->close();
