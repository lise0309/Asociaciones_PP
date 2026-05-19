<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../views/login.php');
    exit;
}

require_once __DIR__ . '/../models/ReportesModel.php';

$model = new ReportesModel();

// Preparar todos los datos para enviar a JavaScript
$data = [
    'kpi' => $model->getKPI(),
    'contratos_mes' => $model->getContratosPorMes(),
    'propiedades_tipo' => $model->getPropiedadesPorTipo(),
    'contratos_estado' => $model->getContratosPorEstado(),
    'top_vendedores' => $model->getTopVendedores(5),
    'propiedades_top' => $model->getPropiedadesDestacadas(5)
];

// Enviar como JSON para que JavaScript lo procese
header('Content-Type: application/json');
echo json_encode(['success' => true, 'data' => $data]);
?>