<?php
session_start();
require_once __DIR__ . '/../models/ContratoModel.php';

header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$model = new ContratoModel();

// OBTENER LA ACCIÓN (de GET o POST)
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

// Si viene por la URL tipo /api/contrato_api.php/listar
if (empty($action) && isset($_SERVER['PATH_INFO'])) {
    $action = trim($_SERVER['PATH_INFO'], '/');
}

// ============================================
// ACCIÓN: listar
// ============================================
if ($action === 'listar') {
    $contratos = $model->getContratosByVendedor($_SESSION['usuario_id']);
    echo json_encode([
        'success' => true,
        'contratos' => $contratos
    ]);
    exit;
}

// ============================================
// ACCIÓN: generar
// ============================================
if ($action === 'generar') {
    $id = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['id']) ? $_POST['id'] : '');
    if (empty($id)) {
        echo json_encode(['success' => false, 'error' => 'ID de contrato requerido']);
        exit;
    }
    $resultado = $model->generarDocumento($id);
    echo json_encode($resultado);
    exit;
}

// ============================================
// Si no hay acción o no es válida
// ============================================
echo json_encode([
    'success' => false, 
    'error' => 'Acción no válida',
    'action_recibida' => $action,
    'get' => $_GET,
    'server' => [
        'request_uri' => $_SERVER['REQUEST_URI'],
        'path_info' => $_SERVER['PATH_INFO'] ?? 'no'
    ]
]);
?>