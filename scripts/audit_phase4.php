<?php
/**
 * Script de Auditoría de Fase 4 para Neiva/ARBIMaps
 * Detecta de forma automatizada referencias problemáticas en la base de código.
 */

// Cargar bootstrap para usar utilidades si se requiere, pero este script se puede correr stand-alone.
require_once __DIR__ . '/../Arbimaps/includes/bootstrap.php';

$rootDir = dirname(__DIR__);
$excludeDirs = [
    'vendor',
    'node_modules',
    '.git',
    '.vscode',
    '.agents',
    'tmp',
    'temp',
    'cache',
    'scripts' // No auto-auditar la carpeta de scripts
];

$scanExtensions = ['php', 'js', 'html', 'htaccess'];

// Patrones a buscar
$patterns = [
    'arbimaps_url' => [
        'regex' => '/(?:\'|")\/[a-zA-Z0-9_\-]*arbimaps/i',
        'label' => 'Ruta/URL hardcodeada con "/arbimaps"'
    ],
    'xampp_path' => [
        'regex' => '/[a-zA-Z]:\\\\xampp\\\\/i',
        'label' => 'Ruta absoluta de XAMPP (C:\\xampp...)'
    ],
    'manual_session' => [
        'regex' => '/\bsession_start\s*\(/',
        'label' => 'session_start() manual (debe usar bootstrap.php)'
    ]
];

$results = [];
$totalFiles = 0;

function scanDirectory($dir, $rootDir, $excludeDirs, $scanExtensions, $patterns, &$results, &$totalFiles) {
    $iterator = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);

    foreach ($files as $file) {
        if ($file->isDir()) {
            continue;
        }

        $filePath = $file->getRealPath();
        $relativePath = str_replace($rootDir . DIRECTORY_SEPARATOR, '', $filePath);

        // Excluir directorios definidos
        $skip = false;
        foreach ($excludeDirs as $excludeDir) {
            if (str_starts_with($relativePath, $excludeDir . DIRECTORY_SEPARATOR) || $relativePath === $excludeDir) {
                $skip = true;
                break;
            }
        }

        if ($skip) {
            continue;
        }

        // Excluir por extensión
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (!in_array($ext, $scanExtensions, true)) {
            continue;
        }

        $totalFiles++;
        auditFile($filePath, $relativePath, $patterns, $results);
    }
}

function auditFile($filePath, $relativePath, $patterns, &$results) {
    $content = file_get_contents($filePath);
    if ($content === false) {
        return;
    }

    $lines = explode("\n", $content);
    foreach ($lines as $lineNum => $lineContent) {
        $trimmedLine = trim($lineContent);
        if ($trimmedLine === '') {
            continue;
        }

        foreach ($patterns as $patternKey => $patternInfo) {
            if (preg_match($patternInfo['regex'], $lineContent)) {
                // Si es un session_start() manual, omitir si está comentado
                if ($patternKey === 'manual_session' && (str_starts_with($trimmedLine, '//') || str_starts_with($trimmedLine, '#') || str_contains($trimmedLine, '/*'))) {
                    continue;
                }

                // Omitir si la línea contiene neiva_app_url o neiva_private_path (ya saneada)
                if ($patternKey === 'arbimaps_url' && (str_contains($lineContent, 'neiva_app_url') || str_contains($lineContent, 'neiva_env') || str_contains($lineContent, 'bootstrap.php') || str_contains($lineContent, 'conexion.php'))) {
                    continue;
                }

                $results[] = [
                    'file' => $relativePath,
                    'line' => $lineNum + 1,
                    'content' => $trimmedLine,
                    'label' => $patternInfo['label']
                ];
            }
        }
    }
}

echo "Iniciando auditoría de Fase 4 en: " . $rootDir . PHP_EOL;
scanDirectory($rootDir, $rootDir, $excludeDirs, $scanExtensions, $patterns, $results, $totalFiles);

echo "Total de archivos analizados: " . $totalFiles . PHP_EOL;
echo "Se encontraron " . count($results) . " posibles problemas:" . PHP_EOL . PHP_EOL;

$grouped = [];
foreach ($results as $res) {
    $grouped[$res['file']][] = $res;
}

foreach ($grouped as $file => $occurrences) {
    echo "--------------------------------------------------" . PHP_EOL;
    echo "Archivo: " . $file . PHP_EOL;
    echo "--------------------------------------------------" . PHP_EOL;
    foreach ($occurrences as $occ) {
        echo "  [Línea " . $occ['line'] . "] (" . $occ['label'] . "):" . PHP_EOL;
        echo "    " . substr($occ['content'], 0, 100) . (strlen($occ['content']) > 100 ? '...' : '') . PHP_EOL;
    }
    echo PHP_EOL;
}
