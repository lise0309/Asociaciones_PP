<?php
/**
 * PLANTILLA CONTROLLER — PP Bienes Raíces
 * controllers/PlantillaController.php
 * Solo admin puede subir/eliminar plantillas
 */
session_start();
require_once __DIR__ . '/../models/plantillamodel.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['ok'=>false,'msg'=>'No autorizado']); exit;
}

$uid   = $_SESSION['usuario_id'];
$rol   = $_SESSION['rol'] ?? 'vendedor';
$act   = $_GET['action'] ?? $_POST['accion'] ?? '';
$model = new PlantillaModel();

// ── LISTAR (todos) ───────────────────────────────────────
if ($act === 'listar') {
    $pl = $rol==='admin' ? $model->listar() : $model->listarActivas();
    echo json_encode(['ok'=>true,'plantillas'=>$pl]);
    exit;
}

// ── LISTAR ACTIVAS (para select del form) ────────────────
if ($act === 'activas') {
    echo json_encode(['ok'=>true,'plantillas'=>$model->listarActivas()]);
    exit;
}

// ── SUBIR (admin only) ───────────────────────────────────
if ($act === 'subir' && $_SERVER['REQUEST_METHOD']==='POST') {
    if ($rol !== 'admin') { echo json_encode(['ok'=>false,'msg'=>'Solo admin']); exit; }

    $nombre = trim($_POST['nombre_plantilla'] ?? '');
    if (!$nombre) { echo json_encode(['ok'=>false,'msg'=>'Nombre requerido']); exit; }

    if (empty($_FILES['plantilla']) || $_FILES['plantilla']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['ok'=>false,'msg'=>'Archivo requerido']); exit;
    }

    $ext  = strtolower(pathinfo($_FILES['plantilla']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['docx','doc','pdf'])) {
        echo json_encode(['ok'=>false,'msg'=>'Solo .docx, .doc o .pdf']); exit;
    }

    $dir  = dirname(__DIR__) . '/uploads/plantillas/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $archivo = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/','_', $_FILES['plantilla']['name']);
    if (!move_uploaded_file($_FILES['plantilla']['tmp_name'], $dir.$archivo)) {
        echo json_encode(['ok'=>false,'msg'=>'Error al subir archivo']); exit;
    }

    $id = $model->crear($nombre, 'uploads/plantillas/'.$archivo);
    echo json_encode($id ? ['ok'=>true,'msg'=>'Plantilla subida','id'=>$id] : ['ok'=>false,'msg'=>'Error en BD']);
    exit;
}

// ── TOGGLE ACTIVA (admin only) ───────────────────────────
if ($act === 'toggle' && $_SERVER['REQUEST_METHOD']==='POST') {
    if ($rol !== 'admin') { echo json_encode(['ok'=>false,'msg'=>'Solo admin']); exit; }
    $id = $_POST['id'] ?? '';
    echo json_encode(['ok'=>$model->toggleActiva($id)]);
    exit;
}

// ── ELIMINAR (admin only) ────────────────────────────────
if ($act === 'eliminar' && $_SERVER['REQUEST_METHOD']==='GET') {
    if ($rol !== 'admin') { echo json_encode(['ok'=>false,'msg'=>'Solo admin']); exit; }
    $id = $_GET['id'] ?? '';
    echo json_encode(['ok'=>$model->eliminar($id)]);
    exit;
}

echo json_encode(['ok'=>false,'msg'=>'Acción no válida']);