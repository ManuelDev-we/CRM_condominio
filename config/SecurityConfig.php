<?php
/**
 * SecurityConfig - Configuración Central de Seguridad
 * Sistema Cyberhole Condominios
 * 
 * Archivo de configuración centralizada para todos los middlewares
 * y componentes de seguridad del sistema.
 * 
 * @version 1.0.0
 * @author Sistema Cyberhole
 */

class SecurityConfig {
    
    /**
     * Configuración de Autenticación
     */
    public static function getAuthConfig(): array {
        return [
            // Configuración de sesiones
            'session' => [
                'name' => 'CYBERHOLE_SESSION',
                'lifetime' => 3600 * 8, // 8 horas
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict'
            ],
            
            // Configuración JWT
            'jwt' => [
                'secret' => self::getJWTSecret(),
                'algorithm' => 'HS256',
                'expiration' => 3600 * 24, // 24 horas
                'issuer' => 'cyberhole-condominios',
                'audience' => 'cyberhole-users'
            ],
            
            // Rutas excluidas de autenticación
            'excluded_routes' => [
                '/login',
                '/login.php',
                '/register',
                '/register.php',
                '/api/auth/login',
                '/api/auth/register',
                '/api/public/*',
                '/docs/*',
                '/css/*',
                '/js/*',
                '/images/*',
                '/assets/*'
            ],
            
            // Configuración de encriptación
            'encryption' => [
                'method' => 'AES-256-CBC',
                'key' => self::getEncryptionKey(),
                'iv_length' => 16
            ]
        ];
    }
    
    /**
     * Configuración de Roles y Permisos
     */
    public static function getRoleConfig(): array {
        return [
            // Jerarquía de roles (orden de mayor a menor privilegio)
            'hierarchy' => ['ADMIN', 'RESIDENTE', 'EMPLEADO'],
            
            // Permisos por rol
            'permissions' => [
                'ADMIN' => [
                    // Administradores pueden hacer todo
                    '*' => ['create', 'read', 'update', 'delete']
                ],
                'RESIDENTE' => [
                    'casa' => ['create', 'read', 'update', 'delete'],
                    'vehiculo' => ['create', 'read', 'update', 'delete'],
                    'persona' => ['read', 'update'],
                    'acceso' => ['read'],
                    'dispositivo' => ['read'],
                    'engomado' => ['create', 'read', 'update'],
                    'blog' => ['read'],
                    'tag' => ['read'],
                    'tarea' => ['read', 'update'],
                    'unidad' => ['read']
                ],
                'EMPLEADO' => [
                    'acceso' => ['create', 'read', 'update', 'delete'],
                    'vehiculo' => ['read'],
                    'persona' => ['read'],
                    'casa' => ['read'],
                    'dispositivo' => ['read', 'update'],
                    'engomado' => ['read'],
                    'blog' => ['read'],
                    'tarea' => ['read', 'update']
                ]
            ],
            
            // Rutas que requieren roles específicos
            'route_permissions' => [
                '/admin/*' => ['ADMIN'],
                '/api/admin/*' => ['ADMIN'],
                '/api/condominios/*' => ['ADMIN'],
                '/api/empleados/*' => ['ADMIN'],
                '/residente/*' => ['ADMIN', 'RESIDENTE'],
                '/api/casas/*' => ['ADMIN', 'RESIDENTE'],
                '/api/vehiculos/*' => ['ADMIN', 'RESIDENTE'],
                '/empleado/*' => ['ADMIN', 'EMPLEADO'],
                '/api/accesos/*' => ['ADMIN', 'EMPLEADO'],
                '/api/dispositivos/*' => ['ADMIN', 'EMPLEADO']
            ]
        ];
    }
    
    /**
     * Configuración de Rate Limiting
     */
    public static function getRateLimitConfig(): array {
        return [
            // Límites por tipo de acción
            'limits' => [
                'login' => [
                    'requests' => 5,
                    'period' => 300, // 5 minutos
                    'block_duration' => 900 // 15 minutos de bloqueo
                ],
                'api' => [
                    'requests' => 100,
                    'period' => 3600, // 1 hora
                    'block_duration' => 3600
                ],
                'general' => [
                    'requests' => 200,
                    'period' => 3600, // 1 hora
                    'block_duration' => 1800 // 30 minutos
                ],
                'upload' => [
                    'requests' => 20,
                    'period' => 3600, // 1 hora
                    'block_duration' => 7200 // 2 horas
                ]
            ],
            
            // Configuración de almacenamiento
            'storage' => [
                'path' => __DIR__ . '/../logs/rate_limits/',
                'cleanup_interval' => 3600 // Limpiar archivos viejos cada hora
            ],
            
            // Rutas con límites específicos
            'route_limits' => [
                '/api/auth/login' => 'login',
                '/api/auth/register' => 'login',
                '/api/*' => 'api',
                '/upload/*' => 'upload'
            ],
            
            // IPs exentas de rate limiting
            'whitelist' => [
                '127.0.0.1',
                '::1'
                // Agregar IPs de servidores internos
            ]
        ];
    }
    
