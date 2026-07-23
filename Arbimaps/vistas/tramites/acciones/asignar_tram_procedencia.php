<?php
if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    echo "<script>alert('Sesión expirada o no iniciada.'); window.location='../../index.php';</script>";
    exit();
}


$rol_usuario = $_SESSION['rol_usuario'] ?? '';
$nombre_usuario = $_SESSION['nombre_usuario'] ?? '';
$apellido_usuario = $_SESSION['apellido_usuario'] ?? '';
$cedula_usuario = $_SESSION['cedula_usuario'] ?? '';

$cod_tramite = $_GET['cod'] ?? '';
$info_cod_tramite = $_GET['cod'] ?? '';


$sql = "SELECT * FROM tramite_radicacion WHERE cod_tramite = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $cod_tramite);
$stmt->execute();
$resultado = $stmt->get_result();
$tramite = $resultado->fetch_assoc();
$tipo_tramite_actual = strtoupper(trim($tramite['tipo_tramite'] ?? 'ACTUALIZACION'));
if (!in_array($tipo_tramite_actual, ['ACTUALIZACION', 'CONSERVACION'], true)) {
    $tipo_tramite_actual = 'ACTUALIZACION';
}

$sql2 = "SELECT * FROM tramite_info_predio WHERE info_cod_tramite = ?";
$stmt2 = $mysqli->prepare($sql2);
$stmt2->bind_param("s", $info_cod_tramite);
$stmt2->execute();
$resultado2 = $stmt2->get_result();
$info_predio = $resultado2->fetch_assoc();
$propietarios = $resultado2->fetch_all(MYSQLI_ASSOC);

$cod_base = preg_replace('/-\d{2}$/', '', $cod_tramite);
$anio = substr($cod_base, 4, 4);
$ruta_pdf = "tramites_conservacion/$anio/$cod_base/";
if (is_dir("tramites_conservacion/$anio/$cod_tramite")) {
    $ruta_pdf = "tramites_conservacion/$anio/$cod_tramite/";
}

// Detecta si el código de trámite tiene un sufijo tipo -01, -02, etc.
$tiene_sufijo = (bool) preg_match('/-\d{2}$/', $cod_tramite);



if (isset($_POST['solo_conteo']) && $_POST['solo_conteo'] == '1') {
    $rol = $_POST['rol'] ?? '';
    $cod = $_POST['cod_tramite'] ?? '';

    // Ajusta la consulta a tu tabla/criterio real del historial:
    // Ejemplo genérico:
    $sql = "SELECT COUNT(*) AS total
            FROM historial_asignacion
            WHERE rol_asignado = ? AND cod_tramite LIKE CONCAT(?, '%')";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ss", $rol, $cod);
    $stmt->execute();
    $res = $stmt->get_result();
    $fila = $res->fetch_assoc();
    $total = (int)($fila['total'] ?? 0);

    header('Content-Type: application/json');
    echo json_encode(['count' => $total]);
    exit;
}



$roles_por_rol = [
    "ventanilla_catastral" => ["procedencia_juridica"],
    "procedencia_juridica" => ["coordinacion_tecnica", "revision_juridica"],
    "coordinacion_tecnica" => ["control_calidad", "componente_economico", "revision_juridica", "director_catastro"],
    "revision_juridica" => ["control_calidad"],
    "control_calidad" => ["consolidacion"],
    "consolidacion" => ["editor"],
    "editor" => ["reconocedor"],
    "componente_economico" => ["avaluos"],
    "avaluos" => ["reconocedor"],
    "director_catastro" => ["ventanilla_catastral"],
    "atencion_procedencia" => ["coordinacion_tecnica"],
    "administrador" => [
        "ventanilla_catastral",
        "atencion_procedencia",
        "coordinacion_tecnica",
        "control_calidad",
        "componente_economico",
        "revision_juridica",
        "director_catastro",
        "consolidacion",
        "editor",
        "reconocedor",
        "procedencia_juridica",
        "avaluos"
    ]
];

$roles_por_rol_conservacion = [
    "ventanilla_catastral" => ["procedencia_juridica"],
    "procedencia_juridica" => ["editor"],
    "editor" => ["reconocedor", "coordinacion_tecnica"],
    "reconocedor" => ["editor"],
    "coordinacion_tecnica" => ["procedencia_juridica"],
    "administrador" => [
        "ventanilla_catastral",
        "procedencia_juridica",
        "editor",
        "reconocedor",
        "coordinacion_tecnica"
    ]
];

if ($tipo_tramite_actual === 'CONSERVACION' && $rol_usuario === 'procedencia_juridica') {
    $sqlEtapaFinal = "SELECT 1
                      FROM asignacion_tramite
                      WHERE asignacion_cod_tramite = ?
                        AND (asignacion_rol_usuario = 'coordinacion_tecnica'
                             OR creacion_tram_rol_usuario = 'coordinacion_tecnica')
                      LIMIT 1";
    $stmtEtapaFinal = $mysqli->prepare($sqlEtapaFinal);
    $es_etapa_final_conservacion = false;
    if ($stmtEtapaFinal) {
        $stmtEtapaFinal->bind_param("s", $cod_tramite);
        $stmtEtapaFinal->execute();
        $es_etapa_final_conservacion = (bool)$stmtEtapaFinal->get_result()->fetch_assoc();
        $stmtEtapaFinal->close();
    }

    if ($es_etapa_final_conservacion) {
        $roles_por_rol_conservacion["procedencia_juridica"] = ["ventanilla_catastral"];
    }
}

$idUsuario = $_SESSION['id_usuario'];
$mapa_roles_actual = $tipo_tramite_actual === 'CONSERVACION'
    ? $roles_por_rol_conservacion
    : $roles_por_rol;
$roles_disponibles = $mapa_roles_actual[$rol_usuario] ?? [];


/** Mapa botón-rol para clases de color del badge */
$mapRolIndex = [
    'ventanilla_catastral' => 1,
    'procedencia_juridica' => 2,
    'atencion_procedencia' => 3,
    'revision_juridica'    => 4,
    'coordinacion_tecnica' => 5,
    'control_calidad'      => 6,
    'consolidacion'        => 7,
    'componente_economico' => 8,
    'editor'               => 9,
    'avaluos'              => 10,
    'reconocedor'          => 11,
    'director_catastro'    => 12,
];

function contarNotificacionesPorRol(mysqli $mysqli, string $cod_tramite): array
{
    $counts = [];
    $sql = "SELECT asignacion_rol_usuario AS rol, COUNT(*) AS c
            FROM asignacion_tramite
            WHERE asignacion_cod_tramite = ?
            GROUP BY asignacion_rol_usuario";
    if ($st = $mysqli->prepare($sql)) {
        $st->bind_param("s", $cod_tramite);
        if ($st->execute()) {
            $rs = $st->get_result();
            while ($row = $rs->fetch_assoc()) {
                if (!empty($row['rol'])) {
                    $counts[$row['rol']] = (int)$row['c'];
                }
            }
        }
        $st->close();
    }
    // fallback: agrupar por creador si existe
    $sql2 = "SELECT creacion_tram_rol_usuario AS rol, COUNT(*) AS c
             FROM asignacion_tramite
             WHERE asignacion_cod_tramite = ?
             GROUP BY creacion_tram_rol_usuario";
    if ($st2 = $mysqli->prepare($sql2)) {
        $st2->bind_param("s", $cod_tramite);
        if ($st2->execute()) {
            $rs2 = $st2->get_result();
            while ($row2 = $rs2->fetch_assoc()) {
                $r = $row2['rol'] ?? '';
                if (!empty($r)) {
                    $counts[$r] = ($counts[$r] ?? 0) + (int)$row2['c'];
                }
            }
        }
        $st2->close();
    }
    return $counts;
}

function renderBadge(string $rol, array $counts, array $mapRolIndex): void
{
    $n = (int)($counts[$rol] ?? 0);
    if ($n > 0) {
        $idx = $mapRolIndex[$rol] ?? 1;
        echo '<span class="noti-badge noti-rol-' . (int)$idx . '">' . $n . '</span>';
    }
}

$notificaciones = contarNotificacionesPorRol($mysqli, $cod_tramite);
?>


<style>
    .btn-rol-1 {
        border-left: 3px solid #4DA6FF;
        border-top: 3px solid #4DA6FF;
        color: #002F55;
        transition: all 0.5s ease;
    }

    .btn-rol-1:hover {
        background-color: #4DA6FF;
        color: #5e5a5aff;
    }

    .btn-rol-2 {
        border-left: 3px solid #66CC99;
        border-top: 3px solid #66CC99;
        color: #002F55;
    }

    .btn-rol-2:hover {
        background-color: #66CC99;
        color: #5e5a5aff;
    }

    .btn-rol-3 {
        border-left: 3px solid #FFCC66;
        border-top: 3px solid #FFCC66;
        color: #002F55;
    }

    .btn-rol-3:hover {
        background-color: #FFCC66;
        color: #5e5a5aff;
    }


    .btn-rol-4 {
        border-left: 3px solid #FF9966;
        border-top: 3px solid #FF9966;
        color: #002F55;
    }

    .btn-rol-4:hover {
        background-color: #FF9966;
        color: #5e5a5aff;
    }


    .btn-rol-5 {
        border-left: 3px solid #B399FF;
        border-top: 3px solid #B399FF;
        color: #002F55;
    }

    .btn-rol-5:hover {
        background-color: #B399FF;
        color: #5e5a5aff;
    }

    .btn-rol-6 {
        border-left: 3px solid #4DD2C6;
        border-top: 3px solid #4DD2C6;
        color: #002F55;
    }

    .btn-rol-6:hover {
        background-color: #4DD2C6;
        color: #5e5a5aff;
    }

    .btn-rol-7 {
        border-left: 3px solid #FFB3B3;
        border-top: 3px solid #FFB3B3;
        color: #002F55;
    }

    .btn-rol-7:hover {
        background-color: #FFB3B3;
        color: #5e5a5aff;
    }

    .btn-rol-8 {
        border-left: 3px solid #CCFF99;
        border-top: 3px solid #CCFF99;
        color: #002F55;
    }

    .btn-rol-8:hover {
        background-color: #CCFF99;
        color: #5e5a5aff;
    }

    .btn-rol-9 {
        border-left: 3px solid #FFD1A9;
        border-top: 3px solid #FFD1A9;
        color: #002F55;
    }

    .btn-rol-9:hover {
        background-color: #FFD1A9;
        color: #5e5a5aff;
    }

    .btn-rol-10 {
        border-left: 3px solid #A6C8FF;
        border-top: 3px solid #A6C8FF;
        color: #002F55;
    }

    .btn-rol-10:hover {
        background-color: #A6C8FF;
        color: #5e5a5aff;
    }

    .btn-rol-11 {
        border-left: 3px solid #E6CCFF;
        border-top: 3px solid #E6CCFF;
        color: #002F55;
    }

    .btn-rol-11:hover {
        background-color: #E6CCFF;
        color: #5e5a5aff;
    }

    .btn-rol-12 {
        border-left: 3px solid #99CCFF;
        border-top: 3px solid #99CCFF;
        color: #002F55;
    }

    .btn-rol-12:hover {
        background-color: #99CCFF;
        color: #5e5a5aff;
    }

    /* Posicionar badge dentro del botón */
    .btn-rol-1,
    .btn-rol-2,
    .btn-rol-3,
    .btn-rol-4,
    .btn-rol-5,
    .btn-rol-6,
    .btn-rol-7,
    .btn-rol-8,
    .btn-rol-9,
    .btn-rol-10,
    .btn-rol-11,
    .btn-rol-12 {
        position: relative;
        overflow: visible;
    }

    .btn-ver:hover {
        background-color: #002F55;
        color: #FFFFFF !important;
    }

    .custom-radio-btn {
        border: 2px solid #ccc;
        border-radius: 8px;
        padding: 8px 16px;
        margin: 5px;
        cursor: pointer;
        transition: all 0.3s ease-in-out;
    }

    .custom-radio-btn:hover {
        background-color: #f1f1f1;
    }

    .custom-radio-btn input[type="radio"]:checked+label {
        font-weight: bold;
        color: white;
        background-color: #002F55;
        border-radius: 6px;
        padding: 4px 10px;
    }

    .info-banner {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border-left: 4px solid #1976d2;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        animation: fadeIn 2s ease-out;
    }

    .info-banner i {
        color: #1976d2;
        font-size: 1.5rem;
    }

    .contenteditable-document {
        height: 620px;
        font-family: Arial, sans-serif;
        line-height: 1.6;
        white-space: pre-wrap;
        border: 1px solid #ccc;
        padding: 15px;
        overflow-y: auto;
        resize: vertical;
    }

    /* Animaciones sutiles */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .btn.has-badge {
        position: relative;
    }

    /* .badge-pin removed: replaced by server-rendered .noti-badge */

    /* NOTI BADGE (colores por rol) */
    .noti-badge {
        position: absolute;
        top: -10px;
        right: -10px;
        border-radius: 999px;
        padding: 4px 8px;
        font-size: 0.78rem;
        font-weight: 700;
        line-height: 1;
        color: #fff;
        box-shadow: 0 0 4px rgba(0, 0, 0, 0.25);
        pointer-events: none;
        z-index: 5;
    }

    .noti-rol-1 {
        background-color: #4DA6FF;
    }

    .noti-rol-2 {
        background-color: #66CC99;
    }

    .noti-rol-3 {
        background-color: #FFCC66;
        color: #002F55;
    }

    .noti-rol-4 {
        background-color: #FF9966;
    }

    .noti-rol-5 {
        background-color: #B399FF;
    }

    .noti-rol-6 {
        background-color: #4DD2C6;
    }

    .noti-rol-7 {
        background-color: #FFB3B3;
    }

    .noti-rol-8 {
        background-color: #CCFF99;
        color: #002F55;
    }

    .noti-rol-9 {
        background-color: #FFD1A9;
        color: #002F55;
    }

    .noti-rol-10 {
        background-color: #A6C8FF;
    }

    .noti-rol-11 {
        background-color: #E6CCFF;
        color: #002F55;
    }

    .noti-rol-12 {
        background-color: #99CCFF;
    }

    .resaltar { font-weight: bold; }
