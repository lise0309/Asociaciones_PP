<?php
/**
 * BITÁCORA — Administrador
 * PP Bienes Raíces — views/bitacora.php
 */
session_start();
if (!isset($_SESSION['usuario_id']) || strtolower($_SESSION['rol'] ?? '') !== 'admin') {
    header('Location: login.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bitácora | PP Bienes Raíces</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/panel.css">
    <link rel="stylesheet" href="../assets/css/bitacora.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
</head>
<body>
<div class="panel-layout">
    <?php include 'layouts/sidebar.php'; ?>
    <div class="panel-main">
        <?php include 'layouts/headerpanel.php'; ?>
        <div class="panel-content">
            <div class="bit-wrap">

                <!-- KPIs fila 1 -->
                <div class="admin-kpis">
                    <div class="kpi-card">
                        <div class="kpi-icon kpi-icon-navy"><i class="fas fa-file-contract"></i></div>
                        <div class="kpi-data">
                            <div class="kpi-label">Acciones contratos</div>
                            <div class="kpi-valor" id="kpiContratos">—</div>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon kpi-icon-green"><i class="fas fa-pen-nib"></i></div>
                        <div class="kpi-data">
                            <div class="kpi-label">Firmas</div>
                            <div class="kpi-valor" id="kpiFirmas">—</div>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon kpi-icon-blue"><i class="fas fa-phone-alt"></i></div>
                        <div class="kpi-data">
                            <div class="kpi-label">Contactos</div>
                            <div class="kpi-valor" id="kpiContactos">—</div>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon kpi-icon-gold"><i class="fas fa-calendar-day"></i></div>
                        <div class="kpi-data">
                            <div class="kpi-label">Eventos hoy</div>
                            <div class="kpi-valor" id="kpiHoy">—</div>
                        </div>
                    </div>
                </div>

                <!-- KPIs fila 2 -->
                <div class="admin-kpis" style="margin-bottom:6px;">
                    <div class="kpi-card">
                        <div class="kpi-icon" style="background:#2d1b00;color:#fcd34d;"><i class="fas fa-sign-in-alt"></i></div>
                        <div class="kpi-data">
                            <div class="kpi-label">Sesiones</div>
                            <div class="kpi-valor" id="kpiSesiones">—</div>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon" style="background:#1a2e1a;color:#6ee7b7;"><i class="fas fa-home"></i></div>
                        <div class="kpi-data">
                            <div class="kpi-label">Propiedades pub.</div>
                            <div class="kpi-valor" id="kpiPropiedades">—</div>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon" style="background:#2d0a2d;color:#f0abfc;"><i class="fas fa-user-plus"></i></div>
                        <div class="kpi-data">
                            <div class="kpi-label">Usuarios registrados</div>
                            <div class="kpi-valor" id="kpiUsuarios">—</div>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon" style="background:#1a1a2e;color:#a5b4fc;"><i class="fas fa-copy"></i></div>
                        <div class="kpi-data">
                            <div class="kpi-label">Plantillas subidas</div>
                            <div class="kpi-valor" id="kpiPlantillas">—</div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="filtros-bar-admin">
                    <div class="filtro-g">
                        <label><i class="fas fa-calendar"></i> Desde</label>
                        <input type="date" id="filtroDe">
                    </div>
                    <div class="filtro-g">
                        <label><i class="fas fa-calendar"></i> Hasta</label>
                        <input type="date" id="filtroA">
                    </div>
                    <div class="filtro-g">
                        <label><i class="fas fa-tag"></i> Tipo de evento</label>
                        <select id="filtroTipo">
                            <option value="">Todos los tipos</option>
                            <option value="contratos">Contratos</option>
                            <option value="firmas">Firmas</option>
                            <option value="contactos">Contactos</option>
                            <option value="sesiones">Sesiones</option>
                            <option value="propiedades">Propiedades</option>
                            <option value="usuarios">Usuarios</option>
                            <option value="plantillas">Plantillas</option>
                        </select>
                    </div>
                    <div class="filtro-g" style="flex:2;min-width:180px;">
                        <label><i class="fas fa-search"></i> Buscar</label>
                        <input type="text" id="filtroBuscar" placeholder="Acción, propiedad, usuario...">
                    </div>
                    <div class="filtro-btns">
                        <button class="btn-filtrar-admin" id="btnBuscar">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <button class="btn-limpiar-admin" id="btnLimpiar">
                            <i class="fas fa-times"></i> Limpiar
                        </button>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="tabla-wrap-admin">
                    <div class="tabla-head-admin">
                        <i class="fas fa-book"></i>
                        Registro de eventos
                        <span class="tabla-contador" id="tablaContador">Cargando...</span>
                        <button class="btn-refresh" id="btnRefresh" title="Actualizar">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="tabla-scroll">
                        <table>
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Descripción / Propiedad</th>
                                    <th>Realizado por</th>
                                    <th>Referencia</th>
                                    <th>Fecha</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody id="bitTablaBody">
                                <tr><td colspan="6">
                                    <div class="loading-state">
                                        <div class="loading-spinner"></div> Cargando...
                                    </div>
                                </td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="bitPaginacion" class="bit-paginacion"></div>
                </div>

            </div>
        </div>
        <?php include 'layouts/footer.php'; ?>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="toast-wrap" id="toastWrap"></div>

<script src="../assets/js/panel.js"></script>
<script src="../assets/js/bitacora.js"></script>
</body>
</html>
