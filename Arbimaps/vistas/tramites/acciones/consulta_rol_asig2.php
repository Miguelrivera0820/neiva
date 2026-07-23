<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('GET', true);
neiva_require_permission('menu.tramites', $PERMISOS, true);

$rol = $_GET['rol'] ?? '';

if ($rol) {
    $stmt = $mysqli->prepare("SELECT id_usuario, cedula_usuario, nombre_usuario, apellido_usuario, usuario_cons, correo_usuario, celular_usuario, rol_usuario FROM usuarios_cons WHERE rol_usuario = ?");
    
    if (!$stmt) {
        neiva_abort(500, 'Error preparando la consulta.', true);
    }

    $stmt->bind_param("s", $rol);
    $stmt->execute();
    $res = $stmt->get_result();

    $usuarios = [];
    while ($row = $res->fetch_assoc()) {
        $usuarios[] = [
            "id_usuario" => $row['id_usuario'],
            "cedula" => $row['cedula_usuario'],
            "nombre" => $row['nombre_usuario'],
            "apellido" => $row['apellido_usuario'],
            "usuario_cons" => $row['usuario_cons'],
            "correo_usuario" => $row['correo_usuario'],
            "celular_usuario" => $row['celular_usuario'],
            "rol_usuario" => $row['rol_usuario']
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($usuarios);
    exit;
} else {
    http_response_code(400);
    echo json_encode(["error" => "Rol no proporcionado"]);
}
?>
