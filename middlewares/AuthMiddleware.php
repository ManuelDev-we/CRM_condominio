<?php
/**
 * AuthMiddleware - Middleware de Autenticación
 * Sistema Cyberhole Condominios
 * 
 * Verifica si existe una sesión activa o token JWT válido antes de 
 * permitir el acceso a recursos protegidos.
 * 
 * @version 1.0.0
 * @author Sistema Cyberhole
 */

require_once __DIR__ . '/../config/SecurityConfig.php';
require_once __DIR__ . '/../config/env.php';

class AuthMiddleware {
    
    private static $excludedRoutes = [
        '/login',
        '/register', 
        '/api/auth/login',
        '/api/auth/register',
        '/api/health',
        '/index.php'
    ];
    
    /**
     * Verificar autenticación de la solicitud
     * 
     * @param string $route Ruta solicitada
     * @return array Resultado de la verificación
     */
    public static function verify(string $route = ''): array {
        try {
            // Inicializar configuración de seguridad
            SecurityConfig::init();
            
            // Verificar si la ruta está excluida
            if (self::isExcludedRoute($route)) {
                return [
                    'success' => true,
                    'message' => 'Ruta pública, autenticación no requerida',
                    'user' => null
                ];
            }
            
            // Verificar sesión activa
            $sessionResult = self::verifySession();
            if ($sessionResult['success']) {
                return $sessionResult;
            }
            
            // Si no hay sesión, verificar token JWT
            $jwtResult = self::verifyJWT();
            if ($jwtResult['success']) {
                return $jwtResult;
            }
            
            // No hay autenticación válida
            return [
                'success' => false,
                'message' => 'No autorizado - Sesión o token requerido',
                'error_code' => 401,
                'user' => null
            ];
            
        } catch (Exception $e) {
            error_log("AuthMiddleware Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno de autenticación',
                'error_code' => 500,
                'user' => null
            ];
        }
    }
    
    /**
     * Verificar si existe sesión activa
     * 
     * @return array Resultado de verificación de sesión
     */
    private static function verifySession(): array {
        // Iniciar sesión si no está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificar si existe usuario en sesión
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
            return [
                'success' => false,
                'message' => 'No hay sesión activa',
                'user' => null
            ];
        }
        
        // Verificar tiempo de expiración de sesión
        if (isset($_SESSION['last_activity'])) {
            $sessionLifetime = SecurityConfig::get('session.lifetime', 7200);
            if (time() - $_SESSION['last_activity'] > $sessionLifetime) {
                session_destroy();
                return [
                    'success' => false,
                    'message' => 'Sesión expirada',
                    'user' => null
                ];
            }
        }
        
        // Regenerar ID de sesión periódicamente
        $regenerateInterval = SecurityConfig::get('session.regenerate_interval', 300);
        if (!isset($_SESSION['last_regeneration']) || 
            time() - $_SESSION['last_regeneration'] > $regenerateInterval) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
        
        // Actualizar último acceso
        $_SESSION['last_activity'] = time();
        
        return [
            'success' => true,
            'message' => 'Sesión válida',
            'user' => [
                'id' => $_SESSION['user_id'],
                'type' => $_SESSION['user_type'],
                'rol' => $_SESSION['user_rol'] ?? null,
                'condominio_id' => $_SESSION['condominio_id'] ?? null,
                'name' => $_SESSION['user_name'] ?? 'Usuario'
            ]
        ];
    }
    
    /**
     * Verificar token JWT si está presente
     * 
     * @return array Resultado de verificación JWT
     */
    private static function verifyJWT(): array {
        // Obtener token del header Authorization
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            return [
                'success' => false,
                'message' => 'Token JWT no encontrado',
                'user' => null
            ];
        }
        
        $token = substr($authHeader, 7); // Remover "Bearer "
        
        try {
            // Decodificar y verificar JWT
            $payload = self::decodeJWT($token);
            
            if (!$payload) {
                return [
                    'success' => false,
                    'message' => 'Token JWT inválido',
                    'user' => null
                ];
            }
            
            // Verificar expiración
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return [
                    'success' => false,
                    'message' => 'Token JWT expirado',
                    'user' => null
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Token JWT válido',
                'user' => [
                    'id' => $payload['user_id'] ?? null,
                    'type' => $payload['user_type'] ?? null,
                    'rol' => $payload['user_rol'] ?? null,
                    'condominio_id' => $payload['condominio_id'] ?? null,
                    'name' => $payload['user_name'] ?? 'Usuario'
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al procesar token JWT',
                'user' => null
            ];
        }
    }
    
    /**
     * Decodificar token JWT (implementación simple)
     * 
     * @param string $token Token JWT
     * @return array|false Payload decodificado o false si es inválido
     */
    private static function decodeJWT(string $token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        [$header, $payload, $signature] = $parts;
        
        // Decodificar payload
        $decodedPayload = json_decode(base64_decode($payload), true);
        
        if (!$decodedPayload) {
            return false;
        }
        
        // Verificar firma (implementación básica)
        $expectedSignature = self::createJWTSignature($header . '.' . $payload);
        
        if (!hash_equals($signature, $expectedSignature)) {
            return false;
        }
        
        return $decodedPayload;
    }
    
    /**
     * Crear firma JWT
     * 
     * @param string $data Datos a firmar
     * @return string Firma generada
     */
    private static function createJWTSignature(string $data): string {
        $key = env('JWT_SECRET_KEY', 'cyberhole_default_secret_key_2025');
        return base64_encode(hash_hmac('sha256', $data, $key, true));
    }
    
    /**
     * Verificar si la ruta está excluida de autenticación
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
     * Ejecutar middleware y detener ejecución si no está autorizado
     * 
     * @param string $route Ruta solicitada
     * @return array Datos del usuario autenticado
     */
    public static function execute(string $route = ''): array {
        $result = self::verify($route);
        
        if (!$result['success']) {
            http_response_code($result['error_code'] ?? 401);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $result['message'],
                'error_code' => $result['error_code'] ?? 401
            ]);
            exit;
        }
        
        return $result['user'] ?? [];
    }
    
    /**
     * Middleware para APIs - No detiene ejecución, solo retorna resultado
     * 
     * @param string $route Ruta solicitada  
     * @return array Resultado de verificación
     */
    public static function check(string $route = ''): array {
        return self::verify($route);
    }
    
    /**
     * Cerrar sesión del usuario actual
     * 
     * @return bool True si se cerró correctamente
     */
    public static function logout(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Limpiar variables de sesión
        $_SESSION = [];
        
        // Eliminar cookie de sesión si existe
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destruir sesión
        return session_destroy();
    }
}
