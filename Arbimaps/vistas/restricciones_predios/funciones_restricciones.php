<?php

function normalizarIdentificadorPredio($valor): ?string
{
    $valor = strtoupper(trim((string) $valor));
    if ($valor === '') {
        return null;
    }

    $normalizado = preg_replace('/[^A-Z0-9]/', '', $valor);
    return $normalizado !== '' ? $normalizado : null;
}

function buscarBloqueoActivoPredio(mysqli $conexion, $npn = '', $fmi = ''): ?array
{
    $npnNormalizado = normalizarIdentificadorPredio($npn);
    $fmiNormalizado = normalizarIdentificadorPredio($fmi);

    if ($npnNormalizado === null && $fmiNormalizado === null) {
        return null;
    }

    if ($npnNormalizado !== null && $fmiNormalizado !== null) {
        $sql = "SELECT id_bloqueo, npn, fmi, motivo, fecha_bloqueo, usuario_bloqueo_nombre
                FROM predios_bloqueados
                WHERE estado = 'BLOQUEADO'
                  AND (npn_normalizado = ? OR fmi_normalizado = ?)
                ORDER BY fecha_bloqueo DESC
                LIMIT 1";
        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('No fue posible consultar el bloqueo del predio.');
        }
        $stmt->bind_param('ss', $npnNormalizado, $fmiNormalizado);
    } elseif ($npnNormalizado !== null) {
        $sql = "SELECT id_bloqueo, npn, fmi, motivo, fecha_bloqueo, usuario_bloqueo_nombre
                FROM predios_bloqueados
                WHERE estado = 'BLOQUEADO' AND npn_normalizado = ?
                ORDER BY fecha_bloqueo DESC
                LIMIT 1";
        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('No fue posible consultar el bloqueo del predio.');
        }
        $stmt->bind_param('s', $npnNormalizado);
    } else {
        $sql = "SELECT id_bloqueo, npn, fmi, motivo, fecha_bloqueo, usuario_bloqueo_nombre
                FROM predios_bloqueados
                WHERE estado = 'BLOQUEADO' AND fmi_normalizado = ?
                ORDER BY fecha_bloqueo DESC
                LIMIT 1";
        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('No fue posible consultar el bloqueo del predio.');
        }
        $stmt->bind_param('s', $fmiNormalizado);
    }

    $stmt->execute();
    $resultado = $stmt->get_result();
    $bloqueo = $resultado ? $resultado->fetch_assoc() : null;
    $stmt->close();

    return $bloqueo ?: null;
}

function usuarioPuedeAdministrarBloqueos(): bool
{
    $rolesPermitidos = [
        'administrador',
        'director_catastro',
        'coordinacion_tecnica',
        'soporte',
    ];

    $rolesUsuario = array_filter([
        $_SESSION['rol_usuario'] ?? null,
        $_SESSION['rol_usuario_dos'] ?? null,
        $_SESSION['rol_usuario_tres'] ?? null,
    ]);

    return count(array_intersect($rolesPermitidos, $rolesUsuario)) > 0;
}
