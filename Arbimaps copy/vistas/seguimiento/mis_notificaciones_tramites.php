<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Asegurar conexión a base de datos
if (!isset($mysqli)) {
    $conexion_path = __DIR__ . "/../../conexion.php";
    if (file_exists($conexion_path)) {
        require_once $conexion_path;
    } else {
        $conexion_alt = "conexion.php";
        if (file_exists($conexion_alt)) {
            require_once $conexion_alt;
        } else {
            die("Error: No se pudo cargar la conexión a la base de datos.");
        }
    }
}

// Validar inicio de sesión
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

$idUsuario = (int) $_SESSION['id_usuario'];
$cedula_usuario = $_SESSION['cedula_usuario'] ?? '';
$rol_usuario = $_SESSION['rol_usuario'] ?? '';

// Array para colectar alertas
$alertas_tramites = [];

// 1. Obtener movimientos de transiciones (Asignaciones y Revisiones)
if (!empty($cedula_usuario)) {
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
        $stmt_alertas->bind_param("s", $cedula_usuario);
        if ($stmt_alertas->execute()) {
            $result_alertas = $stmt_alertas->get_result();
            while ($row_alerta = $result_alertas->fetch_assoc()) {
                $remitente = trim(($row_alerta['remitente_nombre'] ?? '') . ' ' . ($row_alerta['remitente_apellido'] ?? ''));
                $rol_remitente = str_replace('_', ' ', $row_alerta['remitente_rol'] ?? '');
                
                $mensaje = "El trámite catastral fue asignado a tu bandeja por " . $remitente . " (" . ucwords($rol_remitente) . ").";
                $tipo_badge = $row_alerta['tipo_alerta'] === 'Asignación' ? 'bg-primary' : 'bg-info';
                
                $alertas_tramites[] = [
                    'cod_tramite' => $row_alerta['cod_tramite'],
                    'tipo' => $row_alerta['tipo_alerta'],
                    'mensaje' => $mensaje,
                    'remitente' => $remitente,
                    'rol_remitente' => $rol_remitente,
                    'fecha' => $row_alerta['fecha'],
                    'fecha_limite' => $row_alerta['fecha_limite'] ?? null,
                    'url' => 'index.php?page=tramites/acciones/ver_tramite_rad&cod=' . urlencode($row_alerta['cod_tramite']),
                    'badge_class' => $tipo_badge,
                    'icon' => $row_alerta['tipo_alerta'] === 'Asignación' ? 'bi-person-plus-fill' : 'bi-file-earmark-check-fill'
                ];
            }
        }
        $stmt_alertas->close();
    }
}

// 2. Obtener trámites nuevos no asignados (solo para ventanilla_catastral o administrador)
if ($rol_usuario === 'ventanilla_catastral' || $rol_usuario === 'administrador') {
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
            $mensaje = "Nuevo trámite catastral radicado y pendiente de asignación inicial.";
            $alertas_tramites[] = [
                'cod_tramite' => $row_nuevo['cod_tramite'],
                'tipo' => 'Nuevo Trámite',
                'mensaje' => $mensaje,
                'remitente' => 'Ventanilla Radicación',
                'rol_remitente' => 'ventanilla_catastral',
                'fecha' => $row_nuevo['fecha'],
                'fecha_limite' => null,
                'url' => 'index.php?page=tramites/acciones/asignar_tram_procedencia&cod=' . urlencode($row_nuevo['cod_tramite']) . '&src=tr_nuevo',
                'badge_class' => 'bg-success',
                'icon' => 'bi-file-earmark-plus-fill'
            ];
        }
    }
}

// Ordenar todas las alertas unificadas cronológicamente (más recientes primero)
usort($alertas_tramites, static function ($a, $b) {
    return strcmp($b['fecha'], $a['fecha']);
});
?>

