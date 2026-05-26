<?php
/**
 * GESTIÓN DE PROPIEDADES — Admin
 * PP Bienes Raíces — views/propiedades.php
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
    <title>Propiedades | PP Bienes Raíces</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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

            <!-- KPIs -->
            <div class="kpi-grid" style="margin-bottom:6px;">
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon-navy"><i class="fas fa-home"></i></div>
                    <div class="kpi-data"><div class="kpi-label">Total</div><div class="kpi-valor" id="kpiTotal">—</div></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon-gold"><i class="fas fa-check-circle"></i></div>
                    <div class="kpi-data"><div class="kpi-label">Activas</div><div class="kpi-valor" id="kpiActivas">—</div></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon-navy"><i class="fas fa-clock"></i></div>
                    <div class="kpi-data"><div class="kpi-label">Pendientes</div><div class="kpi-valor" id="kpiPendientes">—</div></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon kpi-icon-gold"><i class="fas fa-star"></i></div>
                    <div class="kpi-data"><div class="kpi-label">Destacadas</div><div class="kpi-valor" id="kpiDestacadas">—</div></div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="prop-card">

                <!-- Filtros flat -->
                <div class="prop-toolbar">
                    <div class="prop-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="filtroBuscar" placeholder="Buscar título, vendedor, ubicación...">
                    </div>
                    <div class="prop-filters">
                        <select id="filtroTipoInmueble">
                            <option value="">Todos los tipos</option>
                            <option value="Casa">Casa</option>
                            <option value="Apartamento">Apartamento</option>
                            <option value="Local comercial">Local comercial</option>
                            <option value="Terreno">Terreno</option>
                            <option value="Bodega">Bodega</option>
                        </select>
                        <select id="filtroNegocio">
                            <option value="">Tipo negocio</option>
                            <option value="Venta">Venta</option>
                            <option value="Alquiler">Alquiler</option>
                            <option value="Alquiler con opción a compra">Alq. con opción</option>
                        </select>
                        <select id="filtroEstado">
                            <option value="">Todos los estados</option>
                            <option value="Activa">Activa</option>
                            <option value="Pendiente aprobación">Pendiente</option>
                            <option value="Pausada">Pausada</option>
                            <option value="Borrador">Borrador</option>
                            <option value="Rechazada">Rechazada</option>
                        </select>
                        <button class="btn-filtrar-prop" id="btnFiltrar">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <button class="btn-limpiar-prop" id="btnLimpiar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Head tabla -->
                <div class="prop-table-head">
                    <span><i class="fas fa-list"></i> Todas las propiedades</span>
                    <span class="prop-contador" id="subtituloTabla">Cargando...</span>
                    <button class="btn-refresh-prop" id="btnRefresh" title="Actualizar">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>

                <div class="tabla-scroll">
                    <table class="prop-tabla">
                        <thead>
                            <tr>
                                <th>Propiedad</th>
                                <th>Tipo</th>
                                <th>Negocio</th>
                                <th>Vendedor</th>
                                <th>Precio</th>
                                <th>Ubicación</th>
                                <th>Estado</th>
                                <th><i class="fas fa-star"></i></th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaBody">
                            <tr><td colspan="9" class="tabla-loading">
                                <div class="loading-spinner"></div> Cargando propiedades...
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

<!-- Modal ver detalle -->
<div class="modal-prop-overlay" id="modalDetalleOverlay">
    <div class="modal-prop modal-prop-lg">
        <div class="modal-prop-head">
            <span><i class="fas fa-home"></i> <span id="detalleTitulo">Detalle de propiedad</span></span>
            <button id="modalDetalleCerrar"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-prop-body" id="detalleBody">
            <div class="loading-spinner"></div>
        </div>
        <div class="modal-prop-footer">
            <button class="btn-prop-cancel" id="btnCerrarDetalle">
                <i class="fas fa-times"></i> Cerrar
            </button>
        </div>
    </div>
</div>

<!-- Modal cambiar estado -->
<div class="modal-prop-overlay" id="modalEstadoOverlay">
    <div class="modal-prop modal-prop-sm">
        <div class="modal-prop-head">
            <span><i class="fas fa-exchange-alt"></i> Cambiar estado</span>
            <button id="modalEstadoCerrar"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-prop-body">
            <div class="modal-prop-label">Propiedad</div>
            <div class="modal-prop-nombre" id="propiedadEstadoNombre"></div>
            <div class="modal-prop-label" style="margin-top:14px;">Nuevo estado</div>
            <select class="prop-select" id="selectNuevoEstado">
                <option value="">Seleccionar estado...</option>
            </select>
            <input type="hidden" id="propiedadEstadoId">
        </div>
        <div class="modal-prop-footer">
            <button class="btn-prop-cancel" id="btnCancelarEstado"><i class="fas fa-times"></i> Cancelar</button>
            <button class="btn-prop-confirm" id="btnConfirmarEstado"><i class="fas fa-check"></i> Aplicar</button>
        </div>
    </div>
</div>

<!-- Modal eliminar -->
<div class="modal-prop-overlay" id="modalEliminarOverlay">
    <div class="modal-prop modal-prop-sm">
        <div class="modal-prop-head modal-prop-head-danger">
            <span><i class="fas fa-trash"></i> Eliminar propiedad</span>
            <button id="modalEliminarCerrar"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-prop-body">
            <div class="confirm-icon-flat">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="confirm-msg-flat">
                ¿Eliminar <strong id="nombreEliminar"></strong>?<br>
                <span>Esta acción no se puede deshacer.</span>
            </div>
            <input type="hidden" id="idEliminar">
        </div>
        <div class="modal-prop-footer">
            <button class="btn-prop-cancel" id="btnCancelarEliminar"><i class="fas fa-times"></i> Cancelar</button>
            <button class="btn-prop-danger" id="btnConfirmarEliminar"><i class="fas fa-trash"></i> Eliminar</button>
        </div>
    </div>
</div>

<div class="toast-wrap" id="toastWrap"></div>

<script src="../assets/js/panel.js"></script>
<script src="../assets/js/propiedades.js"></script>
</body>
</html>