</style>

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between my-4 px-4">
        <h1 class="h3 mb-0 fw-bold">ASIGNACIÓN DE TRÁMITES </h1>

        <div class="d-flex gap-2">
            <a href="index.php?page=tramites/base_catastral/predio_mutacion&cod=<?php echo urlencode($tramite['cod_tramite']); ?>"
                class="btn btn-md shadow-lg fw-bold btn-ver"
                style="border-left:2px solid #0b7a53;border-right:2px solid #0b7a53;">
                <i class="bi bi-table me-2 fw-bold"></i> Base catastral
            </a>
            <a href="index.php?page=tramites/acciones/ver_tramite_rad&cod=<?php echo urlencode($tramite['cod_tramite']); ?>"
                target="_blank"
                class="btn  btn-md shadow-lg animated-button fw-bold btn-ver"
                style="border-left:2px solid #022F55;border-right:2px solid #022F55;">
                <i class="bi bi-eye me-2 fw-bold "></i> Ver Radicación
            </a>
        </div>
    </div>

    <!-- Botones modales -->
    <div class="container my-2">
        <div class=" row d-flex align-items-stretch justify-content-center">

            <!-- Ventanilla Catastral-->
            <?php if (
                $rol_usuario !== 'ventanilla_catastral'
                && $rol_usuario !== 'director_catastro'
                && !$tiene_sufijo 
            ): ?>
                <div class="col-6 col-md-4 col-lg-2  align-items-center justify-content-center d-flex p-2">
                    <button
                        type="button"
                        class="btn h-100 w-100 shadow btn-rol-1"
                        onclick="verHistorialAsignacion('ventanilla_catastral', '<?php echo $cod_tramite; ?>')"
                        data-bs-toggle="modal"
                        data-bs-target="#modalHistorial">
                        <b>Ventanilla Catastral</b>
                        <?php renderBadge('ventanilla_catastral', $notificaciones, $mapRolIndex); ?>
                    </button>
                </div>
            <?php endif; ?>


            <!-- Procedencia juridica-->
            <?php if ($rol_usuario !== 'director_catastro' and $rol_usuario !== 'ventanilla_catastral'
            and $rol_usuario !== 'procedencia_juridica'): ?>
                <div class="col-6 col-md-4 col-lg-2  align-items-center justify-content-center d-flex p-2">
                    <button type="button"
                        class="btn h-100 w-100 shadow btn-rol-2"
                        onclick="verHistorialAsignacion('procedencia_juridica', '<?php echo $cod_tramite; ?>')"
                        data-bs-toggle="modal" data-bs-target="#modalHistorial">
                        <b>Procedencia Jurídica</b>
                        <?php renderBadge('procedencia_juridica', $notificaciones, $mapRolIndex); ?>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Atencion procedencia - asesor juridico-->

            <?php if (
                        $rol_usuario !== 'ventanilla_catastral' and $rol_usuario !== 'consolidacion' 
                        and $rol_usuario !== 'editor' and $rol_usuario !== 'reconocedor' 
                        and $rol_usuario !== 'atencion_procedencia' and $rol_usuario !== 'director_catastro'
                        and $rol_usuario !== 'procedencia_juridica' and $rol_usuario !== 'coordinacion_tecnica'
                        and $rol_usuario !== 'control_calidad'
                        ): ?>
                <div class="col-6 col-md-4 col-lg-2  align-items-center justify-content-center d-flex p-2">
                    <button type="button"
                        class="btn h-100 w-100 shadow btn-rol-3"
                        onclick="verHistorialAsignacion('atencion_procedencia', '<?php echo $cod_tramite; ?>')"
                        data-bs-toggle="modal" data-bs-target="#modalHistorial">
                        <b>Atención Procedencia</b>
                        <?php renderBadge('atencion_procedencia', $notificaciones, $mapRolIndex); ?>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Lider juridico-->
            <?php if (
                        $rol_usuario !== 'procedencia_juridica' and $rol_usuario !== 'coordinacion_tecnica' 
                        and $rol_usuario !== 'consolidacion' and $rol_usuario !== 'editor' 
                        and $rol_usuario !== 'reconocedor' and $rol_usuario !== 'atencion_procedencia'
                        and $rol_usuario !== 'ventanilla_catastral' and $rol_usuario !== 'control_calidad'
                        ): ?>
                <div class="col-6 col-md-4 col-lg-2  align-items-center justify-content-center d-flex p-2">
                    <button type="button"
                        class="btn h-100 w-100 shadow btn-rol-4 "
                        onclick="verHistorialAsignacion('revision_juridica', '<?php echo $cod_tramite; ?>')"
                        data-bs-toggle="modal" data-bs-target="#modalHistorial">
                        <b>Revision Jurídica</b>
                        <?php renderBadge('revision_juridica', $notificaciones, $mapRolIndex); ?>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Cordinacion tecnica-->
            <?php if (
                        $rol_usuario !== 'procedencia_juridica' and $rol_usuario !== 'coordinacion_tecnica' 
                        and $rol_usuario !== 'atencion_procedencia' and $rol_usuario !== 'ventanilla_catastral'
                        ): ?>
                <div class="col-6 col-md-4 col-lg-2  align-items-center justify-content-center d-flex p-2">
                    <button type="button"
                        class="btn  h-100 w-100 shadow btn-rol-5"
                        onclick="verHistorialAsignacion('coordinacion_tecnica', '<?php echo $cod_tramite; ?>')"
                        data-bs-toggle="modal" data-bs-target="#modalHistorial">
                        <b>Coordinación Técnica</b>
                        <?php renderBadge('coordinacion_tecnica', $notificaciones, $mapRolIndex); ?>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Control calidad-->
            <?php if (
                        $rol_usuario !== 'procedencia_juridica' and $rol_usuario !== 'coordinacion_tecnica' 
                        and $rol_usuario !== 'revision_juridica' and $rol_usuario !== 'componente_economico' 
                        and $rol_usuario !== 'avaluos' and $rol_usuario !== 'atencion_procedencia'
                        and $rol_usuario !== 'ventanilla_catastral' and $rol_usuario !== 'control_calidad'
                        ): ?>
                <div class="col-6 col-md-4 col-lg-2  align-items-center justify-content-center d-flex p-2">
                    <button type="button"
                        class="btn  h-100 w-100 shadow btn-rol-6"
                        onclick="verHistorialAsignacion('control_calidad', '<?php echo $cod_tramite; ?>')"
                        data-bs-toggle="modal" data-bs-target="#modalHistorial">
                        <b>Control Calidad</b>
                        <?php renderBadge('control_calidad', $notificaciones, $mapRolIndex); ?>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Consolidacion-->
            <?php if (
                $rol_usuario !== 'procedencia_juridica' and $rol_usuario !== 'coordinacion_tecnica' and $rol_usuario !== 'revision_juridica'
                and $rol_usuario !== 'componente_economico' and $rol_usuario !== 'avaluos' and $rol_usuario !== 'control_calidad'
                and $rol_usuario !== 'consolidacion' and $rol_usuario !== 'atencion_procedencia' and $rol_usuario !== 'director_catastro'
                and $rol_usuario !== 'ventanilla_catastral'
            ): ?>
                <div class="col-6 col-md-4 col-lg-2  align-items-center justify-content-center d-flex p-2">
                    <button type="button"
                        class="btn  h-100 w-100 shadow btn-rol-7"
                        onclick="verHistorialAsignacion('consolidacion', '<?php echo $cod_tramite; ?>')"
                        data-bs-toggle="modal" data-bs-target="#modalHistorial">
                        <b>Consolidación</b>
                        <?php renderBadge('consolidacion', $notificaciones, $mapRolIndex); ?>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Comoponente economico-->
            <?php if (
                $rol_usuario !== 'procedencia_juridica' and $rol_usuario !== 'coordinacion_tecnica'
                and $rol_usuario !== 'revision_juridica' and $rol_usuario !== 'componente_economico' 
                and $rol_usuario !== 'avaluos' and $rol_usuario !== 'control_calidad'
                and $rol_usuario !== 'consolidacion' and $rol_usuario !== 'editor' 
                and $rol_usuario !== 'reconocedor' and $rol_usuario !== 'atencion_procedencia'
                and $rol_usuario !== 'ventanilla_catastral'
            ): ?>
                <div class="col-6 col-md-4 col-lg-2  align-items-center justify-content-center d-flex p-2 ">
                    <button type="button"
                        class="btn  h-100 w-100 shadow btn-rol-8"
                        onclick="verHistorialAsignacion('componente_economico', '<?php echo $cod_tramite; ?>')"
                        data-bs-toggle="modal" data-bs-target="#modalHistorial">
                        <b>Lider Economico</b>
                        <?php renderBadge('componente_economico', $notificaciones, $mapRolIndex); ?>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Editor geografico-->
            <?php if (
                $rol_usuario !== 'procedencia_juridica' and $rol_usuario !== 'coordinacion_tecnica'
                and $rol_usuario !== 'revision_juridica' and $rol_usuario !== 'componente_economico' 
                and $rol_usuario !== 'avaluos' and $rol_usuario !== 'control_calidad'
                and $rol_usuario !== 'consolidacion' and $rol_usuario !== 'editor' 
                and $rol_usuario !== 'atencion_procedencia' and $rol_usuario !== 'director_catastro'
                and $rol_usuario !== 'ventanilla_catastral'
            ): ?>
                <div class="col-6 col-md-4 col-lg-2  align-items-center justify-content-center d-flex p-2 ">
                    <button type="button"
                        class="btn h-100 w-100 shadow btn-rol-9"
                        onclick="verHistorialAsignacion('editor', '<?php echo $cod_tramite; ?>')" data-bs-toggle="modal"
                        data-bs-target="#modalHistorial">
                        <b>Editor</b>
                        <?php renderBadge('editor', $notificaciones, $mapRolIndex); ?>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Avaluos-->
            <?php if (
                $rol_usuario !== 'procedencia_juridica' and $rol_usuario !== 'coordinacion_tecnica' 
                and $rol_usuario !== 'revision_juridica'and $rol_usuario !== 'componente_economico' 
                and $rol_usuario !== 'avaluos' and $rol_usuario !== 'control_calidad'
                and $rol_usuario !== 'consolidacion' and $rol_usuario !== 'editor' 
                and $rol_usuario !== 'reconocedor' and $rol_usuario !== 'atencion_procedencia'
                and $rol_usuario !== 'director_catastro' and $rol_usuario !== 'ventanilla_catastral'
            ): ?>
                <div class="col-6 col-md-4 col-lg-2  align-items-center justify-content-center d-flex p-2">
                    <button type="button"
                        class="btn h-100 w-100 shadow btn-rol-10"
                        onclick="verHistorialAsignacion('avaluos', '<?php echo $cod_tramite; ?>')" data-bs-toggle="modal"
                        data-bs-target="#modalHistorial">
                        <b>Avaluo</b>
                        <?php renderBadge('avaluos', $notificaciones, $mapRolIndex); ?>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Reconocedor predial-->
            <?php if (
                $rol_usuario !== 'procedencia_juridica' and $rol_usuario !== 'coordinacion_tecnica' 
                and $rol_usuario !== 'revision_juridica' and $rol_usuario !== 'componente_economico' 
                and $rol_usuario !== 'avaluos' and $rol_usuario !== 'control_calidad'
                and $rol_usuario !== 'consolidacion' and $rol_usuario !== 'editor' 
                and $rol_usuario !== 'reconocedor' and $rol_usuario !== 'atencion_procedencia'
                and $rol_usuario !== 'director_catastro' and $rol_usuario !== 'ventanilla_catastral'
            ): ?>
                <div class="col-6 col-md-4 col-lg-2  align-items-center justify-content-center d-flex p-2">
                    <button type="button"
                        class="btn h-100 w-100 shadow btn-rol-11"
                        onclick="verHistorialAsignacion('reconocedor', '<?php echo $cod_tramite; ?>')"
                        data-bs-toggle="modal" data-bs-target="#modalHistorial">
                        <b>Reconocimiento</b>
                        <?php renderBadge('reconocedor', $notificaciones, $mapRolIndex); ?>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Director Catastro-->
            <?php if ($rol_usuario !== 'director_catastro' and $rol_usuario !== 'editor'
                    and $rol_usuario !== 'ventanilla_catastral' and $rol_usuario !== 'procedencia_juridica'
                    and $rol_usuario !== 'coordinacion_tecnica' and $rol_usuario !== 'control_calidad'
                    and $rol_usuario !== 'consolidacion'
                    ): ?>
                <div class="col-6 col-md-4 col-lg-2  align-items-center justify-content-center d-flex p-2">
                    <button
                        type="button"
                        class="btn h-100 w-100 shadow btn-rol-12"
                        onclick="verHistorialAsignacion('reconocedor', '<?php echo $cod_tramite; ?>')"
                        data-bs-toggle="modal"
                        data-bs-target="#modalHistorial">
                        <b>Director Catastro</b>
                        <?php renderBadge('director_catastro', $notificaciones, $mapRolIndex); ?>
                    </button>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- PRUEBA MODAL DE PROCEDENCIA - INTENTO 1
                        MOSTRAR INFORMACION DE CARGA POR USUARIOS ANTERIORES -->
    <div class="modal fade" id="modalHistorial" tabindex="-1" aria-labelledby="modalHistorialLabel" aria-hidden="true"
        style="z-index: 2000;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <!-- <h5 class="modal-title" id="modalHistorialLabel">Historial del trámite</h5> -->
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" id="historialContenido">
                    Cargando historial...
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->

    <div class="row">

        <!-- col-xl-12 col-lg-7 -> ESTO LO QUE HACE ES DEFINIR EL ESPACIO DE LA TARJETA -->
        <div class="col-md-12 ">
            <div class="card shadow mb-4 p-3  text-center">
                <div style="background-color: #022F55;" class="rounded-3">
                    <h5 class="text-white text-center py-2 m-0">VER TRAMITE DE RADICACIÓN</h5>
                </div>
                <div class="card-body">
                    <form id="miFormulario" action="index.php?page=tramites/asignar_tramite" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <!--  CAMBIAR EL MODO DE ID, DESACTIVAR PORQUE DEBE SER AUTOMATICO -->
                            <div class="form-row">

                                <!-- <div class="col-md-6">
                                    <label for="cod_tramite"><b>ID_Radicacion</b></label>
                                    <input class="form-control py-4" id="cod_tramite" name="cod_tramite" type="text"
                                        value="<?php echo htmlspecialchars($tramite['cod_tramite']); ?>" readonly>
                                </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="cod_tramite" class="form-label fw-bold" style="font-size:0.9em;">ID de radicación</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-file-earmark-binary"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="cod_tramite"
                                            name="cod_tramite"
                                            value="<?php echo htmlspecialchars($tramite['cod_tramite']); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="fecha_rad"><b>Hora Radicación del Trámite</b></label>
                                    <input class="form-control py-4" id="fecha_rad" name="fecha_rad" type="text"
                                        value="<?php echo htmlspecialchars($tramite['fecha_rad']); ?>" readonly>
                                </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="fecha_rad" class="form-label fw-bold" style="font-size:0.9em;">Hora radicación del trámite</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="fecha_rad"
                                            name="fecha_rad"
                                            value="<?php echo htmlspecialchars($tramite['fecha_rad']); ?>" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-2 ">
                                    <label for="rol_asignado" class="form-label" style="font-size:0.9em;"><b>Área a asignar (Rol)</b></label>
                                    <div class="input-group shadow-sm">
                                        <label class="input-group-text" for="rol_asignado">
                                            <i class="bi bi-person-gear"></i>
                                        </label>
                                        <select class="form-select" id="rol_asignado" style="font-size:0.9em;" name="rol_asignado" required>
                                            <option value="">Seleccione un rol</option>
                                            <?php foreach ($roles_disponibles as $rol): ?>
                                                <option value="<?php echo $rol; ?>"><?php echo ucwords(str_replace("_", " ", $rol)); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- <div class="form-group" id="usuarios_container">
                                    <label for="usuario_asignado"><b>Usuario a asignar</b></label>
                                    <select class="form-control" id="usuario_asignado" name="usuario_asignado" required>
                                        <option value="">Seleccione un Área a Asignar Primero</option>
                                    </select>
                                </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="usuario_asignado" class="form-label" style="font-size:0.9em;"><b>Usuario a asignar</b></label>
                                    <div class="input-group shadow-sm">
                                        <label class="input-group-text" for="usuario_asignado">
                                            <i class="bi bi-person-circle"></i>
                                        </label>
                                        <select class="form-select" id="usuario_asignado" name="usuario_asignado" required>
                                            <option value="">Seleccione un área a asignar primero</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="nombre_usuario"><b>Nombres del Usuario</b></label>
                                    <input class="form-control py-4" id="nombre_usuario" name="nombre_usuario" type="text" readonly>
                                </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="nombre_usuario" class="form-label fw-bold" style="font-size:0.9em;">Nombre del usuario</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                        <input class="form-control" id="nombre_usuario" name="nombre_usuario" type="text" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="apellido_usuario"><b>Apellidos del Usuario</b></label>
                                    <input class="form-control py-4" id="apellido_usuario" name="apellido_usuario" type="text" readonly>
                                </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="apellido_usuario" class="form-label fw-bold" style="font-size:0.9em;">Apellidos del usuario</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input class="form-control" id="apellido_usuario" name="apellido_usuario" type="text" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="correo_usuario"><b>Correo de Contacto Usuario</b></label>
                                    <input class="form-control py-4" id="correo_usuario" name="correo_usuario" type="text" readonly>
                                </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="correo_usuario" class="form-label fw-bold" style="font-size:0.9em;">Correo de contacto usuario</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input class="form-control" id="correo_usuario" name="correo_usuario" type="text" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="celular_usuario"><b>Celular de Contacto Usuario</b></label>
                                    <input class="form-control py-4" id="celular_usuario" name="celular_usuario" type="text" readonly>
                                </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="celular_usuario" class="form-label fw-bold" style="font-size:0.9em;">Celular de Contacto Usuario</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                        <input class="form-control" id="celular_usuario" name="celular_usuario" type="text" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6 p-1 px-2 my-2">
                                    <label class="mb-3 "><b>Selecciona el estado del trámite</b></label><br>
                                    <div class="form-check form-check-inline p-2 border bg-success">
                                        <input class="form-check-input" type="radio" name="asignacion_estado_tramite" id="prioridad" value="PRIORIDAD" required>
                                        <label class="form-check-label" for="prioridad">PRIORIDAD</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="asignacion_estado_tramite" id="a_tiempo" value="A TIEMPO">
                                        <label class="form-check-label" for="a_tiempo">A TIEMPO</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="asignacion_estado_tramite" id="suspension" value="SUSPENSION">
                                        <label class="form-check-label" for="suspension">SUSPENSIÓN</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="asignacion_estado_tramite" id="tutela" value="TUTELA">
                                        <label class="form-check-label" for="tutela">TUTELA</label>
                                    </div>
                                </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label class="mb-3"><b>Selecciona el estado del trámite</b></label><br>

                                    <div class="form-check form-check-inline custom-radio-btn">
                                        <input class="form-check-input" type="radio" name="asignacion_estado_tramite" id="prioridad" value="PRIORIDAD" required>
                                        <label class="form-check-label w-100" for="prioridad">PRIORIDAD</label>
                                    </div>

                                    <div class="form-check form-check-inline custom-radio-btn">
                                        <input class="form-check-input" type="radio" name="asignacion_estado_tramite" id="a_tiempo" value="A TIEMPO">
                                        <label class="form-check-label w-100" for="a_tiempo">A TIEMPO</label>
                                    </div>

                                    <div class="form-check form-check-inline custom-radio-btn">
                                        <input class="form-check-input" type="radio" name="asignacion_estado_tramite" id="suspension" value="SUSPENSION">
                                        <label class="form-check-label w-100" for="suspension">SUSPENSIÓN</label>
                                    </div>

                                    <div class="form-check form-check-inline custom-radio-btn">
                                        <input class="form-check-input" type="radio" name="asignacion_estado_tramite" id="tutela" value="TUTELA">
                                        <label class="form-check-label w-100" for="tutela">TUTELA</label>
                                    </div>
                                </div>



                                <!-- <div class="col-md-6">
                                    <label for="rol_asignado"><b>Fecha Limite Respuesta al siguiente área</b></label>
                                    <input type="date" class="form-control" name="fecha_limite" value="<?php echo date('Y-m-d', strtotime('+5 days')); ?>" readonly>
                                </div> -->

                                <!-- <div class="col-md-6">
                                    <label for="rol_asignado"><b>Fecha Limite Respuesta al siguiente área</b></label>
                                    <input type="date" class="form-control" name="fecha_limite" value="<?php echo date('Y-m-d', strtotime('+5 days')); ?>" readonly>
                                </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="rol_asignado" class="form-label fw-bold" style="font-size:0.9em;">Fecha límite de respuesta a siguiente área</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-calendar2-x"></i></span>
                                        <input type="date" class="form-control" name="fecha_limite" value="<?php echo date('Y-m-d', strtotime('+5 days')); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-12 my-4" style="border-bottom: 1px solid #02305561;"></div> -->
                                <?php if ($rol_usuario === 'coordinacion_tecnica' || $rol_usuario === 'control_calidad' || $rol_usuario === 'consolidacion' || $rol_usuario === 'editor'): ?>

                                    <!-- Bloque AnyDesk: copiar / abrir -->
                                    <!-- Input oculto con el ID (rellénalo dinámicamente con PHP si cambia) -->
                                    <!--<input type="hidden" id="anydesk_id" value="1897312615">
                                    <div class="col-md-12 p-1 px-2 my-3 mt-5 ">
                                        <div class="card_any shadow p-4 d-flex flex-column gap-3 aling-content-center justify-content-center rounded-4 w-50"
                                            style="margin-left: 25%; border-left:2px solid #66CC99">
                                            <h4 for="btnAnyDesk" class="fw-bold m-0" style="font-size:1em;">Acceso a editor geográfico</h4>
                                            <a id="btnAnyDesk" href="#" class="btn fw-bold " style="background-color:#66CC99;">
                                                <i class="bi bi-display me-2"></i> Abrir editor ARBIMaps
                                            </a>
                                        </div>

                                        <script>
                                            document.getElementById('btnAnyDesk').addEventListener('click', function(e) {
                                                e.preventDefault(); // evita que navegue a "#"
                                                const id = document.getElementById('anydesk_id').value.trim();
                                                if (id) {
                                                    // Redirige directamente a la app AnyDesk con el ID
                                                    window.location.href = "anydesk://" + id;
                                                }
                                            });
                                        </script>
                                    </div> -->
                                <?php endif; ?>

                                <!-- Aquí va la nueva sección que se añadió para las diferentes tipos de trámites -->

                                <?php if ($rol_usuario === 'procedencia_juridica'): ?>
                                    <?php
                                    // DEFINIR Y TRAE VALORES QUE SE TENGA EN LA BD
                                    $mutacionActual = $tramite['mutacion_tramite'] ?? '';
                                    $tipoProcesoActual = $tramite['tipo_proceso_muta'] ?? '';
                                    ?>

                                    <!-- Mutación de Trámite -->
                                    <!-- <div class="col-md-6 mb-3">
                                        <label for="mutacion_tramite"><b>Confirmar Mutación de Trámite</b></label>
                                        <select class="custom-select" id="mutacion_tramite" name="mutacion_tramite" required>
                                            <option value="" disabled selected>Seleccione mutación</option>
                                            <option value="Mutacion_1" <?= ($mutacionActual == "Mutacion_1") ? "selected" : "" ?>>Mutación 1</option>
                                            <option value="Mutacion_2" <?= ($mutacionActual == "Mutacion_2") ? "selected" : "" ?>>Mutación 2</option>
                                            <option value="Mutacion_3" <?= ($mutacionActual == "Mutacion_3") ? "selected" : "" ?>>Mutación 3</option>
                                            <option value="Mutacion_4" <?= ($mutacionActual == "Mutacion_4") ? "selected" : "" ?>>Mutación 4</option>
                                            <option value="Mutacion_5" <?= ($mutacionActual == "Mutacion_5") ? "selected" : "" ?>>Mutación 5</option>
                                            <option value="Cancelacion" <?= ($mutacionActual == "Cancelacion") ? "selected" : "" ?>>Cancelación</option>
                                            <option value="Complementacion" <?= ($mutacionActual == "Complementacion") ? "selected" : "" ?>>Complementación</option>
                                            <option value="Peticion" <?= ($mutacionActual == "Peticion") ? "selected" : "" ?>>Petición</option>
                                            <option value="Queja" <?= ($mutacionActual == "Queja") ? "selected" : "" ?>>Queja</option>
                                            <option value="Reclamo" <?= ($mutacionActual == "Reclamo") ? "selected" : "" ?>>Reclamo</option>
                                            <option value="Solicitud" <?= ($mutacionActual == "Solicitud") ? "selected" : "" ?>>Solicitud</option>
                                            <option value="Otro" <?= ($mutacionActual == "Otro") ? "selected" : "" ?>>Otro</option>
                                        </select>
                                    </div> -->

                                    <div class="col-md-4 p-1 px-2 my-2 ">
                                        <label for="mutacion_tramite" class="form-label" style="font-size:0.9em;"><b>Confirmar mutación de trámite</b></label>
                                        <div class="input-group shadow-sm">
                                            <label class="input-group-text" for="mutacion_tramite">
                                                <i class="bi bi-file-earmark-medical"></i>
                                            </label>
                                            <select class="form-select" style="font-size:0.9em;" id="mutacion_tramite" name="mutacion_tramite" required>
                                                <option value="" disabled selected>Seleccione mutación</option>
                                                <option value="Mutacion_1" <?= ($mutacionActual == "Mutacion_1") ? "selected" : "" ?>>Mutación 1</option>
                                                <option value="Mutacion_2" <?= ($mutacionActual == "Mutacion_2") ? "selected" : "" ?>>Mutación 2</option>
                                                <option value="Mutacion_3" <?= ($mutacionActual == "Mutacion_3") ? "selected" : "" ?>>Mutación 3</option>
                                                <option value="Mutacion_4" <?= ($mutacionActual == "Mutacion_4") ? "selected" : "" ?>>Mutación 4</option>
                                                <option value="Mutacion_5" <?= ($mutacionActual == "Mutacion_5") ? "selected" : "" ?>>Mutación 5</option>
                                                <option value="Cancelacion" <?= ($mutacionActual == "Cancelacion") ? "selected" : "" ?>>Cancelación</option>
                                                <option value="Complementacion" <?= ($mutacionActual == "Complementacion") ? "selected" : "" ?>>Complementación</option>
                                                <option value="Peticion" <?= ($mutacionActual == "Peticion") ? "selected" : "" ?>>Petición</option>
                                                <option value="Queja" <?= ($mutacionActual == "Queja") ? "selected" : "" ?>>Queja</option>
                                                <option value="Reclamo" <?= ($mutacionActual == "Reclamo") ? "selected" : "" ?>>Reclamo</option>
                                                <option value="Solicitud" <?= ($mutacionActual == "Solicitud") ? "selected" : "" ?>>Solicitud</option>
                                                <option value="Otro" <?= ($mutacionActual == "Otro") ? "selected" : "" ?>>Otro</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- <div class="col-md-6 mb-3">
                                        <label for="tipo_proceso_muta"><b>Seleccione Tipo de Proceso</b></label>
                                        <select class="custom-select" id="tipo_proceso_muta" name="tipo_proceso_muta" required>
                                            <option value="" disabled selected>SELECCIONE</option>
                                        </select>
                                    </div> -->

                                    <div class="col-md-4 p-1 px-2 my-2 ">
                                        <label for="tipo_proceso_muta" class="form-label" style="font-size:0.9em;"><b>Seleccione tipo de proceso</b></label>
                                        <div class="input-group shadow-sm">
                                            <label class="input-group-text" for="tipo_proceso_muta">
                                                <i class="bi bi-file-earmark-medical-fill"></i>
                                            </label>
                                            <select class="form-select" style="font-size:0.9em;" id="tipo_proceso_muta" name="tipo_proceso_muta" required>
                                                <option value="" disabled selected>SELECCIONE</option>
                                            </select>
                                        </div>
                                    </div>

                                    <script>
                                        // Opciones agrupadas por mutación
                                        const opcionesPorMutacion = {
                                            "Mutacion_1": [
                                                "Cambio Propietario",
                                                "Cambio Poseedor",
                                                "Cambio Ocupante",
                                                "Cambio Mejoratario",
                                                "Cambio Arrendador"
                                            ],
                                            "Mutacion_2": [
                                                "Agregación NPH",
                                                "Segregacion NPH",
                                                "Predios RPH"
                                            ],
                                            "Mutacion_3": [
                                                "Incorporación Construcción",
                                                "Demolición Construcción",
                                                "Destino Economico Predio"
                                            ],
                                            "Mutacion_4": [
                                                "Revisión Avalúo"
                                            ],
                                            "Mutacion_5": [
                                                "Inscripción Predio Nuevo",
                                                "Inscripción Predio Omitido",
                                                "Informalidades"
                                            ],
                                            "Cancelacion": [
                                                "Cancelación Inscripción Catastral Orden Judicial",
                                                "Cancelación Doble Inscripción"
                                            ],
                                            "Complementacion": [
                                                "Adición Datos Predio",
                                                "Adición Datos Propietario"
                                            ]
                                            // Aquí puedes seguir agregando si hace falta
                                        };

                                        // Elementos
                                        const selectMutacion = document.getElementById("mutacion_tramite");
                                        const selectProceso = document.getElementById("tipo_proceso_muta");

                                        // Valor actual de BD (inyectado por PHP)
                                        const procesoActual = "<?= $tipoProcesoActual ?>";

                                        function actualizarProcesos() {
                                            const mutacion = selectMutacion.value;
                                            selectProceso.innerHTML = '<option value="" disabled selected>SELECCIONE</option>';

                                            if (opcionesPorMutacion[mutacion]) {
                                                opcionesPorMutacion[mutacion].forEach(opcion => {
                                                    const opt = document.createElement("option");
                                                    opt.value = opcion;
                                                    opt.textContent = opcion;
                                                    if (opcion === procesoActual) opt.selected = true; // marcar actual BD
                                                    selectProceso.appendChild(opt);
                                                });
                                            }
                                        }

                                        // Cuando cambia mutación
                                        selectMutacion.addEventListener("change", actualizarProcesos);

                                        // Inicializar si ya viene con valor
                                        if (selectMutacion.value) {
                                            actualizarProcesos();
                                        }
                                    </script>


                                    <!-- ESTA VARIABLE ES PARA DETERMINAR LA FECHA DE RESPUESTA QUE SE DEBE DAR AL TRAMITE -->
                                    <!-- <div class="col-md-4 p-1 px-2 my-2 ">
                                        <label for="fecha_respuesta_tramite"><b>Fecha de Respuesta a Tramite</b></label>
                                        <input type="date" class="form-control" id="fecha_respuesta_tramite" name="fecha_respuesta_tramite">
                                    </div> -->

                                    <div class="col-md-4 p-1 px-2 my-2">
                                        <label for="fecha_respuesta_tramite" class="form-label fw-bold" style="font-size:0.9em;">Fecha de respuesta al trámite</label>
                                        <div class="input-group shadow-sm">
                                            <span class="input-group-text text-white" style="background-color: #002F55;outline: 1px solid  #002F55;"><i class="bi bi-calendar2-week"></i></span>
                                            <input type="date" style="outline: 1px solid  #002F55;" class="form-control" id="fecha_respuesta_tramite" name="fecha_respuesta_tramite">
                                        </div>
                                    </div>
                                    <!-- ACTIVIDAD QUE DEBE REALIZAR O TENER ENM CUENTA DURANTE EL PROCESO -->
                                    <div class="col-md-10 p-1 px-2 my-2 mx-auto ">
                                        <label for="actividad_tramite" class="mb-2"><b>Actividad a Realizar</b></label>
                                        <textarea class="form-control py-3" id="actividad_tramite" name="actividad_tramite"
                                            placeholder="Ingrese la actividad a realizar con respecto al trámite"></textarea>
                                    </div>

                                <?php endif; ?>



                                <div class="col-md-12 my-4" style="border-bottom: 1px solid #02305561;">
                                </div>



                                <div class="col-md-12 form-group my-3 text-center align-center">
                                    <div class="info-banner mb-4 mt-2 text-center">
                                        <i class="fas fa-info-circle"></i>
                                        <p class="mb-0">
                                            <!-- Desde aquí puedes <strong>agregar</strong> para el usuario que solicitó el trámite y <strong>asignar</strong> a la siguiente área. -->
                                            Aquí puedes <strong> agregar documentos </strong> adicionales que consideres necesarios. <strong> Solamente se admiten hasta 5 documentos.</strong>
                                        </p>
                                    </div>
                                    <button type="button" class="btn btn-ver" id="agregarDocumento" style="border:1px solid #022F55; color:#022F55;">
                                        <i class="bi bi-file-earmark-plus"></i> Agregar otros documentos
                                    </button>
                                </div>

                                <!-- <div class=" col-12 col-lg-6 p-1 px-2 my-2">
                                    <label for="sol_escrita_tramite" class="form-label fw-bold">Agrega otro documento </label>
                                    <div class="input-group mb-1 shadow-sm">
                                        <label class="input-group-text" for="sol_escrita_tramite" style="font-size:0.9em;"><i class="bi bi-file-earmark-plus"></i></label>
                                        <input type="file" class="form-control" style="font-size:0.8em;" id="sol_escrita_tramite" name="sol_escrita_tramite">
                                    </div>
                                    <div class="form-text">Puedes adjuntar hasta 5 documentos más.</div>
                                    </div> -->
                            </div>

                            <!-- Contenedor principal de los documentos -->
                            <div id="documentosContainer">
                                <div class="form-row documento-group">
                                    <div class="col-md-6 p-1 px-2 my-2 ">
                                        <label for="tipo_doc1" class="form-label" style="font-size:0.9em;"><b>Seleccione el trámite</b></label>
                                        <div class="input-group shadow-sm">
                                            <label class="input-group-text" for="tipo_doc1">
                                                <i class="bi bi-file-earmark-text-fill"></i>
                                            </label>
                                            <select class="form-select" id="tipo_doc1" style="font-size:0.9em;" name="tipo_doc1" required>
                                                <option value="" disabled selected>SELECCIONE</option>
                                                <option value="Avaluo_Catastral">Avaluo Catastral</option>
                                                <option value="Acto_Administrativo">Acto Administrativo</option>
                                                <option value="Acto_Judicial">Acto Judicial</option>
                                                <option value="Consulta_VUR">Consulta VUR</option>
                                                <option value="Devolución_Tramite">Devolución Tramite</option>
                                                <option value="Documento_Privado">Documento Privado</option>
                                                <option value="Escritura_Publica">Escrituras Públicas</option>
                                                <option value="Impuesto_Predial">Impuesto Predial</option>
                                                <option value="Informe_Visita">Informe de Visita</option>
                                                <option value="Informe_Edicion">Informe de Edición</option>
                                                <option value="Informe_Calidad">Informe de Calidad</option>
                                                <option value="Manzana_Catastral">Manzana Catastral</option>
                                                <option value="Notificacion">Notificación</option>
                                                <option value="Plano_Predial">Plano Predial</option>
                                                <option value="Resolución">Resolución</option>
                                                <option value="Otro">Otro</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class=" col-12 col-lg-6 p-1 px-2 my-2">
                                        <label for="nombre_doc1" class="form-label fw-bold">Documentos a cargar</label>
                                        <div class="input-group shadow-sm">
                                            <label class="input-group-text" for="nombre_doc1" style="font-size:0.9em;"><i class="bi bi-file-earmark-pdf-fill"></i></label>
                                            <input type="file" class="form-control" style="font-size:0.8em;" id="nombre_doc1" name="nombre_doc1">
                                        </div>
                                        <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 my-4" style="border-bottom: 1px solid #02305561;"></div>

                            <div class="form-group">
                                <label for="observacion_asignacion" class="my-2"><b>Observaciones para la asignación</b></label>
                                <textarea class="form-control py-4 shadow-sm" id="observacion_asignacion" name="observacion_asignacion"
                                    placeholder="Ingrese una descripción/observación para informar al siguiente usuario"></textarea>
                            </div>
                            <!-- Campos ocultos -->
                            <input type="hidden" id="cedula" name="cedula">
                            <input type="hidden" id="rol_usuario" name="rol_usuario" value="<?= $rol_usuario ?>">

                            <div class="form-group mt-4 mb-0">

                                <?php if ($_SESSION['rol_usuario'] === 'procedencia_juridica'): ?>

                                    <button type="submit" id="boton_asignar_procede" class="btn my-2 btn-success btn-block" disabled style="background-color: #022F55;">
                                        <i class="bi bi-file-earmark-arrow-up me-2"></i>ASIGNAR TRÁMITE
                                    </button> </br>

                                    <!-- Botón exclusivo para Procedencia Jurídica -->
                                    <button type="button" class="btn btn-success" id="btnProcedeAsigna">
                                        <i class="bi bi-check-circle-fill me-1"></i> Procede y Asigna
                                    </button>

                                    <button type="button" class="btn btn-danger" id="btnNoProcede">
                                        <i class="bi bi-x-circle-fill me-1"></i> No procede tramite
                                    </button>

                                    <button type="button" class="btn btn-warning" id="btnNoProcedeCompletar">
                                        <i class="bi bi-wrench-adjustable-circle-fill me-1"></i> No procede completar
                                    </button>
                                <?php else: ?>
                                    <!-- Botón general para todos los demás roles -->
                                    <button type="submit" class="btn text-white " style="background-color: #022F55;">
                                        <i class="bi bi-file-earmark-arrow-up me-2"></i> Asignar Trámite
                                    </button>
                                <?php endif; ?>

                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- -----------------Modales------------------------------------- -->

