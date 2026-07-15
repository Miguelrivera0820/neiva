<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../conexion.php';
if (!isset($mysqli) || $mysqli === null) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error: no se encontró la conexión \$mysqli. Revisa conexion.php"
    ]);
    exit;
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validación de sesión
    if (!isset($_SESSION['id_usuario'])) {
        echo json_encode([
            "success" => false,
            "message" => "Error: Usuario no autenticado."
        ]);
        exit;
    }

    // ✅ Obtener datos del formulario
    $sol_usuario_id             = $_POST['sol_usuario_id'] ?? null;
    $con_id                     = $_POST['con_id'] ?? null;
    $con_num_identidad          = $_POST['con_num_identidad'] ?? null;
    $sol_nuevo_salario          = $_POST['sol_nuevo_salario'] ?? null;
    $sol_tipo_otrosi            = isset($_POST['sol_tipo_otrosi']) ? implode(" ", $_POST['sol_tipo_otrosi']) : null;
    $sol_fecha_inicio           = $_POST['sol_fecha_inicio'] ?? null;
    $sol_nueva_fecha_final      = $_POST['sol_nueva_fecha_final'] ?? null;
    $sol_duracion               = $_POST['sol_duracion'] ?? null;
    $sol_motivo                 = $_POST['sol_motivo'] ?? null;
    $sol_valor_otrosi           = $_POST['sol_valor_otrosi'] ?? null;

    // Validación del rol
    $rol = $_POST['rol'] ?? 'no';
    $con_cargo = $_POST['con_cargo'] ?? null;
    $sol_nuevo_rol = ($rol === 'si') ? ($_POST['sol_nuevo_rol'] ?? null) : $con_cargo;

    // Validación del proyecto
    $proyecto = $_POST['proyecto'] ?? 'no';
    $con_proyecto = $_POST['con_proyecto'] ?? null;
    $sol_nuevo_proyecto = ($proyecto === 'si') ? ($_POST['sol_nuevo_proyecto'] ?? null) : $con_proyecto;

    // ✅ Guardar archivo
    function guardarArchivo($archivo, $ruta)
    {
        if ($archivo && $archivo['error'] === UPLOAD_ERR_OK) {
            if (!file_exists($ruta)) {
                mkdir($ruta, 0777, true);
            }
            $nombreArchivo = basename($archivo['name']);
            $rutaDestino = $ruta . $nombreArchivo;
            if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                return $rutaDestino;
            }
        }
        return null;
    }

    // Rutas para archivos
    $uploadDir = 'Arbitrium_otrosi_Nuevo/';
    $carpetaUsuario = $uploadDir . $con_num_identidad . '/Otro si Nuevo/';
    $sol_archivo_otrosi = isset($_FILES['sol_archivo_otrosi'])
        ? guardarArchivo($_FILES['sol_archivo_otrosi'], $carpetaUsuario)
        : null;

    $sql = "INSERT INTO solicitudes_otrosi (
        con_num_identidad,
        sol_nuevo_rol,
        sol_nuevo_proyecto,
        sol_nuevo_salario,
        sol_tipo_otrosi, 
        sol_fecha_inicio, 
        sol_nueva_fecha_final,
        sol_duracion, 
        sol_motivo,
        sol_valor_otrosi,
        con_id,
        sol_archivo_otrosi,
        sol_estado,
        sol_usuario_id,
        sol_estado_usuario
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        echo json_encode([
            "success" => false,
            "message" => "Error al preparar la consulta",
            "error" => $mysqli->error
        ]);
        exit;
    }

    $sol_estado = 'PENDIENTE';
    $sol_estado_usuario = 'PENDIENTE';

    $stmt->bind_param(
        "sssssssssssssis",
        $con_num_identidad,
        $sol_nuevo_rol,
        $sol_nuevo_proyecto,
        $sol_nuevo_salario,
        $sol_tipo_otrosi,
        $sol_fecha_inicio,
        $sol_nueva_fecha_final,
        $sol_duracion,
        $sol_motivo,
        $sol_valor_otrosi,
        $con_id,
        $sol_archivo_otrosi,
        $sol_estado,
        $sol_usuario_id,
        $sol_estado_usuario
    );


    /* Inicia copia a la tabla copia_solicitudes_otrosi de la primer insercción de la solicitud 
     (para evitar que se borren las modificaciones) */
    if ($stmt->execute()) {

        $sqlUpdateContrato = "UPDATE contratacion SET con_estado = ? WHERE con_id = ?";
        $stmtUpdate = $mysqli->prepare($sqlUpdateContrato);

        if ($stmtUpdate) {
            $nuevoEstadoContrato = 'EN CURSO';
            // con_id lo estás recibiendo por POST y lo usas arriba, aquí también:
            $stmtUpdate->bind_param("ss", $nuevoEstadoContrato, $con_id);
            $stmtUpdate->execute();
            $stmtUpdate->close();
        }
        
        echo json_encode([
            "success" => true,
            "message" => "Datos guardados correctamente."
        ]);
        // Preparar datos para la tabla de copia
        $cop_estado = 'PENDIENTE';
        $cop_estado_gerencia = 'PENDIENTE';
        $cop_estado_cargado = 'PENDIENTE';
        $cop_estado_usuario = 'PENDIENTE';
        $cop_rechazo_usuario = '';
        $cop_motivo_devolucion = '';
        $cop_motivo_rechazo = '';

        // Insertar en copia_solicitudes_otrosi
        $sqlCopia = "INSERT INTO copia_solicitudes_otrosi (
        con_num_identidad,
        cop_id,
        cop_tipo_otrosi,
        cop_fecha_inicio,
        cop_nuevo_rol,
        cop_nuevo_proyecto,
        cop_nuevo_salario,
        cop_nueva_fecha_final,
        cop_duracion,
        cop_valor_otrosi,
        cop_motivo,
        cop_archivo_otrosi,
        cop_estado,
        cop_estado_gerencia,
        cop_estado_cargado,
        cop_estado_usuario,
        cop_rechazo_usuario,
        cop_motivo_devolucion,
        cop_motivo_rechazo,
        cop_usuario_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmtCopia = $mysqli->prepare($sqlCopia);

        if ($stmtCopia) {
            $stmtCopia->bind_param(
                "sissssissssssssssssi",
                $con_num_identidad,
                $con_id,
                $sol_tipo_otrosi,
                $sol_fecha_inicio,
                $sol_nuevo_rol,
                $sol_nuevo_proyecto,
                $sol_nuevo_salario,
                $sol_nueva_fecha_final,
                $sol_duracion,
                $sol_valor_otrosi,
                $sol_motivo,
                $sol_archivo_otrosi,
                $cop_estado,
                $cop_estado_gerencia,
                $cop_estado_cargado,
                $cop_estado_usuario,
                $cop_rechazo_usuario,
                $cop_motivo_devolucion,
                $cop_motivo_rechazo,
                $sol_usuario_id
            );
            $stmtCopia->execute();
            $stmtCopia->close();
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Error al guardar los datos.",
            "error" => $stmt->error
        ]);
    }

    $stmt->close();
    $mysqli->close();
}
