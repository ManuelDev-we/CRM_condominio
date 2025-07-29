<?php
/**
 * Cargador de Variables de Entorno
 * Sistema Cyberhole Condominios
 * 
 * Lee las variables definidas en el archivo .env y las coloca en el entorno de ejecución.
 * Permite separar la configuración sensible de la lógica del sistema.
 */

class EnvironmentLoader {
    private static $loaded = false;
    private static $envPath;
    
    /**
     * Carga las variables de entorno desde el archivo .env
     */
    public static function load($envPath = null) {
        if (self::$loaded) {
            return true;
        }
        
        // Determinar la ruta del archivo .env
        if ($envPath === null) {
            $envPath = self::findEnvFile();
        }
        
        self::$envPath = $envPath;
        
        if (!file_exists($envPath)) {
            error_log("[ENV] Archivo .env no encontrado en: {$envPath}");
            self::loadDefaults();
            return false;
        }
        
        try {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                // Ignorar comentarios
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                
                // Procesar variable
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Remover comillas si existen
                    $value = self::parseValue($value);
                    
                    // Establecer en el entorno
                    $_ENV[$key] = $value;
                    putenv("{$key}={$value}");
                }
            }
            
            self::$loaded = true;
            error_log("[ENV] Variables de entorno cargadas desde: {$envPath}");
            
            // Validar variables críticas de seguridad
            self::validateCriticalVariables();
            
            return true;
            
        } catch (Exception $e) {
            error_log("[ENV ERROR] Error cargando variables de entorno: " . $e->getMessage());
            self::loadDefaults();
            return false;
        }
    }
    
    /**
     * Busca el archivo .env en directorios padre
     */
    private static function findEnvFile() {
        $currentDir = __DIR__;
        $maxLevels = 5; // Buscar hasta 5 niveles arriba
        
        for ($i = 0; $i < $maxLevels; $i++) {
            $envFile = $currentDir . DIRECTORY_SEPARATOR . '.env';
            if (file_exists($envFile)) {
                return $envFile;
            }
            $currentDir = dirname($currentDir);
        }
        
        // Por defecto, usar el directorio raíz del proyecto
        return dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . '.env';
    }
    
    /**
     * Procesa el valor de la variable, removiendo comillas y manejando tipos
     */
    private static function parseValue($value) {
        // Remover comillas al inicio y final
        if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
            (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
            $value = substr($value, 1, -1);
        }
        
        // Manejar valores especiales
        switch (strtolower($value)) {
            case 'true':
                return true;
            case 'false':
                return false;
            case 'null':
                return null;
            default:
                return $value;
        }
    }
    
    /**
     * Carga valores por defecto mínimos (NO SENSIBLES) si no se puede leer el archivo .env
     * 🚨 SEGURIDAD: Claves de encriptación NUNCA van aquí - solo en archivo .env
     */
    private static function loadDefaults() {
        $defaults = [
            'APP_ENV' => 'development',
            'APP_DEBUG' => 'true',
            'APP_NAME' => 'Cyberhole Condominios',
            'APP_URL' => 'http://localhost',
            'DB_HOST' => 'localhost',
            'DB_PORT' => '3306',
            'DB_CHARSET' => 'utf8mb4',
            'JWT_EXPIRE' => '3600',
            'CSRF_EXPIRE' => '1800',
            'SESSION_LIFETIME' => '7200',
            'TIMEZONE' => 'America/Mexico_City',
            'AES_METHOD' => 'AES-256-CBC',
            'BCRYPT_ROUNDS' => '12'
        ];
        
        foreach ($defaults as $key => $value) {
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
        
        error_log("[ENV] Valores por defecto (NO SENSIBLES) cargados");
        error_log("[ENV SECURITY WARNING] Variables sensibles (DB_PASSWORD, JWT_SECRET, AES_KEY, PEPPER_SECRET) deben estar en archivo .env");
    }
    
    /**
     * Valida que las variables críticas de seguridad estén presentes
     * 🚨 SEGURIDAD: Variables sensibles deben estar en .env, no hardcodeadas
     */
    private static function validateCriticalVariables() {
        $criticalVars = [
            'DB_PASSWORD' => 'Contraseña de base de datos',
            'JWT_SECRET' => 'Clave secreta JWT',
            'AES_KEY' => 'Clave de encriptación AES',
            'PEPPER_SECRET' => 'Pepper para contraseñas'
        ];
        
        $missingVars = [];
        
        foreach ($criticalVars as $var => $description) {
            if (!isset($_ENV[$var]) || empty($_ENV[$var])) {
                $missingVars[] = "$var ($description)";
            }
        }
        
        if (!empty($missingVars)) {
            $message = "[ENV SECURITY ERROR] Variables críticas faltantes en .env: " . implode(', ', $missingVars);
            error_log($message);
            throw new Exception("Variables de seguridad críticas no configuradas. Revisar archivo .env");
        }
        
        // Validar longitud de claves de encriptación
        if (strlen($_ENV['AES_KEY']) !== 32) {
            error_log("[ENV SECURITY ERROR] AES_KEY debe tener exactamente 32 caracteres");
            throw new Exception("AES_KEY debe tener exactamente 32 caracteres");
        }
        
        if (strlen($_ENV['PEPPER_SECRET']) < 20) {
            error_log("[ENV SECURITY ERROR] PEPPER_SECRET debe tener al menos 20 caracteres");
            throw new Exception("PEPPER_SECRET debe tener al menos 20 caracteres");
        }
        
        error_log("[ENV] Validación de variables críticas de seguridad: OK");
    }
    
    /**
     * Obtiene una variable de entorno con valor por defecto opcional
     */
    public static function get($key, $default = null) {
        return $_ENV[$key] ?? $default;
    }
    
    /**
     * Verifica si una variable de entorno existe
     */
    public static function has($key) {
        return isset($_ENV[$key]);
    }
    
    /**
     * Establece una variable de entorno
     */
    public static function set($key, $value) {
        $_ENV[$key] = $value;
        putenv("{$key}={$value}");
    }
    
    /**
     * Obtiene todas las variables de entorno del sistema
     */
    public static function all() {
        return $_ENV;
    }
    
    /**
     * Obtiene información del estado del cargador
     */
    public static function getStatus() {
        return [
            'loaded' => self::$loaded,
            'env_path' => self::$envPath,
            'environment' => self::get('APP_ENV', 'unknown'),
            'debug' => self::get('APP_DEBUG', false),
            'variables_count' => count($_ENV)
        ];
    }
}

// Cargar automáticamente las variables de entorno
EnvironmentLoader::load();

// Funciones helper para acceso rápido
function env($key, $default = null) {
    return EnvironmentLoader::get($key, $default);
}

function hasEnv($key) {
    return EnvironmentLoader::has($key);
}

function setEnv($key, $value) {
    EnvironmentLoader::set($key, $value);
}