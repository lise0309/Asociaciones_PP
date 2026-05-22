<?php
/**
 * PLANTILLA CONTROLLER — PP Bienes Raíces
 * controllers/plantillacontroller.php
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

// ── LISTAR ───────────────────────────────────────────────
if ($act === 'listar') {
    $pl = $rol==='admin' ? $model->listar() : $model->listarActivas();
    echo json_encode(['ok'=>true,'plantillas'=>$pl]);
    exit;
}

// ── ACTIVAS ──────────────────────────────────────────────
if ($act === 'activas') {
    echo json_encode(['ok'=>true,'plantillas'=>$model->listarActivas()]);
    exit;
}

// ── SUBIR ────────────────────────────────────────────────
if ($act === 'subir' && $_SERVER['REQUEST_METHOD']==='POST') {
    if (strtolower($rol) !== 'admin') { echo json_encode(['ok'=>false,'msg'=>'Solo admin']); exit; }

    $nombre = trim($_POST['nombre_plantilla'] ?? '');
    if (!$nombre) { echo json_encode(['ok'=>false,'msg'=>'Nombre requerido']); exit; }

    if (empty($_FILES['plantilla']) || $_FILES['plantilla']['error'] !== UPLOAD_ERR_OK) {
        $msgs = [1=>'Archivo muy grande',2=>'Muy grande',3=>'Parcial',4=>'Sin archivo'];
        $code = $_FILES['plantilla']['error'] ?? -1;
        echo json_encode(['ok'=>false,'msg'=>$msgs[$code]??'Error al subir']);
        exit;
    }

    $ext = strtolower(pathinfo($_FILES['plantilla']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['docx','doc','pdf'])) {
        echo json_encode(['ok'=>false,'msg'=>'Solo .docx, .doc o .pdf']); exit;
    }

    $dir = dirname(__DIR__) . '/uploads/plantillas/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $archivo = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/','_',$_FILES['plantilla']['name']);
    if (!move_uploaded_file($_FILES['plantilla']['tmp_name'], $dir.$archivo)) {
        echo json_encode(['ok'=>false,'msg'=>'Error al guardar archivo']); exit;
    }

    $id = $model->crear($nombre, 'uploads/plantillas/'.$archivo);
    echo json_encode($id ? ['ok'=>true,'msg'=>'Plantilla subida','id'=>$id] : ['ok'=>false,'msg'=>'Error en BD']);
    exit;
}

// ── TOGGLE ───────────────────────────────────────────────
if ($act === 'toggle' && $_SERVER['REQUEST_METHOD']==='POST') {
    if (strtolower($rol) !== 'admin') { echo json_encode(['ok'=>false,'msg'=>'Solo admin']); exit; }
    $id = $_POST['id'] ?? '';
    echo json_encode(['ok'=>$model->toggleActiva($id)]);
    exit;
}

// ── ELIMINAR ─────────────────────────────────────────────
if ($act === 'eliminar' && $_SERVER['REQUEST_METHOD']==='GET') {
    if (strtolower($rol) !== 'admin') { echo json_encode(['ok'=>false,'msg'=>'Solo admin']); exit; }
    $id = $_GET['id'] ?? '';
    if (!$id) { echo json_encode(['ok'=>false,'msg'=>'ID requerido']); exit; }

    // Verificar si tiene contratos asociados (FK RESTRICT no deja borrar)
    try {
        $db    = Database::conectar();
        $count = $db->prepare("SELECT COUNT(*) FROM contratos WHERE plantilla_id = :id");
        $count->execute([':id' => $id]);
        $total = (int)$count->fetchColumn();

        if ($total > 0) {
            // Eliminar contratos asociados primero (solo si están anulados o en borrador)
            $contratos = $db->prepare("SELECT id, estado_contrato_id FROM contratos WHERE plantilla_id = :id");
            $contratos->execute([':id' => $id]);
            $lista = $contratos->fetchAll();

            // Verificar que todos sean borradores o anulados
            $estadosPermitidos = $db->query("SELECT id FROM opciones_sistema WHERE categoria='estado_contrato' AND nombre_opcion IN ('Borrador','Anulado')")->fetchAll(PDO::FETCH_COLUMN);

            $bloqueados = array_filter($lista, fn($c) => !in_array($c['estado_contrato_id'], $estadosPermitidos));

            if (!empty($bloqueados)) {
                echo json_encode([
                    'ok'  => false,
                    'msg' => "No se puede eliminar: tiene contratos activos (Enviado/Firmado). Desactívala en su lugar."
                ]);
                exit;
            }

            // Eliminar contratos en borrador/anulado
            foreach ($lista as $c) {
                // Eliminar historial primero (FK)
                $db->prepare("DELETE FROM historial_contrato WHERE contrato_id = :id")->execute([':id' => $c['id']]);
                // Eliminar firmas (FK)
                $db->prepare("DELETE FROM firmas_contrato WHERE contrato_id = :id")->execute([':id' => $c['id']]);
                // Eliminar contrato
                $db->prepare("DELETE FROM contratos WHERE id = :id")->execute([':id' => $c['id']]);
            }
        }

        // Eliminar archivo físico también
        $plantilla = $model->getById($id);
        if ($plantilla && !empty($plantilla['archivo_plantilla'])) {
            $rutaFisica = dirname(__DIR__) . '/' . ltrim($plantilla['archivo_plantilla'], '/');
            if (file_exists($rutaFisica)) @unlink($rutaFisica);
        }

        $ok = $model->eliminar($id);
        echo json_encode(['ok'=>$ok, 'msg'=> $ok ? 'Plantilla eliminada' : 'Error al eliminar']);

    } catch (Exception $e) {
        echo json_encode(['ok'=>false,'msg'=>'Error: '.$e->getMessage()]);
    }
    exit;
}

echo json_encode(['ok'=>false,'msg'=>'Acción no válida']);