<?php
require_once dirname(__DIR__, 3) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 3) . '/config/permisos.php';
require_once dirname(__DIR__, 4) . '/conexion.php';

neiva_bootstrap();
neiva_require_methods('POST');
neiva_require_permission('menu.seguimiento', $PERMISOS);
neiva_require_csrf('global');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
date_default_timezone_set('America/Bogota');


// Conexión
$conn = $mysqli;
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$cod_tramite = $_GET['asignacion_cod_tramite'] ?? '';
$rol_actual  = $_SESSION['rol_usuario'] ?? '';
if (!$rol_actual) {
    die("No se ha definido el rol del usuario en la sesión.");
}




try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $rol_actual = $_POST['rol_actual'] ?? $rol_actual;


        // === DATOS DEL POST ===
        $entrega_cod_tramite            = $_POST['entrega_cod_tramite'] ?? '';
        $historial_fecha_tramite        = $_POST['historial_fecha_tramite'] ?? date('Y-m-d H:i:s');
        $observacion_a_usuario_tramite  = $_POST['observacion'] ?? '';
        $fecha_limite                   = $_POST['fecha_limite'] ?? null;
        $creacion_tram_cc_usuario       = $_POST['entrega_cc_usuario'] ?? ($_POST['creacion_tram_cc_usuario'] ?? '');
        $creacion_tram_nombre_usuario   = $_POST['entrega_nombre_usuario'] ?? ($_POST['creacion_tram_nombre_usuario'] ?? '');
        $creacion_tram_apellido_usuario = $_POST['entrega_apellido_usuario'] ?? ($_POST['creacion_tram_apellido_usuario'] ?? '');
        $creacion_tram_rol_usuario      = $_POST['entrega_rol_usuario'] ?? ($_POST['creacion_tram_rol_usuario'] ?? '');
        $motivo_devolucion              = $_POST['motivo_devolucion'] ?? '';


        // Reusar las mismas para devolucion
        $devolucion_tram_cc_usuario         = $creacion_tram_cc_usuario;
        $devolucion_tram_nombre_usuario     = $creacion_tram_nombre_usuario;
        $devolucion_tram_apellido_usuario   = $creacion_tram_apellido_usuario;
        $devolucion_tram_rol_usuario        = $creacion_tram_rol_usuario;


        if (!$entrega_cod_tramite) {
            die("Faltan datos obligatorios (código trámite).");
        }

        // === DATOS DE SESIÓN ===
        $cedula_sesion   = $_SESSION['cedula_usuario'] ?? '';
        $nombre_sesion   = $_SESSION['nombre_usuario'] ?? '';
        $apellido_sesion = $_SESSION['apellido_usuario'] ?? '';


        // Extraer año desde el código
        $anio = (strlen($entrega_cod_tramite) >= 8) ? substr($entrega_cod_tramite, 4, 4) : date('Y');


        // === Crear carpeta base ===
        $ruta_base = "../../../../tramites_conservacion/$anio/$entrega_cod_tramite/tramites_devolucion/$creacion_tram_rol_usuario";
        if (!file_exists($ruta_base)) {
            mkdir($ruta_base, 0777, true);
        }

        // === Manejar documento soporte (opcional) ===
        $ruta_soporte = null;
        if (isset($_FILES['documento_soporte']) && $_FILES['documento_soporte']['error'] === UPLOAD_ERR_OK) {
            // Para evitar colisiones, agrega timestamp o id único al archivo
            $nombre_archivo = time() . "_" . basename($_FILES['documento_soporte']['name']);

            // Ruta física donde se va a guardar el archivo
            $ruta_fisica = $ruta_base . "/" . $nombre_archivo;

            // Ruta relativa (la que guardamos en BD y usaremos en enlaces)
            $ruta_bd = "tramites_conservacion/$anio/$entrega_cod_tramite/tramites_devolucion/$creacion_tram_rol_usuario/" . $nombre_archivo;

            if (move_uploaded_file($_FILES['documento_soporte']['tmp_name'], $ruta_fisica)) {
                $ruta_soporte = $ruta_bd; // <- guardamos solo la ruta en la BD
            }
        }


        // === Buscar el id_tramite_fk (trámite padre) ===
        $stmt_asig = $conn->prepare("
            SELECT asignacion_id_tramite 
            FROM asignacion_tramite 
            WHERE asignacion_cod_tramite = ?
            ORDER BY asignacion_id_tramite DESC 
            LIMIT 1
        ");
        $stmt_asig->bind_param("s", $entrega_cod_tramite);
        $stmt_asig->execute();
        $stmt_asig->bind_result($id_tramite_fk);
        $stmt_asig->fetch();
        $stmt_asig->close();

        if (!$id_tramite_fk) {
            die("No se encontró el trámite padre para el código: $entrega_cod_tramite");
        }

        // ========================
        // INSERTAR EN devolucion_tramites
        // ========================



        $sql = "INSERT INTO devolucion_tramites (
            historial_cod_tramite,
            motivo_devolucion,
            historial_fecha_tramite,
            creacion_tram_cc_usuario,
            creacion_tram_nombre_usuario,
            creacion_tram_apellido_usuario,
            creacion_tram_rol_usuario,
            asignacion_cc_usuario,
            asignacion_nombre_usuario,
            asignacion_apellido_usuario,
            asignacion_rol_usuario,
            rol_actual,
            cedula_sesion,
            nombre_sesion,
            apellido_sesion,
            fecha_limite,
            historial_estado_tramite,
            documento_soporte
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $historial_cod_tramite    = $entrega_cod_tramite;
        $historial_estado_tramite = 'DEVUELTO';


        // Preparar valores de asignación para todos los roles
        $asignacion_cc_usuario = $creacion_tram_cc_usuario;
        $asignacion_nombre_usuario = $creacion_tram_nombre_usuario;
        $asignacion_apellido_usuario = $creacion_tram_apellido_usuario;
        $asignacion_rol_usuario = $creacion_tram_rol_usuario;

        // Usamos 's' para la mayoría de campos para evitar problemas de tipado; MySQL hará la conversión
        $stmt->bind_param(
            str_repeat('s', 18),
            $historial_cod_tramite,       // 1
            $motivo_devolucion,           // 2
            $historial_fecha_tramite,     // 3
            $creacion_tram_cc_usuario,    // 4
            $creacion_tram_nombre_usuario, // 5
            $creacion_tram_apellido_usuario, //6
            $creacion_tram_rol_usuario,   //7
            $asignacion_cc_usuario,       //8
            $asignacion_nombre_usuario,   //9
            $asignacion_apellido_usuario, //10
            $asignacion_rol_usuario,      //11
            $rol_actual,                  //12
            $cedula_sesion,               //13
            $nombre_sesion,               //14
            $apellido_sesion,             //15
            $fecha_limite,                //16
            $historial_estado_tramite,    //17
            $ruta_soporte                 //18
        );



        $stmt->execute();
        $stmt->close();

        // ========================
        // INSERTAR EN estados_tramite
        // ========================
        $sql_estado = "INSERT INTO estados_tramite (
            es_nombre, es_tipo, es_descripcion, es_dias_disparador,
            es_rol_asociado, estado, asignacion_id, cod_tramite
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_estado = $conn->prepare($sql_estado);

        $es_nombre = 'DEVUELTO';
        $es_tipo = 'automatico';
        $es_descripcion = "Trámite devuelto por $rol_actual";
        $es_dias_disparador = 5;
        $estado = 'ACTIVO';

        $stmt_estado->bind_param(
            "sssissis",
            $es_nombre,
            $es_tipo,
            $es_descripcion,
            $es_dias_disparador,
            $creacion_tram_rol_usuario, // el rol que creó el trámite recibe la devolución
            $estado,
            $id_tramite_fk,
            $historial_cod_tramite
        );
        $stmt_estado->execute();
        $stmt_estado->close();

        // === Actualizar historial_revision según rol actual ===

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


        $rol_que_recibe   = $entrega_rol_usuario   ?? null;
        $cedula           = $entrega_cc_usuario    ?? null;
        $nombre_usuario   = $entrega_nombre_usuario ?? null;
        $apellido_usuario = $entrega_apellido_usuario ?? null;
        $fecha_limite_rol = $fecha_limite ?? null;
    }
    if (isset($mapa_estados[$rol_actual])) {
        [$campo_estado, $campo_fecha] = $mapa_estados[$rol_actual];
        $fecha_aprobacion = date('Y-m-d H:i:s');


        $update = $conn->prepare("UPDATE historial_revision 
        SET $campo_estado = 'DEVUELTO',
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
            $creacion_tram_rol_usuario,
            $creacion_tram_cc_usuario,
            $creacion_tram_nombre_usuario,
            $creacion_tram_apellido_usuario,
            $fecha_limite,
            $entrega_cod_tramite

        );
        $update->execute();
        $update->close();
    }


    // Verificar si ya existe historial_devolucion para ese trámite
    $check = $conn->prepare("SELECT COUNT(*) FROM historial_devolucion WHERE historial_cod_tramite = ?");
    $check->bind_param("s", $entrega_cod_tramite);
    $check->execute();
    $check->bind_result($existe);
    $check->fetch();
    $check->close();

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

        $sql_historial = "INSERT INTO historial_devolucion (
    historial_cod_tramite,
    entrega_id_tramite,
    observacion_a_usuario_tramite,
    historial_fecha_tramite,
    cedula_sesion,
    nombre_sesion,
    apellido_sesion,
    devolucion_tram_cc_usuario,
    devolucion_tram_nombre_usuario,
    devolucion_tram_apellido_usuario,
    devolucion_tram_rol_usuario,
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
) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $stmt_historial = $conn->prepare($sql_historial);
        if (!$stmt_historial) {
            die("Error en prepare: " . $conn->error);
        }

        // Cambié $entrega_* → $creacion_tram_*
        $stmt_historial->bind_param(
            "sissississsssssssssssssss",
            $entrega_cod_tramite,
            $id_tramite_fk,
            $observacion_a_usuario_tramite,
            $historial_fecha_tramite,
            $cedula_sesion,
            $nombre_sesion,
            $apellido_sesion,
            $devolucion_tram_cc_usuario,
            $devolucion_tram_nombre_usuario,
            $devolucion_tram_apellido_usuario,
            $devolucion_tram_rol_usuario,
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
            die("Error al ejecutar inserción en historial_devolucion: " . $stmt_historial->error);
        }
        $stmt_historial->close();


        // === Actualizar historial_devolucion según rol actual ===

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

        // === Definir variables de entrega para actualizar historial_revision ===
        $entrega_rol_usuario   = $creacion_tram_rol_usuario;
        $entrega_cc_usuario    = $creacion_tram_cc_usuario;
        $entrega_nombre_usuario   = $creacion_tram_nombre_usuario;
        $entrega_apellido_usuario = $creacion_tram_apellido_usuario;



        $rol_que_recibe   = $entrega_rol_usuario;
        $cedula           = $entrega_cc_usuario;
        $nombre_usuario   = $entrega_nombre_usuario;
        $apellido_usuario = $entrega_apellido_usuario;
        $fecha_limite_rol = $fecha_limite;

        if (isset($mapa_estados[$rol_actual]) && isset($mapa_estados[$rol_que_recibe])) {
            [$campo_estado_actual, $campo_fecha_actual] = $mapa_estados[$rol_actual];
            [$campo_estado_destino, $campo_fecha_destino] = $mapa_estados[$rol_que_recibe];

            $fecha_aprobacion = date('Y-m-d H:i:s');

            // Armamos el UPDATE dinámico
            $sql = "UPDATE historial_revision 
        SET $campo_estado_actual = 'SUBSANADO',
            $campo_fecha_actual = ?,
            $campo_estado_destino = 'PENDIENTE',
            $campo_fecha_destino = ?,
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
        WHERE historial_cod_tramite = ?";


            $update = $conn->prepare($sql);

            $update->bind_param(
                "sssssssssisss",
                $fecha_aprobacion,
                $fecha_aprobacion,   
                $rol_actual,
                $creacion_tram_rol_usuario,
                $creacion_tram_cc_usuario,
                $creacion_tram_nombre_usuario,
                $creacion_tram_apellido_usuario,
                $creacion_tram_rol_usuario,
                $creacion_tram_cc_usuario,
                $creacion_tram_nombre_usuario,
                $creacion_tram_apellido_usuario,
                $fecha_limite,
                $entrega_cod_tramite
            );

            $update->execute();
            $update->close();
        }
    } else {
        // Si ya existía un historial, devolvemos error
    }
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo "Error MySQLi: " . $e->getMessage();
}

$conn->close();
