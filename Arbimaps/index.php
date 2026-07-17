<?php
require "../conexion.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/config/permisos.php";

neiva_require_auth();

$MODULOS_HABILITADOS = [
    'dashboard',
    'tramites',
    'seguimiento',
    'cert_catastrales',
    'restricciones_predios',
    'usuarios',
    'soporte',
    'perfil',
];

$permisosDeshabilitados = [
    'menu.noticias',
    'menu.solicitudesBaqueanos',
    'menu.publicarNoticias',
    'menu.cuentas.titulo',
    'menu.validacion',
    'menu.cuentaValidacion',
    'menu.validacionSeguridadSocial',
    'menu.validacionJefeOperaciones',
    'menu.radicacion',
    'menu.radicacionRadicar',
    'menu.radicacionMisCuentasRadicadas',
    'menu.radicacionDetallesCuentas',
    'menu.radicacionCuentasAprobadas',
    'menu.radicacionCuentasPagadas',
    'menu.radicacionCuentasRechazadas',
    'menu.radicacionTableroControl',
    'menu.personal',
    'menu.miPerfilPersonal',
    'menu.personalContratacion',
    'menu.personalActivo',
    'menu.personalInactivo',
    'menu.personalJefeOperaciones',
    'menu.personalViabilidadFinanciera',
    'menu.personalGerencia',
    'menu.personalTalentoHumano',
    'menu.baqueanos',
    'menu.baqueanosMenuSolicitudes',
    'menu.baqueanosSolicitudBaqueanos',
    'menu.baqueanosSolicitudes',
    'menu.baqueanosDetallesSolicitudes',
    'menu.baqueanosValidarSolicitud',
    'menu.baqueanosValidacionFinal',
    'menu.baqueanosTableroControl',
    'menu.baqueanosPagoAprobaciones',
    'menu.baqueanosAprobarCuentasOperaciones',
    'menu.baqueanosAprobarCuentasGerencia',
    'menu.baqueanosValidarProfesionalSocial',
    'menu.baqueanosMenuCuentas',
    'menu.baqueanosCuentasPagadas',
    'menu.licitaciones',
    'menu.licitacionesSolicitudLicitaciones',
    'menu.licitacionesConsultarLicitaciones',
    'menu.licitacionesTableroControl',
];

foreach ($permisosDeshabilitados as $permisoDeshabilitado) {
    $PERMISOS[$permisoDeshabilitado] = [];
}

$error_login = "";

$sql = "
SELECT *
FROM (
    /* ===== TRÁMITES QUE YA ESTÁN EN REVISIÓN ===== */
    SELECT 
        hr.historial_cod_tramite,
        hr.asignacion_nombre_usuario AS historial_nombre_usuario,
        hr.asignacion_apellido_usuario AS historial_apellido_usuario,
        hr.asignacion_rol_usuario AS historial_rol_usuario,
        hr.historial_estado_tramite,
        hr.fecha_limite,
        'revision' AS etapa
    FROM historial_revision hr

    UNION ALL

    /* ===== TRÁMITES QUE AÚN NO HAN ENTRADO A REVISIÓN ===== */
    SELECT 
        ha.historial_cod_tramite,
        ha.historial_nombre_usuario,
        ha.historial_apellido_usuario,
        ha.historial_rol_usuario,
        ha.historial_estado_tramite,
        ha.fecha_limite,
        'asignacion' AS etapa
    FROM historial_asignacion ha
    WHERE NOT EXISTS (
        SELECT 1
        FROM historial_revision hr2
        WHERE hr2.historial_cod_tramite = ha.historial_cod_tramite
    )
) AS tramites_actuales
ORDER BY fecha_limite DESC
";

$resultado = $mysqli->query($sql);

$tramites = [];
if ($resultado) {
    while ($row = $resultado->fetch_assoc()) {
        $tramites[] = $row;
    }
}

// Consulta para contar todas las asignaciones
$sql = "SELECT COUNT(*) AS total_asignaciones FROM historial_asignacion";
$result = $mysqli->query($sql);
$total_asignaciones = 0;
if ($result && $row = $result->fetch_assoc()) {
    $total_asignaciones = $row['total_asignaciones'];
}

// Consulta para contar asignaciones vencidas
$sql_vencidas = "
    SELECT COUNT(DISTINCT t.historial_cod_tramite) AS total_vencidas
    FROM (
        SELECT
            hr.historial_cod_tramite,
            hr.fecha_limite
        FROM historial_revision hr

        UNION ALL

        SELECT
            ha.historial_cod_tramite,
            ha.fecha_limite
        FROM historial_asignacion ha
        WHERE NOT EXISTS (
            SELECT 1
            FROM historial_revision hr2
            WHERE hr2.historial_cod_tramite = ha.historial_cod_tramite
        )
    ) AS t
    WHERE t.fecha_limite < CURDATE()
      AND NOT EXISTS (
          SELECT 1
          FROM procede_tramite pt
          WHERE pt.cod_radicacion_tramite = t.historial_cod_tramite
      )
      AND NOT EXISTS (
          SELECT 1
          FROM no_procede_completar npc
          WHERE npc.cod_radicacion_tramite = t.historial_cod_tramite
      )
";
$result_vencidas = $mysqli->query($sql_vencidas);
$total_vencidas = 0;
if ($result_vencidas && $row_vencidas = $result_vencidas->fetch_assoc()) {
    $total_vencidas = $row_vencidas['total_vencidas'];
}

$sql_tramites_activos = "
SELECT *
FROM (
    SELECT
        at.asignacion_cod_tramite AS historial_cod_tramite,
        COALESCE(NULLIF(ua.nombre_usuario, ''), at.asignacion_nombre_usuario) AS historial_nombre_usuario,
        COALESCE(NULLIF(ua.apellido_usuario, ''), at.asignacion_apellido_usuario) AS historial_apellido_usuario,
        COALESCE(NULLIF(ua.rol_usuario, ''), at.asignacion_rol_usuario) AS historial_rol_usuario,
        at.asignacion_estado_tramite AS historial_estado_tramite,
        at.fecha_limite,
        at.asignacion_fecha_tramite AS fecha_movimiento,
        'asignacion' AS etapa
    FROM asignacion_tramite at
    LEFT JOIN usuarios_cons ua
        ON ua.cedula_usuario = at.asignacion_cc_usuario

    UNION ALL

    SELECT
        ea.entrega_cod_tramite AS historial_cod_tramite,
        COALESCE(NULLIF(ue.nombre_usuario, ''), ea.entrega_nombre_usuario) AS historial_nombre_usuario,
        COALESCE(NULLIF(ue.apellido_usuario, ''), ea.entrega_apellido_usuario) AS historial_apellido_usuario,
        COALESCE(NULLIF(ue.rol_usuario, ''), ea.entrega_rol_usuario) AS historial_rol_usuario,
        ea.historial_estado_tramite,
        ea.fecha_limite,
        COALESCE(ea.fecha_creacion, ea.historial_fecha_tramite) AS fecha_movimiento,
        'revision' AS etapa
    FROM entrega_asignacion ea
    LEFT JOIN usuarios_cons ue
        ON ue.cedula_usuario = ea.entrega_cc_usuario
) AS movimientos
WHERE NOT EXISTS (
    SELECT 1
    FROM procede_tramite pt
    WHERE pt.cod_radicacion_tramite = movimientos.historial_cod_tramite
)
AND NOT EXISTS (
    SELECT 1
    FROM no_procede_completar npc
    WHERE npc.cod_radicacion_tramite = movimientos.historial_cod_tramite
)
AND NOT EXISTS (
    SELECT 1
    FROM tramites_cancelados tc
    WHERE tc.cod_tramite = movimientos.historial_cod_tramite
      AND tc.estado = 'CANCELADO'
)
ORDER BY fecha_movimiento DESC, etapa DESC
";

$resultado_activos = $mysqli->query($sql_tramites_activos);
$tramites = [];
$tramites_vistos = [];
if ($resultado_activos) {
    while ($row_activo = $resultado_activos->fetch_assoc()) {
        $cod_tramite_actual = $row_activo['historial_cod_tramite'] ?? '';
        if ($cod_tramite_actual === '' || isset($tramites_vistos[$cod_tramite_actual])) {
            continue;
        }

        $tramites_vistos[$cod_tramite_actual] = true;
        $tramites[] = $row_activo;
    }
}

$total_asignaciones = count($tramites);
$total_vencidas = 0;
$hoy_vencidas = new DateTime();
foreach ($tramites as $tramite_vencida) {
    $fecha_limite_vencida = $tramite_vencida['fecha_limite'] ?? '';
    if ($fecha_limite_vencida === '' || $fecha_limite_vencida === '0000-00-00') {
        continue;
    }

    $fecha_limite_obj = new DateTime($fecha_limite_vencida);
    if ((int)$hoy_vencidas->diff($fecha_limite_obj)->format('%r%a') < 0) {
        $total_vencidas++;
    }
}

