/**
 * plantillas.js — PP Bienes Raíces
 * Lógica exclusiva de plantillas de contrato
 * Cargar solo en views/plantillas.php
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
    initPlantillas();
});

function initPlantillas() {
    cargarPlantillas();

    const area  = document.getElementById('uploadArea');
    const input = document.getElementById('plantillaInput');

    area?.addEventListener('click', () => input?.click());
    area?.addEventListener('dragover', e => {
        e.preventDefault(); area.classList.add('drag-over');
    });
    area?.addEventListener('dragleave', () => area.classList.remove('drag-over'));
    area?.addEventListener('drop', e => {
        e.preventDefault(); area.classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (file) { input.files = e.dataTransfer.files; mostrarArchivoSeleccionado(file.name); }
    });
    input?.addEventListener('change', e => {
        if (e.target.files[0]) mostrarArchivoSeleccionado(e.target.files[0].name);
    });

    document.getElementById('btnRefresh')?.addEventListener('click', cargarPlantillas);

    document.getElementById('formPlantilla')?.addEventListener('submit', async e => {
        e.preventDefault();
        const btn = document.getElementById('btnSubir');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subiendo...';

        const fd = new FormData(e.target);
        fd.append('accion', 'subir');

        try {
            const res  = await fetch('../controllers/plantillacontroller.php?action=subir', { method: 'POST', body: fd });
            const data = await res.json();
            toast(data.ok ? 'Plantilla subida correctamente ✓' : (data.msg || 'Error'), data.ok ? 'ok' : 'error');
            if (data.ok) { e.target.reset(); resetUploadArea(); cargarPlantillas(); }
        } catch(err) {
            toast('Error de conexión', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-upload"></i> Subir plantilla';
        }
    });
}

function mostrarArchivoSeleccionado(nombre) {
    const area = document.getElementById('uploadArea');
    if (!area) return;
    area.classList.add('has-file');
    const p = area.querySelector('p');
    if (p) p.textContent = nombre;
    const icon = area.querySelector('i');
    if (icon) { icon.className = 'fas fa-file-word'; }
}

function resetUploadArea() {
    const area = document.getElementById('uploadArea');
    if (!area) return;
    area.classList.remove('has-file');
    const p = area.querySelector('p');
    if (p) p.textContent = 'Haz clic o arrastra aquí';
    const icon = area.querySelector('i');
    if (icon) icon.className = 'fas fa-cloud-upload-alt';
}

async function cargarPlantillas() {
    const el = document.getElementById('listaPlantillas');
    if (!el) return;
    el.innerHTML = '<div class="loading-state"><div class="loading-spinner"></div>Cargando...</div>';

    try {
        const res  = await fetch('../controllers/plantillacontroller.php?action=listar');
        const data = await res.json();

        if (!data.ok || !data.plantillas?.length) {
            el.innerHTML = `<div class="empty-state">
                <i class="fas fa-copy"></i>
                <p>No hay plantillas subidas aún.</p>
            </div>`;
            return;
        }

        el.innerHTML = data.plantillas.map(p => {
            const fecha  = new Date(p.fecha_creacion).toLocaleDateString('es-SV', { day:'2-digit', month:'short', year:'numeric' });
            const activa = p.plantilla_activa == 1;
            return `
            <div class="plantilla-item">
                <div class="plantilla-ico"><i class="fas fa-file-word"></i></div>
                <div class="plantilla-info">
                    <div class="plantilla-nombre" title="${esc(p.nombre_plantilla)}">${esc(p.nombre_plantilla)}</div>
                    <div class="plantilla-fecha"><i class="fas fa-calendar-alt"></i> ${fecha}</div>
                </div>
                <span class="plantilla-badge ${activa ? 'badge-activa' : 'badge-inactiva'}">
                    ${activa ? 'Activa' : 'Inactiva'}
                </span>
                <div style="display:flex;gap:6px;margin-left:8px;">
                    <button class="btn-sm btn-toggle" onclick="togglePlantilla('${p.id}')" title="${activa ? 'Desactivar' : 'Activar'}">
                        <i class="fas fa-${activa ? 'pause' : 'play'}"></i>
                    </button>
                    <button class="btn-sm btn-danger-plt" onclick="eliminarPlantilla('${p.id}','${esc(p.nombre_plantilla)}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>`;
        }).join('');

    } catch(e) {
        el.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error al cargar plantillas</p></div>';
    }
}

async function togglePlantilla(id) {
    const fd = new FormData();
    fd.append('id', id);
    fd.append('accion', 'toggle');
    try {
        const res  = await fetch('../controllers/plantillacontroller.php?action=toggle', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.ok) cargarPlantillas();
        else toast(data.msg || 'Error', 'error');
    } catch(e) { toast('Error de conexión', 'error'); }
}

async function eliminarPlantilla(id, nombre) {
    const ok = await modalConfirmar(
        `¿Eliminar la plantilla <strong>"${nombre}"</strong>?<br><small style="color:#5f6292;font-weight:400;">Esta acción no se puede deshacer.</small>`,
        'Eliminar', '#ef4444'
    );
    if (!ok) return;
    try {
        const res  = await fetch(`../controllers/plantillacontroller.php?action=eliminar&id=${id}`);
        const data = await res.json();
        toast(data.ok ? 'Plantilla eliminada' : (data.msg || 'Error'), data.ok ? 'ok' : 'error');
        if (data.ok) cargarPlantillas();
    } catch(e) { toast('Error de conexión', 'error'); }
}

/* ── Modal confirmación flat ── */
function modalConfirmar(mensaje, btnTexto = 'Confirmar', btnColor = '#1A1953') {
    return new Promise(resolve => {
        const overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;inset:0;background:rgba(15,15,46,.65);backdrop-filter:blur(4px);z-index:3000;display:flex;align-items:center;justify-content:center;padding:20px;';
        overlay.innerHTML = `
            <div style="background:#FFFCFB;width:100%;max-width:400px;box-shadow:6px 6px 0 rgba(0,0,0,.25);">
                <div style="padding:14px 18px;background:#1A1953;border-bottom:2px solid #FFD45A;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-exclamation-triangle" style="color:#FFD45A;font-size:.9rem;"></i>
                    <span style="font-size:.72rem;font-weight:800;color:#FFD45A;text-transform:uppercase;letter-spacing:.1em;">Confirmar acción</span>
                </div>
                <div style="padding:20px 18px;font-size:.875rem;color:#1C1C2E;line-height:1.6;border-left:4px solid #1A1953;">${mensaje}</div>
                <div style="padding:12px 18px 16px;display:flex;gap:8px;justify-content:flex-end;border-top:1px solid #CECCDF;">
                    <button id="btnNo"  style="padding:8px 18px;background:transparent;border:1.5px solid #CECCDF;font-family:Inter,sans-serif;font-size:.75rem;font-weight:800;cursor:pointer;color:#5f6292;text-transform:uppercase;letter-spacing:.06em;">Cancelar</button>
                    <button id="btnSi"  style="padding:8px 18px;background:${btnColor};color:#FFD45A;border:none;font-family:Inter,sans-serif;font-size:.75rem;font-weight:800;cursor:pointer;text-transform:uppercase;letter-spacing:.06em;box-shadow:3px 3px 0 rgba(0,0,0,.2);">${btnTexto}</button>
                </div>
            </div>`;
        document.body.appendChild(overlay);
        overlay.querySelector('#btnSi').addEventListener('click', () => { overlay.remove(); resolve(true); });
        overlay.querySelector('#btnNo').addEventListener('click', () => { overlay.remove(); resolve(false); });
        overlay.addEventListener('click', e => { if (e.target === overlay) { overlay.remove(); resolve(false); } });
    });
}

/* ── Helpers ── */
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
    setTimeout(() => { t.classList.add('hide'); setTimeout(() => t.remove(), 280); }, 3500);
}