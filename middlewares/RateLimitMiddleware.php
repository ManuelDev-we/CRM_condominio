<?php
/**
 * RateLimitMiddleware - Middleware de Limitación de Tasa
 * Sistema Cyberhole Condominios
 * 
 * Limita la frecuencia de solicitudes por usuario/IP para prevenir abusos,
 * ataques DoS y uso excesivo de recursos del servidor.
 * 
 * @version 1.0.0
 * @author Sistema Cyberhole
 */

require_once __DIR__ . '/../config/SecurityConfig.php';

class RateLimitMiddleware {
    
    const CACHE_PREFIX = 'rate_limit_';
    const LOGIN_PREFIX = 'login_attempts_';
    const API_PREFIX = 'api_requests_';
    
    // Límites por defecto (solicitudes por ventana de tiempo)
    private static $defaultLimits = [
        'login' => [
            'max_attempts' => 5,
            'window' => 900, // 15 minutos
            'lockout_time' => 1800 // 30 minutos
        ],
        'api' => [
            'max_attempts' => 100,
            'window' => 3600, // 1 hora
            'lockout_time' => 3600 // 1 hora
        ],
        'general' => [
            'max_attempts' => 200,
            'window' => 3600, // 1 hora
            'lockout_time' => 1800 // 30 minutos
        ]
    ];
    
    // Rutas con límites específicos
    private static $routeLimits = [
        '/api/auth/login' => 'login',
        '/api/auth/register' => 'login',
        '/api/auth/forgot-password' => 'login',
        '/api/auth/reset-password' => 'login',
        '/api/' => 'api'
    ];
    
    /**
     * Verificar límite de tasa para una solicitud
     * 
     * @param string $identifier Identificador único (IP, user_id, etc.)
     * @param string $route Ruta solicitada
     * @param string $type Tipo de límite (login, api, general)
     * @return array Resultado de la verificación
     */
    public static function verify(string $identifier, string $route = '', string $type = 'general'): array {
        try {
            // Verificar si la limitación está habilitada
            if (!SecurityConfig::get('rate_limiting.enabled', true)) {
                return [
                    'success' => true,
                    'message' => 'Limitación de tasa deshabilitada'
                ];
            }
            
            // Determinar tipo de límite basado en la ruta
            if (!empty($route)) {
                $type = self::getRouteType($route);
            }
            
            // Obtener configuración de límites
            $config = self::getLimitConfig($type);
            
            // Generar clave de caché
            $cacheKey = self::generateCacheKey($identifier, $type);
            
            // Obtener datos actuales
            $data = self::getCacheData($cacheKey);
            
            // Verificar si está en período de bloqueo
            if (self::isBlocked($data, $config)) {
                $remaining = self::getRemainingLockoutTime($data, $config);
                return [
                    'success' => false,
                    'message' => 'Demasiados intentos - Acceso bloqueado',
                    'error_code' => 429,
                    'retry_after' => $remaining,
                    'blocked_until' => date('Y-m-d H:i:s', time() + $remaining)
                ];
            }
            
            // Limpiar ventana si ha expirado
            if (self::isWindowExpired($data, $config)) {
                $data = self::resetWindow();
            }
            
            // Incrementar contador
            $data['count']++;
            $data['last_request'] = time();
            
            // Verificar si se excede el límite
            if ($data['count'] > $config['max_attempts']) {
                $data['blocked_until'] = time() + $config['lockout_time'];
                self::setCacheData($cacheKey, $data, $config['lockout_time']);
                
                return [
                    'success' => false,
                    'message' => 'Límite de solicitudes excedido',
                    'error_code' => 429,
                    'max_attempts' => $config['max_attempts'],
                    'window' => $config['window'],
                    'retry_after' => $config['lockout_time']
                ];
            }
            
            // Guardar datos actualizados
            self::setCacheData($cacheKey, $data, $config['window']);
            
            return [
                'success' => true,
                'message' => 'Dentro del límite permitido',
                'remaining_attempts' => $config['max_attempts'] - $data['count'],
                'reset_time' => $data['window_start'] + $config['window']
            ];
            
        } catch (Exception $e) {
            error_log("RateLimitMiddleware Error: " . $e->getMessage());
            return [
                'success' => true, // Fallar abierto para no bloquear el sistema
                'message' => 'Error en verificación de límites - Acceso permitido'
            ];
        }
    }
    
    /**
     * Obtener configuración de límites por tipo
     * 
     * @param string $type Tipo de límite
     * @return array Configuración
     */
    private static function getLimitConfig(string $type): array {
        $config = self::$defaultLimits[$type] ?? self::$defaultLimits['general'];
        
        // Sobrescribir con configuración del archivo de configuración
        return [
            'max_attempts' => SecurityConfig::get("rate_limiting.{$type}_requests", $config['max_attempts']),
            'window' => SecurityConfig::get("rate_limiting.{$type}_window", $config['window']),
            'lockout_time' => SecurityConfig::get('rate_limiting.lockout_time', $config['lockout_time'])
        ];
    }
    
    /**
     * Determinar tipo de límite basado en la ruta
     * 
     * @param string $route Ruta solicitada
     * @return string Tipo de límite
     */
    private static function getRouteType(string $route): string {
        foreach (self::$routeLimits as $pattern => $type) {
            if (str_starts_with($route, $pattern)) {
                return $type;
            }
        }
        return 'general';
    }
    
    /**
     * Generar clave de caché
     * 
     * @param string $identifier Identificador único
     * @param string $type Tipo de límite
     * @return string Clave de caché
     */
    private static function generateCacheKey(string $identifier, string $type): string {
        return self::CACHE_PREFIX . $type . '_' . hash('sha256', $identifier);
    }
    
