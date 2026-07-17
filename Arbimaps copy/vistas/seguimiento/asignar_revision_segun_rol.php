<?php
$rol_usuario        = $_SESSION['rol_usuario'] ?? '';
if ($rol_usuario === 'director_proyectos') {
    $rol_usuario = 'director_catastro';
}
$nombre_usuario     = $_SESSION['nombre_usuario'] ?? '';
$apellido_usuario   = $_SESSION['apellido_usuario'] ?? '';
$cedula_usuario     = $_SESSION['cedula_usuario'] ?? '';
$cod_tramite        = $_GET['cod'] ?? '';
$info_cod_tramite   = $_GET['cod'] ?? '';
$sql                = "SELECT * FROM tramite_radicacion WHERE cod_tramite = ?";
$stmt               = $mysqli->prepare($sql);
$stmt->bind_param("s", $cod_tramite);
$stmt->execute();
$resultado          = $stmt->get_result();
$tramite            = $resultado->fetch_assoc();
$tipo_tramite_actual = strtoupper(trim($tramite['tipo_tramite'] ?? 'ACTUALIZACION'));
if (!in_array($tipo_tramite_actual, ['ACTUALIZACION', 'CONSERVACION'], true)) {
    $tipo_tramite_actual = 'ACTUALIZACION';
}
$ruta_pdf           = "tramites_conservacion/2025/";
$sql2               = "SELECT * FROM tramite_info_predio WHERE info_cod_tramite = ?";
$stmt2              = $mysqli->prepare($sql2);
$stmt2->bind_param("s", $info_cod_tramite);
$stmt2->execute();
$resultado2         = $stmt2->get_result();
$info_predio        = $resultado2->fetch_assoc();
$cod                = $_GET['cod'] ?? null;

// ==============================================
// CONSULTA UNIFICADA DE TRAZABILIDAD / HISTORIAL (Para el botón Ver Trazabilidad)
// ==============================================
$historial = [];

// 1. Radicación inicial (de tramite_radicacion)
if (!empty($tramite)) {
    $historial[] = [
        'fecha' => $tramite['fecha_rad'],
        'actor' => trim(($tramite['nombre_usuario'] ?? '') . ' ' . ($tramite['apellido_usuario'] ?? '')),
        'rol_actor' => str_replace('_', ' ', $tramite['rol_usuario'] ?? 'ventanilla_catastral'),
        'destinatario' => '',
        'rol_destinatario' => '',
        'accion' => 'Radicación Inicial',
        'observacion' => $tramite['observacion_tramite'] ?? '',
        'tipo' => 'radicacion',
        'badge_class' => 'bg-success'
    ];
}

// 2. Historial de asignaciones
$sql_asig = "SELECT ha.*, 
                    d.nombre_doc1 AS doc1, d.nombre_doc2 AS doc2, d.nombre_doc3 AS doc3, d.nombre_doc4 AS doc4, d.nombre_doc5 AS doc5,
                    d.tipo_doc1, d.tipo_doc2, d.tipo_doc3, d.tipo_doc4, d.tipo_doc5
             FROM historial_asignacion ha
             LEFT JOIN documentos_tram_asignacion d 
                    ON d.cod_tramite = ha.historial_cod_tramite 
                   AND d.doc_cedula_usuario = ha.creacion_tram_cc_usuario
             WHERE ha.historial_cod_tramite = ? 
             ORDER BY ha.historial_fecha_tramite ASC";
if ($stmt_asig = $mysqli->prepare($sql_asig)) {
    $stmt_asig->bind_param("s", $cod_tramite);
    $stmt_asig->execute();
    $res_asig = $stmt_asig->get_result();
    while ($row = $res_asig->fetch_assoc()) {
        $fecha = !empty($row['fecha_creacion']) ? $row['fecha_creacion'] : $row['historial_fecha_tramite'];
        $historial[] = [
            'fecha' => $fecha,
            'actor' => trim(($row['creacion_tram_nombre_usuario'] ?? '') . ' ' . ($row['creacion_tram_apellido_usuario'] ?? '')),
            'rol_actor' => str_replace('_', ' ', $row['creacion_tram_rol_usuario'] ?? ''),
            'destinatario' => trim(($row['historial_nombre_usuario'] ?? '') . ' ' . ($row['historial_apellido_usuario'] ?? '')),
            'rol_destinatario' => str_replace('_', ' ', $row['historial_rol_usuario'] ?? ''),
            'accion' => 'Asignación de Trámite',
            'observacion' => $row['observacion_a_usuario_tramite'] ?? '',
            'tipo' => 'asignacion',
            'badge_class' => 'bg-primary',
            'doc1' => $row['doc1'] ?? null,
            'doc2' => $row['doc2'] ?? null,
            'doc3' => $row['doc3'] ?? null,
            'doc4' => $row['doc4'] ?? null,
            'doc5' => $row['doc5'] ?? null,
            'tipo_doc1' => $row['tipo_doc1'] ?? null,
            'tipo_doc2' => $row['tipo_doc2'] ?? null,
            'tipo_doc3' => $row['tipo_doc3'] ?? null,
            'tipo_doc4' => $row['tipo_doc4'] ?? null,
            'tipo_doc5' => $row['tipo_doc5'] ?? null
        ];
    }
    $stmt_asig->close();
}

// 3. Historial de revisiones
$sql_rev = "SELECT hr.*, 
                   d.doc1, d.doc2, d.doc3, d.doc4, d.doc5,
                   d.tipo_doc1, d.tipo_doc2, d.tipo_doc3, d.tipo_doc4, d.tipo_doc5
            FROM historial_revision hr
            LEFT JOIN doc_entrega_asignacion d 
                   ON d.asignacion_id_tramite = hr.entrega_id_tramite
                  AND d.cod_tramite = hr.historial_cod_tramite
            WHERE hr.historial_cod_tramite = ? 
            ORDER BY hr.historial_fecha_tramite ASC";
if ($stmt_rev = $mysqli->prepare($sql_rev)) {
    $stmt_rev->bind_param("s", $cod_tramite);
    $stmt_rev->execute();
    $res_rev = $stmt_rev->get_result();
    while ($row = $res_rev->fetch_assoc()) {
        $fecha = !empty($row['fecha_creacion']) ? $row['fecha_creacion'] : $row['historial_fecha_tramite'];
        $historial[] = [
            'fecha' => $fecha,
            'actor' => trim(($row['creacion_tram_nombre_usuario'] ?? '') . ' ' . ($row['creacion_tram_apellido_usuario'] ?? '')),
            'rol_actor' => str_replace('_', ' ', $row['creacion_tram_rol_usuario'] ?? ''),
            'destinatario' => trim(($row['asignacion_nombre_usuario'] ?? '') . ' ' . ($row['asignacion_apellido_usuario'] ?? '')),
            'rol_destinatario' => str_replace('_', ' ', $row['asignacion_rol_usuario'] ?? ''),
            'accion' => 'Revisión / Envío de Trámite',
            'observacion' => $row['observacion_a_usuario_tramite'] ?? '',
            'tipo' => 'revision',
            'badge_class' => 'bg-info',
            'doc1' => $row['doc1'] ?? null,
            'doc2' => $row['doc2'] ?? null,
            'doc3' => $row['doc3'] ?? null,
            'doc4' => $row['doc4'] ?? null,
            'doc5' => $row['doc5'] ?? null,
            'tipo_doc1' => $row['tipo_doc1'] ?? null,
            'tipo_doc2' => $row['tipo_doc2'] ?? null,
            'tipo_doc3' => $row['tipo_doc3'] ?? null,
            'tipo_doc4' => $row['tipo_doc4'] ?? null,
            'tipo_doc5' => $row['tipo_doc5'] ?? null
        ];
    }
    $stmt_rev->close();
}

// 4. Historial de devoluciones
$sql_dev = "SELECT hd.*, 
                   d.doc1, d.doc2, d.doc3, d.doc4, d.doc5,
                   d.tipo_doc1, d.tipo_doc2, d.tipo_doc3, d.tipo_doc4, d.tipo_doc5
            FROM historial_devolucion hd
            LEFT JOIN doc_entrega_asignacion d 
                   ON d.asignacion_id_tramite = hd.entrega_id_tramite
                  AND d.cod_tramite = hd.historial_cod_tramite
            WHERE hd.historial_cod_tramite = ? 
            ORDER BY hd.historial_fecha_tramite ASC";
if ($stmt_dev = $mysqli->prepare($sql_dev)) {
    $stmt_dev->bind_param("s", $cod_tramite);
    $stmt_dev->execute();
    $res_dev = $stmt_dev->get_result();
    while ($row = $res_dev->fetch_assoc()) {
        $fecha = !empty($row['fecha_creacion']) ? $row['fecha_creacion'] : $row['historial_fecha_tramite'];
        $historial[] = [
            'fecha' => $fecha,
            'actor' => trim(($row['nombre_sesion'] ?? '') . ' ' . ($row['apellido_sesion'] ?? '')),
            'rol_actor' => str_replace('_', ' ', $row['rol_actual'] ?? ''),
            'destinatario' => trim(($row['devolucion_tram_nombre_usuario'] ?? '') . ' ' . ($row['devolucion_tram_apellido_usuario'] ?? '')),
            'rol_destinatario' => str_replace('_', ' ', $row['devolucion_tram_rol_usuario'] ?? ''),
            'accion' => 'Devolución de Trámite',
            'observacion' => $row['observacion_a_usuario_tramite'] ?? '',
            'tipo' => 'devolucion',
            'badge_class' => 'bg-danger',
            'doc1' => $row['doc1'] ?? null,
            'doc2' => $row['doc2'] ?? null,
            'doc3' => $row['doc3'] ?? null,
            'doc4' => $row['doc4'] ?? null,
            'doc5' => $row['doc5'] ?? null,
            'tipo_doc1' => $row['tipo_doc1'] ?? null,
            'tipo_doc2' => $row['tipo_doc2'] ?? null,
            'tipo_doc3' => $row['tipo_doc3'] ?? null,
            'tipo_doc4' => $row['tipo_doc4'] ?? null,
            'tipo_doc5' => $row['tipo_doc5'] ?? null
        ];
    }
    $stmt_dev->close();
}

// 5. Resoluciones finales: Procede
$sql_procede = "SELECT * FROM procede_tramite WHERE cod_radicacion_tramite = ?";
if ($stmt_procede = $mysqli->prepare($sql_procede)) {
    $stmt_procede->bind_param("s", $cod_tramite);
    $stmt_procede->execute();
    $res_procede = $stmt_procede->get_result();
    while ($row = $res_procede->fetch_assoc()) {
        $historial[] = [
            'fecha' => $row['fecha_rad_tramite'],
            'actor' => 'Coordinación Técnica / Consolidaci�n',
            'rol_actor' => 'validador',
            'destinatario' => '',
            'rol_destinatario' => '',
            'accion' => 'Trámite PROCEDE (Aprobación Final)',
            'observacion' => $row['actividad_a_realizar'] ?? '',
            'tipo' => 'resolucion_procede',
            'badge_class' => 'bg-success'
        ];
    }
    $stmt_procede->close();
}

// 6. Resoluciones finales: No procede completar
$sql_noproc = "SELECT * FROM no_procede_completar WHERE cod_radicacion_tramite = ?";
if ($stmt_noproc = $mysqli->prepare($sql_noproc)) {
    $stmt_noproc->bind_param("s", $cod_tramite);
    $stmt_noproc->execute();
    $res_noproc = $stmt_noproc->get_result();
    while ($row = $res_noproc->fetch_assoc()) {
        $historial[] = [
            'fecha' => $row['fecha_rad_tramite'],
            'actor' => 'Coordinación Técnica / Consolidaci�n',
            'rol_actor' => 'validador',
            'destinatario' => '',
            'rol_destinatario' => '',
            'accion' => 'Trámite NO PROCEDE (Por Completar / Devuelto)',
            'observacion' => $row['observacion'] ?? '',
            'tipo' => 'resolucion_noprocede',
            'badge_class' => 'bg-dark'
        ];
    }
    $stmt_noproc->close();
}

// 7. Asignaciones activas actuales
$sql_act_asig = "SELECT ha.*, 
                        d.nombre_doc1 AS doc1, d.nombre_doc2 AS doc2, d.nombre_doc3 AS doc3, d.nombre_doc4 AS doc4, d.nombre_doc5 AS doc5,
                        d.tipo_doc1, d.tipo_doc2, d.tipo_doc3, d.tipo_doc4, d.tipo_doc5
                 FROM asignacion_tramite ha
                 LEFT JOIN documentos_tram_asignacion d 
                        ON d.cod_tramite = ha.asignacion_cod_tramite 
                       AND d.doc_cedula_usuario = ha.creacion_tram_cc_usuario
                 WHERE ha.asignacion_cod_tramite = ?";