<!-- Modal de advertencia -->
<div class="modal fade" id="maxDocsModal" tabindex="-1" aria-labelledby="maxDocsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered ">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #002F55;">
                <h5 class="modal-title" id="maxDocsLabel">Atención</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                Solo puedes agregar hasta 5 documentos
            </div>
            <div class="modal-footer">
                <button type="button" class="btn text-white" style="background-color: #002F55;" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>

<!-- toast para el alert -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    <div id="mensajeToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto" id="toastTitle">Mensaje</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>
        <div class="toast-body" id="toastBody"></div>
    </div>
</div>

<!-- -------------- MODAL: PROCEDE Y ASIGNA --------------->
<div class="modal fade rounded-4" id="modalProcedeAsigna" tabindex="1" aria-labelledby="modalProcedeAsignaLabel" aria-hidden="true" style="z-index:1999">
    <div class="modal-dialog modal-xl modal-dialog-scrollable ">
        <div class="modal-content">
            <div class="modal-header bg-success text-white px-3 py-3">
                <h5 class="modal-title" id="modalProcedeAsignaLabel">Procede y Asigna - Generación de Documento</h5>
                <button type="button" class="btn-close text-white" style="background-color:white !important" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div id="infoTramiteContainer">
                    <!-- Banner informativo -->
                    <div class="info-banner mb-4 mt-2 text-center">
                        <i class="fas fa-info-circle"></i>
                        <p class="mb-0">
                            Desde aquí puedes <strong>generar el certificado</strong> para el usuario que solicitó el trámite y <strong>asignar</strong> a la siguiente área.
                        </p>
                    </div>

                    <!-- información del trámite -->

                    <div class="card border rounded-4 shadow p-3">
                        <div class="card-header rounded-3" style="background-color: #002F55;">
                            <h5 class="text-white text-center mb-0">Información del trámite</h5>
                        </div>
                        <div class="row mt-2 px-2 text-start">

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">ID Radicación</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi-journal-text"></i></span>
                                    <input type="text" id="cod_tramite_modal" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Fecha Radicación</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar2-event"></i></span>
                                    <input type="text" id="fecha_rad_modal" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Tipo de Trámite</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-file-earmark-text-fill"></i></span>
                                    <input type="text" id="tramite_modal" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-6 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Nombre Completo del Interesado</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-fill"></i></span>
                                    <input type="text" id="nombre_interesado_modal" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-3 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Teléfono</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-telephone-fill me-2"></i>+57</span>
                                    <input type="text" id="telefono_modal" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-3 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Correo Electrónico</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at-fill"></i></span>
                                    <input type="text" id="correo_modal" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border mt-3 rounded-4 shadow p-3">
                        <div class="card-header rounded-3" style="background-color: #002F55;">
                            <h5 class="text-white text-center mb-0">Información del predio</h5>
                        </div>

                        <div class="row mt-2 px-2 text-start">
                            <div class="col-md-5 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Código Catastral (NPN)</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-map"></i></span>
                                    <input type="text" id="npn_modal" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-3 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">FMI Predio</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-map-fill"></i></span>
                                    <input type="text" id="fmi_modal" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Dirección del Predio</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-signpost-2-fill"></i></span>
                                    <input type="text" id="direccion_modal" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-12 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Propietarios del Predio</label>
                                <textarea id="propietarios_modal" class="form-control" rows="2" readonly></textarea>
                            </div>



                            <!-- <div class="col-md-12 p-1 px-2 my-3 text-center">
                                    <label class="form-label fw-bold ">Propietarios del Predio/Terreno</label>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm text-center">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th style="background-color: #002F55" class="text-white text-center px-2 rounded-3">Nombre Propietario</th>
                                                    <th style="background-color: #002F55" class="text-white text-center px-2 rounded-3">Tipo Documento</th>
                                                    <th style="background-color: #002F55" class="text-white text-center px-2 rounded-3">Número Documento</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($propietarios as $prop): ?>
                                                    <tr>
                                                        <td class="py-2"><?php echo htmlspecialchars($prop['nombre_propietario_tram']); ?></td>
                                                        <td class="py-2"><?php echo htmlspecialchars($prop['tipo_doc_propietario_tram']); ?></td>
                                                        <td class="py-2"><?php echo htmlspecialchars($prop['cedula_propietario_tram']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div> -->



                        </div>
                    </div>
                </div>

                <!-- SECCIÓN DE DOCUMENTO -->
                <div class="row mt-3 p-2">
                    <div class="col-md-6 mt-3 mt-3 p-1">
                        <div class="rounded-4 mx-2 p-3 h-100" style="background-color: #f8f9fa;">
                            <div class="my-3 text-center" style="border-bottom: 1px solid #002f556e;">
                                <h5 class="mb-3"> <i class="bi bi-file-earmark-richtext me-2 fw-bold "></i>Generar Documento</h5>
                            </div>
                            <form id="formProcedeAsigna">
                                <!-- 🔹 Dentro del <form id="formProcedeAsigna"> -->
                                <input type="hidden" name="cod_tramite" id="cod_tramite_hidden">

                                <!-- Mutación y tipo de proceso -->
                                <input type="hidden" id="mutacion_tramite_modal" name="tipo_mutacion_tramite">
                                <input type="hidden" id="tipo_proceso_muta_modal" name="tipo_proceso_muta">

                                <!-- Fecha de respuesta -->
                                <input type="hidden" id="fecha_respuesta_tramite_modal" name="fecha_respuesta_tramite">

                                <!-- Mapeos correctos: -->
                                <!-- actividad_tramite = tipo_proceso_muta -->
                                <input type="hidden" id="actividad_tramite_modal" name="actividad_tramite">
                                <!-- actividad_a_realizar = textarea “Actividad a Realizar” -->
                                <input type="hidden" id="actividad_a_realizar_modal" name="actividad_a_realizar">

                                <div class="form-group my-3">
                                    <label for="tipo_documento" class="form-label my-2 fw-bold">
                                        <i class="fas fa-file-alt me-1"></i>
                                        Tipo de Documento:
                                    </label>
                                    <select name="tipo_documento" class="form-select ">
                                        <option value="Oficio">Oficio</option>
                                        <option value="Resolución">Resolución</option>
                                    </select>
                                </div>

                                <div class="form-group my-3">
                                    <label for="contenido_html" class="form-label fw-bold">
                                        <i class="bi bi-pen"></i>
                                        Contenido del Documento:
                                    </label>
                                    <textarea class="form-control " id="contenido_html" name="contenido_html" rows="16"></textarea>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-md-6 mt-3 p-1 h-100">
                        <div class="card shadow border rounded-4 mx-2 p-3">
                            <div style="background-color: #002F55;" class="py-3 px-3 rounded-3 text-white d-flex justify-content-between">
                                <h5 class="mb-0"> <i class="bi bi-eye-fill me-2"></i><b>Vista Previa</b></h5>
                                <span class="badge bg-light text-dark mb-0">En tiempo real</span>
                            </div>
                            <iframe id="previewDocumento" style="width:100%;height:550px;border:1px solid #ccc;" class="mt-2 rounded-3"></iframe>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" id="btnGuardarProcede">Guardar y Enviar</button>
            </div>
        </div>
    </div>
</div>

<!-- ---------------MODAL: NO PROCEDE------------------ -->
<div class="modal fade rounded-4" id="modalNoProcede" tabindex="-1" aria-labelledby="modalNoProcedeLabel" aria-hidden="true" style="z-index:1999">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white px-3 py-3">
                <h5 class="modal-title w-100 text-center" id="modalNoProcedeLabel">
                    No Procede - Generación de Documento
                </h5>
                <button type="button" class="btn-close text-white" style="background-color:white !important" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div id="infoTramiteContainerNoProcede">
                    <!-- Banner informativo -->
                    <div class="info-banner mb-4 mt-2 text-center">
                        <i class="fas fa-info-circle"></i>
                        <p class="mb-0">
                            Desde aquí puedes <strong>generar el certificado de NO PROCEDENCIA</strong> para el usuario que solicitó el trámite.
                        </p>
                    </div>

                    <div class="card border rounded-4 shadow p-3">
                        <div class="card-header rounded-3" style="background-color: #002F55;">
                            <h5 class="text-white text-center mb-0">Información del trámite</h5>
                        </div>

                        <div class="row mt-2 px-2 text-start">
                            <div class="col-md-4 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">ID Radicación</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi-journal-text"></i></span>
                                    <input type="text" id="cod_tramite_modal_noprocede" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Fecha Radicación</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar2-event"></i></span>
                                    <input type="text" id="fecha_rad_modal_noprocede" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Tipo de Trámite</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-file-earmark-text-fill"></i></span>
                                    <input type="text" id="tramite_modal_noprocede" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-6 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Nombre Completo del Interesado</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-fill"></i></span>
                                    <input type="text" id="nombre_interesado_modal_noprocede" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-3 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Teléfono</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-telephone-fill me-2"></i>+57</span>
                                    <input type="text" id="telefono_modal_noprocede" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-3 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Correo Electrónico</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at-fill"></i></span>
                                    <input type="text" id="correo_modal_noprocede" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border mt-3 rounded-4 shadow p-3">
                        <div class="card-header rounded-3" style="background-color: #002F55;">
                            <h5 class="text-white text-center mb-0">Información del predio</h5>
                        </div>

                        <div class="row mt-2 px-2 text-start">

                            <div class="col-md-5 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Código Catastral (NPN)</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-map"></i></span>
                                    <input type="text" id="npn_modal_noprocede" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-3 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">FMI Predio</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-map-fill"></i></span>
                                    <input type="text" id="fmi_modal_noprocede" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Dirección del Predio</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-signpost-2-fill"></i></span>
                                    <input type="text" id="direccion_modal_noprocede" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label><b>Propietarios del Predio</b></label>
                                <textarea id="propietarios_modal_noprocede" class="form-control" rows="2" readonly></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- <script>
                        function toggleIframe(id) {
                            const visor = document.getElementById(id);
                            visor.style.display = (visor.style.display === 'none') ? 'block' : 'none';
                        }
                    </script> -->

                    <!-- FUNCION PARA MOSTRAR Y OCULTAR -->
                    <script>
                        function toggleIframe(id, boton) {
                            const visor = document.getElementById(id);
                            const icono = boton.querySelector("i");
                            const texto = boton.querySelector("span");

                            visor.classList.toggle("mostrar");

                            if (visor.classList.contains("mostrar")) {
                                icono.classList.replace("bi-eye", "bi-eye-slash");
                                texto.textContent = "Ocultar Vista Previa";
                            } else {
                                icono.classList.replace("bi-eye-slash", "bi-eye");
                                texto.textContent = "Mostrar Vista Previa";
                            }
                        }
                    </script>

                    <!-- Sección de los documentos subidos -->
                    <div class="card border mt-3 rounded-4 shadow p-3">

                        <div class="card-header rounded-3" style="background-color: #002F55;">
                            <h5 class="text-white text-center mb-0">Documentación</h5>
                        </div>

                        <div class="form-row">
                            <div class="col-md-6 p-1 px-3 my-3">
                                <label for="sol_escrita_tramite" class="form-label fw-bold" style="font-size:0.9em">Solicitud Escrita</label>
                                <!-- <input class="form-control py-4" id="sol_escrita_tramite" name="sol_escrita_tramite" type="text"
                                    value="<?php echo htmlspecialchars($tramite['sol_escrita_tramite']); ?>" readonly> -->

                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-file-earmark-pdf-fill"></i></span>
                                    <input type="text" class="form-control" style="font-size: 0.9em;" id="sol_escrita_tramite" name="sol_escrita_tramite"
                                        value="<?php echo htmlspecialchars($tramite['sol_escrita_tramite']); ?>" readonly>
                                </div>
                                <?php if (!empty($tramite['sol_escrita_tramite'])): ?>
                                    <div class="d-flex justify-content-center gap-2 my-2">
                                        <a href="<?php echo $ruta_pdf . $tramite['sol_escrita_tramite']; ?>"
                                            target="_blank" class="bot_verenotrapesta btn btn-sm">
                                            <i class="bi bi-box-arrow-right"></i> Ver en otra pestaña
                                        </a>
                                        <!-- <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleIframe('visor_sol_escrita')">
                                            Mostrar/Ocultar Vista Previa
                                        </button> -->
                                        <button type="button"
                                            class="bot_mostrar_vista btn btn-sm"
                                            onclick="toggleIframe('visor_sol_escrita', this)">
                                            <i class="bi bi-eye"></i> <span>Mostrar Vista Previa</span>
                                        </button>
                                    </div>
                                    <div id="visor_sol_escrita" class="iframe-animado">
                                        <iframe src="<?php echo $ruta_pdf . $tramite['sol_escrita_tramite']; ?>"
                                            width="100%" height="650px"></iframe>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No se ha cargado aún ningún documento</span>
                                <?php endif; ?>
                            </div>

                            <!-- Copia de Escritura -->
                            <div class="col-md-6 p-1 px-3 my-3">
                                <label for="cop_escritura_tramite" class="form-label fw-bold" style="font-size:0.9em">Copia de Escritura - Sentencia Judicial - Acto Administrativo</label>
                                <!-- <input class="form-control py-4" id="cop_escritura_tramite" name="cop_escritura_tramite" type="text"
                                    value="<?php echo htmlspecialchars($tramite['cop_escritura_tramite']); ?>" readonly> -->
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-file-earmark-pdf-fill"></i></span>
                                    <input class="form-control" id="cop_escritura_tramite" style="font-size: 0.9em;" name="cop_escritura_tramite" type="text"
                                        value="<?php echo htmlspecialchars($tramite['cop_escritura_tramite']); ?>" readonly>
                                </div>

                                <?php if (!empty($tramite['cop_escritura_tramite'])): ?>
                                    <div class="d-flex justify-content-center gap-2 my-2">
                                        <a href="<?php echo $ruta_pdf . $tramite['cop_escritura_tramite']; ?>" 
                                        target="_blank" class="bot_verenotrapesta btn btn-sm">
                                            <i class="bi bi-box-arrow-right"></i> Ver en otra pestaña
                                        </a>
                                        <button type="button" class="bot_mostrar_vista btn btn-sm"
                                            onclick="toggleIframe('visor_escritura',this)">
                                            <i class="bi bi-eye"></i> <span>Mostrar Vista Previa</span>
                                        </button>
                                    </div>
                                    <div id="visor_escritura" class="iframe-animado">
                                        <iframe src="<?php echo $ruta_pdf . $tramite['cop_escritura_tramite']; ?>" width="100%" height="650px"></iframe>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No se ha cargado aún ningún documento</span>
                                <?php endif; ?>
                            </div>

                            <!-- Certificado Tradición -->
                            <div class="col-md-6 p-1 px-3 my-3">
                                <label for="ctl_tramite" class="form-label fw-bold" style="font-size:0.9em">Certificado Tradición y Libertad</label>
                                <!-- <input class="form-control py-4" id="ctl_tramite" name="ctl_tramite" type="text"
                                    value="<?php echo htmlspecialchars($tramite['ctl_tramite']); ?>" readonly> -->

                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-file-earmark-pdf-fill"></i></span>
                                    <input class="form-control" id="ctl_tramite" style="font-size: 0.9em;" name="ctl_tramite" type="text"
                                        value="<?php echo htmlspecialchars($tramite['ctl_tramite']); ?>" readonly>
                                </div>

                                <?php if (!empty($tramite['ctl_tramite'])): ?>
                                    <div class="d-flex justify-content-center gap-2 my-2">
                                        <a href="<?php echo $ruta_pdf . $tramite['ctl_tramite']; ?>" 
                                        target="_blank" class="bot_verenotrapesta btn btn-sm">
                                            <i class="bi bi-box-arrow-right"></i> Ver en otra pestaña
                                        </a>
                                        <button type="button" class="bot_mostrar_vista btn btn-sm"
                                            onclick="toggleIframe('visor_ctl',this)">
                                            <i class="bi bi-eye"></i> <span>Mostrar Vista Previa</span>
                                        </button>
                                    </div>
                                    <div id="visor_ctl" class="iframe-animado">
                                        <iframe src="<?php echo $ruta_pdf . $tramite['ctl_tramite']; ?>" width="100%" height="650px"></iframe>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted my-2">No se ha cargado aún ningún documento</span>
                                <?php endif; ?>
                            </div>

                            <!-- Documento de Identidad -->
                            <div class="col-md-6 p-1 px-3 my-3">
                                <label for="doc_identidad_tramite" class="form-label fw-bold" style="font-size:0.9em"><b>Documento de Identidad</b></label>
                                <!-- <input class="form-control py-4" id="doc_identidad_tramite" name="doc_identidad_tramite" type="text"
                                    value="<?php echo htmlspecialchars($tramite['doc_identidad_tramite']); ?>" readonly> -->

                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                                    <input class="form-control" style="font-size: 0.9em;" id="doc_identidad_tramite" name="doc_identidad_tramite" type="text"
                                        value="<?php echo htmlspecialchars($tramite['doc_identidad_tramite']); ?>" readonly>
                                </div>
                                <?php if (!empty($tramite['doc_identidad_tramite'])): ?>
                                    <div class="d-flex justify-content-center gap-2 my-2">
                                        <a href="<?php echo $ruta_pdf . $tramite['doc_identidad_tramite']; ?>" 
                                        target="_blank" class="bot_verenotrapesta btn btn-sm">
                                            <i class="bi bi-box-arrow-right"></i> Ver en otra pestaña
                                        </a>
                                        <button type="button" class="bot_mostrar_vista btn btn-sm"
                                            onclick="toggleIframe('visor_docid',this)">
                                            <i class="bi bi-eye"></i> <span>Mostrar Vista Previa</span>
                                        </button>
                                    </div>
                                    <div id="visor_docid" class="iframe-animado">
                                        <iframe src="<?php echo $ruta_pdf . $tramite['doc_identidad_tramite']; ?>" width="100%" height="650px"></iframe>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No se ha cargado aún ningún documento</span>
                                <?php endif; ?>
                            </div>

                            <!-- Carta de Autorización -->
                            <div class="col-md-6 p-1 px-3 my-2">
                                <label for="carta_autorizacion_tramite" class="form-label fw-bold" style="font-size:0.9em">Carta Autorización Tramite (en caso de ser tercero)</label>
                                <!-- <input class="form-control py-4" id="carta_autorizacion_tramite" name="carta_autorizacion_tramite" type="text"
                                    value="<?php echo htmlspecialchars($tramite['carta_autorizacion_tramite']); ?>" readonly> -->

                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-envelope-open-fill"></i></span>
                                    <input class="form-control" style="font-size: 0.9em;" id="carta_autorizacion_tramite" name="carta_autorizacion_tramite" type="text"
                                        value="<?php echo htmlspecialchars($tramite['carta_autorizacion_tramite']); ?>" readonly>
                                </div>
                                <?php if (!empty($tramite['carta_autorizacion_tramite'])): ?>
                                    <div class="d-flex justify-content-center gap-2 my-2">
                                        <a href="<?php echo $ruta_pdf . $tramite['carta_autorizacion_tramite']; ?>" 
                                            target="_blank" class="bot_verenotrapesta btn btn-sm">
                                            <i class="bi bi-box-arrow-right"></i> Ver en otra pestaña
                                        </a>
                                        <button type="button" class="bot_mostrar_vista btn btn-sm"
                                            onclick="toggleIframe('visor_autorizacion',this)">
                                            <i class="bi bi-eye"></i> <span>Mostrar Vista Previa</span>
                                        </button>
                                    </div>
                                    <div id="visor_autorizacion" class="iframe-animado">
                                        <iframe src="<?php echo $ruta_pdf . $tramite['carta_autorizacion_tramite']; ?>" width="100%" height="650px"></iframe>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No se ha cargado aún ningún documento</span>
                                <?php endif; ?>
                            </div>

                            <!-- Otros Documentos -->
                            <div class="col-md-6 p-1 px-3 my-2">
                                <label for="otros_doc_tramite" class="form-label fw-bold" style="font-size:0.9em">Otros Documentos Entregados</label>
                                <!-- <input class="form-control py-4" id="otros_doc_tramite" name="otros_doc_tramite" type="text"
                                    value="<?php echo htmlspecialchars($tramite['otros_doc_tramite']); ?>" readonly> -->

                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-file-earmark-pdf"></i></span>
                                    <input class="form-control" id="otros_doc_tramite" style="font-size: 0.9em;" name="otros_doc_tramite" type="text"
                                        value="<?php echo htmlspecialchars($tramite['otros_doc_tramite']); ?>" readonly>
                                </div>
                                <?php if (!empty($tramite['otros_doc_tramite'])): ?>
                                    <div class="d-flex justify-content-center gap-2 my-2">
                                        <a href="<?php echo $ruta_pdf . $tramite['otros_doc_tramite']; ?>"
                                            target="_blank" class="bot_verenotrapesta btn btn-sm">
                                            <i class="bi bi-box-arrow-right"></i> Ver en otra pestaña
                                        </a>
                                        <button type="button" class="bot_mostrar_vista btn btn-sm"
                                            onclick="toggleIframe('visor_otros',this)">
                                            <i class="bi bi-eye"></i> <span>Mostrar Vista Previa</span>
                                        </button>
                                    </div>
                                    <div id="visor_otros" class="iframe-animado">
                                        <iframe src="<?php echo $ruta_pdf . $tramite['otros_doc_tramite']; ?>" width="100%" height="650px"></iframe>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No se ha cargado aún ningún documento</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- SECCIÓN DE DOCUMENTO -->
                <div class="row mt-3 p-2">
                    <div class="col-md-6 mt-3 mt-3 p-1">
                        <div class="rounded-4 mx-2 p-3 h-100" style="background-color: #f8f9fa;">
                            <div class="my-3 text-center" style="border-bottom: 1px solid #002f556e;">
                                <h5 class="mb-3"> <i class="bi bi-file-earmark-richtext me-2 fw-bold "></i>Generar Documento</h5>
                            </div>
                            <form id="formNoProcede">
                                <input type="hidden" name="cod_tramite" id="cod_tramite_hidden_noprocede">
                                <!-- 🔹 AQUÍ van los campos hidden que faltaban -->
                                <input type="hidden" id="mutacion_tramite_modal_noprocede" name="tipo_mutacion_tramite">
                                <input type="hidden" id="tipo_proceso_muta_modal_noprocede" name="tipo_proceso_muta">
                                <input type="hidden" id="fecha_respuesta_tramite_modal_noprocede" name="fecha_respuesta_tramite">
                                <input type="hidden" id="actividad_tramite_modal_noprocede" name="actividad_tramite">
                                <input type="hidden" id="actividad_a_realizar_modal_noprocede" name="actividad_tramite">
                                <div class="form-group my-3">
                                    <label for="tipo_documento" class="form-label my-2 fw-bold">
                                        <i class="fas fa-file-alt me-1"></i>
                                        Tipo de Documento:
                                    </label>
                                    <select name="tipo_documento" id="tipo_doc_noprocede" class="form-select" disabled>
                                        <option value="Rechazado" selected>Rechazado</option>
                                    </select>
                                </div>
                                <div class="form-group my-3">
                                    <label for="contenido_html" class="form-label fw-bold">
                                        <i class="bi bi-pen"></i>
                                        Contenido del Documento:
                                    </label>
                                    <div id="contenido_html_noprocede" contenteditable="true" class="contenteditable-document" name="contenido_html"></div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-6 mt-3 p-1 h-100">
                        <div class="card shadow border rounded-4 mx-2 p-3">
                            <div style="background-color: #002F55;" class="py-3 px-3 rounded-3 text-white d-flex justify-content-between">
                                <h5 class="mb-0"> <i class="bi bi-eye-fill me-2"></i><b>Vista Previa</b></h5>
                                <span class="badge bg-light text-dark mb-0">En tiempo real</span>
                            </div>
                            <iframe id="previewDocumentoNoProcede" style="width:100%;height:750px;border:1px solid #ccc;" class="mt-2 rounded-3"></iframe>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-danger" id="btnGuardarNoProcede">Guardar y Enviar</button>
            </div>
        </div>
    </div>
</div>

<!-- ---------- MODAL: NO PROCEDE COMPLETAR ----------------->
<div class="modal fade rounded-4" id="modalNoProcedeCompletar" tabindex="-1" aria-labelledby="modalNoProcedeCompletarLabel" aria-hidden="true" style="z-index:1999">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning text-black px-3 py-3">
                <h5 class="modal-title w-100 text-center" id="modalNoProcedeCompletarLabel">
                    No Procede Completar - Generación de Documento
                </h5>
                <button type="button" class="btn-close btn-white" style="background-color:white !important" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4">
                <div id="infoTramiteContainerNoProcedeCompletar">


                    <div class="info-banner mb-4 mt-2 text-center">
                        <i class="fas fa-info-circle"></i>
                        <p class="mb-0">
                            Desde aquí puedes <strong>generar el certificado de NO PROCEDENCIA POR COMPLETITUD</strong> para el usuario que solicitó el trámite.
                        </p>
                    </div>

                    <div class="card border rounded-4 shadow p-3">
                        <div class="card-header rounded-3" style="background-color: #002F55;">
                            <h5 class="text-white text-center mb-0">Información del trámite</h5>
                        </div>

                        <div class="row mt-2 px-2 text-start">
                            <!-- <div class="col-md-4">
                                <label><b>ID Radicación</b></label>
                                <input type="text" id="cod_tramite_modal_noprocedecompletar" class="form-control" readonly>
                            </div> -->

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">ID Radicación</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi-journal-text"></i></span>
                                    <input type="text" id="cod_tramite_modal_noprocedecompletar" class="form-control" readonly>
                                </div>
                            </div>

                            <!-- <div class="col-md-4">
                                <label><b>Fecha Radicación</b></label>
                                <input type="text" id="fecha_rad_modal_noprocedecompletar" class="form-control" readonly>
                            </div> -->

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Fecha Radicación</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar2-event"></i></span>
                                    <input type="text" id="fecha_rad_modal_noprocedecompletar" class="form-control" readonly>
                                </div>
                            </div>

                            <!-- <div class="col-md-4">
                                <label><b>Tipo de Trámite</b></label>
                                <input type="text" id="tramite_modal_noprocedecompletar" class="form-control" readonly>
                            </div> -->

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Tipo de Trámite</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-file-earmark-text-fill"></i></span>
                                    <input type="text" id="tramite_modal_noprocedecompletar" class="form-control" readonly>
                                </div>
                            </div>

                            <!-- <div class="col-md-6">
                                <label><b>Nombre Completo del Interesado</b></label>
                                <input type="text" id="nombre_interesado_modal_noprocedecompletar" class="form-control" readonly>
                            </div> -->

                            <div class="col-md-6 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Nombre Completo del Interesado</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-fill"></i></span>
                                    <input type="text" id="nombre_interesado_modal_noprocedecompletar" class="form-control" readonly>
                                </div>
                            </div>

                            <!-- <div class="col-md-3">
                                <label><b>Teléfono</b></label>
                                <input type="text" id="telefono_modal_noprocedecompletar" class="form-control" readonly>
                            </div> -->

                            <div class="col-md-3 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Teléfono</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-telephone-fill me-2"></i>+57</span>
                                    <input type="text" id="telefono_modal_noprocedecompletar" class="form-control" readonly>
                                </div>
                            </div>

                            <!-- <div class="col-md-3">
                                <label><b>Correo Electrónico</b></label>
                                <input type="text" id="correo_modal_noprocedecompletar" class="form-control" readonly>
                            </div> -->

                            <div class="col-md-3 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Correo Electrónico</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at-fill"></i></span>
                                    <input type="text" id="correo_modal_noprocedecompletar" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border mt-3 rounded-4 shadow p-3">
                        <div class="card-header rounded-3" style="background-color: #002F55;">
                            <h5 class="text-white text-center mb-0">Información del predio</h5>
                        </div>
                        <div class="row mt-2 px-2 text-start">

                            <!-- <div class="col-md-4">
                                <label><b>Código Catastral (NPN)</b></label>
                                <input type="text" id="npn_modal_noprocedecompletar" class="form-control" readonly>
                            </div> -->

                            <div class="col-md-5 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Código Catastral (NPN)</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-map"></i></span>
                                    <input type="text" id="npn_modal_noprocedecompletar" class="form-control" readonly>
                                </div>
                            </div>

                            <!-- <div class="col-md-4">
                                <label><b>FMI Predio</b></label>
                                <input type="text" id="fmi_modal_noprocedecompletar" class="form-control" readonly>
                            </div> -->

                            <div class="col-md-3 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">FMI Predio</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-map-fill"></i></span>
                                    <input type="text" id="fmi_modal_noprocedecompletar" class="form-control" readonly>
                                </div>
                            </div>

                            <!-- <div class="col-md-4">
                                <label><b>Dirección del Predio</b></label>
                                <input type="text" id="direccion_modal_noprocedecompletar" class="form-control" readonly>
                            </div> -->

                            <div class="col-md-4 p-1 px-2 my-2">
                                <label class="form-label fw-bold" style="font-size:0.9em;">Dirección del Predio</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-signpost-2-fill"></i></span>
                                    <input type="text" id="direccion_modal_noprocedecompletar" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label><b>Propietarios del Predio</b></label>
                                <textarea id="propietarios_modal_noprocedecompletar" class="form-control" rows="2" readonly></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- sección de documentos -->

                    <div class="card border mt-3 rounded-4 shadow p-3">

                        <div class="card-header rounded-3" style="background-color: #002F55;">
                            <h5 class="text-white text-center mb-0">Documentación</h5>
                        </div>

                        <div class="form-row">
                            <div class="col-md-6 p-1 px-3 my-3">
                                <label for="sol_escrita_tramite"><b>Solicitud Escrita</b></label>
                                <!-- <input class="form-control py-4" id="sol_escrita_tramite" name="sol_escrita_tramite" type="text"
                                    value="<?php echo htmlspecialchars($tramite['sol_escrita_tramite']); ?>" readonly> -->
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-file-earmark-pdf-fill"></i></span>
                                    <input type="text" class="form-control" style="font-size: 0.9em;" id="sol_escrita_tramite" name="sol_escrita_tramite"
                                        value="<?php echo htmlspecialchars($tramite['sol_escrita_tramite']); ?>" readonly>
                                </div>
                                <?php if (!empty($tramite['sol_escrita_tramite'])): ?>
                                    <div class="d-flex justify-content-center gap-2 my-2">
                                        <a href="<?php echo $ruta_pdf . $tramite['sol_escrita_tramite']; ?>" 
                                            target="_blank" class="bot_verenotrapesta btn btn-sm">
                                            <i class="bi bi-box-arrow-right"></i> Ver en otra pestaña
                                        </a>
                                        <button type="button" class="bot_mostrar_vista btn btn-sm"
                                            onclick="toggleIframe('visor_sol_escrita_completar',this)">
                                            <i class="bi bi-eye"></i> <span>Mostrar Vista Previa</span>
                                        </button>
                                    </div>
                                    <div id="visor_sol_escrita_completar" class="iframe-animado">
                                        <iframe src="<?php echo $ruta_pdf . $tramite['sol_escrita_tramite']; ?>" width="100%" height="650px"></iframe>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No se ha cargado aún ningún documento</span>
                                <?php endif; ?>
                            </div>
                            <!-- Copia de Escritura -->
                            <div class="col-md-6 p-1 px-3 my-3">
                                <label for="cop_escritura_tramite" class="form-label fw-bold" style="font-size:0.9em">Copia de Escritura - Sentencia Judicial - Acto Administrativo</label>
                                <!-- <input class="form-control py-4" id="cop_escritura_tramite" name="cop_escritura_tramite" type="text"
                                    value="<?php echo htmlspecialchars($tramite['cop_escritura_tramite']); ?>" readonly> -->
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-file-earmark-pdf-fill"></i></span>
                                    <input class="form-control" id="cop_escritura_tramite" style="font-size: 0.9em;" name="cop_escritura_tramite" type="text"
                                        value="<?php echo htmlspecialchars($tramite['cop_escritura_tramite']); ?>" readonly>
                                </div>

                                <?php if (!empty($tramite['cop_escritura_tramite'])): ?>
                                    <div class="d-flex justify-content-center gap-2 my-2">
                                        <a href="<?php echo $ruta_pdf . $tramite['cop_escritura_tramite']; ?>" 
                                            target="_blank" class="bot_verenotrapesta btn btn-sm">
                                            <i class="bi bi-box-arrow-right"></i> Ver en otra pestaña
                                        </a>
                                        <button type="button" class="bot_mostrar_vista btn btn-sm"
                                            onclick="toggleIframe('visor_escritura_completar',this)">
                                            <i class="bi bi-eye"></i> <span>Mostrar Vista Previa</span>
                                        </button>
                                    </div>
                                    <div id="visor_escritura_completar" class="iframe-animado">
                                        <iframe src="<?php echo $ruta_pdf . $tramite['cop_escritura_tramite']; ?>" width="100%" height="650px"></iframe>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No se ha cargado aún ningún documento</span>
                                <?php endif; ?>
                            </div>
                            <!-- Certificado Tradición -->
                            <div class="col-md-6 p-1 px-3 my-3">
                                <label for="ctl_tramite" class="form-label fw-bold" style="font-size:0.9em">Certificado Tradición y Libertad</label>
                                <!-- <input class="form-control py-4" id="ctl_tramite" name="ctl_tramite" type="text"
                                    value="<?php echo htmlspecialchars($tramite['ctl_tramite']); ?>" readonly> -->

                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-file-earmark-pdf-fill"></i></span>
                                    <input class="form-control" id="ctl_tramite" style="font-size: 0.9em;" name="ctl_tramite" type="text"
                                        value="<?php echo htmlspecialchars($tramite['ctl_tramite']); ?>" readonly>
                                </div>

                                <?php if (!empty($tramite['ctl_tramite'])): ?>
                                    <div class="d-flex justify-content-center gap-2 my-2">
                                        <a href="<?php echo $ruta_pdf . $tramite['ctl_tramite']; ?>" 
                                        target="_blank" class="bot_verenotrapesta btn btn-sm">
                                            <i class="bi bi-box-arrow-right"></i> Ver en otra pestaña
                                        </a>
                                        <button type="button" class="bot_mostrar_vista btn btn-sm"
                                            onclick="toggleIframe('visor_ctl_completar',this)">
                                            <i class="bi bi-eye"></i> <span>Mostrar Vista Previa</span>
                                        </button>
                                    </div>
                                    <div id="visor_ctl_completar" class="iframe-animado">
                                        <iframe src="<?php echo $ruta_pdf . $tramite['ctl_tramite']; ?>" width="100%" height="650px"></iframe>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No se ha cargado aún ningún documento</span>
                                <?php endif; ?>
                            </div>
                            <!-- Documento de Identidad -->
                            <div class="col-md-6 p-1 px-3 my-3">
                                <label for="doc_identidad_tramite" class="form-label fw-bold" style="font-size:0.9em"><b>Documento de Identidad</b></label>
                                <!-- <input class="form-control py-4" id="doc_identidad_tramite" name="doc_identidad_tramite" type="text"
                                    value="<?php echo htmlspecialchars($tramite['doc_identidad_tramite']); ?>" readonly> -->

                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                                    <input class="form-control" style="font-size: 0.9em;" id="doc_identidad_tramite" name="doc_identidad_tramite" type="text"
                                        value="<?php echo htmlspecialchars($tramite['doc_identidad_tramite']); ?>" readonly>
                                </div>

                                <?php if (!empty($tramite['doc_identidad_tramite'])): ?>
                                    <div class="d-flex justify-content-center gap-2 my-2">
                                        <a href="<?php echo $ruta_pdf . $tramite['doc_identidad_tramite']; ?>" 
                                        target="_blank" class="bot_verenotrapesta btn btn-sm">
                                            <i class="bi bi-box-arrow-right"></i> Ver en otra pestaña
                                        </a>
                                        <button type="button" class="bot_mostrar_vista btn btn-sm"
                                            onclick="toggleIframe('visor_docid_completar',this)">
                                            <i class="bi bi-eye"></i> <span>Mostrar Vista Previa</span>
                                        </button>
                                    </div>
                                    <div id="visor_docid_completar" class="iframe-animado">
                                        <iframe src="<?php echo $ruta_pdf . $tramite['doc_identidad_tramite']; ?>" width="100%" height="650px"></iframe>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No se ha cargado aún ningún documento</span>
                                <?php endif; ?>
                            </div>
                            <!-- Carta de Autorización -->
                            <div class="col-md-6 p-1 px-3 my-2">
                                <label for="carta_autorizacion_tramite" class="form-label fw-bold" style="font-size:0.9em">Carta Autorización Tramite (en caso de ser tercero)</label>
                                <!-- <input class="form-control py-4" id="carta_autorizacion_tramite" name="carta_autorizacion_tramite" type="text"
                                    value="<?php echo htmlspecialchars($tramite['carta_autorizacion_tramite']); ?>" readonly> -->

                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-envelope-open-fill"></i></span>
                                    <input class="form-control" style="font-size: 0.9em;" id="carta_autorizacion_tramite" name="carta_autorizacion_tramite" type="text"
                                        value="<?php echo htmlspecialchars($tramite['carta_autorizacion_tramite']); ?>" readonly>
                                </div>

                                <?php if (!empty($tramite['carta_autorizacion_tramite'])): ?>
                                    <div class="d-flex justify-content-center gap-2 my-2">
                                        <a href="<?php echo $ruta_pdf . $tramite['carta_autorizacion_tramite']; ?>" 
                                        target="_blank" class="bot_verenotrapesta btn btn-sm">
                                            <i class="bi bi-box-arrow-right"></i> Ver en otra pestaña
                                        </a>
                                        <button type="button" class="bot_mostrar_vista btn btn-sm"
                                            onclick="toggleIframe('visor_autorizacion_completar',this)">
                                            <i class="bi bi-eye"></i> Mostrar/Ocultar Vista Previa
                                        </button>
                                    </div>
                                    <div id="visor_autorizacion_completar" class="iframe-animado">
                                        <iframe src="<?php echo $ruta_pdf . $tramite['carta_autorizacion_tramite']; ?>" width="100%" height="650px"></iframe>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No se ha cargado aún ningún documento</span>
                                <?php endif; ?>
                            </div>
                            <!-- Otros Documentos -->
                            <div class="col-md-6 p-1 px-3 my-2">
                                <label for="otros_doc_tramite" class="form-label fw-bold" style="font-size:0.9em">Otros Documentos Entregados</label>
                                <!-- <input class="form-control py-4" id="otros_doc_tramite" name="otros_doc_tramite" type="text"
                                    value="<?php echo htmlspecialchars($tramite['otros_doc_tramite']); ?>" readonly> -->

                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-file-earmark-pdf"></i></span>
                                    <input class="form-control" id="otros_doc_tramite" style="font-size: 0.9em;" name="otros_doc_tramite" type="text"
                                        value="<?php echo htmlspecialchars($tramite['otros_doc_tramite']); ?>" readonly>
                                </div>

                                <?php if (!empty($tramite['otros_doc_tramite'])): ?>
                                    <div class="d-flex justify-content-center gap-2 my-2">
                                        <a href="<?php echo $ruta_pdf . $tramite['otros_doc_tramite']; ?>" 
                                            target="_blank" class="bot_verenotrapesta btn btn-sm">
                                            <i class="bi bi-box-arrow-right"></i> Ver en otra pestaña
                                        </a>
                                        <button type="button" class="bot_mostrar_vista btn btn-sm"
                                            onclick="toggleIframe('visor_otros_completar',this)">
                                            <i class="bi bi-eye"></i> <span>Mostrar Vista Previa</span>
                                        </button>
                                    </div>
                                    <div id="visor_otros_completar" class="iframe-animado">
                                        <iframe src="<?php echo $ruta_pdf . $tramite['otros_doc_tramite']; ?>" width="100%" height="650px"></iframe>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No se ha cargado aún ningún documento</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- SECCIÓN DE DOCUMENTO (en el modal No Procede Completar) -->
                <div class="mt-3 p-2 d-flex align-items-stretch">
                    <div class="col-md-6 mt-3 p-1 d-flex">
                        <div class="rounded-4 mx-2 p-3 flex-fill" style="background-color: #f8f9fa;">
                            <div class="my-3 text-center" style="border-bottom: 1px solid #002f556e;">
                                <h5 class="mb-3"> <i class="bi bi-file-earmark-richtext me-2 fw-bold "></i>Generar Documento</h5>
                            </div>
                            <form id="formNoProcedeCompletar">
                                <input type="hidden" name="cod_tramite" id="cod_tramite_hidden_noprocedecompletar">
                                <input type="hidden" id="mutacion_tramite_modal_noprocedecompletar" name="tipo_mutacion_tramite">
                                <input type="hidden" id="tipo_proceso_muta_modal_noprocedecompletar" name="tipo_proceso_muta">
                                <input type="hidden" id="fecha_respuesta_tramite_modal_noprocedecompletar" name="fecha_respuesta_tramite">
                                <input type="hidden" id="actividad_tramite_modal_noprocedecompletar" name="actividad_tramite">

                                <div class="form-group">
                                    <label for="tipo_documento" class="form-label my-2 fw-bold">
                                        <i class="fas fa-file-alt me-1"></i>
                                        Tipo de Documento:
                                    </label>
                                    <select name="tipo_documento" id="tipo_doc_noprocedecompletar" class="form-select" disabled>
                                        <option value="No Procede Completar" selected>No Procede Completar</option>
                                    </select>
                                </div>

                                <div class="form-group my-3">
                                    <label for="contenido_html" class="form-label fw-bold">
                                        <i class="bi bi-pen"></i>
                                        Contenido del Documento (Editable):
                                    </label>
                                    <div id="contenido_html_noprocedecompletar"
                                        contenteditable="true"
                                        class="contenteditable-document"
                                        style="background-color: #f8f9fa; border: 2px solid #dee2e6; min-height: 350px; padding: 15px; border-radius: 4px; overflow-y: auto;">
                                    </div>
                                    <small class="text-muted d-block my-2 text-center"> Puedes editar directamente el contenido. Usa negrita (Ctrl+B o Cmd+B) para destacar texto.</small>
                                </div>

                                <div class="form-group">
                                    <label class="my-2"><b>Observación:</b></label>
                                    <textarea class="form-control" id="observacion_noprocedecompletar" name="observacion" rows="3" placeholder="Ingrese observaciones adicionales"></textarea>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-md-6 mt-3 p-1 d-flex">
                        <div class="card shadow border rounded-4 mx-2 p-3 flex-fill">
                            <div style="background-color: #002F55;" class="py-3 px-3 rounded-3 text-white d-flex justify-content-between">
                                <h5 class="mb-0"> <i class="bi bi-eye-fill me-2"></i><b>Vista Previa</b></h5>
                                <span class="badge bg-light text-dark mb-0">En tiempo real</span>
                            </div>
                            <iframe id="previewDocumentoNoProcedeCompletar" style="width:100%;height:100%;border:1px solid #ccc;" class="mt-2 rounded-3"></iframe>
                        </div>
                    </div>
                </div>

                <style>
                    .contenteditable-document {
                        /* height: auto !important; */
                        min-height: 350px;
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        white-space: pre-wrap;
                        word-wrap: break-word;
                        border: 2px solid #dee2e6;
                        padding: 15px;
                        overflow-y: auto;
                        resize: vertical;
                        background-color: #f8f9fa;
                    }

                    .contenteditable-document:focus {
                        outline: none;
                        border-color: #0d6efd;
                        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
                    }
                </style>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-warning" id="btnGuardarNoProcedeCompletar">Guardar y Enviar</button>
            </div>
        </div>
    </div>
</div>

<!-- script para función de los toast y reemplazar los alerts -->

<script>
    function mostrarToast(titulo, mensaje, tipo = 'info') {
        const toastEl = document.getElementById('mensajeToast');
        const toastHeader = toastEl.querySelector('.toast-header'); // <- obtenemos el header completo
        const toastTitle = document.getElementById('toastTitle');
        const toastBody = document.getElementById('toastBody');

        // Limpiar clases previas del header
        toastHeader.className = 'toast-header';

        // Colores según tipo
        const colores = {
            info: 'bg-primary text-white',
            success: 'bg-success text-white',
            error: 'bg-danger text-white',
            warning: 'bg-warning text-dark'
        };

        // Aplicar color al header
        toastHeader.classList.add(...(colores[tipo]?.split(' ') || ['bg-primary', 'text-white']));

        toastTitle.textContent = titulo;
        toastBody.innerHTML = mensaje;

        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    }
</script>




<!---------------------------------------------------Scripts no procede trámite------------------------------------------ -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnNoProcede = document.getElementById('btnNoProcede');
        const iframePreview = document.getElementById('previewDocumentoNoProcede');
        const editableDiv = document.getElementById('contenido_html_noprocede');
        if (btnNoProcede) {
            btnNoProcede.addEventListener('click', async function() {
                const cod = document.getElementById('cod_tramite').value;
                const mutacion = document.getElementById('mutacion_tramite')?.value || '';
                const tipoProceso = document.getElementById('tipo_proceso_muta')?.value || '';
                const fechaRespuesta = document.getElementById('fecha_respuesta_tramite')?.value || '';
                const url = new URL('vistas/tramites/acciones/cargar_datos_no_aprobados.php', window.location.href);
                url.searchParams.append('cod_tramite', cod);
                url.searchParams.append('mutacion_tramite', mutacion);
                url.searchParams.append('tipo_proceso_muta', tipoProceso);
                url.searchParams.append('fecha_respuesta_tramite', fechaRespuesta);
                url.searchParams.append('modo', 'no_procede');
                try {
                    const res = await fetch(url);
                    const data = await res.json();
                    if (!data.success) {
                        mostrarToast('Atención', 'No se encontraron datos del trámite.', 'warning');
                        return;
                    }
                    const t = data.tramite;
                    document.getElementById('cod_tramite_modal_noprocede').value = t.cod_tramite;
                    document.getElementById('fecha_rad_modal_noprocede').value = t.fecha_radicacion;
                    document.getElementById('tramite_modal_noprocede').value = t.mutacion_tramite;
                    document.getElementById('nombre_interesado_modal_noprocede').value = t.nombre_interesado;
                    document.getElementById('telefono_modal_noprocede').value = t.telefono_interesado;
                    document.getElementById('correo_modal_noprocede').value = t.correo_interesado;
                    document.getElementById('npn_modal_noprocede').value = t.npn_predio_tram;
                    document.getElementById('fmi_modal_noprocede').value = t.fmi_predio || '';
                    document.getElementById('direccion_modal_noprocede').value = t.direccion_predio;
                    document.getElementById('propietarios_modal_noprocede').value = t.propietarios;

                    editableDiv.innerHTML = t.plantilla.replace(/<br\s*\/?>/gi, '<br>');
                    iframePreview.srcdoc = `
                        <div style="
                            padding:25px;
                            font-family:Arial;
                            line-height:1.6;
                            white-space:pre-wrap;
                        ">
                            ${editableDiv.innerHTML}
                        </div>
                    `;
                    document.getElementById('cod_tramite_hidden_noprocede').value = t.cod_tramite;
                    const modal = new bootstrap.Modal(document.getElementById('modalNoProcede'));
                    modal.show();
                    editableDiv.addEventListener('input', () => {
                        iframePreview.srcdoc = `
                            <div style="
                                padding:25px;
                                font-family:Arial;
                                line-height:1.6;
                                white-space:pre-wrap;
                            ">
                                ${editableDiv.innerHTML}
                            </div>
                        `;
                    });
                } catch (error) {
                    console.error('Error cargando datos del trámite:', error);
                    mostrarToast('Advertencia', 'Error cargando los datos del trámite.', 'warning');
                }
            });
        }
    });
