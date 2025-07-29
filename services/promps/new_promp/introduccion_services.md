# 📋 PROMPT MAESTRO - CAPA DE SERVICIOS CYBERHOLE CONDOMINIOS

## 🎯 PROPÓSITO DEL DOCUMENTO
Este documento establece las especificaciones completas para la implementación de la **Capa de Servicios** (Segunda Capa) del sistema Cyberhole Condominios, definiendo la arquitectura, responsabilidades, estructura y reglas de negocio para cada tipo de usuario.

---

## 📌 1. CONTEXTO GENERAL

### **🏢 Sistema Dual de Roles**
El sistema Cyberhole Condominios está dividido en **dos roles principales**: 
- **👨‍💼 Administrador** 
- **🏡 Residente** 

Cada uno cuenta con:
- ✅ **Sistema de autenticación completamente separado**
- ✅ **Rutas independientes y diferenciadas**
- ✅ **Gestión exclusiva de elementos que les pertenecen**
- ✅ **Validaciones específicas según relaciones de BD**

### **🏗️ Arquitectura 3 Capas - Capa de Servicios**
La **capa de servicios** constituye la **segunda capa** del sistema:

```
┌─────────────────────────────────────┐
│        CAPA 3: CONTROLADORES       │ ← Presentación y APIs
├─────────────────────────────────────┤
│     CAPA 2: SERVICIOS (AQUÍ)       │ ← Lógica de Negocio
├─────────────────────────────────────┤
│        CAPA 1: MODELOS              │ ← Acceso a Datos
└─────────────────────────────────────┘
```

### **🎯 Responsabilidad Principal**
- **Gestionar la lógica de negocio**
- **Hacer uso de modelos para acceso a datos**
- **Integrar middlewares y validaciones**
- **Asegurar la integridad del sistema**
- **Mantener separación absoluta entre roles**

---

## 🚪 2. INTRODUCCIÓN A LA NUEVA ESTRUCTURA

### **🔄 Reestructuración Completa**
Hemos reestructurado completamente la capa de servicios para lograr:

#### **✅ Separación Total de Responsabilidades:**
- Cada rol tiene **servicios independientes**
- **Sin cruce de información** entre roles
- **Validaciones específicas** por tipo de usuario

#### **🎛️ Orquestación Centralizada:**
- **`auth_services.php`** actúa como orquestador principal
- **Carga condicional** de servicios según usuario autenticado
- **Gestión centralizada** de middlewares

#### **📚 Referencias Documentales:**
Los archivos de versiones anteriores sirven ahora **solo como referencia**:
- `MIDDLEWARES_EMBEBIDOS_BASESERVICE.md`
- `PLAN_INTEGRACION_MIDDLEWARES.md`

Los middlewares se gestionan de manera **centralizada en la nueva estructura**.

---

## 🔐 3. IMPLEMENTACIÓN DE ROLES

### **👨‍💼 SERVICIOS DE ADMINISTRADOR**
```php
// Validación por admin + condominio
$adminId = $_SESSION['admin_id'];
$condominioId = $data['condominio_id'];

// TODA acción debe validar ownership
if (!Condominio::validateAdminOwnership($adminId, $condominioId)) {
    throw new UnauthorizedException('Sin permisos sobre este condominio');
}
```

### **🏡 SERVICIOS DE RESIDENTE**
```php
// Validación por persona + casa
$personaId = $_SESSION['persona_id'];
$casaId = $data['casa_id'];

// TODA acción debe validar ownership
if (!Casa::validateResidentOwnership($personaId, $casaId)) {
    throw new UnauthorizedException('Sin permisos sobre esta propiedad');
}
```

### **🔒 Reglas de Validación**
#### **Para Administradores:**
- Validan por `id_admin` y relación con condominios via `admin_cond`
- Acceso solo a **SUS condominios asignados**
- Control total sobre empleados, residentes y visitantes **de sus condominios**

#### **Para Residentes:**
- Validan por `id_persona` y relación con casas via `persona_casa`
- Acceso solo a **SUS propiedades asignadas**
- Control personal sobre visitantes y accesos **de sus casas**

