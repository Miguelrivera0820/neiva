<?php
require_once __DIR__ . '/../Arbimaps/includes/bootstrap.php';
require_once __DIR__ . '/../conexion.php';
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este comando solo puede ejecutarse por CLI.\n");
    exit(1);
}

$argv = $_SERVER['argv'] ?? [];
if (count($argv) < 8) {
    fwrite(STDERR, "Uso: php scripts/create_admin.php <usuario> <cedula> <nombres> <apellidos> <correo> <celular> <rol>\n");
    exit(1);
}

[$script, $usuario_cons, $cedula_usuario, $nombre_usuario, $apellido_usuario, $correo_usuario, $celular_usuario, $rol_usuario] = $argv;

if ($rol_usuario !== 'administrador') {
    fwrite(STDERR, "Este comando solo permite crear el rol administrador.\n");
    exit(1);
}

fwrite(STDOUT, "Contraseña: ");
$password = trim((string) fgets(STDIN));
fwrite(STDOUT, "Confirmar contraseña: ");
$passwordConfirm = trim((string) fgets(STDIN));

if ($password === '' || $password !== $passwordConfirm) {
    fwrite(STDERR, "La contraseña es obligatoria y debe coincidir.\n");
    exit(1);
}

$stmtExist = $mysqli->prepare("SELECT id_usuario FROM usuarios_cons WHERE usuario_cons = ? OR cedula_usuario = ? LIMIT 1");
$stmtExist->bind_param('ss', $usuario_cons, $cedula_usuario);
$stmtExist->execute();
$existente = $stmtExist->get_result()->fetch_assoc();
if ($existente) {
    fwrite(STDERR, "Ya existe un usuario con ese usuario o cédula.\n");
    exit(1);
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$id_usuario = $cedula_usuario;

$stmt = $mysqli->prepare(
    "INSERT INTO usuarios_cons
    (id_usuario, nombre_usuario, apellido_usuario, cedula_usuario, correo_usuario, celular_usuario, usuario_cons, password_cons, rol_usuario)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param(
    'sssssssss',
    $id_usuario,
    $nombre_usuario,
    $apellido_usuario,
    $cedula_usuario,
    $correo_usuario,
    $celular_usuario,
    $usuario_cons,
    $passwordHash,
    $rol_usuario
);

if (!$stmt->execute()) {
    fwrite(STDERR, "No fue posible crear el administrador.\n");
    exit(1);
}

fwrite(STDOUT, "Administrador creado correctamente.\n");