    /**
     * Obtener datos de caché (simulado con archivos)
     * 
     * @param string $cacheKey Clave de caché
     * @return array Datos almacenados
     */
    private static function getCacheData(string $cacheKey): array {
        $cacheFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $cacheKey . '.cache';
        
        if (!file_exists($cacheFile)) {
            return self::resetWindow();
        }
        
        $data = unserialize(file_get_contents($cacheFile));
        
        return is_array($data) ? $data : self::resetWindow();
    }
    
    /**
     * Guardar datos en caché
     * 
     * @param string $cacheKey Clave de caché
     * @param array $data Datos a guardar
     * @param int $ttl Tiempo de vida en segundos
     * @return void
     */
    private static function setCacheData(string $cacheKey, array $data, int $ttl): void {
        $cacheFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $cacheKey . '.cache';
        $data['expires_at'] = time() + $ttl;
        file_put_contents($cacheFile, serialize($data));
    }
    
    /**
     * Resetear ventana de tiempo
     * 
     * @return array Datos iniciales
     */
    private static function resetWindow(): array {
        return [
            'count' => 0,
            'window_start' => time(),
            'last_request' => time(),
            'blocked_until' => 0
        ];
    }
    
    /**
     * Verificar si está bloqueado
     * 
     * @param array $data Datos actuales
     * @param array $config Configuración
     * @return bool True si está bloqueado
     */
    private static function isBlocked(array $data, array $config): bool {
        return isset($data['blocked_until']) && 
               $data['blocked_until'] > 0 && 
               $data['blocked_until'] > time();
    }
    
    /**
     * Verificar si la ventana ha expirado
     * 
     * @param array $data Datos actuales
     * @param array $config Configuración
     * @return bool True si ha expirado
     */
    private static function isWindowExpired(array $data, array $config): bool {
        return isset($data['window_start']) && 
               time() - $data['window_start'] > $config['window'];
    }
    
    /**
     * Obtener tiempo restante de bloqueo
     * 
     * @param array $data Datos actuales
     * @param array $config Configuración
     * @return int Segundos restantes
     */
    private static function getRemainingLockoutTime(array $data, array $config): int {
        if (!isset($data['blocked_until'])) {
            return 0;
        }
        
        $remaining = $data['blocked_until'] - time();
        return max(0, $remaining);
    }
    
    /**
     * Obtener identificador único para el usuario/IP
     * 
     * @param array $user Datos del usuario (opcional)
     * @return string Identificador único
     */
    public static function getIdentifier(array $user = []): string {
        // Usar ID de usuario si está disponible
        if (!empty($user['id'])) {
            return 'user_' . $user['id'];
        }
        
        // Usar IP como respaldo
        $ip = self::getRealIP();
        return 'ip_' . $ip;
    }
    
    /**
     * Obtener IP real del cliente
     * 
     * @return string Dirección IP
     */
    private static function getRealIP(): string {
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Ejecutar middleware y detener ejecución si se excede el límite
     * 
     * @param string $identifier Identificador único
     * @param string $route Ruta solicitada
     * @param string $type Tipo de límite
     * @return void
     */
    public static function execute(string $identifier, string $route = '', string $type = 'general'): void {
        $result = self::verify($identifier, $route, $type);
        
        if (!$result['success']) {
            http_response_code($result['error_code'] ?? 429);
            
            // Agregar headers de rate limiting
            if (isset($result['retry_after'])) {
                header('Retry-After: ' . $result['retry_after']);
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $result['message'],
                'error_code' => $result['error_code'] ?? 429,
                'retry_after' => $result['retry_after'] ?? null
            ]);
            exit;
        }
    }
    
    /**
     * Middleware para APIs - No detiene ejecución, solo retorna resultado
     * 
     * @param string $identifier Identificador único
     * @param string $route Ruta solicitada
     * @param string $type Tipo de límite
     * @return array Resultado de verificación
     */
    public static function check(string $identifier, string $route = '', string $type = 'general'): array {
        return self::verify($identifier, $route, $type);
    }
    
    /**
     * Resetear límites para un identificador específico
     * 
     * @param string $identifier Identificador único
     * @param string $type Tipo de límite
     * @return bool True si se reseteó correctamente
     */
    public static function reset(string $identifier, string $type = 'general'): bool {
        try {
            $cacheKey = self::generateCacheKey($identifier, $type);
            $cacheFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $cacheKey . '.cache';
            
            if (file_exists($cacheFile)) {
                return unlink($cacheFile);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error resetting rate limit: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener estadísticas de uso para un identificador
     * 
     * @param string $identifier Identificador único
     * @param string $type Tipo de límite
     * @return array Estadísticas de uso
     */
    public static function getStats(string $identifier, string $type = 'general'): array {
        $cacheKey = self::generateCacheKey($identifier, $type);
        $data = self::getCacheData($cacheKey);
        $config = self::getLimitConfig($type);
        
        return [
            'current_count' => $data['count'] ?? 0,
            'max_attempts' => $config['max_attempts'],
            'remaining_attempts' => max(0, $config['max_attempts'] - ($data['count'] ?? 0)),
            'window_start' => $data['window_start'] ?? time(),
            'window_duration' => $config['window'],
            'is_blocked' => self::isBlocked($data, $config),
            'blocked_until' => $data['blocked_until'] ?? 0,
            'last_request' => $data['last_request'] ?? 0
        ];
    }
}
