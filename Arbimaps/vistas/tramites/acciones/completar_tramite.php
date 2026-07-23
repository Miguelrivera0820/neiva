<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('POST');
neiva_require_permission('menu.tramites', $PERMISOS);
neiva_require_csrf('global');
date_default_timezone_set('America/Bogota');

function app_base_url(): string {
    return neiva_app_url('Arbimaps');
}

$redirectBase = app_base_url() . '/index.php?page=tramites/cuentas_rechazadas/no_procede_completar';

$rol_usuario = $_SESSION['rol_usuario'] ?? '';
$roles_pueden_completar = ['ventanilla_catastral', 'director_catastro', 'administrador'];
if (!in_array($rol_usuario, $roles_pueden_completar, true)) {
    $_SESSION['tramite_msg'] = 'error:No tienes permiso para completar este trÃ¡mite';
    header('Location: ' . $redirectBase);
    exit;
}

function post_value(string $key, array $row, string $fallbackKey = null): string {
    $fallbackKey = $fallbackKey ?? $key;
    if (array_key_exists($key, $_POST)) {
        return trim((string)$_POST[$key]);
    }
    return trim((string)($row[$fallbackKey] ?? ''));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['tramite_msg'] = 'error:Método no permitido';
    header('Location: ' . $redirectBase);
    exit;
}

$id = $_POST['id_completar'] ?? '';
if (empty($id)) {
    $_SESSION['tramite_msg'] = 'error:ID de trámite inválido';
    header('Location: ' . $redirectBase);
    exit;
}

/** =========================================================
 *  Utilidades
 *  ========================================================= */
function base_code(string $code): string {
    return preg_replace('/-\d{2}$/', '', $code ?? '');
}

/**
 * Lee todos los códigos (con o sin sufijo) que empiecen por $base
 * en las tablas relevantes y retorna el siguiente sufijo disponible.
 * Considera:
 *   - cod_tramite = BASE
 *   - cod_tramite = BASE-01, BASE-02, ...
 */
function siguienteSufijo(mysqli $mysqli, string $base): int {
    $max = 0;

    // Buscar sufijos en tramite_radicacion
    $sql1 = "SELECT cod_tramite FROM tramite_radicacion WHERE cod_tramite LIKE CONCAT(?, '%')";
    $stmt1 = $mysqli->prepare($sql1);
    $stmt1->bind_param("s", $base);
    $stmt1->execute();
    $res1 = $stmt1->get_result();
    while ($r = $res1->fetch_assoc()) {
        if ($r['cod_tramite'] === $base) {
            // existe el base sin sufijo → asegura iniciar al menos en 1
            $max = max($max, 0);
        } elseif (preg_match('/^' . preg_quote($base, '/') . '-(\d{2})$/', $r['cod_tramite'], $m)) {
            $n = (int)$m[1];
            if ($n > $max) $max = $n;
        }
    }
    $stmt1->close();

    // Buscar sufijos en tramites_por_completar también
    $sql2 = "SELECT cod_tramite FROM tramites_por_completar WHERE cod_tramite LIKE CONCAT(?, '%')";
    $stmt2 = $mysqli->prepare($sql2);
    $stmt2->bind_param("s", $base);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    while ($r = $res2->fetch_assoc()) {
        if ($r['cod_tramite'] === $base) {
            $max = max($max, 0);
        } elseif (preg_match('/^' . preg_quote($base, '/') . '-(\d{2})$/', $r['cod_tramite'], $m)) {
            $n = (int)$m[1];
            if ($n > $max) $max = $n;
        }
    }
    $stmt2->close();

    // siguiente número (si no había ninguno, empezará en 1)
    return $max + 1;
}

/** Genera un nuevo código con el siguiente sufijo disponible. */
function generarCodigoTramiteIncremental(mysqli $mysqli, string $cod_original): string {
    $base = base_code($cod_original);
    $next = siguienteSufijo($mysqli, $base);
    return sprintf('%s-%02d', $base, $next);
}

/** Verifica si un código ya existe (colisión). */
function existeCodigo(mysqli $mysqli, string $cod): bool {
    $sql = "SELECT 1 FROM tramite_radicacion WHERE cod_tramite = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $cod);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    return $exists;
}

/** =========================================================
 *  Obtener datos del trámite a completar
 *  ========================================================= */
$sqlSelect = "SELECT * FROM tramites_por_completar WHERE cod_tramite = ?";
$stmt = $mysqli->prepare($sqlSelect);
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();

if (!$row = $result->fetch_assoc()) {
    $_SESSION['tramite_msg'] = 'error:Trámite no encontrado';
    header('Location: ' . $redirectBase);
    exit;
}
$stmt->close();

/** =========================================================
 *  Generar nuevo código con sufijo incremental
 *  ========================================================= */
$nuevo_cod_tramite = generarCodigoTramiteIncremental($mysqli, $row['cod_tramite']);

