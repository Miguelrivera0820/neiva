<?php
// session_start();
// require '../../conexion.php';
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
$ruta_pdf = "tramites_conservacion/2025/";
$sql2 = "SELECT * FROM tramite_info_predio WHERE info_cod_tramite = ?";
$stmt2 = $mysqli->prepare($sql2);
$stmt2->bind_param("s", $info_cod_tramite);
$stmt2->execute();
$resultado2 = $stmt2->get_result();
$info_predio = $resultado2->fetch_assoc();
$cod = $_GET['cod'] ?? null;



// FUNCION Y DEFINICIÓN PARA ESTABLECER DEPENDIENDO QUE ROL PUEDE ASIGNAR A QUE OTRO ROL
// EJEMPLO: ventanilla_catastral puede asignar a atencion_procedencia 
//Esto va a evitar que alguien permita asignar un trámite a un rol que no le corresponde y se pierda informacion o trazabilidad del trámite
$roles_por_rol = [
    "ventanilla_catastral" => ["procedencia_juridica"],
    "procedencia_juridica" => ["coordinacion_tecnica", "revision_juridica"],
    "coordinacion_tecnica" => ["control_calidad", "componente_economico", "revision_juridica", "director_catastro"],
    "revision_juridica" => ["control_calidad"],

    //ASIGNACION A VISITA CAMPO
    "control_calidad" => ["consolidacion"],
    "consolidacion" => ["editor"],
    "editor" => ["reconocedor"],
    "reconocedor" => [""],

    //ASIGNACION A VISITA ECONOMICA O AVALUO
    "componente_economico" => ["avaluos"],
    "avaluos" => ["reconocedor"],

    //CIERRE TRAMITE
    "director_catastro" => ["ventanilla_catastral"],

    //REVISIÓN JURIDICA
    "procedencia_juridica" => ["coordinacion_tecnica", "revision_juridica"],

    //ATENCION PROCEDENCIA
    "atencion_procedencia" => ["coordinacion_tecnica"],

    //ASIGNACION A TRAMITE DE ADMINISTRIDADOR
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

// Se obtiene el ID de usuario de la sesión
$idUsuario = $_SESSION['id_usuario'];
$roles_disponibles = $roles_por_rol[$rol_usuario];
$rol_usuario = $_SESSION['rol_usuario'] ?? '';
$roles_disponibles = $roles_por_rol[$rol_usuario] ?? [];
// Consulta para obtener el rol del usuario
$sql = "SELECT  rol_usuario FROM usuarios_cons WHERE id_usuario = $idUsuario";
$resultado = $mysqli->query($sql);
// Consulta para obtener el rol del usuario
$sql = "SELECT rol_usuario FROM usuarios_cons WHERE id_usuario = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$stmt->get_result();
// ✅ Obtener asignacion_rol_usuario desde asignacion_tramite
$asignacion_rol_usuario = null;
$sqlRol = "SELECT asignacion_rol_usuario 
           FROM asignacion_tramite 
           WHERE asignacion_cod_tramite = ? 
           LIMIT 1";
$stmtRol = $mysqli->prepare($sqlRol);

if ($stmtRol) {
    $stmtRol->bind_param("s", $cod_tramite);
    $stmtRol->execute();
    $resRol = $stmtRol->get_result();

    if ($rowRol = $resRol->fetch_assoc()) {
        $asignacion_rol_usuario = $rowRol['asignacion_rol_usuario'];
    }
}

$cedula_usuario = $_SESSION['cedula_usuario'] ?? '';
$cod_tramite    = $_GET['cod'] ?? '';
$entrega_cc_usuario = null;
$entrega_nombre_usuario = null;
$entrega_apellido_usuario = null;
$entrega_rol_usuario = null;
$observacion_a_usuario_tramite = null;
if ($cedula_usuario && $cod_tramite) {
    // Buscar en asignacion_tramite quién me asignó
    $sqlBuscar = "SELECT creacion_tram_cc_usuario, 
                         creacion_tram_nombre_usuario, 
                         creacion_tram_apellido_usuario, 
                         creacion_tram_rol_usuario
                  FROM asignacion_tramite
                  WHERE asignacion_cod_tramite = ?
                  AND asignacion_cc_usuario = ?";
    $stmtBuscar = $mysqli->prepare($sqlBuscar);
    $stmtBuscar->bind_param("ss", $cod_tramite, $cedula_usuario);
    $stmtBuscar->execute();
    $resultado = $stmtBuscar->get_result();

    if ($row = $resultado->fetch_assoc()) {
        // Guardar en variables (listas para pasar al otro archivo)
        $entrega_cc_usuario       = $row['creacion_tram_cc_usuario'];
        $entrega_nombre_usuario   = $row['creacion_tram_nombre_usuario'];
        $entrega_apellido_usuario = $row['creacion_tram_apellido_usuario'];
        $entrega_rol_usuario      = $row['creacion_tram_rol_usuario'];
    }
}

$creacion_tram_cc_usuario = null;
$creacion_tram_nombre_usuario = null;
$creacion_tram_apellido_usuario = null;
$creacion_tram_rol_usuario = null;

if ($cedula_usuario && $cod_tramite) {
    // Buscar en asignacion_tramite quién me creo
    $sqlBuscarAsig = "SELECT asignacion_cc_usuario, 
                             asignacion_nombre_usuario, 
                             asignacion_apellido_usuario, 
                             asignacion_rol_usuario
                      FROM asignacion_tramite
                      WHERE asignacion_cod_tramite = ?
                        AND creacion_tram_cc_usuario = ?";

    $stmtBuscarAsig = $mysqli->prepare($sqlBuscarAsig);
    $stmtBuscarAsig->bind_param("ss", $cod_tramite, $cedula_usuario);
    $stmtBuscarAsig->execute();
    $resultado = $stmtBuscarAsig->get_result();

    if ($row = $resultado->fetch_assoc()) {
        // Guardar en variables de creación
        $creacion_tram_cc_usuario       = $row['asignacion_cc_usuario'];
        $creacion_tram_nombre_usuario   = $row['asignacion_nombre_usuario'];
        $creacion_tram_apellido_usuario = $row['asignacion_apellido_usuario'];
        $creacion_tram_rol_usuario      = $row['asignacion_rol_usuario'];
    }
}

$sqlBuscar = "SELECT observacion_a_usuario_tramite
              FROM entrega_asignacion
              WHERE entrega_cod_tramite = ?
              ORDER BY id_entrega_asignacion DESC
              LIMIT 1";

$stmtBuscar = $mysqli->prepare($sqlBuscar);
if (!$stmtBuscar) {
    die("Error en la consulta: " . $mysqli->error);
}

// Usa $cod_tramite directamente
$stmtBuscar->bind_param("s", $cod_tramite);
$stmtBuscar->execute();
$resultObs = $stmtBuscar->get_result();

$observacion_a_usuario_tramite = '';
if ($row = $resultObs->fetch_assoc()) {
    $observacion_a_usuario_tramite = $row['observacion_a_usuario_tramite'];
}

$stmtBuscar->close();




// Trae los documentos que subió QUIEN TE PASÓ el trámite
// Verifica si tienes el rol del que pasó el trámite
// Trae los documentos que subió QUIEN TE PASÓ el trámite
if (!empty($creacion_tram_cc_usuario)) {
    $stmt = $mysqli->prepare("
        SELECT 
            tipo_doc1, doc1,
            tipo_doc2, doc2,
            tipo_doc3, doc3,
            tipo_doc4, doc4,
            tipo_doc5, doc5
        FROM doc_entrega_asignacion
        WHERE cod_tramite = ?
          AND doc_cedula_usuario = ?
        ORDER BY id_doc_entrega DESC
        LIMIT 1
    ");

    // cod_tramite = string, doc_cedula_usuario = int
    $stmt->bind_param("si", $cod_tramite, $creacion_tram_cc_usuario);
    $stmt->execute();
    $docs = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

?>
<style>
    .btn-ver:hover {
        background-color: #022F55;
        color: white;
    }

    .btn-verr:hover {
        background-color: #022F55;
        color: white !important;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- CONTENIDO PAGINA MODIFICACION -->
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4 px-4">
        <h1 class="h3 mb-0 fw-bold">ASIGNACIÓN DE TRÁMITES</h1>

        <a href="index.php?page=tramites/acciones/ver_tramite_rad&cod=<?php echo urlencode($tramite['cod_tramite']); ?>"
            class="btn  btn-md shadow-lg animated-button fw-bold btn-ver"
            style="border-left:2px solid #022F55;border-right:2px solid #022F55;">
            <i class="bi bi-eye me-2 fw-bold "></i> Ver Radicación
        </a>
    </div>
    <!-- PRUEBA MODAL DE PROCEDENCIA - INTENTO 1
                        MOSTRAR INFORMACION DE CARGA POR USUARIOS ANTERIORES -->
    <div class="modal fade" id="modalHistorial" tabindex="-1" aria-labelledby="modalHistorialLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalHistorialLabel">Historial del trámite</h5>
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
        <div class="col-12">
            <div class="card shadow mb-4 p-3  text-center">

                <div style="background-color: #022F55;" class="rounded-3">
                    <h5 class="text-white text-center py-2 m-0">DATOS DEL USUARIO ORIGINADOR</h5>
                </div>

                <div class="card-body">
                    <form id="miFormulario" action="index.php?page=seguimiento/acciones/procesar_entrega_segun_rol" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <!--  CAMBIAR EL MODO DE ID, DESACTIVAR PORQUE DEBE SER AUTOMATICO -->
                            <div class="form-row">

                                <!-- <div class="col-md-6">
                                    <label for="cod_tramite"><b>ID_Radicacion</b></label>
                                    <input class="form-control py-4" id="cod_tramite" name="entrega_cod_tramite" type="text"
                                        value="<?php echo htmlspecialchars($tramite['cod_tramite']); ?>" readonly>
                                </div> -->

                                <div class="col-md-4 p-1 px-2 my-2">
                                    <label for="cod_tramite" class="form-label fw-bold" style="font-size:0.9em;">ID de radicación</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-file-earmark-binary"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="cod_tramite"
                                            name="entrega_cod_tramite"
                                            value="<?php echo htmlspecialchars($tramite['cod_tramite']); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="fecha_rad"><b>Hora Radicación del Trámite</b></label>
                                    <input class="form-control py-4" id="fecha_rad" name="historial_fecha_tramite" type="text"
                                        value="<?php echo htmlspecialchars($tramite['fecha_rad']); ?>" readonly>
                                </div> -->

                                <div class="col-md-4 p-1 px-2 my-2">
                                    <label for="fecha_rad" class="form-label fw-bold" style="font-size:0.9em;">Hora radicación del trámite</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                                        <input type="text" class="form-control" style="font-size:0.9em;" id="fecha_rad"
                                            name="historial_fecha_tramite"
                                            value="<?php echo htmlspecialchars($tramite['fecha_rad']); ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="nombre_usuario"><b>Cedula del Usuario</b></label>
                                    <input class="form-control" id="creacion_tram_cc_usuario" name="creacion_tram_cc_usuario" type="text" value="<?php echo $creacion_tram_cc_usuario; ?>" readonly>
                                </div> -->

                                <div class="col-md-4 p-1 px-2 my-2">
                                    <label for="creacion_tram_cc_usuario" class="form-label fw-bold" style="font-size:0.9em">Cédula del usuario</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="creacion_tram_cc_usuario" name="creacion_tram_cc_usuario"
                                            value="<?php echo $creacion_tram_cc_usuario; ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="nombre_usuario"><b>Nombres del Usuario</b></label>
                                    <input class="form-control" id="creacion_tram_nombre_usuario" name="creacion_tram_nombre_usuario" type="text" value="<?php echo $creacion_tram_nombre_usuario; ?>" readonly>
                                </div> -->

                                <div class="col-md-4 p-1 px-2 my-2">
                                    <label for="creacion_tram_nombre_usuario" class="form-label fw-bold" style="font-size:0.9em">Nombres del usuario</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="creacion_tram_nombre_usuario" name="creacion_tram_nombre_usuario"
                                            value="<?php echo $creacion_tram_nombre_usuario; ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="apellido_usuario"><b>Apellidos del Usuario</b></label>
                                    <input class="form-control" id="creacion_tram_apellido_usuario" name="creacion_tram_apellido_usuario" type="text" value="<?php echo $creacion_tram_apellido_usuario; ?>" readonly>
                                </div> -->

                                <div class="col-md-4 p-1 px-2 my-2">
                                    <label for="creacion_tram_apellido_usuario" class="form-label fw-bold" style="font-size:0.9em">Apellidos del usuario</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi-people-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="creacion_tram_apellido_usuario" name="creacion_tram_apellido_usuario"
                                            value="<?php echo $creacion_tram_apellido_usuario; ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="nombre_usuario"><b>Cargo</b></label>
                                    <input class="form-control" id="creacion_tram_rol_usuario" name="creacion_tram_rol_usuario" type="text" value="<?php echo $creacion_tram_rol_usuario; ?>" readonly>
                                </div> -->

                                <div class="col-md-4 p-1 px-2 my-2">
                                    <label for="creacion_tram_rol_usuario" class="form-label fw-bold" style="font-size:0.9em">Cargo</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-gear"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="creacion_tram_rol_usuario" name="creacion_tram_rol_usuario"
                                            value="<?php echo $creacion_tram_rol_usuario; ?>" readonly>
                                    </div>
                                </div>

                                <div class="col-md-12 p-1 px-2 m-2">
                                    <div class="row justify-content-center">
                                        <?php
                                        // Obtener documentos de la base de datos
                                        $cod_tramite = $tramite['cod_tramite'];
                                        $sql_docs = "SELECT * FROM doc_entrega_asignacion WHERE cod_tramite = ?";
                                        $stmt = $mysqli->prepare($sql_docs);
                                        $stmt->bind_param("s", $cod_tramite);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        $docs = $result->fetch_assoc();

                                        // Contar documentos disponibles
                                        $total_docs = 0;
                                        if ($docs) {
                                            for ($i = 1; $i <= 5; $i++) {
                                                if (!empty($docs["doc$i"])) {
                                                    $total_docs++;
                                                }
                                            }
                                        }

                                        // Determinar clase de columna según cantidad de documentos
                                        $col_class = ($total_docs == 1) ? 'col-12 col-md-8 col-lg-6' : 'col-12 col-md-6';
                                        ?>

                                        <?php if ($docs && $total_docs > 0): ?>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?php if (!empty($docs["doc$i"])): ?>
                                                    <div class="<?= $col_class ?> p-2">
                                                        <div class="card card-documentos shadow h-100 p-3 border d-flex flex-column text-center">
                                                            <!-- <label><b>Documentos Adjuntos</b></label> -->

                                                            <!-- <p class="mb-1">
                                                                <b>Documento <?= $i ?>:</b> <?= htmlspecialchars($docs["tipo_doc$i"] ?? "Sin descripción") ?>
                                                            </p> -->
                                                            <label for="npn_predio" class="form-label fw-bold">Documentos Adjuntos</label>
                                                            <div class="input-group shadow-sm mb-2">
                                                                <span class="input-group-text"> <i class="bi bi-file-earmark-pdf-fill me-2"></i> <b>Documento <?= $i ?>:</b> </span>
                                                                <input type="text" class="form-control" style="font-size: 0.9em;" id="npn_predio" name="npn_predio"
                                                                    value="<?= htmlspecialchars($docs["tipo_doc$i"] ?? "Sin descripción") ?> " readonly>
                                                            </div>

                                                            <div class="d-flex justify-content-center gap-2 px-4 my-2">
                                                                <a href="<?= $docs["doc$i"] ?>" target="_blank" class="bot_verenotrapesta btn btn-sm">
                                                                    <i class="bi bi-box-arrow-right"></i> Ver en otra pestaña
                                                                </a>
                                                                <button type="button" class="bot_mostrar_vista btn btn-sm"
                                                                    onclick="toggleIframe('visor_doc<?= $i ?>',this)">
                                                                    <i class="bi bi-eye"></i> <span>Mostrar Vista Previa</span>
                                                                </button>
                                                            </div>

                                                            <div id="visor_doc<?= $i ?>" class="iframe-animado">
                                                                <iframe src="<?= $docs["doc$i"] ?>" width="100%" height="750px" style="border:1px solid #ccc;"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        <?php else: ?>
                                            <div class="col-12 text-center">
                                                <span class="text-muted">No hay documentos cargados</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-12 form-group">
                                    <label for="observacion_asignacion" class="my-2"><b>Observaciones de la entrega</b></label>
                                    <textarea class="form-control text-center d-flex "
                                        id="observacion_asignacion"
                                        name="observacion_asignacion"
                                        rows="3"
                                        style="background-color:#ff7e3626; color:#333;"
                                        disabled><?php echo $observacion_a_usuario_tramite; ?></textarea>
                                </div>

                            </div>

                        </div>


                        <!-- Tramite a quien le voy a entregar -->
                        <div style="background-color: #022F55;" class="rounded-3 my-3">
                            <h5 class="text-white text-center py-2 m-0">DESTINATARIO DE LA ASIGNACIÓN PARA REVISIÓN</h5>
                        </div>

                        <div class="form-group">
                            <!--  Datos del usuario que me asigno en asignacion_tramite -->

                            <div class="form-row">
                                <!-- <div class="col-md-6">
                                    <label for="entrega_cc_usuario"><b>Cédula del Usuario</b></label>
                                    <input class="form-control"
                                        id="entrega_cc_usuario"
                                        name="entrega_cc_usuario"
                                        type="text"
                                        value="<?php echo $entrega_cc_usuario ?? ''; ?>"
                                        readonly>
                                </div> -->

                                <div class="col-md-4 p-1 px-2 my-2">
                                    <label for="entrega_cc_usuario" class="form-label fw-bold" style="font-size:0.9em">Cédula del usuario</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="entrega_cc_usuario" name="entrega_cc_usuario"
                                            value="<?php echo $entrega_cc_usuario ?? ''; ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="entrega_nombre_usuario"><b>Nombre del Usuario</b></label>
                                    <input class="form-control"
                                        id="entrega_nombre_usuario"
                                        name="entrega_nombre_usuario"
                                        type="text"
                                        value="<?php echo $entrega_nombre_usuario ?? ''; ?>"
                                        readonly>
                                </div> -->

                                <div class="col-md-4 p-1 px-2 my-2">
                                    <label for="entrega_nombre_usuario" class="form-label fw-bold" style="font-size:0.9em">Nombres del usuario</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="entrega_nombre_usuario" name="entrega_nombre_usuario"
                                            value="<?php echo $entrega_nombre_usuario ?? ''; ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="entrega_apellido_usuario"><b>Apellidos del Usuario</b></label>
                                    <input class="form-control"
                                        id="entrega_apellido_usuario"
                                        name="entrega_apellido_usuario"
                                        type="text"
                                        value="<?php echo $entrega_apellido_usuario ?? ''; ?>"
                                        readonly>
                                </div> -->

                                <div class="col-md-4 p-1 px-2 my-2">
                                    <label for="entrega_apellido_usuario" class="form-label fw-bold" style="font-size:0.9em">Apellidos del usuario</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi-people-fill"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="entrega_apellido_usuario" name="entrega_apellido_usuario"
                                            value="<?php echo $entrega_apellido_usuario ?? ''; ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="entrega_rol_usuario"><b>Cargo del Usuario</b></label>
                                    <input class="form-control"
                                        id="entrega_rol_usuario"
                                        name="entrega_rol_usuario"
                                        type="text"
                                        value="<?php echo $entrega_rol_usuario ?? ''; ?>"
                                        readonly>
                                </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="entrega_rol_usuario" class="form-label fw-bold" style="font-size:0.9em">Cargo del usuario</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-gear"></i></span>
                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="entrega_rol_usuario" name="entrega_rol_usuario"
                                            value="<?php echo $entrega_rol_usuario ?? ''; ?>" readonly>
                                    </div>
                                </div>

                                <!-- <div class="col-md-6">
                                    <label for="fecha_limite"><b>Fecha Limite Respuesta al siguiente área</b></label>
                                    <input type="date" class="form-control" id="fecha_limite" name="fecha_limite" value="<?php echo date('Y-m-d', strtotime('+4 days')); ?>" readonly>
                                </div> -->

                                <div class="col-md-6 p-1 px-2 my-2">
                                    <label for="fecha_limite" class="form-label fw-bold" style="font-size:0.9em; ">Fecha límite de respuesta a siguiente área</label>
                                    <div class="input-group shadow-sm ">
                                        <span class="input-group-text text-white" style="background-color: #022F55; border:1px solid #022F55"><i class="bi bi-calendar2-x"></i></span>
                                        <input type="date" style="border:1px solid #022F55" id="fecha_limite" class="form-control" name="fecha_limite" value="<?php echo date('Y-m-d', strtotime('+4 days')); ?>" readonly>
                                    </div>
                                </div>

                            </div><br>

                            <div class="col-md-12 my-4" style="border-bottom: 1px solid #02305561;"></div>



                            <!-- Contenedor principal de los documentos -->
                            <div id="documentosContainer" class="px-5">
                                <div class="form-row documento-group ">
                                    <div class="col-md-12 form-group my-3 text-center align-center">
                                        <button type="button" class="btn btn-verr" id="agregarDocumento" style="border:1px solid #022F55; color:#022F55;">
                                            <i class="bi bi-file-earmark-plus"></i> Agregar otros documentos
                                        </button>
                                    </div>

                                    <div class="col-md-6 p-1 px-2 my-1 ">
                                        <label for="tipo_doc1" class="form-label" style="font-size:0.9em;"><b>Tipo de documento</b></label>
                                        <div class="input-group shadow-sm">
                                            <label class="input-group-text" for="tipo_doc1">
                                                <i class="bi bi-file-earmark-text-fill"></i>
                                            </label>
                                            <select class="form-select" style="font-size:0.9em;" id="tipo_doc1" name="tipo_doc1" required>
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
                                        <label for="nombre_doc1" class="form-label fw-bold" style="font-size:0.9em;">Documentos a cargar</label>
                                        <div class="input-group  shadow-sm">
                                            <label class="input-group-text" for="nombre_doc1" style="font-size:0.9em;"><i class="bi bi-file-earmark-pdf-fill"></i></label>
                                            <input type="file" class="form-control" style="font-size:0.8em;" id="nombre_doc1" name="nombre_doc1">
                                        </div>
                                        <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                                    </div>

                                </div>
                            </div>

                            <!-- Contenedor principal de los documentos -->
                            <!-- <div id="documentosContainer">
                                <div class="form-row documento-group">
                                    <div class="col-md-6">
                                        <label><b>Tipo de Documento</b></label>
                                        <select class="custom-select" name="tipo_doc1" required>
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
                                    <div class="col-md-6">
                                        <label><b>Documento a Cargar</b></label>
                                        <input type="file" class="form-control-file" name="nombre_doc1" required>
                                    </div>
                                </div>
                            </div> -->

                            <div class="col-md-12 my-4" style="border-bottom: 1px solid #02305561;"></div>

                            <div class="form-group mx-3">
                                <label for="observacion_asignacion" class="mb-2"><b>Observaciones para la entrega</b></label>
                                <textarea class="form-control " id="observacion_asignacion" name="observacion_asignacion"
                                    placeholder="Ingrese una descripción/observación para informar al usuario correspondiente"></textarea>
                            </div>

                            <input type="hidden" id="historial_cc_usuario" name="historial_cc_usuario"
                                value="<?php echo htmlspecialchars($historial_cc_usuario); ?>">

                            <input type="hidden" id="cedula" name="cedula">
                            <input type="hidden" id="rol_usuario" name="rol_usuario">
                            <input type="hidden" id="asignacion_rol_usuario" name="asignacion_rol_usuario"
                                value="<?php echo htmlspecialchars($asignacion_rol_usuario); ?>">
                            <input type="hidden" id="historial_nombre_usuario" name="historial_nombre_usuario">
                            <input type="hidden" id="historial_apellido_usuario" name="historial_apellido_usuario">

                            <div class="form-group mt-4 mb-0">
                                <div class="d-flex justify-content-center">
                                    <!-- Botón Aprobar -->
                                    <button type="submit" name="accion" value="aprobar"
                                        class="btn btn-success px-4">
                                        <i class="bi bi-bookmark-check-fill"></i> APROBAR
                                    </button>

                                    <script>
                                        document.getElementById("btnAprobar").addEventListener("click", function() {
                                            let cod = new URLSearchParams(window.location.search).get("cod");

                                            // Guardar de forma persistente en el navegador
                                            localStorage.setItem("aprobado_" + cod, "true");
                                        });
                                    </script>


                                    <!-- Espacio de 1cm -->
                                    <div style="width: 1cm;"></div>

                                    <!-- Botón Devolver (abre modal, NO está en el form) -->
                                    <?php if ($rol_usuario !== 'reconocedor'): ?>
                                        <button type="button" class="btn btn-danger px-4" data-bs-toggle="modal" data-bs-target="#modalDevolver">
                                            <i class="bi bi-bookmark-x-fill me-1"></i>DEVOLVER
                                        </button>
                                    <?php endif; ?>

                                </div>
                            </div>

                        </div>
                    </form>


                    <!-- MODAL DEVOLVER TRAMITE -->
                    <div class="modal fade" id="modalDevolver" tabindex="-1" role="dialog" style="z-index: 1550;">
                        <div class="modal-dialog modal-xl  modal-dialog-centered" role="document">
                            <div class="modal-content">

                                <div class="modal-header bg-danger text-white px-5">
                                    <h5 class="modal-title"><b>Devolver Trámite</b></h5>
                                    <button type="button" class="btn-close" style="background-color:white !important" data-bs-dismiss="modal"></button>
                                </div>

                                <form id="formModal" enctype="multipart/form-data">

                                    <!-- input hidden para saber qué acción se hizo -->
                                    <input type="hidden" name="accion" id="accion" value="">

                                    <div class="modal-body">
                                        <!-- Devolucion -->
                                        <input type="hidden" name="cod_tramite" value="<?php echo $tramite['cod_tramite']; ?>">

                                        <div class="form-row">
                                            <!-- <div class="col-md-6">
                                                <label for="cod_tramite"><b>ID_Radicacion</b></label>
                                                <input class="form-control py-4" id="cod_tramite" name="entrega_cod_tramite"
                                                    type="text" value="<?php echo htmlspecialchars($tramite['cod_tramite']); ?>" readonly>
                                            </div> -->

                                            <div class="col-md-6 p-1 px-2 my-2">
                                                <label for="cod_tramite" class="form-label fw-bold" style="font-size:0.9em">Identificador radicación</label>
                                                <div class="input-group shadow-sm">
                                                    <span class="input-group-text"><i class="bi-journal-text"></i></span>
                                                    <input type="text" class="form-control" style="font-size: 0.9em;" id="cod_tramite" name="entrega_cod_tramite"
                                                        value="<?php echo htmlspecialchars($tramite['cod_tramite']); ?>" readonly>
                                                </div>
                                            </div>

                                            <!-- <div class="col-md-6">
                                                <label for="fecha_rad"><b>Hora Radicación del Trámite</b></label>
                                                <input class="form-control py-4" id="fecha_rad" name="historial_fecha_tramite"
                                                    type="text" value="<?php echo htmlspecialchars($tramite['fecha_rad']); ?>" readonly>
                                            </div> -->

                                            <div class="col-md-6 p-1 px-2 my-2">
                                                <label for="fecha_rad" class="form-label fw-bold" style="font-size:0.9em">Fecha y hora de radicación</label>
                                                <div class="input-group shadow-sm">
                                                    <span class="input-group-text"><i class="bi bi-calendar-date"></i></span>
                                                    <input type="text" class="form-control" style="font-size: 0.9em;" id="fecha_rad" name="historial_fecha_tramite"
                                                        value="<?php echo htmlspecialchars($tramite['fecha_rad'] ?? ''); ?>" readonly>
                                                </div>
                                            </div>

                                        </div><br>

                                        <div class="form-group">
                                            <div class="form-row">

                                                <!-- <div class="col-md-3">
                                                    <label for="creacion_tram_cc_usuario"><b>Cédula del Usuario</b></label>
                                                    <input class="form-control"
                                                        id="creacion_tram_cc_usuario_modal"
                                                        type="text"
                                                        value="<?php echo $creacion_tram_cc_usuario; ?>"
                                                        readonly>
                                                    -- hidden para que viaje en POST --
                                                    <input type="hidden"
                                                        id="creacion_tram_cc_usuario_modal"
                                                        name="creacion_tram_cc_usuario"
                                                        value="<?php echo $creacion_tram_cc_usuario; ?>">
                                                </div> -->

                                                <div class="col-md-3 p-1 px-2 ">
                                                    <label for="creacion_tram_cc_usuario" class="form-label fw-bold" style="font-size:0.9em">Cédula del interesado</label>
                                                    <div class="input-group shadow-sm">
                                                        <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="creacion_tram_cc_usuario_modal" name="creacion_tram_cc_usuario"
                                                            value="<?php echo $creacion_tram_cc_usuario; ?>" readonly>
                                                        <!-- hidden para que viaje en POST -->
                                                        <input type="hidden"
                                                            id="creacion_tram_cc_usuario_modal"
                                                            name="creacion_tram_cc_usuario"
                                                            value="<?php echo $creacion_tram_cc_usuario; ?>">
                                                    </div>
                                                </div>

                                                <!-- <div class="col-md-3">
                                                    <label for="creacion_tram_nombre_usuario"><b>Nombres del Usuario</b></label>
                                                    <input class="form-control"
                                                        id="creacion_tram_nombre_usuario_modal"
                                                        type="text"
                                                        value="<?php echo $creacion_tram_nombre_usuario; ?>"
                                                        readonly>
                                                    <input type="hidden"
                                                        name="creacion_tram_nombre_usuario"
                                                        value="<?php echo $creacion_tram_nombre_usuario; ?>">
                                                </div>  -->

                                                <div class="col-md-3 p-1 px-2 ">
                                                    <label for="creacion_tram_nombre_usuario" class="form-label fw-bold" style="font-size:0.9em">Nombres del usuario</label>
                                                    <div class="input-group shadow-sm">
                                                        <span class="input-group-text"><i class="bi bi-person-bounding-box"></i></span>
                                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="creacion_tram_nombre_usuario_modal" name="creacion_tram_nombre_usuario"
                                                            value="<?php echo $creacion_tram_nombre_usuario; ?>" readonly>
                                                        <input type="hidden"
                                                            name="creacion_tram_nombre_usuario"
                                                            value="<?php echo $creacion_tram_nombre_usuario; ?>">
                                                    </div>
                                                </div>

                                                <!-- <div class="col-md-3">
                                                    <label for="creacion_tram_apellido_usuario"><b>Apellidos del Usuario</b></label>
                                                    <input class="form-control"
                                                        id="creacion_tram_apellido_usuario_modal"
                                                        type="text"
                                                        value="<?php echo $creacion_tram_apellido_usuario; ?>"
                                                        readonly>
                                                    <input type="hidden"
                                                        name="creacion_tram_apellido_usuario"
                                                        value="<?php echo $creacion_tram_apellido_usuario; ?>">
                                                </div> -->

                                                <div class="col-md-3 p-1 px-2 ">
                                                    <label for="creacion_tram_apellido_usuario" class="form-label fw-bold" style="font-size:0.9em">Apellido del usuario</label>
                                                    <div class="input-group shadow-sm">
                                                        <span class="input-group-text"><i class="bi-people-fill"></i></span>
                                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="creacion_tram_apellido_usuario_modal" name="creacion_tram_apellido_usuario"
                                                            value="<?php echo $creacion_tram_apellido_usuario; ?>" readonly>
                                                        <input type="hidden"
                                                            name="creacion_tram_apellido_usuario"
                                                            value="<?php echo $creacion_tram_apellido_usuario; ?>">
                                                    </div>
                                                </div>

                                                <!-- <div class="col-md-3">
                                                    <label for="creacion_tram_rol_usuario"><b>Cargo</b></label>
                                                    <input class="form-control"
                                                        id="creacion_tram_rol_usuario_modal"
                                                        type="text"
                                                        value="<?php echo $creacion_tram_rol_usuario; ?>"
                                                        readonly>
                                                    <input type="hidden"
                                                        name="creacion_tram_rol_usuario"
                                                        value="<?php echo $creacion_tram_rol_usuario; ?>">
                                                </div> -->

                                                <div class="col-md-3 p-1 px-2 ">
                                                    <label for="creacion_tram_rol_usuario" class="form-label fw-bold" style="font-size:0.9em">Cargo</label>
                                                    <div class="input-group shadow-sm">
                                                        <span class="input-group-text"><i class="bi bi-person-bounding-box"></i></span>
                                                        <input type="text" class="form-control" style="font-size: 0.9em;" id="creacion_tram_rol_usuario_modal" name="creacion_tram_rol_usuario"
                                                            value="<?php echo $creacion_tram_rol_usuario; ?>" readonly>
                                                        <input type="hidden"
                                                            name="creacion_tram_rol_usuario"
                                                            value="<?php echo $creacion_tram_rol_usuario; ?>">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- <div class="form-row mt-3">
                                                <div class="col-md-6">
                                                    <label for="documento_soporte"><b>Documento soporte (opcional)</b></label>
                                                    <input type="file"
                                                        class="form-control-file"
                                                        name="documento_soporte"
                                                        id="documento_soporte"
                                                        accept=".pdf,.jpg,.png,.doc,.docx">
                                                </div>
                                            </div> -->

                                            <div class="col-12 p-1 px-5 my-3">
                                                <label for="documento_soporte" class="form-label fw-bold">Documento soporte (opcional)</label>
                                                <div class="input-group mb-1 shadow-sm">
                                                    <label class="input-group-text" for="documento_soporte" style="font-size:0.9em;"><i class="bi bi-file-earmark-pdf-fill"></i></label>
                                                    <input type="file" class="form-control" style="font-size:0.8em;" id="documento_soporte" name="documento_soporte" accept=".pdf,.jpg,.png,.doc,.docx">
                                                </div>
                                                <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label><b>Motivo de la devolución:</b></label>
                                            <textarea class="form-control" name="motivo_devolucion" rows="3" style="background-color: #0230554d;" required></textarea>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <!-- Importante: el botón NO hace submit directo -->
                                        <button type="button" id="btnDevolver" class="btn btn-danger">
                                            <i class="bi bi-arrow-return-left me-2"></i> Confirmar Devolución
                                        </button>
                                        <button type="button" class="btn text-white" style="background-color: #022F55;" data-bs-dismiss="modal">Cancelar</button>
                                    </div>

                                    <input type="hidden"
                                        name="rol_actual"
                                        value="<?php echo htmlspecialchars($_SESSION['rol_usuario'] ?? ''); ?>">

                                </form>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
<!-- /.container-fluid -->

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

<script>
    document.addEventListener("DOMContentLoaded", function() {

        let contadorDocs = 1;
        const maxDocs = 5;

        document.getElementById("agregarDocumento").addEventListener("click", function() {
            if (contadorDocs >= maxDocs) {
                alert("Solo puedes agregar hasta 5 documentos.");
                return;
            }

            contadorDocs++;
            const container = document.getElementById("documentosContainer");

            const nuevoGrupo = document.createElement("div");
            nuevoGrupo.classList.add("form-row", "documento-group", "mt-3");

            nuevoGrupo.innerHTML = `
            <div class="col-md-6 p-1 px-2 my-2 ">
                <label for="tipo_doc1" class="form-label" style="font-size:0.9em;"><b>Seleccione el trámite</b></label>
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

            <div class=" col-md-6 p-1 px-2 my-2">
                <label for="nombre_doc1" class="form-label fw-bold">Documentos a cargar</label>
                    <div class="input-group  shadow-sm">
                <label class="input-group-text" for="nombre_doc1" style="font-size:0.9em;"><i class="bi bi-file-earmark-pdf-fill"></i></label>
                        <input type="file" class="form-control" style="font-size:0.8em;" id="nombre_doc1" name="nombre_doc${contadorDocs}">
                    </div>
                    <div class="form-text">Solo se permiten archivos PDF de hasta 20 MB.</div>
            </div>
        `;

            container.appendChild(nuevoGrupo);
        });

        function verHistorialAsignacion(rol, codTramite) {
            console.log("Enviando:", rol, codTramite);

            fetch('ajax/ver_historial_rol.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `rol=${encodeURIComponent(rol)}&cod_tramite=${encodeURIComponent(codTramite)}`
                })
                .then(response => response.text())
                .then(data => {
                    document.getElementById('historialContenido').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('historialContenido').innerHTML = 'Error al cargar el historial.';
                    console.error('Error en fetch:', error);
                });
        }
    });

    // function toggleIframe(id) {
    //     const visor = document.getElementById(id);
    //     visor.style.display = (visor.style.display === 'none') ? 'block' : 'none';
    // }


    // Devolver
    document.getElementById("btnDevolver").addEventListener("click", function() {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¿Deseas devolver este trámite?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#002F55',
            confirmButtonText: 'Sí, devolver',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Capturo los datos del modal
                let modalForm = document.getElementById("formModal");
                let formData = new FormData(modalForm);

                // Forzamos que la acción sea DEVOLVER
                formData.set("accion", "devolver");

                fetch("/cons_neiva/Practica/vistas/seguimiento/acciones/procesar_devolucion.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Devolución realizada!',
                            text: data,
                            confirmButtonColor: '#02722d'
                        }).then(() => {
                            window.location.href = "index.php?page=tramites/consultar_tramite";
                        });
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al procesar la devolución.'
                        });
                        console.error("Error:", error);
                    });
            }
        });
    });
</script>