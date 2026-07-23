<?php
// ... código anterior ...

$stmt->execute();
$stmt->close();

// ========================
// INSERTAR EN estados_tramite
// ========================
$sql_estado = "INSERT INTO estados_tramite (
    es_nombre,
    es_tipo,
    es_descripcion,
    es_dias_disparador,
    es_rol_asociado,
    estado,
    asignacion_id,
    cod_tramite
) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt_estado = $conn->prepare($sql_estado);
if (!$stmt_estado) {
    die("Error en prepare estados_tramite: " . $conn->error);
}

$es_nombre = 'DEVUELTO';
$es_tipo = 'automatico';
$es_descripcion = "Trámite devuelto por $rol_actual";
$es_dias_disparador = 5;
$estado = 'ACTIVO';

$stmt_estado->bind_param(
    "sssisisi",
    $es_nombre,
    $es_tipo,
    $es_descripcion,
    $es_dias_disparador,
    $rol_actual,
    $estado,
    $id_tramite_fk,
    $entrega_cod_tramite
);

if (!$stmt_estado->execute()) {
    die("Error al insertar en estados_tramite: " . $stmt_estado->error);
}
$stmt_estado->close();

// ... resto del código ...