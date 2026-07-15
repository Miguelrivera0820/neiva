<?php
$rol_usuario            = $_SESSION['rol_usuario'] ?? '';
$nombre_usuario         = $_SESSION['nombre_usuario'] ?? '';
$apellido_usuario       = $_SESSION['apellido_usuario'] ?? '';
$cedula_usuario         = $_SESSION['cedula_usuario'] ?? '';
$cod_tramite            = $_GET['cod'] ?? '';
$info_cod_tramite       = $_GET['cod'] ?? '';
$sql                    = "SELECT * FROM tramite_radicacion WHERE cod_tramite = ?";
$stmt                   = $mysqli->prepare($sql);
$stmt->bind_param("s", $cod_tramite);
$stmt->execute();
$resultado              = $stmt->get_result();
$tramite                = $resultado->fetch_assoc();
$tipo_tramite_actual    = strtoupper(trim($tramite['tipo_tramite'] ?? 'ACTUALIZACION'));
if (!in_array($tipo_tramite_actual, ['ACTUALIZACION', 'CONSERVACION'], true)) {
    $tipo_tramite_actual = 'ACTUALIZACION';
}
$ruta_pdf               = "tramites_conservacion/2025/";
$sql2                   = "SELECT * FROM tramite_info_predio WHERE info_cod_tramite = ?";
$stmt2                  = $mysqli->prepare($sql2);
$stmt2->bind_param("s", $info_cod_tramite);
$stmt2->execute();
$resultado2             = $stmt2->get_result();
$info_predio            = $resultado2->fetch_assoc();
$cod                    = $_GET['cod'] ?? null;

// Consecutivo de Resoluciones
$sql_count_res = "SELECT COUNT(*) as total FROM resoluciones";
$res_count = 0;
if ($result_res = $mysqli->query($sql_count_res)) {
    $row_res = $result_res->fetch_assoc();
    $res_count = $row_res['total'];
}
$next_res = str_pad($res_count + 1, 3, "0", STR_PAD_LEFT);

// Consecutivo de Oficios
$sql_oficios_procede = "SELECT COUNT(*) as total FROM procede_tramite";
$sql_oficios_no_procede = "SELECT COUNT(*) as total FROM no_procede_completar";
$oficios_count = 0;
if ($result_procede = $mysqli->query($sql_oficios_procede)) {
    $row_procede = $result_procede->fetch_assoc();
    $oficios_count += $row_procede['total'];
}
if ($result_no_procede = $mysqli->query($sql_oficios_no_procede)) {
    $row_no_procede = $result_no_procede->fetch_assoc();
    $oficios_count += $row_no_procede['total'];
}
$next_oficio = str_pad($oficios_count + 1, 3, "0", STR_PAD_LEFT);
// FUNCION Y DEFINICIÓN PARA ESTABLECER DEPENDIENDO QUE ROL PUEDE ASIGNAR A QUE OTRO ROL
// EJEMPLO: ventanilla_catastral puede asignar a atencion_procedencia
//Esto va a evitar que alguien permita asignar un trámite a un rol que no le corresponde y se pierda informacion o trazabilidad del trámite
$roles_por_rol = [
    "ventanilla_catastral"  => ["procedencia_juridica"],
    "procedencia_juridica"  => ["coordinacion_tecnica", "revision_juridica"],
    "coordinacion_tecnica"  => ["control_calidad", "componente_economico", "revision_juridica", "director_catastro"],
    "revision_juridica"     => ["control_calidad"],
    "control_calidad"       => ["consolidacion", "coordinacion_tecnica"],
    "consolidacion"         => ["editor"],
    "editor"                => ["reconocedor"],
    "reconocedor"           => [""],
    "lider_reconocimiento"  => [""],
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
    "lider_reconocimiento" => ["editor"],
    "coordinacion_tecnica" => ["procedencia_juridica"],
    "administrador"        => [
        "ventanilla_catastral",
        "procedencia_juridica",
        "editor",
        "reconocedor",
        "coordinacion_tecnica"
    ]
];
$idUsuario          = $_SESSION['id_usuario'];
$mapa_roles_actual  = $tipo_tramite_actual === 'CONSERVACION' ? $roles_por_rol_conservacion : $roles_por_rol;
$roles_disponibles  = $mapa_roles_actual[$rol_usuario] ?? [];
$rol_usuario        = $_SESSION['rol_usuario'] ?? '';
$roles_disponibles  = $mapa_roles_actual[$rol_usuario] ?? [];

// Consulta para obtener el rol del usuario
$sql    = "SELECT rol_usuario FROM usuarios_cons WHERE id_usuario = ?";
$stmt   = $mysqli->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$stmt->get_result();

// Obtener asignacion_rol_usuario desde asignacion_tramite
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
$creacion_tram_cc_usuario       = null;
$creacion_tram_nombre_usuario   = null;
$creacion_tram_apellido_usuario = null;
$creacion_tram_rol_usuario      = null;
if ($cedula_usuario && $cod_tramite) {
    // Buscar en asignacion_tramite quién me asignó
    $sqlBuscar = "SELECT creacion_tram_cc_usuario,
                            creacion_tram_nombre_usuario,
                            creacion_tram_apellido_usuario,
                            creacion_tram_rol_usuario
                    FROM asignacion_tramite
                    WHERE asignacion_cod_tramite = ?
                    AND asignacion_cc_usuario = ?";
    $stmtBuscar = $mysqli->prepare($sqlBuscar);
    $stmtBuscar->bind_param("ss", $cod_tramite, $cedula_usuario);
    $stmtBuscar->execute();
    $resultado = $stmtBuscar->get_result();
    if ($row = $resultado->fetch_assoc()) {
        $creacion_tram_cc_usuario           = $row['creacion_tram_cc_usuario'];
        $creacion_tram_nombre_usuario       = $row['creacion_tram_nombre_usuario'];
        $creacion_tram_apellido_usuario     = $row['creacion_tram_apellido_usuario'];
        $creacion_tram_rol_usuario          = $row['creacion_tram_rol_usuario'];
    }
}
// Casos especiales para sobreescribir quién me pasó o a quién paso
$creacion_tram_cc_usuario           = null;
$creacion_tram_nombre_usuario       = null;
$creacion_tram_apellido_usuario     = null;
$creacion_tram_rol_usuario          = null;

$entrega_cc_usuario                 = null;
$entrega_nombre_usuario             = null;
$entrega_apellido_usuario           = null;
$entrega_rol_usuario                = null;

if ($cedula_usuario && $cod_tramite) {

    $sqlBuscar = "SELECT creacion_tram_cc_usuario,
                         creacion_tram_nombre_usuario,
                         creacion_tram_apellido_usuario,
                         creacion_tram_rol_usuario
                  FROM asignacion_tramite
                  WHERE asignacion_cod_tramite = ?
                    AND asignacion_cc_usuario = ?
                  LIMIT 1";

    $stmtBuscar = $mysqli->prepare($sqlBuscar);
    if ($stmtBuscar) {
        $stmtBuscar->bind_param("ss", $cod_tramite, $cedula_usuario);
        $stmtBuscar->execute();
        $rs = $stmtBuscar->get_result();

        if ($row = $rs->fetch_assoc()) {
            $creacion_tram_cc_usuario       = $row['creacion_tram_cc_usuario'];
            $creacion_tram_nombre_usuario   = $row['creacion_tram_nombre_usuario'];
            $creacion_tram_apellido_usuario = $row['creacion_tram_apellido_usuario'];
            $creacion_tram_rol_usuario      = $row['creacion_tram_rol_usuario'];
        } else {
            $sqlFallback = "SELECT quien_entrego_cc,
                                   quien_entrego_nombre,
                                   quien_entrego_apellido,
                                   quien_entrego_rol
                            FROM entrega_asignacion
                            WHERE entrega_cod_tramite = ?
                              AND entrega_cc_usuario = ?
                            ORDER BY id_entrega_asignacion DESC
                            LIMIT 1";

            $stmtFallback = $mysqli->prepare($sqlFallback);
            if ($stmtFallback) {
                $stmtFallback->bind_param("ss", $cod_tramite, $cedula_usuario);
                $stmtFallback->execute();
                $rsFb = $stmtFallback->get_result();

                if ($rowFb = $rsFb->fetch_assoc()) {
                    $creacion_tram_cc_usuario       = $rowFb['quien_entrego_cc'];
                    $creacion_tram_nombre_usuario   = $rowFb['quien_entrego_nombre'];
                    $creacion_tram_apellido_usuario = $rowFb['quien_entrego_apellido'];
                    $creacion_tram_rol_usuario      = $rowFb['quien_entrego_rol'];
                }
                $stmtFallback->close();
            }
        }

        $stmtBuscar->close();
    }
}

if ($tipo_tramite_actual === 'CONSERVACION' && $cedula_usuario && $cod_tramite) {
    $sqlUltimaEntrega = "SELECT quien_entrego_cc,
                                quien_entrego_nombre,
                                quien_entrego_apellido,
                                quien_entrego_rol
                         FROM entrega_asignacion
                         WHERE entrega_cod_tramite = ?
                           AND entrega_cc_usuario = ?
                         ORDER BY id_entrega_asignacion DESC
                         LIMIT 1";
    $stmtUltimaEntrega = $mysqli->prepare($sqlUltimaEntrega);
    if ($stmtUltimaEntrega) {
        $stmtUltimaEntrega->bind_param("ss", $cod_tramite, $cedula_usuario);
        $stmtUltimaEntrega->execute();
        $rsUltimaEntrega = $stmtUltimaEntrega->get_result();
        if ($rowUltimaEntrega = $rsUltimaEntrega->fetch_assoc()) {
            $creacion_tram_cc_usuario       = $rowUltimaEntrega['quien_entrego_cc'];
            $creacion_tram_nombre_usuario   = $rowUltimaEntrega['quien_entrego_nombre'];
            $creacion_tram_apellido_usuario = $rowUltimaEntrega['quien_entrego_apellido'];
            $creacion_tram_rol_usuario      = $rowUltimaEntrega['quien_entrego_rol'];
        }
        $stmtUltimaEntrega->close();
    }
}

