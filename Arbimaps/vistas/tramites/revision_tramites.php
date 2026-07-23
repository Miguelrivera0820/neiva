<?php
// require "../../conexion.php";
// session_start();
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
            $_SESSION['id_usuario']         = $row['id_usuario'];
            $_SESSION['usuario_cons']       = $row['usuario_cons'];
            $_SESSION['nombre_usuario']     = $row['nombre_usuario'];
            $_SESSION['apellido_usuario']   = $row['apellido_usuario'];
            $_SESSION['rol_usuario']        = $row['rol_usuario'];
            $_SESSION['cedula_usuario']     = $row['cedula_usuario'];
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
    tr.cod_tramite, 
    tr.fecha_rad, 
    tr.mutacion_tramite, 
    his.asignacion_rol_usuario,
    his.asignacion_nombre_usuario,
    his.asignacion_apellido_usuario,
    his.creacion_tram_nombre_usuario,
    his.creacion_tram_apellido_usuario,
    his.creacion_tram_rol_usuario,
    his.historial_estado_tramite,
    his.est_ventanilla,
    his.fecha_ventanilla,
    his.est_procedencia,
    his.fecha_procedencia,
    his.est_atencion_procedencia,
    his.fecha_ate_procedencia,
    his.est_conservacion,
    his.fecha_conservacion,
    his.est_lider_juridico,
    his.fecha_lid_juridico,
    his.est_control_calidad,
    his.fecha_cont_calidad,
    his.est_lider_economico,
    his.fecha_lid_economico,
    his.est_consolidacion,
    his.fecha_consolidacion,
    his.est_edicion,
    his.fecha_edicion,
    his.est_avaluos,
    his.fecha_avaluos,
    his.est_reconocimiento,
    his.fecha_reconocimiento,
    his.est_director,
    his.fecha_director,
    his.fecha_limite,
    (
        SELECT a.historial_fecha_tramite
        FROM entrega_asignacion a
        WHERE a.entrega_cod_tramite = his.historial_cod_tramite
        ORDER BY a.historial_fecha_tramite DESC
        LIMIT 1
    ) AS ultima_asignacion_fecha
FROM historial_revision his 
LEFT JOIN tramite_radicacion tr 
    ON his.historial_cod_tramite = tr.cod_tramite
LEFT JOIN estados_tramite est
    ON his.historial_estado_tramite = est.id
