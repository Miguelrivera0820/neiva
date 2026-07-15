<?php
/**
 * Script para corregir de forma segura las rutas/URLs hardcodeadas en ARBIMaps/Neiva.
 * Usa token_get_all para diferenciar entre HTML en línea y literales de cadena en PHP.
 */

$rootDir = dirname(__DIR__);
$write = in_array('--write', $argv, true);

// Archivos reportados con advertencias de ruta
$filesToFix = [
    'Arbimaps/vistas/baqueanos/solicitudes/acciones/editar_solicitud_baqueano.php',
    'Arbimaps/vistas/baqueanos/solicitudes/vistas/informacion_solicitud_gerencia.php',
    'Arbimaps/vistas/baqueanos/solicitudes/vistas/informacion_solicitud_operaciones.php',
    'Arbimaps/vistas/baqueanos/solicitudes/vistas/radicar_cuenta_baqueanos.php',
    'Arbimaps/vistas/baqueanos/solicitudes/vistas/tablero_control.php',
    'Arbimaps/vistas/cuentas/radicacion/acciones/aprobar_cuenta_presupuesto.php',
    'Arbimaps/vistas/cuentas/radicacion/acciones/formulario_subir_archivos.php',
    'Arbimaps/vistas/cuentas/radicacion/acciones/procesar_pago_cuenta.php',
    'Arbimaps/vistas/cuentas/radicacion/acciones/rechazar_cuenta.php',
    'Arbimaps/vistas/cuentas/radicar_cuenta.php',
    'Arbimaps/vistas/cuentas/validacion/acciones/aprobar_cuenta_operaciones.php',
    'Arbimaps/vistas/cuentas/validacion/acciones/aprobar_cuenta_social.php',
    'Arbimaps/vistas/cuentas/validacion/revisar_cuentas.php',
    'Arbimaps/vistas/Licitaciones/cerrar_licitacion.php',
    'Arbimaps/vistas/Licitaciones/editar_licitaciones.php',
    'Arbimaps/vistas/Licitaciones/ganadas_licitacion.php',
    'Arbimaps/vistas/Licitaciones/licitacion_prorroga.php',
    'Arbimaps/vistas/Licitaciones/procesar_cierre_prorroga.php',
    'Arbimaps/vistas/Licitaciones/solicitud_licitaciones.php',
    'Arbimaps/vistas/Perfil/acciones/actualizar_perfil_contratacion.php',
    'Arbimaps/vistas/Perfil/vistas/editar_perfil_contratacion.php',
    'Arbimaps/vistas/Personal/acciones/actualizacion_perfil_activo.php',
    'Arbimaps/vistas/Personal/acciones/agregar_perfil_contratacion.php',
    'Arbimaps/vistas/Personal/acciones/otrosi_subir_archivo.php',
    'Arbimaps/vistas/Personal/actualizacion_perfil.php',
    'Arbimaps/vistas/Personal/agregar_mi_perfil.php',
    'Arbimaps/vistas/Personal/cargar_talento_humano.php',
    'Arbimaps/vistas/Personal/completar_datos.php',
    'Arbimaps/vistas/Personal/contratacion.php',
    'Arbimaps/vistas/Personal/detalle_solicitud_otrosi.php',
    'Arbimaps/vistas/Personal/editar_perfil_profesional.php',
    'Arbimaps/vistas/Personal/editar_personal.php',
    'Arbimaps/vistas/Personal/editar_solicitud.php',
    'Arbimaps/vistas/Personal/informacion_personal.php',
    'Arbimaps/vistas/Personal/informacion_personal_individual.php',
    'Arbimaps/vistas/Personal/mis_perfiles.php',
    'Arbimaps/vistas/Personal/personal_activo.php',
    'Arbimaps/vistas/Personal/revisar_modificaciones_gerencia.php',
    'Arbimaps/vistas/Personal/revisar_solicitud_otrosi.php',
    'Arbimaps/vistas/Personal/solicitar_otrosi.php',
    'Arbimaps/vistas/Personal/ver_estudios.php',
    'Arbimaps/vistas/Personal/ver_otrosi.php',
    'Arbimaps/vistas/seguimiento/asignar_revision.php',
    'Arbimaps/vistas/seguimiento/asignar_revision_segun_rol.php',
    'Arbimaps/vistas/seguimiento/asignar_subsanacion.php',
    'Arbimaps/vistas/tramites/cuentas_rechazadas/no_procede_completar.php'
];

