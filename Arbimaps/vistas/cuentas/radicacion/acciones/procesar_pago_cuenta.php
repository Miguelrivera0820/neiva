<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../../../../conexion.php'; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}

$id               = isset   ($_POST['id']) ? (int)$_POST['id'] : 0;
$numero_identidad = trim    ($_POST['numero_identidad'] ?? '');

if (!$id || $numero_identidad === '') {
    $url = neiva_app_url('Arbimaps/index.php?page=cuentas/imprimir_cuentas')
        . '&msg=' . urlencode('Datos incompletos para marcar la cuenta como pagada.');
    header('Location: ' . $url);
    exit;
}

try {
    $mysqli->begin_transaction();

    $sql_update = "
        UPDATE cuenta
            SET pagado = 'Pagado'
        WHERE id = ? AND numero_identidad = ?
    ";
    $stmt_update = $mysqli->prepare($sql_update);
    if (!$stmt_update) {
        throw new Exception('Error al preparar UPDATE: ' . $mysqli->error);
    }

    $stmt_update->bind_param("is", $id, $numero_identidad);
    if (!$stmt_update->execute()) {
        throw new Exception('Error al ejecutar UPDATE: ' . $stmt_update->error);
    }
    $stmt_update->close();

    $sql_select = "SELECT * FROM cuenta WHERE id = ? AND numero_identidad = ?";
    $stmt_select = $mysqli->prepare($sql_select);
    if (!$stmt_select) {
        throw new Exception('Error al preparar SELECT: ' . $mysqli->error);
    }

    $stmt_select->bind_param("is", $id, $numero_identidad);
    $stmt_select->execute();
    $result = $stmt_select->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('La cuenta no existe.');
    }

    $cuenta = $result->fetch_assoc();
    $stmt_select->close();

    $mapeo_columnas = [
        "tipo_documento"          => "pag_tipo_documento",
        "numero_identidad"        => "pag_numero_identidad",
        "primer_nombre"           => "pag_primer_nombre",
        "segundo_nombre"          => "pag_segundo_nombre",
        "primer_apellido"         => "pag_primer_apellido",
        "segundo_apellido"        => "pag_segundo_apellido",
        "telefono"                => "pag_telefono",
        "correo"                  => "pag_correo",
        "Fecha_Inicio"            => "pag_Fecha_Inicio",
        "Fecha_Final"             => "pag_Fecha_Final",
        "Periodo_Facturacion"     => "pag_Periodo_Facturacion",
        "anio_cuenta"             => "pag_anio_cuenta",
        "valor"                   => "pag_valor",
        "Val_Seg_Social"          => "pag_Val_Seg_Social",
        "cant_dias"               => "pag_cant_dias",
        "cargo"                   => "pag_cargo",
        "proyecto"                => "pag_proyecto",
        "observacion"             => "pag_observacion",
        "informe_mensual"         => "pag_informe_mensual",
        "cuenta_cobro"            => "pag_cuenta_cobro",
        "retencion"               => "pag_retencion",
        "primera_vez"             => "pag_primera_vez",
        "seguridad_social"        => "pag_seguridad_social",
        "creado_en"               => "pag_creado_en",
        "estado"                  => "pag_estado",
        "estado_seguridad_social" => "pag_estado_seguridad_social",
        "estado_final"            => "pag_estado_final",
        "fecha_subida"            => "pag_fecha_subida",
        "usuario_id"              => "pag_usuario_id",
        "valor_aprobado"          => "pag_valor_aprobado",
        "pagado"                  => "pag_pagado",
        "fecha_aprobado"          => "pag_fecha_aprobado"
    ];

    $columnas_pagadas = array_values($mapeo_columnas);
    $valores          = [];

    foreach ($mapeo_columnas as $columna_origen => $columna_destino) {
        $valores[] = isset($cuenta[$columna_origen]) ? $cuenta[$columna_origen] : 'Desconocido';
    }

    foreach ($valores as $index => $valor) {
        if ($valor === NULL && $columnas_pagadas[$index] === "pag_pagado") {
            $valores[$index] = "Pendiente";
        }
    }

    $placeholders = implode(', ', array_fill(0, count($valores), '?'));
    $columnas_sql = implode(', ', $columnas_pagadas);
    $sql_insert   = "INSERT INTO cuentas_pagadas ($columnas_sql) VALUES ($placeholders)";

    $stmt_insert = $mysqli->prepare($sql_insert);
    if (!$stmt_insert) {
        throw new Exception('Error al preparar INSERT: ' . $mysqli->error);
    }

    $tipos = str_repeat('s', count($valores));
    $stmt_insert->bind_param($tipos, ...$valores);

    if (!$stmt_insert->execute()) {
        throw new Exception('Error al ejecutar INSERT: ' . $stmt_insert->error);
    }
    $stmt_insert->close();

    $sql_delete = "DELETE FROM cuenta WHERE id = ? AND numero_identidad = ?";
    $stmt_delete = $mysqli->prepare($sql_delete);
    if (!$stmt_delete) {
        throw new Exception('Error al preparar DELETE: ' . $mysqli->error);
    }
    $stmt_delete->bind_param("is", $id, $numero_identidad);

    if (!$stmt_delete->execute()) {
        throw new Exception('Error al ejecutar DELETE: ' . $stmt_delete->error);
    }
    $stmt_delete->close();

    $mysqli->commit();

    $msg = 'Cuenta pagada y movida a cuentas_pagadas correctamente.';

} catch (Exception $e) {
    if ($mysqli->errno === 0) {
    } else {
        $mysqli->rollback();
    }
    $msg = 'Error al procesar el pago: ' . $e->getMessage();
}

$url = neiva_app_url('Arbimaps/index.php?page=cuentas/cuentas_aprobadas')
    . '&msg=' . urlencode($msg);

header('Location: ' . $url);
exit;
