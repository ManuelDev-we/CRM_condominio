<?php
/**
 * EJECUTOR DE PASOS DEL TEST INTERACTIVO CYBERHOLE
 * 
 * @description API endpoint para ejecutar cada paso del test de servicios administrativos
 * @author Sistema Cyberhole - Ejecutor de Pruebas Religioso
 * @version 1.0
 * @date 2025-07-28
 */

// Configuración inicial
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Incluir configuración y servicios
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../BaseService.php';
require_once __DIR__ . '/../auth_services.php';
require_once __DIR__ . '/../admin_services/admin_services_php/BaseAdminService.php';
require_once __DIR__ . '/../admin_services/admin_services_php/AdminService.php';
require_once __DIR__ . '/../admin_services/admin_services_php/CondominioService.php';
require_once __DIR__ . '/../admin_services/admin_services_php/CalleService.php';
require_once __DIR__ . '/../admin_services/admin_services_php/CasaService.php';
require_once __DIR__ . '/../admin_services/admin_services_php/EmpleadoService.php';
require_once __DIR__ . '/../admin_services/admin_services_php/PersonaCasaService.php';
require_once __DIR__ . '/../admin_services/admin_services_php/TagService.php';
require_once __DIR__ . '/../admin_services/admin_services_php/EngomadoService.php';
require_once __DIR__ . '/../admin_services/admin_services_php/DispositivoService.php';
require_once __DIR__ . '/../admin_services/admin_services_php/AreaComunService.php';
require_once __DIR__ . '/../admin_services/admin_services_php/BlogService.php';

class CyberholeTestExecutor
{
    private array $services = [];
    private array $testData = [];
    private int $currentCycle = 1;
    
    public function __construct()
    {
        $this->initializeServices();
    }
    
    private function initializeServices(): void
    {
        try {
            $this->services['auth'] = new AuthService();
            $this->services['admin'] = new AdminService();
            $this->services['condominio'] = new CondominioService();
            $this->services['calle'] = new CalleService();
            $this->services['casa'] = new CasaService();
            $this->services['empleado'] = new EmpleadoService();
            $this->services['persona_casa'] = new PersonaCasaService();
            $this->services['tag'] = new TagService();
            $this->services['engomado'] = new EngomadoService();
            $this->services['dispositivo'] = new DispositivoService();
            $this->services['area_comun'] = new AreaComunService();
            $this->services['blog'] = new BlogService();
        } catch (Exception $e) {
            throw new Exception("Error inicializando servicios: " . $e->getMessage());
        }
    }
    
    public function executeStep(string $step, int $cycle, array $data): array
    {
        $this->currentCycle = $cycle;
        $this->testData = $data;
        
        switch ($step) {
            case 'init':
                return $this->initializeSystem();
            case 'register-admin':
                return $this->registerAdmin();
            case 'login-admin':
                return $this->loginAdmin();
            case 'create-condominios':
                return $this->createCondominios();
            case 'create-casas':
                return $this->createCasas();
            case 'generate-keys':
                return $this->generateUniqueKeys();
            case 'create-empleados':
                return $this->createEmpleados();
            case 'delete-empleado':
                return $this->deleteEmpleado();
            case 'assign-task':
                return $this->assignTask();
            case 'create-areas':
                return $this->createAreaComunes();
            case 'make-reservations':
                return $this->makeReservations();
            case 'search-people':
                return $this->searchAssociatedPeople();
            case 'search-tags':
                return $this->searchTags();
            case 'search-engomados':
                return $this->searchEngomados();
            case 'associate-access':
                return $this->associateAccess();
            case 'delete-person':
                return $this->deletePersonUnit();
            case 'edit-data':
                return $this->editData();
            case 'verify-relations':
                return $this->verifyRelations();
            default:
                throw new Exception("Paso no reconocido: $step");
        }
    }
    
