<?php
require_once __DIR__ . '/../config/database.php';

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
    
    // Registrar acción en el historial
    private function registrarHistorial($contrato_id, $accion, $quien) {
        try {
            $id = $this->generarUUID();
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            
            $sql = "INSERT INTO historial_contrato (id, contrato_id, accion_realizada, quien_lo_hizo, ip_accion, fecha_accion) 
                    VALUES (:id, :contrato_id, :accion, :quien, :ip, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':contrato_id' => $contrato_id,
                ':accion' => $accion,
                ':quien' => $quien,
                ':ip' => $ip
            ]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Error registrar historial: " . $e->getMessage());
            return false;
        }
    }
    
    public function crear($datos) {
    try {
        $id = $this->generarUUID();
        $numero_contrato = $this->generarNumeroContrato();
        
        $sql = "INSERT INTO contratos (
            id, numero_contrato, propiedad_id, vendedor_id, plantilla_id,
            nombre_comprador, correo_comprador, dui_comprador,
            monto_acordado, moneda, tipo_contrato_id, estado_contrato_id,
            fecha_generacion
        ) VALUES (
            :id, :numero_contrato, :propiedad_id, :vendedor_id, :plantilla_id,
            :nombre_comprador, :correo_comprador, :dui_comprador,
            :monto_acordado, :moneda, :tipo_contrato_id, 17, NOW()
        )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':numero_contrato' => $numero_contrato,
            ':propiedad_id' => $datos['propiedad_id'],
            ':vendedor_id' => $datos['vendedor_id'],
            ':plantilla_id' => null,
            ':nombre_comprador' => $datos['nombre_comprador'],
            ':correo_comprador' => $datos['correo_comprador'],
            ':dui_comprador' => $datos['dui_comprador'],
            ':monto_acordado' => $datos['monto_acordado'],
            ':moneda' => $datos['moneda'],
            ':tipo_contrato_id' => $datos['tipo_contrato_id'] ?? 14
        ]);
        
        // Obtener nombre del vendedor para el historial
        $stmt = $this->db->prepare("SELECT nombre, apellido FROM usuarios WHERE id = ?");
        $stmt->execute([$datos['vendedor_id']]);
        $vendedor = $stmt->fetch(PDO::FETCH_ASSOC);
        $nombreVendedor = ($vendedor['nombre'] ?? '') . ' ' . ($vendedor['apellido'] ?? '');
        
        // Registrar historial
        $this->registrarHistorial($id, "Contrato creado en estado Borrador", $nombreVendedor);
        
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
    
    /**
     * Genera documento Word usando ZipArchive
     */
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
        
        // Determinar qué plantilla usar
        $plantilla = $this->getPlantillaPorPropiedad($c['propiedad_id']);
        $templatePath = __DIR__ . '/../plantillas/' . $plantilla;
        
        if (!file_exists($templatePath)) {
            return ['success' => false, 'error' => 'Plantilla no encontrada: ' . $plantilla];
        }
        
        // Crear carpeta temporal
        $temp_dir = __DIR__ . '/../contratos_generados/temp_' . uniqid();
        if (!file_exists($temp_dir)) mkdir($temp_dir, 0777, true);
        
        // Copiar plantilla a directorio temporal
        $temp_docx = $temp_dir . '/template.docx';
        copy($templatePath, $temp_docx);
        
        // Extraer el ZIP
        $zip = new ZipArchive;
        if ($zip->open($temp_docx) === TRUE) {
            // Extraer document.xml
            $xml_content = $zip->getFromName('word/document.xml');
            
            if ($xml_content === false) {
                $zip->close();
                $this->deleteDirectory($temp_dir);
                return ['success' => false, 'error' => 'No se pudo leer document.xml'];
            }
            
            // Preparar datos para reemplazar
            $data = [
                '#CIUDAD#' => 'San Salvador',
                '#DIA#' => date('d'),
                '#MES#' => $this->getMesTexto(date('m')),
                '#AÑO#' => date('Y'),
                '#VENDEDOR_NOMBRE#' => $c['vendedor_nombre'] . ' ' . $c['vendedor_apellido'],
                '#VENDEDOR_CORREO#' => $c['vendedor_correo'] ?? '',
                '#VENDEDOR_TELEFONO#' => $c['vendedor_telefono'] ?? '',
                '#COMPRADOR_NOMBRE#' => $c['nombre_comprador'],
                '#COMPRADOR_DUI#' => $c['dui_comprador'],
                '#COMPRADOR_CORREO#' => $c['correo_comprador'],
                '#PROPIEDAD_TITULO#' => $c['titulo_anuncio'],
                '#PROPIEDAD_DIRECCION#' => $c['direccion_exacta'],
                '#PROPIEDAD_MUNICIPIO#' => $c['municipio'],
                '#PROPIEDAD_DEPARTAMENTO#' => $c['departamento'],
                '#METROS_TERRENO#' => number_format($c['metros_terreno'] ?? 0, 2),
                '#METROS_CONSTRUCCION#' => number_format($c['metros_construccion'] ?? 0, 2),
                '#NUM_HABITACIONES#' => $c['num_habitaciones'] ?? 0,
                '#NUM_BANOS#' => $c['num_banos'] ?? 0,
                '#TIENE_ESTACIONAMIENTO#' => ($c['tiene_estacionamiento'] ?? 0) ? 'Sí' : 'No',
                '#PRECIO#' => number_format($c['monto_acordado'], 2),
                '#MONEDA#' => $c['moneda']
            ];
            
            // Reemplazar marcadores
            foreach ($data as $key => $value) {
                $xml_content = str_replace($key, htmlspecialchars($value), $xml_content);
            }
            
            // Reemplazar en el archivo ZIP
            $zip->deleteName('word/document.xml');
            $zip->addFromString('word/document.xml', $xml_content);
            $zip->close();
            
            // Copiar el archivo generado a la carpeta final
            $dir = __DIR__ . '/../contratos_generados';
            if (!file_exists($dir)) mkdir($dir, 0777, true);
            
            $filename = 'contrato_' . ($c['numero_contrato'] ?? date('Ymd_His')) . '.docx';
            $finalPath = $dir . '/' . $filename;
            copy($temp_docx, $finalPath);
            
            // Registrar en historial
            $this->registrarHistorial($contrato_id, "Documento Word generado", $c['vendedor_nombre'] . ' ' . $c['vendedor_apellido']);
            
            // Limpiar archivos temporales
            $this->deleteDirectory($temp_dir);
            
            return ['success' => true, 'ruta' => $finalPath, 'nombre' => $filename];
        } else {
            $this->deleteDirectory($temp_dir);
            return ['success' => false, 'error' => 'No se pudo abrir la plantilla'];
        }
    }
    
    private function deleteDirectory($dir) {
        if (!file_exists($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
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
    $check = $this->db->prepare("SELECT estado_contrato_id, vendedor_id FROM contratos WHERE id = ? AND vendedor_id = ?");
    $check->execute([$contrato_id, $vendedor_id]);
    $contrato = $check->fetch(PDO::FETCH_ASSOC);
    
    if (!$contrato) {
        return ['success' => false, 'error' => 'Contrato no encontrado'];
    }
    
    // Obtener el nombre del vendedor para el historial
    $stmt = $this->db->prepare("SELECT nombre, apellido FROM usuarios WHERE id = ?");
    $stmt->execute([$vendedor_id]);
    $vendedor = $stmt->fetch(PDO::FETCH_ASSOC);
    $nombreVendedor = ($vendedor['nombre'] ?? '') . ' ' . ($vendedor['apellido'] ?? '');
    
    // SIEMPRE anular (estado 21), nunca eliminar
    $this->registrarHistorial($contrato_id, "Contrato anulado", $nombreVendedor);
    $update = $this->db->prepare("UPDATE contratos SET estado_contrato_id = 21 WHERE id = ?");
    $update->execute([$contrato_id]);
    
    return ['success' => true, 'message' => 'Contrato anulado'];
    }
}

?>