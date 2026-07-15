var mapa = L.map('mapa', { preferCanvas: true }).setView([4.5709, -74.2973], 5);
L.tileLayer('https://{s}.basemaps.cartocdn.com/light_nolabels/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
    subdomains: 'abcd',
    maxZoom: 8,
    minZoom: 5,
    opacity: 0.8,
    className: 'mapa-base'
}).addTo(mapa);


//Pane para organizar capas 
var PaneDepartamento = mapa.createPane('departamento');
PaneDepartamento.style.zIndex = 400; // Asegurarse de que esté por encima

var PaneMunicipio = mapa.createPane('municipio');
PaneMunicipio.style.zIndex = 300; // Asegurarse de que esté por encima

// listado  Municipios intervenidos
var MunDestacados = [
    //Mps Arauca
    'Arauca',
    //Mps Huila
    'La Plata', 'Garzón', 'Paicol', 'Neiva', 'Rivera', 'Gigante', 'Aipe', 'Baraya', 'Pitalito', 'Santa María', 'Acevedo', 
    'Tello', 'Timaná', 'Campoalegre', 'Hobo', 'Tesalia', 'Nátaga', 'Íquira', 'Yaguará', 'Agrado', 'Pital', 'La Argentina', 
    'Colombia', 'Oporapa', 'Tarqui',
    //mps Tolima
    'Ibagué', 'Melgar',
    //Mps Vichada
    'La Primavera',
    //Mps Caldas
    'Samaná', 'Marulanda', 'Pensilvania', 'La Dorada', 'Manzanares', 'Norcasia', 'Chinchiná', 'Palestina', 'Salamina.', 'La Merced', 'Marquetalia', 'Supia',
    //Mps Santander
    'Bucaramanga', 'Lebrija', 'Girón', 'Puente Nacional', 'San Vicente de Chucurí', 'Jesús María', 'La Pazz', 'Simacota', 
    'Vélez', 'Socorro', 'Floridablanca', 'Barrancabermeja', 'Cimitarra', 'Barbosa', 'Rionegroo',
    //Mps Risaralda
    'Dosquebradas', 'Pereira', 'Santa Rosa De Cabal', 'Apía',
    //Mps Meta
    'Villavicencio', 'Puerto López','Vistahermosa','Acacías',
    //Mps Antioquia
    'Medellín', 'Bello', 'San Juan De Urabá', 'San Pedro De Urabá', 'Arboletes', 'Necoclí', 'Yondó',
    //Mps Putumayo
    'Valle Del Guamuez', 'Mocoa', 'Sibundoy', 'Villagarzón',
    //mps Quindio
    'Armenia', 'Circasia',
    //Mps Nariño
    'Pasto', 'Ipiales', 'Leivaa', 'La Uniónnn', 'Buesaco', 'Majagual', 'Ovejas', 'San Marcos', 'Sincelejo',
    //Mps Sucre
    'La Uniónn', 'Corozal', 'Galeras',
    //Mps Cordoba
    'Montería', 'Pueblo Nuevo',
    //Mps Boyacá
    'Saboyá', 'Chiquinquirá', 'Guateque', 'Macanal', 'Chivor', 'Tenza', 'Sutatenza',
    //Mps Cundinamarca
    'Chocontá', 'Bogotá, D.C.', 'Machetá', 'San Antonio Del Tequendama', 'Suesca', 'Zipaquirá', 'Tibirita', 'Supatá',
    'Ubalá', 'Chipaque', 'Cajicá', 'Cogua', 'Nemocón', 'Tocancipá', 'Zipacón', 'La Mesa', 'Cachipay',
    'Tena', 'Tausa', 'Fusagasugá', 'Girardot', 'Sibaté', 'Silvania', 'Soacha', 'Sopó', 'Tibacuy', 'Granadaa',
    //Mps Bolivar
    'CórdobaB', 'El Carmen De Bolívar', 'San Martín De Loba',
    //Mps Cauca
    'El Tamboo','Patía','Sotará','La Vega','Totoró','Suárez','Buenos Aires','Popayán','Páezz','Corinto','Piendamó Tunia',
    //Mps Guajira
    'Barrancas',
    //Mps Magdalena
    'El Banco', 'Sabanas De San Ángel',
    //Mps valle del cauca
    'Ansermanuevo','San Pedroo',
    //Mps caquetá
     'San Vicente Del Caguán', 'Puerto Rico', 'Morelia','Milán','El Paujil','Cartagena Del Chairá', 'Belén De Los Andaquíes',
     //Mps Cesar
     'Pailitas', 'La Jagua De Ibirico',  'La Gloria',  'Gamarra', 'Curumaní', 'Astrea',
     //Mps Atántico
     'Sabanalarga','Repelón'

];

