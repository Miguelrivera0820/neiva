<?php

function td_h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function td_normalize_date($value): string
{
    $value = trim((string) $value);
    if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
        return '';
    }
    return substr($value, 0, 10);
}

function td_due_status(?string $fechaLimite): array
{
    $fecha = td_normalize_date($fechaLimite);
    if ($fecha === '') {
        return ['sin_fecha', 'Sin fecha', null];
    }

    $hoy = new DateTime('today');
    $limite = new DateTime($fecha);
    $dias = (int) $hoy->diff($limite)->format('%r%a');

    if ($dias >= 3) {
        return ['a_tiempo', 'A tiempo', $dias];
    }
    if ($dias >= 1) {
        return ['a_vencer', 'A vencer', $dias];
    }
    if ($dias >= -10) {
        return ['vencido', 'Vencido', $dias];
    }
    return ['caducado', 'Caducado', $dias];
}

function td_dashboard_filters(): array
{
    return [
        'fecha_rad_desde' => trim($_GET['fecha_rad_desde'] ?? ''),
        'fecha_rad_hasta' => trim($_GET['fecha_rad_hasta'] ?? ''),
        'fecha_asig_desde' => trim($_GET['fecha_asig_desde'] ?? ''),
        'fecha_asig_hasta' => trim($_GET['fecha_asig_hasta'] ?? ''),
        'fecha_venc_desde' => trim($_GET['fecha_venc_desde'] ?? ''),
        'fecha_venc_hasta' => trim($_GET['fecha_venc_hasta'] ?? ''),
        'fecha_resp_desde' => trim($_GET['fecha_resp_desde'] ?? ''),
        'fecha_resp_hasta' => trim($_GET['fecha_resp_hasta'] ?? ''),
        'estado' => trim($_GET['estado'] ?? ''),
        'condicion' => trim($_GET['condicion'] ?? ''),
        'rol' => trim($_GET['rol'] ?? ''),
        'usuario' => trim($_GET['usuario'] ?? ''),
        'mutacion' => trim($_GET['mutacion'] ?? ''),
        'tipo_estado' => trim($_GET['tipo_estado'] ?? ''),
    ];
}

function td_date_between(string $value, string $desde, string $hasta): bool
{
    $date = td_normalize_date($value);
    if ($desde !== '' && ($date === '' || $date < $desde)) {
        return false;
    }
    if ($hasta !== '' && ($date === '' || $date > $hasta)) {
        return false;
    }
    return true;
}

function td_filter_match(array $item, array $filters): bool
{
    if (!td_date_between($item['fecha_rad'] ?? '', $filters['fecha_rad_desde'], $filters['fecha_rad_hasta'])) {
        return false;
    }
    if (!td_date_between($item['fecha_movimiento'] ?? '', $filters['fecha_asig_desde'], $filters['fecha_asig_hasta'])) {
        return false;
    }
    if (!td_date_between($item['fecha_limite'] ?? '', $filters['fecha_venc_desde'], $filters['fecha_venc_hasta'])) {
        return false;
    }
    if (!td_date_between($item['fecha_respuesta_tramite'] ?? '', $filters['fecha_resp_desde'], $filters['fecha_resp_hasta'])) {
        return false;
    }
    if ($filters['estado'] !== '' && strcasecmp($item['estado_base'] ?? '', $filters['estado']) !== 0) {
        return false;
    }
    if ($filters['condicion'] !== '' && stripos($item['estado_tramite'] ?? '', $filters['condicion']) === false) {
        return false;
    }
    if ($filters['rol'] !== '' && strcasecmp($item['rol'] ?? '', $filters['rol']) !== 0) {
        return false;
    }
    if ($filters['usuario'] !== '' && (string) ($item['cc_usuario'] ?? '') !== $filters['usuario']) {
        return false;
    }
    if ($filters['mutacion'] !== '' && strcasecmp($item['mutacion'] ?? '', $filters['mutacion']) !== 0) {
        return false;
    }
    if ($filters['tipo_estado'] !== '' && strcasecmp($item['tipo_estado'] ?? '', $filters['tipo_estado']) !== 0) {
        return false;
    }
    return true;
}

