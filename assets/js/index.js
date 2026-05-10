/**
 * index.js
 * PP Bienes Raíces — assets/js/index.js
 * Vista pública — conectado a BD, filtros 100% funcionales
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
  const btnBuscar        = document.getElementById('btnBuscar');
  const btnLimpiar       = document.getElementById('btnLimpiar');
  const ordenSelect      = document.getElementById('ordenSelect');
  const paginacion       = document.getElementById('paginacion');

  // Filtros
  const filtroModalidad  = document.getElementById('filtroModalidad');    // pills negocio
  const filtroTipo       = document.getElementById('filtroTipo');         // select tipo inmueble
  const filtroCiudad     = document.getElementById('filtroCiudad');       // select departamento
  const filtroMoneda     = document.getElementById('filtroMoneda');       // pills moneda (solo visual)
  const precioMin        = document.getElementById('precioMin');
  const precioMax        = document.getElementById('precioMax');

  let paginaActual   = 1;
  let sidebarVisible = true;
  let debounceTimer;

  /* ══════════════════════════════════════
     TOGGLE SIDEBAR DESKTOP
  ══════════════════════════════════════ */
  function toggleSidebar() {
    sidebarVisible = !sidebarVisible;
    pageLayout?.classList.toggle('sidebar-hidden', !sidebarVisible);
    if (iconOpen)  iconOpen.style.display  = sidebarVisible ? 'block' : 'none';
    if (iconClose) iconClose.style.display = sidebarVisible ? 'none'  : 'block';
    if (sidebarToggleBtn) {
      sidebarToggleBtn.style.left = sidebarVisible ? 'var(--sidebar-w)' : '0';
    }
  }
  sidebarToggleBtn?.addEventListener('click', toggleSidebar);

  /* ══════════════════════════════════════
     FILTROS MÓVIL
  ══════════════════════════════════════ */
  function abrirFiltros()  {
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
  filtrosClose?.addEventListener('click',     cerrarFiltros);
  filtrosOverlay?.addEventListener('click',   cerrarFiltros);
  window.addEventListener('resize', () => { if (window.innerWidth > 768) cerrarFiltros(); });

  /* ══════════════════════════════════════
     PILLS — click listener
  ══════════════════════════════════════ */
  document.querySelectorAll('.filtro-pills').forEach(group => {
    group.querySelectorAll('.pill').forEach(pill => {
      pill.addEventListener('click', () => {
        group.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
        pill.classList.add('active');
        // Solo el grupo de modalidad dispara búsqueda
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
    const negocio     = filtroModalidad?.querySelector('.pill.active')?.dataset.val ?? '';
    const tipo        = filtroTipo?.value     ?? '';
    const departamento= filtroCiudad?.value   ?? '';
    const pmin        = precioMin?.value      ?? '';
    const pmax        = precioMax?.value      ?? '';
    const orden       = ordenSelect?.value    ?? 'recientes';

    const params = new URLSearchParams({
      accion: 'listar',
      orden,
      pagina: paginaActual,
    });

    // Solo agregar si tienen valor real
    if (negocio)      params.set('negocio',      negocio);
    if (tipo)         params.set('tipo',          tipo);
    if (departamento) params.set('departamento',  departamento);
    if (pmin)         params.set('precio_min',    pmin);
    if (pmax)         params.set('precio_max',    pmax);

    return params;
  }

  /* ══════════════════════════════════════
     CARGAR PROPIEDADES
  ══════════════════════════════════════ */
  async function cargarPropiedades() {
    if (!propsGrid) return;

    // Skeleton
    propsGrid.innerHTML = `
      <div class="prop-skeleton"></div>
      <div class="prop-skeleton"></div>
      <div class="prop-skeleton"></div>
      <div class="prop-skeleton"></div>
      <div class="prop-skeleton"></div>
      <div class="prop-skeleton"></div>
    `;
    if (paginacion) paginacion.innerHTML = '';

    try {
      const res  = await fetch(`${PROP_URL}?${getFiltros()}`);
      const data = await res.json();

      if (!data.ok) throw new Error(data.msg);

      // Actualizar contador
      if (totalCount) {
        totalCount.textContent = data.total.toLocaleString();
      }

      // Sin resultados
      if (!data.propiedades.length) {
        propsGrid.innerHTML = `
          <div class="props-empty">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
            </svg>
            <p>No se encontraron propiedades con esos filtros.</p>
            <button onclick="window.limpiarFiltros()">Limpiar filtros</button>
          </div>`;
        return;
      }

      // Render tarjetas
      propsGrid.innerHTML = data.propiedades.map(p => renderCard(p)).join('');

      // Hover sutil en tarjetas
      const cards = propsGrid.querySelectorAll('.prop-card');
      cards.forEach(card => {
        card.addEventListener('mouseenter', () => cards.forEach(c => { if (c !== card) c.style.opacity = '.65'; }));
        card.addEventListener('mouseleave', () => cards.forEach(c => c.style.opacity = '1'));
      });

      // Paginación
      renderPaginacion(data.pagina, data.total_pags);

    } catch (err) {
      propsGrid.innerHTML = `
        <div class="props-empty">
          <p>Error al cargar propiedades. Verifica la conexión.</p>
        </div>`;
      console.error('Error:', err);
    }
  }

  /* ══════════════════════════════════════
     RENDER TARJETA
  ══════════════════════════════════════ */
  function renderCard(p) {
    const esVenta   = p.tipo_negocio === 'Venta';
    const esAlqOpc  = p.tipo_negocio === 'Alquiler con opción a compra';
    const tagClass  = esVenta ? 'ptag-venta' : 'ptag-renta';
    const tagText   = esVenta ? 'VENTA' : (esAlqOpc ? 'ALQ. C/ OPCIÓN' : 'RENTA');
    const destacada = p.es_anuncio_destacado == 1;
    const emoji     = getEmoji(p.tipo_inmueble);
    const feats     = getFeats(p);
    const codigo    = '#' + p.id.substring(0, 6).toUpperCase();

    const imgHtml = p.foto_portada
      ? `<img src="${esc(p.foto_portada)}"
               alt="${esc(p.titulo_anuncio)}"
               class="prop-img-real"
               loading="lazy"
               onerror="this.parentNode.innerHTML='<div class=\\'prop-img-ph\\'>${emoji}</div>'">`
      : `<div class="prop-img-ph">${emoji}</div>`;

    return `
      <article class="prop-card ${destacada ? 'prop-card-destacada' : ''}" style="cursor:pointer;" onclick="window.location.href='/Asociaciones_PP/views/detalle.php?id=${esc(p.id)}'">
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
            <div class="prop-price">${formatPrecio(p.precio_pedido, p.tipo_negocio, p.moneda)}</div>
            <h3 class="prop-titulo">${esc(p.titulo_anuncio)}</h3>
            <div class="prop-loc">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd"/>
              </svg>
              ${esc(p.municipio)}, ${esc(p.departamento)}
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
    if (!paginacion || total <= 1) return;

    let html = '';

    // Anterior
    if (pag > 1) {
      html += `<button class="page-btn" data-pag="${pag - 1}">‹</button>`;
    }

    // Páginas
    const inicio = Math.max(1, pag - 2);
    const fin    = Math.min(total, pag + 2);

    if (inicio > 1) html += `<button class="page-btn" data-pag="1">1</button>`;
    if (inicio > 2) html += `<span class="page-dots">...</span>`;

    for (let i = inicio; i <= fin; i++) {
      html += `<button class="page-btn ${i === pag ? 'active' : ''}" data-pag="${i}">${i}</button>`;
    }

    if (fin < total - 1) html += `<span class="page-dots">...</span>`;
    if (fin < total)     html += `<button class="page-btn" data-pag="${total}">${total}</button>`;

    // Siguiente
    if (pag < total) {
      html += `<button class="page-btn" data-pag="${pag + 1}">›</button>`;
    }

    paginacion.innerHTML = html;

    // Bind clicks
    paginacion.querySelectorAll('.page-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        paginaActual = parseInt(btn.dataset.pag);
        cargarPropiedades();
        // Scroll suave al grid
        propsGrid?.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    });
  }

  /* ══════════════════════════════════════
     HELPERS
  ══════════════════════════════════════ */
  function formatPrecio(precio, negocio, moneda) {
    const num = parseFloat(precio).toLocaleString('en-US', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    });
    const sfx = (negocio === 'Alquiler' || negocio === 'Alquiler con opción a compra')
      ? '<small>/mes</small>'
      : '<small>venta</small>';
    return `${moneda ?? 'USD'} ${num} ${sfx}`;
  }

  function getEmoji(tipo) {
    const map = {
      'Casa':            '🏠',
      'Apartamento':     '🏢',
      'Local comercial': '🏪',
      'Terreno':         '🌿',
      'Bodega':          '🏭',
    };
    return map[tipo] ?? '🏡';
  }

  function getFeats(p) {
    const items = [];
    if (p.num_habitaciones)    items.push(`🚪 ${p.num_habitaciones} hab.`);
    if (p.num_banos)           items.push(`🛁 ${p.num_banos} baños`);
    if (p.metros_construccion) items.push(`📐 ${fmt(p.metros_construccion)} m²`);
    else if (p.metros_terreno) items.push(`📐 ${fmt(p.metros_terreno)} m²`);
    return items.join(' <span class="feat-sep">·</span> ');
  }

  function fmt(n) {
    return parseFloat(n).toLocaleString('en-US', { maximumFractionDigits: 0 });
  }

  function esc(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  /* ══════════════════════════════════════
     DEBOUNCE
  ══════════════════════════════════════ */
  function buscarConDebounce() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      paginaActual = 1;
      cargarPropiedades();
    }, 380);
  }

  /* ══════════════════════════════════════
     LIMPIAR FILTROS
  ══════════════════════════════════════ */
  window.limpiarFiltros = function () {
    // Selects
    filtroTipo?.selectedIndex  = 0;
    filtroCiudad?.selectedIndex = 0;

    // Inputs precio
    if (precioMin) precioMin.value = '';
    if (precioMax) precioMax.value = '';

    // Pills — activar primera de cada grupo
    document.querySelectorAll('.filtro-pills').forEach(group => {
      group.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
      group.querySelector('.pill')?.classList.add('active');
    });

    // Orden
    if (ordenSelect) ordenSelect.selectedIndex = 0;

    paginaActual = 1;
    cargarPropiedades();
  };

  /* ══════════════════════════════════════
     STATS DEL HERO
  ══════════════════════════════════════ */
  async function cargarStats() {
    try {
      const res  = await fetch(`${PROP_URL}?accion=stats`);
      const data = await res.json();
      if (!data.ok || !data.stats) return;

      const s    = data.stats;
      const nums = document.querySelectorAll('.stat-num');
      if (nums[0]) nums[0].dataset.count = s.propiedades   ?? 0;
      if (nums[1]) nums[1].dataset.count = s.departamentos ?? 14;
      if (nums[2]) nums[2].dataset.count = s.agentes       ?? 0;
      if (nums[3]) nums[3].dataset.count = 3200; // ventas históricas estático

    } catch (_) {}
  }

  /* ══════════════════════════════════════
     ANIMACIÓN CONTADORES
  ══════════════════════════════════════ */
  function animateCounter(el, target) {
    let current = 0;
    const inc   = Math.max(target / 55, 1);
    const timer = setInterval(() => {
      current += inc;
      if (current >= target) {
        el.textContent = parseInt(target).toLocaleString();
        clearInterval(timer);
      } else {
        el.textContent = Math.floor(current).toLocaleString();
      }
    }, 18);
  }

  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el     = entry.target;
        const target = parseInt(el.dataset.count, 10);
        if (target && !el.classList.contains('animated')) {
          el.classList.add('animated');
          animateCounter(el, target);
        }
        observer.unobserve(el);
      }
    });
  }, { threshold: 0.3 });

  document.querySelectorAll('.stat-num').forEach(el => observer.observe(el));

  /* ══════════════════════════════════════
     EVENT LISTENERS
  ══════════════════════════════════════ */
  btnBuscar?.addEventListener('click', () => { paginaActual = 1; cargarPropiedades(); });
  btnLimpiar?.addEventListener('click', window.limpiarFiltros);
  ordenSelect?.addEventListener('change', () => { paginaActual = 1; cargarPropiedades(); });

  // Selects con debounce
  filtroTipo?.addEventListener('change',   buscarConDebounce);
  filtroCiudad?.addEventListener('change', buscarConDebounce);

  // Precios con debounce
  precioMin?.addEventListener('input', buscarConDebounce);
  precioMax?.addEventListener('input', buscarConDebounce);

  /* ══════════════════════════════════════
     INICIALIZAR
  ══════════════════════════════════════ */
  cargarStats().then(() => cargarPropiedades());

})();