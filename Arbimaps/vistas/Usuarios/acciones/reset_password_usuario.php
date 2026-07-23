<?php
require_once __DIR__ . '/../../../includes/bootstrap.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('POST');
neiva_require_csrf('global');

if (!usuarioTieneAlgunRol('administrador')) {
    http_response_code(403);
    die('Solo un administrador puede restablecer contraseñas.');
}

$id_usuario = trim((string) ($_POST['id_usuario'] ?? ''));

if ($id_usuario === '') {
    die('Falta el id_usuario.');
}

$stmtUsuario = $mysqli->prepare('SELECT id_usuario, usuario_cons, nombre_usuario, apellido_usuario FROM usuarios_cons WHERE id_usuario = ? LIMIT 1');
if (!$stmtUsuario) {
    error_log('Usuarios reset password select prepare failed: ' . $mysqli->error);
    die('No fue posible cargar el usuario.');
}

$stmtUsuario->bind_param('s', $id_usuario);
$stmtUsuario->execute();
$usuario = $stmtUsuario->get_result()->fetch_assoc();

if (!$usuario) {
    die('Usuario no encontrado.');
}

$caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!#$%*@';
$passwordTemporal = '';
$max = strlen($caracteres) - 1;
for ($i = 0; $i < 16; $i++) {
    $passwordTemporal .= $caracteres[random_int(0, $max)];
}

$passwordHash = password_hash($passwordTemporal, PASSWORD_DEFAULT);

$stmt = $mysqli->prepare('UPDATE usuarios_cons SET password_cons = ?, debe_cambiar_password = 1 WHERE id_usuario = ?');
if (!$stmt) {
    error_log('Usuarios reset password update prepare failed: ' . $mysqli->error);
    die('No fue posible preparar el restablecimiento.');
}

$stmt->bind_param('ss', $passwordHash, $id_usuario);

if (!$stmt->execute()) {
    error_log('Usuarios reset password execute failed: ' . $stmt->error);
    die('No fue posible restablecer la contraseña.');
}

$redirectUrl = htmlspecialchars(neiva_app_url('Arbimaps/index.php?page=Usuarios/consultar_usuario'), ENT_QUOTES, 'UTF-8');
$usuarioCons = htmlspecialchars((string) $usuario['usuario_cons'], ENT_QUOTES, 'UTF-8');
$nombreCompleto = htmlspecialchars(trim((string) $usuario['nombre_usuario'] . ' ' . (string) $usuario['apellido_usuario']), ENT_QUOTES, 'UTF-8');
$passwordJs = json_encode($passwordTemporal, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
$usuarioJs = json_encode((string) $usuario['usuario_cons'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

echo "
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Contraseña restablecida</title>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
</head>
<body>
    <script>
        const passwordTemporal = {$passwordJs};
        const usuarioCons = {$usuarioJs};
        Swal.fire({
            icon: 'success',
            title: 'Contraseña restablecida',
            html: '<p><b>Usuario:</b> ' + usuarioCons + '</p>' +
                  '<p><b>Contraseña temporal:</b></p>' +
                  '<input class=\"swal2-input\" readonly value=\"' + passwordTemporal.replace(/\"/g, '&quot;') + '\">' +
                  '<p class=\"small text-muted\">Cópiala ahora y solicítale al usuario cambiarla al ingresar.</p>',
            confirmButtonText: 'Volver'
        }).then(() => {
            window.location.href = '{$redirectUrl}';
        });
    </script>
    <noscript>
        <p>Contraseña restablecida para {$nombreCompleto} ({$usuarioCons}).</p>
        <p>Contraseña temporal: <strong>" . htmlspecialchars($passwordTemporal, ENT_QUOTES, 'UTF-8') . "</strong></p>
        <p><a href='{$redirectUrl}'>Volver</a></p>
    </noscript>
</body>
</html>";
