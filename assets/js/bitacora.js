/**
 * bitacora.js — PP Bienes Raíces
 */
'use strict';

let paginaActual = 1;

document.addEventListener('DOMContentLoaded', () => {
    cargarKpis();
    cargarEventos(1);

    document.getElementById('btnBuscar')?.addEventListener('click',  () => cargarEventos(1));
    document.getElementById('btnLimpiar')?.addEventListener('click', limpiarFiltros);
    document.getElementById('btnRefresh')?.addEventListener('click', () => { cargarKpis(); cargarEventos(paginaActual); });
    document.getElementById('filtroBuscar')?.addEventListener('keydown', e => { if (e.key === 'Enter') cargarEventos(1); });
});

async function cargarKpis() {
    try {
        const res  = await fetch('../controllers/bitacoracontroller.php?action=kpis');
        const data = await res.json();
        if (!data.ok) return;
        setText('kpiContratos',  data.contratos);
        setText('kpiFirmas',     data.firmas);
        setText('kpiContactos',  data.contactos);
        setText('kpiHoy',        data.hoy);
        setText('kpiSesiones',   data.sesiones);
        setText('kpiPropiedades',data.propiedades);
        setText('kpiUsuarios',   data.usuarios);
        setText('kpiPlantillas', data.plantillas);
    } catch (_) {}
}

async function cargarEventos(pagina) {
    paginaActual = pagina;
    const tbody = document.getElementById('bitTablaBody');
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="6"><div class="loading-state"><div class="loading-spinner"></div>Cargando...</div></td></tr>`;

    const params = new URLSearchParams({
        action: 'eventos',
        pagina: pagina,
        desde:  document.getElementById('filtroDe')?.value     ?? '',
        hasta:  document.getElementById('filtroA')?.value      ?? '',
        tipo:   document.getElementById('filtroTipo')?.value   ?? '',
        buscar: document.getElementById('filtroBuscar')?.value ?? '',
    });

    try {
        const res  = await fetch('../controllers/bitacoracontroller.php?' + params.toString());
        const data = await res.json();

        if (!data.ok) {
            tbody.innerHTML = `<tr><td colspan="6"><div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error al cargar los datos</p></div></td></tr>`;
            return;
        }

        document.getElementById('tablaContador').textContent = `${data.total} registro(s)`;

        if (!data.eventos.length) {
            tbody.innerHTML = `<tr><td colspan="6"><div class="empty-state"><i class="fas fa-book-open"></i><p>No hay registros con los filtros aplicados.</p></div></td></tr>`;
            renderPaginacion(0, 1, 1);
            return;
        }

        tbody.innerHTML = data.eventos.map(ev => {
            const { icono, clase, label } = tipoMeta(ev.tipo_evento);
            const fecha = ev.fecha ? formatFecha(ev.fecha) : '—';
            const hora  = ev.fecha ? formatHora(ev.fecha)  : '';
            return `
            <tr>
                <td><span class="tipo-badge ${clase}"><i class="${icono}"></i> ${label}</span></td>
                <td>
                    <div class="td-descripcion">${esc(ev.descripcion)}</div>
                    ${ev.propiedad ? `<div class="td-propiedad"><i class="fas fa-home" style="font-size:.65rem;color:var(--gold-dark);margin-right:3px;"></i>${esc(ev.propiedad)}</div>` : ''}
                </td>
                <td><div class="td-actor">${esc(ev.actor || '—')}</div></td>
                <td>${ev.ref_id ? `<span class="td-ref">#${esc(ev.ref_id).toUpperCase()}</span>` : '<span style="color:var(--muted)">—</span>'}</td>
                <td>
                    <div class="td-fecha-val">${fecha}</div>
                    <div class="td-hora-val">${hora}</div>
                </td>
                <td><div class="td-ip">${esc(ev.ip || '—')}</div></td>
            </tr>`;
        }).join('');

        renderPaginacion(data.total, data.paginas, pagina);

    } catch (e) {
        tbody.innerHTML = `<tr><td colspan="6"><div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error de conexión</p></div></td></tr>`;
    }
}

function renderPaginacion(total, paginas, actual) {
    const wrap = document.getElementById('bitPaginacion');
    if (!wrap) return;
    if (paginas <= 1) { wrap.innerHTML = ''; return; }

    let html = `<button class="bit-pag-btn" onclick="cargarEventos(${actual - 1})" ${actual <= 1 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>`;

    const delta = 2;
    for (let i = 1; i <= paginas; i++) {
        if (i === 1 || i === paginas || (i >= actual - delta && i <= actual + delta)) {
            html += `<button class="bit-pag-btn ${i === actual ? 'active' : ''}" onclick="cargarEventos(${i})">${i}</button>`;
        } else if (i === actual - delta - 1 || i === actual + delta + 1) {
            html += `<span class="bit-pag-info">…</span>`;
        }
    }

    html += `<button class="bit-pag-btn" onclick="cargarEventos(${actual + 1})" ${actual >= paginas ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button>`;
    html += `<span class="bit-pag-info">${total} registros</span>`;
    wrap.innerHTML = html;
}

function limpiarFiltros() {
    ['filtroDe', 'filtroA', 'filtroBuscar'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
    const sel = document.getElementById('filtroTipo');
    if (sel) sel.value = '';
    cargarEventos(1);
}

/* ── Helpers ── */
function tipoMeta(tipo) {
    const mapa = {
        contrato:  { icono: 'fas fa-file-contract', clase: 'tipo-contrato',  label: 'Contrato'   },
        firma:     { icono: 'fas fa-pen-nib',        clase: 'tipo-firma',     label: 'Firma'      },
        contacto:  { icono: 'fas fa-phone-alt',      clase: 'tipo-contacto',  label: 'Contacto'   },
        sesion:    { icono: 'fas fa-sign-in-alt',    clase: 'tipo-sesion',    label: 'Sesión'     },
        propiedad: { icono: 'fas fa-home',           clase: 'tipo-propiedad', label: 'Propiedad'  },
        usuario:   { icono: 'fas fa-user-plus',      clase: 'tipo-usuario',   label: 'Usuario'    },
        plantilla: { icono: 'fas fa-copy',           clase: 'tipo-plantilla', label: 'Plantilla'  },
    };
    return mapa[tipo] || { icono: 'fas fa-circle', clase: 'tipo-contrato', label: tipo };
}

function formatFecha(dt) {
    if (!dt) return '—';
    const d = new Date(dt.replace(' ', 'T'));
    return d.toLocaleDateString('es-SV', { day: '2-digit', month: 'short', year: 'numeric' });
}
function formatHora(dt) {
    if (!dt) return '';
    const d = new Date(dt.replace(' ', 'T'));
    return d.toLocaleTimeString('es-SV', { hour: '2-digit', minute: '2-digit' });
}
function setText(id, val) { const el = document.getElementById(id); if (el) el.textContent = val ?? '—'; }
function esc(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
