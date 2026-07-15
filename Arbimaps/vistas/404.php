<style>
    .error-container {
        min-height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        background-color: #002F55;
    }

    .error-content {
        text-align: center;
        color: #FFFFFF;
        z-index: 2;
        animation: fadeIn 1s ease-in;
    }

    .error-number {
        font-size: 10rem;
        font-weight: bold;
        line-height: 1;
        margin-bottom: 1rem;
        text-shadow: 0 0 30px rgba(255, 255, 255, 0.3);
        animation: float 3s ease-in-out infinite;
    }

    .error-title {
        font-size: 2rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        letter-spacing: 1px;
    }

    .error-description {
        font-size: 1.1rem;
        margin-bottom: 2.5rem;
        opacity: 0.9;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }

    .floating-shapes {
        position: absolute;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: 1;
    }

    .shape {
        position: absolute;
        background-color: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        animation: float-shapes 20s infinite ease-in-out;
    }

    .shape1 {
        width: 300px;
        height: 300px;
        top: 10%;
        left: -150px;
        animation-delay: 0s;
    }

    .shape2 {
        width: 200px;
        height: 200px;
        bottom: 20%;
        right: -100px;
        animation-delay: 2s;
    }

    .shape3 {
        width: 150px;
        height: 150px;
        top: 60%;
        left: 10%;
        animation-delay: 4s;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-20px);
        }
    }

    @keyframes float-shapes {

        0%,
        100% {
            transform: translate(0, 0) rotate(0deg);
        }

        25% {
            transform: translate(50px, -50px) rotate(90deg);
        }

        50% {
            transform: translate(0, -100px) rotate(180deg);
        }

        75% {
            transform: translate(-50px, -50px) rotate(270deg);
        }
    }

    @media (max-width: 768px) {
        .error-number {
            font-size: 6rem;
        }

        .error-title {
            font-size: 1.5rem;
        }

        .error-description {
            font-size: 1rem;
        }
    }
</style>

<body>
    <div class="error-container rounded-5">
        <div class="floating-shapes">
            <div class="shape shape1"></div>
            <div class="shape shape2"></div>
            <div class="shape shape3"></div>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="error-content">
                        <div class="error-number">404</div>
                        <h1 class="error-title">Página no encontrada</h1>
                        <p class="error-description">
                            Lo sentimos, la página que estás buscando no existe o ha sido movida.
                        </p>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>