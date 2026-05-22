<?php
<<<<<<< HEAD
session_start();
require_once __DIR__ . '/../models/ContratoModel.php';

if (!isset($_SESSION['usuario_id'])) {
    die("No hay sesión iniciada");
}

$model = new ContratoModel();
$action = $_POST['accion'] ?? $_GET['action'] ?? '';

// CREAR CONTRATO
if ($action === 'crear') {
    $errores = [];
    if (empty($_POST['propiedad_id'])) $errores[] = "Propiedad requerida";
    if (empty($_POST['nombre_comprador'])) $errores[] = "Nombre requerido";
    if (empty($_POST['correo_comprador'])) $errores[] = "Correo requerido";
    if (empty($_POST['dui_comprador'])) $errores[] = "DUI requerido";
    if (empty($_POST['monto_acordado']) || floatval($_POST['monto_acordado']) <= 0) $errores[] = "Monto inválido";
    
    if (!empty($errores)) {
        $_SESSION['error_mensaje'] = implode("<br>", $errores);
        header('Location: ../views/contrato.php');
        exit;
    }
    
    $datos = [
        'propiedad_id' => $_POST['propiedad_id'],
        'vendedor_id' => $_SESSION['usuario_id'],
        'plantilla_id' => null,
        'nombre_comprador' => trim($_POST['nombre_comprador']),
        'correo_comprador' => trim($_POST['correo_comprador']),
        'dui_comprador' => trim($_POST['dui_comprador']),
        'monto_acordado' => floatval($_POST['monto_acordado']),
        'moneda' => $_POST['moneda'] ?? 'USD',
        'tipo_contrato_id' => intval($_POST['tipo_contrato_id'] ?? 14)
    ];
    
    $resultado = $model->crear($datos);
    $_SESSION[$resultado ? 'mensaje' : 'error_mensaje'] = $resultado ? "✅ Contrato creado" : "❌ Error al crear";
    header('Location: ../views/contrato.php');
    exit;
}

// ELIMINAR CONTRATO
if ($action === 'eliminar' && isset($_GET['id'])) {
    $resultado = $model->eliminarContrato($_GET['id'], $_SESSION['usuario_id']);
    $_SESSION[$resultado['success'] ? 'mensaje' : 'error_mensaje'] = $resultado['message'] ?? $resultado['error'];
    header('Location: ../views/contrato.php');
    exit;
}

// LISTAR
if ($action === 'listar') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'contratos' => $model->getContratosByVendedor($_SESSION['usuario_id'])]);
    exit;
}

// GENERAR Y DESCARGAR
if ($action === 'generar' && isset($_GET['id'])) {
    $resultado = $model->generarDocumento($_GET['id']);
    
    if ($resultado['success']) {
        $archivo = $resultado['ruta'];
        if (file_exists($archivo)) {
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="' . $resultado['nombre'] . '"');
            header('Content-Length: ' . filesize($archivo));
            readfile($archivo);
            exit;
        }
    }
    echo "Error: " . ($resultado['error'] ?? 'No se pudo generar el documento');
    exit;
}

header('Location: ../views/contrato.php');
exit;
?>
=======
/**
 * CONTRATO CONTROLLER — PP Bienes Raíces
 * controllers/ContratoController.php
 */
session_start();
require_once __DIR__ . '/../models/contratomodel.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['ok'=>false,'msg'=>'No autorizado']); exit;
}

$uid  = $_SESSION['usuario_id'];
$rol  = $_SESSION['rol'] ?? 'vendedor';
$act  = $_GET['action'] ?? $_POST['accion'] ?? '';
$model = new ContratoModel();

// ── LISTAR ──────────────────────────────────────────────
if ($act === 'listar' && $_SERVER['REQUEST_METHOD']==='GET') {
    // Filtros para admin
    $filtros = [
        'vendedor' => $_GET['vendedor'] ?? '',
        'estado'   => $_GET['estado']   ?? '',
        'tipo'     => $_GET['tipo']      ?? '',
        'buscar'   => $_GET['buscar']    ?? '',
    ];
    $contratos = $model->listar($uid, strtolower($rol), $filtros);
    echo json_encode(['ok'=>true,'contratos'=>$contratos]);
    exit;
}

