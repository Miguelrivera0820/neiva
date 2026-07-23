<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../../../../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    $back = $_SERVER['HTTP_REFERER'] ?? '/';
    header('Location: ' . $back);
    exit;
}

$id = intval($_POST['id']);

if (!isset($_POST['valor_aprobado']) || $_POST['valor_aprobado'] === '') {
    $back = $_SERVER['HTTP_REFERER'] ?? '/';
    $back .= (strpos($back, '?') === false ? '?' : '&') . 'msg=' . urlencode('Debe ingresar el valor aprobado.');
    header('Location: ' . $back);
    exit;
}

$raw_valor = str_replace(['.', ','], ['', '.'], $_POST['valor_aprobado']);
$raw_valor = str_replace(['$', ' '], '', $raw_valor);
$valor_aprobado = floatval($raw_valor);

if ($valor_aprobado < 0) {
    $back = $_SERVER['HTTP_REFERER'] ?? '/';

    $back .= (strpos($back, '?') === false ? '?' : '&') . 'msg=' . urlencode('El valor aprobado no puede ser negativo.');
    header('Location: ' . $back);
    exit;
}

$valor_cobrar = null;
$stmt = $mysqli->prepare("SELECT valor FROM cuenta WHERE id = ?");
if ($stmt) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($valor_cobrar);
    $stmt->fetch();
    $stmt->close();
}
if ($valor_cobrar === null) {
    $back = $_SERVER['HTTP_REFERER'] ?? '/';
    $back .= (strpos($back, '?') === false ? '?' : '&') . 'msg=' . urlencode('El valor aprobado no puede ser mayor al valor a cobrar.');
    header('Location: ' . $back);
    exit;
}

$valor_cobrar_float = floatval($valor_cobrar);

if ($valor_aprobado > $valor_cobrar_float) {
    $back = $_SERVER['HTTP_REFERER'] ?? '/';
    $mensaje = 'El valor aprobado no puede ser mayor al valor a cobrar (' . number_format($valor_cobrar_float, 0, ',', '.') . ').';
    $back .= (strpos($back, '?') === false ? '?' : '&') . 'msg=' . urlencode($mensaje);
    header('Location: ' . $back);
    exit;
}



$stmt = $mysqli->prepare("
    UPDATE cuenta
    SET estado = 'Aprobado', 
        valor_aprobado = ?,
        fecha_aprobacion_presupuesto = NOW() 
    WHERE id = ?");
if ($stmt) {
    $stmt->bind_param('di', $valor_aprobado, $id);
    $stmt->execute();
    $stmt->close();
}


$redirectUrl = neiva_app_url('Arbimaps/index.php?page=cuentas/detalles_cuentas');
$sep = (strpos($redirectUrl, '?') === false) ? '?' : '&';
$redirectUrl .= $sep . 'msg=' . urlencode('Cuenta aprobada exitosamente.');

header('Location: ' . $redirectUrl);
exit;
?>