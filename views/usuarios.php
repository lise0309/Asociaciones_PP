<?php
/**
 * GESTIÓN DE USUARIOS — Admin
 * PP Bienes Raíces — views/usuarios.php
 */

session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
  header('Location: login.php');
  exit;
}

$titulo_pagina = 'Gestión de usuarios';
$breadcrumb = [
  ['label' => 'Dashboard', 'url' => 'dashboardadmin.php'],
  ['label' => 'Usuarios'],
];
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
  <link rel="stylesheet" href="../assets/css/panel.css">
  <link rel="stylesheet" href="../assets/css/footer.css">
  <link rel="stylesheet" href="../assets/css/usuarios.css">
</head>
<body>

<div class="panel-layout">

  <?php include 'layouts/sidebar.php'; ?>

  <div class="panel-main">

    <?php include 'layouts/headerpanel.php'; ?>

    <div class="panel-content">

      <!-- ════ KPIs ════ -->
      <div class="kpi-grid" id="kpiGrid">
        <div class="kpi-card">
          <div class="kpi-icon kpi-icon-navy">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M7 8a3 3 0 100-6 3 3 0 000 6zM14.5 9a2.5 2.5 0 100-5 2.5 2.5 0 000 5zM1.615 16.428a1.224 1.224 0 01-.569-1.175 6.002 6.002 0 0111.908 0c.058.467-.172.92-.57 1.174A9.953 9.953 0 017 18a9.953 9.953 0 01-5.385-1.572zM14.5 16h-.106c.07-.297.088-.611.048-.933a7.47 7.47 0 00-1.588-3.755 4.502 4.502 0 015.874 2.575c.092.341-.051.703-.345.878A9.969 9.969 0 0114.5 16z"/></svg>
          </div>
          <div class="kpi-data">
            <div class="kpi-label">Total usuarios</div>
            <div class="kpi-valor" id="kpiTotal">—</div>
          </div>
        </div>
        <div class="kpi-card">
          <div class="kpi-icon kpi-icon-green">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>
          </div>
          <div class="kpi-data">
            <div class="kpi-label">Agentes verificados</div>
            <div class="kpi-valor" id="kpiVerificados">—</div>
          </div>
        </div>
        <div class="kpi-card">
          <div class="kpi-icon kpi-icon-gold">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd"/></svg>
          </div>
          <div class="kpi-data">
            <div class="kpi-label">Pendientes doc.</div>
            <div class="kpi-valor" id="kpiPendientes">—</div>
          </div>
        </div>
        <div class="kpi-card">
          <div class="kpi-icon kpi-icon-orange">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M13.5 4.938a7 7 0 11-9.006 1.737c.202-.257.59-.218.793.039.278.352.594.672.943.954.332.269.786-.049.773-.476a5.977 5.977 0 01.572-2.759 6.026 6.026 0 012.486-2.665c.247-.14.55-.016.677.238A6.967 6.967 0 0113.5 4.938zM14 12a4 4 0 01-4 4c-1.913 0-3.52-1.398-3.91-3.182-.093-.429.44-.643.814-.413a4.043 4.043 0 001.601.564c.303.038.531-.24.51-.544a5.975 5.975 0 011.315-4.192.447.447 0 01.431-.16A4.001 4.001 0 0114 12z" clip-rule="evenodd"/></svg>
          </div>
          <div class="kpi-data">
            <div class="kpi-label">Suspendidos</div>
            <div class="kpi-valor" id="kpiSuspendidos">—</div>
          </div>
        </div>
      </div>

      <!-- ════ TABLA ════ -->
      <div class="card">
        <div class="card-header">
          <div>
            <div class="card-title">Todos los usuarios</div>
            <div class="card-subtitle" id="subtituloTabla">Cargando...</div>
          </div>
          <button class="btn-panel btn-panel-gold" id="btnNuevoUsuario">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z"/></svg>
            Nuevo usuario
          </button>
        </div>

        <!-- Toolbar filtros -->
        <div class="tabla-toolbar">
          <div class="tabla-search">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/></svg>
            <input type="text" id="filtroBuscar" placeholder="Buscar por nombre, correo...">
          </div>
          <div class="tabla-filters">
            <select class="tabla-select" id="filtroRol">
              <option value="">Todos los roles</option>
              <option value="admin">Admin</option>
              <option value="vendedor">Vendedor</option>
            </select>
            <select class="tabla-select" id="filtroEstado">
              <option value="todos">Todos los estados</option>
              <option value="activo">Activos</option>
              <option value="inactivo">Suspendidos</option>
            </select>
            <select class="tabla-select" id="filtroVerificado">
              <option value="todos">Verificación</option>
              <option value="verificado">Verificados</option>
              <option value="no_verificado">Sin verificar</option>
            </select>
          </div>
        </div>

        <!-- Tabla -->
        <div class="tabla-wrap">
          <table class="tabla">
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
              <tr>
                <td colspan="7" class="tabla-loading">
                  <div class="loading-spinner"></div>
                  Cargando usuarios...
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>

    </div>
    <?php include 'layouts/footer.php'; ?>

  </div>
