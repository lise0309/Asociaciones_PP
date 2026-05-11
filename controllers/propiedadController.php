<?php
/**
 * PROPIEDAD CONTROLLER — Admin
 * PP Bienes Raíces — controllers/propiedadController.php
 */

session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
  http_response_code(403);
  header('Content-Type: application/json');
  echo json_encode(['ok' => false, 'msg' => 'Acceso no autorizado.']);
  exit;
}

header('Content-Type: application/json');

$accion = $_GET['accion'] ?? 'listar';
$metodo = $_SERVER['REQUEST_METHOD'];

try {
  $db = Database::conectar();

  // ══════════════════════════════════════
  // LISTAR
  // ══════════════════════════════════════
  if ($accion === 'listar' && $metodo === 'GET') {

    $where  = ['1=1'];
    $params = [];

    if (!empty($_GET['buscar'])) {
      $where[]           = '(p.titulo_anuncio LIKE :b OR p.municipio LIKE :b OR p.departamento LIKE :b)';
      $params[':b']      = '%' . trim($_GET['buscar']) . '%';
    }
    if (!empty($_GET['tipo_inmueble'])) {
      $where[]           = 'COALESCE(os_tip.nombre_opcion,"") = :tip';
      $params[':tip']    = $_GET['tipo_inmueble'];
    }
    if (!empty($_GET['negocio'])) {
      $where[]           = 'COALESCE(os_neg.nombre_opcion,"") = :neg';
      $params[':neg']    = $_GET['negocio'];
    }
    if (!empty($_GET['estado'])) {
      $where[]           = 'COALESCE(os_est.nombre_opcion,"") = :est';
      $params[':est']    = $_GET['estado'];
    }

    $whereStr = implode(' AND ', $where);

    $propiedades = $db->prepare("
      SELECT
        p.id,
        p.titulo_anuncio,
        p.precio_pedido,
        p.moneda,
        p.departamento,
        p.municipio,
        p.direccion_exacta,
        p.num_habitaciones,
        p.num_banos,
        p.metros_terreno,
        p.es_anuncio_destacado,
        p.fecha_actualizacion,
        COALESCE(os_tip.nombre_opcion, '—') AS tipo_inmueble,
        COALESCE(os_neg.nombre_opcion, '—') AS tipo_negocio,
        COALESCE(os_est.nombre_opcion, 'Sin estado') AS estado_publicacion,
        COALESCE(os_est.valor_extra, '#6B7280') AS estado_color,
        COALESCE(CONCAT(u.nombre, ' ', u.apellido), 'Sin vendedor') AS vendedor,
        COALESCE(u.correo, '') AS correo_vendedor,
        COALESCE(u.telefono, '') AS telefono_vendedor,
        (SELECT url_foto_miniatura FROM fotos_propiedad
         WHERE propiedad_id = p.id AND es_foto_portada = 1 LIMIT 1) AS foto
      FROM propiedades p
      LEFT JOIN opciones_sistema os_tip ON os_tip.id = p.tipo_inmueble_id
        AND os_tip.categoria = 'tipo_inmueble'
      LEFT JOIN opciones_sistema os_neg ON os_neg.id = p.tipo_negocio_id
        AND os_neg.categoria = 'tipo_negocio'
      LEFT JOIN opciones_sistema os_est ON os_est.id = p.estado_publicacion_id
        AND os_est.categoria = 'estado_publicacion'
      LEFT JOIN usuarios u ON u.id = p.vendedor_id
      WHERE $whereStr
      ORDER BY p.fecha_actualizacion DESC
      LIMIT 200
    ");
    $propiedades->execute($params);
    $rows = $propiedades->fetchAll();

    // KPIs
    $kpis = $db->query("
      SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN os.nombre_opcion = 'Activa' THEN 1 ELSE 0 END) AS activas,
        SUM(CASE WHEN os.nombre_opcion = 'Pendiente aprobación' THEN 1 ELSE 0 END) AS pendientes,
        SUM(CASE WHEN p.es_anuncio_destacado = 1 THEN 1 ELSE 0 END) AS destacadas
      FROM propiedades p
      LEFT JOIN opciones_sistema os ON os.id = p.estado_publicacion_id
        AND os.categoria = 'estado_publicacion'
    ")->fetch();

    // Total filtrado
    $cnt = $db->prepare("
      SELECT COUNT(*) FROM propiedades p
      LEFT JOIN opciones_sistema os_tip ON os_tip.id = p.tipo_inmueble_id AND os_tip.categoria = 'tipo_inmueble'
      LEFT JOIN opciones_sistema os_neg ON os_neg.id = p.tipo_negocio_id  AND os_neg.categoria = 'tipo_negocio'
      LEFT JOIN opciones_sistema os_est ON os_est.id = p.estado_publicacion_id AND os_est.categoria = 'estado_publicacion'
      LEFT JOIN usuarios u ON u.id = p.vendedor_id
      WHERE $whereStr
    ");
    $cnt->execute($params);

    echo json_encode([
      'ok'          => true,
      'propiedades' => $rows,
      'total'       => (int) $cnt->fetchColumn(),
      'kpis'        => $kpis,
    ]);
    exit;
  }

  // ══════════════════════════════════════
  // DETALLE
  // ══════════════════════════════════════
  if ($accion === 'detalle' && $metodo === 'GET') {
    $id = $_GET['id'] ?? '';

    $stmt = $db->prepare("
      SELECT p.*,
        COALESCE(os_tip.nombre_opcion, '—') AS tipo_inmueble,
        COALESCE(os_neg.nombre_opcion, '—') AS tipo_negocio,
        COALESCE(os_est.nombre_opcion, 'Sin estado') AS estado_publicacion,
        COALESCE(os_est.valor_extra, '#6B7280') AS estado_color,
        COALESCE(CONCAT(u.nombre,' ',u.apellido), 'Sin vendedor') AS vendedor,
        COALESCE(u.correo, '') AS correo_vendedor,
        COALESCE(u.telefono, '') AS telefono_vendedor
      FROM propiedades p
      LEFT JOIN opciones_sistema os_tip ON os_tip.id = p.tipo_inmueble_id AND os_tip.categoria = 'tipo_inmueble'
      LEFT JOIN opciones_sistema os_neg ON os_neg.id = p.tipo_negocio_id  AND os_neg.categoria = 'tipo_negocio'
      LEFT JOIN opciones_sistema os_est ON os_est.id = p.estado_publicacion_id AND os_est.categoria = 'estado_publicacion'
      LEFT JOIN usuarios u ON u.id = p.vendedor_id
      WHERE p.id = :id LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $prop = $stmt->fetch();

    if (!$prop) {
      echo json_encode(['ok' => false, 'msg' => 'Propiedad no encontrada.']);
      exit;
    }

    $fotos = $db->prepare('SELECT * FROM fotos_propiedad WHERE propiedad_id = :pid ORDER BY numero_orden ASC');
    $fotos->execute([':pid' => $id]);

    echo json_encode(['ok' => true, 'propiedad' => $prop, 'fotos' => $fotos->fetchAll()]);
    exit;
  }

  // ══════════════════════════════════════
  // ESTADOS DISPONIBLES
  // ══════════════════════════════════════
  if ($accion === 'estados' && $metodo === 'GET') {
    $stmt = $db->query("
      SELECT id, nombre_opcion, valor_extra
      FROM opciones_sistema
      WHERE categoria = 'estado_publicacion' AND disponible = 1
      ORDER BY id
    ");
    echo json_encode(['ok' => true, 'estados' => $stmt->fetchAll()]);
    exit;
  }

  // ══════════════════════════════════════
  // CAMBIAR ESTADO
  // ══════════════════════════════════════
  if ($accion === 'estado' && $metodo === 'POST') {
    $id  = $_POST['id']       ?? '';
    $est = (int)($_POST['estado_id'] ?? 0);

    if (!$id || !$est) {
      echo json_encode(['ok' => false, 'msg' => 'Datos inválidos.']);
      exit;
    }

    $ok = $db->prepare('UPDATE propiedades SET estado_publicacion_id = :e WHERE id = :id')
             ->execute([':e' => $est, ':id' => $id]);

    echo json_encode(['ok' => $ok, 'msg' => $ok ? 'Estado actualizado.' : 'Error al actualizar.']);
    exit;
  }

  // ══════════════════════════════════════
  // ELIMINAR
  // ══════════════════════════════════════
  if ($accion === 'eliminar' && $metodo === 'POST') {
    $id = $_POST['id'] ?? '';
    if (!$id) { echo json_encode(['ok' => false, 'msg' => 'ID inválido.']); exit; }

    // Eliminar fotos físicas
    $fotos = $db->prepare('SELECT url_foto_original, url_foto_miniatura FROM fotos_propiedad WHERE propiedad_id = :pid');
    $fotos->execute([':pid' => $id]);
    $base = $_SERVER['DOCUMENT_ROOT'] . '/Asociaciones_PP/';
    foreach ($fotos->fetchAll() as $f) {
      foreach ([$f['url_foto_original'], $f['url_foto_miniatura']] as $r) {
        if ($r && file_exists($base . ltrim($r, '/'))) @unlink($base . ltrim($r, '/'));
      }
    }

    $ok = $db->prepare('DELETE FROM propiedades WHERE id = :id')->execute([':id' => $id]);
    echo json_encode(['ok' => $ok, 'msg' => $ok ? 'Propiedad eliminada.' : 'Error al eliminar.']);
    exit;
  }

  echo json_encode(['ok' => false, 'msg' => 'Acción no válida.']);

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'msg' => 'Error: ' . $e->getMessage()]);
}