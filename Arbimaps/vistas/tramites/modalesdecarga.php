
<!-- este código es el ejemplo de las pantallas de carga para los archivos php que hagan procesos.
 Se puede reutilizar en donde se necesite:

 Archivo para creación de trámite: -->

 <!-- ---------------------------------------------------------------------------------------------------------------------->
<style>

        .card{
            background-image: url("assets/img/logobnb.png") !important;
            background-size: 15em !important;
            background-repeat: no-repeat !important;
            background-position: 120% 230% !important;
        }
        .custom-loader {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: conic-gradient(
                #022F55 0% 25%,
                #0F5699 25% 50%,
                #4DA6FF 50% 75%,
                #66CC99 75% 100%
            );
            animation: spin 2s linear infinite;
            margin: 0 auto;
            position: relative;
        }

        .custom-loader::before {
            content: '';
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            bottom: 10px;
            background: white;
            border-radius: 50%;
            box-shadow: 0 0 20px rgba(2, 47, 85, 0.1);
        }

        .custom-loader::after {
            content: '';
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            background: linear-gradient(135deg, #022F55, #0F5699);
            border-radius: 50%;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .progress-bar {
            animation: progress-animation 4s ease-in-out;
        }

        @keyframes progress-animation {
            0% { width: 0%; }
            30% { width: 40%; }
            70% { width: 80%; }
            100% { width: 100%; }
        }

        .card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            border: none;
        }

        .loader-container {
            position: relative;
            padding: 20px;
        }

        .pulse-icon {
            animation: pulse-scale 2s infinite;
        }

        @keyframes pulse-scale {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="container-fluid border  d-flex align-items-center justify-content-center" style="min-height: 100%;">
        <div class="card contenedor shadow-lg border-0">
            <div class="card-header text-center text-white" style="background: linear-gradient(135deg, #022F55 0%, #0F5699 100%); border-radius: 0.5rem 0.5rem 0 0;">
                <h4 class="mb-0 fw-bold">
                    <i class="bi bi-file-earmark-plus pulse-icon me-2"></i>
                    Creando Trámite
                </h4>
            </div>
            <div class="card-body text-center py-5">
                <!-- Loader personalizado -->
                <div class="loader-container mb-4">
                    <div class="custom-loader"></div>
                    <!-- <img src="assets/img/logobnb.png" alt="logotipo de Neiva"> -->
                </div>
                
                <h5 class="text-dark mb-3">Procesando información...</h5>
                <p class="text-muted mb-4">Por favor espere mientras se crea el trámite y se organizan los documentos.</p>
                
                <!-- Barra de progreso animada -->
                <div class="progress mb-3" style="height: 8px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         style="background: linear-gradient(90deg, #022F55 0%, #0F5699 50%, #4DA6FF 100%);" 
                         role="progressbar" 
                         aria-valuenow="100" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                    </div>
                </div>
                
                <!-- Estados del proceso -->
                <div class="row text-center">
                    <div class="col-4">
                        <div class="mb-2">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 1.2rem;"></i>
                        </div>
                        <small class="text-muted">Datos guardados</small>
                    </div>
                    <div class="col-4">
                        <div class="mb-2">
                            <div class="spinner-border text-primary" style="width: 1.2rem; height: 1.2rem;"></div>
                        </div>
                        <small class="text-muted">Subiendo archivos</small>
                    </div>
                    <div class="col-4">
                        <div class="mb-2">
                            <i class="bi bi-clock text-secondary" style="font-size: 1.2rem;"></i>
                        </div>
                        <small class="text-muted">Finalizando</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.onload = function () {
            // Mostrar progreso paso a paso
            setTimeout(() => {
                // Cambiar segundo icono a completado
                document.querySelector('.col-4:nth-child(2) .spinner-border').outerHTML = '<i class="bi bi-check-circle-fill text-success" style="font-size: 1.2rem;"></i>';
                document.querySelector('.col-4:nth-child(2) small').textContent = 'Archivos subidos';
            }, 2000);

            setTimeout(() => {
                // Cambiar tercer icono a completado
                document.querySelector('.col-4:nth-child(3) i').className = 'bi bi-check-circle-fill text-success';
                document.querySelector('.col-4:nth-child(3) small').textContent = 'Completado';
            }, 3500);

            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: '¡Trámite Creado!',
                    text: 'El trámite ha sido creado correctamente en la plataforma.',
                    confirmButtonColor: '#022F55',
                    confirmButtonText: 'Continuar',
                    background: '#ffffff',
                    customClass: {
                        title: 'text-dark fw-bold',
                        confirmButton: 'btn-lg px-4'
                    },
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    }
                }).then(() => {
                    window.location.href = '#';
                });
            }, 4000);
        };
    </script>