</div>

<!-- Overlay sidebar móvil -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ════ MODAL CREAR / EDITAR ════ -->
<div class="modal-overlay" id="modalOverlay">
  <div class="modal" id="modal">
    <div class="modal-header">
      <h3 class="modal-titulo" id="modalTitulo">Nuevo usuario</h3>
      <button class="modal-cerrar" id="modalCerrar">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <form id="formUsuario" novalidate>
        <input type="hidden" id="usuarioId">

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Nombre <span class="req">*</span></label>
            <input type="text" class="form-input" id="fNombre" name="nombre" placeholder="Nombre">
            <span class="form-error" id="eNombre"></span>
          </div>
          <div class="form-group">
            <label class="form-label">Apellido <span class="req">*</span></label>
            <input type="text" class="form-input" id="fApellido" name="apellido" placeholder="Apellido">
            <span class="form-error" id="eApellido"></span>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Correo electrónico <span class="req">*</span></label>
          <input type="email" class="form-input" id="fCorreo" name="correo" placeholder="correo@ejemplo.com">
          <span class="form-error" id="eCorreo"></span>
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Contraseña <span class="req" id="claveReq">*</span></label>
            <input type="password" class="form-input" id="fClave" name="clave" placeholder="Mínimo 6 caracteres">
            <span class="form-hint" id="claveHint"></span>
            <span class="form-error" id="eClave"></span>
          </div>
          <div class="form-group">
            <label class="form-label">Teléfono</label>
            <input type="text" class="form-input" id="fTelefono" name="telefono" placeholder="+503 0000-0000">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Rol <span class="req">*</span></label>
          <select class="form-input" id="fRol" name="rol">
            <option value="vendedor">Vendedor</option>
            <option value="admin">Administrador</option>
          </select>
          <span class="form-error" id="eRol"></span>
        </div>

        <div class="form-group">
          <label class="form-label">Descripción personal</label>
          <textarea class="form-input form-textarea" id="fDescripcion" name="descripcion" placeholder="Breve descripción del usuario..." rows="3"></textarea>
        </div>

        <div class="form-checks">
          <label class="check-label">
            <input type="checkbox" id="fVerificado" name="cuenta_verificada" class="check-input">
            <span class="check-box"></span>
            Cuenta verificada
          </label>
          <label class="check-label" id="checkActivoWrap">
            <input type="checkbox" id="fActivo" name="cuenta_activa" class="check-input" checked>
            <span class="check-box"></span>
            Cuenta activa
          </label>
        </div>

      </form>
    </div>
    <div class="modal-footer">
      <button class="btn-panel btn-panel-outline" id="btnCancelar">Cancelar</button>
      <button class="btn-panel btn-panel-primary" id="btnGuardar">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>
        Guardar
      </button>
    </div>
  </div>
</div>

<!-- ════ MODAL CONFIRMAR ELIMINAR ════ -->
<div class="modal-overlay" id="modalEliminarOverlay">
  <div class="modal modal-sm" id="modalEliminar">
    <div class="modal-header">
      <h3 class="modal-titulo">Eliminar usuario</h3>
      <button class="modal-cerrar" id="modalEliminarCerrar">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <div class="confirm-icon">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
      </div>
      <p class="confirm-msg">¿Estás seguro de que quieres eliminar a <strong id="nombreEliminar"></strong>? Esta acción no se puede deshacer.</p>
      <input type="hidden" id="idEliminar">
    </div>
    <div class="modal-footer">
      <button class="btn-panel btn-panel-outline" id="btnCancelarEliminar">Cancelar</button>
      <button class="btn-panel btn-panel-danger-solid" id="btnConfirmarEliminar">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd"/></svg>
        Sí, eliminar
      </button>
    </div>
  </div>
</div>

<!-- Toast notificaciones -->
<div class="toast-wrap" id="toastWrap"></div>

<script>
  const CTRL_URL = '../controllers/UsuarioController.php';
</script>
<script src="../assets/js/panel.js"></script>
<script src="../assets/js/usuarios.js"></script>
</body>
</html>