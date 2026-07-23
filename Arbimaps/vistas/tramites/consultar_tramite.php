<?php
require "../conexion.php";
//  session_start();
$error_login = "";
$puede_cancelar_tramites = usuarioTieneAlgunRol($PERMISOS['tramites.cancelar']);
$puede_exportar_tramites = usuarioTieneAlgunRol($PERMISOS['tramites.exportar']);
$puede_seleccionar_tramites = $puede_cancelar_tramites || $puede_exportar_tramites;

if (!function_exists('redirigirConsultaTramites')) {
    function redirigirConsultaTramites(): void
    {
        $url = 'index.php?page=tramites/consultar_tramite';
        if (!headers_sent()) {
            header('Location: ' . $url);
            exit;
        }

        echo '<script>window.location.replace(' . json_encode($url) . ');</script>';
        exit;
    }
}

if (empty($_SESSION['csrf_cancelar_tramites'])) {
    $_SESSION['csrf_cancelar_tramites'] = bin2hex(random_bytes(32));
}

$es_exportacion_tramites = $_SERVER['REQUEST_METHOD'] === 'POST'
    && (isset($_POST['exportar_tramites']) || (($_GET['page'] ?? '') === 'tramites/acciones/exportar_tramites_seleccionados'));

if ($es_exportacion_tramites) {
    if (!$puede_exportar_tramites) {
        http_response_code(403);
        die('No tiene permiso para exportar tramites.');
    }

    if (!class_exists('ZipArchive')) {
        http_response_code(500);
        die('La extension ZipArchive no esta disponible en el servidor.');
    }

    $autoload_exportacion = dirname(__DIR__, 3) . '/vendor/autoload.php';
    if (!is_file($autoload_exportacion)) {
        http_response_code(500);
        die('No se encontro vendor/autoload.php para generar el Excel.');
    }
    require_once $autoload_exportacion;

    $csrf = $_POST['csrf_token'] ?? '';
    if (!is_string($csrf) || !hash_equals($_SESSION['csrf_cancelar_tramites'], $csrf)) {
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

    $nombreSeguro = static function (string $nombre): string {
        $nombre = trim($nombre);
        $nombre = preg_replace('/[^A-Za-z0-9._ -]/', '_', $nombre);
        $nombre = trim((string) $nombre, " .-_");
        return $nombre !== '' ? $nombre : 'archivo';
    };

    $tipoProceso = static function (array $tramite): string {
        $tipo = strtoupper(trim((string) ($tramite['tipo_tramite'] ?? '')));
        if (!in_array($tipo, ['ACTUALIZACION', 'CONSERVACION'], true)) {
            $tipo = !empty($tramite['subtipo_conservacion']) ? 'CONSERVACION' : 'ACTUALIZACION';
        }
        return $tipo;
    };

    $valorLegible = static function ($valor): string {
        return str_replace('_', ' ', trim((string) $valor));
    };

    $rutasCandidatas = static function (array $tramite): array {
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
    };

    $buscarCarpeta = static function (array $tramite, string $baseAplicacion) use ($rutasCandidatas): ?array {
        foreach ($rutasCandidatas($tramite) as $rutaRelativa) {
            $ruta = $baseAplicacion . '/' . $rutaRelativa;
            if (is_dir($ruta)) {
                return [
                    'fs' => rtrim($ruta, '/\\'),
                    'relativa' => $rutaRelativa,
                ];
            }
        }
        return null;
    };

    $listarArchivos = static function (string $carpeta): array {
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

            $relativa = ltrim(substr($rutaReal, strlen($baseReal)), DIRECTORY_SEPARATOR);
            $archivos[] = [
                'fs' => $rutaReal,
                'relativa' => str_replace('\\', '/', $relativa),
                'tamano' => $item->getSize(),
            ];
        }

        usort($archivos, static function ($a, $b) {
            return strcmp($a['relativa'], $b['relativa']);
        });
        return $archivos;
    };

    $filaExcel = static function ($sheet, int $fila, array $valores): void {
        foreach ($valores as $indice => $valor) {
            $columna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($indice + 1);
            $sheet->setCellValueExplicit($columna . $fila, (string) $valor, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        }
    };

    $prepararHoja = static function ($sheet, array $encabezados): void {
        $sheet->fromArray($encabezados, null, 'A1');
        $ultimaColumna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($encabezados));
        $sheet->getStyle('A1:' . $ultimaColumna . '1')->getFont()->setBold(true);
        $sheet->freezePane('A2');
        for ($indice = 1; $indice <= count($encabezados); $indice++) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($indice))->setAutoSize(true);
        }
    };

    $hojaCompleta = static function (\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, string $titulo, array $filas) use ($prepararHoja, $filaExcel): void {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle($titulo);
        if (!$filas) {
            $sheet->setCellValueExplicit('A1', 'Sin datos', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            return;
        }

        $encabezados = [];
        foreach ($filas as $fila) {
            foreach (array_keys($fila) as $campo) {
                if (!in_array($campo, $encabezados, true)) {
                    $encabezados[] = $campo;
                }
            }
        }

        $prepararHoja($sheet, $encabezados);
        $filaHoja = 2;
        foreach ($filas as $fila) {
            $valores = [];
            foreach ($encabezados as $campo) {
                $valores[] = $fila[$campo] ?? '';
            }
            $filaExcel($sheet, $filaHoja, $valores);
            $filaHoja++;
        }
    };

    $placeholders = implode(',', array_fill(0, count($codigos), '?'));
    $tipos = str_repeat('s', count($codigos));
    $sql_exportacion = "SELECT t.*, COALESCE(e.es_nombre, 'RADICADO') AS estado_actual
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

    $stmt_exportacion = $mysqli->prepare($sql_exportacion);
    if (!$stmt_exportacion) {
        http_response_code(500);
        die('No fue posible preparar la exportacion.');
    }

    $parametros = [$tipos];
    foreach ($codigos as $indice => $codigo) {
        $parametros[] = &$codigos[$indice];
    }
    if (!call_user_func_array([$stmt_exportacion, 'bind_param'], $parametros) || !$stmt_exportacion->execute()) {
        http_response_code(500);
        die('No fue posible consultar los tramites seleccionados.');
    }

    $resultado_exportacion = $stmt_exportacion->get_result();
    $tramitesExportacion = [];
    while ($fila = $resultado_exportacion->fetch_assoc()) {
        $tramitesExportacion[$fila['cod_tramite']] = $fila;
    }
    $stmt_exportacion->close();

    if (count($tramitesExportacion) < 1) {
        http_response_code(404);
        die('No se encontraron tramites activos para exportar.');
    }

    $stmtPredios = $mysqli->prepare(
        "SELECT *
         FROM tramite_info_predio
         WHERE info_cod_tramite IN ($placeholders)
         ORDER BY info_cod_tramite, nombre_propietario_tram"
    );
    if (!$stmtPredios) {
        http_response_code(500);
        die('No fue posible preparar la informacion predial.');
    }

    $parametrosPredios = [$tipos];
    foreach ($codigos as $indice => $codigo) {
        $parametrosPredios[] = &$codigos[$indice];
    }
    if (!call_user_func_array([$stmtPredios, 'bind_param'], $parametrosPredios) || !$stmtPredios->execute()) {
        http_response_code(500);
        die('No fue posible consultar la informacion predial.');
    }

    $resultadoPredios = $stmtPredios->get_result();
    $prediosPorTramite = [];
    while ($filaPredio = $resultadoPredios->fetch_assoc()) {
        $prediosPorTramite[$filaPredio['info_cod_tramite']][] = $filaPredio;
    }
    $stmtPredios->close();

    $baseAplicacion = dirname(__DIR__, 2);
    $documentosEsperados = [
        'Solicitud escrita' => 'sol_escrita_tramite',
        'Copia escritura / acto administrativo' => 'cop_escritura_tramite',
        'Certificado tradicion y libertad' => 'ctl_tramite',
        'Documento identidad' => 'doc_identidad_tramite',
        'Carta autorizacion' => 'carta_autorizacion_tramite',
        'Otros documentos' => 'otros_doc_tramite',
    ];

    $paquetes = [];
    foreach ($tramitesExportacion as $codTramite => $tramite) {
        $carpeta = $buscarCarpeta($tramite, $baseAplicacion);
        $archivos = $carpeta ? $listarArchivos($carpeta['fs']) : [];
        $paquetes[$codTramite] = [
            'tramite' => $tramite,
            'predios' => $prediosPorTramite[$codTramite] ?? [],
            'carpeta' => $carpeta,
            'archivos' => $archivos,
        ];
    }

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $spreadsheet->getProperties()
        ->setCreator('Arbimaps')
        ->setTitle('Exportacion tramites seleccionados');

    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Tramites');
    $prepararHoja($sheet, [
        'Cod. tramite',
        'Fecha radicacion',
        'Tipo proceso',
        'Tramite / subtipo',
        'Subtipo / detalle',
        'Estado actual',
        'Tipo solicitante',
        'Documento interesado',
        'Numero documento',
        'Solicitante',
        'Telefono',
        'Correo',
        'FMI predio',
        'NPN predio',
        'Municipio',
        'Observacion',
        'Carpeta documentos',
        'Archivos encontrados',
    ]);

    $filaHoja = 2;
    foreach ($paquetes as $codTramite => $paquete) {
        $tramite = $paquete['tramite'];
        $tipo = $tipoProceso($tramite);
        $nombreSolicitante = trim(implode(' ', array_filter([
            $tramite['primer_nombre_interesado'] ?? '',
            $tramite['segundo_nombre_interesado'] ?? '',
            $tramite['primer_apellido_interesado'] ?? '',
            $tramite['segundo_apellido_interesado'] ?? '',
        ])));
        $tramitePrincipal = $tipo === 'CONSERVACION'
            ? ($tramite['subtipo_conservacion'] ?? $tramite['mutacion_tramite'] ?? '')
            : ($tramite['mutacion_tramite'] ?? '');
        $detalle = $tipo === 'CONSERVACION'
            ? ($tramite['detalle_subtipo_conservacion'] ?? $tramite['otro_subtipo_conservacion'] ?? '')
            : ($tramite['subtipo_actualizacion'] ?? $tramite['otro_proceso_actualizacion'] ?? '');

        $filaExcel($sheet, $filaHoja, [
            $codTramite,
            $tramite['fecha_rad'] ?? '',
            $valorLegible($tipo),
            $valorLegible($tramitePrincipal),
            $valorLegible($detalle),
            $tramite['estado_actual'] ?? '',
            $tramite['tsolicitante_tramite'] ?? '',
            $tramite['documento_interesado'] ?? '',
            $tramite['num_doc_interesado'] ?? '',
            $nombreSolicitante,
            $tramite['telefono_interesado'] ?? '',
            $tramite['correo_interesado'] ?? '',
            $tramite['fmi_predio'] ?? '',
            $tramite['npn_predio'] ?? '',
            $tramite['municipio_rad'] ?? '',
            $tramite['observacion_tramite'] ?? '',
            $paquete['carpeta']['relativa'] ?? 'No encontrada',
            count($paquete['archivos']),
        ]);
        $filaHoja++;
    }

    $sheetPredios = $spreadsheet->createSheet();
    $sheetPredios->setTitle('Predios');
    $prepararHoja($sheetPredios, [
        'Cod. tramite',
        'FMI predio / terreno',
        'NPN predio',
        'Nombre propietario',
        'Tipo documento propietario',
        'Documento propietario',
        'Valor avaluo terreno',
        'Direccion predio',
        'Destino economico',
        'Area terreno',
        'Area construccion',
    ]);

    $filaHoja = 2;
    foreach ($paquetes as $codTramite => $paquete) {
        if (!$paquete['predios']) {
            $filaExcel($sheetPredios, $filaHoja, [$codTramite, 'Sin informacion predial']);
            $filaHoja++;
            continue;
        }

        foreach ($paquete['predios'] as $predio) {
            $filaExcel($sheetPredios, $filaHoja, [
                $codTramite,
                $predio['fmi_predio_tram'] ?? '',
                $predio['npn_predio_tram'] ?? '',
                $predio['nombre_propietario_tram'] ?? '',
                $predio['tipo_doc_propietario_tram'] ?? '',
                $predio['cedula_propietario_tram'] ?? '',
                $predio['valor_avaluo_terreno_tram'] ?? '',
                $predio['direccion_predio_terreno_tram'] ?? '',
                $predio['destino_econ_predio_tram'] ?? '',
                $predio['area_terr_predio_tram'] ?? '',
                $predio['area_cons_predio_tram'] ?? '',
            ]);
            $filaHoja++;
        }
    }

    $sheetDocumentos = $spreadsheet->createSheet();
    $sheetDocumentos->setTitle('Documentos');
    $prepararHoja($sheetDocumentos, [
        'Cod. tramite',
        'Tipo documento',
        'Archivo registrado',
        'Encontrado en carpeta',
        'Archivo en ZIP',
        'Tamano bytes',
    ]);

    $filaHoja = 2;
    foreach ($paquetes as $codTramite => $paquete) {
        $archivosPorNombre = [];
        foreach ($paquete['archivos'] as $archivo) {
            $archivosPorNombre[basename($archivo['relativa'])] = $archivo;
        }

        foreach ($documentosEsperados as $tipoDocumento => $campo) {
            $archivoRegistrado = (string) ($paquete['tramite'][$campo] ?? '');
            $archivoEncontrado = $archivoRegistrado !== '' && isset($archivosPorNombre[basename($archivoRegistrado)])
                ? $archivosPorNombre[basename($archivoRegistrado)]
                : null;
            $filaExcel($sheetDocumentos, $filaHoja, [
                $codTramite,
                $tipoDocumento,
                $archivoRegistrado,
                $archivoEncontrado ? 'SI' : 'NO',
                $archivoEncontrado ? $archivoEncontrado['relativa'] : '',
                $archivoEncontrado ? $archivoEncontrado['tamano'] : '',
            ]);
            $filaHoja++;
        }

        foreach ($paquete['archivos'] as $archivo) {
            $filaExcel($sheetDocumentos, $filaHoja, [
                $codTramite,
                'Archivo de carpeta',
                basename($archivo['relativa']),
                'SI',
                $archivo['relativa'],
                $archivo['tamano'],
            ]);
            $filaHoja++;
        }
    }

    $prediosCompletos = [];
    foreach ($paquetes as $paquete) {
        foreach ($paquete['predios'] as $predio) {
            $prediosCompletos[] = $predio;
        }
    }
    $hojaCompleta($spreadsheet, 'Radicacion_completa', array_values($tramitesExportacion));
    $hojaCompleta($spreadsheet, 'Predios_completo', $prediosCompletos);

    $spreadsheet->setActiveSheetIndex(0);
    $xlsxPath = tempnam(sys_get_temp_dir(), 'tramites_resumen_');
    $zipPath = tempnam(sys_get_temp_dir(), 'tramites_zip_');
    if ($xlsxPath === false || $zipPath === false) {
        http_response_code(500);
        die('No fue posible crear los archivos temporales.');
    }

    $xlsxFinalPath = $xlsxPath . '.xlsx';
    rename($xlsxPath, $xlsxFinalPath);
    (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($xlsxFinalPath);

    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        @unlink($xlsxFinalPath);
        http_response_code(500);
        die('No fue posible crear el ZIP de exportacion.');
    }

    $zip->addFile($xlsxFinalPath, 'resumen_tramites.xlsx');
    foreach ($paquetes as $codTramite => $paquete) {
        $carpetaZip = $nombreSeguro($codTramite);
        $zip->addEmptyDir($carpetaZip);

        if (!$paquete['carpeta']) {
            $zip->addFromString($carpetaZip . '/SIN_DOCUMENTOS.txt', 'No se encontro una carpeta de documentos para este radicado.');
            continue;
        }

        if (!$paquete['archivos']) {
            $zip->addFromString($carpetaZip . '/SIN_DOCUMENTOS.txt', 'La carpeta fue encontrada, pero no contiene archivos.');
            continue;
        }

        foreach ($paquete['archivos'] as $archivo) {
            $zip->addFile($archivo['fs'], $carpetaZip . '/documentos/' . $archivo['relativa']);
        }
    }

    $zip->close();
    @unlink($xlsxFinalPath);

    $filename = 'tramites_seleccionados_' . date('Ymd_His') . '.zip';
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($zipPath));
    header('Cache-Control: max-age=0');
    header('X-Content-Type-Options: nosniff');
    readfile($zipPath);
    @unlink($zipPath);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_tramites'])) {
    if (!$puede_cancelar_tramites) {
        http_response_code(403);
        die('No tiene permiso para cancelar trámites.');
    }

    $csrf = $_POST['csrf_token'] ?? '';
    if (!is_string($csrf) || !hash_equals($_SESSION['csrf_cancelar_tramites'], $csrf)) {
        http_response_code(419);
        die('La sesión del formulario venció. Recargue la página e intente nuevamente.');
    }

    $codigos = $_POST['codigos'] ?? [];
    $motivo = trim((string)($_POST['motivo_cancelacion'] ?? ''));
    $codigos = is_array($codigos)
        ? array_values(array_unique(array_filter(array_map('trim', $codigos))))
        : [];

    if (count($codigos) < 1 || count($codigos) > 200) {
        $_SESSION['cancelacion_flash'] = ['tipo' => 'danger', 'mensaje' => 'Seleccione entre 1 y 200 trámites.'];
    } elseif (mb_strlen($motivo) < 10 || mb_strlen($motivo) > 1000) {
        $_SESSION['cancelacion_flash'] = ['tipo' => 'danger', 'mensaje' => 'El motivo debe tener entre 10 y 1000 caracteres.'];
    } else {
        foreach ($codigos as $codigo) {
            if (!preg_match('/^[A-Za-z0-9_-]{1,25}$/', $codigo)) {
                $_SESSION['cancelacion_flash'] = ['tipo' => 'danger', 'mensaje' => 'Uno de los códigos seleccionados no es válido.'];
                redirigirConsultaTramites();
            }
        }

        $roles_autorizados = array_values(array_intersect(['administrador', 'director_catastro'], getRolesUsuario()));
        $rol_cancelador = $roles_autorizados[0] ?? ($_SESSION['rol_usuario'] ?? '');
        $nombre_cancelador = trim(($_SESSION['nombre_usuario'] ?? '') . ' ' . ($_SESSION['apellido_usuario'] ?? ''));
        $id_cancelador = (int)($_SESSION['id_usuario'] ?? 0);
        $cedula_cancelador = (string)($_SESSION['cedula_usuario'] ?? '');
        $placeholders = implode(',', array_fill(0, count($codigos), '?'));
        $tipos_codigos = str_repeat('s', count($codigos));

        try {
            $mysqli->begin_transaction();

            $sql_validar = "SELECT t.cod_tramite
                FROM tramite_radicacion t
                LEFT JOIN tramites_cancelados c
                    ON c.cod_tramite = t.cod_tramite AND c.estado = 'CANCELADO'
                WHERE t.cod_tramite IN ($placeholders)
                  AND c.id_cancelacion IS NULL
                FOR UPDATE";
            $stmt_validar = $mysqli->prepare($sql_validar);
            if (!$stmt_validar) {
                throw new RuntimeException($mysqli->error);
            }
            $parametros_validacion = [$tipos_codigos];
            foreach ($codigos as $indice => $codigo_parametro) {
                $parametros_validacion[] = &$codigos[$indice];
            }
            if (!call_user_func_array([$stmt_validar, 'bind_param'], $parametros_validacion)) {
                throw new RuntimeException($stmt_validar->error);
            }
            if (!$stmt_validar->execute()) {
                throw new RuntimeException($stmt_validar->error);
            }
            $resultado_validar = $stmt_validar->get_result();
            $codigos_validos = [];
            while ($fila = $resultado_validar->fetch_assoc()) {
                $codigos_validos[] = $fila['cod_tramite'];
            }
            $stmt_validar->close();

            if (count($codigos_validos) !== count($codigos)) {
                throw new RuntimeException('Uno o más trámites no existen o ya fueron cancelados.');
            }

            $sql_cancelar = "INSERT INTO tramites_cancelados (
                    cod_tramite, motivo, estado, estado_anterior, asignacion_id_anterior,
                    cancelado_por_id, cancelado_por_cedula,
                    cancelado_por_nombre, cancelado_por_rol, fecha_cancelacion
                ) VALUES (?, ?, 'CANCELADO', ?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    motivo = VALUES(motivo),
                    estado = 'CANCELADO',
                    estado_anterior = VALUES(estado_anterior),
                    asignacion_id_anterior = VALUES(asignacion_id_anterior),
                    cancelado_por_id = VALUES(cancelado_por_id),
                    cancelado_por_cedula = VALUES(cancelado_por_cedula),
                    cancelado_por_nombre = VALUES(cancelado_por_nombre),
                    cancelado_por_rol = VALUES(cancelado_por_rol),
                    fecha_cancelacion = NOW(),
                    reactivado_por_id = NULL,
                    reactivado_por_nombre = NULL,
                    reactivado_por_rol = NULL,
                    fecha_reactivacion = NULL,
                    motivo_reactivacion = NULL";
            $stmt_cancelar = $mysqli->prepare($sql_cancelar);
            if (!$stmt_cancelar) {
                throw new RuntimeException($mysqli->error);
            }

            $sql_estado = "INSERT INTO estados_tramite (
                    es_nombre, es_tipo, es_descripcion, es_dias_disparador,
                    es_disparador_evento, es_rol_asociado, estado, asignacion_id, cod_tramite
                ) VALUES ('CANCELADO', 'manual', ?, NULL, 'cancelacion', ?, 'INACTIVO', ?, ?)";
            $stmt_estado = $mysqli->prepare($sql_estado);
            if (!$stmt_estado) {
                throw new RuntimeException($mysqli->error);
            }

            $sql_asignacion = "SELECT es_nombre, asignacion_id
                FROM estados_tramite
                WHERE cod_tramite = ?
                ORDER BY id DESC
                LIMIT 1";
            $stmt_asignacion = $mysqli->prepare($sql_asignacion);
            if (!$stmt_asignacion) {
                throw new RuntimeException($mysqli->error);
            }

            foreach ($codigos as $codigo) {
                $stmt_asignacion->bind_param('s', $codigo);
                if (!$stmt_asignacion->execute()) {
                    throw new RuntimeException($stmt_asignacion->error);
                }
                $estado_previo = $stmt_asignacion->get_result()->fetch_assoc();
                $estado_anterior = $estado_previo['es_nombre'] ?? 'RADICADO';
                $asignacion_id = (int)($estado_previo['asignacion_id'] ?? 0);

                $stmt_cancelar->bind_param(
                    'sssiisss',
                    $codigo,
                    $motivo,
                    $estado_anterior,
                    $asignacion_id,
                    $id_cancelador,
                    $cedula_cancelador,
                    $nombre_cancelador,
                    $rol_cancelador
                );
                if (!$stmt_cancelar->execute()) {
                    throw new RuntimeException($stmt_cancelar->error);
                }

                if ($asignacion_id > 0) {
                    $descripcion_estado = 'Trámite cancelado. Motivo: ' . $motivo;
                    $stmt_estado->bind_param('ssis', $descripcion_estado, $rol_cancelador, $asignacion_id, $codigo);
                    if (!$stmt_estado->execute()) {
                        throw new RuntimeException($stmt_estado->error);
                    }
                }
            }

            $stmt_cancelar->close();
            $stmt_estado->close();
            $stmt_asignacion->close();
            $mysqli->commit();
            $_SESSION['cancelacion_flash'] = [
                'tipo' => 'success',
                'mensaje' => count($codigos) . ' trámite(s) cancelado(s) correctamente.'
            ];
        } catch (Throwable $e) {
            $mysqli->rollback();
            $_SESSION['cancelacion_flash'] = [
                'tipo' => 'danger',
                'mensaje' => 'No fue posible cancelar los trámites: ' . $e->getMessage()
            ];
        }
    }

    redirigirConsultaTramites();
}