</script>
<script>
    // script para guardar trámite no procede
    document.addEventListener('DOMContentLoaded', function() {
        const btnGuardarNoProcede = document.getElementById('btnGuardarNoProcede');
        const editableDiv = document.getElementById('contenido_html_noprocede');
        if (!btnGuardarNoProcede) return;
        btnGuardarNoProcede.addEventListener('click', function() {

            const mutacion = document.getElementById('mutacion_tramite');
            const tipoProceso = document.getElementById('tipo_proceso_muta');
            const fechaResp = document.getElementById('fecha_respuesta_tramite');
            const actividad = document.getElementById('actividad_tramite');

            if (!mutacion || !tipoProceso || !fechaResp || !actividad) {
                mostrarToast('Advertencia', 'Error: Faltan campos en el formulario.', 'warning');
                return;
            }
            if (!fechaResp.value) {
                mostrarToast('Advertencia', 'Debes seleccionar la fecha de respuesta del trámite.', 'warning');
                return;
            }
            document.getElementById('mutacion_tramite_modal_noprocede').value = mutacion.value;
            document.getElementById('tipo_proceso_muta_modal_noprocede').value = tipoProceso.value;
            document.getElementById('fecha_respuesta_tramite_modal_noprocede').value = fechaResp.value;
            document.getElementById('actividad_tramite_modal_noprocede').value = actividad.value;


            const formData = new FormData(document.getElementById('formNoProcede'));


            formData.append('nombre_interesado', document.getElementById('nombre_interesado_modal_noprocede').value);
            formData.append('telefono_interesado', document.getElementById('telefono_modal_noprocede').value);
            formData.append('correo_interesado', document.getElementById('correo_modal_noprocede').value);
            formData.append('cod_catastro', document.getElementById('npn_modal_noprocede').value);
            formData.append('fmi_predio', document.getElementById('fmi_modal_noprocede').value);
            formData.append('direccion_predio', document.getElementById('direccion_modal_noprocede').value);
            formData.append('propietarios_predio', document.getElementById('propietarios_modal_noprocede').value);
            formData.append('cont_documento', editableDiv.innerHTML);
            formData.append('tipo_oficio', 'Rechazado');
            formData.append('modo', 'no_procede');

            console.log('FormData a enviar (No Procede):');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
            fetch('vistas/tramites/acciones/guardar_no_procede.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        return res.text().then(text => {
                            throw new Error(`HTTP ${res.status}: ${text}`);
                        });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        window.location.href = data.redirect || 'index.php?page=tramites/consultar_tramite';
                    } else {
                        alert('Error: ' + (data.error || 'Error desconocido'));
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Error al enviar la solicitud: ' + err.message);
                });


        });
    });