if ($cedula_usuario && $cod_tramite) {

    $sqlDest = "SELECT asignacion_cc_usuario,
                       asignacion_nombre_usuario,
                       asignacion_apellido_usuario,
                       asignacion_rol_usuario
                FROM asignacion_tramite
                WHERE asignacion_cod_tramite = ?
                  AND creacion_tram_cc_usuario = ?
                LIMIT 1";

    $stmtDest = $mysqli->prepare($sqlDest);
    if ($stmtDest) {
        $stmtDest->bind_param("ss", $cod_tramite, $cedula_usuario);
        $stmtDest->execute();
        $rsDest = $stmtDest->get_result();

        if ($rowD = $rsDest->fetch_assoc()) {
            $entrega_cc_usuario       = $rowD['asignacion_cc_usuario'];
            $entrega_nombre_usuario   = $rowD['asignacion_nombre_usuario'];
            $entrega_apellido_usuario = $rowD['asignacion_apellido_usuario'];
            $entrega_rol_usuario      = $rowD['asignacion_rol_usuario'];
        }

        $stmtDest->close();
    }
}



$sqlBuscar = "SELECT observacion_a_usuario_tramite
              FROM entrega_asignacion
              WHERE entrega_cod_tramite = ?
              ORDER BY id_entrega_asignacion DESC
              LIMIT 1";
$stmtBuscar = $mysqli->prepare($sqlBuscar);
if (!$stmtBuscar) {
    die("Error en la consulta: " . $mysqli->error);
}
// Usa $cod_tramite directamente
$stmtBuscar->bind_param("s", $cod_tramite);
$stmtBuscar->execute();
$resultObs = $stmtBuscar->get_result();
$observacion_a_usuario_tramite = '';
if ($row = $resultObs->fetch_assoc()) {
    $observacion_a_usuario_tramite = $row['observacion_a_usuario_tramite'];
}
$stmtBuscar->close();
// Trae los documentos que subió QUIEN TE PASÓ el trámite
// Verifica si tienes el rol del que pasó el trámite
// Trae los documentos que subió QUIEN TE PASÓ el trámite
$cod_tramite = $tramite['cod_tramite'];

function rutaWebDocumentoRevision($ruta)
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
            return '../' . implode('/', array_map('rawurlencode', explode('/', $ruta)));
        }
    }

    return htmlspecialchars($ruta, ENT_QUOTES, 'UTF-8');
}

function normalizarDocumentosAsignacion($row)
{
    if (!$row) {
        return null;
    }
    $docs = [];
    $tieneDocumentos = false;
    for ($i = 1; $i <= 5; $i++) {
        $docs["doc$i"] = rutaWebDocumentoRevision($row["nombre_doc$i"] ?? null);
        $docs["tipo_doc$i"] = $row["tipo_doc$i"] ?? null;
        if (!empty($docs["doc$i"])) {
            $tieneDocumentos = true;
        }
    }
    return $tieneDocumentos ? $docs : null;
}

function tieneDocumentosRevision($row)
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

function obtenerDocumentosRevision($mysqli, $cod_tramite, $cedula_usuario)
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
        if (tieneDocumentosRevision($docsRevision)) {
            for ($i = 1; $i <= 5; $i++) {
                $docsRevision["doc$i"] = rutaWebDocumentoRevision($docsRevision["doc$i"] ?? null);
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
        $docsAsignacion = normalizarDocumentosAsignacion($resDocsAsignacion->fetch_assoc());
        $stmtDocsAsignacion->close();
        if ($docsAsignacion) {
            return $docsAsignacion;
        }
    }

    return null;
}

$docs = obtenerDocumentosRevision($mysqli, $cod_tramite, $cedula_usuario);
// Consulta específica para rol director_catastro
// Consulta específica para rol director_catastro
$documento_resolucion = null;
$ruta_resolucion = null;
$id_resolucion = null;
$has_director_resolucion = false;

if ($rol_usuario === 'director_catastro') {
    // Obtener la última resolución del trámite, sin importar quién la generó
    $sql_res = "SELECT r.id_resoluciones, r.documento, r.resolucion_director, r.ruta_archivo
                FROM resoluciones r
                INNER JOIN entrega_asignacion ea ON ea.id_entrega_asignacion = r.id_entrega_asignacion
                WHERE ea.entrega_cod_tramite = ?
                ORDER BY r.id_resoluciones DESC
                LIMIT 1";
    $stmt_res = $mysqli->prepare($sql_res);
    if ($stmt_res) {
        $stmt_res->bind_param("s", $cod_tramite);
        $stmt_res->execute();
        $result_res = $stmt_res->get_result();
        if ($row_res = $result_res->fetch_assoc()) {
            $documento_resolucion = $row_res['documento'];
            $ruta_resolucion = rutaWebDocumentoRevision($row_res['ruta_archivo'] ?? '');
            $id_resolucion = $row_res['id_resoluciones'];
            $has_director_resolucion = $row_res['resolucion_director'];
        }
        $stmt_res->close();
    }
}





$dest_cc_usuario       = $creacion_tram_cc_usuario;
$dest_nombre_usuario   = $creacion_tram_nombre_usuario;
$dest_apellido_usuario = $creacion_tram_apellido_usuario;
$dest_rol_usuario      = $creacion_tram_rol_usuario;

function buscarUsuarioPorRolRevision($mysqli, $rol)
{
    $sqlUsuario = "SELECT cedula_usuario, nombre_usuario, apellido_usuario, rol_usuario
                   FROM usuarios_cons
                   WHERE rol_usuario = ?
                   ORDER BY id_usuario DESC
                   LIMIT 1";
    $stmtUsuario = $mysqli->prepare($sqlUsuario);
    if (!$stmtUsuario) {
        return null;
    }
    $stmtUsuario->bind_param("s", $rol);
    $stmtUsuario->execute();
    $rsUsuario = $stmtUsuario->get_result();
    $usuario = $rsUsuario->fetch_assoc() ?: null;
    $stmtUsuario->close();
    return $usuario;
}

function buscarVentanillaOriginalRevision($mysqli, $cod_tramite)
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

if ($tipo_tramite_actual !== 'CONSERVACION' && $rol_usuario === 'procedencia_juridica') {
    $sqlDir = "SELECT * 
                FROM usuarios_cons 
                WHERE rol_usuario = 'director_catastro'
                ORDER BY id_usuario DESC
                LIMIT 1";

    $stmtDir = $mysqli->prepare($sqlDir);
    if ($stmtDir) {
        $stmtDir->execute();
        $resDir = $stmtDir->get_result();

        if ($rowDir = $resDir->fetch_assoc()) {
            $dest_cc_usuario       = $rowDir['cedula_usuario'] ?? $rowDir['cc_usuario'] ?? $rowDir['documento'] ?? $dest_cc_usuario;
            $dest_nombre_usuario   = $rowDir['nombre_usuario'] ?? $rowDir['nombres_usuario'] ?? $rowDir['nombre'] ?? $dest_nombre_usuario;
            $dest_apellido_usuario = $rowDir['apellido_usuario'] ?? $rowDir['apellidos_usuario'] ?? $rowDir['apellido'] ?? $dest_apellido_usuario;
            $dest_rol_usuario      = $rowDir['rol_usuario'] ?? 'director_catastro';
        } else {
            $dest_rol_usuario = 'director_catastro';
        }

        $stmtDir->close();
    } else {
        $dest_rol_usuario = 'director_catastro';
    }
}

if ($tipo_tramite_actual !== 'CONSERVACION' && $rol_usuario === 'director_catastro' && $cedula_usuario && $cod_tramite) {
    // 1) ORIGINADOR: último registro donde YO (director) fui el destinatario
    $sqlOriDir = "SELECT quien_entrego_cc,
                         quien_entrego_nombre,
                         quien_entrego_apellido,
                         quien_entrego_rol
                  FROM entrega_asignacion
                  WHERE entrega_cod_tramite = ?
                    AND entrega_cc_usuario = ?
                  ORDER BY id_entrega_asignacion DESC
                  LIMIT 1";
    $stmtOriDir = $mysqli->prepare($sqlOriDir);
    if ($stmtOriDir) {
        $stmtOriDir->bind_param("ss", $cod_tramite, $cedula_usuario);
        $stmtOriDir->execute();
        $rsOriDir = $stmtOriDir->get_result();

        if ($rowOri = $rsOriDir->fetch_assoc()) {
            // Estos son los que se imprimen en "DATOS DEL USUARIO ORIGINADOR"
            $entrega_cc_usuario       = $rowOri['quien_entrego_cc'];
            $entrega_nombre_usuario   = $rowOri['quien_entrego_nombre'];
            $entrega_apellido_usuario = $rowOri['quien_entrego_apellido'];
            $entrega_rol_usuario      = $rowOri['quien_entrego_rol'];

            // Y además los guardamos para trazabilidad (si usas hidden inputs)
            $creacion_tram_cc_usuario           = $entrega_cc_usuario;
            $creacion_tram_nombre_usuario       = $entrega_nombre_usuario;
            $creacion_tram_apellido_usuario     = $entrega_apellido_usuario;
            $creacion_tram_rol_usuario          = $entrega_rol_usuario;
        }
        $stmtOriDir->close();
    }

    // 2) DESTINATARIO: ventanilla asociada al trámite (última asignación a ventanilla)
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
    if ($stmtVent) {
        $stmtVent->bind_param("s", $cod_tramite);
        $stmtVent->execute();
        $rsVent = $stmtVent->get_result();

        if ($rowVent = $rsVent->fetch_assoc()) {
            $dest_cc_usuario       = $rowVent['creacion_tram_cc_usuario'];
            $dest_nombre_usuario   = $rowVent['creacion_tram_nombre_usuario'];
            $dest_apellido_usuario = $rowVent['creacion_tram_apellido_usuario'];
            $dest_rol_usuario      = 'ventanilla_catastral';
        } else {
            // Fallback si no hay registro en asignacion_tramite
            $sqlVentFb = "SELECT cedula_usuario, nombre_usuario, apellido_usuario, rol_usuario
                      FROM usuarios_cons
                      WHERE rol_usuario = 'ventanilla_catastral'
                      ORDER BY id_usuario DESC
                      LIMIT 1";
            $stmtVentFb = $mysqli->prepare($sqlVentFb);
            if ($stmtVentFb) {
                $stmtVentFb->execute();
                $rsVentFb = $stmtVentFb->get_result();
                if ($rowVentFb = $rsVentFb->fetch_assoc()) {
                    $dest_cc_usuario       = $rowVentFb['cedula_usuario'];
                    $dest_nombre_usuario   = $rowVentFb['nombre_usuario'];
                    $dest_apellido_usuario = $rowVentFb['apellido_usuario'];
                    $dest_rol_usuario      = $rowVentFb['rol_usuario'];
                } else {
                    $dest_rol_usuario = 'ventanilla_catastral';
                }
                $stmtVentFb->close();
            }
        }
        $stmtVent->close();
    }

    // coherencia con estados/historial
    $asignacion_rol_usuario = 'ventanilla_catastral';
}

