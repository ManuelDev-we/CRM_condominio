# 🚀 PLAN DE INTEGRACIÓN - NUEVA ARQUITECTURA CYBERHOLE

## 🎯 PROPÓSITO DEL DOCUMENTO
Plan de implementación paso a paso para integrar los **middlewares existentes** con la **nueva arquitectura de servicios dual** del sistema Cyberhole Condominios. Basado en las especificaciones del **prompt maestro** `introduccion_services.md` y la compatibilidad confirmada en `MIDDLEWARES_INTEGRADOS_NUEVA_ESTRUCTURA.md`.

---

## 📋 CONTEXTO Y REFERENCIAS

### **📚 Documentos Base**
- ✅ **`introduccion_services.md`** - Prompt maestro para capa de servicios
- ✅ **`new_diagram_model.md`** - Diagrama UML con control diferenciado
- ✅ **`Relacion_Tablas.md`** - Mapeo modelo-tabla con AES
- ✅ **`MIDDLEWARES_INTEGRADOS_NUEVA_ESTRUCTURA.md`** - Especificación de integración

### **🔍 Compatibilidad Confirmada**
- 🟢 **AuthMiddleware.php** (314 líneas) - Sistema dual admin/residente ✅
- 🟢 **RoleMiddleware.php** (311 líneas) - Jerarquía ADMIN > RESIDENTE > EMPLEADO ✅
- 🟢 **CondominioOwnershipMiddleware.php** (406 líneas) - Validación ownership ✅
- 🟢 **CsrfMiddleware.php** (341 líneas) - Protección CSRF ✅
- 🟢 **RateLimitMiddleware.php** (403 líneas) - Control de tasa ✅
- 🟢 **MiddlewareManager.php** (326 líneas) - Orquestador central ✅

---

## 🏗️ FASE 1: CONFIGURACIÓN INICIAL

### **🔧 1.1 Validar Infraestructura Middleware**
```bash
# Verificar que todos los middlewares estén funcionales
php test_middlewares_completo_integrado.php
```

**Archivos a verificar:**
- ✅ `config/SecurityConfig.php` - Configuración centralizada
- ✅ `middlewares/AuthMiddleware.php` - Autenticación dual
- ✅ `middlewares/RoleMiddleware.php` - Control de roles
- ✅ `middlewares/CondominioOwnershipMiddleware.php` - Ownership
- ✅ `middlewares/CsrfMiddleware.php` - Protección CSRF
- ✅ `middlewares/RateLimitMiddleware.php` - Rate limiting
- ✅ `middlewares/MiddlewareManager.php` - Orquestador

### **📂 1.2 Crear Estructura de Servicios**
```bash
# Crear directorios según prompt maestro
mkdir -p services/admin_services/promp_adminservices
mkdir -p services/resident_services/promp_residentservices
```

**Estructura objetivo:**
```
services/
│   BaseService.php               ← 🏗️ Clase base centralizada (CREAR PRIMERO)
│   auth_services.php             ← Orquestador principal (CREAR)
│
├───admin_services/               ← Servicios administrativos
│   ├───promp_adminservices/     ← Documentación específica
│   ├───BaseAdminService.php     ← Clase base admin (CREAR - hereda de BaseService)
│   ├───AccesosService.php       ← (CREAR)
│   ├───AdminService.php         ← (CREAR)
│   ├───AreaComunService.php     ← (CREAR)
│   ├───BlogService.php          ← (CREAR)
│   ├───CalleService.php         ← (CREAR)
│   ├───CasaService.php          ← (CREAR)
│   ├───CondominioService.php    ← (CREAR)
│   ├───EmpleadoService.php      ← (CREAR)
│   ├───EngomadoService.php      ← (CREAR)
│   ├───TagService.php           ← (CREAR)
│   ├───DispositivoService.php   ← (CREAR)
│   ├───MisCasasService.php      ← (CREAR)
│   ├───PersonaUnidadService.php ← (CREAR)
│   └───PersonaCasaService.php   ← (CREAR)
│
└───resident_services/           ← Servicios de residentes
    ├───promp_residentservices/  ← Documentación específica
    ├───BaseResidentService.php  ← Clase base residente (CREAR - hereda de BaseService)
    ├───AccesosService.php       ← (CREAR)
    ├───BlogService.php          ← (CREAR)
    ├───EngomadoService.php      ← (CREAR)
    ├───TagService.php           ← (CREAR)
    ├───DispositivoService.php   ← (CREAR)
    ├───PersonaUnidadService.php ← (CREAR)
    ├───AreaComunService.php     ← (CREAR)
    ├───MisCasasService.php      ← (CREAR)
    └───PersonaCasaService.php   ← (CREAR)
```

---

## 🏗️ FASE 1.5: IMPLEMENTACIÓN DE BASESERVICE.PHP

### **🎯 1.5.1 Crear BaseService.php - Clase Base Centralizada**

**Archivo:** `services/BaseService.php`

