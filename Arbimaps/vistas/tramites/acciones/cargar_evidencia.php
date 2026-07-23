<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods(['GET', 'POST']);
neiva_require_permission('menu.tramites', $PERMISOS);

function app_base_url(): string {
    return neiva_app_url('Arbimaps');
}

function no_procede_url(string $query = ''): string {
    return app_base_url() . '/index.php?page=tramites/cuentas_rechazadas/no_procede_completar' . $query;
}

function no_procede_storage_dir(string $codigo, string $subdir): string {
    $anio = substr($codigo, 4, 4);
    $baseRoot = realpath(__DIR__ . '/../tramites_conservacion');
    if ($baseRoot === false) {
        $baseRoot = __DIR__ . '/../tramites_conservacion';
    }
    return $baseRoot . "/no_procede_completar/$anio/$codigo/$subdir/";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    neiva_require_csrf('global');
    $codigo = trim($_POST['cod_tramite'] ?? '');

    if (empty($codigo)) {
        $_SESSION['firmado_msg'] = 'error:No se recibió el código del trámite.';
        header("Location: " . no_procede_url());
        exit;
    }

    // Validar archivo
    if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['firmado_msg'] = 'error:Archivo no subido correctamente.';
        header("Location: " . no_procede_url());
        exit;
    }

    // Estructura de carpetas
    $anio = substr($codigo, 4, 4);
    $baseDir = no_procede_storage_dir($codigo, 'evidencias');
    if (!file_exists($baseDir)) mkdir($baseDir, 0777, true);

    // Validar extensión
    $fileType = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
    $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
    if (!in_array($fileType, $allowed)) {
        $_SESSION['firmado_msg'] = 'error:Solo se permiten archivos PDF o imágenes (JPG, PNG).';
        header("Location: " . no_procede_url());
        exit;
    }

    // Nombre único del archivo
    $fileName = "evidencia_{$codigo}_" . date('Ymd_His') . "." . $fileType;
    $targetFile = $baseDir . $fileName;

    // Mover archivo
    if (!move_uploaded_file($_FILES["archivo"]["tmp_name"], $targetFile)) {
        $_SESSION['firmado_msg'] = 'error:Error al mover el archivo.';
        header("Location: " . no_procede_url());
        exit;
    }

    // Leer contenido del archivo
    $file_content = file_get_contents($targetFile);

    // Guardar o actualizar en la base de datos
    $sql_check = "SELECT id_completar FROM no_procede_completar WHERE cod_radicacion_tramite = ?";
    $stmt_check = $mysqli->prepare($sql_check);
    $stmt_check->bind_param("s", $codigo);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        // Actualizar campo evidencias
        $sql_update = "UPDATE no_procede_completar SET evidencias = ? WHERE cod_radicacion_tramite = ?";
        $stmt_update = $mysqli->prepare($sql_update);
        $null = NULL;
        $stmt_update->bind_param("bs", $null, $codigo);
        $stmt_update->send_long_data(0, $file_content);
        $stmt_update->execute();
        $stmt_update->close();
        $action_type = 'actualizada';
    } else {
        // Insertar nuevo registro
        $sql_insert = "INSERT INTO no_procede_completar (cod_radicacion_tramite, evidencias) VALUES (?, ?)";
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

    $_SESSION['firmado_msg'] = "success:Evidencia {$action_type} correctamente.";
    header("Location: " . no_procede_url());
    exit;
}

// envia el dicumento para la vista previa
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    $codigo = $_GET['cod_tramite'] ?? '';

    if (empty($codigo)) {
        echo json_encode(['success' => false, 'msg' => 'Código no recibido']);
        exit;
    }

    $anio = substr($codigo, 4, 4);
    $baseDir = no_procede_storage_dir($codigo, 'evidencias');

    $evidencias = [];

    if (file_exists($baseDir)) {
        $files = glob($baseDir . '*.{pdf,jpg,jpeg,png}', GLOB_BRACE);

        // Ordenar por fecha de modificación
        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        foreach ($files as $file) {
            $evidencias[] = [
                'name' => basename($file),
                'url' => app_base_url() . '/vistas/tramites/tramites_conservacion/no_procede_completar/' . $anio . '/' . $codigo . '/evidencias/' . rawurlencode(basename($file)),
                'date' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }
    }

    echo json_encode(['success' => true, 'evidencias' => $evidencias]);
    exit;
}
