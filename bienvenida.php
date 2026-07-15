<?php
require_once __DIR__ . "/conexion.php";

$sql_noticias = "SELECT 
                    id, 
                    link_noticia, 
                    descripcion_noticia
                FROM noticias_arbimaps
                ORDER BY id DESC";

$result = $mysqli->query($sql_noticias);
if (!$result) {
    die("Error en la consulta: " . $mysqli->error);
}

$noticias = [];
while ($row = $result->fetch_assoc()) {
    $noticias[] = $row;
}

function buildFacebookIframeSrc(string $link_noticia): string
{
    $link_noticia = trim($link_noticia);

    if (stripos($link_noticia, "facebook.com/plugins/post.php") !== false) {
        return $link_noticia;
    }
    return "https://www.facebook.com/plugins/post.php?href=" . urlencode($link_noticia) . "&show_text=true";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARBIPro</title>
    <link rel="icon" href="imagen/ArbiproP.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="bienvenida.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/ScrollTrigger.min.js"></script>
    <script type="module">
        import {
            animate,
            stagger,
            hover
        } from "https://cdn.jsdelivr.net/npm/motion@12.29.2/+esm"

        animate(".example button", {
            opacity: 1,
            y: [80, 0]
        }, {
            delay: stagger(0.2)
        });

        hover(".contacto", (element) => {
            animate(element, {
                scale: 1.1
            })

            return () => animate(element, {
                scale: 1
            })
        });

        animate(".box", {
            scale: 1
        }, {
            ease: "circInOut",
            duration: .8
        })
    </script>

</head>

<body>

    <!---------------------------- barra de navegacion ----------------------------------------------------------->

    <div class="container-fluid header py-4 fixed-top px-sm-5 px-3 d-flex align-items-center justify-content-between ">
        <div class="d-flex align-items-center  px-2">
            <a href="#">
                <img src="imagen/Arbipro.png" style="width: 100px" class="me-2" alt="Logo">
            </a>
            <a href="https://www.arbitrium.com.co/" target="_blank">
                <img src="imagen/10pro.png" style="width: 80px;" alt="Logo" class="ms-2 ps-1 border-start ">
            </a>
        </div>

        <ul class="example p-2 mb-0 px-4 d-flex align-items-center justify-content-between gap-2 rounded-4 d-none d-md-block" style="background-color: rgba(0, 47, 85, 0.21) !important; list-style:none;">
            <i class="bi bi-box-seam-fill fs-5 pe-4" style="border-right: 1px solid white;"
                role="button"
                tabindex="0"
                data-bs-toggle="popover"
                data-bs-trigger="hover focus"
                data-bs-placement="bottom"
                data-bs-html="true"
                data-bs-custom-class="card-popover"
                data-bs-content='
                    <div class="d-flex gap-3">
                         
                            <div class="pop-body ">
                                <div class="pop-title">Arbimaps Web <i class="bi bi-arrow-right ms-2"></i> </div>
                                    <div class="pop-sub">Conoce nuestra caja de herramientas </div>
                                <div class="pop-text">
                                    
                                </div>
                            </div>
                        </div>
                        '></i>

            <button class="btn navegacion" style="opacity: 0;"
                role="button"
                tabindex="0"
                data-bs-toggle="popover"
                data-bs-trigger="hover focus"
                data-bs-placement="bottom"
                data-bs-html="true"
                data-bs-custom-class="card-popover"
                data-bs-content='
                    <div class="d-flex gap-3">
                         <img src="imagenes/Geo_Visor.png" class="pop-img shadow" alt="preview">
                            <div class="pop-body ">
                                <div class="pop-title">Geo Visor Catastral</div>
                                    <div class="pop-sub">Herramienta para Visualización</div>
                                <div class="pop-text">-Información Predial </br>
                                    -Reportes </br>
                                    -Consultoria de Predios </br>
                                    -Archivos Shp - CAD </br>
                                    -Archivos GeoJson
                                </div>
                            </div>
                        </div>
                        '>
                <i class="bi bi-eyeglasses icono"></i> Observatorio
            </button>

            <button class="btn navegacion" style=" opacity: 0;"
                role="button"
                tabindex="0"
                data-bs-toggle="popover"
                data-bs-trigger="hover focus"
                data-bs-placement="bottom"
                data-bs-html="true"
                data-bs-custom-class="card-popover"
                data-bs-content='
                    <div class="d-flex gap-3">
                         <img src="imagen/personal.png" class="pop-img shadow " alt="preview">
                            <div class="pop-body ">
                                <div class="pop-title">Módulo de personal</div>
                                    <div class="pop-sub">Herramienta de gestión</div>
                                <div class="pop-text">-Control de usuarios </br>
                                    -Control de pagos </br>
                                    -Contratación </br>
                                    -Asignación de actividades 
                                </div>
                            </div>
                        </div>
                        '>
                <i class="bi bi-person-circle icono"></i> Gestion personal
            </button>

            </button>
            <button class="btn navegacion" style=" opacity:0;"
                role="button"
                tabindex="0"
                data-bs-toggle="popover"
                data-bs-trigger="hover focus"
                data-bs-placement="bottom"
                data-bs-html="true"
                data-bs-custom-class="card-popover"
                data-bs-content='
                    <div class="d-flex gap-3">
                         <img src="imagenes/modulo_tramite.png" class="pop-img shadow " alt="preview">
                            <div class="pop-body ">
                                <div class="pop-title">Módulo de trámites</div>
                                    <div class="pop-sub">Herramienta para gestión</div>
                                <div class="pop-text">
                                    - Gestión pedrial </br>
                                    - Control catastral </br>
                                    - Trámites de PQRs </br>
                                    - Trámites y reportes
                                </div>
                            </div>
                        </div>
                        '>
                <i class="bi-journal-bookmark-fill icono"></i> Gestion documental
            </button>

            <button class="btn navegacion" style=" opacity:0;"
                role="button"
                tabindex="0"
                data-bs-toggle="popover"
                data-bs-trigger="hover focus"
                data-bs-placement="bottom"
                data-bs-html="true"
                data-bs-custom-class="card-popover"
                data-bs-content='
                    <div class="d-flex gap-3">
                         <img src="imagen/codigo.png" class="pop-img shadow " alt="preview">
                            <div class="pop-body ">
                                <div class="pop-title">Desarrollo web y de aplicaciones</div>
                                    <div class="pop-sub">Proyectos en programación</div>
                                <div class="pop-text">
                                    - Aplicaciones Web </br>
                                    - Aplicaciones Móvil </br>
                                    - Plugins Qgis</br>
                                </div>
                            </div>
                        </div>
                        '>
                <i class="bi bi-eyeglasses icono"></i> Desarrollo SIG
            </button>
        </ul>


        <div class=" d-flex gap-3  align-items-center ">
            <!-- Inicio de sesión -->
            <a class="nav-link i-sesion btn mx-2 d-flex px-3 p-1 align-items-center " style="font-size: 0.9em; background-color:#ffffff23" href="login.php">
                <i class="bi bi-person-circle fs-5"></i>
                <span class="d-none d-sm-inline ms-2 ">Iniciar Sesión</span>
            </a>

            <!-- Botón de contacto -->
            <a class=" contacto btn bs-azul d-flex px-3 p-1 align-items-center" style="font-size: 0.9em" href="#contacto">
                <i class="bi bi-telephone-plus fs-5"></i>
                <span class="d-none d-sm-inline ms-2">Contáctanos</span>
            </a>

        </div>
    </div>

    <!-------------------------------- sección bienvenido a arbimaps --------------------------------------->
    <section id="inicio" class="hero container-fluid d-flex flex-column px-0 justify-content-center align-items-center pb-5 ">
        <div class="container">
            <div class="row align-items-center justify-content-center">
                <div class="col-12 col-md-9  box rounded-5 p-3 " style="transform: scale(0.4); ">
                    <div class="container-fluid">
                        <div class="row text-center">
                            <div class="col-md-7 text-center text-md-start ">
                                <div>
                                    <h5 class=" text-white display-5 fw-bolder text-md-start text-center">Bienvenido a ARBI<span
                                            class="t">PRO</span>
                                    </h5>
                                    <p class="text-white text-md-start text-center" style="font-size: 0.8em;">Plataforma web para procesos de
                                        actualización catastral
                                        que permite agregar, actualizar y editar información jurídica, económica y básica de los
                                        predios de tu municipio. Cartografía en tiempo real, recolección de información y más.</p>

                                    <div class="d-flex align-items-center gap-2 flex-wrap justify-content-center">

                                        <a href="https://app.merginmaps.com/login?redirect=/dashboard" target="_blank"> <button
                                                class="btn bs-azul-vis rounded-3">Captura de datos</button></a>
                                        <a href="https://www.arbitrium.com.co/" target="_blank"> <button
                                                class="btn btn-outline-light rounded-3">Sobre
                                                Nosotros <i class="bi bi-chevron-right ms-2"></i></button></a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-5  d-flex justify-content-center px-0 ">
                                <img src="imagen/Arbipro.png" alt="Logotipo arbipro"
                                    class=" d-none d-md-block img-fluid img-hero p-3 ps-0">
                            </div>

                            <!--------------------------------- Banner  llamativo para el mapa -------------------------------------->
                            <div class="col-12 px-md-5 mt-3 ">
                                <div class="alert alert-warning  fade show mt-0 border-0 shadow-lg position-relative overflow-hidden w-100  animacion-pulso"
                                    style="background: linear-gradient(125deg, #002f55b1, #60d3fda4); border-radius: 12px;">

                                    <div
                                        class="d-flex flex-column flex-md-row gap-1 align-items-center justify-content-between position-relative ">
                                        <div class="d-flex flex-column flex-md-row align-items-center">
                                            <div class="me-2">
                                                <img src="imagen/emogi-col.png" alt="emogi" style="width: 4em;">
                                            </div>
                                            <div class=" text-center text-md-start">
                                                <h4 class="mb-1 text-white fw-bold fs-5"> ¡Descubre nuestra presencia a nivel nacional!
                                                </h4>
                                                <p class="mb-1 text-white fs-6">
                                                    <strong> Impactamos más de 130 municipios</strong> en <strong>26
                                                        departamentos</strong>
                                                </p>
                                                <small class="text-white opacity-75 fs-8 d-none d-md-block"> Visita nuestro mapa interactivo en tiempo
                                                    real</small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <a href="#visor-zonas-intervencion" id="btn-ver-mapa" style="font-size: 0.8em;"
                                                class="btn btn-light btn-lg fw-bold border-primary-2 p-2 d-flex">
                                                <i class="bi bi-eye-fill me-1"></i>
                                                ¡VER MAPA!
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- zona para el carrusel del los anuncios por parte de arbitrum -->

                <div class="col-9 col-md-3 pe-0 ">
                    <div class=" rounded-4 pb-1">
                        <h6 class="text-center  py-2 rounded-3" style="background-color: #002f5561;"> <i class="bi bi-newspaper me-2"></i> Últimas noticias</h6>
                        <div class=" rounded-4   mb-2">
                            <div id="carouselExampleAutoplaying" class="carousel slide " data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    <div class="carousel-item active ">
                                        <button class="btn  mt-2 btn-full-screen d-flex rounded-3" style="position: absolute;border:1px solid #002F55; margin-left:70%"
                                            data-lightbox="imagenes/FlyerLeiva.png"> <i class="bi bi-eye me-2"></i> Ver</button>
                                        <img src="imagenes/FlyerLeiva.png" class="d-block w-100 rounded-4" alt="...">
                                    </div>
                                    <div class="carousel-item ">
                                        <button class="btn  mt-2 btn-full-screen d-flex rounded-3" style="position: absolute;border:1px solid #002F55; margin-left:70%"
                                            data-lightbox="imagenes/FlyerValleGuamuez.png"> <i class="bi bi-eye me-2"></i> Ver</button>
                                        <img src="imagenes/FlyerValleGuamuez.png" class="d-block w-100 rounded-4" alt="...">
                                    </div>
                                    <div class="carousel-item ">
                                        <button class="btn mt-2 btn-full-screen d-flex rounded-3" style="position: absolute;border:1px solid #002F55; margin-left:70%"
                                            data-lightbox="imagenes/ARBOLETES.png"> <i class="bi bi-eye me-2"></i> Ver</button>
                                        <img src="imagenes/ARBOLETES.png" class="d-block w-100 rounded-4 " alt="...">
                                    </div>
                                    <div class="carousel-item ">
                                        <button class="btn  mt-2 btn-full-screen d-flex rounded-3" style="position: absolute;border:1px solid #002F55; margin-left:70%"
                                            data-lightbox="imagenes/SAN JUAN DE URABÁ.png"> <i class="bi bi-eye me-2"></i>Ver</button>
                                        <img src="imagenes/SAN JUAN DE URABÁ.png" class="d-block w-100 rounded-4" alt="...">
                                    </div>
                                    <div class="carousel-item">
                                        <button class="btn  mt-2 btn-full-screen d-flex rounded-3" style="position: absolute;border:1px solid #002F55; margin-left:70%"
                                            data-lightbox="imagenes/SAN PEDRO.png"> <i class="bi bi-eye me-2"></i> Ver</button>
                                        <img src="imagenes/SAN PEDRO.png" class="d-block w-100 rounded-4" alt="...">
                                    </div>
                                    <div class="carousel-item">
                                        <button class="btn  mt-2 btn-full-screen d-flex rounded-3" style="position: absolute;border:1px solid #002F55; margin-left:70%"
                                            data-lightbox="imagenes/NECOCLÍ.png"> <i class="bi bi-eye me-2"></i> Ver</button>
                                        <img src="imagenes/NECOCLÍ.png" class="d-block w-100 rounded-4" alt="...">
                                    </div>
                                    <div class="carousel-item">
                                        <button class="btn  mt-2 btn-full-screen d-flex rounded-3" style="position: absolute;border:1px solid #002F55; margin-left:70%"
                                            data-lightbox="imagenes/FlyerLeiva.png"> <i class="bi bi-eye me-2"></i> Ver</button>
                                        <img src="imagenes/FlyerLeiva.png" class="d-block w-100 rounded-4" alt="...">
                                    </div>
                                </div>
                                <button class="carousel-control-prev control_noticias_2 " type="button" style="margin-left: -40px;" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next control_noticias_2" type="button" style="margin-right: -40px;" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>
                        </div>


                        <!-- <div class="btn text-white rounded-4 p-2 d-flex align-items-center justify-content-center gap-2 rounded-4"
                            style="position: absolute; z-index:200;bottom:0px; background-color: #002F55">
                            <img src="imagen/10pro.png" style="width: 50px;" class="btn-overlay__icon" alt="">
                            <small style="font-size: 0.7em;">
                                Centro de noticias
                            </small>
                        </div> -->
                    </div>
                </div>

                <div id="lightbox" class="lb hidden" style="z-index: 999999;">
                    <button class="lb-close" aria-label="Cerrar">✕</button>
                    <img id="lbImg" alt="">
                </div>


                <!-- ========================== sección de las cards de las noticias ======================= -->
                <!-- la sección de estas cards van a quedar las cards de las noticias -->

                <div class=" col-8 col-md-12 mt-4">
                    <div class="row  g-2">

                        <?php
                        function getPerSlide()
                        {
                            if (preg_match('/Mobile|Android|iPhone/', $_SERVER['HTTP_USER_AGENT'])) {
                                return 1;
                            }
                            return 4;
                        }
                        $total  = count($noticias);
                        // $perSlide = 4;
                        $perSlide = getPerSlide();
                        $slides = (int) ceil($total / $perSlide);
                        ?>

                        <div id="fbCarousel" class="carousel slide w-100 mx-auto  "
                            data-bs-ride="carousel"
                            data-bs-interval="10000"
                            data-bs-pause="hover"
                            style="max-width: 1400px;">

                            <div class="carousel-indicators " style="z-index: 9999;">
                                <?php for ($s = 0; $s < $slides; $s++): ?>
                                    <button type="button"
                                        data-bs-target="#fbCarousel"
                                        data-bs-slide-to="<?= $s ?>"
                                        class="<?= $s === 0 ? 'active' : '' ?>"
                                        <?= $s === 0 ? 'aria-current="true"' : '' ?>
                                        aria-label="Slide <?= $s + 1 ?>">
                                    </button>
                                <?php endfor; ?>
                            </div>

                            <div class="carousel-inner ">
                                <?php for ($i = 0, $s = 0; $i < $total; $i += $perSlide, $s++): ?>
                                    <div class="carousel-item <?= $s === 0 ? 'active' : '' ?> ">
                                        <div class="fb-slide-grid  ">
                                            <?php for ($k = 0; $k < $perSlide; $k++): ?>
                                                <?php if (!isset($noticias[$i + $k])) break; ?>
                                                <?php
                                                $n   = $noticias[$i + $k];
                                                $src = buildFacebookIframeSrc($n['link_noticia']);
                                                ?>
                                                <div class="fb-box border-0 rounded-4  p-0" style="height:260px;">

                                                    <?php if (!empty($n['descripcion_noticia'])): ?>
                                                        <div class="cardOverlay">
                                                            <img src="imagen/10pro.png" class="mb-5" alt="imagen de logotipo" style="width: 6rem;">
                                                            <div class="overlayText">
                                                                <?= htmlspecialchars($n['descripcion_noticia'], ENT_QUOTES, 'UTF-8') ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>

                                                    <iframe class="fb-iframe"
                                                        loading="lazy"
                                                        src="<?= htmlspecialchars($src, ENT_QUOTES, 'UTF-8') ?>"
                                                        scrolling="yes"
                                                        frameborder="0"
                                                        allowfullscreen="true"
                                                        allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
                                                    </iframe>

                                                </div>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>

                            <button class="carousel-control-prev control_noticias " style="width: 3%; z-index:9999" type="button" data-bs-target="#fbCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Anterior</span>
                            </button>

                            <button class="carousel-control-next control_noticias" style="width: 3%; z-index:9999;margin-right:-40px" type="button" data-bs-target="#fbCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Siguiente</span>
                            </button>
                        </div>


                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- seccion adquirir servicios (Omitido por ahora) -->
    <!-- <section class="container-fluid d-flex flex-column justify-content-center align-items-center min-vh-100 adquirir">
        <div class="container">
            <h1 class="fw-bold text-center">¿Por que adquirir nuestros servicios?</h1>
            <h5 class="text-center fs-6 mx-5 my-3">Combinamos tecnología de punta, conocimiento experto y
                herramientas
                robutas
                para
                brindarle un servicio
                integral en gestion territoral.</h5>
        </div>
        <div class="container mt-4">
            <div class="row g-3  pb-2 px-5">

                Card 1 Modelo de Dominio 
                <article class="col-12 col-sm-6 col-lg-3">
                    <div class="card h-100  card_ad_servi">
                        <div class="card-body d-flex flex-column justify-content-center align-items-center">
                            <img src="imagen/1.png" style="width: 4em" class="card-img-top d-block my-2"
                                alt="Tecnología">
                            <h2 class="card-title fs-6 fw-bold text-start text-center">Modelo de dominio catastral
                            </h2>
                            <p class="card-text text-center" style="font-size: 0.8em">Un sistema robusto y flexible
                                que
                                se adapta alas necesidades específicas de su municipio</p>
                            <button class="btn btn-outline-light mt-2 btn_ad_servi">Leer más</button>
                        </div>
                    </div>
                </article>

                 Card 2 herramientas tecnológicas 

                <article class="col-12 col-sm-6 col-lg-3">
                    <div class="card h-100 card_ad_servi">
                        <div class="card-body d-flex flex-column justify-content-center align-items-center">
                            <img src="imagen/2.png" style="width: 4em" class="card-img-top d-block my-2"
                                alt="Tecnología">
                            <h2 class="card-title fs-6 fw-bold text-center">Herramientas tecnológicas de última
                                generación</h2>
                            <p class="card-text text-center" style="font-size: 0.8em">Plataformas intuitivas y
                                fáciles
                                de
                                usar que agilizan la recolección, análisis y visualización de datos catastrales.</p>
                            <button class="btn btn-outline-light mt-2 btn_ad_servi">Leer más</button>
                        </div>
                    </div>
                </article>

                 card 3 Equipo de expertos 

                <article class="col-12 col-sm-6 col-lg-3">
                    <div class="card h-100 card_ad_servi">
                        <div class="card-body d-flex flex-column justify-content-center align-items-center">
                            <img src="imagen/3.png" style="width: 4em" class="card-img-top d-block my-2"
                                alt="Tecnología">
                            <h2 class="card-title fs-6 fw-bold text-center">Equipo de profesionales altamente
                                calificados
                            </h2>
                            <p class="card-text text-center" style="font-size: 0.8em">Expertos en gestión catastral,
                                planificación territorial y desarrollo urbano que brinda un acompaña,miento
                                personalizado.</p>
                            <button class="btn btn-outline-light mt-2 btn_ad_servi">Leer más</button>
                        </div>
                    </div>
                </article>

                 card 4 usos y aplicaciones 

                <article class="col-12 col-sm-6 col-lg-3">
                    <div class="card h-100 card_ad_servi">
                        <div class="card-body d-flex flex-column justify-content-center align-items-center">
                            <img src="imagen/4.png" style="width: 4em" class="card-img-top d-block my-2"
                                alt="Tecnología">
                            <h2 class="card-title fs-6 fw-bold text-center">Usos y aplicaciones</h2>
                            <p class="card-text text-center fw-light fs-8" style="font-size: 0.8em">Fundamentales en
                                gestión
                                del
                                territorio, agricultura, planificación urbana, permitiendo mediciones precisas,
                                identificación de características geográficas y evaluación ambiental.</p>
                            <button class="btn btn-outline-light mt-2 btn_ad_servi">Leer más</button>
                        </div>
                    </div>
                </article>
    </section> -->


    <!-- ----------------------------------------------------------------------------
            script para que se muestre la sección de mapa en el centro de las pantallas
     ------------------------------------------------------------------------- -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnVerMapa = document.getElementById('btn-ver-mapa');
            const seccionMapa = document.getElementById('visor-zonas-intervencion');
            if (btnVerMapa && seccionMapa) {
                btnVerMapa.addEventListener('click', function(e) {
                    e.preventDefault();
                    seccionMapa.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                });
            }
        });
    </script>

    <section id="visor-zonas-intervencion"
        class=" zona-int container-fluid seccion-mapa d-flex  align-items-center h-100 py-5">
        <div class="container">
            <div class="row align-items-center ">
                <div class="col-lg-4 mb-2 mb-lg-0  ">
                    <h2 class="fs-2 fw-bold mb-2 ">
                        <span>Nuestra presencia</span><br>
                        <span>En Colombia</span>
                    </h2>

                    <p class=" mb-4 text-light">
                        Conoce el alcance real de nuestras intervenciones territoriales a través de nuestro
                        mapa interactivo de cobertura nacional.
                    </p>

                    <!-- KPIs principales -->
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class=" kpi-card p-2 rounded-4 text-center  border-opacity-50 h-100 "
                                style="background-color:#002f55eb;" type="button" data-bs-toggle="modal"
                                data-bs-target="#DepartamentosModal">
                                <h3 class="fs-2 fw-bold mb-2 text-white counter" data-target="24">0</h3>
                                <h6 class="mb-1 text-white">Departamentos</h6>
                                <small class="text-white opacity-75" style="font-size: 0.7em;">Hicimos presencia</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class=" kpi-card p-2 rounded-4 text-center h-100" style="background-color: #2b8c30c2!important;"
                                type="button" data-bs-toggle="modal" data-bs-target="#MunicipiosModal">
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <h3 class="fs-2 fw-bold mb-2 text-white counter" data-target="130">0</h3><span
                                        class="fw-bold fs-3 mb-2">+</span>
                                </div>
                                <h6 class="mb-1 text-white">Municipios</h6>
                                <small class="text-white opacity-75" style="font-size: 0.7em;">Intervenidos
                                    directamente</small>
                            </div>
                        </div>
                    </div>

                    <!---------------------------------- Modal para departamentos intervenidos ----------------------------->
                    <div class="modal fade " id="DepartamentosModal" data-bs-backdrop="static" data-bs-keyboard="false"
                        tabindex="-1" aria-labelledby="DepartamentosModalLabel" aria-hidden="true" role="dialog">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content ">
                                <div
                                    class="modal-header header-mapa text-white d-flex justify-content-between align-items-center">
                                    <h1 class="modal-title fs-6" id="DepartamentosModalLabel">Departamentos
                                        Intervenidos</h1>
                                    <i class="bi bi-x fs-2" type="button" data-bs-dismiss="modal"
                                        aria-label="Close"></i>
                                </div>
                                <div class="modal-body text-center">
                                    • Arauca</br>
                                    • Huila</br>
                                    • Tolima</br>
                                    • Vichada</br>
                                    • Caldas</br>
                                    • Santander</br>
                                    • Risaralda</br>
                                    • Meta</br>
                                    • Antioquia</br>
                                    • Putumayo</br>
                                    • Quindío</br>
                                    • Nariño</br>
                                    • Sucre</br>
                                    • Córdoba</br>
                                    • Boyacá</br>
                                    • Cundinamarca</br>
                                    • Bolívar</br>
                                    • Cauca</br>
                                    • La Guajira</br>
                                    • Magdalena</br>
                                    • Valle del Cauca</br>
                                </div>
                                <div class="modal-footer" style="background-color: #002f55eb;">
                                    <small class=" text-center text-white" style="font-size: 0.6em;"> © ARBIMaps todos
                                        los derechos
                                        reservados</small>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!------------------------ Modal para Municipios intervenidos------------------------------------------->
                    <div class="modal fade " id="MunicipiosModal" data-bs-backdrop="static" data-bs-keyboard="false"
                        tabindex="-1" aria-labelledby="MunicipiosModalLabel" aria-hidden="true" role="dialog">
                        <div class="modal-dialog modal-dialog-scrollable">
                            <div class="modal-content ">
                                <div class="modal-header header-mapa text-white d-flex justify-content-between align-items-center"
                                    style="background-color: #257D29C2;">
                                    <h1 class="modal-title fs-6" id="MunicipiosModalLabel">Municipios
                                        Intervenidos</h1>
                                    <i class="bi bi-x fs-2" type="button" data-bs-dismiss="modal"
                                        aria-label="Close"></i>
                                </div>
                                <div class="modal-body text-start">
                                    <strong>Departamento de Arauca:</strong> </br>
                                    • Arauca. </br>
                                    <br> <strong>Departamento de Huila:</strong> </br>
                                    •La Plata, Garzón, Paicol, Neiva, Rivera, Gigante, Aipe, Baraya,
                                    Pitalito, Santa María, Acevedo,
                                    Tello, Timaná, Campoalegre, Hobo, Tesalia, Nátaga, Íquira, Yaguará,
                                    El Agrado, El Pital, La Argentina,
                                    Colombia, Oporapa, Tarqui. </br>

                                    <br> <strong>Departamento de Tolima:</strong> </br>
                                    • Ibagué, Melgar. </br>

                                    <br> <strong>Departamento de Vichada:</strong> </br>
                                    • La Primavera. </br>

                                    <br> <strong>Departamento de Caldas:</strong> </br>
                                    • Samaná, Marulanda, Pensilvania, La Dorada, Manzanares, Norcasia,
                                    Chinchiná, Palestina, Salamina., La Merced, Marquetalia, Supia.</br>

                                    <br> <strong>Departamento de Santander:</strong> </br>
                                    • Bucaramanga, Lebrija, Girón, Puente Nacional, San Vicente de Chucurí,
                                    Jesús María, La Pazz, Simacota,
                                    Vélez, Socorro, Floridablanca, Barrancabermeja, Cimitarra, Barbosa,
                                    Rionegro.</br>

                                    <br> <strong>Departamento de Risaralda:</strong> </br>
                                    • Dosquebradas, Pereira, Santa Rosa De Cabal, Apía.</br>

                                    <br> <strong>Departamento de Meta:</strong> </br>
                                    • Villavicencio, Puerto López, Vista Hermosa, Acacías.</br>

                                    <br> <strong>Departamento de Antioquia:</strong> </br>
                                    • Medellín, Bello, San Juan De Urabá, San Pedro De Urabá, Arboletes,
                                    Necoclí, Yondó.</br>

                                    <br> <strong>Departamento de Putumayo:</strong> </br>
                                    • Valle Del Guamuez, Mocoa, Sibundoy, Villagarzón.</br>

                                    <br> <strong>Departamento de Quindio:</strong> </br>
                                    • Armenia, Circasia.</br>

                                    <br> <strong>Departamento de Nariño:</strong> </br>
                                    • Pasto, Ipiales, Leiva, La Unión, Buesaco.</br>

                                    <br> <strong>Departamento de Sucre:</strong> </br>
                                    • La Unión.</br>

                                    <br> <strong>Departamento de Córdoba:</strong> </br>
                                    • Montería, Pueblo Nuevo.</br>

                                    <br> <strong>Departamento de Boyacá:</strong> </br>
                                    • Saboyá, Chiquinquirá, Guateque, Macanal, Chivor, Tenza, Sutatenza.</br>

                                    <br> <strong>Departamento de Cundinamarca:</strong> </br>
                                    • Chocontá, Bogotá, D.C, Machetá, San Antonio Del Tequendama, Suesca,
                                    Zipaquirá, Tibirita, Supatá,
                                    Ubalá, Chipaque, Cajicá, Cogua, Nemocón, Tocancipá, Zipacón, La
                                    Mesa, Cachipay,
                                    Tena, Tausa, Fusagasugá, Girardot, Sibaté, Silvania, Soacha, Sopó,
                                    Tibacuy, Granada.</br>

                                    <br> <strong>Departamento de Bolívar:</strong> </br>
                                    • Córdoba, El Carmen De Bolívar, San Martín De Loba.</br>

                                    <br> <strong>Departamento de Cauca:</strong> </br>
                                    • El Tambo, Patía, Sotará, La Vega, Totoró, Suárez, Buenos
                                    Aires, Popayán, Páezz, Corinto, Piendamó Tunia.</br>

                                    <br> <strong>Departamento de La Guajira:</strong> </br>
                                    • Barrancas.</br>

                                    <br> <strong>Departamento de Magdalena:</strong> </br>
                                    • El Banco, Sabanas De San Ángel.</br>

                                    <br> <strong>Departamento de Valle del Cauca:</strong> </br>
                                    • Ansermanuevo, San Pedro.</br>
                                </div>
                                <div class="modal-footer" style="background-color: #257d29d1;">
                                    <small class=" text-center text-white" style="font-size: 0.6em;"> © ARBIMaps todos
                                        los derechos
                                        reservados</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Barra de progreso de cobertura -->
                    <div class="mb-4 text-center">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="mb-0 text-warning" style="color: #FFC107;">
                                <i class="bi bi-graph-up-arrow me-2"></i>
                                Cobertura Nacional
                            </h5>
                            <div class="badge bg-warning text-dark fs-6 "><span class="counter"
                                    data-target="75">0</span> <span>%</span></div>
                        </div>
                        <div class="progress mb-2" style="height: 12px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 0%" aria-valuenow="0"
                                aria-valuemin="0" aria-valuemax="100" data-target="75"></div>
                        </div>
                        <small>Con presencia en 24 de los 32 departamentos de Colombia</small>
                    </div>

                    <!-- Estadísticas adicionales -->
                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <div class="rounded-3 p-3 text-center d-flex flex-column justify-content-center h-100 align-items-center"
                                style="background-color: #0F569948;">
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <h4 class=" mb-1 counter" data-target="240">0</h4> <span class="fs-4 mb-1">Mil</span>
                                </div>
                                <small>Predios Gestionados</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="rounded-3 p-3 text-center" style="background-color: #0F569948;">
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <span>+</span>
                                    <h4 class=" mb-1 counter" data-target="2">0</h4> <span class="fs-4 mb-1">Millones</span>
                                </div>
                                <small>Hectáreas Intervenidas</small>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="d-grid gap-2 w-50 " style="margin-left: 25%;">
                        <a href="#contacto" class="btn btn-outline-light btn-lg px-2 fs-6">
                            <i class="bi bi-telephone-fill me-2"></i>
                            Contáctanos
                        </a>
                    </div>
                </div>

                <div class="col-lg-8 py-4">
                    <div class="position-relative">
                        <!-- Header del mapa -->
                        <div class=" rounded-top-4 p-3 px-4 mb-0 header-mapa">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-1 text-white fs-6 ">
                                        Mapa de intervención Nacional
                                    </h4>
                                    <small class="text-white opacity-75 fw-bold d-none d-md-block fs-7">Consulta
                                        departamentos y municipios
                                        intervenidos</small>
                                </div>
                                <div class="text-end">
                                    <div
                                        class="badge bg-success p-2 d-flex align-items-center gap-1 justify-content-center">
                                        <i class="bi bi-circle-fill me-1 animacion-pulso"
                                            style="font-size: 0.6rem;"></i>
                                        <p class="m-0">En tiempo real</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contenedor del mapa -->
                        <div class="bg-white position-relative  overflow-hidden shadow-lg">
                            <div id="mapa" style="height: 33rem; width: 100%;"></div>
                        </div>

                        <!-- Pie de mapa -->
                        <div class="p-2 d-flex align-items-center justify-content-between rounded-bottom-4"
                            style="background-color: #002f55eb;">
                            <small class="px-2 text-center" style="font-size: 0.7em;">Click en <i
                                    class="bi bi-layers-fill"></i> para visualizar municipios</small>
                            <small class="px-2 text-center" style="font-size: 0.6em;"> © ARBIMaps todos los derechos
                                reservados</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!------------------------------------- Sección nuestro clientes -------------------------------------------------------->
    <section class="container  d-flex flex-column justify-content-center align-items-center "
        style="margin-top: 60px; margin-bottom: 60px;">
        <div class="row d-flex align-items-center">
            <div class="col-sm-12 col-md-5 order-2 order-md-1">
                <div class="row">
                    <div class="col-3 p-2 d-flex justify-content-center align-items-center"><img
                            class="img-clie img-fluid" src="./imagen/empresas/ADR_BLANCO.png">
                    </div>
                    <div class="col-3 p-2 d-flex justify-content-center align-items-center"><img
                            class="img-clie img-fluid" src="./imagen/empresas/AMB_BLANCO.png" alt="logo empresa"></div>
                    <div class="col-3 p-2 d-flex justify-content-center align-items-center"><img class="img-clie"
                            src="./imagen/empresas/ANI_BLANCO.png" alt="logo empresa"></div>
                    <div class="col-3 p-2 d-flex justify-content-center align-items-center"><img class="img-clie"
                            src="./imagen/empresas/CEIBAS_BLANCO.png" alt="logo empresa"></div>
                    <div class="col-3 p-2 d-flex justify-content-center align-items-center"><img class="img-clie"
                            src="./imagen/empresas/EPM_BLANCO.png" alt="logo empresa"></div>
                    <div class="col-3 p-2 d-flex justify-content-center align-items-center "><img class="img-clie"
                            src="./imagen/empresas/METRO_BOGOTA_BLANCO.png" alt="logo empresa"></div>
                    <div class="col-3 p-2 d-flex justify-content-center align-items-center "><img class="img-clie"
                            src="./imagen/empresas/INCODER_BLANCO.png" alt="logo empresa"></div>
                    <div class="col-3 p-2 d-flex justify-content-center align-items-center"><img class="img-clie"
                            src="./imagen/empresas/enel.png" alt="logo empresa"></div>
                    <div class="col-6 p-2 d-flex justify-content-center align-items-center"><img class="img-clie"
                            src="./imagen/empresas/GEB_BLANCO.png" alt="logo empresa"></div>
                    <div class="col-3 p-2 d-flex justify-content-center align-items-center"><img class="img-clie"
                            src="./imagen/empresas/GARZN.png" alt="logo empresa"></div>
                    <div class="col-3 p-2 d-flex justify-content-center align-items-center"><img class="img-clie"
                            src="./imagen/empresas/AMVA.png" alt="logo empresa"></div>
                    <div class="col-4 p-2 d-flex justify-content-center align-items-center"><img class="img-clie"
                            src="./imagen/empresas/cbec.png" alt="logo empresa"></div>
                    <div class="col-4 p-2 d-flex justify-content-center align-items-center"><img class="img-clie"
                            src="./imagen/empresas/igac.png" alt="logo empresa"></div>
                    <div class="col-4 p-2 d-flex justify-content-center align-items-center"><img class="img-clie"
                            src="./imagen/empresas/VLR.png" alt="logo empresa"></div>


                </div>
            </div>
            <div class="col-sm-12 col-md-7 order-1 order-md-2 pb-5 pb-md-0">
                <div class="container d-flex flex-column text-center gap-3 ">
                    <h1 class="fw-bold">Clientes que confían, resultados que respaldan</h1>
                    <p class="fw-light fs-6">Hemos trabajado de la mano con nuestros clientes para brindar soluciones
                        catastrales efectivas y
                        puntuales. Gracias a nuestro enfoque oportuno y nuestro compromiso técnico, seguimos
                        fortaleciendo
                        la gestión del territorio en todo el país.</p>

                    <a href="https://hvap3wvdptc5xswg.public.blob.vercel-storage.com/PORTAFOLIO.pdf"
                        target="_blank">
                        <button class="btn-portafolio">Portafolio <i
                                class="bi bi-arrow-up-right-circle"></i></i></button></a>

                </div>
            </div>
        </div>
    </section>



    <section id="contacto"
        class="hero-contacto container-fluid d-flex flex-column justify-content-center align-items-center py-3">
        <h1 class="fs-sm-1  fw-bold mt-5">¡Contáctanos!</h1>

        <div class="container text-center py-3 px-sm-4  rounded-5 shadow-lg " style="backdrop-filter: blur(18px);">
            <div class="row p-3 g-4 px-0">

                <div class="col-12 col-lg-7 px-md-5 ">
                    <div
                        class="container rounded-5 p-4 px-0  h-100 d-flex flex-column justify-content-center align-items-center   ">
                        <small class="text-uppercase mb-2 text-start w-100">
                            Estamos aquí para ayudarte
                        </small>
                        <h1 class="fw-bold text-start mb-2">
                            Cuéntanos tu solicitud <br>
                            o interés en nuestros servicios...
                        </h1>
                        <small class="mb-4 text-start" style="font-size:0.8em"> Ponte en contacto mediante el siguiente formulario o comunícate con nosotros por los siguientes canales de contacto</small>


                        <div class="container-fluid ">
                            <div class="row">
                                <div class="col-12 col-md-7 p-2">
                                    <div class="container-fluid">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="d-flex align-items-center  w-100 mb-3">
                                                    <i class="bi bi-envelope-arrow-up fs-2 me-3"></i>
                                                    <div class="d-flex flex-column">
                                                        <h5 class="fw-bold m-0 fs-7 text-start">Correo</h5>
                                                        <p class="m-0 text-start" style="font-size: 0.7em;">info@arbitrium.com.co
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="d-flex align-items-center mb-3 w-100">
                                                    <i class="bi bi-telephone fs-2 me-3"></i>
                                                    <div class="d-flex flex-column">
                                                        <h5 class="fw-bold m-0 fs-7 text-start">Celular</h5>
                                                        <p class="m-0 text-start" style="font-size: 0.7em;">318 377 7066
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="d-flex align-items-center justify-content-center  w-100">
                                                    <i class="bi bi-geo-alt-fill fs-2 me-3"></i>
                                                    <div class="d-flex flex-column">
                                                        <h5 class="fw-bold m-0 fs-7 text-start">Nuestra sede Neiva</h5>
                                                        <p class="m-0 text-start" style="font-size: 0.7em;">Cra. 15 No 26 -12
                                                            Sur- Oficina 403 Edificio
                                                            Empresarial Prohuila
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <article class="col-12 col-md-5 mt-3 mt-md-0 p-0 ">
                                    <iframe
                                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3325.7633204175827!2d-75.28038243627832!3d2.9053545032845713!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8e3b736127aed24b%3A0x64a3318a86790294!2sEDIFICIO%20EMPRESARIAL%20PROHUILA!5e1!3m2!1ses!2sco!4v1752076764867!5m2!1ses!2sco"
                                        style="width: 100%;" class="rounded-4 p-0 mt-2"></iframe>
                                </article>

                            </div>
                        </div>

                    </div>
                </div>

                <div class="col-12 col-lg-5  ">
                    <div class="container rounded-5 p-4 card-contacto-inputs h-100 d-flex flex-column justify-content-center align-items-center card-contacto " style="border: 1px solid #ffffff21;">

                        <form id="form-contacto"
                            class="d-flex flex-column align-items-center w-100 "
                            action="./acciones/guardar_contactanos.php"
                            method="POST"
                            style="width: 80%;">

                            <div class="container-fluid">
                                <div class="row">

                                    <h6>Formulario de contacto</h6>

                                    <!-- Tipo de solicitud -->
                                    <div class="col-12 p-1 px-2 my-1">

                                        <div class="input-group shadow-sm">
                                            <!-- <span class="input-group-text">
                                                <i class="bi bi-person-badge"></i>
                                            </span> -->

                                            <select
                                                class="form-select card-contacto-inputs border-0 py-2"
                                                style="font-size:0.9em;"
                                                id="tipo_contactanos"
                                                name="tipo_contactanos"
                                                required>
                                                <option value="" selected disabled hidden>Selecciona el tipo de solicitud</option>
                                                <option value="AREA COMERCIAL">Area comercial</option>
                                                <option value="ASESORIA">Asesoria</option>
                                                <option value="ATENCION AL USUARIO">Atencion al usuario</option>
                                                <option value="PETICION">Peticion</option>
                                                <option value="QUEJA">Queja</option>
                                                <option value="RECLAMO">Reclamo</option>
                                                <option value="SUGERENCIA">Sugerencia</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Nombre -->
                                    <div class="col-12 p-1 px-2 my-1">
                                        <div class="input-group shadow-sm">
                                            <!-- <span class="input-group-text">
                                                <i class="bi bi-person"></i>
                                            </span> -->

                                            <input
                                                type="text"
                                                class="form-control  card-contacto-inputs border-0 py-2"
                                                style="font-size:0.9em; "
                                                id="nombre_completo"
                                                name="nombre_completo"
                                                placeholder="Ingresa tu nombre..."
                                                aria-label="Nombres"
                                                required>
                                        </div>
                                    </div>

                                    <!-- Correo -->
                                    <div class="col-12 p-1 px-2 my-1">
                                        <div class="input-group shadow-sm ">
                                            <!-- <span class="input-group-text" id="basic-addon1">
                                                <i class="bi bi-envelope-at"></i>
                                            </span> -->

                                            <input
                                                type="email"
                                                class="form-control card-contacto-inputs border-0 py-2"
                                                style="font-size:0.9em;"
                                                id="correo"
                                                placeholder="correo@ejemplo.com"
                                                name="correo"
                                                aria-label="PrimerNombre">
                                        </div>
                                    </div>

                                    <!-- Teléfono -->
                                    <div class="col-12 p-1 px-2 my-1">
                                        <div class="input-group shadow-sm">
                                            <!-- <span class="input-group-text">
                                                <i class="bi bi-phone"></i>
                                            </span> -->

                                            <input
                                                type="text"
                                                class="form-control card-contacto-inputs border-0 py-2"
                                                style="font-size:0.9em;"
                                                id="numero_telefono"
                                                name="numero_telefono"
                                                placeholder="Número de celular"
                                                aria-label="Ciudad"
                                                aria-describedby="basic-addon1">
                                        </div>
                                    </div>

                                    <!-- Departamento -->
                                    <div class="col-6 p-1 px-2 my-1">
                                        <div class="input-group shadow-sm">
                                            <!-- <span class="input-group-text">
                                                <i class="bi bi-geo-alt"></i>
                                            </span> -->

                                            <select
                                                id="departamento"
                                                class="form-select card-contacto-inputs border-0 py-2"
                                                style="font-size: 0.9em; background-color: #002f5572; color:rgba(255, 255, 255, 0.6)"
                                                name="departamento"
                                                required>
                                                <option value="" selected disabled hidden>Selecciona un departamento</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Municipio -->
                                    <div class="col-6 p-1 px-2 my-1">
                                        <div class="input-group shadow-sm">
                                            <!-- <span class="input-group-text ">
                                                <i class="bi bi-geo"></i>
                                            </span> -->

                                            <select
                                                id="municipio"
                                                class="form-select card-contacto-inputs border-0 py-2"
                                                style="font-size: 0.9em; background-color: #002f5572; color:rgba(255, 255, 255, 0.6)"
                                                name="municipio"
                                                required
                                                disabled>
                                                <option value="" class="text-muted" selected disabled hidden>Selecciona un municipio</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Asunto -->
                                    <div class="col-12 p-1 px-2 my-1">
                                        <div class="input-group shadow-sm">
                                            <!-- <span class="input-group-text">
                                                <i class="bi bi-chat-left-text"></i>
                                            </span> -->

                                            <input
                                                type="text"
                                                class="form-control card-contacto-inputs border-0 py-2"
                                                style="font-size:0.9em;"
                                                name="asunto_contactanos"
                                                placeholder="Ingresa el asunto"
                                                required>
                                        </div>
                                    </div>

                                    <!-- Mensaje -->
                                    <div class="col-12 p-1 px-2 my-1">
                                        <div class="input-group shadow-sm">
                                            <!-- <span class="input-group-text">
                                                <i class="bi bi-pencil-square"></i>
                                            </span> -->

                                            <textarea
                                                class="form-control card-contacto-inputs border-0 py-2"
                                                style="font-size:0.9em;"
                                                style="height: 40%; resize: none;"
                                                name="duda_contactanos"
                                                placeholder="Describe tu duda"
                                                required></textarea>
                                        </div>
                                    </div>

                                    <!-- Botón -->
                                    <div class="col-12 p-1 px-2 ">
                                        <button class="btn-enviar mt-0 w-100 rounded-2 py-1 fw-bold" type="submit">
                                            Enviar
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </form>

                    </div>
                </div>

                <div class="col-12 ">
                    <h4 class="mb-3">Información de nuestras oficinas</h4>
                    <div class="container-fluid">
                        <div class="row">

                            <div class="col-12 col-md-3 ">
                                <div class="card card-popover border-0 text-white rounded-4 py-3" style="background-color: #002f5599;">
                                    <h5 style="font-size: 1.2em;" class="mb-3">Necoclí</h5>
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center  w-100 mb-2 ">
                                            <i class="bi bi-whatsapp fs-5 me-3"></i>
                                            <div class="d-flex flex-column">
                                                <h5 class="fw-bold m-0  text-start" style="font-size: 0.7em;">Contacto</h5>
                                                <p class="m-0 text-start" style="font-size: 0.7em;">321 321 4701
                                                </p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center  w-100 mb-2 ">
                                            <i class="bi bi-envelope fs-5 me-3"></i>
                                            <div class="d-flex flex-column">
                                                <h5 class="fw-bold m-0  text-start" style="font-size: 0.7em;">Correo</h5>
                                                <p class="m-0 text-start" style="font-size: 0.7em;">auxventanilla.necocli@gmail.com
                                                </p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center  w-100 mb-2 ">
                                            <i class="bi bi-geo-alt-fill fs-5 me-3"></i>
                                            <div class="d-flex flex-column">
                                                <h5 class="fw-bold m-0  text-start" style="font-size: 0.7em;">Carrera 50 #52 - 46 </br> Calle el Cucaracho, Barrio central.</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-3 mt-3 mt-md-0">
                                <div class="card card-popover border-0 text-white rounded-4 py-3" style="background-color: #002f5599;">
                                    <h5 style="font-size: 1.2em;" class="mb-3">San Pedro de Urabá</h5>
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center  w-100 mb-2 ">
                                            <i class="bi bi-whatsapp fs-5 me-3"></i>
                                            <div class="d-flex flex-column">
                                                <h5 class="fw-bold m-0  text-start" style="font-size: 0.7em;">Contacto</h5>
                                                <p class="m-0 text-start" style="font-size: 0.7em;">310 585 9053
                                                </p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center  w-100 mb-2 ">
                                            <i class="bi bi-envelope fs-5 me-3"></i>
                                            <div class="d-flex flex-column">
                                                <h5 class="fw-bold m-0  text-start" style="font-size: 0.7em;">Correo</h5>
                                                <p class="m-0 text-start" style="font-size: 0.7em;">auxventanilla.sanpedro@gmail.com
                                                </p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center  w-100 mb-2 ">
                                            <i class="bi bi-geo-alt-fill fs-5 me-3"></i>
                                            <div class="d-flex flex-column">
                                                <h5 class="fw-bold m-0  text-start" style="font-size: 0.7em;">Calle 52 #46 -117 </br> Barrio pueblo nuevo.</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-3 mt-3 mt-md-0">
                                <div class="card card-popover border-0 text-white rounded-4 py-3" style="background-color: #002f5599;">
                                    <h5 style="font-size: 1.2em;" class="mb-3">Arboletes</h5>
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center  w-100 mb-2 ">
                                            <i class="bi bi-whatsapp fs-5 me-3"></i>
                                            <div class="d-flex flex-column">
                                                <h5 class="fw-bold m-0  text-start" style="font-size: 0.7em;">Contacto</h5>
                                                <p class="m-0 text-start" style="font-size: 0.7em;">321 321 4679
                                                </p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center  w-100 mb-2 ">
                                            <i class="bi bi-envelope fs-5 me-3"></i>
                                            <div class="d-flex flex-column">
                                                <h5 class="fw-bold m-0  text-start" style="font-size: 0.7em;">Correo</h5>
                                                <p class="m-0 text-start" style="font-size: 0.7em;">auxventanilla.arboletes@gmail.com
                                                </p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center  w-100 mb-2 ">
                                            <i class="bi bi-geo-alt-fill fs-5 me-3"></i>
                                            <div class="d-flex flex-column">
                                                <h5 class="fw-bold m-0  text-start" style="font-size: 0.7em;">Carrera 29 con calle 28 No. 27-28 </br> Barrio villa luz</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-3 mt-3 mt-md-0">
                                <div class="card card-popover border-0 text-white rounded-4 py-3" style="background-color: #002f5599;">
                                    <h5 style="font-size: 1.2em;" class="mb-3">San Juan de Urabá</h5>
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center  w-100 mb-2 ">
                                            <i class="bi bi-whatsapp fs-5 me-3"></i>
                                            <div class="d-flex flex-column">
                                                <h5 class="fw-bold m-0  text-start" style="font-size: 0.7em;">Contacto</h5>
                                                <p class="m-0 text-start" style="font-size: 0.7em;">321 321 4695
                                                </p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center  w-100 mb-2 ">
                                            <i class="bi bi-envelope fs-5 me-3"></i>
                                            <div class="d-flex flex-column">
                                                <h5 class="fw-bold m-0  text-start" style="font-size: 0.7em;">Correo</h5>
                                                <p class="m-0 text-start" style="font-size: 0.7em;">auxventanilla.sanjuan@gmail.com
                                                </p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center  w-100 mb-2 ">
                                            <i class="bi bi-geo-alt-fill fs-5 me-3"></i>
                                            <div class="d-flex flex-column">
                                                <h5 class="fw-bold m-0  text-start" style="font-size: 0.7em;">Calle 21 No. 21 - 32 </br> Barrio centro</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-------------------------------------------------------- footer ------------------------------------------------------------->
    <footer class="container-fluid bg-footer mt-auto py-2 ">
        <section
            class="container d-flex flex-sm-column flex-md-row align-items-center justify-content-between gap-3 gap-md-0">

            <!-- Redes sociales -->
            <ul class="list-unstyled d-flex gap-3 justify-content-center my-1 ">
                <a href="https://www.facebook.com/profile.php?id=100091909492730&sfnsn=scwspwa&mibextid=RUbZ1f"
                    target="_blank"><i class=" text-white bi bi-facebook fs-5 opacity-75 "></i></a>
                <a href="https://www.instagram.com/arbitrium_sas?utm_source=ig_web_button_share_sheet&igsh=MXBtaGp4Nnl3MTl6cA=="
                    target="_blank"><i class="bi bi-instagram fs-5 opacity-75 text-white"></i></a>
                <a href="https://wa.me/message/35F5EXJA75Z7F1" target="_blank"><i
                        class="bi bi-whatsapp fs-5 opacity-75 text-white"></i></a>
                <a href="https://www.youtube.com/@ArbitriumSas-b8k" target="_blank"><i
                        class="bi bi-youtube fs-5 opacity-75 text-white"></i></a>
            </ul>

            <!-- Texto copyright -->
            <p class="my-0 text-center" style="font-size: 0.6em;">
                &copy; Copyright <strong>Arbitrium SAS</strong> ARBIMaps 2025
            </p>
        </section>
    </footer>

    <!------------------------------------- Botones de WhatsApp y Home flotantes --------------------------------->
    <div class="position-fixed d-flex flex-column bottom-0  end-0 m-3" style="z-index: 1050;">
        <button onclick="scrollToTop()" class="btn btn-primary rounded-circle shadow-lg btn-home-float"
            style="width: 50px; height: 50px; display: none;">
            <i class="bi bi-house-fill fs-4"></i>
        </button>
    </div>

    <div class="position-fixed d-flex flex-column bottom-0 start-0 m-3" style="z-index: 1050;">
        <a href="https://wa.me/message/35F5EXJA75Z7F1" target="_blank" class="btn btn-success rounded-circle btn-whats">
            <i class="bi bi-whatsapp fs-5 opacity-75 text-white"></i>
        </a>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
        crossorigin="anonymous"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="app.js"></script>
    <script src="appMapa.js"></script>

    <!-- ------------import para noticias--------------- -->
    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous"
        src="https://connect.facebook.net/es_ES/sdk.js#xfbml=1&version=v18.0"></script>


    <script>
        function fbReady() {
            return window.FB && FB.XFBML && typeof FB.XFBML.parse === "function";
        }

        document.addEventListener("DOMContentLoaded", function() {
            let tries = 0;
            const t = setInterval(() => {
                tries++;
                if (fbReady()) {
                    clearInterval(t);
                    // Render inicial
                    setTimeout(() => FB.XFBML.parse(), 250);
                }
                if (tries >= 40) clearInterval(t);
            }, 250);
        });
    </script>


    <!-- script del sweet alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>

