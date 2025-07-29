<?php
/**
 * TEST FUNCIONAL DE SERVICIOS ADMIN - CYBERHOLE SYSTEM
 * 
 * @description Test de lógica de negocio de servicios admin
 * @author Sistema Cyberhole - Testing Funcional
 * @version 1.0 - TEST LÓGICA DE NEGOCIO
 * @date 2025-07-29
 */

// Evitar problemas de headers
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(180);

class AdminServicesFunctionalTest
{
    private array $testResults = [];
    private int $totalTests = 0;
    private int $passedTests = 0;
    private array $createdRecords = [];
    
    public function __construct()
    {
        echo "🔥 TEST FUNCIONAL DE SERVICIOS ADMIN\n";
        echo "=" . str_repeat("=", 45) . "\n";
        echo "📋 Fecha: " . date('Y-m-d H:i:s') . "\n";
        echo "📋 Enfoque: Lógica de negocio\n\n";
    }
    
    public function runAllTests(): void
    {
        echo "🚀 EJECUTANDO TESTS FUNCIONALES DE SERVICIOS\n";
        echo "-" . str_repeat("-", 45) . "\n\n";
        
        // 1. Test de carga de servicios básicos
        $this->testBasicServiceLoading();
        
        // 2. Test específico de AdminService
        $this->testAdminServiceFunctionality();
        
        // 3. Test de flujo de administración
        $this->testAdminWorkflow();
        
        // Mostrar resultados
        $this->displayResults();
    }
    
    private function testBasicServiceLoading(): void
    {
        echo "📋 TEST DE CARGA BÁSICA DE SERVICIOS\n";
        echo "-" . str_repeat("-", 35) . "\n";
        
        // Test 1: Incluir BaseService
        $this->runTest(
            "Cargar BaseService",
            function() {
                try {
                    require_once __DIR__ . '/../BaseService.php';
                    return class_exists('BaseService');
                } catch (Exception $e) {
                    return false;
                }
            }
        );
        
        // Test 2: Incluir AdminService específicamente
        $this->runTest(
            "Cargar AdminService",
            function() {
                try {
                    require_once __DIR__ . '/../admin_services/admin_services_php/AdminService.php';
                    return class_exists('AdminService');
                } catch (Exception $e) {
                    echo "Error: " . $e->getMessage() . " ";
                    return false;
                }
            }
        );
        
        echo "\n";
    }
    
    private function testAdminServiceFunctionality(): void
    {
        echo "👤 TEST DE FUNCIONALIDAD ADMINSERVICE\n";
        echo "-" . str_repeat("-", 35) . "\n";
        
        if (!class_exists('AdminService')) {
            echo "⚠️ AdminService no disponible - saltando tests\n\n";
            return;
        }
        
        // Test 1: Instanciar AdminService
        $adminService = null;
        $this->runTest(
            "Instanciar AdminService",
            function() use (&$adminService) {
                try {
                    $adminService = new AdminService();
                    return is_object($adminService);
                } catch (Exception $e) {
                    echo "Error: " . $e->getMessage() . " ";
                    return false;
                }
            }
        );
        
        if (!$adminService) {
            echo "⚠️ No se pudo instanciar AdminService - saltando tests funcionales\n\n";
            return;
        }
        
        // Test 2: Verificar métodos públicos disponibles
        $this->runTest(
            "Verificar método registerAdmin existe",
            function() use ($adminService) {
                return method_exists($adminService, 'registerAdmin');
            }
        );
        
        $this->runTest(
            "Verificar método loginAdmin existe",
            function() use ($adminService) {
                return method_exists($adminService, 'loginAdmin');
            }
        );
        
        $this->runTest(
            "Verificar método getAllAdmins existe", 
            function() use ($adminService) {
                return method_exists($adminService, 'getAllAdmins');
            }
        );
        
        // Test 3: Test básico de validación (sin DB)
        $this->runTest(
            "Validar rechazo de datos vacíos",
            function() use ($adminService) {
                try {
                    $result = $adminService->registerAdmin([]);
                    // Debe devolver error por datos vacíos
                    return isset($result['success']) && $result['success'] === false;
                } catch (Exception $e) {
                    // Si lanza excepción por datos inválidos, también es correcto
                    return true;
                }
            }
        );
        
        echo "\n";
    }
    