function td_dashboard_data(mysqli $mysqli, ?array $filters = null): array
{
    $filters = $filters ?? td_dashboard_filters();

    $cerrados = [];
    $cerradosTipo = [];
    $sqlCerrados = "
        SELECT cod_radicacion_tramite, 'culminado' AS tipo FROM procede_tramite
        UNION ALL
        SELECT cod_radicacion_tramite, 'devuelto' AS tipo FROM no_procede_completar
    ";
    try {
        $resCerrados = $mysqli->query($sqlCerrados);
        while ($resCerrados && ($row = $resCerrados->fetch_assoc())) {
            $cod = $row['cod_radicacion_tramite'] ?? '';
            if ($cod !== '') {
                $cerrados[$cod] = true;
                $cerradosTipo[$cod] = $row['tipo'] ?? 'cerrado';
            }
        }
    } catch (Throwable $e) {
        $cerrados = [];
        $cerradosTipo = [];
    }

    $raw = [];
    $queries = [
        "
            SELECT
                at.asignacion_cod_tramite AS cod_tramite,
                at.asignacion_cc_usuario AS cc_usuario,
                at.asignacion_nombre_usuario AS nombre_usuario,
                at.asignacion_apellido_usuario AS apellido_usuario,
                at.asignacion_rol_usuario AS rol,
                at.asignacion_estado_tramite AS estado_tramite,
                at.fecha_limite,
                at.fecha_respuesta_tramite,
                at.asignacion_fecha_tramite AS fecha_movimiento,
                'asignacion' AS etapa,
                NULL AS fecha_rad,
                at.asig_mutacion_tramite AS mutacion,
                '' AS tipo_tramite,
                '' AS npn_predio
            FROM asignacion_tramite at
        ",
        "
            SELECT
                ha.historial_cod_tramite AS cod_tramite,
                ha.historial_cc_usuario AS cc_usuario,
                ha.historial_nombre_usuario AS nombre_usuario,
                ha.historial_apellido_usuario AS apellido_usuario,
                ha.historial_rol_usuario AS rol,
                ha.historial_estado_tramite AS estado_tramite,
                ha.fecha_limite,
                NULL AS fecha_respuesta_tramite,
                ha.historial_fecha_tramite AS fecha_movimiento,
                'asignacion' AS etapa,
                NULL AS fecha_rad,
                '' AS mutacion,
                '' AS tipo_tramite,
                '' AS npn_predio
            FROM historial_asignacion ha
        ",
        "
            SELECT
                ea.entrega_cod_tramite AS cod_tramite,
                ea.entrega_cc_usuario AS cc_usuario,
                ea.entrega_nombre_usuario AS nombre_usuario,
                ea.entrega_apellido_usuario AS apellido_usuario,
                ea.entrega_rol_usuario AS rol,
                ea.historial_estado_tramite AS estado_tramite,
                ea.fecha_limite,
                NULL AS fecha_respuesta_tramite,
                COALESCE(ea.fecha_creacion, ea.historial_fecha_tramite) AS fecha_movimiento,
                CASE
                    WHEN UPPER(COALESCE(ea.historial_estado_tramite, '')) = 'REASIGNADO'
                         AND UPPER(COALESCE((
                            SELECT et.es_nombre
                            FROM estados_tramite et
                            WHERE et.cod_tramite = ea.entrega_cod_tramite
                            ORDER BY et.id DESC
                            LIMIT 1
                         ), '')) = 'ASIGNADO'
                    THEN 'asignacion'
                    ELSE 'revision'
                END AS etapa,
                NULL AS fecha_rad,
                '' AS mutacion,
                '' AS tipo_tramite,
                '' AS npn_predio
            FROM entrega_asignacion ea
        ",
        "
            SELECT
                hr.historial_cod_tramite AS cod_tramite,
                hr.asignacion_cc_usuario AS cc_usuario,
                hr.asignacion_nombre_usuario AS nombre_usuario,
                hr.asignacion_apellido_usuario AS apellido_usuario,
                hr.asignacion_rol_usuario AS rol,
                hr.historial_estado_tramite AS estado_tramite,
                hr.fecha_limite,
                NULL AS fecha_respuesta_tramite,
                COALESCE(hr.fecha_creacion, hr.historial_fecha_tramite) AS fecha_movimiento,
                'revision' AS etapa,
                NULL AS fecha_rad,
                '' AS mutacion,
                '' AS tipo_tramite,
                '' AS npn_predio
            FROM historial_revision hr
        ",
    ];

    foreach ($queries as $sql) {
        try {
            $res = $mysqli->query($sql);
            while ($res && ($row = $res->fetch_assoc())) {
                $cod = $row['cod_tramite'] ?? '';
                if ($cod !== '') {
                    $raw[] = $row;
                }
            }
        } catch (Throwable $e) {
            continue;
        }
    }

    usort($raw, function ($a, $b) {
        $fechaA = (string)($a['fecha_movimiento'] ?? '');
        $fechaB = (string)($b['fecha_movimiento'] ?? '');
        if ($fechaA === $fechaB) {
            return strcmp((string)($b['etapa'] ?? ''), (string)($a['etapa'] ?? ''));
        }
        return strcmp($fechaB, $fechaA);
    });

    if (!empty($raw)) {
        $codigos = [];
        foreach ($raw as $row) {
            $cod = $row['cod_tramite'] ?? '';
            if ($cod !== '') {
                $codigos[$cod] = true;
            }
        }
        $codigos = array_keys($codigos);
        $escaped = array_map(fn($cod) => "'" . $mysqli->real_escape_string($cod) . "'", $codigos);
        $radicacionMap = [];
        if (!empty($escaped)) {
            $sqlRadicacion = "
                SELECT cod_tramite, fecha_rad, mutacion_tramite AS mutacion, tipo_tramite, npn_predio, fecha_limite_respuesta
                FROM tramite_radicacion
                WHERE cod_tramite IN (" . implode(',', $escaped) . ")
            ";
            try {
                $resRadicacion = $mysqli->query($sqlRadicacion);
                while ($resRadicacion && ($row = $resRadicacion->fetch_assoc())) {
                    $radicacionMap[$row['cod_tramite']] = $row;
                }
            } catch (Throwable $e) {
                $radicacionMap = [];
            }
        }

        foreach ($raw as &$row) {
            $cod = $row['cod_tramite'] ?? '';
            if ($cod !== '' && isset($radicacionMap[$cod])) {
                $row['fecha_rad'] = $radicacionMap[$cod]['fecha_rad'] ?? ($row['fecha_rad'] ?? '');
                $row['tipo_tramite'] = $radicacionMap[$cod]['tipo_tramite'] ?? ($row['tipo_tramite'] ?? '');
                $row['npn_predio'] = $radicacionMap[$cod]['npn_predio'] ?? ($row['npn_predio'] ?? '');
                if (($row['mutacion'] ?? '') === '') {
                    $row['mutacion'] = $radicacionMap[$cod]['mutacion'] ?? '';
                }
                if (($row['fecha_respuesta_tramite'] ?? '') === '') {
                    $row['fecha_respuesta_tramite'] = $radicacionMap[$cod]['fecha_limite_respuesta'] ?? '';
                }
            }
        }
        unset($row);
    }

    $vistos = [];
    $items = [];
    foreach ($raw as $row) {
        $cod = $row['cod_tramite'] ?? '';
        if ($cod === '' || isset($vistos[$cod])) {
            continue;
        }
        $vistos[$cod] = true;
        [$tipoEstado, $estadoVencimiento, $dias] = td_due_status($row['fecha_limite'] ?? '');
        $row['responsable'] = trim(($row['nombre_usuario'] ?? '') . ' ' . ($row['apellido_usuario'] ?? ''));
        if ($row['responsable'] === '' && (string)($row['cc_usuario'] ?? '') !== '') {
            $row['responsable'] = (string)$row['cc_usuario'];
        }
        if ($row['responsable'] === '') {
            $row['responsable'] = 'Sin responsable';
        }
        $row['estado_base'] = ($row['etapa'] ?? '') === 'revision' ? 'en_revision' : 'en_asignacion';
        $row['estado_cierre'] = $cerradosTipo[$cod] ?? 'activo';
        $row['tipo_estado'] = $tipoEstado;
        $row['estado_vencimiento'] = $estadoVencimiento;
        $row['dias'] = $dias;
        if (td_filter_match($row, $filters)) {
            $items[] = $row;
        }
    }

    $usuarios = [];
    $roles = [];
    $mutaciones = [];
    $todosTipos = [];
    $conteos = [];
    $statusCounts = ['a_tiempo' => 0, 'a_vencer' => 0, 'vencido' => 0, 'caducado' => 0, 'sin_fecha' => 0];
    $etapaCounts = ['en_asignacion' => 0, 'en_revision' => 0];
    $mutacionCounts = [];

    foreach ($items as $item) {
        $cc = (string) ($item['cc_usuario'] ?? '');
        $usuarioKey = $cc !== '' ? $cc : md5($item['responsable']);
        $usuarios[$usuarioKey] = [
            'cc' => $cc,
            'nombre' => $item['responsable'],
            'rol' => $item['rol'] ?? '',
        ];
        if (($item['rol'] ?? '') !== '') {
            $roles[$item['rol']] = $item['rol'];
        }
        if (($item['mutacion'] ?? '') !== '') {
            $mutaciones[$item['mutacion']] = $item['mutacion'];
        }

        $clave = $usuarioKey . '|' . ($item['rol'] ?? '');
        if (!isset($conteos[$clave])) {
            $conteos[$clave] = [
                'usuario_key' => $usuarioKey,
                'cc' => $cc,
                'responsable' => $item['responsable'],
                'rol' => $item['rol'] ?? '',
                'asignado' => 0,
                'en_asignacion' => 0,
                'en_revision' => 0,
                'a_tiempo' => 0,
                'a_vencer' => 0,
                'vencido' => 0,
                'caducado' => 0,
                'sin_fecha' => 0,
            ];
        }

        $conteos[$clave]['asignado']++;
        $conteos[$clave][$item['estado_base']]++;
        $conteos[$clave][$item['tipo_estado']]++;
        $statusCounts[$item['tipo_estado']]++;
        $etapaCounts[$item['estado_base']]++;

        $mut = $item['mutacion'] ?: 'Sin mutacion';
        $mutacionCounts[$mut] = ($mutacionCounts[$mut] ?? 0) + 1;
    }

    uasort($conteos, fn($a, $b) => (($b['asignado'] ?? 0) <=> ($a['asignado'] ?? 0)) ?: strcmp($a['responsable'], $b['responsable']));
    uasort($usuarios, fn($a, $b) => strcmp($a['nombre'], $b['nombre']));
    ksort($roles);
    ksort($mutaciones);
    ksort($mutacionCounts);

    try {
        $resTipos = $mysqli->query("
            SELECT DISTINCT
                CASE
                    WHEN mutacion_tramite IS NULL OR TRIM(mutacion_tramite) = '' THEN 'OTRO'
                    ELSE TRIM(mutacion_tramite)
                END AS tipo
            FROM tramite_radicacion
            ORDER BY tipo
        ");
        while ($resTipos && ($row = $resTipos->fetch_assoc())) {
            if (($row['tipo'] ?? '') !== '') {
                $todosTipos[$row['tipo']] = $row['tipo'];
            }
        }
    } catch (Throwable $e) {
        $todosTipos = $mutaciones;
    }

    $totalRad = 0;
    $totalCul = 0;
    try {
        $resRad = $mysqli->query("SELECT COUNT(*) AS total FROM tramite_radicacion");
        if ($resRad && ($row = $resRad->fetch_assoc())) {
            $totalRad = (int) $row['total'];
        }
    } catch (Throwable $e) {
        $totalRad = count($items);
    }
    try {
        $resCul = $mysqli->query("SELECT COUNT(*) AS total FROM procede_tramite");
        if ($resCul && ($row = $resCul->fetch_assoc())) {
            $totalCul = (int) $row['total'];
        }
    } catch (Throwable $e) {
        $totalCul = 0;
    }

    return [
        'filters' => $filters,
        'items' => $items,
        'conteos' => $conteos,
        'usuarios' => $usuarios,
        'roles' => $roles,
        'mutaciones' => $todosTipos ?: $mutaciones,
        'status_counts' => $statusCounts,
        'etapa_counts' => $etapaCounts,
        'mutacion_counts' => $mutacionCounts,
        'total_rad' => $totalRad,
        'total_cul' => $totalCul,
        'total_asignaciones' => count($items),
        'total_vencidas' => ($statusCounts['vencido'] ?? 0) + ($statusCounts['caducado'] ?? 0),
    ];
}

function td_can_reassign(string $role): bool
{
    return in_array($role, ['administrador', 'director_catastro', 'director_proyectos', 'coordinacion_tecnica'], true);
}

function td_assignment_history(mysqli $mysqli, string $codTramite): array
{
    $history = [];
    $safeCod = $mysqli->real_escape_string($codTramite);
    $queries = [
        "
            SELECT asignacion_fecha_tramite AS fecha, asignacion_nombre_usuario AS nombre,
                asignacion_apellido_usuario AS apellido, asignacion_rol_usuario AS rol,
                asignacion_estado_tramite AS estado, creacion_tram_nombre_usuario AS origen_nombre,
                creacion_tram_apellido_usuario AS origen_apellido, creacion_tram_rol_usuario AS origen_rol,
                observacion_a_usuario_tramite AS observacion, 'Asignacion inicial' AS evento
            FROM asignacion_tramite
            WHERE asignacion_cod_tramite = '{$safeCod}'
        ",
        "
            SELECT historial_fecha_tramite AS fecha, historial_nombre_usuario AS nombre,
                historial_apellido_usuario AS apellido, historial_rol_usuario AS rol,
                historial_estado_tramite AS estado, creacion_tram_nombre_usuario AS origen_nombre,
                creacion_tram_apellido_usuario AS origen_apellido, creacion_tram_rol_usuario AS origen_rol,
                observacion_a_usuario_tramite AS observacion, 'Historial asignacion' AS evento
            FROM historial_asignacion
            WHERE historial_cod_tramite = '{$safeCod}'
        ",
        "
            SELECT COALESCE(fecha_creacion, historial_fecha_tramite) AS fecha, entrega_nombre_usuario AS nombre,
                entrega_apellido_usuario AS apellido, entrega_rol_usuario AS rol,
                historial_estado_tramite AS estado, quien_entrego_nombre AS origen_nombre,
                quien_entrego_apellido AS origen_apellido, quien_entrego_rol AS origen_rol,
                observacion_a_usuario_tramite AS observacion, 'Entrega / reasignacion' AS evento
            FROM entrega_asignacion
            WHERE entrega_cod_tramite = '{$safeCod}'
        ",
        "
            SELECT COALESCE(fecha_creacion, historial_fecha_tramite) AS fecha, asignacion_nombre_usuario AS nombre,
                asignacion_apellido_usuario AS apellido, asignacion_rol_usuario AS rol,
                historial_estado_tramite AS estado, creacion_tram_nombre_usuario AS origen_nombre,
                creacion_tram_apellido_usuario AS origen_apellido, creacion_tram_rol_usuario AS origen_rol,
                observacion_a_usuario_tramite AS observacion, 'Revision' AS evento
            FROM historial_revision
            WHERE historial_cod_tramite = '{$safeCod}'
        ",
    ];

    foreach ($queries as $sql) {
        try {
            $res = $mysqli->query($sql);
            while ($res && ($row = $res->fetch_assoc())) {
                $history[] = $row;
            }
        } catch (Throwable $e) {
            continue;
        }
    }

    usort($history, fn($a, $b) => strcmp((string)($b['fecha'] ?? ''), (string)($a['fecha'] ?? '')));
    return $history;
}
