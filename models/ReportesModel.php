<?php
/**
 * REPORTES MODEL — PP Bienes Raíces
 */
require_once __DIR__ . '/../config/database.php';

class ReportesModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    public function getKPI(): array {
        $kpi = [];

        $kpi['total_propiedades'] = (int)$this->db
            ->query("SELECT COUNT(*) FROM propiedades")
            ->fetchColumn();

        $kpi['total_usuarios'] = (int)$this->db
            ->query("SELECT COUNT(*) FROM usuarios WHERE cuenta_activa = 1")
            ->fetchColumn();

        $kpi['total_contratos'] = (int)$this->db
            ->query("SELECT COUNT(*) FROM contratos")
            ->fetchColumn();

        // Monto: contratos en estado Aprobado o Firmado (por nombre, no por ID hardcodeado)
        $kpi['monto_total'] = (float)$this->db->query("
            SELECT COALESCE(SUM(c.monto_acordado), 0)
            FROM contratos c
            JOIN opciones_sistema os ON os.id = c.estado_contrato_id
            WHERE os.nombre_opcion IN ('Aprobado', 'Firmado')
        ")->fetchColumn();

        $kpi['contratos_mes'] = (int)$this->db->query("
            SELECT COUNT(*)
            FROM contratos
            WHERE MONTH(fecha_generacion) = MONTH(NOW())
              AND YEAR(fecha_generacion)  = YEAR(NOW())
        ")->fetchColumn();

        // Propiedades activas
        $kpi['propiedades_activas'] = (int)$this->db->query("
            SELECT COUNT(*) FROM propiedades p
            JOIN opciones_sistema os ON os.id = p.estado_publicacion_id
            WHERE os.nombre_opcion = 'Activa'
        ")->fetchColumn();

        // Propiedades vendidas
        $kpi['propiedades_vendidas'] = (int)$this->db->query("
            SELECT COUNT(*) FROM propiedades p
            JOIN opciones_sistema os ON os.id = p.estado_publicacion_id
            WHERE os.nombre_opcion = 'Vendida'
        ")->fetchColumn();

        return $kpi;
    }

    public function getContratosPorMes(): array {
        $meses = ['01'=>'Ene','02'=>'Feb','03'=>'Mar','04'=>'Abr','05'=>'May',
                  '06'=>'Jun','07'=>'Jul','08'=>'Ago','09'=>'Sep','10'=>'Oct',
                  '11'=>'Nov','12'=>'Dic'];

        $rows = $this->db->query("
            SELECT DATE_FORMAT(fecha_generacion, '%Y-%m') AS mes_raw,
                   DATE_FORMAT(fecha_generacion, '%m')    AS mes_num,
                   DATE_FORMAT(fecha_generacion, '%Y')    AS anio,
                   COUNT(id)              AS total,
                   SUM(monto_acordado)    AS monto_total
            FROM contratos
            WHERE fecha_generacion >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY mes_raw, mes_num, anio
            ORDER BY mes_raw ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function($r) use ($meses) {
            return [
                'mes'         => ($meses[$r['mes_num']] ?? $r['mes_num']) . ' ' . $r['anio'],
                'total'       => (int)$r['total'],
                'monto_total' => (float)($r['monto_total'] ?? 0),
            ];
        }, $rows);
    }

    public function getPropiedadesPorTipo(): array {
        return $this->db->query("
            SELECT os.nombre_opcion AS tipo, COUNT(p.id) AS total
            FROM propiedades p
            JOIN opciones_sistema os ON os.id = p.tipo_inmueble_id
            GROUP BY p.tipo_inmueble_id, os.nombre_opcion
            ORDER BY total DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getContratosPorEstado(): array {
        return $this->db->query("
            SELECT os.nombre_opcion AS estado, COUNT(c.id) AS total
            FROM contratos c
            JOIN opciones_sistema os ON os.id = c.estado_contrato_id
            GROUP BY os.nombre_opcion
            ORDER BY total DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopVendedores(int $limite = 5): array {
        $stmt = $this->db->prepare("
            SELECT u.nombre, u.apellido,
                   COUNT(c.id)           AS total_ventas,
                   SUM(c.monto_acordado) AS monto_total
            FROM contratos c
            JOIN usuarios u ON u.id = c.vendedor_id
            JOIN opciones_sistema os ON os.id = c.estado_contrato_id
            WHERE os.nombre_opcion IN ('Aprobado', 'Firmado')
            GROUP BY u.id, u.nombre, u.apellido
            ORDER BY monto_total DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPropiedadesDestacadas(int $limite = 5): array {
        $stmt = $this->db->prepare("
            SELECT p.titulo_anuncio, p.precio_pedido,
                   u.nombre AS vendedor_nombre, u.apellido AS vendedor_apellido,
                   os.nombre_opcion AS tipo
            FROM propiedades p
            JOIN usuarios u ON u.id = p.vendedor_id
            JOIN opciones_sistema os ON os.id = p.tipo_inmueble_id
            JOIN opciones_sistema ep ON ep.id = p.estado_publicacion_id
            WHERE ep.nombre_opcion = 'Activa'
            ORDER BY p.precio_pedido DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