Esta clase es **FUNDAMENTAL** y debe ser creada **ANTES** que cualquier otro servicio, ya que todos los servicios específicos heredarán de ella.

#### **🔧 Funcionalidades de BaseService.php**

1. **Centralizar invocación de middlewares**
2. **Proporcionar métodos protegidos reutilizables**
3. **Establecer contrato de buenas prácticas**
4. **Servir como superclase para todos los servicios**

#### **📋 Implementación Completa**

```php
<?php
/**
 * BaseService - Clase Base Centralizada para Servicios
 * Sistema Cyberhole Condominios
 * 
 * Centraliza la invocación de middlewares y proporciona métodos reutilizables
 * para todos los servicios del sistema. Mantiene arquitectura desacoplada.
 * 
 * @version 1.0.0
 * @author Sistema Cyberhole
 */

require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../middlewares/RoleMiddleware.php';
require_once __DIR__ . '/../middlewares/CondominioOwnershipMiddleware.php';
require_once __DIR__ . '/../middlewares/CsrfMiddleware.php';
require_once __DIR__ . '/../middlewares/RateLimitMiddleware.php';
require_once __DIR__ . '/../middlewares/MiddlewareManager.php';

abstract class BaseService {
    
    /**
     * Validar autenticación del usuario
     * 
     * @param string $route Ruta actual (opcional)
     * @return array Datos del usuario autenticado
     */
    protected function checkAuth(string $route = ''): array {
        $result = AuthMiddleware::check($route);
        
        if (!$result['success']) {
            throw new UnauthorizedException($result['message']);
        }
        
        return $result['user'];
    }
    
    /**
     * Validar token CSRF para operaciones de modificación
     * 
     * @param string $method Método HTTP
     * @param string $route Ruta actual
     * @return bool True si es válido
     */
    protected function checkCSRF(string $method = '', string $route = ''): bool {
        $result = CsrfMiddleware::check($method, $route);
        
        if (!$result['success']) {
            throw new SecurityException($result['message']);
        }
        
        return true;
    }
    
    /**
     * Validar rol específico del usuario
     * 
     * @param array $user Datos del usuario
     * @param string|array $expectedRoles Rol(es) permitido(s)
     * @param string $route Ruta actual
     * @return bool True si tiene el rol
     */
    protected function checkRole(array $user, $expectedRoles, string $route = ''): bool {
        $result = RoleMiddleware::check($user, (array)$expectedRoles, $route);
        
        if (!$result['success']) {
            throw new UnauthorizedException($result['message']);
        }
        
        return true;
    }
    
    /**
     * Validar ownership de casa para residentes
     * 
     * @param int $personaId ID de la persona
     * @param int $casaId ID de la casa
     * @return bool True si tiene acceso
     */
    protected function checkOwnershipCasa(int $personaId, int $casaId): bool {
        require_once __DIR__ . '/../models/Casa.php';
        
        if (!Casa::validateResidentOwnership($personaId, $casaId)) {
            throw new UnauthorizedException("Sin permisos sobre la casa ID: {$casaId}");
        }
        
        return true;
    }
    
    /**
     * Validar ownership de condominio para admins
     * 
     * @param int $adminId ID del administrador
     * @param int $condominioId ID del condominio
     * @return bool True si tiene acceso
     */
    protected function checkOwnershipCondominio(int $adminId, int $condominioId): bool {
        require_once __DIR__ . '/../models/Condominio.php';
        
        if (!Condominio::validateAdminOwnership($adminId, $condominioId)) {
            throw new UnauthorizedException("Sin permisos sobre el condominio ID: {$condominioId}");
        }
        
        return true;
    }
    
    /**
     * Validar ownership usando middleware centralizado
     * 
     * @param array $user Datos del usuario
     * @param int|null $condominioId ID del condominio
     * @param string $route Ruta actual
     * @param array $requestData Datos de la solicitud
     * @return bool True si tiene acceso
     */
    protected function checkOwnership(array $user, ?int $condominioId, string $route = '', array $requestData = []): bool {
        $result = CondominioOwnershipMiddleware::check($user, $condominioId, $route, $requestData);
        
        if (!$result['success']) {
            throw new UnauthorizedException($result['message']);
        }
        
        return true;
    }
    
    /**
     * Aplicar rate limiting
     * 
     * @param string $identifier Identificador único
     * @param string $route Ruta actual
     * @param string $type Tipo de límite
     * @return bool True si está dentro del límite
     */
    protected function enforceRateLimit(string $identifier, string $route = '', string $type = 'general'): bool {
        $result = RateLimitMiddleware::check($identifier, $route, $type);
        
        if (!$result['success']) {
            throw new RateLimitException($result['message']);
        }
        
        return true;
    }
    
    /**
     * Pipeline completo de validaciones (método de conveniencia)
     * 
     * @param string $route Ruta solicitada
     * @param string $method Método HTTP
     * @param array $requiredRoles Roles requeridos
     * @param int|null $condominioId ID del condominio
     * @param array $requestData Datos de la solicitud
     * @return array Usuario validado
     */
    protected function validateRequest(
        string $route = '',
        string $method = 'GET',
        array $requiredRoles = [],
        ?int $condominioId = null,
        array $requestData = []
    ): array {
        
        // Usar MiddlewareManager para pipeline completo
        return MiddlewareManager::execute($route, $method, $requiredRoles, $condominioId, $requestData);
    }
    
    /**
     * Validar solo autenticación y rol (caso común)
     * 
     * @param string|array $requiredRoles Rol(es) requerido(s)
     * @param string $route Ruta actual
     * @return array Usuario validado
     */
    protected function validateAuthAndRole($requiredRoles, string $route = ''): array {
        return MiddlewareManager::authAndRole($requiredRoles, $route);
    }
    
    /**
     * Formatear respuesta estándar de éxito
     * 
     * @param mixed $data Datos a retornar
     * @param string $message Mensaje de éxito
     * @return array Respuesta estructurada
     */
    protected function successResponse($data = null, string $message = 'Operación exitosa'): array {
        return [
            'success' => true,
            'data' => $data,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Formatear respuesta estándar de error
     * 
     * @param string $message Mensaje de error
     * @param int $code Código de error
     * @param mixed $details Detalles adicionales
     * @return array Respuesta estructurada
     */
    protected function errorResponse(string $message, int $code = 400, $details = null): array {
        return [
            'success' => false,
            'error' => $message,
            'code' => $code,
            'details' => $details,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Validar campos requeridos en datos de entrada
     * 
     * @param array $data Datos a validar
     * @param array $required Campos requeridos
     * @throws InvalidArgumentException Si falta algún campo
     */
    protected function validateRequiredFields(array $data, array $required): void {
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new InvalidArgumentException("Campo requerido faltante: {$field}");
            }
        }
    }
    
    /**
     * Log de actividad del servicio
     * 
     * @param string $action Acción realizada
     * @param array $context Contexto adicional
     * @param array $user Datos del usuario
     */
    protected function logServiceActivity(string $action, array $context = [], array $user = []): void {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'service' => get_class($this),
            'action' => $action,
            'user_id' => $user['id'] ?? 'unknown',
            'user_type' => $user['type'] ?? 'unknown',
            'context' => $context,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        error_log("Service Activity: " . json_encode($logData), 3, __DIR__ . '/../logs/service_activity.log');
    }
}

// Excepciones personalizadas para BaseService
class UnauthorizedException extends Exception {
    public function __construct($message = "No autorizado", $code = 403, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

class SecurityException extends Exception {
    public function __construct($message = "Violación de seguridad", $code = 419, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

class RateLimitException extends Exception {
    public function __construct($message = "Límite de tasa excedido", $code = 429, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
?>
```

