<?php
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../index.php");
    exit();
}

$idUsuario = (int)$_SESSION['id_usuario'];

// Valida rol
$rolUsuario = $_SESSION['rol_usuario'] ?? '';
$rolesPermitidos = ["soporte"];
if (!in_array($rolUsuario, $rolesPermitidos, true)) {
    header("Location: ../../acceso_denegado.php");
    exit();
}

// Trae datos básicos del usuario (si los usas en la vista)
$stmtUser = $mysqli->prepare("SELECT rol_usuario, nombre_usuario FROM usuarios_cons WHERE id_usuario = ?");
$stmtUser->bind_param("i", $idUsuario);
$stmtUser->execute();
$user = $stmtUser->get_result()->fetch_assoc();
$nombre = $user['nombre_usuario'] ?? '';
$stmtUser->close();

// ID de la solicitud (obligatorio para consulta)
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    // Puedes redirigir o mostrar un mensaje
    echo "ID inválido.";
    exit();
}

// Carga de solicitud
$query = "SELECT * FROM solicitud_baqueanos WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    echo "No se encontraron datos para esta solicitud.";
    exit();
}

$row = $result->fetch_assoc();
$stmt->close();

// Asignación segura
$sb_numero_identidad        = $row['sb_numero_identidad'] ?? '';
$sb_tipo_documento          = $row['sb_tipo_documento'] ?? '';
$sb_baqueano_nombre         = $row['sb_baqueano_nombre'] ?? '';
$sb_baqueano_apellido       = $row['sb_baqueano_apellido'] ?? '';
$sb_telefono_baqueano       = $row['sb_telefono_baqueano'] ?? '';
$sb_correo_baqueano         = $row['sb_correo_baqueano'] ?? '';
$sb_direccion               = $row['sb_direccion'] ?? '';

$sb_year                    = $row['sb_year'] ?? '';
$sb_fecha_inicio            = $row['sb_fecha_inicio'] ?? '';
$sb_fecha_fin               = $row['sb_fecha_fin'] ?? '';
$sb_dias_calculados         = $row['sb_dias_calculados'] ?? '';

$sb_cobro_diario            = (float)($row['sb_cobro_diario'] ?? 0);
$sb_valor_cobrar            = (float)($row['sb_valor_cobrar'] ?? 0);

$sb_unidad_intervencion     = $row['sb_unidad_intervencion'] ?? '';
$sb_unidad_operativa        = $row['sb_unidad_operativa'] ?? '';
$sb_tipo_unidad             = $row['sb_tipo_unidad'] ?? '';
$sb_municipio               = $row['sb_municipio'] ?? '';
$sb_vereda                  = $row['sb_vereda'] ?? '';

$sb_profesional_baqueano    = $row['sb_profesional_baqueano'] ?? '';

$sb_cedula_baqueano         = $row['sb_cedula_baqueano'] ?? null;
$sb_rut_baqueano            = $row['sb_rut_baqueano'] ?? null;
$sb_certificado_baqueano    = $row['sb_certificado_baqueano'] ?? null;
$sb_cuenta_baqueano         = $row['sb_cuenta_baqueano'] ?? null;

$sb_observacion_cuenta      = $row['sb_observacion_cuenta'] ?? '';
$ba_periodo_facturacion     = $row['ba_periodo_facturacion'] ?? '';

$valorFormateado  = '$ ' . number_format($sb_cobro_diario, 0, ',', '.');
$valorFormateado1 = '$ ' . number_format($sb_valor_cobrar, 0, ',', '.');

$mysqli->close();
?>

