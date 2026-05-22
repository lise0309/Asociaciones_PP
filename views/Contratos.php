<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: login.php'); exit; }
$rol = $_SESSION['rol'] ?? 'vendedor';
require_once __DIR__ . '/../config/database.php';
$db = Database::conectar();
if ($rol === 'admin') {
    $propiedades = $db->query("SELECT id, titulo_anuncio FROM propiedades ORDER BY titulo_anuncio")->fetchAll();
} else {
    $stmt = $db->prepare("SELECT id, titulo_anuncio FROM propiedades WHERE vendedor_id=? ORDER BY titulo_anuncio");
    $stmt->execute([$_SESSION['usuario_id']]);
    $propiedades = $stmt->fetchAll();
}
$tipos      = $db->query("SELECT id, nombre_opcion FROM opciones_sistema WHERE categoria='tipo_contrato' AND disponible=1")->fetchAll();
$plantillas = $db->query("SELECT id, nombre_plantilla FROM plantillas_contrato WHERE plantilla_activa=1 ORDER BY nombre_plantilla")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contratos | PP Bienes Raíces</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/panel.css">
    <link rel="stylesheet" href="../assets/css/contratos.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
</head>
<body>
<div class="panel-layout">
    <?php include 'layouts/sidebar.php'; ?>
    <div class="panel-main">
        <?php include 'layouts/headerpanel.php'; ?>
        <div class="panel-content">
            <div class="contratos-wrap">

                <!-- KPIs -->
                <div class="kpi-grid" style="margin-bottom:24px;">
                    <div class="kpi-card"><div class="kpi-icon kpi-icon-navy"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg></div><div class="kpi-data"><div class="kpi-label">Total</div><div class="kpi-valor" id="kpiTotal">—</div></div></div>
                    <div class="kpi-card"><div class="kpi-icon kpi-icon-gold"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd"/></svg></div><div class="kpi-data"><div class="kpi-label">Borradores</div><div class="kpi-valor" id="kpiBorrador">—</div></div></div>
                    <div class="kpi-card"><div class="kpi-icon kpi-icon-green"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg></div><div class="kpi-data"><div class="kpi-label">Firmados</div><div class="kpi-valor" id="kpiFirmado">—</div></div></div>
                    <div class="kpi-card"><div class="kpi-icon kpi-icon-navy"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.798 7.45c.512-.67 1.135-.95 1.702-.95s1.19.28 1.702.95a.75.75 0 001.192-.91C12.637 5.55 11.596 5 10.5 5s-2.137.55-2.894 1.54A5.205 5.205 0 006.83 10c0 1.333.39 2.52 1.776 3.46a.75.75 0 00.848-1.244C8.498 11.73 8.33 10.95 8.33 10c0-.85.2-1.65.468-2.55z" clip-rule="evenodd"/></svg></div><div class="kpi-data"><div class="kpi-label">Monto total</div><div class="kpi-valor" id="kpiMonto">—</div></div></div>
                </div>

                <div class="contratos-layout">
                    <!-- Formulario -->
                    <div class="contrato-form-card">
                        <div class="card-head">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
                            Nuevo contrato
                        </div>
                        <div class="card-body">
                            <?php if (empty($plantillas)): ?>
                                <div class="alert-info">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/></svg>
                                    <?= $rol==='admin' ? 'No hay plantillas. <a href="Plantillas.php">Sube una plantilla →</a>' : 'El admin aún no ha subido plantillas.' ?>
                                </div>
                            <?php else: ?>
                            <form id="formContrato">
                                <div class="fg">
                                    <label>PROPIEDAD <span class="req">*</span></label>
                                    <select name="propiedad_id" required>
                                        <option value="">Seleccionar...</option>
                                        <?php foreach ($propiedades as $p): ?>
                                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['titulo_anuncio']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="fg">
                                    <label>PLANTILLA <span class="req">*</span></label>
                                    <select name="plantilla_id" required>
                                        <option value="">Seleccionar...</option>
                                        <?php foreach ($plantillas as $pl): ?>
                                        <option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['nombre_plantilla']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="fg">
                                    <label>TIPO DE CONTRATO <span class="req">*</span></label>
                                    <select name="tipo_contrato_id" required>
                                        <option value="">Seleccionar...</option>
                                        <?php foreach ($tipos as $t): ?>
                                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nombre_opcion']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="fr2">
                                    <div class="fg"><label>NOMBRE COMPRADOR <span class="req">*</span></label><input type="text" name="nombre_comprador" required></div>
                                    <div class="fg"><label>CORREO <span class="req">*</span></label><input type="email" name="correo_comprador" required></div>
                                </div>
                                <div class="fr2">
                                    <div class="fg"><label>DUI <span class="req">*</span></label><input type="text" name="dui_comprador" placeholder="12345678-9" required></div>
                                    <div class="fg"><label>MONTO ACORDADO <span class="req">*</span></label><input type="number" name="monto_acordado" step="0.01" min="0" required></div>
                                </div>
                                <div class="fr2">
                                    <div class="fg"><label>MONEDA</label><select name="moneda"><option value="USD">USD</option><option value="EUR">EUR</option></select></div>
                                    <div class="fg"><label>FECHA VENCIMIENTO</label><input type="date" name="fecha_vencimiento"></div>
                                </div>
                                <button type="submit" class="btn-crear" id="btnCrear">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 00-1.5 0v2.5h-2.5a.75.75 0 000 1.5h2.5v2.5a.75.75 0 001.5 0v-2.5h2.5a.75.75 0 000-1.5h-2.5v-2.5z" clip-rule="evenodd"/></svg>
                                    Crear contrato
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Listado -->
                    <div class="contrato-list-card">
                        <div class="card-head">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z" clip-rule="evenodd"/></svg>
                            Mis contratos
                            <button class="btn-refresh" id="btnRefresh" title="Actualizar">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.989a.75.75 0 00-.75.75v4.242a.75.75 0 001.5 0v-2.43l.31.31a7 7 0 0011.712-3.138.75.75 0 00-1.449-.39zm1.23-3.723a.75.75 0 00.219-.53V2.929a.75.75 0 00-1.5 0V5.36l-.31-.31A7 7 0 003.239 8.188a.75.75 0 101.448.389A5.5 5.5 0 0113.89 6.11l.311.31h-2.432a.75.75 0 000 1.5h4.243a.75.75 0 00.53-.219z" clip-rule="evenodd"/></svg>
                            </button>
                        </div>
                        <div id="listaContratos" class="lista-body">
                            <div class="loading-state"><div class="loading-spinner"></div>Cargando...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'layouts/footer.php'; ?>
    </div>
</div>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="toast-wrap" id="toastWrap"></div>
<script src="../assets/js/panel.js"></script>
<script src="../assets/js/contratos.js"></script>
</body>
</html>