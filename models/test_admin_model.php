<?php
/**
 * TEST COMPLETO DEL MODELO ADMIN - CYBERHOLE SYSTEM
 * 
 * @description Test exhaustivo del modelo Admin.php con todas sus funcionalidades
 * @author Sistema Cyberhole - Fanático Religioso del Testing
 * @version 1.0 - TEST ESPECIALIZADO ADMIN MODEL
 * @date 2025-07-29
 * 
 * 🔥 FUNCIONALIDADES A PROBAR:
 * ✅ Conexión y inicialización del modelo
 * ✅ Validaciones de email y contraseña
 * ✅ Creación de administradores (CREATE)
 * ✅ Búsqueda por ID y email (READ)
 * ✅ Actualización de datos (UPDATE)
 * ✅ Eliminación de administradores (DELETE)
 * ✅ Autenticación y login
 * ✅ Encriptación y desencriptación
 * ✅ Manejo de errores y excepciones
 * ✅ Validación de datos de entrada
 */

// Configuración inicial
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(180); // 3 minutos para el test completo

// Cargar configuración
require_once __DIR__ . '/../config/bootstrap.php';

class AdminModelTest
{
    private array $testResults = [];
    private int $totalTests = 0;
    private int $passedTests = 0;
    private ?Admin $adminModel = null;
    private array $testAdmins = [];
    
    public function __construct()
    {
        echo "🔥 INICIANDO TEST COMPLETO DEL MODELO ADMIN\n";
        echo "=" . str_repeat("=", 50) . "\n";
        echo "📋 Fecha: " . date('Y-m-d H:i:s') . "\n";
        echo "📋 Modelo: Admin.php\n";
        echo "📋 Base de datos: " . $_ENV['DB_DATABASE'] . "\n\n";
        
        $this->initializeTestData();
    }
    
    /**
     * Ejecutar todas las pruebas del modelo Admin
     */
    public function runAllTests(): void
    {
        echo "🚀 EJECUTANDO SUITE COMPLETA DE PRUEBAS DEL MODELO ADMIN\n";
        echo "-" . str_repeat("-", 55) . "\n\n";
        
        // 1. Pruebas de inicialización
        $this->testModelInitialization();
        
        // 2. Pruebas de validación
        $this->testValidationMethods();
        
        // 3. Pruebas de encriptación
        $this->testEncryptionMethods();
        
        // 4. Pruebas CRUD básicas
        $this->testCrudOperations();
        
        // 5. Pruebas de autenticación
        $this->testAuthenticationMethods();
        
        // 6. Pruebas de búsqueda
        $this->testSearchMethods();
        
        // 7. Pruebas de manejo de errores
        $this->testErrorHandling();
        
        // 8. Limpieza de datos de prueba
        $this->cleanupTestData();
        
        // Mostrar resultados finales
        $this->displayFinalResults();
    }
    
    /**
     * Inicializar datos de prueba
     */
    private function initializeTestData(): void
    {
        $this->testAdmins = [
            [
                'nombres' => 'Juan Carlos',
                'apellido1' => 'Pérez',
                'apellido2' => 'González',
                'correo' => 'admin.test1@cyberhole.com',
                'contrasena' => 'TestPassword123!'
            ],
            [
                'nombres' => 'María Elena',
                'apellido1' => 'Rodríguez',
                'apellido2' => 'Martínez',
                'correo' => 'admin.test2@cyberhole.com',
                'contrasena' => 'SecurePass456@'
            ],
            [
                'nombres' => 'Roberto',
                'apellido1' => 'Silva',
                'apellido2' => null,
                'correo' => 'admin.test3@cyberhole.com',
                'contrasena' => 'StrongPass789#'
            ]
        ];
    }
    
    /**
     * Test de inicialización del modelo
     */
    private function testModelInitialization(): void
    {
        echo "🏗️ PRUEBAS DE INICIALIZACIÓN DEL MODELO\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        $this->runTest(
            "Crear instancia del modelo Admin",
            function() {
                try {
                    require_once __DIR__ . '/Admin.php';
                    $this->adminModel = new Admin();
                    return $this->adminModel instanceof Admin;
                } catch (Exception $e) {
                    echo "Error: " . $e->getMessage() . "\n";
                    return false;
                }
            }
        );
        
