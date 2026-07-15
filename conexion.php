<?php
require_once __DIR__ . '/Arbimaps/includes/bootstrap.php';

$servidor = neiva_env('DB_HOST', 'localhost');
$usuario = neiva_env('DB_USER', 'root');
$contrasena = neiva_env('DB_PASSWORD', '');
$base_de_datos = neiva_env('DB_NAME', 'neiva');

$mysqli = @new mysqli($servidor, $usuario, $contrasena, $base_de_datos);

if ($mysqli->connect_error) {
    if (neiva_app_env() === 'development') {
        die('Conexión fallida a la base de datos.');
    }

    error_log('DB connection failed: ' . $mysqli->connect_error);
    die('Error interno del servidor.');
}

$mysqli->set_charset('utf8mb4');

if (!ini_get('date.timezone')) {
    date_default_timezone_set('America/Bogota');
}

$mysqli->query("SET time_zone = '-05:00'");
