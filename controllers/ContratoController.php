<?php
session_start();
require_once __DIR__ . '/../models/ContratoModel.php';

if (!isset($_SESSION['usuario_id'])) {
    die("No hay sesión iniciada");
}

$model = new ContratoModel();
$action = $_POST['accion'] ?? $_GET['action'] ?? '';

// CREAR CONTRATO
if ($action === 'crear') {
    $errores = [];
    if (empty($_POST['propiedad_id'])) $errores[] = "Propiedad requerida";
    if (empty($_POST['nombre_comprador'])) $errores[] = "Nombre requerido";
    if (empty($_POST['correo_comprador'])) $errores[] = "Correo requerido";
    if (empty($_POST['dui_comprador'])) $errores[] = "DUI requerido";
    if (empty($_POST['monto_acordado']) || floatval($_POST['monto_acordado']) <= 0) $errores[] = "Monto inválido";
    
    if (!empty($errores)) {
        $_SESSION['error_mensaje'] = implode("<br>", $errores);
        header('Location: ../views/contrato.php');
        exit;
    }
    
    $datos = [
        'propiedad_id' => $_POST['propiedad_id'],
        'vendedor_id' => $_SESSION['usuario_id'],
        'plantilla_id' => null,
        'nombre_comprador' => trim($_POST['nombre_comprador']),
        'correo_comprador' => trim($_POST['correo_comprador']),
        'dui_comprador' => trim($_POST['dui_comprador']),
        'monto_acordado' => floatval($_POST['monto_acordado']),
        'moneda' => $_POST['moneda'] ?? 'USD',
        'tipo_contrato_id' => intval($_POST['tipo_contrato_id'] ?? 14)
    ];
    
    $resultado = $model->crear($datos);
    $_SESSION[$resultado ? 'mensaje' : 'error_mensaje'] = $resultado ? "✅ Contrato creado" : "❌ Error al crear";
    header('Location: ../views/contrato.php');
    exit;
}

// ELIMINAR CONTRATO
if ($action === 'eliminar' && isset($_GET['id'])) {
    $resultado = $model->eliminarContrato($_GET['id'], $_SESSION['usuario_id']);
    $_SESSION[$resultado['success'] ? 'mensaje' : 'error_mensaje'] = $resultado['message'] ?? $resultado['error'];
    header('Location: ../views/contrato.php');
    exit;
}

// LISTAR
if ($action === 'listar') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'contratos' => $model->getContratosByVendedor($_SESSION['usuario_id'])]);
    exit;
}

// GENERAR Y DESCARGAR
if ($action === 'generar' && isset($_GET['id'])) {
    $resultado = $model->generarDocumento($_GET['id']);
    
    if ($resultado['success']) {
        $archivo = $resultado['ruta'];
        if (file_exists($archivo)) {
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="' . $resultado['nombre'] . '"');
            header('Content-Length: ' . filesize($archivo));
            readfile($archivo);
            exit;
        }
    }
    echo "Error: " . ($resultado['error'] ?? 'No se pudo generar el documento');
    exit;
}

header('Location: ../views/contrato.php');
exit;
?>