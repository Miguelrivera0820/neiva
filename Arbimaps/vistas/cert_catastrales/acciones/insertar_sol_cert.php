<?php
// session_start();
date_default_timezone_set('America/Bogota');
// require '../conexion.php';

$conn = new mysqli($servidor, $usuario, $contrasena, $base_de_datos);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // DATOS DEL FORMULARIO INTERESADO
    $cert_tipo_documento              = $_POST['cert_documento_interado'] ?? '';
    $cert_num_cc_interesado           = $_POST['num_cc_interesado'] ?? '';
    $cert_primer_nombre_interesado    = $_POST['cert_primer_nombre'] ?? '';
    $cert_segundo_nombre_interesado   = $_POST['cert_segundo_nombre'] ?? '';
    $cert_primer_apellido_interesado  = $_POST['cert_primer_apellido'] ?? '';
    $cert_segundo_apellido_interesado = $_POST['cert_segundo_apellido']?? '';
    $cert_numero_cel_interesado       = $_POST['cert_telefono_interesado'] ?? '';
    $cert_correo_electronico          = $_POST['cert_correo_interesado'] ?? '';
    $cert_medio_envio = '';
    if (isset($_POST['cert_medio_envio']) && is_array($_POST['cert_medio_envio'])) {
        $cert_medio_envio = implode(",", $_POST['cert_medio_envio']); 
    }
    $cert_anio_vigencia                 = $_POST['anio_vigencia'] ?? '';
    $cert_avaluo_terreno_tramite        = $_POST['avaluo_terreno_tramite'] ?? '';
    $cert_direccion_predio              = $_POST['direccion_predio'] ?? '';
    $cert_dest_econ_predio              = $_POST['dest_econ_predio'] ?? ''; 
    $cert_area_terreno_predio           = $_POST['area_terreno_predio'] ?? '';  
    $cert_area_construccion_predio      = $_POST['area_construccion_predio'] ?? '';

    $hora_creacion_certificado = date('Y-m-d H:i:s');

    // INFORMACION DE PREDIO
    $fmi_predio_tramite = $_POST['cert_fmi_predio'] ?? '';
    $npn_predio_tramite = $_POST['cert_npn_predio'] ?? '';
    
    $sql = "INSERT INTO certificado_catastral (
        certificado_hora_creacion, cert_tipo_documento, cert_num_cc_interesado,
        cert_primer_nombre_interesado, cert_segundo_nombre_interesado, cert_primer_apellido_interesado,
        cert_segundo_apellido_interesado, cert_numero_cel_interesado, cert_correo_electronico, cert_medio_envio,
        cert_npn_predio, cert_fmi_predio, cert_anio_vigencia, cert_avaluo_terreno_tramite, cert_direccion_predio,
        cert_dest_econ_predio, cert_area_terreno_predio, cert_area_construccion_predio
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error en prepare: " . $conn->error);
    }
    $stmt->bind_param(
        "ssssssssssssssssss",  // 18 valores en total
        $hora_creacion_certificado,
        $cert_tipo_documento,
        $cert_num_cc_interesado,
        $cert_primer_nombre_interesado,
        $cert_segundo_nombre_interesado,
        $cert_primer_apellido_interesado,
        $cert_segundo_apellido_interesado,
        $cert_numero_cel_interesado,
        $cert_correo_electronico,
        $cert_medio_envio,
        $npn_predio_tramite,
        $fmi_predio_tramite,
        $cert_anio_vigencia,
        $cert_avaluo_terreno_tramite,
        $cert_direccion_predio,
        $cert_dest_econ_predio,
        $cert_area_terreno_predio,
        $cert_area_construccion_predio
    );

    if ($stmt->execute()) {
        $id_certificado = $conn->insert_id;

        // ============================
        // 🔹 Generar código_certificado
        // ============================
        $anio_actual = date('Y');

        $sql_count = "SELECT COUNT(*) AS total FROM certificado_catastral 
                      WHERE YEAR(certificado_hora_creacion) = ?";
        $stmt_count = $conn->prepare($sql_count);
        $stmt_count->bind_param("s", $anio_actual);
        $stmt_count->execute();
        $result_count = $stmt_count->get_result();
        $row_count = $result_count->fetch_assoc();
        $consecutivo = $row_count['total'];

        $codigo_certificado = "CERT-" . $anio_actual . "-" . str_pad($consecutivo, 2, "0", STR_PAD_LEFT);

        $sql_update = "UPDATE certificado_catastral SET codigo_certificado = ? WHERE certificado_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $codigo_certificado, $id_certificado);
        $stmt_update->execute();

        // ============================
        // 🔹 Insertar propietarios
        // ============================
        if (isset($_POST['propietarios']) && is_array($_POST['propietarios'])) {
            $sql_prop = "INSERT INTO certificado_propietarios (
                prop_cod_certificado,
                npn_predio_certificado,
                nombres_propietario,
                tipo_doc_propietario,
                cc_num_propietario
            ) VALUES (?, ?, ?, ?, ?)";
            
            $stmt_prop = $conn->prepare($sql_prop);

            foreach ($_POST['propietarios'] as $prop) {
                $nombre   = $prop['nombre'] ?? '';
                $tipo_doc = $prop['tipo_doc'] ?? '';
                $num_doc  = $prop['numero_doc'] ?? '';

                // Usamos el codigo_certificado que acabamos de generar
                $stmt_prop->bind_param(
                    "sssss",
                    $codigo_certificado,
                    $npn_predio_tramite,
                    $nombre,
                    $tipo_doc,
                    $num_doc
                );
                $stmt_prop->execute();
            }
        }
        // ============================
        // 🔹 Guardar soporte de pago
        // ============================
        if (isset($_FILES['cert_sopor_pago']) && $_FILES['cert_sopor_pago']['error'] == UPLOAD_ERR_OK) {
            // Carpeta base relativa a este script
            $directorio_base =  "soportes_pago/" . $codigo_certificado . "/";

            // Crear carpeta si no existe
            if (!is_dir($directorio_base)) {
                mkdir($directorio_base, 0777, true);
            }

            // Nombre final del archivo
            $nombreArchivo = basename($_FILES['cert_sopor_pago']['name']);
            $rutaDestino = $directorio_base . $nombreArchivo;

            if (move_uploaded_file($_FILES['cert_sopor_pago']['tmp_name'], $rutaDestino)) {
                // Guardar ruta relativa en la BD (ej: soportes_pago/CERT-2025-01/archivo.pdf)
                $rutaRelativa = "soportes_pago/" . $codigo_certificado . "/" . $nombreArchivo;

                $sql_update_soporte = "UPDATE certificado_catastral 
                                    SET cert_soporte_pago = ? 
                                    WHERE certificado_id = ?";
                $stmt_update_soporte = $conn->prepare($sql_update_soporte);
                $stmt_update_soporte->bind_param("si", $rutaRelativa, $id_certificado);
                $stmt_update_soporte->execute();
            }
        }


        // ============================
        // 🔹 Mensaje con SweetAlert
        // ============================
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
                    Generando Certificado
                </h4>
            </div>
            <div class='card-body text-center py-5'>
                <!-- Loader personalizado -->
                <div class='loader-container mb-4'>
                    <div class='custom-loader'></div>
                </div>
                
                <h5 class='text-dark mb-3'>Procesando información...</h5>
                <p class='text-muted mb-4'>Por favor espere mientras se genera el certificado.</p>
                
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
        }, 2500);

        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: '¡Certificado Generado!',
                text: 'El certificado ha sido generado satisfactoriamente.',
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
                window.location.href = 'index.php?page=cert_catastrales/consulta_cert_catastrales';
            });
        }, 4000);
    };
    </script>
EOT;
    } else {
        echo "❌ Error en certificado: " . $stmt->error;
    }
} // <-- cierre final de if POST
?>