WHERE NOT EXISTS (
    SELECT 1 FROM tramites_cancelados tc
    WHERE tc.cod_tramite = his.historial_cod_tramite
      AND tc.estado = 'CANCELADO'
)
ORDER BY tr.fecha_rad DESC";
$resultado = $mysqli->query($sql2);
$tramites = [];
if ($resultado) {
    while ($row = $resultado->fetch_assoc()) {

        $tramites[] = $row;
    }
}
?>
<style>
    .estado-filtro {
        display: flex;
        gap: 10px;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }

    .estado-item {
        display: flex;
        align-items: center;
        gap: 6px;
        color: black;
        cursor: pointer;

    }

    .estado-item:hover {
        background-color: #002f551e;

        outline: 2px solid #002F55;
    }

    .estado-bola {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
    }

    .limpiar:hover {
        background-color: #002F55;
        color: white;
    }

    /* Número activo en la paginación */
    .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
        background-color: #002F55 !important;
        /* Rojo */
        border-color: #002F55 !important;
        color: #fff !important;
    }

    /* Hover sobre números */
    .dataTables_wrapper .dataTables_paginate .page-link:hover {
        background-color: #457b9d !important;
        /* Azul oscuro */
        color: #fff !important;
    }

    /* Texto de los links de paginación */
    .dataTables_wrapper .dataTables_paginate .page-link {
        color: #002f55 !important;
        /* Azul Bootstrap */
        border-radius: 8px;
        /* Bordes más redondeados */
        margin: 0 2px;
    }

    .revision-tramites-page {
        background-color: #EDEDED;
        color: #0A2C1B;
        min-height: 100%;
    }

    .revision-tramites-filter-toggle {
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%);
        border: 1px solid #0A2C1B !important;
        border-radius: 20px;
        box-shadow: 0 10px 22px rgba(10, 44, 27, 0.14);
        color: #ffffff;
        font-weight: 700;
        min-height: 42px;
        transition: transform 0.22s ease, box-shadow 0.22s ease;
    }

    .revision-tramites-filter-toggle:hover,
    .revision-tramites-filter-toggle:focus {
        color: #ffffff;
        box-shadow: 0 14px 26px rgba(10, 44, 27, 0.2);
        transform: translateY(-1px);
    }

    .revision-tramites-filter-toggle i {
        display: inline-block;
        transition: transform 0.22s ease;
    }

    .revision-tramites-filter-toggle[aria-expanded="true"] i {
        transform: rotate(-12deg);
    }

    .revision-filtro-activo-badge {
        background-color: #AEE136;
        border-radius: 999px;
        color: #0A2C1B;
        font-size: 0.72rem;
        font-weight: 800;
        line-height: 1;
        padding: 0.38rem 0.55rem;
    }

    .revision-tramites-filter-panel {
        max-height: 0;
        opacity: 0;
        overflow: hidden;
        transform: translateY(-8px);
        transition: max-height 0.32s ease, opacity 0.24s ease, transform 0.32s ease;
    }

    .revision-tramites-filter-panel.is-open {
        max-height: 360px;
        opacity: 1;
        transform: translateY(0);
    }

    .revision-tramites-filter-panel[hidden] {
        display: none !important;
    }

    .revision-tramites-filter-card {
        background-image: url(assets/img/logobnb.png), radial-gradient(circle, rgba(10, 44, 27, 1) 0%, rgba(15, 61, 38, 1) 100%);
        background-repeat: no-repeat, no-repeat;
        background-size: 9rem, auto;
        background-position: 3% 40%, center;
        border: 1px solid rgba(192, 210, 200, 0.78) !important;
        border-radius: 18px !important;
        box-shadow: 0 14px 30px rgba(10, 44, 27, 0.08) !important;
        margin: 0 auto 1.25rem auto !important;
        max-width: 960px;
        padding: 1.9rem 1.15rem !important;
        width: 100% !important;
    }

    .revision-tramites-filter-card h5 {
        color: #ffffff;
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 0.8rem;
    }

    .revision-tramites-page .estado-filtro {
        flex-wrap: wrap;
        gap: 0.6rem;
    }

    .revision-tramites-page .estado-item {
        background-color: #F6F8F7;
        border: 1px solid #C0D2C8 !important;
        border-radius: 10px;
        color: #0A2C1B;
        font-size: 0.82rem;
        font-weight: 700;
        padding: 0.5rem 0.75rem;
        transition: transform 0.22s ease, box-shadow 0.22s ease, background-color 0.22s ease;
    }

    .revision-tramites-page .estado-item:hover {
        background-color: #ffffff;
        box-shadow: 0 10px 20px rgba(10, 44, 27, 0.12);
        color: #0A2C1B;
        outline: none;
        transform: translateY(-1px);
    }

    .revision-tramites-page .estado-item.is-active {
        background: #0A5F5E;
        border-color: #ffffff !important;
        box-shadow: 0 10px 22px rgba(10, 44, 27, 0.18);
        color: #ffffff;
        transform: translateY(-1px);
    }

    .revision-tramites-page .estado-item.is-active .estado-bola {
        border: 2px solid #ffffff;
        box-shadow: 0 0 0 2px rgba(174, 225, 54, 0.35);
    }

    .revision-tramites-page #limpiarFiltros {
        border: 1px solid #0A2C1B !important;
        background-color: #AEE136;
        border-radius: 10px;
        color: #0A2C1B;
        min-height: 38px;
        padding: 0.45rem 0.8rem;
        transition: transform 0.25s ease, color 0.25s ease;
    }

    .revision-tramites-page #limpiarFiltros:hover {
        transform: scale(1.03);
    }

    .revision-tramites-page #limpiarFiltros i {
        display: inline-block;
        transition: transform 0.25s ease, color 0.25s ease;
    }

    .revision-tramites-page #limpiarFiltros:hover i {
        transform: rotate(180deg);
    }

    .revision-tramites-card {
        background: #ffffff;
        border: 1px solid rgba(192, 210, 200, 0.78);
        border-radius: 18px !important;
        box-shadow: 0 14px 30px rgba(10, 44, 27, 0.08) !important;
        overflow: hidden;
    }

    .revision-tramites-card .card-header {
        background: radial-gradient(circle, rgba(10, 44, 27, 1) 60%, rgba(15, 61, 38, 1) 97%) !important;
        border: 0;
        color: #ffffff !important;
        padding: 0.95rem 1.15rem !important;
    }

    .revision-tramites-card .card-header h6 {
        font-size: 0.85rem;
        font-weight: 600;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .revision-tramites-page #dataTable {
        border-collapse: separate !important;
        border-spacing: 0;
        color: #0A2C1B;
    }

    .revision-tramites-page #dataTable thead th {
        background-color: #EDEDED !important;
        border: 0 !important;
        color: #0A2C1B !important;
        font-size: 0.72rem;
        font-weight: 800;
        padding: 0.85rem 0.65rem;
        text-transform: uppercase;
        vertical-align: middle;
    }

    .revision-tramites-page #dataTable tbody td {
        border: 0 !important;
        border-bottom: 1px solid #E3E8E5 !important;
        font-size: 0.83rem;
        padding: 0.78rem 0.65rem;
        vertical-align: middle;
    }

    .revision-tramites-page #dataTable tbody tr:hover td {
        background-color: #F6F8F7;
        color: #0A2C1B !important;
    }

    .revision-tramites-page #dataTable a {
        color: #0A5F5E;
        font-weight: 800;
        text-decoration: none;
    }

    .revision-tramites-page .dataTables_wrapper .dataTables_filter {
        text-align: right !important;
        margin-bottom: 1rem;
    }

    .revision-tramites-page .dataTables_wrapper .dataTables_filter input,
    .revision-tramites-page .dataTables_wrapper .dataTables_length select {
        border-radius: 14px !important;
    }

    .revision-tramites-page .dataTables_wrapper .dataTables_info {
        color: #7F8E85;
        font-size: 0.78rem;
        padding-top: 1rem;
    }

    .revision-tramites-page .dataTables_wrapper .dataTables_paginate .pagination {
        align-items: center;
        gap: 0.25rem;
    }

    .revision-tramites-page .dataTables_wrapper div.dataTables_paginate ul.pagination {
        background-color: transparent;
    }

    .revision-tramites-page .dataTables_wrapper .dataTables_paginate .page-link {
        align-items: center;
        background-color: #F6F8F7 !important;
        border: 1px solid transparent !important;
        border-radius: 18px !important;
        color: #0A2C1B !important;
        display: flex;
        font-size: 0.8rem;
        height: 31px;
        justify-content: center;
        min-width: 31px;
    }

    .revision-tramites-page .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
        background-color: #0A2C1B !important;
        border-color: #0A2C1B !important;
        color: #fff !important;
    }

    .revision-tramites-page .dataTables_wrapper .dataTables_paginate .page-link:hover {
        background-color: #C0D2C8 !important;
        border-color: #C0D2C8 !important;
        color: #0A2C1B !important;
    }

    .revision-icono-filtro {
        position: absolute;
        right: 2.5rem;
        top: 1.8rem;
        background-color: #ffffff;
        border-radius: 120px;
        color: #0A2C1B;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem;
    }