<script>
    const API_DEPTO = "https://www.datos.gov.co/resource/vcjz-niiq.json";
    const API_MPIO = "https://www.datos.gov.co/resource/gdxc-w37w.json";
    const $depto = document.getElementById("departamento");
    const $mpio = document.getElementById("municipio");
    const municipiosCache = new Map();

    function resetMunicipios() {
        $mpio.innerHTML = `<option value="" selected disabled hidden>Selecciona un municipio</option>`;
        $mpio.disabled = true;
    }

    async function cargarDepartamentos() {
        const url = `${API_DEPTO}?$select=codigo_departamento,nombre_departamento&$order=nombre_departamento`;
        const res = await fetch(url);
        const data = await res.json();

        for (const d of data) {
            const opt = document.createElement("option");
            opt.value = d.nombre_departamento;
            opt.textContent = d.nombre_departamento;
            opt.dataset.cod = d.codigo_departamento;
            $depto.appendChild(opt);
        }
    }

    async function cargarMunicipios(codDepto) {
        resetMunicipios();

        if (municipiosCache.has(codDepto)) {
            pintarMunicipios(municipiosCache.get(codDepto));
            return;
        }

        const url =
            `${API_MPIO}?$select=cod_mpio,nom_mpio&` +
            `$where=cod_dpto='${codDepto}'&` +
            `$order=nom_mpio&$limit=2000`;

        const res = await fetch(url);
        const data = await res.json();
        municipiosCache.set(codDepto, data);
        pintarMunicipios(data);
    }

    function pintarMunicipios(lista) {
        for (const m of lista) {
            const opt = document.createElement("option");
            opt.value = m.nom_mpio;
            opt.textContent = m.nom_mpio;
            $mpio.appendChild(opt);
        }
        $mpio.disabled = false;
    }

    $depto.addEventListener("change", (e) => {
        const codDepto = e.target.selectedOptions[0]?.dataset.cod;
        if (!codDepto) return resetMunicipios();
        cargarMunicipios(codDepto);
    });

    resetMunicipios();
    cargarDepartamentos();