</script>
<!------------------------------------------------ fin script no procede ------------------------------------------------->



<!-- -----------------------------------------script no procede completar----------------------------------------- -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnNoProcedeCompletar = document.getElementById('btnNoProcedeCompletar');
        const iframePreviewCompletar = document.getElementById('previewDocumentoNoProcedeCompletar');
        const editableDiv = document.getElementById('contenido_html_noprocedecompletar');

        if (btnNoProcedeCompletar) {
            btnNoProcedeCompletar.addEventListener('click', async function() {
                const cod = document.getElementById('cod_tramite').value;
                const mutacion = document.getElementById('mutacion_tramite')?.value || '';
                const tipoProceso = document.getElementById('tipo_proceso_muta')?.value || '';
                const fechaRespuesta = document.getElementById('fecha_respuesta_tramite')?.value || '';
                const url = new URL('vistas/tramites/acciones/cargar_datos_no_procede.php', window.location.href);

                url.searchParams.append('cod_tramite', cod);
                url.searchParams.append('mutacion_tramite', mutacion);
                url.searchParams.append('tipo_proceso_muta', tipoProceso);
                url.searchParams.append('fecha_respuesta_tramite', fechaRespuesta);
                url.searchParams.append('modo', 'no_procede_completar');

                try {
                    console.log('Solicitando datos desde:', url.toString());

                    const res = await fetch(url);
                    const data = await res.json();
                    console.log('Respuesta recibida:', data);

                    if (!data.success) {
                        mostrarToast('Atención', 'No se encontraron datos del trámite.', 'warning');
                        return;
                    }

                    const t = data.tramite;

                    document.getElementById('cod_tramite_modal_noprocedecompletar').value = t.cod_tramite;
                    document.getElementById('fecha_rad_modal_noprocedecompletar').value = t.fecha_radicacion;
                    document.getElementById('tramite_modal_noprocedecompletar').value = t.mutacion_tramite;
                    document.getElementById('nombre_interesado_modal_noprocedecompletar').value = t.nombre_interesado;
                    document.getElementById('telefono_modal_noprocedecompletar').value = t.telefono_interesado;
                    document.getElementById('correo_modal_noprocedecompletar').value = t.correo_interesado;
                    document.getElementById('npn_modal_noprocedecompletar').value = t.npn_predio_tram;
                    document.getElementById('fmi_modal_noprocedecompletar').value = t.fmi_predio || '';
                    document.getElementById('direccion_modal_noprocedecompletar').value = t.direccion_predio;
                    document.getElementById('propietarios_modal_noprocedecompletar').value = t.propietarios;

                    editableDiv.innerHTML = t.plantilla;

                    actualizarVistaPreviaCompletar();

                    document.getElementById('cod_tramite_hidden_noprocedecompletar').value = t.cod_tramite;

                    const modal = new bootstrap.Modal(document.getElementById('modalNoProcedeCompletar'));
                    modal.show();

                    // Actualizar vista previa en tiempo real mientras se edita
                    editableDiv.addEventListener('input', actualizarVistaPreviaCompletar);

                    // Guardar código del trámite oculto
                    document.getElementById('cod_tramite_hidden_noprocedecompletar').value = t.cod_tramite;

                } catch (error) {
                    console.error('Error cargando datos del trámite:', error);
                    mostrarToast('Error cargando los datos del trámite: ');
                }
            });
        }

        // Función para actualizar la vista previa IGUAL A NO PROCEDE
        function actualizarVistaPreviaCompletar() {
            const contenido = editableDiv.innerHTML;
            iframePreviewCompletar.srcdoc = `
            <div style="
                padding:25px;
                font-family:Arial;
                line-height:1.6;
                white-space:pre-wrap;
            ">
                ${contenido}
            </div>
        `;
        }
    });