// variable de renderizado en canvas
var CanvasRenderMunicipios = L.canvas();
var CanvasRenderDepartamentos = L.canvas()


//Fubción para definir estilos de la capa Geojson de departamentos intervenidos:
function styles(feature) {
    return {
        color: '#020e189d',
        fillColor: '#002f55e9',
        weight: 1,
        opacity: 1,
        fillOpacity: 0.4,
        dashArray: '2'
    };
}



fetch('./data/DepartamentosOp.geojson')
    .then(response => response.json())
    .then(data => {
        // console.log(data);
        L.geoJSON(data, {
            renderer: CanvasRenderDepartamentos, // Usar el renderer de canvas
            pane: 'departamento', // Asignar al pane de departamentos
            style: {
                color: '#002f559d',
                weight: 1,
                opacity: 0.8,
                fillOpacity: 0
            },
        })


            // .bindPopup(function (layer) {
            //     return "Departamento: " + layer.feature.properties.DeNombre;
            // })

            .addTo(mapa);

    })
    .catch(error => {
        console.error('Error cargando el archivo GeoJSON:', error);
    });

// ---------------------------------------------------------------------------------------------------------
//get para los departamentos con intervención

fetch('./data/Dep.geojson')
    .then(response => response.json())
    .then(data => {
        // console.log(data);


        data.features.forEach(feature => {
            var municipiosIntervenidos = feature.properties.MunicipiosInter || []; // Acceder a MunicipiosInter
            // console.log(`Municipios Intervenidos en ${feature.properties.DeNombre}:`, municipiosIntervenidos);
        });

        var geojson;

        // Función para resaltar el feature al pasar el mouse 
        function Resaltar(e) {
            var layer = e.target;
            // console.log("se accedió al arrya" + layer)

            layer.setStyle({
                weight: 3,
                color: '#002F55',
                dashArray: '',
                fillOpacity: 0.7
            });

            // layer.bringToFront();
            infoControl.update(layer.feature.properties); // Actualizar el control de información
            // Mostrar popup al pasar el mouse
        }

        // Función para restaurar el estilo original al quitar el mouse 
        function ResetResaltar(e) {
            geojson.resetStyle(e.target);
            infoControl.update(); // Limpiar el control de información
        };

        //Función para hacer un pequeño zoom al dar click en el departamento
        function ClickZoom(e) {
            mapa.fitBounds(e.target.getBounds());
        }

        function onEachFeature(feature, layer) {
            layer.on({
                mouseover: Resaltar,
                mouseout: ResetResaltar,
                click: ClickZoom
            });
        }

        geojson = L.geoJSON(data, {
            style: styles,
            onEachFeature: onEachFeature,
            pane: 'departamento', // Asignar al pane de departamentos
        })
            //  .bindPopup(function (layer) {
            //      return "Departamento: " + layer.feature.properties.DeNombre + "<br>" +
            //          "Municipios Intervenidos: " + layer.feature.properties.MunicipiosInter.join(', ');
            //  })

            .addTo(mapa);

    })
    .catch(error => {
        console.error('Error cargando el archivo GeoJSON:', error);
    });



// ----------------------------------------------------------
// Añadir la leyenda de información de intervención al mapa.
// -----------------------------------------------------------


var legend = L.control({ position: 'bottomleft' });

