<?php
/**
 * REPORTES — Administrador
 * PP Bienes Raíces — views/reportes.php
 */
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes | PP Bienes Raíces</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/panel.css">
    <link rel="stylesheet" href="../assets/css/reportes.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="panel-layout">
    <?php include 'layouts/sidebar.php'; ?>
    <div class="panel-main">
        <?php include 'layouts/headerpanel.php'; ?>
        <div class="panel-content">
            <div class="reportes-wrap">

                <!-- KPIs — se renderizan desde JS -->
                <div class="admin-kpis" id="kpiContainer">
                    <?php for ($i = 0; $i < 6; $i++): ?>
                    <div class="kpi-card">
                        <div class="kpi-icon kpi-icon-navy"><i class="fas fa-spinner fa-spin"></i></div>
                        <div><div class="kpi-label">Cargando...</div><div class="kpi-valor">—</div></div>
                    </div>
                    <?php endfor; ?>
                </div>

                <!-- Gráficas fila 1 -->
                <div class="charts-row">
                    <div class="chart-card">
                        <div class="chart-card-head">
                            <i class="fas fa-chart-line"></i> Contratos por mes
                        </div>
                        <div class="chart-card-body">
                            <canvas id="chartContratosMes" height="220"></canvas>
                        </div>
                    </div>
                    <div class="chart-card">
                        <div class="chart-card-head">
                            <i class="fas fa-dollar-sign"></i> Monto total por mes
                        </div>
                        <div class="chart-card-body">
                            <canvas id="chartMontoMes" height="220"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Gráficas fila 2 -->
                <div class="charts-row">
                    <div class="chart-card">
                        <div class="chart-card-head">
                            <i class="fas fa-home"></i> Propiedades por tipo
                        </div>
                        <div class="chart-card-body">
                            <canvas id="chartPropiedadesTipo" height="220"></canvas>
                        </div>
                    </div>
                    <div class="chart-card">
                        <div class="chart-card-head">
                            <i class="fas fa-file-contract"></i> Contratos por estado
                        </div>
                        <div class="chart-card-body">
                            <canvas id="chartContratosEstado" height="220"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Tablas -->
                <div class="tables-row">
                    <div class="table-card">
                        <div class="table-card-head">
                            <i class="fas fa-trophy"></i> Top vendedores
                        </div>
                        <table class="data-table" id="tablaVendedores">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Vendedor</th>
                                    <th>Contratos</th>
                                    <th>Monto total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td colspan="4"><div class="loading-state"><div class="loading-spinner"></div>Cargando...</div></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-card">
                        <div class="table-card-head">
                            <i class="fas fa-fire"></i> Propiedades activas — mayor precio
                        </div>
                        <table class="data-table" id="tablaPropiedades">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Propiedad</th>
                                    <th>Tipo</th>
                                    <th>Precio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td colspan="4"><div class="loading-state"><div class="loading-spinner"></div>Cargando...</div></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
        <?php include 'layouts/footer.php'; ?>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script src="../assets/js/panel.js"></script>
<script>
const CHART_COLORS = {
    navy:    '#1A1953',
    navyMid: '#252477',
    navyDrk: '#0F0F2E',
    gold:    '#FFD45A',
    goldDrk: '#E6B800',
    muted:   '#6B7280',
    palette: ['#1A1953','#252477','#FFD45A','#E6B800','#6B7280','#3B82F6','#22C55E'],
};

const CHART_DEFAULTS = {
    responsive: true,
    maintainAspectRatio: true,
    plugins: { legend: { labels: { font: { family: 'Inter', size: 11 }, color: '#1C1C2E' } } },
};