$cancelacion_flash = $_SESSION['cancelacion_flash'] ?? null;
unset($_SESSION['cancelacion_flash']);

if ($_POST) {
    $usuario_cons   = $_POST['usuario_cons'];
    $password_cons  = $_POST['password_cons'];
    $sql = "SELECT 
                id_usuario, 
                usuario_cons, 
                password_cons, 
                nombre_usuario, 
                apellido_usuario, 
                rol_usuario, 
                cedula_usuario 
            FROM usuarios_cons 
            WHERE usuario_cons='$usuario_cons'";
    $resultado = $mysqli->query($sql);
    if (!$resultado) {
        die("Error en la consulta SQL: " . $mysqli->error);
    }
    $num = $resultado->num_rows;
    if ($num > 0) {
        $row = $resultado->fetch_assoc();
        if ($password_cons == $row['password_cons']) {
            $_SESSION['id_usuario']         = $row['id_usuario'];
            $_SESSION['usuario_cons']       = $row['usuario_cons'];
            $_SESSION['nombre_usuario']     = $row['nombre_usuario'];
            $_SESSION['apellido_usuario']   = $row['apellido_usuario'];
            $_SESSION['rol_usuario']        = $row['rol_usuario'];
            $_SESSION['cedula_usuario']     = $row['cedula_usuario'];
            header("Location: inicio.php");
            exit();
        } else {
            $error_login = "Usuario o contraseña incorrectos.";
        }
    } else {
        $error_login = "Usuario o contraseña incorrectos.";
    }
}
$sql = "SELECT 
            t.cod_tramite, 
            t.fecha_rad, 
            t.mutacion_tramite, 
            t.primer_nombre_interesado, 
            t.primer_apellido_interesado, 
            t.fmi_predio,
            t.municipio_rad,
            e.es_nombre
        FROM tramite_radicacion t
        LEFT JOIN estados_tramite e 
            ON e.id = (
                SELECT MAX(e2.id)
                FROM estados_tramite e2
                WHERE e2.cod_tramite = t.cod_tramite
            )
        WHERE NOT EXISTS (
            SELECT 1
            FROM tramites_cancelados tc
            WHERE tc.cod_tramite = t.cod_tramite
              AND tc.estado = 'CANCELADO'
        )
        ORDER BY t.fecha_rad DESC";
