<?php
ob_start();

date_default_timezone_set('America/Bogota');

// Este archivo está en Arbimaps/vistas/tramites. Las rutas anteriores subían
// un nivel de más y devolvían un error PHP que el navegador guardaba como ZIP.
require_once __DIR__ . '/../../includes/bootstrap.php';
neiva_bootstrap();
require_once __DIR__ . '/../../../conexion.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/permisos.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->set_charset('utf8mb4');
}

if (!usuarioTieneAlgunRol($PERMISOS['tramites.exportar'] ?? [])) {
    http_response_code(403);
    die('No tiene permiso para exportar tramites.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Metodo no permitido.');
}

if (!class_exists('ZipArchive')) {
    http_response_code(500);
    die('La extension ZipArchive no esta disponible en el servidor.');
}

$csrf = $_POST['csrf_token'] ?? '';
if (
    !is_string($csrf)
    || empty($_SESSION['csrf_cancelar_tramites'])
    || !hash_equals($_SESSION['csrf_cancelar_tramites'], $csrf)
) {
    http_response_code(419);
    die('La sesion del formulario vencio. Recargue la pagina e intente nuevamente.');
}

$codigos = $_POST['codigos'] ?? [];
$codigos = is_array($codigos)
    ? array_values(array_unique(array_filter(array_map('trim', $codigos))))
    : [];

if (count($codigos) < 1 || count($codigos) > 200) {
    http_response_code(422);
    die('Seleccione entre 1 y 200 tramites.');
}

foreach ($codigos as $codigo) {
    if (!preg_match('/^[A-Za-z0-9_-]{1,25}$/', $codigo)) {
        http_response_code(422);
        die('Uno de los codigos seleccionados no es valido.');
    }
}

function exportarTramitesNombreSeguro(string $nombre): string
{
    $nombre = trim($nombre);
    $nombre = preg_replace('/[^A-Za-z0-9._ -]/', '_', $nombre);
    $nombre = trim((string) $nombre, " .-_");

    return $nombre !== '' ? $nombre : 'archivo';
}

function exportarTramitesRutasCandidatas(array $tramite): array
{
    $codTramite = (string) ($tramite['cod_tramite'] ?? '');
    $rutas = [];
    $anioFechaRad = '';

    if (!empty($tramite['fecha_rad'])) {
        $timestamp = strtotime((string) $tramite['fecha_rad']);
        if ($timestamp !== false) {
            $anioFechaRad = date('Y', $timestamp);
        }
    }

    if (preg_match('/^(?:CAT|ARB)[-_]?(20\d{2})/i', $codTramite, $m)) {
        $rutas[] = "tramites_conservacion/{$m[1]}/$codTramite/";
    } elseif (preg_match('/^(20\d{2})/', $codTramite, $m)) {
        $rutas[] = "tramites_conservacion/{$m[1]}/$codTramite/";
    }

    if ($anioFechaRad !== '') {
        $rutas[] = "tramites_conservacion/$anioFechaRad/$codTramite/";
    }

    $rutas[] = 'tramites_conservacion/' . date('Y') . "/$codTramite/";

    return array_values(array_unique($rutas));
}

function exportarTramitesBuscarCarpetas(array $tramite, array $basesDocumentos): array
{
    $carpetas = [];
    $rutasVistas = [];

    foreach (exportarTramitesRutasCandidatas($tramite) as $rutaRelativa) {
        foreach ($basesDocumentos as $baseDocumento) {
            $ruta = $baseDocumento['fs'] . '/' . $rutaRelativa;
            $rutaReal = realpath($ruta);
            if ($rutaReal === false || !is_dir($rutaReal)) {
                continue;
            }

            $clave = strtolower(str_replace('\\', '/', $rutaReal));
            if (isset($rutasVistas[$clave])) {
                continue;
            }

            $rutasVistas[$clave] = true;
            $carpetas[] = [
                'fs' => rtrim($rutaReal, '/\\'),
                'relativa' => $rutaRelativa,
                'origen' => $baseDocumento['nombre'],
            ];
        }
    }

    return $carpetas;
}

function exportarTramitesListarArchivos(string $carpeta): array
{
    $archivos = [];
    $baseReal = realpath($carpeta);
    if ($baseReal === false || !is_dir($baseReal)) {
        return $archivos;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseReal, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        if (!$item->isFile()) {
            continue;
        }

        $rutaReal = $item->getRealPath();
        if ($rutaReal === false || strpos($rutaReal, $baseReal) !== 0) {
            continue;
        }

        $relativa = str_replace(
            '\\',
            '/',
            ltrim(substr($rutaReal, strlen($baseReal)), DIRECTORY_SEPARATOR)
        );
        $segmentosRuta = array_map('strtolower', explode('/', $relativa));

        // La exportación documental no debe incluir la base catastral ni
        // ninguno de los archivos almacenados dentro de esa carpeta.
        if (in_array('base_catastral', $segmentosRuta, true)) {
            continue;
        }

        $archivos[] = [
            'fs' => $rutaReal,
            'relativa' => $relativa,
            'tamano' => $item->getSize(),
        ];
    }

    usort($archivos, static function ($a, $b) {
        return strcmp($a['relativa'], $b['relativa']);
    });

    return $archivos;
}

