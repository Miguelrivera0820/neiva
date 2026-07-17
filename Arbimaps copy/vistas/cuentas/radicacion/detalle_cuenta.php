<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$anio_actual = $row['anio_cuenta'] ?? null;

$idUsuario = $_SESSION['id_usuario'] ?? 0;
$pag_numero_identidad = $_GET['pag_numero_identidad'] ?? null;
$id_get = isset($_GET['id']) ? (int) $_GET['id'] : null;

$datos_cuentas_pago = null;
$ruta_cuentas_pago  = null;

if ($id_get) {
    $query = "SELECT * FROM cuentas_pagadas WHERE id = ?";
    $stmt  = $mysqli->prepare($query);
    $stmt->bind_param("i", $id_get);
    $stmt->execute();
    $result = $stmt->get_result();
} elseif ($pag_numero_identidad) {
    $query = "SELECT * FROM cuentas_pagadas 
              WHERE pag_numero_identidad = ? 
              ORDER BY pag_fecha_subida DESC 
              LIMIT 1";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $pag_numero_identidad);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = false;
}

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $id = $row['id'];
    $pag_fecha_subida        = $row['pag_fecha_subida']        ?? null;
    $pag_tipo_documento      = $row['pag_tipo_documento']      ?? '';
    $pag_numero_identidad    = $row['pag_numero_identidad']    ?? '';
    $pag_primer_nombre       = $row['pag_primer_nombre']       ?? '';
    $pag_segundo_nombre      = $row['pag_segundo_nombre']      ?? '';
    $pag_primer_apellido     = $row['pag_primer_apellido']     ?? '';
    $pag_segundo_apellido    = $row['pag_segundo_apellido']    ?? '';
    $pag_telefono            = $row['pag_telefono']            ?? '';
    $pag_correo              = $row['pag_correo']              ?? '';
    $pag_Fecha_Inicio        = $row['pag_Fecha_Inicio']        ?? '';
    $pag_Fecha_Final         = $row['pag_Fecha_Final']         ?? '';
    $pag_Periodo_Facturacion = $row['pag_Periodo_Facturacion'] ?? '';
    $pag_anio_cuenta         = $row['pag_anio_cuenta']         ?? '';
    $pag_Valor               = $row['pag_valor']               ?? '';
    $pag_Val_Seg_Social      = $row['pag_Val_Seg_Social']      ?? '';
    $pag_cant_dias           = $row['pag_cant_dias']           ?? '';
    $pag_cargo               = $row['pag_cargo']               ?? '';
    $pag_proyecto            = $row['pag_proyecto']            ?? '';
    $pag_observacion         = $row['pag_observacion']         ?? '';

    $pag_informe_mensual         = $row['pag_informe_mensual']         ?? null;
    $pag_cuenta_cobro            = $row['pag_cuenta_cobro']            ?? null;
    $pag_cuenta_pagos            = $row['pag_cuenta_pagos']            ?? null;
    $pag_seguridad_social        = $row['pag_seguridad_social']        ?? null;
    $pag_retencion               = $row['pag_retencion']               ?? null;
    $pag_primera_vez             = $row['pag_primera_vez']             ?? null;
    $pag_estado_seguridad_social = $row['pag_estado_seguridad_social'] ?? '';
    $pag_estado                  = $row['pag_estado']                  ?? 'Pendiente';
    $pag_estado_final            = $row['pag_estado_final']            ?? '';
    $pag_usuario_id              = $row['pag_usuario_id']              ?? null;
    $pag_valor_aprobado          = $row['pag_valor_aprobado']          ?? null;
    $pag_pagado                  = $row['pag_pagado']                  ?? '';
    $pag_fecha_aprobado          = $row['pag_fecha_aprobado']          ?? null;

    // echo "Datos cargados correctamente.<br>";

    $base_ruta_documento = "../../../../../arbimaps/Arbimaps/DOCUMENTOS/modelo_de_cuenta/";
    $carpeta_anio = (!empty($pag_anio_cuenta) && is_numeric($pag_anio_cuenta))
        ? $pag_anio_cuenta
        : 'sin_anio';
    $carpeta_periodo     = $pag_Periodo_Facturacion ?: 'periodo';
    $carpeta_identidad   = $pag_numero_identidad ?: 'sin_identidad';

    $mostrar_documento = $base_ruta_documento . $carpeta_anio . '/' . $carpeta_periodo . '/' . $carpeta_identidad . '/';
} else {
    echo "No se encontraron datos para esta cédula o ID.<br>";
    $stmt && $stmt->close();
    $mysqli->close();
    exit;
}

