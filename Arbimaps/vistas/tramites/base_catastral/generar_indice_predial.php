<?php
// Ejecutar una vez cuando cambien los CSV oficiales.
// Genera un indice por numero predial para evitar recorrer CSV completos por cada radicado.

require_once __DIR__ . '/base_catastral_lib.php';

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

set_time_limit(0);
ini_set('memory_limit', '1024M');

function bci_read_csv_grouped(string $csvPath, string $registro): array
{
    $handle = fopen($csvPath, 'r');
    if (!$handle) {
        throw new RuntimeException("No se pudo abrir $csvPath");
    }

    $headerRow = fgetcsv($handle, 0, ';');
    if (!is_array($headerRow)) {
        fclose($handle);
        throw new RuntimeException("No se pudo leer encabezado de $csvPath");
    }

    $headers = [];
    foreach ($headerRow as $index => $header) {
        $col = Coordinate::stringFromColumnIndex($index + 1);
        $headers[$col] = bc_normalize_value($header) ?: 'Columna ' . $col;
    }

    $grouped = [];
    $lineNumber = 1;
    while (($data = fgetcsv($handle, 0, ';')) !== false) {
        $lineNumber++;
        $numeroPredial = bc_normalize_value($data[2] ?? '');
        if ($numeroPredial === '') {
            continue;
        }

        $values = [];
        foreach (array_keys($headers) as $index => $col) {
            $values[$col] = bc_normalize_value($data[$index] ?? '');
        }

        if (!isset($grouped[$numeroPredial])) {
            $grouped[$numeroPredial] = [
                'headers' => $headers,
                'rows' => [],
            ];
        }

        $grouped[$numeroPredial]['rows'][] = [
            'row_number' => $lineNumber,
            'values' => $values,
        ];
    }

    fclose($handle);
    return $grouped;
}

$root = bc_root_path();
$csv1 = $root . '/' . BC_REGISTRO1_CSV;
$csv2 = $root . '/' . BC_REGISTRO2_CSV;
$outDir = $root . '/dat_neiva/indice_predial';

if (!is_dir($outDir)) {
    mkdir($outDir, 0777, true);
}

$registro1 = bci_read_csv_grouped($csv1, 'registro1');
$registro2 = bci_read_csv_grouped($csv2, 'registro2');
$predios = array_unique(array_merge(array_keys($registro1), array_keys($registro2)));
sort($predios);

$meta = [
    'version' => 'indice-predial-v1',
    'generated_at' => date('Y-m-d H:i:s'),
    'registro1_csv' => basename($csv1),
    'registro2_csv' => basename($csv2),
    'total_predios' => count($predios),
];

file_put_contents($outDir . '/metadata.json', json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

$count = 0;
foreach ($predios as $numeroPredial) {
    $prefix = substr($numeroPredial, 0, 6) ?: 'sin_id';
    $bucketDir = $outDir . '/' . $prefix;
    if (!is_dir($bucketDir)) {
        mkdir($bucketDir, 0777, true);
    }

    $payload = [
        'numero_predial' => $numeroPredial,
        'registro1' => $registro1[$numeroPredial] ?? ['headers' => [], 'rows' => []],
        'registro2' => $registro2[$numeroPredial] ?? ['headers' => [], 'rows' => []],
    ];

    file_put_contents($bucketDir . '/' . $numeroPredial . '.json', json_encode($payload, JSON_UNESCAPED_UNICODE));
    $count++;
}

echo "Indice generado en $outDir con $count predios.\n";
