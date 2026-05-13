<?php
/**
 * SUBIR FOTOS — Controller AJAX
 * PP Bienes Raíces — controllers/subirFotosController.php
 * Recibe UNA foto por llamada via fetch/FormData
 */

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['ok' => false, 'msg' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

$propiedad_id = $_POST['propiedad_id'] ?? '';
$es_portada   = (int)($_POST['es_portada'] ?? 0);
$orden        = (int)($_POST['orden']      ?? 0);

if (!$propiedad_id) {
    echo json_encode(['ok' => false, 'msg' => 'ID requerido']);
    exit;
}

try {
    $db = Database::conectar();

    // Verificar que la propiedad existe y pertenece al usuario
    $prop = $db->prepare('SELECT id FROM propiedades WHERE id = :id AND vendedor_id = :uid');
    $prop->execute([':id' => $propiedad_id, ':uid' => $_SESSION['usuario_id']]);
    if (!$prop->fetch()) {
        echo json_encode(['ok' => false, 'msg' => 'Propiedad no encontrada']);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => 'Error BD: ' . $e->getMessage()]);
    exit;
}

// Verificar archivo
if (empty($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    $msgs = [
        1 => 'Archivo muy grande (límite: ' . ini_get('upload_max_filesize') . ')',
        2 => 'Archivo muy grande',
        3 => 'Upload incompleto',
        4 => 'No se recibió archivo',
        6 => 'Sin carpeta temporal',
        7 => 'No se puede escribir en disco',
    ];
    $code = $_FILES['foto']['error'] ?? -1;
    echo json_encode(['ok' => false, 'msg' => $msgs[$code] ?? "Error upload ($code)"]);
    exit;
}

// Validar tipo MIME real
$tipos_ok  = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
$tipo_real = mime_content_type($_FILES['foto']['tmp_name']);
if (!in_array($tipo_real, $tipos_ok)) {
    echo json_encode(['ok' => false, 'msg' => "Tipo no permitido: $tipo_real"]);
    exit;
}

// Carpeta destino
$upload_dir = dirname(__DIR__) . '/uploads/propiedades/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

if (!is_writable($upload_dir)) {
    echo json_encode(['ok' => false, 'msg' => 'Sin permisos en uploads/propiedades/']);
    exit;
}

// Guardar archivo con nombre único
$ext         = match($tipo_real) {
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
    default      => 'jpg',
};
$nombre      = substr($propiedad_id, 0, 8) . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
$ruta_fisica = $upload_dir . $nombre;

if (!move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_fisica)) {
    echo json_encode(['ok' => false, 'msg' => 'Error al mover archivo al servidor']);
    exit;
}

$url_original = 'uploads/propiedades/' . $nombre;

// Miniatura
$url_miniatura = generarMiniatura($ruta_fisica, $upload_dir, $propiedad_id, $ext);
if (!$url_miniatura) $url_miniatura = $url_original;

// Insertar en fotos_propiedad
try {
    $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff),
        mt_rand(0,0x0fff)|0x4000, mt_rand(0,0x3fff)|0x8000,
        mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff)
    );

    $stmt = $db->prepare('
        INSERT INTO fotos_propiedad
            (id, propiedad_id, url_foto_original, url_foto_miniatura, es_foto_portada, numero_orden)
        VALUES
            (:id, :pid, :orig, :mini, :portada, :orden)
    ');
    $ok = $stmt->execute([
        ':id'     => $uuid,
        ':pid'    => $propiedad_id,
        ':orig'   => $url_original,
        ':mini'   => $url_miniatura,
        ':portada'=> $es_portada,
        ':orden'  => $orden,
    ]);

    if ($ok) {
        echo json_encode([
            'ok'           => true,
            'url_original' => $url_original,
            'url_mini'     => $url_miniatura,
            'es_portada'   => $es_portada,
        ]);
    } else {
        echo json_encode(['ok' => false, 'msg' => 'Error al insertar en BD']);
    }
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => 'BD error: ' . $e->getMessage()]);
}

// ── MINIATURA ──
function generarMiniatura(string $ruta, string $dir, string $propId, string $ext): ?string {
    if (!extension_loaded('gd')) return null;
    try {
        $info = @getimagesize($ruta);
        if (!$info) return null;
        [$w, $h, $tipo] = $info;
        $src = match ($tipo) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($ruta),
            IMAGETYPE_PNG  => @imagecreatefrompng($ruta),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($ruta) : null,
            IMAGETYPE_GIF  => @imagecreatefromgif($ruta),
            default        => null,
        };
        if (!$src) return null;
        $ratio = min(400/$w, 300/$h);
        $nw = max(1,(int)round($w*$ratio));
        $nh = max(1,(int)round($h*$ratio));
        $mini = imagecreatetruecolor($nw, $nh);
        if ($tipo === IMAGETYPE_PNG) { imagealphablending($mini,false); imagesavealpha($mini,true); }
        imagecopyresampled($mini, $src, 0,0,0,0, $nw,$nh,$w,$h);
        $nom = substr($propId,0,8).'_mini_'.time().'_'.rand(100,999).'.jpg';
        imagejpeg($mini, $dir.$nom, 82);
        imagedestroy($src); imagedestroy($mini);
        return 'uploads/propiedades/'.$nom;
    } catch (Throwable $e) { return null; }
}