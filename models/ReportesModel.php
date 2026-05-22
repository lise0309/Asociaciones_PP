<?php
require_once __DIR__ . '/../config/database.php';

class ReportesModel {
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::conectar();
    }
    
    public function getKPI(): array {
        $kpi = [];
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM propiedades");
        $kpi['total_propiedades'] = $stmt->fetchColumn();
        
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM usuarios");
        $kpi['total_usuarios'] = $stmt->fetchColumn();
        
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM contratos");
        $kpi['total_contratos'] = $stmt->fetchColumn();
        
        $stmt = $this->db->query("SELECT SUM(monto_acordado) as total FROM contratos WHERE estado_contrato_id IN (18, 19)");
        $kpi['monto_total'] = $stmt->fetchColumn() ?: 0;
        
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM contratos WHERE MONTH(fecha_generacion) = MONTH(NOW()) AND YEAR(fecha_generacion) = YEAR(NOW())");
        $kpi['contratos_mes'] = $stmt->fetchColumn();
        
        return $kpi;
    }
    
    public function getContratosPorMes(): array {
        $sql = "SELECT 
                    DATE_FORMAT(c.fecha_generacion, '%Y-%m') as mes,
                    COUNT(c.id) as total,
                    SUM(c.monto_acordado) as monto_total
                FROM contratos c
                WHERE c.fecha_generacion >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(c.fecha_generacion, '%Y-%m')
                ORDER BY mes ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getPropiedadesPorTipo(): array {
        $sql = "SELECT e.nombre_opcion as tipo, COUNT(p.id) as total
                FROM propiedades p
                JOIN opciones_sistema e ON p.tipo_inmueble_id = e.id
                GROUP BY p.tipo_inmueble_id, e.nombre_opcion";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getContratosPorEstado(): array {
        $sql = "SELECT e.nombre_opcion as estado, COUNT(c.id) as total
                FROM contratos c
                JOIN opciones_sistema e ON c.estado_contrato_id = e.id
                GROUP BY c.estado_contrato_id, e.nombre_opcion";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTopVendedores(int $limite = 5): array {
        $sql = "SELECT 
                    u.nombre, u.apellido,
                    COUNT(c.id) as total_ventas,
                    SUM(c.monto_acordado) as monto_total
                FROM contratos c
                JOIN usuarios u ON c.vendedor_id = u.id
                WHERE c.estado_contrato_id IN (18, 19)
                GROUP BY u.id, u.nombre, u.apellido
                ORDER BY monto_total DESC
                LIMIT $limite";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getPropiedadesDestacadas(int $limite = 5): array {
        $sql = "SELECT p.titulo_anuncio, p.precio_pedido,
                       u.nombre as vendedor_nombre, u.apellido as vendedor_apellido
                FROM propiedades p
                JOIN usuarios u ON p.vendedor_id = u.id
                WHERE p.estado_publicacion_id = 11
                ORDER BY p.fecha_actualizacion DESC
                LIMIT $limite";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>