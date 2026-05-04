<?php
/**
 * INDEX — PP Bienes Raíces
 * Versión Premium Optimizada
 * Asociaciones Portillo Pocasangre
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="PP Bienes Raíces — La mejor experiencia inmobiliaria en El Salvador. Casas, apartamentos, terrenos y más.">
  <meta name="keywords" content="bienes raíces, El Salvador, propiedades, casas, apartamentos, terrenos">
  <meta name="author" content="PP Bienes Raíces">
  <meta property="og:title" content="PP Bienes Raíces | Inicio">
  <meta property="og:description" content="Encuentra la propiedad de tus sueños en El Salvador">
  <meta property="og:type" content="website">
  <title>PP Bienes Raíces | Propiedades de lujo en El Salvador</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,700&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Styles -->
  <link rel="stylesheet" href="assets/css/index.css">
  <link rel="stylesheet" href="assets/css/navbar.css">
  <link rel="stylesheet" href="assets/css/footer.css">
  
  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
</head>
<body>

  <!-- ═══════════════════════════════════════════════════════════
       NAVBAR — INCLUIDO DESDE ARCHIVO APARTE
  ═══════════════════════════════════════════════════════════ -->
  <?php include 'views/layouts/navbar.php'; ?>

  <!-- ═══════════════════════════════════════════════════════════
       LAYOUT PRINCIPAL
  ═══════════════════════════════════════════════════════════ -->
  <div class="page-layout">

    <!-- ───────────────────────────────────────────────────────────
         PANEL DE FILTROS
    ─────────────────────────────────────────────────────────── -->
    <aside class="filtros-panel" id="filtrosPanel">
      <div class="filtros-header">
        <h3 class="filtros-titulo">Filtros de búsqueda</h3>
        <button class="filtros-close" id="filtrosClose" aria-label="Cerrar filtros">×</button>
      </div>

      <!-- Modalidad -->
      <div class="filtro-grupo">
        <label class="filtro-label">Modalidad</label>
        <div class="filtro-pills" id="filtroModalidad">
          <button class="pill active" data-val="venta">Venta</button>
          <button class="pill" data-val="renta">Renta</button>
        </div>
      </div>

      <!-- Tipo de propiedad -->
      <div class="filtro-grupo">
        <label class="filtro-label">Tipo de propiedad</label>
        <select class="filtro-select" id="filtroTipo">
          <option value="">Todas</option>
          <option>Casa</option>
          <option>Apartamento</option>
          <option>Local comercial</option>
          <option>Terreno</option>
          <option>Finca</option>
          <option>Bodega</option>
        </select>
      </div>

      <!-- Ubicación -->
      <div class="filtro-grupo">
        <label class="filtro-label">Departamento</label>
        <select class="filtro-select" id="filtroCiudad">
          <option value="">Todos</option>
          <option>Ahuachapán</option>
          <option>Cabañas</option>
          <option>Chalatenango</option>
          <option>Cuscatlán</option>
          <option>La Libertad</option>
          <option>La Paz</option>
          <option>La Unión</option>
          <option>Morazán</option>
          <option>San Miguel</option>
          <option>San Salvador</option>
          <option>San Vicente</option>
          <option>Santa Ana</option>
          <option>Sonsonate</option>
          <option>Usulután</option>
        </select>
      </div>

      <!-- Sector -->
      <div class="filtro-grupo">
        <label class="filtro-label">Sector</label>
        <select class="filtro-select" id="filtroSector">
          <option value="">Todos</option>
          <option>Residencial</option>
          <option>Comercial</option>
          <option>Industrial</option>
          <option>Agrícola</option>
          <option>Turístico</option>
        </select>
      </div>

      <!-- Estado -->
      <div class="filtro-grupo">
        <label class="filtro-label">Estado del inmueble</label>
        <select class="filtro-select" id="filtroEstado">
          <option value="">Todos</option>
          <option>Nuevo</option>
          <option>Excelente estado</option>
          <option>Buen estado</option>
          <option>Para remodelar</option>
        </select>
      </div>

      <!-- Rango de precio -->
      <div class="filtro-grupo">
        <label class="filtro-label">Precio (USD)</label>
        <div class="filtro-precio-row">
          <input type="number" class="filtro-input" placeholder="Mínimo" id="precioMin" step="1000">
          <input type="number" class="filtro-input" placeholder="Máximo" id="precioMax" step="1000">
        </div>
      </div>

      <!-- Moneda -->
      <div class="filtro-grupo">
        <label class="filtro-label">Moneda</label>
        <div class="filtro-pills" id="filtroMoneda">
          <button class="pill active" data-val="usd">USD</button>
          <button class="pill" data-val="es">€ EUR</button>
          <button class="pill" data-val="fr">₿ BTC</button>
        </div>
      </div>

      <!-- Botones de acción -->
      <div class="filtro-actions">
        <button class="btn-buscar" onclick="buscarPropiedades()">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/>
          </svg>
          Buscar propiedades
        </button>
        <button class="btn-limpiar" onclick="limpiarFiltros()">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
            <path fill-rule="evenodd" d="M3.5 3.5a.75.75 0 011.06 0L10 8.94l5.44-5.44a.75.75 0 111.06 1.06L11.06 10l5.44 5.44a.75.75 0 11-1.06 1.06L10 11.06l-5.44 5.44a.75.75 0 01-1.06-1.06L8.94 10 3.5 4.56a.75.75 0 010-1.06z" clip-rule="evenodd"/>
          </svg>
          Limpiar filtros
        </button>
      </div>
    </aside>

    <!-- ───────────────────────────────────────────────────────────
         CONTENIDO PRINCIPAL
    ─────────────────────────────────────────────────────────── -->
    <main class="contenido-principal">

      <!-- HERO SECTION — Premium -->
      <section class="hero-section">
        <div class="hero-content">
          <div class="hero-texto">
            <span class="hero-badge">✦ Propiedades de lujo en El Salvador</span>
            <h1 class="hero-title">
              Invierte en<br>
              <em>El Salvador hoy</em>
            </h1>
            <p class="hero-sub">
              Descubre las propiedades más exclusivas del mercado inmobiliario salvadoreño. 
              Terrenos, fincas, residencias y locales comerciales con los mejores precios.
            </p>
            <div class="hero-stats">
              <div class="hero-stat">
                <span class="stat-num" data-count="868">0</span>
                <span class="stat-lbl">Propiedades activas</span>
              </div>
              <div class="hero-stat">
                <span class="stat-num" data-count="14">0</span>
                <span class="stat-lbl">Departamentos</span>
              </div>
              <div class="hero-stat">
                <span class="stat-num" data-count="120">0</span>
                <span class="stat-lbl">Agentes expertos</span>
              </div>
              <div class="hero-stat">
                <span class="stat-num" data-count="3200">0</span>
                <span class="stat-lbl">Ventas realizadas</span>
              </div>
            </div>
          </div>

          <!-- Banner del mapa -->
          <div class="mapa-banner">
            <div class="mapa-banner-inner">
              <div class="mapa-banner-left">
                <div class="mapa-ico">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                  </svg>
                </div>
                <div class="mapa-info">
                  <strong>Explora en mapa interactivo</strong>
                  <span>Visualiza todas las propiedades por ubicación real</span>
                </div>
              </div>
              <a href="views/propiedades/mapa.php" class="btn-abrir-mapa">
                Abrir mapa
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                  <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/>
                </svg>
              </a>
            </div>
          </div>
        </div>
      </section>

      <!-- BARRA DE RESULTADOS -->
      <div class="resultados-bar">
        <div class="resultados-left">
          <span class="resultados-count">
            <strong id="totalCount">868</strong> propiedades encontradas
          </span>
          <button class="btn-filtros-mobile" id="btnFiltrosMobile" aria-label="Abrir filtros">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
              <path fill-rule="evenodd" d="M2.628 1.601C5.028 1.206 7.49 1 10 1s4.972.206 7.372.601a.75.75 0 01.628.74v2.288a2.25 2.25 0 01-.659 1.59l-4.682 4.683a2.25 2.25 0 00-.659 1.59v3.037c0 .684-.31 1.33-.844 1.757l-1.937 1.55A.75.75 0 018 18.25v-5.757a2.25 2.25 0 00-.659-1.591L2.659 6.22A2.25 2.25 0 012 4.628V2.34a.75.75 0 01.628-.74z" clip-rule="evenodd"/>
            </svg>
            Filtros
          </button>
        </div>
        <div class="resultados-orden">
          <span>Ordenar por:</span>
          <select class="orden-select" id="ordenSelect">
            <option value="recientes">Más recientes</option>
            <option value="menor-precio">Menor precio</option>
            <option value="mayor-precio">Mayor precio</option>
            <option value="destacadas">Destacadas</option>
          </select>
        </div>
      </div>

      <!-- GRID DE PROPIEDADES -->
      <div class="props-grid" id="propsGrid">
        <!-- Card 1 -->
        <article class="prop-card">
          <a href="views/propiedades/detalle.php?id=2342" class="prop-card-link">
            <div class="prop-img">
              <div class="prop-img-bg" style="background-image: url('assets/img/propiedades/finca-cafe.jpg');"></div>
              <div class="prop-img-ph" aria-label="Imagen de la propiedad">🏡</div>
              <div class="prop-tags">
                <span class="ptag ptag-venta">VENTA</span>
              </div>
              <span class="prop-codigo">#2342</span>
            </div>
            <div class="prop-body">
              <div class="prop-price">US$ 298,000 <small>venta</small></div>
              <h3 class="prop-titulo">Finca de Café con vivienda principal</h3>
              <div class="prop-loc">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd"/>
                </svg>
                Jayaque, La Libertad
              </div>
              <div class="prop-feats">
                <span>🚪 2 hab.</span>
                <span>🛁 2 baños</span>
                <span>📐 4.2 mz</span>
              </div>
            </div>
          </a>
        </article>

        <!-- Card 2 -->
        <article class="prop-card">
          <a href="views/propiedades/detalle.php?id=2343" class="prop-card-link">
            <div class="prop-img">
              <div class="prop-img-ph">🌿</div>
              <div class="prop-tags">
                <span class="ptag ptag-venta">VENTA</span>
              </div>
              <span class="prop-codigo">#2343</span>
            </div>
            <div class="prop-body">
              <div class="prop-price">US$ 330,000 <small>venta</small></div>
              <h3 class="prop-titulo">Terreno residencial con vista panorámica</h3>
              <div class="prop-loc">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd"/>
                </svg>
                San José Villanueva, La Libertad
              </div>
              <div class="prop-feats">
                <span>📐 2,500 m²</span>
                <span>🌳 Vista montaña</span>
              </div>
            </div>
          </a>
        </article>

        <!-- Card 3 -->
        <article class="prop-card">
          <a href="views/propiedades/detalle.php?id=2341" class="prop-card-link">
            <div class="prop-img">
              <div class="prop-img-ph">🏖️</div>
              <div class="prop-tags">
                <span class="ptag ptag-venta">VENTA</span>
              </div>
              <span class="prop-codigo">#2341</span>
            </div>
            <div class="prop-body">
              <div class="prop-price">US$ 140,000 <small>venta</small></div>
              <h3 class="prop-titulo">Terreno frente al mar con acceso directo</h3>
              <div class="prop-loc">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd"/>
                </svg>
                La Libertad, La Libertad
              </div>
              <div class="prop-feats">
                <span>📐 1,200 m²</span>
                <span>🌊 Frente al mar</span>
              </div>
            </div>
          </a>
        </article>

        <!-- Card 4 -->
        <article class="prop-card">
          <a href="views/propiedades/detalle.php?id=2340" class="prop-card-link">
            <div class="prop-img">
              <div class="prop-img-ph">🏠</div>
              <div class="prop-tags">
                <span class="ptag ptag-renta">RENTA</span>
              </div>
              <span class="prop-codigo">#2340</span>
            </div>
            <div class="prop-body">
              <div class="prop-price">US$ 1,200 <small>/mes</small></div>
              <h3 class="prop-titulo">Casa residencial totalmente amueblada</h3>
              <div class="prop-loc">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd"/>
                </svg>
                Santa Tecla, La Libertad
              </div>
              <div class="prop-feats">
                <span>🚪 3 hab.</span>
                <span>🛁 2 baños</span>
                <span>📐 220 m²</span>
              </div>
            </div>
          </a>
        </article>

        <!-- Card 5 -->
        <article class="prop-card">
          <a href="views/propiedades/detalle.php?id=2339" class="prop-card-link">
            <div class="prop-img">
              <div class="prop-img-ph">🏢</div>
              <div class="prop-tags">
                <span class="ptag ptag-renta">RENTA</span>
              </div>
              <span class="prop-codigo">#2339</span>
            </div>
            <div class="prop-body">
              <div class="prop-price">US$ 2,400 <small>/mes</small></div>
              <h3 class="prop-titulo">Local comercial en zona céntrica</h3>
              <div class="prop-loc">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd"/>
                </svg>
                San Salvador, San Salvador
              </div>
              <div class="prop-feats">
                <span>📐 180 m²</span>
                <span>📍 Zona rosa</span>
              </div>
            </div>
          </a>
        </article>

        <!-- Card 6 (Destacada) -->
        <article class="prop-card prop-card-destacada">
          <a href="views/propiedades/detalle.php?id=2338" class="prop-card-link">
            <div class="prop-img">
              <div class="prop-img-ph">⭐</div>
              <div class="prop-tags">
                <span class="ptag ptag-venta">VENTA</span>
                <span class="ptag ptag-star">★ Destacada</span>
              </div>
              <span class="prop-codigo">#2338</span>
            </div>
            <div class="prop-body">
              <div class="prop-price">US$ 195,000 <small>venta</small></div>
              <h3 class="prop-titulo">Casa estilo colonial con jardín amplio</h3>
              <div class="prop-loc">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd"/>
                </svg>
                Santa Tecla, La Libertad
              </div>
              <div class="prop-feats">
                <span>🚪 4 hab.</span>
                <span>🛁 3 baños</span>
                <span>📐 320 m²</span>
              </div>
            </div>
          </a>
        </article>
      </div>

      <!-- PAGINACIÓN (placeholder) -->
      <div class="paginacion">
        <button class="page-btn active">1</button>
        <button class="page-btn">2</button>
        <button class="page-btn">3</button>
        <button class="page-btn">4</button>
        <span class="page-dots">...</span>
        <button class="page-btn">12</button>
      </div>

    </main>
  </div>

  <!-- ═══════════════════════════════════════════════════════════
       FOOTER
  ═══════════════════════════════════════════════════════════ -->
  <?php include 'views/layouts/footer.php'; ?>

  <!-- JavaScript optimizado -->
  <script>
    (function() {
      // ==================== NAVBAR SCROLL ====================
      const navbar = document.getElementById('navbar');
      if (navbar) {
        window.addEventListener('scroll', () => {
          navbar.classList.toggle('scrolled', window.scrollY > 20);
        });
      }

      // ==================== HAMBURGER MENU ====================
      const btn = document.getElementById('navHamburger');
      const links = document.getElementById('nav-links');
      if (btn && links) {
        btn.addEventListener('click', () => {
          const open = links.classList.toggle('open');
          btn.setAttribute('aria-expanded', open);
          btn.classList.toggle('open', open);
          document.body.style.overflow = open ? 'hidden' : '';
        });
      }

      // ==================== FILTROS PILLS ====================
      document.querySelectorAll('.filtro-pills').forEach(group => {
        const pills = group.querySelectorAll('.pill');
        pills.forEach(pill => {
          pill.addEventListener('click', () => {
            pills.forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
          });
        });
      });

      // ==================== FILTROS MOBILE ====================
      const filtrosPanel = document.getElementById('filtrosPanel');
      const btnFiltrosMobile = document.getElementById('btnFiltrosMobile');
      const filtrosClose = document.getElementById('filtrosClose');

      if (btnFiltrosMobile && filtrosPanel) {
        btnFiltrosMobile.addEventListener('click', () => {
          filtrosPanel.classList.toggle('open');
          document.body.style.overflow = filtrosPanel.classList.contains('open') ? 'hidden' : '';
        });
      }

      if (filtrosClose && filtrosPanel) {
        filtrosClose.addEventListener('click', () => {
          filtrosPanel.classList.remove('open');
          document.body.style.overflow = '';
        });
      }

      // Cerrar filtros al hacer click fuera (solo mobile)
      document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768 && filtrosPanel && filtrosPanel.classList.contains('open')) {
          if (!filtrosPanel.contains(e.target) && !btnFiltrosMobile?.contains(e.target)) {
            filtrosPanel.classList.remove('open');
            document.body.style.overflow = '';
          }
        }
      });

      // ==================== ANIMACIÓN CONTADORES ====================
      function animateCounter(element, target) {
        let current = 0;
        const increment = target / 50;
        const timer = setInterval(() => {
          current += increment;
          if (current >= target) {
            element.textContent = target.toLocaleString();
            clearInterval(timer);
          } else {
            element.textContent = Math.floor(current).toLocaleString();
          }
        }, 20);
      }

      const statNumbers = document.querySelectorAll('.stat-num');
      const observerOptions = { threshold: 0.3, rootMargin: '0px' };
      
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const el = entry.target;
            const target = parseInt(el.dataset.count, 10);
            if (target && !el.classList.contains('animated')) {
              el.classList.add('animated');
              animateCounter(el, target);
            }
            observer.unobserve(el);
          }
        });
      }, observerOptions);
      
      statNumbers.forEach(num => observer.observe(num));

      // ==================== EFECTO HOVER EN TARJETAS ====================
      const propCards = document.querySelectorAll('.prop-card');
      propCards.forEach(card => {
        card.addEventListener('mouseenter', () => {
          propCards.forEach(c => c.style.opacity = '0.7');
          card.style.opacity = '1';
        });
        card.addEventListener('mouseleave', () => {
          propCards.forEach(c => c.style.opacity = '1');
        });
      });

      // ==================== RESIZE HANDLER ====================
      let resizeTimer;
      window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
          if (window.innerWidth > 768 && filtrosPanel) {
            filtrosPanel.classList.remove('open');
            document.body.style.overflow = '';
          }
        }, 250);
      });

    })();

    // ==================== FUNCIONES GLOBALES ====================
    function buscarPropiedades() {
      console.log('🔍 Buscando propiedades... (conexión a BD pendiente)');
    }

    function limpiarFiltros() {
      document.querySelectorAll('.filtro-select').forEach(s => s.value = '');
      document.querySelectorAll('.filtro-input').forEach(i => i.value = '');
      document.querySelectorAll('.filtro-pills .pill').forEach(p => p.classList.remove('active'));
      document.querySelectorAll('.filtro-pills').forEach(group => {
        const firstPill = group.querySelector('.pill');
        if (firstPill) firstPill.classList.add('active');
      });
      console.log('🧹 Filtros limpiados');
    }
  </script>

</body>
</html>