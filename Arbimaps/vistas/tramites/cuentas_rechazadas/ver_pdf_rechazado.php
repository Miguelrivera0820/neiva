<?php
require "../../../../conexion.php";

// Validar parámetro
if (!isset($_GET['doc_cod'])) {
    http_response_code(400);
    echo "Código no proporcionado.";
    exit;
}

$cod = $_GET['doc_cod'];

// Seguridad básica
if (!is_string($cod) || strlen($cod) > 64) {
    http_response_code(400);
    echo "Código inválido.";
    exit;
}

// Consultar el documento en la base de datos
$stmt = $mysqli->prepare("SELECT documento_generado FROM rechazado_tramite WHERE cod_radicacion_tramite = ? LIMIT 1");
$stmt->bind_param("s", $cod);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($docBlob);

if ($stmt->num_rows > 0) {
    $stmt->fetch();
    if (!empty($docBlob)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="rechazado_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $cod) . '.pdf"');
        echo $docBlob;
        exit;
    }
}

// Si no se encontró el documento
http_response_code(404);
echo "Documento no encontrado.";
exit;
?>
