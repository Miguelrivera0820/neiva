<?php
$error_login = "";
if ($_POST) {
    $usuario_cons = $_POST['usuario_cons'];
    $password_cons = $_POST['password_cons'];

    $sql = "SELECT id_usuario, usuario_cons, password_cons, nombre_usuario, apellido_usuario, rol_usuario, cedula_usuario FROM usuarios_cons WHERE usuario_cons='$usuario_cons'";
    $resultado = $mysqli->query($sql);
    if (!$resultado) {
        die("Error en la consulta SQL: " . $mysqli->error);
    }
    $num = $resultado->num_rows;
    if ($num > 0) {
        $row = $resultado->fetch_assoc();
        if ($password_cons == $row['password_cons']) {
            $_SESSION['id_usuario'] = $row['id_usuario'];
            $_SESSION['usuario_cons'] = $row['usuario_cons'];
            $_SESSION['nombre_usuario'] = $row['nombre_usuario'];
            $_SESSION['apellido_usuario'] = $row['apellido_usuario'];
            $_SESSION['rol_usuario'] = $row['rol_usuario'];
            $_SESSION['cedula_usuario'] = $row['cedula_usuario'];
            header("Location: inicio.php");
            exit();
        } else {
            $error_login = "Usuario o contraseña incorrectos.";
        }
    } else {
        $error_login = "Usuario o contraseña incorrectos.";
    }
}
$cedula_usuario = $_SESSION['cedula_usuario'];
$sql = "SELECT 
    at.asignacion_fecha_tramite,
    at.asignacion_cod_tramite,
    at.asignacion_id_tramite,
    (
        SELECT at2.creacion_tram_rol_usuario
        FROM asignacion_tramite at2
        WHERE at2.asignacion_cod_tramite = at.asignacion_cod_tramite
        ORDER BY at2.asignacion_fecha_tramite DESC
        LIMIT 1
    ) AS creacion_tram_rol_usuario,
    tr.npn_predio,
    tr.mutacion_tramite,
    his.est_ventanilla,
    his.est_procedencia,
    his.est_atencion_procedencia,
    his.est_conservacion,
    his.est_lider_juridico,
    his.est_control_calidad,
    his.est_lider_economico,
    his.est_consolidacion,
    his.est_edicion,
    his.est_avaluos,
    his.est_reconocimiento,
    his.est_director,
    at.asignacion_estado_tramite,
    (
        SELECT et.es_nombre
        FROM estados_tramite et
        WHERE et.cod_tramite = at.asignacion_cod_tramite
        ORDER BY et.id DESC
        LIMIT 1
    ) AS es_nombre,
    (
        SELECT ea.entrega_cod_tramite
        FROM entrega_asignacion ea
        WHERE ea.entrega_cod_tramite = at.asignacion_cod_tramite
        ORDER BY ea.entrega_id_tramite DESC
        LIMIT 1
    ) AS entrega_cod_tramite 
FROM asignacion_tramite AS at
INNER JOIN tramite_radicacion AS tr 
    ON at.asignacion_cod_tramite = tr.cod_tramite
INNER JOIN historial_asignacion AS his
    ON his.historial_cod_tramite = tr.cod_tramite
WHERE at.asignacion_cc_usuario = ?
AND at.asignacion_id_tramite = (
    SELECT MAX(at2.asignacion_id_tramite)
    FROM asignacion_tramite at2
    WHERE at2.asignacion_cod_tramite = at.asignacion_cod_tramite
)
AND NOT EXISTS (
    SELECT 1
    FROM entrega_asignacion ea_mov
    WHERE ea_mov.entrega_id_tramite = at.asignacion_id_tramite
)
AND NOT EXISTS (
    SELECT 1 FROM tramites_cancelados tc
    WHERE tc.cod_tramite = at.asignacion_cod_tramite
      AND tc.estado = 'CANCELADO'
)
UNION ALL
SELECT
    COALESCE(ea.fecha_creacion, ea.historial_fecha_tramite) AS asignacion_fecha_tramite,
    ea.entrega_cod_tramite AS asignacion_cod_tramite,
    ea.id_entrega_asignacion AS asignacion_id_tramite,
    ea.quien_entrego_rol AS creacion_tram_rol_usuario,
    tr.npn_predio,
    tr.mutacion_tramite,
    his.est_ventanilla,
    his.est_procedencia,
    his.est_atencion_procedencia,
    his.est_conservacion,
    his.est_lider_juridico,
    his.est_control_calidad,
    his.est_lider_economico,
    his.est_consolidacion,
    his.est_edicion,
    his.est_avaluos,
    his.est_reconocimiento,
    his.est_director,
    ea.historial_estado_tramite AS asignacion_estado_tramite,
    COALESCE((
        SELECT et.es_nombre
        FROM estados_tramite et
        WHERE et.cod_tramite = ea.entrega_cod_tramite
        ORDER BY et.id DESC
        LIMIT 1
    ), 'ASIGNADO') AS es_nombre,
    ea.entrega_cod_tramite AS entrega_cod_tramite
