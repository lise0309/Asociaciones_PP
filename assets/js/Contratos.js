/**
 * contratos.js — PP Bienes Raíces
 * Lógica de contratos — cargar en contratos.php y contratosadmin.php
 */
'use strict';


document.addEventListener('DOMContentLoaded', () => {
    initContratos();
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
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando...';

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
            btn.innerHTML = '<i class="fas fa-plus"></i> Crear contrato';
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
                <i class="fas fa-file-contract"></i>
                <p>No hay contratos aún. Crea uno nuevo.</p>
            </div>`;
            actualizarKpis([]);
            return;
        }

        actualizarKpis(data.contratos);

        el.innerHTML = data.contratos.map(c => {
            const color     = c.estado_color || '#6B7280';
            const monto     = new Intl.NumberFormat('en-US',{style:'currency',currency:c.moneda||'USD'}).format(c.monto_acordado);
            const fecha     = new Date(c.fecha_generacion).toLocaleDateString('es-SV',{day:'2-digit',month:'short',year:'numeric'});
            const num       = c.id.substring(0,8).toUpperCase();
            const estado    = c.estado_nombre || '';
            const esAnulado = estado === 'Anulado';

            // ── Botones del flujo según ROL y ESTADO ──
            let botonesFlujjo = '';
            if (ROL === 'vendedor' && estado === 'Borrador') {
                botonesFlujjo = `<button class="btn-sm btn-enviar" onclick="enviarAlAdmin('${c.id}')">
                    <i class="fas fa-paper-plane"></i> Enviar al admin
                </button>`;
            } else if (ROL === 'admin') {
                if (estado === 'Enviado') {
                    botonesFlujjo = `<button class="btn-sm btn-revisar" onclick="marcarEnRevision('${c.id}')">
                        <i class="fas fa-eye"></i> Revisar
                    </button>`;
                }
                if (estado === 'Enviado' || estado === 'En revisión') {
                    botonesFlujjo += `
                    <button class="btn-sm btn-aprobar" onclick="aprobarContrato('${c.id}')">
                        <i class="fas fa-check"></i> Aprobar
                    </button>
                    <button class="btn-sm btn-rechazar" onclick="abrirRechazo('${c.id}')">
                        <i class="fas fa-times"></i> Rechazar
                    </button>`;
                }
            }

            return `
            <article class="contrato-item">
                <div class="ci-head">
                    <span class="ci-num">#${num}</span>
                    <span class="ci-badge" >${esc(estado)}</span>
                    <span class="ci-fecha">${fecha}</span>
                </div>
                <div class="ci-body">
                    <div class="ci-comprador"><i class="fas fa-user"></i> ${esc(c.nombre_comprador)}</div>
                    <div class="ci-prop"><i class="fas fa-home"></i> ${esc(c.titulo_anuncio)}</div>
                    <div class="ci-tipo"><i class="fas fa-file-alt"></i> ${esc(c.tipo_nombre)} · ${esc(c.nombre_plantilla)}</div>
                    ${c.nombre_vendedor ? `<div class="ci-vendedor"><i class="fas fa-user-tie"></i> ${esc(c.nombre_vendedor)}</div>` : ''}
                    <div class="ci-monto">${monto}</div>
                </div>
                <div class="ci-actions">
                    <button class="btn-sm btn-ver" onclick="verContrato('${c.id}')">
                        <i class="fas fa-eye"></i> Ver
                    </button>
                    <button class="btn-sm btn-descargar" onclick="generarDocumento('${c.id}')">
                        <i class="fas fa-download"></i> Descargar
                    </button>
                    ${botonesFlujjo}
                    ${!esAnulado && (estado === 'Borrador' || estado === 'Rechazado') ? `
                    <button class="btn-sm btn-eliminar" onclick="eliminarContrato('${c.id}','${esc(estado)}')">
                        <i class="fas fa-trash"></i> ${estado === 'Borrador' ? 'Eliminar' : 'Anular'}
                    </button>` : ''}
                </div>
            </article>`;
        }).join('');

    } catch(e) {
        el.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error al cargar contratos</p></div>';
    }
}

function actualizarKpis(contratos) {
    const total    = contratos.length;
    const borrador = contratos.filter(c=>c.estado_nombre==='Borrador').length;
    const firmado  = contratos.filter(c=>c.estado_nombre==='Firmado' || c.estado_nombre==='Aprobado').length;
    const monto    = contratos.reduce((s,c)=>s+parseFloat(c.monto_acordado||0),0);
    if (document.getElementById('kpiTotal'))    document.getElementById('kpiTotal').textContent    = total;
    if (document.getElementById('kpiBorrador')) document.getElementById('kpiBorrador').textContent = borrador;
    if (document.getElementById('kpiFirmado'))  document.getElementById('kpiFirmado').textContent  = firmado;
    if (document.getElementById('kpiMonto'))    document.getElementById('kpiMonto').textContent    = '$'+monto.toLocaleString('en-US',{minimumFractionDigits:0,maximumFractionDigits:0});
}

/* ── FLUJO DE APROBACIÓN ── */
async function enviarAlAdmin(id) {
    const ok = await modalConfirmar('¿Enviar este contrato al administrador para revisión?', 'Enviar', '#1A1953');
    if (!ok) return;
    const res = await cambiarEstadoPorNombre(id, 'Enviado');
    if (res.ok) { toast('Contrato enviado al administrador ✓', 'ok'); cargarContratos(); }
    else toast(res.msg || 'Error', 'error');
}

async function marcarEnRevision(id) {
    const res = await cambiarEstadoPorNombre(id, 'En revisión');
    if (res.ok) { toast('Marcado en revisión', 'ok'); cargarContratos(); }
    else toast(res.msg || 'Error', 'error');
}

async function aprobarContrato(id) {
    const ok = await modalConfirmar('¿Aprobar este contrato?', 'Aprobar', '#16a34a');
    if (!ok) return;
    const res = await cambiarEstadoPorNombre(id, 'Aprobado');
    if (res.ok) { toast('Contrato aprobado ✓', 'ok'); cargarContratos(); }
    else toast(res.msg || 'Error', 'error');
}

// Abre modal de rechazo con motivo
function abrirRechazo(id) {
    window._contratoRechazarId = id;
    document.getElementById('motivoRechazo').value = '';
    document.getElementById('modalRechazo').style.display = 'flex';
}
window.cerrarModalRechazo = function() {
    document.getElementById('modalRechazo').style.display = 'none';
    window._contratoRechazarId = null;
};
window.confirmarRechazo = async function() {
    const id     = window._contratoRechazarId;
    const motivo = document.getElementById('motivoRechazo').value.trim();
    if (!id) return;
    cerrarModalRechazo();
    // Cambiar a Rechazado y guardar motivo en historial via estado
    const res = await cambiarEstadoPorNombre(id, 'Rechazado', motivo);
    if (res.ok) { toast('Contrato rechazado', 'ok'); cargarContratos(); }
    else toast(res.msg || 'Error', 'error');
};

// Obtiene el ID del estado por nombre y llama cambiarEstado
async function cambiarEstadoPorNombre(contratoId, nombreEstado, motivo = '') {
    try {
        // Obtener estados
        const resEst  = await fetch('../controllers/contratocontroller.php?action=estados');
        const dataEst = await resEst.json();
        const estado  = (dataEst.estados || []).find(e => e.nombre_opcion === nombreEstado);
        if (!estado) return { ok: false, msg: `Estado "${nombreEstado}" no encontrado en el sistema` };

        const fd = new FormData();
        fd.append('accion', 'estado');
        fd.append('id', contratoId);
        fd.append('estado_id', estado.id);
        if (motivo) fd.append('motivo', motivo);

        const res  = await fetch('../controllers/contratocontroller.php?action=estado', { method:'POST', body:fd });
        return await res.json();
    } catch(e) {
        return { ok: false, msg: 'Error de conexión' };
    }
}

/* ── VER CONTRATO ── */
async function verContrato(id) {
    cerrarModal();
    const modal = crearModal('Detalle del contrato', '<div style="text-align:center;padding:32px;color:#6B7280;"><i class="fas fa-spinner fa-spin" style="font-size:1.5rem;"></i></div>');

    try {
        const res  = await fetch(`../controllers/contratocontroller.php?action=ver&id=${id}`);
        const data = await res.json();
        if (!data.ok) { modal.querySelector('.modal-body').innerHTML = `<p style="color:red">${data.msg}</p>`; return; }

        const c     = data.contrato;
        const monto = new Intl.NumberFormat('en-US',{style:'currency',currency:c.moneda||'USD'}).format(c.monto_acordado);
        const fecha = new Date(c.fecha_generacion).toLocaleDateString('es-SV',{day:'2-digit',month:'long',year:'numeric'});
        modal.querySelector('.modal-body').innerHTML = `
            <div class="modal-badges">
                <span class="modal-num">#${c.id.substring(0,8).toUpperCase()}</span>
                <span class="modal-estado" >${esc(c.estado_nombre)}</span>
            </div>
            <table class="modal-table">
                <tr><td><i class="fas fa-file-alt"></i> Tipo</td><td>${esc(c.tipo_nombre)}</td></tr>
                <tr><td><i class="fas fa-copy"></i> Plantilla</td><td>${esc(c.nombre_plantilla)}</td></tr>
                <tr><td><i class="fas fa-home"></i> Propiedad</td><td><strong>${esc(c.titulo_anuncio)}</strong></td></tr>
                <tr><td><i class="fas fa-map-marker-alt"></i> Ubicación</td><td>${esc(c.municipio)}, ${esc(c.departamento)}</td></tr>
                <tr><td><i class="fas fa-user"></i> Comprador</td><td><strong>${esc(c.nombre_comprador)}</strong></td></tr>
                <tr><td><i class="fas fa-envelope"></i> Correo</td><td>${esc(c.correo_comprador)}</td></tr>
                <tr><td><i class="fas fa-id-card"></i> DUI</td><td>${esc(c.dui_comprador)}</td></tr>
                <tr><td><i class="fas fa-dollar-sign"></i> Monto</td><td class="modal-monto">${monto}</td></tr>
                <tr><td><i class="fas fa-user-tie"></i> Vendedor</td><td>${esc(c.vendedor_nombre)}</td></tr>
                <tr><td><i class="fas fa-calendar"></i> Fecha</td><td>${fecha}</td></tr>
                ${c.archivo_generado ? `<tr><td><i class="fas fa-file-word"></i> Documento</td><td><span style="color:#16a34a;font-weight:700;"><i class="fas fa-check-circle"></i> Generado</span></td></tr>` : ''}
            </table>


            <div class="modal-footer-btns">
                <button class="modal-btn-cerrar" onclick="cerrarModal()"><i class="fas fa-times"></i> Cerrar</button>
                <button class="modal-btn-descargar" onclick="generarDocumento('${c.id}')">
                    <i class="fas fa-download"></i> Generar y descargar
                </button>
            </div>`;

    } catch(e) {
        modal.querySelector('.modal-body').innerHTML = '<p style="color:red">Error al cargar</p>';
    }
}



/* ── GENERAR DOCUMENTO ── */
function generarDocumento(contratoId) {
    toast('Generando documento...', 'ok');
    const a = document.createElement('a');
    a.href = `../controllers/generarcontratocontroller.php?id=${contratoId}`;
    a.download = '';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    setTimeout(() => cargarContratos(), 2000);
}

/* ── ELIMINAR / ANULAR ── */
async function eliminarContrato(id, estado) {
    const accion = estado === 'Borrador' ? 'eliminar permanentemente' : 'anular';
    const confirmado = await modalConfirmar(`¿Deseas <strong>${accion}</strong> este contrato?`, estado === 'Borrador' ? 'Eliminar' : 'Anular', '#ef4444');
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
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(15,15,46,.65);backdrop-filter:blur(4px);z-index:2000;display:flex;align-items:center;justify-content:center;padding:20px;';
    overlay.innerHTML = `
        <div class="modal-contrato-box">
            <div class="modal-contrato-head">
                <span><i class="fas fa-file-contract" style="margin-right:8px;"></i>${titulo}</span>
                <button onclick="cerrarModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">${bodyHtml}</div>
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
   HELPERS
══════════════════════════════════════ */
function esc(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function toast(msg, tipo='ok') {
    const wrap = document.getElementById('toastWrap');
    if (!wrap) return;
    const icon = tipo==='ok' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
    const t = document.createElement('div');
    t.className = `toast toast-${tipo}`;
    t.innerHTML = `<i class="${icon}"></i><span>${msg}</span>`;
    wrap.appendChild(t);
    setTimeout(() => { t.classList.add('hide'); setTimeout(()=>t.remove(),280); }, 3500);
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