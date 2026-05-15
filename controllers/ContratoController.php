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
    $contratos = $model->listar($uid, $rol);
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

echo json_encode(['ok'=>false,'msg'=>'Acción no válida']);