</script>
<script>
    // script para guardar el no procede completar
    document.addEventListener('DOMContentLoaded', function() {
        const btnGuardarNoProcedeCompletar = document.getElementById('btnGuardarNoProcedeCompletar');
        const editableDiv = document.getElementById('contenido_html_noprocedecompletar');

        if (!btnGuardarNoProcedeCompletar) return;

        btnGuardarNoProcedeCompletar.addEventListener('click', function() {
            const mutacion = document.getElementById('mutacion_tramite');
            const tipoProceso = document.getElementById('tipo_proceso_muta');
            const fechaResp = document.getElementById('fecha_respuesta_tramite');
            const actividad = document.getElementById('actividad_tramite');

            if (!mutacion || !tipoProceso || !fechaResp || !actividad) {
                mostrarToast('Advertencia', 'Error: Faltan campos en el formulario.', 'warning');
                return;
            }

            if (!fechaResp.value) {
                mostrarToast('Advertencia', 'Debes seleccionar la fecha de respuesta del trámite.', 'warning');
                return;
            }

            // Copiar valores a los campos hidden
            document.getElementById('mutacion_tramite_modal_noprocedecompletar').value = mutacion.value;
            document.getElementById('tipo_proceso_muta_modal_noprocedecompletar').value = tipoProceso.value;
            document.getElementById('fecha_respuesta_tramite_modal_noprocedecompletar').value = fechaResp.value;
            document.getElementById('actividad_tramite_modal_noprocedecompletar').value = actividad.value;

            const formData = new FormData(document.getElementById('formNoProcedeCompletar'));

            formData.append('nombre_interesado', document.getElementById('nombre_interesado_modal_noprocedecompletar').value);
            formData.append('telefono_interesado', document.getElementById('telefono_modal_noprocedecompletar').value);
            formData.append('correo_interesado', document.getElementById('correo_modal_noprocedecompletar').value);
            formData.append('cod_catastro', document.getElementById('npn_modal_noprocedecompletar').value);
            formData.append('fmi_predio', document.getElementById('fmi_modal_noprocedecompletar').value);
            formData.append('direccion_predio', document.getElementById('direccion_modal_noprocedecompletar').value);
            formData.append('propietarios_predio', document.getElementById('propietarios_modal_noprocedecompletar').value);

            // 🔑 GUARDAR HTML COMPLETO CON <b> Y <br> (IGUAL A NO PROCEDE)
            formData.append('cont_documento', editableDiv.innerHTML);

            formData.append('tipo_oficio', 'No Procede Completar');
            formData.append('modo', 'no_procede_completar');
            formData.append('observacion', document.getElementById('observacion_noprocedecompletar').value);

            console.log('FormData a enviar (No Procede Completar):');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            fetch('vistas/tramites/acciones/guardar_no_procede_completar.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        return res.text().then(text => {
                            throw new Error(`HTTP ${res.status}: ${text}`);
                        });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        mostrarToast('Atención', 'Documento guardado correctamente.', 'success');
                        window.location.href = data.redirect || 'index.php?page=tramites/consultar_tramite';
                    } else {
                        alert('Error: ' + (data.error || 'Error desconocido'));
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Error al enviar la solicitud: ' + err.message);
                });
        });
    });
