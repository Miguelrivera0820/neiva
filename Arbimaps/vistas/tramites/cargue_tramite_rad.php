<?php
// session_start();
date_default_timezone_set('America/Bogota');
require '../conexion.php';
require_once __DIR__ . '/../restricciones_predios/funciones_restricciones.php';

$conn = new mysqli($servidor, $usuario, $contrasena, $base_de_datos);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // DATOS DEL FORMULARIO INTERESADO
    $documento_interesado     = $_POST['documento_interesado']     ?? '';
    $num_doc_interesado       = $_POST['num_doc_interesado']       ?? '';
    $primer_nombre_interesado = $_POST['primer_nombre_interesado'] ?? '';
    $segundo_nombre_interesado= $_POST['segundo_nombre_interesado']?? '';
    $primer_apellido_interesado = $_POST['primer_apellido_interesado'] ?? '';
    $segundo_apellido_interesado= $_POST['segundo_apellido_interesado']?? '';
    $telefono_interesado      = $_POST['telefono_interesado']      ?? '';
    $correo_interesado        = $_POST['correo_interesado']        ?? '';
    $mutacion_tramite         = $_POST['mutacion_tramite']         ?? '';
    $tipo_tramite             = strtoupper(trim($_POST['tipo_tramite'] ?? 'ACTUALIZACION'));
    if (!in_array($tipo_tramite, ['ACTUALIZACION', 'CONSERVACION'], true)) {
        $tipo_tramite = 'ACTUALIZACION';
    }
    $subtipo_actualizacion    = trim($_POST['subtipo_actualizacion'] ?? '');
    $otro_proceso_actualizacion = trim($_POST['otro_proceso_actualizacion'] ?? '');
    $subtipo_conservacion     = trim($_POST['subtipo_conservacion'] ?? '');
    $detalle_subtipo_conservacion = trim($_POST['detalle_subtipo_conservacion'] ?? '');
    $otro_subtipo_conservacion = trim($_POST['otro_subtipo_conservacion'] ?? '');
    $tsolicitante_tramite     = $_POST['tsolicitante_tramite']     ?? '';
    $fmi_predio               = $_POST['fmi_predio']               ?? '';
    $npn_predio               = $_POST['npn_predio']               ?? '';
    $es_radicado_mercurio     = ($_POST['es_radicado_mercurio'] ?? 'NO') === 'SI' ? 'SI' : 'NO';
    $cod_tramite_mercurio     = trim($_POST['cod_tramite_mercurio'] ?? '');
    $observacion_tramite      = $_POST['observacion_tramite']      ?? '';
    $fecha_rad = date('Y-m-d H:i:s');

    try {
        $bloqueoPredio = buscarBloqueoActivoPredio($conn, $npn_predio, $fmi_predio);
    } catch (Throwable $error) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'No fue posible validar el estado del predio.']);
        exit;
    }
    if ($bloqueoPredio) {
        http_response_code(423);
        echo json_encode([
            'success' => false,
            'bloqueado' => true,
            'message' => 'Este predio está bloqueado y no se permite crear el trámite. Motivo: ' . $bloqueoPredio['motivo'],
            'bloqueo' => $bloqueoPredio,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // DATOS DEL PREDIO
    $fmi_predio_tramite = $_POST['fmi_predio'] ?? '';
    $npn_predio_tramite = $_POST['npn_predio'] ?? '';
    $nombre_propietario_tramite = $_POST['nombre_propietario_tramite'] ?? '';
    $tipo_doc_propietario_tramite = $_POST['tipo_doc_propietario_tramite'] ?? '';
    $num_doc_propietario_tramite = $_POST['numero_doc_propietario_tramite'] ?? '';
    $avaluo_terreno_tramite = $_POST['avaluo_terreno_tramite'] ?? '';
    $direccion_predio = $_POST['direccion_predio'] ?? '';
    $dest_econ_predio = $_POST['dest_econ_predio'] ?? '';
    $area_terreno_predio = $_POST['area_terreno_predio'] ?? '';
    $area_construccion_predio = $_POST['area_construccion_predio'] ?? '';

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
        documento_interesado, num_doc_interesado, primer_nombre_interesado, 
        segundo_nombre_interesado, primer_apellido_interesado, segundo_apellido_interesado, 
        telefono_interesado, correo_interesado, mutacion_tramite, tipo_tramite, subtipo_actualizacion, otro_proceso_actualizacion, subtipo_conservacion, detalle_subtipo_conservacion, otro_subtipo_conservacion, tsolicitante_tramite, 
        fmi_predio, npn_predio, observacion_tramite, fecha_rad
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) die("Error al preparar INSERT: " . $conn->error);

    $stmt->bind_param(
        "ssssssssssssssssssss",
        $documento_interesado,
        $num_doc_interesado,
        $primer_nombre_interesado,
        $segundo_nombre_interesado,
        $primer_apellido_interesado,
        $segundo_apellido_interesado,
        $telefono_interesado,
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

        // Generar código de trámite
        if ($es_radicado_mercurio === 'SI') {
            $codigo = $cod_tramite_mercurio;
        } else {
            $tipo_codigo_map = [
                'Mutacion_1' => '01', 'Mutacion_2' => '02', 'Mutacion_3' => '03',
                'Mutacion_4' => '04', 'Mutacion_5' => '05', 'Cancelacion' => '06',
                'Complementacion' => '07', 'Peticion' => '08', 'Reclamo' => '09',
                'Queja' => '10', 'Solicitud' => '11', 'Otro' => '12'
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

        // Insertar en tramite_info_predio
        $sql2 = "INSERT INTO tramite_info_predio (
            fmi_predio_tram, npn_predio_tram, nombre_propietario_tram,
            tipo_doc_propietario_tram, cedula_propietario_tram, valor_avaluo_terreno_tram,
            direccion_predio_terreno_tram, destino_econ_predio_tram,
            area_terr_predio_tram, area_cons_predio_tram, id_tramite_rad, info_cod_tramite
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt2 = $conn->prepare($sql2);
        if (!$stmt2) die("Error al preparar INSERT info predio: " . $conn->error);

        $stmt2->bind_param(
            "ssssissssiis",
            $fmi_predio_tramite,
            $npn_predio_tramite,
            $nombre_propietario_tramite,
            $tipo_doc_propietario_tramite,
            $num_doc_propietario_tramite,
            $avaluo_terreno_tramite,
            $direccion_predio,
            $dest_econ_predio,
            $area_terreno_predio,
            $area_construccion_predio,
            $id_tramite,
            $codigo
        );

        if (!$stmt2->execute()) {
            die("Error al ejecutar INSERT info predio: " . $stmt2->error);
        }

        // Actualizar cod_tramite en tramite_radicacion
        $update_codigo = $conn->prepare("UPDATE tramite_radicacion SET cod_tramite = ? WHERE id_tramite = ?");
        if (!$update_codigo) die("Error al preparar UPDATE código: " . $conn->error);
        $update_codigo->bind_param("si", $codigo, $id_tramite);
        $update_codigo->execute();

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
            'sol_escrita_tramite', 'cop_escritura_tramite', 'ctl_tramite',
            'doc_identidad_tramite', 'carta_autorizacion_tramite', 'otros_doc_tramite'
        ];

        $nombres_archivos = [];

        foreach ($archivos as $archivo) {
            if (isset($_FILES[$archivo]) && $_FILES[$archivo]['error'] === 0) {
                $nombre_archivo = basename($_FILES[$archivo]['name']);
                $ruta_destino = $usuario_dir . $nombre_archivo;
                if (move_uploaded_file($_FILES[$archivo]['tmp_name'], $ruta_destino)) {
                    $nombres_archivos[$archivo] = $nombre_archivo;
                } else {
                    $nombres_archivos[$archivo] = null;
                }
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
        if (!$update_stmt) die("Error al preparar UPDATE archivos: " . $conn->error);
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

            // Después de actualizar los archivos y antes del cierre del if ($stmt->execute()) {
            echo "
                <!DOCTYPE html>
                <html lang='es'>
                    <head>
                        <meta charset='UTF-8'>
                        <title>Creando Trámite...</title>
                        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                        <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap' rel='stylesheet'>
                        <style>
                            body {
                                font-family: 'Poppins', sans-serif;
                                background-color: #f8f9fa;
                            }
                            .loader {
                                width: 60px;
                                height: 60px;
                                border-radius: 50%;
                                background: conic-gradient(
                                    #c52f1eff 0% 20%,
                                    #f1c40f 20% 40%,
                                    #008839 40% 60%,
                                    #035286 60% 80%,
                                    #7a00dd 80% 100%
                                );
                                animation: spin 1s linear infinite;
                                margin: 20px auto;
                                mask: radial-gradient(farthest-side, transparent calc(100% - 6px), black calc(100% - 5px));
                                -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 6px), black calc(100% - 5px));
                            }
                            @keyframes spin {
                                0%   { transform: rotate(0deg); }
                                100% { transform: rotate(360deg); }
                            }
                        </style>
                    </head>
                        <body>
                        <script>
                            window.onload = function () {
                                Swal.fire({
                                    title: 'Cargando trámite...',
                                    html: '<div class=\"loader\"></div>',
                                    allowOutsideClick: false,
                                    showConfirmButton: false
                                });

                                setTimeout(() => {
                                    Swal.fire({
                                        icon: 'success',
                                        title: '¡Trámite creado!',
                                        text: 'El trámite ha sido creado correctamente en plataforma.',
                                        confirmButtonColor: '#02722dff',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        window.location.href = 'index.php?page=tramites/consultar_tramite';
                                    });
                                }, 4000);
                            };
                        </script>
                    </body>
                </html>
            ";

    } else {
        echo json_encode(['success' => false, 'message' => 'Error en INSERT: ' . $stmt->error]);
    }
}

$conn->close();
?>

