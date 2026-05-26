<?php
/**
 * DASHBOARD — Vendedor
 * PP Bienes Raíces — views/dashboardvendedor.php
 */
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'vendedor') {
    header('Location: login.php'); exit;
}

require_once __DIR__ . '/../config/database.php';
$db  = Database::conectar();
$uid = $_SESSION['usuario_id'];

// ── KPIs reales del vendedor ──
$kpis = $db->prepare("
    SELECT
        COUNT(*) AS total_props,
        SUM(CASE WHEN os.nombre_opcion = 'Activa' THEN 1 ELSE 0 END) AS activas,
        SUM(CASE WHEN os.nombre_opcion = 'Pendiente aprobación' THEN 1 ELSE 0 END) AS pendientes,
        SUM(CASE WHEN os.nombre_opcion = 'Vendida' THEN 1 ELSE 0 END) AS vendidas
    FROM propiedades p
    JOIN opciones_sistema os ON os.id = p.estado_publicacion_id
    WHERE p.vendedor_id = ?
");
$kpis->execute([$uid]);
$kpis = $kpis->fetch(PDO::FETCH_ASSOC);

// Contratos activos del vendedor
$contratos_activos = (int)$db->prepare("
    SELECT COUNT(*) FROM contratos c
    JOIN opciones_sistema os ON os.id = c.estado_contrato_id
    WHERE c.vendedor_id = ? AND os.nombre_opcion NOT IN ('Anulado','Rechazado')
")->execute([$uid]) ? $db->prepare("
    SELECT COUNT(*) FROM contratos c
    JOIN opciones_sistema os ON os.id = c.estado_contrato_id
    WHERE c.vendedor_id = ? AND os.nombre_opcion NOT IN ('Anulado','Rechazado')
") : null;

$stmt_ca = $db->prepare("
    SELECT COUNT(*) FROM contratos c
    JOIN opciones_sistema os ON os.id = c.estado_contrato_id
    WHERE c.vendedor_id = ? AND os.nombre_opcion NOT IN ('Anulado','Rechazado')
");
$stmt_ca->execute([$uid]);
$contratos_activos = (int)$stmt_ca->fetchColumn();

// Mis propiedades recientes
$mis_props = $db->prepare("
    SELECT p.id, p.titulo_anuncio, p.precio_pedido, p.municipio,
           os_tip.nombre_opcion AS tipo, os_est.nombre_opcion AS estado
    FROM propiedades p
    LEFT JOIN opciones_sistema os_tip ON os_tip.id = p.tipo_inmueble_id
    LEFT JOIN opciones_sistema os_est ON os_est.id = p.estado_publicacion_id
    WHERE p.vendedor_id = ?
    ORDER BY p.fecha_actualizacion DESC
    LIMIT 5
");
$mis_props->execute([$uid]);
$mis_props = $mis_props->fetchAll(PDO::FETCH_ASSOC);

// Mis contratos recientes
$mis_contratos = $db->prepare("
    SELECT c.id, c.nombre_comprador, c.monto_acordado, c.fecha_generacion,
           p.titulo_anuncio, os.nombre_opcion AS estado
    FROM contratos c
    JOIN propiedades p ON p.id = c.propiedad_id
    JOIN opciones_sistema os ON os.id = c.estado_contrato_id
    WHERE c.vendedor_id = ?
    ORDER BY c.fecha_generacion DESC
    LIMIT 5
");
$mis_contratos->execute([$uid]);
$mis_contratos = $mis_contratos->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | PP Bienes Raíces</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/panel.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
</head>
<body>
<div class="panel-layout">
    <?php include 'layouts/sidebar.php'; ?>
    <div class="panel-main">
        <?php include 'layouts/headerpanel.php'; ?>
        <div class="panel-content">

            <!-- Bienvenida -->
            <div class="dash-bienvenida">
                <div class="dash-bienvenida-texto">
                    <h2>Bienvenido, <em><?= htmlspecialchars($_SESSION['nombre'] ?? '') ?></em></h2>
                    <p><i class="fas fa-chart-line" style="color:var(--gold);margin-right:5px;"></i>Aquí tienes un resumen de tu actividad.</p>
                </div>
                <a href="propiedad_form.php" class="dash-btn-nueva">
                    <i class="fas fa-plus"></i> Nueva propiedad
                </a>
            </div>

            <!-- KPIs -->
            <div class="kpi-grid" style="margin-bottom:6px;">
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon-navy"><i class="fas fa-home"></i></div>
                    <div class="kpi-data">
                        <div class="kpi-label">Mis propiedades</div>
                        <div class="kpi-valor"><?= (int)$kpis['total_props'] ?></div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon-gold"><i class="fas fa-check-circle"></i></div>
                    <div class="kpi-data">
                        <div class="kpi-label">Activas</div>
                        <div class="kpi-valor"><?= (int)$kpis['activas'] ?></div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon-navy"><i class="fas fa-file-contract"></i></div>
                    <div class="kpi-data">
                        <div class="kpi-label">Contratos activos</div>
                        <div class="kpi-valor"><?= $contratos_activos ?></div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon-gold"><i class="fas fa-handshake"></i></div>
                    <div class="kpi-data">
                        <div class="kpi-label">Vendidas</div>
                        <div class="kpi-valor"><?= (int)$kpis['vendidas'] ?></div>
                    </div>
                </div>
            </div>

            <!-- Fila: mis propiedades + mis contratos -->
            <div class="dash-grid-2">

                <!-- Mis propiedades -->
                <div class="dash-card">
                    <div class="dash-card-head">
                        <span><i class="fas fa-home"></i> Mis propiedades</span>
                        <a href="Mis_Propiedades.php" class="dash-ver-todos">Ver todas <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="dash-card-body" style="padding:0;">
                        <?php if (empty($mis_props)): ?>
                            <div class="dash-empty">
                                <i class="fas fa-home"></i>
                                <p>No tienes propiedades aún</p>
                                <a href="propiedad_form.php" class="dash-btn dash-btn-primary" style="margin-top:8px;">
                                    <i class="fas fa-plus"></i> Crear primera propiedad
                                </a>
                            </div>
                        <?php else: ?>
                        <?php foreach ($mis_props as $p): ?>
                        <div class="dash-prop-item">
                            <div class="dash-prop-ico"><i class="fas fa-home"></i></div>
                            <div class="dash-prop-data">
                                <div class="dash-prop-nombre"><?= htmlspecialchars($p['titulo_anuncio']) ?></div>
                                <div class="dash-prop-loc">
                                    <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($p['municipio'] ?? '—') ?>
                                    · <span class="dash-tag"><?= htmlspecialchars($p['tipo'] ?? '—') ?></span>
                                </div>
                            </div>
                            <div style="text-align:right;flex-shrink:0;">
                                <div class="dash-precio">$<?= number_format($p['precio_pedido'],0,'.',',') ?></div>
                                <span class="dash-badge <?= $p['estado'] === 'Activa' ? 'badge-activo' : 'badge-navy' ?>">
                                    <?= htmlspecialchars($p['estado'] ?? '—') ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Mis contratos -->
                <div class="dash-card">
                    <div class="dash-card-head">
                        <span><i class="fas fa-file-contract"></i> Mis contratos</span>
                        <a href="contratos.php" class="dash-ver-todos">Ver todos <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="dash-card-body" style="padding:0;">
                        <?php if (empty($mis_contratos)): ?>
                            <div class="dash-empty">
                                <i class="fas fa-file-contract"></i>
                                <p>No tienes contratos aún</p>
                            </div>
                        <?php else: ?>
                        <?php foreach ($mis_contratos as $c): ?>
                        <div class="dash-contrato-item">
                            <div class="dash-contrato-num">#<?= strtoupper(substr($c['id'],0,8)) ?></div>
                            <div class="dash-contrato-info">
                                <div class="dash-contrato-prop"><?= htmlspecialchars($c['titulo_anuncio']) ?></div>
                                <div class="dash-contrato-meta">
                                    <i class="fas fa-user"></i> <?= htmlspecialchars($c['nombre_comprador']) ?>
                                </div>
                            </div>
                            <div style="text-align:right;flex-shrink:0;">
                                <div class="dash-monto">$<?= number_format($c['monto_acordado'],0,'.',',') ?></div>
                                <span class="dash-badge badge-navy"><?= htmlspecialchars($c['estado']) ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

        </div>
        <?php include 'layouts/footer.php'; ?>
    </div>
</div>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<script src="../assets/js/panel.js"></script>
</body>
</html>