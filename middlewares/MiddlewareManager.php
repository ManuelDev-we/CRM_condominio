<?php
/**
 * MiddlewareManager - Gestor Central de Middlewares
 * Sistema Cyberhole Condominios
 * 
 * Clase de utilidad para ejecutar múltiples middlewares en secuencia
 * y proporcionar una interfaz unificada para la gestión de seguridad.
 * 
 * @version 1.0.0
 * @author Sistema Cyberhole
 */

require_once __DIR__ . '/AuthMiddleware.php';
require_once __DIR__ . '/RoleMiddleware.php';
require_once __DIR__ . '/CsrfMiddleware.php';
require_once __DIR__ . '/RateLimitMiddleware.php';
require_once __DIR__ . '/CondominioOwnershipMiddleware.php';

class MiddlewareManager {
    
    /**
     * Ejecutar pipeline completo de middlewares
     * 
     * @param string $route Ruta solicitada
     * @param string $method Método HTTP
     * @param array $requiredRoles Roles requeridos
     * @param int|null $condominioId ID del condominio (opcional)
     * @param array $requestData Datos de la solicitud
     * @return array Datos del usuario autenticado
     */
    public static function execute(
        string $route, 
        string $method = 'GET',
        array $requiredRoles = [],
        ?int $condominioId = null,
        array $requestData = []
    ): array {
        
        // 1. Autenticación - Verificar sesión/token
        $user = AuthMiddleware::execute($route);
        
        // 2. Limitación de tasa - Prevenir abusos
        $identifier = RateLimitMiddleware::getIdentifier($user);
        RateLimitMiddleware::execute($identifier, $route);
        
        // 3. Protección CSRF - Solo para métodos que modifican datos
        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            CsrfMiddleware::execute($method, $route);
        }
        
        // 4. Autorización por rol - Si se especifican roles
        if (!empty($requiredRoles)) {
            RoleMiddleware::execute($user, $requiredRoles, $route);
        }
        
        // 5. Propiedad de condominio - Si se especifica condominio
        if ($condominioId !== null || !empty($requestData)) {
            CondominioOwnershipMiddleware::execute($user, $condominioId, $route, $requestData);
        }
        
