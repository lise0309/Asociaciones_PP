<?php
/**
 * FIRMA CONTROLLER — PP Bienes Raíces
 * controllers/firmacontroller.php
 *
 * Acciones:
 *   GET  ?action=estado       → cuántas firmas hay en el contrato
 *   POST accion=firmar        → guardar imagen firma + código
 *   POST accion=completar     → cuando ambos firmaron, cerrar contrato y marcar propiedad vendida
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['ok' => false, 'msg' => 'No autorizado']); exit;
}

require_once __DIR__ . '/../config/database.php';
$db  = Database::conectar();
$uid = $_SESSION['usuario_id'];
$rol = $_SESSION['rol'] ?? 'vendedor';
$act = $_GET['action'] ?? $_POST['accion'] ?? '';

// ── ESTADO DE FIRMAS ─────────────────────────────────
if ($act === 'estado') {
    $cid = $_GET['contrato_id'] ?? '';
    if (!$cid) { echo json_encode(['ok' => false, 'msg' => 'ID requerido']); exit; }

    $stmt = $db->prepare("
        SELECT f.id, f.nombre_firmante, f.correo_firmante, f.fecha_firma,
               f.imagen_firma, os.nombre_opcion AS rol
        FROM firmas_contrato f
        JOIN opciones_sistema os ON os.id = f.rol_firmante_id
        WHERE f.contrato_id = :cid
        ORDER BY f.fecha_firma ASC
    ");
    $stmt->execute([':cid' => $cid]);
    $firmas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $firmado_vendedor  = false;
    $firmado_comprador = false;
    foreach ($firmas as $f) {
        if ($f['rol'] === 'Vendedor')  $firmado_vendedor  = true;
        if ($f['rol'] === 'Comprador') $firmado_comprador = true;
    }

    echo json_encode([
        'ok'                => true,
        'firmas'            => $firmas,
        'firmado_vendedor'  => $firmado_vendedor,
        'firmado_comprador' => $firmado_comprador,
        'completo'          => $firmado_vendedor && $firmado_comprador,
    ]);
    exit;
}

// ── GUARDAR FIRMA ────────────────────────────────────
if ($act === 'firmar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $cid          = $_POST['contrato_id']    ?? '';
    $imagen_b64   = $_POST['imagen_firma']   ?? '';  // base64 del canvas
    $rol_firma    = $_POST['rol_firma']      ?? '';  // 'Vendedor' o 'Comprador'
    $nombre       = trim($_POST['nombre_firmante']  ?? '');
    $correo       = trim($_POST['correo_firmante']  ?? '');

    if (!$cid || !$imagen_b64 || !$rol_firma || !$nombre || !$correo) {
        echo json_encode(['ok' => false, 'msg' => 'Datos incompletos']); exit;
    }

    // Verificar que el contrato existe y está Aprobado
    $contrato = $db->prepare("
        SELECT c.id, c.propiedad_id, os.nombre_opcion AS estado
        FROM contratos c
        JOIN opciones_sistema os ON os.id = c.estado_contrato_id
        WHERE c.id = :cid
    ");
    $contrato->execute([':cid' => $cid]);
    $c = $contrato->fetch(PDO::FETCH_ASSOC);

    if (!$c) { echo json_encode(['ok' => false, 'msg' => 'Contrato no encontrado']); exit; }
    if (!in_array($c['estado'], ['Aprobado', 'Firmado'])) {
        echo json_encode(['ok' => false, 'msg' => 'El contrato debe estar Aprobado para firmar']); exit;
    }

    // Verificar que este rol no ha firmado ya
    $rolId = $db->prepare("
        SELECT id FROM opciones_sistema
        WHERE categoria = 'rol_firmante' AND nombre_opcion = :r LIMIT 1
    ");
    $rolId->execute([':r' => $rol_firma]);
    $rolFirmanteId = $rolId->fetchColumn();
    if (!$rolFirmanteId) { echo json_encode(['ok' => false, 'msg' => 'Rol inválido']); exit; }

    $yaFirmo = $db->prepare("
        SELECT id FROM firmas_contrato
        WHERE contrato_id = :cid AND rol_firmante_id = :rid
    ");
    $yaFirmo->execute([':cid' => $cid, ':rid' => $rolFirmanteId]);
    if ($yaFirmo->fetch()) {
        echo json_encode(['ok' => false, 'msg' => 'Este firmante ya registró su firma']); exit;
    }

    // Guardar imagen firma en uploads/firmas/
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Asociaciones_PP/uploads/firmas/';
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

    $imgData  = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imagen_b64));
    $filename = 'firma_' . $cid . '_' . strtolower($rol_firma) . '_' . time() . '.png';
    $rutaFisica = $uploadDir . $filename;
    $rutaWeb    = '/Asociaciones_PP/uploads/firmas/' . $filename;

    if (!file_put_contents($rutaFisica, $imgData)) {
        echo json_encode(['ok' => false, 'msg' => 'Error al guardar la imagen']); exit;
    }

    // Insertar firma
    $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff),
        mt_rand(0,0x0fff)|0x4000, mt_rand(0,0x3fff)|0x8000,
        mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff)
    );
    $db->prepare("
        INSERT INTO firmas_contrato
            (id, contrato_id, nombre_firmante, correo_firmante,
             rol_firmante_id, ip_al_firmar, imagen_firma, fecha_firma)
        VALUES (:id, :cid, :nombre, :correo, :rid, :ip, :img, NOW())
    ")->execute([
        ':id'     => $uuid,
        ':cid'    => $cid,
        ':nombre' => $nombre,
        ':correo' => $correo,
        ':rid'    => $rolFirmanteId,
        ':ip'     => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
        ':img'    => $rutaWeb,
    ]);

    // Revisar si ya firmaron ambos
    $totalFirmas = $db->prepare("
        SELECT COUNT(DISTINCT f.rol_firmante_id)
        FROM firmas_contrato f
        JOIN opciones_sistema os ON os.id = f.rol_firmante_id
        WHERE f.contrato_id = :cid
          AND os.nombre_opcion IN ('Vendedor','Comprador')
          AND f.fecha_firma IS NOT NULL
    ");
    $totalFirmas->execute([':cid' => $cid]);
    $totalFirmaron = (int)$totalFirmas->fetchColumn();

    $ambos_firmaron = ($totalFirmaron >= 2);

    if ($ambos_firmaron) {
        // Cambiar contrato a "Firmado"
        $estadoFirmado = $db->query("
            SELECT id FROM opciones_sistema
            WHERE categoria='estado_contrato' AND nombre_opcion='Firmado' LIMIT 1
        ")->fetchColumn();

        $db->prepare("UPDATE contratos SET estado_contrato_id = :est WHERE id = :cid")
           ->execute([':est' => $estadoFirmado, ':cid' => $cid]);

        // Cambiar propiedad a "Vendida" — desaparece del index
        $estadoVendida = $db->query("
            SELECT id FROM opciones_sistema
            WHERE categoria='estado_publicacion' AND nombre_opcion='Vendida' LIMIT 1
        ")->fetchColumn();

        if ($estadoVendida) {
            $db->prepare("UPDATE propiedades SET estado_publicacion_id = :est WHERE id = :pid")
               ->execute([':est' => $estadoVendida, ':pid' => $c['propiedad_id']]);
        }

        // Registrar en historial
        $db->prepare("
            INSERT INTO historial_contrato (id, contrato_id, accion_realizada, quien_lo_hizo, ip_accion)
            VALUES (UUID(), :cid, 'Contrato firmado por ambas partes — propiedad marcada como Vendida', :quien, :ip)
        ")->execute([
            ':cid'   => $cid,
            ':quien' => $_SESSION['nombre'] ?? 'Sistema',
            ':ip'    => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
        ]);
    }

    echo json_encode([
        'ok'            => true,
        'msg'           => 'Firma registrada',
        'ambos_firmaron'=> $ambos_firmaron,
    ]);
    exit;
}

echo json_encode(['ok' => false, 'msg' => 'Acción no válida']);