### **🛡️ Protección por Middlewares Embebidos**
Toda acción debe estar protegida por:
- ✅ **Autenticación** - Usuario válido y sesión activa
- ✅ **Autorización de rol** - Admin o Residente según corresponda
- ✅ **Control de ownership** - Condominio/Casa perteneciente al usuario
- ✅ **CSRF Protection** - Validación de tokens de seguridad
- ✅ **Rate Limiting** - Prevención de abuso y ataques

---

## 📛 4. REGLAS FUNDAMENTALES DE SERVICIOS

### **✅ LO QUE LOS SERVICIOS DEBEN HACER:**

#### **🔐 Validaciones de Seguridad:**
- Validar autenticación y rol correctamente
- Verificar que toda acción pertenezca al usuario autenticado
- Aplicar validaciones de ownership en cada operación

#### **📊 Gestión de Datos:**
- Usar los modelos **únicamente** como acceso a la base de datos
- Emitir respuestas limpias, seguras y estructuradas (JSON/render)
- Delegar validaciones pesadas a middlewares embebidos

#### **🏗️ Arquitectura:**
- Mantener lógica de negocio en la capa de servicios
- Seguir principios de separación de responsabilidades
- Documentar y estructurar código claramente

### **🚫 LO QUE LOS SERVICIOS NO DEBEN HACER:**

#### **❌ Violaciones de Arquitectura:**
- **NO** contener lógica de rutas (eso es Capa 3)
- **NO** saltarse middlewares (aunque se llamen internamente)
- **NO** manipular la sesión directamente
- **NO** mezclarse entre servicios de roles distintos

#### **❌ Duplicación de Esfuerzos:**
- **NO** repetir validaciones ya cubiertas por middleware
- **NO** reimplementar funcionalidades de modelos
- **NO** duplicar lógica entre servicios similares

---

## �️ 5.1 BASESERVICE.PHP - CLASE BASE CENTRALIZADA

### **🎯 Propósito de BaseService.php**
Archivo central ubicado en `services/BaseService.php` que sirve como **superclase** para todos los servicios del sistema. Su responsabilidad es centralizar la invocación de middlewares y proporcionar métodos reutilizables para mantener la arquitectura desacoplada y segura.

### **🔧 Funcionalidades Clave**

#### **✅ 1. Cargar Dinámicamente los Middlewares Integrados**
```php
// BaseService integra todos los middlewares disponibles
- AuthMiddleware      → Autenticación dual admin/residente
- RoleMiddleware      → Control jerárquico de roles
- OwnershipMiddleware → Validación de ownership casa/condominio
- CSRFProtection      → Protección contra ataques CSRF
- RateLimiterMiddleware → Control de tasa de solicitudes
```

#### **✅ 2. Métodos Protegidos Reutilizables**
```php
protected function checkAuth()                           → Validar autenticación
protected function checkCSRF()                          → Validar token CSRF
protected function checkRole($expectedRole)             → Validar rol específico
protected function checkOwnershipCasa($idCasa)          → Validar propiedad casa
protected function checkOwnershipCondominio($idCondominio) → Validar propiedad condominio
protected function enforceRateLimit($key)               → Aplicar rate limiting
```

#### **✅ 3. Contrato de Buenas Prácticas**
- **NO contiene lógica de negocio** - Solo middleware management
- **NO retorna respuestas al cliente** - Eso es trabajo de servicios específicos
- **NO manipula sesiones complejas** - Delega a helpers especializados
- **SÍ refuerza reglas de acceso** - Punto central de validaciones

### **📋 Implementación de BaseService.php**

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

### **🎯 Uso en Servicios Específicos**

