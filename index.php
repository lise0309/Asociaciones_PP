<?php
/**
 * INDEX — PP Bienes Raíces
 * Asociaciones Portillo Pocasangre
 * Vista pública (Guest)
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="PP Bienes Raíces — La mejor experiencia inmobiliaria en El Salvador.">
  <meta name="keywords" content="bienes raíces, El Salvador, propiedades, casas, apartamentos, terrenos">
  <meta property="og:title" content="PP Bienes Raíces | Inicio">
  <meta property="og:type" content="website">
  <title>PP Bienes Raíces | Propiedades en El Salvador</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,800;1,700&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="assets/css/navbar.css">
  <link rel="stylesheet" href="assets/css/index.css">
  <link rel="stylesheet" href="assets/css/footer.css">
</head>
<body>

  <?php include 'views/layouts/navbar.php'; ?>

  <!-- ═══════════════════════════════════════
       LAYOUT PRINCIPAL
  ═══════════════════════════════════════ -->
  <div class="page-layout" id="pageLayout">

    <!-- ── PANEL DE FILTROS ── -->
    <aside class="filtros-panel" id="filtrosPanel">

      <div class="filtros-header">
        <h3 class="filtros-titulo">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M2.628 1.601C5.028 1.206 7.49 1 10 1s4.972.206 7.372.601a.75.75 0 01.628.74v2.288a2.25 2.25 0 01-.659 1.59l-4.682 4.683a2.25 2.25 0 00-.659 1.59v3.037c0 .684-.31 1.33-.844 1.757l-1.937 1.55A.75.75 0 018 18.25v-5.757a2.25 2.25 0 00-.659-1.591L2.659 6.22A2.25 2.25 0 012 4.628V2.34a.75.75 0 01.628-.74z" clip-rule="evenodd"/>
          </svg>
          Filtros
        </h3>
        <!-- Botón cerrar en móvil -->
        <button class="filtros-close" id="filtrosClose" aria-label="Cerrar filtros">✕</button>
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

      <!-- Departamento -->
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

      <!-- Precio -->
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
          <button class="pill" data-val="eur">€ EUR</button>
          <button class="pill" data-val="btc">₿ BTC</button>
        </div>
      </div>

      <div class="filtro-actions">
        <button class="btn-buscar" id="btnBuscar">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/>
          </svg>
          Buscar propiedades
        </button>
        <button class="btn-limpiar" id="btnLimpiar">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4z" clip-rule="evenodd"/>
          </svg>
          Limpiar filtros
        </button>
      </div>

    </aside>

    <!-- Botón toggle sidebar (visible en desktop) -->
    <button class="sidebar-toggle-btn" id="sidebarToggleBtn" aria-label="Mostrar/ocultar filtros" title="Mostrar/ocultar filtros">
      <svg class="toggle-icon-open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M2.628 1.601C5.028 1.206 7.49 1 10 1s4.972.206 7.372.601a.75.75 0 01.628.74v2.288a2.25 2.25 0 01-.659 1.59l-4.682 4.683a2.25 2.25 0 00-.659 1.59v3.037c0 .684-.31 1.33-.844 1.757l-1.937 1.55A.75.75 0 018 18.25v-5.757a2.25 2.25 0 00-.659-1.591L2.659 6.22A2.25 2.25 0 012 4.628V2.34a.75.75 0 01.628-.74z" clip-rule="evenodd"/>
      </svg>
      <svg class="toggle-icon-close" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="display:none">
        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
      </svg>
    </button>

    <!-- ── CONTENIDO PRINCIPAL ── -->
    <main class="contenido-principal" id="contenidoPrincipal">

      <!-- HERO -->
      <section class="hero-section">
        <div class="hero-content">
          <div class="hero-texto">
            <span class="hero-badge">✦ Propiedades de lujo en El Salvador</span>
            <h1 class="hero-title">
              Invierte en<br>
              <em>El Salvador hoy</em>
            </h1>
            <p class="hero-sub">
              Descubre las propiedades más exclusivas del mercado inmobiliario salvadoreño. Terrenos, fincas, residencias y locales con los mejores precios.
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

          <!-- Banner mapa -->
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
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/>
                </svg>
              </a>
            </div>
          </div>
        </div>
      </section>

      <!-- BARRA RESULTADOS -->
      <div class="resultados-bar">
        <div class="resultados-left">
          <span class="resultados-count">
            <strong id="totalCount">868</strong> propiedades encontradas
          </span>
          <!-- Botón filtros solo en móvil -->
          <button class="btn-filtros-mobile" id="btnFiltrosMobile">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
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

      <!-- GRID PROPIEDADES -->
      <div class="props-grid" id="propsGrid">

        <article class="prop-card">
          <a href="views/propiedades/detalle.php?id=2342" class="prop-card-link">
            <div class="prop-img">
              <div class="prop-img-ph">🏡</div>
              <div class="prop-tags"><span class="ptag ptag-venta">VENTA</span></div>
              <span class="prop-codigo">#2342</span>
            </div>
            <div class="prop-body">
              <div class="prop-price">US$ 298,000 <small>venta</small></div>
              <h3 class="prop-titulo">Finca de Café con vivienda principal</h3>
              <div class="prop-loc">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd"/></svg>
                Jayaque, La Libertad
              </div>
              <div class="prop-feats"><span>🚪 2 hab.</span><span>🛁 2 baños</span><span>📐 4.2 mz</span></div>
            </div>
          </a>
        </article>

        <article class="prop-card">
          <a href="views/propiedades/detalle.php?id=2343" class="prop-card-link">
            <div class="prop-img">
              <div class="prop-img-ph">🌿</div>
              <div class="prop-tags"><span class="ptag ptag-venta">VENTA</span></div>
              <span class="prop-codigo">#2343</span>
            </div>
            <div class="prop-body">
              <div class="prop-price">US$ 330,000 <small>venta</small></div>
              <h3 class="prop-titulo">Terreno residencial con vista panorámica</h3>
              <div class="prop-loc">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd"/></svg>
                San José Villanueva, La Libertad
              </div>
              <div class="prop-feats"><span>📐 2,500 m²</span><span>🌳 Vista montaña</span></div>
            </div>
          </a>
        </article>

        <article class="prop-card">
          <a href="views/propiedades/detalle.php?id=2341" class="prop-card-link">
            <div class="prop-img">
              <div class="prop-img-ph">🏖️</div>
              <div class="prop-tags"><span class="ptag ptag-venta">VENTA</span></div>
              <span class="prop-codigo">#2341</span>
            </div>
            <div class="prop-body">
              <div class="prop-price">US$ 140,000 <small>venta</small></div>
              <h3 class="prop-titulo">Terreno frente al mar con acceso directo</h3>
              <div class="prop-loc">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd"/></svg>
                La Libertad, La Libertad
              </div>
              <div class="prop-feats"><span>📐 1,200 m²</span><span>🌊 Frente al mar</span></div>
            </div>
          </a>
        </article>

        <article class="prop-card">
          <a href="views/propiedades/detalle.php?id=2340" class="prop-card-link">
            <div class="prop-img">
              <div class="prop-img-ph">🏠</div>
              <div class="prop-tags"><span class="ptag ptag-renta">RENTA</span></div>
              <span class="prop-codigo">#2340</span>
            </div>
            <div class="prop-body">
              <div class="prop-price">US$ 1,200 <small>/mes</small></div>
              <h3 class="prop-titulo">Casa residencial totalmente amueblada</h3>
              <div class="prop-loc">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd"/></svg>
                Santa Tecla, La Libertad
              </div>
              <div class="prop-feats"><span>🚪 3 hab.</span><span>🛁 2 baños</span><span>📐 220 m²</span></div>
            </div>
          </a>
        </article>

        <article class="prop-card">
          <a href="views/propiedades/detalle.php?id=2339" class="prop-card-link">
            <div class="prop-img">
              <div class="prop-img-ph">🏢</div>
              <div class="prop-tags"><span class="ptag ptag-renta">RENTA</span></div>
              <span class="prop-codigo">#2339</span>
            </div>
            <div class="prop-body">
              <div class="prop-price">US$ 2,400 <small>/mes</small></div>
              <h3 class="prop-titulo">Local comercial en zona céntrica</h3>
              <div class="prop-loc">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd"/></svg>
                San Salvador, San Salvador
              </div>
              <div class="prop-feats"><span>📐 180 m²</span><span>📍 Zona rosa</span></div>
            </div>
          </a>
        </article>

        <article class="prop-card prop-card-destacada">
          <a href="views/propiedades/detalle.php?id=2338" class="prop-card-link">
            <div class="prop-img">
              <div class="prop-img-ph">🌄</div>
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
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd"/></svg>
                Santa Tecla, La Libertad
              </div>
              <div class="prop-feats"><span>🚪 4 hab.</span><span>🛁 3 baños</span><span>📐 320 m²</span></div>
            </div>
          </a>
        </article>

      </div>

      <!-- PAGINACIÓN -->
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

  <!-- Overlay para móvil -->
  <div class="filtros-overlay" id="filtrosOverlay"></div>

  <?php include 'views/layouts/footer.php'; ?>

  <script>
    const PROP_URL = 'controllers/PropiedadController.php';
  </script>
  <script src="assets/js/index.js"></script>

</body>
</html>