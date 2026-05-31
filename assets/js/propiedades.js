/**
 * propiedades.js
 * PP Bienes Raíces — assets/js/propiedades.js
 * Vista admin — gestión de todas las propiedades
 */

(function () {
  'use strict';

  const API = '../controllers/propiedadController.php';

  const tablaBody      = document.getElementById('tablaBody');
  const subtitulo      = document.getElementById('subtituloTabla');
  const filtroBuscar   = document.getElementById('filtroBuscar');
  const filtroTipo     = document.getElementById('filtroTipoInmueble');
  const filtroNegocio  = document.getElementById('filtroNegocio');
  const filtroEstado   = document.getElementById('filtroEstado');

  const kpiTotal      = document.getElementById('kpiTotal');
  const kpiActivas    = document.getElementById('kpiActivas');
  const kpiPendientes = document.getElementById('kpiPendientes');
  const kpiDestacadas = document.getElementById('kpiDestacadas');

  const modalDetalleOverlay  = document.getElementById('modalDetalleOverlay');
  const modalEstadoOverlay   = document.getElementById('modalEstadoOverlay');
  const modalEliminarOverlay = document.getElementById('modalEliminarOverlay');
  const modalEditarOverlay   = document.getElementById('modalEditarOverlay');

  let debounceTimer;

  /* ════════════════════════════════════════
     CARGAR PROPIEDADES
  ════════════════════════════════════════ */
  async function cargar() {
    tablaBody.innerHTML = `
      <tr>
        <td colspan="9" class="tabla-loading">
          <div class="loading-spinner"></div> Cargando propiedades...
        </td>
      </tr>`;

    const params = new URLSearchParams();
    params.append('accion', 'listar');
    if (filtroBuscar?.value)   params.append('buscar',        filtroBuscar.value);
    if (filtroTipo?.value)     params.append('tipo_inmueble', filtroTipo.value);
    if (filtroNegocio?.value)  params.append('negocio',       filtroNegocio.value);
    if (filtroEstado?.value)   params.append('estado',        filtroEstado.value);

    try {
      const res  = await fetch(`${API}?${params.toString()}`);
      const data = await res.json();
      if (!data.ok) throw new Error(data.msg || 'Error al cargar');

      const propiedades = data.propiedades || [];
      const total       = data.total || propiedades.length;
      const kpis        = data.kpis  || {};

      if (kpiTotal)      kpiTotal.textContent      = kpis.total      || total;
      if (kpiActivas)    kpiActivas.textContent    = kpis.activas    || 0;
      if (kpiPendientes) kpiPendientes.textContent = kpis.pendientes || 0;
      if (kpiDestacadas) kpiDestacadas.textContent = kpis.destacadas || 0;

      subtitulo.textContent = `${total} propiedad(es) encontrada(s)`;

      if (!propiedades.length) {
        tablaBody.innerHTML = `
          <tr><td colspan="9" class="tabla-empty">
            <i class="fas fa-home"></i>
            <br>No se encontraron propiedades con esos filtros.
          </td></tr>`;
        return;
      }

      tablaBody.innerHTML = propiedades.map(p => renderFila(p)).join('');
      bindAcciones();

    } catch (err) {
      tablaBody.innerHTML = `
        <tr><td colspan="9" class="tabla-empty">Error: ${err.message}</td></tr>`;
    }
  }

  /* ════════════════════════════════════════
     RENDER FILA — solo diseño flat
  ════════════════════════════════════════ */
  function renderFila(p) {
    const precio   = '$' + parseFloat(p.precio_pedido || 0).toLocaleString('en-US', { minimumFractionDigits: 0 });
    const estado   = p.estado_publicacion || 'Sin estado';

    const foto = p.foto
      ? `<img src="../${p.foto}" alt="" class="prop-foto-thumb-sm" onerror="this.parentNode.innerHTML='<div class=\'prop-foto-placeholder\'><i class=\'fas fa-home\'></i></div>'">`
      : `<div class="prop-foto-placeholder"><i class="fas fa-home"></i></div>`;

    return `
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:10px;">
            ${foto}
            <div>
              <div class="td-titulo">${esc(p.titulo_anuncio || 'Sin título')}</div>
              <div class="td-tipo"><i class="fas fa-map-marker-alt" style="color:var(--gold-dark);margin-right:3px;font-size:.65rem;"></i>${esc(p.municipio || '')}, ${esc(p.departamento || '')}</div>
            </div>
          </div>
        </td>
        <td><span class="td-tipo">${esc(p.tipo_inmueble || '—')}</span></td>
        <td><span class="negocio-tag">${esc(p.tipo_negocio || '—')}</span></td>
        <td>
          <div class="td-vendedor">${esc(p.vendedor || '—')}</div>
          <div class="td-tipo">${esc(p.correo_vendedor || '')}</div>
        </td>
        <td><span class="td-precio">${precio}</span></td>
        <td><span class="td-loc"><i class="fas fa-map-marker-alt" style="color:var(--gold-dark);margin-right:3px;font-size:.65rem;"></i>${esc(p.departamento || '—')}</span></td>
        <td><span class="badge-estado-prop">${esc(estado)}</span></td>
        <td class="td-star">
          <i class="fas fa-star ${p.es_anuncio_destacado == 1 ? 'star-on' : 'star-off'}"></i>
        </td>
        <td>
          <div class="acciones-cell">
            <button class="btn-prop-accion btn-ver btn-ver-prop" data-id="${p.id}" title="Ver detalle">
              <i class="fas fa-eye"></i>
            </button>
            <button class="btn-prop-accion btn-edit btn-editar-prop" data-id="${p.id}" title="Editar">
              <i class="fas fa-edit"></i>
            </button>
            <button class="btn-prop-accion btn-estado btn-estado-prop" data-id="${p.id}" data-titulo="${esc(p.titulo_anuncio)}" title="Cambiar estado">
              <i class="fas fa-exchange-alt"></i>
            </button>
            <button class="btn-prop-accion btn-del btn-eliminar-prop" data-id="${p.id}" data-titulo="${esc(p.titulo_anuncio)}" title="Eliminar">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </td>
      </tr>`;
  }

  /* ════════════════════════════════════════
     BIND ACCIONES
  ════════════════════════════════════════ */
  function bindAcciones() {
    tablaBody.querySelectorAll('.btn-ver-prop').forEach(btn =>
      btn.addEventListener('click', () => abrirDetalle(btn.dataset.id))
    );
    tablaBody.querySelectorAll('.btn-editar-prop').forEach(btn =>
      btn.addEventListener('click', () => abrirEditar(btn.dataset.id))
    );
    tablaBody.querySelectorAll('.btn-estado-prop').forEach(btn =>
      btn.addEventListener('click', () => abrirModalEstado(btn.dataset.id, btn.dataset.titulo))
    );
    tablaBody.querySelectorAll('.btn-eliminar-prop').forEach(btn =>
      btn.addEventListener('click', () => abrirModalEliminar(btn.dataset.id, btn.dataset.titulo))
    );
  }

  /* ════════════════════════════════════════
     MODAL EDITAR PROPIEDAD
  ════════════════════════════════════════ */
  async function abrirEditar(id) {
    const body = document.getElementById('editarBody');
    body.innerHTML = '<div style="text-align:center;padding:32px;"><div class="loading-spinner" style="margin:0 auto;"></div></div>';
    abrirModal(modalEditarOverlay);

    try {
      const res  = await fetch(`${API}?accion=detalle&id=${id}`);
      const data = await res.json();
      if (!data.ok) throw new Error(data.msg);

      const p = data.propiedad;
      document.getElementById('editarId').value = id;

      // Cargar opciones de selects en paralelo
      const [tiposRes, negociosRes, estadosRes] = await Promise.all([
        fetch(`${API}?accion=opciones&categoria=tipo_inmueble`),
        fetch(`${API}?accion=opciones&categoria=tipo_negocio`),
        fetch(`${API}?accion=opciones&categoria=estado_publicacion`),
      ]);
      const [tiposData, negociosData, estadosData] = await Promise.all([
        tiposRes.json(), negociosRes.json(), estadosRes.json()
      ]);

      const optsTipo    = tiposData.opciones   || [];
      const optsNegocio = negociosData.opciones || [];
      const optsEstado  = estadosData.opciones  || [];

      body.innerHTML = `
        <div class="editar-grid">
          <div class="editar-col">
            <div class="editar-section-label"><i class="fas fa-info-circle"></i> Información general</div>

            <div class="form-group-editar">
              <label>Título del anuncio <span class="req">*</span></label>
              <input type="text" id="eTitulo" class="form-input-editar" value="${esc(p.titulo_anuncio || '')}">
            </div>

            <div class="editar-row-2">
              <div class="form-group-editar">
                <label>Tipo de inmueble</label>
                <select id="eTipoInmueble" class="form-input-editar">
                  <option value="">Seleccionar...</option>
                  ${optsTipo.map(o => `<option value="${o.id}" ${o.id == p.tipo_inmueble_id ? 'selected' : ''}>${esc(o.nombre_opcion)}</option>`).join('')}
                </select>
              </div>
              <div class="form-group-editar">
                <label>Tipo de negocio</label>
                <select id="eTipoNegocio" class="form-input-editar">
                  <option value="">Seleccionar...</option>
                  ${optsNegocio.map(o => `<option value="${o.id}" ${o.id == p.tipo_negocio_id ? 'selected' : ''}>${esc(o.nombre_opcion)}</option>`).join('')}
                </select>
              </div>
            </div>

            <div class="editar-row-2">
              <div class="form-group-editar">
                <label>Precio pedido <span class="req">*</span></label>
                <input type="number" id="ePrecio" class="form-input-editar" value="${p.precio_pedido || ''}">
              </div>
              <div class="form-group-editar">
                <label>Estado</label>
                <select id="eEstado" class="form-input-editar">
                  <option value="">Seleccionar...</option>
                  ${optsEstado.map(o => `<option value="${o.id}" ${o.id == p.estado_publicacion_id ? 'selected' : ''}>${esc(o.nombre_opcion)}</option>`).join('')}
                </select>
              </div>
            </div>

            <div class="form-group-editar">
              <label>Descripción detallada</label>
              <textarea id="eDescripcion" class="form-input-editar" rows="4">${esc(p.descripcion_detallada || '')}</textarea>
            </div>
          </div>

          <div class="editar-col">
            <div class="editar-section-label"><i class="fas fa-map-marker-alt"></i> Ubicación</div>

            <div class="editar-row-2">
              <div class="form-group-editar">
                <label>Departamento</label>
                <input type="text" id="eDepartamento" class="form-input-editar" value="${esc(p.departamento || '')}">
              </div>
              <div class="form-group-editar">
                <label>Municipio</label>
                <input type="text" id="eMunicipio" class="form-input-editar" value="${esc(p.municipio || '')}">
              </div>
            </div>

            <div class="form-group-editar">
              <label>Dirección exacta</label>
              <input type="text" id="eDireccion" class="form-input-editar" value="${esc(p.direccion_exacta || '')}">
            </div>

            <div class="editar-section-label" style="margin-top:14px;"><i class="fas fa-home"></i> Características</div>

            <div class="editar-row-2">
              <div class="form-group-editar">
                <label>Habitaciones</label>
                <input type="number" id="eHabitaciones" class="form-input-editar" value="${p.num_habitaciones || ''}">
              </div>
              <div class="form-group-editar">
                <label>Baños</label>
                <input type="number" id="eBanos" class="form-input-editar" value="${p.num_banos || ''}">
              </div>
            </div>

            <div class="editar-row-2">
              <div class="form-group-editar">
                <label>Metros construcción</label>
                <input type="number" id="eMetrosConstruccion" class="form-input-editar" value="${p.metros_construccion || ''}">
              </div>
              <div class="form-group-editar">
                <label>Metros terreno</label>
                <input type="number" id="eMetrosTerreno" class="form-input-editar" value="${p.metros_terreno || ''}">
              </div>
            </div>

            <div class="editar-row-2">
              <div class="form-group-editar">
                <label class="check-editar">
                  <input type="checkbox" id="eEstacionamiento" ${p.tiene_estacionamiento == 1 ? 'checked' : ''}>
                  <span>Estacionamiento</span>
                </label>
              </div>
              <div class="form-group-editar">
                <label class="check-editar">
                  <input type="checkbox" id="ePiscina" ${p.tiene_piscina == 1 ? 'checked' : ''}>
                  <span>Piscina</span>
                </label>
              </div>
            </div>

            <div class="form-group-editar">
              <label class="check-editar">
                <input type="checkbox" id="eDestacado" ${p.es_anuncio_destacado == 1 ? 'checked' : ''}>
                <span><i class="fas fa-star" style="color:var(--gold-dark);margin-right:4px;"></i> Anuncio destacado</span>
              </label>
            </div>
          </div>
        </div>`;

    } catch(err) {
      body.innerHTML = `<p style="color:#ef4444;padding:16px;">Error: ${err.message}</p>`;
    }
  }

  document.getElementById('btnGuardarEditar')?.addEventListener('click', async () => {
    const id = document.getElementById('editarId').value;
    if (!id) return;

    const titulo = document.getElementById('eTitulo')?.value?.trim();
    if (!titulo) { toast('El título es obligatorio.', 'error'); return; }

    const fd = new FormData();
    fd.append('id',                   id);
    fd.append('titulo_anuncio',       titulo);
    fd.append('precio_pedido',        document.getElementById('ePrecio')?.value || '');
    fd.append('tipo_inmueble_id',     document.getElementById('eTipoInmueble')?.value || '');
    fd.append('tipo_negocio_id',      document.getElementById('eTipoNegocio')?.value || '');
    fd.append('estado_publicacion_id',document.getElementById('eEstado')?.value || '');
    fd.append('departamento',         document.getElementById('eDepartamento')?.value || '');
    fd.append('municipio',            document.getElementById('eMunicipio')?.value || '');
    fd.append('direccion_exacta',     document.getElementById('eDireccion')?.value || '');
    fd.append('descripcion_detallada',document.getElementById('eDescripcion')?.value || '');
    fd.append('num_habitaciones',     document.getElementById('eHabitaciones')?.value || '');
    fd.append('num_banos',            document.getElementById('eBanos')?.value || '');
    fd.append('metros_construccion',  document.getElementById('eMetrosConstruccion')?.value || '');
    fd.append('metros_terreno',       document.getElementById('eMetrosTerreno')?.value || '');
    fd.append('tiene_estacionamiento',document.getElementById('eEstacionamiento')?.checked ? '1' : '0');
    fd.append('tiene_piscina',        document.getElementById('ePiscina')?.checked ? '1' : '0');
    fd.append('es_anuncio_destacado', document.getElementById('eDestacado')?.checked ? '1' : '0');

    const btn = document.getElementById('btnGuardarEditar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

    try {
      const res  = await fetch(`${API}?accion=editar`, { method: 'POST', body: fd });
      const data = await res.json();
      cerrarModal(modalEditarOverlay);
      toast(data.msg, data.ok ? 'ok' : 'error');
      if (data.ok) cargar();
    } catch {
      toast('Error de conexión.', 'error');
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-save"></i> Guardar cambios';
    }
  });

  document.getElementById('modalEditarCerrar')?.addEventListener('click',   () => cerrarModal(modalEditarOverlay));
  document.getElementById('btnCancelarEditar')?.addEventListener('click',   () => cerrarModal(modalEditarOverlay));
  modalEditarOverlay?.addEventListener('click', e => { if (e.target === modalEditarOverlay) cerrarModal(modalEditarOverlay); });

  /* ════════════════════════════════════════
     MODAL VER DETALLE
  ════════════════════════════════════════ */
  async function abrirDetalle(id) {
    const body   = document.getElementById('detalleBody');
    const titulo = document.getElementById('detalleTitulo');
    body.innerHTML = '<div style="text-align:center;padding:32px;"><div class="loading-spinner" style="margin:0 auto;"></div></div>';
    abrirModal(modalDetalleOverlay);

    try {
      const res  = await fetch(`${API}?accion=detalle&id=${id}`);
      const data = await res.json();
      if (!data.ok) throw new Error(data.msg);

      const p    = data.propiedad;
      const fotos = data.fotos || [];
      titulo.textContent = p.titulo_anuncio || 'Detalle de propiedad';

      const precio = '$' + parseFloat(p.precio_pedido || 0).toLocaleString('en-US', { minimumFractionDigits: 0 });

      body.innerHTML = `
        <div class="prop-detalle-grid">

          <!-- Columna izquierda -->
          <div>
            <div style="font-size:.62rem;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;border-left:3px solid var(--gold);padding-left:8px;margin-bottom:12px;">
              <i class="fas fa-info-circle" style="color:var(--gold-dark);margin-right:5px;"></i>Información general
            </div>
            <div class="prop-detalle-grid" style="margin-bottom:16px;">
              <div class="prop-detalle-item">
                <div class="prop-detalle-label"><i class="fas fa-building"></i> Tipo inmueble</div>
                <div class="prop-detalle-val">${esc(p.tipo_inmueble || '—')}</div>
              </div>
              <div class="prop-detalle-item">
                <div class="prop-detalle-label"><i class="fas fa-handshake"></i> Negocio</div>
                <div class="prop-detalle-val">${esc(p.tipo_negocio || '—')}</div>
              </div>
              <div class="prop-detalle-item">
                <div class="prop-detalle-label"><i class="fas fa-dollar-sign"></i> Precio</div>
                <div class="prop-detalle-val" style="font-family:var(--font-d);font-size:1.1rem;color:var(--navy);">${precio}</div>
              </div>
              <div class="prop-detalle-item">
                <div class="prop-detalle-label"><i class="fas fa-tag"></i> Estado</div>
                <div class="prop-detalle-val"><span class="badge-estado-prop">${esc(p.estado_publicacion || '—')}</span></div>
              </div>
            </div>

            <div style="font-size:.62rem;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;border-left:3px solid var(--gold);padding-left:8px;margin-bottom:12px;">
              <i class="fas fa-user-tie" style="color:var(--gold-dark);margin-right:5px;"></i>Vendedor
            </div>
            <div style="margin-bottom:16px;">
              <div class="prop-detalle-val" style="margin-bottom:4px;">${esc(p.vendedor || '—')}</div>
              <div class="td-tipo"><i class="fas fa-envelope" style="color:var(--gold-dark);margin-right:4px;"></i>${esc(p.correo_vendedor || '—')}</div>
              <div class="td-tipo"><i class="fas fa-phone" style="color:var(--gold-dark);margin-right:4px;"></i>${esc(p.telefono_vendedor || '—')}</div>
            </div>

            <div style="font-size:.62rem;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;border-left:3px solid var(--gold);padding-left:8px;margin-bottom:12px;">
              <i class="fas fa-map-marker-alt" style="color:var(--gold-dark);margin-right:5px;"></i>Ubicación
            </div>
            <div class="prop-detalle-grid" style="margin-bottom:16px;">
              <div class="prop-detalle-item">
                <div class="prop-detalle-label">Departamento</div>
                <div class="prop-detalle-val">${esc(p.departamento || '—')}</div>
              </div>
              <div class="prop-detalle-item">
                <div class="prop-detalle-label">Municipio</div>
                <div class="prop-detalle-val">${esc(p.municipio || '—')}</div>
              </div>
            </div>
            <div class="prop-detalle-desc">${esc(p.direccion_exacta || 'Sin dirección exacta')}</div>
          </div>

          <!-- Columna derecha -->
          <div>
            <div style="font-size:.62rem;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;border-left:3px solid var(--gold);padding-left:8px;margin-bottom:12px;">
              <i class="fas fa-home" style="color:var(--gold-dark);margin-right:5px;"></i>Características
            </div>
            <div class="prop-detalle-grid" style="margin-bottom:16px;">
              <div class="prop-detalle-item">
                <div class="prop-detalle-label"><i class="fas fa-bed"></i> Habitaciones</div>
                <div class="prop-detalle-val">${p.num_habitaciones || '—'}</div>
              </div>
              <div class="prop-detalle-item">
                <div class="prop-detalle-label"><i class="fas fa-bath"></i> Baños</div>
                <div class="prop-detalle-val">${p.num_banos || '—'}</div>
              </div>
              <div class="prop-detalle-item">
                <div class="prop-detalle-label"><i class="fas fa-ruler-combined"></i> Construcción</div>
                <div class="prop-detalle-val">${p.metros_construccion ? p.metros_construccion + ' m²' : '—'}</div>
              </div>
              <div class="prop-detalle-item">
                <div class="prop-detalle-label"><i class="fas fa-expand"></i> Terreno</div>
                <div class="prop-detalle-val">${p.metros_terreno ? p.metros_terreno + ' m²' : '—'}</div>
              </div>
              <div class="prop-detalle-item">
                <div class="prop-detalle-label"><i class="fas fa-car"></i> Estacionamiento</div>
                <div class="prop-detalle-val">${p.tiene_estacionamiento == 1 ? '<i class="fas fa-check" style="color:var(--gold-dark);"></i> Sí' : 'No'}</div>
              </div>
              <div class="prop-detalle-item">
                <div class="prop-detalle-label"><i class="fas fa-swimming-pool"></i> Piscina</div>
                <div class="prop-detalle-val">${p.tiene_piscina == 1 ? '<i class="fas fa-check" style="color:var(--gold-dark);"></i> Sí' : 'No'}</div>
              </div>
            </div>

            ${p.descripcion_detallada ? `
            <div style="font-size:.62rem;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;border-left:3px solid var(--gold);padding-left:8px;margin-bottom:12px;">
              <i class="fas fa-align-left" style="color:var(--gold-dark);margin-right:5px;"></i>Descripción
            </div>
            <div class="prop-detalle-desc" style="margin-bottom:16px;">${esc(p.descripcion_detallada)}</div>
            ` : ''}

            <div style="font-size:.62rem;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;border-left:3px solid var(--gold);padding-left:8px;margin-bottom:12px;">
              <i class="fas fa-images" style="color:var(--gold-dark);margin-right:5px;"></i>Fotos (${fotos.length})
            </div>
            <div class="prop-fotos-grid">
              ${fotos.length
                ? fotos.slice(0, 6).map(f => `
                    <img src="../${esc(f.url_foto_miniatura || f.url_foto_original)}"
                         class="prop-foto-thumb"
                         onclick="window.open('../${esc(f.url_foto_original)}','_blank')"
                         onerror="this.style.display='none'">`).join('')
                : '<p class="td-tipo">Sin fotos disponibles</p>'
              }
            </div>
          </div>

        </div>`;

    } catch (err) {
      body.innerHTML = `<p style="color:#ef4444;padding:16px;">Error: ${err.message}</p>`;
    }
  }

  document.getElementById('modalDetalleCerrar')?.addEventListener('click', () => cerrarModal(modalDetalleOverlay));
  document.getElementById('btnCerrarDetalle')?.addEventListener('click',   () => cerrarModal(modalDetalleOverlay));
  modalDetalleOverlay?.addEventListener('click', e => { if (e.target === modalDetalleOverlay) cerrarModal(modalDetalleOverlay); });

  /* ════════════════════════════════════════
     MODAL CAMBIAR ESTADO
  ════════════════════════════════════════ */
  async function abrirModalEstado(id, titulo) {
    document.getElementById('propiedadEstadoId').value            = id;
    document.getElementById('propiedadEstadoNombre').textContent  = titulo;

    const select = document.getElementById('selectNuevoEstado');
    select.innerHTML = '<option value="">Cargando...</option>';
    abrirModal(modalEstadoOverlay);

    try {
      const res  = await fetch(`${API}?accion=estados`);
      const data = await res.json();
      if (data.ok) {
        select.innerHTML = '<option value="">Seleccionar estado...</option>' +
          data.estados.map(e => `<option value="${e.id}">${esc(e.nombre_opcion)}</option>`).join('');
      } else {
        select.innerHTML = '<option value="">Error al cargar</option>';
      }
    } catch {
      select.innerHTML = '<option value="">Error al cargar</option>';
    }
  }

  document.getElementById('btnConfirmarEstado')?.addEventListener('click', async () => {
    const id       = document.getElementById('propiedadEstadoId').value;
    const estadoId = document.getElementById('selectNuevoEstado').value;
    if (!estadoId) { toast('Selecciona un estado.', 'error'); return; }

    const fd = new FormData();
    fd.append('id', id); fd.append('estado_id', estadoId);

    try {
      const res  = await fetch(`${API}?accion=estado`, { method: 'POST', body: fd });
      const data = await res.json();
      cerrarModal(modalEstadoOverlay);
      toast(data.msg, data.ok ? 'ok' : 'error');
      if (data.ok) cargar();
    } catch { toast('Error al cambiar estado', 'error'); }
  });

  document.getElementById('btnCancelarEstado')?.addEventListener('click', () => cerrarModal(modalEstadoOverlay));
  document.getElementById('modalEstadoCerrar')?.addEventListener('click', () => cerrarModal(modalEstadoOverlay));
  modalEstadoOverlay?.addEventListener('click', e => { if (e.target === modalEstadoOverlay) cerrarModal(modalEstadoOverlay); });

  /* ════════════════════════════════════════
     MODAL ELIMINAR
  ════════════════════════════════════════ */
  function abrirModalEliminar(id, titulo) {
    document.getElementById('idEliminar').value           = id;
    document.getElementById('nombreEliminar').textContent = titulo;
    abrirModal(modalEliminarOverlay);
  }

  document.getElementById('btnConfirmarEliminar')?.addEventListener('click', async () => {
    const id = document.getElementById('idEliminar').value;
    const fd = new FormData(); fd.append('id', id);

    try {
      const res  = await fetch(`${API}?accion=eliminar`, { method: 'POST', body: fd });
      const data = await res.json();
      cerrarModal(modalEliminarOverlay);
      toast(data.msg, data.ok ? 'ok' : 'error');
      if (data.ok) cargar();
    } catch { toast('Error al eliminar', 'error'); }
  });

  document.getElementById('btnCancelarEliminar')?.addEventListener('click', () => cerrarModal(modalEliminarOverlay));
  document.getElementById('modalEliminarCerrar')?.addEventListener('click', () => cerrarModal(modalEliminarOverlay));
  modalEliminarOverlay?.addEventListener('click', e => { if (e.target === modalEliminarOverlay) cerrarModal(modalEliminarOverlay); });

  /* ════════════════════════════════════════
     HELPERS
  ════════════════════════════════════════ */
  function abrirModal(overlay)  { overlay?.classList.add('open'); }
  function cerrarModal(overlay) { overlay?.classList.remove('open'); }

  function esc(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function toast(msg, tipo = 'ok') {
    const wrap = document.getElementById('toastWrap');
    if (!wrap) return;
    const icon = tipo === 'ok' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
    const t = document.createElement('div');
    t.className = `toast toast-${tipo}`;
    t.innerHTML = `<i class="${icon}"></i><span>${msg}</span>`;
    wrap.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 300); }, 3000);
  }

  function debounce(fn) {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(fn, 400);
  }

  /* ════════════════════════════════════════
     EVENT LISTENERS
  ════════════════════════════════════════ */
  document.getElementById('btnFiltrar')?.addEventListener('click', cargar);
  document.getElementById('btnRefresh')?.addEventListener('click', cargar);
  document.getElementById('btnLimpiar')?.addEventListener('click', () => {
    if (filtroBuscar)  filtroBuscar.value  = '';
    if (filtroTipo)    filtroTipo.value    = '';
    if (filtroNegocio) filtroNegocio.value = '';
    if (filtroEstado)  filtroEstado.value  = '';
    cargar();
  });

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

  cargar();

})();