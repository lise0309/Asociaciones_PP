/**
 * contratos.js — PP Bienes Raíces
 * Contratos + Plantillas
 */
'use strict';

const MODO = window.MODO || 'contratos';

document.addEventListener('DOMContentLoaded', () => {
    if (MODO === 'plantillas') initPlantillas();
    else initContratos();
});

/* ══════════════════════════════════════
   CONTRATOS
══════════════════════════════════════ */
function initContratos() {
    cargarContratos();
    document.getElementById('btnRefresh')?.addEventListener('click', cargarContratos);

    const form = document.getElementById('formContrato');
    form?.addEventListener('submit', async e => {
        e.preventDefault();
        const btn = document.getElementById('btnCrear');
        btn.disabled = true;
        btn.innerHTML = '<div style="display:inline-block;width:16px;height:16px;border:2px solid rgba(255,212,90,.4);border-top-color:#FFD45A;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;margin-right:8px;"></div> Creando...';

        const fd = new FormData(form);
        fd.append('accion', 'crear');

        try {
            const res  = await fetch('../controllers/contratocontroller.php?action=crear', { method:'POST', body:fd });
            const data = await res.json();
            if (data.ok) {
                toast('Contrato creado exitosamente', 'ok');
                form.reset();
                cargarContratos();
            } else {
                toast(data.msg || 'Error al crear', 'error');
            }
        } catch(e) { toast('Error de conexión', 'error'); }
        finally {
            btn.disabled = false;
            btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:18px;height:18px;"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 00-1.5 0v2.5h-2.5a.75.75 0 000 1.5h2.5v2.5a.75.75 0 001.5 0v-2.5h2.5a.75.75 0 000-1.5h-2.5v-2.5z" clip-rule="evenodd"/></svg> Crear contrato';
        }
    });
}

async function cargarContratos() {
    const el = document.getElementById('listaContratos');
    if (!el) return;
    el.innerHTML = '<div class="loading-state"><div class="loading-spinner"></div>Cargando...</div>';

    try {
        const res  = await fetch('../controllers/contratocontroller.php?action=listar');
        const data = await res.json();

        if (!data.ok || !data.contratos?.length) {
            el.innerHTML = `<div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                No hay contratos aún. Crea uno nuevo.
            </div>`;
            actualizarKpis([]);
            return;
        }

        actualizarKpis(data.contratos);

        el.innerHTML = data.contratos.map(c => {
            const color    = c.estado_color || '#6B7280';
            const monto    = new Intl.NumberFormat('en-US',{style:'currency',currency:c.moneda||'USD'}).format(c.monto_acordado);
            const fecha    = new Date(c.fecha_generacion).toLocaleDateString('es-SV',{day:'2-digit',month:'short',year:'numeric'});
            const num      = c.id.substring(0,8).toUpperCase();
            const esAnulado = c.estado_nombre === 'Anulado';

            return `
            <div class="contrato-item">
                <div class="ci-head">
                    <span class="ci-num">#${num}</span>
                    <span class="ci-badge" style="background:${color}20;color:${color};">${esc(c.estado_nombre)}</span>
                    <span style="margin-left:auto;font-size:.75rem;color:var(--muted);">${fecha}</span>
                </div>
                <div class="ci-body">
                    <strong>${esc(c.nombre_comprador)}</strong><br>
                    🏠 ${esc(c.titulo_anuncio)}<br>
                    📄 ${esc(c.tipo_nombre)} · ${esc(c.nombre_plantilla)}<br>
                    <span class="monto">${monto}</span>
                </div>
                <div class="ci-actions">
                    <button class="btn-sm btn-outline" onclick="verContrato('${c.id}')">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"/><path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41z" clip-rule="evenodd"/></svg>
                        Ver
                    </button>
                    <button class="btn-sm btn-primary-sm" onclick="generarDocumento('${c.id}')">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 2.75a.75.75 0 00-1.5 0v8.614L6.295 8.235a.75.75 0 10-1.09 1.03l4.25 4.5a.75.75 0 001.09 0l4.25-4.5a.75.75 0 00-1.09-1.03l-2.955 3.129V2.75z"/><path d="M3.5 12.75a.75.75 0 00-1.5 0v2.5A2.75 2.75 0 004.75 18h10.5A2.75 2.75 0 0018 15.25v-2.5a.75.75 0 00-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5z"/></svg>
                        Descargar
                    </button>
                    ${!esAnulado ? `
                    <button class="btn-sm btn-danger-sm" onclick="eliminarContrato('${c.id}','${esc(c.estado_nombre)}')">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4z" clip-rule="evenodd"/></svg>
                        ${c.estado_nombre === 'Borrador' ? 'Eliminar' : 'Anular'}
                    </button>` : ''}
                </div>
            </div>`;
        }).join('');

    } catch(e) {
        el.innerHTML = '<div class="empty-state">Error al cargar contratos</div>';
    }
}

