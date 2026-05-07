/**
 * PERFIL JS
 * PP Bienes Raíces
 */

const API_URL = '../controllers/PerfilController.php';

// Mostrar toast
function mostrarToast(mensaje, tipo = 'success') {
    const container = document.getElementById('toastMessages');
    if (!container) return;
    
    const toast = document.createElement('div');
    toast.className = `toast-message toast-${tipo}`;
    toast.innerHTML = `
        <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
            ${tipo === 'success' ? 
                '<path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/>' :
                '<path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>'
            }
        </svg>
        <span>${mensaje}</span>
    `;
    
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
            mostrarToast('Perfil actualizado correctamente', 'success');
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
        mostrarToast('Las contraseñas nuevas no coinciden', 'error');
        return;
    }
    
    if (nuevaPassword.length < 6) {
        mostrarToast('La nueva contraseña debe tener al menos 6 caracteres', 'error');
        return;
    }
    
    const data = { 
        password_actual: passwordActual, 
        nueva_password: nuevaPassword 
    };
    
    try {
        const response = await fetch(`${API_URL}?accion=cambiar_password`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('formCambiarPasswordModal').reset();
            modalCambiarPassword.style.display = 'none';
            mostrarToast('Contraseña actualizada correctamente', 'success');
        } else {
            mostrarToast(result.error || 'Error al cambiar contraseña', 'error');
        }
    } catch (error) {
        mostrarToast('Error de conexión', 'error');
    }
});

// ==================== SUBIR DOCUMENTO ====================
const btnSubirDocumento = document.getElementById('btnSubirDocumento');
const modalSubirDocumento = document.getElementById('modalSubirDocumento');
const closeSubirDocumento = document.getElementById('closeSubirDocumento');
const formSubirDocumento = document.getElementById('formSubirDocumento');

btnSubirDocumento?.addEventListener('click', () => {
    modalSubirDocumento.style.display = 'flex';
});

closeSubirDocumento?.addEventListener('click', () => {
    modalSubirDocumento.style.display = 'none';
});

modalSubirDocumento?.addEventListener('click', (e) => {
    if (e.target === modalSubirDocumento) {
        modalSubirDocumento.style.display = 'none';
    }
});

formSubirDocumento?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('tipo', document.getElementById('docTipo').value);
    formData.append('archivo', document.getElementById('docArchivo').files[0]);
    
    try {
        const response = await fetch(`${API_URL}?accion=subir_documento`, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            location.reload();
        } else {
            mostrarToast(result.error || 'Error al subir documento', 'error');
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

// ==================== ELIMINAR DOCUMENTO ====================
window.eliminarDocumento = async (id) => {
    if (!confirm('¿Eliminar este documento?')) return;
    
    try {
        const response = await fetch(`${API_URL}?accion=eliminar_documento&id=${id}`);
        const result = await response.json();
        
        if (result.success) {
            location.reload();
        } else {
            mostrarToast('Error al eliminar', 'error');
        }
    } catch (error) {
        mostrarToast('Error de conexión', 'error');
    }
};