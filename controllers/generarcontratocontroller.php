<?php
/**
 * GENERAR CONTRATO CONTROLLER
 * PP Bienes Raíces — controllers/generarcontratocontroller.php
 * Maneja fragmentación de variables en XML de Word
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
    ob_end_clean();
    die('Plantilla no encontrada: ' . $rutaPlantilla);
}

// ── Variables ────────────────────────────────────────────
$monto_num      = number_format((float)$contrato['monto_acordado'], 2, '.', ',');
$fecha_gen      = date('d/m/Y', strtotime($contrato['fecha_generacion']));
$monto_palabras = numeroAPalabras((float)$contrato['monto_acordado']) . ' dólares con 00/100';

$variables = [
    '{{nombre_comprador}}' => $contrato['nombre_comprador']  ?? '',
    '{{dui_comprador}}'    => $contrato['dui_comprador']     ?? '',
    '{{correo_comprador}}' => $contrato['correo_comprador']  ?? '',
    '{{monto}}'            => $monto_num,
    '{{monto_palabras}}'   => $monto_palabras,
    '{{moneda}}'           => $contrato['moneda']            ?? 'USD',
    '{{propiedad}}'        => $contrato['titulo_anuncio']    ?? '',
    '{{municipio}}'        => $contrato['municipio']         ?? '',
    '{{departamento}}'     => $contrato['departamento']      ?? '',
    '{{direccion}}'        => $contrato['titulo_anuncio']    ?? '',
    '{{vendedor}}'         => $contrato['vendedor_nombre']   ?? '',
    '{{correo_vendedor}}'  => $contrato['vendedor_correo']   ?? '',
    '{{telefono_vendedor}}'=> $contrato['vendedor_telefono'] ?? '',
    '{{tipo_contrato}}'    => $contrato['tipo_nombre']       ?? '',
    '{{fecha}}'            => $fecha_gen,
    '{{fecha_generacion}}' => $fecha_gen,
    '{{id_contrato}}'      => strtoupper(substr($id, 0, 8)),
];

// ── Copiar a temporal ────────────────────────────────────
$tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ctrato_' . uniqid() . '.docx';
copy($rutaPlantilla, $tmpFile);

$zip = new ZipArchive();
if ($zip->open($tmpFile) !== true) {
    ob_end_clean(); die('No se pudo abrir el DOCX');
}

// ── Procesar cada XML del docx ───────────────────────────
for ($i = 0; $i < $zip->numFiles; $i++) {
    $nombre = $zip->getNameIndex($i);
    if (!preg_match('/\.(xml|rels)$/i', $nombre)) continue;

    $xml = $zip->getFromName($nombre);
    if ($xml === false) continue;

    // Solo procesar archivos que puedan tener variables
    if (!str_contains($nombre, 'document') &&
        !str_contains($nombre, 'header')   &&
        !str_contains($nombre, 'footer'))  continue;

    // ── PASO 1: Desfragmentar variables en XML de Word ──
    // Word parte {{variable}} en múltiples <w:r><w:t> runs
    // Necesitamos unir esos runs antes de reemplazar
    $xml = desfragmentarVariables($xml);

    // ── PASO 2: Reemplazar variables ──────────────────
    foreach ($variables as $var => $valor) {
        $valorEsc = htmlspecialchars((string)$valor, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $xml = str_replace($var, $valorEsc, $xml);
    }

    $zip->addFromString($nombre, $xml);
}

$zip->close();

// ── Guardar en servidor + actualizar BD ─────────────────
try {
    $db           = Database::conectar();
    $dirGen       = $raiz . '/uploads/contratos/';
    if (!is_dir($dirGen)) mkdir($dirGen, 0755, true);

    $nombreArch   = 'contrato_' . strtoupper(substr($id,0,8)) . '_' . date('Ymd_His') . '.docx';
    copy($tmpFile, $dirGen . $nombreArch);

    $db->prepare("UPDATE contratos SET archivo_generado=:r WHERE id=:id")
       ->execute([':r' => 'uploads/contratos/' . $nombreArch, ':id' => $id]);

    $estEnv = $db->query("SELECT id FROM opciones_sistema WHERE categoria='estado_contrato' AND nombre_opcion='Enviado' LIMIT 1")->fetchColumn();
    if ($estEnv && ($contrato['estado_nombre'] ?? '') === 'Borrador') {
        $db->prepare("UPDATE contratos SET estado_contrato_id=:e WHERE id=:id")
           ->execute([':e' => $estEnv, ':id' => $id]);
    }
    $model->historial($id, 'Documento generado y descargado', $_SESSION['usuario_id']);
} catch (Exception $e) {
    error_log('generarcontrato: ' . $e->getMessage());
}

// ── Descargar ────────────────────────────────────────────
ob_get_clean();
$nombre = 'Contrato_' . strtoupper(substr($id,0,8)) . '_' . date('Ymd') . '.docx';
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="' . $nombre . '"');
header('Content-Length: ' . filesize($tmpFile));
header('Cache-Control: no-cache');
readfile($tmpFile);
@unlink($tmpFile);
exit;

/* ══════════════════════════════════════
   DESFRAGMENTAR VARIABLES
   Word divide {{variable}} en múltiples <w:r> cuando el usuario
   escribe la plantilla. Necesitamos unirlos antes de reemplazar.
   
   Ejemplo de XML fragmentado:
   <w:r><w:t>{{nombre_</w:t></w:r><w:r><w:t>comprador}}</w:t></w:r>
   
   Se convierte en:
   <w:r><w:t>{{nombre_comprador}}</w:t></w:r>
══════════════════════════════════════ */
function desfragmentarVariables(string $xml): string {
    // Buscar secuencias que contengan {{ ... }} partidas entre runs
    // Patrón: cualquier texto que empiece con {{ y termine con }} 
    // posiblemente con tags XML en medio

    // Estrategia: encontrar {{ y }} y limpiar tags entre ellos
    $resultado = preg_replace_callback(
        '/\{\{[^{}<>]*(?:<[^>]+>[^{}<>]*)*\}\}/',
        function($m) {
            // Quitar todos los tags XML dentro de la variable
            $limpio = strip_tags($m[0]);
            // Quitar espacios extras
            $limpio = preg_replace('/\s+/', '', $limpio);
            return $limpio;
        },
        $xml
    );

    // Si el regex no capturó nada, intentar método alternativo:
    // Buscar {{ en el texto de los runs y unir hasta encontrar }}
    if ($resultado === null) return $xml;

    // Método 2: Unir runs adyacentes que tienen partes de variables
    // Detectar pattern: <w:t...>...{{...</w:t></w:r> ... <w:r...><w:t...>...}}...</w:t>
    $resultado = preg_replace_callback(
        '/(<w:t[^>]*>)([^<]*\{\{[^}]*)(<\/w:t>(?:<\/w:rPr>)?<\/w:r>(?:<w:r[^>]*>(?:<w:rPr>[^<]*(?:<[^\/][^>]*\/?>)?[^<]*<\/w:rPr>)?<w:t[^>]*>)+)([^<]*\}\}[^<]*)(<\/w:t>)/s',
        function($m) {
            // $m[2] = texto hasta {{...
            // $m[4] = ...}} texto
            // Unir eliminando el XML del medio
            $textoCompleto = $m[2] . $m[4];
            return $m[1] . $textoCompleto . $m[5];
        },
        $resultado
    );

    return $resultado ?? $xml;
}

/* ══════════════════════════════════════
   NÚMERO A PALABRAS
══════════════════════════════════════ */
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
    return number_format($entero,0,'.',',');
}