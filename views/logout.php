<?php
/**
 * LOGOUT
 * PP Bienes Raíces — views/auth/logout.php
 */

session_start();

// Eliminar sesión de la BD si existe
if (isset($_SESSION['usuario_id'])) {
  try {
    require_once __DIR__ . '/../config/database.php';
    $db   = Database::conectar();
    $stmt = $db->prepare('DELETE FROM sesiones_activas WHERE usuario_id = :uid');
    $stmt->execute([':uid' => $_SESSION['usuario_id']]);
  } catch (Exception $e) {
    // Continuar aunque falle
  }
}

// Destruir sesión
session_unset();
session_destroy();

// Eliminar cookie
setcookie('pp_token', '', [
  'expires'  => time() - 3600,
  'path'     => '/',
  'httponly' => true,
  'samesite' => 'Strict',
]);

header('Location: login.php?logout=1');
exit;