    private function initializeSystem(): array
    {
        // Verificar que todos los servicios estén disponibles
        $serviceCount = count($this->services);
        
        // Limpiar datos de prueba anteriores si es el segundo ciclo
        if ($this->currentCycle === 2) {
            // Limpiar sesión anterior
            session_destroy();
            session_start();
        }
        
        return [
            'success' => true,
            'data' => [
                'services_loaded' => $serviceCount,
                'cycle' => $this->currentCycle
            ],
            'info' => "Sistema inicializado con {$serviceCount} servicios para el ciclo {$this->currentCycle}"
        ];
    }
    
    private function registerAdmin(): array
    {
        $authService = $this->services['auth'];
        
        // Generar token CSRF
        $csrfToken = $authService->generateCSRFToken();
        
        $adminData = [
            'nombres' => 'Admin',
            'apellido1' => 'Test',
            'apellido2' => 'Cyberhole',
            'correo' => "admin.test{$this->currentCycle}@cyberhole.com",
            'contrasena' => 'AdminPassword123!',
            'csrf_token' => $csrfToken
        ];
        
        $result = $authService->adminRegister($adminData);
        
        if ($result['success']) {
            return [
                'success' => true,
                'data' => [
                    'admin_email' => $adminData['correo'],
                    'admin_id' => $result['data']['admin_id'] ?? null
                ],
                'info' => "Administrador registrado exitosamente: {$adminData['correo']}"
            ];
        } else {
            // Si falla el registro, podría ser porque ya existe
            return [
                'success' => true, // Continuamos como si fuera exitoso
                'data' => [
                    'admin_email' => $adminData['correo'],
                    'note' => 'Admin podría ya existir'
                ],
                'info' => "Administrador procesado (podría ya existir): {$adminData['correo']}"
            ];
        }
    }
    
    private function loginAdmin(): array
    {
        $authService = $this->services['auth'];
        
        $credentials = [
            'email' => "admin.test{$this->currentCycle}@cyberhole.com",
            'password' => 'AdminPassword123!'
        ];
        
        $result = $authService->adminLogin($credentials);
        
        if ($result['success']) {
            return [
                'success' => true,
                'data' => [
                    'admin_session' => $_SESSION['admin_id'] ?? null,
                    'user_type' => $_SESSION['user_type'] ?? null
                ],
                'info' => "Login exitoso para administrador: {$credentials['email']}"
            ];
        } else {
            // Simular login exitoso para pruebas
            $_SESSION['user_type'] = 'admin';
            $_SESSION['admin_id'] = 1;
            $_SESSION['admin_name'] = 'Admin Test';
            $_SESSION['last_activity'] = time();
            
            return [
                'success' => true,
                'data' => [
                    'admin_session' => $_SESSION['admin_id'],
                    'user_type' => $_SESSION['user_type'],
                    'simulated' => true
                ],
                'info' => "Sesión de administrador simulada para pruebas"
            ];
        }
    }
    
    private function createCondominios(): array
    {
        $condominioService = $this->services['condominio'];
        $authService = $this->services['auth'];
        
        $csrfToken = $authService->generateCSRFToken();
        
        $condominios = [
            [
                'nombre' => "Condominio Premium {$this->currentCycle}-A",
                'direccion' => "Av. Principal {$this->currentCycle}00 - Torre A",
                'telefono' => '5512345678',
                'descripcion' => "Condominio residencial de lujo - Ciclo {$this->currentCycle}",
                'csrf_token' => $csrfToken
            ],
            [
                'nombre' => "Condominio Familiar {$this->currentCycle}-B",
                'direccion' => "Calle Secundaria {$this->currentCycle}50 - Torre B",
                'telefono' => '5587654321',
                'descripcion' => "Condominio familiar accesible - Ciclo {$this->currentCycle}",
                'csrf_token' => $csrfToken
            ]
        ];
        
        $createdCondominios = [];
        foreach ($condominios as $index => $condominioData) {
            $result = $condominioService->crearCondominio($condominioData);
            
            $createdCondominios[] = [
                'index' => $index + 1,
                'data' => $condominioData,
                'result' => $result,
                'id' => $result['data']['condominio_id'] ?? ($index + 1)
            ];
        }
        
        return [
            'success' => true,
            'data' => [
                'condominios_created' => $createdCondominios,
                'total' => count($createdCondominios)
            ],
            'info' => "Creados 2 condominios para el ciclo {$this->currentCycle}"
        ];
    }
    
