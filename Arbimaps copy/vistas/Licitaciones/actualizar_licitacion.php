<?php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) session_start();
require __DIR__ . '/../../../conexion.php';

try {
    $radicado = $_POST['lc_radicado'] ?? '';
    if ($radicado === '') {
        echo json_encode(['success'=>false,'message'=>'Radicado inválido']);
        exit;
    }

    // Campos a actualizar
    $lc_tipo_entidad       = $_POST['lc_tipo_entidad'] ?? '';
    $lc_procesos           = $_POST['lc_procesos'] ?? '';
    $lc__municipio         = $_POST['lc__municipio'] ?? '';
    $lc_departamento       = $_POST['lc_departamento'] ?? '';
    $lc_entidad            = $_POST['lc_entidad'] ?? '';
    $lc_valor              = $_POST['lc_valor'] ?? '';
    $lc_numero_proceso     = $_POST['lc_numero_proceso'] ?? '';
    $lc_nombre_licitacion  = $_POST['lc_nombre_licitacion'] ?? '';
    $lc_proyecto           = $_POST['lc_proyecto'] ?? '';
    $lc_fecha_apertura     = $_POST['lc_fecha_apertura'] ?? '';
    $lc_fecha_presentacion = $_POST['lc_fecha_presentacion'] ?? '';

    $sql = "UPDATE licitaciones
            SET
                lc_tipo_entidad = ?,
                lc_procesos = ?,
                lc__municipio = ?,
                lc_departamento = ?,
                lc_entidad = ?,
                lc_valor = ?,
                lc_numero_proceso = ?,
                lc_nombre_licitacion = ?,
                lc_proyecto = ?,
                lc_fecha_apertura = ?,
                lc_fecha_presentacion = ?
            WHERE lc_radicado = ?
            LIMIT 1";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success'=>false,'message'=>'Error prepare: '.$mysqli->error]);
        exit;
    }

    $stmt->bind_param(
        "ssssssssssss",
        $lc_tipo_entidad,
        $lc_procesos,
        $lc__municipio,
        $lc_departamento,
        $lc_entidad,
        $lc_valor,
        $lc_numero_proceso,
        $lc_nombre_licitacion,
        $lc_proyecto,
        $lc_fecha_apertura,
        $lc_fecha_presentacion,
        $radicado
    );

    if ($stmt->execute()) {
        echo json_encode(['success'=>true,'message'=>'Licitación actualizada correctamente']);
    } else {
        echo json_encode(['success'=>false,'message'=>'Error execute: '.$stmt->error]);
    }

    $stmt->close();

} catch (Throwable $e) {
    echo json_encode(['success'=>false,'message'=>'Excepción: '.$e->getMessage()]);
}
