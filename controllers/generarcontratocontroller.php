<?php
/**
 * GENERAR CONTRATO — PP Bienes Raíces
 * controllers/generarcontratocontroller.php
 */
ob_start();
session_start();

if (!isset($_SESSION['usuario_id'])) {
    ob_end_clean(); 
    http_response_code(403); 
    die('No autorizado');
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/contratomodel.php';

/* ══════════════════════════════════════════════════════
   HELPERS NUMÉRICOS Y DE FECHA
══════════════════════════════════════════════════════ */
function numeroAPalabras(float $num): string {
    $n = (int)floor(abs($num));
    $u = ['','uno','dos','tres','cuatro','cinco','seis','siete','ocho','nueve',
          'diez','once','doce','trece','catorce','quince','dieciséis',
          'diecisiete','dieciocho','diecinueve'];
    $d = ['','','veinte','treinta','cuarenta','cincuenta','sesenta','setenta','ochenta','noventa'];
    $c = ['','cien','doscientos','trescientos','cuatrocientos','quinientos',
          'seiscientos','setecientos','ochocientos','novecientos'];
    if ($n===0) return 'cero';
    if ($n<20)  return $u[$n];
    if ($n<100) { $dd=(int)($n/10); $uu=$n%10; return $d[$dd].($uu?' y '.$u[$uu]:''); }
    if ($n<1000){ $cc=(int)($n/100); $r=$n%100; $b=($cc===1&&$r>0)?'ciento':$c[$cc]; return $b.($r?' '.numeroAPalabras($r):''); }
    if ($n<1000000){ $m=(int)($n/1000); $r=$n%1000; $mil=$m===1?'mil':numeroAPalabras($m).' mil'; return $mil.($r?' '.numeroAPalabras($r):''); }
    if ($n<1000000000){ $m=(int)($n/1000000); $r=$n%1000000; $b=$m===1?'un millón':numeroAPalabras($m).' millones'; return $b.($r?' '.numeroAPalabras($r):''); }
    return number_format($n,0,'.',',');
}

function diaLetras(int $d): string {
    $l=['','uno','dos','tres','cuatro','cinco','seis','siete','ocho','nueve','diez','once','doce','trece','catorce','quince','dieciséis','diecisiete','dieciocho','diecinueve','veinte','veintiuno','veintidós','veintitrés','veinticuatro','veinticinco','veintiséis','veintisiete','veintiocho','veintinueve','treinta','treinta y uno'];
    return $l[$d] ?? (string)$d;
}

function mesLetras(int $m): string {
    $ms=['','enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    return $ms[$m] ?? '';
}

function anioLetras(int $a): string {
    $miles=(int)($a/1000); $resto=$a%1000;
    $base=$miles===2?'dos mil':numeroAPalabras($miles).' mil';
    return $resto>0?$base.' '.numeroAPalabras($resto):$base;
}

/* ══════════════════════════════════════════════════════
   LIMPIAR PLACEHOLDERS FRAGMENTADOS POR WORD
══════════════════════════════════════════════════════ */
function limpiarPlaceholders(string $xml): string {
    return preg_replace_callback('/\{\{(.*?)\}\}/s', function($m){
        $limpio = strip_tags($m[1]);
        $limpio = preg_replace('/\s+/','',$limpio);
        return '{{'.$limpio.'}}';
    }, $xml);
}

/* ══════════════════════════════════════════════════════
   RESOLVER RUTA FÍSICA DE FIRMA
══════════════════════════════════════════════════════ */
function resolverRutaFirma(string $rutaBD, string $raiz): string {
    if (!$rutaBD) return '';
    $nombre = basename(str_replace('\\','/',$rutaBD));
    $ruta1  = str_replace('\\','/',$raiz).'/uploads/firmas/'.$nombre;
    if (file_exists($ruta1)) return $ruta1;
    $docRoot = rtrim(str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']??''),'/');
    $ruta2   = $docRoot.str_replace('\\','/',$rutaBD);
    if (file_exists($ruta2)) return $ruta2;
    return '';
}

/* ══════════════════════════════════════════════════════
   CREAR XML DE IMAGEN SIMPLE (SIN DAÑAR EL DOCUMENTO)
══════════════════════════════════════════════════════ */
function crearXmlImagen($rutaImg, $rId) {
    if (!file_exists($rutaImg)) return '';
    
    $imgData = file_get_contents($rutaImg);
    $info = getimagesize($rutaImg);
    $anchoPx = $info[0] ?? 200;
    $altoPx = $info[1] ?? 60;
    
    $anchoEmu = $anchoPx * 9525;
    $altoEmu = $altoPx * 9525;
    
    $maxAncho = 1800000;
    $maxAlto = 720000;
    if ($anchoEmu > $maxAncho) {
        $ratio = $maxAncho / $anchoEmu;
        $anchoEmu = $maxAncho;
        $altoEmu *= $ratio;
    }
    if ($altoEmu > $maxAlto) {
        $ratio = $maxAlto / $altoEmu;
        $altoEmu = $maxAlto;
        $anchoEmu *= $ratio;
    }
    
    return [
        'data' => $imgData,
        'xml' => '<w:r><w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"><wp:extent cx="' . round($anchoEmu) . '" cy="' . round($altoEmu) . '"/><wp:docPr id="' . $rId . '" name="Firma"/><a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:nvPicPr><pic:cNvPr id="' . $rId . '" name="Firma"/><pic:cNvPicPr/></pic:nvPicPr><pic:blipFill><a:blip r:embed="rId' . $rId . '"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill><pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' . round($anchoEmu) . '" cy="' . round($altoEmu) . '"/></a:xfrm><a:prstGeom prst="rect"/></pic:spPr></pic:pic></a:graphicData></a:graphic></wp:inline></w:drawing></w:r>'
    ];
}

/* ══════════════════════════════════════════════════════
   FLUJO PRINCIPAL
══════════════════════════════════════════════════════ */
$id = $_GET['id'] ?? '';
if (!$id) { ob_end_clean(); die('ID requerido'); }

$model = new ContratoModel();
$contrato = $model->getById($id);
if (!$contrato) { ob_end_clean(); die('Contrato no encontrado'); }

$raiz = dirname(__DIR__);
$rutaPlantilla = $raiz.'/'.ltrim($contrato['archivo_plantilla']??'','/');
if (!file_exists($rutaPlantilla)) { ob_end_clean(); die('Plantilla no encontrada'); }

$db = Database::conectar();

// Fechas
$ts = strtotime($contrato['fecha_generacion'] ?? 'now');
$monto_num = number_format((float)$contrato['monto_acordado'], 2, '.', ',');
$fecha_gen = date('d/m/Y', $ts);
$dia_n = (int)date('d', $ts);
$mes_n = (int)date('m', $ts);
$anio_n = (int)date('Y', $ts);
$monto_palabras = numeroAPalabras((float)$contrato['monto_acordado']) . ' dólares con 00/100';
$metros_t = (float)($contrato['metros_terreno'] ?? 0);
$metros_letras = $metros_t > 0 ? numeroAPalabras((int)$metros_t) : 'no especificados';

// Obtener firmas
$rutaV = '';
$rutaC = '';

$stmt = $db->prepare("SELECT imagen_firma FROM firmas_contrato WHERE contrato_id = :id AND rol_firmante_id = (SELECT id FROM opciones_sistema WHERE nombre_opcion = 'Vendedor' AND categoria = 'rol_firmante' LIMIT 1) ORDER BY fecha_firma DESC LIMIT 1");
$stmt->execute([':id' => $id]);
$rutaBD_V = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT imagen_firma FROM firmas_contrato WHERE contrato_id = :id AND rol_firmante_id = (SELECT id FROM opciones_sistema WHERE nombre_opcion = 'Comprador' AND categoria = 'rol_firmante' LIMIT 1) ORDER BY fecha_firma DESC LIMIT 1");
$stmt->execute([':id' => $id]);
$rutaBD_C = $stmt->fetchColumn();

if ($rutaBD_V) {
    $archivo = $raiz . '/uploads/firmas/' . basename($rutaBD_V);
    if (file_exists($archivo)) $rutaV = $archivo;
}
if ($rutaBD_C) {
    $archivo = $raiz . '/uploads/firmas/' . basename($rutaBD_C);
    if (file_exists($archivo)) $rutaC = $archivo;
}

// Variables de texto
$variables = [
    '{{numero_escritura}}' => strtoupper(substr($id, 0, 8)),
    '{{hora_escritura}}' => date('H:i', $ts),
    '{{dia_numero}}' => str_pad($dia_n, 2, '0', STR_PAD_LEFT),
    '{{dia_letras}}' => diaLetras($dia_n),
    '{{mes_escritura}}' => mesLetras($mes_n),
    '{{anio_letras}}' => anioLetras($anio_n),
    '{{nombre_notario}}' => $contrato['nombre_notario'] ?? 'NOMBRE DEL NOTARIO',
    '{{domicilio_notario}}' => $contrato['domicilio_notario'] ?? 'San Salvador',
    '{{vendedor}}' => $contrato['vendedor_nombre'] ?? '',
    '{{telefono_vendedor}}' => $contrato['vendedor_telefono'] ?? '',
    '{{correo_vendedor}}' => $contrato['vendedor_correo'] ?? '',
    '{{nombre_comprador}}' => $contrato['nombre_comprador'] ?? '',
    '{{dui_comprador}}' => $contrato['dui_comprador'] ?? '',
    '{{correo_comprador}}' => $contrato['correo_comprador'] ?? '',
    '{{propiedad}}' => $contrato['titulo_anuncio'] ?? '',
    '{{direccion}}' => $contrato['direccion_exacta'] ?? $contrato['titulo_anuncio'] ?? '',
    '{{municipio}}' => $contrato['municipio'] ?? '',
    '{{departamento}}' => $contrato['departamento'] ?? '',
    '{{metros_terreno}}' => $metros_t > 0 ? number_format($metros_t, 2) : 'NO ESPECIFICADO',
    '{{metros_letras}}' => $metros_letras,
    '{{matricula_inmueble}}' => $contrato['matricula_inmueble'] ?? 'POR DETERMINAR',
    '{{colindancia_norte}}' => $contrato['colindancia_norte'] ?? 'POR DETERMINAR',
    '{{colindancia_sur}}' => $contrato['colindancia_sur'] ?? 'POR DETERMINAR',
    '{{colindancia_oriente}}' => $contrato['colindancia_oriente'] ?? 'POR DETERMINAR',
    '{{colindancia_poniente}}' => $contrato['colindancia_poniente'] ?? 'POR DETERMINAR',
    '{{monto}}' => $monto_num,
    '{{monto_palabras}}' => $monto_palabras,
    '{{moneda}}' => $contrato['moneda'] ?? 'USD',
    '{{forma_pago}}' => $contrato['forma_pago'] ?? 'Al contado',
    '{{tipo_contrato}}' => $contrato['tipo_nombre'] ?? '',
    '{{fecha}}' => $fecha_gen,
    '{{id_contrato}}' => strtoupper(substr($id, 0, 8)),
];

// ============================================
// PROCESAR DOCX
// ============================================
$tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'contrato_' . uniqid() . '.docx';
copy($rutaPlantilla, $tmpFile);

$zip = new ZipArchive();
if ($zip->open($tmpFile) !== true) {
    ob_end_clean();
    die('No se pudo abrir el DOCX');
}

// Leer el XML
$xmlContent = $zip->getFromName('word/document.xml');
if (!$xmlContent) {
    $zip->close();
    ob_end_clean();
    die('No se pudo leer el documento');
}

// 1. Limpiar placeholders fragmentados
$xmlContent = limpiarPlaceholders($xmlContent);

// 2. Reemplazar placeholders de texto
foreach ($variables as $placeholder => $valor) {
    $xmlContent = str_replace($placeholder, htmlspecialchars($valor, ENT_XML1, 'UTF-8'), $xmlContent);
}

// 3. Preparar relaciones
$relsContent = $zip->getFromName('word/_rels/document.xml.rels');
if (!$relsContent) {
    $relsContent = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
    <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>';
}

// 4. Asegurar tipo de contenido PNG
$contentTypes = $zip->getFromName('[Content_Types].xml');
if ($contentTypes && strpos($contentTypes, 'png') === false) {
    $contentTypes = str_replace('</Types>', '<Default Extension="png" ContentType="image/png"/></Types>', $contentTypes);
    $zip->addFromString('[Content_Types].xml', $contentTypes);
}

$rIdCounter = 500;

// 5. Insertar firma del VENDEDOR
if ($rutaV && file_exists($rutaV)) {
    $img = crearXmlImagen($rutaV, $rIdCounter);
    if ($img['xml']) {
        $zip->addFromString('word/media/firma_v_' . $rIdCounter . '.png', $img['data']);
        $relXml = '<Relationship Id="rId' . $rIdCounter . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/firma_v_' . $rIdCounter . '.png"/>';
        $relsContent = str_replace('</Relationships>', $relXml . '</Relationships>', $relsContent);
        $xmlContent = str_replace('{{firma_imagen_vendedor}}', $img['xml'], $xmlContent);
        $rIdCounter++;
    }
}

// 6. Insertar firma del COMPRADOR
if ($rutaC && file_exists($rutaC)) {
    $img = crearXmlImagen($rutaC, $rIdCounter);
    if ($img['xml']) {
        $zip->addFromString('word/media/firma_c_' . $rIdCounter . '.png', $img['data']);
        $relXml = '<Relationship Id="rId' . $rIdCounter . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/firma_c_' . $rIdCounter . '.png"/>';
        $relsContent = str_replace('</Relationships>', $relXml . '</Relationships>', $relsContent);
        $xmlContent = str_replace('{{firma_imagen_comprador}}', $img['xml'], $xmlContent);
        $rIdCounter++;
    }
}

// 7. Guardar cambios
$zip->addFromString('word/document.xml', $xmlContent);
$zip->addFromString('word/_rels/document.xml.rels', $relsContent);
$zip->close();

// 8. Enviar archivo
ob_get_clean();
$nombreDescarga = 'Contrato_' . strtoupper(substr($id, 0, 8)) . '_' . date('Ymd') . '.docx';
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="' . $nombreDescarga . '"');
header('Content-Length: ' . filesize($tmpFile));
readfile($tmpFile);
@unlink($tmpFile);
exit;
?>