// contrato.js
console.log('contrato.js cargado');

document.addEventListener('DOMContentLoaded', function() {
    cargarContratos();
    
    const refreshBtn = document.getElementById('refreshContratos');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', cargarContratos);
    }
});

async function cargarContratos() {
    const container = document.getElementById('contratosContainer');
    if (!container) return;
    
    container.innerHTML = '<div class="loading">Cargando contratos...</div>';
    
    try {
        const response = await fetch('/Asociaciones_PP/controllers/ContratoController.php?action=listar');
        const data = await response.json();
        
        if (data.success && data.contratos && data.contratos.length > 0) {
            let html = '<div class="contratos-list">';
            
            for (const c of data.contratos) {
                let estadoClass = 'estado-borrador';
                let estadoNombre = c.estado_nombre || 'Borrador';
                
                if (c.estado_contrato_id == 18) {
                    estadoClass = 'estado-enviado';
                    estadoNombre = 'Enviado';
                } else if (c.estado_contrato_id == 19) {
                    estadoClass = 'estado-firmado';
                    estadoNombre = 'Firmado';
                } else if (c.estado_contrato_id == 21) {
                    estadoClass = 'estado-anulado';
                    estadoNombre = 'Anulado';
                }
                
                const monto = new Intl.NumberFormat('es-SV', { style: 'currency', currency: c.moneda || 'USD' }).format(c.monto_acordado);
                const numero = c.numero_contrato || c.id.substring(0, 8);
                
                const botonEliminar = (c.estado_contrato_id != 21) ? 
                    `<button class="btn-panel btn-panel-danger btn-sm" onclick="eliminarContrato('${c.id}', ${c.estado_contrato_id})">
                        🗑️ ${c.estado_contrato_id == 17 ? 'Eliminar' : 'Anular'}
                    </button>` : '';
                
                html += `
                    <div class="contrato-item">
                        <div class="contrato-header">
                            <span class="contrato-numero">${numero}</span>
                            <span class="contrato-estado ${estadoClass}">${estadoNombre}</span>
                        </div>
                        <div class="contrato-detalle">
                            <strong>${escapeHtml(c.nombre_comprador)}</strong><br>
                            🏠 ${escapeHtml(c.titulo_anuncio)}<br>
                            💰 ${monto}
                        </div>
                        <div class="contrato-acciones">
                            <button class="btn-panel btn-panel-outline btn-sm" onclick="generarDocumento('${c.id}')">📄 Generar</button>
                            <button class="btn-panel btn-panel-primary btn-sm" onclick="verContrato('${c.id}')">👁️ Ver</button>
                            ${botonEliminar}
                        </div>
                    </div>
                `;
            }
            
            html += '</div>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="loading">📭 No hay contratos aún. Crea uno nuevo.</div>';
        }
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = '<div class="loading">❌ Error al cargar contratos</div>';
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        return m === '&' ? '&amp;' : (m === '<' ? '&lt;' : '&gt;');
    });
}

// GENERAR DOCUMENTO - REDIRIGE DIRECTAMENTE
function generarDocumento(contratoId) {
    if (!confirm('📄 ¿Generar el documento del contrato?')) return;
    window.location.href = `/Asociaciones_PP/controllers/ContratoController.php?action=generar&id=${contratoId}`;
}

function verContrato(contratoId) {
    alert('Ver detalle del contrato ' + contratoId);
}

// ELIMINAR O ANULAR CONTRATO
function eliminarContrato(contratoId, estado) {
    let mensaje = estado == 17 ? '⚠️ ¿Eliminar permanentemente este contrato?' : '⚠️ ¿Anular este contrato?';
    if (confirm(mensaje)) {
        window.location.href = `/Asociaciones_PP/controllers/ContratoController.php?action=eliminar&id=${contratoId}`;
    }
}