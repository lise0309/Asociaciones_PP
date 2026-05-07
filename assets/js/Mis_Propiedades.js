/**
 * MIS PROPIEDADES JS
 * PP Bienes Raíces
 * assets/js/mis_propiedades.js
 */

'use strict';

/* ════════════════════════════════════════════
   MODAL ELIMINAR
════════════════════════════════════════════ */
const modalEliminar = document.getElementById('modalEliminar');

function confirmarEliminar(id, titulo) {
    document.getElementById('eliminarId').value         = id;
    document.getElementById('modalEliminarTexto').textContent =
        'Estás a punto de eliminar "' + titulo + '". Esta acción no se puede deshacer.';
    modalEliminar.style.display = 'flex';
}

function cerrarModal() {
    if (modalEliminar) modalEliminar.style.display = 'none';
}

// Cerrar al hacer clic en el overlay
modalEliminar?.addEventListener('click', function (e) {
    if (e.target === modalEliminar) cerrarModal();
});

// Cerrar con ESC
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') cerrarModal();
});

// Exponer al scope global (llamado desde onclick en PHP)
window.confirmarEliminar = confirmarEliminar;
window.cerrarModal       = cerrarModal;

/* ════════════════════════════════════════════
   TOGGLE VISTA — grid / lista
════════════════════════════════════════════ */
const btnGrid  = document.getElementById('btnGrid');
const btnList  = document.getElementById('btnList');
const grid     = document.getElementById('propiedadesGrid');

const VISTA_KEY = 'pp_vista_propiedades';

function setVista(vista) {
    if (!grid) return;
    if (vista === 'lista') {
        grid.classList.add('vista-lista');
        btnList?.classList.add('active');
        btnGrid?.classList.remove('active');
    } else {
        grid.classList.remove('vista-lista');
        btnGrid?.classList.add('active');
        btnList?.classList.remove('active');
    }
    localStorage.setItem(VISTA_KEY, vista);
}

btnGrid?.addEventListener('click', () => setVista('grid'));
btnList?.addEventListener('click', () => setVista('lista'));

// Restaurar preferencia guardada
(function () {
    const saved = localStorage.getItem(VISTA_KEY);
    if (saved === 'lista') setVista('lista');
})();

/* ════════════════════════════════════════════
   BÚSQUEDA — enviar al presionar Enter
════════════════════════════════════════════ */
document.querySelector('.search-input')?.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('filtroForm')?.submit();
    }
});

/* ════════════════════════════════════════════
   TOAST NOTIFICATIONS
════════════════════════════════════════════ */
function mostrarToast(mensaje, tipo = 'success') {
    const wrap = document.getElementById('toastWrap');
    if (!wrap) return;

    const iconOk  = `<path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/>`;
    const iconErr = `<path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>`;

    const toast = document.createElement('div');
    toast.className = `toast toast-${tipo}`;
    toast.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            ${tipo === 'success' ? iconOk : iconErr}
        </svg>
        <span>${mensaje}</span>
    `;

    wrap.appendChild(toast);
    setTimeout(() => {
        toast.classList.add('hide');
        setTimeout(() => toast.remove(), 280);
    }, 4000);
}

/* ── Leer mensajes desde URL ── */
(function () {
    const p = new URLSearchParams(window.location.search);
    if (p.get('ok') === '1') {
        mostrarToast('Operación realizada correctamente', 'success');
        const u = new URL(window.location.href);
        u.searchParams.delete('ok');
        history.replaceState({}, '', u.toString());
    }
    if (p.get('error')) {
        mostrarToast(decodeURIComponent(p.get('error')), 'error');
        const u = new URL(window.location.href);
        u.searchParams.delete('error');
        history.replaceState({}, '', u.toString());
    }
})();