$sql_culminados = "SELECT COUNT(*) AS total_cul FROM procede_tramite";
$result_culminados = $mysqli->query($sql_culminados);
$total_cul = 0;
if ($result_culminados && $row_culminados = $result_culminados->fetch_assoc()) {
    $total_cul = $row_culminados['total_cul'];
}

$sql3 = "SELECT COUNT(*) AS cod_tramite FROM tramite_radicacion";
$result3 = $mysqli->query($sql3);
$total_radicadas = 0;
if ($result3 && $row_radicadas = $result3->fetch_assoc()) {
    $total_rad = $row_radicadas['cod_tramite'];
}

$noticiaspages = [
    'noticias/vistas/solicitudes_contactanos',
    'noticias/vistas/cargar_noticia',
];

$cuentaspages = [
    'cuentas/radicar_cuenta',
    'cuentas/mis_cuentas',
    'cuentas/detalles_cuentas',
    'cuentas/cuentas_aprobadas',
    'cuentas/cuentas_rechazadas',
    'cuentas/cuentas_pagadas',
    'cuentas/radicacion/imprimir_cuenta_aprobada',
    'cuentas/radicacion/detalle_cuenta_pagada',
    'cuentas/radicacion/detalle_cuenta_rechazada',
    'cuentas/radicacion/detalles_cuenta',
    'cuentas/radicacion/detalle_cuenta',
    'cuentas/radicacion/detalle_cuenta_rechazadas',
    'cuentas/radicacion/tablero_control',
    'cuentas/radicacion/detalles_mi_cuenta',
];

$validacioncuentaspages = [
    'cuentas/validar_cuentas',
    'cuentas/aprobacion_social',
    'cuentas/aprobacion_operaciones',
    'cuentas/validacion/revisar_cuenta_social',
    'cuentas/validacion/revisar_cuenta_operaciones',
    'cuentas/validacion/revisar_cuentas'
];

$datosgestionpages = ['dashboardcopy'];

$tramitesPages = [
    'tramites/crear_tramite',
    'tramites/consultar_tramite',
    'tramites/asignacion_tramites',
    'tramites/revision_tramites',
    'tramites/cargue_tramite_rad',
    'tramites/acciones/ver_tramite_rad',
    'tramites/acciones/asignar_tram_procedencia',
    'tramites/base_catastral/predio_mutacion',
    'tramites/base_catastral/consolidar_oficial',
    'tramites/dashboard_usuario',
    'tramites/acciones/editar_tramite',
    'tramites/asignar_tramite_dos',
    'tramites/asignar_tramite',
    'tramites/cargue_tramite_rad_dos',
    'tramites/cargue_tramite_rad_dos_1',
    'tramites/cargue_tramite_rad_dos_pru',
    'tramites/cuentas_canceladas/cancelados',
    'tramites/cuentas_rechazadas/rechazados',
    'tramites/cuentas_rechazadas/no_procede_completar',
    'tramites/cuentas_completadas/tramites_completos',
    'tramites/dashboard'
];

$restriccionesPrediosPages = [
    'restricciones_predios/administrar'
];

$seguimientoPages = [
    'seguimiento/mis_asignaciones',
    'seguimiento/mis_revisiones',
    'seguimiento/mis_devoluciones',
    'seguimiento/estado_tramite',
    'seguimiento/asignar_revision_segun_rol',
    'seguimiento/asignar_revision',
    'seguimiento/asignar_subsanacion',
    'seguimiento/acciones/procesar_entrega_segun_rol',
    'seguimiento/acciones/procesar_entrega',
    'seguimiento/acciones/procesar_subsanacion',
    'seguimiento/resolucion',
    'seguimiento/mis_notificaciones_tramites',
];

$certcatas = [
    'cert_catastrales/consulta_cert_catastrales',
    'cert_catastrales/generar_cert_catastrales',
    'cert_catastrales/manzanas_catastral',
    'cert_catastrales/gestionar_pago_certificado',
    'cert_catastrales/acciones/insertar_sol_cert',
    'cert_catastrales/acciones/ver_datos_certificado'
];

$prodcatas = [
    'prod_catastrales/solicitar_producto',
    'prod_catastrales/consultar_producto',
    'prod_catastrales/cargar_documentos_producto',
    'prod_catastrales/asignar_producto',
    'prod_catastrales/revisar_producto',
];


$soportePages = [
    'soporte/solicitud_revision',
    'soporte/acciones/cargar_reporte',
    'soporte/mesa_ayuda',
    'soporte/asignar_error',
    'soporte/servicio_cliente',
    'soporte/base',
    'soporte/chat_soporte'
];

$usuarios = ['Usuarios/crear_usuario', 'Usuarios/consultar_usuario', 'seguimiento_usuario'];

$personal = [
    'Personal/contratacion',
    'Personal/personal_activo',
    'Personal/personal_inactivo',
    'Personal/solicitudes_dir_operaciones',
    'Personal/solicitudes_seguridad_social',
    'Personal/viabilidad_financiera',
    'Personal/gerencia',
    'Personal/talento_humano',
    'Personal/informacion_personal',
    'Personal/ver_otrosi',
    'Personal/ver_estudios',
    'Personal/revisar_solicitud_otrosi',
    'Personal/revisar_modificaciones_gerencia',
    'Personal/revisar_solicitud_gerencia',
    'Personal/editar_solicitud',
    'Personal/cargar_talento_humano',
    'Personal/editar_personal',
    'Personal/completar_datos',
    'Personal/mis_perfiles',
    'Personal/contratacion_individual'
];

$licitaciones = [
    'licitaciones/solicitud_licitaciones',
    'licitaciones/consultar_licitaciones',
    'licitaciones/tablero_control_licitaciones',
];

$baqueanos = [
    'baqueanos/solicitudes/vistas/solicitud_baqueanos',
    'baqueanos/solicitudes/vistas/solicitudes_baqueanos',
    'baqueanos/solicitudes/vistas/validar_solicitud',
    'baqueanos/solicitudes/vistas/detalles_profecional_social',
    'baqueanos/solicitudes/vistas/detalles_cargar_cuenta',
    'baqueanos/solicitudes/vistas/detalles_profesional_social',
    'baqueanos/solicitudes/vistas/informacion_solicitud_profesional',
    'baqueanos/solicitudes/vistas/detalles_profesional_solicitudes',
    'baqueanos/solicitudes/vistas/informacion_solicitud_gerencia',
    'baqueanos/solicitudes/vistas/detalles_profesional_rechazadas',
    'baqueanos/solicitudes/vistas/radicar_cuenta_baqueanos',
    'baqueanos/solicitudes/vistas/detalles_cuentas',
    'baqueanos/solicitudes/vistas/detalles_solicitudes',
    'baqueanos/solicitudes/vistas/informacion_solicitud_operaciones',
    'baqueanos/solicitudes/vistas/detalles_gerencia',
    'baqueanos/solicitudes/vistas/tablero_control',
    'baqueanos/solicitudes/vistas/detalles_pagos',
    'baqueanos/solicitudes/vistas/notificaciones_baqueanos',
    'baqueanos/solicitudes/vistas/detalles_cargar_soporte',
    'baqueanos/solicitudes/vistas/informacion_solicitud',
    'baqueanos/solicitudes/vistas/informacion_mi_solicitud',
    'baqueanos/solicitudes/vistas/revisar_baqueanos_two',
    'baqueanos/solicitudes/vistas/revisar_baqueanos_operaciones',
    'baqueanos/cuentas/vistas/detalles_cuentas_pagadas',
    'baqueanos/solicitudes/vistas/aprobacion_radicacion_gerencia',
    'baqueanos/cuentas/vistas/informacion_solicitud_radicacion'
];

$baqueanosCuentas = [
    'baqueanos/cuentas/vistas/detalles_cuentas_pagadas',
];

$currentPage            = $_GET['page'] ?? 'dashboardcopy';
if (preg_match('/^tramites\/dashboard_usuario=(.+)$/', $currentPage, $dashboardUsuarioMatch)) {
    $currentPage = 'tramites/dashboard_usuario';
    $_GET['page'] = $currentPage;
    $_GET['usuario'] = $_GET['usuario'] ?? $dashboardUsuarioMatch[1];
}
if ($currentPage === 'baqueanos/cuentas/vistas/informacion_solicitud_Radicacion') {
    $currentPage = 'baqueanos/cuentas/vistas/informacion_solicitud_radicacion';
    $_GET['page'] = $currentPage;
}
$perfilPages = [
    'Perfil/editar_perfil',
    'Perfil/acciones/actualizar_perfil',
    'Perfil/actualizacion/editar_perfil',
    'Perfil/actualizacion/actualizar_perfil',
];
$paginasHabilitadas = array_merge(
    $datosgestionpages,
    $tramitesPages,
    $certcatas,
    $prodcatas,
    $restriccionesPrediosPages,
    $seguimientoPages,
    $usuarios,
    $soportePages,
    $perfilPages
);
if (!in_array($currentPage, $paginasHabilitadas, true)) {
    $currentPage = 'dashboardcopy';
    $_GET['page'] = $currentPage;
}

