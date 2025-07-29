# MIDDLEWARES CYBERHOLE - DOCUMENTACIÓN COMPLETA
## Sistema de Gestión de Condominios

### VISIÓN GENERAL
El sistema de middlewares de Cyberhole proporciona una capa de seguridad robusta y escalable para proteger todas las operaciones del sistema de gestión de condominios. Implementa un patrón de middleware en cadena donde cada componente tiene una responsabilidad específica pero trabaja coordinadamente con los demás.

### ARQUITECTURA DEL SISTEMA

```
┌─────────────────────────────────────────────────────────┐
│                    REQUEST FLOW                         │
├─────────────────────────────────────────────────────────┤
│ HTTP Request → AuthMiddleware → RateLimitMiddleware →   │
│ CsrfMiddleware → RoleMiddleware → CondominioOwnership → │
│ Application Logic → Response                            │
└─────────────────────────────────────────────────────────┘
```

### COMPONENTES PRINCIPALES

## 1. AuthMiddleware.php
**Propósito**: Autenticación de usuarios mediante sesiones PHP y tokens JWT.

**Funcionalidades clave**:
- Verificación de sesiones activas
- Validación de tokens JWT
- Gestión de exclusiones de rutas
- Creación y destrucción de sesiones
- Encriptación AES-256-CBC para datos sensibles

**Métodos principales**:
- `execute($route)`: Verificación completa con excepción
- `check($route)`: Verificación suave sin excepción
- `verify($user, $route)`: Validación de usuario específico
- `createSession($user)`: Crear sesión de usuario
- `createJWT($user)`: Generar token JWT
- `logout()`: Cerrar sesión actual

**Configuración**:
```php
// Rutas excluidas (no requieren autenticación)
private static $excludedRoutes = [
    '/login', '/register', '/api/public/*', '/docs/*'
];

// Configuración de encriptación
private static $encryptionMethod = 'AES-256-CBC';
```

**Flujo de autenticación**:
1. Verificar si la ruta está excluida
2. Intentar autenticación por sesión
3. Si falla, intentar autenticación por JWT
4. Si ambas fallan, redirigir al login
5. Devolver datos del usuario autenticado

## 2. RoleMiddleware.php
**Propósito**: Autorización basada en roles con jerarquía de permisos.

**Sistema de roles**:
- **ADMIN**: Acceso completo a todo el sistema
- **RESIDENTE**: Gestión de su propiedad y vehículos
- **EMPLEADO**: Control de accesos y seguridad

**Funcionalidades clave**:
- Verificación jerárquica de roles
- Mapeo de permisos por recurso
- Validación de acciones específicas
- Control granular de operaciones CRUD

**Métodos principales**:
- `execute($user, $roles, $route)`: Verificación con excepción
- `check($user, $roles, $route)`: Verificación suave
- `hasRole($user, $role)`: Verificar rol específico
- `canPerformAction($user, $action, $resource)`: Verificar permiso específico
- `isAdmin($user)`: Verificar si es administrador

**Mapeo de permisos**:
```php
private static $permissions = [
    'ADMIN' => ['*'], // Acceso total
    'RESIDENTE' => ['casa:*', 'vehiculo:*', 'persona:read,update'],
    'EMPLEADO' => ['acceso:*', 'vehiculo:read', 'persona:read']
];
```

## 3. CsrfMiddleware.php
**Propósito**: Protección contra ataques Cross-Site Request Forgery.

**Funcionalidades clave**:
- Generación de tokens únicos y seguros
- Validación automática en métodos POST/PUT/DELETE
- Integración con formularios HTML
- Soporte para AJAX y APIs
- Gestión de expiración de tokens

**Métodos principales**:
- `execute($method, $route)`: Verificación completa
- `check($method, $route)`: Verificación suave
- `generateToken($action)`: Crear token para acción específica
- `verifyToken($token, $action)`: Validar token
- `field($action)`: Campo HTML para formularios
- `metaTag($action)`: Meta tag para AJAX
- `getTokenForJS($action)`: Token para JavaScript

