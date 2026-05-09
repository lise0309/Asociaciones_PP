let contratosGlobal = [];
let etapasGlobal = [];
let contratoIdSeleccionado = null;

document.addEventListener('DOMContentLoaded', () => {
    cargarContratos();
    
    // Filtros
    document.querySelectorAll('.filtro-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            filtrarContratos(btn.dataset.filtro);
        });
    });
    
    // Modal
    const modal = document.getElementById('modalAvance');
    const closeBtn = document.querySelector('.close');
    closeBtn.onclick = () => modal.style.display = 'none';
    window.onclick = (e) => {
        if (e.target === modal) modal.style.display = 'none';
    };
    
    document.getElementById('btnConfirmarAvance').onclick = () => {
        const comentario = document.getElementById('comentarioAvance').value;
        avanzarContrato(contratoIdSeleccionado, comentario);
        modal.style.display = 'none';
        document.getElementById('comentarioAvance').value = '';
    };
});

async function cargarContratos() {
    const loader = document.getElementById('loader');
    const grid = document.getElementById('contratos-grid');
    loader.style.display = 'block';
    grid.style.display = 'none';
    
    try {
        const response = await fetch('/api/contratos.php');
        const data = await response.json();
        contratosGlobal = data.contratos;
        etapasGlobal = data.etapas; // Array con los estados del flujo (Borrador, Enviado, Firmado)
        
        document.getElementById('totalContratos').textContent = contratosGlobal.length;
        renderizarContratos(contratosGlobal);
        loader.style.display = 'none';
        grid.style.display = 'grid';
    } catch (err) {
        console.error(err);
        loader.innerHTML = 'Error al cargar contratos. Verifica conexión.';
    }
}

function renderizarContratos(contratos) {
    const grid = document.getElementById('contratos-grid');
    if (!contratos.length) {
        grid.innerHTML = '<div style="grid-column:1/-1; text-align:center;">No hay contratos en esta etapa</div>';
        return;
    }
    
    grid.innerHTML = contratos.map(contrato => {
        const estadoActualId = contrato.estado_contrato_id;
        const montoFormateado = new Intl.NumberFormat('es-SV', { style: 'currency', currency: contrato.moneda }).format(contrato.monto_acordado);
        
        // Generar etapas según el flujo definido (17,18,19)
        let etapasHtml = '';
        etapasGlobal.forEach((etapa, idx) => {
            const etapaId = etapa.id;
            const fecha = contrato.fechas_etapas[etapaId] || '---';
            let clase = '';
            if (estadoActualId > etapaId) clase = 'completada';
            else if (estadoActualId === etapaId) clase = 'activa';
            
            etapasHtml += `
                <div class="etapa ${clase}" onclick="verHistorial('${contrato.id}')">
                    <div class="circulo">${estadoActualId > etapaId ? '✓' : idx+1}</div>
                    <div class="nombre-etapa">${etapa.nombre}</div>
                    <div class="fecha-etapa">${fecha}</div>
                </div>
            `;
        });
        
        const puedeAvanzar = (estadoActualId === 17 || estadoActualId === 18);
        const siguienteNombre = estadoActualId === 17 ? 'Enviar a Cliente' : (estadoActualId === 18 ? 'Marcar como Firmado' : '');
        
        return `
            <div class="contrato-card" data-estado="${estadoActualId}">
                <div class="contrato-header">
                    <h3>${escapeHtml(contrato.nombre_comprador)}</h3>
                    <span class="estado-badge" style="background:${contrato.estado_color || '#cbd5e1'}20; color:${contrato.estado_color || '#000'}">
                        ${contrato.estado_nombre}
                    </span>
                </div>
                <div class="monto">${montoFormateado}</div>
                <div class="propiedad-info">🏠 Propiedad: ${escapeHtml(contrato.propiedad_titulo)}</div>
                <div class="etapas-linea">
                    ${etapasHtml}
                </div>
                <div class="acciones">
                    <button class="btn-secondary" onclick="verDetalle('${contrato.id}')">📄 Ver Detalle</button>
                    ${puedeAvanzar ? `<button class="btn-primary" onclick="abrirModal('${contrato.id}')">➡️ ${siguienteNombre}</button>` : 
                                      (estadoActualId === 19 ? '<button class="btn-primary" disabled style="opacity:0.5;">✅ Contrato Finalizado</button>' : '')}
                </div>
            </div>
        `;
    }).join('');
}

function filtrarContratos(estadoId) {
    if (estadoId === 'todos') {
        renderizarContratos(contratosGlobal);
    } else {
        const filtrados = contratosGlobal.filter(c => c.estado_contrato_id == estadoId);
        renderizarContratos(filtrados);
    }
}

function abrirModal(contratoId) {
    contratoIdSeleccionado = contratoId;
    const contrato = contratosGlobal.find(c => c.id === contratoId);
    document.getElementById('modalInfoContrato').innerHTML = `Contrato de <strong>${escapeHtml(contrato.nombre_comprador)}</strong>`;
    document.getElementById('modalAvance').style.display = 'flex';
}

async function avanzarContrato(contratoId, comentario) {
    try {
        const response = await fetch('/api/contratos.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ contrato_id: contratoId, comentario })
        });
        const result = await response.json();
        if (result.success) {
            mostrarNotificacion('Contrato avanzado correctamente', 'success');
            cargarContratos(); // Recargar lista
        } else {
            mostrarNotificacion(result.message || 'Error al avanzar', 'error');
        }
    } catch (err) {
        mostrarNotificacion('Error de conexión', 'error');
    }
}

function verDetalle(contratoId) {
    const c = contratosGlobal.find(c => c.id === contratoId);
    alert(`📋 Detalle del contrato\nCliente: ${c.nombre_comprador}\nCorreo: ${c.correo_comprador}\nMonto: ${c.monto_acordado} ${c.moneda}\nEstado: ${c.estado_nombre}`);
}

function verHistorial(contratoId) {
    const c = contratosGlobal.find(c => c.id === contratoId);
    let historial = `📜 Historial de cambios - ${c.nombre_comprador}\n\n`;
    etapasGlobal.forEach(etapa => {
        const fecha = c.fechas_etapas[etapa.id];
        historial += `${etapa.nombre}: ${fecha || 'Pendiente'}\n`;
    });
    alert(historial);
}

function mostrarNotificacion(msg, tipo) {
    const noti = document.createElement('div');
    noti.textContent = msg;
    noti.style.cssText = `position:fixed; bottom:20px; right:20px; background:${tipo === 'success' ? '#22c55e' : '#ef4444'}; color:white; padding:12px 20px; border-radius:8px; z-index:9999;`;
    document.body.appendChild(noti);
    setTimeout(() => noti.remove(), 3000);
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}