#### **🎯 Ventajas de BaseService.php**

- ✅ **Centralización**: Todos los middlewares en un solo lugar
- ✅ **Reutilización**: Métodos comunes disponibles para todos los servicios
- ✅ **Consistencia**: Mismo formato de respuestas y manejo de errores
- ✅ **Mantenibilidad**: Cambios en un solo archivo afectan todo el sistema
- ✅ **Seguridad**: Imposible saltarse middlewares accidentalmente

#### **🔒 Contrato de BaseService**

**✅ LO QUE SÍ HACE:**
- Centralizar invocación de middlewares
- Proporcionar métodos reutilizables
- Formatear respuestas estándar
- Manejar excepciones de seguridad
- Logging centralizado

**❌ LO QUE NO HACE:**
- Contener lógica de negocio específica
- Retornar respuestas HTTP directamente
- Manipular sesiones complejas
- Acceder directamente a base de datos

---

## 🚀 FASE 2: IMPLEMENTACIÓN DEL ORQUESTADOR

### **⚡ 2.1 Crear auth_services.php**

**Archivo:** `services/auth_services.php`

```php
<?php
/**
 * AuthServiceOrchestrator - Orquestador Principal de Servicios
 * Sistema Cyberhole Condominios
 * 
 * Carga servicios específicos según el tipo de usuario autenticado.
 * Integra middlewares de forma transparente con la capa de servicios.
 * 
 * @version 1.0.0
 * @author Sistema Cyberhole
 */

require_once __DIR__ . '/../middlewares/MiddlewareManager.php';

class AuthServiceOrchestrator {
    
    private static $loadedServices = [];
    
    /**
     * Cargar servicios según usuario autenticado
     * 
     * @param string $route Ruta solicitada
     * @param string $method Método HTTP
     * @param array $requiredRoles Roles requeridos
     * @param int|null $condominioId ID del condominio para ownership
     * @param array $requestData Datos de la solicitud
     * @return array Container con usuario y servicios disponibles
     */
    public static function loadUserServices(
        string $route = '',
        string $method = 'GET',
        array $requiredRoles = [],
        ?int $condominioId = null,
        array $requestData = []
    ): array {
        
        try {
            // 1. Ejecutar pipeline completo de middlewares
            $user = MiddlewareManager::execute($route, $method, $requiredRoles, $condominioId, $requestData);
            
            // 2. Cargar servicios según tipo de usuario
            $serviceContainer = self::createServiceContainer($user);
            
            // 3. Registrar actividad de usuario
            self::logUserActivity($user, $route, $method);
            
            return $serviceContainer;
            
        } catch (Exception $e) {
            error_log("AuthServiceOrchestrator Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Crear container de servicios según tipo de usuario
     */
    private static function createServiceContainer(array $user): array {
        switch ($user['type']) {
            case 'ADMIN':
                return self::loadAdminServices($user);
            case 'RESIDENTE':
                return self::loadResidentServices($user);
            case 'EMPLEADO':
                return self::loadEmployeeServices($user);
            default:
                throw new UnauthorizedException('Tipo de usuario no válido: ' . $user['type']);
        }
    }
    
    /**
     * Cargar servicios administrativos
     */
    private static function loadAdminServices(array $user): array {
        // Cargar servicios de admin_services/
        self::requireAdminServices();
        
        return [
            'user' => $user,
            'services_type' => 'admin',
            'available_services' => [
                'AccesosService', 'AdminService', 'AreaComunService',
                'BlogService', 'CalleService', 'CasaService',
                'CondominioService', 'EmpleadoService', 'EngomadoService',
                'TagService', 'DispositivoService', 'MisCasasService',
                'PersonaUnidadService', 'PersonaCasaService'
            ],
            'permissions' => ['*'], // Admin tiene acceso total
            'condominio_filter' => true, // Filtrado por condominio asignado
            'ownership_level' => 'condominio'
        ];
    }
    
    /**
     * Cargar servicios de residentes
     */
    private static function loadResidentServices(array $user): array {
        // Cargar servicios de resident_services/
        self::requireResidentServices();
        
        return [
            'user' => $user,
            'services_type' => 'resident',
            'available_services' => [
                'AccesosService', 'BlogService', 'EngomadoService',
                'TagService', 'DispositivoService', 'PersonaUnidadService',
                'AreaComunService', 'MisCasasService', 'PersonaCasaService'
            ],
            'permissions' => [
                'casa' => ['create', 'read', 'update', 'delete'],
                'vehiculo' => ['create', 'read', 'update', 'delete'],
                'persona' => ['read', 'update'],
                'acceso' => ['read']
            ],
            'casa_filter' => true, // Filtrado por casa asignada
            'ownership_level' => 'casa'
        ];
    }
    
    /**
     * Cargar servicios de empleados
     */
    private static function loadEmployeeServices(array $user): array {
        // Los empleados usan algunos servicios admin con permisos limitados
        self::requireEmployeeServices();
        
        return [
            'user' => $user,
            'services_type' => 'employee',
            'available_services' => [
                'AccesosService', 'EngomadoService', 'TagService', 'DispositivoService'
            ],
            'permissions' => [
                'acceso' => ['create', 'read', 'update', 'delete'],
                'vehiculo' => ['read'],
                'persona' => ['read']
            ],
            'condominio_filter' => true, // Filtrado por condominio asignado
            'ownership_level' => 'condominio'
        ];
    }
    
    /**
     * Cargar archivos de servicios administrativos
     */
    private static function requireAdminServices(): void {
        if (isset(self::$loadedServices['admin'])) {
            return; // Ya cargados
        }
        
        $adminServicesPath = __DIR__ . '/admin_services/';
        
        require_once $adminServicesPath . 'BaseAdminService.php';
        require_once $adminServicesPath . 'AccesosService.php';
        require_once $adminServicesPath . 'AdminService.php';
        require_once $adminServicesPath . 'AreaComunService.php';
        require_once $adminServicesPath . 'BlogService.php';
        require_once $adminServicesPath . 'CalleService.php';
        require_once $adminServicesPath . 'CasaService.php';
        require_once $adminServicesPath . 'CondominioService.php';
        require_once $adminServicesPath . 'EmpleadoService.php';
        require_once $adminServicesPath . 'EngomadoService.php';
        require_once $adminServicesPath . 'TagService.php';
        require_once $adminServicesPath . 'DispositivoService.php';
        require_once $adminServicesPath . 'MisCasasService.php';
        require_once $adminServicesPath . 'PersonaUnidadService.php';
        require_once $adminServicesPath . 'PersonaCasaService.php';
        
        self::$loadedServices['admin'] = true;
    }
    
    /**
     * Cargar archivos de servicios de residentes
     */
    private static function requireResidentServices(): void {
        if (isset(self::$loadedServices['resident'])) {
            return; // Ya cargados
        }
        
        $residentServicesPath = __DIR__ . '/resident_services/';
        
        require_once $residentServicesPath . 'BaseResidentService.php';
        require_once $residentServicesPath . 'AccesosService.php';
        require_once $residentServicesPath . 'BlogService.php';
        require_once $residentServicesPath . 'EngomadoService.php';
        require_once $residentServicesPath . 'TagService.php';
        require_once $residentServicesPath . 'DispositivoService.php';
        require_once $residentServicesPath . 'PersonaUnidadService.php';
        require_once $residentServicesPath . 'AreaComunService.php';
        require_once $residentServicesPath . 'MisCasasService.php';
        require_once $residentServicesPath . 'PersonaCasaService.php';
        
        self::$loadedServices['resident'] = true;
    }
    
    /**
     * Cargar servicios para empleados (subconjunto de admin)
     */
    private static function requireEmployeeServices(): void {
        if (isset(self::$loadedServices['employee'])) {
            return; // Ya cargados
        }
        
        $adminServicesPath = __DIR__ . '/admin_services/';
        
        require_once $adminServicesPath . 'BaseAdminService.php';
        require_once $adminServicesPath . 'AccesosService.php';
        require_once $adminServicesPath . 'EngomadoService.php';
        require_once $adminServicesPath . 'TagService.php';
        require_once $adminServicesPath . 'DispositivoService.php';
        
        self::$loadedServices['employee'] = true;
    }
    
    /**
     * Registrar actividad del usuario para auditoría
     */
    private static function logUserActivity(array $user, string $route, string $method): void {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $user['id'],
            'user_type' => $user['type'],
            'route' => $route,
            'method' => $method,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        // Log en archivo para auditoría
        error_log("User Activity: " . json_encode($logData), 3, __DIR__ . '/../logs/user_activity.log');
    }
    
    /**
     * Método de conveniencia para admin
     */
    public static function requireAdmin(string $route = '', string $method = 'GET'): array {
        return self::loadUserServices($route, $method, ['ADMIN']);
    }
    
    /**
     * Método de conveniencia para residente
     */
    public static function requireResident(string $route = '', string $method = 'GET', ?int $casaId = null): array {
        return self::loadUserServices($route, $method, ['ADMIN', 'RESIDENTE'], $casaId);
    }
    
    /**
     * Método de conveniencia para empleado
     */
    public static function requireEmployee(string $route = '', string $method = 'GET', ?int $condominioId = null): array {
        return self::loadUserServices($route, $method, ['ADMIN', 'EMPLEADO'], $condominioId);
    }
}

// Excepción personalizada
class UnauthorizedException extends Exception {
    public function __construct($message = "No autorizado", $code = 403, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
?>
```

