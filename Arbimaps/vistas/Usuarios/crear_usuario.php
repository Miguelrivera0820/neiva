<style>
</style>
<div class="container-fluid">
    <div class=" text-center my-4">
        <h1 class="h3 text-gray-800 text-center mb-2"><b>REGISTRAR USUARIO</b></h1>
        <small class="text-center">Ingresa los siguientes datos para registrar un nuevo usuario</small>
    </div>
    <div class="row">
        <div class="card shadow-sm p-1 ">
            <div class="card-body p-3">
                <form id="miFormulario" action="./vistas/Usuarios/acciones/insertar_usuario.php" method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class=" col-md-2 d-flex shadow-sm flex-column justify-content-center text-center rounded-4" style="background-color: #002F55;">
                            <!-- Imagen redonda que actúa como input -->
                            <label for="foto_perfil" class="d-inline-block">
                                <img id="preview"
                                    src="assets/img/undraw_profile.svg"
                                    alt="Foto de perfil"
                                    class="rounded-circle border shadow-sm"
                                    style="width: 8em; height: 8em; object-fit: cover; cursor: pointer; border:2px solid white">
                            </label>
                            <!-- Input de archivo oculto -->
                            <input type="file" id="foto_perfil" name="foto_user" accept="image/*" class="d-none">
                            <small class="mt-2 text-white">Haz clic en la imagen para seleccionar tu foto</small>
                            <script>
                                // Previsualizar la imagen antes de subir
                                const inputFile = document.getElementById("foto_perfil");
                                const preview = document.getElementById("preview");
                                inputFile.addEventListener("change", function() {
                                    const file = this.files[0];
                                    if (file) {
                                        const reader = new FileReader();
                                        reader.onload = function(e) {
                                            preview.setAttribute("src", e.target.result);
                                        }
                                        reader.readAsDataURL(file);
                                    }
                                });
                            </script>
                        </div>
                        <div class="col-md-10 p-3">
                            <div class="row p-3">
                                <div class="col-md-6 p-1 px-2 my-1">
                                    <label for="nombres_usuario" class="form-label fw-bold">Nombres de usuario</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-circle"></i></span>
                                        <input type="text" class="form-control" id="nombres_usuario"
                                            name="nombres_usuario" aria-label="PrimerNombre" redondly placeholder="Ingrese primer y segundo nombre...">
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-1">
                                    <label for="apellidos_usuario" class="form-label fw-bold">Apellidos de usuario</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-fill"></i></span>
                                        <input type="text" class="form-control" id="apellidos_usuario"
                                            name="apellidos_usuario" aria-label="PrimerNombre" redondly placeholder="Ingrese primer y segundo apellido...">
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-1">
                                    <label for="num_identidad_usuario" class="form-label fw-bold">Número de documento de identidad</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-vcard"></i></span>
                                        <input type="number" class="form-control" id="num_identidad_usuario"
                                            name="num_identidad_usuario" aria-label="PrimerNombre" redondly placeholder="Ingrese el número de documento de identidad...">
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-1">
                                    <label for="correo_usuario" class="form-label fw-bold">Correo electrónico</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-envelope-plus"></i></span>
                                        <input type="text" class="form-control" id="correo_usuario"
                                            name="correo_usuario" aria-label="PrimerNombre" redondly placeholder="Ingrese el correo electrónico...">
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-1">
                                    <label for="usuario_plataforma" class="form-label fw-bold">Usuario de plataforma</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-bounding-box"></i></span>
                                        <input type="text" class="form-control" id="usuario_plataforma"
                                            name="usuario_plataforma" aria-label="PrimerNombre" redondly placeholder="Ingrese usuario de logueo en la plataforma...">
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-1">
                                    <label for="celular_usuario" class="form-label fw-bold">Número de celular</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-phone"></i></span>
                                        <input type="number" class="form-control" id="celular_usuario"
                                            name="celular_usuario" aria-label="Celular" placeholder="Ingrese el número de celular...">
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-1 ">
                                    <label for="cert_documento_interado" class="form-label"><b>Seleccione el rol</b></label>
                                    <div class="input-group shadow-sm">
                                        <label class="input-group-text" for="cert_documento_interado">
                                            <i class="bi bi-person-video3"></i>
                                        </label>
                                        <select class="form-select" id="cert_documento_interado" name="rol_usuario" required>
                                            <option value="" disabled selected>Seleccione el rol</option>
                                            <option value="atencion_procedencia">Atención Procedencia</option>
                                            <option value="componente_economico">Componente Economico</option>
                                            <option value="control_calidad">Control de Calidad</option>
                                            <option value="consolidacion">Consolidación</option>
                                            <option value="coordinacion_tecnica">Coordinación Tecnica</option>
                                            <option value="director_catastro">Director Catastro</option>
                                            <option value="editor">Editor</option>
                                            <option value="reconocedor">Reconocedor Predial</option>
                                            <option value="avaluos">Perito Avaluador</option>
                                            <option value="procedencia_juridica">Procedencia Juridica</option>
                                            <option value="revision_juridica">Revisión Juridica</option>
                                            <option value="ventanilla_catastral">Ventanilla Catastral</option>
                                            <option value="usuarios_ops">Usuario OPS</option>
                                            <option value="social">Social</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-1">
                                    <label for="password_plataforma" class="form-label fw-bold">Contraseña de ingreso</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-unlock-fill"></i></span>
                                        <input type="password" class="form-control" id="password_plataforma"
                                            name="password_plataforma" aria-label="Contraseña" placeholder="Ingrese la contraseña...">
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-1">
                                    <label for="confirmar_password" class="form-label fw-bold">Confirmar contraseña</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-unlock"></i></span>
                                        <input type="password" class="form-control" id="confirmar_password"
                                            name="confirmar_password" aria-label="Confirmar Contraseña" placeholder="Confirme la contraseña...">
                                    </div>
                                </div>

                                <div class="form-group mt-4 mb-0 text-center">
                                    <button type="submit" class="btn btn-block text-white" style="background-color: #002F55;">
                                        <b><i class="bi bi-person-fill-add me-2"></i> CREAR USUARIO </b>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>