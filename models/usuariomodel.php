<?php
/**
 * USUARIO MODEL
 * PP Bienes Raíces — models/UsuarioModel.php
 */

require_once __DIR__ . '/../config/database.php';

class UsuarioModel {

  private PDO $db;

  public function __construct() {
    $this->db = Database::conectar();
  }

  /* ════════════════════════════════════════
     LISTAR
  ════════════════════════════════════════ */

  /**
   * Obtener todos los usuarios con filtros opcionales
   */
  public function obtenerTodos(array $filtros = []): array {
    $where  = ['1=1'];
    $params = [];

    if (!empty($filtros['rol'])) {
      $where[]        = 'rol = :rol';
      $params[':rol'] = $filtros['rol'];
    }

    if (!empty($filtros['cuenta_activa']) && $filtros['cuenta_activa'] !== 'todos') {
      $where[]               = 'cuenta_activa = :activa';
      $params[':activa']     = $filtros['cuenta_activa'] === 'activo' ? 1 : 0;
    }

    if (!empty($filtros['cuenta_verificada']) && $filtros['cuenta_verificada'] !== 'todos') {
      $where[]                    = 'cuenta_verificada = :verificada';
      $params[':verificada']      = $filtros['cuenta_verificada'] === 'verificado' ? 1 : 0;
    }

    if (!empty($filtros['buscar'])) {
      $where[]           = '(nombre LIKE :buscar OR apellido LIKE :buscar OR correo LIKE :buscar)';
      $params[':buscar'] = '%' . $filtros['buscar'] . '%';
    }

    $sql = 'SELECT id, nombre, apellido, correo, rol,
                   telefono, foto_perfil,
                   cuenta_verificada, cuenta_activa,
                   fecha_registro, ultimo_ingreso
            FROM   usuarios
            WHERE  ' . implode(' AND ', $where) . '
            ORDER BY fecha_registro DESC';

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
  }

  /**
   * Obtener un usuario por ID
   */
  public function obtenerPorId(string $id): ?array {
    $stmt = $this->db->prepare('
      SELECT id, nombre, apellido, correo, rol,
             telefono, descripcion_personal, foto_perfil,
             cuenta_verificada, cuenta_activa,
             fecha_registro, ultimo_ingreso
      FROM   usuarios
      WHERE  id = :id
      LIMIT  1
    ');
    $stmt->execute([':id' => $id]);
    $usuario = $stmt->fetch();
    return $usuario ?: null;
  }

  /**
   * Verificar si un correo ya existe (excluyendo un ID)
   */
  public function correoExiste(string $correo, string $excluirId = ''): bool {
    $sql    = 'SELECT COUNT(*) FROM usuarios WHERE correo = :correo';
    $params = [':correo' => $correo];

    if ($excluirId) {
      $sql             .= ' AND id != :id';
      $params[':id']    = $excluirId;
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn() > 0;
  }

  /* ════════════════════════════════════════
     CREAR / EDITAR / ELIMINAR
  ════════════════════════════════════════ */

  /**
   * Crear nuevo usuario
   */
  public function crear(array $datos): bool {
    $stmt = $this->db->prepare('
      INSERT INTO usuarios (
        id, nombre, apellido, correo, clave_cifrada,
        rol, telefono, descripcion_personal,
        cuenta_verificada, cuenta_activa, fecha_registro
      ) VALUES (
        UUID(), :nombre, :apellido, :correo, :clave,
        :rol, :telefono, :descripcion,
        :verificada, :activa, NOW()
      )
    ');

    return $stmt->execute([
      ':nombre'      => $datos['nombre'],
      ':apellido'    => $datos['apellido'],
      ':correo'      => $datos['correo'],
      ':clave'       => password_hash($datos['clave'], PASSWORD_BCRYPT),
      ':rol'         => $datos['rol'],
      ':telefono'    => $datos['telefono']    ?? null,
      ':descripcion' => $datos['descripcion'] ?? null,
      ':verificada'  => $datos['cuenta_verificada'] ?? 0,
      ':activa'      => 1,
    ]);
  }

  /**
   * Editar usuario existente
   */
  public function editar(string $id, array $datos): bool {
    // Si viene nueva clave la ciframos, sino no la tocamos
    if (!empty($datos['clave'])) {
      $sql = '
        UPDATE usuarios SET
          nombre             = :nombre,
          apellido           = :apellido,
          correo             = :correo,
          clave_cifrada      = :clave,
          rol                = :rol,
          telefono           = :telefono,
          descripcion_personal = :descripcion,
          cuenta_verificada  = :verificada,
          cuenta_activa      = :activa
        WHERE id = :id
      ';
      $params = [
        ':clave' => password_hash($datos['clave'], PASSWORD_BCRYPT),
      ];
    } else {
      $sql = '
        UPDATE usuarios SET
          nombre             = :nombre,
          apellido           = :apellido,
          correo             = :correo,
          rol                = :rol,
          telefono           = :telefono,
          descripcion_personal = :descripcion,
          cuenta_verificada  = :verificada,
          cuenta_activa      = :activa
        WHERE id = :id
      ';
      $params = [];
    }

    $params += [
      ':nombre'      => $datos['nombre'],
      ':apellido'    => $datos['apellido'],
      ':correo'      => $datos['correo'],
      ':rol'         => $datos['rol'],
      ':telefono'    => $datos['telefono']    ?? null,
      ':descripcion' => $datos['descripcion'] ?? null,
      ':verificada'  => $datos['cuenta_verificada'] ?? 0,
      ':activa'      => $datos['cuenta_activa'] ?? 1,
      ':id'          => $id,
    ];

    $stmt = $this->db->prepare($sql);
    return $stmt->execute($params);
  }

  /**
   * Cambiar estado activo/suspendido
   */
  public function cambiarEstado(string $id, int $activo): bool {
    $stmt = $this->db->prepare('
      UPDATE usuarios SET cuenta_activa = :activo WHERE id = :id
    ');
    return $stmt->execute([':activo' => $activo, ':id' => $id]);
  }

  /**
   * Verificar cuenta
   */
  public function verificar(string $id): bool {
    $stmt = $this->db->prepare('
      UPDATE usuarios SET cuenta_verificada = 1 WHERE id = :id
    ');
    return $stmt->execute([':id' => $id]);
  }

  /**
   * Eliminar usuario
   */
  public function eliminar(string $id): bool {
    $stmt = $this->db->prepare('DELETE FROM usuarios WHERE id = :id');
    return $stmt->execute([':id' => $id]);
  }

  /* ════════════════════════════════════════
     ESTADÍSTICAS
  ════════════════════════════════════════ */

  /**
   * Conteos para los KPIs del panel
   */
  public function estadisticas(): array {
    $stmt = $this->db->query('
      SELECT
        COUNT(*)                                            AS total,
        SUM(rol = "vendedor" AND cuenta_verificada = 1)    AS agentes_verificados,
        SUM(rol = "vendedor" AND cuenta_verificada = 0
            AND cuenta_activa = 1)                         AS pendientes,
        SUM(cuenta_activa = 0)                             AS suspendidos
      FROM usuarios
    ');
    return $stmt->fetch();
  }
}