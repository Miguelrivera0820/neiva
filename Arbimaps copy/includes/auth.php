<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getRolesUsuario(): array
{
    return array_values(array_filter([
        $_SESSION['rol_usuario']      ?? null,
        $_SESSION['rol_usuario_dos']  ?? null,
        $_SESSION['rol_usuario_tres'] ?? null,
    ]));
}

function usuarioTieneAlgunRol($rolesNecesarios): bool
{
    $rolesUsuario = getRolesUsuario();

    if (!is_array($rolesNecesarios)) {
        $rolesNecesarios = [$rolesNecesarios];
    }

    return count(array_intersect($rolesNecesarios, $rolesUsuario)) > 0;
}
