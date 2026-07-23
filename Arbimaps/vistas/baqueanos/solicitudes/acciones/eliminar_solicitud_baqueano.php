<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../../../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php");
    exit();
}

$id = $_POST['id'] ?? null;
$razon = $_POST['sb_razon_baqueano'] ?? null;

if (!$id || !$razon) {
    die("Datos incompletos");
}

$mysqli->begin_transaction();

try {
    $sqlSelect = "SELECT * FROM solicitud_baqueanos WHERE id = ?";
    $stmtSelect = $mysqli->prepare($sqlSelect);

    if (!$stmtSelect) {
        throw new Exception($mysqli->error);
    }

    $stmtSelect->bind_param("i", $id);
    $stmtSelect->execute();
    $result = $stmtSelect->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Solicitud no encontrada");
    }

    $row = $result->fetch_assoc();
    $stmtSelect->close();

    $sqlInsert = "
        INSERT INTO solicitudes_canceladas (
            sc_tipo_documento, sc_numero_identidad, sc_baqueano_nombre, sc_baqueano_apellido,
            sc_telefono_baqueano, sc_correo_baqueano, sc_direccion, sc_cuenta,
            sc_tipo_cuenta, sc_num_cuenta, sc_titular, sc_fecha_inicio, sc_fecha_fin,
            sc_dias_calculados, sc_cobro_diario, sc_valor_cobrar,
            sc_unidad_intervencion, sc_unidad_operativa, sc_tipo_unidad,
            sc_municipio, sc_vereda, sc_tipo_actividad, sc_lider_cuadrilla,
            sc_transporte, sc_hospedaje, sc_porque_hospedaje, sc_porque_transporte
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ";

    $stmtInsert = $mysqli->prepare($sqlInsert);

    if (!$stmtInsert) {
        throw new Exception($mysqli->error);
    }

    $stmtInsert->bind_param(
        "ssssssssssssiddssssssssssss",
        $row['sb_tipo_documento'],
        $row['sb_numero_identidad'],
        $row['sb_baqueano_nombre'],
        $row['sb_baqueano_apellido'],
        $row['sb_telefono_baqueano'],
        $row['sb_correo_baqueano'],
        $row['sb_direccion'],
        $row['sb_cuenta'],
        $row['sb_tipo_cuenta'],
        $row['sb_num_cuenta'],
        $row['sb_titular'],
        $row['sb_fecha_inicio'],
        $row['sb_fecha_fin'],
        $row['sb_dias_calculados'],
        $row['sb_cobro_diario'],
        $row['sb_valor_cobrar'],
        $row['sb_unidad_intervencion'],
        $row['sb_unidad_operativa'],
        $row['sb_tipo_unidad'],
        $row['sb_municipio'],
        $row['sb_vereda'],
        $row['sb_tipo_actividad'],
        $row['sb_lider_cuadrilla'],
        $row['sb_transporte'],
        $row['sb_hospedaje'],
        $row['sb_porque_hospedaje'],
        $row['sb_porque_transporte']
    );

    $stmtInsert->execute();
    $stmtInsert->close();

    $sqlDelete  = "DELETE FROM solicitud_baqueanos WHERE id = ?";
    $stmtDelete = $mysqli->prepare($sqlDelete);

    if (!$stmtDelete) {
        throw new Exception($mysqli->error);
    }

    $stmtDelete->bind_param("i", $id);
    $stmtDelete->execute();
    $stmtDelete->close();
    $mysqli->commit();

    header("Location: /arbimaps/Arbimaps/index.php?page=baqueanos/solicitudes/vistas/detalles_solicitudes");
    exit();

} catch (Exception $e) {
    $mysqli->rollback();
    die("Error al cancelar la solicitud: " . $e->getMessage());
}
