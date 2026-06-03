<?php
/**
 * BITÁCORA CONTROLLER — PP Bienes Raíces
 * controllers/bitacoracontroller.php
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || strtolower($_SESSION['rol'] ?? '') !== 'admin') {
    echo json_encode(['ok' => false, 'msg' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../config/database.php';
$db = Database::conectar();

$act    = $_GET['action'] ?? '';
$desde  = $_GET['desde']  ?? '';
$hasta  = $_GET['hasta']  ?? '';
$tipo   = $_GET['tipo']   ?? '';
$buscar = trim($_GET['buscar'] ?? '');
$pagina    = max(1, (int)($_GET['pagina'] ?? 1));
$porPagina = 40;
$offset    = ($pagina - 1) * $porPagina;

/* ══════════════════════════════════════════════════════════
   EVENTOS UNIFICADOS
══════════════════════════════════════════════════════════ */
if ($act === 'eventos') {

    $eventos = [];

    /* ── 1. Historial de contratos ── */
    if ($tipo === '' || $tipo === 'contratos') {
        $sql = "
            SELECT hc.id,
                'contrato' AS tipo_evento,
                hc.accion_realizada AS descripcion,
                COALESCE(CONCAT(u.nombre,' ',u.apellido), hc.quien_lo_hizo) AS actor,
                hc.ip_accion AS ip,
                hc.fecha_accion AS fecha,
                SUBSTRING(hc.contrato_id,1,8) AS ref_id,
                p.titulo_anuncio AS propiedad
            FROM historial_contrato hc
            LEFT JOIN contratos c ON c.id = hc.contrato_id
            LEFT JOIN propiedades p ON p.id = c.propiedad_id
            LEFT JOIN usuarios u ON u.id = hc.quien_lo_hizo
            WHERE 1=1
        ";
        $params = [];
        if ($desde) { $sql .= " AND hc.fecha_accion >= :desde"; $params[':desde'] = $desde.' 00:00:00'; }
        if ($hasta) { $sql .= " AND hc.fecha_accion <= :hasta"; $params[':hasta'] = $hasta.' 23:59:59'; }
        if ($buscar) {
            $sql .= " AND (hc.accion_realizada LIKE :b OR p.titulo_anuncio LIKE :b OR CONCAT(u.nombre,' ',u.apellido) LIKE :b)";
            $params[':b'] = '%'.$buscar.'%';
        }
        $stmt = $db->prepare($sql); $stmt->execute($params);
        $eventos = array_merge($eventos, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /* ── 2. Firmas de contratos ── */
    if ($tipo === '' || $tipo === 'firmas') {
        $sql = "
            SELECT fc.id,
                'firma' AS tipo_evento,
                CONCAT('Firma registrada como ', os.nombre_opcion) AS descripcion,
                fc.nombre_firmante AS actor,
                fc.ip_al_firmar AS ip,
                fc.fecha_firma AS fecha,
                SUBSTRING(fc.contrato_id,1,8) AS ref_id,
                p.titulo_anuncio AS propiedad
            FROM firmas_contrato fc
            JOIN opciones_sistema os ON os.id = fc.rol_firmante_id AND os.categoria = 'rol_firmante'
            LEFT JOIN contratos c ON c.id = fc.contrato_id
            LEFT JOIN propiedades p ON p.id = c.propiedad_id
            WHERE fc.fecha_firma IS NOT NULL
        ";
        $params = [];
        if ($desde) { $sql .= " AND fc.fecha_firma >= :desde"; $params[':desde'] = $desde.' 00:00:00'; }
        if ($hasta) { $sql .= " AND fc.fecha_firma <= :hasta"; $params[':hasta'] = $hasta.' 23:59:59'; }
        if ($buscar) {
            $sql .= " AND (fc.nombre_firmante LIKE :b OR p.titulo_anuncio LIKE :b)";
            $params[':b'] = '%'.$buscar.'%';
        }
        $stmt = $db->prepare($sql); $stmt->execute($params);
        $eventos = array_merge($eventos, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /* ── 3. Contactos de propiedades ── */
    if ($tipo === '' || $tipo === 'contactos') {
        $sql = "
            SELECT cp.id,
                'contacto' AS tipo_evento,
                CONCAT('Contacto vía ', cp.canal_contacto) AS descripcion,
                COALESCE(CONCAT(uv.nombre,' ',uv.apellido),'—') AS actor,
                cp.ip_origen AS ip,
                cp.fecha_contacto AS fecha,
                SUBSTRING(cp.propiedad_id,1,8) AS ref_id,
                p.titulo_anuncio AS propiedad
            FROM contactos_propiedad cp
            LEFT JOIN propiedades p ON p.id = cp.propiedad_id
            LEFT JOIN usuarios uv ON uv.id = cp.vendedor_id
            WHERE 1=1
        ";
        $params = [];
        if ($desde) { $sql .= " AND cp.fecha_contacto >= :desde"; $params[':desde'] = $desde.' 00:00:00'; }
        if ($hasta) { $sql .= " AND cp.fecha_contacto <= :hasta"; $params[':hasta'] = $hasta.' 23:59:59'; }
        if ($buscar) {
            $sql .= " AND (p.titulo_anuncio LIKE :b OR CONCAT(uv.nombre,' ',uv.apellido) LIKE :b)";
            $params[':b'] = '%'.$buscar.'%';
        }
        $stmt = $db->prepare($sql); $stmt->execute($params);
        $eventos = array_merge($eventos, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /* ── 4. Inicio de sesiones ── */
    if ($tipo === '' || $tipo === 'sesiones') {
        $sql = "
            SELECT sa.id,
                'sesion' AS tipo_evento,
                CONCAT('Inicio de sesión — ', u.rol) AS descripcion,
                CONCAT(u.nombre,' ',u.apellido) AS actor,
                sa.ip_dispositivo AS ip,
                sa.fecha_inicio AS fecha,
                SUBSTRING(sa.usuario_id,1,8) AS ref_id,
                NULL AS propiedad
            FROM sesiones_activas sa
            LEFT JOIN usuarios u ON u.id = sa.usuario_id
            WHERE 1=1
        ";
        $params = [];
        if ($desde) { $sql .= " AND sa.fecha_inicio >= :desde"; $params[':desde'] = $desde.' 00:00:00'; }
        if ($hasta) { $sql .= " AND sa.fecha_inicio <= :hasta"; $params[':hasta'] = $hasta.' 23:59:59'; }
        if ($buscar) {
            $sql .= " AND CONCAT(u.nombre,' ',u.apellido) LIKE :b";
            $params[':b'] = '%'.$buscar.'%';
        }
        $stmt = $db->prepare($sql); $stmt->execute($params);
        $eventos = array_merge($eventos, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /* ── 5. Publicaciones de propiedades ── */
    if ($tipo === '' || $tipo === 'propiedades') {
        $sql = "
            SELECT p.id,
                'propiedad' AS tipo_evento,
                CONCAT(os_neg.nombre_opcion,' — ',os_tip.nombre_opcion,' publicada') AS descripcion,
                CONCAT(u.nombre,' ',u.apellido) AS actor,
                NULL AS ip,
                p.fecha_publicacion AS fecha,
                SUBSTRING(p.id,1,8) AS ref_id,
                p.titulo_anuncio AS propiedad
            FROM propiedades p
            LEFT JOIN usuarios u ON u.id = p.vendedor_id
            LEFT JOIN opciones_sistema os_tip ON os_tip.id = p.tipo_inmueble_id
            LEFT JOIN opciones_sistema os_neg ON os_neg.id = p.tipo_negocio_id
            WHERE p.fecha_publicacion IS NOT NULL
        ";
        $params = [];
        if ($desde) { $sql .= " AND p.fecha_publicacion >= :desde"; $params[':desde'] = $desde.' 00:00:00'; }
        if ($hasta) { $sql .= " AND p.fecha_publicacion <= :hasta"; $params[':hasta'] = $hasta.' 23:59:59'; }
        if ($buscar) {
            $sql .= " AND (p.titulo_anuncio LIKE :b OR CONCAT(u.nombre,' ',u.apellido) LIKE :b)";
            $params[':b'] = '%'.$buscar.'%';
        }
        $stmt = $db->prepare($sql); $stmt->execute($params);
        $eventos = array_merge($eventos, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /* ── 6. Registros de usuarios ── */
    if ($tipo === '' || $tipo === 'usuarios') {
        $sql = "
            SELECT u.id,
                'usuario' AS tipo_evento,
                CONCAT('Usuario registrado — Rol: ', u.rol) AS descripcion,
                CONCAT(u.nombre,' ',u.apellido) AS actor,
                NULL AS ip,
                u.fecha_registro AS fecha,
                SUBSTRING(u.id,1,8) AS ref_id,
                NULL AS propiedad
            FROM usuarios u
            WHERE u.fecha_registro IS NOT NULL
        ";
        $params = [];
        if ($desde) { $sql .= " AND u.fecha_registro >= :desde"; $params[':desde'] = $desde.' 00:00:00'; }
        if ($hasta) { $sql .= " AND u.fecha_registro <= :hasta"; $params[':hasta'] = $hasta.' 23:59:59'; }
        if ($buscar) {
            $sql .= " AND (CONCAT(u.nombre,' ',u.apellido) LIKE :b OR u.correo LIKE :b)";
            $params[':b'] = '%'.$buscar.'%';
        }
        $stmt = $db->prepare($sql); $stmt->execute($params);
        $eventos = array_merge($eventos, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /* ── 7. Plantillas subidas ── */
    if ($tipo === '' || $tipo === 'plantillas') {
        $sql = "
            SELECT pc.id,
                'plantilla' AS tipo_evento,
                CONCAT('Plantilla subida: ', pc.nombre_plantilla) AS descripcion,
                'Administrador' AS actor,
                NULL AS ip,
                pc.fecha_creacion AS fecha,
                SUBSTRING(pc.id,1,8) AS ref_id,
                pc.nombre_plantilla AS propiedad
            FROM plantillas_contrato pc
            WHERE pc.fecha_creacion IS NOT NULL
        ";
        $params = [];
        if ($desde) { $sql .= " AND pc.fecha_creacion >= :desde"; $params[':desde'] = $desde.' 00:00:00'; }
        if ($hasta) { $sql .= " AND pc.fecha_creacion <= :hasta"; $params[':hasta'] = $hasta.' 23:59:59'; }
        if ($buscar) {
            $sql .= " AND pc.nombre_plantilla LIKE :b";
            $params[':b'] = '%'.$buscar.'%';
        }
        $stmt = $db->prepare($sql); $stmt->execute($params);
        $eventos = array_merge($eventos, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /* ── Ordenar por fecha desc y paginar ── */
    usort($eventos, fn($a, $b) => strcmp($b['fecha'] ?? '', $a['fecha'] ?? ''));
    $total   = count($eventos);
    $paginas = (int)ceil($total / $porPagina) ?: 1;
    $eventos = array_slice($eventos, $offset, $porPagina);

    echo json_encode(['ok' => true, 'eventos' => $eventos, 'total' => $total, 'paginas' => $paginas, 'pagina' => $pagina]);
    exit;
}

/* ══════════════════════════════════════════════════════════
   KPIs
══════════════════════════════════════════════════════════ */
if ($act === 'kpis') {
    $totalH  = (int)$db->query("SELECT COUNT(*) FROM historial_contrato")->fetchColumn();
    $totalF  = (int)$db->query("SELECT COUNT(*) FROM firmas_contrato WHERE fecha_firma IS NOT NULL")->fetchColumn();
    $totalC  = (int)$db->query("SELECT COUNT(*) FROM contactos_propiedad")->fetchColumn();
    $totalS  = (int)$db->query("SELECT COUNT(*) FROM sesiones_activas")->fetchColumn();
    $totalP  = (int)$db->query("SELECT COUNT(*) FROM propiedades WHERE fecha_publicacion IS NOT NULL")->fetchColumn();
    $totalU  = (int)$db->query("SELECT COUNT(*) FROM usuarios WHERE fecha_registro IS NOT NULL")->fetchColumn();
    $totalPl = (int)$db->query("SELECT COUNT(*) FROM plantillas_contrato WHERE fecha_creacion IS NOT NULL")->fetchColumn();
    $hoy     = (int)$db->query("SELECT COUNT(*) FROM historial_contrato WHERE DATE(fecha_accion)=CURDATE()")->fetchColumn();
    $hoy    += (int)$db->query("SELECT COUNT(*) FROM sesiones_activas WHERE DATE(fecha_inicio)=CURDATE()")->fetchColumn();

    echo json_encode([
        'ok'         => true,
        'contratos'  => $totalH,
        'firmas'     => $totalF,
        'contactos'  => $totalC,
        'sesiones'   => $totalS,
        'propiedades'=> $totalP,
        'usuarios'   => $totalU,
        'plantillas' => $totalPl,
        'hoy'        => $hoy,
    ]);
    exit;
}

echo json_encode(['ok' => false, 'msg' => 'Acción no válida']);