**Uso en formularios**:
```html
<form method="POST" action="/api/casas.php">
    <?= CsrfMiddleware::field('create_casa') ?>
    <input type="text" name="numero" required>
    <input type="submit" value="Crear Casa">
</form>
```

**Uso en AJAX**:
```javascript
// Obtener token
const token = await fetch('/middlewares/csrf_token.php').then(r => r.json());

// Usar en solicitud
formData.append('csrf_token', token.token);
```

## 4. RateLimitMiddleware.php
**Propósito**: Prevención del abuso del sistema mediante limitación de solicitudes.

**Tipos de límites**:
- **Login**: 5 intentos por 5 minutos
- **API**: 100 solicitudes por hora
- **General**: 200 solicitudes por hora
- **Upload**: 20 subidas por hora

**Funcionalidades clave**:
- Identificación inteligente de usuarios (ID, IP, sesión)
- Diferentes políticas por tipo de acción
- Almacenamiento en archivos para persistencia
- Limpieza automática de datos viejos
- Estadísticas de uso

**Métodos principales**:
- `execute($identifier, $route, $type)`: Verificación con excepción
- `check($identifier, $route, $type)`: Verificación suave
- `getIdentifier($user)`: Obtener identificador único
- `reset($identifier, $type)`: Resetear límites
- `getStats($identifier, $type)`: Obtener estadísticas

**Configuración de límites**:
```php
private static $limits = [
    'login' => ['requests' => 5, 'period' => 300],
    'api' => ['requests' => 100, 'period' => 3600],
    'general' => ['requests' => 200, 'period' => 3600]
];
```

## 5. CondominioOwnershipMiddleware.php
**Propósito**: Verificación de propiedad en sistemas multi-tenant.

**Funcionalidades clave**:
- Verificación automática de propiedad de condominio
- Detección inteligente de recursos en URLs y datos
- Consultas SQL optimizadas para verificación
- Soporte para múltiples tipos de recursos
- Bypass para administradores

**Métodos principales**:
- `execute($user, $condominioId, $route, $data)`: Verificación completa
- `check($user, $condominioId, $route, $data)`: Verificación suave
- `verifyByResourceId($user, $resourceType, $resourceId)`: Verificar por ID
- `filterByOwnership($user, $query, $resourceType)`: Filtro SQL

**Recursos soportados**:
- **Casas**: Verificación por propietario_id
- **Vehículos**: Verificación por propietario_id y condominio
- **Personas**: Verificación por condominio_id
- **Accesos**: Verificación por persona_id y condominio
- **Dispositivos**: Verificación por condominio_id

## ARCHIVOS ADICIONALES

### MiddlewareManager.php
Gestor central que proporciona:
- **Pipeline completo**: Ejecuta todos los middlewares en secuencia
- **Verificación suave**: Permite checks sin interrumpir ejecución
- **Métodos de conveniencia**: adminOnly(), residentOnly(), employeeOnly()
- **Gestión de sesión**: getCurrentUser(), logout(), etc.
- **Utilidades CSRF**: csrfField(), getCsrfToken()

### SecurityConfig.php
Configuración centralizada que incluye:
- **Configuración de autenticación**: Sesiones, JWT, encriptación
- **Permisos por rol**: Mapeo detallado de acciones permitidas
- **Rate limiting**: Límites configurables por tipo de acción
- **Protección CSRF**: Configuración de tokens y exclusiones
- **Propiedad de condominio**: Mapeo de recursos y verificaciones
- **Logging y auditoría**: Configuración de logs y alertas

### Ejemplos de Uso
Documentación completa con casos de uso reales:
- **Autenticación básica**: Verificación simple de login
- **APIs protegidas**: CRUD completo con múltiples middlewares
- **Formularios con CSRF**: Protección contra ataques CSRF
- **AJAX seguro**: Integración con JavaScript
- **Configuración avanzada**: Personalización de comportamientos

## ESTRUCTURA FINAL

