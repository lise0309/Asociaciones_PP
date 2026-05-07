<?php
/**
 * PERFIL DE USUARIO
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
        'descripcion_personal' => '',
        'foto_perfil' => '',
        'cuenta_verificada' => 0,
        'fecha_registro' => date('Y-m-d')
    ];
}

// Obtener documentos del usuario
$documentos = $perfilModel->getDocumentosByUsuario($_SESSION['usuario_id']);

// Funciones auxiliares
function getDocIcon($tipo) {
    $icons = ['cedula' => '🪪', 'licencia' => '📜', 'nit' => '📑', 'otros' => '📄'];
    return $icons[$tipo] ?? '📄';
}

function getEstadoTexto($estado) {
    $textos = ['verified' => '✓ Verificada', 'pending' => '⏳ Pendiente', 'expiring' => '⚠️ Vence pronto'];
    return $textos[$estado] ?? $estado;
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
                <div class="perfil-header">
                    <div class="perfil-avatar" id="avatarContainer">
                        <?php if (!empty($usuario['foto_perfil'])): ?>
                            <img src="../<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" alt="Foto perfil" id="avatarImg">
                        <?php else: ?>
                            <span id="avatarTexto"><?php echo strtoupper(substr($usuario['nombre'] ?? 'U', 0, 1) . substr($usuario['apellido'] ?? '', 0, 1)); ?></span>
                        <?php endif; ?>
                        <button class="btn-cambiar-foto" id="btnCambiarFoto" title="Cambiar foto">📷</button>
                    </div>
                    <div class="perfil-info">
                        <h1 id="perfilNombre"><?php echo htmlspecialchars(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? '')); ?></h1>
                        <div>
                            <span class="perfil-badge"><?php echo $rol === 'admin' ? 'Administrador' : 'Agente certificado'; ?></span>
                            <span class="perfil-badge">APP</span>
                            <?php if ($usuario['cuenta_verificada']): ?>
                                <span class="perfil-badge verified">✓ Verificado</span>
                            <?php endif; ?>
                        </div>
                        <div class="perfil-location">
                            <span>📍 El Salvador</span>
                            <span>•</span>
                            <span>Desde <?php echo date('Y', strtotime($usuario['fecha_registro'] ?? 'now')); ?></span>
                        </div>
                    </div>
                </div>

                <div class="two-columns">
                    <!-- Columna Izquierda -->
                    <div>
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
                                <div class="info-row">
                                    <div class="info-label">BIOGRAFÍA</div>
                                    <div class="info-value" id="infoBiografia"><?php echo htmlspecialchars($usuario['descripcion_personal'] ?? 'Sin descripción'); ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Seguridad -->
                        <div class="perfil-card">
                            <div class="card-header">
                                <h3>🔒 Seguridad</h3>
                            </div>
                            <div class="card-body">
                                <div class="info-row">
                                    <div class="info-label">CONTRASEÑA</div>
                                    <div class="info-value">••••••••</div>
                                </div>
                                <button class="btn-primary" id="btnCambiarPasswordModal" style="margin-top: 10px;">
                                    Cambiar contraseña
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha -->
                    <div>
                        <!-- Documentos -->
                        <div class="perfil-card">
                            <div class="card-header">
                                <h3>📄 Documentos</h3>
                            </div>
                            <div class="card-body">
                                <?php if (empty($documentos)): ?>
                                    <p class="text-muted">No hay documentos subidos</p>
                                <?php else: ?>
                                    <?php foreach ($documentos as $doc): ?>
                                        <div class="document-item" data-doc-id="<?php echo $doc['id']; ?>">
                                            <div class="document-info">
                                                <div class="document-icon"><?php echo getDocIcon($doc['tipo']); ?></div>
                                                <div class="document-name"><?php echo htmlspecialchars($doc['nombre']); ?></div>
                                            </div>
                                            <div>
                                                <span class="document-status status-<?php echo $doc['estado']; ?>">
                                                    <?php echo getEstadoTexto($doc['estado']); ?>
                                                </span>
                                                <button class="btn-delete-doc" onclick="eliminarDocumento('<?php echo $doc['id']; ?>')">🗑️</button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <div class="upload-btn">
                                <button class="btn-outline" id="btnSubirDocumento">📎 Subir documento</button>
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

<!-- Modal Editar Perfil -->
<div class="modal-overlay" id="modalEditarPerfil">
    <div class="modal modal-perfil">
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
                <div class="form-group">
                    <label>BIOGRAFÍA</label>
                    <textarea class="form-control" id="editBiografia" rows="4" placeholder="Cuéntanos sobre ti..."><?php echo htmlspecialchars($usuario['descripcion_personal'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="btn-primary">Guardar cambios</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cambiar Contraseña -->
<div class="modal-overlay" id="modalCambiarPassword">
    <div class="modal modal-perfil">
        <div class="modal-header">
            <h3>🔒 Cambiar contraseña</h3>
            <button class="modal-close" id="closeCambiarPassword">×</button>
        </div>
        <div class="modal-body">
            <form id="formCambiarPasswordModal">
                <div class="form-group">
                    <label>CONTRASEÑA ACTUAL</label>
                    <input type="password" class="form-control" id="passwordActualModal" placeholder="Ingresa tu contraseña actual" required>
                </div>
                <div class="form-group">
                    <label>NUEVA CONTRASEÑA</label>
                    <input type="password" class="form-control" id="nuevaPasswordModal" placeholder="Mínimo 6 caracteres" required>
                </div>
                <div class="form-group">
                    <label>CONFIRMAR NUEVA CONTRASEÑA</label>
                    <input type="password" class="form-control" id="confirmarPasswordModal" placeholder="Confirma tu nueva contraseña" required>
                </div>
                <button type="submit" class="btn-primary">Actualizar contraseña</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Subir Documento -->
<div class="modal-overlay" id="modalSubirDocumento">
    <div class="modal modal-perfil">
        <div class="modal-header">
            <h3>📎 Subir documento</h3>
            <button class="modal-close" id="closeSubirDocumento">×</button>
        </div>
        <div class="modal-body">
            <form id="formSubirDocumento" enctype="multipart/form-data">
                <div class="form-group">
                    <label>TIPO DE DOCUMENTO</label>
                    <select class="form-control" id="docTipo" required>
                        <option value="">Seleccionar...</option>
                        <option value="cedula">Cédula de identidad</option>
                        <option value="licencia">Licencia inmobiliaria</option>
                        <option value="nit">NIT / Registro tributario</option>
                        <option value="otros">Otros</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>ARCHIVO (PDF, JPG, PNG)</label>
                    <input type="file" class="form-control" id="docArchivo" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
                <button type="submit" class="btn-primary">Subir documento</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cambiar Foto -->
<div class="modal-overlay" id="modalCambiarFoto">
    <div class="modal modal-perfil">
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