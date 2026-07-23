<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods(['GET', 'POST'], true);
neiva_require_permission('menu.tramites', $PERMISOS, true);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

$mutacion_tramite = $_POST['mutacion_tramite'] ?? $_GET['mutacion_tramite'] ?? '';
$tipo_proceso_muta = $_POST['tipo_proceso_muta'] ?? $_GET['tipo_proceso_muta'] ?? '';
$fecha_respuesta_tramite = $_POST['fecha_respuesta_tramite'] ?? $_GET['fecha_respuesta_tramite'] ?? '';

$cod = $_GET['cod_tramite'] ?? '';
$response = ['success' => false];

if (!$cod) {
    echo json_encode(['success' => false, 'error' => 'Código de trámite no especificado.']);
    exit;
}

// --- Consulta principal ---
$sql = "SELECT 
            r.cod_tramite,
            r.fecha_rad,
            r.mutacion_tramite,
            r.primer_nombre_interesado,
            r.segundo_nombre_interesado,
            r.primer_apellido_interesado,
            r.segundo_apellido_interesado,
            r.telefono_interesado,
            r.correo_interesado,
            i.npn_predio_tram,
            r.fmi_predio,
            i.direccion_predio_terreno_tram,
            i.valor_avaluo_terreno_tram,
            i.destino_econ_predio_tram,
            i.area_terr_predio_tram,
            i.area_cons_predio_tram,
            i.nombre_propietario_tram,
            i.tipo_doc_propietario_tram,
            i.cedula_propietario_tram
        FROM tramite_radicacion r
        LEFT JOIN tramite_info_predio i ON i.info_cod_tramite = r.cod_tramite
        WHERE r.cod_tramite = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $cod);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // --- Formatear datos ---
    $nombre_interesado = trim(
        $row['primer_nombre_interesado'] . ' ' .
        $row['segundo_nombre_interesado'] . ' ' .
        $row['primer_apellido_interesado'] . ' ' .
        $row['segundo_apellido_interesado']
    );

    $propietarios = $row['nombre_propietario_tram'] . ' - ' .
                    $row['tipo_doc_propietario_tram'] . ' ' .
                    $row['cedula_propietario_tram'];

    // --- Datos de funcionario en sesión ---
    $nombre_funcionario = ($_SESSION['nombre_usuario'] ?? '') . ' ' . ($_SESSION['apellido_usuario'] ?? '');
    $cargo_funcionario  = $_SESSION['rol_usuario'] ?? '';

    // --- Construcción del cuerpo tipo oficio ---
    $fecha = new DateTime();
    $dia = $fecha->format('d');
    $meses = [
        1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    $mes = $meses[(int)$fecha->format('n')];
    $anio = $fecha->format('Y');

    $plantilla = "
        <b>Asunto:</b> Comunicación sobre la procedencia del trámite <b>{$row['cod_tramite']}</b>.
        <br><br>
        Estimado(a) Señor(a)/Sr(a). <b>{$nombre_interesado}</b>:
        <br><br>
        Con relación a su solicitud presentada el día <b>{$row['fecha_rad']}</b> referente a <b>{$mutacion_tramite}</b> con actividad <b>{$tipo_proceso_muta}</b>, nos permitimos informarle que, tras la revisión pertinente, su petición ha sido aceptada para su procedencia y se dará solución en los próximos días.<br><br>
        En este sentido, le comunicamos que el proceso se encuentra en curso y estará resuelto en la fecha <b>{$fecha_respuesta_tramite}</b>, o bien, en el transcurso del presente mes, según lo previsto en nuestros plazos de trámite.<br><br>
        Agradecemos su paciencia y comprensión durante el desarrollo de este proceso. En caso de requerir información adicional, se le comunicará a través de los medios suministrados por el solicitante.<br><br>
        Cordialmente,<br><br><br>
        _________________________________<br>
        {$nombre_funcionario}<br>
        {$cargo_funcionario}<br>
        Dirección de Catastro ARBITRIUM S.A.S.
        ";

    // --- Respuesta JSON ---
    $response = [
        'success' => true,
        'tramite' => [
            'cod_tramite' => $row['cod_tramite'],
            'fecha_radicacion' => $row['fecha_rad'],
            'mutacion_tramite' => $mutacion_tramite ?: $row['mutacion_tramite'],
            'tipo_proceso_muta' => $tipo_proceso_muta,
            'fecha_respuesta_tramite' => $fecha_respuesta_tramite,
            'nombre_interesado' => $nombre_interesado,
            'correo_interesado' => $row['correo_interesado'],
            'telefono_interesado' => $row['telefono_interesado'],
            'direccion_predio' => $row['direccion_predio_terreno_tram'],
            'npn_predio_tram' => $row['npn_predio_tram'],
            'fmi_predio' => $row['fmi_predio'],
            'propietarios' => $propietarios,
            'nombre_funcionario' => $nombre_funcionario,
            'cargo_funcionario' => $cargo_funcionario,
            'plantilla' => nl2br($plantilla) // para mostrar en vista previa HTML
        ]
    ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