legend.onAdd = function (map) {
    var div = L.DomUtil.create('div', 'info legend');

    // HTML de la leyenda
    div.innerHTML = `
    <section class="legend-section">
    <h4>Área Intervenida</h4>
    <section/>
    <div class="legend-item">
    <i class="bi bi-grid-3x3 me-2 fs-6" style="color: #002f55ff;"></i>
            <span class="legend-text">240.000 Predios</span>
        </div>
        <div class="legend-item">
            <i class="bi bi-globe-americas me-2 fs-6" style="color: #002f55ff;"></i>
            <span class="legend-text">2.200.000 Has</span>
        </div>
        <div class="legend-item">
            <span class="legend-color" style="background-color: #002f559d; opacity: 1; border: 1px solid #000;"></span>
            <span class="legend-text">24 Departamentos</span>
        </div>
        <div class="legend-item">
            <span class="legend-color" style="background-color: #FFC107; border: 1px solid #000;"></span>
            <span class="legend-text">138 Municipios</span>
        </div>
    `;

    return div;
};

legend.addTo(mapa);



//------------------------------------------------------------------------------------------------
// Control interactivo que va mostrando los municipios intervenidos en cada departamento
//------------------------------------------------------------------------------------------------

var infoControl = L.control({ position: 'topright' });

infoControl.onAdd = function (map) {
    this._div = L.DomUtil.create('div', 'info-control');
    this._div.zIndex = '1000';
    this.update();
    return this._div;
};

// ----------------------------------------------------------
// función para controlar la información que se muestra en el infoControl
// ----------------------------------------------------------

infoControl.update = function (props) {
    if (props && props.MunicipiosInter && props.MunicipiosInter.length > 0) {
        // Crear lista HTML con los municipios
        var listaMunicipios = props.MunicipiosInter
            .sort() // Ordenar alfabéticamente (puedes cambiar por .sort().reverse() para orden descendente)
            .map(municipio => '<li class="info-control-Listitem">' + municipio + '</li>')
            .join('');

        this._div.innerHTML = '<h4 class="info-control-title">Municipios intervenidos</h4>' +
            '<b class="info-control-departamento">Dpt: ' + props.DeNombre + '</b><br />' +
            '<ul style="margin: 8px 0; list-style: none; padding-left: 0;text-align: center;">' + listaMunicipios + '</ul>';
    } else {
        this._div.innerHTML = '<h4 class="info-control-title">Municipios intervenidos</h4>' +
            ' <p class="info-control-Listitem">Pasa el mouse sobre un departamento</p>';
    }
};

infoControl.addTo(mapa);

// ---------------------------------------------------------------
// función para el botón de home para regresar al zoom inicial
// ---------------------------------------------------------------

var HomeButton = L.control({ position: 'topleft' });

L.control.scale({
    position: 'bottomright',
    maxWidth: 150,
    metric: true,
    imperial: false
}).addTo(mapa);

HomeButton.onAdd = function (map) {
    var button = L.DomUtil.create('button', 'btn btn-light border shadow btn-sm');
    button.innerHTML = '<i class="bi bi-house-door-fill"></i>';
    button.title = 'Regresar al zoom inicial';
    button.onclick = function () {
        mapa.setView([4.5709, -74.2973], 5); // Coordenadas y zoom inicial
    };
    return button;
};

HomeButton.addTo(mapa);

// ------------------------------------------------------
//Botón para añadir capa de municipios intervenidos --> Permite mostrar la capa de municipios destacados
//-------------------------------------------------------


// Variable global para guardar la capa de municipios
var capaMunicipios = null;
var municipiosActivos = false;
var municipiosFiltrados = null;

// Realizar el fetch al cargar la página
document.addEventListener('DOMContentLoaded', function () {
    fetch('./data/Municipios.geojson')
        .then(response => response.json())
        .then(dato => {
            // console.log('Capa de municipios cargada previamente.');

            // Filtrar solo los municipios destacados
            municipiosFiltrados = {
                type: "FeatureCollection",
                features: dato.features.filter(feature => {
                    var nombreMun = feature.properties.MpNombre;
                    return MunDestacados.includes(nombreMun);
                })
            };

            // console.log(`Municipios filtrados: ${municipiosFiltrados.features.length} de ${dato.features.length}`);

            // Identificar los municipios faltantes
            const municipiosGeoJSON = dato.features.map(feature => feature.properties.MpNombre);
            const municipiosFaltantes = MunDestacados.filter(municipio => !municipiosGeoJSON.includes(municipio));

            // console.log(`Municipios faltantes: ${municipiosFaltantes.length}`);
            // console.log(municipiosFaltantes);
        })
        .catch(error => {
            console.error('Error cargando el archivo GeoJSON:', error);
        });
});


