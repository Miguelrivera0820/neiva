<?php
$where = "";
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../index.php");
    exit();
}

$idUsuario = $_SESSION['id_usuario'];

$sql = "SELECT rol_usuario, nombre_usuario FROM usuarios_cons WHERE id_usuario = ?";
$stmtUser = $mysqli->prepare($sql);
$stmtUser->bind_param("i", $idUsuario);
$stmtUser->execute();
$stmtUser->bind_result($rolUsuarioDB, $nombreUsuarioDB);
$stmtUser->fetch();
$stmtUser->close();

$rolUsuario = $_SESSION['rol_usuario'];
$rolesPermitidos = array("administrador", "director_catastro", "Directivos", "soporte");

if (!in_array($rolUsuario, $rolesPermitidos)) {
    header("Location: ../../acceso_denegado.php");
    exit();
}

$nombre = $_SESSION['nombre_usuario'];
?>

<div class="container-fluid">
    <br><br>
    <h2 class="mt-7 text-center" style="color: #3f51b5;">
        <b>Solicitudes Devueltas</b>
    </h2>
    <br><br>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-pagos table-striped mb-0" id="dataTable" width="100%" cellspacing="0">
                <thead class="table-primary">
                    <tr class="pendiente">
                        <th class="text-center">N° Radicado</th>
                        <th class="text-center">Nombres</th>
                        <th class="text-center">Apellidos</th>
                        <th class="text-center">N° identidad</th>
                        <th class="text-center">Cantidad de Días</th>
                        <th class="text-center">Valor del Día</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Tipo de Actividad</th>
                    </tr>
                </thead>
                <tbody>

                    <?php
                    $sql2 = "
                    SELECT 
                        id,
                        sb_baqueano_nombre,
                        sb_baqueano_apellido,
                        sb_numero_identidad,
                        sb_dias_calculados,
                        sb_cobro_diario,
                        sb_valor_cobrar,
                        sb_tipo_actividad
                    FROM solicitud_baqueanos 
                    WHERE 
                        (sb_estado_gerencia = 'DEVUELTO' 
                        OR sb_estado_operaciones = 'DEVUELTO')
                        AND sb_tipo_motivo = 'TIEMPOS'
                    ORDER BY id ASC
                ";

                    $stmt = $mysqli->prepare($sql2);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                    ?>
                            <tr>
                                <td class="text-center">
                                    <a href="informacion_sol_lider_devuelta.php?id=<?= urlencode($row['id']) ?>"
                                        class="btn btn-link">
                                        ARB_<?= htmlspecialchars($row['id']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($row['sb_baqueano_nombre']) ?></td>
                                <td><?= htmlspecialchars($row['sb_baqueano_apellido']) ?></td>
                                <td><?= htmlspecialchars($row['sb_numero_identidad']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['sb_dias_calculados']) ?></td>
                                <td class="text-center">
                                    $<?= number_format($row['sb_cobro_diario'], 0, ',', '.') ?>
                                </td>
                                <td class="text-center">
                                    $<?= number_format($row['sb_valor_cobrar'], 0, ',', '.') ?>
                                </td>
                                <td><?= htmlspecialchars($row['sb_tipo_actividad']) ?></td>
                            </tr>
                        <?php
                        endwhile;
                    else:
                        ?>
                        <tr>
                            <td colspan="8" class="text-center">
                                No se encontraron registros disponibles
                            </td>
                        </tr>
                    <?php
                    endif;
                    $stmt->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>