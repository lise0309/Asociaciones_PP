/**
 * REPORTES JS - PP Bienes Raíces
 */

let charts = {};

document.addEventListener('DOMContentLoaded', function() {
    cargarReportes();
});

async function cargarReportes() {
    try {
        const response = await fetch('../../api/reportes_api.php?action=dashboard');
        const result = await response.json();
        
        if (result.success) {
            const data = result.data;
            
            // Renderizar KPI
            renderizarKPI(data.kpi);
            
            // Renderizar gráficas
            renderizarGraficaContratosMes(data.contratos_mes);
            renderizarGraficaPropiedadesTipo(data.propiedades_tipo);
            renderizarGraficaContratosEstado(data.contratos_estado);
            
            // Renderizar tablas
            renderizarTablaVendedores(data.top_vendedores);
            renderizarTablaPropiedades(data.propiedades_top);
        } else {
            console.error('Error:', result.error);
        }
    } catch (error) {
        console.error('Error al cargar reportes:', error);
    }
}

function renderizarKPI(kpi) {
    const container = document.getElementById('kpiContainer');
    if (!container) return;
    
    const montoTotal = new Intl.NumberFormat('es-SV', { style: 'currency', currency: 'USD' }).format(kpi.monto_total || 0);
    
    container.innerHTML = `
        <div class="kpi-card">
            <div class="kpi-label">Propiedades</div>
            <div class="kpi-value">${kpi.total_propiedades || 0}</div>
            <div class="kpi-sub">en total</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Usuarios</div>
            <div class="kpi-value">${kpi.total_usuarios || 0}</div>
            <div class="kpi-sub">registrados</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Contratos</div>
            <div class="kpi-value">${kpi.total_contratos || 0}</div>
            <div class="kpi-sub">${kpi.contratos_mes || 0} este mes</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Monto total</div>
            <div class="kpi-value">${montoTotal}</div>
            <div class="kpi-sub">en contratos</div>
        </div>
    `;
}

function renderizarGraficaContratosMes(data) {
    const canvas = document.getElementById('chartContratosMes');
    if (!canvas) return;
    
    const meses = data.map(item => item.mes);
    const totales = data.map(item => item.total);
    
    if (charts.contratosMes) charts.contratosMes.destroy();
    
    charts.contratosMes = new Chart(canvas, {
        type: 'line',
        data: {
            labels: meses,
            datasets: [{
                label: 'Contratos',
                data: totales,
                borderColor: '#1A1953',
                backgroundColor: 'rgba(26, 25, 83, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { position: 'top' } }
        }
    });
}

function renderizarGraficaPropiedadesTipo(data) {
    const canvas = document.getElementById('chartPropiedadesTipo');
    if (!canvas) return;
    
    const tipos = data.map(item => item.tipo);
    const totales = data.map(item => item.total);
    
    if (charts.propiedadesTipo) charts.propiedadesTipo.destroy();
    
    charts.propiedadesTipo = new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: tipos,
            datasets: [{
                data: totales,
                backgroundColor: ['#1A1953', '#252477', '#FFD45A', '#E6B800', '#6B7280']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { position: 'right' } }
        }
    });
}

function renderizarGraficaContratosEstado(data) {
    const canvas = document.getElementById('chartContratosEstado');
    if (!canvas) return;
    
    const estados = data.map(item => item.estado);
    const totales = data.map(item => item.total);
    
    if (charts.contratosEstado) charts.contratosEstado.destroy();
    
    charts.contratosEstado = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: estados,
            datasets: [{
                label: 'Contratos',
                data: totales,
                backgroundColor: '#1A1953',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } }
        }
    });
}

function renderizarTablaVendedores(vendedores) {
    const tbody = document.querySelector('#tablaVendedores tbody');
    if (!tbody) return;
    
    if (!vendedores || vendedores.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3">No hay datos disponibles</td></tr>';
        return;
    }
    
    tbody.innerHTML = vendedores.map(v => `
        <tr>
            <td>${v.nombre} ${v.apellido}</td>
            <td>${v.total_ventas || 0}</td>
            <td class="monto">$${new Intl.NumberFormat().format(v.monto_total || 0)}</td>
        </tr>
    `).join('');
}

function renderizarTablaPropiedades(propiedades) {
    const tbody = document.querySelector('#tablaPropiedades tbody');
    if (!tbody) return;
    
    if (!propiedades || propiedades.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3">No hay datos disponibles</td></tr>';
        return;
    }
    
    tbody.innerHTML = propiedades.map(p => `
        <tr>
            <td>${p.titulo_anuncio}</td>
            <td class="monto">$${new Intl.NumberFormat().format(p.precio_pedido)}</td>
            <td>${p.vendedor_nombre} ${p.vendedor_apellido}</td>
        </tr>
    `).join('');
}