if ($tipo_tramite_actual === 'CONSERVACION') {
    $destino_conservacion = [
        'reconocedor'          => 'editor',
        'lider_reconocimiento' => 'editor',
        'editor'               => 'coordinacion_tecnica',
        'coordinacion_tecnica' => 'procedencia_juridica',
        'procedencia_juridica' => 'ventanilla_catastral'
    ];
    $rol_destino_conservacion = $destino_conservacion[$rol_usuario] ?? ($roles_disponibles[0] ?? '');

    if (in_array($rol_usuario, ['reconocedor', 'lider_reconocimiento'], true) && $creacion_tram_cc_usuario) {
        $dest_cc_usuario       = $creacion_tram_cc_usuario;
        $dest_nombre_usuario   = $creacion_tram_nombre_usuario;
        $dest_apellido_usuario = $creacion_tram_apellido_usuario;
        $dest_rol_usuario      = 'editor';
    } elseif ($rol_destino_conservacion === 'ventanilla_catastral') {
        $ventanillaOriginal = buscarVentanillaOriginalRevision($mysqli, $cod_tramite);
        if ($ventanillaOriginal) {
            $dest_cc_usuario       = $ventanillaOriginal['creacion_tram_cc_usuario'];
            $dest_nombre_usuario   = $ventanillaOriginal['creacion_tram_nombre_usuario'];
            $dest_apellido_usuario = $ventanillaOriginal['creacion_tram_apellido_usuario'];
            $dest_rol_usuario      = 'ventanilla_catastral';
        }
    } elseif ($rol_destino_conservacion) {
        $usuarioDestino = buscarUsuarioPorRolRevision($mysqli, $rol_destino_conservacion);
        if ($usuarioDestino) {
            $dest_cc_usuario       = $usuarioDestino['cedula_usuario'];
            $dest_nombre_usuario   = $usuarioDestino['nombre_usuario'];
            $dest_apellido_usuario = $usuarioDestino['apellido_usuario'];
            $dest_rol_usuario      = $usuarioDestino['rol_usuario'];
        } else {
            $dest_rol_usuario = $rol_destino_conservacion;
        }
    }

    $asignacion_rol_usuario = $dest_rol_usuario;
}

if ($cedula_usuario && $cod_tramite) {
    $sqlReasignadoActual = "SELECT id_entrega_asignacion, historial_estado_tramite
                            FROM entrega_asignacion
                            WHERE entrega_cod_tramite = ?
                              AND entrega_cc_usuario = ?
                            ORDER BY id_entrega_asignacion DESC
                            LIMIT 1";
    $stmtReasignadoActual = $mysqli->prepare($sqlReasignadoActual);
    if ($stmtReasignadoActual) {
        $stmtReasignadoActual->bind_param("ss", $cod_tramite, $cedula_usuario);
        $stmtReasignadoActual->execute();
        $rsReasignadoActual = $stmtReasignadoActual->get_result();
        if ($rowReasignadoActual = $rsReasignadoActual->fetch_assoc()) {
            $idEntregaActual = (int)($rowReasignadoActual['id_entrega_asignacion'] ?? 0);
            $estadoEntregaActual = strtoupper(trim($rowReasignadoActual['historial_estado_tramite'] ?? ''));

            if ($idEntregaActual > 0 && $estadoEntregaActual === 'REASIGNADO') {
                $sqlOrigenReal = "SELECT creacion_tram_cc_usuario,
                                         creacion_tram_nombre_usuario,
                                         creacion_tram_apellido_usuario,
                                         creacion_tram_rol_usuario,
                                         quien_entrego_cc,
                                         quien_entrego_nombre,
                                         quien_entrego_apellido,
                                         quien_entrego_rol
                                  FROM entrega_asignacion
                                  WHERE entrega_cod_tramite = ?
                                    AND id_entrega_asignacion < ?
                                    AND UPPER(COALESCE(historial_estado_tramite, '')) <> 'REASIGNADO'
                                  ORDER BY id_entrega_asignacion DESC
                                  LIMIT 1";
                $stmtOrigenReal = $mysqli->prepare($sqlOrigenReal);
                if ($stmtOrigenReal) {
                    $stmtOrigenReal->bind_param("si", $cod_tramite, $idEntregaActual);
                    $stmtOrigenReal->execute();
                    $rsOrigenReal = $stmtOrigenReal->get_result();
                    if ($rowOrigenReal = $rsOrigenReal->fetch_assoc()) {
                        $origenCc = $rowOrigenReal['quien_entrego_cc'] ?: $rowOrigenReal['creacion_tram_cc_usuario'];
                        $origenNombre = $rowOrigenReal['quien_entrego_nombre'] ?: $rowOrigenReal['creacion_tram_nombre_usuario'];
                        $origenApellido = $rowOrigenReal['quien_entrego_apellido'] ?: $rowOrigenReal['creacion_tram_apellido_usuario'];
                        $origenRol = $rowOrigenReal['quien_entrego_rol'] ?: $rowOrigenReal['creacion_tram_rol_usuario'];

                        if ($origenCc) {
                            $creacion_tram_cc_usuario = $origenCc;
                            $creacion_tram_nombre_usuario = $origenNombre;
                            $creacion_tram_apellido_usuario = $origenApellido;
                            $creacion_tram_rol_usuario = $origenRol;
                            $entrega_cc_usuario = $origenCc;
                            $entrega_nombre_usuario = $origenNombre;
                            $entrega_apellido_usuario = $origenApellido;
                            $entrega_rol_usuario = $origenRol;
                        }
                    }
                    $stmtOrigenReal->close();
                }
            }
        }
        $stmtReasignadoActual->close();
    }
}


?>

<script>
    console.log("Rol usuario:", <?php echo json_encode($rol_usuario); ?>);
    console.log("Documento resolución:", <?php echo json_encode($documento_resolucion); ?>);
    console.log("Ruta resolución:", <?php echo json_encode($ruta_resolucion); ?>);
    console.log("ID resolución:", <?php echo json_encode($id_resolucion); ?>);
    console.log("Tiene resolución director:", <?php echo json_encode($has_director_resolucion); ?>);
