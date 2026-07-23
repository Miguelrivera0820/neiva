<?php

require_once __DIR__ . '/base_catastral_lib.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    die('Este proceso solo puede ejecutarse por consola.');
}

try {
    $dir = bc_create_full_daily_backup();
    echo 'Backup completo diario creado en: ' . $dir . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, 'No se pudo crear el backup completo diario: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
