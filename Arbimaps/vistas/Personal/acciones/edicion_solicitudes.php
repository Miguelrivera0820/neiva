<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../../conexion.php';

if (!isset($mysqli) || $mysqli === null) {
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "No se encontró la conexión \$mysqli. Revisa conexion.php"]);
    exit;
}

$conn = $mysqli;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pendientes_otrosi.php');
    exit;
}

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["status" => "error", "message" => "No autenticado"]);
    exit();
}

$idUsuario = $_SESSION['id_usuario'];

function limpiar($dato, $conexion)
{
    return mysqli_real_escape_string($conexion, trim($dato));
}

$accion = limpiar($_POST['accion'] ?? '', $conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $rec_num_identidad = limpiar($_POST['con_num_identidad'] ?? '', $conn);
    $con_id            = limpiar($_POST['con_id'] ?? '', $conn);
    $id                = limpiar($_POST['id'] ?? '', $conn);

    if (!empty($rec_num_identidad) && !empty($con_id)) {
        $sol_fecha_inicio      = limpiar($_POST['sol_fecha_inicio'] ?? '', $conn);
        $sol_nuevo_rol         = limpiar($_POST['sol_nuevo_rol'] ?? '', $conn);
        $sol_nuevo_proyecto    = limpiar($_POST['sol_nuevo_proyecto'] ?? '', $conn);
        $sol_nuevo_salario = isset($_POST['sol_nuevo_salario']) && is_numeric($_POST['sol_nuevo_salario']) && $_POST['sol_nuevo_salario'] > 0
            ? limpiar($_POST['sol_nuevo_salario'], $conn)
            : null;
        $sol_valor_otrosi = isset($_POST['sol_valor_otrosi']) && is_numeric($_POST['sol_valor_otrosi']) && $_POST['sol_valor_otrosi'] > 0
            ? limpiar($_POST['sol_valor_otrosi'], $conn)
            : null;
        $sol_nueva_fecha_final = limpiar($_POST['sol_nueva_fecha_final'] ?? '', $conn);
        $sol_duracion          = limpiar($_POST['sol_duracion'] ?? '', $conn);
        $sol_fecha_solicitud   = date('Y-m-d');

        $query_estado = "SELECT sol_estado_gerencia, sol_estado 
                    FROM solicitudes_otrosi 
                    WHERE con_num_identidad = '$rec_num_identidad'";

        $resultado_estado = mysqli_query($conn, $query_estado);

        $set_gerencia = '';
        $sol_estado   = 'PENDIENTE'; 

        if ($resultado_estado && $row = mysqli_fetch_assoc($resultado_estado)) {
            if ($row['sol_estado_gerencia'] === 'DEVUELTO') {
                $set_gerencia = ", sol_estado_gerencia = 'PENDIENTE', sol_motivo_devolucion = NULL";

                if ($row['sol_estado'] === 'NO VIABLE') {
                    $sol_estado = 'PENDIENTE';
                }
            }
        }

        $sql = "UPDATE solicitudes_otrosi SET
                    sol_fecha_inicio = '$sol_fecha_inicio',
                    sol_nuevo_rol = '$sol_nuevo_rol',
                    sol_nuevo_proyecto = '$sol_nuevo_proyecto',
                    sol_nuevo_salario = " . ($sol_nuevo_salario !== null ? $sol_nuevo_salario : "NULL") . ",
                    sol_nueva_fecha_final = '$sol_nueva_fecha_final',
                    sol_duracion = '$sol_duracion',
                    sol_estado = '$sol_estado',
                    sol_valor_otrosi = " . ($sol_valor_otrosi !== null ? $sol_valor_otrosi : "NULL") . "
                    $set_gerencia
                WHERE con_num_identidad = '$rec_num_identidad'";

        $resultado = mysqli_query($conn, $sql);

        if ($resultado) {
            echo json_encode(["status" => "ok", "message" => "Actualización exitosa"]);
            exit();
        } else {
            echo json_encode([
                "status"  => "error",
                "message" => "Error al actualizar: " . mysqli_error($conn)
            ]);
            exit();
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Datos inválidos"]);
        exit();
    }
} else {
    echo json_encode(["status" => "error", "message" => "Método no permitido"]);
    exit();
}
