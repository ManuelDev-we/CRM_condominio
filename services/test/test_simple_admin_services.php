<?php
/**
 * TEST SIMPLE DE SERVICIOS ADMIN - CYBERHOLE SYSTEM
 * 
 * @description Test directo de servicios admin sin bootstrap completo
 * @author Sistema Cyberhole - Testing Simplificado
 * @version 1.0 - TEST DIRECTO SERVICIOS
 * @date 2025-07-29
 */

// Configuración inicial básica
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(180); // 3 minutos

// Cargar solo lo esencial
require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/SecurityConfig.php';

class SimpleAdminServicesTest
{
    private array $testResults = [];
    private int $totalTests = 0;
    private int $passedTests = 0;
    private string $servicesPath;
    
    public function __construct()
    {
        echo "🔥 TEST SIMPLE DE SERVICIOS ADMIN\n";
        echo "=" . str_repeat("=", 40) . "\n";
        echo "📋 Fecha: " . date('Y-m-d H:i:s') . "\n";
        echo "📋 Base de datos: " . ($_ENV['DB_DATABASE'] ?? 'N/A') . "\n\n";
        
        $this->servicesPath = __DIR__ . '/../admin_services/admin_services_php';
    }
    
    public function runAllTests(): void
    {
        echo "🚀 EJECUTANDO TESTS SIMPLES DE SERVICIOS\n";
        echo "-" . str_repeat("-", 40) . "\n\n";
        
        // 1. Verificar estructura de archivos
        $this->testFileStructure();
        
        // 2. Verificar sintaxis PHP de cada servicio
        $this->testServicesSyntax();
        
        // 3. Verificar clases y métodos básicos
        $this->testServicesClasses();
        
        // 4. Test básico de instanciación
        $this->testServicesInstantiation();
        
        // Mostrar resultados
        $this->displayResults();
    }
    
    private function testFileStructure(): void
    {
        echo "📋 VERIFICACIÓN DE ESTRUCTURA DE ARCHIVOS\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        $expectedServices = [
            'AdminService.php',
            'AreaComunService.php', 
            'BaseAdminService.php',
            'BlogService.php',
            'CalleService.php',
            'CasaService.php',
            'CondominioService.php',
            'DispositivoService.php',
            'EmpleadoService.php',
            'EngomadoService.php',
            'PersonaCasaService.php',
            'TagService.php'
        ];
        
        foreach ($expectedServices as $service) {
            $this->runTest(
                "Archivo {$service} existe",
                function() use ($service) {
                    return file_exists($this->servicesPath . '/' . $service);
                }
            );
        }
        