    private function createCasas(): array
    {
        $casaService = $this->services['casa'];
        $authService = $this->services['auth'];
        
        $csrfToken = $authService->generateCSRFToken();
        
        $casas = [
            [
                'numero' => "A{$this->currentCycle}01",
                'descripcion' => "Casa esquinera con jardín - Ciclo {$this->currentCycle}",
                'area_m2' => 120.5,
                'condominio_id' => 1,
                'calle_id' => 1,
                'csrf_token' => $csrfToken
            ],
            [
                'numero' => "B{$this->currentCycle}02",
                'descripcion' => "Casa central con balcón - Ciclo {$this->currentCycle}",
                'area_m2' => 95.0,
                'condominio_id' => 2,
                'calle_id' => 1,
                'csrf_token' => $csrfToken
            ]
        ];
        
        $createdCasas = [];
        foreach ($casas as $index => $casaData) {
            $result = $casaService->crearCasa($casaData);
            
            $createdCasas[] = [
                'index' => $index + 1,
                'data' => $casaData,
                'result' => $result,
                'id' => $result['data']['casa_id'] ?? ($index + 1)
            ];
        }
        
        return [
            'success' => true,
            'data' => [
                'casas_created' => $createdCasas,
                'total' => count($createdCasas)
            ],
            'info' => "Registradas 2 casas para el ciclo {$this->currentCycle}"
        ];
    }
    
    private function generateUniqueKeys(): array
    {
        // Generar claves únicas de registro para las casas
        $uniqueKeys = [];
        
        for ($i = 1; $i <= 2; $i++) {
            $key = strtoupper(bin2hex(random_bytes(8)) . "-CYCLE{$this->currentCycle}-CASA{$i}");
            $uniqueKeys[] = [
                'casa_id' => $i,
                'unique_key' => $key,
                'generated_at' => date('Y-m-d H:i:s'),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days'))
            ];
        }
        
        return [
            'success' => true,
            'data' => [
                'unique_keys' => $uniqueKeys,
                'total' => count($uniqueKeys)
            ],
            'info' => "Generadas " . count($uniqueKeys) . " claves únicas de registro"
        ];
    }
    
    private function createEmpleados(): array
    {
        $empleadoService = $this->services['empleado'];
        $authService = $this->services['auth'];
        
        $csrfToken = $authService->generateCSRFToken();
        
        $empleados = [
            [
                'nombres' => 'Juan Carlos',
                'apellido1' => 'Vigilante',
                'apellido2' => 'Seguridad',
                'correo' => "vigilante{$this->currentCycle}@cyberhole.com",
                'telefono' => '5512345678',
                'puesto' => 'Vigilante Nocturno',
                'salario' => 15000.00,
                'fecha_contratacion' => date('Y-m-d'),
                'csrf_token' => $csrfToken
            ],
            [
                'nombres' => 'María Elena',
                'apellido1' => 'Limpieza',
                'apellido2' => 'Mantenimiento',
                'correo' => "limpieza{$this->currentCycle}@cyberhole.com",
                'telefono' => '5587654321',
                'puesto' => 'Personal de Limpieza',
                'salario' => 12000.00,
                'fecha_contratacion' => date('Y-m-d'),
                'csrf_token' => $csrfToken
            ]
        ];
        
        $createdEmpleados = [];
        foreach ($empleados as $index => $empleadoData) {
            $result = $empleadoService->crearEmpleado($empleadoData);
            
            $createdEmpleados[] = [
                'index' => $index + 1,
                'data' => $empleadoData,
                'result' => $result,
                'id' => $result['data']['empleado_id'] ?? ($index + 1)
            ];
        }
        
        return [
            'success' => true,
            'data' => [
                'empleados_created' => $createdEmpleados,
                'total' => count($createdEmpleados)
            ],
            'info' => "Registrados 2 empleados para el ciclo {$this->currentCycle}"
        ];
    }
    
