<?php

require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 4) . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

const BC_REGISTRO1 = 'dat_neiva/CATASTRAL_REGISTRO1_20250505.xlsx';
const BC_REGISTRO2 = 'dat_neiva/CATASTRAL_REGISTRO2_20250505.xlsx';
const BC_REGISTRO1_CSV = 'dat_neiva/CATASTRAL_REGISTRO1_20250505.csv';
const BC_REGISTRO2_CSV = 'dat_neiva/CATASTRAL_REGISTRO2_20250505.csv';
const BC_INDICE_PREDIAL_DIR = 'dat_neiva/indice_predial';
const BC_PREDIOS_JSON = 'dat_neiva/predios.json';
const BC_PREDIOS_JSON_BACKUP_DIR = 'dat_neiva/backups/predios_json';
const BC_FULL_BACKUP_DIR = 'dat_neiva/backups/base_catastral_diario';
const BC_TEMP_FILE = 'propuesta_mutacion.xlsx';
const BC_JSON_FILE = 'propuesta_mutacion.json';

function bc_root_path(): string
{
    return dirname(__DIR__, 4);
}

function bc_private_dataset_path(string $fileName): string
{
    return neiva_private_path('base_catastral' . DIRECTORY_SEPARATOR . $fileName);
}

function bc_existing_dataset_path(string $publicRelativePath, string $privateFileName): string
{
    $privatePath = bc_private_dataset_path($privateFileName);
    if (is_file($privatePath)) {
        return $privatePath;
    }

    return bc_root_path() . '/' . $publicRelativePath;
}

function bc_storage_roots(): array
{
    return [
        bc_root_path() . '/dat_neiva',
        neiva_private_path('base_catastral'),
        neiva_private_path('tramites_conservacion'),
        bc_arbimaps_path() . '/tramites_conservacion',
    ];
}

function bc_arbimaps_path(): string
{
    return dirname(__DIR__, 3);
}

function bc_safe_code(string $codTramite): string
{
    return preg_replace('/[^A-Za-z0-9_\-]/', '', $codTramite);
}

function bc_year_from_code(string $codTramite): string
{
    if (preg_match('/(20\d{2})/', $codTramite, $m)) {
        return $m[1];
    }
    return date('Y');
}

function bc_radicado_dir(string $codTramite): string
{
    $safeCode = bc_safe_code($codTramite);
    return bc_arbimaps_path() . '/tramites_conservacion/' . bc_year_from_code($safeCode) . '/' . $safeCode . '/base_catastral';
}

function bc_temp_xlsx_path(string $codTramite): string
{
    return bc_radicado_dir($codTramite) . '/' . BC_TEMP_FILE;
}

function bc_temp_json_path(string $codTramite): string
{
    return bc_radicado_dir($codTramite) . '/' . BC_JSON_FILE;
}

function bc_official_path(string $registro): string
{
    $relative = $registro === 'registro2' ? BC_REGISTRO2 : BC_REGISTRO1;
    return bc_existing_dataset_path($relative, basename($relative));
}

function bc_official_csv_path_from_xlsx(string $xlsxPath): string
{
    $registro2 = bc_official_path('registro2');
    $relative = $xlsxPath === $registro2 ? BC_REGISTRO2_CSV : BC_REGISTRO1_CSV;
    return bc_root_path() . '/' . $relative;
}

function bc_normalize_value($value): string
{
    if ($value === null) {
        return '';
    }
    return trim((string) $value);
}

function bc_index_predio_path(string $numeroPredial): string
{
    $safePredial = preg_replace('/[^A-Za-z0-9_\-]/', '', $numeroPredial);
    $prefix = substr($safePredial, 0, 6) ?: 'sin_id';
    $privatePath = neiva_private_path('base_catastral' . DIRECTORY_SEPARATOR . 'indice_predial' . DIRECTORY_SEPARATOR . $prefix . DIRECTORY_SEPARATOR . $safePredial . '.json');
    if (is_file($privatePath)) {
        return $privatePath;
    }

    return bc_root_path() . '/' . BC_INDICE_PREDIAL_DIR . '/' . $prefix . '/' . $safePredial . '.json';
}

function bc_predios_json_path(): string
{
    return bc_existing_dataset_path(BC_PREDIOS_JSON, 'predios.json');
}

