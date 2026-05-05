<?php
/**
 * DATABASE CONFIG
 * PP Bienes Raíces — Asociaciones Portillo Pocasangre
 * config/database.php
 */

define('DB_HOST',    'localhost');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_NAME',    'pp_bienes_raices');
define('DB_CHARSET', 'utf8mb4');

/**
 * Clase Database — Singleton con PDO
 * Uso: $db = Database::conectar();
 */
class Database {

  private static ?PDO $conexion = null;

  public static function conectar(): PDO {
    if (self::$conexion === null) {
      try {
        $dsn = 'mysql:host=' . DB_HOST
             . ';dbname='    . DB_NAME
             . ';charset='   . DB_CHARSET;

        $opciones = [
          PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        self::$conexion = new PDO($dsn, DB_USER, DB_PASS, $opciones);

      } catch (PDOException $e) {
        // En producción cambiar por log de error, nunca mostrar detalles
        http_response_code(500);
        die(json_encode([
          'error' => 'No se pudo conectar a la base de datos.',
          'detalle' => $e->getMessage() // quitar en producción
        ]));
      }
    }
    return self::$conexion;
  }

  // Evitar clonación e instanciación directa
  private function __construct() {}
  private function __clone()    {}
}