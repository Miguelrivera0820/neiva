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
    if (!$stmtUsuarios) return;

    $stmtUsuarios->bind_param("sss", $rolDestino, $rolDestino, $rolDestino);

    if (!$stmtUsuarios->execute()) {
        $stmtUsuarios->close();
        return;
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
        return;
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
    $back = $_SERVER['HTTP_REFERER'] ?? '/';
    header('Location: ' . $back);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Compatibilidad con claves de sesión diferentes: 'id_usuario' o 'id'.
    $usuario_id = $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? $_POST['sol_usuario_id'] ?? null;
    if (!$usuario_id) {
        echo json_encode(['success' => false, 'message' => "Usuario no autenticado."]);
        exit;
    }
    // Datos recibidos
    $sb_tipo_documento          = $_POST['sb_tipo_documento'] ?? null;
    $sb_numero_identidad        = $_POST['sb_numero_identidad'] ?? null;
    $sb_baqueano_nombre         = $_POST['sb_baqueano_nombre'] ?? null;
    $sb_baqueano_apellido       = $_POST['sb_baqueano_apellido'] ?? null;
    $sb_telefono_baqueano       = $_POST['sb_telefono_baqueano'] ?? null;
    $sb_correo_baqueano         = $_POST['sb_correo_baqueano'] ?? null;
    $sb_direccion               = $_POST['sb_direccion'] ?? null;
    $sb_cuenta                  = $_POST['sb_cuenta'] ?? null;
    $sb_tipo_cuenta             = $_POST['sb_tipo_cuenta'] ?? null;
    $sb_num_cuenta              = $_POST['sb_num_cuenta'] ?? null;
    $sb_titular                 = $_POST['sb_titular'] ?? null;
    $sb_year                    = $_POST['sb_year'] ?? null;
    $sb_fecha_inicio            = $_POST['sb_fecha_inicio'] ?? null;
    $sb_fecha_fin               = $_POST['sb_fecha_fin'] ?? null;
    $sb_dias_calculados         = $_POST['sb_dias_calculados'] ?? null;
    $sb_cobro_diario            = $_POST['sb_cobro_diario'] ?? null;
    $sb_valor_cobrar            = $_POST['sb_valor_cobrar'] ?? null;
    $sb_cobro_diario = $sb_cobro_diario !== null ? preg_replace('/\D+/', '', $sb_cobro_diario) : null;
    $sb_valor_cobrar = $sb_valor_cobrar !== null ? preg_replace('/\D+/', '', $sb_valor_cobrar) : null;
    $sb_unidad_intervencion     = $_POST['sb_unidad_intervencion'] ?? null;
    $sb_unidad_operativa        = $_POST['sb_unidad_operativa'] ?? null;
    $sb_tipo_unidad             = $_POST['sb_tipo_unidad'] ?? null;
    $sb_municipio               = $_POST['sb_municipio'] ?? null;
    $sb_vereda                  = $_POST['sb_vereda'] ?? null;
    $sb_tipo_actividad = (isset($_POST['sb_tipo_actividad']) && trim($_POST['sb_tipo_actividad']) !== '')
        ? trim($_POST['sb_tipo_actividad'])
        : null;
    $sb_coordinador             = $_POST['sb_coordinador'] ?? null;
    $sb_lider_cuadrilla         = $_POST['sb_lider_cuadrilla'] ?? null;
    $sb_transporte              = $_POST['sb_transporte'] ?? null;
    $sb_porque_transporte       = $_POST['sb_porque_transporte'] ?? null;
    $sb_hospedaje               = $_POST['sb_hospedaje'] ?? null;
    $sb_porque_hospedaje        = $_POST['sb_porque_hospedaje'] ?? null;
    $sb_cuanto_transporte       = $_POST['sb_cuanto_transporte'] ?? null;
    $sb_cuanto_hospedaje        = $_POST['sb_cuanto_hospedaje'] ?? null;
    $sb_profesional_baqueano    = $_POST['sb_profesional_baqueano'] ?? null;
    $sb_reconocedor             = $_POST['sb_reconocedor'] ?? null;


    $sb_cuenta      = isset($_POST['sb_cuenta']) && trim($_POST['sb_cuenta']) !== '' ? trim($_POST['sb_cuenta']) : null;
    $sb_tipo_cuenta = isset($_POST['sb_tipo_cuenta']) && trim($_POST['sb_tipo_cuenta']) !== '' ? trim($_POST['sb_tipo_cuenta']) : null;
    $sb_num_cuenta  = isset($_POST['sb_num_cuenta']) && trim($_POST['sb_num_cuenta']) !== '' ? trim($_POST['sb_num_cuenta']) : null;
    $sb_titular     = isset($_POST['sb_titular']) && trim($_POST['sb_titular']) !== '' ? trim($_POST['sb_titular']) : null;


    // Validación
    if (!filter_var($sb_correo_baqueano, FILTER_VALIDATE_EMAIL)) {
        die(json_encode(['success' => false, 'message' => "Correo electrónico no válido."]));
    }


    $sb_estado_lider = 'PENDIENTE';
    $sb_estado_profesional = 'PENDIENTE';
    $sb_estado_operaciones = 'PENDIENTE';
    $sb_estado_gerencia = 'PENDIENTE';
    $sb_estado_pagos = 'PENDIENTE';

    // Inserción en solicitud_baqueanos
    $sql = "INSERT INTO solicitud_baqueanos (
         usuario_id, 
         sb_tipo_documento, 
         sb_numero_identidad,
         sb_baqueano_nombre, 
         sb_baqueano_apellido, 
         sb_telefono_baqueano, 
         sb_correo_baqueano, 
         sb_direccion,
         sb_cuenta, 
         sb_tipo_cuenta, 
         sb_num_cuenta,
         sb_titular, 
         sb_year,
         sb_fecha_inicio,
         sb_fecha_fin, 
         sb_dias_calculados,
         sb_cobro_diario,
         sb_valor_cobrar, 
         sb_unidad_intervencion,
         sb_unidad_operativa,
         sb_tipo_unidad,
         sb_municipio,
         sb_vereda,
         sb_tipo_actividad,
         sb_coordinador ,
         sb_lider_cuadrilla,
         sb_transporte,
         sb_porque_transporte,
         sb_hospedaje,
         sb_porque_hospedaje,
         sb_estado_lider,
         sb_estado_profesional,
         sb_estado_operaciones,
         sb_estado_gerencia,
         sb_estado_pagos,
         sb_cuanto_transporte,
         sb_cuanto_hospedaje,
         sb_profesional_baqueano,
         sb_reconocedor
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        die(json_encode(['success' => false, 'message' => "Error en la consulta: " . $mysqli->error]));
    }

    $types = "i" . str_repeat("s", 38);
    $stmt->bind_param(
        $types,
        $usuario_id,
        $sb_tipo_documento,
        $sb_numero_identidad,
        $sb_baqueano_nombre,
        $sb_baqueano_apellido,
        $sb_telefono_baqueano,
        $sb_correo_baqueano,
        $sb_direccion,
        $sb_cuenta,
        $sb_tipo_cuenta,
        $sb_num_cuenta,
        $sb_titular,
        $sb_year,
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
        $sb_hospedaje,
        $sb_porque_hospedaje,
        $sb_estado_lider,
        $sb_estado_profesional,
        $sb_estado_operaciones,
        $sb_estado_gerencia,
        $sb_estado_pagos,
        $sb_cuanto_transporte,
        $sb_cuanto_hospedaje,
        $sb_profesional_baqueano,
        $sb_reconocedor
    );
    if ($stmt->execute()) {

        $idSolicitudNueva = $mysqli->insert_id;

        $stmt->close();

        $tipo    = "SOLICITUD_BAQUEANOS";
        $mensaje = "Nueva solicitud ARB_{$idSolicitudNueva} creada y pendiente de revisión.";

        insertarNotificacionPorRol($mysqli, "lider_reconocimiento", (int)$idSolicitudNueva, $tipo, $mensaje);

        $_SESSION['swal_success'] = true;

        header("Location: /arbimaps/Arbimaps/index.php?page=baqueanos/solicitudes/vistas/solicitudes_baqueanos");
        exit;


        $sqlCopia = "INSERT INTO sb_copia (
            copia_sb_numero_identidad, 
            copia_sb_year,
            copia_sb_fecha_inicio, 
            copia_sb_fecha_fin,
            copia_sb_dias_calculados, 
            copia_sb_cobro_diario, 
            copia_sb_valor_cobrar,
            copia_sb_unidad_intervencion, 
            copia_sb_unidad_operativa, 
            copia_sb_tipo_unidad,
            copia_sb_municipio,
            copia_sb_vereda, 
            copia_sb_tipo_actividad,
            copia_sb_coordinador, 
            copia_sb_lider_cuadrilla,
            copia_sb_transporte,
            copia_sb_porque_transporte,
            copia_sb_hospedaje, 
            copia_sb_porque_hospedaje,
            copia_sb_cuanto_transporte,
            copia_sb_cuanto_hospedaje,
            copia_sb_profesional_baqueano
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmtCopia = $mysqli->prepare($sqlCopia);

        if ($stmtCopia) {
            // Asignar valores
            $copia_sb_numero_identidad      = $sb_numero_identidad;
            $copia_sb_year                  = $sb_year;
            $copia_sb_fecha_inicio          = $sb_fecha_inicio;
            $copia_sb_fecha_fin             = $sb_fecha_fin;
            $copia_sb_dias_calculados       = $sb_dias_calculados;
            $copia_sb_cobro_diario          = $sb_cobro_diario;
            $copia_sb_valor_cobrar          = $sb_valor_cobrar;
            $copia_sb_unidad_intervencion   = $sb_unidad_intervencion;
            $copia_sb_unidad_operativa      = $sb_unidad_operativa;
            $copia_sb_tipo_unidad           = $sb_tipo_unidad;
            $copia_sb_municipio             = $sb_municipio;
            $copia_sb_vereda                = $sb_vereda;
            $copia_sb_tipo_actividad        = $sb_tipo_actividad;
            $copia_sb_coordinador           = $sb_coordinador;
            $copia_sb_lider_cuadrilla       = $sb_lider_cuadrilla;
            $copia_sb_transporte            = $sb_transporte;
            $copia_sb_porque_transporte     = $sb_porque_transporte;
            $copia_sb_hospedaje             = $sb_hospedaje;
            $copia_sb_porque_hospedaje      = $sb_porque_hospedaje;
            $copia_sb_cuanto_transporte     = $sb_cuanto_transporte;
            $copia_sb_cuanto_hospedaje      = $sb_cuanto_hospedaje;
            $copia_sb_profesional_baqueano  = $sb_profesional_baqueano;

            $typesCopia = str_repeat("s", 22);

            $stmtCopia->bind_param(
                $typesCopia,
                $copia_sb_numero_identidad,
                $copia_sb_year,
                $copia_sb_fecha_inicio,
                $copia_sb_fecha_fin,
                $copia_sb_dias_calculados,
                $copia_sb_cobro_diario,
                $copia_sb_valor_cobrar,
                $copia_sb_unidad_intervencion,
                $copia_sb_unidad_operativa,
                $copia_sb_tipo_unidad,
                $copia_sb_municipio,
                $copia_sb_vereda,
                $copia_sb_tipo_actividad,
                $copia_sb_coordinador,
                $copia_sb_lider_cuadrilla,
                $copia_sb_transporte,
                $copia_sb_porque_transporte,
                $copia_sb_hospedaje,
                $copia_sb_porque_hospedaje,
                $copia_sb_cuanto_transporte,
                $copia_sb_cuanto_hospedaje,
                $copia_sb_profesional_baqueano
            );
            $stmtCopia->execute();
            $stmtCopia->close();
        }

        $_SESSION['swal_success'] = true;

        header("Location: /arbimaps/Arbimaps/index.php?page=baqueanos/solicitudes/vistas/solicitudes_baqueanos");
        exit;
    } else {
        $_SESSION['swal_error'] = 'Error al crear la solicitud. Intente nuevamente.';
        header("Location: /arbimaps/Arbimaps/index.php?page=baqueanos/solicitudes/vistas/solicitudes_baqueanos");
        exit;
        $stmt->close();
    }
}

$mysqli->close();