---

## 🛡️ FASE 3: CLASES BASE DE SERVICIOS

### **👨‍💼 3.1 BaseAdminService.php**

**Archivo:** `services/admin_services/BaseAdminService.php`

```php
<?php
/**
 * BaseAdminService - Clase Base para Servicios Administrativos
 * Sistema Cyberhole Condominios
 * 
 * Extiende BaseService añadiendo funcionalidad específica para administradores
 * como validaciones de condominio y métodos de utilidad.
 * 
 * @version 1.0.0
 * @author Sistema Cyberhole
 */

require_once __DIR__ . '/../BaseService.php';
require_once __DIR__ . '/../../models/Condominio.php';

abstract class BaseAdminService extends BaseService {
    
    protected $user;
    protected $condominiosAsignados;
    
    /**
     * Constructor - Valida automáticamente que sea admin
     */
    public function __construct() {
        // Validación automática usando BaseService
        $this->user = $this->validateAuthAndRole(['ADMIN']);
        
        // Cargar condominios asignados
        $this->condominiosAsignados = $this->getCondominiosAsignados();
    }
    
    /**
     * Validar que el admin tenga acceso al condominio usando BaseService
     */
    protected function validateCondominioOwnership(int $condominioId): void {
        $this->checkOwnershipCondominio($this->user['id'], $condominioId);
    }
    
    /**
     * Obtener condominios asignados al admin actual
     */
    protected function getCondominiosAsignados(): array {
        return Condominio::getCondominiosByAdmin($this->user['id']);
    }
    
    /**
     * Aplicar filtro de condominio a query
     */
    protected function applyCondominioFilter(string $baseQuery, array $params = []): array {
        $condominioIds = array_column($this->condominiosAsignados, 'id_condominio');
        
        if (empty($condominioIds)) {
            // Admin sin condominios asignados - sin acceso
            return [$baseQuery . " AND 1 = 0", $params];
        }
        
        $placeholders = str_repeat('?,', count($condominioIds) - 1) . '?';
        $filteredQuery = $baseQuery . " AND id_condominio IN ({$placeholders})";
        
        return [$filteredQuery, array_merge($params, $condominioIds)];
    }
    
    /**
     * Log de actividad administrativa usando BaseService
     */
    protected function logAdminActivity(string $action, array $context = []): void {
        $this->logServiceActivity($action, $context, $this->user);
    }
    
    /**
     * Obtener información del admin actual
     */
    protected function getCurrentAdmin(): array {
        return $this->user;
    }
    
    /**
     * Verificar si puede acceder a un condominio específico
     */
    protected function canAccessCondominio(int $condominioId): bool {
        return in_array($condominioId, array_column($this->condominiosAsignados, 'id_condominio'));
    }
}
?>
```

