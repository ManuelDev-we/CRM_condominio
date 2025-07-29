# 🛡️ MIDDLEWARES INTEGRADOS - NUEVA ESTRUCTURA CYBERHOLE

## 🎯 PROPÓSITO DEL DOCUMENTO
Documento técnico que establece la **integración completa de middlewares** con la nueva arquitectura de servicios del sistema Cyberhole Condominios. Define cómo los middlewares existentes se integran perfectamente con la **capa de servicios dual** (admin_services/ y resident_services/).

---

## 🔍 COMPATIBILIDAD CONFIRMADA

### ✅ **AUDITORÍA COMPLETA REALIZADA**
Se verificó la compatibilidad total entre:
- **🔐 AuthMiddleware.php** (314 líneas) - Sistema dual admin/residente
- **👥 RoleMiddleware.php** (311 líneas) - Jerarquía ADMIN > RESIDENTE > EMPLEADO
- **🏢 CondominioOwnershipMiddleware.php** (406 líneas) - Validación de ownership
- **🛡️ CsrfMiddleware.php** (341 líneas) - Protección CSRF
- **⏱️ RateLimitMiddleware.php** (403 líneas) - Control de tasa
- **🎛️ MiddlewareManager.php** (326 líneas) - Orquestador central

### 🟢 **VEREDICTO: 100% COMPATIBLE**
Todos los middlewares están perfectamente alineados con la nueva arquitectura de servicios dual.

---

## 🏗️ ARQUITECTURA DE INTEGRACIÓN

### **📋 Flujo de Integración Completo**
```
┌─────────────────────────────────────────────────────────┐
│                    REQUEST PROCESSING                   │
├─────────────────────────────────────────────────────────┤
│ 1. HTTP Request                                         │
│ 2. MiddlewareManager::execute()                         │
│ 3. AuthMiddleware → Valida admin/residente              │
│ 4. RoleMiddleware → Verifica permisos                   │
│ 5. CondominioOwnershipMiddleware → Filtra ownership     │
│ 6. CsrfMiddleware → Protege modificaciones              │
│ 7. RateLimitMiddleware → Previene abusos                │
│ 8. auth_services.php → Carga servicios según rol       │
│ 9. admin_services/ o resident_services/                │
│ 10. Modelos → Acceso a datos                           │
│ 11. Respuesta estructurada                             │
└─────────────────────────────────────────────────────────┘
```

### **🔄 Integración con auth_services.php**
```php
<?php
// auth_services.php - Orquestador principal
require_once __DIR__ . '/../middlewares/MiddlewareManager.php';

class AuthServiceOrchestrator {
    
    /**
     * Cargar servicios según usuario autenticado
     */
    public static function loadUserServices($route = '', $method = 'GET', $requiredRoles = []) {
        // 1. Ejecutar pipeline completo de middlewares
        $user = MiddlewareManager::execute($route, $method, $requiredRoles);
        
        // 2. Cargar servicios según tipo de usuario
        switch ($user['type']) {
            case 'ADMIN':
                return self::loadAdminServices($user);
            case 'RESIDENTE':
                return self::loadResidentServices($user);
            case 'EMPLEADO':
                return self::loadEmployeeServices($user);
            default:
                throw new UnauthorizedException('Tipo de usuario no válido');
        }
    }
    
    private static function loadAdminServices($user) {
        // Cargar servicios de admin_services/
        require_once __DIR__ . '/admin_services/AdminService.php';
        require_once __DIR__ . '/admin_services/CondominioService.php';
        // ... otros servicios admin
        
        return [
            'user' => $user,
            'services_type' => 'admin',
            'available_services' => [
                'AccesosService', 'AdminService', 'AreaComunService',
                'BlogService', 'CalleService', 'CasaService',
                'CondominioService', 'EmpleadoService', 'EngomadoService',
                'TagService', 'DispositivoService', 'MisCasasService',
                'PersonaUnidadService', 'PersonaCasaService'
            ]
        ];
    }
    
    private static function loadResidentServices($user) {
        // Cargar servicios de resident_services/
        require_once __DIR__ . '/resident_services/AccesosService.php';
        require_once __DIR__ . '/resident_services/BlogService.php';
        // ... otros servicios residente
        
        return [
            'user' => $user,
            'services_type' => 'resident',
            'available_services' => [
                'AccesosService', 'BlogService', 'EngomadoService',
                'TagService', 'DispositivoService', 'PersonaUnidadService',
                'AreaComunService', 'MisCasasService', 'PersonaCasaService'
            ]
        ];
    }
}
?>
```