function bc_predios_json_backup_dir(): string
{
    $privateDir = neiva_private_path('base_catastral' . DIRECTORY_SEPARATOR . 'backups' . DIRECTORY_SEPARATOR . 'predios_json');
    if (is_dir(dirname($privateDir)) || !is_dir(bc_root_path() . '/' . BC_PREDIOS_JSON_BACKUP_DIR)) {
        return $privateDir;
    }

    return bc_root_path() . '/' . BC_PREDIOS_JSON_BACKUP_DIR;
}

function bc_full_backup_root_dir(): string
{
    $privateDir = neiva_private_path('base_catastral' . DIRECTORY_SEPARATOR . 'backups' . DIRECTORY_SEPARATOR . 'base_catastral_diario');
    if (is_dir(dirname($privateDir)) || !is_dir(bc_root_path() . '/' . BC_FULL_BACKUP_DIR)) {
        return $privateDir;
    }

    return bc_root_path() . '/' . BC_FULL_BACKUP_DIR;
}

function bc_json_last_error_message(): string
{
    $message = json_last_error_msg();
    return $message !== '' ? $message : 'Error JSON desconocido';
}

function bc_decimal_from_index_value($value): float
{
    $normalized = str_replace(',', '.', bc_normalize_value($value));
    $normalized = ltrim($normalized, '0');
    if ($normalized === '' || $normalized === '.') {
        return 0.0;
    }

    return round((float) $normalized, 2);
}

function bc_int_from_index_value($value): int
{
    $normalized = str_replace(',', '', bc_normalize_value($value));
    if ($normalized === '') {
        return 0;
    }

    return (int) $normalized;
}

function bc_predios_json_records_from_index_payload(array $payload): array
{
    $numeroPredial = bc_normalize_value($payload['numero_predial'] ?? '');
    if ($numeroPredial === '') {
        return [];
    }

    $registro1 = $payload['registro1']['rows'] ?? [];
    $registro2 = $payload['registro2']['rows'] ?? [];

    $matricula = null;
    foreach ($registro2 as $row) {
        $candidate = bc_normalize_value($row['values']['G'] ?? '');
        if ($candidate !== '') {
            $matricula = $candidate;
        }
    }

    $records = [];
    foreach ($registro1 as $row) {
        $values = $row['values'] ?? [];
        $records[] = [
            'numero_predio' => $numeroPredial,
            'nombre_propietario_tramite' => bc_normalize_value($values['G'] ?? ''),
            'tipo_doc_propietario' => bc_normalize_value($values['I'] ?? ''),
            'numero_doc_propietario' => bc_normalize_value($values['J'] ?? ''),
            'direccion_predio' => bc_normalize_value($values['K'] ?? ''),
            'dest_econ_predio' => bc_normalize_value($values['M'] ?? ''),
            'area_terreno_predio' => bc_decimal_from_index_value($values['N'] ?? ''),
            'area_construccion_predio' => bc_decimal_from_index_value($values['O'] ?? ''),
            'avaluo_terreno_tramite' => bc_int_from_index_value($values['P'] ?? ''),
            'anio_vigencia' => bc_normalize_value($values['Q'] ?? ''),
            'matricula_inmobiliaria' => $matricula,
        ];
    }

    if (!empty($records)) {
        return $records;
    }

    return [[
        'numero_predio' => $numeroPredial,
        'nombre_propietario_tramite' => '',
        'tipo_doc_propietario' => '',
        'numero_doc_propietario' => '',
        'direccion_predio' => '',
        'dest_econ_predio' => '',
        'area_terreno_predio' => 0,
        'area_construccion_predio' => 0,
        'avaluo_terreno_tramite' => 0,
        'anio_vigencia' => '',
        'matricula_inmobiliaria' => $matricula,
    ]];
}

