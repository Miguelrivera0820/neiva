<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conexion_path = __DIR__ . '/../../../../../conexion.php';
if (file_exists($conexion_path)) {
    require_once $conexion_path;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $Periodo_Facturacion = $_POST['Periodo_Facturacion'] ?? null;
    $Periodo_Facturacion = $Periodo_Facturacion ? strtoupper(trim($Periodo_Facturacion)) : null;
    $tipo_documento             = $_POST['Documento'] ?? null;
    $numero_identidad           = $_POST['numero_identidad'] ?? null;
    $primer_nombre              = $_POST['primer_nombre'] ?? null;
    $segundo_nombre             = $_POST['segundo_nombre'] ?? null;
    $primer_apellido            = $_POST['primer_apellido'] ?? null;
    $segundo_apellido           = $_POST['segundo_apellido'] ?? null;
    $telefono                   = $_POST['telefono'] ?? null;
    $correo                     = $_POST['correo'] ?? null;
    $Fecha_Inicio               = $_POST['Fecha_Inicio'] ?? null;
    $Fecha_Final                = $_POST['Fecha_Final'] ?? null;
    $Periodo_Facturacion        = $_POST['Periodo_Facturacion'] ?? null;

    $anio_cuenta = isset($_POST['anio_cuenta']) ? (int)$_POST['anio_cuenta'] : null;
    if (!$anio_cuenta || $anio_cuenta < 1900 || $anio_cuenta > 2100) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Año inválido. Debe estar entre 1900 y 2100.'
        ]);
        exit;
    }

    $valor = $_POST['valor'] ?? null;
    if (!is_null($valor)) {
        $valor = preg_replace('/\D/', '', $valor);
        $valor = (int)$valor;
    } else {
        $valor = 0;
    }
    if ($valor >= 1750905) {
        $Val_Seg_Social = (int) round($valor * 0.40);
    } else {
        $Val_Seg_Social = 0; 
    }


    $cant_dias                  = $_POST['cant_dias'] ?? null;
    $cargo                      = $_POST['cargo'] ?? null;
    $proyecto                   = $_POST['proyecto'] ?? null;
    $observacion                = $_POST['observacion'] ?? null;
    $usuario_id                 = $_SESSION['id_usuario'] ?? null;

    $informe_mensual            = $_FILES['informe_mensual']['name'] ?? null;
    $cuenta_cobro               = $_FILES['cuenta_cobro']['name'] ?? null;
    $seguridad_social           = $_FILES['seguridad_social']['name'] ?? null;
    $retencion                  = $_FILES['retencion']['name'] ?? null;
    $primera_vez                = $_FILES['primera_vez']['name'] ?? null;

    $año_actual = (string)$anio_cuenta;

    $base_upload_dir = '../../../../DOCUMENTOS/modelo_de_cuenta/';

    // ---- tu validación de periodo futuro (se deja igual) ----
    if (!empty($Periodo_Facturacion)) {

        $periodo        = trim($Periodo_Facturacion);
        $periodo_upper  = strtoupper($periodo);
        $mes_num        = null;
        $anio_periodo   = (int)$año_actual;
        $mes_actual     = (int)date('m');
        $anio_actual_int = (int)date('Y');
        $anio_from_fecha = null;

        if (!empty($Fecha_Inicio)) {
            $ts = strtotime($Fecha_Inicio);
            if ($ts !== false) $anio_from_fecha = (int)date('Y', $ts);
        }
        if ($anio_from_fecha === null && !empty($Fecha_Final)) {
            $ts2 = strtotime($Fecha_Final);
            if ($ts2 !== false) $anio_from_fecha = (int)date('Y', $ts2);
        }

        if (preg_match('/^(\d{4})-(\d{2})$/', $periodo, $m)) {
            $anio_periodo = (int)$m[1];
            $mes_num = (int)$m[2];
        } elseif (preg_match('/^(\d{1,2})$/', $periodo, $m)) {
            $mes_num = (int)$m[1];
            if ($anio_from_fecha !== null) {
                $anio_periodo = $anio_from_fecha;
            } else {
                if ($mes_num > $mes_actual) {
                    $anio_periodo = $anio_actual_int - 1;
                } else {
                    $anio_periodo = $anio_actual_int;
                }
            }
        } else {
            $meses = [
                'ENERO' => 1,
                'FEBRERO' => 2,
                'MARZO' => 3,
                'ABRIL' => 4,
                'MAYO' => 5,
                'JUNIO' => 6,
                'JULIO' => 7,
                'AGOSTO' => 8,
                'SEPTIEMBRE' => 9,
                'SETIEMBRE' => 9,
                'OCTUBRE' => 10,
                'NOVIEMBRE' => 11,
                'DICIEMBRE' => 12,
            ];

            if (isset($meses[$periodo_upper])) {
                $mes_num = $meses[$periodo_upper];
                if ($anio_from_fecha !== null) {
                    $anio_periodo = $anio_from_fecha;
                } else {
                    if ($mes_num > $mes_actual) {
                        $anio_periodo = $anio_actual_int - 1;
                    } else {
                        $anio_periodo = $anio_actual_int;
                    }
                }
            }
        }

        if ($mes_num !== null && $mes_num >= 1 && $mes_num <= 12) {
            $periodo_ts = strtotime(sprintf('%04d-%02d-01', $anio_periodo, $mes_num));
            $actual_ts = strtotime(date('Y-m-01'));

            if ($periodo_ts > $actual_ts) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Debe pedir autorizacion para radicar cuentas en periodos futuros.'
                ]);
                exit;
            }
        }
    }
    // ---- fin validación periodo ----

    if (!file_exists($base_upload_dir)) {
        mkdir($base_upload_dir, 0777, true);
    }

    // ahora la carpeta va por el año que escribió el usuario
    $usuario_dir = $base_upload_dir . $año_actual . '/' . ($Periodo_Facturacion ?: 'periodo') . '/' . ($numero_identidad ?: 'sin_identidad') . '/';
    if (!file_exists($usuario_dir)) mkdir($usuario_dir, 0777, true);

    $sub_dir_map = [
        'informe_mensual'   => 'informe_mensual',
        'cuenta_cobro'      => 'cuenta_de_cobro',
        'seguridad_social'  => 'planilla_comprobante',
        'retencion'         => 'retencion_de_la_fuente',
        'primera_vez'       => 'primera_vez'
    ];

    foreach ($sub_dir_map as $file_key => $dir_name) {
        if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === 0) {
            $upload_dir = $usuario_dir . $dir_name . '/';
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
            $file_name = $_FILES[$file_key]['name'];
            move_uploaded_file($_FILES[$file_key]['tmp_name'], $upload_dir . $file_name);
        }
    }

    $sql = "INSERT INTO cuenta 
        (
            tipo_documento, 
            numero_identidad, 
            primer_nombre, 
            segundo_nombre, 
            primer_apellido,
            segundo_apellido, 
            telefono, 
            correo, 
            Fecha_Inicio, 
            Fecha_Final, 
            Periodo_Facturacion, 
            anio_cuenta,
            valor, 
            Val_Seg_Social, 
            cant_dias,
            cargo, 
            proyecto, 
            observacion, 
            informe_mensual, 
            cuenta_cobro, 
            seguridad_social, 
            retencion, 
            primera_vez, 
            usuario_id
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $db = $mysqli ?? ($conn ?? null);
    if (!$db) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: conexión a base de datos no encontrada.']);
        exit;
    }

    // Validar duplicado en cuenta
    $sql_check = "SELECT COUNT(*) 
              FROM cuenta 
              WHERE numero_identidad = ? 
                AND Periodo_Facturacion = ?
                AND anio_cuenta = ?";

    $stmt_check = $db->prepare($sql_check);
    if (!$stmt_check) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error al preparar validación de cuenta: ' . (method_exists($db, 'error') ? $db->error : 'desconocido')
        ]);
        exit;
    }

    $stmt_check->bind_param("ssi", $numero_identidad, $Periodo_Facturacion, $anio_cuenta);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count > 0) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error: Ya existe una cuenta con este número de identidad, este periodo y este año.'
        ]);
        exit;
    }

    // Validar duplicado en cuentas_pagadas
    $sql_check_pagadas = "
    SELECT COUNT(*) 
    FROM cuentas_pagadas
    WHERE pag_numero_identidad = ? 
      AND pag_Periodo_Facturacion = ?
      AND pag_anio_cuenta = ?
