<?php
$anio_cuenta = $row['anio_cuenta'] ?? null;

$numero_identidad   = $_GET['numero_identidad'] ?? null;
$fecha_subida       = $_GET['fecha_subida'] ?? null;
$tipo_documento     = $_GET['Documento'] ?? null;

$id         = isset($_GET['id']) ? $_GET['id'] : null;
$mes_get    = isset($_GET['mes']) ? $_GET['mes'] : null;


$primer_nombre          = filter_var($_GET['primer_nombre'] ?? '', FILTER_SANITIZE_STRING);
$segundo_nombre         = filter_var($_POST['segundo_nombre'] ?? '', FILTER_SANITIZE_STRING);
$primer_apellido        = filter_var($_POST['primer_apellido'] ?? '', FILTER_SANITIZE_STRING);
$segundo_apellido       = filter_var($_POST['segundo_apellido'] ?? '', FILTER_SANITIZE_STRING);
$telefono               = filter_var($_POST['telefono'] ?? '', FILTER_SANITIZE_STRING);
$correo                 = filter_var($_POST['correo'] ?? '', FILTER_VALIDATE_EMAIL);
$Fecha_Inicio           = filter_var($_POST['Fecha_Inicio'] ?? '');
$Fecha_Final            = filter_var($_POST['Fecha_Final'] ?? '');
$Periodo_Facturacion    = filter_var($_POST['Periodo_Facturacion'] ?? '');
$anio_cuenta            = filter_var($_POST['anio_cuenta'] ?? '');
$valor                  = filter_var($_POST['valor'] ?? '');
$Val_Seg_Social         = filter_var($_POST['Val_Seg_Social'] ?? '');
$cant_dias              = filter_var($_POST['cant_dias'] ?? '');
$cargo                  = $_POST['cargo'] ?? '';
$proyecto               = $_POST['proyecto'] ?? null;
$observacion            = $_POST['observacion'] ?? null;





if ($numero_identidad || $id) {
    if (!empty($id)) {
        $query = "SELECT * FROM cuenta WHERE id = ?";
        $stmt  = $mysqli->prepare($query);
        $stmt->bind_param("i", $id);
    } else {
        if (!empty($mes_get)) {
            $query = "SELECT * FROM cuenta WHERE numero_identidad = ? AND Periodo_Facturacion = ? ORDER BY fecha_subida DESC LIMIT 1";
            $stmt  = $mysqli->prepare($query);
            $stmt->bind_param("ss", $numero_identidad, $mes_get);
        } else {
            $query = "SELECT * FROM cuenta WHERE numero_identidad = ? ORDER BY fecha_subida DESC LIMIT 1";
            $stmt  = $mysqli->prepare($query);
            $stmt->bind_param("s", $numero_identidad);
        }
    }

    $stmt->execute();
    $result = $stmt->get_result();


    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $numero_identidad_cuenta = $row['numero_identidad'] ?? $numero_identidad ?? null;
        $foto_user = null;
        $id_usuario_foto = null;

        if (!empty($numero_identidad_cuenta)) {
            $queryFoto = "SELECT id_usuario, foto_user FROM usuarios_cons WHERE cedula_usuario = ? LIMIT 1";
            $stmtFoto  = $mysqli->prepare($queryFoto);

            if (!$stmtFoto) {
                die("Error al preparar consulta de foto: " . $mysqli->error);
            }

            $stmtFoto->bind_param("s", $numero_identidad_cuenta);

            if (!$stmtFoto->execute()) {
                die("Error al ejecutar consulta de foto: " . $stmtFoto->error);
            }

            $resultFoto = $stmtFoto->get_result();

            if ($resultFoto && $resultFoto->num_rows > 0) {
                $rowFoto        = $resultFoto->fetch_assoc();
                $foto_user      = $rowFoto['foto_user'];     
                $id_usuario_foto = $rowFoto['id_usuario'];   
            }

            $stmtFoto->close();
        }
        $id                = $row['id'] ?? $id;
        $fecha_subida      = $row['fecha_subida'] ?? null;
        $tipo_documento    = $row['tipo_documento'];
        $primer_nombre     = $row['primer_nombre'];
        $segundo_nombre    = $row['segundo_nombre'];
        $primer_apellido   = $row['primer_apellido'];
        $segundo_apellido  = $row['segundo_apellido'];
        $telefono          = $row['telefono'];
        $correo            = $row['correo'];
        $Fecha_Inicio      = $row['Fecha_Inicio'];
        $Fecha_Final       = $row['Fecha_Final'];
        $Periodo_Facturacion = $row['Periodo_Facturacion'];
        $anio_cuenta       = $row['anio_cuenta'];
        $valor             = $row['valor'];
        $Val_Seg_Social    = $row['Val_Seg_Social'] ?? null;
        $cant_dias         = $row['cant_dias'];
        $cargo             = $row['cargo'];
        $proyecto          = $row['proyecto'];
        $observacion       = $row['observacion'];
        $estado            = $row['estado'] ?? null;
        $informe_mensual   = $row['informe_mensual'] ?? null;
        $cuenta_cobro      = $row['cuenta_cobro'] ?? null;
        $cuenta_pagos      = $row['cuenta_pagos'] ?? null;
        $seguridad_social  = $row['seguridad_social'] ?? null;
        $retencion         = $row['retencion'] ?? null;
        $primera_vez       = $row['primera_vez'] ?? null;

        // echo "Datos cargados correctamente.<br>";

        $base_upload_dir  = '../../../../arbimaps/Arbimaps/DOCUMENTOS/modelo_de_cuenta/';
        $carpeta_anio = (!empty($anio_cuenta) && is_numeric($anio_cuenta))
            ? $anio_cuenta
            : 'sin_anio';
        $carpeta_periodo  = $row['Periodo_Facturacion'] ?? $Periodo_Facturacion ?? 'periodo';
        $carpeta_identidad = $row['numero_identidad'] ?? $numero_identidad ?? 'sin_identidad';

        $base_cuenta_dir  = $base_upload_dir . $carpeta_anio . '/' . $carpeta_periodo . '/' . $carpeta_identidad . '/';
    } else {
        echo "No se encontraron datos para esta cédula o id especificado.<br>";
        exit;
    }
    $stmt->close();
} else {
    echo "No se recibió número de identidad ni id.<br>";
    exit;
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
        border: 1px solid #0477d462;
        box-shadow: 0 0 5px rgba(1, 56, 14, 0.24) !important;
    }

    .precargadodos input {
        background-color: #0477d462 !important;
        font-weight: 600;
    }