---

## 🔐 ESPECIFICACIONES POR MIDDLEWARE

### **1. AuthMiddleware - Sistema Dual**

#### **🎯 Funcionalidad**
- Autenticación separada para `admin` y `personas` tables
- Soporte para sesiones PHP y JWT tokens
- Exclusión automática de rutas públicas

#### **🔗 Integración con Servicios**
```php
// En cualquier servicio admin
class AdminService {
    public function createCondominio($data) {
        // Middleware ya validó que es ADMIN
        $user = $_SESSION['user_data']; // Disponible automáticamente
        
        // Validar ownership si es necesario
        if (!Condominio::validateAdminOwnership($user['id'], $data['condominio_id'])) {
            throw new UnauthorizedException('Sin permisos sobre este condominio');
        }
        
        // Proceder con lógica de negocio
        return Condominio::create($data);
    }
}

// En cualquier servicio residente
class ResidentEngomadoService {
    public function createEngomado($data) {
        // Middleware ya validó que es RESIDENTE
        $user = $_SESSION['user_data']; // Disponible automáticamente
        
        // Validar ownership de la casa
        if (!Casa::validateResidentOwnership($user['id'], $data['casa_id'])) {
            throw new UnauthorizedException('Sin permisos sobre esta propiedad');
        }
        
        // Proceder con lógica de negocio
        return Engomado::create($data);
    }
}
```

#### **📋 Datos de Usuario Disponibles**
```php
$_SESSION['user_data'] = [
    'id' => 123,
    'type' => 'ADMIN', // o 'RESIDENTE'
    'email' => 'admin@example.com',
    'nombre' => 'Juan Pérez',
    'condominio_id' => 5, // Para admins
    'casa_id' => 15, // Para residentes
    'permissions' => [...],
    'last_login' => '2025-07-26 10:30:00'
];
```

### **2. RoleMiddleware - Control Jerárquico**

#### **🎯 Jerarquía de Roles**
- **ADMIN (Nivel 3)**: Acceso completo, puede hacer todo lo de RESIDENTE y EMPLEADO
- **RESIDENTE (Nivel 2)**: Gestión de sus propiedades y datos personales
- **EMPLEADO (Nivel 1)**: Control de accesos y tareas específicas

#### **🔗 Integración con Servicios**
```php
// Uso en servicios con roles específicos
class CondominioService {
    public function deleteCondominio($condominioId) {
        // Solo ADMIN puede eliminar condominios
        $user = MiddlewareManager::adminOnly('/api/condominios/delete');
        
        // Lógica de eliminación
        return Condominio::delete($condominioId);
    }
}

class AccesosService {
    public function viewAllAccesos($condominioId) {
        // ADMIN o EMPLEADO pueden ver todos los accesos
        $user = MiddlewareManager::execute(
            '/api/accesos',
            'GET',
            ['ADMIN', 'EMPLEADO']
        );
        
        // Lógica diferenciada por rol
        if ($user['type'] === 'ADMIN') {
            return Acceso::obtenerTodosPorCondominio($condominioId);
        } else {
            return Acceso::obtenerEmpleadosPorCondominio($condominioId);
        }
    }
}
```

#### **📊 Permisos por Recurso**
```php
// Configuración automática en SecurityConfig.php
'permissions' => [
    'ADMIN' => ['*' => ['create', 'read', 'update', 'delete']],
    'RESIDENTE' => [
        'casa' => ['create', 'read', 'update', 'delete'],
        'vehiculo' => ['create', 'read', 'update', 'delete'],
        'persona' => ['read', 'update'],
        'acceso' => ['read']
    ],
    'EMPLEADO' => [
        'acceso' => ['create', 'read', 'update', 'delete'],
        'vehiculo' => ['read'],
        'persona' => ['read']
    ]
]
```

### **3. CondominioOwnershipMiddleware - Validación de Ownership**

#### **🎯 Funcionalidad**
- Validación automática de que usuarios solo accedan a SUS recursos
- Bypass automático para ADMINs
- Filtrado por `condominio_id` para admins y `casa_id` para residentes

