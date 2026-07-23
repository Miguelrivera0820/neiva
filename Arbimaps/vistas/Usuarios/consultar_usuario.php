<?php

$error_login = "";

if ($_POST) {
    $usuario_cons   = $_POST['usuario_cons'];
    $password_cons  = $_POST['password_cons'];
    $sql = "SELECT 
                id_usuario, 
                usuario_cons, 
                password_cons, 
                nombre_usuario, 
                apellido_usuario, 
                rol_usuario, 
                cedula_usuario 
            FROM usuarios_cons 
            WHERE usuario_cons='$usuario_cons'";
    $resultado = $mysqli->query($sql);
    if (!$resultado) {
        die("Error en la consulta SQL: " . $mysqli->error);
    }
    $num = $resultado->num_rows;
    if ($num > 0) {
        $row = $resultado->fetch_assoc();
        if ($password_cons == $row['password_cons']) {
            $_SESSION['id_usuario'] = $row['id_usuario'];
            $_SESSION['usuario_cons'] = $row['usuario_cons'];
            $_SESSION['nombre_usuario'] = $row['nombre_usuario'];
            $_SESSION['apellido_usuario'] = $row['apellido_usuario'];
            $_SESSION['rol_usuario'] = $row['rol_usuario'];
            $_SESSION['cedula_usuario'] = $row['cedula_usuario'];

            header("Location: inicio.php");
            exit();
        } else {
            $error_login = "Usuario o contraseña incorrectos.";
        }
    } else {
        $error_login = "Usuario o contraseña incorrectos.";
    }
}
$sql2 = "SELECT 
            id_usuario, 
            usuario_cons, 
            password_cons, 
            cedula_usuario, 
            nombre_usuario, 
            apellido_usuario,
            correo_usuario, 
            rol_usuario,
            rol_usuario_dos
        FROM usuarios_cons
        ORDER BY id_usuario DESC";
$resultado2 = $mysqli->query($sql2);
$esAdministradorUsuarios = function_exists('usuarioTieneAlgunRol') && usuarioTieneAlgunRol('administrador');
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h2 mb-0 text-gray-1200"><B>TRAMITES CATASTRALES NEIVA</B></h1>
    </div>
    <a href="index.php?page=Usuarios/crear_usuario" class="btn text-white" style="background-color: #002F55;">
        <b><i class="bi bi-person-fill-add me-2"></i>Registrar Usuario</b>
    </a>
    <br>
    <br>
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between" style="background-color: #002F55;">
                    <h6 class="m-0 font-weight-bold text-white">Información de Trámites</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle text-white" href="#" role="button" id="dropdownMenuLink"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr style="align-items: center;">
                                    <th class="text-center">Cedula</th>
                                    <th class="text-center">Nombre</th>
                                    <th class="text-center">Apellido</th>
                                    <th class="text-center">Correo</th>
                                    <th class="text-center">Usuario</th>
                                    <th class="text-center">Contraseña</th>
                                    <th class="text-center">Rol principal</th>
                                    <th class="text-center">Rol segundario</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $resultado2->fetch_assoc()) { ?>
                                    <tr>
                                        <td style="font-size:0.8em"><?php echo $row['cedula_usuario']; ?></td>
                                        <td style="font-size:0.8em"><?php echo $row['nombre_usuario'] ?></td>
                                        <td style="font-size:0.8em"><?php echo $row['apellido_usuario'] ?></td>
                                        <td style="font-size:0.8em"><?php echo $row['correo_usuario'] ?></td>
                                        <td style="font-size:0.8em"><?php echo $row['usuario_cons']; ?></td>
                                        <td style="font-size:0.8em">
                                            Protegida
                                            <?php if (!empty($row['password_cons'])): ?>
                                                <span class="badge badge-success d-block mt-1">Hash activo</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger d-block mt-1">Sin clave</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="font-size:0.8em"><?php echo $row['rol_usuario']; ?></td>
                                        <td style="font-size:0.8em"><?php echo $row['rol_usuario_dos']; ?></td>
                                        <td class="text-center">
                                            <a href="index.php?page=Usuarios/informacion_usuario&id_usuario=<?php echo urlencode($row['id_usuario']); ?>"
                                                class="btn btn-sm text-white m-1" style="font-size:0.7em; background-color: #002F55"><b>EDITAR</b></a>
                                            <a href="#"
                                                class="btn btn-sm btn-danger btn-eliminar"
                                                data-id="<?php echo htmlspecialchars($row['id_usuario']); ?>"
                                                style="font-size:0.7em">
                                                <b>ELIMINAR</b>
                                            </a>
                                            <?php if ($esAdministradorUsuarios): ?>
                                                <a href="#"
                                                    class="btn btn-sm btn-warning btn-reset-password m-1"
                                                    data-id="<?php echo htmlspecialchars($row['id_usuario']); ?>"
                                                    data-usuario="<?php echo htmlspecialchars($row['usuario_cons'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    style="font-size:0.7em">
                                                    <b>RESET CLAVE</b>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../js/sb-admin-2.min.js"></script>
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#dataTable')) {
        $('#dataTable').DataTable({
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Todos']],
            ordering: true,
            searching: true,
            paging: true,
            info: true,
            language: window.neivaDataTablesLanguage ? window.neivaDataTablesLanguage() : {}
        });
    } else if (!$.fn.DataTable) {
        console.warn('DataTables no está disponible para la tabla de usuarios.');
    }
});

$(document).on('click', '.btn-eliminar', function(e) {
    e.preventDefault();
    const id = $(this).data('id');

    Swal.fire({
        icon: 'warning',
        title: '¿Eliminar usuario?',
        text: 'Esta acción NO se puede deshacer.',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'vistas/Usuarios/acciones/eliminar_usuario.php';

            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id_usuario';
            idInput.value = id;
            form.appendChild(idInput);

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = window.APP_CSRF_TOKEN || '';
            form.appendChild(csrfInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
});

$(document).on('click', '.btn-reset-password', function(e) {
    e.preventDefault();
    const id = $(this).data('id');
    const usuario = $(this).data('usuario');

    Swal.fire({
        icon: 'warning',
        title: '¿Restablecer contraseña?',
        text: 'Se generará una contraseña temporal para ' + usuario + '. La contraseña actual no se mostrará.',
        showCancelButton: true,
        confirmButtonText: 'Sí, restablecer',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'vistas/Usuarios/acciones/reset_password_usuario.php';

            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id_usuario';
            idInput.value = id;
            form.appendChild(idInput);

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = window.APP_CSRF_TOKEN || '';
            form.appendChild(csrfInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
});
</script>