function actualizarKpis(contratos) {
    const total    = contratos.length;
    const borrador = contratos.filter(c=>c.estado_nombre==='Borrador').length;
    const firmado  = contratos.filter(c=>c.estado_nombre==='Firmado').length;
    const monto    = contratos.reduce((s,c)=>s+parseFloat(c.monto_acordado||0),0);
    if (document.getElementById('kpiTotal'))    document.getElementById('kpiTotal').textContent    = total;
    if (document.getElementById('kpiBorrador')) document.getElementById('kpiBorrador').textContent = borrador;
    if (document.getElementById('kpiFirmado'))  document.getElementById('kpiFirmado').textContent  = firmado;
    if (document.getElementById('kpiMonto'))    document.getElementById('kpiMonto').textContent    = '$'+monto.toLocaleString('en-US',{minimumFractionDigits:0,maximumFractionDigits:0});
}

/* ── VER CONTRATO (modal completo) ── */
async function verContrato(id) {
    cerrarModal();
    const modal = crearModal('Detalle del contrato', '<div style="text-align:center;padding:32px;color:#6B7280;">Cargando...</div>');

    try {
        const res  = await fetch(`../controllers/contratocontroller.php?action=ver&id=${id}`);
        const data = await res.json();
        if (!data.ok) { modal.querySelector('.modal-body').innerHTML = `<p style="color:red">${data.msg}</p>`; return; }

        const c     = data.contrato;
        const monto = new Intl.NumberFormat('en-US',{style:'currency',currency:c.moneda||'USD'}).format(c.monto_acordado);
        const fecha = new Date(c.fecha_generacion).toLocaleDateString('es-SV',{day:'2-digit',month:'long',year:'numeric'});
        const color = c.estado_color || '#6B7280';

        // Cargar estados disponibles
        const resEst  = await fetch('../controllers/contratocontroller.php?action=estados');
        const dataEst = await resEst.json();
        const optsEst = (dataEst.estados || []).map(e =>
            `<option value="${e.id}" ${e.nombre_opcion===c.estado_nombre?'selected':''}>${esc(e.nombre_opcion)}</option>`
        ).join('');

        modal.querySelector('.modal-body').innerHTML = `
            <!-- Badges -->
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
                <span style="font-family:monospace;font-size:.8rem;font-weight:700;background:rgba(26,25,83,.08);color:#1A1953;padding:4px 12px;border-radius:999px;">#${c.id.substring(0,8).toUpperCase()}</span>
                <span style="font-size:.72rem;font-weight:700;padding:4px 12px;border-radius:999px;background:${color}20;color:${color};" id="badgeEstado">${esc(c.estado_nombre)}</span>
            </div>

            <!-- Datos -->
            <table style="width:100%;border-collapse:collapse;font-size:.875rem;margin-bottom:24px;">
                <tr><td style="padding:8px 0;color:#6B7280;width:38%;">Tipo</td><td style="font-weight:600;color:#1C1C2E;">${esc(c.tipo_nombre)}</td></tr>
                <tr style="border-top:1px solid #E5E7EB;"><td style="padding:8px 0;color:#6B7280;">Plantilla</td><td style="font-weight:600;">${esc(c.nombre_plantilla)}</td></tr>
                <tr style="border-top:1px solid #E5E7EB;"><td style="padding:8px 0;color:#6B7280;">Propiedad</td><td style="font-weight:700;color:#1A1953;">${esc(c.titulo_anuncio)}</td></tr>
                <tr style="border-top:1px solid #E5E7EB;"><td style="padding:8px 0;color:#6B7280;">Ubicación</td><td>${esc(c.municipio)}, ${esc(c.departamento)}</td></tr>
                <tr style="border-top:1px solid #E5E7EB;"><td style="padding:8px 0;color:#6B7280;">Comprador</td><td style="font-weight:700;">${esc(c.nombre_comprador)}</td></tr>
                <tr style="border-top:1px solid #E5E7EB;"><td style="padding:8px 0;color:#6B7280;">Correo</td><td>${esc(c.correo_comprador)}</td></tr>
                <tr style="border-top:1px solid #E5E7EB;"><td style="padding:8px 0;color:#6B7280;">DUI</td><td>${esc(c.dui_comprador)}</td></tr>
                <tr style="border-top:1px solid #E5E7EB;"><td style="padding:8px 0;color:#6B7280;">Monto</td><td style="font-weight:800;font-size:1rem;color:#1A1953;">${monto}</td></tr>
                <tr style="border-top:1px solid #E5E7EB;"><td style="padding:8px 0;color:#6B7280;">Vendedor</td><td>${esc(c.vendedor_nombre)}</td></tr>
                <tr style="border-top:1px solid #E5E7EB;"><td style="padding:8px 0;color:#6B7280;">Fecha</td><td>${fecha}</td></tr>
                ${c.archivo_generado ? `<tr style="border-top:1px solid #E5E7EB;"><td style="padding:8px 0;color:#6B7280;">Documento</td><td><span style="color:#16a34a;font-weight:600;">✓ Generado</span></td></tr>` : ''}
            </table>

            <!-- Cambiar estado -->
            <div style="background:rgba(26,25,83,.04);border-radius:12px;padding:16px;margin-bottom:16px;">
                <div style="font-size:.72rem;font-weight:800;color:#1A1953;letter-spacing:.1em;text-transform:uppercase;margin-bottom:10px;">Cambiar estado</div>
                <div style="display:flex;gap:10px;">
                    <select id="selectEstado" style="flex:1;padding:10px 14px;border:1.5px solid #E5E7EB;border-radius:10px;font-family:Inter,sans-serif;font-size:.875rem;outline:none;">
                        ${optsEst}
                    </select>
                    <button onclick="cambiarEstado('${c.id}')" style="padding:10px 18px;background:#1A1953;color:#FFD45A;border:none;border-radius:10px;font-weight:700;cursor:pointer;font-size:.82rem;white-space:nowrap;">
                        ✓ Aplicar
                    </button>
                </div>
            </div>

            <!-- Botones -->
            <div style="display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap;">
                <button onclick="cerrarModal()" style="padding:10px 20px;border-radius:999px;border:1.5px solid #E5E7EB;background:white;color:#6B7280;cursor:pointer;font-weight:600;font-size:.82rem;">Cerrar</button>
                <button onclick="generarDocumento('${c.id}')" style="padding:10px 20px;border-radius:999px;background:#1A1953;color:#FFD45A;border:none;cursor:pointer;font-weight:700;font-size:.82rem;display:flex;align-items:center;gap:6px;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;"><path d="M10.75 2.75a.75.75 0 00-1.5 0v8.614L6.295 8.235a.75.75 0 10-1.09 1.03l4.25 4.5a.75.75 0 001.09 0l4.25-4.5a.75.75 0 00-1.09-1.03l-2.955 3.129V2.75z"/><path d="M3.5 12.75a.75.75 0 00-1.5 0v2.5A2.75 2.75 0 004.75 18h10.5A2.75 2.75 0 0018 15.25v-2.5a.75.75 0 00-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5z"/></svg>
                    Generar y descargar
                </button>
            </div>`;

    } catch(e) {
        modal.querySelector('.modal-body').innerHTML = '<p style="color:red">Error al cargar</p>';
    }
}

