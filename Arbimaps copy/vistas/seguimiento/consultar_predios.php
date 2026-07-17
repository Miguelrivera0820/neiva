<?php
$fmi = isset($_REQUEST['fmi_predio']) ? trim($_REQUEST['fmi_predio']) : '';
$npn = isset($_REQUEST['npn_predio']) ? trim($_REQUEST['npn_predio']) : '';
$sinSeleccion = ($fmi === '' && $npn === '');
?>
<div class="col-xl-8 p-1" style="margin-left: 5%; width: 90%;">
    <div class="card card-especial shadow p-2 h-100 d-flex flex-column justify-content-center" style="border-radius: 12px">
        <div class="card-header py-2 text-center" style="border-radius: 12px; background-color: #002F55; color: white;">
            <h6 class="text-center m-0"><b>INFORMACIÓN DEL PREDIO</b></h6>
            <small>Busca por FMI o Código Catastral y verás la información en tablas.</small>
        </div>

        <div class="card-body px-2">
            <!-- Filtros de búsqueda -->
            <form method="get" class="mb-0">
                <div class="row g-2 justify-content-center">
                    <div class="col-md-5 position-relative">
                        <label for="fmi_predio" class="form-label fw-bold" style="font-size:0.9em;">FMI Predio</label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text"><i class="bi bi-map-fill"></i></span>
                            <input class="form-control" style="font-size:0.9em;" id="fmi_predio" name="fmi_predio" type="text"
                                placeholder="Ingrese FMI del Predio" value="<?= htmlspecialchars($fmi) ?>">
                        </div>
                    </div>

                    <div class="col-md-5 position-relative">
                        <label for="npn_predio" class="form-label fw-bold" style="font-size:0.9em;">Cod. Catastral Predio</label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text"><i class="bi bi-map"></i></span>
                            <input class="form-control" style="font-size:0.9em;" id="npn_predio" name="npn_predio" type="text"
                                placeholder="Ingrese N° Predial" autocomplete="off" value="<?= htmlspecialchars($npn) ?>">
                        </div>
                    </div>
                </div>
            </form>

            <hr class="my-3">

            <!-- Estado vacío bonito cuando no hay selección -->
            <div id="estadoVacio" class="<?= $sinSeleccion ? '' : 'd-none' ?>">
                <div class="text-center p-4">
                    <!-- Ilustración simple (SVG) -->
                    <svg width="120" height="120" viewBox="0 0 24 24" fill="none" class="mb-2">
                        <path d="M3 10.5L12 3l9 7.5v8A1.5 1.5 0 0 1 19.5 20h-15A1.5 1.5 0 0 1 3 18.5v-8Z" stroke="#002F55" stroke-width="1.3" />
                        <path d="M9 20v-6h6v6" stroke="#002F55" stroke-width="1.3" />
                    </svg>
                    <h6 class="fw-bold mb-1">Aún no has ingresado un FMI o un Código Catastral</h6>
                    <p class="text-muted mb-3" style="font-size:0.95em;">
                        Escribe al menos uno de los campos para ver el resumen del predio.
                    </p>
                </div>
            </div>

            <!-- Bloque de resultados (se oculta si no hay selección) -->
            <div id="bloqueResultados" class="<?= $sinSeleccion ? 'd-none' : '' ?>">
                <!-- Resumen del Predio -->
                <div class="row">
                    <div class="col-12">
                        <h6 class="fw-bold text-center mb-2">Resumen del Predio</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered text-secondary" id="tablaResumen">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="background-color:#002F55;font-size:0.75em" class="text-white text-center rounded-3">Campo</th>
                                        <th style="background-color:#002F55;font-size:0.75em" class="text-white text-center rounded-3">Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="text-center">
                                        <td colspan="2" class="text-muted">Sin datos</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Propietarios -->
                    <div class="col-12 mt-3">
                        <h6 class="fw-bold text-center mb-2">Propietarios del Predio</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered text-secondary" id="tablaPropietarios">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="background-color:#002F55;font-size:0.75em" class="text-white text-center rounded-3">Nombre Propietario</th>
                                        <th style="background-color:#002F55;font-size:0.75em" class="text-white text-center rounded-3">Tipo Documento</th>
                                        <th style="background-color:#002F55;font-size:0.75em" class="text-white text-center rounded-3">Número Documento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="text-center">
                                        <td colspan="3" class="text-muted">Sin datos</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Avisos -->
                    <div class="col-12 mt-2">
                        <div id="alertaResultados" class="alert alert-info py-2 d-none" role="alert" style="font-size:0.9em;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card-especial {
        min-height: 80vh;
    }

    .contenido-alto {
        flex: 1 1 auto;
        overflow: auto;
    }

    .autocomplete-items {
        max-height: 260px;
        overflow-y: auto;
        border: 1px solid rgba(0, 0, 0, .125);
        border-top: none;
        border-radius: 0 0 .375rem .375rem;
    }

    .autocomplete-item {
        padding: .5rem .75rem;
        cursor: pointer;
        background: #fff;
    }

    .autocomplete-item:hover {
        background: #f8f9fa;
    }
