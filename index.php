<?php
/**
 * INDEX — Asociaciones PP
 * Página principal del sistema
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Asociaciones PP — Plataforma líder en gestión de asociaciones de propietarios en El Salvador. Propiedades, terrenos y urbanizaciones exclusivas.">
  <title>Asociaciones PP | Inicio</title>

  <!-- CSS -->
  <link rel="stylesheet" href="assets/css/main.css">
  <link rel="stylesheet" href="assets/css/slider.css">
  <link rel="stylesheet" href="assets/css/footer.css">
</head>
<body>

  <!-- ══════════════════════════════════════
       NAVBAR
  ══════════════════════════════════════ -->
  <header class="navbar" id="navbar">
    <div class="navbar-inner container">

      <!-- Logo -->
      <a href="index.php" class="nav-logo" id="nav-logo">
        <div class="nav-logo-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
            <path d="M11.47 3.841a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.061l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 101.061 1.06l8.69-8.689z"/>
            <path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.432z"/>
          </svg>
        </div>
        <div class="nav-logo-text">
          <strong>Asociaciones PP</strong>
          <span>El Salvador</span>
        </div>
      </a>

      <!-- Nav links -->
      <nav class="nav-links" id="nav-links" aria-label="Menú principal">
        <a href="#" id="nav-inicio" class="nav-link active">Inicio</a>
        <a href="#" id="nav-asociaciones" class="nav-link">Asociaciones</a>
        <a href="#" id="nav-propiedades" class="nav-link">Propiedades</a>
        <a href="#" id="nav-servicios" class="nav-link">Servicios</a>
        <a href="#contacto" id="nav-contacto" class="nav-link">Contacto</a>
      </nav>

      <!-- Actions -->
      <div class="nav-actions">
        <a href="#" class="btn btn-outline-dark" id="nav-login">Iniciar Sesión</a>
        <a href="#" class="btn btn-primary" id="nav-registro">Registrarse</a>
        <!-- Hamburger -->
        <button class="nav-hamburger" id="navHamburger" aria-label="Abrir menú" aria-expanded="false">
          <span></span><span></span><span></span>
        </button>
      </div>

    </div>
  </header>

  <!-- ══════════════════════════════════════
       SLIDER HERO
  ══════════════════════════════════════ -->
  <?php include 'views/layouts/slider.php'; ?>

  <!-- ══════════════════════════════════════
       SECCIÓN: CARACTERÍSTICAS RÁPIDAS
  ══════════════════════════════════════ -->
  <section class="features-strip" id="features">
    <div class="container">
      <div class="features-grid">

        <div class="feature-card">
          <div class="feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
              <path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 117.5 0 3.75 3.75 0 01-7.5 0zM15.75 9.75a3 3 0 116 0 3 3 0 01-6 0zM2.25 9.75a3 3 0 116 0 3 3 0 01-6 0zM6.31 15.117A6.745 6.745 0 0112 12a6.745 6.745 0 016.709 7.498.75.75 0 01-.372.568A12.696 12.696 0 0112 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 01-.372-.568 6.787 6.787 0 011.019-4.38z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="feature-info">
            <h3>+5,000 Socios</h3>
            <p>Propietarios activos gestionados</p>
          </div>
        </div>

        <div class="feature-card">
          <div class="feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
              <path d="M11.47 3.841a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.061l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 101.061 1.06l8.69-8.689z"/>
              <path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.432z"/>
            </svg>
          </div>
          <div class="feature-info">
            <h3>+120 Asociaciones</h3>
            <p>Comunidades registradas activas</p>
          </div>
        </div>

        <div class="feature-card">
          <div class="feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
              <path fill-rule="evenodd" d="M12.516 2.17a.75.75 0 00-1.032 0 11.209 11.209 0 01-7.877 3.08.75.75 0 00-.722.515A12.74 12.74 0 002.25 9.75c0 5.942 4.064 10.933 9.563 12.348a.749.749 0 00.374 0c5.499-1.415 9.563-6.406 9.563-12.348 0-1.39-.223-2.73-.635-3.985a.75.75 0 00-.722-.516l-.143.001c-2.996 0-5.717-1.17-7.734-3.08zm3.094 8.016a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="feature-info">
            <h3>Gestión Legal</h3>
            <p>Respaldo jurídico y transparencia</p>
          </div>
        </div>

        <div class="feature-card">
          <div class="feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
              <path d="M10.5 18.75a.75.75 0 000 1.5h3a.75.75 0 000-1.5h-3z"/>
              <path fill-rule="evenodd" d="M8.625.75A3.375 3.375 0 005.25 4.125v15.75a3.375 3.375 0 003.375 3.375h6.75a3.375 3.375 0 003.375-3.375V4.125A3.375 3.375 0 0015.375.75h-6.75zM7.5 4.125C7.5 3.504 8.004 3 8.625 3H9.75v.375c0 .621.504 1.125 1.125 1.125h2.25c.621 0 1.125-.504 1.125-1.125V3h1.125c.621 0 1.125.504 1.125 1.125v15.75c0 .621-.504 1.125-1.125 1.125h-6.75A1.125 1.125 0 017.5 19.875V4.125z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="feature-info">
            <h3>App en Línea</h3>
            <p>Gestiona desde cualquier lugar</p>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ══════════════════════════════════════
       FOOTER
  ══════════════════════════════════════ -->
  <?php include 'views/layouts/footer.php'; ?>

  <!-- ══════════════════════════════════════
       SCRIPTS GLOBALES
  ══════════════════════════════════════ -->
  <script>
  // ── Navbar scroll effect ──
  (function () {
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
      navbar.classList.toggle('scrolled', window.scrollY > 50);
    });

    // ── Mobile hamburger ──
    const btn   = document.getElementById('navHamburger');
    const links = document.getElementById('nav-links');
    btn.addEventListener('click', () => {
      const open = links.classList.toggle('open');
      btn.setAttribute('aria-expanded', open);
      btn.classList.toggle('open', open);
    });
  })();
  </script>

</body>
</html>