</script>


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

    /* Estilos adicionales para el editor Word-like */
    .pj-document-editor {
        background: white;
        border: 2px solid #000;
        border-radius: 0;
        padding: 30px 40px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        font-family: 'Times New Roman', Times, serif;
        max-width: 100%;
        font-size: 11pt;
        line-height: 1.5;
        min-height: 500px;
        overflow-y: auto;
        outline: none;
    }

    .pj-document-editor:focus {
        outline: 2px solid #004177;
    }

    .pj-document-editor [contenteditable="true"] {
        outline: none;
        cursor: text;
    }

    button[data-bs-target="#chatbotModal"] {
        width: 60px !important;
        height: 60px !important;
        min-width: 60px !important;
        max-width: 60px !important;
        padding: 0 !important;
        flex-shrink: 0 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    button[data-bs-target="#chatbotModal"]:hover {
        transform: scale(1.1);
        transition: transform 0.2s ease;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<div class="d-sm-flex align-items-center justify-content-between mb-4 px-4">
    <h1 class="h3 mb-0 fw-bold my-4">ASIGNACIÓN DE TRÁMITES</h1>

    <a href="index.php?page=tramites/base_catastral/predio_mutacion&cod=<?php echo urlencode($tramite['cod_tramite']); ?>"
        class="btn btn-md shadow-lg fw-bold btn-ver"
        style="border-left:2px solid #0b7a53;border-right:2px solid #0b7a53;">
        <i class="bi bi-table me-2 fw-bold"></i> Base catastral
    </a>

    <a href="index.php?page=tramites/acciones/ver_tramite_rad&cod=<?php echo urlencode($tramite['cod_tramite']); ?>"
        class="btn  btn-md shadow-lg animated-button fw-bold btn-ver"
        style="border-left:2px solid #022F55;border-right:2px solid #022F55;">
        <i class="bi bi-eye me-2 fw-bold "></i> Ver Radicación
    </a>
</div>

<!-- Condicional para hacer que las cards solamente se muestren para el reconocedor predial -->
<?php if ($rol_usuario === 'reconocedor'): ?>
    <!-- Botones modales -->
    <div class="container my-2">
        <div class=" row d-flex align-items-stretch justify-content-center">

            <!-- Ventanilla Catastral-->
            <?php if ($rol_usuario !== 'ventanilla_catastral' and $rol_usuario !== 'director_catastro'): ?>
                <div class="col-6 col-md-4 col-lg-2  align-items-center justify-content-center d-flex p-2">
                    <button
                        type="button"
                        class="btn h-100 w-100 shadow btn-rol-1"
                        onclick="verHistorialAsignacion('ventanilla_catastral', '<?php echo $cod_tramite; ?>')"
                        data-bs-toggle="modal"
                        data-bs-target="#modalHistorial">
                        <b>Ventanilla Catastral</b>
                    </button>
                </div>
            <?php endif; ?>


            <!-- Procedencia juridica-->
            <?php if ($rol_usuario !== 'director_catastro'): ?>
                <div class="col-6 col-md-4 col-lg-2  align-items-center justify-content-center d-flex p-2">
                    <button type="button"
                        class="btn h-100 w-100 shadow btn-rol-2"
                        onclick="verHistorialAsignacion('procedencia_juridica', '<?php echo $cod_tramite; ?>')"
                        data-bs-toggle="modal" data-bs-target="#modalHistorial">
                        <b>Procedencia Jurídica</b>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Atencion procedencia - asesor juridico-->

            <?php if ($rol_usuario !== 'ventanilla_catastral' and $rol_usuario !== 'consolidacion' and $rol_usuario !== 'editor' and $rol_usuario !== 'reconocedor' and $rol_usuario !== 'atencion_procedencia' and $rol_usuario !== 'director_catastro'): ?>
                <div class="col-6 col-md-4 col-lg-2  align-items-center justify-content-center d-flex p-2">
                    <button type="button"
                        class="btn h-100 w-100 shadow btn-rol-3"
                        onclick="verHistorialAsignacion('atencion_procedencia', '<?php echo $cod_tramite; ?>')"
                        data-bs-toggle="modal" data-bs-target="#modalHistorial">
                        <b>Atención Procedencia</b>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Lider juridico-->
            <?php if ($rol_usuario !== 'procedencia_juridica' and $rol_usuario !== 'coordinacion_tecnica' and $rol_usuario !== 'consolidacion' and $rol_usuario !== 'editor' and $rol_usuario !== 'reconocedor' and $rol_usuario !== 'atencion_procedencia'): ?>
                <div class="col-6 col-md-4 col-lg-2  align-items-center justify-content-center d-flex p-2">
                    <button type="button"
                        class="btn h-100 w-100 shadow btn-rol-4 "
                        onclick="verHistorialAsignacion('revision_juridica', '<?php echo $cod_tramite; ?>')"
                        data-bs-toggle="modal" data-bs-target="#modalHistorial">
                        <b>Revision Jurídica</b>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Cordinacion tecnica-->
            <?php if ($rol_usuario !== 'procedencia_juridica' and $rol_usuario !== 'coordinacion_tecnica' and $rol_usuario !== 'atencion_procedencia'): ?>
                <div class="col-6 col-md-4 col-lg-2  align-items-center justify-content-center d-flex p-2">
                    <button type="button"
                        class="btn  h-100 w-100 shadow btn-rol-5"
                        onclick="verHistorialAsignacion('coordinacion_tecnica', '<?php echo $cod_tramite; ?>')"
                        data-bs-toggle="modal" data-bs-target="#modalHistorial">
                        <b>Coordinación Técnica</b>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Control calidad-->
            <?php if ($rol_usuario !== 'procedencia_juridica' and $rol_usuario !== 'coordinacion_tecnica' and $rol_usuario !== 'revision_juridica' and $rol_usuario !== 'componente_economico' and $rol_usuario !== 'avaluos' and $rol_usuario !== 'consolidacion' and $rol_usuario !== 'atencion_procedencia'): ?>
                <div class="col-6 col-md-4 col-lg-2  align-items-center justify-content-center d-flex p-2">
                    <button type="button"
                        class="btn  h-100 w-100 shadow btn-rol-6"
                        onclick="verHistorialAsignacion('control_calidad', '<?php echo $cod_tramite; ?>')"
                        data-bs-toggle="modal" data-bs-target="#modalHistorial">
                        <b>Control Calidad</b>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Consolidacion-->
            <?php if (
                $rol_usuario !== 'procedencia_juridica' and $rol_usuario !== 'coordinacion_tecnica' and $rol_usuario !== 'revision_juridica'
                and $rol_usuario !== 'componente_economico' and $rol_usuario !== 'avaluos' and $rol_usuario !== 'control_calidad'
                and $rol_usuario !== 'consolidacion' and $rol_usuario !== 'atencion_procedencia' and $rol_usuario !== 'director_catastro'
            ): ?>
                <div class="col-6 col-md-4 col-lg-2  align-items-center justify-content-center d-flex p-2">
                    <button type="button"
                        class="btn  h-100 w-100 shadow btn-rol-7"
                        onclick="verHistorialAsignacion('consolidacion', '<?php echo $cod_tramite; ?>')"
                        data-bs-toggle="modal" data-bs-target="#modalHistorial">
                        <b>Consolidación</b>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Comoponente economico-->
            <?php if (
                $rol_usuario !== 'procedencia_juridica' and $rol_usuario !== 'coordinacion_tecnica'
                and $rol_usuario !== 'revision_juridica' and $rol_usuario !== 'componente_economico' and $rol_usuario !== 'avaluos' and $rol_usuario !== 'control_calidad'
                and $rol_usuario !== 'consolidacion' and $rol_usuario !== 'editor' and $rol_usuario !== 'reconocedor' and $rol_usuario !== 'atencion_procedencia'
            ): ?>
                <div class="col-6 col-md-4 col-lg-2  align-items-center justify-content-center d-flex p-2 ">
                    <button type="button"
                        class="btn  h-100 w-100 shadow btn-rol-8"
                        onclick="verHistorialAsignacion('componente_economico', '<?php echo $cod_tramite; ?>')"
                        data-bs-toggle="modal" data-bs-target="#modalHistorial">
                        <b>Lider Economico</b>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Editor geografico-->
            <?php if (
                $rol_usuario !== 'procedencia_juridica' and $rol_usuario !== 'coordinacion_tecnica'
                and $rol_usuario !== 'revision_juridica' and $rol_usuario !== 'componente_economico' and $rol_usuario !== 'avaluos' and $rol_usuario !== 'control_calidad'
                and $rol_usuario !== 'consolidacion' and $rol_usuario !== 'editor' and $rol_usuario !== 'atencion_procedencia' and $rol_usuario !== 'director_catastro'
            ): ?>
                <div class="col-6 col-md-4 col-lg-2  align-items-center justify-content-center d-flex p-2 ">
                    <button type="button"
                        class="btn h-100 w-100 shadow btn-rol-9"
                        onclick="verHistorialAsignacion('editor', '<?php echo $cod_tramite; ?>')" data-bs-toggle="modal"
                        data-bs-target="#modalHistorial">
                        <b>Editor</b>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Avaluos-->
            <?php if (
                $rol_usuario !== 'procedencia_juridica' and $rol_usuario !== 'coordinacion_tecnica' and $rol_usuario !== 'revision_juridica'
                and $rol_usuario !== 'componente_economico' and $rol_usuario !== 'avaluos' and $rol_usuario !== 'control_calidad'
                and $rol_usuario !== 'consolidacion' and $rol_usuario !== 'editor' and $rol_usuario !== 'reconocedor' and $rol_usuario !== 'atencion_procedencia'
                and $rol_usuario !== 'director_catastro'
            ): ?>
                <div class="col-6 col-md-4 col-lg-2  align-items-center justify-content-center d-flex p-2">
                    <button type="button"
                        class="btn h-100 w-100 shadow btn-rol-10"
                        onclick="verHistorialAsignacion('avaluos', '<?php echo $cod_tramite; ?>')" data-bs-toggle="modal"
                        data-bs-target="#modalHistorial">
                        <b>Avaluo</b>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Reconocedor predial-->
            <?php if (
                $rol_usuario !== 'procedencia_juridica' and $rol_usuario !== 'coordinacion_tecnica' and $rol_usuario !== 'revision_juridica'
                and $rol_usuario !== 'componente_economico' and $rol_usuario !== 'avaluos' and $rol_usuario !== 'control_calidad'
                and $rol_usuario !== 'consolidacion' and $rol_usuario !== 'editor' and $rol_usuario !== 'reconocedor' and $rol_usuario !== 'atencion_procedencia'
                and $rol_usuario !== 'director_catastro'
            ): ?>
                <div class="col-6 col-md-4 col-lg-2  align-items-center justify-content-center d-flex p-2">
                    <button type="button"
                        class="btn h-100 w-100 shadow btn-rol-11"
                        onclick="verHistorialAsignacion('reconocedor', '<?php echo $cod_tramite; ?>')"
                        data-bs-toggle="modal" data-bs-target="#modalHistorial">
                        <b>Reconocimiento</b>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Director Catastro-->
            <?php if ($rol_usuario !== 'director_catastro'): ?>
                <div class="col-6 col-md-4 col-lg-2  align-items-center justify-content-center d-flex p-2">
                    <button
                        type="button"
                        class="btn h-100 w-100 shadow btn-rol-12"
                        onclick="verHistorialAsignacion('reconocedor', '<?php echo $cod_tramite; ?>')"
                        data-bs-toggle="modal"
                        data-bs-target="#modalHistorial">
                        <b>Director Catastro</b>
                    </button>
                </div>
            <?php endif; ?>

        </div>
    </div>
<?php endif; ?>

<!-- PRUEBA MODAL DE PROCEDENCIA - INTENTO 1
                        MOSTRAR INFORMACION DE CARGA POR USUARIOS ANTERIORES -->
<div class="modal fade" id="modalHistorial" tabindex="-1" aria-labelledby="modalHistorialLabel" aria-hidden="true"
    style="z-index: 2000;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <!-- <h5 class="modal-title" id="modalHistorialLabel">Historial del trámite</h5> -->
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
    <!-- col-xl-12 col-lg-7 -> ESTO LO QUE HACE ES DEFINIR EL ESPACIO DE LA TARJETA -->
    <div class="col-12">
        <div class="card shadow mb-4 p-3  text-center">
            <div style="background-color: #022F55;" class="rounded-3">
                <h5 class="text-white text-center py-2 m-0">DATOS DEL USUARIO ORIGINADOR</h5>
            </div>
            <div class="card-body">
                <form id="miFormulario" action="index.php?page=seguimiento/acciones/procesar_entrega" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <!-- CAMBIAR EL MODO DE ID, DESACTIVAR PORQUE DEBE SER AUTOMATICO -->
                        <div class="form-row">
                            <div class="col-md-4 p-1 px-2 my-2">
                                <label for="cod_tramite" class="form-label fw-bold" style="font-size:0.9em;">ID de radicación</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-file-earmark-binary"></i></span>
                                    <input type="text" class="form-control" id="cod_tramite_form"
                                        name="entrega_cod_tramite"
                                        value="<?php echo htmlspecialchars($tramite['cod_tramite']); ?>" readonly>
                                </div>
                            </div>
                            <!--<div class="col-md-6">
                                <label for="fecha_rad"><b>Hora Radicación del Trámite</b></label>
                                <input class="form-control py-4" id="fecha_rad" name="historial_fecha_tramite" type="text"
                                    value="<?php echo htmlspecialchars($tramite['fecha_rad']); ?>" readonly>
                            </div>-->
                            <div class="col-md-4 p-1 px-2 my-2">
                                <label for="fecha_rad" class="form-label fw-bold" style="font-size:0.9em;">Hora radicación del trámite</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                                    <input type="text" class="form-control" id="fecha_rad_form"
                                        name="historial_fecha_tramite"
                                        value="<?php echo htmlspecialchars($tramite['fecha_rad']); ?>" readonly>
                                </div>
                            </div>


                            <!--<div class="col-md-6">
                                <label for="nombre_usuario"><b>Cedula del Usuario</b></label>
                                <input class="form-control" id="creacion_tram_cc_usuario" name="creacion_tram_cc_usuario" type="text" value="<?php echo $creacion_tram_cc_usuario; ?>" readonly>
                            </div>-->

                            <!--<div class="col-md-6">
                                <label for="nombre_usuario"><b>Nombres del Usuario</b></label>
                                <input class="form-control" id="creacion_tram_nombre_usuario" name="creacion_tram_nombre_usuario" type="text" value="<?php echo $creacion_tram_nombre_usuario; ?>" readonly>
                            </div>-->

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label for="entrega_cc_usuario" class="form-label fw-bold" style="font-size:0.9em">Cédula del usuario</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                                    <input type="text" class="form-control" style="font-size: 0.9em;" id="entrega_cc_usuario"
                                        value="<?php echo $entrega_cc_usuario; ?>" readonly>
                                </div>
                            </div>

                            <!--<div class="col-md-6">
                                <label for="apellido_usuario"><b>Apellidos del Usuario</b></label>
                                <input class="form-control" id="creacion_tram_apellido_usuario" name="creacion_tram_apellido_usuario" type="text" value="<?php echo $creacion_tram_apellido_usuario; ?>" readonly>
                            </div>-->

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label for="entrega_nombre_usuario" class="form-label fw-bold" style="font-size:0.9em">Nombres del usuario</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                    <input type="text" class="form-control" style="font-size: 0.9em;" id="entrega_nombre_usuario"
                                        value="<?php echo $entrega_nombre_usuario; ?>" readonly>
                                </div>
                            </div>

                            <!--<div class="col-md-6">
                                <label for="apellido_usuario"><b>Apellidos del Usuario</b></label>
                                <input class="form-control" id="creacion_tram_apellido_usuario" name="creacion_tram_apellido_usuario" type="text" value="<?php echo $creacion_tram_apellido_usuario; ?>" readonly>
                            </div>-->

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label for="entrega_apellido_usuario" class="form-label fw-bold" style="font-size:0.9em">Apellidos del usuario</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi-people-fill"></i></span>
                                    <input type="text" class="form-control" style="font-size: 0.9em;" id="entrega_apellido_usuario"
                                        value="<?php echo $entrega_apellido_usuario; ?>" readonly>
                                </div>
                            </div>

                            <!--<div class="col-md-6">
                                <label for="nombre_usuario"><b>Cargo</b></label>
                                <input class="form-control" id="creacion_tram_rol_usuario" name="creacion_tram_rol_usuario" type="text" value="<?php echo $creacion_tram_rol_usuario; ?>" readonly>
                            </div>-->

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label for="entrega_rol_usuario" class="form-label fw-bold" style="font-size:0.9em">Cargo</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-person-gear"></i></span>
                                    <input type="text" class="form-control" style="font-size: 0.9em;" id="entrega_rol_usuario"
                                        value="<?php echo $entrega_rol_usuario; ?>" readonly>
                                </div>
                            </div>

                            <div class="col-md-12 p-1 px-2 m-2 mx-auto">
                                <div class="row justify-content-center">

                                    <?php if ($docs): ?>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if (!empty($docs["doc$i"])): ?>
                                                <div class="col-6 p-2">
                                                    <div class="card card-documentos shadow h-100 p-3 border d-flex flex-column text-center">
                                                        <!-- <label><b>Documentos Adjuntos</b></label> -->
                                                        <!-- <p class="mb-1">
                                                                <b>Documento <?= $i ?>:</b> <?= htmlspecialchars($docs["tipo_doc$i"] ?? "Sin descripción") ?>
                                                            </p> -->
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

                                    <?php if (in_array($rol_usuario, ['director_catastro', 'ventanilla_catastral']) && $ruta_resolucion): ?>

                                        <!-- <div class="card p-2 my-2 mt-3">
                                                <label><b>Resolución Asociada</b></label>
                                                <p class="mb-1">
                                                    <b>Documento:</b> <?= htmlspecialchars($documento_resolucion) ?>
                                                </p>
                                                <div class="d-flex gap-2">
                                                    <a href="<?= $ruta_resolucion ?>" target="_blank" class="btn btn-outline-info btn-sm">
                                                        Ver en otra pestaña
                                                    </a>
                                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                                        onclick="toggleIframe('visor_resolucion')">
                                                        Mostrar/Ocultar Vista Previa
                                                    </button>
                                                </div>
                                                <div id="visor_resolucion" style="display:none;">
                                                    <iframe src="<?= $ruta_resolucion ?>" width="100%" height="600px" style="border:1px solid #ccc;"></iframe>
                                                </div>
                                            </div> -->
                                        <!-- obtener el nombre del documento -->
                                        <?php $nombre_resolucion = basename($ruta_resolucion ?? ''); ?>

                                        <div class="col-6 p-2">
                                            <div class="card card-documentos shadow h-100 p-3 border d-flex flex-column text-center"
                                                style=" background-color: #002f551a">
                                                <label for="visor_resolucion" class="form-label fw-bold">Resolución Asociada</label>
                                                <div class="input-group shadow-sm mb-2">
                                                    <span class="input-group-text"> <i class="bi bi-file-earmark-pdf-fill me-2"></i> <b>Documento:</b> </span>
                                                    <input type="text" class="form-control" style="font-size: 0.9em;" id="visor_resolucion_" name="visor_resolucion"
                                                        value="<?= htmlspecialchars($nombre_resolucion ?? "Sin descripción") ?> " readonly>
                                                </div>

                                                <div class="d-flex justify-content-center gap-2 px-4 my-2">
                                                    <a href="<?= $ruta_resolucion ?>" target="_blank" class="bot_verenotrapesta btn btn-sm">
                                                        <i class="bi bi-box-arrow-right"></i> Ver en otra pestaña
                                                    </a>
                                                    <button type="button" class="bot_mostrar_vista btn btn-sm"
                                                        onclick="toggleIframe('visor_resolucion',this)">
                                                        <i class="bi bi-eye"></i> <span>Mostrar Vista Previa</span>
                                                    </button>
                                                </div>

                                                <div id="visor_resolucion" class="iframe-animado">
                                                    <iframe src="<?= $ruta_resolucion ?>" width="100%" height="750px" style="border:1px solid #ccc;"></iframe>
                                                </div>
                                            </div>
                                        </div>

                                    <?php endif; ?>



                                    <?php if ($rol_usuario === 'director_catastro' && $id_resolucion): ?>

                                        <!-- <div class="card p-2 my-2 mt-3">
                                                <label><b>Subir Resolución Director (PDF)</b></label>
                                                <?php if (!$has_director_resolucion): ?>
                                                    <input type="file" id="pdf_director" accept=".pdf" class="form-control-file mb-2">
                                                    <button type="button" class="btn btn-primary btn-sm" onclick="uploadResolucionDirector()">
                                                        <i class="fas fa-upload"></i> Subir y Guardar
                                                    </button>
                                                <?php else: ?>
                                                    <p class="text-info">Resolución Director ya ha sido subida.</p>
                                                <?php endif; ?>
                                                <div id="status_director" class="mt-2"></div>
                                            </div> -->

                                        <div class="col-9 p-2">
                                            <div class="card  my-2 mt-3 card-documentos shadow h-100 p-3 border d-flex flex-column text-center">
                                                <label for="nombre_doc1" class="form-label fw-bold">Subir Resolución Director (PDF)</label>
                                                <?php if (!$has_director_resolucion): ?>
                                                    <div class="input-group  shadow-sm">
                                                        <label class="input-group-text" style="font-size:0.9em;"><i class="bi bi-file-earmark-pdf-fill"></i></label>
                                                        <input type="file" class="form-control" style="font-size:0.8em;" accept=".pdf" id="pdf_director">
                                                        <button type="button" class="btn btn-sm text-white" style="background-color: #002F55;" onclick="uploadResolucionDirector()">
                                                            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Subir y Guardar
                                                        </button>
                                                    </div>
                                                <?php else: ?>
                                                    <p class=" fw-bold" style="color: #022F55;">Resolución Director ya ha sido subida.</p>
                                                <?php endif; ?>
                                                <!-- <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div> -->
                                            <?php endif; ?>
                                            <div id="status_director" class="mt-2"></div>
                                            </div>
                                        </div>


                                </div>

                                <div class="col-12 form-group my-3">
                                    <label for="observacion_asignacion" class="my-2"><b>Observaciones de la entrega</b></label>
                                    <textarea class="form-control text-center d-flex "
                                        id="observacion_asignacion"
                                        name="observacion_asignacion"
                                        rows="3"
                                        style="background-color:#ff7e3626; color:#333;"
                                        disabled><?php echo $observacion_a_usuario_tramite; ?></textarea>
                                </div>

                            </div>
                            <!-- Tramite a quien le voy a entregar -->
                            <div style="background-color: #022F55; width: 100%;" class="rounded-3 my-3">
                                <h5 class="text-white text-center py-2 m-0">DESTINATARIO DE LA ASIGNACIÓN PARA REVISIÓN</h5>
                            </div>


                            <!-- Tramite a quien le voy a entregar -->
                            <div class="form-group">
                                <div class="form-row">
                                    <div class="col-md-4 p-1 px-2 my-2">
                                        <label class="form-label fw-bold" style="font-size:0.9em">Cédula del usuario</label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                                            <input type="text" class="form-control" name="entrega_cc_usuario" style="font-size:0.9em"
                                                value="<?php echo htmlspecialchars($dest_cc_usuario ?? ''); ?>" readonly>
                                        </div>
                                    </div>


                                    <div class="col-md-4 p-1 px-2 my-2">
                                        <label class="form-label fw-bold" style="font-size:0.9em">Nombres del usuario</label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                            <input type="text" class="form-control" name="entrega_nombre_usuario" style="font-size:0.9em"
                                                value="<?php echo htmlspecialchars($dest_nombre_usuario ?? ''); ?>" readonly>
                                        </div>
                                    </div>

                                    <div class="col-md-4 p-1 px-2 my-2">
                                        <label for="creacion_tram_apellido_usuario" class="form-label fw-bold" style="font-size:0.9em">Apellidos del usuario</label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text"><i class="bi-people-fill"></i></span>
                                            <input type="text" class="form-control" name="entrega_apellido_usuario" style="font-size: 0.9em;" name="creacion_tram_nombre_usuario"
                                                value="<?php echo htmlspecialchars($dest_apellido_usuario ?? ''); ?>" readonly>
                                        </div>
                                    </div>


                                    <div class="col-md-6 p-1 px-2 my-2">
                                        <label class="form-label fw-bold" style="font-size:0.9em">Cargo del usuario</label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text"><i class="bi bi-person-gear"></i></span>
                                            <input type="text" class="form-control" name="entrega_rol_usuario" style="font-size:0.9em"
                                                value="<?php echo htmlspecialchars($dest_rol_usuario ?? ''); ?>" readonly>
                                        </div>
                                    </div>

                                    <div class="col-md-6 p-1 px-2 my-2">
                                        <label for="fecha_limite" class="form-label fw-bold" style="font-size:0.9em; ">Fecha límite de respuesta a siguiente área</label>
                                        <div class="input-group shadow-sm ">
                                            <span class="input-group-text text-white" style="background-color: #022F55; border:1px solid #022F55"><i class="bi bi-calendar2-x"></i></span>
                                            <input type="date" style="border:1px solid #022F55" id="fecha_limite" class="form-control" name="fecha_limite" value="<?php echo date('Y-m-d', strtotime('+4 days')); ?>" readonly>
                                        </div>
                                    </div>
                                    <br><br>
                                    <div class="col-md-12 my-4" style="border-bottom: 1px solid #02305561;"></div>


                                    <!-- Contenedor principal de los documentos -->
                                    <div id="documentosContainer" class="px-5" style="margin-left: 16%;">
                                        <div class="form-row documento-group ">
                                            <div class="col-md-12 form-group my-3 text-center align-center">
                                                <button type="button" class="btn btn-verr" id="agregarDocumento" style="border:1px solid #022F55; color:#022F55;">
                                                    <i class="bi bi-file-earmark-plus"></i> Agregar otros documentos
                                                </button>
                                            </div>

                                            <div class="col-md-6 p-1 px-2 my-1 ">
                                                <label class="form-label" style="font-size:0.9em;"><b>Tipo de documento</b></label>
                                                <div class="input-group shadow-sm">
                                                    <label class="input-group-text">
                                                        <i class="bi bi-file-earmark-text-fill"></i>
                                                    </label>
                                                    <select class="form-select" style="font-size:0.9em;" name="tipo_doc1" required>
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
                                                <div class="input-group  shadow-sm">
                                                    <label class="input-group-text" style="font-size:0.9em;"><i class="bi bi-file-earmark-pdf-fill"></i></label>
                                                    <input type="file" class="form-control" style="font-size:0.8em;" name="nombre_doc1">
                                                </div>
                                                <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="col-md-12 my-4" style="border-bottom: 1px solid #02305561;"></div>

                                    <div class="form-group mx-3" style="width: 97%;">
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
                                    <input type="hidden" id="historial_nombre_usuario" name="historial_nombre_usuario">
                                    <input type="hidden" id="historial_apellido_usuario" name="historial_apellido_usuario">

                                    <div class="form-group mt-4 mb-5" style="margin-left: 39.5%;">
                                        <div class="d-flex justify-content-center gap-3">
                                            <!-- Botón Aprobar -->
                                            <button type="submit" name="accion" value="aprobar"
                                                class="btn btn-success px-4">
                                                <i class="bi bi-bookmark-check-fill"></i> APROBAR
                                            </button>

                                            <script>
                                                document.getElementById("btnAprobar").addEventListener("click", function() {
                                                    let cod = new URLSearchParams(window.location.search).get("cod");
                                                    localStorage.setItem("aprobado_" + cod, "true");
                                                });
                                            </script>


                                            <?php if ($rol_usuario !== 'reconocedor'): ?>
                                                <button type="button" class="btn btn-danger px-4" data-bs-toggle="modal" data-bs-target="#modalDevolver">
                                                    <i class="bi bi-bookmark-x-fill me-1"></i>DEVOLVER
                                                </button>
                                            <?php endif; ?>
                                            <!-- Modal para radicar documento -->
                                            <?php if ($rol_usuario === 'procedencia_juridica'): ?>
                                                <button type="button" class="pj-btn-open" data-bs-toggle="modal" data-bs-target="#pjModal">
                                                    <i class="bi bi-send-check me-1"></i> GENERAR RESOLUCIÓN
                                                </button>
                                                <!-- Modal -->
                                                <div class="modal fade" id="pjModal" tabindex="-1" aria-labelledby="pjModalLabel" aria-hidden="true" style="z-index: 2000;">
                                                    <div class="pj-modal-dialog modal-dialog">
                                                        <div class="pj-modal-content modal-content">
                                                            <div class="pj-modal-header modal-header text-white px-4" style="background-color: #002F55 !important" ;>
                                                                <h5 class="pj-modal-title modal-title" id="pjModalLabel">Creación de documento</h5>
                                                                <button type="button" class="pj-btn-close btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="pj-modal-body modal-body">
                                                                <div class="pj-container">
                                                                    <!-- Panel izquierdo: Editor Word-like -->
                                                                    <div class="pj-editor-panel">
                                                                        <h6 class="pj-panel-title text-white py-2 rounded-3" style="background-color: #002F55;"><i class="bi bi-pencil-square me-2"></i> Editor de Documento</h6>

                                                                        <div class="mb-3 d-flex align-items-center bg-light p-2 rounded border">
                                                                            <label for="pjDocTypeSelect" class="form-label mb-0 me-2 fw-bold text-dark" style="font-size:0.9em;">
                                                                                <i class="bi bi-file-earmark-diff-fill text-primary me-1"></i> Tipo de Documento:
                                                                            </label>
                                                                            <select id="pjDocTypeSelect" class="form-select form-select-sm" style="width: auto; font-size:0.9em; display: inline-block;">
                                                                                <option value="RESOLUCIÓN" selected>Resolución</option>
                                                                                <option value="OFICIO">Oficio</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="pj-document-editor" id="documentEditor" contenteditable="true">
                                                                            <!-- Estructura inicial editable, misma que preview -->
                                                                            <!-- Header con 3 columnas -->
                                                                            <div class="pj-doc-header">
                                                                                <!-- Columna izquierda: Escudo y entidad -->
                                                                                <div class="pj-header-left">
                                                                                    <div class="pj-logo-escudo">
                                                                                        <img src="assets/img/logo_final_arbitrium.png" alt="Escudo" class="pj-escudo">
                                                                                    </div>
                                                                                    <div class="pj-entidad-info">
                                                                                        <div class="pj-entidad-nombre">Gestión Catastral<br>ARBITRIUM S.A.S.</div>
                                                                                        <div class="pj-entidad-nit">NIT: 900.749.675-1</div>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- Columna central: Tipo de documento -->
                                                                                <div class="pj-header-center">
                                                                                    <div class="pj-doc-type-title" id="prev_tipo">RESOLUCIÓN</div>
                                                                                </div>
                                                                                <!-- Columna derecha: Código y logo alcaldía -->
                                                                                <div class="pj-header-right">
                                                                                    <div class="pj-codigo-box">
                                                                                        <div class="pj-codigo-label">CÓDIGO: </div>
                                                                                        <div class="pj-version">VERSIÓN: 01</div>
                                                                                        <div class="pj-vigencia">VIGENCIA: <strong>2025</strong></div>
                                                                                    </div>
                                                                                    <div class="pj-logo-alcaldia">
                                                                                        <img src="assets/img/logo_final_arbitrium.png" alt="Logo alcaldía" class="pj-logo-alc">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <!-- Número de resolución -->
                                                                            <div class="pj-resolution-number">
                                                                                <span class="pj-res-label">No.</span>
                                                                                <span class="pj-res-numero" id="prev_numero"><?php echo $next_res; ?></span>
                                                                            </div>
                                                                            <!-- Fecha -->
                                                                            <div class="pj-doc-date">
                                                                                <span id="prev_fecha">-</span>
                                                                            </div>
                                                                            <!-- Título del documento -->
                                                                            <div class="pj-doc-title">
                                                                                <p id="prev_asunto">
                                                                                    "POR LA CUAL SE INSCRIBE EN LA BASE DE DATOS CATASTRAL LA MUTACIÓN POR ENGLOBO
                                                                                    DEL PREDIO IDENTIFICADO CON MATRICULA INMOBILIARIA No. 375-23456"
                                                                                </p>
                                                                            </div>
                                                                            <!-- Cargo del emisor -->
                                                                            <div class="pj-emisor-cargo">
                                                                                <p>EL DIRECTOR LOCAL ADMINISTRATIVO DE CATASTRO</p>
                                                                            </div>
                                                                            <!-- Texto introductorio -->
                                                                            <div class="pj-intro-text">
                                                                                <p>En uso de sus facultades legales, en especial las conferidas por el artículo 5°
                                                                                    de la Ley 14 de 1983, artículo 6° literal a del Decreto 3496 de 1983, artículo
                                                                                    10° de la Resolución 70 de 2011 expedida por el IGAC y,</p>
                                                                            </div>
                                                                            <!-- CONSIDERANDO -->
                                                                            <div class="pj-considerando-title">
                                                                                <strong>CONSIDERANDO</strong>
                                                                            </div>
                                                                            <!-- Cuerpo del documento -->
                                                                            <div class="pj-doc-body" id="prev_cuerpo">
                                                                                <p>Que mediante radicado No. <span id="prev_radicado">-</span> de fecha
                                                                                    <span id="prev_fecha_radicado">-</span>, el(la) señor(a) <span id="prev_solicitante">-</span>,
                                                                                    identificado(a) con <span id="tip_cc">-</span>, No. <span id="prev_cc">-</span>, actuando en calidad de
                                                                                    <span id="prev_calidad">-</span>, solicitó la inscripción de mutación por englobo
                                                                                    del predio identificado con <strong>NPN <span id="prev_npn">-</span></strong> y
                                                                                    matrícula inmobiliaria No. <span id="prev_matricula">-</span>.
                                                                                </p>
                                                                                <p>Que una vez revisada la documentación allegada, se encontró que cumple con los
                                                                                    requisitos establecidos en la normatividad vigente para adelantar el trámite solicitado.</p>
                                                                                <p>Que en mérito de lo expuesto,</p>
                                                                            </div>
                                                                            <!-- RESUELVE -->

                                                                            <!-- Artículos -->
                                                                            <!-- Firma -->
                                                                            <div class="pj-doc-signature">
                                                                                <div class="pj-firma-linea"></div>
                                                                                <p><strong id="prev_firma">-</strong></p>
                                                                                <p id="prev_cargo">Director Local Administrativo de Catastro</p>
                                                                            </div>
                                                                            <!-- Pie de página -->
                                                                            <div class="pj-doc-footer">
                                                                                <p>Proyectó: <span id="prev_proyecto">-</span></p>
                                                                                <p>Revisó: <span id="prev_reviso">-</span></p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <!-- Panel derecho: Vista previa (no editable) -->
                                                                    <div class="pj-preview-panel px-3">
                                                                        <h6 class="fw-bold my-3 text-white py-2 rounded-3" style="background-color: #002F55;"><i class="bi bi-eye-fill me-2"></i></i>Vista Previa</h6>
                                                                        <div class="pj-document-preview" id="documentPreview">
                                                                            <!-- Copia inicial de la estructura, se sincronizará -->
                                                                            <!-- Header con 3 columnas -->
                                                                            <div class="pj-doc-header">
                                                                                <!-- Columna izquierda: Escudo y entidad -->
                                                                                <div class="pj-header-left">
                                                                                    <div class="pj-logo-escudo">
                                                                                        <img src="logo_final_arbitrium.png" alt="Escudo" class="pj-escudo">
                                                                                    </div>
                                                                                    <div class="pj-entidad-info">
                                                                                        <div class="pj-entidad-nombre">ALCALDÍA MUNICIPAL<br>LA VICTORIA - VALLE</div>
                                                                                        <div class="pj-entidad-nit">NIT: 891.900.406-2</div>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- Columna central: Tipo de documento -->
                                                                                <div class="pj-header-center">
                                                                                    <div class="pj-doc-type-title" id="prev_tipo_preview">RESOLUCIÓN</div>
                                                                                </div>
                                                                                <!-- Columna derecha: Código y logo alcaldía -->
                                                                                <div class="pj-header-right">
                                                                                    <div class="pj-codigo-box">
                                                                                        <div class="pj-codigo-label">CÓDIGO: GA-FR-09</div>
                                                                                        <div class="pj-version">VERSIÓN: 01</div>
                                                                                        <div class="pj-vigencia">VIGENCIA: <strong>2019</strong></div>
                                                                                    </div>
                                                                                    <div class="pj-logo-alcaldia">
                                                                                        <img src="../../imagenes/logo_final_arbitrium.png" alt="Logo Alcaldía" class="pj-logo-alc">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <!-- Número de resolución -->
                                                                            <div class="pj-resolution-number">
                                                                                <span class="pj-res-label">No.</span>
                                                                                <span class="pj-res-numero" id="prev_numero_preview"><?php echo $next_res; ?></span>
                                                                            </div>
                                                                            <!-- Fecha -->
                                                                            <div class="pj-doc-date">
                                                                                <span id="prev_fecha_preview">-</span>
                                                                            </div>
                                                                            <!-- Título del documento -->
                                                                            <div class="pj-doc-title">
                                                                                <p id="prev_asunto_preview">
                                                                                    "POR LA CUAL SE INSCRIBE EN LA BASE DE DATOS CATASTRAL LA MUTACIÓN POR ENGLOBO
                                                                                    DEL PREDIO IDENTIFICADO CON MATRICULA INMOBILIARIA No. 375-23456"
                                                                                </p>
                                                                            </div>
                                                                            <!-- Cargo del emisor -->
                                                                            <div class="pj-emisor-cargo">
                                                                                <p>EL DIRECTOR LOCAL ADMINISTRATIVO DE CATASTRO</p>
                                                                            </div>
                                                                            <!-- Texto introductorio -->
                                                                            <div class="pj-intro-text">
                                                                                <p>En uso de sus facultades legales, en especial las conferidas por el artículo 5°
                                                                                    de la Ley 14 de 1983, artículo 6° literal a) del Decreto 3496 de 1983, artículo
                                                                                    10° de la Resolución 70 de 2011 expedida por el IGAC y,</p>
                                                                            </div>
                                                                            <!-- CONSIDERANDO -->
                                                                            <div class="pj-considerando-title">
                                                                                <strong>CONSIDERANDO</strong>
                                                                            </div>
                                                                            <!-- Cuerpo del documento -->
                                                                            <div class="pj-doc-body" id="prev_cuerpo_preview">
                                                                                <p>Que mediante radicado No. <span id="prev_radicado_preview">-</span> de fecha
                                                                                    <span id="prev_fecha_radicado_preview">-</span>, el(la) señor(a) <span id="prev_solicitante_preview">-</span>,
                                                                                    identificado(a) con <span id="tip_cc_preview">-</span>, No. <span id="prev_cc_preview">-</span>, actuando en calidad de
                                                                                    <span id="prev_calidad_preview">-</span>, solicitó la inscripción de mutación por englobo
                                                                                    del predio identificado con <strong>NPN <span id="prev_npn_preview">-</span></strong> y
                                                                                    matrícula inmobiliaria No. <span id="prev_matricula_preview">-</span>.
                                                                                </p>
                                                                                <p>Que una vez revisada la documentación allegada, se encontró que cumple con los
                                                                                    requisitos establecidos en la normatividad vigente para adelantar el trámite solicitado.</p>
                                                                                <p>Que en mérito de lo expuesto,</p>
                                                                            </div>
                                                                            <!-- RESUELVE -->
                                                                            <div class="pj-resuelve-title">
                                                                                <strong>RESUELVE</strong>
                                                                            </div>
                                                                            <!-- Artículos -->
                                                                            <div class="pj-articulos">
                                                                                <div class="pj-articulo">
                                                                                    <p><strong>ARTÍCULO PRIMERO:</strong> <span id="prev_articulo1_preview">Inscribir en la base de datos
                                                                                            catastral la mutación por englobo solicitada.</span></p>
                                                                                </div>
                                                                                <div class="pj-articulo">
                                                                                    <p><strong>ARTÍCULO SEGUNDO:</strong> Notificar la presente resolución conforme a lo establecido
                                                                                        en el artículo 44 del Código de Procedimiento Administrativo y de lo Contencioso Administrativo.</p>
                                                                                </div>
                                                                                <div class="pj-articulo">
                                                                                    <p><strong>ARTÍCULO TERCERO:</strong> Contra la presente resolución procede el recurso de reposición
                                                                                        ante el Director Local Administrativo de Catastro, dentro de los diez (10) días siguientes a su
                                                                                        notificación.</p>
                                                                                </div>
                                                                                <div class="pj-articulo">
                                                                                    <p><strong>ARTÍCULO CUARTO:</strong> La presente resolución rige a partir de la fecha de su expedición.</p>
                                                                                </div>
                                                                            </div>
                                                                            <!-- Firma -->
                                                                            <div class="pj-doc-signature">
                                                                                <div class="pj-firma-linea"></div>
                                                                                <p><strong id="prev_firma_preview">-</strong></p>
                                                                                <p id="prev_cargo_preview">Director Local Administrativo de Catastro</p>
                                                                            </div>
                                                                            <!-- Pie de página -->
                                                                            <div class="pj-doc-footer">
                                                                                <p>Proyectó: <span id="prev_proyecto_preview">-</span></p>
                                                                                <p>Revisó: <span id="prev_reviso_preview">-</span></p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="pj-modal-footer modal-footer">
                                                                <button type="button" class="pj-btn-secondary btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                                <button type="button" class="pj-btn-primary btn btn-primary" onclick="guardarDocumento()">Guardar Documento</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                </form>
                <!-- MODAL DEVOLVER TRAMITE -->
                <div class="modal fade" id="modalDevolver" tabindex="-1" role="dialog" style="z-index: 1550;">
                    <div class="modal-dialog modal-xl  modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white px-5">
                                <h5 class="modal-title"><b>Devolver Trámite</b></h5>
                                <button type="button" class="btn-close" style="background-color:white !important" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="formModal" enctype="multipart/form-data">
                                <!-- input hidden para saber qué acción se hizo -->
                                <input type="hidden" name="accion" id="accion" value="">
                                <div class="modal-body">
                                    <!-- Devolucion -->
                                    <input type="hidden" name="cod_tramite" value="<?php echo $tramite['cod_tramite']; ?>">
                                    <div class="form-row">
                                        <!--<div class="col-md-6">
                                            <label for="cod_tramite"><b>ID_Radicacion</b></label>
                                            <input class="form-control py-4" id="cod_tramite" name="entrega_cod_tramite"
                                                type="text" value="<?php echo htmlspecialchars($tramite['cod_tramite']); ?>" readonly>
                                        </div>-->

                                        <div class="col-md-6 p-1 px-2 my-2">
                                            <label for="cod_tramite" class="form-label fw-bold" style="font-size:0.9em">Identificador radicación</label>
                                            <div class="input-group shadow-sm">
                                                <span class="input-group-text"><i class="bi-journal-text"></i></span>
                                                <input type="text" class="form-control" style="font-size: 0.9em;" id="cod_tramite" name="entrega_cod_tramite"
                                                    value="<?php echo htmlspecialchars($tramite['cod_tramite']); ?>" readonly>
                                            </div>
                                        </div>

                                        <!--<div class="col-md-6">
                                            <label for="fecha_rad"><b>Hora Radicación del Trámite</b></label>
                                            <input class="form-control py-4" id="fecha_rad" name="historial_fecha_tramite"
                                                type="text" value="<?php echo htmlspecialchars($tramite['fecha_rad']); ?>" readonly>
                                        </div>-->

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
                                                    <input type="text" class="form-control" style="font-size: 0.9em;" id="entrega_cc_usuario_modal" name="entrega_cc_usuario"
                                                        value="<?php echo htmlspecialchars($creacion_tram_cc_usuario ?? ''); ?>" readonly>
                                                    <input type="hidden"
                                                        id="entrega_cc_usuario_modal_hidden"
                                                        name="entrega_cc_usuario"
                                                        value="<?php echo htmlspecialchars($creacion_tram_cc_usuario ?? ''); ?>">
                                                </div>
                                            </div>

                                            <div class="col-md-3 p-1 px-2 ">
                                                <label for="creacion_tram_nombre_usuario" class="form-label fw-bold" style="font-size:0.9em">Nombres del usuario</label>
                                                <div class="input-group shadow-sm">
                                                    <span class="input-group-text"><i class="bi bi-person-bounding-box"></i></span>
                                                    <input type="text" class="form-control" style="font-size: 0.9em;" id="entrega_nombre_usuario_modal" name="entrega_nombre_usuario"
                                                        value="<?php echo htmlspecialchars($creacion_tram_nombre_usuario ?? ''); ?>" readonly>
                                                    <input type="hidden"
                                                        id="entrega_nombre_usuario_modal_hidden"
                                                        name="entrega_nombre_usuario"
                                                        value="<?php echo htmlspecialchars($creacion_tram_nombre_usuario ?? ''); ?>">
                                                </div>
                                            </div>

                                            <div class="col-md-3 p-1 px-2 ">
                                                <label for="creacion_tram_apellido_usuario" class="form-label fw-bold" style="font-size:0.9em">Apellido del usuario</label>
                                                <div class="input-group shadow-sm">
                                                    <span class="input-group-text"><i class="bi-people-fill"></i></span>
                                                    <input type="text" class="form-control" style="font-size: 0.9em;" id="entrega_apellido_usuario_modal" name="entrega_apellido_usuario"
                                                        value="<?php echo htmlspecialchars($creacion_tram_apellido_usuario ?? ''); ?>" readonly>
                                                    <input type="hidden"
                                                        id="entrega_apellido_usuario_modal_hidden"
                                                        name="entrega_apellido_usuario"
                                                        value="<?php echo htmlspecialchars($creacion_tram_apellido_usuario ?? ''); ?>">
                                                </div>
                                            </div>

                                            <div class="col-md-3 p-1 px-2 ">
                                                <label for="creacion_tram_rol_usuario" class="form-label fw-bold" style="font-size:0.9em">Cargo</label>
                                                <div class="input-group shadow-sm">
                                                    <span class="input-group-text"><i class="bi bi-person-bounding-box"></i></span>
                                                    <input type="text" class="form-control" style="font-size: 0.9em;" id="entrega_rol_usuario_modal" name="entrega_rol_usuario"
                                                        value="<?php echo htmlspecialchars($creacion_tram_rol_usuario ?? ''); ?>" readonly>
                                                    <input type="hidden"
                                                        id="entrega_rol_usuario_modal_hidden"
                                                        name="entrega_rol_usuario"
                                                        value="<?php echo htmlspecialchars($creacion_tram_rol_usuario ?? ''); ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <!--<div class="form-row mt-3">
                                            <div class="col-md-6">
                                                <label for="documento_soporte"><b>Documento soporte (opcional)</b></label>
                                                <input type="file"
                                                    class="form-control-file"
                                                    name="documento_soporte"
                                                    id="documento_soporte"
                                                    accept=".pdf,.jpg,.png,.doc,.docx">
                                            </div>
                                        </div>-->
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
                                        <textarea class="form-control" name="motivo_devolucion" rows="3" required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <!-- Importante: el botón NO hace submit directo -->
                                    <button type="button" id="btnDevolver" class="btn btn-danger">
                                        <i class="bi bi-arrow-return-left me-2"></i> Confirmar Devolución
                                    </button>
                                    <button type="button" class="btn btn-white text-white" style="background-color: #022F55;" data-bs-dismiss="modal">Cancelar</button>
                                </div>
                                <input type="hidden"
                                    name="rol_actual"
                                    value="<?php echo htmlspecialchars($_SESSION['rol_usuario'] ?? ''); ?>">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.container-fluid -->
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
        const container = document.getElementById("documentosContainer");
        const btnAgregar = document.getElementById("agregarDocumento");

        btnAgregar.addEventListener("click", function() {
            if (contadorDocs >= maxDocs) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Límite alcanzado',
                    text: 'Solo puedes agregar hasta 5 documentos.',
                    confirmButtonColor: '#022F55'
                });
                return;
            }

            contadorDocs++;

            // Clonamos el bloque original
            const grupoOriginal = container.querySelector(".documento-group");
            const nuevoGrupo = grupoOriginal.cloneNode(true);

            // Eliminamos el botón del clon para evitar duplicaciones
            const boton = nuevoGrupo.querySelector("#agregarDocumento");
            if (boton) boton.remove();

            // Limpiamos los valores de los campos del clon
            nuevoGrupo.querySelectorAll("select, input[type='file']").forEach(el => {
                if (el.name.startsWith("tipo_doc")) {
                    el.name = `tipo_doc${contadorDocs}`;
                    el.selectedIndex = 0;
                }
                if (el.name.startsWith("nombre_doc")) {
                    el.name = `nombre_doc${contadorDocs}`;
                    el.value = "";
                }
            });

            // Insertamos el nuevo bloque al final del contenedor
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


    // Devolver
    document.getElementById("btnDevolver").addEventListener("click", function() {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¿Deseas devolver este trámite?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, devolver',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Capturo los datos del modal
                let modalForm = document.getElementById("formModal");
                let formData = new FormData(modalForm);
                // Forzamos que la acción sea DEVOLVER
                formData.set("accion", "devolver");
                fetch("<?= neiva_app_url('Arbimaps/vistas/seguimiento/acciones/procesar_devolucion.php') ?>", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Devolución realizada!',
                            text: data,
                            confirmButtonColor: '#02722d'
                        }).then(() => {
                            window.location.href = "index.php?page=tramites/consultar_tramite";
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
    // Función para subir resolución director
    async function uploadResolucionDirector() {
        const fileInput = document.getElementById('pdf_director');
        const file = fileInput.files[0];
        const statusDiv = document.getElementById('status_director');
        if (!file) {
            Swal.fire({
                icon: 'warning',
                title: 'Archivo requerido',
                text: 'Por favor, selecciona un archivo PDF.'
            });
            return;
        }
        if (file.type !== 'application/pdf') {
            Swal.fire({
                icon: 'error',
                title: 'Formato inválido',
                text: 'Solo se permiten archivos PDF.'
            });
            return;
        }
        const formData = new FormData();
        formData.append('pdf_file', file);
        formData.append('cod_tramite', '<?php echo htmlspecialchars($cod_tramite); ?>');
        formData.append('id_resolucion', '<?php echo $id_resolucion; ?>');
        statusDiv.innerHTML = '<small class="text-info">Subiendo...</small>';
        try {
            const response = await fetch('vistas/seguimiento/acciones/guardar_resolucion_director.php', {
                method: 'POST',
                body: formData
            });
            const responseText = await response.text();
            const jsonStart = responseText.lastIndexOf('{');
            if (jsonStart === -1) {
                throw new Error(responseText.trim().slice(0, 250) || 'El servidor no devolvio JSON.');
            }
            let result;
            try {
                result = JSON.parse(responseText.slice(jsonStart));
            } catch (parseError) {
                throw new Error(responseText.trim().slice(0, 250) || 'Respuesta invalida del servidor.');
            }
            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: result.message
                });
                statusDiv.innerHTML = '<small class="text-success">Subido correctamente. Recargando página...</small>';
                setTimeout(() => location.reload(), 1500);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message
                });
                statusDiv.innerHTML = '<small class="text-danger">Error al subir.</small>';
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: error.message || 'Hubo un problema al subir el archivo.'
            });
            statusDiv.innerHTML = '<small class="text-danger">Error de conexión.</small>';
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
    // Script para el editor Word-like SIN AUTO–RELLENO
    document.addEventListener('DOMContentLoaded', function() {
        const editor = document.getElementById('documentEditor');
        const preview = document.getElementById('documentPreview');

        // Solo dejamos el contenido tal cual está en el HTML,
        // sin cargar nada desde PHP ni desde la base de datos.
        preview.innerHTML = editor.innerHTML;

        // Dynamic consecutive numbers from PHP
        const nextResolutionNumber = "<?php echo $next_res; ?>";
        const nextOficioNumber = "<?php echo $next_oficio; ?>";

        const docTypeSelect = document.getElementById('pjDocTypeSelect');
        if (docTypeSelect) {
            docTypeSelect.addEventListener('change', function() {
                const docType = this.value;
                const typeLabel = document.getElementById('prev_tipo');
                const typeLabelPreview = document.getElementById('prev_tipo_preview');
                const numLabel = document.getElementById('prev_numero');
                const numLabelPreview = document.getElementById('prev_numero_preview');
                
                if (typeLabel) typeLabel.innerText = docType;
                if (typeLabelPreview) typeLabelPreview.innerText = docType;
                
                const nextNum = (docType === 'RESOLUCIÓN') ? nextResolutionNumber : nextOficioNumber;
                if (numLabel) numLabel.innerText = nextNum;
                if (numLabelPreview) numLabelPreview.innerText = nextNum;
                
                // Synchronize preview content
                preview.innerHTML = editor.innerHTML;
            });
        }

        // Sincronizar preview en tiempo real
        editor.addEventListener('input', function() {
            preview.innerHTML = this.innerHTML;
        });

        // Guardamos el código del trámite solo para enviarlo al backend
        const codTramite = "<?php echo htmlspecialchars($cod_tramite); ?>";
        window.codTramite = codTramite;

        // Al cerrar el modal, NO recargamos desde BD, solo dejamos como está
        const modal = document.getElementById('pjModal');
        if (modal) {
            modal.addEventListener('hidden.bs.modal', function() {
                // Si quieres limpiar el contenido, puedes descomentar:
                // editor.innerHTML = '';
                // preview.innerHTML = '';
            });
        }
    });

    // guardarDocumento SIN validaciones de contenido
    async function guardarDocumento() {
        const preview = document.getElementById('documentPreview');
        const codTramite = window.codTramite;

        try {
            const pdfBlob = await html2pdf()
                .from(preview)
                .set({
                    margin: 1,
                    filename: `resolucion_${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.pdf`,
                    image: {
                        type: 'jpeg',
                        quality: 0.98
                    },
                    html2canvas: {
                        scale: 2
                    },
                    jsPDF: {
                        unit: 'in',
                        format: 'letter',
                        orientation: 'portrait'
                    }
                })
                .outputPdf('blob');

            const formData = new FormData();
            formData.append('cod_tramite', codTramite);
            formData.append('pdf_file', pdfBlob, 'resolucion.pdf');

            const response = await fetch('vistas/seguimiento/acciones/guardar_resolucion.php', {
                method: 'POST',
                body: formData
            });

            const responseText = await response.text();
            const jsonStart = responseText.lastIndexOf('{');
            if (jsonStart === -1) {
                throw new Error(responseText.trim().slice(0, 250) || 'El servidor no devolvio JSON.');
            }

            let result;
            try {
                result = JSON.parse(responseText.slice(jsonStart));
            } catch (parseError) {
                throw new Error(responseText.trim().slice(0, 250) || 'Respuesta invalida del servidor.');
            }

            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Documento guardado!',
                    text: result.message,
                    confirmButtonColor: '#004177'
                }).then(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('pjModal'));
                    if (modal) modal.hide();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al guardar',
                    text: result.message
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Hubo un problema al generar o guardar el documento.'
            });
        }
    }
</script>
