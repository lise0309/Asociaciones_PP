/**
 * PROPIEDAD FORM JS
 * PP Bienes Raíces
 */

'use strict';

/* ── Estado ── */
let map = null;
let marker = null;
let currentLat = null;
let currentLng = null;
let tempLat = null;
let tempLng = null;
let dragSrcEl = null;

/* ────────────────────────────────────────────
   MAPA — Google Maps
─────────────────────────────────────────── */
window.initMap = function () {
    const lat = parseFloat(document.getElementById('latitud').value) || 13.6894;
    const lng = parseFloat(document.getElementById('longitud').value) || -89.1872;

    currentLat = lat;
    currentLng = lng;

    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 13,
        center: { lat, lng },
        mapTypeId: 'roadmap',
        disableDefaultUI: false,
        streetViewControl: false,
        mapTypeControl: false,
    });

    marker = new google.maps.Marker({
        position: { lat, lng },
        map,
        draggable: true,
        title: 'Ubicación de la propiedad',
    });

    marker.addListener('dragend', function () {
        const pos = marker.getPosition();
        tempLat = pos.lat();
        tempLng = pos.lng();
    });

    map.addListener('click', function (e) {
        const pos = e.latLng;
        marker.setPosition(pos);
        tempLat = pos.lat();
        tempLng = pos.lng();
    });
};

function confirmarUbicacion() {
    if (tempLat !== null && tempLng !== null) {
        currentLat = tempLat;
        currentLng = tempLng;
        document.getElementById('latitud').value = currentLat.toFixed(6);
        document.getElementById('longitud').value = currentLng.toFixed(6);
        document.getElementById('coordenadasTexto').textContent =
            currentLat.toFixed(4) + '° N, ' + Math.abs(currentLng).toFixed(4) + '° O';
    }
    cerrarModalMapa();
}

/* ────────────────────────────────────────────
   MODAL MAPA
─────────────────────────────────────────── */
const modalOverlay = document.getElementById('modalMapaOverlay');

function abrirModalMapa() {
    if (!modalOverlay) return;
    modalOverlay.style.display = 'flex';
    tempLat = currentLat;
    tempLng = currentLng;
    // Trigger resize para que el mapa se renderice bien
    setTimeout(() => {
        if (map) {
            google.maps.event.trigger(map, 'resize');
            if (currentLat && currentLng) {
                map.setCenter({ lat: currentLat, lng: currentLng });
            }
        }
    }, 150);
}

function cerrarModalMapa() {
    if (modalOverlay) modalOverlay.style.display = 'none';
}

document.getElementById('btnMapa')?.addEventListener('click', abrirModalMapa);
document.getElementById('mapaPreview')?.addEventListener('click', abrirModalMapa);
document.getElementById('modalMapaCerrar')?.addEventListener('click', cerrarModalMapa);
document.getElementById('btnCerrarMapa')?.addEventListener('click', cerrarModalMapa);
document.getElementById('btnConfirmarMapa')?.addEventListener('click', confirmarUbicacion);

modalOverlay?.addEventListener('click', function (e) {
    if (e.target === modalOverlay) cerrarModalMapa();
});

/* ────────────────────────────────────────────
   FOTOS — Upload & Preview
─────────────────────────────────────────── */
const fotosInput  = document.getElementById('fotosInput');
const fotosGrid   = document.getElementById('fotosGrid');
const btnAgregar  = document.getElementById('btnAgregarFoto');

btnAgregar?.addEventListener('click', () => fotosInput?.click());

fotosInput?.addEventListener('change', (e) => {
    handleFiles(Array.from(e.target.files));
    fotosInput.value = ''; // reset para permitir re-selección del mismo archivo
});

