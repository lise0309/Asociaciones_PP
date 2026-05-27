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
        $valorEsc = htmlspecialchars((string)$valor, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $xml = str_replace($var, $valorEsc, $xml);
    }

    $zip->addFromString($nombre, $xml);
}
$zip->close();

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