</script>
<!---------------------------------------- fin script no procede completar ------------------------------------->


<script>
    //Función para mostrar los datos al asignar el trámite
    let usuariosData = [];

    document.getElementById('rol_asignado').addEventListener('change', function() {
        const rol = this.value;

        fetch('vistas/tramites/acciones/consulta_rol_asig2.php?rol=' + rol)
            .then(response => response.json())
            .then(data => {
                usuariosData = data; // Guarda todos los usuarios para acceder luego
                const select = document.getElementById('usuario_asignado');
                select.innerHTML = '<option value="">Selecciona un usuario</option>';

                data.forEach(usuario => {
                    const option = document.createElement('option');
                    option.value = usuario.id_usuario;
                    option.text = `${usuario.nombre} ${usuario.apellido}  -  (${usuario.usuario_cons})`;
                    select.appendChild(option);
                });

                // Limpia los campos al cambiar de rol
                document.getElementById('nombre_usuario').value = '';
                document.getElementById('apellido_usuario').value = '';
                document.getElementById('correo_usuario').value = '';
                document.getElementById('celular_usuario').value = '';
                document.getElementById('cedula').value = '';
                document.getElementById('rol_usuario').value = '';
            })
            .catch(error => {
                console.error('Error al cargar usuarios:', error);
            });
    });

    document.getElementById('usuario_asignado').addEventListener('change', function() {
        const selectedId = this.value;
        const usuario = usuariosData.find(u => u.id_usuario == selectedId); // usar == para coincidir tipo

        if (usuario) {
            document.getElementById('nombre_usuario').value = usuario.nombre;
            document.getElementById('apellido_usuario').value = usuario.apellido;
            document.getElementById('correo_usuario').value = usuario.correo_usuario;
            document.getElementById('celular_usuario').value = usuario.celular_usuario;
            document.getElementById('cedula').value = usuario.cedula;
            document.getElementById('rol_usuario').value = usuario.rol_usuario;
        } else {
            document.getElementById('nombre_usuario').value = '';
            document.getElementById('apellido_usuario').value = '';
            document.getElementById('correo_usuario').value = '';
            document.getElementById('celular_usuario').value = '';
        }
    });

    let contadorDocs = 1;
    const maxDocs = 5;

    document.getElementById("agregarDocumento").addEventListener("click", function() {
        if (contadorDocs >= maxDocs) {
            const maxDocsModal = new bootstrap.Modal(document.getElementById('maxDocsModal'));
            maxDocsModal.show();
            return;
        }

        contadorDocs++;
        const container = document.getElementById("documentosContainer");

        const nuevoGrupo = document.createElement("div");
        nuevoGrupo.classList.add("form-row", "documento-group", "mt-3");

        nuevoGrupo.innerHTML = `
        <div class="col-md-6 p-1 px-2 my-2 ">
            <label for="tipo_doc1" class="form-label" style="font-size:0.9em;">
                <b>Seleccione el trámite</b>
            </label>
            <div class="input-group shadow-sm">
                <label class="input-group-text" for="tipo_doc1">
                    <i class="bi bi-file-earmark-text-fill"></i>
                </label>
                <select class="form-select" id="tipo_doc1" style="font-size:0.9em;" name="tipo_doc${contadorDocs}" required>
                    <option value="" disabled selected>SELECCIONE</option>
                    <option value="Avaluo_Catastral">Avaluo Catastral</option>
                    <option value="Acto_Administrativo">Acto Administrativo</option>
                    <option value="Acto_Judicial">Acto Judicial</option>
                    <option value="Consulta_VUR">Consulta VUR</option>
                    <option value="Devolución_Tramite">Devolución Tramite</option>
                    <option value="Documento_Privado">Documento Privado</option>
                    <option value="Escritura_Publica">Escrituras Públicas</option>
                    <option value="Impuesto_Predial">Impuesto Predial</option>
                    <option value="Informe_Visita">Informe de Visita</option>
                    <option value="Informe_Edicion">Informe de Edición</option>
                    <option value="Informe_Calidad">Informe de Calidad</option>
                    <option value="Manzana_Catastral">Manzana Catastral</option>
                    <option value="Notificacion">Notificación</option>
                    <option value="Plano_Predial">Plano Predial</option>
                    <option value="Resolución">Resolución</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>
        </div>

        <div class=" col-12 col-lg-6 p-1 px-2 my-2">
            <label for="nombre_doc1" class="form-label fw-bold">Documentos a cargar</label>
                <div class="input-group mb-1 shadow-sm">
            <label class="input-group-text" for="nombre_doc1" style="font-size:0.9em;"><i class="bi bi-file-earmark-pdf-fill"></i></label>
            <input type="file" class="form-control" style="font-size:0.8em;" id="nombre_doc1" name="nombre_doc${contadorDocs}">
                </div>
            <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
        </div>
    `;

        container.appendChild(nuevoGrupo);
    });


    // FUNCION PARA MOSTRAR QUE ME RADICO EL USUARIO ANTERIOR
    function verHistorialAsignacion(rol, codTramite) {
        const cont = document.getElementById('historialContenido');

        

        // Ahora: usa el código completo tal cual llega
        const cod = String(codTramite || '');

        cont.innerHTML = `
    <div class="text-center py-3">
      <div class="spinner-border" role="status" aria-label="cargando"></div>
      <div class="mt-2">Cargando historial...</div>
    </div>
  `;

        fetch('vistas/tramites/ajax/ver_historial_rol.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `rol=${encodeURIComponent(rol)}&cod_tramite=${encodeURIComponent(cod)}`
            })
            .then(r => r.ok ? r.text() : Promise.reject(new Error('HTTP ' + r.status)))
            .then(html => {
                const limpio = (html || '').trim();
                cont.innerHTML = limpio || '<div class="text-center text-muted py-3">Sin registros para este trámite.</div>';
            })
            .catch(err => {
                console.error('Error en historial:', err);
                cont.innerHTML = '<div class="text-danger py-3 text-center">Error al cargar el historial.</div>';
            });
    }