    /**
     * Configuración de Protección CSRF
     */
    public static function getCsrfConfig(): array {
        return [
            // Configuración de tokens
            'token' => [
                'length' => 32,
                'expiration' => 3600, // 1 hora
                'regenerate_on_use' => false
            ],
            
            // Métodos que requieren protección CSRF
            'protected_methods' => ['POST', 'PUT', 'PATCH', 'DELETE'],
            
            // Rutas excluidas de protección CSRF
            'excluded_routes' => [
                '/api/auth/logout', // Logout puede ser GET
                '/api/public/*',
                '/webhooks/*'
            ],
            
            // Configuración de almacenamiento
            'storage' => [
                'session_key' => 'csrf_tokens',
                'max_tokens_per_session' => 10
            ],
            
            // Headers HTTP permitidos para tokens
            'token_headers' => [
                'X-CSRF-Token',
                'X-Requested-With'
            ]
        ];
    }
    
    /**
     * Configuración de Propiedad de Condominio
     */
    public static function getOwnershipConfig(): array {
        return [
            // Mapeo de recursos a tablas y campos
            'resource_mapping' => [
                'casa' => [
                    'table' => 'casas',
                    'id_field' => 'id',
                    'condominio_field' => 'condominio_id',
                    'owner_field' => 'propietario_id'
                ],
                'vehiculo' => [
                    'table' => 'vehiculos',
                    'id_field' => 'id',
                    'condominio_field' => 'condominio_id',
                    'owner_field' => 'propietario_id'
                ],
                'persona' => [
                    'table' => 'personas',
                    'id_field' => 'id',
                    'condominio_field' => 'condominio_id',
                    'owner_field' => 'id'
                ],
                'acceso' => [
                    'table' => 'accesos',
                    'id_field' => 'id',
                    'condominio_field' => 'condominio_id',
                    'owner_field' => 'persona_id'
                ],
                'dispositivo' => [
                    'table' => 'dispositivos',
                    'id_field' => 'id',
                    'condominio_field' => 'condominio_id',
                    'owner_field' => null // Los dispositivos no tienen propietario específico
                ],
                'engomado' => [
                    'table' => 'engomados',
                    'id_field' => 'id',
                    'condominio_field' => 'condominio_id',
                    'owner_field' => 'vehiculo_id'
                ]
            ],
            
            // Rutas que requieren verificación de propiedad
            'protected_routes' => [
                '/api/casas/',
                '/api/vehiculos/',
                '/api/personas/',
                '/api/accesos/',
                '/api/engomados/'
            ],
            
            // Acciones que requieren verificación estricta
            'strict_actions' => ['update', 'delete'],
            
            // Roles que pueden omitir verificación de propiedad
            'bypass_roles' => ['ADMIN']
        ];
    }
    
    /**
     * Configuración de Logging y Auditoría
     */
    public static function getLoggingConfig(): array {
        return [
            // Configuración de logs
            'enabled' => true,
            'log_level' => 'INFO', // DEBUG, INFO, WARNING, ERROR
            'log_path' => __DIR__ . '/../logs/',
            'max_file_size' => 10 * 1024 * 1024, // 10MB
            'max_files' => 10,
            
            // Eventos a registrar
            'log_events' => [
                'auth_success' => true,
                'auth_failure' => true,
                'role_violation' => true,
                'csrf_violation' => true,
                'rate_limit_exceeded' => true,
                'ownership_violation' => true,
                'admin_actions' => true
            ],
            
            // Formato de logs
            'log_format' => '[{timestamp}] {level}: {event} - User: {user_id} - IP: {ip} - Route: {route} - Message: {message}',
            
            // Configuración de alertas
            'alerts' => [
                'enabled' => false,
                'email' => 'admin@cyberhole.com',
                'threshold' => 10 // Alertar después de 10 violaciones en 1 hora
            ]
        ];
    }
    
