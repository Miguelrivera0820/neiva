<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('POST');
neiva_require_permission('menu.seguimiento', $PERMISOS);
neiva_require_csrf('global');
?>
<style>
    .custom-loader {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: conic-gradient(#022F55 0% 25%,
                #0F5699 25% 50%,
                #4DA6FF 50% 75%,
                #66CC99 75% 100%);
        animation: spin 2s linear infinite;
        margin: 0 auto;
        position: relative;
    }

    .custom-loader::before {
        content: '';
        position: absolute;
        top: 10px;
        left: 10px;
        right: 10px;
        bottom: 10px;
        background: white;
        border-radius: 50%;
        box-shadow: 0 0 20px rgba(2, 47, 85, 0.1);
    }

    .custom-loader::after {
        content: '';
        position: absolute;
        top: 20px;
        left: 20px;
        right: 20px;
        bottom: 20px;
        background: linear-gradient(135deg, #022F55, #0F5699);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .progress-bar {
        animation: progress-animation 3s ease-in-out;
    }

    @keyframes progress-animation {
        0% {
            width: 0%;
        }

        50% {
            width: 70%;
        }

        100% {
            width: 100%;
        }
    }

    .card {
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.95);
    }

    .loader-container {
        position: relative;
        padding: 20px;
    }
</style>

<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
date_default_timezone_set('America/Bogota');
$conn = $mysqli;
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$cod_tramite = $_GET['asignacion_cod_tramite'] ?? '';

$rol_actual = $_SESSION['rol_usuario'] ?? '';
if (!$rol_actual) {
    die("No se ha definido el rol del usuario en la sesión.");
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $entrega_cod_tramite            = $_POST['entrega_cod_tramite'] ?? '';
        $historial_fecha_tramite        = $_POST['historial_fecha_tramite'] ?? '';
        $observacion_a_usuario_tramite  = $_POST['observacion_asignacion'] ?? '';
        $fecha_limite                   = $_POST['fecha_limite'] ?? null;
        $asignacion_rol_usuario         = $_POST['asignacion_rol_usuario'] ?? '';
        $creacion_tram_cc_usuario       = $_POST['creacion_tram_cc_usuario'] ?? '';
        $creacion_tram_nombre_usuario   = $_POST['creacion_tram_nombre_usuario'] ?? '';
        $creacion_tram_apellido_usuario = $_POST['creacion_tram_apellido_usuario'] ?? '';
        $creacion_tram_rol_usuario      = $_POST['creacion_tram_rol_usuario'] ?? '';
        $entrega_cc_usuario             = $_POST['entrega_cc_usuario'] ?? '';
        $entrega_nombre_usuario         = $_POST['entrega_nombre_usuario'] ?? '';
        $entrega_apellido_usuario       = $_POST['entrega_apellido_usuario'] ?? '';
        $entrega_rol_usuario            = $_POST['entrega_rol_usuario'] ?? '';

        $creacion_tram_cc_usuario       = $_SESSION['cedula_usuario'] ?? $creacion_tram_cc_usuario;
        $creacion_tram_nombre_usuario   = $_SESSION['nombre_usuario'] ?? $creacion_tram_nombre_usuario;
        $creacion_tram_apellido_usuario = $_SESSION['apellido_usuario'] ?? $creacion_tram_apellido_usuario;
        $creacion_tram_rol_usuario      = ($_POST['rol_actual'] ?? '') ?: ($_SESSION['rol_usuario'] ?? $creacion_tram_rol_usuario);

        $quien_entrego_cc               = $creacion_tram_cc_usuario;
        $quien_entrego_nombre           = $creacion_tram_nombre_usuario;
        $quien_entrego_apellido         = $creacion_tram_apellido_usuario;
        $quien_entrego_rol              = $creacion_tram_rol_usuario;

        $asignacion_rol_usuario         = $entrega_rol_usuario ?: $asignacion_rol_usuario;

        $historial_cod_tramite = $entrega_cod_tramite;

        $entrega_cod_tramite    = $_POST['entrega_cod_tramite'] ?? null;
        $accion                 = $_POST['accion'] ?? null;

        if (!$entrega_cod_tramite || !$asignacion_rol_usuario) {
            die("Faltan datos obligatorios (código trámite o rol actual).");
        }
        $anio = substr($entrega_cod_tramite, 4, 4);

        $tipo_doc = [];
        for ($i = 1; $i <= 5; $i++) {
            $tipo_doc[$i] = $_POST["tipo_doc{$i}"] ?? null;
        }

        $rol_logueado = $_SESSION['rol_usuario'] ?? 'sin_rol';
        $ruta_base = "../tramites_conservacion/$anio/$entrega_cod_tramite/tramites_revision/$rol_logueado";
        if (!file_exists($ruta_base)) {
            mkdir($ruta_base, 0777, true);
        }

        $documentos = [];
        for ($i = 1; $i <= 5; $i++) {
            $campo = "nombre_doc{$i}";
            if (isset($_FILES[$campo]) && $_FILES[$campo]['error'] === UPLOAD_ERR_OK) {
                $nombre_archivo = basename($_FILES[$campo]['name']);
                $ruta_destino = $ruta_base . '/' . $nombre_archivo;
                if (move_uploaded_file($_FILES[$campo]['tmp_name'], $ruta_destino)) {
                    $documentos["doc{$i}"] = $ruta_destino;
                } else {
                    $documentos["doc{$i}"] = null;
                }
            } else {
                $documentos["doc{$i}"] = null;
            }
        }

        $stmt_asig = $conn->prepare(
            "SELECT asignacion_id_tramite 
                                        FROM asignacion_tramite 
                                        WHERE asignacion_cod_tramite = ?
                                        ORDER BY asignacion_id_tramite DESC 
                                        LIMIT 1"
        );
        $stmt_asig->bind_param("s", $entrega_cod_tramite);
        $stmt_asig->execute();
        $stmt_asig->bind_result($id_tramite_fk);
        $stmt_asig->fetch();
        $stmt_asig->close();
        if (!$id_tramite_fk) {
            die("No se encontró el trámite padre para el código: $entrega_cod_tramite");
        }
        $sql = "INSERT INTO entrega_asignacion (
                                    entrega_id_tramite,
                                    entrega_cod_tramite,
                                    historial_fecha_tramite,
                                    entrega_nombre_usuario,
                                    entrega_apellido_usuario,
                                    entrega_cc_usuario,
                                    entrega_rol_usuario,
                                    observacion_a_usuario_tramite,
                                    creacion_tram_cc_usuario,
                                    creacion_tram_nombre_usuario,
                                    creacion_tram_apellido_usuario,
                                    creacion_tram_rol_usuario,
                                    fecha_limite,
                                    quien_entrego_cc,
                                    quien_entrego_nombre,
                                    quien_entrego_apellido,
                                    quien_entrego_rol
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "issssisssssssisss",
            $id_tramite_fk,
            $entrega_cod_tramite,
            $historial_fecha_tramite,
            $entrega_nombre_usuario,
            $entrega_apellido_usuario,
            $entrega_cc_usuario,
            $entrega_rol_usuario,
            $observacion_a_usuario_tramite,
            $creacion_tram_cc_usuario,
            $creacion_tram_nombre_usuario,
            $creacion_tram_apellido_usuario,
            $creacion_tram_rol_usuario,
            $fecha_limite,
            $quien_entrego_cc,
            $quien_entrego_nombre,
            $quien_entrego_apellido,
            $quien_entrego_rol
        );
        $stmt->execute();
        $entrega_id_tramite = $conn->insert_id;
        $stmt->close();

        $sql_estado = "INSERT INTO 
                                estados_tramite (
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
        if (!$stmt_estado) die("Error al preparar estados_tramite: " . $conn->error);
        $es_nombre          = 'REVISION';
        $es_tipo            = 'automatico';
        $es_descripcion     = 'Trámite ';
        $es_dias_disparador = 5;
        $estado             = 'ACTIVO';
        $stmt_estado->bind_param(
            "sssissis",
            $es_nombre,
            $es_tipo,
            $es_descripcion,
            $es_dias_disparador,
            $asignacion_rol_usuario,
            $estado,
            $id_tramite_fk,
            $historial_cod_tramite
        );
        $stmt_estado->execute();
        $stmt_estado->close();
        $sql_doc = "INSERT INTO doc_entrega_asignacion (
                                        cod_tramite, 
                                        doc_cedula_usuario, 
                                        asignacion_id_tramite,
                                        doc1, 
                                        doc2, 
                                        doc3, 
                                        doc4, 
                                        doc5,
                                        tipo_doc1, 
                                        tipo_doc2, 
                                        tipo_doc3, 
                                        tipo_doc4, 
                                        tipo_doc5,
                                        doc_observaciones, 
                                        documento_estado
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_doc = $conn->prepare($sql_doc);
        $doc_cedula_usuario = $creacion_tram_cc_usuario ?: ($_SESSION['cedula_usuario'] ?? null);
        $doc_observaciones  = $observacion_a_usuario_tramite;
        $documento_estado   = "CARGADO";
        $stmt_doc->bind_param(
            "siissssssssssss",
            $entrega_cod_tramite,
            $doc_cedula_usuario,
            $id_tramite_fk,
            $documentos['doc1'],
            $documentos['doc2'],
            $documentos['doc3'],
            $documentos['doc4'],
            $documentos['doc5'],
            $tipo_doc[1],
            $tipo_doc[2],
            $tipo_doc[3],
            $tipo_doc[4],
            $tipo_doc[5],
            $doc_observaciones,
            $documento_estado
        );
        $stmt_doc->execute();
        $stmt_doc->close();

        $rol_actual = $_SESSION['rol_usuario'];
        $mapa_estados = [
            'ventanilla_catastral' => ['est_ventanilla', 'fecha_ventanilla'],
            'procedencia_juridica' => ['est_procedencia', 'fecha_procedencia'],
            'atencion_procedencia' => ['est_atencion_procedencia', 'fecha_ate_procedencia'],
            'coordinacion_tecnica' => ['est_conservacion', 'fecha_conservacion'],
            'revision_juridica'    => ['est_lider_juridico', 'fecha_lid_juridico'],
            'control_calidad'      => ['est_control_calidad', 'fecha_cont_calidad'],
            'componente_economico' => ['est_lider_economico', 'fecha_lid_economico'],
            'consolidacion'        => ['est_consolidacion', 'fecha_consolidacion'],
            'avaluos'              => ['est_avaluos', 'fecha_avaluos'],
            'editor'               => ['est_edicion', 'fecha_edicion'],
            'reconocedor'          => ['est_reconocimiento', 'fecha_reconocimiento'],
            'director_catastro'    => ['est_director', 'fecha_director']
        ];
        $stmt_check = $conn->prepare("
                    SELECT COUNT(*) 
                    FROM historial_revision 
                    WHERE historial_cod_tramite = ?
                ");
        $stmt_check->bind_param("s", $entrega_cod_tramite);
        $stmt_check->execute();
        $stmt_check->bind_result($existe);
        $stmt_check->fetch();
        $stmt_check->close();
        $id_historial_asignacion = null;
        $stmt_asig = $conn->prepare(
            "SELECT id_historial_asignacion  
                                        FROM historial_asignacion
                                        WHERE historial_cod_tramite = ? 
                                        ORDER BY id_historial_asignacion DESC LIMIT 1"
        );
        $stmt_asig->bind_param("s", $entrega_cod_tramite);
        $stmt_asig->execute();
        $stmt_asig->bind_result($id_historial_asignacion);
        $stmt_asig->fetch();
        $stmt_asig->close();

        if (!$id_historial_asignacion) {
            $id_historial_asignacion = 0;
        }

        if ($existe == 0) {
            $est_ventanilla           = 'ASIGNADO';
            $est_procedencia          = 'PENDIENTE';
            $est_atencion_procedencia = 'PENDIENTE';
            $est_conservacion         = 'PENDIENTE';
            $est_lider_juridico       = 'PENDIENTE';
            $est_control_calidad      = 'PENDIENTE';
            $est_lider_economico      = 'PENDIENTE';
            $est_consolidacion        = 'PENDIENTE';
            $est_edicion              = 'PENDIENTE';
            $est_avaluos              = 'PENDIENTE';
            $est_reconocimiento       = 'PENDIENTE';
            $est_director             = 'PENDIENTE';

            $sql_historial = "INSERT INTO historial_revision (
                                                id_historial_asignacion,
                                                historial_cod_tramite,
                                                entrega_id_tramite,
                                                asignacion_cc_usuario,
                                                asignacion_nombre_usuario,
                                                asignacion_apellido_usuario,
                                                asignacion_rol_usuario,
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
                                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_historial = $conn->prepare($sql_historial);
            if (!$stmt_historial) {
                die("Error al preparar inserción en historial_revision: " . $conn->error);
            }
            $historial_estado_tramite = "REVISION";
            $stmt_historial->bind_param(
                "isiisssssisssssssssssssssss",
                $id_historial_asignacion,
                $entrega_cod_tramite,
                $entrega_id_tramite,
                $entrega_cc_usuario,
                $entrega_nombre_usuario,
                $entrega_apellido_usuario,
                $asignacion_rol_usuario,
                $observacion_a_usuario_tramite,
                $historial_fecha_tramite,
                $creacion_tram_cc_usuario,
                $creacion_tram_nombre_usuario,
                $creacion_tram_apellido_usuario,
                $creacion_tram_rol_usuario,
                $fecha_limite,
                $historial_estado_tramite,
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
                die("Error al ejecutar inserción en historial_revision: " . $stmt_historial->error);
            }

            $stmt_historial->close();
        }
        $rol_actual = $_SESSION['rol_usuario'];
        $mapa_estados = [
            'ventanilla_catastral' => ['est_ventanilla', 'fecha_ventanilla'],
            'procedencia_juridica' => ['est_procedencia', 'fecha_procedencia'],
            'atencion_procedencia' => ['est_atencion_procedencia', 'fecha_ate_procedencia'],
            'coordinacion_tecnica' => ['est_conservacion', 'fecha_conservacion'],
            'revision_juridica'    => ['est_lider_juridico', 'fecha_lid_juridico'],
            'control_calidad'      => ['est_control_calidad', 'fecha_cont_calidad'],
            'componente_economico' => ['est_lider_economico', 'fecha_lid_economico'],
            'consolidacion'        => ['est_consolidacion', 'fecha_consolidacion'],
            'avaluos'              => ['est_avaluos', 'fecha_avaluos'],
            'editor'               => ['est_edicion', 'fecha_edicion'],
            'reconocedor'          => ['est_reconocimiento', 'fecha_reconocimiento'],
            'director_catastro'    => ['est_director', 'fecha_director']
        ];


        $rol_que_recibe   = $entrega_rol_usuario;
        $cedula           = $entrega_cc_usuario;
        $nombre_usuario   = $entrega_nombre_usuario;
        $apellido_usuario = $entrega_apellido_usuario;
        $fecha_limite_rol = $fecha_limite;


        if (isset($mapa_estados[$rol_actual])) {
            [$campo_estado, $campo_fecha] = $mapa_estados[$rol_actual];
            $fecha_aprobacion = date('Y-m-d H:i:s');

            $update = $conn->prepare("UPDATE historial_revision 
        SET $campo_estado = 'SUBSANADO',
            $campo_fecha = ?,
            rol_actual = ?,
            creacion_tram_rol_usuario = ?,
            creacion_tram_cc_usuario = ?,
            creacion_tram_nombre_usuario = ?,
            creacion_tram_apellido_usuario = ?,
            asignacion_rol_usuario  = ?,
            asignacion_cc_usuario   = ?,
            asignacion_nombre_usuario = ?,
            asignacion_apellido_usuario  = ?,
            fecha_limite = ?
        WHERE historial_cod_tramite = ?");
            $update->bind_param(
                "sssssssissss",
                $fecha_aprobacion,
                $rol_actual,
                $creacion_tram_rol_usuario,
                $creacion_tram_cc_usuario,
                $creacion_tram_nombre_usuario,
                $creacion_tram_apellido_usuario,
                $rol_que_recibe,
                $cedula,
                $nombre_usuario,
                $apellido_usuario,
                $fecha_limite,
                $entrega_cod_tramite
            );
            $update->execute();
            $update->close();
        }
        $id_devolucion = $_POST['id_devolucion'] ?? null;
        if ($id_devolucion) {
            echo "ID de devolución recibido: $id_devolucion<br>";
            $update_devolucion = $conn->prepare("UPDATE devolucion_tramites 
                                                    SET estado_devolucion = 'SUBSANADO'
                                                    WHERE id_devolucion = ?");
            if (!$update_devolucion) {
                die("Error al preparar la consulta: " . $conn->error);
            }
            $update_devolucion->bind_param("i", $id_devolucion);
            if (!$update_devolucion->execute()) {
                die("Error al ejecutar la consulta: " . $update_devolucion->error);
            }
            echo "Filas afectadas: " . $update_devolucion->affected_rows;
            $update_devolucion->close();
        } else {
            die("Falta el ID de devolución.");
        }
        if (isset($mapa_estados[$rol_que_recibe])) {
            [$campo_estado_recibe, $campo_fecha_recibe] = $mapa_estados[$rol_que_recibe];

            $update2 = $conn->prepare("UPDATE historial_revision
            SET $campo_estado_recibe = 'PENDIENTE',
                $campo_fecha_recibe = ?
            WHERE historial_cod_tramite = ?");
            $fecha_hoy = date('Y-m-d H:i:s');
            $update2->bind_param("ss", $fecha_hoy, $entrega_cod_tramite);
            $update2->execute();
            $update2->close();
        }


        echo <<<EOT
        <!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Animate.css para animaciones -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

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

            <h5 class="text-dark mb-3">Enviando información del trámite subsanado.</h5>
            <p class="text-muted mb-4">Por favor espere mientras se procesa y se entrega a la siguiente área</p>


            <div class="progress mb-3" style="height: 8px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated"
                    style="background: linear-gradient(90deg, #022F55 0%, #0F5699 50%, #4DA6FF 100%);"
                    role="progressbar"
                    aria-valuenow="100"
                    aria-valuemin="0"
                    aria-valuemax="100">
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
    // Auto-refresh para procesar el trámite después de mostrar el loader
    setTimeout(function() {
        // El procesamiento real ocurre aquí
        procesarAsignacion();
    }, 2000);

    function procesarAsignacion() {
        // Mostrar mensaje de finalización
        setTimeout(function() {
            // SweetAlert con colores del dashboard
            Swal.fire({
                icon: 'success',
                title: '¡Trámite Subsanado!',
                text: 'El trámite ha sido asignado correctamente para la revisión de la subsanación',
                confirmButtonColor: '#022F55',
                confirmButtonText: 'Continuar',
                background: '#ffffff',
                customClass: {
                    title: 'text-dark fw-bold',
                    confirmButton: 'btn-lg px-4'
                },
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            }).then(() => {
                window.location.href = 'index.php?page=tramites/consultar_tramite';
            });
        }, 1000);
    }
</script>
EOT;
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({
                title: 'Error',
                text: 'Ocurrió un error al asignar el trámite subsanado.',
                icon: 'error',
                confirmButtonColor: '#022F55',
                confirmButtonText: 'Aceptar'
            });
        </script>";
    }
} catch (mysqli_sql_exception $e) {
    die("Error MySQLi: " . $e->getMessage());
}

$conn->close();
