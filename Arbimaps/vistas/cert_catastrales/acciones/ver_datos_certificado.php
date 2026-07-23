<?php
require "../conexion.php";

if (isset($_GET['codigo_certificado'])) {
    $codigo = $_GET['codigo_certificado'];

    // Consulta principal con JOIN
    $sql = "SELECT c.*, p.npn_predio_certificado, p.nombres_propietario, 
               p.tipo_doc_propietario, p.cc_num_propietario
        FROM certificado_catastral c
        LEFT JOIN certificado_propietarios p 
        ON c.codigo_certificado = p.prop_cod_certificado
        WHERE c.codigo_certificado = ?";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $codigo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $row = $resultado->fetch_assoc();
    } else {
        echo "<div class='alert alert-warning'>No se encontró información para este certificado.</div>";
    }
} else {
    echo "<div class='alert alert-danger'>No se proporcionó un código de certificado.</div>";
}

$propietarios = [];
$sqlProp = "SELECT * FROM certificado_propietarios WHERE prop_cod_certificado = ?";
$stmtProp = $mysqli->prepare($sqlProp);
$stmtProp->bind_param("s", $codigo);
$stmtProp->execute();
$resProp = $stmtProp->get_result();

while ($prop = $resProp->fetch_assoc()) {
    $propietarios[] = $prop;
}