/* ── CAMBIAR ESTADO ── */
async function cambiarEstado(contratoId) {
    const sel = document.getElementById('selectEstado');
    if (!sel) return;
    const estadoId = sel.value;

    const fd = new FormData();
    fd.append('accion', 'estado');
    fd.append('id', contratoId);
    fd.append('estado_id', estadoId);

    try {
        const res  = await fetch('../controllers/contratocontroller.php?action=estado', { method:'POST', body:fd });
        const data = await res.json();
        if (data.ok) {
            toast('Estado actualizado', 'ok');
            cerrarModal();
            cargarContratos();
        } else {
            toast(data.msg || 'Error', 'error');
        }
    } catch(e) { toast('Error de conexión', 'error'); }
}

/* ── GENERAR Y DESCARGAR DOCUMENTO ── */
function generarDocumento(contratoId) {
    toast('Generando documento...', 'ok');
    // Descarga directa via link
    const a = document.createElement('a');
    a.href = `../controllers/generarcontratocontroller.php?id=${contratoId}`;
    a.download = '';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);

    // Recargar lista después de unos segundos para actualizar estado
    setTimeout(() => cargarContratos(), 2000);
}

/* ── ELIMINAR / ANULAR ── */
async function eliminarContrato(id, estado) {
    const accion = estado === 'Borrador' ? 'eliminar permanentemente' : 'anular';
    const confirmado = await modalConfirmar(`¿Deseas <strong>${accion}</strong> este contrato?`);
    if (!confirmado) return;
    try {
        const res  = await fetch(`../controllers/contratocontroller.php?action=eliminar&id=${id}`);
        const data = await res.json();
        toast(data.ok ? 'Contrato ' + (estado==='Borrador'?'eliminado':'anulado') : data.msg, data.ok?'ok':'error');
        if (data.ok) cargarContratos();
    } catch(e) { toast('Error de conexión','error'); }
}