fetch('../controllers/ReportesController.php')
    .then(r => r.json())
    .then(result => {
        if (!result.success) return;
        const d = result.data;
        const kpi = d.kpi;

        /* ── KPIs (6) ── */
        const montoFmt = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(kpi.monto_total || 0);
        document.getElementById('kpiContainer').innerHTML = `
            <div class="kpi-card">
                <div class="kpi-icon kpi-icon-navy"><i class="fas fa-home"></i></div>
                <div>
                    <div class="kpi-label">Propiedades</div>
                    <div class="kpi-valor">${kpi.total_propiedades || 0}</div>
                    <div class="kpi-sub">en total</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon kpi-icon-green"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="kpi-label">Activas</div>
                    <div class="kpi-valor">${kpi.propiedades_activas || 0}</div>
                    <div class="kpi-sub">publicadas</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon kpi-icon-gold"><i class="fas fa-tag"></i></div>
                <div>
                    <div class="kpi-label">Vendidas</div>
                    <div class="kpi-valor">${kpi.propiedades_vendidas || 0}</div>
                    <div class="kpi-sub">cerradas</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon kpi-icon-blue"><i class="fas fa-file-contract"></i></div>
                <div>
                    <div class="kpi-label">Contratos</div>
                    <div class="kpi-valor">${kpi.total_contratos || 0}</div>
                    <div class="kpi-sub">${kpi.contratos_mes || 0} este mes</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon kpi-icon-navy"><i class="fas fa-users"></i></div>
                <div>
                    <div class="kpi-label">Usuarios</div>
                    <div class="kpi-valor">${kpi.total_usuarios || 0}</div>
                    <div class="kpi-sub">activos</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon kpi-icon-gold"><i class="fas fa-dollar-sign"></i></div>
                <div>
                    <div class="kpi-label">Monto total</div>
                    <div class="kpi-valor" style="font-size:1rem;">${montoFmt}</div>
                    <div class="kpi-sub">contratos activos</div>
                </div>
            </div>
        `;

        /* ── Contratos por mes — línea ── */
        new Chart(document.getElementById('chartContratosMes'), {
            type: 'line',
            data: {
                labels: d.contratos_mes.map(i => i.mes),
                datasets: [{
                    label: 'Contratos',
                    data: d.contratos_mes.map(i => i.total),
                    borderColor: CHART_COLORS.navy,
                    backgroundColor: 'rgba(26,25,83,0.08)',
                    borderWidth: 2.5,
                    tension: 0.35,
                    fill: true,
                    pointBackgroundColor: CHART_COLORS.gold,
                    pointBorderColor: CHART_COLORS.navy,
                    pointRadius: 5,
                }],
            },
            options: {
                ...CHART_DEFAULTS,
                plugins: { ...CHART_DEFAULTS.plugins, legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: 'rgba(0,0,0,.05)' } },
                    x: { ticks: { font: { size: 11 } }, grid: { display: false } },
                },
            },
        });

        /* ── Monto por mes — barras ── */
        new Chart(document.getElementById('chartMontoMes'), {
            type: 'bar',
            data: {
                labels: d.contratos_mes.map(i => i.mes),
                datasets: [{
                    label: 'Monto (USD)',
                    data: d.contratos_mes.map(i => i.monto_total || 0),
                    backgroundColor: CHART_COLORS.navy,
                    borderRadius: 0,
                }],
            },
            options: {
                ...CHART_DEFAULTS,
                plugins: {
                    ...CHART_DEFAULTS.plugins,
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => '$' + new Intl.NumberFormat('en-US').format(ctx.raw) } },
                },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: v => '$' + new Intl.NumberFormat('en-US', { notation: 'compact' }).format(v), font: { size: 11 } }, grid: { color: 'rgba(0,0,0,.05)' } },
                    x: { ticks: { font: { size: 11 } }, grid: { display: false } },
                },
            },
        });

        /* ── Propiedades por tipo — dona ── */
        new Chart(document.getElementById('chartPropiedadesTipo'), {
            type: 'doughnut',
            data: {
                labels: d.propiedades_tipo.map(i => i.tipo),
                datasets: [{
                    data: d.propiedades_tipo.map(i => i.total),
                    backgroundColor: CHART_COLORS.palette,
                    borderWidth: 0,
                }],
            },
            options: {
                ...CHART_DEFAULTS,
                plugins: { ...CHART_DEFAULTS.plugins, legend: { position: 'right' } },
                cutout: '60%',
            },
        });

        /* ── Contratos por estado — barras horizontales ── */
        new Chart(document.getElementById('chartContratosEstado'), {
            type: 'bar',
            data: {
                labels: d.contratos_estado.map(i => i.estado),
                datasets: [{
                    label: 'Contratos',
                    data: d.contratos_estado.map(i => i.total),
                    backgroundColor: d.contratos_estado.map(i =>
                        i.estado === 'Firmado'  ? CHART_COLORS.gold :
                        i.estado === 'Aprobado' ? CHART_COLORS.navy :
                        i.estado === 'Rechazado' || i.estado === 'Anulado' ? '#EF4444' :
                        CHART_COLORS.navyMid
                    ),
                    borderRadius: 0,
                }],
            },
            options: {
                indexAxis: 'y',
                ...CHART_DEFAULTS,
                plugins: { ...CHART_DEFAULTS.plugins, legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: 'rgba(0,0,0,.05)' } },
                    y: { ticks: { font: { size: 11 } }, grid: { display: false } },
                },
            },
        });

        /* ── Top vendedores ── */
        document.querySelector('#tablaVendedores tbody').innerHTML =
            (d.top_vendedores || []).length
            ? d.top_vendedores.map((v, i) => `
                <tr>
                    <td><span class="td-rank">${i + 1}</span></td>
                    <td><strong>${esc(v.nombre)} ${esc(v.apellido)}</strong></td>
                    <td>${v.total_ventas || 0}</td>
                    <td class="monto">$${new Intl.NumberFormat('en-US').format(v.monto_total || 0)}</td>
                </tr>`).join('')
            : '<tr><td colspan="4" style="text-align:center;color:var(--muted);padding:24px;font-size:.82rem;">Sin datos disponibles</td></tr>';

        /* ── Propiedades top ── */
        document.querySelector('#tablaPropiedades tbody').innerHTML =
            (d.propiedades_top || []).length
            ? d.propiedades_top.map((p, i) => `
                <tr>
                    <td><span class="td-rank">${i + 1}</span></td>
                    <td>${esc(p.titulo_anuncio)}</td>
                    <td><span style="font-size:.7rem;font-weight:700;color:var(--muted);">${esc(p.tipo || '')}</span></td>
                    <td class="monto">$${new Intl.NumberFormat('en-US').format(p.precio_pedido)}</td>
                </tr>`).join('')
            : '<tr><td colspan="4" style="text-align:center;color:var(--muted);padding:24px;font-size:.82rem;">Sin datos disponibles</td></tr>';
    })
    .catch(err => console.error('Error cargando reportes:', err));

function esc(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
</body>
</html>
