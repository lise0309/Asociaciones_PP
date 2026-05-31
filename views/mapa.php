<?php session_start(); ?>
<?php
/**
 * MAPA DE PROPIEDADES — PP Bienes Raíces
 * views/mapa.php
 * Muestra propiedades en mapa Leaflet con búsqueda por área
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mapa de Propiedades | PP Bienes Raíces</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <!-- Font Awesome 5 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <!-- Navbar CSS con ruta absoluta para funcionar desde views/ -->
  <link rel="stylesheet" href="/Asociaciones_PP/assets/css/navbar.css">
  <!-- Leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
  <!-- Leaflet MarkerCluster -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">


  <style>
    :root {
      --navy:#1A1953; --navy-dark:#0F0F2E; --navy-mid:#252477;
      --gold:#FFD45A; --gold-dark:#E6B800; --gold-soft:rgba(255,212,90,.15);
      --white:#FFFCFB; --off:#EEEDF5; --text:#1C1C2E; --muted:#6366a0;
      --border:#D8D6EC; --t:.18s cubic-bezier(.4,0,.2,1);
      --font-d:'Playfair Display',serif; --font-b:'Inter',sans-serif;
    }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:var(--font-b);background:var(--off);color:var(--text);padding-top:130px;}

    /* ── TOP BAR ── */
    .mapa-topbar {
      background:var(--navy-dark);
      border-bottom:3px solid var(--gold);
      padding:14px 28px;
      display:flex; align-items:center; gap:16px; flex-wrap:wrap;
      min-height:64px;
    }
    .mapa-topbar h1 {
      font-family:var(--font-d); font-size:1rem; font-weight:800;
      color:var(--gold); white-space:nowrap;
      display:flex; align-items:center; gap:8px;
    }
    .mapa-topbar h1 i { font-size:.9rem; }

    .mapa-search-group {
      display:flex; gap:8px; flex:1; max-width:600px;
    }
    .mapa-search-input {
      flex:1; padding:9px 14px;
      background:rgba(255,255,255,.08); border:1.5px solid rgba(255,255,255,.15);
      color:var(--white); font-family:var(--font-b); font-size:.82rem;
      outline:none; transition:border-color var(--t);
    }
    .mapa-search-input:focus { border-color:var(--gold); }
    .mapa-search-input::placeholder { color:rgba(255,255,255,.4); }
    .btn-mapa-buscar {
      padding:9px 18px; background:var(--gold); color:var(--navy-dark);
      border:none; font-family:var(--font-b); font-size:.78rem; font-weight:800;
      cursor:pointer; transition:all var(--t); text-transform:uppercase;
      letter-spacing:.06em; box-shadow:3px 3px 0 rgba(0,0,0,.25);
      display:flex; align-items:center; gap:7px;
    }
    .btn-mapa-buscar:hover { background:#ffe070; transform:translate(-1px,-1px); }
    .btn-mapa-buscar i { font-size:.85rem; }

    .mapa-filtros-grupo {
      display:flex; gap:8px; align-items:center;
    }
    .mapa-select {
      padding:8px 28px 8px 10px;
      background:rgba(255,255,255,.07); border:1.5px solid rgba(255,255,255,.12);
      color:var(--white); font-family:var(--font-b); font-size:.78rem;
      outline:none; appearance:none; cursor:pointer;
      background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%23FFD45A'%3E%3Cpath fill-rule='evenodd' d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z' clip-rule='evenodd'/%3E%3C/svg%3E");
      background-repeat:no-repeat; background-position:right 7px center; background-size:12px;
      transition:border-color var(--t);
    }
    .mapa-select:focus { border-color:var(--gold); }
    .mapa-select option { background:var(--navy-dark); }

    .btn-volver {
      padding:8px 16px; background:transparent;
      border:1.5px solid rgba(255,255,255,.2); color:rgba(255,255,255,.6);
      font-family:var(--font-b); font-size:.75rem; font-weight:600;
      cursor:pointer; transition:all var(--t); white-space:nowrap;
      display:flex; align-items:center; gap:6px;
    }
    .btn-volver:hover { border-color:var(--gold); color:var(--gold); }
    .btn-volver i { font-size:.8rem; }

    /* ── LAYOUT MAPA + SIDEBAR ── */
    .mapa-layout {
      display:grid; grid-template-columns:360px 1fr;
      height:calc(100vh - 194px);
    }
    @media(max-width:900px){ .mapa-layout { grid-template-columns:1fr; } }

    /* Sidebar de resultados */
    .mapa-sidebar {
      background:var(--navy);
      overflow-y:auto; border-right:3px solid var(--navy-mid);
      display:flex; flex-direction:column;
    }
    .mapa-sidebar-head {
      padding:14px 18px;
      background:var(--navy-dark); border-bottom:2px solid var(--navy-mid);
      position:sticky; top:0; z-index:1;
      display:flex; align-items:center; justify-content:space-between;
    }
    .mapa-sidebar-head span {
      font-size:.7rem; font-weight:800; color:var(--gold);
      text-transform:uppercase; letter-spacing:.1em;
    }
    .mapa-sidebar-count {
      font-size:.75rem; color:rgba(255,255,255,.5); font-weight:500;
    }

    .mapa-resultado-item {
      padding:16px 18px; border-bottom:1px solid rgba(255,255,255,.08);
      cursor:pointer; transition:background var(--t);
      display:flex; gap:14px; align-items:flex-start;
    }
    .mapa-resultado-item:hover { background:rgba(255,212,90,.08); }
    .mapa-resultado-item.activo { background:rgba(255,212,90,.12); border-left:3px solid var(--gold); }

    .mapa-res-ico {
      width:38px; height:38px; flex-shrink:0; background:var(--navy-mid);
      display:flex; align-items:center; justify-content:center;
    }
    .mapa-res-ico i { font-size:.9rem; color:var(--gold); }

    .mapa-res-info { flex:1; min-width:0; }
    .mapa-res-titulo {
      font-size:.8rem; font-weight:700; color:var(--white);
      white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
      margin-bottom:3px;
    }
    .mapa-res-ubicacion {
      font-size:.7rem; color:rgba(255,255,255,.45);
      display:flex; align-items:center; gap:4px; margin-bottom:5px;
    }
    .mapa-res-ubicacion i { font-size:.65rem; color:var(--gold-dark); }
    .mapa-res-precio {
      font-size:.85rem; font-weight:800; color:var(--gold);
      font-family:var(--font-d);
      font-variant-numeric: lining-nums tabular-nums;
    }
    .mapa-res-badge {
      font-size:.58rem; font-weight:800; text-transform:uppercase;
      padding:2px 7px; background:var(--navy-dark); color:var(--gold);
      border-left:2px solid var(--gold); margin-bottom:4px;
      display:inline-block;
    }

    .mapa-empty {
      flex:1; display:flex; flex-direction:column;
      align-items:center; justify-content:center; gap:10px;
      padding:32px 20px; color:rgba(255,255,255,.3); text-align:center;
    }
    .mapa-empty i { font-size:2rem; color:rgba(255,255,255,.1); }
    .mapa-empty p { font-size:.82rem; }

    /* Mapa Leaflet */
    #mapaLeaflet {
      width:100%; height:100%; z-index:0;
    }

    /* Popup personalizado */
    .popup-prop { font-family:var(--font-b); min-width:200px; }
    .popup-prop-titulo { font-weight:700; font-size:.875rem; color:var(--navy); margin-bottom:4px; }
    .popup-prop-precio { font-weight:800; font-size:1rem; color:var(--navy); margin-bottom:6px; font-family:var(--font-d); font-variant-numeric: lining-nums tabular-nums; }
    .popup-prop-loc { font-size:.75rem; color:var(--muted); margin-bottom:8px; display:flex; align-items:center; gap:4px; }
    .popup-prop-btn {
      display:block; text-align:center; padding:7px;
      background:var(--navy); color:var(--gold);
      font-size:.72rem; font-weight:800; text-transform:uppercase;
      letter-spacing:.08em; text-decoration:none; transition:background .15s;
    }
    .popup-prop-btn:hover { background:var(--navy-mid); }

    /* Marcador custom */
    .marker-prop {
      background:var(--navy); border:2px solid var(--gold);
      padding:3px 8px; font-size:.65rem; font-weight:800;
      color:var(--gold); white-space:nowrap; box-shadow:2px 2px 0 rgba(0,0,0,.3);
      position:relative; font-family:var(--font-d);
      font-variant-numeric: lining-nums tabular-nums;
    }
    .marker-prop::after {
      content:''; position:absolute; bottom:-7px; left:50%; transform:translateX(-50%);
      border:4px solid transparent; border-top-color:var(--navy);
    }
    .marker-prop.destacado { background:var(--gold); color:var(--navy-dark); border-color:var(--navy); }
    .marker-prop.destacado::after { border-top-color:var(--gold); }

    /* Loading */
    .mapa-loading {
      position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);
      z-index:1000; background:var(--navy-dark); padding:20px 28px;
      display:flex; align-items:center; gap:12px;
      border:2px solid var(--gold); color:var(--white);
      font-size:.82rem; font-weight:600;
    }
    .spinner-sm {
      width:20px; height:20px;
      border:2px solid rgba(255,212,90,.2); border-top-color:var(--gold);
      border-radius:50%; animation:spin .7s linear infinite;
    }
    @keyframes spin { to { transform:rotate(360deg); } }
  </style>
