/**
 * Contratos.js — PP Bienes Raíces
 * Maneja tanto la vista de Contratos como la de Plantillas
 */
'use strict';

const MODO = window.MODO || 'contratos';

document.addEventListener('DOMContentLoaded', () => {
    if (MODO === 'plantillas') {
        initPlantillas();
    } else {
        initContratos();
    }
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
        btn.innerHTML = '<div class="loading-spinner" style="width:16px;height:16px;border-width:2px;"></div> Creando...';

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
        } catch(e) {
            toast('Error de conexión', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 00-1.5 0v2.5h-2.5a.75.75 0 000 1.5h2.5v2.5a.75.75 0 001.5 0v-2.5h2.5a.75.75 0 000-1.5h-2.5v-2.5z" clip-rule="evenodd"/></svg> Crear contrato';
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
            const color  = c.estado_color || '#6B7280';
            const monto  = new Intl.NumberFormat('en-US',{style:'currency',currency:c.moneda||'USD'}).format(c.monto_acordado);
            const fecha  = new Date(c.fecha_generacion).toLocaleDateString('es-SV',{day:'2-digit',month:'short',year:'numeric'});
            const num    = c.id.substring(0,8).toUpperCase();
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

    document.getElementById('kpiTotal')?.setAttribute('data-val', total);
    document.getElementById('kpiBorrador')?.setAttribute('data-val', borrador);
    document.getElementById('kpiFirmado')?.setAttribute('data-val', firmado);

    if (document.getElementById('kpiTotal'))    document.getElementById('kpiTotal').textContent    = total;
    if (document.getElementById('kpiBorrador')) document.getElementById('kpiBorrador').textContent = borrador;
    if (document.getElementById('kpiFirmado'))  document.getElementById('kpiFirmado').textContent  = firmado;
    if (document.getElementById('kpiMonto'))    document.getElementById('kpiMonto').textContent    = '$'+monto.toLocaleString('en-US',{minimumFractionDigits:0,maximumFractionDigits:0});
}

function verContrato(id) {
    // Por ahora muestra alerta básica — se puede expandir a modal
    alert('Contrato ID: ' + id + '\n\nPróximamente: vista detalle con historial y opciones de firma.');
}

async function eliminarContrato(id, estado) {
    const accion = estado === 'Borrador' ? 'eliminar permanentemente' : 'anular';
    if (!confirm(`¿Deseas ${accion} este contrato?`)) return;
    try {
        const res  = await fetch(`../controllers/contratocontroller.php?action=eliminar&id=${id}`);
        const data = await res.json();
        toast(data.ok ? 'Contrato ' + (estado==='Borrador'?'eliminado':'anulado') : data.msg, data.ok?'ok':'error');
        if (data.ok) cargarContratos();
    } catch(e) { toast('Error de conexión','error'); }
}

/* ══════════════════════════════════════
   PLANTILLAS
══════════════════════════════════════ */
function initPlantillas() {
    cargarPlantillas();

    // Upload area
    const area  = document.getElementById('uploadArea');
    const input = document.getElementById('plantillaInput');

    area?.addEventListener('click', () => input?.click());
    area?.addEventListener('dragover', e => { e.preventDefault(); area.classList.add('drag-over'); });
    area?.addEventListener('dragleave', () => area.classList.remove('drag-over'));
    area?.addEventListener('drop', e => {
        e.preventDefault();
        area.classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (file) { input.files = e.dataTransfer.files; mostrarArchivoSeleccionado(file.name); }
    });
    input?.addEventListener('change', e => {
        if (e.target.files[0]) mostrarArchivoSeleccionado(e.target.files[0].name);
    });

    // Form
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
    area.querySelector('p').textContent = 'Haz clic o arrastra el archivo aquí';
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
            const fecha = new Date(p.fecha_creacion).toLocaleDateString('es-SV',{day:'2-digit',month:'short',year:'numeric'});
            const activa = p.plantilla_activa == 1;
            return `
            <div class="plantilla-item">
                <div class="plantilla-ico">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
                </div>
                <div class="plantilla-info">
                    <div class="plantilla-nombre" title="${esc(p.nombre_plantilla)}">${esc(p.nombre_plantilla)}</div>
                    <div class="plantilla-fecha">${fecha}</div>
                </div>
                <span class="plantilla-badge ${activa?'badge-activa':'badge-inactiva'}">${activa?'Activa':'Inactiva'}</span>
                <div style="display:flex;gap:6px;margin-left:8px;">
                    <button class="btn-sm btn-outline" onclick="togglePlantilla('${p.id}')" title="${activa?'Desactivar':'Activar'}">
                        ${activa ? '⏸' : '▶'}
                    </button>
                    <button class="btn-sm btn-danger-sm" onclick="eliminarPlantilla('${p.id}','${esc(p.nombre_plantilla)}')">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4z" clip-rule="evenodd"/></svg>
                    </button>
                </div>
            </div>`;
        }).join('');
    } catch(e) {
        el.innerHTML = '<div class="empty-state">Error al cargar plantillas</div>';
    }
}

async function togglePlantilla(id) {
    const fd = new FormData(); fd.append('id',id); fd.append('accion','toggle');
    const res  = await fetch('../controllers/plantillacontroller.php?action=toggle', {method:'POST',body:fd});
    const data = await res.json();
    if (data.ok) cargarPlantillas();
}

async function eliminarPlantilla(id, nombre) {
    if (!confirm(`¿Eliminar la plantilla "${nombre}"?`)) return;
    const res  = await fetch(`../controllers/plantillacontroller.php?action=eliminar&id=${id}`);
    const data = await res.json();
    toast(data.ok ? 'Plantilla eliminada' : 'Error', data.ok?'ok':'error');
    if (data.ok) cargarPlantillas();
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