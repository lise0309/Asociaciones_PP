<?php
/**
 * PERFIL — PP Bienes Raíces
 * views/perfil.php
 */
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: login.php'); exit; }

$rol = $_SESSION['rol'] ?? 'vendedor';
require_once __DIR__ . '/../models/PerfilModel.php';
$perfilModel = new PerfilModel();
$usuario = $perfilModel->getUsuarioById($_SESSION['usuario_id']);

if (!$usuario) {
    $usuario = [
        'nombre'            => $_SESSION['nombre']   ?? '',
        'apellido'          => $_SESSION['apellido'] ?? '',
        'correo'            => $_SESSION['correo']   ?? '',
        'telefono'          => '',
        'foto_perfil'       => '',
        'cuenta_verificada' => 0,
        'fecha_registro'    => date('Y-m-d'),
    ];
}

$propiedadesVendidas = $perfilModel->getPropiedadesVendidas($_SESSION['usuario_id']);
$resumenVentas       = $perfilModel->getResumenVentas($_SESSION['usuario_id']);

$iniciales = strtoupper(
    substr($usuario['nombre']   ?? 'U', 0, 1) .
    substr($usuario['apellido'] ?? '',  0, 1)
);
$nombreCompleto = htmlspecialchars(trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? '')));
$anioRegistro   = date('Y', strtotime($usuario['fecha_registro'] ?? 'now'));
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/panel.css">
    <link rel="stylesheet" href="../assets/css/perfil.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
