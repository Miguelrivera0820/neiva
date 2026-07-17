<?php
$anio_actual = $row['anio_cuenta'] ?? null;
// Variables iniciales
$numero_identidad       = $_GET['numero_identidad']            ?? null;
$id_get                 = isset($_GET['id']) ? (int) $_GET['id'] : null;
$tipo_documento         = $_GET['Documento']                   ?? null;
$primer_nombre          = filter_var($_GET['primer_nombre']       ?? '', FILTER_SANITIZE_STRING);
$segundo_nombre         = filter_var($_POST['segundo_nombre']      ?? '', FILTER_SANITIZE_STRING);
$primer_apellido        = filter_var($_POST['primer_apellido']     ?? '', FILTER_SANITIZE_STRING);
$segundo_apellido       = filter_var($_POST['segundo_apellido']    ?? '', FILTER_SANITIZE_STRING);
$telefono               = filter_var($_POST['telefono']            ?? '', FILTER_SANITIZE_STRING);
$correo                 = filter_var($_POST['correo']              ?? '', FILTER_VALIDATE_EMAIL);
$cargo                  = $_POST['cargo']                       ?? '';
$proyecto               = $_POST['proyecto']                    ?? null;
$observacion            = $_POST['observacion']                 ?? null;

$datos_cuentas  = null;

// Preferir consultar por id si está presente (evita ambigüedades entre meses)
if ($id_get) {
    $query = "SELECT * FROM cuenta WHERE id = ? LIMIT 1";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id_get);
} elseif ($numero_identidad) {
    // Si no hay id, tomar la cuenta más reciente de la cédula
    $query = "SELECT * FROM cuenta WHERE numero_identidad = ? ORDER BY fecha_subida DESC LIMIT 1";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $numero_identidad);
} else {
    echo "No se proporcionó número de identidad ni id de cuenta.<br>";
    exit;
}
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $id                     = $row['id']                    ?? null;
    $fecha_radicado         = $row['creado_en']       ?? null;
    $tipo_documento         = $row['tipo_documento'];
    $primer_nombre          = $row['primer_nombre'];
    $segundo_nombre         = $row['segundo_nombre'];
    $primer_apellido        = $row['primer_apellido'];
    $segundo_apellido       = $row['segundo_apellido'];
    $telefono               = $row['telefono'];
    $correo                 = $row['correo'];
    $Fecha_Inicio           = $row['Fecha_Inicio'];
    $Fecha_Final            = $row['Fecha_Final'];
    $Periodo_Facturacion    = $row['Periodo_Facturacion'];
    $anio_cuenta            = $row['anio_cuenta'];
    $valor                  = $row['valor'];
    $Val_Seg_Social         = $row['Val_Seg_Social']        ?? null;
    $cant_dias              = $row['cant_dias'];
    $cargo                  = $row['cargo'];
    $proyecto               = $row['proyecto'];
    $observacion            = $row['observacion'];
    $informe_mensual        = $row['informe_mensual']       ?? null;
    $cuenta_cobro           = $row['cuenta_cobro']          ?? null;
    $cuenta_pagos           = $row['cuenta_pagos']          ?? null;
    $seguridad_social       = $row['seguridad_social']      ?? null;
    $retencion              = $row['retencion']             ?? null;
    $primera_vez            = $row['primera_vez']           ?? null;

    // echo "Datos cargados correctamente.<br>";

    $obtenerdocumentos      = '../../../../arbimaps/Arbimaps/DOCUMENTOS/modelo_de_cuenta/';
    $carpeta_anio = (!empty($anio_cuenta) && is_numeric($anio_cuenta))
        ? $anio_cuenta
        : 'sin_anio';
    $carpeta_periodo        = $row['Periodo_Facturacion']   ?? $Periodo_Facturacion ?? 'periodo';
    $carpeta_identidad      = $row['numero_identidad']      ?? $numero_identidad    ?? 'sin_identidad';

    $mostrar_documentos     = $obtenerdocumentos . $carpeta_anio . '/' . $carpeta_periodo . '/' . $carpeta_identidad . '/';
} else {
    echo "No se encontraron datos para esta cédula.<br>";
    exit;
}


