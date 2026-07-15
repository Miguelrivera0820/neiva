<?php
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}

$con_id = isset($_GET['con_id']) ? intval($_GET['con_id']) : 0;

if ($con_id <= 0) {
    die("No se recibió un ID de contrato válido.");
}

$sql = "
SELECT 
    c.con_id,
    c.con_nombres,
    c.con_apellidos,
    c.con_tipo_documento,
    c.con_num_identidad,
    c.con_fecha_inicio,
    c.con_fecha_final,
    c.con_cargo,
    c.con_proyecto,
    c.con_salario,
    c.con_valor_proyecto,
    s.sol_nueva_fecha_final AS otr_fecha_Final
FROM contratacion c
LEFT JOIN solicitudes_otrosi s 
    ON c.con_id = s.con_id
    AND s.sol_nueva_fecha_final = (
        SELECT MAX(sol_nueva_fecha_final) 
        FROM solicitudes_otrosi 
        WHERE con_id = c.con_id
    )
WHERE c.con_id = ?
LIMIT 1
";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    die("Error al preparar la consulta: " . $mysqli->error);
}

$stmt->bind_param("i", $con_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $con_nombres         = $row['con_nombres'];
    $con_apellidos       = $row['con_apellidos'];
    $con_tipo_documento  = $row['con_tipo_documento'];
    $con_num_identidad   = $row['con_num_identidad'];
    $con_fecha_inicio    = $row['con_fecha_inicio'];
    $con_fecha_final     = $row['con_fecha_final'];
    $con_cargo           = $row['con_cargo'];
    $con_proyecto        = $row['con_proyecto'];
    $con_salario         = $row['con_salario'];
    $con_valor_proyecto  = $row['con_valor_proyecto'];
    $otr_fecha_Final     = $row['otr_fecha_Final'];
} else {
    die("No se encontró información del contrato.");
}

$sql_otrosi = "
    SELECT COUNT(*) AS total_otrosi FROM (
        SELECT con_num_identidad FROM solicitudes_otrosi WHERE con_num_identidad = ?
        UNION ALL
        SELECT otr_cedula FROM otrosi WHERE otr_cedula = ?
    ) AS todos_los_otrosi
";

$stmt_otrosi = $mysqli->prepare($sql_otrosi);

if ($stmt_otrosi) {
    $stmt_otrosi->bind_param("ss", $con_num_identidad, $con_num_identidad);
    $stmt_otrosi->execute();
    $result_otrosi   = $stmt_otrosi->get_result();
    $row_otrosi      = $result_otrosi->fetch_assoc();
    $cantidad_otrosi = $row_otrosi['total_otrosi'];
} else {
    $cantidad_otrosi = 0;
}


$valorFormateado = '$ ' . number_format($con_salario, 0, ',', '.');
?>