// Reintento si hubiera colisión por carrera (muy raro, pero seguro)
if (existeCodigo($mysqli, $nuevo_cod_tramite)) {
    // recalcula con el estado actual
    $base = base_code($row['cod_tramite']);
    $next = siguienteSufijo($mysqli, $base);
    $nuevo_cod_tramite = sprintf('%s-%02d', $base, $next);
}

/** =========================================================
 *  Carpeta de almacenamiento → siempre la del código base
 *  ========================================================= */
$base_cod = base_code($id);
$anio     = substr($base_cod, 4, 4); // CAT-2025-... → 2025
$base_path = realpath(__DIR__ . '/../../..');
if ($base_path === false) {
    $base_path = __DIR__ . '/../../..';
}
$usuario_dir = $base_path . "/tramites_conservacion/$anio/$base_cod/";

if (!file_exists($usuario_dir)) {
    if (!mkdir($usuario_dir, 0777, true)) {
        $_SESSION['tramite_msg'] = "error:No se pudo crear el directorio: $usuario_dir";
        header('Location: ' . $redirectBase . '&action=completar&cod=' . urlencode($id));
        exit;
    }
    @chmod($usuario_dir, 0777);
}
if (!is_writable($usuario_dir)) {
    $_SESSION['tramite_msg'] = "error:El directorio $usuario_dir no tiene permisos de escritura";
    header('Location: ' . $redirectBase . '&action=completar&cod=' . urlencode($id));
    exit;
}

/** =========================================================
 *  Archivos subidos
 *  ========================================================= */
$archivos = [
    'sol_escrita_tramite'     => 'Solicitud Escrita',
    'cop_escritura_tramite'   => 'Copia de Escritura',
    'ctl_tramite'             => 'CTL',
    'doc_identidad_tramite'   => 'Documento de Identidad',
    'carta_autorizacion_tramite' => 'Carta de Autorización',
    'otros_doc_tramite'       => 'Otros Documentos'
];

$nombres_archivos = [];

foreach ($archivos as $campo => $titulo) {
    if (isset($_FILES[$campo]) && $_FILES[$campo]['error'] === UPLOAD_ERR_OK) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES[$campo]['tmp_name']);
        finfo_close($finfo);

        if ($mime_type !== 'application/pdf') {
            $_SESSION['tramite_msg'] = "error:El archivo $titulo debe ser PDF (detectado: $mime_type)";
            header('Location: ' . $redirectBase . '&action=completar&cod=' . urlencode($id));
            exit;
        }

        $nombre_archivo = basename($_FILES[$campo]['name']);
        $ruta_destino = $usuario_dir . $nombre_archivo;

        if (file_exists($ruta_destino)) @unlink($ruta_destino);

        if (move_uploaded_file($_FILES[$campo]['tmp_name'], $ruta_destino)) {
            $nombres_archivos[$campo] = $nombre_archivo;
        } else {
            $_SESSION['tramite_msg'] = "error:No se pudo mover el archivo $titulo a $ruta_destino";
            header('Location: ' . $redirectBase . '&action=completar&cod=' . urlencode($id));
            exit;
        }
    } else {
        $nombres_archivos[$campo] = $row[$campo] ?? null;
    }
}

if (empty($nombres_archivos['sol_escrita_tramite']) && empty($row['sol_escrita_tramite'])) {
    $_SESSION['tramite_msg'] = 'error:La Solicitud Escrita es obligatoria. Por favor, adjúntela';
    header('Location: ' . $redirectBase . '&action=completar&cod=' . urlencode($id));
    exit;
}

$datos = [
    'documento_interesado' => post_value('documento_interesado', $row),
    'num_doc_interesado' => post_value('num_doc_interesado', $row),
    'primer_nombre_interesado' => post_value('primer_nombre_interesado', $row),
    'segundo_nombre_interesado' => post_value('segundo_nombre_interesado', $row),
    'primer_apellido_interesado' => post_value('primer_apellido_interesado', $row),
    'segundo_apellido_interesado' => post_value('segundo_apellido_interesado', $row),
    'telefono_interesado' => post_value('telefono_interesado', $row),
    'correo_interesado' => post_value('correo_interesado', $row),
    'fmi_predio' => post_value('fmi_predio', $row),
    'npn_predio' => post_value('cod_catastro', $row, 'npn_predio'),
    'tsolicitante_tramite' => post_value('actividad_tramite', $row, 'tsolicitante_tramite'),
    'nombre_propietario_tram' => post_value('nombre_propietario_tram', $row),
    'tipo_doc_propietario_tram' => post_value('tipo_doc_propietario_tram', $row),
    'cedula_propietario_tram' => post_value('cedula_propietario_tram', $row),
    'valor_avaluo_terreno_tram' => post_value('valor_avaluo_terreno_tram', $row),
    'direccion_predio_terreno_tram' => post_value('direccion_predio_terreno_tram', $row),
    'destino_econ_predio_tram' => post_value('destino_econ_predio_tram', $row),
    'area_terr_predio_tram' => post_value('area_terr_predio_tram', $row),
    'area_cons_predio_tram' => post_value('area_cons_predio_tram', $row),
    'observacion_tramite' => post_value('observacion_tramite', $row),
];

