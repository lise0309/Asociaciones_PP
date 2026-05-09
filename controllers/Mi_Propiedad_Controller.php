<?php
/**
 * CONTROLADOR DE PROPIEDADES
 * PP Bienes Raíces
 */

session_start();
require_once __DIR__ . '/../models/Mis_Propiedades_Model.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../views/login.php');
    exit;
}

$vendedorId = $_SESSION['usuario_id'];
$model = new PropiedadModel();

// ============================================
// PROCESAR ELIMINACIÓN
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    
    $propiedad_id = $_POST['propiedad_id'] ?? '';
    
    if (empty($propiedad_id)) {
        $_SESSION['error_mensaje'] = "ID de propiedad no válido";
        header('Location: ../views/mis_propiedades.php');
        exit;
    }
    
    // Verificar que la propiedad pertenece al vendedor
    if (!$model->esDelVendedor($propiedad_id, $vendedorId)) {
        $_SESSION['error_mensaje'] = "No tienes permiso para eliminar esta propiedad";
        header('Location: ../views/mis_propiedades.php');
        exit;
    }
    
    // Obtener fotos para eliminar archivos físicos
    $fotos = $model->getFotos($propiedad_id);
    
    // Eliminar archivos de imagen del servidor
    $basePath = $_SERVER['DOCUMENT_ROOT'] . '/Asociaciones_PP/';
    foreach ($fotos as $foto) {
        $rutaOriginal = $basePath . ltrim($foto['url_foto_original'], '/');
        $rutaMiniatura = $basePath . ltrim($foto['url_foto_miniatura'], '/');
        
        if (file_exists($rutaOriginal)) {
            @unlink($rutaOriginal);
        }
        if (file_exists($rutaMiniatura)) {
            @unlink($rutaMiniatura);
        }
    }
    
    // Eliminar carpeta de la propiedad
    $carpeta = $basePath . 'uploads/propiedades/' . $propiedad_id;
    if (is_dir($carpeta)) {
        $archivos = glob($carpeta . '/*');
        foreach ($archivos as $archivo) {
            if (is_file($archivo)) {
                @unlink($archivo);
            }
        }
        @rmdir($carpeta);
    }
    
    // Eliminar la propiedad de la base de datos
    if ($model->eliminar($propiedad_id)) {
        $_SESSION['mensaje'] = "✅ Propiedad eliminada correctamente";
        $_SESSION['mensaje_tipo'] = 'success';
    } else {
        $_SESSION['error_mensaje'] = "❌ Error al eliminar la propiedad";
    }
    
    header('Location: ../views/mis_propiedades.php');
    exit;
}

// Si no es POST, redirigir
header('Location: ../views/mis_propiedades.php');
exit;
?>