var MunicipalButton = L.control({ position: 'topleft' });

MunicipalButton.onAdd = function (map) {
    var button = L.DomUtil.create('button', 'btn btn-light border shadow btn-sm');
    button.innerHTML = '<i class="bi bi-layers-fill"></i>';
    button.title = 'Añadir capa de municipios intervenidos';

    button.onclick = function () {
        if (!municipiosActivos) {
            // Activar capa de municipios usando los datos precargados
            if (municipiosFiltrados) {
                capaMunicipios = L.geoJSON(municipiosFiltrados, {
                    renderer: CanvasRenderMunicipios, // Usar el renderer de canvas Municipios
                    pane: 'municipio', // Asignar al pane de municipios
                    style: function (feature) {
                        return {
                            color: "#000406ff",
                            fillColor: '#FFC107',
                            weight: 0.7,
                            opacity: 0.8,
                            fillOpacity: 1
                        };
                    },
                    onEachFeature: function (feature, layer) {
                        layer.bindPopup(`
                            <strong>Municipio:</strong> ${feature.properties.MpNombre}<br>
                            <strong>Departamento:</strong> ${feature.properties.Depto || 'N/A'}
                        `);
                    }
                }).addTo(mapa);

                // Cambiar estado y apariencia del botón
                municipiosActivos = true;
                button.classList.remove('btn-light');
                button.classList.add('btn-warning');
                button.title = 'Ocultar capa de municipios intervenidos';
            } else {
                console.error('Los datos de municipios aún no están cargados.');
            }
        } else {
            // Desactivar capa de municipios
            if (capaMunicipios) {
                mapa.removeLayer(capaMunicipios);
                capaMunicipios = null;
            }

            // Cambiar estado y apariencia del botón
            municipiosActivos = false;
            button.classList.remove('btn-warning');
            button.classList.add('btn-light');
            button.title = 'Añadir capa de municipios intervenidos';
        }
    };

    return button;
};

MunicipalButton.addTo(mapa);

// Función para scroll suave al inicio
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Mostrar/ocultar botón según scroll
window.addEventListener('scroll', function() {
    const homeButton = document.querySelector('.btn-home-float');
    if (homeButton) {
        if (window.scrollY > 300) { // Mostrar después de 300px de scroll
            homeButton.style.display = 'block';
            homeButton.style.animation = 'fadeInUp 0.3s ease';
        } else {
            homeButton.style.display = 'none';
        }
    }
});

document.querySelectorAll(".counter").forEach(el => {
  let counter = { val: 0 };
  gsap.to(counter, {
    val: parseInt(el.dataset.target),
    duration: 2,
    ease: "power1.out",
    scrollTrigger: {
      trigger: el,
      start: "top 99%",
      once: true
    },
    onUpdate: () => el.textContent = Math.floor(counter.val)
  });
});

// -------------------------------------------------------------------------
// Animación de incremento de número y barras de progreso hechas con Gsap
// -------------------------------------------------------------------------

document.addEventListener('DOMContentLoaded', function () {
    const progressBars = document.querySelectorAll('.progress-bar'); // Seleccionar todas las barras de progreso

    // Función para animar la barra de progreso
    const animateProgressBar = (progressBar) => {
        const target = +progressBar.getAttribute('data-target'); // Obtener el valor final
        gsap.to(progressBar, {
            width: `${target}%`, // Animar el ancho de la barra
            duration: 1.5, // Duración de la animación en segundos
            ease: "power1.out", // Tipo de easing para la animación
            onUpdate: function () {
                const currentWidth = parseFloat(progressBar.style.width);
                progressBar.setAttribute('aria-valuenow', Math.ceil(currentWidth)); // Actualizar el atributo aria-valuenow
            }
        });
    };

    // Configuración del Intersection Observer
    const observerOptions = {
        root: null, // Usar el viewport como referencia
        threshold: 0.9 // Ejecutar cuando el 90% del elemento sea visible
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const progressBar = entry.target;
                animateProgressBar(progressBar); // Animar la barra de progreso
                observer.unobserve(progressBar); // Dejar de observar después de animar
            }
        });
    }, observerOptions);

    // Observar cada barra de progreso
    progressBars.forEach(progressBar => {
        observer.observe(progressBar);
    });
});