$placeholders = implode(',', array_fill(0, count($codigos), '?'));
$sql = "SELECT
            t.*,
            COALESCE(e.es_nombre, 'RADICADO') AS estado_actual
        FROM tramite_radicacion t
        LEFT JOIN estados_tramite e
            ON e.id = (
                SELECT MAX(e2.id)
                FROM estados_tramite e2
                WHERE e2.cod_tramite = t.cod_tramite
            )
        WHERE t.cod_tramite IN ($placeholders)
          AND NOT EXISTS (
              SELECT 1
              FROM tramites_cancelados tc
              WHERE tc.cod_tramite = t.cod_tramite
                AND tc.estado = 'CANCELADO'
          )
        ORDER BY t.fecha_rad DESC";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    die('No fue posible preparar la exportacion.');
}

$tipos = str_repeat('s', count($codigos));
$parametros = [$tipos];
foreach ($codigos as $indice => $codigo) {
    $parametros[] = &$codigos[$indice];
}

if (!call_user_func_array([$stmt, 'bind_param'], $parametros) || !$stmt->execute()) {
    http_response_code(500);
    die('No fue posible consultar los tramites seleccionados.');
}

$resultado = $stmt->get_result();
$tramites = [];
while ($fila = $resultado->fetch_assoc()) {
    $tramites[$fila['cod_tramite']] = $fila;
}
$stmt->close();

if (count($tramites) < 1) {
    http_response_code(404);
    die('No se encontraron tramites activos para exportar.');
}

$baseAplicacion = dirname(__DIR__, 3);
$basesDocumentos = [
    // Ruta oficial de los expedientes:
    // /neiva/Arbimaps/tramites_conservacion/{anio}/{codigo}
    ['fs' => $baseAplicacion . '/Arbimaps', 'nombre' => 'arbimaps'],
];

$paquetes = [];
foreach ($tramites as $codTramite => $tramite) {
    $carpetas = exportarTramitesBuscarCarpetas($tramite, $basesDocumentos);
    $archivos = [];
    $nombresZipUsados = [];

    foreach ($carpetas as $carpeta) {
        foreach (exportarTramitesListarArchivos($carpeta['fs']) as $archivo) {
            // Todos los documentos quedan directamente en documentos/, sin
            // reproducir las subcarpetas internas del expediente.
            $relativaZip = basename($archivo['relativa']);
            $extension = pathinfo($relativaZip, PATHINFO_EXTENSION);
            $nombreBase = pathinfo($relativaZip, PATHINFO_FILENAME);
            $claveZip = strtolower($relativaZip);
            $sufijo = 2;
            while (isset($nombresZipUsados[$claveZip])) {
                $relativaZip = $nombreBase . '_' . $sufijo . ($extension !== '' ? '.' . $extension : '');
                $claveZip = strtolower($relativaZip);
                $sufijo++;
            }

            $nombresZipUsados[$claveZip] = true;
            $archivo['relativa_zip'] = $relativaZip;
            $archivo['origen'] = $carpeta['origen'];
            $archivos[] = $archivo;
        }
    }

    $paquetes[$codTramite] = [
        'tramite' => $tramite,
        'carpetas' => $carpetas,
        'archivos' => $archivos,
    ];
}

$spreadsheet = new Spreadsheet();
$spreadsheet->getProperties()
    ->setCreator('Arbimaps')
    ->setTitle('Resumen tramites seleccionados');

$sheetTramites = $spreadsheet->getActiveSheet();
$sheetTramites->setTitle('Tramites');
$encabezadosTramites = [
    'Codigo tramite', 'Fecha radicacion', 'Estado actual', 'Solicitante',
    'Telefono', 'Correo', 'Documentos exportados'
];
$sheetTramites->fromArray($encabezadosTramites, null, 'A1');
$sheetTramites->getStyle('A1:G1')->getFont()->setBold(true);
$sheetTramites->freezePane('A2');

$filaExcel = 2;
foreach ($paquetes as $codTramite => $paquete) {
    $tramite = $paquete['tramite'];
    $nombreSolicitante = trim(implode(' ', array_filter([
        $tramite['primer_nombre_interesado'] ?? '',
        $tramite['segundo_nombre_interesado'] ?? '',
        $tramite['primer_apellido_interesado'] ?? '',
        $tramite['segundo_apellido_interesado'] ?? '',
    ])));

    $valores = [
        $codTramite,
        $tramite['fecha_rad'] ?? '',
        $tramite['estado_actual'] ?? '',
        $nombreSolicitante,
        $tramite['telefono_interesado'] ?? '',
        $tramite['correo_interesado'] ?? '',
        count($paquete['archivos']),
    ];
    foreach ($valores as $indice => $valor) {
        $columna = chr(65 + $indice);
        $sheetTramites->setCellValueExplicit(
            $columna . $filaExcel,
            (string) $valor,
            DataType::TYPE_STRING
        );
    }
    $filaExcel++;
}