$resultado = $mysqli->query($sql);
$tramites = [];
if ($resultado) {
    while ($row = $resultado->fetch_assoc()) {
        $tramites[] = $row;
    }
}
?>
<style>
    .consultar-tramite-page {
        background-color: #EDEDED;
        color: #0A2C1B;
        min-height: 100%;
    }

    .consultar-tramite-page h2 {
        color: #0A2C1B !important;
        font-weight: 700 !important;
        letter-spacing: 0;
    }

    .consultar-tramite-page small {
        color: #7F8E85;
    }

    .consultar-tramite-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
        margin-bottom: 1.25rem;
    }

    .consultar-tramite-nav-btn {
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 61%, rgba(18, 64, 25, 1) 98%) !important;
        border: 1px solid rgba(192, 210, 200, 0.82) !important;
        border-radius: 18px !important;
        color: #ffffff !important;
        font-size: 0.82rem;
        font-weight: 700;
        min-height: 42px;
        padding: 0.65rem 1rem;
        transition: transform 0.22s ease, box-shadow 0.22s ease;
    }

    .consultar-tramite-nav-btn:hover {
        box-shadow: 0 10px 22px rgba(10, 44, 27, 0.12);
        color: #0A2C1B !important;
        transform: translateY(-1px);
    }

    .consultar-tramite-card {
        background: #ffffff;
        border: 1px solid rgba(192, 210, 200, 0.78);
        border-radius: 18px !important;
        box-shadow: 0 14px 30px rgba(10, 44, 27, 0.08) !important;
        overflow: hidden;
    }

    .consultar-tramite-card .card-header {
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%) !important;
        border: 0;
        color: #ffffff !important;
        padding: 0.95rem 1.15rem !important;
    }

    .consultar-tramite-card .card-header h6 {
        font-size: 0.86rem;
        font-weight: 700;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .consultar-tramite-selection {
        background: transparent;
        border: none;
        border-radius: 16px;
        padding:  0.75rem 0rem;
    }

    .consultar-tramite-selection #contadorSeleccionados {
        color: #C2D4CA !important;
        font-weight: 700;
    }

    #dataTable {
        border-collapse: separate !important;
        border-spacing: 0;
        color: #0A2C1B;
    }

    #dataTable thead th {
        background-color: #EDEDED !important;
        border: 0 !important;
        color: #0A2C1B !important;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.85rem 0.65rem;
        text-align: center;
        text-transform: uppercase;
        vertical-align: middle;
    }

    #dataTable tbody td {
        border: 0 !important;
        border-bottom: 1px solid #E3E8E5 !important;
        font-size: 0.83rem;
        padding: 0.78rem 0.65rem;
        vertical-align: middle;
    }

    #dataTable tbody tr:hover td {
        background-color: #F6F8F7;
        color: #0A2C1B !important;
    }

    #dataTable a {
        color: #0A5F5E;
        font-weight: 700;
        text-decoration: none;
    }

    #dataTable .btn {
        border: 0 !important;
        border-radius: 14px !important;
        font-weight: 700;
        min-width: 72px;
        transition: transform 0.22s ease, box-shadow 0.22s ease;
    }

    #dataTable .btn:hover {
        box-shadow: 0 10px 20px rgba(10, 44, 27, 0.16);
        transform: scale(1.02);
    }

    #dataTable .btn[style*="#002F55"],
    #btnCerrarModalDescarga {
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%) !important;
        color: #ffffff !important;
    }

    #dataTable .btn[style*="#66CC99"] {
        background: #A2C985 !important;
        color: #0A2C1B !important;
    }

    #btnExportarSeleccionados {
        /* background: #A2C985 !important; */
        border: 0 !important;
        border-radius: 14px !important;
        color: #0A2C1B !important;
        font-weight: 700;
    }

    #btnAbrirCancelacion {
        border: 0 !important;
        border-radius: 14px !important;
        font-weight: 700;
    }

    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 0.85rem;
        text-align: left !important;
    }

    .dataTables_wrapper .dataTables_filter label,
    .dataTables_wrapper .dataTables_length label {
        color: #7F8E85;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #C0D2C8;
        border-radius: 18px;
        color: #0A2C1B;
        font-size: 0.82rem;
        margin-left: 0.5rem;
        outline: none;
        padding: 0.45rem 0.85rem;
    }

    .dataTables_wrapper .dataTables_filter input:focus,
    .dataTables_wrapper .dataTables_length select:focus {
        border-color: #0A2C1B;
        box-shadow: 0 0 0 0.15rem rgba(10, 44, 27, 0.12);
    }

    .dataTables_wrapper .dataTables_info {
        color: #7F8E85;
        font-size: 0.78rem;
        padding-top: 1rem;
    }

    .dataTables_wrapper .dataTables_paginate .pagination {
        align-items: center;
        gap: 0.25rem;
    }

    .dataTables_wrapper .dataTables_paginate .page-link {
        align-items: center;
        background-color: #F6F8F7 !important;
        border: 1px solid transparent !important;
        border-radius: 18px !important;
        color: #0A2C1B !important;
        display: flex;
        font-size: 0.8rem;
        height: 31px;
        justify-content: center;
        min-width: 31px;
    }

    .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
        background-color: #0A2C1B !important;
        border-color: #0A2C1B !important;
        color: #ffffff !important;
    }

    .dataTables_wrapper .dataTables_paginate .page-link:hover {
        background-color: #C0D2C8 !important;
        color: #0A2C1B !important;
    }

    #modalDescargaArchivos .modal-content,
    #modalCancelarTramites .modal-content {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
    }

    #modalDescargaArchivos .modal-header,
    #modalCancelarTramites .modal-header {
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%) !important;
        color: #ffffff !important;
    }

    #barraProgresoExportacion {
        background: linear-gradient(90deg, #0A2C1B, #0A5F5E, #029F96) !important;
    }

    #modalCancelarTramites textarea {
        border: 1px solid #C0D2C8;
        border-radius: 16px;
        color: #0A2C1B;
    }

    #modalCancelarTramites textarea:focus {
        border-color: #0A2C1B;
        box-shadow: 0 0 0 0.15rem rgba(10, 44, 27, 0.12);
    }

    /* Número activo en la paginación */
    .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
        background-color: #002F55 !important;
        /* Rojo */
        border-color: #002F55 !important;
        color: #fff !important;
    }

    /* Hover sobre números */
    .dataTables_wrapper .dataTables_paginate .page-link:hover {
        background-color: #457b9d !important;
        /* Azul oscuro */
        color: #fff !important;
    }

    /* Texto de los links de paginación */
    .dataTables_wrapper .dataTables_paginate .page-link {
        color: #002f55 !important;
        /* Azul Bootstrap */
        border-radius: 8px;
        /* Bordes más redondeados */
        margin: 0 2px;
    }

    .consultar-tramite-page .dataTables_wrapper .dataTables_paginate .page-link {
        align-items: center;
        background-color: #F6F8F7 !important;
        border: 1px solid transparent !important;
        border-radius: 18px !important;
        color: #0A2C1B !important;
        display: flex;
        font-size: 0.8rem;
        height: 31px;
        justify-content: center;
        min-width: 31px;
    }

    .consultar-tramite-page .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
        background-color: #0A2C1B !important;
        border-color: #0A2C1B !important;
        color: #ffffff !important;
    }

    .consultar-tramite-page .dataTables_wrapper .dataTables_paginate .page-link:hover {
        background-color: #C0D2C8 !important;
        color: #0A2C1B !important;
    }

    .consultar-tramite-page #dataTable thead th {
        background-color: #EDEDED !important;
        border: 0 !important;
        color: #0A2C1B !important;
    }

    .consultar-tramite-page #dataTable tbody td {
        border: 0 !important;
        border-bottom: 1px solid #E3E8E5 !important;
    }

    .consultar-tramite-page .dataTables_wrapper .dataTables_filter {
        text-align: left !important;
    }

    .consultar-tramite-page .dataTables_wrapper .dataTables_filter input,
    .consultar-tramite-page .dataTables_wrapper .dataTables_length select {
        border: 1px solid #C0D2C8 !important;
        border-radius: 18px !important;
        color: #0A2C1B !important;
        font-size: 0.82rem;
        padding: 0.45rem 0.85rem;
    }

    .consultar-tramite-page .dataTables_wrapper .dataTables_paginate .page-link {
        background-color: #F6F8F7 !important;
        border: 1px solid transparent !important;
        border-radius: 18px !important;
        color: #0A2C1B !important;
    }

    .consultar-tramite-page .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
        background-color: #0A2C1B !important;
        border-color: #0A2C1B !important;
        color: #ffffff !important;
    }

    .consultar-tramite-page #dataTable_wrapper>.row:first-child {
        align-items: center;
        display: flex;
        margin: 0 0 1rem 0;
        row-gap: 0.75rem;
    }

    .consultar-tramite-page #dataTable_wrapper>.row:first-child>[class*="col-"]:first-child {
        display: flex;
        justify-content: flex-start;
        padding-left: 0;
    }

    .consultar-tramite-page #dataTable_wrapper>.row:first-child>[class*="col-"]:last-child {
        display: flex;
        justify-content: flex-end;
        padding-right: 0;
    }

    .consultar-tramite-page #dataTable_wrapper .dataTables_length,
    .consultar-tramite-page #dataTable_wrapper .dataTables_filter {
        align-items: center;
        display: flex;
        margin: 0;
    }

    .consultar-tramite-page #dataTable_wrapper .dataTables_length label,
    .consultar-tramite-page #dataTable_wrapper .dataTables_filter label {
        align-items: center;
        color: #7F8E85;
        display: flex;
        font-size: 0.86rem;
        gap: 0.45rem;
        margin-bottom: 0;
        white-space: nowrap;
    }

    .consultar-tramite-page #dataTable_wrapper .dataTables_length select {
        appearance: none;
        -moz-appearance: none;
        -webkit-appearance: none;
        background-color: #ffffff;
        background-position: right 0.75rem center;
        background-repeat: no-repeat;
        background-size: 16px 12px;
        border: 1px solid #C0D2C8 !important;
        border-radius: 16px !important;
        color: #0A2C1B;
        height: 38px;
        min-width: 76px;
        padding: 0.35rem 2rem 0.35rem 0.9rem;
    }

    .consultar-tramite-page #dataTable_wrapper .dataTables_filter input {
        border: 1px solid #C0D2C8 !important;
        border-radius: 18px !important;
        box-shadow: none !important;
        color: #0A2C1B;
        height: 38px;
        margin-left: 0.5rem;
        min-width: 190px;
        padding: 0.35rem 0.95rem;
    }

    @media (max-width: 767.98px) {
        .consultar-tramite-page #dataTable_wrapper>.row:first-child,
        .consultar-tramite-page #dataTable_wrapper>.row:first-child>[class*="col-"]:first-child,
        .consultar-tramite-page #dataTable_wrapper>.row:first-child>[class*="col-"]:last-child,
        .consultar-tramite-page #dataTable_wrapper .dataTables_filter,
        .consultar-tramite-page #dataTable_wrapper .dataTables_filter label,
        .consultar-tramite-page #dataTable_wrapper .dataTables_filter input {
            justify-content: center;
            width: 100%;
        }
    }

    .boton_Gene_Reporte {
        display: flex;
        align-items: center;
        justify-content: center;
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%);
        border: none;
        font-size: 1rem;
        border-radius: 20px;
        text-decoration: none;
        color: #ffffff !important;
    }

    .boton_Gene_Reporte2 {
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(164deg, rgba(214, 221, 218, 0.83) 0%, rgba(255, 255, 255, 0.37) 15%, rgba(255, 255, 255, 1) 85%, rgba(214, 221, 218, 0.68) 100%);
        border: 1px solid #0A2C1B;
        color: #0A2C1B !important;
        font-size: 1rem;
        border-radius: 20px;
        text-decoration: none;
    }

    .boton_Gene_Reporte,
    .boton_Gene_Reporte2,
    #btnAbrirCancelacion,
    #btnExportarSeleccionados {
        transition: transform 0.25s ease, color 0.25s ease;
    }

    .boton_Gene_Reporte,
    #btnAbrirCancelacion,
    #btnExportarSeleccionados i {
        display: inline-block;
        transition: transform 0.25s ease, color 0.25s ease;
    }

    .boton_Gene_Reporte2 i {
        display: inline-block;
        transition: transform 0.25s ease, color 0.25s ease;
    }

    .boton_Gene_Reporte:hover {
        color: #ffffff !important;
        transform: scale(1.01);
        font-weight: bold;
    }

    #btnAbrirCancelacion:hover,
    #btnExportarSeleccionados:hover {
        transform: scale(1.01);
        font-weight: bold;
    }

    .boton_Gene_Reporte2:hover {
        transform: scale(1.01);
        font-weight: bold;
    }
