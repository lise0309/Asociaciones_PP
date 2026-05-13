/**
 * propiedad_form.js — PP Bienes Raíces
 * Form tradicional con enctype multipart (mismo método que test_foto.php)
 */

'use strict';

let mapaLeaflet = null, marcador = null;
let tempLat = null, tempLng = null;
let currentLat = null, currentLng = null;
let dragSrcEl  = null;

/* ══════════════════════════════════════
   MAPA LEAFLET
══════════════════════════════════════ */
function initMapa() {
    const lat = parseFloat(document.getElementById('latitud').value)  || 13.6894;
    const lng = parseFloat(document.getElementById('longitud').value) || -89.1872;
    currentLat = lat; currentLng = lng;

    mapaLeaflet = L.map('mapaLeaflet', { scrollWheelZoom: true }).setView([lat, lng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap', maxZoom: 19,
    }).addTo(mapaLeaflet);

    const icono = L.divIcon({
        html: `<div style="width:40px;height:40px;border-radius:50% 50% 50% 0;background:#1A1953;border:3px solid #FFD45A;transform:rotate(-45deg);box-shadow:0 4px 14px rgba(26,25,83,.4);display:flex;align-items:center;justify-content:center;"><span style="transform:rotate(45deg);font-size:1rem;">📍</span></div>`,
        className:'', iconSize:[40,40], iconAnchor:[20,40], popupAnchor:[0,-44],
    });

    marcador = L.marker([lat,lng], { icon: icono, draggable: true }).addTo(mapaLeaflet);
    marcador.on('dragend', () => {
        const p = marcador.getLatLng();
        tempLat = p.lat; tempLng = p.lng;
        actualizarCoordsDisplay(tempLat, tempLng);
    });
    mapaLeaflet.on('click', e => {
        tempLat = e.latlng.lat; tempLng = e.latlng.lng;
        marcador.setLatLng([tempLat, tempLng]);
        actualizarCoordsDisplay(tempLat, tempLng);
    });
    tempLat = lat; tempLng = lng;
    actualizarCoordsDisplay(lat, lng);
}

function actualizarCoordsDisplay(lat, lng) {
    const el = document.getElementById('coordsDisplay');
    if (el) el.textContent = `${parseFloat(lat).toFixed(5)}° N, ${Math.abs(parseFloat(lng)).toFixed(5)}° O`;
}

function confirmarUbicacion() {
    if (tempLat !== null && tempLng !== null) {
        currentLat = tempLat; currentLng = tempLng;
        document.getElementById('latitud').value  = currentLat.toFixed(6);
        document.getElementById('longitud').value = currentLng.toFixed(6);
        const texto = document.getElementById('coordenadasTexto');
        if (texto) texto.textContent = `${currentLat.toFixed(5)}° N, ${Math.abs(currentLng).toFixed(5)}° O`;
        document.getElementById('mapaPreview')?.classList.add('tiene-coords');
    }
    cerrarModalMapa();
}

const modalOverlay = document.getElementById('modalMapaOverlay');
function abrirModalMapa() {
    if (!modalOverlay) return;
    modalOverlay.classList.add('open');
    if (!mapaLeaflet) setTimeout(initMapa, 100);
    else setTimeout(() => { mapaLeaflet.invalidateSize(); mapaLeaflet.setView([currentLat||13.6894, currentLng||-89.1872], 13); }, 150);
}
function cerrarModalMapa() { modalOverlay?.classList.remove('open'); }