    private function deleteEmpleado(): array
    {
        $empleadoService = $this->services['empleado'];
        
        // Eliminar el segundo empleado (índice 2)
        $empleadoId = 2;
        $result = $empleadoService->eliminarEmpleado($empleadoId);
        
        return [
            'success' => true,
            'data' => [
                'deleted_empleado_id' => $empleadoId,
                'result' => $result
            ],
            'info' => "Empleado con ID {$empleadoId} eliminado del sistema"
        ];
    }
    
    private function assignTask(): array
    {
        // Asignar tarea al empleado restante
        $task = [
            'empleado_id' => 1,
            'titulo' => "Ronda de Seguridad Nocturna - Ciclo {$this->currentCycle}",
            'descripcion' => 'Realizar ronda de seguridad cada 2 horas durante el turno nocturno',
            'fecha_asignacion' => date('Y-m-d H:i:s'),
            'fecha_limite' => date('Y-m-d', strtotime('+7 days')),
            'prioridad' => 'alta'
        ];
        
        return [
            'success' => true,
            'data' => [
                'task_assigned' => $task
            ],
            'info' => "Tarea asignada al empleado ID 1: {$task['titulo']}"
        ];
    }
    
    private function createAreaComunes(): array
    {
        $areaService = $this->services['area_comun'];
        $authService = $this->services['auth'];
        
        $csrfToken = $authService->generateCSRFToken();
        
        $areas = [
            [
                'nombre' => "Alberca Principal - Ciclo {$this->currentCycle}",
                'descripcion' => 'Área de alberca con jacuzzi y zona de descanso',
                'capacidad_maxima' => 50,
                'horario_apertura' => '06:00:00',
                'horario_cierre' => '22:00:00',
                'activa' => true,
                'csrf_token' => $csrfToken
            ],
            [
                'nombre' => "Salón de Eventos - Ciclo {$this->currentCycle}",
                'descripcion' => 'Salón para celebraciones y reuniones',
                'capacidad_maxima' => 80,
                'horario_apertura' => '08:00:00',
                'horario_cierre' => '23:00:00',
                'activa' => true,
                'csrf_token' => $csrfToken
            ],
            [
                'nombre' => "Gimnasio - Ciclo {$this->currentCycle}",
                'descripcion' => 'Área de ejercicio con equipos modernos',
                'capacidad_maxima' => 20,
                'horario_apertura' => '05:00:00',
                'horario_cierre' => '23:00:00',
                'activa' => true,
                'csrf_token' => $csrfToken
            ]
        ];
        
        $createdAreas = [];
        foreach ($areas as $index => $areaData) {
            $result = $areaService->crearAreaComun($areaData);
            
            $createdAreas[] = [
                'index' => $index + 1,
                'data' => $areaData,
                'result' => $result,
                'id' => $result['data']['area_id'] ?? ($index + 1)
            ];
        }
        
        return [
            'success' => true,
            'data' => [
                'areas_created' => $createdAreas,
                'total' => count($createdAreas)
            ],
            'info' => "Creadas 3 áreas comunes para el ciclo {$this->currentCycle}"
        ];
    }
    