        return $user;
    }
    
    /**
     * Verificar middlewares sin detener ejecución
     * 
     * @param string $route Ruta solicitada
     * @param string $method Método HTTP
     * @param array $requiredRoles Roles requeridos
     * @param int|null $condominioId ID del condominio
     * @param array $requestData Datos de la solicitud
     * @return array Resultado de todas las verificaciones
     */
    public static function check(
        string $route,
        string $method = 'GET',
        array $requiredRoles = [],
        ?int $condominioId = null,
        array $requestData = []
    ): array {
        
        $results = [
            'overall_success' => true,
            'user' => null,
            'checks' => []
        ];
        
        // 1. Verificar autenticación
        $authResult = AuthMiddleware::check($route);
        $results['checks']['auth'] = $authResult;
        
        if (!$authResult['success']) {
            $results['overall_success'] = false;
            return $results;
        }
        
        $user = $authResult['user'] ?? [];
        $results['user'] = $user;
        
        // 2. Verificar limitación de tasa
        $identifier = RateLimitMiddleware::getIdentifier($user);
        $rateLimitResult = RateLimitMiddleware::check($identifier, $route);
        $results['checks']['rate_limit'] = $rateLimitResult;
        
        if (!$rateLimitResult['success']) {
            $results['overall_success'] = false;
        }
        
        // 3. Verificar CSRF si es necesario
        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $csrfResult = CsrfMiddleware::check($method, $route);
            $results['checks']['csrf'] = $csrfResult;
            
            if (!$csrfResult['success']) {
                $results['overall_success'] = false;
            }
        }
        
        // 4. Verificar rol si se especifica
        if (!empty($requiredRoles)) {
            $roleResult = RoleMiddleware::check($user, $requiredRoles, $route);
            $results['checks']['role'] = $roleResult;
            
            if (!$roleResult['success']) {
                $results['overall_success'] = false;
            }
        }
        
        // 5. Verificar propiedad de condominio si es necesario
        if ($condominioId !== null || !empty($requestData)) {
            $ownershipResult = CondominioOwnershipMiddleware::check($user, $condominioId, $route, $requestData);
            $results['checks']['ownership'] = $ownershipResult;
            
            if (!$ownershipResult['success']) {
                $results['overall_success'] = false;
            }
        }
        
        return $results;
    }
    
    /**
     * Verificar solo autenticación y rol (caso común)
     * 
     * @param string|array $requiredRoles Rol(es) requerido(s)
     * @param string $route Ruta solicitada
     * @return array Usuario autenticado y autorizado
     */
    public static function authAndRole($requiredRoles, string $route = ''): array {
        $user = AuthMiddleware::execute($route);
        RoleMiddleware::execute($user, $requiredRoles, $route);
        return $user;
    }
    
    /**
     * Configuración rápida para APIs protegidas
     * 
     * @param string $method Método HTTP
     * @param array $requiredRoles Roles requeridos
     * @param int|null $condominioId ID del condominio
     * @return array Usuario verificado
     */
    public static function protectedApi(
        string $method = 'GET',
        array $requiredRoles = [],
        ?int $condominioId = null
    ): array {
        
        $route = $_SERVER['REQUEST_URI'] ?? '';
        $requestData = [];
        
        // Obtener datos de la solicitud según el método
        switch (strtoupper($method)) {
            case 'POST':
            case 'PUT':
            case 'PATCH':
                $requestData = json_decode(file_get_contents('php://input'), true) ?? $_POST;
                break;
            case 'GET':
            case 'DELETE':
                $requestData = $_GET;
                break;
        }
        
        return self::execute($route, $method, $requiredRoles, $condominioId, $requestData);
    }
    
    /**
     * Obtener token CSRF para formularios
     * 
     * @param string $action Acción específica
     * @return array Token data
     */
    public static function getCsrfToken(string $action = 'default'): array {
        return CsrfMiddleware::getTokenForJS($action);
    }
    
    /**
     * Generar campo CSRF para formularios HTML
     * 
     * @param string $action Acción específica
     * @return string HTML del campo
     */
    public static function csrfField(string $action = 'default'): string {
        return CsrfMiddleware::field($action);
    }
    
    /**
     * Verificar si el usuario actual es administrador
     * 
     * @return bool True si es admin
     */
    public static function isCurrentUserAdmin(): bool {
        try {
            $authResult = AuthMiddleware::check();
            if (!$authResult['success']) {
                return false;
            }
            
            return RoleMiddleware::isAdmin($authResult['user'] ?? []);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Obtener información del usuario actual
     * 
     * @return array|null Datos del usuario o null si no está autenticado
     */
    public static function getCurrentUser(): ?array {
        try {
            $authResult = AuthMiddleware::check();
            return $authResult['success'] ? ($authResult['user'] ?? null) : null;
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Verificar si el usuario actual puede realizar una acción
     * 
     * @param string $action Acción (create, read, update, delete)
     * @param string $resource Recurso objetivo
     * @return bool True si puede realizar la acción
     */
    public static function canCurrentUserPerform(string $action, string $resource): bool {
        $user = self::getCurrentUser();
        if (!$user) {
            return false;
        }
        
        return RoleMiddleware::canPerformAction($user, $action, $resource);
    }
    
    /**
     * Obtener estadísticas de rate limiting del usuario actual
     * 
     * @param string $type Tipo de límite
     * @return array Estadísticas
     */
    public static function getCurrentUserRateStats(string $type = 'general'): array {
        $user = self::getCurrentUser();
        $identifier = RateLimitMiddleware::getIdentifier($user ?? []);
        
        return RateLimitMiddleware::getStats($identifier, $type);
    }
    
    /**
     * Limpiar límites de rate limiting del usuario actual
     * 
     * @param string $type Tipo de límite
     * @return bool True si se limpió correctamente
     */
    public static function resetCurrentUserRateLimit(string $type = 'general'): bool {
        $user = self::getCurrentUser();
        $identifier = RateLimitMiddleware::getIdentifier($user ?? []);
        
        return RateLimitMiddleware::reset($identifier, $type);
    }
    
    /**
     * Middleware especial para rutas de administración
     * 
     * @param string $route Ruta solicitada
     * @param string $method Método HTTP
     * @return array Usuario administrador verificado
     */
    public static function adminOnly(string $route = '', string $method = 'GET'): array {
        return self::execute($route, $method, ['ADMIN']);
    }
    
    /**
     * Middleware especial para rutas de residentes
     * 
     * @param string $route Ruta solicitada
     * @param string $method Método HTTP
     * @param int|null $condominioId ID del condominio
     * @return array Usuario residente verificado
     */
    public static function residentOnly(string $route = '', string $method = 'GET', ?int $condominioId = null): array {
        return self::execute($route, $method, ['ADMIN', 'RESIDENTE'], $condominioId);
    }
    
    /**
     * Middleware especial para rutas de empleados
     * 
     * @param string $route Ruta solicitada
     * @param string $method Método HTTP
     * @param int|null $condominioId ID del condominio
     * @return array Usuario empleado verificado
     */
    public static function employeeOnly(string $route = '', string $method = 'GET', ?int $condominioId = null): array {
        return self::execute($route, $method, ['ADMIN', 'EMPLEADO'], $condominioId);
    }
    
    /**
     * Cerrar sesión del usuario actual
     * 
     * @return bool True si se cerró correctamente
     */
    public static function logout(): bool {
        return AuthMiddleware::logout();
    }
}