<div class="container-fluid">
    <h1 class="mt-4">Consulta de radicaciones de solicitudes gerencia</h1>
    <div class="card mb-4">
        <div class="card-body">Vista solo consulta (sin operaciones).</div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-header">
                    <h2 class="text-center font-weight-light my-4">Información de la Cuenta</h2>
                </div>

                <div class="card-body">
                    <!-- Sin action / sin POST -->
                    <form id="formulario_consulta" method="GET" autocomplete="off">
                        <div class="form-row">
                            <div class="col-md-6">
                                <label><b>Seleccione Tipo Documento</b></label>
                                <input class="form-control" value="<?php echo htmlspecialchars($sb_tipo_documento); ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label><b>Número Documento de Identidad</b></label>
                                <input class="form-control" value="<?php echo htmlspecialchars($sb_numero_identidad); ?>" disabled>
                            </div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md-6">
                                <label><b>Nombres</b></label>
                                <input class="form-control" value="<?php echo htmlspecialchars($sb_baqueano_nombre); ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label><b>Apellidos</b></label>
                                <input class="form-control" value="<?php echo htmlspecialchars($sb_baqueano_apellido); ?>" disabled>
                            </div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md-6">
                                <label><b>Número Telefónico</b></label>
                                <input class="form-control" value="<?php echo htmlspecialchars($sb_telefono_baqueano); ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label><b>Correo Electrónico</b></label>
                                <input class="form-control" value="<?php echo htmlspecialchars($sb_correo_baqueano); ?>" disabled>
                            </div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md-6">
                                <label><b>Año</b></label>
                                <input class="form-control" value="<?php echo htmlspecialchars($sb_year); ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label><b>Fecha Inicio</b></label>
                                <input type="date" class="form-control" value="<?php echo htmlspecialchars($sb_fecha_inicio); ?>" disabled>
                            </div>
                            <div class="col-md-6 mt-2">
                                <label><b>Fecha Fin</b></label>
                                <input type="date" class="form-control" value="<?php echo htmlspecialchars($sb_fecha_fin); ?>" disabled>
                            </div>
                            <div class="col-md-6 mt-2">
                                <label><b>Total de Días</b></label>
                                <input class="form-control" value="<?php echo htmlspecialchars($sb_dias_calculados); ?>" disabled>
                            </div>

                            <div class="col-md-6 mt-2">
                                <label class="form-label">Valor Diario</label>
                                <input class="form-control" value="<?php echo htmlspecialchars($valorFormateado); ?>" disabled>
                            </div>

                            <div class="col-md-6 mt-2">
                                <label class="form-label">Total a Cobrar</label>
                                <input class="form-control" value="<?php echo htmlspecialchars($valorFormateado1); ?>" disabled>
                            </div>

                            <div class="col-md-6 mt-2">
                                <label class="form-label">Unidad de Intervención</label>
                                <input class="form-control" value="<?php echo htmlspecialchars($sb_unidad_intervencion); ?>" disabled>
                            </div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md-6">
                                <label class="form-label">Unidad Operativa</label>
                                <input class="form-control" value="<?php echo htmlspecialchars($sb_unidad_operativa); ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo de Unidad</label>
                                <input class="form-control" value="<?php echo htmlspecialchars($sb_tipo_unidad); ?>" disabled>
                            </div>
                            <div class="col-md-6 mt-2">
                                <label class="form-label">Municipio</label>
                                <input class="form-control" value="<?php echo htmlspecialchars($sb_municipio); ?>" disabled>
                            </div>
                            <div class="col-md-6 mt-2">
                                <label class="form-label">Vereda/Barrio</label>
                                <input class="form-control" value="<?php echo htmlspecialchars($sb_vereda); ?>" disabled>
                            </div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md-6">
                                <label><b>Profesional asignado</b></label>
                                <input class="form-control" value="<?php echo htmlspecialchars($sb_profesional_baqueano); ?>" disabled>
                            </div>
                        </div>
                        <hr class="my-4">
                        <!-- Documentos -->
                        <div class="form-row">
                            <div class="col-md-6">
                                <label>Cédula Baqueano</label>
                                <input class="form-control mb-2" value="<?php echo htmlspecialchars((string)$sb_cedula_baqueano); ?>" readonly>
                                <?php if (!empty($sb_cedula_baqueano)) : 
                                    $base_upload_dir = '../Arbimaps/vistas/baqueanos/solicitudes/DOCUMENTOS_BAQUENOS/cuentas_baqueanos/';
                                    $year = (int)($sb_year ?? 0);
                                    $ruta = $base_upload_dir . $year . '/' . $ba_periodo_facturacion . '/RAD_' . $id . '/cedula/' . $sb_cedula_baqueano;
                                ?>
                                    <iframe src="<?php echo htmlspecialchars($ruta); ?>" style="width:100%; height:700px;" frameborder="0"></iframe>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label>RUT</label>
                                <input class="form-control mb-2" value="<?php echo htmlspecialchars((string)$sb_rut_baqueano); ?>" readonly>
                                <?php if (!empty($sb_rut_baqueano)) :
                                    $base_upload_dir = '../Arbimaps/vistas/baqueanos/solicitudes/DOCUMENTOS_BAQUENOS/cuentas_baqueanos/';
                                    $year = (int)($sb_year ?? 0);
                                    $ruta = $base_upload_dir . $year . '/' . $ba_periodo_facturacion . '/RAD_' . $id . '/RUT/' . $sb_rut_baqueano;
                                ?>
                                    <iframe src="<?php echo htmlspecialchars($ruta); ?>" style="width:100%; height:700px;" frameborder="0"></iframe>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-row mt-4">
                            <div class="col-md-6">
                                <label>Certificado Bancario</label>
                                <input class="form-control mb-2" value="<?php echo htmlspecialchars((string)$sb_certificado_baqueano); ?>" readonly>
                                <?php if (!empty($sb_certificado_baqueano)) :
                                    $base_upload_dir = '../Arbimaps/vistas/baqueanos/solicitudes/DOCUMENTOS_BAQUENOS/cuentas_baqueanos/';
                                    $year = (int)($sb_year ?? 0);
                                    $ruta = $base_upload_dir . $year . '/' . $ba_periodo_facturacion . '/RAD_' . $id . '/certificado_bancario/' . $sb_certificado_baqueano;
                                ?>
                                    <iframe src="<?php echo htmlspecialchars($ruta); ?>" style="width:100%; height:700px;" frameborder="0"></iframe>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label>Cuenta de Cobro</label>
                                <input class="form-control mb-2" value="<?php echo htmlspecialchars((string)$sb_cuenta_baqueano); ?>" readonly>
                                <?php if (!empty($sb_cuenta_baqueano)) :
                                    $base_upload_dir = '../Arbimaps/vistas/baqueanos/solicitudes/DOCUMENTOS_BAQUENOS/cuentas_baqueanos/';
                                    $year = (int)($sb_year ?? 0);
                                    $ruta = $base_upload_dir . $year . '/' . $ba_periodo_facturacion . '/RAD_' . $id . '/cuenta_de_cobro/' . $sb_cuenta_baqueano;
                                ?>
                                    <iframe src="<?php echo htmlspecialchars($ruta); ?>" style="width:100%; height:700px;" frameborder="0"></iframe>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-row mt-4">
                            <div class="col-md-12">
                                <label>Observaciones</label>
                                <input class="form-control" value="<?php echo htmlspecialchars($sb_observacion_cuenta); ?>" readonly>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="../../js/scripts.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/js/all.min.js" crossorigin="anonymous"></script>
<script src="https://kit.fontawesome.com/9944c94262.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
