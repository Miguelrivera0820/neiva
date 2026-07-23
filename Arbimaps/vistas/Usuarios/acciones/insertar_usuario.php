<?php
require_once __DIR__ . '/../../../includes/bootstrap.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('POST');
neiva_require_permission('usuarios.crear');
neiva_require_csrf('global');

$nombre_usuario = trim((string) ($_POST['nombres_usuario'] ?? ''));
$apellido_usuario = trim((string) ($_POST['apellidos_usuario'] ?? ''));
$cedula_usuario = trim((string) ($_POST['num_identidad_usuario'] ?? ''));
$correo_usuario = trim((string) ($_POST['correo_usuario'] ?? ''));
$celular_usuario = trim((string) ($_POST['celular_usuario'] ?? ''));
$usuario_cons = trim((string) ($_POST['usuario_plataforma'] ?? ''));
$password_plataforma = (string) ($_POST['password_plataforma'] ?? '');
$confirmar_password = (string) ($_POST['confirmar_password'] ?? '');
$rol_usuario = trim((string) ($_POST['rol_usuario'] ?? ''));

if (
    $nombre_usuario === '' ||
    $apellido_usuario === '' ||
    $cedula_usuario === '' ||
    $correo_usuario === '' ||
    $usuario_cons === '' ||
    $password_plataforma === '' ||
    $rol_usuario === ''
) {
    die('Por favor, complete todos los campos requeridos.');
}

if (!filter_var($correo_usuario, FILTER_VALIDATE_EMAIL)) {
    die('El correo electrónico no es válido.');
}

if ($password_plataforma !== $confirmar_password) {
    die('Las contraseñas no coinciden.');
}

$id_usuario = $cedula_usuario;
$password_cons = password_hash($password_plataforma, PASSWORD_DEFAULT);
$foto_user = null;

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
        $foto_user = file_get_contents($fotoValidada['tmp_name']);
    }
}

$sql = "INSERT INTO usuarios_cons (
            id_usuario,
            nombre_usuario,
            apellido_usuario,
            cedula_usuario,
            correo_usuario,
            celular_usuario,
            usuario_cons,
            password_cons,
            rol_usuario,
            foto_user,
            debe_cambiar_password
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    error_log('Usuarios insertar prepare failed: ' . $mysqli->error);
    die('No fue posible registrar el usuario.');
}

$stmt->bind_param(
    "ssssssssss",
    $id_usuario,
    $nombre_usuario,
    $apellido_usuario,
    $cedula_usuario,
    $correo_usuario,
    $celular_usuario,
    $usuario_cons,
    $password_cons,
    $rol_usuario,
    $foto_user
);

if ($stmt->execute()) {
    $redirectUrl = htmlspecialchars(neiva_app_url('Arbimaps/index.php?page=Usuarios/consultar_usuario'), ENT_QUOTES, 'UTF-8');
    echo "
    <!DOCTYPE html>
    <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <title>Registrando Usuario...</title>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Usuario registrado exitosamente',
                    text: 'El usuario ha sido creado correctamente.',
                    confirmButtonText: 'Aceptar'
                }).then(function() {
                    window.location.href = '{$redirectUrl}';
                });
            </script>
        </body>
    </html>";
    exit;
}

error_log('Usuarios insertar execute failed: ' . $stmt->error);
echo 'No fue posible registrar el usuario.';
