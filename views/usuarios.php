<?php
/**
 * GESTIÓN DE USUARIOS — Admin
 * PP Bienes Raíces — views/usuarios.php
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
    <title>Usuarios | PP Bienes Raíces</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/panel.css">
    <link rel="stylesheet" href="../assets/css/usuarios.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
</head>
<body>
<div class="panel-layout">
    <?php include 'layouts/sidebar.php'; ?>
    <div class="panel-main">
        <?php include 'layouts/headerpanel.php'; ?>
        <div class="panel-content">

            <!-- KPIs -->
            <div class="kpi-grid" id="kpiGrid" style="margin-bottom:6px;">
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon-navy"><i class="fas fa-users"></i></div>
                    <div class="kpi-data">
                        <div class="kpi-label">Total usuarios</div>
                        <div class="kpi-valor" id="kpiTotal">—</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon-gold"><i class="fas fa-check-circle"></i></div>
                    <div class="kpi-data">
                        <div class="kpi-label">Agentes verificados</div>
                        <div class="kpi-valor" id="kpiVerificados">—</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon-navy"><i class="fas fa-clock"></i></div>
                    <div class="kpi-data">
                        <div class="kpi-label">Pendientes doc.</div>
                        <div class="kpi-valor" id="kpiPendientes">—</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon-gold"><i class="fas fa-ban"></i></div>
                    <div class="kpi-data">
                        <div class="kpi-label">Suspendidos</div>
                        <div class="kpi-valor" id="kpiSuspendidos">—</div>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="usr-card">

                <!-- Head -->
                <div class="usr-card-head">
                    <div>
                        <span><i class="fas fa-users"></i> Todos los usuarios</span>
                        <span class="usr-contador" id="subtituloTabla">Cargando...</span>
                    </div>
                    <button class="usr-btn-nuevo" id="btnNuevoUsuario">
                        <i class="fas fa-plus"></i> Nuevo usuario
                    </button>
                </div>

                <!-- Toolbar filtros -->
                <div class="usr-toolbar">
                    <div class="usr-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="filtroBuscar" placeholder="Buscar por nombre, correo...">
                    </div>
                    <div class="usr-filters">
                        <select id="filtroRol">
                            <option value="">Todos los roles</option>
                            <option value="admin">Admin</option>
                            <option value="vendedor">Vendedor</option>
                        </select>
                        <select id="filtroEstado">
                            <option value="todos">Todos los estados</option>
                            <option value="activo">Activos</option>
                            <option value="inactivo">Suspendidos</option>
                        </select>
                        <select id="filtroVerificado">
                            <option value="todos">Verificación</option>
                            <option value="verificado">Verificados</option>
                            <option value="no_verificado">Sin verificar</option>
                        </select>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="usr-tabla-scroll">
                    <table class="usr-tabla">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Teléfono</th>
                                <th>Verificado</th>
                                <th>Estado</th>
                                <th>Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaBody">
                            <tr><td colspan="7" class="tabla-loading">
                                <div class="loading-spinner"></div> Cargando usuarios...
                            </td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <?php include 'layouts/footer.php'; ?>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Modal crear/editar -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal" id="modal">
        <div class="modal-header">
            <span class="modal-titulo" id="modalTitulo"><i class="fas fa-user-plus"></i> Nuevo usuario</span>
            <button class="modal-cerrar" id="modalCerrar"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form id="formUsuario" novalidate>
                <input type="hidden" id="usuarioId">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-user"></i> Nombre <span class="req">*</span></label>
                        <input type="text" class="form-input" id="fNombre" name="nombre" placeholder="Nombre">
                        <span class="form-error" id="eNombre"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-user"></i> Apellido <span class="req">*</span></label>
                        <input type="text" class="form-input" id="fApellido" name="apellido" placeholder="Apellido">
                        <span class="form-error" id="eApellido"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-envelope"></i> Correo electrónico <span class="req">*</span></label>
                    <input type="email" class="form-input" id="fCorreo" name="correo" placeholder="correo@ejemplo.com">
                    <span class="form-error" id="eCorreo"></span>
                </div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-lock"></i> Contraseña <span class="req" id="claveReq">*</span></label>
                        <input type="password" class="form-input" id="fClave" name="clave" placeholder="Mínimo 6 caracteres">
                        <span class="form-hint" id="claveHint"></span>
                        <span class="form-error" id="eClave"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-phone"></i> Teléfono</label>
                        <input type="text" class="form-input" id="fTelefono" name="telefono" placeholder="+503 0000-0000">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-shield-alt"></i> Rol <span class="req">*</span></label>
                    <select class="form-input" id="fRol" name="rol">
                        <option value="vendedor">Vendedor</option>
                        <option value="admin">Administrador</option>
                    </select>
                    <span class="form-error" id="eRol"></span>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-align-left"></i> Descripción personal</label>
                    <textarea class="form-input form-textarea" id="fDescripcion" name="descripcion" placeholder="Breve descripción..." rows="3"></textarea>
                </div>
                <div class="form-checks">
                    <label class="check-label">
                        <input type="checkbox" id="fVerificado" name="cuenta_verificada" class="check-input">
                        <span class="check-box"></span>
                        <i class="fas fa-check-circle" style="color:var(--gold-dark);margin-right:4px;font-size:.8rem;"></i>
                        Cuenta verificada
                    </label>
                    <label class="check-label" id="checkActivoWrap">
                        <input type="checkbox" id="fActivo" name="cuenta_activa" class="check-input" checked>
                        <span class="check-box"></span>
                        <i class="fas fa-toggle-on" style="color:var(--gold-dark);margin-right:4px;font-size:.8rem;"></i>
                        Cuenta activa
                    </label>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="usr-btn-cancel" id="btnCancelar"><i class="fas fa-times"></i> Cancelar</button>
            <button class="usr-btn-save" id="btnGuardar"><i class="fas fa-save"></i> Guardar</button>
        </div>
    </div>
</div>

<!-- Modal eliminar -->
<div class="modal-overlay" id="modalEliminarOverlay">
    <div class="modal modal-sm" id="modalEliminar">
        <div class="modal-header modal-header-danger">
            <span class="modal-titulo"><i class="fas fa-trash"></i> Eliminar usuario</span>
            <button class="modal-cerrar" id="modalEliminarCerrar"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="confirm-icon-flat"><i class="fas fa-exclamation-triangle"></i></div>
            <p class="confirm-msg">¿Eliminar a <strong id="nombreEliminar"></strong>?<br>
            <span style="font-size:.8rem;">Esta acción no se puede deshacer.</span></p>
            <input type="hidden" id="idEliminar">
        </div>
        <div class="modal-footer">
            <button class="usr-btn-cancel" id="btnCancelarEliminar"><i class="fas fa-times"></i> Cancelar</button>
            <button class="usr-btn-danger" id="btnConfirmarEliminar"><i class="fas fa-trash"></i> Sí, eliminar</button>
        </div>
    </div>
</div>

<div class="toast-wrap" id="toastWrap"></div>

<script>const CTRL_URL = '../controllers/UsuarioController.php';</script>
<script src="../assets/js/panel.js"></script>
<script src="../assets/js/usuarios.js"></script>
</body>
</html>