```
middlewares/
├── AuthMiddleware.php              # Autenticación (sesiones + JWT)
├── RoleMiddleware.php              # Autorización por roles
├── CsrfMiddleware.php              # Protección CSRF
├── RateLimitMiddleware.php         # Limitación de solicitudes
├── CondominioOwnershipMiddleware.php # Verificación de propiedad
├── MiddlewareManager.php           # Gestor central
├── MIDDLEWARES_CYBERHOLE_COMPLETO.md # Documentación técnica
└── EJEMPLOS_USO_MIDDLEWARES.md     # Ejemplos prácticos

config/
└── SecurityConfig.php              # Configuración centralizada
```

## CASOS DE USO COMUNES

### 1. API Protegida Completa
```php
<?php
// api/vehiculos.php
require_once __DIR__ . '/../middlewares/MiddlewareManager.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

try {
    switch ($method) {
        case 'GET':
            $user = MiddlewareManager::execute($uri, 'GET');
            break;
            
        case 'POST':
            $user = MiddlewareManager::execute(
                $uri, 'POST', 
                ['ADMIN', 'RESIDENTE'], 
                $_POST['condominio_id'] ?? null, 
                $_POST
            );
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $user = MiddlewareManager::execute(
                $uri, 'PUT', 
                ['ADMIN', 'RESIDENTE'], 
                $data['condominio_id'] ?? null, 
                $data
            );
            break;
            
        case 'DELETE':
            $user = MiddlewareManager::adminOnly($uri, 'DELETE');
            break;
    }
    
    // Lógica de la API aquí
    echo json_encode(['success' => true, 'data' => $result]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
```

### 2. Página Web con Autenticación
```php
<?php
// dashboard.php
require_once __DIR__ . '/middlewares/MiddlewareManager.php';

try {
    $user = MiddlewareManager::execute(
        '/dashboard',
        'GET',
        ['ADMIN', 'RESIDENTE', 'EMPLEADO']
    );
    
    $canCreateCasa = MiddlewareManager::canCurrentUserPerform('create', 'casa');
    $isAdmin = MiddlewareManager::isCurrentUserAdmin();
    
} catch (Exception $e) {
    // Redirige automáticamente al login
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - <?= htmlspecialchars($user['nombre']) ?></title>
</head>
<body>
    <h1>Bienvenido, <?= htmlspecialchars($user['nombre']) ?></h1>
    
    <?php if ($canCreateCasa): ?>
        <form method="POST" action="/api/casas.php">
            <?= MiddlewareManager::csrfField('create_casa') ?>
            <input type="text" name="numero" required>
            <input type="submit" value="Crear Casa">
        </form>
    <?php endif; ?>
    
    <?php if ($isAdmin): ?>
        <a href="/admin/panel">Panel de Administración</a>
    <?php endif; ?>
</body>
</html>
```

### 3. AJAX con Protección CSRF
```javascript
// Función para realizar solicitudes seguras
async function secureRequest(url, method, data) {
    // Obtener token CSRF
    const csrfResponse = await fetch('/middlewares/csrf_token.php');
    const csrfData = await csrfResponse.json();
    
    // Preparar datos
    const formData = new FormData();
    formData.append('csrf_token', csrfData.token);
    
    if (data) {
        Object.keys(data).forEach(key => {
            formData.append(key, data[key]);
        });
    }
    
    // Realizar solicitud
    const response = await fetch(url, {
        method: method,
        body: formData
    });
    
    return await response.json();
}

// Uso
secureRequest('/api/casas.php', 'POST', {
    numero: '123',
    condominio_id: 1
}).then(result => {
    console.log('Casa creada:', result);
});
```

## INTEGRACIÓN CON EL SISTEMA

### Inicialización en bootstrap.php
```php
<?php
require_once __DIR__ . '/config/SecurityConfig.php';
require_once __DIR__ . '/middlewares/MiddlewareManager.php';

// Inicializar configuración de seguridad
SecurityConfig::initialize();

// Configurar manejo de errores
set_exception_handler(function($e) {
    if ($e instanceof AuthException) {
        header('Location: /login');
        exit;
    } elseif ($e instanceof RoleException) {
        http_response_code(403);
        echo "Acceso denegado";
        exit;
    } else {
        http_response_code(500);
        echo "Error interno del servidor";
        exit;
    }
});
?>
```

### Uso en APIs
```php
<?php
// Patrón estándar para todas las APIs
$user = MiddlewareManager::execute($route, $method, $roles, $condominioId, $data);
?>
```

