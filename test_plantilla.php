<?php
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

$plantilla_path = __DIR__ . '/plantillas/casa_venta.docx';

echo "<h2>Diagnóstico de Plantilla</h2>";
echo "Ruta: " . $plantilla_path . "<br>";

if (file_exists($plantilla_path)) {
    echo "✅ La plantilla existe<br>";
    
    try {
        $template = new TemplateProcessor($plantilla_path);
        echo "✅ Se pudo cargar la plantilla<br>";
        
        // Probar guardar
        $test_path = __DIR__ . '/contratos_generados/test.docx';
        $template->saveAs($test_path);
        echo "✅ Se pudo guardar el documento en: " . $test_path . "<br>";
        
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage();
    }
} else {
    echo "❌ La plantilla NO existe<br>";
    echo "Las plantillas disponibles en la carpeta son:<br>";
    $files = glob(__DIR__ . '/plantillas/*.docx');
    foreach ($files as $f) {
        echo "- " . basename($f) . "<br>";
    }
}
?>