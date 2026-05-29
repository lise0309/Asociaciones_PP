<?php
/**
 * CONTRATO MODEL — PP Bienes Raíces
 */
require_once __DIR__ . '/../config/database.php';

class ContratoModel {
    private PDO $db;
    public function __construct() { $this->db = Database::conectar(); }

    private function uuid(): string {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff),
            mt_rand(0,0x0fff)|0x4000,mt_rand(0,0x3fff)|0x8000,
            mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff));
    }

    public function crear(array $d): string|false {
        try {
            $id  = $this->uuid();
            $est = $this->db->query("SELECT id FROM opciones_sistema WHERE categoria='estado_contrato' AND nombre_opcion='Borrador' LIMIT 1")->fetchColumn() ?: 17;
            $stmt = $this->db->prepare("
                INSERT INTO contratos (id,propiedad_id,vendedor_id,plantilla_id,nombre_comprador,correo_comprador,dui_comprador,monto_acordado,moneda,tipo_contrato_id,estado_contrato_id,fecha_generacion)
                VALUES (:id,:pid,:vid,:plid,:nc,:ec,:dui,:monto,:moneda,:tipo,:est,NOW())
            ");
            $ok = $stmt->execute([':id'=>$id,':pid'=>$d['propiedad_id'],':vid'=>$d['vendedor_id'],':plid'=>$d['plantilla_id'],':nc'=>$d['nombre_comprador'],':ec'=>$d['correo_comprador'],':dui'=>$d['dui_comprador'],':monto'=>$d['monto_acordado'],':moneda'=>$d['moneda']??'USD',':tipo'=>$d['tipo_contrato_id'],':est'=>$est]);
            if ($ok) $this->historial($id,'Contrato creado',$d['vendedor_id']);
            return $ok ? $id : false;
        } catch (PDOException $e) { error_log($e->getMessage()); return false; }
    }

    public function listar(string $vendedorId, string $rol='vendedor', array $filtros=[]): array {
        $sql = "SELECT c.*,p.titulo_anuncio,pc.nombre_plantilla,
                       ot.nombre_opcion AS tipo_nombre,
                       oe.nombre_opcion AS estado_nombre,
                       oe.valor_extra   AS estado_color,
                       CONCAT(u.nombre,' ',u.apellido) AS vendedor_nombre
                FROM contratos c
                JOIN propiedades p          ON p.id  = c.propiedad_id
                JOIN plantillas_contrato pc ON pc.id = c.plantilla_id
                JOIN opciones_sistema ot    ON ot.id = c.tipo_contrato_id
                JOIN opciones_sistema oe    ON oe.id = c.estado_contrato_id
                JOIN usuarios u             ON u.id  = c.vendedor_id
                WHERE 1=1";

        $params = [];

        if (strtolower($rol) !== 'admin') {
            $sql .= " AND c.vendedor_id = :vid";
            $params[':vid'] = $vendedorId;
        } elseif (!empty($filtros['vendedor'])) {
            $sql .= " AND c.vendedor_id = :vid";
            $params[':vid'] = $filtros['vendedor'];
        }

        if (!empty($filtros['estado'])) {
            $sql .= " AND oe.nombre_opcion = :estado";
            $params[':estado'] = $filtros['estado'];
        }
        if (!empty($filtros['tipo'])) {
            $sql .= " AND c.tipo_contrato_id = :tipo";
            $params[':tipo'] = $filtros['tipo'];
        }
        if (!empty($filtros['buscar'])) {
            $sql .= " AND (c.nombre_comprador LIKE :buscar OR c.dui_comprador LIKE :buscar)";
            $params[':buscar'] = '%' . $filtros['buscar'] . '%';
        }

        $sql .= " ORDER BY c.fecha_generacion DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
public function getById(string $id): ?array {
    $stmt = $this->db->prepare("
        SELECT 
            c.*,
            p.titulo_anuncio,
            p.departamento,
            p.municipio,
            p.direccion_exacta,
            p.metros_terreno,
            pc.nombre_plantilla,
            pc.archivo_plantilla,
            ot.nombre_opcion AS tipo_nombre,
            oe.nombre_opcion AS estado_nombre,
            CONCAT(u.nombre, ' ', u.apellido) AS vendedor_nombre,
            u.correo AS vendedor_correo,
            u.telefono AS vendedor_telefono
        FROM contratos c 
        JOIN propiedades p ON p.id = c.propiedad_id 
        JOIN plantillas_contrato pc ON pc.id = c.plantilla_id 
        JOIN opciones_sistema ot ON ot.id = c.tipo_contrato_id 
        JOIN opciones_sistema oe ON oe.id = c.estado_contrato_id 
        JOIN usuarios u ON u.id = c.vendedor_id 
        WHERE c.id = :id 
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Agregar valores por defecto para los campos que no existen en la BD
    if ($result) {
        $result['matricula_inmueble'] = $result['matricula_inmueble'] ?? 'POR DETERMINAR';
        $result['colindancia_norte'] = $result['colindancia_norte'] ?? 'POR DETERMINAR';
        $result['colindancia_sur'] = $result['colindancia_sur'] ?? 'POR DETERMINAR';
        $result['colindancia_oriente'] = $result['colindancia_oriente'] ?? 'POR DETERMINAR';
        $result['colindancia_poniente'] = $result['colindancia_poniente'] ?? 'POR DETERMINAR';
    }
    
    return $result;
}

    public function eliminar(string $id, string $vendedorId): bool {
        $c=$this->getById($id);
        if (!$c) return false;
        if ($c['estado_nombre']==='Borrador') {
            return $this->db->prepare("DELETE FROM contratos WHERE id=:id AND vendedor_id=:vid")->execute([':id'=>$id,':vid'=>$vendedorId]);
        }
        $est=$this->db->query("SELECT id FROM opciones_sistema WHERE categoria='estado_contrato' AND nombre_opcion='Anulado' LIMIT 1")->fetchColumn()?:21;
        $ok=$this->db->prepare("UPDATE contratos SET estado_contrato_id=:est WHERE id=:id AND vendedor_id=:vid")->execute([':est'=>$est,':id'=>$id,':vid'=>$vendedorId]);
        if ($ok) $this->historial($id,'Contrato anulado',$vendedorId);
        return $ok;
    }

    public function historial(string $cid, string $accion, string $quien): void {
        try { $this->db->prepare("INSERT INTO historial_contrato(id,contrato_id,accion_realizada,quien_lo_hizo) VALUES(UUID(),:cid,:a,:q)")->execute([':cid'=>$cid,':a'=>$accion,':q'=>$quien]); } catch(Throwable){}
    }
}