foreach ($filesToFix as $relFile) {
    $filePath = $rootDir . '/' . str_replace('\\', '/', $relFile);
    if (!file_exists($filePath)) {
        echo "Archivo no encontrado: $relFile\n";
        continue;
    }

    $content = file_get_contents($filePath);
    $tokens = token_get_all($content);
    $newContent = '';
    $changed = false;

    foreach ($tokens as $token) {
        if (is_array($token)) {
            $id = $token[0];
            $text = $token[1];

            if ($id === T_INLINE_HTML) {
                // HTML: reemplazar URLs /arbimaps/Arbimaps/ o /Arbimaps/ con PHP echo
                $modified = preg_replace_callback(
                    '/\/arbimaps\/Arbimaps\/([a-zA-Z0-9_\-\.\?\&\=\/]*)/i',
                    function ($matches) {
                        return "<?= neiva_app_url('Arbimaps/" . $matches[1] . "') ?>";
                    },
                    $text
                );
                
                $modified = preg_replace_callback(
                    '/\/Arbimaps\/([a-zA-Z0-9_\-\.\?\&\=\/]*)/i',
                    function ($matches) {
                        // Evitar doble reemplazo si ya tenía <?=
                        if (str_contains($matches[0], 'neiva_app_url')) {
                            return $matches[0];
                        }
                        return "<?= neiva_app_url('Arbimaps/" . $matches[1] . "') ?>";
                    },
                    $modified
                );

                if ($modified !== $text) {
                    $text = $modified;
                    $changed = true;
                }
            } elseif ($id === T_CONSTANT_ENCAPSED_STRING) {
                // PHP string literal
                // Ejemplos: '/arbimaps/Arbimaps/index.php' o "/arbimaps/Arbimaps/index.php"
                $quote = $text[0]; // ' o "
                $innerStr = substr($text, 1, -1);

                if (preg_match('/^\/arbimaps\/Arbimaps\/(.*)$/i', $innerStr, $matches)) {
                    $path = $matches[1];
                    $text = "neiva_app_url('Arbimaps/" . addslashes($path) . "')";
                    $changed = true;
                } elseif (preg_match('/^\/Arbimaps\/(.*)$/i', $innerStr, $matches)) {
                    $path = $matches[1];
                    $text = "neiva_app_url('Arbimaps/" . addslashes($path) . "')";
                    $changed = true;
                }
            }

            $newContent .= $text;
        } else {
            $newContent .= $token;
        }
    }

    // Caso especial para definir APP_WEB_BASE en asignar_subsanacion.php
    if ($relFile === 'Arbimaps/vistas/seguimiento/asignar_subsanacion.php') {
        if (str_contains($newContent, "define('APP_WEB_BASE', '/Arbimaps');")) {
            $newContent = str_replace(
                "define('APP_WEB_BASE', '/Arbimaps');",
                "define('APP_WEB_BASE', neiva_app_base_path() . '/Arbimaps');",
                $newContent
            );
            $newContent = str_replace(
                "define('APP_FS_BASE', rtrim(\$_SERVER['DOCUMENT_ROOT'], '/\\\\') . APP_WEB_BASE);",
                "define('APP_FS_BASE', neiva_public_path('Arbimaps'));",
                $newContent
            );
            $changed = true;
        }
    }

    if ($changed) {
        if ($write) {
            file_put_contents($filePath, $newContent);
            echo "[CORREGIDO] $relFile\n";
        } else {
            echo "[DETECTADO] $relFile (ejecute con --write para guardar)\n";
        }
    }
}
