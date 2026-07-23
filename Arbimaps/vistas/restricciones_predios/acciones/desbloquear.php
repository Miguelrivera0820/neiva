<?php
require_once __DIR__ . '/../../../includes/bootstrap.php';
require_once __DIR__ . '/../../../../conexion.php';
require_once __DIR__ . '/../funciones_restricciones.php';

neiva_bootstrap();
neiva_require_methods('POST', true);
neiva_require_auth(true);

header('Content-Type: application/json; charset=utf-8');

if (!usuarioPuedeAdministrarBloqueos()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'mensaje' => 'No tiene permisos para desbloquear predios.']);
    exit;
}

if (
    !neiva_validate_csrf_token($_POST['csrf_token'] ?? null, 'global')
    && !neiva_validate_csrf_token($_POST['csrf_token'] ?? null, 'predios_bloqueados')
) {
    http_response_code(419);
    echo json_encode(['ok' => false, 'mensaje' => 'Token de seguridad inválido.']);
    exit;
}

$idBloqueo = (int) ($_POST['id_bloqueo'] ?? 0);
$motivo = trim((string) ($_POST['motivo_desbloqueo'] ?? ''));

if ($idBloqueo <= 0 || mb_strlen($motivo) < 5) {
    echo json_encode(['ok' => false, 'mensaje' => 'Debe indicar el bloqueo y el motivo de desbloqueo.']);
    exit;
}

$usuarioId = (string) ($_SESSION['id_usuario'] ?? '');
$usuarioNombre = trim((string) (($_SESSION['nombre_usuario'] ?? '') . ' ' . ($_SESSION['apellido_usuario'] ?? '')));
if ($usuarioNombre === '') {
    $usuarioNombre = $usuarioId !== '' ? $usuarioId : 'Usuario';
}

$stmt = $mysqli->prepare(
    "UPDATE predios_bloqueados
     SET estado = 'DESBLOQUEADO',
         fecha_desbloqueo = NOW(),
         usuario_desbloqueo_id = ?,
         usuario_desbloqueo_nombre = ?,
         motivo_desbloqueo = ?
     WHERE id_bloqueo = ? AND estado = 'BLOQUEADO'"
);
$stmt->bind_param('sssi', $usuarioId, $usuarioNombre, $motivo, $idBloqueo);
$stmt->execute();
$actualizados = $stmt->affected_rows;

if ($actualizados < 1) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'mensaje' => 'El bloqueo no existe o ya fue desbloqueado.']);
    exit;
}

echo json_encode(['ok' => true, 'mensaje' => 'Predio desbloqueado correctamente.']);
