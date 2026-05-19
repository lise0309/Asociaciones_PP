/**
 * PERFIL JS - PP Bienes Raíces
 */

const API_URL = '../controllers/PerfilController.php';

function mostrarToast(mensaje, tipo = 'success') {
    const container = document.getElementById('toastMessages');
    if (!container) return;
    
    const toast = document.createElement('div');
    toast.className = `toast-message toast-${tipo}`;
    toast.innerHTML = `<span>${mensaje}</span>`;
    
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}

// ==================== EDITAR PERFIL ====================
const btnEditarPerfil = document.getElementById('btnEditarPerfil');
const modalEditarPerfil = document.getElementById('modalEditarPerfil');
const closeEditarPerfil = document.getElementById('closeEditarPerfil');
const formEditarPerfil = document.getElementById('formEditarPerfil');

btnEditarPerfil?.addEventListener('click', () => {
    modalEditarPerfil.style.display = 'flex';
});

closeEditarPerfil?.addEventListener('click', () => {
    modalEditarPerfil.style.display = 'none';
});

modalEditarPerfil?.addEventListener('click', (e) => {
    if (e.target === modalEditarPerfil) {
        modalEditarPerfil.style.display = 'none';
    }
});

formEditarPerfil?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const data = {
        nombre: document.getElementById('editNombre').value,
        apellido: document.getElementById('editApellido').value,
        telefono: document.getElementById('editTelefono').value
    };
    
    try {
        const response = await fetch(`${API_URL}?accion=editar_perfil`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('infoNombre').innerText = data.nombre;
            document.getElementById('infoApellido').innerText = data.apellido;
            document.getElementById('infoTelefono').innerText = data.telefono || 'No especificado';
            document.getElementById('perfilNombre').innerText = `${data.nombre} ${data.apellido}`;
            modalEditarPerfil.style.display = 'none';
            mostrarToast('Perfil actualizado', 'success');
        } else {
            mostrarToast(result.error || 'Error al actualizar', 'error');
        }
    } catch (error) {
        mostrarToast('Error de conexión', 'error');
    }
});

// ==================== CAMBIAR CONTRASEÑA ====================
const btnCambiarPassword = document.getElementById('btnCambiarPasswordModal');
const modalCambiarPassword = document.getElementById('modalCambiarPassword');
const closeCambiarPassword = document.getElementById('closeCambiarPassword');
const formCambiarPassword = document.getElementById('formCambiarPasswordModal');

btnCambiarPassword?.addEventListener('click', () => {
    modalCambiarPassword.style.display = 'flex';
});

closeCambiarPassword?.addEventListener('click', () => {
    modalCambiarPassword.style.display = 'none';
});

modalCambiarPassword?.addEventListener('click', (e) => {
    if (e.target === modalCambiarPassword) {
        modalCambiarPassword.style.display = 'none';
    }
});

formCambiarPassword?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const passwordActual = document.getElementById('passwordActualModal').value;
    const nuevaPassword = document.getElementById('nuevaPasswordModal').value;
    const confirmarPassword = document.getElementById('confirmarPasswordModal').value;
    
    if (nuevaPassword !== confirmarPassword) {
        mostrarToast('Las contraseñas no coinciden', 'error');
        return;
    }
    
    if (nuevaPassword.length < 6) {
        mostrarToast('Mínimo 6 caracteres', 'error');
        return;
    }
    
    const data = { password_actual: passwordActual, nueva_password: nuevaPassword };
    
    try {
        const response = await fetch(`${API_URL}?accion=cambiar_password`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        
        if (result.success) {
            formCambiarPassword.reset();
            modalCambiarPassword.style.display = 'none';
            mostrarToast('Contraseña actualizada', 'success');
        } else {
            mostrarToast(result.error || 'Error', 'error');
        }
    } catch (error) {
        mostrarToast('Error de conexión', 'error');
    }
});

// ==================== CAMBIAR FOTO ====================
const btnCambiarFoto = document.getElementById('btnCambiarFoto');
const modalCambiarFoto = document.getElementById('modalCambiarFoto');
const closeCambiarFoto = document.getElementById('closeCambiarFoto');
const formCambiarFoto = document.getElementById('formCambiarFoto');

btnCambiarFoto?.addEventListener('click', () => {
    modalCambiarFoto.style.display = 'flex';
});

closeCambiarFoto?.addEventListener('click', () => {
    modalCambiarFoto.style.display = 'none';
});

modalCambiarFoto?.addEventListener('click', (e) => {
    if (e.target === modalCambiarFoto) {
        modalCambiarFoto.style.display = 'none';
    }
});

formCambiarFoto?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('foto', document.getElementById('fotoArchivo').files[0]);
    
    try {
        const response = await fetch(`${API_URL}?accion=cambiar_foto`, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            location.reload();
        } else {
            mostrarToast(result.error || 'Error al subir foto', 'error');
        }
    } catch (error) {
        mostrarToast('Error de conexión', 'error');
    }
});