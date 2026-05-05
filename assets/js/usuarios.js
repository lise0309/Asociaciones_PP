/**
 * usuarios.js
 * PP Bienes Raíces — assets/js/usuarios.js
 * CRUD completo de usuarios conectado al controlador
 */

(function () {
  'use strict';

  // ── Estado ──
  let modoEdicion = false;
  let idEditando  = '';

  // ── Referencias DOM ──
  const tablaBody          = document.getElementById('tablaBody');
  const subtituloTabla     = document.getElementById('subtituloTabla');
  const filtroBuscar       = document.getElementById('filtroBuscar');
  const filtroRol          = document.getElementById('filtroRol');
  const filtroEstado       = document.getElementById('filtroEstado');
  const filtroVerificado   = document.getElementById('filtroVerificado');
  const btnNuevoUsuario    = document.getElementById('btnNuevoUsuario');

  // Modal crear/editar
  const modalOverlay       = document.getElementById('modalOverlay');
  const modalTitulo        = document.getElementById('modalTitulo');
  const modalCerrar        = document.getElementById('modalCerrar');
  const btnCancelar        = document.getElementById('btnCancelar');
  const btnGuardar         = document.getElementById('btnGuardar');
  const formUsuario        = document.getElementById('formUsuario');
  const checkActivoWrap    = document.getElementById('checkActivoWrap');
  const claveHint          = document.getElementById('claveHint');
  const claveReq           = document.getElementById('claveReq');

  // Modal eliminar
  const modalEliminarOverlay  = document.getElementById('modalEliminarOverlay');
  const modalEliminarCerrar   = document.getElementById('modalEliminarCerrar');
  const btnCancelarEliminar   = document.getElementById('btnCancelarEliminar');
  const btnConfirmarEliminar  = document.getElementById('btnConfirmarEliminar');
  const nombreEliminar        = document.getElementById('nombreEliminar');
  const idEliminar            = document.getElementById('idEliminar');

  // KPIs
  const kpiTotal       = document.getElementById('kpiTotal');
  const kpiVerificados = document.getElementById('kpiVerificados');
  const kpiPendientes  = document.getElementById('kpiPendientes');
  const kpiSuspendidos = document.getElementById('kpiSuspendidos');

  // ════════════════════════════════════════
  // CARGAR USUARIOS
  // ════════════════════════════════════════
  async function cargarUsuarios() {
    tablaBody.innerHTML = `
      <tr>
        <td colspan="7" class="tabla-loading">
          <div class="loading-spinner"></div>
          Cargando usuarios...
        </td>
      </tr>`;

    const params = new URLSearchParams({
      accion:             'listar',
      buscar:             filtroBuscar.value,
      rol:                filtroRol.value,
      cuenta_activa:      filtroEstado.value,
      cuenta_verificada:  filtroVerificado.value,
    });

    try {
      const res  = await fetch(`${CTRL_URL}?${params}`);
      const data = await res.json();

      if (!data.ok) throw new Error(data.msg);

      // Actualizar KPIs
      if (data.stats) {
        kpiTotal.textContent       = data.stats.total         ?? 0;
        kpiVerificados.textContent = data.stats.agentes_verificados ?? 0;
        kpiPendientes.textContent  = data.stats.pendientes    ?? 0;
        kpiSuspendidos.textContent = data.stats.suspendidos   ?? 0;
      }

      subtituloTabla.textContent = `${data.usuarios.length} usuario(s) encontrado(s)`;

      if (!data.usuarios.length) {
        tablaBody.innerHTML = `
          <tr>
            <td colspan="7" class="tabla-empty">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path d="M7 8a3 3 0 100-6 3 3 0 000 6zM14.5 9a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"/>
              </svg>
              No se encontraron usuarios con esos filtros.
            </td>
          </tr>`;
        return;
      }

      tablaBody.innerHTML = data.usuarios.map(u => renderFila(u)).join('');
      bindAcciones();

    } catch (err) {
      tablaBody.innerHTML = `<tr><td colspan="7" class="tabla-empty">Error al cargar usuarios: ${err.message}</td></tr>`;
    }
  }

  // ════════════════════════════════════════
  // RENDER FILA
  // ════════════════════════════════════════
  function renderFila(u) {
    const iniciales = (u.nombre[0] + (u.apellido[0] ?? '')).toUpperCase();
    const verificadoBadge = u.cuenta_verificada == 1
      ? '<span class="badge badge-activa">Verificado</span>'
      : '<span class="badge badge-pendiente">Pendiente</span>';
    const estadoBadge = u.cuenta_activa == 1
      ? '<span class="badge badge-activa">Activo</span>'
      : '<span class="badge badge-rechazada">Suspendido</span>';
    const rolBadge = u.rol === 'admin'
      ? '<span class="badge badge-admin">Admin</span>'
      : '<span class="badge badge-vendedor">Vendedor</span>';

    const fecha = u.fecha_registro
      ? new Date(u.fecha_registro).toLocaleDateString('es-SV', { day:'2-digit', month:'short', year:'numeric' })
      : '—';

    const btnVerificar = u.cuenta_verificada == 0
      ? `<button class="btn-panel btn-panel-primary btn-panel-sm btn-verificar" data-id="${u.id}">Verificar</button>`
      : '';

    const btnEstado = u.cuenta_activa == 1
      ? `<button class="btn-panel btn-panel-danger btn-panel-sm btn-estado" data-id="${u.id}" data-activo="0">Suspender</button>`
      : `<button class="btn-panel btn-panel-outline btn-panel-sm btn-estado" data-id="${u.id}" data-activo="1">Reactivar</button>`;

    return `
      <tr>
        <td>
          <div class="user-cell">
            <div class="user-av">${iniciales}</div>
            <div class="user-cell-info">
              <span class="user-cell-nombre">${u.nombre} ${u.apellido}</span>
              <span class="user-cell-correo">${u.correo}</span>
            </div>
          </div>
        </td>
        <td>${rolBadge}</td>
        <td>${u.telefono || '—'}</td>
        <td>${verificadoBadge}</td>
        <td>${estadoBadge}</td>
        <td>${fecha}</td>
        <td>
          <div class="acciones-cell">
            <button class="btn-panel btn-panel-outline btn-panel-sm btn-editar" data-id="${u.id}">Editar</button>
            ${btnVerificar}
            ${btnEstado}
            <button class="btn-panel btn-panel-danger btn-panel-sm btn-eliminar" data-id="${u.id}" data-nombre="${u.nombre} ${u.apellido}">Eliminar</button>
          </div>
        </td>
      </tr>`;
  }

  // ════════════════════════════════════════
  // BIND ACCIONES DE TABLA
  // ════════════════════════════════════════
  function bindAcciones() {
    // Editar
    tablaBody.querySelectorAll('.btn-editar').forEach(btn => {
      btn.addEventListener('click', () => abrirEditar(btn.dataset.id));
    });
    // Verificar
    tablaBody.querySelectorAll('.btn-verificar').forEach(btn => {
      btn.addEventListener('click', () => verificarUsuario(btn.dataset.id));
    });
    // Cambiar estado
    tablaBody.querySelectorAll('.btn-estado').forEach(btn => {
      btn.addEventListener('click', () => cambiarEstado(btn.dataset.id, btn.dataset.activo));
    });
    // Eliminar
    tablaBody.querySelectorAll('.btn-eliminar').forEach(btn => {
      btn.addEventListener('click', () => abrirEliminar(btn.dataset.id, btn.dataset.nombre));
    });
  }

  // ════════════════════════════════════════
  // MODAL CREAR
  // ════════════════════════════════════════
  function abrirCrear() {
    modoEdicion = false;
    idEditando  = '';
    modalTitulo.textContent = 'Nuevo usuario';
    formUsuario.reset();
    limpiarErrores();
    checkActivoWrap.style.display = 'none';
    claveHint.textContent = '';
    claveReq.style.display = 'inline';
    document.getElementById('fActivo').checked = true;
    abrirModal(modalOverlay);
  }

  // ════════════════════════════════════════
  // MODAL EDITAR
  // ════════════════════════════════════════
  async function abrirEditar(id) {
    modoEdicion = true;
    idEditando  = id;
    modalTitulo.textContent = 'Editar usuario';
    claveHint.textContent   = 'Dejar vacío para no cambiar la contraseña.';
    claveReq.style.display  = 'none';
    checkActivoWrap.style.display = '';
    limpiarErrores();

    try {
      const res  = await fetch(`${CTRL_URL}?accion=obtener&id=${id}`);
      const data = await res.json();
      if (!data.ok) throw new Error(data.msg);

      const u = data.usuario;
      document.getElementById('usuarioId').value    = u.id;
      document.getElementById('fNombre').value      = u.nombre;
      document.getElementById('fApellido').value    = u.apellido;
      document.getElementById('fCorreo').value      = u.correo;
      document.getElementById('fClave').value       = '';
      document.getElementById('fRol').value         = u.rol;
      document.getElementById('fTelefono').value    = u.telefono    ?? '';
      document.getElementById('fDescripcion').value = u.descripcion_personal ?? '';
      document.getElementById('fVerificado').checked = u.cuenta_verificada == 1;
      document.getElementById('fActivo').checked     = u.cuenta_activa    == 1;

      abrirModal(modalOverlay);
    } catch (err) {
      toast('Error al cargar el usuario.', 'error');
    }
  }

  // ════════════════════════════════════════
  // GUARDAR (CREAR O EDITAR)
  // ════════════════════════════════════════
  async function guardar() {
    limpiarErrores();

    const formData = new FormData(formUsuario);
    // Checkboxes manuales
    formData.set('cuenta_verificada', document.getElementById('fVerificado').checked ? '1' : '0');
    formData.set('cuenta_activa',     document.getElementById('fActivo').checked     ? '1' : '0');

    const url = modoEdicion
      ? `${CTRL_URL}?accion=editar&id=${idEditando}`
      : `${CTRL_URL}?accion=crear`;

    try {
      btnGuardar.disabled = true;
      btnGuardar.textContent = 'Guardando...';

      const res  = await fetch(url, { method: 'POST', body: formData });
      const data = await res.json();

      if (!data.ok) {
        if (data.errores) mostrarErrores(data.errores);
        else toast(data.msg, 'error');
        return;
      }

      cerrarModal(modalOverlay);
      toast(data.msg, 'ok');
      cargarUsuarios();

    } catch (err) {
      toast('Error de conexión.', 'error');
    } finally {
      btnGuardar.disabled = false;
      btnGuardar.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>
        Guardar`;
    }
  }

  // ════════════════════════════════════════
  // VERIFICAR
  // ════════════════════════════════════════
  async function verificarUsuario(id) {
    try {
      const res  = await fetch(`${CTRL_URL}?accion=verificar&id=${id}`, { method: 'POST' });
      const data = await res.json();
      toast(data.msg, data.ok ? 'ok' : 'error');
      if (data.ok) cargarUsuarios();
    } catch {
      toast('Error de conexión.', 'error');
    }
  }

  // ════════════════════════════════════════
  // CAMBIAR ESTADO
  // ════════════════════════════════════════
  async function cambiarEstado(id, activo) {
    try {
      const body = new FormData();
      body.append('activo', activo);
      const res  = await fetch(`${CTRL_URL}?accion=estado&id=${id}`, { method: 'POST', body });
      const data = await res.json();
      toast(data.msg, data.ok ? 'ok' : 'error');
      if (data.ok) cargarUsuarios();
    } catch {
      toast('Error de conexión.', 'error');
    }
  }

  // ════════════════════════════════════════
  // ELIMINAR
  // ════════════════════════════════════════
  function abrirEliminar(id, nombre) {
    idEliminar.value          = id;
    nombreEliminar.textContent = nombre;
    abrirModal(modalEliminarOverlay);
  }

  async function confirmarEliminar() {
    const id = idEliminar.value;
    try {
      const res  = await fetch(`${CTRL_URL}?accion=eliminar&id=${id}`, { method: 'POST' });
      const data = await res.json();
      cerrarModal(modalEliminarOverlay);
      toast(data.msg, data.ok ? 'ok' : 'error');
      if (data.ok) cargarUsuarios();
    } catch {
      toast('Error de conexión.', 'error');
    }
  }

  // ════════════════════════════════════════
  // HELPERS MODAL
  // ════════════════════════════════════════
  function abrirModal(overlay)  { overlay.classList.add('open'); }
  function cerrarModal(overlay) { overlay.classList.remove('open'); }

  function limpiarErrores() {
    document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
    document.querySelectorAll('.form-group.has-error').forEach(el => el.classList.remove('has-error'));
  }

  function mostrarErrores(errores) {
    const mapa = {
      nombre:   'eNombre',
      apellido: 'eApellido',
      correo:   'eCorreo',
      clave:    'eClave',
      rol:      'eRol',
    };
    Object.entries(errores).forEach(([campo, msg]) => {
      const el = document.getElementById(mapa[campo]);
      const gr = el?.closest('.form-group');
      if (el) el.textContent = msg;
      if (gr) gr.classList.add('has-error');
    });
  }

  // ════════════════════════════════════════
  // TOAST
  // ════════════════════════════════════════
  function toast(msg, tipo = 'ok') {
    const wrap = document.getElementById('toastWrap');
    const icono = tipo === 'ok'
      ? `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>`
      : `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>`;

    const t = document.createElement('div');
    t.className = `toast toast-${tipo}`;
    t.innerHTML = `${icono}<span>${msg}</span>`;
    wrap.appendChild(t);

    setTimeout(() => {
      t.style.transition = 'opacity .4s ease, transform .4s ease';
      t.style.opacity    = '0';
      t.style.transform  = 'translateX(10px)';
      setTimeout(() => t.remove(), 400);
    }, 3500);
  }

  // ════════════════════════════════════════
  // EVENT LISTENERS
  // ════════════════════════════════════════

  // Filtros con debounce
  let debounceTimer;
  [filtroBuscar, filtroRol, filtroEstado, filtroVerificado].forEach(el => {
    el?.addEventListener('input', () => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(cargarUsuarios, 350);
    });
  });

  // Botón nuevo usuario
  btnNuevoUsuario?.addEventListener('click', abrirCrear);

  // Guardar
  btnGuardar?.addEventListener('click', guardar);
  btnCancelar?.addEventListener('click', () => cerrarModal(modalOverlay));
  modalCerrar?.addEventListener('click', () => cerrarModal(modalOverlay));

  // Cerrar al click fuera
  modalOverlay?.addEventListener('click', (e) => {
    if (e.target === modalOverlay) cerrarModal(modalOverlay);
  });

  // Eliminar
  btnConfirmarEliminar?.addEventListener('click', confirmarEliminar);
  btnCancelarEliminar?.addEventListener('click', () => cerrarModal(modalEliminarOverlay));
  modalEliminarCerrar?.addEventListener('click', () => cerrarModal(modalEliminarOverlay));
  modalEliminarOverlay?.addEventListener('click', (e) => {
    if (e.target === modalEliminarOverlay) cerrarModal(modalEliminarOverlay);
  });

  // Cerrar con Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      cerrarModal(modalOverlay);
      cerrarModal(modalEliminarOverlay);
    }
  });

  // ── Inicializar ──
  cargarUsuarios();

})();