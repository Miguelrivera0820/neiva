<?php
require_once __DIR__ . '/../../../includes/bootstrap.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('POST');
neiva_require_permission('usuarios.consultar');
neiva_require_csrf('global');

$id_usuario = trim((string) ($_POST['id_usuario'] ?? ''));
$nombre_usuario = trim((string) ($_POST['nombres_usuario'] ?? ''));
$apellido_usuario = trim((string) ($_POST['apellidos_usuario'] ?? ''));
$correo_usuario = trim((string) ($_POST['correo_usuario'] ?? ''));
$celular_usuario = trim((string) ($_POST['celular_usuario'] ?? ''));
$usuario_cons = trim((string) ($_POST['usuario_plataforma'] ?? ''));
$password_plataforma = (string) ($_POST['password_plataforma'] ?? '');
$rol_usuario = trim((string) ($_POST['rol_usuario'] ?? ''));

if (
    $id_usuario === '' ||
    $nombre_usuario === '' ||
    $apellido_usuario === '' ||
    $correo_usuario === '' ||
    $usuario_cons === '' ||
    $rol_usuario === ''
) {
    die('Por favor, complete todos los campos requeridos.');
}

if (!filter_var($correo_usuario, FILTER_VALIDATE_EMAIL)) {
    die('El correo electrónico no es válido.');
}

$sqlActual = "SELECT password_cons, foto_user FROM usuarios_cons WHERE id_usuario = ?";
$stmtActual = $mysqli->prepare($sqlActual);
if (!$stmtActual) {
    error_log('Usuarios actualizar prepare actual failed: ' . $mysqli->error);
    die('No fue posible cargar la información actual del usuario.');
}

$stmtActual->bind_param("s", $id_usuario);
$stmtActual->execute();
$resActual = $stmtActual->get_result();

if ($resActual->num_rows === 0) {
    die('Usuario no encontrado.');
}

$actual = $resActual->fetch_assoc();
$password_final = (string) ($actual['password_cons'] ?? '');
$foto_final = $actual['foto_user'] ?? null;

if ($password_plataforma !== '') {
    $password_final = password_hash($password_plataforma, PASSWORD_DEFAULT);
}

if (isset($_FILES['foto_user']) && ($_FILES['foto_user']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    $fotoValidada = neiva_validate_upload($_FILES['foto_user'], [
        'required' => false,
        'max_bytes' => 5 * 1024 * 1024,
        'allowed_mimes' => [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        ],
        'image_decode' => true,
    ]);

    if (!empty($fotoValidada)) {
        $foto_final = file_get_contents($fotoValidada['tmp_name']);
    }
}

$sql = "UPDATE usuarios_cons SET
            nombre_usuario = ?,
            apellido_usuario = ?,
            correo_usuario = ?,
            celular_usuario = ?,
            usuario_cons = ?,
            password_cons = ?,
            rol_usuario = ?,
            foto_user = ?
        WHERE id_usuario = ?";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    error_log('Usuarios actualizar prepare failed: ' . $mysqli->error);
    die('No fue posible actualizar el usuario.');
}

$stmt->bind_param(
    "sssssssss",
    $nombre_usuario,
    $apellido_usuario,
    $correo_usuario,
    $celular_usuario,
    $usuario_cons,
    $password_final,
    $rol_usuario,
    $foto_final,
    $id_usuario
);

if ($stmt->execute()) {
    $redirectUrl = htmlspecialchars(neiva_app_url('Arbimaps/index.php?page=Usuarios/consultar_usuario'), ENT_QUOTES, 'UTF-8');
    echo "
    <!DOCTYPE html>
    <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <title>Actualizando Usuario...</title>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Usuario actualizado',
                    text: 'Los datos del usuario fueron actualizados correctamente.',
                    confirmButtonText: 'Aceptar'
                }).then(function() {
                    window.location.href = '{$redirectUrl}';
                });
            </script>
        </body>
    </html>";
    exit;
}

error_log('Usuarios actualizar execute failed: ' . $stmt->error);
echo 'No fue posible actualizar el usuario.';
