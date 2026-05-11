/**
 * index.js
 * PP Bienes Raíces — assets/js/index.js
 * Vista pública — conectado a BD con overlay de carga
 */

(function () {
  'use strict';

  /* ══════════════════════════════════════
     REFERENCIAS DOM
  ══════════════════════════════════════ */
  const pageLayout       = document.getElementById('pageLayout');
  const filtrosPanel     = document.getElementById('filtrosPanel');
  const filtrosOverlay   = document.getElementById('filtrosOverlay');
  const filtrosClose     = document.getElementById('filtrosClose');
  const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
  const btnFiltrosMobile = document.getElementById('btnFiltrosMobile');
  const iconOpen         = sidebarToggleBtn?.querySelector('.toggle-icon-open');
  const iconClose        = sidebarToggleBtn?.querySelector('.toggle-icon-close');
  const propsGrid        = document.getElementById('propsGrid');
  const totalCount       = document.getElementById('totalCount');
  const btnLimpiar       = document.getElementById('btnLimpiar');
  const ordenSelect      = document.getElementById('ordenSelect');
  const paginacion       = document.getElementById('paginacion');
  const contenidoPrincipal = document.getElementById('contenidoPrincipal');

  // Filtros
  const filtroModalidad  = document.getElementById('filtroModalidad');
  const filtroTipo       = document.getElementById('filtroTipo');
  const filtroCiudad     = document.getElementById('filtroCiudad');
  const precioMin        = document.getElementById('precioMin');
  const precioMax        = document.getElementById('precioMax');

  let paginaActual   = 1;
  let sidebarVisible = true;
  let debounceTimer;
  let isLoading      = false;

  // URL del controlador
  const PROP_URL = 'controllers/PropiedadController.php';

  /* ══════════════════════════════════════
     CREAR OVERLAY DE CARGA
  ══════════════════════════════════════ */
  let loadingOverlay = null;

  function mostrarOverlayCarga() {
    // Si ya existe, no crear otro
    if (loadingOverlay) return;
    
    // Crear overlay
    loadingOverlay = document.createElement('div');
    loadingOverlay.id = 'loadingOverlay';
    loadingOverlay.innerHTML = `
      <div class="loading-spinner">
        <div class="spinner"></div>
        <p>Cargando propiedades...</p>
      </div>
    `;
    
    // Estilos del overlay
    loadingOverlay.style.cssText = `
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(3px);
      z-index: 1000;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
    `;
    
    document.body.appendChild(loadingOverlay);
    
    // También aplicar opacidad al grid
    if (propsGrid) {
      propsGrid.style.transition = 'opacity 0.2s ease';
      propsGrid.style.opacity = '0.4';
    }
  }

  function ocultarOverlayCarga() {
    if (loadingOverlay) {
      loadingOverlay.style.opacity = '0';
      setTimeout(() => {
        if (loadingOverlay && loadingOverlay.parentNode) {
          loadingOverlay.parentNode.removeChild(loadingOverlay);
        }
        loadingOverlay = null;
      }, 200);
    }
    
    // Restaurar opacidad del grid
    if (propsGrid) {
      propsGrid.style.opacity = '1';
    }
  }

  /* ══════════════════════════════════════
     TOGGLE SIDEBAR DESKTOP
  ══════════════════════════════════════ */
  function toggleSidebar() {
    sidebarVisible = !sidebarVisible;
    pageLayout?.classList.toggle('sidebar-hidden', !sidebarVisible);
    
    if (iconOpen)  iconOpen.style.display  = sidebarVisible ? 'block' : 'none';
    if (iconClose) iconClose.style.display = sidebarVisible ? 'none'  : 'block';
    
    if (sidebarToggleBtn) {
      sidebarToggleBtn.style.transform = sidebarVisible ? 'translateX(0)' : 'translateX(-10px)';
    }
  }
  
  sidebarToggleBtn?.addEventListener('click', toggleSidebar);

  /* ══════════════════════════════════════
     FILTROS MÓVIL
  ══════════════════════════════════════ */
  function abrirFiltros() {
    filtrosPanel?.classList.add('open');
    filtrosOverlay?.classList.add('active');
    document.body.style.overflow = 'hidden';
  }
  
  function cerrarFiltros() {
    filtrosPanel?.classList.remove('open');
    filtrosOverlay?.classList.remove('active');
    document.body.style.overflow = '';
  }
  
  btnFiltrosMobile?.addEventListener('click', abrirFiltros);
  filtrosClose?.addEventListener('click', cerrarFiltros);
  filtrosOverlay?.addEventListener('click', cerrarFiltros);
  
  window.addEventListener('resize', () => { 
    if (window.innerWidth > 768) cerrarFiltros(); 
  });

  /* ══════════════════════════════════════
     PILLS — click listener con animación
  ══════════════════════════════════════ */
  document.querySelectorAll('.filtro-pills').forEach(group => {
    group.querySelectorAll('.pill').forEach(pill => {
      pill.addEventListener('click', (e) => {
        e.preventDefault();
        
        // Animación de click
        pill.style.transform = 'scale(0.95)';
        setTimeout(() => { pill.style.transform = ''; }, 150);
        
        group.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
        pill.classList.add('active');
        
        if (group.id === 'filtroModalidad') {
          paginaActual = 1;
          buscarConDebounce();
        }
      });
    });
  });

  /* ══════════════════════════════════════
     OBTENER FILTROS ACTIVOS
  ══════════════════════════════════════ */
  function getFiltros() {
    const pillModalidad = filtroModalidad?.querySelector('.pill.active')?.dataset.val ?? '';
    let negocio = '';
    
    if (pillModalidad === 'venta') negocio = 'venta';
    else if (pillModalidad === 'renta') negocio = 'alquiler';
    
    const tipo        = filtroTipo?.value ?? '';
    const departamento = filtroCiudad?.value ?? '';
    const pmin        = precioMin?.value ?? '';
    const pmax        = precioMax?.value ?? '';
    const orden       = ordenSelect?.value ?? 'recientes';

    const params = new URLSearchParams({
      accion: 'listar',
      orden,
      pagina: paginaActual,
    });

    if (negocio)      params.set('negocio', negocio);
    if (tipo)         params.set('tipo', tipo);
    if (departamento) params.set('departamento', departamento);
    if (pmin)         params.set('precio_min', pmin);
    if (pmax)         params.set('precio_max', pmax);

    return params;
  }

  /* ══════════════════════════════════════
     ANIMACIÓN DEL CONTADOR
  ══════════════════════════════════════ */
  function animateNumber(element, start, end) {
    const duration = 600;
    const stepTime = 16;
    const steps = duration / stepTime;
    const increment = (end - start) / steps;
    let current = start;
    let step = 0;
    
    const timer = setInterval(() => {
      step++;
      current += increment;
      if (step >= steps) {
        element.textContent = end.toLocaleString();
        clearInterval(timer);
      } else {
        element.textContent = Math.round(current).toLocaleString();
      }
    }, stepTime);
  }

  /* ══════════════════════════════════════
     CARGAR PROPIEDADES CON OVERLAY
  ══════════════════════════════════════ */
  async function cargarPropiedades() {
    if (!propsGrid || isLoading) return;

    isLoading = true;
    
    // MOSTRAR OVERLAY DE CARGA
    mostrarOverlayCarga();

    try {
      const res  = await fetch(`${PROP_URL}?${getFiltros()}`);
      const data = await res.json();

      if (!data.ok) throw new Error(data.msg || 'Error al cargar');

      // Actualizar contador con animación
      if (totalCount) {
        const oldValue = parseInt(totalCount.textContent) || 0;
        const newValue = data.total;
        animateNumber(totalCount, oldValue, newValue);
      }

      // Sin resultados
      if (!data.propiedades.length) {
        propsGrid.innerHTML = `
          <div class="props-empty" style="animation: fadeIn 0.5s ease">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
            </svg>
            <p>No se encontraron propiedades con esos filtros.</p>
            <button onclick="limpiarFiltrosExterno()">Limpiar filtros</button>
          </div>`;
        return;
      }

      // Render tarjetas
      propsGrid.innerHTML = data.propiedades.map((p, index) => renderCard(p, index)).join('');
      
      // Animar entrada de tarjetas
      setTimeout(() => {
        const cards = propsGrid.querySelectorAll('.prop-card');
        cards.forEach((card, i) => {
          card.style.animation = `fadeInUp 0.4s ease forwards`;
          card.style.animationDelay = `${i * 0.03}s`;
          card.style.opacity = '0';
          card.style.transform = 'translateY(20px)';
        });
      }, 50);

      // Paginación
      renderPaginacion(data.pagina, data.total_pags);

    } catch (err) {
      propsGrid.innerHTML = `
        <div class="props-empty">
          <p>Error al cargar propiedades. Verifica la conexión.</p>
          <button onclick="location.reload()">Reintentar</button>
        </div>`;
      console.error('Error:', err);
    } finally {
      // OCULTAR OVERLAY DE CARGA
      setTimeout(() => {
        ocultarOverlayCarga();
      }, 300);
      isLoading = false;
    }
  }

  /* ══════════════════════════════════════
     RENDER TARJETA
  ══════════════════════════════════════ */
  function renderCard(p, index) {
    const esVenta   = p.tipo_negocio === 'Venta';
    const tagClass  = esVenta ? 'ptag-venta' : 'ptag-renta';
    const tagText   = esVenta ? 'VENTA' : 'RENTA';
    const destacada = p.es_anuncio_destacado == 1;
    const emoji     = getEmoji(p.tipo_inmueble);
    const feats     = getFeats(p);
    const codigo    = '#' + String(p.id).substring(0, 6).toUpperCase();

    const imgHtml = p.foto_portada
      ? `<img src="${p.foto_portada}"
               alt="${escapeHtml(p.titulo_anuncio)}"
               class="prop-img-real"
               loading="lazy"
               onerror="this.parentNode.innerHTML='<div class=\\'prop-img-ph\\'>${emoji}</div>'">`
      : `<div class="prop-img-ph">${emoji}</div>`;

    const precioFormateado = formatPrecio(p.precio_pedido, p.tipo_negocio);

    return `
      <article class="prop-card ${destacada ? 'prop-card-destacada' : ''}" 
               style="cursor:pointer; opacity:0; transform:translateY(20px);"
               onclick="window.location.href='/Asociaciones_PP/views/detalle.php?id=${p.id}'">
        <div class="prop-card-link">
          <div class="prop-img">
            ${imgHtml}
            <div class="prop-tags">
              <span class="ptag ${tagClass}">${tagText}</span>
              ${destacada ? '<span class="ptag ptag-star">★ Destacada</span>' : ''}
            </div>
            <span class="prop-codigo">${codigo}</span>
          </div>
          <div class="prop-body">
            <div class="prop-price">${precioFormateado}</div>
            <h3 class="prop-titulo">${escapeHtml(p.titulo_anuncio)}</h3>
            <div class="prop-loc">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd"/>
              </svg>
              ${escapeHtml(p.municipio || '')}, ${escapeHtml(p.departamento || '')}
            </div>
            ${feats ? `<div class="prop-feats">${feats}</div>` : ''}
          </div>
        </div>
      </article>`;
  }

  /* ══════════════════════════════════════
     PAGINACIÓN
  ══════════════════════════════════════ */
  function renderPaginacion(pag, total) {
    if (!paginacion || total <= 1) {
      if (paginacion) paginacion.innerHTML = '';
      return;
    }

    let html = '';

    if (pag > 1) {
      html += `<button class="page-btn" data-pag="${pag - 1}">‹</button>`;
    }

    const inicio = Math.max(1, pag - 2);
    const fin    = Math.min(total, pag + 2);

    if (inicio > 1) html += `<button class="page-btn" data-pag="1">1</button>`;
    if (inicio > 2) html += `<span class="page-dots">...</span>`;

    for (let i = inicio; i <= fin; i++) {
      html += `<button class="page-btn ${i === pag ? 'active' : ''}" data-pag="${i}">${i}</button>`;
    }

    if (fin < total - 1) html += `<span class="page-dots">...</span>`;
    if (fin < total)     html += `<button class="page-btn" data-pag="${total}">${total}</button>`;

    if (pag < total) {
      html += `<button class="page-btn" data-pag="${pag + 1}">›</button>`;
    }

    paginacion.innerHTML = html;

    paginacion.querySelectorAll('.page-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        paginaActual = parseInt(btn.dataset.pag);
        cargarPropiedades();
        window.scrollTo({ top: propsGrid?.offsetTop - 100, behavior: 'smooth' });
      });
    });
  }

  /* ══════════════════════════════════════
     HELPERS
  ══════════════════════════════════════ */
  function formatPrecio(precio, negocio) {
    const num = parseFloat(precio).toLocaleString('en-US', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    });
    const esAlquiler = (negocio === 'Alquiler' || negocio === 'Alquiler con opción a compra');
    const sfx = esAlquiler ? '<small>/mes</small>' : '<small>venta</small>';
    return `US$ ${num} ${sfx}`;
  }

  function getEmoji(tipo) {
    const map = {
      'Casa': '🏠',
      'Apartamento': '🏢',
      'Local comercial': '🏪',
      'Terreno': '🌿',
      'Bodega': '🏭',
      'Oficina': '🏢'
    };
    return map[tipo] ?? '🏡';
  }

  function getFeats(p) {
    const items = [];
    if (p.num_habitaciones) items.push(`🚪 ${p.num_habitaciones} hab.`);
    if (p.num_banos) items.push(`🛁 ${p.num_banos} baños`);
    if (p.metros_construccion) items.push(`📐 ${Number(p.metros_construccion).toLocaleString()} m²`);
    else if (p.metros_terreno) items.push(`📐 ${Number(p.metros_terreno).toLocaleString()} m²`);
    return items.join(' <span class="feat-sep">·</span> ');
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  /* ══════════════════════════════════════
     DEBOUNCE
  ══════════════════════════════════════ */
  function buscarConDebounce() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      paginaActual = 1;
      cargarPropiedades();
    }, 350);
  }

  /* ══════════════════════════════════════
     LIMPIAR FILTROS
  ══════════════════════════════════════ */
  window.limpiarFiltrosExterno = function() {
    if (filtroTipo) filtroTipo.selectedIndex = 0;
    if (filtroCiudad) filtroCiudad.selectedIndex = 0;
    if (precioMin) precioMin.value = '';
    if (precioMax) precioMax.value = '';

    document.querySelectorAll('.filtro-pills').forEach(group => {
      group.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
      group.querySelector('.pill')?.classList.add('active');
    });

    if (ordenSelect) ordenSelect.selectedIndex = 0;

    paginaActual = 1;
    cargarPropiedades();
    if (window.innerWidth <= 768) cerrarFiltros();
  };

  window.limpiarFiltros = window.limpiarFiltrosExterno;

  /* ══════════════════════════════════════
     STATS DEL HERO
  ══════════════════════════════════════ */
  async function cargarStats() {
    try {
      const res  = await fetch(`${PROP_URL}?accion=stats`);
      const data = await res.json();
      if (!data.ok || !data.stats) return;

      const s = data.stats;
      const nums = document.querySelectorAll('.stat-num');
      
      if (nums[0] && s.propiedades) animateNumber(nums[0], 0, s.propiedades);
      if (nums[1] && s.departamentos) animateNumber(nums[1], 0, s.departamentos);
      if (nums[2] && s.agentes) animateNumber(nums[2], 0, s.agentes);
      if (nums[3]) animateNumber(nums[3], 0, 3200);

    } catch (err) {
      console.error('Error cargando stats:', err);
    }
  }

  /* ══════════════════════════════════════
     EVENT LISTENERS
  ══════════════════════════════════════ */
  btnLimpiar?.addEventListener('click', window.limpiarFiltrosExterno);
  ordenSelect?.addEventListener('change', () => { paginaActual = 1; cargarPropiedades(); });

  // Selects con debounce y overlay
  filtroTipo?.addEventListener('change', buscarConDebounce);
  filtroCiudad?.addEventListener('change', buscarConDebounce);
  precioMin?.addEventListener('input', buscarConDebounce);
  precioMax?.addEventListener('input', buscarConDebounce);

  /* ══════════════════════════════════════
     ANIMACIONES CSS ADICIONALES
  ══════════════════════════════════════ */
  const styleAnimations = document.createElement('style');
  styleAnimations.textContent = `
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes fadeInRight {
      from { opacity: 0; transform: translateX(-20px); }
      to { opacity: 1; transform: translateX(0); }
    }
    
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    .prop-card {
      animation: fadeInUp 0.4s ease forwards;
    }
    
    .sidebar-toggle-btn {
      transition: transform 0.3s ease, left 0.3s ease;
    }
    
    /* Spinner de carga */
    .loading-spinner {
      text-align: center;
      color: white;
    }
    
    .spinner {
      width: 50px;
      height: 50px;
      border: 4px solid rgba(255, 255, 255, 0.3);
      border-top: 4px solid var(--gold, #FFD45A);
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
      margin: 0 auto 16px;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    .loading-spinner p {
      font-size: 0.9rem;
      font-weight: 500;
      margin-top: 12px;
    }
  `;
  document.head.appendChild(styleAnimations);

  /* ══════════════════════════════════════
     INICIALIZAR
  ══════════════════════════════════════ */
  cargarStats();
  cargarPropiedades();

})();