/** =========================================================
 *  Insertar en tramite_radicacion
 *  ========================================================= */
$sqlInsert = "
    INSERT INTO tramite_radicacion (
        id_tramite, cod_tramite, fecha_rad, documento_interesado, num_doc_interesado,
        primer_nombre_interesado, segundo_nombre_interesado, primer_apellido_interesado,
        segundo_apellido_interesado, telefono_interesado, correo_interesado,
        mutacion_tramite, fecha_limite_respuesta, tsolicitante_tramite,
        fmi_predio, npn_predio, sol_escrita_tramite, cop_escritura_tramite,
        ctl_tramite, doc_identidad_tramite, carta_autorizacion_tramite,
        otros_doc_tramite, observacion_tramite
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
";

$stmtInsert = $mysqli->prepare($sqlInsert);
if (!$stmtInsert) {
    $_SESSION['tramite_msg'] = 'error:Error en la base de datos: ' . $mysqli->error;
    header('Location: ' . $redirectBase . '&action=completar&cod=' . urlencode($id));
    exit;
}

$stmtInsert->bind_param(
    "issssssssssssssssssssss",
    $row['id_tramite'],               // i
    $nuevo_cod_tramite,               // s
    $row['fecha_rad'],                // s
    $datos['documento_interesado'],     // s
    $datos['num_doc_interesado'],       // s
    $datos['primer_nombre_interesado'], // s
    $datos['segundo_nombre_interesado'],// s
    $datos['primer_apellido_interesado'],// s
    $datos['segundo_apellido_interesado'],// s
    $datos['telefono_interesado'],      // s
    $datos['correo_interesado'],        // s
    $row['mutacion_tramite'],         // s
    $row['fecha_limite_respuesta'],   // s
    $datos['tsolicitante_tramite'],     // s
    $datos['fmi_predio'],               // s
    $datos['npn_predio'],               // s
    $nombres_archivos['sol_escrita_tramite'],    // s
    $nombres_archivos['cop_escritura_tramite'],  // s
    $nombres_archivos['ctl_tramite'],            // s
    $nombres_archivos['doc_identidad_tramite'],  // s
    $nombres_archivos['carta_autorizacion_tramite'], // s
    $nombres_archivos['otros_doc_tramite'],      // s
    $datos['observacion_tramite']       // s
);

if ($stmtInsert->execute()) {
    $id_tramite_radicacion = $mysqli->insert_id;

    // tramite_info_predio
    $sqlInfo = "
        INSERT INTO tramite_info_predio (
            info_cod_tramite, id_tramite_rad, fmi_predio_tram, npn_predio_tram,
            nombre_propietario_tram, tipo_doc_propietario_tram, cedula_propietario_tram,
            valor_avaluo_terreno_tram, direccion_predio_terreno_tram, destino_econ_predio_tram,
            area_terr_predio_tram, area_cons_predio_tram
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";
    $stmtInfo = $mysqli->prepare($sqlInfo);
    if (!$stmtInfo) {
        $_SESSION['tramite_msg'] = 'error:Error al preparar inserción en tramite_info_predio: ' . $mysqli->error;
        header('Location: ' . $redirectBase . '&action=completar&cod=' . urlencode($id));
        exit;
    }

    // Nota: si los dos últimos campos son numéricos/decimales, usa "dd"; si son texto, cambia a "ss"
    $stmtInfo->bind_param(
        "sissssssssdd",
        $nuevo_cod_tramite,
        $id_tramite_radicacion,
        $datos['fmi_predio'],
        $datos['npn_predio'],
        $datos['nombre_propietario_tram'],
        $datos['tipo_doc_propietario_tram'],
        $datos['cedula_propietario_tram'],
        $datos['valor_avaluo_terreno_tram'],
        $datos['direccion_predio_terreno_tram'],
        $datos['destino_econ_predio_tram'],
        $datos['area_terr_predio_tram'],
        $datos['area_cons_predio_tram']
    );

    if (!$stmtInfo->execute()) {
        $_SESSION['tramite_msg'] = 'error:No se pudo insertar información del predio: ' . $stmtInfo->error;
        header('Location: ' . $redirectBase . '&action=completar&cod=' . urlencode($id));
        exit;
    }
    $stmtInfo->close();

    // eliminar original
    $sqlDelete = "DELETE FROM tramites_por_completar WHERE cod_tramite = ?";
    $stmtDel = $mysqli->prepare($sqlDelete);
    $stmtDel->bind_param("s", $id);
    $stmtDel->execute();
    $stmtDel->close();

    $_SESSION['tramite_msg'] = "success:Trámite completado. Nuevo código: $nuevo_cod_tramite";
    header('Location: ' . $redirectBase);
    exit;
} else {
    $_SESSION['tramite_msg'] = 'error:Error al completar el trámite: ' . $stmtInsert->error;
    header('Location: ' . $redirectBase . '&action=completar&cod=' . urlencode($id));
}

$mysqli->close();
?>
