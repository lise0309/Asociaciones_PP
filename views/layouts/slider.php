<?php
/**
 * SLIDER / HERO CAROUSEL
 * Asociaciones PP — views/layouts/slider.php
 */
?>

<section class="hero-slider" id="hero-slider" aria-label="Slider principal">

  <!-- Progress bar -->
  <div class="slider-progress" id="sliderProgress"></div>

  <!-- Slides track -->
  <div class="slider-track" id="sliderTrack">

    <!-- ── SLIDE 1 ── -->
    <div class="slide active" id="slide-1">
      <div class="slide-bg" style="background-image: url('assets/img/slider_1.png');"></div>
      <div class="slide-overlay"></div>
      <div class="slide-shape"></div>
      <div class="slide-content">
        <div class="slide-inner">
          <span class="slide-badge">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
              <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
            </svg>
            Invierte con Confianza
          </span>

          <h1 class="slide-title">
            Tu <span>Propiedad Ideal</span><br>en El Salvador
          </h1>

          <p class="slide-subtitle">
            Descubre las mejores asociaciones de propietarios con terrenos, casas y proyectos de inversión en las zonas más exclusivas del país.
          </p>

          <div class="slide-actions">
            <a href="#asociaciones" class="btn btn-primary btn-lg" id="slide1-cta-primary">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                <path d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
              </svg>
              Ver Asociaciones
            </a>
            <a href="#contacto" class="btn btn-outline btn-lg" id="slide1-cta-secondary">
              Más Información
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- ── SLIDE 2 ── -->
    <div class="slide" id="slide-2">
      <div class="slide-bg" style="background-image: url('assets/img/slider_2.png');"></div>
      <div class="slide-overlay"></div>
      <div class="slide-shape"></div>
      <div class="slide-content">
        <div class="slide-inner">
          <span class="slide-badge">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
              <path d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
            </svg>
            Proyectos Agrícolas
          </span>

          <h1 class="slide-title">
            <span>Tierra Fértil</span> para<br>Grandes Proyectos
          </h1>

          <p class="slide-subtitle">
            Amplia cartera de terrenos agrícolas y fincas productivas. Invierte en el campo salvadoreño con garantías y respaldo legal.
          </p>

          <div class="slide-actions">
            <a href="#proyectos" class="btn btn-primary btn-lg" id="slide2-cta-primary">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                <path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 117.5 0 3.75 3.75 0 01-7.5 0zM15.75 9.75a3 3 0 116 0 3 3 0 01-6 0zM2.25 9.75a3 3 0 116 0 3 3 0 01-6 0zM6.31 15.117A6.745 6.745 0 0112 12a6.745 6.745 0 016.709 7.498.75.75 0 01-.372.568A12.696 12.696 0 0112 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 01-.372-.568 6.787 6.787 0 011.019-4.38z" clip-rule="evenodd"/>
              </svg>
              Explorar Proyectos
            </a>
            <a href="#mapa" class="btn btn-outline btn-lg" id="slide2-cta-secondary">
              Ver Mapa
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- ── SLIDE 3 ── -->
    <div class="slide" id="slide-3">
      <div class="slide-bg" style="background-image: url('assets/img/slider_3.png');"></div>
      <div class="slide-overlay"></div>
      <div class="slide-shape"></div>
      <div class="slide-content">
        <div class="slide-inner">
          <span class="slide-badge">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12.75 12.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM7.5 15.75a.75.75 0 100-1.5.75.75 0 000 1.5zM8.25 17.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM9.75 15.75a.75.75 0 100-1.5.75.75 0 000 1.5zM10.5 17.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12 15.75a.75.75 0 100-1.5.75.75 0 000 1.5zM12.75 17.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM14.25 15.75a.75.75 0 100-1.5.75.75 0 000 1.5zM15 17.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM16.5 15.75a.75.75 0 100-1.5.75.75 0 000 1.5zM15 12.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM16.5 13.5a.75.75 0 100-1.5.75.75 0 000 1.5z"/>
              <path fill-rule="evenodd" d="M6.75 2.25A.75.75 0 017.5 3v1.5h9V3A.75.75 0 0118 3v1.5h.75a3 3 0 013 3v11.25a3 3 0 01-3 3H5.25a3 3 0 01-3-3V7.5a3 3 0 013-3H6V3a.75.75 0 01.75-.75zm13.5 9a1.5 1.5 0 00-1.5-1.5H5.25a1.5 1.5 0 00-1.5 1.5v7.5a1.5 1.5 0 001.5 1.5h13.5a1.5 1.5 0 001.5-1.5v-7.5z" clip-rule="evenodd"/>
            </svg>
            Urbanizaciones Exclusivas
          </span>

          <h1 class="slide-title">
            Residenciales de<br><span>Alto Nivel</span> y Calidad
          </h1>

          <p class="slide-subtitle">
            Urbanizaciones planificadas con seguridad 24/7, áreas verdes y todos los servicios. La mejor calidad de vida para tu familia.
          </p>

          <div class="slide-actions">
            <a href="#residenciales" class="btn btn-primary btn-lg" id="slide3-cta-primary">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm4.28 10.28a.75.75 0 000-1.06l-3-3a.75.75 0 10-1.06 1.06l1.72 1.72H8.25a.75.75 0 000 1.5h5.69l-1.72 1.72a.75.75 0 101.06 1.06l3-3z" clip-rule="evenodd"/>
              </svg>
              Ver Residenciales
            </a>
            <a href="#galeria" class="btn btn-outline btn-lg" id="slide3-cta-secondary">
              Ver Galería
            </a>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /slider-track -->

  <!-- ── Stats Floating Cards ── -->
  <div class="slider-stats" aria-hidden="true">
    <div class="stat-card">
      <div class="stat-number">+120</div>
      <div class="stat-label">Asociaciones</div>
    </div>
    <div class="stat-card">
      <div class="stat-number">+5K</div>
      <div class="stat-label">Propietarios</div>
    </div>
    <div class="stat-card">
      <div class="stat-number">15+</div>
      <div class="stat-label">Años de exp.</div>
    </div>
  </div>

  <!-- ── Navigation Arrows ── -->
  <button class="slider-arrow slider-arrow-prev" id="sliderPrev" aria-label="Slide anterior">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
    </svg>
  </button>

  <button class="slider-arrow slider-arrow-next" id="sliderNext" aria-label="Siguiente slide">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
    </svg>
  </button>

  <!-- ── Dots ── -->
  <div class="slider-dots" role="tablist" aria-label="Slides">
    <button class="slider-dot active" data-slide="0" role="tab" aria-selected="true"  aria-label="Slide 1" id="dot-0"></button>
    <button class="slider-dot"        data-slide="1" role="tab" aria-selected="false" aria-label="Slide 2" id="dot-1"></button>
    <button class="slider-dot"        data-slide="2" role="tab" aria-selected="false" aria-label="Slide 3" id="dot-2"></button>
  </div>