$stmt && $stmt->close();
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
        border: 1px solid #28613662;
        box-shadow: 0 0 10px #34c7591e !important;
    }

    .precargadodos input {
        background-color: #34c75933 !important;
        font-weight: 600;
    }
</style>

<div class="container-fluid">
    <div class="my-3 text-center">
        <h4 class=" mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 700 !important ">CUENTAS PAGADAS</h4>
        <small>Módulo para consulta de la cuenta de cobro pagadas al personal OPS </small>
    </div>

    <div class="container p-2 my-4 px-4">
        <div class="row px-5">

            <div class="col-4 p-1 px-2">
                <div class="card shadow-sm p-3 " style="border: 1px solid #28613662;">
                    <label for="numero_identidad" class="form-label fw-bold" style="font-size:0.9em;">Valor cobrado</label>
                    <div class="input-group shadow-sm rounded-2 <?php echo (!empty($id)) ? 'precargadodos' : ''; ?>">
                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                        <input type="text" class="form-control valor " style="font-size:0.9em;" name="numero_identidad" placeholder="Ingrese el número de documento..."
                            aria-label="PrimerNombre" aria-describedby="basic-addon1" value="<?php echo $pag_Valor; ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="col-4 p-1 px-2">
                <div class="card shadow-sm p-3 ">
                    <label for="numero_identidad" class="form-label fw-bold" style="font-size:0.9em;">Fecha y hora de radicación</label>
                    <div class="input-group shadow-sm rounded-2 <?php echo (!empty($pag_fecha_subida)) ? 'precargado' : ''; ?>">
                        <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                        <input type="text" class="form-control " style="font-size:0.9em;" id="numero_identidad" name="numero_identidad" placeholder="Ingrese el número de documento..."
                            aria-label="PrimerNombre" aria-describedby="basic-addon1" value="<?php echo date('Y-m-d'); ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="col-4 p-1 px-2">
                <div class="card shadow-sm p-3 ">
                    <label for="numero_identidad" class="form-label fw-bold" style="font-size:0.9em;">Identificador de radicación</label>
                    <div class="input-group shadow-sm rounded-2 <?php echo (!empty($id)) ? 'precargado' : ''; ?>">
                        <span class="input-group-text"><i class="bi bi-file-earmark-code-fill"></i></span>
                        <input type="text" class="form-control " style="font-size:0.9em;" id="numero_identidad" name="numero_identidad" placeholder="Ingrese el número de documento..."
                            aria-label="PrimerNombre" aria-describedby="basic-addon1" value="<?php echo $id; ?>" readonly>
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
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($pag_tipo_documento)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text"><i class="bi-person-badge"></i></span>
                            <input type="text" class="form-control " style="font-size:0.9em;" id="DocumentoTipo" name="DocumentoTipo" placeholder="Ingrese el número de documento..."
                                aria-label="TipoDeDocumento" aria-describedby="basic-addon1" value="<?php echo $pag_tipo_documento; ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="numero_identidad" class="form-label fw-bold" style="font-size:0.9em;">Número Documento</label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($pag_numero_identidad)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                            <input type="number" class="form-control " style="font-size:0.9em;" id="numero_identidad" name="numero_identidad" placeholder="Ingrese el número de documento..."
                                aria-label="NumeroDeIdentidad" aria-describedby="basic-addon1" value="<?php echo $pag_numero_identidad; ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="primer_nombre" class="form-label fw-bold" style="font-size:0.9em;">Primer nombre </label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($pag_primer_nombre)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                            <input type="text" class="form-control" style="font-size:0.9em;" id="primer_nombre" name="primer_nombre"
                                placeholder="Ingrese primer nombre..." name="primer_nombre" aria-label="PrimerNombre" value="<?php echo $pag_primer_nombre; ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="segundo_nombre" class="form-label fw-bold" style="font-size:0.9em;">Segundo nombre</label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($pag_segundo_nombre)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" style="font-size:0.9em;" id="segundo_nombre" name="segundo_nombre"
                                placeholder="Ingrese segundo nombre..." name="cert_primer_nombre" aria-label="PrimerNombre" value="<?php echo $pag_segundo_nombre; ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="primer_apellido" class="form-label fw-bold" style="font-size:0.9em;">Primer Apellido </label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($pag_primer_apellido)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi-people-fill"></i></span>
                            <input type="text" class="form-control" style="font-size:0.9em;" id="primer_apellido" placeholder="Ingrese primer apellido..."
                                name="primer_apellido" aria-label="PrimerApellido" aria-describedby="basic-addon1" value="<?php echo $pag_primer_apellido; ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="segundo_apellido" class="form-label fw-bold" style="font-size:0.9em;">Segundo Apellido </label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($pag_segundo_apellido)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi-people"></i></span>
                            <input type="text" class="form-control" style="font-size:0.9em;" id="segundo_apellido" placeholder="Ingrese segundo apellido..."
                                name="segundo_apellido" aria-label="SegundoApellido" value="<?php echo $pag_segundo_apellido; ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="telefono" class="form-label fw-bold" style="font-size:0.9em;">Número telefónico</label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($pag_telefono)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-telephone-fill me-1"></i> +57</span>
                            <input type="text" style="font-size:0.9em;" class="form-control" id="telefono" placeholder="Número telefónico..."
                                name="telefono" aria-label="PrimerNombre" value="<?php echo $pag_telefono; ?>">
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-8 p-1 px-2 my-3">
                        <label for="correo" class="form-label fw-bold" style="font-size:0.9em;">Correo electrónico</label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($pag_correo)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-at-fill"></i></span>
                            <input type="text" style="font-size:0.9em;" class="form-control " id="correo" placeholder="Correo electrónico..."
                                name="correo" aria-label="PrimerNombre" value="<?php echo $pag_correo; ?>" readonly>
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
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($pag_Periodo_Facturacion)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-week"></i></span>
                            <input type="text" style="font-size:0.9em;" class="form-control" id="Periodo_Facturacion" placeholder="Ingresa valor ..."
                                name="Periodo_Facturacion" aria-label="PrimerNombre" value="<?php echo $pag_Periodo_Facturacion; ?>">
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="pag_anio_cuenta" class="form-label" style="font-size:0.9em;"><b>Periodo Seleccionado</b></label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($pag_anio_cuenta)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-week"></i></span>
                            <input type="text" style="font-size:0.9em;" class="form-control" id="pag_anio_cuenta" placeholder="Ingresa valor ..."
                                name="pag_anio_cuenta" aria-label="PrimerNombre" value="<?php echo $pag_anio_cuenta; ?>">
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="valor" class="form-label fw-bold" style="font-size:0.9em;">Valor a cobrar radicado</label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($pag_Valor)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-currency-dollar"></i></span>
                            <input type="text" style="font-size:0.9em;" class="form-control valor" placeholder="Ingresa valor ..."
                                name="valor" aria-label="PrimerNombre" value="<?php echo $pag_Valor; ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="Val_Seg_Social" class=" form-label fw-bold" style="font-size:0.9em;">Valor del IBC radicado</label>
                        <div class="input-group shadow-sm shadow-warning rounded-2 border border-warning ">
                            <span class="input-group-text bg-warning" id="basic-addon1"><i class="bi bi-currency-dollar"></i></span>
                            <input type="text" style="font-size:0.9em;" class="form-control valor " id="Val_Seg_Social" placeholder="Valor IBC ..."
                                name="Val_Seg_Social" aria-label="PrimerNombre" value="<?php echo $pag_Val_Seg_Social; ?>" readonly>
                        </div>
                        <!-- <small id="msgIBC" class="p-1 fw-bold mt-1 bg-warning rounded-2 text-center tilt" style="color: #002F55;">⚠ Este valor es un aproximado </small>  -->
                    </div>


                    <!-- script para poner los puntos de los miles y millones en los inputs que correspondan a los montos de dinero  -->
                    <script>
                        const inputs = document.querySelectorAll('.valor');

                        inputs.forEach(input => {
                            // Formatear el valor inicial aunque sea readonly
                            let value = input.value.replace(/\D/g, "");
                            input.value = new Intl.NumberFormat('es-CO').format(value);
                        });
                    </script>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="Fecha_Inicio" class="form-label fw-bold" style="font-size:0.9em;">Periodo fecha de inicio seleccionado</label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($pag_Fecha_Inicio)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-event"></i></span>
                            <input type="date" style="font-size:0.9em;" class="form-control" id="Fecha_Inicio" placeholder="Ingresa Fecha_Inicio ..."
                                name="Fecha_Inicio" aria-label="PrimerNombre" value="<?php echo $pag_Fecha_Inicio; ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="Fecha_Final" class="form-label fw-bold" style="font-size:0.9em;">Periodo fecha final seleccionado </label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($pag_Fecha_Final)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-event-fill"></i></span>
                            <input type="date" style="font-size:0.9em;" class="form-control" id="Fecha_Final" placeholder="Ingresa Fecha_Final ..."
                                name="Fecha_Final" aria-label="PrimerNombre" value="<?php echo $pag_Fecha_Final; ?>" readonly>
                        </div>
                    </div>


                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="cant_dias" class="form-label fw-bold" style="font-size:0.9em;">Cantidad de días radicados</label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($pag_cant_dias)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-week"></i></span>
                            <input type="number" style="font-size:0.9em;" class="form-control" id="cant_dias" placeholder="Ingresa cant_dias ..."
                                name="cant_dias" aria-label="cantidaddedias" value="<?php echo $pag_cant_dias; ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="rol" class="form-label fw-bold" style="font-size:0.9em;">Cargo</label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($pag_cargo)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-week"></i></span>
                            <input type="text" style="font-size:0.9em;" class="form-control" id="rol" placeholder="Ingresa rol ..."
                                name="rol" aria-label="cantidaddedias" value="<?php echo $pag_cargo; ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="proyecto" class="form-label fw-bold" style="font-size:0.9em;">Cargo</label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($pag_proyecto)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-week"></i></span>
                            <input type="text" style="font-size:0.9em;" class="form-control" id="proyecto" placeholder="Ingresa proyecto ..."
                                name="proyecto" aria-label="cantidaddedias" value="<?php echo $pag_proyecto; ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow col-12 mt-5 px-2 mb-4 border rounded-4" style=" position:relative;  overflow:visible; border: 1px solid #2887D5 !important">
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
                                value="<?php echo $row['pag_informe_mensual'] ?? ''; ?>" readonly>
                        </div>

                        <?php if (!empty($row['pag_informe_mensual'])): ?>

                            <?php
                            $ruta_informe = $mostrar_documento . "informe_mensual/" . $pag_informe_mensual;
                            ?>

                            <div class="d-flex justify-content-center gap-2 my-2">
                                <!-- Botón Abrir modal -->
                                <button type="button" class="bot_mostrar_vista btn btn-sm"
                                    onclick="abrirModalInforme('<?php echo $ruta_informe; ?>')">
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
                                value="<?php echo $row['pag_cuenta_cobro'] ?? ''; ?>" readonly>
                        </div>

                        <?php if (!empty($row['pag_cuenta_cobro'])): ?>

                            <?php
                            $ruta_cuenta_cobro = $mostrar_documento . "cuenta_de_cobro/" . $pag_cuenta_cobro;
                            ?>

                            <div class="d-flex justify-content-center gap-2 my-2">
                                <!-- Botón Abrir modal -->
                                <button type="button" class="bot_mostrar_vista btn btn-sm"
                                    onclick="abrirModalInforme('<?php echo $ruta_cuenta_cobro ?>')">
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
                            <span class="input-group-text"><i class="bi bi-file-earmark-pdf-fill"></i></span>
                            <input type="text" class="form-control" style="font-size:0.9em"
                                value="<?php echo $row['pag_retencion'] ?? ''; ?>" readonly>
                        </div>

                        <?php if (!empty($row['pag_retencion'])): ?>

                            <?php
                            $ruta_retencion = $mostrar_documento . "retencion_de_la_fuente/" . $pag_retencion;
                            ?>

                            <div class="d-flex justify-content-center gap-2 my-2">
                                <!-- Botón Abrir modal -->
                                <button type="button" class="bot_mostrar_vista btn btn-sm"
                                    onclick="abrirModalInforme('<?php echo $ruta_retencion ?>')">
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
                                value="<?php echo $row['pag_seguridad_social'] ?? ''; ?>" readonly>
                        </div>

                        <?php if (!empty($row['pag_seguridad_social'])): ?>

                            <?php
                            $ruta_seguridad = $mostrar_documento . "planilla_comprobante/" . $pag_seguridad_social;
                            ?>

                            <div class="d-flex justify-content-center gap-2 my-2">
                                <!-- Botón Abrir modal -->
                                <button type="button" class="bot_mostrar_vista btn btn-sm"
                                    onclick="abrirModalInforme('<?php echo $ruta_seguridad ?>')">
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
                                value="<?php echo $row['pag_primera_vez'] ?? ''; ?>" readonly>
                        </div>

                        <?php if (!empty($row['pag_primera_vez'])): ?>

                            <?php
                            $ruta_primera_vez = $mostrar_documento . "primera_vez/" . $pag_primera_vez;
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
        </div>
    </div>

    <div class="row px-2 mb-4">
        <div class="col-md-12  px-4 d-flex flex-column  justify-content-center">
            <label for="observacion" style="font-size:1.2em;" class="my-2"><b>Observaciones</b></label>
            <textarea class="form-control p-4 rounded-4 shadow" style="font-size:0.8em; height:auto; text-align:center min-height:100px; white-space:pre-wrap;" id="observacion" name="observacion" readonly> <?php echo $row['pag_observacion'] ?></textarea>
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
                                    <label for="pag_fecha_subida">Fecha de Radicación Solicitud</label>
                                    <input class="form-control" id="pag_fecha_subida" value="<?php echo $pag_fecha_subida; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="numero_identidad">ID Radicación</label>
                                    <input class="form-control" id="id" value="<?php echo $id; ?>" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tipo_documento">Tipo Documento del usuario</label>
                                    <input class="form-control" id="pag_tipo_documento" value="<?php echo $pag_tipo_documento; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="numero_identidad">Documento de Identidad</label>
                                    <input class="form-control" id="pag_numero_identidad" value="<?php echo $pag_numero_identidad; ?>" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pag_primer_nombre">Primer Nombre</label>
                                    <input class="form-control" id="pag_primer_nombre" value="<?php echo $pag_primer_nombre; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pag_segundo_nombre">Segundo Nombre</label>
                                    <input class="form-control" id="pag_segundo_nombre" value="<?php echo $pag_segundo_nombre; ?>" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pag_primer_apellido">Primer Apellido</label>
                                    <input class="form-control" id="pag_primer_apellido" value="<?php echo $pag_primer_apellido; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pag_segundo_apellido">Segundo Apellido</label>
                                    <input class="form-control" id="pag_segundo_apellido" value="<?php echo $pag_segundo_apellido; ?>" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pag_telefono">Número Telefonico</label>
                                    <input class="form-control" id="pag_telefono" value="<?php echo $pag_telefono; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pag_correo">Correo Electrónico</label>
                                    <input class="form-control" id="pag_correo" value="<?php echo $pag_correo; ?>" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pag_Fecha_Inicio">Periodo Fecha Inicio</label>
                                    <input class="form-control py-4" id="pag_Fecha_Inicio" value="<?php echo $pag_Fecha_Inicio; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pag_Fecha_Final">Periodo Fecha Final</label>
                                    <input class="form-control py-4" id="pag_Fecha_Final" value="<?php echo $pag_Fecha_Final; ?>" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pag_Periodo_Facturacion">Seleccione Periodo</label>
                                    <input class="form-control py-4" id="pag_Periodo_Facturacion" value="<?php echo $pag_Periodo_Facturacion; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="pag_valor">Ingrese Valor a Cobrar</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input class="form-control py-4" id="pag_valor" value="<?php echo $pag_Valor; ?>" readonly>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-row">
                            <div class="col-md-6">
                                <label for="pag_Val_Seg_Social">VALOR IBC</label>
                                <input class="form-control py-4" id="pag_Val_Seg_Social" value="<?php echo $pag_Val_Seg_Social; ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="pag_cant_dias">Cantidad de Días</label>
                                <input class="form-control py-4" id="pag_cant_dias" value="<?php echo $pag_cant_dias; ?>" readonly>
                            </div>
                        </div>
                        <br>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pag_cargo">Cargo</label>
                                    <input class="form-control" id="pag_cargo" value="<?php echo $pag_cargo; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pag_proyecto">Proyecto</label>
                                    <input class="form-control" id="pag_proyecto" value="<?php echo $pag_proyecto; ?>" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pag_informe_mensual">Informe mensual</label>
                                    <input class="form-control mb-2" id="pag_informe_mensual" value="<?php echo htmlspecialchars($pag_informe_mensual); ?>" readonly>
                                    <br>
                                    <?php
                                    if (!empty($pag_informe_mensual)) {
                                        $ruta_informe = $mostrar_documento . "informe_mensual/" . $pag_informe_mensual;
                                        echo "<iframe src='" . $ruta_informe . "' id='iframe_informe_mensual' style='width: 100%; height: 500px;' frameborder='0'></iframe>";
                                        echo "<a href='" . $ruta_informe . "' target='_blank' class='btn btn-primary btn-sm mt-2'>Abrir en otra pestaña</a>";
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pag_cuenta_cobro">Cuenta de Cobro</label>
                                    <input class="form-control mb-2" id="pag_cuenta_cobro" value="<?php echo htmlspecialchars($pag_cuenta_cobro); ?>" readonly>
                                    <br>
                                    <?php
                                    if (!empty($pag_cuenta_cobro)) {
                                        $ruta_cuenta_cobro = $mostrar_documento . "cuenta_de_cobro/" . $pag_cuenta_cobro;
                                        echo "<iframe src='" . $ruta_cuenta_cobro . "' id='iframe_cuenta_cobro' style='width: 100%; height: 500px;' frameborder='0'></iframe>";
                                        echo "<a href='" . $ruta_cuenta_cobro . "' target='_blank' class='btn btn-primary btn-sm mt-2'>Abrir en otra pestaña</a>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pag_seguridad_social">Seguridad Social</label>
                                    <input class="form-control mb-2" id="pag_seguridad_social" value="<?php echo htmlspecialchars($pag_seguridad_social); ?>" readonly>
                                    <br>
                                    <?php
                                    if (!empty($pag_seguridad_social)) {
                                        $ruta_seguridad = $mostrar_documento . "planilla_comprobante/" . $pag_seguridad_social;
                                        echo "<iframe src='" . $ruta_seguridad . "' id='iframe_seguridad_social' style='width: 100%; height: 500px;' frameborder='0'></iframe>";
                                        echo "<a href='" . $ruta_seguridad . "' target='_blank' class='btn btn-primary btn-sm mt-2'>Abrir en otra pestaña</a>";
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pag_retencion">Retención de la Fuente</label>
                                    <input class="form-control mb-2" id="pag_retencion" value="<?php echo htmlspecialchars($pag_retencion); ?>" readonly>
                                    <br>
                                    <?php
                                    if (!empty($pag_retencion)) {
                                        $ruta_retencion = $mostrar_documento . "retencion_de_la_fuente/" . $pag_retencion;
                                        echo "<iframe src='" . $ruta_retencion . "' id='iframe_retencion' style='width: 100%; height: 500px;' frameborder='0'></iframe>";
                                        echo "<a href='" . $ruta_retencion . "' target='_blank' class='btn btn-primary btn-sm mt-2'>Abrir en otra pestaña</a>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <label for="pag_observacion">Observaciones</label>
                                <input class="form-control mb-2" id="pag_observacion" value="<?php echo $row['pag_observacion'] ?>" readonly>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> -->


<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="../../../js/scripts.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>