/* ══════════════════════════════════════
   MODAL HELPER
══════════════════════════════════════ */
function crearModal(titulo, bodyHtml) {
    cerrarModal();
    const overlay = document.createElement('div');
    overlay.id = 'modalContratoOverlay';
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(15,15,46,.6);backdrop-filter:blur(4px);z-index:2000;display:flex;align-items:center;justify-content:center;padding:20px;';
    overlay.innerHTML = `
        <div style="background:#FFFCFB;border-radius:20px;width:100%;max-width:560px;max-height:90vh;overflow-y:auto;box-shadow:0 24px 64px rgba(0,0,0,.3);animation:slideUp .25s ease;">
            <div style="padding:18px 24px;background:linear-gradient(135deg,#1A1953,#252477);border-radius:20px 20px 0 0;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:1;">
                <span style="font-family:'Playfair Display',serif;font-size:1.05rem;font-weight:700;color:#FFD45A;">${titulo}</span>
                <button onclick="cerrarModal()" style="background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);width:34px;height:34px;border-radius:50%;cursor:pointer;color:white;font-size:1rem;display:flex;align-items:center;justify-content:center;transition:all .2s;">✕</button>
            </div>
            <div class="modal-body" style="padding:24px;">${bodyHtml}</div>
        </div>`;
    overlay.addEventListener('click', e => { if (e.target === overlay) cerrarModal(); });
    document.body.appendChild(overlay);
    document.addEventListener('keydown', onEscModal);
    return overlay;
}

function cerrarModal() {
    document.getElementById('modalContratoOverlay')?.remove();
    document.removeEventListener('keydown', onEscModal);
}

function onEscModal(e) { if (e.key === 'Escape') cerrarModal(); }

/* ══════════════════════════════════════
   PLANTILLAS
══════════════════════════════════════ */
function initPlantillas() {
    cargarPlantillas();

    const area  = document.getElementById('uploadArea');
    const input = document.getElementById('plantillaInput');

    area?.addEventListener('click', () => input?.click());
    area?.addEventListener('dragover', e => { e.preventDefault(); area.classList.add('drag-over'); });
    area?.addEventListener('dragleave', () => area.classList.remove('drag-over'));
    area?.addEventListener('drop', e => {
        e.preventDefault(); area.classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (file) { input.files = e.dataTransfer.files; mostrarArchivoSeleccionado(file.name); }
    });
    input?.addEventListener('change', e => {
        if (e.target.files[0]) mostrarArchivoSeleccionado(e.target.files[0].name);
    });

    document.getElementById('formPlantilla')?.addEventListener('submit', async e => {
        e.preventDefault();
        const btn = document.getElementById('btnSubir');
        btn.disabled = true;
        const fd = new FormData(e.target);
        fd.append('accion', 'subir');
        try {
            const res  = await fetch('../controllers/plantillacontroller.php?action=subir', { method:'POST', body:fd });
            const data = await res.json();
            toast(data.ok ? 'Plantilla subida correctamente' : data.msg, data.ok?'ok':'error');
            if (data.ok) { e.target.reset(); resetUploadArea(); cargarPlantillas(); }
        } catch(err) { toast('Error de conexión','error'); }
        finally { btn.disabled = false; }
    });
}

function mostrarArchivoSeleccionado(nombre) {
    const area = document.getElementById('uploadArea');
    if (!area) return;
    area.classList.add('has-file');
    area.querySelector('p').textContent = nombre;
}
function resetUploadArea() {
    const area = document.getElementById('uploadArea');
    if (!area) return;
    area.classList.remove('has-file');
    area.querySelector('p').textContent = 'Haz clic o arrastra aquí';
}

