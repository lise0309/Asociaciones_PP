<?php
/**
 * LOGIN
 * PP Bienes Raíces — Asociaciones Portillo Pocasangre
 * vielogin.php
 */

session_start();

// Si ya hay sesión activa redirigir según rol
if (isset($_SESSION['usuario_id'])) {
  if ($_SESSION['rol'] === 'admin') {
    header('Location: dashboardadmin.php');
  } else {
    header('Location: dashboardvendedor.php');
  }
  exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/google.php';

$error  = '';
$correo = '';

// Errores que vienen del callback de Google
$erroresGoogle = [
  'no_registrado' => 'Tu cuenta de Google no está registrada en el sistema. Contacta al administrador.',
  'suspendida'    => 'Tu cuenta ha sido suspendida. Contacta al administrador.',
  'cancelado'     => 'Cancelaste el inicio de sesión con Google.',
  'csrf'          => 'Error de seguridad. Intenta nuevamente.',
  'token'         => 'No se pudo completar la autenticación con Google. Intenta nuevamente.',
  'perfil'        => 'No se pudo obtener tu perfil de Google. Intenta nuevamente.',
  'servidor'      => 'Error del servidor. Intenta nuevamente.',
];
if (!empty($_GET['error']) && isset($erroresGoogle[$_GET['error']])) {
  $error = $erroresGoogle[$_GET['error']];
}

// ── Procesar formulario ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $correo = trim($_POST['correo']   ?? '');
  $clave  = trim($_POST['clave']    ?? '');

  // Validación básica
  if (empty($correo) || empty($clave)) {
    $error = 'Por favor ingresa tu correo y contraseña.';

  } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $error = 'El formato del correo no es válido.';

  } else {
    try {
      $db  = Database::conectar();
      $sql = 'SELECT id, nombre, apellido, correo, clave_cifrada, rol,
                     cuenta_activa, cuenta_verificada
              FROM   usuarios
              WHERE  correo = :correo
              LIMIT  1';

      $stmt = $db->prepare($sql);
      $stmt->execute([':correo' => $correo]);
      $usuario = $stmt->fetch();

      if (!$usuario) {
        $error = 'Correo o contraseña incorrectos.';

      } elseif (
        !password_verify($clave, $usuario['clave_cifrada']) &&
        $clave !== $usuario['clave_cifrada']
      ) {
        $error = 'Correo o contraseña incorrectos.';

      } elseif (!$usuario['cuenta_activa']) {
        $error = 'Tu cuenta ha sido suspendida. Contacta al administrador.';

      } elseif (!$usuario['cuenta_verificada']) {
        $error = 'Tu cuenta aún no ha sido verificada. Revisa tu correo.';

      } else {
        // ── Login exitoso ──

        // Regenerar ID de sesión por seguridad
        session_regenerate_id(true);

        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre']     = $usuario['nombre'];
        $_SESSION['apellido']   = $usuario['apellido'];
        $_SESSION['correo']     = $usuario['correo'];
        $_SESSION['rol']        = $usuario['rol'];

        // Actualizar último ingreso
        $upd = $db->prepare('UPDATE usuarios SET ultimo_ingreso = NOW() WHERE id = :id');
        $upd->execute([':id' => $usuario['id']]);

        // Guardar sesión en tabla sesiones_activas
        $token = bin2hex(random_bytes(32));
        $ins   = $db->prepare('
          INSERT INTO sesiones_activas (id, usuario_id, token_cifrado, ip_dispositivo, fecha_expiracion)
          VALUES (UUID(), :uid, :token, :ip, DATE_ADD(NOW(), INTERVAL 8 HOUR))
        ');
        $ins->execute([
          ':uid'   => $usuario['id'],
          ':token' => hash('sha256', $token),
          ':ip'    => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);

        // Guardar token en cookie segura (httpOnly)
        setcookie('pp_token', $token, [
          'expires'  => time() + (8 * 3600),
          'path'     => '/',
          'httponly' => true,
          'samesite' => 'Strict',
        ]);

        // Redirigir según rol
        if ($usuario['rol'] === 'admin') {
          header('Location: dashboardadmin.php');
        } else {
          header('Location: dashboardvendedor.php');
        }
        exit;
      }

    } catch (PDOException $e) {
      $error = 'Error al procesar el ingreso. Intenta nuevamente.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Iniciar sesión | PP Bienes Raíces</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>

  <div class="login-page">

    <!-- ── PANEL IZQUIERDO — decorativo ── -->
    <div class="login-panel-left">
      <div class="panel-content">

        <a href="../index.php" class="panel-logo">
          <img src="../assets/img/logo_PP_positivo.png" alt="PP Bienes Raíces">
        </a>

        <div class="panel-texto">
          <h2>Bienvenido de<br>vuelta a <em>PP</em></h2>
          <p>Gestiona tus propiedades, contratos y clientes desde un solo lugar con total seguridad.</p>
        </div>

        <!-- Decoración -->
        <div class="panel-orb panel-orb-1"></div>
        <div class="panel-orb panel-orb-2"></div>
        <div class="panel-orb panel-orb-3"></div>
        <div class="panel-dots"></div>
        <div class="panel-line"></div>

      </div>
    </div>

    <!-- ── PANEL DERECHO — formulario ── -->
    <div class="login-panel-right">
      <div class="login-form-wrap">

        <!-- Logo móvil -->
        <a href="../index.php" class="login-logo-mobile">
          <img src="../assets/img/Logo.jpeg" alt="PP Bienes Raíces">
        </a>

        <div class="login-header">
          <div class="login-header-badge">Acceso exclusivo</div>
          <h1>Iniciar sesión</h1>
          <p>Ingresa con tu correo y contraseña asignados por el sistema.</p>
        </div>

        <!-- Mensaje de error -->
        <?php if ($error): ?>
        <div class="login-alert" role="alert" id="loginAlert">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
          </svg>
          <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- Google Auth -->
          <a href="<?= google_auth_url() ?>" class="btn-google">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
              <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
              <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
              <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Continuar con Google
          </a>

          <!-- Divisor -->
          <div class="form-divider">o ingresa con tu correo</div>

        <!-- Formulario -->
        <form class="login-form" id="loginForm" method="POST" action="" novalidate>

          <!-- Correo -->
          <div class="form-group" id="groupCorreo">
            <label for="correo" class="form-label">Correo electrónico</label>
            <div class="input-wrap">
              <span class="input-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M3 4a2 2 0 00-2 2v1.161l8.441 4.221a1.25 1.25 0 001.118 0L19 7.162V6a2 2 0 00-2-2H3z"/>
                  <path d="M19 8.839l-7.77 3.885a2.75 2.75 0 01-2.46 0L1 8.839V14a2 2 0 002 2h14a2 2 0 002-2V8.839z"/>
                </svg>
              </span>
              <input
                type="email"
                id="correo"
                name="correo"
                class="form-input"
                placeholder="ejemplo@correo.com"
                value="<?= htmlspecialchars($correo) ?>"
                autocomplete="email"
                required
              >
            </div>
            <span class="form-error" id="errorCorreo"></span>
          </div>

          <!-- Contraseña -->
          <div class="form-group" id="groupClave">
            <label for="clave" class="form-label">
              Contraseña
              <a href="recuperar.php" class="forgot-link">¿Olvidaste tu contraseña?</a>
            </label>
            <div class="input-wrap">
              <span class="input-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
                </svg>
              </span>
              <input
                type="password"
                id="clave"
                name="clave"
                class="form-input"
                placeholder="Tu contraseña"
                autocomplete="current-password"
                required
              >
              <button type="button" class="toggle-pass" id="togglePass" aria-label="Mostrar contraseña" tabindex="-1">
                <!-- Ojo cerrado -->
                <svg id="eyeOff" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M3.28 2.22a.75.75 0 00-1.06 1.06l14.5 14.5a.75.75 0 101.06-1.06l-1.745-1.745a10.029 10.029 0 003.3-4.38 1.651 1.651 0 000-1.185A10.004 10.004 0 009.999 3a9.956 9.956 0 00-4.744 1.194L3.28 2.22zM7.752 6.69l1.092 1.092a2.5 2.5 0 013.374 3.373l1.091 1.092a4 4 0 00-5.557-5.557z" clip-rule="evenodd"/>
                  <path d="M10.748 13.93l2.523 2.523a9.987 9.987 0 01-3.27.547c-4.258 0-7.894-2.66-9.337-6.41a1.651 1.651 0 010-1.186A10.007 10.007 0 012.839 6.02L6.07 9.252a4 4 0 004.678 4.678z"/>
                </svg>
                <!-- Ojo abierto -->
                <svg id="eyeOn" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="display:none">
                  <path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"/>
                  <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                </svg>
              </button>
            </div>
            <span class="form-error" id="errorClave"></span>
          </div>

          <!-- Recordarme -->
          <div class="form-check">
            <label class="check-label">
              <input type="checkbox" name="recordar" id="recordar" class="check-input">
              <span class="check-box"></span>
              Recordarme por 7 días
            </label>
          </div>

          <!-- Botón submit -->
          <button type="submit" class="btn-submit" id="btnSubmit">
            <span class="btn-text">Ingresar al sistema</span>
            <span class="btn-loader" id="btnLoader" style="display:none">
              <svg class="spinner" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="3" stroke-dasharray="31.4" stroke-dashoffset="10"/>
              </svg>
              Ingresando...
            </span>
          </button>

        </form>

      </div>
    </div>

  </div>

  <script src="../assets/js/login.js"></script>
</body>
</html>