<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../../../../conexion.php';

function limpiar($dato, $conexion)
{
    return mysqli_real_escape_string($conexion, trim($dato));
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
$razon  = isset($_POST['sb_razon_cuenta']) ? trim($_POST['sb_razon_cuenta']) : null;

if (!$id || !$accion) {
    echo "datos incompletos";
    exit;
}

switch ($accion) {
    case 'aprobar':
        $sqlUpdate = "UPDATE solicitud_baqueanos 
                        SET sb_estado_cuenta = 'APROBADO' 
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
        $mensaje = "La solicitud ARB_{$id} fue APROBADA por el director en el proceso de radicación de pago y esta lista para tu revisión.";

        insertarNotificacionPorRol($mysqli, "gerencia", (int)$id, $tipo, $mensaje);

        $_SESSION['alerta'] = 'aprobado';
        header("Location: /arbimaps/Arbimaps/index.php?page=baqueanos/solicitudes/vistas/detalles_cuentas");
        exit;
    case 'devolver':
        if ($razon === null) {
            $razon = '';
        }
        $query_select = "SELECT * FROM solicitud_baqueanos WHERE id = ?";
        $stmt_select = $mysqli->prepare($query_select);
        if (!$stmt_select) {
            die("Error al preparar la consulta SELECT");
        }

        $stmt_select->bind_param("i", $id);
        $stmt_select->execute();
        $cuenta = $stmt_select->get_result()->fetch_assoc();
        $stmt_select->close();

        $sql = "UPDATE solicitud_baqueanos 
                SET sb_estado_cuenta = 'DEVUELTO', sb_razon_cuenta = ? 
                WHERE id = ?";

        $stmtUpdate = $mysqli->prepare($sql);
        if (!$stmtUpdate) {
            die("Error al preparar la consulta UPDATE");
        }

        $stmtUpdate->bind_param("si", $razon, $id);
        if (!$stmtUpdate->execute()) {
            $stmtUpdate->close();
            die("Error al actualizar el estado");
        }
        $stmtUpdate->close();

        $tipo    = "SOLICITUD_BAQUEANOS";
        $mensaje = "La solicitud ARB_{$id} fue DEVUELTA por el área de cuentas en el proceso de radicación de pago."
            . ($razon !== '' ? " Razón: {$razon}" : "");

        insertarNotificacionPorRol($mysqli, "gerencia", (int)$id, $tipo, $mensaje);

        $cuenta['sb_estado_cuenta'] = 'DEVUELTO';
        $cuenta['sb_razon_cuenta'] = $razon;

        $mapa = [
            'id'                        => 'copia_id',
            'usuario_id'                => 'copia_usuario_id',
            'sb_tipo_documento'         => 'copia_sb_tipo_documento',
            'sb_numero_identidad'       => 'copia_sb_numero_identidad',
            'sb_baqueano_nombre'        => 'copia_sb_baqueano_nombre',
            'sb_baqueano_apellido'      => 'copia_sb_baqueano_apellido',
            'sb_telefono_baqueano'      => 'copia_sb_telefono_baqueano',
            'sb_correo_baqueano'        => 'copia_sb_correo_baqueano',
            'sb_direccion'              => 'copia_sb_direccion',
            'sb_cuenta'                 => 'copia_sb_cuenta',
            'sb_tipo_cuenta'            => 'copia_sb_tipo_cuenta',
            'sb_num_cuenta'             => 'copia_sb_num_cuenta',
            'sb_titular'                => 'copia_sb_titular',
            'sb_year'                   => 'copia_sb_year',
            'sb_fecha_inicio'           => 'copia_sb_fecha_inicio',
            'sb_fecha_fin'              => 'copia_sb_fecha_fin',
            'sb_dias_calculados'        => 'copia_sb_dias_calculados',
            'sb_cobro_diario'           => 'copia_sb_cobro_diario',
            'sb_valor_cobrar'           => 'copia_sb_valor_cobrar',
            'sb_unidad_intervencion'    => 'copia_sb_unidad_intervencion',
            'sb_unidad_operativa'       => 'copia_sb_unidad_operativa',
            'sb_tipo_unidad'            => 'copia_sb_tipo_unidad',
            'sb_municipio'              => 'copia_sb_municipio',
            'sb_vereda'                 => 'copia_sb_vereda',
            'sb_tipo_actividad'         => 'copia_sb_tipo_actividad',
            'sb_coordinador'            => 'copia_sb_coordinador',
            'sb_lider_cuadrilla'        => 'copia_sb_lider_cuadrilla',
            'sb_transporte'             => 'copia_sb_transporte',
            'sb_porque_transporte'      => 'copia_sb_porque_transporte',
            'sb_cuanto_hospedaje'       => 'copia_sb_cuanto_hospedaje',
            'sb_hospedaje'              => 'copia_sb_hospedaje',
            'sb_porque_hospedaje'       => 'copia_sb_porque_hospedaje',
            'sb_cuanto_transporte'      => 'copia_sb_cuanto_transporte',
            'sb_estado_lider'           => 'copia_sb_estado_lider',
            'sb_razon_lider'            => 'copia_sb_razon_lider',
            'sb_estado_profesional'     => 'copia_sb_estado_profesional',
            'sb_razon_profesional'      => 'copia_sb_razon_profesional',
            'sb_estado_operaciones'     => 'copia_sb_estado_operaciones',
            'sb_razon_operaciones'      => 'copia_sb_razon_operaciones',
            'sb_estado_gerencia'        => 'copia_sb_estado_gerencia',
            'sb_razon_gerencia'         => 'copia_sb_razon_gerencia',
            'sb_estado'                 => 'copia_sb_estado',
            'sb_razon_baqueano'         => 'copia_sb_razon_baqueano',
            'sb_estado_pagos'           => 'copia_sb_estado_pagos',
            'sb_profesional_baqueano'   => 'copia_sb_profesional_baqueano',
            'ba_periodo_facturacion'    => 'copia_ba_periodo_facturacion',
            'sb_cuenta_baqueano'        => 'copia_sb_cuenta_baqueano',
            'sb_cedula_baqueano'        => 'copia_sb_cedula_baqueano',
            'sb_rut_baqueano'           => 'copia_sb_rut_baqueano',
            'sb_certificado_baqueano'   => 'copia_sb_certificado_baqueano',
            'sb_observacion_cuenta'     => 'copia_sb_observacion_cuenta',
            'sb_estado_cuenta'          => 'copia_sb_estado_cuenta',
            'sb_razon_cuenta'           => 'copia_sb_razon_cuenta',
            'fecha_devolucion_cuenta'   => 'copia_fecha_devolucion_cuenta'
        ];
        $columnas = [];
        $valores = [];
        foreach ($mapa as $campo_original => $campo_copia) {
            $columnas[] = $campo_copia;
            $valores[] = $cuenta[$campo_original] ?? null;
        }

        $columnas[] = "fecha_rechazo";
        $valores[] = date("Y-m-d H:i:s");
        $columnas[] = "usuario_rechazo_id";
        $valores[] = $_SESSION['id_usuario'];

        $placeholders = implode(", ", array_fill(0, count($valores), "?"));
        $columnas_sql = implode(", ", $columnas);

        $sql_insert = "INSERT INTO copia_devolucion_baqueanos ($columnas_sql) VALUES ($placeholders)";
        $stmt_insert = $mysqli->prepare($sql_insert);
        if (!$stmt_insert) {
            die("Error al preparar INSERT: " . $mysqli->error);
        }

        $tipos = str_repeat("s", count($valores));
        $stmt_insert->bind_param($tipos, ...$valores);
        if (!$stmt_insert->execute()) {
            die("Error al insertar: " . $stmt_insert->error);
        }
        $stmt_insert->close();
        $_SESSION['alerta'] = 'devuelto';
        header("Location: /arbimaps/Arbimaps/index.php?page=baqueanos/solicitudes/vistas/detalles_cuentas");
        exit;
    case 'editar':
        break;
    default:
        echo "acción no válida";
        exit;
}
$mysqli->close();