<?php
/**
 * Script de migración para hashear contraseñas de usuarios en usuarios_cons.
 * Convierte contraseñas en texto plano a hashes seguros usando PASSWORD_DEFAULT.
 */

require_once __DIR__ . '/../conexion.php';

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error . PHP_EOL);
}

// Obtener todos los usuarios
$result = $mysqli->query("SELECT id_usuario, usuario_cons, password_cons FROM usuarios_cons");
if (!$result) {
    die("Error al consultar usuarios: " . $mysqli->error . PHP_EOL);
}

$total = 0;
$migrated = 0;
$skipped = 0;
$errors = 0;

echo "Iniciando migración de contraseñas..." . PHP_EOL;
echo str_repeat("-", 60) . PHP_EOL;

// Preparar consulta de actualización
$stmt = $mysqli->prepare("UPDATE usuarios_cons SET password_cons = ? WHERE id_usuario = ?");
if (!$stmt) {
    die("Error al preparar la consulta de actualización: " . $mysqli->error . PHP_EOL);
}

while ($row = $result->fetch_assoc()) {
    $total++;
    $id = $row['id_usuario'];
    $usuario = $row['usuario_cons'];
    $pass = $row['password_cons'];

    // Verificar si ya es un hash de bcrypt
    $is_hash = (str_starts_with($pass, '$2y$') || str_starts_with($pass, '$2a$') || str_starts_with($pass, '$2x$'));

    if ($is_hash) {
        $skipped++;
        continue;
    }

    // Hashear la contraseña en texto plano
    $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
    if ($hashed_pass === false) {
        echo "[ERROR] No se pudo generar el hash para el usuario: {$usuario} (ID: {$id})" . PHP_EOL;
        $errors++;
        continue;
    }

    // Actualizar en base de datos
    $stmt->bind_param('ss', $hashed_pass, $id);
    if ($stmt->execute()) {
        $migrated++;
    } else {
        echo "[ERROR] No se pudo actualizar en BD al usuario: {$usuario} (ID: {$id}). Error: {$stmt->error}" . PHP_EOL;
        $errors++;
    }
}

$stmt->close();
$mysqli->close();

echo str_repeat("-", 60) . PHP_EOL;
echo "Migración completada con éxito." . PHP_EOL;
echo "Total usuarios procesados: {$total}" . PHP_EOL;
echo "Usuarios migrados (hasheados): {$migrated}" . PHP_EOL;
echo "Usuarios omitidos (ya tenían hash): {$skipped}" . PHP_EOL;
echo "Errores encontrados: {$errors}" . PHP_EOL;
?>