document.getElementById('btnMapa')?.addEventListener('click', abrirModalMapa);
document.getElementById('mapaPreview')?.addEventListener('click', abrirModalMapa);
document.getElementById('modalMapaCerrar')?.addEventListener('click', cerrarModalMapa);
document.getElementById('btnCerrarMapa')?.addEventListener('click', cerrarModalMapa);
document.getElementById('btnConfirmarMapa')?.addEventListener('click', confirmarUbicacion);
modalOverlay?.addEventListener('click', e => { if (e.target === modalOverlay) cerrarModalMapa(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarModalMapa(); });

(function () {
    const lat = document.getElementById('latitud')?.value;
    const lng = document.getElementById('longitud')?.value;
    if (lat && lng) {
        document.getElementById('mapaPreview')?.classList.add('tiene-coords');
        currentLat = parseFloat(lat); currentLng = parseFloat(lng);
    }
})();

/* ══════════════════════════════════════
   FOTOS — el input file real se mantiene
   El JS solo hace preview visual
   El submit normal envía los archivos
══════════════════════════════════════ */
const fotosInput = document.getElementById('fotosInput');
const fotosGrid  = document.getElementById('fotosGrid');
const btnAgregar = document.getElementById('btnAgregarFoto');

// Sin DataTransfer — el input file nativo envía los archivos al submit
// El botón "Agregar" abre el selector de archivos
btnAgregar?.addEventListener('click', () => fotosInput?.click());

fotosInput?.addEventListener('change', e => {
    Array.from(e.target.files).forEach(file => {
        if (!file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = ev => agregarFotoPreview(ev.target.result, file.name);
        reader.readAsDataURL(file);
    });
    // No resetear el input — mantener los archivos para el submit
});

function agregarFotoPreview(src, filename) {
    const div = document.createElement('div');
    div.className = 'foto-item';
    div.setAttribute('data-new', 'true');
    div.setAttribute('draggable', 'true');
    div.innerHTML = `
        <img src="${src}" alt="${filename}">
        <button type="button" class="foto-delete" title="Eliminar">
            <svg viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>`;
    div.querySelector('.foto-delete').addEventListener('click', () => {
        div.remove();
        actualizarPortada();
    });
    fotosGrid.insertBefore(div, btnAgregar);
    agregarDragEvents(div);
    actualizarPortada();
}

window.eliminarFotoExistente = function (btn, fotoId) {
    btn.closest('.foto-item').remove();
    actualizarPortada();
    const input = document.createElement('input');
    input.type = 'hidden'; input.name = 'fotos_eliminar[]'; input.value = fotoId;
    document.getElementById('propiedadForm').appendChild(input);
};

function actualizarPortada() {
    const items = fotosGrid.querySelectorAll('.foto-item');
    items.forEach((item, i) => {
        item.classList.remove('is-portada');
        item.querySelector('.foto-badge')?.remove();
        if (i === 0) {
            item.classList.add('is-portada');
            const b = document.createElement('div');
            b.className = 'foto-badge'; b.textContent = 'Principal';
            item.prepend(b);
        }
    });
}

/* ══════════════════════════════════════
   DRAG & DROP
══════════════════════════════════════ */
function agregarDragEvents(el) {
    el.addEventListener('dragstart', onDragStart);
    el.addEventListener('dragover',  onDragOver);
    el.addEventListener('dragenter', onDragEnter);
    el.addEventListener('dragleave', onDragLeave);
    el.addEventListener('drop',      onDrop);
    el.addEventListener('dragend',   onDragEnd);
}
function onDragStart(e) { dragSrcEl = this; this.classList.add('dragging'); e.dataTransfer.effectAllowed = 'move'; }
function onDragOver(e)  { e.preventDefault(); return false; }
function onDragEnter()  { if (this !== dragSrcEl) this.classList.add('drag-over'); }
function onDragLeave()  { this.classList.remove('drag-over'); }
function onDrop(e) {
    e.preventDefault(); e.stopPropagation();
    if (dragSrcEl && dragSrcEl !== this) {
        if (getIndex(dragSrcEl) < getIndex(this)) fotosGrid.insertBefore(dragSrcEl, this.nextSibling);
        else fotosGrid.insertBefore(dragSrcEl, this);
        fotosGrid.appendChild(btnAgregar);
    }
    this.classList.remove('drag-over');
    actualizarPortada();
    return false;
}
function onDragEnd() {
    this.classList.remove('dragging');
    fotosGrid.querySelectorAll('.foto-item').forEach(el => el.classList.remove('drag-over'));
}
function getIndex(el) { return [...fotosGrid.children].indexOf(el); }

document.querySelectorAll('.foto-item[data-foto-id]').forEach(el => {
    el.setAttribute('draggable', 'true');
    agregarDragEvents(el);
});

/* ══════════════════════════════════════
   SUBMIT — validar y enviar normal
══════════════════════════════════════ */
const form = document.getElementById('propiedadForm');

form?.addEventListener('submit', function (e) {
    const destacarRadio = document.querySelector('input[name="destacar_radio"]:checked');
    if (destacarRadio) document.getElementById('destacarHidden').value = destacarRadio.value;

    const campos = [
        { id: 'titulo',        msg: 'El nombre de la propiedad es requerido' },
        { id: 'tipo_inmueble', msg: 'Selecciona el tipo de inmueble' },
        { id: 'precio',        msg: 'Ingresa un precio válido' },
        { id: 'descripcion',   msg: 'La descripción es requerida' },
        { id: 'departamento',  msg: 'Selecciona el departamento' },
        { id: 'municipio',     msg: 'Ingresa el municipio' },
        { id: 'direccion',     msg: 'Ingresa la dirección exacta' },
    ];
    for (const campo of campos) {
        const el = document.getElementById(campo.id);
        if (!el || !el.value.trim() || el.value.trim() === '0') {
            e.preventDefault();
            mostrarToast(campo.msg, 'error');
            el?.focus();
            el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
    }
    const precio = parseFloat(document.getElementById('precio')?.value);
    if (isNaN(precio) || precio <= 0) {
        e.preventDefault();
        mostrarToast('Ingresa un precio válido mayor a 0', 'error');
        return;
    }

    // Loading
    const btn = document.getElementById('btnGuardar');
    if (btn) { btn.classList.add('loading'); btn.disabled = true; btn.textContent = 'Guardando...'; }
    // El form se envía normalmente con multipart/form-data
});

/* ══════════════════════════════════════
   BOTONES
══════════════════════════════════════ */
function irAMisPropiedades() { window.location.href = 'Mis_Propiedades.php'; }
document.getElementById('btnCancelar')?.addEventListener('click', irAMisPropiedades);
document.getElementById('btnCancelarHeader')?.addEventListener('click', irAMisPropiedades);
document.getElementById('btnGuardarHeader')?.addEventListener('click', () => form?.requestSubmit());

window.confirmarEliminar = function (id, titulo) {
    if (confirm(`¿Eliminar la propiedad "${titulo}"?\n\nEsta acción no se puede deshacer.`)) {
        window.location.href = `../controllers/vendedorPropiedadController.php?action=eliminar&id=${id}`;
    }
};

/* ══════════════════════════════════════
   TOAST
══════════════════════════════════════ */
function mostrarToast(mensaje, tipo = 'success') {
    const wrap = document.getElementById('toastWrap');
    if (!wrap) return;
    const svg = tipo === 'success'
        ? `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>`
        : `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>`;
    const t = document.createElement('div');
    t.className = `toast toast-${tipo}`;
    t.innerHTML = `${svg}<span>${mensaje}</span>`;
    wrap.appendChild(t);
    setTimeout(() => { t.classList.add('hide'); setTimeout(() => t.remove(), 280); }, 3500);
}
window.mostrarToast = mostrarToast;

(function () {
    const p = new URLSearchParams(window.location.search);
    if (p.get('ok') === '1') mostrarToast('Propiedad guardada correctamente', 'success');
    if (p.get('error'))      mostrarToast(decodeURIComponent(p.get('error')), 'error');
})();