### **🏠 3.2 BaseResidentService.php**

**Archivo:** `services/resident_services/BaseResidentService.php`

```php
<?php
/**
 * BaseResidentService - Clase Base para Servicios de Residentes
 * Sistema Cyberhole Condominios
 * 
 * Extiende BaseService añadiendo funcionalidad específica para residentes
 * como validaciones de casa y métodos de utilidad.
 * 
 * @version 1.0.0
 * @author Sistema Cyberhole
 */

require_once __DIR__ . '/../BaseService.php';
require_once __DIR__ . '/../../models/Casa.php';

abstract class BaseResidentService extends BaseService {
    
    protected $user;
    protected $casasAsignadas;
    
    /**
     * Constructor - Valida automáticamente que sea residente
     */
    public function __construct() {
        // Validación automática usando BaseService
        $this->user = $this->validateAuthAndRole(['ADMIN', 'RESIDENTE']);
        
        // Cargar casas asignadas (solo para residentes, admin tiene acceso total)
        if ($this->user['type'] === 'RESIDENTE') {
            $this->casasAsignadas = $this->getCasasAsignadas();
        } else {
            $this->casasAsignadas = []; // Admin puede acceder a cualquier casa
        }
    }
    
    /**
     * Validar que el residente tenga acceso a la casa usando BaseService
     */
    protected function validateCasaOwnership(int $casaId): void {
        // Los admins pueden acceder a cualquier casa
        if ($this->user['type'] === 'ADMIN') {
            return;
        }
        
        $this->checkOwnershipCasa($this->user['id'], $casaId);
    }
    
    /**
     * Obtener casas asignadas al residente actual
     */
    protected function getCasasAsignadas(): array {
        if ($this->user['type'] === 'ADMIN') {
            return []; // Admin no necesita filtro
        }
        
        return Casa::getCasasByResidente($this->user['id']);
    }
    
    /**
     * Aplicar filtro de casa a query
     */
    protected function applyCasaFilter(string $baseQuery, array $params = []): array {
        // Los admins no necesitan filtro
        if ($this->user['type'] === 'ADMIN') {
            return [$baseQuery, $params];
        }
        
        $casaIds = array_column($this->casasAsignadas, 'id_casa');
        
        if (empty($casaIds)) {
            // Residente sin casas asignadas - sin acceso
            return [$baseQuery . " AND 1 = 0", $params];
        }
        
        $placeholders = str_repeat('?,', count($casaIds) - 1) . '?';
        $filteredQuery = $baseQuery . " AND id_casa IN ({$placeholders})";
        
        return [$filteredQuery, array_merge($params, $casaIds)];
    }
    
    /**
     * Validar que una persona sea el usuario actual (para residentes) usando BaseService
     */
    protected function validatePersonaOwnership(int $personaId): void {
        if ($this->user['type'] === 'ADMIN') {
            return; // Admin puede gestionar cualquier persona
        }
        
        if ($personaId !== $this->user['id']) {
            throw new UnauthorizedException('Solo puedes gestionar tus propios datos');
        }
    }
    
    /**
     * Log de actividad de residente usando BaseService
     */
    protected function logResidentActivity(string $action, array $context = []): void {
        $this->logServiceActivity($action, $context, $this->user);
    }
    
    /**
     * Obtener información del residente actual
     */
    protected function getCurrentResident(): array {
        return $this->user;
    }
    
    /**
     * Verificar si puede acceder a una casa específica
     */
    protected function canAccessCasa(int $casaId): bool {
        if ($this->user['type'] === 'ADMIN') {
            return true;
        }
        
        return in_array($casaId, array_column($this->casasAsignadas, 'id_casa'));
    }
}
?>
```