        echo "\n";
    }
    
    private function testServicesSyntax(): void
    {
        echo "✅ VERIFICACIÓN DE SINTAXIS PHP\n";
        echo "-" . str_repeat("-", 30) . "\n";
        
        $serviceFiles = glob($this->servicesPath . '/*.php');
        
        foreach ($serviceFiles as $serviceFile) {
            $serviceName = basename($serviceFile);
            
            $this->runTest(
                "Sintaxis PHP válida en {$serviceName}",
                function() use ($serviceFile) {
                    $output = shell_exec("php -l \"{$serviceFile}\" 2>&1");
                    return strpos($output, 'No syntax errors') !== false;
                }
            );
        }
        
        echo "\n";
    }
    
    private function testServicesClasses(): void
    {
        echo "🔍 VERIFICACIÓN DE CLASES Y MÉTODOS\n";
        echo "-" . str_repeat("-", 35) . "\n";
        
        // Primero incluir BaseService si existe
        $baseServicePath = __DIR__ . '/../BaseService.php';
        if (file_exists($baseServicePath)) {
            require_once $baseServicePath;
        }
        
        $serviceFiles = glob($this->servicesPath . '/*.php');
        
        foreach ($serviceFiles as $serviceFile) {
            $serviceName = basename($serviceFile, '.php');
            
            $this->runTest(
                "Clase {$serviceName} se puede cargar",
                function() use ($serviceFile, $serviceName) {
                    try {
                        require_once $serviceFile;
                        return class_exists($serviceName);
                    } catch (Exception $e) {
                        echo "Error: " . $e->getMessage() . " ";
                        return false;
                    }
                }
            );
        }
        
        echo "\n";
    }
    
    private function testServicesInstantiation(): void
    {
        echo "🏗️ VERIFICACIÓN DE INSTANCIACIÓN\n";
        echo "-" . str_repeat("-", 35) . "\n";
        
        $serviceClasses = [
            'AdminService',
            'AreaComunService', 
            'BaseAdminService',
            'BlogService',
            'CalleService',
            'CasaService',
            'CondominioService',
            'DispositivoService',
            'EmpleadoService',
            'EngomadoService',
            'PersonaCasaService',
            'TagService'
        ];
        
        foreach ($serviceClasses as $className) {
            $this->runTest(
                "Instanciar {$className}",
                function() use ($className) {
                    try {
                        if (!class_exists($className)) {
                            return false;
                        }
                        
                        $instance = new $className();
                        return is_object($instance);
                    } catch (Exception $e) {
                        echo "Error: " . $e->getMessage() . " ";
                        return false;
                    }
                }
            );
        }
        
        echo "\n";
    }
    
    private function runTest(string $testName, callable $testFunction): void
    {
        $this->totalTests++;
        
        try {
            $startTime = microtime(true);
            $result = $testFunction();
            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2);
            
            if ($result) {
                $this->passedTests++;
                echo "✅ {$testName} ({$executionTime}ms)\n";
                $this->testResults[] = [
                    'name' => $testName,
                    'status' => 'PASS',
                    'time' => $executionTime
                ];
            } else {
                echo "❌ {$testName} - FALLÓ ({$executionTime}ms)\n";
                $this->testResults[] = [
                    'name' => $testName,
                    'status' => 'FAIL',
                    'time' => $executionTime
                ];
            }
        } catch (Exception $e) {
            echo "💥 {$testName} - ERROR: {$e->getMessage()}\n";
            $this->testResults[] = [
                'name' => $testName,
                'status' => 'ERROR',
                'error' => $e->getMessage(),
                'time' => 0
            ];
        }
    }
    
    private function displayResults(): void
    {
        echo "\n" . "=" . str_repeat("=", 50) . "\n";
        echo "🏁 RESULTADOS DEL TEST SIMPLE DE SERVICIOS\n";
        echo "=" . str_repeat("=", 50) . "\n\n";
        
        $failedTests = $this->totalTests - $this->passedTests;
        $successRate = round(($this->passedTests / $this->totalTests) * 100, 2);
        
        echo "📊 ESTADÍSTICAS:\n";
        echo "   Total de pruebas: {$this->totalTests}\n";
        echo "   Exitosas: {$this->passedTests}\n";
        echo "   Fallidas: {$failedTests}\n";
        echo "   Tasa de éxito: {$successRate}%\n\n";
        
        // Mostrar pruebas fallidas
        $failedTestsList = array_filter($this->testResults, function($test) {
            return $test['status'] !== 'PASS';
        });
        
        if (!empty($failedTestsList)) {
            echo "❌ PRUEBAS FALLIDAS:\n";
            foreach ($failedTestsList as $test) {
                echo "   • {$test['name']} - {$test['status']}";
                if (isset($test['error'])) {
                    echo " ({$test['error']})";
                }
                echo "\n";
            }
            echo "\n";
        }
        
        // Conclusión
        if ($successRate >= 90) {
            echo "🎯 CONCLUSIÓN: ✅ Servicios listos para pruebas funcionales\n";
        } elseif ($successRate >= 70) {
            echo "🎯 CONCLUSIÓN: ⚠️ Servicios necesitan correcciones menores\n";
        } else {
            echo "🎯 CONCLUSIÓN: ❌ Servicios requieren revisión mayor\n";
        }
        
        echo "\n✨ Test completado - " . date('Y-m-d H:i:s') . "\n";
        echo "=" . str_repeat("=", 50) . "\n";
    }
}

// Ejecutar test simple
try {
    $tester = new SimpleAdminServicesTest();
    $tester->runAllTests();
} catch (Exception $e) {
    echo "💥 ERROR CRÍTICO: " . $e->getMessage() . "\n";
    exit(1);
}
?>
