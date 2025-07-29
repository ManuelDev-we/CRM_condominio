<?php
/**
 * CsrfMiddleware - Middleware de Protección CSRF
 * Sistema Cyberhole Condominios
 * 
 * Protege contra ataques Cross-Site Request Forgery generando y validando
 * tokens únicos para formularios y solicitudes POST/PUT/DELETE.
 * 
 * @version 1.0.0
 * @author Sistema Cyberhole
 */

require_once __DIR__ . '/../config/SecurityConfig.php';

class CsrfMiddleware {
    
    const TOKEN_NAME = '_token';
    const HEADER_NAME = 'X-CSRF-TOKEN';
    const SESSION_KEY = 'csrf_tokens';
    
    // Métodos HTTP que requieren verificación CSRF
    private static $protectedMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
    
    // Rutas excluidas de verificación CSRF
    private static $excludedRoutes = [
        '/api/auth/login',
        '/api/auth/logout',
        '/api/health',
        '/webhook'
    ];
    
    /**
     * Generar token CSRF
     * 
     * @param string $action Acción específica (opcional)
     * @return string Token CSRF generado
     */
    public static function generateToken(string $action = 'default'): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Inicializar array de tokens si no existe
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }
        
        // Generar token único
        $token = bin2hex(random_bytes(32));
        $timestamp = time();
        
        // Almacenar token con timestamp
        $_SESSION[self::SESSION_KEY][$action] = [
            'token' => $token,
            'timestamp' => $timestamp
        ];
        
        // Limpiar tokens expirados
        self::cleanExpiredTokens();
        
        return $token;
    }
    
    /**
     * Verificar token CSRF
     * 
     * @param string $token Token a verificar
     * @param string $action Acción específica
     * @return array Resultado de la verificación
     */
    public static function verifyToken(string $token, string $action = 'default'): array {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Verificar si existen tokens en sesión
            if (!isset($_SESSION[self::SESSION_KEY][$action])) {
                return [
                    'success' => false,
                    'message' => 'Token CSRF no encontrado en sesión',
                    'error_code' => 419
                ];
            }
            
            $storedData = $_SESSION[self::SESSION_KEY][$action];
            $storedToken = $storedData['token'];
            $timestamp = $storedData['timestamp'];
            
            // Verificar expiración del token
            $expireTime = SecurityConfig::get('csrf.expire_time', 1800); // 30 minutos
            if (time() - $timestamp > $expireTime) {
                unset($_SESSION[self::SESSION_KEY][$action]);
                return [
                    'success' => false,
                    'message' => 'Token CSRF expirado',
                    'error_code' => 419
                ];
            }
            
            // Comparar tokens de forma segura
            if (!hash_equals($storedToken, $token)) {
                return [
                    'success' => false,
                    'message' => 'Token CSRF inválido',
                    'error_code' => 419
                ];
            }
            
            // Si está configurado, regenerar token después del uso
            if (SecurityConfig::get('csrf.regenerate_on_use', true)) {
                unset($_SESSION[self::SESSION_KEY][$action]);
            }
            
            return [
                'success' => true,
                'message' => 'Token CSRF válido'
            ];
            
        } catch (Exception $e) {
            error_log("CsrfMiddleware Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno de verificación CSRF',
                'error_code' => 500
            ];
        }
    }
    
    /**
     * Verificar solicitud HTTP contra CSRF
     * 
     * @param string $method Método HTTP
     * @param string $route Ruta solicitada
     * @return array Resultado de la verificación
     */
    public static function verify(string $method = '', string $route = ''): array {
        // Obtener método HTTP si no se especifica
        if (empty($method)) {
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        }
        
        // Verificar si el método requiere protección CSRF
        if (!in_array(strtoupper($method), self::$protectedMethods)) {
            return [
                'success' => true,
                'message' => 'Método no requiere verificación CSRF'
            ];
        }
        
        // Verificar si la ruta está excluida
        if (self::isExcludedRoute($route)) {
            return [
                'success' => true,
                'message' => 'Ruta excluida de verificación CSRF'
            ];
        }
        
        // Verificar si CSRF está habilitado
        if (!SecurityConfig::get('csrf.enabled', true)) {
            return [
                'success' => true,
                'message' => 'Verificación CSRF deshabilitada'
            ];
        }
        
        // Obtener token de la solicitud
        $token = self::getTokenFromRequest();
        
        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'Token CSRF requerido pero no encontrado',
                'error_code' => 419
            ];
        }
        
        // Verificar token
        return self::verifyToken($token);
    }
    
    /**
     * Obtener token CSRF de la solicitud
     * 
     * @return string Token encontrado o cadena vacía
     */
    private static function getTokenFromRequest(): string {
        // Buscar en POST data
        if (isset($_POST[self::TOKEN_NAME])) {
            return $_POST[self::TOKEN_NAME];
        }
        
        // Buscar en headers
        $headers = getallheaders();
        if (isset($headers[self::HEADER_NAME])) {
            return $headers[self::HEADER_NAME];
        }
        
        // Buscar en headers con formato alternativo
        if (isset($headers['X-Csrf-Token'])) {
            return $headers['X-Csrf-Token'];
        }
        
        // Buscar en JSON body
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input[self::TOKEN_NAME])) {
            return $input[self::TOKEN_NAME];
        }
        
        return '';
    }
    
    /**
     * Verificar si la ruta está excluida de verificación CSRF
     * 
     * @param string $route Ruta a verificar
     * @return bool True si está excluida
     */
    private static function isExcludedRoute(string $route): bool {
        foreach (self::$excludedRoutes as $excludedRoute) {
            if (str_contains($route, $excludedRoute)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Limpiar tokens expirados de la sesión
     * 
     * @return void
     */
    private static function cleanExpiredTokens(): void {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            return;
        }
        
        $expireTime = SecurityConfig::get('csrf.expire_time', 1800);
        $currentTime = time();
        
        foreach ($_SESSION[self::SESSION_KEY] as $action => $data) {
            if (isset($data['timestamp']) && 
                $currentTime - $data['timestamp'] > $expireTime) {
                unset($_SESSION[self::SESSION_KEY][$action]);
            }
        }
    }
    
    /**
     * Ejecutar middleware y detener ejecución si CSRF no es válido
     * 
     * @param string $method Método HTTP
     * @param string $route Ruta solicitada
     * @return void
     */
    public static function execute(string $method = '', string $route = ''): void {
        $result = self::verify($method, $route);
        
        if (!$result['success']) {
            http_response_code($result['error_code'] ?? 419);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $result['message'],
                'error_code' => $result['error_code'] ?? 419,
                'csrf_token' => self::generateToken() // Generar nuevo token
            ]);
            exit;
        }
    }
    
    /**
     * Middleware para APIs - No detiene ejecución, solo retorna resultado
     * 
     * @param string $method Método HTTP
     * @param string $route Ruta solicitada
     * @return array Resultado de verificación
     */
    public static function check(string $method = '', string $route = ''): array {
        return self::verify($method, $route);
    }
    
    /**
     * Generar campo de formulario HTML con token CSRF
     * 
     * @param string $action Acción específica
     * @return string HTML del campo oculto
     */
    public static function field(string $action = 'default'): string {
        $token = self::generateToken($action);
        return '<input type="hidden" name="' . self::TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Obtener token para uso en JavaScript
     * 
     * @param string $action Acción específica
     * @return array Token y nombre del campo
     */
    public static function getTokenForJS(string $action = 'default'): array {
        return [
            'token' => self::generateToken($action),
            'field_name' => self::TOKEN_NAME,
            'header_name' => self::HEADER_NAME
        ];
    }
    
    /**
     * Validar referrer HTTP (si está habilitado)
     * 
     * @return bool True si el referrer es válido
     */
    public static function validateReferrer(): bool {
        if (!SecurityConfig::get('csrf.validate_referrer', true)) {
            return true;
        }
        
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        
        if (empty($referrer) || empty($host)) {
            return false;
        }
        
        $referrerHost = parse_url($referrer, PHP_URL_HOST);
        
        return $referrerHost === $host;
    }
    
    /**
     * Obtener meta tag para HTML head
     * 
     * @param string $action Acción específica
     * @return string Meta tag HTML
     */
    public static function metaTag(string $action = 'default'): string {
        $token = self::generateToken($action);
        return '<meta name="csrf-token" content="' . htmlspecialchars($token) . '">';
    }
}
