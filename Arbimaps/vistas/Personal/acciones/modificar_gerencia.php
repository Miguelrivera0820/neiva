<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

require_once dirname(__DIR__, 4) . '/conexion.php';
if (!isset($mysqli) || $mysqli === null) {
    http_response_code(500);
    exit('Error: no se encontró la conexión $mysqli. Revisa conexion.php');
}

neiva_bootstrap();
neiva_require_methods('POST', true);
neiva_require_permission('menu.personalGerencia', $PERMISOS, true);
neiva_require_csrf('global', true);

// usuario en sesión
$idUsuario = $_SESSION['id_usuario'] ?? null;

header('Content-Type: application/json; charset=utf-8');

// helper de saneamiento usando la conexión $mysqli
function sanitize($value) {
    global $mysqli;
    if ($value === null) return '';
    return $mysqli->real_escape_string(trim((string)$value));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion !== 'devolver') {
        echo json_encode(["status" => "error", "message" => "Acción no válida"]);
        exit();
    }



    $rec_num_identidad = sanitize($_POST['con_num_identidad'] ?? '');
    $con_id = sanitize($_POST['con_id'] ?? '');
    $id = sanitize($_POST['id'] ?? '');

    if (!empty($rec_num_identidad)) {
        // DATOS NECESARIOS
        $sol_fecha_inicio = sanitize($_POST['sol_fecha_inicio'] ?? '');
        $sol_nueva_fecha_final = sanitize($_POST['sol_nueva_fecha_final'] ?? '');
        $sol_duracion = sanitize($_POST['sol_duracion'] ?? '');
        $sol_nuevo_salario = isset($_POST['sol_nuevo_salario']) && is_numeric($_POST['sol_nuevo_salario']) && $_POST['sol_nuevo_salario'] > 0
            ? sanitize($_POST['sol_nuevo_salario'])
            : null;

        $sol_valor_otrosi = isset($_POST['sol_valor_otrosi']) && is_numeric($_POST['sol_valor_otrosi']) && $_POST['sol_valor_otrosi'] > 0
            ? sanitize($_POST['sol_valor_otrosi'])
            : null;


        $sol_motivo_devolucion = sanitize($_POST['sol_motivo_devolucion'] ?? '');

        // Datos para la copia (asegúrate que todos se envíen desde el frontend)
        $sol_tipo_otrosi = sanitize($_POST['sol_tipo_otrosi'] ?? '');
        $sol_nuevo_rol = sanitize($_POST['sol_nuevo_rol'] ?? '');
        $sol_nuevo_proyecto = sanitize($_POST['sol_nuevo_proyecto'] ?? '');
        $sol_valor_otrosi = sanitize($_POST['sol_valor_otrosi'] ?? '');
        $sol_motivo = sanitize($_POST['sol_motivo'] ?? '');
        $sol_archivo_otrosi = sanitize($_POST['sol_archivo_otrosi'] ?? '');
        $cop_estado = "PENDIENTE";
        $cop_estado_gerencia = "DEVUELTO";
        $cop_estado_cargado = "PENDIENTE";
        $cop_estado_usuario = "PENDIENTE";
        $cop_rechazo_usuario = "NO";
        $cop_motivo_rechazo = "";
        $sol_usuario_id = $idUsuario;

        $whereClause = !empty($id) ? "id = '$id'" : "con_num_identidad = '$rec_num_identidad'";

        $sql = "UPDATE solicitudes_otrosi SET
        sol_fecha_inicio = ?,
        sol_nueva_fecha_final = ?,
        sol_duracion = ?,
        sol_nuevo_salario = ?,
        sol_motivo_devolucion = ?,
        sol_estado_gerencia = 'DEVUELTO',
        sol_valor_otrosi = ?
    WHERE $whereClause";

        $stmtUpdate = $mysqli->prepare($sql);
        if ($stmtUpdate) {
            // bind params (use null where appropriate)
            $param_salario = $sol_nuevo_salario !== null ? $sol_nuevo_salario : null;
            $param_valor = $sol_valor_otrosi !== null ? $sol_valor_otrosi : null;
            $stmtUpdate->bind_param('ssssss', $sol_fecha_inicio, $sol_nueva_fecha_final, $sol_duracion, $param_salario, $sol_motivo_devolucion, $param_valor);
            $resultado = $stmtUpdate->execute();
            $stmtUpdate->close();
        } else {
            echo json_encode(["status" => "error", "message" => "Error preparando actualización: " . $mysqli->error]);
            exit;
        }

        if ($resultado) {
            // INSERTAR EN COPIA
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
                    $rec_num_identidad,
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
                    $sol_motivo_devolucion,
                    $cop_motivo_rechazo,
                    $sol_usuario_id
                );

                if ($stmtCopia->execute()) {
                    echo json_encode(["status" => "ok", "message" => "Actualización y copia exitosa"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Error al guardar copia: " . $stmtCopia->error]);
                }

                $stmtCopia->close();
            } else {
                echo json_encode(["status" => "error", "message" => "Error preparando copia: " . $conn->error]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Error al actualizar: " . $mysqli->error]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Datos inválidos: falta número de identidad"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Método no permitido"]);
}

$mysqli->close();
