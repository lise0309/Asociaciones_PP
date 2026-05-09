<?php
/**
 * CONTROLADOR PROPIEDAD PARA VENDEDOR
 * PP Bienes Raíces
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../models/vendedorPropiedadModel.php';

// Verificar usuario logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../views/login.php');
    exit;
}

$vendedorId = $_SESSION['usuario_id'];
$model = new VendedorPropiedadModel();

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/mis_propiedades.php');
    exit;
}

$accion = $_POST['accion'] ?? '';
$propiedad_id = $_POST['propiedad_id'] ?? '';

// Validar campos requeridos
$errores = [];

if (empty(trim($_POST['titulo'] ?? ''))) $errores[] = "El título es requerido";
if (empty($_POST['tipo_inmueble'] ?? '')) $errores[] = "El tipo de inmueble es requerido";
if (empty($_POST['tipo_negocio'] ?? '')) $errores[] = "La modalidad es requerida";
if (empty($_POST['precio'] ?? '') || floatval($_POST['precio']) <= 0) $errores[] = "El precio debe ser mayor a 0";
if (empty(trim($_POST['descripcion'] ?? ''))) $errores[] = "La descripción es requerida";
if (empty($_POST['departamento'] ?? '')) $errores[] = "El departamento es requerido";
if (empty(trim($_POST['municipio'] ?? ''))) $errores[] = "El municipio es requerido";
if (empty(trim($_POST['direccion'] ?? ''))) $errores[] = "La dirección es requerida";

if (!empty($errores)) {
    $_SESSION['error_mensaje'] = implode(", ", $errores);
    $redirect = $propiedad_id ? "../views/propiedad_form.php?id=" . $propiedad_id : "../views/propiedad_form.php";
    header('Location: ' . $redirect);
    exit;
}

// Mapeo de valores
$tipos_inmueble = ['1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6];
$tipos_negocio = ['6' => 6, '7' => 7, '8' => 8];

// Preparar datos
$datos = [
    'vendedor_id' => $vendedorId,
    'tipo_inmueble_id' => $tipos_inmueble[$_POST['tipo_inmueble']] ?? 1,
    'tipo_negocio_id' => $tipos_negocio[$_POST['tipo_negocio']] ?? 6,
    'titulo' => trim($_POST['titulo']),
    'descripcion' => trim($_POST['descripcion']),
    'precio' => floatval($_POST['precio']),
    'habitaciones' => intval($_POST['habitaciones'] ?? 0),
    'banos' => intval($_POST['banos'] ?? 0),
    'metros_construccion' => !empty($_POST['metros_construccion']) ? floatval($_POST['metros_construccion']) : null,
    'metros_terreno' => !empty($_POST['metros_terreno']) ? floatval($_POST['metros_terreno']) : null,
    'estacionamiento' => intval($_POST['estacionamiento'] ?? 0),
    'piscina' => isset($_POST['piscina']) ? 1 : 0,
    'amueblado' => isset($_POST['amueblado']) ? 1 : 0,
    'destacar' => isset($_POST['destacar']) ? intval($_POST['destacar']) : (isset($_POST['destacar_radio']) && $_POST['destacar_radio'] == 1 ? 1 : 0),
    'departamento' => $_POST['departamento'],
    'municipio' => trim($_POST['municipio']),
    'direccion' => trim($_POST['direccion']),
    'referencia' => trim($_POST['referencia'] ?? ''),
    'latitud' => !empty($_POST['latitud']) ? $_POST['latitud'] : null,
    'longitud' => !empty($_POST['longitud']) ? $_POST['longitud'] : null,
    'estado_publicacion_id' => ($_POST['visibilidad'] ?? 'publica') === 'publica' ? 11 : 12
];

// Guardar
if ($accion === 'crear') {
    $propiedad_id = $model->crear($datos);
    if ($propiedad_id) {
        $_SESSION['mensaje'] = "Propiedad creada exitosamente";
        $_SESSION['mensaje_tipo'] = 'success';
    } else {
        $_SESSION['error_mensaje'] = "Error al crear la propiedad";
        header('Location: ../views/propiedad_form.php');
        exit;
    }
} else {
    if ($model->actualizar($propiedad_id, $datos)) {
        $_SESSION['mensaje'] = "Propiedad actualizada exitosamente";
        $_SESSION['mensaje_tipo'] = 'success';
    } else {
        $_SESSION['error_mensaje'] = "Error al actualizar la propiedad";
        header('Location: ../views/propiedad_form.php?id=' . $propiedad_id);
        exit;
    }
}

// Eliminar fotos marcadas
if (isset($_POST['fotos_eliminar']) && is_array($_POST['fotos_eliminar'])) {
    foreach ($_POST['fotos_eliminar'] as $foto_id) {
        $model->eliminarFoto($foto_id, $propiedad_id);
    }
}

// Subir nuevas fotos
if ($propiedad_id && isset($_FILES['fotos']) && !empty($_FILES['fotos']['name'][0])) {
$upload_dir = __DIR__ . '/../uploads/propiedades/' . $propiedad_id . '/';    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $fotos_existentes = $model->getFotos($propiedad_id);
    $orden_actual = count($fotos_existentes);
    
    foreach ($_FILES['fotos']['tmp_name'] as $i => $tmp_name) {
        if ($_FILES['fotos']['error'][$i] === UPLOAD_ERR_OK) {
            $extension = strtolower(pathinfo($_FILES['fotos']['name'][$i], PATHINFO_EXTENSION));
            $nombre_archivo = time() . '_' . uniqid() . '.' . $extension;
            $ruta_completa = $upload_dir . $nombre_archivo;
            
            if (move_uploaded_file($tmp_name, $ruta_completa)) {
                $url = '/Asociaciones_PP/uploads/propiedades/' . $propiedad_id . '/' . $nombre_archivo;
                $es_portada = ($orden_actual === 0 && $i === 0 && count($fotos_existentes) === 0);
                $model->guardarFoto($propiedad_id, $url, $url, $orden_actual + $i, $es_portada);
            }
        }
    }
}
// ============================================
// ELIMINAR PROPIEDAD
// ============================================
if (isset($_GET['action']) && $_GET['action'] === 'eliminar' && isset($_GET['id'])) {
    $propiedad_id = $_GET['id'];
    
    // Verificar que la propiedad pertenece al vendedor
    $propiedad = $model->getById($propiedad_id, $vendedorId);
    
    if ($propiedad) {
        if ($model->eliminar($propiedad_id, $vendedorId)) {
            $_SESSION['mensaje'] = "Propiedad eliminada correctamente";
            $_SESSION['mensaje_tipo'] = 'success';
        } else {
            $_SESSION['error_mensaje'] = "Error al eliminar la propiedad";
        }
    } else {
        $_SESSION['error_mensaje'] = "Propiedad no encontrada";
    }
    
    header('Location: ../views/mis_propiedades.php');
    exit;
}

header('Location: ../views/mis_propiedades.php');
exit;
?>