<?php

require_once __DIR__ . '/../conexion.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este comando solo puede ejecutarse por CLI.\n");
    exit(1);
}

$dryRun = in_array('--dry-run', $_SERVER['argv'] ?? [], true);

$result = $mysqli->query('SELECT id_usuario, usuario_cons, password_cons FROM usuarios_cons');
if (!$result) {
    fwrite(STDERR, "No fue posible cargar los usuarios.\n");
    exit(1);
}

$pending = [];
while ($row = $result->fetch_assoc()) {
    $password = (string) ($row['password_cons'] ?? '');
    $info = password_get_info($password);
    $algo = $info['algo'] ?? null;
    $isHashed = $algo !== null && $algo !== '' && $algo !== 0;

    if ($isHashed) {
        continue;
    }

    if ($password === '') {
        fwrite(STDERR, "El usuario {$row['usuario_cons']} tiene contraseña vacía; no se migró.\n");
        continue;
    }

    $pending[] = [
        'id_usuario' => (string) $row['id_usuario'],
        'usuario_cons' => (string) $row['usuario_cons'],
        'password_cons' => $password,
    ];
}

if ($dryRun) {
    echo json_encode([
        'dry_run' => true,
        'pending' => count($pending),
        'sample' => array_slice(array_map(static function (array $row): array {
            return [
                'id_usuario' => $row['id_usuario'],
                'usuario_cons' => $row['usuario_cons'],
                'password_len' => strlen($row['password_cons']),
            ];
        }, $pending), 0, 20),
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
    exit(0);
}

if (empty($pending)) {
    echo "No hay contraseñas en texto plano por migrar.\n";
    exit(0);
}

$mysqli->begin_transaction();

try {
    $stmt = $mysqli->prepare('UPDATE usuarios_cons SET password_cons = ? WHERE id_usuario = ?');
    if (!$stmt) {
        throw new RuntimeException('No fue posible preparar la actualización de contraseñas.');
    }

    $migrated = 0;
    foreach ($pending as $row) {
        $hash = password_hash($row['password_cons'], PASSWORD_DEFAULT);
        if ($hash === false) {
            throw new RuntimeException('No fue posible generar el hash para el usuario ' . $row['usuario_cons'] . '.');
        }

        $stmt->bind_param('ss', $hash, $row['id_usuario']);
        if (!$stmt->execute()) {
            throw new RuntimeException('No fue posible actualizar el usuario ' . $row['usuario_cons'] . '.');
        }

        $migrated++;
    }

    $mysqli->commit();
    echo json_encode([
        'migrated' => $migrated,
        'status' => 'ok',
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
} catch (Throwable $e) {
    $mysqli->rollback();
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}
