<?php
require_once __DIR__ . '/funciones_productos.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!usuarioProductoTieneRol(['ventanilla_catastral', 'administrador'])) {
    echo '<div class="alert alert-danger">No tiene permisos para crear solicitudes de productos catastrales.</div>';
    return;
}
?>
<style>
    .card-especial {
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid #e3e6f0;
    }

    .card-header-especial {
        background-color: #002F55;
        color: #ffffff;
        border-radius: 14px 14px 0 0;
    }

    .form-label {
        font-weight: 700;
        font-size: 0.95rem;
    }

    .input-group-text {
        background-color: #f8f9fa;
        border-right: 0;
    }

    .form-control,
    .form-select {
        border-left: 0;
        border-radius: 0 0.5rem 0.5rem 0;
    }

    .form-text-small {
        font-size: 0.85rem;
        color: #6c757d;
    }

    .page-title {
        letter-spacing: 0.04em;
    }

    .card-body {
        background-color: #ffffff;
    }

    .input-group.shadow-sm {
        border-radius: 0.75rem;
        overflow: hidden;
    }
</style>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow card-especial">
                <div class="card-header card-header-especial text-center py-4">
                    <h2 class="h4 mb-1 page-title">Solicitud de productos catastrales</h2>
                    <p class="mb-0 text-white-50">Complete los datos del interesado y adjunte el soporte de pago.</p>
                </div>
                <div class="card-body px-4 py-4">
                    <form id="miFormulario" action="vistas/prod_catastrales/acciones/insertar_sol_prod.php" method="post" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-lg-6 col-md-6">
                                <label for="cert_documento_interado" class="form-label">Tipo de documento</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                    <select class="form-select" id="cert_documento_interado" name="cert_documento_interado" required>
                                        <option value="" disabled selected>Selecciona el tipo de documento</option>
                                        <option value="Cedula_Ciudadania">Cédula de ciudadanía</option>
                                        <option value="Cedula_Extranjeria">Cédula de extranjería</option>
                                        <option value="NIT">N.I.T.</option>
                                        <option value="Pasaporte">Pasaporte</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="num_cc_interesado" class="form-label">Número documento de identidad</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-person-vcard-fill"></i></span>
                                    <input type="number" class="form-control" id="num_cc_interesado" name="num_cc_interesado" placeholder="Ingrese el número de documento..." required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="cert_primer_nombre" class="form-label">Primer nombre</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                    <input type="text" class="form-control" id="cert_primer_nombre" name="cert_primer_nombre" placeholder="Ingrese primer nombre..." required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="cert_segundo_nombre" class="form-label">Segundo nombre</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="cert_segundo_nombre" name="cert_segundo_nombre" placeholder="Ingrese segundo nombre...">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="cert_primer_apellido" class="form-label">Primer apellido</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-person-lines-fill"></i></span>
                                    <input type="text" class="form-control" id="cert_primer_apellido" name="cert_primer_apellido" placeholder="Ingrese primer apellido..." required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="cert_segundo_apellido" class="form-label">Segundo apellido</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-person-bounding-box"></i></span>
                                    <input type="text" class="form-control" id="cert_segundo_apellido" name="cert_segundo_apellido" placeholder="Ingrese segundo apellido...">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="cert_telefono_interesado" class="form-label">Número telefónico</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text">+57</span>
                                    <input type="text" class="form-control" id="cert_telefono_interesado" name="cert_telefono_interesado" placeholder="Ingrese número telefónico...">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="cert_correo_interesado" class="form-label">Correo electrónico</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-envelope-at-fill"></i></span>
                                    <input type="email" class="form-control" id="cert_correo_interesado" name="cert_correo_interesado" placeholder="Ingrese correo electrónico...">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="prod_tipo_producto" class="form-label">Tipo de producto catastral</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-card-checklist"></i></span>
                                    <select class="form-select" id="prod_tipo_producto" name="prod_tipo_producto" required>
                                        <option value="" disabled selected>Selecciona el tipo de producto</option>
                                        <option value="Carta_Catastral_Rural">Carta catastral rural</option>
                                        <option value="Carta_Catastral_Urbana">Carta catastral urbana</option>
                                        <option value="Plano_Predial_Catastral">Plano predial catastral</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="cert_sopor_pago" class="form-label">Soporte de pago</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text"><i class="bi bi-cloud-upload-fill"></i></span>
                                    <input type="file" class="form-control" id="cert_sopor_pago" name="cert_sopor_pago" accept="application/pdf">
                                </div>
                                <div class="form-text form-text-small">Solo se permiten archivos PDF de hasta 20 MB.</div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col text-end">
                                <button type="submit" class="btn btn-success px-4">Enviar solicitud</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
