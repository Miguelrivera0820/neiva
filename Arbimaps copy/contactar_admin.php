<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contactar Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <!-- Fuente Open Sans-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --color-primary: #002F55;
            --color-white: #ffffff;
        }

        body {
            background: linear-gradient(135deg, var(--color-primary) 0%, #004d7a 100%);
            background-image: url(../imagenes/panoramica_mj.png) !important;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: "Open Sans", sans-serif;
            background-size: cover;
            background-position: bottom;
            background-attachment: fixed;
            animation: mover 7s linear infinite;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(1, 27, 48, 0.675);
            z-index: 1;
            pointer-events: none;
        }


        @keyframes mover {
            0% {
                background-position: 0 0;
            }

            50% {
                background-position: 20px 0;
            }

            100% {
                background-position: 0 0;
            }
        }

        .contact-container {
            max-width: 1200px;
            width: 100%;
            z-index: 1;
        }

        .contact-card {
            background: var(--color-white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .contact-header {
            background: var(--color-primary);
            color: var(--color-white);
            background-image: url(assets/img/NBN.png), url(assets/img/ArgaLogo4.png);
            background-repeat: no-repeat;
            background-position: 90% 50%, 10% 50%;
            background-size: 8em,10em;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        /* .contact-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(210, 34, 34, 0.1) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        } */

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        .contact-header h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .contact-header p {
            font-size: 1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .contact-icon {
            width: 80px;
            height: 80px;
            background: var(--color-white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
            color: var(--color-primary);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 1;
        }

        .contact-body {
            padding: 40px 30px;
        }

        .form-label {
            color: var(--color-primary);
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .form-control,
        .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 0.2rem rgba(0, 47, 85, 0.15);
        }

        .form-control::placeholder {
            color: #999;
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }

        .btn-send {
            background: var(--color-primary);
            color: var(--color-white);
            border: none;
            padding: 14px 40px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 10px;
        }

        .btn-send:hover {
            background: #001f3a;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 47, 85, 0.3);
        }

        .btn-send:active {
            transform: translateY(0);
        }

        .info-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .info-card:hover {
            border-color: var(--color-primary);
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 47, 85, 0.1);
        }

        .info-card-icon {
            width: 50px;
            height: 50px;
            background: var(--color-primary);
            color: var(--color-white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5rem;
        }

        .info-card h5 {
            color: var(--color-primary);
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .info-card p {
            color: #6c757d;
            font-size: 0.85rem;
            margin: 0;
        }

        .alert-custom {
            background: #d4edda;
            border: 2px solid #28a745;
            border-radius: 10px;
            padding: 15px;
            display: none;
            margin-bottom: 20px;
        }

        .alert-custom.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .char-counter {
            font-size: 0.85rem;
            color: #6c757d;
            text-align: right;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .contact-header h2 {
                font-size: 1.5rem;
            }

            .contact-body {
                padding: 30px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="contact-container ">
        <div class="contact-card" style="z-index: 4250;">
            <!-- Header -->
            <div class="contact-header py-4">
                <div class="contact-icon">
                    <i class="bi bi-headset"></i>
                </div>
                <h2>Contactar Administrador</h2>
                <p>¿Necesitas ayuda? Estamos aquí para asistirte</p>
            </div>

            <!-- Body -->
            <div class="contact-body">
                <!-- Alert de éxito -->
                <div id="successAlert" class="alert-custom">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill text-success me-2" style="font-size: 1.5rem;"></i>
                        <div>
                            <strong>¡Mensaje enviado con éxito!</strong>
                            <p class="mb-0 small">El administrador te responderá pronto.</p>
                        </div>
                    </div>
                </div>

                <!-- Info Cards
                <div class="info-cards">
                    <div class="info-card">
                        <div class="info-card-icon">
                            <i class="bi bi-clock"></i>
                        </div>
                        <h5>Tiempo de Respuesta</h5>
                        <p>24-48 horas</p>
                    </div>
                    <div class="info-card">
                        <div class="info-card-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h5>Soporte Garantizado</h5>
                        <p>Atención prioritaria</p>
                    </div>
                    <div class="info-card">
                        <div class="info-card-icon">
                            <i class="bi bi-envelope"></i>
                        </div>
                        <h5>Correo Directo</h5>
                        <p>admin@sistema.com</p>
                    </div>
                </div> -->

                <!-- Formulario -->
                <form id="contactForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label">
                                <i class="bi bi-person me-1"></i>Nombre Completo *
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                placeholder="Ej: Juan Pérez" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope me-1"></i>Correo Electrónico *
                            </label>
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="ejemplo@correo.com" required>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="telefono" class="form-label">
                                <i class="bi bi-telephone me-1"></i>Teléfono
                            </label>
                            <input type="tel" class="form-control" id="telefono" name="telefono"
                                placeholder="Ej: 3001234567">
                        </div>

                        <!-- <div class="form-floating mb-3 ">
                            <input type="email" class="form-control " id="floatingInput" placeholder="name@example.com">
                            <label for="floatingInput">Email address</label>
                        </div> -->

                        <div class="col-md-6 mb-3">
                            <label for="asunto" class="form-label">
                                <i class="bi bi-tag me-1"></i>Asunto *
                            </label>
                            <select class="form-select" id="asunto" name="asunto" required>
                                <option value="">Selecciona un asunto</option>
                                <option value="soporte">Soporte Técnico</option>
                                <option value="consulta">Consulta General</option>
                                <option value="sugerencia">Sugerencia</option>
                                <option value="reporte">Reportar Problema</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="mensaje" class="form-label">
                            <i class="bi bi-chat-dots me-1"></i>Mensaje *
                        </label>
                        <textarea class="form-control" id="mensaje" name="mensaje"
                            placeholder="Escribe tu mensaje aquí..."
                            maxlength="500" required></textarea>
                        <div class="char-counter">
                            <span id="charCount">0</span>/500 caracteres
                        </div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="terminos" required>
                        <label class="form-check-label" for="terminos">
                            Acepto que mi información sea utilizada para responder mi consulta
                        </label>
                    </div>

                    <button type="submit" class="btn btn-send">
                        <i class="bi bi-send-fill me-2"></i>Enviar Mensaje
                    </button>
                </form>

                <a class="btn d-flex aling-items-center justify-content-center mt-3" href="../index.php" >
                    <i class="bi bi-person-circle me-2"></i>Volver e inicar sesión.
                </a>
            </div>


        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Contador de caracteres
        const mensajeTextarea = document.getElementById('mensaje');
        const charCount = document.getElementById('charCount');

        mensajeTextarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });

        // Manejo del formulario
        const contactForm = document.getElementById('contactForm');
        const successAlert = document.getElementById('successAlert');

        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Recopilar datos del formulario
            const formData = {
                nombre: document.getElementById('nombre').value,
                email: document.getElementById('email').value,
                telefono: document.getElementById('telefono').value,
                asunto: document.getElementById('asunto').value,
                mensaje: document.getElementById('mensaje').value
            };

            // Aquí harías la petición AJAX a tu servidor
            console.log('Datos del formulario:', formData);

            // Simulación de envío exitoso
            successAlert.classList.add('show');
            contactForm.reset();
            charCount.textContent = '0';

            // Ocultar alerta después de 5 segundos
            setTimeout(() => {
                successAlert.classList.remove('show');
            }, 5000);

        });

        // Validación en tiempo real del email
        const emailInput = document.getElementById('email');
        emailInput.addEventListener('blur', function() {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(this.value) && this.value !== '') {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    </script>
</body>

</html>