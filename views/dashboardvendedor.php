<?php
/**
 * DASHBOARD — Vendedor
 * PP Bienes Raíces — views/dashboardvendedor.php
 */

session_start();

// Verificar sesión y rol
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'vendedor') {
  header('Location: login.php');
  exit;
}

// Variables para el layout
$titulo_pagina = 'Dashboard';
$breadcrumb    = [
  ['label' => 'Inicio', 'url' => 'dashboardvendedor.php'],
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
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,700&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/panel.css">
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

      <!-- Saludo personalizado -->
      <div class="bienvenida">
        <div class="bienvenida-texto">
          <h2>Bienvenido, <em><?= htmlspecialchars($_SESSION['nombre'] ?? '') ?></em> 👋</h2>
          <p>Aquí tienes un resumen de tu actividad reciente.</p>
        </div>
        <a href="nuevapropiedad.php" class="btn-panel btn-panel-gold">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z"/>
          </svg>
          Nueva propiedad
        </a>
      </div>

      <!-- ════ KPIs ════ -->
      <div class="kpi-grid">

        <div class="kpi-card">
          <div class="kpi-icon kpi-icon-navy">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9.293 2.293a1 1 0 011.414 0l7 7A1 1 0 0117 11h-1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3a1 1 0 00-1-1H9a1 1 0 00-1 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6H3a1 1 0 01-.707-1.707l7-7z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="kpi-data">
            <div class="kpi-label">Mis propiedades</div>
            <div class="kpi-valor">18</div>
            <div class="kpi-sub kpi-sub-up">↑ +2 este mes</div>
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
            <div class="kpi-valor">14</div>
            <div class="kpi-sub" style="color:var(--muted);">Publicadas</div>
          </div>
        </div>

        <div class="kpi-card">
          <div class="kpi-icon kpi-icon-gold">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 2c-2.236 0-4.43.18-6.57.524C1.993 2.755 1 4.014 1 5.426v5.148c0 1.413.993 2.67 2.43 2.902.848.137 1.705.248 2.57.331v3.443a.75.75 0 001.28.53l3.58-3.579a.78.78 0 01.527-.224 41.202 41.202 0 005.183-.5c1.437-.232 2.43-1.49 2.43-2.903V5.426c0-1.413-.993-2.67-2.43-2.902A41.289 41.289 0 0010 2zm0 7a1 1 0 100-2 1 1 0 000 2zM6 9a1 1 0 11-2 0 1 1 0 012 0zm5 1a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="kpi-data">
            <div class="kpi-label">Consultas nuevas</div>
            <div class="kpi-valor">3</div>
            <div class="kpi-sub" style="color:var(--orange);">Sin responder</div>
          </div>
        </div>

        <div class="kpi-card">
          <div class="kpi-icon kpi-icon-blue">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="kpi-data">
            <div class="kpi-label">Contratos activos</div>
            <div class="kpi-valor">2</div>
            <div class="kpi-sub kpi-sub-up">12 firmados este año</div>
          </div>
        </div>

      </div>

      <!-- ════ FILA: MIS PROPIEDADES + CONSULTAS RECIENTES ════ -->
      <div class="dash-grid-2">

        <!-- Mis propiedades -->
        <div class="card">
          <div class="card-header">
            <div>
              <div class="card-title">Mis propiedades</div>
              <div class="card-subtitle">18 publicadas · 868 en el sistema</div>
            </div>
            <div style="display:flex;gap:8px;">
              <a href="nuevapropiedad.php" class="btn-panel btn-panel-gold btn-panel-sm">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"><path d="M8.75 3.75a.75.75 0 00-1.5 0v3.5h-3.5a.75.75 0 000 1.5h3.5v3.5a.75.75 0 001.5 0v-3.5h3.5a.75.75 0 000-1.5h-3.5v-3.5z"/></svg>
                Nueva
              </a>
              <a href="propiedadesvendedor.php" class="btn-panel btn-panel-outline btn-panel-sm">Ver todas</a>
            </div>
          </div>
          <div class="tabla-wrap">
            <table class="tabla">
              <thead>
                <tr>
                  <th>Propiedad</th>
                  <th>Tipo</th>
                  <th>Precio</th>
                  <th>Vistas</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <div style="font-weight:600;font-size:.84rem;">Finca de Café</div>
                    <div style="font-size:.74rem;color:var(--muted);">Jayaque · #2342</div>
                  </td>
                  <td style="font-size:.82rem;">Finca</td>
                  <td style="font-weight:600;">$298K</td>
                  <td>
                    <div class="vistas-count">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" ><path d="M8 9.5a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/><path fill-rule="evenodd" d="M1.38 8.28a.87.87 0 000 .44 7.4 7.4 0 0014.24 0 .87.87 0 000-.44 7.4 7.4 0 00-14.24 0zM8 12a4 4 0 100-8 4 4 0 000 8z" clip-rule="evenodd"/></svg>
                      142
                    </div>
                  </td>
                  <td><span class="badge badge-activa">Activa</span></td>
                  <td>
                    <div style="display:flex;gap:5px;">
                      <button class="btn-panel btn-panel-outline btn-panel-sm">Editar</button>
                      <button class="btn-panel btn-panel-danger btn-panel-sm">Pausar</button>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td>
                    <div style="font-weight:600;font-size:.84rem;">Terreno residencial</div>
                    <div style="font-size:.74rem;color:var(--muted);">S.J. Villanueva · #2343</div>
                  </td>
                  <td style="font-size:.82rem;">Terreno</td>
                  <td style="font-weight:600;">$330K</td>
                  <td>
                    <div class="vistas-count">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" ><path d="M8 9.5a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/><path fill-rule="evenodd" d="M1.38 8.28a.87.87 0 000 .44 7.4 7.4 0 0014.24 0 .87.87 0 000-.44 7.4 7.4 0 00-14.24 0zM8 12a4 4 0 100-8 4 4 0 000 8z" clip-rule="evenodd"/></svg>
                      98
                    </div>
                  </td>
                  <td><span class="badge badge-activa">Activa</span></td>
                  <td>
                    <div style="display:flex;gap:5px;">
                      <button class="btn-panel btn-panel-outline btn-panel-sm">Editar</button>
                      <button class="btn-panel btn-panel-danger btn-panel-sm">Pausar</button>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td>
                    <div style="font-weight:600;font-size:.84rem;">Terreno rural</div>
                    <div style="font-size:.74rem;color:var(--muted);">Izalco, Sonsonate · #2339</div>
                  </td>
                  <td style="font-size:.82rem;">Terreno</td>
                  <td style="font-weight:600;">$55K</td>
                  <td>
                    <div class="vistas-count">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" ><path d="M8 9.5a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/><path fill-rule="evenodd" d="M1.38 8.28a.87.87 0 000 .44 7.4 7.4 0 0014.24 0 .87.87 0 000-.44 7.4 7.4 0 00-14.24 0zM8 12a4 4 0 100-8 4 4 0 000 8z" clip-rule="evenodd"/></svg>
                      61
                    </div>
                  </td>
                  <td><span class="badge badge-pendiente">Revisión</span></td>
                  <td>
                    <div style="display:flex;gap:5px;">
                      <button class="btn-panel btn-panel-outline btn-panel-sm">Editar</button>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td>
                    <div style="font-weight:600;font-size:.84rem;">Casa residencial</div>
                    <div style="font-size:.74rem;color:var(--muted);">Santa Tecla · #2338</div>
                  </td>
                  <td style="font-size:.82rem;">Casa</td>
                  <td style="font-weight:600;">$195K</td>
                  <td>
                    <div class="vistas-count">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" ><path d="M8 9.5a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/><path fill-rule="evenodd" d="M1.38 8.28a.87.87 0 000 .44 7.4 7.4 0 0014.24 0 .87.87 0 000-.44 7.4 7.4 0 00-14.24 0zM8 12a4 4 0 100-8 4 4 0 000 8z" clip-rule="evenodd"/></svg>
                      213
                    </div>
                  </td>
                  <td><span class="badge badge-firmado">Vendida</span></td>
                  <td>
                    <div style="display:flex;gap:5px;">
                      <button class="btn-panel btn-panel-outline btn-panel-sm">Ver detalle</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Consultas recientes -->
        <div class="card">
          <div class="card-header">
            <div>
              <div class="card-title">Consultas recientes</div>
              <div class="card-subtitle">3 sin responder</div>
            </div>
            <a href="consultas.php" class="btn-panel btn-panel-outline btn-panel-sm">Ver todas</a>
          </div>
          <div >

            <div class="consulta-item unread">
              <div class="consulta-canal canal-tel">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 3.5A1.5 1.5 0 013.5 2h1.148a1.5 1.5 0 011.465 1.175l.716 3.223a1.5 1.5 0 01-1.052 1.767l-.933.267c-.41.117-.643.555-.48.95a11.542 11.542 0 006.254 6.254c.395.163.833-.07.95-.48l.267-.933a1.5 1.5 0 011.767-1.052l3.223.716A1.5 1.5 0 0118 15.352V16.5a1.5 1.5 0 01-1.5 1.5H15c-1.149 0-2.263-.15-3.326-.43A13.022 13.022 0 012.43 8.326 13.019 13.019 0 012 5V3.5z" clip-rule="evenodd"/></svg>
              </div>
              <div class="consulta-info">
                <div class="consulta-prop">Finca de Café #2342</div>
                <div class="consulta-meta">Llamada · Hace 10 min</div>
              </div>
              <span class="consulta-dot"></span>
            </div>

            <div class="consulta-item unread">
              <div class="consulta-canal canal-wa">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
              </div>
              <div class="consulta-info">
                <div class="consulta-prop">Terreno residencial #2343</div>
                <div class="consulta-meta">WhatsApp · Hace 1 hora</div>
              </div>
              <span class="consulta-dot"></span>
            </div>

            <div class="consulta-item unread">
              <div class="consulta-canal canal-mail">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M3 4a2 2 0 00-2 2v1.161l8.441 4.221a1.25 1.25 0 001.118 0L19 7.162V6a2 2 0 00-2-2H3z"/><path d="M19 8.839l-7.77 3.885a2.75 2.75 0 01-2.46 0L1 8.839V14a2 2 0 002 2h14a2 2 0 002-2V8.839z"/></svg>
              </div>
              <div class="consulta-info">
                <div class="consulta-prop">Casa Santa Tecla #2338</div>
                <div class="consulta-meta">Correo · Hace 3 horas</div>
              </div>
              <span class="consulta-dot"></span>
            </div>

            <div class="consulta-item">
              <div class="consulta-canal canal-tel">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 3.5A1.5 1.5 0 013.5 2h1.148a1.5 1.5 0 011.465 1.175l.716 3.223a1.5 1.5 0 01-1.052 1.767l-.933.267c-.41.117-.643.555-.48.95a11.542 11.542 0 006.254 6.254c.395.163.833-.07.95-.48l.267-.933a1.5 1.5 0 011.767-1.052l3.223.716A1.5 1.5 0 0118 15.352V16.5a1.5 1.5 0 01-1.5 1.5H15c-1.149 0-2.263-.15-3.326-.43A13.022 13.022 0 012.43 8.326 13.019 13.019 0 012 5V3.5z" clip-rule="evenodd"/></svg>
              </div>
              <div class="consulta-info">
                <div class="consulta-prop">Local comercial #2336</div>
                <div class="consulta-meta">Llamada · Ayer</div>
              </div>
            </div>

          </div>
        </div>

      </div>

      <!-- ════ CONTRATOS EN CURSO ════ -->
      <div class="card">
        <div class="card-header">
          <div>
            <div class="card-title">Contratos en curso</div>
            <div class="card-subtitle">2 activos · 12 firmados este año</div>
          </div>
          <a href="contratosvendedor.php" class="btn-panel btn-panel-outline btn-panel-sm">Ver todos</a>
        </div>

        <div class="contratos-list">

          <!-- Contrato 1 -->
          <div class="contrato-card">
            <div class="contrato-header">
              <div>
                <div class="contrato-num">Contrato #CNT-2024-089</div>
                <div class="contrato-prop">Finca de Café · #2342</div>
              </div>
              <div style="display:flex;align-items:center;gap:8px;">
                <span class="badge badge-pendiente">Pendiente firma</span>
                <button class="btn-panel btn-panel-outline btn-panel-sm">Ver documento</button>
                <button class="btn-panel btn-panel-primary btn-panel-sm">Firmar ahora</button>
              </div>
            </div>
            <div class="contrato-detalles">
              <div class="contrato-dato"><span>Comprador</span><strong>Ana Fuentes Ramírez</strong></div>
              <div class="contrato-dato"><span>Precio acordado</span><strong>US$ 285,000</strong></div>
              <div class="contrato-dato"><span>Forma de pago</span><strong>Financiamiento + inicial 20%</strong></div>
              <div class="contrato-dato"><span>Cierre estimado</span><strong>15 mayo 2026</strong></div>
            </div>
            <!-- Barra de progreso por etapas -->
            <div class="contrato-progreso">
              <div class="progreso-label">
                <span>Progreso</span>
                <strong>60%</strong>
              </div>
              <div class="progreso-bar">
                <div class="progreso-fill" style="width:60%"></div>
              </div>
              <div class="progreso-etapas">
                <div class="etapa completada">
                  <div class="etapa-icono">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>
                  </div>
                  <span>Oferta aceptada</span>
                </div>
                <div class="etapa-linea completada"></div>
                <div class="etapa completada">
                  <div class="etapa-icono">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>
                  </div>
                  <span>Inspección</span>
                </div>
                <div class="etapa-linea activa"></div>
                <div class="etapa activa">
                  <div class="etapa-icono">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
                  </div>
                  <span>Firma contrato</span>
                </div>
                <div class="etapa-linea"></div>
                <div class="etapa pendiente">
                  <div class="etapa-icono">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 10.818v2.614A3.13 3.13 0 0011.888 13c.482-.315.612-.648.612-.875 0-.227-.13-.56-.612-.875a3.13 3.13 0 00-1.138-.432zM8.33 8.62c.053.055.115.11.184.164.208.16.46.284.736.363V6.603a2.45 2.45 0 00-.35.13c-.14.065-.27.143-.386.233-.377.292-.514.627-.514.909 0 .184.058.39.33.615z"/><path fill-rule="evenodd" d="M9.99 1.012a9 9 0 100 18 9 9 0 000-18zM9.25 4a.75.75 0 011.5 0v.665c.628.112 1.227.372 1.686.74.706.566 1.064 1.353 1.064 2.095 0 .98-.508 1.733-1.056 2.261l-.172.163c.18.128.35.275.498.437.413.461.68 1.1.68 1.839 0 .98-.508 1.733-1.056 2.261-.386.37-.888.655-1.444.793V18a.75.75 0 01-1.5 0v-.689A4.312 4.312 0 016.516 16c-.413-.46-.68-1.1-.68-1.839a.75.75 0 011.5 0c0 .307.134.682.532.977a3.1 3.1 0 001.382.484V12.43a4.323 4.323 0 01-1.695-.768C6.573 11.16 6.25 10.31 6.25 9.5c0-.893.417-1.686 1.061-2.264A4.312 4.312 0 019.25 6.39V4z" clip-rule="evenodd"/></svg>
                  </div>
                  <span>Transferencia</span>
                </div>
                <div class="etapa-linea"></div>
                <div class="etapa pendiente">
                  <div class="etapa-icono">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                  </div>
                  <span>Escritura</span>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>
    <!-- fin panel-content -->

    <!-- Footer -->
    <footer class="panel-footer">
      <span>&copy; <?= date('Y') ?> <strong>PP Bienes Raíces</strong> — Asociaciones Portillo Pocasangre</span>
      <div class="panel-footer-links">
        <a href="#">Privacidad</a>
        <a href="#">Términos</a>
        <a href="#">Soporte</a>
      </div>
    </footer>

  </div>

</div>

<!-- Overlay móvil -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>



  <script src="../assets/js/panel.js"></script>
</body>
</html>