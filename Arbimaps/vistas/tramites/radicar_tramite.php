<?php
header('Content-Type: application/json');

require_once dirname(__DIR__, 3) . '/conexion.php';

$municipio = $_GET['municipio'] ?? '';

// =================================================
// CONSULTA ÚNICA NORMALIZADA
// =================================================
$sql = "
    SELECT 
        CASE 
            WHEN mutacion_tramite IS NULL OR TRIM(mutacion_tramite) = '' 
                THEN 'OTRO'
            ELSE UPPER(TRIM(mutacion_tramite))
        END AS tipo,
        COUNT(*) AS total
    FROM tramite_radicacion
";

if ($municipio !== '') {
    $sql .= " WHERE UPPER(TRIM(municipio_rad)) = UPPER(TRIM(?))";
}

$sql .= " GROUP BY tipo";

$stmt = $mysqli->prepare($sql);
if ($municipio !== '') {
    $stmt->bind_param("s", $municipio);
}

$stmt->execute();
$result = $stmt->get_result();

// =================================================
// MAPA RESULTADOS
// =================================================
$mapa = [];
while ($row = $result->fetch_assoc()) {
    $mapa[$row['tipo']] = (int)$row['total'];
}

// =================================================
// ORDEN DE LA TORTA
// =================================================
$tiposTorta = [
    'MUTACION_5',
    'MUTACION_4',
    'MUTACION_3',
    'MUTACION_2',
    'MUTACION_1',
    'CANCELACION',
    'COMPLEMENTACION',
    'PETICION',
    'RECLAMO',
    'QUEJA',
    'SOLICITUD',
    'OTRO'
];

$labelsTorta = [];
$datosTorta  = [];

foreach ($tiposTorta as $tipo) {
    $labelsTorta[] = $tipo;
    $datosTorta[]  = $mapa[$tipo] ?? 0;
}

$labelsBarra = [];
$datosBarra  = [];

foreach ($tiposTorta as $tipo) {
    $labelsBarra[] = $tipo;
    $datosBarra[]  = $mapa[$tipo] ?? 0;
}

// =================================================
// RESPUESTA JSON
// =================================================
echo json_encode([
    'torta' => [
        'labels' => $labelsTorta,
        'datos'  => $datosTorta
    ],
    'barra' => [
        'labels' => $labelsBarra,
        'datos'  => $datosBarra
    ]
]);
