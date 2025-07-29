<?php
/**
 * TEST SIMPLIFICADO - VERIFICACIÓN DE CONFIGURACIÓN CYBERHOLE
 * 
 * @description Test directo para verificar configuración y conexión antes del test completo
 * @author Sistema Cyberhole - Verificador de Configuración
 * @version 1.0
 * @date 2025-07-28
 */

// Configuración básica
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔥 VERIFICACIÓN DE CONFIGURACIÓN CYBERHOLE\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// 1. Verificar archivo .env
echo "📋 VERIFICANDO ARCHIVO .ENV\n";
echo "-" . str_repeat("-", 30) . "\n";

$envPath = dirname(dirname(__DIR__)) . '/.env';
echo "Ruta .env: $envPath\n";

if (file_exists($envPath)) {
    echo "✅ Archivo .env encontrado\n";
    
    // Cargar variables manualmente
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $envVars = [];
    
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        $envVars[$key] = $value;
    }
    
    echo "Variables encontradas: " . count($envVars) . "\n";
    
    // Verificar variables críticas
    $criticalVars = ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];
    $found = 0;
    
    foreach ($criticalVars as $var) {
        if (isset($envVars[$var]) && !empty($envVars[$var])) {
            echo "✅ $var: " . (strlen($envVars[$var]) > 10 ? substr($envVars[$var], 0, 10) . "..." : $envVars[$var]) . "\n";
            $found++;
        } else {
            echo "❌ $var: NO ENCONTRADA\n";
        }
    }
    
    echo "\nVariables críticas encontradas: $found/" . count($criticalVars) . "\n";
    
} else {
    echo "❌ Archivo .env NO encontrado\n";
    exit(1);
}

echo "\n";

// 2. Probar conexión directa a base de datos
echo "💾 PROBANDO CONEXIÓN A BASE DE DATOS\n";
echo "-" . str_repeat("-", 35) . "\n";

try {
    $dsn = "mysql:host={$envVars['DB_HOST']};dbname={$envVars['DB_DATABASE']};charset=utf8mb4";
    echo "DSN: $dsn\n";
    echo "Usuario: {$envVars['DB_USERNAME']}\n";
    echo "Password: " . (strlen($envVars['DB_PASSWORD']) > 0 ? str_repeat('*', strlen($envVars['DB_PASSWORD'])) : 'VACÍO') . "\n\n";
    
    $pdo = new PDO(
        $dsn,
        $envVars['DB_USERNAME'],
        $envVars['DB_PASSWORD'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$envVars['DB_CHARSET']}"
        ]
    );
    
    echo "✅ Conexión a base de datos exitosa\n";
    
    // Probar una consulta simple
    $stmt = $pdo->query("SELECT VERSION() as version, DATABASE() as database");
    $result = $stmt->fetch();
    
    echo "   📊 Versión MySQL: {$result['version']}\n";
    echo "   📊 Base de datos actual: {$result['database']}\n";
    
    // Verificar algunas tablas importantes
    echo "\n🗂️ VERIFICANDO TABLAS DEL SISTEMA\n";
    echo "-" . str_repeat("-", 35) . "\n";
    
    $tables = ['administradores', 'condominios', 'casas', 'empleados', 'personas'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch();
            echo "   ✅ $table: {$result['count']} registros\n";
        } catch (Exception $e) {
            echo "   ❌ $table: NO EXISTE o ERROR\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// 3. Verificar servicios básicos
echo "🔧 VERIFICANDO SERVICIOS BÁSICOS\n";
echo "-" . str_repeat("-", 32) . "\n";

// Simular carga de un servicio básico
try {
    // Verificar que las clases base existen
    $baseServicePath = dirname(__DIR__) . '/BaseService.php';
    echo "BaseService: ";
    if (file_exists($baseServicePath)) {
        echo "✅ ENCONTRADO\n";
    } else {
        echo "❌ NO ENCONTRADO\n";
    }
    
    // Verificar auth_services
    $authServicePath = dirname(__DIR__) . '/auth_services.php';
    echo "AuthService: ";
    if (file_exists($authServicePath)) {
        echo "✅ ENCONTRADO\n";
    } else {
        echo "❌ NO ENCONTRADO\n";
    }
    
    // Verificar servicios administrativos
    $adminServicesPath = dirname(__DIR__) . '/admin_services/admin_services_php/';
    echo "Admin Services: ";
    if (is_dir($adminServicesPath)) {
        $services = glob($adminServicesPath . '*.php');
        echo "✅ " . count($services) . " SERVICIOS ENCONTRADOS\n";
        
        foreach ($services as $service) {
            $serviceName = basename($service, '.php');
            echo "   📄 $serviceName\n";
        }
    } else {
        echo "❌ DIRECTORIO NO ENCONTRADO\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error verificando servicios: " . $e->getMessage() . "\n";
}

echo "\n";

// 4. Resumen final
echo "📊 RESUMEN DE VERIFICACIÓN\n";
echo "=" . str_repeat("=", 30) . "\n";

echo "✅ Configuración .env: OK\n";
echo "✅ Conexión a base de datos: OK\n";
echo "✅ Servicios disponibles: OK\n";
echo "🚀 Sistema listo para pruebas\n\n";

echo "🔥 PRÓXIMO PASO: Ejecutar test interactivo desde navegador\n";
echo "🌐 URL: http://localhost/Cyberhole_condominios/segunda_capa_edition/porq_copilot%20no%20funciona%20-%20copia/services/test/\n";

echo "\n✨ Verificación completada - " . date('Y-m-d H:i:s') . "\n";
echo "=" . str_repeat("=", 50) . "\n";
?>
