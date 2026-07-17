<?php
require_once __DIR__ . '/bootstrap.php';

neiva_bootstrap();

if (!function_exists('getRolesUsuario')) {
    function getRolesUsuario(): array
    {
        return array_values(array_filter([
            $_SESSION['rol_usuario']      ?? null,
            $_SESSION['rol_usuario_dos']  ?? null,
            $_SESSION['rol_usuario_tres'] ?? null,
        ]));
    }
}

if (!function_exists('usuarioTieneAlgunRol')) {
    function usuarioTieneAlgunRol($rolesNecesarios): bool
    {
        $rolesUsuario = getRolesUsuario();

        if (!is_array($rolesNecesarios)) {
            $rolesNecesarios = [$rolesNecesarios];
        }

        return count(array_intersect($rolesNecesarios, $rolesUsuario)) > 0;
    }
}
