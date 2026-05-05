<?php
/**
 * GOOGLE OAUTH CALLBACK
 * PP Bienes Raíces — views/auth/google_callback.php
 * Google redirige aquí después de que el usuario autoriza
 */

session_start();
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/google.php';

// ── Verificar state CSRF ──
if (empty($_GET['state']) || $_GET['state'] !== ($_SESSION['csrf_token'] ?? '')) {
  session_destroy();
  header('Location: ../views/login.php?error=csrf');
  exit;
}
unset($_SESSION['csrf_token']);

// ── Verificar que llegó el code ──
if (empty($_GET['code'])) {
  header('Location: ../views/login.php?error=cancelado');
  exit;
}

// ── Intercambiar code por token ──
$tokenData = google_get_token($_GET['code']);
if (empty($tokenData['access_token'])) {
  header('Location: ../views/login.php?error=token');
  exit;
}

// ── Obtener perfil del usuario ──
$googleUser = google_get_user($tokenData['access_token']);
if (empty($googleUser['email'])) {
  header('Location: ../views/login.php?error=perfil');
  exit;
}

$correoGoogle = $googleUser['email'];
$nombreGoogle = $googleUser['given_name']  ?? '';
$apellidoGoogle = $googleUser['family_name'] ?? '';

// ── Buscar si el correo existe en el sistema ──
try {
  $db   = Database::conectar();
  $stmt = $db->prepare('
    SELECT id, nombre, apellido, correo, rol, cuenta_activa, cuenta_verificada
    FROM   usuarios
    WHERE  correo = :correo
    LIMIT  1
  ');
  $stmt->execute([':correo' => $correoGoogle]);
  $usuario = $stmt->fetch();

  // El correo de Google NO está registrado en el sistema
  if (!$usuario) {
    header('Location: ../views/login.php?error=no_registrado');
    exit;
  }

  // Cuenta suspendida
  if (!$usuario['cuenta_activa']) {
    header('Location: ../views/login.php?error=suspendida');
    exit;
  }

  // ── Login exitoso con Google ──
  session_regenerate_id(true);

  $_SESSION['usuario_id'] = $usuario['id'];
  $_SESSION['nombre']     = $usuario['nombre'];
  $_SESSION['apellido']   = $usuario['apellido'];
  $_SESSION['correo']     = $usuario['correo'];
  $_SESSION['rol']        = $usuario['rol'];
  $_SESSION['via_google'] = true;

  // Actualizar último ingreso
  $upd = $db->prepare('UPDATE usuarios SET ultimo_ingreso = NOW() WHERE id = :id');
  $upd->execute([':id' => $usuario['id']]);

  // Registrar sesión
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

  setcookie('pp_token', $token, [
    'expires'  => time() + (8 * 3600),
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Strict',
  ]);

  // Redirigir según rol
  if ($usuario['rol'] === 'admin') {
    header('Location: ../views/dashboardadmin.php');
  } else {
    header('Location: ../views/dashboardvendedor.php');
  }
  exit;

} catch (PDOException $e) {
  header('Location: ../views/login.php?error=servidor');
  exit;
}