</style>
<!-- DataTables CSS con integración Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container-fluid rounded-4 p-3 consultar-tramite-page">
    <?php if ($cancelacion_flash): ?>
        <div class="alert alert-<?php echo htmlspecialchars($cancelacion_flash['tipo']); ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($cancelacion_flash['mensaje']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center  mb-3 px-1">
        <div class="my-4 text-center">
            <h2 class=" mb-0 fw-bold" style="color: #0A2C1B; font-weight: 700 !important">TRÁMITES CATASTRALES</h2>
            <small>Consulta y exporta el listado de los trámites catatrales.</small>
        </div>

        <div class="d-flex align-items-center justify-content-center gap-2">

            <a href="index.php?page=tramites/asignacion_tramites" class="text-white" style="text-decoration: none;">
                <button type="button"
                    class="boton_Gene_Reporte p-3 px-4 " style="background-color: #fff !important;">
                    <i class="bi bi-person-workspace  me-2 "></i>
                    <small class="h-100 text-white">Ver trámites asignados</small>
                </button>
            </a>

            <a href="index.php?page=tramites/revision_tramites" class="text-white" style="text-decoration: none;">
                <button type="button"
                    class="boton_Gene_Reporte2 p-3 px-4 " style="background-color: #fff !important;">
                    <i class="bi bi-file-earmark-break  me-2 "></i>
                    <small class="h-100" style="color: #0A2C1B;">Ver trámites en revisión</small>
                </button>
            </a>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row ">
            <div class="col-lg-12 px-0">
                <div class="card consultar-tramite-card mb-4">
                    <div
                        class="card-header py-3 text-center d-flex flex-row align-items-center justify-content-between" style="background-color: #002F55;">
                        <!-- <div class="m-0 font-weight-bold text-white">Información de Trámites</div> -->
                        <div class="text-xs text-white text-uppercase my-2 " style="font-size:1rem; font-weight:500;">
                            Información de trámites</div>
                        <!-- <div class="dropdown no-arrow">
                            <a class="dropdown-toggle text-white" href="#" role="button" id="dropdownMenuLink"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-white-400"></i>
                            </a>
                        </div> -->

                        <?php if ($puede_seleccionar_tramites): ?>
                            <div class="d-flex flex-wrap align-items-center justify-content-between shadow-sm gap-4  consultar-tramite-selection">
                                <span id="contadorSeleccionados" class="text-white small">0 trámites seleccionados</span>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php if ($puede_exportar_tramites): ?>
                                        <form method="post" action="vistas/tramites/exportar_tramites_seleccionados.php"
                                            id="formExportarTramites" class="m-0">
                                            <input type="hidden" name="csrf_token"
                                                value="<?php echo htmlspecialchars($_SESSION['csrf_cancelar_tramites'], ENT_QUOTES); ?>">
                                            <input type="hidden" name="exportar_tramites" value="1">
                                            <div id="codigosExportacion"></div>
                                            <button type="submit" id="btnExportarSeleccionados" class="btn btn-sm text-dark px-3 p-2" style="background-color: #ADDF2F;" disabled>
                                                <i class="bi bi-file-earmark-zip me-1"></i> Exportar seleccionados
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($puede_cancelar_tramites): ?>
                                        <button type="button" id="btnAbrirCancelacion" class="btn btn-sm text-white px-3 p-2" style="background-color: #DD0000;" disabled
                                            data-bs-toggle="modal" data-bs-target="#modalCancelarTramites">
                                            <i class="bi bi-x-circle me-1"></i> Cancelar seleccionados
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                    <div class="card-body">


                        <div class="table-responsive">
                            <table class="table" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <?php if ($puede_seleccionar_tramites): ?>
                                            <th class="text-center" data-orderable="false">
                                                <input type="checkbox" id="seleccionarTodos" class="form-check-input" aria-label="Seleccionar todos">
                                            </th>
                                        <?php endif; ?>
                                        <th style="text-align: center; vertical-align: middle;">Fecha Radicación</th>
                                        <th style="text-align: center; vertical-align: middle;">Cod. Tramite</th>
                                        <th style="text-align: center; vertical-align: middle;">Solicitud Tramite</th>
                                        <th style="text-align: center; vertical-align: middle;">Nombre y Apellido Solicitante</th>
                                        <th style="text-align: center; vertical-align: middle;">FMI Predio</th>
                                        <th style="text-align: center; vertical-align: middle;">Municipio</th>
                                        <th style="text-align: center; vertical-align: middle;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tramites as $tramite): ?>
                                        <tr>
                                            <?php if ($puede_seleccionar_tramites): ?>
                                                <td class="text-center align-middle">
                                                    <input type="checkbox" class="form-check-input tramite-checkbox"
                                                        value="<?php echo htmlspecialchars($tramite['cod_tramite'], ENT_QUOTES); ?>"
                                                        aria-label="Seleccionar <?php echo htmlspecialchars($tramite['cod_tramite'], ENT_QUOTES); ?>">
                                                </td>
                                            <?php endif; ?>
                                            <td style="text-align: center; vertical-align: middle;"><?php echo date("Y-m-d H:i", strtotime($tramite['fecha_rad'])); ?></td>
                                            <td style="text-align: center; vertical-align: middle;"><a href="index.php?page=tramites/acciones/ver_tramite_rad&cod=<?php echo urlencode($tramite['cod_tramite']); ?>">
                                                    <?php echo htmlspecialchars($tramite['cod_tramite']); ?></a>
                                            </td>
                                            <td style="text-align: center; vertical-align: middle;"><?php echo htmlspecialchars($tramite['mutacion_tramite']); ?></td>
                                            <td style="text-align: center; vertical-align: middle;"><?php echo htmlspecialchars($tramite['primer_nombre_interesado'] . ' ' . $tramite['primer_apellido_interesado']); ?></td>
                                            <td style="text-align: center; vertical-align: middle;"><?php echo htmlspecialchars($tramite['fmi_predio']); ?></td>
                                            <td style="text-align: center; vertical-align: middle;"><?php echo htmlspecialchars($tramite['municipio_rad']); ?></td>
                                            <td style="text-align: center; vertical-align: middle;">
                                                <a href="index.php?page=tramites/acciones/ver_tramite_rad&cod=<?php echo urlencode($tramite['cod_tramite']); ?>"
                                                    class="btn btn-sm text-white  my-1" style="background-color: #002F55">
                                                    Ver
                                                </a>
                                                <!-- <a href="index.php?page=tramites/acciones/editar_tramite&cod=<?php echo urlencode($tramite['cod_tramite']); ?>"
                                                class="btn btn-sm shadow my-1 text-white" style="background-color:#0F5699">Editar</a> -->
                                                <?php if (!in_array(strtoupper($tramite['es_nombre'] ?? ''), ['ASIGNADO', 'REVISION', 'ENTREGADO', 'DEVUELTO'])): ?>
                                                    <a href="index.php?page=tramites/acciones/asignar_tram_procedencia&cod=<?php echo urlencode($tramite['cod_tramite']); ?>"
                                                        class="btn btn-sm shadow my-1" style="background-color:#66CC99">Asignar</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDescargaArchivos" tabindex="-1" aria-labelledby="tituloModalDescargaArchivos" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #002F55; color: white;">
                <h5 class="modal-title" id="tituloModalDescargaArchivos">Exportando trámites</h5>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-3 fw-semibold" id="textoProgresoExportacion">Preparando la exportación...</p>
                <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" id="barraProgresoExportacion" style="width: 0%; background-color: #002F55;"></div>
                </div>
                <div class="small text-muted mt-2" id="porcentajeProgresoExportacion">0%</div>
                <div class="mt-3 d-none" id="mensajeErrorExportacion"></div>
                <button type="button" class="btn btn-primary mt-3 d-none" id="btnCerrarModalDescarga" data-bs-dismiss="modal">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<?php if ($puede_cancelar_tramites): ?>
    <div class="modal fade" id="modalCancelarTramites" tabindex="-1" aria-labelledby="tituloCancelarTramites" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="post" class="modal-content" id="formCancelarTramites">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloCancelarTramites">Cancelar trámites</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning py-2">
                        Los trámites seleccionados saldrán de las bandejas activas y pasarán a “Trámites cancelados”.
                    </div>
                    <p class="mb-2"><strong id="resumenCancelacion"></strong></p>
                    <div id="codigosCancelacion"></div>
                    <label for="motivoCancelacion" class="form-label">Motivo de cancelación <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="motivoCancelacion" name="motivo_cancelacion"
                        rows="4" minlength="10" maxlength="1000" required
                        placeholder="Explique por qué se cancelan los trámites seleccionados"></textarea>
                    <div class="form-text">Entre 10 y 1000 caracteres. El mismo motivo se aplicará a toda la selección.</div>
                    <input type="hidden" name="csrf_token"
                        value="<?php echo htmlspecialchars($_SESSION['csrf_cancelar_tramites'], ENT_QUOTES); ?>">
                    <input type="hidden" name="cancelar_tramites" value="1">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Volver</button>
                    <button type="submit" class="btn btn-danger">Confirmar cancelación</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    function idiomaDataTableEspanol() {
        return {
            decimal: "",
            emptyTable: "No hay información disponible en la tabla",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            lengthMenu: "Mostrar _MENU_ registros",
            loadingRecords: "Cargando...",
            processing: "Procesando...",
            search: "Buscar:",
            zeroRecords: "No se encontraron registros coincidentes",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            },
            aria: {
                sortAscending: ": activar para ordenar la columna ascendente",
                sortDescending: ": activar para ordenar la columna descendente"
            }
        };
    }

    $(document).ready(function() {
        const tabla = $('#dataTable').DataTable({
            order: [
                [<?php echo $puede_seleccionar_tramites ? 1 : 0; ?>, 'desc']
            ],
            language: idiomaDataTableEspanol()
        });

        <?php if ($puede_seleccionar_tramites): ?>
            const seleccionados = new Set();
            const btnCancelar = document.getElementById('btnAbrirCancelacion');
            const btnExportar = document.getElementById('btnExportarSeleccionados');
            const formExportar = document.getElementById('formExportarTramites');
            const contador = document.getElementById('contadorSeleccionados');
            const seleccionarTodos = document.getElementById('seleccionarTodos');
            const modalDescarga = document.getElementById('modalDescargaArchivos');
            const barraProgreso = document.getElementById('barraProgresoExportacion');
            const textoProgreso = document.getElementById('textoProgresoExportacion');
            const porcentajeProgreso = document.getElementById('porcentajeProgresoExportacion');
            const mensajeErrorExportacion = document.getElementById('mensajeErrorExportacion');
            const btnCerrarModalDescarga = document.getElementById('btnCerrarModalDescarga');
            let intervaloProgreso = null;

            function inicializarProgresoExportacion() {
                if (barraProgreso) {
                    barraProgreso.style.width = '0%';
                    barraProgreso.setAttribute('aria-valuenow', '0');
                }
                if (textoProgreso) {
                    textoProgreso.textContent = 'Preparando la exportación...';
                }
                if (porcentajeProgreso) {
                    porcentajeProgreso.textContent = '0%';
                }
                if (mensajeErrorExportacion) {
                    mensajeErrorExportacion.className = 'mt-3 d-none';
                    mensajeErrorExportacion.textContent = '';
                }
                if (btnCerrarModalDescarga) {
                    btnCerrarModalDescarga.classList.add('d-none');
                }
            }

            function actualizarProgresoExportacion(porcentaje, texto) {
                if (barraProgreso) {
                    barraProgreso.style.width = porcentaje + '%';
                    barraProgreso.setAttribute('aria-valuenow', porcentaje);
                }
                if (textoProgreso) {
                    textoProgreso.textContent = texto;
                }
                if (porcentajeProgreso) {
                    porcentajeProgreso.textContent = porcentaje + '%';
                }
            }

            function mostrarModalDescarga() {
                inicializarProgresoExportacion();
                if (modalDescarga) {
                    const modalInstance = window.bootstrap && window.bootstrap.Modal ?
                        window.bootstrap.Modal.getOrCreateInstance(modalDescarga) :
                        null;
                    if (modalInstance) {
                        modalInstance.show();
                    } else if (window.jQuery) {
                        $(modalDescarga).modal('show');
                    }
                }
                if (intervaloProgreso) {
                    clearInterval(intervaloProgreso);
                }
                intervaloProgreso = setInterval(function() {
                    const barraActual = parseInt(barraProgreso?.getAttribute('aria-valuenow') || '0', 10);
                    const siguiente = Math.min(90, barraActual + Math.floor(Math.random() * 10) + 4);
                    actualizarProgresoExportacion(siguiente, 'Procesando la exportación...');
                }, 350);
            }

            function detenerProgresoExportacion() {
                if (intervaloProgreso) {
                    clearInterval(intervaloProgreso);
                    intervaloProgreso = null;
                }
            }

            function finalizarProgresoExportacion(conMensaje) {
                detenerProgresoExportacion();
                actualizarProgresoExportacion(100, conMensaje || 'Exportación finalizada.');
                if (btnCerrarModalDescarga) {
                    btnCerrarModalDescarga.classList.remove('d-none');
                }
                setTimeout(function() {
                    if (modalDescarga) {
                        const modalInstance = window.bootstrap && window.bootstrap.Modal ?
                            window.bootstrap.Modal.getOrCreateInstance(modalDescarga) :
                            null;
                        if (modalInstance) {
                            modalInstance.hide();
                        } else if (window.jQuery) {
                            $(modalDescarga).modal('hide');
                        }
                    }
                }, 800);
            }

            function mostrarErrorExportacion(mensaje) {
                detenerProgresoExportacion();
                if (mensajeErrorExportacion) {
                    mensajeErrorExportacion.className = 'mt-3 alert alert-danger py-2';
                    mensajeErrorExportacion.textContent = mensaje;
                }
                if (btnCerrarModalDescarga) {
                    btnCerrarModalDescarga.classList.remove('d-none');
                }
                actualizarProgresoExportacion(0, 'No se pudo completar la exportación.');
            }

            function actualizarSeleccion() {
                contador.textContent = seleccionados.size + (seleccionados.size === 1 ? ' trámite seleccionado' : ' trámites seleccionados');
                if (btnCancelar) {
                    btnCancelar.disabled = seleccionados.size === 0;
                }
                if (btnExportar) {
                    btnExportar.disabled = seleccionados.size === 0;
                }

                const checkboxesFiltrados = $(tabla.rows({
                    search: 'applied'
                }).nodes()).find('.tramite-checkbox').toArray();
                seleccionarTodos.checked = checkboxesFiltrados.length > 0 && checkboxesFiltrados.every(cb => seleccionados.has(cb.value));
                seleccionarTodos.indeterminate =
                    checkboxesFiltrados.some(cb => seleccionados.has(cb.value)) && !seleccionarTodos.checked;
            }

            $('#dataTable tbody').on('change', '.tramite-checkbox', function() {
                if (this.checked) {
                    seleccionados.add(this.value);
                } else {
                    seleccionados.delete(this.value);
                }
                actualizarSeleccion();
            });

            seleccionarTodos.addEventListener('change', function() {
                $(tabla.rows({
                    search: 'applied'
                }).nodes()).find('.tramite-checkbox').each(function() {
                    this.checked = seleccionarTodos.checked;
                    if (this.checked) {
                        seleccionados.add(this.value);
                    } else {
                        seleccionados.delete(this.value);
                    }
                });
                actualizarSeleccion();
            });

            tabla.on('draw', function() {
                $(tabla.rows({
                    page: 'current'
                }).nodes()).find('.tramite-checkbox').each(function() {
                    this.checked = seleccionados.has(this.value);
                });
                actualizarSeleccion();
            });

            function llenarCodigosSeleccionados(contenedor) {
                contenedor.innerHTML = '';
                Array.from(seleccionados).forEach(function(codigo) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'codigos[]';
                    input.value = codigo;
                    contenedor.appendChild(input);
                });
            }

            if (btnCancelar) {
                btnCancelar.addEventListener('click', function() {
                    const contenedor = document.getElementById('codigosCancelacion');
                    llenarCodigosSeleccionados(contenedor);
                    document.getElementById('resumenCancelacion').textContent =
                        'Se cancelarán ' + seleccionados.size + (seleccionados.size === 1 ? ' trámite.' : ' trámites.');
                });
            }

            if (formExportar) {
                formExportar.addEventListener('submit', async function(event) {
                    event.preventDefault();

                    if (seleccionados.size === 0) {
                        return;
                    }

                    llenarCodigosSeleccionados(document.getElementById('codigosExportacion'));
                    mostrarModalDescarga();

                    const formData = new FormData(formExportar);

                    try {
                        const response = await fetch(formExportar.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!response.ok) {
                            const textoError = await response.text();
                            throw new Error(textoError || 'No se pudo completar la exportación.');
                        }

                        const disposition = response.headers.get('content-disposition') || '';
                        const match = disposition.match(/filename="?([^";]+)"?/i);
                        const filename = match ? decodeURIComponent(match[1]) : 'tramites_seleccionados.zip';
                        const blob = await response.blob();
                        const url = window.URL.createObjectURL(blob);
                        const link = document.createElement('a');
                        link.href = url;
                        link.download = filename;
                        document.body.appendChild(link);
                        link.click();
                        link.remove();
                        window.URL.revokeObjectURL(url);

                        finalizarProgresoExportacion('La descarga ha finalizado.');
                    } catch (error) {
                        mostrarErrorExportacion(error.message || 'No se pudo completar la exportación.');
                    }
                });
            }
        <?php endif; ?>
    });
</script>