</head>
<body>
  <?php include __DIR__ . '/layouts/navbar.php'; ?>

  <!-- Top bar -->
  <div class="mapa-topbar">
    <h1><i class="fas fa-map-marked-alt"></i> Mapa de propiedades</h1>

    <div class="mapa-search-group">
      <input type="text" class="mapa-search-input" id="mapaSearch"
             placeholder="Buscar por zona, municipio o departamento..." />
      <button class="btn-mapa-buscar" id="btnMapaBuscar">
        <i class="fas fa-search"></i> Buscar
      </button>
    </div>

    <div class="mapa-filtros-grupo">
      <select class="mapa-select" id="mapaFiltroTipo">
        <option value="">Todos los tipos</option>
        <option value="Casa">Casa</option>
        <option value="Apartamento">Apartamento</option>
        <option value="Terreno">Terreno</option>
        <option value="Local comercial">Local comercial</option>
        <option value="Bodega">Bodega</option>
      </select>
      <select class="mapa-select" id="mapaFiltroNegocio">
        <option value="">Venta y renta</option>
        <option value="venta">Solo venta</option>
        <option value="alquiler">Solo renta</option>
      </select>
    </div>

    <button class="btn-volver" onclick="window.location.href='../index.php'">
      <i class="fas fa-arrow-left"></i> Volver
    </button>
  </div>

  <!-- Layout -->
  <div class="mapa-layout">

    <!-- Sidebar resultados -->
    <div class="mapa-sidebar" id="mapaSidebar">
      <div class="mapa-sidebar-head">
        <span><i class="fas fa-list" style="margin-right:6px;"></i> Propiedades</span>
        <span class="mapa-sidebar-count" id="mapaCount">Cargando...</span>
      </div>
      <div id="mapaListado">
        <div class="mapa-empty">
          <div class="spinner-sm"></div>
          <p>Cargando propiedades...</p>
        </div>
      </div>
    </div>

    <!-- Mapa -->
    <div style="position:relative;">
      <div id="mapaLeaflet"></div>
      <div class="mapa-loading" id="mapaLoading" style="display:none;">
        <div class="spinner-sm"></div> Buscando propiedades...
      </div>
    </div>

  </div>

  <!-- footer omitido en vista mapa -->

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
  <script>
  'use strict';

  const API = '/Asociaciones_PP/controllers/propiedadindexcontroller.php';
  let mapa, markerGroup, propiedades = [], marcadorSeleccionado = null;

  /* ── Iconos por tipo ── */
  const faIcons = {
    'Casa':'fa-home','Apartamento':'fa-building','Local comercial':'fa-store',
    'Terreno':'fa-mountain','Bodega':'fa-warehouse','Finca':'fa-leaf',
  };
  function getIcon(tipo) { return faIcons[tipo] || 'fa-home'; }

  /* ── Helper: obtener lat/lng de una propiedad ── */
  function getLat(p) { return parseFloat(p.latitud || p.lat || p.latitude || 0); }
  function getLng(p) { return parseFloat(p.longitud || p.lng || p.longitude || 0); }
  function tieneCoordenadas(p) { const la=getLat(p),lo=getLng(p); return la!==0 && lo!==0 && !isNaN(la) && !isNaN(lo); }

  /* ── Inicializar mapa ── */
  function initMapa() {
    mapa = L.map('mapaLeaflet', {
      center: [13.6929, -89.2182], // El Salvador centro
      zoom: 9,
      zoomControl: true,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap', maxZoom: 19,
    }).addTo(mapa);

    // Tile alternativo oscuro (comentado — descomenta si prefieres)
    // L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { attribution: '© CARTO', maxZoom: 19 }).addTo(mapa);

    markerGroup = L.markerClusterGroup({
      maxClusterRadius: 50,
      iconCreateFunction: (cluster) => {
        const n = cluster.getChildCount();
        return L.divIcon({
          html: `<div style="background:var(--navy,#1A1953);color:#FFD45A;width:36px;height:36px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.85rem;font-family:var(--font-d,Inter,sans-serif);font-variant-numeric:lining-nums tabular-nums;border:2px solid #FFD45A;box-shadow:3px 3px 0 rgba(0,0,0,.3);">${n}</div>`,
          className: '', iconSize: [36,36], iconAnchor: [18,18],
        });
      },
    });
    mapa.addLayer(markerGroup);

    // Detectar click en mapa para buscar propiedades cercanas
    mapa.on('click', (e) => {
      buscarCercanas(e.latlng.lat, e.latlng.lng, 30); // 30km radio
    });

    cargarPropiedades();
  }

  /* ── Cargar propiedades ── */
  async function cargarPropiedades() {
    mostrarLoading(true);
    const tipo    = document.getElementById('mapaFiltroTipo').value;
    const negocio = document.getElementById('mapaFiltroNegocio').value;
    const buscar  = document.getElementById('mapaSearch').value.trim();

    const params = new URLSearchParams({ accion:'listar', por_pagina:500 });
    if (tipo)    params.set('tipo', tipo);
    if (negocio) params.set('negocio', negocio);
    if (buscar)  params.set('departamento', buscar);

    try {
      const res  = await fetch(`${API}?${params}`);
      const data = await res.json();
      const todas = data.propiedades || [];
      propiedades = todas;
      const conCoords = todas.filter(tieneCoordenadas);
      console.log(`Total: ${todas.length} | Con coords: ${conCoords.length}`);
      if (conCoords.length > 0) {
        renderMarcadores(conCoords);
        mapa.fitBounds(markerGroup.getBounds(), { padding:[30,30] });
      }
      renderListado(todas);
    } catch(e) {
      console.error(e);
      document.getElementById('mapaListado').innerHTML = `<div class="mapa-empty"><i class="fas fa-exclamation-triangle"></i><p>Error al cargar propiedades</p></div>`;
    } finally {
      mostrarLoading(false);
    }
  }

  /* ── Buscar por zona (texto) con geocoding ── */
  async function buscarPorZona() {
    const query = document.getElementById('mapaSearch').value.trim();
    if (!query) { cargarPropiedades(); return; }

    mostrarLoading(true);
    try {
      // Geocodificar la búsqueda con Nominatim
      const geo = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query + ', El Salvador')}&format=json&limit=1`);
      const geoData = await geo.json();

      if (geoData.length > 0) {
        const lat = parseFloat(geoData[0].lat);
        const lng = parseFloat(geoData[0].lon);
        mapa.setView([lat, lng], 12);
        buscarCercanas(lat, lng, 25); // 25km de la zona buscada
      } else {
        // Fallback: filtrar por departamento/municipio en texto
        cargarPropiedades();
      }
    } catch(e) {
      cargarPropiedades();
    }
  }

  /* ── Buscar propiedades cercanas a un punto ── */
  function buscarCercanas(lat, lng, radioKm) {
    // Calcular distancia Haversine
    function haversine(lat1,lng1,lat2,lng2) {
      const R = 6371;
      const dLat = (lat2-lat1) * Math.PI/180;
      const dLng = (lng2-lng1) * Math.PI/180;
      const a = Math.sin(dLat/2)**2 +
                Math.cos(lat1*Math.PI/180) * Math.cos(lat2*Math.PI/180) *
                Math.sin(dLng/2)**2;
      return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    }

    // Filtrar propiedades dentro del radio
    // Con coords: filtrar por distancia
    const conCoords = propiedades.filter(tieneCoordenadas);
    const cercanas = conCoords
      .map(p => ({ ...p, distancia: haversine(lat, lng, getLat(p), getLng(p)) }))
      .filter(p => p.distancia <= radioKm)
      .sort((a,b) => a.distancia - b.distancia);
    // Sin coords: mostrar en sidebar
    const sinCoords = propiedades.filter(p => !tieneCoordenadas(p));

    // Mostrar círculo de búsqueda
    if (window._circuloBusqueda) mapa.removeLayer(window._circuloBusqueda);
    window._circuloBusqueda = L.circle([lat,lng], {
      radius: radioKm * 1000,
      color: '#FFD45A', weight: 2, opacity: .6,
      fillColor: '#FFD45A', fillOpacity: .05,
    }).addTo(mapa);

    renderMarcadores(cercanas);
    renderListado([...cercanas, ...sinCoords.slice(0, 10)]);

    if (cercanas.length) {
      mapa.fitBounds(markerGroup.getBounds(), {padding:[20,20]});
    }
  }

  /* ── Render marcadores ── */
  function renderMarcadores(lista) {
    markerGroup.clearLayers();

    lista.forEach(p => {
      if (!tieneCoordenadas(p)) return;
      const precio   = '$' + parseFloat(p.precio_pedido).toLocaleString('en-US',{maximumFractionDigits:0});
      const destacada = p.es_anuncio_destacado == 1;

      const icon = L.divIcon({
        html: `<div class="marker-prop ${destacada?'destacado':''}">${precio}</div>`,
        className:'', iconSize:[null,null], iconAnchor:[0,28],
      });

      const marker = L.marker([getLat(p), getLng(p)], { icon })
        .bindPopup(popupHtml(p), { maxWidth:240, className:'leaflet-popup-pp' });

      marker.on('click', () => resaltarItem(p.id));
      markerGroup.addLayer(marker);
    });
  }

  /* ── Popup HTML ── */
  function popupHtml(p) {
    const precio = '$' + parseFloat(p.precio_pedido).toLocaleString('en-US',{maximumFractionDigits:0});
    const esAlquiler = (p.tipo_negocio==='Alquiler'||p.tipo_negocio==='Alquiler con opción a compra');
    const img = p.foto_portada ? `<img src="${p.foto_portada}" style="width:100%;height:100px;object-fit:cover;display:block;margin-bottom:8px;" loading="lazy">` : '';
    return `
      <div class="popup-prop">
        ${img}
        <div style="padding:${img?'0':'8px'} 0 0;">
          <div style="font-size:.6rem;font-weight:800;text-transform:uppercase;color:#1A1953;margin-bottom:4px;">${esc(p.tipo_inmueble||'')} · ${esAlquiler?'RENTA':'VENTA'}</div>
          <div class="popup-prop-titulo">${esc(p.titulo_anuncio)}</div>
          <div class="popup-prop-precio">${precio}${esAlquiler?'<span style="font-size:.65rem;font-weight:400;">/mes</span>':''}</div>
          <div class="popup-prop-loc"><i class="fas fa-map-marker-alt" style="color:#E6B800;"></i> ${esc(p.municipio||'')}, ${esc(p.departamento||'')}</div>
          <a href="/Asociaciones_PP/views/detalle.php?id=${p.id}" class="popup-prop-btn">Ver propiedad <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>`;
  }

  /* ── Render listado sidebar ── */
  function renderListado(lista) {
    const el = document.getElementById('mapaListado');
    const count = document.getElementById('mapaCount');
    count.textContent = `${lista.length} encontrada(s)`;

    if (!lista.length) {
      el.innerHTML = `<div class="mapa-empty"><i class="fas fa-map-marker-alt"></i><p>No hay propiedades encontradas.<br><small>Ajusta los filtros o busca otra zona.</small></p></div>`;
      return;
    }

    el.innerHTML = lista.slice(0,50).map(p => {
      const precio = '$' + parseFloat(p.precio_pedido).toLocaleString('en-US',{maximumFractionDigits:0});
      const esAlquiler = (p.tipo_negocio==='Alquiler'||p.tipo_negocio==='Alquiler con opción a compra');
      const dist = p.distancia ? `<span style="color:var(--gold);font-size:.65rem;font-weight:700;margin-left:auto;">${p.distancia.toFixed(1)} km</span>` : '';
      return `
        <div class="mapa-resultado-item" id="item-${p.id}" onclick="centrarEn('${p.id}',${getLat(p)},${getLng(p)})">
          <div class="mapa-res-ico"><i class="fas ${getIcon(p.tipo_inmueble)}"></i></div>
          <div class="mapa-res-info">
            <div class="mapa-res-badge">${esAlquiler?'RENTA':'VENTA'}</div>
            <div class="mapa-res-titulo">${esc(p.titulo_anuncio)}</div>
            <div class="mapa-res-ubicacion"><i class="fas fa-map-marker-alt"></i> ${esc(p.municipio||'')}, ${esc(p.departamento||'')} ${dist}</div>
            <div class="mapa-res-precio">${precio}</div>
          </div>
        </div>`;
    }).join('');

    if (lista.length > 50) {
      el.innerHTML += `<div style="text-align:center;padding:14px;font-size:.72rem;color:rgba(255,255,255,.35);">+ ${lista.length-50} propiedades más en el mapa</div>`;
    }
  }

  /* ── Centrar en propiedad y abrir popup ── */
  function centrarEn(id, lat, lng) {
    resaltarItem(id);
    if (lat && lng && lat != 0 && lng != 0) {
      mapa.setView([lat, lng], 15, {animate:true});
      markerGroup.eachLayer(layer => {
        if (layer._latlng && Math.abs(layer._latlng.lat-lat)<0.001 && Math.abs(layer._latlng.lng-lng)<0.001) {
          layer.openPopup();
        }
      });
    }
  }

  function resaltarItem(id) {
    document.querySelectorAll('.mapa-resultado-item').forEach(el => el.classList.remove('activo'));
    const el = document.getElementById(`item-${id}`);
    if (el) { el.classList.add('activo'); el.scrollIntoView({behavior:'smooth',block:'nearest'}); }
  }

  function mostrarLoading(show) {
    document.getElementById('mapaLoading').style.display = show ? 'flex' : 'none';
  }

  function esc(str) {
    if(!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  /* ── Event listeners ── */
  document.getElementById('btnMapaBuscar').addEventListener('click', buscarPorZona);
  document.getElementById('mapaSearch').addEventListener('keydown', e => { if(e.key==='Enter') buscarPorZona(); });
  document.getElementById('mapaFiltroTipo').addEventListener('change', cargarPropiedades);
  document.getElementById('mapaFiltroNegocio').addEventListener('change', cargarPropiedades);

  // Inicializar
  initMapa();
  </script>
</body>
</html>