$pagePermission = null;
if (in_array($currentPage, $tramitesPages, true)) {
    $pagePermission = 'menu.tramites';
} elseif (in_array($currentPage, $certcatas, true)) {
    $pagePermission = 'menu.cert_catastrales';
} elseif (in_array($currentPage, $prodcatas, true)) {
    $pagePermission = 'menu.productos_catastrales';
} elseif (in_array($currentPage, $restriccionesPrediosPages, true)) {
    $pagePermission = 'predios_bloqueados.administrar';
} elseif (in_array($currentPage, $seguimientoPages, true)) {
    $pagePermission = 'menu.seguimiento';
} elseif (in_array($currentPage, $usuarios, true)) {
    $pagePermission = 'menu.usuarios';
} elseif (in_array($currentPage, $soportePages, true)) {
    $pagePermission = 'menu.soporte';
} elseif (in_array($currentPage, $perfilPages, true)) {
    $pagePermission = 'menu.miPerfilPersonal';
}

if ($pagePermission !== null) {
    neiva_require_permission($pagePermission, $PERMISOS);
}

function seleccionarRolModuloActual(?string $permisoModulo, array $permisos): string
{
    $rolesUsuario = function_exists('getRolesUsuario')
        ? getRolesUsuario()
        : array_values(array_filter([
            $_SESSION['rol_usuario'] ?? null,
            $_SESSION['rol_usuario_dos'] ?? null,
            $_SESSION['rol_usuario_tres'] ?? null,
        ]));

    if ($permisoModulo !== null && isset($permisos[$permisoModulo])) {
        $rolesPermitidos = is_array($permisos[$permisoModulo])
            ? $permisos[$permisoModulo]
            : [$permisos[$permisoModulo]];

        foreach ($rolesUsuario as $rolUsuario) {
            if (in_array($rolUsuario, $rolesPermitidos, true)) {
                return $rolUsuario;
            }
        }
    }

    return $rolesUsuario[0] ?? 'Sin rol';
}

$rolModuloActual        = seleccionarRolModuloActual($pagePermission, $PERMISOS);
$nombreModuloActual     = 'Panel principal';

$isSocial = in_array($currentPage, $noticiaspages);

if (in_array($currentPage, $tramitesPages) || in_array($currentPage, $seguimientoPages)) {
    $nombreModuloActual = 'PQR - Trámites y seguimiento';
}
if (in_array($currentPage, $restriccionesPrediosPages)) {
    $nombreModuloActual = 'Bloqueo de predios';
}
if (in_array($currentPage, $certcatas)) {
    $nombreModuloActual = 'Certificados catastrales';
}
if (in_array($currentPage, $prodcatas)) {
    $nombreModuloActual = 'Productos catastrales';
}
if (in_array($currentPage, $cuentaspages) || in_array($currentPage, $validacioncuentaspages)) {
    $nombreModuloActual = 'Cuentas';
}
if (in_array($currentPage, $usuarios)) {
    $nombreModuloActual = 'Gestión de usuarios';
}
if (in_array($currentPage, $personal)) {
    $nombreModuloActual = 'Gestión de personal';
}
if (in_array($currentPage, $soportePages)) {
    $nombreModuloActual = 'Soporte técnico';
}

$notificaciones = [];
$notificacionesBaqueanos = [];
$notificacionesNoLeidas = 0;

$idUsuarioLogueado = $_SESSION['id_usuario'] ?? null;

$listaUnificada = [];
$notificacionesNoLeidas = 0;

$cedula_usuario_alertas = $_SESSION['cedula_usuario'] ?? '';
if (!empty($cedula_usuario_alertas)) {
    $sql_alertas = "
        SELECT *
        FROM (
            SELECT
                at.asignacion_cod_tramite AS cod_tramite,
                at.creacion_tram_nombre_usuario AS remitente_nombre,
                at.creacion_tram_apellido_usuario AS remitente_apellido,
                at.creacion_tram_rol_usuario AS remitente_rol,
                at.asignacion_fecha_tramite AS fecha,
                at.fecha_limite,
                at.asignacion_cc_usuario AS cc_usuario,
                'Asignación' AS tipo_alerta
            FROM asignacion_tramite at

            UNION ALL

            SELECT
                ea.entrega_cod_tramite AS cod_tramite,
                ea.creacion_tram_nombre_usuario AS remitente_nombre,
                ea.creacion_tram_apellido_usuario AS remitente_apellido,
                ea.creacion_tram_rol_usuario AS remitente_rol,
                COALESCE(ea.fecha_creacion, ea.historial_fecha_tramite) AS fecha,
                ea.fecha_limite,
                ea.entrega_cc_usuario AS cc_usuario,
                'Revisión' AS tipo_alerta
            FROM entrega_asignacion ea
        ) AS movimientos
        WHERE TRIM(CAST(movimientos.cc_usuario AS CHAR)) = TRIM(CAST(? AS CHAR))
          AND NOT EXISTS (
              SELECT 1 FROM (
                  SELECT asignacion_cod_tramite AS cod, asignacion_fecha_tramite AS f FROM asignacion_tramite
                  UNION ALL
                  SELECT entrega_cod_tramite AS cod, COALESCE(fecha_creacion, historial_fecha_tramite) AS f FROM entrega_asignacion
              ) AS ultimos
              WHERE ultimos.cod = movimientos.cod_tramite
                AND ultimos.f > movimientos.fecha
          )
          AND NOT EXISTS (
              SELECT 1 FROM procede_tramite pt 
              WHERE pt.cod_radicacion_tramite = movimientos.cod_tramite
          )
          AND NOT EXISTS (
              SELECT 1 FROM no_procede_completar npc 
              WHERE npc.cod_radicacion_tramite = movimientos.cod_tramite
          )
        ORDER BY movimientos.fecha DESC
    ";

    if ($stmt_alertas = $mysqli->prepare($sql_alertas)) {
        $stmt_alertas->bind_param("s", $cedula_usuario_alertas);
        if ($stmt_alertas->execute()) {
            $result_alertas = $stmt_alertas->get_result();
            while ($row_alerta = $result_alertas->fetch_assoc()) {
                $remitente = trim(($row_alerta['remitente_nombre'] ?? '') . ' ' . ($row_alerta['remitente_apellido'] ?? ''));
                $rol_remitente = str_replace('_', ' ', $row_alerta['remitente_rol'] ?? '');

                $mensaje = "Trámite " . $row_alerta['cod_tramite'] . " asignado por " . $remitente . " (" . $rol_remitente . ")";

                $listaUnificada[] = [
                    'id' => $row_alerta['cod_tramite'],
                    'solicitud_id' => $row_alerta['cod_tramite'],
                    'mensaje' => $mensaje,
                    'fecha' => $row_alerta['fecha'],
                    'leido' => 0,
                    'src' => 'tr'
                ];
                $notificacionesNoLeidas++;
            }
        }
        $stmt_alertas->close();
    }
}

// Obtener trámites recién creados y no asignados aún (notificación para ventanilla y admin)
$rol_usuario_alertas = $_SESSION['rol_usuario'] ?? '';
if ($rol_usuario_alertas === 'ventanilla_catastral' || $rol_usuario_alertas === 'administrador') {
    $sql_nuevos = "
        SELECT 
            t.cod_tramite,
            t.fecha_rad AS fecha
        FROM tramite_radicacion t
        WHERE NOT EXISTS (
            SELECT 1 FROM asignacion_tramite at 
            WHERE at.asignacion_cod_tramite = t.cod_tramite
        )
        AND NOT EXISTS (
            SELECT 1 FROM tramites_cancelados tc 
            WHERE tc.cod_tramite = t.cod_tramite AND tc.estado = 'CANCELADO'
        )
        ORDER BY t.fecha_rad DESC
    ";
    if ($result_nuevos = $mysqli->query($sql_nuevos)) {
        while ($row_nuevo = $result_nuevos->fetch_assoc()) {
            $mensaje = "Nuevo trámite " . htmlspecialchars($row_nuevo['cod_tramite'], ENT_QUOTES, 'UTF-8') . " radicado y pendiente de asignación.";
            $listaUnificada[] = [
                'id' => $row_nuevo['cod_tramite'],
                'solicitud_id' => $row_nuevo['cod_tramite'],
                'mensaje' => $mensaje,
                'fecha' => $row_nuevo['fecha'],
                'leido' => 0,
                'src' => 'tr_nuevo'
            ];
            $notificacionesNoLeidas++;
        }
    }
}



