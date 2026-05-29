<?php
/**
 * DIAGNÓSTICO DE FIRMAS — PP Bienes Raíces
 * Coloca este archivo en /Asociaciones_PP/ y ábrelo en el navegador.
 * Muestra exactamente qué rutas usa PHP y qué hay en la BD.
 * ELIMINAR después de diagnosticar.
 */
session_start();
require_once __DIR__ . '/config/database.php';
$db = Database::conectar();

echo '<style>body{font-family:monospace;padding:20px;background:#f5f5f5;}
h2{background:#1A1953;color:#FFD45A;padding:10px;margin-top:20px;}
.ok{color:green;font-weight:bold;} .err{color:red;font-weight:bold;}
table{border-collapse:collapse;width:100%;background:#fff;}
td,th{border:1px solid #ccc;padding:8px;text-align:left;}
th{background:#1A1953;color:#FFD45A;}</style>';

echo '<h2>1. RUTAS DEL SERVIDOR</h2>';
echo '<table>';
echo '<tr><th>Variable</th><th>Valor</th></tr>';
echo '<tr><td>__DIR__</td><td>' . __DIR__ . '</td></tr>';
echo '<tr><td>dirname(__DIR__)</td><td>' . dirname(__DIR__) . '</td></tr>';
echo '<tr><td>$_SERVER[DOCUMENT_ROOT]</td><td>' . ($_SERVER['DOCUMENT_ROOT'] ?? 'NO DEFINIDO') . '</td></tr>';
echo '<tr><td>$_SERVER[SERVER_NAME]</td><td>' . ($_SERVER['SERVER_NAME'] ?? '') . '</td></tr>';
echo '</table>';

echo '<h2>2. CARPETA DE FIRMAS</h2>';
$posibles = [
    __DIR__ . '/uploads/firmas/',
    dirname(__DIR__) . '/uploads/firmas/',
    ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/Asociaciones_PP/uploads/firmas/',
    ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/uploads/firmas/',
];
echo '<table><tr><th>Ruta</th><th>¿Existe?</th><th>¿Escribible?</th></tr>';
foreach ($posibles as $ruta) {
    $existe    = is_dir($ruta) ? '<span class="ok">SÍ</span>' : '<span class="err">NO</span>';
    $escribible = is_dir($ruta) && is_writable($ruta) ? '<span class="ok">SÍ</span>' : '<span class="err">NO</span>';
    echo "<tr><td>$ruta</td><td>$existe</td><td>$escribible</td></tr>";
}
echo '</table>';

echo '<h2>3. FIRMAS EN LA BASE DE DATOS</h2>';
try {
    $firmas = $db->query("
        SELECT f.id, f.contrato_id, f.nombre_firmante, f.imagen_firma,
               f.fecha_firma, os.nombre_opcion AS rol, os.categoria
        FROM firmas_contrato f
        JOIN opciones_sistema os ON os.id = f.rol_firmante_id
        ORDER BY f.fecha_firma DESC
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);

    if (empty($firmas)) {
        echo '<p class="err">No hay firmas registradas en la BD.</p>';
    } else {
        echo '<table><tr><th>Contrato</th><th>Firmante</th><th>Rol</th><th>Categoría</th><th>imagen_firma (BD)</th><th>¿Archivo existe?</th><th>Fecha</th></tr>';
        foreach ($firmas as $f) {
            $rutaBD   = $f['imagen_firma'] ?? '';
            $docRoot  = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
            $raiz     = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');
            $rutaBDn  = str_replace('\\', '/', $rutaBD);

            $candidatos = [
                $docRoot . $rutaBDn,
                $raiz . '/' . ltrim(preg_replace('#^/Asociaciones_PP/#', '', $rutaBDn), '/'),
                $raiz . '/' . ltrim($rutaBDn, '/'),
                $docRoot . preg_replace('#^/Asociaciones_PP#', '', $rutaBDn),
            ];
            $existe = false;
            $rutaEncontrada = '';
            foreach ($candidatos as $c) {
                if (file_exists($c)) { $existe = true; $rutaEncontrada = $c; break; }
            }

            $existeHtml = $existe
                ? '<span class="ok">SÍ → ' . $rutaEncontrada . '</span>'
                : '<span class="err">NO<br>Intenté:<br>' . implode('<br>', $candidatos) . '</span>';

            echo '<tr>';
            echo '<td>' . substr($f['contrato_id'], 0, 8) . '</td>';
            echo '<td>' . htmlspecialchars($f['nombre_firmante']) . '</td>';
            echo '<td>' . htmlspecialchars($f['rol']) . '</td>';
            echo '<td>' . htmlspecialchars($f['categoria']) . '</td>';
            echo '<td>' . htmlspecialchars($rutaBD) . '</td>';
            echo '<td>' . $existeHtml . '</td>';
            echo '<td>' . $f['fecha_firma'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
} catch (Exception $e) {
    echo '<p class="err">Error BD: ' . $e->getMessage() . '</p>';
}

echo '<h2>4. OPCIONES_SISTEMA — rol_firmante</h2>';
$ops = $db->query("SELECT id, categoria, nombre_opcion FROM opciones_sistema WHERE categoria='rol_firmante' ORDER BY id")->fetchAll();
echo '<table><tr><th>ID</th><th>Categoría</th><th>Nombre</th></tr>';
foreach ($ops as $o) {
    echo '<tr><td>' . $o['id'] . '</td><td>' . $o['categoria'] . '</td><td>' . $o['nombre_opcion'] . '</td></tr>';
}
echo '</table>';

echo '<h2>5. ÚLTIMO CONTRATO APROBADO</h2>';
$cont = $db->query("
    SELECT c.id, c.nombre_comprador, os.nombre_opcion AS estado
    FROM contratos c
    JOIN opciones_sistema os ON os.id = c.estado_contrato_id
    WHERE os.nombre_opcion = 'Aprobado'
    ORDER BY c.fecha_generacion DESC LIMIT 1
")->fetch();
if ($cont) {
    echo '<p><b>ID:</b> ' . $cont['id'] . '<br><b>Comprador:</b> ' . $cont['nombre_comprador'] . '</p>';
    echo '<p>Usa este ID para probar: <b>' . $cont['id'] . '</b></p>';
} else {
    echo '<p class="err">No hay contratos en estado Aprobado.</p>';
}