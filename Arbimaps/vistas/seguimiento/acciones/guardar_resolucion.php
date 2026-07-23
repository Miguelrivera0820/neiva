<?php
// session_start();
require '../../../../conexion.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cod_tramite = $_POST['cod_tramite'] ?? '';
   
    // Verificar que se recibió el archivo
    if (empty($cod_tramite) || !isset($_FILES['pdf_file'])) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit;
    }
   
    $archivo = $_FILES['pdf_file'];
   
    // Verificar que no hubo errores en la carga
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Error al cargar el archivo']);
        exit;
    }

    // Verificar tipo MIME del archivo
    if (mime_content_type($archivo['tmp_name']) !== 'application/pdf') {
        echo json_encode(['success' => false, 'message' => 'El archivo debe ser un PDF válido']);
        exit;
    }
   
    // Leer el contenido del archivo
    $pdf_data = file_get_contents($archivo['tmp_name']);
   
    if ($pdf_data === false) {
        echo json_encode(['success' => false, 'message' => 'Error al leer el archivo']);
        exit;
    }
   
    // Buscar el id_entrega_asignacion más reciente para este trámite
    $sql = "SELECT id_entrega_asignacion FROM entrega_asignacion
            WHERE entrega_cod_tramite = ?
            ORDER BY id_entrega_asignacion DESC
            LIMIT 1";
   
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $cod_tramite);
    $stmt->execute();
    $result = $stmt->get_result();
   
    if ($row = $result->fetch_assoc()) {
        $id_entrega_asignacion = $row['id_entrega_asignacion'];
       
        // ✅ Carpeta 'resoluciones' fuera de 'Practica'
        $carpeta_resoluciones = dirname(__DIR__, 4) . '/resoluciones/';

        // Crear carpeta si no existe
        // $carpeta_resoluciones = '../../../../resoluciones/';
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
        $nombre_archivo = "resolucion_{$fecha_actual}.pdf";
        $ruta_completa = $subcarpeta . $nombre_archivo;
       
        // Guardar el archivo en el servidor
        $archivo_guardado = file_put_contents($ruta_completa, $pdf_data);
       
        if ($archivo_guardado === false) {
            echo json_encode(['success' => false, 'message' => 'Error al guardar el archivo en el servidor']);
            exit;
        }
       
        // Ruta relativa para guardar en BD
        $ruta_relativa = 'resoluciones/' . $cod_tramite . '/' . $nombre_archivo;
       
        $numero_resolucion = isset($_POST['numero_resolucion']) ? (int)preg_replace('/[^0-9]/', '', $_POST['numero_resolucion']) : 0;
        if ($numero_resolucion <= 0) {
            // Si por alguna razón no se envió o es inválido, calcular el consecutivo en servidor
            $sql_next_res = "SELECT MAX(numero_resolucion) as max_res FROM resoluciones";
            $max_res = 0;
            if ($result_next_res = $mysqli->query($sql_next_res)) {
                $row_next_res = $result_next_res->fetch_assoc();
                if ($row_next_res['max_res'] !== null) {
                    $max_res = (int)$row_next_res['max_res'];
                } else {
                    $sql_count_res = "SELECT COUNT(*) as total FROM resoluciones";
                    if ($result_count_res = $mysqli->query($sql_count_res)) {
                        $row_count_res = $result_count_res->fetch_assoc();
                        $max_res = (int)$row_count_res['total'];
                    }
                }
            }
            $numero_resolucion = $max_res + 1;
        } else {
            // VALIDACIÓN: Verificar si el número ya existe en OTRO trámite
            $sql_check = "SELECT COUNT(*) as existe 
                          FROM resoluciones r
                          INNER JOIN entrega_asignacion ea ON r.id_entrega_asignacion = ea.id_entrega_asignacion
                          WHERE r.numero_resolucion = ? AND ea.entrega_cod_tramite != ?";
            $stmt_check = $mysqli->prepare($sql_check);
            $stmt_check->bind_param("is", $numero_resolucion, $cod_tramite);
            $stmt_check->execute();
            $res_check = $stmt_check->get_result()->fetch_assoc();
            $stmt_check->close();
            if ($res_check['existe'] > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'El número de resolución ' . str_pad($numero_resolucion, 3, '0', STR_PAD_LEFT) . ' ya está registrado en otro trámite. Por favor, asigne un número diferente.'
                ]);
                exit;
            }
        }

        // Insertar el documento en la tabla resoluciones
        $sql_insert = "INSERT INTO resoluciones (id_entrega_asignacion, documento, ruta_archivo, numero_resolucion)
                       VALUES (?, ?, ?, ?)";
        $stmt_insert = $mysqli->prepare($sql_insert);
        $stmt_insert->bind_param("issi", $id_entrega_asignacion, $pdf_data, $ruta_relativa, $numero_resolucion);
       
        if ($stmt_insert->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Resolución guardada correctamente en BD y servidor',
                'id_resolucion' => $mysqli->insert_id,
                'ruta_archivo' => $ruta_relativa,
                'nombre_archivo' => $nombre_archivo
            ]);
        } else {
            // Si falla la BD, eliminar el archivo físico
            if (file_exists($ruta_completa)) {
                unlink($ruta_completa);
            }
            echo json_encode(['success' => false, 'message' => 'Error al guardar en BD: ' . $mysqli->error]);
        }
       
        $stmt_insert->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró la asignación del trámite']);
    }
   
    $stmt->close();
    $mysqli->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>