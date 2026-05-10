/**
 * propiedades.js
 * PP Bienes Raíces — assets/js/propiedades.js
 * Vista admin — gestión de todas las propiedades
 */

(function () {
  'use strict';

  const API = '../controllers/propiedadController.php';

  // ── Referencias ──
  const tablaBody      = document.getElementById('tablaBody');
  const subtitulo      = document.getElementById('subtituloTabla');
  const filtroBuscar   = document.getElementById('filtroBuscar');
  const filtroTipo     = document.getElementById('filtroTipoInmueble');
  const filtroNegocio  = document.getElementById('filtroNegocio');
  const filtroEstado   = document.getElementById('filtroEstado');

  // KPIs
  const kpiTotal      = document.getElementById('kpiTotal');
  const kpiActivas    = document.getElementById('kpiActivas');
  const kpiPendientes = document.getElementById('kpiPendientes');
  const kpiDestacadas = document.getElementById('kpiDestacadas');

  // Modales
  const modalDetalleOverlay  = document.getElementById('modalDetalleOverlay');
  const modalEstadoOverlay   = document.getElementById('modalEstadoOverlay');
  const modalEliminarOverlay = document.getElementById('modalEliminarOverlay');

  let debounceTimer;

  // ════════════════════════════════════════
  // CARGAR PROPIEDADES
  // ════════════════════════════════════════
  async function cargar() {
    tablaBody.innerHTML = `
      <tr>
        <td colspan="9" class="tabla-loading">
          <div class="loading-spinner"></div>
          Cargando propiedades...
        </td>
      </tr>`;

    const params = new URLSearchParams({
      accion:       'listar',
      buscar:       filtroBuscar?.value    ?? '',
      tipo_inmueble:filtroTipo?.value      ?? '',
      negocio:      filtroNegocio?.value   ?? '',
      estado:       filtroEstado?.value    ?? '',
    });

    try {
      const res  = await fetch(`${API}?${params}`);
      const data = await res.json();

      if (!data.ok) throw new Error(data.msg);

      // KPIs
      if (data.kpis) {
        if (kpiTotal)      kpiTotal.textContent      = data.kpis.total      ?? 0;
        if (kpiActivas)    kpiActivas.textContent    = data.kpis.activas    ?? 0;
        if (kpiPendientes) kpiPendientes.textContent = data.kpis.pendientes ?? 0;
        if (kpiDestacadas) kpiDestacadas.textContent = data.kpis.destacadas ?? 0;
      }

      subtitulo.textContent = `${data.total} propiedad(es) encontrada(s)`;

      if (!data.propiedades.length) {
        tablaBody.innerHTML = `
          <tr>
            <td colspan="9" class="tabla-empty">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
              </svg>
              No se encontraron propiedades con esos filtros.
            </td>
          </tr>`;
        return;
      }

      tablaBody.innerHTML = data.propiedades.map(p => renderFila(p)).join('');
      bindAcciones();

    } catch (err) {
      tablaBody.innerHTML = `
        <tr><td colspan="9" class="tabla-empty">
          Error: ${err.message}
        </td></tr>`;
    }
  }

  // ════════════════════════════════════════
  // RENDER FILA
  // ════════════════════════════════════════
  function renderFila(p) {
    const precio     = `$${parseFloat(p.precio_pedido).toLocaleString('en-US', {minimumFractionDigits:0})}`;
    const colorStyle = p.estado_color ? `background:${p.estado_color}20;color:${p.estado_color};` : '';
    const foto       = p.foto
      ? `<img src="../${p.foto}" alt="" style="width:36px;height:36px;border-radius:6px;object-fit:cover;">`
      : `<div style="width:36px;height:36px;border-radius:6px;background:#e5e7eb;display:flex;align-items:center;justify-content:center;font-size:1rem;">🏠</div>`;

    return `
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:10px;">
            ${foto}
            <div>
              <div style="font-weight:600;font-size:.84rem;">${esc(p.titulo_anuncio)}</div>
              <div style="font-size:.74rem;color:var(--muted);">${esc(p.direccion_exacta ?? '')} ${esc(p.municipio ?? '')}</div>
            </div>
          </div>
        </td>
        <td style="font-size:.84rem;">${esc(p.tipo_inmueble)}</td>
        <td>
          <span class="badge ${p.tipo_negocio === 'Venta' ? 'badge-vendedor' : 'badge-activa'}">
            ${esc(p.tipo_negocio)}
          </span>
        </td>
        <td>
          <div style="font-size:.84rem;font-weight:500;">${esc(p.vendedor)}</div>
          <div style="font-size:.74rem;color:var(--muted);">${esc(p.correo_vendedor ?? '')}</div>
        </td>
        <td style="font-weight:700;color:var(--navy);">${precio}</td>
        <td style="font-size:.84rem;">${esc(p.departamento)}</td>
        <td>
          <span class="badge" style="${colorStyle}">${esc(p.estado_publicacion)}</span>
        </td>
        <td style="text-align:center;">
          ${p.es_anuncio_destacado == 1 ? '<span title="Destacada">⭐</span>' : '—'}
        </td>
        <td>
          <div class="acciones-cell">
            <button class="btn-panel btn-panel-outline btn-panel-sm btn-ver"
                    data-id="${p.id}">Ver</button>
            <button class="btn-panel btn-panel-primary btn-panel-sm btn-estado"
                    data-id="${p.id}" data-titulo="${esc(p.titulo_anuncio)}">Estado</button>
            <button class="btn-panel btn-panel-danger btn-panel-sm btn-eliminar"
                    data-id="${p.id}" data-titulo="${esc(p.titulo_anuncio)}">Eliminar</button>
          </div>
        </td>
      </tr>`;
  }

  // ════════════════════════════════════════
  // BIND ACCIONES
  // ════════════════════════════════════════
  function bindAcciones() {
    tablaBody.querySelectorAll('.btn-ver').forEach(btn =>
      btn.addEventListener('click', () => abrirDetalle(btn.dataset.id))
    );
    tablaBody.querySelectorAll('.btn-estado').forEach(btn =>
      btn.addEventListener('click', () => abrirModalEstado(btn.dataset.id, btn.dataset.titulo))
    );
    tablaBody.querySelectorAll('.btn-eliminar').forEach(btn =>
      btn.addEventListener('click', () => abrirModalEliminar(btn.dataset.id, btn.dataset.titulo))
    );
  }

  // ════════════════════════════════════════
  // MODAL VER DETALLE
  // ════════════════════════════════════════
  async function abrirDetalle(id) {
    const body   = document.getElementById('detalleBody');
    const titulo = document.getElementById('detalleTitulo');
    body.innerHTML = '<div class="loading-spinner"></div>';
    abrirModal(modalDetalleOverlay);

    try {
      const res  = await fetch(`${API}?accion=detalle&id=${id}`);
      const data = await res.json();
      if (!data.ok) throw new Error(data.msg);

      const p = data.propiedad;
      titulo.textContent = p.titulo_anuncio;

      body.innerHTML = `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
          <div>
            <h4 style="font-size:.8rem;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px;">Información general</h4>
            <table style="width:100%;font-size:.875rem;border-collapse:collapse;">
              <tr><td style="padding:5px 0;color:var(--muted);width:45%;">Tipo inmueble</td><td style="font-weight:500;">${esc(p.tipo_inmueble)}</td></tr>
              <tr><td style="padding:5px 0;color:var(--muted);">Tipo negocio</td><td style="font-weight:500;">${esc(p.tipo_negocio)}</td></tr>
              <tr><td style="padding:5px 0;color:var(--muted);">Precio</td><td style="font-weight:700;color:var(--navy);">$${parseFloat(p.precio_pedido).toLocaleString()}</td></tr>
              <tr><td style="padding:5px 0;color:var(--muted);">Vendedor</td><td style="font-weight:500;">${esc(p.vendedor)}</td></tr>
              <tr><td style="padding:5px 0;color:var(--muted);">Correo</td><td>${esc(p.correo_vendedor ?? '—')}</td></tr>
              <tr><td style="padding:5px 0;color:var(--muted);">Teléfono</td><td>${esc(p.telefono_vendedor ?? '—')}</td></tr>
            </table>

            <h4 style="font-size:.8rem;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin:16px 0 12px;">Ubicación</h4>
            <table style="width:100%;font-size:.875rem;border-collapse:collapse;">
              <tr><td style="padding:5px 0;color:var(--muted);width:45%;">Departamento</td><td>${esc(p.departamento)}</td></tr>
              <tr><td style="padding:5px 0;color:var(--muted);">Municipio</td><td>${esc(p.municipio)}</td></tr>
              <tr><td style="padding:5px 0;color:var(--muted);">Dirección</td><td>${esc(p.direccion_exacta ?? '—')}</td></tr>
            </table>

            <h4 style="font-size:.8rem;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin:16px 0 12px;">Características</h4>
            <table style="width:100%;font-size:.875rem;border-collapse:collapse;">
              <tr><td style="padding:5px 0;color:var(--muted);width:45%;">Habitaciones</td><td>${p.num_habitaciones ?? '—'}</td></tr>
              <tr><td style="padding:5px 0;color:var(--muted);">Baños</td><td>${p.num_banos ?? '—'}</td></tr>
              <tr><td style="padding:5px 0;color:var(--muted);">Metros terreno</td><td>${p.metros_terreno ? p.metros_terreno + ' m²' : '—'}</td></tr>
              <tr><td style="padding:5px 0;color:var(--muted);">Estacionamiento</td><td>${p.tiene_estacionamiento == 1 ? 'Sí' : 'No'}</td></tr>
              <tr><td style="padding:5px 0;color:var(--muted);">Piscina</td><td>${p.tiene_piscina == 1 ? 'Sí' : 'No'}</td></tr>
              <tr><td style="padding:5px 0;color:var(--muted);">Amueblado</td><td>${p.viene_amueblado == 1 ? 'Sí' : 'No'}</td></tr>
            </table>
          </div>
          <div>
            <h4 style="font-size:.8rem;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px;">Fotos (${data.fotos.length})</h4>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
              ${data.fotos.length
                ? data.fotos.map(f => `
                    <img src="../${esc(f.url_foto_miniatura || f.url_foto_original)}"
                         style="width:100%;height:90px;object-fit:cover;border-radius:8px;cursor:pointer;"
                         onclick="window.open('../${esc(f.url_foto_original)}','_blank')"
                         onerror="this.style.display='none'">`).join('')
                : '<p style="color:var(--muted);font-size:.875rem;">Sin fotos</p>'
              }
            </div>
            ${p.descripcion_detallada ? `
              <h4 style="font-size:.8rem;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin:16px 0 8px;">Descripción</h4>
              <p style="font-size:.875rem;color:var(--muted);line-height:1.65;">${esc(p.descripcion_detallada)}</p>
            ` : ''}
          </div>
        </div>`;

    } catch (err) {
      body.innerHTML = `<p style="color:var(--red);">Error: ${err.message}</p>`;
    }
  }

  document.getElementById('modalDetalleCerrar')?.addEventListener('click', () => cerrarModal(modalDetalleOverlay));
  document.getElementById('btnCerrarDetalle')?.addEventListener('click',   () => cerrarModal(modalDetalleOverlay));
  modalDetalleOverlay?.addEventListener('click', e => { if (e.target === modalDetalleOverlay) cerrarModal(modalDetalleOverlay); });

  // ════════════════════════════════════════
  // MODAL CAMBIAR ESTADO
  // ════════════════════════════════════════
  async function abrirModalEstado(id, titulo) {
    document.getElementById('propiedadEstadoId').value     = id;
    document.getElementById('propiedadEstadoNombre').textContent = titulo;

    // Cargar opciones de estado
    const select = document.getElementById('selectNuevoEstado');
    select.innerHTML = '<option value="">Cargando...</option>';
    abrirModal(modalEstadoOverlay);

    try {
      const res  = await fetch(`${API}?accion=estados`);
      const data = await res.json();
      select.innerHTML = '<option value="">Seleccionar estado...</option>' +
        data.estados.map(e =>
          `<option value="${e.id}">${esc(e.nombre_opcion)}</option>`
        ).join('');
    } catch {
      select.innerHTML = '<option value="">Error al cargar</option>';
    }
  }

  document.getElementById('btnConfirmarEstado')?.addEventListener('click', async () => {
    const id       = document.getElementById('propiedadEstadoId').value;
    const estadoId = document.getElementById('selectNuevoEstado').value;

    if (!estadoId) { toast('Selecciona un estado.', 'error'); return; }

    const body = new FormData();
    body.append('id',        id);
    body.append('estado_id', estadoId);

    const res  = await fetch(`${API}?accion=estado`, { method: 'POST', body });
    const data = await res.json();

    cerrarModal(modalEstadoOverlay);
    toast(data.msg, data.ok ? 'ok' : 'error');
    if (data.ok) cargar();
  });

  document.getElementById('btnCancelarEstado')?.addEventListener('click',  () => cerrarModal(modalEstadoOverlay));
  document.getElementById('modalEstadoCerrar')?.addEventListener('click',  () => cerrarModal(modalEstadoOverlay));
  modalEstadoOverlay?.addEventListener('click', e => { if (e.target === modalEstadoOverlay) cerrarModal(modalEstadoOverlay); });

  // ════════════════════════════════════════
  // MODAL ELIMINAR
  // ════════════════════════════════════════
  function abrirModalEliminar(id, titulo) {
    document.getElementById('idEliminar').value        = id;
    document.getElementById('nombreEliminar').textContent = titulo;
    abrirModal(modalEliminarOverlay);
  }

  document.getElementById('btnConfirmarEliminar')?.addEventListener('click', async () => {
    const id = document.getElementById('idEliminar').value;
    const body = new FormData();
    body.append('id', id);

    const res  = await fetch(`${API}?accion=eliminar`, { method: 'POST', body });
    const data = await res.json();

    cerrarModal(modalEliminarOverlay);
    toast(data.msg, data.ok ? 'ok' : 'error');
    if (data.ok) cargar();
  });

  document.getElementById('btnCancelarEliminar')?.addEventListener('click', () => cerrarModal(modalEliminarOverlay));
  document.getElementById('modalEliminarCerrar')?.addEventListener('click', () => cerrarModal(modalEliminarOverlay));
  modalEliminarOverlay?.addEventListener('click', e => { if (e.target === modalEliminarOverlay) cerrarModal(modalEliminarOverlay); });

  // ════════════════════════════════════════
  // HELPERS
  // ════════════════════════════════════════
  function abrirModal(overlay)  { overlay?.classList.add('open'); }
  function cerrarModal(overlay) { overlay?.classList.remove('open'); }

  function esc(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function toast(msg, tipo = 'ok') {
    const wrap = document.getElementById('toastWrap');
    if (!wrap) return;
    const icono = tipo === 'ok'
      ? `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>`
      : `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>`;

    const t = document.createElement('div');
    t.className = `toast toast-${tipo}`;
    t.innerHTML = `${icono}<span>${msg}</span>`;
    wrap.appendChild(t);
    setTimeout(() => {
      t.style.opacity = '0'; t.style.transform = 'translateX(10px)';
      setTimeout(() => t.remove(), 400);
    }, 3500);
  }

  // Debounce filtros
  function debounce(fn) {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(fn, 350);
  }

  // ════════════════════════════════════════
  // EVENT LISTENERS
  // ════════════════════════════════════════
  filtroBuscar?.addEventListener('input',  () => debounce(cargar));
  filtroTipo?.addEventListener('change',   cargar);
  filtroNegocio?.addEventListener('change',cargar);
  filtroEstado?.addEventListener('change', cargar);

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      cerrarModal(modalDetalleOverlay);
      cerrarModal(modalEstadoOverlay);
      cerrarModal(modalEliminarOverlay);
    }
  });

  // Inicializar
  cargar();

})();