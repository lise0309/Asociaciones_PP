<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$titulo_pagina = 'Reportes y Analytics';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes y Analytics | PP Bienes Raíces</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/panel.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/reportes.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>

<div class="panel-layout">
    <?php include 'layouts/sidebar.php'; ?>

    <div class="panel-main">
        <?php include 'layouts/headerpanel.php'; ?>

        <div class="panel-content">
            <div class="reportes-container">

                <div class="page-header">
                    <h1> Reportes y Analytics</h1>
                    <p>Visualiza el rendimiento de tu plataforma</p>
                </div>

                <!-- KPI Cards -->
                <div class="kpi-grid" id="kpiContainer">
                    <div class="kpi-card">Cargando...</div>
                </div>

                <!-- Gráficas -->
                <div class="charts-row">
                    <div class="chart-card">
                        <div class="chart-header"><h3> Contratos por mes</h3></div>
                        <div class="chart-body"><canvas id="chartContratosMes" height="250"></canvas></div>
                    </div>
                    <div class="chart-card">
                        <div class="chart-header"><h3> Monto total por mes</h3></div>
                        <div class="chart-body"><canvas id="chartMontoMes" height="250"></canvas></div>
                    </div>
                </div>

                <div class="charts-row">
                    <div class="chart-card">
                        <div class="chart-header"><h3> Propiedades por tipo</h3></div>
                        <div class="chart-body"><canvas id="chartPropiedadesTipo" height="250"></canvas></div>
                    </div>
                    <div class="chart-card">
                        <div class="chart-header"><h3> Contratos por estado</h3></div>
                        <div class="chart-body"><canvas id="chartContratosEstado" height="250"></canvas></div>
                    </div>
                </div>

                <!-- Tablas -->
                <div class="tables-row">
                    <div class="table-card">
                        <div class="table-header"><h3> Top Vendedores</h3></div>
                        <div class="table-body">
                            <table class="data-table" id="tablaVendedores">
                                <thead><tr><th>Vendedor</th><th>Ventas</th><th>Monto total</th></tr></thead>
                                <tbody><tr><td colspan="3">Cargando...</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="table-card">
                        <div class="table-header"><h3> Propiedades destacadas</h3></div>
                        <div class="table-body">
                            <table class="data-table" id="tablaPropiedades">
                                <thead><tr><th>Propiedad</th><th>Precio</th><th>Vendedor</th></tr></thead>
                                <tbody><tr><td colspan="3">Cargando...</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <?php include 'layouts/footer.php'; ?>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script>
// Cargar datos directamente del controlador
fetch('../controllers/ReportesController.php')
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            const data = result.data;
            
            // KPI
            const montoTotal = new Intl.NumberFormat('es-SV', { style: 'currency', currency: 'USD' }).format(data.kpi.monto_total || 0);
            document.getElementById('kpiContainer').innerHTML = `
                <div class="kpi-card"><div class="kpi-label">Propiedades</div><div class="kpi-value">${data.kpi.total_propiedades || 0}</div><div class="kpi-sub">en total</div></div>
                <div class="kpi-card"><div class="kpi-label">Usuarios</div><div class="kpi-value">${data.kpi.total_usuarios || 0}</div><div class="kpi-sub">registrados</div></div>
                <div class="kpi-card"><div class="kpi-label">Contratos</div><div class="kpi-value">${data.kpi.total_contratos || 0}</div><div class="kpi-sub">${data.kpi.contratos_mes || 0} este mes</div></div>
                <div class="kpi-card"><div class="kpi-label">Monto total</div><div class="kpi-value">${montoTotal}</div><div class="kpi-sub">en contratos</div></div>
            `;
            
            // Gráfica: Contratos por mes
            new Chart(document.getElementById('chartContratosMes'), {
                type: 'line',
                data: { labels: data.contratos_mes.map(i => i.mes), datasets: [{ label: 'Contratos', data: data.contratos_mes.map(i => i.total), borderColor: '#1A1953', backgroundColor: 'rgba(26,25,83,0.1)', tension: 0.3, fill: true }] },
                options: { responsive: true, maintainAspectRatio: true }
            });
            
            // Gráfica: Monto por mes
            new Chart(document.getElementById('chartMontoMes'), {
                type: 'bar',
                data: { labels: data.contratos_mes.map(i => i.mes), datasets: [{ label: 'Monto (USD)', data: data.contratos_mes.map(i => i.monto_total || 0), backgroundColor: '#FFD45A', borderRadius: 4 }] },
                options: { responsive: true, maintainAspectRatio: true, plugins: { tooltip: { callbacks: { label: (ctx) => '$' + new Intl.NumberFormat().format(ctx.raw) } } } }
            });
            
            // Gráfica: Propiedades por tipo
            new Chart(document.getElementById('chartPropiedadesTipo'), {
                type: 'doughnut',
                data: { labels: data.propiedades_tipo.map(i => i.tipo), datasets: [{ data: data.propiedades_tipo.map(i => i.total), backgroundColor: ['#1A1953', '#252477', '#FFD45A', '#E6B800', '#6B7280'] }] },
                options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'right' } } }
            });
            
            // Gráfica: Contratos por estado
            new Chart(document.getElementById('chartContratosEstado'), {
                type: 'bar',
                data: { labels: data.contratos_estado.map(i => i.estado), datasets: [{ label: 'Contratos', data: data.contratos_estado.map(i => i.total), backgroundColor: '#1A1953', borderRadius: 4 }] },
                options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false } } }
            });
            
            // Tabla: Top vendedores
            document.querySelector('#tablaVendedores tbody').innerHTML = (data.top_vendedores || []).map(v => `
                <tr><td>${v.nombre} ${v.apellido}</td><td>${v.total_ventas || 0}</td><td class="monto">$${new Intl.NumberFormat().format(v.monto_total || 0)}</td></tr>
            `).join('') || '<tr><td colspan="3">No hay datos</td></tr>';
            
            // Tabla: Propiedades destacadas
            document.querySelector('#tablaPropiedades tbody').innerHTML = (data.propiedades_top || []).map(p => `
                <tr><td>${p.titulo_anuncio}</td><td class="monto">$${new Intl.NumberFormat().format(p.precio_pedido)}</td><td>${p.vendedor_nombre} ${p.vendedor_apellido}</td></tr>
            `).join('') || '<tr><td colspan="3">No hay datos</td></tr>';
        }
    })
    .catch(error => console.error('Error:', error));
</script>
</body>
</html>