if (isset($stmt) && $stmt) {
    $stmt->close();
}
$mysqli->close();

?>

<style>
    .precargado {
        border: 1px solid #002f55a1;
        box-shadow: 0 0 10px #002f5517 !important;
    }

    .precargado input {
        background-color: #002f5517 !important;
        font-weight: 500;
    }

    .precargadodos {
        border: 1px solid #edd51e93;
        box-shadow: 0 0 10px #c7c0341e !important;
    }

    .precargadodos input {
        background-color: #e9e51433 !important;
        font-weight: 600;
    }

    /* Estado inicial (oculto) */
    .iframe-animado {
        opacity: 0;
        max-height: 0;
        overflow: hidden;
        transition: all 0.4s ease-in-out;
        /* Animación suave */
    }

    /* Estado visible */
    .iframe-animado.mostrar {
        opacity: 1;
        max-height: 700px;
        /* Ajusta un poco más que el height del iframe */
    }
</style>

<div class="container-fluid">
    <div class="my-3 text-center">
        <h4 class=" mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 700 !important ">CUENTAS RADICADAS</h4>
        <small>Módulo para consulta de la cuenta de cobro radicadas por el personal OPS </small>
    </div>

    <div class="container p-2 my-4 px-4">
        <div class="row px-5">
            <div class="col-4 p-1 px-2">
                <div class="card shadow-sm p-3 " style="border: 1px solid #d3c66593;">
                    <label for="numero_identidad" class="form-label fw-bold" style="font-size:0.9em;">Valor a cobrar</label>
                    <div class="input-group shadow-sm rounded-2 <?php echo (!empty($valor)) ? 'precargadodos' : ''; ?>">
                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                        <input type="text" class="form-control valor " style="font-size:0.9em;" name="numero_identidad" placeholder="Ingrese el número de documento..."
                            aria-label="PrimerNombre" aria-describedby="basic-addon1" value="<?php echo $valor; ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="col-4 p-1 px-2">
                <div class="card shadow-sm p-3 ">
                    <label for="numero_identidad" class="form-label fw-bold" style="font-size:0.9em;">Fecha y hora de radicación</label>
                    <div class="input-group shadow-sm rounded-2 <?php echo (!empty($fecha_radicado)) ? 'precargado' : ''; ?>">
                        <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                        <input type="text" class="form-control " style="font-size:0.9em;" id="numero_identidad" name="numero_identidad" placeholder="Ingrese el número de documento..."
                            aria-label="PrimerNombre" aria-describedby="basic-addon1" value="<?php echo $fecha_radicado; ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="col-4 p-1 px-2">
                <div class="card shadow-sm p-3 ">
                    <label for="numero_identidad" class="form-label fw-bold" style="font-size:0.9em;">Identificador de radicación</label>
                    <div class="input-group shadow-sm rounded-2 <?php echo (!empty($id)) ? 'precargado' : ''; ?>">
                        <span class="input-group-text"><i class="bi bi-file-earmark-code-fill"></i></span>
                        <input type="text" class="form-control " style="font-size:0.9em;" id="numero_identidad" name="numero_identidad" placeholder="Ingrese el número de documento..."
                            aria-label="PrimerNombre" aria-describedby="basic-addon1" value="ARB_CUE_<?php echo $id; ?>" readonly>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <div class="row px-2">
        <div class="col-12">
            <div class=" card shadow  col-12 mt-4 px-2 mb-4 border rounded-4" style=" position:relative;  overflow:visible; border: 1px solid #002F55 !important ">
                <div class=" p-2 w-50 text-center text-white rounded-4" style="background-color: #002f55; position:absolute; top:-30px; left:1%">
                    <h6 class="fw-bold mb-0 ">Información personal</h6>
                    <small>Aquí se muestra tu información personal ingresada en la radicación</small>
                </div>
                <div class="row p-4  pt-4">
                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3 ">
                        <label for="DocumentoTipo" class="form-label fw-bold" style="font-size:0.9em;">Tipo de documento</label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($tipo_documento)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text"><i class="bi-person-badge"></i></span>
                            <input type="text" class="form-control " style="font-size:0.9em;" id="DocumentoTipo" name="DocumentoTipo" placeholder="Ingrese el número de documento..."
                                aria-label="TipoDeDocumento" aria-describedby="basic-addon1" value="<?php echo $tipo_documento; ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="numero_identidad" class="form-label fw-bold" style="font-size:0.9em;">Número Documento</label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($numero_identidad)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                            <input type="number" class="form-control " style="font-size:0.9em;" id="numero_identidad" name="numero_identidad" placeholder="Ingrese el número de documento..."
                                aria-label="NumeroDeIdentidad" aria-describedby="basic-addon1" value="<?php echo $numero_identidad; ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="primer_nombre" class="form-label fw-bold" style="font-size:0.9em;">Primer nombre </label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($primer_nombre)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                            <input type="text" class="form-control" style="font-size:0.9em;" id="primer_nombre" name="primer_nombre"
                                placeholder="Ingrese primer nombre..." name="primer_nombre" aria-label="PrimerNombre" value="<?php echo $primer_nombre; ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="segundo_nombre" class="form-label fw-bold" style="font-size:0.9em;">Segundo nombre</label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($segundo_nombre)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" style="font-size:0.9em;" id="segundo_nombre" name="segundo_nombre"
                                placeholder="Ingrese segundo nombre..." name="cert_primer_nombre" aria-label="PrimerNombre" value="<?php echo $segundo_nombre; ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="primer_apellido" class="form-label fw-bold" style="font-size:0.9em;">Primer Apellido </label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($primer_apellido)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi-people-fill"></i></span>
                            <input type="text" class="form-control" style="font-size:0.9em;" id="primer_apellido" placeholder="Ingrese primer apellido..."
                                name="primer_apellido" aria-label="PrimerApellido" aria-describedby="basic-addon1" value="<?php echo $primer_apellido; ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="segundo_apellido" class="form-label fw-bold" style="font-size:0.9em;">Segundo Apellido </label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($segundo_apellido)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi-people"></i></span>
                            <input type="text" class="form-control" style="font-size:0.9em;" id="segundo_apellido" placeholder="Ingrese segundo apellido..."
                                name="segundo_apellido" aria-label="SegundoApellido" value="<?php echo $segundo_apellido; ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="telefono" class="form-label fw-bold" style="font-size:0.9em;">Número telefónico</label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($telefono)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-telephone-fill me-1"></i> +57</span>
                            <input type="text" style="font-size:0.9em;" class="form-control" id="telefono" placeholder="Número telefónico..."
                                name="telefono" aria-label="PrimerNombre" value="<?php echo $telefono; ?>">
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-8 p-1 px-2 my-3">
                        <label for="correo" class="form-label fw-bold" style="font-size:0.9em;">Correo electrónico</label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($correo)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at-fill"></i></span>
                            <input type="text" style="font-size:0.9em;" class="form-control " id="correo" placeholder="Correo electrónico..."
                                name="correo" aria-label="PrimerNombre" value="<?php echo $correo; ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow col-12 mt-5 px-2 mb-4 border rounded-4" style=" position:relative;  overflow:visible; border: 1px solid #05518F !important">
                <div class=" p-2 w-50 text-center text-white rounded-4" style="background-color: #05518F; position:absolute; top:-30px; left:49%">
                    <h6 class="fw-bold mb-0 ">Información del cobro</h6>
                    <small>Datos de la cuenta de cobro</small>
                </div>
                <div class="row p-4 pt-4">
                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="Periodo_Facturacion" class="form-label" style="font-size:0.9em;"><b>Periodo Seleccionado</b></label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($Periodo_Facturacion)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-week"></i></span>
                            <input type="text" style="font-size:0.9em;" class="form-control" id="Periodo_Facturacion" placeholder="Ingresa valor ..."
                                name="Periodo_Facturacion" aria-label="PrimerNombre" value="<?php echo $Periodo_Facturacion; ?>">
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="anio_cuenta" class="form-label" style="font-size:0.9em;"><b>Año</b></label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($anio_cuenta)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-week"></i></span>
                            <input type="text" style="font-size:0.9em;" class="form-control" id="anio_cuenta" placeholder="Ingresa valor ..."
                                name="anio_cuenta" aria-label="PrimerNombre" value="<?php echo $anio_cuenta; ?>">
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="valor" class="form-label fw-bold" style="font-size:0.9em;">Valor a cobrar radicado</label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($valor)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-currency-dollar"></i></span>
                            <input type="text" style="font-size:0.9em;" class="form-control valor" placeholder="Ingresa valor ..."
                                name="valor" aria-label="PrimerNombre" value="<?php echo $valor; ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="Val_Seg_Social" class=" form-label fw-bold" style="font-size:0.9em;">Valor del IBC radicado</label>
                        <div class="input-group shadow-sm shadow-warning rounded-2 border border-warning ">
                            <span class="input-group-text bg-warning" id="basic-addon1"><i class="bi bi-currency-dollar"></i></span>
                            <input type="text" style="font-size:0.9em;" class="form-control valor " id="Val_Seg_Social" placeholder="Valor IBC ..."
                                name="Val_Seg_Social" aria-label="PrimerNombre" value="<?php echo $Val_Seg_Social; ?>" readonly>
                        </div>
                    </div>


                    <!-- script para poner los puntos de los miles y millones en los inputs que correspondan a los montos de dinero  -->
                    <script>
                        const inputs = document.querySelectorAll('.valor');

                        inputs.forEach(input => {

                            let value = input.value.replace(/\D/g, "");
                            input.value = new Intl.NumberFormat('es-CO').format(value);
                        });
                    </script>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="Fecha_Inicio" class="form-label fw-bold" style="font-size:0.9em;">Periodo fecha de inicio seleccionado</label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($Fecha_Inicio)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-event"></i></span>
                            <input type="date" style="font-size:0.9em;" class="form-control" id="Fecha_Inicio" placeholder="Ingresa Fecha_Inicio ..."
                                name="Fecha_Inicio" aria-label="PrimerNombre" value="<?php echo $Fecha_Inicio; ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="Fecha_Final" class="form-label fw-bold" style="font-size:0.9em;">Periodo fecha final seleccionado </label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($Fecha_Final)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-event-fill"></i></span>
                            <input type="date" style="font-size:0.9em;" class="form-control" id="Fecha_Final" placeholder="Ingresa Fecha_Final ..."
                                name="Fecha_Final" aria-label="PrimerNombre" value="<?php echo $Fecha_Final; ?>" readonly>
                        </div>
                    </div>


                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="cant_dias" class="form-label fw-bold" style="font-size:0.9em;">Cantidad de días radicados</label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($cant_dias)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-week"></i></span>
                            <input type="number" style="font-size:0.9em;" class="form-control" id="cant_dias" placeholder="Ingresa cant_dias ..."
                                name="cant_dias" aria-label="cantidaddedias" value="<?php echo $cant_dias; ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="rol" class="form-label fw-bold" style="font-size:0.9em;">Cargo</label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($cargo)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-week"></i></span>
                            <input type="text" style="font-size:0.9em;" class="form-control" id="rol" placeholder="Ingresa rol ..."
                                name="rol" aria-label="cantidaddedias" value="<?php echo $cargo; ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="proyecto" class="form-label fw-bold" style="font-size:0.9em;">Proyecto</label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($proyecto)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-week"></i></span>
                            <input type="text" style="font-size:0.9em;" class="form-control" id="proyecto" placeholder="Ingresa proyecto ..."
                                name="proyecto" aria-label="cantidaddedias" value="<?php echo $proyecto; ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow col-12 mt-5 px-2 mb-4 border rounded-4 " style=" position:relative;  overflow:visible; border: 1px solid #2887D5 !important">
                <div class=" p-2 w-50 text-center text-white rounded-4" style="background-color: #2887D5; position:absolute; top:-30px; left:1%">
                    <h6 class="fw-bold mb-0 ">Documentos obligatorios</h6>
                    <small>visualiza los documentos adjuntados</small>
                </div>

                <div class="row p-4  pt-4 d-flex justify-content-center">

                    <!-- Informe Mensual -->

                    <div class="col-12 col-lg-4 p-1 px-2 my-4">
                        <label class="form-label fw-bold mb-1" style="font-size:0.9em">Informe Mensual</label>

                        <div class="input-group shadow-sm rounded-3">
                            <span class="input-group-text"><i class="bi bi-file-earmark-pdf-fill"></i></span>
                            <input type="text" class="form-control" style="font-size:0.9em"
                                value="<?php echo $row['informe_mensual'] ?? ''; ?>" readonly>
                        </div>

                        <?php if (!empty($row['informe_mensual'])): ?>

                            <div class="d-flex justify-content-center gap-2 my-2">
                                <!-- Botón Abrir modal -->
                                <button type="button" class="bot_mostrar_vista btn btn-sm"
                                    onclick="abrirModalInforme('<?php echo $mostrar_documentos . 'informe_mensual/' . $row['informe_mensual']; ?>')">
                                    <i class="bi bi-eye"></i> Vista Previa
                                </button>
                            </div>

                        <?php else: ?>
                            <span class="text-muted">No se ha cargado ningún documento</span>
                        <?php endif; ?>
                    </div>

                    <!-- cuenta de cobro -->

                    <div class="col-12 col-lg-4 p-1 px-2 my-4">
                        <label class="form-label fw-bold mb-1" style="font-size:0.9em">Cuenta de cobro</label>

                        <div class="input-group shadow-sm rounded-3">
                            <span class="input-group-text"><i class="bi bi-file-earmark-pdf-fill"></i></span>
                            <input type="text" class="form-control" style="font-size:0.9em"
                                value="<?php echo $row['cuenta_cobro'] ?? ''; ?>" readonly>
                        </div>

                        <?php if (!empty($row['cuenta_cobro'])): ?>

                            <?php
                            $ruta_cobro = $mostrar_documentos . "cuenta_de_cobro/" . $row['cuenta_cobro'];
                            ?>

                            <div class="d-flex justify-content-center gap-2 my-2">
                                <!-- Botón Abrir modal -->
                                <button type="button" class="bot_mostrar_vista btn btn-sm"
                                    onclick="abrirModalInforme('<?php echo $ruta_cobro ?>')">
                                    <i class="bi bi-eye"></i> Vista Previa
                                </button>
                            </div>

                        <?php else: ?>
                            <span class="text-muted">No se ha cargado ningún documento</span>
                        <?php endif; ?>
                    </div>

                    <!-- Retención a la fuente -->

                    <div class="col-12 col-lg-4 p-1 px-2 my-4">
                        <label class="form-label fw-bold mb-1" style="font-size:0.9em">Retención a la fuente</label>

                        <div class="input-group shadow-sm rounded-3">
                            <span class="input-group-tddddext"><i class="bi bi-file-earmark-pdf-fill"></i></span>
                            <input type="text" class="form-control" style="font-size:0.9em"
                                value="<?php echo $row['retencion'] ?? ''; ?>" readonly>
                        </div>

                        <?php if (!empty($row['retencion'])): ?>

                            <?php
                            $ruta_periodo = $mostrar_documentos . "retencion_de_la_fuente/" . $row['retencion'];
                            ?>

                            <div class="d-flex justify-content-center gap-2 my-2">
                                <!-- Botón Abrir modal -->
                                <button type="button" class="bot_mostrar_vista btn btn-sm"
                                    onclick="abrirModalInforme('<?php echo $ruta_periodo ?>')">
                                    <i class="bi bi-eye"></i> Vista Previa
                                </button>
                            </div>

                        <?php else: ?>
                            <span class="text-muted">No se ha cargado ningún documento</span>
                        <?php endif; ?>
                    </div>

                    <!-- seguridad social -->
                    <div class="col-12 col-lg-4 p-1 px-2 my-4">
                        <label class="form-label fw-bold mb-1" style="font-size:0.9em">Cert. Seguridad Social</label>

                        <div class="input-group shadow-sm rounded-3">
                            <span class="input-group-text"><i class="bi bi-file-earmark-pdf-fill"></i></span>
                            <input type="text" class="form-control" style="font-size:0.9em"
                                value="<?php echo $row['seguridad_social'] ?? ''; ?>" readonly>
                        </div>

                        <?php if (!empty($row['seguridad_social'])): ?>

                            <?php
                            $ruta_social = $mostrar_documentos . "planilla_comprobante/" . $row['seguridad_social'];
                            ?>

                            <div class="d-flex justify-content-center gap-2 my-2">
                                <!-- Botón Abrir modal -->
                                <button type="button" class="bot_mostrar_vista btn btn-sm"
                                    onclick="abrirModalInforme('<?php echo $ruta_social ?>')">
                                    <i class="bi bi-eye"></i> Vista Previa
                                </button>
                            </div>

                        <?php else: ?>
                            <span class="text-muted">No se ha cargado ningún documento</span>
                        <?php endif; ?>
                    </div>

                    <!-- compilado rut-cc-certificado bancario -->

                    <div class="col-12 col-lg-4 p-1 px-2 my-4">
                        <label class="form-label fw-bold mb-1" style="font-size:0.9em">CC - RUT - Cert. Banco</label>

                        <div class="input-group shadow-sm rounded-3">
                            <span class="input-group-text"><i class="bi bi-file-earmark-pdf-fill"></i></span>
                            <input type="text" class="form-control" style="font-size:0.9em"
                                value="<?php echo $row['primera_vez'] ?? ''; ?>" readonly>
                        </div>

                        <?php if (!empty($row['primera_vez'])): ?>

                            <?php
                            $ruta_primera_vez = $mostrar_documentos . "primera_vez/" . $row['primera_vez'];
                            ?>

                            <div class="d-flex justify-content-center gap-2 my-2">
                                <!-- Botón Abrir modal -->
                                <button type="button" class="bot_mostrar_vista btn btn-sm"
                                    onclick="abrirModalInforme('<?php echo $ruta_primera_vez ?>')">
                                    <i class="bi bi-eye"></i> Vista Previa
                                </button>
                            </div>

                        <?php else: ?>
                            <span class="text-muted">No se ha cargado ningún documento</span>
                        <?php endif; ?>
                    </div>

                    <!-- FUNCIÓN PARA MOSTRAR Y OCULTAR -->
                    <script>
                        function abrirModalInforme(rutaPDF) {
                            const iframe = document.getElementById("iframePDF");
                            iframe.src = rutaPDF;

                            const modal = new bootstrap.Modal(document.getElementById("modalPDF"));
                            modal.show();
                        }
                    </script>
                </div>
            </div>

            <div class="row px-2 mb-4">
                <div class="col-md-12  px-4 d-flex flex-column  justify-content-center">
                    <label for="observacion" style="font-size:1.2em;" class="my-2"><b>Observaciones</b></label>
                    <input class="form-control py-4 rounded-4 shadow" style="font-size:0.8em;  resize: none;" id="observacion" name="observacion" value="<?php echo $row['observacion'] ?>" readonly></input>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- MODAL PARA VISTA PREVIA PDF -->
