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
    cargarNotificaciones();
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

  // ── Cargar notificaciones reales ──
  async function cargarNotificaciones() {
    const list = document.getElementById('notifList');
    if (!list) return;

    try {
      const res  = await fetch('../controllers/notificacionescontroller.php');
      const data = await res.json();
      if (!data.ok) return;

      // Punto rojo
      const dot = document.getElementById('notifDot');
      if (dot) dot.style.display = data.no_leidas > 0 ? 'block' : 'none';

      // Sin notificaciones
      if (!data.notifs.length) {
        list.innerHTML = '<div class="notif-vacio"><p>Sin notificaciones pendientes</p></div>';
        return;
      }

      const colores = {
        gold:  'notif-icon-gold',
        navy:  'notif-icon-blue',
        blue:  'notif-icon-blue',
        green: 'notif-icon-green',
        red:   'notif-icon-red',
      };

      list.innerHTML = data.notifs.map(n => `
        <a href="${n.url}" class="notif-item${n.leida ? '' : ' unread'}" style="text-decoration:none;">
          <div class="notif-icon ${colores[n.icono] || 'notif-icon-blue'}">
            <i class="fas ${n.icon_class}"></i>
          </div>
          <div class="notif-body">
            <p>${n.texto}</p>
            <span>${n.sub ? '<strong>' + n.sub + '</strong> &middot; ' : ''}${n.tiempo}</span>
          </div>
        </a>`).join('');

    } catch(e) { /* silencioso */ }
  }

  // Cargar al inicio en background
  setTimeout(cargarNotificaciones, 500);

})();