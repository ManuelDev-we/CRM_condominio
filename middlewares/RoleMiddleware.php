<?php
/**
 * RoleMiddleware - Middleware de Autorización por Roles
 * Sistema Cyberhole Condominios
 * 
 * Verifica que el usuario tenga el rol adecuado para acceder al recurso solicitado.
 * Roles: ADMIN, RESIDENTE, EMPLEADO
 * 
 * @version 1.0.0
 * @author Sistema Cyberhole
 */

class RoleMiddleware {
    
    // Roles válidos del sistema
    const ROLE_ADMIN = 'ADMIN';
    const ROLE_RESIDENTE = 'RESIDENTE';
    const ROLE_EMPLEADO = 'EMPLEADO';
    
    // Jerarquía de roles (mayor número = mayor privilegio)
    private static $roleHierarchy = [
        self::ROLE_EMPLEADO => 1,
        self::ROLE_RESIDENTE => 2,
        self::ROLE_ADMIN => 3
    ];
    
    // Rutas y roles permitidos
    private static $routePermissions = [
        // Rutas de administración - Solo ADMIN
        '/admin' => [self::ROLE_ADMIN],
        '/api/admin' => [self::ROLE_ADMIN],
        '/api/condominios' => [self::ROLE_ADMIN],
        '/api/calles' => [self::ROLE_ADMIN],
        '/api/casas' => [self::ROLE_ADMIN],
        '/api/blog' => [self::ROLE_ADMIN],
        
        // Rutas de empleados - ADMIN y EMPLEADO
        '/api/empleados' => [self::ROLE_ADMIN, self::ROLE_EMPLEADO],
        '/api/tareas' => [self::ROLE_ADMIN, self::ROLE_EMPLEADO],
        '/api/areas-comunes' => [self::ROLE_ADMIN, self::ROLE_EMPLEADO],
        
        // Rutas de residentes - ADMIN y RESIDENTE
        '/api/personas' => [self::ROLE_ADMIN, self::ROLE_RESIDENTE],
        '/api/vehiculos' => [self::ROLE_ADMIN, self::ROLE_RESIDENTE],
        '/api/tags' => [self::ROLE_ADMIN, self::ROLE_RESIDENTE],
        '/api/engomados' => [self::ROLE_ADMIN, self::ROLE_RESIDENTE],
        
        // Rutas de acceso - Todos los roles
        '/api/accesos' => [self::ROLE_ADMIN, self::ROLE_RESIDENTE, self::ROLE_EMPLEADO],
        '/api/dispositivos' => [self::ROLE_ADMIN, self::ROLE_RESIDENTE, self::ROLE_EMPLEADO],
    ];
    
    /**
     * Verificar si el usuario tiene el rol requerido
     * 
     * @param array $user Datos del usuario autenticado
     * @param string|array $requiredRoles Rol(es) requerido(s)
     * @param string $route Ruta solicitada (opcional)
     * @return array Resultado de la verificación
     */
    public static function verify(array $user, $requiredRoles, string $route = ''): array {
        try {
            // Verificar que el usuario tenga datos válidos
            if (empty($user) || !isset($user['type'])) {
                return [
                    'success' => false,
                    'message' => 'Datos de usuario no válidos',
                    'error_code' => 401
                ];
            }
            
            $userRole = strtoupper($user['type']);
            
            // Verificar que el rol del usuario sea válido
            if (!self::isValidRole($userRole)) {
                return [
                    'success' => false,
                    'message' => 'Rol de usuario no reconocido: ' . $userRole,
                    'error_code' => 403
                ];
            }
            
            // Convertir roles requeridos a array si es string
            if (is_string($requiredRoles)) {
                $requiredRoles = [$requiredRoles];
            }
            
            // Verificar permisos por ruta si se especifica
            if (!empty($route)) {
                $routeRoles = self::getRoutePermissions($route);
                if (!empty($routeRoles)) {
                    $requiredRoles = $routeRoles;
                }
            }
            
            // Verificar si el usuario tiene algún rol requerido
            foreach ($requiredRoles as $requiredRole) {
                $requiredRole = strtoupper($requiredRole);
                
                if (self::hasRole($userRole, $requiredRole)) {
                    return [
                        'success' => true,
                        'message' => 'Acceso autorizado',
                        'user_role' => $userRole,
                        'required_roles' => $requiredRoles
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'Acceso denegado - Rol insuficiente',
                'error_code' => 403,
                'user_role' => $userRole,
                'required_roles' => $requiredRoles
            ];
            
        } catch (Exception $e) {
            error_log("RoleMiddleware Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno de autorización',
                'error_code' => 500
            ];
        }
    }
    
    /**
     * Verificar si el usuario tiene el rol específico o superior
     * 
     * @param string $userRole Rol del usuario
     * @param string $requiredRole Rol requerido
     * @return bool True si tiene acceso
     */
    private static function hasRole(string $userRole, string $requiredRole): bool {
        // Acceso exacto al rol
        if ($userRole === $requiredRole) {
            return true;
        }
        
        // Verificar jerarquía de roles
        $userLevel = self::$roleHierarchy[$userRole] ?? 0;
        $requiredLevel = self::$roleHierarchy[$requiredRole] ?? 0;
        
        // El admin tiene acceso a todo
        if ($userRole === self::ROLE_ADMIN) {
            return true;
        }
        
        return $userLevel >= $requiredLevel;
    }
    
    /**
     * Verificar si es un rol válido del sistema
     * 
     * @param string $role Rol a verificar
     * @return bool True si es válido
     */
    private static function isValidRole(string $role): bool {
        return array_key_exists($role, self::$roleHierarchy);
    }
    
    /**
     * Obtener permisos de una ruta específica
     * 
     * @param string $route Ruta solicitada
     * @return array Roles permitidos para la ruta
     */
    private static function getRoutePermissions(string $route): array {
        foreach (self::$routePermissions as $pattern => $roles) {
            if (str_starts_with($route, $pattern)) {
                return $roles;
            }
        }
        return [];
    }
    
    /**
     * Ejecutar middleware y detener ejecución si no está autorizado
     * 
     * @param array $user Datos del usuario autenticado
     * @param string|array $requiredRoles Rol(es) requerido(s)
     * @param string $route Ruta solicitada
     * @return void
     */
    public static function execute(array $user, $requiredRoles, string $route = ''): void {
        $result = self::verify($user, $requiredRoles, $route);
        
        if (!$result['success']) {
            http_response_code($result['error_code'] ?? 403);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $result['message'],
                'error_code' => $result['error_code'] ?? 403,
                'required_roles' => $result['required_roles'] ?? []
            ]);
            exit;
        }
    }
    
