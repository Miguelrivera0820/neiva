<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('GET');
neiva_require_permission('menu.tramites', $PERMISOS);

if (isset($_GET['cod'])) {
    $codigo = trim($_GET['cod']);
    
    // Buscar notificación en la base de datos
    $sql = "SELECT notificacion FROM no_procede_completar WHERE cod_radicacion_tramite = ? AND notificacion IS NOT NULL";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $codigo);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($notificacion);
    
    if ($stmt->fetch()) {
        // Configurar headers para PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="notificacion_' . $codigo . '.pdf"');
        header('Content-Length: ' . strlen($notificacion));
        
        // Output del PDF
        echo $notificacion;
    } else {
        http_response_code(404);
        echo "Notificación no encontrada";
    }
    
    $stmt->close();
    $mysqli->close();
    exit;
} else {
    http_response_code(400);
    echo "Código de trámite no especificado";
    exit;
}
?>
