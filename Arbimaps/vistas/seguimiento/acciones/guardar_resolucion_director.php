<?php
// session_start();
require '../../../../conexion.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cod_tramite = $_POST['cod_tramite'] ?? '';
    $id_resolucion = $_POST['id_resolucion'] ?? null;
  
    // Verificar que se recibió el archivo y el ID
    if (empty($cod_tramite) || empty($id_resolucion) || !isset($_FILES['pdf_file'])) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit;
    }
  
    $archivo = $_FILES['pdf_file'];
  
    // Verificar que no hubo errores en la carga
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Error al cargar el archivo']);
        exit;
    }
  
    // Leer el contenido del archivo
    $pdf_data = file_get_contents($archivo['tmp_name']);
  
    if ($pdf_data === false) {
        echo json_encode(['success' => false, 'message' => 'Error al leer el archivo']);
        exit;
    }
  
    // Verificar que el registro existe
    $sql_check = "SELECT id_entrega_asignacion FROM resoluciones WHERE id_resoluciones = ?";
    $stmt_check = $mysqli->prepare($sql_check);
    $stmt_check->bind_param("i", $id_resolucion);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if (!$row_check = $result_check->fetch_assoc()) {
        echo json_encode(['success' => false, 'message' => 'Registro no encontrado']);
        exit;
    }
    $id_entrega_asignacion = $row_check['id_entrega_asignacion'];
    $stmt_check->close();
  
    // Crear carpeta si no existe
    $carpeta_resoluciones = '../../../../resoluciones/';
    if (!file_exists($carpeta_resoluciones)) {
        mkdir($carpeta_resoluciones, 0777, true);
    }
  
    // Crear subcarpeta con el cod_tramite si no existe
    $subcarpeta = $carpeta_resoluciones . $cod_tramite . '/';
    if (!file_exists($subcarpeta)) {
        mkdir($subcarpeta, 0777, true);
    }
  
    // Generar nombre único para el archivo
    $fecha_actual = date('Y-m-d_H-i-s');
    $nombre_archivo = "resolucion_director_{$fecha_actual}.pdf";
    $ruta_completa = $subcarpeta . $nombre_archivo;
  
    // Guardar el archivo en el servidor
    $archivo_guardado = file_put_contents($ruta_completa, $pdf_data);
  
    if ($archivo_guardado === false) {
        echo json_encode(['success' => false, 'message' => 'Error al guardar el archivo en el servidor']);
        exit;
    }
  
    // Ruta relativa para guardar en BD
    $ruta_relativa = 'resoluciones/' . $cod_tramite . '/' . $nombre_archivo;
  
    // Actualizar el documento en la tabla resoluciones
    $sql_update = "UPDATE resoluciones SET resolucion_director = ?, ruta_archivo = ? WHERE id_resoluciones = ?";
    $stmt_update = $mysqli->prepare($sql_update);
    $stmt_update->bind_param("ssi", $pdf_data, $ruta_relativa, $id_resolucion);
  
    if ($stmt_update->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Resolución director guardada correctamente en BD y servidor',
            'id_resolucion' => $id_resolucion,
            'ruta_archivo' => $ruta_relativa,
            'nombre_archivo' => $nombre_archivo
        ]);
    } else {
        // Si falla la BD, eliminar el archivo físico
        if (file_exists($ruta_completa)) {
            unlink($ruta_completa);
        }
        echo json_encode(['success' => false, 'message' => 'Error al actualizar en BD: ' . $mysqli->error]);
    }
  
    $stmt_update->close();
    $mysqli->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>