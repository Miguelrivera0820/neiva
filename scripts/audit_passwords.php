<?php

require_once __DIR__ . '/../conexion.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este comando solo puede ejecutarse por CLI.\n");
    exit(1);
}

$result = $mysqli->query('SELECT id_usuario, usuario_cons, correo_usuario, rol_usuario, password_cons FROM usuarios_cons');
if (!$result) {
    fwrite(STDERR, "No fue posible auditar las contraseñas.\n");
    exit(1);
}

$total = 0;
$hashed = 0;
$plain = 0;
$examples = [];

while ($row = $result->fetch_assoc()) {
    $total++;
    $info = password_get_info((string) $row['password_cons']);
    $algo = $info['algo'] ?? null;
    $isHashed = $algo !== null && $algo !== '' && $algo !== 0;

    if ($isHashed) {
        $hashed++;
        continue;
    }

    $plain++;
    if (count($examples) < 20) {
        $examples[] = [
            'id_usuario' => $row['id_usuario'],
            'usuario_cons' => $row['usuario_cons'],
            'correo_usuario' => $row['correo_usuario'],
            'rol_usuario' => $row['rol_usuario'],
            'len' => strlen((string) $row['password_cons']),
        ];
    }
}

echo json_encode([
    'total' => $total,
    'hashed' => $hashed,
    'plain' => $plain,
    'plain_examples' => $examples,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
