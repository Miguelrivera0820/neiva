<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../../../../conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    $back = $_SERVER['HTTP_REFERER'] ?? '/';
    $back .= (strpos($back, '?') === false ? '?' : '&') . 'msg=' . urlencode('Usuario no autenticado.');
    header('Location: ' . $back);
    exit;
}

$usuario_rechazo_id = $_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
    empty($_POST['id']) ||
    empty($_POST['numero_identidad']) ||
    empty($_POST['razon'])) {

    $back = $_SERVER['HTTP_REFERER'] ?? '/';
    $back .= (strpos($back, '?') === false ? '?' : '&') . 'msg=' . urlencode('Datos incompletos para rechazar la cuenta.');
    header('Location: ' . $back);
    exit;
}

$id               = intval($_POST['id']);
$numero_identidad = trim($_POST['numero_identidad']);
$razon            = trim($_POST['razon']);

if ($numero_identidad === '') {
    $back = $_SERVER['HTTP_REFERER'] ?? '/';
    $back .= (strpos($back, '?') === false ? '?' : '&') . 'msg=' . urlencode('Número de identidad inválido.');
    header('Location: ' . $back);
    exit;
}

$mysqli->begin_transaction();

$columnas = [
    "tipo_documento",
    "numero_identidad",
    "primer_nombre",
    "segundo_nombre",
    "primer_apellido",
    "segundo_apellido",
    "telefono",
    "correo",
    "cargo",
    "proyecto",
    "observacion",
    "informe_mensual",
    "cuenta_cobro",
    "seguridad_social",
    "retencion",
    "primera_vez",
    "creado_en",
    "usuario_id",
    "valor",
    "Periodo_Facturacion",
    "Fecha_Inicio",
    "Fecha_Final",
    "Val_Seg_Social",
    "estado",
    "estado_seguridad_social",
    "estado_final",
    "fecha_subida"
];

$columnas_extra = ["rec_anio_cuenta", "razon", "fecha_rechazo", "usuario_rechazo_id"];
$columnas_sql   = implode(", ", array_merge($columnas, $columnas_extra));

