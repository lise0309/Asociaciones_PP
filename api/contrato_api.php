<?php
session_start();
require_once __DIR__ . '/../models/ContratoModel.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$model = new ContratoModel();
$db = Database::conectar();  // ← IMPORTANTE: Obtener conexión a la BD
$action = $_GET['action'] ?? '';

// Listar contratos
if ($action === 'listar') {
    $contratos = $model->getContratosByVendedor($_SESSION['usuario_id']);
    echo json_encode(['success' => true, 'contratos' => $contratos]);
    exit;
}

// Generar documento
if ($action === 'generar' && isset($_GET['id'])) {
    $resultado = $model->generarDocumento($_GET['id']);
    echo json_encode($resultado);
    exit;
}

// ============================================
// OBTENER HISTORIAL DEL CONTRATO
// ============================================
if ($action === 'historial' && isset($_GET['id'])) {
    try {
        $sql = "SELECT * FROM historial_contrato WHERE contrato_id = ? ORDER BY fecha_accion DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$_GET['id']]);
        $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'historial' => $historial]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Acción no válida']);
?>