---

## 📝 FASE 4: IMPLEMENTACIÓN DE SERVICIOS ESPECÍFICOS

### **🎯 4.1 Orden de Implementación**

#### **🔥 Prioridad ALTA (Implementar primero)**
1. **CondominioService** (admin) - Base del sistema
2. **AdminService** (admin) - Gestión de cuenta admin
3. **AccesosService** (admin/resident) - Control de accesos
4. **CasaService** (admin) - Gestión de propiedades
5. **MisCasasService** (resident) - Vista de propiedades del residente

#### **⚡ Prioridad MEDIA (Implementar segundo)**
6. **CalleService** (admin) - Gestión de calles
7. **EngomadoService** (admin/resident) - Gestión vehicular
8. **TagService** (admin/resident) - Gestión de identificadores
9. **EmpleadoService** (admin) - Gestión de personal
10. **PersonaCasaService** (admin/resident) - Relaciones persona-casa

#### **🔧 Prioridad BAJA (Implementar último)**
11. **BlogService** (admin/resident) - Publicaciones
12. **AreaComunService** (admin/resident) - Áreas comunes
13. **DispositivoService** (admin/resident) - Dispositivos
14. **PersonaUnidadService** (admin/resident) - Datos extendidos

### **📋 4.2 Template de Implementación**