#### **Ejemplo Admin Service:**
```php
<?php
// admin_services/CondominioService.php
require_once __DIR__ . '/../BaseService.php';

class CondominioService extends BaseService {
    
    public function createCondominio(array $data): array {
        try {
            // 1. Validar request completo usando BaseService
            $user = $this->validateAuthAndRole(['ADMIN']);
            
            // 2. Validar campos requeridos usando BaseService
            $this->validateRequiredFields($data, ['nombre', 'direccion']);
            
            // 3. Validar ownership si es necesario
            if (isset($data['condominio_id'])) {
                $this->checkOwnershipCondominio($user['id'], $data['condominio_id']);
            }
            
            // 4. Lógica de negocio específica del servicio
            $condominioId = Condominio::create($data);
            
            // 5. Log usando BaseService
            $this->logServiceActivity('create_condominio', [
                'condominio_id' => $condominioId,
                'nombre' => $data['nombre']
            ], $user);
            
            // 6. Respuesta usando BaseService
            return $this->successResponse(
                ['condominio_id' => $condominioId],
                'Condominio creado exitosamente'
            );
            
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
?>
```

#### **Ejemplo Resident Service:**
```php
<?php
// resident_services/EngomadoService.php
require_once __DIR__ . '/../BaseService.php';

class EngomadoService extends BaseService {
    
    public function createEngomado(array $data): array {
        try {
            // 1. Validar request usando BaseService
            $user = $this->validateAuthAndRole(['ADMIN', 'RESIDENTE']);
            
            // 2. Validar campos requeridos
            $this->validateRequiredFields($data, ['placa', 'casa_id']);
            
            // 3. Validar ownership de casa usando BaseService
            $this->checkOwnershipCasa($user['id'], $data['casa_id']);
            
            // 4. CSRF si es POST usando BaseService
            $this->checkCSRF('POST');
            
            // 5. Lógica de negocio específica
            $engomadoId = Engomado::create($data);
            
            // 6. Log y respuesta usando BaseService
            $this->logServiceActivity('create_engomado', [
                'engomado_id' => $engomadoId,
                'casa_id' => $data['casa_id']
            ], $user);
            
            return $this->successResponse(
                ['engomado_id' => $engomadoId],
                'Engomado creado exitosamente'
            );
            
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
?>
```

### **🔧 Ventajas de BaseService.php**

#### **✅ Centralización de Middlewares**
- Un solo punto de entrada para todas las validaciones
- Métodos reutilizables en todos los servicios
- Consistencia en manejo de errores y respuestas

#### **✅ Desacoplamiento**
- Servicios se enfocan solo en lógica de negocio
- Middlewares manejados de forma transparente
- Fácil mantenimiento y actualización

#### **✅ Arquitectura Coherente**
- Respeta la separación de responsabilidades
- Mantiene la arquitectura 3 capas intacta
- Facilita testing y debugging

#### **✅ Seguridad Centralizada**
- Todas las validaciones pasan por BaseService
- Imposible saltarse middlewares accidentalmente
- Logging centralizado de actividad

---

## �🌳 5.2 ESTRUCTURA COMPLETA DE SERVICIOS (ACTUALIZADA)

### **📂 Directorio Raíz:**
```
services/
│   BaseService.php           ← 🏗️ Clase base - Centraliza middlewares y métodos reutilizables
│   auth_services.php         ← Orquestador principal - Carga servicios según usuario
│
├───admin_services/           ← Servicios exclusivos para administradores
├───resident_services/        ← Servicios exclusivos para residentes
├───Promp_Servicios/         ← Documentación general
├───promps/                  ← Documentación y referencias
└───test/                    ← Pruebas unitarias
```