FROM entrega_asignacion AS ea
INNER JOIN tramite_radicacion AS tr
    ON ea.entrega_cod_tramite = tr.cod_tramite
INNER JOIN historial_asignacion AS his
    ON his.historial_cod_tramite = tr.cod_tramite
WHERE ea.entrega_cc_usuario = ?
AND UPPER(COALESCE(ea.historial_estado_tramite, '')) = 'REASIGNADO'
AND ea.id_entrega_asignacion = (
    SELECT MAX(ea2.id_entrega_asignacion)
    FROM entrega_asignacion ea2
    WHERE ea2.entrega_cod_tramite = ea.entrega_cod_tramite
)
AND NOT EXISTS (
    SELECT 1 FROM tramites_cancelados tc
    WHERE tc.cod_tramite = ea.entrega_cod_tramite
      AND tc.estado = 'CANCELADO'
)
ORDER BY asignacion_fecha_tramite DESC";



$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    die("Error en prepare: " . $mysqli->error);
}


$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ss", $cedula_usuario, $cedula_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

$tramites = [];
while ($row = $resultado->fetch_assoc()) {
    $tramites[] = $row;
}

$rol_usuario = $_SESSION['rol_usuario'];

$mapa_roles = [
    'coordinacion_tecnica' => 'est_conservacion',
    'lider_juridico'       => 'est_lider_juridico',
    'control_calidad'      => 'est_control_calidad',
    'lider_economico'      => 'est_lider_economico',
    'consolidacion'        => 'est_consolidacion',
    'editor'               => 'est_edicion',
    'avaluos'              => 'est_avaluos',
    'reconocedor'          => 'est_reconocimiento',
    'director'             => 'est_director',
    'ventanilla_catastral' => 'est_ventanilla',
    'procedencia_juridica' => 'est_procedencia',
];

?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
        background-color: #002F55 !important;
        border-color: #002F55 !important;
        color: #fff !important;
    }

    .dataTables_wrapper .dataTables_paginate .page-link:hover {
        background-color: #457b9d !important;
        color: #fff !important;
    }

    .dataTables_wrapper .dataTables_paginate .page-link {
        color: #002f55 !important;
        border-radius: 8px;
        margin: 0 2px;
    }
</style>