<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 fw-bold"><i class="bi bi-bell-fill me-2 text-primary"></i>Bandeja de Alertas Catastrales</h1>
        <a href="index.php?page=dashboardcopy" class="btn btn-outline-secondary btn-sm shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Volver al Inicio
        </a>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="card shadow-sm border-0 mb-4 rounded-3">
        <div class="card-body p-3">
            <div class="row align-items-center g-3">
                <!-- Filtros por Categoría -->
                <div class="col-md-7 d-flex flex-wrap gap-2">
                    <button class="btn btn-primary btn-sm rounded-pill px-3 filter-btn" data-filter="all">
                        Todas <span class="badge bg-white text-primary ms-1"><?php echo count($alertas_tramites); ?></span>
                    </button>
                    <button class="btn btn-outline-primary btn-sm rounded-pill px-3 filter-btn" data-filter="Asignación">
                        Asignaciones
                    </button>
                    <button class="btn btn-outline-info btn-sm rounded-pill px-3 filter-btn" data-filter="Revisión">
                        Revisiones
                    </button>
                    <?php if ($rol_usuario === 'ventanilla_catastral' || $rol_usuario === 'administrador'): ?>
                        <button class="btn btn-outline-success btn-sm rounded-pill px-3 filter-btn" data-filter="Nuevo Trámite">
                            Nuevos Trámites
                        </button>
                    <?php endif; ?>
                </div>
                <!-- Buscador rápido -->
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" id="searchInput" class="form-control bg-light border-start-0" placeholder="Buscar por código de trámite o remitente...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Alertas -->
    <div class="row" id="alertsContainer">
        <?php if (empty($alertas_tramites)): ?>
            <div class="col-12 text-center py-5 text-muted">
                <div class="card shadow-sm border-0 rounded-3 py-5">
                    <div class="card-body">
                        <i class="bi bi-bell-slash text-secondary opacity-50" style="font-size: 4rem;"></i>
                        <h4 class="mt-4 fw-semibold">No tienes alertas pendientes</h4>
                        <p class="text-muted mb-0">¡Todo al día! No hay transiciones catastrales dirigidas a tu usuario en este momento.</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($alertas_tramites as $a): ?>
                <div class="col-12 mb-3 alert-card-wrapper" data-category="<?php echo htmlspecialchars($a['tipo']); ?>">
                    <div class="card shadow-sm border-0 border-start border-4 rounded-3 position-relative" style="border-left-color: <?php 
                        echo match($a['tipo']) {
                            'Asignación' => '#4e73df',
                            'Revisión' => '#36b9cc',
                            'Nuevo Trámite' => '#1cc88a',
                            default => '#858796'
                        };
                    ?> !important; background-color: #fff; transition: transform 0.2s, box-shadow 0.2s;">
                        
                        <div class="card-body p-3">
                            <div class="row align-items-center g-3">
                                <!-- Ícono de Alerta -->
                                <div class="col-auto">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white <?php echo $a['badge_class']; ?>" style="width: 48px; height: 48px; font-size: 1.25rem;">
                                        <i class="bi <?php echo $a['icon']; ?>"></i>
                                    </div>
                                </div>
                                
                                <!-- Contenido de la Alerta -->
                                <div class="col">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-1">
                                        <h6 class="fw-bold mb-0 text-dark alert-tramite-code" style="font-size: 1.05rem;">
                                            Trámite: <?php echo htmlspecialchars($a['cod_tramite']); ?>
                                        </h6>
                                        <span class="badge bg-light text-secondary border px-2 py-1 rounded" style="font-size: 0.75rem;">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            <?php echo date('d/m/Y h:i A', strtotime($a['fecha'])); ?>
                                        </span>
                                    </div>
                                    <p class="text-muted mb-0 alert-message-text" style="font-size: 0.92rem;">
                                        <?php echo htmlspecialchars($a['mensaje']); ?>
                                    </p>
                                    
                                    <div class="d-flex flex-wrap gap-3 mt-2" style="font-size: 0.82rem;">
                                        <span class="text-secondary"><i class="bi bi-person-fill me-1"></i> Remite: <strong class="alert-remitente-name"><?php echo htmlspecialchars($a['remitente']); ?></strong></span>
                                        <?php if ($a['fecha_limite']): ?>
                                            <span class="text-danger fw-semibold">
                                                <i class="bi bi-hourglass-split me-1"></i> Límite: <?php echo date('d/m/Y', strtotime($a['fecha_limite'])); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Botón de Acción -->
                                <div class="col-auto">
                                    <a href="<?php echo $a['url']; ?>" class="btn btn-sm btn-primary px-3 fw-bold rounded-pill shadow-sm">
                                        <i class="bi bi-box-arrow-in-right me-1"></i> Ir al Trámite
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.alert-card-wrapper:hover .card {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
}
.filter-btn {
    transition: all 0.2s ease-in-out;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const filterButtons = document.querySelectorAll(".filter-btn");
    const alertCards = document.querySelectorAll(".alert-card-wrapper");
    const searchInput = document.getElementById("searchInput");

    // Función de filtrado y búsqueda combinada
    function filterAlerts() {
        const query = searchInput.value.toLowerCase().trim();
        const activeFilter = document.querySelector(".filter-btn.btn-primary, .filter-btn.btn-success, .filter-btn.btn-info").getAttribute("data-filter");

        let visibleCount = 0;

        alertCards.forEach(card => {
            const category = card.getAttribute("data-category");
            const code = card.querySelector(".alert-tramite-code").textContent.toLowerCase();
            const message = card.querySelector(".alert-message-text").textContent.toLowerCase();
            const remitente = card.querySelector(".alert-remitente-name").textContent.toLowerCase();

            const matchesCategory = (activeFilter === "all" || category === activeFilter);
            const matchesSearch = (code.includes(query) || message.includes(query) || remitente.includes(query));

            if (matchesCategory && matchesSearch) {
                card.style.display = "block";
                visibleCount++;
            } else {
                card.style.display = "none";
            }
        });

        // Mostrar u ocultar mensaje de vacío dinámico
        let emptyMsg = document.getElementById("emptyFilteredMsg");
        if (visibleCount === 0 && alertCards.length > 0) {
            if (!emptyMsg) {
                emptyMsg = document.createElement("div");
                emptyMsg.id = "emptyFilteredMsg";
                emptyMsg.className = "col-12 text-center py-5 text-muted";
                emptyMsg.innerHTML = `
                    <div class="card shadow-sm border-0 rounded-3 py-4">
                        <div class="card-body">
                            <i class="bi bi-search text-secondary opacity-50" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 fw-semibold">No se encontraron alertas</h5>
                            <p class="text-muted mb-0">Ningún trámite coincide con los filtros aplicados.</p>
                        </div>
                    </div>
                `;
                document.getElementById("alertsContainer").appendChild(emptyMsg);
            }
        } else if (emptyMsg) {
            emptyMsg.remove();
        }
    }

    // Eventos de botones de filtrado
    filterButtons.forEach(btn => {
        btn.addEventListener("click", function() {
            // Limpiar clases primarias y outlines
            filterButtons.forEach(b => {
                const f = b.getAttribute("data-filter");
                b.className = b.className.replace("btn-primary", "btn-outline-primary")
                                         .replace("btn-success", "btn-outline-success")
                                         .replace("btn-info", "btn-outline-info");
            });

            const filter = this.getAttribute("data-filter");
            if (filter === "all") {
                this.className = this.className.replace("btn-outline-primary", "btn-primary");
            } else if (filter === "Asignación") {
                this.className = this.className.replace("btn-outline-primary", "btn-primary");
            } else if (filter === "Revisión") {
                this.className = this.className.replace("btn-outline-info", "btn-info");
            } else if (filter === "Nuevo Trámite") {
                this.className = this.className.replace("btn-outline-success", "btn-success");
            }

            filterAlerts();
        });
    });

    // Evento de búsqueda
    searchInput.addEventListener("input", filterAlerts);
});
</script>
