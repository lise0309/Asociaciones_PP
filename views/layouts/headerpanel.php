<?php
/**
 * HEADER DEL PANEL
 * PP Bienes Raíces — views/layouts/header_panel.php
 * Cabecera superior del área de contenido
 *
 * Uso: include con $titulo_pagina y $breadcrumb definidos antes
 * Ejemplo:
 *   $titulo_pagina = 'Dashboard';
 *   $breadcrumb    = [['label' => 'Inicio', 'url' => 'dashboard.php'], ['label' => 'Dashboard']];
 *   include '../layouts/header_panel.php';
 */
?>

<header class="panel-header">
  <div class="panel-header-left">

    <!-- Botón hamburger móvil -->
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Abrir menú">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z" clip-rule="evenodd"/>
      </svg>
    </button>

    <!-- Título y breadcrumb -->
    <div>
      <h1 class="panel-titulo"><?= htmlspecialchars($titulo_pagina ?? 'Panel') ?></h1>
      <?php if (!empty($breadcrumb)): ?>
      <nav class="panel-breadcrumb" aria-label="Breadcrumb">
        <?php foreach ($breadcrumb as $i => $item): ?>
          <?php if ($i > 0): ?><span class="bc-sep">›</span><?php endif; ?>
          <?php if (!empty($item['url'])): ?>
            <a href="<?= $item['url'] ?>" class="bc-link"><?= htmlspecialchars($item['label']) ?></a>
          <?php else: ?>
            <span class="bc-current"><?= htmlspecialchars($item['label']) ?></span>
          <?php endif; ?>
        <?php endforeach; ?>
      </nav>
      <?php endif; ?>
    </div>

  </div>

  <div class="panel-header-right">

    <!-- Fecha actual -->
    <div class="panel-fecha">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z" clip-rule="evenodd"/>
      </svg>
      <?= date('d/m/Y') ?>
    </div>

    <!-- Notificaciones -->
    <div class="panel-notif" id="notifWrap">
      <button class="panel-notif-btn" id="notifBtn" aria-label="Notificaciones">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M4 8a6 6 0 1112 0c0 1.887.454 3.665 1.257 5.234a.75.75 0 01-.515 1.076 32.91 32.91 0 01-3.256.508 3.5 3.5 0 01-6.972 0 32.903 32.903 0 01-3.256-.508.75.75 0 01-.515-1.076A11.448 11.448 0 004 8zm6 7c-.655 0-1.305-.02-1.95-.057A2 2 0 0010 17a2 2 0 001.95-2.057A48.661 48.661 0 018 15z" clip-rule="evenodd"/>
        </svg>
        <span class="notif-dot" id="notifDot"></span>
      </button>
      <!-- Dropdown notificaciones -->
      <div class="notif-dropdown" id="notifDropdown">
        <div class="notif-header">
          <span>Notificaciones</span>
          <button class="notif-mark-all">Marcar todas</button>
        </div>
        <div class="notif-list">
          <div class="notif-item unread">
            <div class="notif-icon notif-icon-blue">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"><path d="M8 8a3 3 0 100-6 3 3 0 000 6z"/><path fill-rule="evenodd" d="M8.186 1.113a.5.5 0 00-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7l-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6z" clip-rule="evenodd"/></svg>
            </div>
            <div class="notif-body">
              <p>Nueva propiedad pendiente de aprobación</p>
              <span>Hace 5 min</span>
            </div>
          </div>
          <div class="notif-item unread">
            <div class="notif-icon notif-icon-gold">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L13.414 4A2 2 0 0114 5.414V14a2 2 0 01-2 2H4a2 2 0 01-2-2V4zm2 1a1 1 0 000 2h4a1 1 0 000-2H6zm0 3a1 1 0 000 2h4a1 1 0 100-2H6zm0 3a1 1 0 100 2h2a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
            </div>
            <div class="notif-body">
              <p>Contrato #CNT-089 pendiente de firma</p>
              <span>Hace 1 hora</span>
            </div>
          </div>
          <div class="notif-item">
            <div class="notif-icon notif-icon-green">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" d="M8 15A7 7 0 108 1a7 7 0 000 14zm3.844-8.791a.75.75 0 00-1.188-.918l-3.7 4.79-1.646-1.647a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.124-.1l4.22-5.435z" clip-rule="evenodd"/></svg>
            </div>
            <div class="notif-body">
              <p>Vendedor Mario Rodríguez verificado</p>
              <span>Hace 2 horas</span>
            </div>
          </div>
        </div>
        <div class="notif-footer">
          <a href="notificaciones.php">Ver todas las notificaciones</a>
        </div>
      </div>
    </div>

    <!-- Avatar usuario -->
    <div class="panel-user" id="userMenu">
      <button class="panel-user-btn" id="userBtn">
        <div class="panel-user-avatar">
          <?= strtoupper(substr($_SESSION['nombre'] ?? 'U', 0, 1) . substr($_SESSION['apellido'] ?? '', 0, 1)) ?>
        </div>
        <span class="panel-user-name"><?= htmlspecialchars($_SESSION['nombre'] ?? '') ?></span>
        <svg class="panel-user-chev" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 011.06 0L10 11.94l3.72-3.72a.75.75 0 111.06 1.06l-4.25 4.25a.75.75 0 01-1.06 0L5.22 9.28a.75.75 0 010-1.06z" clip-rule="evenodd"/>
        </svg>
      </button>
      <div class="user-dropdown" id="userDropdown">
        <?php if ($_SESSION['rol'] === 'vendedor'): ?>
        <a href="perfil.php" class="user-drop-item">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z"/></svg>
          Mi perfil
        </a>
        <?php endif; ?>
        <?php if ($_SESSION['rol'] === 'admin'): ?>
        <a href="configuracion.php" class="user-drop-item">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.84 1.804A1 1 0 018.82 1h2.36a1 1 0 01.98.804l.331 1.652a6.993 6.993 0 011.929 1.115l1.598-.54a1 1 0 011.186.447l1.18 2.044a1 1 0 01-.205 1.251l-1.267 1.113a7.047 7.047 0 010 2.228l1.267 1.113a1 1 0 01.206 1.25l-1.18 2.045a1 1 0 01-1.187.447l-1.598-.54a6.993 6.993 0 01-1.929 1.115l-.33 1.652a1 1 0 01-.98.804H8.82a1 1 0 01-.98-.804l-.331-1.652a6.993 6.993 0 01-1.929-1.115l-1.598.54a1 1 0 01-1.186-.447l-1.18-2.044a1 1 0 01.205-1.251l1.267-1.114a7.05 7.05 0 010-2.227L1.821 7.773a1 1 0 01-.206-1.25l1.18-2.045a1 1 0 011.187-.447l1.598.54A6.993 6.993 0 017.51 3.456l.33-1.652zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>
          Configuración
        </a>
        <?php endif; ?>
        <div class="user-drop-divider"></div>
        <a href="logout.php" class="user-drop-item user-drop-item-danger">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 015.25 2h5.5A2.25 2.25 0 0113 4.25v2a.75.75 0 01-1.5 0v-2a.75.75 0 00-.75-.75h-5.5a.75.75 0 00-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 00.75-.75v-2a.75.75 0 011.5 0v2A2.25 2.25 0 0110.75 18h-5.5A2.25 2.25 0 013 15.75V4.25z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M6 10a.75.75 0 01.75-.75h9.546l-1.048-.943a.75.75 0 111.004-1.114l2.5 2.25a.75.75 0 010 1.114l-2.5 2.25a.75.75 0 11-1.004-1.114l1.048-.943H6.75A.75.75 0 016 10z" clip-rule="evenodd"/></svg>
          Cerrar sesión
        </a>
      </div>
    </div>

  </div>
</header>