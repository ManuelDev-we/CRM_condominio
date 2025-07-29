<?php
/**
 * TEST DE DIAGNÓSTICO DE SERVICIOS ADMIN
 * 
 * Test básico para diagnosticar problemas con servicios
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 DIAGNÓSTICO DE SERVICIOS ADMIN\n";
echo "=" . str_repeat("=", 35) . "\n\n";

// 1. Verificar estructura de directorios
echo "📁 ESTRUCTURA DE DIRECTORIOS:\n";
$servicesPath = __DIR__ . '/../admin_services/admin_services_php';
echo "Ruta servicios: {$servicesPath}\n";
echo "Existe directorio: " . (is_dir($servicesPath) ? "✅ SÍ" : "❌ NO") . "\n\n";

// 2. Listar archivos
echo "📄 ARCHIVOS ENCONTRADOS:\n";
if (is_dir($servicesPath)) {
    $files = scandir($servicesPath);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $fullPath = $servicesPath . '/' . $file;
            $size = filesize($fullPath);
            echo "   📄 {$file} ({$size} bytes)\n";
        }
    }
} else {
    echo "   ❌ Directorio no encontrado\n";
}
echo "\n";

// 3. Test básico de sintaxis en un archivo específico
$testFile = $servicesPath . '/AdminService.php';
echo "🔧 TEST DE SINTAXIS EN AdminService.php:\n";
if (file_exists($testFile)) {
    echo "   📄 Archivo existe: ✅ SÍ\n";
    echo "   📏 Tamaño: " . filesize($testFile) . " bytes\n";
    
    // Verificar sintaxis con información detallada
    $output = [];
    $returnVar = 0;
    exec("c:\\xampp\\php\\php.exe -l \"$testFile\" 2>&1", $output, $returnVar);
    
    echo "   🔍 Verificación sintaxis:\n";
    foreach ($output as $line) {
        echo "      $line\n";
    }
    echo "   📊 Código de retorno: $returnVar\n";
} else {
    echo "   ❌ Archivo no encontrado\n";
}
echo "\n";

// 4. Verificar dependencias básicas
echo "🔗 VERIFICACIÓN DE DEPENDENCIAS:\n";
$baseServicePath = __DIR__ . '/../BaseService.php';
echo "   BaseService.php: " . (file_exists($baseServicePath) ? "✅ Existe" : "❌ No existe") . "\n";

$middlewarePath = __DIR__ . '/../../middlewares/MiddlewareManager.php';
echo "   MiddlewareManager.php: " . (file_exists($middlewarePath) ? "✅ Existe" : "❌ No existe") . "\n";

$configPath = __DIR__ . '/../../config';
echo "   Directorio config: " . (is_dir($configPath) ? "✅ Existe" : "❌ No existe") . "\n";

echo "\n";

// 5. Test de lectura de archivo
echo "🔍 CONTENIDO PARCIAL DE AdminService.php:\n";
if (file_exists($testFile)) {
    $content = file_get_contents($testFile);
    $lines = explode("\n", $content);
    echo "   📊 Total de líneas: " . count($lines) . "\n";
    echo "   📄 Primeras 5 líneas:\n";
    for ($i = 0; $i < min(5, count($lines)); $i++) {
        echo "      " . ($i + 1) . ": " . trim($lines[$i]) . "\n";
    }
}

echo "\n✨ Diagnóstico completado\n";
?>
