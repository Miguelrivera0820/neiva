

<?php
// session_start();
date_default_timezone_set('America/Bogota');
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 2) . '/config/permisos.php';
require_once dirname(__DIR__, 3) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('POST');
neiva_require_permission('menu.tramites', $PERMISOS);
neiva_require_csrf('global');


$conn = $mysqli;
if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Error de conexión: " . $conn->connect_error);
}

$redirect_consultar_tramite = neiva_app_url('Arbimaps/index.php?page=tramites/consultar_tramite');
$redirect_consultar_tramite_js = json_encode($redirect_consultar_tramite, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // DATOS DEL USUARIO ASIGNADO (receptor del trámite)
    $asignacion_cod_tramite = $_POST['cod_tramite'] ?? '';
    $asignacion_cc_usuario = $_POST['cedula'] ?? '';
    $asignacion_nombre_usuario = $_POST['nombre_usuario'] ?? '';
    $asignacion_apellido_usuario = $_POST['apellido_usuario'] ?? '';
    $asignacion_rol_usuario = $_POST['rol_asignado'] ?? '';
    $observacion_a_usuario_tramite = $_POST['observacion_asignacion'] ?? '';
    $asignacion_fecha_tramite = date('Y-m-d H:i:s');
    $asignacion_estado_tramite = $_POST['asignacion_estado_tramite'] ?? '';
    $fecha_limite = $_POST['fecha_limite'] ?? '';
    $cod_tramite = $_POST['cod_tramite'];
    $doc_observaciones = $_POST['doc_observaciones'] ?? '';
    $doc_cedula_usuario = $asignacion_cc_usuario; // Asegúrate que esté definido
    $documento_estado = 1;
    $anio = substr($cod_tramite, 4, 4); // Extraer año desde el código (2025)


    // VARIABLE NUEVAS QUE INGRESA
    // O DEFINE EL USUARIO PROCEDENCIA JURIDICA
    $mutacion_tramite = $_POST['mutacion_tramite'] ?? '';
    $tipo_proceso_muta = $_POST['tipo_proceso_muta'] ?? '';
    $actividad_tramite = $_POST['actividad_tramite'] ?? '';
    $fecha_respuesta_tramite = $_POST['fecha_respuesta_tramite'] ?? '';
    // ///////////////////////////////////////////////////////////////////////////

    $tipo_doc1 = !empty($_POST['tipo_doc1']) ? $_POST['tipo_doc1'] : null;
    $tipo_doc2 = !empty($_POST['tipo_doc2']) ? $_POST['tipo_doc2'] : null;
    $tipo_doc3 = !empty($_POST['tipo_doc3']) ? $_POST['tipo_doc3'] : null;
    $tipo_doc4 = !empty($_POST['tipo_doc4']) ? $_POST['tipo_doc4'] : null;
    $tipo_doc5 = !empty($_POST['tipo_doc5']) ? $_POST['tipo_doc5'] : null;

    // DATOS DEL USUARIO QUE CREA LA ASIGNACIÓN (desde la sesión)
    $creacion_tram_cc_usuario = $_SESSION['cedula_usuario'] ?? '';
    $creacion_tram_nombre_usuario = $_SESSION['nombre_usuario'] ?? '';
    $creacion_tram_apellido_usuario = $_SESSION['apellido_usuario'] ?? '';
    $creacion_tram_rol_usuario = $_SESSION['rol_usuario'] ?? '';

    $tipo_tramite_actual = 'ACTUALIZACION';
    $stmt_tipo = $conn->prepare("SELECT tipo_tramite FROM tramite_radicacion WHERE cod_tramite = ? LIMIT 1");
    if ($stmt_tipo) {
        $stmt_tipo->bind_param("s", $cod_tramite);
        $stmt_tipo->execute();
        $res_tipo = $stmt_tipo->get_result();
        if ($row_tipo = $res_tipo->fetch_assoc()) {
            $tipo_tramite_actual = strtoupper(trim($row_tipo['tipo_tramite'] ?? 'ACTUALIZACION'));
        }
        $stmt_tipo->close();
    }
    if (!in_array($tipo_tramite_actual, ['ACTUALIZACION', 'CONSERVACION'], true)) {
        $tipo_tramite_actual = 'ACTUALIZACION';
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

    if ($tipo_tramite_actual === 'CONSERVACION' && $creacion_tram_rol_usuario === 'procedencia_juridica') {
        $sqlEtapaFinal = "SELECT 1
                          FROM asignacion_tramite
                          WHERE asignacion_cod_tramite = ?
                            AND (asignacion_rol_usuario = 'coordinacion_tecnica'
                                 OR creacion_tram_rol_usuario = 'coordinacion_tecnica')
                          LIMIT 1";
        $stmtEtapaFinal = $conn->prepare($sqlEtapaFinal);
        $es_etapa_final_conservacion = false;
        if ($stmtEtapaFinal) {
            $stmtEtapaFinal->bind_param("s", $asignacion_cod_tramite);
            $stmtEtapaFinal->execute();
            $es_etapa_final_conservacion = (bool)$stmtEtapaFinal->get_result()->fetch_assoc();
            $stmtEtapaFinal->close();
        }

        if ($es_etapa_final_conservacion) {
            $roles_por_rol_conservacion["procedencia_juridica"] = ["ventanilla_catastral"];
        }
    }

    $mapa_roles_actual = $tipo_tramite_actual === 'CONSERVACION'
        ? $roles_por_rol_conservacion
        : $roles_por_rol;
    $roles_permitidos = $mapa_roles_actual[$creacion_tram_rol_usuario] ?? [];
    if (!in_array($asignacion_rol_usuario, $roles_permitidos, true)) {
        die("El rol seleccionado no estÃ¡ permitido para el flujo {$tipo_tramite_actual}.");
    }


    // Consulta SQL

    // SOLUCIONAR ERROR POR ROL
    $sql = "INSERT INTO asignacion_tramite (
        asignacion_cod_tramite,
        asignacion_cc_usuario,
        asignacion_nombre_usuario,
        asignacion_apellido_usuario,
        asignacion_rol_usuario,
        observacion_a_usuario_tramite,
        asig_mutacion_tramite,
        tipo_proceso_muta,
        actividad_tramite,
        fecha_respuesta_tramite,
        asignacion_fecha_tramite,
        creacion_tram_cc_usuario,
        creacion_tram_nombre_usuario,
        creacion_tram_apellido_usuario,
        creacion_tram_rol_usuario,
        asignacion_estado_tramite,
        fecha_limite
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error al preparar la consulta: " . $conn->error);
    }

    $stmt->bind_param(
        "sssssssssssssssss",
        $asignacion_cod_tramite,
        $asignacion_cc_usuario,
        $asignacion_nombre_usuario,
        $asignacion_apellido_usuario,
        $asignacion_rol_usuario,
        $observacion_a_usuario_tramite,
        $mutacion_tramite,
        $tipo_proceso_muta,
        $actividad_tramite,
        $fecha_respuesta_tramite,
        $asignacion_fecha_tramite,
        $creacion_tram_cc_usuario,
        $creacion_tram_nombre_usuario,
        $creacion_tram_apellido_usuario,
        $creacion_tram_rol_usuario,
        $asignacion_estado_tramite,
        $fecha_limite
    );


    // Ruta base del expediente: se guarda relativa en BD y fisica fuera de Arbimaps.
    $ruta_base_relativa = "tramites_conservacion/$anio/$cod_tramite/$creacion_tram_rol_usuario";
    $ruta_base = neiva_join_paths(neiva_app_root(), $ruta_base_relativa);

    // Crear la carpeta si no existe
    neiva_ensure_directory($ruta_base);

    // Guardar archivos o null si no se subió
    $documentos = [];

    for ($i = 1; $i <= 5; $i++) {
        $campo = "nombre_doc{$i}";

        if (isset($_FILES[$campo]) && $_FILES[$campo]['error'] === UPLOAD_ERR_OK) {
            $nombre_archivo = trim(basename($_FILES[$campo]['name']));
            $nombre_archivo = preg_replace('/\s+/', '_', $nombre_archivo);
            $nombre_archivo = preg_replace('/[^A-Za-z0-9_.-]/', '_', $nombre_archivo);
            $nombre_archivo = preg_replace('/_+/', '_', $nombre_archivo);
            if ($nombre_archivo === '' || $nombre_archivo === '_') {
                $nombre_archivo = "documento_{$i}.pdf";
            }
            $ruta_destino = $ruta_base . '/' . $nombre_archivo;

            // Mover archivo a la carpeta correspondiente
            if (move_uploaded_file($_FILES[$campo]['tmp_name'], $ruta_destino)) {
                $documentos[$campo] = $ruta_base_relativa . '/' . $nombre_archivo;
            } else {
                $documentos[$campo] = null; // Error al mover
            }
        } else {
            $documentos[$campo] = null; // No se subió archivo
        }
    }

    if ($stmt->execute()) {
        // ==========================
        // 1. ACTUALIZAR RADICACION SOLO SI ROL = procedencia_juridica
        // ==========================


        $asignacion_id_insertado = $conn->insert_id; //Inicia insercción para la tabla estados
        $es_nombre = 'ASIGNADO';
        $es_tipo = 'automatico';
        $es_descripcion = 'Trámite asignado automáticamente según rol';
        $es_dias_disparador = 5;
        $es_rol_asociado = $asignacion_rol_usuario;
        $estado = 'ACTIVO';

        $sql_estado = "INSERT INTO estados_tramite (
                es_nombre,
                es_tipo,
                es_descripcion,
                es_dias_disparador,
                es_rol_asociado,
                estado,
                asignacion_id,
                cod_tramite
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt_estado = $conn->prepare($sql_estado);
        if ($stmt_estado === false) {
            die("Error al preparar la inserción en estados_tramite: " . $conn->error);
        }

        $stmt_estado->bind_param(
            "sssissss",
            $es_nombre,
            $es_tipo,
            $es_descripcion,
            $es_dias_disparador,
            $es_rol_asociado,
            $estado,
            $asignacion_id_insertado,
            $asignacion_cod_tramite
        );

        $stmt_estado->execute();   // Final de insercción estados

        $sql_doc = "INSERT INTO documentos_tram_asignacion (
            cod_tramite,
            doc_cedula_usuario,
            nombre_doc1,
            nombre_doc2,
            nombre_doc3,
            nombre_doc4,
            nombre_doc5,
            tipo_doc1,
            tipo_doc2,
            tipo_doc3,
            tipo_doc4,
            tipo_doc5,
            doc_observaciones,
            documento_estado
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt_doc = $conn->prepare($sql_doc);
        if (!$stmt_doc) die("Error al preparar inserción de documentos: " . $conn->error);

        $doc_observaciones = $observacion_a_usuario_tramite;
        $documento_estado = 1;

        $stmt_doc->bind_param(
            "sisssssssssssi",
            $cod_tramite,
            $doc_cedula_usuario,
            $documentos['nombre_doc1'],
            $documentos['nombre_doc2'],
            $documentos['nombre_doc3'],
            $documentos['nombre_doc4'],
            $documentos['nombre_doc5'],
            $tipo_doc1,
            $tipo_doc2,
            $tipo_doc3,
            $tipo_doc4,
            $tipo_doc5,
            $doc_observaciones,
            $documento_estado
        );

        if (!$stmt_doc->execute()) {
            die("Error al ejecutar la inserción de documentos: " . $stmt_doc->error); // Fin insercción documentos

        }

        // 1. Primero verifica si ya existe el cod_tramite en historial
        $consultaExistencia = "SELECT COUNT(*) FROM historial_asignacion WHERE historial_cod_tramite = ?";
        $stmtExistencia = $conn->prepare($consultaExistencia);
        $stmtExistencia->bind_param("s", $cod_tramite);
        $stmtExistencia->execute();
        $stmtExistencia->bind_result($existe);
        $stmtExistencia->fetch();
        $stmtExistencia->close();

        // Solo insertar si no existe
        if ($existe == 0) {
            // Valores por defecto para los estados
            $est_ventanilla             = 'ASIGNADO';
            $est_procedencia            = 'PENDIENTE';
            $est_atencion_procedencia   = 'PENDIENTE';
            $est_conservacion           = 'PENDIENTE';
            $est_lider_juridico         = 'PENDIENTE';
            $est_control_calidad        = 'PENDIENTE';
            $est_lider_economico        = 'PENDIENTE';
            $est_consolidacion          = 'PENDIENTE';
            $est_edicion                = 'PENDIENTE';
            $est_avaluos                = 'PENDIENTE';
            $est_reconocimiento         = 'PENDIENTE';
            $est_director               = 'PENDIENTE';

            $sql_historial = "INSERT INTO historial_asignacion (
                    historial_cod_tramite, 
                    historial_id_tramite,
                    historial_cc_usuario,
                    historial_nombre_usuario,
                    historial_apellido_usuario,
                    historial_rol_usuario,
                    observacion_a_usuario_tramite,
                    historial_fecha_tramite,
                    creacion_tram_cc_usuario,
                    creacion_tram_nombre_usuario,
                    creacion_tram_apellido_usuario,
                    creacion_tram_rol_usuario,
                    fecha_limite,
                    historial_estado_tramite,
                    est_ventanilla,
                    est_procedencia,
                    est_atencion_procedencia,
                    est_conservacion,
                    est_lider_juridico,
                    est_control_calidad,
                    est_lider_economico,
                    est_consolidacion,
                    est_edicion,
                    est_avaluos,
                    est_reconocimiento,
                    est_director
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";


            $stmt_historial = $conn->prepare($sql_historial);

            if (!$stmt_historial) {
                die("Error al preparar inserción en historial_asignacion: " . $conn->error);
            }

            // Asignar correctamente los datos
            $stmt_historial->bind_param(
                "siisssssisssssssssssssssss",
                $cod_tramite,
                $asignacion_id_insertado,
                $asignacion_cc_usuario,
                $asignacion_nombre_usuario,
                $asignacion_apellido_usuario,
                $asignacion_rol_usuario,
                $observacion_a_usuario_tramite,
                $asignacion_fecha_tramite,
                $creacion_tram_cc_usuario,
                $creacion_tram_nombre_usuario,
                $creacion_tram_apellido_usuario,
                $creacion_tram_rol_usuario,
                $fecha_limite,
                $asignacion_estado_tramite,
                $est_ventanilla,
                $est_procedencia,
                $est_atencion_procedencia,
                $est_conservacion,
                $est_lider_juridico,
                $est_control_calidad,
                $est_lider_economico,
                $est_consolidacion,
                $est_edicion,
                $est_avaluos,
                $est_reconocimiento,
                $est_director
            );

            if (!$stmt_historial->execute()) {
                die("Error al ejecutar inserción en historial_asignacion: " . $stmt_historial->error);
            }

            $stmt_historial->close();
        }

        $rol_actual = $_SESSION['rol_usuario'];
        $campo_estado = ''; // Campo a actualizar
        $campo_fecha = ''; // Campo de fecha a actualizar

        // Determinar el campo de estado según el rol actual
        switch ($rol_actual) {
            case 'ventanilla_catastral':
                $campo_estado = 'est_ventanilla';
                $campo_fecha = 'fecha_ventanilla';
                break;
            case 'procedencia_juridica':
                $campo_estado = 'est_procedencia';
                $campo_fecha = 'fecha_procedencia';
                break;
            case 'atencion_procedencia':
                $campo_estado = 'est_atencion_procedencia';
                $campo_fecha = 'fecha_ate_procedencia';
                break;
            case 'coordinacion_tecnica':
                $campo_estado = 'est_conservacion';
                $campo_fecha = 'fecha_conservacion';
                break;
            case 'revision_juridica':
                $campo_estado = 'est_lider_juridico';
                $campo_fecha = 'fecha_lid_juridico';
                break;
            case 'control_calidad':
                $campo_estado = 'est_control_calidad';
                $campo_fecha = 'fecha_cont_calidad';
                break;
            case 'componente_economico':
                $campo_estado = 'est_lider_economico';
                $campo_fecha = 'fecha_lid_economico';
                break;
            case 'consolidacion':
                $campo_estado = 'est_consolidacion';
                $campo_fecha = 'fecha_consolidacion';
                break;
            case 'avaluos':
                $campo_estado = 'est_avaluos';
                $campo_fecha = 'fecha_avaluos';
                break;
            case 'editor':
                $campo_estado = 'est_edicion';
                $campo_fecha = 'fecha_edicion';
                break;
            case 'reconocedor':
                $campo_estado = 'est_reconocimiento';
                $campo_fecha = 'fecha_reconocimiento';
                break;
            case 'director_catastro':
                $campo_estado = 'est_director';
                $campo_fecha = 'fecha_director';
                break;
            default:
                // Manejo si el rol no está en la lista
                break;
        }

        $rol_que_recibe   = $asignacion_rol_usuario;
        $nombre_usuario   = $asignacion_nombre_usuario;
        $apellido_usuario = $asignacion_apellido_usuario;
        $fecha_limite_rol = $fecha_limite;

        // Solo si el rol está mapeado
        if ($campo_estado && $campo_fecha) {
            $fecha_aprobacion = date('Y-m-d H:i:s');

            // Prepara la consulta sin coma antes del WHERE
            $update = $conn->prepare("UPDATE historial_asignacion 
            SET $campo_estado = 'APROBADO',
                $campo_fecha = ?,
                creacion_tram_rol_usuario = ?,
                creacion_tram_cc_usuario = ?,
                creacion_tram_nombre_usuario = ?,
                creacion_tram_apellido_usuario = ?,
                historial_rol_usuario = ?,
                historial_nombre_usuario = ?,
                historial_apellido_usuario = ?,
                fecha_limite = ?
            WHERE historial_cod_tramite = ?");

            // Pasa todas las variables necesarias
            $update->bind_param(
                "ssisssssss",
                $fecha_aprobacion,
                $rol_actual,
                $creacion_tram_cc_usuario,
                $creacion_tram_nombre_usuario,
                $creacion_tram_apellido_usuario,
                $rol_que_recibe,
                $nombre_usuario,
                $apellido_usuario,
                $fecha_limite,
                $cod_tramite

            );

            $update->execute();
        }

        if ($_SESSION['rol_usuario'] === 'procedencia_juridica') {
            $sql_update_rad = "UPDATE tramite_radicacion 
                            SET mutacion_tramite = ?, 
                                fecha_limite_respuesta = ?
                            WHERE cod_tramite = ?";

            $stmt_update_rad = $conn->prepare($sql_update_rad);
            if ($stmt_update_rad === false) {
                die("Error al preparar el UPDATE en tramite_radicacion: " . $conn->error);
            }

            $stmt_update_rad->bind_param("sss", $mutacion_tramite, $fecha_respuesta_tramite, $cod_tramite);

            if (!$stmt_update_rad->execute()) {
                die("Error al ejecutar el UPDATE en tramite_radicacion: " . $stmt_update_rad->error);
            }

            $stmt_update_rad->close();
        }

        echo <<<HTML
<!-- <!DOCTYPE html>
<html lang="es"> -->
<!-- <head>
    <meta charset="UTF-8">
    <title>Procesando Asignación</title> -->

    <!-- Bootstrap, Animate.css, SweetAlert2 -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .custom-loader {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: conic-gradient(#022F55 0% 25%, #0F5699 25% 50%, #4DA6FF 50% 75%, #66CC99 75% 100%);
            animation: spin 2s linear infinite;
            margin: 0 auto;
            position: relative;
        }
        .custom-loader::before {
            content: '';
            position: absolute;
            top: 10px; left: 10px; right: 10px; bottom: 10px;
            background: white;
            border-radius: 50%;
            box-shadow: 0 0 20px rgba(2, 47, 85, 0.1);
        }
        .custom-loader::after {
            content: '';
            position: absolute;
            top: 20px; left: 20px; right: 20px; bottom: 20px;
            background: linear-gradient(135deg, #022F55, #0F5699);
            border-radius: 50%;
        }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .progress-bar { animation: progress-animation 3s ease-in-out; }
        @keyframes progress-animation { 0%{width:0%}50%{width:70%}100%{width:100%} }
        .card { backdrop-filter: blur(10px); background: rgba(255,255,255,0.95); }
        .loader-container { position: relative; padding: 20px; }
    </style>
<!-- </head> -->
<!-- <body class="d-flex align-items-center justify-content-center" style="min-height: 100vh; background-color:#f8f9fa;"> -->

    <div class="container-fluid d-flex align-items-center justify-content-center" style="min-height: 70vh;">
        <div class="card shadow-lg border-0" style="max-width: 500px; width: 100%;">
            <div class="card-header text-center text-white" style="background: linear-gradient(135deg, #022F55 0%, #0F5699 100%); border-radius: 0.5rem 0.5rem 0 0;">
                <h4 class="mb-0 fw-bold">
                    <i class="bi bi-gear-fill me-2"></i>
                    Procesando Asignación
                </h4>
            </div>
            <div class="card-body text-center py-5">
                <div class="loader-container mb-4">
                    <div class="custom-loader"></div>
                </div>
                <h5 class="text-dark mb-3">Asignando trámite...</h5>
                <p class="text-muted mb-4">Por favor espere mientras se procesa la asignación del trámite.</p>

                <div class="progress mb-3" style="height: 8px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                        style="background: linear-gradient(90deg, #022F55 0%, #0F5699 50%, #4DA6FF 100%);"
                        role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>

                <div class="d-flex justify-content-center align-items-center">
                    <div class="spinner-border text-primary me-2" role="status" style="width: 1.2rem; height: 1.2rem;">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <small class="text-muted">Finalizando proceso...</small>
                </div>
            </div>
        </div>
    </div>

    <script>
        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: '¡Trámite Asignado!',
                text: 'El trámite ha sido asignado correctamente.',
                confirmButtonColor: '#022F55',
                confirmButtonText: 'Continuar',
                background: '#ffffff',
                customClass: {
                    title: 'text-dark fw-bold',
                    confirmButton: 'btn-lg px-4'
                },
                showClass: { popup: 'animate__animated animate__fadeInDown' },
                hideClass: { popup: 'animate__animated animate__fadeOutUp' }
            }).then(() => {
                window.location.href = {$redirect_consultar_tramite_js};
            });
        }, 3000);
    </script>
<!-- </body> -->
<!-- </html> -->
HTML;

        exit;
    }
} else {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({
                title: 'Error',
                text: 'Ocurrió un error al asignar el trámite.',
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        </script>";
}
$conn->close();
