<?php
/**
 * PERFIL DE USUARIO - Versión Mejorada
 * PP Bienes Raíces — views/perfil.php
 */

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$titulo_pagina = 'Mi perfil';
$rol = $_SESSION['rol'] ?? 'vendedor';

require_once __DIR__ . '/../models/PerfilModel.php';
$perfilModel = new PerfilModel();
$usuario = $perfilModel->getUsuarioById($_SESSION['usuario_id']);

if (!$usuario) {
    $usuario = [
        'nombre' => $_SESSION['nombre'] ?? '',
        'apellido' => $_SESSION['apellido'] ?? '',
        'correo' => $_SESSION['correo'] ?? '',
        'telefono' => '',
        'foto_perfil' => '',
        'cuenta_verificada' => 0,
        'fecha_registro' => date('Y-m-d')
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil | PP Bienes Raíces</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/panel.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/perfil.css">
</head>
<body>

<div class="panel-layout">
    <?php include 'layouts/sidebar.php'; ?>

    <div class="panel-main">
        <?php include 'layouts/headerpanel.php'; ?>

        <div class="panel-content">
            <div class="perfil-container">

                <!-- Toast Messages -->
                <div id="toastMessages"></div>

                <!-- Header del perfil -->
               <!-- Header del perfil - Diseño horizontal -->
<div class="perfil-header">
    <div class="perfil-avatar" id="avatarContainer">
        <?php if (!empty($usuario['foto_perfil'])): ?>
            <img src="<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" alt="Foto perfil" id="avatarImg">
        <?php else: ?>
            <span id="avatarTexto"><?php echo strtoupper(substr($usuario['nombre'] ?? 'U', 0, 1) . substr($usuario['apellido'] ?? '', 0, 1)); ?></span>
        <?php endif; ?>
        <button class="btn-cambiar-foto" id="btnCambiarFoto" title="Cambiar foto">📷</button>
    </div>
    <div class="perfil-info">
        <h1 id="perfilNombre"><?php echo htmlspecialchars(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? '')); ?></h1>
        <div class="perfil-badges">
            <span class="perfil-badge"><?php echo $rol === 'admin' ? 'Administrador' : 'Vendedor'; ?></span>
            <?php if ($usuario['cuenta_verificada']): ?>
                <span class="perfil-badge verified">✓ Verificado</span>
            <?php endif; ?>
        </div>
        <div class="perfil-location">
            <span>📍 El Salvador</span>
            <span>•</span>
            <span>📅 Desde <?php echo date('Y', strtotime($usuario['fecha_registro'] ?? 'now')); ?></span>
        </div>
    </div>
</div>

                <!-- Información personal -->
                <div class="perfil-card">
                    <div class="card-header">
                        <h3>📋 Información personal</h3>
                        <button class="btn-edit" id="btnEditarPerfil">✏️ Editar</button>
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <div class="info-label">NOMBRE</div>
                            <div class="info-value" id="infoNombre"><?php echo htmlspecialchars($usuario['nombre'] ?? ''); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">APELLIDO</div>
                            <div class="info-value" id="infoApellido"><?php echo htmlspecialchars($usuario['apellido'] ?? ''); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">CORREO</div>
                            <div class="info-value" id="infoCorreo"><?php echo htmlspecialchars($usuario['correo'] ?? ''); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">TELÉFONO</div>
                            <div class="info-value" id="infoTelefono"><?php echo htmlspecialchars($usuario['telefono'] ?? 'No especificado'); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Seguridad - Botón pequeño -->
                <div class="security-section">
                    <button class="btn-security" id="btnCambiarPasswordModal">
                        🔒 Cambiar contraseña
                    </button>
                </div>

                <!-- Historial de Propiedades Vendidas -->
<div class="perfil-card">
    <div class="card-header">
        <h3>🏠 Propiedades Vendidas</h3>
    </div>
    <div class="card-body">
        <?php
        $propiedadesVendidas = $perfilModel->getPropiedadesVendidas($_SESSION['usuario_id']);
        $resumenVentas = $perfilModel->getResumenVentas($_SESSION['usuario_id']);
        ?>
        
        <!-- Resumen de ventas -->
        <div class="ventas-resumen">
            <div class="resumen-item">
                <span class="resumen-label">Total ventas</span>
                <span class="resumen-valor"><?php echo $resumenVentas['total_ventas'] ?? 0; ?></span>
            </div>
            <div class="resumen-item">
                <span class="resumen-label">Monto total</span>
                <span class="resumen-valor">$<?php echo number_format($resumenVentas['monto_total'] ?? 0, 2); ?></span>
            </div>
            <div class="resumen-item">
                <span class="resumen-label">Propiedades</span>
                <span class="resumen-valor"><?php echo $resumenVentas['propiedades_vendidas'] ?? 0; ?></span>
            </div>
        </div>
        
        <?php if (empty($propiedadesVendidas)): ?>
            <div class="text-muted" style="text-align: center; padding: 20px;">
                📭 No hay propiedades vendidas aún
            </div>
        <?php else: ?>
            <div class="tabla-responsive">
                <table class="tabla-ventas">
                    <thead>
                        <tr>
                            <th># Contrato</th>
                            <th>Propiedad</th>
                            <th>Comprador</th>
                            <th>Monto</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($propiedadesVendidas as $venta): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($venta['numero_contrato'] ?? substr($venta['contrato_id'], 0, 8)); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($venta['titulo_anuncio']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($venta['direccion_exacta'] . ', ' . $venta['municipio']); ?></small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($venta['nombre_comprador']); ?><br>
                                    <small>DUI: <?php echo htmlspecialchars($venta['dui_comprador']); ?></small>
                                </td>
                                <td class="monto-venta">$<?php echo number_format($venta['monto_acordado'], 2); ?> <?php echo $venta['moneda']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($venta['fecha_generacion'])); ?></td>
                                <td><span class="estado-venta estado-<?php echo strtolower($venta['estado']); ?>"><?php echo $venta['estado']; ?></span></td>
                                <td>
                                    <a href="../controllers/ContratoController.php?action=generar&id=<?php echo $venta['contrato_id']; ?>" 
                                       class="btn-ver-contrato" target="_blank">📄 Ver Contrato</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
            </div>
        </div>

        <?php include 'layouts/footer.php'; ?>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Modal Editar Perfil -->
<div class="modal-overlay" id="modalEditarPerfil">
    <div class="modal-perfil">
        <div class="modal-header">
            <h3>✏️ Editar perfil</h3>
            <button class="modal-close" id="closeEditarPerfil">×</button>
        </div>
        <div class="modal-body">
            <form id="formEditarPerfil">
                <div class="form-group">
                    <label>NOMBRE</label>
                    <input type="text" class="form-control" id="editNombre" value="<?php echo htmlspecialchars($usuario['nombre'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>APELLIDO</label>
                    <input type="text" class="form-control" id="editApellido" value="<?php echo htmlspecialchars($usuario['apellido'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>TELÉFONO</label>
                    <input type="tel" class="form-control" id="editTelefono" value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>">
                </div>
                <button type="submit" class="btn-primary">Guardar cambios</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cambiar Contraseña -->
<div class="modal-overlay" id="modalCambiarPassword">
    <div class="modal-perfil">
        <div class="modal-header">
            <h3>🔒 Cambiar contraseña</h3>
            <button class="modal-close" id="closeCambiarPassword">×</button>
        </div>
        <div class="modal-body">
            <form id="formCambiarPasswordModal">
                <div class="form-group">
                    <label>CONTRASEÑA ACTUAL</label>
                    <input type="password" class="form-control" id="passwordActualModal" required>
                </div>
                <div class="form-group">
                    <label>NUEVA CONTRASEÑA</label>
                    <input type="password" class="form-control" id="nuevaPasswordModal" required>
                </div>
                <div class="form-group">
                    <label>CONFIRMAR CONTRASEÑA</label>
                    <input type="password" class="form-control" id="confirmarPasswordModal" required>
                </div>
                <button type="submit" class="btn-primary">Actualizar contraseña</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cambiar Foto -->
<div class="modal-overlay" id="modalCambiarFoto">
    <div class="modal-perfil">
        <div class="modal-header">
            <h3>📷 Cambiar foto de perfil</h3>
            <button class="modal-close" id="closeCambiarFoto">×</button>
        </div>
        <div class="modal-body">
            <form id="formCambiarFoto" enctype="multipart/form-data">
                <div class="form-group">
                    <label>SELECCIONAR IMAGEN</label>
                    <input type="file" class="form-control" id="fotoArchivo" accept="image/jpeg,image/png,image/jpg" required>
                </div>
                <button type="submit" class="btn-primary">Subir foto</button>
            </form>
        </div>
    </div>
</div>

<script src="../assets/js/perfil.js"></script>
</body>
</html>