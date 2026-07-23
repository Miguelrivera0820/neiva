<?php
require "../../../../conexion.php";

function vh_web_base(): string
{
    $scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $parts = explode('/', trim($scriptPath, '/'));
    $appRoot = $parts[0] ?? 'neiva';
    return '/' . trim($appRoot, '/');
}

function vh_encode_path(string $path): string
{
    $path = str_replace('\\', '/', $path);
    $parts = array_map('rawurlencode', explode('/', $path));
    return implode('/', $parts);
}

function vh_document_url($path): string
{
    $path = trim((string)$path);
    if ($path === '') {
        return '#';
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    $path = str_replace('\\', '/', $path);
    $docRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\'));
    if ($docRoot !== '' && stripos($path, $docRoot) === 0) {
        $path = substr($path, strlen($docRoot));
    }

    $path = preg_replace('#^/?(?:(?:neiva|Arbimaps)/)+#i', '', $path);
    return vh_web_base() . '/' . vh_encode_path(ltrim($path, '/'));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rol = $_POST['rol'] ?? '';
    $cod_tramite = $_POST['cod_tramite'] ?? '';
    $cedula_usuario = '';
    $rol_asignador = $rol;

    if (empty($cod_tramite)) {
        echo "<h5 class='text-danger'>No se recibió el código del trámite.</h5>";
        exit;
    }

    echo "<h5 class='mb-3 text-center text-white py-2 rounded-3' style='background-color:#002F55'>Historial del trámite</h5>";

    // Mostrar historial de asignación
    $stmt = $mysqli->prepare("SELECT * FROM asignacion_tramite WHERE asignacion_cod_tramite = ? AND creacion_tram_rol_usuario = ? ORDER BY asignacion_fecha_tramite DESC LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("ss", $cod_tramite, $rol);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $row = $resultado->fetch_assoc();

            echo "<div class='mb-3'>";
            echo "<p><strong>Fecha de asignación:</strong> " . $row['asignacion_fecha_tramite'] . "</p>";
            echo "<p><strong>Fecha límite a Respuesta:</strong> " . $row['fecha_limite'] . "</p>";
            echo "<p><strong>Estado:</strong> " . $row['asignacion_estado_tramite'] . "</p>";
            echo "<p><strong>Asignado por:</strong> " . $row['creacion_tram_nombre_usuario'] . " " . $row['creacion_tram_apellido_usuario'] . " (" . $row['creacion_tram_rol_usuario'] . ")</p>";
            echo "<p><strong>Observación:</strong> " . $row['observacion_a_usuario_tramite'] . "</p>";
            echo "</div>";

            // Guardar datos necesarios para la consulta de documentos
            $cedula_usuario = $row['asignacion_cc_usuario'];
            $rol_asignador = $row['creacion_tram_rol_usuario'];
        } else {
            echo "<p style='color:#022F55'>No se encontraron datos para este trámite y rol.</p>";
        }

        $stmt->close();
    } else {
        echo "<p style='color:#022F55'>Error en prepare: " . $mysqli->error . "</p>";
    }

    // Mostrar documentos aportados
    $stmt2 = $mysqli->prepare("SELECT * FROM documentos_tram_asignacion WHERE cod_tramite = ? AND doc_cedula_usuario = ? ORDER BY fecha_cargue_doc DESC LIMIT 1");
    if ($stmt2) {
        $stmt2->bind_param("ss", $cod_tramite, $cedula_usuario);
        $stmt2->execute();
        $resultado2 = $stmt2->get_result();

        if ($resultado2->num_rows > 0) {
            $doc = $resultado2->fetch_assoc();

            echo "<div class='mb-3'>";
            echo "<br>";
            echo "<h6>Tramite y Documentos aportados por: <strong>$rol_asignador</strong>:</h6>";
            echo "<br>";
            echo "<br>";
            echo "<table class='table table-bordered table-sm'>";
            echo "<thead class='table-warning my-2 text-center'><tr>
             <th style='background-color:#002F55;color:#FFFFFF'>Tipo de Documento</th>
             <th style='background-color:#002F55;color:#FFFFFF'>Archivo</th></tr>
                </thead>";
            echo "<tbody class='text-center'>";

            for ($i = 1; $i <= 5; $i++) {
                $campoNombre = ($i == 5) ? "nombre_doc5" : "nombre_doc$i";
                $campoTipo = "tipo_doc$i"; // <-- columna en BD que guarda el tipo de documento

                $nombreDoc = $doc[$campoNombre] ?? null;
                $tipoDoc = $doc[$campoTipo] ?? "Sin tipo";

                if (!empty($nombreDoc)) {
                    $urlDoc = vh_document_url($nombreDoc);
                    echo "<tr>";
                    echo '<td style="text-align: center; vertical-align: middle;padding:11px">' . htmlspecialchars($tipoDoc) . "</td>";
                    echo "<td style='text-align: center; vertical-align: middle;padding:11px'><a href='" . htmlspecialchars($urlDoc, ENT_QUOTES, 'UTF-8') . "' target='_blank' class='btn btn-sm text-white' style='background-color:#002F55'><b>Ver Documento</b></a></td>";
                    echo "</tr>";
                }
            }

            echo "</tbody></table>";
            echo "</div>";
        } else {
            echo "<p style='color:#022F55'>No se encontraron documentos aportados por este rol.</p>";
        }
        $stmt2->close();
    }
}
?>