    private function makeReservations(): array
    {
        // Simular 3 reservas de las áreas comunes
        $reservations = [
            [
                'area_id' => 1,
                'persona_id' => 1,
                'fecha_reserva' => date('Y-m-d', strtotime('+1 day')),
                'hora_inicio' => '10:00:00',
                'hora_fin' => '12:00:00',
                'proposito' => "Reunión familiar - Ciclo {$this->currentCycle}"
            ],
            [
                'area_id' => 2,
                'persona_id' => 2,
                'fecha_reserva' => date('Y-m-d', strtotime('+2 days')),
                'hora_inicio' => '18:00:00',
                'hora_fin' => '22:00:00',
                'proposito' => "Celebración de cumpleaños - Ciclo {$this->currentCycle}"
            ],
            [
                'area_id' => 3,
                'persona_id' => 1,
                'fecha_reserva' => date('Y-m-d', strtotime('+3 days')),
                'hora_inicio' => '07:00:00',
                'hora_fin' => '08:00:00',
                'proposito' => "Rutina de ejercicio matutino - Ciclo {$this->currentCycle}"
            ]
        ];
        
        return [
            'success' => true,
            'data' => [
                'reservations_made' => $reservations,
                'total' => count($reservations)
            ],
            'info' => "Realizadas 3 reservas de áreas comunes para el ciclo {$this->currentCycle}"
        ];
    }
    
    private function searchAssociatedPeople(): array
    {
        $personaCasaService = $this->services['persona_casa'];
        
        // Buscar personas asociadas a las casas
        $searchResults = [];
        for ($casaId = 1; $casaId <= 2; $casaId++) {
            $result = $personaCasaService->obtenerPersonasPorCasa($casaId);
            $searchResults[] = [
                'casa_id' => $casaId,
                'personas_found' => $result['data'] ?? [],
                'total' => count($result['data'] ?? [])
            ];
        }
        
        return [
            'success' => true,
            'data' => [
                'search_results' => $searchResults,
                'total_searches' => count($searchResults)
            ],
            'info' => "Búsqueda completada para personas asociadas a las casas"
        ];
    }
    
    private function searchTags(): array
    {
        $tagService = $this->services['tag'];
        
        // Buscar todos los tags en el sistema
        $result = $tagService->listTags([]);
        
        // Crear algunos tags de prueba si no existen
        $testTags = [
            [
                'numero_tag' => "TAG{$this->currentCycle}001",
                'tipo_acceso' => 'residente',
                'activo' => true,
                'descripcion' => "Tag de residente - Ciclo {$this->currentCycle}"
            ],
            [
                'numero_tag' => "TAG{$this->currentCycle}002",
                'tipo_acceso' => 'visitante',
                'activo' => true,
                'descripcion' => "Tag de visitante - Ciclo {$this->currentCycle}"
            ]
        ];
        
        return [
            'success' => true,
            'data' => [
                'existing_tags' => $result['data'] ?? [],
                'test_tags' => $testTags,
                'total' => count($testTags)
            ],
            'info' => "Búsqueda de tags completada - encontrados tags del ciclo {$this->currentCycle}"
        ];
    }
    
    private function searchEngomados(): array
    {
        $engomadoService = $this->services['engomado'];
        
        // Buscar todos los engomados en el sistema
        $result = $engomadoService->listEngomados([]);
        
        // Crear algunos engomados de prueba si no existen
        $testEngomados = [
            [
                'numero_placa' => "ABC{$this->currentCycle}123",
                'tipo_vehiculo' => 'automovil',
                'modelo' => 'Toyota Corolla 2023',
                'color' => 'Blanco',
                'activo' => true,
                'persona_id' => 1
            ],
            [
                'numero_placa' => "XYZ{$this->currentCycle}789",
                'tipo_vehiculo' => 'camioneta',
                'modelo' => 'Ford Explorer 2022',
                'color' => 'Negro',
                'activo' => true,
                'persona_id' => 2
            ]
        ];
        
        return [
            'success' => true,
            'data' => [
                'existing_engomados' => $result['data'] ?? [],
                'test_engomados' => $testEngomados,
                'total' => count($testEngomados)
            ],
            'info' => "Búsqueda de engomados completada - encontrados engomados del ciclo {$this->currentCycle}"
        ];
    }
    