if ($stmt_act_asig = $mysqli->prepare($sql_act_asig)) {
    $stmt_act_asig->bind_param("s", $cod_tramite);
    $stmt_act_asig->execute();
    $res_act_asig = $stmt_act_asig->get_result();
    while ($row = $res_act_asig->fetch_assoc()) {
        $fecha = $row['asignacion_fecha_tramite'];
        $historial[] = [
            'fecha' => $fecha,
            'actor' => trim(($row['creacion_tram_nombre_usuario'] ?? '') . ' ' . ($row['creacion_tram_apellido_usuario'] ?? '')),
            'rol_actor' => str_replace('_', ' ', $row['creacion_tram_rol_usuario'] ?? ''),
            'destinatario' => trim(($row['asignacion_nombre_usuario'] ?? '') . ' ' . ($row['asignacion_apellido_usuario'] ?? '')),
            'rol_destinatario' => str_replace('_', ' ', $row['asignacion_rol_usuario'] ?? ''),
            'accion' => 'Asignación Activa (Pendiente)',
            'observacion' => $row['observacion_a_usuario_tramite'] ?? '',
            'tipo' => 'asignacion_activa',
            'badge_class' => 'bg-warning text-dark',
            'doc1' => $row['doc1'] ?? null,
            'doc2' => $row['doc2'] ?? null,
            'doc3' => $row['doc3'] ?? null,
            'doc4' => $row['doc4'] ?? null,
            'doc5' => $row['doc5'] ?? null,
            'tipo_doc1' => $row['tipo_doc1'] ?? null,
            'tipo_doc2' => $row['tipo_doc2'] ?? null,
            'tipo_doc3' => $row['tipo_doc3'] ?? null,
            'tipo_doc4' => $row['tipo_doc4'] ?? null,
            'tipo_doc5' => $row['tipo_doc5'] ?? null
        ];
    }
    $stmt_act_asig->close();
}

// 8. Entregas activas actuales
$sql_act_ent = "SELECT ea.*, 
                       d.doc1, d.doc2, d.doc3, d.doc4, d.doc5,
                       d.tipo_doc1, d.tipo_doc2, d.tipo_doc3, d.tipo_doc4, d.tipo_doc5
                FROM entrega_asignacion ea
                LEFT JOIN doc_entrega_asignacion d 
                       ON d.asignacion_id_tramite = ea.id_entrega_asignacion
                      AND d.cod_tramite = ea.entrega_cod_tramite
                WHERE ea.entrega_cod_tramite = ?";
if ($stmt_act_ent = $mysqli->prepare($sql_act_ent)) {
    $stmt_act_ent->bind_param("s", $cod_tramite);
    $stmt_act_ent->execute();
    $res_act_ent = $stmt_act_ent->get_result();
    while ($row = $res_act_ent->fetch_assoc()) {
        $fecha = !empty($row['fecha_creacion']) ? $row['fecha_creacion'] : $row['historial_fecha_tramite'];
        $historial[] = [
            'fecha' => $fecha,
            'actor' => trim(($row['quien_entrego_nombre'] ?? '') . ' ' . ($row['quien_entrego_apellido'] ?? '')),
            'rol_actor' => str_replace('_', ' ', $row['quien_entrego_rol'] ?? ''),
            'destinatario' => trim(($row['entrega_nombre_usuario'] ?? '') . ' ' . ($row['entrega_apellido_usuario'] ?? '')),
            'rol_destinatario' => str_replace('_', ' ', $row['entrega_rol_usuario'] ?? ''),
            'accion' => 'Entrega Activa (Pendiente de Validación)',
            'observacion' => $row['observacion_a_usuario_tramite'] ?? '',
            'tipo' => 'entrega_activa',
            'badge_class' => 'bg-warning text-dark',
            'doc1' => $row['doc1'] ?? null,
            'doc2' => $row['doc2'] ?? null,
            'doc3' => $row['doc3'] ?? null,
            'doc4' => $row['doc4'] ?? null,
            'doc5' => $row['doc5'] ?? null,
            'tipo_doc1' => $row['tipo_doc1'] ?? null,
            'tipo_doc2' => $row['tipo_doc2'] ?? null,
            'tipo_doc3' => $row['tipo_doc3'] ?? null,
            'tipo_doc4' => $row['tipo_doc4'] ?? null,
            'tipo_doc5' => $row['tipo_doc5'] ?? null
        ];
    }
    $stmt_act_ent->close();
}

// Ordenar cronológicamente por fecha ascendente
usort($historial, static function ($a, $b) {
    return strtotime($a['fecha']) <=> strtotime($b['fecha']);
});

$roles_por_rol = [
    "ventanilla_catastral"  => ["procedencia_juridica"],
    "procedencia_juridica"  => ["coordinacion_tecnica", "revision_juridica"],
    "coordinacion_tecnica"  => ["control_calidad", "componente_economico", "revision_juridica", "director_catastro"],
    "revision_juridica"     => ["control_calidad"],
    "control_calidad"       => ["consolidacion"],
    "consolidacion"         => ["editor"],
    "editor"                => ["reconocedor"],
    "reconocedor"           => [""],
    "componente_economico"  => ["avaluos"],
    "avaluos"               => ["reconocedor"],
    "director_catastro"     => ["ventanilla_catastral"],
    "procedencia_juridica"  => ["coordinacion_tecnica", "revision_juridica"],
    "atencion_procedencia"  => ["coordinacion_tecnica"],
    "administrador"         => [
        "ventanilla_catastral",
        "atencion_procedencia",
        "coordinacion_tecnica",
        "control_calidad",
        "componente_economico",
        "revision_juridica",
        "director_catastro",
        "consolidacion",
        "editor",
        "reconocedor",
        "procedencia_juridica",
        "avaluos"
    ]
];
$roles_por_rol_conservacion = [
    "ventanilla_catastral" => ["procedencia_juridica"],
    "procedencia_juridica" => ["ventanilla_catastral"],
    "editor"               => ["coordinacion_tecnica"],
    "reconocedor"          => ["editor"],
    "coordinacion_tecnica" => ["procedencia_juridica"],
    "administrador"        => [
        "ventanilla_catastral",
        "procedencia_juridica",
        "editor",
        "reconocedor",
        "coordinacion_tecnica"
    ]
];


$idUsuario              = $_SESSION['id_usuario'];
$mapa_roles_actual      = $tipo_tramite_actual === 'CONSERVACION' ? $roles_por_rol_conservacion : $roles_por_rol;
$rol_flujo_actual       = $rol_usuario;
$roles_disponibles      = $mapa_roles_actual[$rol_flujo_actual] ?? [];
$rol_usuario            = $_SESSION['rol_usuario'] ?? '';
$roles_disponibles      = $mapa_roles_actual[$rol_flujo_actual] ?? [];

$sql            = "SELECT rol_usuario FROM usuarios_cons WHERE id_usuario = $idUsuario";
$resultado      = $mysqli->query($sql);
$sql            = "SELECT rol_usuario FROM usuarios_cons WHERE id_usuario = ?";
$stmt           = $mysqli->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$stmt->get_result();

$asignacion_rol_usuario = null;
$sqlRol = "SELECT asignacion_rol_usuario
            FROM asignacion_tramite
            WHERE asignacion_cod_tramite = ?
            LIMIT 1";
$stmtRol = $mysqli->prepare($sqlRol);
if ($stmtRol) {
    $stmtRol->bind_param("s", $cod_tramite);
    $stmtRol->execute();
    $resRol = $stmtRol->get_result();
    if ($rowRol = $resRol->fetch_assoc()) {
        $asignacion_rol_usuario = $rowRol['asignacion_rol_usuario'];
    }
}
$cedula_usuario                 = $_SESSION['cedula_usuario'] ?? '';
$cod_tramite                    = $_GET['cod'] ?? '';
$entrega_cc_usuario             = null;
$entrega_nombre_usuario         = null;
$entrega_apellido_usuario       = null;
$entrega_rol_usuario            = null;
$observacion_a_usuario_tramite  = null;
if ($cedula_usuario && $cod_tramite) {
    $sqlBuscar = "SELECT creacion_tram_cc_usuario,
                          creacion_tram_nombre_usuario,
                          creacion_tram_apellido_usuario,
                          creacion_tram_rol_usuario
                   FROM asignacion_tramite
                   WHERE asignacion_cod_tramite = ?
                     AND (asignacion_rol_usuario = ? OR asignacion_cc_usuario = ?)
                   ORDER BY CASE WHEN asignacion_rol_usuario = ? THEN 0 ELSE 1 END,
                            asignacion_id_tramite DESC
                   LIMIT 1";
    $stmtBuscar = $mysqli->prepare($sqlBuscar);
    $stmtBuscar->bind_param("ssss", $cod_tramite, $rol_flujo_actual, $cedula_usuario, $rol_flujo_actual);
    $stmtBuscar->execute();
    $resultado = $stmtBuscar->get_result();
    if ($row = $resultado->fetch_assoc()) {
        $entrega_cc_usuario       = $row['creacion_tram_cc_usuario'];
        $entrega_nombre_usuario   = $row['creacion_tram_nombre_usuario'];
        $entrega_apellido_usuario = $row['creacion_tram_apellido_usuario'];
        $entrega_rol_usuario      = $row['creacion_tram_rol_usuario'];
    } else {
        $sqlBuscarEntrega = "SELECT entrega_cc_usuario,
                                    entrega_nombre_usuario,
                                    entrega_apellido_usuario,
                                    entrega_rol_usuario
                             FROM entrega_asignacion
                             WHERE entrega_cod_tramite = ?
                               AND (entrega_rol_usuario = ? OR entrega_cc_usuario = ?)
                             ORDER BY CASE WHEN entrega_rol_usuario = ? THEN 0 ELSE 1 END,
                                      id_entrega_asignacion DESC
                             LIMIT 1";
        $stmtBuscarEntrega = $mysqli->prepare($sqlBuscarEntrega);
        if ($stmtBuscarEntrega) {
            $stmtBuscarEntrega->bind_param("ssss", $cod_tramite, $rol_flujo_actual, $cedula_usuario, $rol_flujo_actual);
            $stmtBuscarEntrega->execute();
            $resultadoEntrega = $stmtBuscarEntrega->get_result();
            if ($rowEntrega = $resultadoEntrega->fetch_assoc()) {
                $entrega_cc_usuario       = $rowEntrega['entrega_cc_usuario'];
                $entrega_nombre_usuario   = $rowEntrega['entrega_nombre_usuario'];
                $entrega_apellido_usuario = $rowEntrega['entrega_apellido_usuario'];
                $entrega_rol_usuario      = $rowEntrega['entrega_rol_usuario'];
            }
            $stmtBuscarEntrega->close();
        }
    }
    $stmtBuscar->close();
}
// Contexto real del tramite: evita usar un rol secundario de sesion como si fuera el rol del flujo.
$devolver_cc_usuario       = null;
$devolver_nombre_usuario   = null;
$devolver_apellido_usuario = null;
$devolver_rol_usuario      = null;
if ($cedula_usuario && $cod_tramite) {
    $sqlUltimaEntrega = "SELECT entrega_rol_usuario,
                                quien_entrego_cc,
                                quien_entrego_nombre,
                                quien_entrego_apellido,
                                quien_entrego_rol
                         FROM entrega_asignacion
                         WHERE entrega_cod_tramite = ?
                           AND (entrega_rol_usuario = ? OR entrega_cc_usuario = ?)
                         ORDER BY CASE WHEN entrega_rol_usuario = ? THEN 0 ELSE 1 END,
                                  id_entrega_asignacion DESC
                         LIMIT 1";
    $stmtUltimaEntrega = $mysqli->prepare($sqlUltimaEntrega);
    if ($stmtUltimaEntrega) {
        $stmtUltimaEntrega->bind_param("ssss", $cod_tramite, $rol_flujo_actual, $cedula_usuario, $rol_flujo_actual);
        $stmtUltimaEntrega->execute();
        $rsUltimaEntrega = $stmtUltimaEntrega->get_result();
        if ($rowUltimaEntrega = $rsUltimaEntrega->fetch_assoc()) {
            if (!empty($rowUltimaEntrega['entrega_rol_usuario'])) {
                $rol_flujo_actual = $rowUltimaEntrega['entrega_rol_usuario'];
            }
            $devolver_cc_usuario       = $rowUltimaEntrega['quien_entrego_cc'];
            $devolver_nombre_usuario   = $rowUltimaEntrega['quien_entrego_nombre'];
            $devolver_apellido_usuario = $rowUltimaEntrega['quien_entrego_apellido'];
            $devolver_rol_usuario      = $rowUltimaEntrega['quien_entrego_rol'];
        }
        $stmtUltimaEntrega->close();
    }
}

$creacion_tram_cc_usuario           = null;
$creacion_tram_nombre_usuario       = null;
$creacion_tram_apellido_usuario     = null;
$creacion_tram_rol_usuario          = null;
$tiene_asignacion_actual           = false;
if ($cedula_usuario && $cod_tramite) {
    $sqlBuscarAsig = "SELECT asignacion_rol_usuario,
                             creacion_tram_cc_usuario,
                             creacion_tram_nombre_usuario,
                             creacion_tram_apellido_usuario,
                             creacion_tram_rol_usuario
                      FROM asignacion_tramite
                      WHERE asignacion_cod_tramite = ?
                        AND (asignacion_rol_usuario = ? OR asignacion_cc_usuario = ?)
                      ORDER BY CASE WHEN asignacion_rol_usuario = ? THEN 0 ELSE 1 END,
                               asignacion_id_tramite DESC
                      LIMIT 1";
    $stmtBuscarAsig = $mysqli->prepare($sqlBuscarAsig);
    if ($stmtBuscarAsig) {
        $stmtBuscarAsig->bind_param("ssss", $cod_tramite, $rol_flujo_actual, $cedula_usuario, $rol_flujo_actual);
        $stmtBuscarAsig->execute();
        $resultado = $stmtBuscarAsig->get_result();
        if ($row = $resultado->fetch_assoc()) {
            $tiene_asignacion_actual        = true;
            $rol_flujo_actual               = $row['asignacion_rol_usuario'] ?? $rol_flujo_actual;
            $creacion_tram_cc_usuario       = $row['creacion_tram_cc_usuario'];
            $creacion_tram_nombre_usuario   = $row['creacion_tram_nombre_usuario'];
            $creacion_tram_apellido_usuario = $row['creacion_tram_apellido_usuario'];
            $creacion_tram_rol_usuario      = $row['creacion_tram_rol_usuario'];
        }
        $stmtBuscarAsig->close();
    }
}
$roles_disponibles = $mapa_roles_actual[$rol_flujo_actual] ?? [];
if (!$tiene_asignacion_actual && !empty($devolver_cc_usuario)) {
    $creacion_tram_cc_usuario       = $devolver_cc_usuario;
    $creacion_tram_nombre_usuario   = $devolver_nombre_usuario;
    $creacion_tram_apellido_usuario = $devolver_apellido_usuario;
    $creacion_tram_rol_usuario      = $devolver_rol_usuario;
} elseif (empty($devolver_cc_usuario)) {
    $devolver_cc_usuario       = $creacion_tram_cc_usuario;
    $devolver_nombre_usuario   = $creacion_tram_nombre_usuario;
    $devolver_apellido_usuario = $creacion_tram_apellido_usuario;
    $devolver_rol_usuario      = $creacion_tram_rol_usuario;
}

