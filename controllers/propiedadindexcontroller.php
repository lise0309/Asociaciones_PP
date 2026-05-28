<?php
/**
 * PROPIEDAD CONTROLLER — Vista pública (Guest)
 * PP Bienes Raíces — controllers/propiedadindexcontroller.php
 */

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(405);
  header('Content-Type: application/json');
  echo json_encode(['ok' => false, 'msg' => 'Método no permitido.']);
  exit;
}

header('Content-Type: application/json');

$accion = $_GET['accion'] ?? 'listar';

try {
  $db = Database::conectar();

  // ── STATS ──────────────────────────────────────────────
  if ($accion === 'stats') {
    $stats = $db->query("
      SELECT COUNT(*) AS propiedades, COUNT(DISTINCT p.departamento) AS departamentos
      FROM propiedades p
      JOIN opciones_sistema os ON os.id = p.estado_publicacion_id
        AND os.nombre_opcion = 'Activa'
        AND os.categoria = 'estado_publicacion'
    ")->fetch();
    $agentes = $db->query("SELECT COUNT(*) FROM usuarios WHERE rol='vendedor' AND cuenta_activa=1")->fetchColumn();
    echo json_encode(['ok' => true, 'stats' => [
      'propiedades'   => $stats['propiedades']   ?? 0,
      'departamentos' => $stats['departamentos'] ?? 0,
      'agentes'       => $agentes ?? 0,
    ]]);
    exit;
  }

  // ── LISTAR ─────────────────────────────────────────────
  // ★ JOIN forzado a os_est — solo muestra propiedades en estado "Activa"
  // Cualquier otro estado (Pausada, Borrador, Rechazada, Vendida) queda excluido
  $where  = [
    "os_est.nombre_opcion = 'Activa'",
    "os_est.categoria = 'estado_publicacion'",
  ];
  $params = [];

  if (!empty($_GET['negocio'])) {
    $mapa = ['venta' => 'Venta', 'alquiler' => 'Alquiler', 'opcion' => 'Alquiler con opción a compra'];
    $neg  = $mapa[$_GET['negocio']] ?? null;
    if ($neg) { $where[] = 'o_neg.nombre_opcion = :negocio'; $params[':negocio'] = $neg; }
  }
  if (!empty($_GET['tipo'])) {
    $where[] = 'o_tip.nombre_opcion = :tipo';
    $params[':tipo'] = trim($_GET['tipo']);
  }
  if (!empty($_GET['departamento'])) {
    $where[] = 'p.departamento = :dpto';
    $params[':dpto'] = trim($_GET['departamento']);
  }
  if (!empty($_GET['precio_min']) && is_numeric($_GET['precio_min'])) {
    $where[] = 'p.precio_pedido >= :pmin';
    $params[':pmin'] = (float)$_GET['precio_min'];
  }
  if (!empty($_GET['precio_max']) && is_numeric($_GET['precio_max'])) {
    $where[] = 'p.precio_pedido <= :pmax';
    $params[':pmax'] = (float)$_GET['precio_max'];
  }
  if (!empty($_GET['q'])) {
    $where[] = '(p.titulo_anuncio LIKE :q OR p.municipio LIKE :q OR p.departamento LIKE :q)';
    $params[':q'] = '%' . trim($_GET['q']) . '%';
  }

  $orden = match ($_GET['orden'] ?? 'recientes') {
    'menor-precio' => 'p.precio_pedido ASC',
    'mayor-precio' => 'p.precio_pedido DESC',
    default        => 'p.es_anuncio_destacado DESC, p.fecha_publicacion DESC',
  };

  $pagina    = max(1, (int)($_GET['pagina']    ?? 1));
  $porPagina = max(1, min(500, (int)($_GET['por_pagina'] ?? 12)));
  $offset    = ($pagina - 1) * $porPagina;
  $whereStr  = implode(' AND ', $where);

  $sql = "
    SELECT
      p.id,
      p.titulo_anuncio,
      p.precio_pedido,
      p.moneda,
      p.departamento,
      p.municipio,
      p.num_habitaciones,
      p.num_banos,
      p.metros_construccion,
      p.metros_terreno,
      p.tiene_piscina,
      p.es_anuncio_destacado,
      p.fecha_publicacion,
      p.latitud,
      p.longitud,
      COALESCE(o_tip.nombre_opcion, '') AS tipo_inmueble,
      COALESCE(o_neg.nombre_opcion, '') AS tipo_negocio,
      (SELECT url_foto_original FROM fotos_propiedad
       WHERE propiedad_id = p.id AND es_foto_portada = 1 LIMIT 1) AS foto_portada
    FROM propiedades p
    LEFT JOIN opciones_sistema o_tip ON o_tip.id = p.tipo_inmueble_id AND o_tip.categoria = 'tipo_inmueble'
    LEFT JOIN opciones_sistema o_neg ON o_neg.id = p.tipo_negocio_id  AND o_neg.categoria = 'tipo_negocio'
    JOIN  opciones_sistema os_est ON os_est.id = p.estado_publicacion_id
    WHERE $whereStr
    ORDER BY $orden
    LIMIT :lim OFFSET :off
  ";

  $stmt = $db->prepare($sql);
  foreach ($params as $k => $v) $stmt->bindValue($k, $v);
  $stmt->bindValue(':lim', $porPagina, PDO::PARAM_INT);
  $stmt->bindValue(':off', $offset,    PDO::PARAM_INT);
  $stmt->execute();
  $propiedades = $stmt->fetchAll();

  // Count con mismo filtro
  $sqlCount = "
    SELECT COUNT(*) FROM propiedades p
    LEFT JOIN opciones_sistema o_tip ON o_tip.id = p.tipo_inmueble_id AND o_tip.categoria = 'tipo_inmueble'
    LEFT JOIN opciones_sistema o_neg ON o_neg.id = p.tipo_negocio_id  AND o_neg.categoria = 'tipo_negocio'
    JOIN  opciones_sistema os_est ON os_est.id = p.estado_publicacion_id
    WHERE $whereStr
  ";
  $stmtC = $db->prepare($sqlCount);
  foreach ($params as $k => $v) $stmtC->bindValue($k, $v);
  $stmtC->execute();
  $total = (int)$stmtC->fetchColumn();

  echo json_encode([
    'ok'          => true,
    'propiedades' => $propiedades,
    'total'       => $total,
    'pagina'      => $pagina,
    'por_pagina'  => $porPagina,
    'total_pags'  => (int)ceil(max($total, 1) / $porPagina),
  ]);

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'msg' => 'Error: ' . $e->getMessage()]);
}