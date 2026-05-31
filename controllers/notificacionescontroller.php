<?php
/**
 * NOTIFICACIONES CONTROLLER
 * PP Bienes Raíces — controllers/notificacionescontroller.php
 * Devuelve notificaciones reales según el rol del usuario
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
$rol = $_SESSION['rol'] ?? '';

$notifs = [];

if ($rol === 'admin') {

    // 1. Contratos enviados (pendientes de revisión)
    $stmt = $db->query("
        SELECT c.id, c.nombre_comprador, p.titulo_anuncio,
               u.nombre AS vendedor, c.fecha_generacion
        FROM contratos c
        JOIN propiedades p ON p.id = c.propiedad_id
        JOIN usuarios u ON u.id = c.vendedor_id
        JOIN opciones_sistema os ON os.id = c.estado_contrato_id
        WHERE os.nombre_opcion = 'Enviado'
        ORDER BY c.fecha_generacion DESC
        LIMIT 5
    ");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $notifs[] = [
            'tipo'  => 'contrato_enviado',
            'icono' => 'gold',
            'icon_class' => 'fa-file-contract',
            'texto' => 'Contrato de ' . $r['vendedor'] . ' pendiente de revisión',
            'sub'   => htmlspecialchars($r['titulo_anuncio']),
            'tiempo'=> tiempoRelativo($r['fecha_generacion']),
            'url'   => 'contratosadmin.php',
            'leida' => false,
        ];
    }

    // 2. Contratos aprobados pendientes de firma
    $stmt = $db->query("
        SELECT c.id, c.nombre_comprador, p.titulo_anuncio, c.fecha_generacion
        FROM contratos c
        JOIN propiedades p ON p.id = c.propiedad_id
        JOIN opciones_sistema os ON os.id = c.estado_contrato_id
        LEFT JOIN firmas_contrato fc ON fc.contrato_id = c.id
        WHERE os.nombre_opcion = 'Aprobado'
          AND fc.id IS NULL
        ORDER BY c.fecha_generacion DESC
        LIMIT 3
    ");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $notifs[] = [
            'tipo'  => 'firma_pendiente',
            'icono' => 'navy',
            'icon_class' => 'fa-pen-nib',
            'texto' => 'Contrato aprobado sin firmar',
            'sub'   => htmlspecialchars($r['titulo_anuncio']),
            'tiempo'=> tiempoRelativo($r['fecha_generacion']),
            'url'   => 'contratosadmin.php',
            'leida' => false,
        ];
    }

    // 3. Propiedades pendientes de aprobación
    $stmt = $db->query("
        SELECT p.id, p.titulo_anuncio, u.nombre AS vendedor, p.fecha_creacion
        FROM propiedades p
        JOIN usuarios u ON u.id = p.vendedor_id
        JOIN opciones_sistema os ON os.id = p.estado_publicacion_id
        WHERE os.nombre_opcion IN ('Pendiente aprobación','Pendiente')
        ORDER BY p.fecha_creacion DESC
        LIMIT 3
    ");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $notifs[] = [
            'tipo'  => 'propiedad_pendiente',
            'icono' => 'blue',
            'icon_class' => 'fa-home',
            'texto' => 'Propiedad pendiente de revisión',
            'sub'   => htmlspecialchars($r['titulo_anuncio']) . ' · ' . $r['vendedor'],
            'tiempo'=> tiempoRelativo($r['fecha_creacion']),
            'url'   => 'propiedades.php',
            'leida' => true,
        ];
    }

    // 4. Usuarios sin verificar
    $stmt = $db->query("
        SELECT id, nombre, apellido, fecha_registro
        FROM usuarios
        WHERE cuenta_verificada = 0 AND cuenta_activa = 1
        ORDER BY fecha_registro DESC
        LIMIT 3
    ");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $notifs[] = [
            'tipo'  => 'usuario_sin_verificar',
            'icono' => 'green',
            'icon_class' => 'fa-user-check',
            'texto' => $r['nombre'] . ' ' . $r['apellido'] . ' pendiente de verificación',
            'sub'   => 'Nuevo agente registrado',
            'tiempo'=> tiempoRelativo($r['fecha_registro']),
            'url'   => 'usuarios.php',
            'leida' => true,
        ];
    }

} else {
    // VENDEDOR

    // 1. Contratos aprobados — listo para firmar
    $stmt = $db->prepare("
        SELECT c.id, p.titulo_anuncio, c.fecha_generacion
        FROM contratos c
        JOIN propiedades p ON p.id = c.propiedad_id
        JOIN opciones_sistema os ON os.id = c.estado_contrato_id
        LEFT JOIN firmas_contrato fc ON fc.contrato_id = c.id
        WHERE c.vendedor_id = ?
          AND os.nombre_opcion = 'Aprobado'
          AND fc.id IS NULL
        ORDER BY c.fecha_generacion DESC
        LIMIT 5
    ");
    $stmt->execute([$uid]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $notifs[] = [
            'tipo'  => 'contrato_aprobado',
            'icono' => 'gold',
            'icon_class' => 'fa-check-circle',
            'texto' => '¡Contrato aprobado! Listo para firmar',
            'sub'   => htmlspecialchars($r['titulo_anuncio']),
            'tiempo'=> tiempoRelativo($r['fecha_generacion']),
            'url'   => 'contratos.php',
            'leida' => false,
        ];
    }

    // 2. Contratos rechazados
    $stmt = $db->prepare("
        SELECT c.id, p.titulo_anuncio, c.fecha_generacion
        FROM contratos c
        JOIN propiedades p ON p.id = c.propiedad_id
        JOIN opciones_sistema os ON os.id = c.estado_contrato_id
        WHERE c.vendedor_id = ?
          AND os.nombre_opcion = 'Rechazado'
        ORDER BY c.fecha_generacion DESC
        LIMIT 3
    ");
    $stmt->execute([$uid]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $notifs[] = [
            'tipo'  => 'contrato_rechazado',
            'icono' => 'red',
            'icon_class' => 'fa-times-circle',
            'texto' => 'Contrato rechazado por el administrador',
            'sub'   => htmlspecialchars($r['titulo_anuncio']),
            'tiempo'=> tiempoRelativo($r['fecha_generacion']),
            'url'   => 'contratos.php',
            'leida' => false,
        ];
    }

    // 3. Contratos firmados completamente
    $stmt = $db->prepare("
        SELECT c.id, p.titulo_anuncio, c.fecha_generacion
        FROM contratos c
        JOIN propiedades p ON p.id = c.propiedad_id
        JOIN opciones_sistema os ON os.id = c.estado_contrato_id
        WHERE c.vendedor_id = ?
          AND os.nombre_opcion = 'Firmado'
        ORDER BY c.fecha_generacion DESC
        LIMIT 3
    ");
    $stmt->execute([$uid]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $notifs[] = [
            'tipo'  => 'contrato_firmado',
            'icono' => 'green',
            'icon_class' => 'fa-file-signature',
            'texto' => '¡Contrato firmado! Propiedad vendida',
            'sub'   => htmlspecialchars($r['titulo_anuncio']),
            'tiempo'=> tiempoRelativo($r['fecha_generacion']),
            'url'   => 'contratos.php',
            'leida' => true,
        ];
    }
}

// Ordenar: no leídas primero, luego por tiempo
usort($notifs, fn($a,$b) => $a['leida'] <=> $b['leida']);

$no_leidas = count(array_filter($notifs, fn($n) => !$n['leida']));

echo json_encode([
    'ok'        => true,
    'notifs'    => array_slice($notifs, 0, 8),
    'no_leidas' => $no_leidas,
]);

/* ── Helper tiempo relativo ── */
function tiempoRelativo(string $fecha): string {
    $diff = time() - strtotime($fecha);
    if ($diff < 60)     return 'Hace un momento';
    if ($diff < 3600)   return 'Hace ' . floor($diff/60) . ' min';
    if ($diff < 86400)  return 'Hace ' . floor($diff/3600) . ' h';
    if ($diff < 604800) return 'Hace ' . floor($diff/86400) . ' días';
    return date('d/m/Y', strtotime($fecha));
}