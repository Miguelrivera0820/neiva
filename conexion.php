<?php
require_once __DIR__ . '/Arbimaps/includes/bootstrap.php';

$servidor = neiva_env('DB_HOST', 'localhost');
$usuario = neiva_env('DB_USER', 'root');
$contrasena = neiva_env('DB_PASSWORD', '');
$base_de_datos = neiva_env('DB_NAME', 'neiva');
$puerto = (int) (neiva_env('DB_PORT', '3306') ?? '3306');
$charset = neiva_env('DB_CHARSET', 'utf8mb4') ?? 'utf8mb4';

$mysqli = @new mysqli($servidor, $usuario, $contrasena, $base_de_datos, $puerto);

if ($mysqli->connect_error) {
    error_log('DB connection failed: ' . $mysqli->connect_error);

    if (neiva_app_debug()) {
        die('Conexión fallida a la base de datos: ' . htmlspecialchars($mysqli->connect_error, ENT_QUOTES, 'UTF-8'));
    }

    die('Error interno del servidor.');
}

$mysqli->set_charset($charset);
$mysqli->query("SET time_zone = '" . $mysqli->real_escape_string(neiva_db_timezone()) . "'");