### **👨‍💼 SERVICIOS ADMINISTRATIVOS:**
```
admin_services/
├───promp_adminservices/              ← Documentación específica
│
├───AccesosService.php                ← 🚪 Gestión de accesos por condominio
│   ├── Accesos de residentes        ← Filtrado por condominio asignado
│   ├── Accesos de empleados         ← Control total de empleados
│   └── Accesos de visitantes        ← Supervisión de visitas
│
├───AdminService.php                  ← 👨‍💼 Gestión de cuenta admin
│   ├── Perfil y configuración       ← Datos personales del admin
│   ├── Condominios asignados        ← Lista de condominios bajo gestión
│   └── Cambio de contraseña         ← Seguridad de cuenta
│
├───AreaComunService.php              ← 🏊 Gestión de áreas comunes
│   ├── Crear/editar áreas           ← Configuración de espacios
│   ├── Ver reservas                 ← Supervisión de reservas
│   └── Gestión de horarios          ← Control de disponibilidad
│
├───BlogService.php                   ← 📝 Gestión de publicaciones
│   ├── Crear publicaciones          ← Anuncios y noticias
│   ├── Editar contenido             ← Modificación de posts
│   └── Control de visibilidad       ← Audiencia objetivo
│
├───CalleService.php                  ← 🛣️ Gestión de calles
│   ├── Alta de calles               ← Crear nuevas calles
│   ├── Edición de información       ← Modificar datos existentes
│   └── Eliminación                  ← Borrado de calles vacías
│
├───CasaService.php                   ← 🏠 Administración de casas
│   ├── Crear/editar casas           ← Gestión de propiedades
│   ├── Asignación de residentes     ← Vinculación persona-casa
│   └── Generación de claves         ← Sistema de registro
│
├───CondominioService.php             ← 🏢 Gestión de condominios
│   ├── Crear condominios            ← Solo SUS condominios
│   ├── Editar información           ← Datos básicos
│   └── Configuración general        ← Parámetros del condominio
│
├───EmpleadoService.php               ← 👷 Gestión de empleados
│   ├── Alta de empleados            ← Registro con encriptación AES
│   ├── Asignación de tareas         ← Distribución de trabajo
│   ├── Control de accesos           ← Códigos físicos únicos
│   └── Activar/desactivar           ← Estado laboral
│
├───EngomadoService.php               ← 🚗 Gestión de engomados
│   ├── Crear engomados              ← Para residentes del condominio
│   ├── Editar información           ← Modificar datos vehiculares
│   └── Activar/desactivar           ← Control de estado
│
├───TagService.php                    ← 🏷️ Gestión de tags RFID/NFC
│   ├── Crear tags                   ← Para residentes del condominio
│   ├── Asignar a residentes         ← Vinculación con personas
│   └── Activar/desactivar           ← Control de acceso
│
├───DispositivoService.php            ← 📱 Gestión de dispositivos
│   ├── Asociar dispositivos         ← A residentes del condominio
│   ├── Ver dispositivos activos     ← Lista de equipos
│   └── Gestión de permisos          ← Control de acceso
│
├───MisCasasService.php               ← 🏠 Vista de casas del condominio
│   ├── Listado de casas             ← Todas las del condominio
│   ├── Residentes por casa          ← Ocupación actual
│   └── Gestión de claves            ← Sistema de registro
│
├───PersonaUnidadService.php          ← 👥 Gestión de unidades adicionales
│   ├── Crear unidades               ← Datos extendidos de personas
│   ├── Asociar dispositivos         ← Vinculación con equipos
│   └── Gestión de información       ← Datos complementarios
│
└───PersonaCasaService.php            ← 🔗 Gestión de relaciones
    ├── Asignar persona a casa       ← Crear vinculación
    ├── Eliminar asignación          ← Romper relación
    └── Ver relaciones activas       ← Estado actual
```

