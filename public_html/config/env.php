<?php
/**
 * Cargador de variables de entorno para el sistema de condominios
 */

// Prevenir acceso directo
if (!defined('SECURE_ACCESS')) {
    define('SECURE_ACCESS', true);
}

class EnvLoader {
    
    private static $variables = [];
    private static $loaded = false;
    
    /**
     * Cargar variables del archivo .env
     */
    public static function load($envFile = null) {
        if (self::$loaded) {
            return;
        }
        
        if ($envFile === null) {
            $envFile = __DIR__ . '/../../Public_html/.env';
        }
        
        if (!file_exists($envFile)) {
            throw new Exception("Archivo .env no encontrado: " . $envFile);
        }
        
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
            if (preg_match('/^"(.*)"$/', $value, $matches)) {
                $value = $matches[1];
            } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
                $value = $matches[1];
            }
            
            self::$variables[$key] = $value;
            
            // También establecer en $_ENV si no existe
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
            }
        }
        
        self::$loaded = true;
    }
    
    /**
     * Obtener valor de variable de entorno
     */
    public static function get($key, $default = null) {
        if (!self::$loaded) {
            self::load();
        }
        
        // Buscar en variables cargadas primero
        if (isset(self::$variables[$key])) {
            return self::$variables[$key];
        }
        
        // Buscar en $_ENV
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        // Buscar en getenv()
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        return $default;
    }
    
    /**
     * Verificar si una variable existe
     */
    public static function has($key) {
        return self::get($key) !== null;
    }
    
    /**
     * Obtener todas las variables cargadas
     */
    public static function all() {
        if (!self::$loaded) {
            self::load();
        }
        
        return self::$variables;
    }
}