<!-- ------------------------------------------------------------------------------------------------------------------------ -->



<!-- Archivo para asignación de trámite: -->

<!-- ----------------------------------------------------------------------------------------------------------------------------- -->
<!-- <div class="container-fluid d-flex align-items-center justify-content-center" style="min-height: 70vh;">
        <div class="card shadow-lg border-0" style="max-width: 500px; width: 100%;">
            <div class="card-header text-center text-white" style="background: linear-gradient(135deg, #022F55 0%, #0F5699 100%); border-radius: 0.5rem 0.5rem 0 0;">
                <h4 class="mb-0 fw-bold">
                    <i class="bi bi-gear-fill me-2"></i>
                    Procesando Asignación
                </h4>
            </div>
            <div class="card-body text-center py-5">
                ----- Loader personalizado ------
                <div class="loader-container mb-4">
                    <div class="custom-loader"></div>
                </div>
                
                <h5 class="text-dark mb-3">Asignando trámite...</h5>
                <p class="text-muted mb-4">Por favor espere mientras se procesa la asignación del trámite.</p>
                
                ------ Barra de progreso animada ------
                <div class="progress mb-3" style="height: 8px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         style="background: linear-gradient(90deg, #022F55 0%, #0F5699 50%, #4DA6FF 100%);" 
                         role="progressbar" 
                         aria-valuenow="100" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                    </div>
                </div>
                
                <div class="d-flex justify-content-center align-items-center">
                    <div class="spinner-border text-primary me-2" role="status" style="width: 1.2rem; height: 1.2rem;">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <small class="text-muted">Finalizando proceso...</small>
                </div>
            </div>
        </div>
    </div> -->

    <!-- <style>
        .custom-loader {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: conic-gradient(
                #022F55 0% 25%,
                #0F5699 25% 50%,
                #4DA6FF 50% 75%,
                #66CC99 75% 100%
            );
            animation: spin 2s linear infinite;
            margin: 0 auto;
            position: relative;
        }

        .custom-loader::before {
            content: '';
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            bottom: 10px;
            background: white;
            border-radius: 50%;
            box-shadow: 0 0 20px rgba(2, 47, 85, 0.1);
        }

        .custom-loader::after {
            content: '';
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            background: linear-gradient(135deg, #022F55, #0F5699);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .progress-bar {
            animation: progress-animation 3s ease-in-out;
        }

        @keyframes progress-animation {
            0% { width: 0%; }
            50% { width: 70%; }
            100% { width: 100%; }
        }

        .card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }

        .loader-container {
            position: relative;
            padding: 20px;
        }
    </style> -->

    <!-- <script>
        // Auto-refresh para procesar el trámite después de mostrar el loader
        setTimeout(function() {
            // El procesamiento real ocurre aquí
            procesarAsignacion();
        }, 2000);

        function procesarAsignacion() {
            // Mostrar mensaje de finalización
            setTimeout(function() {
                // SweetAlert con colores del dashboard
                Swal.fire({
                    icon: 'success',
                    title: '¡Trámite Asignado!',
                    text: 'El trámite ha sido asignado correctamente.',
                    confirmButtonColor: '#022F55',
                    confirmButtonText: 'Continuar',
                    background: '#ffffff',
                    customClass: {
                        title: 'text-dark fw-bold',
                        confirmButton: 'btn-lg px-4'
                    },
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    }
                }).then(() => {
                    window.location.href = 'index.php?page=tramites/consultar_tramite';
                });
            }, 1000);
        }
    </script> -->
<!-- --------------------------------------------------------------------------------------------------------------------------------- -->
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Animate.css para animaciones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
