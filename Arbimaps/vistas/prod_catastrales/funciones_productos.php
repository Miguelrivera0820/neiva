<?php

if (!defined('ESTADO_PRODUCTO_SIN_CARGAR')) {
    define('ESTADO_PRODUCTO_SIN_CARGAR', 'SIN CARGAR');
}

if (!defined('ESTADO_PRODUCTO_DOCUMENTOS_CARGADOS')) {
    define('ESTADO_PRODUCTO_DOCUMENTOS_CARGADOS', 'DOCUMENTOS CARGADOS');
}

if (!function_exists('estadoProductoTieneDocumentos')) {
    function estadoProductoTieneDocumentos($estado)
    {
        $estadoNormalizado = strtoupper(trim((string) $estado));

        return in_array($estadoNormalizado, [
            ESTADO_PRODUCTO_DOCUMENTOS_CARGADOS,
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
