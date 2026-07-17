<?php
// require "../../conexion.php";
// // session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['cedula_usuario'])) {
    header("Location: ../../index.php");
    exit();
}

// Obtener el rol del usuario
$rol_usuario = $_SESSION['rol_usuario'] ?? '';

// Obtener el código del trámite desde la URL
if (!isset($_GET['cod'])) {
    die("Código de trámite no especificado.");
}
$cod_tramite = $_GET['cod'];

// Consulta para obtener los datos del trámite desde la tabla entrega_asignacion
$sql = "SELECT ea.entrega_cod_tramite,
               ea.historial_fecha_tramite,
               ea.creacion_tram_cc_usuario,
               ea.creacion_tram_nombre_usuario,
               ea.creacion_tram_apellido_usuario,
               ea.creacion_tram_rol_usuario,
               ea.quien_entrego_cc,
               ea.quien_entrego_nombre,
               ea.quien_entrego_apellido,
               ea.quien_entrego_rol,
               tr.fecha_rad,
               tr.npn_predio,
               tr.mutacion_tramite,
               tr.cod_tramite
        FROM entrega_asignacion ea
        INNER JOIN tramite_radicacion tr ON ea.entrega_cod_tramite = tr.cod_tramite
        WHERE ea.entrega_cod_tramite = ?
        ORDER BY ea.id_entrega_asignacion DESC
        LIMIT 1";
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    die("Error en prepare: " . $mysqli->error);
}
$stmt->bind_param("s", $cod_tramite);
$stmt->execute();
$resultado = $stmt->get_result();
if ($resultado->num_rows === 0) {
    die("No se encontró el trámite especificado.");
}
$tramite = $resultado->fetch_assoc();

// Asignar valores a variables para facilitar su uso en el HTML
$creacion_tram_cc_usuario = $tramite['quien_entrego_cc'] ?: $tramite['creacion_tram_cc_usuario'];
$creacion_tram_nombre_usuario = $tramite['quien_entrego_nombre'] ?: $tramite['creacion_tram_nombre_usuario'];
$creacion_tram_apellido_usuario = $tramite['quien_entrego_apellido'] ?: $tramite['creacion_tram_apellido_usuario'];
$creacion_tram_rol_usuario = $tramite['quien_entrego_rol'] ?: $tramite['creacion_tram_rol_usuario'];

function rutaWebDocumentoResolucion($ruta)
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

    $posArchivos = stripos($ruta, 'archivos/');
    if ($posArchivos !== false) {
        $ruta = substr($ruta, $posArchivos);
        return implode('/', array_map('rawurlencode', explode('/', $ruta)));
    }

    $carpetasPublicas = ['tramites_conservacion/', 'resoluciones/'];
    foreach ($carpetasPublicas as $carpeta) {
        $pos = stripos($ruta, $carpeta);
        if ($pos !== false) {
            $ruta = substr($ruta, $pos);
            return '../' . implode('/', array_map('rawurlencode', explode('/', $ruta)));
        }
    }

    return htmlspecialchars($ruta, ENT_QUOTES, 'UTF-8');
}

function agregarDocumentoResolucion(&$documentos, &$vistos, $ruta, $tipo, $origen)
{
    $ruta = rutaWebDocumentoResolucion($ruta);
    if (empty($ruta) || isset($vistos[$ruta])) {
        return;
    }
    $vistos[$ruta] = true;
    $documentos[] = [
        'ruta' => $ruta,
        'tipo' => $tipo ?: 'Sin descripciÃ³n',
        'origen' => $origen
    ];
}

$documentos_adjuntos = [];
$documentos_vistos = [];

$sql_docs_revision = "SELECT doc1, doc2, doc3, doc4, doc5,
                             tipo_doc1, tipo_doc2, tipo_doc3, tipo_doc4, tipo_doc5
                      FROM doc_entrega_asignacion
                      WHERE cod_tramite = ?
                        AND (doc1 IS NOT NULL OR doc2 IS NOT NULL OR doc3 IS NOT NULL OR doc4 IS NOT NULL OR doc5 IS NOT NULL)
                      ORDER BY id_doc_entrega DESC
                      LIMIT 1";
$stmt_docs_revision = $mysqli->prepare($sql_docs_revision);
if ($stmt_docs_revision) {
    $stmt_docs_revision->bind_param("s", $cod_tramite);
    $stmt_docs_revision->execute();
    $res_docs_revision = $stmt_docs_revision->get_result();
    if ($row_doc = $res_docs_revision->fetch_assoc()) {
        for ($i = 1; $i <= 5; $i++) {
            agregarDocumentoResolucion(
                $documentos_adjuntos,
                $documentos_vistos,
                $row_doc["doc$i"] ?? null,
                $row_doc["tipo_doc$i"] ?? null,
                'Revision'
            );
        }
    }
    $stmt_docs_revision->close();
}

$sql_docs_asignacion = "SELECT nombre_doc1, nombre_doc2, nombre_doc3, nombre_doc4, nombre_doc5,
                               tipo_doc1, tipo_doc2, tipo_doc3, tipo_doc4, tipo_doc5
                        FROM documentos_tram_asignacion
                        WHERE cod_tramite = ?
                        ORDER BY fecha_cargue_doc DESC";
$stmt_docs_asignacion = $mysqli->prepare($sql_docs_asignacion);
if (false && $stmt_docs_asignacion) {
    $stmt_docs_asignacion->bind_param("s", $cod_tramite);
    $stmt_docs_asignacion->execute();
    $res_docs_asignacion = $stmt_docs_asignacion->get_result();
    while ($row_doc = $res_docs_asignacion->fetch_assoc()) {
        for ($i = 1; $i <= 5; $i++) {
            agregarDocumentoResolucion(
                $documentos_adjuntos,
                $documentos_vistos,
                $row_doc["nombre_doc$i"] ?? null,
                $row_doc["tipo_doc$i"] ?? null,
                'AsignaciÃ³n'
            );
        }
    }
    $stmt_docs_asignacion->close();
}