        $this->runTest(
            "Verificar herencia de BaseModel",
            function() {
                return $this->adminModel instanceof BaseModel;
            }
        );
        
        $this->runTest(
            "Verificar conexión a base de datos",
            function() {
                try {
                    // Usar reflection para acceder a la propiedad protegida
                    $reflection = new ReflectionClass($this->adminModel);
                    $property = $reflection->getProperty('connection');
                    $property->setAccessible(true);
                    $connection = $property->getValue($this->adminModel);
                    
                    return $connection instanceof PDO;
                } catch (Exception $e) {
                    return false;
                }
            }
        );
        
        $this->runTest(
            "Verificar tabla configurada correctamente",
            function() {
                try {
                    $reflection = new ReflectionClass($this->adminModel);
                    $property = $reflection->getProperty('table');
                    $property->setAccessible(true);
                    $table = $property->getValue($this->adminModel);
                    
                    return $table === 'admin';
                } catch (Exception $e) {
                    return false;
                }
            }
        );
        
        echo "\n";
    }
    
    /**
     * Test de métodos de validación
     */
    private function testValidationMethods(): void
    {
        echo "✅ PRUEBAS DE MÉTODOS DE VALIDACIÓN\n";
        echo "-" . str_repeat("-", 35) . "\n";
        
        // Test de validación de email
        $this->runTest(
            "Validar email válido",
            function() {
                return $this->adminModel->validateEmailFormat('admin@example.com');
            }
        );
        
        $this->runTest(
            "Rechazar email inválido",
            function() {
                return !$this->adminModel->validateEmailFormat('invalid-email');
            }
        );
        
        $this->runTest(
            "Rechazar email vacío",
            function() {
                return !$this->adminModel->validateEmailFormat('');
            }
        );
        
        // Test de validación de contraseña
        $this->runTest(
            "Validar contraseña con longitud adecuada",
            function() {
                return $this->adminModel->validatePasswordLength('Password123!');
            }
        );
        
        $this->runTest(
            "Rechazar contraseña muy corta",
            function() {
                return !$this->adminModel->validatePasswordLength('123');
            }
        );
        
        $this->runTest(
            "Rechazar contraseña vacía",
            function() {
                return !$this->adminModel->validatePasswordLength('');
            }
        );
        
        echo "\n";
    }
    
    /**
     * Test de métodos de encriptación
     */
    private function testEncryptionMethods(): void
    {
        echo "🔒 PRUEBAS DE MÉTODOS DE ENCRIPTACIÓN\n";
        echo "-" . str_repeat("-", 35) . "\n";
        
        $this->runTest(
            "Generar hash de contraseña",
            function() {
                $hash = $this->adminModel->hashPassword('TestPassword123');
                return is_string($hash) && strlen($hash) > 20;
            }
        );
        
        $this->runTest(
            "Verificar que hashes diferentes para misma contraseña",
            function() {
                $hash1 = $this->adminModel->hashPassword('TestPassword123');
                $hash2 = $this->adminModel->hashPassword('TestPassword123');
                return $hash1 !== $hash2; // Deben ser diferentes por salt
            }
        );
        
        $this->runTest(
            "Hash de contraseña no contiene texto plano",
            function() {
                $password = 'MySecretPassword';
                $hash = $this->adminModel->hashPassword($password);
                return strpos($hash, $password) === false;
            }
        );
        
        echo "\n";
    }
    
    /**
     * Test de operaciones CRUD
     */
    private function testCrudOperations(): void
    {
        echo "📝 PRUEBAS DE OPERACIONES CRUD\n";
        echo "-" . str_repeat("-", 30) . "\n";
        
        $createdIds = [];
        
        // CREATE - Crear administradores
        foreach ($this->testAdmins as $index => $adminData) {
            $this->runTest(
                "Crear administrador " . ($index + 1),
                function() use ($adminData, &$createdIds) {
                    $id = $this->adminModel->create($adminData);
                    if ($id !== false) {
                        $createdIds[] = $id;
                        return true;
                    }
                    return false;
                }
            );
        }
        
        // READ - Buscar por ID
        if (!empty($createdIds)) {
            $this->runTest(
                "Buscar administrador por ID",
                function() use ($createdIds) {
                    $admin = $this->adminModel->findById($createdIds[0]);
                    return $admin !== null && isset($admin['id_admin']);
                }
            );
            
            $this->runTest(
                "Verificar datos desencriptados correctamente",
                function() use ($createdIds) {
                    $admin = $this->adminModel->findById($createdIds[0]);
                    return $admin !== null && 
                           isset($admin['nombres']) && 
                           $admin['nombres'] === $this->testAdmins[0]['nombres'];
                }
            );
        }
        
        // READ - Buscar por email
        $this->runTest(
            "Buscar administrador por email",
            function() {
                $admin = $this->adminModel->findByEmail($this->testAdmins[0]['correo']);
                return $admin !== null && isset($admin['correo']);
            }
        );
        
        // UPDATE - Actualizar datos
        if (!empty($createdIds)) {
            $this->runTest(
                "Actualizar datos de administrador",
                function() use ($createdIds) {
                    $updateData = [
                        'nombres' => 'Juan Carlos Actualizado',
                        'apellido1' => 'Pérez Actualizado'
                    ];
                    return $this->adminModel->update($createdIds[0], $updateData);
                }
            );
        }
        
        // Almacenar IDs creados para limpieza posterior
        $this->testResults['created_ids'] = $createdIds;
        
        echo "\n";
    }
    
    /**
     * Test de métodos de autenticación
     */
    private function testAuthenticationMethods(): void
    {
        echo "🔐 PRUEBAS DE MÉTODOS DE AUTENTICACIÓN\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        $this->runTest(
            "Login con credenciales válidas",
            function() {
                $result = $this->adminModel->adminLogin(
                    $this->testAdmins[0]['correo'],
                    $this->testAdmins[0]['contrasena']
                );
                return is_array($result) && isset($result['id_admin']);
            }
        );
        
        $this->runTest(
            "Fallar login con email incorrecto",
            function() {
                $result = $this->adminModel->adminLogin(
                    'email.inexistente@test.com',
                    $this->testAdmins[0]['contrasena']
                );
                return $result === false;
            }
        );
        
        $this->runTest(
            "Fallar login con contraseña incorrecta",
            function() {
                $result = $this->adminModel->adminLogin(
                    $this->testAdmins[0]['correo'],
                    'contraseña_incorrecta'
                );
                return $result === false;
            }
        );
        
        $this->runTest(
            "Validar credenciales de administrador",
            function() {
                return $this->adminModel->validateAdminCredentials(
                    $this->testAdmins[0]['correo'],
                    $this->testAdmins[0]['contrasena']
                );
            }
        );
        
        $this->runTest(
            "Obtener rol de administrador",
            function() {
                $role = $this->adminModel->getAdminRole();
                return $role === 'ADMIN';
            }
        );
        
        echo "\n";
    }
    
    /**
     * Test de métodos de búsqueda
     */
    private function testSearchMethods(): void
    {
        echo "🔍 PRUEBAS DE MÉTODOS DE BÚSQUEDA\n";
        echo "-" . str_repeat("-", 35) . "\n";
        
        $this->runTest(
            "Obtener todos los administradores",
            function() {
                $admins = $this->adminModel->getAllAdmins();
                return is_array($admins) && count($admins) >= count($this->testAdmins);
            }
        );
        
        $this->runTest(
            "Buscar administrador inexistente por email",
            function() {
                $admin = $this->adminModel->findByEmail('no.existe@test.com');
                return $admin === null;
            }
        );
        
        $this->runTest(
            "Buscar administrador inexistente por ID",
            function() {
                $admin = $this->adminModel->findById(99999);
                return $admin === null;
            }
        );
        
        echo "\n";
    }
    
    /**
     * Test de manejo de errores
     */
    private function testErrorHandling(): void
    {
        echo "🚨 PRUEBAS DE MANEJO DE ERRORES\n";
        echo "-" . str_repeat("-", 35) . "\n";
        
        $this->runTest(
            "Fallar creación con email duplicado",
            function() {
                $result = $this->adminModel->create($this->testAdmins[0]);
                return $result === false;
            }
        );
        
        $this->runTest(
            "Fallar creación con datos incompletos",
            function() {
                $incompleteData = [
                    'nombres' => 'Test',
                    // Falta apellido1, correo, contrasena
                ];
                $result = $this->adminModel->create($incompleteData);
                return $result === false;
            }
        );
        
        $this->runTest(
            "Fallar creación con email inválido",
            function() {
                $invalidData = [
                    'nombres' => 'Test',
                    'apellido1' => 'User',
                    'correo' => 'email-invalido',
                    'contrasena' => 'ValidPassword123!'
                ];
                $result = $this->adminModel->create($invalidData);
                return $result === false;
            }
        );
        
        $this->runTest(
            "Fallar creación con contraseña muy corta",
            function() {
                $invalidData = [
                    'nombres' => 'Test',
                    'apellido1' => 'User',
                    'correo' => 'test.unique@example.com',
                    'contrasena' => '123'
                ];
                $result = $this->adminModel->create($invalidData);
                return $result === false;
            }
        );
        
        echo "\n";
    }
    
    /**
     * Limpiar datos de prueba
     */
    private function cleanupTestData(): void
    {
        echo "🧹 LIMPIEZA DE DATOS DE PRUEBA\n";
        echo "-" . str_repeat("-", 30) . "\n";
        
        if (isset($this->testResults['created_ids'])) {
            foreach ($this->testResults['created_ids'] as $id) {
                $this->runTest(
                    "Eliminar administrador de prueba ID: $id",
                    function() use ($id) {
                        return $this->adminModel->delete($id);
                    }
                );
            }
        }
        
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
        echo "🏁 RESULTADOS FINALES DEL TEST DEL MODELO ADMIN\n";
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
            'Inicialización' => 'instancia|conexión|tabla|herencia',
            'Validación' => 'Validar|Rechazar',
            'Encriptación' => 'hash|encript',
            'CRUD' => 'Crear|Buscar|Actualizar|Eliminar',
            'Autenticación' => 'Login|credenciales|rol',
            'Búsqueda' => 'todos|inexistente',
            'Errores' => 'Fallar|duplicado|incompletos|inválido'
        ];
        
        echo "📋 RESUMEN POR CATEGORÍAS:\n";
        foreach ($categories as $category => $pattern) {
            $categoryTests = array_filter($this->testResults, function($test) use ($pattern) {
                if (is_array($test) && isset($test['name'])) {
                    return preg_match("/{$pattern}/i", $test['name']);
                }
                return false;
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
            return is_array($test) && $test['status'] !== 'PASS';
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
        $totalTime = array_sum(array_column(array_filter($this->testResults, 'is_array'), 'time'));
        echo "   {$totalTime}ms (" . round($totalTime / 1000, 2) . " segundos)\n";
        
        // Conclusión final
        echo "\n🎯 CONCLUSIÓN DEL TEST DEL MODELO ADMIN:\n";
        if ($successRate >= 95) {
            echo "   🟢 EXCELENTE - Modelo Admin funcionando perfectamente\n";
        } elseif ($successRate >= 85) {
            echo "   🟡 BUENO - Modelo Admin funcional con mejoras menores\n";
        } elseif ($successRate >= 70) {
            echo "   🟠 REGULAR - Modelo Admin necesita atención\n";
        } else {
            echo "   🔴 CRÍTICO - Modelo Admin requiere revisión inmediata\n";
        }
        
        echo "\n✨ Test del modelo Admin completado - " . date('Y-m-d H:i:s') . "\n";
        echo "=" . str_repeat("=", 60) . "\n";
    }
}

// Ejecutar las pruebas del modelo Admin
try {
    $adminTester = new AdminModelTest();
    $adminTester->runAllTests();
} catch (Exception $e) {
    echo "💥 ERROR CRÍTICO EN TEST DEL MODELO ADMIN: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
?>