</section><!-- /hero-slider -->

<!-- ── Slider JavaScript ── -->
<script>
(function () {
  const track    = document.getElementById('sliderTrack');
  const slides   = track.querySelectorAll('.slide');
  const dots     = document.querySelectorAll('.slider-dot');
  const progress = document.getElementById('sliderProgress');
  const prevBtn  = document.getElementById('sliderPrev');
  const nextBtn  = document.getElementById('sliderNext');

  let current   = 0;
  let total     = slides.length;
  let autoTimer = null;
  const DELAY   = 5000;

  function goTo(index) {
    slides[current].classList.remove('active');
    dots[current].classList.remove('active');
    dots[current].setAttribute('aria-selected', 'false');

    current = (index + total) % total;

    slides[current].classList.add('active');
    dots[current].classList.add('active');
    dots[current].setAttribute('aria-selected', 'true');

    track.style.transform = `translateX(-${current * (100 / total)}%)`;

    // Reset progress bar animation
    progress.style.animation = 'none';
    progress.offsetHeight; // reflow
    progress.style.animation = `progressBar ${DELAY / 1000}s linear`;
  }

  function startAuto() {
    clearInterval(autoTimer);
    autoTimer = setInterval(() => goTo(current + 1), DELAY);
  }

  function stopAuto() {
    clearInterval(autoTimer);
  }

  // Arrow events
  prevBtn.addEventListener('click', () => { goTo(current - 1); startAuto(); });
  nextBtn.addEventListener('click', () => { goTo(current + 1); startAuto(); });

  // Dot events
  dots.forEach((dot, i) => {
    dot.addEventListener('click', () => { goTo(i); startAuto(); });
  });

  // Pause on hover
  const slider = document.getElementById('hero-slider');
  slider.addEventListener('mouseenter', stopAuto);
  slider.addEventListener('mouseleave', startAuto);

  // Keyboard navigation
  slider.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft')  { goTo(current - 1); startAuto(); }
    if (e.key === 'ArrowRight') { goTo(current + 1); startAuto(); }
  });

  // Touch / swipe support
  let touchStartX = 0;
  slider.addEventListener('touchstart', (e) => { touchStartX = e.touches[0].clientX; }, { passive: true });
  slider.addEventListener('touchend',   (e) => {
    const diff = touchStartX - e.changedTouches[0].clientX;
    if (Math.abs(diff) > 50) {
      goTo(diff > 0 ? current + 1 : current - 1);
      startAuto();
    }
  });

  // Init
  goTo(0);
  startAuto();
})();
</script>
