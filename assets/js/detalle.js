/**
 * detalle.js
 * PP Bienes Raíces — assets/js/detalle.js
 * Vista pública de detalle de propiedad
 */

(function () {
  'use strict';

  if (!PROP_ID) { mostrarError(); return; }

  let fotos       = [];
  let lbIndex     = 0;
  let mapaInst    = null;

  // ════════════════════════════════════════
  // CARGAR DATOS
  // ════════════════════════════════════════
  async function init() {
    try {
      const res  = await fetch(`${DETALLE_URL}?id=${encodeURIComponent(PROP_ID)}`);
      const data = await res.json();

      if (!data.ok) { mostrarError(); return; }

      fotos = data.fotos || [];
      renderTodo(data.propiedad, fotos);

      document.getElementById('skeletonWrap').style.display = 'none';
      document.getElementById('mainContent').style.display  = 'block';

      // Mapa después del render
      if (data.propiedad.latitud && data.propiedad.longitud) {
        initMapa(
          parseFloat(data.propiedad.latitud),
          parseFloat(data.propiedad.longitud),
          data.propiedad.titulo_anuncio,
          data.propiedad.municipio + ', ' + data.propiedad.departamento
        );
      } else {
        document.getElementById('secMapa').style.display = 'none';
      }

    } catch (err) {
      console.error(err);
      mostrarError();
    }
  }

  // ════════════════════════════════════════
  // RENDER PRINCIPAL
  // ════════════════════════════════════════
  function renderTodo(p, imgs) {
    // Título de la página
    document.title = p.titulo_anuncio + ' | PP Bienes Raíces';

    // Galería
    renderGaleria(imgs, p.tipo_inmueble);

    // Tags
    const esVenta = p.tipo_negocio === 'Venta';
    const tagNeg  = esVenta ? 'det-tag-venta' : 'det-tag-renta';
    const texNeg  = esVenta ? 'VENTA' : (p.tipo_negocio === 'Alquiler' ? 'RENTA' : 'ALQ. C/ OPCIÓN');
    document.getElementById('detTags').innerHTML = `
      <span class="det-tag ${tagNeg}">${texNeg}</span>
      <span class="det-tag det-tag-tipo">${esc(p.tipo_inmueble)}</span>
      ${p.es_anuncio_destacado == 1 ? '<span class="det-tag det-tag-star">★ Destacada</span>' : ''}
    `;

    // Título, ubicación, precio
    document.getElementById('detTitulo').textContent    = p.titulo_anuncio;
    document.getElementById('ubicacionTexto').textContent =
      `${p.direccion_exacta ? p.direccion_exacta + ' — ' : ''}${p.municipio}, ${p.departamento}, El Salvador`;
    document.getElementById('detPrecio').innerHTML = formatPrecio(p.precio_pedido, p.tipo_negocio, p.moneda);

    // Sidebar precio
    document.getElementById('spPrecio').innerHTML = formatPrecio(p.precio_pedido, p.tipo_negocio, p.moneda);
    document.getElementById('spNeg').textContent  = p.tipo_negocio;

    // Características rápidas
    renderFeats(p);

    // Descripción
    if (p.descripcion_detallada) {
      document.getElementById('detDesc').textContent = p.descripcion_detallada;
    } else {
      document.getElementById('secDesc').style.display = 'none';
    }

    // Ficha técnica
    renderFicha(p);

    // Mapa referencia
    if (p.punto_referencia) {
      document.getElementById('mapaRef').textContent = p.punto_referencia;
    }

    // Tarjeta contacto
    renderContacto(p);

    // Perfil vendedor
    renderVendedor(p);
  }

  // ════════════════════════════════════════
  // GALERÍA
  // ════════════════════════════════════════
  function renderGaleria(imgs, tipo) {
    const grid = document.getElementById('galeriaGrid');

    if (!imgs.length) {
      grid.innerHTML = `<div class="gal-sin-foto">${getEmoji(tipo)}</div>`;
      return;
    }

    // Mostrar máx 5 fotos en el grid
    const visibles  = imgs.slice(0, 5);
    const restantes = imgs.length - visibles.length;

    grid.innerHTML = visibles.map((f, i) => `
      <div class="gal-item" data-index="${i}">
        <img src="${esc(f.url_foto_original || f.url_foto_miniatura)}"
             alt="Foto ${i + 1}"
             loading="${i === 0 ? 'eager' : 'lazy'}"
             onerror="this.parentNode.innerHTML='<div class=\\'gal-placeholder\\'>${getEmoji(tipo)}</div>'">
        <div class="gal-item-overlay"></div>
        ${i === visibles.length - 1 && restantes > 0
          ? `<button class="gal-ver-todas">+${restantes} fotos</button>`
          : ''}
      </div>
    `).join('');

    // Clicks en grid
    grid.querySelectorAll('.gal-item').forEach(el => {
      el.addEventListener('click', () => abrirLightbox(parseInt(el.dataset.index)));
    });
    grid.querySelector('.gal-ver-todas')?.addEventListener('click', (e) => {
      e.stopPropagation();
      abrirLightbox(0);
    });
  }

  // ── LIGHTBOX ──
  function abrirLightbox(idx) {
    if (!fotos.length) return;
    lbIndex = idx;
    actualizarLb();
    document.getElementById('lightbox').classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function cerrarLightbox() {
    document.getElementById('lightbox').classList.remove('open');
    document.body.style.overflow = '';
  }
  function actualizarLb() {
    const f = fotos[lbIndex];
    document.getElementById('lbImg').src       = f.url_foto_original || f.url_foto_miniatura;
    document.getElementById('lbCounter').textContent = `${lbIndex + 1} / ${fotos.length}`;
  }

  document.getElementById('lbClose')?.addEventListener('click', cerrarLightbox);
  document.getElementById('lbPrev')?.addEventListener('click', () => {
    lbIndex = (lbIndex - 1 + fotos.length) % fotos.length;
    actualizarLb();
  });
  document.getElementById('lbNext')?.addEventListener('click', () => {
    lbIndex = (lbIndex + 1) % fotos.length;
    actualizarLb();
  });
  document.getElementById('lightbox')?.addEventListener('click', (e) => {
    if (e.target.id === 'lightbox') cerrarLightbox();
  });
  document.addEventListener('keydown', (e) => {
    const lb = document.getElementById('lightbox');
    if (!lb.classList.contains('open')) return;
    if (e.key === 'Escape')      cerrarLightbox();
    if (e.key === 'ArrowLeft')  { lbIndex = (lbIndex - 1 + fotos.length) % fotos.length; actualizarLb(); }
    if (e.key === 'ArrowRight') { lbIndex = (lbIndex + 1) % fotos.length; actualizarLb(); }
  });

  // ════════════════════════════════════════
  // CARACTERÍSTICAS RÁPIDAS
  // ════════════════════════════════════════
  function renderFeats(p) {
    const items = [];

    if (p.num_habitaciones) items.push({ ico: '🚪', val: p.num_habitaciones, lbl: 'Habitaciones' });
    if (p.num_banos)        items.push({ ico: '🛁', val: p.num_banos,        lbl: 'Baños' });
    if (p.metros_construccion) items.push({ ico: '📐', val: fmt(p.metros_construccion) + ' m²', lbl: 'Construcción' });
    if (p.metros_terreno)   items.push({ ico: '🌿', val: fmt(p.metros_terreno) + ' m²',   lbl: 'Terreno' });
    if (p.tiene_estacionamiento == 1) items.push({ ico: '🚗', val: 'Sí', lbl: 'Estacionamiento' });
    if (p.tiene_piscina == 1)         items.push({ ico: '🏊', val: 'Sí', lbl: 'Piscina' });

    const cont = document.getElementById('detFeats');
    if (!items.length) { cont.style.display = 'none'; return; }

    cont.innerHTML = items.map(i => `
      <div class="feat-item">
        <span class="feat-ico">${i.ico}</span>
        <span class="feat-val">${i.val}</span>
        <span class="feat-lbl">${i.lbl}</span>
      </div>
    `).join('');
  }

  // ════════════════════════════════════════
  // FICHA TÉCNICA
  // ════════════════════════════════════════
  function renderFicha(p) {
    const items = [
      { lbl: 'Tipo de inmueble',  val: p.tipo_inmueble },
      { lbl: 'Tipo de negocio',   val: p.tipo_negocio },
      { lbl: 'Departamento',      val: p.departamento },
      { lbl: 'Municipio',         val: p.municipio },
      { lbl: 'Precio',            val: `${p.moneda} ${fmt(p.precio_pedido)}` },
      { lbl: 'Habitaciones',      val: p.num_habitaciones ?? '—' },
      { lbl: 'Baños',             val: p.num_banos        ?? '—' },
      { lbl: 'M² construcción',   val: p.metros_construccion ? fmt(p.metros_construccion) + ' m²' : '—' },
      { lbl: 'M² terreno',        val: p.metros_terreno      ? fmt(p.metros_terreno)      + ' m²' : '—' },
      { lbl: 'Estacionamiento',   val: p.tiene_estacionamiento == 1, bool: true },
      { lbl: 'Piscina',           val: p.tiene_piscina        == 1, bool: true },
      { lbl: 'Amueblado',         val: p.viene_amueblado      == 1, bool: true },
    ];

    document.getElementById('fichaGrid').innerHTML = items.map(i => {
      const valHtml = i.bool
        ? `<span class="ficha-val ${i.val ? 'ficha-val-si' : 'ficha-val-no'}">${i.val ? '✓ Sí' : '✗ No'}</span>`
        : `<span class="ficha-val">${esc(String(i.val))}</span>`;
      return `
        <div class="ficha-item">
          <span class="ficha-lbl">${i.lbl}</span>
          ${valHtml}
        </div>`;
    }).join('');
  }

  // ════════════════════════════════════════
  // MAPA LEAFLET
  // ════════════════════════════════════════
  function initMapa(lat, lng, titulo, ubicacion) {
    mapaInst = L.map('mapa', { scrollWheelZoom: false }).setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a>',
      maxZoom: 19,
    }).addTo(mapaInst);

    // Marcador personalizado navy/dorado
    const icono = L.divIcon({
      html: `
        <div style="
          width:44px;height:44px;border-radius:50% 50% 50% 0;
          background:var(--navy,#1A1953);
          border:3px solid #FFD45A;
          transform:rotate(-45deg);
          box-shadow:0 4px 14px rgba(26,25,83,.35);
          display:flex;align-items:center;justify-content:center;
        ">
          <span style="transform:rotate(45deg);font-size:1.1rem;">🏠</span>
        </div>`,
      className: '',
      iconSize:   [44, 44],
      iconAnchor: [22, 44],
      popupAnchor:[0, -44],
    });

    L.marker([lat, lng], { icon: icono })
      .addTo(mapaInst)
      .bindPopup(`
        <div style="font-family:'Inter',sans-serif;min-width:180px;">
          <div class="mapa-marker-label" style="font-size:.9rem;font-weight:700;color:#1A1953;margin-bottom:4px;">
            ${esc(titulo)}
          </div>
          <div style="font-size:.8rem;color:#6B7280;">${esc(ubicacion)}</div>
        </div>
      `, { maxWidth: 260 })
      .openPopup();

    // Activar scroll al entrar en el mapa
    document.getElementById('mapa').addEventListener('click', () => {
      mapaInst.scrollWheelZoom.enable();
    });
    document.getElementById('mapa').addEventListener('mouseleave', () => {
      mapaInst.scrollWheelZoom.disable();
    });
  }

  // ════════════════════════════════════════
  // TARJETA CONTACTO
  // ════════════════════════════════════════
  function renderContacto(p) {
    // Avatar
    const avatar = document.getElementById('ccAvatar');
    if (p.vendedor_foto) {
      avatar.innerHTML = `<img src="${esc(p.vendedor_foto)}" alt="${esc(p.vendedor_nombre)}">`;
    } else {
      avatar.textContent = iniciales(p.vendedor_nombre, p.vendedor_apellido);
    }
    document.getElementById('ccNombre').textContent = `${p.vendedor_nombre} ${p.vendedor_apellido}`;

    // Stats contacto
    document.getElementById('ccStats').innerHTML = `
      <div class="cc-stat">
        <span class="cc-stat-val">${p.vendedor_propiedades ?? 0}</span>
        <span class="cc-stat-lbl">Propiedades</span>
      </div>
      <div class="cc-stat">
        <span class="cc-stat-val">✓</span>
        <span class="cc-stat-lbl">Verificado</span>
      </div>
    `;

    if (p.vendedor_descripcion) {
      document.getElementById('ccDesc').textContent = p.vendedor_descripcion;
    } else {
      document.getElementById('ccDesc').style.display = 'none';
    }

    // Botones de contacto
    const tel = p.vendedor_telefono?.replace(/\D/g, '') ?? '';
    const msg = encodeURIComponent(`Hola, me interesa la propiedad: ${p.titulo_anuncio}`);

    const btnTel  = document.getElementById('btnTel');
    const btnWa   = document.getElementById('btnWa');
    const btnMail = document.getElementById('btnMail');

    if (p.vendedor_telefono) {
      btnTel.href = `tel:${p.vendedor_telefono}`;
      btnTel.addEventListener('click', () => registrarContacto('telefono'));
      btnWa.href  = `https://wa.me/${tel}?text=${msg}`;
      btnWa.target = '_blank';
      btnWa.addEventListener('click', () => registrarContacto('whatsapp'));
    } else {
      btnTel.style.display = 'none';
      btnWa.style.display  = 'none';
    }

    if (p.vendedor_correo) {
      btnMail.href = `mailto:${p.vendedor_correo}?subject=${encodeURIComponent('Consulta sobre: ' + p.titulo_anuncio)}&body=${encodeURIComponent('Hola, estoy interesado en la propiedad "' + p.titulo_anuncio + '" publicada en PP Bienes Raíces. Me gustaría recibir más información.')}`;
      btnMail.addEventListener('click', () => registrarContacto('correo'));
    } else {
      btnMail.style.display = 'none';
    }
  }

  // ════════════════════════════════════════
  // PERFIL VENDEDOR (estilo tienda FB)
  // ════════════════════════════════════════
  function renderVendedor(p) {
    // Avatar grande
    const vcAv = document.getElementById('vcAvatar');
    if (p.vendedor_foto) {
      vcAv.innerHTML = `<img src="${esc(p.vendedor_foto)}" alt="${esc(p.vendedor_nombre)}">`;
    } else {
      vcAv.textContent = iniciales(p.vendedor_nombre, p.vendedor_apellido);
    }

    document.getElementById('vcNombre').textContent =
      `${p.vendedor_nombre} ${p.vendedor_apellido}`;

    // Stats
    const desde = p.vendedor_desde
      ? new Date(p.vendedor_desde).getFullYear()
      : '—';
    document.getElementById('vcStats').innerHTML = `
      <div class="vc-stat">
        <span class="vc-stat-val">${p.vendedor_propiedades ?? 0}</span>
        <span class="vc-stat-lbl">Propiedades activas</span>
      </div>
      <div class="vc-stat">
        <span class="vc-stat-val">✓</span>
        <span class="vc-stat-lbl">Verificado</span>
      </div>
      <div class="vc-stat">
        <span class="vc-stat-val">${desde}</span>
        <span class="vc-stat-lbl">Desde</span>
      </div>
    `;

    if (p.vendedor_descripcion) {
      document.getElementById('vcDesc').textContent = p.vendedor_descripcion;
    } else {
      document.getElementById('vcDesc').style.display = 'none';
    }

    document.getElementById('vcDesde').textContent =
      `Miembro desde ${p.vendedor_desde
        ? new Date(p.vendedor_desde).toLocaleDateString('es-SV', { month: 'long', year: 'numeric' })
        : 'antes de 2024'}`;
  }

  // ════════════════════════════════════════
  // REGISTRAR CONTACTO
  // ════════════════════════════════════════
  async function registrarContacto(canal) {
    try {
      await fetch(`${DETALLE_URL}?id=${PROP_ID}&contacto=${canal}`);
    } catch (_) {}
  }

  // ════════════════════════════════════════
  // HELPERS
  // ════════════════════════════════════════
  function mostrarError() {
    document.getElementById('skeletonWrap').style.display = 'none';
    document.getElementById('errorWrap').style.display    = 'flex';
  }

  function formatPrecio(precio, negocio, moneda) {
    const num = parseFloat(precio).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    const sfx  = negocio === 'Alquiler' || negocio === 'Alquiler con opción a compra'
      ? '<small>/mes</small>' : '<small>venta</small>';
    return `${moneda ?? 'USD'} ${num} ${sfx}`;
  }

  function fmt(n) {
    return parseFloat(n).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
  }

  function esc(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function iniciales(nombre, apellido) {
    return ((nombre?.[0] ?? '') + (apellido?.[0] ?? '')).toUpperCase();
  }

  function getEmoji(tipo) {
    const map = { 'Casa':'🏠', 'Apartamento':'🏢', 'Local comercial':'🏪', 'Terreno':'🌿', 'Bodega':'🏭' };
    return map[tipo] ?? '🏡';
  }

  // ── Inicializar ──
  init();

})();