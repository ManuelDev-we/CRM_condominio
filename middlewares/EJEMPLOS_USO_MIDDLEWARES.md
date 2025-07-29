# EJEMPLOS DE USO - MIDDLEWARES CYBERHOLE
## Sistema de Gestión de Condominios

### 1. AUTENTICACIÓN BÁSICA
```php
<?php
// En cualquier página que requiera login
require_once __DIR__ . '/middlewares/AuthMiddleware.php';

try {
    $user = AuthMiddleware::execute('/dashboard');
    echo "Bienvenido: " . $user['nombre'];
} catch (Exception $e) {
    // Redirige automáticamente al login
    exit;
}
?>
```

### 2. VERIFICACIÓN DE ROLES
```php
<?php
// Solo administradores
require_once __DIR__ . '/middlewares/MiddlewareManager.php';

try {
    $user = MiddlewareManager::adminOnly('/admin/panel');
    echo "Panel de administración disponible";
} catch (Exception $e) {
    http_response_code(403);
    echo "Acceso denegado";
}
?>
```

### 3. PROTECCIÓN CSRF EN FORMULARIOS
```php
<!-- En tu formulario HTML -->
<form method="POST" action="/api/casas.php">
    <?= MiddlewareManager::csrfField('create_casa') ?>
    <input type="text" name="numero" required>
    <input type="submit" value="Crear Casa">
</form>
```

```php
<?php
// En el endpoint que recibe el formulario
try {
    $user = MiddlewareManager::execute(
        '/api/casas.php',
        'POST',
        ['ADMIN', 'RESIDENTE'],
        $_POST['condominio_id'] ?? null,
        $_POST
    );
    
    // Procesar creación de casa
    echo "Casa creada exitosamente";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
```

### 4. API PROTEGIDA COMPLETA
```php
<?php
// api/vehiculos.php - CRUD de vehículos
require_once __DIR__ . '/../middlewares/MiddlewareManager.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

try {
    switch ($method) {
        case 'GET':
            // Listar vehículos - Solo necesita autenticación
            $user = MiddlewareManager::execute($uri, 'GET');
            // Lógica para listar vehículos
            break;
            
        case 'POST':
            // Crear vehículo - Requiere rol RESIDENTE y CSRF
            $user = MiddlewareManager::execute(
                $uri,
                'POST',
                ['ADMIN', 'RESIDENTE'],
                $_POST['condominio_id'] ?? null,
                $_POST
            );
            // Lógica para crear vehículo
            break;
            
        case 'PUT':
            // Actualizar vehículo - Verificar propiedad
            $data = json_decode(file_get_contents('php://input'), true);
            $user = MiddlewareManager::execute(
                $uri,
                'PUT',
                ['ADMIN', 'RESIDENTE'],
                $data['condominio_id'] ?? null,
                $data
            );
            // Lógica para actualizar vehículo
            break;
            
        case 'DELETE':
            // Eliminar vehículo - Solo admin
            $user = MiddlewareManager::adminOnly($uri, 'DELETE');
            // Lógica para eliminar vehículo
            break;
    }
    
    echo json_encode(['success' => true, 'data' => $result ?? []]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
```

### 5. AJAX CON PROTECCIÓN CSRF
```javascript
// En tu JavaScript
async function createCasa(casaData) {
    // Obtener token CSRF
    const response = await fetch('/middlewares/csrf_token.php');
    const csrfData = await response.json();
    
    // Incluir token en la solicitud
    const formData = new FormData();
    formData.append('csrf_token', csrfData.token);
    formData.append('numero', casaData.numero);
    formData.append('condominio_id', casaData.condominioId);
    
    const result = await fetch('/api/casas.php', {
        method: 'POST',
        body: formData
    });
    
    return await result.json();
}
```

```php
<?php
// csrf_token.php - Endpoint para obtener tokens CSRF via AJAX
require_once __DIR__ . '/CsrfMiddleware.php';

header('Content-Type: application/json');
echo json_encode(CsrfMiddleware::getTokenForJS($_GET['action'] ?? 'default'));
?>
```

### 6. VERIFICACIÓN SUAVE (SIN EXCEPCIÓN)
```php
<?php
// Para verificaciones que no deben interrumpir la página
$checkResult = MiddlewareManager::check(
    '/admin/reports',
    'GET',
    ['ADMIN'],
    $condominioId
);

if ($checkResult['overall_success']) {
    echo "Acceso completo disponible";
    $user = $checkResult['user'];
} else {
    echo "Acceso limitado";
    foreach ($checkResult['checks'] as $check => $result) {
        if (!$result['success']) {
            echo "Fallo en: $check - " . $result['message'];
        }
    }
}
?>
```

