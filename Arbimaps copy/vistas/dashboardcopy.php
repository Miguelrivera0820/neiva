<style>
  .container-fluid-dos {
    position: relative;
    overflow: hidden;
    min-height: 100%;
  }

  .bg-carousel {
    position: absolute;
    inset: 0;
    z-index: 0;
  }

  .bg-slide {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    opacity: 0;
    transform: scale(1);
    transition: opacity 1s ease-in-out, transform 7s ease-in-out;
    will-change: transform, opacity;
  }

  .bg-slide.active {
    opacity: 1;
    transform: scale(1.12);
  }

  .bg-slide:nth-child(1) {
    background-image: url('../imagen/BG_1.webp');
    background-position: -10% 50%;
  }

  .bg-slide:nth-child(2) {
    background-image: url('../imagen/BG_3.webp');
    background-position: center;
  }

  .bg-slide:nth-child(3) {
    background-image: url('../imagen/Neiva_1.webp');
    background-position: center;
  }
  
  .bg-slide:nth-child(4) {
    background-image: url('../imagen/NP.webp');
    background-position: center;
  }

  .container-fluid-dos::before {
    content: "";
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.2);
    z-index: 1;
  }

  .container-fluid-dos .container {
    position: relative;
    z-index: 2;
  }

  .welcome-hero {
    background-image: url('../imagen/logobnb.webp');
    background-repeat: no-repeat;
    background-size: 6rem;
    background-position: 102% 90%;
    background-color: #0A2C1B;
    /* height: 25vh; */
    position: relative;
    animation: slideInLeft 1.3s ease-out forwards;
    opacity: 0;
    transform: translateX(-50px);
  }

  .welcome-hero::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: #0A2C1B;
    border-radius: inherit;
  }

  .welcome-hero>div {
    position: relative;
    z-index: 2;
  }

  .text-primary {
    color: #002F55 !important;
  }

  .quick-card:hover {
    background-color: #002F5533;
    transition: 0.3s;
  }

  @keyframes slideInLeft {
    0% {
      opacity: 0;
      transform: translateX(-50px);
    }

    100% {
      opacity: 1;
      transform: translateX(0);
    }
  }
</style>

<div class="container-fluid container-fluid-dos p-2 h-100 rounded-4 ">

  <div class="bg-carousel">
    <div class="bg-slide"></div>
    <div class="bg-slide"></div>
    <div class="bg-slide"></div>
    <div class="bg-slide"></div>
  </div>

  <div class="container d-flex justify-content-center align-items-end h-100 p-0">
    <div class="welcome-hero rounded-4 d-flex  align-items-start p-4 px-5 rounded" >
      <div class="text-white text-start">
        <h4 class="fw-bold" style="color: #A8CF8A">¡Bienvenido!</h4>
        <p style="font-size: 0.8em; color:#C2D4CA">Estamos listos para ayudarte a seguir trabajando.</p>

        <a class="btn fw-bold bg-white me-2 px-4 rounded-3" style="color: #0A2C1B;" href="index.php?page=Perfil/editar_perfil">
          Mi Perfil
        </a>
        <a class="btn btn-outline-light px-4 rounded-3" href="index.php?page=tramites/dashboard">
          Trámites
        </a>
      </div>
    </div>
  </div>

</div>

<script>
  const slides = document.querySelectorAll('.bg-slide');
  let currentSlide = 0;

  function activateSlide(index) {
    slides.forEach(slide => slide.classList.remove('active'));
    slides[index].classList.add('active');
  }

  function changeSlide() {
    currentSlide = (currentSlide + 1) % slides.length;
    activateSlide(currentSlide);
  }

  window.addEventListener('load', () => {
    setTimeout(() => {
      activateSlide(0);
    }, 100);
  });

  setInterval(changeSlide, 7000);
</script>
