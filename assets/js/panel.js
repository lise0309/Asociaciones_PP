/**
 * panel.js
 * PP Bienes Raíces — assets/js/panel.js
 * JavaScript global del panel admin y vendedor
 */

(function () {
  'use strict';

  // ── Toggle sidebar en móvil ──
  const sidebarToggle  = document.getElementById('sidebarToggle');
  const sidebar        = document.getElementById('sidebar');
  const sidebarOverlay = document.getElementById('sidebarOverlay');

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => {
      const open = sidebar.classList.toggle('open');
      if (sidebarOverlay) sidebarOverlay.style.display = open ? 'block' : 'none';
    });
  }
  if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', () => {
      sidebar?.classList.remove('open');
      sidebarOverlay.style.display = 'none';
    });
  }

  // ── Dropdown notificaciones ──
  const notifBtn      = document.getElementById('notifBtn');
  const notifDropdown = document.getElementById('notifDropdown');

  notifBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    notifDropdown?.classList.toggle('open');
    document.getElementById('userDropdown')?.classList.remove('open');
  });

  // ── Dropdown usuario ──
  const userBtn      = document.getElementById('userBtn');
  const userDropdown = document.getElementById('userDropdown');

  userBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    userDropdown?.classList.toggle('open');
    notifDropdown?.classList.remove('open');
  });

  // ── Cerrar dropdowns al click fuera ──
  document.addEventListener('click', () => {
    notifDropdown?.classList.remove('open');
    userDropdown?.classList.remove('open');
  });

  // ── Marcar notificaciones como leídas ──
  document.querySelector('.notif-mark-all')?.addEventListener('click', () => {
    document.querySelectorAll('.notif-item.unread').forEach(item => {
      item.classList.remove('unread');
    });
    document.getElementById('notifDot')?.remove();
  });

})();