#### **🔗 Integración con Servicios**
```php
class AdminAccesosService {
    public function getAccesosByCondominio($condominioId) {
        // Middleware automáticamente valida que el admin tenga acceso al condominio
        $user = MiddlewareManager::execute(
            '/api/admin/accesos',
            'GET',
            ['ADMIN'],
            $condominioId
        );
        
        // Si llegamos aquí, el admin tiene acceso al condominio
        return Acceso::obtenerResidentesPorCondominio($condominioId);
    }
}

class ResidentCasaService {
    public function updateCasa($casaId, $data) {
        // Middleware automáticamente valida que el residente sea propietario
        $user = MiddlewareManager::residentOnly('/api/resident/casas', 'PUT', $casaId);
        
        // Si llegamos aquí, el residente tiene acceso a la casa
        return Casa::update($casaId, $data);
    }
}
```

#### **🔍 Métodos de Validación Automática**
```php
// Para admins - Validación por condominio
CondominioOwnershipMiddleware::verify($user, $condominioId);

// Para residentes - Validación por casa
Casa::validateResidentOwnership($user['id'], $casaId);

// Filtros SQL automáticos
[$condition, $params] = CondominioOwnershipMiddleware::getCondominioFilter($user);
```

### **4. CsrfMiddleware - Protección CSRF**

#### **🎯 Funcionalidad**
- Protección automática en métodos POST/PUT/PATCH/DELETE
- Tokens únicos por acción y sesión
- Integración con formularios HTML y AJAX

#### **🔗 Integración con Servicios**
```php
// En formularios HTML
class CasaService {
    public function renderCreateForm() {
        return '
        <form method="POST" action="/api/casas">
            ' . MiddlewareManager::csrfField('create_casa') . '
            <input type="text" name="numero" required>
            <input type="submit" value="Crear Casa">
        </form>';
    }
    
    public function createCasa($data) {
        // CSRF automáticamente validado por middleware
        $user = MiddlewareManager::execute('/api/casas', 'POST', ['ADMIN']);
        
        // Proceder con creación
        return Casa::create($data);
    }
}

// Para AJAX/APIs
class ApiService {
    public function getCsrfToken() {
        return MiddlewareManager::getCsrfToken('api_action');
    }
}
```

#### **📱 Configuración para JavaScript**
```php
// En el head del HTML
echo MiddlewareManager::getCsrfToken()['meta_tag'];

// En JavaScript
const token = document.querySelector('meta[name="csrf-token"]').content;
fetch('/api/endpoint', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': token,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
});
```

### **5. RateLimitMiddleware - Control de Tasa**

#### **🎯 Límites Configurados**
- **Login**: 5 intentos / 15 minutos
- **API General**: 100 requests / hora
- **API Específicas**: 200 requests / hora

#### **🔗 Integración con Servicios**
```php
class AuthService {
    public function adminLogin($credentials) {
        // Rate limiting automático para login
        $user = MiddlewareManager::execute('/api/auth/login', 'POST');
        
        // Proceder con autenticación
        return Admin::adminLogin($credentials);
    }
}

class ApiService {
    public function heavyOperation() {
        // Rate limiting automático para APIs
        $user = MiddlewareManager::protectedApi('POST', ['ADMIN']);
        
        // Operación pesada protegida
        return $this->processHeavyOperation();
    }
}
```

#### **📊 Estadísticas de Uso**
```php
// Obtener estadísticas del usuario actual
$stats = MiddlewareManager::getCurrentUserRateStats('api');
/*
[
    'current_count' => 45,
    'max_attempts' => 100,
    'remaining_attempts' => 55,
    'window_start' => 1627123456,
    'is_blocked' => false
]
*/
```

### **6. MiddlewareManager - Orquestador Central**

#### **🎯 Métodos Especializados para Servicios**

##### **🔐 Métodos por Rol**
```php
// Solo administradores
$user = MiddlewareManager::adminOnly('/admin/panel');

// Solo residentes (con validación de propiedad opcional)
$user = MiddlewareManager::residentOnly('/resident/dashboard', 'GET', $casaId);

// Solo empleados (con validación de condominio opcional)
$user = MiddlewareManager::employeeOnly('/employee/tasks', 'GET', $condominioId);
```