</style>

<script>
    let predios = [];

    // Cargar datos del JSON
    fetch('../dat_neiva/predios.json')
        .then(res => res.json())
        .then(data => {
            predios = data || [];
        })
        .catch(() => {
            predios = [];
        });

    document.addEventListener('DOMContentLoaded', function() {
        configurarBusqueda('npn_predio', 'numero_predio');
        configurarBusqueda('fmi_predio', 'matricula_inmobiliaria');

        const valFmi = document.getElementById('fmi_predio').value.trim();
        const valNpn = document.getElementById('npn_predio').value.trim();
        if (valFmi.length > 0 || valNpn.length > 0) {
            document.getElementById('estadoVacio')?.classList.add('d-none');
            document.getElementById('bloqueResultados')?.classList.remove('d-none');
            // El render final se hace cuando el usuario selecciona una coincidencia
            const campo = valFmi ? 'matricula_inmobiliaria' : 'numero_predio';
            const valor = valFmi || valNpn;
            const base = predios.find(p => String(p[campo] || '') === valor);
            if (base) {
                const relacionados = predios.filter(pr => pr.numero_predio === base.numero_predio);
                renderResumen(base);
                renderPropietarios(relacionados);
                setAlerta(`Se encontraron ${relacionados.length} registro(s) para el predio ${base.numero_predio}.`, 'success');
            }
        }
    });

    /** Renderiza la tabla de Resumen usando el primer registro base del predio */
    function renderResumen(base) {
        const cuerpo = document.querySelector('#tablaResumen tbody');
        cuerpo.innerHTML = '';

        const filas = [{
                campo: 'FMI Predio',
                valor: base.matricula_inmobiliaria || ''
            },
            {
                campo: 'Cod. Catastral',
                valor: base.numero_predio || ''
            },
            {
                campo: 'Dirección',
                valor: base.direccion_predio || ''
            },
            {
                campo: 'Destino Económico',
                valor: base.dest_econ_predio || ''
            },
            {
                campo: 'Área Terreno (m²)',
                valor: base.area_terreno_predio || ''
            },
            {
                campo: 'Área Construcción (m²)',
                valor: base.area_construccion_predio || ''
            },
            {
                campo: 'Avalúo Terreno',
                valor: base.avaluo_terreno_tramite || ''
            },
            {
                campo: 'Año de vigencia',
                valor: base.anio_vigencia || ''
            }
        ];

        let hayValor = false;
        filas.forEach(f => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
        <td class="fw-semibold" style="font-size:.9em;">${f.campo}</td>
        <td class="text-center" style="font-size:.9em;">${(f.valor ?? '').toString().trim() || '—'}</td>
      `;
            if ((f.valor ?? '').toString().trim() !== '') hayValor = true;
            cuerpo.appendChild(tr);
        });

        if (!hayValor) {
            cuerpo.innerHTML = `<tr class="text-center"><td colspan="2" class="text-muted">Sin datos</td></tr>`;
        }
    }

    /** Renderiza la tabla de Propietarios con todos los relacionados */
    function renderPropietarios(relacionados) {
        const cuerpo = document.querySelector('#tablaPropietarios tbody');
        cuerpo.innerHTML = '';

        if (!relacionados || relacionados.length === 0) {
            cuerpo.innerHTML = `<tr class="text-center"><td colspan="3" class="text-muted">Sin datos</td></tr>`;
            return;
        }

        relacionados.forEach(prop => {
            const doc = (String(prop.numero_doc_propietario || '')).replace(/^0+/, '');
            const tr = document.createElement('tr');
            tr.innerHTML = `
        <td class="text-center" style="font-size:.9em;">${prop.nombre_propietario_tramite || '—'}</td>
        <td class="text-center" style="font-size:.9em;">${prop.tipo_doc_propietario || '—'}</td>
        <td class="text-center" style="font-size:.9em;">${doc || '—'}</td>
      `;
            cuerpo.appendChild(tr);
        });
    }

    /** Muestra aviso encima de las tablas */
    function setAlerta(msg, tipo = 'info') {
        const alerta = document.getElementById('alertaResultados');
        alerta.className = `alert alert-${tipo} py-2`;
        alerta.textContent = msg;
        alerta.classList.remove('d-none');
    }

    /** Autocompletado y acción de selección */
    function configurarBusqueda(inputId, campo) {
        const input = document.getElementById(inputId);
        if (!input) return;

        // Contenedor de sugerencias
        let contenedor = document.createElement('div');
        contenedor.className = 'autocomplete-items position-absolute w-100 z-3';
        contenedor.style.top = '100%';
        contenedor.style.left = '0';
        contenedor.style.right = '0';
        contenedor.style.zIndex = '9999';
        input.parentNode.appendChild(contenedor);

        input.addEventListener('input', function() {
            const valor = this.value.trim();
            contenedor.innerHTML = '';
            if (valor.length < 3) return;

            const valNorm = (campo === 'numero_doc_propietario') ? valor.replace(/^0+/, '') : valor;

            const coincidencias = predios
                .filter(p => {
                    let cVal = String(p[campo] || '');
                    if (campo === 'numero_doc_propietario') cVal = cVal.replace(/^0+/, '');
                    return cVal.startsWith(valNorm);
                })
                .slice(0, 10);

            coincidencias.forEach(p => {
                const item = document.createElement('div');
                item.className = 'autocomplete-item';
                item.textContent = p[campo];

                item.addEventListener('click', () => {
                    input.value = p[campo];
                    contenedor.innerHTML = '';

                    // Mostrar bloque resultados y ocultar estado vacío
                    document.getElementById('estadoVacio')?.classList.add('d-none');
                    document.getElementById('bloqueResultados')?.classList.remove('d-none');

                    // Traer todos los registros con el mismo número_predio
                    const relacionados = predios.filter(pr => pr.numero_predio === p.numero_predio);

                    if (relacionados.length > 0) {
                        const base = relacionados[0];
                        renderResumen(base);
                        renderPropietarios(relacionados);
                        setAlerta(`Se encontraron ${relacionados.length} registro(s) para el predio ${base.numero_predio}.`, 'success');
                    } else {
                        // Sin relacionados: limpiar tablas
                        document.querySelector('#tablaResumen tbody').innerHTML =
                            `<tr class="text-center"><td colspan="2" class="text-muted">Sin datos</td></tr>`;
                        document.querySelector('#tablaPropietarios tbody').innerHTML =
                            `<tr class="text-center"><td colspan="3" class="text-muted">Sin datos</td></tr>`;
                        setAlerta('No se encontraron registros relacionados para la selección.', 'warning');
                    }
                });

                contenedor.appendChild(item);
            });
        });

        // Cerrar dropdown al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !contenedor.contains(e.target)) {
                contenedor.innerHTML = '';
            }
        });
    }
</script>