$notificaciones_chat = "SELECT 
                            id_mensaje, 
                            codigo_error, 
                            id_usuario, 
                            mensaje, 
                            fecha_envio, 
                            tipo_remitente, 
                            leido_soporte
                        FROM soporte_chat
                        ORDER BY fecha_envio ASC";
$mostrar_notificaciones = $mysqli->query($notificaciones_chat);


$resultado = null;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>ARBIMapps | Neiva</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/sb-asig-rev.css">
    <link rel="icon" type="image/png" href="../imagen/L_NW.webp">
    <link href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_CO">
    <meta property="og:site_name" content="Arbitrium S.A.S">
    <meta property="og:title" content="Arbitrium S.A.S | Gestión predial y Catastro Multipropósito.">
    <meta property="og:description"
        content="Soluciones integrales en gestión predial, catastro multipropósito y tecnología en Colombia.">
    <meta property="og:url" content="https://www.arbitrium.com.co/">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        window.neivaDataTablesLanguage = function() {
            return {
                decimal: "",
                emptyTable: "No hay información disponible en la tabla",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                infoEmpty: "Mostrando 0 a 0 de 0 registros",
                infoFiltered: "(filtrado de _MAX_ registros totales)",
                lengthMenu: "Mostrar _MENU_ registros",
                loadingRecords: "Cargando...",
                processing: "Procesando...",
                search: "Buscar:",
                zeroRecords: "No se encontraron registros coincidentes",
                paginate: {
                    first: "Primero",
                    last: "Último",
                    next: "Siguiente",
                    previous: "Anterior"
                },
                aria: {
                    sortAscending: ": activar para ordenar la columna ascendente",
                    sortDescending: ": activar para ordenar la columna descendente"
                }
            };
        };

        window.neivaApplyDataTablesSpanishDefaults = function() {
            if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.dataTable) {
                return;
            }
            window.jQuery.extend(true, window.jQuery.fn.dataTable.defaults, {
                language: window.neivaDataTablesLanguage()
            });
        };

        document.addEventListener('load', function(event) {
            const target = event.target;
            if (target && target.tagName === 'SCRIPT' && /jquery\.dataTables/i.test(target.src || '')) {
                window.neivaApplyDataTablesSpanishDefaults();
            }
        }, true);

        document.addEventListener('DOMContentLoaded', window.neivaApplyDataTablesSpanishDefaults);
    </script>

</head>

