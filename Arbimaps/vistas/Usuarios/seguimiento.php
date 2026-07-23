<?php
require_once dirname(__DIR__, 3) . '/conexion.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/config/permisos.php';

neiva_require_auth();
neiva_require_methods('GET');
neiva_require_permission('menu.usuarios', $PERMISOS);

$id_usuario = $_GET['id_usuario'] ?? '';
if (empty($id_usuario)) {
    neiva_abort(400, 'Falta el identificador del usuario.');
}

$sql = "SELECT id_usuario, nombre_usuario, apellido_usuario, cedula_usuario, correo_usuario, celular_usuario, usuario_cons, password_cons, rol_usuario, foto_user
        FROM usuarios_cons
        WHERE id_usuario = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    neiva_abort(404, 'Usuario no encontrado.');
}

$usuario = $result->fetch_assoc();

$img_src = "assets/img/undraw_profile.svg";
if (!empty($usuario['foto_user'])) {
    $img_src = "data:image/jpeg;base64," . base64_encode($usuario['foto_user']);
}
?>
<div class="container-fluid">
    <div class="text-center my-4">
        <h1 class="h3 text-gray-800 text-center mb-2"><b>EDITAR USUARIO</b></h1>
        <small class="text-center">Actualiza los datos del usuario</small>
    </div>

    <div class="row">
        <div class="card shadow-sm p-1">
            <div class="card-body p-3">
                <form id="miFormulario" action="./vistas/Usuarios/acciones/actualizar_usuario.php" method="POST" enctype="multipart/form-data">

                    <!-- Enviamos el id_usuario para el UPDATE -->
                    <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($usuario['id_usuario']); ?>">

                    <div class="form-row">
                        <div class="col-md-2 d-flex shadow-sm flex-column justify-content-center text-center rounded-4" style="background-color: #002F55;">
                            <label for="foto_perfil" class="d-inline-block">
                                <img id="preview"
                                     src="<?php echo $img_src; ?>"
                                     alt="Foto de perfil"
                                     class="rounded-circle border shadow-sm"
                                     style="width: 8em; height: 8em; object-fit: cover; cursor: pointer; border:2px solid white">
                            </label>

                            <input type="file" id="foto_perfil" name="foto_user" accept="image/*" class="d-none">
                            <small class="mt-2 text-white">Haz clic para cambiar la foto (opcional)</small>

                            <script>
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
                                    <label class="form-label fw-bold">Nombres de usuario</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-circle"></i></span>
                                        <input type="text" class="form-control" name="nombres_usuario"
                                               value="<?php echo htmlspecialchars($usuario['nombre_usuario']); ?>"
                                               placeholder="Ingrese nombres..." required>
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-1">
                                    <label class="form-label fw-bold">Apellidos de usuario</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                        <input type="text" class="form-control" name="apellidos_usuario"
                                               value="<?php echo htmlspecialchars($usuario['apellido_usuario']); ?>"
                                               placeholder="Ingrese apellidos..." required>
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-1">
                                    <label class="form-label fw-bold">Número de documento</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                                        <!-- Si NO quieres que lo cambien, déjalo readonly -->
                                        <input type="number" class="form-control" name="num_identidad_usuario"
                                               value="<?php echo htmlspecialchars($usuario['cedula_usuario']); ?>"
                                               readonly>
                                    </div>
                                    <small class="text-muted">El documento es el ID del usuario (no se puede cambiar).</small>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-1">
                                    <label class="form-label fw-bold">Correo electrónico</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-envelope-plus"></i></span>
                                        <input type="email" class="form-control" name="correo_usuario"
                                               value="<?php echo htmlspecialchars($usuario['correo_usuario']); ?>"
                                               placeholder="Ingrese correo..." required>
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-1">
                                    <label class="form-label fw-bold">Usuario de plataforma</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-bounding-box"></i></span>
                                        <input type="text" class="form-control" name="usuario_plataforma"
                                               value="<?php echo htmlspecialchars($usuario['usuario_cons']); ?>"
                                               placeholder="Usuario..." required>
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-1">
                                    <label class="form-label fw-bold">Número de celular</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                        <input type="number" class="form-control" name="celular_usuario"
                                               value="<?php echo htmlspecialchars($usuario['celular_usuario']); ?>"
                                               placeholder="Celular...">
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-1">
                                    <label class="form-label"><b>Seleccione el rol</b></label>
                                    <div class="input-group shadow-sm">
                                        <label class="input-group-text"><i class="bi bi-person-video3"></i></label>
                                        <select class="form-select" name="rol_usuario" required>
                                            <option value="" disabled>Seleccione el rol</option>
                                            <?php
                                            $roles = [
                                                "atencion_procedencia" => "Atención Procedencia",
                                                "componente_economico" => "Componente Economico",
                                                "control_calidad" => "Control de Calidad",
                                                "consolidacion" => "Consolidación",
                                                "coordinacion_tecnica" => "Coordinación Tecnica",
                                                "director_catastro" => "Director Catastro",
                                                "editor" => "Editor",
                                                "reconocedor" => "Reconocedor Predial",
                                                "avaluos" => "Perito Avaluador",
                                                "procedencia_juridica" => "Procedencia Juridica",
                                                "revision_juridica" => "Revisión Juridica",
                                                "ventanilla_catastral" => "Ventanilla Catastral",
                                                "usuarios_ops" => "Usuario OPS"
                                            ];
                                            foreach ($roles as $value => $label) {
                                                $selected = ($usuario['rol_usuario'] === $value) ? "selected" : "";
                                                echo "<option value='{$value}' {$selected}>{$label}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 p-1 px-2 my-1">
                                    <label class="form-label fw-bold">Nueva contraseña (opcional)</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-unlock-fill"></i></span>
                                        <input type="password" class="form-control" name="password_plataforma"
                                               placeholder="Dejar en blanco para mantener la actual">
                                    </div>
                                </div>

                                <div class="form-group mt-4 mb-0 text-center">
                                    <button type="submit" class="btn btn-block text-white" style="background-color: #002F55;">
                                        <b><i class="bi bi-save me-2"></i> ACTUALIZAR USUARIO </b>
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
