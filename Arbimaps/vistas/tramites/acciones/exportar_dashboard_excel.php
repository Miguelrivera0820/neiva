<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';
require_once dirname(__DIR__) . '/dashboard_lib.php';
require_once dirname(__DIR__, 4) . '/vendor/autoload.php';

neiva_bootstrap();
neiva_require_methods('GET');
neiva_require_permission('menu.tramites', $PERMISOS);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

$data = td_dashboard_data($mysqli);
$spreadsheet = new Spreadsheet();
$spreadsheet->getProperties()
    ->setCreator('Arbimaps')
    ->setTitle('Reporte tablero de control');

$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Resumen');
$sheet->fromArray([
    ['Indicador', 'Valor'],
    ['Tramites radicados', $data['total_rad']],
    ['Tramites asignados activos', $data['total_asignaciones']],
    ['Tramites culminados', $data['total_cul']],
    ['Tramites vencidos/caducados', $data['total_vencidas']],
    ['Generado', date('Y-m-d H:i:s')],
], null, 'A1');

$sheet = $spreadsheet->createSheet();
$sheet->setTitle('Indicadores');
$sheet->fromArray([['Estado', 'Cantidad']], null, 'A1');
$row = 2;
foreach ($data['status_counts'] as $estado => $cantidad) {
    $sheet->setCellValue('A' . $row, $estado);
    $sheet->setCellValue('B' . $row, $cantidad);
    $row++;
}
$row += 2;
$sheet->fromArray([['Mutacion', 'Cantidad']], null, 'A' . $row);
$row++;
foreach ($data['mutacion_counts'] as $mutacion => $cantidad) {
    $sheet->setCellValue('A' . $row, $mutacion);
    $sheet->setCellValue('B' . $row, $cantidad);
    $row++;
}

$sheet = $spreadsheet->createSheet();
$sheet->setTitle('Asignaciones_usuario');
$sheet->fromArray([['Responsable', 'Cedula', 'Rol', 'Total', 'En asignacion', 'En revision', 'A tiempo', 'A vencer', 'Vencido', 'Caducado']], null, 'A1');
$row = 2;
foreach ($data['conteos'] as $item) {
    $sheet->fromArray([
        $item['responsable'],
        $item['cc'],
        $item['rol'],
        $item['asignado'],
        $item['en_asignacion'],
        $item['en_revision'],
        $item['a_tiempo'],
        $item['a_vencer'],
        $item['vencido'],
        $item['caducado'],
    ], null, 'A' . $row);
    $row++;
}

$sheet = $spreadsheet->createSheet();
$sheet->setTitle('Detalle_general');
$sheet->fromArray([['Cod tramite', 'Responsable', 'Cedula', 'Rol', 'Etapa', 'Estado flujo', 'Estado plazo', 'Dias', 'Fecha radicacion', 'Fecha asignacion', 'Fecha limite', 'Fecha respuesta', 'Mutacion', 'Tipo tramite']], null, 'A1');
$row = 2;
foreach ($data['items'] as $item) {
    $values = [
        $item['cod_tramite'],
        $item['responsable'],
        $item['cc_usuario'],
        $item['rol'],
        $item['etapa'],
        $item['estado_tramite'],
        $item['estado_vencimiento'],
        $item['dias'] === null ? '' : $item['dias'],
        $item['fecha_rad'],
        $item['fecha_movimiento'],
        $item['fecha_limite'],
        $item['fecha_respuesta_tramite'],
        $item['mutacion'],
        $item['tipo_tramite'],
    ];
    foreach ($values as $idx => $value) {
        $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
        $sheet->setCellValueExplicit($col . $row, (string) $value, DataType::TYPE_STRING);
    }
    $row++;
}

foreach ($spreadsheet->getAllSheets() as $ws) {
    foreach (range('A', 'N') as $col) {
        $ws->getColumnDimension($col)->setAutoSize(true);
    }
    $ws->getStyle('A1:N1')->getFont()->setBold(true);
}

$spreadsheet->setActiveSheetIndex(0);
$filename = 'reporte_tablero_control_' . date('Ymd_His') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
(new Xlsx($spreadsheet))->save('php://output');
exit;