try {
    $sql_select  = "SELECT * FROM cuenta WHERE id = ? AND numero_identidad = ?";
    $stmt_select = $mysqli->prepare($sql_select);

    if (!$stmt_select) {
        throw new Exception('Error al preparar SELECT: ' . $mysqli->error);
    }

    $stmt_select->bind_param("is", $id, $numero_identidad);
    $stmt_select->execute();
    $result = $stmt_select->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('La cuenta no existe o los datos no coinciden.');
    }

    $cuenta = $result->fetch_assoc();
    $stmt_select->close();

    $valores = [];
    foreach ($columnas as $columna) {
        $valores[] = $cuenta[$columna] ?? null;
    }

    $valores[] = $cuenta['anio_cuenta'] ?? null;
    $valores[] = $razon;
    date_default_timezone_set('America/Bogota');
    $valores[] = date("Y-m-d H:i:s");
    $valores[] = $usuario_rechazo_id;

    $placeholders = implode(", ", array_fill(0, count($valores), "?"));
    $sql_insert   = "INSERT INTO cuentas_rechazadas ($columnas_sql) VALUES ($placeholders)";
    $stmt_insert  = $mysqli->prepare($sql_insert);

    if (!$stmt_insert) {
        throw new Exception('Error al preparar INSERT: ' . $mysqli->error);
    }

    $tipos = str_repeat("s", count($valores));
    $stmt_insert->bind_param($tipos, ...$valores);

    if (!$stmt_insert->execute()) {
        throw new Exception('Error al insertar en cuentas_rechazadas: ' . $stmt_insert->error);
    }

    $stmt_insert->close();

    $year_folder = (!empty($cuenta['anio_cuenta']) && is_numeric($cuenta['anio_cuenta'])) ? $cuenta['anio_cuenta'] : 'sin_anio';
    $periodo_folder = $cuenta['Periodo_Facturacion'] ?? ($Periodo_Facturacion ?? 'periodo');
    $identidad_folder = $cuenta['numero_identidad'] ?? $numero_identidad;

    $periodo_folder = trim($periodo_folder) === '' ? 'periodo' : trim($periodo_folder);
    $identidad_folder = trim($identidad_folder) === '' ? 'sin_identidad' : basename(trim($identidad_folder));

    $possible_base = realpath(__DIR__ . '/../../../../DOCUMENTOS/modelo_de_cuenta');
    if ($possible_base !== false) {
        $base_upload_dir = $possible_base;
    } else {
        $base_upload_dir = __DIR__ . '/../../../../DOCUMENTOS/modelo_de_cuenta';
    }

    $sourceDir = rtrim($base_upload_dir, '/\\') . DIRECTORY_SEPARATOR . $year_folder . DIRECTORY_SEPARATOR . $periodo_folder . DIRECTORY_SEPARATOR . $identidad_folder;

    $possible_dest_base = realpath(__DIR__ . '/../../../../DOCUMENTOS');
    if ($possible_dest_base !== false) {
        $dest_base = $possible_dest_base . DIRECTORY_SEPARATOR . 'cuentas_rechazadas' . DIRECTORY_SEPARATOR . 'modelo_de_cuenta';
    } else {
        $dest_base = __DIR__ . '/../../../../DOCUMENTOS/cuentas_rechazadas/modelo_de_cuenta';
    }

    $targetDir = rtrim($dest_base, '/\\') . DIRECTORY_SEPARATOR . $year_folder . DIRECTORY_SEPARATOR . $periodo_folder . DIRECTORY_SEPARATOR . $identidad_folder;

    $recursive_copy = function ($src, $dst) use (&$recursive_copy) {
        $dir = opendir($src);
        @mkdir($dst, 0777, true);
        while(false !== ($file = readdir($dir))) {
            if (($file !== '.') && ($file !== '..')) {
                $srcPath = $src . DIRECTORY_SEPARATOR . $file;
                $dstPath = $dst . DIRECTORY_SEPARATOR . $file;
                if (is_dir($srcPath)) {
                    $recursive_copy($srcPath, $dstPath);
                } else {
                    copy($srcPath, $dstPath);
                }
            }
        }
        closedir($dir);
    };

    $recursive_remove = function ($dir) use (&$recursive_remove) {
        if (!is_dir($dir)) return;
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $recursive_remove($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    };

    if (is_dir($sourceDir)) {
        if (!is_dir(dirname($targetDir))) {
            @mkdir(dirname($targetDir), 0777, true);
        }

        $moved = false;
        if (@rename($sourceDir, $targetDir)) {
            $moved = true;
        } else {
            try {
                $recursive_copy($sourceDir, $targetDir);
                if (is_dir($targetDir)) {
                    $recursive_remove($sourceDir);
                    $moved = true;
                }
            } catch (Exception $e) {
                $moved = false;
            }
        }
        if (!$moved) {
            throw new Exception('No se pudo mover la carpeta de documentos: ' . $sourceDir);
        }
    }

    $sql_delete  = "DELETE FROM cuenta WHERE id = ? AND numero_identidad = ?";
    $stmt_delete = $mysqli->prepare($sql_delete);

    if (!$stmt_delete) {
        throw new Exception('Error al preparar DELETE: ' . $mysqli->error);
    }

    $stmt_delete->bind_param("is", $id, $numero_identidad);

    if (!$stmt_delete->execute()) {
        throw new Exception('Error al eliminar la cuenta: ' . $stmt_delete->error);
    }

    $stmt_delete->close();

    $mysqli->commit();

    $referer    = $_SERVER['HTTP_REFERER'] ?? '';
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $combined   = $referer . ' ' . $requestUri;

    $back = $referer !== '' ? $referer : neiva_app_url('Arbimaps/index.php');

    if (strpos($combined, 'page=cuentas/validacion/revisar_cuenta_social') !== false || strpos($combined, 'page=cuentas/aprobacion_social') !== false) {
        $back = neiva_app_url('Arbimaps/index.php?page=cuentas/aprobacion_social');
    } elseif (strpos($combined, 'page=cuentas/validacion/revisar_cuentas') !== false || strpos($combined, 'page=cuentas/radicacion/revisar_cuentas') !== false) {
        $back = neiva_app_url('Arbimaps/index.php?page=cuentas/validar_cuentas');
    } elseif (strpos($combined, 'page=cuentas/validacion/revisar_cuenta_operaciones') !== false) {
        $back = neiva_app_url('Arbimaps/index.php?page=cuentas/aprobacion_operaciones');
    }

    $back .= (strpos($back, '?') === false ? '?' : '&') . 'msg=' . urlencode('Cuenta rechazada y movida a cuentas_rechazadas.');
    header('Location: ' . $back);
    exit;

} catch (Exception $e) {
    $mysqli->rollback();
    $redirectUrl = neiva_app_url('Arbimaps/index.php?page=cuentas/detalles_cuentas');
    $sep = (strpos($redirectUrl, '?') === false) ? '?' : '&';
    $redirectUrl .= $sep . 'msg=' . urlencode('Error al rechazar la cuenta: ' . $e->getMessage());
    header('Location: ' . $redirectUrl);
    exit;
}
