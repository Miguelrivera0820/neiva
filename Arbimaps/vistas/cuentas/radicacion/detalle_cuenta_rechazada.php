<?php
$anio_actual = $row['rec_anio_cuenta'] ?? null;
$numero_identidad = $_GET['id'] ?? null;
if (!$numero_identidad) {
    echo json_encode(["success" => false, "message" => "Número de identidad no proporcionado"]);
    exit();
}

$query = "SELECT 
            tipo_documento,
            numero_identidad, 
            primer_nombre, 
            segundo_nombre, 
            primer_apellido, 
            segundo_apellido, 
            telefono, 
            correo, 
            cargo, 
            proyecto, 
            observacion, 
            informe_mensual,
            valor,
            cuenta_cobro,
            seguridad_social,
            Val_Seg_Social,
            Fecha_Inicio,
            Fecha_Final,
            periodo_facturacion,
            Periodo_Facturacion,
            razon,
            fecha_rechazo,
            retencion, 
            primera_vez,
            rec_anio_cuenta
        FROM cuentas_rechazadas 
        WHERE id = ?";
$stmt = $mysqli->prepare($query);
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Error en la consulta SQL: " . $mysqli->error]);
    exit();
}
$id = intval($numero_identidad);

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $tipo_documento         = $row['tipo_documento'];
    $numero_cedula          = $row['numero_identidad'];
    $primer_nombre          = $row['primer_nombre'];
    $razon                  = $row['razon'] ?? null;
    $segundo_nombre         = $row['segundo_nombre'];
    $primer_apellido        = $row['primer_apellido'];
    $segundo_apellido       = $row['segundo_apellido'];
    $telefono               = $row['telefono'];
    $correo                 = $row['correo'];
    $cargo                  = $row['cargo'];
    $proyecto               = $row['proyecto'];
    $observacion            = $row['observacion'];
    $informe_mensual        = $row['informe_mensual'] ?? null;
    $cuenta_cobro           = $row['cuenta_cobro'] ?? null;
    $valor                  = $row['valor'];
    $valor_seguridad_social = $row['Val_Seg_Social'];
    $pag_Fecha_Inicio       = $row['Fecha_Inicio']          ?? null;
    $pag_Fecha_Final        = $row['Fecha_Final']           ?? null;
    $periodo_facturacion    = $row['periodo_facturacion']   ?? null;
    $Periodo_Facturacion    = $row['Periodo_Facturacion']   ?? null;
    $rec_anio_cuenta        = $row['rec_anio_cuenta']      ?? null;

    $seguridad_social       = $row['seguridad_social'] ?? null;
    $retencion              = $row['retencion'] ?? null;
    $primera_vez            = $row['primera_vez'] ?? null;
    if (!empty($row['fecha_rechazo'])) {
        $ts = strtotime($row['fecha_rechazo']);
        if ($ts !== false) {
            $anio_actual = date('Y', $ts);
        } else {
            $anio_actual = date('Y');
        }
    } else {
        $anio_actual = date('Y');
    }
    $base_ruta_documento    = '../../../../../arbimaps/Arbimaps/DOCUMENTOS/cuentas_rechazadas/modelo_de_cuenta/';
    $carpeta_anio = (!empty($rec_anio_cuenta) && is_numeric($rec_anio_cuenta))
        ? $rec_anio_cuenta
        : 'sin_anio';
    $carpeta_perido         = $row['Periodo_Facturacion']   ?? $Periodo_Facturacion ?? 'periodo';
    $carpeta_identidad      = $row['numero_identidad']      ?? $numero_identidad    ?? 'sin_identidad';
    $mostrar_documento      = $base_ruta_documento . $carpeta_anio . '/' . $carpeta_perido . '/' . $carpeta_identidad . '/';
} else {
    echo "No se encontraron datos para esta cédula.<br>";
    echo json_encode(["success" => false, "message" => "No se encontraron datos para esta cédula"]);
}