async function cargarPlantillas() {
    const el = document.getElementById('listaPlantillas');
    if (!el) return;
    el.innerHTML = '<div class="loading-state"><div class="loading-spinner"></div>Cargando...</div>';
    try {
        const res  = await fetch('../controllers/plantillacontroller.php?action=listar');
        const data = await res.json();
        if (!data.ok || !data.plantillas?.length) {
            el.innerHTML = '<div class="empty-state"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>No hay plantillas subidas aún.</div>';
            return;
        }
        el.innerHTML = data.plantillas.map(p => {
            const fecha  = new Date(p.fecha_creacion).toLocaleDateString('es-SV',{day:'2-digit',month:'short',year:'numeric'});
            const activa = p.plantilla_activa == 1;
            return `
            <div class="plantilla-item">
                <div class="plantilla-ico"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg></div>
                <div class="plantilla-info">
                    <div class="plantilla-nombre" title="${esc(p.nombre_plantilla)}">${esc(p.nombre_plantilla)}</div>
                    <div class="plantilla-fecha">${fecha}</div>
                </div>
                <span class="plantilla-badge ${activa?'badge-activa':'badge-inactiva'}">${activa?'Activa':'Inactiva'}</span>
                <div style="display:flex;gap:6px;margin-left:8px;">
                    <button class="btn-sm btn-outline" onclick="togglePlantilla('${p.id}')" title="${activa?'Desactivar':'Activar'}">${activa?'⏸':'▶'}</button>
                    <button class="btn-sm btn-danger-sm" onclick="eliminarPlantilla('${p.id}','${esc(p.nombre_plantilla)}')">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4z" clip-rule="evenodd"/></svg>
                    </button>
                </div>
            </div>`;
        }).join('');
    } catch(e) { el.innerHTML = '<div class="empty-state">Error al cargar plantillas</div>'; }
}

async function togglePlantilla(id) {
    const fd = new FormData(); fd.append('id',id); fd.append('accion','toggle');
    const res  = await fetch('../controllers/plantillacontroller.php?action=toggle',{method:'POST',body:fd});
    const data = await res.json();
    if (data.ok) cargarPlantillas();
}

async function eliminarPlantilla(id, nombre) {
    // Modal de confirmación propio (evita bloqueo de confirm() en localhost)
    const confirmado = await modalConfirmar(`¿Eliminar la plantilla "<strong>${nombre}</strong>"?<br><small style="color:#6B7280;">Esta acción no se puede deshacer.</small>`);
    if (!confirmado) return;
    try {
        const res  = await fetch(`../controllers/plantillacontroller.php?action=eliminar&id=${id}`);
        const data = await res.json();
        toast(data.ok ? 'Plantilla eliminada' : (data.msg || 'Error'), data.ok ? 'ok' : 'error');
        if (data.ok) cargarPlantillas();
    } catch(e) { toast('Error de conexión', 'error'); }
}


/* ══════════════════════════════════════
   MODAL CONFIRMACIÓN (reemplaza confirm nativo)
══════════════════════════════════════ */
function modalConfirmar(mensaje) {
    return new Promise(resolve => {
        const overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;inset:0;background:rgba(15,15,46,.55);backdrop-filter:blur(4px);z-index:3000;display:flex;align-items:center;justify-content:center;padding:20px;';
        overlay.innerHTML = `
            <div style="background:#FFFCFB;border-radius:16px;width:100%;max-width:380px;box-shadow:0 24px 64px rgba(0,0,0,.25);overflow:hidden;animation:slideUp .2s ease;">
                <div style="padding:20px 24px 16px;">
                    <div style="font-size:.95rem;color:#1C1C2E;line-height:1.6;">${mensaje}</div>
                </div>
                <div style="padding:12px 24px 20px;display:flex;gap:10px;justify-content:flex-end;">
                    <button id="btnCancelarConfirm" style="padding:9px 20px;border-radius:999px;border:1.5px solid #E5E7EB;background:white;color:#6B7280;cursor:pointer;font-weight:600;font-size:.82rem;font-family:Inter,sans-serif;">Cancelar</button>
                    <button id="btnAceptarConfirm" style="padding:9px 20px;border-radius:999px;border:none;background:#ef4444;color:white;cursor:pointer;font-weight:700;font-size:.82rem;font-family:Inter,sans-serif;">Eliminar</button>
                </div>
            </div>`;
        document.body.appendChild(overlay);

        overlay.querySelector('#btnAceptarConfirm').addEventListener('click', () => {
            overlay.remove(); resolve(true);
        });
        overlay.querySelector('#btnCancelarConfirm').addEventListener('click', () => {
            overlay.remove(); resolve(false);
        });
        overlay.addEventListener('click', e => { if (e.target === overlay) { overlay.remove(); resolve(false); } });
    });
}

/* ══════════════════════════════════════
   HELPERS
══════════════════════════════════════ */
function esc(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function toast(msg, tipo='ok') {
    const wrap = document.getElementById('toastWrap');
    if (!wrap) return;
    const svg = tipo==='ok'
        ? `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>`
        : `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>`;
    const t = document.createElement('div');
    t.className = `toast toast-${tipo}`;
    t.innerHTML = `${svg}<span>${msg}</span>`;
    wrap.appendChild(t);
    setTimeout(() => { t.classList.add('hide'); setTimeout(()=>t.remove(),280); }, 3500);
}