function handleFiles(files) {
    const maxFotos = 20;
    const actuales = fotosGrid.querySelectorAll('.foto-item').length;
    const permitidas = maxFotos - actuales;

    if (files.length > permitidas) {
        mostrarToast(`Máximo ${maxFotos} fotos. Solo se agregarán ${permitidas}.`, 'error');
        files = files.slice(0, permitidas);
    }

    files.forEach(file => {
        if (!file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = (e) => agregarFotoPreview(e.target.result, file.name, file);
        reader.readAsDataURL(file);
    });
}

function agregarFotoPreview(src, filename, file) {
    // Crear input oculto para el archivo — se usará al enviar el form
    // (El file input múltiple original ya captura los archivos)

    const div = document.createElement('div');
    div.className = 'foto-item';
    div.setAttribute('data-new', 'true');
    div.setAttribute('data-filename', filename);
    div.setAttribute('draggable', 'true');

    div.innerHTML = `
        <img src="${src}" alt="${filename}">
        <button type="button" class="foto-delete" title="Eliminar">
            <svg viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    `;

    div.querySelector('.foto-delete').addEventListener('click', () => {
        div.remove();
        actualizarPortada();
    });

    // Insertar antes del botón "Agregar foto"
    fotosGrid.insertBefore(div, btnAgregar);
    agregarDragEvents(div);
    actualizarPortada();
}

/* Eliminar fotos existentes (del servidor) */
window.eliminarFotoExistente = function (btn, fotoId) {
    const item = btn.closest('.foto-item');
    item.remove();
    actualizarPortada();

    // Agregar campo hidden para que el controller sepa que debe borrarla
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'fotos_eliminar[]';
    input.value = fotoId;
    document.getElementById('propiedadForm').appendChild(input);
};

/* Actualizar badge de portada */
function actualizarPortada() {
    const items = fotosGrid.querySelectorAll('.foto-item');
    items.forEach((item, i) => {
        item.classList.remove('is-portada');
        const badge = item.querySelector('.foto-badge');
        if (badge) badge.remove();

        if (i === 0) {
            item.classList.add('is-portada');
            const b = document.createElement('div');
            b.className = 'foto-badge';
            b.textContent = 'Principal';
            item.prepend(b);
        }
    });
}

/* ────────────────────────────────────────────
   DRAG & DROP — Reordenar fotos
─────────────────────────────────────────── */
function agregarDragEvents(el) {
    el.addEventListener('dragstart', onDragStart);
    el.addEventListener('dragover',  onDragOver);
    el.addEventListener('dragenter', onDragEnter);
    el.addEventListener('dragleave', onDragLeave);
    el.addEventListener('drop',      onDrop);
    el.addEventListener('dragend',   onDragEnd);
}

function onDragStart(e) {
    dragSrcEl = this;
    this.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
}

function onDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    return false;
}

function onDragEnter() {
    if (this !== dragSrcEl) this.classList.add('drag-over');
}

function onDragLeave() {
    this.classList.remove('drag-over');
}

function onDrop(e) {
    e.preventDefault();
    e.stopPropagation();

    if (dragSrcEl && dragSrcEl !== this) {
        const srcIdx  = getIndex(dragSrcEl);
        const destIdx = getIndex(this);
        const addBtn  = document.getElementById('btnAgregarFoto');

        if (srcIdx < destIdx) {
            fotosGrid.insertBefore(dragSrcEl, this.nextSibling);
        } else {
            fotosGrid.insertBefore(dragSrcEl, this);
        }

        // Asegurar que el botón "Agregar" siga al final
        fotosGrid.appendChild(addBtn);
    }

    this.classList.remove('drag-over');
    actualizarPortada();
    return false;
}

function onDragEnd() {
    this.classList.remove('dragging');
    fotosGrid.querySelectorAll('.foto-item').forEach(el => el.classList.remove('drag-over'));
}

function getIndex(el) {
    return Array.from(fotosGrid.children).indexOf(el);
}

// Aplicar drag events a fotos existentes al cargar la página
document.querySelectorAll('.foto-item').forEach(el => {
    el.setAttribute('draggable', 'true');
    agregarDragEvents(el);

    // Agregar evento de eliminar a fotos existentes
    const deleteBtn = el.querySelector('.btn-eliminar-foto, .foto-delete');
    if (deleteBtn) {
        const fotoId = el.getAttribute('data-foto-id');
        deleteBtn.addEventListener('click', () => {
            if (fotoId) {
                window.eliminarFotoExistente(deleteBtn, fotoId);
            } else {
                el.remove();
                actualizarPortada();
            }
        });
    }
});

