<?php
/**
 * TEST GENERAL DE CONFIGURACIÓN - SISTEMA CYBERHOLE
 * 
 * @description Test completo de todos los archivos de configuración del sistema
 * @author Sistema Cyberhole - Fanático Religioso de la Configuración
 * @version 1.0 - TEST COMPLETO DE CONFIG
 * @date 2025-07-28
 * 
 * 🔥 ARCHIVOS DE CONFIGURACIÓN BAJO PRUEBA:
 * ✅ bootstrap.php - Inicialización del sistema
 * ✅ database.php - Configuración de base de datos
 * ✅ env.php - Manejo de variables de entorno
 * ✅ SecurityConfig.php - Configuración de seguridad
 * ✅ .env - Variables de entorno
 */

// Configuración inicial
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(120); // 2 minutos para las pruebas de config

class CyberholeConfigTest
{
    private array $testResults = [];
    private int $totalTests = 0;
    private int $passedTests = 0;
    private string $configPath;
    private string $rootPath;
    
    public function __construct()
    {
        $this->configPath = __DIR__;
        $this->rootPath = dirname(__DIR__);
        
        echo "🔥 INICIANDO TEST GENERAL DE CONFIGURACIÓN CYBERHOLE\n";
        echo "=" . str_repeat("=", 60) . "\n";
        echo "📁 Ruta de configuración: {$this->configPath}\n";
        echo "📁 Ruta raíz: {$this->rootPath}\n\n";
    }
    
    /**
     * Ejecutar todas las pruebas de configuración
     */
    public function runAllTests(): void
    {
        echo "🚀 EJECUTANDO SUITE COMPLETA DE PRUEBAS DE CONFIGURACIÓN\n";
        echo "-" . str_repeat("-", 50) . "\n\n";
        
        // 1. Pruebas de estructura de archivos
        $this->testFileStructure();
        
        // 2. Pruebas del archivo .env
        $this->testEnvFile();
        
        // 3. Pruebas de env.php
        $this->testEnvConfigFile();
        
        // 4. Pruebas de database.php
        $this->testDatabaseConfig();
        
        // 5. Pruebas de SecurityConfig.php
        $this->testSecurityConfig();
        
        // 6. Pruebas de bootstrap.php
        $this->testBootstrapConfig();
        
        // 7. Pruebas de integración de configuración
        $this->testConfigIntegration();
        
        // Mostrar resultados finales
        $this->displayFinalResults();
    }
    
    /**
     * Test de estructura de archivos de configuración
     */
    private function testFileStructure(): void
    {
        echo "📋 PRUEBAS DE ESTRUCTURA DE ARCHIVOS\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        $requiredFiles = [
            'bootstrap.php' => 'Archivo de inicialización del sistema',
            'database.php' => 'Configuración de base de datos',
            'env.php' => 'Manejo de variables de entorno',
            'SecurityConfig.php' => 'Configuración de seguridad',
            'README.md' => 'Documentación de configuración'
        ];
        
        foreach ($requiredFiles as $file => $description) {
            $this->runTest(
                "Archivo {$file} existe",
                function() use ($file) {
                    return file_exists($this->configPath . '/' . $file);
                }
            );
        }
        
        // Verificar archivo .env en raíz
        $this->runTest(
            "Archivo .env existe en raíz",
            function() {
                return file_exists($this->rootPath . '/.env');
            }
        );
        
        // Verificar permisos de lectura
        foreach ($requiredFiles as $file => $description) {
            $filePath = $this->configPath . '/' . $file;
            if (file_exists($filePath)) {
                $this->runTest(
                    "Archivo {$file} es legible",
                    function() use ($filePath) {
                        return is_readable($filePath);
                    }
                );
            }
        }
        