<div class="modal fade" id="modalPDF" tabindex="-1" style="z-index: 999999;">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; height: 92vh; display:flex; flex-direction:column;">

            <!-- Header -->
            <div class="modal-header">
                <h7 class="modal-title fw-bold" style="color: #002F55;">Vista previa del documento</h7>
                <button type="button" class="btn-close text-white" style="background-color: #002F55;" data-bs-dismiss="modal"></button>
            </div>

            <!-- Cuerpo flexible -->
            <div class="modal-body p-0" style="flex:1; overflow:hidden;">
                <iframe id="iframePDF" width="100%" height="100%" style="border:0;"></iframe>
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn text-white btn-sm" style="background-color: #002F55;" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>



<!-- <div class="container-fluid">
    <h1 class="mt-4">Cuentas Radicadas</h1>
    <div class="card mb-4">
        <div class="card-body">En este módulo esta la cuenta radica por el personal OPS.</div>
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-header">
                    <h2 class="text-center font-weight-light my-4">Radicación de cuentas OPS.</h2>
                </div>
                <div class="card-body">
                    <form>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id">Número de radicado</label>
                                    <input class="form-control" id="id" value="ARB_CUE.<?php echo $id; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="Date_Ingreso">Fecha de Radicación Solicitud</label>
                                    <input class="form-control" id="Date_Ingreso" value="<?php echo date('Y-m-d'); ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tipo_documento">Tipo Documento del usuario</label>
                                    <input class="form-control" id="tipo_documento" value="<?php echo $tipo_documento; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="numero_identidad">Documento de Identidad</label>
                                    <input class="form-control" id="numero_identidad" value="<?php echo $numero_identidad; ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="primer_nombre">Primer Nombre</label>
                                    <input class="form-control" id="primer_nombre" value="<?php echo $primer_nombre; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="segundo_nombre">Segundo Nombre</label>
                                    <input class="form-control" id="segundo_nombre" value="<?php echo $segundo_nombre; ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="primer_apellido">Primer Apellido</label>
                                    <input class="form-control" id="primer_apellido" value="<?php echo $primer_apellido; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="segundo_apellido">Segundo Apellido</label>
                                    <input class="form-control" id="segundo_apellido" value="<?php echo $segundo_apellido; ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="telefono">Número Telefonico</label>
                                    <input class="form-control" id="telefono" value="<?php echo $telefono; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="correo">Correo Electrónico</label>
                                    <input class="form-control" id="correo" value="<?php echo $correo; ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="Fecha_Inicio">Periodo Fecha Inicio</label>
                                    <input class="form-control py-4" id="Fecha_Inicio" value="<?php echo $Fecha_Inicio; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="Fecha_Final">Periodo Fecha Final</label>
                                    <input class="form-control py-4" id="Fecha_Final" value="<?php echo $Fecha_Final; ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="col-md-6">
                                <label for="Periodo_Facturacion">Seleccione Periodo</label>
                                <input class="form-control py-4" id="Periodo_Facturacion" value="<?php echo $Periodo_Facturacion; ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="valor">Ingrese Valor a Cobrar</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input class="form-control py-4" id="valor" value="<?php echo $valor; ?>" readonly>
                                </div>
                            </div>
                        </div>
                        <br>

                        <div class="form-row">
                            <div class="col-md-6">
                                <label for="Val_Seg_Social">VALOR IBC</label>
                                <input class="form-control py-4" id="Val_Seg_Social" value="<?php echo $Val_Seg_Social; ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="cant_dias">Cantidad de Días</label>
                                <input class="form-control py-4" id="cant_dias" value="<?php echo $cant_dias; ?>" readonly>
                            </div>
                        </div>
                        <br>

                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cargo">Cargo</label>
                                    <input class="form-control" id="cargo" value="<?php echo $cargo; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="proyecto">Proyecto</label>
                                    <input class="form-control" id="proyecto" value="<?php echo $proyecto; ?>" readonly>
                                </div>
                            </div>
                        </div>


                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="informe_mensual">Informe mensual</label>
                                    <input class="form-control mb-2" id="informe_mensual" value="<?php echo $row['informe_mensual'] ?>" readonly>
                                    <br>
                                    <?php
                                    if (!empty($row['informe_mensual'])) {
                                        $ruta_informe = $mostrar_documentos . "informe_mensual/" . $row['informe_mensual'];
                                        echo "<iframe src='" . $ruta_informe . "' id='iframe_informe_mensual' style='width: 100%; height: 700px;' frameborder='0'></iframe>";
                                        echo "<a href='" . $ruta_informe . "' target='_blank' class='btn btn-primary btn-sm mt-2'>Abrir en otra pestaña</a>";
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cuenta_cobro">Cuenta de Cobro</label>
                                    <input class="form-control mb-2" id="cuenta_cobro" value="<?php echo $row['cuenta_cobro'] ?>" readonly>
                                    <br>
                                    <?php
                                    if (!empty($row['cuenta_cobro'])) {
                                        $ruta_cobro = $mostrar_documentos . "cuenta_de_cobro/" . $row['cuenta_cobro'];
                                        echo "<iframe src='" . $ruta_cobro . "' id='iframe_cuenta_cobro' style='width: 100%; height: 700px;' frameborder='0'></iframe>";
                                        echo "<a href='" . $ruta_cobro . "' target='_blank' class='btn btn-primary btn-sm mt-2'>Abrir en otra pestaña</a>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <br>
                        <br>
                        <div class="form-row">
                            <div class="col-md-6">
                                <label for="seguridad_social">Seguridad Social</label>
                                <input class="form-control mb-2" id="seguridad_social" value="<?php echo $row['seguridad_social'] ?>" readonly>
                                <br>
                                <?php
                                if (!empty($row['seguridad_social'])) {
                                    $ruta_social = $mostrar_documentos . "planilla_comprobante/" . $row['seguridad_social'];
                                    echo "<iframe src='" . $ruta_social . "' id='iframe_seguridad_social' style='width: 100%; height: 700px;' frameborder='0'></iframe>";
                                    echo "<a href='" . $ruta_social . "' target='_blank' class='btn btn-primary btn-sm mt-2'>Abrir en otra pestaña</a>";
                                }
                                ?>
                            </div>
                            <div class="col-md-6">
                                <label for="retencion">Retención de la Fuente</label>
                                <input class="form-control mb-2" id="retencion" value="<?php echo $row['retencion'] ?>" readonly>
                                <br>
                                <?php
                                if (!empty($row['retencion'])) {
                                    $ruta_periodo = $mostrar_documentos . "retencion_de_la_fuente/" . $row['retencion'];
                                    echo "<iframe src='" . $ruta_periodo . "' id='iframe_retencion' style='width: 100%; height: 700px;' frameborder='0'></iframe>";
                                    echo "<a href='" . $ruta_periodo . "' target='_blank' class='btn btn-primary btn-sm mt-2'>Abrir en otra pestaña</a>";
                                }
                                ?>
                            </div>
                        </div>
                        <br>
                        <br>
                        <div class="form-row">
                            <div class="col-md-6">
                                <label for="primera_vez">Archivos por Primera Vez</label>
                                <input class="form-control mb-2" id="primera_vez" value="<?php echo $row['primera_vez'] ?>" readonly>
                                <br>
                                <?php
                                if (!empty($row['primera_vez'])) {
                                    $ruta_primera_vez = $mostrar_documentos . "primera_vez/" . $row['primera_vez'];
                                    echo "<iframe src='" . $ruta_primera_vez . "' id='iframe_primera_vez' style='width: 100%; height: 500px;' frameborder='0'></iframe>";
                                    echo "<a href='" . $ruta_primera_vez . "' target='_blank' class='btn btn-primary btn-sm mt-2'>Abrir en otra pestaña</a>";
                                }
                                ?>
                            </div>
                            <div class="col-md-6">
                                <label for="observacion">Observaciones</label>
                                <input class="form-control mb-2" id="observacion" value="<?php echo $row['observacion'] ?>" readonly>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div> -->


<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<!-- <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script> -->
<script src="../../../js/scripts.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
<script>
    // Inicializar DataTable
    $(document).ready(function() {
        $('#dataTable').DataTable();
    });
</script>