/* ────────────────────────────────────────────
   VALIDACIÓN Y ENVÍO
─────────────────────────────────────────── */
const form = document.getElementById('propiedadForm');

form?.addEventListener('submit', function (e) {
    // Sincronizar destacar
    const destacarRadio = document.querySelector('input[name="destacar_radio"]:checked');
    if (destacarRadio) {
        document.getElementById('destacarHidden').value = destacarRadio.value;
    }

    // Validaciones
    const campos = [
        { id: 'titulo',       msg: 'El nombre de la propiedad es requerido' },
        { id: 'tipo_inmueble',msg: 'Selecciona el tipo de inmueble' },
        { id: 'precio',       msg: 'Ingresa un precio válido' },
        { id: 'descripcion',  msg: 'La descripción es requerida' },
        { id: 'departamento', msg: 'Selecciona el departamento' },
        { id: 'municipio',    msg: 'Ingresa el municipio' },
        { id: 'direccion',    msg: 'Ingresa la dirección exacta' },
    ];

    for (const campo of campos) {
        const el = document.getElementById(campo.id);
        if (!el) continue;
        const val = el.value.trim();
        if (!val || val === '0') {
            e.preventDefault();
            mostrarToast(campo.msg, 'error');
            el.focus();
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
    }

    const precio = parseFloat(document.getElementById('precio').value);
    if (isNaN(precio) || precio <= 0) {
        e.preventDefault();
        mostrarToast('Ingresa un precio válido mayor a 0', 'error');
        document.getElementById('precio').focus();
        return;
    }

    // Estado de carga en botones
    const btnGuardar = document.getElementById('btnGuardar');
    const btnGuardarHeader = document.getElementById('btnGuardarHeader');
    if (btnGuardar) {
        btnGuardar.classList.add('loading');
        btnGuardar.textContent = 'Guardando...';
    }
    if (btnGuardarHeader) {
        btnGuardarHeader.classList.add('loading');
    }
});

/* ────────────────────────────────────────────
   BOTONES — Cancelar / Guardar header
─────────────────────────────────────────── */
function irAMisPropiedades() {
    window.location.href = 'mis_propiedades.php';
}

document.getElementById('btnCancelar')?.addEventListener('click', irAMisPropiedades);
document.getElementById('btnCancelarHeader')?.addEventListener('click', irAMisPropiedades);

document.getElementById('btnGuardarHeader')?.addEventListener('click', () => {
    form?.requestSubmit();
});

/* ────────────────────────────────────────────
   TOAST NOTIFICATIONS
─────────────────────────────────────────── */
function mostrarToast(mensaje, tipo = 'success') {
    const wrap = document.getElementById('toastWrap');
    if (!wrap) return;

    const iconSVG = tipo === 'success'
        ? `<path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/>`
        : `<path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>`;

    const toast = document.createElement('div');
    toast.className = `toast toast-${tipo}`;
    toast.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">${iconSVG}</svg>
        <span>${mensaje}</span>
    `;

    wrap.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('hide');
        setTimeout(() => toast.remove(), 280);
    }, 3500);
}

// Exponer para uso externo (ej: desde el controller via query string)
window.mostrarToast = mostrarToast;

/* ── Mostrar toast de éxito si viene en URL ── */
(function () {
    const params = new URLSearchParams(window.location.search);
    if (params.get('ok') === '1') {
        mostrarToast('Propiedad guardada correctamente', 'success');
        // Limpiar param de URL sin recargar
        const url = new URL(window.location.href);
        url.searchParams.delete('ok');
        window.history.replaceState({}, '', url.toString());
    }
    if (params.get('error')) {
        mostrarToast(decodeURIComponent(params.get('error')), 'error');
    }
})();