<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../../../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

$url         = trim($_POST['url'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

if ($url === '' || $descripcion === '') {
    header('Location: ../../../index.php?page=noticias/vistas/cargar_noticia&error=campos');
    exit;
}

if (!filter_var($url, FILTER_VALIDATE_URL)) {
    header('Location: ../../../index.php?page=noticias/vistas/cargar_noticia&error=url');
    exit;
}

if (mb_strlen($descripcion) > 180) {
    $descripcion = mb_substr($descripcion, 0, 180);
}

$sql = "INSERT INTO 
                noticias_arbimaps 
                (
                    link_noticia, 
                    descripcion_noticia
                ) VALUES (?, ?)";
$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    header('Location: ../../../index.php?page=noticias/vistas/cargar_noticia&error=prepare');
    exit;
}

$stmt->bind_param("ss", $url, $descripcion);

if ($stmt->execute()) {
    $stmt->close();
    header('Location: ../../../index.php?page=noticias/vistas/cargar_noticia&ok=1');
    exit;
}

$stmt->close();
header('Location: ../../../index.php?page=noticias/vistas/cargar_noticia&error=insert');
exit;