<body>
    <div class="wrapper p-2" style="display: flex; height: 100vh;">
        <div class="sidebar d-flex flex-column gap-2 rounded-4 ">

            <div class="d-flex flex-column align-items-center justify-content-center gap-2 p-4 py-3 rounded-5 panel_plataforma shadow ">
                <div class="p-2 bg-white rounded-5 panel_plataforma_dot"></div>
                <div class="w-100 ">
                    <img src="../imagen/L_Neiva.png" alt="Logo-conervación" style="width:2.1em;">
                </div>

                <div class="d-flex flex-column align-items-center justify-content-center  w-100 ">
                    <h4 class="text-center fw-bold m-0 w-100  d-flex justify-content-start" style="letter-spacing: 0.8px; font-size: 1.45rem;"> ARBI<span class="text-outline" style="letter-spacing: 1px;">Mapps</span></h4>
                    <small class="w-100 fw-bold text-start mt-0">Neiva</small>
                </div>
                <small class="text-start mt-1" style="font-size: 0.60rem;">Gestión de trámites prediales y catastrales para la ciudad de Neiva.</small>
            </div>


            <ul class="nav  flex-column mb-auto acordion " id="accordionSidebar">

                <hr class="m-2 " style="color: #002f44 !important;">

                <li class="my-2 ">
                    <a href="index.php?page=dashboardcopy" class="nav-link d-flex gap-2 justify-content-center align-items-center py-2 m-0 <?php echo in_array($currentPage, $datosgestionpages) ? 'active' : 'collapsed'; ?> " style="font-size: 0.8em;">
                        <i class="bi bi-house-fill" style="font-size: 1.3em"></i> Página Inicial
                    </a>
                </li>

                <hr class="m-2 " style="color: #002f44 !important;">

                <?php if (usuarioTieneAlgunRol($PERMISOS['menu.tramites'])): ?>
                    <!-- <hr class="m-2" style="background-color: #0f569980;"> -->
                    <small class="my-2" style="color: #0A2C1B; font-size: 0.95rem; font-weight: 600;">Trámites Catastrales</small>
                    <li class="nav-item" style="font-size: 0.9em;">
                        <a class="nav-link collapsed <?php echo in_array($currentPage, $tramitesPages) ? 'active' : 'collapsed'; ?>"
                            data-bs-toggle="collapse" href="#tramites"
                            aria-expanded="false" aria-controls="tramites">
                            <i class="bi-envelope-paper me-2"></i> Trámites
                        </a>
                        <div class="collapse " id="tramites" data-bs-parent="#accordionSidebar" aria-labelledby="headingTre">
                            <div class="bg-white shadow-sm rounded-4 p-2" style="color: #0F5699;">
                                <h6 class="my-2 fw-bold ms-2" style="font-size: 0.8rem; color:#7f8e85">Acciones trámites</h6>
                                <?php if (usuarioTieneAlgunRol($PERMISOS['tramites.crear'])): ?>
                                    <a href="index.php?page=tramites/crear_tramite" class="nav-link especial  mb-1" style="font-size: 0.8rem; color:002F55">
                                        <i class="bi bi-file-earmark-plus me-1"></i> Crear Trámite
                                    </a>
                                <?php endif; ?>
                                <?php if (usuarioTieneAlgunRol($PERMISOS['tramites.consultar'])): ?>
                                    <a href="index.php?page=tramites/consultar_tramite" class="nav-link mb-1 especial" style="font-size: 0.8rem;">
                                        <i class="bi bi-search me-1"></i> Consultar Trámite
                                    </a>
                                <?php endif; ?>
                                <?php if (usuarioTieneAlgunRol($PERMISOS['tramites.rechazados'])): ?>
                                    <a href="index.php?page=tramites/cuentas_rechazadas/rechazados" class="nav-link mb-1 especial" style="font-size: 0.8rem;">
                                        <i class="bi bi-file-earmark-x me-1"></i> Trámites rechazados
                                    </a>
                                <?php endif; ?>
                                <?php if (usuarioTieneAlgunRol($PERMISOS['tramites.cancelados'])): ?>
                                    <a href="index.php?page=tramites/cuentas_canceladas/cancelados" class="nav-link mb-1 especial" style="font-size: 0.8rem;">
                                        <i class="bi bi-x-octagon me-1"></i> Trámites cancelados
                                    </a>
                                <?php endif; ?>
                                <?php if (usuarioTieneAlgunRol($PERMISOS['tramites.completar'])): ?>
                                    <a href="index.php?page=tramites/cuentas_rechazadas/no_procede_completar" class="nav-link mb-1 especial" style="font-size: 0.8rem;">
                                        <i class="bi bi-file-richtext me-1"></i> Trámites por completar
                                    </a>
                                <?php endif; ?>
                                <?php if (usuarioTieneAlgunRol($PERMISOS['tramites.completos'])): ?>
                                    <a href="index.php?page=tramites/cuentas_completadas/tramites_completos" class="nav-link especial  mb-1" style="font-size: 0.8rem; color:002F55">
                                        <i class="bi bi-file-earmark-check me-1"></i> Trámites completados
                                    </a>
                                <?php endif; ?>
                                <?php if (usuarioTieneAlgunRol($PERMISOS['tramites.tablero'])): ?>
                                    <a href="index.php?page=tramites/dashboard" class="nav-link especial  mb-1" style="font-size: 0.8rem; color:002F55">
                                        <i class="bi bi-file-spreadsheet me-1"></i> Tablero control
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                <?php endif; ?>

                <?php if (usuarioTieneAlgunRol($PERMISOS['menu.cert_catastrales'])): ?>
                    <li class="nav-item" style="font-size: 0.9em;">
                        <a class="nav-link collapsed <?php echo in_array($currentPage, $certcatas) ? 'active' : 'collapsed'; ?>"
                            data-bs-toggle="collapse" href="#certificadosCatastrales"
                            aria-expanded="false" aria-controls="certificadosCatastrales">
                            <i class="bi bi-file-earmark-text me-2"></i> Cert. Catastrales
                        </a>
                        <div class="collapse " id="certificadosCatastrales" data-bs-parent="#accordionSidebar">
                            <div class="bg-white shadow-sm  rounded-4 p-2" style="color: #0F5699;">
                                <h6 class="my-2 fw-bold ms-2" style="font-size: 0.8rem; color:#7f8e85">Acciones certificados</h6>
                                <a href="index.php?page=cert_catastrales/generar_cert_catastrales" class="nav-link mb-0 especial" style="font-size: 0.8rem;">
                                    <i class="bi bi-file-earmark-plus me-2"></i> Generar certificado
                                </a>
                                <a href="index.php?page=cert_catastrales/consulta_cert_catastrales" class="nav-link mb-0 especial" style="font-size: 0.8rem;">
                                    <i class="bi bi-search me-2"></i> Consultar certificados
                                </a>
                            </div>
                        </div>
                    </li>
                <?php endif; ?>

                <?php if (usuarioTieneAlgunRol($PERMISOS['menu.productos_catastrales'])): ?>
                    <li class="nav-item" style="font-size: 0.9em;">
                        <a class="nav-link collapsed <?php echo in_array($currentPage, $prodcatas) ? 'active' : 'collapsed'; ?>"
                            data-bs-toggle="collapse" href="#productosCatastrales"
                            aria-expanded="false" aria-controls="productosCatastrales">
                            <i class="bi bi-file-earmark-text me-2"></i> Productos Catastrales
                        </a>
                        <div class="collapse " id="productosCatastrales" data-bs-parent="#accordionSidebar">
                            <div class="bg-white shadow-sm  rounded-4 p-2" style="color: #0F5699;">
                                <h6 class="my-2 fw-bold ms-2" style="font-size: 0.8rem; color:#7f8e85">Acciones productos catastrales</h6>
                                <?php if (usuarioTieneAlgunRol(['ventanilla_catastral', 'administrador'])): ?>
                                    <a href="index.php?page=prod_catastrales/solicitar_producto" class="nav-link mb-0 especial" style="font-size: 0.8rem;">
                                        <i class="bi bi-file-earmark-plus me-2"></i> Solicitar producto
                                    </a>
                                <?php endif; ?>
                                <a href="index.php?page=prod_catastrales/consultar_producto" class="nav-link mb-0 especial" style="font-size: 0.8rem;">
                                    <i class="bi bi-file-earmark-text me-2"></i> Consultar producto
                                </a>
                            </div>
                        </div>
                    </li>
                <?php endif; ?>

                <?php if (usuarioTieneAlgunRol($PERMISOS['predios_bloqueados.administrar'])): ?>
                    <hr class="m-2" style="color: #002f44 !important;">
                    <small class="my-2" style="color: #0A2C1B; font-size: 0.95rem; font-weight: 600;">Gestión predial</small>
                    <li class="nav-item" style="font-size: 0.9em;">
                        <a class="nav-link <?= in_array($currentPage, $restriccionesPrediosPages) ? 'active' : '' ?>"
                            href="index.php?page=restricciones_predios/administrar">
                            <i class="bi bi-lock-fill me-2"></i> Bloqueo de predios
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (usuarioTieneAlgunRol($PERMISOS['menu.seguimiento'])): ?>
                    <li class="nav-item my-1" style="font-size: 0.9rem;">
                        <a class="nav-link collapsed <?php echo in_array($currentPage, $seguimientoPages) ? 'active' : 'collapsed'; ?> "
                            data-bs-toggle="collapse" href="#seguimiento" class="nav-link">
                            <i class="bi-clipboard-data me-2 "></i> Seguimiento
                        </a>
                        <div class="collapse " id="seguimiento" data-bs-parent="#accordionSidebar" aria-labelledby="headingTre">
                            <div class="bg-white shadow-sm rounded-4 p-2" style="color: #0F5699;">
                                <h6 class="my-2 fw-bold ms-2" style="font-size: 0.8rem; color:#7f8e85">Acciones seguimientos</h6>
                                <a href="index.php?page=seguimiento/mis_asignaciones" class="nav-link mb-0 especial" style="font-size: 0.8rem;">
                                    <i class="bi bi-file-person m-1"></i> Mis Asignaciones</a>
                                <a href="index.php?page=seguimiento/mis_revisiones" class="nav-link mb-0 especial" style="font-size: 0.8rem;">
                                    <i class="bi bi-person-exclamation m-1"></i> Mis Revisiones</a>
                                <a href="index.php?page=seguimiento/mis_devoluciones" class="nav-link mb-0 especial" style="font-size: 0.8rem;">
                                    <i class="bi bi-person-down m-1"></i> Mis Devoluciones</a>
                                <a href="index.php?page=seguimiento/estado_tramite" class="nav-link mb-0 especial" style="font-size: 0.8rem;">
                                    <i class="bi bi-bar-chart-steps m-1"></i> Estado del Trámite</a>
                            </div>
                        </div>
                    </li>
                <?php endif; ?>

                <?php if (usuarioTieneAlgunRol($PERMISOS['menu.usuarios'])): ?>
                    <hr class="m-2" style="color: #002f44 !important;">
                    <small class="my-2" style="color: #0A2C1B; font-size: 0.95rem; font-weight: 600;">Gestión de Usuario</small>
                    <li class="nav-item" style="font-size: 0.9em;">
                        <a class="nav-link collapsed <?php echo in_array($currentPage, $usuarios) ? 'active' : 'collapsed'; ?>" data-bs-toggle="collapse" href="#usuarios" role="button"
                            aria-expanded="false" aria-controls="usuarios">
                            <i class="bi bi-people-fill me-2"></i> Usuarios
                        </a>

                        <div class="collapse" id="usuarios" data-bs-parent="#accordionSidebar" aria-labelledby="headingTwo">
                            <div class="bg-white rounded-4 shadow-sm p-2" style="color: #0F5699;">
                                <h6 class="my-2 fw-bold ms-2" style="font-size: 0.8rem; color:#7f8e85">Acciones Usuarios</h6>
                                <a href="index.php?page=Usuarios/crear_usuario" class="nav-link especial" style="font-size: 0.8rem;">
                                    <i class="bi bi-person-plus me-2"></i> Crear Usuario</a>
                                <a href="index.php?page=Usuarios/consultar_usuario" class="nav-link especial" style="font-size: 0.8rem;">
                                    <i class="bi bi-person-gear me-2"></i> Consultar Usuario</a>
                                <a href="index.php?page=seguimiento_usuario" class="nav-link especial" style="font-size: 0.8rem;">
                                    <i class="bi bi-calendar-check me-2"></i> Seguimiento Usuario</a>
                            </div>
                        </div>
                    </li>

                <?php endif; ?>

                <?php if (usuarioTieneAlgunRol($PERMISOS['menu.soporte'])): ?>
                    <hr class="m-2" style="color: #002f44 !important;">
                    <small class="my-2 " style="color: #0A2C1B; font-size: 0.95rem; font-weight: 600;">Soporte</small>
                    <li class="nav-item my-1" style="font-size: 0.9rem;">
                        <a class="nav-link collapsed <?php echo in_array($currentPage, $soportePages) ? 'active' : 'collapsed'; ?> "
                            data-bs-toggle="collapse" href="#soporte">
                            <i class="bi bi-headset m-2"></i></i> Soporte Tecnico
                        </a>
                        <div class="collapse " id="soporte" data-bs-parent="#accordionSidebar" aria-labelledby="headingSoporte">
                            <div class="bg-white rounded-4 shadow-sm p-2" style="color: #0F5699;">
                                <h6 class="my-2 fw-bold ms-2" style="font-size: 0.8rem; color:#7f8e85">Mesa de ayuda</h6>
                                <?php if (usuarioTieneAlgunRol($PERMISOS['soporte.ticket'])): ?>
                                    <a href="index.php?page=soporte/solicitud_revision" class="nav-link mb-1 especial" style="font-size: 0.8rem;">
                                        <i class="bi bi-bug-fill m-1"></i> Generar ticket
                                    </a>
                                <?php endif; ?>
                                <?php if (usuarioTieneAlgunRol($PERMISOS['soporte.mis_ticket'])): ?>
                                    <a href="index.php?page=soporte/servicio_cliente" class="nav-link mb-1 especial" style="font-size: 0.8rem;">
                                        <i class="bi bi-chat-square-dots m-1"></i> Mis ticket
                                    </a>
                                <?php endif; ?>
                                <?php if (usuarioTieneAlgunRol($PERMISOS['soporte.tickets_asignados'])): ?>
                                    <a href="index.php?page=soporte/mesa_ayuda" class="nav-link mb-1 especial" style="font-size: 0.8rem;">
                                        <i class="bi bi-terminal-plus m-1"></i> Tickets asignados
                                    </a>
                                <?php endif; ?>
                                <?php if (usuarioTieneAlgunRol($PERMISOS['soporte.gestion_tickets'])): ?>
                                    <a href="index.php?page=soporte/base" class="nav-link mb-1 especial" style="font-size: 0.8rem;">
                                        <i class="bi bi-life-preserver m-1"></i> Gestion tickets
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                <?php endif; ?>

            </ul>


        </div>

        <!-- <script>
            document.getElementById("toggleSidebar").addEventListener("click", function() {

                let sidebar = document.querySelector(".sidebar");
                if (sidebar.classList.contains("minimized")) {
                    sidebar.classList.add("expanding");
                    setTimeout(() => {
                        sidebar.classList.remove("expanding");
                    }, 300);
                }

                sidebar.classList.toggle("minimized");

                let icon = this.querySelector("i");
                icon.classList.toggle("bi-chevron-left");
                icon.classList.toggle("bi-chevron-right");
            });
        </script> -->

        <div class="content-wrapper ms-2 mb-2">
            <nav class="navbar navbar-expand-lg  rounded-4 p-1 mb-1">
                <div class="container-fluid p-2 px-3 justify-content-between">
                    <form id="navbarSearchForm"
                        class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100  navbar-search">
                        <div class="input-group shadow-sm  bg-white" style="border-radius: 12px; border: 1px solid #c7d3cd;">
                            <input type="text"
                                id="searchInput"
                                class="form-control bg-transparent border-0 small"
                                placeholder="Buscar trámites, perfil..."
                                aria-label="Search"
                                aria-describedby="basic-addon2"
                                autocomplete="off">
                            <div class="input-group-append">
                                <button class="btn text-white " type="button" style="background-color: #0A2C1B; border-radius:12px">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        <div id="searchSuggestions" class="search-suggestions"></div>
                    </form>

                    <div class=" d-flex justify-content-end align-items-center g-2">


                        <?php if (usuarioTieneAlgunRol($PERMISOS['menu.tokenNeiva'])): ?>
                            <a class="btn-geo me-4 text-start" href="https://visor-neiva-v5-beta.vercel.app/" target="_blank">
                                <i class="bi bi-geo-fill"></i>
                                <span style="font-size:0.89em">GeoVisor Neiva</span>
                            </a>
                        <?php endif; ?>
                        <!-- fin icono de chat de mensajes -->
                        <div class="d-flex justify-content-end align-items-center me-4  rounded-5">
                            <div class="nav-item dropdown">
                                <a class="nav-link position-relative"
                                    id="notificationsDropdown"
                                    href="#"
                                    role="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <!-- <i class="fa-regular fa-bell"></i> -->
                                    <i class="bi bi-bell"></i>
                                    <?php if ($notificacionesNoLeidas > 0): ?>
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                            style="font-size: 0.6rem; transform: translate(-40%, -40%);">
                                            <?php echo $notificacionesNoLeidas; ?>
                                        </span>
                                    <?php endif; ?>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end notifications-dropdown-menu"
                                    aria-labelledby="notificationsDropdown">
                                    <div class="notifications-header d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold">Notificaciones</span>
                                        <?php if ($notificacionesNoLeidas > 0): ?>
                                            <span class="badge rounded-pill">
                                                <?php echo $notificacionesNoLeidas; ?> nuevas
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div id="listaNotificaciones" class="notifications-list">
                                        <?php if (empty($listaUnificada)): ?>
                                            <div class="px-3 py-3 text-center text-muted" style="font-size: 0.8rem;">
                                                <i class="bi bi-bell-slash mb-1" style="font-size:1.4rem;"></i><br>
                                                No tienes notificaciones
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($listaUnificada as $n): ?>
                                                <?php
                                                $esLeida = (int)$n['leido'] === 1;

                                                if (($n['src'] ?? 'gen') === 'bq') {
                                                    $urlDetalle = 'index.php?page=baqueanos/solicitudes/vistas/notificaciones_baqueanos'
                                                        . '&id=' . $n['solicitud_id']
                                                        . '&notif=' . $n['id']
                                                        . '&src=bq';
                                                } elseif (($n['src'] ?? '') === 'tr') {
                                                    $urlDetalle = 'index.php?page=tramites/acciones/ver_tramite_rad'
                                                        . '&cod=' . urlencode($n['solicitud_id']);
                                                } elseif (($n['src'] ?? '') === 'tr_nuevo') {
                                                    $urlDetalle = 'index.php?page=tramites/acciones/asignar_tram_procedencia'
                                                        . '&cod=' . urlencode($n['solicitud_id'])
                                                        . '&src=tr_nuevo';
                                                } else {
                                                    $urlDetalle = 'index.php?page=Personal/detalle_solicitud_otrosi'
                                                        . '&id=' . $n['solicitud_id']
                                                        . '&notif=' . $n['id']
                                                        . '&src=gen';
                                                }
                                                ?>
                                                <a href="<?php echo $urlDetalle; ?>"
                                                    class="notification-item <?php echo $esLeida ? '' : 'unread'; ?>">
                                                    <span class="notification-dot"></span>
                                                    <div class="flex-grow-1">
                                                        <div class="notification-text">
                                                            <?php echo htmlspecialchars($n['mensaje'], ENT_QUOTES, 'UTF-8'); ?>
                                                        </div>
                                                        <div class="notification-meta text-muted mt-1">
                                                            <i class="bi bi-clock me-1"></i>
                                                            <?php echo date('d/m/Y H:i', strtotime($n['fecha'])); ?>
                                                        </div>
                                                    </div>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="notifications-footer text-center">
                                        <a class="text-decoration-none small"
                                            style="color:#0F5699;"
                                            href="index.php?page=seguimiento/mis_notificaciones_tramites">
                                            Ver mis notificaciones
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <span class="me-3 small"
                            id="infoRolUsuario"
                            data-rol-modulo="<?php echo htmlspecialchars($rolModuloActual ?? '', ENT_QUOTES); ?>"
                            data-modulo="<?php echo htmlspecialchars($nombreModuloActual ?? '', ENT_QUOTES); ?>">
                            <?php echo $_SESSION['nombre_usuario'] . " " . $_SESSION['apellido_usuario']; ?>
                            <br>
                            <b>Rol módulo:</b> <?php echo $rolModuloActual; ?><br>
                            <small>Módulo: <?php echo $nombreModuloActual; ?></small>
                        </span>

                        <div class="dropdown ">
                            <?php
                            $foto_usuario = '';
                            $mostrar_foto_usuario = false;

                            $foto_usuario_default = './assets/fotos_usuarios/default.png';

                            if (!empty($_SESSION['id_usuario']) && !empty($_SESSION['foto_user'])) {
                                $possible = __DIR__ . '/assets/fotos_usuarios/' . $_SESSION['id_usuario'] . '/' . $_SESSION['foto_user'];
                                if (file_exists($possible)) {
                                    $foto_usuario = './assets/fotos_usuarios/' . $_SESSION['id_usuario'] . '/' . $_SESSION['foto_user'];
                                    $mostrar_foto_usuario = true;
                                }
                            }
                            $display_name = htmlspecialchars((($_SESSION['nombre_usuario'] ?? '') . ' ' . ($_SESSION['apellido_usuario'] ?? '')));
                            ?>
                            <a class="btn  dropdown-toggle bg-white menu-user rounded-4 shadow px-2 d-flex align-items-center" style="color: #002F55; gap:8px;" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php if ($mostrar_foto_usuario): ?>
                                    <span class="user-avatar" style="background-image: url('<?php echo $foto_usuario; ?>')" title="Foto de <?php echo $display_name; ?>"></span>
                                <?php else: ?>
                                    <span class="user-avatar small" style="background-image: url('<?php echo $foto_usuario_default; ?>');" title="Sin foto de <?php echo $display_name; ?>"></span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end animated--grow-in rounded-4 p-2">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="index.php?page=Perfil/editar_perfil">
                                        <i class="bi bi-person-circle"></i>
                                        <span>Perfil</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 py-2"
                                        href="index.php?page=Perfil/editar_perfil">
                                        <i class="bi bi-file-earmark-medical"></i>
                                        <span>Información de contratación</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="">
                                        <i class="bi bi-toggles2"></i>
                                        <span>Configuraciones</span>
                                    </a>
                                </li>
                                <li>
                                    <hr class="my-2" style="border-color:#002f44;">
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2"
                                        data-bs-toggle="modal" data-bs-target="#cerrarsesion">
                                        <i class="bi bi-box-arrow-left"></i>
                                        <span>Cerrar sesión</span>
                                    </button>
                                </li>
                            </ul>
                        </div>

                    </div>


            </nav>

            <main class="container-fluid py-1 pb-0 px-0 mb-3 ">
                <?php
                $page = isset($_GET['page']) ? $_GET['page'] : 'dashboardcopy';
                $file = "vistas/{$page}.php";

                if (file_exists($file)) {
                    include $file;
                } else {
                    include "vistas/404.php";
                }
                ?>


                <button
                    type="button"
                    class="position-fixed bottom-0 end-0 overflow mb-3 me-3 btn rounded-circle bg-warning"
                    data-bs-toggle="modal"
                    data-bs-target="#chatbotModal"
                    title="Preguntas frecuentes"
                    style="z-index:1050;">
                    <i class="bi bi-person-raised-hand fs-4"></i>
                </button>

                <!-- Modal del Chatbot -->
                <div class="modal fade" id="chatbotModal" tabindex="-1" aria-labelledby="chatbotModalLabel" aria-hidden="true" style="z-index: 5500;">
                    <div class="modal-dialog">
                        <div class="modal-content " style="border-radius: 15px; overflow: hidden;">
                            <!-- Header -->
                            <div class="modal-header header-bot px-4" style="background: linear-gradient(135deg, #0F5699 0%, #002F55 100%) ; color: white; border: none;">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bot-avatar" style="width: 35px; height: 35px; font-size: 16px;">
                                        <i class="bi bi-robot"></i>
                                    </div>
                                    <div>
                                        <h5 class="modal-title mb-0" id="chatbotModalLabel">Asistente de preguntas frecuentes</h5>
                                        <small style="opacity: 0.9;"> <i class="bi bi-circle-fill text-success"></i> En línea</small>
                                    </div>
                                </div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <!-- Chat Container -->
                            <div class="position-relative">
                                <div class="chat-container " id="chatContainer">
                                    <!-- Los mensajes se generan aquí dinámicamente -->
                                </div>
                                <button class="scroll-to-bottom" id="scrollBtn">
                                    <i class="bi bi-arrow-down"></i>
                                </button>
                            </div>

                            <!-- Input Area -->
                            <div class="chat-input-area">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="userInput" placeholder="Escribe tu pregunta..." style="border-radius: 20px 0 0 20px;">
                                    <button class="btn" style="background: #0F5699; color: white; border-radius: 0 20px 20px 0;" id="sendBtn">
                                        <i class="bi bi-send-fill"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- modal de chat de mensajes -->

                <!-- fin del modal de chat de mensajes -->

                <!-- script para el modal de chat de mensajes -->


                <!-- fin script para el modal de chat de mensajes -->

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Inicializar tooltips
                        const button = document.querySelector('[title="Preguntas frecuentes"]');
                        const tooltip = new bootstrap.Tooltip(button, {
                            trigger: 'hover'
                        });

                        // Ocultar tooltip cuando se abre el modal
                        button.addEventListener('click', function() {
                            tooltip.hide();
                        });
                    });
                </script>

                <!-- script para el chatbot de preguntas frecuentes -->
                <script>
                    // Preguntas frecuentes
                    const preguntasFrecuentes = [{
                            id: 1,
                            pregunta: "¿Cómo crear un nuevo trámite?",
                            respuesta: "Para crear un nuevo trámite, dirígete al menú Trámites en la barra lateral izquierda y selecciona 'Crear Trámite'. Completa el formulario con la información requerida y haz clic en 'Guardar'."
                        },
                        {
                            id: 2,
                            pregunta: "¿Cómo consultar mis asignaciones?",
                            respuesta: "Ve al menú 'Seguimiento' > 'Mis Asignaciones' para ver todos los trámites que han sido asignados a tu usuario. Allí podrás filtrar por estado y fecha."
                        },
                        {
                            id: 3,
                            pregunta: "¿Qué hacer si un trámite está vencido?",
                            respuesta: "Si un trámite está vencido, aparecerá marcado en color rojo en tu panel. Debes priorizarlo y completar las acciones pendientes lo antes posible."
                        },
                        {
                            id: 4,
                            pregunta: "¿Cómo reportar un problema de soporte?",
                            respuesta: "Dirígete al menú 'Soporte' > 'Generar ticket'. Describe el problema, adjunta evidencia si aplica y envía la solicitud."
                        },
                        {
                            id: 5,
                            pregunta: "¿Cómo cambiar mi contraseña?",
                            respuesta: "Ve a tu perfil haciendo clic en tu foto de usuario en la esquina superior derecha, selecciona 'Perfil' y luego busca la opción 'Cambiar contraseña'."
                        }
                    ];

                    const chatContainer = document.getElementById('chatContainer');
                    const scrollBtn = document.getElementById('scrollBtn');
                    const userInput = document.getElementById('userInput');
                    const sendBtn = document.getElementById('sendBtn');

                    // Función para agregar mensaje del bot
                    function addBotMessage(content, isHTML = false) {
                        const messageDiv = document.createElement('div');
                        messageDiv.className = 'message bot-message';
                        messageDiv.innerHTML = `
                        <div class="bot-avatar">
                             <i class="bi bi-robot"></i>
                        </div>
                             <div class="bot-bubble">
                         ${isHTML ? content : `<p class="mb-0">${content}</p>`}
                         </div>
                        `;
                        chatContainer.appendChild(messageDiv);
                        scrollToBottom();
                    }

                    // Función para agregar mensaje del usuario
                    function addUserMessage(content) {
                        const messageDiv = document.createElement('div');
                        messageDiv.className = 'message user-message';
                        messageDiv.innerHTML = `
                    <div class="user-bubble">
                         <p class="mb-0">${content}</p>
                    </div>
                        `;
                        chatContainer.appendChild(messageDiv);
                        scrollToBottom();
                    }

                    // Función para mostrar indicador de escritura
                    function showTyping() {
                        const typingDiv = document.createElement('div');
                        typingDiv.className = 'message bot-message';
                        typingDiv.id = 'typing-indicator';
                        typingDiv.innerHTML = `
                    <div class="bot-avatar">
                     <i class="bi bi-robot"></i>
                    </div>
                    <div class="bot-bubble">
                        <div class="typing-indicator">
                            <div class="typing-dot"></div>
                            <div class="typing-dot"></div>
                            <div class="typing-dot"></div>
                    </div>
                    </div>
                    `;
                        chatContainer.appendChild(typingDiv);
                        scrollToBottom();
                    }

                    // Función para ocultar indicador de escritura
                    function hideTyping() {
                        const typingIndicator = document.getElementById('typing-indicator');
                        if (typingIndicator) {
                            typingIndicator.remove();
                        }
                    }

                    // Función para generar botones de preguntas
                    function generateQuestionButtons() {
                        let buttonsHTML = '<div class="d-flex flex-wrap gap-2">';
                        preguntasFrecuentes.forEach(item => {
                            buttonsHTML += `
                    <button class="question-btn" data-id="${item.id}">
                         <i class="bi bi-caret-right"></i>${item.pregunta}
                     </button>
                        `;
                        });
                        buttonsHTML += '</div>';
                        return buttonsHTML;
                    }

                    // Función para manejar clic en pregunta
                    function handleQuestionClick(questionId) {
                        const pregunta = preguntasFrecuentes.find(p => p.id === parseInt(questionId));
                        if (pregunta) {
                            addUserMessage(pregunta.pregunta);

                            showTyping();
                            setTimeout(() => {
                                hideTyping();
                                addBotMessage(pregunta.respuesta);

                                // Después de responder, solo preguntar si quiere verlas otra vez
                                setTimeout(() => {
                                    addBotMessage('¿Quieres ver de nuevo las preguntas frecuentes? (Escribe "sí" o "no")');
                                }, 500);
                            }, 1000);
                        }
                    }

                    // Adjuntar eventos a los botones de preguntas
                    function attachQuestionListeners() {
                        document.querySelectorAll('.question-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const questionId = this.getAttribute('data-id');
                                handleQuestionClick(questionId);
                            });
                        });
                    }

                    // Scroll automático
                    function scrollToBottom() {
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                    }

                    // Mostrar/ocultar botón de scroll
                    chatContainer.addEventListener('scroll', function() {
                        const isAtBottom = chatContainer.scrollHeight - chatContainer.scrollTop <= chatContainer.clientHeight + 50;
                        scrollBtn.style.display = isAtBottom ? 'none' : 'flex';
                    });

                    scrollBtn.addEventListener('click', scrollToBottom);

                    // Inicializar chat cuando se abre el modal
                    document.getElementById('chatbotModal').addEventListener('shown.bs.modal', function() {
                        if (chatContainer.children.length === 0) {
                            setTimeout(() => {
                                addBotMessage('¡Hola! 👋 Soy tu asistente virtual en la plataforma de  Conservación Neiva.');
                            }, 300);

                            setTimeout(() => {
                                addBotMessage('Estoy aquí para ayudarte y enseñarte preguntas frecuentes.');
                            }, 1000);

                            setTimeout(() => {
                                addBotMessage(generateQuestionButtons(), true);
                                attachQuestionListeners();
                            }, 1500);
                        }
                    });

                    // Manejar envío de mensajes personalizados
                    sendBtn.addEventListener('click', sendMessage);
                    userInput.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            sendMessage();
                        }
                    });

                    function sendMessage() {
                        const message = userInput.value.trim().toLowerCase();
                        if (message) {
                            addUserMessage(message);
                            userInput.value = '';

                            // Caso: si el usuario respondió que quiere ver de nuevo las preguntas
                            if (message === 'sí' || message === 'si') {
                                showTyping();
                                setTimeout(() => {
                                    hideTyping();
                                    addBotMessage(generateQuestionButtons(), true);
                                    attachQuestionListeners();
                                }, 800);
                                return;
                            }

                            // Caso: si respondió "no"
                            if (message === 'no') {
                                showTyping();
                                setTimeout(() => {
                                    hideTyping();
                                    addBotMessage('Perfecto 👍, si necesitas más ayuda, puedes escribirme lo que quieras.');
                                }, 800);
                                return;
                            }

                            // Caso general: mensaje personalizado cualquiera
                            showTyping();
                            setTimeout(() => {
                                hideTyping();
                                addBotMessage('Disculpa, aún estoy aprendiendo a responder preguntas personalizadas. Por favor, selecciona una de las preguntas frecuentes si lo deseas.');
                            }, 1000);
                        }
                    }


                    // Tooltip del botón
                    document.addEventListener('DOMContentLoaded', function() {
                        const button = document.querySelector('[title="Preguntas frecuentes"]');
                        if (button) {
                            new bootstrap.Tooltip(button, {
                                trigger: 'hover'
                            });
                        }
                    });
                </script>


                <!-- scripts para que funcionen las opciones de buscar, por ahora está solamente organizado para llegar a algunas rutas específicas -->

                <script>
                    // Base de datos de opciones
                    const database = {
                        opciones: [{
                                id: 1,
                                nombre: "Trámites asignados",
                                descripcion: "Consulta los trámites que tienes asignados",
                                icono: "bi bi-file-earmark-person",
                                ruta: "index.php?page=seguimiento/mis_asignaciones"
                            },
                            {
                                id: 2,
                                nombre: "Mis trámites en revisión",
                                descripcion: "Ver trámites que están siendo revisados",
                                icono: "bi bi-file-earmark-fill",
                                ruta: "index.php?page=tramites/revision_tramites"
                            },
                            {
                                id: 3,
                                nombre: "Mis trámites en devolución",
                                descripcion: "Consulta tus trámites que han sido devueltos",
                                icono: "bi bi-file-earmark-x-fill",
                                ruta: "index.php?page=seguimiento/mis_devoluciones"
                            },
                            {
                                id: 4,
                                nombre: "Editar perfil",
                                descripcion: "Modificar información de tu perfil",
                                icono: "bi-person-gear",
                                ruta: "index.php?page=Perfil/editar_perfil"
                            }
                        ]
                    };

                    const searchInput = document.getElementById('searchInput');
                    const searchSuggestions = document.getElementById('searchSuggestions');

                    // Función para destacar el texto buscado
                    function highlightText(text, query) {
                        if (!query) return text;
                        const regex = new RegExp(`(${query})`, 'gi');
                        return text.replace(regex, '<span class="highlight">$1</span>');
                    }

                    // Función para buscar en la base de datos
                    function search(query) {
                        if (!query || query.length < 2) {
                            searchSuggestions.classList.remove('show');
                            return;
                        }

                        query = query.toLowerCase();

                        // Buscar en opciones
                        const results = database.opciones.filter(opcion =>
                            opcion.nombre.toLowerCase().includes(query) ||
                            opcion.descripcion.toLowerCase().includes(query)
                        );

                        displayResults(results, query);
                    }

                    // Función para mostrar los resultados
                    function displayResults(results, query) {
                        let html = '';

                        if (results.length > 0) {
                            html += '<div class="suggestion-category"><i class="bi bi-menu-button-wide-fill me-2"></i>Opciones disponibles</div>';
                            results.forEach(opcion => {
                                html += `
                        <div class="suggestion-item" onclick="navigateTo('${opcion.ruta}')">
                            <div class="suggestion-icon icon-file">
                                <i class="${opcion.icono}"></i>
                            </div>
                            <div class="suggestion-content">
                                <p class="suggestion-title">${highlightText(opcion.nombre, query)}</p>
                                <p class="suggestion-description">${highlightText(opcion.descripcion, query)}</p>
                            </div>
                        </div>
                    `;
                            });
                        } else {
                            html = `
                    <div class="no-results">
                        <i class="bi bi-search" style="font-size: 2rem; color: #dee2e6;"></i>
                        <p class="mt-2">No se encontraron resultados para "${query}"</p>
                    </div>
                `;
                        }

                        searchSuggestions.innerHTML = html;
                        searchSuggestions.classList.add('show');
                    }

                    // Función para navegar (simulada)
                    function navigateTo(ruta) {
                        // alert(`Navegando a: ${ruta}`);
                        searchInput.value = '';
                        searchSuggestions.classList.remove('show');
                        window.location.href = ruta;
                        // En tu aplicación real, aquí usarías: window.location.href = ruta;
                    }

                    // Event listener para el input
                    searchInput.addEventListener('input', (e) => {
                        search(e.target.value);
                    });

                    // Cerrar sugerencias al hacer clic fuera
                    document.addEventListener('click', (e) => {
                        if (!searchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
                            searchSuggestions.classList.remove('show');
                        }
                    });

                    // Prevenir que el formulario se envíe
                    const navbarSearchForm = document.getElementById('navbarSearchForm');
                    if (navbarSearchForm) {
                        navbarSearchForm.addEventListener('submit', (e) => {
                            e.preventDefault();
                        });
                    }
                </script>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const infoRol = document.getElementById('infoRolUsuario');
                        if (!infoRol) return;

                        const rolSistema = infoRol.getAttribute('data-rol-sistema') || '';
                        const rolModulo = infoRol.getAttribute('data-rol-modulo') || '';
                        const modulo = infoRol.getAttribute('data-modulo') || 'este módulo';
                        const storageKey = 'arbimaps_rol_modulo_activo';
                        const ultimoRolGuardado = localStorage.getItem(storageKey);

                        if (rolModulo && ultimoRolGuardado !== rolModulo) {
                            const toastEl = document.getElementById('toastCambioRol');
                            const toastBody = document.getElementById('toastCambioRolBody');

                            if (toastEl && toastBody && typeof bootstrap !== 'undefined') {
                                toastBody.innerHTML =
                                    '<strong>Rol de módulo actualizado</strong><br>' +
                                    'Ahora estás trabajando como <b>' + rolModulo +
                                    '</b> en <b>' + modulo + '</b>.';

                                const toast = new bootstrap.Toast(toastEl, {
                                    delay: 3500
                                });
                                toast.show();
                            }
                        }
                        localStorage.setItem(storageKey, rolModulo || '');
                    });
                </script>



            </main>

            <!-- Footer -->
            <!-- <footer class="text-center rounded-3 text-white p-1" style="background-color: #002F55;">
                <small>Copyright &copy; Arbitrium <?php echo date("Y"); ?></small>
            </footer> -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Page level custom scripts -->
    <script src="../js/demo/datatables-demo.js"></script>

    <div class="position-fixed bottom-0 start-50 translate-middle-x p-3" style="z-index: 1080;">
        <div id="toastCambioRol" class="toast align-items-center text-bg-primary border-0 shadow"
            role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastCambioRolBody">
                    <!-- Aquí se llenará el mensaje por JS -->
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto"
                    data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cerrarsesion" tabindex="-1" aria-labelledby="cerrarsesionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" style="color: #0A2C1B;" id="cerrarsesionLabel"><strong>Cerrar sesi&oacute;n</strong></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center" style="color: #0A2C1B;">
                    &iquest;Est&aacute;s seguro que deseas <strong>cerrar tu sesi&oacute;n</strong>?<br>
                    Tendr&aacute;s que iniciar sesi&oacute;n nuevamente para seguir usando la plataforma.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal" style="color: #0A2C1B; border:1px solid #0A2C1B">Cancelar</button>
                    <form action="../logout.php" method="POST" class="m-0">
                        <?= neiva_csrf_input('logout') ?>
                        <button type="submit" class="btn text-white" style="background-color: #0A2C1B; border:1px solid #0A2C1B">Si, cerrar sesi&oacute;n</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.APP_CSRF_TOKEN = <?= json_encode(neiva_csrf_token('global')) ?>;

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form[method="post"], form[method="POST"]').forEach(function(form) {
                if (form.querySelector('input[name="csrf_token"]')) {
                    return;
                }

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'csrf_token';
                input.value = window.APP_CSRF_TOKEN;
                form.appendChild(input);
            });

            if (window.jQuery && typeof window.jQuery.ajaxSetup === 'function') {
                window.jQuery.ajaxSetup({
                    headers: {
                        'X-CSRF-Token': window.APP_CSRF_TOKEN
                    }
                });
            }
        });

        if (window.fetch) {
            const originalFetch = window.fetch.bind(window);
            window.fetch = function(resource, init) {
                const options = init || {};
                const method = String(options.method || 'GET').toUpperCase();
                const headers = new Headers(options.headers || {});

                if (!['GET', 'HEAD', 'OPTIONS'].includes(method)) {
                    headers.set('X-CSRF-Token', window.APP_CSRF_TOKEN);
                }

                return originalFetch(resource, {
                    ...options,
                    headers
                });
            };
        }
    </script>

</body>

</html>
