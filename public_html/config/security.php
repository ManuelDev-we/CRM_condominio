<?php
/**
 * Configuración de Seguridad - Sistema de Condominios
 */

// Prevenir acceso directo
if (!defined('SECURE_ACCESS')) {
    define('SECURE_ACCESS', true);
}

class SecurityConfig {
    
    /**
     * Cargar configuración de seguridad desde .env
     */
    public static function loadConfig() {
        self::loadEnvVariables();
        
        // Configurar sesiones seguras
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        // Configurar tiempo de vida de sesión
        $sessionLifetime = $_ENV['SESSION_LIFETIME'] ?? 28800; // 8 horas por defecto
        ini_set('session.gc_maxlifetime', $sessionLifetime);
        
        // Headers de seguridad
        self::setSecurityHeaders();
    }
    
    /**
     * Establecer headers de seguridad
     */
    public static function setSecurityHeaders() {
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: DENY');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            
            // CORS según configuración
            $allowedOrigins = $_ENV['ALLOWED_ORIGINS'] ?? '';
            if (!empty($allowedOrigins)) {
                $origins = explode(',', $allowedOrigins);
                $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
                
                if (in_array($origin, $origins)) {
                    header("Access-Control-Allow-Origin: $origin");
                }
            }
            
            // Content Security Policy básico
            header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;");
        }
    }
    
    /**
     * Obtener configuración de encriptación
     */
    public static function getEncryptionConfig() {
        return [
            'aes_key' => $_ENV['AES_KEY'] ?? 'Cyberhole2025SecretKey32CharLong!',
            'aes_method' => $_ENV['AES_METHOD'] ?? 'AES-256-CBC',
            'bcrypt_rounds' => (int)($_ENV['BCRYPT_ROUNDS'] ?? 12),
            'pepper_secret' => $_ENV['PEPPER_SECRET'] ?? 'CyberholeCondominios2025PepperSecret'
        ];
    }
    
    /**
     * Obtener configuración de rate limiting
     */
    public static function getRateLimitConfig() {
        return [
            'max_requests_per_hour' => (int)($_ENV['MAX_REQUESTS_PER_HOUR'] ?? 1000),
            'max_login_attempts' => (int)($_ENV['MAX_LOGIN_ATTEMPTS'] ?? 5),
            'lockout_duration' => (int)($_ENV['LOCKOUT_DURATION'] ?? 900)
        ];
    }
    
    /**
     * Verificar si está en modo debug
     */
    public static function isDebugMode() {
        return ($_ENV['DEBUG_MODE'] ?? 'false') === 'true';
    }
    
    /**
     * Obtener entorno actual
     */
    public static function getEnvironment() {
        return $_ENV['APP_ENV'] ?? 'development';
    }
    
    /**
     * Cargar variables de entorno desde archivo .env
     */
    private static function loadEnvVariables() {
        $envFile = __DIR__ . '/../.env';
        
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                $line = trim($line);
                
                // Ignorar comentarios y líneas vacías
                if (empty($line) || $line[0] === '#') {
                    continue;
                }
                
                // Buscar = en la línea
                if (strpos($line, '=') === false) {
                    continue;
                }
                
                list($key, $value) = explode('=', $line, 2);
                
                $key = trim($key);
                $value = trim($value);
                
                // Remover comillas si existen
                if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                    $value = substr($value, 1, -1);
                }
                
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}
?>