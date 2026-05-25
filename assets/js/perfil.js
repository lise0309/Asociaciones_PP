/**
 * perfil.js — PP Bienes Raíces
 */
'use strict';

const API_PERFIL = '../controllers/PerfilController.php';

/* ── Toast ── */
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

/* ── Abrir/cerrar modales ── */
function abrirModal(id)  { document.getElementById(id).style.display = 'flex'; }
function cerrarModal(id) { document.getElementById(id).style.display = 'none'; }

// Cerrar al hacer click fuera
document.querySelectorAll('.modal-pf-overlay').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) m.style.display = 'none'; });
});

// Botones de apertura
document.getElementById('btnEditarPerfil')?.addEventListener('click', () => abrirModal('modalEditarPerfil'));
document.getElementById('btnCambiarPasswordModal')?.addEventListener('click', () => abrirModal('modalCambiarPassword'));
document.getElementById('btnCambiarFoto')?.addEventListener('click', () => abrirModal('modalCambiarFoto'));

/* ── Editar perfil ── */
document.getElementById('formEditarPerfil')?.addEventListener('submit', async e => {
    e.preventDefault();
    const nombre   = document.getElementById('editNombre').value.trim();
    const apellido = document.getElementById('editApellido').value.trim();
    const telefono = document.getElementById('editTelefono').value.trim();

    if (!nombre || !apellido) { toast('Nombre y apellido son requeridos', 'error'); return; }

    try {
        const res  = await fetch(`${API_PERFIL}?accion=editar_perfil`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre, apellido, telefono }),
        });
        const data = await res.json();
        if (data.success) {
            // Actualizar DOM
            document.getElementById('infoNombre').textContent   = nombre;
            document.getElementById('infoApellido').textContent = apellido;
            document.getElementById('infoTelefono').textContent = telefono || 'No especificado';
            document.getElementById('perfilNombre').textContent = `${nombre} ${apellido}`;
            cerrarModal('modalEditarPerfil');
            toast('Perfil actualizado correctamente ✓', 'ok');
        } else {
            toast(data.error || 'Error al actualizar', 'error');
        }
    } catch(err) { toast('Error de conexión', 'error'); }
});

/* ── Cambiar contraseña ── */
document.getElementById('formCambiarPasswordModal')?.addEventListener('submit', async e => {
    e.preventDefault();
    const actual    = document.getElementById('passwordActualModal').value;
    const nueva     = document.getElementById('nuevaPasswordModal').value;
    const confirmar = document.getElementById('confirmarPasswordModal').value;

    if (nueva !== confirmar) { toast('Las contraseñas no coinciden', 'error'); return; }
    if (nueva.length < 6)    { toast('Mínimo 6 caracteres', 'error'); return; }

    try {
        const res  = await fetch(`${API_PERFIL}?accion=cambiar_password`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ password_actual: actual, nueva_password: nueva }),
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('formCambiarPasswordModal').reset();
            cerrarModal('modalCambiarPassword');
            toast('Contraseña actualizada correctamente ✓', 'ok');
        } else {
            toast(data.error || 'Contraseña actual incorrecta', 'error');
        }
    } catch(err) { toast('Error de conexión', 'error'); }
});

/* ── Cambiar foto ── */
document.getElementById('formCambiarFoto')?.addEventListener('submit', async e => {
    e.preventDefault();
    const archivo = document.getElementById('fotoArchivo').files[0];
    if (!archivo) { toast('Selecciona una imagen', 'error'); return; }

    const fd = new FormData();
    fd.append('foto', archivo);

    try {
        const res  = await fetch(`${API_PERFIL}?accion=cambiar_foto`, { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            toast('Foto actualizada ✓', 'ok');
            setTimeout(() => location.reload(), 800);
        } else {
            toast(data.error || 'Error al subir foto', 'error');
        }
    } catch(err) { toast('Error de conexión', 'error'); }
});