?>
<!-- CONTENIDO PAGINA MODIFICACION -->
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-center mb-2">
        <h1 class="h3 text-gray-800 text-center mb-0"><b>Solicitud Certificados Catastrales</b></h1>
    </div>

    <div class="row">

        <!-- col-xl-12 col-lg-7 -> ESTO LO QUE HACE ES DEFINIR EL ESPACIO DE LA TARJETA -->
        <div class="col-md-12">
            <div class="card shadow mb-4"></BR>
                <!-- Card Header - Dropdown -->
                <div class="card-header mx-3 rounded-4" style="background-color: #002F55;">
                    <h5 class="text-white text-center py-1 m-0"><B>DATOS DEL SOLICITANTE / INTERESADO</B></h5>
                </div>
                <div class="card-body">
                    <!--  CAMBIAR EL MODO DE ID, DESACTIVAR PORQUE DEBE SER AUTOMATICO -->
                    <div class="form-row p-2 px-3">
                        <!-- <div class="col-md-6">
                            <label for="codigo_certificado"><b>Código Certificado</b></label>
                            <input type="text" class="form-control" value="<?php echo $row['codigo_certificado']; ?>" readonly>
                        </div> -->

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="codigo_certificado" class="form-label fw-bold" style="font-size:0.9em">Código certificado</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="bi bi-file-earmark-binary"></i></span>
                                <input type="text" class="form-control" style="font-size: 0.9em;" id="codigo_certificado" name="codigo_certificado"
                                    value="<?php echo $row['codigo_certificado']; ?>" readonly>
                            </div>
                        </div>

                        <!-- <div class="col-md-6">
                            <label for="certificado_hora_creacion"><b>Fecha de Solicitud</b></label>
                            <input type="text" class="form-control" value="<?php echo $row['certificado_hora_creacion']; ?>" readonly>
                        </div> -->

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="certificado_hora_creacion" class="form-label fw-bold" style="font-size:0.9em">Fecha de solicitud</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="bi bi-calendar-date"></i></span>
                                <input type="text" class="form-control" style="font-size: 0.9em;" id="certificado_hora_creacion" name="certificado_hora_creacion"
                                    value="<?php echo $row['certificado_hora_creacion']; ?>" readonly>
                            </div>
                        </div>

                        <!-- <div class="col-md-6">
                            <label for="cert_documento_interado"><b>Seleccione Tipo Documento Interesado</b></label>
                            <input type="text" class="form-control" value="<?php echo $row['cert_tipo_documento']; ?>" readonly>
                        </div>  -->

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="cert_documento_interado" class="form-label fw-bold" style="font-size:0.9em">Tipo de documento interesado</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="bi-person-badge"></i></span>
                                <input type="text" class="form-control" style="font-size: 0.9em;" id="cert_documento_interado" name="cert_documento_interado"
                                    value="<?php echo $row['cert_tipo_documento']; ?>" readonly>
                            </div>
                        </div>

                        <!-- <div class="col-md-6">
                            <label for="num_cc_interesado"><b>Número Documento de Identidad Interesado</b></label>
                            <input type="text" class="form-control" value="<?php echo $row['cert_num_cc_interesado']; ?>" readonly>
                        </div> -->

                        <div class="col-md-4  p-1 px-2 my-2">
                            <label for="cert_num_cc_interesado" class="form-label fw-bold" style="font-size:0.9em">Número de documento de identidad interesado</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                                <input type="text" class="form-control" style="font-size: 0.9em;" id="cert_num_cc_interesado" name="cert_num_cc_interesado"
                                    value="<?php echo $row['cert_num_cc_interesado']; ?>" readonly>
                            </div>
                        </div>

                        <!-- <div class="col-md-6">
                            <label for="cert_primer_nombre"><b>Primer Nombre Interesado</b></label>
                            <input type="text" class="form-control" value="<?php echo $row['cert_primer_nombre_interesado']; ?>" readonly>
                        </div>  -->

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="cert_primer_nombre" class="form-label fw-bold" style="font-size:0.9em">Primer nombre del interesado</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                <input type="text" class="form-control" style="font-size: 0.9em;" id="cert_primer_nombre" name="cert_primer_nombre"
                                    value="<?php echo $row['cert_primer_nombre_interesado']; ?>" readonly>
                            </div>
                        </div>

                        <!-- <div class="col-md-6">
                            <label for="cert_segundo_nombre"><b>Segundo Nombre Interesado</b></label>
                            <input type="text" class="form-control" value="<?php echo $row['cert_segundo_nombre_interesado']; ?>" readonly>
                        </div> -->

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="cert_segundo_nombre" class="form-label fw-bold" style="font-size:0.9em">Segundo nombre del interesado</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" style="font-size: 0.9em;" id="cert_segundo_nombre" name="cert_segundo_nombre"
                                    value="<?php echo $row['cert_segundo_nombre_interesado']; ?>" readonly>
                            </div>
                        </div>

                        <!-- <div class="col-md-6">
                            <label for="cert_primer_apellido"><b>Primer Apellido Interesado</b></label>
                            <input type="text" class="form-control" value="<?php echo $row['cert_primer_apellido_interesado']; ?>" readonly>
                        </div> -->

                        <div class="col-md-6 p-1 px-2 my-2">
                            <label for="cert_primer_apellido" class="form-label fw-bold" style="font-size:0.9em">Primer apellido del interesado</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="bi-people-fill"></i></span>
                                <input type="text" class="form-control" style="font-size: 0.9em;" id="cert_primer_apellido" name="cert_primer_apellido"
                                    value="<?php echo $row['cert_primer_apellido_interesado']; ?>" readonly>
                            </div>
                        </div>

                        <!-- <div class="col-md-6">
                            <label for="cert_segundo_apellido"><b>Segundo Apellido Interesado</b></label>
                            <input type="text" class="form-control" value="<?php echo $row['cert_segundo_apellido_interesado']; ?>" readonly>
                        </div> -->

                        <div class="col-md-6 p-1 px-2 my-2">
                            <label for="cert_segundo_apellido" class="form-label fw-bold" style="font-size:0.9em">Segundo apellido del interesado</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="bi-people"></i></span>
                                <input type="text" class="form-control" style="font-size: 0.9em;" id="cert_segundo_apellido" name="cert_segundo_apellido"
                                    value="<?php echo $row['cert_segundo_apellido_interesado']; ?>" readonly>
                            </div>
                        </div>

                        <!-- <div class="col-md-6">
                            <label for="cert_telefono_interesado"><b>Número Telefónico Interesado</b></label>
                            <input type="text" class="form-control" value="<?php echo $row['cert_numero_cel_interesado']; ?>" readonly>
                        </div> -->

                        <div class="col-md-6 p-1 px-2 my-2">
                            <label for="cert_telefono_interesado" class="form-label fw-bold" style="font-size:0.9em">Número telefónico del interesado</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="bi bi-telephone-fill me-2"></i>+57</span>
                                <input type="text" class="form-control" style="font-size: 0.9em;" id="cert_telefono_interesado" name="cert_telefono_interesado"
                                    value="<?php echo $row['cert_numero_cel_interesado']; ?>" readonly>
                            </div>
                        </div>

                        <!-- <div class="col-md-6">
                            <label for="cert_correo_interesado"><b>Correo Electrónico Interesado</b></label>
                            <input type="text" class="form-control" value="<?php echo $row['cert_correo_electronico']; ?>" readonly>
                        </div> -->

                        <div class="col-md-6 p-1 px-2 my-2">
                            <label for="cert_correo_interesado" class="form-label fw-bold" style="font-size:0.9em">Correo electrónico interesado</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="bi bi-envelope-at-fill"></i></span>
                                <input type="text" class="form-control" style="font-size: 0.9em;" id="cert_correo_interesado" name="cert_correo_interesado"
                                    value="<?php echo $row['cert_correo_electronico']; ?>" readonly>
                            </div>
                        </div>

                        <!-- <div class="col-md-6">
                            <label for="cert_sopor_pago"><b>Soporte de Pago</b></label>
                            <input type="text" class="form-control" value="<?php echo $row['cert_medio_envio']; ?>" readonly>
                        </div> -->

                        <div class="col-md-6 p-1 px-2 my-2">
                            <label for="cert_sopor_pago" class="form-label fw-bold" style="font-size:0.9em">Soporte de pago</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="bi bi-file-earmark-richtext"></i></span>
                                <input type="text" class="form-control" style="font-size: 0.9em;" id="cert_sopor_pago" name="cert_sopor_pago"
                                    value="<?php echo $row['cert_medio_envio']; ?>" readonly>
                            </div>
                        </div>

                        <!-- <div class="col-md-6">
                            <label for="cert_medio_envio"><b>Medios de Envio/Entrega</b></label>
                            <input type="text" class="form-control" value="<?php echo $row['cert_medio_envio']; ?>" readonly>
                        </div> -->

                        <div class="col-md-6 p-1 px-2 my-2">
                            <label for="cert_medio_envio" class="form-label fw-bold" style="font-size:0.9em">Medios de envío / entrega</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="bi bi-send-fill"></i></span>
                                <input type="text" class="form-control" style="font-size: 0.9em;" id="cert_medio_envio" name="cert_medio_envio"
                                    value="<?php echo $row['cert_medio_envio']; ?>" readonly>
                            </div>
                        </div>

                        <!-- <div class="col-md-6">
                            <label for="cert_fmi_predio"><b>Busqueda por FMI Predio</b></label>
                            <input type="text" class="form-control" value="<?php echo $row['cert_fmi_predio']; ?>" readonly>
                        </div> -->

                        <div class="col-md-6 p-1 px-2 my-2">
                            <label for="cert_fmi_predio" class="form-label fw-bold" style="font-size:0.9em">Busqueda por FMI</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="bi bi-map-fill"></i></span>
                                <input type="text" class="form-control" style="font-size: 0.9em;" id="cert_fmi_predio" name="cert_fmi_predio"
                                    value="<?php echo $row['cert_fmi_predio']; ?>" readonly>
                            </div>
                        </div>

                        <!-- <div class="col-md-6">
                            <label for="cert_npn_predio"><b>Busqueda por Cod. Catastral Predio</b></label>
                            <input type="text" class="form-control" value="<?php echo $row['cert_npn_predio']; ?>" readonly>
                        </div> -->

                        <div class="col-md-6 p-1 px-2 my-2">
                            <label for="cert_npn_predio" class="form-label fw-bold" style="font-size:0.9em">Busqueda por cod. catastral predio</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="bi bi-map"></i></span>
                                <input type="text" class="form-control" style="font-size: 0.9em;" id="cert_npn_predio" name="cert_npn_predio"
                                    value="<?php echo $row['cert_npn_predio']; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-12 p-1 px-2 my-2">
                            <label class="mb-2"><b>Propietarios del Predio:</b></label>
                            <table class="table table-bordered table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="py-2 text-center text-white" style="background-color: #002F55;">Nombre Propietario</th>
                                        <th class="py-2 text-center text-white" style="background-color: #002F55;">Tipo Documento</th>
                                        <th class="py-2 text-center text-white" style="background-color: #002F55;">Número Documento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($propietarios) > 0) { ?>
                                        <?php foreach ($propietarios as $p) { ?>
                                            <tr>
                                                <td class="py-3 text-center"><?php echo $p['nombres_propietario']; ?></td>
                                                <td class="py-3 text-center"><?php echo $p['tipo_doc_propietario']; ?></td>
                                                <td class="py-3 text-center"><?php echo $p['cc_num_propietario']; ?></td>

                                            </tr>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No hay propietarios registrados para este certificado.</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- <div class="col-md-6">
                            <label for="anio_vigencia"><b>Año Vigencia de Avaluo</b></label>
                            <input type="text" class="form-control" value="<?php echo $row['cert_anio_vigencia']; ?>" readonly>
                        </div> -->

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="anio_vigencia" class="form-label fw-bold" style="font-size:0.9em">Año vigencia del avalúo</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="bi bi-calendar-date"></i></span>
                                <input type="text" class="form-control" style="font-size: 0.9em;" id="anio_vigencia" name="anio_vigencia"
                                    value="<?php echo $row['cert_anio_vigencia']; ?>" readonly>
                            </div>
                        </div>

                        <!-- <div class="col-md-6">
                            <label for="avaluo_terreno_tramite"><b>Valor Avaluo de Terreno</b></label>
                            <input type="text" class="form-control" value="<?php echo $row['cert_avaluo_terreno_tramite']; ?>" readonly>
                        </div> -->

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="avaluo_terreno_tramite" class="form-label fw-bold" style="font-size:0.9em">Valor avalúo de terreno</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="bi bi-piggy-bank"></i></span>
                                <input type="text" class="form-control" style="font-size: 0.9em;" id="avaluo_terreno_tramite" name="avaluo_terreno_tramite"
                                    value="<?php echo $row['cert_avaluo_terreno_tramite']; ?>" readonly>
                            </div>
                        </div>

                        <!-- <div class="col-md-6">
                            <label for="direccion_predio"><b>Dirección Predio</b></label>
                            <input type="text" class="form-control" value="<?php echo $row['cert_direccion_predio']; ?>" readonly>
                        </div> -->

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="direccion_predio" class="form-label fw-bold" style="font-size:0.9em">Dirección predio</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="bi bi-signpost-2-fill"></i></span>
                                <input type="text" class="form-control" style="font-size: 0.9em;" id="direccion_predio" name="direccion_predio"
                                    value="<?php echo $row['cert_direccion_predio']; ?>" readonly>
                            </div>
                        </div>

                        <!-- <div class="col-md-6">
                            <label for="dest_econ_predio"><b>Destino Economico Predio</b></label>
                            <input type="text" class="form-control" value="<?php echo $row['cert_dest_econ_predio']; ?>" readonly>
                        </div> -->

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="dest_econ_predio" class="form-label fw-bold" style="font-size:0.9em">Destino económico del predio</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="bi bi-house-exclamation "></i></span>
                                <input type="text" class="form-control" style="font-size: 0.9em;" id="dest_econ_predio" name="dest_econ_predio"
                                    value="<?php echo $row['cert_dest_econ_predio']; ?>" readonly>
                            </div>
                        </div>

                        <!-- <div class="col-md-6">
                            <label for="area_terreno_predio"><b>Area Terreno Predio</b></label>
                            <input type="text" class="form-control" value="<?php echo $row['cert_area_terreno_predio']; ?>" readonly>
                        </div> -->

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="area_terreno_predio" class="form-label fw-bold" style="font-size:0.9em">Area de terreno predio</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="bi bi-globe-americas"></i></span>
                                <input type="text" class="form-control" style="font-size: 0.9em;" id="area_terreno_predio" name="area_terreno_predio"
                                    value="<?php echo $row['cert_area_terreno_predio']; ?>" readonly>
                            </div>
                        </div>

                        <!-- <div class="col-md-6">
                            <label for="area_construccion_predio"><b>Area Construccion Predio</b></label>
                            <input type="text" class="form-control" value="<?php echo $row['cert_area_construccion_predio']; ?>" readonly>
                        </div> -->

                        <div class="col-md-4 p-1 px-2 my-2">
                            <label for="area_construccion_predio" class="form-label fw-bold" style="font-size:0.9em">Area de construcción del predio</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="bi bi-house-door-fill"></i></span>
                                <input type="text" class="form-control" style="font-size: 0.9em;" id="area_construccion_predio" name="area_construccion_predio"
                                    value="<?php echo $row['cert_area_construccion_predio']; ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-12 text-center mt-4 mb-0 ">
                        <button type="submit" class="btn btn-block text-white" style="background-color: #002F55;">
                            <b><i class="bi bi-file-earmark-arrow-up me-2"></i> GENERAR CERTIFICADO </b>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.container-fluid -->