</script>


<script>
    document.addEventListener("DOMContentLoaded", () => {
        const form = document.getElementById("form-contacto");
        if (!form) return;

        let submitting = false;

        const ArbiSwal = Swal.mixin({
            customClass: {
                popup: "arbi-swal",
                confirmButton: "arbi-confirm",
                cancelButton: "arbi-cancel"
            },
            buttonsStyling: false,
            allowOutsideClick: false
        });
        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            if (submitting) return;
            const tipo = form.querySelector('[name="tipo_contactanos"]')?.value || "-";
            const asunto = form.querySelector('[name="asunto_contactanos"]')?.value || "-";
            const correo = form.querySelector('[name="correo"]')?.value || "-";
            const html = `
                    <div class="arbi-topbar"></div>
                    <div style="text-align:left">
                    <div><b>Vas a enviar tu solicitud.</b></div>
                    <div class="arbi-mini">Antes de continuar, revisa que la información sea correcta.</div>
                    <div class="arbi-summary">
                        <div><b>Tipo:</b> ${escapeHtml(tipo)}</div>
                        <div><b>Asunto:</b> ${escapeHtml(asunto)}</div>
                        <div><b>Correo:</b> ${escapeHtml(correo)}</div>
                    </div>
                    <div class="arbi-mini">
                        Al confirmar, tu mensaje será enviado y te contactaremos lo antes posible.
                    </div>
                    </div>
                `;
            const result = await ArbiSwal.fire({
                title: "Confirmar envío",
                html,
                icon: "question",
                showCancelButton: true,
                confirmButtonText: 'Enviar',
                cancelButtonText: 'Volver',
                reverseButtons: true,
                focusCancel: true
            });
            if (!result.isConfirmed) return;
            submitting = true;
            ArbiSwal.fire({
                title: "Enviando...",
                html: '<span class="arbi-mini">Un momento por favor.</span>',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            form.submit();
        });

        function escapeHtml(str) {
            return String(str)
                .replaceAll("&", "&amp;")
                .replaceAll("<", "&lt;")
                .replaceAll(">", "&gt;")
                .replaceAll('"', "&quot;")
                .replaceAll("'", "&#039;");
        }
    });
