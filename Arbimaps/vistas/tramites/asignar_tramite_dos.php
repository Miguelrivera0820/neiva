<!-- este archivo está modificado levemente para organizar el loader. el archivo original es asignar_tramite.php -->

<?php
date_default_timezone_set('America/Bogota');

$conn = new mysqli($servidor, $usuario, $contrasena, $base_de_datos);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mostrar vista de loading inmediatamente
?>
    <div class="container-fluid d-flex align-items-center justify-content-center" style="min-height: 70vh;">
        <div class="card shadow-lg border-0" style="max-width: 500px; width: 100%;">
            <div class="card-header text-center text-white" style="background: linear-gradient(135deg, #022F55 0%, #0F5699 100%); border-radius: 0.5rem 0.5rem 0 0;">
                <h4 class="mb-0 fw-bold">
                    <i class="bi bi-gear-fill me-2"></i>
                    Procesando Asignación
                </h4>
            </div>
            <div class="card-body text-center py-5">
                <!-- Loader personalizado -->
                <div class="loader-container mb-4">
                    <div class="custom-loader"></div>
                </div>

                <h5 class="text-dark mb-3">Asignando trámite...</h5>
                <p class="text-muted mb-4">Por favor espere mientras se procesa la asignación del trámite.</p>

                <!-- Barra de progreso animada -->
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
                    title: '¡Trámite Asignado!',
                    text: 'El trámite ha sido asignado correctamente.',
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

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Animate.css para animaciones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

<?php
    // Aquí va la lógica de procesamiento real
    try {
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

        // Iniciar transacción
        $conn->autocommit(false);

        // 1. Insertar en asignacion_tramite
        $sql = "INSERT INTO asignacion_tramite (
            asignacion_cod_tramite,
            asignacion_cc_usuario,
            asignacion_nombre_usuario,
            asignacion_apellido_usuario,
            asignacion_rol_usuario,
            observacion_a_usuario_tramite,
            asignacion_fecha_tramite,
            creacion_tram_cc_usuario,
            creacion_tram_nombre_usuario,
            creacion_tram_apellido_usuario,
            creacion_tram_rol_usuario,
            asignacion_estado_tramite,
            fecha_limite
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Error al preparar la consulta: " . $conn->error);
        }

        $stmt->bind_param(
            "sssssssssssss",
            $asignacion_cod_tramite,
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
            $asignacion_estado_tramite,
            $fecha_limite
        );

        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar inserción: " . $stmt->error);
        }

        $asignacion_id_insertado = $conn->insert_id;

        // 2. Manejo de archivos: se guarda ruta relativa en BD y fisica fuera de Arbimaps.
        $ruta_base_relativa = "tramites_conservacion/$anio/$cod_tramite/$creacion_tram_rol_usuario";
        $ruta_base = dirname(__DIR__, 3) . '/' . $ruta_base_relativa;

        if (!is_dir($ruta_base)) {
            mkdir($ruta_base, 0777, true);
        }

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

                if (move_uploaded_file($_FILES[$campo]['tmp_name'], $ruta_destino)) {
                    $documentos[$campo] = $ruta_base_relativa . '/' . $nombre_archivo;
                } else {
                    $documentos[$campo] = null;
                }
            } else {
                $documentos[$campo] = null;
            }
        }

        // 3. Insertar en estados_tramite
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
            throw new Exception("Error al preparar inserción en estados_tramite: " . $conn->error);
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

        if (!$stmt_estado->execute()) {
            throw new Exception("Error al insertar estado: " . $stmt_estado->error);
        }

        // 4. Insertar documentos
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
        if (!$stmt_doc) {
            throw new Exception("Error al preparar inserción de documentos: " . $conn->error);
        }

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
            $observacion_a_usuario_tramite,
            $documento_estado
        );

        if (!$stmt_doc->execute()) {
            throw new Exception("Error al insertar documentos: " . $stmt_doc->error);
        }

        // 5. Verificar e insertar en historial_asignacion
        $consultaExistencia = "SELECT COUNT(*) FROM historial_asignacion WHERE historial_cod_tramite = ?";
        $stmtExistencia = $conn->prepare($consultaExistencia);
        $stmtExistencia->bind_param("s", $cod_tramite);
        $stmtExistencia->execute();
        $stmtExistencia->bind_result($existe);
        $stmtExistencia->fetch();
        $stmtExistencia->close();

        if ($existe == 0) {
            // Valores por defecto para los estados
            $est_ventanilla = 'ASIGNADO';
            $est_procedencia = 'PENDIENTE';
            $est_atencion_procedencia = 'PENDIENTE';
            $est_conservacion = 'PENDIENTE';
            $est_lider_juridico = 'PENDIENTE';
            $est_control_calidad = 'PENDIENTE';
            $est_lider_economico = 'PENDIENTE';
            $est_consolidacion = 'PENDIENTE';
            $est_edicion = 'PENDIENTE';
            $est_avaluos = 'PENDIENTE';
            $est_reconocimiento = 'PENDIENTE';
            $est_director = 'PENDIENTE';

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
                throw new Exception("Error al preparar inserción en historial_asignacion: " . $conn->error);
            }

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
                throw new Exception("Error al ejecutar inserción en historial_asignacion: " . $stmt_historial->error);
            }

            $stmt_historial->close();
        }

        // 6. Actualizar historial según rol actual
        $rol_actual = $_SESSION['rol_usuario'];
        $campo_estado = '';
        $campo_fecha = '';

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
        }

        if ($campo_estado && $campo_fecha) {
            $fecha_aprobacion = date('Y-m-d H:i:s');

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

            $update->bind_param(
                "ssisssssss",
                $fecha_aprobacion,
                $rol_actual,
                $creacion_tram_cc_usuario,
                $creacion_tram_nombre_usuario,
                $creacion_tram_apellido_usuario,
                $asignacion_rol_usuario,
                $asignacion_nombre_usuario,
                $asignacion_apellido_usuario,
                $fecha_limite,
                $cod_tramite
            );

            $update->execute();
        }

        // Confirmar transacción
        $conn->commit();

        // Establecer mensaje de éxito
        $_SESSION['mensaje_exito'] = "Trámite " . $cod_tramite . " asignado exitosamente a " . $asignacion_nombre_usuario . " " . $asignacion_apellido_usuario;
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        $_SESSION['mensaje_error'] = "Error al asignar el trámite: " . $e->getMessage();

        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al asignar el trámite.',
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                history.back();
            });
        </script>";
    }

    $stmt->close();
    $conn->close();
    exit();
} else {
    // Si no es POST, mostrar error
    $_SESSION['mensaje_error'] = "Acceso no válido";
    header("Location: index.php?page=tramites/consultar_tramite");
    exit();
}
?>
