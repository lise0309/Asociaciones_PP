/**
 * PROPIEDADES JS
 * PP Bienes Raíces
 */

let currentPage = 1;
let currentFilters = {
    buscar: '',
    tipo_inmueble: '',
    negocio: '',
    estado: ''
};

// Cargar propiedades al inicio
document.addEventListener('DOMContentLoaded', () => {
    cargarPropiedades();
    
    // Event listeners
    document.getElementById('filtroBuscar')?.addEventListener('input', debounce(() => {
        currentFilters.buscar = document.getElementById('filtroBuscar').value;
        currentPage = 1;
        cargarPropiedades();
    }, 500));
    
    document.getElementById('filtroTipoInmueble')?.addEventListener('change', (e) => {
        currentFilters.tipo_inmueble = e.target.value;
        currentPage = 1;
        cargarPropiedades();
    });
    
    document.getElementById('filtroNegocio')?.addEventListener('change', (e) => {
        currentFilters.negocio = e.target.value;
        currentPage = 1;
        cargarPropiedades();
    });
    
    document.getElementById('filtroEstado')?.addEventListener('change', (e) => {
        currentFilters.estado = e.target.value;
        currentPage = 1;
        cargarPropiedades();
    });
    
    document.getElementById('btnNuevaPropiedad')?.addEventListener('click', () => {
        window.location.href = 'propiedad_form.php';
    });
    
    // Modal cerrar
    document.getElementById('modalDetalleCerrar')?.addEventListener('click', cerrarModalDetalle);
    document.getElementById('btnCerrarDetalle')?.addEventListener('click', cerrarModalDetalle);
    document.getElementById('modalDetalleOverlay')?.addEventListener('click', (e) => {
        if (e.target === document.getElementById('modalDetalleOverlay')) cerrarModalDetalle();
    });
    
    document.getElementById('modalEstadoCerrar')?.addEventListener('click', cerrarModalEstado);
    document.getElementById('btnCancelarEstado')?.addEventListener('click', cerrarModalEstado);
    document.getElementById('modalEstadoOverlay')?.addEventListener('click', (e) => {
        if (e.target === document.getElementById('modalEstadoOverlay')) cerrarModalEstado();
    });
    
    document.getElementById('modalEliminarCerrar')?.addEventListener('click', cerrarModalEliminar);
    document.getElementById('btnCancelarEliminar')?.addEventListener('click', cerrarModalEliminar);
    document.getElementById('modalEliminarOverlay')?.addEventListener('click', (e) => {
        if (e.target === document.getElementById('modalEliminarOverlay')) cerrarModalEliminar();
    });
    
    document.getElementById('btnConfirmarEliminar')?.addEventListener('click', confirmarEliminar);
    document.getElementById('btnConfirmarEstado')?.addEventListener('click', confirmarCambioEstado);
});