</head>
<body>
<div class="panel-layout">
    <?php include 'layouts/sidebar.php'; ?>
    <div class="panel-main">
        <?php include 'layouts/headerpanel.php'; ?>
        <div class="panel-content">
            <div class="perfil-container">

                <!-- HEADER -->
                <div class="perfil-header">
                    <div class="perfil-avatar" id="avatarContainer">
                        <?php if (!empty($usuario['foto_perfil'])): ?>
                            <img src="<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="Foto perfil" id="avatarImg">
                        <?php else: ?>
                            <span id="avatarTexto"><?= $iniciales ?></span>
                        <?php endif; ?>
                        <button class="btn-cambiar-foto" id="btnCambiarFoto" title="Cambiar foto">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                    <div class="perfil-info">
                        <h1 id="perfilNombre"><?= $nombreCompleto ?></h1>
                        <div class="perfil-badges">
                            <span class="perfil-badge">
                                <i class="fas fa-<?= $rol === 'admin' ? 'shield-alt' : 'user-tie' ?>"></i>
                                <?= $rol === 'admin' ? 'Administrador' : 'Vendedor' ?>
                            </span>
                            <?php if ($usuario['cuenta_verificada']): ?>
                                <span class="perfil-badge verified">
                                    <i class="fas fa-check-circle"></i> Verificado
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="perfil-location">
                            <span><i class="fas fa-map-marker-alt"></i> El Salvador</span>
                            <span class="sep">·</span>
                            <span><i class="fas fa-calendar-alt"></i> Desde <?= $anioRegistro ?></span>
                            <?php if (!empty($usuario['correo'])): ?>
                            <span class="sep">·</span>
                            <span><i class="fas fa-envelope"></i> <?= htmlspecialchars($usuario['correo']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <button class="btn-editar-header" id="btnEditarPerfil">
                        <i class="fas fa-edit"></i> Editar perfil
                    </button>
                </div>

                <!-- GRID INFO + SEGURIDAD -->
                <div class="perfil-grid">

                    <!-- Info personal -->
                    <div class="perfil-card">
                        <div class="card-head-flat">
                            <i class="fas fa-id-card"></i> Información personal
                        </div>
                        <div class="card-body-flat">
                            <div class="info-row">
                                <div class="info-label"><i class="fas fa-user"></i> Nombre</div>
                                <div class="info-value" id="infoNombre"><?= htmlspecialchars($usuario['nombre'] ?? '—') ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label"><i class="fas fa-user"></i> Apellido</div>
                                <div class="info-value" id="infoApellido"><?= htmlspecialchars($usuario['apellido'] ?? '—') ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label"><i class="fas fa-envelope"></i> Correo</div>
                                <div class="info-value" id="infoCorreo"><?= htmlspecialchars($usuario['correo'] ?? '—') ?></div>
                            </div>
                            <div class="info-row last">
                                <div class="info-label"><i class="fas fa-phone"></i> Teléfono</div>
                                <div class="info-value" id="infoTelefono"><?= htmlspecialchars($usuario['telefono'] ?: 'No especificado') ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Seguridad -->
                    <div class="perfil-card">
                        <div class="card-head-flat">
                            <i class="fas fa-lock"></i> Seguridad
                        </div>
                        <div class="card-body-flat">
                            <div class="security-item">
                                <div class="security-item-info">
                                    <i class="fas fa-key"></i>
                                    <div>
                                        <div class="security-item-title">Contraseña</div>
                                        <div class="security-item-sub">Última actualización desconocida</div>
                                    </div>
                                </div>
                                <button class="btn-security" id="btnCambiarPasswordModal">
                                    <i class="fas fa-edit"></i> Cambiar
                                </button>
                            </div>
                            <div class="security-item">
                                <div class="security-item-info">
                                    <i class="fas fa-check-circle" style="color:var(--gold)"></i>
                                    <div>
                                        <div class="security-item-title">Estado de cuenta</div>
                                        <div class="security-item-sub">
                                            <?= $usuario['cuenta_verificada'] ? 'Verificada' : 'Sin verificar' ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="security-item last">
                                <div class="security-item-info">
                                    <i class="fas fa-calendar"></i>
                                    <div>
                                        <div class="security-item-title">Miembro desde</div>
                                        <div class="security-item-sub">
                                            <?= date('d \d\e F, Y', strtotime($usuario['fecha_registro'] ?? 'now')) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- RESUMEN VENTAS (solo vendedor) -->
                <?php if ($rol === 'vendedor'): ?>
                <div class="ventas-kpis">
                    <div class="vkpi">
                        <div class="vkpi-icon"><i class="fas fa-file-contract"></i></div>
                        <div class="vkpi-data">
                            <div class="vkpi-val"><?= (int)$resumenVentas['total_ventas'] ?></div>
                            <div class="vkpi-label">Contratos firmados</div>
                        </div>
                    </div>
                    <div class="vkpi">
                        <div class="vkpi-icon"><i class="fas fa-dollar-sign"></i></div>
                        <div class="vkpi-data">
                            <div class="vkpi-val">$<?= number_format((float)$resumenVentas['monto_total'], 0, '.', ',') ?></div>
                            <div class="vkpi-label">Monto total vendido</div>
                        </div>
                    </div>
                    <div class="vkpi">
                        <div class="vkpi-icon"><i class="fas fa-home"></i></div>
                        <div class="vkpi-data">
                            <div class="vkpi-val"><?= (int)$resumenVentas['propiedades_vendidas'] ?></div>
                            <div class="vkpi-label">Propiedades vendidas</div>
                        </div>
                    </div>
                </div>

                <!-- TABLA VENTAS -->
                <div class="perfil-card">
                    <div class="card-head-flat">
                        <i class="fas fa-history"></i> Historial de ventas
                    </div>
                    <div class="card-body-flat" style="padding:0;">
                        <?php if (empty($propiedadesVendidas)): ?>
                            <div class="empty-ventas">
                                <i class="fas fa-inbox"></i>
                                <p>No hay propiedades vendidas aún</p>
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
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($propiedadesVendidas as $v): ?>
                                    <tr>
                                        <td><span class="td-num">#<?= strtoupper(substr($v['contrato_id'], 0, 8)) ?></span></td>
                                        <td>
                                            <div class="td-prop-nombre"><?= htmlspecialchars($v['titulo_anuncio']) ?></div>
                                            <div class="td-prop-loc"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($v['municipio'] . ', ' . $v['departamento']) ?></div>
                                        </td>
                                        <td>
                                            <div class="td-comprador"><?= htmlspecialchars($v['nombre_comprador']) ?></div>
                                            <div class="td-dui">DUI: <?= htmlspecialchars($v['dui_comprador']) ?></div>
                                        </td>
                                        <td class="td-monto">$<?= number_format((float)$v['monto_acordado'], 0, '.', ',') ?> <span class="td-moneda"><?= htmlspecialchars($v['moneda']) ?></span></td>
                                        <td class="td-fecha"><?= date('d/m/Y', strtotime($v['fecha_generacion'])) ?></td>
                                        <td><span class="estado-venta"><?= htmlspecialchars($v['estado']) ?></span></td>
                                        <td>
                                            <a href="../controllers/generarcontratocontroller.php?id=<?= $v['contrato_id'] ?>" class="btn-ver-contrato" target="_blank">
                                                <i class="fas fa-download"></i> Contrato
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
        <?php include 'layouts/footer.php'; ?>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="toast-wrap" id="toastWrap"></div>

<!-- Modal editar perfil -->
<div class="modal-pf-overlay" id="modalEditarPerfil">
    <div class="modal-pf-box">
        <div class="modal-pf-head">
            <span><i class="fas fa-edit"></i> Editar perfil</span>
            <button onclick="document.getElementById('modalEditarPerfil').style.display='none'"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-pf-body">
            <form id="formEditarPerfil">
                <div class="fg-pf">
                    <label><i class="fas fa-user"></i> Nombre</label>
                    <input type="text" id="editNombre" value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>" required>
                </div>
                <div class="fg-pf">
                    <label><i class="fas fa-user"></i> Apellido</label>
                    <input type="text" id="editApellido" value="<?= htmlspecialchars($usuario['apellido'] ?? '') ?>" required>
                </div>
                <div class="fg-pf">
                    <label><i class="fas fa-phone"></i> Teléfono</label>
                    <input type="tel" id="editTelefono" value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>">
                </div>
                <button type="submit" class="btn-pf-submit">
                    <i class="fas fa-save"></i> Guardar cambios
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Modal cambiar contraseña -->
<div class="modal-pf-overlay" id="modalCambiarPassword">
    <div class="modal-pf-box">
        <div class="modal-pf-head">
            <span><i class="fas fa-lock"></i> Cambiar contraseña</span>
            <button onclick="document.getElementById('modalCambiarPassword').style.display='none'"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-pf-body">
            <form id="formCambiarPasswordModal">
                <div class="fg-pf">
                    <label><i class="fas fa-lock"></i> Contraseña actual</label>
                    <input type="password" id="passwordActualModal" required>
                </div>
                <div class="fg-pf">
                    <label><i class="fas fa-key"></i> Nueva contraseña</label>
                    <input type="password" id="nuevaPasswordModal" required>
                </div>
                <div class="fg-pf">
                    <label><i class="fas fa-key"></i> Confirmar contraseña</label>
                    <input type="password" id="confirmarPasswordModal" required>
                </div>
                <button type="submit" class="btn-pf-submit">
                    <i class="fas fa-save"></i> Actualizar contraseña
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Modal cambiar foto -->
<div class="modal-pf-overlay" id="modalCambiarFoto">
    <div class="modal-pf-box">
        <div class="modal-pf-head">
            <span><i class="fas fa-camera"></i> Cambiar foto</span>
            <button onclick="document.getElementById('modalCambiarFoto').style.display='none'"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-pf-body">
            <form id="formCambiarFoto" enctype="multipart/form-data">
                <div class="fg-pf">
                    <label><i class="fas fa-image"></i> Seleccionar imagen</label>
                    <input type="file" id="fotoArchivo" accept="image/jpeg,image/png,image/jpg" required>
                </div>
                <button type="submit" class="btn-pf-submit">
                    <i class="fas fa-upload"></i> Subir foto
                </button>
            </form>
        </div>
    </div>
</div>

<script src="../assets/js/panel.js"></script>
<script src="../assets/js/perfil.js"></script>
</body>
</html>