**Ejemplo: admin_services/CondominioService.php**

```php
<?php
/**
 * CondominioService - Servicio de Gestión de Condominios
 * Sistema Cyberhole Condominios
 * 
 * Gestiona CRUD de condominios para administradores con validaciones
 * de ownership y middlewares integrados.
 */

require_once __DIR__ . '/BaseAdminService.php';
require_once __DIR__ . '/../../models/Condominio.php';

class CondominioService extends BaseAdminService {
    
    /**
     * Crear nuevo condominio
     */
    public function createCondominio(array $data): array {
        try {
            // Validar campos requeridos
            $this->validateRequiredFields($data, ['nombre', 'direccion']);
            
            // Crear condominio
            $condominioId = Condominio::create($data);
            
            if (!$condominioId) {
                return $this->errorResponse('Error al crear condominio', 500);
            }
            
            // Asignar admin actual al condominio
            Condominio::assignAdminToCondominio($this->user['id'], $condominioId);
            
            // Log de actividad
            $this->logAdminActivity('create_condominio', [
                'condominio_id' => $condominioId,
                'nombre' => $data['nombre']
            ]);
            
            return $this->successResponse(
                ['condominio_id' => $condominioId],
                'Condominio creado exitosamente'
            );
            
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 400);
        }
    }
    
    /**
     * Obtener condominios del admin actual
     */
    public function getMyCondominios(): array {
        try {
            $condominios = $this->getCondominiosAsignados();
            
            return $this->successResponse(
                $condominios,
                'Condominios obtenidos exitosamente'
            );
            
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    
    /**
     * Actualizar condominio
     */
    public function updateCondominio(int $condominioId, array $data): array {
        try {
            // Validar ownership
            $this->validateCondominioOwnership($condominioId);
            
            // Actualizar
            $success = Condominio::update($condominioId, $data);
            
            if (!$success) {
                return $this->errorResponse('Error al actualizar condominio', 500);
            }
            
            // Log de actividad
            $this->logAdminActivity('update_condominio', [
                'condominio_id' => $condominioId,
                'changes' => $data
            ]);
            
            return $this->successResponse(null, 'Condominio actualizado exitosamente');
            
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 400);
        }
    }
    
    /**
     * Eliminar condominio (solo si no tiene casas)
     */
    public function deleteCondominio(int $condominioId): array {
        try {
            // Validar ownership
            $this->validateCondominioOwnership($condominioId);
            
            // Verificar que no tenga casas asociadas
            if (Condominio::hasCasasAsociadas($condominioId)) {
                return $this->errorResponse(
                    'No se puede eliminar condominio con casas asociadas',
                    400
                );
            }
            
            // Eliminar
            $success = Condominio::delete($condominioId);
            
            if (!$success) {
                return $this->errorResponse('Error al eliminar condominio', 500);
            }
            
            // Log de actividad
            $this->logAdminActivity('delete_condominio', [
                'condominio_id' => $condominioId
            ]);
            
            return $this->successResponse(null, 'Condominio eliminado exitosamente');
            
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
?>
```

---

## 🔧 FASE 5: INTEGRACIÓN CON APIs Y FRONTEND

### **🌐 5.1 API REST Endpoints**

**Archivo:** `api/admin/condominios.php`

```php
<?php
/**
 * API REST para Gestión de Condominios - Admin
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../services/auth_services.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

try {
    // Cargar servicios administrativos
    $serviceContainer = AuthServiceOrchestrator::requireAdmin($uri, $method);
    $condominioService = new CondominioService();
    
    switch ($method) {
        case 'GET':
            echo json_encode($condominioService->getMyCondominios());
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($condominioService->createCondominio($data));
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $condominioId = $data['id'] ?? $_GET['id'] ?? null;
            echo json_encode($condominioService->updateCondominio($condominioId, $data));
            break;
            
        case 'DELETE':
            $condominioId = $_GET['id'] ?? null;
            echo json_encode($condominioService->deleteCondominio($condominioId));
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
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

### **📱 5.2 Integración JavaScript**

**Archivo:** `js/admin-condominios.js`

```javascript
/**
 * Gestión de Condominios - Admin Frontend
 */

class CondominioManager {
    constructor() {
        this.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        this.apiBase = '/api/admin/condominios.php';
    }
    
    async getCondominios() {
        try {
            const response = await fetch(this.apiBase);
            const data = await response.json();
            
            if (data.success) {
                this.renderCondominios(data.data);
            } else {
                this.showError(data.error);
            }
        } catch (error) {
            this.showError('Error de conexión: ' + error.message);
        }
    }
    
    async createCondominio(formData) {
        try {
            const response = await fetch(this.apiBase, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(data.message);
                this.getCondominios(); // Recargar lista
            } else {
                this.showError(data.error);
            }
        } catch (error) {
            this.showError('Error de conexión: ' + error.message);
        }
    }
    
