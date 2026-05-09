/**
 * index.js
 * PP Bienes Raíces — assets/js/index.js
 * Vista pública — conectado a BD
 */

(function () {
  'use strict';

  // ── Referencias DOM ──
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

  // ── Estado sidebar ──
  let sidebarVisible = true;
  let debounceTimer;

  // ════════════════════════════════════════
  // TOGGLE SIDEBAR DESKTOP
  // ════════════════════════════════════════
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

  // ════════════════════════════════════════
  // FILTROS MÓVIL
  // ════════════════════════════════════════
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
  window.addEventListener('resize', () => { if (window.innerWidth > 768) cerrarFiltros(); });

  // ════════════════════════════════════════
  // PILLS
  // ════════════════════════════════════════
  document.querySelectorAll('.filtro-pills').forEach(group => {
    group.querySelectorAll('.pill').forEach(pill => {
      pill.addEventListener('click', () => {
        group.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
        pill.classList.add('active');
        buscarConDebounce();
      });
    });
  });

  // ════════════════════════════════════════
  // OBTENER FILTROS ACTIVOS
  // ════════════════════════════════════════
  function getFiltros() {
    const modalidad   = document.querySelector('#filtroModalidad .pill.active')?.dataset.val ?? '';
    const tipo        = document.getElementById('filtroTipo')?.value ?? '';
    const departamento= document.getElementById('filtroCiudad')?.value ?? '';
    const precioMin   = document.getElementById('precioMin')?.value ?? '';
    const precioMax   = document.getElementById('precioMax')?.value ?? '';
    const orden       = ordenSelect?.value ?? 'recientes';

    const params = new URLSearchParams({ accion: 'listar', orden });
    if (modalidad)    params.set('negocio',       modalidad);
    if (tipo)         params.set('tipo',           tipo);
    if (departamento) params.set('departamento',   departamento);
    if (precioMin)    params.set('precio_min',     precioMin);
    if (precioMax)    params.set('precio_max',     precioMax);
    return params;
  }

  // ════════════════════════════════════════
  // CARGAR PROPIEDADES DESDE BD
  // ════════════════════════════════════════
  async function cargarPropiedades() {
    if (!propsGrid) return;

    // Loading skeleton
    propsGrid.innerHTML = `
      <div class="prop-skeleton"></div>
      <div class="prop-skeleton"></div>
      <div class="prop-skeleton"></div>
    `;

    try {
      const res  = await fetch(`${PROP_URL}?${getFiltros()}`);
      const data = await res.json();

      if (!data.ok) throw new Error(data.msg);

      // Actualizar contador
      if (totalCount) totalCount.textContent = data.total.toLocaleString();

      if (!data.propiedades.length) {
        propsGrid.innerHTML = `
          <div class="props-empty">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9.293 2.293a1 1 0 011.414 0l7 7A1 1 0 0117 11h-1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3a1 1 0 00-1-1H9a1 1 0 00-1 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6H3a1 1 0 01-.707-1.707l7-7z" clip-rule="evenodd"/>
            </svg>
            <p>No se encontraron propiedades con esos filtros.</p>
            <button onclick="limpiarFiltros()">Limpiar filtros</button>
          </div>`;
        return;
      }

      propsGrid.innerHTML = data.propiedades.map(p => renderCard(p)).join('');

      // Hover effect en tarjetas
      const cards = propsGrid.querySelectorAll('.prop-card');
      cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
          cards.forEach(c => { if (c !== card) c.style.opacity = '.65'; });
        });
        card.addEventListener('mouseleave', () => {
          cards.forEach(c => c.style.opacity = '1');
        });
      });

    } catch (err) {
      propsGrid.innerHTML = `<div class="props-empty"><p>Error al cargar propiedades. Verifica la conexión a la BD.</p></div>`;
      console.error(err);
    }
  }

  // ════════════════════════════════════════
  // RENDER TARJETA
  // ════════════════════════════════════════
  function renderCard(p) {
    const esVenta    = p.tipo_negocio === 'Venta';
    const precio     = formatPrecio(p.precio_pedido, p.tipo_negocio, p.moneda);
    const tagClass   = esVenta ? 'ptag-venta' : 'ptag-renta';
    const tagText    = esVenta ? 'VENTA' : (p.tipo_negocio === 'Alquiler' ? 'RENTA' : 'ALQ. CON OPCIÓN');
    const destacada  = p.es_anuncio_destacado == 1;
    const emoji      = getEmoji(p.tipo_inmueble);
    const feats      = getFeats(p);

    // Código corto basado en ID
    const codigo = '#' + p.id.substring(0, 4).toUpperCase();

    const imgHtml = p.foto_portada
      ? `<img src="${p.foto_portada}" alt="${p.titulo_anuncio}" class="prop-img-real" loading="lazy">`
      : `<div class="prop-img-ph">${emoji}</div>`;

    return `
      <article class="prop-card ${destacada ? 'prop-card-destacada' : ''}">
        <a href="views/propiedades/detalle.php?id=${p.id}" class="prop-card-link">
          <div class="prop-img">
            ${imgHtml}
            <div class="prop-tags">
              <span class="ptag ${tagClass}">${tagText}</span>
              ${destacada ? '<span class="ptag ptag-star">★ Destacada</span>' : ''}
            </div>
            <span class="prop-codigo">${codigo}</span>
          </div>
          <div class="prop-body">
            <div class="prop-price">${precio}</div>
            <h3 class="prop-titulo">${p.titulo_anuncio}</h3>
            <div class="prop-loc">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd"/>
              </svg>
              ${p.municipio}, ${p.departamento}
            </div>
            <div class="prop-feats">${feats}</div>
          </div>
        </a>
      </article>`;
  }

  // ════════════════════════════════════════
  // HELPERS
  // ════════════════════════════════════════
  function formatPrecio(precio, negocio, moneda) {
    const num = parseFloat(precio).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    const sufijo = negocio === 'Alquiler' || negocio === 'Alquiler con opción a compra'
      ? '<small>/mes</small>'
      : '<small>venta</small>';
    return `${moneda} ${num} ${sufijo}`;
  }

  function getEmoji(tipo) {
    const map = {
      'Casa':             '🏠',
      'Apartamento':      '🏢',
      'Local comercial':  '🏪',
      'Terreno':          '🌿',
      'Bodega':           '🏭',
    };
    return map[tipo] ?? '🏡';
  }

  function getFeats(p) {
    const feats = [];
    if (p.num_habitaciones) feats.push(`🚪 ${p.num_habitaciones} hab.`);
    if (p.num_banos)        feats.push(`🛁 ${p.num_banos} baños`);
    if (p.metros_construccion) feats.push(`📐 ${parseFloat(p.metros_construccion).toLocaleString()} m²`);
    else if (p.metros_terreno) feats.push(`📐 ${parseFloat(p.metros_terreno).toLocaleString()} m²`);
    return feats.length ? feats.join('<span class="feat-sep">·</span>') : '—';
  }

  // ════════════════════════════════════════
  // BUSCAR CON DEBOUNCE
  // ════════════════════════════════════════
  function buscarConDebounce() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(cargarPropiedades, 350);
  }

  // ════════════════════════════════════════
  // LIMPIAR FILTROS
  // ════════════════════════════════════════
  window.limpiarFiltros = function () {
    document.querySelectorAll('.filtro-select').forEach(s => s.value = '');
    document.querySelectorAll('.filtro-input').forEach(i => i.value = '');
    document.querySelectorAll('.filtro-pills').forEach(group => {
      group.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
      group.querySelector('.pill')?.classList.add('active');
    });
    cargarPropiedades();
  };

  // ════════════════════════════════════════
  // STATS DEL HERO DESDE BD
  // ════════════════════════════════════════
  async function cargarStats() {
    try {
      const res  = await fetch(`${PROP_URL}?accion=stats`);
      const data = await res.json();
      if (!data.ok) return;

      const s = data.stats;
      // Actualizar data-count para la animación
      const nums = document.querySelectorAll('.stat-num');
      if (nums[0]) nums[0].dataset.count = s.propiedades  ?? 0;
      if (nums[1]) nums[1].dataset.count = s.departamentos ?? 14;
      if (nums[2]) nums[2].dataset.count = s.agentes       ?? 0;
      // Ventas no viene de BD aún — dejamos el valor estático
      if (nums[3]) nums[3].dataset.count = 3200;

    } catch (_) {}
  }

  // ════════════════════════════════════════
  // ANIMACIÓN CONTADORES
  // ════════════════════════════════════════
  function animateCounter(el, target) {
    let current = 0;
    const inc = Math.max(target / 50, 1);
    const timer = setInterval(() => {
      current += inc;
      if (current >= target) {
        el.textContent = parseInt(target).toLocaleString();
        clearInterval(timer);
      } else {
        el.textContent = Math.floor(current).toLocaleString();
      }
    }, 20);
  }

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
  }, { threshold: 0.3 });

  document.querySelectorAll('.stat-num').forEach(el => observer.observe(el));

  // ════════════════════════════════════════
  // EVENT LISTENERS
  // ════════════════════════════════════════
  btnBuscar?.addEventListener('click', cargarPropiedades);
  btnLimpiar?.addEventListener('click', window.limpiarFiltros);
  ordenSelect?.addEventListener('change', cargarPropiedades);

  // Filtros con debounce
  ['filtroTipo', 'filtroCiudad', 'filtroSector', 'filtroEstado'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', buscarConDebounce);
  });
  ['precioMin', 'precioMax'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', buscarConDebounce);
  });

  // ════════════════════════════════════════
  // INICIALIZAR
  // ════════════════════════════════════════
  cargarStats().then(() => cargarPropiedades());

})();