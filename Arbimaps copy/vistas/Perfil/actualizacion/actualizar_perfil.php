<?php 
session_start();

require_once '../../../../conexion.php';

$id_usuario = $_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {  
    $username           = trim($_POST['nombre_usuario']         ?? '');  
    $password_input     = trim($_POST['password_cons']          ?? '');
    $cedula_usuario     = trim($_POST['cedula_usuario']         ?? '');
    $nombre_real        = trim($_POST['nombre_usuario_real']    ?? '');  
    $apellido_usuario   = trim($_POST['apellido_usuario']       ?? '');
    $correo_usuario     = trim($_POST['correo_usuario']         ?? '');
    $celular_usuario    = trim($_POST['celular_usuario']        ?? '');
    $rol_usuario        = trim($_POST['rol_usuario']            ?? '');    
    


    $errores = [];
    if (empty($username)) $errores[]            = "El nombre de usuario es requerido.";
    if (empty($cedula_usuario)) $errores[]      = "La cédula es requerida.";
    if (empty($nombre_real)) $errores[]         = "Los nombres son requeridos.";  
    if (empty($apellido_usuario)) $errores[]    = "Los apellidos son requeridos.";
    if (empty($correo_usuario) || !filter_var($correo_usuario, FILTER_VALIDATE_EMAIL)) $errores[] = "Correo inválido.";
    if (empty($celular_usuario)) $errores[]     = "El celular es requerido.";

    if (!preg_match('/^\d{1,10}$/', $cedula_usuario)) {
        $errores[] = "La cédula debe ser un número de hasta 10 dígitos sin caracteres especiales.";
    }

    if (!empty($errores)) {
        $_SESSION['error_actualizacion'] = implode('<br>', $errores);
        header("Location: /arbimaps/Arbimaps/index.php?page=Perfil/editar_perfil");
        exit();
    }



    $password_cons = '';
    if (!empty($password_input)) {
        $password_cons = $password_input;  
    } else {
        $usuarios_cons = "SELECT password_cons FROM usuarios_cons WHERE id_usuario = ?";
        $cargar_datos = $mysqli->prepare($usuarios_cons);
        $cargar_datos->bind_param("i", $id_usuario);
        $cargar_datos->execute();
        $resultado_consulta = $cargar_datos->get_result();
        $datos_usuario = $resultado_consulta->fetch_assoc();
        $password_cons = $datos_usuario['password_cons'];
    }

    $foto_usuario_guardada = '';
    if (isset($_FILES['foto_user_file']) && $_FILES['foto_user_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['foto_user_file']['error'] === UPLOAD_ERR_OK) {
            $file       = $_FILES['foto_user_file'];
            $maxSize    = 5 * 1024 * 1024;

            if ($file['size'] > $maxSize) {
                $_SESSION['error_actualizacion'] = 'El archivo es demasiado grande. Límite 5MB.';
                header("Location: /arbimaps/Arbimaps/index.php?page=Perfil/editar_perfil");
                exit();
            }

            $finfo      = finfo_open(FILEINFO_MIME_TYPE);
            $mime       = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            $allowed    = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];

            if (!array_key_exists($mime, $allowed)) {
                $_SESSION['error_actualizacion'] = 'Tipo de archivo no permitido. Use JPG, PNG o GIF.';
                header("Location: /arbimaps/Arbimaps/index.php?page=Perfil/editar_perfil");
                exit();
            }

            $ext = $allowed[$mime];
            try {
                $random = bin2hex(random_bytes(6));
            } catch (Exception $e) {
                $random = uniqid();
            }
            $nombreNuevo = time() . '_' . $random . '.' . $ext;

            $ruta_base_foto = __DIR__ . '/../../../assets/fotos_usuarios/' . $id_usuario . '/';
            if (!is_dir($ruta_base_foto)) {
                mkdir($ruta_base_foto, 0755, true);
            }
            $guardar_foto = $ruta_base_foto . $nombreNuevo;

            if (!move_uploaded_file($file['tmp_name'], $guardar_foto)) {
                $_SESSION['error_actualizacion'] = 'No se pudo mover el archivo subido.';
                header("Location: /arbimaps/Arbimaps/index.php?page=Perfil/editar_perfil");
                exit();
            }

            $foto_usuario_guardada = $nombreNuevo;
        } else {
            $_SESSION['error_actualizacion'] = 'Error al subir el archivo.';
            header("Location: /arbimaps/Arbimaps/index.php?page=Perfil/editar_perfil");
            exit();
        }
    } else {
        $foto_usuarios_cons = "SELECT foto_user FROM usuarios_cons WHERE id_usuario = ?";
        $cargar_foto_usuario = $mysqli->prepare($foto_usuarios_cons);
        $cargar_foto_usuario->bind_param("i", $id_usuario);
        $cargar_foto_usuario->execute();
        $mostrar_foto_usuario = $cargar_foto_usuario->get_result();
        $relacion_foto_contraseña = $mostrar_foto_usuario->fetch_assoc();
        $foto_usuario_guardada = $relacion_foto_contraseña['foto_user'];
    }

    $sql = "UPDATE usuarios_cons SET
                usuario_cons = ?,
                password_cons = ?,
                cedula_usuario = ?,
                nombre_usuario = ?,
                apellido_usuario = ?,
                correo_usuario = ?,
                celular_usuario = ?,
                rol_usuario = ?,
                foto_user = ?
            WHERE id_usuario = ?"; 

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("sssssssssi", 
                        $username, 
                        $password_cons, 
                        $cedula_usuario, 
                        $nombre_real, 
                        $apellido_usuario, 
                        $correo_usuario, 
                        $celular_usuario, 
                        $rol_usuario, 
                        $foto_usuario_guardada, 
                        $id_usuario  
                    );

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $_SESSION['usuario_cons']       = $username;
        $_SESSION['nombre_usuario']     = $nombre_real;  
        $_SESSION['apellido_usuario']   = $apellido_usuario;
        $_SESSION['correo_usuario']     = $correo_usuario;
        $_SESSION['celular_usuario']    = $celular_usuario;
        $_SESSION['rol_usuario']        = $rol_usuario;
        $_SESSION['cedula_usuario']     = $cedula_usuario;

        if (!empty($foto_usuario_guardada)) {
            $_SESSION['foto_user']      = $foto_usuario_guardada;
        }

        if (!empty($password_input)) {
            $_SESSION['password_cons']  = $password_cons;
        }

        $_SESSION['success_actualizacion'] = "Perfil actualizado correctamente";

        header("Location: /arbimaps/Arbimaps/index.php?page=Perfil/editar_perfil");
        exit();
    } else {
        $_SESSION['error_actualizacion'] = "No se pudo actualizar (affected_rows: " . $stmt->affected_rows . "). Verifica los datos. Error: " . $mysqli->error;
        header("Location: /arbimaps/Arbimaps/index.php?page=Perfil/editar_perfil");
        exit();
    }
} else {
    header("Location: /arbimaps/Arbimaps/index.php?page=Perfil/editar_perfil");
    exit();
}
?>