$stmt->close();
$mysqli->close();
$pag_cant_dias = null;
if (!empty($pag_Fecha_Inicio) && !empty($pag_Fecha_Final)) {
    try {
        $ini = new DateTime($pag_Fecha_Inicio);
        $fin = new DateTime($pag_Fecha_Final);

        if ($fin < $ini) {
            $pag_cant_dias = 0;
        } else {
            $pag_cant_dias = $ini->diff($fin)->days + 1;
        }
    } catch (Exception $e) {
        $pag_cant_dias = null;
    }
}
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
        border: 1px solid #fb8c00;

    }

    .precargadodos input {
        background-color: #fb8a008e !important;
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
        <h4 class=" mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 700 !important ">DETALLE DE CUENTA RECHAZADA</h4>
        <small>Módulo para consultar detalles de la cuenta rechazada</small>
    </div>

    <div class="container p-2  my-4  ">
        <div class="row px-5 d-flex justify-content-center">
            <div class="col-12 mb-3 p-1 px-3">
                <div class="card shadow-sm p-3 " style="border: 1px solid #fb8c00;">
                    <label for="razon" class="form-label fw-bold" style="font-size:0.9em;">Motivo de la devolución</label>
                    <div class="input-group shadow-sm rounded-2 <?php echo (!empty($razon)) ? 'precargadodos' : ''; ?>">
                        <span class="input-group-text"><i class="bi bi-info-circle-fill"></i></span>
                        <input type="text" class="form-control" style="font-size:0.9em;"
                            id="razon" name="razon"
                            placeholder="Motivo de la devolución..."
                            aria-label="MotivoDevolucion" aria-describedby="basic-addon1"
                            value="<?php echo htmlspecialchars($razon); ?>" readonly>
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
                    <small>Aquí se muestra la información personal del la radicación</small>
                </div>
                <div class="row p-4  pt-4">
                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3 ">
                        <label for="DocumentoTipo" class="form-label fw-bold" style="font-size:0.9em;">Tipo de documento</label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($tipo_documento)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text"><i class="bi-person-badge"></i></span>
                            <input type="text" class="form-control " style="font-size:0.9em;" id="DocumentoTipo" name="DocumentoTipo" placeholder="Ingrese el número de documento..."
                                aria-label="TipoDeDocumento" aria-describedby="basic-addon1" value="<?php echo htmlspecialchars($tipo_documento); ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="numero_identidad" class="form-label fw-bold" style="font-size:0.9em;">Número Documento</label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($numero_cedula)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                            <input type="number" class="form-control " style="font-size:0.9em;" id="numero_identidad" name="numero_identidad" placeholder="Ingrese el número de documento..."
                                aria-label="NumeroDeIdentidad" aria-describedby="basic-addon1" value="<?php echo $numero_cedula; ?>" readonly>
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
                        <label for="rec_anio_cuenta" class="form-label" style="font-size:0.9em;"><b>Año</b></label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($rec_anio_cuenta)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-week"></i></span>
                            <input type="text" style="font-size:0.9em;" class="form-control" id="rec_anio_cuenta" placeholder="Año ..."
                                name="rec_anio_cuenta" aria-label="PrimerNombre" value="<?php echo $rec_anio_cuenta; ?>">
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="valor" class="form-label fw-bold" style="font-size:0.9em;">Valor radicado </label>
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
                                name="Val_Seg_Social" aria-label="PrimerNombre" value="<?php echo $valor_seguridad_social; ?>" readonly>
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
                                name="cant_dias" aria-label="cantidaddedias" value="<?php echo htmlspecialchars($pag_cant_dias ?? ''); ?>" readonly>
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

                            <?php
                            $ruta_informe = $mostrar_documento . "informe_mensual/" . $informe_mensual;
                            ?>

                            <div class="d-flex justify-content-center gap-2 my-2">
                                <a href="<?php echo $ruta_informe ?>"
                                    target="_blank" class="bot_verenotrapesta btn btn-sm">
                                    <i class="bi bi-box-arrow-right"></i> Ver en otra pestaña
                                </a>
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
                                value="<?php echo $row['cuenta_cobro'] ?? ''; ?>" readonly>
                        </div>

                        <?php if (!empty($row['cuenta_cobro'])): ?>

                            <?php
                            $ruta_cuenta_cobro = $mostrar_documento . "cuenta_de_cobro/" . $cuenta_cobro;
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
                                value="<?php echo $row['retencion'] ?? ''; ?>" readonly>
                        </div>

                        <?php if (!empty($row['retencion'])): ?>

                            <?php
                            $ruta_retencion = $mostrar_documento . "retencion_de_la_fuente/" . $retencion;
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
                                value="<?php echo $row['seguridad_social'] ?? ''; ?>" readonly>
                        </div>

                        <?php if (!empty($row['seguridad_social'])): ?>

                            <?php
                            $ruta_seguridad = $mostrar_documento . "planilla_comprobante/" . $seguridad_social;
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
                                value="<?php echo $row['primera_vez'] ?? ''; ?>" readonly>
                        </div>

                        <?php if (!empty($row['primera_vez'])): ?>

                            <?php
                            $ruta_primera_vez = $mostrar_documento . "primera_vez/" . $primera_vez;
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
                    <textarea class="form-control p-4 rounded-4 shadow" style="font-size:0.8em; height:auto; text-align:center min-height:100px; white-space:pre-wrap;" id="observacion" name="observacion" readonly> <?php echo $row['razon'] ?></textarea>
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

<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="../../../js/scripts.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
<script>
    // Inicializar DataTable
    $(document).ready(function() {
        $('#dataTable').DataTable();
    });
</script>