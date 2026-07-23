<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../../../conexion.php';

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
    header("Location: ../../index.php");
    exit();
}
if (!isset($_SESSION['id_usuario'])) {
    exit("Sesión inválida");
}

$usuarioId = (int)$_SESSION['id_usuario'];

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nuevaFin = $_POST['nueva_fecha_fin'] ?? '';
$observacion = trim($_POST['observacion'] ?? '');

if ($id <= 0 || !$nuevaFin) {
    exit("Datos incompletos");
}

try {

    $mysqli->begin_transaction();
    $stmt = $mysqli->prepare("
        SELECT 
            sb_fecha_inicio, 
            sb_fecha_fin, 
            sb_dias_calculados, 
            sb_cobro_diario, 
            sb_valor_cobrar
        FROM solicitud_baqueanos
        WHERE id = ?
        FOR UPDATE
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $base = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$base) {
        throw new Exception("Solicitud no encontrada");
    }

    $inicioBase = $base['sb_fecha_inicio'];
    $finBase    = $base['sb_fecha_fin'];
    $cobroDiario = (int)$base['sb_cobro_diario'];
    $valorBase   = (int)$base['sb_valor_cobrar'];
    $diasBase    = (int)$base['sb_dias_calculados'];

    $stmt = $mysqli->prepare("
        SELECT 
            fecha_fin_nueva, 
            dias_nuevo_total, 
            valor_nuevo_total
        FROM solicitud_baqueanos_extensiones
        WHERE solicitud_id = ?
        ORDER BY id DESC
        LIMIT 1
        FOR UPDATE
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $ext = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $finActual   = $ext ? $ext['fecha_fin_nueva'] : $finBase;
    $diasActual  = $ext ? (int)$ext['dias_nuevo_total'] : $diasBase;
    $valorActual = $ext ? (int)$ext['valor_nuevo_total'] : $valorBase;

    if (strtotime($nuevaFin) <= strtotime($finActual)) {
        throw new Exception("Solo puedes aumentar la fecha fin.");
    }

    $inicioDT   = new DateTime($inicioBase);
    $nuevaFinDT = new DateTime($nuevaFin);

    if ($nuevaFinDT < $inicioDT) {
        throw new Exception("Rango inválido.");
    }

    $diasNuevoTotal = $inicioDT->diff($nuevaFinDT)->days + 1;
    $diasAgregados  = $diasNuevoTotal - $diasActual;

    if ($diasAgregados <= 0) {
        throw new Exception("La extensión no incrementa días.");
    }

    $valorAdicional  = $cobroDiario * $diasAgregados;
    $valorNuevoTotal = $valorActual + $valorAdicional;

    $stmt = $mysqli->prepare("
        INSERT INTO solicitud_baqueanos_extensiones (
            solicitud_id, 
            fecha_inicio_base,
            fecha_fin_anterior, 
            fecha_fin_nueva,
            dias_base, 
            dias_agregados, 
            dias_nuevo_total,
            valor_base, 
            valor_adicional, 
            valor_nuevo_total,
            observacion, 
            creado_por_usuario_id, 
            estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'REGISTRADO')
    ");

    $stmt->bind_param(
        "isssiiiiiisi",
        $id,
        $inicioBase,
        $finActual,
        $nuevaFin,
        $diasActual,
        $diasAgregados,
        $diasNuevoTotal,
        $valorActual,
        $valorAdicional,
        $valorNuevoTotal,
        $observacion,
        $usuarioId
    );

    if (!$stmt->execute()) {
        throw new Exception("No se pudo registrar la extensión.");
    }

    $stmt->close();

    $estadoPendiente = "PENDIENTE";

    $stmt = $mysqli->prepare("
        UPDATE solicitud_baqueanos
        SET sb_estado_operaciones = ?, 
            sb_estado_gerencia = ?
        WHERE id = ?
    ");

    $stmt->bind_param("ssi", $estadoPendiente, $estadoPendiente, $id);

    if (!$stmt->execute()) {
        throw new Exception("No se pudo actualizar el estado de la solicitud.");
    }

    $stmt->close();

    $mysqli->commit();
    $tipo    = "EXTENSION_BAQUEANOS";
    $mensaje = "Se registró una EXTENSIÓN de días en la solicitud ARB_{$id}. Está pendiente de revisión.";

    insertarNotificacionPorRol($mysqli, "director_proyectos", (int)$id, $tipo, $mensaje);
    header("Location: /arbimaps/Arbimaps/index.php?page=baqueanos/solicitudes/vistas/detalles_cargar_cuenta");
    exit();

} catch (Exception $e) {
    $mysqli->rollback();
    exit("Error: " . $e->getMessage());
}