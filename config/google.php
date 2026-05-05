<?php
/**
 * GOOGLE OAUTH CONFIG
 * PP Bienes Raíces — config/google.php
 */

define('GOOGLE_CLIENT_ID',     '81898622975-ln9d52ar6fi4okg3nhdi1ekc2ih03706.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI',  'http://localhost/Asociaciones_PP/config/googlecallback.php');

// Scopes que pedimos a Google
define('GOOGLE_SCOPES', implode(' ', [
  'https://www.googleapis.com/auth/userinfo.email',
  'https://www.googleapis.com/auth/userinfo.profile',
  'openid',
]));

// URL base de Google OAuth
define('GOOGLE_AUTH_URL',  'https://accounts.google.com/o/oauth2/v2/auth');
define('GOOGLE_TOKEN_URL', 'https://oauth2.googleapis.com/token');
define('GOOGLE_USER_URL',  'https://www.googleapis.com/oauth2/v3/userinfo');

/**
 * Genera la URL de autorización de Google
 */
function google_auth_url(): string {
  $params = http_build_query([
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope'         => GOOGLE_SCOPES,
    'access_type'   => 'online',
    'state'         => csrf_token(),
    'prompt'        => 'select_account',
  ]);
  return GOOGLE_AUTH_URL . '?' . $params;
}

/**
 * Genera y guarda un token CSRF en sesión
 */
function csrf_token(): string {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
  }
  return $_SESSION['csrf_token'];
}

/**
 * Intercambia el code por un access_token
 */
function google_get_token(string $code): ?array {
  $ch = curl_init(GOOGLE_TOKEN_URL);
  curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS     => http_build_query([
      'code'          => $code,
      'client_id'     => GOOGLE_CLIENT_ID,
      'client_secret' => GOOGLE_CLIENT_SECRET,
      'redirect_uri'  => GOOGLE_REDIRECT_URI,
      'grant_type'    => 'authorization_code',
    ]),
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
  ]);
  $res = curl_exec($ch);
  curl_close($ch);
  return $res ? json_decode($res, true) : null;
}

/**
 * Obtiene el perfil del usuario desde Google
 */
function google_get_user(string $access_token): ?array {
  $ch = curl_init(GOOGLE_USER_URL);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $access_token],
  ]);
  $res = curl_exec($ch);
  curl_close($ch);
  return $res ? json_decode($res, true) : null;
}