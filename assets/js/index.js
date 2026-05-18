/**
 * index.js
 * PP Bienes Raíces — assets/js/index.js
 * Vista pública — conectado a BD con overlay de carga
 */

(function () {
  'use strict';

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

  const filtroModalidad  = document.getElementById('filtroModalidad');
  const filtroTipo       = document.getElementById('filtroTipo');
  const filtroCiudad     = document.getElementById('filtroCiudad');
  const precioMin        = document.getElementById('precioMin');
  const precioMax        = document.getElementById('precioMax');

  let paginaActual   = 1;
  let sidebarVisible = true;
  let debounceTimer;
  let isLoading      = false;

  const PROP_URL = window.PROP_URL || 'controllers/propiedadindexcontroller.php';

  /* ── Overlay de carga ── */
  let loadingOverlay = null;

  function mostrarOverlayCarga() {
    if (loadingOverlay) return;
    loadingOverlay = document.createElement('div');
    loadingOverlay.id = 'loadingOverlay';
    loadingOverlay.innerHTML = `<div class="loading-spinner"><div class="spinner"></div><p>Cargando propiedades...</p></div>`;
    loadingOverlay.style.cssText = `position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,15,46,.65);backdrop-filter:blur(3px);z-index:1000;display:flex;align-items:center;justify-content:center;transition:opacity .3s ease;`;
    document.body.appendChild(loadingOverlay);
    if (propsGrid) { propsGrid.style.transition='opacity .2s ease'; propsGrid.style.opacity='.4'; }
  }

  function ocultarOverlayCarga() {
    if (loadingOverlay) {
      loadingOverlay.style.opacity = '0';
      setTimeout(() => { loadingOverlay?.parentNode?.removeChild(loadingOverlay); loadingOverlay = null; }, 200);
    }
    if (propsGrid) propsGrid.style.opacity = '1';
  }

  /* ── Toggle sidebar desktop ── */
  function toggleSidebar() {
    sidebarVisible = !sidebarVisible;
    pageLayout?.classList.toggle('sidebar-hidden', !sidebarVisible);
    if (iconOpen)  iconOpen.style.display  = sidebarVisible ? 'block' : 'none';
    if (iconClose) iconClose.style.display = sidebarVisible ? 'none'  : 'block';
  }
  sidebarToggleBtn?.addEventListener('click', toggleSidebar);

  /* ── Filtros móvil ── */
  function abrirFiltros() { filtrosPanel?.classList.add('open'); filtrosOverlay?.classList.add('active'); document.body.style.overflow='hidden'; }
  function cerrarFiltros() { filtrosPanel?.classList.remove('open'); filtrosOverlay?.classList.remove('active'); document.body.style.overflow=''; }

  btnFiltrosMobile?.addEventListener('click', abrirFiltros);
  filtrosClose?.addEventListener('click', cerrarFiltros);
  filtrosOverlay?.addEventListener('click', cerrarFiltros);
  window.addEventListener('resize', () => { if (window.innerWidth > 768) cerrarFiltros(); });

  /* ── Pills ── */
  document.querySelectorAll('.filtro-pills').forEach(group => {
    group.querySelectorAll('.pill').forEach(pill => {
      pill.addEventListener('click', (e) => {
        e.preventDefault();
        pill.style.transform = 'scale(0.95)';
        setTimeout(() => { pill.style.transform = ''; }, 150);
        group.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
        pill.classList.add('active');
        if (group.id === 'filtroModalidad') { paginaActual = 1; buscarConDebounce(); }
      });
    });
  });

  /* ── Filtros activos ── */
  function getFiltros() {
    const pillModalidad = filtroModalidad?.querySelector('.pill.active')?.dataset.val ?? '';
    let negocio = '';
    if (pillModalidad === 'venta') negocio = 'venta';
    else if (pillModalidad === 'renta') negocio = 'alquiler';

    const params = new URLSearchParams({ accion:'listar', orden: ordenSelect?.value ?? 'recientes', pagina: paginaActual });
    if (negocio)              params.set('negocio', negocio);
    if (filtroTipo?.value)    params.set('tipo', filtroTipo.value);
    if (filtroCiudad?.value)  params.set('departamento', filtroCiudad.value);
    if (precioMin?.value)     params.set('precio_min', precioMin.value);
    if (precioMax?.value)     params.set('precio_max', precioMax.value);
    return params;
  }

  /* ── Animación número ── */
  function animateNumber(element, start, end) {
    const dur = 600, step = 16, steps = dur/step;
    const inc = (end-start)/steps;
    let cur = start, s = 0;
    const t = setInterval(() => {
      s++; cur += inc;
      if (s >= steps) { element.textContent = end.toLocaleString(); clearInterval(t); }
      else element.textContent = Math.round(cur).toLocaleString();
    }, step);
  }

  /* ── Cargar propiedades ── */
  async function cargarPropiedades() {
    if (!propsGrid || isLoading) return;
    isLoading = true;
    mostrarOverlayCarga();

    try {
      const res  = await fetch(`${PROP_URL}?${getFiltros()}`);
      const data = await res.json();
      if (!data.ok) throw new Error(data.msg || 'Error al cargar');

      if (totalCount) animateNumber(totalCount, parseInt(totalCount.textContent)||0, data.total);

      if (!data.propiedades.length) {
        propsGrid.innerHTML = `<div class="props-empty"><i class="fas fa-home"></i><p>No se encontraron propiedades con esos filtros.</p><button onclick="limpiarFiltrosExterno()">Limpiar filtros</button></div>`;
        document.getElementById('paginacion').innerHTML = '';
        return;
      }

      propsGrid.innerHTML = data.propiedades.map((p, i) => renderCard(p, i)).join('');

      // Animar entrada de tarjetas
      setTimeout(() => {
        propsGrid.querySelectorAll('.prop-card').forEach((card, i) => {
          card.style.animationDelay = `${i * 0.04}s`;
        });
      }, 50);

      renderPaginacion(data.pagina, data.total_pags);

    } catch (err) {
      propsGrid.innerHTML = `<div class="props-empty"><p>Error al cargar propiedades.</p><button onclick="location.reload()">Reintentar</button></div>`;
    } finally {
      setTimeout(() => { ocultarOverlayCarga(); }, 300);
      isLoading = false;
    }
  }

  /* ══════════════════════════════════════
     RENDER CARD — FLAT STYLE CON FONT AWESOME
     Cuadradas, 4 columnas, iconos FA, más azul
  ══════════════════════════════════════ */
  function renderCard(p, index) {
    const esAlquiler = (p.tipo_negocio === 'Alquiler' || p.tipo_negocio === 'Alquiler con opción a compra');
    const destacada  = p.es_anuncio_destacado == 1;
    const icono      = getFAIcon(p.tipo_inmueble);
    const codigo     = '#' + String(p.id).substring(0,8).toUpperCase();
    const precio     = formatPrecio(p.precio_pedido, p.tipo_negocio);

    // Foto o placeholder
    const imgHtml = p.foto_portada
      ? `<img src="${p.foto_portada}" alt="${escapeHtml(p.titulo_anuncio)}" class="prop-img-real" loading="lazy"
             onerror="this.parentNode.innerHTML='<div class=\\'prop-img-ph\\'><i class=\\'fas ${icono}\\'></i><span>${escapeHtml(p.tipo_inmueble||'')}</span></div>'">`
      : `<div class="prop-img-ph"><i class="fas ${icono}"></i><span>${escapeHtml(p.tipo_inmueble||'Propiedad')}</span></div>`;

    // Chips FA
    const chips = [];
    if (p.num_habitaciones)    chips.push(`<span class="feat-chip"><i class="fas fa-bed"></i> ${p.num_habitaciones}</span>`);
    if (p.num_banos)           chips.push(`<span class="feat-chip"><i class="fas fa-bath"></i> ${p.num_banos}</span>`);
    if (p.metros_construccion) chips.push(`<span class="feat-chip"><i class="fas fa-ruler-combined"></i> ${Number(p.metros_construccion).toLocaleString()}m²</span>`);
    else if (p.metros_terreno) chips.push(`<span class="feat-chip"><i class="fas fa-expand-arrows-alt"></i> ${Number(p.metros_terreno).toLocaleString()}m²</span>`);
    if (p.tiene_piscina==1)    chips.push(`<span class="feat-chip"><i class="fas fa-swimming-pool"></i></span>`);

    return `
    <article class="prop-card ${destacada?'prop-card-destacada':''}"
             onclick="window.location.href='/Asociaciones_PP/views/detalle.php?id=${p.id}'">
      <div class="prop-img">
        ${imgHtml}
        <div class="prop-tags">
          <span class="ptag ${esAlquiler?'ptag-renta':'ptag-venta'}">
            <i class="fas ${esAlquiler?'fa-key':'fa-tag'}"></i> ${esAlquiler?'RENTA':'VENTA'}
          </span>
          ${destacada?'<span class="ptag ptag-star"><i class="fas fa-star"></i> Top</span>':''}
        </div>
        <span class="prop-codigo">${codigo}</span>
        <div class="prop-img-overlay">
          <div>
            <div class="precio-overlay">${precio}</div>
            <div class="tipo-overlay">${esAlquiler?'por mes':'en venta'}</div>
          </div>
        </div>
      </div>
      <div class="prop-body">
        <h3 class="prop-titulo">${escapeHtml(p.titulo_anuncio)}</h3>
        <div class="prop-loc"><i class="fas fa-map-marker-alt"></i> ${escapeHtml(p.municipio||'')}, ${escapeHtml(p.departamento||'')}</div>
        ${chips.length?`<div class="prop-chips">${chips.join('')}</div>`:''}
      </div>
      <a class="prop-card-btn" href="/Asociaciones_PP/views/detalle.php?id=${p.id}" onclick="event.stopPropagation()">
        Ver propiedad <i class="fas fa-arrow-right"></i>
      </a>
    </article>`;
  }

  /* ── Iconos FA por tipo ── */
  function getFAIcon(tipo) {
    const map = {'Casa':'fa-home','Apartamento':'fa-building','Local comercial':'fa-store','Terreno':'fa-mountain','Bodega':'fa-warehouse','Finca':'fa-leaf','Oficina':'fa-briefcase'};
    return map[tipo] || 'fa-home';
  }

  /* ── Paginación ── */
  function renderPaginacion(pag, total) {
    if (!paginacion || total <= 1) { if(paginacion) paginacion.innerHTML=''; return; }
    let html = '';
    if (pag>1) html += `<button class="page-btn" data-pag="${pag-1}"><i class="fas fa-chevron-left"></i></button>`;
    const ini = Math.max(1,pag-2), fin = Math.min(total,pag+2);
    if (ini>1) html+=`<button class="page-btn" data-pag="1">1</button>`;
    if (ini>2) html+=`<span class="page-dots">…</span>`;
    for (let i=ini;i<=fin;i++) html+=`<button class="page-btn ${i===pag?'active':''}" data-pag="${i}">${i}</button>`;
    if (fin<total-1) html+=`<span class="page-dots">…</span>`;
    if (fin<total)   html+=`<button class="page-btn" data-pag="${total}">${total}</button>`;
    if (pag<total)   html+=`<button class="page-btn" data-pag="${pag+1}"><i class="fas fa-chevron-right"></i></button>`;
    paginacion.innerHTML = html;
    paginacion.querySelectorAll('.page-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        paginaActual = parseInt(btn.dataset.pag);
        cargarPropiedades();
        window.scrollTo({top:propsGrid?.offsetTop-100,behavior:'smooth'});
      });
    });
  }

  /* ── Precio ── */
  function formatPrecio(precio, negocio) {
    const num = parseFloat(precio).toLocaleString('en-US',{minimumFractionDigits:0,maximumFractionDigits:0});
    return `US$ ${num}`;
  }

  /* ── Escape ── */
  function escapeHtml(str) {
    if(!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  /* ── Debounce ── */
  function buscarConDebounce() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => { paginaActual=1; cargarPropiedades(); }, 350);
  }

  /* ── Limpiar filtros ── */
  window.limpiarFiltrosExterno = function() {
    if (filtroTipo) filtroTipo.selectedIndex=0;
    if (filtroCiudad) filtroCiudad.selectedIndex=0;
    if (precioMin) precioMin.value='';
    if (precioMax) precioMax.value='';
    document.querySelectorAll('.filtro-pills').forEach(g => { g.querySelectorAll('.pill').forEach(p=>p.classList.remove('active')); g.querySelector('.pill')?.classList.add('active'); });
    if (ordenSelect) ordenSelect.selectedIndex=0;
    paginaActual=1; cargarPropiedades();
    if (window.innerWidth<=768) cerrarFiltros();
  };
  window.limpiarFiltros = window.limpiarFiltrosExterno;

  /* ── Stats de BD ── */
  async function cargarStats() {
    try {
      const res  = await fetch(`${PROP_URL}?accion=stats`);
      const data = await res.json();
      if (!data.ok || !data.stats) return;
      const s = data.stats;
      const nums = document.querySelectorAll('.stat-num');
      if (nums[0] && s.propiedades)   animateNumber(nums[0], 0, s.propiedades);
      if (nums[1] && s.departamentos) animateNumber(nums[1], 0, s.departamentos);
      if (nums[2] && s.agentes)       animateNumber(nums[2], 0, s.agentes);
      if (nums[3]) animateNumber(nums[3], 0, 3200);
    } catch(e) {}
  }

  /* ── Event listeners ── */
  btnLimpiar?.addEventListener('click', window.limpiarFiltrosExterno);
  ordenSelect?.addEventListener('change', () => { paginaActual=1; cargarPropiedades(); });
  filtroTipo?.addEventListener('change', buscarConDebounce);
  filtroCiudad?.addEventListener('change', buscarConDebounce);
  precioMin?.addEventListener('input', buscarConDebounce);
  precioMax?.addEventListener('input', buscarConDebounce);

  /* ── Estilos animación ── */
  const style = document.createElement('style');
  style.textContent = `
    @keyframes fadeInUp { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
    .prop-card { animation: fadeInUp .35s ease both; }
    .loading-spinner { text-align:center; color:#FFFCFB; }
    .spinner { width:44px;height:44px;border:3px solid rgba(255,212,90,.2);border-top-color:#FFD45A;border-radius:50%;animation:spin .7s linear infinite;margin:0 auto 14px; }
    @keyframes spin { to{transform:rotate(360deg)} }
    .loading-spinner p { font-size:.875rem;font-weight:600;color:#FFFCFB; }
  `;
  document.head.appendChild(style);

  /* ── Inicializar ── */
  cargarStats();
  cargarPropiedades();

})();