$mostrar_remitente_cc_usuario       = !empty($devolver_cc_usuario) ? $devolver_cc_usuario : $creacion_tram_cc_usuario;
$mostrar_remitente_nombre_usuario   = !empty($devolver_nombre_usuario) ? $devolver_nombre_usuario : $creacion_tram_nombre_usuario;
$mostrar_remitente_apellido_usuario = !empty($devolver_apellido_usuario) ? $devolver_apellido_usuario : $creacion_tram_apellido_usuario;
$mostrar_remitente_rol_usuario      = !empty($devolver_rol_usuario) ? $devolver_rol_usuario : $creacion_tram_rol_usuario;
$sqlBuscar = "SELECT observacion_a_usuario_tramite
                FROM entrega_asignacion
                WHERE entrega_cod_tramite = ?
                ORDER BY id_entrega_asignacion DESC
                LIMIT 1";
$stmtBuscar = $mysqli->prepare($sqlBuscar);
if (!$stmtBuscar) {
    die("Error en la consulta: " . $mysqli->error);
}

$stmtBuscar->bind_param("s", $cod_tramite);
$stmtBuscar->execute();
$resultObs = $stmtBuscar->get_result();
$observacion_a_usuario_tramite = '';
if ($row = $resultObs->fetch_assoc()) {
    $observacion_a_usuario_tramite = $row['observacion_a_usuario_tramite'];
}
$stmtBuscar->close();


$cod_tramite    = $tramite['cod_tramite'];

function webBaseRevisionSegunRol()
{
    $scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $parts = explode('/', trim($scriptPath, '/'));
    $appRoot = $parts[0] ?? 'neiva';
    return '/' . trim($appRoot, '/');
}

function rutaWebDocumentoRevisionSegunRol($ruta)
{
    $ruta = trim(str_replace('\\', '/', (string)$ruta));
    if ($ruta === '') {
        return '';
    }
    if (preg_match('#^(https?:)?//#i', $ruta) || strpos($ruta, 'data:') === 0) {
        return $ruta;
    }

    $ruta = rawurldecode($ruta);
    $ruta = ltrim($ruta, '/');
    $ruta = preg_replace('#^\.\./#', '', $ruta);
    $ruta = preg_replace('#^(?:neiva/)?Arbimaps/#i', '', $ruta);

    $carpetasPublicas = ['tramites_conservacion/', 'resoluciones/', 'archivos/'];
    foreach ($carpetasPublicas as $carpeta) {
        $pos = stripos($ruta, $carpeta);
        if ($pos !== false) {
            $ruta = substr($ruta, $pos);
            return webBaseRevisionSegunRol() . '/' . implode('/', array_map('rawurlencode', explode('/', $ruta)));
        }
    }

    return htmlspecialchars($ruta, ENT_QUOTES, 'UTF-8');
}

function normalizarDocumentosAsignacionSegunRol($row)
{
    if (!$row) {
        return null;
    }
    $docs = [];
    $tieneDocumentos = false;
    for ($i = 1; $i <= 5; $i++) {
        $docs["doc$i"] = rutaWebDocumentoRevisionSegunRol($row["nombre_doc$i"] ?? null);
        $docs["tipo_doc$i"] = $row["tipo_doc$i"] ?? null;
        if (!empty($docs["doc$i"])) {
            $tieneDocumentos = true;
        }
    }
    return $tieneDocumentos ? $docs : null;
}

function tieneDocumentosRevisionSegunRol($row)
{
    if (!$row) {
        return false;
    }
    for ($i = 1; $i <= 5; $i++) {
        if (!empty($row["doc$i"])) {
            return true;
        }
    }
    return false;
}

function obtenerDocumentosRevisionSegunRol($mysqli, $cod_tramite, $cedula_usuario)
{
    $sqlDocsRevision = "SELECT *
                        FROM doc_entrega_asignacion
                        WHERE cod_tramite = ?
                          AND (doc1 IS NOT NULL OR doc2 IS NOT NULL OR doc3 IS NOT NULL OR doc4 IS NOT NULL OR doc5 IS NOT NULL)
                        ORDER BY id_doc_entrega DESC
                        LIMIT 1";
    $stmtDocsRevision = $mysqli->prepare($sqlDocsRevision);
    if ($stmtDocsRevision) {
        $stmtDocsRevision->bind_param("s", $cod_tramite);
        $stmtDocsRevision->execute();
        $resDocsRevision = $stmtDocsRevision->get_result();
        $docsRevision = $resDocsRevision->fetch_assoc();
        $stmtDocsRevision->close();
        if (tieneDocumentosRevisionSegunRol($docsRevision)) {
            for ($i = 1; $i <= 5; $i++) {
                $docsRevision["doc$i"] = rutaWebDocumentoRevisionSegunRol($docsRevision["doc$i"] ?? null);
            }
            return $docsRevision;
        }
    }

    $sqlDocsAsignacion = "SELECT *
                          FROM documentos_tram_asignacion
                          WHERE cod_tramite = ?
                            AND doc_cedula_usuario = ?
                            AND (nombre_doc1 IS NOT NULL OR nombre_doc2 IS NOT NULL OR nombre_doc3 IS NOT NULL OR nombre_doc4 IS NOT NULL OR nombre_doc5 IS NOT NULL)
                          ORDER BY fecha_cargue_doc DESC
                          LIMIT 1";
    $stmtDocsAsignacion = $mysqli->prepare($sqlDocsAsignacion);
    if ($stmtDocsAsignacion) {
        $stmtDocsAsignacion->bind_param("ss", $cod_tramite, $cedula_usuario);
        $stmtDocsAsignacion->execute();
        $resDocsAsignacion = $stmtDocsAsignacion->get_result();
        $docsAsignacion = normalizarDocumentosAsignacionSegunRol($resDocsAsignacion->fetch_assoc());
        $stmtDocsAsignacion->close();
        if ($docsAsignacion) {
            return $docsAsignacion;
        }
    }

    return null;
}

$docs = obtenerDocumentosRevisionSegunRol($mysqli, $cod_tramite, $cedula_usuario);

function buscarUsuarioPorRolRevisionSegunRol($mysqli, $rol)
{
    $sqlUsuario = "SELECT cedula_usuario, nombre_usuario, apellido_usuario, rol_usuario, rol_usuario_dos
                   FROM usuarios_cons
                   WHERE rol_usuario = ? OR rol_usuario_dos = ?
                   ORDER BY id_usuario DESC
                   LIMIT 1";
    $stmtUsuario = $mysqli->prepare($sqlUsuario);
    if (!$stmtUsuario) {
        return null;
    }
    $stmtUsuario->bind_param("ss", $rol, $rol);
    $stmtUsuario->execute();
    $rsUsuario = $stmtUsuario->get_result();
    $usuario = $rsUsuario->fetch_assoc() ?: null;
    $stmtUsuario->close();
    if ($usuario) {
        $usuario['rol_usuario'] = $rol;
    }
    return $usuario;
}

function buscarVentanillaOriginalRevisionSegunRol($mysqli, $cod_tramite)
{
    $sqlVent = "SELECT creacion_tram_cc_usuario,
                       creacion_tram_nombre_usuario,
                       creacion_tram_apellido_usuario,
                       creacion_tram_rol_usuario
                FROM asignacion_tramite
                WHERE asignacion_cod_tramite = ?
                  AND creacion_tram_rol_usuario = 'ventanilla_catastral'
                ORDER BY asignacion_id_tramite ASC
                LIMIT 1";
    $stmtVent = $mysqli->prepare($sqlVent);
    if (!$stmtVent) {
        return null;
    }
    $stmtVent->bind_param("s", $cod_tramite);
    $stmtVent->execute();
    $rsVent = $stmtVent->get_result();
    $ventanilla = $rsVent->fetch_assoc() ?: null;
    $stmtVent->close();
    return $ventanilla;
}

function resolverVentanillaRevisionSegunRol($mysqli, $cod_tramite)
{
    $ventanillaOriginal = buscarVentanillaOriginalRevisionSegunRol($mysqli, $cod_tramite);
    if ($ventanillaOriginal) {
        return [
            'cedula_usuario' => $ventanillaOriginal['creacion_tram_cc_usuario'],
            'nombre_usuario' => $ventanillaOriginal['creacion_tram_nombre_usuario'],
            'apellido_usuario' => $ventanillaOriginal['creacion_tram_apellido_usuario'],
            'rol_usuario' => 'ventanilla_catastral',
        ];
    }

    return buscarUsuarioPorRolRevisionSegunRol($mysqli, 'ventanilla_catastral');
}

$destino_conservacion = [
    'reconocedor'          => 'editor',
    'editor'               => 'consolidacion',
    'coordinacion_tecnica' => 'procedencia_juridica',
    'procedencia_juridica' => 'ventanilla_catastral'
];
$destino_revision_actualizacion = [
    'reconocedor'          => 'editor',
    'editor'               => 'consolidacion',
    'consolidacion'        => 'control_calidad',
    'control_calidad'      => 'coordinacion_tecnica',
    'coordinacion_tecnica' => 'procedencia_juridica',
    'procedencia_juridica' => 'director_catastro',
    'director_catastro'    => 'ventanilla_catastral',
    'avaluos'              => 'componente_economico',
    'componente_economico' => 'coordinacion_tecnica',
    'revision_juridica'    => 'procedencia_juridica',
];

$rol_destino_flujo = $tipo_tramite_actual === 'CONSERVACION'
    ? ($destino_conservacion[$rol_flujo_actual] ?? ($roles_disponibles[0] ?? ''))
    : ($destino_revision_actualizacion[$rol_flujo_actual] ?? ($roles_disponibles[0] ?? ''));

if ($rol_destino_flujo === 'ventanilla_catastral') {
    $usuarioDestino = resolverVentanillaRevisionSegunRol($mysqli, $cod_tramite);
    if ($usuarioDestino) {
        $entrega_cc_usuario       = $usuarioDestino['cedula_usuario'];
        $entrega_nombre_usuario   = $usuarioDestino['nombre_usuario'];
        $entrega_apellido_usuario = $usuarioDestino['apellido_usuario'];
        $entrega_rol_usuario      = 'ventanilla_catastral';
    } else {
        $entrega_cc_usuario       = '';
        $entrega_nombre_usuario   = '';
        $entrega_apellido_usuario = '';
        $entrega_rol_usuario      = 'ventanilla_catastral';
    }
} elseif (
    $rol_destino_flujo
    && !empty($creacion_tram_cc_usuario)
    && !empty($creacion_tram_rol_usuario)
    && $rol_destino_flujo === $creacion_tram_rol_usuario
) {
    $entrega_cc_usuario       = $creacion_tram_cc_usuario;
    $entrega_nombre_usuario   = $creacion_tram_nombre_usuario;
    $entrega_apellido_usuario = $creacion_tram_apellido_usuario;
    $entrega_rol_usuario      = $creacion_tram_rol_usuario;
} elseif ($rol_destino_flujo) {
    $usuarioDestino = buscarUsuarioPorRolRevisionSegunRol($mysqli, $rol_destino_flujo);
    if ($usuarioDestino) {
        $entrega_cc_usuario       = $usuarioDestino['cedula_usuario'];
        $entrega_nombre_usuario   = $usuarioDestino['nombre_usuario'];
        $entrega_apellido_usuario = $usuarioDestino['apellido_usuario'];
        $entrega_rol_usuario      = $usuarioDestino['rol_usuario'];
    } else {
        $entrega_cc_usuario       = '';
        $entrega_nombre_usuario   = '';
        $entrega_apellido_usuario = '';
        $entrega_rol_usuario      = $rol_destino_flujo;
    }
}
$asignacion_rol_usuario = $entrega_rol_usuario ?: $rol_destino_flujo ?: $asignacion_rol_usuario;

$mapRolIndex = [
    'ventanilla_catastral'  => 1,
    'procedencia_juridica'  => 2,
    'atencion_procedencia'  => 3,
    'revision_juridica'     => 4,
    'coordinacion_tecnica'  => 5,
    'control_calidad'       => 6,
    'consolidacion'         => 7,
    'componente_economico'  => 8,
    'editor'                => 9,
    'avaluos'               => 10,
    'reconocedor'           => 11,
    'director_catastro'     => 12,
];


