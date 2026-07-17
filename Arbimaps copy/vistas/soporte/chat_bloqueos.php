<?php
require $_SERVER['DOCUMENT_ROOT'] . '/arbimaps/conexion.php';

header('Content-Type: application/json; charset=utf-8');

$lista = isset($_GET['codigos']) ? trim($_GET['codigos']) : '';
if ($lista === '') {
    echo json_encode(new stdClass());
    exit;
}

$codigos = explode(',', $lista);
$codigos_limpios = [];

foreach ($codigos as $c) {
    $c = trim($c);

    if ($c !== '' && preg_match('/^[A-Za-z0-9\-_]+$/', $c)) {
        $codigos_limpios[] = $c;
    }
}

if (count($codigos_limpios) === 0) {
    echo json_encode(new stdClass());
    exit;
}

$salida = [];

foreach ($codigos_limpios as $c) {
    $salida[$c] = ['chat_bloqueado' => 0];
}

$placeholders = implode(',', array_fill(0, count($codigos_limpios), '?'));
$sql = "SELECT codigo_error, chat_bloqueado
        FROM solicitud_soporte
        WHERE codigo_error IN ($placeholders)";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    echo json_encode($salida);
    exit;
}

$tipos = str_repeat('s', count($codigos_limpios));
$stmt->bind_param($tipos, ...$codigos_limpios);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $codigo = $row['codigo_error'];
        $bloq = (int)($row['chat_bloqueado'] ?? 0);
        $salida[$codigo] = ['chat_bloqueado' => $bloq];
    }
}

$stmt->close();

echo json_encode($salida);
exit;
