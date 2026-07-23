<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('POST');
neiva_require_permission('menu.tramites', $PERMISOS);
neiva_require_csrf('global');

session_start(); // Agregar para manejar mensajes de sesión

function app_base_url(): string {
    return neiva_app_url('Arbimaps');
}

function redirect_no_procede_ver(string $codigo): void {
    header("Location: " . app_base_url() . "/index.php?page=tramites/cuentas_rechazadas/no_procede_completar&action=ver&cod=" . urlencode($codigo));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir código automáticamente desde el formulario
    $codigo = trim($_POST['cod_tramite'] ?? '');

    if (empty($codigo)) {
        $_SESSION['notification_msg'] = 'error:No se recibió el código del trámite.';
        redirect_no_procede_ver($codigo);
    }

    // Validar archivo
    if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['notification_msg'] = 'error:Archivo no subido correctamente.';
        redirect_no_procede_ver($codigo);
    }

    // Definir estructura de carpetas usando ruta absoluta
    $anio = substr($codigo, 4, 4);
    $baseRoot = realpath(__DIR__ . '/../tramites_conservacion');
    if ($baseRoot === false) {
        $baseRoot = __DIR__ . '/../tramites_conservacion';
    }
    $baseDir = $baseRoot . "/no_procede_completar/$anio/$codigo/notificaciones/";
    if (!file_exists($baseDir)) mkdir($baseDir, 0777, true);

    // Nombre único del archivo
    $fileName = "notificacion_{$codigo}_" . date('Ymd_His') . ".pdf";
    $targetFile = $baseDir . $fileName;

    // Validar tipo PDF
    $fileType = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
    if ($fileType !== 'pdf') {
        $_SESSION['notification_msg'] = 'error:Solo se permiten archivos PDF.';
        redirect_no_procede_ver($codigo);
    }

    // Mover archivo al destino
    if (!move_uploaded_file($_FILES["archivo"]["tmp_name"], $targetFile)) {
        $_SESSION['notification_msg'] = 'error:Error al mover el archivo.';
        redirect_no_procede_ver($codigo);
    }

    // Leer contenido del archivo
    $file_content = file_get_contents($targetFile);

    // Guardar en la base de datos
    $sql_check = "SELECT id_completar FROM no_procede_completar WHERE cod_radicacion_tramite = ?";
    $stmt_check = $mysqli->prepare($sql_check);
    $stmt_check->bind_param("s", $codigo);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    $action_type = '';
    if ($result->num_rows > 0) {
        // Si ya existe el trámite, actualiza la notificación
        $sql_update = "UPDATE no_procede_completar SET notificacion = ? WHERE cod_radicacion_tramite = ?";
        $stmt_update = $mysqli->prepare($sql_update);
        $null = NULL;
        $stmt_update->bind_param("bs", $null, $codigo);
        $stmt_update->send_long_data(0, $file_content);
        $stmt_update->execute();
        $stmt_update->close();
        $action_type = 'actualizada';
    } else {
        // Si no existe, inserta un nuevo registro con el código y la notificación
        $sql_insert = "INSERT INTO no_procede_completar (cod_radicacion_tramite, notificacion) VALUES (?, ?)";
        $stmt_insert = $mysqli->prepare($sql_insert);
        $null = NULL;
        $stmt_insert->bind_param("sb", $codigo, $null);
        $stmt_insert->send_long_data(1, $file_content);
        $stmt_insert->execute();
        $stmt_insert->close();
        $action_type = 'insertada';
    }

    $stmt_check->close();
    $mysqli->close();

    $_SESSION['notification_msg'] = "success:Notificación {$action_type} correctamente.";
    redirect_no_procede_ver($codigo);
}
?>