$id_entrega = null;
$documento_resolucion = null;
$ruta_resolucion = null;
$notificacion = null;
$ruta_notificacion = null;
$message = '';

function anioDesdeRadicadoResolucion($cod_tramite)
{
    if (preg_match('/CAT-(\d{4})-/i', $cod_tramite, $matches)) {
        return $matches[1];
    }

    return date('Y');
}

function rutaFisicaExpedienteResolucion($ruta_relativa)
{
    $ruta_relativa = ltrim(str_replace('\\', '/', (string)$ruta_relativa), '/');
    return dirname(__DIR__, 3) . '/' . $ruta_relativa;
}

function asegurarPdfDirectorEnExpediente($mysqli, $id_resolucion, $cod_tramite, $pdf_data, $ruta_actual)
{
    $ruta_actual = ltrim(str_replace('\\', '/', (string)$ruta_actual), '/');
    $anio = anioDesdeRadicadoResolucion($cod_tramite);
    $directorio_relativo = "tramites_conservacion/{$anio}/{$cod_tramite}/tramites_revision/director_catastro";

    if (
        $ruta_actual !== ''
        && strpos($ruta_actual, $directorio_relativo . '/') === 0
        && file_exists(rutaFisicaExpedienteResolucion($ruta_actual))
    ) {
        return $ruta_actual;
    }

    if (empty($pdf_data) || (int)$id_resolucion <= 0) {
        return $ruta_actual;
    }

    $directorio_fisico = rutaFisicaExpedienteResolucion($directorio_relativo);
    if (!is_dir($directorio_fisico) && !mkdir($directorio_fisico, 0777, true)) {
        return $ruta_actual;
    }

    $nombre_archivo = 'resolucion_director_' . date('Ymd_His') . '.pdf';
    $ruta_nueva = $directorio_relativo . '/' . $nombre_archivo;
    $ruta_fisica = rutaFisicaExpedienteResolucion($ruta_nueva);

    if (file_put_contents($ruta_fisica, $pdf_data) === false) {
        return $ruta_actual;
    }

    $stmt_update = $mysqli->prepare("UPDATE resoluciones SET ruta_archivo = ? WHERE id_resoluciones = ?");
    if ($stmt_update) {
        $stmt_update->bind_param("si", $ruta_nueva, $id_resolucion);
        $stmt_update->execute();
        $stmt_update->close();
    }

    return $ruta_nueva;
}

