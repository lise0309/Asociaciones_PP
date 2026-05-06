// Agregar estos métodos al modelo PropiedadModel

public function getAllAdmin(string $buscar = '', string $tipo_inmueble = '', string $negocio = '', string $estado = '', int $limit = 10, int $offset = 0): array {
    $sql = "SELECT p.*, 
                   CONCAT(u.nombre, ' ', u.apellido) as vendedor,
                   u.correo as correo_vendedor,
                   u.telefono as telefono_vendedor,
                   ti.nombre_opcion as tipo_inmueble,
                   tn.nombre_opcion as tipo_negocio,
                   ep.nombre_opcion as estado_publicacion,
                   ep.valor_extra as estado_color,
                   (SELECT url_foto_miniatura FROM fotos_propiedad WHERE propiedad_id = p.id ORDER BY numero_orden LIMIT 1) as foto
            FROM propiedades p
            JOIN usuarios u ON p.vendedor_id = u.id
            JOIN opciones_sistema ti ON p.tipo_inmueble_id = ti.id
            JOIN opciones_sistema tn ON p.tipo_negocio_id = tn.id
            JOIN opciones_sistema ep ON p.estado_publicacion_id = ep.id
            WHERE 1=1";
    
    $params = [];
    
    if (!empty($buscar)) {
        $sql .= " AND (p.titulo_anuncio LIKE :buscar OR CONCAT(u.nombre, ' ', u.apellido) LIKE :buscar OR p.direccion_exacta LIKE :buscar)";
        $params[':buscar'] = "%$buscar%";
    }
    
    if (!empty($tipo_inmueble)) {
        $sql .= " AND ti.nombre_opcion = :tipo_inmueble";
        $params[':tipo_inmueble'] = $tipo_inmueble;
    }
    
    if (!empty($negocio)) {
        $sql .= " AND tn.nombre_opcion = :negocio";
        $params[':negocio'] = $negocio;
    }
    
    if (!empty($estado)) {
        $sql .= " AND ep.nombre_opcion = :estado";
        $params[':estado'] = $estado;
    }
    
    $sql .= " ORDER BY p.fecha_publicacion DESC LIMIT :limit OFFSET :offset";
    $params[':limit'] = $limit;
    $params[':offset'] = $offset;
    
    $stmt = $this->db->prepare($sql);
    foreach ($params as $key => &$val) {
        if ($key == ':limit' || $key == ':offset') {
            $stmt->bindValue($key, $val, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $val);
        }
    }
    $stmt->execute();
    return $stmt->fetchAll();
}

public function getTotalCountAdmin(string $buscar = '', string $tipo_inmueble = '', string $negocio = '', string $estado = ''): int {
    $sql = "SELECT COUNT(*) as total 
            FROM propiedades p
            JOIN opciones_sistema ti ON p.tipo_inmueble_id = ti.id
            JOIN opciones_sistema tn ON p.tipo_negocio_id = tn.id
            JOIN opciones_sistema ep ON p.estado_publicacion_id = ep.id
            WHERE 1=1";
    
    $params = [];
    
    if (!empty($buscar)) {
        $sql .= " AND p.titulo_anuncio LIKE :buscar";
        $params[':buscar'] = "%$buscar%";
    }
    
    if (!empty($tipo_inmueble)) {
        $sql .= " AND ti.nombre_opcion = :tipo_inmueble";
        $params[':tipo_inmueble'] = $tipo_inmueble;
    }
    
    if (!empty($negocio)) {
        $sql .= " AND tn.nombre_opcion = :negocio";
        $params[':negocio'] = $negocio;
    }
    
    if (!empty($estado)) {
        $sql .= " AND ep.nombre_opcion = :estado";
        $params[':estado'] = $estado;
    }
    
    $stmt = $this->db->prepare($sql);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->execute();
    return (int) $stmt->fetch()['total'];
}

public function getKPIs(): array {
    $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN ep.nombre_opcion = 'Activa' THEN 1 ELSE 0 END) as activas,
                SUM(CASE WHEN ep.nombre_opcion = 'Pendiente aprobación' THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN p.es_anuncio_destacado = 1 THEN 1 ELSE 0 END) as destacadas
            FROM propiedades p
            JOIN opciones_sistema ep ON p.estado_publicacion_id = ep.id";
    
    $stmt = $this->db->query($sql);
    return $stmt->fetch();
}

public function getByIdAdmin(string $id): ?array {
    $sql = "SELECT p.*, 
                   CONCAT(u.nombre, ' ', u.apellido) as vendedor,
                   u.correo as correo_vendedor,
                   u.telefono as telefono_vendedor,
                   ti.nombre_opcion as tipo_inmueble,
                   tn.nombre_opcion as tipo_negocio,
                   ep.nombre_opcion as estado_publicacion
            FROM propiedades p
            JOIN usuarios u ON p.vendedor_id = u.id
            JOIN opciones_sistema ti ON p.tipo_inmueble_id = ti.id
            JOIN opciones_sistema tn ON p.tipo_negocio_id = tn.id
            JOIN opciones_sistema ep ON p.estado_publicacion_id = ep.id
            WHERE p.id = :id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}