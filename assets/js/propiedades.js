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

  // Mapeo de colores por estado
  const estadoColores = {
    'Activa': '#10B981',
    'Pendiente aprobación': '#F59E0B',
    'Pausada': '#6B7280',
    'Borrador': '#8B5CF6',
    'Rechazada': '#EF4444'
  };

  /* ════════════════════════════════════════
     CARGAR PROPIEDADES
  ════════════════════════════════════════ */
  async function cargar() {
    tablaBody.innerHTML = `
      <tr>
        <td colspan="9" class="tabla-loading">
          <div class="loading-spinner"></div>
          Cargando propiedades...
        </td>
      </tr>`;

    const params = new URLSearchParams();
    params.append('accion', 'listar');
    
    if (filtroBuscar?.value) params.append('buscar', filtroBuscar.value);
    if (filtroTipo?.value) params.append('tipo_inmueble', filtroTipo.value);
    if (filtroNegocio?.value) params.append('negocio', filtroNegocio.value);
    if (filtroEstado?.value) params.append('estado', filtroEstado.value);

    try {
      const res = await fetch(`${API}?${params.toString()}`);
      const data = await res.json();

      if (!data.ok) throw new Error(data.msg || 'Error al cargar');

      const propiedades = data.propiedades || [];
      const total = data.total || propiedades.length;
      const kpis = data.kpis || {};

      // Actualizar KPIs
      if (kpiTotal) kpiTotal.textContent = kpis.total || total;
      if (kpiActivas) kpiActivas.textContent = kpis.activas || 0;
      if (kpiPendientes) kpiPendientes.textContent = kpis.pendientes || 0;
      if (kpiDestacadas) kpiDestacadas.textContent = kpis.destacadas || 0;

      subtitulo.textContent = `${total} propiedad(es) encontrada(s)`;

      if (!propiedades.length) {
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

      tablaBody.innerHTML = propiedades.map(p => renderFila(p)).join('');
      bindAcciones();

    } catch (err) {
      console.error('Error:', err);
      tablaBody.innerHTML = `
        <tr><td colspan="9" class="tabla-empty">
          Error: ${err.message}
        </td></tr>`;
    }
  }

  /* ════════════════════════════════════════
     RENDER FILA
  ════════════════════════════════════════ */
  function renderFila(p) {
    const precio = `$${parseFloat(p.precio_pedido || 0).toLocaleString('en-US', { minimumFractionDigits: 0 })}`;
    const estado = p.estado_publicacion || 'Sin estado';
    const colorStyle = estadoColores[estado] ? `background:${estadoColores[estado]}20;color:${estadoColores[estado]};` : '';
    
    const foto = p.foto
      ? `<img src="../${p.foto}" alt="" style="width:40px;height:40px;border-radius:8px;object-fit:cover;" onerror="this.parentNode.innerHTML='<div style=\\'width:40px;height:40px;border-radius:8px;background:#e5e7eb;display:flex;align-items:center;justify-content:center;font-size:1rem;\\'>🏠</div>'">`
      : `<div style="width:40px;height:40px;border-radius:8px;background:#e5e7eb;display:flex;align-items:center;justify-content:center;font-size:1rem;">🏠</div>`;

    const vendedor = p.vendedor || '—';
    const correo = p.correo_vendedor || '—';

    return `
      <tr>
        <td style="min-width:260px;">
          <div style="display:flex;align-items:center;gap:12px;">
            ${foto}
            <div>
              <div style="font-weight:600;font-size:.85rem;color:var(--navy);">${escapeHtml(p.titulo_anuncio || 'Sin título')}</div>
              <div style="font-size:.72rem;color:var(--muted);">${escapeHtml(p.municipio || '')}, ${escapeHtml(p.departamento || '')}</div>
            </div>
          </div>
        </td>
        <td style="font-size:.85rem;">${escapeHtml(p.tipo_inmueble || '—')}</td>
        <td>
          <span class="badge ${p.tipo_negocio === 'Venta' ? 'badge-vendedor' : 'badge-activa'}">
            ${escapeHtml(p.tipo_negocio || '—')}
          </span>
        </td>
        <td>
          <div style="font-size:.85rem;font-weight:500;">${escapeHtml(vendedor)}</div>
          <div style="font-size:.7rem;color:var(--muted);">${escapeHtml(correo)}</div>
        </td>
        <td style="font-weight:700;color:var(--navy);">${precio}</td>
        <td style="font-size:.85rem;">${escapeHtml(p.departamento || '—')}</td>
        <td>
          <span class="badge" style="${colorStyle} padding:4px 12px; border-radius:20px; font-size:.75rem; font-weight:600;">
            ${escapeHtml(estado)}
          </span>
        </td>
        <td style="text-align:center;">
          ${p.es_anuncio_destacado == 1 ? '<span style="color:var(--gold); font-size:1.1rem;">⭐</span>' : '—'}
         </td>
        <td>
          <div style="display:flex;gap:6px;flex-wrap:wrap;">
            <button class="btn-panel btn-panel-outline btn-panel-sm btn-ver" data-id="${p.id}">Ver</button>
            <button class="btn-panel btn-panel-primary btn-panel-sm btn-estado" data-id="${p.id}" data-titulo="${escapeHtml(p.titulo_anuncio)}">Estado</button>
            <button class="btn-panel btn-panel-danger btn-panel-sm btn-eliminar" data-id="${p.id}" data-titulo="${escapeHtml(p.titulo_anuncio)}">Eliminar</button>
          </div>
         </td>
      </tr>`;
  }

  /* ════════════════════════════════════════
     BIND ACCIONES
  ════════════════════════════════════════ */
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

  /* ════════════════════════════════════════
     MODAL VER DETALLE
  ════════════════════════════════════════ */
  async function abrirDetalle(id) {
    const body = document.getElementById('detalleBody');
    const titulo = document.getElementById('detalleTitulo');
    body.innerHTML = '<div class="loading-spinner"></div>';
    abrirModal(modalDetalleOverlay);

    try {
      const res = await fetch(`${API}?accion=detalle&id=${id}`);
      const data = await res.json();
      
      if (!data.ok) throw new Error(data.msg);

      const p = data.propiedad;
      const fotos = data.fotos || [];
      
      titulo.textContent = p.titulo_anuncio || 'Detalle de propiedad';

      const precio = new Intl.NumberFormat('es-SV', { style: 'currency', currency: 'USD', minimumFractionDigits: 0 }).format(p.precio_pedido || 0);
      
      const estadoColor = estadoColores[p.estado_publicacion] || '#6B7280';

      body.innerHTML = `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
          
          <!-- Columna izquierda -->
          <div>
            <div style="margin-bottom:24px;">
              <h4 style="font-size:.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;margin-bottom:16px;border-left:3px solid var(--gold);padding-left:10px;">
                📋 Información general
              </h4>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div><span style="color:var(--muted);font-size:.8rem;">Tipo inmueble</span><br><strong>${escapeHtml(p.tipo_inmueble || '—')}</strong></div>
                <div><span style="color:var(--muted);font-size:.8rem;">Tipo negocio</span><br><strong>${escapeHtml(p.tipo_negocio || '—')}</strong></div>
                <div><span style="color:var(--muted);font-size:.8rem;">Precio</span><br><strong style="color:var(--navy);font-size:1.1rem;">${precio}</strong></div>
                <div><span style="color:var(--muted);font-size:.8rem;">Estado</span><br><span style="display:inline-block;background:${estadoColor}20;color:${estadoColor};padding:4px 12px;border-radius:20px;font-size:.8rem;font-weight:600;">${escapeHtml(p.estado_publicacion || '—')}</span></div>
              </div>
            </div>
            
            <div style="margin-bottom:24px;">
              <h4 style="font-size:.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;margin-bottom:16px;border-left:3px solid var(--gold);padding-left:10px;">
                👤 Vendedor / Agente
              </h4>
              <div>
                <div style="font-weight:600;margin-bottom:8px;">${escapeHtml(p.vendedor || '—')}</div>
                <div style="font-size:.8rem;color:var(--muted);">📧 ${escapeHtml(p.correo_vendedor || '—')}</div>
                <div style="font-size:.8rem;color:var(--muted);">📞 ${escapeHtml(p.telefono_vendedor || '—')}</div>
              </div>
            </div>
            
            <div style="margin-bottom:24px;">
              <h4 style="font-size:.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;margin-bottom:16px;border-left:3px solid var(--gold);padding-left:10px;">
                📍 Ubicación
              </h4>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div><span style="color:var(--muted);font-size:.8rem;">Departamento</span><br><strong>${escapeHtml(p.departamento || '—')}</strong></div>
                <div><span style="color:var(--muted);font-size:.8rem;">Municipio</span><br><strong>${escapeHtml(p.municipio || '—')}</strong></div>
                <div><span style="color:var(--muted);font-size:.8rem;">Dirección</span><br><strong>${escapeHtml(p.direccion_exacta || '—')}</strong></div>
              </div>
            </div>
          </div>
          
          <!-- Columna derecha -->
          <div>
            <div style="margin-bottom:24px;">
              <h4 style="font-size:.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;margin-bottom:16px;border-left:3px solid var(--gold);padding-left:10px;">
                🏠 Características
              </h4>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div><span style="color:var(--muted);font-size:.8rem;">Habitaciones</span><br><strong>${p.num_habitaciones || '—'}</strong></div>
                <div><span style="color:var(--muted);font-size:.8rem;">Baños</span><br><strong>${p.num_banos || '—'}</strong></div>
                <div><span style="color:var(--muted);font-size:.8rem;">Metros construcción</span><br><strong>${p.metros_construccion ? p.metros_construccion.toLocaleString() + ' m²' : '—'}</strong></div>
                <div><span style="color:var(--muted);font-size:.8rem;">Metros terreno</span><br><strong>${p.metros_terreno ? p.metros_terreno.toLocaleString() + ' m²' : '—'}</strong></div>
              </div>
            </div>
            
            <div style="margin-bottom:24px;">
              <h4 style="font-size:.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;margin-bottom:16px;border-left:3px solid var(--gold);padding-left:10px;">
                📝 Descripción
              </h4>
              <div style="background:var(--off);padding:16px;border-radius:12px;font-size:.85rem;line-height:1.7;color:var(--text);max-height:200px;overflow-y:auto;">
                ${escapeHtml(p.descripcion_detallada || 'Sin descripción disponible.')}
              </div>
            </div>
            
            <div>
              <h4 style="font-size:.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;margin-bottom:16px;border-left:3px solid var(--gold);padding-left:10px;">
                📸 Fotos (${fotos.length})
              </h4>
              <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;">
                ${fotos.length ? fotos.slice(0, 6).map(f => `
                  <div style="aspect-ratio:1;background:var(--off);border-radius:8px;overflow:hidden;cursor:pointer;" onclick="window.open('../${escapeHtml(f.url_foto_original)}', '_blank')">
                    <img src="../${escapeHtml(f.url_foto_miniatura || f.url_foto_original)}" style="width:100%;height:100%;object-fit:cover;" onerror="this.parentNode.innerHTML='<div style=\\'width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:1.5rem;\\'>📷</div>'">
                  </div>
                `).join('') : '<p style="color:var(--muted);font-size:.8rem;">Sin fotos disponibles</p>'}
              </div>
            </div>
          </div>
          
        </div>
      `;

    } catch (err) {
      console.error('Error en detalle:', err);
      body.innerHTML = `<p style="color:var(--red);">Error: ${err.message}</p>`;
    }
  }

  document.getElementById('modalDetalleCerrar')?.addEventListener('click', () => cerrarModal(modalDetalleOverlay));
  document.getElementById('btnCerrarDetalle')?.addEventListener('click', () => cerrarModal(modalDetalleOverlay));
  modalDetalleOverlay?.addEventListener('click', e => { if (e.target === modalDetalleOverlay) cerrarModal(modalDetalleOverlay); });

  /* ════════════════════════════════════════
     MODAL CAMBIAR ESTADO
  ════════════════════════════════════════ */
  async function abrirModalEstado(id, titulo) {
    document.getElementById('propiedadEstadoId').value = id;
    document.getElementById('propiedadEstadoNombre').textContent = titulo;

    const select = document.getElementById('selectNuevoEstado');
    select.innerHTML = '<option value="">Cargando...</option>';
    abrirModal(modalEstadoOverlay);

    try {
      const res = await fetch(`${API}?accion=estados`);
      const data = await res.json();
      if (data.ok) {
        select.innerHTML = '<option value="">Seleccionar estado...</option>' +
          data.estados.map(e => `<option value="${e.id}">${escapeHtml(e.nombre_opcion)}</option>`).join('');
      } else {
        select.innerHTML = '<option value="">Error al cargar</option>';
      }
    } catch {
      select.innerHTML = '<option value="">Error al cargar</option>';
    }
  }

  document.getElementById('btnConfirmarEstado')?.addEventListener('click', async () => {
    const id = document.getElementById('propiedadEstadoId').value;
    const estadoId = document.getElementById('selectNuevoEstado').value;

    if (!estadoId) { toast('Selecciona un estado.', 'error'); return; }

    const formData = new FormData();
    formData.append('id', id);
    formData.append('estado_id', estadoId);

    try {
      const res = await fetch(`${API}?accion=estado`, { method: 'POST', body: formData });
      const data = await res.json();
      cerrarModal(modalEstadoOverlay);
      toast(data.msg, data.ok ? 'ok' : 'error');
      if (data.ok) cargar();
    } catch (err) {
      toast('Error al cambiar estado', 'error');
    }
  });

  document.getElementById('btnCancelarEstado')?.addEventListener('click', () => cerrarModal(modalEstadoOverlay));
  document.getElementById('modalEstadoCerrar')?.addEventListener('click', () => cerrarModal(modalEstadoOverlay));
  modalEstadoOverlay?.addEventListener('click', e => { if (e.target === modalEstadoOverlay) cerrarModal(modalEstadoOverlay); });

  /* ════════════════════════════════════════
     MODAL ELIMINAR
  ════════════════════════════════════════ */
  function abrirModalEliminar(id, titulo) {
    document.getElementById('idEliminar').value = id;
    document.getElementById('nombreEliminar').textContent = titulo;
    abrirModal(modalEliminarOverlay);
  }

  document.getElementById('btnConfirmarEliminar')?.addEventListener('click', async () => {
    const id = document.getElementById('idEliminar').value;
    const formData = new FormData();
    formData.append('id', id);

    try {
      const res = await fetch(`${API}?accion=eliminar`, { method: 'POST', body: formData });
      const data = await res.json();
      cerrarModal(modalEliminarOverlay);
      toast(data.msg, data.ok ? 'ok' : 'error');
      if (data.ok) cargar();
    } catch (err) {
      toast('Error al eliminar', 'error');
    }
  });

  document.getElementById('btnCancelarEliminar')?.addEventListener('click', () => cerrarModal(modalEliminarOverlay));
  document.getElementById('modalEliminarCerrar')?.addEventListener('click', () => cerrarModal(modalEliminarOverlay));
  modalEliminarOverlay?.addEventListener('click', e => { if (e.target === modalEliminarOverlay) cerrarModal(modalEliminarOverlay); });

  /* ════════════════════════════════════════
     HELPERS
  ════════════════════════════════════════ */
  function abrirModal(overlay) { overlay?.classList.add('open'); }
  function cerrarModal(overlay) { overlay?.classList.remove('open'); }

  function escapeHtml(str) {
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
      t.style.opacity = '0';
      setTimeout(() => t.remove(), 300);
    }, 3000);
  }

  function debounce(fn) {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(fn, 400);
  }

  /* ════════════════════════════════════════
     EVENT LISTENERS
  ════════════════════════════════════════ */
  filtroBuscar?.addEventListener('input', () => debounce(cargar));
  filtroTipo?.addEventListener('change', cargar);
  filtroNegocio?.addEventListener('change', cargar);
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