    /**
     * Configuración de Base de Datos
     */
    public static function getDatabaseConfig(): array {
        return [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'database' => $_ENV['DB_DATABASE'] ?? 'cyberhole_condominios',
            'username' => $_ENV['DB_USERNAME'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => 'utf8mb4',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        ];
    }
    
    /**
     * Obtener clave secreta JWT
     */
    private static function getJWTSecret(): string {
        $secret = $_ENV['JWT_SECRET'] ?? null;
        
        if (!$secret) {
            // Generar y guardar una clave si no existe
            $secret = base64_encode(random_bytes(64));
            // En producción, esto debería guardarse en un archivo de configuración seguro
            file_put_contents(__DIR__ . '/.jwt_secret', $secret);
        }
        
        return $secret;
    }
    
    /**
     * Obtener clave de encriptación
     */
    private static function getEncryptionKey(): string {
        $key = $_ENV['ENCRYPTION_KEY'] ?? null;
        
        if (!$key) {
            // Generar y guardar una clave si no existe
            $key = base64_encode(random_bytes(32));
            // En producción, esto debería guardarse en un archivo de configuración seguro
            file_put_contents(__DIR__ . '/.encryption_key', $key);
        }
        
        return $key;
    }
    
    /**
     * Obtener configuración de entorno
     */
    public static function getEnvironmentConfig(): array {
        return [
            'environment' => $_ENV['APP_ENV'] ?? 'development',
            'debug' => $_ENV['APP_DEBUG'] ?? true,
            'timezone' => $_ENV['APP_TIMEZONE'] ?? 'America/Mexico_City',
            'locale' => $_ENV['APP_LOCALE'] ?? 'es_MX',
            'url' => $_ENV['APP_URL'] ?? 'http://localhost'
        ];
    }
    
    /**
     * Obtener toda la configuración
     */
    public static function getAllConfig(): array {
        return [
            'auth' => self::getAuthConfig(),
            'roles' => self::getRoleConfig(),
            'rate_limit' => self::getRateLimitConfig(),
            'csrf' => self::getCsrfConfig(),
            'ownership' => self::getOwnershipConfig(),
            'logging' => self::getLoggingConfig(),
            'database' => self::getDatabaseConfig(),
            'environment' => self::getEnvironmentConfig()
        ];
    }
    
    /**
     * Validar configuración
     */
    public static function validateConfig(): array {
        $errors = [];
        
        // Verificar que existan directorios necesarios
        $requiredDirs = [
            __DIR__ . '/../logs/',
            __DIR__ . '/../logs/rate_limits/'
        ];
        
        foreach ($requiredDirs as $dir) {
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    $errors[] = "No se puede crear el directorio: $dir";
                }
            }
        }
        
        // Verificar permisos de escritura
        $writableDirs = [
            __DIR__ . '/../logs/',
            __DIR__ . '/../logs/rate_limits/'
        ];
        
        foreach ($writableDirs as $dir) {
            if (!is_writable($dir)) {
                $errors[] = "El directorio no es escribible: $dir";
            }
        }
        
        // Verificar extensiones PHP necesarias
        $requiredExtensions = ['pdo', 'pdo_mysql', 'openssl', 'json'];
        
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $errors[] = "Extensión PHP requerida no encontrada: $ext";
            }
        }
        
        return $errors;
    }
    
    /**
     * Inicializar configuración del sistema
     */
    public static function initialize(): bool {
        // Validar configuración
        $errors = self::validateConfig();
        
        if (!empty($errors)) {
            throw new Exception("Errores de configuración: " . implode(', ', $errors));
        }
        
        // Configurar zona horaria
        $envConfig = self::getEnvironmentConfig();
        date_default_timezone_set($envConfig['timezone']);
        
        // Configurar sesiones
        $authConfig = self::getAuthConfig();
        $sessionConfig = $authConfig['session'];
        
        ini_set('session.name', $sessionConfig['name']);
        ini_set('session.cookie_lifetime', $sessionConfig['lifetime']);
        ini_set('session.cookie_secure', $sessionConfig['secure'] ? '1' : '0');
        ini_set('session.cookie_httponly', $sessionConfig['httponly'] ? '1' : '0');
        ini_set('session.cookie_samesite', $sessionConfig['samesite']);
        
        // Iniciar sesión si no está activa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return true;
    }
}
