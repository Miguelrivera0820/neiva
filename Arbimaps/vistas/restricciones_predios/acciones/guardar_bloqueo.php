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
    echo json_encode(['ok' => false, 'mensaje' => 'No tiene permisos para bloquear predios.']);
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

$npn = trim((string) ($_POST['npn'] ?? ''));
$fmi = trim((string) ($_POST['fmi'] ?? ''));
$motivo = trim((string) ($_POST['motivo'] ?? ''));
$npnNormalizado = normalizarIdentificadorPredio($npn);
$fmiNormalizado = normalizarIdentificadorPredio($fmi);

if ($npnNormalizado === null && $fmiNormalizado === null) {
    echo json_encode(['ok' => false, 'mensaje' => 'Debe indicar un NPN o un FMI.']);
    exit;
}

if (mb_strlen($motivo) < 5) {
    echo json_encode(['ok' => false, 'mensaje' => 'Debe registrar el motivo del bloqueo.']);
    exit;
}

try {
    $existente = buscarBloqueoActivoPredio($mysqli, $npn, $fmi);
    if ($existente) {
        http_response_code(409);
        echo json_encode(['ok' => false, 'mensaje' => 'El predio ya tiene un bloqueo activo.']);
        exit;
    }

    $usuarioId = (string) ($_SESSION['id_usuario'] ?? '');
    $usuarioNombre = trim((string) (($_SESSION['nombre_usuario'] ?? '') . ' ' . ($_SESSION['apellido_usuario'] ?? '')));
    if ($usuarioNombre === '') {
        $usuarioNombre = $usuarioId !== '' ? $usuarioId : 'Usuario';
    }

    $stmt = $mysqli->prepare(
        "INSERT INTO predios_bloqueados
        (npn, npn_normalizado, fmi, fmi_normalizado, motivo, usuario_bloqueo_id, usuario_bloqueo_nombre)
        VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        'sssssss',
        $npn,
        $npnNormalizado,
        $fmi,
        $fmiNormalizado,
        $motivo,
        $usuarioId,
        $usuarioNombre
    );
    $stmt->execute();

    echo json_encode(['ok' => true, 'mensaje' => 'Predio bloqueado correctamente.']);
} catch (Throwable $error) {
    error_log('Restricciones guardar_bloqueo failed: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'No fue posible guardar el bloqueo.']);
}
