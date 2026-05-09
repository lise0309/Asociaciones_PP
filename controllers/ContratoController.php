<?php
require_once __DIR__ . '/../models/ContratoModel.php';

session_start();

class ContratoController {
    private $model;
    
    public function __construct() {
        $this->model = new ContratoModel();
        // Verificar que el vendedor esté logueado (suponiendo que guardas 'user_id' en sesión)
        if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'vendedor') {
            // Podrías redirigir o devolver error
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }
    }
    
    /**
     * GET: Devuelve todos los contratos del vendedor + definición de etapas
     */
    public function getContratos() {
        $vendedor_id = $_SESSION['user_id']; // UUID del vendedor logueado
        $contratos = $this->model->getContratosByVendedor($vendedor_id);
        $etapas = $this->model->getEstadosContrato(); // lista de estados disponibles
        
        // Para la línea de tiempo, solo nos interesan los estados del flujo principal (Borrador, Enviado, Firmado)
        $flujoEtapas = array_filter($etapas, function($e) {
            return in_array($e['id'], [17, 18, 19]);
        });
        // Reindexar para que el front los use en orden
        $flujoEtapas = array_values($flujoEtapas);
        
        // Enriquecer cada contrato con las fechas de cada etapa
        foreach ($contratos as &$cont) {
            $fechas = [];
            foreach ($flujoEtapas as $etapa) {
                $fechas[$etapa['id']] = $cont['historial'][$etapa['id']] ?? null;
            }
            $cont['fechas_etapas'] = $fechas;
        }
        
        return [
            'contratos' => $contratos,
            'etapas' => $flujoEtapas,
            'estados_todos' => $etapas // por si quieres mostrar badges
        ];
    }
    
    /**
     * POST: Avanza un contrato a su siguiente etapa
     */
    public function avanzarEtapa() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['contrato_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Falta ID del contrato']);
            return;
        }
        
        $contrato_id = $input['contrato_id'];
        $comentario = $input['comentario'] ?? '';
        $quien = $_SESSION['nombre'] . ' ' . ($_SESSION['apellido'] ?? 'Vendedor');
        
        $resultado = $this->model->avanzarEstado($contrato_id, $comentario, $quien);
        
        if ($resultado) {
            echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'No se pudo avanzar (estado actual no permite avance o error interno)']);
        }
    }
}
?>