async function cargarPropiedades() {
    const tbody = document.getElementById('tablaBody');
    tbody.innerHTML = `<tr><td colspan="9" class="tabla-loading"><div class="loading-spinner"></div>Cargando propiedades...</td></tr>`;
    
    try {
        const params = new URLSearchParams({
            accion: 'listar',
            pagina: currentPage,
            ...currentFilters
        });
        
        const response = await fetch(`${API_URL}?${params}`);
        const data = await response.json();
        
        if (data.success) {
            actualizarKPIs(data.kpis);
            renderizarTabla(data.propiedades);
            document.getElementById('subtituloTabla').textContent = `${data.total} propiedad(es) encontrada(s)`;
        } else {
            mostrarError(data.error || 'Error al cargar propiedades');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarError('Error de conexión al servidor');
    }
}

function renderizarTabla(propiedades) {
    const tbody = document.getElementById('tablaBody');
    
    if (!propiedades || propiedades.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="tabla-empty"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg><p>No se encontraron propiedades</p></td></tr>`;
        return;
    }
    
    tbody.innerHTML = propiedades.map(prop => `
        <tr>
            <td class="prop-titulo">
                <div class="prop-thumb">
                    ${prop.foto ? `<img src="../${prop.foto}" alt="${prop.titulo_anuncio}">` : '<div class="no-img">🏠</div>'}
                </div>
                <div class="prop-info">
                    <strong>${escapeHtml(prop.titulo_anuncio.substring(0, 50))}</strong>
                    <small>${escapeHtml(prop.direccion_exacta || 'Dirección no especificada')}</small>
                </div>
            </td>
            <td>${escapeHtml(prop.tipo_inmueble)}</td>
            <td>${escapeHtml(prop.tipo_negocio)}</td>
            <td>${escapeHtml(prop.vendedor)}</td>
            <td class="prop-precio">$${formatNumber(prop.precio_pedido)}</td>
            <td>${escapeHtml(prop.departamento)}</td>
            <td><span class="status-badge" style="background: ${prop.estado_color}20; color: ${prop.estado_color};">${escapeHtml(prop.estado_publicacion)}</span></td>
            <td class="prop-destacada">${prop.es_anuncio_destacado ? '⭐ Sí' : '—'}</td>
            <td class="acciones">
                <button class="btn-icon" onclick="verDetalle('${prop.id}')" title="Ver detalles">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg>
                </button>
                <button class="btn-icon" onclick="abrirModalEstado('${prop.id}', '${escapeHtml(prop.titulo_anuncio)}')" title="Cambiar estado">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 01-7.876 2.03.75.75 0 01.334-1.329 4 4 0 003.644-5.93.75.75 0 011.27-.764 5.5 5.5 0 012.628 5.993zM12 1a1 1 0 01.707 1.707L9.414 6l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4A1 1 0 0112 1z" clip-rule="evenodd"/></svg>
                </button>
                <button class="btn-icon btn-icon-danger" onclick="abrirModalEliminar('${prop.id}', '${escapeHtml(prop.titulo_anuncio)}')" title="Eliminar">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd"/></svg>
                </button>
            </td>
        </tr>
    `).join('');
}

async function verDetalle(id) {
    const modal = document.getElementById('modalDetalleOverlay');
    const body = document.getElementById('detalleBody');
    const titulo = document.getElementById('detalleTitulo');
    
    modal.style.display = 'flex';
    body.innerHTML = '<div class="loading-spinner"></div>Cargando detalles...';
    
    try {
        const response = await fetch(`${API_URL}?accion=detalle&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            titulo.textContent = data.propiedad.titulo_anuncio;
            body.innerHTML = renderDetalle(data.propiedad, data.fotos);
        } else {
            body.innerHTML = `<p class="error-msg">${data.error || 'Error al cargar detalles'}</p>`;
        }
    } catch (error) {
        body.innerHTML = '<p class="error-msg">Error de conexión</p>';
    }
}

function renderDetalle(prop, fotos) {
    return `
        <div class="detalle-grid">
            <div class="detalle-info">
                <h4>Información general</h4>
                <p><strong>Tipo:</strong> ${escapeHtml(prop.tipo_inmueble)}</p>
                <p><strong>Negocio:</strong> ${escapeHtml(prop.tipo_negocio)}</p>
                <p><strong>Precio:</strong> $${formatNumber(prop.precio_pedido)}</p>
                <p><strong>Vendedor:</strong> ${escapeHtml(prop.vendedor)}</p>
                <p><strong>Correo:</strong> ${escapeHtml(prop.correo_vendedor || 'No disponible')}</p>
                <p><strong>Teléfono:</strong> ${escapeHtml(prop.telefono_vendedor || 'No disponible')}</p>
                
                <h4>Ubicación</h4>
                <p><strong>Departamento:</strong> ${escapeHtml(prop.departamento)}</p>
                <p><strong>Municipio:</strong> ${escapeHtml(prop.municipio)}</p>
                <p><strong>Dirección:</strong> ${escapeHtml(prop.direccion_exacta || 'No especificada')}</p>
                
                <h4>Características</h4>
                <p><strong>Habitaciones:</strong> ${prop.num_habitaciones || 'N/A'}</p>
                <p><strong>Baños:</strong> ${prop.num_banos || 'N/A'}</p>
                <p><strong>Metros construcción:</strong> ${prop.metros_construccion || 'N/A'} m²</p>
                <p><strong>Estacionamiento:</strong> ${prop.tiene_estacionamiento ? 'Sí' : 'No'}</p>
                <p><strong>Piscina:</strong> ${prop.tiene_piscina ? 'Sí' : 'No'}</p>
            </div>
            ${fotos && fotos.length > 0 ? `
                <div class="detalle-fotos">
                    <h4>Fotos (${fotos.length})</h4>
                    <div class="fotos-grid">
                        ${fotos.map(foto => `
                            <img src="../${foto.url_foto_miniatura || foto.url_foto_original}" alt="Foto propiedad" onclick="window.open('../${foto.url_foto_original}', '_blank')">
                        `).join('')}
                    </div>
                </div>
            ` : '<div class="detalle-fotos"><p>No hay fotos disponibles</p></div>'}
        </div>
        ${prop.descripcion_detallada ? `<div class="detalle-descripcion"><h4>Descripción</h4><p>${escapeHtml(prop.descripcion_detallada)}</p></div>` : ''}
    `;
}

function cerrarModalDetalle() {
    document.getElementById('modalDetalleOverlay').style.display = 'none';
}

// Funciones auxiliares
function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

function formatNumber(num) {
    return new Intl.NumberFormat('es-SV').format(num);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
        }
    }
}