<div class="container-fluid">
    <div class=" text-center my-4">
        <h1 class="h2 mb-0 text-gray-1200"><B>TRAMITES ASIGNADOS</B></h1>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div
                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between" style="background-color: #022F55;">
                    <h6 class="m-0 font-weight-bold text-white">Tabla de información de mis asignaciones</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle text-white" href="#" role="button" id="dropdownMenuLink"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th style="text-align: center; vertical-align: middle;">Fecha Asignación</th>
                                    <th style="text-align: center; vertical-align: middle;">Cod. Tramite</th>
                                    <th style="text-align: center; vertical-align: middle;">Area Asignada</th>
                                    <th style="text-align: center; vertical-align: middle;">NPN Predio</th>
                                    <th style="text-align: center; vertical-align: middle;">Tipo de Trámite</th>
                                    <th style="text-align: center; vertical-align: middle;">Estado de Tramite</th>
                                    <th style="text-align: center; vertical-align: middle;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tramites as $tramite): ?>
                                    <tr>
                                        <td style="text-align: center; vertical-align: middle;"><?php echo date("Y-m-d H:i", strtotime($tramite['asignacion_fecha_tramite'])); ?></td>
                                        <td style="text-align: center; vertical-align: middle;"><a href="index.php?page=tramites/acciones/ver_tramite_rad&cod=<?php echo urlencode($tramite['asignacion_cod_tramite']); ?>">
                                                <?php echo htmlspecialchars($tramite['asignacion_cod_tramite']); ?></a>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;"><?php echo htmlspecialchars($tramite['creacion_tram_rol_usuario']); ?></td>
                                        <td style="text-align: center; vertical-align: middle;"><?php echo htmlspecialchars($tramite['npn_predio']); ?></td>
                                        <td style="text-align: center; vertical-align: middle;"><?php echo htmlspecialchars($tramite['mutacion_tramite']); ?></td>
                                        <td style="text-align: center; vertical-align: middle;"><?php echo htmlspecialchars($tramite['es_nombre']); ?></td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <a href="index.php?page=tramites/acciones/ver_tramite_rad&cod=<?php echo urlencode($tramite['asignacion_cod_tramite']); ?>"
                                                class="btn btn-sm text-white my-1" style="background-color: #022F55">Ver</a>

                                            <?php if ($rol_usuario === 'ventanilla_catastral'): ?>
                                                <a href="index.php?page=seguimiento/resolucion&cod=<?php echo urlencode($tramite['asignacion_cod_tramite']); ?>"
                                                    class="btn btn-sm btn-success my-1"><b>Respuesta</b></a>
                                            <?php endif; ?>

                                            <?php
                                            $mapa_roles = [
                                                'coordinacion_tecnica' => 'est_conservacion',
                                                'lider_juridico'       => 'est_lider_juridico',
                                                'control_calidad'      => 'est_control_calidad',
                                                'lider_economico'      => 'est_lider_economico',
                                                'consolidacion'        => 'est_consolidacion',
                                                'editor'               => 'est_edicion',
                                                'avaluos'              => 'est_avaluos',
                                                'reconocedor'          => 'est_reconocimiento',
                                                'director'             => 'est_director',
                                                'ventanilla_catastral' => 'est_ventanilla',
                                                'procedencia_juridica' => 'est_procedencia',
                                            ];

                                            $campo_estado   = $mapa_roles[$rol_usuario] ?? null;
                                            $estado_actual  = $campo_estado ? strtoupper(trim($tramite[$campo_estado] ?? '')) : null;
                                            $cod_tramite    = $tramite['asignacion_cod_tramite'];

                                            $campo_estado_rol = null;
                                            if ($rol_usuario === 'editor') {
                                                $campo_estado_rol = 'est_edicion';
                                            } elseif ($rol_usuario === 'reconocedor') {
                                                $campo_estado_rol = 'est_reconocimiento';
                                            }

                                            $estado_revision = 'PENDIENTE';
                                            if ($campo_estado_rol) {
                                                $sql_revision = "SELECT $campo_estado_rol
                                                                    FROM historial_revision
                                                                    WHERE historial_cod_tramite = ?
                                                                    ORDER BY id_revision DESC
                                                                    LIMIT 1";
                                                $stmt_rev = $mysqli->prepare($sql_revision);
                                                $stmt_rev->bind_param("s", $cod_tramite);
                                                $stmt_rev->execute();
                                                $res_rev = $stmt_rev->get_result();
                                                if ($row_rev = $res_rev->fetch_assoc()) {
                                                    $estado_revision = strtoupper(trim($row_rev[$campo_estado_rol] ?? 'PENDIENTE'));
                                                }
                                                $stmt_rev->close();
                                            }
                                            $es_nombre_actual = strtoupper(trim($tramite['es_nombre'] ?? ''));
                                            $esta_en_revision = ($es_nombre_actual === 'REVISION');
                                            $puede_mostrar_botones_revision = ($estado_revision === 'PENDIENTE' || $esta_en_revision);
                                            if (
                                                $campo_estado &&
                                                $estado_actual === 'PENDIENTE' &&
                                                $rol_usuario !== 'reconocedor' &&
                                                $puede_mostrar_botones_revision
                                            ): ?>
                                                <a href="index.php?page=tramites/acciones/asignar_tram_procedencia&cod=<?php echo urlencode($tramite['asignacion_cod_tramite']); ?>"
                                                    class="btn btn-sm fw-bold" style="background:#66CC99">ASIGNAR</a>
                                            <?php endif; ?>
                                            <?php
                                            $ocultar_boton = !$puede_mostrar_botones_revision;
                                            if (
                                                $rol_usuario !== 'ventanilla_catastral' &&
                                                $rol_usuario !== 'director_catastro' &&
                                                $rol_usuario !== 'consolidacion' &&
                                                $rol_usuario !== 'procedencia_juridica' &&
                                                $rol_usuario !== 'revision_juridica' &&
                                                $rol_usuario !== 'atencion_procedencia' &&
                                                $rol_usuario !== 'coordinacion_tecnica' &&
                                                $rol_usuario !== 'control_calidad' &&
                                                $rol_usuario !== 'componente_economico' &&
                                                $rol_usuario !== 'avaluos' &&
                                                ($estado_actual !== 'ENTREGADO' || $esta_en_revision) &&
                                                $estado_actual !== 'DEVUELTO' &&
                                                !$ocultar_boton &&
                                                $tramite['es_nombre'] !== 'DEVUELTO'
                                            ):
                                            ?>
                                                <a href="index.php?page=seguimiento/asignar_revision_segun_rol&cod=<?php echo urlencode($tramite['asignacion_cod_tramite']); ?>"
                                                    class="btn btn-sm btn-info"><b>REVISAR</b></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            language: window.neivaDataTablesLanguage ? window.neivaDataTablesLanguage() : {}
        });
    });
</script>
