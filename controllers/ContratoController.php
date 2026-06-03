<?php
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
        $stmtNombre = $db->prepare("SELECT nombre_opcion FROM opciones_sistema WHERE id=:est LIMIT 1");
        $stmtNombre->execute([':est'=>$estadoId]);
        $nombre = $stmtNombre->fetchColumn();
        if ($ok) $model->historial($id, "Estado cambiado a: $nombre", $uid);

        // Si el nuevo estado es "Firmado", marcar la propiedad como Vendida
        if ($ok && $nombre === 'Firmado') {
            $contrato = $model->getById($id);
            if ($contrato && !empty($contrato['propiedad_id'])) {
                $estadoVendida = $db->query("SELECT id FROM opciones_sistema WHERE categoria='estado_publicacion' AND nombre_opcion='Vendida' LIMIT 1")->fetchColumn();
                if ($estadoVendida) {
                    $db->prepare("UPDATE propiedades SET estado_publicacion_id=:est WHERE id=:pid")
                       ->execute([':est'=>$estadoVendida, ':pid'=>$contrato['propiedad_id']]);
                    $model->historial($id, 'Propiedad marcada como Vendida por cambio de estado del contrato', $uid);
                }
            }
        }

        echo json_encode(['ok'=>$ok]);
    } catch(Exception $e) {
        echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);
    }
    exit;
}

echo json_encode(['ok'=>false,'msg'=>'Acción no válida']);