function contarNotificacionesPorRol(mysqli $mysqli, string $cod_tramite): array
{
    $counts = [];
    $sql = "SELECT asignacion_rol_usuario AS rol, COUNT(*) AS c
            FROM asignacion_tramite
            WHERE asignacion_cod_tramite = ?
            GROUP BY asignacion_rol_usuario";
    if ($st = $mysqli->prepare($sql)) {
        $st->bind_param("s", $cod_tramite);
        if ($st->execute()) {
            $rs = $st->get_result();
            while ($row = $rs->fetch_assoc()) {
                if (!empty($row['rol'])) {
                    $counts[$row['rol']] = (int)$row['c'];
                }
            }
        }
        $st->close();
    }
    $sql2 = "SELECT creacion_tram_rol_usuario AS rol, COUNT(*) AS c
             FROM asignacion_tramite
             WHERE asignacion_cod_tramite = ?
             GROUP BY creacion_tram_rol_usuario";
    if ($st2 = $mysqli->prepare($sql2)) {
        $st2->bind_param("s", $cod_tramite);
        if ($st2->execute()) {
            $rs2 = $st2->get_result();
            while ($row2 = $rs2->fetch_assoc()) {
                $r = $row2['rol'] ?? '';
                if (!empty($r)) {
                    $counts[$r] = ($counts[$r] ?? 0) + (int)$row2['c'];
                }
            }
        }
        $st2->close();
    }
    return $counts;
}

function renderBadge(string $rol, array $counts, array $mapRolIndex): void
{
    $n = (int)($counts[$rol] ?? 0);
    if ($n > 0) {
        $idx = $mapRolIndex[$rol] ?? 1;
        echo '<span class="noti-badge noti-rol-' . (int)$idx . '">' . $n . '</span>';
    }
}
$notificaciones = contarNotificacionesPorRol($mysqli, $cod_tramite);

$documento_resolucion = null;
$ruta_resolucion = null;
$id_resolucion = null;
$has_director_resolucion = false;
$sqlResolucion = "SELECT r.id_resoluciones, r.documento, r.resolucion_director, r.ruta_archivo
                  FROM resoluciones r
                  INNER JOIN entrega_asignacion ea ON ea.id_entrega_asignacion = r.id_entrega_asignacion
                  WHERE ea.entrega_cod_tramite = ?
                  ORDER BY r.id_resoluciones DESC
                  LIMIT 1";