function actualizar_predios_json_por_predio(string $numeroPredial): array
{
    $numeroPredial = bc_normalize_value($numeroPredial);
    if ($numeroPredial === '') {
        return ['success' => false, 'message' => 'No se recibio numero predial para actualizar predios.json.'];
    }

    $prediosPath = bc_predios_json_path();
    if (!file_exists($prediosPath)) {
        return ['success' => false, 'message' => 'No existe el archivo dat_neiva/predios.json.'];
    }

    $indexPath = bc_index_predio_path($numeroPredial);
    if (!file_exists($indexPath)) {
        return ['success' => false, 'message' => 'No existe el indice predial actualizado para ' . $numeroPredial . '.'];
    }

    $prediosRaw = file_get_contents($prediosPath);
    if ($prediosRaw === false) {
        return ['success' => false, 'message' => 'No se pudo leer dat_neiva/predios.json.'];
    }

    $predios = json_decode($prediosRaw, true);
    unset($prediosRaw);
    if (!is_array($predios)) {
        return ['success' => false, 'message' => 'dat_neiva/predios.json no es un JSON valido: ' . bc_json_last_error_message() . '.'];
    }

    $indexRaw = file_get_contents($indexPath);
    if ($indexRaw === false) {
        return ['success' => false, 'message' => 'No se pudo leer el indice predial actualizado de ' . $numeroPredial . '.'];
    }

    $indexPayload = json_decode($indexRaw, true);
    unset($indexRaw);
    if (!is_array($indexPayload)) {
        return ['success' => false, 'message' => 'El indice predial de ' . $numeroPredial . ' no es un JSON valido: ' . bc_json_last_error_message() . '.'];
    }

    $updatedRecords = bc_predios_json_records_from_index_payload($indexPayload);
    unset($indexPayload);
    if (empty($updatedRecords)) {
        return ['success' => false, 'message' => 'No se pudieron construir registros compatibles para predios.json a partir de ' . $numeroPredial . '.'];
    }

    $backupDir = bc_predios_json_backup_dir();
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0777, true);
    }

    $backupPath = $backupDir . '/predios_' . date('Ymd_His') . '.json';
    if (!copy($prediosPath, $backupPath)) {
        return ['success' => false, 'message' => 'No se pudo crear backup de predios.json en ' . $backupPath . '.'];
    }

    $tempPath = $prediosPath . '.tmp_' . getmypid() . '_' . time();
    $output = fopen($tempPath, 'wb');
    if ($output === false) {
        @unlink($tempPath);
        return ['success' => false, 'message' => 'No se pudo escribir el archivo temporal para predios.json.'];
    }

    fwrite($output, '[');
    $firstRecord = true;
    foreach ($predios as $record) {
        if (!is_array($record)) {
            continue;
        }

        if (bc_normalize_value($record['numero_predio'] ?? '') === $numeroPredial) {
            continue;
        }

        $recordJson = json_encode($record, JSON_UNESCAPED_UNICODE);
        if ($recordJson === false) {
            fclose($output);
            @unlink($tempPath);
            return ['success' => false, 'message' => 'No se pudo serializar un registro de predios.json: ' . bc_json_last_error_message() . '.'];
        }

        fwrite($output, ($firstRecord ? '' : ',') . $recordJson);
        $firstRecord = false;
    }
    unset($predios);

    foreach ($updatedRecords as $record) {
        $recordJson = json_encode($record, JSON_UNESCAPED_UNICODE);
        if ($recordJson === false) {
            fclose($output);
            @unlink($tempPath);
            return ['success' => false, 'message' => 'No se pudo serializar un registro actualizado de predios.json: ' . bc_json_last_error_message() . '.'];
        }

        fwrite($output, ($firstRecord ? '' : ',') . $recordJson);
        $firstRecord = false;
    }

    fwrite($output, ']');
    fclose($output);

    if (!copy($tempPath, $prediosPath)) {
        @unlink($tempPath);
        return ['success' => false, 'message' => 'No se pudo reemplazar dat_neiva/predios.json con la version actualizada.'];
    }

    @unlink($tempPath);

    return [
        'success' => true,
        'message' => 'predios.json actualizado para ' . $numeroPredial . ' con ' . count($updatedRecords) . ' registro(s). Backup: ' . basename($backupPath) . '.',
    ];
}

function bc_read_matching_rows_from_index(string $registro, string $numeroPredial): ?array
{
    $path = bc_index_predio_path($numeroPredial);
    if (!file_exists($path)) {
        return null;
    }

    $payload = json_decode(file_get_contents($path), true);
    if (!is_array($payload)) {
        return null;
    }

    $key = $registro === 'registro2' ? 'registro2' : 'registro1';
    return $payload[$key] ?? ['headers' => [], 'rows' => []];
}

