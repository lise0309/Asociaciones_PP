<?php
require_once __DIR__ . '/../config/database.php';

class ContratoModel {
    private $conn;
    
    public function __construct() {
        global $conn; // Tu conexión existente
        $this->conn = $conn;
    }
    
    /**
     * Obtiene los estados de contrato desde opciones_sistema
     * @return array Lista de estados con id, nombre, color (hex)
     */
    public function getEstadosContrato() {
        $sql = "SELECT id, nombre_opcion as nombre, valor_extra as color 
                FROM opciones_sistema 
                WHERE categoria = 'estado_contrato' AND disponible = 1
                ORDER BY id";
        $result = $this->conn->query($sql);
        $estados = [];
        while ($row = $result->fetch_assoc()) {
            $estados[] = $row;
        }
        return $estados;
    }
    
    /**
     * Obtiene los contratos de un vendedor específico (con datos de propiedad y comprador)
     * @param string $vendedor_id UUID del vendedor
     * @return array Contratos enriquecidos
     */
    public function getContratosByVendedor($vendedor_id) {
        $sql = "SELECT c.id, c.nombre_comprador, c.correo_comprador, c.monto_acordado, 
                       c.moneda, c.fecha_generacion, c.estado_contrato_id,
                       p.titulo_anuncio as propiedad_titulo,
                       e.nombre_opcion as estado_nombre,
                       e.valor_extra as estado_color
                FROM contratos c
                INNER JOIN propiedades p ON c.propiedad_id = p.id
                INNER JOIN opciones_sistema e ON c.estado_contrato_id = e.id
                WHERE c.vendedor_id = ?
                ORDER BY c.fecha_generacion DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $vendedor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $contratos = [];
        while ($row = $result->fetch_assoc()) {
            // Obtener el historial de cambios para este contrato
            $row['historial'] = $this->getHistorialCambios($row['id']);
            $contratos[] = $row;
        }
        return $contratos;
    }
    
    /**
     * Obtiene el historial de cambios de estado de un contrato
     * @param string $contrato_id UUID
     * @return array [ id_estado => fecha ]
     */
    private function getHistorialCambios($contrato_id) {
        $sql = "SELECT h.accion_realizada, h.fecha_accion
                FROM historial_contrato h
                WHERE h.contrato_id = ?
                ORDER BY h.fecha_accion ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $contrato_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $fechasPorEstado = [];
        // Mapear acciones a IDs de estado (según el texto)
        $mapaAcciones = [
            'Borrador' => 17,
            'Enviado' => 18,
            'Firmado' => 19,
            'Vencido' => 20,
            'Anulado' => 21
        ];
        
        while ($row = $result->fetch_assoc()) {
            $accion = $row['accion_realizada'];
            // Extraer el nombre del estado (ej: "Cambio de estado a Enviado")
            if (preg_match('/a (Borrador|Enviado|Firmado|Vencido|Anulado)/', $accion, $matches)) {
                $nombreEstado = $matches[1];
                if (isset($mapaAcciones[$nombreEstado])) {
                    $estadoId = $mapaAcciones[$nombreEstado];
                    $fechasPorEstado[$estadoId] = date('d/m/Y', strtotime($row['fecha_accion']));
                }
            }
        }
        
        // Si no hay historial, al menos la fecha de creación corresponde a "Borrador"
        return $fechasPorEstado;
    }
    
    /**
     * Avanza el contrato al siguiente estado (Borrador -> Enviado -> Firmado)
     * @param string $contrato_id
     * @param string $comentario Opcional
     * @param string $quien (nombre o email del vendedor)
     * @return bool
     */
    public function avanzarEstado($contrato_id, $comentario = '', $quien = 'Sistema') {
        // Obtener estado actual
        $sql = "SELECT estado_contrato_id FROM contratos WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $contrato_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $actual = $result->fetch_assoc();
        
        if (!$actual) return false;
        
        $estadoActualId = $actual['estado_contrato_id'];
        $siguienteId = null;
        
        // Definir flujo: 17(Borrador) -> 18(Enviado) -> 19(Firmado)
        if ($estadoActualId == 17) {
            $siguienteId = 18;
        } elseif ($estadoActualId == 18) {
            $siguienteId = 19;
        } else {
            // Ya está Firmado o estado terminal, no avanza
            return false;
        }
        
        // Obtener nombre del nuevo estado
        $sqlNombre = "SELECT nombre_opcion FROM opciones_sistema WHERE id = ?";
        $stmtNom = $this->conn->prepare($sqlNombre);
        $stmtNom->bind_param('i', $siguienteId);
        $stmtNom->execute();
        $nombreEstado = $stmtNom->get_result()->fetch_assoc()['nombre_opcion'];
        
        // Iniciar transacción
        $this->conn->begin_transaction();
        try {
            // Actualizar contrato
            $sqlUpd = "UPDATE contratos SET estado_contrato_id = ? WHERE id = ?";
            $stmtUpd = $this->conn->prepare($sqlUpd);
            $stmtUpd->bind_param('is', $siguienteId, $contrato_id);
            $stmtUpd->execute();
            
            // Insertar en historial_contrato
            $accion = "Cambio de estado a $nombreEstado";
            if (!empty($comentario)) {
                $accion .= " - Comentario: $comentario";
            }
            $sqlHist = "INSERT INTO historial_contrato (id, contrato_id, accion_realizada, quien_lo_hizo, ip_accion) 
                        VALUES (UUID(), ?, ?, ?, ?)";
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $stmtHist = $this->conn->prepare($sqlHist);
            $stmtHist->bind_param('ssss', $contrato_id, $accion, $quien, $ip);
            $stmtHist->execute();
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error al avanzar contrato: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene datos de un contrato específico (para detalles)
     */
    public function getContratoById($contrato_id) {
        $sql = "SELECT c.*, p.titulo_anuncio, u.nombre as vendedor_nombre, u.apellido as vendedor_apellido
                FROM contratos c
                JOIN propiedades p ON c.propiedad_id = p.id
                JOIN usuarios u ON c.vendedor_id = u.id
                WHERE c.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $contrato_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>