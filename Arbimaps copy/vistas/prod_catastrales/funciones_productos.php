<?php

if (!defined('ESTADO_PRODUCTO_SIN_CARGAR')) {
    define('ESTADO_PRODUCTO_SIN_CARGAR', 'SIN CARGAR');
}

if (!defined('ESTADO_PRODUCTO_DOCUMENTOS_CARGADOS')) {
    define('ESTADO_PRODUCTO_DOCUMENTOS_CARGADOS', 'DOCUMENTOS CARGADOS');
}

if (!defined('ESTADO_PRODUCTO_EN_COORDINACION')) {
    define('ESTADO_PRODUCTO_EN_COORDINACION', 'ENVIADO A COORDINACION');
}

if (!defined('ESTADO_PRODUCTO_EN_EDITOR')) {
    define('ESTADO_PRODUCTO_EN_EDITOR', 'ENVIADO A EDITOR');
}

if (!defined('ESTADO_PRODUCTO_PENDIENTE_APROBACION')) {
    define('ESTADO_PRODUCTO_PENDIENTE_APROBACION', 'PENDIENTE DE APROBACION');
}

if (!defined('ESTADO_PRODUCTO_DEVOLUCION')) {
    define('ESTADO_PRODUCTO_DEVOLUCION', 'DEVOLUCION');
}

if (!defined('ESTADO_PRODUCTO_APROBADO')) {
    define('ESTADO_PRODUCTO_APROBADO', 'APROBADO');
}

if (!function_exists('rolesActualesProducto')) {
    function rolesActualesProducto()
    {
        return array_values(array_unique(array_filter([
            $_SESSION['rol_usuario'] ?? '',
            $_SESSION['rol_usuario_dos'] ?? '',
            $_SESSION['rol_usuario_tres'] ?? '',
        ], static function ($rol) {
            return trim((string) $rol) !== '';
        })));
    }
}

if (!function_exists('usuarioProductoTieneRol')) {
    function usuarioProductoTieneRol($rolesPermitidos)
    {
        $rolesPermitidos = is_array($rolesPermitidos) ? $rolesPermitidos : [$rolesPermitidos];

        return count(array_intersect($rolesPermitidos, rolesActualesProducto())) > 0;
    }
}

if (!function_exists('cedulaUsuarioProductoActual')) {
    function cedulaUsuarioProductoActual()
    {
        return trim((string) ($_SESSION['cedula_usuario'] ?? ''));
    }
}

if (!function_exists('asegurarFlujoProductosCatastrales')) {
    function asegurarFlujoProductosCatastrales($conexion)
    {
        if (!($conexion instanceof mysqli)) {
            return false;
        }

        $sqlTabla = "CREATE TABLE IF NOT EXISTS producto_catastral_flujo (
            codigo_producto VARCHAR(50) NOT NULL,
            editor_cedula VARCHAR(30) DEFAULT NULL,
            editor_nombre VARCHAR(150) DEFAULT NULL,
            observacion_revision TEXT DEFAULT NULL,
            fecha_asignacion DATETIME DEFAULT NULL,
            fecha_revision DATETIME DEFAULT NULL,
            actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (codigo_producto),
            KEY idx_producto_editor (editor_cedula)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        if (!$conexion->query($sqlTabla)) {
            return false;
        }

        // Compatibilidad con los productos creados antes de implementar el flujo.
        $estadoCoordinacion = ESTADO_PRODUCTO_EN_COORDINACION;
        $estadoPendiente = ESTADO_PRODUCTO_PENDIENTE_APROBACION;
        $estadoSinCargar = ESTADO_PRODUCTO_SIN_CARGAR;
        $estadoDocumentos = ESTADO_PRODUCTO_DOCUMENTOS_CARGADOS;

        $stmtMigracionPago = $conexion->prepare(
            "UPDATE certificado_catastral
             SET estado = ?
             WHERE prod_tipo_producto IS NOT NULL
               AND TRIM(prod_tipo_producto) <> ''
               AND TRIM(COALESCE(cert_soporte_pago, '')) <> ''
               AND (TRIM(COALESCE(estado, '')) = '' OR UPPER(TRIM(estado)) = ?)"
        );
        if ($stmtMigracionPago) {
            $stmtMigracionPago->bind_param('ss', $estadoCoordinacion, $estadoSinCargar);
            $stmtMigracionPago->execute();
            $stmtMigracionPago->close();
        }

        $stmtMigracionDocumentos = $conexion->prepare(
            "UPDATE certificado_catastral
             SET estado = ?
             WHERE prod_tipo_producto IS NOT NULL
               AND TRIM(prod_tipo_producto) <> ''
               AND UPPER(TRIM(COALESCE(estado, ''))) = ?"
        );
        if ($stmtMigracionDocumentos) {
            $stmtMigracionDocumentos->bind_param('ss', $estadoPendiente, $estadoDocumentos);
            $stmtMigracionDocumentos->execute();
            $stmtMigracionDocumentos->close();
        }

        return true;
    }
}

