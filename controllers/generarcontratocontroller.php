<?php
/**
 * GENERAR CONTRATO CONTROLLER
 * PP Bienes Raíces — controllers/generarcontratocontroller.php
 * Rellena TODAS las variables del contrato incluyendo notaría,
 * fecha completa, colindancias, metros, forma de pago, etc.
 */

ob_start();
session_start();

if (!isset($_SESSION['usuario_id'])) {
    ob_end_clean(); http_response_code(403); die('No autorizado');
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/contratomodel.php';

$id = $_GET['id'] ?? '';
if (!$id) { ob_end_clean(); die('ID requerido'); }

$model    = new ContratoModel();
$contrato = $model->getById($id);
if (!$contrato) { ob_end_clean(); die('Contrato no encontrado'); }

$raiz          = dirname(__DIR__);
$rutaPlantilla = $raiz . '/' . ltrim($contrato['archivo_plantilla'] ?? '', '/');
if (!file_exists($rutaPlantilla)) {
    ob_end_clean(); die('Plantilla no encontrada: ' . $rutaPlantilla);
}

// ── Helpers de fecha en español ────────────────────────────────
function diaLetras(int $dia): string {
    $letras = ['','uno','dos','tres','cuatro','cinco','seis','siete','ocho','nueve',
               'diez','once','doce','trece','catorce','quince','dieciséis',
               'diecisiete','dieciocho','diecinueve','veinte','veintiuno',
               'veintidós','veintitrés','veinticuatro','veinticinco','veintiséis',
               'veintisiete','veintiocho','veintinueve','treinta','treinta y uno'];
    return $letras[$dia] ?? (string)$dia;
}

function mesLetras(int $mes): string {
    $meses = ['','enero','febrero','marzo','abril','mayo','junio',
              'julio','agosto','septiembre','octubre','noviembre','diciembre'];
    return $meses[$mes] ?? '';
}

function anioLetras(int $anio): string {
    // Ej: 2026 → "dos mil veintiséis"
    $miles = (int)($anio / 1000);
    $resto = $anio % 1000;
    $base  = $miles === 1 ? 'dos mil' : numeroAPalabras($miles) . ' mil';
    return $resto > 0 ? $base . ' ' . numeroAPalabras($resto) : $base;
}

// ── Variables ────────────────────────────────────────────────
$ts            = strtotime($contrato['fecha_generacion'] ?? 'now');
$monto_num     = number_format((float)$contrato['monto_acordado'], 2, '.', ',');
$fecha_gen     = date('d/m/Y', $ts);
$dia_n         = (int)date('d', $ts);
$mes_n         = (int)date('m', $ts);
$anio_n        = (int)date('Y', $ts);
$monto_palabras = numeroAPalabras((float)$contrato['monto_acordado']) . ' dólares con 00/100';

// Datos de la propiedad — metros en letras
$metros_t = (float)($contrato['metros_terreno'] ?? 0);
$metros_letras = $metros_t > 0
    ? numeroAPalabras((int)$metros_t)
    : 'no especificados';

$variables = [
    // ── Datos del documento ──
    '{{numero_escritura}}'   => strtoupper(substr($id, 0, 8)),
    '{{hora_escritura}}'     => date('H:i', $ts),
    '{{dia_numero}}'         => str_pad($dia_n, 2, '0', STR_PAD_LEFT),
    '{{dia_letras}}'         => diaLetras($dia_n),
    '{{mes_escritura}}'      => mesLetras($mes_n),
    '{{anio_letras}}'        => anioLetras($anio_n),
    '{{anio_numero}}'        => date('y', $ts),   // últimos 2 dígitos para el "(20__)"

    // ── Notario (datos por defecto — se personalizan según el notario real) ──
    '{{nombre_notario}}'     => $contrato['nombre_notario']    ?? 'NOMBRE DEL NOTARIO',
    '{{domicilio_notario}}'  => $contrato['domicilio_notario'] ?? 'San Salvador',

    // ── Vendedor ──
    '{{vendedor}}'           => $contrato['vendedor_nombre']   ?? '',
    '{{telefono_vendedor}}'  => $contrato['vendedor_telefono'] ?? '',
    '{{correo_vendedor}}'    => $contrato['vendedor_correo']   ?? '',

    // ── Comprador ──
    '{{nombre_comprador}}'   => $contrato['nombre_comprador']  ?? '',
    '{{dui_comprador}}'      => $contrato['dui_comprador']     ?? '',
    '{{correo_comprador}}'   => $contrato['correo_comprador']  ?? '',

    // ── Propiedad ──
    '{{propiedad}}'          => $contrato['titulo_anuncio']    ?? '',
    '{{direccion}}'          => $contrato['direccion_exacta']  ?? $contrato['titulo_anuncio'] ?? '',
    '{{municipio}}'          => $contrato['municipio']         ?? '',
    '{{departamento}}'       => $contrato['departamento']      ?? '',
    '{{metros_terreno}}'     => $metros_t > 0 ? number_format($metros_t, 2) : 'NO ESPECIFICADO',
    '{{metros_letras}}'      => $metros_letras,
    '{{matricula_inmueble}}' => $contrato['matricula_inmueble'] ?? 'POR DETERMINAR',

    // ── Colindancias ──
    '{{colindancia_norte}}'    => $contrato['colindancia_norte']    ?? 'POR DETERMINAR',
    '{{colindancia_sur}}'      => $contrato['colindancia_sur']      ?? 'POR DETERMINAR',
    '{{colindancia_oriente}}'  => $contrato['colindancia_oriente']  ?? 'POR DETERMINAR',
    '{{colindancia_poniente}}' => $contrato['colindancia_poniente'] ?? 'POR DETERMINAR',

    // ── Precio ──
    '{{monto}}'              => $monto_num,
    '{{monto_palabras}}'     => $monto_palabras,
    '{{moneda}}'             => $contrato['moneda']             ?? 'USD',

    // ── Contrato ──
    '{{forma_pago}}'         => $contrato['forma_pago']         ?? 'Al contado',
    '{{tipo_contrato}}'      => $contrato['tipo_nombre']        ?? '',
    '{{fecha}}'              => $fecha_gen,
    '{{fecha_generacion}}'   => $fecha_gen,
    '{{id_contrato}}'        => strtoupper(substr($id, 0, 8)),
];

// ── Copiar a temporal y procesar ────────────────────────────
$tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ctrato_' . uniqid() . '.docx';
copy($rutaPlantilla, $tmpFile);

$zip = new ZipArchive();
if ($zip->open($tmpFile) !== true) {
    ob_end_clean(); die('No se pudo abrir el DOCX');
}

for ($i = 0; $i < $zip->numFiles; $i++) {
    $nombre = $zip->getNameIndex($i);
    if (!preg_match('/\.(xml|rels)$/i', $nombre)) continue;

    $xml = $zip->getFromName($nombre);
    if ($xml === false) continue;

    if (!str_contains($nombre, 'document') &&
        !str_contains($nombre, 'header')   &&
        !str_contains($nombre, 'footer'))  continue;

    // Desfragmentar variables (por si Word fragmentó alguna)
    $xml = desfragmentarVariables($xml);

    // Reemplazar todas las variables
    foreach ($variables as $var => $valor) {
        // Saltar variables de firma — se insertan como imagen después
        if (strpos($valor, '###FIRMA_IMG###') === 0) continue;
        $valorEsc = htmlspecialchars((string)$valor, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $xml = str_replace($var, $valorEsc, $xml);
    }

    $zip->addFromString($nombre, $xml);
}
$zip->close();

// ── Insertar imágenes de firma en el DOCX ──────────────────
insertarImagenesFirma($tmpFile, $variables);

// ── Guardar en servidor + actualizar BD ──────────────────────
try {
    $db      = Database::conectar();
    $dirGen  = $raiz . '/uploads/contratos/';
    if (!is_dir($dirGen)) mkdir($dirGen, 0755, true);

    $nombreArch = 'contrato_' . strtoupper(substr($id,0,8)) . '_' . date('Ymd_His') . '.docx';
    copy($tmpFile, $dirGen . $nombreArch);

    $db->prepare("UPDATE contratos SET archivo_generado=:r WHERE id=:id")
       ->execute([':r' => 'uploads/contratos/' . $nombreArch, ':id' => $id]);

    // Solo cambiar a "Enviado" si estaba en Borrador
    $estEnv = $db->query("SELECT id FROM opciones_sistema WHERE categoria='estado_contrato' AND nombre_opcion='Enviado' LIMIT 1")->fetchColumn();
    if ($estEnv && ($contrato['estado_nombre'] ?? '') === 'Borrador') {
        $db->prepare("UPDATE contratos SET estado_contrato_id=:e WHERE id=:id")
           ->execute([':e' => $estEnv, ':id' => $id]);
    }

    $model->historial($id, 'Documento generado y descargado', $_SESSION['usuario_id']);
} catch (Exception $e) {
    error_log('generarcontrato: ' . $e->getMessage());
}

// ── Descargar ────────────────────────────────────────────────
ob_get_clean();
$nombre = 'Contrato_' . strtoupper(substr($id,0,8)) . '_' . date('Ymd') . '.docx';
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="' . $nombre . '"');
header('Content-Length: ' . filesize($tmpFile));
header('Cache-Control: no-cache');
readfile($tmpFile);
@unlink($tmpFile);
exit;

/* ══════════════════════════════════════════════════════════════
   INSERTAR IMÁGENES DE FIRMA EN EL DOCX
══════════════════════════════════════════════════════════════ */
function insertarImagenesFirma(string $docxPath, array $variables): void {
    $marcadores = [];
    foreach ($variables as $var => $valor) {
        if (strpos($valor, '###FIRMA_IMG###') === 0) {
            // Extraer ruta e imagen
            $partes = explode('###', $valor);
            // $partes[1] = ruta, $partes[2] = nombre
            $marcadores[$var] = ['ruta' => $partes[1] ?? '', 'nombre' => $partes[2] ?? ''];
        }
    }
    if (empty($marcadores)) return;
    // Si llegamos aquí, hay firmas para insertar

    $zip = new ZipArchive();
    if ($zip->open($docxPath) !== true) return;

    $xmlDoc = $zip->getFromName('word/document.xml');
    $xmlDoc = desfragmentarVariables($xmlDoc); // Asegurar que {{firma_imagen_*}} no estén fragmentados
    $xmlRels = $zip->getFromName('word/_rels/document.xml.rels') ?: '';

    $rIdCounter = 100; // Empezar en rId100 para no colisionar

    foreach ($marcadores as $var => $info) {
        $rutaImg = $info['ruta'];
        $nombre  = $info['nombre'];

        if (!file_exists($rutaImg)) {
            // Si no hay imagen, reemplazar con nombre
            $xmlDoc = str_replace(
                htmlspecialchars($var, ENT_XML1),
                htmlspecialchars($nombre),
                $xmlDoc
            );
            $xmlDoc = str_replace($var, htmlspecialchars($nombre), $xmlDoc);
            continue;
        }

        // Leer imagen y obtener dimensiones
        $imgData = file_get_contents($rutaImg);
        $size    = getimagesize($rutaImg);
        $w_px    = $size[0] ?? 200;
        $h_px    = $size[1] ?? 60;

        // Escalar a máx 200x60 píxeles manteniendo proporción
        $maxW = 200; $maxH = 60;
        $ratio = min($maxW / $w_px, $maxH / $h_px);
        $w_emu = (int)($w_px * $ratio * 9525); // 1px = 9525 EMU
        $h_emu = (int)($h_px * $ratio * 9525);

        // Agregar imagen al ZIP
        $imgNombre = 'word/media/firma_' . $rIdCounter . '.png';
        $rId = 'rId' . $rIdCounter;
        $zip->addFromString($imgNombre, $imgData);

        // Agregar relación
        $relXml = '<Relationship Id="' . $rId . '" '
            . 'Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" '
            . 'Target="media/firma_' . $rIdCounter . '.png"/>';

        // Insertar en .rels
        $xmlRels = str_replace('</Relationships>', $relXml . '</Relationships>', $xmlRels);

        // XML de imagen inline para Word
        $imgXml = '<w:r><w:rPr/><w:drawing>'
            . '<wp:inline distT="0" distB="0" distL="0" distR="0"'
            . ' xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing">'
            . '<wp:extent cx="' . $w_emu . '" cy="' . $h_emu . '"/>'
            . '<wp:effectExtent l="0" t="0" r="0" b="0"/>'
            . '<wp:docPr id="' . $rIdCounter . '" name="firma_' . $rIdCounter . '"/>'
            . '<wp:cNvGraphicFramePr><a:graphicFrameLocks xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" noChangeAspect="1"/></wp:cNvGraphicFramePr>'
            . '<a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">'
            . '<a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">'
            . '<pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">'
            . '<pic:nvPicPr>'
            . '<pic:cNvPr id="' . $rIdCounter . '" name="firma_' . $rIdCounter . '"/>'
            . '<pic:cNvPicPr><a:picLocks noChangeAspect="1" noChangeArrowheads="1"/></pic:cNvPicPr>'
            . '</pic:nvPicPr>'
            . '<pic:blipFill>'
            . '<a:blip xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" r:embed="' . $rId . '"/>'
            . '<a:stretch><a:fillRect/></a:stretch>'
            . '</pic:blipFill>'
            . '<pic:spPr bwMode="auto">'
            . '<a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $w_emu . '" cy="' . $h_emu . '"/></a:xfrm>'
            . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom>'
            . '<a:noFill/>'
            . '</pic:spPr>'
            . '</pic:pic></a:graphicData></a:graphic>'
            . '</wp:inline></w:drawing></w:r>';

        // Reemplazar el placeholder en el XML del documento
        // El placeholder está dentro de un <w:t> tag
        $placeholder = htmlspecialchars($var, ENT_XML1);
        $xmlDoc = str_replace($placeholder, $imgXml, $xmlDoc);
        $xmlDoc = str_replace($var, $imgXml, $xmlDoc);

        $rIdCounter++;
    }

    // Guardar cambios
    $zip->addFromString('word/document.xml', $xmlDoc);
    if ($xmlRels) $zip->addFromString('word/_rels/document.xml.rels', $xmlRels);
    $zip->close();
}

/* ══════════════════════════════════════════════════════════════
   DESFRAGMENTAR VARIABLES
   Word parte {{variable}} en múltiples <w:r> al escribir la plantilla
══════════════════════════════════════════════════════════════ */
function desfragmentarVariables(string $xml): string {
    // Paso 1: regex que elimina tags XML dentro de {{ ... }}
    $resultado = preg_replace_callback(
        '/\{\{[^{}<>]*(?:<[^>]+>[^{}<>]*)*\}\}/',
        function($m) {
            $limpio = strip_tags($m[0]);
            $limpio = preg_replace('/\s+/', '', $limpio);
            return $limpio;
        },
        $xml
    );
    return $resultado ?? $xml;
}

/* ══════════════════════════════════════════════════════════════
   NÚMERO A PALABRAS (español)
══════════════════════════════════════════════════════════════ */
function numeroAPalabras(float $num): string {
    $entero = (int)floor($num);
    $u = ['','uno','dos','tres','cuatro','cinco','seis','siete','ocho','nueve',
          'diez','once','doce','trece','catorce','quince','dieciséis',
          'diecisiete','dieciocho','diecinueve'];
    $d = ['','','veinte','treinta','cuarenta','cincuenta','sesenta','setenta','ochenta','noventa'];
    $c = ['','cien','doscientos','trescientos','cuatrocientos','quinientos',
          'seiscientos','setecientos','ochocientos','novecientos'];
    if ($entero === 0) return 'cero';
    if ($entero < 20)  return $u[$entero];
    if ($entero < 100) {
        $dd = (int)($entero/10); $uu = $entero%10;
        return $d[$dd] . ($uu ? ' y '.$u[$uu] : '');
    }
    if ($entero < 1000) {
        $cc = (int)($entero/100); $r = $entero%100;
        $base = $cc===1 && $r>0 ? 'ciento' : $c[$cc];
        return $base . ($r ? ' '.numeroAPalabras($r) : '');
    }
    if ($entero < 1000000) {
        $miles = (int)($entero/1000); $r = $entero%1000;
        $mil = $miles===1 ? 'mil' : numeroAPalabras($miles).' mil';
        return $mil . ($r ? ' '.numeroAPalabras($r) : '');
    }
    if ($entero < 1000000000) {
        $mill = (int)($entero/1000000); $r = $entero%1000000;
        $base = $mill===1 ? 'un millón' : numeroAPalabras($mill).' millones';
        return $base . ($r ? ' '.numeroAPalabras($r) : '');
    }
    return number_format($entero,0,'.',',');
}

/* ══════════════════════════════════════════════════════════════
   OBTENER XML DE IMAGEN DE FIRMA PARA INSERTAR EN DOCX
   Retorna el texto alternativo si no hay firma, o el XML de imagen
══════════════════════════════════════════════════════════════ */
function obtenerXmlFirma(PDO $db, string $contratoId, string $rol): string {
    try {
        $stmt = $db->prepare("
            SELECT f.imagen_firma, f.nombre_firmante
            FROM firmas_contrato f
            JOIN opciones_sistema os ON os.id = f.rol_firmante_id
            WHERE f.contrato_id = :cid
              AND os.nombre_opcion = :rol
              AND f.imagen_firma IS NOT NULL
            LIMIT 1
        ");
        $stmt->execute([':cid' => $contratoId, ':rol' => $rol]);
        $firma = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$firma || empty($firma['imagen_firma'])) {
            return '_________________________ (' . $rol . ' — Pendiente)';
        }

        // Ruta física de la imagen
        // imagen_firma viene como /Asociaciones_PP/uploads/firmas/...
        // DOCUMENT_ROOT = C:/wamp/www  → ruta completa = DOCUMENT_ROOT + imagen_firma
        $rutaImg = $_SERVER['DOCUMENT_ROOT'] . $firma['imagen_firma'];
        // Fallback: si no existe, intentar con raiz del proyecto
        if (!file_exists($rutaImg)) {
            $raiz    = dirname(__DIR__);
            $rutaImg = $raiz . '/' . ltrim($firma['imagen_firma'], '/');
        }
        // Fallback 2: quitar doble /Asociaciones_PP/
        if (!file_exists($rutaImg)) {
            $rutaImg = $_SERVER['DOCUMENT_ROOT'] . preg_replace('#^/Asociaciones_PP#', '', $firma['imagen_firma']);
        }

        if (!file_exists($rutaImg)) {
            return htmlspecialchars($firma['nombre_firmante'] ?? $rol);
        }

        // Retornar nombre + indicador — la imagen se inserta via relación Word
        // Para insertar imagen real en docx necesitamos la ruta para el ZIP
        // Guardamos la ruta en un marcador especial que procesamos después
        return '###FIRMA_IMG###' . $rutaImg . '###' . htmlspecialchars($firma['nombre_firmante'] ?? $rol) . '###';
    } catch (Exception $e) {
        return '_________________________ (' . $rol . ')';
    }
}