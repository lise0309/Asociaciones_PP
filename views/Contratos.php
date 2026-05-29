<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: login.php'); exit; }
$rol = $_SESSION['rol'] ?? 'vendedor';
require_once __DIR__ . '/../config/database.php';
$db = Database::conectar();
// Obtener el ID del estado "Vendida"
$estadoVendida = $db->query("SELECT id FROM opciones_sistema WHERE categoria='estado_publicacion' AND nombre_opcion='Vendida' LIMIT 1")->fetchColumn();

if ($rol === 'admin') {
    // Excluir propiedades vendidas
    $sql = "SELECT id, titulo_anuncio FROM propiedades";
    if ($estadoVendida) {
        $sql .= " WHERE estado_publicacion_id != $estadoVendida OR estado_publicacion_id IS NULL";
    }
    $sql .= " ORDER BY titulo_anuncio";
    $propiedades = $db->query($sql)->fetchAll();
} else {
    // Excluir propiedades vendidas del vendedor
    $sql = "SELECT id, titulo_anuncio FROM propiedades WHERE vendedor_id = ?";
    if ($estadoVendida) {
        $sql .= " AND (estado_publicacion_id != $estadoVendida OR estado_publicacion_id IS NULL)";
    }
    $sql .= " ORDER BY titulo_anuncio";
    $stmt = $db->prepare($sql);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
                <div class="kpi-grid" style="margin-bottom:6px;">
                    <div class="kpi-card">
                        <div class="kpi-icon kpi-icon-navy"><i class="fas fa-file-contract"></i></div>
                        <div class="kpi-data"><div class="kpi-label">Total</div><div class="kpi-valor" id="kpiTotal">—</div></div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon kpi-icon-gold"><i class="fas fa-clock"></i></div>
                        <div class="kpi-data"><div class="kpi-label">Borradores</div><div class="kpi-valor" id="kpiBorrador">—</div></div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon kpi-icon-green"><i class="fas fa-check-double"></i></div>
                        <div class="kpi-data"><div class="kpi-label">Aprobados</div><div class="kpi-valor" id="kpiFirmado">—</div></div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon kpi-icon-navy"><i class="fas fa-dollar-sign"></i></div>
                        <div class="kpi-data"><div class="kpi-label">Monto total</div><div class="kpi-valor" id="kpiMonto">—</div></div>
                    </div>
                </div>

                <div class="contratos-layout">
                    <!-- Formulario -->
                    <div class="contrato-form-card">
                        <div class="card-head">
                            <i class="fas fa-plus-circle"></i> Nuevo contrato
                        </div>
                        <div class="card-body">
                            <?php if (empty($plantillas)): ?>
                                <div class="alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <?= $rol==='admin' ? 'No hay plantillas. <a href="Plantillas.php">Sube una plantilla →</a>' : 'El admin aún no ha subido plantillas.' ?>
                                </div>
                            <?php else: ?>
                            <form id="formContrato">
                                <div class="fg">
                                    <label><i class="fas fa-home"></i> Propiedad <span class="req">*</span></label>
                                    <select name="propiedad_id" required>
                                        <option value="">Seleccionar...</option>
                                        <?php foreach ($propiedades as $p): ?>
                                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['titulo_anuncio']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="fg">
                                    <label><i class="fas fa-copy"></i> Plantilla <span class="req">*</span></label>
                                    <select name="plantilla_id" required>
                                        <option value="">Seleccionar...</option>
                                        <?php foreach ($plantillas as $pl): ?>
                                        <option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['nombre_plantilla']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="fg">
                                    <label><i class="fas fa-file-alt"></i> Tipo de contrato <span class="req">*</span></label>
                                    <select name="tipo_contrato_id" required>
                                        <option value="">Seleccionar...</option>
                                        <?php foreach ($tipos as $t): ?>
                                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nombre_opcion']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="fr2">
                                    <div class="fg"><label><i class="fas fa-user"></i> Comprador <span class="req">*</span></label><input type="text" name="nombre_comprador" required></div>
                                    <div class="fg"><label><i class="fas fa-envelope"></i> Correo <span class="req">*</span></label><input type="email" name="correo_comprador" required></div>
                                </div>
                                <div class="fr2">
                                    <div class="fg"><label><i class="fas fa-id-card"></i> DUI <span class="req">*</span></label><input type="text" name="dui_comprador" placeholder="12345678-9" required></div>
                                    <div class="fg"><label><i class="fas fa-dollar-sign"></i> Monto <span class="req">*</span></label><input type="number" name="monto_acordado" step="0.01" min="0" required></div>
                                </div>
                                <div class="fr2">
                                    <div class="fg"><label><i class="fas fa-coins"></i> Moneda</label><select name="moneda"><option value="USD">USD</option><option value="EUR">EUR</option></select></div>
                                    <div class="fg"><label><i class="fas fa-calendar"></i> Vencimiento</label><input type="date" name="fecha_vencimiento"></div>
                                </div>
                                <button type="submit" class="btn-crear" id="btnCrear">
                                    <i class="fas fa-plus"></i> Crear contrato
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Listado -->
                    <div class="contrato-list-card">
                        <div class="card-head">
                            <i class="fas fa-list"></i>
                            <?= $rol === 'admin' ? 'Todos los contratos' : 'Mis contratos' ?>
                            <button class="btn-refresh" id="btnRefresh" title="Actualizar">
                                <i class="fas fa-sync-alt"></i>
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

<!-- Modal rechazo (solo visible para admin) -->
<div class="modal-rechazo-overlay" id="modalRechazo">
    <div class="modal-rechazo-box">
        <div class="modal-rechazo-head">
            <span><i class="fas fa-ban"></i> Rechazar contrato</span>
            <button onclick="cerrarModalRechazo()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-rechazo-body">
            <label class="modal-rechazo-label"><i class="fas fa-comment-alt"></i> Motivo (opcional)</label>
            <textarea id="motivoRechazo" rows="3" placeholder="Explica al vendedor por qué se rechaza..."></textarea>
        </div>
        <div class="modal-rechazo-footer">
            <button class="modal-rechazo-cancel" onclick="cerrarModalRechazo()">Cancelar</button>
            <button class="modal-rechazo-confirm" onclick="confirmarRechazo()"><i class="fas fa-ban"></i> Rechazar</button>
        </div>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="toast-wrap" id="toastWrap"></div>

<script>
    // Usar var para evitar conflicto con const en contratos.js
    var MODO = 'contratos';
    var ROL  = '<?= $rol ?>';
</script>
<script src="../assets/js/panel.js"></script>
<script src="../assets/js/contratos.js"></script>
</body>
</html>