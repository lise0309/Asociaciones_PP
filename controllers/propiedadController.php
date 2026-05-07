<?php
/**
 * CONTROLADOR PROPIEDAD (Procesar Formulario)
 * PP Bienes Raíces
 */

session_start();

require_once __DIR__ . '/../models/propiedadModel.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../views/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/mis_propiedades.php');
    exit;
}

$propiedadModel = new PropiedadModel();
$usuario_id = $_SESSION['usuario_id'];
$accion = $_POST['accion'] ?? 'crear';
$propiedad_id = $_POST['propiedad_id'] ?? '';

// Mapeo de valores de formulario a IDs de la BD
$tipos_inmueble = [
    '1' => 1,  // Casa
    '2' => 2,  // Apartamento  
    '3' => 3,  // Local comercial
    '4' => 4,  // Terreno
    '5' => 5   // Finca
];

$tipos_negocio = [
    '6' => 6,   // Venta
    '7' => 7,   // Alquiler
    '8' => 8    // Ambas
];

// Preparar datos
$datos = [
    'vendedor_id' => $usuario_id,
    'tipo_inmueble_id' => $tipos_inmueble[$_POST['tipo_inmueble']] ?? 1,
    'tipo_negocio_id' => $tipos_negocio[$_POST['tipo_negocio']] ?? 6,
    'titulo' => $_POST['titulo'] ?? '',
    'descripcion' => $_POST['descripcion'] ?? '',
    'precio' => $_POST['precio'] ?? 0,
    'habitaciones' => $_POST['habitaciones'] ?? 0,
    'banos' => $_POST['banos'] ?? 0,
    'metros_construccion' => $_POST['metros_construccion'] ?? null,
    'metros_terreno' => $_POST['metros_terreno'] ?? null,
    'estacionamiento' => $_POST['estacionamiento'] ?? 0,
    'destacar' => isset($_POST['destacar']),
    'departamento' => $_POST['departamento'] ?? '',
    'municipio' => $_POST['municipio'] ?? '',
    'direccion' => $_POST['direccion'] ?? '',
    'referencia' => $_POST['referencia'] ?? '',
    'latitud' => $_POST['latitud'] ?? '',
    'longitud' => $_POST['longitud'] ?? ''
];

// Procesar según acción
if ($accion === 'crear') {
    $propiedad_id = $propiedadModel->crear($datos);
    $mensaje = "Propiedad creada exitosamente";
} else {
    $propiedadModel->actualizar($propiedad_id, $datos);
    $mensaje = "Propiedad actualizada exitosamente";
}

// Procesar fotos subidas
if ($propiedad_id && isset($_FILES['fotos']) && !empty($_FILES['fotos']['name'][0])) {
    // Eliminar fotos anteriores si es edición
    if ($accion === 'editar') {
        $propiedadModel->eliminarFotos($propiedad_id);
    }
    
    $carpeta = __DIR__ . '/../uploads/propiedades/' . $propiedad_id . '/';
    if (!file_exists($carpeta)) {
        mkdir($carpeta, 0777, true);
    }
    
    foreach ($_FILES['fotos']['tmp_name'] as $i => $tmp_name) {
        if ($_FILES['fotos']['error'][$i] === UPLOAD_ERR_OK) {
            $extension = pathinfo($_FILES['fotos']['name'][$i], PATHINFO_EXTENSION);
            $nombre_archivo = uniqid() . '.' . $extension;
            $ruta_original = $carpeta . $nombre_archivo;
            
            if (move_uploaded_file($tmp_name, $ruta_original)) {
                $url_original = 'uploads/propiedades/' . $propiedad_id . '/' . $nombre_archivo;
                $url_miniatura = $url_original; // Por ahora igual, luego se puede generar miniatura
                
                $propiedadModel->guardarFoto($propiedad_id, $url_original, $url_miniatura, $i, $i === 0);
            }
        }
    }
}

// Redireccionar con mensaje
$_SESSION['mensaje'] = $mensaje;
$_SESSION['mensaje_tipo'] = 'success';

if ($_SESSION['rol'] === 'admin') {
    header('Location: ../views/propiedades.php');
} else {
    header('Location: ../views/mis_propiedades.php');
}
exit;
?>