<?php
require_once __DIR__ . '/../../../includes/bootstrap.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('POST');
neiva_require_permission('usuarios.consultar');
neiva_require_csrf('global');

$id_usuario = trim((string) ($_POST['id_usuario'] ?? ''));

if ($id_usuario === '') {
    die('Falta el id_usuario.');
}

if (isset($_SESSION['id_usuario']) && (string) $_SESSION['id_usuario'] === $id_usuario) {
    die('No puedes eliminar tu propio usuario.');
}

$sql = "DELETE FROM usuarios_cons WHERE id_usuario = ?";
$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    error_log('Usuarios eliminar prepare failed: ' . $mysqli->error);
    die('No fue posible eliminar el usuario.');
}

$stmt->bind_param("s", $id_usuario);

if ($stmt->execute()) {
    $redirectUrl = htmlspecialchars(neiva_app_url('Arbimaps/index.php?page=Usuarios/consultar_usuario'), ENT_QUOTES, 'UTF-8');
    echo "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <title>Eliminando usuario...</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Usuario eliminado',
                text: 'El usuario fue eliminado correctamente.',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.location.href = '{$redirectUrl}';
            });
        </script>
    </body>
    </html>";
    exit;
}

error_log('Usuarios eliminar execute failed: ' . $stmt->error);
echo 'No fue posible eliminar el usuario.';
