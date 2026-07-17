<?php
require $_SERVER['DOCUMENT_ROOT'] . '/arbimaps/conexion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id_usuario_sesion = $_SESSION['id_usuario'] ?? null;

header('Content-Type: application/json; charset=utf-8');

if (!$id_usuario_sesion) {
    echo json_encode([]);
    exit;
}

$codigos_param = isset($_GET['codigos']) ? trim($_GET['codigos']) : '';

if ($codigos_param === '') {
    echo json_encode([]);
    exit;
}

$codigos_array = array_filter(array_map('trim', explode(',', $codigos_param)));
if (empty($codigos_array)) {
    echo json_encode([]);
    exit;
}

$codigos_seguro = [];
foreach ($codigos_array as $c) {
    $codigos_seguro[] = "'" . $mysqli->real_escape_string($c) . "'";
}
$lista_codigos = implode(',', $codigos_seguro);

$respuesta = [];

$sql = "
    SELECT c.codigo_error, c.tipo_remitente
    FROM soporte_chat c
    INNER JOIN (
        SELECT codigo_error, MAX(fecha_envio) AS max_fecha
        FROM soporte_chat
        WHERE codigo_error IN ($lista_codigos)
        GROUP BY codigo_error
    ) ult ON ult.codigo_error = c.codigo_error
         AND ult.max_fecha    = c.fecha_envio
";

$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $codigo = $row['codigo_error'];
        $tipo   = strtoupper($row['tipo_remitente'] ?? '');

        $respuesta[$codigo] = [
            'ultimo_tipo' => $tipo,
            'tiene_nuevo' => ($tipo === 'CLIENTE')
        ];
    }
}

echo json_encode($respuesta);
exit;