    /**
     * Middleware para APIs - No detiene ejecución, solo retorna resultado
     * 
     * @param array $user Datos del usuario autenticado
     * @param string|array $requiredRoles Rol(es) requerido(s)
     * @param string $route Ruta solicitada
     * @return array Resultado de verificación
     */
    public static function check(array $user, $requiredRoles, string $route = ''): array {
        return self::verify($user, $requiredRoles, $route);
    }
    
    /**
     * Verificar si el usuario es administrador
     * 
     * @param array $user Datos del usuario
     * @return bool True si es admin
     */
    public static function isAdmin(array $user): bool {
        return isset($user['type']) && strtoupper($user['type']) === self::ROLE_ADMIN;
    }
    
    /**
     * Verificar si el usuario es residente
     * 
     * @param array $user Datos del usuario
     * @return bool True si es residente
     */
    public static function isResidente(array $user): bool {
        return isset($user['type']) && strtoupper($user['type']) === self::ROLE_RESIDENTE;
    }
    
    /**
     * Verificar si el usuario es empleado
     * 
     * @param array $user Datos del usuario
     * @return bool True si es empleado
     */
    public static function isEmpleado(array $user): bool {
        return isset($user['type']) && strtoupper($user['type']) === self::ROLE_EMPLEADO;
    }
    
    /**
     * Obtener todos los roles válidos del sistema
     * 
     * @return array Lista de roles válidos
     */
    public static function getValidRoles(): array {
        return array_keys(self::$roleHierarchy);
    }
    
    /**
     * Obtener el nivel de jerarquía de un rol
     * 
     * @param string $role Rol a consultar
     * @return int Nivel de jerarquía (0 si no existe)
     */
    public static function getRoleLevel(string $role): int {
        return self::$roleHierarchy[strtoupper($role)] ?? 0;
    }
    
    /**
     * Verificar si el usuario puede realizar una acción específica
     * 
     * @param array $user Datos del usuario
     * @param string $action Acción solicitada (create, read, update, delete)
     * @param string $resource Recurso sobre el que actúa
     * @return bool True si puede realizar la acción
     */
    public static function canPerformAction(array $user, string $action, string $resource): bool {
        if (!isset($user['type'])) {
            return false;
        }
        
        $userRole = strtoupper($user['type']);
        
        // Reglas específicas por acción y recurso
        $permissions = [
            'create' => [
                'condominios' => [self::ROLE_ADMIN],
                'empleados' => [self::ROLE_ADMIN],
                'tareas' => [self::ROLE_ADMIN, self::ROLE_EMPLEADO],
                'accesos' => [self::ROLE_ADMIN, self::ROLE_RESIDENTE, self::ROLE_EMPLEADO],
            ],
            'read' => [
                'condominios' => [self::ROLE_ADMIN, self::ROLE_RESIDENTE, self::ROLE_EMPLEADO],
                'empleados' => [self::ROLE_ADMIN, self::ROLE_EMPLEADO],
                'tareas' => [self::ROLE_ADMIN, self::ROLE_EMPLEADO],
                'accesos' => [self::ROLE_ADMIN, self::ROLE_RESIDENTE, self::ROLE_EMPLEADO],
            ],
            'update' => [
                'condominios' => [self::ROLE_ADMIN],
                'empleados' => [self::ROLE_ADMIN],
                'tareas' => [self::ROLE_ADMIN, self::ROLE_EMPLEADO],
                'accesos' => [self::ROLE_ADMIN],
            ],
            'delete' => [
                'condominios' => [self::ROLE_ADMIN],
                'empleados' => [self::ROLE_ADMIN],
                'tareas' => [self::ROLE_ADMIN],
                'accesos' => [self::ROLE_ADMIN],
            ]
        ];
        
        $allowedRoles = $permissions[$action][$resource] ?? [];
        
        return in_array($userRole, $allowedRoles) || $userRole === self::ROLE_ADMIN;
    }
}
