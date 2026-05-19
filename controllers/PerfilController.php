<?php
/**
 * CONTROLADOR PERFIL
 * PP Bienes Raíces
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../models/PerfilModel.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$usuarioId = $_SESSION['usuario_id'];
$model = new PerfilModel();
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

switch ($accion) {
    case 'editar_perfil':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['nombre']) || empty($data['apellido'])) {
            echo json_encode(['success' => false, 'error' => 'Nombre y apellido son requeridos']);
            break;
        }
        
        $datos = [
            'nombre' => trim($data['nombre']),
            'apellido' => trim($data['apellido']),
            'telefono' => $data['telefono'] ?? ''
        ];
        
        $resultado = $model->actualizarPerfil($usuarioId, $datos);
        echo json_encode(['success' => $resultado]);
        break;
        
    case 'cambiar_password':
        $data = json_decode(file_get_contents('php://input'), true);
        
        $passwordActual = $data['password_actual'] ?? '';
        $nuevaPassword = $data['nueva_password'] ?? '';
        
        if (empty($passwordActual) || empty($nuevaPassword)) {
            echo json_encode(['success' => false, 'error' => 'Todos los campos son requeridos']);
            break;
        }
        
        if (strlen($nuevaPassword) < 6) {
            echo json_encode(['success' => false, 'error' => 'La nueva contraseña debe tener al menos 6 caracteres']);
            break;
        }
        
        if (!$model->verificarPassword($usuarioId, $passwordActual)) {
            echo json_encode(['success' => false, 'error' => 'Contraseña actual incorrecta']);
            break;
        }
        
        $resultado = $model->cambiarPassword($usuarioId, $nuevaPassword);
        echo json_encode(['success' => $resultado]);
        break;
        
    case 'subir_documento':
        $tipo = $_POST['tipo'] ?? '';
        
        if (empty($tipo) || empty($_FILES['archivo'])) {
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
            break;
        }
        
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Asociaciones_PP/uploads/documentos/' . $usuarioId . '/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $extension = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
        $nombreArchivo = $tipo . '_' . time() . '.' . $extension;
        $rutaCompleta = $uploadDir . $nombreArchivo;
        
        if (move_uploaded_file($_FILES['archivo']['tmp_name'], $rutaCompleta)) {
            echo json_encode(['success' => true, 'message' => 'Documento subido correctamente']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al subir el archivo']);
        }
        break;
        
    case 'cambiar_foto':
        if (empty($_FILES['foto'])) {
            echo json_encode(['success' => false, 'error' => 'No se seleccionó ninguna foto']);
            break;
        }
        
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Asociaciones_PP/uploads/fotos_perfil/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nombreArchivo = $usuarioId . '_' . time() . '.' . $extension;
        $rutaCompleta = $uploadDir . $nombreArchivo;
        $rutaWeb = '/Asociaciones_PP/uploads/fotos_perfil/' . $nombreArchivo;
        
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaCompleta)) {
            $model->guardarFotoPerfil($usuarioId, $rutaWeb);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al subir la foto']);
        }
        break;
        
    case 'eliminar_documento':
        $id = $_GET['id'] ?? '';
        echo json_encode(['success' => true]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Acción no válida']);
}
?>