### **🏡 SERVICIOS DE RESIDENTES:**
```
resident_services/
├───promp_residentservices/           ← Documentación específica
│
├───AccesosService.php                ← 🚪 Ver accesos personales
│   ├── Mis accesos                  ← Historial personal
│   ├── Visitantes a mi casa         ← Solo MI propiedad
│   └── Dispositivos usados          ← Tags/engomados propios
│
├───BlogService.php                   ← 📖 Lectura de publicaciones
│   ├── Ver posts del condominio     ← Solo lectura
│   ├── Filtrar por relevancia       ← Contenido dirigido
│   └── Marcar como leído            ← Seguimiento personal
│
├───EngomadoService.php               ← 🚗 Gestión de mis engomados
│   ├── Crear mis engomados          ← Solo MIS vehículos
│   ├── Editar información           ← Modificar MIS datos
│   └── Activar/desactivar           ← Control personal
│
├───TagService.php                    ← 🏷️ Gestión de mis tags
│   ├── Crear mis tags               ← Solo MIS identificadores
│   ├── Editar información           ← Modificar MIS datos
│   └── Activar/desactivar           ← Control personal
│
├───DispositivoService.php            ← 📱 Mis dispositivos
│   ├── Asociar a mi cuenta          ← Solo MI perfil
│   ├── Ver mis dispositivos         ← Lista personal
│   └── Gestión de permisos          ← Configuración propia
│
├───PersonaUnidadService.php          ← 👤 Mi información extendida
│   ├── Gestionar mi unidad          ← Datos adicionales propios
│   ├── Actualizar información       ← Modificar MI perfil
│   └── Asociar dispositivos         ← Vinculación personal
│
├───AreaComunService.php              ← 🏊 Mis reservas de áreas
│   ├── Hacer reservas               ← Solo para MIS casas
│   ├── Ver mis reservas             ← Historial personal
│   └── Cancelar reservas            ← Gestión propia
│
├───MisCasasService.php               ← 🏠 Gestión de mis propiedades
│   ├── Ver mis casas                ← Solo MIS propiedades
│   ├── Canjear claves               ← Registro en nuevas casas
│   └── Desvincular casa             ← Salir de propiedad
│
└───PersonaCasaService.php            ← 🔗 Mis relaciones con casas
    ├── Confirmar relación           ← Aceptar vinculación
    ├── Solicitar desvinculación     ← Romper relación
    └── Ver estado de relaciones     ← Mi situación actual
```

### **📚 DOCUMENTACIÓN Y REFERENCIAS:**
```
promps/
├───apis_model_folder/               ← Diagramas UML y modelos
├───inventarios/                     ← Entidades clave del sistema
├───new_promp/                       ← Guías modernas de estructura
└───promps_version_anterior/         ← Documentos históricos

test/                                ← Pruebas unitarias (no afectan servicios)
```

---

## 🎯 6. IMPLEMENTACIÓN Y FLUJO DE TRABAJO

### **🔄 Flujo de Ejecución:**
```
1. Usuario hace request
2. Middleware valida autenticación/autorización
3. auth_services.php carga servicios según rol
4. Servicio específico procesa lógica de negocio
5. Servicio usa modelos para acceso a datos
6. Respuesta estructurada al usuario
```

### **🛡️ Validaciones por Capa:**
```
MIDDLEWARE → Autenticación, CSRF, Rate Limit
SERVICIO  → Ownership, Lógica de Negocio, Validaciones Específicas
MODELO    → Integridad de Datos, Sanitización, CRUD
```

### **📊 Estructura de Respuestas:**
```php
// Éxito
return [
    'success' => true,
    'data' => $resultado,
    'message' => 'Operación completada exitosamente'
];

// Error
return [
    'success' => false,
    'error' => $mensajeError,
    'code' => $codigoError
];
```

---

## 🚀 7. PRÓXIMOS PASOS

### **📋 Orden de Implementación:**
1. **BaseService.php** - Clase base centralizada con middlewares integrados
2. **auth_services.php** - Orquestador principal
3. **Servicios básicos** - Admin/Residente core heredando de BaseService
4. **Servicios de accesos** - Control diferenciado
5. **Servicios especializados** - Funcionalidades específicas
6. **Testing y validación** - Pruebas unitarias

### **🔧 Herramientas de Desarrollo:**
- Seguir documentación de `new_diagram_model.md`
- Usar especificaciones de `Relacion_Tablas.md`
- Implementar encriptación AES según `EMPLEADO_MODELO_ACTUALIZADO.md`
- Aplicar control de accesos según `ADICIONES_MODELO_ACCESOS.md`

---

**📅 Creado:** 26 de Julio, 2025  
**🔄 Versión:** 1.0 - Prompt Maestro para Capa de Servicios  
**✅ Estado:** ESPECIFICACIÓN COMPLETA - Listo para implementación de servicios