";

    $stmt_check_pagadas = $db->prepare($sql_check_pagadas);
    if (!$stmt_check_pagadas) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error al preparar validación de cuentas pagadas: ' . (method_exists($db, 'error') ? $db->error : 'desconocido')
        ]);
        exit;
    }

    $stmt_check_pagadas->bind_param("ssi", $numero_identidad, $Periodo_Facturacion, $anio_cuenta);
    $stmt_check_pagadas->execute();
    $stmt_check_pagadas->bind_result($count_pagadas);
    $stmt_check_pagadas->fetch();
    $stmt_check_pagadas->close();

    if ($count_pagadas > 0) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error: Ya existe una cuenta pagada con este número de identidad, este periodo y este año.'
        ]);
        exit;
    }

    $stmt = $db->prepare($sql);
    if (!$stmt) {
        header('Content-Type: application/json');
        $dbErr = method_exists($db, 'error') ? $db->error : 'unknown';
        echo json_encode(['success' => false, 'message' => 'Error preparar INSERT: ' . $dbErr]);
        exit;
    }

    $stmt->bind_param(
        "sssssssssssssssssssssssi",
        $tipo_documento,
        $numero_identidad,
        $primer_nombre,
        $segundo_nombre,
        $primer_apellido,
        $segundo_apellido,
        $telefono,
        $correo,
        $Fecha_Inicio,
        $Fecha_Final,
        $Periodo_Facturacion,
        $anio_cuenta,
        $valor,
        $Val_Seg_Social,
        $cant_dias,
        $cargo,
        $proyecto,
        $observacion,
        $informe_mensual,
        $cuenta_cobro,
        $seguridad_social,
        $retencion,
        $primera_vez,
        $usuario_id
    );

    if ($stmt->execute()) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Cuenta creada con éxito.',
            'redirect' => neiva_app_url('Arbimaps/index.php?page=cuentas/mis_cuentas')
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error ejecutar INSERT: ' . $stmt->error]);
    }

    $stmt->close();
}

if (isset($mysqli) && is_object($mysqli)) {
    @$mysqli->close();
} elseif (isset($conn) && is_object($conn)) {
    @$conn->close();
}