<style>
    body {
        background: #f3f6fb;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .card-form {
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        background-color: #fff;
        margin-top: 2rem;
        margin-bottom: 2rem;
    }

    .card-form header {
        background: linear-gradient(90deg, #003B66, #0062cc);
        color: #fff;
        padding: 1.5rem 2rem;
        font-size: 1.4rem;
        font-weight: 600;
        text-align: center;
    }

    .form-label {
        font-weight: 600;
        color: #1d1e1e;
        font-size: 0.95rem;
    }

    .texto-grande {
        font-size: 15px;
        font-weight: bold;
    }

    select.form-control,
    input.form-control,
    textarea.form-control {
        border-radius: 0.5rem;
    }

    .btn-primary {
        background-color: #003B66;
        border-color: #003B66;
        border-radius: 0.7rem;
        padding-left: 2.5rem;
        padding-right: 2.5rem;
        font-weight: 600;
    }

    .btn-primary:hover {
        background-color: #004780;
        border-color: #004780;
    }
</style>

<style>
    .formulario-otrosi {
        width: 92%;
        max-width: 1400px;
        margin: 2rem auto;
        padding: 2rem;
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 0.7rem 1.6rem rgba(0, 0, 0, 0.12);
    }

    .formulario-otrosi header {
        background: linear-gradient(90deg, #003B66, #005fa3);
        color: #fff;
        padding: 1.7rem;
        font-size: 1.7rem;
        font-weight: 700;
        text-align: center;
        border-radius: .7rem;
        margin-bottom: 1.8rem;
    }

    .formulario-otrosi .form-control,
    .formulario-otrosi select,
    .formulario-otrosi textarea {
        border-radius: .55rem;
        padding: .65rem .85rem;
        font-size: .95rem;
        border: 1px solid #cdd6e0;
    }

    .formulario-otrosi textarea {
        min-height: 95px;
    }

    .formulario-otrosi .btn-primary {
        display: block;
        margin: 0 auto;
        padding: .8rem 3rem;
        border-radius: .6rem;
        background-color: #003B66;
        border: none;
        font-size: 1.1rem;
        font-weight: 600;
    }

    .formulario-otrosi .btn-primary:hover {
        background-color: #004b83;
    }
</style>

<div class="container">
    <h1 class="mt-4 text-center text-dark"><b>Solicitar Otro Sí</b></h1>
    <p class="text-center text-muted mb-4">
        A continuación podrá solicitar un nuevo Otro Sí para el contrato seleccionado.
    </p>

    <div class="card-form mx-auto formulario-otrosi">
        <header>SOLICITAR OTRO SÍ</header>
        <div class="card-body">
            <!-- Cantidad de Otro Sí -->
            <div class="mb-3 texto-grande">
                <b>Cantidad de Otro Sí: </b> <?php echo $cantidad_otrosi; ?>
            </div>

            <form id="multiStepForm" method="POST" action="<?= neiva_app_url('Arbimaps/vistas/Personal/solicitud_otrosi_guardar.php') ?>">
                <div class="row">
                    <!-- Datos básicos -->
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label class="form-label" for="sol_nombres">Nombres</label>
                        <input type="text" class="form-control" id="sol_nombres" name="sol_nombres"
                            value="<?php echo $con_nombres ?? ''; ?>" disabled>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label class="form-label" for="sol_apellidos">Apellidos</label>
                        <input type="text" class="form-control" name="sol_apellidos" id="sol_apellidos"
                            value="<?php echo $con_apellidos ?? ''; ?>" disabled>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label class="form-label" for="sol_tipo_documento">T.Documento</label>
                        <input class="form-control text-truncate" name="sol_tipo_documento" id="sol_tipo_documento"
                            value="<?php echo $con_tipo_documento ?? ''; ?>" disabled>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label class="form-label" for="con_num_identidad">N° Identidad</label>
                        <input class="form-control" name="con_num_identidad" id="con_num_identidad"
                            value="<?php echo $con_num_identidad ?? ''; ?>" readonly>
                    </div>

                    <div class="col-md-3 col-sm-6 mb-3">
                        <label class="form-label" for="con_fecha_inicio">Fecha de Inicio de Contrato</label>
                        <input class="form-control" name="con_fecha_inicio" id="con_fecha_inicio"
                            disabled value="<?php echo $con_fecha_inicio ?? ''; ?>">
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label class="form-label" for="con_fecha_final">Fecha Final del Contrato</label>
                        <input class="form-control" name="con_fecha_final" id="con_fecha_final"
                            disabled value="<?php echo $con_fecha_final ?? ''; ?>">
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label class="form-label" for="con_cargo">Cargo/Rol</label>
                        <input type="text" class="form-control" id="con_cargo" name="con_cargo"
                            value="<?php echo $con_cargo ?? ''; ?>" disabled>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label class="form-label" for="con_proyecto">Proyecto</label>
                        <input type="text" class="form-control" id="con_proyecto" name="con_proyecto"
                            value="<?php echo $con_proyecto ?? ''; ?>" disabled>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label class="form-label" for="con_salario">Honorarios</label>
                        <input type="text" class="form-control" id="con_salario" name="con_salario"
                            value="<?php echo $valorFormateado ?? ''; ?>" disabled>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label class="form-label" for="con_valor_proyecto">Honorarios Actuales</label>
                        <input type="text" class="form-control" id="con_valor_proyecto" name="con_valor_proyecto"
                            value="<?php echo $con_valor_proyecto ?? ''; ?>" disabled>
                    </div>

                    <!-- Cambio de rol / proyecto -->
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label class="form-label" for="sol_nuevo_rol">¿Es Cambio de Cargo/Rol?</label>
                        <select class="form-control" id="rol" name="rol" onchange="mostrar()" required>
                            <option value="">SELECCIONE</option>
                            <option value="si">Sí</option>
                            <option value="no">No</option>
                        </select>
                        <input type="hidden" name="con_cargo" value="<?php echo $con_cargo; ?>">
                    </div>

                    <div class="col-md-3 col-sm-6 mb-3" id="campo_cual" style="display: none;">
                        <label class="form-label" for="con_descripcion">¿Cuál?</label>
                        <select class="form-control" id="sol_nuevo_rol" name="sol_nuevo_rol">
                            <option value="<?php echo $con_cargo; ?>"><?php echo $con_cargo; ?></option>
                            <!-- lista de cargos -->
                            <option value="ABOGADOS">ABOGADOS</option>
                            <option value="ASIGNADOR">ASIGNADOR</option>
                            <option value="AUXILIAR_ADMINISTRATIVO">AUXILIAR_ADMINISTRATIVO</option>
                            <option value="AUXILIAR_ADMINISTRATIVO_VENTANILLA_UNICA">AUXILIAR ADMINISTRATIVO VENTANILLA ÚNICA</option>
                            <option value="AUXILIAR_ADMINISTRATIVO_Y_DE_OPERACIONES">AUXILIAR ADMINISTRATIVO Y DE OPERACIONES</option>
                            <option value="AUXILIAR_DE_SERVICIOS_GENERALES">AUXILIAR DE SERVICIOS GENERALES</option>
                            <option value="AUXILIAR_SOCIAL">AUXILIAR SOCIAL</option>
                            <option value="AUXILIAR_VENTANILLA">AUXILIAR VENTANILLA</option>
                            <option value="CONSOLIDADOR">CONSOLIDADOR</option>
                            <option value="CONSULTA_VUR">CONSULTA VUR</option>
                            <option value="CONTROL_DE_CALIDAD">CONTROL DE CALIDAD</option>
                            <option value="COORDINADOR_CUADRILLA">COORDINADOR CUADRILLA</option>
                            <option value="DESARROLLADOR">DESARROLLADOR</option>
                            <option value="DIGITADOR">DIGITADOR</option>
                            <option value="DIGITALIZADOR">DIGITALIZADOR</option>
                            <option value="DIRECTOR_DE_OPERACIONES">DIRECTOR DE OPERACIONES</option>
                            <option value="DIRECTOR_FINANCIERO">DIRECTOR FINANCIERO</option>
                            <option value="DIRECTOR_PLANEACION">DIRECTOR PLANEACIÓN</option>
                            <option value="DIRECTORA_COMERCIAL">DIRECTORA COMERCIAL</option>
                            <option value="EDITOR">EDITOR</option>
                            <option value="GERENTE">GERENTE</option>
                            <option value="GESTORA_DE_TALENTO_HUMANO">GESTORA DE TALENTO HUMANO</option>
                            <option value="HSEQ">HSEQ</option>
                            <option value="JEFE_DE_OPERACIONES">JEFE DE OPERACIONES</option>
                            <option value="LIDER_CONSOLIDACION">LÍDER CONSOLIDACIÓN</option>
                            <option value="LIDER_RECONOCIMIENTO">LÍDER RECONOCIMIENTO</option>
                            <option value="LIDER_TECNICO">LÍDER TÉCNICO</option>
                            <option value="MERGIN_MAPS">MERGIN MAPS</option>
                            <option value="PRESUPUESTO">PRESUPUESTO</option>
                            <option value="PROFESIONAL_2_CON_POSGRADO">PROFESIONAL 2 CON POSGRADO</option>
                            <option value="PROFESIONAL_3_CON_POSGRADO">PROFESIONAL 3 CON POSGRADO</option>
                            <option value="PROFESIONAL_HSEQ_CON_POSGRADO">PROFESIONAL HSEQ CON POSGRADO</option>
                            <option value="PROFESIONAL_CATASTRAL_1">PROFESIONAL CATASTRAL 1</option>
                            <option value="PROGRAMADORA_DE_SOFTWARE">PROGRAMADORA DE SOFTWARE</option>
                            <option value="PROFESIONAL_SIG">PROFESIONAL SIG</option>
                            <option value="PROFESIONAL_SOCIAL">PROFESIONAL_SOCIAL</option>
                            <option value="RECONOCEDOR_PREDIAL">RECONOCEDOR PREDIAL</option>
                            <option value="SOPORTE_NIVEL_1">SOPORTE NIVEL 1</option>
                            <option value="SOPORTE_NIVEL_2">SOPORTE NIVEL 2</option>
                            <option value="SUBGERENTE_GENERAL">SUBGERENTE GENERAL</option>
                            <option value="TECNOLOGO">TECNOLOGO</option>
                            <option value="TECNOLOGO_GRADO_2">TECNOLOGO GRADO 2</option>
                            <option value="RECONOCEDOR_PREDIAL_JUNIOR">RECONOCEDOR PREDIAL JUNIOR</option>
                            <option value="COORDINADOR_SIG">COORDINADOR SIG</option>
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-6 mb-3">
                        <label class="form-label" for="sol_nuevo_proyecto">¿Es Cambio de Proyecto?</label>
                        <select class="form-control" id="proyecto" name="proyecto" onchange="mostrarProyecto()" required>
                            <option value="">SELECCIONE</option>
                            <option value="si">Sí</option>
                            <option value="no">No</option>
                        </select>
                        <input type="hidden" name="con_proyecto" value="<?php echo $con_proyecto; ?>">
                    </div>

                    <div class="col-md-3 col-sm-6 mb-3" id="campo_proyecto" style="display: none;">
                        <label class="form-label" for="con_descripcion_proyecto">¿Cuál?</label>
                        <select class="form-control" id="sol_nuevo_proyecto" name="sol_nuevo_proyecto">
                            <option value="<?php echo $con_proyecto; ?>"><?php echo $con_proyecto; ?></option>
                            <option value="ANT_AVALUOS">ANT_AVALÚOS</option>
                            <option value="ADMINISTRATIVO">ADMINISTRATIVO</option>
                            <option value="ARBIMAPS">ARBIMAPS</option>
                            <option value="ARBITECH">ARBITECH</option>
                            <option value="ARBOLETES">VALOR + ARBOLETES</option>
                            <option value="CONTABILIDAD">CONTABILIDAD</option>
                            <option value="LEIVA">IGAC LEIVA</option>
                            <option value="NECOCLÍ">VALOR + NECOCLÍ</option>
                            <option value="SAN_JUAN">VALOR + SAN JUAN DE URABA</option>
                            <option value="SAN_PEDRO">VALOR + SAN PEDRO DE URABA</option>
                            <option value="VALLE_ABURRA">VALLE DE ABURRA BELLO</option>
                            <option value="VALLE_GUAMUEZ">IGAC VALLE DEL GUAMUEZ</option>
                        </select>
                    </div>

                    <!-- Datos financieros del Otro Sí -->
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label class="form-label" for="sol_nuevo_salario">Honorarios</label>
                        <input class="form-control" id="con_salario_mostrado" name="con_salario_mostrado"
                            oninput="formatCurrency(this)" required>
                        <input type="hidden" id="sol_nuevo_salario" name="sol_nuevo_salario"
                            value="<?php echo $con_salario; ?>">
                    </div>

                    <div class="col-md-3 col-sm-6 mb-3">
                        <label class="form-label" for="sol_fecha_inicio">Fecha de Inicio de Otro Sí</label>
                        <input class="form-control" name="sol_fecha_inicio" id="sol_fecha_inicio" type="date">
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label class="form-label" for="sol_nueva_fecha_final">Nueva Fecha Final de Otro Sí</label>
                        <input class="form-control" name="sol_nueva_fecha_final" id="sol_nueva_fecha_final" type="date">
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label class="form-label" for="sol_duracion">Duración de Otro Sí</label>
                        <input class="form-control" type="text" id="sol_duracion" name="sol_duracion" readonly>
                        <input class="form-control" type="hidden" id="sol_duracion_calc" name="sol_duracion_calc">
                    </div>

                    <div class="col-md-3 col-sm-6 mb-3">
                        <label class="form-label" for="sol_valor_otrosi">Valor Total Otro Sí</label>
                        <input type="text" class="form-control" id="sol_valor_mostrado" name="sol_valor_mostrado" readonly>
                        <input type="hidden" id="sol_valor_otrosi" name="sol_valor_otrosi">
                    </div>

                    <div class="col-12 col-md-6 file-upload-item mb-3">
                        <label class="form-label" for="sol_motivo">Justificación Técnica</label>
                        <textarea class="form-control" id="sol_motivo" name="sol_motivo"
                            placeholder="Ingrese la justificación técnica" required
                            oninput="autoResize(this)"></textarea>
                    </div>

                    <input type="hidden" name="con_id" value="<?php echo $con_id ?? ''; ?>">
                    <input type="hidden" name="sol_usuario_id" id="sol_usuario_id"
                        value="<?php echo $_SESSION['id_usuario']; ?>">

                    <!-- Tipos de solicitud -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label d-block">Tipo de Solicitud Otro Sí</label>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="sol_tipo_otrosi[]"
                                value="PRORROGA" id="otrosi_prorroga">
                            <label class="form-check-label" for="otrosi_prorroga">PRÓRROGA</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="sol_tipo_otrosi[]"
                                value="ADICION" id="otrosi_adicion">
                            <label class="form-check-label" for="otrosi_adicion">ADICIÓN</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="sol_tipo_otrosi[]"
                                value="CAMBIO_DE_ROL" id="otrosi_cambio_rol">
                            <label class="form-check-label" for="otrosi_cambio_rol">CAMBIO DE ROL</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="otrosi_otro"
                                onclick="mostrarCampoOtroSi()">
                            <label class="form-check-label" for="otrosi_otro">Otro</label>
                        </div>

                        <div class="mt-2" id="campo_otro_otrosi" style="display: none;">
                            <input class="form-control" type="text" name="sol_tipo_otrosi[]"
                                placeholder="Especifique otro tipo de solicitud" />
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-center">
                    <button type="button" class="btn btn-primary" id="btn_solicitud">SOLICITAR</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://kit.fontawesome.com/9944c94262.js" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="../../../js/scripts.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
<script>
    function autoResize(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = (textarea.scrollHeight) + 'px';
    }
    document.addEventListener('DOMContentLoaded', () => {
        const textarea = document.getElementById('sol_motivo');
        if (textarea) autoResize(textarea);
    });

    function formatCurrency(input) {
        let value = input.value.replace(/\D/g, "");
        if (!value) {
            input.value = "";
            document.getElementById("sol_nuevo_salario").value = "";
            return;
        }
        let formattedValue = new Intl.NumberFormat("es-CO", {
            style: "currency",
            currency: "COP",
            minimumFractionDigits: 0
        }).format(value);
        input.value = formattedValue.replace("COP", "").trim();
        document.getElementById("sol_nuevo_salario").value = value;
    }

    function actual(input) {
        let rawValue = input.value.replace(/\D/g, "");
        if (!rawValue) {
            document.getElementById("sol_valor_otrosi").value = "";
            input.value = "";
            return;
        }
        let formattedValue = new Intl.NumberFormat("es-CO", {
            style: "currency",
            currency: "COP",
            minimumFractionDigits: 0
        }).format(rawValue);
        input.value = formattedValue.replace("COP", "").trim();
        document.getElementById("sol_valor_otrosi").value = rawValue;
    }

    function mostrar() {
        const seleccion = document.getElementById("rol").value;
        const campo = document.getElementById("campo_cual");
        campo.style.display = (seleccion === "si") ? "block" : "none";
    }

    function mostrarProyecto() {
        const seleccion = document.getElementById("proyecto").value;
        const campo = document.getElementById("campo_proyecto");
        campo.style.display = (seleccion === "si") ? "block" : "none";
    }

    function mostrarCampoOtroSi() {
        const checkOtro = document.getElementById("otrosi_otro");
        const campoTexto = document.getElementById("campo_otro_otrosi");
        campoTexto.style.display = checkOtro.checked ? "block" : "none";
    }

    // =========================
    // ✅ NUEVA LÓGICA (DÍAS + VALOR TOTAL)
    // =========================
    function parseYMD(ymd) {
        const [y, m, d] = ymd.split("-").map(Number);
        return new Date(y, m - 1, d);
    }

    function diffDias(inicio, fin) {
        const msPorDia = 24 * 60 * 60 * 1000;
        return Math.round((fin - inicio) / msPorDia) + 1; // inclusivo
    }

    function esMesCompleto(dIni, dFin) {
        if (dIni.getFullYear() !== dFin.getFullYear()) return false;
        if (dIni.getMonth() !== dFin.getMonth()) return false;
        if (dIni.getDate() !== 1) return false;

        const ultimoDiaMes = new Date(dIni.getFullYear(), dIni.getMonth() + 1, 0).getDate();
        return dFin.getDate() === ultimoDiaMes;
    }

    document.getElementById("sol_fecha_inicio").addEventListener("input", recalcularTodo);
    document.getElementById("sol_fecha_inicio").addEventListener("change", recalcularTodo);

    document.getElementById("sol_nueva_fecha_final").addEventListener("input", recalcularTodo);
    document.getElementById("sol_nueva_fecha_final").addEventListener("change", recalcularTodo);

    // cuando el salario visible cambia, esperamos a que formatCurrency actualice el hidden
    document.getElementById("con_salario_mostrado").addEventListener("input", () => setTimeout(calcularValorProyecto, 0));
    document.getElementById("con_salario_mostrado").addEventListener("change", () => setTimeout(calcularValorProyecto, 0));

    function recalcularTodo() {
        calcularDuracion();
        calcularValorProyecto();
    }

    function calcularDuracion() {
        const inicio = document.getElementById("sol_fecha_inicio").value;
        const final  = document.getElementById("sol_nueva_fecha_final").value;

        const durTxt  = document.getElementById("sol_duracion");
        const durCalc = document.getElementById("sol_duracion_calc"); // ✅ ESTE ES EL QUE EXISTE

        if (!inicio || !final) {
            durTxt.value = "";
            durCalc.value = "";
            return;
        }

        const start = parseYMD(inicio);
        const end   = parseYMD(final);

        if (end < start) {
            durTxt.value = "Fecha final inválida";
            durCalc.value = "";
            return;
        }

        const dias = diffDias(start, end);
        durTxt.value  = `${dias} día${dias === 1 ? "" : "s"}`;
        durCalc.value = String(dias);
    }

    function calcularValorProyecto() {
        const salarioMensual = Number(document.getElementById("sol_nuevo_salario").value || 0);
        const inicioStr = document.getElementById("sol_fecha_inicio").value;
        const finStr    = document.getElementById("sol_nueva_fecha_final").value;

        const valorHidden  = document.getElementById("sol_valor_otrosi");
        const valorVisible = document.getElementById("sol_valor_mostrado");

        if (!salarioMensual || !inicioStr || !finStr) {
            valorHidden.value = "";
            valorVisible.value = "";
            return;
        }

        const dIni = parseYMD(inicioStr);
        const dFin = parseYMD(finStr);

        if (dFin < dIni) {
            valorHidden.value = "";
            valorVisible.value = "";
            return;
        }

        let total;
        if (esMesCompleto(dIni, dFin)) {
            total = salarioMensual;
        } else {
            const dias = diffDias(dIni, dFin);
            total = Math.round((salarioMensual / 30) * dias);
        }

        valorHidden.value = String(total);
        valorVisible.value = new Intl.NumberFormat("es-CO", {
            style: "currency",
            currency: "COP",
            minimumFractionDigits: 0
        }).format(total).replace("COP", "").trim();
    }

    // (Opcional pero útil) recalcula si ya hay valores precargados
    document.addEventListener("DOMContentLoaded", () => {
        recalcularTodo();
        setTimeout(calcularValorProyecto, 0);
    });

    // =========================
    // ✅ TU SUBMIT NO SE TOCA
    // =========================
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('multiStepForm');
        const submitButton = document.getElementById('btn_solicitud');
        submitButton.addEventListener('click', function(event) {
            event.preventDefault();
            if (!form.checkValidity()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos incompletos',
                    text: 'Por favor, completa todos los campos requeridos.',
                    confirmButtonText: 'Entendido'
                });
                return;
            }
            const formData = new FormData(form);
            fetch(form.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(text => {
                    console.log("Respuesta cruda del servidor:", text);
                    try {
                        const data = JSON.parse(text);
                        if (!data.success) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Ocurrió un error en el servidor.'
                            });
                        } else {
                            Swal.fire({
                                icon: 'success',
                                title: 'Solicitud enviada',
                                text: 'La solicitud de Otro Sí ha sido registrada.',
                                confirmButtonText: 'Entendido'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = '<?= neiva_app_url('Arbimaps/index.php?page=Personal/personal_activo') ?>';
                                }
                            });
                        }
                    } catch (e) {
                        console.error("Respuesta inesperada del servidor:", text);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de respuesta',
                            text: 'La respuesta del servidor no tiene el formato esperado.'
                        });
                    }
                })
                .catch(error => {
                    console.error("Error al enviar los datos:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de red',
                        text: 'Hubo un problema al enviar los datos. Inténtalo de nuevo.'
                    });
                });
        });
    });
</script>
