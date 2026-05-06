<?php
/**
 * CONTROLADOR PROPIEDAD
 * PP Bienes Raíces
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../models/propiedadModel.php';

// Verificar autenticación
if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$propiedadModel = new PropiedadModel();
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

switch ($accion) {
    case 'listar':
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $buscar = $_GET['buscar'] ?? '';
        $tipo_inmueble = $_GET['tipo_inmueble'] ?? '';
        $negocio = $_GET['negocio'] ?? '';
        $estado = $_GET['estado'] ?? '';
        
        $limit = 10;
        $offset = ($pagina - 1) * $limit;
        
        $propiedades = $propiedadModel->getAllAdmin($buscar, $tipo_inmueble, $negocio, $estado, $limit, $offset);
        $total = $propiedadModel->getTotalCountAdmin($buscar, $tipo_inmueble, $negocio, $estado);
        $kpis = $propiedadModel->getKPIs();
        
        echo json_encode([
            'success' => true,
            'propiedades' => $propiedades,
            'total' => $total,
            'kpis' => $kpis
        ]);
        break;
        
    case 'detalle':
        $id = $_GET['id'] ?? '';
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
            break;
        }
        
        $propiedad = $propiedadModel->getByIdAdmin($id);
        $fotos = $propiedadModel->getFotos($id);
        
        if ($propiedad) {
            echo json_encode([
                'success' => true,
                'propiedad' => $propiedad,
                'fotos' => $fotos
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Propiedad no encontrada']);
        }
        break;
        
    case 'cambiar_estado':
        $id = $_GET['id'] ?? '';
        $estado_id = $_POST['estado_id'] ?? 0;
        
        if (!$id || !$estado_id) {
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
            break;
        }
        
        $resultado = $propiedadModel->updateEstado($id, $estado_id);
        echo json_encode(['success' => $resultado]);
        break;
        
    case 'eliminar':
        $id = $_GET['id'] ?? '';
        
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
            break;
        }
        
        $resultado = $propiedadModel->delete($id);
        echo json_encode(['success' => $resultado]);
        break;
        
    case 'obtenerEstados':
        $estados = $propiedadModel->getEstadosPublicacion();
        echo json_encode(['success' => true, 'estados' => $estados]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Acción no válida']);
}
?>