function bc_read_matching_rows(string $xlsxPath, string $numeroPredial): array
{
    $registro = $xlsxPath === bc_official_path('registro2') ? 'registro2' : 'registro1';
    $indexed = bc_read_matching_rows_from_index($registro, $numeroPredial);
    if ($indexed !== null) {
        return $indexed;
    }

    $csvPath = bc_official_csv_path_from_xlsx($xlsxPath);
    if (file_exists($csvPath)) {
        return bc_read_matching_rows_csv($csvPath, $numeroPredial);
    }

    if (!file_exists($xlsxPath)) {
        return ['headers' => [], 'rows' => []];
    }

    $reader = IOFactory::createReader('Xlsx');
    $reader->setReadDataOnly(true);
    $sheet = $reader->load($xlsxPath)->getActiveSheet()->toArray(null, true, true, true);

    $headerRow = $sheet[1] ?? [];
    $headers = [];
    foreach ($headerRow as $col => $header) {
        $label = bc_normalize_value($header);
        $headers[$col] = $label !== '' ? $label : 'Columna ' . $col;
    }

    $rows = [];
    foreach ($sheet as $rowNumber => $row) {
        if ((int) $rowNumber === 1) {
            continue;
        }

        if (bc_normalize_value($row['C'] ?? '') !== bc_normalize_value($numeroPredial)) {
            continue;
        }

        $values = [];
        foreach ($headers as $col => $label) {
            $values[$col] = bc_normalize_value($row[$col] ?? '');
        }

        $rows[] = [
            'row_number' => (int) $rowNumber,
            'values' => $values,
        ];
    }

    return ['headers' => $headers, 'rows' => $rows];
}

function bc_read_matching_rows_csv(string $csvPath, string $numeroPredial): array
{
    $handle = fopen($csvPath, 'r');
    if (!$handle) {
        return ['headers' => [], 'rows' => []];
    }

    $headerRow = fgetcsv($handle, 0, ';');
    if (!is_array($headerRow)) {
        fclose($handle);
        return ['headers' => [], 'rows' => []];
    }

    $headers = [];
    foreach ($headerRow as $index => $header) {
        $col = Coordinate::stringFromColumnIndex($index + 1);
        $label = bc_normalize_value($header);
        $headers[$col] = $label !== '' ? $label : 'Columna ' . $col;
    }

    $rows = [];
    $lineNumber = 1;
    $target = bc_normalize_value($numeroPredial);

    while (($data = fgetcsv($handle, 0, ';')) !== false) {
        $lineNumber++;
        $numeroPredialFila = bc_normalize_value($data[2] ?? '');
        if ($numeroPredialFila !== $target) {
            continue;
        }

        $values = [];
        foreach (array_keys($headers) as $index => $col) {
            $values[$col] = bc_normalize_value($data[$index] ?? '');
        }

        $rows[] = [
            'row_number' => $lineNumber,
            'values' => $values,
        ];
    }

    fclose($handle);
    return ['headers' => $headers, 'rows' => $rows];
}

function bc_default_draft(string $codTramite, string $numeroPredial, string $mutacion, string $createdByRole, string $createdByName): array
{
    $registro1 = bc_read_matching_rows(bc_official_path('registro1'), $numeroPredial);
    $registro2 = bc_read_matching_rows(bc_official_path('registro2'), $numeroPredial);

    return [
        'meta' => [
            'cod_tramite' => $codTramite,
            'numero_predial' => $numeroPredial,
            'mutacion' => $mutacion,
            'estado' => 'EN_PROPUESTA',
            'source_reader' => 'indice-v1',
            'created_at' => date('Y-m-d H:i:s'),
            'created_by_role' => $createdByRole,
            'created_by_name' => $createdByName,
            'updated_at' => date('Y-m-d H:i:s'),
        ],
        'registro1_original' => $registro1,
        'registro2_original' => $registro2,
        'registro1_propuesto' => $registro1,
        'registro2_propuesto' => $registro2,
        'cambios' => [],
        'trazabilidad' => [],
    ];
}

