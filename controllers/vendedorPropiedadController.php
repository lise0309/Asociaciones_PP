<?php
/**
 * CONTROLADOR PROPIEDAD PARA VENDEDOR
 * PP Bienes Raíces — controllers/vendedorPropiedadController.php
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../models/vendedorPropiedadModel.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../views/login.php');
    exit;
}

$vendedorId = $_SESSION['usuario_id'];
$model      = new VendedorPropiedadModel();

// ── ELIMINAR (GET) ──────────────────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'eliminar' && isset($_GET['id'])) {
    $propiedad_id = $_GET['id'];
    $propiedad    = $model->getById($propiedad_id, $vendedorId);
    if ($propiedad && $model->eliminar($propiedad_id, $vendedorId)) {
        $_SESSION['mensaje'] = 'Propiedad eliminada correctamente';
    } else {
        $_SESSION['error_mensaje'] = 'Error al eliminar';
    }
    header('Location: ../views/Mis_Propiedades.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/Mis_Propiedades.php');
    exit;
}

$accion       = $_POST['accion']       ?? '';
$propiedad_id = $_POST['propiedad_id'] ?? '';

// ── VALIDAR ─────────────────────────────────────────────
$errores = [];
if (empty(trim($_POST['titulo']       ?? '')))                        $errores[] = 'El título es requerido';
if (empty($_POST['tipo_inmueble']     ?? ''))                         $errores[] = 'El tipo de inmueble es requerido';
if (empty($_POST['tipo_negocio']      ?? ''))                         $errores[] = 'La modalidad es requerida';
if (empty($_POST['precio'] ?? '') || floatval($_POST['precio']) <= 0) $errores[] = 'El precio debe ser mayor a 0';
if (empty(trim($_POST['descripcion']  ?? '')))                        $errores[] = 'La descripción es requerida';
if (empty($_POST['departamento']      ?? ''))                         $errores[] = 'El departamento es requerido';
if (empty(trim($_POST['municipio']    ?? '')))                        $errores[] = 'El municipio es requerido';
if (empty(trim($_POST['direccion']    ?? '')))                        $errores[] = 'La dirección es requerida';

if (!empty($errores)) {
    $_SESSION['error_mensaje'] = implode(', ', $errores);
    header('Location: ' . ($propiedad_id
        ? '../views/propiedad_form.php?id=' . $propiedad_id
        : '../views/propiedad_form.php'));
    exit;
}

// ── DATOS ───────────────────────────────────────────────
$datos = [
    'vendedor_id'           => $vendedorId,
    'tipo_inmueble_id'      => (int)($_POST['tipo_inmueble']     ?? 1),
    'tipo_negocio_id'       => (int)($_POST['tipo_negocio']      ?? 6),
    'estado_publicacion_id' => ($_POST['visibilidad'] ?? 'publica') === 'publica' ? 11 : 12,
    'titulo'                => trim($_POST['titulo']),
    'descripcion'           => trim($_POST['descripcion']),
    'precio'                => floatval($_POST['precio']),
    'habitaciones'          => intval($_POST['habitaciones']        ?? 0),
    'banos'                 => intval($_POST['banos']               ?? 0),
    'metros_construccion'   => !empty($_POST['metros_construccion']) ? floatval($_POST['metros_construccion']) : null,
    'metros_terreno'        => !empty($_POST['metros_terreno'])      ? floatval($_POST['metros_terreno'])      : null,
    'estacionamiento'       => intval($_POST['estacionamiento']     ?? 0),
    'piscina'               => isset($_POST['piscina'])   ? 1 : 0,
    'amueblado'             => isset($_POST['amueblado']) ? 1 : 0,
    'destacar'              => (int)($_POST['destacar_radio']       ?? $_POST['destacar'] ?? 0),
    'departamento'          => $_POST['departamento'],
    'municipio'             => trim($_POST['municipio']),
    'direccion'             => trim($_POST['direccion']),
    'referencia'            => trim($_POST['referencia']            ?? ''),
    'latitud'               => !empty($_POST['latitud'])  ? $_POST['latitud']  : null,
    'longitud'              => !empty($_POST['longitud']) ? $_POST['longitud'] : null,
];

// ── CREAR / EDITAR ───────────────────────────────────────
if ($accion === 'crear') {
    $propiedad_id = $model->crear($datos);
    if (!$propiedad_id) {
        $_SESSION['error_mensaje'] = 'Error al crear la propiedad';
        header('Location: ../views/propiedad_form.php');
        exit;
    }
} else {
    if (!$model->actualizar($propiedad_id, $datos)) {
        $_SESSION['error_mensaje'] = 'Error al actualizar la propiedad';
        header('Location: ../views/propiedad_form.php?id=' . $propiedad_id);
        exit;
    }
    // Eliminar fotos marcadas
    if (!empty($_POST['fotos_eliminar']) && is_array($_POST['fotos_eliminar'])) {
        foreach ($_POST['fotos_eliminar'] as $foto_id) {
            $model->eliminarFoto(trim($foto_id), $propiedad_id);
        }
    }
}

// ── SUBIR FOTOS ──────────────────────────────────────────
// Mismo método exacto que funciona en test_foto.php
if ($propiedad_id && !empty($_FILES['fotos']['name'][0])) {

    $upload_dir = dirname(__DIR__) . '/uploads/propiedades/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    $fotos_existentes = $model->getFotos($propiedad_id);
    $orden_actual     = count($fotos_existentes);

    foreach ($_FILES['fotos']['tmp_name'] as $i => $tmp_name) {
        if ($_FILES['fotos']['error'][$i] !== UPLOAD_ERR_OK) continue;

        $ext          = strtolower(pathinfo($_FILES['fotos']['name'][$i], PATHINFO_EXTENSION)) ?: 'jpg';
        $nombre       = substr($propiedad_id, 0, 8) . '_' . time() . '_' . $i . '.' . $ext;
        $ruta_fisica  = $upload_dir . $nombre;

        if (!move_uploaded_file($tmp_name, $ruta_fisica)) continue;

        $url = 'uploads/propiedades/' . $nombre;
        $es_portada = ($orden_actual === 0 && $i === 0 && empty($fotos_existentes));

        $model->guardarFoto($propiedad_id, $url, $url, $orden_actual + $i, $es_portada);
    }
}

$_SESSION['mensaje']      = $accion === 'crear' ? 'Propiedad creada exitosamente' : 'Propiedad actualizada';
$_SESSION['mensaje_tipo'] = 'success';
header('Location: ../views/Mis_Propiedades.php');
exit;