    private function testAdminWorkflow(): void
    {
        echo "🔄 TEST DE FLUJO DE ADMINISTRACIÓN\n";
        echo "-" . str_repeat("-", 35) . "\n";
        
        if (!class_exists('AdminService')) {
            echo "⚠️ AdminService no disponible - saltando workflow tests\n\n";
            return;
        }
        
        try {
            $adminService = new AdminService();
            
            // Test 1: Registro de administrador con datos válidos
            $testAdminData = [
                'nombres' => 'Admin Test Funcional',
                'apellido1' => 'Prueba',
                'apellido2' => 'Servicios',
                'correo' => 'admin.test.funcional@cyberhole.com',
                'contrasena' => 'TestFuncional123!'
            ];
            
            $this->runTest(
                "Registrar administrador con datos válidos",
                function() use ($adminService, $testAdminData) {
                    try {
                        $result = $adminService->registerAdmin($testAdminData);
                        
                        if (is_array($result) && isset($result['success'])) {
                            if ($result['success'] && isset($result['admin_id'])) {
                                $this->createdRecords['admin_id'] = $result['admin_id'];
                                return true;
                            }
                        }
                        
                        return false;
                    } catch (Exception $e) {
                        echo "Error: " . $e->getMessage() . " ";
                        return false;
                    }
                }
            );
            
            // Test 2: Login con credenciales válidas (si se creó el admin)
            if (isset($this->createdRecords['admin_id'])) {
                $this->runTest(
                    "Login con credenciales válidas",
                    function() use ($adminService, $testAdminData) {
                        try {
                            $result = $adminService->loginAdmin(
                                $testAdminData['correo'],
                                $testAdminData['contrasena']
                            );
                            
                            return is_array($result) && 
                                   isset($result['success']) && 
                                   $result['success'] === true;
                        } catch (Exception $e) {
                            echo "Error: " . $e->getMessage() . " ";
                            return false;
                        }
                    }
                );
            }
            
            // Test 3: Obtener lista de administradores
            $this->runTest(
                "Obtener lista de administradores",
                function() use ($adminService) {
                    try {
                        $result = $adminService->getAllAdmins();
                        return is_array($result);
                    } catch (Exception $e) {
                        echo "Error: " . $e->getMessage() . " ";
                        return false;
                    }
                }
            );
            
            // Test 4: Validar email duplicado
            $this->runTest(
                "Rechazar email duplicado",
                function() use ($adminService, $testAdminData) {
                    try {
                        $result = $adminService->registerAdmin($testAdminData);
                        // Debe fallar por email duplicado
                        return isset($result['success']) && $result['success'] === false;
                    } catch (Exception $e) {
                        // También es válido que lance excepción
                        return true;
                    }
                }
            );
            
        } catch (Exception $e) {
            echo "⚠️ Error en flujo de administración: " . $e->getMessage() . "\n";
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
        echo "\n" . "=" . str_repeat("=", 55) . "\n";
        echo "🏁 RESULTADOS DEL TEST FUNCIONAL DE SERVICIOS\n";
        echo "=" . str_repeat("=", 55) . "\n\n";
        
        $failedTests = $this->totalTests - $this->passedTests;
        $successRate = round(($this->passedTests / $this->totalTests) * 100, 2);
        
        echo "📊 ESTADÍSTICAS:\n";
        echo "   Total de pruebas: {$this->totalTests}\n";
        echo "   Exitosas: {$this->passedTests}\n";
        echo "   Fallidas: {$failedTests}\n";
        echo "   Tasa de éxito: {$successRate}%\n\n";
        
        // Registros creados
        if (!empty($this->createdRecords)) {
            echo "📝 REGISTROS CREADOS:\n";
            foreach ($this->createdRecords as $type => $id) {
                echo "   🆔 {$type}: {$id}\n";
            }
            echo "\n";
        }
        
        // Pruebas fallidas
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
        echo "🎯 CONCLUSIÓN DE LÓGICA DE NEGOCIO:\n";
        if ($successRate >= 90) {
            echo "   🟢 EXCELENTE - Lógica de negocio funcionando correctamente\n";
            echo "   ✅ Los servicios están listos para uso en producción\n";
        } elseif ($successRate >= 70) {
            echo "   🟡 BUENO - Lógica de negocio funcional con mejoras menores\n";
            echo "   ⚠️ Revisar las fallas para optimizar los servicios\n";
        } elseif ($successRate >= 50) {
            echo "   🟠 REGULAR - Servicios requieren atención en lógica de negocio\n";
            echo "   🔧 Hay problemas importantes que corregir\n";
        } else {
            echo "   🔴 CRÍTICO - Lógica de negocio requiere revisión completa\n";
            echo "   🚨 Los servicios no están listos para producción\n";
        }
        
        echo "\n✨ Test funcional completado - " . date('Y-m-d H:i:s') . "\n";
        echo "=" . str_repeat("=", 55) . "\n";
    }
}

// Ejecutar test funcional
try {
    $tester = new AdminServicesFunctionalTest();
    $tester->runAllTests();
} catch (Exception $e) {
    echo "💥 ERROR CRÍTICO: " . $e->getMessage() . "\n";
    exit(1);
}
?>
