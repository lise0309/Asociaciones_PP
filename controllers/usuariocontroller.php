<?php
/**
 * USUARIO CONTROLLER
 * PP Bienes Raíces — controllers/UsuarioController.php
 */

require_once __DIR__ . '/../models/UsuarioModel.php';

class UsuarioController {

  private UsuarioModel $model;

  public function __construct() {
    $this->model = new UsuarioModel();
  }

  /* ════════════════════════════════════════
     HELPERS
  ════════════════════════════════════════ */

  private function json(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
  }

  private function soloAdmin(): void {
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
      $this->json(['ok' => false, 'msg' => 'Acceso no autorizado.'], 403);
    }
  }

  private function limpiar(string $valor): string {
    return htmlspecialchars(strip_tags(trim($valor)));
  }

  /* ════════════════════════════════════════
     LISTAR — GET /controllers/UsuarioController.php?accion=listar
  ════════════════════════════════════════ */

  public function listar(): void {
    $this->soloAdmin();

    $filtros = [
      'rol'               => $_GET['rol']               ?? '',
      'cuenta_activa'     => $_GET['cuenta_activa']     ?? 'todos',
      'cuenta_verificada' => $_GET['cuenta_verificada'] ?? 'todos',
      'buscar'            => $_GET['buscar']             ?? '',
    ];

    $usuarios = $this->model->obtenerTodos($filtros);
    $stats    = $this->model->estadisticas();

    $this->json(['ok' => true, 'usuarios' => $usuarios, 'stats' => $stats]);
  }

  /* ════════════════════════════════════════
     OBTENER UNO — GET ?accion=obtener&id=
  ════════════════════════════════════════ */

  public function obtener(): void {
    $this->soloAdmin();

    $id      = $_GET['id'] ?? '';
    $usuario = $this->model->obtenerPorId($id);

    if (!$usuario) {
      $this->json(['ok' => false, 'msg' => 'Usuario no encontrado.'], 404);
    }

    // No exponer la clave
    unset($usuario['clave_cifrada']);
    $this->json(['ok' => true, 'usuario' => $usuario]);
  }

  /* ════════════════════════════════════════
     CREAR — POST ?accion=crear
  ════════════════════════════════════════ */

  public function crear(): void {
    $this->soloAdmin();

    $datos = [
      'nombre'             => $this->limpiar($_POST['nombre']    ?? ''),
      'apellido'           => $this->limpiar($_POST['apellido']  ?? ''),
      'correo'             => $this->limpiar($_POST['correo']    ?? ''),
      'clave'              => $_POST['clave']                    ?? '',
      'rol'                => $_POST['rol']                      ?? 'vendedor',
      'telefono'           => $this->limpiar($_POST['telefono']  ?? ''),
      'descripcion'        => $this->limpiar($_POST['descripcion'] ?? ''),
      'cuenta_verificada'  => isset($_POST['cuenta_verificada']) ? 1 : 0,
    ];

    // Validaciones
    $errores = $this->validar($datos);
    if ($errores) {
      $this->json(['ok' => false, 'errores' => $errores], 422);
    }

    if ($this->model->correoExiste($datos['correo'])) {
      $this->json(['ok' => false, 'msg' => 'Este correo ya está registrado.'], 409);
    }

    if ($this->model->crear($datos)) {
      $this->json(['ok' => true, 'msg' => 'Usuario creado correctamente.']);
    }

    $this->json(['ok' => false, 'msg' => 'Error al crear el usuario.'], 500);
  }

  /* ════════════════════════════════════════
     EDITAR — POST ?accion=editar&id=
  ════════════════════════════════════════ */

  public function editar(): void {
    $this->soloAdmin();

    $id = $_GET['id'] ?? '';
    if (!$this->model->obtenerPorId($id)) {
      $this->json(['ok' => false, 'msg' => 'Usuario no encontrado.'], 404);
    }

    $datos = [
      'nombre'             => $this->limpiar($_POST['nombre']    ?? ''),
      'apellido'           => $this->limpiar($_POST['apellido']  ?? ''),
      'correo'             => $this->limpiar($_POST['correo']    ?? ''),
      'clave'              => $_POST['clave']                    ?? '',
      'rol'                => $_POST['rol']                      ?? 'vendedor',
      'telefono'           => $this->limpiar($_POST['telefono']  ?? ''),
      'descripcion'        => $this->limpiar($_POST['descripcion'] ?? ''),
      'cuenta_verificada'  => isset($_POST['cuenta_verificada']) ? 1 : 0,
      'cuenta_activa'      => isset($_POST['cuenta_activa'])     ? 1 : 0,
    ];

    // Validar sin requerir clave (puede estar vacía en edición)
    $errores = $this->validar($datos, false);
    if ($errores) {
      $this->json(['ok' => false, 'errores' => $errores], 422);
    }

    if ($this->model->correoExiste($datos['correo'], $id)) {
      $this->json(['ok' => false, 'msg' => 'Este correo ya está en uso.'], 409);
    }

    if ($this->model->editar($id, $datos)) {
      $this->json(['ok' => true, 'msg' => 'Usuario actualizado correctamente.']);
    }

    $this->json(['ok' => false, 'msg' => 'Error al actualizar el usuario.'], 500);
  }

  /* ════════════════════════════════════════
     CAMBIAR ESTADO — POST ?accion=estado&id=
  ════════════════════════════════════════ */

  public function cambiarEstado(): void {
    $this->soloAdmin();

    $id     = $_GET['id']    ?? '';
    $activo = (int) ($_POST['activo'] ?? 0);

    // No puede suspenderse a sí mismo
    if ($id === ($_SESSION['usuario_id'] ?? '')) {
      $this->json(['ok' => false, 'msg' => 'No puedes suspender tu propia cuenta.'], 403);
    }

    if ($this->model->cambiarEstado($id, $activo)) {
      $msg = $activo ? 'Cuenta reactivada.' : 'Cuenta suspendida.';
      $this->json(['ok' => true, 'msg' => $msg]);
    }

    $this->json(['ok' => false, 'msg' => 'Error al cambiar el estado.'], 500);
  }

  /* ════════════════════════════════════════
     VERIFICAR — POST ?accion=verificar&id=
  ════════════════════════════════════════ */

  public function verificar(): void {
    $this->soloAdmin();

    $id = $_GET['id'] ?? '';

    if ($this->model->verificar($id)) {
      $this->json(['ok' => true, 'msg' => 'Usuario verificado correctamente.']);
    }

    $this->json(['ok' => false, 'msg' => 'Error al verificar el usuario.'], 500);
  }

  /* ════════════════════════════════════════
     ELIMINAR — POST ?accion=eliminar&id=
  ════════════════════════════════════════ */

  public function eliminar(): void {
    $this->soloAdmin();

    $id = $_GET['id'] ?? '';

    // No puede eliminarse a sí mismo
    if ($id === ($_SESSION['usuario_id'] ?? '')) {
      $this->json(['ok' => false, 'msg' => 'No puedes eliminar tu propia cuenta.'], 403);
    }

    if ($this->model->eliminar($id)) {
      $this->json(['ok' => true, 'msg' => 'Usuario eliminado correctamente.']);
    }

    $this->json(['ok' => false, 'msg' => 'Error al eliminar el usuario.'], 500);
  }

  /* ════════════════════════════════════════
     VALIDACIONES
  ════════════════════════════════════════ */

  private function validar(array $datos, bool $claveRequerida = true): array {
    $errores = [];

    if (empty($datos['nombre']))   $errores['nombre']   = 'El nombre es obligatorio.';
    if (empty($datos['apellido'])) $errores['apellido'] = 'El apellido es obligatorio.';

    if (empty($datos['correo'])) {
      $errores['correo'] = 'El correo es obligatorio.';
    } elseif (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
      $errores['correo'] = 'El formato del correo no es válido.';
    }

    if ($claveRequerida && empty($datos['clave'])) {
      $errores['clave'] = 'La contraseña es obligatoria.';
    } elseif (!empty($datos['clave']) && strlen($datos['clave']) < 6) {
      $errores['clave'] = 'La contraseña debe tener al menos 6 caracteres.';
    }

    if (!in_array($datos['rol'], ['admin', 'vendedor'])) {
      $errores['rol'] = 'El rol no es válido.';
    }

    return $errores;
  }

  /* ════════════════════════════════════════
     ROUTER — punto de entrada único
  ════════════════════════════════════════ */

  public static function manejar(): void {
    session_start();

    $accion = $_GET['accion'] ?? '';
    $metodo = $_SERVER['REQUEST_METHOD'];

    $ctrl = new self();

    match(true) {
      $accion === 'listar'   && $metodo === 'GET'  => $ctrl->listar(),
      $accion === 'obtener'  && $metodo === 'GET'  => $ctrl->obtener(),
      $accion === 'crear'    && $metodo === 'POST' => $ctrl->crear(),
      $accion === 'editar'   && $metodo === 'POST' => $ctrl->editar(),
      $accion === 'estado'   && $metodo === 'POST' => $ctrl->cambiarEstado(),
      $accion === 'verificar'&& $metodo === 'POST' => $ctrl->verificar(),
      $accion === 'eliminar' && $metodo === 'POST' => $ctrl->eliminar(),
      default => (function() {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'msg' => 'Acción no válida.']);
        exit;
      })()
    };
  }
}

// Ejecutar solo si se llama directamente
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
  UsuarioController::manejar();
}