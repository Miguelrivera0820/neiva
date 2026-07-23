<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';
require_once __DIR__ . '/../../../../../vendor/autoload.php';

neiva_bootstrap();
neiva_require_methods('GET');
neiva_require_permission('menu.tramites', $PERMISOS);

date_default_timezone_set('America/Bogota');

use Dompdf\Dompdf;
use Dompdf\Options;

if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->set_charset('utf8mb4');
}

$sql_tramites = "
    SELECT *
    FROM (
        SELECT
            at.asignacion_cod_tramite AS historial_cod_tramite,
            at.asignacion_nombre_usuario AS historial_nombre_usuario,
            at.asignacion_apellido_usuario AS historial_apellido_usuario,
            at.asignacion_rol_usuario AS historial_rol_usuario,
            at.asignacion_estado_tramite AS historial_estado_tramite,
            at.fecha_limite,
            at.asignacion_fecha_tramite AS fecha_movimiento,
            'asignacion' AS etapa
        FROM asignacion_tramite at

        UNION ALL

        SELECT
            ea.entrega_cod_tramite AS historial_cod_tramite,
            ea.entrega_nombre_usuario AS historial_nombre_usuario,
            ea.entrega_apellido_usuario AS historial_apellido_usuario,
            ea.entrega_rol_usuario AS historial_rol_usuario,
            ea.historial_estado_tramite,
            ea.fecha_limite,
            COALESCE(ea.fecha_creacion, ea.historial_fecha_tramite) AS fecha_movimiento,
            'revision' AS etapa
        FROM entrega_asignacion ea
    ) AS tramites_actuales
    ORDER BY fecha_movimiento DESC, etapa DESC
";

$resultado = $mysqli->query($sql_tramites);
$tramites_raw = [];
while ($resultado && ($row = $resultado->fetch_assoc())) {
    $cod = $row['historial_cod_tramite'] ?? '';
    if ($cod === '') {
        continue;
    }
    $tramites_raw[] = $row;
}

$cerrados = [];
$sql_cerrados = "
    SELECT cod_radicacion_tramite
    FROM procede_tramite
    UNION
    SELECT cod_radicacion_tramite
    FROM no_procede_completar
";
$res_cerrados = $mysqli->query($sql_cerrados);
while ($res_cerrados && ($row = $res_cerrados->fetch_assoc())) {
    $cerrados[$row['cod_radicacion_tramite']] = true;
}

$tramites = [];
$tramites_vistos = [];
foreach ($tramites_raw as $row) {
    $cod = $row['historial_cod_tramite'] ?? '';
    if ($cod === '' || isset($tramites_vistos[$cod]) || isset($cerrados[$cod])) {
        continue;
    }

    $tramites_vistos[$cod] = true;
    $tramites[] = $row;
}

$total_asignaciones = 0;
$total_vencidas = 0;
$total_cul = 0;
$total_rad = 0;

$total_asignaciones = count($tramites);

$hoy_vencidas = new DateTime();
foreach ($tramites as $tramite_vencida) {
    $fecha_limite_vencida = $tramite_vencida['fecha_limite'] ?? '';
    if ($fecha_limite_vencida === '' || $fecha_limite_vencida === '0000-00-00') {
        continue;
    }

    $fecha_limite_obj = new DateTime($fecha_limite_vencida);
    if ((int)$hoy_vencidas->diff($fecha_limite_obj)->format('%r%a') < 0) {
        $total_vencidas++;
    }
}

$res = $mysqli->query("SELECT COUNT(*) AS total_cul FROM procede_tramite");
if ($res && $row = $res->fetch_assoc()) {
    $total_cul = (int)$row['total_cul'];
}

$res = $mysqli->query("SELECT COUNT(*) AS total_rad FROM tramite_radicacion");
if ($res && $row = $res->fetch_assoc()) {
    $total_rad = (int)$row['total_rad'];
}

$conteos = [];
$detalle = [];
$hoy = new DateTime();

