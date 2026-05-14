<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

use PhpOffice\PhpWord\TemplateProcessor;

class ContratoModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::conectar();
    }
    
    private function generarUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    private function generarNumeroContrato() {
        $year = date('Y');
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM contratos WHERE YEAR(fecha_generacion) = ?");
        $stmt->execute([$year]);
        $count = $stmt->fetchColumn() + 1;
        return "CNT-{$year}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
    
    private function getPlantillaPorPropiedad($propiedad_id) {
        $sql = "SELECT p.tipo_inmueble_id, p.tipo_negocio_id,
                       ti.nombre_opcion as tipo_inmueble,
                       tn.nombre_opcion as tipo_negocio
                FROM propiedades p
                JOIN opciones_sistema ti ON p.tipo_inmueble_id = ti.id
                JOIN opciones_sistema tn ON p.tipo_negocio_id = tn.id
                WHERE p.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$propiedad_id]);
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$datos) {
            return 'plantilla_generica.docx';
        }
        
        $mapa = [
            'Casa_6' => 'casa_venta.docx',
            'Casa_7' => 'casa_alquiler.docx',
            'Apartamento_6' => 'apartamento_venta.docx',
            'Apartamento_7' => 'apartamento_alquiler.docx',
            'Terreno_6' => 'terreno_venta.docx',
            'Local comercial_7' => 'local_alquiler.docx',
        ];
        
        $key = $datos['tipo_inmueble'] . '_' . $datos['tipo_negocio_id'];
        return $mapa[$key] ?? 'plantilla_generica.docx';
    }
    
    public function crear($datos) {
        try {
            $id = $this->generarUUID();
            $numero_contrato = $this->generarNumeroContrato();
            
            $sql = "INSERT INTO contratos (
                id, propiedad_id, vendedor_id, plantilla_id,
                nombre_comprador, correo_comprador, dui_comprador,
                monto_acordado, moneda, tipo_contrato_id, estado_contrato_id,
                fecha_generacion, numero_contrato
            ) VALUES (
                :id, :propiedad_id, :vendedor_id, :plantilla_id,
                :nombre_comprador, :correo_comprador, :dui_comprador,
                :monto_acordado, :moneda, :tipo_contrato_id, 17, NOW(), :numero_contrato
            )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':propiedad_id' => $datos['propiedad_id'],
                ':vendedor_id' => $datos['vendedor_id'],
                ':plantilla_id' => null,
                ':nombre_comprador' => $datos['nombre_comprador'],
                ':correo_comprador' => $datos['correo_comprador'],
                ':dui_comprador' => $datos['dui_comprador'],
                ':monto_acordado' => $datos['monto_acordado'],
                ':moneda' => $datos['moneda'],
                ':tipo_contrato_id' => $datos['tipo_contrato_id'] ?? 14,
                ':numero_contrato' => $numero_contrato
            ]);
            
            return $id;
        } catch (PDOException $e) {
            error_log("Error crear contrato: " . $e->getMessage());
            return false;
        }
    }
    
    public function getContratosByVendedor($vendedor_id) {
        $sql = "SELECT c.*, p.titulo_anuncio, 
                       e.nombre_opcion as estado_nombre
                FROM contratos c
                JOIN propiedades p ON c.propiedad_id = p.id
                JOIN opciones_sistema e ON c.estado_contrato_id = e.id
                WHERE c.vendedor_id = ?
                ORDER BY c.fecha_generacion DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$vendedor_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function generarDocumento($contrato_id) {
        // Obtener datos del contrato
        $sql = "SELECT c.*, p.titulo_anuncio, p.direccion_exacta, p.municipio, p.departamento,
                       p.num_habitaciones, p.num_banos, p.metros_construccion, p.metros_terreno,
                       p.tiene_estacionamiento, p.tiene_piscina, p.viene_amueblado,
                       u.nombre as vendedor_nombre, u.apellido as vendedor_apellido,
                       u.correo as vendedor_correo, u.telefono as vendedor_telefono
                FROM contratos c
                JOIN propiedades p ON c.propiedad_id = p.id
                JOIN usuarios u ON c.vendedor_id = u.id
                WHERE c.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$contrato_id]);
        $c = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$c) {
            return ['success' => false, 'error' => 'Contrato no encontrado'];
        }
        
        $plantilla = $this->getPlantillaPorPropiedad($c['propiedad_id']);
        $templatePath = __DIR__ . '/../plantillas/' . $plantilla;
        
        if (!file_exists($templatePath)) {
            return ['success' => false, 'error' => 'Plantilla no encontrada'];
        }
        
        try {
            $templateProcessor = new TemplateProcessor($templatePath);
            
            $data = [
                'ciudad' => 'San Salvador',
                'dia' => date('d'),
                'mes' => $this->getMesTexto(date('m')),
                'año' => date('Y'),
                'vendedor_nombre' => $c['vendedor_nombre'] . ' ' . $c['vendedor_apellido'],
                'comprador_nombre' => $c['nombre_comprador'],
                'comprador_dui' => $c['dui_comprador'],
                'propiedad_direccion' => $c['direccion_exacta'],
                'propiedad_municipio' => $c['municipio'],
                'propiedad_departamento' => $c['departamento'],
                'metros_terreno' => number_format($c['metros_terreno'] ?? 0, 2),
                'metros_construccion' => number_format($c['metros_construccion'] ?? 0, 2),
                'num_habitaciones' => $c['num_habitaciones'] ?? 0,
                'num_banos' => $c['num_banos'] ?? 0,
                'tiene_estacionamiento' => ($c['tiene_estacionamiento'] ?? 0) ? 'Sí' : 'No',
                'precio' => number_format($c['monto_acordado'], 2),
                'moneda' => $c['moneda']
            ];
            
            foreach ($data as $key => $value) {
                $templateProcessor->setValue($key, $value);
                $templateProcessor->setValue('${' . $key . '}', $value);
            }
            
            $dir = __DIR__ . '/../contratos_generados';
            if (!file_exists($dir)) mkdir($dir, 0777, true);
            
            $filename = 'contrato_' . ($c['numero_contrato'] ?? date('Ymd_His')) . '.docx';
            $wordPath = $dir . '/' . $filename;
            $templateProcessor->saveAs($wordPath);
            
            return ['success' => true, 'ruta' => $wordPath, 'nombre' => $filename];
            
        } catch (Exception $e) {
            error_log("Error al generar Word: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function getMesTexto($mes) {
        $meses = [
            '01' => 'enero', '02' => 'febrero', '03' => 'marzo', '04' => 'abril',
            '05' => 'mayo', '06' => 'junio', '07' => 'julio', '08' => 'agosto',
            '09' => 'septiembre', '10' => 'octubre', '11' => 'noviembre', '12' => 'diciembre'
        ];
        return $meses[$mes] ?? 'enero';
    }
    
    public function eliminarContrato($contrato_id, $vendedor_id) {
        $check = $this->db->prepare("SELECT estado_contrato_id FROM contratos WHERE id = ? AND vendedor_id = ?");
        $check->execute([$contrato_id, $vendedor_id]);
        $estado = $check->fetchColumn();
        
        if (!$estado) {
            return ['success' => false, 'error' => 'Contrato no encontrado'];
        }
        
        if ($estado == 17) {
            $delete = $this->db->prepare("DELETE FROM contratos WHERE id = ?");
            $delete->execute([$contrato_id]);
            return ['success' => true, 'message' => 'Contrato eliminado'];
        } else {
            $update = $this->db->prepare("UPDATE contratos SET estado_contrato_id = 21 WHERE id = ?");
            $update->execute([$contrato_id]);
            return ['success' => true, 'message' => 'Contrato anulado'];
        }
    }
}
?>