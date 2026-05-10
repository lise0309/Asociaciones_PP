<?php
/**
 * PROPIEDAD CONTROLLER — Vista pública (Guest)
 * PP Bienes Raíces — controllers/PropiedadController.php
 *
 * Solo muestra propiedades con estado_publicacion = "Activa"
 * y vendedor con cuenta_activa = 1
 *
 * Acciones:
 *   GET ?accion=stats   → contadores para el hero
 *   GET ?accion=listar  → propiedades con filtros
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

  /* ═══════════════════════════════════════════
     STATS DEL HERO
  ═══════════════════════════════════════════ */
  if ($accion === 'stats') {

    $stats = $db->query('
      SELECT
        (SELECT COUNT(*)
         FROM   propiedades p
         JOIN   opciones_sistema o ON p.estado_publicacion_id = o.id
         JOIN   usuarios u         ON p.vendedor_id           = u.id
         WHERE  o.nombre_opcion = "Activa"
         AND    u.cuenta_activa = 1)                              AS propiedades,

        (SELECT COUNT(DISTINCT p.departamento)
         FROM   propiedades p
         JOIN   opciones_sistema o ON p.estado_publicacion_id = o.id
         JOIN   usuarios u         ON p.vendedor_id           = u.id
         WHERE  o.nombre_opcion = "Activa"
         AND    u.cuenta_activa = 1)                              AS departamentos,

        (SELECT COUNT(*)
         FROM   usuarios
         WHERE  rol = "vendedor"
         AND    cuenta_activa    = 1
         AND    cuenta_verificada = 1)                            AS agentes
    ')->fetch();

    echo json_encode(['ok' => true, 'stats' => $stats]);
    exit;
  }

  /* ═══════════════════════════════════════════
     LISTAR PROPIEDADES
  ═══════════════════════════════════════════ */

  // Base: solo propiedades ACTIVAS de vendedores ACTIVOS
  $where  = [
    "o_pub.nombre_opcion = 'Activa'",
    "u.cuenta_activa = 1",
  ];
  $params = [];

  // ── Modalidad (negocio): venta | alquiler | opcion ──
  if (!empty($_GET['negocio'])) {
    $mapa = [
      'venta'   => 'Venta',
      'alquiler'=> 'Alquiler',
      'opcion'  => 'Alquiler con opción a compra',
    ];
    $neg = $mapa[$_GET['negocio']] ?? null;
    if ($neg) {
      $where[]           = 'o_neg.nombre_opcion = :negocio';
      $params[':negocio']= $neg;
    }
  }

  // ── Tipo de inmueble: valor exacto del select ──
  // El select envía el texto exacto: "Casa", "Apartamento", etc.
  if (!empty($_GET['tipo'])) {
    $where[]        = 'o_tip.nombre_opcion = :tipo';
    $params[':tipo']= trim($_GET['tipo']);
  }

  // ── Departamento ──
  if (!empty($_GET['departamento'])) {
    $where[]         = 'p.departamento = :dpto';
    $params[':dpto'] = trim($_GET['departamento']);
  }

  // ── Precio mínimo ──
  if (isset($_GET['precio_min']) && $_GET['precio_min'] !== '' && is_numeric($_GET['precio_min'])) {
    $where[]         = 'p.precio_pedido >= :pmin';
    $params[':pmin'] = (float) $_GET['precio_min'];
  }

  // ── Precio máximo ──
  if (isset($_GET['precio_max']) && $_GET['precio_max'] !== '' && is_numeric($_GET['precio_max'])) {
    $where[]         = 'p.precio_pedido <= :pmax';
    $params[':pmax'] = (float) $_GET['precio_max'];
  }

  // ── Búsqueda por texto ──
  if (!empty($_GET['q'])) {
    $q = '%' . trim($_GET['q']) . '%';
    $where[]      = '(p.titulo_anuncio LIKE :q OR p.municipio LIKE :q OR p.departamento LIKE :q OR p.descripcion_detallada LIKE :q)';
    $params[':q'] = $q;
  }

  // ── Ordenar ──
  $orden = match ($_GET['orden'] ?? 'recientes') {
    'menor-precio' => 'p.precio_pedido ASC',
    'mayor-precio' => 'p.precio_pedido DESC',
    'destacadas'   => 'p.es_anuncio_destacado DESC, p.fecha_publicacion DESC',
    default        => 'p.es_anuncio_destacado DESC, p.fecha_publicacion DESC',
  };

  $whereStr = implode(' AND ', $where);

  // ── Paginación ──
  $pagina    = max(1, (int) ($_GET['pagina'] ?? 1));
  $porPagina = 12;
  $offset    = ($pagina - 1) * $porPagina;

  // ── Query principal ──
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
      p.es_anuncio_destacado,
      p.fecha_publicacion,
      o_tip.nombre_opcion AS tipo_inmueble,
      o_neg.nombre_opcion AS tipo_negocio,
      (SELECT url_foto_original FROM fotos_propiedad
       WHERE  propiedad_id = p.id AND es_foto_portada = 1
       LIMIT  1)          AS foto_portada
    FROM  propiedades p
    JOIN  opciones_sistema o_pub ON p.estado_publicacion_id = o_pub.id
    JOIN  opciones_sistema o_tip ON p.tipo_inmueble_id      = o_tip.id
    JOIN  opciones_sistema o_neg ON p.tipo_negocio_id       = o_neg.id
    JOIN  usuarios u              ON p.vendedor_id           = u.id
    WHERE {$whereStr}
    ORDER BY {$orden}
    LIMIT :lim OFFSET :off
  ";

  $stmt = $db->prepare($sql);
  foreach ($params as $k => $v) $stmt->bindValue($k, $v);
  $stmt->bindValue(':lim', $porPagina, PDO::PARAM_INT);
  $stmt->bindValue(':off', $offset,    PDO::PARAM_INT);
  $stmt->execute();
  $propiedades = $stmt->fetchAll();

  // ── Total ──
  $sqlCount = "
    SELECT COUNT(*)
    FROM  propiedades p
    JOIN  opciones_sistema o_pub ON p.estado_publicacion_id = o_pub.id
    JOIN  opciones_sistema o_tip ON p.tipo_inmueble_id      = o_tip.id
    JOIN  opciones_sistema o_neg ON p.tipo_negocio_id       = o_neg.id
    JOIN  usuarios u              ON p.vendedor_id           = u.id
    WHERE {$whereStr}
  ";
  $stmtCount = $db->prepare($sqlCount);
  foreach ($params as $k => $v) $stmtCount->bindValue($k, $v);
  $stmtCount->execute();
  $total = (int) $stmtCount->fetchColumn();

  echo json_encode([
    'ok'          => true,
    'propiedades' => $propiedades,
    'total'       => $total,
    'pagina'      => $pagina,
    'por_pagina'  => $porPagina,
    'total_pags'  => (int) ceil($total / $porPagina),
  ]);

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'msg' => 'Error del servidor.']);
}