if ($stmtResolucion = $mysqli->prepare($sqlResolucion)) {
    $stmtResolucion->bind_param("s", $cod_tramite);
    $stmtResolucion->execute();
    $rsResolucion = $stmtResolucion->get_result();
    if ($rowResolucion = $rsResolucion->fetch_assoc()) {
        $documento_resolucion = $rowResolucion['documento'];
        $ruta_resolucion = rutaWebDocumentoRevisionSegunRol($rowResolucion['ruta_archivo'] ?? '');
        $id_resolucion = $rowResolucion['id_resoluciones'];
        $has_director_resolucion = !empty($rowResolucion['resolucion_director']);
    }
    $stmtResolucion->close();
}
$requiere_resolucion = ($tipo_tramite_actual !== 'CONSERVACION' && $rol_flujo_actual === 'procedencia_juridica');
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<style>
    .btn-ver:hover {
        background-color: #022F55;
        color: white;
    }

    .btn-verr:hover {
        background-color: #022F55;
        color: white !important;
    }

    .btn-rol-1 {
        border-left: 3px solid #4DA6FF;
        border-top: 3px solid #4DA6FF;
        color: #002F55;
        transition: all 0.5s ease;
    }

    .btn-rol-1:hover {
        background-color: #4DA6FF;
        color: #5e5a5aff;
    }

    .btn-rol-2 {
        border-left: 3px solid #66CC99;
        border-top: 3px solid #66CC99;
        color: #002F55;
    }

    .btn-rol-2:hover {
        background-color: #66CC99;
        color: #5e5a5aff;
    }

    .btn-rol-3 {
        border-left: 3px solid #FFCC66;
        border-top: 3px solid #FFCC66;
        color: #002F55;
    }

    .btn-rol-3:hover {
        background-color: #FFCC66;
        color: #5e5a5aff;
    }

    .btn-rol-4 {
        border-left: 3px solid #FF9966;
        border-top: 3px solid #FF9966;
        color: #002F55;
    }

    .btn-rol-4:hover {
        background-color: #FF9966;
        color: #5e5a5aff;
    }

    .btn-rol-5 {
        border-left: 3px solid #B399FF;
        border-top: 3px solid #B399FF;
        color: #002F55;
    }

    .btn-rol-5:hover {
        background-color: #B399FF;
        color: #5e5a5aff;
    }

    .btn-rol-6 {
        border-left: 3px solid #4DD2C6;
        border-top: 3px solid #4DD2C6;
        color: #002F55;
    }

    .btn-rol-6:hover {
        background-color: #4DD2C6;
        color: #5e5a5aff;
    }

    .btn-rol-7 {
        border-left: 3px solid #FFB3B3;
        border-top: 3px solid #FFB3B3;
        color: #002F55;
    }

    .btn-rol-7:hover {
        background-color: #FFB3B3;
        color: #5e5a5aff;
    }

    .btn-rol-8 {
        border-left: 3px solid #CCFF99;
        border-top: 3px solid #CCFF99;
        color: #002F55;
    }

    .btn-rol-8:hover {
        background-color: #CCFF99;
        color: #5e5a5aff;
    }

    .btn-rol-9 {
        border-left: 3px solid #FFD1A9;
        border-top: 3px solid #FFD1A9;
        color: #002F55;
    }

    .btn-rol-9:hover {
        background-color: #FFD1A9;
        color: #5e5a5aff;
    }

    .btn-rol-10 {
        border-left: 3px solid #A6C8FF;
        border-top: 3px solid #A6C8FF;
        color: #002F55;
    }

    .btn-rol-10:hover {
        background-color: #A6C8FF;
        color: #5e5a5aff;
    }

    .btn-rol-11 {
        border-left: 3px solid #E6CCFF;
        border-top: 3px solid #E6CCFF;
        color: #002F55;
    }

    .btn-rol-11:hover {
        background-color: #E6CCFF;
        color: #5e5a5aff;
    }

    .btn-rol-12 {
        border-left: 3px solid #99CCFF;
        border-top: 3px solid #99CCFF;
        color: #002F55;
    }

    .btn-rol-12:hover {
        background-color: #99CCFF;
        color: #5e5a5aff;
    }

    .btn-rol-1,
    .btn-rol-2,
    .btn-rol-3,
    .btn-rol-4,
    .btn-rol-5,
    .btn-rol-6,
    .btn-rol-7,
    .btn-rol-8,
    .btn-rol-9,
    .btn-rol-10,
    .btn-rol-11,
    .btn-rol-12 {
        position: relative;
        overflow: visible;
    }

    .noti-badge {
        position: absolute;
        top: -10px;
        right: -10px;
        border-radius: 999px;
        padding: 4px 8px;
        font-size: 0.78rem;
        font-weight: 700;
        line-height: 1;
        color: #fff;
        box-shadow: 0 0 4px rgba(0, 0, 0, 0.25);
        pointer-events: none;
        z-index: 5;
    }

    .noti-rol-1 {
        background-color: #4DA6FF;
    }

    .noti-rol-2 {
        background-color: #66CC99;
    }

    .noti-rol-3 {
        background-color: #FFCC66;
        color: #002F55;
    }

    .noti-rol-4 {
        background-color: #FF9966;
    }

    .noti-rol-5 {
        background-color: #B399FF;
    }

    .noti-rol-6 {
        background-color: #4DD2C6;
    }

    .noti-rol-7 {
        background-color: #FFB3B3;
    }

    .noti-rol-8 {
        background-color: #CCFF99;
        color: #002F55;
    }

    .resolucion-box {
        border: 1px solid #d7e3ee;
        border-left: 4px solid #022F55;
        border-radius: 8px;
        background: #f8fbff;
        padding: 14px;
        text-align: left;
    }

    .resolucion-preview {
        background: #fff;
        color: #111;
        border: 1px solid #ccd6e0;
        padding: 28px;
        max-width: 780px;
        margin: 0 auto;
        font-family: Arial, sans-serif;
        line-height: 1.45;
    }

    .resolucion-preview h3,
    .resolucion-preview h4 {
        text-align: center;
        margin: 8px 0;
    }

    .pj-resolution-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 22px;
    }

    .modal-resolucion .modal-dialog {
        width: 96vw;
        max-width: 1680px;
    }

    .modal-resolucion .modal-content {
        max-height: 94vh;
    }

    .modal-resolucion .modal-body {
        background: #f5f8fb;
        padding: 20px 24px;
    }

    .pj-document-editor,
    .pj-document-preview {
        background: #fff;
        color: #111;
        border: 1px solid #cbd6e2;
        border-radius: 6px;
        padding: 30px 42px;
        font-family: "Times New Roman", Times, serif;
        font-size: 11.5pt;
        line-height: 1.45;
        min-height: 720px;
        text-align: left;
        box-shadow: 0 8px 20px rgba(2, 47, 85, 0.08);
    }

    .pj-document-editor {
        outline: 2px dashed #9dbad3;
        max-height: 72vh;
        overflow: auto;
    }

    .pj-document-editor:focus {
        outline: 2px solid #022F55;
    }

    .pj-document-preview {
        max-height: 72vh;
        overflow: auto;
    }

    .pj-document-preview.exporting {
        max-height: none;
        overflow: visible;
    }

    .pj-doc-header {
        display: grid;
        grid-template-columns: 1.2fr 1fr 1.2fr;
        border: 1px solid #333;
        margin-bottom: 18px;
        min-height: 96px;
    }

    .pj-doc-header>div {
        border-right: 1px solid #333;
        padding: 10px 12px;
        min-height: 96px;
    }

    .pj-doc-header>div:last-child {
        border-right: 0;
    }

    .pj-header-left,
    .pj-header-right {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 10pt;
        line-height: 1.25;
    }

    .pj-header-left {
        justify-content: flex-start;
    }

    .pj-header-right {
        justify-content: space-between;
    }

    .pj-header-center {
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        font-weight: bold;
        font-size: 11pt;
        line-height: 1.25;
    }

    .pj-escudo,
    .pj-logo-alc {
        width: 42px;
        min-width: 42px;
        height: auto;
        object-fit: contain;
    }

    .pj-codigo-box {
        font-size: 8.5pt;
        line-height: 1.35;
        border: 1px solid #333;
        padding: 7px 9px;
        min-width: 92px;
        background: #fff;
    }

    .pj-resolution-number,
    .pj-doc-date,
    .pj-doc-title,
    .pj-emisor-cargo,
    .pj-considerando-title,
    .pj-resuelve-title {
        text-align: center;
    }

    .pj-doc-title,
    .pj-emisor-cargo,
    .pj-considerando-title,
    .pj-resuelve-title {
        font-weight: bold;
        margin: 18px 0;
    }

    .pj-articulo {
        margin: 12px 0;
        text-align: justify;
    }

    .pj-doc-body {
        text-align: justify;
    }

    .pj-doc-signature {
        margin-top: 52px;
        text-align: center;
    }

    .pj-firma-linea {
        border-top: 1px solid #111;
        width: 260px;
        margin: 0 auto 8px;
    }

    .pj-doc-footer {
        margin-top: 28px;
        font-size: 9pt;
    }

    @media (max-width: 1200px) {
        .pj-resolution-grid {
            grid-template-columns: 1fr;
        }
    }

    .noti-rol-9 {
        background-color: #FFD1A9;
        color: #002F55;
    }

    .noti-rol-10 {
        background-color: #A6C8FF;
    }

    .noti-rol-11 {
        background-color: #E6CCFF;
        color: #002F55;
    }

    .noti-rol-12 {
        background-color: #99CCFF;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4 px-4">
        <a href="index.php?page=tramites/base_catastral/predio_mutacion&cod=<?php echo urlencode($tramite['cod_tramite']); ?>"
            class="btn btn-md shadow-lg fw-bold btn-ver"
            style="border-left:2px solid #0b7a53;border-right:2px solid #0b7a53;order:2;">
            <i class="bi bi-table me-2 fw-bold"></i> Base catastral
        </a>
        <h1 class="h3 mb-0 fw-bold my-4">ASIGNACIÓN DE TRÁMITES</h1>
        <div class="d-flex gap-2 ms-auto align-items-center" style="order:1;">
            <a href="index.php?page=tramites/acciones/ver_tramite_rad&cod=<?php echo urlencode($tramite['cod_tramite']); ?>"
                class="btn btn-md shadow-lg animated-button fw-bold btn-ver m-0"
                style="border-left:2px solid #022F55;border-right:2px solid #022F55;">
                <i class="bi bi-eye me-2 fw-bold "></i> Ver Radicación
            </a>
            <button type="button" class="btn btn-warning btn-md text-white fw-bold shadow-lg" data-bs-toggle="modal" data-bs-target="#modalTrazabilidad">
                <i class="bi bi-clock-history me-1"></i> Ver Trazabilidad
            </button>
        </div>
    </div>
    <?php if ($rol_usuario === 'reconocedor'): ?>
        <div class="container my-2">
            <div class=" row d-flex align-items-stretch justify-content-center">
                <?php
                if (
                    $rol_usuario !== 'ventanilla_catastral'
                    and $rol_usuario !== 'director_catastro'
                ):
                ?>
                    <div class="col-6 col-md-4 col-lg-2 align-items-center justify-content-center d-flex p-2">
                        <button
                            type="button"
                            class="btn h-100 w-100 shadow btn-rol-1"
                            onclick="verHistorialAsignacion('ventanilla_catastral', '<?php echo $cod_tramite; ?>')"
                            data-bs-toggle="modal"
                            data-bs-target="#modalHistorial">
                            <b>Ventanilla Catastral</b>
                            <?php renderBadge('ventanilla_catastral', $notificaciones, $mapRolIndex); ?>
                        </button>
                    </div>
                <?php endif; ?>
                <?php if ($rol_usuario !== 'director_catastro'): ?>
                    <div class="col-6 col-md-4 col-lg-2 align-items-center justify-content-center d-flex p-2">
                        <button type="button"
                            class="btn h-100 w-100 shadow btn-rol-2"
                            onclick="verHistorialAsignacion('procedencia_juridica', '<?php echo $cod_tramite; ?>')"
                            data-bs-toggle="modal" data-bs-target="#modalHistorial">
                            <b>Procedencia Jurídica</b>
                            <?php renderBadge('procedencia_juridica', $notificaciones, $mapRolIndex); ?>
                        </button>
                    </div>
                <?php endif; ?>
                <?php if (
                    $rol_usuario !== 'ventanilla_catastral' and $rol_usuario !== 'consolidacion'
                    and $rol_usuario !== 'editor' and $rol_usuario !== 'reconocedor'
                    and $rol_usuario !== 'atencion_procedencia' and $rol_usuario !== 'director_catastro'
                ): ?>
                    <div class="col-6 col-md-4 col-lg-2 align-items-center justify-content-center d-flex p-2">
                        <button type="button"
                            class="btn h-100 w-100 shadow btn-rol-3"
                            onclick="verHistorialAsignacion('atencion_procedencia', '<?php echo $cod_tramite; ?>')"
                            data-bs-toggle="modal" data-bs-target="#modalHistorial">
                            <b>Atención Procedencia</b>
                            <?php renderBadge('atencion_procedencia', $notificaciones, $mapRolIndex); ?>
                        </button>
                    </div>
                <?php endif; ?>
                <?php if (
                    $rol_usuario !== 'procedencia_juridica' and $rol_usuario !== 'coordinacion_tecnica'
                    and $rol_usuario !== 'consolidacion' and $rol_usuario !== 'editor'
                    and $rol_usuario !== 'reconocedor' and $rol_usuario !== 'atencion_procedencia'
                ): ?>
                    <div class="col-6 col-md-4 col-lg-2 align-items-center justify-content-center d-flex p-2">
                        <button type="button"
                            class="btn h-100 w-100 shadow btn-rol-4 "
                            onclick="verHistorialAsignacion('revision_juridica', '<?php echo $cod_tramite; ?>')"
                            data-bs-toggle="modal" data-bs-target="#modalHistorial">
                            <b>Revisión Jurídica</b>
                            <?php renderBadge('revision_juridica', $notificaciones, $mapRolIndex); ?>
                        </button>
                    </div>
                <?php endif; ?>
                <?php if (
                    $rol_usuario !== 'procedencia_juridica' and $rol_usuario !== 'coordinacion_tecnica'
                    and $rol_usuario !== 'atencion_procedencia'
                ): ?>
                    <div class="col-6 col-md-4 col-lg-2 align-items-center justify-content-center d-flex p-2">
                        <button type="button"
                            class="btn h-100 w-100 shadow btn-rol-5"
                            onclick="verHistorialAsignacion('coordinacion_tecnica', '<?php echo $cod_tramite; ?>')"
                            data-bs-toggle="modal" data-bs-target="#modalHistorial">
                            <b>Coordinación Técnica</b>
                            <?php renderBadge('coordinacion_tecnica', $notificaciones, $mapRolIndex); ?>
                        </button>
                    </div>
                <?php endif; ?>
                <?php if (
                    $rol_usuario !== 'procedencia_juridica' and $rol_usuario !== 'coordinacion_tecnica'
                    and $rol_usuario !== 'revision_juridica' and $rol_usuario !== 'componente_economico'
                    and $rol_usuario !== 'avaluos' and $rol_usuario !== 'consolidacion'
                    and $rol_usuario !== 'atencion_procedencia'
                ): ?>
                    <div class="col-6 col-md-4 col-lg-2 align-items-center justify-content-center d-flex p-2">
                        <button type="button"
                            class="btn h-100 w-100 shadow btn-rol-6"
                            onclick="verHistorialAsignacion('control_calidad', '<?php echo $cod_tramite; ?>')"
                            data-bs-toggle="modal" data-bs-target="#modalHistorial">
                            <b>Control Calidad</b>
                            <?php renderBadge('control_calidad', $notificaciones, $mapRolIndex); ?>
                        </button>
                    </div>
                <?php endif; ?>
                <?php if (
                    $rol_usuario !== 'procedencia_juridica' and $rol_usuario !== 'coordinacion_tecnica'
                    and $rol_usuario !== 'revision_juridica' and $rol_usuario !== 'componente_economico'
                    and $rol_usuario !== 'avaluos' and $rol_usuario !== 'control_calidad'
                    and $rol_usuario !== 'consolidacion' and $rol_usuario !== 'atencion_procedencia'
                    and $rol_usuario !== 'director_catastro'
                ): ?>
                    <div class="col-6 col-md-4 col-lg-2 align-items-center justify-content-center d-flex p-2">
                        <button type="button"
                            class="btn h-100 w-100 shadow btn-rol-7"
                            onclick="verHistorialAsignacion('consolidacion', '<?php echo $cod_tramite; ?>')"
                            data-bs-toggle="modal" data-bs-target="#modalHistorial">
                            <b>Consolidación</b>
                            <?php renderBadge('consolidacion', $notificaciones, $mapRolIndex); ?>
                        </button>
                    </div>
                <?php endif; ?>
                <?php if (
                    $rol_usuario !== 'procedencia_juridica' and $rol_usuario !== 'coordinacion_tecnica'
                    and $rol_usuario !== 'revision_juridica' and $rol_usuario !== 'componente_economico'
                    and $rol_usuario !== 'avaluos' and $rol_usuario !== 'control_calidad'
                    and $rol_usuario !== 'consolidacion' and $rol_usuario !== 'editor'
                    and $rol_usuario !== 'reconocedor' and $rol_usuario !== 'atencion_procedencia'
                ): ?>
                    <div class="col-6 col-md-4 col-lg-2 align-items-center justify-content-center d-flex p-2 ">
                        <button type="button"
                            class="btn h-100 w-100 shadow btn-rol-8"
                            onclick="verHistorialAsignacion('componente_economico', '<?php echo $cod_tramite; ?>')"
                            data-bs-toggle="modal" data-bs-target="#modalHistorial">
                            <b>Lider Economico</b>
                            <?php renderBadge('componente_economico', $notificaciones, $mapRolIndex); ?>
                        </button>
                    </div>
                <?php endif; ?>
                <?php if (
                    $rol_usuario !== 'procedencia_juridica' and $rol_usuario !== 'coordinacion_tecnica'
                    and $rol_usuario !== 'revision_juridica' and $rol_usuario !== 'componente_economico'
                    and $rol_usuario !== 'avaluos' and $rol_usuario !== 'control_calidad'
                    and $rol_usuario !== 'consolidacion' and $rol_usuario !== 'editor'
                    and $rol_usuario !== 'atencion_procedencia' and $rol_usuario !== 'director_catastro'
                ): ?>
                    <div class="col-6 col-md-4 col-lg-2 align-items-center justify-content-center d-flex p-2 ">
                        <button type="button"
                            class="btn h-100 w-100 shadow btn-rol-9"
                            onclick="verHistorialAsignacion('editor', '<?php echo $cod_tramite; ?>')" data-bs-toggle="modal"
                            data-bs-target="#modalHistorial">
                            <b>Editor</b>
                            <?php renderBadge('editor', $notificaciones, $mapRolIndex); ?>
                        </button>
                    </div>
                <?php endif; ?>
                <?php if (
                    $rol_usuario !== 'procedencia_juridica' and $rol_usuario !== 'coordinacion_tecnica'
                    and $rol_usuario !== 'revision_juridica' and $rol_usuario !== 'componente_economico'
                    and $rol_usuario !== 'avaluos' and $rol_usuario !== 'control_calidad'
                    and $rol_usuario !== 'consolidacion' and $rol_usuario !== 'editor'
                    and $rol_usuario !== 'reconocedor' and $rol_usuario !== 'atencion_procedencia'
                    and $rol_usuario !== 'director_catastro'
                ): ?>
                    <div class="col-6 col-md-4 col-lg-2 align-items-center justify-content-center d-flex p-2">
                        <button type="button"
                            class="btn h-100 w-100 shadow btn-rol-10"
                            onclick="verHistorialAsignacion('avaluos', '<?php echo $cod_tramite; ?>')" data-bs-toggle="modal"
                            data-bs-target="#modalHistorial">
                            <b>Avaluo</b>
                            <?php renderBadge('avaluos', $notificaciones, $mapRolIndex); ?>
                        </button>
                    </div>
                <?php endif; ?>
                <?php if (
                    $rol_usuario !== 'procedencia_juridica' and $rol_usuario !== 'coordinacion_tecnica'
                    and $rol_usuario !== 'revision_juridica' and $rol_usuario !== 'componente_economico'
                    and $rol_usuario !== 'avaluos' and $rol_usuario !== 'control_calidad'
                    and $rol_usuario !== 'consolidacion' and $rol_usuario !== 'editor'
                    and $rol_usuario !== 'reconocedor' and $rol_usuario !== 'atencion_procedencia'
                    and $rol_usuario !== 'director_catastro'
                ): ?>
                    <div class="col-6 col-md-4 col-lg-2 align-items-center justify-content-center d-flex p-2">
                        <button type="button"
                            class="btn h-100 w-100 shadow btn-rol-11"
                            onclick="verHistorialAsignacion('reconocedor', '<?php echo $cod_tramite; ?>')"
                            data-bs-toggle="modal" data-bs-target="#modalHistorial">
                            <b>Reconocimiento</b>
                            <?php renderBadge('reconocedor', $notificaciones, $mapRolIndex); ?>
                        </button>
                    </div>
                <?php endif; ?>
                <?php if ($rol_usuario !== 'director_catastro' and $rol_usuario !== 'reconocedor'): ?>
                    <div class="col-6 col-md-4 col-lg-2 align-items-center justify-content-center d-flex p-2">
                        <button
                            type="button"
                            class="btn h-100 w-100 shadow btn-rol-12"
                            onclick="verHistorialAsignacion('director_catastro', '<?php echo $cod_tramite; ?>')"
                            data-bs-toggle="modal"
                            data-bs-target="#modalHistorial">
                            <b>Director Catastro</b>
                            <?php renderBadge('director_catastro', $notificaciones, $mapRolIndex); ?>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    <div class="modal fade" id="modalHistorial" tabindex="-1" aria-labelledby="modalHistorialLabel" aria-hidden="true"
        style="z-index: 2000;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" id="historialContenido">
                    Cargando historial...
                </div>
            </div>
        </div>
    </div>
    <!-- Content Row -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4 p-3 text-center">
                <div style="background-color: #022F55;" class="rounded-3">
                    <h5 class="text-white text-center py-2 m-0">DATOS DEL USUARIO ORIGINADOR</h5>
                </div>
                <div class="card-body">
                    <form id="miFormulario" action="index.php?page=seguimiento/acciones/procesar_entrega_segun_rol" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-4 p-1 px-2 my-2">
                                    <label for="cod_tramite" class="form-label fw-bold" style="font-size:0.9em;">ID de radicaci�n</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-file-earmark-binary"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="cod_tramite"
                                            name="entrega_cod_tramite"
                                            value="<?php echo htmlspecialchars($tramite['cod_tramite']); ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4 p-1 px-2 my-2">
                                    <label for="fecha_rad" class="form-label fw-bold" style="font-size:0.9em;">Hora radicaci�n del tr�mite</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="fecha_rad"
                                            name="historial_fecha_tramite"
                                            value="<?php echo htmlspecialchars($tramite['fecha_rad']); ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4 p-1 px-2 my-2">
                                    <label for="creacion_tram_cc_usuario" class="form-label fw-bold" style="font-size:0.9em">C�dula del usuario</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="creacion_tram_cc_usuario" name="creacion_tram_cc_usuario"
                                            value="<?php echo $mostrar_remitente_cc_usuario; ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4 p-1 px-2 my-2">
                                    <label for="creacion_tram_nombre_usuario" class="form-label fw-bold" style="font-size:0.9em">Nombres del usuario</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="creacion_tram_nombre_usuario" name="creacion_tram_nombre_usuario"
                                            value="<?php echo $mostrar_remitente_nombre_usuario; ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4 p-1 px-2 my-2">
                                    <label for="creacion_tram_apellido_usuario" class="form-label fw-bold" style="font-size:0.9em">Apellidos del usuario</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi-people-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="creacion_tram_apellido_usuario" name="creacion_tram_apellido_usuario"
                                            value="<?php echo $mostrar_remitente_apellido_usuario; ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4 p-1 px-2 my-2">
                                    <label for="creacion_tram_rol_usuario" class="form-label fw-bold" style="font-size:0.9em">Cargo</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-gear"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="creacion_tram_rol_usuario" name="creacion_tram_rol_usuario"
                                            value="<?php echo $mostrar_remitente_rol_usuario; ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-12 p-1 px-2 m-2 mx-auto">
                                    <div class="row justify-content-center">
                                        <?php if ($docs): ?>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?php if (!empty($docs["doc$i"])): ?>
                                                    <div class="col-6 p-2">
                                                        <div class="card card-documentos shadow h-100 p-3 border d-flex flex-column text-center">
                                                            <label for="npn_predio" class="form-label fw-bold">Documentos Adjuntos</label>
                                                            <div class="input-group shadow-sm mb-2">
                                                                <span class="input-group-text"> <i class="bi bi-file-earmark-pdf-fill me-2"></i> <b>Documento <?= $i ?>:</b> </span>
                                                                <input type="text" class="form-control" style="font-size: 0.9em;" id="npn_predio" name="npn_predio"
                                                                    value="<?= htmlspecialchars($docs["tipo_doc$i"] ?? "Sin descripción") ?> " readonly>
                                                            </div>
                                                            <div class="d-flex justify-content-center gap-2 px-4 my-2">
                                                                <a href="<?= $docs["doc$i"] ?>" target="_blank" class="bot_verenotrapesta btn btn-sm">
                                                                    <i class="bi bi-box-arrow-right"></i> Ver en otra pestaña
                                                                </a>
                                                                <button type="button" class="bot_mostrar_vista btn btn-sm"
                                                                    onclick="toggleIframe('visor_doc<?= $i ?>',this)">
                                                                    <i class="bi bi-eye"></i> <span>Mostrar Vista Previa</span>
                                                                </button>
                                                            </div>
                                                            <div id="visor_doc<?= $i ?>" class="iframe-animado">
                                                                <iframe src="<?= $docs["doc$i"] ?>" width="100%" height="750px" style="border:1px solid #ccc;"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        <?php else: ?>
                                            <div class="col-12 text-center">
                                                <span class="text-muted">No hay documentos cargados</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-12 form-group">
                                    <label for="observacion_asignacion" class="my-2"><b>Observaciones de la entrega</b></label>
                                    <textarea class="form-control text-center d-flex "
                                        id="observacion_asignacion"
                                        name="observacion_asignacion"
                                        rows="3"
                                        style="background-color:#ff7e3626; color:#333;"
                                        disabled><?php echo $observacion_a_usuario_tramite; ?></textarea>
                                </div>
                                <?php if ($requiere_resolucion || ($ruta_resolucion && in_array($rol_flujo_actual, ['director_catastro', 'ventanilla_catastral'], true))): ?>
                                    <div class="col-12 my-3">
                                        <div class="resolucion-box shadow-sm">
                                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                                <div>
                                                    <h6 class="fw-bold mb-1">Resolucion del tramite</h6>
                                                    <small class="text-muted">
                                                        <?php echo $ruta_resolucion ? 'Ya existe una resolucion asociada a este tramite.' : 'Procedencia debe generar y guardar la resolucion antes de aprobar.'; ?>
                                                    </small>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <?php if ($ruta_resolucion): ?>
                                                        <a href="<?php echo htmlspecialchars($ruta_resolucion); ?>" target="_blank" class="btn btn-outline-info btn-sm">
                                                            <i class="bi bi-box-arrow-up-right"></i> Ver resolucion
                                                        </a>
                                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleIframe('visor_resolucion', this)">
                                                            <i class="bi bi-eye"></i> <span>Mostrar vista previa</span>
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if ($requiere_resolucion): ?>
                                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalResolucionProcedencia">
                                                            <i class="bi bi-file-earmark-plus"></i> Generar resolucion
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php if ($ruta_resolucion): ?>
                                                <div id="visor_resolucion" class="iframe-animado mt-3">
                                                    <iframe src="<?php echo htmlspecialchars($ruta_resolucion); ?>" width="100%" height="650px" style="border:1px solid #ccc;"></iframe>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($rol_flujo_actual === 'director_catastro' && $id_resolucion && !$has_director_resolucion): ?>
                                                <div class="mt-3">
                                                    <label for="pdf_director" class="form-label fw-bold">Resolucion firmada por director</label>
                                                    <div class="input-group">
                                                        <input type="file" class="form-control" id="pdf_director" accept="application/pdf">
                                                        <button type="button" class="btn btn-success" onclick="uploadResolucionDirector()">Guardar firmada</button>
                                                    </div>
                                                    <div id="status_director" class="mt-1"></div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($requiere_resolucion): ?>
                            <div class="modal fade modal-resolucion" id="modalResolucionProcedencia" tabindex="-1" aria-hidden="true" style="z-index: 2000;">
                                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header text-white" style="background-color:#022F55;">
                                            <h5 class="modal-title">Creacion de resolucion</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="pj-resolution-grid">
                                                <div>
                                                    <h6 class="text-white text-center py-2 rounded-3" style="background-color:#022F55;">
                                                        <i class="bi bi-pencil-square me-1"></i> Editor del documento
                                                    </h6>
                                                    <div id="documentEditor" class="pj-document-editor" contenteditable="true">
                                                        <div class="pj-doc-header">
                                                            <div class="pj-header-left">
                                                                <img src="assets/img/logo_final_arbitrium.png" alt="Arbimaps" class="pj-escudo">
                                                                <div>
                                                                    <strong>GESTION CATASTRAL</strong><br>
                                                                    ARBITRIUM S.A.S.<br>
                                                                    NIT: 900.749.675-1
                                                                </div>
                                                            </div>
                                                            <div class="pj-header-center">
                                                                ACTO ADMINISTRATIVO<br>
                                                                RESOLUCION
                                                            </div>
                                                            <div class="pj-header-right">
                                                                <div class="pj-codigo-box">
                                                                    CODIGO: GC-PQR-RES<br>
                                                                    VERSION: 01<br>
                                                                    VIGENCIA: 2026
                                                                </div>
                                                                <img src="assets/img/logo_final_arbitrium.png" alt="Arbimaps" class="pj-logo-alc">
                                                            </div>
                                                        </div>

                                                        <div class="pj-resolution-number">
                                                            <strong>RESOLUCION No. ________</strong>
                                                        </div>
                                                        <div class="pj-doc-date">
                                                            Neiva, <?php echo date('Y-m-d'); ?>
                                                        </div>
                                                        <div class="pj-doc-title">
                                                            "POR MEDIO DE LA CUAL SE RESUELVE UNA SOLICITUD CATASTRAL"
                                                        </div>
                                                        <div class="pj-emisor-cargo">
                                                            EL DIRECTOR LOCAL ADMINISTRATIVO DE CATASTRO
                                                        </div>

                                                        <div class="pj-doc-body">
                                                            <p>
                                                                En uso de sus facultades legales y en especial las conferidas por la normativa
                                                                catastral vigente,
                                                            </p>
                                                        </div>

                                                        <div class="pj-considerando-title">CONSIDERANDO</div>

                                                        <div class="pj-doc-body">
                                                            <p>
                                                                Que mediante radicado No. <strong><?php echo htmlspecialchars($cod_tramite); ?></strong>,
                                                                recibido el <strong><?php echo htmlspecialchars($tramite['hora_radicacion'] ?? date('Y-m-d')); ?></strong>,
                                                                se adelanta el tramite <strong><?php echo htmlspecialchars($tramite['tipo_tramite'] ?? ''); ?></strong>.
                                                            </p>
                                                            <p>
                                                                Que el predio asociado al expediente se identifica con NPN
                                                                <strong><?php echo htmlspecialchars($info_predio['npn_predio'] ?? ''); ?></strong>.
                                                            </p>
                                                            <p>
                                                                Que revisados los documentos, soportes y actuaciones surtidas dentro del expediente,
                                                                procede emitir el acto administrativo correspondiente.
                                                            </p>
                                                            <p>En merito de lo expuesto,</p>
                                                        </div>

                                                        <div class="pj-resuelve-title">RESUELVE</div>

                                                        <div class="pj-articulos">
                                                            <div class="pj-articulo">
                                                                <p><strong>ARTICULO PRIMERO:</strong> Resolver la solicitud catastral presentada dentro del radicado indicado, conforme a la revision tecnica y juridica obrante en el expediente.</p>
                                                            </div>
                                                            <div class="pj-articulo">
                                                                <p><strong>ARTICULO SEGUNDO:</strong> Ordenar las actualizaciones, anotaciones o actuaciones catastrales a que haya lugar, de acuerdo con la decision adoptada.</p>
                                                            </div>
                                                            <div class="pj-articulo">
                                                                <p><strong>ARTICULO TERCERO:</strong> Notificar el presente acto administrativo al interesado, conforme al procedimiento establecido.</p>
                                                            </div>
                                                            <div class="pj-articulo">
                                                                <p><strong>ARTICULO CUARTO:</strong> Contra la presente decision proceden los recursos de ley, en los terminos aplicables.</p>
                                                            </div>
                                                        </div>

                                                        <div class="pj-doc-signature">
                                                            <div class="pj-firma-linea"></div>
                                                            <p><strong>DIRECTOR LOCAL ADMINISTRATIVO DE CATASTRO</strong></p>
                                                        </div>

                                                        <div class="pj-doc-footer">
                                                            <p>Proyecto: <?php echo htmlspecialchars(trim($nombre_usuario . ' ' . $apellido_usuario)); ?></p>
                                                            <p>Rol: <?php echo htmlspecialchars($rol_flujo_actual); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h6 class="text-white text-center py-2 rounded-3" style="background-color:#022F55;">
                                                        <i class="bi bi-eye-fill me-1"></i> Vista previa en linea
                                                    </h6>
                                                    <div id="documentPreview" class="pj-document-preview"></div>
                                                </div>
                                            </div>
                                            <div class="alert alert-info mt-3 mb-0">
                                                Edita el documento en el panel izquierdo. La vista previa se actualiza en linea y esa version se guarda como PDF del tramite.
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                            <button type="button" class="btn btn-primary" onclick="guardarDocumento()">
                                                <i class="bi bi-file-earmark-pdf me-1"></i> Guardar PDF resolucion
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div style="background-color: #022F55;" class="rounded-3 my-3">
                            <h5 class="text-white text-center py-2 m-0">DESTINATARIO DE LA ASIGNACIÓN PARA REVISIÓN</h5>
                        </div>
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-4 p-1 px-2 my-2">
                                    <label for="entrega_cc_usuario" class="form-label fw-bold" style="font-size:0.9em">Cédula del usuario</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="entrega_cc_usuario" name="entrega_cc_usuario"
                                            value="<?php echo $entrega_cc_usuario ?? ''; ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4 p-1 px-2 my-2">
                                    <label for="entrega_nombre_usuario" class="form-label fw-bold" style="font-size:0.9em">Nombres del usuario</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="entrega_nombre_usuario" name="entrega_nombre_usuario"
                                            value="<?php echo $entrega_nombre_usuario ?? ''; ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4 p-1 px-2 my-2">
                                    <label for="entrega_apellido_usuario" class="form-label fw-bold" style="font-size:0.9em">Apellidos del usuario</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi-people-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="entrega_apellido_usuario" name="entrega_apellido_usuario"
                                            value="<?php echo $entrega_apellido_usuario ?? ''; ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="entrega_rol_usuario" class="form-label fw-bold" style="font-size:0.9em">Cargo del usuario</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-gear"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="entrega_rol_usuario" name="entrega_rol_usuario"
                                            value="<?php echo $entrega_rol_usuario ?? ''; ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="fecha_limite" class="form-label fw-bold" style="font-size:0.9em; ">Fecha límite de respuesta a siguiente área</label>
                                    <div class="input-group shadow-sm ">
                                        <span class="input-group-text text-white" style="background-color: #022F55; border:1px solid #022F55"><i class="bi bi-calendar2-x"></i></span>
                                        <input type="date" style="border:1px solid #022F55" id="fecha_limite" class="form-control" name="fecha_limite" value="<?php echo date('Y-m-d', strtotime('+4 days')); ?>" readonly>
                                    </div>
                                </div>
                            </div><br>
                            <div class="col-md-12 my-4" style="border-bottom: 1px solid #02305561;"></div>
                            <div id="documentosContainer" class="px-5">
                                <div class="form-row documento-group ">
                                    <div class="col-md-12 form-group my-3 text-center align-center">
                                        <button type="button" class="btn btn-verr" id="agregarDocumento" style="border:1px solid #022F55; color:#022F55;">
                                            <i class="bi bi-file-earmark-plus"></i> Agregar otros documentos
                                        </button>
                                    </div>
                                    <div class="col-md-6 p-1 px-2 my-1 ">
                                        <label for="tipo_doc1" class="form-label" style="font-size:0.9em;"><b>Tipo de documento</b></label>
                                        <div class="input-group shadow-sm">
                                            <label class="input-group-text" for="tipo_doc1">
                                                <i class="bi bi-file-earmark-text-fill"></i>
                                            </label>
                                            <select class="form-select" style="font-size:0.9em;" id="tipo_doc1" name="tipo_doc1" required>
                                                <option value="" disabled selected>SELECCIONE</option>
                                                <option value="Avaluo_Catastral">Avaluo Catastral</option>
                                                <option value="Acto_Administrativo">Acto Administrativo</option>
                                                <option value="Acto_Judicial">Acto Judicial</option>
                                                <option value="Consulta_VUR">Consulta VUR</option>
                                                <option value="Devolución_Tramite">Devolución Tramite</option>
                                                <option value="Documento_Privado">Documento Privado</option>
                                                <option value="Escritura_Publica">Escrituras Públicas</option>
                                                <option value="Impuesto_Predial">Impuesto Predial</option>
                                                <option value="Informe_Visita">Informe de Visita</option>
                                                <option value="Informe_Edicion">Informe de Edición</option>
                                                <option value="Informe_Calidad">Informe de Calidad</option>
                                                <option value="Manzana_Catastral">Manzana Catastral</option>
                                                <option value="Notificacion">Notificación</option>
                                                <option value="Plano_Predial">Plano Predial</option>
                                                <option value="Resolución">Resolución</option>
                                                <option value="Otro">Otro</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class=" col-12 col-lg-6 p-1 px-2 my-2">
                                        <label for="nombre_doc1" class="form-label fw-bold" style="font-size:0.9em;">Documentos a cargar</label>
                                        <div class="input-group shadow-sm">
                                            <label class="input-group-text" for="nombre_doc1" style="font-size:0.9em;"><i class="bi bi-file-earmark-pdf-fill"></i></label>
                                            <input type="file" class="form-control" style="font-size:0.8em;" id="nombre_doc1" name="nombre_doc1">
                                        </div>
                                        <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 my-4" style="border-bottom: 1px solid #02305561;"></div>
                            <div class="form-group mx-3">
                                <label for="observacion_asignacion" class="mb-2"><b>Observaciones para la entrega</b></label>
                                <textarea class="form-control " id="observacion_asignacion" name="observacion_asignacion"
                                    placeholder="Ingrese una descripción/observación para informar al usuario correspondiente"></textarea>
                            </div>
                            <input type="hidden" id="historial_cc_usuario" name="historial_cc_usuario"
                                value="<?php echo htmlspecialchars($historial_cc_usuario); ?>">
                            <input type="hidden" id="cedula" name="cedula">
                            <input type="hidden" id="rol_usuario" name="rol_usuario">
                            <input type="hidden" id="asignacion_rol_usuario" name="asignacion_rol_usuario"
                                value="<?php echo htmlspecialchars($asignacion_rol_usuario); ?>">
                            <input type="hidden" name="rol_actual" value="<?php echo htmlspecialchars($rol_flujo_actual); ?>">
                            <input type="hidden" id="resolucion_guardada" name="resolucion_guardada"
                                value="<?php echo $ruta_resolucion ? '1' : '0'; ?>">
                            <input type="hidden" id="historial_nombre_usuario" name="historial_nombre_usuario">
                            <input type="hidden" id="historial_apellido_usuario" name="historial_apellido_usuario">
                            <div class="form-group mt-4 mb-0">
                                <div class="d-flex justify-content-center">
                                    <button type="submit" id="btnAprobar" name="accion" value="aprobar"
                                        data-requiere-resolucion="<?php echo $requiere_resolucion ? '1' : '0'; ?>"
                                        class="btn btn-success px-4">
                                        <i class="bi bi-bookmark-check-fill"></i> APROBAR
                                    </button>
                                    <script>
                                        document.getElementById("btnAprobar").addEventListener("click", function() {
                                            let cod = new URLSearchParams(window.location.search).get("cod");
                                            localStorage.setItem("aprobado_" + cod, "true");
                                        });
                                    </script>
                                    <div style="width: 1cm;"></div>
                                    <?php if ($rol_usuario !== 'reconocedor'): ?>
                                        <button type="button" class="btn btn-danger px-4" data-bs-toggle="modal" data-bs-target="#modalDevolver">
                                            <i class="bi bi-bookmark-x-fill me-1"></i>DEVOLVER
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- MODAL DEVOLVER TRAMITE -->
                    <div class="modal fade" id="modalDevolver" tabindex="-1" role="dialog" style="z-index: 1550;">
                        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white px-5">
                                    <h5 class="modal-title"><b>Devolver Trámite</b></h5>
                                    <button type="button" class="btn-close" style="background-color:white !important" data-bs-dismiss="modal"></button>
                                </div>
                                <form id="formModal" enctype="multipart/form-data">
                                    <input type="hidden" name="accion" id="accion" value="">
                                    <div class="modal-body">
                                        <input type="hidden" name="cod_tramite" value="<?php echo $tramite['cod_tramite']; ?>">
                                        <div class="form-row">
                                            <div class="col-md-6 p-1 px-2 my-2">
                                                <label for="cod_tramite" class="form-label fw-bold" style="font-size:0.9em">Identificador radicación</label>
                                                <div class="input-group shadow-sm">
                                                    <span class="input-group-text"><i class="bi-journal-text"></i></span>
                                                    <input type="text" class="form-control" style="font-size: 0.9em;" id="cod_tramite" name="entrega_cod_tramite"
                                                        value="<?php echo htmlspecialchars($tramite['cod_tramite']); ?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-6 p-1 px-2 my-2">
                                                <label for="fecha_rad" class="form-label fw-bold" style="font-size:0.9em">Fecha y hora de radicación</label>
                                                <div class="input-group shadow-sm">
                                                    <span class="input-group-text"><i class="bi bi-calendar-date"></i></span>
                                                    <input type="text" class="form-control" style="font-size: 0.9em;" id="fecha_rad" name="historial_fecha_tramite"
                                                        value="<?php echo htmlspecialchars($tramite['fecha_rad'] ?? ''); ?>" readonly>
                                                </div>
                                            </div>
                                        </div><br>
                                        <div class="form-group">
                                            <div class="form-row">
                                                <div class="col-md-3 p-1 px-2 ">
                                                    <label for="creacion_tram_cc_usuario" class="form-label fw-bold" style="font-size:0.9em">Cédula del interesado</label>
                                                    <div class="input-group shadow-sm">
                                                        <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="creacion_tram_cc_usuario_modal" name="creacion_tram_cc_usuario"
                                                            value="<?php echo $devolver_cc_usuario; ?>" readonly>
                                                        <input type="hidden"
                                                            id="creacion_tram_cc_usuario_modal"
                                                            name="creacion_tram_cc_usuario"
                                                            value="<?php echo $devolver_cc_usuario; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3 p-1 px-2 ">
                                                    <label for="creacion_tram_nombre_usuario" class="form-label fw-bold" style="font-size:0.9em">Nombres del usuario</label>
                                                    <div class="input-group shadow-sm">
                                                        <span class="input-group-text"><i class="bi bi-person-bounding-box"></i></span>
                                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="creacion_tram_nombre_usuario_modal" name="creacion_tram_nombre_usuario"
                                                            value="<?php echo $devolver_nombre_usuario; ?>" readonly>
                                                        <input type="hidden"
                                                            name="creacion_tram_nombre_usuario"
                                                            value="<?php echo $devolver_nombre_usuario; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3 p-1 px-2 ">
                                                    <label for="creacion_tram_apellido_usuario" class="form-label fw-bold" style="font-size:0.9em">Apellido del usuario</label>
                                                    <div class="input-group shadow-sm">
                                                        <span class="input-group-text"><i class="bi-people-fill"></i></span>
                                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="creacion_tram_apellido_usuario_modal" name="creacion_tram_apellido_usuario"
                                                            value="<?php echo $devolver_apellido_usuario; ?>" readonly>
                                                        <input type="hidden"
                                                            name="creacion_tram_apellido_usuario"
                                                            value="<?php echo $devolver_apellido_usuario; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3 p-1 px-2 ">
                                                    <label for="creacion_tram_rol_usuario" class="form-label fw-bold" style="font-size:0.9em">Cargo</label>
                                                    <div class="input-group shadow-sm">
                                                        <span class="input-group-text"><i class="bi bi-person-bounding-box"></i></span>
                                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="creacion_tram_rol_usuario_modal" name="creacion_tram_rol_usuario"
                                                            value="<?php echo $devolver_rol_usuario; ?>" readonly>
                                                        <input type="hidden"
                                                            name="creacion_tram_rol_usuario"
                                                            value="<?php echo $devolver_rol_usuario; ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 p-1 px-5 my-3">
                                                <label for="documento_soporte" class="form-label fw-bold">Documento soporte (opcional)</label>
                                                <div class="input-group mb-1 shadow-sm">
                                                    <label class="input-group-text" for="documento_soporte" style="font-size:0.9em;"><i class="bi bi-file-earmark-pdf-fill"></i></label>
                                                    <input type="file" class="form-control" style="font-size:0.8em;" id="documento_soporte" name="documento_soporte" accept=".pdf,.jpg,.png,.doc,.docx">
                                                </div>
                                                <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label><b>Motivo de la devolución:</b></label>
                                            <textarea class="form-control" name="motivo_devolucion" rows="3" style="background-color: #0230554d;" required></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" id="btnDevolver" class="btn btn-danger">
                                            <i class="bi bi-arrow-return-left me-2"></i> Confirmar Devolución
                                        </button>
                                        <button type="button" class="btn text-white" style="background-color: #022F55;" data-bs-dismiss="modal">Cancelar</button>
                                    </div>
                                    <input type="hidden"
                                        name="rol_actual"
                                        value="<?php echo htmlspecialchars($rol_flujo_actual); ?>">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function toggleIframe(id, boton) {
        const visor = document.getElementById(id);
        const icono = boton.querySelector("i");
        const texto = boton.querySelector("span");
        visor.classList.toggle("mostrar");
        if (visor.classList.contains("mostrar")) {
            icono.classList.replace("bi-eye", "bi-eye-slash");
            texto.textContent = "Ocultar Vista Previa";
        } else {
            icono.classList.replace("bi-eye-slash", "bi-eye");
            texto.textContent = "Mostrar Vista Previa";
        }
    }
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let contadorDocs = 1;
        const maxDocs = 5;
        document.getElementById("agregarDocumento").addEventListener("click", function() {
            if (contadorDocs >= maxDocs) {
                alert("Solo puedes agregar hasta 5 documentos.");
                return;
            }
            contadorDocs++;
            const container = document.getElementById("documentosContainer");
            const nuevoGrupo = document.createElement("div");
            nuevoGrupo.classList.add("form-row", "documento-group", "mt-3");
            nuevoGrupo.innerHTML = `
            <div class="col-md-6 p-1 px-2 my-2 ">
                <label for="tipo_doc1" class="form-label" style="font-size:0.9em;"><b>Seleccione el trámite</b></label>
                    <div class="input-group shadow-sm">
                    <label class="input-group-text" for="tipo_doc1">
                        <i class="bi bi-file-earmark-text-fill"></i>
                    </label>
                            <select class="form-select" id="tipo_doc1" style="font-size:0.9em;" name="tipo_doc${contadorDocs}" required>
                                <option value="" disabled selected>SELECCIONE</option>
                                <option value="Avaluo_Catastral">Avaluo Catastral</option>
                                <option value="Acto_Administrativo">Acto Administrativo</option>
                                <option value="Acto_Judicial">Acto Judicial</option>
                                <option value="Consulta_VUR">Consulta VUR</option>
                                <option value="Devolución_Tramite">Devolución Tramite</option>
                                <option value="Documento_Privado">Documento Privado</option>
                                <option value="Escritura_Publica">Escrituras Públicas</option>
                                <option value="Impuesto_Predial">Impuesto Predial</option>
                                <option value="Informe_Visita">Informe de Visita</option>
                                <option value="Informe_Edicion">Informe de Edición</option>
                                <option value="Informe_Calidad">Informe de Calidad</option>
                                <option value="Manzana_Catastral">Manzana Catastral</option>
                                <option value="Notificacion">Notificación</option>
                                <option value="Plano_Predial">Plano Predial</option>
                                <option value="Resolución">Resolución</option>
                                <option value="Otro">Otro</option>
                            </select>
                    </div>
            </div>
            <div class=" col-md-6 p-1 px-2 my-2">
                <label for="nombre_doc1" class="form-label fw-bold">Documentos a cargar</label>
                    <div class="input-group shadow-sm">
                <label class="input-group-text" for="nombre_doc1" style="font-size:0.9em;"><i class="bi bi-file-earmark-pdf-fill"></i></label>
                        <input type="file" class="form-control" style="font-size:0.8em;" id="nombre_doc1" name="nombre_doc${contadorDocs}">
                    </div>
                    <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
            </div>
        `;
            container.appendChild(nuevoGrupo);
        });

        function verHistorialAsignacion(rol, codTramite) {
            console.log("Enviando:", rol, codTramite);
            fetch('ajax/ver_historial_rol.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `rol=${encodeURIComponent(rol)}&cod_tramite=${encodeURIComponent(codTramite)}`
                })
                .then(response => response.text())
                .then(data => {
                    document.getElementById('historialContenido').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('historialContenido').innerHTML = 'Error al cargar el historial.';
                    console.error('Error en fetch:', error);
                });
        }
    });

    document.getElementById("btnDevolver")?.addEventListener("click", function() {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¿Deseas devolver este trámite?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#002F55',
            confirmButtonText: 'Sí, devolver',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                let modalForm = document.getElementById("formModal");
                let formData = new FormData(modalForm);
                formData.set("accion", "devolver");
                fetch("vistas/seguimiento/acciones/procesar_devolucion.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.text().then(text => {
                        if (!response.ok) {
                            throw new Error(text || "No se pudo procesar la devolucion.");
                        }
                        return text;
                    }))
                    .then(data => {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Devolución realizada!',
                            text: data || "El tramite fue devuelto correctamente.",
                            confirmButtonColor: '#02722d'
                        }).then(() => {
                            window.location.href = "index.php?page=seguimiento/mis_revisiones";
                        });
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al procesar la devolución.'
                        });
                        console.error("Error:", error);
                    });
            }
        });
    });

    document.getElementById("miFormulario")?.addEventListener("submit", function(e) {
        const btnAprobar = document.getElementById("btnAprobar");
        const requiereResolucion = btnAprobar?.dataset.requiereResolucion === "1";
        const resolucionGuardada = document.getElementById("resolucion_guardada")?.value === "1";
        const accion = e.submitter?.value || "";
        if (accion === "aprobar" && requiereResolucion && !resolucionGuardada) {
            e.preventDefault();
            Swal.fire({
                icon: "warning",
                title: "Resolucion requerida",
                text: "Primero genera y guarda el PDF de resolucion para poder pasar el tramite a Director.",
                confirmButtonColor: "#022F55"
            });
        }
    });

    document.addEventListener("DOMContentLoaded", function() {
        const editor = document.getElementById("documentEditor");
        const preview = document.getElementById("documentPreview");
        if (!editor || !preview) return;

        const syncResolucionPreview = () => {
            preview.innerHTML = editor.innerHTML;
        };

        syncResolucionPreview();
        editor.addEventListener("input", syncResolucionPreview);
    });

    async function guardarDocumento() {
        const editor = document.getElementById("documentEditor");
        const preview = document.getElementById("documentPreview");
        const statusInput = document.getElementById("resolucion_guardada");
        if (!preview) {
            Swal.fire("Error", "No se encontro la plantilla de resolucion.", "error");
            return;
        }
        if (typeof html2pdf === "undefined") {
            Swal.fire("Error", "No cargo la libreria para generar PDF. Recarga la pagina e intenta otra vez.", "error");
            return;
        }

        try {
            Swal.fire({
                title: "Generando resolucion",
                text: "Guardando PDF en el expediente del tramite...",
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            if (editor) {
                preview.innerHTML = editor.innerHTML;
            }
            preview.classList.add("exporting");
            const pdfBlob = await html2pdf()
                .from(preview)
                .set({
                    margin: 0.6,
                    filename: `resolucion_${new Date().toISOString().slice(0, 19).replace(/:/g, "-")}.pdf`,
                    image: { type: "jpeg", quality: 0.98 },
                    html2canvas: { scale: 2 },
                    jsPDF: { unit: "in", format: "letter", orientation: "portrait" }
                })
                .outputPdf("blob");

            const formData = new FormData();
            formData.append("cod_tramite", "<?php echo htmlspecialchars($cod_tramite); ?>");
            formData.append("pdf_file", pdfBlob, "resolucion.pdf");

            const response = await fetch("vistas/seguimiento/acciones/guardar_resolucion.php", {
                method: "POST",
                body: formData
            });
            const responseText = await response.text();
            const jsonStart = responseText.lastIndexOf("{");
            if (jsonStart === -1) {
                throw new Error(responseText.trim().slice(0, 250) || "El servidor no devolvio JSON.");
            }
            const result = JSON.parse(responseText.slice(jsonStart));
            if (!result.success) {
                throw new Error(result.message || "No se pudo guardar la resolucion.");
            }

            if (statusInput) statusInput.value = "1";
            preview.classList.remove("exporting");
            const modal = bootstrap.Modal.getInstance(document.getElementById("modalResolucionProcedencia"));
            if (modal) modal.hide();
            Swal.fire({
                icon: "success",
                title: "Resolucion guardada",
                text: result.message || "El PDF quedo guardado. Ya puedes aprobar hacia Director.",
                confirmButtonColor: "#022F55"
            });
        } catch (error) {
            preview.classList.remove("exporting");
            console.error("Error resolucion:", error);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: error.message || "Hubo un problema al generar o guardar el documento."
            });
        }
    }

    async function uploadResolucionDirector() {
        const fileInput = document.getElementById("pdf_director");
        const file = fileInput?.files?.[0];
        const statusDiv = document.getElementById("status_director");
        if (!file) {
            Swal.fire("Archivo requerido", "Selecciona un PDF para guardar la resolucion firmada.", "warning");
            return;
        }
        if (file.type !== "application/pdf") {
            Swal.fire("Formato invalido", "Solo se permiten archivos PDF.", "error");
            return;
        }
        const formData = new FormData();
        formData.append("pdf_file", file);
        formData.append("cod_tramite", "<?php echo htmlspecialchars($cod_tramite); ?>");
        formData.append("id_resolucion", "<?php echo (int)($id_resolucion ?? 0); ?>");
        if (statusDiv) statusDiv.innerHTML = '<small class="text-info">Subiendo...</small>';
        try {
            const response = await fetch("vistas/seguimiento/acciones/guardar_resolucion_director.php", {
                method: "POST",
                body: formData
            });
            const responseText = await response.text();
            const jsonStart = responseText.lastIndexOf("{");
            if (jsonStart === -1) {
                throw new Error(responseText.trim().slice(0, 250) || "El servidor no devolvio JSON.");
            }
            const result = JSON.parse(responseText.slice(jsonStart));
            if (!result.success) {
                throw new Error(result.message || "No se pudo guardar el PDF firmado.");
            }
            if (statusDiv) statusDiv.innerHTML = '<small class="text-success">Guardado correctamente.</small>';
            Swal.fire("Listo", result.message || "Resolucion firmada guardada.", "success");
        } catch (error) {
            console.error("Error director:", error);
            if (statusDiv) statusDiv.innerHTML = '<small class="text-danger">Error al subir.</small>';
            Swal.fire("Error", error.message || "Hubo un problema al subir el archivo.", "error");
        }
    }

    function verHistorialAsignacion(rol, codTramite) {
        console.log("Enviando:", rol, codTramite);
        fetch('vistas/tramites/ajax/ver_historial_rol.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `rol=${encodeURIComponent(rol)}&cod_tramite=${encodeURIComponent(codTramite)}`
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById('historialContenido').innerHTML = data;
            })
            .catch(error => {
                document.getElementById('historialContenido').innerHTML = 'Error al cargar el historial.';
                console.error('Error en fetch:', error);
            });
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mapRolIndex = <?php echo json_encode($mapRolIndex); ?>;

        function contarItemsEnHTML(html) {
            if (!html) return 0;
            const doc = new DOMParser().parseFromString(html, 'text/html');
            let n = doc.querySelectorAll('table tbody tr').length;
            if (n) return n;
            const totalTr = doc.querySelectorAll('table tr').length;
            if (totalTr) {
                const theadTr = doc.querySelectorAll('table thead tr').length;
                const tfootTr = doc.querySelectorAll('table tfoot tr').length;
                let headerLike = 0;
                const firstTr = doc.querySelector('table tr');
                if (firstTr && firstTr.querySelectorAll('th').length > 0) headerLike = 1;
                const bodyTr = Math.max(0, totalTr - theadTr - tfootTr - headerLike);
                if (bodyTr) return bodyTr;
            }
            n = doc.querySelectorAll('ul > li, ol > li').length;
            if (n) return n;
            n = doc.querySelectorAll('.historial-item, .item, .registro, .fila, .documento-item').length;
            if (n) return n;
            n = doc.querySelectorAll('a[href$=".pdf"], a[href*="/tramites_conservacion/"]').length;
            if (n) return n;
            return 0;
        }

        function getCodBase() {
            return document.getElementById('cod_tramite')?.value || '';
        }
        async function fetchHtmlRol(rol) {
            const codBase = getCodBase();
            try {
                const body = new URLSearchParams({
                    rol: rol,
                    cod_tramite: codBase
                });
                const res = await fetch('vistas/tramites/ajax/ver_historial_rol.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: body.toString()
                });
                if (!res.ok) return '';
                return await res.text();
            } catch (e) {
                console.error('Error fetch historial rol', rol, e);
                return '';
            }
        }
        async function actualizarBadges() {
            const buttons = Array.from(document.querySelectorAll('button[onclick^="verHistorialAsignacion("]'));
            const codFull = document.getElementById('cod_tramite')?.value || '';
            for (const btn of buttons) {
                const m = btn.getAttribute('onclick')?.match(/verHistorialAsignacion\('([^']+)'/);
                if (!m) continue;
                const rol = m[1];

                let badge = btn.querySelector('.noti-badge');
                if (!badge) {
                    const idx = mapRolIndex[rol] ?? 1;
                    badge = document.createElement('span');
                    badge.className = `noti-badge noti-rol-${idx}`;
                    btn.appendChild(badge);
                }
                const html = await fetchHtmlRol(rol);
                const n = contarItemsEnHTML(html);
                if (n > 0) {
                    badge.textContent = n;
                    badge.style.display = 'inline-block';
                } else {
                    badge.remove();
                }
            }
        }
        actualizarBadges().catch(e => console.error(e));
        window.actualizarBadges = actualizarBadges;
    });