function bc_load_or_create_draft(string $codTramite, string $numeroPredial, string $mutacion, string $role, string $name): array
{
    $jsonPath = bc_temp_json_path($codTramite);
    if (file_exists($jsonPath)) {
        $draft = json_decode(file_get_contents($jsonPath), true);
        if (is_array($draft)) {
            $sourceReader = $draft['meta']['source_reader'] ?? '';
            $hasChanges = !empty($draft['cambios'] ?? []);
            if ($sourceReader !== 'indice-v1' && !$hasChanges) {
                $draft = bc_default_draft($codTramite, $numeroPredial, $mutacion, $role, $name);
                $draft['trazabilidad'][] = [
                    'fecha' => date('Y-m-d H:i:s'),
                    'usuario' => $name,
                    'rol' => $role,
                    'accion' => 'REGENERAR_PROPUESTA_INDICE',
                    'detalle' => 'Preliminar sin cambios regenerado con indice predial.',
                ];
                bc_save_draft($codTramite, $draft);
                return $draft;
            }
            return $draft;
        }
    }

    $draft = bc_default_draft($codTramite, $numeroPredial, $mutacion, $role, $name);
    bc_save_draft($codTramite, $draft);
    return $draft;
}

function bc_save_draft(string $codTramite, array $draft): void
{
    $dir = bc_radicado_dir($codTramite);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    file_put_contents(
        bc_temp_json_path($codTramite),
        json_encode($draft, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    bc_write_temp_workbook($codTramite, $draft);
}

function bc_set_sheet_array($sheet, array $headers, array $rows): void
{
    $colIndex = 1;
    foreach ($headers as $label) {
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex) . '1', $label);
        $colIndex++;
    }

    $rowIndex = 2;
    foreach ($rows as $row) {
        $colIndex = 1;
        foreach (array_keys($headers) as $col) {
            $coordinate = Coordinate::stringFromColumnIndex($colIndex) . $rowIndex;
            $sheet->setCellValueExplicit($coordinate, $row['values'][$col] ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $colIndex++;
        }
        $rowIndex++;
    }
}

function bc_write_temp_workbook(string $codTramite, array $draft): void
{
    $spreadsheet = new Spreadsheet();
    $spreadsheet->removeSheetByIndex(0);

    $sheets = [
        'Registro1_Original' => $draft['registro1_original'],
        'Registro2_Original' => $draft['registro2_original'],
        'Registro1_Propuesto' => $draft['registro1_propuesto'],
        'Registro2_Propuesto' => $draft['registro2_propuesto'],
    ];

    foreach ($sheets as $title => $data) {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle($title);
        bc_set_sheet_array($sheet, $data['headers'] ?? [], $data['rows'] ?? []);
    }

    $cambios = $spreadsheet->createSheet();
    $cambios->setTitle('Cambios');
    $cambios->fromArray(['Fecha', 'Usuario', 'Rol', 'Accion', 'Registro', 'Fila', 'Campo', 'Valor anterior', 'Valor nuevo'], null, 'A1');
    $i = 2;
    foreach ($draft['cambios'] ?? [] as $change) {
        $cambios->fromArray([
            $change['fecha'] ?? '',
            $change['usuario'] ?? '',
            $change['rol'] ?? '',
            $change['accion'] ?? 'MODIFICACION',
            $change['registro'] ?? '',
            $change['fila'] ?? '',
            $change['campo'] ?? '',
            $change['valor_anterior'] ?? '',
            $change['valor_nuevo'] ?? '',
        ], null, 'A' . $i);
        $i++;
    }

    $trace = $spreadsheet->createSheet();
    $trace->setTitle('Trazabilidad');
    $trace->fromArray(['Fecha', 'Usuario', 'Rol', 'Accion', 'Detalle'], null, 'A1');
    $i = 2;
    foreach ($draft['trazabilidad'] ?? [] as $event) {
        $trace->fromArray([
            $event['fecha'] ?? '',
            $event['usuario'] ?? '',
            $event['rol'] ?? '',
            $event['accion'] ?? '',
            $event['detalle'] ?? '',
        ], null, 'A' . $i);
        $i++;
    }

    $spreadsheet->setActiveSheetIndex(0);
    (new Xlsx($spreadsheet))->save(bc_temp_xlsx_path($codTramite));
}

function bc_mutation_tabs(string $mutacion): array
{
    $base = ['informacion', 'propietarios', 'construcciones', 'direcciones', 'avaluo', 'cambios', 'comparativo'];
    $normalized = strtolower($mutacion);

    if (strpos($normalized, '1') !== false || strpos($normalized, 'primera') !== false) {
        return ['informacion', 'propietarios', 'cambios', 'comparativo'];
    }
    if (strpos($normalized, '2') !== false || strpos($normalized, 'segunda') !== false) {
        return ['informacion', 'direcciones', 'cambios', 'comparativo'];
    }
    if (strpos($normalized, '3') !== false || strpos($normalized, 'tercera') !== false) {
        return ['informacion', 'construcciones', 'direcciones', 'cambios', 'comparativo'];
    }
    if (strpos($normalized, '4') !== false || strpos($normalized, 'cuarta') !== false || strpos($normalized, 'avalu') !== false) {
        return ['informacion', 'avaluo', 'cambios', 'comparativo'];
    }
    if (strpos($normalized, '5') !== false || strpos($normalized, 'quinta') !== false || strpos($normalized, 'rectific') !== false || strpos($normalized, 'cancel') !== false) {
        return $base;
    }

    return $base;
}

function bc_editable_tabs_for_role(string $role): array
{
    $map = [
        'ventanilla_catastral' => [],
        'atencion_procedencia' => [],
        'procedencia' => ['propietarios'],
        'procedencia_juridica' => ['propietarios'],
        'revision_juridica' => ['propietarios'],
        'coordinacion_tecnica' => ['informacion', 'propietarios', 'construcciones', 'direcciones', 'avaluo'],
        'control_calidad' => ['informacion', 'construcciones', 'direcciones', 'avaluo', 'propietarios'],
        'reconocedor' => ['construcciones', 'direcciones'],
        'editor' => ['informacion', 'construcciones', 'direcciones'],
        'componente_economico' => ['avaluo'],
        'avaluos' => ['avaluo'],
        'consolidacion' => ['informacion', 'propietarios', 'construcciones', 'direcciones', 'avaluo'],
        'director_catastro' => [],
        'administrador' => ['informacion', 'propietarios', 'construcciones', 'direcciones', 'avaluo'],
    ];

    return $map[$role] ?? [];
}

function bc_can_consolidate(string $role): bool
{
    return in_array($role, ['coordinacion_tecnica', 'consolidacion', 'director_catastro', 'administrador'], true);
}

function bc_can_revert_column(string $role, string $col): bool
{
    foreach (bc_editable_tabs_for_role($role) as $tab) {
        if (in_array($col, bc_columns_for_tab($tab), true)) {
            return true;
        }
    }

    return false;
}

function bc_create_full_daily_backup(?string $timestamp = null): string
{
    $timestamp = $timestamp ?: date('Ymd_His');
    $backupDir = bc_full_backup_root_dir() . '/' . $timestamp;
    if (!is_dir($backupDir) && !mkdir($backupDir, 0777, true)) {
        throw new RuntimeException('No se pudo crear la carpeta de backup diario.');
    }

    foreach (['registro1', 'registro2'] as $registro) {
        $csvPath = bc_official_csv_path_from_xlsx(bc_official_path($registro));
        if (!file_exists($csvPath)) {
            throw new RuntimeException('No existe el CSV oficial ' . $registro . ' para backup diario.');
        }

        $target = $backupDir . '/' . $registro . '_oficial_completo_' . $timestamp . '.csv';
        if (!copy($csvPath, $target)) {
            throw new RuntimeException('No se pudo copiar el CSV oficial ' . $registro . ' al backup diario.');
        }
    }

    file_put_contents($backupDir . '/backup_info.txt', 'Backup completo diario base catastral: ' . date('Y-m-d H:i:s') . PHP_EOL);
    return $backupDir;
}

function bc_apply_rows_to_official_csv(string $registro, array $proposedData, string $codTramite): void
{
    $csvPath = bc_official_csv_path_from_xlsx(bc_official_path($registro));
    if (!file_exists($csvPath)) {
        throw new RuntimeException('No existe el CSV oficial ' . $registro);
    }

    $backupDir = bc_radicado_dir($codTramite) . '/backup_oficial';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0777, true);
    }

    $backupName = $registro . '_oficial_antes_' . date('Ymd_His') . '.csv';
    copy($csvPath, $backupDir . '/' . $backupName);

    $rowsByNumber = [];
    foreach ($proposedData['rows'] ?? [] as $row) {
        $rowNumber = (int) ($row['row_number'] ?? 0);
        if ($rowNumber < 2) {
            continue;
        }
        $rowsByNumber[$rowNumber] = $row['values'] ?? [];
    }

    if (empty($rowsByNumber)) {
        return;
    }

    $input = fopen($csvPath, 'r');
    if (!$input) {
        throw new RuntimeException('No se pudo abrir el CSV oficial ' . $registro);
    }

    $tempPath = $csvPath . '.tmp_' . getmypid();
    $output = fopen($tempPath, 'w');
    if (!$output) {
        fclose($input);
        throw new RuntimeException('No se pudo crear temporal para el CSV oficial ' . $registro);
    }

    $lineNumber = 0;
    while (($data = fgetcsv($input, 0, ';')) !== false) {
        $lineNumber++;
        if (isset($rowsByNumber[$lineNumber])) {
            foreach ($rowsByNumber[$lineNumber] as $col => $value) {
                $index = Coordinate::columnIndexFromString($col) - 1;
                $data[$index] = (string) $value;
            }
        }
        fputcsv($output, $data, ';');
    }

    fclose($input);
    fclose($output);

    if (!copy($tempPath, $csvPath)) {
        @unlink($tempPath);
        throw new RuntimeException('No se pudo reemplazar el CSV oficial ' . $registro);
    }
    @unlink($tempPath);
}