</style>


<div class="container-fluid">

    <?php if (!empty($_GET['msg'])): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($_GET['msg']); ?></div>
    <?php endif; ?>

    <div class="my-3 text-center">
        <h4 class=" mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 700 !important ">VALIDAR CUENTA</h4>
        <small>Módulo para primera validación en la radicación de cuenta</small>
    </div>


    <div class="container p-2 my-4 px-4">

        <div class="row px-5">
            <div class="col-4 p-1 px-2" style="width: 25%;">
                <div class="card shadow-sm p-3 " style="border: 1px solid #002F55;">
                    <label for="numero_identidad" class="form-label fw-bold" style="font-size:0.9em;">Valor a cobrar</label>
                    <div class="input-group shadow-sm rounded-2 <?php echo (!empty($valor)) ? 'precargadodos' : ''; ?>">
                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                        <input type="text" class="form-control valor " style="font-size:0.9em;" name="numero_identidad" placeholder="Ingrese el número de documento..."
                            aria-label="PrimerNombre" aria-describedby="basic-addon1" value="<?php echo $valor; ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="col-4 p-1 px-2" style="width: 25%;">
                <div class="card shadow-sm p-3 ">
                    <label for="numero_identidad" class="form-label fw-bold" style="font-size:0.9em;">Fecha y hora de radicación</label>
                    <div class="input-group shadow-sm rounded-2 <?php echo (!empty($fecha_subida)) ? 'precargado' : ''; ?>">
                        <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                        <input type="text" class="form-control " style="font-size:0.9em;" id="Date_Ingreso" name="Date_Ingreso" placeholder="Ingrese el número de documento..."
                            aria-label="PrimerNombre" aria-describedby="basic-addon1" value="<?php echo $fecha_subida; ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="col-4 p-1 px-2" style="width: 25%;">
                <div class="card shadow-sm p-3 ">
                    <label for="numero_identidad" class="form-label fw-bold" style="font-size:0.9em;">Identificador de radicación</label>
                    <div class="input-group shadow-sm rounded-2 <?php echo (!empty($id)) ? 'precargado' : ''; ?>">
                        <span class="input-group-text"><i class="bi bi-file-earmark-code-fill"></i></span>
                        <input type="text" class="form-control " style="font-size:0.9em;" id="numero_identidad" name="numero_identidad" placeholder="Ingrese el número de documento..."
                            aria-label="PrimerNombre" aria-describedby="basic-addon1" value="ARB_FEB_<?php echo $id; ?>" readonly>
                    </div>
                </div>
            </div>
            <div class="" style="width: 25%; margin-top: -8%;">
                <div class="card shadow-sm p-3 ">
                    <label for="numero_identidad" class="form-label fw-bold text-center" style="font-size:0.9em;">USUARIO</label>
                    <?php if (!empty($foto_user) && !empty($id_usuario_foto)): ?>
                        <img src="/arbimaps/Arbimaps/assets/fotos_usuarios/<?php echo (int)$id_usuario_foto; ?>/<?php echo htmlspecialchars($foto_user); ?>"
                            alt="Foto de usuario"
                            class="img-thumbnail rounded-circle shadow-sm"
                            style="width: 120px; height: 120px; object-fit: cover;">
                        <p class="mt-2 mb-0" style="font-size:0.8em;">
                            <?php echo htmlspecialchars($primer_nombre . ' ' . $primer_apellido); ?>
                        </p>
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center mb-2"
                            style="width: 120px; height: 120px; margin: 0 auto; border-radius: 50%; border: 1px dashed #ccc;">
                            <i class="bi bi-person fs-1 text-muted"></i>
                        </div>
                        <small class="text-muted d-block text-center">Sin foto de usuario</small>
                    <?php endif; ?>
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
                        <label for="tipo_documento" class="form-label fw-bold" style="font-size:0.9em;">Tipo de documento</label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($tipo_documento)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text"><i class="bi-person-badge"></i></span>
                            <input type="text" class="form-control " style="font-size:0.9em;" id="tipo_documento" name="tipo_documento" placeholder="Ingrese el número de documento..."
                                aria-label="TipoDeDocumento" aria-describedby="basic-addon1" value="<?php echo $tipo_documento; ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="numero_identidad" class="form-label fw-bold" style="font-size:0.9em;">Número Documento</label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($numero_identidad)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                            <input type="text" class="form-control " style="font-size:0.9em;" id="numero_identidad" name="numero_identidad" placeholder="Ingrese el número de documento..."
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
                                name="valor" id="valor" aria-label="PrimerNombre" value="<?php echo $valor; ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="Val_Seg_Social" class=" form-label fw-bold" style="font-size:0.9em;">Valor del IBC radicado</label>
                        <div class="input-group shadow-sm shadow-warning rounded-2 border border-warning ">
                            <span class="input-group-text bg-warning" id="basic-addon1"><i class="bi bi-currency-dollar"></i></span>
                            <input type="text" style="font-size:0.9em;" class="form-control valor " id="Val_Seg_Social" placeholder="Valor IBC ..."
                                name="Val_Seg_Social" aria-label="PrimerNombre" value="<?php echo $Val_Seg_Social; ?>" readonly>
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
                        <label for="cargo" class="form-label fw-bold" style="font-size:0.9em;">Cargo</label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($cargo)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-week"></i></span>
                            <input type="text" style="font-size:0.9em;" class="form-control" id="cargo" placeholder="Ingresa rol ..."
                                name="cargo" aria-label="cantidaddedias" value="<?php echo $cargo; ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 p-1 px-2 my-3">
                        <label for="proyecto" class="form-label fw-bold" style="font-size:0.9em;">Cargo</label>
                        <div class="input-group shadow-sm rounded-2 <?php echo (!empty($proyecto)) ? 'precargado' : ''; ?>">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-week"></i></span>
                            <input type="text" style="font-size:0.9em;" class="form-control" id="proyecto" placeholder="Ingresa proyecto ..."
                                name="proyecto" aria-label="cantidaddedias" value="<?php echo $proyecto; ?>" readonly>
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
                            <input type="text" class="form-control" id="informe_mensual" style="font-size:0.9em"
                                value="<?php echo $row['informe_mensual'] ?? ''; ?>" readonly>
                        </div>

                        <?php if (!empty($row['informe_mensual'])): ?>

                            <?php
                            $ruta_informe = $base_cuenta_dir . "informe_mensual/" . $row['informe_mensual'];
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
                            <input type="text" class="form-control" style="font-size:0.9em" id="cuenta_cobro"
                                value="<?php echo $row['cuenta_cobro'] ?? ''; ?>" readonly>
                        </div>

                        <?php if (!empty($row['cuenta_cobro'])): ?>

                            <?php
                            $ruta_cuenta_cobro = $base_cuenta_dir . "cuenta_de_cobro/" . $row['cuenta_cobro'];
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
                            <input type="text" class="form-control" style="font-size:0.9em" id="retencion"
                                value="<?php echo $row['retencion'] ?? ''; ?>" readonly>
                        </div>

                        <?php if (!empty($row['retencion'])): ?>

                            <?php
                            $ruta_retencion = $base_cuenta_dir . "retencion_de_la_fuente/" . $row['retencion'];
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
                            <input type="text" class="form-control" style="font-size:0.9em" id="seguridad_social"
                                value="<?php echo $row['seguridad_social'] ?? ''; ?>" readonly>
                        </div>

                        <?php if (!empty($row['seguridad_social'])): ?>

                            <?php
                            $ruta_seguridad = $base_cuenta_dir . "planilla_comprobante/" . $row['seguridad_social'];
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
                            <input type="text" class="form-control" style="font-size:0.9em" id="primera_vez"
                                value="<?php echo $row['primera_vez'] ?? ''; ?>" readonly>
                        </div>

                        <?php if (!empty($row['primera_vez'])): ?>

                            <?php
                            $ruta_primera_vez = $base_cuenta_dir . "primera_vez/" . $row['primera_vez'];
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
            <textarea class="form-control p-4 rounded-4 shadow" style="font-size:0.8em; resize:none; height:auto; text-align:center min-height:100px; white-space:pre-wrap;" id="observacion" name="observacion" readonly> <?php echo $row['observacion'] ?></textarea>
        </div>
    </div>


    <?php if (in_array($_SESSION['rol_usuario'], ['director_presupuestos'])): ?>
        <div class="container rounded-4 p-3 shadow text-white mb-3" style="background-color: #002F55;">
            <?php if ($estado !== 'Aprobado'): ?>
                <div class="row">
                    <div colspan="12" class="py-3 text-center">
                        <h5 class="fw-bold">Acciones de validación</h5>
                        <small>Selecciona una de las siguientes acciones para la cuenta radicada</small>
                    </div>

                    <div class="col-6">
                        <div class="card p-3 h-100">
                            <form method="post" class="text-center" action="<?php echo rtrim(dirname($_SERVER['PHP_SELF']), '/'); ?>/vistas/cuentas/radicacion/acciones/aprobar_cuenta_presupuesto.php" onsubmit="return confirm('¿Confirmas aprobar esta cuenta?');">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                                <input type="hidden" name="numero_identidad" value="<?php echo htmlspecialchars($numero_identidad); ?>">
                                <div class="form-group mr-2 d-flex flex-column aling-items-center justify-content-center h-100">
                                    <label for="valor_aprobado" class="valoraprobado mb-2 fw-bold text-center" style="color: #002F55;">Valor aprobado</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="text"
                                            class="form-control"
                                            id="valor_aprobado"
                                            name="valor_aprobado"
                                            step="0.01"
                                            min="0"
                                            placeholder="Ingrese el valor aprobado"
                                            required>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success ml-2"> <i class="bi bi-journal-check me-2"></i> Aprobar</button>
                            </form>

                            <script>
                                document.getElementById("valor_aprobado").addEventListener("input", function() {
                                    let valorLimpio = this.value.replace(/\D/g, "");
                                    let valorCobrar = parseFloat(valorLimpio) || 0;
                                    this.value = new Intl.NumberFormat("es-CO").format(valorCobrar);
                                });
                            </script>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card p-3 ">
                            <form method="post" class="text-center"
                                action="<?php echo rtrim(dirname($_SERVER['PHP_SELF']), '/'); ?>/vistas/cuentas/radicacion/acciones/rechazar_cuenta.php"
                                onsubmit="return confirm('¿Confirmas rechazar esta cuenta? Esta acción no se puede deshacer.');">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                                <input type="hidden" name="numero_identidad" value="<?php echo htmlspecialchars($numero_identidad); ?>">
                                <div class="form-group mr-2 d-flex flex-column aling-items-center justify-content-center">
                                    <label for="razon_rechazo" class="mb-2 fw-bold text-center" style="color: #002F55;">Razón del rechazo</label>
                                    <textarea
                                        class="form-control"
                                        id="razon_rechazo"
                                        name="razon"
                                        style="height: 100px; font-size:0.9em; resize:none;"
                                        rows="3"
                                        placeholder="Describe brevemente por qué se rechaza la cuenta"
                                        required></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger mt-2"> <i class="bi bi-journal-x me-2"></i> Rechazar</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info mt-3">
                    Esta cuenta ya ha sido aprobada, no es posible modificarlo.
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<div class="modal fade" id="modalPDF" tabindex="-1" style="z-index: 999999;">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; height: 92vh; display:flex; flex-direction:column;">
            <div class="modal-header">
                <h7 class="modal-title fw-bold" style="color: #002F55;">Vista previa del documento</h7>
                <button type="button" class="btn-close text-white" style="background-color: #002F55;" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="flex:1; overflow:hidden;">
                <iframe id="iframePDF" width="100%" height="100%" style="border:0;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn text-white btn-sm" style="background-color: #002F55;" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="../../../js/scripts.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>

<script>
    $(document).ready(function() {
        $('#dataTable').DataTable();
    });
</script>