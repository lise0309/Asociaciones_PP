<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$titulo_pagina = 'Gestión de Contratos';
$rol = $_SESSION['rol'] ?? 'vendedor';

require_once __DIR__ . '/../models/ContratoModel.php';
require_once __DIR__ . '/../config/database.php';

$model = new ContratoModel();
$db = Database::conectar();

// Obtener propiedades
if ($rol === 'admin') {
    $propiedades = $db->query("SELECT id, titulo_anuncio FROM propiedades")->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $db->prepare("SELECT id, titulo_anuncio FROM propiedades WHERE vendedor_id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $propiedades = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener tipos de contrato
$tipos = $db->query("SELECT id, nombre_opcion FROM opciones_sistema WHERE categoria = 'tipo_contrato' AND disponible = 1")->fetchAll(PDO::FETCH_ASSOC);

$mensaje = $_SESSION['mensaje'] ?? '';
$error = $_SESSION['error_mensaje'] ?? '';
unset($_SESSION['mensaje'], $_SESSION['error_mensaje']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Contratos | PP Bienes Raíces</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/panel.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/contrato.css">
</head>
<body>

<div class="panel-layout">
    <?php include 'layouts/sidebar.php'; ?>

    <div class="panel-main">
        <?php include 'layouts/headerpanel.php'; ?>

        <div class="panel-content">
            <div class="contrato-container">

                <?php if ($mensaje): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="contrato-layout">
                    <!-- Formulario de creación -->
                    <div class="contrato-form card">
                        <div class="card-header">
                            <h3>📝 Nuevo Contrato</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="../controllers/ContratoController.php">
                                <input type="hidden" name="accion" value="crear">
                                
                                <div class="form-group">
                                    <label>PROPIEDAD *</label>
                                    <select name="propiedad_id" required>
                                        <option value="">Seleccionar...</option>
                                        <?php foreach ($propiedades as $p): ?>
                                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['titulo_anuncio']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>TIPO DE CONTRATO *</label>
                                    <select name="tipo_contrato_id" required>
                                        <option value="">Seleccionar...</option>
                                        <?php foreach ($tipos as $t): ?>
                                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nombre_opcion']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-row-2">
                                    <div class="form-group">
                                        <label>NOMBRE DEL COMPRADOR *</label>
                                        <input type="text" name="nombre_comprador" required>
                                    </div>
                                    <div class="form-group">
                                        <label>CORREO *</label>
                                        <input type="email" name="correo_comprador" required>
                                    </div>
                                </div>
                                
                                <div class="form-row-2">
                                    <div class="form-group">
                                        <label>DUI *</label>
                                        <input type="text" name="dui_comprador" required placeholder="12345678-9">
                                    </div>
                                    <div class="form-group">
                                        <label>MONTO ACORDADO *</label>
                                        <input type="number" name="monto_acordado" step="0.01" required>
                                    </div>
                                </div>
                                
                                <div class="form-row-2">
                                    <div class="form-group">
                                        <label>MONEDA</label>
                                        <select name="moneda">
                                            <option value="USD">USD</option>
                                            <option value="EUR">EUR</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>FORMA DE PAGO</label>
                                        <select name="forma_pago">
                                            <option value="Contado">Contado</option>
                                            <option value="Financiamiento">Financiamiento</option>
                                        </select>
                                    </div>
                                    
                                </div>
                                
                                <button type="submit" class="btn-panel btn-panel-primary">✅ Crear Contrato</button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Listado de contratos -->
                    <div class="contrato-list card">
                        <div class="card-header">
                            <h3>📄 Mis Contratos</h3>
                            <button class="btn-panel btn-panel-outline btn-panel-sm" id="refreshContratos">Actualizar</button>
                        </div>
                        <div class="card-body">
                            <div id="contratosContainer">
                                <div class="loading">Cargando contratos...</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <?php include 'layouts/footer.php'; ?>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script src="../assets/js/contrato.js"></script>
</body>
</html>