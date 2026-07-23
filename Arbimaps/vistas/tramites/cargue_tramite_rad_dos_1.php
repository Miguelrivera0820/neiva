<?php

date_default_timezone_set('America/Bogota');
register_shutdown_function(function () {
    $error = error_get_last();
    if (!$error || !in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        return;
    }

    $mensaje = htmlspecialchars($error['message'], ENT_QUOTES, 'UTF-8');
    $archivo = htmlspecialchars(basename($error['file']), ENT_QUOTES, 'UTF-8');
    $linea = (int) $error['line'];

    echo "<div class='container-fluid py-4'>
        <div class='alert alert-danger shadow-sm'>
            <h5 class='alert-heading'>No fue posible crear el trámite</h5>
            <p class='mb-1'>{$mensaje}</p>
            <small>Archivo: {$archivo}, línea {$linea}</small>
            <hr>
            <a class='btn btn-outline-danger btn-sm' href='index.php?page=tramites/crear_tramite'>Volver al formulario</a>
        </div>
    </div>";
});

require '../conexion.php';
require_once __DIR__ . '/../restricciones_predios/funciones_restricciones.php';

$conn = new mysqli($servidor, $usuario, $contrasena, $base_de_datos);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // DATOS DEL FORMULARIO INTERESADO
    $documento_interesado           = $_POST['documento_interesado']     ?? '';
    $num_doc_interesado             = $_POST['num_doc_interesado']       ?? '';
    $primer_nombre_interesado       = $_POST['primer_nombre_interesado'] ?? '';
    $segundo_nombre_interesado      = $_POST['segundo_nombre_interesado'] ?? '';
    $primer_apellido_interesado     = $_POST['primer_apellido_interesado'] ?? '';
    $segundo_apellido_interesado    = $_POST['segundo_apellido_interesado'] ?? '';
    $telefono_interesado            = $_POST['telefono_interesado']      ?? '';
    $municipi_rad                   = $_POST['municipio_rad']            ?? '';
    $correo_interesado              = $_POST['correo_interesado']        ?? '';
    $mutacion_tramite               = $_POST['mutacion_tramite']         ?? '';
    $tipo_tramite                   = strtoupper(trim($_POST['tipo_tramite'] ?? 'ACTUALIZACION'));
    if (!in_array($tipo_tramite, ['ACTUALIZACION', 'CONSERVACION'], true)) {
        $tipo_tramite = 'ACTUALIZACION';
    }
    $subtipo_actualizacion          = trim($_POST['subtipo_actualizacion'] ?? '');
    $otro_proceso_actualizacion     = trim($_POST['otro_proceso_actualizacion'] ?? '');
    $subtipo_conservacion           = trim($_POST['subtipo_conservacion'] ?? '');
    $detalle_subtipo_conservacion   = trim($_POST['detalle_subtipo_conservacion'] ?? '');
    $otro_subtipo_conservacion      = trim($_POST['otro_subtipo_conservacion'] ?? '');
    $tsolicitante_tramite           = $_POST['tsolicitante_tramite']     ?? '';
    $fmi_predio                     = $_POST['fmi_predio']               ?? '';
    $npn_predio                     = $_POST['npn_predio']               ?? '';
    $es_radicado_mercurio           = ($_POST['es_radicado_mercurio'] ?? 'NO') === 'SI' ? 'SI' : 'NO';
    $cod_tramite_mercurio           = trim($_POST['cod_tramite_mercurio'] ?? '');
    $observacion_tramite            = $_POST['observacion_tramite']      ?? '';
    $fecha_rad = date('Y-m-d H:i:s');

    try {
        $bloqueoPredio = buscarBloqueoActivoPredio($conn, $npn_predio, $fmi_predio);
    } catch (Throwable $error) {
        http_response_code(500);
        die('No fue posible validar el estado de bloqueo del predio.');
    }
    if ($bloqueoPredio) {
        $mensajeBloqueo = json_encode(
            'Este predio está bloqueado y no se permite crear el trámite. Motivo: ' . $bloqueoPredio['motivo'],
            JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );
        echo "<!doctype html><html lang='es'><head><meta charset='utf-8'>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body>
            <script>
            Swal.fire({
                icon: 'error',
                title: 'Predio bloqueado',
                text: {$mensajeBloqueo},
                confirmButtonColor: '#b42318'
            }).then(() => history.back());
            </script></body></html>";
        exit;
    }

    // DATOS DEL PREDIO (generales)
    $avaluo_terreno_tramite     = $_POST['avaluo_terreno_tramite'] ?? '';
    $direccion_predio           = $_POST['direccion_predio'] ?? '';
    $dest_econ_predio           = $_POST['dest_econ_predio'] ?? '';
    $area_terreno_predio        = $_POST['area_terreno_predio'] ?? '';
    $area_construccion_predio   = $_POST['area_construccion_predio'] ?? '';

    // VALIDAR CORREO
    if (!$correo_interesado) {
        echo json_encode(['success' => false, 'message' => 'Correo no válido.']);
        exit;
    }

    if ($tipo_tramite === 'ACTUALIZACION' && $mutacion_tramite === '') {
        echo json_encode(['success' => false, 'message' => 'Debe seleccionar el tramite de actualizacion.']);
        exit;
    }

    if ($tipo_tramite === 'CONSERVACION' && $subtipo_conservacion === '') {
        echo json_encode(['success' => false, 'message' => 'Debe seleccionar el subtipo de conservacion.']);
        exit;
    }

    if ($tipo_tramite === 'CONSERVACION' && $detalle_subtipo_conservacion === '') {
        echo json_encode(['success' => false, 'message' => 'Debe seleccionar el detalle de conservacion.']);
        exit;
    }

    // Mantiene compatibilidad con listados antiguos que muestran mutacion_tramite.
    if ($tipo_tramite === 'CONSERVACION') {
        $mutacion_tramite = $subtipo_conservacion;
    }

    if ($es_radicado_mercurio === 'SI' && $cod_tramite_mercurio === '') {
        echo json_encode(['success' => false, 'message' => 'Debe ingresar el radicado MERCURIO.']);
        exit;
    }

    if ($es_radicado_mercurio === 'SI') {
        $validar_codigo = $conn->prepare("SELECT id_tramite FROM tramite_radicacion WHERE cod_tramite = ? LIMIT 1");
        if (!$validar_codigo) die("Error al preparar validacion MERCURIO: " . $conn->error);
        $validar_codigo->bind_param("s", $cod_tramite_mercurio);
        $validar_codigo->execute();
        $validar_codigo->store_result();

        if ($validar_codigo->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'El radicado MERCURIO ya existe en el sistema.']);
            exit;
        }
    }

    // Insertar en tramite_radicacion
    $sql = "INSERT INTO tramite_radicacion (
                        documento_interesado, 
                        num_doc_interesado, 
                        primer_nombre_interesado, 
                        segundo_nombre_interesado, 
                        primer_apellido_interesado, 
                        segundo_apellido_interesado, 
                        telefono_interesado,
                        municipio_rad, 
                        correo_interesado, 
                        mutacion_tramite, 
                        tipo_tramite,
                        subtipo_actualizacion,
                        otro_proceso_actualizacion,
                        subtipo_conservacion,
                        detalle_subtipo_conservacion,
                        otro_subtipo_conservacion,
                        tsolicitante_tramite, 
                        fmi_predio, 
                        npn_predio, 
                        observacion_tramite, 
                        fecha_rad
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) die("Error al preparar INSERT: " . $conn->error);

    $stmt->bind_param(
        "sssssssssssssssssssss",
        $documento_interesado,
        $num_doc_interesado,
        $primer_nombre_interesado,
        $segundo_nombre_interesado,
        $primer_apellido_interesado,
        $segundo_apellido_interesado,
        $telefono_interesado,
        $municipi_rad,
        $correo_interesado,
        $mutacion_tramite,
        $tipo_tramite,
        $subtipo_actualizacion,
        $otro_proceso_actualizacion,
        $subtipo_conservacion,
        $detalle_subtipo_conservacion,
        $otro_subtipo_conservacion,
        $tsolicitante_tramite,
        $fmi_predio,
        $npn_predio,
        $observacion_tramite,
        $fecha_rad
    );

    if ($stmt->execute()) {
        $id_tramite = $conn->insert_id;

        if ($es_radicado_mercurio === 'SI') {
            $codigo = $cod_tramite_mercurio;
        } else {
            // Generar cÃ³digo de trÃ¡mite
            $tipo_codigo_map = [
                'Mutacion_1'        => '01',
                'Mutacion_2'        => '02',
                'Mutacion_3'        => '03',
                'Mutacion_4'        => '04',
                'Mutacion_5'        => '05',
                'Cancelacion'       => '06',
                'Complementacion'   => '07',
                'Peticion'          => '08',
                'Reclamo'           => '09',
                'Queja'             => '10',
                'Solicitud'         => '11',
                'Otro'              => '12'
            ];
            $subtipo_conservacion_codigo_map = [
                'Mutaciones' => '80',
                'Rectificacion' => '81',
                'Otros_Procesos' => '82',
                'Edicion_GeograficaV2' => '83',
                'Proceso_Notificacion' => '84',
                'Avaluo_Puntual' => '85',
                'Procesos_Especiales' => '86'
            ];
            $tipo_codigo = $tipo_tramite === 'CONSERVACION'
                ? ($subtipo_conservacion_codigo_map[$subtipo_conservacion] ?? '99')
                : ($tipo_codigo_map[$mutacion_tramite] ?? '99');
            $codigo = "CAT-" . date("Y") . "-" . $tipo_codigo . "-" . str_pad($id_tramite, 5, "0", STR_PAD_LEFT);
        }

        // Actualizar cod_tramite en tramite_radicacion
        $update_codigo = $conn->prepare("UPDATE tramite_radicacion SET cod_tramite = ? WHERE id_tramite = ?");
        $update_codigo->bind_param("si", $codigo, $id_tramite);
        $update_codigo->execute();

        if (!$update_codigo->affected_rows && $conn->errno) {
            echo json_encode(['success' => false, 'message' => 'Error al guardar el codigo de tramite: ' . $conn->error]);
            exit;
        }

        if (isset($_POST['propietarios']) && is_array($_POST['propietarios'])) {
            $sql2 = "INSERT INTO tramite_info_predio (
                                    fmi_predio_tram, 
                                    npn_predio_tram, 
                                    nombre_propietario_tram,
                                    tipo_doc_propietario_tram, 
                                    cedula_propietario_tram, 
                                    valor_avaluo_terreno_tram,
                                    direccion_predio_terreno_tram, 
                                    destino_econ_predio_tram,
                                    area_terr_predio_tram, 
                                    area_cons_predio_tram, 
                                    id_tramite_rad, 
                                    info_cod_tramite
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt2 = $conn->prepare($sql2);
            if (!$stmt2) die("Error al preparar INSERT info predio: " . $conn->error);

            foreach ($_POST['propietarios'] as $prop) {
                $nombre = $prop['nombre'] ?? '';
                $tipo_doc = $prop['tipo_doc'] ?? '';
                $numero_doc = preg_replace('/^0+/', '', $prop['numero_doc'] ?? '');

                $stmt2->bind_param(
                    "ssssissssiis",
                    $fmi_predio,
                    $npn_predio,
                    $nombre,
                    $tipo_doc,
                    $numero_doc,
                    $avaluo_terreno_tramite,
                    $direccion_predio,
                    $dest_econ_predio,
                    $area_terreno_predio,
                    $area_construccion_predio,
                    $id_tramite,
                    $codigo
                );
                $stmt2->execute();
            }
        }

        // Crear carpeta
        $anio_actual = date('Y');
        if ($es_radicado_mercurio === 'SI' && preg_match('/^(20\d{2})/', $codigo, $m)) {
            $anio_actual = $m[1];
        }
        $usuario_dir = "tramites_conservacion/$anio_actual/$codigo/";
        if (!file_exists($usuario_dir)) {
            mkdir($usuario_dir, 0777, true);
        }

        // Subir archivos
        $archivos = [
            'sol_escrita_tramite',
            'cop_escritura_tramite',
            'ctl_tramite',
            'doc_identidad_tramite',
            'carta_autorizacion_tramite',
            'otros_doc_tramite'
        ];

        $nombres_archivos = [];

        foreach ($archivos as $archivo) {
            if (isset($_FILES[$archivo]) && $_FILES[$archivo]['error'] === 0) {
                $nombre_archivo = basename($_FILES[$archivo]['name']);
                $ruta_destino = $usuario_dir . $nombre_archivo;
                move_uploaded_file($_FILES[$archivo]['tmp_name'], $ruta_destino);
                $nombres_archivos[$archivo] = $nombre_archivo;
            } else {
                $nombres_archivos[$archivo] = null;
            }
        }

        // Actualizar archivos en tramite_radicacion
        $update = "UPDATE tramite_radicacion SET 
            sol_escrita_tramite=?, cop_escritura_tramite=?, ctl_tramite=?, 
            doc_identidad_tramite=?, carta_autorizacion_tramite=?, otros_doc_tramite=?
            WHERE id_tramite=?";
        $update_stmt = $conn->prepare($update);
        $update_stmt->bind_param(
            "ssssssi",
            $nombres_archivos['sol_escrita_tramite'],
            $nombres_archivos['cop_escritura_tramite'],
            $nombres_archivos['ctl_tramite'],
            $nombres_archivos['doc_identidad_tramite'],
            $nombres_archivos['carta_autorizacion_tramite'],
            $nombres_archivos['otros_doc_tramite'],
            $id_tramite
        );
        $update_stmt->execute();

        $codigo_mostrar = htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8');
        $url_consulta = 'index.php?page=tramites/consultar_tramite';
        echo "<div class='container-fluid py-4'>
            <div class='alert alert-success shadow-sm text-center d-none'>
                <h4 class='alert-heading'>Trámite creado correctamente</h4>
                <p class='mb-3'>El radicado <strong>{$codigo_mostrar}</strong> fue guardado.</p>
                <a class='btn text-white' style='background:radial-gradient(circle, rgba(10,44,27,1) 60%, rgba(15,61,38,1) 97%); border-radius:16px;' href='{$url_consulta}'>Continuar</a>
            </div>
        </div>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            (function () {
                var destino = " . json_encode($url_consulta) . ";
                var codigo = " . json_encode($codigo) . ";
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Trámite creado',
                        text: 'El trámite fue guardado correctamente.',
                        confirmButtonColor: '#0A2C1B',
                        html: 'El tramite fue guardado correctamente.<br><strong>Radicado: ' + codigo + '</strong>',
                        confirmButtonText: 'Continuar',
                        background: '#ffffff',
                        color: '#0A2C1B',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then(function () {
                        window.location.href = destino;
                    });
                } else {
                    window.setTimeout(function () {
                        window.location.href = destino;
                    }, 2500);
                }
            }());
        </script>";
        return;

        // Después de actualizar los archivos y antes del cierre del if ($stmt->execute()) {
        echo <<<EOT
            <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Animate.css para animaciones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        .card{
            background-image: url('assets/img/logobnb.png') !important;
            background-size: 15em !important;
            background-repeat: no-repeat !important;
            background-position: 120% 230% !important;
        }
        .custom-loader {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: conic-gradient(
                #022F55 0% 25%,
                #0F5699 25% 50%,
                #4DA6FF 50% 75%,
                #66CC99 75% 100%
            );
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
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .progress-bar {
            animation: progress-animation 4s ease-in-out;
        }
        @keyframes progress-animation {
            0% { width: 0%; }
            30% { width: 40%; }
            70% { width: 80%; }
            100% { width: 100%; }
        }
        .card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            border: none;
        }
        .loader-container {
            position: relative;
            padding: 20px;
        }
        .pulse-icon {
            animation: pulse-scale 2s infinite;
        }
        @keyframes pulse-scale {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class='container-fluid border  d-flex align-items-center justify-content-center' style='min-height: 100%;'>
        <div class='card contenedor shadow-lg border-0'>
            <div class='card-header text-center text-white' style='background: linear-gradient(135deg, #022F55 0%, #0F5699 100%); border-radius: 0.5rem 0.5rem 0 0;'>
                <h4 class='mb-0 fw-bold'>
                    <i class='bi bi-file-earmark-plus pulse-icon me-2'></i>
                    Creando TrÃ¡mite
                </h4>
            </div>
            <div class='card-body text-center py-5'>
                <!-- Loader personalizado -->
                <div class='loader-container mb-4'>
                    <div class='custom-loader'></div>
                </div>
                
                <h5 class='text-dark mb-3'>Procesando información...</h5>
                <p class='text-muted mb-4'>Por favor espere mientras se crea el trámite y se organizan los documentos.</p>
                
                <!-- Barra de progreso animada -->
                <div class='progress mb-3' style='height: 8px;'>
                    <div class='progress-bar progress-bar-striped progress-bar-animated' 
                         style='background: linear-gradient(90deg, #022F55 0%, #0F5699 50%, #4DA6FF 100%);' 
                         role='progressbar' 
                         aria-valuenow='100' 
                         aria-valuemin='0' 
                         aria-valuemax='100'>
                    </div>
                </div>
                
                <!-- Estados del proceso -->
                <div class='row text-center'>
                    <div class='col-4'>
                        <div class='mb-2'>
                            <i class='bi bi-check-circle-fill text-success' style='font-size: 1.2rem;'></i>
                        </div>
                        <small class='text-muted'>Datos guardados</small>
                    </div>
                    <div class='col-4'>
                        <div class='mb-2'>
                            <div class='spinner-border text-primary' style='width: 1.2rem; height: 1.2rem;'></div>
                        </div>
                        <small class='text-muted'>Subiendo archivos</small>
                    </div>
                    <div class='col-4'>
                        <div class='mb-2'>
                            <i class='bi bi-clock text-secondary' style='font-size: 1.2rem;'></i>
                        </div>
                        <small class='text-muted'>Finalizando</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    window.onload = function () {
        // Mostrar progreso paso a paso
        setTimeout(() => {
            document.querySelector('.col-4:nth-child(2) .spinner-border').outerHTML = "<i class='bi bi-check-circle-fill text-success' style='font-size: 1.2rem;'></i>";
            document.querySelector('.col-4:nth-child(2) small').textContent = 'Archivos subidos';
        }, 2000);

        setTimeout(() => {
            // Cambiar tercer icono a completado
            document.querySelector('.col-4:nth-child(3) i').className = 'bi bi-check-circle-fill text-success';
            document.querySelector('.col-4:nth-child(3) small').textContent = 'Completado';
        }, 3500);

        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: '¡Trámite Creado!',
                text: 'El trámite ha sido creado correctamente en la plataforma.',
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
        }, 4000);
    };
    </script>
EOT;
    } else {
        echo json_encode(['success' => false, 'message' => 'Error en INSERT: ' . $stmt->error]);
    }
}

$conn->close();
?>

