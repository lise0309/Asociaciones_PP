<?php
/**
 * FIRMA CONTROLLER — PP Bienes Raíces
 * controllers/firmacontroller.php
 *
 * CORRECCIONES APLICADAS:
 *  1. Query de rol_firmante_id filtra por categoria='rol_firmante' para evitar
 *     colisiones con los IDs duplicados (22/28 Vendedor, 23/29 Comprador).
 *  2. Query de verificación "ya firmó" también filtra por categoria.
 *  3. Query de conteo de ambos firmantes también filtra por categoria.
 *  4. Validación de estado acepta 'Aprobado' Y 'Firmado' para firmar.
 *
 * Acciones:
 *   GET  ?action=estado        → estado de firmas del contrato
 *   POST accion=firmar         → guardar imagen de firma
 */

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['ok' => false, 'msg' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

$db  = Database::conectar();
$uid = $_SESSION['usuario_id'];
$rol = $_SESSION['rol'] ?? 'vendedor';
$act = $_GET['action'] ?? $_POST['accion'] ?? '';

/* ══════════════════════════════════════════════════════════════
   ESTADO DE FIRMAS
══════════════════════════════════════════════════════════════ */
if ($act === 'estado') {
    $cid = $_GET['contrato_id'] ?? '';
    if (!$cid) {
        echo json_encode(['ok' => false, 'msg' => 'ID requerido']);
        exit;
    }

    $stmt = $db->prepare("
        SELECT f.id,
               f.nombre_firmante,
               f.correo_firmante,
               f.fecha_firma,
               f.imagen_firma,
               os.nombre_opcion AS rol
        FROM   firmas_contrato f
        JOIN   opciones_sistema os ON os.id = f.rol_firmante_id
        WHERE  f.contrato_id = :cid
          AND  os.categoria  = 'rol_firmante'
        ORDER  BY f.fecha_firma ASC
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

/* ══════════════════════════════════════════════════════════════
   GUARDAR FIRMA
══════════════════════════════════════════════════════════════ */
if ($act === 'firmar' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $cid        = $_POST['contrato_id']     ?? '';
    $imagen_b64 = $_POST['imagen_firma']    ?? '';
    $rol_firma  = $_POST['rol_firma']       ?? '';  // 'Vendedor' o 'Comprador'
    $nombre     = trim($_POST['nombre_firmante'] ?? '');
    $correo     = trim($_POST['correo_firmante'] ?? '');

    if (!$cid || !$imagen_b64 || !$rol_firma || !$nombre || !$correo) {
        echo json_encode(['ok' => false, 'msg' => 'Datos incompletos']);
        exit;
    }

    // Verificar que el contrato existe y tiene un estado que permite firmar
    $stmtC = $db->prepare("
        SELECT c.id, c.propiedad_id, os.nombre_opcion AS estado
        FROM   contratos c
        JOIN   opciones_sistema os ON os.id = c.estado_contrato_id
        WHERE  c.id = :cid
    ");
    $stmtC->execute([':cid' => $cid]);
    $c = $stmtC->fetch(PDO::FETCH_ASSOC);

    if (!$c) {
        echo json_encode(['ok' => false, 'msg' => 'Contrato no encontrado']);
        exit;
    }
    if (!in_array($c['estado'], ['Aprobado', 'Firmado'], true)) {
        echo json_encode(['ok' => false, 'msg' => 'El contrato debe estar Aprobado para poder firmar']);
        exit;
    }

    // Obtener el ID del rol firmante — CORRECCIÓN: filtrar por categoria
    $stmtRol = $db->prepare("
        SELECT id
        FROM   opciones_sistema
        WHERE  categoria     = 'rol_firmante'
          AND  nombre_opcion = :r
        ORDER  BY id ASC
        LIMIT  1
    ");
    $stmtRol->execute([':r' => $rol_firma]);
    $rolFirmanteId = $stmtRol->fetchColumn();

    if (!$rolFirmanteId) {
        echo json_encode(['ok' => false, 'msg' => 'Rol de firmante inválido: ' . $rol_firma]);
        exit;
    }

    // Verificar que este rol no ha firmado ya — CORRECCIÓN: filtrar por categoria
    $stmtYa = $db->prepare("
        SELECT f.id
        FROM   firmas_contrato f
        JOIN   opciones_sistema os ON os.id = f.rol_firmante_id
        WHERE  f.contrato_id     = :cid
          AND  os.nombre_opcion  = :rol
          AND  os.categoria      = 'rol_firmante'
          AND  f.fecha_firma     IS NOT NULL
        LIMIT  1
    ");
    $stmtYa->execute([':cid' => $cid, ':rol' => $rol_firma]);
    if ($stmtYa->fetch()) {
        echo json_encode(['ok' => false, 'msg' => 'Este rol ya registró su firma en el contrato']);
        exit;
    }

    // ── Guardar imagen PNG en disco ──
    // Intentar primero DOCUMENT_ROOT, luego ruta relativa al proyecto
    $uploadBase = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/Asociaciones_PP/uploads/firmas/';
    if (!is_dir($uploadBase)) {
        // Fallback: ruta relativa al proyecto
        $uploadBase = dirname(__DIR__) . '/uploads/firmas/';
    }
    if (!is_dir($uploadBase)) {
        mkdir($uploadBase, 0777, true);
    }

    $imgData    = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imagen_b64));
    $filename   = 'firma_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $cid)
                . '_' . strtolower($rol_firma)
                . '_' . time()
                . '.png';
    $rutaFisica = $uploadBase . $filename;

    // Calcular ruta web relativa para guardar en BD
    // Si el uploadBase es dentro de DOCUMENT_ROOT, construir ruta web
    $docRoot   = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
    $rutaWeb   = str_replace($docRoot, '', rtrim($rutaFisica, '/'));
    if (!$rutaWeb || $rutaWeb === $rutaFisica) {
        // Si no es subpath de DOCUMENT_ROOT, guardar ruta relativa al proyecto
        $rutaWeb = '/Asociaciones_PP/uploads/firmas/' . $filename;
    }

    if (!file_put_contents($rutaFisica, $imgData)) {
        echo json_encode(['ok' => false, 'msg' => 'Error al guardar la imagen de firma en el servidor']);
        exit;
    }

    // ── Insertar registro de firma ──
    $uuid = sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );

    $db->prepare("
        INSERT INTO firmas_contrato
            (id, contrato_id, nombre_firmante, correo_firmante,
             rol_firmante_id, ip_al_firmar, imagen_firma, fecha_firma)
        VALUES
            (:id, :cid, :nombre, :correo, :rid, :ip, :img, NOW())
    ")->execute([
        ':id'     => $uuid,
        ':cid'    => $cid,
        ':nombre' => $nombre,
        ':correo' => $correo,
        ':rid'    => $rolFirmanteId,
        ':ip'     => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
        ':img'    => $rutaWeb,
    ]);

    // ── Verificar si ya firmaron ambos ──
    // CORRECCIÓN: filtrar por categoria para no contar duplicados
    $stmtCount = $db->prepare("
        SELECT COUNT(DISTINCT os.nombre_opcion)
        FROM   firmas_contrato f
        JOIN   opciones_sistema os ON os.id = f.rol_firmante_id
        WHERE  f.contrato_id      = :cid
          AND  os.nombre_opcion  IN ('Vendedor', 'Comprador')
          AND  os.categoria       = 'rol_firmante'
          AND  f.fecha_firma      IS NOT NULL
    ");
    $stmtCount->execute([':cid' => $cid]);
    $totalFirmaron = (int) $stmtCount->fetchColumn();
    $ambos_firmaron = ($totalFirmaron >= 2);

    if ($ambos_firmaron) {
        // Cambiar contrato a estado "Firmado"
        $estadoFirmado = $db->query("
            SELECT id FROM opciones_sistema
            WHERE  categoria     = 'estado_contrato'
              AND  nombre_opcion = 'Firmado'
            LIMIT  1
        ")->fetchColumn();

        if ($estadoFirmado) {
            $db->prepare("UPDATE contratos SET estado_contrato_id = :est WHERE id = :cid")
               ->execute([':est' => $estadoFirmado, ':cid' => $cid]);
        }

        // Cambiar propiedad a "Vendida"
        $estadoVendida = $db->query("
            SELECT id FROM opciones_sistema
            WHERE  categoria     = 'estado_publicacion'
              AND  nombre_opcion = 'Vendida'
            LIMIT  1
        ")->fetchColumn();

        if ($estadoVendida && !empty($c['propiedad_id'])) {
            $db->prepare("UPDATE propiedades SET estado_publicacion_id = :est WHERE id = :pid")
               ->execute([':est' => $estadoVendida, ':pid' => $c['propiedad_id']]);
        }

        // Historial
        $db->prepare("
            INSERT INTO historial_contrato
                (id, contrato_id, accion_realizada, quien_lo_hizo, ip_accion)
            VALUES
                (UUID(), :cid,
                 'Contrato firmado por ambas partes — propiedad marcada como Vendida',
                 :quien, :ip)
        ")->execute([
            ':cid'   => $cid,
            ':quien' => $_SESSION['nombre'] ?? 'Sistema',
            ':ip'    => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
        ]);
    }

    echo json_encode([
        'ok'             => true,
        'msg'            => 'Firma registrada correctamente',
        'ambos_firmaron' => $ambos_firmaron,
    ]);
    exit;
}

// Acción no reconocida
echo json_encode(['ok' => false, 'msg' => 'Acción no válida: ' . htmlspecialchars($act)]);