// ── CREAR ───────────────────────────────────────────────
if ($act === 'crear' && $_SERVER['REQUEST_METHOD']==='POST') {
    $req = ['propiedad_id','plantilla_id','nombre_comprador','correo_comprador','dui_comprador','monto_acordado','tipo_contrato_id'];
    foreach ($req as $campo) {
        if (empty($_POST[$campo])) {
            echo json_encode(['ok'=>false,'msg'=>"Campo requerido: $campo"]); exit;
        }
    }
    $datos = array_merge($_POST, ['vendedor_id'=>$uid]);
    $id = $model->crear($datos);
    echo json_encode($id
        ? ['ok'=>true,'msg'=>'Contrato creado','id'=>$id]
        : ['ok'=>false,'msg'=>'Error al crear contrato']);
    exit;
}

// ── ELIMINAR / ANULAR ────────────────────────────────────
if ($act === 'eliminar' && $_SERVER['REQUEST_METHOD']==='GET') {
    $id = $_GET['id'] ?? '';
    $ok = $id ? $model->eliminar($id, $uid) : false;
    echo json_encode(['ok'=>$ok,'msg'=>$ok?'OK':'Error']);
    exit;
}

// ── VER DETALLE ──────────────────────────────────────────
if ($act === 'ver' && $_SERVER['REQUEST_METHOD']==='GET') {
    $id = $_GET['id'] ?? '';
    $c  = $id ? $model->getById($id) : null;
    echo json_encode($c ? ['ok'=>true,'contrato'=>$c] : ['ok'=>false,'msg'=>'No encontrado']);
    exit;
}

// ── LISTAR ESTADOS ───────────────────────────────────────
if ($act === 'estados' && $_SERVER['REQUEST_METHOD']==='GET') {
    $db      = Database::conectar();
    $estados = $db->query("SELECT id, nombre_opcion, valor_extra FROM opciones_sistema WHERE categoria='estado_contrato' AND disponible=1 ORDER BY id")->fetchAll();
    echo json_encode(['ok'=>true,'estados'=>$estados]);
    exit;
}

// ── CAMBIAR ESTADO ───────────────────────────────────────
if ($act === 'estado' && $_SERVER['REQUEST_METHOD']==='POST') {
    $id        = $_POST['id']        ?? '';
    $estadoId  = (int)($_POST['estado_id'] ?? 0);
    if (!$id || !$estadoId) { echo json_encode(['ok'=>false,'msg'=>'Datos incompletos']); exit; }

    try {
        $db = Database::conectar();
        // Verificar que el contrato pertenece al usuario (o es admin)
        $check = $db->prepare("SELECT id FROM contratos WHERE id=:id AND (vendedor_id=:uid OR :rol='admin')");
        $check->execute([':id'=>$id, ':uid'=>$uid, ':rol'=>$rol]);
        if (!$check->fetch()) { echo json_encode(['ok'=>false,'msg'=>'Sin permiso']); exit; }

        $ok = $db->prepare("UPDATE contratos SET estado_contrato_id=:est WHERE id=:id")
                 ->execute([':est'=>$estadoId, ':id'=>$id]);

        // Obtener nombre del nuevo estado para historial
        $nombre = $db->query("SELECT nombre_opcion FROM opciones_sistema WHERE id=$estadoId LIMIT 1")->fetchColumn();
        if ($ok) $model->historial($id, "Estado cambiado a: $nombre", $uid);

        echo json_encode(['ok'=>$ok]);
    } catch(Exception $e) {
        echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);
    }
    exit;
}

echo json_encode(['ok'=>false,'msg'=>'Acción no válida']);
>>>>>>> origin/Scrum1