### Uso en páginas web
```php
<?php
// Patrón estándar para páginas
$user = MiddlewareManager::authAndRole(['ADMIN', 'RESIDENTE']);
?>
```

## CONFIGURACIÓN Y PERSONALIZACIÓN

### Configurar Rutas Excluidas
```php
<?php
// En SecurityConfig.php
'excluded_routes' => [
    '/login',
    '/register',
    '/api/public/*',
    '/docs/*',
    '/assets/*'
]
?>
```

### Personalizar Límites de Rate Limiting
```php
<?php
// En SecurityConfig.php
'limits' => [
    'login' => ['requests' => 5, 'period' => 300],
    'api' => ['requests' => 100, 'period' => 3600],
    'upload' => ['requests' => 20, 'period' => 3600]
]
?>
```

### Configurar Permisos por Rol
```php
<?php
// En SecurityConfig.php
'permissions' => [
    'ADMIN' => ['*'],
    'RESIDENTE' => [
        'casa' => ['create', 'read', 'update', 'delete'],
        'vehiculo' => ['create', 'read', 'update', 'delete'],
        'persona' => ['read', 'update']
    ],
    'EMPLEADO' => [
        'acceso' => ['create', 'read', 'update', 'delete'],
        'vehiculo' => ['read'],
        'persona' => ['read']
    ]
]
?>
```

## LOGGING Y AUDITORÍA

El sistema incluye logging completo de eventos de seguridad:

- **Intentos de autenticación** (exitosos y fallidos)
- **Violaciones de roles** y permisos
- **Ataques CSRF** detectados
- **Límites de rate limiting** excedidos
- **Violaciones de propiedad** de condominio
- **Acciones administrativas** críticas

### Configuración de Logs
```php
<?php
// En SecurityConfig.php
'logging' => [
    'enabled' => true,
    'log_level' => 'INFO',
    'log_path' => __DIR__ . '/../logs/',
    'log_events' => [
        'auth_failure' => true,
        'role_violation' => true,
        'csrf_violation' => true,
        'rate_limit_exceeded' => true
    ]
]
?>
```

## PRUEBAS Y DEBUGGING

### Verificar Estado de Middlewares
```php
<?php
// debug_middlewares.php
$user = MiddlewareManager::getCurrentUser();
$checkResult = MiddlewareManager::check('/test', 'POST', ['ADMIN'], 1);

echo "<pre>";
echo "Usuario actual: " . print_r($user, true);
echo "Resultado de verificación: " . print_r($checkResult, true);
echo "</pre>";
?>
```

### Estadísticas de Rate Limiting
```php
<?php
$stats = MiddlewareManager::getCurrentUserRateStats();
echo "Solicitudes restantes: " . $stats['remaining'];
echo "Tiempo hasta reset: " . $stats['reset_time'];
?>
```

## CONCLUSIÓN

La implementación completa de middlewares proporciona una base sólida de seguridad para el sistema Cyberhole Condominios. Cada componente trabaja de manera independiente pero coordinada para garantizar:

- **Seguridad robusta** contra amenazas comunes (CSRF, DoS, acceso no autorizado)
- **Flexibilidad** para diferentes casos de uso (web, API, AJAX)
- **Escalabilidad** para crecimiento futuro con configuración centralizada
- **Mantenibilidad** con código bien estructurado y documentado
- **Facilidad de uso** con MiddlewareManager y ejemplos completos

### Beneficios del Sistema:
1. **Protección multicapa**: 5 middlewares especializados
2. **Configuración centralizada**: Un solo punto de configuración
3. **Gestión simplificada**: MiddlewareManager para casos comunes
4. **Documentación completa**: Ejemplos y casos de uso reales
5. **Preparado para producción**: Logging, auditoría y monitoreo

El sistema está completamente preparado para integración con la capa de servicios y APIs del proyecto, proporcionando una base de seguridad enterprise-grade para condominios.

---

**Versión**: 1.0.0  
**Autor**: Sistema Cyberhole  
**Fecha**: $(date)  
**Estado**: Producción Ready
