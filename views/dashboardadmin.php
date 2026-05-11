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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
            <i class="fas fa-home"></i>
          </div>
          <div class="kpi-data">
            <div class="kpi-label">Propiedades activas</div>
            <div class="kpi-valor">868</div>
            <div class="kpi-sub kpi-sub-up">↑ +12 este mes</div>
          </div>
        </div>

        <div class="kpi-card">
          <div class="kpi-icon kpi-icon-blue">
            <i class="fas fa-users"></i>
          </div>
          <div class="kpi-data">
            <div class="kpi-label">Usuarios registrados</div>
            <div class="kpi-valor">142</div>
            <div class="kpi-sub kpi-sub-up">↑ +8 este mes</div>
          </div>
        </div>

        <div class="kpi-card">
          <div class="kpi-icon kpi-icon-gold">
            <i class="fas fa-file-contract"></i>
          </div>
          <div class="kpi-data">
            <div class="kpi-label">Contratos activos</div>
            <div class="kpi-valor">48</div>
            <div class="kpi-sub kpi-sub-up">↑ +5 este mes</div>
          </div>
        </div>

        <div class="kpi-card">
          <div class="kpi-icon kpi-icon-green">
            <i class="fas fa-dollar-sign"></i>
          </div>
          <div class="kpi-data">
            <div class="kpi-label">Valor total contratos</div>
            <div class="kpi-valor">$8.4M</div>
            <div class="kpi-sub kpi-sub-up">↑ +$1.2M este año</div>
          </div>
        </div>

        <div class="kpi-card">
          <div class="kpi-icon kpi-icon-navy">
            <i class="fas fa-chart-line"></i>
          </div>
          <div class="kpi-data">
            <div class="kpi-label">Ventas este mes</div>
            <div class="kpi-valor">23</div>
            <div class="kpi-sub kpi-sub-up">↑ +4 vs mes anterior</div>
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