<?php
/**
 * NAVBAR - PP Bienes Raíces
 * views/layouts/navbar.php
 * Versión Premium - Logo más grande
 */
?>

<header class="navbar" id="navbar">
  <div class="navbar-inner container">

    <!-- Logo más grande y premium -->
    <a href="index.php" class="nav-logo">
      <div class="nav-logo-premium">
        <img src="assets/img/logo.jpeg" alt="PP Bienes Raíces" class="nav-logo-img">
        <div class="nav-logo-glow"></div>
      </div>
      <div class="nav-logo-text">
        <strong>PP</strong>
        <span>Bienes Raíces</span>
      </div>
    </a>

    <!-- Menú de navegación premium -->
    <nav class="nav-links" id="nav-links" aria-label="Menú principal">
      <a href="index.php" class="nav-link active">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
          <path d="M9.293 2.293a1 1 0 011.414 0l7 7A1 1 0 0117 11h-1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3a1 1 0 00-1-1H9a1 1 0 00-1 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6H3a1 1 0 01-.707-1.707l7-7z"/>
        </svg>
        Inicio
      </a>
      <a href="views/propiedades/listado.php" class="nav-link">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
          <path d="M10.707 2.293a1 1 0 00-1.414 0l-5 5a1 1 0 001.414 1.414L9 5.414V13a1 1 0 102 0V5.414l3.293 3.293a1 1 0 001.414-1.414l-5-5z"/>
          <path d="M5 15a1 1 0 100 2h10a1 1 0 100-2H5z"/>
        </svg>
        Propiedades
      </a>
      <a href="views/propiedades/listado.php?negocio=venta" class="nav-link">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
          <path fill-rule="evenodd" d="M2.5 10a.75.75 0 01.75-.75h13.5a.75.75 0 010 1.5H3.25A.75.75 0 012.5 10z" clip-rule="evenodd"/>
          <path fill-rule="evenodd" d="M12.5 4a.75.75 0 01.75-.75h3a.75.75 0 01.75.75v11a.75.75 0 01-.75.75h-3a.75.75 0 010-1.5h2.25v-9.5H13.25a.75.75 0 01-.75-.75zM5.5 4a.75.75 0 01.75-.75h3a.75.75 0 010 1.5H6.75v9.5h2.5a.75.75 0 010 1.5h-3a.75.75 0 01-.75-.75v-11z" clip-rule="evenodd"/>
        </svg>
        En venta
      </a>
      <a href="views/propiedades/listado.php?negocio=alquiler" class="nav-link">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
          <path d="M10 2a1 1 0 00-1 1v1a1 1 0 002 0V3a1 1 0 00-1-1zM4 4h.01a1 1 0 000 2H4a1 1 0 010-2zM16 4h.01a1 1 0 000 2H16a1 1 0 010-2zM3 8a1 1 0 011-1h12a1 1 0 011 1v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/>
          <path d="M8 12a1 1 0 100-2 1 1 0 000 2zM12 12a1 1 0 100-2 1 1 0 000 2z"/>
        </svg>
        En alquiler
      </a>
      <a href="#agentes" class="nav-link">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
          <path d="M10 9a3 3 0 100-6 3 3 0 000 6zM6 8a2 2 0 11-4 0 2 2 0 014 0zM1.49 15.326a.78.78 0 01-.358-.442 3 3 0 014.308-3.516 6.484 6.484 0 00-1.905 3.959c-.023.222-.014.442.025.654a4.97 4.97 0 01-2.07-.655zM16.44 15.98a4.97 4.97 0 002.07-.654.78.78 0 00.357-.442 3 3 0 00-4.308-3.517 6.484 6.484 0 011.882 3.96 4.84 4.84 0 01-.001.653zM14 8a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        Agentes
      </a>
      <a href="#contacto" class="nav-link">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
          <path d="M3 4a2 2 0 00-2 2v1.161l8.441 4.221a1.25 1.25 0 001.118 0L19 7.162V6a2 2 0 00-2-2H3z"/>
          <path d="M19 8.839l-7.77 3.885a2.75 2.75 0 01-2.46 0L1 8.839V14a2 2 0 002 2h14a2 2 0 002-2V8.839z"/>
        </svg>
        Contacto
      </a>
    </nav>

    <!-- Hamburguesa premium -->
    <button class="nav-hamburger" id="navHamburger" aria-label="Abrir menú" aria-expanded="false">
      <span></span>
      <span></span>
      <span></span>
    </button>

  </div>
</header>