</script>

<!-- Modal de Trazabilidad -->
<div class="modal fade" id="modalTrazabilidad" tabindex="-1" aria-labelledby="modalTrazabilidadLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header text-white" style="background-color: #002F55; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <h5 class="modal-title fw-bold" id="modalTrazabilidadLabel">
                    <i class="bi bi-clock-history me-2"></i>Historial de Trazabilidad - <?php echo htmlspecialchars($cod_tramite); ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <?php if (empty($historial)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-info-circle-fill" style="font-size: 2.5rem;"></i>
                        <p class="mt-3">No hay historial registrado para este trámite.</p>
                    </div>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($historial as $index => $h): ?>
                            <div class="timeline-item mb-4 position-relative d-flex" style="text-align: left;">
                                <div class="timeline-badge-container d-flex flex-column align-items-center me-3">
                                    <div class="timeline-badge rounded-circle d-flex align-items-center justify-content-center text-white <?php echo $h['badge_class']; ?>" 
                                         style="width: 40px; height: 40px; z-index: 2;">
                                        <i class="bi <?php 
                                            echo match($h['tipo']) {
                                                'radicacion' => 'bi-file-earmark-plus',
                                                'asignacion' => 'bi-person-plus-fill',
                                                'revision' => 'bi-file-earmark-check-fill',
                                                'devolucion' => 'bi-arrow-left-circle-fill',
                                                'resolucion_procede' => 'bi-check-circle-fill',
                                                'resolucion_noprocede' => 'bi-x-circle-fill',
                                                'asignacion_activa', 'entrega_activa' => 'bi-hourglass-split',
                                                default => 'bi-dot'
                                            };
                                        ?>"></i>
                                    </div>
                                    <?php if ($index < count($historial) - 1): ?>
                                        <div class="timeline-line bg-secondary opacity-25" style="width: 2px; flex-grow: 1; min-height: 40px; margin-top: 5px;"></div>
                                    <?php endif; ?>
                                </div>
                                <div class="timeline-panel card border-0 shadow-sm rounded-3 flex-grow-1 p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold mb-0 text-primary" style="font-size: 1.05rem;">
                                            <?php echo htmlspecialchars($h['accion']); ?>
                                        </h6>
                                        <span class="badge bg-secondary opacity-75 text-white" style="font-size: 0.75rem;">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            <?php echo date('d/m/Y h:i A', strtotime($h['fecha'])); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="mb-2" style="font-size: 0.9rem;">
                                        <strong>Remitente/Actor:</strong> 
                                        <span class="text-dark"><?php echo htmlspecialchars($h['actor']); ?></span> 
                                        <?php if (!empty($h['rol_actor'])): ?>
                                            <span class="badge bg-light text-secondary border ms-1" style="font-size: 0.75rem; text-transform: uppercase;">
                                                <?php echo htmlspecialchars($h['rol_actor']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (!empty($h['destinatario'])): ?>
                                        <div class="mb-2" style="font-size: 0.9rem;">
                                            <strong>Destinatario/Asignado:</strong> 
                                            <span class="text-dark"><?php echo htmlspecialchars($h['destinatario']); ?></span>
                                            <?php if (!empty($h['rol_destinatario'])): ?>
                                                <span class="badge bg-light text-secondary border ms-1" style="font-size: 0.75rem; text-transform: uppercase;">
                                                    <?php echo htmlspecialchars($h['rol_destinatario']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($h['observacion'])): ?>
                                        <div class="mt-2 bg-light p-2 rounded text-muted mb-2" style="font-size: 0.85rem; border-left: 3px solid #ccc; font-style: italic;">
                                            "<?php echo htmlspecialchars($h['observacion']); ?>"
                                        </div>
                                    <?php endif; ?>

                                    <?php 
                                    $adjuntos_trazabilidad = [];
                                    for ($i = 1; $i <= 5; $i++) {
                                        if (!empty($h["doc$i"])) {
                                            $adjuntos_trazabilidad[] = [
                                                'ruta' => rutaWebDocumentoRevisionSegunRol($h["doc$i"]),
                                                'tipo' => $h["tipo_doc$i"] ?? 'Documento'
                                            ];
                                        }
                                    }
                                    ?>
                                    <?php if (!empty($adjuntos_trazabilidad)): ?>
                                        <div class="mt-2 pt-2 border-top" style="font-size: 0.82rem;">
                                            <span class="fw-bold text-secondary"><i class="bi bi-paperclip me-1"></i> Documentos Adjuntos:</span>
                                            <div class="d-flex flex-wrap gap-2 mt-1">
                                                <?php foreach ($adjuntos_trazabilidad as $adj): ?>
                                                    <?php 
                                                    $clean_url = htmlspecialchars((string)$adj['ruta'], ENT_QUOTES, 'UTF-8');
                                                    ?>
                                                    <a href="<?php echo $clean_url; ?>" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2 fw-semibold rounded-pill d-flex align-items-center gap-1" style="font-size: 0.75rem;">
                                                        <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                                                        <?php echo htmlspecialchars($adj['tipo'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                <button type="button" class="btn text-white fw-bold shadow-sm" style="background-color: #002F55;" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 10px;
}
.timeline-item {
    position: relative;
}
.timeline-panel {
    background: #fff;
    border: 1px solid #e3e6f0;
}
.timeline-badge {
    box-shadow: 0 0 0 5px rgba(255,255,255,1), 0 0 10px rgba(0,0,0,0.1);
}
</style>