foreach ($tramites as $tramite) {
    $cod = $tramite['historial_cod_tramite'] ?? '';
    if ($cod === '' || isset($cerrados[$cod])) {
        continue;
    }

    $responsable = trim(($tramite['historial_nombre_usuario'] ?? '') . ' ' . ($tramite['historial_apellido_usuario'] ?? ''));
    if ($responsable === '') {
        $responsable = 'Sin responsable';
    }
    $rol = $tramite['historial_rol_usuario'] ?? '';
    $fecha_limite = $tramite['fecha_limite'] ?? '';

    if (!isset($conteos[$responsable])) {
        $conteos[$responsable] = [
            'rol' => $rol,
            'asignado' => 0,
            'en_asignacion' => 0,
            'en_revision' => 0,
            'a_tiempo' => 0,
            'a_vencer' => 0,
            'vencido' => 0,
            'caducado' => 0,
        ];
    }

    $conteos[$responsable]['asignado']++;
    if (($tramite['etapa'] ?? '') === 'asignacion') {
        $conteos[$responsable]['en_asignacion']++;
    } else {
        $conteos[$responsable]['en_revision']++;
    }

    $dias = null;
    if (!empty($fecha_limite) && $fecha_limite !== '0000-00-00') {
        $fechaLimiteObj = new DateTime($fecha_limite);
        $dias = (int)$hoy->diff($fechaLimiteObj)->format('%r%a');

        if ($dias >= 3) {
            $conteos[$responsable]['a_tiempo']++;
        } elseif ($dias >= 1 && $dias <= 2) {
            $conteos[$responsable]['a_vencer']++;
        } elseif ($dias < 0 && abs($dias) <= 10) {
            $conteos[$responsable]['vencido']++;
        } elseif (abs($dias) > 10) {
            $conteos[$responsable]['caducado']++;
        }
    }

    $detalle[] = [
        'cod' => $cod,
        'responsable' => $responsable,
        'rol' => $rol,
        'etapa' => $tramite['etapa'] ?? '',
        'estado' => $tramite['historial_estado_tramite'] ?? '',
        'fecha_limite' => $fecha_limite,
        'dias' => $dias,
    ];
}

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10pt;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }
        .header {
            background: #002F55;
            color: #fff;
            padding: 18px 22px;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            margin: 0 0 4px 0;
        }
        .subtitle {
            font-size: 10px;
            margin: 0;
            opacity: 0.9;
        }
        .section {
            padding: 16px 22px;
        }
        .stats {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
            margin-bottom: 12px;
        }
        .stats td {
            border: 1px solid #dbe3ea;
            border-radius: 8px;
            padding: 10px;
            vertical-align: top;
            width: 25%;
        }
        .stat-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 8px;
        }
        .stat-value {
            font-size: 22px;
            font-weight: bold;
            color: #002F55;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #002F55;
            margin: 16px 0 8px 0;
        }
        table.report {
            width: 100%;
            border-collapse: collapse;
        }
        table.report th,
        table.report td {
            border: 1px solid #d1d5db;
            padding: 6px 5px;
            font-size: 8.8pt;
        }
        table.report th {
            background: #eaf2f8;
            color: #002F55;
            text-align: left;
        }
        .right { text-align: right; }
        .center { text-align: center; }
        .bar-wrap {
            background: #edf2f7;
            border-radius: 999px;
            height: 10px;
            overflow: hidden;
        }
        .bar {
            background: #0f5699;
            height: 10px;
            border-radius: 999px;
        }
        .muted { color: #6b7280; }
        .small { font-size: 8pt; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">Reporte de tablero de trámites</p>
        <p class="subtitle">Generado el <?php echo date('Y-m-d H:i'); ?> | Trámites radicados: <?php echo $total_rad; ?> | Completados: <?php echo $total_cul; ?> | Vencidos: <?php echo $total_vencidas; ?></p>
    </div>

    <div class="section">
        <table class="stats">
            <tr>
                <td><div class="stat-label">Trámites radicados</div><div class="stat-value"><?php echo $total_rad; ?></div></td>
                <td><div class="stat-label">Trámites asignados</div><div class="stat-value"><?php echo $total_asignaciones; ?></div></td>
                <td><div class="stat-label">Trámites culminados</div><div class="stat-value"><?php echo $total_cul; ?></div></td>
                <td><div class="stat-label">Trámites vencidos</div><div class="stat-value"><?php echo $total_vencidas; ?></div></td>
            </tr>
        </table>

        <div class="section-title">Distribución por responsable</div>
        <table class="report">
            <thead>
                <tr>
                    <th>Responsable</th>
                    <th>Rol</th>
                    <th class="center">Total</th>
                    <th class="center">En asignación</th>
                    <th class="center">En revisión</th>
                    <th class="center">A tiempo</th>
                    <th class="center">A vencer</th>
                    <th class="center">Vencido</th>
                    <th class="center">Caducado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($conteos as $responsable => $data): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($responsable); ?></td>
                        <td><?php echo htmlspecialchars($data['rol']); ?></td>
                        <td class="center"><?php echo (int)$data['asignado']; ?></td>
                        <td class="center"><?php echo (int)$data['en_asignacion']; ?></td>
                        <td class="center"><?php echo (int)$data['en_revision']; ?></td>
                        <td class="center"><?php echo (int)$data['a_tiempo']; ?></td>
                        <td class="center"><?php echo (int)$data['a_vencer']; ?></td>
                        <td class="center"><?php echo (int)$data['vencido']; ?></td>
                        <td class="center"><?php echo (int)$data['caducado']; ?></td>
                    </tr>
                    <tr>
                        <td colspan="9">
                            <div class="small muted" style="margin-bottom:4px;">Proporción de trámites del responsable</div>
                            <div class="bar-wrap">
                                <div class="bar" style="width: <?php echo max(8, min(100, ((int)$data['asignado'] / max(1, $total_asignaciones)) * 100)); ?>%;"></div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($conteos)): ?>
                    <tr><td colspan="9" class="center muted">No hay trámites activos para mostrar.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="page-break"></div>
        <div class="section-title">Listado de trámites activos</div>
        <table class="report">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Responsable</th>
                    <th>Rol</th>
                    <th>Etapa</th>
                    <th>Estado</th>
                    <th>Fecha límite</th>
                    <th class="center">Días</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalle as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['cod']); ?></td>
                        <td><?php echo htmlspecialchars($item['responsable']); ?></td>
                        <td><?php echo htmlspecialchars($item['rol']); ?></td>
                        <td><?php echo htmlspecialchars($item['etapa']); ?></td>
                        <td><?php echo htmlspecialchars($item['estado']); ?></td>
                        <td><?php echo htmlspecialchars($item['fecha_limite']); ?></td>
                        <td class="center"><?php echo $item['dias'] === null ? '-' : (int)$item['dias']; ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($detalle)): ?>
                    <tr><td colspan="7" class="center muted">No hay trámites activos para listar.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php
$html = ob_get_clean();

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

header('Content-Type: application/pdf');
$dompdf->stream('reporte_dashboard_tramites.pdf', ['Attachment' => true]);
exit;