function registrarCierreNotificacionFinal($mysqli, $cod_tramite, $nombre_notificacion)
{
    $fecha_cierre = date('Y-m-d H:i:s');

    $stmt_estado = $mysqli->prepare("INSERT INTO estados_tramite (
        es_nombre, es_tipo, es_descripcion, es_dias_disparador,
        es_rol_asociado, estado, asignacion_id, cod_tramite
    ) VALUES ('ENTREGADO', 'automatico', 'Notificacion final cargada por ventanilla', 0, 'ventanilla_catastral', 'ACTIVO', 0, ?)");
    if ($stmt_estado) {
        $stmt_estado->bind_param("s", $cod_tramite);
        $stmt_estado->execute();
        $stmt_estado->close();
    }

    $stmt_historial = $mysqli->prepare("UPDATE historial_revision
        SET est_ventanilla = 'ENTREGADO',
            fecha_ventanilla = ?,
            historial_estado_tramite = 'ENTREGADO',
            rol_actual = 'ventanilla_catastral'
        WHERE historial_cod_tramite = ?");
    if ($stmt_historial) {
        $stmt_historial->bind_param("ss", $fecha_cierre, $cod_tramite);
        $stmt_historial->execute();
        $stmt_historial->close();
    }

    $stmt_existe = $mysqli->prepare("SELECT id_procede FROM procede_tramite WHERE cod_radicacion_tramite = ? LIMIT 1");
    $id_procede = null;
    if ($stmt_existe) {
        $stmt_existe->bind_param("s", $cod_tramite);
        $stmt_existe->execute();
        $res_existe = $stmt_existe->get_result();
        if ($row_existe = $res_existe->fetch_assoc()) {
            $id_procede = $row_existe['id_procede'];
        }
        $stmt_existe->close();
    }

    if ($id_procede) {
        $stmt_update = $mysqli->prepare("UPDATE procede_tramite SET notificacion_procede_nombre = ? WHERE id_procede = ?");
        if ($stmt_update) {
            $stmt_update->bind_param("si", $nombre_notificacion, $id_procede);
            $stmt_update->execute();
            $stmt_update->close();
        }
        return;
    }

    $stmt_insert = $mysqli->prepare("INSERT INTO procede_tramite (
        cod_radicacion_tramite, fecha_rad_tramite, tipo_mutacion_tramite,
        actividad_tramite, fecha_resp_tramite, actividad_a_realizar,
        nombre_comp_interesado, telefono_interesado, correo_interesado,
        cod_catastro, fmi_predio, direccion_predio, propietarios_predio,
        tipo_oficio, cont_documento, notificacion_procede_nombre
    )
    SELECT
        r.cod_tramite,
        r.fecha_rad,
        r.mutacion_tramite,
        'CONSERVACION',
        COALESCE(NULLIF(r.fecha_limite_respuesta, ''), CURDATE()),
        'CIERRE POR VENTANILLA',
        TRIM(CONCAT_WS(' ', r.primer_nombre_interesado, r.segundo_nombre_interesado, r.primer_apellido_interesado, r.segundo_apellido_interesado)),
        r.telefono_interesado,
        r.correo_interesado,
        COALESCE(p.npn_predio_tram, r.npn_predio),
        r.fmi_predio,
        p.direccion_predio_terreno_tram,
        p.nombre_propietario_tram,
        'OFICIO',
        '',
        ?
    FROM tramite_radicacion r
    LEFT JOIN tramite_info_predio p ON p.info_cod_tramite = r.cod_tramite
    WHERE r.cod_tramite = ?
    LIMIT 1");
    if ($stmt_insert) {
        $stmt_insert->bind_param("ss", $nombre_notificacion, $cod_tramite);
        $stmt_insert->execute();
        $stmt_insert->close();
    }
}

function responderNotificacionFinal($response)
{
    $es_ajax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    if ($es_ajax) {
        echo json_encode($response);
        exit;
    }

    $redirect = $response['success']
        ? ($response['redirect'] ?? 'index.php?page=tramites/cuentas_completadas/tramites_completos')
        : 'javascript:history.back()';
    $mensaje = $response['message'] ?? '';
    echo "<script>
        alert(" . json_encode($mensaje) . ");
        window.location.href = " . json_encode($redirect) . ";
    </script>";
    exit;
}

if (!empty($cod_tramite)) {
    // Obtener el id_entrega_asignacion donde se creó la resolución (rol procedencia_juridica)
    $sql_entrega = "SELECT ea.id_entrega_asignacion FROM entrega_asignacion ea INNER JOIN resoluciones r ON ea.id_entrega_asignacion = r.id_entrega_asignacion WHERE ea.entrega_cod_tramite = ? AND ea.entrega_rol_usuario = 'procedencia_juridica' ORDER BY ea.id_entrega_asignacion DESC LIMIT 1";
    $stmt_entrega = $mysqli->prepare($sql_entrega);
    if ($stmt_entrega) {
        $stmt_entrega->bind_param("s", $cod_tramite);
        $stmt_entrega->execute();
        $result_entrega = $stmt_entrega->get_result();
        if ($row_entrega = $result_entrega->fetch_assoc()) {
            $id_entrega = $row_entrega['id_entrega_asignacion'];
            // Obtener el documento y ruta directamente desde la join o de resoluciones
            $sql_res = "SELECT id_resoluciones, resolucion_director, ruta_archivo, notificacion FROM resoluciones WHERE id_entrega_asignacion = ? LIMIT 1";
            $stmt_res = $mysqli->prepare($sql_res);
            if ($stmt_res) {
                $stmt_res->bind_param("i", $id_entrega);
                $stmt_res->execute();
                $result_res = $stmt_res->get_result();
                if ($row_res = $result_res->fetch_assoc()) {
                    $documento_resolucion = $row_res['resolucion_director'];
                    // Construir la ruta web completa utilizando el campo ruta_archivo (incluye subcarpeta)
                    $ruta_completa = asegurarPdfDirectorEnExpediente(
                        $mysqli,
                        (int)($row_res['id_resoluciones'] ?? 0),
                        $cod_tramite,
                        $row_res['resolucion_director'] ?? null,
                        $row_res['ruta_archivo'] ?? ''
                    );
                    $ruta_resolucion = $ruta_completa ? rutaWebDocumentoResolucion($ruta_completa) : null;
                    $notificacion = $row_res['notificacion'];
                    if ($notificacion) {
                        $ruta_notificacion = rutaWebDocumentoResolucion('archivos/notificaciones/' . $notificacion);
                    }
                }
                $stmt_res->close();
            }
        }
        $stmt_entrega->close();
    }
}

// ================= DOCUMENTO DIRECTOR =================

$doc_director = null;
$ruta_doc_director_web = null;
$ruta_doc_director_fisica = null;

// ---------------- BASE DE CARPETA SEGÚN TIPO DE RADICADO ----------------
$base_carpeta = '';

if (str_starts_with($cod_tramite, 'CAT-')) {
    // Ej: CAT-2025-10-00811 → 2025
    preg_match('/CAT-(\d{4})-/', $cod_tramite, $match);
    $base_carpeta = $match[1] ?? '';
} else {
    // Ej: ARB540 → 40
    $base_carpeta = substr(preg_replace('/[^0-9]/', '', $cod_tramite), -2);
}



// ---------------- CONSULTA DOCUMENTO DIRECTOR ----------------
$sql_doc_dir = "
    SELECT d.doc1
    FROM doc_entrega_asignacion d
    INNER JOIN usuarios_cons u 
        ON d.doc_cedula_usuario = u.cedula_usuario
    WHERE d.cod_tramite = ?
      AND u.rol_usuario = 'director_catastro'
      AND d.doc1 IS NOT NULL
    ORDER BY d.id_doc_entrega DESC
    LIMIT 1
";

$stmt_doc = $mysqli->prepare($sql_doc_dir);
if (!$stmt_doc) {
    die("❌ Error en prepare doc_director: " . $mysqli->error);
}

$stmt_doc->bind_param("s", $cod_tramite);
$stmt_doc->execute();
$res_doc = $stmt_doc->get_result();

if ($row = $res_doc->fetch_assoc()) {

    // 🔥 SOLO EL NOMBRE DEL ARCHIVO
    $doc_director = basename($row['doc1']);

    // RUTA FÍSICA
    $ruta_doc_director_fisica = rutaFisicaExpedienteResolucion(
        "tramites_conservacion/" .
        $base_carpeta . "/" .
        $cod_tramite . "/tramites_revision/director_catastro/" .
        $doc_director
    );

    // RUTA WEB
    $ruta_doc_director_web = rutaWebDocumentoResolucion(
        "tramites_conservacion/" .
        $base_carpeta . "/" .
        $cod_tramite . "/tramites_revision/director_catastro/" .
        $doc_director
    );
}

if (!empty($row['doc1'])) {
    $doc_registrado_original = ltrim(str_replace('\\', '/', (string)$row['doc1']), '/');
    $doc_original = basename($doc_registrado_original);
    $doc_registrado = ltrim(trim($doc_registrado_original), '/');
    $doc_registrado = preg_replace('#^\.\./#', '', $doc_registrado);
    $doc_director = basename($doc_registrado);

    if (strpos($doc_registrado, 'tramites_conservacion/') !== false) {
        $ruta_doc_director_relativa = substr($doc_registrado, strpos($doc_registrado, 'tramites_conservacion/'));
    } else {
        $ruta_doc_director_relativa =
            "tramites_conservacion/" .
            $base_carpeta . "/" .
            $cod_tramite . "/tramites_revision/director_catastro/" .
            $doc_director;
    }

    $ruta_doc_director_fisica = rutaFisicaExpedienteResolucion($ruta_doc_director_relativa);

    if (!file_exists($ruta_doc_director_fisica) && $doc_director !== '') {
        $carpeta_esperada = "tramites_conservacion/" .
            $base_carpeta . "/" .
            $cod_tramite . "/tramites_revision/director_catastro/";

        $candidatos = [
            rutaFisicaExpedienteResolucion($carpeta_esperada . $doc_director),
            rutaFisicaExpedienteResolucion($carpeta_esperada . $doc_original),
            __DIR__ . "/" . $carpeta_esperada . $doc_director,
            __DIR__ . "/" . $carpeta_esperada . $doc_original,
        ];

        foreach ($candidatos as $ruta_legacy) {
            if (!file_exists($ruta_legacy)) {
                continue;
            }
            $directorio_destino = dirname($ruta_doc_director_fisica);
            if (!is_dir($directorio_destino)) {
                mkdir($directorio_destino, 0777, true);
            }
            copy($ruta_legacy, $ruta_doc_director_fisica);
            break;
        }
    }

    $ruta_doc_director_web = rutaWebDocumentoResolucion($ruta_doc_director_relativa);
}

$stmt_doc->close();

// Manejar la subida de notificación
if ($_POST && isset($_POST['upload_notificacion']) && in_array($rol_usuario, ['ventanilla_catastral', 'administrador'], true)) {
    $cod_tramite_post = $_POST['cod_tramite_hidden'];
    // Refetchear id_entrega para el POST
    $sql_entrega_post = "SELECT ea.id_entrega_asignacion FROM entrega_asignacion ea INNER JOIN resoluciones r ON ea.id_entrega_asignacion = r.id_entrega_asignacion WHERE ea.entrega_cod_tramite = ? AND ea.entrega_rol_usuario = 'procedencia_juridica' ORDER BY ea.id_entrega_asignacion DESC LIMIT 1";
    $stmt_entrega_post = $mysqli->prepare($sql_entrega_post);
    $response = ['success' => false, 'message' => ''];
    if ($stmt_entrega_post) {
        $stmt_entrega_post->bind_param("s", $cod_tramite_post);
        $stmt_entrega_post->execute();
        $result_entrega_post = $stmt_entrega_post->get_result();
        if ($row_entrega_post = $result_entrega_post->fetch_assoc()) {
            $id_entrega_post = $row_entrega_post['id_entrega_asignacion'];
            if (isset($_FILES['notificacion_file']) && $_FILES['notificacion_file']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = dirname(__DIR__, 3) . '/archivos/notificaciones/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                // Obtener el nombre de notificacion actual para poder eliminarlo si se reemplaza
                $old_notificacion = null;
                $sql_old = "SELECT notificacion FROM resoluciones WHERE id_entrega_asignacion = ? LIMIT 1";
                $stmt_old = $mysqli->prepare($sql_old);
                if ($stmt_old) {
                    $stmt_old->bind_param("i", $id_entrega_post);
                    $stmt_old->execute();
                    $res_old = $stmt_old->get_result();
                    if ($row_old = $res_old->fetch_assoc()) {
                        $old_notificacion = $row_old['notificacion'];
                    }
                    $stmt_old->close();
                }

                $file_extension = strtolower(pathinfo($_FILES['notificacion_file']['name'], PATHINFO_EXTENSION));
                $extensiones_permitidas = ['pdf'];
                if (!in_array($file_extension, $extensiones_permitidas, true)) {
                    $response['message'] = "Solo se permiten archivos PDF.";
                    responderNotificacionFinal($response);
                }

                $cod_seguro = preg_replace('/[^A-Za-z0-9_-]/', '_', $cod_tramite_post);
                $new_filename = $cod_seguro . '_recibido_notificacion_' . date('Ymd_His') . '.pdf';
                $target_file = $upload_dir . $new_filename;

                if (move_uploaded_file($_FILES['notificacion_file']['tmp_name'], $target_file)) {
                    $sql_update = "UPDATE resoluciones SET notificacion = ? WHERE id_entrega_asignacion = ?";
                    $stmt_update = $mysqli->prepare($sql_update);
                    if ($stmt_update) {
                        $stmt_update->bind_param("si", $new_filename, $id_entrega_post);
                        if ($stmt_update->execute()) {
                            $response['success'] = true;
                            $response['filename'] = $new_filename;
                            $response['message'] = "Recibido de notificacion cargado y tramite cerrado correctamente.";
                            $response['redirect'] = "index.php?page=tramites/cuentas_completadas/tramites_completos";
                            registrarCierreNotificacionFinal($mysqli, $cod_tramite_post, $new_filename);

                            // Si existía un archivo anterior y el nombre es distinto, eliminarlo
                            if ($old_notificacion && $old_notificacion !== $new_filename) {
                                $old_path = $upload_dir . $old_notificacion;
                                if (file_exists($old_path)) {
                                    @unlink($old_path);
                                }
                            }
                        } else {
                            $response['message'] = "Error al actualizar la base de datos: " . $mysqli->error;
                        }
                        $stmt_update->close();
                    }
                } else {
                    $response['message'] = "Error al mover el archivo subido.";
                }
            } else {
                $response['message'] = "Error en la subida del archivo: " . ($_FILES['notificacion_file']['error'] ?? 'No file uploaded');
            }
        } else {
            $response['message'] = "No se encontró el registro correspondiente para este trámite.";
        }
        $stmt_entrega_post->close();
    }


    responderNotificacionFinal($response);
}

if ($_POST && isset($_POST['upload_notificacion'])) {
    responderNotificacionFinal([
        'success' => false,
        'message' => 'El recibido de notificacion final solo puede cargarlo ventanilla catastral o administrador.'
    ]);
}

?>
<style>
    .btn-orange {
        background-color: #ff9800;
        color: #000;
        border: none;
    }

    .btn-orange:hover {
        background-color: #fb8c00;
        color: #000;
    }

    .btn-peach {
        background-color: #ffd8a9;
        color: #000;
        border: 1px solid #fcbf8a;
    }

    .btn-peach:hover {
        background-color: #ffc48f;
        color: #000;
    }

    .btn-coral {
        background-color: #fca79e;
        color: #000;
        border: none;
    }

    .btn-coral:hover {
        background-color: #f88f86;
    }

    .btn-mint {
        background-color: #b9fbc0;
        color: #000;
        border: 1px solid #a9ebaf;
    }

    .btn-mint:hover {
        background-color: #a1e3a8;
    }

    .btn-lila {
        background-color: #d8b4fe;
        color: #2e2e2e;
        border: 1px solid #c59bfa;
    }

    .btn-lila:hover {
        background-color: #c59bfa;
    }

    .btn-pastel-blue {
        background-color: #b5ead7;
        color: #000;
        border: 1px solid #9fdcc3;
    }

    .btn-pastel-yellow {
        background-color: #fff5ba;
        color: #000;
        border: 1px solid #f7e49c;
    }

    .btn-pastel-pink {
        background-color: #ffd6e0;
        color: #000;
        border: 1px solid #f7b6c2;
    }

    .btn-pastel-orange {
        background-color: #ffe5b4;
        color: #000;
        border: 1px solid #ffd194;
    }

    .btn-pastel-redn {
        background-color: #ffb3b3;
        color: #000;
        border: 1px solid #e89a9a;
    }

    .btn-pastel-azul {
        background-color: #b3d8ff;
        color: #000;
        border: 1px solid #8bbbe8;
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

    .save-notification {
        background-color: #b3d8ff;
        border-radius: 20px;
        width: 50%;
        color: #353434ff;
    }
</style>


<!-- CONTENIDO PAGINA MODIFICACION -->
<div class="container-fluid">
    <!-- Page Heading -->
    <!-- <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 fw-bold my-4">RESOLUCIÓN DE TRÁMITES</h1>
        <a href="asignacion_tramites.php?cod=<?php echo urlencode($cod_tramite); ?>" class="btn btn-primary btn-md shadow-sm animated-button">
            <i class="fas fa-clipboard mr-2 icon-pulse"></i> Volver a Asignación
        </a>
    </div> -->

    <div class="d-sm-flex align-items-center justify-content-between mb-2 px-4">
        <h1 class="h3 m-0 fw-bold my-4">RESOLUCIÓN DE TRÁMITES</h1>

        <a href="index.php?page=tramites/asignacion_tramites&cod=<?php echo urlencode($tramite['cod_tramite']); ?>"
            class="btn  btn-md shadow-lg animated-button fw-bold btn-ver"
            style="border-left:2px solid #022F55;border-right:2px solid #022F55;">
            <i class="bi bi-eye me-2 fw-bold "></i> Ver trámites asignados
        </a>
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
                    <!-- <h4 class="text-primary text-center mb-3"><B>Quien me paso</B></h4> -->
                    <div class="form-group  ">
                        <!-- CAMBIAR EL MODO DE ID, DESACTIVAR PORQUE DEBE SER AUTOMATICO -->
                        <div class="form-row ">

                            <!-- <div class="col-md-6">
                                <label for="cod_tramite"><b>ID_Radicacion</b></label>
                                <input class="form-control py-4" id="cod_tramite" name="entrega_cod_tramite" type="text" value="<?php echo htmlspecialchars($tramite['cod_tramite']); ?>" readonly>
                            </div> -->

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label for="cod_tramite" class="form-label fw-bold" style="font-size:0.9em;">ID de radicación</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-file-earmark-binary"></i></span>
                                    <input type="text" class="form-control" style="font-size:0.9em;" id="cod_tramite"
                                        name="entrega_cod_tramite"
                                        value="<?php echo htmlspecialchars($tramite['cod_tramite']); ?>" readonly>
                                </div>
                            </div>

                            <!-- <div class="col-md-6">
                                <label for="fecha_rad"><b>Hora Radicación del Trámite</b></label>
                                <input class="form-control py-4" id="fecha_rad" name="historial_fecha_tramite" type="text" value="<?php echo htmlspecialchars($tramite['fecha_rad']); ?>" readonly>
                            </div> -->

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label for="fecha_rad" class="form-label fw-bold" style="font-size:0.9em;">Hora radicación del trámite</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                                    <input type="text" class="form-control" style="font-size:0.9em;" id="fecha_rad"
                                        name="historial_fecha_tramite"
                                        value="<?php echo htmlspecialchars($tramite['fecha_rad']); ?>" readonly>
                                </div>
                            </div>

                            <!-- <div class="col-md-6">
                                <label for="nombre_usuario"><b>Cedula del Usuario</b></label>
                                <input class="form-control" id="creacion_tram_cc_usuario" name="creacion_tram_cc_usuario" type="text" value="<?php echo $creacion_tram_cc_usuario; ?>" readonly>
                            </div> -->

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label for="creacion_tram_cc_usuario" class="form-label fw-bold" style="font-size:0.9em">Cédula del usuario</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                                    <input type="text" class="form-control" style="font-size: 0.9em;" id="creacion_tram_cc_usuario" name="creacion_tram_cc_usuario"
                                        value="<?php echo $creacion_tram_cc_usuario; ?>" readonly>
                                </div>
                            </div>

                            <!-- <div class="col-md-6">
                                <label for="nombre_usuario"><b>Nombres del Usuario</b></label>
                                <input class="form-control" id="creacion_tram_nombre_usuario" name="creacion_tram_nombre_usuario" type="text" value="<?php echo $creacion_tram_nombre_usuario; ?>" readonly>
                            </div> -->

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label for="creacion_tram_nombre_usuario" class="form-label fw-bold" style="font-size:0.9em">Nombres del usuario</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                    <input type="text" class="form-control" style="font-size: 0.9em;" id="creacion_tram_nombre_usuario" name="creacion_tram_nombre_usuario"
                                        value="<?php echo $creacion_tram_nombre_usuario; ?>" readonly>
                                </div>
                            </div>

                            <!-- <div class="col-md-6">
                                <label for="apellido_usuario"><b>Apellidos del Usuario</b></label>
                                <input class="form-control" id="creacion_tram_apellido_usuario" name="creacion_tram_apellido_usuario" type="text" value="<?php echo $creacion_tram_apellido_usuario; ?>" readonly>
                            </div> -->

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label for="creacion_tram_apellido_usuario" class="form-label fw-bold" style="font-size:0.9em">Apellidos del usuario</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi-people-fill"></i></span>
                                    <input type="text" class="form-control" style="font-size: 0.9em;" id="creacion_tram_apellido_usuario" name="creacion_tram_apellido_usuario"
                                        value="<?php echo $creacion_tram_apellido_usuario; ?>" readonly>
                                </div>
                            </div>

                            <!-- <div class="col-md-6">
                                <label for="nombre_usuario"><b>Cargo</b></label>
                                <input class="form-control" id="creacion_tram_rol_usuario" name="creacion_tram_rol_usuario" type="text" value="<?php echo $creacion_tram_rol_usuario; ?>" readonly>
                            </div> -->

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label for="creacion_tram_rol_usuario" class="form-label fw-bold" style="font-size:0.9em">Cargo</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-person-gear"></i></span>
                                    <input type="text" class="form-control" style="font-size: 0.9em;" id="creacion_tram_rol_usuario" name="creacion_tram_rol_usuario"
                                        value="<?php echo $creacion_tram_rol_usuario; ?>" readonly>
                                </div>
                            </div>

                            <!-- <div class="col-md-6">
                                <label for="npn_predio"><b>NPN Predio</b></label>
                                <input class="form-control" id="npn_predio" name="npn_predio" type="text" value="<?php echo htmlspecialchars($tramite['npn_predio']); ?>" readonly>
                            </div> -->

                            <div class="col-md-6 p-1 px-2 my-1">
                                <label for="npn_predio" class="form-label fw-bold" style="font-size:0.9em;">NPN predio</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-map"></i></span>
                                    <input type="text" class="form-control" style="font-size:0.9em;" id="npn_predio"
                                        name="npn_predio" aria-label="PrimerNombre" value="<?php echo htmlspecialchars($tramite['npn_predio'] ?? ''); ?>" readonly>
                                    <a class="bot_mostrar_vista btn" style="font-size:0.9em" type="button" id="button-addon2"
                                        href="../neiva_visor_dos/index.html?valor=<?php echo htmlspecialchars($tramite['npn_predio'] ?? ''); ?>" target="_blank">
                                        <i class="bi bi-globe-americas me-1"></i> Ver en Visor Cat.</a>
                                </div>
                            </div>
                            <!-- <div class="col-md-6">
                                <label for="mutacion_tramite"><b>Tipo de Trámite</b></label>
                                <input class="form-control" id="mutacion_tramite" name="mutacion_tramite" type="text" value="<?php echo htmlspecialchars($tramite['mutacion_tramite']); ?>" readonly>
                            </div> -->

                            <div class="col-md-6 p-1 px-2 my-1">
                                <label for="mutacion_tramite" class="form-label fw-bold" style="font-size:0.9em">Tipo de trámite</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-file-earmark-richtext-fill"></i></span>
                                    <input type="text" class="form-control" style="font-size: 0.9em;" id="mutacion_tramite" name="mutacion_tramite"
                                        value="<?php echo htmlspecialchars($tramite['mutacion_tramite']); ?>" readonly>
                                </div>
                            </div>

                        </div>


                            <?php if ($ruta_resolucion): ?>
                                <div class="row">

                                    <!-- <div class="card p-2 my-2 mt-3" id="resolucion-card">
                                    <label><b>Resolución Asociada</b></label>
                                    <p class="mb-1">
                                        <b>Documento:</b> <?= htmlspecialchars($documento_resolucion) ?>
                                    </p>
                                    <div class="d-flex gap-2">
                                        <a href="<?= $ruta_resolucion ?>" target="_blank" class="btn btn-outline-info btn-sm"> Ver en otra pestaña </a>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleIframe('visor_resolucion')"> Mostrar/Ocultar Vista Previa </button>
                                    </div>
                                    <div id="visor_resolucion" style="display:none;">
                                        <iframe src="<?= $ruta_resolucion ?>" width="100%" height="600px" style="border:1px solid #ccc;"></iframe>
                                    </div>
                                 </div> -->

                                    <!-- <div class="col-md-12 p-1 px-2 my-2">
                                        <div class="row justify-content-center"></div>
                                    </div> -->

                                    <!-- obtener el nombre del documento -->
                                    <?php $nombre_resolucion = basename($ruta_resolucion ?? ''); ?>

                                    <div class="card card-documentos col-8 mx-auto shadow h-100 p-3 border d-flex flex-column text-center my-3">
                                        <label for="visor_resolucion" class="form-label fw-bold">OFICIO - SOLICITUD (NO ENTREGAR)</label>
                                        <div class="input-group shadow-sm mb-2">
                                            <span class="input-group-text"> <i class="bi bi-file-earmark-pdf-fill me-2"></i> <b>Documento:</b> </span>
                                            <input type="text" class="form-control" style="font-size: 0.9em;" id="visor_resolucion_" name="visor_resolucion"
                                                value="<?= htmlspecialchars($nombre_resolucion ?? 'sin información') ?> " readonly>
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


                                <?php endif; ?>

                                <?php if (!empty($documentos_adjuntos)): ?>
                                    <div class="row justify-content-center">
                                        <?php foreach ($documentos_adjuntos as $idx => $documento_adjunto): ?>
                                            <?php $visor_adjunto = 'visor_adjunto_' . $idx; ?>
                                            <div class="card card-documentos resolucion-adjunto-card col-5 mx-2 shadow p-3 border d-flex flex-column text-center my-3">
                                                <label class="form-label fw-bold">Documento Adjunto</label>
                                                <div class="input-group shadow-sm mb-2">
                                                    <span class="input-group-text">
                                                        <i class="bi bi-file-earmark-pdf-fill me-2"></i>
                                                        <b><?= htmlspecialchars($documento_adjunto['origen']) ?>:</b>
                                                    </span>
                                                    <input type="text"
                                                        class="form-control"
                                                        style="font-size: 0.9em;"
                                                        value="<?= htmlspecialchars($documento_adjunto['tipo']) ?>"
                                                        readonly>
                                                </div>

                                                <div class="d-flex justify-content-center gap-2 px-4 my-2">
                                                    <a href="<?= htmlspecialchars($documento_adjunto['ruta']) ?>" target="_blank" class="bot_verenotrapesta btn btn-sm">
                                                        <i class="bi bi-box-arrow-right"></i> Ver en otra pestana
                                                    </a>
                                                    <button type="button" class="bot_mostrar_vista btn btn-sm"
                                                        onclick="toggleIframe('<?= $visor_adjunto ?>',this)">
                                                        <i class="bi bi-eye"></i> <span>Mostrar Vista Previa</span>
                                                    </button>
                                                </div>

                                                <div id="<?= $visor_adjunto ?>" class="iframe-animado resolucion-visor-adjunto">
                                                    <iframe src="<?= htmlspecialchars($documento_adjunto['ruta']) ?>" width="100%" height="650px" style="border:1px solid #ccc;"></iframe>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($ruta_doc_director_web): ?>

                                    <div class="card card-documentos col-8 mx-auto shadow h-100 p-3 border d-flex flex-column text-center my-4">
                                        <label class="form-label fw-bold">RESPUESTA – DIRECTOR DE CATASTRO OFICIAL</label>

                                        <div class="input-group shadow-sm mb-2">
                                            <span class="input-group-text">
                                                <i class="bi bi-file-earmark-pdf-fill me-2"></i>
                                                <b>Documento:</b>
                                            </span>
                                            <input type="text"
                                                class="form-control"
                                                style="font-size: 0.9em;"
                                                value="<?= htmlspecialchars($doc_director) ?>"
                                                readonly>
                                        </div>

                                        <div class="d-flex justify-content-center gap-2 px-4 my-2">
                                            <a href="<?= $ruta_doc_director_web ?>" target="_blank"
                                                class="bot_verenotrapesta btn btn-sm">
                                                <i class="bi bi-box-arrow-right"></i> Ver en otra pestaña
                                            </a>

                                            <button type="button"
                                                class="bot_mostrar_vista btn btn-sm"
                                                onclick="toggleIframe('visor_doc_director', this)">
                                                <i class="bi bi-eye"></i>
                                                <span>Mostrar Vista Previa</span>
                                            </button>
                                        </div>

                                        <div id="visor_doc_director" class="iframe-animado">
                                            <iframe
                                                src="<?= $ruta_doc_director_web ?>"
                                                width="100%"
                                                height="750px"
                                                style="border:1px solid #ccc;">
                                            </iframe>
                                        </div>
                                    </div>

                                <?php endif; ?>


                                <div id="message-container"></div>

                                <?php if ($notificacion): ?>

                                    <!-- <div class="card p-2 my-2 mt-3" id="notificacion-card">
                                    <label><b>Notificación de Recibido</b></label>
                                    <p class="mb-1">
                                        <b>Documento:</b> <?= htmlspecialchars($notificacion) ?>
                                    </p>
                                    <div class="d-flex gap-2">
                                        <a href="<?= $ruta_notificacion ?>" target="_blank" class="btn btn-outline-info btn-sm"> Ver en otra pestaña </a>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleIframe('visor_notificacion')"> Mostrar/Ocultar Vista Previa </button>
                                    </div>
                                    <div id="visor_notificacion" style="display:none;">
                                        <iframe src="<?= $ruta_notificacion ?>" width="100%" height="600px" style="border:1px solid #ccc;"></iframe>
                                    </div>
                                </div> -->

                                    <div class="card card-documentos col-8 mx-auto shadow h-100 p-3 border d-flex flex-column text-center my-4" id="notificacion-card">
                                        <label for="visor_resolucion" class="form-label fw-bold">Notificación de Recibido</label>
                                        <div class="input-group shadow-sm mb-2">
                                            <span class="input-group-text"> <i class="bi bi-file-earmark-pdf-fill me-2"></i> <b>Documento:</b> </span>
                                            <input type="text" class="form-control" style="font-size: 0.9em;" id="visor_resolucion" name="visor_resolucion"
                                                value="<?= htmlspecialchars($notificacion) ?> " readonly>
                                        </div>

                                        <div class="d-flex justify-content-center gap-2 px-4 my-2">
                                            <a href="<?= $ruta_notificacion ?>" target="_blank" class="bot_verenotrapesta btn btn-sm">
                                                <i class="bi bi-box-arrow-right"></i> Ver en otra pestaña
                                            </a>
                                            <button type="button" class="bot_mostrar_vista btn btn-sm"
                                                onclick="toggleIframe('visor_notificacion',this)">
                                                <i class="bi bi-eye"></i> <span>Mostrar Vista Previa</span>
                                            </button>
                                        </div>

                                        <div id="visor_notificacion" class="iframe-animado">
                                            <iframe src="<?= $ruta_notificacion ?>" width="100%" height="750px" style="border:1px solid #ccc;"></iframe>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (in_array($rol_usuario, ['ventanilla_catastral', 'administrador'], true) && empty($notificacion)): ?>
                                <div class="card card-documentos col-8 mx-auto shadow p-3 border d-flex flex-column text-center my-4" id="upload-section">
                                    <div class="save-title">
                                        <label class="form-label fw-bold">Subir recibido final de notificacion</label>
                                    </div>
                                    <div class="save-content">
                                        <form method="post" enctype="multipart/form-data" id="uploadForm">
                                            <input type="hidden" name="cod_tramite_hidden" value="<?= htmlspecialchars($cod_tramite) ?>">
                                            <input type="hidden" name="upload_notificacion" value="1">
                                            <div class="input-group">
                                                <label for="notificacion_file" class="input-group-text"><i class="bi bi-file-earmark-pdf-fill"></i></label>
                                                <input type="file" class="form-control" id="notificacion_file" name="notificacion_file" accept=".pdf,application/pdf" required>
                                            </div>
                                            <div class="form-text">Seleccionar recibido firmado por el usuario. Solo PDF.</div>
                                            <button type="submit" class="btn my-3 text-white" style="background-color: #002F55;"> <i class="bi bi-upload me-2"></i>
                                                <?php echo $notificacion ? 'Reemplazar recibido final' : 'Subir recibido final'; ?>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <?php endif; ?>

                                </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->
</div>
<!-- End of Main Content -->

<style>
    .resolucion-adjunto-card {
        height: auto !important;
        min-height: 0 !important;
        align-self: flex-start;
    }

    .resolucion-visor-adjunto {
        display: none;
        max-height: 0;
        overflow: hidden;
    }

    .resolucion-visor-adjunto.mostrar {
        display: block;
        max-height: 680px;
        margin-top: 12px;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="../../js/asig_rev.js"></script>
<script>
    // function toggleIframe(id) {
    //     const visor = document.getElementById(id);
    //     visor.style.display = (visor.style.display === 'none') ? 'block' : 'none';
    // }

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

    document.addEventListener('DOMContentLoaded', function() {
        const uploadForm = document.getElementById('uploadForm');
        if (uploadForm) {
            uploadForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(uploadForm);
                const submitBtn = uploadForm.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Subiendo...';
                submitBtn.disabled = true;

                fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => response.text())
                    .then(text => {
                        const inicioJson = text.lastIndexOf('{');
                        if (inicioJson === -1) {
                            throw new Error('El servidor no devolvio una respuesta valida.');
                        }
                        let data;
                        try {
                            data = JSON.parse(text.slice(inicioJson));
                        } catch (e) {
                            throw new Error('El servidor respondio HTML en lugar de confirmar la subida.');
                        }
                        if (!data.success) {
                            throw new Error(data.message || 'No se pudo subir la notificacion.');
                        }
                        Swal.fire({
                            icon: 'success',
                            title: 'Notificacion cargada',
                            text: data.message || 'El tramite quedo cerrado correctamente.',
                            confirmButtonColor: '#002F55'
                        }).then(() => {
                            window.location.href = data.redirect || 'index.php?page=tramites/cuentas_completadas/tramites_completos';
                        });
                        // Recargar la página inmediatamente al completar la subida para reflejar el nuevo archivo
                    })
                    .catch(error => {
                        // Mantener la alerta de error para informar al usuario
                        Swal.fire({
                            icon: 'error',
                            title: 'Error en la subida',
                            text: error.message,
                            confirmButtonColor: '#d33'
                        });
                    })
                    .finally(() => {
                        // En caso de que la recarga no ocurra (por ejemplo, error), restaurar el botón
                        submitBtn.textContent = originalText;
                        submitBtn.disabled = false;
                    });
            });
        }
    });
</script>


</body>

</html>