### 7. MANEJO DE SESIONES AVANZADO
```php
<?php
// login.php - Proceso de login
require_once __DIR__ . '/middlewares/AuthMiddleware.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Verificar credenciales (tu lógica de autenticación)
    $user = verificarCredenciales($email, $password);
    
    if ($user) {
        // Iniciar sesión
        AuthMiddleware::createSession($user);
        
        // Opcional: Crear JWT para APIs
        $jwt = AuthMiddleware::createJWT($user);
        
        header('Location: /dashboard');
        exit;
    } else {
        $error = "Credenciales incorrectas";
    }
}
?>
```

### 8. MIDDLEWARE PERSONALIZADO PARA PÁGINAS
```php
<?php
// dashboard.php - Panel principal
require_once __DIR__ . '/middlewares/MiddlewareManager.php';

// Verificar acceso con rate limiting
$user = MiddlewareManager::execute(
    '/dashboard',
    'GET',
    ['ADMIN', 'RESIDENTE', 'EMPLEADO']
);

// Obtener estadísticas de uso
$rateStats = MiddlewareManager::getCurrentUserRateStats();

// Verificar permisos específicos
$canCreateCasa = MiddlewareManager::canCurrentUserPerform('create', 'casa');
$canDeleteVehiculo = MiddlewareManager::canCurrentUserPerform('delete', 'vehiculo');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - <?= htmlspecialchars($user['nombre']) ?></title>
</head>
<body>
    <h1>Bienvenido, <?= htmlspecialchars($user['nombre']) ?></h1>
    <p>Rol: <?= htmlspecialchars($user['rol']) ?></p>
    
    <?php if ($canCreateCasa): ?>
        <button onclick="showCreateCasaForm()">Crear Casa</button>
    <?php endif; ?>
    
    <?php if ($canDeleteVehiculo): ?>
        <button onclick="showDeleteOptions()">Eliminar Vehículos</button>
    <?php endif; ?>
    
    <div id="rate-limit-info">
        <p>Solicitudes restantes: <?= $rateStats['remaining'] ?? 'N/A' ?></p>
    </div>
</body>
</html>
```

### 9. CONFIGURACIÓN AVANZADA
```php
<?php
// config/middleware_config.php - Configuración personalizada
class MiddlewareConfig {
    public static function customize() {
        // Personalizar rutas excluidas de autenticación
        AuthMiddleware::setExcludedRoutes([
            '/login',
            '/register',
            '/api/public/*',
            '/docs/*'
        ]);
        
        // Personalizar límites de rate limiting
        RateLimitMiddleware::setLimits([
            'login' => ['requests' => 5, 'period' => 300],
            'api' => ['requests' => 100, 'period' => 3600],
            'general' => ['requests' => 200, 'period' => 3600]
        ]);
        
        // Personalizar permisos por rol
        RoleMiddleware::setPermissions([
            'ADMIN' => ['*'],
            'RESIDENTE' => ['casa:*', 'vehiculo:read,create,update', 'persona:read,update'],
            'EMPLEADO' => ['acceso:*', 'vehiculo:read', 'persona:read']
        ]);
    }
}

// Aplicar configuración al inicio de la aplicación
MiddlewareConfig::customize();
?>
```

### 10. DEBUGGING Y MONITOREO
```php
<?php
// debug_middlewares.php - Para desarrollo
require_once __DIR__ . '/middlewares/MiddlewareManager.php';

// Información del usuario actual
$currentUser = MiddlewareManager::getCurrentUser();
echo "<h2>Usuario Actual:</h2>";
echo "<pre>" . print_r($currentUser, true) . "</pre>";

// Estadísticas de rate limiting
$rateStats = MiddlewareManager::getCurrentUserRateStats();
echo "<h2>Rate Limiting:</h2>";
echo "<pre>" . print_r($rateStats, true) . "</pre>";

// Verificar permisos
$permissions = [
    'create casa' => MiddlewareManager::canCurrentUserPerform('create', 'casa'),
    'delete vehiculo' => MiddlewareManager::canCurrentUserPerform('delete', 'vehiculo'),
    'update persona' => MiddlewareManager::canCurrentUserPerform('update', 'persona')
];

echo "<h2>Permisos:</h2>";
echo "<pre>" . print_r($permissions, true) . "</pre>";

// Estado de verificación completa
$checkResult = MiddlewareManager::check('/test', 'POST', ['ADMIN'], 1);
echo "<h2>Verificación Completa:</h2>";
echo "<pre>" . print_r($checkResult, true) . "</pre>";
?>
```

### NOTAS IMPORTANTES:

1. **Orden de Middlewares**: Siempre Auth → RateLimit → CSRF → Role → Ownership
2. **Manejo de Errores**: Cada middleware lanza excepciones específicas
3. **Performance**: Los middlewares cachean resultados para evitar consultas repetidas
4. **Seguridad**: Nunca omitir CSRF en formularios POST/PUT/DELETE
5. **Flexibilidad**: Usar `check()` para verificaciones no bloqueantes
6. **Debugging**: Activar logs en desarrollo para monitorear comportamiento
