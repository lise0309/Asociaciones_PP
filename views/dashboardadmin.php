<?php
/**
 * DASHBOARD — Administrador
 * PP Bienes Raíces — views/admin/dashboard.php
 */

session_start();

// Verificar sesión y rol
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
  header('Location: login.php');
  exit;
}

// Variables para el layout
$titulo_pagina = 'Dashboard';
$breadcrumb    = [
  ['label' => 'Inicio', 'url' => 'dashboard.php'],
  ['label' => 'Dashboard'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | PP Bienes Raíces</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/panel.css">
  <link rel="stylesheet" href="../assets/css/footer.css">
</head>
<body>

<div class="panel-layout">

  <!-- ── SIDEBAR ── -->
  <?php include 'layouts/sidebar.php'; ?>

  <!-- ── CONTENIDO PRINCIPAL ── -->
  <div class="panel-main">

    <!-- Header -->
    <?php include 'layouts/headerpanel.php'; ?>

    <!-- Contenido -->
    <div class="panel-content">

      <!-- ════ KPIs GLOBALES ════ -->
      <div class="kpi-grid">

        <div class="kpi-card">
          <div class="kpi-icon kpi-icon-navy">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9.293 2.293a1 1 0 011.414 0l7 7A1 1 0 0117 11h-1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3a1 1 0 00-1-1H9a1 1 0 00-1 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6H3a1 1 0 01-.707-1.707l7-7z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="kpi-data">
            <div class="kpi-label">Propiedades activas</div>
            <div class="kpi-valor">868</div>
           
          </div>
        </div>

        <div class="kpi-card">
          <div class="kpi-icon kpi-icon-blue">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path d="M7 8a3 3 0 100-6 3 3 0 000 6zM14.5 9a2.5 2.5 0 100-5 2.5 2.5 0 000 5zM1.615 16.428a1.224 1.224 0 01-.569-1.175 6.002 6.002 0 0111.908 0c.058.467-.172.92-.57 1.174A9.953 9.953 0 017 18a9.953 9.953 0 01-5.385-1.572zM14.5 16h-.106c.07-.297.088-.611.048-.933a7.47 7.47 0 00-1.588-3.755 4.502 4.502 0 015.874 2.575c.092.341-.051.703-.345.878A9.969 9.969 0 0114.5 16z"/>
            </svg>
          </div>
          <div class="kpi-data">
            <div class="kpi-label">Usuarios registrados</div>
            <div class="kpi-valor">142</div>
            
          </div>
        </div>

        <div class="kpi-card">
          <div class="kpi-icon kpi-icon-gold">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="kpi-data">
            <div class="kpi-label">Contratos activos</div>
            <div class="kpi-valor">48</div>
            
          </div>
        </div>

        <div class="kpi-card">
          <div class="kpi-icon kpi-icon-green">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path d="M10.75 10.818v2.614A3.13 3.13 0 0011.888 13c.482-.315.612-.648.612-.875 0-.227-.13-.56-.612-.875a3.13 3.13 0 00-1.138-.432zM8.33 8.62c.053.055.115.11.184.164.208.16.46.284.736.363V6.603a2.45 2.45 0 00-.35.13c-.14.065-.27.143-.386.233-.377.292-.514.627-.514.909 0 .184.058.39.33.615z"/>
              <path fill-rule="evenodd" d="M9.99 1.012a9 9 0 100 18 9 9 0 000-18zM9.25 4a.75.75 0 011.5 0v.665c.628.112 1.227.372 1.686.74.706.566 1.064 1.353 1.064 2.095 0 .98-.508 1.733-1.056 2.261l-.172.163c.18.128.35.275.498.437.413.461.68 1.1.68 1.839 0 .98-.508 1.733-1.056 2.261-.386.37-.888.655-1.444.793V18a.75.75 0 01-1.5 0v-.689A4.312 4.312 0 016.516 16c-.413-.46-.68-1.1-.68-1.839a.75.75 0 011.5 0c0 .307.134.682.532.977a3.1 3.1 0 001.382.484V12.43a4.323 4.323 0 01-1.695-.768C6.573 11.16 6.25 10.31 6.25 9.5c0-.893.417-1.686 1.061-2.264A4.312 4.312 0 019.25 6.39V4z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="kpi-data">
            <div class="kpi-label">Valor total contratos</div>
            <div class="kpi-valor">$8.4M</div>
            
          </div>
        </div>

      </div>
      <!-- fin kpi-grid -->

      <!-- ════ FILA: TABLA USUARIOS + CONTRATOS RECIENTES ════ -->
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">

        <!-- Usuarios recientes -->
        <div class="card">
          <div class="card-header">
            <div>
              <div class="card-title">Gestión de usuarios</div>
              <div class="card-subtitle">142 registrados · 38 agentes verificados</div>
            </div>
            <a href="usuarios.php" class="btn-panel btn-panel-outline btn-panel-sm">Ver todos</a>
          </div>
          <div class="tabla-wrap">
            <table class="tabla">
              <thead>
                <tr>
                  <th>Usuario</th>
                  <th>Rol</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <div style="display:flex;align-items:center;gap:9px;">
                      <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#1A1953,#252477);display:flex;align-items:center;justify-content:center;font-size:.68rem;font-weight:700;color:#FFD45A;flex-shrink:0;">CP</div>
                      <div>
                        <div style="font-weight:600;font-size:.84rem;">Carlos Portillo</div>
                        <div style="font-size:.74rem;color:var(--muted);">carlos.p@pp.sv</div>
                      </div>
                    </div>
                  </td>
                  <td><span class="badge badge-admin">Admin</span></td>
                  <td><span class="badge badge-activa">Activo</span></td>
                  <td>
                    <div style="display:flex;gap:5px;">
                      <button class="btn-panel btn-panel-outline btn-panel-sm">Editar</button>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td>
                    <div style="display:flex;align-items:center;gap:9px;">
                      <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#1A1953,#252477);display:flex;align-items:center;justify-content:center;font-size:.68rem;font-weight:700;color:#FFD45A;flex-shrink:0;">MR</div>
                      <div>
                        <div style="font-weight:600;font-size:.84rem;">Mario Rodríguez</div>
                        <div style="font-size:.74rem;color:var(--muted);">mario.r@pp.sv · La Libertad</div>
                      </div>
                    </div>
                  </td>
                  <td><span class="badge badge-vendedor">Agente</span></td>
                  <td><span class="badge badge-activa">Verificado</span></td>
                  <td>
                    <div style="display:flex;gap:5px;">
                      <button class="btn-panel btn-panel-outline btn-panel-sm">Editar</button>
                      <button class="btn-panel btn-panel-danger btn-panel-sm">Suspender</button>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td>
                    <div style="display:flex;align-items:center;gap:9px;">
                      <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#1A1953,#252477);display:flex;align-items:center;justify-content:center;font-size:.68rem;font-weight:700;color:#FFD45A;flex-shrink:0;">RG</div>
                      <div>
                        <div style="font-weight:600;font-size:.84rem;">Rosa García</div>
                        <div style="font-size:.74rem;color:var(--muted);">rosa.g@gmail.com · Santa Ana</div>
                      </div>
                    </div>
                  </td>
                  <td><span class="badge badge-vendedor">Agente</span></td>
                  <td><span class="badge badge-pendiente">Pendiente doc.</span></td>
                  <td>
                    <div style="display:flex;gap:5px;">
                      <button class="btn-panel btn-panel-primary btn-panel-sm">Verificar</button>
                      <button class="btn-panel btn-panel-danger btn-panel-sm">Rechazar</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Contratos recientes -->
        <div class="card">
          <div class="card-header">
            <div>
              <div class="card-title">Contratos recientes</div>
              <div class="card-subtitle">48 totales · $8.4M en valor</div>
            </div>
            <div style="display:flex;gap:8px;">
              <a href="contratos.php" class="btn-panel btn-panel-gold btn-panel-sm">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"><path d="M8.75 3.75a.75.75 0 00-1.5 0v3.5h-3.5a.75.75 0 000 1.5h3.5v3.5a.75.75 0 001.5 0v-3.5h3.5a.75.75 0 000-1.5h-3.5v-3.5z"/></svg>
                Nuevo
              </a>
              <a href="contratos.php" class="btn-panel btn-panel-outline btn-panel-sm">Ver todos</a>
            </div>
          </div>
          <div class="tabla-wrap">
            <table class="tabla">
              <thead>
                <tr>
                  <th>N° Contrato</th>
                  <th>Propiedad</th>
                  <th>Valor</th>
                  <th>Estado</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td style="font-weight:600;color:var(--navy);">#CNT-089</td>
                  <td>
                    <div style="font-size:.84rem;font-weight:500;">Finca de Café #2342</div>
                    <div style="font-size:.74rem;color:var(--muted);">M. Rodríguez</div>
                  </td>
                  <td style="font-weight:600;">$285,000</td>
                  <td><span class="badge badge-pendiente">Firma pend.</span></td>
                  <td>
                    <div style="display:flex;gap:5px;">
                      <button class="btn-panel btn-panel-outline btn-panel-sm">Ver</button>
                      <button class="btn-panel btn-panel-outline btn-panel-sm">Editar</button>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td style="font-weight:600;color:var(--navy);">#CNT-075</td>
                  <td>
                    <div style="font-size:.84rem;font-weight:500;">Local comercial #2336</div>
                    <div style="font-size:.74rem;color:var(--muted);">M. Rodríguez</div>
                  </td>
                  <td style="font-weight:600;">$2,400/mo</td>
                  <td><span class="badge badge-activa">Activo</span></td>
                  <td>
                    <div style="display:flex;gap:5px;">
                      <button class="btn-panel btn-panel-outline btn-panel-sm">Ver</button>
                      <button class="btn-panel btn-panel-outline btn-panel-sm">Editar</button>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td style="font-weight:600;color:var(--navy);">#CNT-060</td>
                  <td>
                    <div style="font-size:.84rem;font-weight:500;">Casa Santa Tecla #2338</div>
                    <div style="font-size:.74rem;color:var(--muted);">L. Portillo</div>
                  </td>
                  <td style="font-weight:600;">$195,000</td>
                  <td><span class="badge badge-firmado">Firmado</span></td>
                  <td>
                    <div style="display:flex;gap:5px;">
                      <button class="btn-panel btn-panel-outline btn-panel-sm">Ver</button>
                      <button class="btn-panel btn-panel-primary btn-panel-sm">PDF</button>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td style="font-weight:600;color:var(--navy);">#CNT-044</td>
                  <td>
                    <div style="font-size:.84rem;font-weight:500;">Hacienda ganadera #2340</div>
                    <div style="font-size:.74př;color:var(--muted);">A. Pocasangre</div>
                  </td>
                  <td style="font-weight:600;">$1.65M</td>
                  <td><span class="badge badge-pendiente">En proceso</span></td>
                  <td>
                    <div style="display:flex;gap:5px;">
                      <button class="btn-panel btn-panel-outline btn-panel-sm">Ver</button>
                      <button class="btn-panel btn-panel-outline btn-panel-sm">Editar</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>

      <!-- ════ PROPIEDADES PENDIENTES ════ -->
      <div class="card">
        <div class="card-header">
          <div>
            <div class="card-title">Propiedades pendientes de aprobación</div>
            <div class="card-subtitle">Revisa y aprueba antes de publicar</div>
          </div>
          <a href="propiedades.php" class="btn-panel btn-panel-outline btn-panel-sm">Ver todas</a>
        </div>
        <div class="tabla-toolbar">
          <div class="tabla-search">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/></svg>
            <input type="text" placeholder="Buscar propiedad...">
          </div>
          <div class="tabla-filters">
            <select class="tabla-select">
              <option>Todos los tipos</option>
              <option>Casa</option>
              <option>Terreno</option>
              <option>Finca</option>
              <option>Local</option>
            </select>
          </div>
        </div>
        <div class="tabla-wrap">
          <table class="tabla">
            <thead>
              <tr>
                <th>Propiedad</th>
                <th>Tipo</th>
                <th>Precio</th>
                <th>Modalidad</th>
                <th>Agente</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>
                  <div style="font-weight:600;font-size:.87rem;">Finca de Café con vivienda</div>
                  <div style="font-size:.74rem;color:var(--muted);">Jayaque, La Libertad · #2342</div>
                </td>
                <td>Finca</td>
                <td style="font-weight:600;">$298K</td>
                <td><span class="badge badge-vendedor">Venta</span></td>
                <td>M. Rodríguez</td>
                <td><span class="badge badge-pendiente">Pendiente</span></td>
                <td>
                  <div style="display:flex;gap:6px;">
                    <button class="btn-panel btn-panel-outline btn-panel-sm">Ver</button>
                    <button class="btn-panel btn-panel-primary btn-panel-sm">Aprobar</button>
                    <button class="btn-panel btn-panel-danger btn-panel-sm">Rechazar</button>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <div style="font-weight:600;font-size:.87rem;">Terreno residencial 2,500 m²</div>
                  <div style="font-size:.74rem;color:var(--muted);">San José Villanueva · #2343</div>
                </td>
                <td>Terreno</td>
                <td style="font-weight:600;">$330K</td>
                <td><span class="badge badge-vendedor">Venta</span></td>
                <td>M. Rodríguez</td>
                <td><span class="badge badge-pendiente">Pendiente</span></td>
                <td>
                  <div style="display:flex;gap:6px;">
                    <button class="btn-panel btn-panel-outline btn-panel-sm">Ver</button>
                    <button class="btn-panel btn-panel-primary btn-panel-sm">Aprobar</button>
                    <button class="btn-panel btn-panel-danger btn-panel-sm">Rechazar</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
    <!-- fin panel-content -->

    <!-- Footer -->
    <?php include 'layouts/footer.php'; ?>

  </div>
  <!-- fin panel-main -->

</div>

<!-- Overlay móvil para cerrar sidebar -->
<div class="sidebar-overlay" id="sidebarOverlay" ></div>



  <script src="../../assets/js/panel.js"></script>
</body>
</html>