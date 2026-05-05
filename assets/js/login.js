/**
 * login.js
 * PP Bienes Raíces — Asociaciones Portillo Pocasangre
 * Validación en tiempo real y UX del formulario de login
 */

(function () {
  'use strict';

  // ── Referencias DOM ──
  const form       = document.getElementById('loginForm');
  const inputEmail = document.getElementById('correo');
  const inputPass  = document.getElementById('clave');
  const togglePass = document.getElementById('togglePass');
  const eyeOff     = document.getElementById('eyeOff');
  const eyeOn      = document.getElementById('eyeOn');
  const btnSubmit  = document.getElementById('btnSubmit');
  const btnText    = btnSubmit?.querySelector('.btn-text');
  const btnLoader  = document.getElementById('btnLoader');
  const groupEmail = document.getElementById('groupCorreo');
  const groupPass  = document.getElementById('groupClave');
  const errorEmail = document.getElementById('errorCorreo');
  const errorPass  = document.getElementById('errorClave');

  if (!form) return;

  // ════════════════════════════════════════
  // TOGGLE CONTRASEÑA (mostrar/ocultar)
  // ════════════════════════════════════════
  if (togglePass && inputPass) {
    togglePass.addEventListener('click', () => {
      const visible = inputPass.type === 'text';
      inputPass.type = visible ? 'password' : 'text';
      eyeOff.style.display = visible ? 'block' : 'none';
      eyeOn.style.display  = visible ? 'none'  : 'block';
      togglePass.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
      inputPass.focus();
    });
  }

  // ════════════════════════════════════════
  // VALIDACIONES
  // ════════════════════════════════════════
  function validarEmail(valor) {
    if (!valor.trim())                        return 'El correo es obligatorio.';
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor)) return 'Ingresa un correo válido.';
    return '';
  }

  function validarClave(valor) {
    if (!valor) return 'La contraseña es obligatoria.';
    if (valor.length < 6) return 'La contraseña debe tener al menos 6 caracteres.';
    return '';
  }

  function setEstado(group, errorEl, mensaje) {
    if (!group || !errorEl) return;
    group.classList.remove('has-error', 'is-valid');
    errorEl.textContent = '';

    if (mensaje) {
      group.classList.add('has-error');
      errorEl.textContent = mensaje;
    } else {
      group.classList.add('is-valid');
    }
  }

  function limpiarEstado(group, errorEl) {
    if (!group || !errorEl) return;
    group.classList.remove('has-error', 'is-valid');
    errorEl.textContent = '';
  }

  // ── Validar al perder foco ──
  if (inputEmail) {
    inputEmail.addEventListener('blur', () => {
      const msg = validarEmail(inputEmail.value);
      setEstado(groupEmail, errorEmail, msg);
    });
    inputEmail.addEventListener('input', () => {
      if (groupEmail.classList.contains('has-error')) {
        const msg = validarEmail(inputEmail.value);
        setEstado(groupEmail, errorEmail, msg);
      }
    });
  }

  if (inputPass) {
    inputPass.addEventListener('blur', () => {
      const msg = validarClave(inputPass.value);
      setEstado(groupPass, errorPass, msg);
    });
    inputPass.addEventListener('input', () => {
      if (groupPass.classList.contains('has-error')) {
        const msg = validarClave(inputPass.value);
        setEstado(groupPass, errorPass, msg);
      }
    });
  }

  // ════════════════════════════════════════
  // SUBMIT — validar antes de enviar
  // ════════════════════════════════════════
  form.addEventListener('submit', (e) => {
    const msgEmail = validarEmail(inputEmail?.value ?? '');
    const msgPass  = validarClave(inputPass?.value  ?? '');

    setEstado(groupEmail, errorEmail, msgEmail);
    setEstado(groupPass,  errorPass,  msgPass);

    if (msgEmail || msgPass) {
      e.preventDefault();
      // Enfocar el primer campo con error
      if (msgEmail) inputEmail.focus();
      else          inputPass.focus();
      return;
    }

    // ── Mostrar loader ──
    if (btnText && btnLoader && btnSubmit) {
      btnText.style.display   = 'none';
      btnLoader.style.display = 'flex';
      btnSubmit.disabled      = true;
    }
  });

  // ════════════════════════════════════════
  // AUTO-CERRAR ALERTA DE ERROR
  // ════════════════════════════════════════
  const alerta = document.getElementById('loginAlert');
  if (alerta) {
    setTimeout(() => {
      alerta.style.transition = 'opacity .5s ease, transform .5s ease';
      alerta.style.opacity    = '0';
      alerta.style.transform  = 'translateY(-6px)';
      setTimeout(() => alerta.remove(), 500);
    }, 5000);
  }

  // ════════════════════════════════════════
  // CAPS LOCK AVISO
  // ════════════════════════════════════════
  if (inputPass) {
    inputPass.addEventListener('keyup', (e) => {
      const capsOn = e.getModifierState?.('CapsLock');
      let aviso = document.getElementById('capsAviso');

      if (capsOn) {
        if (!aviso) {
          aviso = document.createElement('span');
          aviso.id          = 'capsAviso';
          aviso.className   = 'form-error';
          aviso.textContent = '⚠ Mayúsculas activadas';
          inputPass.closest('.form-group')?.appendChild(aviso);
        }
      } else {
        aviso?.remove();
      }
    });
  }

})();