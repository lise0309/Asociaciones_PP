<?php
/**
 * DETALLE PROPIEDAD CONTROLLER — Público (Guest)
 * PP Bienes Raíces — controllers/detallecontroller.php
 */

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? '';

if (!$id) {
  echo json_encode(['ok' => false, 'msg' => 'ID requerido.']);
  exit;
}

try {
  $db = Database::conectar();

  $stmt = $db->prepare('
    SELECT
      p.*,
      os_tip.nombre_opcion            AS tipo_inmueble,
      os_neg.nombre_opcion            AS tipo_negocio,
      os_est.nombre_opcion            AS estado_publicacion,
      u.id                            AS vendedor_id,
      u.nombre                        AS vendedor_nombre,
      u.apellido                      AS vendedor_apellido,
      u.correo                        AS vendedor_correo,
      u.telefono                      AS vendedor_telefono,
      u.descripcion_personal          AS vendedor_descripcion,
      u.foto_perfil                   AS vendedor_foto,
      u.fecha_registro                AS vendedor_desde,
      (SELECT COUNT(*) FROM propiedades p2
       JOIN opciones_sistema o2 ON p2.estado_publicacion_id = o2.id
       WHERE p2.vendedor_id = u.id
       AND o2.nombre_opcion = "Activa") AS vendedor_propiedades
    FROM propiedades p
    JOIN opciones_sistema os_tip ON os_tip.id = p.tipo_inmueble_id
    JOIN opciones_sistema os_neg ON os_neg.id = p.tipo_negocio_id
    JOIN opciones_sistema os_est ON os_est.id = p.estado_publicacion_id
    JOIN usuarios u               ON u.id      = p.vendedor_id
    WHERE p.id = :id
    LIMIT 1
  ');
  $stmt->execute([':id' => $id]);
  $propiedad = $stmt->fetch();

  if (!$propiedad) {
    echo json_encode(['ok' => false, 'msg' => 'Propiedad no encontrada.']);
    exit;
  }

  // Fotos
  $stmtFotos = $db->prepare('
    SELECT * FROM fotos_propiedad
    WHERE propiedad_id = :pid
    ORDER BY es_foto_portada DESC, numero_orden ASC
  ');
  $stmtFotos->execute([':pid' => $id]);
  $fotos = $stmtFotos->fetchAll();

  // Registrar contacto si se indica canal
  if (!empty($_GET['contacto'])) {
    $canales = ['telefono', 'correo', 'whatsapp'];
    $canal   = in_array($_GET['contacto'], $canales) ? $_GET['contacto'] : null;
    if ($canal) {
      $db->prepare('
        INSERT INTO contactos_propiedad (id, propiedad_id, vendedor_id, canal_contacto, ip_origen)
        VALUES (UUID(), :pid, :vid, :canal, :ip)
      ')->execute([
        ':pid'   => $id,
        ':vid'   => $propiedad['vendedor_id'],
        ':canal' => $canal,
        ':ip'    => $_SERVER['REMOTE_ADDR'] ?? null,
      ]);
    }
  }

  echo json_encode([
    'ok'        => true,
    'propiedad' => $propiedad,
    'fotos'     => $fotos,
  ]);

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'msg' => 'Error del servidor: ' . $e->getMessage()]);
}