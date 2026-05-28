<?php
/**
 * DASHBOARD — Administrador
 * PP Bienes Raíces — views/dashboardadmin.php
 */
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php'); exit;
}

require_once __DIR__ . '/../config/database.php';
$db = Database::conectar();

// ── KPIs reales ──
$kpis = $db->query("
    SELECT
        (SELECT COUNT(*) FROM propiedades p
         JOIN opciones_sistema os ON os.id = p.estado_publicacion_id
         WHERE os.nombre_opcion = 'Activa') AS prop_activas,
        (SELECT COUNT(*) FROM usuarios WHERE cuenta_activa = 1) AS usuarios,
        (SELECT COUNT(*) FROM contratos) AS contratos,
        (SELECT COALESCE(SUM(monto_acordado),0) FROM contratos
         JOIN opciones_sistema os ON os.id = estado_contrato_id
         WHERE os.nombre_opcion IN ('Firmado','Aprobado')) AS monto_total
")->fetch(PDO::FETCH_ASSOC);

// ── Contratos pendientes (Enviado + En revisión) ──
$pendientes_contratos = (int)$db->query("
    SELECT COUNT(*) FROM contratos c
    JOIN opciones_sistema os ON os.id = c.estado_contrato_id
    WHERE os.nombre_opcion IN ('Enviado','En revisión')
")->fetchColumn();

// ── Propiedades pendientes aprobación ──
$pendientes_props = $db->query("
    SELECT p.id, p.titulo_anuncio, p.municipio, p.departamento,
           p.precio_pedido,
           CONCAT(u.nombre,' ',u.apellido) AS vendedor,
           os_tip.nombre_opcion AS tipo,
           os_neg.nombre_opcion AS negocio
    FROM propiedades p
    JOIN opciones_sistema os ON os.id = p.estado_publicacion_id
    LEFT JOIN opciones_sistema os_tip ON os_tip.id = p.tipo_inmueble_id
    LEFT JOIN opciones_sistema os_neg ON os_neg.id = p.tipo_negocio_id
    LEFT JOIN usuarios u ON u.id = p.vendedor_id
    WHERE os.nombre_opcion = 'Pendiente aprobación'
    ORDER BY p.fecha_actualizacion DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// ── Contratos recientes ──
$contratos_recientes = $db->query("
    SELECT c.id, c.nombre_comprador, c.monto_acordado, c.moneda,
           c.fecha_generacion,
           p.titulo_anuncio,
           os.nombre_opcion AS estado,
           CONCAT(u.nombre,' ',u.apellido) AS vendedor
    FROM contratos c
    JOIN propiedades p ON p.id = c.propiedad_id
    JOIN opciones_sistema os ON os.id = c.estado_contrato_id
    JOIN usuarios u ON u.id = c.vendedor_id
    ORDER BY c.fecha_generacion DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// ── Usuarios recientes ──
$usuarios_recientes = $db->query("
    SELECT id, nombre, apellido, correo, rol, cuenta_verificada, cuenta_activa, fecha_registro
    FROM usuarios
    ORDER BY fecha_registro DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

$fmt_monto = fn($n) => '$' . number_format($n >= 1000000 ? $n/1000000 : ($n >= 1000 ? $n/1000 : $n), 1) . ($n >= 1000000 ? 'M' : ($n >= 1000 ? 'K' : ''));
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

            <!-- KPIs -->
            <div class="kpi-grid" style="margin-bottom:6px;">
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon-navy"><i class="fas fa-home"></i></div>
                    <div class="kpi-data">
                        <div class="kpi-label">Propiedades activas</div>
                        <div class="kpi-valor"><?= (int)$kpis['prop_activas'] ?></div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon-gold"><i class="fas fa-users"></i></div>
                    <div class="kpi-data">
                        <div class="kpi-label">Usuarios activos</div>
                        <div class="kpi-valor"><?= (int)$kpis['usuarios'] ?></div>
                    </div>
                </div>
                <div class="kpi-card <?= $pendientes_contratos > 0 ? 'kpi-card-alerta' : '' ?>">
                    <div class="kpi-icon kpi-icon-navy"><i class="fas fa-file-contract"></i></div>
                    <div class="kpi-data">
                        <div class="kpi-label">Contratos<?= $pendientes_contratos > 0 ? ' · <span style="color:#FFD45A">'.$pendientes_contratos.' pend.</span>' : '' ?></div>
                        <div class="kpi-valor"><?= (int)$kpis['contratos'] ?></div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon-gold"><i class="fas fa-dollar-sign"></i></div>
                    <div class="kpi-data">
                        <div class="kpi-label">Valor firmado</div>
                        <div class="kpi-valor"><?= $fmt_monto($kpis['monto_total']) ?></div>
                    </div>
                </div>
            </div>

            <!-- Fila: usuarios + contratos -->
            <div class="dash-grid-2" style="margin-bottom:6px;">

                <!-- Usuarios recientes -->
                <div class="dash-card">
                    <div class="dash-card-head">
                        <span><i class="fas fa-users"></i> Usuarios recientes</span>
                        <a href="usuarios.php" class="dash-ver-todos">Ver todos <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="dash-card-body" style="padding:0;">
                        <?php if (empty($usuarios_recientes)): ?>
                            <div class="dash-empty"><i class="fas fa-users"></i><p>Sin usuarios aún</p></div>
                        <?php else: ?>
                        <?php foreach ($usuarios_recientes as $u): ?>
                        <div class="dash-user-item">
                            <div class="dash-avatar">
                                <?= strtoupper(substr($u['nombre'],0,1).substr($u['apellido'],0,1)) ?>
                            </div>
                            <div class="dash-user-info">
                                <div class="dash-user-nombre"><?= htmlspecialchars($u['nombre'].' '.$u['apellido']) ?></div>
                                <div class="dash-user-correo"><?= htmlspecialchars($u['correo']) ?></div>
                            </div>
                            <span class="dash-badge <?= $u['rol'] === 'admin' ? 'badge-navy' : 'badge-gold' ?>">
                                <?= $u['rol'] === 'admin' ? 'Admin' : 'Vendedor' ?>
                            </span>
                            <span class="dash-badge <?= $u['cuenta_activa'] ? 'badge-activo' : 'badge-inactivo' ?>">
                                <?= $u['cuenta_activa'] ? 'Activo' : 'Inactivo' ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Contratos recientes -->
                <div class="dash-card">
                    <div class="dash-card-head">
                        <span><i class="fas fa-file-contract"></i> Contratos recientes</span>
                        <a href="contratosadmin.php" class="dash-ver-todos">Ver todos <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="dash-card-body" style="padding:0;">
                        <?php if (empty($contratos_recientes)): ?>
                            <div class="dash-empty"><i class="fas fa-file-contract"></i><p>Sin contratos aún</p></div>
                        <?php else: ?>
                        <?php foreach ($contratos_recientes as $c): ?>
                        <div class="dash-contrato-item">
                            <div class="dash-contrato-num">#<?= strtoupper(substr($c['id'],0,8)) ?></div>
                            <div class="dash-contrato-info">
                                <div class="dash-contrato-prop"><?= htmlspecialchars($c['titulo_anuncio']) ?></div>
                                <div class="dash-contrato-meta">
                                    <i class="fas fa-user"></i> <?= htmlspecialchars($c['nombre_comprador']) ?>
                                    · <i class="fas fa-user-tie"></i> <?= htmlspecialchars($c['vendedor']) ?>
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

            <!-- Propiedades pendientes -->
            <div class="dash-card">
                <div class="dash-card-head">
                    <span><i class="fas fa-clock"></i> Propiedades pendientes de aprobación</span>
                    <a href="propiedades.php" class="dash-ver-todos">Ver todas <i class="fas fa-arrow-right"></i></a>
                </div>
                <?php if (empty($pendientes_props)): ?>
                    <div class="dash-empty" style="padding:32px;">
                        <i class="fas fa-check-circle" style="color:var(--gold-dark);"></i>
                        <p>No hay propiedades pendientes</p>
                    </div>
                <?php else: ?>
                <div class="tabla-scroll">
                    <table class="dash-tabla">
                        <thead>
                            <tr>
                                <th>Propiedad</th>
                                <th>Tipo</th>
                                <th>Precio</th>
                                <th>Modalidad</th>
                                <th>Vendedor</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendientes_props as $p): ?>
                            <tr>
                                <td>
                                    <div class="dash-prop-nombre"><?= htmlspecialchars($p['titulo_anuncio']) ?></div>
                                    <div class="dash-prop-loc"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($p['municipio'].', '.$p['departamento']) ?></div>
                                </td>
                                <td><span class="dash-tag"><?= htmlspecialchars($p['tipo'] ?? '—') ?></span></td>
                                <td class="dash-precio">$<?= number_format($p['precio_pedido'],0,'.',',') ?></td>
                                <td><span class="dash-tag"><?= htmlspecialchars($p['negocio'] ?? '—') ?></span></td>
                                <td><span class="dash-vendedor"><?= htmlspecialchars($p['vendedor'] ?? '—') ?></span></td>
                                <td>
                                    <div style="display:flex;gap:5px;">
                                        <a href="propiedades.php" class="dash-btn dash-btn-outline">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="propiedades.php" class="dash-btn dash-btn-primary">
                                            <i class="fas fa-check"></i> Aprobar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

        </div>
        <?php include 'layouts/footer.php'; ?>
    </div>
</div>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<script src="../assets/js/panel.js"></script>
</body>
</html>