</script>


<script>
    document.addEventListener("DOMContentLoaded", () => {
        const params = new URLSearchParams(window.location.search);
        const ok = params.get("contacto") === "ok";
        if (!ok) return;
        const ArbiSwal = Swal.mixin({
            customClass: {
                popup: "arbi-swal",
                confirmButton: "arbi-confirm"
            },
            buttonsStyling: false,
            allowOutsideClick: false
        });
        ArbiSwal.fire({
            title: `
                <div class="arbi-success-title">
                <span class="arbi-check">✓</span>
                Solicitud enviada
                </div>
            `,
            html: `
                <div class="arbi-topbar"></div>
                <div style="text-align:left">
                <div><b>Listo.</b> Hemos recibido tu solicitud correctamente.</div>
                <div class="arbi-mini">Nuestro equipo la revisará y te contactará lo antes posible.</div>
                </div>
            `,
            icon: "success",
            confirmButtonText: "Entendido",
        }).then(() => {
            params.delete("contacto");
            const newUrl =
                window.location.pathname + (params.toString() ? `?${params.toString()}` : "");
            window.history.replaceState({}, document.title, newUrl);
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = [].slice.call(
            document.querySelectorAll('[data-bs-toggle="tooltip"]')
        );
        tooltipTriggerList.forEach(function(tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

<script>
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
        new bootstrap.Popover(el, {
            container: 'body' // evita que se corte si hay overflow en el contenedor
        });
    });
</script>

<script>
    const lb = document.getElementById('lightbox');
    const lbImg = document.getElementById('lbImg');
    const lbClose = document.querySelector('.lb-close');
    const body = document.body;

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-lightbox]');
        if (!btn) return;

        lbImg.src = btn.getAttribute('data-lightbox');
        lb.classList.remove('hidden');
        body.classList.add('no-scroll'); // 🔒 bloquea scroll
    });

    function closeLightbox() {
        lb.classList.add('hidden');
        body.classList.remove('no-scroll'); // 🔓 habilita scroll
        lbImg.src = '';
    }

    lb.addEventListener('click', (e) => {
        if (e.target === lb || e.target === lbClose) {
            closeLightbox();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !lb.classList.contains('hidden')) {
            closeLightbox();
        }
    });
</script>