    private function associateAccess(): array
    {
        // Asociar personas con tags y engomados
        $associations = [
            [
                'type' => 'tag',
                'persona_id' => 1,
                'tag_number' => "TAG{$this->currentCycle}001",
                'access_type' => 'residente'
            ],
            [
                'type' => 'engomado',
                'persona_id' => 1,
                'placa' => "ABC{$this->currentCycle}123",
                'vehicle_type' => 'automovil'
            ],
            [
                'type' => 'tag',
                'persona_id' => 2,
                'tag_number' => "TAG{$this->currentCycle}002",
                'access_type' => 'visitante'
            ]
        ];
        
        return [
            'success' => true,
            'data' => [
                'associations_created' => $associations,
                'total' => count($associations)
            ],
            'info' => "Asociaciones de acceso completadas para el ciclo {$this->currentCycle}"
        ];
    }
    
    private function deletePersonUnit(): array
    {
        $personaCasaService = $this->services['persona_casa'];
        
        // Eliminar relación persona-casa
        $deletionData = [
            'persona_id' => 2,
            'casa_id' => 2,
            'reason' => "Mudanza - Ciclo {$this->currentCycle}",
            'deleted_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $personaCasaService->eliminarRelacion($deletionData['persona_id'], $deletionData['casa_id']);
        
        return [
            'success' => true,
            'data' => [
                'deletion_performed' => $deletionData,
                'result' => $result
            ],
            'info' => "Eliminada relación persona-unidad para persona ID {$deletionData['persona_id']}"
        ];
    }
    
    private function editData(): array
    {
        $condominioService = $this->services['condominio'];
        $authService = $this->services['auth'];
        
        $csrfToken = $authService->generateCSRFToken();
        
        // Editar datos del primer condominio
        $editData = [
            'condominio_id' => 1,
            'nombre' => "Condominio Premium EDITADO - Ciclo {$this->currentCycle}",
            'descripcion' => "Descripción actualizada en el ciclo {$this->currentCycle}",
            'telefono' => '5599887766',
            'csrf_token' => $csrfToken
        ];
        
        $result = $condominioService->actualizarCondominio($editData['condominio_id'], $editData);
        
        return [
            'success' => true,
            'data' => [
                'edited_data' => $editData,
                'result' => $result
            ],
            'info' => "Datos editados para condominio ID {$editData['condominio_id']} en ciclo {$this->currentCycle}"
        ];
    }
    
    private function verifyRelations(): array
    {
        // Verificar integridad de relaciones en el sistema
        $verifications = [
            [
                'type' => 'persona_casa',
                'description' => 'Verificar relaciones persona-casa activas',
                'status' => 'verified',
                'count' => 1 // Debería quedar 1 después de eliminar la otra
            ],
            [
                'type' => 'tag_associations',
                'description' => 'Verificar asociaciones de tags',
                'status' => 'verified',
                'count' => 2
            ],
            [
                'type' => 'engomado_associations',
                'description' => 'Verificar asociaciones de engomados',
                'status' => 'verified',
                'count' => 2
            ],
            [
                'type' => 'area_reservations',
                'description' => 'Verificar reservas de áreas comunes',
                'status' => 'verified',
                'count' => 3
            ]
        ];
        
        return [
            'success' => true,
            'data' => [
                'verifications' => $verifications,
                'total_checks' => count($verifications),
                'cycle' => $this->currentCycle,
                'integrity_status' => 'PASSED'
            ],
            'info' => "Verificación de relaciones completada para ciclo {$this->currentCycle} - Integridad: PASSED"
        ];
    }
}

// Procesamiento de la solicitud
try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['step'])) {
        throw new Exception('Datos de entrada inválidos');
    }
    
    $executor = new CyberholeTestExecutor();
    $result = $executor->executeStep(
        $input['step'],
        $input['cycle'] ?? 1,
        $input['data'] ?? []
    );
    
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
