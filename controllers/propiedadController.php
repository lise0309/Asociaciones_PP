<?php
/**
 * PROPIEDADES CONTROLLER — Vista pública (Guest)
 * PP Bienes Raíces — controllers/PropiedadController.php
 * Devuelve propiedades activas en JSON para el index
 */

require_once __DIR__ . '/../config/database.php';

// Solo GET permitido
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

  // ── STATS DEL HERO ──────────────────────────────────────
  if ($accion === 'stats') {
    $stats = $db->query('
      SELECT
        (SELECT COUNT(*) FROM propiedades p
         JOIN opciones_sistema o ON p.estado_publicacion_id = o.id
         WHERE o.nombre_opcion = "Activa") AS propiedades,
        (SELECT COUNT(DISTINCT departamento) FROM propiedades p
         JOIN opciones_sistema o ON p.estado_publicacion_id = o.id
         WHERE o.nombre_opcion = "Activa") AS departamentos,
        (SELECT COUNT(*) FROM usuarios WHERE rol = "vendedor" AND cuenta_activa = 1) AS agentes
    ')->fetch();

    echo json_encode(['ok' => true, 'stats' => $stats]);
    exit;
  }

  // ── LISTAR PROPIEDADES ───────────────────────────────────
  $where  = ["o_pub.nombre_opcion = 'Activa'"];
  $params = [];

  // Filtro modalidad (tipo_negocio)
  if (!empty($_GET['negocio'])) {
    $negocioMap = [
      'venta'   => 'Venta',
      'alquiler'=> 'Alquiler',
      'opcion'  => 'Alquiler con opción a compra',
    ];
    $negocioNombre = $negocioMap[$_GET['negocio']] ?? null;
    if ($negocioNombre) {
      $where[]             = "o_neg.nombre_opcion = :negocio";
      $params[':negocio']  = $negocioNombre;
    }
  }

  // Filtro tipo inmueble
  if (!empty($_GET['tipo'])) {
    $tipoMap = [
      'casa'        => 'Casa',
      'apartamento' => 'Apartamento',
      'local'       => 'Local comercial',
      'terreno'     => 'Terreno',
      'finca'       => 'Casa',
      'bodega'      => 'Bodega',
    ];
    $tipoNombre = $tipoMap[$_GET['tipo']] ?? $_GET['tipo'];
    $where[]           = "o_tip.nombre_opcion = :tipo";
    $params[':tipo']   = $tipoNombre;
  }

  // Filtro departamento
  if (!empty($_GET['departamento'])) {
    $where[]                  = "p.departamento = :dpto";
    $params[':dpto']          = $_GET['departamento'];
  }

  // Filtro precio mínimo
  if (!empty($_GET['precio_min']) && is_numeric($_GET['precio_min'])) {
    $where[]                  = "p.precio_pedido >= :pmin";
    $params[':pmin']          = (float) $_GET['precio_min'];
  }

  // Filtro precio máximo
  if (!empty($_GET['precio_max']) && is_numeric($_GET['precio_max'])) {
    $where[]                  = "p.precio_pedido <= :pmax";
    $params[':pmax']          = (float) $_GET['precio_max'];
  }

  // Búsqueda por texto
  if (!empty($_GET['q'])) {
    $where[]           = "(p.titulo_anuncio LIKE :q OR p.municipio LIKE :q OR p.departamento LIKE :q)";
    $params[':q']      = '%' . trim($_GET['q']) . '%';
  }

  // Ordenar
  $orden = match($_GET['orden'] ?? 'recientes') {
    'menor-precio' => 'p.precio_pedido ASC',
    'mayor-precio' => 'p.precio_pedido DESC',
    'destacadas'   => 'p.es_anuncio_destacado DESC, p.fecha_publicacion DESC',
    default        => 'p.fecha_publicacion DESC',
  };

  $sql = '
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
      p.es_anuncio_destacado,
      p.fecha_publicacion,
      o_tip.nombre_opcion AS tipo_inmueble,
      o_neg.nombre_opcion AS tipo_negocio,
      o_pub.nombre_opcion AS estado,
      (SELECT url_foto_original FROM fotos_propiedad
       WHERE propiedad_id = p.id AND es_foto_portada = 1
       LIMIT 1) AS foto_portada
    FROM  propiedades p
    JOIN  opciones_sistema o_pub ON p.estado_publicacion_id = o_pub.id
    JOIN  opciones_sistema o_tip ON p.tipo_inmueble_id      = o_tip.id
    JOIN  opciones_sistema o_neg ON p.tipo_negocio_id       = o_neg.id
    WHERE ' . implode(' AND ', $where) . '
    ORDER BY ' . $orden . '
    LIMIT 50
  ';

  $stmt = $db->prepare($sql);
  $stmt->execute($params);
  $propiedades = $stmt->fetchAll();

  // Total para el contador
  $sqlCount = '
    SELECT COUNT(*) FROM propiedades p
    JOIN opciones_sistema o_pub ON p.estado_publicacion_id = o_pub.id
    JOIN opciones_sistema o_neg ON p.tipo_negocio_id       = o_neg.id
    JOIN opciones_sistema o_tip ON p.tipo_inmueble_id      = o_tip.id
    WHERE ' . implode(' AND ', $where);

  $stmtCount = $db->prepare($sqlCount);
  $stmtCount->execute($params);
  $total = (int) $stmtCount->fetchColumn();

  echo json_encode([
    'ok'          => true,
    'propiedades' => $propiedades,
    'total'       => $total,
  ]);

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'msg' => 'Error al obtener propiedades.']);
}