if (!function_exists('obtenerFlujoProducto')) {
    function obtenerFlujoProducto($conexion, $codigoProducto)
    {
        $flujo = null;
        $stmt = $conexion->prepare(
            "SELECT codigo_producto, editor_cedula, editor_nombre, observacion_revision,
                    fecha_asignacion, fecha_revision
             FROM producto_catastral_flujo
             WHERE codigo_producto = ?
             LIMIT 1"
        );
        if ($stmt) {
            $stmt->bind_param('s', $codigoProducto);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $flujo = $resultado ? $resultado->fetch_assoc() : null;
            $stmt->close();
        }

        return $flujo;
    }
}

if (!function_exists('estadoProductoTieneDocumentos')) {
    function estadoProductoTieneDocumentos($estado)
    {
        $estadoNormalizado = strtoupper(trim((string) $estado));

        return in_array($estadoNormalizado, [
            ESTADO_PRODUCTO_DOCUMENTOS_CARGADOS,
            ESTADO_PRODUCTO_PENDIENTE_APROBACION,
            ESTADO_PRODUCTO_APROBADO,
            ESTADO_PRODUCTO_DEVOLUCION,
            'DOCUMENTOS',
            'CARGADO',
            'CARGADOS',
        ], true);
    }
}

if (!function_exists('normalizarCodigoProducto')) {
    function normalizarCodigoProducto($codigo)
    {
        return preg_replace('/[^A-Za-z0-9_-]/', '', (string) $codigo);
    }
}

if (!function_exists('directorioDocumentosProducto')) {
    function directorioDocumentosProducto($codigo)
    {
        $codigoSeguro = normalizarCodigoProducto($codigo);

        return dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR . 'productos_catastrales'
            . DIRECTORY_SEPARATOR . $codigoSeguro;
    }
}

if (!function_exists('listarDocumentosProducto')) {
    function listarDocumentosProducto($codigo)
    {
        $codigoSeguro = normalizarCodigoProducto($codigo);
        $directorio = directorioDocumentosProducto($codigoSeguro);

        if ($codigoSeguro === '' || !is_dir($directorio)) {
            return [];
        }

        $documentos = [];
        $elementos = scandir($directorio);

        if ($elementos === false) {
            return [];
        }

        foreach ($elementos as $nombreArchivo) {
            if ($nombreArchivo === '.' || $nombreArchivo === '..') {
                continue;
            }

            $rutaCompleta = $directorio . DIRECTORY_SEPARATOR . $nombreArchivo;
            if (!is_file($rutaCompleta) || strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION)) !== 'pdf') {
                continue;
            }

            $documentos[] = [
                'nombre' => $nombreArchivo,
                'tamano' => filesize($rutaCompleta) ?: 0,
                'fecha' => filemtime($rutaCompleta) ?: 0,
                'url' => 'productos_catastrales/'
                    . rawurlencode($codigoSeguro) . '/'
                    . rawurlencode($nombreArchivo),
            ];
        }

        usort($documentos, static function ($documentoA, $documentoB) {
            return $documentoB['fecha'] <=> $documentoA['fecha'];
        });

        return $documentos;
    }
}

if (!function_exists('rutaMarcaDocumentoFirmadoProducto')) {
    function rutaMarcaDocumentoFirmadoProducto($codigo)
    {
        return directorioDocumentosProducto($codigo)
            . DIRECTORY_SEPARATOR . '.documento_firmado.flag';
    }
}

if (!function_exists('productoTieneDocumentos')) {
    function productoTieneDocumentos($codigo)
    {
        return count(listarDocumentosProducto($codigo)) > 0;
    }
}

if (!function_exists('formatearTamanoArchivoProducto')) {
    function formatearTamanoArchivoProducto($bytes)
    {
        $bytes = (int) $bytes;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
        }

        return number_format(max($bytes, 0) / 1024, 1, ',', '.') . ' KB';
    }
}