$sheetDocumentos = $spreadsheet->createSheet();
$sheetDocumentos->setTitle('Documentos');
$sheetDocumentos->fromArray(
    ['Codigo tramite', 'Archivo en ZIP', 'Tamano bytes'],
    null,
    'A1'
);
$sheetDocumentos->getStyle('A1:C1')->getFont()->setBold(true);
$sheetDocumentos->freezePane('A2');

$filaExcel = 2;
foreach ($paquetes as $codTramite => $paquete) {
    foreach ($paquete['archivos'] as $archivo) {
        $sheetDocumentos->setCellValueExplicit('A' . $filaExcel, $codTramite, DataType::TYPE_STRING);
        $sheetDocumentos->setCellValueExplicit('B' . $filaExcel, $archivo['relativa_zip'], DataType::TYPE_STRING);
        $sheetDocumentos->setCellValueExplicit('C' . $filaExcel, (string) $archivo['tamano'], DataType::TYPE_STRING);
        $filaExcel++;
    }
}

foreach ([$sheetTramites, $sheetDocumentos] as $sheet) {
    foreach ($sheet->getColumnIterator() as $columna) {
        $sheet->getColumnDimension($columna->getColumnIndex())->setAutoSize(true);
    }
}
$spreadsheet->setActiveSheetIndex(0);

$xlsxPath = tempnam(sys_get_temp_dir(), 'tramites_resumen_');
$zipPath = tempnam(sys_get_temp_dir(), 'tramites_zip_');
if ($xlsxPath === false || $zipPath === false) {
    http_response_code(500);
    die('No fue posible crear los archivos temporales de la exportacion.');
}

$xlsxFinalPath = $xlsxPath . '.xlsx';
if (!rename($xlsxPath, $xlsxFinalPath)) {
    @unlink($xlsxPath);
    @unlink($zipPath);
    http_response_code(500);
    die('No fue posible preparar el resumen Excel.');
}
(new Xlsx($spreadsheet))->save($xlsxFinalPath);

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    @unlink($xlsxFinalPath);
    http_response_code(500);
    die('No fue posible crear el ZIP de exportacion.');
}

$erroresArchivosZip = [];
if (!$zip->addFile($xlsxFinalPath, 'resumen_tramites.xlsx')) {
    $erroresArchivosZip[] = 'No se pudo agregar el resumen Excel.';
}

foreach ($paquetes as $codTramite => $paquete) {
    $carpetaZip = exportarTramitesNombreSeguro($codTramite);
    $zip->addEmptyDir($carpetaZip);
    $zip->addEmptyDir($carpetaZip . '/documentos');

    if (!$paquete['carpetas']) {
        continue;
    }

    if (!$paquete['archivos']) {
        continue;
    }

    // Se agregan todos los archivos encontrados recursivamente en la carpeta
    // del expediente, no solamente los documentos registrados en la BD.
    foreach ($paquete['archivos'] as $archivo) {
        $rutaZip = $carpetaZip . '/documentos/' . $archivo['relativa_zip'];
        if (!is_file($archivo['fs']) || !is_readable($archivo['fs'])) {
            $erroresArchivosZip[] = $codTramite . ': no se puede leer ' . $archivo['relativa'];
            continue;
        }
        if (!$zip->addFile($archivo['fs'], $rutaZip)) {
            $erroresArchivosZip[] = $codTramite . ': no se pudo agregar ' . $archivo['relativa'];
        }
    }
}

if ($erroresArchivosZip) {
    $zip->close();
    @unlink($xlsxFinalPath);
    @unlink($zipPath);
    http_response_code(500);
    die("No fue posible incluir todos los documentos en el ZIP:\n" . implode("\n", $erroresArchivosZip));
}

if (!$zip->close()) {
    @unlink($xlsxFinalPath);
    @unlink($zipPath);
    http_response_code(500);
    die('No fue posible finalizar el ZIP de exportacion.');
}
@unlink($xlsxFinalPath);

$filename = 'tramites_seleccionados_' . date('Ymd_His') . '.zip';
$zipSize = filesize($zipPath);
if ($zipSize === false || $zipSize < 22) {
    @unlink($zipPath);
    http_response_code(500);
    die('El ZIP generado esta vacio o no es valido.');
}

$zipHandle = fopen($zipPath, 'rb');
$zipSignature = $zipHandle ? fread($zipHandle, 2) : false;
if ($zipHandle) {
    fclose($zipHandle);
}
if ($zipSignature !== 'PK') {
    @unlink($zipPath);
    http_response_code(500);
    die('El archivo generado no tiene un formato ZIP valido.');
}

while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . $zipSize);
header('Cache-Control: max-age=0');
header('X-Content-Type-Options: nosniff');

readfile($zipPath);
@unlink($zipPath);
exit;