##### **🛡️ Métodos para APIs**
```php
// API protegida completa
$user = MiddlewareManager::protectedApi(
    'POST',                    // Método HTTP
    ['ADMIN', 'RESIDENTE'],    // Roles permitidos
    $condominioId              // ID para validación ownership
);

// Solo autenticación y rol
$user = MiddlewareManager::authAndRole(['ADMIN'], '/admin/users');
```

##### **🔧 Métodos de Utilidad**
```php
// Información del usuario actual
$user = MiddlewareManager::getCurrentUser();

// Verificar si es admin
$isAdmin = MiddlewareManager::isCurrentUserAdmin();

// Verificar permisos específicos
$canDelete = MiddlewareManager::canCurrentUserPerform('delete', 'casa');

// Cerrar sesión
MiddlewareManager::logout();
```

---

## 📋 IMPLEMENTACIÓN EN SERVICIOS

### **🎯 Patrón Base para Admin Services**
```php
<?php
// admin_services/BaseAdminService.php
abstract class BaseAdminService {
    protected $user;
    
    public function __construct() {
        // Validación automática de admin
        $this->user = MiddlewareManager::adminOnly();
    }
    
    protected function validateCondominioOwnership($condominioId) {
        if (!Condominio::validateAdminOwnership($this->user['id'], $condominioId)) {
            throw new UnauthorizedException('Sin permisos sobre este condominio');
        }
    }
    
    protected function getCondominiosAsignados() {
        return Condominio::getCondominiosByAdmin($this->user['id']);
    }
}

// Ejemplo: admin_services/CondominioService.php
class CondominioService extends BaseAdminService {
    public function createCondominio($data) {
        // Middleware ya validó autenticación y rol ADMIN
        
        // Aplicar validaciones específicas
        $this->validateRequiredFields($data, ['nombre', 'direccion']);
        
        // Crear condominio
        $condominioId = Condominio::create($data);
        
        // Asignar admin al condominio
        Condominio::assignAdminToCondominio($this->user['id'], $condominioId);
        
        return [
            'success' => true,
            'data' => ['condominio_id' => $condominioId],
            'message' => 'Condominio creado exitosamente'
        ];
    }
    
    public function getMyCondominios() {
        // Solo condominios asignados al admin
        return [
            'success' => true,
            'data' => $this->getCondominiosAsignados(),
            'message' => 'Condominios obtenidos exitosamente'
        ];
    }
}
?>
```

### **🏠 Patrón Base para Resident Services**
```php
<?php
// resident_services/BaseResidentService.php
abstract class BaseResidentService {
    protected $user;
    
    public function __construct() {
        // Validación automática de residente
        $this->user = MiddlewareManager::residentOnly();
    }
    
    protected function validateCasaOwnership($casaId) {
        if (!Casa::validateResidentOwnership($this->user['id'], $casaId)) {
            throw new UnauthorizedException('Sin permisos sobre esta propiedad');
        }
    }
    
    protected function getMyCasas() {
        return Casa::getCasasByResidente($this->user['id']);
    }
}

// Ejemplo: resident_services/EngomadoService.php
class EngomadoService extends BaseResidentService {
    public function createEngomado($data) {
        // Middleware ya validó autenticación y rol RESIDENTE
        
        // Validar ownership de la casa
        $this->validateCasaOwnership($data['casa_id']);
        
        // Validar que la persona sea el usuario actual
        if ($data['persona_id'] !== $this->user['id']) {
            throw new UnauthorizedException('Solo puedes crear engomados para ti mismo');
        }
        
        // Crear engomado
        $engomadoId = Engomado::create($data);
        
        return [
            'success' => true,
            'data' => ['engomado_id' => $engomadoId],
            'message' => 'Engomado creado exitosamente'
        ];
    }
    
    public function getMyEngomados() {
        // Solo engomados del residente actual
        return [
            'success' => true,
            'data' => Engomado::getEngomadosByResidente($this->user['id']),
            'message' => 'Engomados obtenidos exitosamente'
        ];
    }
}
?>
```

---

## 🔄 FLUJOS DE INTEGRACIÓN ESPECÍFICOS

