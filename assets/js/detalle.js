/**
 * detalle.js — PP Bienes Raíces
 * Con botón Waze, Google Maps e iconos Font Awesome
 */

(function () {
  'use strict';

  if (!PROP_ID) { mostrarError(); return; }

  let fotos    = [];
  let lbIndex  = 0;
  let mapaInst = null;

  /* ── Inicializar ── */
  async function init() {
    try {
      const res  = await fetch(`${DETALLE_URL}?id=${encodeURIComponent(PROP_ID)}`);
      const data = await res.json();
      if (!data.ok) { mostrarError(); return; }
      fotos = data.fotos || [];
      renderTodo(data.propiedad, fotos);
      document.getElementById('skeletonWrap').style.display = 'none';
      document.getElementById('mainContent').style.display  = 'block';
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
      console.error(err); mostrarError();
    }
  }

  /* ── Render principal ── */
  function renderTodo(p, imgs) {
    document.title = p.titulo_anuncio + ' | PP Bienes Raíces';
    renderGaleria(imgs, p.tipo_inmueble);

    const esVenta = p.tipo_negocio === 'Venta';
    const tagNeg  = esVenta ? 'det-tag-venta' : 'det-tag-renta';
    const texNeg  = esVenta ? 'VENTA' : (p.tipo_negocio === 'Alquiler' ? 'RENTA' : 'ALQ. C/ OPCIÓN');
    document.getElementById('detTags').innerHTML = `
      <span class="det-tag ${tagNeg}"><i class="fas ${esVenta?'fa-tag':'fa-key'}"></i> ${texNeg}</span>
      <span class="det-tag det-tag-tipo"><i class="fas ${getFAIcon(p.tipo_inmueble)}"></i> ${esc(p.tipo_inmueble)}</span>
      ${p.es_anuncio_destacado==1 ? '<span class="det-tag det-tag-star"><i class="fas fa-star"></i> Destacada</span>' : ''}
    `;

    document.getElementById('detTitulo').textContent     = p.titulo_anuncio;
    document.getElementById('ubicacionTexto').textContent =
      `${p.direccion_exacta ? p.direccion_exacta + ' — ' : ''}${p.municipio}, ${p.departamento}, El Salvador`;
    document.getElementById('detPrecio').innerHTML       = formatPrecio(p.precio_pedido, p.tipo_negocio, p.moneda);
    document.getElementById('spPrecio').innerHTML        = formatPrecio(p.precio_pedido, p.tipo_negocio, p.moneda);
    document.getElementById('spNeg').textContent         = p.tipo_negocio;

    renderFeats(p);

    if (p.descripcion_detallada) {
      document.getElementById('detDesc').textContent = p.descripcion_detallada;
    } else {
      document.getElementById('secDesc').style.display = 'none';
    }

    renderFicha(p);
    if (p.punto_referencia) document.getElementById('mapaRef').textContent = p.punto_referencia;
    renderContacto(p);
    renderVendedor(p);
  }

  /* ── Botones de acción rápida (Waze + Google Maps) ── */
  function renderAcciones(p) {
    const cont = document.getElementById('detAcciones');
    if (!cont) return;
    const lat = p.latitud  ? parseFloat(p.latitud)  : null;
    const lng = p.longitud ? parseFloat(p.longitud) : null;
    if (!lat || !lng) { cont.style.display='none'; return; }

    cont.innerHTML = `
      <a href="https://waze.com/ul?ll=${lat},${lng}&navigate=yes&zoom=17"
         target="_blank" rel="noopener" class="btn-accion btn-accion-waze">
        <i class="fas fa-road"></i> Cómo llegar en Waze
      </a>
      <a href="https://www.google.com/maps/search/?api=1&query=${lat},${lng}"
         target="_blank" rel="noopener" class="btn-accion btn-accion-gmaps">
        <i class="fas fa-map"></i> Google Maps
      </a>
    `;
  }

  /* ── Galería ── */
  /* ── CARRUSEL ── */
  let carruselIdx   = 0;
  let carruselFotos = [];

  function renderGaleria(imgs, tipo) {
    carruselFotos = imgs;
    const main   = document.getElementById('carruselMain');
    const thumbs = document.getElementById('carruselThumbs');
    if (!main) return;

    if (!imgs.length) {
      main.insertAdjacentHTML('afterbegin',
        `<div class="carrusel-main-placeholder">
           <i class="fas ${getFAIcon(tipo)}"></i>
           <span>${esc(tipo)||'Propiedad'}</span>
         </div>`);
      ['carruselPrev','carruselNext','carruselAmpliar','carruselCounter']
        .forEach(id => { const el=document.getElementById(id); if(el) el.style.display='none'; });
      if (thumbs) thumbs.style.display = 'none';
      return;
    }

    // Imagen principal
    const imgEl = document.createElement('img');
    imgEl.className = 'carrusel-main-img';
    imgEl.id  = 'carruselImgMain';
    imgEl.src = imgs[0].url_foto_original || imgs[0].url_foto_miniatura;
    imgEl.alt = 'Foto 1';
    imgEl.loading = 'eager';
    main.insertAdjacentElement('afterbegin', imgEl);
    actualizarCarrusel(0);

    // Miniaturas
    if (thumbs) {
      thumbs.innerHTML = imgs.map((f,i) => {
        const src = f.url_foto_miniatura || f.url_foto_original;
        return `<div class="carrusel-thumb ${i===0?'active':''}" data-idx="${i}">
          ${src
            ? `<img src="${esc(src)}" alt="Foto ${i+1}" loading="lazy">`
            : `<div class="carrusel-thumb-placeholder"><i class="fas ${getFAIcon(tipo)}"></i></div>`}
        </div>`;
      }).join('');
      thumbs.querySelectorAll('.carrusel-thumb').forEach(th => {
        th.addEventListener('click', () => irAFoto(parseInt(th.dataset.idx)));
      });
      if (imgs.length <= 1) thumbs.style.display = 'none';
    }

    if (imgs.length <= 1) {
      ['carruselPrev','carruselNext'].forEach(id => {
        const el=document.getElementById(id); if(el) el.style.display='none';
      });
    }

    // Flechas
    document.getElementById('carruselPrev')?.addEventListener('click', () =>
      irAFoto((carruselIdx - 1 + carruselFotos.length) % carruselFotos.length));
    document.getElementById('carruselNext')?.addEventListener('click', () =>
      irAFoto((carruselIdx + 1) % carruselFotos.length));
    document.getElementById('carruselAmpliar')?.addEventListener('click', () =>
      abrirLightbox(carruselIdx));

    // Swipe táctil
    let txStart = 0;
    main.addEventListener('touchstart', e => { txStart = e.touches[0].clientX; });
    main.addEventListener('touchend', e => {
      const diff = txStart - e.changedTouches[0].clientX;
      if (Math.abs(diff) > 40)
        irAFoto(diff > 0
          ? (carruselIdx+1) % carruselFotos.length
          : (carruselIdx-1+carruselFotos.length) % carruselFotos.length);
    });
  }

  function irAFoto(idx) {
    carruselIdx = idx;
    actualizarCarrusel(idx);
  }

  function actualizarCarrusel(idx) {
    const imgs = carruselFotos;
    if (!imgs.length) return;
    const imgEl = document.getElementById('carruselImgMain');
    if (imgEl) {
      imgEl.style.opacity = '0';
      setTimeout(() => {
        imgEl.src = imgs[idx].url_foto_original || imgs[idx].url_foto_miniatura;
        imgEl.alt = `Foto ${idx+1}`;
        imgEl.style.opacity = '1';
      }, 150);
    }
    const counter = document.getElementById('carruselCounter');
    if (counter) counter.textContent = `${idx+1} / ${imgs.length}`;
    document.querySelectorAll('.carrusel-thumb').forEach((th,i) =>
      th.classList.toggle('active', i===idx));
    document.querySelector(`.carrusel-thumb[data-idx="${idx}"]`)
      ?.scrollIntoView({behavior:'smooth', block:'nearest', inline:'center'});
  }

  function abrirLightbox(idx) {
    if (!fotos.length) return;
    lbIndex = idx; actualizarLb();
    const lb = document.getElementById('lightbox');
    lb.style.display = 'flex';
    lb.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function cerrarLightbox() {
    const lb = document.getElementById('lightbox');
    lb.classList.remove('open');
    lb.style.display = 'none';
    document.body.style.overflow = '';
    document.body.style.position = '';
  }
  function actualizarLb() {
    const f = fotos[lbIndex];
    document.getElementById('lbImg').src = f.url_foto_original || f.url_foto_miniatura;
    document.getElementById('lbCounter').textContent = `${lbIndex+1} / ${fotos.length}`;
  }

  document.getElementById('lbClose')?.addEventListener('click', cerrarLightbox);
  document.getElementById('lbPrev')?.addEventListener('click', () => {
    lbIndex = (lbIndex-1+fotos.length)%fotos.length; actualizarLb();
  });
  document.getElementById('lbNext')?.addEventListener('click', () => {
    lbIndex = (lbIndex+1)%fotos.length; actualizarLb();
  });
  document.getElementById('lightbox')?.addEventListener('click', e => {
    if (e.target.id==='lightbox') cerrarLightbox();
  });
  document.addEventListener('keydown', e => {
    const lb = document.getElementById('lightbox');
    if (!lb.classList.contains('open')) return;
    if (e.key==='Escape')      cerrarLightbox();
    if (e.key==='ArrowLeft')  { lbIndex=(lbIndex-1+fotos.length)%fotos.length; actualizarLb(); }
    if (e.key==='ArrowRight') { lbIndex=(lbIndex+1)%fotos.length; actualizarLb(); }
  });

  /* ── Características rápidas con FA ── */
  function renderFeats(p) {
    const items = [];
    if (p.num_habitaciones)         items.push({ico:'fa-bed',              val:p.num_habitaciones,               lbl:'Habitaciones'});
    if (p.num_banos)                items.push({ico:'fa-bath',             val:p.num_banos,                      lbl:'Baños'});
    if (p.metros_construccion)      items.push({ico:'fa-ruler-combined',   val:fmt(p.metros_construccion)+' m²', lbl:'Construcción'});
    if (p.metros_terreno)           items.push({ico:'fa-expand-arrows-alt',val:fmt(p.metros_terreno)+' m²',      lbl:'Terreno'});
    if (p.tiene_estacionamiento==1) items.push({ico:'fa-car',              val:'Sí',                             lbl:'Estacionamiento'});
    if (p.tiene_piscina==1)         items.push({ico:'fa-swimming-pool',    val:'Sí',                             lbl:'Piscina'});

    const cont = document.getElementById('detFeats');
    if (!items.length) { cont.style.display='none'; return; }
    cont.innerHTML = items.map(i => `
      <div class="feat-item">
        <span class="feat-ico"><i class="fas ${i.ico}"></i></span>
        <span class="feat-val">${i.val}</span>
        <span class="feat-lbl">${i.lbl}</span>
      </div>
    `).join('');
  }

  /* ── Ficha técnica ── */
  function renderFicha(p) {
    const items = [
      {lbl:'Tipo de inmueble', val:p.tipo_inmueble},
      {lbl:'Tipo de negocio',  val:p.tipo_negocio},
      {lbl:'Departamento',     val:p.departamento},
      {lbl:'Municipio',        val:p.municipio},
      {lbl:'Precio',           val:`${p.moneda} ${fmt(p.precio_pedido)}`},
      {lbl:'Habitaciones',     val:p.num_habitaciones??'—'},
      {lbl:'Baños',            val:p.num_banos??'—'},
      {lbl:'M² construcción',  val:p.metros_construccion?fmt(p.metros_construccion)+' m²':'—'},
      {lbl:'M² terreno',       val:p.metros_terreno?fmt(p.metros_terreno)+' m²':'—'},
      {lbl:'Estacionamiento',  val:p.tiene_estacionamiento==1, bool:true},
      {lbl:'Piscina',          val:p.tiene_piscina==1,         bool:true},
      {lbl:'Amueblado',        val:p.viene_amueblado==1,       bool:true},
    ];
    document.getElementById('fichaGrid').innerHTML = items.map(i => {
      const valHtml = i.bool
        ? `<span class="ficha-val ${i.val?'ficha-val-si':'ficha-val-no'}">${i.val?'<i class="fas fa-check"></i> Sí':'<i class="fas fa-times"></i> No'}</span>`
        : `<span class="ficha-val">${esc(String(i.val))}</span>`;
      return `<div class="ficha-item"><span class="ficha-lbl">${i.lbl}</span>${valHtml}</div>`;
    }).join('');
  }

  /* ── Mapa Leaflet ── */
  function initMapa(lat, lng, titulo, ubicacion) {
    // Botones bajo el mapa — flat premium
    const mapaAcc = document.getElementById('mapaAcciones');
    if (mapaAcc) {
      mapaAcc.innerHTML = `
        <a href="https://waze.com/ul?ll=${lat},${lng}&navigate=yes&zoom=17"
           target="_blank" rel="noopener" class="btn-accion btn-accion-waze">
          <i class="fas fa-road"></i>
          <span>
            <span class="btn-accion-label">Cómo llegar</span>
            <span class="btn-accion-sub">Abrir en Waze</span>
          </span>
        </a>
        <a href="https://www.google.com/maps/search/?api=1&query=${lat},${lng}"
           target="_blank" rel="noopener" class="btn-accion btn-accion-gmaps">
          <i class="fas fa-map-marked-alt"></i>
          <span>
            <span class="btn-accion-label">Ver ubicación</span>
            <span class="btn-accion-sub">Abrir en Google Maps</span>
          </span>
        </a>
      `;
    }

    mapaInst = L.map('mapa', {scrollWheelZoom:false}).setView([lat,lng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution:'© <a href="https://openstreetmap.org">OpenStreetMap</a>', maxZoom:19,
    }).addTo(mapaInst);

    const icono = L.divIcon({
      html:`<div style="width:44px;height:44px;border-radius:50% 50% 50% 0;background:#1A1953;border:3px solid #FFD45A;transform:rotate(-45deg);box-shadow:0 4px 14px rgba(26,25,83,.35);"></div>`,
      className:'', iconSize:[44,44], iconAnchor:[22,44], popupAnchor:[0,-44],
    });

    L.marker([lat,lng], {icon:icono}).addTo(mapaInst)
      .bindPopup(`<div style="font-family:'Inter',sans-serif;min-width:180px;">
        <div style="font-size:.9rem;font-weight:700;color:#1A1953;margin-bottom:4px;">${esc(titulo)}</div>
        <div style="font-size:.8rem;color:#6B7280;">${esc(ubicacion)}</div>
      </div>`, {maxWidth:260})
      .openPopup();

    document.getElementById('mapa')?.addEventListener('click', () => mapaInst.scrollWheelZoom.enable());
    document.getElementById('mapa')?.addEventListener('mouseleave', () => mapaInst.scrollWheelZoom.disable());
  }

  /* ── Contacto ── */
  function renderContacto(p) {
    const avatar = document.getElementById('ccAvatar');
    if (p.vendedor_foto) {
      avatar.innerHTML = `<img src="${esc(p.vendedor_foto)}" alt="${esc(p.vendedor_nombre)}">`;
    } else {
      avatar.textContent = iniciales(p.vendedor_nombre, p.vendedor_apellido);
    }
    document.getElementById('ccNombre').textContent = `${p.vendedor_nombre} ${p.vendedor_apellido}`;
    document.getElementById('ccStats').innerHTML = `
      <div class="cc-stat"><span class="cc-stat-val">${p.vendedor_propiedades??0}</span><span class="cc-stat-lbl">Propiedades</span></div>
      <div class="cc-stat"><span class="cc-stat-val"><i class="fas fa-check" style="color:var(--green)"></i></span><span class="cc-stat-lbl">Verificado</span></div>
    `;
    if (p.vendedor_descripcion) {
      document.getElementById('ccDesc').textContent = p.vendedor_descripcion;
    } else {
      document.getElementById('ccDesc').style.display='none';
    }

    const tel  = p.vendedor_telefono?.replace(/\D/g,'') ?? '';
    const msg  = encodeURIComponent(`Hola, me interesa la propiedad: ${p.titulo_anuncio}`);
    const btnTel  = document.getElementById('btnTel');
    const btnWa   = document.getElementById('btnWa');
    const btnMail = document.getElementById('btnMail');

    if (p.vendedor_telefono) {
      btnTel.href = `tel:${p.vendedor_telefono}`;
      btnTel.addEventListener('click', () => registrarContacto('telefono'));
      btnWa.href = `https://wa.me/${tel}?text=${msg}`;
      btnWa.target = '_blank';
      btnWa.addEventListener('click', () => registrarContacto('whatsapp'));
    } else {
      btnTel.style.display='none'; btnWa.style.display='none';
    }
    if (p.vendedor_correo) {
      btnMail.href = `mailto:${p.vendedor_correo}?subject=${encodeURIComponent('Consulta sobre: '+p.titulo_anuncio)}`;
      btnMail.addEventListener('click', () => registrarContacto('correo'));
    } else {
      btnMail.style.display='none';
    }
  }

  /* ── Vendedor ── */
  function renderVendedor(p) {
    const vcAv = document.getElementById('vcAvatar');
    if (p.vendedor_foto) {
      vcAv.innerHTML = `<img src="${esc(p.vendedor_foto)}" alt="${esc(p.vendedor_nombre)}">`;
    } else {
      vcAv.textContent = iniciales(p.vendedor_nombre, p.vendedor_apellido);
    }
    document.getElementById('vcNombre').textContent = `${p.vendedor_nombre} ${p.vendedor_apellido}`;
    const desde = p.vendedor_desde ? new Date(p.vendedor_desde).getFullYear() : '—';
    document.getElementById('vcStats').innerHTML = `
      <div class="vc-stat"><span class="vc-stat-val">${p.vendedor_propiedades??0}</span><span class="vc-stat-lbl">Propiedades</span></div>
      <div class="vc-stat"><span class="vc-stat-val"><i class="fas fa-check" style="color:var(--green)"></i></span><span class="vc-stat-lbl">Verificado</span></div>
      <div class="vc-stat"><span class="vc-stat-val">${desde}</span><span class="vc-stat-lbl">Desde</span></div>
    `;
    if (p.vendedor_descripcion) {
      document.getElementById('vcDesc').textContent = p.vendedor_descripcion;
    } else {
      document.getElementById('vcDesc').style.display='none';
    }
    document.getElementById('vcDesde').textContent =
      `Miembro desde ${p.vendedor_desde
        ? new Date(p.vendedor_desde).toLocaleDateString('es-SV',{month:'long',year:'numeric'})
        : 'antes de 2024'}`;
  }

  /* ── Registrar contacto ── */
  async function registrarContacto(canal) {
    try { await fetch(`${DETALLE_URL}?id=${PROP_ID}&contacto=${canal}`); } catch(_){}
  }

  /* ── Helpers ── */
  function mostrarError() {
    document.getElementById('skeletonWrap').style.display = 'none';
    document.getElementById('errorWrap').style.display    = 'flex';
  }
  function formatPrecio(precio, negocio, moneda) {
    const num = parseFloat(precio).toLocaleString('en-US',{minimumFractionDigits:0,maximumFractionDigits:0});
    const sfx = negocio==='Alquiler'||negocio==='Alquiler con opción a compra'
      ? '<small>/mes</small>' : '<small>venta</small>';
    return `${moneda??'USD'} ${num} ${sfx}`;
  }
  function fmt(n) { return parseFloat(n).toLocaleString('en-US',{minimumFractionDigits:0,maximumFractionDigits:0}); }
  function esc(str) {
    if(!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
  function iniciales(nombre,apellido) { return ((nombre?.[0]??'')+(apellido?.[0]??'')).toUpperCase(); }
  function getFAIcon(tipo) {
    const map = {'Casa':'fa-home','Apartamento':'fa-building','Local comercial':'fa-store',
                 'Terreno':'fa-mountain','Bodega':'fa-warehouse','Finca':'fa-leaf'};
    return map[tipo] || 'fa-home';
  }

  init();
})();