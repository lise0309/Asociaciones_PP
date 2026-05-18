<?php
/**
 * NAVBAR — PP Bienes Raíces
 * views/layouts/navbar.php
 */
$phpSelf     = $_SERVER['PHP_SELF'] ?? '';
$paginaActual = basename($phpSelf);
?>
<header class="navbar" id="navbar">
  <div class="navbar-inner container">

    <a href="/Asociaciones_PP/index.php" class="nav-logo">
      <div class="nav-logo-premium">
        <img src="/Asociaciones_PP/assets/img/Logo.jpeg" alt="PP Bienes Raíces" class="nav-logo-img">
        <div class="nav-logo-glow"></div>
      </div>
      <div class="nav-logo-text">
        <strong>PP</strong>
        <span>Bienes Raíces</span>
      </div>
    </a>

    <nav class="nav-links" id="nav-links" aria-label="Menú principal">

      <a href="/Asociaciones_PP/index.php"
         class="nav-link <?= $paginaActual === 'index.php' ? 'active' : '' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
          <path d="M9.293 2.293a1 1 0 011.414 0l7 7A1 1 0 0117 11h-1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3a1 1 0 00-1-1H9a1 1 0 00-1 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6H3a1 1 0 01-.707-1.707l7-7z"/>
        </svg>
        Inicio
      </a>

      <a href="/Asociaciones_PP/views/mapa.php"
         class="nav-link <?= $paginaActual === 'mapa.php' ? 'active' : '' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
          <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd"/>
        </svg>
        Nuestras propiedades
      </a>

      <a href="#agentes" class="nav-link">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
          <path d="M10 9a3 3 0 100-6 3 3 0 000 6zM6 8a2 2 0 11-4 0 2 2 0 014 0zM1.49 15.326a.78.78 0 01-.358-.442 3 3 0 014.308-3.516 6.484 6.484 0 00-1.905 3.959c-.023.222-.014.442.025.654a4.97 4.97 0 01-2.07-.655zM16.44 15.98a4.97 4.97 0 002.07-.654.78.78 0 00.357-.442 3 3 0 00-4.308-3.517 6.484 6.484 0 011.882 3.96 4.84 4.84 0 01-.001.653zM14 8a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        Agentes
      </a>

    </nav>

    <button class="nav-hamburger" id="navHamburger" aria-label="Abrir menú" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>

  </div>
</header>

<script>
(function(){
  const navbar    = document.getElementById('navbar');
  const hamburger = document.getElementById('navHamburger');
  const navLinks  = document.getElementById('nav-links');
  window.addEventListener('scroll', () => navbar?.classList.toggle('scrolled', window.scrollY > 20));
  hamburger?.addEventListener('click', () => {
    const open = hamburger.classList.toggle('open');
    navLinks?.classList.toggle('open', open);
    hamburger.setAttribute('aria-expanded', String(open));
  });
})();
</script>