</style>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<div class="container-fluid rounded-4 p-3 revision-tramites-page">
    <div class="d-sm-flex align-items-center justify-content-between mb-4 px-1 my-4">
        <div class="d-flex flex-column align-items-center gap-1 my-4 ps-2">
            <h2 class="mb-0 w-100 text-start" style="color: #0A2C1B; font-weight: 700 !important">TRAMITES CATASTRALES</h2>
            <small class="text-start w-100" style="color: #7F8E85;">Consulta la información de los trámites en revisión.</small>
        </div>

        <div class="d-flex justify-content-end mb-3">
            <button type="button" id="toggleFiltrosRevision" class="btn revision-tramites-filter-toggle d-inline-flex align-items-center gap-2 p-3 px-4" aria-expanded="false" aria-controls="panelFiltrosRevision">
                <i class="bi bi-funnel-fill"></i>
                <span id="toggleFiltrosRevisionTexto">Mostrar filtros</span>
                <span id="revisionEstadoFiltroBadge" class="revision-filtro-activo-badge d-none"></span>
            </button>
        </div>
    </div>

    <div id="panelFiltrosRevision" class="revision-tramites-filter-panel" hidden>
        <div class="revision-tramites-filter-card card-filtros" style="position: relative;">
            <div class="d-flex align-items-center flex-column py-3">
                <h5 class="text-center mb-0">Filtrar por estado del trámite</h5>
                <small style="color:#7f8e85;">Selecciona un botón para filtrar por estado del trámite</small>
            </div>

            <i class="bi bi-funnel-fill revision-icono-filtro"></i>

            <div class="estado-filtro py-2 ">
                <button class="estado-item btn border" data-estado="a_tiempo">
                    <span class="estado-bola" style="background-color: #28a745;"></span> A TIEMPO
                </button>
                <button class="estado-item btn border" data-estado="a_vencer">
                    <span class="estado-bola" style="background-color: #ffc107;"></span> A VENCER
                </button>
                <button class="estado-item btn border" data-estado="vencido">
                    <span class="estado-bola" style="background-color: #dc3545;"></span> VENCIDO
                </button>
                <button class="estado-item btn border" data-estado="caducado">
                    <span class="estado-bola" style="background-color: #6c757d;"></span> CADUCADO
                </button>
                <button id="limpiarFiltros" class="btn btn-sm fw-bold limpiar">
                    <i class="bi bi-filter-right me-2"></i> LIMPIAR FILTROS
                </button>
            </div>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-lg-12">
            <div class="card revision-tramites-card mb-4">
                <div
                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between" style="background-color:#002F55">
                    <h6 class="m-0 py-2">Información de trámites en Revisión</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle text-white" href="#" role="button" id="dropdownMenuLink"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th style="text-align: center; vertical-align: middle;">Fecha Radicación</th>
                                    <th style="text-align: center; vertical-align: middle;">Cod. Trámite</th>
                                    <th style="text-align: center; vertical-align: middle;">Tipo Trámite</th>
                                    <th style="text-align: center; vertical-align: middle;">Rol</th>
                                    <th style="text-align: center; vertical-align: middle;">Responsable</th>
                                    <th style="text-align: center; vertical-align: middle;">Fecha Asignación</th>
                                    <th style="text-align: center; vertical-align: middle;">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tramites as $tramite): ?>
                                    <?php
                                    $fecha_limite = strtotime($tramite['fecha_limite']);
                                    $hoy = strtotime(date('Y-m-d'));
                                    $diferencia_dias = floor(($fecha_limite - $hoy) / (60 * 60 * 24));

                                    if ($diferencia_dias >= 3) {
                                        $estado = "A TIEMPO";
                                        $color = "green"; // Verde
                                    } elseif ($diferencia_dias >= 1 && $diferencia_dias <= 2) {
                                        $estado = "A VENCER";
                                        $color = "orange"; // Naranja
                                    } elseif ($diferencia_dias < 0 && abs($diferencia_dias) <= 10) {
                                        $estado = "VENCIDO";
                                        $color = "red"; // Rojo
                                    } elseif (abs($diferencia_dias) > 10) {
                                        $estado = "CADUCADO";
                                        $color = "gray"; // Gris
                                    } else {
                                        $estado = "HOY";
                                        $color = "blue"; // Azul
                                    }

                                    $estado_attr = strtolower(str_replace(' ', '_', $estado));
                                    ?>
                                    <tr data-estado="<?= $estado_attr ?>">
                                        <td style="text-align: center; vertical-align: middle;"><?= date("Y-m-d H:i", strtotime($tramite['fecha_rad'])); ?></td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <a href="../ver_tramite_rad.php?cod=<?= urlencode($tramite['cod_tramite']); ?>">
                                                <?= htmlspecialchars($tramite['cod_tramite']); ?>
                                            </a>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;"><?= htmlspecialchars($tramite['mutacion_tramite']); ?></td>
                                        <td style="text-align: center; vertical-align: middle;"><?= htmlspecialchars($tramite['asignacion_rol_usuario']); ?></td>
                                        <td style="text-align: center; vertical-align: middle;"><?= htmlspecialchars($tramite['asignacion_nombre_usuario'] . '-' . $tramite['asignacion_apellido_usuario']); ?></td>
                                        <td style="text-align: center; vertical-align: middle;"><?= htmlspecialchars($tramite['ultima_asignacion_fecha']); ?></td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <span style="color: <?= $color ?>; font-weight: bold;"><?= htmlspecialchars($estado) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    const botonesFiltroRevision = document.querySelectorAll('.estado-item');
    const panelFiltrosRevision = document.getElementById('panelFiltrosRevision');
    const toggleFiltrosRevision = document.getElementById('toggleFiltrosRevision');
    const toggleFiltrosRevisionTexto = document.getElementById('toggleFiltrosRevisionTexto');
    const revisionEstadoFiltroBadge = document.getElementById('revisionEstadoFiltroBadge');

    toggleFiltrosRevision?.addEventListener('click', function() {
        const panelAbierto = panelFiltrosRevision.classList.contains('is-open');

        this.setAttribute('aria-expanded', String(!panelAbierto));
        toggleFiltrosRevisionTexto.textContent = panelAbierto ? 'Mostrar filtros' : 'Ocultar filtros';

        if (panelAbierto) {
            panelFiltrosRevision.classList.remove('is-open');
            panelFiltrosRevision.addEventListener('transitionend', function ocultarPanel(event) {
                if (event.propertyName !== 'max-height') {
                    return;
                }

                panelFiltrosRevision.hidden = true;
                panelFiltrosRevision.removeEventListener('transitionend', ocultarPanel);
            });
            return;
        }

        panelFiltrosRevision.hidden = false;
        requestAnimationFrame(() => {
            panelFiltrosRevision.classList.add('is-open');
        });
    });

    botonesFiltroRevision.forEach(item => {
        item.setAttribute('aria-pressed', 'false');

        item.addEventListener('click', function() {
            const estadoSeleccionado = this.getAttribute('data-estado');
            const filas = document.querySelectorAll('#dataTable tbody tr');

            botonesFiltroRevision.forEach(boton => {
                boton.classList.remove('is-active');
                boton.setAttribute('aria-pressed', 'false');
            });
            this.classList.add('is-active');
            this.setAttribute('aria-pressed', 'true');
            revisionEstadoFiltroBadge.textContent = this.textContent.trim();
            revisionEstadoFiltroBadge.classList.remove('d-none');

            filas.forEach(fila => {
                if (estadoSeleccionado === 'todos' || fila.getAttribute('data-estado') === estadoSeleccionado) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        });
    });

    document.getElementById('limpiarFiltros')?.addEventListener('click', function() {
        const filas = document.querySelectorAll('#dataTable tbody tr');

        botonesFiltroRevision.forEach(boton => {
            boton.classList.remove('is-active');
            boton.setAttribute('aria-pressed', 'false');
        });
        revisionEstadoFiltroBadge.textContent = '';
        revisionEstadoFiltroBadge.classList.add('d-none');

        filas.forEach(fila => {
            fila.style.display = '';
        });
    });
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    function idiomaDataTableEspanol() {
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
    }

    $(document).ready(function() {
        $('#dataTable').DataTable({
            order: [[0, 'desc']],
            language: idiomaDataTableEspanol()
        });
    });
</script>