    renderCondominios(condominios) {
        const container = document.getElementById('condominios-list');
        container.innerHTML = '';
        
        condominios.forEach(condominio => {
            const div = document.createElement('div');
            div.className = 'condominio-card';
            div.innerHTML = `
                <h3>${condominio.nombre}</h3>
                <p>${condominio.direccion}</p>
                <div class="actions">
                    <button onclick="editCondominio(${condominio.id_condominio})">Editar</button>
                    <button onclick="deleteCondominio(${condominio.id_condominio})">Eliminar</button>
                </div>
            `;
            container.appendChild(div);
        });
    }
    
    showSuccess(message) {
        // Implementar notificación de éxito
        alert('Éxito: ' + message);
    }
    
    showError(message) {
        // Implementar notificación de error
        alert('Error: ' + message);
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.condominioManager = new CondominioManager();
    condominioManager.getCondominios();
});
```

---

## ✅ FASE 6: TESTING Y VALIDACIÓN

### **🧪 6.1 Plan de Pruebas**

```php
<?php
/**
 * Test Suite para Integración de Middlewares + Servicios
 */

// Test 1: Autenticación dual funcional
function testDualAuthentication() {
    // Test admin login
    // Test residente login  
    // Test middleware integration
}

// Test 2: Ownership validation
function testOwnershipValidation() {
    // Test admin condominio filter
    // Test residente casa filter
    // Test unauthorized access blocked
}

// Test 3: CSRF protection
function testCsrfProtection() {
    // Test token generation
    // Test token validation
    // Test blocked requests without token
}

// Test 4: Rate limiting
function testRateLimiting() {
    // Test normal requests allowed
    // Test excessive requests blocked
    // Test different limits by route
}

// Test 5: Service layer integration
function testServiceIntegration() {
    // Test admin service loading
    // Test resident service loading
    // Test proper data filtering
}
?>
```

### **📊 6.2 Métricas de Validación**

- ✅ **Autenticación**: 100% de requests validados
- ✅ **Authorization**: 100% de ownership validado
- ✅ **CSRF**: 100% de modificaciones protegidas
- ✅ **Rate Limiting**: 100% de límites respetados
- ✅ **Data Filtering**: 100% de datos filtrados por ownership

---

## 🎯 FASE 7: DOCUMENTACIÓN Y MANTENIMIENTO

### **📚 7.1 Documentación por Servicio**

Para cada servicio crear:
- Documentación de métodos públicos
- Ejemplos de uso
- Validaciones aplicadas
- Logs de actividad
- Casos de error comunes

### **🔧 7.2 Mantenimiento Continuo**

- Monitor de logs de actividad
- Revisión periódica de permisos
- Actualización de configuraciones de seguridad
- Optimización de queries con filtros
- Backup de configuraciones

---

## 📈 CRONOGRAMA DE IMPLEMENTACIÓN

### **🗓️ Semana 1: Fundación**
- ✅ Validar middlewares existentes
- ✅ Crear estructura de servicios
- ✅ Implementar orquestador auth_services.php
- ✅ Crear clases base

### **🗓️ Semana 2: Servicios Core**
- [ ] CondominioService
- [ ] AdminService  
- [ ] AccesosService (admin)
- [ ] CasaService
- [ ] MisCasasService (resident)

### **🗓️ Semana 3: Servicios Secundarios**
- [ ] CalleService
- [ ] EngomadoService (admin/resident)
- [ ] TagService (admin/resident)
- [ ] EmpleadoService
- [ ] PersonaCasaService

### **🗓️ Semana 4: Servicios Finales y Testing**
- [ ] BlogService
- [ ] AreaComunService
- [ ] DispositivoService
- [ ] PersonaUnidadService
- [ ] Testing completo
- [ ] Documentación final

---

## 🎉 RESULTADOS ESPERADOS

### **🔒 Seguridad Robusta**
- Sistema completamente protegido por middlewares
- Validación automática de ownership
- Protección contra ataques comunes
- Logs completos de actividad

### **🏗️ Arquitectura Limpia**
- Separación total de responsabilidades
- Servicios especializados por rol
- Código mantenible y escalable
- Integración transparente de middlewares

### **⚡ Rendimiento Óptimo**
- Filtrado eficiente por ownership
- Carga condicional de servicios
- Rate limiting preventivo
- Logs optimizados

### **📱 Experiencia de Usuario**
- APIs REST completas y seguras
- Frontend integrado con protecciones
- Mensajes de error claros
- Respuestas estructuradas

---

**📅 Creado:** 26 de Julio, 2025  
**🔄 Versión:** 1.0 - Plan de Integración Completo  
**✅ Estado:** LISTO PARA IMPLEMENTACIÓN - Basado en prompt maestro introduccion_services.md  
**🎯 Objetivo:** Integración completa de middlewares existentes con nueva arquitectura de servicios dual