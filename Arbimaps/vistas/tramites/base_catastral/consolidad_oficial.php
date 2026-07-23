<?php
require_once __DIR__ . '/../../../includes/bootstrap.php';
neiva_bootstrap();
neiva_require_methods('POST');
neiva_require_auth();
neiva_require_csrf('global');

require_once __DIR__ . '/base_catastral_lib.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$cod_tramite = trim((string) ($_POST['cod'] ?? ''));
$rol_usuario = $_SESSION['rol_usuario'] ?? '';
$nombre_usuario = trim(($_SESSION['nombre_usuario'] ?? '') . ' ' . ($_SESSION['apellido_usuario'] ?? ''));

function bc_finish_consolidacion(string $codTramite): void
{
    $url = 'index.php?page=tramites/base_catastral/predio_mutacion&cod=' . urlencode($codTramite) . '&ok=consolidado';
    if (!headers_sent()) {
        header('Location: ../../../' . $url);
        exit;
    }

    echo '<div class="alert alert-success m-4">Cambios aplicados al Excel oficial. Redirigiendo...</div>';
    echo '<script>window.location.href = ' . json_encode($url) . ';</script>';
    exit;
}

if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    http_response_code(403);
    die('Sesion expirada o no iniciada.');
}

if ($cod_tramite === '') {
    die('Falta el codigo del tramite.');
}

if (!bc_can_consolidate($rol_usuario)) {
    http_response_code(403);
    die('Tu rol no tiene permiso para aplicar cambios al Excel oficial.');
}

$jsonPath = bc_temp_json_path($cod_tramite);
if (!file_exists($jsonPath)) {
    die('No existe propuesta temporal para consolidar.');
}

$draft = json_decode(file_get_contents($jsonPath), true);
if (!is_array($draft)) {
    die('La propuesta temporal no se puede leer.');
}

function bc_apply_rows_to_workbook(string $registro, array $proposedData, string $codTramite): void
{
    $officialPath = bc_official_path($registro);
    if (!file_exists($officialPath)) {
        throw new RuntimeException('No existe el archivo oficial ' . $registro);
    }

    $backupDir = bc_radicado_dir($codTramite) . '/backup_oficial';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0777, true);
    }

    $backupName = $registro . '_oficial_antes_' . date('Ymd_His') . '.xlsx';
    copy($officialPath, $backupDir . '/' . $backupName);

    $spreadsheet = IOFactory::load(neiva_resolve_existing_path($officialPath, bc_storage_roots()));
    $sheet = $spreadsheet->getActiveSheet();

    foreach ($proposedData['rows'] ?? [] as $row) {
        $rowNumber = (int) ($row['row_number'] ?? 0);
        if ($rowNumber < 2) {
            continue;
        }

        foreach (($row['values'] ?? []) as $col => $value) {
            $sheet->setCellValueExplicit($col . $rowNumber, (string) $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        }
    }

    (new Xlsx($spreadsheet))->save($officialPath);
}

try {
    bc_apply_rows_to_workbook('registro1', $draft['registro1_propuesto'] ?? [], $cod_tramite);
    bc_apply_rows_to_workbook('registro2', $draft['registro2_propuesto'] ?? [], $cod_tramite);

    $draft['meta']['estado'] = 'CONSOLIDADO_OFICIAL';
    $draft['meta']['updated_at'] = date('Y-m-d H:i:s');
    $draft['trazabilidad'][] = [
        'fecha' => date('Y-m-d H:i:s'),
        'usuario' => $nombre_usuario,
        'rol' => $rol_usuario,
        'accion' => 'APLICAR_EXCEL_OFICIAL',
        'detalle' => 'La propuesta fue aplicada a Registro 1 y Registro 2 oficiales. Se genero backup en la carpeta del radicado.',
    ];
    bc_save_draft($cod_tramite, $draft);

    bc_finish_consolidacion($cod_tramite);
} catch (Throwable $e) {
    http_response_code(500);
    echo 'No se pudo consolidar: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
