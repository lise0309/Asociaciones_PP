<?php
/**
 * GESTIÓN DE PROPIEDADES — Admin
 * PP Bienes Raíces — views/propiedades.php
 */

session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
  header('Location: login.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Propiedades | PP Bienes Raíces</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/panel.css">
  <link rel="stylesheet" href="../assets/css/propiedades.css">
  <link rel="stylesheet" href="../assets/css/footer.css">
</head>
<body>

<div class="panel-layout">

  <?php include 'layouts/sidebar.php'; ?>

  <div class="panel-main">

    <?php include 'layouts/headerpanel.php'; ?>

    <div class="panel-content">

      <!-- ════ KPIs ════ -->
      <div class="kpi-grid">
        <div class="kpi-card">
          <div class="kpi-icon kpi-icon-navy">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
            </svg>
          </div>
          <div class="kpi-data">
            <div class="kpi-label">Total propiedades</div>
            <div class="kpi-valor" id="kpiTotal">—</div>
          </div>
        </div>
        <div class="kpi-card">
          <div class="kpi-icon kpi-icon-green">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="kpi-data">
            <div class="kpi-label">Activas</div>
            <div class="kpi-valor" id="kpiActivas">—</div>
          </div>
        </div>
        <div class="kpi-card">
          <div class="kpi-icon kpi-icon-gold">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="kpi-data">
            <div class="kpi-label">Pendientes</div>
            <div class="kpi-valor" id="kpiPendientes">—</div>
          </div>
        </div>
        <div class="kpi-card">
          <div class="kpi-icon kpi-icon-gold">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="kpi-data">
            <div class="kpi-label">Destacadas</div>
            <div class="kpi-valor" id="kpiDestacadas">—</div>
          </div>
        </div>
      </div>

      <!-- ════ TABLA ════ -->
      <div class="card">
        <div class="card-header">
          <div>
            <div class="card-title">Todas las propiedades</div>
            <div class="card-subtitle" id="subtituloTabla">Cargando...</div>
          </div>
        </div>

        <!-- Filtros -->
        <div class="tabla-toolbar">
          <div class="tabla-search">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/>
            </svg>
            <input type="text" id="filtroBuscar" placeholder="Buscar por título, vendedor, ubicación...">
          </div>
          <div class="tabla-filters">
            <select class="tabla-select" id="filtroTipoInmueble">
              <option value="">Todos los tipos</option>
              <option value="Casa">Casa</option>
              <option value="Apartamento">Apartamento</option>
              <option value="Local comercial">Local comercial</option>
              <option value="Terreno">Terreno</option>
              <option value="Bodega">Bodega</option>
            </select>
            <select class="tabla-select" id="filtroNegocio">
              <option value="">Tipo negocio</option>
              <option value="Venta">Venta</option>
              <option value="Alquiler">Alquiler</option>
              <option value="Alquiler con opción a compra">Alq. con opción</option>
            </select>
            <select class="tabla-select" id="filtroEstado">
              <option value="">Todos los estados</option>
              <option value="Activa">Activa</option>
              <option value="Pendiente aprobación">Pendiente</option>
              <option value="Pausada">Pausada</option>
              <option value="Borrador">Borrador</option>
              <option value="Rechazada">Rechazada</option>
            </select>
          </div>
        </div>

        <!-- Tabla -->
        <div class="tabla-wrap">
          <table class="tabla">
            <thead>
              <tr>
                <th>Propiedad</th>
                <th>Tipo</th>
                <th>Negocio</th>
                <th>Vendedor</th>
                <th>Precio</th>
                <th>Ubicación</th>
                <th>Estado</th>
                <th>⭐</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="tablaBody">
              <tr>
                <td colspan="9" class="tabla-loading">
                  <div class="loading-spinner"></div>
                  Cargando propiedades...
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>

    </div><!-- /panel-content -->

    <?php include 'layouts/footer.php'; ?>

  </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ════ MODAL VER DETALLE ════ -->
<div class="modal-overlay" id="modalDetalleOverlay">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h3 class="modal-titulo" id="detalleTitulo">Detalle de propiedad</h3>
      <button class="modal-cerrar" id="modalDetalleCerrar">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
          <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
        </svg>
      </button>
    </div>
    <div class="modal-body" id="detalleBody">
      <div class="loading-spinner"></div>
    </div>
    <div class="modal-footer">
      <button class="btn-panel btn-panel-outline" id="btnCerrarDetalle">Cerrar</button>
    </div>
  </div>
</div>

<!-- ════ MODAL CAMBIAR ESTADO ════ -->
<div class="modal-overlay" id="modalEstadoOverlay">
  <div class="modal modal-sm">
    <div class="modal-header">
      <h3 class="modal-titulo">Cambiar estado</h3>
      <button class="modal-cerrar" id="modalEstadoCerrar">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
          <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
        </svg>
      </button>
    </div>
    <div class="modal-body">
      <p class="confirm-msg" style="text-align:left;margin-bottom:16px;">
        Cambiar estado de <strong id="propiedadEstadoNombre"></strong>:
      </p>
      <select class="form-input" id="selectNuevoEstado">
        <option value="">Seleccionar estado...</option>
      </select>
      <input type="hidden" id="propiedadEstadoId">
    </div>
    <div class="modal-footer">
      <button class="btn-panel btn-panel-outline" id="btnCancelarEstado">Cancelar</button>
      <button class="btn-panel btn-panel-primary" id="btnConfirmarEstado">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/>
        </svg>
        Cambiar estado
      </button>
    </div>
  </div>
</div>

<!-- ════ MODAL CONFIRMAR ELIMINAR ════ -->
<div class="modal-overlay" id="modalEliminarOverlay">
  <div class="modal modal-sm">
    <div class="modal-header">
      <h3 class="modal-titulo">Eliminar propiedad</h3>
      <button class="modal-cerrar" id="modalEliminarCerrar">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
          <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
        </svg>
      </button>
    </div>
    <div class="modal-body">
      <div class="confirm-icon">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
        </svg>
      </div>
      <p class="confirm-msg">
        ¿Eliminar <strong id="nombreEliminar"></strong>? Esta acción no se puede deshacer.
      </p>
      <input type="hidden" id="idEliminar">
    </div>
    <div class="modal-footer">
      <button class="btn-panel btn-panel-outline" id="btnCancelarEliminar">Cancelar</button>
      <button class="btn-panel btn-panel-danger-solid" id="btnConfirmarEliminar">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4z" clip-rule="evenodd"/>
        </svg>
        Sí, eliminar
      </button>
    </div>
  </div>
</div>

<!-- Toast -->
<div class="toast-wrap" id="toastWrap"></div>

<script src="../assets/js/panel.js"></script>
<script src="../assets/js/propiedades.js"></script>
</body>
</html>