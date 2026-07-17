<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../conexion.php';
if (!isset($mysqli) || $mysqli === null) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error: no se encontró la conexión \$mysqli. Revisa conexion.php"
    ]);
    exit;
}
$est_cedula = $_GET['con_num_identidad'] ?? '';

function normalizarRutaDoc($ruta)
{
    $ruta = trim((string)$ruta);
    if ($ruta === '') return '';
    if (strpos($ruta, '/vistas/Personal/') !== false) {
        return $ruta;
    }
    $old = '/arbimaps/Arbimaps/';
    $new = '/arbimaps/Arbimaps/vistas/Personal/';

    if (strpos($ruta, $old) === 0) {
        $resto = substr($ruta, strlen($old));
        return $new . ltrim($resto, '/');
    }
    if (strpos($ruta, 'Arbitrium_estudios/') === 0) {
        return $new . ltrim($ruta, '/');
    }
    return $ruta;
}
if (empty($est_cedula)) {
    echo "Cédula no proporcionada.";
    exit;
}
$sql = "SELECT 
            est_fechaGra, 
            est_nom_estudio, 
            est_diploma, 
            est_Cestudios, 
            est_escolaridad
        FROM estudios
        WHERE est_cedula = ?";
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    echo "Error al preparar la consulta: " . htmlspecialchars($mysqli->error);
    exit;
}
$stmt->bind_param("s", $est_cedula);
$stmt->execute();
$resultado = $stmt->get_result();
if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        echo "<p><strong>Fecha de Grado:</strong> " . htmlspecialchars($fila['est_fechaGra']) . "</p>";
        echo "<p><strong>Nombre del Título:</strong> " . htmlspecialchars($fila['est_nom_estudio']) . "</p>";
        echo "<p><strong>Escolaridad:</strong> " . htmlspecialchars($fila['est_escolaridad']) . "</p>";

        $archivoDiploma     = normalizarRutaDoc($fila['est_diploma']);
        $extensionDiploma   = strtolower(pathinfo($archivoDiploma, PATHINFO_EXTENSION));

        echo "<p><strong>Diploma:</strong></p>";
        if ($extensionDiploma === 'pdf') {
            echo "<embed src='" . htmlspecialchars($archivoDiploma) . "' width='100%' height='600px' />";
        } else {
            echo "<p><a href='" . htmlspecialchars($archivoDiploma) . "' target='_blank'>Ver archivo de diploma</a></p>";
        }
        $archivoCertificado     = normalizarRutaDoc($fila['est_Cestudios']);
        $extensionCertificado   = strtolower(pathinfo($archivoCertificado, PATHINFO_EXTENSION));

        echo "<p><strong>Certificado de Estudios:</strong></p>";
        if ($extensionCertificado === 'pdf') {
            echo "<embed src='" . htmlspecialchars($archivoCertificado) . "' width='100%' height='600px' />";
        } else {
            echo "<p><a href='" . htmlspecialchars($archivoCertificado) . "' target='_blank'>Ver certificado de estudios</a></p>";
        }
    }
} else {
    echo "<p>No hay documentos registrados para esta cédula.</p>";
}
$stmt->close();
$mysqli->close();