</script>

<!----------------------------------------------scripts procede y asigna--------------------------------------------------- -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnProcede = document.getElementById('btnProcedeAsigna');
        const iframePreview = document.getElementById('previewDocumento');

        if (!btnProcede) return;

        btnProcede.addEventListener('click', async function() {
            const cod = document.getElementById('cod_tramite').value;
            const mutacion = document.getElementById('mutacion_tramite')?.value || '';
            const tipoProceso = document.getElementById('tipo_proceso_muta')?.value || '';
            const fechaRespuesta = document.getElementById('fecha_respuesta_tramite')?.value || '';
            const actividad = document.getElementById('actividad_tramite')?.value || '';

            const url = new URL('vistas/tramites/acciones/cargar_datos_tramite.php', window.location.href);
            url.searchParams.append('cod_tramite', cod);
            url.searchParams.append('mutacion_tramite', mutacion);
            url.searchParams.append('tipo_proceso_muta', tipoProceso);
            url.searchParams.append('fecha_respuesta_tramite', fechaRespuesta);

            try {
                const res = await fetch(url);
                const data = await res.json();
                if (!data.success) {
                    alert('No se encontraron datos del trámite.');
                    return;
                }

                const t = data.tramite;

                // Llenar campos visibles
                document.getElementById('cod_tramite_modal').value = t.cod_tramite;
                document.getElementById('fecha_rad_modal').value = t.fecha_radicacion;
                document.getElementById('tramite_modal').value = t.mutacion_tramite;
                document.getElementById('nombre_interesado_modal').value = t.nombre_interesado;
                document.getElementById('telefono_modal').value = t.telefono_interesado;
                document.getElementById('correo_modal').value = t.correo_interesado;
                document.getElementById('npn_modal').value = t.npn_predio_tram;
                document.getElementById('fmi_modal').value = t.fmi_predio || '';
                document.getElementById('direccion_modal').value = t.direccion_predio;
                document.getElementById('propietarios_modal').value = t.propietarios;

                // Llenar ocultos correctos
                document.getElementById('cod_tramite_hidden').value = t.cod_tramite;
                document.getElementById('mutacion_tramite_modal').value = mutacion;
                document.getElementById('tipo_proceso_muta_modal').value = tipoProceso;
                document.getElementById('fecha_respuesta_tramite_modal').value = fechaRespuesta;

                // 🔴 Mapeo CLAVE:
                // actividad_tramite = tipo_proceso_muta (lo que pides)
                document.getElementById('actividad_tramite_modal').value = tipoProceso || '';
                // actividad_a_realizar = textarea “Actividad a Realizar”
                document.getElementById('actividad_a_realizar_modal').value = actividad || '';

                // Plantilla y vista previa
                const contenidoPlano = t.plantilla
                    .replace(/<br\s*\/?>/gi, '\n')
                    .replace(/<\/?[^>]+(>|$)/g, '')
                    .trim();

                const textarea = document.getElementById('contenido_html');
                textarea.value = contenidoPlano;
                textarea.dataset.htmlOriginal = t.plantilla;

                // Usar el mismo CSS y cabecera que el generador de PDF (`guardar_procede.php`) para la vista previa
                const PROCE_CSS = `
                    <style>
                    @page { 
                        size: A4 portrait;
                        margin: 1.0cm 1.5cm 1.5cm 1.5cm;
                    }
                    body {
                        font-family: DejaVu Sans, Arial, sans-serif;
                        font-size: 11pt;
                        line-height: 1.25;
                        margin: 0;
                        padding: 0;
                        color: #000;
                    }
                    table { border-collapse: collapse; width: 100%; }
                    td { padding: 4px; vertical-align: middle; }
                    img { max-width: 55px; height: auto; display: block; margin: 0 auto; }
                    .contenido { text-align: justify; font-size: 11pt; margin-top: 8px; padding: 12px; page-break-inside: avoid; }
                    b, strong { font-weight: bold; }
                    br { display: block; content: ""; margin: 0.3em 0; }
                    .firma-wrap { text-align: center; margin-top: 90px; margin-bottom: 2px; }
                    .firma-line { display: inline-block; width: 55%; border-bottom: 1px solid #000; height: 1px; vertical-align: middle; }
                    .firma-nombre { text-align: center; margin-top: 2px; font-weight: bold; }
                    .contenido p { margin: 0 0 8px 0; }
                    .firma-wrap, .firma-nombre { page-break-inside: avoid; }
                    </style>
                `;

                const PROCE_HEADER = `
                    <table style='border: 1px solid #000; width: 100%; font-family: Arial, sans-serif;' cellpadding='4' cellspacing='0'>
                        <tr>
                            <td style='width: 12%; text-align: center; vertical-align: middle;'>
                                <img src='../../../../arbimaps/Arbimaps/assets/img/logo_final_arbitrium.png' alt='Logo Alcaldía' style='width: 55px; height: auto;'>
                            </td>
                            <td style='width: 23%; text-align: left; font-size: 9px; line-height: 1.2; vertical-align: middle;'>
                                <strong>ARBITRIUM S.A.S.</strong><br>
                                Gestión Catastral<br>
                                NIT:900.749.675-1
                            </td>
                            <td style='width: 25%; text-align: center; vertical-align: middle;'>
                                <span style='font-weight: bold; font-size: 11px;'>Respuestas</span>
                            </td>
                            <td style='width: 23%; text-align: right; font-size: 9px; line-height: 1.3; vertical-align: middle;'>
                                <strong>CÓDIGO:</strong><br>
                                <strong>VERSIÓN: 01</strong><br>
                                <strong>VIGENCIA: 2025</strong>
                            </td>
                            <td style='width: 12%; text-align: center; vertical-align: middle;'>
                                <img src='../../../../arbimaps/Arbimaps/assets/img/logo_final_arbitrium.png' alt='Logo Alcaldía' style='width: 55px; height: auto;'>
                            </td>
                        </tr>
                    </table>
                `;

                const generarVistaPrevia = (contenidoHTML) => `<!doctype html>
                    <html>
                    <head>
                        <meta charset="UTF-8">
                        ${PROCE_CSS}
                    </head>
                    <body>
                        ${PROCE_HEADER}
                        <div class="contenido">
                            ${contenidoHTML}
                        </div>
                    </body>
                    </html>`;

                iframePreview.srcdoc = generarVistaPrevia(t.plantilla);

                const modal = new bootstrap.Modal(document.getElementById('modalProcedeAsigna'));
                modal.show();

                textarea.addEventListener('input', () => {
                    iframePreview.srcdoc = generarVistaPrevia(
                        textarea.value.replace(/(\r\n|\r|\n)/g, '<br>')
                    );
                });

            } catch (e) {
                console.error(e);
                alert('Error cargando los datos del trámite.');
            }
        });
    });
</script>




<!-- SCRIPT para guardar variables -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rolUsuario = document.getElementById('rol_usuario')?.value || '';
        if (rolUsuario !== 'procedencia_juridica') return;

        const btnAsignar = document.querySelector('button[type="submit"].btn-success.btn-block');
        const btnGuardarProcede = document.getElementById('btnGuardarProcede');

        if (btnAsignar) {
            btnAsignar.disabled = true;
            btnAsignar.setAttribute('title', 'Debe completar y guardar el documento en "Procede y Asigna" antes de asignar el trámite.');
        }

        if (!btnGuardarProcede) return;

        btnGuardarProcede.addEventListener('click', async function(event) {
            event.preventDefault();

            btnGuardarProcede.disabled = true;
            btnGuardarProcede.textContent = 'Guardando...';

            const form = document.getElementById('formProcedeAsigna');
            const formData = new FormData(form);

            // Asegurar mapeos correctos ANTES de enviar
            formData.set('actividad_tramite', document.getElementById('tipo_proceso_muta_modal').value || '');
            formData.set('actividad_a_realizar', document.getElementById('actividad_a_realizar_modal').value || '');
            formData.append('nombre_interesado', document.getElementById('nombre_interesado_modal').value);
            formData.append('telefono_interesado', document.getElementById('telefono_modal').value);
            formData.append('correo_interesado', document.getElementById('correo_modal').value);
            formData.append('cod_catastro', document.getElementById('npn_modal').value);
            formData.append('fmi_predio', document.getElementById('fmi_modal').value);
            formData.append('direccion_predio', document.getElementById('direccion_modal').value);
            formData.append('propietarios_predio', document.getElementById('propietarios_modal').value);

            // Contenido del documento (respetando saltos como <br>)
            formData.append(
                'cont_documento',
                document.getElementById('contenido_html').value.replace(/\n/g, '<br>')
            );

            formData.append(
                'tipo_oficio',
                form.querySelector('select[name="tipo_documento"]').value
            );

            try {
                const res = await fetch('vistas/tramites/acciones/guardar_procede.php', {
                    method: 'POST',
                    body: formData
                });

                const raw = await res.text();
                let data;
                try {
                    data = JSON.parse(raw);
                } catch (e) {
                    throw new Error('La respuesta del servidor no es un JSON válido: ' + raw);
                }

                if (data.success) {
                    alert('Documento guardado correctamente.');

                    if (btnAsignar) {
                        btnAsignar.disabled = false;
                        btnAsignar.removeAttribute('title');
                        btnAsignar.classList.add('btn-outline-white');
                        btnAsignar.textContent = 'Asignar Trámite (Listo para enviar)';
                    }

                    const modalEl = document.getElementById('modalProcedeAsigna');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();

                    console.log('PDF generado en:', data.ruta_pdf);
                } else {
                    alert('Error: ' + (data.error || 'Error desconocido.'));
                }

            } catch (err) {
                console.error(err);
                alert('Error al enviar la solicitud: ' + err.message);
            } finally {
                btnGuardarProcede.disabled = false;
                btnGuardarProcede.textContent = 'Guardar y Enviar';
            }
        });
    });
</script>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mapRolIndex = <?php echo json_encode($mapRolIndex); ?>;

    function contarItemsEnHTML(html) {
        if (!html) return 0;
        const doc = new DOMParser().parseFromString(html, 'text/html');

        let n = doc.querySelectorAll('table tbody tr').length;
        if (n) return n;

        const totalTr = doc.querySelectorAll('table tr').length;
        if (totalTr) {
            const theadTr = doc.querySelectorAll('table thead tr').length;
            const tfootTr = doc.querySelectorAll('table tfoot tr').length;
            let headerLike = 0;
            const firstTr = doc.querySelector('table tr');
            if (firstTr && firstTr.querySelectorAll('th').length > 0) headerLike = 1;
            const bodyTr = Math.max(0, totalTr - theadTr - tfootTr - headerLike);
            if (bodyTr) return bodyTr;
        }

        n = doc.querySelectorAll('ul > li, ol > li').length;
        if (n) return n;

        n = doc.querySelectorAll('.historial-item, .item, .registro, .fila, .documento-item').length;
        if (n) return n;

        n = doc.querySelectorAll('a[href$=".pdf"], a[href*="/tramites_conservacion/"]').length;
        if (n) return n;

        return 0;
    }

    function getCodBase() {
  return document.getElementById('cod_tramite')?.value || '';
}

    async function fetchHtmlRol(rol) {
        const codBase = getCodBase();
        try {
            const body = new URLSearchParams({ rol: rol, cod_tramite: codBase });
            const res = await fetch('vistas/tramites/ajax/ver_historial_rol.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            });
            if (!res.ok) return '';
            return await res.text();
        } catch (e) {
            console.error('Error fetch historial rol', rol, e);
            return '';
        }
    }

    async function actualizarBadges() {
        const buttons = Array.from(document.querySelectorAll('button[onclick^="verHistorialAsignacion("]'));
        const codFull = document.getElementById('cod_tramite')?.value || '';

        for (const btn of buttons) {
            // Extraer rol desde el onclick
            const m = btn.getAttribute('onclick')?.match(/verHistorialAsignacion\('([^']+)'/);
            if (!m) continue;
            const rol = m[1];


            // buscar badge renderizada por servidor
            let badge = btn.querySelector('.noti-badge');
            if (!badge) {
                // crear una si no existe
                const idx = mapRolIndex[rol] ?? 1;
                badge = document.createElement('span');
                badge.className = `noti-badge noti-rol-${idx}`;
                btn.appendChild(badge);
            }

            // obtener HTML y contar
            const html = await fetchHtmlRol(rol);
            const n = contarItemsEnHTML(html);

            if (n > 0) {
                badge.textContent = n;
                badge.style.display = 'inline-block';
            } else {
                badge.remove();
            }
        }
    }

    // Ejecutar una vez al cargar la página
    actualizarBadges().catch(e => console.error(e));
    window.actualizarBadges = actualizarBadges;
});
</script>