### **📱 Flujo API REST Completo**
```php
<?php
// api/condominios.php
require_once __DIR__ . '/../services/auth_services.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

try {
    // Cargar servicios según usuario autenticado
    $serviceContainer = AuthServiceOrchestrator::loadUserServices($uri, $method, ['ADMIN']);
    
    switch ($method) {
        case 'GET':
            $condominioService = new CondominioService();
            echo json_encode($condominioService->getMyCondominios());
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $condominioService = new CondominioService();
            echo json_encode($condominioService->createCondominio($data));
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $condominioService = new CondominioService();
            echo json_encode($condominioService->updateCondominio($data));
            break;
            
        case 'DELETE':
            $condominioId = $_GET['id'] ?? null;
            $condominioService = new CondominioService();
            echo json_encode($condominioService->deleteCondominio($condominioId));
            break;
    }
    
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
}
?>
```

### **🖥️ Flujo de Página Web Completa**
```php
<?php
// admin/dashboard.php
require_once __DIR__ . '/../services/auth_services.php';

try {
    // Autenticación y carga de servicios
    $serviceContainer = AuthServiceOrchestrator::loadUserServices('/admin/dashboard', 'GET', ['ADMIN']);
    $user = $serviceContainer['user'];
    
    // Cargar datos necesarios
    $condominioService = new CondominioService();
    $condominios = $condominioService->getMyCondominios();
    
    $accesosService = new AccesosService();
    $estadisticas = $accesosService->getEstadisticasGeneral();
    
} catch (Exception $e) {
    header('Location: /login.php?error=' . urlencode($e->getMessage()));
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin - Cyberhole</title>
    <?= MiddlewareManager::getCsrfToken()['meta_tag'] ?>
</head>
<body>
    <h1>Bienvenido, <?= htmlspecialchars($user['nombre']) ?></h1>
    
    <div class="stats">
        <h2>Estadísticas</h2>
        <p>Accesos hoy: <?= $estadisticas['data']['accesos_hoy'] ?></p>
    </div>
    
    <div class="condominios">
        <h2>Mis Condominios</h2>
        <?php foreach ($condominios['data'] as $condominio): ?>
            <div class="condominio">
                <h3><?= htmlspecialchars($condominio['nombre']) ?></h3>
                <p><?= htmlspecialchars($condominio['direccion']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    
    <script>
        // Token CSRF disponible para AJAX
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    </script>
</body>
</html>
```

---

## ✅ CHECKLIST DE IMPLEMENTACIÓN

### **🔧 Configuración Inicial**
- [x] SecurityConfig.php configurado
- [x] Todos los middlewares disponibles
- [x] MiddlewareManager funcional
- [x] Estructura de servicios creada

### **📋 Implementación de Servicios**
- [ ] auth_services.php (orquestador)
- [ ] BaseAdminService.php
- [ ] BaseResidentService.php
- [ ] Servicios específicos admin_services/
- [ ] Servicios específicos resident_services/

### **🧪 Testing y Validación**
- [ ] Pruebas de autenticación dual
- [ ] Pruebas de validación de ownership
- [ ] Pruebas de protección CSRF
- [ ] Pruebas de rate limiting
- [ ] Pruebas de integración completa

### **📚 Documentación**
- [x] Especificación de middlewares
- [ ] Guías de implementación de servicios
- [ ] Ejemplos de uso por rol
- [ ] Documentación de APIs

---

## 🚀 BENEFICIOS DE LA INTEGRACIÓN

### **🔒 Seguridad Robusta**
- ✅ Protección automática contra ataques comunes
- ✅ Validación de ownership en todas las operaciones
- ✅ Control de acceso granular por rol y recurso
- ✅ Encriptación AES para datos sensibles

### **🎯 Separación de Responsabilidades**
- ✅ Middlewares manejan toda la seguridad
- ✅ Servicios se enfocan en lógica de negocio
- ✅ Modelos solo manejan acceso a datos
- ✅ Separación total admin/residente

### **⚡ Desarrollo Eficiente**
- ✅ Patrón base reutilizable para servicios
- ✅ Validaciones automáticas por middleware
- ✅ APIs protegidas sin código repetitivo
- ✅ Orquestación centralizada

### **🛠️ Mantenibilidad**
- ✅ Configuración centralizada
- ✅ Código modular y testeable
- ✅ Escalabilidad horizontal
- ✅ Documentación completa

---

**📅 Creado:** 26 de Julio, 2025  
**🔄 Versión:** 1.0 - Integración Completa Middlewares + Servicios  
**✅ Estado:** ESPECIFICACIÓN COMPLETA - Listo para implementación