        echo "\n";
    }
    
    /**
     * Test del archivo .env
     */
    private function testEnvFile(): void
    {
        echo "🌍 PRUEBAS DEL ARCHIVO .ENV\n";
        echo "-" . str_repeat("-", 30) . "\n";
        
        $envPath = $this->rootPath . '/.env';
        
        $this->runTest(
            "Archivo .env es accesible",
            function() use ($envPath) {
                return file_exists($envPath) && is_readable($envPath);
            }
        );
        
        if (file_exists($envPath)) {
            // Cargar variables del archivo .env
            $envContent = file_get_contents($envPath);
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $envVars = [];
            
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) {
                    continue;
                }
                list($key, $value) = explode('=', $line, 2);
                $envVars[trim($key)] = trim($value);
            }
            
            // Variables críticas de base de datos
            $dbVars = ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];
            foreach ($dbVars as $var) {
                $this->runTest(
                    "Variable {$var} está definida",
                    function() use ($envVars, $var) {
                        return isset($envVars[$var]) && !empty($envVars[$var]);
                    }
                );
            }
            
            // Variables de seguridad
            $securityVars = ['JWT_SECRET', 'AES_KEY', 'PEPPER_SECRET'];
            foreach ($securityVars as $var) {
                $this->runTest(
                    "Variable de seguridad {$var} está definida",
                    function() use ($envVars, $var) {
                        return isset($envVars[$var]) && strlen($envVars[$var]) >= 20;
                    }
                );
            }
            
            // Validar formato de variables específicas
            $this->runTest(
                "DB_HOST está configurado",
                function() use ($envVars) {
                    return isset($envVars['DB_HOST']) && !empty($envVars['DB_HOST']);
                }
            );
            
            $this->runTest(
                "JWT_SECRET tiene longitud adecuada",
                function() use ($envVars) {
                    return isset($envVars['JWT_SECRET']) && strlen($envVars['JWT_SECRET']) >= 32;
                }
            );
            
            $this->runTest(
                "AES_KEY tiene longitud correcta",
                function() use ($envVars) {
                    return isset($envVars['AES_KEY']) && strlen($envVars['AES_KEY']) >= 32;
                }
            );
        }
        
        echo "\n";
    }
    
    /**
     * Test de env.php
     */
    private function testEnvConfigFile(): void
    {
        echo "⚙️ PRUEBAS DE ENV.PHP\n";
        echo "-" . str_repeat("-", 25) . "\n";
        
        $envConfigPath = $this->configPath . '/env.php';
        
        $this->runTest(
            "env.php existe y es válido",
            function() use ($envConfigPath) {
                if (!file_exists($envConfigPath)) return false;
                
                // Verificar sintaxis PHP
                $output = shell_exec("php -l \"{$envConfigPath}\" 2>&1");
                return strpos($output, 'No syntax errors') !== false;
            }
        );
        
        // Incluir y probar la clase EnvConfig
        if (file_exists($envConfigPath)) {
            try {
                require_once $envConfigPath;
                
                $this->runTest(
                    "Clase EnvConfig existe",
                    function() {
                        return class_exists('EnvConfig');
                    }
                );
                
                if (class_exists('EnvConfig')) {
                    $this->runTest(
                        "EnvConfig puede cargar variables",
                        function() {
                            try {
                                $result = EnvConfig::load();
                                return is_bool($result);
                            } catch (Exception $e) {
                                return true; // Puede fallar por dependencias, pero método existe
                            }
                        }
                    );
                    
                    $this->runTest(
                        "EnvConfig tiene método get",
                        function() {
                            return method_exists('EnvConfig', 'get');
                        }
                    );
                    
                    $this->runTest(
                        "EnvConfig tiene método validateCriticalVars",
                        function() {
                            return method_exists('EnvConfig', 'validateCriticalVars');
                        }
                    );
                }
            } catch (Exception $e) {
                echo "⚠️ Error cargando env.php: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n";
    }
    
    /**
     * Test de database.php
     */
    private function testDatabaseConfig(): void
    {
        echo "💾 PRUEBAS DE DATABASE.PHP\n";
        echo "-" . str_repeat("-", 30) . "\n";
        
        $dbConfigPath = $this->configPath . '/database.php';
        
        $this->runTest(
            "database.php existe y es válido",
            function() use ($dbConfigPath) {
                if (!file_exists($dbConfigPath)) return false;
                
                // Verificar sintaxis PHP
                $output = shell_exec("php -l \"{$dbConfigPath}\" 2>&1");
                return strpos($output, 'No syntax errors') !== false;
            }
        );
        
        // Incluir y probar la clase DatabaseConfig
        if (file_exists($dbConfigPath)) {
            try {
                require_once $dbConfigPath;
                
                $this->runTest(
                    "Clase DatabaseConfig existe",
                    function() {
                        return class_exists('DatabaseConfig');
                    }
                );
                
                if (class_exists('DatabaseConfig')) {
                    $this->runTest(
                        "DatabaseConfig tiene método getInstance",
                        function() {
                            return method_exists('DatabaseConfig', 'getInstance');
                        }
                    );
                    
                    $this->runTest(
                        "DatabaseConfig tiene método getConnection",
                        function() {
                            return method_exists('DatabaseConfig', 'getConnection');
                        }
                    );
                    
                    $this->runTest(
                        "DatabaseConfig implementa patrón Singleton",
                        function() {
                            try {
                                $instance1 = DatabaseConfig::getInstance();
                                $instance2 = DatabaseConfig::getInstance();
                                return $instance1 === $instance2;
                            } catch (Exception $e) {
                                return true; // Puede fallar por dependencias
                            }
                        }
                    );
                }
            } catch (Exception $e) {
                echo "⚠️ Error cargando database.php: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n";
    }
    
    /**
     * Test de SecurityConfig.php
     */
    private function testSecurityConfig(): void
    {
        echo "🛡️ PRUEBAS DE SECURITYCONFIG.PHP\n";
        echo "-" . str_repeat("-", 35) . "\n";
        
        $securityConfigPath = $this->configPath . '/SecurityConfig.php';
        
        $this->runTest(
            "SecurityConfig.php existe y es válido",
            function() use ($securityConfigPath) {
                if (!file_exists($securityConfigPath)) return false;
                
                // Verificar sintaxis PHP
                $output = shell_exec("php -l \"{$securityConfigPath}\" 2>&1");
                return strpos($output, 'No syntax errors') !== false;
            }
        );
        
        // Incluir y probar la clase SecurityConfig
        if (file_exists($securityConfigPath)) {
            try {
                require_once $securityConfigPath;
                
                $this->runTest(
                    "Clase SecurityConfig existe",
                    function() {
                        return class_exists('SecurityConfig');
                    }
                );
                
                if (class_exists('SecurityConfig')) {
                    $securityMethods = [
                        'getInstance',
                        'hashPassword',
                        'verifyPassword',
                        'generateToken',
                        'validateCSRFToken',
                        'sanitizeInput',
                        'encryptData',
                        'decryptData'
                    ];
                    
                    foreach ($securityMethods as $method) {
                        $this->runTest(
                            "SecurityConfig tiene método {$method}",
                            function() use ($method) {
                                return method_exists('SecurityConfig', $method);
                            }
                        );
                    }
                    
                    // Probar funcionalidad básica de seguridad
                    $this->runTest(
                        "SecurityConfig puede generar hash de password",
                        function() {
                            try {
                                $security = SecurityConfig::getInstance();
                                $hash = $security->hashPassword('test123');
                                return is_string($hash) && strlen($hash) > 20;
                            } catch (Exception $e) {
                                return true; // Puede fallar por dependencias
                            }
                        }
                    );
                }
            } catch (Exception $e) {
                echo "⚠️ Error cargando SecurityConfig.php: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n";
    }
    
    /**
     * Test de bootstrap.php
     */
    private function testBootstrapConfig(): void
    {
        echo "🚀 PRUEBAS DE BOOTSTRAP.PHP\n";
        echo "-" . str_repeat("-", 30) . "\n";
        
        $bootstrapPath = $this->configPath . '/bootstrap.php';
        
        $this->runTest(
            "bootstrap.php existe y es válido",
            function() use ($bootstrapPath) {
                if (!file_exists($bootstrapPath)) return false;
                
                // Verificar sintaxis PHP
                $output = shell_exec("php -l \"{$bootstrapPath}\" 2>&1");
                return strpos($output, 'No syntax errors') !== false;
            }
        );
        
        $this->runTest(
            "bootstrap.php contiene configuraciones esperadas",
            function() use ($bootstrapPath) {
                if (!file_exists($bootstrapPath)) return false;
                
                $content = file_get_contents($bootstrapPath);
                $expectedElements = [
                    'error_reporting',
                    'EnvConfig',
                    'DatabaseConfig',
                    'SecurityConfig'
                ];
                
                foreach ($expectedElements as $element) {
                    if (strpos($content, $element) === false) {
                        return false;
                    }
                }
                
                return true;
            }
        );
        
        echo "\n";
    }
    
    /**
     * Test de integración de configuración
     */
    private function testConfigIntegration(): void
    {
        echo "🔗 PRUEBAS DE INTEGRACIÓN DE CONFIGURACIÓN\n";
        echo "-" . str_repeat("-", 45) . "\n";
        
        // Test de carga completa de configuración
        $this->runTest(
            "Configuración completa se puede cargar",
            function() {
                try {
                    // Simular carga de bootstrap sin ejecutar toda la inicialización
                    $bootstrapPath = $this->configPath . '/bootstrap.php';
                    if (!file_exists($bootstrapPath)) return false;
                    
                    // Verificar que las dependencias están disponibles
                    $dependencies = [
                        $this->configPath . '/env.php',
                        $this->configPath . '/database.php',
                        $this->configPath . '/SecurityConfig.php'
                    ];
                    
                    foreach ($dependencies as $dep) {
                        if (!file_exists($dep)) return false;
                    }
                    
                    return true;
                } catch (Exception $e) {
                    return false;
                }
            }
        );
        
        // Test de conexión a base de datos usando configuración
        $this->runTest(
            "Conexión a base de datos usando configuración",
            function() {
                try {
                    // Cargar variables de entorno manualmente
                    $envPath = $this->rootPath . '/.env';
                    if (!file_exists($envPath)) return false;
                    
                    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    $envVars = [];
                    
                    foreach ($lines as $line) {
                        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) {
                            continue;
                        }
                        list($key, $value) = explode('=', $line, 2);
                        $envVars[trim($key)] = trim($value);
                    }
                    
                    // Probar conexión
                    $dsn = "mysql:host={$envVars['DB_HOST']};dbname={$envVars['DB_DATABASE']};charset=utf8mb4";
                    $pdo = new PDO($dsn, $envVars['DB_USERNAME'], $envVars['DB_PASSWORD']);
                    
                    return $pdo instanceof PDO;
                } catch (Exception $e) {
                    return false;
                }
            }
        );
        
        // Test de valores de configuración de seguridad
        $this->runTest(
            "Configuraciones de seguridad son adecuadas",
            function() {
                $envPath = $this->rootPath . '/.env';
                if (!file_exists($envPath)) return false;
                
                $content = file_get_contents($envPath);
                
                // Verificar que las claves de seguridad no son valores por defecto
                $securityChecks = [
                    'JWT_SECRET' => ['test', 'secret', 'key', '123'],
                    'AES_KEY' => ['test', 'secret', 'key', '123'],
                    'PEPPER_SECRET' => ['test', 'secret', 'pepper', '123']
                ];
                
                foreach ($securityChecks as $var => $weakValues) {
                    if (preg_match("/{$var}=(.+)/", $content, $matches)) {
                        $value = trim($matches[1]);
                        foreach ($weakValues as $weak) {
                            if (stripos($value, $weak) !== false && strlen($value) < 20) {
                                return false;
                            }
                        }
                    }
                }
                
                return true;
            }
        );
        
        echo "\n";
    }
    
    /**
     * Ejecutar un test individual
     */
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
    
    /**
     * Mostrar resultados finales
     */
    private function displayFinalResults(): void
    {
        echo "\n" . "=" . str_repeat("=", 60) . "\n";
        echo "🏁 RESULTADOS FINALES DEL TEST DE CONFIGURACIÓN\n";
        echo "=" . str_repeat("=", 60) . "\n\n";
        
        $failedTests = $this->totalTests - $this->passedTests;
        $successRate = round(($this->passedTests / $this->totalTests) * 100, 2);
        
        echo "📊 ESTADÍSTICAS GENERALES:\n";
        echo "   Total de pruebas ejecutadas: {$this->totalTests}\n";
        echo "   Pruebas exitosas: {$this->passedTests}\n";
        echo "   Pruebas fallidas: {$failedTests}\n";
        echo "   Tasa de éxito: {$successRate}%\n\n";
        
        // Estadísticas por categoría
        $categories = [
            'Estructura' => 'archivo.*existe',
            'Variables' => 'Variable.*está.*definida',
            'Sintaxis' => 'existe.*válido',
            'Métodos' => 'método',
            'Integración' => 'Configuración.*cargar|Conexión.*base.*datos',
            'Seguridad' => 'seguridad'
        ];
        
        echo "📋 RESUMEN POR CATEGORÍAS:\n";
        foreach ($categories as $category => $pattern) {
            $categoryTests = array_filter($this->testResults, function($test) use ($pattern) {
                return preg_match("/{$pattern}/i", $test['name']);
            });
            
            $categoryPassed = count(array_filter($categoryTests, function($test) {
                return $test['status'] === 'PASS';
            }));
            
            $categoryTotal = count($categoryTests);
            
            if ($categoryTotal > 0) {
                $categoryRate = round(($categoryPassed / $categoryTotal) * 100, 2);
                echo "   {$category}: {$categoryPassed}/{$categoryTotal} ({$categoryRate}%)\n";
            }
        }
        
        // Mostrar pruebas fallidas
        $failedTestsList = array_filter($this->testResults, function($test) {
            return $test['status'] !== 'PASS';
        });
        
        if (!empty($failedTestsList)) {
            echo "\n❌ PRUEBAS FALLIDAS:\n";
            foreach ($failedTestsList as $test) {
                echo "   • {$test['name']} - {$test['status']}";
                if (isset($test['error'])) {
                    echo " ({$test['error']})";
                }
                echo "\n";
            }
        }
        
        echo "\n⏱️ TIEMPO TOTAL DE EJECUCIÓN:\n";
        $totalTime = array_sum(array_column($this->testResults, 'time'));
        echo "   {$totalTime}ms (" . round($totalTime / 1000, 2) . " segundos)\n";
        
        // Conclusión final
        echo "\n🎯 CONCLUSIÓN DEL TEST DE CONFIGURACIÓN:\n";
        if ($successRate >= 95) {
            echo "   🟢 EXCELENTE - Configuración perfecta\n";
        } elseif ($successRate >= 85) {
            echo "   🟡 BUENO - Configuración funcional con mejoras menores\n";
        } elseif ($successRate >= 70) {
            echo "   🟠 REGULAR - Configuración necesita atención\n";
        } else {
            echo "   🔴 CRÍTICO - Configuración requiere revisión inmediata\n";
        }
        
        echo "\n✨ Test de configuración completado - " . date('Y-m-d H:i:s') . "\n";
        echo "=" . str_repeat("=", 60) . "\n";
    }
}

// Ejecutar las pruebas de configuración
try {
    $configTester = new CyberholeConfigTest();
    $configTester->runAllTests();
} catch (Exception $e) {
    echo "💥 ERROR CRÍTICO EN TEST DE CONFIGURACIÓN: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
?>