function bc_update_predio_index_from_draft(array $draft): void
{
    $numeroPredial = $draft['meta']['numero_predial'] ?? '';
    if ($numeroPredial === '') {
        return;
    }

    $path = bc_index_predio_path($numeroPredial);
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $payload = [
        'numero_predial' => $numeroPredial,
        'registro1' => $draft['registro1_propuesto'] ?? ['headers' => [], 'rows' => []],
        'registro2' => $draft['registro2_propuesto'] ?? ['headers' => [], 'rows' => []],
    ];

    file_put_contents($path, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

function bc_consolidate_official(string $codTramite, array $draft, string $role, string $user): array
{
    if (!bc_can_consolidate($role)) {
        throw new RuntimeException('Tu rol no tiene permiso para aplicar cambios al Excel oficial.');
    }

    $numeroPredial = bc_normalize_value($draft['meta']['numero_predial'] ?? '');
    bc_apply_rows_to_official_csv('registro1', $draft['registro1_propuesto'] ?? [], $codTramite);
    bc_apply_rows_to_official_csv('registro2', $draft['registro2_propuesto'] ?? [], $codTramite);
    bc_update_predio_index_from_draft($draft);
    $prediosJsonResult = actualizar_predios_json_por_predio($numeroPredial);
    if (!$prediosJsonResult['success']) {
        error_log('[base_catastral] ' . $prediosJsonResult['message']);
    }

    $draft['meta']['estado'] = 'CONSOLIDADO_OFICIAL';
    $draft['meta']['updated_at'] = date('Y-m-d H:i:s');
    $draft['trazabilidad'][] = [
        'fecha' => date('Y-m-d H:i:s'),
        'usuario' => $user,
        'rol' => $role,
        'accion' => 'APLICAR_EXCEL_OFICIAL',
        'detalle' => 'La propuesta fue aplicada a Registro 1 y Registro 2 CSV oficiales. Se actualizo el indice predial y se genero backup en la carpeta del radicado. predios.json: ' . $prediosJsonResult['message'],
    ];

    bc_save_draft($codTramite, $draft);
    return $draft;
}

function bc_columns_for_tab(string $tab): array
{
    $columns = [
        'informacion' => ['C', 'K', 'M', 'N', 'O', 'P', 'Q'],
        'propietarios' => ['G', 'I', 'J'],
        'construcciones' => ['O'],
        'direcciones' => ['K'],
        'avaluo' => ['M', 'P', 'Q'],
    ];

    return $columns[$tab] ?? [];
}

function bc_apply_posted_changes(string $codTramite, array $draft, array $fields, string $role, string $user): array
{
    foreach ($fields as $registroKey => $rows) {
        if (!in_array($registroKey, ['registro1_propuesto', 'registro2_propuesto'], true) || !is_array($rows)) {
            continue;
        }

        foreach ($rows as $rowIndex => $cols) {
            foreach ($cols as $col => $newValue) {
                if (!isset($draft[$registroKey]['rows'][$rowIndex]['values'][$col])) {
                    continue;
                }

                $oldValue = bc_normalize_value($draft[$registroKey]['rows'][$rowIndex]['values'][$col]);
                $newValue = bc_normalize_value($newValue);
                if ($oldValue === $newValue) {
                    continue;
                }

                $draft[$registroKey]['rows'][$rowIndex]['values'][$col] = $newValue;
                $label = $draft[$registroKey]['headers'][$col] ?? $col;
                $draft['cambios'][] = [
                    'fecha' => date('Y-m-d H:i:s'),
                    'usuario' => $user,
                    'rol' => $role,
                    'accion' => 'MODIFICACION',
                    'registro' => str_replace('_propuesto', '', $registroKey),
                    'fila' => $draft[$registroKey]['rows'][$rowIndex]['row_number'] ?? '',
                    'campo' => $label,
                    'columna' => $col,
                    'valor_anterior' => $oldValue,
                    'valor_nuevo' => $newValue,
                ];
            }
        }
    }

    $draft['meta']['updated_at'] = date('Y-m-d H:i:s');
    $draft['trazabilidad'][] = [
        'fecha' => date('Y-m-d H:i:s'),
        'usuario' => $user,
        'rol' => $role,
        'accion' => 'GUARDAR_PROPUESTA',
        'detalle' => 'Actualizacion de campos de base catastral temporal',
    ];

    bc_save_draft($codTramite, $draft);
    return $draft;
}

function bc_revert_proposed_change(string $codTramite, array $draft, string $registro, int $rowIndex, string $col, string $role, string $user): array
{
    if (!in_array($registro, ['registro1', 'registro2'], true)) {
        throw new RuntimeException('Registro no valido para revertir.');
    }

    if (!bc_can_revert_column($role, $col)) {
        throw new RuntimeException('Tu rol no tiene permiso para revertir este campo.');
    }

    $originalKey = $registro . '_original';
    $proposedKey = $registro . '_propuesto';

    if (!isset($draft[$originalKey]['rows'][$rowIndex]['values'][$col], $draft[$proposedKey]['rows'][$rowIndex]['values'][$col])) {
        throw new RuntimeException('No se encontro el campo seleccionado para revertir.');
    }

    $currentValue = bc_normalize_value($draft[$proposedKey]['rows'][$rowIndex]['values'][$col]);
    $originalValue = bc_normalize_value($draft[$originalKey]['rows'][$rowIndex]['values'][$col]);

    if ($currentValue === $originalValue) {
        throw new RuntimeException('El campo ya coincide con el valor oficial.');
    }

    $draft[$proposedKey]['rows'][$rowIndex]['values'][$col] = $originalValue;
    $label = $draft[$proposedKey]['headers'][$col] ?? $col;
    $rowNumber = $draft[$proposedKey]['rows'][$rowIndex]['row_number'] ?? '';

    $draft['cambios'][] = [
        'fecha' => date('Y-m-d H:i:s'),
        'usuario' => $user,
        'rol' => $role,
        'accion' => 'REVERSION_CAMBIO',
        'registro' => $registro,
        'fila' => $rowNumber,
        'campo' => $label,
        'columna' => $col,
        'valor_anterior' => $currentValue,
        'valor_nuevo' => $originalValue,
    ];

    $draft['meta']['updated_at'] = date('Y-m-d H:i:s');
    $draft['trazabilidad'][] = [
        'fecha' => date('Y-m-d H:i:s'),
        'usuario' => $user,
        'rol' => $role,
        'accion' => 'REVERSION_CAMBIO',
        'detalle' => 'Se revirtio ' . $registro . ' fila ' . $rowNumber . ' campo ' . $label . ' al valor oficial original.',
    ];

    bc_save_draft($codTramite, $draft);
    return $draft;
}
