<?php
/**
 * PROPIEDAD MODEL
 * PP Bienes Raíces
 * models/propiedadModel.php
 */

require_once __DIR__ . '/../config/database.php';

class PropiedadModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    /* ═══════════════════════════════════════════
       OBTENER PROPIEDAD POR ID
    ═══════════════════════════════════════════ */
    public function getById(string $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*,
                    os_tipo.nombre_opcion  AS tipo_inmueble_nombre,
                    os_neg.nombre_opcion   AS tipo_negocio_nombre,
                    os_est.nombre_opcion   AS estado_publicacion_nombre,
                    os_est.valor_extra     AS estado_publicacion_color
             FROM   propiedades p
             LEFT JOIN opciones_sistema os_tipo ON os_tipo.id = p.tipo_inmueble_id
             LEFT JOIN opciones_sistema os_neg  ON os_neg.id  = p.tipo_negocio_id
             LEFT JOIN opciones_sistema os_est  ON os_est.id  = p.estado_publicacion_id
             WHERE  p.id = :id
             LIMIT  1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /* ═══════════════════════════════════════════
       OBTENER FOTOS DE UNA PROPIEDAD
    ═══════════════════════════════════════════ */
    public function getFotos(string $propiedadId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM fotos_propiedad
             WHERE  propiedad_id = :pid
             ORDER  BY numero_orden ASC, fecha_subida ASC'
        );
        $stmt->execute([':pid' => $propiedadId]);
        return $stmt->fetchAll();
    }

    /* ═══════════════════════════════════════════
       LISTAR PROPIEDADES DE UN VENDEDOR (con filtros)
    ═══════════════════════════════════════════ */
    public function listarPorVendedor(
        string $vendedorId,
        int    $pagina    = 1,
        int    $porPagina = 12,
        string $estado    = '',
        string $tipo      = '',
        string $buscar    = ''
    ): array {
        [$where, $params] = $this->_whereVendedor($vendedorId, $estado, $tipo, $buscar);
        $offset = ($pagina - 1) * $porPagina;

        $stmt = $this->db->prepare(
            "SELECT p.*,
                    os_tipo.nombre_opcion  AS tipo_nombre,
                    os_neg.nombre_opcion   AS tipo_negocio_nombre,
                    os_est.nombre_opcion   AS estado_nombre,
                    os_est.valor_extra     AS estado_color,
                    (SELECT url_foto_miniatura FROM fotos_propiedad
                     WHERE propiedad_id = p.id AND es_foto_portada = 1 LIMIT 1) AS foto_portada
             FROM   propiedades p
             LEFT JOIN opciones_sistema os_tipo ON os_tipo.id = p.tipo_inmueble_id
             LEFT JOIN opciones_sistema os_neg  ON os_neg.id  = p.tipo_negocio_id
             LEFT JOIN opciones_sistema os_est  ON os_est.id  = p.estado_publicacion_id
             WHERE  {$where}
             ORDER  BY p.fecha_actualizacion DESC
             LIMIT  :lim OFFSET :off"
        );
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':lim', $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset,    PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /* ── Contar propiedades de un vendedor ── */
    public function contarPorVendedor(
        string $vendedorId,
        string $estado = '',
        string $tipo   = '',
        string $buscar = ''
    ): int {
        [$where, $params] = $this->_whereVendedor($vendedorId, $estado, $tipo, $buscar);
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM propiedades p WHERE {$where}"
        );
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /* ── WHERE reutilizable para vendedor ── */
    private function _whereVendedor(
        string $vendedorId,
        string $estado,
        string $tipo,
        string $buscar
    ): array {
        $conds  = ['p.vendedor_id = :vid'];
        $params = [':vid' => $vendedorId];

        if ($estado !== '') {
            $conds[]         = 'p.estado_publicacion_id = :estado';
            $params[':estado'] = (int) $estado;
        }
        if ($tipo !== '') {
            $conds[]        = 'p.tipo_inmueble_id = :tipo';
            $params[':tipo'] = (int) $tipo;
        }
        if ($buscar !== '') {
            $conds[]          = '(p.titulo_anuncio LIKE :buscar OR p.direccion_exacta LIKE :buscar OR p.municipio LIKE :buscar)';
            $params[':buscar'] = '%' . $buscar . '%';
        }

        return [implode(' AND ', $conds), $params];
    }

    /* ═══════════════════════════════════════════
       LISTAR TODAS (ADMIN) con filtros
    ═══════════════════════════════════════════ */
    public function listarTodas(
        int    $pagina    = 1,
        int    $porPagina = 12,
        string $estado    = '',
        string $tipo      = '',
        string $buscar    = ''
    ): array {
        [$where, $params] = $this->_whereTodas($estado, $tipo, $buscar);
        $offset = ($pagina - 1) * $porPagina;

        $stmt = $this->db->prepare(
            "SELECT p.*,
                    os_tipo.nombre_opcion  AS tipo_nombre,
                    os_neg.nombre_opcion   AS tipo_negocio_nombre,
                    os_est.nombre_opcion   AS estado_nombre,
                    os_est.valor_extra     AS estado_color,
                    CONCAT(u.nombre, ' ', u.apellido) AS vendedor_nombre,
                    (SELECT url_foto_miniatura FROM fotos_propiedad
                     WHERE propiedad_id = p.id AND es_foto_portada = 1 LIMIT 1) AS foto_portada
             FROM   propiedades p
             LEFT JOIN opciones_sistema os_tipo ON os_tipo.id = p.tipo_inmueble_id
             LEFT JOIN opciones_sistema os_neg  ON os_neg.id  = p.tipo_negocio_id
             LEFT JOIN opciones_sistema os_est  ON os_est.id  = p.estado_publicacion_id
             LEFT JOIN usuarios u               ON u.id       = p.vendedor_id
             WHERE  {$where}
             ORDER  BY p.fecha_actualizacion DESC
             LIMIT  :lim OFFSET :off"
        );
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':lim', $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset,    PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function contarTodas(
        string $estado = '',
        string $tipo   = '',
        string $buscar = ''
    ): int {
        [$where, $params] = $this->_whereTodas($estado, $tipo, $buscar);
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM propiedades p WHERE {$where}"
        );
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    private function _whereTodas(string $estado, string $tipo, string $buscar): array
    {
        $conds  = ['1=1'];
        $params = [];

        if ($estado !== '') {
            $conds[]           = 'p.estado_publicacion_id = :estado';
            $params[':estado']  = (int) $estado;
        }
        if ($tipo !== '') {
            $conds[]          = 'p.tipo_inmueble_id = :tipo';
            $params[':tipo']   = (int) $tipo;
        }
        if ($buscar !== '') {
            $conds[]           = '(p.titulo_anuncio LIKE :buscar OR p.direccion_exacta LIKE :buscar OR p.municipio LIKE :buscar)';
            $params[':buscar']  = '%' . $buscar . '%';
        }

        return [implode(' AND ', $conds), $params];
    }

    /* ═══════════════════════════════════════════
       CONTADORES RÁPIDOS PARA LAS STATS
    ═══════════════════════════════════════════ */

    /** Contadores para el panel del vendedor */
    public function contadoresVendedor(string $vendedorId): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                COUNT(*)                                              AS total,
                SUM(estado_publicacion_id = 11)                      AS activas,
                SUM(estado_publicacion_id = 10)                      AS pendientes,
                SUM(estado_publicacion_id = 12)                      AS pausadas,
                SUM(estado_publicacion_id = 9)                       AS borradores,
                SUM(estado_publicacion_id = 13)                      AS rechazadas
             FROM propiedades
             WHERE vendedor_id = :vid'
        );
        $stmt->execute([':vid' => $vendedorId]);
        return $stmt->fetch() ?: [];
    }

    /** Contadores globales para admin */
    public function contadoresAdmin(): array
    {
        $stmt = $this->db->query(
            'SELECT
                COUNT(*)                         AS total,
                SUM(estado_publicacion_id = 11)  AS activas,
                SUM(estado_publicacion_id = 10)  AS pendientes,
                SUM(estado_publicacion_id = 12)  AS pausadas,
                SUM(estado_publicacion_id = 9)   AS borradores,
                SUM(estado_publicacion_id = 13)  AS rechazadas
             FROM propiedades'
        );
        return $stmt->fetch() ?: [];
    }

    /* ═══════════════════════════════════════════
       CREAR PROPIEDAD — retorna nuevo UUID
    ═══════════════════════════════════════════ */
    public function crear(array $d): string
    {
        $id = $this->generarUUID();

        $this->db->prepare(
            'INSERT INTO propiedades (
                id, vendedor_id, tipo_inmueble_id, tipo_negocio_id,
                estado_publicacion_id, titulo_anuncio, descripcion_detallada,
                precio_pedido, moneda,
                num_habitaciones, num_banos, metros_construccion, metros_terreno,
                tiene_estacionamiento, tiene_piscina, viene_amueblado,
                es_anuncio_destacado,
                departamento, municipio, direccion_exacta, punto_referencia,
                latitud, longitud, fecha_publicacion
             ) VALUES (
                :id, :vendedor_id, :tipo_inmueble_id, :tipo_negocio_id,
                :estado_pub, :titulo, :descripcion,
                :precio, "USD",
                :habitaciones, :banos, :m_const, :m_terr,
                :estac, :piscina, :amuebl,
                :destacado,
                :depto, :mun, :dir, :ref,
                :lat, :lng, :fecha_pub
             )'
        )->execute([
            ':id'               => $id,
            ':vendedor_id'      => $d['vendedor_id'],
            ':tipo_inmueble_id' => $d['tipo_inmueble_id'],
            ':tipo_negocio_id'  => $d['tipo_negocio_id'],
            ':estado_pub'       => $d['estado_publicacion_id'],
            ':titulo'           => $d['titulo_anuncio'],
            ':descripcion'      => $d['descripcion_detallada'],
            ':precio'           => $d['precio_pedido'],
            ':habitaciones'     => $d['num_habitaciones'],
            ':banos'            => $d['num_banos'],
            ':m_const'          => $d['metros_construccion'],
            ':m_terr'           => $d['metros_terreno'],
            ':estac'            => $d['tiene_estacionamiento'],
            ':piscina'          => $d['tiene_piscina'],
            ':amuebl'           => $d['viene_amueblado'],
            ':destacado'        => $d['es_anuncio_destacado'],
            ':depto'            => $d['departamento'],
            ':mun'              => $d['municipio'],
            ':dir'              => $d['direccion_exacta'],
            ':ref'              => $d['punto_referencia'],
            ':lat'              => $d['latitud'],
            ':lng'              => $d['longitud'],
            ':fecha_pub'        => $d['fecha_publicacion'],
        ]);

        return $id;
    }

    /* ═══════════════════════════════════════════
       EDITAR PROPIEDAD
    ═══════════════════════════════════════════ */
    public function editar(string $id, array $d): bool
    {
        return $this->db->prepare(
            'UPDATE propiedades SET
                tipo_inmueble_id      = :tipo_inmueble_id,
                tipo_negocio_id       = :tipo_negocio_id,
                estado_publicacion_id = :estado_pub,
                titulo_anuncio        = :titulo,
                descripcion_detallada = :descripcion,
                precio_pedido         = :precio,
                num_habitaciones      = :habitaciones,
                num_banos             = :banos,
                metros_construccion   = :m_const,
                metros_terreno        = :m_terr,
                tiene_estacionamiento = :estac,
                tiene_piscina         = :piscina,
                viene_amueblado       = :amuebl,
                es_anuncio_destacado  = :destacado,
                departamento          = :depto,
                municipio             = :mun,
                direccion_exacta      = :dir,
                punto_referencia      = :ref,
                latitud               = :lat,
                longitud              = :lng,
                fecha_publicacion     = :fecha_pub
             WHERE id = :id'
        )->execute([
            ':id'               => $id,
            ':tipo_inmueble_id' => $d['tipo_inmueble_id'],
            ':tipo_negocio_id'  => $d['tipo_negocio_id'],
            ':estado_pub'       => $d['estado_publicacion_id'],
            ':titulo'           => $d['titulo_anuncio'],
            ':descripcion'      => $d['descripcion_detallada'],
            ':precio'           => $d['precio_pedido'],
            ':habitaciones'     => $d['num_habitaciones'],
            ':banos'            => $d['num_banos'],
            ':m_const'          => $d['metros_construccion'],
            ':m_terr'           => $d['metros_terreno'],
            ':estac'            => $d['tiene_estacionamiento'],
            ':piscina'          => $d['tiene_piscina'],
            ':amuebl'           => $d['viene_amueblado'],
            ':destacado'        => $d['es_anuncio_destacado'],
            ':depto'            => $d['departamento'],
            ':mun'              => $d['municipio'],
            ':dir'              => $d['direccion_exacta'],
            ':ref'              => $d['punto_referencia'],
            ':lat'              => $d['latitud'],
            ':lng'              => $d['longitud'],
            ':fecha_pub'        => $d['fecha_publicacion'],
        ]);
    }

    /* ═══════════════════════════════════════════
       GUARDAR FOTO
    ═══════════════════════════════════════════ */
    public function guardarFoto(
        string  $propiedadId,
        string  $urlOriginal,
        ?string $urlMiniatura,
        bool    $esPortada,
        int     $orden
    ): string {
        $id = $this->generarUUID();

        if ($esPortada) {
            $this->db->prepare(
                'UPDATE fotos_propiedad SET es_foto_portada = 0 WHERE propiedad_id = :pid'
            )->execute([':pid' => $propiedadId]);
        }

        $this->db->prepare(
            'INSERT INTO fotos_propiedad
                (id, propiedad_id, url_foto_original, url_foto_miniatura, es_foto_portada, numero_orden)
             VALUES (:id, :pid, :orig, :mini, :portada, :orden)'
        )->execute([
            ':id'      => $id,
            ':pid'     => $propiedadId,
            ':orig'    => $urlOriginal,
            ':mini'    => $urlMiniatura,
            ':portada' => $esPortada ? 1 : 0,
            ':orden'   => $orden,
        ]);

        return $id;
    }

    /* ═══════════════════════════════════════════
       ELIMINAR FOTO — devuelve urls para borrar archivos
    ═══════════════════════════════════════════ */
    public function eliminarFoto(string $fotoId): array
    {
        $stmt = $this->db->prepare(
            'SELECT url_foto_original, url_foto_miniatura FROM fotos_propiedad WHERE id = :id'
        );
        $stmt->execute([':id' => $fotoId]);
        $foto = $stmt->fetch() ?: [];

        $this->db->prepare('DELETE FROM fotos_propiedad WHERE id = :id')
                 ->execute([':id' => $fotoId]);

        return $foto;
    }

    /* ═══════════════════════════════════════════
       ACTUALIZAR ORDEN Y PORTADA DE FOTOS
    ═══════════════════════════════════════════ */
    public function actualizarOrdenFotos(array $fotosIds): void
    {
        if (empty($fotosIds)) return;

        // Obtener propiedad_id del primer item para resetear portadas
        $firstId = reset($fotosIds);
        $pid = $this->db->prepare(
            'SELECT propiedad_id FROM fotos_propiedad WHERE id = :id'
        );
        $pid->execute([':id' => $firstId]);
        $propiedadId = $pid->fetchColumn();

        if ($propiedadId) {
            $this->db->prepare(
                'UPDATE fotos_propiedad SET es_foto_portada = 0 WHERE propiedad_id = :pid'
            )->execute([':pid' => $propiedadId]);
        }

        $stmt = $this->db->prepare(
            'UPDATE fotos_propiedad SET numero_orden = :orden, es_foto_portada = :portada WHERE id = :id'
        );
        foreach ($fotosIds as $orden => $fotoId) {
            $stmt->execute([
                ':orden'   => $orden,
                ':portada' => ($orden === 0) ? 1 : 0,
                ':id'      => $fotoId,
            ]);
        }
    }

    /* ═══════════════════════════════════════════
       ELIMINAR PROPIEDAD COMPLETA
    ═══════════════════════════════════════════ */
    public function eliminar(string $id): bool
    {
        return $this->db->prepare('DELETE FROM propiedades WHERE id = :id')
                        ->execute([':id' => $id]);
    }

    /* ═══════════════════════════════════════════
       VERIFICAR DUEÑO
    ═══════════════════════════════════════════ */
    public function esDelVendedor(string $propiedadId, string $vendedorId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM propiedades WHERE id = :id AND vendedor_id = :vid LIMIT 1'
        );
        $stmt->execute([':id' => $propiedadId, ':vid' => $vendedorId]);
        return (bool) $stmt->fetch();
    }

    /* ═══════════════════════════════════════════
       UUID v4
    ═══════════════════════════════════════════ */
    private function generarUUID(): string
    {
        $data    = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}